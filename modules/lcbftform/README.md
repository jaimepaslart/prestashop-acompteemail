# Module LCB-FT Form pour PrestaShop

## C'est quoi ce module ?

Ce module permet de collecter des informations obligatoires sur les clients qui achètent des objets de valeur (or, bijoux, métaux précieux, oeuvres d'art...). C'est une obligation légale liée à la **Lutte Contre le Blanchiment et le Financement du Terrorisme** (LCB-FT).

---

## Compatibilité

- **PrestaShop** : 1.7.6.0 à 1.7.8.99
- **PHP** : 7.2 à 7.4

---

## Comment ça marche côté client ?

1. **Pendant la commande** : Une nouvelle étape apparaît juste après "Informations personnelles", avant l'adresse de livraison.

2. **Le formulaire demande** :
   - Identité complète (nom, prénom, adresse, téléphone...)
   - Situation professionnelle
   - Informations sur le patrimoine (revenus, estimation du patrimoine)
   - Si la personne est soumise à l'IFI (Impôt sur la Fortune Immobilière)
   - L'origine des fonds utilisés pour l'achat (salaire, vente immobilière, héritage, etc.)
   - Les justificatifs qui seront fournis

3. **Signature électronique** : Le client coche une case pour certifier que les informations sont exactes, puis signe avec son nom. La date et l'heure sont enregistrées automatiquement.

4. **Validation** : Sans ce formulaire rempli et signé, impossible de passer à l'étape suivante et donc de finaliser la commande.

5. **Après la commande** : Le client peut télécharger un PDF récapitulatif de sa déclaration depuis la page de confirmation ou depuis son compte client.

---

## Comment ça marche côté vendeur ?

1. **Dans le Back-Office** : Un nouvel onglet "Formulaires LCB-FT" apparaît dans le menu. Il liste tous les formulaires reçus avec :
   - Le numéro de commande associé
   - Le nom du client
   - La date de signature
   - Un bouton pour voir le détail

2. **Sur chaque commande** : Un bloc spécial affiche le résumé du formulaire LCB-FT directement sur la page de la commande.

3. **Export PDF** : Le vendeur peut télécharger le formulaire en PDF pour l'archiver (obligation légale de conservation).

---

## Les points importants

- **Fonctionne pour tous** : Que le client ait un compte ou qu'il commande en tant qu'invité, le formulaire est obligatoire.

- **Données sécurisées** : Les informations sont stockées en base de données et liées à la commande.

- **Conforme à la loi** : Le formulaire respecte les exigences de la réglementation LCB-FT pour les professionnels concernés.

- **Intégré au tunnel** : L'étape s'intègre naturellement dans le processus de commande PrestaShop, avec le même style visuel.

---

## Installation

### Méthode 1 : Via le Back-Office
1. Aller dans **Modules > Gestionnaire de modules**
2. Cliquer sur **Installer un module**
3. Uploader le fichier `lcbftform.zip`

### Méthode 2 : Via les scripts PHP
```bash
# Installation du module
php modules/lcbftform/install.php

# Installation des overrides de thème (étape checkout)
php modules/lcbftform/install_theme_overrides.php
```

### Méthode 3 : Via le navigateur
- Installation : `http://votre-site.com/modules/lcbftform/install.php`
- Overrides thème : `http://votre-site.com/modules/lcbftform/install_theme_overrides.php`

---

## Désinstallation

### Via les scripts PHP
```bash
# Nettoyage du cache uniquement
php modules/lcbftform/clean.php

# Désinstallation complète (supprime la BDD et les overrides thème)
php modules/lcbftform/clean.php --yes
```

### Via le navigateur
- Nettoyage cache : `http://votre-site.com/modules/lcbftform/clean.php`
- Désinstallation : `http://votre-site.com/modules/lcbftform/clean.php?action=uninstall`

La désinstallation supprime :
- La table de base de données
- Tous les formulaires enregistrés
- L'onglet d'administration
- Les fichiers du thème (restaure les originaux si backup disponible)

---

## Diagnostic

Un script de diagnostic permet de vérifier l'état du module :

```bash
php modules/lcbftform/diagnostic.php
```

Ou via le navigateur : `http://votre-site.com/modules/lcbftform/diagnostic.php`

---

## Structure des fichiers

```
lcbftform/
├── lcbftform.php              # Classe principale du module
├── install.php                # Script d'installation
├── clean.php                  # Script de désinstallation
├── diagnostic.php             # Script de diagnostic
├── install_theme_overrides.php # Installation overrides thème
├── classes/
│   ├── LcbftForm.php          # Modèle de données
│   └── LcbftPdfGenerator.php  # Génération PDF
├── controllers/
│   ├── admin/
│   │   └── AdminLcbftFormsController.php  # Liste des formulaires BO
│   └── front/
│       ├── validation.php     # Validation AJAX du formulaire
│       ├── download.php       # Téléchargement PDF
│       └── account.php        # Liste formulaires client
├── views/
│   ├── css/
│   │   ├── front.css          # Styles front-office
│   │   └── admin.css          # Styles back-office
│   ├── js/
│   │   └── front.js           # JavaScript front-office
│   └── templates/
│       ├── admin/             # Templates back-office
│       ├── front/             # Templates front-office
│       └── hook/              # Templates des hooks
├── theme_overrides/           # Fichiers à copier dans le thème
│   └── child_warehouse/
│       └── templates/checkout/
└── sql/
    ├── install.sql            # Création table
    └── uninstall.sql          # Suppression table
```

---

## Auteur

**Paul Bihr**

---

## Licence

MIT
