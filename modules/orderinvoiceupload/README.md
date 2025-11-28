# Order Invoice Upload

Module PrestaShop permettant de téléverser manuellement une facture PDF pour chaque commande dans le Back Office, avec possibilité pour les clients de télécharger leurs factures depuis leur espace compte.

## Fonctionnalités

### Back Office (Administration)
- ✅ Téléversement de factures PDF pour chaque commande
- ✅ Affichage dans la page de détail d'une commande
- ✅ Téléchargement sécurisé des factures
- ✅ Remplacement et suppression des factures

### Front Office (Compte Client)
- ✅ Affichage du bloc "Facture associée" sur la page de détail de commande
- ✅ Téléchargement sécurisé par les clients connectés
- ✅ Interface en lecture seule (aucun upload côté front)
- ✅ Vérification que la commande appartient au client

### Configuration
- ✅ Page de configuration dans le Back Office
- ✅ Activation/désactivation de l'affichage front
- ✅ Notification email au client lors de l'ajout d'une facture
- ✅ Taille maximale des fichiers configurable (1-50 Mo)

### Général
- ✅ Compatible PrestaShop 1.7.6.5 à 1.7.8.11
- ✅ Compatible PHP 7.2 à 7.4
- ✅ Aucun override du cœur

## Installation

### Via le Back Office (recommandé)

1. Téléchargez le fichier `orderinvoiceupload.zip`
2. Allez dans **Modules > Gestionnaire de modules**
3. Cliquez sur **Installer un module**
4. Sélectionnez le fichier ZIP
5. Le module s'installe automatiquement

### Via les scripts PHP

```bash
# Depuis le répertoire du module
php install.php
```

Ou accédez à :
```
http://votresite.com/modules/orderinvoiceupload/install.php
```

## Utilisation

1. Allez dans **Commandes > Commandes**
2. Cliquez sur une commande pour voir les détails
3. Vous verrez le bloc **"Facture manuelle"**
4. Téléversez votre fichier PDF (max 5 Mo)

### Actions disponibles

- **Téléverser** : Ajouter une nouvelle facture
- **Télécharger** : Récupérer la facture associée
- **Remplacer** : Changer la facture existante
- **Supprimer** : Retirer la facture

## Scripts d'administration

### Diagnostic

Vérifiez l'état du module :

```bash
php diagnostic.php
```

Ou accédez à :
```
http://votresite.com/modules/orderinvoiceupload/diagnostic.php
```

### Nettoyage

```bash
# Nettoyer uniquement le cache
php clean.php --cache-only

# Désinstaller complètement le module
php clean.php --yes
```

## Structure des fichiers

```
orderinvoiceupload/
├── orderinvoiceupload.php          # Fichier principal du module
├── config.xml                       # Configuration XML
├── logo.png                         # Logo du module
├── index.php                        # Sécurité
├── install.php                      # Script d'installation
├── diagnostic.php                   # Script de diagnostic
├── clean.php                        # Script de nettoyage
├── README.md                        # Ce fichier
│
├── classes/
│   ├── index.php
│   └── OrderInvoiceUploadFile.php  # Classe helper
│
├── controllers/
│   ├── admin/
│   │   ├── index.php
│   │   └── AdminOrderInvoiceUploadController.php  # Téléchargement admin
│   └── front/
│       ├── index.php
│       └── download.php            # Téléchargement client (sécurisé)
│
├── sql/
│   ├── index.php
│   ├── install.sql                  # Création de la table
│   └── uninstall.sql                # Suppression de la table
│
├── views/
│   ├── css/
│   │   ├── admin.css               # Styles Back Office
│   │   └── front.css               # Styles Front Office
│   ├── js/
│   │   └── admin.js                # JavaScript Back Office
│   └── templates/
│       ├── admin/
│       │   └── order_invoice_block.tpl  # Template admin
│       ├── front/
│       │   └── error.tpl           # Page d'erreur front
│       └── hook/
│           └── order_invoice_front.tpl  # Bloc facture front
│
├── uploads/                         # Dossier des factures (protégé)
│   ├── index.php
│   └── .htaccess
│
├── mails/                           # Templates d'email
│   ├── fr/
│   │   ├── orderinvoiceupload_notification.html
│   │   └── orderinvoiceupload_notification.txt
│   └── en/
│       ├── orderinvoiceupload_notification.html
│       └── orderinvoiceupload_notification.txt
│
└── translations/
    └── index.php
```

## Base de données

Le module crée une table `ps_order_invoice_upload` avec les champs :

| Champ | Type | Description |
|-------|------|-------------|
| `id_order_invoice_upload` | INT | Clé primaire |
| `id_order` | INT | ID de la commande |
| `file_name` | VARCHAR(255) | Nom du fichier stocké |
| `original_name` | VARCHAR(255) | Nom original du fichier |
| `mime_type` | VARCHAR(100) | Type MIME |
| `date_add` | DATETIME | Date d'ajout |

## Hooks utilisés

### Back Office

| Hook | Version PS | Description |
|------|-----------|-------------|
| `displayAdminOrder` | 1.7.6.x | Affichage dans la page commande |
| `displayAdminOrderMain` | 1.7.7+ | Zone principale page commande |
| `actionAdminControllerSetMedia` | Toutes | Chargement CSS/JS admin |
| `displayBackOfficeHeader` | Toutes | Fallback CSS admin |

### Front Office

| Hook | Version PS | Description |
|------|-----------|-------------|
| `displayOrderDetail` | Toutes | Bloc facture sur page commande client |
| `displayHeader` | Toutes | Chargement CSS front |

## Sécurité

### Back Office
- ✅ Vérification du token admin pour toutes les actions
- ✅ Validation du type MIME et de l'extension
- ✅ Vérification des magic bytes PDF
- ✅ Noms de fichiers hashés/uniques
- ✅ Protection .htaccess du dossier uploads
- ✅ Téléchargement via contrôleur sécurisé

### Front Office
- ✅ Vérification que le client est connecté
- ✅ Vérification que la commande appartient au client
- ✅ Téléchargement via contrôleur front sécurisé
- ✅ Interface en lecture seule (aucun upload possible)
- ✅ Chemin physique du fichier jamais exposé

## Configuration

Le module dispose d'une page de configuration accessible via :
**Modules > Gestionnaire de modules > Order Invoice Upload > Configurer**

### Paramètres disponibles

| Paramètre | Type | Description | Défaut |
|-----------|------|-------------|--------|
| Affichage front-office | Switch | Permet aux clients de voir leurs factures | Activé |
| Notification email | Switch | Envoie un email au client lors de l'ajout d'une facture | Désactivé |
| Taille max fichiers | Numérique | Limite en Mo (1-50) | 5 Mo |

### Templates d'email

Le module inclut des templates d'email multilingues (FR/EN) pour notifier les clients.
Les templates sont situés dans `mails/fr/` et `mails/en/`.

## Compatibilité

- **PrestaShop** : 1.7.6.0 à 1.7.8.99
- **PHP** : 7.2, 7.3, 7.4
- **MySQL** : 5.6+

## Licence

MIT License - © 2025 Paul Bihr

## Support

Pour toute question ou problème :
1. Exécutez le script de diagnostic
2. Vérifiez les logs PrestaShop (`var/logs/`)
3. Vérifiez la console JavaScript (F12)
