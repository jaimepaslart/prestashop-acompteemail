<?php
/**
 * Product Status In Order
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductStatusInOrder extends Module
{
    public function __construct()
    {
        $this->name = 'productstatusinorder';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Paul Bihr';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => '1.7.8.99');
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product Status In Order');
        $this->description = $this->l('Display product active/inactive status when creating orders in back office');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    /**
     * Install module
     *
     * @return bool
     */
    public function install()
    {
        return parent::install()
            && $this->registerHook('actionAdminControllerSetMedia');
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Hook to add CSS and JS files in back office
     * Only loads on AdminOrders controller (order creation page)
     *
     * @param array $params
     * @return void
     */
    public function hookActionAdminControllerSetMedia($params)
    {
        // Only load assets on AdminOrders controller
        if ($this->context->controller->controller_name !== 'AdminOrders') {
            return;
        }

        // Add CSS
        $this->context->controller->addCSS($this->_path . 'views/css/product-status.css');

        // Add JS
        $this->context->controller->addJS($this->_path . 'views/js/product-status.js');
    }
}
