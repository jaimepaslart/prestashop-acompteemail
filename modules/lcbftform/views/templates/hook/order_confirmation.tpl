{**
 * Template pour la page de confirmation de commande
 *
 * Affiche le lien de telechargement du PDF apres validation de commande
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 *}

<div class="lcbft-confirmation-block alert alert-info">
    <p class="mb-2">
        <i class="material-icons">&#xE876;</i>
        <strong>{l s='Votre formulaire LCB-FT a été enregistré avec succès.' mod='lcbftform'}</strong>
    </p>
    <p class="mb-0">
        <a href="{$lcbft_download_url|escape:'htmlall':'UTF-8'}" class="btn btn-outline-primary btn-sm" target="_blank">
            <i class="material-icons">&#xE2C4;</i>
            {l s='Télécharger le formulaire LCB-FT (PDF)' mod='lcbftform'}
        </a>
    </p>
</div>
