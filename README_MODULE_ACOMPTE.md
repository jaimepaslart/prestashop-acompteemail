# Module AcompteEmail - Installation sur d'autres PrestaShop

## 📦 Contenu du module dans Git

Le module **AcompteEmail** et tous les fichiers nécessaires sont disponibles dans ce dépôt Git.

### Fichiers du module
```
modules/acompteemail/
├── acompteemail.php          # Code principal du module
├── index.php                 # Fichier de sécurité
├── README.md                 # Documentation utilisateur
├── GUIDE_INSTALLATION.md     # Guide d'installation détaillé
└── TECHNICAL_SUMMARY.md      # Documentation technique
```

### Templates email modifiés
```
mails/fr/order_conf.html      # Template FR avec support acompte
mails/en/order_conf.html      # Template EN avec support acompte
```

### Documentation
```
INSTALLATION_AUTRE_PRESTASHOP.md  # Guide d'installation pour autres PS
test_acompte_email_smarty.php     # Script de test fonctionnel
```

---

## 🚀 Installation rapide sur un autre PrestaShop

### Méthode 1 : Clone complet du dépôt

Si le nouveau PrestaShop est vide ou si vous voulez tout le projet :

```bash
# Cloner le dépôt
git clone <url-du-depot> prestashop-avec-acompte
cd prestashop-avec-acompte

# Le module et les templates sont déjà en place !
```

### Méthode 2 : Extraire uniquement le module

Si vous voulez juste le module pour un PrestaShop existant :

```bash
# 1. Cloner le dépôt dans un dossier temporaire
git clone <url-du-depot> /tmp/prestashop-source

# 2. Copier le module dans votre PrestaShop
cp -r /tmp/prestashop-source/modules/acompteemail /path/to/your/prestashop/modules/

# 3. Copier le template email modifié
cp /tmp/prestashop-source/mails/fr/order_conf.html /path/to/your/prestashop/mails/fr/

# 4. (Optionnel) Copier le script de test
cp /tmp/prestashop-source/test_acompte_email_smarty.php /path/to/your/prestashop/

# 5. Nettoyer
rm -rf /tmp/prestashop-source
```

### Méthode 3 : Téléchargement direct via Git (sparse checkout)

Pour télécharger uniquement le module sans cloner tout le dépôt :

```bash
# 1. Initialiser un dépôt Git vide
mkdir acompte-module && cd acompte-module
git init
git remote add origin <url-du-depot>

# 2. Activer le sparse checkout
git config core.sparseCheckout true

# 3. Spécifier les fichiers à télécharger
cat > .git/info/sparse-checkout << EOF
modules/acompteemail/
mails/fr/order_conf.html
mails/en/order_conf.html
test_acompte_email_smarty.php
INSTALLATION_AUTRE_PRESTASHOP.md
EOF

# 4. Télécharger uniquement ces fichiers
git pull origin main

# 5. Les fichiers sont maintenant disponibles localement
ls -la modules/acompteemail/
```

---

## 📋 Après l'installation

1. **Installer le module via le Back-Office** :
   - Modules > Module Manager
   - Rechercher "Acompte Email"
   - Cliquer sur "Installer"

2. **Vérifier le template email** :
   - Le fichier `mails/fr/order_conf.html` doit contenir les modifications
   - Chercher les variables `{amount_paid}`, `{amount_remaining}`, `{total_to_pay}`

3. **Vider le cache** :
   - Paramètres avancés > Performance > Vider le cache

4. **Tester** :
   ```bash
   php test_acompte_email_smarty.php
   ```
   Puis vérifier l'email dans MailHog : http://localhost:8025

---

## 📚 Documentation complète

- **[README.md](modules/acompteemail/README.md)** - Vue d'ensemble du module
- **[GUIDE_INSTALLATION.md](modules/acompteemail/GUIDE_INSTALLATION.md)** - Guide pas à pas
- **[TECHNICAL_SUMMARY.md](modules/acompteemail/TECHNICAL_SUMMARY.md)** - Détails techniques
- **[INSTALLATION_AUTRE_PRESTASHOP.md](INSTALLATION_AUTRE_PRESTASHOP.md)** - Installation sur autre PS

---

## 🔍 Vérifier que tout est dans Git

```bash
# Vérifier le module
git ls-files modules/acompteemail/

# Vérifier les templates
git ls-files mails/*/order_conf.html

# Vérifier la documentation
git ls-files *INSTALLATION*.md
```

---

## 🎯 Historique Git

Les commits importants pour le module :

```bash
436266c - docs: Add installation guide and test script for other PrestaShop instances
376e33d - docs: Update author from Claude Code to Paul Bihr
79be16e - docs: Add delivery recap for AcompteEmail module
818aced - docs: Add installation guide and technical summary for AcompteEmail module
10603f3 - feat: Add AcompteEmail module for partial payment display in order confirmation emails
```

Pour voir les détails d'un commit :
```bash
git show 10603f3
```

---

## ✅ Checklist d'installation

- [ ] Module copié dans `modules/acompteemail/`
- [ ] Template `mails/fr/order_conf.html` copié
- [ ] Module installé via le Back-Office
- [ ] Cache vidé
- [ ] Test effectué avec `test_acompte_email_smarty.php`
- [ ] Email vérifié dans MailHog
- [ ] Affichage correct : Total à payer, Acompte, Reste à payer

---

## 🆘 Support

En cas de problème, consultez :
1. [INSTALLATION_AUTRE_PRESTASHOP.md](INSTALLATION_AUTRE_PRESTASHOP.md) pour le guide détaillé
2. Les logs PrestaShop : `var/logs/prod.log`
3. L'historique Git pour voir les modifications

---

**Auteur** : Paul Bihr
**Version** : 1.0.0
**Licence** : MIT
**Compatibilité** : PrestaShop 1.7.0.0 - 1.7.8.x
