<?php
/**
 * Script de nettoyage du module ProductStatusInOrder
 *
 * @author Paul Bihr
 * @license MIT
 */

// Configuration
define('MODULE_NAME', 'productstatusinorder');

// Détection de l'environnement
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Nettoyage ProductStatusInOrder</title>';
    echo '<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}';
    echo '.success{color:#28a745;} .error{color:#dc3545;} .info{color:#007bff;} .warning{color:#ffc107;}';
    echo 'pre{background:#fff;padding:15px;border-left:3px solid #ffc107;margin:10px 0;}';
    echo '.confirm{background:#fff;padding:20px;border:2px solid #ffc107;margin:20px 0;}';
    echo 'button{padding:10px 20px;margin:5px;font-size:14px;cursor:pointer;}</style></head><body>';
}

/**
 * Afficher un message
 */
function msg($message, $type = 'info')
{
    global $isCli;

    $icons = ['success' => '✅', 'error' => '❌', 'info' => 'ℹ️', 'warning' => '⚠️'];
    $icon = isset($icons[$type]) ? $icons[$type] : '';

    if ($isCli) {
        echo $icon . ' ' . $message . PHP_EOL;
    } else {
        echo '<div class="' . $type . '">' . $icon . ' ' . htmlspecialchars($message) . '</div>';
    }
}

/**
 * Titre
 */
function title($text)
{
    global $isCli;

    if ($isCli) {
        echo PHP_EOL . str_repeat('=', 50) . PHP_EOL;
        echo '  ' . $text . PHP_EOL;
        echo str_repeat('=', 50) . PHP_EOL . PHP_EOL;
    } else {
        echo '<h1 style="color:#ffc107;">' . htmlspecialchars($text) . '</h1>';
    }
}

title('Nettoyage du module ProductStatusInOrder');

// Vérifier les options
$cacheOnly = in_array('--cache-only', $argv ?? []) || isset($_GET['cache_only']);
$confirm = in_array('--yes', $argv ?? []) || isset($_POST['confirm']);

// Charger PrestaShop
if (!defined('_PS_VERSION_')) {
    $configPaths = [
        __DIR__ . '/../../config/config.inc.php',
        __DIR__ . '/../../../config/config.inc.php',
        dirname(__DIR__, 3) . '/config/config.inc.php',
    ];

    $configFound = false;
    foreach ($configPaths as $configPath) {
        if (file_exists($configPath)) {
            require_once $configPath;
            $configFound = true;
            break;
        }
    }

    if (!$configFound) {
        msg('Impossible de trouver le fichier config.inc.php de PrestaShop', 'error');
        exit(1);
    }
}

///////////////////////////////////////////////////////////////////////////////
// MODE: NETTOYAGE DU CACHE UNIQUEMENT
///////////////////////////////////////////////////////////////////////////////

if ($cacheOnly) {
    msg('Mode: Nettoyage du cache uniquement', 'info');

    // Nettoyer le cache
    if (class_exists('Cache')) {
        Cache::clean('*');
        msg('Cache PrestaShop nettoyé', 'success');
    }

    // Supprimer les fichiers de cache
    $cacheDirs = [
        _PS_ROOT_DIR_ . '/var/cache/prod/',
        _PS_ROOT_DIR_ . '/var/cache/dev/',
    ];

    foreach ($cacheDirs as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '*');
            $count = 0;
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                    $count++;
                }
            }
            if ($count > 0) {
                msg('Supprimés: ' . $count . ' fichiers dans ' . basename($dir), 'success');
            }
        }
    }

    // Supprimer class_index.php
    $classIndex = _PS_CACHE_DIR_ . 'class_index.php';
    if (file_exists($classIndex)) {
        @unlink($classIndex);
        msg('Fichier class_index.php supprimé', 'success');
    }

    msg('Cache nettoyé avec succès !', 'success');
    exit(0);
}

///////////////////////////////////////////////////////////////////////////////
// MODE: DÉSINSTALLATION COMPLÈTE
///////////////////////////////////////////////////////////////////////////////

// Vérifier si le module est installé
$moduleData = Db::getInstance()->getRow(
    'SELECT id_module, name, active
    FROM ' . _DB_PREFIX_ . 'module
    WHERE name = "' . pSQL(MODULE_NAME) . '"'
);

if (!$moduleData) {
    msg('Le module n\'est pas installé en base de données', 'warning');
    msg('Rien à nettoyer', 'info');
    exit(0);
}

