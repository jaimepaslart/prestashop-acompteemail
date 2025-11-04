Correction affichage acomptes emails commande Bonjour, J'ai corrigé
l'affichage des acomptes dans les emails de confirmation de commande.
Problème : L'email affichait "Total payé : 1 000 EUR" même pour un
paiement partiel. Solution : Le module détecte les paiements partiels
et affiche :

Total à payer : 1 000,00 EUR
Acompte versé : 250,00 EUR
Reste à payer : 750,00 EUR

Si paiement complet, affichage classique conservé. Technique : Hook
actionEmailSendBefore + modification template order_conf.html
Cordialement, Paul