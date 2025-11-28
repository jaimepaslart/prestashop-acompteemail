<?php
/**
 * Order Invoice Upload - Classe helper pour la gestion des fichiers
 *
 * Cette classe représente une facture téléversée et gère les
 * opérations CRUD sur la table ps_order_invoice_upload.
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Classe OrderInvoiceUploadFile
 *
 * Représente un fichier de facture téléversé pour une commande.
 * Utilise le pattern ObjectModel de PrestaShop pour la compatibilité.
 */
class OrderInvoiceUploadFile extends ObjectModel
{
    /**
     * @var int ID de l'entrée en base de données
     */
    public $id_order_invoice_upload;

    /**
     * @var int ID de la commande associée
     */
    public $id_order;

    /**
     * @var string Nom du fichier stocké sur le serveur (hashé/unique)
     */
    public $file_name;

    /**
     * @var string Nom original du fichier uploadé
     */
    public $original_name;

    /**
     * @var string Type MIME du fichier
     */
    public $mime_type;

    /**
     * @var string Date d'ajout
     */
    public $date_add;

    /**
     * Définition de la structure de la table pour ObjectModel
     *
     * @var array
     */
    public static $definition = array(
        'table' => 'order_invoice_upload',
        'primary' => 'id_order_invoice_upload',
        'fields' => array(
            'id_order' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ),
            'file_name' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 255,
            ),
            'original_name' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 255,
            ),
            'mime_type' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 100,
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ),
        ),
    );

    /**
     * Récupère la facture associée à une commande
     *
     * @param int $idOrder ID de la commande
     * @return OrderInvoiceUploadFile|null Objet facture ou null si non trouvé
     */
    public static function getByOrderId($idOrder)
    {
        $idOrder = (int) $idOrder;
        if ($idOrder <= 0) {
            return null;
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('order_invoice_upload');
        $sql->where('id_order = ' . $idOrder);
        $sql->orderBy('id_order_invoice_upload DESC');
        $sql->limit(1);

        $result = Db::getInstance()->getRow($sql);

        if (!$result) {
            return null;
        }

        $invoiceFile = new self();
        $invoiceFile->id = (int) $result['id_order_invoice_upload'];
        $invoiceFile->id_order_invoice_upload = (int) $result['id_order_invoice_upload'];
        $invoiceFile->id_order = (int) $result['id_order'];
        $invoiceFile->file_name = $result['file_name'];
        $invoiceFile->original_name = $result['original_name'];
        $invoiceFile->mime_type = $result['mime_type'];
        $invoiceFile->date_add = $result['date_add'];

        return $invoiceFile;
    }

    /**
     * Vérifie si une commande a une facture associée
     *
     * @param int $idOrder ID de la commande
     * @return bool True si une facture existe
     */
    public static function hasInvoice($idOrder)
    {
        $idOrder = (int) $idOrder;
        if ($idOrder <= 0) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select('COUNT(*)');
        $sql->from('order_invoice_upload');
        $sql->where('id_order = ' . $idOrder);

        return (int) Db::getInstance()->getValue($sql) > 0;
    }

    /**
     * Supprime toutes les factures d'une commande
     *
     * @param int $idOrder ID de la commande
     * @return bool True si la suppression a réussi
     */
    public static function deleteByOrderId($idOrder)
    {
        $idOrder = (int) $idOrder;
        if ($idOrder <= 0) {
            return false;
        }

        return Db::getInstance()->delete('order_invoice_upload', 'id_order = ' . $idOrder);
    }

    /**
     * Récupère toutes les factures (pour l'administration)
     *
     * @param int $page Page (pagination)
     * @param int $perPage Éléments par page
     * @return array Liste des factures
     */
    public static function getAll($page = 1, $perPage = 50)
    {
        $page = max(1, (int) $page);
        $perPage = max(1, min(100, (int) $perPage));
        $offset = ($page - 1) * $perPage;

        $sql = new DbQuery();
        $sql->select('oiu.*, o.reference as order_reference');
        $sql->from('order_invoice_upload', 'oiu');
        $sql->leftJoin('orders', 'o', 'o.id_order = oiu.id_order');
        $sql->orderBy('oiu.date_add DESC');
        $sql->limit($perPage, $offset);

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Compte le nombre total de factures
     *
     * @return int Nombre de factures
     */
    public static function countAll()
    {
        $sql = new DbQuery();
        $sql->select('COUNT(*)');
        $sql->from('order_invoice_upload');

        return (int) Db::getInstance()->getValue($sql);
    }

    /**
     * Sauvegarde l'objet en base de données
     *
     * @param bool $nullValues Force l'insertion des valeurs NULL
     * @param bool $autoDate Mise à jour automatique des dates
     * @return bool True si la sauvegarde a réussi
     */
    public function save($nullValues = false, $autoDate = true)
    {
        // Si l'objet existe déjà, on fait un UPDATE
        if ($this->id) {
            return $this->update($nullValues);
        }

        // Sinon, on fait un INSERT
        return $this->add($nullValues, $autoDate);
    }

    /**
     * Ajoute une nouvelle entrée en base de données
     *
     * @param bool $nullValues Force l'insertion des valeurs NULL
     * @param bool $autoDate Mise à jour automatique des dates
     * @return bool True si l'ajout a réussi
     */
    public function add($nullValues = false, $autoDate = true)
    {
        // Définir la date d'ajout si non définie
        if (empty($this->date_add)) {
            $this->date_add = date('Y-m-d H:i:s');
        }

        $result = Db::getInstance()->insert('order_invoice_upload', array(
            'id_order' => (int) $this->id_order,
            'file_name' => pSQL($this->file_name),
            'original_name' => pSQL($this->original_name),
            'mime_type' => pSQL($this->mime_type),
            'date_add' => pSQL($this->date_add),
        ));

        if ($result) {
            $this->id = (int) Db::getInstance()->Insert_ID();
            $this->id_order_invoice_upload = $this->id;
        }

        return $result;
    }

    /**
     * Met à jour une entrée existante en base de données
     *
     * @param bool $nullValues Force l'insertion des valeurs NULL
     * @return bool True si la mise à jour a réussi
     */
    public function update($nullValues = false)
    {
        if (!$this->id) {
            return false;
        }

        return Db::getInstance()->update('order_invoice_upload', array(
            'id_order' => (int) $this->id_order,
            'file_name' => pSQL($this->file_name),
            'original_name' => pSQL($this->original_name),
            'mime_type' => pSQL($this->mime_type),
        ), 'id_order_invoice_upload = ' . (int) $this->id);
    }

    /**
     * Supprime l'entrée de la base de données
     *
     * @return bool True si la suppression a réussi
     */
    public function delete()
    {
        if (!$this->id) {
            return false;
        }

        return Db::getInstance()->delete('order_invoice_upload', 'id_order_invoice_upload = ' . (int) $this->id);
    }

    /**
     * Formate la date d'ajout pour l'affichage
     *
     * @param string $format Format de date (PHP date())
     * @return string Date formatée
     */
    public function getFormattedDate($format = 'd/m/Y H:i')
    {
        if (empty($this->date_add)) {
            return '';
        }

        $timestamp = strtotime($this->date_add);
        if ($timestamp === false) {
            return $this->date_add;
        }

        return date($format, $timestamp);
    }

    /**
     * Récupère la taille du fichier en octets
     *
     * @param string $uploadDir Répertoire d'upload
     * @return int Taille en octets ou 0 si le fichier n'existe pas
     */
    public function getFileSize($uploadDir)
    {
        $filePath = $uploadDir . $this->file_name;
        if (!file_exists($filePath)) {
            return 0;
        }

        return filesize($filePath);
    }

    /**
     * Formate la taille du fichier pour l'affichage
     *
     * @param string $uploadDir Répertoire d'upload
     * @return string Taille formatée (ex: "1.5 Mo")
     */
    public function getFormattedFileSize($uploadDir)
    {
        $size = $this->getFileSize($uploadDir);
        if ($size === 0) {
            return 'N/A';
        }

        $units = array('o', 'Ko', 'Mo', 'Go');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;

        return number_format($size / pow(1024, $power), 2, ',', ' ') . ' ' . $units[$power];
    }
}
