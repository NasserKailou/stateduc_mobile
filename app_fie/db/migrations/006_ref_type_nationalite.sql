-- ──────────────────────────────────────────────────────────────────────────────
-- Migration 006 — Table ref_type_nationalite
-- Synchronisée depuis StatEduc SQL Server : [BURUNDI].[dbo].[TYPE_NATIONALITE]
-- Colonnes : CODE_TYPE_NATIONALITE, LIBELLE_TYPE_NATIONALITE, ORDRE_TYPE_NATIONALITE
-- ──────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `ref_type_nationalite` (
  `code_type_nationalite` int(10) UNSIGNED NOT NULL COMMENT 'Code StatEduc',
  `libelle`               varchar(100) NOT NULL,
  `ordre`                 smallint(5) UNSIGNED DEFAULT 0,
  `synced_at`             datetime DEFAULT NULL,
  PRIMARY KEY (`code_type_nationalite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Nationalités — synchronisé depuis StatEduc TYPE_NATIONALITE';

-- Données par défaut (Burundi en premier, valeurs ISO courantes)
INSERT IGNORE INTO `ref_type_nationalite`
  (`code_type_nationalite`, `libelle`, `ordre`)
VALUES
  (1,  'Burundaise',        1),
  (2,  'Rwandaise',         2),
  (3,  'Congolaise (RDC)',  3),
  (4,  'Tanzanienne',       4),
  (5,  'Ougandaise',        5),
  (6,  'Kenyane',           6),
  (99, 'Autres',            99);
