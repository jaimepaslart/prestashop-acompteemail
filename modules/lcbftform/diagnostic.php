<?php
/**
 * Script de diagnostic du module LCB-FT Form
 *
 * Vérifie l'état complet du module et de son installation.
 *
 * Usage CLI : php diagnostic.php
 * Usage Web : http://localhost/modules/lcbftform/diagnostic.php
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

// Detecter le mode d'execution
$isCli = (php_sapi_name() === 'cli');

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

function outputSection($title)
{
    global $isCli;
    if ($isCli) {
        echo PHP_EOL . '=== ' . $title . ' ===' . PHP_EOL;
    } else {
        echo '<h3 style="margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">' . htmlspecialchars($title) . '</h3>';
    }
}

// En-tete HTML si mode web
if (!$isCli) {
    echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic - Module LCB-FT Form</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; max-width: 900px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 2px solid #0088cc; padding-bottom: 10px; }
        h3 { color: #555; }
        .container { background: #f5f5f5; padding: 20px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #e9e9e9; }
        .btn { display: inline-block; padding: 10px 20px; background: #0088cc; color: #fff; text-decoration: none; border-radius: 3px; margin-top: 15px; margin-right: 10px; }
        .btn:hover { background: #006699; }
        .btn-warning { background: #f0ad4e; }
        .btn-danger { background: #d9534f; }
    </style>
</head>
<body>
<h1>Diagnostic du module LCB-FT Form</h1>
<div class="container">';
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
$moduleDir = dirname(__FILE__) . '/';

// ================================
// SECTION : ENVIRONNEMENT
// ================================
outputSection('Environnement');

output('PHP : ' . phpversion(), version_compare(phpversion(), '7.2.0', '>=') && version_compare(phpversion(), '8.0.0', '<') ? 'success' : 'warning');
output('PrestaShop : ' . _PS_VERSION_, 'info');
output('MySQL : ' . Db::getInstance()->getVersion(), 'info');

// ================================
// SECTION : FICHIERS DU MODULE
// ================================
outputSection('Fichiers du module');

$requiredFiles = array(
    'lcbftform.php' => 'Fichier principal',
    'config.xml' => 'Configuration XML',
    'classes/LcbftForm.php' => 'Classe ObjectModel',
    'classes/LcbftPdfGenerator.php' => 'Générateur PDF',
    'controllers/front/validation.php' => 'Contrôleur validation',
    'controllers/front/download.php' => 'Contrôleur téléchargement',
    'controllers/admin/AdminLcbftFormsController.php' => 'Contrôleur admin',
    'sql/install.sql' => 'Script SQL installation',
    'sql/uninstall.sql' => 'Script SQL désinstallation',
    'views/templates/hook/checkout_form.tpl' => 'Template formulaire checkout',
    'views/templates/admin/form_detail.tpl' => 'Template détail admin',
    'views/templates/admin/order_block.tpl' => 'Template bloc commande',
    'views/css/front.css' => 'CSS front-office',
    'views/css/admin.css' => 'CSS back-office',
    'views/js/front.js' => 'JavaScript front-office',
);

$missingFiles = array();
foreach ($requiredFiles as $file => $description) {
    $path = $moduleDir . $file;
    if (file_exists($path)) {
        output($description . ' (' . $file . ') : présent', 'success');
    } else {
        output($description . ' (' . $file . ') : MANQUANT', 'error');
        $missingFiles[] = $file;
    }
}

// ================================
// SECTION : SYNTAXE PHP
// ================================
outputSection('Validation syntaxe PHP');

$phpFiles = array(
    'lcbftform.php',
    'classes/LcbftForm.php',
    'classes/LcbftPdfGenerator.php',
    'controllers/front/validation.php',
    'controllers/front/download.php',
    'controllers/admin/AdminLcbftFormsController.php',
);

$syntaxErrors = 0;
foreach ($phpFiles as $file) {
    $path = $moduleDir . $file;
    if (file_exists($path)) {
        $output = array();
        $return = 0;
        exec('php -l ' . escapeshellarg($path) . ' 2>&1', $output, $return);

        if ($return === 0) {
            output($file . ' : syntaxe OK', 'success');
        } else {
            output($file . ' : ERREUR DE SYNTAXE', 'error');
            $syntaxErrors++;
        }
    }
}

// ================================
// SECTION : BASE DE DONNEES
// ================================
outputSection('Base de données');

// Verifier la table
$tableName = _DB_PREFIX_ . 'lcbft_form';
$tableExists = (bool) Db::getInstance()->executeS('SHOW TABLES LIKE "' . pSQL($tableName) . '"');

if ($tableExists) {
    output('Table ' . $tableName . ' : existe', 'success');

    // Verifier les colonnes
    $columns = Db::getInstance()->executeS('DESCRIBE ' . pSQL($tableName));
    output('Nombre de colonnes : ' . count($columns), 'info');

    // Compter les enregistrements
    $count = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . pSQL($tableName));
    output('Nombre de formulaires : ' . (int) $count, 'info');

    $signedCount = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . pSQL($tableName) . ' WHERE acknowledged = 1');
    output('Formulaires signés : ' . (int) $signedCount, 'info');
} else {
    output('Table ' . $tableName . ' : N\'EXISTE PAS', 'error');
}

// ================================
// SECTION : MODULE PRESTASHOP
// ================================
outputSection('État du module');

$isInstalled = Module::isInstalled($moduleName);
$isEnabled = Module::isEnabled($moduleName);

output('Installé : ' . ($isInstalled ? 'Oui' : 'Non'), $isInstalled ? 'success' : 'error');
output('Actif : ' . ($isEnabled ? 'Oui' : 'Non'), $isEnabled ? 'success' : 'warning');

// Verifier les hooks
output('', 'info');
output('Hooks enregistrés :', 'info');

$hooks = array(
    'displayPersonalInformationBottom' => 'Formulaire dans checkout',
    'displayHeader' => 'Chargement CSS/JS front',
    'actionValidateOrder' => 'Liaison commande',
    'displayOrderDetail' => 'PDF compte client',
    'displayOrderConfirmation' => 'Page confirmation',
    'displayAdminOrder' => 'Bloc admin commande (1.7.6)',
    'displayAdminOrderMain' => 'Bloc admin commande (1.7.7+)',
    'actionAdminControllerSetMedia' => 'CSS/JS admin',
    'displayBackOfficeHeader' => 'Header admin',
);

$sql = new DbQuery();
$sql->select('h.name');
$sql->from('hook_module', 'hm');
$sql->innerJoin('hook', 'h', 'h.id_hook = hm.id_hook');
$sql->innerJoin('module', 'm', 'm.id_module = hm.id_module');
$sql->where('m.name = "' . pSQL($moduleName) . '"');

$registeredHooks = array();
$results = Db::getInstance()->executeS($sql);
if ($results) {
    foreach ($results as $row) {
        $registeredHooks[] = $row['name'];
    }
}

foreach ($hooks as $hook => $description) {
    $registered = in_array($hook, $registeredHooks);
    output('  ' . $hook . ' : ' . ($registered ? 'OK' : 'NON'), $registered ? 'success' : 'warning');
}

// ================================
// SECTION : ONGLET ADMIN
// ================================
outputSection('Onglet administration');

$tabId = Tab::getIdFromClassName('AdminLcbftForms');
if ($tabId) {
    output('Onglet AdminLcbftForms : installé (ID: ' . $tabId . ')', 'success');
} else {
    output('Onglet AdminLcbftForms : NON INSTALLÉ', 'warning');
}

// ================================
// SECTION : CACHE
// ================================
outputSection('Cache');

$cacheDir = _PS_CACHE_DIR_;
$smartyCacheDir = _PS_CACHE_DIR_ . 'smarty/compile/';
$classCacheDir = _PS_CACHE_DIR_ . 'class_index.php';

output('Dossier cache : ' . (is_writable($cacheDir) ? 'accessible en écriture' : 'NON accessible'), is_writable($cacheDir) ? 'success' : 'error');

// ================================
// RÉSUMÉ
// ================================
outputSection('Résumé');

$issues = 0;
if (!empty($missingFiles)) {
    output('Fichiers manquants : ' . count($missingFiles), 'error');
    $issues += count($missingFiles);
}
if ($syntaxErrors > 0) {
    output('Erreurs de syntaxe : ' . $syntaxErrors, 'error');
    $issues += $syntaxErrors;
}
if (!$tableExists) {
    output('Table SQL manquante', 'error');
    $issues++;
}
if (!$isInstalled) {
    output('Module non installé', 'error');
    $issues++;
}

if ($issues === 0) {
    output('Aucun problème détecté. Le module est opérationnel.', 'success');
} else {
    output('Nombre de problèmes détectés : ' . $issues, 'warning');
    output('Exécutez install.php pour corriger les problèmes.', 'info');
}

// Pied HTML si mode web
if (!$isCli) {
    echo '</div>
<a href="install.php" class="btn">Réinstaller</a>
<a href="clean.php" class="btn btn-warning">Nettoyer le cache</a>
<a href="clean.php?action=uninstall" class="btn btn-danger">Désinstaller</a>
</body>
</html>';
}
