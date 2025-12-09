<?php
/**
 * Classe de generation PDF pour les formulaires LCB-FT
 *
 * Utilise TCPDF (inclus dans PrestaShop) pour generer les PDF.
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Charger TCPDF de PrestaShop (emplacement varie selon la version)
if (file_exists(_PS_ROOT_DIR_ . '/vendor/tecnickcom/tcpdf/tcpdf.php')) {
    // PrestaShop 1.7.x - TCPDF via Composer
    require_once _PS_ROOT_DIR_ . '/vendor/tecnickcom/tcpdf/tcpdf.php';
} elseif (file_exists(_PS_TOOL_DIR_ . 'tcpdf/tcpdf.php')) {
    // PrestaShop 1.6.x / anciennes versions 1.7
    require_once _PS_TOOL_DIR_ . 'tcpdf/tcpdf.php';
} else {
    throw new Exception('TCPDF library not found. Please check your PrestaShop installation.');
}

/**
 * Generateur de PDF pour les formulaires LCB-FT
 */
class LcbftPdfGenerator
{
    /**
     * @var Module Instance du module
     */
    protected $module;

    /**
     * @var TCPDF Instance TCPDF
     */
    protected $pdf;

    /**
     * Constructeur
     *
     * @param Module $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Genere et telecharge le PDF
     *
     * @param LcbftFormModel $form
     * @param Order|null $order
     */
    public function generateAndDownload($form, $order = null)
    {
        $this->initPdf();
        $this->generateContent($form, $order);

        // Nom du fichier
        $filename = 'LCBFT_';
        if ($order) {
            $filename .= $order->reference . '_';
        }
        $filename .= $form->nom . '_' . $form->prenom . '.pdf';
        $filename = $this->sanitizeFilename($filename);

        // Telecharger
        $this->pdf->Output($filename, 'D');
    }

    /**
     * Genere et retourne le contenu PDF en string
     *
     * @param LcbftFormModel $form
     * @param Order|null $order
     * @return string
     */
    public function generateString($form, $order = null)
    {
        $this->initPdf();
        $this->generateContent($form, $order);

        return $this->pdf->Output('', 'S');
    }

    /**
     * Initialise l'instance TCPDF
     */
    protected function initPdf()
    {
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Informations document
        $this->pdf->SetCreator('PrestaShop - Module LCB-FT');
        $this->pdf->SetAuthor('PrestaShop');
        $this->pdf->SetTitle('Formulaire LCB-FT');
        $this->pdf->SetSubject('Lutte contre le blanchiment et financement du terrorisme');

        // Supprimer header/footer par defaut
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // Marges
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(true, 15);

        // Police par defaut
        $this->pdf->SetFont('helvetica', '', 10);
    }

    /**
     * Genere le contenu du PDF
     *
     * @param LcbftFormModel $form
     * @param Order|null $order
     */
    protected function generateContent($form, $order = null)
    {
        $this->pdf->AddPage();

        // En-tete
        $this->renderHeader($form, $order);

        // Section Coordonnees
        $this->renderCoordonnees($form);

        // Section Attestation
        $this->renderAttestation($form);

        // Section Origine des fonds
        $this->renderOrigineFonds($form);

        // Section Commentaires
        $this->renderCommentaires($form);

        // Section Justificatifs
        $this->renderJustificatifs($form);

        // Section Signature
        $this->renderSignature($form);
    }

    /**
     * Rendu de l'en-tete
     */
    protected function renderHeader($form, $order)
    {
        // Titre principal
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->SetTextColor(0, 102, 153);
        $this->pdf->Cell(0, 10, 'FORMULAIRE LCB-FT', 0, 1, 'C');

        $this->pdf->SetFont('helvetica', 'I', 9);
        $this->pdf->SetTextColor(80, 80, 80);
        $this->pdf->Cell(0, 5, 'Article L 561-16 du Code monétaire et financier', 0, 1, 'C');

        // Reference commande si existante
        if ($order) {
            $this->pdf->Ln(2);
            $this->pdf->SetFont('helvetica', 'B', 11);
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->Cell(0, 7, 'Commande n° ' . $order->reference, 0, 1, 'C');
        }

        $this->pdf->Ln(4);

        // Encadre d'introduction
        $this->pdf->SetFillColor(245, 245, 245);
        $this->pdf->SetDrawColor(200, 200, 200);
        $startY = $this->pdf->GetY();

        // Texte d'introduction dans un cadre
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColor(50, 50, 50);

        $intro = "En application de la réglementation en vigueur relative à la lutte contre le blanchiment de capitaux et le financement du terrorisme, nous vous remercions de bien vouloir compléter ce formulaire.\n\nLes informations demandées sont strictement confidentielles et destinées à satisfaire aux obligations légales.";

        $this->pdf->MultiCell(0, 5, $intro, 1, 'J', true);

        $this->pdf->Ln(6);
        $this->pdf->SetTextColor(0, 0, 0);
    }

