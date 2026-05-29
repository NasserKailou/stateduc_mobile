# CHANGELOG — StatEduc Mobile (Flutter)

Historique complet de toutes les modifications apportées à l'application Flutter StatEduc Mobile.

---

# CHANGELOG — StatEduc Mobile (Flutter)

Historique complet de toutes les modifications apportées à l'application Flutter StatEduc Mobile.

---

## [Unreleased] — 2026-05-29 — Session 3 : fixes données serveur + formulaires

### Fix critique — Données non persistées sur le serveur (`data_save.php`)

**Problème** : L'utilisateur voyait « Données envoyées avec succès » mais le serveur ne montrait aucune donnée.

**Cause racine** : `data_save.php` → `theme_save_handler()` — le relais curl interne vers `questionnaire_ws.php` ne transmettait pas le paramètre `annee`. Sans année en session, `questionnaire_ws.php` ne pouvait pas exécuter les requêtes SQL filtrées par année, causant un retour silencieux sans écriture en base.

**Fichier corrigé** : `StatEduc_MEN_2025/data_save.php`
- Ligne 139 (route web) et ligne 291 (`theme_save_handler`) : ajout de `&annee=$id_year` à l'URL curl vers `questionnaire_ws.php`.
- Avant : `...&type_ent_stat='.$id_camp`
- Après  : `...&type_ent_stat='.$id_camp.'&annee='.$id_year`

### Ajouté — En-tête d'identification au-dessus de chaque formulaire

Affiche les informations complètes de l'établissement en haut de chaque formulaire (comme sur le serveur web) :
- **Année Courante** : ex. « 2024-2025 »
- **Hiérarchie géographique** : ex. « AGADEZ / ADERBISANAT / ADEBISSANAT »
- **Établissement** : Nom, Identifiant, Code Administratif
- **Statut** et **Sous secteur**

Widgets ajoutés dans `school_data_screen.dart` : `_SchoolInfoHeader`, `_InfoRow`, `_InfoChip`.

### Ajouté — Pré-remplissage du formulaire d'identification

