<?php
/**
 * Order Invoice Upload - Script de nettoyage/désinstallation
 *
 * Ce script permet de nettoyer le cache ou de désinstaller complètement le module.
 * Il peut être exécuté en CLI ou via navigateur.
 *
 * Usage CLI :
 *   php clean.php                  # Affiche les options
 *   php clean.php --cache-only     # Nettoie uniquement le cache
 *   php clean.php --yes            # Désinstalle complètement le module
 *
 * Usage navigateur :
 *   http://votresite.com/modules/orderinvoiceupload/clean.php
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

// Configuration
define('MODULE_NAME', 'orderinvoiceupload');
define('MODULE_DISPLAY_NAME', 'Order Invoice Upload');

// Détection du mode d'exécution
$isCli = php_sapi_name() === 'cli';

// Récupérer les options
$cacheOnly = $isCli ? in_array('--cache-only', $argv) : isset($_GET['cache-only']);
$confirmUninstall = $isCli ? in_array('--yes', $argv) : isset($_POST['confirm_uninstall']);

/**
 * Affiche un message formaté
 *
 * @param string $message Message à afficher
 * @param string $type Type de message (success, error, warning, info)
 * @return void
 */
function msg($message, $type = 'info')
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

/**
 * Affiche le header HTML
 *
 * @return void
 */
function displayHtmlHeader()
{
    global $isCli;
    if ($isCli) {
        return;
    }

    echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nettoyage - ' . MODULE_DISPLAY_NAME . '</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .danger-box {
            background: #f8d7da;
            border: 1px solid #dc3545;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            text-decoration: none;
        }
        .btn-primary {
            background: #25b9d7;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
        }
        code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #25b9d7;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
<h1>🧹 Nettoyage de ' . MODULE_DISPLAY_NAME . '</h1>';
}

/**
 * Affiche le footer HTML
 *
 * @return void
 */
function displayHtmlFooter()
{
    global $isCli;
    if ($isCli) {
        return;
    }

    echo '<a href="../../admin1762188721/index.php?controller=AdminModules" class="back-link">← Retour aux modules</a>
</div>
</body>
</html>';
}

/**
 * Nettoie le cache PrestaShop
 *
 * @return int Nombre de fichiers supprimés
 */
function cleanCache()
{
    $deleted = 0;

    // Cache PrestaShop
    if (class_exists('Cache')) {
        Cache::clean('*');
    }

    // Cache Smarty
    $smartyDirs = array(
        _PS_CACHE_DIR_ . 'smarty/compile/',
        _PS_CACHE_DIR_ . 'smarty/cache/',
    );

    foreach ($smartyDirs as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '*');
            if ($files) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                        $deleted++;
                    }
                }
            }
        }
    }

    // Cache de classe
    $classCacheDir = _PS_CACHE_DIR_ . 'class_index.php';
    if (file_exists($classCacheDir)) {
        @unlink($classCacheDir);
        $deleted++;
    }

    return $deleted;
}

/**
 * Supprime les fichiers uploadés du module
 *
 * @return int Nombre de fichiers supprimés
 */
function cleanUploadedFiles()
{
    $deleted = 0;
    $uploadsDir = _PS_MODULE_DIR_ . MODULE_NAME . '/uploads/';

    if (is_dir($uploadsDir)) {
        $files = glob($uploadsDir . '*.pdf');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                    $deleted++;
                }
            }
        }
    }

    return $deleted;
}

// Charger PrestaShop
$configPaths = array(
    __DIR__ . '/../../config/config.inc.php',
    dirname(__DIR__, 2) . '/config/config.inc.php',
);

$configLoaded = false;
foreach ($configPaths as $configPath) {
    if (file_exists($configPath)) {
        require_once $configPath;
        $configLoaded = true;
        break;
    }
}

// Début de l'affichage
displayHtmlHeader();

if (!$configLoaded || !defined('_PS_VERSION_')) {
    msg('Impossible de charger PrestaShop.', 'error');
    displayHtmlFooter();
    exit(1);
}

// Mode cache uniquement
if ($cacheOnly) {
    msg('Nettoyage du cache uniquement...', 'info');

    $deleted = cleanCache();
    msg($deleted . ' fichiers de cache supprimés', 'success');

    msg('Cache nettoyé avec succès !', 'success');
    displayHtmlFooter();
    exit(0);
}

