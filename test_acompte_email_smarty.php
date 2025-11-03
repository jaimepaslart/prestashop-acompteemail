<?php
/**
 * Script pour tester l'envoi d'email avec traitement Smarty simplifié
 */

error_reporting(E_ALL & ~E_DEPRECATED);

echo "=== Test d'envoi d'email avec module AcompteEmail (avec Smarty) ===\n\n";

// Configuration
$smtp_host = '127.0.0.1';
$smtp_port = 1025;
$to_email = 'test@example.com';
$to_name = 'Client Test';

// Données de la commande avec acompte
$order_id = 5;
$order_reference = 'KHWLILZLL';
$total_to_pay = 20.90;
$amount_paid = 5.00;
$amount_remaining = 15.90;

// Charger le template HTML
$template_path = __DIR__ . '/mails/fr/order_conf.html';

if (!file_exists($template_path)) {
    echo "❌ Erreur : Template introuvable à $template_path\n";
    exit(1);
}

$html_content = file_get_contents($template_path);

// Variables du template
$replacements = [
    '{firstname}' => 'Jean',
    '{lastname}' => 'Dupont',
    '{email}' => $to_email,
    '{order_name}' => $order_reference,
    '{id_order}' => $order_id,
    '{date}' => date('d/m/Y H:i:s'),
    '{carrier}' => 'Colissimo',
    '{payment}' => 'Virement bancaire',
    '{invoice_block_txt}' => "Jean Dupont\n123 rue de la Paix\n75000 Paris\nFrance",
    '{invoice_block_html}' => "Jean Dupont<br>123 rue de la Paix<br>75000 Paris<br>France",
    '{delivery_block_txt}' => "Jean Dupont\n123 rue de la Paix\n75000 Paris\nFrance",
    '{delivery_block_html}' => "Jean Dupont<br>123 rue de la Paix<br>75000 Paris<br>France",
    '{products}' => '<tr><td>Produit Test</td><td>1</td><td>18,90 €</td></tr>',
    '{products_txt}' => "Produit Test x1 - 18,90 €",
    '{discounts}' => '',
    '{discounts_txt}' => '',
    '{total_products}' => '18,90 €',
    '{total_discounts}' => '0,00 €',
    '{total_shipping}' => '2,00 €',
    '{total_wrapping}' => '0,00 €',
    '{total_tax_paid}' => '3,48 €',
    '{total_paid}' => number_format($total_to_pay, 2, ',', ' ') . ' €',
    // Variables du module AcompteEmail
    '{total_to_pay}' => number_format($total_to_pay, 2, ',', ' ') . ' €',
    '{amount_paid}' => number_format($amount_paid, 2, ',', ' ') . ' €',
    '{amount_remaining}' => number_format($amount_remaining, 2, ',', ' ') . ' €',
    '{total_to_pay_raw}' => $total_to_pay,
    '{amount_paid_raw}' => $amount_paid,
    '{amount_remaining_raw}' => $amount_remaining,
    '{is_fully_paid}' => 0,
    '{shop_name}' => 'Ma Boutique Test',
    '{shop_url}' => 'http://localhost:8081',
];

echo "Données de la commande :\n";
echo "  - Commande : #$order_id ($order_reference)\n";
echo "  - Total à payer : " . number_format($total_to_pay, 2, ',', ' ') . " €\n";
echo "  - Acompte : " . number_format($amount_paid, 2, ',', ' ') . " €\n";
echo "  - Reste à payer : " . number_format($amount_remaining, 2, ',', ' ') . " €\n\n";

// === TRAITEMENT SIMPLIFIÉ DES CONDITIONS SMARTY ===

// Condition : {if isset($amount_remaining_raw) && $amount_remaining_raw > 0 && isset($amount_paid_raw) && $amount_paid_raw > 0}
$has_partial_payment = ($amount_remaining > 0 && $amount_paid > 0);

echo "Traitement des conditions Smarty...\n";
echo "  - Paiement partiel détecté : " . ($has_partial_payment ? "OUI" : "NON") . "\n\n";

if ($has_partial_payment) {
    // CAS 1 : Paiement partiel - Garder le bloc {if} et supprimer le bloc {else}
    // On supprime le bloc ELSE (ligne "Total payé")
    $pattern = '/{else}.*?<td[^>]*>Total payé.*?<\/tr>/s';
    $html_content = preg_replace($pattern, '', $html_content);

    echo "✅ Affichage mode ACOMPTE :\n";
    echo "   - Total à payer\n";
    echo "   - Acompte\n";
    echo "   - Reste à payer\n";
} else {
    // CAS 2 : Paiement complet - Garder le bloc {else} et supprimer le bloc {if}
    $pattern = '/{if isset\(\$amount_remaining_raw\).*?{else}/s';
    $html_content = preg_replace($pattern, '', $html_content);

    echo "✅ Affichage mode STANDARD :\n";
    echo "   - Total payé\n";
}

