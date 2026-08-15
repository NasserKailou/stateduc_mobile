-- ============================================================
-- Migration : Création de la table fie_settings
-- Paramètres de configuration dynamique de l'application FIE
-- Interopérabilité FIE ↔ StatEduc
-- Version : 1.0.0  |  Date : 2026-08-15
-- ============================================================

CREATE TABLE IF NOT EXISTS `fie_settings` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `cle`         VARCHAR(100)    NOT NULL COMMENT 'Clé de paramètre (ex: stateduc_url)',
    `valeur`      TEXT            NULL     COMMENT 'Valeur du paramètre',
    `description` VARCHAR(255)    NULL     COMMENT 'Description lisible du paramètre',
    `groupe`      VARCHAR(50)     NOT NULL DEFAULT 'general' COMMENT 'Groupe fonctionnel',
    `cree_le`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modifie_le`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_fie_settings_cle` (`cle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Paramètres dynamiques de configuration FIE';

-- ── Valeurs par défaut ─────────────────────────────────────────────────────────

INSERT INTO `fie_settings` (`cle`, `valeur`, `description`, `groupe`) VALUES
    ('stateduc_url',
     'http://stateduc.ins.bi/',
     'URL de base du serveur StatEduc avec lequel FIE synchronise les établissements et agrégats',
     'interop'),

    ('stateduc_api_token',
     '',
     'Token Bearer d\'authentification pour l\'API StatEduc (laisser vide si non utilisé)',
     'interop'),

    ('stateduc_sync_enabled',
     '1',
     'Activer/désactiver la synchronisation automatique avec StatEduc (1=actif, 0=inactif)',
     'interop'),

    ('stateduc_sync_interval_minutes',
     '60',
     'Intervalle en minutes entre deux synchronisations automatiques',
     'interop'),

    ('fie_api_token',
     '',
     'Token Bearer que StatEduc doit présenter pour accéder aux endpoints API de FIE (agrégats, établissements)',
     'securite'),

    ('fie_api_enabled',
     '1',
     'Activer/désactiver l\'exposition de l\'API FIE vers l\'extérieur (1=actif, 0=inactif)',
     'securite')

ON DUPLICATE KEY UPDATE
    `description` = VALUES(`description`),
    `groupe`      = VALUES(`groupe`);