Le premier formulaire (thème d'identification) est automatiquement pré-rempli avec les données déjà connues de l'établissement :
- Nom établissement (`NOM_ETABLISSEMENT`, `NOM_ETAB`, `LIB_ETABLISSEMENT`)
- Code administratif (`CODE_ETABLISSEMENT`, `COD_ETAB`, `CODE_ADMIN`)
- Statut (`STATUT`, `LIB_STATUT`)
- Sous-secteur (`SOUS_SECTEUR`, `LIB_SOUS_SECTEUR`)
- Année scolaire (`ANNEE_SCOLAIRE`, `LIB_ANNEE`)

Méthode `_prefillIdentificationFields()` ajoutée dans `DataEntryProvider`.

### Modifié — `DataEntryProvider.initForSchool()` (paramètres étendus)

Nouveaux paramètres optionnels : `codeEtab`, `libyear`, `codeyear`, `libStatus`, `libSubsector`, `adminHierarchy`.
Nouveaux getters publics : `codeEtab`, `libyear`, `codeyear`, `libStatus`, `libSubsector`, `adminHierarchy`.

### Modifié — `School` model

Ajout de deux champs optionnels : `libStatus` (libellé du statut résolu, ex. « Public ») et `libHierarchy` (hiérarchie géographique, ex. « AGADEZ / ADERBISANAT »). Ajout de `copyWith()`.

### Fix — Encodage HTML (ISO-8859-15 → UTF-8)

Correction de l'affichage en Mojibake (« UtilisÃ©e » → « Utilisée »).
`dynamic_form_widget.dart` : ajout de `_preprocessHtml()` qui détecte et corrige le double-encodage Latin-1/UTF-8 avant rendu WebView.

### Fix — `$NUMERO_LOCAL_N` non résolu

Les templates HTML de grille contiennent `$NUMERO_LOCAL_0`, `$NUMERO_LOCAL_1` etc. au lieu de numéros de lignes.
`_preprocessHtml()` les remplace par leur numéro d'ordre affiché (1, 2, 3 …).

### Fix — `didUpdateWidget` dans `DynamicFormWidget`

Rechargement automatique de l'URL WebView lorsque le HTML du formulaire change (navigation entre questions).

- **Icône de lancement** : `assets/icon/icon.png` (2048×2048) utilisée pour générer toutes les densités Android mipmap (`mdpi` 48px, `hdpi` 72px, `xhdpi` 96px, `xxhdpi` 144px, `xxxhdpi` 192px) via script Python/Pillow.
- **Splash screen** : `drawable/splash_logo.png` (512×512) générée depuis `icon.png`. `launch_background.xml` mis à jour pour afficher ce logo centré sur fond bleu `#1565C0`.
- **`ic_launcher_round`** dans tous les dossiers mipmap (Android 7.1+).
- `android:roundIcon="@mipmap/ic_launcher_round"` dans `AndroidManifest.xml`.

### Modifié
- `drawable/launch_background.xml` : référence `@drawable/splash_logo` (512px) au lieu de `@mipmap/ic_launcher` (trop petit pour un splash).

### Fix supplémentaire — navigation regroups (2e round)

**Problème identifié après tests** : `_buildEmptyState` apparaissait **immédiatement après** le clic sur "Education de Base" (avant même de naviguer dans un sous-regroup).

**Cause** : `getChildRegroups(idCamp, null)` ne retournait aucun regroup racine. Deux sous-cas :
1. Les regroups racines ont `id_parent_regp = '-1'` (string) au lieu de `NULL` — la requête `IS NULL` ne les trouve pas.
2. Les regroups racines ont `id_parent_regp = ''` (chaîne vide) — même problème.

**Corrections — `lib/services/database_service.dart`** :
- `getChildRegroups()` : si la requête `IS NULL` retourne 0, tente une requête de fallback `OR '-1' OR ''` pour les cas de mauvais stockage.
- `getChildRegroups()` : si toujours 0, retourne **tous** les regroups de la campagne (last resort).
- `getSchoolsByRegroup()` : ajout du sentinel `'__all__'` — quand `idRegp == '__all__'`, retourne tous les établissements de la campagne directement (court-circuit Strategy 1 & 2).
- Logs `[DB]` ajoutés dans `getChildRegroups()` pour chaque chemin (IS NULL, fallback -1/empty, last resort).

**Corrections — `lib/providers/campaign_provider.dart`** :
- `selectSystem()` : si `_loadRegroups(null)` retourne 0 regroups, bascule automatiquement en `_loadSchoolsForRegroup('__all__')` → affiche tous les établissements de la campagne directement.
- `_loadRegroups()` : ajout log `[Nav]` avec le nombre de regroups retournés.

---

## [1.0.2] — 2026-05-29 — fix(schools): triple-strategy fallback

### Problème
Après chargement d'une campagne, cliquer sur un système éducatif (ex : **Education de Base** sous **MOBILE**) affichait `_buildEmptyState` — "Aucun établissement trouvé pour ce regroupement." — même avec ≥ 4 établissements chargés.

### Cause racine
`getSchoolsByRegroup()` cherchait uniquement via `localisations.regroups_json`. Or le serveur `locs_camp` ne stocke que les IDs de la **chaîne directe** de l'utilisateur (feuilles + parents via `ID_REGROUP_PARENTS`), pas tous les nœuds intermédiaires de l'arbre de navigation. Quand l'utilisateur clique sur un nœud intermédiaire absent de `regroups_json`, la méthode retournait `[]`.

### Corrections — `lib/services/database_service.dart`
Ajout d'un import `package:flutter/foundation.dart` pour `debugPrint`.

**Triple stratégie dans `getSchoolsByRegroup()`** :

| Stratégie | Mécanisme | Cas couvert |
|-----------|-----------|-------------|
| **Strategy 1** *(existante)* | `localisations.regroups_json` contient `idRegp` | Chaîne locs complète — cas nominal |
| **Strategy 2** *(nouveau fallback)* | `schools.id_regroup = idRegp` direct SQL | Nœud intermédiaire absent de la chaîne locs |
| **Strategy 3** *(last resort)* | Tous les établissements de la campagne | Aucune correspondance → jamais d'écran vide |

**Logs debug `[DB]`** ajoutés :
- Nombre de lignes `localisations` pour `(id_camp, id_system)` — détecte mismatch `id_system`
- Échantillon `regroups_json` du premier enregistrement
- Résultats quantifiés pour chaque stratégie

### Corrections — `lib/providers/campaign_provider.dart`
**Logs debug `[Nav]`** ajoutés dans :
- `navigateIntoRegroup()` : affiche le `idRegp` cliqué, le nombre d'enfants détectés, et si on bascule en mode leaf
- `_loadSchoolsForRegroup()` : affiche le nombre d'établissements retournés

Ces logs sont visibles via `flutter logs` sur l'appareil physique pour diagnostics terrain.

---

## [1.0.1] — 2026-05-28 — fix(ssl): accept self-signed certificates

### Problème
L'écran "Charger une campagne" affichait **"Erreur réseau : The connection errored: Software caused connection abort"** malgré une connexion réseau stable.

### Cause racine — triple blocage SSL
1. `network_security_config.xml` contenait des entrées CIDR invalides (`<domain>192.168.1.0/24</domain>`) bloquant tout le trafic HTTP.
2. Dart/Flutter utilise son propre moteur TLS (BoringSSL) indépendamment d'Android — rejetait les certificats auto-signés du serveur intranet.
3. Absence de `HttpOverrides` global — les instances `HttpClient` hors Dio n'étaient pas configurées.

### Corrections

#### `android/app/src/main/res/xml/network_security_config.xml`
Remplacé la configuration invalide par un `base-config` permissif :
```xml
<network-security-config>
    <base-config cleartextTrafficPermitted="true">
        <trust-anchors>
            <certificates src="system"/>
            <certificates src="user"/>
        </trust-anchors>
    </base-config>
</network-security-config>
```

#### `lib/services/api_service.dart`
Ajout imports `dart:io` et `package:dio/io.dart`.
Override de l'adaptateur HTTP Dio pour accepter les certificats auto-signés :
```dart
(_dio.httpClientAdapter as IOHttpClientAdapter).createHttpClient = () {
  final client = HttpClient();
  client.badCertificateCallback = (cert, host, port) => true;
  return client;
};
```

#### `lib/main.dart`
Ajout d'un `HttpOverrides` global couvrant toutes les instances `HttpClient` hors Dio :
```dart
class _TrustAllCertificates extends HttpOverrides {
  @override
  HttpClient createHttpClient(SecurityContext? context) {
    return super.createHttpClient(context)
      ..badCertificateCallback = (cert, host, port) => true;
  }
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  HttpOverrides.global = _TrustAllCertificates();
  ...
}
```

---

## [1.0.0] — 2026-05-22 à 2026-05-28 — Réécriture complète Flutter

### Vue d'ensemble
Réécriture complète de l'application mobile StatEduc depuis Cordova/JavaScript vers Flutter/Dart. L'application originale utilisait `localStorage` et des appels AJAX jQuery ; la nouvelle version utilise SQLite (sqflite), Provider pour la gestion d'état, Dio pour les requêtes HTTP, et une architecture propre MVC/Provider.

### Architecture générale

#### Modèles (`lib/models/`)
- `User` — utilisateur authentifié (id, login, prénom, nom, année, filtres)
- `Campaign` — campagne de collecte (id, nom, dates, statut, typeRegroups CSV)
- `Regroup` / `RegroupType` — arbre de regroupements administratifs
- `School` / `SchoolStatus` — établissements scolaires
- `EducationSystem` — systèmes éducatifs (MOBILE, Education de Base, etc.)
- `Question` — thèmes de collecte avec règles de validation
- `Localisation` — liaisons école ↔ système ↔ chaîne de regroupements

#### Services (`lib/services/`)
- `ApiService` — singleton Dio avec Basic Auth, intercepteurs de log, SSL bypass, retry
- `DatabaseService` — singleton SQLite remplaçant les 25+ clés `localStorage` originales
- `AuthService` — authentification + stockage sécurisé des credentials (flutter_secure_storage)
- `CoherenceEvaluator` — moteur d'évaluation offline des règles de cohérence

#### Providers (`lib/providers/`)
- `CampaignProvider` — gestion des campagnes, navigation hiérarchique regroupements → établissements, téléchargement en 9 étapes (regroups → types → statuts → schools → locs → systems → formulaires + règles)
- `DataEntryProvider` — saisie de données par établissement, sauvegarde locale + synchronisation serveur, cohérence offline

#### Écrans (`lib/screens/`)
- `SplashScreen` — écran de démarrage avec vérification session
- `OnboardingScreen` — configuration URL serveur + login
- `LoginScreen` — authentification
- `CampaignsScreen` — liste des campagnes téléchargées
- `LoadCampaignScreen` — chargement d'une nouvelle campagne (9 étapes avec progress bar)
- `CampaignDetailScreen` — sélecteur de système éducatif → navigation hiérarchique → liste établissements
- `SchoolDataScreen` — formulaire de saisie par établissement (rendu HTML WebView + bannière cohérence)

### Schéma SQLite (version 3)
Tables créées : `settings`, `campaigns`, `education_systems`, `regroup_types`, `regroups`, `school_statuses`, `schools`, `localisations`, `questions`, `form_html`, `validation_rules`, `coherence_rules`, `collected_data`, `filter_periods`

### Fonctionnalités migrées depuis JavaScript original
- Authentification Basic Auth (charge_camp.js, users.js)
- Chargement campagne multi-étapes avec barre de progression (charge_camp.js — stmChargeCamp)
- Navigation hiérarchique regroupements (page_camp.js — displayRegroups, displayFinalRegroupEtabs)
- Affichage liste établissements filtrés par regroupement et système
- Rendu formulaires HTML via WebView (deux requêtes authentifiées)
- Sauvegarde données : POST multipart vers `/data_camp.php/save_data/` (page_etab.js)
- Règles de cohérence offline : évaluation SQL côté client (data_rules.php)
- Indicateur connectivité réseau (connectivity_plus)
- Stockage PIN/credentials sécurisé (flutter_secure_storage)

### Build Android
- Gradle 8.14.x, AGP 8.11.1, Kotlin 2.2.20, compileSdk 36
- Dépendances : dio 5.7, provider 6.1, sqflite 2.3, flutter_secure_storage 9.2, webview_flutter 4.10
- Signing : keystore configuré dans `key.properties`

### Corrections Build
- Suppression `flutter_sms` (namespace AGP 8.x incompatible)
- Suppression `vibration` (incompatibilité KGP)
- Remplacement `flutter_html` (beta cassée) par `webview_flutter` pour le rendu formulaires
- Fix `GeneratedPluginRegistrant.java` après changement plugins
- Upgrade Kotlin 2.0.21 → 2.1.0 → 2.2.20 pour compatibilité AGP 8.11.1
- Fix `debugPrint` string interpolation (erreur Dart)
- Fix `ValidationRule.validate()` méthode manquante
- Fix `ApiService` singleton — instance partagée AuthService + CampaignProvider
- Fix timeouts Dio : connectTimeout 60s, receiveTimeout 180s, sendTimeout 120s

### Fix spinner infini
Le `StreamBuilder` de chargement n'avait pas de condition de terminaison quand le step atteignait 100%. Remplacé par un `Consumer<CampaignProvider>` qui lit `isLoadingCampaign` directement.

### Fix formulaires
- Encodage UTF-8 des paramètres POST
- Transformation radio buttons : `{"radio_name": "value"}` → `"radio_name=value"` (format FormData)
- Authentification sur la seconde requête HTML (fetch URL → fetch contenu)

### Fix navigation
- `CampaignDetailScreen` : machine à 5 états (`selectedSystem == null` / `isNavigating` / `regroups` / `schools` / `empty`) pour éviter le spinner infini
- `navigateUpRegroup` : recalcul du niveau correct en remontant le breadcrumb

---

## Notes techniques

### Endpoint mapping (JS → Flutter)
| Endpoint | Paramètre clé | Notes |
|----------|---------------|-------|
| `new_camp/{userId}/1` | `currentUser.id` | Liste des campagnes disponibles |
| `reg_camp/{login}/{campId}/1` | `currentUser.login` ← LOGIN, pas ID ! | Regroupements |
| `etabs_camp/{userId}/{campId}/1` | `currentUser.id` | Établissements |
| `locs_camp/{userId}/{campId}` | `currentUser.id` | Localisations |
| `sys_camp/{userId}/{campId}` | `currentUser.id` | Systèmes éducatifs |
| `theme_camp/{campId}/{sysId}/eng` | — | Questions/formulaires |
| `regle_theme_camp/{qstId}/{sysId}` | — | Règles de cohérence |
| `save_data/{campId}/{etabId}/{sysId}/{qstId}/{filterId}` | — | Sauvegarde saisie |

### Format `locs_camp` (serveur)
```json
{ "idloc": ..., "idcamp": "...", "idsys": "...",
  "regroups": "id1,id2,...",  // CSV — IDs chaîne utilisateur seulement
  "etabs": "id1,id2,..." }    // CSV — établissements filtrés
```
⚠ `regroups` contient uniquement la chaîne directe de l'utilisateur, pas tous les nœuds de navigation.

### Format `reg_camp` (serveur → client)
```json
{ "id": "...", "nom": "...", "type": "...", "parentid": "-1" }
```
`parentid == "-1"` → racine → stocké `NULL` dans SQLite.
