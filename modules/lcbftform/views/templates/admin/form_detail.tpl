{**
 * Template de detail d'un formulaire LCB-FT (Back-Office)
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 *}

<div class="panel lcbft-detail-panel">
    <div class="panel-heading">
        <i class="icon-file-text"></i>
        {l s='Formulaire LCB-FT #%d' sprintf=[$lcbft_form->id] mod='lcbftform'}
        {if $lcbft_form->acknowledged}
            <span class="badge badge-success pull-right">{l s='Signé' mod='lcbftform'}</span>
        {else}
            <span class="badge badge-warning pull-right">{l s='Non signé' mod='lcbftform'}</span>
        {/if}
    </div>

    <div class="row">
        {* Colonne gauche : Informations *}
        <div class="col-lg-8">
            {* Informations generales *}
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-info-circle"></i> {l s='Informations générales' mod='lcbftform'}
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>{l s='ID :' mod='lcbftform'}</strong> #{$lcbft_form->id|intval}</p>
                        <p><strong>{l s='Client :' mod='lcbftform'}</strong>
                            {if isset($lcbft_customer) && $lcbft_customer->id}
                                <a href="{$link->getAdminLink('AdminCustomers', true, [], ['viewcustomer' => 1, 'id_customer' => $lcbft_customer->id])|escape:'htmlall':'UTF-8'}">
                                    {$lcbft_customer->firstname|escape:'htmlall':'UTF-8'} {$lcbft_customer->lastname|escape:'htmlall':'UTF-8'}
                                </a>
                            {else}
                                #{$lcbft_form->id_customer|intval}
                            {/if}
                        </p>
                        <p><strong>{l s='Créé le :' mod='lcbftform'}</strong> {$lcbft_form->date_add|escape:'htmlall':'UTF-8'}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>{l s='Commande :' mod='lcbftform'}</strong>
                            {if isset($lcbft_order) && $lcbft_order}
                                <a href="{$link->getAdminLink('AdminOrders', true, [], ['vieworder' => 1, 'id_order' => $lcbft_order->id])|escape:'htmlall':'UTF-8'}" class="badge badge-primary">
                                    #{$lcbft_order->reference|escape:'htmlall':'UTF-8'}
                                </a>
                            {else}
                                <span class="badge badge-secondary">{l s='Aucune' mod='lcbftform'}</span>
                            {/if}
                        </p>
                        <p><strong>{l s='Panier :' mod='lcbftform'}</strong> #{$lcbft_form->id_cart|intval}</p>
                        <p><strong>{l s='Modifié le :' mod='lcbftform'}</strong> {$lcbft_form->date_upd|escape:'htmlall':'UTF-8'}</p>
                    </div>
                </div>
            </div>

            {* Coordonnees *}
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-user"></i> {l s='Coordonnées' mod='lcbftform'}
                </div>
                <table class="table">
                    <tr><td width="200"><strong>{l s='Entreprise' mod='lcbftform'}</strong></td><td>{$lcbft_form->entreprise|escape:'htmlall':'UTF-8'}</td></tr>
                    <tr><td><strong>{l s='Civilité' mod='lcbftform'}</strong></td><td>{$lcbft_form->civilite|escape:'htmlall':'UTF-8'}</td></tr>
                    <tr><td><strong>{l s='Nom' mod='lcbftform'}</strong></td><td>{$lcbft_form->nom|escape:'htmlall':'UTF-8'}</td></tr>
                    <tr><td><strong>{l s='Prénom' mod='lcbftform'}</strong></td><td>{$lcbft_form->prenom|escape:'htmlall':'UTF-8'}</td></tr>
                    <tr><td><strong>{l s='Profession' mod='lcbftform'}</strong></td><td>{$lcbft_form->profession|escape:'htmlall':'UTF-8'}</td></tr>
                    <tr><td><strong>{l s='Identifiant connexion' mod='lcbftform'}</strong></td><td>{$lcbft_form->identifiant_connexion|escape:'htmlall':'UTF-8'}</td></tr>
                    <tr><td><strong>{l s='Adresse' mod='lcbftform'}</strong></td><td>{$lcbft_form->adresse|escape:'htmlall':'UTF-8'}</td></tr>
                    <tr><td><strong>{l s='Code postal' mod='lcbftform'}</strong></td><td>{$lcbft_form->code_postal|escape:'htmlall':'UTF-8'}</td></tr>
                    <tr><td><strong>{l s='Ville' mod='lcbftform'}</strong></td><td>{$lcbft_form->ville|escape:'htmlall':'UTF-8'}</td></tr>
                    <tr><td><strong>{l s='Pays' mod='lcbftform'}</strong></td><td>{$lcbft_form->pays|escape:'htmlall':'UTF-8'}</td></tr>
                    <tr><td><strong>{l s='Email' mod='lcbftform'}</strong></td><td>{$lcbft_form->email|escape:'htmlall':'UTF-8'}</td></tr>
                    <tr><td><strong>{l s='Téléphone' mod='lcbftform'}</strong></td><td>{$lcbft_form->telephone|escape:'htmlall':'UTF-8'}</td></tr>
                </table>
            </div>

            {* Informations patrimoniales *}
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-money"></i> {l s='Informations patrimoniales' mod='lcbftform'}
                </div>
                <table class="table">
                    <tr><td width="200"><strong>{l s='Rémunérations annuelles' mod='lcbftform'}</strong></td><td>{$lcbft_form->remunerations_annuelles|escape:'htmlall':'UTF-8'}</td></tr>
                    <tr><td><strong>{l s='Patrimoine estimé' mod='lcbftform'}</strong></td><td>{$lcbft_form->getPatrimoineLabel()|escape:'htmlall':'UTF-8'}</td></tr>
                    <tr><td><strong>{l s='Soumis à l\'IFI' mod='lcbftform'}</strong></td><td>{if $lcbft_form->soumis_ifi}{l s='Oui' mod='lcbftform'}{else}{l s='Non' mod='lcbftform'}{/if}</td></tr>
                </table>
            </div>

            {* Origine des fonds *}
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-briefcase"></i> {l s='Origine des fonds' mod='lcbftform'}
                </div>
                {if $lcbft_origine_fonds}
                    <ul>
                        {foreach from=$lcbft_origine_fonds item=origine}
                            {if isset($lcbft_origine_labels[$origine])}
                                <li>{$lcbft_origine_labels[$origine]|escape:'htmlall':'UTF-8'}</li>
                            {/if}
                        {/foreach}
                    </ul>
                    {if $lcbft_form->origine_cession_detail}
                        <p class="text-muted"><em>{l s='Cession d\'actifs :' mod='lcbftform'} {$lcbft_form->origine_cession_detail|escape:'htmlall':'UTF-8'}</em></p>
                    {/if}
                    {if $lcbft_form->origine_epargne_detail}
                        <p class="text-muted"><em>{l s='Épargne personnelle :' mod='lcbftform'} {$lcbft_form->origine_epargne_detail|escape:'htmlall':'UTF-8'}</em></p>
                    {/if}
                    {if $lcbft_form->origine_autre_detail}
                        <p class="text-muted"><em>{l s='Autre :' mod='lcbftform'} {$lcbft_form->origine_autre_detail|escape:'htmlall':'UTF-8'}</em></p>
                    {/if}
                {else}
                    <p class="text-muted">{l s='Non renseigné' mod='lcbftform'}</p>
                {/if}
                {if $lcbft_form->justificatif_origine_fonds}
                    <hr>
                    <p><strong>{l s='Justificatif origine fonds (> 15 000 €) :' mod='lcbftform'}</strong><br>{$lcbft_form->justificatif_origine_fonds|escape:'htmlall':'UTF-8'}</p>
                {/if}
            </div>

            {* Commentaires *}
            {if $lcbft_commentaires}
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-comment"></i> {l s='Commentaires' mod='lcbftform'}
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{l s='Date' mod='lcbftform'}</th>
                            <th>{l s='Montant' mod='lcbftform'}</th>
                            <th>{l s='Origine' mod='lcbftform'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$lcbft_commentaires item=comment}
                            <tr>
                                <td>{$comment.date|escape:'htmlall':'UTF-8'}</td>
                                <td>{$comment.montant|escape:'htmlall':'UTF-8'}</td>
                                <td>{$comment.origine|escape:'htmlall':'UTF-8'}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
            {/if}

            {* Justificatifs fournis *}
            {if $lcbft_justificatifs}
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-file"></i> {l s='Justificatifs fournis' mod='lcbftform'}
                </div>
                <ul>
                    {foreach from=$lcbft_justificatifs item=justif}
                        {if isset($lcbft_justificatifs_labels[$justif])}
                            <li>{$lcbft_justificatifs_labels[$justif]|escape:'htmlall':'UTF-8'}</li>
                        {/if}
                    {/foreach}
                </ul>
                {if $lcbft_form->justificatif_autre_detail}
                    <p class="text-muted"><em>{l s='Autre :' mod='lcbftform'} {$lcbft_form->justificatif_autre_detail|escape:'htmlall':'UTF-8'}</em></p>
                {/if}
            </div>
            {/if}
        </div>

        {* Colonne droite : Signature et actions *}
        <div class="col-lg-4">
            {* Bloc signature *}
            <div class="panel {if $lcbft_form->acknowledged}panel-success{else}panel-warning{/if}">
                <div class="panel-heading">
                    <i class="icon-pencil"></i> {l s='Signature' mod='lcbftform'}
                </div>
                <div class="text-center">
                    {if $lcbft_form->acknowledged}
                        <p><strong>{l s='Lieu :' mod='lcbftform'}</strong> {$lcbft_form->lieu_signature|escape:'htmlall':'UTF-8'}</p>
                        <p><strong>{l s='Date :' mod='lcbftform'}</strong> {$lcbft_form->date_signature|escape:'htmlall':'UTF-8'}</p>
                        <hr>
                        <p class="text-muted">{l s='Lu et approuvé' mod='lcbftform'}</p>
                        <p class="lcbft-signature-display">{$lcbft_form->signature_name|escape:'htmlall':'UTF-8'}</p>
                        <p class="small text-muted">{$lcbft_form->getFormattedSignature()|escape:'htmlall':'UTF-8'}</p>
                    {else}
                        <p class="text-warning"><i class="icon-warning"></i> {l s='Ce formulaire n\'a pas été signé.' mod='lcbftform'}</p>
                    {/if}
                </div>
            </div>

            {* Actions *}
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-cogs"></i> {l s='Actions' mod='lcbftform'}
                </div>
                <div class="text-center">
                    {if $lcbft_form->acknowledged}
                        <a href="{$lcbft_pdf_url|escape:'htmlall':'UTF-8'}" class="btn btn-primary btn-lg btn-block">
                            <i class="icon-file-pdf-o"></i> {l s='Télécharger le PDF' mod='lcbftform'}
                        </a>
                    {else}
                        <button class="btn btn-default btn-lg btn-block" disabled>
                            <i class="icon-ban"></i> {l s='PDF non disponible' mod='lcbftform'}
                        </button>
                        <p class="small text-muted">{l s='Le formulaire doit être signé pour générer le PDF.' mod='lcbftform'}</p>
                    {/if}

                    <hr>

                    <a href="{$link->getAdminLink('AdminLcbftForms')|escape:'htmlall':'UTF-8'}" class="btn btn-default">
                        <i class="icon-arrow-left"></i> {l s='Retour à la liste' mod='lcbftform'}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.lcbft-signature-display {
    font-family: 'Times New Roman', serif;
    font-size: 24px;
    font-style: italic;
    color: #000080;
    margin: 10px 0;
}
.panel-success .panel-heading {
    background-color: #dff0d8;
    border-color: #d6e9c6;
    color: #3c763d;
}
.panel-warning .panel-heading {
    background-color: #fcf8e3;
    border-color: #faebcc;
    color: #8a6d3b;
}
</style>
