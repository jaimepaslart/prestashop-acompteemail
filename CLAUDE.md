# Contexte PrestaShop 1.7.6.5 - Modules Personnalisés

## Vue d'ensemble du projet

Instance PrestaShop 1.7.6.5 avec modules personnalisés :
- **AcompteEmail** : Gestion des paiements partiels (acomptes) dans les emails
- **ProductStatusInOrder** : Affichage du statut actif/inactif des produits lors de la création de commandes

**Dépôt Git** : https://github.com/jaimepaslart/prestashop-acompteemail.git

---

## Modules Personnalisés

### Module AcompteEmail (`modules/acompteemail/`)
**Version** : 1.0.0
**Auteur** : Paul Bihr
**Licence** : MIT

**Fonctionnalité** :
Affiche dans l'email de confirmation de commande :
- Total à payer
- Acompte versé
- Reste à payer

**Hook principal** : `actionEmailSendBefore`

**Calcul automatique** :
- Total = `ps_orders.total_paid_tax_incl`
- Acompte = Somme de `ps_order_payment.amount`
- Reste = Total - Acompte

### Module ProductStatusInOrder (`modules/productstatusinorder/`)
**Version** : 1.0.0
**Auteur** : Paul Bihr
**Licence** : MIT

**Fonctionnalité** :
Affiche le statut actif/inactif des produits lors de la création de commandes dans le Back-Office :
- 🟢 Badge vert pour produits actifs
- 🔴 Badge rouge pour produits inactifs
- Injection dans le dropdown "Rechercher un produit"

**Hook principal** : `actionAdminControllerSetMedia`

**Technologie** :
- JavaScript : Interception AJAX (`ajaxComplete`)
- CSS : Styles des badges (optionnel avec emojis)
- Utilise le champ `active` déjà présent dans `Product::searchByName()`

**Scripts inclus** :
- `install.php` - Installation automatique
- `diagnostic.php` - Vérification complète
- `clean.php` - Nettoyage/désinstallation

---

## Hooks PrestaShop Utilisés

| Hook | Module | Usage |
|------|--------|-------|
| `actionEmailSendBefore` | acompteemail | Injection des variables d'acompte dans l'email |
| `actionAdminControllerSetMedia` | productstatusinorder | Chargement JS/CSS pour badges statut produits |
| `displayHeader` | Natifs | Divers modules natifs |
| `displayFooter` | Natifs | Divers modules natifs |

---

## Structure des Fichiers Modifiés

```
/
├── modules/acompteemail/              # Module paiement partiel
│   ├── acompteemail.php              # Classe principale
│   ├── index.php                     # Sécurité
│   ├── README.md                     # Documentation
│   ├── GUIDE_INSTALLATION.md         # Guide installation
│   └── TECHNICAL_SUMMARY.md          # Doc technique
│
├── modules/productstatusinorder/      # Module statut produits
│   ├── productstatusinorder.php      # Classe principale
│   ├── install.php                   # Script installation
│   ├── diagnostic.php                # Script diagnostic
│   ├── clean.php                     # Script nettoyage
│   ├── index.php                     # Sécurité
│   ├── logo.png                      # Logo module
│   ├── README.md                     # Documentation
│   ├── INSTALLATION.md               # Guide installation
│   └── views/
│       ├── css/product-status.css    # Styles badges
│       └── js/product-status.js      # Logique AJAX
│
├── mails/                            # Templates email modifiés
│   ├── fr/order_conf.html           # Template FR avec acompte
│   └── en/order_conf.html           # Template EN avec acompte
│
├── .claude/                          # Configuration Claude Code
│   ├── agents/
│   │   └── prestashop-developer.md  # Agent dev PrestaShop
│   └── commands/
│       ├── debug-module.md          # Commande debug module
│       ├── analyze-hooks.md         # Commande analyse hooks
│       └── check-conventions.md     # Vérification conventions
│
└── Documentation
    ├── README.md                     # README principal
    ├── README_MODULE_ACOMPTE.md      # Doc complète module
    └── INSTALLATION_AUTRE_PRESTASHOP.md  # Guide installation
```

---

## Configuration Environnement

### Base de Données
- **Host** : 127.0.0.1
- **Database** : prestashop_1765
- **User** : root
- **Password** : (vide)
- **Prefix** : ps_
- **Engine** : InnoDB

### Serveur Web
- **URL Front** : http://localhost:8081
- **URL Admin** : http://localhost:8081/admin1762188721
- **PHP** : 7.4
- **Serveur** : PHP Built-in Server

### Email (MailHog)
- **SMTP** : 127.0.0.1:1025
- **Web UI** : http://localhost:8025
- **Transport** : smtp

---

## Commandes Utiles

### Serveur
```bash
# Démarrer le serveur PrestaShop
/usr/local/opt/php@7.4/bin/php -d memory_limit=512M -S 127.0.0.1:8081 -t /Users/paulbihr/Sites/prestashop-1765 &

# Vérifier le serveur
ps aux | grep "127.0.0.1:8081"

# MailHog (déjà actif)
ps aux | grep mailhog
```