    /**
     * Rendu de la section Coordonnees
     */
    protected function renderCoordonnees($form)
    {
        $this->renderSectionTitle('VOS COORDONNÉES');

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColor(0, 0, 0);

        // Tableau des coordonnees - affiche tous les champs
        $data = array(
            array('Nom de l\'entreprise', $form->entreprise),
            array('Civilité', $form->civilite),
            array('Nom', $form->nom),
            array('Prénom', $form->prenom),
            array('Profession', $form->profession),
            array('Identifiant de connexion', $form->identifiant_connexion),
            array('Adresse', $form->adresse),
            array('Code postal', $form->code_postal),
            array('Ville', $form->ville),
            array('Pays', $form->pays),
            array('E-mail', $form->email),
            array('Téléphone', $form->telephone),
        );

        $this->renderDataTableAll($data);
        $this->pdf->Ln(5);
    }

    /**
     * Rendu de la section Attestation
     */
    protected function renderAttestation($form)
    {
        $year = date('Y');
        $this->renderSectionTitle('ATTESTATION SUR L\'HONNEUR (valable jusqu\'au 31 décembre ' . $year . ')');

        // Sous-section : Informations patrimoniales
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetTextColor(0, 102, 153);
        $this->pdf->Cell(0, 6, 'INFORMATIONS PATRIMONIALES', 0, 1);

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColor(0, 0, 0);

        // Afficher tous les champs meme vides
        $patrimoineLabel = $form->getPatrimoineLabel();
        $ifiLabel = ($form->soumis_ifi == 1) ? 'Oui' : (($form->soumis_ifi == 0) ? 'Non' : '-');

        $data = array(
            array('Rémunérations brutes annuelles', $form->remunerations_annuelles),
            array('Estimation du patrimoine total', $patrimoineLabel),
            array('Soumis à l\'IFI', $ifiLabel),
        );

        $this->renderDataTableAll($data);

        $this->pdf->Ln(3);
    }

    /**
     * Rendu de la section Origine des fonds
     * Affiche TOUTES les options avec cases cochees ou non
     */
    protected function renderOrigineFonds($form)
    {
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetTextColor(0, 102, 153);
        $this->pdf->Cell(0, 6, 'ORIGINE DES FONDS', 0, 1);

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColor(0, 0, 0);

        $origines = $form->getOrigineFonds();
        $allOptions = array(
            'vente_immo' => 'Vente Immobilière',
            'donation' => 'Donation',
            'heritage' => 'Héritage',
            'revenus' => 'Revenus ou Dividendes',
            'jeux' => 'Gains aux jeux',
            'cession' => 'Cession d\'actifs',
            'epargne' => 'Épargne personnelle',
            'autre' => 'Autre',
        );

        // Afficher TOUTES les options avec case cochee ou non
        foreach ($allOptions as $key => $label) {
            $checked = in_array($key, $origines) ? '☑' : '☐';
            $this->pdf->Cell(0, 5, $checked . ' ' . $label, 0, 1);
        }

        $this->pdf->Ln(2);

        // Champs de precision (toujours affichés)
        $this->renderFormField('Cession d\'actifs (précision)', $form->origine_cession_detail);
        $this->renderFormField('Épargne personnelle (précision)', $form->origine_epargne_detail);
        $this->renderFormField('Autre origine (précision)', $form->origine_autre_detail);

        // Justificatif origine fonds
        $this->pdf->Ln(2);
        $this->pdf->SetFont('helvetica', 'I', 8);
        $this->pdf->SetTextColor(80, 80, 80);
        $this->pdf->Cell(0, 4, 'Nature du justificatif (pour versements > 15 000 €) :', 0, 1);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->renderFormField('', $form->justificatif_origine_fonds, 180);

        $this->pdf->Ln(3);
    }

