<?php
/**
 * Script d'installation du module LCB-FT Form
 *
 * Permet d'installer le module via CLI ou navigateur web.
 *
 * Usage CLI : php install.php
 * Usage Web : http://localhost/modules/lcbftform/install.php
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

// En-tete HTML si mode web
if (!$isCli) {
    echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Module LCB-FT Form</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 2px solid #0088cc; padding-bottom: 10px; }
        .container { background: #f5f5f5; padding: 20px; border-radius: 5px; }
        .btn { display: inline-block; padding: 10px 20px; background: #0088cc; color: #fff; text-decoration: none; border-radius: 3px; margin-top: 15px; }
        .btn:hover { background: #006699; }
    </style>
</head>
<body>
<h1>Installation du module LCB-FT Form</h1>
<div class="container">';
}

output('Démarrage de l\'installation du module LCB-FT Form...');

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
    output('Impossible de trouver le fichier config.inc.php de PrestaShop.', 'error');
    output('Assurez-vous que ce script est dans le dossier modules/lcbftform/', 'warning');
    exit(1);
}

output('Configuration PrestaShop trouvée.', 'success');

// Charger PrestaShop
try {
    require_once $configPath;
    output('PrestaShop chargé avec succès (version ' . _PS_VERSION_ . ').', 'success');
} catch (Exception $e) {
    output('Erreur lors du chargement de PrestaShop : ' . $e->getMessage(), 'error');
    exit(1);
}

// Verifier la version PHP
$phpVersion = phpversion();
if (version_compare($phpVersion, '7.2.0', '<')) {
    output('Version PHP insuffisante. Minimum requis : 7.2.0. Actuel : ' . $phpVersion, 'error');
    exit(1);
}
if (version_compare($phpVersion, '8.0.0', '>=')) {
    output('Version PHP trop récente. Maximum supporté : 7.4.x. Actuel : ' . $phpVersion, 'warning');
}
output('Version PHP : ' . $phpVersion, 'success');

// Verifier la version PrestaShop
if (version_compare(_PS_VERSION_, '1.7.6.0', '<') || version_compare(_PS_VERSION_, '1.7.9.0', '>=')) {
    output('Version PrestaShop non supportée. Requis : 1.7.6.x à 1.7.8.x. Actuel : ' . _PS_VERSION_, 'warning');
}
output('Version PrestaShop : ' . _PS_VERSION_, 'success');

// Charger le module
output('Chargement du module...', 'info');

$moduleName = 'lcbftform';
$module = Module::getInstanceByName($moduleName);

if (!$module) {
    output('Impossible de charger le module. Vérifiez que tous les fichiers sont présents.', 'error');
    exit(1);
}

// Verifier si deja installe
if (Module::isInstalled($moduleName)) {
    output('Le module est déjà installé.', 'warning');

    // Verifier si actif
    if (Module::isEnabled($moduleName)) {
        output('Le module est actif.', 'success');
    } else {
        output('Le module n\'est pas actif. Activation...', 'info');
        if ($module->enable()) {
            output('Module activé avec succès.', 'success');
        } else {
            output('Erreur lors de l\'activation du module.', 'error');
        }
    }
} else {
    // Installation
    output('Installation du module en cours...', 'info');

    if ($module->install()) {
        output('Module installé avec succès !', 'success');
    } else {
        output('Erreur lors de l\'installation du module.', 'error');
        if (!empty($module->_errors)) {
            foreach ($module->_errors as $error) {
                output('Erreur : ' . $error, 'error');
            }
        }
        exit(1);
    }
}

// Verifier les hooks
output('Vérification des hooks...', 'info');
$hooks = array(
    'displayPersonalInformationBottom',
    'displayHeader',
    'actionValidateOrder',
    'displayOrderDetail',
    'displayOrderConfirmation',
    'displayAdminOrder',
    'displayAdminOrderMain',
    'actionAdminControllerSetMedia',
    'displayBackOfficeHeader',
);

$registeredHooks = array();
$sql = new DbQuery();
$sql->select('h.name');
$sql->from('hook_module', 'hm');
$sql->innerJoin('hook', 'h', 'h.id_hook = hm.id_hook');
$sql->innerJoin('module', 'm', 'm.id_module = hm.id_module');
$sql->where('m.name = "' . pSQL($moduleName) . '"');

$results = Db::getInstance()->executeS($sql);
if ($results) {
    foreach ($results as $row) {
        $registeredHooks[] = $row['name'];
    }
}

foreach ($hooks as $hook) {
    if (in_array($hook, $registeredHooks)) {
        output('Hook ' . $hook . ' : enregistré', 'success');
    } else {
        output('Hook ' . $hook . ' : NON enregistré', 'warning');
        // Tenter d'enregistrer
        if ($module->registerHook($hook)) {
            output('Hook ' . $hook . ' : enregistré avec succès', 'success');
        }
    }
}

// Verifier la table SQL
output('Vérification de la base de données...', 'info');
$tableName = _DB_PREFIX_ . 'lcbft_form';
$tableExists = (bool) Db::getInstance()->executeS('SHOW TABLES LIKE "' . pSQL($tableName) . '"');

if ($tableExists) {
    output('Table ' . $tableName . ' : existe', 'success');

    // Compter les enregistrements
    $count = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . pSQL($tableName));
    output('Nombre de formulaires en base : ' . (int) $count, 'info');
} else {
    output('Table ' . $tableName . ' : N\'EXISTE PAS', 'error');
    output('Tentative de création de la table...', 'info');

    // Charger le SQL d'installation
    $sqlFile = dirname(__FILE__) . '/sql/install.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);

        if (Db::getInstance()->execute($sql)) {
            output('Table créée avec succès.', 'success');
        } else {
            output('Erreur lors de la création de la table.', 'error');
        }
    }
}

// Vider le cache
output('Nettoyage du cache...', 'info');
if (class_exists('Tools')) {
    Tools::clearAllCache();
    output('Cache vidé.', 'success');
}

// Résumé
output('', 'info');
output('========================================', 'info');
output('INSTALLATION TERMINÉE', 'success');
output('========================================', 'info');
output('');
output('Le module LCB-FT Form est maintenant installé.', 'success');
output('');
output('Prochaines étapes :', 'info');
output('1. Allez dans le Back-Office PrestaShop', 'info');
output('2. Menu Modules > Modules installés', 'info');
output('3. Recherchez "LCB-FT" pour configurer', 'info');
output('4. Le formulaire apparaîtra automatiquement dans le checkout', 'info');

// Pied HTML si mode web
if (!$isCli) {
    $boUrl = defined('__PS_BASE_URI__') ? __PS_BASE_URI__ . 'admin' : '../admin';
    echo '</div>
<a href="' . htmlspecialchars($boUrl) . '" class="btn">Aller au Back-Office</a>
</body>
</html>';
}
