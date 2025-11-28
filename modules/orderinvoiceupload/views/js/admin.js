/**
 * Order Invoice Upload - JavaScript pour le Back Office
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

(function() {
    'use strict';

    /**
     * Module OrderInvoiceUpload
     * Gère les interactions JavaScript pour l'upload de factures
     */
    var OrderInvoiceUpload = {

        /**
         * Configuration
         */
        config: {
            maxFileSize: 5242880, // 5 Mo (doit correspondre à la valeur PHP)
            allowedExtensions: ['pdf'],
            allowedMimeTypes: ['application/pdf']
        },

        /**
         * Éléments DOM
         */
        elements: {
            form: null,
            fileInput: null,
            fileLabel: null,
            submitButton: null
        },

        /**
         * Initialisation du module
         */
        init: function() {
            this.cacheElements();
            this.bindEvents();
            console.log('[OrderInvoiceUpload] Module initialisé');
        },

        /**
         * Cache les éléments DOM
         */
        cacheElements: function() {
            this.elements.form = document.getElementById('orderinvoiceupload-form');
            this.elements.fileInput = document.getElementById('invoice_file');

            if (this.elements.fileInput) {
                this.elements.fileLabel = this.elements.fileInput.nextElementSibling;
                this.elements.submitButton = this.elements.form ?
                    this.elements.form.querySelector('button[type="submit"]') : null;
            }
        },

        /**
         * Lie les événements
         */
        bindEvents: function() {
            var self = this;

            // Événement de changement de fichier
            if (this.elements.fileInput) {
                this.elements.fileInput.addEventListener('change', function(e) {
                    self.handleFileChange(e);
                });

                // Drag & drop (optionnel)
                this.elements.fileInput.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                });

                this.elements.fileInput.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                });

                this.elements.fileInput.addEventListener('drop', function(e) {
                    this.classList.remove('dragover');
                });
            }

            // Validation du formulaire avant soumission
            if (this.elements.form) {
                this.elements.form.addEventListener('submit', function(e) {
                    if (!self.validateForm()) {
                        e.preventDefault();
                        return false;
                    }

                    // Afficher un indicateur de chargement
                    self.showLoading();
                });
            }
        },

        /**
         * Gère le changement de fichier
         * @param {Event} e Événement de changement
         */
        handleFileChange: function(e) {
            var file = e.target.files[0];

            if (!file) {
                this.resetFileInput();
                return;
            }

            // Mettre à jour le label avec le nom du fichier
            if (this.elements.fileLabel) {
                this.elements.fileLabel.textContent = file.name;
            }

            // Valider le fichier
            var validation = this.validateFile(file);
            if (!validation.valid) {
                this.showFileError(validation.message);
                this.resetFileInput();
                return;
            }

            // Afficher une prévisualisation (optionnel)
            this.showFileInfo(file);
        },

        /**
         * Valide un fichier avant l'upload
         * @param {File} file Fichier à valider
         * @returns {Object} Résultat de validation {valid: bool, message: string}
         */
        validateFile: function(file) {
            // Vérifier l'extension
            var extension = file.name.split('.').pop().toLowerCase();
            if (this.config.allowedExtensions.indexOf(extension) === -1) {
                return {
                    valid: false,
                    message: 'Extension non autorisée. Seuls les fichiers PDF sont acceptés.'
                };
            }

            // Vérifier le type MIME
            if (this.config.allowedMimeTypes.indexOf(file.type) === -1) {
                return {
                    valid: false,
                    message: 'Type de fichier non autorisé. Seuls les fichiers PDF sont acceptés.'
                };
            }

            // Vérifier la taille
            if (file.size > this.config.maxFileSize) {
                var maxSizeMb = Math.round(this.config.maxFileSize / (1024 * 1024) * 10) / 10;
                return {
                    valid: false,
                    message: 'Le fichier est trop volumineux. Taille maximale : ' + maxSizeMb + ' Mo.'
                };
            }

            return {valid: true, message: ''};
        },

        /**
         * Valide le formulaire complet
         * @returns {boolean} True si valide
         */
        validateForm: function() {
            if (!this.elements.fileInput || !this.elements.fileInput.files[0]) {
                this.showFileError('Veuillez sélectionner un fichier.');
                return false;
            }

            var validation = this.validateFile(this.elements.fileInput.files[0]);
            if (!validation.valid) {
                this.showFileError(validation.message);
                return false;
            }

            return true;
        },

        /**
         * Affiche une erreur de fichier
         * @param {string} message Message d'erreur
         */
        showFileError: function(message) {
            // Utiliser la fonction native PrestaShop si disponible
            if (typeof showErrorMessage === 'function') {
                showErrorMessage(message);
                return;
            }

            // Fallback : alerte simple
            alert(message);
        },

        /**
         * Affiche les infos du fichier sélectionné
         * @param {File} file Fichier
         */
        showFileInfo: function(file) {
            console.log('[OrderInvoiceUpload] Fichier sélectionné:', {
                name: file.name,
                size: this.formatFileSize(file.size),
                type: file.type
            });
        },

        /**
         * Formate une taille de fichier pour l'affichage
         * @param {number} bytes Taille en octets
         * @returns {string} Taille formatée
         */
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 o';
            var k = 1024;
            var sizes = ['o', 'Ko', 'Mo', 'Go'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        /**
         * Réinitialise l'input fichier
         */
        resetFileInput: function() {
            if (this.elements.fileInput) {
                this.elements.fileInput.value = '';
            }
            if (this.elements.fileLabel) {
                this.elements.fileLabel.textContent = 'Choisir un fichier...';
            }
        },

        /**
         * Affiche un indicateur de chargement
         */
        showLoading: function() {
            if (this.elements.submitButton) {
                this.elements.submitButton.disabled = true;
                this.elements.submitButton.innerHTML = '<i class="material-icons">hourglass_empty</i> Téléversement en cours...';
            }
        },

        /**
         * Masque l'indicateur de chargement
         */
        hideLoading: function() {
            if (this.elements.submitButton) {
                this.elements.submitButton.disabled = false;
                this.elements.submitButton.innerHTML = '<i class="material-icons">cloud_upload</i> Téléverser la facture';
            }
        }
    };

    /**
     * Initialisation au chargement du DOM
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            OrderInvoiceUpload.init();
        });
    } else {
        OrderInvoiceUpload.init();
    }

    // Exposer le module globalement (optionnel, pour le debug)
    window.OrderInvoiceUpload = OrderInvoiceUpload;

})();
