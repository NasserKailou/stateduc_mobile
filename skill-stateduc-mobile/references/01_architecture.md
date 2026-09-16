# Architecture du Système StatEduc Mobile

## Vue d'ensemble

```
┌─────────────────────────────────────────────────────────────────┐
│                    APPLICATION MOBILE (Flutter)                  │
│                                                                   │
│  ┌──────────┐  ┌────────────────┐  ┌────────────────────────┐  │
│  │  Screens │  │   Providers    │  │       Services         │  │
│  │  login   │  │ AuthProvider   │  │  ApiService (Dio)      │  │
│  │  settings│  │ DataEntry      │  │  DatabaseService(SQLite)│  │
│  │  campaigns│  │ CampaignProvider│  │  CoherenceEvaluator    │  │
│  │  data_entry│  └────────────────┘  └────────────────────────┘  │
│  └──────────┘                                                     │
└──────────────────────────┬──────────────────────────────────────┘
                           │ HTTP/HTTPS (Dio)
                           │ Auth: Basic (login:password base64)
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                  SERVEUR PHP (Apache + Slim 2.x)                 │
│                                                                   │
│  Endpoints REST (Web Services) :                                 │
│  ┌─────────────────┬──────────────────┬────────────────────┐   │
│  │ annees_ws.php   │ data_camp.php     │ data_save.php      │   │
│  │ /active/:login  │ /camp/:user       │ POST /theme_save/  │   │
│  │ /all/:login     │ /regroups/:u/:c   │ :user/:camp/:sect  │   │
│  │                 │ /schools/:u/:c    │ /:theme/:etab      │   │
│  └─────────────────┴──────────────────┤ /:filter/:start    │   │
│                                        │ /:id_annee         │   │
│  ┌─────────────────┬──────────────────┴────────────────────┐   │
│  │ data_rules.php  │ data_reload.php                        │   │
│  │ /rules/:u/:c/:t │ /reload/:u/:c/:t/:e/:f/:s/:annee      │   │
│  └─────────────────┴────────────────────────────────────────┘   │
│                           │                                       │
│                    curl interne                                   │
│                           ▼                                       │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │              questionnaire_ws.php                         │   │
│  │  (moteur HTML de saisie + sauvegarde ADODB en base)      │   │
│  │  Retourne : "ISOKSAVEINDATABASE" ou HTML d'erreur        │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           │                                       │
└───────────────────────────┼─────────────────────────────────────┘
                            │ ADODB (Microsoft Access ODBC)
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                BASE DE DONNÉES ACCESS                            │
│                                                                   │
│  dico_DB.mdb / dico_DB.accdb  (dictionnaire)                    │
│  ├── ADMIN_USERS              (utilisateurs mobiles)            │
│  ├── DICO_FIXE_REGROUPEMENT   (affectations user→campagne)      │
│  ├── ANNEES_SCOLAIRES         (années disponibles)              │
│  └── PARAM_DEFAUT             (paramètres globaux)              │
│                                                                   │
│  data_DB.mdb / data_DB.accdb  (données collectées)              │
│  ├── ETABLISSEMENT_REGROUPEMENT (écoles)                        │
│  ├── INDIVIDU_*               (données collectées par thème)    │
│  └── CODE_ANNEE_*             (colonnes année ajoutées)         │
└─────────────────────────────────────────────────────────────────┘
```

---

## Topologie réseau typique

### Production (Burundi)
```
Internet ──→ Fortinet/NAT:9191 ──→ VM Apache:8083
```
- Le port externe (9191) ≠ port local Apache (8083)
- `SERVER_PORT` = 8083 (port réel Apache)
- `HTTP_HOST` = `172.24.55.55:8083` ou nom de domaine:9191
- `SISED_AURL_INTERNAL` doit utiliser `127.0.0.1:8083` (pas le port NAT)

### Développement local
```
localhost:8080 ou 127.0.0.1:80
```

### Derrière reverse proxy SSL (Nginx/Fortinet)
```
Internet:443 (SSL) ──→ Nginx ──→ Apache:80
```
- Forcer `$_sised_local_scheme = 'http'` (jamais https vers 127.0.0.1)
- Exclure port 443 de la détection de port local

---

## Cycle de vie d'un envoi de données (happy path)

```
Flutter                          data_save.php              questionnaire_ws.php
  │                                    │                            │
  │── POST /theme_save/user/camp/... ──→│                            │
  │                                    │── _checkYearConsistency() │
  │                                    │   (annees_ws.php, 8s max) │
  │                                    │                            │
  │                                    │── session_write_close()    │
  │                                    │                            │
  │                                    │── curl POST ──────────────→│
  │                                    │   questionnaire_ws.php     │
  │                                    │   ?sector=&theme=&annee=   │
  │                                    │   &login=&code_etab=       │
  │                                    │                            │── session_start()
  │                                    │                            │── session_write_close()
  │                                    │                            │── require common.php
  │                                    │                            │   └─ session_start()
  │                                    │                            │      (avec guard)
  │                                    │                            │── ADODB save
  │                                    │                            │── echo "ISOKSAVEINDATABASE"
  │                                    │←── "ISOKSAVEINDATABASE" ───│
  │                                    │
  │                                    │── log moblogs/user.log
  │←── {"se_status":200,"se_data":"OKSAVE"} ──────────────────────│
```

---

## Structure des fichiers clés

### PHP (StatEduc_burundi/)
| Fichier | Rôle |
|---------|------|
| `config_app.php` | Détection URL interne, ports, chemins |
| `common_ws.php` | Bootstrap connexions ADODB pour Web Services |
| `common.php` | Bootstrap connexions + session pour pages HTML |
| `annees_ws.php` | Endpoint années scolaires (active, liste) |
| `data_camp.php` | Campagnes, regroupements, écoles, filtres |
| `data_save.php` | Sauvegarde données mobile → curl → questionnaire_ws |
| `data_reload.php` | Rechargement données existantes |
| `data_rules.php` | Règles de cohérence |
| `questionnaire_ws.php` | Moteur HTML formulaire + ADODB save |
| `params.php` | Paramètres métier (noms de tables/colonnes) |

### Flutter (stateduc_flutter/lib/)
| Fichier | Rôle |
|---------|------|
| `services/api_service.dart` | Toutes les requêtes HTTP (Dio) |
| `services/database_service.dart` | SQLite local (cache + données offline) |
| `providers/auth_provider.dart` | Authentification, années actives |
| `providers/data_entry_provider.dart` | Saisie, envoi, rechargement |
| `screens/settings/settings_screen.dart` | Paramètres : connexion, année, PIN |
| `screens/data_entry/school_data_screen.dart` | Saisie par école/thème |

---

## Gestion des sessions PHP (point critique)

```
questionnaire_ws.php appelé via curl interne de data_save.php :

1. SANS cookie PHPSESSID transmis → nouvelle session PHP indépendante
2. questionnaire_ws.php : session_start() [avec guard] → écriture bootstrap
3. questionnaire_ws.php : session_write_close() → libère le verrou
4. require common.php → session_start() [DOIT avoir guard session_status()]
   Sans guard : re-lock sur fichier session → blocage si concurrence

RÈGLE : tout session_start() dans le chemin d'un curl interne
         DOIT être protégé par session_status() === PHP_SESSION_NONE
```
