-- migration_multiannee.sql — Ajout du support multi-année
--
-- USAGE: Exécuter via ADODB ou outil d'administration Access
--        Script adapté pour Microsoft Access (Jet SQL)
--
-- TAGS: AK-YEAR-MULTI-DB
--
-- ORDRE D'EXÉCUTION: Respecter l'ordre — contraintes de clé étrangère
--
-- AVERTISSEMENT: Microsoft Access ne supporte pas toutes les syntaxes SQL standard.
--   - Pas de transactions DDL (les ALTER sont auto-committés)
--   - Les index: utiliser CREATE INDEX (supporté)
--   - Les contraintes FK: limitées dans Access, simulées par application
--
-- AVANT D'EXÉCUTER:
--   1. Sauvegarder la base .mdb
--   2. Fermer toutes les connexions ADODB
--   3. Vérifier que l'utilisateur a les droits DDL

-- ─────────────────────────────────────────────────────────────────────────────
-- ÉTAPE 1 — Créer la table annees (catalogue)
-- ─────────────────────────────────────────────────────────────────────────────

-- Note: En Access, TEXT = VARCHAR. TINYINT = BIT (0/1).
-- Utiliser TEXT pour les codes (plus flexible que INT pour "2024-2025")

CREATE TABLE annees (
    code        TEXT(10)    NOT NULL,    -- "2024" ou "2024-2025"
    libelle     TEXT(150)   NOT NULL,    -- "Année scolaire 2024-2025"
    active      BYTE        NOT NULL DEFAULT 0,   -- 1 = année active
    date_debut  DATETIME,
    date_fin    DATETIME,
    CONSTRAINT pk_annees PRIMARY KEY (code)
);

