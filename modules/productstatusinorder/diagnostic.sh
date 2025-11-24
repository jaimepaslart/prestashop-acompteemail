#!/bin/bash

###############################################################################
# Script de diagnostic du module ProductStatusInOrder
# Auteur: Paul Bihr
# Licence: MIT
###############################################################################

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

error() {
    echo -e "${RED}❌ $1${NC}"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

section() {
    echo ""
    echo -e "${CYAN}═══════════════════════════════════════════════${NC}"
    echo -e "${CYAN}  $1${NC}"
    echo -e "${CYAN}═══════════════════════════════════════════════${NC}"
    echo ""
}

title() {
    echo ""
    echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║     Diagnostic du module ProductStatusInOrder             ║${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

title

# Vérifier les arguments
if [ $# -eq 0 ]; then
    info "Usage: $0 /path/to/prestashop [db_host] [db_name] [db_user] [db_password]"
    info "Exemple: $0 /var/www/html/prestashop localhost prestashop root password"
    echo ""
    read -p "Entrez le chemin vers votre installation PrestaShop: " PRESTASHOP_PATH

    if [ -z "$PRESTASHOP_PATH" ]; then
        error "Aucun chemin spécifié"
        exit 1
    fi
else
    PRESTASHOP_PATH="$1"
    DB_HOST="${2:-127.0.0.1}"
    DB_NAME="${3:-}"
    DB_USER="${4:-}"
    DB_PASSWORD="${5:-}"
fi

PRESTASHOP_PATH="${PRESTASHOP_PATH%/}"

###############################################################################
# 1. VÉRIFICATION DE L'ENVIRONNEMENT
###############################################################################

section "1. Environnement"

# Vérifier que PrestaShop existe
if [ ! -d "$PRESTASHOP_PATH" ]; then
    error "Le répertoire $PRESTASHOP_PATH n'existe pas"
    exit 1
fi

if [ ! -f "$PRESTASHOP_PATH/config/config.inc.php" ]; then
    error "Le répertoire $PRESTASHOP_PATH ne semble pas être une installation PrestaShop valide"
    exit 1
fi

success "Installation PrestaShop détectée: $PRESTASHOP_PATH"

# Vérifier la version PHP
PHP_VERSION=$(php -r "echo PHP_VERSION;" 2>/dev/null || echo "0.0.0")
PHP_MAJOR=$(echo "$PHP_VERSION" | cut -d. -f1)
PHP_MINOR=$(echo "$PHP_VERSION" | cut -d. -f2)

echo -n "Version PHP: $PHP_VERSION ... "
if [ "$PHP_MAJOR" -lt 7 ] || ([ "$PHP_MAJOR" -eq 7 ] && [ "$PHP_MINOR" -lt 2 ]); then
    error "PHP 7.2+ requis"
else
    success "OK"
fi

# Vérifier PrestaShop version
if [ -f "$PRESTASHOP_PATH/app/AppKernel.php" ]; then
    PS_VERSION=$(grep "const VERSION =" "$PRESTASHOP_PATH/app/AppKernel.php" | cut -d"'" -f2 || echo "Unknown")
    echo "Version PrestaShop: $PS_VERSION"
fi

###############################################################################
# 2. VÉRIFICATION DES FICHIERS DU MODULE
###############################################################################

section "2. Fichiers du module"

MODULE_NAME="productstatusinorder"
MODULE_DIR="$PRESTASHOP_PATH/modules/$MODULE_NAME"

echo -n "Module installé: "
if [ -d "$MODULE_DIR" ]; then
    success "$MODULE_DIR"
else
    error "Module non trouvé dans $MODULE_DIR"
    exit 1
fi

# Vérifier les fichiers principaux
FILES=(
    "productstatusinorder.php"
    "index.php"
    "views/js/product-status.js"
    "views/css/product-status.css"
)

echo ""
info "Fichiers principaux:"
for FILE in "${FILES[@]}"; do
    echo -n "  • $FILE ... "
    if [ -f "$MODULE_DIR/$FILE" ]; then
        success "OK"
    else
        error "MANQUANT"
    fi
done

# Vérifier les permissions
echo ""
info "Permissions:"
PERMS=$(stat -f "%Lp" "$MODULE_DIR" 2>/dev/null || stat -c "%a" "$MODULE_DIR" 2>/dev/null || echo "???")
echo -n "  • Répertoire module ($PERMS) ... "
if [ "$PERMS" = "755" ] || [ "$PERMS" = "775" ]; then
    success "OK"
else
    warning "Permission inhabituelle: $PERMS"
fi

# Vérifier la syntaxe PHP
echo ""
info "Syntaxe PHP:"
echo -n "  • productstatusinorder.php ... "
PHP_CHECK=$(php -l "$MODULE_DIR/productstatusinorder.php" 2>&1)
if echo "$PHP_CHECK" | grep -q "No syntax errors"; then
    success "OK"
else
    error "ERREUR"
    echo "$PHP_CHECK"
fi

###############################################################################
# 3. VÉRIFICATION EN BASE DE DONNÉES
###############################################################################

section "3. Base de données"

# Essayer de récupérer les informations de connexion depuis config.inc.php
if [ -z "$DB_NAME" ]; then
    info "Lecture de la configuration PrestaShop..."
    DB_HOST=$(grep "define('_DB_SERVER_'" "$PRESTASHOP_PATH/config/config.inc.php" | cut -d"'" -f4 || echo "")
    DB_NAME=$(grep "define('_DB_NAME_'" "$PRESTASHOP_PATH/config/config.inc.php" | cut -d"'" -f4 || echo "")
    DB_USER=$(grep "define('_DB_USER_'" "$PRESTASHOP_PATH/config/config.inc.php" | cut -d"'" -f4 || echo "")
    DB_PASSWORD=$(grep "define('_DB_PASSWD_'" "$PRESTASHOP_PATH/config/config.inc.php" | cut -d"'" -f4 || echo "")
    DB_PREFIX=$(grep "define('_DB_PREFIX_'" "$PRESTASHOP_PATH/config/config.inc.php" | cut -d"'" -f4 || echo "ps_")
fi

if [ -z "$DB_NAME" ]; then
    warning "Impossible de lire la configuration de la base de données"
    warning "Relancez le script avec: $0 $PRESTASHOP_PATH [db_host] [db_name] [db_user] [db_password]"
else
    info "Base de données: $DB_NAME@$DB_HOST"

    # Vérifier si le module est enregistré
    echo ""
    echo -n "Module enregistré en BDD ... "

    MYSQL_CMD="mysql -h $DB_HOST -u $DB_USER"
    if [ -n "$DB_PASSWORD" ]; then
        MYSQL_CMD="$MYSQL_CMD -p$DB_PASSWORD"
    fi

    MODULE_CHECK=$($MYSQL_CMD -N -e "SELECT COUNT(*) FROM ${DB_PREFIX}module WHERE name='$MODULE_NAME'" $DB_NAME 2>/dev/null || echo "0")

    if [ "$MODULE_CHECK" = "1" ]; then
        success "OK"

        # Vérifier si actif
        echo -n "Module actif ... "
        MODULE_ACTIVE=$($MYSQL_CMD -N -e "SELECT active FROM ${DB_PREFIX}module WHERE name='$MODULE_NAME'" $DB_NAME 2>/dev/null || echo "0")
        if [ "$MODULE_ACTIVE" = "1" ]; then
            success "OUI"
        else
            error "NON (désactivé)"
        fi

        # Vérifier la version
        echo -n "Version installée ... "
        MODULE_VERSION=$($MYSQL_CMD -N -e "SELECT version FROM ${DB_PREFIX}module WHERE name='$MODULE_NAME'" $DB_NAME 2>/dev/null || echo "Unknown")
        echo "$MODULE_VERSION"

    else
        error "NON (module non installé en BDD)"
    fi

    # Vérifier les hooks
    echo ""
    info "Hooks enregistrés:"
    $MYSQL_CMD -e "
        SELECT h.name, hm.position
        FROM ${DB_PREFIX}hook h
        JOIN ${DB_PREFIX}hook_module hm ON h.id_hook = hm.id_hook
        JOIN ${DB_PREFIX}module m ON m.id_module = hm.id_module
        WHERE m.name = '$MODULE_NAME'
    " $DB_NAME 2>/dev/null || warning "Impossible de vérifier les hooks"
fi

###############################################################################
# 4. VÉRIFICATION DU CACHE
###############################################################################

section "4. Cache"

CACHE_DIRS=(
    "$PRESTASHOP_PATH/var/cache/prod"
    "$PRESTASHOP_PATH/var/cache/dev"
    "$PRESTASHOP_PATH/cache"
)

for CACHE_DIR in "${CACHE_DIRS[@]}"; do
    if [ -d "$CACHE_DIR" ]; then
        CACHE_SIZE=$(du -sh "$CACHE_DIR" 2>/dev/null | cut -f1)
        echo "  • $CACHE_DIR: $CACHE_SIZE"
    fi
done

if [ -f "$PRESTASHOP_PATH/var/cache/prod/class_index.php" ]; then
    warning "Fichier class_index.php présent (peut causer des problèmes)"
    info "    Supprimez-le avec: rm $PRESTASHOP_PATH/var/cache/prod/class_index.php"
fi

###############################################################################
# 5. RECOMMANDATIONS
###############################################################################

section "5. Recommandations"

echo "Pour tester le module:"
echo "  1. Connectez-vous au Back-Office"
echo "  2. Allez dans: Ventes > Commandes > Ajouter une commande"
echo "  3. Sélectionnez un client"
echo "  4. Cherchez un produit"
echo "  5. Ouvrez la console JavaScript (F12)"
echo "  6. Vérifiez les messages: [ProductStatusInOrder]"
echo ""

echo "Si les badges ne s'affichent pas:"
echo "  • Videz le cache: ./clean.sh $PRESTASHOP_PATH"
echo "  • Videz le cache navigateur: Ctrl+Shift+R"
echo "  • Vérifiez la console JavaScript (F12)"
echo "  • Vérifiez que 'active' est présent dans la réponse AJAX"
echo ""

info "Logs PrestaShop: $PRESTASHOP_PATH/var/logs/"
echo ""

echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║             Diagnostic terminé ! 🔍                        ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

exit 0
