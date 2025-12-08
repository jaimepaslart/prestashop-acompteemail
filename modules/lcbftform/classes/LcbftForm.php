<?php
/**
 * Classe LcbftFormModel - ObjectModel pour les formulaires LCB-FT
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * ObjectModel pour stocker les formulaires LCB-FT
 */
class LcbftFormModel extends ObjectModel
{
    /**
     * @var int ID du formulaire
     */
    public $id_lcbft_form;

    /**
     * @var int ID du panier
     */
    public $id_cart;

    /**
     * @var int ID de la commande (nullable)
     */
    public $id_order;

    /**
     * @var int ID du client
     */
    public $id_customer;

    // ====== COORDONNEES ======
    /**
     * @var string Nom de l'entreprise
     */
    public $entreprise;

    /**
     * @var string Civilite (Mme / M.)
     */
    public $civilite;

    /**
     * @var string Nom
     */
    public $nom;

    /**
     * @var string Prenom
     */
    public $prenom;

    /**
     * @var string Profession
     */
    public $profession;

    /**
     * @var string Identifiant de connexion
     */
    public $identifiant_connexion;

    /**
     * @var string Adresse
     */
    public $adresse;

    /**
     * @var string Code postal
     */
    public $code_postal;

    /**
     * @var string Ville
     */
    public $ville;

    /**
     * @var string Pays
     */
    public $pays;

    /**
     * @var string Email
     */
    public $email;

    /**
     * @var string Telephone
     */
    public $telephone;

    // ====== INFORMATIONS PATRIMONIALES ======
    /**
     * @var string Remunerations brutes annuelles
     */
    public $remunerations_annuelles;

    /**
     * @var string Estimation patrimoine (less_300k, 300k_720k, 720k_1500k, more_1500k)
     */
    public $patrimoine_estimation;

    /**
     * @var string Precision si patrimoine > 1.5M
     */
    public $patrimoine_precision;

    /**
     * @var int Soumis a l'IFI (0/1)
     */
    public $soumis_ifi;

    // ====== ORIGINE DES FONDS ======
    /**
     * @var string Donnees JSON des origines des fonds (checkboxes)
     */
    public $origine_fonds_json;

    /**
     * @var string Precision vente immobiliere
     */
    public $origine_vente_immo_detail;

    /**
     * @var string Precision cession actifs
     */
    public $origine_cession_detail;

    /**
     * @var string Precision epargne
     */
    public $origine_epargne_detail;

    /**
     * @var string Precision autre origine
     */
    public $origine_autre_detail;

    /**
     * @var string Nature justificatif (pour versements > 15000 EUR)
     */
    public $justificatif_origine_fonds;

    // ====== COMMENTAIRES ======
    /**
     * @var string Donnees JSON des commentaires (tableau date/montant/origine)
     */
    public $commentaires_json;

    // ====== JUSTIFICATIFS FOURNIS ======
    /**
     * @var string Donnees JSON des justificatifs coches
     */
    public $justificatifs_json;

    /**
     * @var string Precision autre justificatif
     */
    public $justificatif_autre_detail;

    // ====== SIGNATURE ======
    /**
     * @var string Lieu de signature
     */
    public $lieu_signature;

    /**
     * @var string Date de signature format FR
     */
    public $date_signature;

    /**
     * @var int Case "lu et approuve" cochee
     */
    public $acknowledged;

    /**
     * @var string Nom complet de la signature
     */
    public $signature_name;

    /**
     * @var string Date/heure horodatage signature
     */
    public $signed_at;

    // ====== METADONNEES ======
    /**
     * @var string Date de creation
     */
    public $date_add;

    /**
     * @var string Date de modification
     */
    public $date_upd;

