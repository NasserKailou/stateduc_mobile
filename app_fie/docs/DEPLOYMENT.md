# FIE — Feuille de route de déploiement

## Prérequis serveur
- PHP ≥ 8.1 (PDO, mbstring, curl, fileinfo, json)
- MySQL ≥ 8.0
- Apache 2.4+ ou Nginx avec mod_rewrite / try_files
- Accès réseau vers le serveur StatEduc (SQL Server)

## 1. Installation initiale

```bash
# 1. Cloner le dépôt et se positionner sur la branche ak_fie
git clone <repo> && git checkout ak_fie

# 2. Créer la base de données MySQL
mysql -u root -p -e "CREATE DATABASE fie_burundi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p fie_burundi < app_fie/db/schema.sql

# 3. Copier et configurer les variables d'environnement
cp app_fie/config/config.php.dist app_fie/config/config.php
# Éditer DB_HOST, DB_NAME, DB_USER, DB_PASS, STATEDUC_API_TOKEN, FIE_AGREGATS_API_TOKEN

# 4. Configurer le vhost Apache (DocumentRoot → app_fie/public/)
# Activer mod_rewrite et AllowOverride All

# 5. Créer le fichier .htaccess dans app_fie/public/
```

## 2. Fichier .htaccess (app_fie/public/)

```apache
Options -Indexes
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]

# Sécurité
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
```

## 3. Synchronisation des établissements

```bash
# Copier l'endpoint API côté StatEduc
cp app_fie/api/stateduc/etabs_fie_ws.php StatEduc_burundi/etabs_fie_ws.php
# Configurer le token dans StatEduc_burundi/config_fie_api.php (non versionné)

# Premier import depuis l'API via l'interface admin
# → /admin/sync → Lancer (mode Complète)

# Ou fallback Excel si API indisponible
# → /admin/import-excel → uploader infos_etab_bu.xlsx
```

## 4. Création du premier administrateur

```sql
INSERT INTO fie_users (username, password_hash, nom, prenom, role, actif)
VALUES ('admin', '$2y$12$...', 'Administrateur', 'FIE', 'admin', 1);
-- Générer le hash avec : php -r "echo password_hash('motdepasse', PASSWORD_BCRYPT, ['cost'=>12]);"
```

## 5. Jalons de déploiement

| Phase | Livrable | Délai estimé |
|-------|----------|-------------|
| P0 | Infrastructure + schéma BD + admin | Sem. 1–2 |
| P1 | Module inscription complet + IUE | Sem. 3–6 |
| P2 | Module mouvement (transferts) | Sem. 7–10 |
| P3 | Module examens (BEPC/BAC) | Sem. 11–16 |
| P4 | Synchronisation agrégats → StatEduc | Sem. 17–20 |
| P5 | Tableaux de bord avancés | Sem. 21–24 |

## 6. Note MySQL ↔ SQL Server

**Options d'intégration :**

1. **API REST (recommandé)** — FIE expose `aggregates_ws.php`, StatEduc consomme via CURL ou SSIS. Pas de dépendance driver ODBC côté FIE.

2. **ODBC/PDO SQLSRV** — PHP connecte directement SQL Server via `pdo_sqlsrv` (driver Microsoft). Requiert installation du driver sur le serveur FIE.

3. **Fichiers CSV/intermédiaires** — Export MySQL → CSV → Import SSIS SQL Server. Solution de dernier recours, non temps-réel.

**Recommandation :** Option 1 (API REST) pour la synchronisation agrégats, Option 1 inverse (StatEduc API → FIE) pour les établissements. Zéro couplage technique direct entre les deux SGBD.
