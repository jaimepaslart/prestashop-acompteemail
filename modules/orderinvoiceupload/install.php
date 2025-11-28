<?php
/**
 * Order Invoice Upload - Script d'installation
 *
 * Ce script permet d'installer le module via CLI ou navigateur.
 * Il détecte automatiquement l'environnement PrestaShop.
 *
 * Usage CLI :
 *   php install.php
 *
 * Usage navigateur :
 *   http://votresite.com/modules/orderinvoiceupload/install.php
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
    <title>Installation - ' . MODULE_DISPLAY_NAME . '</title>
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
            border-bottom: 2px solid #25b9d7;
            padding-bottom: 10px;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
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
<h1>🔧 Installation de ' . MODULE_DISPLAY_NAME . '</h1>';
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

// Début de l'affichage
displayHtmlHeader();

msg('Démarrage de l\'installation de ' . MODULE_DISPLAY_NAME . '...', 'info');

// Vérifier la version PHP
if (version_compare(PHP_VERSION, '7.2.0', '<')) {
    msg('PHP 7.2 ou supérieur est requis. Version actuelle : ' . PHP_VERSION, 'error');
    displayHtmlFooter();
    exit(1);
}

msg('Version PHP : ' . PHP_VERSION, 'success');

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

if (!$configLoaded || !defined('_PS_VERSION_')) {
    msg('Impossible de charger PrestaShop. Vérifiez que le module est dans le bon répertoire.', 'error');
    displayHtmlFooter();
    exit(1);
}

msg('PrestaShop ' . _PS_VERSION_ . ' chargé', 'success');

// Vérifier que le module existe
$modulePath = _PS_MODULE_DIR_ . MODULE_NAME . '/';
if (!is_dir($modulePath)) {
    msg('Répertoire du module non trouvé : ' . $modulePath, 'error');
    displayHtmlFooter();
    exit(1);
}

msg('Répertoire du module trouvé', 'success');

// Vérifier les fichiers requis
$requiredFiles = array(
    MODULE_NAME . '.php' => 'Fichier principal du module',
    'classes/OrderInvoiceUploadFile.php' => 'Classe helper',
    'sql/install.sql' => 'Script SQL d\'installation',
    'views/templates/admin/order_invoice_block.tpl' => 'Template d\'affichage',
);

$missingFiles = array();
foreach ($requiredFiles as $file => $description) {
    if (!file_exists($modulePath . $file)) {
        $missingFiles[] = $file;
    }
}

if (!empty($missingFiles)) {
    msg('Fichiers manquants :', 'error');
    foreach ($missingFiles as $file) {
        msg('  - ' . $file, 'error');
    }
    displayHtmlFooter();
    exit(1);
}

msg('Tous les fichiers requis sont présents', 'success');

// Créer le répertoire uploads s'il n'existe pas
$uploadsDir = $modulePath . 'uploads/';
if (!is_dir($uploadsDir)) {
    if (!mkdir($uploadsDir, 0755, true)) {
        msg('Impossible de créer le répertoire uploads', 'error');
        displayHtmlFooter();
        exit(1);
    }
    msg('Répertoire uploads créé', 'success');
} else {
    msg('Répertoire uploads existant', 'info');
}

// Vérifier les permissions d'écriture
if (!is_writable($uploadsDir)) {
    msg('Le répertoire uploads n\'est pas accessible en écriture', 'warning');
}

// Charger et instancier le module
require_once $modulePath . MODULE_NAME . '.php';

$module = new OrderInvoiceUpload();

// Vérifier si le module est déjà installé
$moduleInstalled = Db::getInstance()->getValue(
    'SELECT id_module FROM ' . _DB_PREFIX_ . 'module WHERE name = "' . pSQL(MODULE_NAME) . '"'
);

if ($moduleInstalled) {
    msg('Le module est déjà installé (ID: ' . $moduleInstalled . ')', 'warning');
    msg('Vous pouvez le réinstaller depuis le Back Office si nécessaire.', 'info');
    displayHtmlFooter();
    exit(0);
}

// Installer le module
msg('Installation du module en cours...', 'info');

if (!$module->install()) {
    msg('Erreur lors de l\'installation du module', 'error');
    if ($module->getErrors()) {
        foreach ($module->getErrors() as $error) {
            msg('  - ' . $error, 'error');
        }
    }
    displayHtmlFooter();
    exit(1);
}

msg('Module installé avec succès !', 'success');

// Afficher les hooks enregistrés
$hooks = Db::getInstance()->executeS(
    'SELECT h.name
    FROM ' . _DB_PREFIX_ . 'hook h
    JOIN ' . _DB_PREFIX_ . 'hook_module hm ON h.id_hook = hm.id_hook
    JOIN ' . _DB_PREFIX_ . 'module m ON m.id_module = hm.id_module
    WHERE m.name = "' . pSQL(MODULE_NAME) . '"'
);

if ($hooks) {
    msg('Hooks enregistrés :', 'info');
    foreach ($hooks as $hook) {
        msg('  - ' . $hook['name'], 'info');
    }
}

// Vider le cache
msg('Nettoyage du cache...', 'info');
if (class_exists('Cache')) {
    Cache::clean('*');
}

// Supprimer les fichiers de cache Smarty
$cacheDir = _PS_CACHE_DIR_ . 'smarty/compile/';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '*');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
            $count++;
        }
    }
    msg($count . ' fichiers de cache Smarty supprimés', 'info');
}

msg('Cache nettoyé', 'success');

// Instructions finales
if (!$isCli) {
    echo '<div class="section">';
}
msg('', 'info');
msg('=== INSTALLATION TERMINÉE ===', 'success');
msg('', 'info');
msg('Pour utiliser le module :', 'info');
msg('1. Allez dans le Back Office > Commandes > Détail d\'une commande', 'info');
msg('2. Vous verrez le bloc "Facture manuelle" pour téléverser un PDF', 'info');
msg('3. Téléversez votre facture PDF (max 5 Mo)', 'info');
if (!$isCli) {
    echo '</div>';
}

displayHtmlFooter();
exit(0);