// Afficher l'interface de confirmation (si pas encore confirmé)
if (!$confirmUninstall) {
    if ($isCli) {
        echo PHP_EOL;
        echo '=== Options de nettoyage ===' . PHP_EOL;
        echo PHP_EOL;
        echo 'Usage :' . PHP_EOL;
        echo '  php clean.php --cache-only    Nettoie uniquement le cache' . PHP_EOL;
        echo '  php clean.php --yes           Désinstalle complètement le module' . PHP_EOL;
        echo PHP_EOL;
        echo '⚠️  La désinstallation supprimera :' . PHP_EOL;
        echo '    - Les données en base de données' . PHP_EOL;
        echo '    - Toutes les factures téléversées' . PHP_EOL;
        echo '    - Les hooks enregistrés' . PHP_EOL;
        echo PHP_EOL;
    } else {
        echo '<div class="section">';
        echo '<h2>Options disponibles</h2>';

        echo '<div class="warning-box">';
        echo '<h3>🧹 Nettoyage du cache</h3>';
        echo '<p>Supprime les fichiers de cache sans toucher au module.</p>';
        echo '<a href="?cache-only=1" class="btn btn-primary">Nettoyer le cache uniquement</a>';
        echo '</div>';

        echo '<div class="danger-box">';
        echo '<h3>⚠️ Désinstallation complète</h3>';
        echo '<p><strong>Cette action supprimera :</strong></p>';
        echo '<ul>';
        echo '<li>Les données en base de données</li>';
        echo '<li>Toutes les factures téléversées</li>';
        echo '<li>Les hooks enregistrés</li>';
        echo '</ul>';
        echo '<p><strong>Cette action est irréversible !</strong></p>';

        echo '<form method="post" onsubmit="return confirm(\'Êtes-vous sûr de vouloir désinstaller le module ?\')">';
        echo '<input type="hidden" name="confirm_uninstall" value="1">';
        echo '<button type="submit" class="btn btn-danger">Désinstaller complètement</button>';
        echo '</form>';
        echo '</div>';

        echo '</div>';
    }

    displayHtmlFooter();
    exit(0);
}

// === DÉSINSTALLATION COMPLÈTE ===
msg('Désinstallation complète du module...', 'warning');

// Vérifier si le module est installé
$moduleData = Db::getInstance()->getRow(
    'SELECT id_module FROM ' . _DB_PREFIX_ . 'module WHERE name = "' . pSQL(MODULE_NAME) . '"'
);

if ($moduleData) {
    // Charger et désinstaller le module
    $modulePath = _PS_MODULE_DIR_ . MODULE_NAME . '/';
    if (file_exists($modulePath . MODULE_NAME . '.php')) {
        require_once $modulePath . MODULE_NAME . '.php';

        $module = new OrderInvoiceUpload();

        msg('Désinstallation du module via PrestaShop...', 'info');

        if ($module->uninstall()) {
            msg('Module désinstallé via PrestaShop', 'success');
        } else {
            msg('Erreur lors de la désinstallation via PrestaShop', 'warning');

            // Nettoyage manuel
            msg('Nettoyage manuel des données...', 'info');

            // Supprimer les hooks
            Db::getInstance()->delete('hook_module', 'id_module = ' . (int) $moduleData['id_module']);
            msg('Hooks supprimés', 'info');

            // Supprimer le module de la table
            Db::getInstance()->delete('module', 'id_module = ' . (int) $moduleData['id_module']);
            msg('Entrée module supprimée', 'info');
        }
    }
} else {
    msg('Module non enregistré en BDD', 'info');
}

// Supprimer la table personnalisée
$tableExists = Db::getInstance()->executeS(
    'SHOW TABLES LIKE "' . _DB_PREFIX_ . 'order_invoice_upload"'
);

if ($tableExists) {
    // Compter les entrées avant suppression
    $count = Db::getInstance()->getValue(
        'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'order_invoice_upload'
    );

    Db::getInstance()->execute(
        'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'order_invoice_upload'
    );
    msg('Table supprimée (' . $count . ' entrées)', 'success');
} else {
    msg('Table n\'existait pas', 'info');
}

// Supprimer les fichiers uploadés
$deletedFiles = cleanUploadedFiles();
if ($deletedFiles > 0) {
    msg($deletedFiles . ' fichiers de factures supprimés', 'success');
} else {
    msg('Aucun fichier de facture à supprimer', 'info');
}

// Nettoyer le cache
$cacheDeleted = cleanCache();
msg($cacheDeleted . ' fichiers de cache supprimés', 'success');

// Résumé
echo $isCli ? PHP_EOL : '<div class="section">';
msg('=== DÉSINSTALLATION TERMINÉE ===', 'success');
msg('Le module a été complètement désinstallé.', 'success');
msg('Les fichiers du module sont toujours présents dans /modules/' . MODULE_NAME . '/', 'info');
msg('Vous pouvez les supprimer manuellement si nécessaire.', 'info');
echo $isCli ? '' : '</div>';

displayHtmlFooter();
exit(0);
