# Architecture Technique — StatEduc Mobile

> **Projet** : StatEduc Mobile — Application de collecte de données éducatives  
> **Version document** : 1.0 — Session 21  
> **Date** : 2026-06-22  
> **Branche Git** : `ak_main`  
> **Auteur** : Équipe StatEduc / Sessions AI-assisted 1–21

---

## Table des matières

1. [Vue d'ensemble](#1-vue-densemble)
2. [Architecture applicative](#2-architecture-applicative)
3. [Application mobile Flutter](#3-application-mobile-flutter)
4. [Inventaire des dépendances Flutter](#4-inventaire-des-dépendances-flutter)
5. [Application serveur PHP](#5-application-serveur-php)
6. [Communication mobile ↔ serveur](#6-communication-mobile--serveur)
7. [Configuration et déploiement](#7-configuration-et-déploiement)
8. [Historique des sessions et correctifs](#8-historique-des-sessions-et-correctifs)

---

## 1. Vue d'ensemble

### 1.1 Objectif du projet

**StatEduc Mobile** est l'application mobile officielle du Ministère de l'Éducation Nationale du Burundi (MEN) pour la **collecte de données statistiques éducatives**. Elle permet aux agents de collecte (inspecteurs, collecteurs de terrain) de :

- Télécharger les **campagnes de collecte** qui leur sont assignées depuis un serveur central ;
- Naviguer dans la **hiérarchie administrative** (régions → départements → communes → établissements) ;
- Saisir les données statistiques de chaque établissement scolaire via des **formulaires dynamiques** ;
- Travailler **hors ligne** (mode déconnecté) avec persistance locale SQLite ;
- **Synchroniser** les données saisies vers le serveur dès qu'une connexion réseau est disponible ;
- Recevoir un retour immédiat sur la **cohérence des données** (validation offline et serveur).

### 1.2 Périmètre fonctionnel

| Fonctionnalité | Description |
|---|---|
| Authentification sécurisée | PIN (4–8 chiffres) + question de sécurité, credentials serveur chiffrés |
| Téléchargement de campagnes | 9 étapes séquentielles (regroups, types, statuts, établissements, localisations, systèmes, questions, HTML, règles) |
| Navigation hiérarchique | Drill-down avec fil d'Ariane (système éducatif → regroupements → établissements) |
| Saisie de données | Formulaires HTML dynamiques dans WebView, pré-remplissage depuis serveur ou SQLite |
| Mode hors ligne | SQLite local, synchronisation différée, indicateur d'état de connectivité |
| Cohérence offline | Moteur d'évaluation local (`CoherenceEvaluator`) avec debounce 800 ms |
| Cohérence serveur | Contrôle post-envoi via `data_controle.php` |
| Envoi multi-formulaires | Envoi global par établissement ou par campagne complète |
| Gestion des erreurs | Retry automatique (3 tentatives), messages d'erreur en français |

### 1.3 Architecture globale

```
┌─────────────────────────────────────────────────────────────────────┐
│                    APPLICATION MOBILE FLUTTER                        │
│                                                                      │
│  ┌──────────────┐  ┌───────────────┐  ┌────────────────────────┐   │
│  │  Présentation │  │   Providers   │  │       Services         │   │
│  │  (Screens +  │  │  (State Mgmt) │  │  ┌───────────────────┐ │   │
│  │   Widgets)   │◄─┤  - Auth       │  │  │   ApiService      │ │   │
│  │              │  │  - Campaign   │  │  │   (Dio HTTP)      │ │   │
│  │  Splash      │  │  - DataEntry  ├──┼─►│   BasicAuth       │ │   │
│  │  Pin/Login   │  │               │  │  └───────────────────┘ │   │
│  │  CampList    │  │               │  │  ┌───────────────────┐ │   │
│  │  CampDetail  │  │               │  │  │  DatabaseService  │ │   │
│  │  SchoolData  │  │               ├──┼─►│  (SQLite/sqflite) │ │   │
│  │  Settings    │  │               │  │  └───────────────────┘ │   │
│  └──────────────┘  └───────────────┘  │  ┌───────────────────┐ │   │
│                                        │  │   AuthService     │ │   │
│                                        │  │  (SecureStorage)  │ │   │
│                                        │  └───────────────────┘ │   │
│                                        │  ┌───────────────────┐ │   │
│                                        │  │CoherenceEvaluator │ │   │
│                                        │  └───────────────────┘ │   │
│                                        └────────────────────────┘   │
└──────────────────────────┬──────────────────────────────────────────┘
                           │ HTTPS / HTTP Basic Auth (REST/JSON)
                           │ Headers: Authorization: Basic base64(login:pwd)
                           │
┌──────────────────────────▼──────────────────────────────────────────┐
│                    SERVEUR PHP (StatEduc MEN 2025)                   │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │                 Slim v2 Framework (routing REST)              │   │
│  │                + HttpAuth Middleware (Basic Auth)             │   │
│  └──────────┬──────────────────────────────────────────────────┘    │
│             │                                                        │
│  ┌──────────▼──────────────────────────────────────────────────┐   │
│  │              Endpoints Web Services                          │   │
│  │  user_ident.php   → /user/:login/:password                   │   │
│  │  user_camp.php    → /new_camp/ /sys_camp/ /etabs_camp/ ...   │   │
│  │  data_camp.php    → /theme_camp/                             │   │
│  │  data_save.php    → /theme_save/                             │   │
│  │  data_rules.php   → /theme_rules/                            │   │
│  │  data_controle.php → /theme_controle/                        │   │
│  │  data_reload.php  → /theme_data/                             │   │
│  └──────────┬──────────────────────────────────────────────────┘   │
│             │                                                        │
│  ┌──────────▼────────────────┐   ┌───────────────────────────────┐ │
│  │  conn_dico (MS Access)    │   │  conn (Oracle / SQL Server /  │ │
│  │  dico_DB.mdb / .accdb     │   │       MySQL)                  │ │
│  │  - Configuration          │   │  - Données collectées         │ │
│  │  - Règles de cohérence    │   │  - Établissements             │ │
│  │  - Nomenclatures/Dico     │   │  - Utilisateurs               │ │
│  │  - Paramètres globaux     │   │  - Campagnes                  │ │
│  └───────────────────────────┘   └───────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────────┘
```

---

## 2. Architecture applicative

### 2.1 Patterns architecturaux

#### MVVM + Provider (Flutter)

L'application Flutter suit le pattern **MVVM (Model-View-ViewModel)** implémenté avec le package `provider` :

| Couche MVVM | Rôle | Implémentation |
|---|---|---|
| **Model** | Données métier | `lib/models/*.dart` |
| **View** | Interfaces utilisateur | `lib/screens/*.dart` + `lib/widgets/*.dart` |
| **ViewModel** | Logique de présentation + état | `lib/providers/*.dart` (ChangeNotifier) |

Les providers servent de ViewModels : ils exposent l'état à l'UI via `notifyListeners()` et délèguent la logique métier aux services.

#### Repository Pattern (Flutter)

Les services (`ApiService`, `DatabaseService`, `AuthService`) implémentent un **Repository Pattern** qui abstrait les sources de données :

```
UI (Screen) → Provider (ViewModel) → Service (Repository) → Source (SQLite / HTTP)
```

- `DatabaseService` est le repository SQLite : toutes les opérations de persistance locale passent par lui.
- `ApiService` est le repository HTTP : tous les appels réseau passent par lui.
- `AuthService` est le repository d'authentification : toute gestion de credentials passe par lui.

#### Singleton Services

Les trois services sont des **singletons** instanciés une fois dans le `MultiProvider` de `main.dart` et injectés dans les providers :

```dart
// lib/main.dart
MultiProvider(
  providers: [
    Provider<DatabaseService>(create: (_) => DatabaseService()),
    Provider<ApiService>(create: (_) => ApiService()),
    Provider<AuthService>(create: (_) => AuthService()),
    ChangeNotifierProxyProvider<AuthService, AuthProvider>(
      create: (ctx) => AuthProvider(
        authService: ctx.read<AuthService>(),
        apiService: ctx.read<ApiService>(),
      ),
      update: (_, auth, prev) => prev!..updateAuthService(auth),
    ),
    ChangeNotifierProxyProvider2<DatabaseService, ApiService, CampaignProvider>(
      create: (ctx) => CampaignProvider(
        db: ctx.read<DatabaseService>(),
        api: ctx.read<ApiService>(),
      ),
      update: (_, db, api, prev) => prev!,
    ),
    ChangeNotifierProxyProvider2<DatabaseService, ApiService, DataEntryProvider>(
      create: (ctx) => DataEntryProvider(
        db: ctx.read<DatabaseService>(),
        api: ctx.read<ApiService>(),
      ),
      update: (_, db, api, prev) => prev!,
    ),
  ],
  ...
)
```

#### MVC light (PHP)

Côté serveur, l'architecture PHP suit un **MVC allégé** :

| Couche | Rôle | Implémentation |
|---|---|---|
| **Router/Controller** | Routing REST + logique d'endpoint | `*.php` (user_ident, user_camp, data_save, …) |
| **Model/Service** | Logique métier complexe | `server-side/classes/metier/*.class.php` |
| **Data Access** | Accès base de données | ADODB abstraction layer |

### 2.2 Structure des dossiers

#### Flutter — `stateduc_flutter/`

```
stateduc_flutter/
├── pubspec.yaml                    # Dépendances
├── CHANGELOG.md                    # Journal des modifications
├── architecture_technique.md       # Ce document
└── lib/
    ├── main.dart                   # Point d'entrée, MultiProvider, MaterialApp
    ├── models/                     # Modèles de données
    │   ├── campaign.dart           # Campaign, + Campaign.fromJson
    │   ├── education_system.dart   # EducationSystem
    │   ├── question.dart           # Question, ValidationRule, CollectedData
    │   ├── regroup.dart            # Regroup, RegroupType, Localisation, SchoolStatus
    │   ├── school.dart             # School + Localisation
    │   └── user.dart               # User, FilterPeriod
    ├── providers/                  # ViewModels (ChangeNotifier)
    │   ├── auth_provider.dart      # AuthState machine, login/PIN flows
    │   ├── campaign_provider.dart  # Campagnes, navigation hiérarchique
    │   └── data_entry_provider.dart # Saisie, sauvegarde, envoi, cohérence
    ├── services/                   # Repositories (singletons)
    │   ├── api_service.dart        # Tous appels HTTP (Dio + BasicAuth)
    │   ├── auth_service.dart       # Credentials (flutter_secure_storage)
    │   ├── coherence_evaluator.dart # Moteur cohérence offline
    │   └── database_service.dart   # CRUD SQLite (sqflite)
    ├── screens/                    # Écrans (Views)
    │   ├── splash/
    │   │   └── splash_screen.dart  # Splash animé → routing initial
    │   ├── onboarding/
    │   │   └── onboarding_screen.dart # 5 pages (premier lancement)
    │   ├── login/
    │   │   └── pin_screen.dart     # PIN pad + setup + server login
    │   ├── campaigns/
    │   │   ├── campaign_list_screen.dart  # Liste campagnes locales
    │   │   └── load_campaign_screen.dart  # Téléchargement campagne (9 étapes)
    │   ├── schools/
    │   │   └── campaign_detail_screen.dart # Navigation hiérarchique
    │   ├── data_entry/
    │   │   └── school_data_screen.dart    # Saisie formulaire établissement
    │   └── settings/
    │       └── settings_screen.dart       # URL serveur, PIN, question sécurité
    └── widgets/                    # Composants réutilisables
        ├── common/
        │   ├── confirm_dialog.dart        # Dialogue de confirmation générique
        │   ├── connection_status_widget.dart # Indicateur connectivité
        │   └── loading_overlay.dart       # Overlay de chargement
        └── dynamic_form/
            └── dynamic_form_widget.dart   # WebView + bridge JS
```

#### PHP — `StatEduc_MEN_2025/`

```
StatEduc_MEN_2025/
├── config.php / config_app.php    # Variables de chemin globales ($SISED_PATH, etc.)
├── constants.php                  # Constantes PHP du projet
├── params.php                     # Paramètres métier ($GLOBALS['PARAM'])
├── params_ws.php                  # Constantes WS (se_status, se_data, 200/400, etc.)
├── params_sys.php                 # Paramètres système
├── connexion.php                  # Chaînes de connexion DB (format CSV commenté)
├── common_ws.php                  # Bootstrap WS (Slim, ADODB, session, conn_dico)
├── common.php                     # Bootstrap web classique (sessions, auth)
├── index.php                      # Page de login web (HTML)
│
├── user_ident.php                 # WS auth : /user/:login/:password
├── user_camp.php                  # WS campagnes : /new_camp/ /sys_camp/ /etabs_camp/ ...
├── data_camp.php                  # WS questions : /theme_camp/
├── data_save.php                  # WS sauvegarde : /theme_save/ (POST)
├── data_rules.php                 # WS règles cohérence : /theme_rules/
├── data_controle.php              # WS contrôle cohérence : /theme_controle/
├── data_reload.php                # WS rechargement : /theme_data/
│
└── server-side/
    ├── dico_DB.mdb / dico_DB.accdb  # Base MS Access (dictionnaire/config)
    ├── classes/
    │   ├── adodb/                   # ADODB ORM (abstraction multi-BD)
    │   ├── connexion.class.php      # Gestion connexions DB (lit connexion.php)
    │   └── metier/
    │       ├── controle_theme_batch.class.php  # Moteur cohérence serveur
    │       └── theme_manager_ws.class.php      # Logique métier thèmes/formulaires
    └── include/
        ├── web_services/
        │   └── HttpAuth.php        # Middleware BasicAuth pour Slim
        └── administration/
            └── ...                 # Scripts d'administration
```

### 2.3 Justification des choix

| Choix | Justification |
|---|---|
| **Flutter** | Framework cross-platform (Android/iOS), performances natives, WebView intégré pour les formulaires HTML legacy |
| **Provider** | Solution officielle recommandée par l'équipe Flutter, légère, suffisante pour l'échelle du projet |
| **Slim v2 PHP** | Framework micro-REST léger, déjà en place côté serveur legacy, supporte le routage REST simple sans overhead |
| **ADODB** | Abstraction multi-BDD (MS Access, Oracle, SQL Server, MySQL) — critique car le serveur utilise deux BD de types différents |
| **SQLite/sqflite** | Base de données mobile la plus stable sur Flutter, support du mode hors ligne, PRAGMA foreign_keys pour intégrité |
| **flutter_secure_storage** | Chiffrement natif via Keystore (Android) / Keychain (iOS) pour les données sensibles (PIN, credentials) |
| **Dio** | Client HTTP avec intercepteurs (BasicAuth automatique), timeouts configurables, retry facile |

---

## 3. Application mobile Flutter

### 3.1 Point d'entrée — `lib/main.dart`

```dart
// Contournement SSL auto-signé (environnement de développement/test)
class _TrustAllCertificates extends HttpOverrides {
  @override
  HttpClient createHttpClient(SecurityContext? context) {
    return super.createHttpClient(context)
      ..badCertificateCallback = (_, __, ___) => true;
  }
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  HttpOverrides.global = _TrustAllCertificates();
  // Pré-initialisation SQLite avant le premier build
  await DatabaseService().database;
  runApp(const StatEducApp());
}
```

Le `MaterialApp` utilise le thème **Material 3** avec la couleur seed `0xFF1565C0` (bleu StatEduc).

### 3.2 Navigation et routing

La navigation est **impérative** (pas de routing nommé) via `Navigator.push/pushReplacement/pushAndRemoveUntil` :

```
SplashScreen (2.5s)
    │
    ├─ [premier lancement] → OnboardingScreen (5 pages) → PinScreen
    │
    └─ [relancement] → PinScreen
                           │
            ┌──────────────┴──────────────────────┐
            │ AuthState                           │
            ├─ firstTimeSetup → [PIN setup form]  │
            ├─ needsServerLogin → [Server login]  │
            └─ pinRequired → [PIN pad]            │
                           │                      │
                     [isLoggedIn]                 │
                           │                      │
                    CampaignListScreen             │
                           │                      │
              ┌────────────┴────────────┐         │
              │ FloatingActionButton    │ List     │
              ▼                        ▼          │
     LoadCampaignScreen      CampaignDetailScreen  │
     (9 étapes download)     (drill-down)          │
                                       │          │
                               SchoolDataScreen   │
                               (saisie + WebView) │
                                                  │
                           SettingsScreen (tabs)  │
```

### 3.3 Gestion d'état

#### AuthProvider — machine à états d'authentification

```dart
// lib/providers/auth_provider.dart
enum AuthState { unknown, firstTimeSetup, needsServerLogin, pinRequired, loggedIn }
```

| État | Description | Transition suivante |
|---|---|---|
| `unknown` | Initialisation en cours | → `firstTimeSetup` ou `pinRequired` |
| `firstTimeSetup` | Aucun PIN configuré | → `needsServerLogin` après setupPin() |
| `needsServerLogin` | PIN OK, pas de session serveur | → `pinRequired` après loginToServer() |
| `pinRequired` | Tout configuré, PIN requis | → `loggedIn` après unlockWithPin() |
| `loggedIn` | Utilisateur authentifié | → `pinRequired` après logout() |

#### CampaignProvider — état de navigation

Gère l'état de la hiérarchie de navigation (systèmes → regroupements → établissements) et le workflow de téléchargement 9 étapes.

#### DataEntryProvider — état du formulaire

Gère le cycle de vie complet de la saisie pour un établissement : chargement, modification, sauvegarde, envoi, cohérence.

### 3.4 Gestion des données locales

#### SQLite — `DatabaseService`

Base de données `stateduc.db` (v3) avec 15+ tables :

```
settings          → clé/valeur (paramètres app)
campaigns         → campagnes téléchargées
education_systems → systèmes éducatifs par campagne
regroup_types     → types de regroupements administratifs
regroups          → regroupements (hiérarchie administrative)
school_statuses   → statuts établissements (Public, Privé, Communautaire)
schools           → établissements scolaires
localisations     → liens étab ↔ système ↔ regroupements (JSON array)
questions         → thèmes/formulaires par système éducatif
form_html         → cache HTML des formulaires (évite re-téléchargement)
validation_rules  → règles de validation champs
filter_periods    → périodes de filtre (ex. T1, T2, Annuel)
collected_data    → données saisies (clé unique: camp+etab+qst+filter+field)
coherence_rules   → règles de cohérence offline (fetched depuis data_rules.php)
```

**Migrations** :
- v1 → v2 : ajout colonne `sort_order` dans `questions`
- v2 → v3 : création table `coherence_rules` + index

**Clé unique** de `collected_data` :
```sql
CREATE UNIQUE INDEX idx_collected_data_key
  ON collected_data (id_camp, id_etab, id_qst, COALESCE(id_filter,''), field_name)
```

### 3.5 Mode hors ligne

Le mode hors ligne repose sur :

1. **Cache local complet** : tout le contenu téléchargé lors du chargement de campagne est persisté dans SQLite (HTML, questions, règles, données établissements).
2. **Sauvegarde locale avant envoi** : `saveLocally()` persiste dans SQLite avec `is_sent=0` ; `sendToServer()` met à jour `is_sent=1` en cas de succès.
3. **Flag `is_sent`** : permet de détecter les données non synchronisées.
4. **Cohérence offline** : `CoherenceEvaluator` évalue les règles stockées localement sans appel serveur.
5. **Indicateur connectivité** : `connectivity_plus` détecte les changements de réseau.

### 3.6 Gestion des appels réseau — `ApiService`

Singleton Dio configuré dans `lib/services/api_service.dart` :

```dart
// Configuration Dio
_dio = Dio(BaseOptions(
  baseUrl: serverUrl,
  connectTimeout: const Duration(seconds: 60),
  receiveTimeout: const Duration(seconds: 600),  // 600s pour questionnaire_ws.php
  sendTimeout: null,  // désactivé — évite timeouts prématurés (session 19)
  headers: {'Accept': 'application/json'},
));

// Intercepteur Basic Auth automatique
_dio.interceptors.add(_AuthInjectorInterceptor(
  getCredentials: () => (_login, _password),
));
```

**Retry automatique** (`_withRetry<T>`) :
- 3 tentatives au total (1 initiale + 2 retry) avec délai progressif (5s × attempt)
- Ne retente pas sur les erreurs métier (`ApiException`) ni `connectionTimeout`
- Retente sur : `sendTimeout`, `receiveTimeout`, `unknown` (erreur socket transitoire)

**Gestion du mojibake** (pour formulaires HTML) :
```dart
// getFormHtml() : réponse binaire → détection ISO-8859-1 → repair UTF-8
final bytes = response.data as List<int>;
String htmlStr = String.fromCharCodes(bytes);
if (_looksLikeMojibake(htmlStr)) {
  htmlStr = utf8.decode(latin1.encode(htmlStr), allowMalformed: true);
}
```

### 3.7 Formulaires dynamiques — `DynamicFormWidget`

Le widget `lib/widgets/dynamic_form/dynamic_form_widget.dart` charge les formulaires HTML dans un `WebView` et établit un pont JavaScript bidirectionnel :

**JS → Flutter** (capture des modifications de champs) :
```javascript
// Injecté dans le HTML par _preprocessHtml()
document.addEventListener('change', function(e) {
  if (window.FieldChanged) {
    window.FieldChanged.postMessage(e.target.name + '|' + e.target.value);
  }
});
```

```dart
// Dart : réception et transmission au provider
JavascriptChannel(
  name: 'FieldChanged',
  onMessageReceived: (msg) {
    final parts = msg.message.split('|');
    if (parts.length >= 2) {
      widget.onFieldChanged(parts[0], parts.sublist(1).join('|'));
    }
  },
)
```

**Flutter → JS** (pré-remplissage des champs) :
```dart
// Injection des données sauvegardées dans le formulaire
_controller.runJavaScript(
  "document.querySelector('[name=\"$fieldName\"]').value = '$escapedValue';"
);
```

---

## 4. Inventaire des dépendances Flutter

Toutes les dépendances sont déclarées dans `stateduc_flutter/pubspec.yaml`.

### 4.1 `dio: ^5.7.0`

**Rôle** : Client HTTP avancé avec intercepteurs, timeouts configurables et gestion des types de réponse.

**Implémentation** (`lib/services/api_service.dart`) :

| Aspect | Valeur configurée | Justification |
|---|---|---|
| `connectTimeout` | 60s | Délai raisonnable pour établir la connexion |
| `receiveTimeout` | 600s | `questionnaire_ws.php` peut mettre plusieurs minutes pour écrire en Oracle |
| `sendTimeout` | `null` | Désactivé (session 19) — évite les timeouts sur envois de gros formulaires |
| `ResponseType.bytes` | `getFormHtml()` | Récupération binaire pour réparer le mojibake Latin-1/UTF-8 |
| `ResponseType.plain` | `saveData()` | Réponse texte brut (OKSAVE/KOSAVE) |

```dart
// Intercepteur d'injection Basic Auth automatique
class _AuthInjectorInterceptor extends Interceptor {
  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    final (login, password) = _getCredentials();
    if (login != null && password != null) {
      final encoded = base64Encode(utf8.encode('$login:$password'));
      options.headers['Authorization'] = 'Basic $encoded';
    }
    handler.next(options);
  }
}
```

### 4.2 `provider: ^6.1.2`

**Rôle** : Gestion d'état réactive basée sur `ChangeNotifier` + `InheritedWidget`.

**Implémentation** :

- `MultiProvider` à la racine de l'arbre de widgets (`main.dart`)
- `ChangeNotifierProxyProvider2` pour injecter les services (`DatabaseService`, `ApiService`) dans les providers
- `Consumer<T>` et `Consumer2<T1,T2>` dans les écrans pour écouter l'état
- `context.read<T>()` pour les actions ponctuelles (sans écoute)

```dart
// Exemple d'utilisation dans un écran
Consumer2<AuthProvider, DataEntryProvider>(
  builder: (context, auth, entry, _) {
    // Rebuild automatique quand auth ou entry notifient
    return Scaffold(...);
  },
)
```

### 4.3 `sqflite: ^2.3.3+1`

**Rôle** : Accès SQLite sur mobile (Android/iOS), base de données locale pour le mode hors ligne.

**Implémentation** (`lib/services/database_service.dart`) :

- Chemin : `getApplicationDocumentsDirectory()/stateduc.db`
- `PRAGMA foreign_keys = ON` activé à l'ouverture
- 3 versions de schéma avec migrations `onUpgrade`
- `ConflictAlgorithm.replace` pour les upserts
- Transactions SQLite (`db.transaction()`) pour les suppressions en cascade

```dart
// Ouverture avec migrations
final db = await openDatabase(
  path,
  version: 3,
  onCreate: _onCreate,
  onUpgrade: (db, oldV, newV) async {
    if (oldV < 2) await db.execute('ALTER TABLE questions ADD COLUMN sort_order INTEGER NOT NULL DEFAULT 0');
    if (oldV < 3) await _createCoherenceRulesTable(db);
  },
  onOpen: (db) async => await db.execute('PRAGMA foreign_keys = ON'),
);
```

### 4.4 `path: ^1.9.0`

**Rôle** : Utilitaire de manipulation de chemins de fichiers (portable multi-plateforme).

**Implémentation** : Utilisé dans `DatabaseService` pour construire le chemin vers `stateduc.db` :
```dart
final dbPath = join(await getDatabasesPath(), 'stateduc.db');
```

### 4.5 `flutter_secure_storage: ^9.2.2`

**Rôle** : Stockage chiffré de données sensibles via Keystore Android / Keychain iOS.

**Implémentation** (`lib/services/auth_service.dart`) :

9 clés stockées :

| Clé | Contenu |
|---|---|
| `auth_pin` | Code PIN hashé (en clair dans le storage chiffré) |
| `auth_security_q` | Question de sécurité |
| `auth_security_a` | Réponse de sécurité |
| `auth_server_url` | URL du serveur StatEduc |
| `auth_login` | Login utilisateur |
| `auth_password` | Mot de passe utilisateur |
| `auth_user_id` | ID numérique de l'utilisateur |
| `auth_user_name` | Nom complet de l'utilisateur |
| `auth_codeyear` | Code de l'année scolaire courante |
| `auth_libyear` | Libellé de l'année scolaire courante |

```dart
final _storage = const FlutterSecureStorage(
  aOptions: AndroidOptions(encryptedSharedPreferences: true),
);
```

### 4.6 `shared_preferences: ^2.3.2`

**Rôle** : Préférences simples non sensibles (clés/valeurs primitives, non chiffrées).

**Implémentation** :
- `SplashScreen` : clé `onboarding_done` (bool) — détecte le premier lancement
- `OnboardingScreen` : écriture de `onboarding_done = true` après le tutoriel

```dart
final prefs = await SharedPreferences.getInstance();
final onboardingDone = prefs.getBool('onboarding_done') ?? false;
```

### 4.7 `connectivity_plus: ^6.1.0`

**Rôle** : Détection de la connectivité réseau (WiFi, mobile, hors ligne).

**Implémentation** :
- Utilisé dans `CampaignListScreen` via le widget `_ConnectivityIcon` (indicateur visuel dans l'AppBar)
- Importé mais le mécanisme de reconnexion automatique est prévu pour une version ultérieure

```dart
// Icône connectivité dans CampaignListScreen
class _ConnectivityIcon extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return const Icon(Icons.wifi, size: 20);
    // Future: ConnectivityPlus stream pour état dynamique
  }
}
```

### 4.8 `flutter_html: ^3.0.0-beta.2`

**Rôle** : Rendu de fragments HTML simples dans des widgets Flutter natifs.

**Implémentation** : Présent dans `pubspec.yaml` comme alternative légère pour afficher des libellés HTML courts (messages d'erreur formatés côté serveur). Le rendu principal des formulaires reste `WebView` (plus fidèle).

### 4.9 `webview_flutter: ^4.10.0`

**Rôle** : Composant WebView natif (Chrome WebView sur Android, WKWebView sur iOS) pour le rendu fidèle des formulaires HTML StatEduc.

**Implémentation** (`lib/widgets/dynamic_form/dynamic_form_widget.dart`) :

Le widget `DynamicFormWidget` :
1. Reçoit le HTML brut du formulaire (stocké dans SQLite `form_html`)
2. Prétraite le HTML (`_preprocessHtml()`) : injection du script JS de capture des événements `change`
3. Charge le HTML via `loadHtmlString()`
4. Établit le canal `FieldChanged` (JS → Flutter)
5. Injecte les valeurs sauvegardées via `runJavaScript()` (Flutter → JS)
6. Transmet les modifications à `DataEntryProvider.updateField()` via `onFieldChanged`

```dart
// Pont bidirectionnel complet
WebViewController()
  ..setJavaScriptMode(JavaScriptMode.unrestricted)
  ..addJavaScriptChannel('FieldChanged',
      onMessageReceived: (msg) {
        final parts = msg.message.split('|');
        widget.onFieldChanged(parts[0], parts.sublist(1).join('|'));
      })
  ..loadHtmlString(_preprocessHtml(html))
```

### 4.10 `cupertino_icons: ^1.0.8`

**Rôle** : Pack d'icônes style iOS. Présent par défaut dans les projets Flutter ; non utilisé directement (l'application utilise `Icons.` Material).

---

## 5. Application serveur PHP

### 5.1 Architecture et organisation

Le serveur **StatEduc MEN 2025** est une application PHP hébergée sur **IIS** (Windows Server) ou **Apache/XAMPP** selon l'environnement. Il combine :

- Une interface web classique (HTML/JS pour navigateur)
- Une **API REST** via **Slim v2** pour l'application mobile

### 5.2 Bootstrap — `common_ws.php`

Tous les fichiers de web services commencent par `require_once 'common_ws.php'` qui :

1. Charge les fichiers de configuration (`config_app.php`, `params.php`, `params_sys.php`, `params_ws.php`, `constants.php`)
2. Inclut le framework **Slim v2** (`codeguy-Slim`)
3. Inclut le middleware **HttpAuth** (`server-side/include/web_services/HttpAuth.php`)
4. Initialise **ADODB** et se connecte à la base `dico_DB.mdb` / `dico_DB.accdb` → `$conn_dico`
5. Établit la connexion `conn` (Oracle/SQL Server/MySQL) via `connexion.class.php`
6. Démarre la session PHP (`session_start()`)
7. Charge les paramètres globaux depuis `PARAM_DEFAUT` (langue, secteur, année, filtre, style)

```php
// common_ws.php — extrait des connexions ADODB
if (file_exists($GLOBALS['SISED_PATH'] . 'server-side/dico_DB.mdb')) {
    $conn_dico = ADONewConnection('access');
    $conn_dico->Connect('Driver={Microsoft Access Driver (*.mdb)};Dbq=...');
} elseif (file_exists($GLOBALS['SISED_PATH'] . 'server-side/dico_DB.accdb')) {
    $conn_dico = ADONewConnection('access');
    $conn_dico->Connect('Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=...');
}
```

### 5.3 Double base de données

| Variable globale | Type | Fichier physique | Contenu |
|---|---|---|---|
| `$GLOBALS['conn_dico']` | MS Access (ADODB) | `server-side/dico_DB.mdb` ou `.accdb` | Dictionnaire (règles, nomenclatures, paramètres, utilisateurs, campagnes) |
| `$GLOBALS['conn']` | Oracle / SQL Server / MySQL | Défini dans `connexion.php` | Données de collecte, établissements, résultats statistiques |

La chaîne de connexion `conn` est lue depuis `connexion.php` au format CSV commenté :
```
#96;Sql Serveur;mssql;DESKTOP-AA23SDR\SQLEXPRESS;test;1234;BD_NIGER_EDUC
```
Format : `#id;nom;type;serveur;utilisateur;mdp;base`

Types supportés : `mssql` (SQL Server), `access` (MS Access), `oci8` (Oracle), `mysql` (MySQL).

### 5.4 Authentification HTTP Basic — `HttpAuth.php`

```php
class HttpAuth extends \Slim\Middleware {
    public function authenticate($username, $password) {
        if (!ctype_alnum($username)) return false;
        $password_md5 = md5($password);
        return valide_user_ws($username, $password_md5);
    }
    
    public function call() {
        $authUser = $req->headers('PHP_AUTH_USER');
        $authPass = $req->headers('PHP_AUTH_PW');
        if ($this->authenticate($authUser, $authPass)) {
            $this->next->call();  // continuer
        } else {
            $this->deny_access();  // HTTP 401
        }
    }
}
```

Le middleware est ajouté aux routes qui requièrent authentification :
```php
$app->add(new \HttpAuth());  // dans user_ident.php, data_save.php, etc.
```

> **Note** : `user_camp.php` a le middleware commenté (`//$app->add(new \HttpAuth());`), indiquant une route accessible sans ré-authentification si la session PHP est active.

### 5.5 Paramètres de réponse — `params_ws.php`

Toutes les réponses JSON suivent l'enveloppe :

```json
{
  "se_status": 200,
  "se_message": "log_ok",
  "se_data": { ... }
}
```

| Constante PHP | Valeur | Usage |
|---|---|---|
| `PARAM_WS['LIB_STATUS']` | `se_status` | Clé du code statut |
| `PARAM_WS['LIB_MESSAGE']` | `se_message` | Clé du message |
| `PARAM_WS['LIB_DATA']` | `se_data` | Clé des données |
| `PARAM_WS['STATUS_OK']` | `200` | Succès |
| `PARAM_WS['STATUS_KO']` | `400` | Erreur |
| `PARAM_WS['LOGIN_OK']` | `log_ok` | Login réussi |
| `PARAM_WS['LOGIN_KO']` | `log_ko` | Login échoué |

### 5.6 Moteur de cohérence — `controle_theme_batch.class.php`

La classe `controle_theme` implémente le contrôle de cohérence des données collectées.

**Principe** : Deux règles SQL (R1, R2) sont comparées via un opérateur (critère) :
- `R1 OP R2` est **VRAIE** → données cohérentes (OK)
- `R1 OP R2` est **FAUSSE** → violation détectée (KO)

**Tables de la base `dico_DB`** :

| Table | Contenu |
|---|---|
| `DICO_REGLE_THEME` | Définit les règles SQL (R1, R2) par thème — SQL interpolé via `eval()` |
| `DICO_REGLE_THEME_ASSOC` | Associe R1 et R2 avec l'opérateur (`CRITERE`) et flag `ACTIVER_CTRL=1` |
| `DICO_MESSAGE` | Messages d'erreur traduits (IDs 101–107) |
| `DICO_TRADUCTION` | Libellés traduits de toutes les nomenclatures |

**Interpolation SQL** (via `eval()`) :
```php
// Les variables PHP sont interpolées dans le SQL stocké :
// ex: "SELECT SUM(NB_FILLES) FROM COLLECTE WHERE CODE_ETAB=$code_etablissement"
// devient :
eval('$sql = "' . $sql_regle_theme . '";');
// → "SELECT SUM(NB_FILLES) FROM COLLECTE WHERE CODE_ETAB=12345"
```

**Deux modes** :
- **Mode HTML** (`alert=true`) : usage navigateur web → alertes JavaScript
- **Mode Batch/API** (`alert=false`) : usage mobile → JSON via `data_controle.php`

### 5.7 Flux de sauvegarde — `data_save.php`

```
Mobile POST → data_save.php/theme_save/... 
    │
    ├─ session_write_close()  ← anti-deadlock (libère le verrou de session)
    │
    └─ cURL interne → questionnaire_ws.php (CURLOPT_TIMEOUT=120s)
                          │
                          ├─ theme_manager_ws.class.php (logique métier)
                          │
                          └─ Oracle/MySQL DB
                                   │
                          OKSAVE / KOSAVE (réponse texte)
```

**Logging** : Chaque sauvegarde est loggée dans :
- Fichier texte : `moblogs/{user}.log`
- Table base de données : `DATA_SAVING_LOGS`

**Réponses** :
- `OKSAVE` : données écrites en base
- `KOSAVE` : données reçues mais écriture base échouée (fichier thème introuvable, erreur SQL, etc.)

### 5.8 Structure de la base de données — côté `dico_DB` (MS Access)

Tables principales utilisées par les web services :

| Table | Rôle |
|---|---|
| `PARAM_DEFAUT` | Paramètres globaux (langue, année, secteur, style) |
| `ADMIN_USERS` | Comptes utilisateurs (login, password MD5, groupe) |
| `DICO_FIXE_REGROUPEMENT` | Affectations utilisateur→campagne→regroupements |
| `DICO_REGLE_THEME` | Règles SQL de cohérence |
| `DICO_REGLE_THEME_ASSOC` | Associations R1↔R2 avec critère |
| `DICO_MESSAGE` | Messages traduits |
| `DICO_TRADUCTION` | Traductions nomenclatures |
| `DICO_LIBELLE_PAGE` | Libellés pages traduits |
| `DICO_THEME_SYSTEME` | Liens thème↔système éducatif |
| `DICO_ZONE` | Zones/filtres (has_filter flag) |

### 5.9 Structure de la base de données — côté `conn` (Oracle/SQL Server)

Tables principales (noms réels dépendent du déploiement) :

| Table | Rôle |
|---|---|
| `TYPE_RECENSEMENT` | Campagnes de collecte |
| `TYPE_SYSTEME_ENSEIGNEMENT` | Systèmes éducatifs |
| `TYPE_REGROUPEMENT` | Types de regroupements administratifs |
| `REGROUPEMENT` | Regroupements géographiques/administratifs |
| `TYPE_STATUT_ETABLISSEMENT` | Statuts établissements |
| `ETABLISSEMENT` | Établissements scolaires |
| `ETABLISSEMENT_REGROUPEMENT` | Liens étab↔regroupement |
| `TYPE_ANNEE` | Années scolaires |
| `DATA_SAVING_LOGS` | Journal des sauvegardes mobiles |
| Tables collecte (thème) | Données statistiques collectées par formulaire |

---

## 6. Communication mobile ↔ serveur

### 6.1 Protocole et format

| Aspect | Valeur |
|---|---|
| Protocole | HTTP / HTTPS |
| Format de réponse | JSON (application/json) |
| Encodage | UTF-8 (avec repair mojibake pour HTML) |
| Authentification | HTTP Basic Auth (`Authorization: Basic base64(login:pwd)`) |
| Framework routage serveur | Slim v2 REST |

### 6.2 Catalogue complet des endpoints

#### 6.2.1 Authentification — `user_ident.php`

**`GET /user_ident.php/user/:login/:password`**

```
Paramètres URL :
  :login     → identifiant utilisateur (alphanumérique)
  :password  → mot de passe en clair (hashé en MD5 côté serveur)

Réponse succès (se_message = "log_ok") :
{
  "se_status": 200,
  "se_message": "log_ok",
  "se_data": {
    "id":        "42",
    "group":     "1",
    "login":     "jdupont",
    "firstname": "Jean Dupont",
    "lastname":  "jdupont@men.gov.bi",
    "codeyear":  "2024",
    "libyear":   "2024-2025",
    "filter":    "TYPE_PERIODE",   // présent si PARAM['FILTRE'] == true
    "filters":   [                 // présent si PARAM['FILTRE'] == true
      {"CODE_TYPE_PERIOD": "1", "NAME_TYPE_PERIOD": "T1", "ORDER_TYPE_PERIOD": 1},
      ...
    ]
  }
}

Réponse échec (se_message = "log_ko") :
{ "se_status": 200, "se_message": "log_ko", "se_data": { "id": null, ... } }
```

> **Distinction critique** : `se_data.id` est l'`idUser` (ID numérique interne), `se_data.login` est le login textuel.  
> → `user.login` est utilisé dans les URLs de sauvegarde/rechargement.  
> → `user.idUser` est utilisé dans les endpoints de campagne (`new_camp`, `sys_camp`, `etabs_camp`).

**`GET /user_ident.php/leave/:login/:password`** — déconnexion (réponse vide, statut 200).

---

#### 6.2.2 Campagnes — `user_camp.php`

**`GET /user_camp.php/new_camp/:user_id/:id_period`** — liste des campagnes disponibles

```
Paramètres :
  :user_id   → user.idUser
  :id_period → "1" (valeur fixe)

Réponse se_data : [
  {
    "id": "5",
    "nom": "Statistiques Education 2024-2025",
    "debut": "",
    "fin": "",
    "statut": 2,
    "typeregroups": "1,2,3"
  },
  ...
]
```

**`GET /user_camp.php/sys_camp/:user_id/:camp_id`** — systèmes éducatifs d'une campagne

```
Réponse se_data : [
  { "id": "1", "nom": "Education de Base" },
  { "id": "2", "nom": "Enseignement Secondaire" },
  ...
]
```

**`GET /user_camp.php/typ_reg_camp/:user_id/:camp_id/:type_regroups_csv`** — types de regroupements

```
Paramètres :
  :type_regroups_csv → "1,2,3" (CSV des types de regroupement de la campagne)

Réponse se_data : [
  { "id": "1", "nom": "Province" },
  { "id": "2", "nom": "District" },
  ...
]
```

**`GET /user_camp.php/status_camp/:user_id/:camp_id`** — statuts des établissements

```
Réponse se_data : [
  { "id": "1", "name": "Public" },
  { "id": "2", "name": "Privé" },
  { "id": "3", "name": "Communautaire" }
]
```

**`GET /user_camp.php/reg_camp/:login/:camp_id/1`** — regroupements de la campagne

```
Paramètres :
  :login → user.login (différent de user.idUser !)

Réponse se_data : [
  { "id": "100", "nom": "BUJUMBURA MAIRIE", "type": "1", "parentid": "-1" },
  { "id": "101", "nom": "NGAGARA",          "type": "2", "parentid": "100" },
  ...
]
Note : parentid = "-1" signifie regroupement racine (pas de parent)
```

**`GET /user_camp.php/etabs_camp/:user_id/:camp_id/1`** — établissements de la campagne

```
Réponse se_data : [
  {
    "id": "12345",
    "code": "101012071",
    "nom": "École Primaire de Ngagara",
    "status": "1",
    "idregroup": "101"    ← LOWERCASE "idregroup" (différent du modèle Dart "idRegroup")
  },
  ...
]
```

**`GET /user_camp.php/locs_camp/:user_id/:camp_id/1`** — localisations (liens étab↔système↔regroups)

```
Réponse se_data : [
  {
    "idsys": "1",
    "etabs": "12345,12346",   ← CSV d'IDs d'établissements
    "regroups": "101,100"     ← CSV d'IDs de regroupements (du plus proche au plus loin)
  },
  ...
]
```

---

#### 6.2.3 Questions/Thèmes — `data_camp.php`

**`GET /data_camp.php/theme_camp/:id_camp/:id_sys/:code_lang`**

```
Paramètres :
  :id_camp   → ID de la campagne
  :id_sys    → ID du système éducatif
  :code_lang → code langue (ex. "FR")

Réponse se_data : [
  {
    "id_qst":    "10",
    "lib_qst":   "Effectifs des élèves",
    "id_system": "1",
    "has_filter": 0,
    "sort_order": 1
  },
  ...
]
```

Source : `DICO_THEME_SYSTEME` + `DICO_TRADUCTION` + `DICO_ZONE` (pour `has_filter`).

---

#### 6.2.4 Formulaires HTML — `data_reload.php`

**`GET /data_reload.php/theme_data/:login/:id_sys/:id_qst/:id_camp/:id_etab/:id_filter`**

```
Réponse : HTML brut du formulaire pré-rempli avec les données existantes
  → Encodage : ISO-8859-1 ou UTF-8 (peut nécessiter repair mojibake côté Flutter)
  → ContentType : text/html
```

Cette route sert à la fois :
1. **Téléchargement initial** : récupère le HTML "template" (formulaire vide ou pré-rempli serveur)
2. **Rechargement** : récupère le HTML pré-rempli avec les données de la dernière sauvegarde

---

#### 6.2.5 Règles de cohérence offline — `data_rules.php`

**`GET /data_rules.php/theme_rules/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:id_annee`**

```
Paramètres :
  :user       → login utilisateur
  :id_camp    → ID campagne
  :id_sector  → ID système éducatif
  :id_theme   → ID question/thème
  :id_etab    → ID établissement
  :id_filter  → ID filtre (ou "0")
  :id_annee   → code année scolaire (yearCode — contournement session PHP manquante)

Réponse se_data :
{
  "nb_regles": 3,
  "regles": [
    {
      "id_regle":     12,
      "lib_regle":    "Nb élèves filles",
      "sql_regle":    "SELECT SUM(NB_FILLES) FROM COLLECTE WHERE CODE_ETAB=$code_etablissement AND CODE_ANNEE=$code_annee",
      "associations": [
        {
          "id_assoc":      45,
          "id_regle_assoc": 13,
          "lib_regle_assoc": "Nb élèves total",
          "sql_assoc":     "SELECT SUM(NB_TOTAL) FROM COLLECTE WHERE CODE_ETAB=$code_etablissement AND CODE_ANNEE=$code_annee",
          "critere":       "<=",
          "message":       "Le nombre de filles ne peut pas dépasser le total"
        }
      ]
    }
  ]
}
```

> Le `yearCode` (`:id_annee`) est passé pour contourner l'absence de session PHP côté mobile (correction session 14). Sans ce paramètre, `$_SESSION['annee']` est vide côté serveur, rendant les règles SQL inopérantes.

---

#### 6.2.6 Contrôle de cohérence serveur — `data_controle.php`

**`GET /data_controle.php/theme_controle/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:id_annee`**

```
Réponse se_data :
{
  "nb_erreurs": 1,
  "erreurs": [
    {
      "id_regle":      12,
      "id_regle_assoc": 13,
      "message":       "Le nombre de filles ne peut pas dépasser le total",
      "regle_1":       "Nb élèves filles",
      "regle_2":       "Nb élèves total",
      "critere":       "<="
    }
  ]
}
```

Délègue à `controle_theme_batch.class.php` (mode batch, `alert=false`).

---

#### 6.2.7 Sauvegarde des données — `data_save.php`

**`POST /data_save.php/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start`**  
**`POST /data_save.php/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start/:id_annee`**

```
Paramètres URL :
  :user       → user.login (NOT user.idUser)
  :id_camp    → ID campagne
  :id_sector  → ID système éducatif
  :id_theme   → ID question/thème
  :id_etab    → ID établissement
  :id_filter  → ID filtre (ou "0")
  :start      → "0" (valeur fixe)
  :id_annee   → code année (optional, mobile uniquement)

Corps POST (Content-Type: application/x-www-form-urlencoded) :
  field1=valeur1&field2=valeur2&...&LOC_REG_0={idRegroup}&switch_theme_id=&save_and_prev=&save_and_next=

Encodage champs :
  - Champs radio  : "fieldName#optionId=1" → transformé en "fieldName=optionId"
  - Autres champs : remplacement "/" → "_slh_" (uniquement)
  - NB : PAS d'encodeURIComponent (comme JS original)

Réponse :
  "OKSAVE"  → données écrites en Oracle/MySQL
  "KOSAVE"  → données reçues mais écriture base échouée (traité comme succès côté mobile)
  { "se_status": 400, "se_data": "message d'erreur" }  → erreur métier
```

---

### 6.3 Workflow de chargement de campagne (9 étapes séquentielles)

```dart
// lib/providers/campaign_provider.dart — loadCampaignFromServer()
// Étape 1 : Liste des campagnes
final campaigns = await _api.getAvailableCampaigns(userId: user.idUser);

// Étape 2 : Systèmes éducatifs
final systems = await _api.getEducationSystems(userId: user.idUser, campId: camp.idCamp);

// Étape 3 : Types de regroupements
final regroupTypes = await _api.getRegroupTypes(userId: user.idUser, campId: camp.idCamp, typeRegroupsCsv: camp.typeRegroups);

// Étape 4 : Statuts établissements
final statuses = await _api.getSchoolStatuses(userId: user.idUser, campId: camp.idCamp);

// Étape 5 : Établissements
final schools = await _api.getSchools(userId: user.idUser, campId: camp.idCamp);

// Étape 6 : Localisations
final locs = await _api.getLocalisations(userId: user.idUser, campId: camp.idCamp);

// Étape 7 : Regroupements
final regroups = await _api.getRegroups(login: user.login, campId: camp.idCamp);

// Étape 8 : Questions (par système éducatif)
for (final system in systems) {
  final questions = await _api.getQuestions(campId: camp.idCamp, sysId: system.idSystem);
  // + formulaires HTML pour chaque question
  for (final q in questions) {
    final html = await _api.getFormHtml(login, campId, sysId, qstId, etabId);
    await _db.insertFormHtml(campId, q.idQst, html);
  }
}

// Étape 9 : Règles de validation
final rules = await _api.getValidationRules(campId, sysId, qstId);
```

### 6.4 Workflow de saisie et envoi

```
SchoolDataScreen.initState()
    │
    ▼
DataEntryProvider.initForSchool(idCamp, idEtab, idSystem)
    │
    ├─ DatabaseService.getQuestions()       → questions depuis SQLite
    ├─ DatabaseService.getFilterPeriods()   → périodes filtre depuis SQLite
    └─ [auto] selectQuestion(questions.first)
                │
                ├─ DatabaseService.getFormHtml()        → HTML depuis SQLite
                ├─ DatabaseService.getValidationRules()  → règles validation
                ├─ DatabaseService.getCollectedData()    → données sauvegardées
                ├─ [si données vides] _autoReloadFromServer() → GET /theme_data/
                └─ _fetchAndStoreCoherenceRulesBackground() → GET /theme_rules/ [async]
                        │
                        └─ DatabaseService.insertCoherenceRules()
                               └─ [re-déclenchement] checkCoherenceOffline()

[Utilisateur modifie un champ]
    │
    ▼
DynamicFormWidget → FieldChanged.postMessage(name|value)
    │
    ▼
DataEntryProvider.updateField(fieldName, value)
    │
    ├─ _formData[fieldName] = value
    ├─ notifyListeners()
    └─ debounce 800ms → checkCoherenceOffline()
                │
                └─ CoherenceEvaluator.evaluate(rules, formData, ...)
                        │
                        └─ Affichage _OfflineCoherenceBanner

[Sauvegarde locale]
    │
    ▼
DataEntryProvider.saveLocally()
    │
    └─ DatabaseService.upsertCollectedData(is_sent=0)
          └─ checkCoherenceOffline()

[Envoi serveur]
    │
    ▼
DataEntryProvider.sendToServer()
    │
    ├─ [1] saveLocally() (snapshot local avant envoi)
    ├─ [2] ApiService.saveData() → POST /theme_save/... (avec retry)
    └─ [3] ApiService.checkCoherence() → GET /theme_controle/...
                └─ Affichage AlertDialog violations si nb_erreurs > 0
```

### 6.5 Authentification et sécurité

| Mécanisme | Côté mobile | Côté serveur |
|---|---|---|
| Transport | HTTP Basic Auth sur chaque requête | HttpAuth Slim Middleware |
| Credentials | `flutter_secure_storage` (Keystore/Keychain) | `md5(password)` comparé en base `conn_dico` |
| Session | Pas de session HTTP (REST stateless) | `yearCode` URL param contourne `$_SESSION['annee']` manquante |
| PIN | Code local 4–8 chiffres, jamais transmis | N/A |
| SSL auto-signé | `_TrustAllCertificates` (dev/test uniquement) | N/A |

> **Avertissement** : `_TrustAllCertificates` dans `main.dart` désactive la vérification des certificats SSL. À remplacer par un certificat valide en production.

### 6.6 Gestion des erreurs

| Type d'erreur | Traitement mobile | Traitement serveur |
|---|---|---|
| HTTP 401 | `ApiException('Accès refusé')` | HttpAuth renvoie 401 + header WWW-Authenticate |
| HTTP 404 | `ApiException('Endpoint introuvable')` | Route Slim non trouvée |
| `connectionTimeout` | Exception sans retry | N/A |
| `receiveTimeout` / `sendTimeout` | Retry automatique (2 fois, délai progressif 5s/10s) | N/A |
| `DioExceptionType.unknown` | Retry + message réseau | N/A |
| `KOSAVE` | Traité comme succès (warning dans logs) | Log dans `DATA_SAVING_LOGS` |
| `se_status=400` | `ApiException(se_data)` | Retourné en JSON |
| Règles manquantes offline | Ignoré silencieusement (conservatisme) | N/A |

### 6.7 Stratégie de synchronisation

| Scénario | Comportement |
|---|---|
| **Hors ligne — saisie** | Sauvegarde SQLite avec `is_sent=0` |
| **Retour en ligne — envoi** | Bouton "Envoyer au serveur" ou "Envoyer tous les formulaires" |
| **Envoi multi-établissements** | `DataEntryProvider.sendAllFormsForCampaign()` avec progress bar |
| **Rechargement données** | Option menu "Recharger depuis serveur" → GET `/theme_data/` → remplace SQLite |
| **Conflit** | Les données serveur écrasent les données locales en cas de rechargement |
| **Retry** | 3 tentatives automatiques sur erreurs transitoires |

---

## 7. Configuration et déploiement

### 7.1 Prérequis

#### Côté serveur PHP

| Prérequis | Version requise |
|---|---|
| PHP | 5.6+ (recommandé 7.x) |
| IIS ou Apache/XAMPP | XAMPP recommandé pour développement local |
| Extension PHP ODBC | Requise pour connexion MS Access (`php_com_dotnet`, `php_odbc`) |
| MS Access Database Engine | Requis sur le serveur Windows pour driver ODBC Access |
| PHP extension mssql ou sqlsrv | Requise si base `conn` est SQL Server |
| PHP extension oci8 | Requise si base `conn` est Oracle |
| PHP `curl` extension | Requise pour le cURL interne `data_save.php` → `questionnaire_ws.php` |

#### Côté Flutter

| Prérequis | Version |
|---|---|
| Flutter SDK | 3.35.4+ (Dart 3.9.2+) |
| Android SDK | API 21+ (Android 5.0 Lollipop) |
| iOS | iOS 12+ |
| Xcode | 14+ (pour build iOS) |

### 7.2 Variables de configuration PHP

#### `config_app.php` (et `config.php`) — chemins

```php
$GLOBALS['SISED_PATH']       = '/chemin/absolu/vers/StatEduc_MEN_2025/';
$GLOBALS['SISED_URL']        = 'http://serveur:port/StatEduc_MEN_2025/';
$GLOBALS['SISED_AURL']       = 'http://serveur:port/StatEduc_MEN_2025/';
$GLOBALS['SISED_PATH_INC']   = $GLOBALS['SISED_PATH'] . 'server-side/include/';
$GLOBALS['SISED_PATH_CLS']   = $GLOBALS['SISED_PATH'] . 'server-side/classes/';
$GLOBALS['SISED_PATH_LIB']   = $GLOBALS['SISED_PATH'] . 'server-side/lib/';
$GLOBALS['SISED_PATH_DB']    = $GLOBALS['SISED_PATH'] . 'server-side/db/';
$GLOBALS['SISED_PATH_ADC']   = $GLOBALS['SISED_PATH'] . 'server-side/adodbcache/';
```

#### `connexion.php` — chaîne de connexion base de données

```
#id;nom;type;serveur;utilisateur;mdp;base

Exemple SQL Server :
#96;Sql Serveur;mssql;DESKTOP-AA23SDR\SQLEXPRESS;test;1234;BD_NIGER_EDUC

Exemple Oracle :
#1;Oracle;oci8;192.168.1.10/ORCL;scott;tiger;STATSEDUC

Exemple MySQL :
#1;MySQL;mysql;localhost;root;password;stateduc_db
```

Types supportés : `mssql`, `access`, `oci8`, `mysql`

#### `dico_DB.mdb` / `dico_DB.accdb`

La base MS Access de dictionnaire est un fichier à déposer dans `server-side/`. Elle contient :
- La table `PARAM_DEFAUT` avec les paramètres globaux de l'instance (langue par défaut, année courante, secteur par défaut)
- Les nomenclatures (`DICO_TRADUCTION`, `DICO_LIBELLE_PAGE`)
- Les règles de cohérence (`DICO_REGLE_THEME`, `DICO_REGLE_THEME_ASSOC`)
- Les comptes utilisateurs (`ADMIN_USERS`)
- Les affectations campagnes-utilisateurs (`DICO_FIXE_REGROUPEMENT`)

### 7.3 Configuration côté Flutter

#### URL du serveur

L'URL serveur est saisie par l'utilisateur dans l'écran de connexion (`PinScreen`) ou modifiable dans `SettingsScreen`. Elle est stockée dans `flutter_secure_storage` (clé `auth_server_url`) et transmise à `ApiService.configure()`.

Format attendu : `http://192.168.1.100:8083/StatEduc_MEN_2025`

#### Assets

```yaml
# pubspec.yaml
flutter:
  assets:
    - assets/
    - assets/icon/
```

Fichiers requis dans `assets/icon/` :
- `icon.png` — icône de l'application (logo StatEduc sur fond blanc)
- `Flag_of_country.png` — drapeau du Burundi (affiché sur l'écran de connexion)

### 7.4 Build et déploiement Flutter

#### Build Android (APK debug)
```bash
cd stateduc_flutter
flutter pub get
flutter build apk --debug
# APK généré : build/app/outputs/flutter-apk/app-debug.apk
```

#### Build Android (APK release)
```bash
flutter build apk --release
# Nécessite keystore.jks configuré dans android/app/build.gradle
```

#### Build iOS
```bash
flutter build ios --release
# Nécessite Xcode, Apple Developer account, provisioning profile
```

#### Permissions Android requises (`android/app/src/main/AndroidManifest.xml`)

```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
```

### 7.5 Déploiement serveur PHP

#### XAMPP (développement local)

1. Copier `StatEduc_MEN_2025/` dans `htdocs/`
2. Configurer `connexion.php` avec les paramètres de la base de données cible
3. Déposer `dico_DB.mdb` ou `dico_DB.accdb` dans `server-side/`
4. Activer les extensions PHP : `php_com_dotnet`, `php_odbc`, `php_curl`, et le driver DB correspondant
5. Démarrer Apache via XAMPP Control Panel
6. Accéder à : `http://localhost/StatEduc_MEN_2025/`

#### IIS (production Windows Server)

1. Créer une application IIS pointant vers `StatEduc_MEN_2025/`
2. Configurer le pool d'applications avec l'identité ayant accès aux fichiers `.mdb`
3. Activer l'extension PHP via PHP Manager for IIS
4. S'assurer que le driver ODBC Microsoft Access est installé (32 ou 64 bits selon PHP)
5. Configurer les droits d'écriture sur `moblogs/`, `server-side/adodbcache/`

#### Configuration réseau mobile ↔ serveur

- L'appareil mobile doit être sur le même réseau local que le serveur (ou le serveur doit être accessible via internet)
- Ouvrir le port du serveur (ex. 8083 pour XAMPP) dans le pare-feu Windows
- URL exemple pour XAMPP local : `http://192.168.1.100:80/StatEduc_MEN_2025`
- URL exemple pour IIS production : `https://stats.ministere-education.bi/StatEduc_MEN_2025`

---

## 8. Historique des sessions et correctifs

Cette section résume les corrections et améliorations architecturales introduites au fil des sessions de développement.

### Session 14 — Correction yearCode (contournement session PHP)

**Problème** : L'application mobile ne maintient pas de session PHP. Les endpoints qui lisaient `$_SESSION['annee']` recevaient une valeur vide, rendant les règles SQL de cohérence inopérantes.

**Correction** : Ajout du paramètre `:id_annee` comme dernier segment d'URL dans `data_rules.php`, `data_controle.php` et `data_save.php`. Le mobile passe `user.codeyear` dans ce segment.

### Session 19 — Désactivation sendTimeout

**Problème** : Le timeout d'envoi (`sendTimeout`) déclenchait des erreurs prématurées sur de gros formulaires avec connexion lente.

**Correction** : `sendTimeout: null` dans `BaseOptions` de `ApiService` — Dio n'impose plus de limite de temps sur l'envoi.

### Session 21 — Correction cohérence offline

**Trois bugs corrigés dans le pipeline de cohérence offline** :

| # | Fichier | Bug | Correction |
|---|---|---|---|
| 1 | `coherence_evaluator.dart` | `_sumFieldAcrossAllFilters()` retournait `0.0` quand le champ était absent → évaluait `0 OP 0` → faux négatifs | Retourne `null` (type `double?`) → règle ignorée silencieusement |
| 2 | `data_entry_provider.dart` | Condition `_formData.isNotEmpty` bloquait le re-déclenchement quand les règles arrivaient avant que l'utilisateur ait saisi | Suppression de la condition → re-déclenchement systématique |
| 3 | `data_entry_provider.dart` | Garde `_formData.isNotEmpty` redondante dans le debounce de `updateField()` | Suppression |

**Améliorations UX** :
- Bouton "Vérifier la cohérence" ajouté dans le menu popup de `school_data_screen.dart`
- `debugPrint` diagnostics ajoutés dans tout le pipeline de cohérence

---

*Document généré à partir du code source réel du dépôt `stateduc_mobile` — branche `ak_main`.*  
*Toutes les références de fichiers sont vérifiées et correspondent aux chemins effectifs dans le dépôt.*
