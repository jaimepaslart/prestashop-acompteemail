<?php
/**
 * Script d'installation du module ProductStatusInOrder
 *
 * @author Paul Bihr
 * @license MIT
 */

// Configuration
define('MODULE_NAME', 'productstatusinorder');
define('MODULE_VERSION', '1.0.0');

// Détection de l'environnement
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Installation ProductStatusInOrder</title>';
    echo '<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}';
    echo '.success{color:#28a745;} .error{color:#dc3545;} .info{color:#007bff;} .warning{color:#ffc107;}';
    echo 'pre{background:#fff;padding:15px;border-left:3px solid #007bff;}</style></head><body>';
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
        echo '<h1 style="color:#007bff;">' . htmlspecialchars($text) . '</h1>';
    }
}

title('Installation du module ProductStatusInOrder');

// Vérifier que PrestaShop est chargé
if (!defined('_PS_VERSION_')) {
    // Chercher le fichier config.inc.php
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
            msg('PrestaShop chargé depuis: ' . $configPath, 'success');
            break;
        }
    }

    if (!$configFound) {
        msg('Impossible de trouver le fichier config.inc.php de PrestaShop', 'error');
        msg('Placez ce script dans modules/productstatusinorder/ ou spécifiez le chemin', 'warning');
        exit(1);
    }
}

// Vérifier la version PHP
msg('Version PHP: ' . PHP_VERSION, 'info');
if (version_compare(PHP_VERSION, '7.2.0', '<')) {
    msg('PHP 7.2 ou supérieur est requis', 'error');
    exit(1);
}
msg('Version PHP compatible', 'success');

// Vérifier PrestaShop
if (defined('_PS_VERSION_')) {
    msg('Version PrestaShop: ' . _PS_VERSION_, 'info');
}

// Vérifier si le module existe
$modulePath = _PS_MODULE_DIR_ . MODULE_NAME . '/';
if (!file_exists($modulePath . MODULE_NAME . '.php')) {
    msg('Module non trouvé dans: ' . $modulePath, 'error');
    exit(1);
}
msg('Fichiers du module trouvés', 'success');

// Vérifier si le module est déjà installé
$moduleInstalled = Db::getInstance()->getValue(
    'SELECT id_module FROM ' . _DB_PREFIX_ . 'module WHERE name = "' . pSQL(MODULE_NAME) . '"'
);

if ($moduleInstalled) {
    msg('Le module est déjà installé (ID: ' . $moduleInstalled . ')', 'warning');
    msg('Voulez-vous le réinstaller? Désinstallez-le d\'abord avec clean.php', 'info');
    exit(0);
}

// Charger le module
require_once $modulePath . MODULE_NAME . '.php';

// Instancier le module
$module = new ProductStatusInOrder();

msg('Tentative d\'installation du module...', 'info');

// Installer le module
if (!$module->install()) {
    msg('Erreur lors de l\'installation du module', 'error');
    if (count($module->_errors)) {
        foreach ($module->_errors as $error) {
            msg('  → ' . $error, 'error');
        }
    }
    exit(1);
}

msg('Module installé avec succès !', 'success');

// Vérifier les hooks
$hooks = Db::getInstance()->executeS(
    'SELECT h.name, hm.position
    FROM ' . _DB_PREFIX_ . 'hook h
    JOIN ' . _DB_PREFIX_ . 'hook_module hm ON h.id_hook = hm.id_hook
    JOIN ' . _DB_PREFIX_ . 'module m ON m.id_module = hm.id_module
    WHERE m.name = "' . pSQL(MODULE_NAME) . '"'
);

if ($hooks) {
    msg('Hooks enregistrés:', 'info');
    foreach ($hooks as $hook) {
        msg('  → ' . $hook['name'] . ' (position: ' . $hook['position'] . ')', 'success');
    }
} else {
    msg('Aucun hook enregistré', 'warning');
}

// Nettoyer le cache
msg('Nettoyage du cache...', 'info');
$cacheCleared = false;

// Nettoyer le cache Symfony (PrestaShop 1.7)
if (class_exists('Cache')) {
    Cache::clean('*');
    $cacheCleared = true;
}

// Supprimer les fichiers de cache
$cacheDirs = [
    _PS_CACHE_DIR_ . 'class_index.php',
    _PS_ROOT_DIR_ . '/var/cache/prod/',
    _PS_ROOT_DIR_ . '/var/cache/dev/',
];

foreach ($cacheDirs as $cacheItem) {
    if (file_exists($cacheItem)) {
        if (is_file($cacheItem)) {
            @unlink($cacheItem);
        } elseif (is_dir($cacheItem)) {
            $files = glob($cacheItem . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        $cacheCleared = true;
    }
}

if ($cacheCleared) {
    msg('Cache nettoyé', 'success');
}

// Instructions finales
echo PHP_EOL;
msg('╔════════════════════════════════════════════════════════════╗', 'success');
msg('║           Installation terminée avec succès ! 🎉           ║', 'success');
msg('╚════════════════════════════════════════════════════════════╝', 'success');
echo PHP_EOL;

msg('📋 Prochaines étapes:', 'info');
msg('1. Allez dans: Modules > Module Manager', 'info');
msg('2. Vérifiez que le module est actif', 'info');
msg('3. Testez: Ventes > Commandes > Ajouter une commande', 'info');
msg('4. Cherchez un produit et vérifiez les badges 🟢/🔴', 'info');
echo PHP_EOL;

msg('Pour diagnostiquer: php diagnostic.php', 'info');
msg('Pour désinstaller: php clean.php', 'info');

if (!$isCli) {
    echo '</body></html>';
}

exit(0);
