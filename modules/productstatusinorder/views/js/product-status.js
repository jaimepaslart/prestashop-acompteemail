/**
 * Product Status In Order - JavaScript
 *
 * Displays active/inactive badge for products in the order creation product search
 *
 * @author Paul Bihr
 * @license MIT
 */

$(document).ready(function() {

    // Intercept AJAX calls to searchProducts action
    $(document).ajaxComplete(function(_event, xhr, settings) {
        // Check if this is a searchProducts AJAX call
        if (settings.data && settings.data.indexOf('action=searchProducts') !== -1) {
            try {
                var response = JSON.parse(xhr.responseText);

                // If products were found, add status badges to the select options
                if (response.found && response.products) {
                    // Wait a short moment for the DOM to update
                    setTimeout(function() {
                        addProductStatusBadges(response.products);
                    }, 100);
                }
            } catch (e) {
                // Silent fail if JSON parsing fails
            }
        }
    });

    /**
     * Add status badges to product select options
     * @param {Array} products - Array of products from AJAX response
     */
    function addProductStatusBadges(products) {
        var $productSelect = $('#id_product');

        if ($productSelect.length === 0) {
            return;
        }

        // Loop through products and update corresponding options
        $.each(products, function(_index, product) {
            // Find the option for this product
            var $option = $productSelect.find('option[value="' + product.id_product + '"]');

            if ($option.length > 0) {
                var currentText = $option.text();

                // Check if badge is not already added
                if (currentText.indexOf('🟢') === -1 && currentText.indexOf('🔴') === -1) {
                    // Determine status with emoji
                    var statusEmoji = product.active == 1 ? '🟢' : '🔴';
                    var statusText = product.active == 1 ? 'Actif' : 'Inactif';

                    // Add emoji and status text at the beginning for better visibility
                    var newText = statusEmoji + ' ' + currentText + ' [' + statusText + ']';
                    $option.text(newText);

                    // Add data attribute for potential future use
                    $option.attr('data-product-status', product.active == 1 ? 'active' : 'inactive');
                }
            }
        });
    }
});
