-- =============================================================================
-- MIGRATION 005 — Nettoyage établissements_miroir
-- =============================================================================
-- Supprime les colonnes qui n'existent PAS dans FICHIER_ETAB.xlsx / ATLAS_COLLINE
-- (14 colonnes) afin d'éviter les erreurs d'import.
--
-- Structure ATLAS_COLLINE conservée :
--   CODE_PROVINCE, PROVINCE, CODE_COMMUNE, COMMUNE, CODE_COLLINE, COLLINE,
--   CODE_TYPE_SECTEUR_ENS, SECTEUR_ENS, CODE_TYPE_STATUT_ORG, STATUT (→statut_org),
--   NOM_ETAB (→nom_etablissement), CODE_ETABLISSEMENT, CODE_TYPE_MILIEU, MILIEU
--
-- Colonnes opérationnelles conservées :
--   chaine_localisation, source, synced_at, stateduc_updated_at, actif
--
-- Colonnes supprimées (n'existent pas dans FICHIER_ETAB.xlsx) :
--   zone_admin, code_zone, code_type_fonction, code_type_etablissement,
--   code_type_etat_fonct, code_ecole_pays, code_etablissement_parent,
--   telephone, adresse_electronique, responsable_ecole, annee_creation
--
-- Idempotent : DROP COLUMN IF EXISTS (MariaDB 10.4+)
-- =============================================================================

-- zone_admin (libellé zone administrative — non présent dans ATLAS_COLLINE)
ALTER TABLE `etablissements_miroir`
    DROP COLUMN IF EXISTS `zone_admin`;

-- code_zone (code numérique de la zone — non présent dans ATLAS_COLLINE)
ALTER TABLE `etablissements_miroir`
    DROP COLUMN IF EXISTS `code_zone`;

-- code_type_fonction
ALTER TABLE `etablissements_miroir`
    DROP COLUMN IF EXISTS `code_type_fonction`;

-- code_type_etablissement
ALTER TABLE `etablissements_miroir`
    DROP COLUMN IF EXISTS `code_type_etablissement`;

-- code_type_etat_fonct
ALTER TABLE `etablissements_miroir`
    DROP COLUMN IF EXISTS `code_type_etat_fonct`;

-- code_ecole_pays (code administratif national — hors ATLAS_COLLINE)
ALTER TABLE `etablissements_miroir`
    DROP COLUMN IF EXISTS `code_ecole_pays`;

-- code_etablissement_parent
ALTER TABLE `etablissements_miroir`
    DROP COLUMN IF EXISTS `code_etablissement_parent`;

-- telephone
ALTER TABLE `etablissements_miroir`
    DROP COLUMN IF EXISTS `telephone`;

-- adresse_electronique
ALTER TABLE `etablissements_miroir`
    DROP COLUMN IF EXISTS `adresse_electronique`;

-- responsable_ecole
ALTER TABLE `etablissements_miroir`
    DROP COLUMN IF EXISTS `responsable_ecole`;

-- annee_creation
ALTER TABLE `etablissements_miroir`
    DROP COLUMN IF EXISTS `annee_creation`;

-- =============================================================================
-- S'assurer que les colonnes ATLAS_COLLINE et opérationnelles existent bien
-- (idempotent — ADD COLUMN IF NOT EXISTS, MariaDB 10.4+)
-- =============================================================================

-- Libellés géographiques (ajoutés par migration 003 mais vérification idempotente)
ALTER TABLE `etablissements_miroir`
    ADD COLUMN IF NOT EXISTS `secteur_ens` VARCHAR(100) NULL
        COMMENT 'Libellé SECTEUR_ENS (ATLAS_COLLINE col 8)';

ALTER TABLE `etablissements_miroir`
    ADD COLUMN IF NOT EXISTS `statut_org` VARCHAR(150) NULL
        COMMENT 'Libellé STATUT (ATLAS_COLLINE col 10 — STATUT_ORG)';

ALTER TABLE `etablissements_miroir`
    ADD COLUMN IF NOT EXISTS `milieu` VARCHAR(50) NULL
        COMMENT 'Libellé MILIEU (ATLAS_COLLINE col 14)';

-- =============================================================================
-- FIN MIGRATION 005
-- =============================================================================
-- Colonnes restantes après migration (ordre logique) :
--   code_etablissement  — PK
--   nom_etablissement
--   province
--   commune
--   colline
--   chaine_localisation
--   code_province
--   code_commune
--   code_colline
--   code_type_milieu
--   code_type_statut_org
--   code_type_secteur_ens
--   secteur_ens
--   statut_org
--   milieu
--   source
--   synced_at
--   stateduc_updated_at
--   actif
-- =============================================================================
