-- migration_multiannee.sql — Migration base Access/ADODB vers multi-année
--
-- USAGE: Exécuter via l'interface ADODB ou directement dans Access
-- TAGS: AK-YEAR-MULTI-DB
--
-- ORDRE D'EXÉCUTION OBLIGATOIRE:
--   1. Créer table annees
--   2. Insérer l'année initiale
--   3. Ajouter colonne annee_code dans les tables métier
--   4. Créer les index composites
--   5. Vérifier l'intégrité
--
-- NOTE ACCESS: Access ne supporte pas IF EXISTS → exécuter chaque ALTER
-- individuellement et ignorer les erreurs "colonne déjà existante"
-- NOTE ADODB: utiliser $conn->Execute($sql) pour chaque instruction

-- ─────────────────────────────────────────────────────────────────────────────
-- ÉTAPE 1 — Table annees (catalogue des années scolaires)
-- ─────────────────────────────────────────────────────────────────────────────

-- Access DDL (CREATE TABLE)
CREATE TABLE annees (
    code        VARCHAR(10)   NOT NULL,
    libelle     VARCHAR(100)  NOT NULL,
    active      BYTE          NOT NULL DEFAULT 0,
    date_debut  DATETIME,
    date_fin    DATETIME,
    CONSTRAINT pk_annees PRIMARY KEY (code)
);

-- ─────────────────────────────────────────────────────────────────────────────
-- ÉTAPE 2 — Données initiales
-- ─────────────────────────────────────────────────────────────────────────────

-- Insérer l'année courante comme année active
-- ADAPTER : remplacer '2024' et le libellé selon l'environnement
INSERT INTO annees (code, libelle, active) VALUES ('2024', 'Année scolaire 2024-2025', 1);

-- Années précédentes (désactivées)
-- INSERT INTO annees (code, libelle, active) VALUES ('2023', 'Année scolaire 2023-2024', 0);
-- INSERT INTO annees (code, libelle, active) VALUES ('2022', 'Année scolaire 2022-2023', 0);

-- ─────────────────────────────────────────────────────────────────────────────
-- ÉTAPE 3 — Ajouter colonne annee_code dans les tables métier
-- ─────────────────────────────────────────────────────────────────────────────
-- ⚠ Access: ALTER TABLE ADD COLUMN ne supporte pas DEFAULT sur les textes
--   avec données existantes. Utiliser 2 étapes: ADD COLUMN puis UPDATE.

-- Table ETABLISSEMENT (établissements scolaires)
ALTER TABLE ETABLISSEMENT ADD COLUMN annee_code VARCHAR(10);
UPDATE ETABLISSEMENT SET annee_code = '2024' WHERE annee_code IS NULL;

-- Table DICO_FIXE_REGROUPEMENT (affectation agents mobiles)
ALTER TABLE DICO_FIXE_REGROUPEMENT ADD COLUMN annee_code VARCHAR(10);
UPDATE DICO_FIXE_REGROUPEMENT SET annee_code = '2024' WHERE annee_code IS NULL;

-- Table QUESTIONNAIRE (instances de formulaires)
-- Note: vérifier si cette table existe dans votre déploiement
-- ALTER TABLE QUESTIONNAIRE ADD COLUMN annee_code VARCHAR(10);
-- UPDATE QUESTIONNAIRE SET annee_code = '2024' WHERE annee_code IS NULL;

-- Table REPONSE (réponses aux formulaires)
-- Note: peut être volumineuse — prévoir une migration par batch
-- ALTER TABLE REPONSE ADD COLUMN annee_code VARCHAR(10);
-- UPDATE REPONSE SET annee_code = '2024' WHERE annee_code IS NULL;

-- ─────────────────────────────────────────────────────────────────────────────
-- ÉTAPE 4 — Index composites pour performance
-- ─────────────────────────────────────────────────────────────────────────────
-- Access: CREATE INDEX supporte les index composites

