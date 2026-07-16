-- =============================================================================
-- alter_password_field_sqlserver.sql
-- StatEduc Burundi -- Session 37b
-- =============================================================================
-- PROBLEME CRITIQUE : Collation SQL Server CI (Case Insensitive) + bcrypt
--
-- Un hash bcrypt genere par PHP password_hash($pwd, PASSWORD_BCRYPT) contient
-- des lettres majuscules ET minuscules SIGNIFICATIVES (base64 modifie), ex :
--   $2y$12$BKWYlzyZuR5GrapX6c2ApuoxHONZ6GEGANd3ZA3DmaDe76LGYVGV2
--                ^^^  ^^^                                          ^
--
-- Sur SQL Server avec une collation Case Insensitive (CI) comme :
--   - French_CI_AS  (Burundi / France)
--   - Latin1_General_CI_AS
--   - SQL_Latin1_General_CP1_CI_AS  (defaut SQL Server)
--
-- Le champ VARCHAR(100) stocke le hash correctement, MAIS quand SQL Server
-- retourne la valeur via certains drivers ODBC/native, la casse peut etre
-- normalisee selon la collation du serveur/base/champ.
--
-- Resultat : password_verify($input, $hash_retourne) retourne FALSE meme si
-- le mot de passe est correct => HTTP 401 permanent sur data_camp.php.
--
-- DOUBLE SOLUTION implementee en session 37b :
--
-- SOLUTION 1 (code PHP - deja appliquee) :
--   Les SELECT dans valide_user_ws() et infos_user_ws() utilisent maintenant :
--   CONVERT(VARCHAR(100), PASSWORD) COLLATE Latin1_General_CS_AS AS PASSWORD
--   Ce COLLATE force la lecture en mode case-sensitive au niveau de la requete,
--   independamment de la collation du serveur/base/champ.
--
-- SOLUTION 2 (base de donnees - ce script) :
--   Modifier la collation du champ PASSWORD au niveau de la table
--   pour que le stockage ET la lecture soient toujours case-sensitive.
--   C'est la solution definitive et la plus propre.
--
-- RECOMMANDATION : Appliquer les DEUX solutions (defense en profondeur).
-- =============================================================================

-- =============================================================================
-- ETAPE 0 : Diagnostic - verifier la collation actuelle
-- =============================================================================

-- Collation du serveur SQL Server
SELECT SERVERPROPERTY('Collation') AS CollationServeur;

-- Collation de la base de donnees courante
SELECT DATABASEPROPERTYEX(DB_NAME(), 'Collation') AS CollationBase;

-- Collation et type actuel du champ PASSWORD dans ADMIN_USERS
SELECT
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_MAXIMUM_LENGTH,
    COLLATION_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'ADMIN_USERS'
  AND COLUMN_NAME = 'PASSWORD';

-- Verification du hash stocke (longueur et debut)
SELECT
    NOM_USER,
    LEN(PASSWORD) AS len_pwd,
    LEFT(PASSWORD, 4) AS debut_hash,
    CASE
        WHEN LEN(PASSWORD) = 60 AND LEFT(PASSWORD,4) IN ('$2y$','$2a$') THEN 'bcrypt OK'
        WHEN LEN(PASSWORD) = 32 THEN 'MD5 legacy'
        ELSE 'FORMAT INCONNU'
    END AS etat
FROM ADMIN_USERS
ORDER BY NOM_USER;

-- =============================================================================
-- ETAPE 1 : Modifier le champ PASSWORD en VARCHAR(100) COLLATE CS_AS
-- Latin1_General_CS_AS = Case Sensitive, Accent Sensitive
-- Compatible avec bcrypt ($2y$, base64 modifie, case-sensitive)
-- =============================================================================

-- ATTENTION : Si le champ a des contraintes (DEFAULT, CHECK, FK),
-- les supprimer avant et les recreer apres.

ALTER TABLE ADMIN_USERS
    ALTER COLUMN PASSWORD VARCHAR(100)
    COLLATE Latin1_General_CS_AS
    NOT NULL;

-- Si la colonne PASSWORD est nullable (pas de NOT NULL) :
-- ALTER TABLE ADMIN_USERS
--     ALTER COLUMN PASSWORD VARCHAR(100)
--     COLLATE Latin1_General_CS_AS NULL;

-- =============================================================================
-- ETAPE 2 : Verifier le resultat
-- =============================================================================

SELECT
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_MAXIMUM_LENGTH,
    COLLATION_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'ADMIN_USERS'
  AND COLUMN_NAME = 'PASSWORD';
-- Attendu : COLLATION_NAME = Latin1_General_CS_AS

-- =============================================================================
-- ETAPE 3 : Re-injecter le hash bcrypt de l'administrateur nasser
-- (le changement de collation ne corrompt pas les donnees existantes,
--  mais si la collation CI avait deja corrompu un hash stocke, le reinjecter)
-- =============================================================================

-- Verifier d'abord si le hash est intact (60 chars, commence par $2y$) :
SELECT NOM_USER, LEN(PASSWORD), LEFT(PASSWORD,4) FROM ADMIN_USERS WHERE NOM_USER = 'nasser';

-- Si le hash est correct (LEN=60, LEFT='$2y$'), rien a faire.
-- Sinon, reinjecter :
--
-- UPDATE ADMIN_USERS
-- SET PASSWORD = '$2y$12$BKWYlzyZuR5GrapX6c2ApuoxHONZ6GEGANd3ZA3DmaDe76LGYVGV2'
-- WHERE NOM_USER = 'nasser';
--
-- (Hash correspondant au mot de passe : nasser@2026)
-- Voir aussi create_admin_nasser_bcrypt.sql

-- =============================================================================
-- ETAPE 4 : Test de verification PHP (a executer depuis un script PHP)
-- =============================================================================
--
-- <?php
-- $hash = /* resultat du SELECT PASSWORD FROM ADMIN_USERS WHERE NOM_USER='nasser' */;
-- $pwd  = 'nasser@2026'; // remplacer par le vrai mot de passe
-- var_dump(strlen($hash));        // Doit afficher int(60)
-- var_dump(substr($hash,0,4));    // Doit afficher string(4) "$2y$"
-- var_dump(password_verify($pwd, $hash)); // Doit afficher bool(true)
-- ?>

-- =============================================================================
-- NOTE : Compatibilite multi-SGBD
-- =============================================================================
-- Le correctif PHP (CONVERT ... COLLATE Latin1_General_CS_AS dans le SELECT)
-- fonctionne uniquement sur SQL Server.
-- Sur Access, MySQL, PostgreSQL, ce CONVERT/COLLATE est ignore ou provoque
-- une erreur silencieuse - mais ces SGBD n'ont pas ce probleme de collation CI.
-- La modification du champ (ALTER TABLE) est specifique SQL Server.
-- =============================================================================
