{**
 * Template de l'etape LCB-FT dans le tunnel de commande
 *
 * Cette etape dediee apparait apres l'etape "Informations personnelles"
 * et avant l'etape "Adresses".
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 *}

{* Recuperer le statut du formulaire via le hook *}
{assign var="lcbft_form_content" value={hook h='displayLcbftFormStep'}}
{assign var="lcbft_is_complete" value={hook h='displayLcbftFormStatus'}}

{* Determiner l'etat de l'etape *}
{assign var="lcbft_step_is_current" value=false}
{assign var="lcbft_step_is_reachable" value=false}
{assign var="lcbft_step_is_complete" value=false}

{* L'etape est complete si le formulaire est signe *}
{if $lcbft_is_complete == '1'}
    {assign var="lcbft_step_is_complete" value=true}
{/if}

{* L'etape est accessible si l'etape precedente (personal-info) est complete *}
{if isset($step_is_complete) && $step_is_complete}
    {assign var="lcbft_step_is_reachable" value=true}
{/if}

{* L'etape est courante si accessible et non complete *}
{if $lcbft_step_is_reachable && !$lcbft_step_is_complete}
    {assign var="lcbft_step_is_current" value=true}
{/if}

<section id="checkout-lcbft-step"
         class="checkout-step {if $lcbft_step_is_current}-current js-current-step{/if} {if $lcbft_step_is_reachable}-reachable{/if} {if $lcbft_step_is_complete}-complete{/if}"
         data-step="lcbft"
>
    <h1 class="step-title h3">
        <span class="step-number">{$position}.</span>
        {l s='Formulaire reglementaire' d='Shop.Theme.Checkout'}
        <i class="fa fa-check rtl-no-flip done" aria-hidden="true"></i>
        <span class="step-edit text-muted"><i class="fa fa-pencil" aria-hidden="true"></i> {l s='edit' d='Shop.Theme.Actions'}</span>
    </h1>

    <div class="content">
        {* Message si l'etape precedente n'est pas complete *}
        {if !$lcbft_step_is_reachable}
            <p class="alert alert-info">
                {l s='Veuillez d\'abord remplir vos informations personnelles.' d='Shop.Theme.Checkout'}
            </p>
        {else}
            {* Contenu du formulaire LCB-FT via hook *}
            {$lcbft_form_content nofilter}

            {* Bouton continuer si formulaire complete *}
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