### Base de Données
```sql
-- Commandes avec paiement partiel
SELECT o.id_order, o.reference, o.total_paid_tax_incl, o.total_paid_real,
       (o.total_paid_tax_incl - o.total_paid_real) as reste
FROM ps_orders o
WHERE o.total_paid_real < o.total_paid_tax_incl;

-- Paiements d'une commande
SELECT * FROM ps_order_payment WHERE order_reference = 'REF';

-- Hooks du module AcompteEmail
SELECT h.name, hm.position
FROM ps_hook_module hm
JOIN ps_hook h ON h.id_hook = hm.id_hook
JOIN ps_module m ON m.id_module = hm.id_module
WHERE m.name = 'acompteemail';
```

### Git
```bash
# État du dépôt
git status

# Historique du module
git log --oneline -- modules/acompteemail/

# Derniers commits
git log --oneline -10
```

---

## Variables Smarty Personnalisées

Le module AcompteEmail ajoute ces variables aux emails `order_conf` :

| Variable | Type | Description |
|----------|------|-------------|
| `{total_to_pay}` | string | Total formaté (ex: "20,90 €") |
| `{amount_paid}` | string | Acompte formaté (ex: "5,00 €") |
| `{amount_remaining}` | string | Reste formaté (ex: "15,90 €") |
| `{total_to_pay_raw}` | float | Total brut pour conditions |
| `{amount_paid_raw}` | float | Acompte brut pour conditions |
| `{amount_remaining_raw}` | float | Reste brut pour conditions |
| `{is_fully_paid}` | int | 1 si soldé, 0 sinon |

**Utilisation dans les templates** :
```smarty
{if isset($amount_remaining_raw) && $amount_remaining_raw > 0 && $amount_paid_raw > 0}
  <!-- Affichage mode acompte -->
  Total à payer : {total_to_pay}
  Acompte : {amount_paid}
  Reste à payer : {amount_remaining}
{else}
  <!-- Affichage mode complet -->
  Total payé : {total_paid}
{/if}
```

---

## Tests Disponibles

### Commande de Test
- **ID** : #4
- **Référence** : FFATNOMMJ
- **Total** : 14,90 €
- **Acompte** : 5,00 €
- **Reste** : 9,90 €

**Pour tester** :
1. Aller dans le Back-Office : http://localhost:8081/admin1762188721
2. Commandes > Commande #4
3. Renvoyer l'email de confirmation
4. Vérifier dans MailHog : http://localhost:8025

---

## Dépendances et Compatibilité

- **PrestaShop** : 1.7.0.0 à 1.7.8.x
- **PHP** : 7.1 à 8.1
- **MySQL** : 5.6+
- **Smarty** : 3.x (intégré à PrestaShop)

---

## Agent et Commandes Claude Code

### Agent Développeur PrestaShop
Invoquer avec : `@prestashop-developer [votre demande]`

Expert en :
- Développement de modules
- Hooks et événements
- Emails et templates Smarty
- Paiements et commandes
- Conventions et sécurité PrestaShop

### Commandes Disponibles

```bash
# Debugger un module
/debug-module acompteemail

# Analyser un hook
/analyze-hooks actionEmailSendBefore

# Vérifier les conventions
/check-conventions modules/acompteemail
```

---

## Notes de Développement

### Workflow de Développement
1. Développer dans `/modules/[nom_module]/`
2. Tester via le Back-Office
3. Vérifier les logs : `var/logs/prod.log`
4. Valider dans MailHog (pour emails)
5. Commiter les changements

### Convention de Création de Modules

**IMPORTANT** : Chaque nouveau module PrestaShop DOIT inclure les 3 scripts PHP d'administration suivants :

#### 1. `install.php` - Installation automatique
- Auto-détection de PrestaShop (`config.inc.php`)
- Support CLI + navigateur (HTML stylé)
- Vérification version PHP
- Installation du module en BDD
- Enregistrement des hooks
- Nettoyage du cache
- Affichage des instructions

#### 2. `diagnostic.php` - Diagnostic complet
- Vérification environnement (PHP, PrestaShop)
- Contrôle des fichiers et permissions
- Validation syntaxe PHP
- Vérification BDD (module, hooks)
- État du cache
- Recommandations

#### 3. `clean.php` - Nettoyage/Désinstallation
- Mode cache uniquement (`--cache-only`)
- Désinstallation complète (`--yes`)
- Interface de confirmation (CLI + HTML)
- Suppression BDD (module, hooks)
- Nettoyage du cache

**Template des scripts** : Voir `/modules/productstatusinorder/` pour référence

**Caractéristiques communes** :
- Détection auto CLI vs navigateur : `php_sapi_name() === 'cli'`
- Messages avec emojis : ✅ success, ❌ error, ℹ️ info, ⚠️ warning
- HTML stylé pour navigateur
- Sortie formatée pour CLI
- Gestion erreurs PrestaShop

### Fichiers à NE PAS versionner
- `/config/` (sauf exemples)
- `/cache/`
- `/var/`
- `/vendor/` (si composer)
- Fichiers de test (test_*.php, send_*.php)
- Documentation temporaire

### Commits Importants
```
717f3b2 - feat: Update ProductStatusInOrder module ZIP with PHP installation scripts
359bb1b - feat: Add ProductStatusInOrder module v1.0.0
69e57ae - docs: Add clean README for the repository
151fc67 - chore: Clean up repository
10603f3 - feat: Add AcompteEmail module
```

---

**Dernière mise à jour** : Novembre 2025
**Mainteneur** : Paul Bihr
