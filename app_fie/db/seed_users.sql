-- =============================================================================
-- FIE — Seed utilisateurs de test (Phase 3)
-- 5 profils couvrant toute la hiérarchie RBAC
-- Hashes bcrypt cost 12 RÉELS générés avec bcrypt (Python/PHP compatibles)
-- ($2b$ Python == $2y$ PHP pour password_verify())
--
-- ┌─────────────────────┬──────────────────────┬────────────────────────┐
-- │ Login               │ Mot de passe (clair) │ Rôle                   │
-- ├─────────────────────┼──────────────────────┼────────────────────────┤
-- │ admin.fie           │ AdminFIE2026!        │ super_admin            │
-- │ admin.bujumbura     │ BujumburaFIE!        │ admin_provincial       │
-- │ gest.lycee.mwm      │ LyceeFIE2026!        │ gestionnaire_etab      │
-- │ enseignant.dupont   │ EnseignFIE26!        │ enseignant             │
-- │ consultant.mineduc  │ ConsultFIE26!        │ consultant             │
-- └─────────────────────┴──────────────────────┴────────────────────────┘
--
-- Hashes générés avec bcrypt cost 12 (compatibles password_verify() PHP 8).
-- Pour vérifier en PHP :
--   var_dump(password_verify('AdminFIE2026!', '$2b$12$VGP/y4X1XA6FxFEFzbEvfu...'));
-- Pour regénérer en PHP :
--   php -r "echo password_hash('NouveauMotDePasse', PASSWORD_BCRYPT, ['cost'=>12]);"
-- =============================================================================

-- Désactiver vérifications FK temporairement
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
-- 1. SUPER ADMIN — Administrateur national FIE
--    Accès : TOTAL — toutes provinces, toutes fonctions
--    Mot de passe : AdminFIE2026!
--    Hash bcrypt cost 12 (réel) :
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms, role,
    province_code, actif, created_at
) VALUES (
    'admin.fie',
    '$2b$12$VGP/y4X1XA6FxFEFzbEvfuUiw2uHtJLGK/2LSIvmjAjfkacXYbApS',
    'NDAYISHIMIYE',
    'Adolphe',
    'super_admin',
    NULL,
    1,
    NOW()
);

-- =============================================================================
-- 2. ADMIN PROVINCIAL — Administrateur province Bujumbura Mairie
--    Accès : Province BJM uniquement — inscription, recherche, modif, audit
--    Mot de passe : BujumburaFIE!
--    Hash bcrypt cost 12 (réel) :
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms, role,
    province_code, actif, created_at
) VALUES (
    'admin.bujumbura',
    '$2b$12$jQxo5BIHbYMNL7FYDAm9Fu8M1N3w427Ca.bfvIfJ2VwrFZeS5w26y',
    'HAKIZIMANA',
    'Pierre-Claver',
    'admin_provincial',
    'BJM',
    1,
    NOW()
);

-- =============================================================================
-- 3. GESTIONNAIRE D'ÉTABLISSEMENT — Lycée Municipal de Muramvya
--    Accès : Inscription, recherche, modification — son établissement
--    Mot de passe : LyceeFIE2026!
--    Hash bcrypt cost 12 (réel) :
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms, role,
    province_code, actif, created_at
) VALUES (
    'gest.lycee.mwm',
    '$2b$12$GlDgu0HMuRigM3LHecWoRe8Z7qQUEtUjMskFeN7YdcTo1wKRmLjt2',
    'NZEYIMANA',
    'Jeanne',
    'gestionnaire_etab',
    'MWM',
    1,
    NOW()
);

-- =============================================================================
-- 4. ENSEIGNANT — Agent de saisie des inscriptions, province Gitega
--    Accès : Inscription + recherche uniquement — pas d'administration
--    Mot de passe : EnseignFIE26!
--    Hash bcrypt cost 12 (réel) :
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms, role,
    province_code, actif, created_at
) VALUES (
    'enseignant.dupont',
    '$2b$12$kxKTMJRglg2dFGl/d6HV.OTbpkQj9KuDvQDYY.KOjqdCKvWWqYz5a',
    'NIYONGABO',
    'Jean-Paul',
    'enseignant',
    'GIT',
    1,
    NOW()
);

-- =============================================================================
-- 5. CONSULTANT MENERS — Accès lecture seule (rapports, tableaux de bord)
--    Accès : Recherche + consultation uniquement, aucune modification
--    Mot de passe : ConsultFIE26!
--    Hash bcrypt cost 12 (réel) :
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms, role,
    province_code, actif, created_at
) VALUES (
    'consultant.mineduc',
    '$2b$12$0HTU.8VlCdpmrGRtzRPmDO0rSLEPIRkCQbjsZ9VdLkgLQHi..YKYG',
    'KABURA',
    'Marie-Louise',
    'consultant',
    NULL,
    1,
    NOW()
);

-- Réactiver FK
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Vérification post-import
-- =============================================================================
SELECT id, login, nom, prenoms, role, province_code, actif
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
--
-- NOTE COMPATIBILITÉ :
-- PHP password_verify() accepte indifféremment $2b$ (Python/OpenBSD) et $2y$ (PHP).
-- Les hashes ci-dessus sont donc 100% compatibles avec AuthController.php.
-- Référence : https://www.php.net/manual/fr/function.password-verify.php
-- =============================================================================
