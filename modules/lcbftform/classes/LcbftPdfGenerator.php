<?php
/**
 * Classe de generation PDF pour les formulaires LCB-FT
 *
 * Reproduit le format exact du formulaire papier OR INVESTISSEMENT
 *
 * @author    Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Charger TCPDF de PrestaShop
if (file_exists(_PS_ROOT_DIR_ . '/vendor/tecnickcom/tcpdf/tcpdf.php')) {
    require_once _PS_ROOT_DIR_ . '/vendor/tecnickcom/tcpdf/tcpdf.php';
} elseif (file_exists(_PS_TOOL_DIR_ . 'tcpdf/tcpdf.php')) {
    require_once _PS_TOOL_DIR_ . 'tcpdf/tcpdf.php';
} else {
    throw new Exception('TCPDF library not found.');
}

class LcbftPdfGenerator
{
    protected $module;
    protected $pdf;

    // Couleur verte pour les titres de section
    protected $greenColor = array(0, 128, 0);

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function generateAndDownload($form, $order = null)
    {
        $this->initPdf();
        $this->generateContent($form, $order);

        $filename = 'LCBFT_';
        if ($order) {
            $filename .= $order->reference . '_';
        }
        $filename .= $form->nom . '_' . $form->prenom . '.pdf';
        $filename = $this->sanitizeFilename($filename);

        $this->pdf->Output($filename, 'D');
    }

    public function generateString($form, $order = null)
    {
        $this->initPdf();
        $this->generateContent($form, $order);
        return $this->pdf->Output('', 'S');
    }

    protected function initPdf()
    {
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $this->pdf->SetCreator('PrestaShop - Module LCB-FT');
        $this->pdf->SetAuthor('PrestaShop');
        $this->pdf->SetTitle('Formulaire LCB-FT');

        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(true, 15);

        // Utiliser dejavusans pour le support Unicode des cases a cocher
        $this->pdf->SetFont('dejavusans', '', 10);
    }

    protected function generateContent($form, $order = null)
    {
        $this->pdf->AddPage();

        // En-tete
        $this->renderHeader($order);

        // Coordonnees
        $this->renderCoordonnees($form);

        // Attestation sur l'honneur
        $this->renderAttestation($form);

        // Origine des fonds
        $this->renderOrigineFonds($form);

        // Page 2
        $this->pdf->AddPage();

        // Commentaires
        $this->renderCommentaires($form);

        // Justificatifs
        $this->renderJustificatifs($form);

        // Signature
        $this->renderSignature($form);
    }

    /**
     * En-tete du formulaire
     */
    protected function renderHeader($order)
    {
        // Titre
        $this->pdf->SetFont('dejavusans', 'B', 14);
        $this->pdf->Cell(0, 8, 'FORMULAIRE LCB-FT', 0, 1, 'C');

        $this->pdf->SetFont('dejavusans', '', 10);
        $this->pdf->Cell(0, 5, 'Article L 561-16 du Code monetaire et financier', 0, 1, 'C');

        $this->pdf->Ln(4);

        // Texte d'introduction
        $this->pdf->SetFont('dejavusans', '', 9);
        $intro = "Dans le cadre des obligations reglementaires relatives au dispositif de lutte contre le blanchiment des capitaux et le financement du terrorisme imposees a l'OR INVESTISSEMENT dans le cadre des operations effectuees aupres de ses Clients, nous vous prions de nous retourner le present formulaire dument complete, date et signe, accompagne d'un justificatif d'identite en cours de validite (recto-verso) des lors que la valeur de vos biens ou operations depassent la somme de 15 000 euros et/ou lorsque vous effectuez des operations successives.";
        $this->pdf->MultiCell(0, 4.5, $intro, 0, 'J');

        $this->pdf->Ln(5);
    }

    /**
     * Section Coordonnees
     */
    protected function renderCoordonnees($form)
    {
        // Titre section
        $this->pdf->SetFont('dejavusans', 'B', 11);
        $this->pdf->Cell(0, 7, 'VOS COORDONNEES', 0, 1, 'C');
        $this->pdf->Ln(2);

        $this->pdf->SetFont('dejavusans', '', 9);
        $this->pdf->SetDrawColor(0, 0, 0);

        // Nom de l'entreprise
        $this->pdf->Cell(35, 6, 'Nom de l\'entreprise :', 0, 0);
        $this->pdf->Cell(145, 6, $form->entreprise, 'B', 1);

        // Civilite + Nom + Prenom sur meme ligne
        $isMme = ($form->civilite == 'Mme' || $form->civilite == 'Madame');
        $isM = ($form->civilite == 'M.' || $form->civilite == 'M' || $form->civilite == 'Monsieur');

        $this->drawCheckbox($isMme);
        $this->pdf->Cell(12, 5, 'Mme.', 0, 0);
        $this->drawCheckbox($isM);
        $this->pdf->Cell(8, 5, 'M.', 0, 0);
        $this->pdf->Cell(12, 5, 'Nom :', 0, 0);
        $this->pdf->Cell(55, 5, $form->nom, 'B', 0);
        $this->pdf->Cell(18, 5, 'Prenom :', 0, 0);
        $this->pdf->Cell(55, 5, $form->prenom, 'B', 1);

        // Profession
        $this->pdf->Cell(22, 6, 'Profession :', 0, 0);
        $this->pdf->Cell(158, 6, $form->profession, 'B', 1);

        // Identifiant connexion
        $this->pdf->Cell(60, 6, 'Identifiant de connexion sur l\'espace prive :', 0, 0);
        $this->pdf->Cell(120, 6, $form->identifiant_connexion, 'B', 1);

        // Adresse
        $this->pdf->Cell(18, 6, 'Adresse :', 0, 0);
        $this->pdf->Cell(162, 6, $form->adresse, 'B', 1);

        // Code postal + Ville + Pays
        $this->pdf->Cell(22, 6, 'Code postal :', 0, 0);
        $this->pdf->Cell(25, 6, $form->code_postal, 'B', 0);
        $this->pdf->Cell(12, 6, 'Ville :', 0, 0);
        $this->pdf->Cell(60, 6, $form->ville, 'B', 0);
        $this->pdf->Cell(12, 6, 'Pays :', 0, 0);
        $this->pdf->Cell(49, 6, $form->pays, 'B', 1);

        // Email + Telephone
        $this->pdf->Cell(15, 6, 'E-mail :', 0, 0);
        $this->pdf->Cell(85, 6, $form->email, 'B', 0);
        $this->pdf->Cell(22, 6, 'Telephone :', 0, 0);
        $this->pdf->Cell(58, 6, $form->telephone, 'B', 1);

        $this->pdf->Ln(5);
    }

    /**
     * Section Attestation sur l'honneur
     */
    protected function renderAttestation($form)
    {
        // Titre
        $this->pdf->SetFont('dejavusans', 'B', 11);
        $this->pdf->Cell(0, 7, 'ATTESTATION SUR L\'HONNEUR', 0, 1, 'C');

        $this->pdf->SetFont('dejavusans', '', 9);
        $this->pdf->Cell(0, 5, '(Valable jusqu\'au 31 decembre de l\'annee en cours)', 0, 1, 'C');
        $this->pdf->Ln(2);
        $this->pdf->Cell(0, 5, 'J\'atteste sur l\'honneur des informations suivantes :', 0, 1);
        $this->pdf->Ln(2);

        // INFORMATIONS PATRIMONIALES
        $this->pdf->SetTextColor($this->greenColor[0], $this->greenColor[1], $this->greenColor[2]);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(5, 5, html_entity_decode('&#9830;', ENT_NOQUOTES, 'UTF-8'), 0, 0); // Losange
        $this->pdf->Cell(0, 5, 'INFORMATIONS PATRIMONIALES', 0, 1);
        $this->pdf->SetTextColor(0, 0, 0);

        $this->pdf->SetFont('dejavusans', '', 9);

        // Remunerations
        $this->pdf->MultiCell(0, 5, 'Remunerations brutes annuelles du Client (salaire, benefices, bonus, prime, pension de retraite, pension d\'invalidite...) :', 0, 'L');
        $this->pdf->Cell(180, 6, $form->remunerations_annuelles, 'B', 1);

        // Estimation patrimoine
        $this->pdf->Cell(0, 5, 'Estimation du patrimoine total :', 0, 1);

        $patrimoine = $form->patrimoine_estimation;
        $this->renderCheckboxLine('moins de 300 000 euros', ($patrimoine == 'less_300k'));
        $this->renderCheckboxLine('de 300 000 EUR a 720 000 euros', ($patrimoine == '300k_720k'));
        $this->renderCheckboxLine('de 720 000 EUR a 1,5 million d\'euros', ($patrimoine == '720k_1500k'));

        // Plus de 1,5M avec precision
        $isPlus1500k = ($patrimoine == 'more_1500k');
        $this->drawCheckbox($isPlus1500k);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(55, 5, 'plus de 1,5 million d\'euros', 0, 0);
        $this->pdf->SetFont('dejavusans', '', 9);
        $this->pdf->Cell(20, 5, '-> Precisez', 0, 0);
        $this->pdf->Cell(95, 5, $form->patrimoine_precision, 'B', 1);

        // IFI
        $this->pdf->Ln(2);
        $ifiOui = ($form->soumis_ifi == 1);
        $ifiNon = ($form->soumis_ifi === 0 || $form->soumis_ifi === '0');
        $this->pdf->Cell(75, 5, 'Etes-vous soumis (e) a l\'impot sur la Fortune Immobiliere ?', 0, 0);
        $this->drawCheckbox($ifiOui);
        $this->pdf->Cell(10, 5, 'Oui', 0, 0);
        $this->drawCheckbox($ifiNon);
        $this->pdf->Cell(10, 5, 'Non', 0, 1);

        $this->pdf->Ln(3);
    }

    /**
     * Section Origine des fonds
     */
    protected function renderOrigineFonds($form)
    {
        // Titre
        $this->pdf->SetTextColor($this->greenColor[0], $this->greenColor[1], $this->greenColor[2]);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(5, 5, html_entity_decode('&#9830;', ENT_NOQUOTES, 'UTF-8'), 0, 0);
        $this->pdf->Cell(0, 5, 'ORIGINE DES FONDS', 0, 1);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('dejavusans', '', 9);

        $this->pdf->MultiCell(0, 4.5, 'En cas de pluralite d\'origine des fonds, veuillez fournir le detail dans la zone "commentaires", les dates, montants et origines.', 0, 'L');
        $this->pdf->Ln(1);

        $origines = $form->getOrigineFonds();

        // Options simples
        $this->renderCheckboxLine('Vente Immobiliere', in_array('vente_immo', $origines));
        $this->renderCheckboxLine('Donation', in_array('donation', $origines));
        $this->renderCheckboxLine('Heritage', in_array('heritage', $origines));
        $this->renderCheckboxLine('Revenus ou Dividendes', in_array('revenus', $origines));
        $this->renderCheckboxLine('Gains aux jeux', in_array('jeux', $origines));

        // Cession d'actifs avec precision
        $isCession = in_array('cession', $origines);
        $this->drawCheckbox($isCession);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(75, 5, 'Cession d\'actifs (professionnels, Immobiliers, mobiliers...)', 0, 0);
        $this->pdf->SetFont('dejavusans', '', 9);
        $this->pdf->Cell(20, 5, '-> Precisez', 0, 0);
        $this->pdf->Cell(75, 5, $form->origine_cession_detail, 'B', 1);

        // Epargne personnelle avec precision
        $isEpargne = in_array('epargne', $origines);
        $this->drawCheckbox($isEpargne);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(35, 5, 'Epargne personnelle', 0, 0);
        $this->pdf->SetFont('dejavusans', '', 9);
        $this->pdf->Cell(70, 5, '-> Precisez la date et l\'origine de l\'investissement initial :', 0, 0);
        $this->pdf->Cell(65, 5, $form->origine_epargne_detail, 'B', 1);
        // Ligne supplementaire
        $this->pdf->Cell(180, 5, '', 'B', 1);

        // Autre avec precision
        $isAutre = in_array('autre', $origines);
        $this->drawCheckbox($isAutre);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(15, 5, 'Autre', 0, 0);
        $this->pdf->SetFont('dejavusans', '', 9);
        $this->pdf->Cell(20, 5, '-> Precisez :', 0, 0);
        $this->pdf->Cell(135, 5, $form->origine_autre_detail, 'B', 1);

        // Nature du justificatif
        $this->pdf->Ln(2);
        $this->pdf->MultiCell(0, 4.5, 'Nature du justificatif d\'origine des fonds fourni pour tout versement superieur a 15 000 EUR (montant total unique ou cumule sur 12 mois civils :', 0, 'L');
        $this->pdf->Cell(180, 5, $form->justificatif_origine_fonds, 'B', 1);
    }

    /**
     * Section Commentaires (page 2)
     */
    protected function renderCommentaires($form)
    {
        $this->pdf->SetFont('dejavusans', 'I', 8);
        $this->pdf->Cell(0, 5, 'Exemples : acte notarie, releve de compte, avis d\'imposition, ...', 0, 1);
        $this->pdf->Ln(3);

        // Titre
        $this->pdf->SetTextColor($this->greenColor[0], $this->greenColor[1], $this->greenColor[2]);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(5, 5, html_entity_decode('&#9830;', ENT_NOQUOTES, 'UTF-8'), 0, 0);
        $this->pdf->Cell(0, 5, 'COMMENTAIRES :', 0, 1);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('dejavusans', '', 9);

        $this->pdf->Cell(0, 5, 'En cas de pluralite d\'origine des fonds, veuillez detailler les dates, montants et origines ci-dessous :', 0, 1);
        $this->pdf->Ln(3);

        // Tableau commentaires
        $this->pdf->SetFont('dejavusans', '', 9);
        $this->pdf->Cell(35, 6, 'Date', 1, 0, 'L');
        $this->pdf->Cell(45, 6, 'Montant', 1, 0, 'L');
        $this->pdf->Cell(100, 6, 'Origine', 1, 1, 'L');

        // Lignes du tableau (8 lignes vides ou avec donnees)
        $commentaires = $form->getCommentaires();
        for ($i = 0; $i < 8; $i++) {
            $date = isset($commentaires[$i]['date']) ? $commentaires[$i]['date'] : '';
            $montant = isset($commentaires[$i]['montant']) ? $commentaires[$i]['montant'] : '';
            $origine = isset($commentaires[$i]['origine']) ? $commentaires[$i]['origine'] : '';

            $this->pdf->Cell(35, 6, $date, 1, 0, 'L');
            $this->pdf->Cell(45, 6, $montant, 1, 0, 'L');
            $this->pdf->Cell(100, 6, $origine, 1, 1, 'L');
        }

        $this->pdf->Ln(5);
    }

    /**
     * Section Justificatifs
     */
    protected function renderJustificatifs($form)
    {
        // Titre
        $this->pdf->SetTextColor($this->greenColor[0], $this->greenColor[1], $this->greenColor[2]);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(5, 5, html_entity_decode('&#9830;', ENT_NOQUOTES, 'UTF-8'), 0, 0);
        $this->pdf->Cell(0, 5, 'JUSTIFICATIFS FOURNIS :', 0, 1);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Ln(2);

        $justificatifs = $form->getJustificatifs();

        // Justificatif d'identite
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(0, 5, 'Justificatif D\'identite (en cours de validite)', 0, 1);
        $this->pdf->SetFont('dejavusans', '', 9);

        $this->renderCheckboxLine('Carte nationale d\'identite (recto-verso)', in_array('cni', $justificatifs));
        $this->renderCheckboxLine('Passeport (pages contenant vos informations, photo et signature)', in_array('passeport', $justificatifs));
        $this->renderCheckboxLine('Titre de sejour (recto-verso)', in_array('titre_sejour', $justificatifs));

        $this->pdf->Ln(2);

        // Justificatif Financier
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(0, 5, 'Justificatif Financier', 0, 1);
        $this->pdf->SetFont('dejavusans', '', 9);

        // Sur une ligne
        $this->drawCheckbox(in_array('acte_notarie', $justificatifs));
        $this->pdf->Cell(25, 5, 'Acte notarie', 0, 0);
        $this->drawCheckbox(in_array('releve_compte', $justificatifs));
        $this->pdf->Cell(35, 5, 'Releve de compte', 0, 0);
        $this->drawCheckbox(in_array('avis_imposition', $justificatifs));
        $this->pdf->Cell(35, 5, 'Avis d\'imposition', 0, 1);

        // Autre
        $isAutre = in_array('justif_autre', $justificatifs);
        $this->drawCheckbox($isAutre);
        $this->pdf->Cell(15, 5, 'Autre', 0, 0);
        $this->pdf->Cell(20, 5, '-> Precisez :', 0, 0);
        $this->pdf->Cell(135, 5, $form->justificatif_autre_detail, 'B', 1);

        $this->pdf->Ln(8);
    }

    /**
     * Section Signature
     */
    protected function renderSignature($form)
    {
        $this->pdf->SetFont('dejavusans', '', 9);

        // Date et Lieu + Signature sur meme ligne
        $this->pdf->Cell(15, 6, 'Fait le', 0, 0);

        // Format date JJ / MM / AAAA
        $dateSignature = $form->date_signature;
        $jour = '';
        $mois = '';
        $annee = '';
        if (!empty($dateSignature)) {
            $parts = explode('/', $dateSignature);
            if (count($parts) == 3) {
                $jour = $parts[0];
                $mois = $parts[1];
                $annee = $parts[2];
            } elseif (strpos($dateSignature, '-') !== false) {
                $parts = explode('-', $dateSignature);
                if (count($parts) == 3) {
                    $annee = $parts[0];
                    $mois = $parts[1];
                    $jour = $parts[2];
                }
            }
        }

        $this->pdf->Cell(15, 6, $jour, 'B', 0, 'C');
        $this->pdf->Cell(5, 6, '/', 0, 0, 'C');
        $this->pdf->Cell(15, 6, $mois, 'B', 0, 'C');
        $this->pdf->Cell(5, 6, '/', 0, 0, 'C');
        $this->pdf->Cell(20, 6, $annee, 'B', 0, 'C');

        $this->pdf->Cell(20, 6, '', 0, 0); // Espace

        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(0, 6, 'Signature (precedee de la mention "lu et approuve")', 0, 1);

        // Lieu
        $this->pdf->SetFont('dejavusans', '', 9);
        $this->pdf->Cell(5, 6, 'A', 0, 0);
        $this->pdf->Cell(60, 6, $form->lieu_signature, 'B', 0);

        $this->pdf->Ln(10);

        // Zone de signature (cadre)
        $this->pdf->SetDrawColor(0, 0, 0);
        $startY = $this->pdf->GetY();

        // Cadre signature a droite
        $this->pdf->Rect(100, $startY - 15, 80, 35);

        // Contenu signature si signee
        if ($form->acknowledged && !empty($form->signature_name)) {
            $this->pdf->SetXY(100, $startY - 10);
            $this->pdf->SetFont('dejavusans', 'I', 10);
            $this->pdf->Cell(80, 5, 'Lu et approuve', 0, 1, 'C');

            $this->pdf->SetX(100);
            $this->pdf->SetFont('times', 'BI', 14);
            $this->pdf->SetTextColor(0, 0, 128);
            $this->pdf->Cell(80, 8, $form->signature_name, 0, 1, 'C');

            $this->pdf->SetX(100);
            $this->pdf->SetFont('dejavusans', '', 7);
            $this->pdf->SetTextColor(100, 100, 100);
            $this->pdf->Cell(80, 4, $form->getFormattedSignature(), 0, 1, 'C');
        }

        $this->pdf->SetY($startY + 25);
        $this->pdf->SetTextColor(0, 0, 0);
    }

    /**
     * Dessine une case a cocher (carre) - coche ou non
     */
    protected function drawCheckbox($checked = false)
    {
        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();

        // Dessiner le carre de la case
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->SetLineWidth(0.3);
        $this->pdf->Rect($x + 0.5, $y + 1, 3.5, 3.5);

        // Si coche, dessiner un X ou une coche
        if ($checked) {
            $this->pdf->SetLineWidth(0.5);
            // Dessiner un X
            $this->pdf->Line($x + 1, $y + 1.5, $x + 3.5, $y + 4);
            $this->pdf->Line($x + 3.5, $y + 1.5, $x + 1, $y + 4);
        }

        $this->pdf->SetLineWidth(0.2);

        // Avancer le curseur
        $this->pdf->SetX($x + 6);
    }

    /**
     * Affiche une ligne avec case a cocher et label
     */
    protected function renderCheckboxLine($label, $checked = false)
    {
        $this->drawCheckbox($checked);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(0, 5, $label, 0, 1);
        $this->pdf->SetFont('dejavusans', '', 9);
    }

    protected function sanitizeFilename($filename)
    {
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        return $filename;
    }
}