-- Insérer l'année initiale (adapter selon l'environnement)
INSERT INTO annees (code, libelle, active, date_debut, date_fin)
VALUES ('2024', 'Année scolaire 2024-2025', 1,
        #01/09/2024#, #31/08/2025#);

-- ─────────────────────────────────────────────────────────────────────────────
-- ÉTAPE 2 — Ajouter annee_code à chaque table métier
-- ─────────────────────────────────────────────────────────────────────────────
-- Note: DEFAULT non supporté dans ALTER TABLE Access pour certaines versions.
-- Si erreur: ALTER TABLE puis UPDATE séparé.

-- Table: etablissements
ALTER TABLE etablissements ADD COLUMN annee_code TEXT(10) NOT NULL;
UPDATE etablissements SET annee_code = '2024' WHERE annee_code IS NULL OR annee_code = '';

-- Table: questionnaires
ALTER TABLE questionnaires ADD COLUMN annee_code TEXT(10) NOT NULL;
UPDATE questionnaires SET annee_code = '2024' WHERE annee_code IS NULL OR annee_code = '';

-- Table: reponses
ALTER TABLE reponses ADD COLUMN annee_code TEXT(10) NOT NULL;
UPDATE reponses SET annee_code = '2024' WHERE annee_code IS NULL OR annee_code = '';

-- Table: enqueteurs
ALTER TABLE enqueteurs ADD COLUMN annee_code TEXT(10) NOT NULL;
UPDATE enqueteurs SET annee_code = '2024' WHERE annee_code IS NULL OR annee_code = '';

-- Table: soumissions (si existante)
-- ALTER TABLE soumissions ADD COLUMN annee_code TEXT(10) NOT NULL;
-- UPDATE soumissions SET annee_code = '2024' WHERE annee_code IS NULL OR annee_code = '';

-- ─────────────────────────────────────────────────────────────────────────────
-- ÉTAPE 3 — Créer les index composites pour la performance
-- ─────────────────────────────────────────────────────────────────────────────
-- Index sur (annee_code, id) pour les requêtes WHERE annee_code = ? ORDER BY id

CREATE INDEX idx_etablissements_annee ON etablissements (annee_code, id);
CREATE INDEX idx_questionnaires_annee ON questionnaires (annee_code, etab_id);
CREATE INDEX idx_reponses_annee       ON reponses       (annee_code, quest_id);
CREATE INDEX idx_enqueteurs_annee     ON enqueteurs     (annee_code, id);

-- ─────────────────────────────────────────────────────────────────────────────
-- ÉTAPE 4 — Créer la table SQLite locale (dans l'app Flutter)
-- ─────────────────────────────────────────────────────────────────────────────
-- À exécuter via database_helper.dart lors de la migration SQLite

-- cache_annees: copie locale des années pour fonctionnement offline
-- CREATE TABLE IF NOT EXISTS cache_annees (
--     code        TEXT PRIMARY KEY,
--     libelle     TEXT NOT NULL,
--     active      INTEGER NOT NULL DEFAULT 0,
--     synced_at   TEXT NOT NULL     -- ISO-8601: "2024-09-15T10:30:00.000Z"
-- );

-- pending_syncs: file d'attente pour envoi différé (mode offline)
-- CREATE TABLE IF NOT EXISTS pending_syncs (
--     id          INTEGER PRIMARY KEY AUTOINCREMENT,
--     annee_code  TEXT    NOT NULL,
--     payload     TEXT    NOT NULL,    -- JSON encodé
--     created_at  TEXT    NOT NULL,
--     attempts    INTEGER NOT NULL DEFAULT 0,
--     last_error  TEXT
-- );

-- ─────────────────────────────────────────────────────────────────────────────
-- ÉTAPE 5 — Script de vérification post-migration
-- ─────────────────────────────────────────────────────────────────────────────

-- Vérifier que toutes les lignes ont un annee_code
SELECT 'etablissements', COUNT(*) AS total, SUM(IIF(annee_code='',1,0)) AS sans_annee
FROM etablissements;

SELECT 'questionnaires', COUNT(*) AS total, SUM(IIF(annee_code='',1,0)) AS sans_annee
FROM questionnaires;

SELECT 'reponses', COUNT(*) AS total, SUM(IIF(annee_code='',1,0)) AS sans_annee
FROM reponses;

-- Vérifier qu'il y a exactement une année active
SELECT COUNT(*) AS nb_actives FROM annees WHERE active = 1;
-- Doit retourner: 1

-- ─────────────────────────────────────────────────────────────────────────────
-- ROLLBACK (si migration échoue — à exécuter manuellement)
-- ─────────────────────────────────────────────────────────────────────────────

-- ALTER TABLE etablissements DROP COLUMN annee_code;
-- ALTER TABLE questionnaires DROP COLUMN annee_code;
-- ALTER TABLE reponses       DROP COLUMN annee_code;
-- ALTER TABLE enqueteurs     DROP COLUMN annee_code;
-- DROP TABLE annees;

-- DROP INDEX idx_etablissements_annee ON etablissements;
-- DROP INDEX idx_questionnaires_annee ON questionnaires;
-- DROP INDEX idx_reponses_annee       ON reponses;
-- DROP INDEX idx_enqueteurs_annee     ON enqueteurs;

-- ─────────────────────────────────────────────────────────────────────────────
-- NOTES D'EXÉCUTION ADODB (PHP)
-- ─────────────────────────────────────────────────────────────────────────────

/*
// Exécuter via PHP ADODB:
require_once 'adodb/adodb.inc.php';
$conn = ADONewConnection('access');
$conn->Connect('', '', '', 'C:/chemin/vers/stateduc.mdb');

// Lire et exécuter chaque statement
$sql = file_get_contents('migration_multiannee.sql');
// Splitter sur ';' (simpliste — adapter si besoin)
$statements = array_filter(array_map('trim', explode(';', $sql)));
foreach ($statements as $stmt) {
    if (empty($stmt) || strpos($stmt, '--') === 0) continue;
    $ok = $conn->Execute($stmt);
    if (!$ok) {
        error_log('[migration] ERREUR: ' . $conn->ErrorMsg() . ' | SQL: ' . $stmt);
    }
}
*/
