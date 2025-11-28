{**
 * Order Invoice Upload - Template pour la page commande
 *
 * Ce template affiche :
 * - Le formulaire d'upload de facture
 * - Les informations sur la facture existante
 * - Les boutons de téléchargement et suppression
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 *}

<div class="card orderinvoiceupload-card" id="orderinvoiceupload-block">
    <div class="card-header">
        <h3 class="card-header-title">
            <i class="material-icons">receipt</i>
            {l s='Facture manuelle' mod='orderinvoiceupload'}
        </h3>
    </div>

    <div class="card-body">
        {* Messages d'erreur *}
        {if isset($orderinvoiceupload_messages.errors) && $orderinvoiceupload_messages.errors|count > 0}
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <ul class="list-unstyled">
                    {foreach from=$orderinvoiceupload_messages.errors item=error}
                        <li><i class="material-icons">error</i> {$error|escape:'html':'UTF-8'}</li>
                    {/foreach}
                </ul>
            </div>
        {/if}

        {* Messages de succès *}
        {if isset($orderinvoiceupload_messages.success) && $orderinvoiceupload_messages.success|count > 0}
            <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <ul class="list-unstyled">
                    {foreach from=$orderinvoiceupload_messages.success item=success}
                        <li><i class="material-icons">check_circle</i> {$success|escape:'html':'UTF-8'}</li>
                    {/foreach}
                </ul>
            </div>
        {/if}

        {* Section : Facture existante *}
        {if $orderinvoiceupload_invoice}
            <div class="orderinvoiceupload-existing-invoice">
                <div class="alert alert-info">
                    <h4>
                        <i class="material-icons">description</i>
                        {l s='Facture actuellement associée' mod='orderinvoiceupload'}
                    </h4>

                    <table class="table table-bordered table-sm mt-3">
                        <tbody>
                            <tr>
                                <th style="width: 150px;">{l s='Nom du fichier' mod='orderinvoiceupload'}</th>
                                <td>
                                    <i class="material-icons text-danger" style="font-size: 18px; vertical-align: middle;">picture_as_pdf</i>
                                    {$orderinvoiceupload_invoice->original_name|escape:'html':'UTF-8'}
                                </td>
                            </tr>
                            <tr>
                                <th>{l s='Date d\'ajout' mod='orderinvoiceupload'}</th>
                                <td>{$orderinvoiceupload_invoice->date_add|date_format:'%d/%m/%Y %H:%M'}</td>
                            </tr>
                            <tr>
                                <th>{l s='Type MIME' mod='orderinvoiceupload'}</th>
                                <td>{$orderinvoiceupload_invoice->mime_type|escape:'html':'UTF-8'}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="btn-group mt-3">
                        <a href="{$orderinvoiceupload_download_url|escape:'html':'UTF-8'}"
                           class="btn btn-primary"
                           target="_blank"
                           title="{l s='Télécharger la facture' mod='orderinvoiceupload'}">
                            <i class="material-icons">download</i>
                            {l s='Télécharger' mod='orderinvoiceupload'}
                        </a>

                        <button type="button"
                                class="btn btn-danger"
                                data-toggle="modal"
                                data-target="#orderinvoiceupload-delete-modal"
                                title="{l s='Supprimer la facture' mod='orderinvoiceupload'}">
                            <i class="material-icons">delete</i>
                            {l s='Supprimer' mod='orderinvoiceupload'}
                        </button>
                    </div>
                </div>
            </div>

            {* Modal de confirmation de suppression *}
            <div class="modal fade" id="orderinvoiceupload-delete-modal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="material-icons text-danger">warning</i>
                                {l s='Confirmer la suppression' mod='orderinvoiceupload'}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>{l s='Êtes-vous sûr de vouloir supprimer cette facture ?' mod='orderinvoiceupload'}</p>
                            <p><strong>{$orderinvoiceupload_invoice->original_name|escape:'html':'UTF-8'}</strong></p>
                            <p class="text-danger">{l s='Cette action est irréversible.' mod='orderinvoiceupload'}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                {l s='Annuler' mod='orderinvoiceupload'}
                            </button>
                            <form method="post" action="{$orderinvoiceupload_upload_url|escape:'html':'UTF-8'}" style="display: inline;">
                                <input type="hidden" name="token" value="{$orderinvoiceupload_token|escape:'html':'UTF-8'}">
                                <input type="hidden" name="id_order" value="{$orderinvoiceupload_id_order|intval}">
                                <button type="submit" name="deleteOrderInvoice" class="btn btn-danger">
                                    <i class="material-icons">delete</i>
                                    {l s='Supprimer définitivement' mod='orderinvoiceupload'}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            <h4 class="mt-3">
                <i class="material-icons">swap_horiz</i>
                {l s='Remplacer la facture' mod='orderinvoiceupload'}
            </h4>
        {else}
            <div class="alert alert-warning">
                <i class="material-icons">info</i>
                {l s='Aucune facture manuelle n\'est actuellement associée à cette commande.' mod='orderinvoiceupload'}
            </div>

            <h4>
                <i class="material-icons">cloud_upload</i>
                {l s='Téléverser une facture' mod='orderinvoiceupload'}
            </h4>
        {/if}

        {* Formulaire d'upload *}
        <form method="post"
              action="{$orderinvoiceupload_upload_url|escape:'html':'UTF-8'}"
              enctype="multipart/form-data"
              class="orderinvoiceupload-form mt-3"
              id="orderinvoiceupload-form">

            <input type="hidden" name="token" value="{$orderinvoiceupload_token|escape:'html':'UTF-8'}">
            <input type="hidden" name="id_order" value="{$orderinvoiceupload_id_order|intval}">
            <input type="hidden" name="submitOrderInvoiceUpload" value="1">

            <div class="form-group">
                <label for="invoice_file" class="form-control-label">
                    {l s='Fichier PDF' mod='orderinvoiceupload'}
                    <span class="text-danger">*</span>
                </label>

                <div class="custom-file">
                    <input type="file"
                           class="custom-file-input"
                           id="invoice_file"
                           name="invoice_file"
                           accept=".pdf,application/pdf"
                           required>
                    <label class="custom-file-label" for="invoice_file" data-browse="{l s='Parcourir' mod='orderinvoiceupload'}">
                        {l s='Choisir un fichier...' mod='orderinvoiceupload'}
                    </label>
                </div>

                <small class="form-text text-muted">
                    <i class="material-icons" style="font-size: 14px; vertical-align: middle;">info</i>
                    {l s='Format accepté :' mod='orderinvoiceupload'} {$orderinvoiceupload_allowed_extensions|escape:'html':'UTF-8'|upper}
                    — {l s='Taille max :' mod='orderinvoiceupload'} {$orderinvoiceupload_max_size_mb} Mo
                </small>
            </div>

            <div class="form-group">
                <button type="submit" name="submitOrderInvoiceUpload" class="btn btn-primary">
                    <i class="material-icons">cloud_upload</i>
                    {if $orderinvoiceupload_invoice}
                        {l s='Remplacer la facture' mod='orderinvoiceupload'}
                    {else}
                        {l s='Téléverser la facture' mod='orderinvoiceupload'}
                    {/if}
                </button>
            </div>
        </form>
    </div>
</div>

{* Script pour afficher le nom du fichier sélectionné *}
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        var fileInput = document.getElementById('invoice_file');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                var fileName = e.target.files[0] ? e.target.files[0].name : '{l s="Choisir un fichier..." mod="orderinvoiceupload" js=1}';
                var label = this.nextElementSibling;
                if (label) {
                    label.innerText = fileName;
                }
            });
        }
    });
</script>
