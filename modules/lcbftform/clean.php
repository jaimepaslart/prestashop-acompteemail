<?php
/**
 * Script de nettoyage/désinstallation du module LCB-FT Form
 *
 * Options :
 * - Sans paramètre : nettoie uniquement le cache
 * - --yes ou ?action=uninstall : désinstalle le module
 *
 * Usage CLI : php clean.php [--yes]
 * Usage Web : http://localhost/modules/lcbftform/clean.php[?action=uninstall]
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

// Detecter le mode d'execution
$isCli = (php_sapi_name() === 'cli');

// Detecter l'action demandee
$doUninstall = false;
if ($isCli) {
    $doUninstall = in_array('--yes', $argv);
} else {
    $doUninstall = (isset($_GET['action']) && $_GET['action'] === 'uninstall');
}

// Fonction d'affichage
function output($message, $type = 'info')
{
    global $isCli;

    $icons = array(
        'success' => '✅',
        'error' => '❌',
        'warning' => '⚠️',
        'info' => 'ℹ️',
    );

    $colors = array(
        'success' => '#28a745',
        'error' => '#dc3545',
        'warning' => '#ffc107',
        'info' => '#17a2b8',
    );

    $icon = isset($icons[$type]) ? $icons[$type] : $icons['info'];
    $color = isset($colors[$type]) ? $colors[$type] : $colors['info'];

    if ($isCli) {
        echo $icon . ' ' . $message . PHP_EOL;
    } else {
        echo '<p style="color: ' . $color . '; margin: 5px 0;">' . $icon . ' ' . htmlspecialchars($message) . '</p>';
    }
}

// En-tete HTML si mode web
if (!$isCli) {
    echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nettoyage - Module LCB-FT Form</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 2px solid #0088cc; padding-bottom: 10px; }
        .container { background: #f5f5f5; padding: 20px; border-radius: 5px; }
        .btn { display: inline-block; padding: 10px 20px; background: #0088cc; color: #fff; text-decoration: none; border-radius: 3px; margin-top: 15px; margin-right: 10px; }
        .btn:hover { background: #006699; }
        .btn-danger { background: #d9534f; }
        .btn-danger:hover { background: #c9302c; }
        .warning-box { background: #fcf8e3; border: 1px solid #faebcc; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
<h1>' . ($doUninstall ? 'Désinstallation' : 'Nettoyage du cache') . ' - Module LCB-FT Form</h1>
<div class="container">';

    // Si désinstallation demandée sans confirmation
    if ($doUninstall && !isset($_GET['confirm'])) {
        echo '<div class="warning-box">
            <h3 style="color: #8a6d3b; margin-top: 0;">⚠️ Attention</h3>
            <p>Vous êtes sur le point de désinstaller le module LCB-FT Form.</p>
            <p><strong>Cette action va :</strong></p>
            <ul>
                <li>Supprimer la table de la base de données</li>
                <li>Supprimer tous les formulaires LCB-FT enregistrés</li>
                <li>Supprimer l\'onglet d\'administration</li>
                <li>Restaurer les fichiers originaux du thème (checkout-process.tpl)</li>
            </ul>
            <p><strong>Les données des formulaires seront perdues définitivement !</strong></p>
            <a href="clean.php?action=uninstall&confirm=yes" class="btn btn-danger">Confirmer la désinstallation</a>
            <a href="diagnostic.php" class="btn">Annuler</a>
        </div>
        </div></body></html>';
        exit;
    }
}

// Trouver le chemin vers PrestaShop
$configPath = null;
$searchPaths = array(
    dirname(__FILE__) . '/../../config/config.inc.php',
    dirname(__FILE__) . '/../../../config/config.inc.php',
);

foreach ($searchPaths as $path) {
    if (file_exists($path)) {
        $configPath = $path;
        break;
    }
}

if (!$configPath) {
    output('Impossible de trouver PrestaShop.', 'error');
    exit(1);
}

// Charger PrestaShop
require_once $configPath;

$moduleName = 'lcbftform';

if ($doUninstall) {
    // ================================
    // DESINSTALLATION COMPLETE
    // ================================
    output('Désinstallation du module LCB-FT Form...', 'warning');

    // Charger le module
    $module = Module::getInstanceByName($moduleName);

    if ($module && Module::isInstalled($moduleName)) {
        // Désinstaller le module
        if ($module->uninstall()) {
            output('Module désinstallé avec succès.', 'success');
        } else {
            output('Erreur lors de la désinstallation du module.', 'error');
            if (!empty($module->_errors)) {
                foreach ($module->_errors as $error) {
                    output('  - ' . $error, 'error');
                }
            }
        }
    } else {
        output('Le module n\'est pas installé.', 'warning');

        // Nettoyer la base de données manuellement si besoin
        $tableName = _DB_PREFIX_ . 'lcbft_form';
        $tableExists = (bool) Db::getInstance()->executeS('SHOW TABLES LIKE "' . pSQL($tableName) . '"');

        if ($tableExists) {
            output('Table trouvée en base. Suppression...', 'info');
            if (Db::getInstance()->execute('DROP TABLE IF EXISTS ' . pSQL($tableName))) {
                output('Table supprimée.', 'success');
            }
        }
    }

    // Supprimer l'onglet admin si existe
    $tabId = Tab::getIdFromClassName('AdminLcbftForms');
    if ($tabId) {
        $tab = new Tab($tabId);
        if ($tab->delete()) {
            output('Onglet admin supprimé.', 'success');
        }
    }

    // Supprimer de la table des modules
    Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'module WHERE name = "' . pSQL($moduleName) . '"');

    // ================================
    // SUPPRESSION DES OVERRIDES THEME
    // ================================
    output('', 'info');
    output('Suppression des overrides du thème...', 'info');

    // Récupérer le thème actif
    $shop = new Shop((int) Configuration::get('PS_SHOP_DEFAULT'));
    $themeActive = $shop->theme_name;
    $themeDir = _PS_ALL_THEMES_DIR_ . $themeActive . '/';

    // Liste des fichiers installés par le module
    $themeOverrideFiles = array(
        'templates/checkout/_partials/steps/lcbft-step.tpl',
        'templates/checkout/checkout-process.tpl',
    );

    $removedFiles = 0;
    $restoredFiles = 0;

    foreach ($themeOverrideFiles as $relativePath) {
        $filePath = $themeDir . $relativePath;

        if (file_exists($filePath)) {
            // Chercher une sauvegarde .bak pour restaurer
            $backupPattern = $filePath . '.bak.*';
            $backups = glob($backupPattern);

            if (!empty($backups)) {
                // Prendre la sauvegarde la plus récente
                rsort($backups);
                $latestBackup = $backups[0];

                // Restaurer la sauvegarde
                if (copy($latestBackup, $filePath)) {
                    output('Restauré depuis backup : ' . $relativePath, 'success');
                    // Supprimer les backups
                    foreach ($backups as $backup) {
                        @unlink($backup);
                    }
                    $restoredFiles++;
                } else {
                    output('Erreur restauration : ' . $relativePath, 'error');
                }
            } else {
                // Pas de backup, supprimer le fichier si c'est le nôtre
                // Vérifier que c'est bien notre fichier (contient lcbft)
                $content = file_get_contents($filePath);
                if (strpos($content, 'lcbft') !== false || strpos($content, 'LCB-FT') !== false) {
                    if (@unlink($filePath)) {
                        output('Supprimé : ' . $relativePath, 'success');
                        $removedFiles++;
                    } else {
                        output('Erreur suppression : ' . $relativePath, 'error');
                    }
                } else {
                    output('Fichier non modifié par le module, ignoré : ' . $relativePath, 'info');
                }
            }
        }
    }

    if ($removedFiles > 0 || $restoredFiles > 0) {
        output('Fichiers supprimés : ' . $removedFiles . ', restaurés : ' . $restoredFiles, 'info');
    } else {
        output('Aucun override trouvé dans le thème.', 'info');
    }

    output('', 'info');
    output('Désinstallation terminée.', 'success');
    output('Les fichiers du module sont toujours présents dans /modules/lcbftform/', 'info');
    output('Vous pouvez les supprimer manuellement si nécessaire.', 'info');

} else {
    // ================================
    // NETTOYAGE DU CACHE UNIQUEMENT
    // ================================
    output('Nettoyage du cache PrestaShop...', 'info');

    // Vider le cache Smarty
    if (class_exists('Tools')) {
        Tools::clearAllCache();
        output('Cache Smarty vidé.', 'success');
    }

    // Vider le cache de classe
    $classIndexFile = _PS_CACHE_DIR_ . 'class_index.php';
    if (file_exists($classIndexFile)) {
        @unlink($classIndexFile);
        output('Index des classes supprimé.', 'success');
    }

    // Supprimer les fichiers de cache du module
    $smartyCacheDir = _PS_CACHE_DIR_ . 'smarty/compile/';
    if (is_dir($smartyCacheDir)) {
        $files = glob($smartyCacheDir . '*lcbft*');
        $count = 0;
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
                $count++;
            }
        }
        if ($count > 0) {
            output('Fichiers de cache Smarty du module supprimés : ' . $count, 'success');
        }
    }

    output('', 'info');
    output('Nettoyage du cache terminé.', 'success');
    output('', 'info');
    output('Pour désinstaller complètement le module :', 'info');
    if ($isCli) {
        output('  php clean.php --yes', 'info');
    } else {
        output('Utilisez le lien ci-dessous.', 'info');
    }
}

// Pied HTML si mode web
if (!$isCli) {
    echo '</div>';
    if (!$doUninstall) {
        echo '<a href="diagnostic.php" class="btn">Retour au diagnostic</a>';
        echo '<a href="clean.php?action=uninstall" class="btn btn-danger">Désinstaller le module</a>';
    } else {
        echo '<a href="install.php" class="btn">Réinstaller</a>';
    }
    echo '</body></html>';
}
