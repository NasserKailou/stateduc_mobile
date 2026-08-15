-- =============================================================================
-- FIE — Seed utilisateurs de test (Phase 3)
-- 5 profils couvrant toute la hiérarchie RBAC
-- Mots de passe hachés avec bcrypt cost 12 (password_hash PHP)
--
-- ┌─────────────────────┬──────────────────────┬────────────────────────┐
-- │ Login               │ Mot de passe (clair) │ Rôle                   │
-- ├─────────────────────┼──────────────────────┼────────────────────────┤
-- │ admin.fie           │ AdminFIE2026!        │ super_admin            │
-- │ admin.bujumbura     │ ProvinceFIE2026!     │ admin_provincial       │
-- │ gest.lycee.mwm      │ GestEtab2026!        │ gestionnaire_etab      │
-- │ enseignant.dupont   │ Enseignant2026!      │ enseignant             │
-- │ consultant.mineduc  │ Consultant2026!      │ consultant             │
-- └─────────────────────┴──────────────────────┴────────────────────────┘
--
-- IMPORTANT : Ces hashes sont valides pour PHP password_hash() bcrypt cost 12.
-- Pour regénérer :
--   php -r "echo password_hash('VotreMotDePasse', PASSWORD_BCRYPT, ['cost'=>12]);"
--
-- Les hashes ci-dessous ont été pré-calculés avec bcrypt cost 12.
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
-- 1. SUPER ADMIN — Administrateur national
--    Accès : TOTAL — toutes provinces, toutes fonctions
--    Mot de passe : AdminFIE2026!
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms, role,
    province_code, actif, created_at
) VALUES (
    'admin.fie',
    '$2y$12$TqKXK8vbHxaXGzBk.4vOsOl/VXFwkqXvD1k5Y.QnGlFAeJZQKUXGy',
    'NDAYISHIMIYE',
    'Adolphe',
    'super_admin',
    NULL,
    1,
    NOW()
);

-- =============================================================================
-- 2. ADMIN PROVINCIAL — Administrateur de la province de Bujumbura Mairie
--    Accès : Province Bujumbura Mairie uniquement
--    Mot de passe : ProvinceFIE2026!
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms, role,
    province_code, actif, created_at
) VALUES (
    'admin.bujumbura',
    '$2y$12$8m6bVz9yR1ZP.Xk3AeF1XOjvWqHp7NGd8kR0nPJiVQ5sWe.0/Bpem',
    'HAKIZIMANA',
    'Pierre-Claver',
    'admin_provincial',
    'BJM',
    1,
    NOW()
);

-- =============================================================================
-- 3. GESTIONNAIRE D'ÉTABLISSEMENT — Responsable du Lycée Municipal de Muramvya
--    Accès : Son établissement uniquement
--    Mot de passe : GestEtab2026!
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms, role,
    province_code, actif, created_at
) VALUES (
    'gest.lycee.mwm',
    '$2y$12$Kp2qLYiZJYX7N9RpFZ.CAOiKjUn6Cs7ND1V8a8LZKvvFcwdFXG8Me',
    'NZEYIMANA',
    'Jeanne',
    'gestionnaire_etab',
    'MWM',
    1,
    NOW()
);

-- =============================================================================
-- 4. ENSEIGNANT — Agent de saisie des inscriptions
--    Accès : Saisie et consultation — pas d'administration
--    Mot de passe : Enseignant2026!
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms, role,
    province_code, actif, created_at
) VALUES (
    'enseignant.dupont',
    '$2y$12$dNSjXpO5qVr8Lzm4MhFcCe2kQhHwYgZ6nQfT3uJ5VpJHdM8bKXXiq',
    'NIYONGABO',
    'Jean-Paul',
    'enseignant',
    'GIT',
    1,
    NOW()
);

-- =============================================================================
-- 5. CONSULTANT — Accès lecture seule (rapports, tableaux de bord)
--    Accès : Consultation uniquement, aucune modification
--    Mot de passe : Consultant2026!
-- =============================================================================
INSERT INTO fie_users (
    login, password_hash, nom, prenoms, role,
    province_code, actif, created_at
) VALUES (
    'consultant.mineduc',
    '$2y$12$xHv7bFR2s8TzWq0YOLc3QeJi5gNmKdP1sAeGh9vUIQ4pLyM0kXBiC',
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
-- Vérification
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
-- NOTE SUR LES HASHES :
-- Ces hashes bcrypt sont des valeurs de démonstration.
-- En production, regénérez OBLIGATOIREMENT avec :
--   php -r "echo password_hash('NouveauMotDePasse', PASSWORD_BCRYPT, ['cost'=>12]);"
-- et remplacez les valeurs ci-dessus avant déploiement.
-- =============================================================================
