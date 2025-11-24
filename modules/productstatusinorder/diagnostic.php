<?php
/**
 * Script de diagnostic du module ProductStatusInOrder
 *
 * @author Paul Bihr
 * @license MIT
 */

// Configuration
define('MODULE_NAME', 'productstatusinorder');

// Détection de l'environnement
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Diagnostic ProductStatusInOrder</title>';
    echo '<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}';
    echo '.success{color:#28a745;} .error{color:#dc3545;} .info{color:#007bff;} .warning{color:#ffc107;}';
    echo 'pre{background:#fff;padding:15px;border-left:3px solid #007bff;margin:10px 0;}';
    echo 'table{border-collapse:collapse;width:100%;background:#fff;margin:10px 0;}';
    echo 'th,td{padding:10px;text-align:left;border:1px solid #ddd;}';
    echo 'th{background:#007bff;color:#fff;}</style></head><body>';
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
 * Section
 */
function section($text)
{
    global $isCli;

    if ($isCli) {
        echo PHP_EOL . str_repeat('=', 50) . PHP_EOL;
        echo '  ' . $text . PHP_EOL;
        echo str_repeat('=', 50) . PHP_EOL . PHP_EOL;
    } else {
        echo '<h2 style="color:#007bff;border-bottom:2px solid #007bff;padding-bottom:5px;">' . htmlspecialchars($text) . '</h2>';
    }
}

/**
 * Titre
 */
function title($text)
{
    global $isCli;

    if ($isCli) {
        echo PHP_EOL . str_repeat('=', 60) . PHP_EOL;
        echo '  ' . $text . PHP_EOL;
        echo str_repeat('=', 60) . PHP_EOL;
    } else {
        echo '<h1 style="color:#007bff;text-align:center;">' . htmlspecialchars($text) . '</h1>';
    }
}

title('Diagnostic du module ProductStatusInOrder');

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
// 1. ENVIRONNEMENT
///////////////////////////////////////////////////////////////////////////////

section('1. Environnement');

msg('Version PHP: ' . PHP_VERSION, 'info');
if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
    msg('Version PHP compatible', 'success');
} else {
    msg('PHP 7.2+ requis', 'error');
}

if (defined('_PS_VERSION_')) {
    msg('Version PrestaShop: ' . _PS_VERSION_, 'info');
}

msg('Répertoire PrestaShop: ' . _PS_ROOT_DIR_, 'info');

///////////////////////////////////////////////////////////////////////////////
// 2. FICHIERS DU MODULE
///////////////////////////////////////////////////////////////////////////////

section('2. Fichiers du module');

$modulePath = _PS_MODULE_DIR_ . MODULE_NAME . '/';

if (is_dir($modulePath)) {
    msg('Répertoire du module: ' . $modulePath, 'success');
} else {
    msg('Répertoire du module introuvable: ' . $modulePath, 'error');
    exit(1);
}

// Vérifier les fichiers principaux
$files = [
    MODULE_NAME . '.php' => 'Fichier principal',
    'index.php' => 'Protection',
    'views/js/product-status.js' => 'JavaScript',
    'views/css/product-status.css' => 'CSS',
    'install.php' => 'Installation',
    'diagnostic.php' => 'Diagnostic',
    'clean.php' => 'Nettoyage',
];

foreach ($files as $file => $description) {
    $fullPath = $modulePath . $file;
    if (file_exists($fullPath)) {
        msg($description . ': ' . $file, 'success');
    } else {
        msg($description . ': ' . $file . ' MANQUANT', 'error');
    }
}

// Vérifier les permissions
$perms = substr(sprintf('%o', fileperms($modulePath)), -3);
msg('Permissions du répertoire: ' . $perms, $perms >= '755' ? 'success' : 'warning');

// Vérifier la syntaxe PHP
$mainFile = $modulePath . MODULE_NAME . '.php';
if (file_exists($mainFile)) {
    $output = [];
    $return = 0;
    exec('php -l ' . escapeshellarg($mainFile) . ' 2>&1', $output, $return);
    if ($return === 0) {
        msg('Syntaxe PHP: OK', 'success');
    } else {
        msg('Syntaxe PHP: ERREUR', 'error');
        foreach ($output as $line) {
            msg('  ' . $line, 'error');
        }
    }
}

///////////////////////////////////////////////////////////////////////////////
// 3. BASE DE DONNÉES
///////////////////////////////////////////////////////////////////////////////