    /**
     * Rendu de la section Commentaires
     */
    protected function renderCommentaires($form)
    {
        $commentaires = $form->getCommentaires();

        if (empty($commentaires)) {
            return;
        }

        $this->renderSectionTitle('COMMENTAIRES');

        $this->pdf->SetFont('helvetica', '', 8);

        // En-tete du tableau
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->Cell(35, 6, 'Date', 1, 0, 'C', true);
        $this->pdf->Cell(35, 6, 'Montant', 1, 0, 'C', true);
        $this->pdf->Cell(110, 6, 'Origine', 1, 1, 'C', true);

        // Lignes
        $this->pdf->SetFillColor(255, 255, 255);
        foreach ($commentaires as $ligne) {
            $this->pdf->Cell(35, 5, isset($ligne['date']) ? $ligne['date'] : '', 1, 0, 'C');
            $this->pdf->Cell(35, 5, isset($ligne['montant']) ? $ligne['montant'] : '', 1, 0, 'C');
            $this->pdf->Cell(110, 5, isset($ligne['origine']) ? $ligne['origine'] : '', 1, 1, 'L');
        }

        $this->pdf->Ln(5);
    }

    /**
     * Rendu de la section Justificatifs
     * Affiche TOUTES les options avec cases cochees ou non
     */
    protected function renderJustificatifs($form)
    {
        $justificatifs = $form->getJustificatifs();

        $this->renderSectionTitle('JUSTIFICATIFS FOURNIS');

        $this->pdf->SetFont('helvetica', '', 9);

        $allOptions = array(
            'cni' => 'Carte nationale d\'identité (recto-verso)',
            'passeport' => 'Passeport (pages avec informations, photo, signature)',
            'titre_sejour' => 'Titre de séjour (recto-verso)',
            'acte_notarie' => 'Acte notarié',
            'releve_compte' => 'Relevé de compte',
            'avis_imposition' => 'Avis d\'imposition',
            'justif_autre' => 'Autre',
        );

        // Afficher TOUTES les options avec case cochee ou non
        foreach ($allOptions as $key => $label) {
            $checked = in_array($key, $justificatifs) ? '☑' : '☐';
            $this->pdf->Cell(0, 5, $checked . ' ' . $label, 0, 1);
        }

        // Champ precision pour "Autre"
        $this->pdf->Ln(2);
        $this->renderFormField('Autre (précision)', $form->justificatif_autre_detail);

        $this->pdf->Ln(5);
    }

