<?php
/**
 * Order Invoice Upload - Contrôleur Admin
 *
 * Ce contrôleur gère le téléchargement sécurisé des factures.
 * Il vérifie les droits d'accès et le token admin avant de servir les fichiers.
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Charger la classe helper si nécessaire
require_once _PS_MODULE_DIR_ . 'orderinvoiceupload/classes/OrderInvoiceUploadFile.php';

/**
 * Classe AdminOrderInvoiceUploadController
 *
 * Contrôleur d'administration pour la gestion des factures téléversées.
 * Actions disponibles :
 * - download : Télécharger une facture
 * - delete : Supprimer une facture
 * - list : Lister toutes les factures (optionnel)
 */
class AdminOrderInvoiceUploadController extends ModuleAdminController
{
    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'order_invoice_upload';
        $this->identifier = 'id_order_invoice_upload';
        $this->className = 'OrderInvoiceUploadFile';

        parent::__construct();

        // Vérifier que le module est actif
        if (!Module::isEnabled('orderinvoiceupload')) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
        }
    }

    /**
     * Initialisation du contrôleur
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        // Traiter les actions
        $action = Tools::getValue('action');

        switch ($action) {
            case 'download':
                $this->processDownload();
                break;

            case 'delete':
                $this->processDeleteInvoice();
                break;

            default:
                // Par défaut, rediriger vers le dashboard
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
                break;
        }
    }

    /**
     * Traite le téléchargement d'une facture
     *
     * Cette méthode :
     * - Vérifie l'ID de la commande
     * - Vérifie que la facture existe
     * - Vérifie que le fichier existe physiquement
     * - Envoie le fichier au navigateur
     *
     * @return void
     */
    protected function processDownload()
    {
        $idOrder = (int) Tools::getValue('id_order');

        // Vérifier l'ID de la commande
        if ($idOrder <= 0) {
            $this->displayError('ID de commande invalide.');
            return;
        }

        // Vérifier que la commande existe
        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            $this->displayError('Commande non trouvée.');
            return;
        }

        // Récupérer la facture
        $invoiceFile = OrderInvoiceUploadFile::getByOrderId($idOrder);
        if (!$invoiceFile) {
            $this->displayError('Aucune facture associée à cette commande.');
            return;
        }

        // Charger le module pour accéder aux méthodes
        $module = Module::getInstanceByName('orderinvoiceupload');
        if (!$module) {
            $this->displayError('Module non trouvé.');
            return;
        }

        // Vérifier que le fichier existe
        $filePath = $module->getUploadDir() . $invoiceFile->file_name;
        if (!file_exists($filePath)) {
            $this->displayError('Fichier de facture non trouvé sur le serveur.');
            return;
        }

        // Envoyer le fichier au navigateur
        $this->sendFile($filePath, $invoiceFile->original_name, $invoiceFile->mime_type);
    }

    /**
     * Traite la suppression d'une facture
     *
     * @return void
     */
    protected function processDeleteInvoice()
    {
        $idOrder = (int) Tools::getValue('id_order');

        // Vérifier l'ID de la commande
        if ($idOrder <= 0) {
            $this->errors[] = $this->l('ID de commande invalide.');
            return;
        }

        // Récupérer la facture
        $invoiceFile = OrderInvoiceUploadFile::getByOrderId($idOrder);
        if (!$invoiceFile) {
            $this->errors[] = $this->l('Aucune facture à supprimer.');
            return;
        }

        // Charger le module
        $module = Module::getInstanceByName('orderinvoiceupload');
        if (!$module) {
            $this->errors[] = $this->l('Module non trouvé.');
            return;
        }

        // Supprimer le fichier physique
        $filePath = $module->getUploadDir() . $invoiceFile->file_name;
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        // Supprimer l'entrée en BDD
        if ($invoiceFile->delete()) {
            $this->confirmations[] = $this->l('Facture supprimée avec succès.');
        } else {
            $this->errors[] = $this->l('Erreur lors de la suppression de la facture.');
        }

        // Rediriger vers la page de la commande
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminOrders')
            . '&vieworder&id_order=' . $idOrder
            . '&conf=1'
        );
    }

    /**
     * Envoie un fichier au navigateur
     *
     * @param string $filePath Chemin complet du fichier
     * @param string $fileName Nom du fichier pour le téléchargement
     * @param string $mimeType Type MIME du fichier
     * @return void
     */
    protected function sendFile($filePath, $fileName, $mimeType)
    {
        // Nettoyer le buffer de sortie
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Définir les headers
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $this->sanitizeFilename($fileName) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');

        // Envoyer le fichier
        readfile($filePath);
        exit;
    }

    /**
     * Nettoie un nom de fichier pour les headers HTTP
     *
     * @param string $filename Nom de fichier
     * @return string Nom de fichier nettoyé
     */
    protected function sanitizeFilename($filename)
    {
        // Supprimer les caractères potentiellement dangereux
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Limiter la longueur
        if (strlen($filename) > 200) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $basename = pathinfo($filename, PATHINFO_FILENAME);
            $filename = substr($basename, 0, 200 - strlen($extension) - 1) . '.' . $extension;
        }

        return $filename;
    }

    /**
     * Affiche une erreur et termine l'exécution
     *
     * @param string $message Message d'erreur
     * @return void
     */
    protected function displayError($message)
    {
        // Pour les téléchargements, on affiche une page d'erreur simple
        header('HTTP/1.1 404 Not Found');
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><title>Erreur</title></head>';
        echo '<body style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">';
        echo '<h1>Erreur</h1>';
        echo '<p>' . htmlspecialchars($message) . '</p>';
        echo '<p><a href="javascript:history.back()">Retour</a></p>';
        echo '</body></html>';
        exit;
    }

    /**
     * Vérification des permissions (appelé automatiquement par PrestaShop)
     *
     * @param string $action Action demandée
     * @param Employee|null $employee Employé
     * @return bool True si autorisé
     */
    public function access($action, $employee = null)
    {
        // Vérifier les permissions de base
        if (!parent::access($action, $employee)) {
            return false;
        }

        // Vérifier également les permissions sur AdminOrders
        // car ce module est lié à la gestion des commandes
        return $this->context->employee->hasAccess(
            'AdminOrders',
            Profile::PROFILE_VIEW
        );
    }
}
