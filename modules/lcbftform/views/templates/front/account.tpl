{**
 * Template - Espace client LCB-FT
 *
 * Liste des formulaires du client avec options de telechargement
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 *}
{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='Mes formulaires LCB-FT' d='Modules.Lcbftform.Shop'}
{/block}

{block name='page_content'}
    <h6>{l s='Voici vos formulaires LCB-FT (Lutte Contre le Blanchiment et Financement du Terrorisme).' d='Modules.Lcbftform.Shop'}</h6>

    {if $has_forms}
        {* Version desktop *}
        <table class="table table-striped table-bordered table-labeled hidden-sm-down">
            <thead class="thead-default">
                <tr>
                    <th>{l s='Nom' d='Modules.Lcbftform.Shop'}</th>
                    <th>{l s='Prenom' d='Modules.Lcbftform.Shop'}</th>
                    <th>{l s='Commande' d='Modules.Lcbftform.Shop'}</th>
                    <th>{l s='Date' d='Modules.Lcbftform.Shop'}</th>
                    <th>{l s='Statut' d='Modules.Lcbftform.Shop'}</th>
                    <th>{l s='Actions' d='Modules.Lcbftform.Shop'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$forms item=form}
                    <tr>
                        <td>{$form.nom|escape:'html':'UTF-8'}</td>
                        <td>{$form.prenom|escape:'html':'UTF-8'}</td>
                        <td>
                            {if $form.order_reference}
                                <a href="{$form.order_link}">{$form.order_reference}</a>
                            {else}
                                -
                            {/if}
                        </td>
                        <td>{$form.date_add|date_format:'%d/%m/%Y'}</td>
                        <td>
                            {if $form.signed}
                                <span class="label label-pill" style="background-color:#4cbb6c;color:#fff;">
                                    {l s='Signe' d='Modules.Lcbftform.Shop'}
                                </span>
                            {else}
                                <span class="label label-pill" style="background-color:#f39d12;color:#fff;">
                                    {l s='En attente' d='Modules.Lcbftform.Shop'}
                                </span>
                            {/if}
                        </td>
                        <td class="text-sm-center">
                            {if $form.signed && $form.download_link}
                                <a href="{$form.download_link}" title="{l s='Telecharger le PDF' d='Modules.Lcbftform.Shop'}">
                                    <i class="material-icons">&#xE415;</i>
                                    {l s='PDF' d='Modules.Lcbftform.Shop'}
                                </a>
                            {else}
                                <span class="text-muted">{l s='Non disponible' d='Modules.Lcbftform.Shop'}</span>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>

        {* Version mobile *}
        <div class="lcbft-forms-mobile hidden-md-up">
            {foreach from=$forms item=form}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xs-8">
                                <h5 class="card-title">{$form.prenom|escape:'html':'UTF-8'} {$form.nom|escape:'html':'UTF-8'}</h5>
                                <p class="card-text">
                                    <small class="text-muted">{$form.date_add|date_format:'%d/%m/%Y'}</small>
                                    {if $form.order_reference}
                                        <br><a href="{$form.order_link}">Commande {$form.order_reference}</a>
                                    {/if}
                                </p>
                                {if $form.signed}
                                    <span class="label label-pill" style="background-color:#4cbb6c;color:#fff;">
                                        {l s='Signe' d='Modules.Lcbftform.Shop'}
                                    </span>
                                {else}
                                    <span class="label label-pill" style="background-color:#f39d12;color:#fff;">
                                        {l s='En attente' d='Modules.Lcbftform.Shop'}
                                    </span>
                                {/if}
                            </div>
                            <div class="col-xs-4 text-xs-right">
                                {if $form.signed && $form.download_link}
                                    <a href="{$form.download_link}" class="btn btn-primary btn-sm">
                                        <i class="material-icons">&#xE415;</i>
                                    </a>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>

    {else}
        <div class="alert alert-info">
            {l s='Vous n\'avez pas encore de formulaire LCB-FT.' d='Modules.Lcbftform.Shop'}
        </div>
    {/if}
{/block}
