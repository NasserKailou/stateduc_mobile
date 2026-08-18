-- =============================================================================
-- Migration 003 — Alignement ATLAS_COLLINE + Années scolaires TYPE_ANNEE
-- Auteur   : AI Dev Session 6 — 2026-08-18
-- Description :
--   1. Patch etablissements_miroir : colonnes exactes de la vue ATLAS_COLLINE
--      (= structure exacte du fichier FICHIER_ETAB.xlsx)
--      14 colonnes : CODE_PROVINCE, PROVINCE, CODE_COMMUNE, COMMUNE,
--                    CODE_COLLINE, COLLINE, CODE_TYPE_SECTEUR_ENS, SECTEUR_ENS,
--                    CODE_TYPE_STATUT_ORG, STATUT, NOM_ETAB,
--                    CODE_ETABLISSEMENT, CODE_TYPE_MILIEU, MILIEU
--   2. Table ref_type_annee : années scolaires depuis TYPE_ANNEE StatEduc
--   3. Index optimisés pour les cascades province → commune → colline → etab
--   4. Tables ref_province / ref_commune / ref_colline pour lookups déconnectés
-- =============================================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- =============================================================================
-- 1. PATCH etablissements_miroir
--    Ajouter les colonnes libellé manquantes (SECTEUR_ENS, STATUT, MILIEU)
--    La table existante garde ses colonnes (province, commune, colline textes)
--    pour rétrocompatibilité ; on ajoute les libellés ATLAS_COLLINE.
-- =============================================================================

-- 1.1 Libellés ATLAS_COLLINE (colonnes texte pour affichage sans JOIN)
ALTER TABLE etablissements_miroir
    ADD COLUMN IF NOT EXISTS secteur_ens   VARCHAR(100) NULL COMMENT 'Libellé SECTEUR_ENS (ATLAS_COLLINE / Excel col 8)',
    ADD COLUMN IF NOT EXISTS statut_org    VARCHAR(150) NULL COMMENT 'Libellé STATUT (ATLAS_COLLINE / Excel col 10)',
    ADD COLUMN IF NOT EXISTS milieu        VARCHAR(50)  NULL COMMENT 'Libellé MILIEU (ATLAS_COLLINE / Excel col 14)';

-- 1.2 Index supplémentaires pour cascades AJAX Province → Commune → Colline → Etab
-- Index sur (province, commune) pour texte (cascade legacy)
ALTER TABLE etablissements_miroir
    DROP INDEX IF EXISTS idx_prov_comm;
ALTER TABLE etablissements_miroir
    ADD INDEX idx_prov_comm (province(80), commune(80));

-- Index sur codes entiers (cascade nouvelle)
ALTER TABLE etablissements_miroir
    DROP INDEX IF EXISTS idx_codes_geo;
ALTER TABLE etablissements_miroir
    ADD INDEX idx_codes_geo (code_province, code_commune, code_colline);

-- Index sur secteur + statut + milieu pour filtres
ALTER TABLE etablissements_miroir
    DROP INDEX IF EXISTS idx_typ_secteur_statut;
ALTER TABLE etablissements_miroir
    ADD INDEX idx_typ_secteur_statut (code_type_secteur_ens, code_type_statut_org, code_type_milieu);

-- =============================================================================
-- 2. TABLE ref_type_annee — Années scolaires depuis TYPE_ANNEE StatEduc
--    Miroir de :
--      SELECT CODE_TYPE_ANNEE, LIBELLE_TYPE_ANNEE, ORDRE_TYPE_ANNEE
--      FROM [BURUNDI].[dbo].[TYPE_ANNEE]
-- =============================================================================

CREATE TABLE IF NOT EXISTS ref_type_annee (
    code_type_annee   SMALLINT UNSIGNED NOT NULL COMMENT 'CODE_TYPE_ANNEE StatEduc',
    libelle           VARCHAR(30)       NOT NULL COMMENT 'LIBELLE_TYPE_ANNEE (ex: 2025-2026)',
    ordre             SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'ORDRE_TYPE_ANNEE',
    actif             TINYINT(1)        NOT NULL DEFAULT 0,
    synced_at         DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (code_type_annee),
    KEY idx_ordre (ordre),
    KEY idx_actif (actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Miroir TYPE_ANNEE StatEduc — synchronisé depuis SQL Server';

-- Valeurs par défaut (seront remplacées/complétées par sync depuis StatEduc)
INSERT IGNORE INTO ref_type_annee (code_type_annee, libelle, ordre, actif) VALUES
(1, '2018-2019', 1, 0),
(2, '2019-2020', 2, 0),
(3, '2020-2021', 3, 0),
(4, '2021-2022', 4, 0),
(5, '2022-2023', 5, 0),
(6, '2023-2024', 6, 0),
(7, '2024-2025', 7, 0),
(8, '2025-2026', 8, 1);  -- actif = 1 → année courante par défaut

-- =============================================================================
-- 3. TABLES DE RÉFÉRENCE GÉOGRAPHIQUE
--    Alimentées lors du 1er import Excel / sync ATLAS_COLLINE.
--    Permettent les cascades province→commune→colline sans accès StatEduc.
-- =============================================================================

CREATE TABLE IF NOT EXISTS ref_province (
    code_province   MEDIUMINT UNSIGNED NOT NULL COMMENT 'CODE_PROVINCE StatEduc',
    libelle         VARCHAR(100)       NOT NULL,
    PRIMARY KEY (code_province),
    KEY idx_libelle (libelle(60))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Provinces Burundi (ATLAS_COLLINE)';

CREATE TABLE IF NOT EXISTS ref_commune (
    code_commune    MEDIUMINT UNSIGNED NOT NULL COMMENT 'CODE_COMMUNE StatEduc',
    code_province   MEDIUMINT UNSIGNED NOT NULL,
    libelle         VARCHAR(100)       NOT NULL,
    PRIMARY KEY (code_commune),
    KEY idx_province (code_province),
    KEY idx_libelle  (libelle(60))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Communes Burundi (ATLAS_COLLINE)';

CREATE TABLE IF NOT EXISTS ref_colline (
    code_colline    INT UNSIGNED       NOT NULL COMMENT 'CODE_COLLINE StatEduc',
    code_commune    MEDIUMINT UNSIGNED NOT NULL,
    code_province   MEDIUMINT UNSIGNED NOT NULL,
    libelle         VARCHAR(100)       NOT NULL,
    PRIMARY KEY (code_colline),
    KEY idx_commune  (code_commune),
    KEY idx_province (code_province),
    KEY idx_libelle  (libelle(60))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Collines Burundi (ATLAS_COLLINE)';

-- =============================================================================
-- 4. TABLE sync_type_annee_log — trace la sync des années scolaires
-- =============================================================================

CREATE TABLE IF NOT EXISTS sync_type_annee_log (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    triggered_by VARCHAR(100) NULL,
    status       ENUM('success','error') NOT NULL DEFAULT 'success',
    total        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    details      TEXT NULL,
    synced_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Log sync années scolaires TYPE_ANNEE';

SET foreign_key_checks = 1;
