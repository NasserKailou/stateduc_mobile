-- =============================================================================
-- SCRIPT DE MIGRATION DES REGLES DE COHERENCE
-- StatEduc Burundi - dico_DB (SQL Server 2012)
--
-- Auteur  : kailounasser@gmail.com - Abdoul Nasser Kailou
-- Session : 31 (2026-06-26)
--
-- OBJECTIF :
--   Copier l'ensemble des règles de cohérence d'un thème SOURCE vers un thème
--   DESTINATION, en respectant les séquences ID pour éviter les doublons.
--
-- TABLES CONCERNEES :
--   1. DICO_REGLE_THEME       : Règles propres à un thème (72 pour theme 90)
--      - ID_REGLE_THEME  (PK)
--      - ID_THEME        → clé de jointure avec DICO_THEME
--      - SQL_REGLE_THEME : requête SQL de la règle
--      - ORDRE_REGLE_THEME : ordre d'exécution
--      - LIBELLE_REGLE, MSG_ERREUR_REGLE, etc.
--
--   2. DICO_REGLE_THEME_ASSOC : Associations entre règles (52 pour theme 90)
--      - ID_ASSOC_REG_THM (PK)
--      - ID_REGLE_THEME      → règle principale (dont ID_THEME = source)
--      - ID_REGLE_THEME_ASSOC → règle associée (peut pointer vers autre thème)
--      - CRITERE, ACTIVER_CTRL, etc.
--
-- PRINCIPE :
--   - Pour chaque règle de DICO_REGLE_THEME avec ID_THEME = @ID_SOURCE,
--     insérer une copie avec le nouvel ID_THEME = @ID_DEST.
--   - Conserver une table de correspondance old_id → new_id pour mettre à
--     jour DICO_REGLE_THEME_ASSOC en cohérence.
--   - Les nouveaux ID utilisent MAX(ID_REGLE_THEME) + rang pour garantir
--     l'unicité sans dépendre d'une séquence ou d'un identity.
--
-- USAGE :
--   Remplacer @ID_SOURCE = 90 et @ID_DEST = 900 selon votre besoin,
--   puis exécuter dans SQL Server Management Studio sur la base dico_DB.
--
-- PRECAUTIONS :
--   - Exécuter d'abord le bloc VERIFICATION (SELECT) avant tout INSERT.
--   - L'ensemble est wrappé dans une TRANSACTION. En cas d'erreur,
--     tout est annulé (ROLLBACK).
--   - Tester sur une copie de la base avant production.
-- =============================================================================

-- ============================================================
-- PARAMETRES : modifier uniquement ces deux valeurs
-- ============================================================
DECLARE @ID_SOURCE  INT = 90;    -- ID du thème source  (ex: 90)
DECLARE @ID_DEST    INT = 900;   -- ID du thème destination (ex: 900)

-- ============================================================
-- BLOC 1 : VERIFICATION PREALABLE (exécuter seul en premier)
-- ============================================================
PRINT '===== VERIFICATION PREALABLE =====';

-- 1a. Vérifier que le thème source existe
SELECT 'THEME SOURCE' AS info, ID AS id_theme, CODE, LIBELLE
FROM   DICO_THEME
WHERE  ID = @ID_SOURCE;

-- 1b. Vérifier que le thème destination existe
SELECT 'THEME DEST' AS info, ID AS id_theme, CODE, LIBELLE
FROM   DICO_THEME
WHERE  ID = @ID_DEST;

-- 1c. Compter les règles source
SELECT 'REGLES SOURCE' AS info,
       COUNT(*) AS nb_regles_theme
FROM   DICO_REGLE_THEME
WHERE  ID_THEME = @ID_SOURCE;

-- 1d. Compter les associations source
SELECT 'ASSOC SOURCE' AS info,
       COUNT(*) AS nb_assoc
FROM   DICO_REGLE_THEME_ASSOC AS A
JOIN   DICO_REGLE_THEME        AS R ON A.ID_REGLE_THEME = R.ID_REGLE_THEME
WHERE  R.ID_THEME = @ID_SOURCE;

-- 1e. Vérifier qu'il n'existe pas déjà des règles pour le thème destination
SELECT 'REGLES DEST EXISTANTES' AS info,
       COUNT(*) AS nb_existants
FROM   DICO_REGLE_THEME
WHERE  ID_THEME = @ID_DEST;

-- 1f. Voir le prochain ID disponible
SELECT 'NEXT_ID_REGLE_THEME' AS info,
       MAX(ID_REGLE_THEME) AS current_max,
       MAX(ID_REGLE_THEME) + 1 AS next_available
