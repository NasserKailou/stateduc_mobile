# StatEduc Mobile — Restitution Technique
## Bilan des travaux de développement · Sessions 1 à 40
### UNESCO / MEN Burundi · Projet PAQABU · Consultant : Abdoul Nasser Kailou

---

> **Usage** : Document de support pour réunion technique de restitution.  
> Dépôt GitHub : `https://github.com/NasserKailou/stateduc_mobile`  
> Branches principales : `ak_secure` (serveur PHP + Flutter principal) · `ak_main` (miroir mobile)  
> Pull Request ouverte : [PR #2](https://github.com/NasserKailou/stateduc_mobile/pull/2)

---

## Sommaire

1. [Vue d'ensemble du projet](#1-vue-densemble-du-projet)
2. [Architecture technique](#2-architecture-technique)
3. [Application serveur PHP — Nouveautés et corrections](#3-application-serveur-php--nouveautés-et-corrections)
4. [Application mobile Flutter — Construction complète](#4-application-mobile-flutter--construction-complète)
5. [Sécurité — Migration MD5 → bcrypt](#5-sécurité--migration-md5--bcrypt)
6. [Chaîne de téléchargement de campagne (9 étapes)](#6-chaîne-de-téléchargement-de-campagne-9-étapes)
7. [Contrôle de cohérence — Online et Offline](#7-contrôle-de-cohérence--online-et-offline)
8. [Résolution de bugs complexes (diagnostics)](#8-résolution-de-bugs-complexes-diagnostics)
9. [Tableau de synthèse des fichiers modifiés](#9-tableau-de-synthèse-des-fichiers-modifiés)
10. [État final et tests confirmés](#10-état-final-et-tests-confirmés)
11. [Points d'attention pour la suite](#11-points-dattention-pour-la-suite)

---

## 1. Vue d'ensemble du projet

### Objectif
Réécrire et moderniser **StatEduc** — système de gestion des données éducatives du MEN Burundi — en une **application mobile Android** permettant la collecte de données d'établissements scolaires en mode connecté ET hors ligne.

### Périmètre des travaux
| Composant | Nature | Technologies |
|-----------|--------|-------------|
| Serveur back-end | Correction + nouveaux endpoints REST | PHP 7.3.4, Slim v2, ADOdb, SQL Server 2012 |
| Application mobile | Réécriture complète (zéro Cordova) | Flutter 3.35 / Dart 3.9, SQLite, Provider |
| Base de données | SQL Server 2012 (production) + Access (développement) | ADOdb mssqlnative, OLEDB |

### Contrainte principale
Le serveur historique fonctionne sous **XAMPP / PHP 7.3.4** sur **Windows**. Toutes les corrections et ajouts doivent rester compatibles avec cette version.

---

## 2. Architecture technique

### 2.1 Architecture globale

```
┌─────────────────────────────────────────────────────────────────┐
│                    APPLICATION MOBILE (Flutter)                  │
│                                                                   │
│  ┌──────────┐   ┌───────────────┐   ┌──────────────────────┐   │
│  │  Screens  │ → │   Providers   │ → │      Services        │   │
│  │ (UI/UX)  │   │ (State mgmt)  │   │  ApiService (HTTP)   │   │
│  │          │   │ AuthProvider  │   │  DatabaseService     │   │
│  │ Login    │   │ CampaignProv. │   │   (SQLite local)     │   │
│  │ Campagnes│   │ DataEntryProv.│   │  CoherenceEvaluator  │   │
│  │ Saisie   │   │               │   │   (moteur offline)   │   │
│  └──────────┘   └───────────────┘   └──────────────────────┘   │
│                          │                      │                │
│                    HTTP Basic Auth         SQLite local          │
│                    (Dio + intercepteur)    (stateduc.db)         │
└────────────────────┬─────────────────────────────────────────────┘
                     │ HTTPS / HTTP (réseau MEN)
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│                    SERVEUR PHP (XAMPP Windows)                    │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  Endpoints REST (Slim v2)                                │   │
│  │                                                          │   │
│  │  user_ident.php     → Authentification                  │   │
│  │  user_camp.php      → Campagnes, regroupements, écoles  │   │
│  │  data_camp.php      → Formulaires (HTML + règles)       │   │
│  │  data_save.php      → Sauvegarde données saisie         │   │
│  │  data_controle.php  → Contrôle cohérence (online)       │   │
│  │  data_rules.php     → Règles cohérence (offline)        │   │
│  │  data_reload.php    → Rechargement données serveur      │   │
│  └─────────────────────────────────────────────────────────┘   │
│                          │                                        │
│                   ADOdb (mssqlnative)                            │
│                          │                                        │
│              SQL Server 2012 / Access                            │
│          (DICO_*, ADMIN_USERS, données saisies)                  │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 Pile technologique Flutter

| Package | Version | Rôle |
|---------|---------|------|
| `flutter` | 3.35.4 | Framework UI |
| `dart` | 3.9.2 | Langage |
| `dio` | ^5.7.0 | Client HTTP avec intercepteurs |
| `provider` | ^6.1.2 | Gestion d'état (ChangeNotifier) |
| `sqflite` | ^2.3.3 | Base de données locale SQLite |
| `flutter_secure_storage` | ^9.2.2 | Stockage sécurisé PIN + credentials (Keystore Android) |
| `shared_preferences` | ^2.3.2 | Cache settings non-sensibles |
| `connectivity_plus` | ^6.1.0 | Détection connectivité réseau |
| `flutter_html` | ^3.0.0-beta | Rendu HTML formulaires serveur |
| `webview_flutter` | ^4.10.0 | WebView fidèle pour formulaires complexes |

### 2.3 Pattern de réponse REST standard

Tous les endpoints retournent la même enveloppe JSON :
```json
{
  "se_status":  200,
  "se_message": "OK",
  "se_data":    { ... }
}
```
`se_status 200` = succès · `se_status 400` = erreur métier

---

## 3. Application serveur PHP — Nouveautés et corrections

### 3.1 Correction ADOdb debug mode (`common.php`)

**Problème** : La page `administration.php?val=param` affichait du SQL brut dans le HTML au lieu d'un rendu correct. La cause : `$conn_dico->debug = true` dans la branche `postgres9` de `common.php` faisait émettre chaque requête SQL dans des balises `<pre>` par ADOdb.

**Correction** : commentaire de `$conn_dico->debug = true`. Cette ligne de debug laissée active en production corrompait toutes les réponses JSON AJAX.

**Impact secondaire corrigé** : L'endpoint AJAX `gestion_base_service.php` recevait un JSON corrompu (SQL + JSON concaténé) → jQuery ne pouvait pas le parser. Solution : ajout de `ob_start()` en début de fichier + `ob_clean()` avant chaque `echo json_encode(...)` pour éliminer toute pollution de sortie.

---

### 3.2 Nouveau endpoint : `data_save.php` — Route mobile avec `id_annee`

**Problème** : Le serveur utilisait `$_SESSION['annee']` pour l'année scolaire. L'app mobile (Dio, pas de cookie) n'avait pas de session PHP → la sauvegarde échouait silencieusement.

**Solution** : ajout d'une **route étendue** `/:id_annee` dans `data_save.php` :
```
POST /data_save.php/theme_save/{user}/{camp}/{sector}/{theme}/{etab}/{filter}/{start}/{id_annee}
```

La route injecte `id_annee` dans `$_SESSION['annee']` si absente. La route web standard sans `id_annee` est conservée pour rétrocompatibilité. La logique de sauvegarde a été extraite dans une fonction `theme_save_handler()` partagée par les deux routes.

---

### 3.3 Nouveau endpoint : `data_controle.php` — Contrôle de cohérence online

**Objectif** : permettre à l'app mobile de déclencher un contrôle de cohérence **serveur** après chaque envoi de formulaire.

```
GET /data_controle.php/theme_controle/{user}/{camp}/{sector}/{theme}/{etab}/{filter}/{id_annee}
```

**Implémentation** : utilise `controle_theme_batch.class.php` (version batch sans sortie HTML) qui exécute les règles SQL sur la base de données et retourne les violations en JSON.

**Réponse** :
```json
{
  "se_data": {
    "nb_erreurs": 2,
    "erreurs": [
      { "id_regle": 12, "message": "Effectif total ≠ somme tranches d'âge : 120 > 98" }
    ]
  }
}
```

---

### 3.4 Nouveau endpoint : `data_rules.php` — Règles de cohérence pour évaluation offline

**Objectif** : exposer les règles de cohérence interpolées pour stockage local et évaluation hors ligne.

```
GET /data_rules.php/theme_rules/{user}/{camp}/{sector}/{theme}/{etab}/{filter}/{id_annee}
```

**Fonctionnement** : lit `DICO_REGLE_THEME` + `DICO_REGLE_THEME_ASSOC`, substitue les variables PHP dans les SQL via `eval()` (même logique que `controle_theme.class.php::get_regles()`), retourne les SQL interpolés avec les vraies valeurs (code établissement, code annee).

**Correction majeure (Session 39)** : la fonction `rules_resolve_theme_id()` corrige la décomposition de l'ID composite thème. L'ancien code `strlen(id_sector)` donnait un mauvais raw ID → 0 règles retournées. La nouvelle fonction teste plusieurs longueurs de suffixe (1 à 4 digits) et **valide chaque candidat contre la base de données** :
```
composite=10102, sector="2"
  → strip 1 char → candidat "1010" → 0 règles en DB → rejeté
  → strip 2 chars → candidat "101" → N règles en DB → ✅ retenu
```

---

### 3.5 Stratégie de session pour l'app mobile

Le mobile n'ayant pas de cookie de session PHP, chaque endpoint REST critique accepte `id_annee` comme paramètre URL :

| Endpoint | Paramètre | Effet |
|----------|-----------|-------|
| `data_save.php` | `/:id_annee` | Injecte dans `$_SESSION['annee']` |
| `data_controle.php` | `/:id_annee` | Injecte dans `$_SESSION['annee']` |
| `data_rules.php` | `/:id_annee` | Injecte dans `$_SESSION['annee']` |

`User.codeyear` est renseigné lors de l'authentification et transmis dans chaque requête.

---

## 4. Application mobile Flutter — Construction complète

L'application mobile a été **entièrement réécrite** en Flutter/Dart, remplaçant l'ancienne application Cordova/JavaScript. Voici les composants principaux réalisés.

### 4.1 Base de données locale SQLite (`DatabaseService`)

Remplacement complet des 25+ clés `localStorage` de l'application web par une base SQLite structurée (`stateduc.db`).

**Tables créées** :

| Table SQLite | Correspondance localStorage | Contenu |
|---|---|---|
| `settings` | `stm_User`, `stm_Year`, `stm_Filter` | Clé/valeur génériques |
| `campaigns` | `stm_Campagnes` | Liste des campagnes téléchargées |
| `education_systems` | `stm_Systems` | Systèmes éducatifs |
| `regroup_types` | `stm_TypeRegroups` | Types de regroupements |
| `regroups` | `stm_Regroups` | Regroupements administratifs |
| `schools` | `stm_Etabs` | Établissements scolaires |
| `school_statuses` | `stm_Status` | Statuts établissements |
| `localisations` | `stm_Localisations` | Liens camp + system + school |
| `questions` | `stm_Questions` | Thèmes / formulaires |
| `form_html` | — | Cache HTML formulaires |
| `validation_rules` | `stm_Rules` | Règles de validation champs |
| `collected_data` | `stm_EtabCollectData_*` | Données saisies par l'agent |
| `filter_periods` | `StmFilter` | Périodes de collecte |
| `coherence_rules` | — | Règles cohérence offline (v3) |

**Méthodes clés** :
- `getAllCollectedDataForCoherence()` : charge toutes les périodes d'un formulaire (SUM cross-périodes)
- `getAllCollectedDataForCampEtab()` : charge tous les formulaires de l'école (SUM cross-formulaires)
- `getSchoolsByRegroup()` : 3 stratégies de recherche avec fallbacks robustes

---

### 4.2 Client HTTP (`ApiService`)

Service singleton gérant tous les appels REST vers le serveur.

**Caractéristiques techniques** :
- **Client Dio** avec intercepteur `_AuthInjectorInterceptor` réinjectant `Authorization: Basic` sur chaque requête
- **Timeouts adaptés** :
  - `connectTimeout = 60s` (réseau MEN potentiellement lent)
  - `receiveTimeout = 600s` (10 min pour `data_save.php → questionnaire_ws.php`)
  - `sendTimeout = null` (désactivé — évite faux-positif sur serveur lent à accuser réception)
- **Retry automatique** : 2 re-tentatives avec délai 5s sur timeout/erreur réseau
- **SSL** : certificats auto-signés acceptés (intranet MEN)
- **Correction Mojibake** : formulaires encodés ISO-8859-15 côté serveur → récupération des bytes bruts (`ResponseType.bytes`) + `String.fromCharCodes()` pour préserver les caractères Latin-1

**Méthodes principales** :

| Méthode | Endpoint serveur | Rôle |
|---------|-----------------|------|
| `authenticate()` | `user_ident.php` | Connexion utilisateur |
| `getAvailableCampaigns()` | `user_camp.php` | Liste des campagnes |
| `getRegroups()` / `getSchools()` etc. | `user_camp.php` | Données de navigation |
| `getFormHtml()` | `data_camp.php/html_theme_camp` | HTML formulaire (2 étapes) |
| `saveData()` | `data_save.php` | Envoi formulaire saisi |
| `checkCoherence()` | `data_controle.php` | Contrôle cohérence serveur |
| `fetchRules()` | `data_rules.php` | Règles cohérence offline |
| `reloadData()` | `data_reload.php` | Rechargement depuis serveur |

---

### 4.3 Gestion d'état (`Provider` pattern)

**3 providers** gèrent l'état de l'application :

#### `AuthProvider`
- Authentification utilisateur + stockage sécurisé des credentials
- Gestion du code PIN (4–8 chiffres) avec FlutterSecureStorage (Android Keystore)
- Question de sécurité pour récupération PIN oublié

#### `CampaignProvider`
- Téléchargement des 9 étapes de campagne (avec état de progression)
- `_loadingCampaigns` : état de chargement pour le spinner "Actualiser"
- Stockage/chargement SQLite des données de campagne

#### `DataEntryProvider`
- Saisie de formulaires avec sauvegarde locale automatique (`saveLocally()`)
- Debounce 800ms sur `updateField()` → déclenchement cohérence offline
- Double contrôle de cohérence : offline (SQLite) + online (serveur)
- `_fetchAndStoreCoherenceRulesBackground()` : préchargement des règles en arrière-plan
- Envoi groupé (`sendAllFormsForCampaign()`) + relance individuelle

---

### 4.4 Écrans principaux

| Écran | Fichier | Fonctionnalités |
|-------|---------|----------------|
| **Connexion / PIN** | `pin_screen.dart` | Setup PIN (1ère fois), déverrouillage, récupération par question secrète |
| **Onboarding** | `onboarding_screen.dart` | Saisie URL serveur + credentials, test de connexion |
| **Liste campagnes** | `campaign_list_screen.dart` | Campagnes disponibles + campagnes téléchargées |
| **Chargement campagne** | `load_campaign_screen.dart` | Barre de progression 9 étapes + bouton Actualiser avec spinner |
| **Détail campagne** | `campaign_detail_screen.dart` | Navigation regroupements → établissements |
| **Saisie données** | `school_data_screen.dart` | Formulaire dynamique + indicateurs cohérence inline |
| **Paramètres** | `settings_screen.dart` | Serveur, compte, reset |

---

### 4.5 Formulaires dynamiques (`DynamicFormWidget`)

Les formulaires HTML sont générés côté serveur (PHP + base de données). Le widget Flutter `DynamicFormWidget` les rend fidèlement grâce à `flutter_html` + `webview_flutter`.

**Traitement spécial** :
- Correction encodage ISO-8859-15 → UTF-8 (seuil 5% U+FFFD)
- Injection des valeurs sauvegardées dans les champs HTML
- Extraction des `<input>` et `<select>` pour la saisie native Flutter

---

### 4.6 Sécurité — Code PIN et stockage

- **PIN 4–8 chiffres** stocké haché dans `FlutterSecureStorage` (Android Keystore hardware)
- **Credentials serveur** (login + mot de passe) également dans FlutterSecureStorage
- **Question de sécurité** pour récupération PIN sans déconnexion
- **Verrouillage automatique** au retour d'arrière-plan (optionnel)

---

## 5. Sécurité — Migration MD5 → bcrypt

### 5.1 Contexte

Les mots de passe utilisateurs étaient stockés en **MD5** dans `ADMIN_USERS.PASSWORD` — algorithme cryptographiquement cassé (rainbow tables, GPU cracking en quelques secondes).

### 5.2 Stratégie de migration

| Aspect | Avant | Après |
|--------|-------|-------|
| Algorithme | `md5($password)` | `password_hash($password, PASSWORD_BCRYPT, ['cost'=>12])` |
| Hash stocké | `5f4dcc3b5aa765d61d8327deb882cf99` (32 chars) | `$2y$12$BKWYlzy...` (60 chars) |
| Vérification | `WHERE PASSWORD = md5(input)` | `password_verify(input, $hash_from_db)` |
| Impact mobile | Aucun — le mobile envoie le mot de passe en clair (HTTP Basic), c'est le serveur qui faisait le md5 | Transparent |

### 5.3 Gestion des hashes legacy

`valide_user_ws()` gère **4 cas** pour assurer la transition :

```
Cas 1 → Hash bcrypt valide ($2y$, 60 chars) → password_verify() ✅
Cas 2 → Hash MD5 legacy (32 chars hex)      → md5() + AUTO-MIGRATION vers bcrypt ✅
Cas 3 → Hash bcrypt tronqué (<60 chars)     → error_log() + refus (champ DB trop petit) ⚠️
Cas 4 → Format inconnu                      → error_log() + refus sécurisé ⚠️
```

### 5.4 Problème de collation SQL Server (Session 37b)

**Découverte** : SQL Server avec collation `French_CI_AS` (Case Insensitive) normalisait la casse des `VARCHAR` lors de la lecture via ADOdb `mssqlnative`. Le hash bcrypt contient des majuscules/minuscules **significatives** (base64 modifié). La collation altérait le hash → `password_verify()` retournait `false` → HTTP 401 permanent.

**Double correctif** :
1. **PHP** : `SELECT CONVERT(VARCHAR(100), PASSWORD) COLLATE Latin1_General_CS_AS AS PASSWORD` dans les requêtes
2. **SQL Server** : `ALTER TABLE ADMIN_USERS ALTER COLUMN PASSWORD VARCHAR(100) COLLATE Latin1_General_CS_AS NOT NULL`

---

## 6. Chaîne de téléchargement de campagne (9 étapes)

### 6.1 Architecture de la chaîne

L'app mobile télécharge une campagne en 9 étapes séquentielles. Deux endpoints serveur sont impliqués :

| Étapes | Endpoint | HttpAuth |
|--------|----------|----------|
| 1–6 | `user_camp.php` | ❌ désactivé |
| 7–9 | `data_camp.php` | ✅ actif → `valide_user_ws()` |

### 6.2 Détail des 9 étapes

| Étape | Route | Contenu chargé |
|-------|-------|----------------|
| 1 | `regroup_types_camp/` | Types de regroupements |
| 2 | `regroups_camp/` | Regroupements administratifs |
| 3 | `status_camp/` | Statuts établissements |
| 4 | `etabs_camp/` | Établissements |
| 5 | `locs_camp/` | Localisations (liens system+regroupements+école) |
| 6 | `sys_camp/` | Systèmes éducatifs |
| 7 | `theme_camp/` | Thèmes / formulaires (avec HTML FRAME) |
| 8 | `html_theme_camp/` | HTML des formulaires |
| 9 | `regle_theme_camp/` | Règles de validation des champs |

### 6.3 Bug critique résolu — Filtre `FRAME <> ''` (Session 38)

Le SQL de la route `theme_camp` contenait :
```sql
AND (DICO_THEME_SYSTEME.FRAME <> '')
```
Ce filtre masquait tous les thèmes sans fichier `.frame` pré-généré → `GetAll()` retournait `[]` → étape 7 retournait zéro formulaires → **boucle de téléchargement ne s'exécutait jamais**.

**Correction** : suppression du filtre. Le champ `FRAME` est désormais inclus dans le SELECT pour que le client puisse gérer le cas vide.

### 6.4 Bug résolu — Boucle de tri `while ($nb > $nbo)` (Session 38)

L'algorithme de tri des thèmes par chaîne `PRECEDENT` utilisait une boucle sans protection. Si un élément `PRECEDENT` pointait vers un ID absent (chaîne brisée), la boucle ne terminait jamais → PHP timeout → réponse vide.

**Correction** : réécriture avec compteur `$max_iter = $nb * $nb + 1` + ajout des éléments restants en queue si chaîne brisée.

### 6.5 Bug résolu — `utf8_encode()` déprécié PHP 8.2 (Session 38)

`utf8_encode()` est déprécié en PHP 8.2 et supprimé en PHP 9. Sur certaines configurations, il génère des warnings qui corrompent la réponse JSON.

**Correction** : remplacement par `mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1')` avec fallback `iconv()`.

### 6.6 Amélioration UX — Spinner "Actualiser" (Session 38)

Le bouton "Actualiser" de l'écran "Charger une campagne" restait statique pendant le chargement car `fetchServerCampaigns()` n'activait jamais `_loadingCampaigns = true`.

**Correction** : `_loadingCampaigns = true/false` correctement géré → affichage d'un `CircularProgressIndicator` + label "Chargement…" + désactivation du bouton pendant le fetch.

---

## 7. Contrôle de cohérence — Online et Offline

Le système comporte **deux mécanismes complémentaires** de contrôle de cohérence.

### 7.1 Contrôle en ligne (après envoi)

**Déclenchement** : automatiquement après `sendToServer()` réussi.

**Mécanisme côté serveur** :
1. `data_controle.php` appelle `controle_theme_batch.class.php`
2. La classe lit les règles depuis `DICO_REGLE_THEME` + `DICO_REGLE_THEME_ASSOC`
3. Exécute les SQL interpolés sur la base de données réelle
4. Retourne les violations en JSON

**Affichage Flutter** : indicateur coloré inline dans le formulaire avec le nombre de violations et les messages d'erreur.

---

### 7.2 Contrôle hors ligne (en saisie)

**Déclenchement** : debounce 800ms après chaque `updateField()` + automatiquement après `saveLocally()`.

#### Phase de préchargement des règles

À l'ouverture d'un établissement, `_fetchAndStoreCoherenceRulesBackground()` récupère les règles depuis `data_rules.php` et les stocke dans la table SQLite `coherence_rules`. Ce chargement est **non bloquant** (en arrière-plan).

#### Moteur d'évaluation `CoherenceEvaluator`

Le moteur évalue les règles stockées localement sans connexion réseau.

**Pipeline d'évaluation (4 étapes)** :

```
Étape 1 : Charge collected_data pour l'idQst courant (toutes périodes)
          → getAllCollectedDataForCoherence(idCamp, idEtab, idQst)
          → clés "CHAMP#FILTER_ID" ou "CHAMP" selon présence filtre

Étape 2 : Charge les données de TOUS les formulaires de l'école
          → getAllCollectedDataForCampEtab(idCamp, idEtab)
          → résout les champs cross-formulaires (DONNEES_ETABLISSEMENT)

Étape 3 : Superpose les données non sauvegardées (formData en mémoire)
          → priorité maximale → écrase les données persistées

Étape 4 : Injecte les totaux virtuels pour les colonnes de vues DB
          → TOTAL_AGE_NIVEAU  ← SUM(tous les champs numériques du formulaire)
          → FILLES_AGE_NIVEAU ← SUM(champs dont le nom contient _F_, NB_F_, FILLES)

Pour chaque règle :
  → _extractValue(sql_regle) → V1
  → _extractValue(sql_assoc) → V2
  → _applyOperator(V1, critere, V2) → violation si NON respecté
  → si V1 ou V2 = null → règle ignorée silencieusement (conservatisme)
```

---

### 7.3 Corrections majeures du moteur offline (Sessions 39–40)

#### Bug 1 — ID composite thème mal décomposé (Session 39)

Les IDs de thèmes sont composites : `raw_id || zero_padded_sector`.
```
Exemple : raw=101, sector=2 → composite=10102 (suffixe "02", 2 digits)
```
L'ancienne logique `strlen(sector)` ne prenait qu'1 char → candidat `1010` → 0 règles en DB.

**Correction** : `rules_resolve_theme_id()` teste 1 à 4 chars et valide chaque candidat contre `DICO_REGLE_THEME` avant de le retenir.

#### Bug 2 — Regex `\w+` s'arrête au point dans `TABLE.FIELD` (Session 40 — CRITIQUE)

```
SQL en production : SUM(ELEVES_AGE_NIVEAU_SEXE.FILLES_AGE_NIVEAU)
                          ↑
Ancienne regex : SUM\s*\(\s*(\w+)\s*\)
                             ↑ \w+ s'arrête au '.' → capture le NOM DE TABLE
                               au lieu du NOM DE CHAMP → lookup null

Nouvelle regex : SUM\s*\(\s*(?:\w+\.)?\s*(\w+)\s*\)
                             ↑ qualificateur TABLE. optionnel et non capturant
```

#### Bug 3 — Colonnes de vues DB absentes de `collected_data` (Session 40)

`FILLES_AGE_NIVEAU` et `TOTAL_AGE_NIVEAU` sont des colonnes de la **vue SQL Server `ELEVES_AGE_NIVEAU_SEXE`** — elles n'existent jamais comme champs de formulaire. Un lookup direct échoue toujours.

**Solution** : `_injectVirtualAggregates()` calcule des approximations :
- `TOTAL_AGE_NIVEAU` ← somme de tous les champs numériques du formulaire
- `FILLES_AGE_NIVEAU` ← somme des champs avec marqueur filles (`NB_F_*`, `_FILLES_*`, `FILLE`)

#### Bug 4 — Données cross-formulaires manquantes (Session 40)

Certaines règles (`sql_assoc`) référencent des champs collectés via un **autre formulaire** (autre `id_qst`). La méthode `getAllCollectedDataForCampEtab()` résout ce cas en chargeant tous les formulaires de l'école.

---

## 8. Résolution de bugs complexes (diagnostics)

### 8.1 Symptôme asymétrique : étapes 1–6 OK / étapes 7–9 KO

**Cause racine** : `common_ws.php` utilisait `@session_start(['read_and_close' => true])` — mode **lecture seule**. `valide_user_ws()` écrivait `$_SESSION['groupe']` puis vérifiait `isset($_SESSION['groupe'])` pour retourner `true`. Avec `read_and_close`, l'écriture était ignorée → `isset()` = false → retour `false` → HTTP 401 sur tous les appels `data_camp.php`.

`user_camp.php` fonctionnait car `HttpAuth` y était commenté → aucune vérification.

**Correction** : 
- `session_start()` normal + `session_write_close()` explicite **après** les initialisations
- `valide_user_ws()` retourne `true` directement après `password_verify()` (sans écriture/lecture session)
- Suppression du `session_start()` dupliqué dans `user_camp.php`

### 8.2 `ctype_alnum` bloquait les logins non alphanumériques (Session 36)

`HttpAuth::authenticate()` avait un garde `if(!ctype_alnum($username))` qui rejetait tout login contenant `-`, `_`, `.`, `@`. Remplacé par `empty($username) || empty($password)`.

### 8.3 Gestion des établissements dans la navigation (multiple stratégies)

`getSchoolsByRegroup()` implémente 3 stratégies de fallback :
1. **via `localisations.regroups_json`** : cherche l'idRegp dans le JSON array stocké
2. **direct `schools.id_regroup`** : si stratégie 1 vide
3. **tous les établissements** : dernier recours pour ne jamais laisser l'écran vide

### 8.4 Champ PASSWORD trop petit dans Access (Session 37)

Un hash bcrypt mesure 60 caractères. Si le champ `PASSWORD` est `TEXT(32)` dans Access, le hash est silencieusement tronqué → `password_verify()` échoue toujours. Solution : `ALTER TABLE` → `TEXT(255)` + script de diagnostic fourni.

---

## 9. Tableau de synthèse des fichiers modifiés

### Côté serveur PHP

| Fichier | Nature | Modifications |
|---------|--------|--------------|
| `common.php` | Corrigé | Commentaire `$conn_dico->debug = true` (branche postgres9) |
| `server-side/include/administration/gestion_base_service.php` | Corrigé | `ob_start()` + `ob_clean()` — protection JSON contre pollution |
| `data_save.php` | Amélioré | Route étendue `/:id_annee` + refactoring `theme_save_handler()` |
| `data_controle.php` | **Créé** | Endpoint contrôle cohérence online pour mobile |
| `data_rules.php` | **Créé** + corrigé | Endpoint règles cohérence offline + `rules_resolve_theme_id()` |
| `data_camp.php` | Corrigé | Suppression filtre `FRAME <> ''`, boucle tri sécurisée, utf8_encode → mb_convert_encoding |
| `user_camp.php` | Corrigé | Suppression `session_start()` dupliqué |
| `common_ws.php` | Corrigé | `read_and_close` → session normale + `session_write_close()` |
| `server-side/lib/fonctions.inc.php` | Corrigé | `valide_user_ws()` : 4 cas bcrypt/MD5/tronqué/inconnu + auto-migration; `valide_user()`, `infos_user_ws()` : COLLATE CS_AS |
| `server-side/include/web_services/HttpAuth.php` | Corrigé | `ctype_alnum` → `empty()` |
| `user_ident.php` | Corrigé | Suppression `md5()`, ajout `password_hash()` |
| `server-side/classes/metier/user.class.php` | Corrigé | Import/création utilisateurs : md5 → bcrypt |
| `server-side/sql/create_admin_nasser_bcrypt.sql` | **Créé** | Script SQL admin nasser/nasser@2026 en bcrypt |
| `server-side/sql/alter_password_field_access.sql` | **Créé** | Instructions agrandissement champ PASSWORD Access |
| `server-side/sql/alter_password_field_sqlserver.sql` | **Créé** | ALTER TABLE + COLLATE CS_AS sur SQL Server |
| `StatEduc_burundi/CHANGELOG.md` | **Créé** | Journal détaillé de tous les travaux |

### Côté application mobile Flutter

| Fichier | Nature | Modifications |
|---------|--------|--------------|
| `lib/services/api_service.dart` | **Créé** | Service HTTP singleton complet (Dio, auth, retry, tous endpoints) |
| `lib/services/database_service.dart` | **Créé** + amélioré | 14 tables SQLite, toutes méthodes CRUD, v2→v3 migrations, getAllCollectedDataForCoherence(), getAllCollectedDataForCampEtab() |
| `lib/services/coherence_evaluator.dart` | **Créé** + réécrit | Moteur offline : regex TABLE.FIELD, agrégats virtuels, cross-formulaires |
| `lib/services/auth_service.dart` | **Créé** | Authentification + PIN + question secrète |
| `lib/providers/auth_provider.dart` | **Créé** | État auth + PIN FlutterSecureStorage |
| `lib/providers/campaign_provider.dart` | **Créé** + corrigé | Téléchargement 9 étapes + `_loadingCampaigns` spinner |
| `lib/providers/data_entry_provider.dart` | **Créé** | Saisie, save locale/serveur, cohérence double |
| `lib/models/*.dart` | **Créés** | Campaign, School, Question, User, Regroup, EducationSystem |
| `lib/screens/login/pin_screen.dart` | **Créé** | Setup/déverrouillage/récupération PIN |
| `lib/screens/campaigns/load_campaign_screen.dart` | **Créé** + corrigé | Progression 9 étapes + spinner Actualiser |
| `lib/screens/campaigns/campaign_list_screen.dart` | **Créé** | Liste campagnes disponibles/téléchargées |
| `lib/screens/schools/campaign_detail_screen.dart` | **Créé** | Navigation regroupements → écoles |
| `lib/screens/data_entry/school_data_screen.dart` | **Créé** | Saisie formulaire + indicateurs cohérence |
| `lib/widgets/dynamic_form/dynamic_form_widget.dart` | **Créé** | Rendu HTML formulaires + extraction champs |
| `pubspec.yaml` | **Créé** | Dépendances Flutter (dio, sqflite, provider, etc.) |

---

## 10. État final et tests confirmés

### Résultats de tests (device réel, après Sessions 38–40)

| Fonctionnalité | Résultat | Notes |
|----------------|----------|-------|
| Connexion utilisateur (bcrypt) | ✅ | `nasser` / `nasser@2026` |
| Téléchargement campagne étape 1–6 | ✅ | Régroupements, écoles, systèmes |
| Téléchargement campagne étape 7 (thèmes) | ✅ | Corrigé Session 38 — filtre FRAME supprimé |
| Téléchargement campagne étape 8 (HTML formulaires) | ✅ | Corrigé Session 38 |
| Téléchargement campagne étape 9 (règles validation) | ✅ | Corrigé Session 38 |
| Spinner "Actualiser" | ✅ | Corrigé Session 38 |
| Saisie données formulaire | ✅ | |
| Sauvegarde locale SQLite | ✅ | |
| Envoi serveur | ✅ | Avec retry automatique |
| Contrôle cohérence **online** | ✅ | "2 incohérence(s) détectée(s)" confirmé |
| Contrôle cohérence **offline** | 🔄 | À re-tester après git pull Session 40 |
| Navigation hors ligne | ✅ | Toutes données en cache SQLite |

---

## 11. Points d'attention pour la suite

### 11.1 Migration des utilisateurs MD5 existants

Les utilisateurs avec mots de passe MD5 seront auto-migrés vers bcrypt à leur première connexion après le déploiement. **Action requise** : informer les agents de collecte qu'une réinitialisation de mot de passe peut être nécessaire via `administration.php?val=gestionuser`.

### 11.2 Champ PASSWORD dans Access/SQL Server

Pour les bases de données Access : vérifier que le champ `PASSWORD` est bien en `TEXT(255)` (script `alter_password_field_access.sql` fourni).

Pour SQL Server : exécuter `alter_password_field_sqlserver.sql` pour appliquer `COLLATE Latin1_General_CS_AS` sur le champ `PASSWORD`.

### 11.3 Test approfondi du contrôle de cohérence offline

Après re-téléchargement de la campagne (Session 40), vérifier les logs Flutter :
```
[CoherenceEval] virtual TOTAL_AGE_NIVEAU = <valeur>
[CoherenceEval] virtual FILLES_AGE_NIVEAU = <valeur>
[CoherenceEval] rule=493 ... → v1=<number> | ... → v2=<number>
[CoherenceEval] evaluate complete: 2 violation(s)
```
Si les noms de champs du formulaire `idQst=9502` ne contiennent pas le pattern `_F_`/`NB_F_`/`FILLES`, le calcul de `FILLES_AGE_NIVEAU` devra être affiné — fournir les noms de champs des logs Flutter.

### 11.4 Règles de cohérence pour d'autres thèmes

Les règles de cohérence configurées en base (`DICO_REGLE_THEME`) sont spécifiques à chaque thème. Le moteur offline `CoherenceEvaluator` est générique et s'adapte à tout SQL `SUM(TABLE.FIELD)`. Pour les thèmes avec des SQL plus complexes (JOINs, sous-requêtes), la règle sera silencieusement ignorée (comportement conservatif — pas de faux positifs).

### 11.5 Performances SQLite

La méthode `getAllCollectedDataForCampEtab()` charge tous les formulaires de l'école. Pour les campagnes avec de nombreux formulaires et établissements, surveiller les performances. Un index supplémentaire sur `collected_data(id_camp, id_etab)` peut être ajouté si nécessaire.

### 11.6 Version PHP

Le serveur est en **PHP 7.3.4**. Toutes les corrections sont compatibles. Attention : si une mise à jour vers PHP 8.0+ est planifiée, réviser les usages de `utf8_encode()` (désormais supprimé) et la syntaxe `@session_start()`.

---

## Annexe — Commits principaux

| SHA | Session | Description |
|-----|---------|-------------|
| `2d7aec6` | 40 | fix(session40): cohérence offline — regex TABLE.FIELD + agrégats virtuels + cross-formulaires |
| `7689882` | 39 | fix(session39): contrôle cohérence offline — mauvaise décomposition ID composite theme |
| `7498ba5` | 38 | fix(session38): formulaires mobiles étapes 7-9 + indicateur chargement Actualiser |
| `1a93437` | 37b | fix(session37b): collation SQL Server CI corrompt hash bcrypt → 401 |
| `107c353` | 37 | fix(session37): correctif complet chargement formulaires mobiles post-bcrypt |
| `c58a44c` | 36 | fix(auth): session 36 — correctif régression chargement formulaires après bcrypt |
| — | 35 | fix(security): migration md5 → bcrypt PASSWORD_BCRYPT cost=12 |
| `381de3e` | ~10 | fix(admin): suppression ADOdb debug + protection JSON contre pollution |
| `b544819` | ~14 | fix(save/coherence): sauvegarde données + contrôle cohérence mobile |

---

*Document généré le 2026-07-06 · Abdoul Nasser Kailou · kailounasser@gmail.com*  
*Dépôt : https://github.com/NasserKailou/stateduc_mobile · PR #2 · Branch : ak_secure*
