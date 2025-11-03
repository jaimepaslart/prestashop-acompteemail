# Installation du module AcompteEmail sur un autre PrestaShop

## 📦 Fichier à transférer

Le module est packagé dans le fichier : **`acompteemail.zip`** (11 KB)

---

## 🚀 Installation rapide (3 étapes)

### Étape 1 : Installer le module

1. Connectez-vous au **Back-Office** de votre PrestaShop
2. Allez dans **Modules** > **Module Manager**
3. Cliquez sur **"Uploader un module"** (bouton en haut à droite)
4. Glissez-déposez le fichier **`acompteemail.zip`**
5. Cliquez sur **"Installer"**
6. Le module est maintenant installé et actif ✅

### Étape 2 : Modifier le template email

Le module injecte les variables, mais il faut aussi modifier le template HTML de l'email de confirmation de commande.

**Option A : Modification automatique (recommandée)**

Si vous avez accès SSH ou FTP :

1. **Localiser le template** :
   ```
   /mails/fr/order_conf.html
   ```
   (Remplacez `fr` par votre langue : `en`, `es`, `de`, etc.)

2. **Faire une sauvegarde** :
   ```bash
   cp mails/fr/order_conf.html mails/fr/order_conf.html.bak
   ```

3. **Chercher la section "Total payé"** dans le fichier (vers la ligne 817) :
   ```html
   <tr class="order_summary">
     <td colspan="3" align="right">Total payé</td>
     <td colspan="3">{total_paid}</td>
   </tr>
   ```

4. **Remplacer par ce code** :
   ```html
   <!-- Affichage conditionnel selon paiement partiel ou complet -->
   {if isset($amount_remaining_raw) && $amount_remaining_raw > 0 && isset($amount_paid_raw) && $amount_paid_raw > 0}
   <!-- CAS 1 : Paiement partiel (acompte) -->
   <tr class="order_summary">
     <td colspan="3" align="right" style="font-weight: 600;">Total à payer</td>
     <td colspan="3" style="font-weight: 600;">{total_to_pay}</td>
   </tr>
   <tr class="order_summary">
     <td colspan="3" align="right">Acompte</td>
     <td colspan="3">{amount_paid}</td>
   </tr>
   <tr class="order_summary">
     <td colspan="3" align="right" style="font-weight: 600;">Reste à payer</td>
     <td colspan="3" style="font-weight: 600;">{amount_remaining}</td>
   </tr>
   {else}
   <!-- CAS 2 : Paiement complet -->
   <tr class="order_summary">
     <td colspan="3" align="right" style="font-weight: 600;">Total payé</td>
     <td colspan="3" style="font-weight: 600;">{total_paid}</td>
   </tr>
   {/if}
   ```

5. **Vider le cache** :
   - Back-Office > Paramètres avancés > Performance > **Vider le cache**

**Option B : Copier le template modifié**

Si vous avez déjà le template modifié de ce PrestaShop :

1. Copiez le fichier `/mails/fr/order_conf.html` de ce PrestaShop
2. Remplacez le même fichier sur l'autre PrestaShop
3. Videz le cache

### Étape 3 : Tester le module

**Test 1 : Vérifier l'installation**
1. Back-Office > Modules > Module Manager
2. Recherchez "Acompte Email"
3. Vérifiez qu'il est bien installé et activé ✅

**Test 2 : Envoyer un email de test**
1. Créez une commande de test (ou utilisez une existante)
2. Ajoutez un paiement partiel à la commande
3. Renvoyez l'email de confirmation depuis le Back-Office
4. Vérifiez que l'email contient :
   - Total à payer
   - Acompte
   - Reste à payer

---

## 🎯 Compatibilité

- **PrestaShop** : 1.7.0.0 à 1.7.8.x
- **PHP** : 7.1 à 8.1
- **Modules requis** : Aucun

---

## 📋 Fichiers du module

```
acompteemail/
├── acompteemail.php          # Fichier principal du module
├── index.php                 # Sécurité (empêche l'accès direct)
├── README.md                 # Documentation utilisateur
├── GUIDE_INSTALLATION.md     # Guide détaillé
└── TECHNICAL_SUMMARY.md      # Documentation technique
```

---

## ❓ Résolution de problèmes

### Le module ne s'installe pas
- Vérifiez les permissions : `chmod 755 modules/acompteemail`
- Vérifiez que le dossier `modules/` est accessible en écriture

### Les variables ne s'affichent pas dans l'email
1. Vérifiez que le module est bien installé et actif
2. Vérifiez que le template `order_conf.html` a été modifié
3. Videz le cache PrestaShop
4. Vérifiez les logs : `var/logs/prod.log`

### L'email affiche toujours "Total payé" au lieu de l'acompte
- Le template n'a pas été modifié correctement
- Suivez l'**Étape 2** ci-dessus

---

## 📞 Support

Pour plus d'informations, consultez :
- [README.md](modules/acompteemail/README.md) - Documentation complète
- [GUIDE_INSTALLATION.md](modules/acompteemail/GUIDE_INSTALLATION.md) - Guide détaillé
- [TECHNICAL_SUMMARY.md](modules/acompteemail/TECHNICAL_SUMMARY.md) - Documentation technique

---

## 🎁 Bonus : Script de test

Pour tester l'envoi d'email sans passer de commande réelle, copiez aussi le fichier :
- `test_acompte_email_smarty.php`

Et exécutez-le :
```bash
php test_acompte_email_smarty.php
```

Cela enverra un email de test à MailHog ou votre serveur SMTP configuré.
