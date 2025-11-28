{**
 * Order Invoice Upload - Template front pour le détail de commande
 *
 * Ce template affiche le bloc "Facture associée" sur la page
 * de détail d'une commande côté client.
 *
 * Affiché via le hook displayOrderDetail
 *
 * Variables disponibles :
 * - $orderinvoiceupload_invoice : Objet facture (ou null)
 * - $orderinvoiceupload_download_url : URL de téléchargement
 * - $orderinvoiceupload_order_reference : Référence de la commande
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 *}

{if $orderinvoiceupload_invoice}
    <div class="box orderinvoiceupload-front-block">
        <h3 class="page-subheading">
            <i class="material-icons">receipt</i>
            {l s='Facture associée' mod='orderinvoiceupload'}
        </h3>

        <div class="orderinvoiceupload-invoice-info">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>{l s='Nom du fichier' mod='orderinvoiceupload'}</th>
                        <td>
                            <i class="material-icons text-danger">picture_as_pdf</i>
                            {$orderinvoiceupload_invoice->original_name|escape:'html':'UTF-8'}
                        </td>
                    </tr>
                    <tr>
                        <th>{l s='Date d\'ajout' mod='orderinvoiceupload'}</th>
                        <td>{$orderinvoiceupload_invoice->date_add|date_format:'%d/%m/%Y'}</td>
                    </tr>
                </tbody>
            </table>

            <div class="orderinvoiceupload-download-btn">
                <a href="{$orderinvoiceupload_download_url|escape:'html':'UTF-8'}"
                   class="btn btn-primary"
                   title="{l s='Télécharger la facture' mod='orderinvoiceupload'}">
                    <i class="material-icons">download</i>
                    {l s='Télécharger la facture' mod='orderinvoiceupload'}
                </a>
            </div>
        </div>
    </div>
{/if}
