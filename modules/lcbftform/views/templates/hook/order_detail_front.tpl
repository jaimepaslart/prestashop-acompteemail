{**
 * Template pour l'affichage du lien PDF dans le compte client
 *
 * Affiche sur la page de detail commande du compte client
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 *}

<div class="lcbft-order-detail-block card mb-3">
    <div class="card-header">
        <h3 class="h5 mb-0">
            <i class="material-icons">&#xE873;</i>
            {l s='Formulaire LCB-FT' mod='lcbftform'}
        </h3>
    </div>
    <div class="card-body">
        <p class="mb-2">
            <strong>{l s='Statut :' mod='lcbftform'}</strong>
            <span class="badge badge-success">{l s='Signé' mod='lcbftform'}</span>
        </p>
        {if isset($lcbft_form) && $lcbft_form->signed_at}
        <p class="mb-3 small text-muted">
            {$lcbft_form->getFormattedSignature()|escape:'htmlall':'UTF-8'}
        </p>
        {/if}
        <a href="{$lcbft_download_url|escape:'htmlall':'UTF-8'}" class="btn btn-outline-primary btn-sm" target="_blank">
            <i class="material-icons">&#xE2C4;</i>
            {l s='Télécharger le formulaire LCB-FT (PDF)' mod='lcbftform'}
        </a>
    </div>
</div>
