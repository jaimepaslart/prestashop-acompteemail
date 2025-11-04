# Module Acompte Email pour PrestaShop

Un module simple pour afficher proprement les acomptes dans les emails de confirmation de commande.

## Ce que ça fait

Quand un client paie en plusieurs fois, l'email de confirmation montre maintenant :
- Le prix total de la commande
- L'acompte déjà payé
- Ce qu'il reste à payer

**Par exemple :**
```
Total à payer    : 20,90 €
Acompte versé    : 5,00 €
Reste à payer    : 15,90 €
```

## Installation

### 1. Copier le module
```bash
cp -r modules/acompteemail /chemin/vers/votre/prestashop/modules/
```

### 2. Copier le template email
```bash
cp mails/fr/order_conf.html /chemin/vers/votre/prestashop/mails/fr/
```

### 3. L'activer dans PrestaShop
- Aller dans **Modules** > **Module Manager**
- Chercher "Acompte Email"
- Cliquer sur **Installer**

### 4. Vider le cache
- **Paramètres avancés** > **Performance** > **Vider le cache**

C'est tout !

## Comment ça marche

Le module calcule automatiquement :
- Total = le prix total de la commande
- Acompte = ce qui a déjà été payé
- Reste = la différence entre les deux

Tout est fait automatiquement, vous n'avez rien à configurer.

## Compatibilité

- PrestaShop 1.7.x
- PHP 7.1 et plus

## Auteur

Paul Bihr
