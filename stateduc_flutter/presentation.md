# StatEduc Mobile — Présentation technique complète

**Auteur :** Abdoul Nasser Kailou  
**Projet :** PAQABU / UNESCO — Ministère de l'Éducation Nationale du Burundi  
**Version :** 2.0 — Juillet 2026  
**Branche :** `ak_secure`

---

## Table des matières

1. [Contexte et périmètre du projet](#1-contexte-et-périmètre-du-projet)
2. [Architecture générale du système](#2-architecture-générale-du-système)
3. [Serveur PHP — Architecture et composants](#3-serveur-php--architecture-et-composants)
4. [Serveur PHP — Mécanique de sauvegarde des données](#4-serveur-php--mécanique-de-sauvegarde-des-données)
5. [Serveur PHP — Bypass NAT/Fortinet](#5-serveur-php--bypass-natfortinet)
6. [Application Flutter — Structure et organisation](#6-application-flutter--structure-et-organisation)
7. [Application Flutter — Gestion des campagnes](#7-application-flutter--gestion-des-campagnes)
8. [Application Flutter — Collecte et persistance des données](#8-application-flutter--collecte-et-persistance-des-données)
9. [Application Flutter — Moteur de cohérence](#9-application-flutter--moteur-de-cohérence)
10. [Application Flutter — Envoi des données au serveur](#10-application-flutter--envoi-des-données-au-serveur)
11. [Application Flutter — Authentification et sécurité](#11-application-flutter--authentification-et-sécurité)
12. [Schéma SQLite local](#12-schéma-sqlite-local)
13. [Catalogue des endpoints API](#13-catalogue-des-endpoints-api)
14. [Bilan des travaux et état de livraison](#14-bilan-des-travaux-et-état-de-livraison)

---

## 1. Contexte et périmètre du projet

### 1.1 Présentation générale

**StatEduc Mobile** est une application Android native développée sous Flutter pour le compte du **Ministère de l'Éducation Nationale (MEN) du Burundi**, dans le cadre du projet **PAQABU** (Programme d'Appui à la Qualité de l'Éducation de Base au Burundi) financé par l'**UNESCO/IIEP**.

L'application permet aux **agents de collecte** mandatés par le MEN de :
- Se connecter au système central d'information éducative (SISED)
- Télécharger les campagnes de collecte statistique actives
- Saisir les données dans les établissements scolaires, en ligne ou hors ligne
- Contrôler la cohérence des données saisies avant envoi
- Transmettre les données au serveur central dès qu'une connexion réseau est disponible

### 1.2 Contexte technologique

| Composant | Technologie |
|-----------|------------|
| Application mobile | Flutter 3.35 / Dart 3.9 — Android |
| Backend | PHP (Slim v2) sur XAMPP/Windows |
| Base de données serveur | SQL Server 2012 |
| Base de données locale | SQLite (sqflite) |
| Réseau | HTTPS via Fortinet (NAT external port 9191) |
| Gestion d'état | Provider |
| HTTP client | Dio |
| Sécurité locale | flutter_secure_storage |

### 1.3 Périmètre des travaux

Les travaux couvrent la **migration complète** de l'ancienne application Cordova/jQuery vers une application Flutter native, ainsi que la **refonte du serveur PHP** pour garantir la fiabilité des échanges dans le contexte réseau du MEN Burundi :

- Refonte architecture PHP (Slim v2, ADOdb, bcrypt)
- Développement Flutter natif (MVVM, SQLite, Provider)
- Moteur de cohérence offline/online dual-mode
- Mécanisme de téléchargement campagne en 9 étapes
- Bypass DNS/NAT Fortinet pour les appels curl internes PHP
- Signature APK release (keystore RSA 2048 bits)

---

## 2. Architecture générale du système

```
┌─────────────────────────────────────────────────────────────┐
│                    RÉSEAU MEN BURUNDI                        │
│                                                             │
│  ┌─────────────┐    HTTPS:9191      ┌───────────────────┐  │
│  │   Android   │ ─────────────────► │  Fortinet (NAT)   │  │
│  │  (Flutter)  │                    │  port ext. 9191   │  │
│  └─────────────┘                    └────────┬──────────┘  │
│                                              │              │
│                                    ┌─────────▼──────────┐  │
│                                    │  Apache / PHP      │  │
│                                    │  port int. 80/8080 │  │
│                                    │  (XAMPP/Windows)   │  │
│                                    └─────────┬──────────┘  │
│                                              │              │
│                                    ┌─────────▼──────────┐  │
│                                    │  SQL Server 2012   │  │
│                                    │  Base SISED        │  │
│                                    └────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

**Mode offline** : quand l'agent est hors réseau, toutes les données sont persistées dans SQLite (sqflite) sur l'appareil. Le moteur de cohérence fonctionne localement. La transmission se fait ultérieurement dès que le réseau est disponible.

---

## 3. Serveur PHP — Architecture et composants

### 3.1 Fichiers principaux du serveur

| Fichier | Rôle |
|---------|------|
| `config_app.php` | Configuration centrale : constantes, URLs, détection port interne |
| `common_ws.php` | Bootstrap des web services : headers CORS, ADOdb, autoload classes |
| `common.php` | Bootstrap des pages d'administration |
| `data_save.php` | Endpoint de sauvegarde des données de collecte |
| `data_reload.php` | Endpoint de rechargement des données depuis SQL Server |
| `data_rules.php` | Endpoint de récupération des règles de cohérence |
| `data_controle.php` | Endpoint de contrôle de cohérence côté serveur |
| `questionnaire_ws.php` | Web service principal : CRUD des questionnaires |
| `user.class.php` | Classe métier utilisateur (auth, profil, insertion DICO) |

### 3.2 Structure Slim v2

```
StatEduc_burundi/
├── config_app.php          ← Variables globales + _sised_local_port()
├── common_ws.php           ← Bootstrap WS (ADOdb, classes)
├── common.php              ← Bootstrap admin
├── data_save.php           ← POST /theme_save/, /init/
├── data_reload.php         ← GET /theme_reload/
├── data_rules.php          ← GET /rules/
├── data_controle.php       ← POST /controle/
├── questionnaire_ws.php    ← Routes Slim v2 CRUD
└── server-side/
    └── classes/
        └── metier/
            ├── user.class.php
            ├── campagne.class.php
            └── questionnaire.class.php
```

### 3.3 Middleware d'authentification

Chaque requête web service vérifie le token JWT en header :

```
Authorization: Bearer <token>
```

La classe `user.class.php` valide le token, récupère le profil utilisateur depuis `DICO_FIXE_PERSONNE`, et injecte les droits dans le contexte de la requête. L'authentification bcrypt remplace l'ancienne vérification MD5 de l'application Cordova.

---

## 4. Serveur PHP — Mécanique de sauvegarde des données

### 4.1 Rôle de `data_save.php`

`data_save.php` est l'endpoint central de sauvegarde. Il reçoit les données saisies par l'agent mobile, les valide, et les persiste dans SQL Server via `questionnaire_ws.php` (appel curl interne).

**Route principale :**
```
POST /stateduc/data_save.php/theme_save/{id_theme}/{id_camp}/{id_etab}/{id_sector}/
```

### 4.2 Flux d'exécution

```
Flutter POST data_save.php
    │
    ├─► Validation token (user.class.php)
    ├─► Vérification droits (campagne active, établissement assigné)
    ├─► Construction $urlBase = SISED_AURL_INTERNAL + 'questionnaire_ws.php?...'
    ├─► curl POST → questionnaire_ws.php (appel interne)
    │       ├─► ADOdb → SQL Server : INSERT / UPDATE données
    │       └─► Réponse JSON {status, data}
    └─► Retour Flutter : {se_status, se_data}
```

### 4.3 Rôle de `SqlTranslator` et requêtes CTE pivot

Le serveur PHP utilise un `SqlTranslator` pour convertir les données du formulaire en requêtes SQL compatibles SQL Server 2012.

#### Mode SCALAR (formulaire simple)

Pour les thèmes à données uniques (ex : effectifs par établissement) :

```sql
UPDATE DONNEES_ETABLISSEMENT
SET NB_ELEVES = 1250, NB_FILLES = 612
WHERE CODE_ETAB = 'BI001' AND CODE_ANNEE = '2026'
```

#### Mode EXISTS avec CTE pivot (formulaire multi-lignes)

Pour les thèmes à données matricielles (ex : effectifs par âge et niveau) :

```sql
WITH PivotData AS (
  SELECT
    '6 ans' AS TRANCHE_AGE,
    150     AS FILLES_AGE_NIVEAU,
    168     AS GARCONS_AGE_NIVEAU
  UNION ALL
  SELECT '7 ans', 142, 155
  -- ...
)
MERGE ELEVES_AGE_NIVEAU_SEXE AS target
USING PivotData AS source
ON (target.CODE_ETAB = 'BI001' AND target.TRANCHE_AGE = source.TRANCHE_AGE)
WHEN MATCHED THEN UPDATE SET ...
WHEN NOT MATCHED THEN INSERT ...;
```

Ce pattern dual-mode (EXISTS/SCALAR) est déterminé dynamiquement par `SqlTranslator` en fonction du type de thème.

---

## 5. Serveur PHP — Bypass NAT/Fortinet

### 5.1 Problématique

La topologie réseau du MEN Burundi présente un cas particulier : les requêtes extérieures arrivent sur **Fortinet port 9191** qui fait NAT vers **Apache interne sur un port différent (80 ou 8080)**. Le port 9191 n'existe PAS sur la VM Apache.

```
Internet/LAN → Fortinet:9191 (NAT externe) → VM Apache:80 ou :8080
```

Conséquence : quand `data_save.php` essaie d'appeler `questionnaire_ws.php` via curl en utilisant l'URL publique (`http://stateduc.ins.ne:9191/...`), le curl échoue avec **code 6 (DNS) ou code 7 (Connection refused)** car :
- Le serveur ne peut pas résoudre son propre hostname DNS depuis la VM
- Le port 9191 n'existe pas localement sur la VM

### 5.2 Solution : `_sised_local_port()` + `Host` header

**Dans `config_app.php`** — Fonction `_sised_local_port()` :

Cette fonction détermine le port Apache réel par **sondage TCP** sur `127.0.0.1` :

```php
function _sised_local_port() {
    // Priorité 1 : SERVER_PORT validé par fsockopen
    $sp = (int)$_SERVER['SERVER_PORT'];
    if ($sp > 0 && @fsockopen('127.0.0.1', $sp, $e, $m, 1)) return $sp;
    
    // Priorité 2 : sonde 80, 8080, 8000, 8888
    foreach ([80, 8080, 8000, 8888] as $p) {
        if (@fsockopen('127.0.0.1', $p, $e, $m, 1)) return $p;
    }
    
    // Fallback : 80
    return 80;
}

$SISED_AURL_INTERNAL = 'http://127.0.0.1:' . _sised_local_port() . $SISED_URL;
$SISED_HOST_HEADER   = $_SERVER['HTTP_HOST']; // ex: stateduc.ins.ne:9191
```

**Dans `data_save.php` et `data_reload.php`** — Injection du header `Host` :

```php
// curl vers 127.0.0.1:80 (Apache local, jamais le Fortinet)
// mais avec le header Host correct pour le VirtualHost Apache
$curl->setHeader('Host', $GLOBALS['SISED_HOST_HEADER']);
// urlBase = http://127.0.0.1:80/stateduc/questionnaire_ws.php?...
```

**Analogie** : c'est comme passer par la porte de service d'un bâtiment tout en présentant le badge du visiteur principal — Apache reconnaît l'identité via le header `Host` et achemine vers le bon VirtualHost, mais la connexion TCP ne passe jamais par le Fortinet.

### 5.3 Tableau récapitulatif des solutions testées

| Approche | URL curl | Résultat |
|----------|---------|---------|
| URL publique directe | `stateduc.ins.ne:9191` | DNS non résolvable depuis VM |
| Loopback avec port externe | `127.0.0.1:9191` | Port 9191 inexistant sur VM (Fortinet only) |
| Sondage TCP LAN | `172.16.0.32:9191` | Connexion TCP acceptée mais requête refusée |
| CURLOPT_RESOLVE | `stateduc.ins.ne:9191 → 127.0.0.1` | Port 9191 inexistant sur VM |
| **_sised_local_port() + Host header** | **`127.0.0.1:80` + Host** | **✅ Fonctionnel — solution définitive** |

---

## 6. Application Flutter — Structure et organisation

### 6.1 Pattern architectural : MVVM

L'application adopte le pattern **MVVM (Model-View-ViewModel)** avec Provider pour la gestion d'état :

```
lib/
├── main.dart                    ← Entrée + injection Provider
├── models/                      ← Modèles de données
│   ├── user.dart
│   ├── campagne.dart
│   ├── questionnaire.dart
│   └── coherence_result.dart
├── viewmodels/                  ← ViewModels (logique métier + état)
│   ├── auth_viewmodel.dart
│   ├── campagne_viewmodel.dart
│   ├── data_entry_viewmodel.dart
│   └── coherence_viewmodel.dart
├── views/                       ← Écrans (consomment les ViewModels)
│   ├── login_screen.dart
│   ├── home_screen.dart
│   ├── campagne_screen.dart
│   ├── data_entry_screen.dart
│   └── coherence_screen.dart
├── services/                    ← Services techniques
│   ├── api_service.dart         ← Dio + intercepteurs
│   ├── database_service.dart    ← SQLite (sqflite)
│   ├── coherence_evaluator.dart ← Moteur cohérence offline
│   └── secure_storage_service.dart
└── widgets/                     ← Composants UI réutilisables
```

### 6.2 Stack technique Flutter

| Dépendance | Usage |
|-----------|------|
| `flutter 3.35` / `dart 3.9` | Framework mobile |
| `provider` | Gestion d'état MVVM |
| `dio` | Client HTTP + intercepteurs |
| `sqflite` | SQLite local |
| `flutter_secure_storage` | PIN et token chiffrés |
| `shared_preferences` | Cache DNS, préférences légères |
| `path_provider` | Chemins fichiers locaux |
| `connectivity_plus` | Détection réseau |

---

## 7. Application Flutter — Gestion des campagnes

### 7.1 Principe du téléchargement en 9 étapes

Le téléchargement d'une campagne est une opération **atomique et séquentielle** en 9 étapes. Chaque étape est matérialisée par une barre de progression dans l'interface. L'opération peut être relancée intégralement en cas d'échec partiel.

### 7.2 Les 9 étapes

| Étape | Opération | Endpoint |
|-------|-----------|---------|
| 1 | Vérification existence campagne | `GET /campagne/{id}/check` |
| 2 | Téléchargement métadonnées campagne | `GET /campagne/{id}/meta` |
| 3 | Liste des établissements assignés | `GET /campagne/{id}/etab` |
| 4 | Thèmes et questionnaires | `GET /campagne/{id}/themes` |
| 5 | Définition des champs (DICO_FIXE) | `GET /campagne/{id}/dico` |
| 6 | Règles de cohérence | `GET /campagne/{id}/rules` |
| 7 | Données pré-existantes | `GET /campagne/{id}/data` |
| 8 | Validation intégrité locale (SQLite) | *(local)* |
| 9 | Marquage campagne comme active | *(local)* |

### 7.3 Persistance SQLite de la campagne

Toutes les données téléchargées sont persistées dans SQLite. La structure garantit que l'application est **pleinement fonctionnelle hors ligne** après le téléchargement :

```sql
-- Campagne téléchargée
INSERT INTO campagnes (id, nom, annee, statut) VALUES (...);
INSERT INTO etablissements (id, code, nom, id_campagne) VALUES (...);
INSERT INTO themes (id, nom, id_campagne, type) VALUES (...);
INSERT INTO dico_champs (id_theme, nom_champ, type, ordre) VALUES (...);
INSERT INTO coherence_rules (id_theme, sql_regle, sql_assoc, operateur, valeur_ref) VALUES (...);
```

---

## 8. Application Flutter — Collecte et persistance des données

### 8.1 Flux de saisie

```
Sélection établissement
    │
    ▼
Sélection thème / questionnaire
    │
    ▼
Affichage formulaire dynamique (construit depuis dico_champs SQLite)
    │
    ├─► Saisie champs (TextField, Dropdown, DatePicker selon type)
    ├─► Validation format (regex, min/max)
    └─► Sauvegarde locale SQLite (auto-save toutes les 30s)
            │
            ▼
    Déclenchement contrôle cohérence (debounce 800ms)
            │
            └─► Affichage résultat inline (✅ / ⚠️)
```

### 8.2 Table SQLite `collected_data`

```sql
CREATE TABLE collected_data (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    id_camp     TEXT    NOT NULL,
    id_etab     TEXT    NOT NULL,
    id_qst      TEXT    NOT NULL,
    id_filter   TEXT,               -- période, secteur, etc.
    field_name  TEXT    NOT NULL,
    field_value TEXT,
    is_sent     INTEGER DEFAULT 0,  -- 0 = non envoyé, 1 = envoyé
    updated_at  TEXT,
    UNIQUE (id_camp, id_etab, id_qst, id_filter, field_name)
);
```

### 8.3 Chaîne de sauvegarde

```
UI (TextField) 
  → ViewModel.updateField(fieldName, value)
    → DatabaseService.upsertCollectedData(...)
      → SQLite UPSERT (INSERT OR REPLACE)
        → is_sent = 0 (réinitialisé à chaque modification)
```

---

## 9. Application Flutter — Moteur de cohérence

### 9.1 Dual-mode : offline et online

Le contrôle de cohérence fonctionne dans **deux modes complémentaires** :

| Mode | Déclenchement | Moteur | Latence |
|------|--------------|--------|---------|
| **Offline** | Automatique (debounce 800ms après saisie) | `CoherenceEvaluator` (Dart local) | ~50ms |
| **Online** | Manuel (bouton "Vérifier") + avant envoi | `data_controle.php` (SQL Server) | ~2s |

### 9.2 Moteur offline : `CoherenceEvaluator`

Le moteur offline évalue les règles de cohérence **en local** sur les données SQLite, en reconstruisant la logique SQL de cohérence serveur en Dart.

**Étapes d'évaluation pour une règle :**

```
1. Chargement règle depuis SQLite (coherence_rules)
2. Chargement données formulaire courant (collected_data)
3. Chargement données cross-formulaires (getAllCollectedDataForCampEtab)
4. Injection agrégats virtuels (_injectVirtualAggregates)
5. Extraction V1 depuis sql_regle (SUM, COUNT, valeur simple)
6. Extraction V2 depuis sql_assoc ou valeur_ref
7. Application opérateur (=, <>, <, >, <=, >=, BETWEEN)
8. Résultat : COHERENT / INCOHERENT + message
```

### 9.3 Extraction de valeurs : regex TABLE.FIELD

Un point technique clé est l'extraction des noms de champs depuis les SQL de cohérence, qui peuvent contenir des qualificateurs de table :

```dart
// Pattern correct : préfixe table optionnel et non-capturant
static final _sumPattern = RegExp(
  r'SUM\s*\(\s*(?:\w+\.)?\s*(\w+)\s*\)',
  caseSensitive: false,
);
// Exemples capturés correctement :
// SUM(ELEVES_AGE_NIVEAU_SEXE.FILLES_AGE_NIVEAU) → FILLES_AGE_NIVEAU
// SUM(NB_ELEVES)                                → NB_ELEVES
```

### 9.4 Agrégats virtuels

Certaines colonnes référencées dans les règles de cohérence sont des colonnes de **vues SQL** (`ELEVES_AGE_NIVEAU_SEXE`, etc.) qui n'existent pas directement dans les données collectées. Le moteur les reconstitue par calcul :

```dart
void _injectVirtualAggregates(Map<String, dynamic> values) {
  // TOTAL_AGE_NIVEAU = somme de tous les champs numériques
  double total = 0;
  double totalFilles = 0;
  for (final entry in values.entries) {
    final v = double.tryParse(entry.value?.toString() ?? '') ?? 0;
    total += v;
    // FILLES_AGE_NIVEAU = champs marqués filles (NB_F_*, _FILLES_*, etc.)
    if (_isFillesField(entry.key)) totalFilles += v;
  }
  values['TOTAL_AGE_NIVEAU']   = total.toString();
  values['FILLES_AGE_NIVEAU']  = totalFilles.toString();
}
```

### 9.5 Injection par SAVEPOINT

Pour les règles de cohérence qui nécessitent une évaluation côté serveur (mode online), la mécanique utilise des **SAVEPOINTs SQL Server** pour garantir l'atomicité :

```sql
SAVE TRANSACTION sp_coherence_check;
-- Insertion temporaire des données à vérifier
INSERT INTO DONNEES_TEMP SELECT ...;
-- Exécution des règles SQL Server
EXEC sp_run_coherence_rules @id_theme=?, @id_etab=?, @id_camp=?;
-- Récupération résultats
SELECT * FROM COHERENCE_RESULTS WHERE id_session = SCOPE_IDENTITY();
-- Annulation systématique (les données temp ne sont jamais commitées)
ROLLBACK TRANSACTION sp_coherence_check;
```

Ce mécanisme permet d'exécuter les règles SQL Server complexes (vues, agrégats, jointures) sans modifier les données réelles.

---

## 10. Application Flutter — Envoi des données au serveur

### 10.1 Flux d'envoi

```
Bouton "Envoyer les données"
    │
    ├─► Vérification connexion réseau
    ├─► Contrôle cohérence online (data_controle.php)
    │       ├─► 0 incohérence → continuer
    │       └─► N incohérences → affichage liste + confirmation requise
    │
    ├─► Chargement données non envoyées (is_sent = 0)
    ├─► Groupement par thème / établissement
    └─► POST data_save.php/theme_save/{params}
            ├─► Succès → UPDATE collected_data SET is_sent = 1
            └─► Erreur → retry avec backoff (3 tentatives max)
```

### 10.2 Timeouts Dio configurés

```dart
_dio.options = BaseOptions(
  connectTimeout: const Duration(seconds: 30),
  receiveTimeout: const Duration(seconds: 60),
  sendTimeout:    const Duration(seconds: 60),
);
```

Ces valeurs ont été calibrées pour les conditions réseau réelles du MEN Burundi (connexion 4G variable, parfois saturée).

### 10.3 Intercepteurs Dio

```
Chaîne des intercepteurs (ordre ajout = ordre onRequest, LIFO pour onError) :

  [1] _AuthInjectorInterceptor  → Injecte header Authorization: Bearer <token>
  [2] _DnsFallbackInterceptor   → Fallback IP si DNS échoue (réseau variable)
  [3] LogInterceptor            → Log requête/réponse en mode debug
```

**`_DnsFallbackInterceptor`** : en cas d'erreur DNS (`Failed host lookup`, `Could not resolve host`), l'intercepteur remplace automatiquement le hostname par l'IP numérique mise en cache lors de la dernière authentification réussie, puis relance la requête — sans aucune intervention utilisateur.

---

## 11. Application Flutter — Authentification et sécurité

### 11.1 Flux d'authentification

```
Saisie login / mot de passe
    │
    ├─► POST /stateduc/questionnaire_ws.php/auth/
    │       ├─► PHP : vérification bcrypt (password_verify)
    │       ├─► PHP : _resolveAndCacheIp() en background (DNS cache)
    │       └─► Retour : {token, user_info}
    │
    ├─► Stockage token dans flutter_secure_storage
    ├─► Stockage profil dans SharedPreferences
    └─► Navigation vers HomeScreen
```

### 11.2 PIN de déverrouillage

Un **PIN local** (4 à 6 chiffres) est défini lors de la première connexion. Il est stocké hashé dans `flutter_secure_storage`. Lors des sessions suivantes, l'agent peut déverrouiller l'application avec le PIN sans refaire une authentification réseau.

### 11.3 Migration bcrypt

L'ancienne application Cordova utilisait un hash MD5 non salé pour les mots de passe. La version Flutter/PHP utilise **bcrypt** (`password_hash` / `password_verify` PHP) avec un coût de facteur 10. La migration inclut un endpoint de transition pour les utilisateurs ayant un hash MD5 existant.

### 11.4 Cache DNS persistant

Lors de chaque authentification réussie, l'IP numérique du serveur est résolue et persistée dans `SharedPreferences` sous la clé `dns_cache_<hostname>`. Ce cache permet aux requêtes de fonctionner même si la résolution DNS est temporairement indisponible (réseau mobile en déplacement).

---

## 12. Schéma SQLite local

### 12.1 Tables principales

```sql
-- Campagnes téléchargées
CREATE TABLE campagnes (
    id          TEXT PRIMARY KEY,
    nom         TEXT,
    annee       TEXT,
    statut      TEXT DEFAULT 'actif',
    downloaded_at TEXT
);

-- Établissements assignés
CREATE TABLE etablissements (
    id          TEXT PRIMARY KEY,
    code        TEXT,
    nom         TEXT,
    id_campagne TEXT,
    FOREIGN KEY (id_campagne) REFERENCES campagnes(id)
);

-- Thèmes / questionnaires
CREATE TABLE themes (
    id          TEXT PRIMARY KEY,
    nom         TEXT,
    id_campagne TEXT,
    type        TEXT,  -- 'simple' | 'multi_lignes'
    ordre       INTEGER
);

-- Définition des champs (dictionnaire)
CREATE TABLE dico_champs (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    id_theme    TEXT,
    nom_champ   TEXT,
    libelle     TEXT,
    type_champ  TEXT,  -- 'texte' | 'entier' | 'decimal' | 'date' | 'liste'
    ordre       INTEGER,
    obligatoire INTEGER DEFAULT 0,
    valeurs_liste TEXT  -- JSON si type=liste
);

-- Règles de cohérence
CREATE TABLE coherence_rules (
    id          INTEGER PRIMARY KEY,
    id_theme    TEXT,
    libelle     TEXT,
    sql_regle   TEXT,  -- expression V1 (SUM, COUNT, valeur)
    sql_assoc   TEXT,  -- expression V2
    operateur   TEXT,  -- '=', '<>', '<', '>', '<=', '>=', 'BETWEEN'
    valeur_ref  TEXT,  -- valeur de référence si pas de sql_assoc
    gravite     TEXT   -- 'bloquant' | 'avertissement'
);

-- Données collectées
CREATE TABLE collected_data (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    id_camp     TEXT,
    id_etab     TEXT,
    id_qst      TEXT,
    id_filter   TEXT,
    field_name  TEXT,
    field_value TEXT,
    is_sent     INTEGER DEFAULT 0,
    updated_at  TEXT,
    UNIQUE (id_camp, id_etab, id_qst, id_filter, field_name)
);
```

---

## 13. Catalogue des endpoints API

### 13.1 Authentification

| Méthode | Endpoint | Description |
|---------|---------|-------------|
| `POST` | `/questionnaire_ws.php/auth/` | Authentification bcrypt, retourne token |
| `POST` | `/questionnaire_ws.php/auth/change_pin/` | Modification PIN local |

### 13.2 Campagnes

| Méthode | Endpoint | Description |
|---------|---------|-------------|
| `GET` | `/questionnaire_ws.php/campagnes/` | Liste campagnes actives |
| `GET` | `/questionnaire_ws.php/campagne/{id}/meta` | Métadonnées campagne |
| `GET` | `/questionnaire_ws.php/campagne/{id}/etab` | Établissements assignés |
| `GET` | `/questionnaire_ws.php/campagne/{id}/themes` | Thèmes et questionnaires |
| `GET` | `/questionnaire_ws.php/campagne/{id}/dico` | Dictionnaire des champs |

### 13.3 Collecte

| Méthode | Endpoint | Description |
|---------|---------|-------------|
| `GET` | `/campagne/{id}/rules` | Règles de cohérence (offline) |
| `GET` | `/campagne/{id}/data` | Données pré-existantes |
| `POST` | `/data_save.php/theme_save/{theme}/{camp}/{etab}/{sector}/` | Sauvegarde données |
| `GET` | `/data_reload.php/theme_reload/{theme}/{camp}/{etab}/` | Rechargement depuis serveur |
| `POST` | `/data_controle.php/controle/{theme}/{camp}/{etab}/` | Contrôle cohérence online |

### 13.4 Diagnostic

| Méthode | Endpoint | Description |
|---------|---------|-------------|
| `GET` | `/data_save.php/test/` | Diagnostic serveur (URLs, ports, TCP probe) |

---

## 14. Bilan des travaux et état de livraison

### 14.1 Fonctionnalités livrées

| Composant | Fonctionnalité | État |
|-----------|---------------|------|
| **PHP — Serveur** | Migration Slim v2 + ADOdb | ✅ Livré |
| **PHP — Auth** | bcrypt (remplacement MD5) | ✅ Livré |
| **PHP — DNS** | Bypass NAT/Fortinet via `_sised_local_port()` + Host header | ✅ Livré |
| **PHP — DICO** | INSERT `DICO_FIXE_REGROUPEMENT` avec colonnes SQL Server réelles | ✅ Livré |
| **Flutter — Auth** | Login + PIN + cache DNS persistant | ✅ Livré |
| **Flutter — Campagne** | Téléchargement 9 étapes + persistance SQLite | ✅ Livré |
| **Flutter — Collecte** | Formulaires dynamiques depuis dico SQLite | ✅ Livré |
| **Flutter — Cohérence** | Moteur offline (CoherenceEvaluator) | ✅ Livré |
| **Flutter — Cohérence** | Contrôle online (data_controle.php) | ✅ Livré |
| **Flutter — Envoi** | POST data_save.php + retry + is_sent tracking | ✅ Livré |
| **Flutter — DNS** | _DnsFallbackInterceptor + SharedPreferences cache | ✅ Livré |
| **APK** | Signature release (keystore RSA 2048 — stateduc_release.jks) | ✅ Livré |

### 14.2 Environnements de déploiement

| Environnement | URL | Statut |
|--------------|-----|--------|
| Développement | `http://localhost:8080/stateduc/` | ✅ Actif |
| Production MEN | `http://stateduc.mnineduc.gov.bi/stateduc/` | ✅ Déployé |
| Production MEN (legacy) | `http://stateduc.ins.ne:9191/StatEduc/` | ✅ Déployé |

### 14.3 APK release

```
Fichier    : app-release.apk
Build      : flutter build apk --release
Keystore   : android/app/stateduc_release.jks
Alias      : stateduc_key
Algorithme : RSA 2048 / SHA384withRSA
Validité   : 10 000 jours (~01/12/2053)
SHA256     : 35:39:D8:F4:BA:FD:B2:13:91:D4:B4:8E:56:FA:E6:84:
             95:3E:7C:5E:46:A4:8C:99:63:5B:E1:AC:7D:72:B7:22
```

L'APK signé s'installe sans avertissement PlayProtect sur tous les appareils Android du parc MEN Burundi.

---

*Document rédigé par Abdoul Nasser Kailou — PAQABU / UNESCO — Juillet 2026*