CREATE INDEX idx_etab_annee
    ON ETABLISSEMENT (annee_code, ID_ETABLISSEMENT);

CREATE INDEX idx_fixe_annee
    ON DICO_FIXE_REGROUPEMENT (annee_code, ID_REGROUPEMENT);

-- ─────────────────────────────────────────────────────────────────────────────
-- ÉTAPE 5 — Vérification intégrité
-- ─────────────────────────────────────────────────────────────────────────────

-- Vérifier qu'une seule année est active
SELECT COUNT(*) AS nb_actives FROM annees WHERE active = 1;
-- Résultat attendu: 1

-- Vérifier qu'aucun enregistrement n'a annee_code NULL
SELECT COUNT(*) AS sans_annee FROM ETABLISSEMENT WHERE annee_code IS NULL;
-- Résultat attendu: 0

-- ─────────────────────────────────────────────────────────────────────────────
-- MIGRATION PHP — script ADODB pour exécuter via le web (admin uniquement)
-- ─────────────────────────────────────────────────────────────────────────────
/*
PHP:

<?php
// migration_multiannee.php — À exécuter UNE SEULE FOIS depuis l'admin
require_once 'config_app.php';
require_once 'common_ws.php';

$steps = [
    // Étape 1 : table annees
    "CREATE TABLE annees (
        code    VARCHAR(10) NOT NULL CONSTRAINT pk_annees PRIMARY KEY,
        libelle VARCHAR(100) NOT NULL,
        active  BYTE NOT NULL DEFAULT 0
    )",
    // Étape 2 : données initiales
    "INSERT INTO annees (code, libelle, active) VALUES ('2024', 'Année scolaire 2024-2025', 1)",
    // Étape 3 : colonne ETABLISSEMENT
    "ALTER TABLE ETABLISSEMENT ADD COLUMN annee_code VARCHAR(10)",
    "UPDATE ETABLISSEMENT SET annee_code = '2024'",
    // Étape 4 : index
    "CREATE INDEX idx_etab_annee ON ETABLISSEMENT (annee_code, ID_ETABLISSEMENT)",
];

$conn = $GLOBALS['conn'];
$results = [];
foreach ($steps as $i => $sql) {
    try {
        $conn->Execute($sql);
        $results[] = ['step' => $i+1, 'ok' => true, 'sql' => substr($sql, 0, 60)];
    } catch (Exception $e) {
        $results[] = ['step' => $i+1, 'ok' => false, 'error' => $e->getMessage()];
        // Continuer même en cas d'erreur (colonne déjà existante = OK)
    }
}
header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);
*/

-- ─────────────────────────────────────────────────────────────────────────────
-- MIGRATION SQLite Flutter (database_helper.dart)
-- ─────────────────────────────────────────────────────────────────────────────
/*
Dart (dans _createDb ou _upgradeDb):

// Créer table school_years
await db.execute('''
    CREATE TABLE IF NOT EXISTS school_years (
        code       TEXT PRIMARY KEY,
        libelle    TEXT NOT NULL,
        active     INTEGER NOT NULL DEFAULT 0,
        synced_at  TEXT NOT NULL
    )
''');

// Créer table collected_data avec annee_code
await db.execute('''
    CREATE TABLE IF NOT EXISTS collected_data (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        camp_id    TEXT NOT NULL,
        etab_id    TEXT NOT NULL,
        annee_code TEXT NOT NULL DEFAULT '2024',
        field_key  TEXT NOT NULL,
        field_val  TEXT,
        updated_at TEXT NOT NULL,
        UNIQUE(camp_id, etab_id, annee_code, field_key)
    )
''');

// Migration depuis version sans annee_code:
// Dans onUpgrade(db, oldVersion, newVersion):
if (oldVersion < 2) {
    await db.execute(
        "ALTER TABLE collected_data ADD COLUMN annee_code TEXT NOT NULL DEFAULT '2024'"
    );
}
*/