FROM   DICO_REGLE_THEME;

SELECT 'NEXT_ID_ASSOC' AS info,
       MAX(ID_ASSOC_REG_THM) AS current_max,
       MAX(ID_ASSOC_REG_THM) + 1 AS next_available
FROM   DICO_REGLE_THEME_ASSOC;

-- ============================================================
-- BLOC 2 : MIGRATION (exécuter après vérification OK)
-- ============================================================
-- Décommenter et exécuter ce bloc UNIQUEMENT après validation du bloc 1

/*
BEGIN TRANSACTION migration_regles;

BEGIN TRY

    PRINT '===== DEBUT MIGRATION =====';
    PRINT CONCAT('Source : ID_THEME = ', @ID_SOURCE, ' → Destination : ID_THEME = ', @ID_DEST);

    -- ── Table temporaire de correspondance old_id → new_id ────────────────
    CREATE TABLE #map_regles (
        old_id_regle  INT NOT NULL,
        new_id_regle  INT NOT NULL
    );

    -- ── Calcul du premier ID disponible ───────────────────────────────────
    DECLARE @base_id_regle INT;
    SELECT @base_id_regle = MAX(ID_REGLE_THEME) FROM DICO_REGLE_THEME;
    -- +1 pour commencer après le dernier existant (avec marge de sécurité +10)
    SET @base_id_regle = @base_id_regle + 10;

    DECLARE @base_id_assoc INT;
    SELECT @base_id_assoc = MAX(ID_ASSOC_REG_THM) FROM DICO_REGLE_THEME_ASSOC;
    SET @base_id_assoc = @base_id_assoc + 10;

    -- ── ETAPE 1 : Copie de DICO_REGLE_THEME ───────────────────────────────
    -- Insertion avec nouveaux IDs séquentiels à partir de @base_id_regle
    -- On utilise ROW_NUMBER() pour générer les nouveaux IDs déterministement
    DECLARE @regles_source TABLE (
        rank_insert      INT,
        old_id_regle     INT,
        new_id_regle     INT,
        id_theme_dest    INT,
        sql_regle        NVARCHAR(MAX),
        ordre_regle      INT,
        libelle_regle    NVARCHAR(500),
        msg_erreur       NVARCHAR(500),
        activer_ctrl     INT,
        type_ctrl        NVARCHAR(50),
        sens_ctrl        NVARCHAR(50)
    );

    INSERT INTO @regles_source
        (rank_insert, old_id_regle, new_id_regle, id_theme_dest,
         sql_regle, ordre_regle, libelle_regle, msg_erreur,
         activer_ctrl, type_ctrl, sens_ctrl)
    SELECT
        ROW_NUMBER() OVER (ORDER BY ORDRE_REGLE_THEME, ID_REGLE_THEME) AS rank_insert,
        ID_REGLE_THEME                                                   AS old_id_regle,
        @base_id_regle
          + ROW_NUMBER() OVER (ORDER BY ORDRE_REGLE_THEME, ID_REGLE_THEME) - 1
                                                                         AS new_id_regle,
        @ID_DEST                                                         AS id_theme_dest,
        SQL_REGLE_THEME,
        ORDRE_REGLE_THEME,
        LIBELLE_REGLE_THEME,
        MSG_ERREUR_REGLE_THEME,
        ACTIVER_CTRL,
        TYPE_CTRL,
        SENS_CTRL
    FROM DICO_REGLE_THEME
    WHERE ID_THEME = @ID_SOURCE;

    -- Insérer dans DICO_REGLE_THEME
    INSERT INTO DICO_REGLE_THEME
        (ID_REGLE_THEME, ID_THEME, SQL_REGLE_THEME, ORDRE_REGLE_THEME,
         LIBELLE_REGLE_THEME, MSG_ERREUR_REGLE_THEME, ACTIVER_CTRL,
         TYPE_CTRL, SENS_CTRL)
    SELECT
        new_id_regle, id_theme_dest, sql_regle, ordre_regle,
        libelle_regle, msg_erreur, activer_ctrl,
        type_ctrl, sens_ctrl
    FROM @regles_source;

    -- Alimenter la table de correspondance
    INSERT INTO #map_regles (old_id_regle, new_id_regle)
    SELECT old_id_regle, new_id_regle FROM @regles_source;

    DECLARE @nb_regles_inserees INT = @@ROWCOUNT;
    PRINT CONCAT('DICO_REGLE_THEME : ', @nb_regles_inserees, ' lignes insérées');

    -- ── ETAPE 2 : Copie de DICO_REGLE_THEME_ASSOC ────────────────────────
    -- Seules les associations dont la règle principale appartient au thème source
    -- sont copiées. La règle associée (ID_REGLE_THEME_ASSOC) conserve son ID
    -- original (elle peut appartenir à un autre thème).
    DECLARE @assoc_source TABLE (
        rank_insert          INT,
        old_id_assoc         INT,
        new_id_assoc         INT,
        new_id_regle_theme   INT,   -- nouveau ID de la règle principale (thème dest)
        id_regle_theme_assoc INT,   -- ID de la règle associée (inchangé)
        critere              NVARCHAR(MAX),
        activer_ctrl         INT
    );

    INSERT INTO @assoc_source
        (rank_insert, old_id_assoc, new_id_assoc,
         new_id_regle_theme, id_regle_theme_assoc,
         critere, activer_ctrl)
    SELECT
        ROW_NUMBER() OVER (ORDER BY A.ID_ASSOC_REG_THM)    AS rank_insert,
        A.ID_ASSOC_REG_THM                                 AS old_id_assoc,
        @base_id_assoc
          + ROW_NUMBER() OVER (ORDER BY A.ID_ASSOC_REG_THM) - 1
                                                           AS new_id_assoc,
        M.new_id_regle                                     AS new_id_regle_theme,
        A.ID_REGLE_THEME_ASSOC                             AS id_regle_theme_assoc,
        A.CRITERE,
        A.ACTIVER_CTRL
    FROM   DICO_REGLE_THEME_ASSOC AS A
    JOIN   #map_regles              AS M ON A.ID_REGLE_THEME = M.old_id_regle;

    INSERT INTO DICO_REGLE_THEME_ASSOC
        (ID_ASSOC_REG_THM, ID_REGLE_THEME, ID_REGLE_THEME_ASSOC,
         CRITERE, ACTIVER_CTRL)
    SELECT
        new_id_assoc, new_id_regle_theme, id_regle_theme_assoc,
        critere, activer_ctrl
    FROM @assoc_source;

    DECLARE @nb_assoc_inserees INT = @@ROWCOUNT;
    PRINT CONCAT('DICO_REGLE_THEME_ASSOC : ', @nb_assoc_inserees, ' lignes insérées');

    -- ── ETAPE 3 : Vérification post-insertion ─────────────────────────────
    DECLARE @check_regles INT;
    SELECT @check_regles = COUNT(*) FROM DICO_REGLE_THEME WHERE ID_THEME = @ID_DEST;
    PRINT CONCAT('Vérification - Règles pour thème dest ', @ID_DEST, ' : ', @check_regles);

    DECLARE @check_assoc INT;
    SELECT @check_assoc = COUNT(*)
    FROM   DICO_REGLE_THEME_ASSOC AS A
    JOIN   DICO_REGLE_THEME        AS R ON A.ID_REGLE_THEME = R.ID_REGLE_THEME
    WHERE  R.ID_THEME = @ID_DEST;
    PRINT CONCAT('Vérification - Associations pour thème dest ', @ID_DEST, ' : ', @check_assoc);

    DROP TABLE #map_regles;

    PRINT '===== MIGRATION TERMINEE AVEC SUCCES =====';
    COMMIT TRANSACTION migration_regles;

END TRY
BEGIN CATCH
    ROLLBACK TRANSACTION migration_regles;
    IF OBJECT_ID('tempdb..#map_regles') IS NOT NULL DROP TABLE #map_regles;
    PRINT '===== ERREUR - TRANSACTION ANNULEE =====';
    PRINT ERROR_MESSAGE();
    THROW;
END CATCH;
*/

-- ============================================================
-- BLOC 3 : ROLLBACK D'URGENCE (si migration à annuler manuellement)
-- ============================================================
-- Décommenter UNIQUEMENT si vous devez annuler une migration déjà commitée

/*
DECLARE @ID_DEST_ROLLBACK INT = 900;

BEGIN TRANSACTION rollback_migration;
    -- Supprimer les associations des règles du thème destination
    DELETE FROM DICO_REGLE_THEME_ASSOC
    WHERE ID_REGLE_THEME IN (
        SELECT ID_REGLE_THEME FROM DICO_REGLE_THEME WHERE ID_THEME = @ID_DEST_ROLLBACK
    );

    -- Supprimer les règles du thème destination
    DELETE FROM DICO_REGLE_THEME
    WHERE ID_THEME = @ID_DEST_ROLLBACK;

    PRINT CONCAT('Rollback effectué pour ID_THEME = ', @ID_DEST_ROLLBACK);
COMMIT TRANSACTION rollback_migration;
*/
