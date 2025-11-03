# Module Acompte Email

Module PrestaShop 1.7.6.5 pour afficher correctement l'acompte payé et le reste à payer dans les emails de confirmation de commande.

## 📋 Contexte

Lorsqu'une commande est partiellement payée (acompte), l'email de confirmation affiche par défaut "Total payé : XX €" avec le montant total de la commande, ce qui est trompeur pour le client.

Ce module corrige ce comportement en affichant :
- **Total à payer** : Montant total de la commande
- **Acompte** : Montant déjà payé
- **Reste à payer** : Montant restant dû

## 🎯 Fonctionnalités

### Cas 1 : Commande partiellement payée (acompte)
Exemple : Commande de 33 723,00 € avec acompte de 3 377,70 €

L'email affichera :
```
Total à payer    : 33 723,00 €
Acompte          : 3 377,70 €
Reste à payer    : 30 345,30 €
```

### Cas 2 : Commande totalement payée
L'email affichera le comportement classique :
```
Total payé : 33 723,00 €
```

### Cas 3 : Aucun paiement enregistré
Comportement par défaut de PrestaShop (Total payé)

## 🔧 Installation

### Étape 1 : Installer le module

1. Connectez-vous au **Back-Office** de PrestaShop
2. Allez dans **Modules > Module Manager**
3. Recherchez "**Acompte Email**"
4. Cliquez sur "**Installer**"

Ou via la ligne de commande :
```bash
cd /path/to/prestashop
cp -r modules/acompteemail /path/to/production/modules/
```

### Étape 2 : Vider le cache

```bash
rm -rf var/cache/*
```

Ou depuis le BO : **Paramètres avancés > Performances > Vider le cache**

### Étape 3 : Template d'email modifié

Le template `/mails/fr/order_conf.html` a été modifié.

**Important** : Si vous mettez à jour PrestaShop, pensez à sauvegarder ce fichier car il pourrait être écrasé.

Backup disponible : `/mails/fr/order_conf.html.bak`

## 📁 Fichiers modifiés

### Fichiers du module
- `modules/acompteemail/acompteemail.php` - Classe principale du module
- `modules/acompteemail/index.php` - Fichier de sécurité

### Template email
- `mails/fr/order_conf.html` - Template de confirmation de commande modifié
- `mails/fr/order_conf.html.bak` - Backup de l'original

## 🧪 Comment tester

### Test 1 : Commande avec acompte

1. Créez une commande test dans le BO
2. Ajoutez un paiement partiel :
   - Allez dans **Commandes > Afficher la commande**
   - Section "Paiement" > Ajouter un paiement
   - Montant : entrez un montant inférieur au total (ex: 10% du total)
3. Renvoyez l'email de confirmation :
   - Dans la commande, section "Email" > "Renvoyer l'email de confirmation de commande"
4. Vérifiez l'email reçu dans MailHog (http://localhost:8025) ou votre client email

**Résultat attendu** : L'email doit afficher "Total à payer", "Acompte" et "Reste à payer"

### Test 2 : Commande totalement payée

1. Créez une commande test
2. Ajoutez un paiement pour le montant total
3. Renvoyez l'email

**Résultat attendu** : L'email doit afficher uniquement "Total payé" (comportement classique)

### Test 3 : Paiements multiples

1. Créez une commande test de 1000 €
2. Ajoutez un premier paiement de 300 €
3. Renvoyez l'email → Doit afficher acompte de 300 € et reste de 700 €
4. Ajoutez un second paiement de 200 €
5. Renvoyez l'email → Doit afficher acompte de 500 € et reste de 500 €
6. Ajoutez le dernier paiement de 500 €
7. Renvoyez l'email → Doit afficher "Total payé : 1000 €"

## 🔍 Logique technique

### Hook utilisé
Le module s'enregistre sur le hook **`actionEmailSendBefore`** qui est appelé avant l'envoi de chaque email.

### Calcul de l'acompte
```php
// Récupération de tous les paiements
$payments = $order->getOrderPayments();

// Somme des paiements
$paid = 0;
foreach ($payments as $payment) {
    $paid += $payment->amount;
}

// Reste à payer
$remaining = max(0, $total - $paid);
```

### Variables ajoutées au template

Variables brutes (pour conditions) :
- `{amount_paid_raw}` - Montant payé (float)
- `{amount_remaining_raw}` - Reste à payer (float)
- `{total_to_pay_raw}` - Total de la commande (float)

Variables formatées (pour affichage) :
- `{amount_paid}` - Acompte formaté avec devise
- `{amount_remaining}` - Reste formaté avec devise
- `{total_to_pay}` - Total formaté avec devise
- `{is_fully_paid}` - Flag 1 si soldé, 0 sinon

## ⚠️ Notes importantes

### Gestion des erreurs
Le module utilise un try/catch pour éviter qu'une erreur empêche l'envoi de l'email. En cas d'erreur, les variables ne seront pas ajoutées et le template affichera le comportement par défaut.

### Compatibilité
- PrestaShop 1.7.0.0 à 1.7.6.5+
- Fonctionne avec les templates d'email legacy (format `{variable}`)
- Compatible avec les paiements multiples

### Performance
Impact minimal : le module ne s'exécute que lors de l'envoi d'emails de confirmation de commande (event rare).

## 🐛 Dépannage

### Le module ne s'affiche pas dans la liste
- Vérifiez les permissions des fichiers : `chmod 755 modules/acompteemail`
- Videz le cache PrestaShop

### L'email affiche toujours "Total payé"
- Vérifiez que le module est bien installé et actif
- Videz le cache
- Vérifiez qu'il y a bien un paiement partiel enregistré sur la commande
- Consultez les logs : **Paramètres avancés > Logs**

### Variables non remplacées dans l'email
- Le template legacy de PrestaShop utilise la syntaxe `{variable}`
- Vérifiez que les accolades sont bien présentes
- Testez avec une vraie commande (pas en mode test/debug)

## 📝 Logs

En cas d'erreur, le module enregistre des logs dans :
- **Back-Office > Paramètres avancés > Logs**
- Recherchez "AcompteEmail"

## 🔄 Désinstallation

Pour désinstaller le module :
1. BO > Modules > Module Manager
2. Recherchez "Acompte Email"
3. Cliquez sur "Désinstaller"

**Important** : Le template email modifié restera en place. Pour revenir à l'original :
```bash
cp mails/fr/order_conf.html.bak mails/fr/order_conf.html
```

## 📄 Licence

MIT License

## 👨‍💻 Auteur

Développé par Claude Code pour PrestaShop 1.7.6.5

---

**Version** : 1.0.0
**Date** : 2025-11-03