    /**
     * Definition de la table et des champs
     *
     * @var array
     */
    public static $definition = array(
        'table' => 'lcbft_form',
        'primary' => 'id_lcbft_form',
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'allow_null' => true),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),

            // Coordonnees
            'entreprise' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'civilite' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 10),
            'nom' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'size' => 255, 'required' => true),
            'prenom' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'size' => 255, 'required' => true),
            'profession' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'identifiant_connexion' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'adresse' => array('type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 500),
            'code_postal' => array('type' => self::TYPE_STRING, 'validate' => 'isPostCode', 'size' => 20),
            'ville' => array('type' => self::TYPE_STRING, 'validate' => 'isCityName', 'size' => 255),
            'pays' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 255),
            'telephone' => array('type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32),

            // Informations patrimoniales
            'remunerations_annuelles' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'patrimoine_estimation' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 50),
            'patrimoine_precision' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'soumis_ifi' => array('type' => self::TYPE_INT, 'validate' => 'isBool'),

            // Origine des fonds
            'origine_fonds_json' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'origine_vente_immo_detail' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 500),
            'origine_cession_detail' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 500),
            'origine_epargne_detail' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 500),
            'origine_autre_detail' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 500),
            'justificatif_origine_fonds' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),

            // Commentaires
            'commentaires_json' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),

            // Justificatifs
            'justificatifs_json' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'justificatif_autre_detail' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 500),

            // Signature
            'lieu_signature' => array('type' => self::TYPE_STRING, 'validate' => 'isCityName', 'size' => 255),
            'date_signature' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 20),
            'acknowledged' => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'signature_name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'signed_at' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),

            // Metadonnees
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    /**
     * Recupere un formulaire par ID de panier
     *
     * @param int $idCart
     * @return LcbftFormModel|null
     */
    public static function getByCartId($idCart)
    {
        $idCart = (int) $idCart;
        if ($idCart <= 0) {
            return null;
        }

        $sql = new DbQuery();
        $sql->select('id_lcbft_form');
        $sql->from('lcbft_form');
        $sql->where('id_cart = ' . $idCart);
        $sql->orderBy('id_lcbft_form DESC');

        $id = Db::getInstance()->getValue($sql);

        if ($id) {
            return new LcbftFormModel((int) $id);
        }

        return null;
    }

    /**
     * Recupere un formulaire par ID de commande
     *
     * @param int $idOrder
     * @return LcbftFormModel|null
     */
    public static function getByOrderId($idOrder)
    {
        $idOrder = (int) $idOrder;
        if ($idOrder <= 0) {
            return null;
        }

        $sql = new DbQuery();
        $sql->select('id_lcbft_form');
        $sql->from('lcbft_form');
        $sql->where('id_order = ' . $idOrder);
        $sql->orderBy('id_lcbft_form DESC');

        $id = Db::getInstance()->getValue($sql);

        if ($id) {
            return new LcbftFormModel((int) $id);
        }

        return null;
    }

    /**
     * Decode les origines des fonds depuis JSON
     *
     * @return array
     */
    public function getOrigineFonds()
    {
        if (empty($this->origine_fonds_json)) {
            return array();
        }
        $decoded = json_decode($this->origine_fonds_json, true);
        return is_array($decoded) ? $decoded : array();
    }

    /**
     * Encode les origines des fonds en JSON
     *
     * @param array $data
     * @return void
     */
    public function setOrigineFonds($data)
    {
        $this->origine_fonds_json = json_encode($data);
    }

    /**
     * Decode les commentaires depuis JSON
     *
     * @return array
     */
    public function getCommentaires()
    {
        if (empty($this->commentaires_json)) {
            return array();
        }
        $decoded = json_decode($this->commentaires_json, true);
        return is_array($decoded) ? $decoded : array();
    }

    /**
     * Encode les commentaires en JSON
     *
     * @param array $data
     * @return void
     */
    public function setCommentaires($data)
    {
        $this->commentaires_json = json_encode($data);
    }

    /**
     * Decode les justificatifs depuis JSON
     *
     * @return array
     */
    public function getJustificatifs()
    {
        if (empty($this->justificatifs_json)) {
            return array();
        }
        $decoded = json_decode($this->justificatifs_json, true);
        return is_array($decoded) ? $decoded : array();
    }

    /**
     * Encode les justificatifs en JSON
     *
     * @param array $data
     * @return void
     */
    public function setJustificatifs($data)
    {
        $this->justificatifs_json = json_encode($data);
    }

    /**
     * Genere la signature electronique
     *
     * @return void
     */
    public function generateSignature()
    {
        if ($this->acknowledged && !empty($this->prenom) && !empty($this->nom)) {
            $this->signature_name = trim($this->prenom) . ' ' . trim($this->nom);
            $this->signed_at = date('Y-m-d H:i:s');
        }
    }

    /**
     * Retourne la signature formatee pour affichage
     *
     * @return string
     */
    public function getFormattedSignature()
    {
        if (empty($this->signature_name) || empty($this->signed_at)) {
            return '';
        }

        $date = new DateTime($this->signed_at);
        return sprintf(
            'Signé électroniquement par %s le %s à %s',
            $this->signature_name,
            $date->format('d/m/Y'),
            $date->format('H:i')
        );
    }

    /**
     * Verifie si le formulaire est complet et valide
     *
     * @return bool
     */
    public function isComplete()
    {
        // Champs obligatoires
        if (empty($this->nom) || empty($this->prenom)) {
            return false;
        }

        // Case de reconnaissance
        if (!$this->acknowledged) {
            return false;
        }

        // Signature
        if (empty($this->signature_name) || empty($this->signed_at)) {
            return false;
        }

        return true;
    }

    /**
     * Retourne le libelle du patrimoine
     *
     * @return string
     */
    public function getPatrimoineLabel()
    {
        $labels = array(
            'less_300k' => 'Moins de 300 000 €',
            '300k_720k' => 'De 300 000 € à 720 000 €',
            '720k_1500k' => 'De 720 000 € à 1,5 million €',
            'more_1500k' => 'Plus de 1,5 million €',
        );

        if (isset($labels[$this->patrimoine_estimation])) {
            $label = $labels[$this->patrimoine_estimation];
            if ($this->patrimoine_estimation === 'more_1500k' && !empty($this->patrimoine_precision)) {
                $label .= ' (' . $this->patrimoine_precision . ')';
            }
            return $label;
        }

        return '';
    }
}
