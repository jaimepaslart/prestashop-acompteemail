#!/bin/bash

###############################################################################
# Script d'installation du module ProductStatusInOrder pour PrestaShop 1.7.x
# Auteur: Paul Bihr
# Licence: MIT
###############################################################################

set -e  # Arrêter en cas d'erreur

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher un message d'erreur et quitter
error_exit() {
    echo -e "${RED}❌ ERREUR: $1${NC}" >&2
    exit 1
}

# Fonction pour afficher un succès
success() {
    echo -e "${GREEN}✅ $1${NC}"
}

# Fonction pour afficher une information
info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Fonction pour afficher un avertissement
warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Fonction pour afficher le titre
title() {
    echo ""
    echo -e "${BLUE}================================================${NC}"
    echo -e "${BLUE}  Installation du module ProductStatusInOrder${NC}"
    echo -e "${BLUE}================================================${NC}"
    echo ""
}

# Afficher le titre
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

# Supprimer le slash final si présent
PRESTASHOP_PATH="${PRESTASHOP_PATH%/}"

info "Chemin PrestaShop: $PRESTASHOP_PATH"
echo ""

# Vérifier que le répertoire PrestaShop existe
if [ ! -d "$PRESTASHOP_PATH" ]; then
    error_exit "Le répertoire $PRESTASHOP_PATH n'existe pas"
fi

# Vérifier que c'est bien un PrestaShop
if [ ! -f "$PRESTASHOP_PATH/config/config.inc.php" ]; then
    error_exit "Le répertoire $PRESTASHOP_PATH ne semble pas être une installation PrestaShop valide"
fi

success "Installation PrestaShop détectée"

# Vérifier la version PHP
PHP_VERSION=$(php -r "echo PHP_VERSION;" 2>/dev/null || echo "0.0.0")
PHP_MAJOR=$(echo "$PHP_VERSION" | cut -d. -f1)
PHP_MINOR=$(echo "$PHP_VERSION" | cut -d. -f2)

info "Version PHP: $PHP_VERSION"

if [ "$PHP_MAJOR" -lt 7 ] || ([ "$PHP_MAJOR" -eq 7 ] && [ "$PHP_MINOR" -lt 2 ]); then
    error_exit "PHP 7.2 ou supérieur est requis (version actuelle: $PHP_VERSION)"
fi

success "Version PHP compatible"

# Déterminer le répertoire du script
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
MODULE_NAME="productstatusinorder"
MODULE_SOURCE="$SCRIPT_DIR"
MODULE_DEST="$PRESTASHOP_PATH/modules/$MODULE_NAME"

info "Répertoire source: $MODULE_SOURCE"
info "Répertoire destination: $MODULE_DEST"
echo ""

# Vérifier si le module est déjà installé
if [ -d "$MODULE_DEST" ]; then
    warning "Le module est déjà présent dans $MODULE_DEST"
    read -p "Voulez-vous le remplacer? (y/n): " REPLACE

    if [ "$REPLACE" != "y" ] && [ "$REPLACE" != "Y" ]; then
        info "Installation annulée"
        exit 0
    fi

    info "Suppression de l'ancienne version..."
    rm -rf "$MODULE_DEST"
    success "Ancienne version supprimée"
fi

# Créer le répertoire de destination
info "Copie des fichiers du module..."
mkdir -p "$MODULE_DEST"

# Copier les fichiers (en excluant les fichiers inutiles)
rsync -av --exclude='install.sh' \
          --exclude='INSTALLATION.md' \
          --exclude='.git' \
          --exclude='.DS_Store' \
          --exclude='*.zip' \
          "$MODULE_SOURCE/" "$MODULE_DEST/"

success "Fichiers copiés"

# Définir les permissions
info "Définition des permissions..."
chmod -R 755 "$MODULE_DEST"

# Trouver l'utilisateur du serveur web
WEB_USER=$(ps aux | grep -E 'apache|httpd|nginx|www-data' | grep -v grep | head -1 | awk '{print $1}')

if [ -n "$WEB_USER" ] && [ "$WEB_USER" != "root" ]; then
    info "Utilisateur du serveur web détecté: $WEB_USER"

    if [ "$(id -u)" -eq 0 ]; then
        chown -R "$WEB_USER:$WEB_USER" "$MODULE_DEST"
        success "Propriétaire changé vers $WEB_USER"
    else
        warning "Impossible de changer le propriétaire (nécessite les droits root)"
        warning "Exécutez manuellement: sudo chown -R $WEB_USER:$WEB_USER $MODULE_DEST"
    fi
fi

success "Permissions définies"

# Vider le cache PrestaShop
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

success "Cache nettoyé"

# Afficher le résumé
echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║           Installation terminée avec succès ! 🎉           ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

info "Le module a été installé dans: $MODULE_DEST"
echo ""

# Instructions pour activer le module
echo -e "${BLUE}📋 Prochaines étapes:${NC}"
echo ""
echo "1️⃣  Connectez-vous au Back-Office PrestaShop"
echo "2️⃣  Allez dans: Modules > Module Manager"
echo "3️⃣  Cherchez: \"Product Status In Order\""
echo "4️⃣  Cliquez sur: \"Installer\""
echo "5️⃣  Testez le module:"
echo "    → Ventes > Commandes > Ajouter une commande"
echo "    → Cherchez un produit"
echo "    → Vous devriez voir: 🟢 [Actif] et 🔴 [Inactif]"
echo ""

# Afficher les informations de configuration
info "Configuration du module:"
echo "  • Hook: actionAdminControllerSetMedia"
echo "  • Version: 1.0.0"
echo "  • Compatibilité: PrestaShop 1.7.x"
echo "  • PHP requis: 7.2+"
echo ""

info "Documentation complète: $MODULE_SOURCE/README.md"
echo ""

success "Installation terminée !"
echo ""

exit 0
