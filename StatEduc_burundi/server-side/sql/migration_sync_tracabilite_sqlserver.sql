-- =============================================================================
-- MIGRATION SYNCHRONISATION / TRACABILITE MOBILE  (branche ak_burundi)
-- Cible : base DICO (connexion $GLOBALS['conn_dico']) — SQL Server 2012+
-- Auteur : Abdoul Nasser Kailou — kailounasser@gmail.com
--
-- PRINCIPES (non-regression) :
--   * 100 % additif : aucune colonne existante modifiee, aucune table supprimee.
--   * Toutes les nouvelles colonnes sont NULLables -> l'ancien code PHP qui fait
--     INSERT INTO DATA_SAVING_LOGS (10 colonnes) continue de fonctionner.
--   * Idempotent : chaque bloc verifie l'existence avant de creer.
--
-- A EXECUTER AVANT le deploiement des fichiers PHP (data_save.php, sync_lib.php).
-- Faire une sauvegarde de la base dico au prealable.
-- Pour Access : voir migration_sync_tracabilite_access.sql
-- =============================================================================

-- ---------------------------------------------------------------------------
-- U09 : DATA_SAVING_LOGS — colonnes de tracabilite enrichie
-- ---------------------------------------------------------------------------
IF COL_LENGTH('DATA_SAVING_LOGS', 'SYNC_ID') IS NULL
    ALTER TABLE DATA_SAVING_LOGS ADD SYNC_ID VARCHAR(36) NULL;          -- U11 idempotence (UUID mobile)
IF COL_LENGTH('DATA_SAVING_LOGS', 'SYNC_HASH') IS NULL
    ALTER TABLE DATA_SAVING_LOGS ADD SYNC_HASH VARCHAR(40) NULL;        -- U22 empreinte sha1 du POST
IF COL_LENGTH('DATA_SAVING_LOGS', 'NB_INSERT') IS NULL
    ALTER TABLE DATA_SAVING_LOGS ADD NB_INSERT INT NULL;                -- U08 compteurs maj_bdd()
IF COL_LENGTH('DATA_SAVING_LOGS', 'NB_UPDATE') IS NULL
    ALTER TABLE DATA_SAVING_LOGS ADD NB_UPDATE INT NULL;
IF COL_LENGTH('DATA_SAVING_LOGS', 'NB_DELETE') IS NULL
    ALTER TABLE DATA_SAVING_LOGS ADD NB_DELETE INT NULL;
IF COL_LENGTH('DATA_SAVING_LOGS', 'NB_UNCHANGED') IS NULL
    ALTER TABLE DATA_SAVING_LOGS ADD NB_UNCHANGED INT NULL;
IF COL_LENGTH('DATA_SAVING_LOGS', 'MESSAGE_ERREUR') IS NULL
    ALTER TABLE DATA_SAVING_LOGS ADD MESSAGE_ERREUR VARCHAR(500) NULL;  -- U10 motif de rejet / erreur
IF COL_LENGTH('DATA_SAVING_LOGS', 'APP_VERSION') IS NULL
    ALTER TABLE DATA_SAVING_LOGS ADD APP_VERSION VARCHAR(20) NULL;
IF COL_LENGTH('DATA_SAVING_LOGS', 'IP_CLIENT') IS NULL
    ALTER TABLE DATA_SAVING_LOGS ADD IP_CLIENT VARCHAR(45) NULL;

-- Index de consultation (ecran suivi_sync_mobile, WS sync_status_ws)
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_DSL_SUIVI' AND object_id = OBJECT_ID('DATA_SAVING_LOGS'))
    CREATE INDEX IX_DSL_SUIVI ON DATA_SAVING_LOGS (CODE_CAMPAGNE, CODE_ANNEE, CODE_ECOLE, ID_THEME_SYSTEME);
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_DSL_SYNC_ID' AND object_id = OBJECT_ID('DATA_SAVING_LOGS'))
    CREATE INDEX IX_DSL_SYNC_ID ON DATA_SAVING_LOGS (SYNC_ID);
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_DSL_SYNC_HASH' AND object_id = OBJECT_ID('DATA_SAVING_LOGS'))
    CREATE INDEX IX_DSL_SYNC_HASH ON DATA_SAVING_LOGS (SYNC_HASH);

-- ---------------------------------------------------------------------------
-- U18 : SYNC_ETAB_STATUT — statut administratif par (annee, campagne, etab[, theme][, filtre])
--   STATUT : RECU | A_CORRIGER | VALIDE | VERROUILLE
--   ID_THEME_SYSTEME NULL  = statut applicable a tous les themes de l'etablissement
--   CODE_FILTRE NULL       = toutes periodes
-- Decision C2 : THEME_VALIDATION/TYPE_VALIDATION conservees intactes (lecture sms_sync),
--               nouvelle table dediee pour ne rien casser.
-- ---------------------------------------------------------------------------
IF OBJECT_ID('SYNC_ETAB_STATUT', 'U') IS NULL
BEGIN
    CREATE TABLE SYNC_ETAB_STATUT (
        ID_STATUT          INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        CODE_ANNEE         INT          NOT NULL,
        ID_CAMPAGNE        INT          NOT NULL,
        CODE_SECTEUR       INT          NULL,
        CODE_ETABLISSEMENT INT          NOT NULL,
        ID_THEME_SYSTEME   INT          NULL,
        CODE_FILTRE        INT          NULL,
        STATUT             VARCHAR(20)  NOT NULL,
        COMMENTAIRE        VARCHAR(1000) NULL,
        CODE_USER_ADMIN    VARCHAR(100) NULL,
        DATE_STATUT        VARCHAR(19)  NOT NULL,   -- 'YYYY/MM/DD HH:MM:SS' (meme format que DATA_SAVING_LOGS.LOG_DATE_TIME)
        ACTIF              INT          NOT NULL DEFAULT 1
    );
    CREATE INDEX IX_SES_CLE ON SYNC_ETAB_STATUT (CODE_ANNEE, ID_CAMPAGNE, CODE_ETABLISSEMENT, ID_THEME_SYSTEME, ACTIF);
END;

-- ---------------------------------------------------------------------------
-- Historique des decisions admin (journal — jamais purge automatiquement)
-- ---------------------------------------------------------------------------
IF OBJECT_ID('SYNC_ETAB_STATUT_HIST', 'U') IS NULL
BEGIN
    CREATE TABLE SYNC_ETAB_STATUT_HIST (
        ID_HIST            INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        CODE_ANNEE         INT          NOT NULL,
        ID_CAMPAGNE        INT          NOT NULL,
        CODE_ETABLISSEMENT INT          NOT NULL,
        ID_THEME_SYSTEME   INT          NULL,
        CODE_FILTRE        INT          NULL,
        ANCIEN_STATUT      VARCHAR(20)  NULL,
        NOUVEAU_STATUT     VARCHAR(20)  NOT NULL,
        COMMENTAIRE        VARCHAR(1000) NULL,
        CODE_USER_ADMIN    VARCHAR(100) NULL,
        DATE_HIST          VARCHAR(19)  NOT NULL
    );
END;
