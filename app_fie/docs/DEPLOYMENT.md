# FIE — Guide de déploiement XAMPP (Phase 4)

## Prérequis
- XAMPP ≥ 8.1 (PHP 8.1+, Apache 2.4, MySQL/MariaDB 10.4+)
- Module Apache : `mod_rewrite` activé
- Extension PHP : `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`

## Étapes

### 1. Placement des fichiers
```
C:\xampp\htdocs\app_fie\   (Windows)
/opt/lampp/htdocs/app_fie/ (Linux)
```
Le point d'entrée est **`app_fie/public/index.php`**.

### 2. Configuration Apache (VirtualHost recommandé)
Ajouter dans `httpd-vhosts.conf` :
```apache
<VirtualHost *:80>
    ServerName fie.local
    DocumentRoot "C:/xampp/htdocs/app_fie/public"
    <Directory "C:/xampp/htdocs/app_fie/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
Ajouter `127.0.0.1 fie.local` dans `C:\Windows\System32\drivers\etc\hosts`.

**Ou sans VirtualHost** (accès direct) :
- URL : `http://localhost/app_fie/public/`
- S'assurer que `AllowOverride All` est activé pour `htdocs`.

### 3. Base de données (phpMyAdmin)
```sql
CREATE DATABASE fie_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fie_user'@'localhost' IDENTIFIED BY 'MotDePasseSecurise!';
GRANT ALL PRIVILEGES ON fie_db.* TO 'fie_user'@'localhost';
FLUSH PRIVILEGES;
```
Puis importer dans l'ordre :
1. `app_fie/db/schema.sql` — schéma complet
2. `app_fie/db/seed_users.sql` — utilisateurs de test

### 4. Configuration `config/config.php`
```php
define('DB_HOST',     'localhost');
define('DB_NAME',     'fie_db');
define('DB_USER',     'fie_user');    // ou 'root' pour dev local
define('DB_PASS',     'MotDePasseSecurise!'); // vide pour root XAMPP par défaut
define('FIE_DEBUG',   true);          // false en production
```

### 5. Dossier cache (permissions)
```bash
mkdir app_fie/cache
chmod 750 app_fie/cache   # Linux/Mac
```
Sur Windows : clic droit → Propriétés → Sécurité → Écriture pour `IUSR`.

### 6. Vérification post-déploiement
| Test | URL | Résultat attendu |
|------|-----|-----------------|
| Page d'accueil publique | `http://fie.local/` | Hero FIE, navbar rouge |
| Connexion admin | `http://fie.local/connexion` | Formulaire Bootstrap |
| Login | `admin.fie` / `AdminFIE2026!` | Tableau de bord |
| Recherche élève | `http://fie.local/inscription/recherche` | Formulaire de recherche |
| Admin | `http://fie.local/admin` | Page administration |

### 7. Comptes de test (voir `db/seed_users.sql`)
| Login | Mot de passe | Rôle |
|-------|-------------|------|
| `admin.fie` | `AdminFIE2026!` | Super Admin |
| `admin.bujumbura` | `ProvinceFIE2026!` | Admin provincial |
| `gest.lycee.mwm` | `GestEtab2026!` | Gestionnaire étab. |
| `enseignant.dupont` | `Enseignant2026!` | Enseignant |
| `consultant.mineduc` | `Consultant2026!` | Consultant (lecture) |

**IMPORTANT** : Changer tous les mots de passe avant mise en production.
