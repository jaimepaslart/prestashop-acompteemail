<?php
/**
 * Module LCB-FT Form
 *
 * Formulaire LCB-FT obligatoire dans le tunnel de commande PrestaShop.
 * Conforme à l'article L 561-16 du Code monétaire et financier.
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 * @version   1.0.0
 *
 * COMPATIBILITE:
 * - PrestaShop 1.7.6.5 à 1.7.8.11
 * - PHP 7.2 à 7.4
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Charger les classes du module
require_once dirname(__FILE__) . '/classes/LcbftForm.php';

class LcbftForm extends Module
{
    /**
     * Nom de la table SQL
     */
    const TABLE_NAME = 'lcbft_form';

    /**
     * Cle de session pour le statut du formulaire
     */
    const SESSION_KEY = 'lcbft_form_validated';

    /**
     * Constructeur du module
     */
    public function __construct()
    {
        $this->name = 'lcbftform';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Paul Bihr';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Formulaire LCB-FT');
        $this->description = $this->l('Formulaire de lutte contre le blanchiment et financement du terrorisme - Article L 561-16 du Code monétaire et financier');
        $this->confirmUninstall = $this->l('Êtes-vous sûr de vouloir désinstaller ce module ? Toutes les données LCB-FT seront supprimées.');

        $this->ps_versions_compliancy = array('min' => '1.7.6.0', 'max' => '1.7.8.99');
    }

    /**
     * Installation du module
     *
     * @return bool
     */
    public function install()
    {
        // Executer le SQL d'installation
        if (!$this->executeSqlFile('install')) {
            return false;
        }

        // Installer l'onglet admin
        if (!$this->installTab()) {
            return false;
        }

        // Installer le module et enregistrer les hooks
        return parent::install()
            // Hooks Front-Office Checkout
            && $this->registerHook('displayPaymentTop')
            && $this->registerHook('displayPersonalInformationBottom')
            && $this->registerHook('displayPersonalInformationTop')
            && $this->registerHook('displayLcbftFormStep')
            && $this->registerHook('displayLcbftFormStatus')
            && $this->registerHook('Header')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('displayOrderDetail')
            && $this->registerHook('displayOrderConfirmation')
            // Hooks Back-Office
            && $this->registerHook('displayAdminOrder')
            && $this->registerHook('displayAdminOrderMain')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('displayBackOfficeHeader')
            // Hook Compte Client
            && $this->registerHook('displayCustomerAccount');
    }

    /**
     * Desinstallation du module
     *
     * @return bool
     */
    public function uninstall()
    {
        // Supprimer l'onglet admin
        $this->uninstallTab();

        // Executer le SQL de desinstallation
        if (!$this->executeSqlFile('uninstall')) {
            return false;
        }

        return parent::uninstall();
    }

    /**
     * Installe l'onglet admin dans le menu BO
     *
     * @return bool
     */
    protected function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminLcbftForms';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Formulaires LCB-FT';
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentOrders');
        $tab->module = $this->name;

        return $tab->add();
    }

    /**
     * Desinstalle l'onglet admin
     *
     * @return bool
     */
    protected function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminLcbftForms');
        if ($idTab) {
            $tab = new Tab($idTab);
            return $tab->delete();
        }
        return true;
    }

    /**
     * Execute un fichier SQL
     *
     * @param string $type Type de fichier (install ou uninstall)
     * @return bool
     */
    protected function executeSqlFile($type)
    {
        $file = dirname(__FILE__) . '/sql/' . $type . '.sql';
        if (!file_exists($file)) {
            return true;
        }

        $sql = file_get_contents($file);
        if (empty($sql)) {
            return true;
        }

        // Remplacer le prefixe de table
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);

        // Executer les requetes
        $queries = preg_split('/;\s*[\r\n]+/', $sql);
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                if (!Db::getInstance()->execute($query)) {
                    $this->_errors[] = Db::getInstance()->getMsgError();
                    return false;
                }
            }
        }

        return true;
    }

    /* =========================================================================
     * PAGE DE CONFIGURATION
     * ========================================================================= */

    /**
     * Page de configuration du module
     *
     * @return string
     */
    public function getContent()
    {
        $output = '';

        // Enregistrer le hook displayCustomerAccount si pas encore fait
        if (!$this->isRegisteredInHook('displayCustomerAccount')) {
            $this->registerHook('displayCustomerAccount');
        }

        // Informations sur le module
        $output .= $this->displayConfirmation(
            $this->l('Le module LCB-FT est actif. Le formulaire apparaît automatiquement dans le tunnel de commande.')
        );

        // Statistiques
        $stats = $this->getFormStatistics();

        $output .= '<div class="panel">';
        $output .= '<h3><i class="icon-bar-chart"></i> ' . $this->l('Statistiques') . '</h3>';
        $output .= '<div class="row">';
        $output .= '<div class="col-md-3"><div class="alert alert-info text-center"><strong>' . (int) $stats['total'] . '</strong><br>' . $this->l('Formulaires total') . '</div></div>';
        $output .= '<div class="col-md-3"><div class="alert alert-success text-center"><strong>' . (int) $stats['signed'] . '</strong><br>' . $this->l('Formulaires signés') . '</div></div>';
        $output .= '<div class="col-md-3"><div class="alert alert-warning text-center"><strong>' . (int) $stats['pending'] . '</strong><br>' . $this->l('En attente de signature') . '</div></div>';
        $output .= '<div class="col-md-3"><div class="alert alert-primary text-center"><strong>' . (int) $stats['with_order'] . '</strong><br>' . $this->l('Liés à une commande') . '</div></div>';
        $output .= '</div>';
        $output .= '<p class="text-center"><a href="' . $this->context->link->getAdminLink('AdminLcbftForms') . '" class="btn btn-default"><i class="icon-list"></i> ' . $this->l('Voir tous les formulaires') . '</a></p>';
        $output .= '</div>';

        // Instructions
        $output .= '<div class="panel">';
        $output .= '<h3><i class="icon-info-circle"></i> ' . $this->l('Fonctionnement') . '</h3>';
        $output .= '<ul>';
        $output .= '<li>' . $this->l('Le formulaire LCB-FT apparaît après la section "Informations personnelles" du checkout.') . '</li>';
        $output .= '<li>' . $this->l('Le client doit remplir tous les champs obligatoires et cocher la case de reconnaissance.') . '</li>';
        $output .= '<li>' . $this->l('Une signature électronique horodatée est générée automatiquement.') . '</li>';
        $output .= '<li>' . $this->l('Le client ne peut pas poursuivre sa commande tant que le formulaire n\'est pas signé.') . '</li>';
        $output .= '<li>' . $this->l('Le PDF peut être téléchargé depuis le Back-Office ou le compte client.') . '</li>';
        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Recupere les statistiques des formulaires
     *
     * @return array
     */
    protected function getFormStatistics()
    {
        $sql = new DbQuery();
        $sql->select('COUNT(*) as total');
        $sql->select('SUM(CASE WHEN acknowledged = 1 THEN 1 ELSE 0 END) as signed');
        $sql->select('SUM(CASE WHEN acknowledged = 0 THEN 1 ELSE 0 END) as pending');
        $sql->select('SUM(CASE WHEN id_order > 0 THEN 1 ELSE 0 END) as with_order');
        $sql->from(self::TABLE_NAME);

        $result = Db::getInstance()->getRow($sql);

        return array(
            'total' => isset($result['total']) ? $result['total'] : 0,
            'signed' => isset($result['signed']) ? $result['signed'] : 0,
            'pending' => isset($result['pending']) ? $result['pending'] : 0,
            'with_order' => isset($result['with_order']) ? $result['with_order'] : 0,
        );
    }

    /* =========================================================================
     * HOOKS FRONT-OFFICE
     * ========================================================================= */

    /**
     * Hook Header - Chargement CSS/JS front
     *
     * @param array $params
     * @return void
     */
    public function hookHeader($params)
    {
        // Charger uniquement sur les pages de checkout
        $controller = Tools::getValue('controller');
        if ($controller === 'order' || $controller === 'order-opc') {
            $this->context->controller->registerStylesheet(
                'lcbftform-front',
                'modules/' . $this->name . '/views/css/front.css',
                array('media' => 'all', 'priority' => 150)
            );
            $this->context->controller->registerJavascript(
                'lcbftform-front',
                'modules/' . $this->name . '/views/js/front.js',
                array('position' => 'bottom', 'priority' => 150)
            );

            // Passer les URLs AJAX au JS
            Media::addJsDef(array(
                'lcbftform_ajax_url' => $this->context->link->getModuleLink($this->name, 'validation', array(), true),
                'lcbftform_validation_error' => $this->l('Vous devez remplir et signer le formulaire LCB-FT pour continuer.'),
            ));
        }

        // Charger sur la page de detail commande (compte client)
        if ($controller === 'order-detail') {
            $this->context->controller->registerStylesheet(
                'lcbftform-front',
                'modules/' . $this->name . '/views/css/front.css',
                array('media' => 'all', 'priority' => 150)
            );
        }

        // Charger sur la page compte client LCB-FT
        if ($controller === 'account' && Tools::getValue('module') === $this->name) {
            $this->context->controller->registerStylesheet(
                'lcbftform-front',
                'modules/' . $this->name . '/views/css/front.css',
                array('media' => 'all', 'priority' => 150)
            );
        }
    }

    /**
     * Hook displayPaymentTop - Desactive (utilise displayPersonalInformationBottom)
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayPaymentTop($params)
    {
        // Desactive - le formulaire s'affiche maintenant dans l'etape 1 via displayPersonalInformationBottom
        return '';
    }

    /**
     * Hook displayPersonalInformationTop - Fallback pour affichage formulaire
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayPersonalInformationTop($params)
    {
        // Ne pas afficher ici si on utilise displayPaymentTop
        // Pour eviter d'afficher le formulaire deux fois
        return '';
    }

    /**
     * Hook displayPersonalInformationBottom - Desactive (utilise etape dediee)
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayPersonalInformationBottom($params)
    {
        // Desactive - le formulaire s'affiche maintenant dans l'etape dediee
        return '';
    }

    /**
     * Hook displayLcbftFormStep - Affichage du formulaire dans l'etape dediee
     *
     * Ce hook est appele par le template lcbft-step.tpl du theme enfant
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayLcbftFormStep($params)
    {
        return $this->renderLcbftForm();
    }

    /**
     * Hook displayLcbftFormStatus - Retourne le statut du formulaire
     *
     * Retourne '1' si le formulaire est complet et signe, '0' sinon
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayLcbftFormStatus($params)
    {
        if (!$this->context->customer->isLogged()) {
            return '0';
        }

        $idCart = (int) $this->context->cart->id;
        $form = LcbftFormModel::getByCartId($idCart);

        if ($form && $form->isComplete()) {
            return '1';
        }

        return '0';
    }

    /**
     * Rendu du formulaire LCB-FT
     *
     * Methode centralisee pour afficher le formulaire.
     *
     * @return string
     */
    protected function renderLcbftForm()
    {
        // Verifier que le client est connecte
        if (!$this->context->customer->isLogged()) {
            return '';
        }

        // Recuperer ou creer le formulaire pour ce panier
        $idCart = (int) $this->context->cart->id;
        $idCustomer = (int) $this->context->customer->id;

        $form = LcbftFormModel::getByCartId($idCart);

        // Verifier si le formulaire est deja valide (signe)
        $isFormValid = ($form && $form->acknowledged == 1);

        // Si pas de formulaire existant, pre-remplir avec les donnees client
        $customerData = array();
        if (!$form) {
            $customer = $this->context->customer;
            $address = new Address(Address::getFirstCustomerAddressId($customer->id));

            $customerData = array(
                'civilite' => ($customer->id_gender == 1) ? 'M' : 'Mme',
                'nom' => $customer->lastname,
                'prenom' => $customer->firstname,
                'email' => $customer->email,
                'adresse' => Validate::isLoadedObject($address) ? $address->address1 : '',
                'code_postal' => Validate::isLoadedObject($address) ? $address->postcode : '',
                'ville' => Validate::isLoadedObject($address) ? $address->city : '',
                'pays' => Validate::isLoadedObject($address) ? Country::getNameById($this->context->language->id, $address->id_country) : '',
                'telephone' => Validate::isLoadedObject($address) ? ($address->phone_mobile ? $address->phone_mobile : $address->phone) : '',
            );
        }

        // Preparer les variables pour le template
        $this->context->smarty->assign(array(
            'lcbft_form' => $form,
            'lcbft_form_valid' => $isFormValid,
            'lcbft_customer_data' => $customerData,
            'lcbft_id_cart' => $idCart,
            'lcbft_id_customer' => $idCustomer,
            'lcbft_ajax_url' => $this->context->link->getModuleLink($this->name, 'validation', array(), true),
            'lcbft_current_year' => date('Y'),
            'lcbft_countries' => Country::getCountries($this->context->language->id, true),
        ));

        return $this->display(__FILE__, 'views/templates/hook/checkout_form.tpl');
    }

    /**
     * Hook actionValidateOrder - Lier le formulaire a la commande creee
     *
     * @param array $params
     * @return void
     */
    public function hookActionValidateOrder($params)
    {
        $order = $params['order'];
        $cart = $params['cart'];

        if (!$order || !$cart) {
            return;
        }

        // Recuperer le formulaire LCB-FT du panier
        $form = LcbftFormModel::getByCartId((int) $cart->id);

        if ($form && $form->id) {
            // Associer la commande au formulaire
            $form->id_order = (int) $order->id;
            $form->save();
        }
    }

    /**
     * Hook displayOrderDetail - Lien PDF dans le compte client
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayOrderDetail($params)
    {
        if (!isset($params['order'])) {
            return '';
        }

        $order = $params['order'];

        // Verifier que le client est proprietaire de la commande
        if ((int) $order->id_customer !== (int) $this->context->customer->id) {
            return '';
        }

        // Recuperer le formulaire LCB-FT
        $form = LcbftFormModel::getByOrderId((int) $order->id);

        if (!$form || !$form->acknowledged) {
            return '';
        }

        // URL de telechargement
        $downloadUrl = $this->context->link->getModuleLink(
            $this->name,
            'download',
            array('id_order' => (int) $order->id),
            true
        );

        $this->context->smarty->assign(array(
            'lcbft_form' => $form,
            'lcbft_download_url' => $downloadUrl,
            'lcbft_order_reference' => $order->reference,
        ));

        return $this->display(__FILE__, 'views/templates/hook/order_detail_front.tpl');
    }

    /**
     * Hook displayOrderConfirmation - Lien PDF sur page confirmation
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayOrderConfirmation($params)
    {
        if (!isset($params['order'])) {
            return '';
        }

        $order = $params['order'];
        $form = LcbftFormModel::getByOrderId((int) $order->id);

        if (!$form || !$form->acknowledged) {
            return '';
        }

        $downloadUrl = $this->context->link->getModuleLink(
            $this->name,
            'download',
            array('id_order' => (int) $order->id),
            true
        );

        $this->context->smarty->assign(array(
            'lcbft_download_url' => $downloadUrl,
        ));

        return $this->display(__FILE__, 'views/templates/hook/order_confirmation.tpl');
    }

    /**
     * Hook displayCustomerAccount - Lien vers espace LCB-FT dans Mon compte
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayCustomerAccount($params)
    {
        $this->context->smarty->assign(array(
            'lcbft_account_link' => $this->context->link->getModuleLink($this->name, 'account', array(), true),
        ));

        return $this->display(__FILE__, 'views/templates/hook/customer_account.tpl');
    }

    /* =========================================================================
     * HOOKS BACK-OFFICE
     * ========================================================================= */

    /**
     * Hook actionAdminControllerSetMedia - Chargement CSS/JS admin
     *
     * @param array $params
     * @return void
     */
    public function hookActionAdminControllerSetMedia($params)
    {
        $controller = Tools::getValue('controller');
        if ($controller === 'AdminOrders' || $controller === 'AdminLcbftForms') {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
        }
    }

    /**
     * Hook displayBackOfficeHeader - Fallback CSS admin
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayBackOfficeHeader($params)
    {
        return '';
    }

    /**
     * Hook displayAdminOrder - Bloc LCB-FT sur fiche commande (1.7.6.x)
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayAdminOrder($params)
    {
        // Pour les versions < 1.7.7, utiliser ce hook
        if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            return '';
        }

        return $this->renderAdminOrderBlock($params);
    }

    /**
     * Hook displayAdminOrderMain - Bloc LCB-FT sur fiche commande (1.7.7+)
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayAdminOrderMain($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
            return '';
        }

        return $this->renderAdminOrderBlock($params);
    }

    /**
     * Rendu du bloc LCB-FT pour la page commande admin
     *
     * @param array $params
     * @return string
     */
    protected function renderAdminOrderBlock($params)
    {
        // Recuperer l'ID de la commande
        $idOrder = $this->getOrderIdFromParams($params);
        if (!$idOrder) {
            return '';
        }

        // Recuperer le formulaire LCB-FT
        $form = LcbftFormModel::getByOrderId($idOrder);

        if (!$form) {
            // Pas de formulaire pour cette commande
            $this->context->smarty->assign(array(
                'lcbft_has_form' => false,
            ));
        } else {
            // Formulaire existe
            $downloadUrl = $this->context->link->getAdminLink('AdminLcbftForms', true, array(), array(
                'action' => 'downloadPdf',
                'id_lcbft_form' => $form->id,
            ));

            $detailUrl = $this->context->link->getAdminLink('AdminLcbftForms', true, array(), array(
                'viewlcbft_form' => 1,
                'id_lcbft_form' => $form->id,
            ));

            $this->context->smarty->assign(array(
                'lcbft_has_form' => true,
                'lcbft_form' => $form,
                'lcbft_download_url' => $downloadUrl,
                'lcbft_detail_url' => $detailUrl,
            ));
        }

        return $this->display(__FILE__, 'views/templates/admin/order_block.tpl');
    }

    /**
     * Recupere l'ID de la commande depuis les parametres du hook
     *
     * @param array $params
     * @return int|null
     */
    protected function getOrderIdFromParams($params)
    {
        if (isset($params['id_order'])) {
            return (int) $params['id_order'];
        }

        if (isset($params['order']) && $params['order'] instanceof Order) {
            return (int) $params['order']->id;
        }

        $idOrder = (int) Tools::getValue('id_order');
        if ($idOrder > 0) {
            return $idOrder;
        }

        return null;
    }

    /* =========================================================================
     * METHODES UTILITAIRES
     * ========================================================================= */

    /**
     * Verifie si le formulaire LCB-FT est valide pour un panier
     *
     * @param int $idCart
     * @return bool
     */
    public static function isFormValidForCart($idCart)
    {
        $form = LcbftFormModel::getByCartId((int) $idCart);
        return $form && $form->acknowledged && !empty($form->signature_name);
    }

    /**
     * Recupere le chemin du repertoire des PDF generes
     *
     * @return string
     */
    public function getPdfDir()
    {
        $dir = dirname(__FILE__) . '/pdf/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }
}