section('3. Base de données');

// Vérifier si le module est installé
$moduleData = Db::getInstance()->getRow(
    'SELECT id_module, name, version, active
    FROM ' . _DB_PREFIX_ . 'module
    WHERE name = "' . pSQL(MODULE_NAME) . '"'
);

if ($moduleData) {
    msg('Module enregistré en BDD: OUI', 'success');
    msg('  → ID: ' . $moduleData['id_module'], 'info');
    msg('  → Version: ' . $moduleData['version'], 'info');
    msg('  → Actif: ' . ($moduleData['active'] == 1 ? 'OUI' : 'NON'), $moduleData['active'] == 1 ? 'success' : 'error');
} else {
    msg('Module NON enregistré en BDD', 'error');
    msg('Lancez: php install.php', 'warning');
}

// Vérifier les hooks
if ($moduleData) {
    $hooks = Db::getInstance()->executeS(
        'SELECT h.name, hm.position
        FROM ' . _DB_PREFIX_ . 'hook h
        JOIN ' . _DB_PREFIX_ . 'hook_module hm ON h.id_hook = hm.id_hook
        WHERE hm.id_module = ' . (int) $moduleData['id_module']
    );

    if ($hooks) {
        msg('Hooks enregistrés: ' . count($hooks), 'success');

        if (!$isCli) {
            echo '<table><thead><tr><th>Hook</th><th>Position</th></tr></thead><tbody>';
            foreach ($hooks as $hook) {
                echo '<tr><td>' . htmlspecialchars($hook['name']) . '</td><td>' . $hook['position'] . '</td></tr>';
            }
            echo '</tbody></table>';
        } else {
            foreach ($hooks as $hook) {
                msg('  → ' . $hook['name'] . ' (position: ' . $hook['position'] . ')', 'info');
            }
        }
    } else {
        msg('Aucun hook enregistré', 'warning');
    }
}

///////////////////////////////////////////////////////////////////////////////
// 4. CACHE
///////////////////////////////////////////////////////////////////////////////

section('4. Cache');

$cacheDirs = [
    _PS_ROOT_DIR_ . '/var/cache/prod/' => 'Cache production',
    _PS_ROOT_DIR_ . '/var/cache/dev/' => 'Cache développement',
    _PS_CACHE_DIR_ => 'Cache classe',
];

foreach ($cacheDirs as $dir => $label) {
    if (is_dir($dir)) {
        $size = 0;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        $sizeFormatted = $size > 1024 * 1024 ? round($size / 1024 / 1024, 2) . ' MB' : round($size / 1024, 2) . ' KB';
        msg($label . ': ' . $sizeFormatted, 'info');
    }
}

$classIndex = _PS_CACHE_DIR_ . 'class_index.php';
if (file_exists($classIndex)) {
    msg('Fichier class_index.php présent (peut causer des problèmes)', 'warning');
    msg('Supprimez-le avec: rm ' . $classIndex, 'info');
} else {
    msg('Pas de fichier class_index.php', 'success');
}

///////////////////////////////////////////////////////////////////////////////
// 5. RECOMMANDATIONS
///////////////////////////////////////////////////////////////////////////////

section('5. Recommandations');

msg('Pour tester le module:', 'info');
msg('  1. Back-Office > Ventes > Commandes > Ajouter une commande', 'info');
msg('  2. Sélectionnez un client', 'info');
msg('  3. Cherchez un produit', 'info');
msg('  4. Ouvrez la console JavaScript (F12)', 'info');
msg('  5. Vérifiez les messages: [ProductStatusInOrder]', 'info');
echo PHP_EOL;

msg('Si les badges ne s\'affichent pas:', 'warning');
msg('  • Videz le cache: php clean.php --cache-only', 'info');
msg('  • Videz le cache navigateur: Ctrl+Shift+R', 'info');
msg('  • Vérifiez la console JavaScript (F12)', 'info');
msg('  • Vérifiez que "active" est dans la réponse AJAX', 'info');
echo PHP_EOL;

msg('Logs PrestaShop: ' . _PS_ROOT_DIR_ . '/var/logs/', 'info');

echo PHP_EOL;
msg('╔════════════════════════════════════════════════════════════╗', 'success');
msg('║             Diagnostic terminé ! 🔍                        ║', 'success');
msg('╚════════════════════════════════════════════════════════════╝', 'success');

if (!$isCli) {
    echo '</body></html>';
}

exit(0);
