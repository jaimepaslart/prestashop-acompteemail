<?php
/**
 * Controleur Admin - Gestion des formulaires LCB-FT
 *
 * Liste, visualisation et telechargement PDF des formulaires.
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'lcbftform/classes/LcbftForm.php';
require_once _PS_MODULE_DIR_ . 'lcbftform/classes/LcbftPdfGenerator.php';

class AdminLcbftFormsController extends ModuleAdminController
{
    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'lcbft_form';
        $this->className = 'LcbftFormModel';
        $this->identifier = 'id_lcbft_form';
        $this->lang = false;
        $this->allow_export = true;
        $this->_defaultOrderBy = 'date_add';
        $this->_defaultOrderWay = 'DESC';

        parent::__construct();

        // Configuration de la liste
        $this->fields_list = array(
            'id_lcbft_form' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'id_order' => array(
                'title' => $this->l('Commande'),
                'align' => 'center',
                'callback' => 'getOrderLink',
            ),
            'nom' => array(
                'title' => $this->l('Nom'),
                'filter_key' => 'a!nom',
            ),
            'prenom' => array(
                'title' => $this->l('Prénom'),
                'filter_key' => 'a!prenom',
            ),
            'email' => array(
                'title' => $this->l('Email'),
            ),
            'acknowledged' => array(
                'title' => $this->l('Signé'),
                'align' => 'center',
                'type' => 'bool',
                'active' => 'status',
                'class' => 'fixed-width-sm',
            ),
            'signed_at' => array(
                'title' => $this->l('Date signature'),
                'type' => 'datetime',
                'filter_key' => 'a!signed_at',
            ),
            'date_add' => array(
                'title' => $this->l('Créé le'),
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ),
        );

        // Actions en masse
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Supprimer'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Supprimer les éléments sélectionnés ?'),
            ),
        );

        // Actions sur les lignes
        $this->addRowAction('view');
        $this->addRowAction('downloadPdf');
        $this->addRowAction('delete');
    }

    /**
     * Initialisation du contenu de la page
     */
    public function initContent()
    {
        parent::initContent();
    }

    /**
     * Initialisation de la toolbar
     */
    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        // Pas de bouton "Ajouter" pour ce controller
        unset($this->page_header_toolbar_btn['new']);
    }

    /**
     * Rendu de la liste
     */
    public function renderList()
    {
        // Ajouter le CSS admin
        $this->addCSS(_PS_MODULE_DIR_ . 'lcbftform/views/css/admin.css');

        return parent::renderList();
    }

    /**
     * Rendu de la vue detail
     */
    public function renderView()
    {
        $id = (int) Tools::getValue('id_lcbft_form');
        $form = new LcbftFormModel($id);

        if (!Validate::isLoadedObject($form)) {
            $this->errors[] = $this->l('Formulaire introuvable.');
            return;
        }

        // Charger la commande si existante
        $order = null;
        if ($form->id_order > 0) {
            $order = new Order((int) $form->id_order);
            if (!Validate::isLoadedObject($order)) {
                $order = null;
            }
        }

        // Charger le client
        $customer = new Customer((int) $form->id_customer);

        // URL de telechargement PDF
        $pdfUrl = $this->context->link->getAdminLink('AdminLcbftForms', true, array(), array(
            'action' => 'downloadPdf',
            'id_lcbft_form' => $form->id,
        ));

        // Assigner les variables au template
        $this->context->smarty->assign(array(
            'lcbft_form' => $form,
            'lcbft_order' => $order,
            'lcbft_customer' => $customer,
            'lcbft_pdf_url' => $pdfUrl,
            'lcbft_origine_fonds' => $form->getOrigineFonds(),
            'lcbft_commentaires' => $form->getCommentaires(),
            'lcbft_justificatifs' => $form->getJustificatifs(),
            'lcbft_origine_labels' => $this->getOrigineFondsLabels(),
            'lcbft_justificatifs_labels' => $this->getJustificatifsLabels(),
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'lcbftform/views/templates/admin/form_detail.tpl');
    }

    /**
     * Traitement des actions
     */
    public function postProcess()
    {
        // Action telecharger PDF
        if (Tools::getValue('action') === 'downloadPdf' || Tools::isSubmit('downloadPdflcbft_form')) {
            $this->processDownloadPdf();
            return;
        }

        parent::postProcess();
    }

    /**
     * Telecharger le PDF d'un formulaire
     */
    protected function processDownloadPdf()
    {
        $id = (int) Tools::getValue('id_lcbft_form');
        $form = new LcbftFormModel($id);

        if (!Validate::isLoadedObject($form)) {
            $this->errors[] = $this->l('Formulaire introuvable.');
            return;
        }

        // Charger la commande si existante
        $order = null;
        if ($form->id_order > 0) {
            $order = new Order((int) $form->id_order);
        }

        // Generer et telecharger le PDF
        try {
            $module = Module::getInstanceByName('lcbftform');
            $generator = new LcbftPdfGenerator($module);
            $generator->generateAndDownload($form, $order);
            exit;
        } catch (Exception $e) {
            $this->errors[] = $this->l('Erreur lors de la génération du PDF : ') . $e->getMessage();
            PrestaShopLogger::addLog('LcbftForm PDF Error: ' . $e->getMessage(), 3);
        }
    }

    /**
     * Callback pour afficher le lien vers la commande
     *
     * @param int $idOrder
     * @param array $row
     * @return string
     */
    public function getOrderLink($idOrder, $row)
    {
        if (empty($idOrder) || $idOrder <= 0) {
            return '<span class="badge badge-secondary">' . $this->l('Aucune') . '</span>';
        }

        $order = new Order((int) $idOrder);
        if (!Validate::isLoadedObject($order)) {
            return '<span class="badge badge-danger">' . $this->l('Supprimée') . '</span>';
        }

        $link = $this->context->link->getAdminLink('AdminOrders', true, array(), array(
            'vieworder' => 1,
            'id_order' => $idOrder,
        ));

        return '<a href="' . $link . '" class="badge badge-primary">#' . $order->reference . '</a>';
    }

    /**
     * Bouton d'action personnalise pour telecharger le PDF
     *
     * @param string $token
     * @param int $id
     * @param string $name
     * @return string
     */
    public function displayDownloadPdfLink($token, $id, $name = null)
    {
        $form = new LcbftFormModel((int) $id);

        if (!$form->acknowledged) {
            return '<span class="btn btn-default btn-xs disabled" title="' . $this->l('Non signé') . '"><i class="icon-ban"></i></span>';
        }

        $link = $this->context->link->getAdminLink('AdminLcbftForms', true, array(), array(
            'action' => 'downloadPdf',
            'id_lcbft_form' => $id,
        ));

        return '<a class="btn btn-default btn-xs" href="' . $link . '" title="' . $this->l('Télécharger PDF') . '"><i class="icon-file-pdf-o"></i></a>';
    }

    /**
     * Labels des origines des fonds
     *
     * @return array
     */
    protected function getOrigineFondsLabels()
    {
        return array(
            'vente_immo' => $this->l('Vente Immobilière'),
            'donation' => $this->l('Donation'),
            'heritage' => $this->l('Héritage'),
            'revenus' => $this->l('Revenus ou Dividendes'),
            'jeux' => $this->l('Gains aux jeux'),
            'cession' => $this->l('Cession d\'actifs'),
            'epargne' => $this->l('Épargne personnelle'),
            'autre' => $this->l('Autre'),
        );
    }

    /**
     * Labels des justificatifs
     *
     * @return array
     */
    protected function getJustificatifsLabels()
    {
        return array(
            'cni' => $this->l('Carte nationale d\'identité'),
            'passeport' => $this->l('Passeport'),
            'titre_sejour' => $this->l('Titre de séjour'),
            'acte_notarie' => $this->l('Acte notarié'),
            'releve_compte' => $this->l('Relevé de compte'),
            'avis_imposition' => $this->l('Avis d\'imposition'),
            'justif_autre' => $this->l('Autre'),
        );
    }
}
