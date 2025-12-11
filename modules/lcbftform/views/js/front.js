/**
 * JavaScript Front-Office pour le module LCB-FT Form
 *
 * Gère la validation du formulaire, la soumission AJAX,
 * et le blocage de la progression du checkout.
 *
 * Compatible ES6 / PrestaShop 1.7.6.5
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 */

(function() {
    'use strict';

    /**
     * Module LCB-FT Form
     */
    const LcbftForm = {
        /**
         * Elements DOM
         */
        elements: {
            container: null,
            form: null,
            submitBtn: null,
            acknowledgedCheckbox: null,
            signatureDisplay: null,
            statusIndicator: null,
            errorMessage: null,
            successMessage: null
        },

        /**
         * Configuration
         */
        config: {
            ajaxUrl: '',
            validationError: '',
            isComplete: false
        },

        /**
         * Initialisation
         */
        init: function() {
            // Récupérer les éléments DOM
            this.elements.container = document.getElementById('lcbft-form-container');
            this.elements.lcbftStep = document.getElementById('checkout-lcbft-step');

            // Pas sur la page de checkout si ni le container ni l'étape n'existent
            if (!this.elements.container && !this.elements.lcbftStep) {
                return;
            }

            this.elements.form = document.getElementById('lcbft-form');
            this.elements.submitBtn = document.getElementById('lcbft-submit-btn');
            this.elements.acknowledgedCheckbox = document.getElementById('lcbft_acknowledged');
            this.elements.signatureDisplay = document.getElementById('lcbft-signature-display');
            this.elements.statusIndicator = document.getElementById('lcbft-status-indicator');
            this.elements.errorMessage = document.getElementById('lcbft-error-message');
            this.elements.successMessage = document.getElementById('lcbft-success-message');
            this.elements.showFormLink = document.getElementById('lcbft-show-form-link');
            this.elements.formWrapper = document.getElementById('lcbft-form-wrapper');
            this.elements.continueBtn = document.getElementById('lcbft-continue-btn');

            // Récupérer la configuration
            if (typeof lcbftform_ajax_url !== 'undefined') {
                this.config.ajaxUrl = lcbftform_ajax_url;
            }
            if (typeof lcbftform_validation_error !== 'undefined') {
                this.config.validationError = lcbftform_validation_error;
            }

            // Vérifier si déjà complet via data-attribute ou classe CSS
            if (this.elements.container) {
                this.config.isComplete = this.elements.container.getAttribute('data-form-valid') === '1';
            }
            if (this.elements.lcbftStep && this.elements.lcbftStep.classList.contains('-complete')) {
                this.config.isComplete = true;
            }

            // Attacher les événements
            this.bindEvents();

            // Initialiser les champs conditionnels
            this.initConditionalFields();

            // Gérer l'étape dédiée LCB-FT (si elle existe)
            if (this.elements.lcbftStep) {
                this.handleLcbftStep();
            } else {
                // Mode legacy : blocage des paiements
                this.updatePaymentOptions();
                this.interceptCheckout();
            }
        },

        /**
         * Attacher les événements
         */
        bindEvents: function() {
            const self = this;

            // Soumission du formulaire
            if (this.elements.form) {
                this.elements.form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    self.submitForm();
                });
            }

            // Lien "Afficher/modifier le formulaire"
            if (this.elements.showFormLink) {
                this.elements.showFormLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (self.elements.formWrapper) {
                        const isHidden = self.elements.formWrapper.style.display === 'none';
                        self.elements.formWrapper.style.display = isHidden ? 'block' : 'none';
                        this.textContent = isHidden ? 'Masquer le formulaire' : 'Afficher/modifier le formulaire';
                    }
                });
            }

            // Case de reconnaissance
            if (this.elements.acknowledgedCheckbox) {
                this.elements.acknowledgedCheckbox.addEventListener('change', function() {
                    self.updateSignaturePreview();
                });
            }

            // Champs patrimoine
            const patrimoineRadios = document.querySelectorAll('input[name="patrimoine_estimation"]');
            patrimoineRadios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    self.togglePatrimoinePrecision();
                });
            });

            // Champs origine des fonds
            const origineCheckboxes = document.querySelectorAll('input[name="origine_fonds[]"]');
            origineCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    self.toggleOrigineDetails();
                });
            });

            // Justificatif autre
            const justifAutre = document.querySelector('input[name="justificatifs[]"][value="justif_autre"]');
            if (justifAutre) {
                justifAutre.addEventListener('change', function() {
                    self.toggleJustificatifAutre();
                });
            }
        },

        /**
         * Initialiser les champs conditionnels
         */
        initConditionalFields: function() {
            this.togglePatrimoinePrecision();
            this.toggleOrigineDetails();
            this.toggleJustificatifAutre();
        },

        /**
         * Afficher/masquer le champ précision patrimoine
         */
        togglePatrimoinePrecision: function() {
            const container = document.getElementById('lcbft_patrimoine_precision_container');
            const selected = document.querySelector('input[name="patrimoine_estimation"]:checked');

            if (container) {
                container.style.display = (selected && selected.value === 'more_1500k') ? 'block' : 'none';
            }
        },

        /**
         * Afficher/masquer les champs détail origine des fonds
         */
        toggleOrigineDetails: function() {
            const mappings = {
                'cession': 'lcbft_origine_cession_detail',
                'epargne': 'lcbft_origine_epargne_detail',
                'autre': 'lcbft_origine_autre_detail'
            };

            Object.keys(mappings).forEach(function(value) {
                const checkbox = document.querySelector('input[name="origine_fonds[]"][value="' + value + '"]');
                const container = document.getElementById(mappings[value]);

                if (container && checkbox) {
                    container.style.display = checkbox.checked ? 'block' : 'none';
                }
            });
        },

        /**
         * Afficher/masquer le champ justificatif autre
         */
        toggleJustificatifAutre: function() {
            const checkbox = document.querySelector('input[name="justificatifs[]"][value="justif_autre"]');
            const container = document.getElementById('lcbft_justificatif_autre_detail');

            if (container && checkbox) {
                container.style.display = checkbox.checked ? 'block' : 'none';
            }
        },

        /**
         * Mettre à jour l'aperçu de la signature
         */
        updateSignaturePreview: function() {
            if (!this.elements.signatureDisplay || !this.elements.acknowledgedCheckbox) {
                return;
            }

            if (this.elements.acknowledgedCheckbox.checked) {
                const nom = document.getElementById('lcbft_nom');
                const prenom = document.getElementById('lcbft_prenom');

                if (nom && prenom && nom.value && prenom.value) {
                    const fullName = prenom.value.trim() + ' ' + nom.value.trim();
                    const now = new Date();
                    const dateStr = now.toLocaleDateString('fr-FR');
                    const timeStr = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });

                    this.elements.signatureDisplay.innerHTML =
                        '<span class="lcbft-signature-text">Lu et approuvé</span>' +
                        '<span class="lcbft-signature-name">' + this.escapeHtml(fullName) + '</span>' +
                        '<span class="lcbft-signature-date">Signé électroniquement par ' + this.escapeHtml(fullName) + ' le ' + dateStr + ' à ' + timeStr + '</span>';
                }
            } else {
                this.elements.signatureDisplay.innerHTML =
                    '<span class="lcbft-signature-placeholder">Cochez la case ci-dessus pour signer électroniquement</span>';
            }
        },

        /**
         * Soumission du formulaire via AJAX
         */
        submitForm: function() {
            const self = this;

            // Validation côté client
            if (!this.validateForm()) {
                return;
            }

            // Désactiver le bouton
            if (this.elements.submitBtn) {
                this.elements.submitBtn.disabled = true;
                this.elements.submitBtn.innerHTML = '⏳ Enregistrement...';
            }

            // Préparer les données
            const formData = new FormData(this.elements.form);
            formData.append('action', 'save');

            // Envoyer la requête AJAX
            fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    self.showSuccess(data.message);
                    self.config.isComplete = data.is_complete;

                    // Mettre à jour l'indicateur de statut
                    if (data.is_complete) {
                        self.updateStatusIndicator(true);
                    }

                    // Mettre à jour la signature
                    if (data.signature && self.elements.signatureDisplay) {
                        self.elements.signatureDisplay.innerHTML =
                            '<span class="lcbft-signature-text">Lu et approuvé</span>' +
                            '<span class="lcbft-signature-name">' + formData.get('prenom') + ' ' + formData.get('nom') + '</span>' +
                            '<span class="lcbft-signature-date">' + self.escapeHtml(data.signature) + '</span>';
                    }

                    // Débloquer les options de paiement
                    self.updatePaymentOptions();

                    // Mettre à jour l'attribut data
                    if (self.elements.container) {
                        self.elements.container.setAttribute('data-form-valid', '1');
                    }

                    // Passer à l'étape suivante
                    if (data.is_complete) {
                        // Si on est en mode étape dédiée, passer à l'étape adresses
                        if (self.elements.lcbftStep) {
                            setTimeout(function() {
                                self.goToNextStep();
                            }, 1000);
                        } else {
                            // Mode legacy : recharger la page
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    }
                } else {
                    self.showError(data.message);
                }
            })
            .catch(function(error) {
                console.error('LCB-FT Form Error:', error);
                self.showError('Une erreur est survenue. Veuillez réessayer.');
            })
            .finally(function() {
                // Réactiver le bouton
                if (self.elements.submitBtn) {
                    self.elements.submitBtn.disabled = false;
                    self.elements.submitBtn.innerHTML = '✍️ Valider et signer le formulaire LCB-FT';
                }
            });
        },

        /**
         * Validation côté client
         */
        validateForm: function() {
            let isValid = true;
            const errors = [];

            // Vérifier nom
            const nom = document.getElementById('lcbft_nom');
            if (!nom || !nom.value.trim()) {
                errors.push('Le nom est obligatoire.');
                isValid = false;
                if (nom) nom.classList.add('is-invalid');
            } else {
                if (nom) nom.classList.remove('is-invalid');
            }

            // Vérifier prénom
            const prenom = document.getElementById('lcbft_prenom');
            if (!prenom || !prenom.value.trim()) {
                errors.push('Le prénom est obligatoire.');
                isValid = false;
                if (prenom) prenom.classList.add('is-invalid');
            } else {
                if (prenom) prenom.classList.remove('is-invalid');
            }

            // Vérifier case de reconnaissance
            if (!this.elements.acknowledgedCheckbox || !this.elements.acknowledgedCheckbox.checked) {
                errors.push('Vous devez cocher la case de reconnaissance pour valider le formulaire.');
                isValid = false;
            }

            if (!isValid) {
                this.showError(errors.join('<br>'));
            }

            return isValid;
        },

        /**
         * Intercepter la progression du checkout
         */
        interceptCheckout: function() {
            const self = this;

            // Intercepter les clics sur les boutons de progression
            document.addEventListener('click', function(e) {
                // Vérifier si c'est un bouton de progression checkout
                const target = e.target.closest('.continue, [data-link-action="register-new-customer"], .checkout-step button[type="submit"]');

                if (target) {
                    // Vérifier si on est sur une étape après personal-information
                    const checkoutSteps = document.querySelectorAll('.checkout-step');
                    let personalInfoPassed = false;

                    checkoutSteps.forEach(function(step) {
                        if (step.classList.contains('js-current-step')) {
                            return;
                        }
                        if (step.id === 'checkout-personal-information-step') {
                            personalInfoPassed = true;
                        }
                    });

                    // Si on essaie de continuer depuis l'étape personal-information et formulaire non complet
                    if ((personalInfoPassed || target.closest('#checkout-personal-information-step')) && !self.config.isComplete) {
                        // Bloquer IMMEDIATEMENT de façon synchrone
                        e.preventDefault();
                        e.stopPropagation();

                        // Afficher un message d'erreur
                        self.showCheckoutBlockMessage();

                        // Scroller vers le formulaire
                        if (self.elements.container) {
                            self.scrollToElement(self.elements.container);
                        }
                    }
                }
            }, true);

            // Observer les changements d'étape du checkout
            this.observeCheckoutSteps();
        },

        /**
         * Observer les changements d'étape du checkout
         */
        observeCheckoutSteps: function() {
            const self = this;

            // Utiliser MutationObserver pour détecter les changements
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        // Une étape a changé d'état
                        if (!self.config.isComplete) {
                            self.enforceFormCompletion();
                        }
                    }
                });
            });

            // Observer les étapes du checkout
            const checkoutSteps = document.querySelectorAll('.checkout-step');
            checkoutSteps.forEach(function(step) {
                observer.observe(step, { attributes: true });
            });
        },

        /**
         * Forcer la complétion du formulaire
         */
        enforceFormCompletion: function() {
            const self = this;

            // Vérifier si on a dépassé l'étape personal-information sans formulaire complet
            const addressStep = document.getElementById('checkout-addresses-step');
            if (addressStep && addressStep.classList.contains('js-current-step')) {
                this.checkFormStatus(function(isComplete) {
                    if (!isComplete) {
                        // Revenir à l'étape précédente
                        self.showCheckoutBlockMessage();
                        self.scrollToElement(self.elements.container);
                    }
                });
            }
        },

        /**
         * Vérifier le statut du formulaire via AJAX
         */
        checkFormStatus: function(callback) {
            const self = this;

            if (this.config.isComplete) {
                callback(true);
                return;
            }

            const formData = new FormData();
            formData.append('action', 'check');

            fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                self.config.isComplete = data.is_complete || false;
                callback(self.config.isComplete);
            })
            .catch(function() {
                callback(false);
            });
        },

        /**
         * Afficher le message de blocage du checkout
         */
        showCheckoutBlockMessage: function() {
            // Créer une alerte si elle n'existe pas
            let alert = document.getElementById('lcbft-checkout-block-alert');
            if (!alert) {
                alert = document.createElement('div');
                alert.id = 'lcbft-checkout-block-alert';
                alert.className = 'alert alert-danger';
                alert.style.cssText = 'position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; max-width: 90%; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2);';
                alert.innerHTML = '<strong>⚠️ ' + this.config.validationError + '</strong><button type="button" class="close" style="margin-left: 15px;">&times;</button>';
                document.body.appendChild(alert);

                // Fermer au clic
                alert.querySelector('.close').addEventListener('click', function() {
                    alert.remove();
                });

                // Auto-fermeture après 5 secondes
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 5000);
            }

            // Mettre en surbrillance le conteneur du formulaire
            this.elements.container.style.boxShadow = '0 0 20px rgba(220, 53, 69, 0.5)';
            setTimeout(function() {
                if (this.elements && this.elements.container) {
                    this.elements.container.style.boxShadow = '';
                }
            }.bind(this), 3000);
        },

        /**
         * Mettre à jour l'indicateur de statut
         */
        updateStatusIndicator: function(isComplete) {
            if (!this.elements.statusIndicator) {
                return;
            }

            if (isComplete) {
                this.elements.statusIndicator.className = 'lcbft-status lcbft-status-complete';
                this.elements.statusIndicator.innerHTML = '✅ <span>Formulaire LCB-FT signé</span>';
            } else {
                this.elements.statusIndicator.className = 'lcbft-status lcbft-status-pending';
                this.elements.statusIndicator.innerHTML = '📋 <span>Formulaire LCB-FT requis</span>';
            }
        },

        /**
         * Afficher un message d'erreur
         */
        showError: function(message) {
            if (this.elements.errorMessage) {
                this.elements.errorMessage.innerHTML = message;
                this.elements.errorMessage.style.display = 'block';
            }
            if (this.elements.successMessage) {
                this.elements.successMessage.style.display = 'none';
            }
        },

        /**
         * Afficher un message de succès
         */
        showSuccess: function(message) {
            if (this.elements.successMessage) {
                this.elements.successMessage.innerHTML = message;
                this.elements.successMessage.style.display = 'block';
            }
            if (this.elements.errorMessage) {
                this.elements.errorMessage.style.display = 'none';
            }
        },

        /**
         * Échapper le HTML
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * Bloquer/débloquer les options de paiement (mode legacy)
         */
        updatePaymentOptions: function() {
            const self = this;

            // Sélectionner tous les boutons/options de paiement
            const paymentOptions = document.querySelectorAll('.payment-options .payment-option, #payment-confirmation button, .js-payment-option-form');
            const paymentConfirmBtn = document.querySelector('#payment-confirmation button');

            if (this.config.isComplete) {
                // Débloquer les options de paiement
                paymentOptions.forEach(function(option) {
                    option.style.pointerEvents = '';
                    option.style.opacity = '';
                    option.classList.remove('lcbft-blocked');
                });

                if (paymentConfirmBtn) {
                    paymentConfirmBtn.disabled = false;
                    paymentConfirmBtn.title = '';
                }

                // Supprimer le message de blocage s'il existe
                const blockMessage = document.getElementById('lcbft-payment-block-message');
                if (blockMessage) {
                    blockMessage.remove();
                }
            } else {
                // Bloquer les options de paiement
                paymentOptions.forEach(function(option) {
                    option.style.pointerEvents = 'none';
                    option.style.opacity = '0.5';
                    option.classList.add('lcbft-blocked');
                });

                if (paymentConfirmBtn) {
                    paymentConfirmBtn.disabled = true;
                    paymentConfirmBtn.title = 'Veuillez d\'abord remplir et signer le formulaire LCB-FT';
                }

                // Ajouter un message de blocage au-dessus des options de paiement
                const paymentSection = document.querySelector('.payment-options');
                if (paymentSection && !document.getElementById('lcbft-payment-block-message')) {
                    const blockMessage = document.createElement('div');
                    blockMessage.id = 'lcbft-payment-block-message';
                    blockMessage.className = 'alert alert-danger mb-3';
                    blockMessage.innerHTML = '<strong>🚫 Paiement bloqué</strong><br>' +
                        'Vous devez remplir et signer le formulaire LCB-FT ci-dessus avant de pouvoir procéder au paiement.';
                    paymentSection.parentNode.insertBefore(blockMessage, paymentSection);
                }
            }
        },

        /**
         * Gérer l'étape dédiée LCB-FT
         *
         * Cette méthode gère la logique de l'étape dédiée dans le tunnel de commande
         */
        handleLcbftStep: function() {
            const self = this;
            const lcbftStep = this.elements.lcbftStep;
            const continueBtn = this.elements.continueBtn;

            // Gérer le clic sur "Continuer" après signature
            if (continueBtn) {
                continueBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.goToNextStep();
                });
            }

            // Intercepter les clics sur le titre de l'étape pour la rendre cliquable
            const stepTitle = lcbftStep.querySelector('.step-title');
            if (stepTitle) {
                stepTitle.style.cursor = 'pointer';
                stepTitle.addEventListener('click', function() {
                    if (lcbftStep.classList.contains('-complete') || lcbftStep.classList.contains('-reachable')) {
                        self.activateLcbftStep();
                    }
                });
            }

            // Intercepter la progression depuis l'étape personal-information
            document.addEventListener('click', function(e) {
                const target = e.target.closest('#checkout-personal-information-step .continue');
                if (target && !self.config.isComplete) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Activer l'étape LCB-FT au lieu de passer aux adresses
                    self.activateLcbftStep();
                }
            }, true);

            // Observer les changements d'étape
            this.observeLcbftStep();
        },

        /**
         * Activer l'étape LCB-FT
         */
        activateLcbftStep: function() {
            const lcbftStep = this.elements.lcbftStep;

            if (lcbftStep) {
                // Désactiver toutes les autres étapes
                const allSteps = document.querySelectorAll('.checkout-step');
                allSteps.forEach(function(step) {
                    if (step.id !== 'checkout-lcbft-step') {
                        step.classList.remove('-current', 'js-current-step');
                        // Garder -complete et -reachable si déjà présents
                    }
                });

                // Activer l'étape LCB-FT
                lcbftStep.classList.add('-current', 'js-current-step', '-reachable');
                lcbftStep.classList.remove('-complete');

                // Scroller vers l'étape avec un offset pour ne pas coller au haut
                this.scrollToElement(lcbftStep);
            }
        },

        /**
         * Passer à l'étape suivante (Adresses)
         */
        goToNextStep: function() {
            const lcbftStep = this.elements.lcbftStep;

            if (lcbftStep) {
                // Marquer visuellement l'étape LCB-FT comme complète
                lcbftStep.classList.remove('-current', 'js-current-step');
                lcbftStep.classList.add('-complete', '-reachable');

                // Recharger la page pour que PrestaShop gère la transition d'étape
                // côté serveur (plus fiable que la manipulation DOM)
                location.reload();
            }
        },

        /**
         * Scroller vers un élément avec un offset (comme les autres étapes PrestaShop)
         * Animation douce et progressive
         */
        scrollToElement: function(element) {
            if (!element) return;

            // Offset plus grand pour ne pas descendre trop bas (250px depuis le haut)
            const headerOffset = 250;
            const elementPosition = element.getBoundingClientRect().top;
            const targetPosition = elementPosition + window.pageYOffset - headerOffset;
            const startPosition = window.pageYOffset;
            const distance = targetPosition - startPosition;

            // Durée plus longue pour un scroll plus doux (800ms)
            const duration = 800;
            let startTime = null;

            // Fonction d'easing pour un mouvement plus naturel
            function easeOutCubic(t) {
                return 1 - Math.pow(1 - t, 3);
            }

            function animateScroll(currentTime) {
                if (startTime === null) startTime = currentTime;
                const timeElapsed = currentTime - startTime;
                const progress = Math.min(timeElapsed / duration, 1);

                window.scrollTo(0, startPosition + distance * easeOutCubic(progress));

                if (progress < 1) {
                    requestAnimationFrame(animateScroll);
                }
            }

            requestAnimationFrame(animateScroll);
        },

        /**
         * Observer les changements de l'étape LCB-FT
         */
        observeLcbftStep: function() {
            const self = this;
            const lcbftStep = this.elements.lcbftStep;

            if (!lcbftStep) return;

            // Observer les mutations de classe
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        // Mettre à jour le bouton continuer si l'étape devient complète
                        if (lcbftStep.classList.contains('-complete')) {
                            self.config.isComplete = true;
                        }
                    }
                });
            });

            observer.observe(lcbftStep, { attributes: true });
        }
    };

    // Initialiser au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            LcbftForm.init();
        });
    } else {
        LcbftForm.init();
    }

    // Exposer globalement si besoin
    window.LcbftForm = LcbftForm;

})();
