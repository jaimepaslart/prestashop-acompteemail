# Module Acompte Email pour PrestaShop

Module pour afficher correctement les acomptes dans les emails de confirmation de commande.

## Fonctionnalité

Quand un client paie seulement une partie de sa commande, l'email de confirmation affiche maintenant :
- Le montant total de la commande
- L'acompte déjà payé
- Le reste à payer

Exemple :
```
Total à payer    : 1 000,00 €
Acompte versé    : 250,00 €
Reste à payer    : 750,00 €
```

## Installation rapide

### 1. Copier le module
```bash
cp -r acompteemail /chemin/vers/prestashop/modules/
```

### 2. Copier le template email
```bash
cp themes/child_warehouse/mails/fr/order_conf.html /chemin/vers/prestashop/themes/VOTRE_THEME/mails/fr/
```

Note : Adaptez le chemin selon votre thème.

### 3. Activer le module
- Modules > Module Manager
- Chercher "Acompte Email"
- Cliquer sur Installer

### 4. Vider le cache
- Paramètres avancés > Performance > Vider le cache

## Fonctionnement technique

Le module utilise le hook `actionEmailSendBefore` pour :
1. Détecter les emails de type `order_conf`
2. Calculer le montant payé via les paiements enregistrés
3. Injecter les variables dans le template email :
   - `{total_to_pay}` - Total de la commande
   - `{amount_paid}` - Montant payé
   - `{amount_remaining}` - Reste à payer
   - `{is_fully_paid}` - Indicateur paiement complet (0 ou 1)

Le template utilise ces variables pour afficher conditionnellement les informations.

## Structure

```
acompteemail/
├── acompteemail.php          # Classe principale du module
├── index.php                 # Protection dossier
└── README.md                 # Documentation

themes/child_warehouse/mails/fr/
├── order_conf.html           # Template modifié
└── order_conf.html.bak       # Backup
```

## Compatibilité

- PrestaShop 1.7.x
- PHP 7.1+

## Adaptation pour autres thèmes

Le template fourni est pour le thème `child_warehouse`. Pour l'adapter à votre thème :

1. Ouvrez votre fichier `themes/VOTRE_THEME/mails/fr/order_conf.html`
2. Trouvez la ligne affichant le total payé (cherchez `{total_paid}`)
3. Remplacez par le code conditionnel fourni dans `order_conf.html`

La modification principale se situe dans la section affichant le total de la commande.

## Développement

Développé en 2 jours pour répondre au besoin d'affichage clair des acomptes dans les emails clients.

Le module est autonome et ne nécessite aucune configuration supplémentaire après installation.
