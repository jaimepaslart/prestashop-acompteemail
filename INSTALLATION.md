# Guide d'installation

## Prérequis

- PrestaShop 1.7.x
- Accès FTP ou SSH au serveur
- Accès au back-office PrestaShop

## Installation

### Étape 1 : Extraction

Extrayez l'archive `acompte-email-package.zip` sur votre ordinateur.

### Étape 2 : Upload du module

Via FTP ou SSH, copiez le dossier `acompteemail/` vers :
```
/votre-prestashop/modules/acompteemail/
```

### Étape 3 : Upload du template

Copiez le fichier `themes/child_warehouse/mails/fr/order_conf.html` vers l'emplacement correspondant à votre thème :

**Option 1 - Thème child_warehouse :**
```
/votre-prestashop/themes/child_warehouse/mails/fr/order_conf.html
```

**Option 2 - Autre thème :**
```
/votre-prestashop/themes/VOTRE_THEME/mails/fr/order_conf.html
```

**Option 3 - Global (tous thèmes) :**
```
/votre-prestashop/mails/fr/order_conf.html
```

### Étape 4 : Installation du module

1. Connectez-vous au back-office PrestaShop
2. Menu : Modules > Module Manager
3. Recherchez "Acompte Email"
4. Cliquez sur "Installer"
5. Le module s'active automatiquement

### Étape 5 : Cache

1. Menu : Paramètres avancés > Performance
2. Cliquez sur "Vider le cache"

## Vérification

Pour vérifier que l'installation fonctionne :

1. Créez ou modifiez une commande
2. Ajoutez un paiement partiel (exemple : 30% du total)
3. Envoyez l'email de confirmation
4. Vérifiez que l'email affiche :
   - Total à payer
   - Acompte versé
   - Reste à payer

## Adaptation du template

Si vous n'utilisez pas le thème child_warehouse, vous devrez adapter le template :

1. Sauvegardez votre template actuel :
```bash
cp themes/VOTRE_THEME/mails/fr/order_conf.html themes/VOTRE_THEME/mails/fr/order_conf.html.bak
```

2. Ouvrez votre fichier `order_conf.html`

3. Localisez la section affichant le total payé (recherchez `{total_paid}`)

4. Remplacez cette section par le code suivant :

```smarty
{if isset($is_fully_paid) && !$is_fully_paid}
<tr class="order_summary">
    <td align="right" bgcolor="#FDFDFD" colspan="3">Total à payer</td>
    <td align="right" bgcolor="#FDFDFD" colspan="3">{total_to_pay}</td>
</tr>
<tr class="order_summary">
    <td align="right" bgcolor="#FDFDFD" colspan="3" style="color: #28a745;">Acompte versé</td>
    <td align="right" bgcolor="#FDFDFD" colspan="3" style="color: #28a745;">{amount_paid}</td>
</tr>
<tr class="order_summary">
    <td align="right" bgcolor="#FDFDFD" colspan="3" style="color: #dc3545;">Reste à payer</td>
    <td align="right" bgcolor="#FDFDFD" colspan="3" style="color: #dc3545;">{amount_remaining}</td>
</tr>
{else}
<tr class="order_summary">
    <td align="right" bgcolor="#FDFDFD" colspan="3">Total payé</td>
    <td align="right" bgcolor="#FDFDFD" colspan="3">{total_paid}</td>
</tr>
{/if}
```

Note : Adaptez les attributs `style` selon votre template (classes, couleurs, etc.).

## Dépannage

### Le module n'apparaît pas dans la liste

- Vérifiez que tous les fichiers sont présents dans `modules/acompteemail/`
- Vérifiez les permissions : dossiers 755, fichiers 644
- Videz le cache PrestaShop

### L'email n'affiche pas les acomptes

- Vérifiez que le template a bien été modifié
- Vérifiez que le module est activé
- Videz le cache
- Vérifiez les logs : Paramètres avancés > Logs

### Les couleurs ne s'affichent pas

Certains clients email (Gmail, Outlook) filtrent les styles CSS. Les montants restent visibles mais peuvent ne pas être colorés.

## Désinstallation

Si vous souhaitez désinstaller le module :

1. Modules > Module Manager
2. Recherchez "Acompte Email"
3. Cliquez sur "Désinstaller"
4. Restaurez le template original si nécessaire

## Support

Pour les questions techniques, consultez le fichier `acompteemail/README.md` pour plus de détails sur le fonctionnement du module.
