{**
 * Override Checkout Process - Ajout de l'etape LCB-FT
 *
 * Ce fichier est une copie minimale du template Warehouse original
 * avec l'insertion de l'etape LCB-FT apres "Informations personnelles".
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 *}

{foreach from=$steps item="step" key="index"}
  {* Rendre l'etape PrestaShop normale (identique a l'original) *}
  {render identifier  =  $step.identifier
          position    =  ($index + 1)
          ui          =  $step.ui
  }

  {* Apres l'etape personal-information, inserer l'etape LCB-FT *}
  {if $step.identifier == 'checkout-personal-information-step'}
    {hook h='displayLcbftCheckoutStep' position=2}
  {/if}
{/foreach}
