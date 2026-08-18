-- ============================================================================
-- Migration 002 — Nouveaux modules FIE
-- Auteur   : AI Dev Session 3 — 2026-08-18
-- Description :
--   - Nouveaux rôles utilisateurs (directeur_ecole, enseignant, bibliothecaire)
--   - Mini-bibliothèque publique (documents classés par thématique)
--   - Suivi pédagogique (promotion/redoublement + transferts en cours d'année)
--   - Historique élève (journal complet de toutes les actions)
--   - Colonnes supplémentaires sur users (ecole_code, classe_id)
-- ============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- ============================================================================
-- 1. MISE À JOUR TABLE users — nouveaux rôles et champs
-- ============================================================================
ALTER TABLE users
    MODIFY COLUMN role ENUM(
        'super_admin','admin_central',
        'directeur_ecole','enseignant','bibliothecaire'
    ) NOT NULL DEFAULT 'enseignant';

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS ecole_code VARCHAR(20) NULL COMMENT 'Code établissement (directeur/enseignant)',
    ADD COLUMN IF NOT EXISTS classe_id INT UNSIGNED NULL COMMENT 'ID classe de l\'enseignant',
    ADD COLUMN IF NOT EXISTS nom_complet VARCHAR(255) NULL COMMENT 'Nom et prénom de l\'utilisateur';

-- ============================================================================
-- 2. TABLE classes — classes d'établissement
-- ============================================================================
CREATE TABLE IF NOT EXISTS classes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ecole_code      VARCHAR(20)  NOT NULL COMMENT 'Code établissement StatEduc',
    annee_scolaire  VARCHAR(12)  NOT NULL COMMENT 'Ex: 2025-2026',
    niveau          VARCHAR(50)  NOT NULL COMMENT 'Ex: CP1, CP2, CE1... 6ème',
    nom_classe      VARCHAR(100) NOT NULL COMMENT 'Nom complet ex: CP1-A',
    enseignant_id   INT UNSIGNED NULL COMMENT 'FK vers users.id',
    effectif        SMALLINT UNSIGNED DEFAULT 0,
    actif           TINYINT(1) NOT NULL DEFAULT 1,
    cree_le         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_ecole_annee (ecole_code, annee_scolaire),
    INDEX idx_enseignant  (enseignant_id),
    FOREIGN KEY (enseignant_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Classes d\'un établissement par année scolaire';

-- ============================================================================
-- 3. TABLE suivi_pedagogique — Résultats fin d'année par élève/classe
-- ============================================================================
CREATE TABLE IF NOT EXISTS suivi_pedagogique (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    eleve_iue       VARCHAR(20)  NOT NULL COMMENT 'IUE de l\'élève',
    classe_id       INT UNSIGNED NOT NULL COMMENT 'FK vers classes.id',
    annee_scolaire  VARCHAR(12)  NOT NULL,
    ecole_code      VARCHAR(20)  NOT NULL,

    -- Décision de fin d'année
    decision        ENUM('en_attente','passe','redouble','abandonne') NOT NULL DEFAULT 'en_attente'
                    COMMENT 'Décision fin d\'année',
    decision_date   DATE NULL COMMENT 'Date de la décision',

    -- Notes et observations
    moyenne_annuelle DECIMAL(5,2) NULL,
    observations     TEXT NULL,

    -- Validation
    valide_par      INT UNSIGNED NULL COMMENT 'FK users.id (directeur ou admin)',
    valide_le       DATETIME NULL,

    -- Méta
    cree_par        INT UNSIGNED NOT NULL COMMENT 'FK users.id (enseignant)',
    cree_le         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_eleve_classe_annee (eleve_iue, classe_id, annee_scolaire),
    INDEX idx_ecole_annee (ecole_code, annee_scolaire),
    INDEX idx_decision    (decision),
    FOREIGN KEY (cree_par) REFERENCES users(id),
    FOREIGN KEY (valide_par) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Suivi pédagogique fin d\'année par élève';

-- ============================================================================
-- 4. TABLE transferts_scolaires — Transferts en cours d'année
-- ============================================================================
CREATE TABLE IF NOT EXISTS transferts_scolaires (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    eleve_iue           VARCHAR(20)  NOT NULL,
    annee_scolaire      VARCHAR(12)  NOT NULL,

    -- Établissement source
    ecole_source_code   VARCHAR(20)  NOT NULL,
    classe_source_id    INT UNSIGNED NULL,
    motif_depart        VARCHAR(255) NULL,

    -- Établissement destination
    ecole_dest_code     VARCHAR(20)  NULL COMMENT 'NULL si inconnu à la demande',
    classe_dest_id      INT UNSIGNED NULL,

    -- Statut du transfert
    statut              ENUM('demande','approuve','rejete','execute') NOT NULL DEFAULT 'demande',
    date_demande        DATE NOT NULL DEFAULT (CURDATE()),
    date_decision       DATE NULL,
    motif_decision      VARCHAR(500) NULL,

    -- Niveau dans le nouveau établissement
    niveau_dest         VARCHAR(50) NULL COMMENT 'Niveau accueilli dans l\'étab destination',

    -- Gestion
    demande_par         INT UNSIGNED NOT NULL COMMENT 'FK users.id',
    traite_par          INT UNSIGNED NULL COMMENT 'FK users.id (admin qui approuve)',
    cree_le             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le          DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_eleve      (eleve_iue),
    INDEX idx_source     (ecole_source_code),
    INDEX idx_dest       (ecole_dest_code),
    INDEX idx_statut     (statut),
    INDEX idx_annee      (annee_scolaire),
    FOREIGN KEY (demande_par) REFERENCES users(id),
    FOREIGN KEY (traite_par)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Demandes de transfert scolaire en cours d\'année';

-- ============================================================================
-- 5. TABLE historique_eleve — Journal complet de toutes les actions
-- ============================================================================
CREATE TABLE IF NOT EXISTS historique_eleve (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    eleve_iue       VARCHAR(20)  NOT NULL,
    annee_scolaire  VARCHAR(12)  NULL COMMENT 'Null si action hors calendrier scolaire',

    -- Type d'événement
    type_action     ENUM(
        'inscription','reinscription','desinscription',
        'transfert_depart','transfert_arrivee',
        'promotion','redoublement','abandon',
        'examen','iue_emis','modification',
        'autre'
    ) NOT NULL,

    -- Détails de l'événement
    description     TEXT NOT NULL COMMENT 'Description lisible de l\'action',
    ecole_code      VARCHAR(20)  NULL,
    classe_id       INT UNSIGNED NULL,
    niveau          VARCHAR(50)  NULL,

    -- Référence vers la table source (optionnel)
    ref_table       VARCHAR(60)  NULL COMMENT 'Table source ex: inscriptions, mouvements...',
    ref_id          INT UNSIGNED NULL COMMENT 'ID dans la table source',

    -- Données supplémentaires en JSON
    donnees_json    JSON NULL COMMENT 'Données complémentaires sérialisées',

    -- Traçabilité
    effectue_par    INT UNSIGNED NULL COMMENT 'FK users.id',
    ip_address      VARCHAR(45)  NULL,
    cree_le         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_eleve       (eleve_iue),
    INDEX idx_annee       (annee_scolaire),
    INDEX idx_type        (type_action),
    INDEX idx_ecole       (ecole_code),
    INDEX idx_cree_le     (cree_le),
    FOREIGN KEY (effectue_par) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Historique complet de toutes les actions sur un élève';

-- ============================================================================
-- 6. TABLE bibliotheque_thematiques — Thématiques de classement
-- ============================================================================
CREATE TABLE IF NOT EXISTS bibliotheque_thematiques (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(30)  NOT NULL UNIQUE,
    libelle     VARCHAR(150) NOT NULL,
    description TEXT NULL,
    icone_fa    VARCHAR(60)  NOT NULL DEFAULT 'fa-book' COMMENT 'Classe FontAwesome',
    couleur     VARCHAR(20)  NOT NULL DEFAULT '#007bff',
    ordre       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    actif       TINYINT(1) NOT NULL DEFAULT 1,
    cree_le     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Thématiques de la bibliothèque FIE';

-- Données initiales des thématiques
INSERT IGNORE INTO bibliotheque_thematiques (code, libelle, description, icone_fa, couleur, ordre) VALUES
('annales',        'Annales d\'examens',          'Sujets et corrigés des examens nationaux',           'fa-file-pen',      '#17a2b8', 1),
('manuels',        'Manuels scolaires',           'Manuels et livres pour élèves et enseignants',       'fa-book-open',     '#007bff', 2),
('guides_ens',     'Guides pédagogiques',         'Guides et ressources pour les enseignants',          'fa-chalkboard-user','#6d28d9',3),
('legislation',    'Législation & réglementation','Textes réglementaires du système éducatif',          'fa-scale-balanced','#178a2b', 4),
('formations',     'Supports de formation',       'Documents partagés lors des formations FIE',         'fa-graduation-cap','#fd7e14', 5),
('statistiques',   'Rapports & statistiques',     'Rapports annuels et données statistiques',           'fa-chart-bar',     '#0f766e', 6),
('autres',         'Autres documents',            'Documents divers non classifiés ailleurs',           'fa-folder-open',   '#6c757d', 99);

-- ============================================================================
-- 7. TABLE bibliotheque_documents — Documents téléchargeables
-- ============================================================================
CREATE TABLE IF NOT EXISTS bibliotheque_documents (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    thematique_id   INT UNSIGNED NOT NULL COMMENT 'FK vers bibliotheque_thematiques.id',

    titre           VARCHAR(255) NOT NULL,
    description     TEXT NULL,
    auteur          VARCHAR(150) NULL,
    annee_publication YEAR NULL,
    niveau_scolaire VARCHAR(100) NULL COMMENT 'Ex: Primaire, Secondaire, Tous niveaux',

    -- Fichier
    nom_fichier     VARCHAR(255) NOT NULL COMMENT 'Nom original du fichier',
    chemin_fichier  VARCHAR(500) NOT NULL COMMENT 'Chemin relatif dans uploads/',
    type_mime       VARCHAR(100) NOT NULL DEFAULT 'application/pdf',
    taille_octets   INT UNSIGNED NOT NULL DEFAULT 0,
    extension       VARCHAR(10) NOT NULL DEFAULT 'pdf',

    -- Visibilité et état
    statut          ENUM('brouillon','publie','archive') NOT NULL DEFAULT 'brouillon',
    public          TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=visible par tous sans connexion',
    telechargements INT UNSIGNED NOT NULL DEFAULT 0,

    -- Méta
    publie_par      INT UNSIGNED NOT NULL COMMENT 'FK users.id (bibliothécaire ou admin)',
    publie_le       DATETIME NULL COMMENT 'Date de publication',
    cree_le         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    FULLTEXT KEY ft_titre_desc (titre, description),
    INDEX idx_thematique (thematique_id),
    INDEX idx_statut     (statut),
    INDEX idx_public     (public, statut),
    INDEX idx_annee      (annee_publication),
    FOREIGN KEY (thematique_id) REFERENCES bibliotheque_thematiques(id),
    FOREIGN KEY (publie_par)    REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Documents de la bibliothèque FIE';

-- ============================================================================
-- 8. TABLE bibliotheque_tags — Tags libres sur les documents
-- ============================================================================
CREATE TABLE IF NOT EXISTS bibliotheque_tags (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle     VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bibliotheque_document_tags (
    document_id INT UNSIGNED NOT NULL,
    tag_id      INT UNSIGNED NOT NULL,
    PRIMARY KEY (document_id, tag_id),
    FOREIGN KEY (document_id) REFERENCES bibliotheque_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id)      REFERENCES bibliotheque_tags(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 9. Dossier uploads — noter dans fie_settings
-- ============================================================================
INSERT IGNORE INTO fie_settings (cle, valeur, description) VALUES
('bibliotheque_upload_path', 'uploads/bibliotheque/', 'Dossier de stockage des documents bibliothèque'),
('bibliotheque_max_size_mb', '20', 'Taille max de fichier (Mo)'),
('bibliotheque_extensions',  'pdf,doc,docx,ppt,pptx,xls,xlsx,zip', 'Extensions autorisées'),
('sync_allow_anytime',       '1', 'Permettre re-synchronisation à tout moment (1=oui)');

SET foreign_key_checks = 1;
