# StatEduc Mobile — Journal des travaux (Flutter)

Branche de développement : `ak_main`  
Dépôt : `https://github.com/NasserKailou/stateduc_mobile`  
Pull Request ouverte : [PR #1](https://github.com/NasserKailou/stateduc_mobile/pull/1)

---

## Table des matières

1. [Architecture générale](#1-architecture-générale)
2. [Authentification et configuration réseau](#2-authentification-et-configuration-réseau)
3. [Chargement de campagne](#3-chargement-de-campagne)
4. [Saisie de données — formulaire WebView](#4-saisie-de-données--formulaire-webview)
5. [Sauvegarde des données](#5-sauvegarde-des-données)
6. [Contrôle de cohérence côté serveur](#6-contrôle-de-cohérence-côté-serveur)
7. [Base de données locale SQLite](#7-base-de-données-locale-sqlite)
8. [Corrections build Android](#8-corrections-build-android)
9. [Corrections réseau (Dio)](#9-corrections-réseau-dio)
10. [Corrections UI](#10-corrections-ui)
11. [Table coherence_rules — schéma réservé](#11-table-coherence_rules--schéma-réservé)

---

## 1. Architecture générale

### Principe
L'application Flutter remplace une application web JavaScript/jQuery originale nommée **StatEduc MEN 2025**. Elle conserve **exactement** la même logique métier, les mêmes endpoints REST PHP, et les mêmes règles de validation, mais dans un contexte mobile Android natif.

### Structure des services
| Fichier | Rôle |
|---|---|
| `lib/services/api_service.dart` | Toutes les communications HTTP avec le serveur PHP (Dio) |
| `lib/services/database_service.dart` | Base SQLite locale via `sqflite` — remplace les 25+ clés localStorage du JS original |
| `lib/providers/data_entry_provider.dart` | State manager ChangeNotifier — gère l'état du formulaire de saisie |
| `lib/providers/campaign_provider.dart` | Chargement et persistance des campagnes |

### Correspondance localStorage → SQLite
L'application JS originale stockait toutes ses données dans `localStorage` avec des clés comme `stm_User`, `stm_Campagnes`, `stm_EtabCollectData_*`. Ces données sont maintenant persistées dans des tables SQLite :

| Clé localStorage originale | Table SQLite |
|---|---|
| `stm_User`, `stm_Year`, `stm_Filter` | `settings` |
| `stm_Campagnes` | `campaigns` |
| `stm_Localisations` | `localisations` |
| `stm_Systems` | `education_systems` |
| `stm_TypeRegroups` | `regroup_types` |
| `stm_Regroups` | `regroups` |
| `stm_Etabs` | `schools` |
| `stm_Status` | `school_statuses` |
| `stm_Questions` | `questions` |
| `stm_Rules` | `validation_rules` |
| `stm_EtabCollectData_{etab}_{qst}[_{filter}]` | `collected_data` |
| *(nouveau)* | `filter_periods` |
| *(réservé)* | `coherence_rules` |

---

## 2. Authentification et configuration réseau

### Fichier : `lib/services/api_service.dart`

#### 2.1 Normalisation de l'URL serveur (`normalizeServerUrl`)
- Ajoute automatiquement `http://` si l'utilisateur saisit une URL sans schéma
- Garantit un **slash final** (obligatoire pour que Dio préserve le chemin de base)
- Raison : Dio 5.x résout `baseUrl='http://host/app'` + `get('/endpoint')` → `http://host/endpoint` (PERD `/app`). Avec slash final + chemin relatif, cela devient correct.

#### 2.2 Authentification HTTP Basic (`authenticate`)
- Endpoint : `GET /user_ident.php/user/{login}/{password}`
- Header `Authorization: Basic base64(login:password)` injecté automatiquement
- Gestion des cas : HTTP 401, 404, `se_message == 'log_ko'`
- Parsing manuel du JSON (responseType plain) pour résister aux Content-Type incorrects du serveur

#### 2.3 Intercepteur d'authentification (`_AuthInjectorInterceptor`)
- Ré-injecte l'en-tête Authorization sur **toutes** les requêtes, y compris les redirections 3xx
- Garantit qu'aucune requête ne part sans authentification

#### 2.4 User-Agent
- `Mozilla/5.0 (Linux; Android 10) StatEduc/1.0` — imite un navigateur pour passer les éventuels filtres serveur

---

## 3. Chargement de campagne

### Fichier : `lib/providers/campaign_provider.dart`

#### 3.1 Chargement complet en une seule opération
Reproduction exacte du flux JavaScript `charge_camp.js` :
1. `GET /user_camp.php/new_camp/{userId}/1` → liste des campagnes disponibles
2. `GET /user_camp.php/reg_camp/{login}/{campId}/1` → entités administratives (regroups)
3. `GET /user_camp.php/typ_reg_camp/{userId}/{campId}/{typeRegroups}` → types de regroups
4. `GET /user_camp.php/etabs_status/` → statuts d'établissements
5. `GET /user_camp.php/etabs_camp/{userId}/{campId}/1` → liste des établissements
6. `GET /user_camp.php/locs_camp/{userId}/{campId}` → localisations (école → regroupement)
7. `GET /user_camp.php/sys_camp/{userId}/{campId}` → secteurs d'éducation
8. Pour chaque secteur : `GET /data_camp.php/theme_camp/{campId}/{sysId}/eng` → questions/thèmes
9. Pour chaque question : `GET /data_camp.php/html_theme_camp/{campId}/{qstId}/eng` → HTML formulaire (deux étapes)
10. `GET /data_camp.php/regle_theme_camp/{qstId}/{sysId}` → règles de validation

#### 3.2 Persistance locale
Chaque étape du chargement persiste en SQLite via `database_service.dart`. En cas de déconnexion ultérieure, toutes les données restent disponibles.

#### 3.3 `idYear` / `codeyear` — code d'année scolaire
- Le serveur PHP gère la session avec `$_SESSION['annee']`
- L'application mobile ne maintient pas de session HTTP
- Solution : `User.codeyear` est lu depuis la réponse d'authentification, transmis en dernier segment de l'URL save (`/{id_annee}`) et reload
- Le serveur reconnaît ce segment et réinjecte la valeur dans `$_SESSION['annee']`

---

## 4. Saisie de données — formulaire WebView

### Fichier : `lib/screens/data_entry/school_data_screen.dart`

#### 4.1 Rendu HTML via WebView
- Le formulaire de saisie est le HTML original du serveur, rendu dans un WebView
- Inject JS bridge pour intercepter les soumissions de formulaire
- Extraction des valeurs saisies via JavaScript avant sauvegarde

#### 4.2 Sélecteur de question
- Les questions sont affichées sous forme de chips colorés
- Couleur : vert = données saisies, gris = vide
- Correction : les chips utilisent maintenant `ChoiceChip` avec couleur sélectionnée correcte (bug précédent : couleur ne changeait pas à la sélection)

#### 4.3 Sélecteur de filtre (période)
- Visible uniquement si la question a `has_filter = 1`
- Change les données affichées pour la période sélectionnée

#### 4.4 Alerte de cohérence post-envoi
- Après un envoi réussi, si le serveur retourne des violations de cohérence, un `AlertDialog` s'affiche listant chaque violation avec son message
- Les violations sont non-bloquantes (l'envoi a déjà eu lieu)

---

## 5. Sauvegarde des données

### Fichier : `lib/services/api_service.dart` — méthode `saveData()`

#### 5.1 Endpoint
```
POST /data_save.php/theme_save/{login}/{campId}/{sysId}/{qstId}/{etabId}/{filter}/0[/{id_annee}]
Content-Type: application/x-www-form-urlencoded
```

#### 5.2 Encodage des champs radio
Problème corrigé : dans l'application JS, les champs radio sont stockés comme `fieldName#optionId = "1"`. La transformation vers le format serveur est :
- Clé `fieldName#optionId` avec valeur `"1"` → envoyé comme `fieldName=optionId`
- Clé `fieldName#optionId` avec valeur `"0"` → non envoyé (option non cochée)

#### 5.3 Substitution `/` → `_slh_`
Le serveur PHP original utilise `str_replace('_slh_', '/', $value)` pour décoder les valeurs. L'application mobile reproduit l'encodage inverse : `strVal.replaceAll('/', '_slh_')`.

#### 5.4 Injection `LOC_REG_0`
La première question doit toujours inclure `&LOC_REG_0={school.idRegroup}` dans le body POST. Ce champ lie l'établissement à son regroupement administratif.

#### 5.5 Segment `id_annee`
Si `user.codeyear` est non vide, il est ajouté comme dernier segment de l'URL. Le serveur PHP dispose d'une route dédiée pour ce cas :
```
/theme_save/{login}/{campId}/{sysId}/{qstId}/{etabId}/{filter}/0/{id_annee}
```

#### 5.6 Timeouts Dio élevés
Les timeouts ont été portés à des valeurs élevées pour les réseaux lents fréquents en contexte africain :
- `connectTimeout` : 60 s
- `receiveTimeout` : 180 s
- `sendTimeout` : 120 s

---

## 6. Contrôle de cohérence côté serveur

> **Important** : Le contrôle de cohérence est **exclusivement côté serveur**. Il n'y a pas de moteur d'évaluation embarqué dans l'application mobile.

### Fichier : `lib/services/api_service.dart` — méthode `checkCoherence()`

#### 6.1 Endpoint
```
GET /data_controle.php/theme_controle/{login}/{campId}/{sysId}/{qstId}/{etabId}/{filter}/{yearCode}
```

#### 6.2 Déclenchement
- Appelé **automatiquement** après chaque `saveData()` réussi
- Non bloquant : si l'appel échoue (réseau), il est silencieusement ignoré

#### 6.3 Réponse serveur
```json
{
  "se_status": 200,
  "se_data": {
    "nb_erreurs": 2,
    "erreurs": [
      {
        "id_regle": 5,
        "id_regle_assoc": 7,
        "message": "Effectif garçons doit être <= effectif total",
        "regle_1": "NB_GARCONS",
        "regle_2": "TOTAL",
        "critere": "<="
      }
    ]
  }
}
```

#### 6.4 Modèle `CoherenceError`
```dart
class CoherenceError {
  final int    idRegle;
  final int    idRegleAssoc;
  final String message;
  final String regle1;     // valeur calculée côté 1
  final String regle2;     // valeur calculée côté 2
  final String critere;    // opérateur de comparaison
}
```

#### 6.5 Affichage
- `DataEntryProvider._coherenceErrors` est peuplé après chaque envoi réussi
- `school_data_screen.dart` affiche un `AlertDialog` si la liste est non vide
- Le getter `hasCoherenceErrors` permet au widget de réagir

---

## 7. Base de données locale SQLite

### Fichier : `lib/services/database_service.dart`

#### 7.1 Versioning
| Version | Description |
|---|---|
| v1 | Toutes les tables de base |
| v2 | Ajout colonne `sort_order` dans `questions` (migration ALTER TABLE) |
| v3 | Ajout table `coherence_rules` (schéma réservé, voir §11) |

#### 7.2 `collected_data` — stockage des saisies
Table centrale qui remplace toutes les clés `stm_EtabCollectData_*` :
```sql
CREATE TABLE collected_data (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  id_camp     TEXT NOT NULL,
  id_etab     TEXT NOT NULL,
  id_qst      TEXT NOT NULL,
  id_filter   TEXT,
  field_name  TEXT NOT NULL,
  field_value TEXT,
  is_sent     INTEGER NOT NULL DEFAULT 0,  -- 1 après envoi réussi
  updated_at  TEXT NOT NULL
);
CREATE UNIQUE INDEX idx_collected_data_key
  ON collected_data (id_camp, id_etab, id_qst, COALESCE(id_filter,''), field_name);
```

#### 7.3 `filter_periods` — périodes de filtre
Stocke les périodes disponibles (ex : Trim1, Trim2, Trim3) pour chaque campagne :
```sql
CREATE TABLE filter_periods (
  id_camp     TEXT NOT NULL,
  id_filter   TEXT NOT NULL,
  lib_filter  TEXT NOT NULL,
  PRIMARY KEY (id_camp, id_filter)
);
```

#### 7.4 CRUD disponibles
Chaque table dispose de méthodes `get*`, `insert*` (batch), et pour `collected_data` : `saveCollectedField`, `saveCollectedData`, `getCollectedData`, `markCollectedDataSent`, `deleteCollectedData`.

---

## 8. Corrections build Android

### Fichier : `android/app/build.gradle`, `pubspec.yaml`

#### 8.1 Suppression de `vibration`
La dépendance `vibration` utilisait Kotlin Gradle Plugin (KGP) incompatible avec AGP 8.x et ne définissait pas `namespace`. Erreur de build résolue en retirant la dépendance.

#### 8.2 Suppression de `flutter_sms`
Même problème : `flutter_sms` causait une erreur AGP 8.x `namespace not specified`. Supprimé.

#### 8.3 Mise à jour `GeneratedPluginRegistrant.java`
Régénéré après les suppressions de plugins.

---

## 9. Corrections réseau (Dio)

### Fichier : `lib/services/api_service.dart`

#### 9.1 Timeouts élevés
Portés à 60 s (connect), 180 s (receive), 120 s (send) pour les connexions lentes.

#### 9.2 Message d'erreur timeout clair
Avant : message d'erreur Dio brut, incompréhensible pour l'utilisateur.  
Après : message en français indiquant que le délai est dépassé et suggérant de vérifier la connexion.

#### 9.3 Gestion `responseType: plain`
Toutes les réponses sont récupérées en plain text puis parsées manuellement en JSON. Évite les exceptions Dio quand le Content-Type serveur est `text/html` au lieu de `application/json`.

---

## 10. Corrections UI

### `lib/screens/data_entry/school_data_screen.dart`

#### 10.1 Couleur des chips
Avant : la couleur sélectionnée ne s'affichait pas.  
Après : `ChoiceChip` avec `selectedColor` et `backgroundColor` distincts.

---

## 11. Table `coherence_rules` — schéma réservé

### Fichier : `lib/services/database_service.dart`

La table `coherence_rules` est **créée dans le schéma SQLite** (version 3) mais **n'est pas utilisée par l'application**. Elle est réservée pour un éventuel usage futur.

Le contrôle de cohérence est géré intégralement par le serveur via `data_controle.php` (voir §6). L'application mobile appelle cet endpoint après chaque sauvegarde et affiche les violations retournées.

```sql
CREATE TABLE IF NOT EXISTS coherence_rules (
  id              INTEGER PRIMARY KEY AUTOINCREMENT,
  id_camp         TEXT NOT NULL,
  id_qst          TEXT NOT NULL,
  id_etab         TEXT NOT NULL,
  id_filter       TEXT,
  id_regle        INTEGER NOT NULL,
  lib_regle       TEXT NOT NULL DEFAULT '',
  sql_regle       TEXT NOT NULL,
  id_assoc        INTEGER NOT NULL,
  id_regle_assoc  INTEGER NOT NULL,
  lib_regle_assoc TEXT NOT NULL DEFAULT '',
  sql_assoc       TEXT NOT NULL,
  critere         TEXT NOT NULL,
  message         TEXT NOT NULL DEFAULT '',
  fetched_at      TEXT NOT NULL
);
```

---

## Commits associés

| SHA | Message |
|---|---|
| `381de3e` | fix(admin): suppress ADOdb debug echo + guard AJAX JSON against output pollution |
| `b544819` | fix(save/coherence): complete data-not-saved fix + coherence control for mobile |
| `7bb5ac3` | serveur add code |
| `6178a0b` | fix(network): raise Dio timeouts + clean saveData timeout error message |
| `0237f45` | fix(ui/data): chip colors, UTF-8 form encoding, saveData radio transform |
| `3e712c5` | 20260528 |
| `654652d` | fix(build): remove unused vibration dep (also had KGP/namespace issues) |
| `0785d3a` | fix(build): remove unused flutter_sms causing AGP 8.x namespace error |
