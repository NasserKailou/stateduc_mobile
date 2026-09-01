-- =============================================================================
-- Script : create_admin_nasser_bcrypt.sql
-- Projet : StatEduc Burundi
-- Session 35 : Migration md5 -> bcrypt
--
-- Description :
--   Cree ou met a jour l'utilisateur administrateur 'nasser' avec un mot de
--   passe stocke en bcrypt (PASSWORD_BCRYPT, PHP compatible $2y$, cost=12).
--   A executer APRES avoir migre tous les mots de passe existants ou lors
--   d'une installation fraiche.
--
-- Mot de passe clair  : nasser@2026
-- Hash bcrypt ($2y$)  : $2y$12$BKWYlzyZuR5GrapX6c2ApuoxHONZ6GEGANd3ZA3DmaDe76LGYVGV2
-- Cost factor         : 12
-- Algo                : bcrypt (PHP password_hash / PASSWORD_BCRYPT)
--
-- Verification PHP :
--   password_verify('nasser@2026', '$2y$12$BKWYlzyZuR5GrapX6c2ApuoxHONZ6GEGANd3ZA3DmaDe76LGYVGV2')
--   => true
--
-- @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou
-- @modifie Session 35 - Migration securite md5 -> bcrypt
-- =============================================================================

-- -----------------------------------------------------------------------------
-- OPTION A : Insertion d'un nouvel administrateur (si l'utilisateur n'existe pas)
-- Adapter CODE_USER si necessaire (verifier le MAX existant dans ADMIN_USERS)
-- -----------------------------------------------------------------------------

INSERT INTO ADMIN_USERS
    (CODE_USER, NOM_LONG_USER, EMAIL_USER, TEL_USER, NOM_USER, PASSWORD, CODE_GROUPE, CODE_USER_PARENT)
SELECT
    (SELECT COALESCE(MAX(CODE_USER), 0) + 1 FROM ADMIN_USERS),
    'Abdoul Nasser Kailou',
    'kailounasser@gmail.com',
    '',
    'nasser',
    '$2y$12$BKWYlzyZuR5GrapX6c2ApuoxHONZ6GEGANd3ZA3DmaDe76LGYVGV2',
    1,
    0
WHERE NOT EXISTS (
    SELECT 1 FROM ADMIN_USERS WHERE NOM_USER = 'nasser'
);

-- -----------------------------------------------------------------------------
-- OPTION B : Mise a jour du mot de passe si l'utilisateur 'nasser' existe deja
-- (utile pour migrer un compte existant de md5 vers bcrypt)
-- -----------------------------------------------------------------------------

UPDATE ADMIN_USERS
SET PASSWORD = '$2y$12$BKWYlzyZuR5GrapX6c2ApuoxHONZ6GEGANd3ZA3DmaDe76LGYVGV2',
    CODE_GROUPE = 1
WHERE NOM_USER = 'nasser';

-- -----------------------------------------------------------------------------
-- VERIFICATION : lister les admins du groupe 1
-- -----------------------------------------------------------------------------

SELECT CODE_USER, NOM_USER, NOM_LONG_USER, EMAIL_USER, CODE_GROUPE
FROM ADMIN_USERS
WHERE CODE_GROUPE = 1
ORDER BY CODE_USER;

-- =============================================================================
-- NOTE IMPORTANTE : Migration des mots de passe existants
-- =============================================================================
-- Les mots de passe existants en MD5 dans la base NE FONCTIONNERONT PLUS
-- apres la migration du code vers bcrypt.
-- Pour chaque utilisateur existant :
--   1. L'administrateur doit reinitialiser le mot de passe via l'interface
--      (gestion_user.php) -> le nouveau hash sera stocke en bcrypt
--   2. OU utiliser le script de migration ci-dessous (necessite connaitre
--      les mots de passe en clair - non recommande en production)
-- =============================================================================
