{**
 * Order Invoice Upload - Template d'erreur front-office
 *
 * Affiche un message d'erreur propre en cas de problème
 * lors du téléchargement d'une facture.
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 *}

{extends file='page.tpl'}

{block name='page_title'}
    {l s='Erreur' mod='orderinvoiceupload'}
{/block}

{block name='page_content'}
    <div class="alert alert-danger" role="alert">
        <p>
            <i class="material-icons">error</i>
            {$error_message|escape:'html':'UTF-8'}
        </p>
    </div>

    <div class="orderinvoiceupload-error-actions">
        <a href="{$link->getPageLink('history', true)|escape:'html':'UTF-8'}" class="btn btn-primary">
            <i class="material-icons">arrow_back</i>
            {l s='Retour à mes commandes' mod='orderinvoiceupload'}
        </a>
    </div>
{/block}
