<?php
/**
 * Controleur Front - Telechargement PDF du formulaire LCB-FT
 *
 * Permet au client de telecharger son formulaire LCB-FT signe.
 * Supporte les clients connectes ET les invites (via token securise).
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../classes/LcbftForm.php';
require_once dirname(__FILE__) . '/../../classes/LcbftPdfGenerator.php';

class LcbftformDownloadModuleFrontController extends ModuleFrontController
{
    /**
     * Initialisation du controleur
     * Ne pas rediriger vers login - on gere l'acces dans postProcess
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Traitement de la requete
     */
    public function postProcess()
    {
        $idOrder = (int) Tools::getValue('id_order', 0);
        $secureKey = Tools::getValue('key', '');

        if ($idOrder <= 0) {
            $this->errors[] = $this->module->l('Commande invalide.', 'download');
            return;
        }

        // Charger la commande
        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            $this->errors[] = $this->module->l('Commande introuvable.', 'download');
            return;
        }

        // SECURITE : Verifier l'acces a la commande
        $hasAccess = false;

        // Option 1 : Client connecte et proprietaire de la commande
        if ($this->context->customer && $this->context->customer->isLogged()) {
            if ((int) $order->id_customer === (int) $this->context->customer->id) {
                $hasAccess = true;
            }
        }

        // Option 2 : Acces via token securise (pour invites)
        if (!$hasAccess && !empty($secureKey)) {
            // Verifier le secure_key du client associe a la commande
            $customer = new Customer((int) $order->id_customer);
            if (Validate::isLoadedObject($customer) && $customer->secure_key === $secureKey) {
                $hasAccess = true;
            }
        }

        // Option 3 : Verifier si le panier de la commande est dans la session actuelle
        if (!$hasAccess && $this->context->cart) {
            if ((int) $order->id_cart === (int) $this->context->cart->id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            $this->errors[] = $this->module->l('Vous n\'êtes pas autorisé à accéder à ce document.', 'download');
            return;
        }

        // Recuperer le formulaire LCB-FT
        $form = LcbftFormModel::getByOrderId($idOrder);

        if (!$form) {
            $this->errors[] = $this->module->l('Aucun formulaire LCB-FT trouvé pour cette commande.', 'download');
            return;
        }

        // Verifier que le formulaire est signe
        if (!$form->acknowledged || empty($form->signature_name)) {
            $this->errors[] = $this->module->l('Le formulaire n\'a pas été signé.', 'download');
            return;
        }

        // Generer et telecharger le PDF
        try {
            $generator = new LcbftPdfGenerator($this->module);
            $generator->generateAndDownload($form, $order);
            exit;
        } catch (Exception $e) {
            $this->errors[] = $this->module->l('Erreur lors de la génération du PDF.', 'download');
            PrestaShopLogger::addLog('LcbftForm PDF Error: ' . $e->getMessage(), 3);
        }
    }

    /**
     * Affichage en cas d'erreur
     */
    public function initContent()
    {
        parent::initContent();

        if (!empty($this->errors)) {
            $this->context->smarty->assign(array(
                'errors' => $this->errors,
            ));
            $this->setTemplate('module:lcbftform/views/templates/front/error.tpl');
        }
    }
}
