{**
 * Template d'erreur pour le front-office
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 *}

{extends file='page.tpl'}

{block name='page_title'}
    {l s='Erreur' mod='lcbftform'}
{/block}

{block name='page_content'}
    <div class="alert alert-danger">
        <h4>{l s='Une erreur est survenue' mod='lcbftform'}</h4>
        {if isset($errors) && $errors}
            <ul class="mb-0">
                {foreach from=$errors item=error}
                    <li>{$error|escape:'htmlall':'UTF-8'}</li>
                {/foreach}
            </ul>
        {/if}
    </div>
    <a href="{$urls.pages.my_account}" class="btn btn-primary">
        {l s='Retour à mon compte' mod='lcbftform'}
    </a>
{/block}
