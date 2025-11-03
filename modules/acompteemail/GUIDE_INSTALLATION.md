# Guide d'installation - Module Acompte Email

## Installation rapide (5 minutes)

### Étape 1 : Copier les fichiers

Si le module n'est pas déjà en place :

```bash
# Sur le serveur de production
cd /path/to/your/prestashop
cp -r /path/to/modules/acompteemail modules/
chmod -R 755 modules/acompteemail
```

### Étape 2 : Installer le module

1. Connectez-vous au **Back-Office** PrestaShop
2. Menu : **Modules > Module Manager** (ou **Modules et Services**)
3. Dans la barre de recherche, tapez : **"Acompte"**
4. Le module "**Acompte Email**" devrait apparaître
5. Cliquez sur **"Installer"**
6. Un message de confirmation "Module installé" devrait s'afficher

> **Note** : Si le module n'apparaît pas, videz le cache (Étape 3) puis rechargez la page.

### Étape 3 : Vider le cache

**Via le Back-Office :**
1. Menu : **Paramètres avancés > Performances**
2. Section "Vider le cache"
3. Cliquez sur **"Vider le cache"**

**Ou via ligne de commande :**
```bash
cd /path/to/your/prestashop
rm -rf var/cache/*
```

### Étape 4 : Vérifier l'installation

1. Menu : **Modules > Module Manager**
2. Recherchez "**Acompte Email**"
3. Statut doit être : **Activé** ✅

---

## Test fonctionnel

### Test 1 : Email avec acompte

#### Via le Back-Office

1. **Créer une commande test** :
   - Menu : **Commandes > Commandes**
   - Cliquez sur une commande existante (ou créez-en une)
   - Exemple : Commande de 1 000 €

2. **Ajouter un paiement partiel** :
   - Dans la commande, section **"Paiement"**
   - Cliquez sur le bouton **"+"** (Ajouter un paiement)
   - Remplissez :
     - **Montant** : 100.00 (10% du total)
     - **Date** : Date du jour
     - **Moyen de paiement** : Virement bancaire
   - Cliquez sur **"Ajouter"**

3. **Renvoyer l'email de confirmation** :
   - Section **"Email"** ou **"Documents"**
   - Cliquez sur **"Renvoyer l'email de confirmation de commande"**

4. **Vérifier l'email reçu** :
   - Ouvrez l'email (client email ou MailHog si configuré)
   - **Résultat attendu** :
     ```
     Total à payer    : 1 000,00 €
     Acompte          : 100,00 €
     Reste à payer    : 900,00 €
     ```

### Test 2 : Email avec paiement complet

1. **Créer une nouvelle commande** de 500 €
2. **Ajouter un paiement complet** de 500 €
3. **Renvoyer l'email**
4. **Résultat attendu** :
   ```
   Total payé : 500,00 €
   ```
   *(Pas de lignes "Acompte" / "Reste à payer")*

### Test 3 : Paiements multiples

1. **Créer une commande** de 10 000 €
2. **Ajouter un premier paiement** de 3 000 €
3. **Renvoyer l'email** → Vérifier : Acompte 3 000 €, Reste 7 000 €
4. **Ajouter un deuxième paiement** de 3 000 €
5. **Renvoyer l'email** → Vérifier : Acompte 6 000 €, Reste 4 000 €
6. **Ajouter le dernier paiement** de 4 000 €
7. **Renvoyer l'email** → Vérifier : "Total payé : 10 000 €"

---

## Cas d'usage réel (exemple EPABHNVQM)

**Commande** : 33 723,00 €
**Acompte payé** : 3 377,70 €

**Email client affichera** :
```
┌─────────────────────────────────┐
│ Total à payer    : 33 723,00 € │
│ Acompte          : 3 377,70 €  │
│ Reste à payer    : 30 345,30 € │
└─────────────────────────────────┘
```

**Au lieu de** (ancien comportement) :
```
┌─────────────────────────────────┐
│ Total payé       : 33 723,00 € │  ❌ FAUX !
└─────────────────────────────────┘
```

