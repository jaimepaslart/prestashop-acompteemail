<?php
/**
 * Front Controller - Espace client LCB-FT
 *
 * Permet au client de consulter, telecharger et modifier
 * ses formulaires LCB-FT depuis son compte.
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

class LcbftFormAccountModuleFrontController extends ModuleFrontController
{
    /**
     * @var bool Require authentication
     */
    public $auth = true;

    /**
     * @var bool Require SSL
     */
    public $ssl = true;

    /**
     * @var string Auth redirect controller
     */
    public $authRedirection = 'my-account';

    /**
     * Initialisation
     */
    public function init()
    {
        parent::init();
        $this->context->smarty->assign('my_account_page', true);
    }

    /**
     * Set media (CSS/JS)
     */
    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS($this->module->getPathUri() . 'views/css/front.css');
    }

    /**
     * Traitement des actions
     */
    public function postProcess()
    {
        // Action telecharger PDF
        if (Tools::getValue('action') === 'download' && Tools::getValue('id_form')) {
            $this->downloadPdf((int) Tools::getValue('id_form'));
        }
    }

    /**
     * Initialisation du contenu
     */
    public function initContent()
    {
        parent::initContent();

        // Recuperer tous les formulaires du client
        $idCustomer = (int) $this->context->customer->id;
        $forms = $this->getCustomerForms($idCustomer);

        // Preparer les donnees pour le template
        $formsData = array();
        foreach ($forms as $form) {
            $order = null;
            if ($form['id_order'] > 0) {
                $order = new Order((int) $form['id_order']);
                if (!Validate::isLoadedObject($order)) {
                    $order = null;
                }
            }

            $formsData[] = array(
                'id' => $form['id_lcbft_form'],
                'id_order' => $form['id_order'],
                'order_reference' => $order ? $order->reference : null,
                'order_link' => $order ? $this->context->link->getPageLink('order-detail', true, null, array('id_order' => $order->id)) : null,
                'nom' => $form['nom'],
                'prenom' => $form['prenom'],
                'signed' => (bool) $form['acknowledged'],
                'signed_at' => $form['signed_at'],
                'date_add' => $form['date_add'],
                'download_link' => $form['acknowledged'] ? $this->context->link->getModuleLink(
                    $this->module->name,
                    'account',
                    array('action' => 'download', 'id_form' => $form['id_lcbft_form']),
                    true
                ) : null,
            );
        }

        // Assigner les variables
        $this->context->smarty->assign(array(
            'forms' => $formsData,
            'has_forms' => !empty($formsData),
            'module_name' => $this->module->displayName,
        ));

        $this->setTemplate('module:lcbftform/views/templates/front/account.tpl');
    }

    /**
     * Recuperer les formulaires d'un client
     *
     * @param int $idCustomer
     * @return array
     */
    protected function getCustomerForms($idCustomer)
    {
        $sql = 'SELECT f.*, o.reference as order_reference
                FROM `' . _DB_PREFIX_ . 'lcbft_form` f
                LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON f.id_order = o.id_order
                WHERE f.id_customer = ' . (int) $idCustomer . '
                ORDER BY f.date_add DESC';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Telecharger le PDF d'un formulaire
     *
     * @param int $idForm
     */
    protected function downloadPdf($idForm)
    {
        $form = new LcbftFormModel($idForm);

        // Verifier que le formulaire appartient au client
        if (!Validate::isLoadedObject($form) || $form->id_customer != $this->context->customer->id) {
            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'account'));
            return;
        }

        // Verifier que le formulaire est signe
        if (!$form->acknowledged) {
            $this->errors[] = $this->trans('Ce formulaire n\'est pas encore signé.', array(), 'Modules.Lcbftform.Shop');
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

        // Generer et telecharger le PDF
        try {
            $generator = new LcbftPdfGenerator($this->module);
            $generator->generateAndDownload($form, $order);
            exit;
        } catch (Exception $e) {
            $this->errors[] = $this->trans('Erreur lors de la génération du PDF.', array(), 'Modules.Lcbftform.Shop');
            PrestaShopLogger::addLog('LcbftForm PDF Error: ' . $e->getMessage(), 3);
        }
    }

    /**
     * Breadcrumb
     */
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = array(
            'title' => $this->trans('My account', array(), 'Shop.Theme.Customeraccount'),
            'url' => $this->context->link->getPageLink('my-account'),
        );

        $breadcrumb['links'][] = array(
            'title' => $this->trans('Mes formulaires LCB-FT', array(), 'Modules.Lcbftform.Shop'),
            'url' => $this->context->link->getModuleLink($this->module->name, 'account'),
        );

        return $breadcrumb;
    }
}
