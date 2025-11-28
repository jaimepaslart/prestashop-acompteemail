<?php
/**
 * Order Invoice Upload - Contrôleur Front pour le téléchargement
 *
 * Ce contrôleur permet aux clients connectés de télécharger
 * les factures associées à leurs commandes.
 *
 * Sécurité :
 * - Vérifie que le client est connecté
 * - Vérifie que la commande appartient au client
 * - Ne permet que le téléchargement (lecture seule)
 * - N'expose jamais le chemin physique du fichier
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Charger la classe helper
require_once _PS_MODULE_DIR_ . 'orderinvoiceupload/classes/OrderInvoiceUploadFile.php';

/**
 * Classe OrderinvoiceuploadDownloadModuleFrontController
 *
 * Contrôleur front pour le téléchargement sécurisé des factures.
 * URL : /module/orderinvoiceupload/download?id_order=XXX
 */
class OrderinvoiceuploadDownloadModuleFrontController extends ModuleFrontController
{
    /**
     * @var bool Authentification requise
     */
    public $auth = true;

    /**
     * @var bool Page SSL obligatoire
     */
    public $ssl = true;

    /**
     * Initialisation du contrôleur
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        // Vérifier que le client est connecté
        if (!$this->context->customer->isLogged()) {
            Tools::redirect('index.php?controller=authentication');
        }
    }

    /**
     * Traitement de la requête
     *
     * @return void
     */
    public function initContent()
    {
        parent::initContent();

        // Récupérer l'ID de la commande
        $idOrder = (int) Tools::getValue('id_order');

        if ($idOrder <= 0) {
            $this->displayError($this->trans('Commande invalide.', array(), 'Modules.Orderinvoiceupload.Shop'));
            return;
        }

        // Charger la commande
        $order = new Order($idOrder);

        if (!Validate::isLoadedObject($order)) {
            $this->displayError($this->trans('Commande non trouvée.', array(), 'Modules.Orderinvoiceupload.Shop'));
            return;
        }

        // SÉCURITÉ : Vérifier que la commande appartient au client connecté
        if ((int) $order->id_customer !== (int) $this->context->customer->id) {
            $this->displayError($this->trans('Vous n\'êtes pas autorisé à accéder à cette commande.', array(), 'Modules.Orderinvoiceupload.Shop'));
            return;
        }

        // Récupérer la facture associée
        $invoiceFile = OrderInvoiceUploadFile::getByOrderId($idOrder);

        if (!$invoiceFile) {
            $this->displayError($this->trans('Aucune facture associée à cette commande.', array(), 'Modules.Orderinvoiceupload.Shop'));
            return;
        }

        // Vérifier que le fichier existe physiquement
        $filePath = $this->module->getUploadDir() . $invoiceFile->file_name;

        if (!file_exists($filePath)) {
            $this->displayError($this->trans('Le fichier de facture est introuvable.', array(), 'Modules.Orderinvoiceupload.Shop'));
            return;
        }

        // Envoyer le fichier au client
        $this->sendFile($filePath, $invoiceFile->original_name, $invoiceFile->mime_type);
    }

    /**
     * Envoie le fichier au navigateur
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

        // Nettoyer le nom de fichier pour les headers HTTP
        $safeFileName = $this->sanitizeFilename($fileName);

        // Définir les headers
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $safeFileName . '"');
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
     * Affiche une page d'erreur
     *
     * @param string $message Message d'erreur
     * @return void
     */
    protected function displayError($message)
    {
        $this->context->smarty->assign(array(
            'error_message' => $message,
        ));

        $this->setTemplate('module:orderinvoiceupload/views/templates/front/error.tpl');
    }
}
