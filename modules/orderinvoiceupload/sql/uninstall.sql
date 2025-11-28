/**
 * Order Invoice Upload - Uninstallation SQL
 *
 * Suppression de la table des factures téléversées.
 * ATTENTION : Cela supprimera toutes les données !
 *
 * PREFIX_ sera remplacé par le préfixe de table PrestaShop (ex: ps_)
 */

DROP TABLE IF EXISTS `PREFIX_order_invoice_upload`;
