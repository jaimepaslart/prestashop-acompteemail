/**
 * Order Invoice Upload - Installation SQL
 *
 * Création de la table pour stocker les factures téléversées.
 * Une seule facture par commande (gérée au niveau applicatif).
 *
 * PREFIX_ sera remplacé par le préfixe de table PrestaShop (ex: ps_)
 */

CREATE TABLE IF NOT EXISTS `PREFIX_order_invoice_upload` (
    `id_order_invoice_upload` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_order` INT(11) UNSIGNED NOT NULL,
    `file_name` VARCHAR(255) NOT NULL COMMENT 'Nom du fichier stocké sur le serveur (hashé/unique)',
    `original_name` VARCHAR(255) NOT NULL COMMENT 'Nom original du fichier uploadé',
    `mime_type` VARCHAR(100) NOT NULL DEFAULT 'application/pdf',
    `date_add` DATETIME NOT NULL,
    PRIMARY KEY (`id_order_invoice_upload`),
    KEY `idx_id_order` (`id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
