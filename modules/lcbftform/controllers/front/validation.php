<?php
/**
 * Controleur Front - Validation du formulaire LCB-FT
 *
 * Gere la soumission AJAX du formulaire depuis le checkout.
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../classes/LcbftForm.php';

class LcbftformValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @var bool Desactiver la colonne de gauche
     */
    public $display_column_left = false;

    /**
     * @var bool Desactiver la colonne de droite
     */
    public $display_column_right = false;

    /**
     * Initialisation du controleur
     */
    public function init()
    {
        parent::init();

        // Verifier que c'est une requete AJAX
        if (!$this->ajax) {
            $this->ajax = true;
        }
    }

    /**
     * Traitement de la requete POST
     */
    public function postProcess()
    {
        // Verifier que le client est connecte
        if (!$this->context->customer->isLogged()) {
            $this->ajaxResponse(false, $this->module->l('Vous devez être connecté pour remplir ce formulaire.', 'validation'));
            return;
        }

        // Verifier le panier
        $idCart = (int) $this->context->cart->id;
        if ($idCart <= 0) {
            $this->ajaxResponse(false, $this->module->l('Panier invalide.', 'validation'));
            return;
        }

        $idCustomer = (int) $this->context->customer->id;

        // Determiner l'action
        $action = Tools::getValue('action', 'save');

        switch ($action) {
            case 'save':
                $this->processSave($idCart, $idCustomer);
                break;

            case 'check':
                $this->processCheck($idCart);
                break;

            default:
                $this->ajaxResponse(false, $this->module->l('Action inconnue.', 'validation'));
        }
    }

    /**
     * Traitement de la sauvegarde du formulaire
     *
     * @param int $idCart
     * @param int $idCustomer
     */
    protected function processSave($idCart, $idCustomer)
    {
        // Recuperer ou creer le formulaire
        $form = LcbftFormModel::getByCartId($idCart);
        if (!$form) {
            $form = new LcbftFormModel();
            $form->id_cart = $idCart;
            $form->id_customer = $idCustomer;
            $form->date_add = date('Y-m-d H:i:s');
        }

        // Valider les champs obligatoires
        $errors = array();

        // Nom et prenom (obligatoires)
        $nom = trim(Tools::getValue('nom', ''));
        $prenom = trim(Tools::getValue('prenom', ''));

        if (empty($nom)) {
            $errors[] = $this->module->l('Le nom est obligatoire.', 'validation');
        }
        if (empty($prenom)) {
            $errors[] = $this->module->l('Le prénom est obligatoire.', 'validation');
        }

        // Verifier la case de reconnaissance
        $acknowledged = (int) Tools::getValue('acknowledged', 0);
        if (!$acknowledged) {
            $errors[] = $this->module->l('Vous devez cocher la case de reconnaissance pour valider le formulaire.', 'validation');
        }

        // Si erreurs, retourner
        if (!empty($errors)) {
            $this->ajaxResponse(false, implode('<br>', $errors), array('errors' => $errors));
            return;
        }

        // Remplir les coordonnees
        $form->entreprise = $this->sanitizeString(Tools::getValue('entreprise', ''));
        $form->civilite = $this->sanitizeString(Tools::getValue('civilite', ''));
        $form->nom = $this->sanitizeString($nom);
        $form->prenom = $this->sanitizeString($prenom);
        $form->profession = $this->sanitizeString(Tools::getValue('profession', ''));
        $form->identifiant_connexion = $this->sanitizeString(Tools::getValue('identifiant_connexion', ''));
        $form->adresse = $this->sanitizeString(Tools::getValue('adresse', ''));
        $form->code_postal = $this->sanitizeString(Tools::getValue('code_postal', ''));
        $form->ville = $this->sanitizeString(Tools::getValue('ville', ''));
        $form->pays = $this->sanitizeString(Tools::getValue('pays', ''));
        $form->email = $this->sanitizeEmail(Tools::getValue('email', ''));
        $form->telephone = $this->sanitizeString(Tools::getValue('telephone', ''));

        // Informations patrimoniales
        $form->remunerations_annuelles = $this->sanitizeString(Tools::getValue('remunerations_annuelles', ''));
        $form->patrimoine_estimation = $this->sanitizeString(Tools::getValue('patrimoine_estimation', ''));
        $form->patrimoine_precision = $this->sanitizeString(Tools::getValue('patrimoine_precision', ''));
        $form->soumis_ifi = (int) Tools::getValue('soumis_ifi', 0);

        // Origine des fonds
        $origineFonds = Tools::getValue('origine_fonds', array());
        if (!is_array($origineFonds)) {
            $origineFonds = array();
        }
        $form->setOrigineFonds($origineFonds);

        $form->origine_vente_immo_detail = $this->sanitizeString(Tools::getValue('origine_vente_immo_detail', ''));
        $form->origine_cession_detail = $this->sanitizeString(Tools::getValue('origine_cession_detail', ''));
        $form->origine_epargne_detail = $this->sanitizeString(Tools::getValue('origine_epargne_detail', ''));
        $form->origine_autre_detail = $this->sanitizeString(Tools::getValue('origine_autre_detail', ''));
        $form->justificatif_origine_fonds = $this->sanitizeText(Tools::getValue('justificatif_origine_fonds', ''));

        // Commentaires (tableau date/montant/origine)
        $commentaires = Tools::getValue('commentaires', array());
        if (!is_array($commentaires)) {
            $commentaires = array();
        }
        // Nettoyer les lignes vides
        $cleanCommentaires = array();
        foreach ($commentaires as $ligne) {
            if (is_array($ligne) && (!empty($ligne['date']) || !empty($ligne['montant']) || !empty($ligne['origine']))) {
                $cleanCommentaires[] = array(
                    'date' => $this->sanitizeString(isset($ligne['date']) ? $ligne['date'] : ''),
                    'montant' => $this->sanitizeString(isset($ligne['montant']) ? $ligne['montant'] : ''),
                    'origine' => $this->sanitizeString(isset($ligne['origine']) ? $ligne['origine'] : ''),
                );
            }
        }
        $form->setCommentaires($cleanCommentaires);

        // Justificatifs fournis
        $justificatifs = Tools::getValue('justificatifs', array());
        if (!is_array($justificatifs)) {
            $justificatifs = array();
        }
        $form->setJustificatifs($justificatifs);
        $form->justificatif_autre_detail = $this->sanitizeString(Tools::getValue('justificatif_autre_detail', ''));

        // Signature
        $form->lieu_signature = $this->sanitizeString(Tools::getValue('lieu_signature', ''));
        $form->date_signature = $this->sanitizeString(Tools::getValue('date_signature', ''));
        $form->acknowledged = $acknowledged;

        // Generer la signature si acknowledge
        if ($form->acknowledged) {
            $form->generateSignature();
        }

        // Mettre a jour la date de modification
        $form->date_upd = date('Y-m-d H:i:s');

        // Sauvegarder
        if (!$form->save()) {
            $this->ajaxResponse(false, $this->module->l('Erreur lors de l\'enregistrement du formulaire.', 'validation'));
            return;
        }

        // Retourner succes avec les infos de signature
        $data = array(
            'id_form' => $form->id,
            'is_complete' => $form->isComplete(),
            'signature' => $form->getFormattedSignature(),
        );

        $this->ajaxResponse(true, $this->module->l('Formulaire LCB-FT enregistré et signé avec succès.', 'validation'), $data);
    }

    /**
     * Verification du statut du formulaire
     *
     * @param int $idCart
     */
    protected function processCheck($idCart)
    {
        $form = LcbftFormModel::getByCartId($idCart);

        if (!$form) {
            $this->ajaxResponse(false, $this->module->l('Aucun formulaire trouvé.', 'validation'), array(
                'is_complete' => false,
                'exists' => false,
            ));
            return;
        }

        $this->ajaxResponse(true, '', array(
            'is_complete' => $form->isComplete(),
            'exists' => true,
            'acknowledged' => (bool) $form->acknowledged,
            'signature' => $form->getFormattedSignature(),
        ));
    }

    /**
     * Envoie une reponse JSON
     *
     * @param bool $success
     * @param string $message
     * @param array $data
     */
    protected function ajaxResponse($success, $message = '', $data = array())
    {
        $response = array(
            'success' => $success,
            'message' => $message,
        );

        if (!empty($data)) {
            $response = array_merge($response, $data);
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    /**
     * Sanitize une chaine de caracteres
     *
     * @param string $value
     * @return string
     */
    protected function sanitizeString($value)
    {
        if (!is_string($value)) {
            return '';
        }
        return strip_tags(trim($value));
    }

    /**
     * Sanitize un email
     *
     * @param string $value
     * @return string
     */
    protected function sanitizeEmail($value)
    {
        $value = $this->sanitizeString($value);
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : '';
    }

    /**
     * Sanitize un texte long
     *
     * @param string $value
     * @return string
     */
    protected function sanitizeText($value)
    {
        if (!is_string($value)) {
            return '';
        }
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
}
