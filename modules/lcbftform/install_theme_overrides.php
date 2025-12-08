<?php
/**
 * Script d'installation des overrides de theme pour LCB-FT Form
 *
 * Ce script copie les fichiers necessaires vers le theme enfant actif
 * pour activer l'etape dediee LCB-FT dans le tunnel de commande.
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

// Detection mode CLI ou navigateur
$isCli = php_sapi_name() === 'cli';

// Configuration
define('MODULE_DIR', dirname(__FILE__) . '/');
define('THEME_OVERRIDES_DIR', MODULE_DIR . 'theme_overrides/child_warehouse/');

// Auto-detection de PrestaShop
$rootDir = dirname(dirname(dirname(__FILE__)));
$configFile = $rootDir . '/config/config.inc.php';

if (!file_exists($configFile)) {
    outputError('Configuration PrestaShop non trouvee. Verifiez que le module est dans /modules/');
    exit(1);
}

// Charger PrestaShop
define('_PS_ADMIN_DIR_', $rootDir . '/admin1762188721');
require_once($configFile);

// Fonctions d'affichage
function outputSuccess($message) {
    global $isCli;
    if ($isCli) {
        echo "\033[32m✓\033[0m " . $message . PHP_EOL;
    } else {
        echo '<div style="color: #22c55e; margin: 5px 0;">✓ ' . htmlspecialchars($message) . '</div>';
    }
}

function outputError($message) {
    global $isCli;
    if ($isCli) {
        echo "\033[31m✗\033[0m " . $message . PHP_EOL;
    } else {
        echo '<div style="color: #ef4444; margin: 5px 0;">✗ ' . htmlspecialchars($message) . '</div>';
    }
}

function outputInfo($message) {
    global $isCli;
    if ($isCli) {
        echo "\033[34mℹ\033[0m " . $message . PHP_EOL;
    } else {
        echo '<div style="color: #2563eb; margin: 5px 0;">ℹ ' . htmlspecialchars($message) . '</div>';
    }
}

function outputWarning($message) {
    global $isCli;
    if ($isCli) {
        echo "\033[33m⚠\033[0m " . $message . PHP_EOL;
    } else {
        echo '<div style="color: #f59e0b; margin: 5px 0;">⚠ ' . htmlspecialchars($message) . '</div>';
    }
}

// Header HTML
if (!$isCli) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Installation Theme Overrides - LCB-FT</title>';
    echo '<style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;max-width:800px;margin:40px auto;padding:20px;background:#f8fafc;}</style>';
    echo '</head><body>';
    echo '<h1>Installation des Overrides de Theme - LCB-FT Form</h1>';
}

outputInfo('Demarrage de l\'installation...');
outputInfo('');

// Recuperer le theme actif
$shop = new Shop((int) Configuration::get('PS_SHOP_DEFAULT'));
$themeActive = $shop->theme_name;

outputInfo('Theme actif : ' . $themeActive);

// Determiner le dossier du theme cible
$themeDir = _PS_ALL_THEMES_DIR_ . $themeActive . '/';

if (!is_dir($themeDir)) {
    outputError('Dossier du theme non trouve : ' . $themeDir);
    exit(1);
}

outputSuccess('Dossier theme trouve : ' . $themeDir);

// Verifier que les fichiers source existent
if (!is_dir(THEME_OVERRIDES_DIR)) {
    outputError('Dossier des overrides non trouve : ' . THEME_OVERRIDES_DIR);
    exit(1);
}

outputSuccess('Dossier des overrides trouve');
outputInfo('');

// Liste des fichiers a copier
$filesToCopy = array(
    'templates/checkout/checkout-process.tpl' => 'Processus checkout (insertion etape LCB-FT)',
    'templates/checkout/_partials/steps/lcbft-step.tpl' => 'Template etape LCB-FT',
);

$success = true;
$copiedFiles = 0;
$skippedFiles = 0;

foreach ($filesToCopy as $relativePath => $description) {
    $sourceFile = THEME_OVERRIDES_DIR . $relativePath;
    $destFile = $themeDir . $relativePath;
    $destDir = dirname($destFile);

    outputInfo('Traitement : ' . $description);

    // Verifier le fichier source
    if (!file_exists($sourceFile)) {
        outputError('  Fichier source manquant : ' . $relativePath);
        $success = false;
        continue;
    }

    // Creer le repertoire destination si necessaire
    if (!is_dir($destDir)) {
        if (!mkdir($destDir, 0755, true)) {
            outputError('  Impossible de creer le repertoire : ' . $destDir);
            $success = false;
            continue;
        }
        outputSuccess('  Repertoire cree : ' . str_replace($themeDir, '', $destDir));
    }

    // Verifier si le fichier existe deja
    if (file_exists($destFile)) {
        // Comparer les contenus
        $sourceContent = file_get_contents($sourceFile);
        $destContent = file_get_contents($destFile);

        if ($sourceContent === $destContent) {
            outputInfo('  Fichier identique, ignore : ' . $relativePath);
            $skippedFiles++;
            continue;
        }

        // Creer une sauvegarde
        $backupFile = $destFile . '.bak.' . date('YmdHis');
        if (copy($destFile, $backupFile)) {
            outputWarning('  Sauvegarde creee : ' . basename($backupFile));
        }
    }

    // Copier le fichier
    if (copy($sourceFile, $destFile)) {
        outputSuccess('  Copie : ' . $relativePath);
        $copiedFiles++;
    } else {
        outputError('  Echec copie : ' . $relativePath);
        $success = false;
    }
}

outputInfo('');

// Vider le cache
outputInfo('Vidage du cache...');

try {
    Tools::clearSmartyCache();
    Tools::clearXMLCache();

    // Supprimer les fichiers cache Smarty
    $cacheDir = _PS_CACHE_DIR_ . 'smarty/compile/';
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '*.php');
        if ($files) {
            array_map('unlink', $files);
        }
    }

    outputSuccess('Cache vide');
} catch (Exception $e) {
    outputWarning('Erreur vidage cache : ' . $e->getMessage());
}

outputInfo('');

// Resume
outputInfo('=== RESUME ===');
outputInfo('Fichiers copies : ' . $copiedFiles);
outputInfo('Fichiers ignores (deja identiques) : ' . $skippedFiles);

if ($success) {
    outputSuccess('');
    outputSuccess('Installation terminee avec succes !');
    outputInfo('');
    outputInfo('L\'etape LCB-FT apparaitra maintenant dans le tunnel de commande');
    outputInfo('apres l\'etape "Informations personnelles".');
} else {
    outputError('');
    outputError('Installation terminee avec des erreurs.');
    outputInfo('Verifiez les permissions des dossiers et reessayez.');
}

// Footer HTML
if (!$isCli) {
    echo '<p style="margin-top:30px;"><a href="' . _PS_BASE_URL_ . __PS_BASE_URI__ . 'admin1762188721/index.php">Retour au Back-Office</a></p>';
    echo '</body></html>';
}
