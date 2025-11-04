# Module Acompte Email

Affiche clairement l'acompte et le reste à payer dans les emails de confirmation de commande.

## Le problème

Quand un client paie seulement une partie de sa commande, l'email dit "Total payé : 33 723,00 €" alors qu'il n'a versé qu'un acompte. C'est trompeur !

## La solution

Ce module affiche la vérité :
```
Total à payer    : 33 723,00 €
Acompte versé    : 3 377,70 €
Reste à payer    : 30 345,30 €
```

## Installation

1. **Installer le module**
   - Back-Office > Modules > Module Manager
   - Chercher "Acompte Email"
   - Cliquer sur "Installer"

2. **Vider le cache**
   - Paramètres avancés > Performance > Vider le cache

C'est tout ! Le module fait le reste automatiquement.

## Comment tester

1. Créer une commande de test dans le back-office
2. Ajouter un paiement partiel (par exemple 10% du total)
3. Renvoyer l'email de confirmation
4. Vérifier que l'email affiche bien l'acompte et le reste à payer

## Comment ça marche

Le module :
- Récupère tous les paiements de la commande
- Calcule l'acompte total
- Calcule ce qu'il reste à payer
- Met à jour l'email avec les bonnes informations

Tout est automatique, pas besoin de configuration.

## En cas de problème

**Le module ne s'affiche pas ?**
- Vider le cache PrestaShop

**L'email ne change pas ?**
- Vérifier que le module est bien actif
- Vider le cache
- Vérifier qu'il y a bien un paiement partiel sur la commande

## Auteur

Paul Bihr