// Supprimer toutes les balises Smarty restantes
$html_content = preg_replace('/{if[^}]*}/', '', $html_content);
$html_content = preg_replace('/{else}/', '', $html_content);
$html_content = preg_replace('/{\/if}/', '', $html_content);

// Remplacer les variables
foreach ($replacements as $key => $value) {
    $html_content = str_replace($key, $value, $html_content);
}

// Créer l'email
$boundary = md5(uniqid(time()));

$headers = "From: Ma Boutique <noreply@example.com>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";

$text_content = "Confirmation de commande #$order_reference\n\n";
$text_content .= "Bonjour Jean Dupont,\n\n";
$text_content .= "Votre commande a bien été enregistrée.\n\n";

if ($has_partial_payment) {
    $text_content .= "Total à payer : " . number_format($total_to_pay, 2, ',', ' ') . " €\n";
    $text_content .= "Acompte versé : " . number_format($amount_paid, 2, ',', ' ') . " €\n";
    $text_content .= "Reste à payer : " . number_format($amount_remaining, 2, ',', ' ') . " €\n\n";
} else {
    $text_content .= "Total payé : " . number_format($total_to_pay, 2, ',', ' ') . " €\n\n";
}

$text_content .= "Merci de votre confiance !\n";

$message = "--$boundary\r\n";
$message .= "Content-Type: text/plain; charset=UTF-8\r\n";
$message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
$message .= $text_content . "\r\n";
$message .= "--$boundary\r\n";
$message .= "Content-Type: text/html; charset=UTF-8\r\n";
$message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
$message .= $html_content . "\r\n";
$message .= "--$boundary--\r\n";

// Connexion SMTP
echo "Connexion à MailHog ($smtp_host:$smtp_port)...\n";

$smtp = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);

if (!$smtp) {
    echo "❌ Erreur : Impossible de se connecter à MailHog\n";
    echo "   $errstr ($errno)\n";
    exit(1);
}

function smtp_read($smtp) {
    $response = '';
    while ($line = fgets($smtp, 515)) {
        $response .= $line;
        if (substr($line, 3, 1) == ' ') break;
    }
    return $response;
}

function smtp_send($smtp, $command, $show = true) {
    if ($show) echo "  > $command\n";
    fputs($smtp, $command . "\r\n");
    $response = smtp_read($smtp);
    if ($show) echo "  < " . trim($response) . "\n";
    return $response;
}

echo "\nEnvoi de l'email...\n\n";

smtp_read($smtp);
smtp_send($smtp, "EHLO localhost");
smtp_send($smtp, "MAIL FROM:<noreply@example.com>");
smtp_send($smtp, "RCPT TO:<$to_email>");
smtp_send($smtp, "DATA");

$email_content = "To: $to_name <$to_email>\r\n";
$email_content .= "Subject: =?UTF-8?B?" . base64_encode("Confirmation de commande #$order_reference") . "?=\r\n";
$email_content .= $headers . "\r\n";
$email_content .= $message;

fputs($smtp, $email_content . "\r\n.\r\n");
$response = smtp_read($smtp);
echo "  < " . trim($response) . "\n";

smtp_send($smtp, "QUIT", false);
fclose($smtp);

echo "\n✅ Email envoyé avec succès !\n\n";
echo "┌─────────────────────────────────────────────┐\n";
echo "│ 📧 Vérifiez MailHog                        │\n";
echo "│                                             │\n";
echo "│ URL : http://localhost:8025                 │\n";
echo "│                                             │\n";

if ($has_partial_payment) {
    echo "│ L'email affiche :                           │\n";
    echo "│  - Total à payer    : 20,90 €              │\n";
    echo "│  - Acompte          : 5,00 €               │\n";
    echo "│  - Reste à payer    : 15,90 €              │\n";
    echo "│                                             │\n";
    echo "│ ❌ PAS de ligne \"Total payé\"               │\n";
} else {
    echo "│ L'email affiche :                           │\n";
    echo "│  - Total payé       : 20,90 €              │\n";
}

echo "└─────────────────────────────────────────────┘\n";
