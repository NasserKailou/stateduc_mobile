-- =============================================================================
-- FIE — Seed utilisateurs de test (Phase 3 — corrigé)
-- 5 profils couvrant toute la hiérarchie RBAC
-- Hashes bcrypt cost 12 RÉELS (Python bcrypt, $2b$ compatible PHP password_verify)
--
-- SCHÉMA fie_users (colonnes réelles) :
--   login, password_hash, nom, prenoms, email, telephone,
--   role, code_etablissement, province_perimetre,   ← PAS province_code
--   actif, must_change_password, created_at
--
-- ┌─────────────────────┬──────────────────────┬────────────────────────┐
-- │ Login               │ Mot de passe (clair) │ Rôle                   │
-- ├─────────────────────┼──────────────────────┼────────────────────────┤
-- │ admin.fie           │ AdminFIE2026!        │ super_admin            │
-- │ admin.bujumbura     │ BujumburaFIE!        │ admin_provincial (BJM) │
-- │ gest.lycee.mwm      │ LyceeFIE2026!        │ gestionnaire_etab (MWM)│
-- │ enseignant.dupont   │ EnseignFIE26!        │ enseignant (GIT)       │
-- │ consultant.mineduc  │ ConsultFIE26!        │ consultant             │
-- └─────────────────────┴──────────────────────┴────────────────────────┘
--
-- Compatibilité : $2b$ (Python) = $2y$ (PHP) pour password_verify() PHP 8
-- Pour regénérer : php -r "echo password_hash('Pwd', PASSWORD_BCRYPT, ['cost'=>12]);"
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Nettoyage préalable (idempotent)
DELETE FROM fie_users WHERE login IN (
    'admin.fie',
    'admin.bujumbura',
    'gest.lycee.mwm',
    'enseignant.dupont',
    'consultant.mineduc'
);

-- =============================================================================
-- 1. SUPER ADMIN — Accès TOTAL, toutes provinces, toutes fonctions
--    Mot de passe : AdminFIE2026!
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms,
    role, code_etablissement, province_perimetre,
    actif, must_change_password, created_at
) VALUES (
    'admin.fie',
    '$2b$12$VGP/y4X1XA6FxFEFzbEvfuUiw2uHtJLGK/2LSIvmjAjfkacXYbApS',
    'NDAYISHIMIYE', 'Adolphe',
    'super_admin', NULL, NULL,
    1, 0, NOW()
);

-- =============================================================================
-- 2. ADMIN PROVINCIAL — Province Bujumbura Mairie (BJM)
--    Accès : inscription, recherche, modification, audit — province BJM
--    Mot de passe : BujumburaFIE!
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms,
    role, code_etablissement, province_perimetre,
    actif, must_change_password, created_at
) VALUES (
    'admin.bujumbura',
    '$2b$12$jQxo5BIHbYMNL7FYDAm9Fu8M1N3w427Ca.bfvIfJ2VwrFZeS5w26y',
    'HAKIZIMANA', 'Pierre-Claver',
    'admin_provincial', NULL, 'Bujumbura Mairie',
    1, 0, NOW()
);

-- =============================================================================
-- 3. GESTIONNAIRE D'ÉTABLISSEMENT — Lycée Municipal de Muramvya (MWM)
--    Accès : inscription, recherche, modification — son établissement
--    Mot de passe : LyceeFIE2026!
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms,
    role, code_etablissement, province_perimetre,
    actif, must_change_password, created_at
) VALUES (
    'gest.lycee.mwm',
    '$2b$12$GlDgu0HMuRigM3LHecWoRe8Z7qQUEtUjMskFeN7YdcTo1wKRmLjt2',
    'NZEYIMANA', 'Jeanne',
    'gestionnaire_etab', NULL, 'Muramvya',
    1, 0, NOW()
);

-- =============================================================================
-- 4. ENSEIGNANT — Province Gitega (GIT)
--    Accès : inscription + recherche uniquement
--    Mot de passe : EnseignFIE26!
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms,
    role, code_etablissement, province_perimetre,
    actif, must_change_password, created_at
) VALUES (
    'enseignant.dupont',
    '$2b$12$kxKTMJRglg2dFGl/d6HV.OTbpkQj9KuDvQDYY.KOjqdCKvWWqYz5a',
    'NIYONGABO', 'Jean-Paul',
    'enseignant', NULL, 'Gitega',
    1, 0, NOW()
);

-- =============================================================================
-- 5. CONSULTANT MENERS — Lecture seule (rapports, tableaux de bord)
--    Accès : recherche + consultation uniquement
--    Mot de passe : ConsultFIE26!
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms,
    role, code_etablissement, province_perimetre,
    actif, must_change_password, created_at
) VALUES (
    'consultant.mineduc',
    '$2b$12$0HTU.8VlCdpmrGRtzRPmDO0rSLEPIRkCQbjsZ9VdLkgLQHi..YKYG',
    'KABURA', 'Marie-Louise',
    'consultant', NULL, NULL,
    1, 0, NOW()
);

SET FOREIGN_KEY_CHECKS = 1;

-- Vérification post-import
SELECT id, login, nom, prenoms, role, province_perimetre, actif
FROM fie_users
WHERE login IN (
    'admin.fie', 'admin.bujumbura', 'gest.lycee.mwm',
    'enseignant.dupont', 'consultant.mineduc'
)
ORDER BY id;

-- =============================================================================
-- TABLEAU RÉCAPITULATIF DES PERMISSIONS RBAC
-- =============================================================================
-- Rôle                | Inscription | Recherche | Modif | Sync | Audit | Users
-- super_admin         |     ✅      |     ✅    |  ✅   |  ✅  |  ✅   |  ✅
-- admin_central       |     ✅      |     ✅    |  ✅   |  ✅  |  ✅   |  ✅
-- admin_provincial    |     ✅      |     ✅    |  ✅   |  ❌  |  ✅   |  ❌
-- gestionnaire_etab   |     ✅      |     ✅    |  ✅   |  ❌  |  ❌   |  ❌
-- enseignant          |     ✅      |     ✅    |  ❌   |  ❌  |  ❌   |  ❌
-- consultant          |     ❌      |     ✅    |  ❌   |  ❌  |  ❌   |  ❌
-- api_client          |     ❌      |     ❌    |  ❌   |  ❌  |  ❌   |  ❌
-- =============================================================================
-- NOTE : $2b$ (Python bcrypt) == $2y$ (PHP) — password_verify() PHP 8 OK
-- =============================================================================