---

## Dépannage

### Le module n'apparaît pas dans la liste

**Solution** :
```bash
# Vérifier les permissions
chmod -R 755 modules/acompteemail

# Vider le cache
rm -rf var/cache/*

# Vérifier que les fichiers sont bien présents
ls -la modules/acompteemail/
```

Vous devriez voir :
- `acompteemail.php`
- `index.php`
- `README.md`

### L'email affiche toujours "Total payé" (même avec acompte)

**Causes possibles** :

1. **Le module n'est pas installé**
   - Vérifiez dans Modules > Module Manager
   - Le statut doit être "Activé"

2. **Le cache n'a pas été vidé**
   - Videz le cache (voir Étape 3)

3. **Aucun paiement enregistré**
   - Vérifiez dans la commande, section "Paiement"
   - Il doit y avoir au moins un paiement enregistré

4. **Le template n'a pas été modifié**
   - Vérifiez que le fichier `mails/fr/order_conf.html` contient les modifications
   - Recherchez `{amount_paid}` dans le fichier

**Vérification** :
```bash
grep "amount_paid" mails/fr/order_conf.html
```

Doit afficher plusieurs lignes contenant `{amount_paid}`, `{amount_remaining}`, etc.

### Variables non remplacées ({amount_paid} s'affiche tel quel)

**Cause** : Le module n'injecte pas les variables

**Solution** :
1. Vérifiez les logs :
   - BO > Paramètres avancés > Logs
   - Recherchez "AcompteEmail"

2. Vérifiez que le hook est bien enregistré :
   ```sql
   SELECT * FROM ps_hook_module
   WHERE id_module = (SELECT id_module FROM ps_module WHERE name = 'acompteemail');
   ```

3. Réinstallez le module :
   - Désinstaller
   - Réinstaller
   - Vider le cache

---

## Vérification post-installation (Checklist)

- [ ] Le module "Acompte Email" est visible dans Module Manager
- [ ] Le statut du module est "Activé"
- [ ] Le cache PrestaShop a été vidé
- [ ] Le fichier `mails/fr/order_conf.html` contient les modifications (vérifier avec `grep`)
- [ ] Un email de test avec acompte affiche correctement les 3 lignes
- [ ] Un email de test avec paiement complet affiche "Total payé"
- [ ] Les logs ne contiennent pas d'erreur "AcompteEmail"

---

## Support

### Consulter les logs

1. BO > Paramètres avancés > Logs
2. Filtre sur "AcompteEmail" ou "Email"
3. Vérifier les erreurs

### Restaurer l'ancien template (rollback)

Si vous voulez revenir à l'ancien comportement :

```bash
# Backup automatique créé
cp mails/fr/order_conf.html.bak mails/fr/order_conf.html

# Désinstaller le module
# BO > Modules > Module Manager > Acompte Email > Désinstaller
```

### Fichiers modifiés

Le module modifie **uniquement** :
- `modules/acompteemail/` (nouveau dossier)
- `mails/fr/order_conf.html` (backup : `.bak`)

**Aucune modification du core PrestaShop** ✅

---

## Mise en production

### Transfert vers serveur de production

```bash
# Depuis votre machine locale
scp -r modules/acompteemail user@production:/path/to/prestashop/modules/
scp mails/fr/order_conf.html user@production:/path/to/prestashop/mails/fr/

# Sur le serveur de production
cd /path/to/prestashop
chmod -R 755 modules/acompteemail
rm -rf var/cache/*
```

Puis suivez les étapes d'installation dans le BO.

### Rollback rapide (si problème)

```bash
# Restaurer l'ancien template
cp mails/fr/order_conf.html.bak mails/fr/order_conf.html

# Désactiver le module via BO
# Ou supprimer le dossier
rm -rf modules/acompteemail

# Vider le cache
rm -rf var/cache/*
```

---

**Installation réussie ?** 🎉

Vous pouvez maintenant envoyer des emails de confirmation avec affichage correct de l'acompte !

Pour toute question : consultez le [README.md](README.md) complet.
