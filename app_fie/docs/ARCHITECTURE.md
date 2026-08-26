# FIE — Note d'architecture technique

## Stack
- **Backend** : PHP 8.1+, PDO MySQL, architecture MVC
- **Frontend** : HTML5/CSS3/JS Vanilla (zéro jQuery, zéro Bootstrap)
- **Base de données** : MySQL 8.0 (app_fie) ↔ SQL Server 2012+ (StatEduc)
- **Authentification** : Sessions PHP + bcrypt cost-12 + brute-force lockout

## Arborescence `app_fie/`
```
app_fie/
├── api/
│   ├── endpoints/aggregates_ws.php   ← REST exposé vers StatEduc
│   └── stateduc/
│       ├── etabs_fie_ws.php          ← À copier dans StatEduc_burundi/
│       └── StatEducApiClient.php     ← Client cURL côté FIE
├── app/
│   ├── controllers/                  ← MVC Controllers
│   ├── models/                       ← PDO Models
│   └── views/                        ← PHP Views (layouts, modules)
├── cache/                            ← Fichiers temporaires
├── config/
│   ├── config.php                    ← Constantes + autoloader
│   ├── Database.php                  ← Singleton PDO
│   └── Router.php                    ← Routeur HTTP
├── db/schema.sql                     ← Schéma MySQL complet (18 tables)
├── docs/                             ← Documentation
├── logs/                             ← Logs applicatifs
├── public/
│   ├── css/fie.css                   ← Feuille de style (couleurs Burundi)
│   ├── js/fie.js                     ← JS utilitaire vanilla
│   └── index.php                     ← Front Controller
└── services/                         ← Services transversaux
    ├── AggregateService.php
    ├── IueGenerator.php
    ├── Logger.php
    ├── SecurityHelper.php
    └── SyncService.php
```

## Format IUE
`BI-SSSS-AAAA-NNNNNN-CC`
- BI = ISO 3166-1 alpha-2 Burundi
- SSSS = CODE_TYPE_SECTEUR_ENS (4 chiffres, zero-padded)
- AAAA = CODE_TYPE_ANNEE (année scolaire)
- NNNNNN = Séquence atomique (LOCK TABLES iue_sequences WRITE)
- CC = Chiffres de contrôle ISO 7064 MOD 97-10

## Flux d'interopérabilité StatEduc ↔ FIE
1. StatEduc expose `etabs_fie_ws.php` (Bearer token, pagination OFFSET/FETCH)
2. FIE consomme via `SyncService::syncFromApi()` → upsert `etablissements_miroir`
3. FIE calcule les agrégats via `AggregateService::recalculate()`
4. StatEduc consomme `aggregates_ws.php` (Bearer token) → mark-synced POST
