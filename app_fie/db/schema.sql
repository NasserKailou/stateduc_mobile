-- =============================================================================
-- app_fie/db/schema.sql
-- Schéma MySQL complet — FIE (Fichier Informatisé des Élèves)
-- Système SIGE Burundi — v1.0.0 — 2026-08-11
--
-- Principe :
--   • Les codes de nomenclature (TYPE_MILIEU, TYPE_NIVEAU, etc.) RÉUTILISENT
--     exactement les valeurs de StatEduc SQL Server (aucune réécriture).
--   • La table `etablissements_miroir` est le cache local alimenté soit par
--     l'API StatEduc (source de vérité), soit par le script d'import Excel
--     (mode dégradé).
--   • Toutes les clés étrangères sont déclarées pour garantir l'intégrité.
--   • Chaque table sensible dispose d'une piste d'audit (created_at, updated_at,
--     created_by, updated_by).
--
-- Pour créer la base :
--   CREATE DATABASE fie_burundi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   USE fie_burundi;
--   SOURCE schema.sql;
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ─────────────────────────────────────────────────────────────────────────────
-- 1. TABLES DE RÉFÉRENCE — MIROIRS DES CODES STATEDUC
--    Ces tables reproduisent exactement les nomenclatures SQL Server afin
--    que les FK locales gardent les mêmes valeurs entières.
-- ─────────────────────────────────────────────────────────────────────────────

