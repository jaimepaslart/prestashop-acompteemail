# Module Acompte Email

Module PrestaShop pour afficher correctement les acomptes dans les emails de confirmation de commande.

## Problème résolu

Quand un client paie seulement une partie de sa commande, l'email de confirmation affichait "Total payé : XX EUR" avec le montant total de la commande, créant une confusion.

Ce module corrige ce comportement en affichant :
- Total à payer : Montant total de la commande
- Acompte versé : Montant déjà payé
- Reste à payer : Montant restant dû

## Cas d'usage

### Commande partiellement payée
Exemple : Commande de 1 000 EUR avec acompte de 250 EUR

L'email affiche :
```
Total à payer    : 1 000,00 EUR
Acompte versé    : 250,00 EUR
Reste à payer    : 750,00 EUR
```

### Commande totalement payée
L'email affiche le comportement classique :
```
Total payé : 1 000,00 EUR
```

## Installation

### 1. Installer le module

Via le back-office :
1. Modules > Module Manager
2. Rechercher "Acompte Email"
3. Cliquer sur "Installer"

Ou via ligne de commande :
```bash
cp -r acompteemail /chemin/vers/prestashop/modules/
```

### 2. Vider le cache

```bash
rm -rf var/cache/*
```

Ou depuis le BO : Paramètres avancés > Performances > Vider le cache

### 3. Modifier le template email

Le template `mails/fr/order_conf.html` doit être modifié pour utiliser les nouvelles variables.

Backup disponible : `mails/fr/order_conf.html.bak`

## Fichiers

### Module
- `modules/acompteemail/acompteemail.php` - Classe principale
- `modules/acompteemail/index.php` - Sécurité

### Template email
- `mails/fr/order_conf.html` - Template modifié
- `mails/fr/order_conf.html.bak` - Backup

## Test

### Test commande avec acompte

1. Créer une commande dans le BO
2. Ajouter un paiement partiel (Commandes > Afficher > Section Paiement)
3. Renvoyer l'email de confirmation
4. Vérifier l'affichage

Résultat attendu : Affichage de "Total à payer", "Acompte versé" et "Reste à payer"

### Test commande totalement payée

1. Créer une commande
2. Ajouter un paiement pour le montant total
3. Renvoyer l'email

Résultat attendu : Affichage de "Total payé" uniquement

## Logique technique

### Hook utilisé
Le module s'enregistre sur le hook `actionEmailSendBefore` appelé avant l'envoi de chaque email.

### Calcul
```php
// Récupération des paiements
$payments = $order->getOrderPayments();

// Somme des paiements
$paid = 0;
foreach ($payments as $payment) {
    $paid += $payment->amount;
}

// Reste à payer
$remaining = max(0, $total - $paid);
```

### Variables injectées

Variables brutes (pour conditions) :
- `{amount_paid_raw}` - Montant payé (float)
- `{amount_remaining_raw}` - Reste à payer (float)
- `{total_to_pay_raw}` - Total de la commande (float)

Variables formatées (pour affichage) :
- `{amount_paid}` - Acompte formaté avec devise
- `{amount_remaining}` - Reste formaté avec devise
- `{total_to_pay}` - Total formaté avec devise
- `{is_fully_paid}` - 1 si soldé, 0 sinon

## Gestion des erreurs

Le module utilise try/catch pour éviter qu'une erreur empêche l'envoi de l'email. En cas d'erreur, le template affiche le comportement par défaut et l'erreur est loguée.

## Compatibilité

- PrestaShop 1.7.0+
- PHP 7.1+
- Compatible avec paiements multiples

## Dépannage

### Le module ne s'affiche pas
- Vérifier les permissions : `chmod 755 modules/acompteemail`
- Vider le cache PrestaShop

### L'email affiche toujours "Total payé"
- Vérifier que le module est installé et actif
- Vider le cache
- Vérifier qu'il y a un paiement partiel enregistré
- Consulter les logs : Paramètres avancés > Logs

### Variables non remplacées
- Vérifier la syntaxe `{variable}` avec accolades
- Tester avec une vraie commande

## Logs

En cas d'erreur, consulter :
- Back-Office > Paramètres avancés > Logs
- Rechercher "AcompteEmail"

## Désinstallation

1. BO > Modules > Module Manager
2. Rechercher "Acompte Email"
3. Cliquer sur "Désinstaller"

Pour restaurer le template original :
```bash
cp mails/fr/order_conf.html.bak mails/fr/order_conf.html
```

## Auteur

Paul Bihr

Version 1.0.0
