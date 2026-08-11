# StatEduc Mobile — Restitution Technique

## Bilan des travaux de développement
### UNESCO / MEN Burundi · Projet PAQABU · Auteur : Abdoul Nasser Kailou

---

> **Usage** : Document de compte-rendu technique couvrant l'ensemble des travaux réalisés sur le système StatEduc Mobile.  
> Dépôt GitHub : `https://github.com/NasserKailou/stateduc_mobile`  
> Branches principales : `ak_secure` (serveur PHP + Flutter principal) · `ak_main` (miroir mobile)  
> Pull Request : [PR #2](https://github.com/NasserKailou/stateduc_mobile/pull/2)

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

**Fonctionnement** : lit `DICO_REGLE_THEME` + `DICO_REGLE_THEME_ASSOC`, substitue les variables PHP dans les SQL, retourne les SQL interpolés avec les vraies valeurs (code établissement, code annee).

**Correction décomposition ID composite** : La fonction `rules_resolve_theme_id()` corrige la décomposition de l'ID composite thème. L'ancien code `strlen(id_sector)` donnait un mauvais raw ID → 0 règles retournées. La nouvelle fonction teste plusieurs longueurs de suffixe (1 à 4 digits) et **valide chaque candidat contre la base de données** :
```
composite=10102, sector="2"
  → strip 1 char → candidat "1010" → 0 règles en DB → rejeté
  → strip 2 chars → candidat "101" → N règles en DB → ✅ retenu
```

---

### 3.5 Correctif DNS/NAT : `_sised_local_port()` + Host header

**Contexte** : Dans la topologie réseau MEN Burundi, Fortinet fait NAT sur le port 9191 vers Apache interne (port 80 ou 8080). `data_save.php` appelle `questionnaire_ws.php` via curl interne → curl échoue car le port 9191 n'existe pas sur la VM et le hostname DNS n'est pas résolvable depuis le serveur lui-même.

**Solution définitive** dans `config_app.php` :

```php
function _sised_local_port() {
    $sp = (int)$_SERVER['SERVER_PORT'];
    if ($sp > 0 && @fsockopen('127.0.0.1', $sp, $e, $m, 1)) return $sp;
    foreach ([80, 8080, 8000, 8888] as $p) {
        if (@fsockopen('127.0.0.1', $p, $e, $m, 1)) return $p;
    }
    return 80;
}
$SISED_AURL_INTERNAL = 'http://127.0.0.1:' . _sised_local_port() . $SISED_URL;
$SISED_HOST_HEADER   = $_SERVER['HTTP_HOST'];
```

Dans `data_save.php` et `data_reload.php` :
```php
$curl->setHeader('Host', $GLOBALS['SISED_HOST_HEADER']);
// curl connecte à 127.0.0.1:80 mais présente Host: stateduc.ins.ne:9191
```

---

### 3.6 Correctif INSERT `DICO_FIXE_REGROUPEMENT` (user.class.php)

**Problème** : La méthode `maj_bdd_excel()` de `user.class.php` produisait une erreur SQL Server `42S22` (colonne inconnue `ID_SYSTEMES`) et retournait HTTP 500.

**Cause** : Noms de colonnes incorrects utilisés dans l'INSERT — la table réelle utilise `ID_SYSTEME` (sans S) et requiert des valeurs spécifiques (`ID_STATUS=2`, `ID_TYPE_REGROUP=0`).

**Correction** : Réécriture complète de l'INSERT avec :
- Noms de colonnes corrects issus du schéma SQL Server réel
- Pattern `SELECT TOP 1 ... WHERE` pour obtenir les valeurs de référence
- Vérification doublon PK avant INSERT (`SELECT COUNT(*)`)
- Capture `ErrorMsg()` pour diagnostic précis en cas d'erreur SQL

---

## 4. Application mobile Flutter — Construction complète

L'application mobile a été **entièrement réécrite** en Flutter/Dart, remplaçant l'ancienne application Cordova/JavaScript.

### 4.1 Base de données locale SQLite (`DatabaseService`)

Remplacement complet des 25+ clés `localStorage` de l'application web par une base SQLite structurée (`stateduc.db`).

**Tables créées** :

| Table SQLite | Contenu |
|---|---|
| `settings` | Clé/valeur génériques (User, Year, Filter) |
| `campaigns` | Liste des campagnes téléchargées |
| `education_systems` | Systèmes éducatifs |
| `regroup_types` | Types de regroupements |
| `regroups` | Regroupements administratifs |
| `schools` | Établissements scolaires |
| `school_statuses` | Statuts établissements |
| `localisations` | Liens camp + system + school |
| `questions` | Thèmes / formulaires |
| `form_html` | Cache HTML formulaires |
| `validation_rules` | Règles de validation champs |
| `collected_data` | Données saisies par l'agent |
| `filter_periods` | Périodes de collecte |
| `coherence_rules` | Règles cohérence offline |

**Méthodes clés** :
- `getAllCollectedDataForCoherence()` : charge toutes les périodes d'un formulaire (SUM cross-périodes)
- `getAllCollectedDataForCampEtab()` : charge tous les formulaires de l'école (SUM cross-formulaires)
- `getSchoolsByRegroup()` : 3 stratégies de recherche avec fallbacks robustes

---

### 4.2 Client HTTP (`ApiService`)

Service singleton gérant tous les appels REST vers le serveur.

**Caractéristiques techniques** :
- **Client Dio** avec intercepteur `_AuthInjectorInterceptor` réinjectant `Authorization: Basic` sur chaque requête
- **`_DnsFallbackInterceptor`** : en cas d'échec DNS, remplace hostname par IP numérique mise en cache lors de la dernière authentification réussie et relance la requête automatiquement
- **Timeouts adaptés** : `connectTimeout=60s`, `receiveTimeout=600s`
- **Retry automatique** : 2 re-tentatives avec délai 5s sur timeout/erreur réseau
- **SSL** : certificats auto-signés acceptés (intranet MEN)

**Méthodes principales** :

| Méthode | Endpoint serveur | Rôle |
|---------|-----------------|------|
| `authenticate()` | `user_ident.php` | Connexion utilisateur |
| `getAvailableCampaigns()` | `user_camp.php` | Liste des campagnes |
| `getRegroups()` / `getSchools()` | `user_camp.php` | Données de navigation |
| `getFormHtml()` | `data_camp.php/html_theme_camp` | HTML formulaire |
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
- Stockage/chargement SQLite des données de campagne

#### `DataEntryProvider`
- Saisie de formulaires avec sauvegarde locale automatique
- Debounce 800ms sur `updateField()` → déclenchement cohérence offline
- Double contrôle de cohérence : offline (SQLite) + online (serveur)
- Envoi groupé + relance individuelle

---

### 4.4 Écrans principaux

| Écran | Fichier | Fonctionnalités |
|-------|---------|----------------|
| **Connexion / PIN** | `pin_screen.dart` | Setup PIN, déverrouillage, récupération par question secrète |
| **Onboarding** | `onboarding_screen.dart` | Saisie URL serveur + credentials, test connexion |
| **Liste campagnes** | `campaign_list_screen.dart` | Campagnes disponibles + téléchargées |
| **Chargement campagne** | `load_campaign_screen.dart` | Barre progression 9 étapes + bouton Actualiser |
| **Détail campagne** | `campaign_detail_screen.dart` | Navigation regroupements → établissements |
| **Saisie données** | `school_data_screen.dart` | Formulaire dynamique + indicateurs cohérence inline |
| **Paramètres** | `settings_screen.dart` | Serveur, compte, reset |

---

## 5. Sécurité — Migration MD5 → bcrypt

### 5.1 Contexte

Les mots de passe utilisateurs étaient stockés en **MD5** dans `ADMIN_USERS.PASSWORD` — algorithme cryptographiquement cassé (rainbow tables).

### 5.2 Stratégie de migration

| Aspect | Avant | Après |
|--------|-------|-------|
| Algorithme | `md5($password)` | `password_hash($password, PASSWORD_BCRYPT, ['cost'=>12])` |
| Hash stocké | 32 chars MD5 | `$2y$12$...` 60 chars bcrypt |
| Vérification | `WHERE PASSWORD = md5(input)` | `password_verify(input, $hash_from_db)` |

### 5.3 Gestion des hashes legacy

`valide_user_ws()` gère **4 cas** pour assurer la transition :

```
Cas 1 → Hash bcrypt valide ($2y$, 60 chars) → password_verify() ✅
Cas 2 → Hash MD5 legacy (32 chars hex)      → md5() + AUTO-MIGRATION vers bcrypt ✅
Cas 3 → Hash bcrypt tronqué (<60 chars)     → error_log() + refus (champ DB trop petit) ⚠️
Cas 4 → Format inconnu                      → error_log() + refus sécurisé ⚠️
```

### 5.4 Correctif collation SQL Server (Case Insensitive)

**Problème** : SQL Server avec collation `French_CI_AS` (Case Insensitive) normalisait la casse des `VARCHAR` lors de la lecture via ADOdb. Le hash bcrypt contenant des majuscules/minuscules significatives, la collation altérait le hash → `password_verify()` retournait toujours `false` → HTTP 401 permanent.

**Double correctif** :
1. **PHP** : `SELECT CONVERT(VARCHAR(100), PASSWORD) COLLATE Latin1_General_CS_AS AS PASSWORD`
2. **SQL Server** : `ALTER TABLE ADMIN_USERS ALTER COLUMN PASSWORD VARCHAR(100) COLLATE Latin1_General_CS_AS NOT NULL`

---

## 6. Chaîne de téléchargement de campagne (9 étapes)

### 6.1 Architecture de la chaîne

| Étapes | Endpoint | Auth |
|--------|----------|------|
| 1–6 | `user_camp.php` | ❌ désactivé |
| 7–9 | `data_camp.php` | ✅ actif → `valide_user_ws()` |

### 6.2 Détail des 9 étapes

| Étape | Route | Contenu chargé |
|-------|-------|----------------|
| 1 | `regroup_types_camp/` | Types de regroupements |
| 2 | `regroups_camp/` | Regroupements administratifs |
| 3 | `status_camp/` | Statuts établissements |
| 4 | `etabs_camp/` | Établissements |
| 5 | `locs_camp/` | Localisations |
| 6 | `sys_camp/` | Systèmes éducatifs |
| 7 | `theme_camp/` | Thèmes / formulaires |
| 8 | `html_theme_camp/` | HTML des formulaires |
| 9 | `regle_theme_camp/` | Règles de validation des champs |

### 6.3 Bug critique résolu — Filtre `FRAME <> ''`

Le SQL de la route `theme_camp` contenait `AND (DICO_THEME_SYSTEME.FRAME <> '')` qui masquait tous les thèmes sans fichier `.frame` pré-généré → étape 7 retournait zéro formulaires. **Correction** : suppression du filtre.

### 6.4 Bug résolu — Boucle de tri sans protection

L'algorithme de tri des thèmes par chaîne `PRECEDENT` utilisait une boucle sans garde. Si un élément `PRECEDENT` pointait vers un ID absent (chaîne brisée), la boucle ne terminait jamais → PHP timeout → réponse vide. **Correction** : réécriture avec compteur `$max_iter = $nb * $nb + 1` + ajout des éléments restants en queue.

---

## 7. Contrôle de cohérence — Online et Offline

### 7.1 Dual-mode

| Mode | Déclenchement | Moteur | Latence |
|------|--------------|--------|---------|
| **Offline** | Auto (debounce 800ms après saisie) | `CoherenceEvaluator` Dart | ~50ms |
| **Online** | Manuel (bouton "Vérifier") + avant envoi | `data_controle.php` (SQL Server) | ~2s |

### 7.2 `CoherenceEvaluator` — Moteur offline

**Étapes d'évaluation** :
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

**Correction regex TABLE.FIELD** :
```dart
// Préfixe table optionnel et non-capturant
static final _sumPattern = RegExp(
  r'SUM\s*\(\s*(?:\w+\.)?\s*(\w+)\s*\)',
  caseSensitive: false,
);
// SUM(ELEVES_AGE_NIVEAU_SEXE.FILLES_AGE_NIVEAU) → FILLES_AGE_NIVEAU ✅
```

**Agrégats virtuels** (colonnes de vues DB absentes de `collected_data`) :
```dart
void _injectVirtualAggregates(Map<String, dynamic> values) {
  double total = 0, totalFilles = 0;
  for (final entry in values.entries) {
    final v = double.tryParse(entry.value?.toString() ?? '') ?? 0;
    total += v;
    if (_isFillesField(entry.key)) totalFilles += v;
  }
  values['TOTAL_AGE_NIVEAU']  = total.toString();
  values['FILLES_AGE_NIVEAU'] = totalFilles.toString();
}
```

### 7.3 Injection par SAVEPOINT (mode online)

Le contrôle online utilise des **SAVEPOINTs SQL Server** pour évaluer les règles sur des données temporaires sans jamais les committer :

```sql
SAVE TRANSACTION sp_coherence_check;
  -- Insertion données à vérifier
  -- Exécution règles SQL Server (vues, agrégats, jointures)
  -- Récupération résultats
ROLLBACK TRANSACTION sp_coherence_check;
-- Les données temporaires sont annulées — zéro effet de bord
```

---

## 8. Résolution de bugs complexes (diagnostics)

### 8.1 DNS cache `_DnsFallbackInterceptor`

**Problème** : L'agent collecte les données dans une école hors réseau MEN, puis essaie d'envoyer depuis un réseau 4G où `stateduc.ins.ne` n'est pas résolvable → DioException `Failed host lookup`.

**Solution** : Lors de l'authentification réussie (DNS disponible), l'IP est résolue et persistée dans `SharedPreferences`. L'intercepteur `_DnsFallbackInterceptor` détecte l'erreur DNS et remplace automatiquement le hostname par l'IP cachée :

```dart
class _DnsFallbackInterceptor extends Interceptor {
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    if (err.type == DioExceptionType.connectionError &&
        _isDnsError(err.message) &&
        _cachedServerIp != null) {
      final fallbackOptions = _buildFallbackOptions(err.requestOptions);
      final response = await _service._dio.fetch(fallbackOptions);
      handler.resolve(response);
      return;
    }
    handler.next(err);
  }
}
```

### 8.2 Regex ID composite thème

**Problème** : `rules_resolve_theme_id()` dans `data_rules.php` calculait `strlen(id_sector)=1` pour sector="2", mais le suffixe réel dans l'ID composite était "02" (2 chiffres) → candidat "1010" au lieu de "101" → 0 règles retournées → contrôle offline silencieux.

**Solution** : Test itératif des longueurs de suffixe (1 à 4) avec validation DB à chaque étape.

### 8.3 Données cross-formulaires absentes

**Problème** : La règle de cohérence 493 référençait `DONNEES_ETABLISSEMENT.NB_ELEVES_F` — un champ collecté via un formulaire différent (`id_qst` différent). L'ancienne implémentation ne chargeait que les données du formulaire courant.

**Solution** : Nouvelle méthode `getAllCollectedDataForCampEtab()` chargeant TOUTES les données pour l'école + campagne (tous formulaires, tous filtres), injectées dans la map avec priorité inférieure.

---

## 9. Tableau de synthèse des fichiers modifiés

### Côté serveur PHP

| Fichier | Nature | Modifications |
|---------|--------|--------------|
| `StatEduc_burundi/config_app.php` | Modifié | `_sised_local_port()` + `SISED_HOST_HEADER` (bypass NAT Fortinet) |
| `StatEduc_burundi/common.php` | Modifié | Suppression `$conn_dico->debug = true` |
| `StatEduc_burundi/data_save.php` | Modifié | Route mobile `/:id_annee` + Host header curl |
| `StatEduc_burundi/data_reload.php` | Modifié | Host header curl + timeouts |
| `StatEduc_burundi/data_controle.php` | **Créé** | Contrôle cohérence online (endpoint mobile) |
| `StatEduc_burundi/data_rules.php` | **Créé** | Règles cohérence offline + `rules_resolve_theme_id()` |
| `StatEduc_burundi/questionnaire_ws.php` | Modifié | Routes Slim v2 + `_sised_aurl_internal` |
| `StatEduc_burundi/server-side/classes/metier/user.class.php` | Modifié | bcrypt + `maj_bdd_excel()` INSERT DICO_FIXE_REGROUPEMENT corrigé |
| `StatEduc_burundi/CHANGELOG.md` | **Créé** | Journal des travaux serveur PHP |

### Côté application mobile Flutter

| Fichier | Nature | Modifications |
|---------|--------|--------------|
| `lib/services/api_service.dart` | **Créé** | Service HTTP singleton complet (Dio, auth, retry, `_DnsFallbackInterceptor`) |
| `lib/services/database_service.dart` | **Créé** + amélioré | 14 tables SQLite, CRUD complet, `getAllCollectedDataForCampEtab()` |
| `lib/services/coherence_evaluator.dart` | **Créé** + réécrit | Moteur offline : regex TABLE.FIELD, agrégats virtuels, cross-formulaires |
| `lib/services/auth_service.dart` | **Créé** | Authentification + PIN + question secrète |
| `lib/providers/auth_provider.dart` | **Créé** | État auth + PIN FlutterSecureStorage |
| `lib/providers/campaign_provider.dart` | **Créé** + corrigé | Téléchargement 9 étapes |
| `lib/providers/data_entry_provider.dart` | **Créé** | Saisie, save locale/serveur, cohérence double |
| `lib/models/*.dart` | **Créés** | Campaign, School, Question, User, Regroup, EducationSystem |
| `lib/screens/login/pin_screen.dart` | **Créé** | Setup/déverrouillage/récupération PIN |
| `lib/screens/campaigns/load_campaign_screen.dart` | **Créé** | Progression 9 étapes |
| `lib/screens/campaigns/campaign_list_screen.dart` | **Créé** | Liste campagnes |
| `lib/screens/schools/campaign_detail_screen.dart` | **Créé** | Navigation regroupements → écoles |
| `lib/screens/data_entry/school_data_screen.dart` | **Créé** | Saisie formulaire + indicateurs cohérence |
| `lib/widgets/dynamic_form/dynamic_form_widget.dart` | **Créé** | Rendu HTML formulaires |
| `pubspec.yaml` | **Créé** | Dépendances Flutter |

---

## 10. État final et tests confirmés

### Résultats de tests (device réel)

| Fonctionnalité | Résultat | Notes |
|----------------|----------|-------|
| Connexion utilisateur (bcrypt) | ✅ | `nasser` / `nasser@2026` |
| Téléchargement campagne étapes 1–9 | ✅ | Toutes étapes fonctionnelles |
| Saisie données formulaire | ✅ | |
| Sauvegarde locale SQLite | ✅ | |
| Envoi serveur | ✅ | Avec retry automatique |
| Contrôle cohérence **online** | ✅ | "2 incohérence(s) détectée(s)" confirmé |
| Contrôle cohérence **offline** | ✅ | Règles offline fonctionnelles après correction regex |
| Navigation hors ligne | ✅ | Toutes données en cache SQLite |
| APK release (PlayProtect) | ✅ | Installation sans avertissement |

---

## 11. Points d'attention pour la suite

### 11.1 Migration des utilisateurs MD5 existants

Les utilisateurs avec mots de passe MD5 seront auto-migrés vers bcrypt à leur première connexion. **Action requise** : informer les agents de collecte qu'une réinitialisation peut être nécessaire via `administration.php?val=gestionuser`.

### 11.2 Champ PASSWORD dans Access/SQL Server

Pour les bases Access : vérifier que le champ `PASSWORD` est en `TEXT(255)`.  
Pour SQL Server : exécuter `alter_password_field_sqlserver.sql` pour appliquer `COLLATE Latin1_General_CS_AS`.

### 11.3 Règles de cohérence pour d'autres thèmes

Le moteur offline `CoherenceEvaluator` est générique. Pour les thèmes avec des SQL plus complexes (JOINs, sous-requêtes), la règle sera silencieusement ignorée (comportement conservatif — pas de faux positifs).

### 11.4 Version PHP

Le serveur est en **PHP 7.3.4**. Toutes les corrections sont compatibles. Si une mise à jour vers PHP 8.0+ est planifiée, réviser les usages de `utf8_encode()` et la syntaxe `@session_start()`.

---

## Annexe — Synthèse des corrections

| Composant | Correction | Impact |
|-----------|-----------|--------|
| PHP `user.class.php` | INSERT `DICO_FIXE_REGROUPEMENT` — colonnes SQL Server réelles | Résolution erreur HTTP 500 + SQL 42S22 |
| PHP `config_app.php` | `_sised_local_port()` + `SISED_HOST_HEADER` | Bypass NAT Fortinet (curl interne PHP) |
| PHP `data_save.php` + `data_reload.php` | Host header curl interne | Routing VirtualHost correct |
| PHP `data_rules.php` | `rules_resolve_theme_id()` | Correction décomposition ID composite thème |
| PHP `user.class.php` | bcrypt + collation CS_AS | Sécurité mots de passe + correction collation SQL Server |
| Flutter `api_service.dart` | `_DnsFallbackInterceptor` + cache DNS | Envoi données sur réseau variable (MEN) |
| Flutter `coherence_evaluator.dart` | Regex TABLE.FIELD + agrégats virtuels | Contrôle cohérence offline opérationnel |
| Flutter `database_service.dart` | `getAllCollectedDataForCampEtab()` | Règles cross-formulaires offline |
| Flutter `campaign_provider.dart` | Correction chaîne 9 étapes (filtre FRAME) | Téléchargement campagne complet |

---

*Document rédigé par Abdoul Nasser Kailou — PAQABU / UNESCO — Juillet 2026*  
*Dépôt : https://github.com/NasserKailou/stateduc_mobile · PR #2 · Branche : ak_secure*
