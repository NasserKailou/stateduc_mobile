-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 18 août 2026 à 12:28
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `fie_burundi`
--

-- --------------------------------------------------------

--
-- Structure de la table `agregats_eleves_age_niveau_sexe`
--

CREATE TABLE `agregats_eleves_age_niveau_sexe` (
  `code_etablissement` int(11) NOT NULL,
  `code_type_annee` smallint(5) UNSIGNED NOT NULL,
  `code_type_niveau` smallint(5) UNSIGNED NOT NULL,
  `code_type_age` smallint(5) UNSIGNED NOT NULL,
  `code_type_section` smallint(5) UNSIGNED NOT NULL,
  `filles_age_niveau` smallint(6) DEFAULT NULL,
  `total_age_niveau` smallint(6) DEFAULT NULL,
  `estimation` int(11) DEFAULT NULL,
  `code_type_etat_fonct` smallint(5) UNSIGNED DEFAULT NULL,
  `calculated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced_to_stateduc` tinyint(1) NOT NULL DEFAULT 0,
  `synced_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cache agrégats à pousser vers ELEVES_AGE_NIVEAU_SEXE StatEduc';

-- --------------------------------------------------------

--
-- Structure de la table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `table_name` varchar(60) NOT NULL,
  `record_id` varchar(30) NOT NULL COMMENT 'PK de l enregistrement affecté',
  `action` enum('INSERT','UPDATE','DELETE','LOGIN','LOGOUT','SYNC','EXPORT') NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `user_login` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Piste d audit — loi 1/03-2026 protection données';

-- --------------------------------------------------------

--
-- Structure de la table `bibliotheque_documents`
--

CREATE TABLE `bibliotheque_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `thematique_id` int(10) UNSIGNED NOT NULL COMMENT 'FK vers bibliotheque_thematiques.id',
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `auteur` varchar(150) DEFAULT NULL,
  `annee_publication` year(4) DEFAULT NULL,
  `niveau_scolaire` varchar(100) DEFAULT NULL COMMENT 'Ex: Primaire, Secondaire, Tous niveaux',
  `nom_fichier` varchar(255) NOT NULL COMMENT 'Nom original du fichier',
  `chemin_fichier` varchar(500) NOT NULL COMMENT 'Chemin relatif dans uploads/',
  `type_mime` varchar(100) NOT NULL DEFAULT 'application/pdf',
  `taille_octets` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `extension` varchar(10) NOT NULL DEFAULT 'pdf',
  `statut` enum('brouillon','publie','archive') NOT NULL DEFAULT 'brouillon',
  `public` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=visible par tous sans connexion',
  `telechargements` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `publie_par` int(10) UNSIGNED NOT NULL COMMENT 'FK users.id (bibliothécaire ou admin)',
  `publie_le` datetime DEFAULT NULL COMMENT 'Date de publication',
  `cree_le` datetime NOT NULL DEFAULT current_timestamp(),
  `modifie_le` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Documents de la bibliothèque FIE';

--
-- Déchargement des données de la table `bibliotheque_documents`
--

INSERT INTO `bibliotheque_documents` (`id`, `thematique_id`, `titre`, `description`, `auteur`, `annee_publication`, `niveau_scolaire`, `nom_fichier`, `chemin_fichier`, `type_mime`, `taille_octets`, `extension`, `statut`, `public`, `telechargements`, `publie_par`, `publie_le`, `cree_le`, `modifie_le`) VALUES
(1, 1, 'Annall 2022', NULL, NULL, NULL, NULL, '20260814114756.pdf', 'uploads/bibliotheque/20260818110114_6a841f5a843cc.pdf', 'application/pdf', 114539, 'pdf', 'publie', 1, 1, 2, '2026-08-18 10:01:14', '2026-08-18 10:01:14', '2026-08-18 10:01:26');

-- --------------------------------------------------------

--
-- Structure de la table `bibliotheque_document_tags`
--

CREATE TABLE `bibliotheque_document_tags` (
  `document_id` int(10) UNSIGNED NOT NULL,
  `tag_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `bibliotheque_tags`
--

CREATE TABLE `bibliotheque_tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `libelle` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `bibliotheque_thematiques`
--

CREATE TABLE `bibliotheque_thematiques` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `libelle` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `icone_fa` varchar(60) NOT NULL DEFAULT 'fa-book' COMMENT 'Classe FontAwesome',
  `couleur` varchar(20) NOT NULL DEFAULT '#007bff',
  `ordre` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `cree_le` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Thématiques de la bibliothèque FIE';

--
-- Déchargement des données de la table `bibliotheque_thematiques`
--

INSERT INTO `bibliotheque_thematiques` (`id`, `code`, `libelle`, `description`, `icone_fa`, `couleur`, `ordre`, `actif`, `cree_le`) VALUES
(1, 'annales', 'Annales d\'examens', 'Sujets et corrigés des examens nationaux', 'fa-file-pen', '#17a2b8', 1, 1, '2026-08-18 08:14:10'),
(2, 'manuels', 'Manuels scolaires', 'Manuels et livres pour élèves et enseignants', 'fa-book-open', '#007bff', 2, 1, '2026-08-18 08:14:10'),
(3, 'guides_ens', 'Guides pédagogiques', 'Guides et ressources pour les enseignants', 'fa-chalkboard-user', '#6d28d9', 3, 1, '2026-08-18 08:14:10'),
(4, 'legislation', 'Législation & réglementation', 'Textes réglementaires du système éducatif', 'fa-scale-balanced', '#178a2b', 4, 1, '2026-08-18 08:14:10'),
(5, 'formations', 'Supports de formation', 'Documents partagés lors des formations FIE', 'fa-graduation-cap', '#fd7e14', 5, 1, '2026-08-18 08:14:10'),
(6, 'statistiques', 'Rapports & statistiques', 'Rapports annuels et données statistiques', 'fa-chart-bar', '#0f766e', 6, 1, '2026-08-18 08:14:10'),
(7, 'autres', 'Autres documents', 'Documents divers non classifiés ailleurs', 'fa-folder-open', '#6c757d', 99, 1, '2026-08-18 08:14:10');

-- --------------------------------------------------------

--
-- Structure de la table `classes`
--

CREATE TABLE `classes` (
  `id` int(10) UNSIGNED NOT NULL,
  `ecole_code` varchar(20) NOT NULL COMMENT 'Code établissement StatEduc',
  `annee_scolaire` varchar(12) NOT NULL COMMENT 'Ex: 2025-2026',
  `niveau` varchar(50) NOT NULL COMMENT 'Ex: CP1, CP2, CE1... 6ème',
  `nom_classe` varchar(100) NOT NULL COMMENT 'Nom complet ex: CP1-A',
  `enseignant_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK vers users.id',
  `effectif` smallint(5) UNSIGNED DEFAULT 0,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `cree_le` datetime NOT NULL DEFAULT current_timestamp(),
  `modifie_le` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Classes d''un établissement par année scolaire';

-- --------------------------------------------------------

--
-- Structure de la table `eleves`
--

CREATE TABLE `eleves` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `iue` varchar(25) NOT NULL COMMENT 'Format : BI-SSSS-AAAA-NNNNNN-CC',
  `nom` varchar(100) NOT NULL,
  `prenoms` varchar(150) NOT NULL,
  `sexe` char(1) NOT NULL,
  `date_naissance` date NOT NULL,
  `lieu_naissance` varchar(150) DEFAULT NULL,
  `province_naissance` varchar(100) DEFAULT NULL,
  `nationalite` char(3) NOT NULL DEFAULT 'BDI' COMMENT 'Code ISO 3166-1 alpha-3',
  `numero_acte_naissance` varchar(50) DEFAULT NULL,
  `date_acte_naissance` date DEFAULT NULL,
  `commune_acte` varchar(100) DEFAULT NULL,
  `nom_pere` varchar(150) DEFAULT NULL,
  `nom_mere` varchar(150) DEFAULT NULL,
  `nom_tuteur` varchar(150) DEFAULT NULL,
  `telephone_tuteur` varchar(30) DEFAULT NULL,
  `adresse_tuteur` varchar(300) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `statut` enum('actif','transféré','sorti','décédé','abandon') NOT NULL DEFAULT 'actif',
  `doublon_suspect` tinyint(1) NOT NULL DEFAULT 0,
  `doublon_iue_ref` varchar(25) DEFAULT NULL COMMENT 'IUE du doublon potentiel',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Structure de la table `etablissements_miroir`
--

CREATE TABLE `etablissements_miroir` (
  `code_etablissement` int(11) NOT NULL COMMENT 'Clé primaire StatEduc',
  `nom_etablissement` varchar(255) NOT NULL,
  `province` varchar(100) DEFAULT NULL,
  `commune` varchar(100) DEFAULT NULL,
  `zone_admin` varchar(100) DEFAULT NULL,
  `colline` varchar(100) DEFAULT NULL,
  `chaine_localisation` varchar(500) DEFAULT NULL COMMENT 'Province / Commune / Zone / Colline / Étab.',
  `code_province` int(10) UNSIGNED DEFAULT NULL,
  `code_commune` int(10) UNSIGNED DEFAULT NULL,
  `code_zone` int(10) UNSIGNED DEFAULT NULL,
  `code_colline` int(10) UNSIGNED DEFAULT NULL,
  `code_type_milieu` tinyint(3) UNSIGNED DEFAULT NULL,
  `code_type_statut_org` tinyint(3) UNSIGNED DEFAULT NULL,
  `code_type_secteur_ens` tinyint(3) UNSIGNED DEFAULT NULL,
  `code_type_fonction` tinyint(3) UNSIGNED DEFAULT NULL,
  `code_type_etablissement` tinyint(3) UNSIGNED DEFAULT NULL,
  `code_type_etat_fonct` tinyint(3) UNSIGNED DEFAULT NULL,
  `code_ecole_pays` varchar(20) DEFAULT NULL COMMENT 'Code administratif national',
  `code_etablissement_parent` int(11) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `adresse_electronique` varchar(200) DEFAULT NULL,
  `responsable_ecole` varchar(200) DEFAULT NULL,
  `annee_creation` smallint(5) UNSIGNED DEFAULT NULL,
  `source` enum('api_stateduc','excel_import','manuel') NOT NULL DEFAULT 'excel_import',
  `synced_at` datetime NOT NULL DEFAULT current_timestamp(),
  `stateduc_updated_at` datetime DEFAULT NULL COMMENT 'Dernière MAJ côté StatEduc',
  `actif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cache local du référentiel établissements StatEduc';

-- --------------------------------------------------------

--
-- Structure de la table `examens`
--

CREATE TABLE `examens` (
  `id` int(10) UNSIGNED NOT NULL,
  `libelle` varchar(200) NOT NULL,
  `type_examen` enum('fin_cycle','concours','certification','autre') NOT NULL,
  `code_type_secteur_ens` tinyint(3) UNSIGNED NOT NULL,
  `code_type_annee` smallint(5) UNSIGNED NOT NULL,
  `date_session` date DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Référentiel des examens/concours';

-- --------------------------------------------------------

--
-- Structure de la table `fie_api_tokens`
--

CREATE TABLE `fie_api_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL COMMENT 'SHA-256 du token brut',
  `description` varchar(200) DEFAULT NULL,
  `scopes` set('read_eleves','write_eleves','read_etabs','sync_etabs','aggregates') NOT NULL DEFAULT 'read_eleves',
  `expires_at` datetime DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fie_settings`
--

CREATE TABLE `fie_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `cle` varchar(100) NOT NULL COMMENT 'Clé de paramètre (ex: stateduc_url)',
  `valeur` text DEFAULT NULL COMMENT 'Valeur du paramètre',
  `description` varchar(255) DEFAULT NULL COMMENT 'Description lisible du paramètre',
  `groupe` varchar(50) NOT NULL DEFAULT 'general' COMMENT 'Groupe fonctionnel',
  `cree_le` datetime NOT NULL DEFAULT current_timestamp(),
  `modifie_le` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Paramètres dynamiques de configuration FIE';

--
-- Déchargement des données de la table `fie_settings`
--

INSERT INTO `fie_settings` (`id`, `cle`, `valeur`, `description`, `groupe`, `cree_le`, `modifie_le`) VALUES
(1, 'stateduc_url', 'http://localhost:8085/StatEduc_burundi/', 'URL de base du serveur StatEduc avec lequel FIE synchronise les établissements et agrégats', 'interop', '2026-08-15 21:57:22', '2026-08-18 09:22:29'),
(2, 'stateduc_api_token', '', 'Token Bearer d\'authentification pour l\'API StatEduc (laisser vide si non utilisé)', 'interop', '2026-08-15 21:57:22', '2026-08-15 21:57:22'),
(3, 'stateduc_sync_enabled', '1', 'Activer/désactiver la synchronisation automatique avec StatEduc (1=actif, 0=inactif)', 'interop', '2026-08-15 21:57:22', '2026-08-15 21:57:22'),
(4, 'stateduc_sync_interval_minutes', '60', 'Intervalle en minutes entre deux synchronisations automatiques', 'interop', '2026-08-15 21:57:22', '2026-08-15 21:57:22'),
(5, 'fie_api_token', '', 'Token Bearer que StatEduc doit présenter pour accéder aux endpoints API de FIE (agrégats, établissements)', 'securite', '2026-08-15 21:57:22', '2026-08-15 21:57:22'),
(6, 'fie_api_enabled', '1', 'Activer/désactiver l\'exposition de l\'API FIE vers l\'extérieur (1=actif, 0=inactif)', 'securite', '2026-08-15 21:57:22', '2026-08-15 21:57:22'),
(7, 'bibliotheque_upload_path', 'uploads/bibliotheque/', 'Dossier de stockage des documents bibliothèque', 'general', '2026-08-18 08:14:10', '2026-08-18 08:14:10'),
(8, 'bibliotheque_max_size_mb', '20', 'Taille max de fichier (Mo)', 'general', '2026-08-18 08:14:10', '2026-08-18 08:14:10'),
(9, 'bibliotheque_extensions', 'pdf,doc,docx,ppt,pptx,xls,xlsx,zip', 'Extensions autorisées', 'general', '2026-08-18 08:14:10', '2026-08-18 08:14:10'),
(10, 'sync_allow_anytime', '1', 'Permettre re-synchronisation à tout moment (1=oui)', 'general', '2026-08-18 08:14:10', '2026-08-18 08:14:10');

-- --------------------------------------------------------

--
-- Structure de la table `fie_users`
--

CREATE TABLE `fie_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `login` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'bcrypt via password_hash()',
  `nom` varchar(100) NOT NULL,
  `prenoms` varchar(150) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `role` enum('super_admin','admin_central','directeur_ecole','enseignant','bibliothecaire') NOT NULL DEFAULT 'enseignant',
  `province_code` varchar(10) DEFAULT NULL,
  `code_etablissement` int(11) DEFAULT NULL COMMENT 'NULL = accès national',
  `province_perimetre` varchar(100) DEFAULT NULL COMMENT 'NULL = toutes provinces',
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 1,
  `failed_login_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `ecole_code` varchar(20) DEFAULT NULL COMMENT 'Code établissement (directeur/enseignant)',
  `classe_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID classe de l''enseignant',
  `nom_complet` varchar(255) DEFAULT NULL COMMENT 'Nom et prénom de l''utilisateur'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Utilisateurs de l application FIE';

--
-- Déchargement des données de la table `fie_users`
--

INSERT INTO `fie_users` (`id`, `login`, `password_hash`, `nom`, `prenoms`, `email`, `telephone`, `role`, `province_code`, `code_etablissement`, `province_perimetre`, `actif`, `must_change_password`, `failed_login_count`, `locked_until`, `last_login_at`, `last_login_ip`, `created_at`, `updated_at`, `created_by`, `ecole_code`, `classe_id`, `nom_complet`) VALUES
(2, 'admin.fie', '$2b$12$VGP/y4X1XA6FxFEFzbEvfuUiw2uHtJLGK/2LSIvmjAjfkacXYbApS', 'NDAYISHIMIYE', 'Adolphe', NULL, NULL, 'super_admin', NULL, NULL, NULL, 1, 1, 0, NULL, '2026-08-18 10:53:29', '::1', '2026-08-15 20:23:30', '2026-08-18 10:53:29', NULL, NULL, NULL, NULL),
(3, 'admin.bujumbura', '$2b$12$jQxo5BIHbYMNL7FYDAm9Fu8M1N3w427Ca.bfvIfJ2VwrFZeS5w26y', 'HAKIZIMANA', 'Pierre-Claver', NULL, NULL, '', 'BJM', NULL, NULL, 1, 1, 0, NULL, NULL, NULL, '2026-08-15 20:23:30', '2026-08-15 20:23:30', NULL, NULL, NULL, NULL),
(4, 'gest.lycee.mwm', '$2b$12$GlDgu0HMuRigM3LHecWoRe8Z7qQUEtUjMskFeN7YdcTo1wKRmLjt2', 'NZEYIMANA', 'Jeanne', NULL, NULL, '', 'MWM', NULL, NULL, 1, 1, 0, NULL, NULL, NULL, '2026-08-15 20:23:30', '2026-08-15 20:23:30', NULL, NULL, NULL, NULL),
(5, 'enseignant.dupont', '$2b$12$kxKTMJRglg2dFGl/d6HV.OTbpkQj9KuDvQDYY.KOjqdCKvWWqYz5a', 'NIYONGABO', 'Jean-Paul', NULL, NULL, 'enseignant', 'GIT', NULL, NULL, 1, 1, 0, NULL, NULL, NULL, '2026-08-15 20:23:30', '2026-08-15 20:23:30', NULL, NULL, NULL, NULL),
(6, 'consultant.mineduc', '$2b$12$0HTU.8VlCdpmrGRtzRPmDO0rSLEPIRkCQbjsZ9VdLkgLQHi..YKYG', 'KABURA', 'Marie-Louise', NULL, NULL, '', NULL, NULL, NULL, 1, 1, 0, NULL, NULL, NULL, '2026-08-15 20:23:30', '2026-08-15 20:23:30', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `historique_eleve`
--

CREATE TABLE `historique_eleve` (
  `id` int(10) UNSIGNED NOT NULL,
  `eleve_iue` varchar(20) NOT NULL,
  `annee_scolaire` varchar(12) DEFAULT NULL COMMENT 'Null si action hors calendrier scolaire',
  `type_action` enum('inscription','reinscription','desinscription','transfert_depart','transfert_arrivee','promotion','redoublement','abandon','examen','iue_emis','modification','autre') NOT NULL,
  `description` text NOT NULL COMMENT 'Description lisible de l''action',
  `ecole_code` varchar(20) DEFAULT NULL,
  `classe_id` int(10) UNSIGNED DEFAULT NULL,
  `niveau` varchar(50) DEFAULT NULL,
  `ref_table` varchar(60) DEFAULT NULL COMMENT 'Table source ex: inscriptions, mouvements...',
  `ref_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID dans la table source',
  `donnees_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Données complémentaires sérialisées' CHECK (json_valid(`donnees_json`)),
  `effectue_par` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK users.id',
  `ip_address` varchar(45) DEFAULT NULL,
  `cree_le` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historique complet de toutes les actions sur un élève';

-- --------------------------------------------------------

--
-- Structure de la table `inscriptions`
--

CREATE TABLE `inscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eleve_id` bigint(20) UNSIGNED NOT NULL,
  `code_etablissement` int(11) NOT NULL,
  `code_type_secteur_ens` tinyint(3) UNSIGNED NOT NULL,
  `code_type_annee` smallint(5) UNSIGNED NOT NULL,
  `code_type_niveau` smallint(5) UNSIGNED NOT NULL,
  `code_type_section` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `numero_classe` varchar(20) DEFAULT NULL COMMENT 'ex: 3AF-A',
  `date_inscription` date NOT NULL,
  `date_debut_annee` date DEFAULT NULL,
  `statut` enum('inscrit','promu','redoublant','transféré_sortant','transféré_entrant','abandon','exclu','diplômé') NOT NULL DEFAULT 'inscrit',
  `code_etab_precedent` int(11) DEFAULT NULL,
  `annee_precedente` smallint(5) UNSIGNED DEFAULT NULL,
  `motif_arrivee` varchar(200) DEFAULT NULL,
  `frais_inscription` decimal(10,2) DEFAULT NULL,
  `bourse` tinyint(1) NOT NULL DEFAULT 0,
  `matricule_etab` varchar(30) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Inscriptions annuelles des élèves';

-- --------------------------------------------------------

--
-- Structure de la table `iue_sequences`
--

CREATE TABLE `iue_sequences` (
  `code_type_secteur_ens` tinyint(3) UNSIGNED NOT NULL,
  `code_type_annee` smallint(5) UNSIGNED NOT NULL,
  `last_seq` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Séquences pour génération IUE';

-- --------------------------------------------------------

--
-- Structure de la table `mouvements`
--

CREATE TABLE `mouvements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eleve_id` bigint(20) UNSIGNED NOT NULL,
  `inscription_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type_mouvement` enum('transfert_sortant','transfert_entrant','abandon','réintégration','décès','exclusion','promotion','redoublement','diplômé') NOT NULL,
  `date_mouvement` date NOT NULL,
  `code_etab_origine` int(11) DEFAULT NULL,
  `code_etab_destination` int(11) DEFAULT NULL,
  `motif` varchar(300) DEFAULT NULL,
  `document_ref` varchar(100) DEFAULT NULL COMMENT 'N° de la lettre/acte',
  `statut_validation` enum('en_attente','validé','rejeté') NOT NULL DEFAULT 'en_attente',
  `validé_par` int(10) UNSIGNED DEFAULT NULL,
  `validé_le` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Mouvements des élèves';

-- --------------------------------------------------------

--
-- Structure de la table `ref_secteur_ens`
--

CREATE TABLE `ref_secteur_ens` (
  `code_type_secteur_ens` tinyint(3) UNSIGNED NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `ordre` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `actif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Miroir TYPE_SECTEUR_ENS StatEduc';

--
-- Déchargement des données de la table `ref_secteur_ens`
--

INSERT INTO `ref_secteur_ens` (`code_type_secteur_ens`, `libelle`, `ordre`, `actif`) VALUES
(1, 'Préscolaire', 1, 1),
(2, 'Fondamental', 2, 1),
(3, 'Post-Fondamental Général & Pédag.', 3, 1),
(4, 'Post-Fondamental Technique A2', 4, 1),
(5, 'Secondaire Technique A3/A4', 5, 1),
(6, 'Alphabétisation', 6, 1),
(7, 'Supérieur', 7, 1);

-- --------------------------------------------------------

--
-- Structure de la table `ref_sexe`
--

CREATE TABLE `ref_sexe` (
  `code_sexe` char(1) NOT NULL,
  `libelle` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ref_sexe`
--

INSERT INTO `ref_sexe` (`code_sexe`, `libelle`) VALUES
('F', 'Féminin'),
('M', 'Masculin');

-- --------------------------------------------------------

--
-- Structure de la table `ref_type_age`
--

CREATE TABLE `ref_type_age` (
  `code_type_age` smallint(5) UNSIGNED NOT NULL,
  `libelle` varchar(50) NOT NULL,
  `age_min` tinyint(3) UNSIGNED NOT NULL COMMENT 'Age minimum inclus',
  `age_max` tinyint(3) UNSIGNED NOT NULL COMMENT 'Age maximum inclus'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Miroir TYPE_TRANCHE_AGE StatEduc';

--
-- Déchargement des données de la table `ref_type_age`
--

INSERT INTO `ref_type_age` (`code_type_age`, `libelle`, `age_min`, `age_max`) VALUES
(1, 'Moins de 3 ans', 0, 2),
(2, '3 ans', 3, 3),
(3, '4 ans', 4, 4),
(4, '5 ans', 5, 5),
(5, '6 ans', 6, 6),
(6, '7 ans', 7, 7),
(7, '8 ans', 8, 8),
(8, '9 ans', 9, 9),
(9, '10 ans', 10, 10),
(10, '11 ans', 11, 11),
(11, '12 ans', 12, 12),
(12, '13 ans', 13, 13),
(13, '14 ans', 14, 14),
(14, '15 ans', 15, 15),
(15, '16 ans', 16, 16),
(16, '17 ans', 17, 17),
(17, '18 ans', 18, 18),
(18, '19 ans', 19, 19),
(19, '20 ans', 20, 20),
(20, '21 ans et plus', 21, 99);

-- --------------------------------------------------------

--
-- Structure de la table `ref_type_annee`
--

CREATE TABLE `ref_type_annee` (
  `code_type_annee` smallint(5) UNSIGNED NOT NULL,
  `libelle` varchar(20) NOT NULL COMMENT 'ex: 2024-2025',
  `annee_debut` smallint(5) UNSIGNED NOT NULL,
  `annee_fin` smallint(5) UNSIGNED NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Années scolaires miroir StatEduc';

--
-- Déchargement des données de la table `ref_type_annee`
--

INSERT INTO `ref_type_annee` (`code_type_annee`, `libelle`, `annee_debut`, `annee_fin`, `actif`) VALUES
(2024, '2024-2025', 2024, 2025, 0),
(2025, '2025-2026', 2025, 2026, 1),
(2026, '2026-2027', 2026, 2027, 0);

-- --------------------------------------------------------

--
-- Structure de la table `ref_type_milieu`
--

CREATE TABLE `ref_type_milieu` (
  `code_type_milieu` tinyint(3) UNSIGNED NOT NULL,
  `libelle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Miroir TYPE_MILIEU StatEduc';

--
-- Déchargement des données de la table `ref_type_milieu`
--

INSERT INTO `ref_type_milieu` (`code_type_milieu`, `libelle`) VALUES
(1, 'Urbain'),
(2, 'Rural'),
(255, 'Non défini');

-- --------------------------------------------------------

--
-- Structure de la table `ref_type_niveau`
--

CREATE TABLE `ref_type_niveau` (
  `code_type_niveau` smallint(5) UNSIGNED NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `code_secteur` tinyint(3) UNSIGNED NOT NULL,
  `ordre` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Miroir TYPE_NIVEAU StatEduc';

--
-- Déchargement des données de la table `ref_type_niveau`
--

INSERT INTO `ref_type_niveau` (`code_type_niveau`, `libelle`, `code_secteur`, `ordre`) VALUES
(1, 'Petite section', 1, 1),
(2, 'Moyenne section', 1, 2),
(3, 'Grande section', 1, 3),
(4, 'Fondamental 1 (1AF)', 2, 1),
(5, 'Fondamental 2 (2AF)', 2, 2),
(6, 'Fondamental 3 (3AF)', 2, 3),
(7, 'Fondamental 4 (4AF)', 2, 4),
(8, 'Fondamental 5 (5AF)', 2, 5),
(9, 'Fondamental 6 (6AF)', 2, 6),
(10, 'Fondamental 7 (7AF)', 2, 7),
(11, 'Fondamental 8 (8AF)', 2, 8),
(12, 'Fondamental 9 (9AF)', 2, 9),
(13, 'Secondaire 1ère', 3, 1),
(14, 'Secondaire 2ème', 3, 2),
(15, 'Secondaire 3ème (Terminal)', 3, 3),
(16, 'Technique A2 – 1ère', 4, 1),
(17, 'Technique A2 – 2ème', 4, 2),
(18, 'Technique A2 – 3ème', 4, 3),
(19, 'Technique A3 – 1ère', 5, 1),
(20, 'Technique A3 – 2ème', 5, 2);

-- --------------------------------------------------------

--
-- Structure de la table `ref_type_section`
--

CREATE TABLE `ref_type_section` (
  `code_type_section` smallint(5) UNSIGNED NOT NULL,
  `libelle` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Miroir TYPE_SECTION StatEduc';

--
-- Déchargement des données de la table `ref_type_section`
--

INSERT INTO `ref_type_section` (`code_type_section`, `libelle`) VALUES
(1, 'Francophone'),
(2, 'Kirundi'),
(3, 'Anglophone'),
(4, 'Swahili'),
(5, 'Bilingue FR/EN'),
(255, 'Non défini');

-- --------------------------------------------------------

--
-- Structure de la table `ref_type_statut_org`
--

CREATE TABLE `ref_type_statut_org` (
  `code` tinyint(3) UNSIGNED NOT NULL,
  `libelle` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Miroir TYPE_STATUT_ORG StatEduc';

--
-- Déchargement des données de la table `ref_type_statut_org`
--

INSERT INTO `ref_type_statut_org` (`code`, `libelle`) VALUES
(1, 'Public'),
(2, 'Privé agréé subventionné'),
(3, 'Privé agréé non subventionné'),
(4, 'Confessionnel catholique'),
(5, 'Confessionnel protestant'),
(6, 'Communautaire'),
(7, 'Privé laïc'),
(8, 'Privé confessionnel'),
(9, 'Public communal'),
(10, 'Public provincial'),
(11, 'Militaire'),
(13, 'Enseignement intégré');

-- --------------------------------------------------------

--
-- Structure de la table `resultats_examen`
--

CREATE TABLE `resultats_examen` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eleve_id` bigint(20) UNSIGNED NOT NULL,
  `examen_id` int(10) UNSIGNED NOT NULL,
  `inscription_id` bigint(20) UNSIGNED DEFAULT NULL,
  `numero_candidat` varchar(30) DEFAULT NULL,
  `note_totale` decimal(6,2) DEFAULT NULL,
  `mention` varchar(50) DEFAULT NULL,
  `admis` tinyint(1) DEFAULT NULL COMMENT '1=admis, 0=non admis',
  `rang` int(10) UNSIGNED DEFAULT NULL,
  `iue_conserve` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'L IUE persiste après l examen',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Résultats d examens';

-- --------------------------------------------------------

--
-- Structure de la table `suivi_pedagogique`
--

CREATE TABLE `suivi_pedagogique` (
  `id` int(10) UNSIGNED NOT NULL,
  `eleve_iue` varchar(20) NOT NULL COMMENT 'IUE de l''élève',
  `classe_id` int(10) UNSIGNED NOT NULL COMMENT 'FK vers classes.id',
  `annee_scolaire` varchar(12) NOT NULL,
  `ecole_code` varchar(20) NOT NULL,
  `decision` enum('en_attente','passe','redouble','abandonne') NOT NULL DEFAULT 'en_attente' COMMENT 'Décision fin d''année',
  `decision_date` date DEFAULT NULL COMMENT 'Date de la décision',
  `moyenne_annuelle` decimal(5,2) DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `valide_par` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK users.id (directeur ou admin)',
  `valide_le` datetime DEFAULT NULL,
  `cree_par` int(10) UNSIGNED NOT NULL COMMENT 'FK users.id (enseignant)',
  `cree_le` datetime NOT NULL DEFAULT current_timestamp(),
  `modifie_le` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Suivi pédagogique fin d''année par élève';

-- --------------------------------------------------------

--
-- Structure de la table `sync_log`
--

CREATE TABLE `sync_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `source_type` enum('api_stateduc','excel_import') NOT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `status` enum('running','success','partial','error') NOT NULL DEFAULT 'running',
  `total_records` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `inserted` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `updated` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `errors` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_page` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'Pour reprise en cas d erreur',
  `details` text DEFAULT NULL COMMENT 'JSON des erreurs éventuelles',
  `triggered_by` varchar(100) DEFAULT NULL COMMENT 'login utilisateur ou cron'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Journal des synchronisations établissements';

--
-- Déchargement des données de la table `sync_log`
--

INSERT INTO `sync_log` (`id`, `source_type`, `started_at`, `ended_at`, `status`, `total_records`, `inserted`, `updated`, `errors`, `last_page`, `details`, `triggered_by`) VALUES
(1, 'api_stateduc', '2026-08-15 22:45:21', '2026-08-15 22:45:21', 'error', 0, 0, 0, 0, NULL, '[{\"err\":\"API StatEduc inaccessible\"}]', 'admin'),
(2, 'api_stateduc', '2026-08-15 22:45:31', '2026-08-15 22:45:31', 'error', 0, 0, 0, 0, NULL, '[{\"err\":\"API StatEduc inaccessible\"}]', 'admin'),
(3, 'api_stateduc', '2026-08-15 22:47:22', '2026-08-15 22:47:23', 'error', 0, 0, 0, 0, NULL, '[{\"err\":\"API StatEduc inaccessible\"}]', 'admin'),
(4, 'api_stateduc', '2026-08-15 23:01:57', '2026-08-15 23:01:57', 'error', 0, 0, 0, 0, NULL, '[{\"err\":\"API StatEduc inaccessible\"}]', 'admin'),
(5, 'api_stateduc', '2026-08-15 23:02:03', '2026-08-15 23:02:03', 'error', 0, 0, 0, 0, NULL, '[{\"err\":\"API StatEduc inaccessible\"}]', 'admin'),
(6, 'api_stateduc', '2026-08-15 23:02:44', '2026-08-15 23:02:44', 'error', 0, 0, 0, 0, NULL, '[{\"err\":\"API StatEduc inaccessible\"}]', 'admin'),
(7, 'api_stateduc', '2026-08-15 23:06:37', '2026-08-15 23:06:37', 'error', 0, 0, 0, 0, NULL, '[{\"err\":\"API StatEduc inaccessible\"}]', 'admin'),
(8, 'api_stateduc', '2026-08-15 23:17:27', '2026-08-15 23:17:27', 'error', 0, 0, 0, 0, NULL, '[{\"err\":\"API StatEduc inaccessible\"}]', 'admin'),
(9, 'api_stateduc', '2026-08-15 23:17:32', '2026-08-15 23:17:32', 'error', 0, 0, 0, 0, NULL, '[{\"err\":\"API StatEduc inaccessible\"}]', 'admin'),
(10, 'api_stateduc', '2026-08-15 23:30:43', '2026-08-15 23:30:43', 'error', 0, 0, 0, 0, NULL, '[{\"err\":\"API StatEduc inaccessible\"}]', 'admin'),
(11, 'api_stateduc', '2026-08-15 23:30:47', '2026-08-15 23:30:47', 'error', 0, 0, 0, 0, NULL, '[{\"err\":\"API StatEduc inaccessible\"}]', 'admin'),
(12, 'api_stateduc', '2026-08-15 23:48:27', '2026-08-15 23:48:58', 'error', 0, 0, 0, 0, NULL, '[{\"err\":\"StatEduc API cURL error: Operation timed out after 30014 milliseconds with 0 bytes received\"}]', 'admin.fie'),
(13, 'api_stateduc', '2026-08-15 23:49:50', '2026-08-15 23:50:21', 'error', 0, 0, 0, 0, NULL, '[{\"err\":\"StatEduc API cURL error: Operation timed out after 30009 milliseconds with 0 bytes received\"}]', 'admin.fie'),
(14, 'api_stateduc', '2026-08-15 23:51:21', '2026-08-15 23:51:24', 'success', 0, 0, 0, 0, 1, NULL, 'admin.fie'),
(15, 'api_stateduc', '2026-08-18 10:01:55', NULL, 'running', 0, 11000, 0, 0, 22, NULL, 'admin.fie'),
(16, 'api_stateduc', '2026-08-18 10:29:27', '2026-08-18 10:30:40', 'error', 11975, 0, 1500, 0, 3, '[{\"err\":\"StatEduc API cURL error: Operation timed out after 30017 milliseconds with 0 bytes received\"}]', 'admin.fie');

-- --------------------------------------------------------

--
-- Structure de la table `transferts_scolaires`
--

CREATE TABLE `transferts_scolaires` (
  `id` int(10) UNSIGNED NOT NULL,
  `eleve_iue` varchar(20) NOT NULL,
  `annee_scolaire` varchar(12) NOT NULL,
  `ecole_source_code` varchar(20) NOT NULL,
  `classe_source_id` int(10) UNSIGNED DEFAULT NULL,
  `motif_depart` varchar(255) DEFAULT NULL,
  `ecole_dest_code` varchar(20) DEFAULT NULL COMMENT 'NULL si inconnu à la demande',
  `classe_dest_id` int(10) UNSIGNED DEFAULT NULL,
  `statut` enum('demande','approuve','rejete','execute') NOT NULL DEFAULT 'demande',
  `date_demande` date NOT NULL DEFAULT curdate(),
  `date_decision` date DEFAULT NULL,
  `motif_decision` varchar(500) DEFAULT NULL,
  `niveau_dest` varchar(50) DEFAULT NULL COMMENT 'Niveau accueilli dans l''étab destination',
  `demande_par` int(10) UNSIGNED NOT NULL COMMENT 'FK users.id',
  `traite_par` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK users.id (admin qui approuve)',
  `cree_le` datetime NOT NULL DEFAULT current_timestamp(),
  `modifie_le` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Demandes de transfert scolaire en cours d''année';

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `agregats_eleves_age_niveau_sexe`
--
ALTER TABLE `agregats_eleves_age_niveau_sexe`
  ADD PRIMARY KEY (`code_etablissement`,`code_type_annee`,`code_type_niveau`,`code_type_age`,`code_type_section`),
  ADD KEY `idx_etab_annee` (`code_etablissement`,`code_type_annee`),
  ADD KEY `idx_sync` (`synced_to_stateduc`);

--
-- Index pour la table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_table_record` (`table_name`,`record_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_date` (`created_at`);

--
-- Index pour la table `bibliotheque_documents`
--
ALTER TABLE `bibliotheque_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_thematique` (`thematique_id`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_public` (`public`,`statut`),
  ADD KEY `idx_annee` (`annee_publication`),
  ADD KEY `publie_par` (`publie_par`);
ALTER TABLE `bibliotheque_documents` ADD FULLTEXT KEY `ft_titre_desc` (`titre`,`description`);

--
-- Index pour la table `bibliotheque_document_tags`
--
ALTER TABLE `bibliotheque_document_tags`
  ADD PRIMARY KEY (`document_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Index pour la table `bibliotheque_tags`
--
ALTER TABLE `bibliotheque_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `libelle` (`libelle`);

--
-- Index pour la table `bibliotheque_thematiques`
--
ALTER TABLE `bibliotheque_thematiques`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ecole_annee` (`ecole_code`,`annee_scolaire`),
  ADD KEY `idx_enseignant` (`enseignant_id`);

--
-- Index pour la table `eleves`
--
ALTER TABLE `eleves`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `iue` (`iue`),
  ADD KEY `idx_nom` (`nom`,`prenoms`),
  ADD KEY `idx_ddn` (`date_naissance`),
  ADD KEY `idx_sexe` (`sexe`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_doublon` (`doublon_suspect`);

--
-- Index pour la table `etablissements_miroir`
--
ALTER TABLE `etablissements_miroir`
  ADD PRIMARY KEY (`code_etablissement`),
  ADD KEY `idx_province` (`province`),
  ADD KEY `idx_commune` (`commune`),
  ADD KEY `idx_secteur` (`code_type_secteur_ens`),
  ADD KEY `idx_milieu` (`code_type_milieu`),
  ADD KEY `idx_synced` (`synced_at`);
ALTER TABLE `etablissements_miroir` ADD FULLTEXT KEY `ft_nom` (`nom_etablissement`);

--
-- Index pour la table `examens`
--
ALTER TABLE `examens`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `fie_api_tokens`
--
ALTER TABLE `fie_api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `fie_settings`
--
ALTER TABLE `fie_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fie_settings_cle` (`cle`);

--
-- Index pour la table `fie_users`
--
ALTER TABLE `fie_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_etab` (`code_etablissement`);

--
-- Index pour la table `historique_eleve`
--
ALTER TABLE `historique_eleve`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_eleve` (`eleve_iue`),
  ADD KEY `idx_annee` (`annee_scolaire`),
  ADD KEY `idx_type` (`type_action`),
  ADD KEY `idx_ecole` (`ecole_code`),
  ADD KEY `idx_cree_le` (`cree_le`),
  ADD KEY `effectue_par` (`effectue_par`);

--
-- Index pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_eleve_annee_etab` (`eleve_id`,`code_type_annee`,`code_etablissement`),
  ADD KEY `idx_etab_annee` (`code_etablissement`,`code_type_annee`),
  ADD KEY `idx_niveau` (`code_type_niveau`),
  ADD KEY `idx_statut` (`statut`);

--
-- Index pour la table `iue_sequences`
--
ALTER TABLE `iue_sequences`
  ADD PRIMARY KEY (`code_type_secteur_ens`,`code_type_annee`);

--
-- Index pour la table `mouvements`
--
ALTER TABLE `mouvements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_eleve` (`eleve_id`),
  ADD KEY `idx_date` (`date_mouvement`),
  ADD KEY `idx_type` (`type_mouvement`);

--
-- Index pour la table `ref_secteur_ens`
--
ALTER TABLE `ref_secteur_ens`
  ADD PRIMARY KEY (`code_type_secteur_ens`);

--
-- Index pour la table `ref_sexe`
--
ALTER TABLE `ref_sexe`
  ADD PRIMARY KEY (`code_sexe`);

--
-- Index pour la table `ref_type_age`
--
ALTER TABLE `ref_type_age`
  ADD PRIMARY KEY (`code_type_age`);

--
-- Index pour la table `ref_type_annee`
--
ALTER TABLE `ref_type_annee`
  ADD PRIMARY KEY (`code_type_annee`);

--
-- Index pour la table `ref_type_milieu`
--
ALTER TABLE `ref_type_milieu`
  ADD PRIMARY KEY (`code_type_milieu`);

--
-- Index pour la table `ref_type_niveau`
--
ALTER TABLE `ref_type_niveau`
  ADD PRIMARY KEY (`code_type_niveau`),
  ADD KEY `idx_secteur` (`code_secteur`);

--
-- Index pour la table `ref_type_section`
--
ALTER TABLE `ref_type_section`
  ADD PRIMARY KEY (`code_type_section`);

--
-- Index pour la table `ref_type_statut_org`
--
ALTER TABLE `ref_type_statut_org`
  ADD PRIMARY KEY (`code`);

--
-- Index pour la table `resultats_examen`
--
ALTER TABLE `resultats_examen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_eleve_examen` (`eleve_id`,`examen_id`),
  ADD KEY `examen_id` (`examen_id`);

--
-- Index pour la table `suivi_pedagogique`
--
ALTER TABLE `suivi_pedagogique`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_eleve_classe_annee` (`eleve_iue`,`classe_id`,`annee_scolaire`),
  ADD KEY `idx_ecole_annee` (`ecole_code`,`annee_scolaire`),
  ADD KEY `idx_decision` (`decision`),
  ADD KEY `cree_par` (`cree_par`),
  ADD KEY `valide_par` (`valide_par`);

--
-- Index pour la table `sync_log`
--
ALTER TABLE `sync_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_started` (`started_at`);

--
-- Index pour la table `transferts_scolaires`
--
ALTER TABLE `transferts_scolaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_eleve` (`eleve_iue`),
  ADD KEY `idx_source` (`ecole_source_code`),
  ADD KEY `idx_dest` (`ecole_dest_code`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_annee` (`annee_scolaire`),
  ADD KEY `demande_par` (`demande_par`),
  ADD KEY `traite_par` (`traite_par`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bibliotheque_documents`
--
ALTER TABLE `bibliotheque_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `bibliotheque_tags`
--
ALTER TABLE `bibliotheque_tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bibliotheque_thematiques`
--
ALTER TABLE `bibliotheque_thematiques`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `eleves`
--
ALTER TABLE `eleves`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `examens`
--
ALTER TABLE `examens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `fie_api_tokens`
--
ALTER TABLE `fie_api_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `fie_settings`
--
ALTER TABLE `fie_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `fie_users`
--
ALTER TABLE `fie_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `historique_eleve`
--
ALTER TABLE `historique_eleve`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `mouvements`
--
ALTER TABLE `mouvements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `resultats_examen`
--
ALTER TABLE `resultats_examen`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `suivi_pedagogique`
--
ALTER TABLE `suivi_pedagogique`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sync_log`
--
ALTER TABLE `sync_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `transferts_scolaires`
--
ALTER TABLE `transferts_scolaires`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `bibliotheque_documents`
--
ALTER TABLE `bibliotheque_documents`
  ADD CONSTRAINT `bibliotheque_documents_ibfk_1` FOREIGN KEY (`thematique_id`) REFERENCES `bibliotheque_thematiques` (`id`),
  ADD CONSTRAINT `bibliotheque_documents_ibfk_2` FOREIGN KEY (`publie_par`) REFERENCES `fie_users` (`id`);

--
-- Contraintes pour la table `bibliotheque_document_tags`
--
ALTER TABLE `bibliotheque_document_tags`
  ADD CONSTRAINT `bibliotheque_document_tags_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `bibliotheque_documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bibliotheque_document_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `bibliotheque_tags` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`enseignant_id`) REFERENCES `fie_users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `fie_api_tokens`
--
ALTER TABLE `fie_api_tokens`
  ADD CONSTRAINT `fie_api_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `fie_users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `historique_eleve`
--
ALTER TABLE `historique_eleve`
  ADD CONSTRAINT `historique_eleve_ibfk_1` FOREIGN KEY (`effectue_par`) REFERENCES `fie_users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`),
  ADD CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`code_etablissement`) REFERENCES `etablissements_miroir` (`code_etablissement`);

--
-- Contraintes pour la table `mouvements`
--
ALTER TABLE `mouvements`
  ADD CONSTRAINT `mouvements_ibfk_1` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`);

--
-- Contraintes pour la table `resultats_examen`
--
ALTER TABLE `resultats_examen`
  ADD CONSTRAINT `resultats_examen_ibfk_1` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`),
  ADD CONSTRAINT `resultats_examen_ibfk_2` FOREIGN KEY (`examen_id`) REFERENCES `examens` (`id`);

--
-- Contraintes pour la table `suivi_pedagogique`
--
ALTER TABLE `suivi_pedagogique`
  ADD CONSTRAINT `suivi_pedagogique_ibfk_1` FOREIGN KEY (`cree_par`) REFERENCES `fie_users` (`id`),
  ADD CONSTRAINT `suivi_pedagogique_ibfk_2` FOREIGN KEY (`valide_par`) REFERENCES `fie_users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `transferts_scolaires`
--
ALTER TABLE `transferts_scolaires`
  ADD CONSTRAINT `transferts_scolaires_ibfk_1` FOREIGN KEY (`demande_par`) REFERENCES `fie_users` (`id`),
  ADD CONSTRAINT `transferts_scolaires_ibfk_2` FOREIGN KEY (`traite_par`) REFERENCES `fie_users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
