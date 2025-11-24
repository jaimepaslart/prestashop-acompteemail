#!/bin/bash

###############################################################################
# Script de nettoyage du module ProductStatusInOrder
# Auteur: Paul Bihr
# Licence: MIT
###############################################################################

set -e

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

error_exit() {
    echo -e "${RED}❌ ERREUR: $1${NC}" >&2
    exit 1
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

title() {
    echo ""
    echo -e "${BLUE}================================================${NC}"
    echo -e "${BLUE}  Nettoyage du module ProductStatusInOrder${NC}"
    echo -e "${BLUE}================================================${NC}"
    echo ""
}

title

# Vérifier les arguments
if [ $# -eq 0 ]; then
    info "Usage: $0 /path/to/prestashop"
    info "Exemple: $0 /var/www/html/prestashop"
    echo ""
    read -p "Entrez le chemin vers votre installation PrestaShop: " PRESTASHOP_PATH

    if [ -z "$PRESTASHOP_PATH" ]; then
        error_exit "Aucun chemin spécifié"
    fi
else
    PRESTASHOP_PATH="$1"
fi

PRESTASHOP_PATH="${PRESTASHOP_PATH%/}"

info "Chemin PrestaShop: $PRESTASHOP_PATH"
echo ""

# Vérifier que le répertoire PrestaShop existe
if [ ! -d "$PRESTASHOP_PATH" ]; then
    error_exit "Le répertoire $PRESTASHOP_PATH n'existe pas"
fi

if [ ! -f "$PRESTASHOP_PATH/config/config.inc.php" ]; then
    error_exit "Le répertoire $PRESTASHOP_PATH ne semble pas être une installation PrestaShop valide"
fi

MODULE_NAME="productstatusinorder"
MODULE_DIR="$PRESTASHOP_PATH/modules/$MODULE_NAME"

# Vérifier si le module existe
if [ ! -d "$MODULE_DIR" ]; then
    warning "Le module n'est pas installé dans $MODULE_DIR"
    info "Rien à nettoyer"
    exit 0
fi

info "Module trouvé: $MODULE_DIR"
echo ""

# Demander confirmation
read -p "Voulez-vous supprimer le module? (y/n): " CONFIRM

if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
    info "Nettoyage annulé"
    exit 0
fi

# Supprimer le module
info "Suppression du module..."
rm -rf "$MODULE_DIR"
success "Module supprimé"

# Vider le cache
info "Nettoyage du cache PrestaShop..."

CACHE_DIRS=(
    "$PRESTASHOP_PATH/var/cache/prod"
    "$PRESTASHOP_PATH/var/cache/dev"
    "$PRESTASHOP_PATH/cache"
)

for CACHE_DIR in "${CACHE_DIRS[@]}"; do
    if [ -d "$CACHE_DIR" ]; then
        rm -rf "$CACHE_DIR"/*
        success "Cache vidé: $CACHE_DIR"
    fi
done

# Supprimer le fichier class_index.php
if [ -f "$PRESTASHOP_PATH/var/cache/prod/class_index.php" ]; then
    rm -f "$PRESTASHOP_PATH/var/cache/prod/class_index.php"
    success "Fichier class_index.php supprimé"
fi

# Nettoyer la base de données (optionnel)
echo ""
info "Note: Les entrées en base de données ne sont pas supprimées automatiquement"
info "Pour nettoyer complètement, exécutez ces requêtes SQL :"
echo ""
echo -e "${YELLOW}DELETE FROM ps_module WHERE name = 'productstatusinorder';${NC}"
echo -e "${YELLOW}DELETE FROM ps_hook_module WHERE id_module NOT IN (SELECT id_module FROM ps_module);${NC}"
echo ""

echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║           Nettoyage terminé avec succès ! 🧹              ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

success "Le module a été supprimé"
info "Vous pouvez maintenant réinstaller le module si nécessaire"
echo ""

exit 0
