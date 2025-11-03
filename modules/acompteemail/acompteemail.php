<?php
/**
 * Module Acompte Email
 *
 * Affiche correctement l'acompte payé et le reste à payer dans les emails de confirmation de commande
 *
 * @author  Paul Bihr
 * @version 1.0.0
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AcompteEmail extends Module
{
    public function __construct()
    {
        $this->name = 'acompteemail';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Paul Bihr';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Acompte Email');
        $this->description = $this->l('Affiche l\'acompte payé et le reste à payer dans les emails de confirmation de commande');
        $this->confirmUninstall = $this->l('Êtes-vous sûr de vouloir désinstaller ce module ?');
    }

    /**
     * Installation du module
     */
    public function install()
    {
        return parent::install()
            && $this->registerHook('actionEmailSendBefore');
    }

    /**
     * Désinstallation du module
     */
    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Hook appelé avant l'envoi de chaque email
     *
     * @param array $params Paramètres de l'email
     * @return void
     */
    public function hookActionEmailSendBefore($params)
    {
        // Protection globale en cas d'erreur
        try {
            // Ne traiter que les emails de confirmation de commande
            if (!isset($params['template']) || $params['template'] !== 'order_conf') {
                return;
            }

            // Récupérer les variables du template
            if (!isset($params['templateVars']) || !is_array($params['templateVars'])) {
                return;
            }

            $templateVars = &$params['templateVars'];

            // Récupérer l'ID de la commande depuis les variables du template
            $id_order = null;

            if (isset($templateVars['{id_order}'])) {
                $id_order = (int)$templateVars['{id_order}'];
            } elseif (isset($templateVars['id_order'])) {
                $id_order = (int)$templateVars['id_order'];
            } elseif (isset($templateVars['{order_name}'])) {
                // Parfois l'ID est dans order_name
                preg_match('/(\d+)/', $templateVars['{order_name}'], $matches);
                if (isset($matches[1])) {
                    $id_order = (int)$matches[1];
                }
            }

            // Si on n'a pas trouvé l'ID, on arrête
            if (!$id_order) {
                return;
            }

            // Charger la commande
            $order = new Order($id_order);
            if (!Validate::isLoadedObject($order)) {
                return;
            }

            // Récupérer le total de la commande
            // On utilise la même valeur que celle affichée dans l'email (total_paid_tax_incl)
            $total = (float)$order->total_paid_tax_incl;

            // Calculer le montant total payé via les paiements
            $paid = 0;
            $payments = $order->getOrderPayments();

            if (is_array($payments)) {
                foreach ($payments as $payment) {
                    if (isset($payment->amount)) {
                        $paid += (float)$payment->amount;
                    }
                }
            }

            // Calculer le reste à payer
            $remaining = max(0, round($total - $paid, 2));

            // Récupérer la devise de la commande
            $currency = new Currency($order->id_currency);

            // Ajouter les valeurs brutes (pour les conditions dans le template)
            $templateVars['{amount_paid_raw}'] = $paid;
            $templateVars['{amount_remaining_raw}'] = $remaining;
            $templateVars['{total_to_pay_raw}'] = $total;

            // Ajouter les valeurs formatées avec la devise
            $templateVars['{amount_paid}'] = Tools::displayPrice($paid, $currency);
            $templateVars['{amount_remaining}'] = Tools::displayPrice($remaining, $currency);
            $templateVars['{total_to_pay}'] = Tools::displayPrice($total, $currency);

            // Ajouter un flag pour savoir si la commande est soldée
            $templateVars['{is_fully_paid}'] = ($remaining <= 0) ? 1 : 0;

        } catch (Exception $e) {
            // En cas d'erreur, on ne fait rien pour que l'email parte quand même
            // On peut logger l'erreur si besoin
            PrestaShopLogger::addLog(
                'AcompteEmail : Erreur dans hookActionEmailSendBefore : ' . $e->getMessage(),
                2,
                null,
                'Order',
                (int)$id_order,
                true
            );
        }
    }
}
