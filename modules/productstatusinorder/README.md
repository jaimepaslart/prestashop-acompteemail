# Product Status In Order

Module PrestaShop pour afficher le statut actif/inactif des produits lors de la création de commandes en back-office.

## Description

Ce module ajoute un badge visuel (vert pour "Actif", rouge pour "Inactif") à côté de chaque produit dans la liste de recherche lors de la création d'une commande dans le back-office PrestaShop.

## Fonctionnalités

- ✅ Badge vert "Actif" pour les produits actifs
- ✅ Badge rouge "Inactif" pour les produits inactifs
- ✅ Affichage uniquement sur la page de création de commande (AdminOrders)
- ✅ Pas de modification du core PrestaShop
- ✅ Compatible PrestaShop 1.7.x
- ✅ Compatible PHP 7.2+

## Installation

### Méthode 1 : Installation automatique (recommandée)

```bash
# Rendre les scripts exécutables
chmod +x install.sh clean.sh diagnostic.sh

# Lancer l'installation
./install.sh /path/to/prestashop

# Vérifier l'installation
./diagnostic.sh /path/to/prestashop

# Nettoyer/désinstaller (optionnel)
./clean.sh /path/to/prestashop
```

### Méthode 2 : Installation via le Back-Office

1. Télécharger le fichier `productstatusinorder.zip`
2. Aller dans Back-Office > Modules > Gestionnaire de modules
3. Cliquer sur "Charger un module"
4. Glisser-déposer le fichier ZIP
5. Cliquer sur "Installer"

### Méthode 3 : Installation manuelle

1. Télécharger le module
2. Placer le dossier `productstatusinorder` dans `/modules/`
3. Définir les permissions : `chmod 755 -R modules/productstatusinorder/`
4. Aller dans Back-Office > Modules > Gestionnaire de modules
5. Rechercher "Product Status In Order"
6. Cliquer sur "Installer"

📖 **Guide complet** : Voir [INSTALLATION.md](INSTALLATION.md)

## Utilisation

Une fois installé, le module fonctionne automatiquement :

1. Aller dans **Ventes > Commandes > Ajouter une commande**
2. Sélectionner un client
3. Dans le champ "Rechercher un produit", taper le nom d'un produit
4. Les résultats affichent maintenant :
   - Un badge **vert "Actif"** pour les produits actifs
   - Un badge **rouge "Inactif"** pour les produits inactifs

## Structure des fichiers

```
productstatusinorder/
├── productstatusinorder.php    # Fichier principal du module
├── index.php                    # Protection de sécurité
├── README.md                    # Ce fichier
└── views/
    ├── js/
    │   └── product-status.js    # JavaScript pour ajouter les badges
    └── css/
        └── product-status.css   # Styles CSS pour les badges
```

## Compatibilité

- PrestaShop : 1.7.0.0 à 1.7.8.99
- PHP : 7.2 à 8.1

## Auteur

**Paul Bihr**

## Licence

MIT License

Copyright (c) 2025 Paul Bihr

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