-- 1.1 Sous-secteurs d'enseignement (TYPE_SECTEUR_ENS dans StatEduc)
CREATE TABLE IF NOT EXISTS ref_secteur_ens (
    code_type_secteur_ens  TINYINT UNSIGNED NOT NULL,
    libelle                VARCHAR(100)     NOT NULL,
    ordre                  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    actif                  TINYINT(1)       NOT NULL DEFAULT 1,
    PRIMARY KEY (code_type_secteur_ens)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Miroir TYPE_SECTEUR_ENS StatEduc';

INSERT IGNORE INTO ref_secteur_ens VALUES
(1, 'Préscolaire',                         1, 1),
(2, 'Fondamental',                         2, 1),
(3, 'Post-Fondamental Général & Pédag.',   3, 1),
(4, 'Post-Fondamental Technique A2',       4, 1),
(5, 'Secondaire Technique A3/A4',          5, 1),
(6, 'Alphabétisation',                     6, 1),
(7, 'Supérieur',                           7, 1);

-- 1.2 Milieu géographique (1=Urbain, 2=Rural)
CREATE TABLE IF NOT EXISTS ref_type_milieu (
    code_type_milieu  TINYINT UNSIGNED NOT NULL,
    libelle           VARCHAR(50)      NOT NULL,
    PRIMARY KEY (code_type_milieu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Miroir TYPE_MILIEU StatEduc';

INSERT IGNORE INTO ref_type_milieu VALUES
(1, 'Urbain'),
(2, 'Rural'),
(255, 'Non défini');

-- 1.3 Statut organisationnel (TYPE_STATUT_ORG)
CREATE TABLE IF NOT EXISTS ref_type_statut_org (
    code  TINYINT UNSIGNED NOT NULL,
    libelle VARCHAR(100)   NOT NULL,
    PRIMARY KEY (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Miroir TYPE_STATUT_ORG StatEduc';

INSERT IGNORE INTO ref_type_statut_org VALUES
(1,  'Public'),
(2,  'Privé agréé subventionné'),
(3,  'Privé agréé non subventionné'),
(4,  'Confessionnel catholique'),
(5,  'Confessionnel protestant'),
(6,  'Communautaire'),
(7,  'Privé laïc'),
(8,  'Privé confessionnel'),
(9,  'Public communal'),
(10, 'Public provincial'),
(11, 'Militaire'),
(13, 'Enseignement intégré');

-- 1.4 Niveaux d'enseignement (CODE_TYPE_NIVEAU dans ELEVES_AGE_NIVEAU_SEXE)
--     Valeurs observées dans le thème 10602 : 4..12
CREATE TABLE IF NOT EXISTS ref_type_niveau (
    code_type_niveau  SMALLINT UNSIGNED NOT NULL,
    libelle           VARCHAR(100)      NOT NULL,
    code_secteur      TINYINT UNSIGNED  NOT NULL,
    ordre             TINYINT UNSIGNED  NOT NULL DEFAULT 0,
    PRIMARY KEY (code_type_niveau),
    KEY idx_secteur (code_secteur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Miroir TYPE_NIVEAU StatEduc';

INSERT IGNORE INTO ref_type_niveau VALUES
-- Préscolaire
(1,  'Petite section',           1, 1),
(2,  'Moyenne section',          1, 2),
(3,  'Grande section',           1, 3),
-- Fondamental (niveaux 1-9)
(4,  'Fondamental 1 (1AF)',      2, 1),
(5,  'Fondamental 2 (2AF)',      2, 2),
(6,  'Fondamental 3 (3AF)',      2, 3),
(7,  'Fondamental 4 (4AF)',      2, 4),
(8,  'Fondamental 5 (5AF)',      2, 5),
(9,  'Fondamental 6 (6AF)',      2, 6),
(10, 'Fondamental 7 (7AF)',      2, 7),
(11, 'Fondamental 8 (8AF)',      2, 8),
(12, 'Fondamental 9 (9AF)',      2, 9),
-- Post-fondamental
(13, 'Secondaire 1ère',          3, 1),
(14, 'Secondaire 2ème',          3, 2),
(15, 'Secondaire 3ème (Terminal)',3, 3),
-- Technique A2
(16, 'Technique A2 – 1ère',      4, 1),
(17, 'Technique A2 – 2ème',      4, 2),
(18, 'Technique A2 – 3ème',      4, 3),
-- Technique A3/A4
(19, 'Technique A3 – 1ère',      5, 1),
(20, 'Technique A3 – 2ème',      5, 2);

-- 1.5 Tranches d'âge (CODE_TYPE_AGE — miroir TYPE_TRANCHE_AGE StatEduc)
--     Règle de calcul : age = année_scolaire - année_naissance
--     (ou différence en années révolues à la date de rentrée)
CREATE TABLE IF NOT EXISTS ref_type_age (
    code_type_age  SMALLINT UNSIGNED NOT NULL,
    libelle        VARCHAR(50)       NOT NULL,
    age_min        TINYINT UNSIGNED  NOT NULL COMMENT 'Age minimum inclus',
    age_max        TINYINT UNSIGNED  NOT NULL COMMENT 'Age maximum inclus',
    PRIMARY KEY (code_type_age)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Miroir TYPE_TRANCHE_AGE StatEduc';

INSERT IGNORE INTO ref_type_age VALUES
(1,  'Moins de 3 ans',  0,  2),
(2,  '3 ans',           3,  3),
(3,  '4 ans',           4,  4),
(4,  '5 ans',           5,  5),
(5,  '6 ans',           6,  6),
(6,  '7 ans',           7,  7),
(7,  '8 ans',           8,  8),
(8,  '9 ans',           9,  9),
(9,  '10 ans',         10, 10),
(10, '11 ans',         11, 11),
(11, '12 ans',         12, 12),
(12, '13 ans',         13, 13),
(13, '14 ans',         14, 14),
(14, '15 ans',         15, 15),
(15, '16 ans',         16, 16),
(16, '17 ans',         17, 17),
(17, '18 ans',         18, 18),
(18, '19 ans',         19, 19),
(19, '20 ans',         20, 20),
(20, '21 ans et plus', 21, 99);

-- 1.6 Sections (CODE_TYPE_SECTION — ex : 1=Francophone, 2=Kirundi, etc.)
CREATE TABLE IF NOT EXISTS ref_type_section (
    code_type_section  SMALLINT UNSIGNED NOT NULL,
    libelle            VARCHAR(100)      NOT NULL,
    PRIMARY KEY (code_type_section)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Miroir TYPE_SECTION StatEduc';

INSERT IGNORE INTO ref_type_section VALUES
(1, 'Francophone'),
(2, 'Kirundi'),
(3, 'Anglophone'),
(4, 'Swahili'),
(5, 'Bilingue FR/EN'),
(255, 'Non défini');

-- 1.7 Années scolaires (CODE_TYPE_ANNEE)
CREATE TABLE IF NOT EXISTS ref_type_annee (
    code_type_annee  SMALLINT UNSIGNED NOT NULL,
    libelle          VARCHAR(20)       NOT NULL COMMENT 'ex: 2024-2025',
    annee_debut      SMALLINT UNSIGNED NOT NULL,
    annee_fin        SMALLINT UNSIGNED NOT NULL,
    actif            TINYINT(1)        NOT NULL DEFAULT 0,
    PRIMARY KEY (code_type_annee)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Années scolaires miroir StatEduc';

-- Années à alimenter lors du déploiement
INSERT IGNORE INTO ref_type_annee VALUES
(2024, '2024-2025', 2024, 2025, 0),
(2025, '2025-2026', 2025, 2026, 1),
(2026, '2026-2027', 2026, 2027, 0);

-- 1.8 Sexe
CREATE TABLE IF NOT EXISTS ref_sexe (
    code_sexe  CHAR(1) NOT NULL,
    libelle    VARCHAR(20) NOT NULL,
    PRIMARY KEY (code_sexe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO ref_sexe VALUES
('M', 'Masculin'),
('F', 'Féminin');

-- ─────────────────────────────────────────────────────────────────────────────
-- 2. TABLE MIROIR DES ÉTABLISSEMENTS
--    Alimentée par l'API StatEduc (source normale) ou le script d'import Excel
--    (mode dégradé). Upsert idempotent par CODE_ETABLISSEMENT.
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS etablissements_miroir (
    code_etablissement     INT           NOT NULL COMMENT 'Clé primaire StatEduc',
    nom_etablissement      VARCHAR(255)  NOT NULL,
    -- Localisation (chaîne hiérarchique)
    province               VARCHAR(100)  DEFAULT NULL,
    commune                VARCHAR(100)  DEFAULT NULL,
    zone_admin             VARCHAR(100)  DEFAULT NULL,
    colline                VARCHAR(100)  DEFAULT NULL,
    chaine_localisation    VARCHAR(500)  DEFAULT NULL COMMENT 'Province / Commune / Zone / Colline / Étab.',
    -- Codes de localisation (si disponibles via API)
    code_province          INT UNSIGNED  DEFAULT NULL,
    code_commune           INT UNSIGNED  DEFAULT NULL,
    code_zone              INT UNSIGNED  DEFAULT NULL,
    code_colline           INT UNSIGNED  DEFAULT NULL,
    -- Codes de typologie StatEduc (réutilisés sans modification)
    code_type_milieu       TINYINT UNSIGNED  DEFAULT NULL,
    code_type_statut_org   TINYINT UNSIGNED  DEFAULT NULL,
    code_type_secteur_ens  TINYINT UNSIGNED  DEFAULT NULL,
    code_type_fonction     TINYINT UNSIGNED  DEFAULT NULL,
    code_type_etablissement TINYINT UNSIGNED DEFAULT NULL,
    code_type_etat_fonct   TINYINT UNSIGNED  DEFAULT NULL,
    -- Informations complémentaires
    code_ecole_pays        VARCHAR(20)   DEFAULT NULL COMMENT 'Code administratif national',
    code_etablissement_parent INT        DEFAULT NULL,
    telephone              VARCHAR(30)   DEFAULT NULL,
    adresse_electronique   VARCHAR(200)  DEFAULT NULL,
    responsable_ecole      VARCHAR(200)  DEFAULT NULL,
    annee_creation         SMALLINT UNSIGNED DEFAULT NULL,
    -- Métadonnées de synchronisation
    source                 ENUM('api_stateduc','excel_import','manuel') NOT NULL DEFAULT 'excel_import',
    synced_at              DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    stateduc_updated_at    DATETIME      DEFAULT NULL COMMENT 'Dernière MAJ côté StatEduc',
    actif                  TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (code_etablissement),
    KEY idx_province (province),
    KEY idx_commune (commune),
    KEY idx_secteur (code_type_secteur_ens),
    KEY idx_milieu (code_type_milieu),
    KEY idx_synced (synced_at),
    FULLTEXT KEY ft_nom (nom_etablissement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Cache local du référentiel établissements StatEduc';

-- ─────────────────────────────────────────────────────────────────────────────
-- 3. JOURNAL DE SYNCHRONISATION
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS sync_log (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    source_type     ENUM('api_stateduc','excel_import') NOT NULL,
    started_at      DATETIME        NOT NULL,
    ended_at        DATETIME        DEFAULT NULL,
    status          ENUM('running','success','partial','error') NOT NULL DEFAULT 'running',
    total_records   INT UNSIGNED    NOT NULL DEFAULT 0,
    inserted        INT UNSIGNED    NOT NULL DEFAULT 0,
    updated         INT UNSIGNED    NOT NULL DEFAULT 0,
    errors          INT UNSIGNED    NOT NULL DEFAULT 0,
    last_page       SMALLINT UNSIGNED DEFAULT NULL COMMENT 'Pour reprise en cas d erreur',
    details         TEXT            DEFAULT NULL COMMENT 'JSON des erreurs éventuelles',
    triggered_by    VARCHAR(100)    DEFAULT NULL COMMENT 'login utilisateur ou cron',
    PRIMARY KEY (id),
    KEY idx_status (status),
    KEY idx_started (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Journal des synchronisations établissements';

-- ─────────────────────────────────────────────────────────────────────────────
-- 4. UTILISATEURS FIE
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS fie_users (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    login               VARCHAR(50)     NOT NULL UNIQUE,
    password_hash       VARCHAR(255)    NOT NULL COMMENT 'bcrypt via password_hash()',
    nom                 VARCHAR(100)    NOT NULL,
    prenoms             VARCHAR(150)    DEFAULT NULL,
    email               VARCHAR(200)    DEFAULT NULL UNIQUE,
    telephone           VARCHAR(30)     DEFAULT NULL,
    -- Profil et périmètre
    role                ENUM('super_admin','admin_central','admin_provincial',
                             'gestionnaire_etab','enseignant','consultant','api_client')
                        NOT NULL DEFAULT 'gestionnaire_etab',
    code_etablissement  INT             DEFAULT NULL COMMENT 'NULL = accès national',
    province_perimetre  VARCHAR(100)    DEFAULT NULL COMMENT 'NULL = toutes provinces',
    -- État
    actif               TINYINT(1)      NOT NULL DEFAULT 1,
    must_change_password TINYINT(1)     NOT NULL DEFAULT 1,
    failed_login_count  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until        DATETIME        DEFAULT NULL,
    last_login_at       DATETIME        DEFAULT NULL,
    last_login_ip       VARCHAR(45)     DEFAULT NULL,
    -- Audit
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by          INT UNSIGNED    DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_role (role),
    KEY idx_etab (code_etablissement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Utilisateurs de l application FIE';

-- Token API (pour les clients machine-to-machine)
CREATE TABLE IF NOT EXISTS fie_api_tokens (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id      INT UNSIGNED  NOT NULL,
    token_hash   VARCHAR(255)  NOT NULL UNIQUE COMMENT 'SHA-256 du token brut',
    description  VARCHAR(200)  DEFAULT NULL,
    scopes       SET('read_eleves','write_eleves','read_etabs','sync_etabs','aggregates')
                 NOT NULL DEFAULT 'read_eleves',
    expires_at   DATETIME      DEFAULT NULL,
    last_used_at DATETIME      DEFAULT NULL,
    actif        TINYINT(1)    NOT NULL DEFAULT 1,
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES fie_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────────────
-- 5. TABLE ÉLÈVE
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS eleves (
    id                   BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    -- Identifiant Unique de l'Élève (IUE)
    iue                  VARCHAR(25)      NOT NULL UNIQUE
                         COMMENT 'Format : BI-SSSS-AAAA-NNNNNN-CC',
    -- État civil
    nom                  VARCHAR(100)     NOT NULL,
    prenoms              VARCHAR(150)     NOT NULL,
    sexe                 CHAR(1)          NOT NULL,
    date_naissance       DATE             NOT NULL,
    lieu_naissance       VARCHAR(150)     DEFAULT NULL,
    province_naissance   VARCHAR(100)     DEFAULT NULL,
    nationalite          CHAR(3)          NOT NULL DEFAULT 'BDI' COMMENT 'Code ISO 3166-1 alpha-3',
    -- Documents d'identité
    numero_acte_naissance VARCHAR(50)     DEFAULT NULL,
    date_acte_naissance  DATE             DEFAULT NULL,
    commune_acte         VARCHAR(100)     DEFAULT NULL,
    -- Tuteur / parent
    nom_pere             VARCHAR(150)     DEFAULT NULL,
    nom_mere             VARCHAR(150)     DEFAULT NULL,
    nom_tuteur           VARCHAR(150)     DEFAULT NULL,
    telephone_tuteur     VARCHAR(30)      DEFAULT NULL,
    adresse_tuteur       VARCHAR(300)     DEFAULT NULL,
    -- Photo
    photo_path           VARCHAR(255)     DEFAULT NULL,
    -- Statut
    statut               ENUM('actif','transféré','sorti','décédé','abandon')
                         NOT NULL DEFAULT 'actif',
    -- Détection doublon
    doublon_suspect      TINYINT(1)       NOT NULL DEFAULT 0,
    doublon_iue_ref      VARCHAR(25)      DEFAULT NULL COMMENT 'IUE du doublon potentiel',
    -- Audit
    created_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by           INT UNSIGNED     DEFAULT NULL,
    updated_by           INT UNSIGNED     DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_nom (nom, prenoms),
    KEY idx_ddn (date_naissance),
    KEY idx_sexe (sexe),
    KEY idx_statut (statut),
    KEY idx_doublon (doublon_suspect),
    CONSTRAINT chk_sexe CHECK (sexe IN ('M','F'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Fichier Informatisé des Élèves — table centrale';

-- ─────────────────────────────────────────────────────────────────────────────
-- 6. SÉQUENCE IUE (compteurs par secteur + année)
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS iue_sequences (
    code_type_secteur_ens  TINYINT UNSIGNED  NOT NULL,
    code_type_annee        SMALLINT UNSIGNED NOT NULL,
    last_seq               INT UNSIGNED      NOT NULL DEFAULT 0,
    PRIMARY KEY (code_type_secteur_ens, code_type_annee)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Séquences pour génération IUE';

-- ─────────────────────────────────────────────────────────────────────────────
-- 7. TABLE INSCRIPTION
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS inscriptions (
    id                   BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    eleve_id             BIGINT UNSIGNED  NOT NULL,
    -- Contexte scolaire
    code_etablissement   INT              NOT NULL,
    code_type_secteur_ens TINYINT UNSIGNED NOT NULL,
    code_type_annee      SMALLINT UNSIGNED NOT NULL,
    code_type_niveau     SMALLINT UNSIGNED NOT NULL,
    code_type_section    SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    numero_classe        VARCHAR(20)      DEFAULT NULL COMMENT 'ex: 3AF-A',
    -- Dates
    date_inscription     DATE             NOT NULL,
    date_debut_annee     DATE             DEFAULT NULL,
    -- Statut de l'inscription
    statut               ENUM('inscrit','promu','redoublant','transféré_sortant',
                              'transféré_entrant','abandon','exclu','diplômé')
                         NOT NULL DEFAULT 'inscrit',
    -- Provenance (si transfert ou reprise)
    code_etab_precedent  INT              DEFAULT NULL,
    annee_precedente     SMALLINT UNSIGNED DEFAULT NULL,
    motif_arrivee        VARCHAR(200)     DEFAULT NULL,
    -- Frais et bourses
    frais_inscription    DECIMAL(10,2)    DEFAULT NULL,
    bourse               TINYINT(1)       NOT NULL DEFAULT 0,
    -- Numéro de matricule interne à l'établissement
    matricule_etab       VARCHAR(30)      DEFAULT NULL,
    -- Audit
    created_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by           INT UNSIGNED     DEFAULT NULL,
    updated_by           INT UNSIGNED     DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_eleve_annee_etab (eleve_id, code_type_annee, code_etablissement),
    FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE RESTRICT,
    FOREIGN KEY (code_etablissement) REFERENCES etablissements_miroir(code_etablissement),
    KEY idx_etab_annee (code_etablissement, code_type_annee),
    KEY idx_niveau (code_type_niveau),
    KEY idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Inscriptions annuelles des élèves';

-- ─────────────────────────────────────────────────────────────────────────────
-- 8. AGRÉGATS ELEVES_AGE_NIVEAU_SEXE (cache local pré-calculé)
--    Miroir de la table SQL Server, calculé automatiquement depuis inscriptions.
--    Envoyé vers StatEduc via l'endpoint REST d'interopérabilité.
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS agregats_eleves_age_niveau_sexe (
    code_etablissement   INT              NOT NULL,
    code_type_annee      SMALLINT UNSIGNED NOT NULL,
    code_type_niveau     SMALLINT UNSIGNED NOT NULL,
    code_type_age        SMALLINT UNSIGNED NOT NULL,
    code_type_section    SMALLINT UNSIGNED NOT NULL,
    filles_age_niveau    SMALLINT         DEFAULT NULL,
    total_age_niveau     SMALLINT         DEFAULT NULL,
    estimation           INT              DEFAULT NULL,
    code_type_etat_fonct SMALLINT UNSIGNED DEFAULT NULL,
    calculated_at        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    synced_to_stateduc   TINYINT(1)       NOT NULL DEFAULT 0,
    synced_at            DATETIME         DEFAULT NULL,
    PRIMARY KEY (code_etablissement, code_type_annee, code_type_niveau,
                 code_type_age, code_type_section),
    KEY idx_etab_annee (code_etablissement, code_type_annee),
    KEY idx_sync (synced_to_stateduc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Cache agrégats à pousser vers ELEVES_AGE_NIVEAU_SEXE StatEduc';

-- ─────────────────────────────────────────────────────────────────────────────
-- 9. MOUVEMENTS D'ÉLÈVES (transferts, abandons, réintégrations)
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS mouvements (
    id                   BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    eleve_id             BIGINT UNSIGNED  NOT NULL,
    inscription_id       BIGINT UNSIGNED  DEFAULT NULL,
    type_mouvement       ENUM('transfert_sortant','transfert_entrant','abandon',
                              'réintégration','décès','exclusion','promotion',
                              'redoublement','diplômé') NOT NULL,
    date_mouvement       DATE             NOT NULL,
    code_etab_origine    INT              DEFAULT NULL,
    code_etab_destination INT             DEFAULT NULL,
    motif                VARCHAR(300)     DEFAULT NULL,
    document_ref         VARCHAR(100)     DEFAULT NULL COMMENT 'N° de la lettre/acte',
    statut_validation    ENUM('en_attente','validé','rejeté') NOT NULL DEFAULT 'en_attente',
    validé_par           INT UNSIGNED     DEFAULT NULL,
    validé_le            DATETIME         DEFAULT NULL,
    -- Audit
    created_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by           INT UNSIGNED     DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE RESTRICT,
    KEY idx_eleve (eleve_id),
    KEY idx_date (date_mouvement),
    KEY idx_type (type_mouvement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Mouvements des élèves';

-- ─────────────────────────────────────────────────────────────────────────────
-- 10. EXAMENS ET CONCOURS
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS examens (
    id                   INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    libelle              VARCHAR(200)     NOT NULL,
    type_examen          ENUM('fin_cycle','concours','certification','autre') NOT NULL,
    code_type_secteur_ens TINYINT UNSIGNED NOT NULL,
    code_type_annee      SMALLINT UNSIGNED NOT NULL,
    date_session         DATE             DEFAULT NULL,
    actif                TINYINT(1)       NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Référentiel des examens/concours';

CREATE TABLE IF NOT EXISTS resultats_examen (
    id                   BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    eleve_id             BIGINT UNSIGNED  NOT NULL,
    examen_id            INT UNSIGNED     NOT NULL,
    inscription_id       BIGINT UNSIGNED  DEFAULT NULL,
    numero_candidat      VARCHAR(30)      DEFAULT NULL,
    note_totale          DECIMAL(6,2)     DEFAULT NULL,
    mention              VARCHAR(50)      DEFAULT NULL,
    admis                TINYINT(1)       DEFAULT NULL COMMENT '1=admis, 0=non admis',
    rang                 INT UNSIGNED     DEFAULT NULL,
    iue_conserve         TINYINT(1)       NOT NULL DEFAULT 1
                         COMMENT 'L IUE persiste après l examen',
    -- Audit
    created_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_eleve_examen (eleve_id, examen_id),
    FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE RESTRICT,
    FOREIGN KEY (examen_id) REFERENCES examens(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Résultats d examens';

-- ─────────────────────────────────────────────────────────────────────────────
-- 11. PISTE D'AUDIT GLOBALE
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS audit_log (
    id           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    table_name   VARCHAR(60)      NOT NULL,
    record_id    VARCHAR(30)      NOT NULL COMMENT 'PK de l enregistrement affecté',
    action       ENUM('INSERT','UPDATE','DELETE','LOGIN','LOGOUT','SYNC','EXPORT')
                 NOT NULL,
    old_values   JSON             DEFAULT NULL,
    new_values   JSON             DEFAULT NULL,
    user_id      INT UNSIGNED     DEFAULT NULL,
    user_login   VARCHAR(50)      DEFAULT NULL,
    ip_address   VARCHAR(45)      DEFAULT NULL,
    user_agent   VARCHAR(255)     DEFAULT NULL,
    created_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_table_record (table_name, record_id),
    KEY idx_user (user_id),
    KEY idx_action (action),
    KEY idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Piste d audit — loi 1/03-2026 protection données';

-- ─────────────────────────────────────────────────────────────────────────────
-- 12. COMPTE ADMINISTRATEUR PAR DÉFAUT
--     Mot de passe : 'AdminFIE2026!' (à changer immédiatement en production)
-- ─────────────────────────────────────────────────────────────────────────────

INSERT IGNORE INTO fie_users
    (id, login, password_hash, nom, prenoms, email, role, actif, must_change_password)
VALUES (
    1,
    'admin',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- AdminFIE2026!
    'Administrateur',
    'FIE',
    'admin@fie.bi',
    'super_admin',
    1,
    1
);

SET FOREIGN_KEY_CHECKS = 1;

-- FIN DU SCHÉMA
