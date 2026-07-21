<?php
/**
 * download_user_excel_template.php — Session 68
 * Génère et télécharge le canevas Excel 11 colonnes pour l'import utilisateurs
 * avec liaison école+campagne (DICO_FIXE_REGROUPEMENT).
 *
 * Colonnes :
 *   A (0)  NOM_LONG_USER  — Nom complet de l'utilisateur
 *   B (1)  EMAIL_USER     — Adresse e-mail
 *   C (2)  TEL_USER       — Numéro de téléphone
 *   D (3)  CODE_USER      — Login (identifiant de connexion)
 *   E (4)  PWD            — Mot de passe (en clair, sera hashé bcrypt)
 *   F (5)  CODE_GROUPE    — Code groupe/profil (ex: 1, 2, 3)
 *   G (6)  CODE_ETAB      — Code administratif école (ex: 101012071)
 *   H (7)  ID_CAMP        — Identifiant campagne (ex: 12)
 *   I (8)  ID_SYSTEME     — Identifiant secteur (ex: 1)
 *   J (9)  ID_ANNEE       — Identifiant année scolaire (ex: 2024)
 *   K (10) ID_CHAINE      — Identifiant chaîne (ex: 1)
 *
 * Colonnes G-K optionnelles : si vides, seul le compte ADMIN_USERS est créé
 * (comportement identique à l'import 6 colonnes existant).
 */

require_once $GLOBALS['SISED_PATH_LIB'] . 'autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$spreadsheet = new Spreadsheet();

// ── Feuille 1 : Canevas import ───────────────────────────────────────────────
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Import_Utilisateurs');

// En-têtes colonnes
$headers = [
    'A1' => 'NOM_LONG_USER',
    'B1' => 'EMAIL_USER',
    'C1' => 'TEL_USER',
    'D1' => 'CODE_USER (login)',
    'E1' => 'MOT_DE_PASSE',
    'F1' => 'CODE_GROUPE',
    'G1' => 'CODE_ETAB',
    'H1' => 'ID_CAMP',
    'I1' => 'ID_SYSTEME',
    'J1' => 'ID_ANNEE',
    'K1' => 'ID_CHAINE',
];

foreach ($headers as $cell => $label) {
    $sheet->setCellValue($cell, $label);
}

// Style en-têtes obligatoires (A-F) : fond bleu foncé, texte blanc, gras
$styleOblig = [
    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
];
$sheet->getStyle('A1:F1')->applyFromArray($styleOblig);

// Style en-têtes optionnels (G-K) : fond orange, texte blanc, gras
$styleOpt = [
    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C55A11']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
];
$sheet->getStyle('G1:K1')->applyFromArray($styleOpt);

// Ligne exemple
$sheet->setCellValue('A2', 'Jean Dupont');
$sheet->setCellValue('B2', 'jean.dupont@education.bi');
$sheet->setCellValue('C2', '+25779123456');
$sheet->setCellValue('D2', 'jdupont');
$sheet->setCellValue('E2', 'MotDePasse123');
$sheet->setCellValue('F2', '2');
$sheet->setCellValue('G2', '101012071');
$sheet->setCellValue('H2', '12');
$sheet->setCellValue('I2', '1');
$sheet->setCellValue('J2', '2024');
$sheet->setCellValue('K2', '1');

// Style ligne exemple : fond gris clair
$styleEx = [
    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
];
$sheet->getStyle('A2:K2')->applyFromArray($styleEx);

// Largeurs colonnes
$colWidths = ['A'=>28,'B'=>30,'C'=>18,'D'=>18,'E'=>18,'F'=>14,'G'=>18,'H'=>12,'I'=>14,'J'=>12,'K'=>12];
foreach ($colWidths as $col => $w) {
    $sheet->getColumnDimension($col)->setWidth($w);
}

// Ligne de note
$sheet->setCellValue('A4', '* Colonnes A-F : OBLIGATOIRES');
$sheet->setCellValue('A5', '* Colonnes G-K (fond orange) : OPTIONNELLES — liaison automatique école+campagne dans DICO_FIXE_REGROUPEMENT');
$sheet->setCellValue('A6', '* La ligne 1 (en-têtes) et cette section de notes ne doivent PAS être modifiées.');
$sheet->setCellValue('A7', '* Saisir les utilisateurs à partir de la ligne 2 (supprimer la ligne exemple si nécessaire).');
$sheet->getStyle('A4:K7')->applyFromArray([
    'font' => ['italic' => true, 'color' => ['rgb' => '595959']],
]);

// ── Feuille 2 : Référence codes groupes ──────────────────────────────────────
$sheetRef = $spreadsheet->createSheet();
$sheetRef->setTitle('Ref_Groupes');
$sheetRef->setCellValue('A1', 'CODE_GROUPE');
$sheetRef->setCellValue('B1', 'Description');
$sheetRef->getStyle('A1:B1')->applyFromArray($styleOblig);

// Récupère les groupes depuis la BDD si disponible
$groupRows = [];
try {
    if (isset($GLOBALS['conn_dico'])) {
        $sql_grp = "SELECT DISTINCT CODE_USER, NOM_LONG_USER FROM ADMIN_USERS ORDER BY CODE_USER LIMIT 50";
        $rs = $GLOBALS['conn_dico']->GetAll("SELECT ID_GROUPE, LIBELLE FROM ADMIN_GROUPE ORDER BY ID_GROUPE");
        if (is_array($rs)) {
            foreach ($rs as $i => $row) {
                $sheetRef->setCellValue('A' . ($i + 2), $row[0] ?? $row['ID_GROUPE'] ?? '');
                $sheetRef->setCellValue('B' . ($i + 2), $row[1] ?? $row['LIBELLE'] ?? '');
            }
        }
    }
} catch (Exception $e) {
    $sheetRef->setCellValue('A2', '1');
    $sheetRef->setCellValue('B2', 'Administrateur');
    $sheetRef->setCellValue('A3', '2');
    $sheetRef->setCellValue('B3', 'Enquêteur mobile');
    $sheetRef->setCellValue('A4', '3');
    $sheetRef->setCellValue('B4', 'Superviseur');
}
$sheetRef->getColumnDimension('A')->setWidth(16);
$sheetRef->getColumnDimension('B')->setWidth(30);

// ── Téléchargement ────────────────────────────────────────────────────────────
$filename = 'canevas_import_utilisateurs_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
