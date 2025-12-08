{**
 * Template du formulaire LCB-FT dans le tunnel de commande
 *
 * Ce formulaire apparait avant l'etape de paiement
 * et bloque la progression tant qu'il n'est pas signe.
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 *}

<div id="lcbft-form-container" class="lcbft-form-container card mb-3" data-form-valid="{if isset($lcbft_form_valid) && $lcbft_form_valid}1{else}0{/if}">
    <div class="card-header lcbft-header {if isset($lcbft_form_valid) && $lcbft_form_valid}lcbft-header-valid{/if}">
        <h2 class="h4 mb-0">
            {if isset($lcbft_form_valid) && $lcbft_form_valid}
                <i class="material-icons" style="color: #4caf50;">&#xE86C;</i>
            {else}
                <i class="material-icons" style="color: #ff9800;">&#xE002;</i>
            {/if}
            {l s='FORMULAIRE LCB-FT' mod='lcbftform'}
            {if isset($lcbft_form_valid) && $lcbft_form_valid}
                <span class="badge badge-success ml-2">{l s='Signé' mod='lcbftform'}</span>
            {else}
                <span class="badge badge-warning ml-2">{l s='Requis' mod='lcbftform'}</span>
            {/if}
        </h2>
        <p class="mb-0 small">
            {l s='Article L 561-16 du Code monétaire et financier' mod='lcbftform'}
        </p>
    </div>

    <div class="card-body">
        {* Si formulaire deja valide, afficher un resume *}
        {if isset($lcbft_form_valid) && $lcbft_form_valid && isset($lcbft_form) && $lcbft_form}
            <div class="alert alert-success">
                <strong><i class="material-icons" style="vertical-align: middle;">&#xE86C;</i> {l s='Formulaire LCB-FT signé avec succès' mod='lcbftform'}</strong>
                <p class="mb-0 mt-2">
                    {l s='Signé par' mod='lcbftform'} <strong>{$lcbft_form->prenom|escape:'htmlall':'UTF-8'} {$lcbft_form->nom|escape:'htmlall':'UTF-8'}</strong>
                    {l s='le' mod='lcbftform'} {$lcbft_form->signed_at|date_format:'%d/%m/%Y à %H:%M'|escape:'htmlall':'UTF-8'}
                </p>
            </div>
            <p class="text-muted small">
                <a href="#" id="lcbft-show-form-link">{l s='Afficher/modifier le formulaire' mod='lcbftform'}</a>
            </p>
            <div id="lcbft-form-wrapper" style="display: none;">
        {else}
            {* Alerte bloquante si non signe *}
            <div class="alert alert-warning lcbft-blocking-alert">
                <strong><i class="material-icons" style="vertical-align: middle;">&#xE002;</i> {l s='Action requise' mod='lcbftform'}</strong>
                <p class="mb-0">{l s='Vous devez remplir et signer ce formulaire réglementaire pour pouvoir procéder au paiement.' mod='lcbftform'}</p>
            </div>
            <div id="lcbft-form-wrapper">
        {* Message d'introduction *}
        <div class="alert alert-info lcbft-intro">
            <p class="mb-2"><strong>{l s='Lutte contre le blanchiment et le financement du terrorisme' mod='lcbftform'}</strong></p>
            <p class="mb-0 small">
                {l s='En application de la réglementation en vigueur, nous vous remercions de bien vouloir compléter, dater, et signer ce formulaire. Les informations demandées sont strictement confidentielles et destinées à satisfaire aux obligations légales.' mod='lcbftform'}
            </p>
        </div>

        {* Formulaire *}
        <form id="lcbft-form" class="lcbft-form" data-ajax-url="{$lcbft_ajax_url|escape:'htmlall':'UTF-8'}">
            <input type="hidden" name="id_cart" value="{$lcbft_id_cart|intval}">
            <input type="hidden" name="id_customer" value="{$lcbft_id_customer|intval}">

            {* ================================================================ *}
            {* SECTION A : VOS COORDONNEES                                      *}
            {* ================================================================ *}
            <fieldset class="lcbft-section">
                <legend class="lcbft-legend">{l s='VOS COORDONNÉES' mod='lcbftform'}</legend>

                <div class="row">
                    <div class="col-md-12 form-group">
                        <label for="lcbft_entreprise">{l s='Nom de l\'entreprise' mod='lcbftform'}</label>
                        <input type="text" class="form-control" id="lcbft_entreprise" name="entreprise"
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->entreprise|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>{l s='Civilité' mod='lcbftform'} <span class="required">*</span></label>
                        <div class="lcbft-radio-group">
                            <label class="lcbft-radio">
                                <input type="radio" name="civilite" value="Mme"
                                       {if (isset($lcbft_form) && $lcbft_form && $lcbft_form->civilite == 'Mme') || (isset($lcbft_customer_data.civilite) && $lcbft_customer_data.civilite == 'Mme')}checked{/if}>
                                <span>Mme</span>
                            </label>
                            <label class="lcbft-radio">
                                <input type="radio" name="civilite" value="M"
                                       {if (isset($lcbft_form) && $lcbft_form && $lcbft_form->civilite == 'M') || (isset($lcbft_customer_data.civilite) && $lcbft_customer_data.civilite == 'M')}checked{/if}>
                                <span>M.</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="lcbft_nom">{l s='Nom' mod='lcbftform'} <span class="required">*</span></label>
                        <input type="text" class="form-control required" id="lcbft_nom" name="nom" required
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->nom|escape:'htmlall':'UTF-8'}{elseif isset($lcbft_customer_data.nom)}{$lcbft_customer_data.nom|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                    <div class="col-md-5 form-group">
                        <label for="lcbft_prenom">{l s='Prénom' mod='lcbftform'} <span class="required">*</span></label>
                        <input type="text" class="form-control required" id="lcbft_prenom" name="prenom" required
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->prenom|escape:'htmlall':'UTF-8'}{elseif isset($lcbft_customer_data.prenom)}{$lcbft_customer_data.prenom|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="lcbft_profession">{l s='Profession' mod='lcbftform'}</label>
                        <input type="text" class="form-control" id="lcbft_profession" name="profession"
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->profession|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="lcbft_identifiant_connexion">{l s='Identifiant de connexion sur l\'espace privé' mod='lcbftform'}</label>
                        <input type="text" class="form-control" id="lcbft_identifiant_connexion" name="identifiant_connexion"
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->identifiant_connexion|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 form-group">
                        <label for="lcbft_adresse">{l s='Adresse' mod='lcbftform'}</label>
                        <input type="text" class="form-control" id="lcbft_adresse" name="adresse"
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->adresse|escape:'htmlall':'UTF-8'}{elseif isset($lcbft_customer_data.adresse)}{$lcbft_customer_data.adresse|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="lcbft_code_postal">{l s='Code postal' mod='lcbftform'}</label>
                        <input type="text" class="form-control" id="lcbft_code_postal" name="code_postal"
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->code_postal|escape:'htmlall':'UTF-8'}{elseif isset($lcbft_customer_data.code_postal)}{$lcbft_customer_data.code_postal|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                    <div class="col-md-5 form-group">
                        <label for="lcbft_ville">{l s='Ville' mod='lcbftform'}</label>
                        <input type="text" class="form-control" id="lcbft_ville" name="ville"
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->ville|escape:'htmlall':'UTF-8'}{elseif isset($lcbft_customer_data.ville)}{$lcbft_customer_data.ville|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="lcbft_pays">{l s='Pays' mod='lcbftform'}</label>
                        <input type="text" class="form-control" id="lcbft_pays" name="pays"
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->pays|escape:'htmlall':'UTF-8'}{elseif isset($lcbft_customer_data.pays)}{$lcbft_customer_data.pays|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="lcbft_email">{l s='E-mail' mod='lcbftform'}</label>
                        <input type="email" class="form-control" id="lcbft_email" name="email"
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->email|escape:'htmlall':'UTF-8'}{elseif isset($lcbft_customer_data.email)}{$lcbft_customer_data.email|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="lcbft_telephone">{l s='Téléphone' mod='lcbftform'}</label>
                        <input type="tel" class="form-control" id="lcbft_telephone" name="telephone"
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->telephone|escape:'htmlall':'UTF-8'}{elseif isset($lcbft_customer_data.telephone)}{$lcbft_customer_data.telephone|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                </div>
            </fieldset>

            {* ================================================================ *}
            {* SECTION B : ATTESTATION SUR L'HONNEUR                            *}
            {* ================================================================ *}
            <fieldset class="lcbft-section">
                <legend class="lcbft-legend">
                    {l s='ATTESTATION SUR L\'HONNEUR' mod='lcbftform'}
                    <span class="lcbft-legend-sub">({l s='valable jusqu\'au 31 décembre' mod='lcbftform'} {$lcbft_current_year|escape:'htmlall':'UTF-8'})</span>
                </legend>

                {* Sous-section : Informations patrimoniales *}
                <h4 class="lcbft-subsection">{l s='INFORMATIONS PATRIMONIALES' mod='lcbftform'}</h4>

                <div class="row">
                    <div class="col-md-12 form-group">
                        <label for="lcbft_remunerations">{l s='Rémunérations brutes annuelles du Client' mod='lcbftform'}</label>
                        <input type="text" class="form-control" id="lcbft_remunerations" name="remunerations_annuelles"
                               placeholder="{l s='Montant en euros' mod='lcbftform'}"
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->remunerations_annuelles|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                </div>

                <div class="form-group">
                    <label>{l s='Estimation du patrimoine total' mod='lcbftform'}</label>
                    <div class="lcbft-patrimoine-options">
                        <label class="lcbft-radio-block">
                            <input type="radio" name="patrimoine_estimation" value="less_300k"
                                   {if isset($lcbft_form) && $lcbft_form && $lcbft_form->patrimoine_estimation == 'less_300k'}checked{/if}>
                            <span>{l s='Moins de 300 000 euros' mod='lcbftform'}</span>
                        </label>
                        <label class="lcbft-radio-block">
                            <input type="radio" name="patrimoine_estimation" value="300k_720k"
                                   {if isset($lcbft_form) && $lcbft_form && $lcbft_form->patrimoine_estimation == '300k_720k'}checked{/if}>
                            <span>{l s='De 300 000 € à 720 000 euros' mod='lcbftform'}</span>
                        </label>
                        <label class="lcbft-radio-block">
                            <input type="radio" name="patrimoine_estimation" value="720k_1500k"
                                   {if isset($lcbft_form) && $lcbft_form && $lcbft_form->patrimoine_estimation == '720k_1500k'}checked{/if}>
                            <span>{l s='De 720 000 € à 1,5 million d\'euros' mod='lcbftform'}</span>
                        </label>
                        <label class="lcbft-radio-block">
                            <input type="radio" name="patrimoine_estimation" value="more_1500k"
                                   {if isset($lcbft_form) && $lcbft_form && $lcbft_form->patrimoine_estimation == 'more_1500k'}checked{/if}>
                            <span>{l s='Plus de 1,5 million d\'euros' mod='lcbftform'}</span>
                        </label>
                    </div>
                    <div id="lcbft_patrimoine_precision_container" class="mt-2" style="display: {if isset($lcbft_form) && $lcbft_form && $lcbft_form->patrimoine_estimation == 'more_1500k'}block{else}none{/if};">
                        <input type="text" class="form-control" name="patrimoine_precision"
                               placeholder="{l s='Précisez le montant' mod='lcbftform'}"
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->patrimoine_precision|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                </div>

                <div class="form-group">
                    <label>{l s='Êtes-vous soumis(e) à l\'Impôt sur la Fortune Immobilière (IFI) ?' mod='lcbftform'}</label>
                    <div class="lcbft-radio-group">
                        <label class="lcbft-radio">
                            <input type="radio" name="soumis_ifi" value="1"
                                   {if isset($lcbft_form) && $lcbft_form && $lcbft_form->soumis_ifi == 1}checked{/if}>
                            <span>{l s='Oui' mod='lcbftform'}</span>
                        </label>
                        <label class="lcbft-radio">
                            <input type="radio" name="soumis_ifi" value="0"
                                   {if !isset($lcbft_form) || !$lcbft_form || $lcbft_form->soumis_ifi == 0}checked{/if}>
                            <span>{l s='Non' mod='lcbftform'}</span>
                        </label>
                    </div>
                </div>

                {* Sous-section : Origine des fonds *}
                <h4 class="lcbft-subsection">{l s='ORIGINE DES FONDS' mod='lcbftform'}</h4>

                <div class="form-group">
                    <label>{l s='Cochez les origines des fonds correspondantes :' mod='lcbftform'}</label>
                    <div class="lcbft-checkbox-grid">
                        <label class="lcbft-checkbox">
                            <input type="checkbox" name="origine_fonds[]" value="vente_immo"
                                   {if isset($lcbft_form) && $lcbft_form && in_array('vente_immo', $lcbft_form->getOrigineFonds())}checked{/if}>
                            <span>{l s='Vente Immobilière' mod='lcbftform'}</span>
                        </label>
                        <label class="lcbft-checkbox">
                            <input type="checkbox" name="origine_fonds[]" value="donation"
                                   {if isset($lcbft_form) && $lcbft_form && in_array('donation', $lcbft_form->getOrigineFonds())}checked{/if}>
                            <span>{l s='Donation' mod='lcbftform'}</span>
                        </label>
                        <label class="lcbft-checkbox">
                            <input type="checkbox" name="origine_fonds[]" value="heritage"
                                   {if isset($lcbft_form) && $lcbft_form && in_array('heritage', $lcbft_form->getOrigineFonds())}checked{/if}>
                            <span>{l s='Héritage' mod='lcbftform'}</span>
                        </label>
                        <label class="lcbft-checkbox">
                            <input type="checkbox" name="origine_fonds[]" value="revenus"
                                   {if isset($lcbft_form) && $lcbft_form && in_array('revenus', $lcbft_form->getOrigineFonds())}checked{/if}>
                            <span>{l s='Revenus ou Dividendes' mod='lcbftform'}</span>
                        </label>
                        <label class="lcbft-checkbox">
                            <input type="checkbox" name="origine_fonds[]" value="jeux"
                                   {if isset($lcbft_form) && $lcbft_form && in_array('jeux', $lcbft_form->getOrigineFonds())}checked{/if}>
                            <span>{l s='Gains aux jeux' mod='lcbftform'}</span>
                        </label>
                        <label class="lcbft-checkbox">
                            <input type="checkbox" name="origine_fonds[]" value="cession"
                                   {if isset($lcbft_form) && $lcbft_form && in_array('cession', $lcbft_form->getOrigineFonds())}checked{/if}>
                            <span>{l s='Cession d\'actifs' mod='lcbftform'}</span>
                        </label>
                        <label class="lcbft-checkbox">
                            <input type="checkbox" name="origine_fonds[]" value="epargne"
                                   {if isset($lcbft_form) && $lcbft_form && in_array('epargne', $lcbft_form->getOrigineFonds())}checked{/if}>
                            <span>{l s='Épargne personnelle' mod='lcbftform'}</span>
                        </label>
                        <label class="lcbft-checkbox">
                            <input type="checkbox" name="origine_fonds[]" value="autre"
                                   {if isset($lcbft_form) && $lcbft_form && in_array('autre', $lcbft_form->getOrigineFonds())}checked{/if}>
                            <span>{l s='Autre' mod='lcbftform'}</span>
                        </label>
                    </div>
                </div>

                {* Champs de precision pour origines *}
                <div id="lcbft_origine_cession_detail" class="form-group lcbft-detail-field" style="display: none;">
                    <label>{l s='Cession d\'actifs - Précisez :' mod='lcbftform'}</label>
                    <input type="text" class="form-control" name="origine_cession_detail"
                           value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->origine_cession_detail|escape:'htmlall':'UTF-8'}{/if}">
                </div>

                <div id="lcbft_origine_epargne_detail" class="form-group lcbft-detail-field" style="display: none;">
                    <label>{l s='Épargne personnelle - Précisez la date et l\'origine de l\'investissement initial :' mod='lcbftform'}</label>
                    <input type="text" class="form-control" name="origine_epargne_detail"
                           value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->origine_epargne_detail|escape:'htmlall':'UTF-8'}{/if}">
                </div>

                <div id="lcbft_origine_autre_detail" class="form-group lcbft-detail-field" style="display: none;">
                    <label>{l s='Autre origine - Précisez :' mod='lcbftform'}</label>
                    <input type="text" class="form-control" name="origine_autre_detail"
                           value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->origine_autre_detail|escape:'htmlall':'UTF-8'}{/if}">
                </div>

                <div class="form-group">
                    <label for="lcbft_justificatif_origine">{l s='Nature du justificatif d\'origine des fonds fourni pour tout versement supérieur à 15 000 € (montant total unique ou cumulé sur 12 mois civils) :' mod='lcbftform'}</label>
                    <textarea class="form-control" id="lcbft_justificatif_origine" name="justificatif_origine_fonds" rows="3">{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->justificatif_origine_fonds|escape:'htmlall':'UTF-8'}{/if}</textarea>
                </div>
            </fieldset>

            {* ================================================================ *}
            {* SECTION C : COMMENTAIRES                                         *}
            {* ================================================================ *}
            <fieldset class="lcbft-section">
                <legend class="lcbft-legend">{l s='COMMENTAIRES' mod='lcbftform'}</legend>

                <p class="small text-muted mb-2">{l s='Informations complémentaires sur les versements :' mod='lcbftform'}</p>

                <div class="table-responsive">
                    <table class="table table-bordered lcbft-comments-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">{l s='Date' mod='lcbftform'}</th>
                                <th style="width: 25%;">{l s='Montant' mod='lcbftform'}</th>
                                <th style="width: 50%;">{l s='Origine' mod='lcbftform'}</th>
                            </tr>
                        </thead>
                        <tbody id="lcbft-comments-body">
                            {assign var="commentaires" value=[]}
                            {if isset($lcbft_form) && $lcbft_form}
                                {assign var="commentaires" value=$lcbft_form->getCommentaires()}
                            {/if}
                            {for $i=0 to 4}
                            <tr>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                           name="commentaires[{$i}][date]" placeholder="JJ/MM/AAAA"
                                           value="{if isset($commentaires[$i].date)}{$commentaires[$i].date|escape:'htmlall':'UTF-8'}{/if}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                           name="commentaires[{$i}][montant]" placeholder="€"
                                           value="{if isset($commentaires[$i].montant)}{$commentaires[$i].montant|escape:'htmlall':'UTF-8'}{/if}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                           name="commentaires[{$i}][origine]"
                                           value="{if isset($commentaires[$i].origine)}{$commentaires[$i].origine|escape:'htmlall':'UTF-8'}{/if}">
                                </td>
                            </tr>
                            {/for}
                        </tbody>
                    </table>
                </div>
            </fieldset>

            {* ================================================================ *}
            {* SECTION D : JUSTIFICATIFS FOURNIS                                *}
            {* ================================================================ *}
            <fieldset class="lcbft-section">
                <legend class="lcbft-legend">{l s='JUSTIFICATIFS FOURNIS' mod='lcbftform'}</legend>

                <div class="row">
                    <div class="col-md-6">
                        <h5 class="lcbft-subsection-small">{l s='Justificatif d\'identité (en cours de validité)' mod='lcbftform'}</h5>
                        <div class="lcbft-checkbox-list">
                            <label class="lcbft-checkbox">
                                <input type="checkbox" name="justificatifs[]" value="cni"
                                       {if isset($lcbft_form) && $lcbft_form && in_array('cni', $lcbft_form->getJustificatifs())}checked{/if}>
                                <span>{l s='Carte nationale d\'identité (recto-verso)' mod='lcbftform'}</span>
                            </label>
                            <label class="lcbft-checkbox">
                                <input type="checkbox" name="justificatifs[]" value="passeport"
                                       {if isset($lcbft_form) && $lcbft_form && in_array('passeport', $lcbft_form->getJustificatifs())}checked{/if}>
                                <span>{l s='Passeport (pages avec informations, photo, signature)' mod='lcbftform'}</span>
                            </label>
                            <label class="lcbft-checkbox">
                                <input type="checkbox" name="justificatifs[]" value="titre_sejour"
                                       {if isset($lcbft_form) && $lcbft_form && in_array('titre_sejour', $lcbft_form->getJustificatifs())}checked{/if}>
                                <span>{l s='Titre de séjour (recto-verso)' mod='lcbftform'}</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5 class="lcbft-subsection-small">{l s='Justificatif financier' mod='lcbftform'}</h5>
                        <div class="lcbft-checkbox-list">
                            <label class="lcbft-checkbox">
                                <input type="checkbox" name="justificatifs[]" value="acte_notarie"
                                       {if isset($lcbft_form) && $lcbft_form && in_array('acte_notarie', $lcbft_form->getJustificatifs())}checked{/if}>
                                <span>{l s='Acte notarié' mod='lcbftform'}</span>
                            </label>
                            <label class="lcbft-checkbox">
                                <input type="checkbox" name="justificatifs[]" value="releve_compte"
                                       {if isset($lcbft_form) && $lcbft_form && in_array('releve_compte', $lcbft_form->getJustificatifs())}checked{/if}>
                                <span>{l s='Relevé de compte' mod='lcbftform'}</span>
                            </label>
                            <label class="lcbft-checkbox">
                                <input type="checkbox" name="justificatifs[]" value="avis_imposition"
                                       {if isset($lcbft_form) && $lcbft_form && in_array('avis_imposition', $lcbft_form->getJustificatifs())}checked{/if}>
                                <span>{l s='Avis d\'imposition' mod='lcbftform'}</span>
                            </label>
                            <label class="lcbft-checkbox">
                                <input type="checkbox" name="justificatifs[]" value="justif_autre"
                                       {if isset($lcbft_form) && $lcbft_form && in_array('justif_autre', $lcbft_form->getJustificatifs())}checked{/if}>
                                <span>{l s='Autre' mod='lcbftform'}</span>
                            </label>
                        </div>
                        <div id="lcbft_justificatif_autre_detail" class="mt-2" style="display: none;">
                            <input type="text" class="form-control form-control-sm" name="justificatif_autre_detail"
                                   placeholder="{l s='Précisez' mod='lcbftform'}"
                                   value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->justificatif_autre_detail|escape:'htmlall':'UTF-8'}{/if}">
                        </div>
                    </div>
                </div>
            </fieldset>

            {* ================================================================ *}
            {* SECTION E : SIGNATURE                                            *}
            {* ================================================================ *}
            <fieldset class="lcbft-section lcbft-signature-section">
                <legend class="lcbft-legend">{l s='DATE ET SIGNATURE' mod='lcbftform'}</legend>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="lcbft_date_signature">{l s='Fait le' mod='lcbftform'}</label>
                        <input type="text" class="form-control" id="lcbft_date_signature" name="date_signature"
                               placeholder="JJ/MM/AAAA" value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->date_signature|escape:'htmlall':'UTF-8'}{else}{$smarty.now|date_format:'%d/%m/%Y'}{/if}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="lcbft_lieu_signature">{l s='À' mod='lcbftform'}</label>
                        <input type="text" class="form-control" id="lcbft_lieu_signature" name="lieu_signature"
                               placeholder="{l s='Ville' mod='lcbftform'}"
                               value="{if isset($lcbft_form) && $lcbft_form}{$lcbft_form->lieu_signature|escape:'htmlall':'UTF-8'}{elseif isset($lcbft_customer_data.ville)}{$lcbft_customer_data.ville|escape:'htmlall':'UTF-8'}{/if}">
                    </div>
                </div>

                {* Case de reconnaissance obligatoire *}
                <div class="lcbft-acknowledgment">
                    <label class="lcbft-checkbox lcbft-checkbox-required">
                        <input type="checkbox" id="lcbft_acknowledged" name="acknowledged" value="1"
                               {if isset($lcbft_form) && $lcbft_form && $lcbft_form->acknowledged}checked{/if}>
                        <span class="lcbft-acknowledgment-text">
                            <strong>{l s='Je reconnais avoir lu et accepté les termes du présent formulaire' mod='lcbftform'}</strong> <span class="required">*</span>
                        </span>
                    </label>
                </div>

                {* Zone de signature *}
                <div class="lcbft-signature-box">
                    <div class="lcbft-signature-label">{l s='Signature' mod='lcbftform'} <small>({l s='précédée de la mention « lu et approuvé »' mod='lcbftform'})</small></div>
                    <div id="lcbft-signature-display" class="lcbft-signature-content">
                        {if isset($lcbft_form) && $lcbft_form && $lcbft_form->signature_name}
                            <span class="lcbft-signature-text">Lu et approuvé</span>
                            <span class="lcbft-signature-name">{$lcbft_form->signature_name|escape:'htmlall':'UTF-8'}</span>
                            <span class="lcbft-signature-date">{$lcbft_form->getFormattedSignature()|escape:'htmlall':'UTF-8'}</span>
                        {else}
                            <span class="lcbft-signature-placeholder">{l s='Cochez la case ci-dessus pour signer électroniquement' mod='lcbftform'}</span>
                        {/if}
                    </div>
                </div>
            </fieldset>

            {* Bouton de soumission *}
            <div class="lcbft-submit-section">
                <div id="lcbft-error-message" class="alert alert-danger" style="display: none;"></div>
                <div id="lcbft-success-message" class="alert alert-success" style="display: none;"></div>

                <button type="submit" id="lcbft-submit-btn" class="btn btn-primary btn-lg">
                    <i class="material-icons">&#xE876;</i>
                    {l s='Valider et signer le formulaire LCB-FT' mod='lcbftform'}
                </button>

                <p class="lcbft-mandatory-notice">
                    <span class="required">*</span> {l s='Champs obligatoires' mod='lcbftform'}
                </p>
            </div>
        </form>
        </div>{* Fin lcbft-form-wrapper *}
        {/if}{* Fin if lcbft_form_valid *}
    </div>
</div>
