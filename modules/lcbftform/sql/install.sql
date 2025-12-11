-- Installation de la table LCB-FT Form
-- Module lcbftform pour PrestaShop 1.7.6.5

CREATE TABLE IF NOT EXISTS `PREFIX_lcbft_form` (
    `id_lcbft_form` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cart` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `id_order` INT(11) UNSIGNED DEFAULT NULL,
    `id_customer` INT(11) UNSIGNED NOT NULL,

    -- Coordonnees
    `entreprise` VARCHAR(255) DEFAULT NULL,
    `civilite` VARCHAR(10) DEFAULT NULL,
    `nom` VARCHAR(255) NOT NULL,
    `prenom` VARCHAR(255) NOT NULL,
    `profession` VARCHAR(255) DEFAULT NULL,
    `identifiant_connexion` VARCHAR(255) DEFAULT NULL,
    `adresse` VARCHAR(500) DEFAULT NULL,
    `code_postal` VARCHAR(20) DEFAULT NULL,
    `ville` VARCHAR(255) DEFAULT NULL,
    `pays` VARCHAR(255) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `telephone` VARCHAR(32) DEFAULT NULL,

    -- Informations patrimoniales
    `remunerations_annuelles` VARCHAR(255) DEFAULT NULL,
    `patrimoine_estimation` VARCHAR(50) DEFAULT NULL,
    `patrimoine_precision` VARCHAR(255) DEFAULT NULL,
    `soumis_ifi` TINYINT(1) UNSIGNED DEFAULT NULL,

    -- Origine des fonds (JSON)
    `origine_fonds_json` TEXT DEFAULT NULL,
    `origine_vente_immo_detail` VARCHAR(500) DEFAULT NULL,
    `origine_cession_detail` VARCHAR(500) DEFAULT NULL,
    `origine_epargne_detail` VARCHAR(500) DEFAULT NULL,
    `origine_autre_detail` VARCHAR(500) DEFAULT NULL,
    `justificatif_origine_fonds` TEXT DEFAULT NULL,

    -- Commentaires (JSON)
    `commentaires_json` TEXT DEFAULT NULL,

    -- Justificatifs fournis (JSON)
    `justificatifs_json` TEXT DEFAULT NULL,
    `justificatif_autre_detail` VARCHAR(500) DEFAULT NULL,

    -- Signature
    `lieu_signature` VARCHAR(255) DEFAULT NULL,
    `date_signature` VARCHAR(20) DEFAULT NULL,
    `acknowledged` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `signature_name` VARCHAR(255) DEFAULT NULL,
    `signed_at` DATETIME DEFAULT NULL,

    -- Metadonnees
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME DEFAULT NULL,

    PRIMARY KEY (`id_lcbft_form`),
    KEY `idx_id_cart` (`id_cart`),
    KEY `idx_id_order` (`id_order`),
    KEY `idx_id_customer` (`id_customer`),
    KEY `idx_acknowledged` (`acknowledged`),
    KEY `idx_signed_at` (`signed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
