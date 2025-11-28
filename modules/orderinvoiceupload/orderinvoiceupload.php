<?php
/**
 * Order Invoice Upload
 *
 * Module permettant de téléverser manuellement une facture PDF
 * pour chaque commande dans le Back Office PrestaShop.
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 * @version   1.0.0
 *
 * COMPATIBILITÉ:
 * - PrestaShop 1.7.6.5 à 1.7.8.11
 * - PHP 7.2 à 7.4
 * - Aucun override du cœur
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Charger la classe helper
require_once dirname(__FILE__) . '/classes/OrderInvoiceUploadFile.php';

class OrderInvoiceUpload extends Module
{
    /**
     * Clés de configuration du module
     */
    const CONFIG_FRONT_ENABLED = 'ORDERINVOICEUPLOAD_FRONT_ENABLED';
    const CONFIG_EMAIL_ENABLED = 'ORDERINVOICEUPLOAD_EMAIL_ENABLED';
    const CONFIG_MAX_FILE_SIZE = 'ORDERINVOICEUPLOAD_MAX_FILE_SIZE';

    /**
     * Taille maximale par défaut (5 Mo)
     */
    const DEFAULT_MAX_FILE_SIZE = 5;

    /**
     * Extensions autorisées
     */
    const ALLOWED_EXTENSIONS = array('pdf');

    /**
     * Types MIME autorisés
     */
    const ALLOWED_MIME_TYPES = array('application/pdf');

    /**
     * Messages d'erreur/succès pour le template
     *
     * @var array
     */
    protected $moduleMessages = array();

    /**
     * Constructeur du module
     */
    public function __construct()
    {
        $this->name = 'orderinvoiceupload';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Paul Bihr';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Order Invoice Upload');
        $this->description = $this->l('Permet de téléverser manuellement une facture PDF pour chaque commande.');
        $this->confirmUninstall = $this->l('Êtes-vous sûr de vouloir désinstaller ce module ? Toutes les factures téléversées seront supprimées.');

        $this->ps_versions_compliancy = array('min' => '1.7.6.0', 'max' => '1.7.8.99');
    }

    /**
     * Installation du module
     *
     * @return bool
     */
    public function install()
    {
        // Créer le répertoire d'upload s'il n'existe pas
        $uploadDir = $this->getUploadDir();
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $this->_errors[] = $this->l('Impossible de créer le répertoire d\'upload.');
                return false;
            }
        }

        // Créer le fichier .htaccess de protection
        $htaccessPath = $uploadDir . '.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "# Deny access to all files\n";
            $htaccessContent .= "<IfModule mod_authz_core.c>\n";
            $htaccessContent .= "    Require all denied\n";
            $htaccessContent .= "</IfModule>\n";
            $htaccessContent .= "<IfModule !mod_authz_core.c>\n";
            $htaccessContent .= "    Order deny,allow\n";
            $htaccessContent .= "    Deny from all\n";
            $htaccessContent .= "</IfModule>\n";
            file_put_contents($htaccessPath, $htaccessContent);
        }

        // Créer le fichier index.php de protection
        $indexPath = $uploadDir . 'index.php';
        if (!file_exists($indexPath)) {
            $indexContent = "<?php\nheader('Location: ../../');\nexit;\n";
            file_put_contents($indexPath, $indexContent);
        }

        // Exécuter le SQL d'installation
        if (!$this->executeSqlFile('install')) {
            return false;
        }

        // Initialiser les paramètres de configuration par défaut
        Configuration::updateValue(self::CONFIG_FRONT_ENABLED, 1);
        Configuration::updateValue(self::CONFIG_EMAIL_ENABLED, 0);
        Configuration::updateValue(self::CONFIG_MAX_FILE_SIZE, self::DEFAULT_MAX_FILE_SIZE);

        // Installer les templates d'email
        $this->installEmailTemplates();

        // Installer le module et enregistrer les hooks
        // Hooks Back Office :
        // - displayAdminOrder : Affichage dans la page commande (compatible 1.7.6.x)
        // - displayAdminOrderMain : Zone principale page commande (1.7.7+)
        // - displayAdminOrderTabLink : Lien d'onglet (1.7.7+)
        // - displayAdminOrderTabContent : Contenu d'onglet (1.7.7+)
        // - actionAdminControllerSetMedia : Chargement CSS/JS admin
        // - displayBackOfficeHeader : Fallback CSS admin
        //
        // Hooks Front Office :
        // - displayOrderDetail : Affichage sur la page détail commande client
        // - displayHeader : Chargement CSS front
        return parent::install()
            && $this->registerHook('displayAdminOrder')
            && $this->registerHook('displayAdminOrderMain')
            && $this->registerHook('displayAdminOrderTabLink')
            && $this->registerHook('displayAdminOrderTabContent')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayOrderDetail')
            && $this->registerHook('displayHeader');
    }

    /**
     * Désinstallation du module
     *
     * @return bool
     */
    public function uninstall()
    {
        // Supprimer tous les fichiers uploadés
        $this->deleteAllUploadedFiles();

        // Exécuter le SQL de désinstallation
        if (!$this->executeSqlFile('uninstall')) {
            return false;
        }

        // Supprimer les paramètres de configuration
        Configuration::deleteByName(self::CONFIG_FRONT_ENABLED);
        Configuration::deleteByName(self::CONFIG_EMAIL_ENABLED);
        Configuration::deleteByName(self::CONFIG_MAX_FILE_SIZE);

        return parent::uninstall();
    }

    /**
     * Exécute un fichier SQL
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

        // Remplacer le préfixe de table
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);

        // Exécuter les requêtes
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

    /**
     * Supprime tous les fichiers uploadés
     *
     * @return void
     */
    protected function deleteAllUploadedFiles()
    {
        $uploadDir = $this->getUploadDir();
        if (!is_dir($uploadDir)) {
            return;
        }

        $files = glob($uploadDir . '*.pdf');
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }

    /**
     * Retourne le chemin du répertoire d'upload
     *
     * @return string
     */
    public function getUploadDir()
    {
        return dirname(__FILE__) . '/uploads/';
    }

    /**
     * Hook pour charger les médias dans le Back Office
     *
     * @param array $params Paramètres du hook
     * @return void
     */
    public function hookActionAdminControllerSetMedia($params)
    {
        // Charger CSS/JS uniquement sur la page commande
        $controller = Tools::getValue('controller');
        if ($controller === 'AdminOrders') {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            $this->context->controller->addJS($this->_path . 'views/js/admin.js');
        }
    }

    /**
     * Hook pour le header du Back Office (fallback CSS/JS)
     *
     * @param array $params Paramètres du hook
     * @return string
     */
    public function hookDisplayBackOfficeHeader($params)
    {
        // Ce hook est utilisé comme fallback pour les versions où
        // actionAdminControllerSetMedia ne fonctionne pas correctement
        $controller = Tools::getValue('controller');
        $output = '';

        if ($controller === 'AdminOrders' && Tools::getValue('vieworder')) {
            // Ajouter CSS inline si le fichier externe n'est pas chargé
            $output .= '<style>' . file_get_contents($this->getLocalPath() . 'views/css/admin.css') . '</style>';
        }

        return $output;
    }

    /**
     * Hook displayAdminOrder - Compatible PrestaShop 1.7.6.x
     *
     * Ce hook s'affiche dans la page de détail d'une commande.
     * Il est le principal hook pour les versions 1.7.6.x.
     *
     * @param array $params Paramètres du hook (contient id_order)
     * @return string HTML à afficher
     */
    public function hookDisplayAdminOrder($params)
    {
        // Vérifier si on est sur une version < 1.7.7 (où ce hook est le principal)
        // Pour les versions 1.7.7+, on utilise displayAdminOrderMain
        if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            return '';
        }

        return $this->renderOrderInvoiceBlock($params);
    }

    /**
     * Hook displayAdminOrderMain - Compatible PrestaShop 1.7.7+
     *
     * Ce hook s'affiche dans la zone principale de la page commande.
     * C'est le hook principal pour les versions 1.7.7+.
     *
     * @param array $params Paramètres du hook
     * @return string HTML à afficher
     */
    public function hookDisplayAdminOrderMain($params)
    {
        // Ce hook n'est disponible que sur 1.7.7+
        if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
            return '';
        }

        return $this->renderOrderInvoiceBlock($params);
    }

    /**
     * Hook displayAdminOrderTabLink - Onglet dans la page commande (1.7.7+)
     *
     * Ajoute un lien d'onglet "Facture manuelle" dans la page commande.
     *
     * @param array $params Paramètres du hook
     * @return string HTML du lien d'onglet
     */
    public function hookDisplayAdminOrderTabLink($params)
    {
        // Disponible uniquement sur 1.7.7+
        if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
            return '';
        }

        // Ce hook affiche le lien de l'onglet
        // L'implémentation dépend du template de PrestaShop
        // Pour garder les choses simples, on n'utilise pas ce hook
        // et on affiche directement dans displayAdminOrderMain
        return '';
    }

    /**
     * Hook displayAdminOrderTabContent - Contenu d'onglet (1.7.7+)
     *
     * @param array $params Paramètres du hook
     * @return string HTML du contenu d'onglet
     */
    public function hookDisplayAdminOrderTabContent($params)
    {
        // Disponible uniquement sur 1.7.7+
        if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
            return '';
        }

        // Même logique que displayAdminOrderTabLink
        return '';
    }

    /**
     * Rendu du bloc de facture pour la page commande
     *
     * Cette méthode gère :
     * - Le traitement des actions (upload, suppression)
     * - L'affichage du formulaire et des informations
     *
     * @param array $params Paramètres du hook
     * @return string HTML du bloc
     */
    protected function renderOrderInvoiceBlock($params)
    {
        // Récupérer l'ID de la commande
        $idOrder = $this->getOrderIdFromParams($params);
        if (!$idOrder) {
            return '';
        }

        // Traiter les actions POST (upload, suppression)
        $this->processActions($idOrder);

        // Récupérer les informations sur la facture existante
        $invoiceFile = OrderInvoiceUploadFile::getByOrderId($idOrder);

        // Préparer les variables pour le template
        $maxFileSize = $this->getMaxFileSize();
        $this->context->smarty->assign(array(
            'orderinvoiceupload_id_order' => $idOrder,
            'orderinvoiceupload_invoice' => $invoiceFile,
            'orderinvoiceupload_max_size' => $maxFileSize,
            'orderinvoiceupload_max_size_mb' => round($maxFileSize / (1024 * 1024), 1),
            'orderinvoiceupload_allowed_extensions' => implode(', ', self::ALLOWED_EXTENSIONS),
            'orderinvoiceupload_upload_url' => $this->context->link->getAdminLink('AdminOrders') . '&vieworder&id_order=' . $idOrder,
            'orderinvoiceupload_download_url' => $invoiceFile ? $this->getDownloadUrl($idOrder) : '',
            'orderinvoiceupload_token' => Tools::getAdminTokenLite('AdminOrders'),
            'orderinvoiceupload_module_name' => $this->name,
            'orderinvoiceupload_messages' => $this->moduleMessages,
            'orderinvoiceupload_ps_version' => _PS_VERSION_,
        ));

        return $this->display(__FILE__, 'views/templates/admin/order_invoice_block.tpl');
    }

    /**
     * Récupère l'ID de la commande depuis les paramètres du hook
     *
     * @param array $params Paramètres du hook
     * @return int|null ID de la commande ou null si non trouvé
     */
    protected function getOrderIdFromParams($params)
    {
        // Méthode 1 : Depuis les paramètres du hook (1.7.7+)
        if (isset($params['id_order'])) {
            return (int) $params['id_order'];
        }

        // Méthode 2 : Depuis l'objet Order (1.7.7+)
        if (isset($params['order']) && $params['order'] instanceof Order) {
            return (int) $params['order']->id;
        }

        // Méthode 3 : Depuis la requête GET (1.7.6.x)
        $idOrder = (int) Tools::getValue('id_order');
        if ($idOrder > 0) {
            return $idOrder;
        }

        return null;
    }

    /**
     * Traite les actions POST (upload, suppression)
     *
     * @param int $idOrder ID de la commande
     * @return void
     */
    protected function processActions($idOrder)
    {
        // Vérifier qu'on est en POST et que c'est notre formulaire
        if (!Tools::isSubmit('submitOrderInvoiceUpload') && !Tools::isSubmit('deleteOrderInvoice')) {
            return;
        }

        // Vérifier le token de sécurité
        if (!$this->isValidToken()) {
            $this->moduleMessages['errors'][] = $this->l('Token de sécurité invalide.');
            return;
        }

        // Traiter la suppression
        if (Tools::isSubmit('deleteOrderInvoice')) {
            $this->processDelete($idOrder);
            return;
        }

        // Traiter l'upload
        if (Tools::isSubmit('submitOrderInvoiceUpload')) {
            $this->processUpload($idOrder);
        }
    }

    /**
     * Vérifie la validité du token de sécurité
     *
     * @return bool
     */
    protected function isValidToken()
    {
        $token = Tools::getValue('token');
        $expectedToken = Tools::getAdminTokenLite('AdminOrders');

        return $token === $expectedToken;
    }

    /**
     * Traite l'upload d'une facture
     *
     * @param int $idOrder ID de la commande
     * @return bool
     */
    protected function processUpload($idOrder)
    {
        // Vérifier que le fichier a été envoyé
        if (!isset($_FILES['invoice_file']) || $_FILES['invoice_file']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->moduleMessages['errors'][] = $this->l('Aucun fichier sélectionné.');
            return false;
        }

        $file = $_FILES['invoice_file'];

        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->moduleMessages['errors'][] = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Vérifier la taille du fichier
        $maxFileSize = $this->getMaxFileSize();
        if ($file['size'] > $maxFileSize) {
            $this->moduleMessages['errors'][] = sprintf(
                $this->l('Le fichier est trop volumineux. Taille maximale : %s Mo.'),
                round($maxFileSize / (1024 * 1024), 1)
            );
            return false;
        }

        // Vérifier l'extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            $this->moduleMessages['errors'][] = sprintf(
                $this->l('Extension non autorisée. Extensions acceptées : %s.'),
                implode(', ', self::ALLOWED_EXTENSIONS)
            );
            return false;
        }

        // Vérifier le type MIME
        $mimeType = $this->getMimeType($file['tmp_name']);
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            $this->moduleMessages['errors'][] = $this->l('Type de fichier non autorisé. Seuls les fichiers PDF sont acceptés.');
            return false;
        }

        // Vérifier que c'est un vrai PDF (magic bytes)
        if (!$this->isValidPdf($file['tmp_name'])) {
            $this->moduleMessages['errors'][] = $this->l('Le fichier ne semble pas être un PDF valide.');
            return false;
        }

        // Supprimer l'ancienne facture si elle existe
        $existingFile = OrderInvoiceUploadFile::getByOrderId($idOrder);
        if ($existingFile) {
            $this->deleteInvoiceFile($existingFile);
        }

        // Générer un nom de fichier unique et sécurisé
        $uniqueName = $this->generateUniqueFileName($idOrder, $extension);
        $destPath = $this->getUploadDir() . $uniqueName;

        // Déplacer le fichier
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->moduleMessages['errors'][] = $this->l('Erreur lors de l\'enregistrement du fichier.');
            return false;
        }

        // Enregistrer en base de données
        $invoiceFile = new OrderInvoiceUploadFile();
        $invoiceFile->id_order = $idOrder;
        $invoiceFile->file_name = $uniqueName;
        $invoiceFile->original_name = $file['name'];
        $invoiceFile->mime_type = $mimeType;
        $invoiceFile->date_add = date('Y-m-d H:i:s');

        if (!$invoiceFile->save()) {
            // Supprimer le fichier si l'enregistrement en BDD échoue
            @unlink($destPath);
            $this->moduleMessages['errors'][] = $this->l('Erreur lors de l\'enregistrement en base de données.');
            return false;
        }

        // Envoyer l'email de notification au client (si activé)
        $this->sendInvoiceNotificationEmail($idOrder);

        $this->moduleMessages['success'][] = $this->l('Facture téléversée avec succès.');
        return true;
    }

    /**
     * Traite la suppression d'une facture
     *
     * @param int $idOrder ID de la commande
     * @return bool
     */
    protected function processDelete($idOrder)
    {
        $invoiceFile = OrderInvoiceUploadFile::getByOrderId($idOrder);
        if (!$invoiceFile) {
            $this->moduleMessages['errors'][] = $this->l('Aucune facture à supprimer.');
            return false;
        }

        if (!$this->deleteInvoiceFile($invoiceFile)) {
            $this->moduleMessages['errors'][] = $this->l('Erreur lors de la suppression de la facture.');
            return false;
        }

        $this->moduleMessages['success'][] = $this->l('Facture supprimée avec succès.');
        return true;
    }

    /**
     * Supprime un fichier de facture (fichier + BDD)
     *
     * @param OrderInvoiceUploadFile $invoiceFile Objet facture
     * @return bool
     */
    protected function deleteInvoiceFile($invoiceFile)
    {
        // Supprimer le fichier physique
        $filePath = $this->getUploadDir() . $invoiceFile->file_name;
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        // Supprimer l'entrée en BDD
        return $invoiceFile->delete();
    }

    /**
     * Génère un nom de fichier unique et sécurisé
     *
     * @param int $idOrder ID de la commande
     * @param string $extension Extension du fichier
     * @return string Nom de fichier unique
     */
    protected function generateUniqueFileName($idOrder, $extension)
    {
        // Format : invoice_{id_order}_{timestamp}_{random}.pdf
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        return sprintf('invoice_%d_%d_%s.%s', $idOrder, $timestamp, $random, $extension);
    }

    /**
     * Récupère le type MIME d'un fichier
     *
     * @param string $filePath Chemin du fichier
     * @return string Type MIME
     */
    protected function getMimeType($filePath)
    {
        // Utiliser finfo si disponible (recommandé)
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mimeType;
        }

        // Fallback avec mime_content_type
        if (function_exists('mime_content_type')) {
            return mime_content_type($filePath);
        }

        // Fallback basique
        return 'application/octet-stream';
    }

    /**
     * Vérifie si un fichier est un PDF valide (magic bytes)
     *
     * @param string $filePath Chemin du fichier
     * @return bool
     */
    protected function isValidPdf($filePath)
    {
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }

        // Les fichiers PDF commencent par "%PDF-"
        $header = fread($handle, 5);
        fclose($handle);

        return $header === '%PDF-';
    }

    /**
     * Retourne le message d'erreur d'upload correspondant au code d'erreur
     *
     * @param int $errorCode Code d'erreur PHP
     * @return string Message d'erreur
     */
    protected function getUploadErrorMessage($errorCode)
    {
        $messages = array(
            UPLOAD_ERR_INI_SIZE => $this->l('Le fichier dépasse la taille maximale autorisée par le serveur.'),
            UPLOAD_ERR_FORM_SIZE => $this->l('Le fichier dépasse la taille maximale autorisée.'),
            UPLOAD_ERR_PARTIAL => $this->l('Le fichier n\'a été que partiellement téléchargé.'),
            UPLOAD_ERR_NO_FILE => $this->l('Aucun fichier n\'a été téléchargé.'),
            UPLOAD_ERR_NO_TMP_DIR => $this->l('Dossier temporaire manquant sur le serveur.'),
            UPLOAD_ERR_CANT_WRITE => $this->l('Échec de l\'écriture du fichier sur le disque.'),
            UPLOAD_ERR_EXTENSION => $this->l('Upload bloqué par une extension PHP.'),
        );

        return isset($messages[$errorCode])
            ? $messages[$errorCode]
            : $this->l('Erreur inconnue lors de l\'upload.');
    }

    /**
     * Génère l'URL de téléchargement sécurisée pour une facture
     *
     * @param int $idOrder ID de la commande
     * @return string URL de téléchargement
     */
    public function getDownloadUrl($idOrder)
    {
        // Utiliser notre contrôleur admin pour le téléchargement sécurisé
        $params = array(
            'action' => 'download',
            'id_order' => $idOrder,
        );

        return $this->context->link->getAdminLink('AdminOrderInvoiceUpload', true, array(), $params);
    }

    /**
     * Télécharge un fichier de facture (appelé par le contrôleur)
     *
     * @param int $idOrder ID de la commande
     * @return void
     */
    public function downloadInvoice($idOrder)
    {
        $invoiceFile = OrderInvoiceUploadFile::getByOrderId($idOrder);
        if (!$invoiceFile) {
            die('Facture non trouvée.');
        }

        $filePath = $this->getUploadDir() . $invoiceFile->file_name;
        if (!file_exists($filePath)) {
            die('Fichier non trouvé.');
        }

        // Envoyer les headers pour le téléchargement
        header('Content-Type: ' . $invoiceFile->mime_type);
        header('Content-Disposition: attachment; filename="' . $invoiceFile->original_name . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        // Envoyer le contenu du fichier
        readfile($filePath);
        exit;
    }

    /* =========================================================================
     * HOOKS FRONT OFFICE
     * ========================================================================= */

    /**
     * Hook displayHeader - Chargement des CSS front-office
     *
     * @param array $params Paramètres du hook
     * @return void
     */
    public function hookDisplayHeader($params)
    {
        // Charger le CSS front uniquement sur la page de détail de commande
        $controller = Tools::getValue('controller');
        if ($controller === 'order-detail') {
            $this->context->controller->registerStylesheet(
                'orderinvoiceupload-front',
                'modules/' . $this->name . '/views/css/front.css',
                array('media' => 'all', 'priority' => 150)
            );
        }
    }

    /**
     * Hook displayOrderDetail - Affichage sur la page détail commande client
     *
     * Ce hook affiche le bloc "Facture associée" sur la page de détail
     * d'une commande dans le compte client.
     *
     * Sécurité :
     * - Vérifie que le client est connecté
     * - Vérifie que la commande appartient au client
     * - Interface en lecture seule uniquement
     *
     * @param array $params Paramètres du hook (contient 'order')
     * @return string HTML à afficher
     */
    public function hookDisplayOrderDetail($params)
    {
        // Vérifier si l'affichage front est activé
        if (!Configuration::get(self::CONFIG_FRONT_ENABLED)) {
            return '';
        }

        // Vérifier que le client est connecté
        if (!$this->context->customer->isLogged()) {
            return '';
        }

        // Récupérer la commande depuis les paramètres
        $order = isset($params['order']) ? $params['order'] : null;

        if (!$order || !Validate::isLoadedObject($order)) {
            return '';
        }

        // SÉCURITÉ : Vérifier que la commande appartient au client connecté
        if ((int) $order->id_customer !== (int) $this->context->customer->id) {
            return '';
        }

        // Récupérer la facture associée
        $invoiceFile = OrderInvoiceUploadFile::getByOrderId((int) $order->id);

        // Si aucune facture, ne rien afficher
        if (!$invoiceFile) {
            return '';
        }

        // Générer l'URL de téléchargement front
        $downloadUrl = $this->getFrontDownloadUrl((int) $order->id);

        // Préparer les variables pour le template
        $this->context->smarty->assign(array(
            'orderinvoiceupload_invoice' => $invoiceFile,
            'orderinvoiceupload_download_url' => $downloadUrl,
            'orderinvoiceupload_order_reference' => $order->reference,
        ));

        return $this->display(__FILE__, 'views/templates/hook/order_invoice_front.tpl');
    }

    /**
     * Génère l'URL de téléchargement front pour une facture
     *
     * @param int $idOrder ID de la commande
     * @return string URL de téléchargement
     */
    public function getFrontDownloadUrl($idOrder)
    {
        return $this->context->link->getModuleLink(
            $this->name,
            'download',
            array('id_order' => (int) $idOrder),
            true
        );
    }

    /* =========================================================================
     * PAGE DE CONFIGURATION
     * ========================================================================= */

    /**
     * Affiche la page de configuration du module
     *
     * @return string HTML de la page de configuration
     */
    public function getContent()
    {
        $output = '';

        // Traiter le formulaire si soumis
        if (Tools::isSubmit('submitOrderInvoiceUploadConfig')) {
            $output .= $this->processConfigForm();
        }

        // Afficher le formulaire de configuration
        $output .= $this->renderConfigForm();

        return $output;
    }

    /**
     * Traite la soumission du formulaire de configuration
     *
     * @return string Messages de confirmation ou d'erreur
     */
    protected function processConfigForm()
    {
        $errors = array();

        // Récupérer les valeurs
        $frontEnabled = (int) Tools::getValue(self::CONFIG_FRONT_ENABLED);
        $emailEnabled = (int) Tools::getValue(self::CONFIG_EMAIL_ENABLED);
        $maxFileSize = (int) Tools::getValue(self::CONFIG_MAX_FILE_SIZE);

        // Valider la taille maximale
        if ($maxFileSize < 1 || $maxFileSize > 50) {
            $errors[] = $this->l('La taille maximale doit être comprise entre 1 et 50 Mo.');
        }

        // S'il y a des erreurs, les afficher
        if (!empty($errors)) {
            return $this->displayError(implode('<br>', $errors));
        }

        // Enregistrer les valeurs
        Configuration::updateValue(self::CONFIG_FRONT_ENABLED, $frontEnabled);
        Configuration::updateValue(self::CONFIG_EMAIL_ENABLED, $emailEnabled);
        Configuration::updateValue(self::CONFIG_MAX_FILE_SIZE, $maxFileSize);

        return $this->displayConfirmation($this->l('Paramètres enregistrés avec succès.'));
    }

    /**
     * Génère le formulaire de configuration avec HelperForm
     *
     * @return string HTML du formulaire
     */
    protected function renderConfigForm()
    {
        // Définir les champs du formulaire
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Paramètres'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Affichage côté front-office'),
                        'name' => self::CONFIG_FRONT_ENABLED,
                        'desc' => $this->l('Permettre aux clients de voir et télécharger leurs factures depuis leur compte.'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'front_enabled_on',
                                'value' => 1,
                                'label' => $this->l('Oui'),
                            ),
                            array(
                                'id' => 'front_enabled_off',
                                'value' => 0,
                                'label' => $this->l('Non'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Notification client par email'),
                        'name' => self::CONFIG_EMAIL_ENABLED,
                        'desc' => $this->l('Envoyer un email au client lorsqu\'une facture est ajoutée ou remplacée.'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'email_enabled_on',
                                'value' => 1,
                                'label' => $this->l('Oui'),
                            ),
                            array(
                                'id' => 'email_enabled_off',
                                'value' => 0,
                                'label' => $this->l('Non'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Taille maximale des fichiers (Mo)'),
                        'name' => self::CONFIG_MAX_FILE_SIZE,
                        'desc' => $this->l('Taille maximale autorisée pour les fichiers PDF (entre 1 et 50 Mo).'),
                        'class' => 'fixed-width-sm',
                        'suffix' => 'Mo',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Enregistrer'),
                    'class' => 'btn btn-default pull-right',
                ),
            ),
        );

        // Créer l'instance HelperForm
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitOrderInvoiceUploadConfig';

        // Valeurs par défaut
        $helper->fields_value[self::CONFIG_FRONT_ENABLED] = Configuration::get(self::CONFIG_FRONT_ENABLED);
        $helper->fields_value[self::CONFIG_EMAIL_ENABLED] = Configuration::get(self::CONFIG_EMAIL_ENABLED);
        $helper->fields_value[self::CONFIG_MAX_FILE_SIZE] = Configuration::get(self::CONFIG_MAX_FILE_SIZE);

        return $helper->generateForm(array($fields_form));
    }

    /**
     * Retourne la taille maximale autorisée pour les fichiers (en octets)
     *
     * @return int Taille en octets
     */
    public function getMaxFileSize()
    {
        $maxSizeMb = (int) Configuration::get(self::CONFIG_MAX_FILE_SIZE);
        if ($maxSizeMb < 1) {
            $maxSizeMb = self::DEFAULT_MAX_FILE_SIZE;
        }
        return $maxSizeMb * 1024 * 1024;
    }

    /**
     * Installe les templates d'email du module
     *
     * @return bool
     */
    protected function installEmailTemplates()
    {
        $languages = Language::getLanguages(false);
        $sourcePath = dirname(__FILE__) . '/mails/';

        foreach ($languages as $lang) {
            $isoCode = $lang['iso_code'];

            // Utiliser le template FR par défaut si la langue n'existe pas
            $langSource = file_exists($sourcePath . $isoCode) ? $isoCode : 'fr';
            $sourceDir = $sourcePath . $langSource . '/';
            $destDir = _PS_MAIL_DIR_ . $isoCode . '/';

            // S'assurer que le répertoire destination existe
            if (!is_dir($destDir)) {
                continue;
            }

            // Copier les templates si ils existent
            $templates = array(
                'orderinvoiceupload_notification.html',
                'orderinvoiceupload_notification.txt',
            );

            foreach ($templates as $template) {
                if (file_exists($sourceDir . $template)) {
                    copy($sourceDir . $template, $destDir . $template);
                }
            }
        }

        return true;
    }

    /**
     * Envoie un email de notification au client
     *
     * @param int $idOrder ID de la commande
     * @return bool
     */
    protected function sendInvoiceNotificationEmail($idOrder)
    {
        // Vérifier que les emails sont activés
        if (!Configuration::get(self::CONFIG_EMAIL_ENABLED)) {
            return true;
        }

        // Charger la commande
        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            return false;
        }

        // Charger le client
        $customer = new Customer($order->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            return false;
        }

        // Préparer les variables du template
        $templateVars = array(
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{order_name}' => $order->reference,
            '{order_reference}' => $order->reference,
            '{order_link}' => $this->context->link->getPageLink(
                'order-detail',
                true,
                (int) $order->id_lang,
                array('id_order' => $idOrder)
            ),
        );

        // Envoyer l'email
        return Mail::Send(
            (int) $order->id_lang,
            'orderinvoiceupload_notification',
            $this->l('Une facture a été ajoutée à votre commande'),
            $templateVars,
            $customer->email,
            $customer->firstname . ' ' . $customer->lastname,
            null,
            null,
            null,
            null,
            dirname(__FILE__) . '/mails/',
            false,
            (int) $order->id_shop
        );
    }
}
