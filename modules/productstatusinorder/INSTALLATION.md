# Guide d'installation - Module ProductStatusInOrder

Module PrestaShop pour afficher le statut actif/inactif des produits lors de la création de commandes.

## Prérequis

- PrestaShop 1.7.0.0 à 1.7.8.x
- PHP 7.2 ou supérieur
- Accès au Back-Office PrestaShop
- Accès FTP/SSH (optionnel)

---

## Méthode 1 : Installation via le Back-Office (recommandée)

### Étape 1 : Télécharger le module

Téléchargez le fichier ZIP du module :
- **Depuis GitHub** : https://github.com/jaimepaslart/prestashop-acompteemail/raw/module/productstatusinorder/productstatusinorder.zip
- **Depuis le dépôt local** : `productstatusinorder.zip`

### Étape 2 : Installer le module

1. Connectez-vous au **Back-Office PrestaShop**
2. Allez dans **Modules > Module Manager** (ou Gestionnaire de modules)
3. Cliquez sur le bouton **"Charger un module"** (Upload a module) en haut à droite
4. Glissez-déposez le fichier `productstatusinorder.zip` ou cliquez pour le sélectionner
5. Attendez la fin de l'installation
6. Un message de confirmation apparaîtra

### Étape 3 : Vider le cache

1. Allez dans **Paramètres avancés > Performance**
2. Cliquez sur **"Vider le cache"**
3. Ou via SSH : `rm -rf var/cache/prod/*`

### Étape 4 : Tester le module

1. Allez dans **Ventes > Commandes > Ajouter une commande**
2. Sélectionnez un client
3. Dans le champ **"Rechercher un produit"**, tapez quelques lettres
4. Vous devriez voir les badges :
   - 🟢 **[Actif]** pour les produits actifs
   - 🔴 **[Inactif]** pour les produits inactifs

---

## Méthode 2 : Installation via FTP/SSH

### Étape 1 : Uploader les fichiers

Via FTP ou SSH, copiez le dossier `productstatusinorder/` dans le répertoire `modules/` de votre PrestaShop :

```bash
# Exemple via SCP
scp -r productstatusinorder/ user@votreserveur.com:/path/to/prestashop/modules/

# Ou via rsync
rsync -avz productstatusinorder/ user@votreserveur.com:/path/to/prestashop/modules/productstatusinorder/
```

### Étape 2 : Définir les permissions

```bash
cd /path/to/prestashop/modules/productstatusinorder
chmod 755 -R .
chown www-data:www-data -R .  # Ou l'utilisateur de votre serveur web
```

### Étape 3 : Installer via le Back-Office

1. Allez dans **Modules > Module Manager**
2. Cherchez "Product Status In Order" dans la liste
3. Cliquez sur **"Installer"**

---

## Méthode 3 : Installation via scripts automatiques

### Scripts disponibles

Le module inclut 3 scripts pour faciliter l'installation :

#### 1. `install.sh` - Installation automatique

```bash
chmod +x install.sh
./install.sh /path/to/prestashop
```

**Actions** :
- ✅ Vérifie PrestaShop et PHP
- ✅ Copie les fichiers du module
- ✅ Définit les permissions
- ✅ Vide le cache
- ✅ Affiche les instructions

#### 2. `diagnostic.sh` - Vérification de l'installation

```bash
chmod +x diagnostic.sh
./diagnostic.sh /path/to/prestashop
```

**Vérifie** :
- ✅ Présence des fichiers
- ✅ Permissions
- ✅ Enregistrement en BDD
- ✅ Hooks actifs
- ✅ Cache PrestaShop
- ✅ Version PHP

#### 3. `clean.sh` - Nettoyage/Désinstallation

```bash
chmod +x clean.sh
./clean.sh /path/to/prestashop
```

**Actions** :
- ✅ Supprime le module
- ✅ Vide le cache
- ✅ Affiche les requêtes SQL pour nettoyer la BDD

---

## Vérification de l'installation

### Vérifier que le module est installé

```sql
-- Via MySQL
SELECT m.name, m.active, m.version
FROM ps_module m
WHERE m.name = 'productstatusinorder';

-- Résultat attendu :
-- name                    active  version
-- productstatusinorder    1       1.0.0
```

### Vérifier que le hook est enregistré

```sql
SELECT h.name, hm.position
FROM ps_hook h
JOIN ps_hook_module hm ON h.id_hook = hm.id_hook
JOIN ps_module m ON m.id_module = hm.id_module
WHERE m.name = 'productstatusinorder';

-- Résultat attendu :
-- name                           position
-- actionAdminControllerSetMedia  (n'importe quel nombre)
```

### Vérifier que les fichiers JS/CSS se chargent

1. Ouvrir la page **Ventes > Commandes > Ajouter une commande**
2. Ouvrir les **outils développeur** (F12)
3. Onglet **"Réseau"** ou **"Network"**
4. Chercher dans la liste :
   - ✅ `product-status.js` (statut 200)
   - ✅ `product-status.css` (statut 200)

---

## Désinstallation

### Via le Back-Office

1. **Modules > Module Manager**
2. Chercher "Product Status In Order"
3. Cliquer sur **"Désinstaller"**
4. Confirmer la désinstallation

### Via FTP/SSH

```bash
# Supprimer le dossier du module
rm -rf /path/to/prestashop/modules/productstatusinorder/

# Vider le cache
rm -rf /path/to/prestashop/var/cache/prod/*
```

---

## Dépannage

### Le module n'apparaît pas dans la liste

**Solution 1** : Vider le cache
```bash
rm -rf var/cache/prod/*
rm -f var/cache/prod/class_index.php
```

**Solution 2** : Vérifier les permissions
```bash
chmod 755 -R modules/productstatusinorder/
```

**Solution 3** : Vérifier la syntaxe PHP
```bash
php -l modules/productstatusinorder/productstatusinorder.php
```

### Les badges n'apparaissent pas

**Solution 1** : Vider le cache du navigateur
- Chrome/Edge : `Ctrl+Shift+R` (Windows) ou `Cmd+Shift+R` (Mac)
- Firefox : `Ctrl+F5` (Windows) ou `Cmd+Shift+R` (Mac)

**Solution 2** : Vérifier la console JavaScript (F12)
- Ouvrir les outils développeur
- Onglet "Console"
- Chercher les messages `[ProductStatusInOrder]`
- Si vous voyez `Has active field? false`, contactez le support

**Solution 3** : Réinstaller le module
1. Désinstaller le module
2. Vider le cache PrestaShop
3. Réinstaller le module

### Erreur 500 après installation

**Solution** : Vérifier les logs PrestaShop
```bash
tail -f var/logs/prod.log
# Ou
tail -f var/logs/dev.log
```

---

## Support

- **Documentation** : [README.md](README.md)
- **GitHub** : https://github.com/jaimepaslart/prestashop-acompteemail
- **Issues** : https://github.com/jaimepaslart/prestashop-acompteemail/issues

---

## Licence

MIT License - Copyright (c) 2025 Paul Bihr
