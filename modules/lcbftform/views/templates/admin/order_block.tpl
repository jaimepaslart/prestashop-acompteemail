{**
 * Bloc LCB-FT sur la page de detail commande (Back-Office)
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 *}

<div class="panel lcbft-order-block">
    <div class="panel-heading">
        <i class="icon-file-text"></i>
        {l s='Formulaire LCB-FT' mod='lcbftform'}
    </div>

    {if $lcbft_has_form}
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <p>
                        <strong>{l s='Client :' mod='lcbftform'}</strong>
                        {$lcbft_form->prenom|escape:'htmlall':'UTF-8'} {$lcbft_form->nom|escape:'htmlall':'UTF-8'}
                    </p>
                    <p>
                        <strong>{l s='Statut :' mod='lcbftform'}</strong>
                        {if $lcbft_form->acknowledged}
                            <span class="label label-success">{l s='Signé' mod='lcbftform'}</span>
                        {else}
                            <span class="label label-warning">{l s='Non signé' mod='lcbftform'}</span>
                        {/if}
                    </p>
                </div>
                <div class="col-md-6">
                    {if $lcbft_form->acknowledged && $lcbft_form->signed_at}
                        <p>
                            <strong>{l s='Signature :' mod='lcbftform'}</strong><br>
                            <small class="text-muted">{$lcbft_form->getFormattedSignature()|escape:'htmlall':'UTF-8'}</small>
                        </p>
                    {/if}
                </div>
            </div>

            <hr>

            <div class="text-center">
                <a href="{$lcbft_detail_url|escape:'htmlall':'UTF-8'}" class="btn btn-default">
                    <i class="icon-eye"></i> {l s='Voir le détail' mod='lcbftform'}
                </a>
                {if $lcbft_form->acknowledged}
                    <a href="{$lcbft_download_url|escape:'htmlall':'UTF-8'}" class="btn btn-primary">
                        <i class="icon-file-pdf-o"></i> {l s='Télécharger PDF' mod='lcbftform'}
                    </a>
                {/if}
            </div>
        </div>
    {else}
        <div class="panel-body text-center text-muted">
            <i class="icon-warning" style="font-size: 24px;"></i>
            <p>{l s='Aucun formulaire LCB-FT associé à cette commande.' mod='lcbftform'}</p>
        </div>
    {/if}
</div>
