-- =============================================================================
-- Migration 004 — Correctifs Session 8
-- Auteur   : AI Dev Session 8 — 2026-08-18
-- Description :
--   1. ref_type_annee : la vraie table N'A PAS de colonne 'ordre'.
--      Supprimer la colonne 'ordre' si elle a été ajoutée par la migration 003.
--      Le tri se fait par code_type_annee DESC (= année la plus récente en tête).
--   2. etablissements_miroir : s'assurer que les colonnes ATLAS_COLLINE
--      (secteur_ens, statut_org, milieu) existent bien (idempotent).
--   3. Correction données ref_type_annee : supprimer les valeurs fictives
--      et garder le vrai format (code = année début, ex: 2025 pour 2025-2026).
-- =============================================================================

SET NAMES utf8mb4;

-- =============================================================================
-- 1. ref_type_annee — Supprimer la colonne 'ordre' si elle existe
--    (la vraie table utilise code_type_annee comme clé de tri)
-- =============================================================================

-- Vérifier et supprimer la colonne 'ordre' si présente (ajoutée par migration 003)
-- NOTE : ALTER TABLE ... DROP COLUMN IF EXISTS n'est disponible qu'à partir de
-- MariaDB 10.4 / MySQL 8.0. Utilisation d'un bloc procédural pour compatibilité.

SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'ref_type_annee'
      AND COLUMN_NAME  = 'ordre'
);

-- Si la colonne 'ordre' existe, la supprimer
-- (syntaxe compatible MariaDB 10.4+)
ALTER TABLE ref_type_annee
    MODIFY COLUMN libelle VARCHAR(30) NOT NULL COMMENT 'ex: 2025-2026';

-- Supprimer la colonne ordre si elle existe
-- (le SELECT conditionnel ci-dessous gère la compatibilité)
DROP PROCEDURE IF EXISTS fie_drop_ordre_col;
DELIMITER //
CREATE PROCEDURE fie_drop_ordre_col()
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'ref_type_annee'
          AND COLUMN_NAME  = 'ordre'
    ) THEN
        ALTER TABLE ref_type_annee DROP COLUMN ordre;
    END IF;
END //
DELIMITER ;
CALL fie_drop_ordre_col();
DROP PROCEDURE IF EXISTS fie_drop_ordre_col;

-- Recréer l'index si nécessaire (sur actif uniquement)
DROP INDEX IF EXISTS idx_ordre ON ref_type_annee;

-- =============================================================================
-- 2. ref_type_annee — Nettoyer et corriger les données par défaut
--    Format réel : code_type_annee = année de début (ex: 2025 pour 2025-2026)
--                  annee_debut, annee_fin = années entières
-- =============================================================================

-- Supprimer les entrées fictives (code 1 à 8) si elles ont été insérées par 003
DELETE FROM ref_type_annee WHERE code_type_annee BETWEEN 1 AND 20;

-- Insérer les valeurs réelles (idempotent grâce à INSERT IGNORE)
INSERT IGNORE INTO ref_type_annee (code_type_annee, libelle, annee_debut, annee_fin, actif) VALUES
(2018, '2018-2019', 2018, 2019, 0),
(2019, '2019-2020', 2019, 2020, 0),
(2020, '2020-2021', 2020, 2021, 0),
(2021, '2021-2022', 2021, 2022, 0),
(2022, '2022-2023', 2022, 2023, 0),
(2023, '2023-2024', 2023, 2024, 0),
(2024, '2024-2025', 2024, 2025, 0),
(2025, '2025-2026', 2025, 2026, 1); -- actif = 1 → année courante

-- =============================================================================
-- 3. etablissements_miroir — Colonnes ATLAS_COLLINE (idempotent)
-- =============================================================================

-- secteur_ens (libellé SECTEUR_ENS)
ALTER TABLE etablissements_miroir
    MODIFY COLUMN IF EXISTS secteur_ens VARCHAR(100) NULL
    COMMENT 'Libellé SECTEUR_ENS (ATLAS_COLLINE)';

-- Si la colonne n'existe pas encore (ADD IF NOT EXISTS — MariaDB 10.4+)
ALTER TABLE etablissements_miroir
    ADD COLUMN IF NOT EXISTS secteur_ens VARCHAR(100) NULL
    COMMENT 'Libellé SECTEUR_ENS (ATLAS_COLLINE)';

ALTER TABLE etablissements_miroir
    ADD COLUMN IF NOT EXISTS statut_org VARCHAR(150) NULL
    COMMENT 'Libellé STATUT (ATLAS_COLLINE col 10)';

ALTER TABLE etablissements_miroir
    ADD COLUMN IF NOT EXISTS milieu VARCHAR(50) NULL
    COMMENT 'Libellé MILIEU (ATLAS_COLLINE col 14)';

-- =============================================================================
-- 4. ref_province / ref_commune / ref_colline (idempotent)
-- =============================================================================

CREATE TABLE IF NOT EXISTS ref_province (
    code_province   MEDIUMINT UNSIGNED NOT NULL,
    libelle         VARCHAR(100)       NOT NULL,
    PRIMARY KEY (code_province),
    KEY idx_libelle (libelle(60))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Provinces Burundi (ATLAS_COLLINE)';

CREATE TABLE IF NOT EXISTS ref_commune (
    code_commune    MEDIUMINT UNSIGNED NOT NULL,
    code_province   MEDIUMINT UNSIGNED NOT NULL,
    libelle         VARCHAR(100)       NOT NULL,
    PRIMARY KEY (code_commune),
    KEY idx_province (code_province)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Communes Burundi (ATLAS_COLLINE)';

CREATE TABLE IF NOT EXISTS ref_colline (
    code_colline    INT UNSIGNED       NOT NULL,
    code_commune    MEDIUMINT UNSIGNED NOT NULL,
    code_province   MEDIUMINT UNSIGNED NOT NULL,
    libelle         VARCHAR(100)       NOT NULL,
    PRIMARY KEY (code_colline),
    KEY idx_commune (code_commune),
    KEY idx_province (code_province)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Collines Burundi (ATLAS_COLLINE)';

-- =============================================================================
-- FIN MIGRATION 004
-- =============================================================================
