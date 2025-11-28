<?php
/**
 * Order Invoice Upload - Script de diagnostic
 *
 * Ce script vérifie l'état du module et aide à identifier les problèmes.
 * Il peut être exécuté en CLI ou via navigateur.
 *
 * Usage CLI :
 *   php diagnostic.php
 *
 * Usage navigateur :
 *   http://votresite.com/modules/orderinvoiceupload/diagnostic.php
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
 * Affiche une section
 *
 * @param string $title Titre de la section
 * @return void
 */
function section($title)
{
    global $isCli;

    if ($isCli) {
        echo PHP_EOL . '=== ' . $title . ' ===' . PHP_EOL;
    } else {
        echo '<div class="section"><h2>' . htmlspecialchars($title) . '</h2>';
    }
}

/**
 * Ferme une section
 *
 * @return void
 */
function endSection()
{
    global $isCli;

    if (!$isCli) {
        echo '</div>';
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
    <title>Diagnostic - ' . MODULE_DISPLAY_NAME . '</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 900px;
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
        h2 {
            color: #555;
            font-size: 18px;
            margin-top: 0;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #25b9d7;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background: #f1f3f5;
            font-weight: 600;
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
        .summary {
            background: #e8f4f8;
            border: 1px solid #25b9d7;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
<h1>🔍 Diagnostic de ' . MODULE_DISPLAY_NAME . '</h1>';
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

// Compteurs de résultats
$errors = 0;
$warnings = 0;
$success = 0;

// Début de l'affichage
displayHtmlHeader();

msg('Diagnostic du module ' . MODULE_DISPLAY_NAME, 'info');
msg('Date : ' . date('Y-m-d H:i:s'), 'info');

// === SECTION : ENVIRONNEMENT ===
section('Environnement');

msg('Version PHP : ' . PHP_VERSION, version_compare(PHP_VERSION, '7.2.0', '>=') ? 'success' : 'error');
if (version_compare(PHP_VERSION, '7.2.0', '<')) {
    $errors++;
} else {
    $success++;
}

msg('Système d\'exploitation : ' . PHP_OS, 'info');
msg('Mode d\'exécution : ' . ($isCli ? 'CLI' : 'Navigateur'), 'info');

endSection();

// === SECTION : PRESTASHOP ===
section('PrestaShop');

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
    msg('PrestaShop non trouvé', 'error');
    $errors++;
    displayHtmlFooter();
    exit(1);
}

msg('Version PrestaShop : ' . _PS_VERSION_, 'success');
$success++;

$psVersionOk = version_compare(_PS_VERSION_, '1.7.6.0', '>=') && version_compare(_PS_VERSION_, '1.7.8.99', '<=');
if ($psVersionOk) {
    msg('Version compatible (1.7.6.0 - 1.7.8.x)', 'success');
    $success++;
} else {
    msg('Version peut être incompatible', 'warning');
    $warnings++;
}

msg('Préfixe de table : ' . _DB_PREFIX_, 'info');

endSection();

// === SECTION : FICHIERS DU MODULE ===
section('Fichiers du module');

$modulePath = _PS_MODULE_DIR_ . MODULE_NAME . '/';

if (is_dir($modulePath)) {
    msg('Répertoire du module : ' . $modulePath, 'success');
    $success++;
} else {
    msg('Répertoire du module non trouvé', 'error');
    $errors++;
    displayHtmlFooter();
    exit(1);
}

$requiredFiles = array(
    MODULE_NAME . '.php' => 'Fichier principal',
    'classes/OrderInvoiceUploadFile.php' => 'Classe helper',
    'sql/install.sql' => 'Script SQL installation',
    'sql/uninstall.sql' => 'Script SQL désinstallation',
    'views/templates/admin/order_invoice_block.tpl' => 'Template admin',
    'views/css/admin.css' => 'Styles CSS',
    'views/js/admin.js' => 'JavaScript',
    'controllers/admin/AdminOrderInvoiceUploadController.php' => 'Contrôleur admin',
);

if (!$isCli) {
    echo '<table><thead><tr><th>Fichier</th><th>Description</th><th>Statut</th></tr></thead><tbody>';
}

foreach ($requiredFiles as $file => $description) {
    $exists = file_exists($modulePath . $file);
    $status = $exists ? 'success' : 'error';

    if ($isCli) {
        msg($file . ' - ' . ($exists ? 'OK' : 'MANQUANT'), $status);
    } else {
        $icon = $exists ? '✅' : '❌';
        echo '<tr><td><code>' . htmlspecialchars($file) . '</code></td><td>' . htmlspecialchars($description) . '</td><td>' . $icon . '</td></tr>';
    }

    if ($exists) {
        $success++;
    } else {
        $errors++;
    }
}

if (!$isCli) {
    echo '</tbody></table>';
}

// Vérifier la syntaxe PHP du fichier principal
$mainFile = $modulePath . MODULE_NAME . '.php';
if (file_exists($mainFile)) {
    $output = array();
    $returnVar = 0;
    exec('php -l ' . escapeshellarg($mainFile) . ' 2>&1', $output, $returnVar);

    if ($returnVar === 0) {
        msg('Syntaxe PHP valide', 'success');
        $success++;
    } else {
        msg('Erreur de syntaxe PHP : ' . implode(' ', $output), 'error');
        $errors++;
    }
}

endSection();

// === SECTION : RÉPERTOIRE UPLOADS ===
section('Répertoire uploads');

$uploadsDir = $modulePath . 'uploads/';

if (is_dir($uploadsDir)) {
    msg('Répertoire existe : ' . $uploadsDir, 'success');
    $success++;

    if (is_writable($uploadsDir)) {
        msg('Permissions d\'écriture OK', 'success');
        $success++;
    } else {
        msg('Pas de permission d\'écriture', 'error');
        $errors++;
    }

    // Compter les fichiers uploadés
    $uploadedFiles = glob($uploadsDir . '*.pdf');
    $fileCount = $uploadedFiles ? count($uploadedFiles) : 0;
    msg('Factures téléversées : ' . $fileCount, 'info');

    // Vérifier le .htaccess
    if (file_exists($uploadsDir . '.htaccess')) {
        msg('Protection .htaccess présente', 'success');
        $success++;
    } else {
        msg('Protection .htaccess manquante', 'warning');
        $warnings++;
    }
} else {
    msg('Répertoire uploads non trouvé', 'error');
    $errors++;
}

endSection();

// === SECTION : BASE DE DONNÉES ===
section('Base de données');

// Vérifier si le module est installé
$moduleData = Db::getInstance()->getRow(
    'SELECT id_module, name, version, active
    FROM ' . _DB_PREFIX_ . 'module
    WHERE name = "' . pSQL(MODULE_NAME) . '"'
);

if ($moduleData) {
    msg('Module enregistré en BDD (ID: ' . $moduleData['id_module'] . ')', 'success');
    msg('Version en BDD : ' . $moduleData['version'], 'info');
    msg('Statut : ' . ($moduleData['active'] ? 'Actif' : 'Inactif'), $moduleData['active'] ? 'success' : 'warning');
    $success++;

    if (!$moduleData['active']) {
        $warnings++;
    }
} else {
    msg('Module non enregistré en BDD (non installé)', 'warning');
    $warnings++;
}

// Vérifier la table personnalisée
$tableExists = Db::getInstance()->executeS(
    'SHOW TABLES LIKE "' . _DB_PREFIX_ . 'order_invoice_upload"'
);

if ($tableExists) {
    msg('Table ' . _DB_PREFIX_ . 'order_invoice_upload existe', 'success');
    $success++;

    // Compter les entrées
    $count = Db::getInstance()->getValue(
        'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'order_invoice_upload'
    );
    msg('Entrées en base : ' . $count, 'info');

    // Vérifier la structure de la table
    $columns = Db::getInstance()->executeS(
        'SHOW COLUMNS FROM ' . _DB_PREFIX_ . 'order_invoice_upload'
    );

    $expectedColumns = array('id_order_invoice_upload', 'id_order', 'file_name', 'original_name', 'mime_type', 'date_add');
    $actualColumns = array_column($columns, 'Field');
    $missingColumns = array_diff($expectedColumns, $actualColumns);

    if (empty($missingColumns)) {
        msg('Structure de la table correcte', 'success');
        $success++;
    } else {
        msg('Colonnes manquantes : ' . implode(', ', $missingColumns), 'error');
        $errors++;
    }
} else {
    msg('Table ' . _DB_PREFIX_ . 'order_invoice_upload n\'existe pas', 'warning');
    $warnings++;
}

endSection();

// === SECTION : HOOKS ===
section('Hooks');

if ($moduleData) {
    $hooks = Db::getInstance()->executeS(
        'SELECT h.name, hm.position
        FROM ' . _DB_PREFIX_ . 'hook h
        JOIN ' . _DB_PREFIX_ . 'hook_module hm ON h.id_hook = hm.id_hook
        WHERE hm.id_module = ' . (int) $moduleData['id_module']
    );

    if ($hooks && count($hooks) > 0) {
        msg('Hooks enregistrés : ' . count($hooks), 'success');
        $success++;

        if (!$isCli) {
            echo '<table><thead><tr><th>Hook</th><th>Position</th></tr></thead><tbody>';
        }

        foreach ($hooks as $hook) {
            if ($isCli) {
                msg('  - ' . $hook['name'] . ' (position: ' . $hook['position'] . ')', 'info');
            } else {
                echo '<tr><td><code>' . htmlspecialchars($hook['name']) . '</code></td><td>' . $hook['position'] . '</td></tr>';
            }
        }

        if (!$isCli) {
            echo '</tbody></table>';
        }

        // Vérifier les hooks essentiels
        $hookNames = array_column($hooks, 'name');
        $essentialHooks = array('displayAdminOrder', 'displayAdminOrderMain');
        $hasEssentialHook = !empty(array_intersect($essentialHooks, $hookNames));

        if ($hasEssentialHook) {
            msg('Hook d\'affichage présent', 'success');
            $success++;
        } else {
            msg('Aucun hook d\'affichage trouvé', 'error');
            $errors++;
        }
    } else {
        msg('Aucun hook enregistré', 'warning');
        $warnings++;
    }
} else {
    msg('Module non installé - vérification des hooks impossible', 'warning');
    $warnings++;
}

endSection();

// === SECTION : CACHE ===
section('Cache');

$cacheDir = _PS_CACHE_DIR_;
msg('Répertoire de cache : ' . $cacheDir, 'info');

$smartyCacheDir = $cacheDir . 'smarty/compile/';
if (is_dir($smartyCacheDir)) {
    $cacheFiles = glob($smartyCacheDir . '*');
    $cacheCount = $cacheFiles ? count($cacheFiles) : 0;
    msg('Fichiers de cache Smarty : ' . $cacheCount, 'info');

    if ($cacheCount > 1000) {
        msg('Beaucoup de fichiers en cache - envisagez un nettoyage', 'warning');
        $warnings++;
    }
}

endSection();

// === RÉSUMÉ ===
if (!$isCli) {
    echo '<div class="summary">';
}

section('Résumé du diagnostic');

msg('Tests réussis : ' . $success, 'success');
if ($warnings > 0) {
    msg('Avertissements : ' . $warnings, 'warning');
}
if ($errors > 0) {
    msg('Erreurs : ' . $errors, 'error');
}

if ($errors === 0 && $warnings === 0) {
    msg('Le module semble correctement configuré !', 'success');
} elseif ($errors === 0) {
    msg('Le module fonctionne mais certains points méritent attention.', 'warning');
} else {
    msg('Des erreurs ont été détectées. Corrigez-les pour un fonctionnement optimal.', 'error');
}

endSection();

if (!$isCli) {
    echo '</div>';
}

// Recommandations
if ($errors > 0 || $warnings > 0) {
    section('Recommandations');

    if (!$moduleData) {
        msg('Installez le module via le Back Office ou le script install.php', 'info');
    }

    if (!$tableExists) {
        msg('Réinstallez le module pour créer la table de base de données', 'info');
    }

    if (isset($uploadsDir) && !is_writable($uploadsDir)) {
        msg('Exécutez : chmod 755 ' . $uploadsDir, 'info');
    }

    endSection();
}

displayHtmlFooter();
exit($errors > 0 ? 1 : 0);
