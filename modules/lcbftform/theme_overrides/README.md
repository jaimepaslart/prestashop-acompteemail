# Installation des Overrides de Theme - Module LCB-FT Form

## Pourquoi des overrides de theme ?

Le module LCB-FT Form affiche une etape dediee dans le tunnel de commande.
Cette fonctionnalite necessite de modifier le fichier `checkout-process.tpl`
du theme pour inserer l'etape LCB-FT apres l'etape "Informations personnelles".

Ces fichiers sont des **overrides de theme** (pas de module) pour :
- Respecter les bonnes pratiques PrestaShop
- Eviter les conflits avec les mises a jour du theme parent
- Permettre une personnalisation facile

## Installation automatique

Executez le script d'installation (depuis le navigateur ou en CLI) :

```bash
# Via CLI
php /modules/lcbftform/install_theme_overrides.php

# Via navigateur
http://votre-site.com/modules/lcbftform/install_theme_overrides.php
```

## Installation manuelle

1. Copiez le contenu du dossier `theme_overrides/child_warehouse/` vers votre theme enfant :

```
themes/child_warehouse/templates/checkout/checkout-process.tpl
themes/child_warehouse/templates/checkout/_partials/steps/lcbft-step.tpl
```

2. Videz le cache PrestaShop (Back-Office > Parametres avances > Performances)

## Structure des fichiers

```
theme_overrides/
└── child_warehouse/
    └── templates/
        └── checkout/
            ├── checkout-process.tpl       # Override pour inserer l'etape
            └── _partials/
                └── steps/
                    └── lcbft-step.tpl     # Template de l'etape LCB-FT
```

## Personnalisation

### Modifier le titre de l'etape

Dans `lcbft-step.tpl`, modifiez la ligne :
```smarty
{l s='Formulaire reglementaire' d='Shop.Theme.Checkout'}
```

### Modifier le design

Les styles sont dans `/modules/lcbftform/views/css/front.css`
Les styles specifiques a l'etape commencent par `#checkout-lcbft-step`

## Compatibilite

- PrestaShop 1.7.6.x a 1.7.8.x
- Theme Warehouse 4.x et child themes
- Theme Classic et child themes (avec adaptations)

## Support

En cas de probleme, verifiez :
1. Le theme enfant est bien actif
2. Le cache a ete vide
3. Le module LCB-FT est installe et actif