msg('Module trouvé en BDD (ID: ' . $moduleData['id_module'] . ')', 'info');
msg('Statut: ' . ($moduleData['active'] == 1 ? 'Actif' : 'Inactif'), 'info');

// Demander confirmation
if (!$confirm) {
    if ($isCli) {
        echo PHP_EOL;
        msg('⚠️  ATTENTION: Cette action va désinstaller le module !', 'warning');
        msg('Le module sera supprimé de la base de données', 'warning');
        msg('Les fichiers resteront dans /modules/' . MODULE_NAME . '/', 'info');
        echo PHP_EOL;
        echo 'Voulez-vous continuer? (yes/no): ';
        $handle = fopen('php://stdin', 'r');
        $line = trim(fgets($handle));
        fclose($handle);

        if (strtolower($line) !== 'yes') {
            msg('Nettoyage annulé', 'info');
            exit(0);
        }
    } else {
        echo '<div class="confirm">';
        echo '<h3 style="color:#ffc107;">⚠️ Confirmation requise</h3>';
        echo '<p>Cette action va <strong>désinstaller le module</strong> de la base de données.</p>';
        echo '<p>Les fichiers resteront dans <code>/modules/' . MODULE_NAME . '/</code></p>';
        echo '<form method="POST">';
        echo '<button type="submit" name="confirm" value="1" style="background:#dc3545;color:#fff;border:none;">Désinstaller</button>';
        echo '<button type="button" onclick="history.back()" style="background:#6c757d;color:#fff;border:none;">Annuler</button>';
        echo '</form>';
        echo '</div>';
        echo '</body></html>';
        exit(0);
    }
}

// Charger le module
$modulePath = _PS_MODULE_DIR_ . MODULE_NAME . '/';
if (!file_exists($modulePath . MODULE_NAME . '.php')) {
    msg('Fichier du module introuvable', 'error');
    exit(1);
}

require_once $modulePath . MODULE_NAME . '.php';
$module = new ProductStatusInOrder();

msg('Désinstallation du module...', 'info');

// Désinstaller
if (!$module->uninstall()) {
    msg('Erreur lors de la désinstallation', 'error');
    if (count($module->_errors)) {
        foreach ($module->_errors as $error) {
            msg('  → ' . $error, 'error');
        }
    }
    exit(1);
}

msg('Module désinstallé avec succès !', 'success');

// Nettoyer le cache
msg('Nettoyage du cache...', 'info');

if (class_exists('Cache')) {
    Cache::clean('*');
}

$cacheDirs = [
    _PS_ROOT_DIR_ . '/var/cache/prod/',
    _PS_ROOT_DIR_ . '/var/cache/dev/',
];

foreach ($cacheDirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}

$classIndex = _PS_CACHE_DIR_ . 'class_index.php';
if (file_exists($classIndex)) {
    @unlink($classIndex);
}

msg('Cache nettoyé', 'success');

// Vérifier que tout est bien nettoyé
$stillExists = Db::getInstance()->getValue(
    'SELECT id_module FROM ' . _DB_PREFIX_ . 'module WHERE name = "' . pSQL(MODULE_NAME) . '"'
);

if ($stillExists) {
    msg('Le module est toujours en BDD (nettoyage incomplet)', 'warning');
    msg('Exécutez manuellement:', 'info');
    echo ($isCli ? '' : '<pre>');
    echo 'DELETE FROM ' . _DB_PREFIX_ . 'module WHERE name = "' . MODULE_NAME . '";' . PHP_EOL;
    echo 'DELETE FROM ' . _DB_PREFIX_ . 'hook_module WHERE id_module NOT IN (SELECT id_module FROM ' . _DB_PREFIX_ . 'module);' . PHP_EOL;
    echo ($isCli ? '' : '</pre>');
} else {
    msg('Module complètement supprimé de la BDD', 'success');
}

echo PHP_EOL;
msg('╔════════════════════════════════════════════════════════════╗', 'success');
msg('║           Nettoyage terminé avec succès ! 🧹              ║', 'success');
msg('╚════════════════════════════════════════════════════════════╝', 'success');
echo PHP_EOL;

msg('Les fichiers du module sont toujours dans:', 'info');
msg('  ' . $modulePath, 'info');
echo PHP_EOL;

msg('Pour supprimer les fichiers:', 'warning');
msg('  rm -rf ' . $modulePath, 'warning');
echo PHP_EOL;

msg('Pour réinstaller:', 'info');
msg('  php install.php', 'info');

if (!$isCli) {
    echo '</body></html>';
}

exit(0);
