# PrestaShop 1.7.6.5 - Module AcompteEmail

Ce dépôt contient PrestaShop 1.7.6.5 avec le module **AcompteEmail** pour afficher correctement les paiements partiels dans les emails de confirmation de commande.

## 📦 Contenu du module

### Module AcompteEmail
- **Emplacement** : `modules/acompteemail/`
- **Version** : 1.0.0
- **Auteur** : Paul Bihr
- **Licence** : MIT

### Fonctionnalité
Affiche dans l'email de confirmation de commande :
- **Total à payer** : Montant total de la commande
- **Acompte** : Montant déjà versé
- **Reste à payer** : Montant restant dû

**Exemple visuel :**
```
Total à payer    : 20,90 €
Acompte          : 5,00 €
Reste à payer    : 15,90 €
```

## 📂 Structure du dépôt

```
.
├── modules/acompteemail/              # Module AcompteEmail
│   ├── acompteemail.php              # Code principal
│   ├── index.php                     # Sécurité
│   ├── README.md                     # Documentation utilisateur
│   ├── GUIDE_INSTALLATION.md         # Guide d'installation
│   └── TECHNICAL_SUMMARY.md          # Documentation technique
│
├── mails/                            # Templates email modifiés
│   ├── fr/order_conf.html           # Template français
│   └── en/order_conf.html           # Template anglais
│
├── INSTALLATION_AUTRE_PRESTASHOP.md  # Guide pour autres instances
└── README_MODULE_ACOMPTE.md          # Documentation complète
```

## 🚀 Installation

### Sur cette instance PrestaShop
Le module est déjà installé et configuré.

### Sur une autre instance PrestaShop

1. **Copier le module** :
   ```bash
   cp -r modules/acompteemail /path/to/other/prestashop/modules/
   ```

2. **Copier les templates email** :
   ```bash
   cp mails/fr/order_conf.html /path/to/other/prestashop/mails/fr/
   cp mails/en/order_conf.html /path/to/other/prestashop/mails/en/
   ```

3. **Installer via le Back-Office** :
   - Allez dans **Modules** > **Module Manager**
   - Recherchez "Acompte Email"
   - Cliquez sur **"Installer"**

4. **Vider le cache** :
   - **Paramètres avancés** > **Performance** > **Vider le cache**

Pour plus de détails, consultez [INSTALLATION_AUTRE_PRESTASHOP.md](INSTALLATION_AUTRE_PRESTASHOP.md)

## 📚 Documentation

- **[README_MODULE_ACOMPTE.md](README_MODULE_ACOMPTE.md)** - Documentation complète et méthodes d'installation
- **[INSTALLATION_AUTRE_PRESTASHOP.md](INSTALLATION_AUTRE_PRESTASHOP.md)** - Guide d'installation détaillé
- **[modules/acompteemail/README.md](modules/acompteemail/README.md)** - Documentation utilisateur du module
- **[modules/acompteemail/GUIDE_INSTALLATION.md](modules/acompteemail/GUIDE_INSTALLATION.md)** - Guide pas à pas
- **[modules/acompteemail/TECHNICAL_SUMMARY.md](modules/acompteemail/TECHNICAL_SUMMARY.md)** - Documentation technique

## 🔧 Compatibilité

- **PrestaShop** : 1.7.0.0 à 1.7.8.x
- **PHP** : 7.1 à 8.1
- **Base de données** : MySQL 5.6+

## ✨ Fonctionnement

Le module utilise le hook `actionEmailSendBefore` pour :
1. Détecter les emails de confirmation de commande
2. Récupérer les paiements enregistrés dans `ps_order_payment`
3. Calculer automatiquement l'acompte et le reste à payer
4. Injecter les variables dans le template email

**Calcul automatique** :
- Total = `ps_orders.total_paid_tax_incl`
- Acompte = Somme de `ps_order_payment.amount`
- Reste = Total - Acompte

## 📧 Templates modifiés

Les templates email utilisent des conditions Smarty pour afficher :
- **Si paiement partiel** : Total à payer, Acompte, Reste à payer
- **Si paiement complet** : Total payé

## 🎯 Commits principaux

```bash
# Voir l'historique du module
git log --oneline -- modules/acompteemail/

# Voir les modifications des templates
git log --oneline -- mails/fr/order_conf.html
```

## 🛠️ Développement

Le dépôt contient uniquement :
- Le code source du module
- Les templates email modifiés
- La documentation

Les scripts de test et fichiers temporaires ne sont pas versionnés (voir `.gitignore`).

## 📄 Licence

MIT

## 👤 Auteur

**Paul Bihr**

---

**Version PrestaShop** : 1.7.6.5
**Version Module** : 1.0.0
**Date de création** : Novembre 2025