    /**
     * Rendu de la section Signature
     */
    protected function renderSignature($form)
    {
        $this->renderSectionTitle('DATE ET SIGNATURE');

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetDrawColor(180, 180, 180);

        // Lieu et date - style formulaire avec lignes
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(25, 6, 'Fait le : ', 0, 0);
        $this->pdf->SetFont('helvetica', '', 9);
        $dateVal = !empty($form->date_signature) ? $form->date_signature : '';
        $this->pdf->Cell(50, 6, $dateVal, 'B', 0);

        $this->pdf->Cell(10, 6, '', 0, 0); // Espace

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(10, 6, 'À : ', 0, 0);
        $this->pdf->SetFont('helvetica', '', 9);
        $lieuVal = !empty($form->lieu_signature) ? $form->lieu_signature : '';
        $this->pdf->Cell(80, 6, $lieuVal, 'B', 1);

        $this->pdf->Ln(4);

        // Case de reconnaissance
        $this->pdf->SetFont('helvetica', 'B', 9);
        if ($form->acknowledged) {
            $this->pdf->Cell(0, 6, '☑ Je reconnais avoir lu et accepté les termes du présent formulaire', 0, 1);
        } else {
            $this->pdf->Cell(0, 6, '☐ Je reconnais avoir lu et accepté les termes du présent formulaire', 0, 1);
        }

        $this->pdf->Ln(5);

        // Zone de signature
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->Cell(0, 5, 'Signature (précédée de la mention « lu et approuvé ») :', 0, 1);

        // Cadre signature
        $this->pdf->SetDrawColor(0, 0, 0);
        $startY = $this->pdf->GetY();
        $this->pdf->Rect(15, $startY, 180, 30);

        if ($form->acknowledged && !empty($form->signature_name)) {
            // Mention "Lu et approuve"
            $this->pdf->SetY($startY + 5);
            $this->pdf->SetFont('helvetica', 'I', 10);
            $this->pdf->Cell(0, 5, 'Lu et approuvé', 0, 1, 'C');

            // Nom signature (style manuscrit)
            $this->pdf->SetFont('times', 'BI', 16);
            $this->pdf->SetTextColor(0, 0, 128);
            $this->pdf->Cell(0, 8, $form->signature_name, 0, 1, 'C');

            // Horodatage
            $this->pdf->SetFont('helvetica', '', 8);
            $this->pdf->SetTextColor(100, 100, 100);
            $this->pdf->Cell(0, 5, $form->getFormattedSignature(), 0, 1, 'C');
        }

        // Reset position
        $this->pdf->SetY($startY + 35);
        $this->pdf->SetTextColor(0, 0, 0);
    }

    /**
     * Rendu d'un titre de section
     *
     * @param string $title
     */
    protected function renderSectionTitle($title)
    {
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->SetFillColor(0, 102, 153);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->Cell(0, 7, ' ' . $title, 0, 1, 'L', true);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Ln(2);
    }

    /**
     * Rendu d'un tableau de donnees simple (saute les champs vides)
     *
     * @param array $data
     */
    protected function renderDataTable($data)
    {
        $this->pdf->SetFont('helvetica', '', 9);

        foreach ($data as $row) {
            if (!empty($row[1])) {
                $this->pdf->SetFont('helvetica', 'B', 9);
                $this->pdf->Cell(60, 5, $row[0] . ' :', 0, 0);
                $this->pdf->SetFont('helvetica', '', 9);
                $this->pdf->Cell(0, 5, $row[1], 0, 1);
            }
        }
    }

    /**
     * Rendu d'un tableau de donnees - affiche TOUS les champs meme vides
     * Style formulaire papier avec ligne de saisie
     *
     * @param array $data
     */
    protected function renderDataTableAll($data)
    {
        $this->pdf->SetFont('helvetica', '', 9);

        foreach ($data as $row) {
            $this->pdf->SetFont('helvetica', 'B', 9);
            $this->pdf->Cell(60, 6, $row[0] . ' :', 0, 0);
            $this->pdf->SetFont('helvetica', '', 9);

            // Valeur ou ligne vide pour saisie manuelle
            $value = !empty($row[1]) ? $row[1] : '';

            // Dessiner une ligne de saisie style formulaire
            $this->pdf->SetDrawColor(180, 180, 180);
            $this->pdf->Cell(115, 6, $value, 'B', 1);
        }
    }

    /**
     * Rendu d'un champ de formulaire avec ligne de saisie
     *
     * @param string $label
     * @param string $value
     * @param int $width Largeur du champ (defaut: 115)
     */
    protected function renderFormField($label, $value, $width = 115)
    {
        $this->pdf->SetFont('helvetica', '', 9);

        if (!empty($label)) {
            $this->pdf->SetFont('helvetica', 'B', 8);
            $this->pdf->Cell(60, 5, $label . ' :', 0, 0);
            $this->pdf->SetFont('helvetica', '', 9);
        }

        // Valeur ou vide
        $displayValue = !empty($value) ? $value : '';

        // Ligne de saisie
        $this->pdf->SetDrawColor(180, 180, 180);
        $cellWidth = !empty($label) ? $width : $width + 60;
        $this->pdf->Cell($cellWidth, 6, $displayValue, 'B', 1);
    }

    /**
     * Sanitize le nom de fichier
     *
     * @param string $filename
     * @return string
     */
    protected function sanitizeFilename($filename)
    {
        // Remplacer les caracteres speciaux
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        return $filename;
    }
}
