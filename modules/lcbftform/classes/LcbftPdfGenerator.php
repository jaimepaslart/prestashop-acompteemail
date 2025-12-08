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

// Charger TCPDF de PrestaShop
require_once _PS_TOOL_DIR_ . 'tcpdf/tcpdf.php';

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
        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->SetTextColor(0, 102, 153);
        $this->pdf->Cell(0, 10, 'FORMULAIRE LCB-FT', 0, 1, 'C');

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->Cell(0, 5, 'Article L 561-16 du Code monétaire et financier', 0, 1, 'C');

        // Reference commande si existante
        if ($order) {
            $this->pdf->SetFont('helvetica', 'B', 10);
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->Cell(0, 8, 'Commande : ' . $order->reference, 0, 1, 'C');
        }

        $this->pdf->Ln(3);

        // Texte d'introduction
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetTextColor(60, 60, 60);
        $intro = "En application de la réglementation en vigueur relative à la lutte contre le blanchiment de capitaux et le financement du terrorisme, nous vous remercions de bien vouloir compléter ce formulaire. Les informations demandées sont strictement confidentielles et destinées à satisfaire aux obligations légales.";
        $this->pdf->MultiCell(0, 4, $intro, 0, 'J');

        $this->pdf->Ln(5);
    }

    /**
     * Rendu de la section Coordonnees
     */
    protected function renderCoordonnees($form)
    {
        $this->renderSectionTitle('VOS COORDONNÉES');

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColor(0, 0, 0);

        // Tableau des coordonnees
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

        $this->renderDataTable($data);
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

        $data = array(
            array('Rémunérations brutes annuelles', $form->remunerations_annuelles),
            array('Estimation du patrimoine total', $form->getPatrimoineLabel()),
            array('Soumis à l\'IFI', $form->soumis_ifi ? 'Oui' : 'Non'),
        );

        $this->renderDataTable($data);
        $this->pdf->Ln(3);
    }

    /**
     * Rendu de la section Origine des fonds
     */
    protected function renderOrigineFonds($form)
    {
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetTextColor(0, 102, 153);
        $this->pdf->Cell(0, 6, 'ORIGINE DES FONDS', 0, 1);

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColor(0, 0, 0);

        $origines = $form->getOrigineFonds();
        $labels = array(
            'vente_immo' => 'Vente Immobilière',
            'donation' => 'Donation',
            'heritage' => 'Héritage',
            'revenus' => 'Revenus ou Dividendes',
            'jeux' => 'Gains aux jeux',
            'cession' => 'Cession d\'actifs',
            'epargne' => 'Épargne personnelle',
            'autre' => 'Autre',
        );

        // Afficher les origines cochees
        $origineText = '';
        foreach ($origines as $origine) {
            if (isset($labels[$origine])) {
                $origineText .= '• ' . $labels[$origine] . "\n";
            }
        }

        if (!empty($origineText)) {
            $this->pdf->MultiCell(0, 4, $origineText, 0, 'L');
        }

        // Details
        if (!empty($form->origine_cession_detail)) {
            $this->pdf->Cell(0, 5, '   Cession d\'actifs (précision) : ' . $form->origine_cession_detail, 0, 1);
        }
        if (!empty($form->origine_epargne_detail)) {
            $this->pdf->Cell(0, 5, '   Épargne personnelle (précision) : ' . $form->origine_epargne_detail, 0, 1);
        }
        if (!empty($form->origine_autre_detail)) {
            $this->pdf->Cell(0, 5, '   Autre origine (précision) : ' . $form->origine_autre_detail, 0, 1);
        }

        // Justificatif origine fonds
        if (!empty($form->justificatif_origine_fonds)) {
            $this->pdf->Ln(2);
            $this->pdf->SetFont('helvetica', 'I', 8);
            $this->pdf->MultiCell(0, 4, 'Nature du justificatif (versements > 15 000 €) : ' . $form->justificatif_origine_fonds, 0, 'L');
        }

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
     */
    protected function renderJustificatifs($form)
    {
        $justificatifs = $form->getJustificatifs();

        if (empty($justificatifs)) {
            return;
        }

        $this->renderSectionTitle('JUSTIFICATIFS FOURNIS');

        $this->pdf->SetFont('helvetica', '', 9);

        $labels = array(
            'cni' => 'Carte nationale d\'identité (recto-verso)',
            'passeport' => 'Passeport (pages avec informations, photo, signature)',
            'titre_sejour' => 'Titre de séjour (recto-verso)',
            'acte_notarie' => 'Acte notarié',
            'releve_compte' => 'Relevé de compte',
            'avis_imposition' => 'Avis d\'imposition',
            'justif_autre' => 'Autre',
        );

        $text = '';
        foreach ($justificatifs as $justif) {
            if (isset($labels[$justif])) {
                $text .= '☑ ' . $labels[$justif] . "\n";
            }
        }

        if (!empty($text)) {
            $this->pdf->MultiCell(0, 5, $text, 0, 'L');
        }

        if (!empty($form->justificatif_autre_detail)) {
            $this->pdf->Cell(0, 5, '   Autre (précision) : ' . $form->justificatif_autre_detail, 0, 1);
        }

        $this->pdf->Ln(5);
    }

    /**
     * Rendu de la section Signature
     */
    protected function renderSignature($form)
    {
        $this->renderSectionTitle('DATE ET SIGNATURE');

        $this->pdf->SetFont('helvetica', '', 9);

        // Lieu et date
        $this->pdf->Cell(40, 6, 'Fait le : ', 0, 0);
        $this->pdf->Cell(50, 6, $form->date_signature, 0, 0);
        $this->pdf->Cell(20, 6, 'À : ', 0, 0);
        $this->pdf->Cell(70, 6, $form->lieu_signature, 0, 1);

        $this->pdf->Ln(3);

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
     * Rendu d'un tableau de donnees simple
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
