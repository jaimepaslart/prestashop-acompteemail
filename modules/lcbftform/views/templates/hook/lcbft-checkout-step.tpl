{**
 * Template de l'etape LCB-FT dans le tunnel de commande
 *
 * Cette etape dediee apparait apres l'etape "Informations personnelles"
 * et avant l'etape "Adresses".
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 *}

<section id="checkout-lcbft-step"
         class="checkout-step {if $lcbft_step_is_current}-current js-current-step{/if} {if $lcbft_step_is_reachable}-reachable{/if} {if $lcbft_step_is_complete}-complete{/if}"
         data-step="lcbft"
>
    <h1 class="step-title h3">
        <span class="step-number">{$lcbft_position|intval}.</span>
        {l s='Formulaire reglementaire' mod='lcbftform'}
        <span class="step-edit text-muted"><i class="fa fa-pencil" aria-hidden="true"></i> {l s='edit' d='Shop.Theme.Actions'}</span>
    </h1>

    <div class="content">
        {if !$lcbft_step_is_reachable}
            <p class="alert alert-info">
                {l s='Veuillez d\'abord remplir vos informations personnelles.' mod='lcbftform'}
            </p>
        {else}
            {$lcbft_form_content nofilter}

            {if $lcbft_step_is_complete}
                <div class="clearfix mt-3">
                    <button type="button" id="lcbft-continue-btn" class="continue btn btn-primary btn-block btn-lg">
                        {l s='Continue' d='Shop.Theme.Actions'}
                    </button>
                </div>
            {/if}
        {/if}
    </div>
</section>
