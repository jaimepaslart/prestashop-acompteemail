{**
 * Override Checkout Process - Ajout de l'etape LCB-FT
 *
 * Insere une etape dediee au formulaire LCB-FT apres l'etape 1 (Informations personnelles)
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 *}

{* Variable pour suivre si on a passe l'etape personal-information *}
{assign var="lcbft_step_inserted" value=false}
{assign var="position_offset" value=0}

{foreach from=$steps item="step" key="index"}
  {* Calculer la position reelle *}
  {assign var="real_position" value=($index + 1 + $position_offset)}

  {* Rendre l'etape normale *}
  {render identifier  =  $step.identifier
          position    =  $real_position
          ui          =  $step.ui
  }

  {* Apres l'etape personal-information, inserer l'etape LCB-FT *}
  {if $step.identifier == 'checkout-personal-information-step' && !$lcbft_step_inserted}
    {assign var="lcbft_step_inserted" value=true}
    {assign var="position_offset" value=1}

    {* Etape LCB-FT - position 2 *}
    {include file='checkout/_partials/steps/lcbft-step.tpl'
             position=($index + 2)
             step_is_complete=$step.ui.is_complete
    }
  {/if}
{/foreach}
