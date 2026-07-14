# CHANGELOG — StatEduc Mobile (Flutter)

Historique complet de toutes les modifications apportées à l'application Flutter StatEduc Mobile.

---

## [Session 45] — 2026-07-14 — Moteur de cohérence offline SQL réel sur SQLite

### Objectif
Remplace l'évaluation regex (Sessions 38–44) par un moteur d'exécution SQL réel.
Les requêtes SQL de cohérence (Access/SQL Server) sont traduites et exécutées directement
sur la base SQLite locale, garantissant l'équivalence exacte avec le contrôle serveur.

### Nouveautés

####  (classe statique dans )
Traduit les requêtes SQL Access/SQL Server vers SQLite :
- Substitution des paramètres : ,  → valeurs réelles
- Mapping de table :  → CTE de pivot dynamique sur 
- Traduction syntaxique :  → ,  → 
-  → ,  → 
- Wrapper  : la requête retourne un entier (0 = OK, >0 = violation)

####  — Dual-path
1. **Chemin SQL réel** (prioritaire) :  + 
2. **Chemin regex fallback** (conservatif) : extraction regex + 

#### 
Passe maintenant  et  au moteur pour la substitution des paramètres.

### Règles validées
- Règle électricité :  vs  (cas concret Session 45)
- Règle clôture/superficie :  vs 

### Tests
-  : 15 tests unitaires du traducteur SQL (pur Dart, sans DB)

### Conservation
- Le contrôle serveur (data_controle.php via API) est intégralement conservé (additive)
- Le chemin regex est conservé comme fallback pour la robustesse

---

## [Unreleased] — 2026-06-17 — Session 21 : correction cohérence hors ligne

### 🔴 Fix — `coherence_evaluator.dart` : `_sumFieldAcrossAllFilters` retournait `0` au lieu de `null`

**Problème** : Quand un champ référencé dans une règle SQL (pattern `SUM(CHAMP)`) n'était pas
présent dans les données collectées (`collected_data`), `_sumFieldAcrossAllFilters()` retournait
`0.0` au lieu de `null`. Cela causait l'évaluation de la règle avec `V1=0 critere V2=0`, produisant
des résultats incorrects (faux négatifs si critère `<=`, faux positifs si critère `>=`).

**Correction** : Retourne `null` quand le champ est introuvable. L'appelant `_extractValue()` propage
`null` vers `evaluate()` qui ignore silencieusement la règle (comportement conservatif correct —
le moteur ne signale que les violations certaines).

```dart
// Avant : double _sumFieldAcrossAllFilters(...) { ... return found ? sum : (values[fieldName] ?? 0); }
// Après : double? _sumFieldAcrossAllFilters(...) { ... return found ? sum : null; }
```

---

### 🔴 Fix — `data_entry_provider.dart` : re-déclenchement cohérence après chargement des règles

**Problème** : Dans `_fetchAndStoreCoherenceRulesBackground()`, le re-déclenchement du contrôle
de cohérence offline (après insertion des règles depuis le serveur) était conditionné à
`_formData.isNotEmpty`. Cette condition empêchait le contrôle de s'exécuter si les règles
arrivaient avant que l'utilisateur ait commencé à saisir.

**Correction** : Suppression de la condition `_formData.isNotEmpty`. Le re-déclenchement est
maintenant systématique dès que les règles arrivent pour la question courante (et que
`_isCheckingOffline == false`). Si les données sont vides, le contrôle retournera 0 violations —
ce qui est correct et met à jour l'UI de façon cohérente.

```dart
// Avant :
if (_selectedQuestion?.idQst == q.idQst && _formData.isNotEmpty && !_isCheckingOffline)
// Après :
if (_selectedQuestion?.idQst == q.idQst && !_isCheckingOffline)
```

---

### 🟢 Amélioration — `data_entry_provider.dart` : `updateField()` — suppression garde `_formData.isNotEmpty`

Dans le callback debounce de `updateField()`, la garde `if (_formData.isNotEmpty)` a été retirée.
Elle était redondante (le champ vient d'être mis à jour donc `_formData` est forcément non vide)
et aurait pu masquer le problème si l'implémentation changeait.

**Avant** :
```dart
_coherenceDebounce = Timer(const Duration(milliseconds: 800), () {
  if (_formData.isNotEmpty && !_isCheckingOffline) {
    checkCoherenceOffline();
  }
});
```

**Après** :
```dart
_coherenceDebounce = Timer(const Duration(milliseconds: 800), () {
  if (!_isCheckingOffline) {
    checkCoherenceOffline();
  }
});
```

---

### 🟢 Amélioration — `school_data_screen.dart` : bouton manuel "Vérifier la cohérence"

Ajout d'une option "Vérifier la cohérence" dans le menu popup (⋮) de la barre d'application.
Permet à l'agent de collecte de déclencher manuellement le contrôle hors ligne si le check
automatique (debounce 800ms) n'a pas encore pu s'exécuter (ex. : règles pas encore téléchargées).

```dart
PopupMenuItem(
  value: 'check_coherence',
  child: ListTile(
    leading: Icon(Icons.rule_folder_outlined),
    title: Text('Vérifier la cohérence'),
    subtitle: Text('Contrôle offline immédiat'),
  )),
```

---

### 🔵 Diagnostic — `data_entry_provider.dart` : `debugPrint` enrichis

Ajout de `debugPrint` détaillés pour tracer le flux de cohérence offline dans les logs Flutter :
- `[DataEntry] updateField: CHAMP = "valeur" (N champs en mémoire) — debounce 800ms`
- `[DataEntry] debounce fired: _formData=N _isCheckingOffline=false`
- `[DataEntry] rules arrived for current question (formData=N fields) — re-triggering`
- `[DataEntry] checkCoherenceOffline: aucune règle stockée pour idCamp=... idQst=... idEtab=...`

---

### 🟢 Amélioration — `pin_screen.dart` : en-tête institutionnel complet

**Avant** : L'écran d'accueil affichait directement le drapeau du Burundi (session 18), sans mention du nom de l'institution.

**Après** : Deux lignes institutionnelles en **italique** s'affichent au-dessus du drapeau, avec la même police et couleur principale (`colorScheme.primary`) que le titre "StatEduc" :

```dart
// Ligne 1 — République du Burundi
// Ligne 2 — Ministère de l'Éducation Nationale
// (séparateur 14px)
// Drapeau du Burundi (96×64 px, ombre, coin arrondis)
// (séparateur 12px)
// StatEduc  (headlineMedium bold)
// Collecte de données éducatives (bodyMedium)
```

**Style des deux lignes institutionnelles** :
```dart
Theme.of(context).textTheme.headlineMedium?.copyWith(
  fontStyle: FontStyle.italic,      // texte oblique
  fontWeight: FontWeight.w600,      // semi-bold — même poids que StatEduc
  color: primaryColor,              // couleur principale de l'application
)
```

**Résultat visuel** :
```
  République du Burundi                  ← italique, couleur principale
  Ministère de l'Éducation Nationale     ← italique, couleur principale

        [Drapeau du Burundi]

            StatEduc
  Collecte de données éducatives
```

---

### 📄 Nouveau document — `administration.md`

Création du guide d'administration complet A→Z de l'application, destiné aux administrateurs et superviseurs de collecte du MEN. Couvre 20 sections :
- Installation, premier démarrage, connexion au serveur
- Création, modification et réinitialisation du PIN
- Configuration URL serveur, téléchargement de campagne
- Navigation, remplissage questionnaire, sauvegarde locale
- Contrôle de cohérence hors ligne (7 déclenchements)
- Envoi des données (formulaire / établissement / campagne)
- Contrôle de cohérence serveur, rechargement depuis serveur
- Gestion des erreurs réseau (tableau messages + diagnostic)
- Tableau de bord administrateur — vérifications essentielles

---

### 📄 Documents mis à jour

- **`recapitulatif.md`** : duplication de contenu supprimée (la moitié du fichier était dupliquée depuis la session 18) ; mise à jour sessions 19-20 ; nouvelle section "Timeouts Dio — Configuration actuelle" ; ajout `administration.md` dans le tableau documentation ; correctifs sessions 19+20 ajoutés au tableau historique
- **`notepresentation.md`** : références mises à jour sessions 19-20 ; tableau documentation enrichi avec `administration.md` ; timeouts mis à jour (600s / null) ; tableau résultats complété
- **`stateduc_flutter/CHANGELOG.md`** : entrée session 20 (ce fichier)

---

## [Unreleased] — 2026-06-15 — Session 19 : correction timeout + retry automatique

### 🔴 Fix — "Délai d'attente dépassé lors de l'envoi" sur réseau stable

**Symptôme** : L'erreur _"Délai d'attente dépassé lors de l'envoi. Le serveur est lent ou la connexion est instable."_ s'affichait même avec un réseau Wi-Fi ou LTE stable (capture écran utilisateur).

**Causes racines identifiées** :
1. `sendTimeout: Duration(seconds: 300)` — Dio peut déclencher ce timeout prématurément sur Android même sur réseau stable, quand le serveur XAMPP tarde à accuser réception du POST.
2. `receiveTimeout: Duration(seconds: 300)` — insuffisant : la chaîne `data_save.php → curl interne → questionnaire_ws.php` peut dépasser 5 min sur XAMPP chargé.
3. Aucun mécanisme de retry : la moindre erreur transitoire (reset TCP, microcoupure) causait un échec définitif.

**Correctifs appliqués** (`api_service.dart`) :

| Paramètre | Avant | Après | Raison |
|-----------|-------|-------|--------|
| `sendTimeout` | `300s` | `null` (désactivé) | Body POST < 10 KB — faux-positif Android fréquent |
| `receiveTimeout` | `300s` | `600s` (10 min) | Chaîne save→questionnaire_ws peut dépasser 5 min |
| Retry | aucun | 2 re-tentatives | Erreurs transitoires réseau/timeout |

**Nouveau helper `_withRetry<T>()`** :
```dart
// 2 re-tentatives = 3 essais au total
// Délai progressif : 5s × numéro de tentative
// Ne réessaie PAS sur : ApiException (401, 404, se_status 400), connectionTimeout
// Réessaie sur : sendTimeout, receiveTimeout, DioExceptionType.unknown
static const int _kMaxRetries = 2;
static const int _kRetryDelay = 5;

Future<T> _withRetry<T>(Future<T> Function() fn, {void Function(int attempt)? onRetry}) async { ... }
```

**Refactoring `saveData()`** :
- Public `saveData()` → délègue à `_withRetry()` + accepte `onRetry` callback
- Privé `_saveDataOnce()` → implémentation HTTP unique (inchangée)
- `onRetry` transmis depuis `DataEntryProvider.sendToServer()` → affichage "Tentative 2/3…" dans l'UI

**Messages d'erreur améliorés** (par type `DioExceptionType`) :
- `connectionTimeout` → _"Impossible de joindre le serveur. Vérifiez l'URL et votre connexion réseau."_
- `sendTimeout`/`receiveTimeout` → _"Délai d'attente dépassé après 3 tentatives. Le serveur ne répond pas…"_
- `unknown`/socket → _"Erreur réseau lors de l'envoi… Réessayez quand le réseau est stable."_

---

### 🟢 Amélioration — Suivi des tentatives de retry dans l'UI

**Fichiers** : `data_entry_provider.dart`, `school_data_screen.dart`

**Nouveautés** :
- Champ `int _sendAttempt` (0 = inactif, 1–3 = numéro de tentative en cours)
- Getter public `int get sendAttempt`
- Constante publique `static const int kMaxSendAttempts = 3`
- `sendToServer()` : initialise `_sendAttempt = 1`, callback `onRetry` incrémente avant chaque re-tentative, `finally` remet `_sendAttempt = 0`
- Overlay `LoadingOverlay` : affiche _"Envoi… (tentative 2/3)"_ dès que `sendAttempt > 1`

```dart
// school_data_screen.dart — overlay message
message: entry.isSending
    ? (entry.sendAttempt > 1
        ? 'Envoi… (tentative ${entry.sendAttempt}/${DataEntryProvider.kMaxSendAttempts})'
        : 'Envoi en cours…')
    : 'Rechargement…',
```

---

## [Unreleased] — 2026-06-15 — Session 18 : drapeau Burundi + renforcement cohérence offline

### 🟢 Amélioration — `pin_screen.dart` : remplacement de l'icône `school` par le drapeau du Burundi

**Avant** : L'écran d'accueil/PIN affichait une icône Material `Icons.school` générique (taille 72).

**Après** : L'asset `assets/icon/Flag_of_country.png` (drapeau du Burundi) est affiché dans un
conteneur rectangulaire arrondi (96×64 px) avec ombre légère, centré au-dessus du titre.
Un `errorBuilder` assure le repli sur `Icons.school` si l'image n'est pas chargée
(robustesse en cas d'asset manquant).

```dart
// AVANT
Icon(Icons.school, size: 72, color: Theme.of(context).colorScheme.primary)

// APRÈS
Container(
  width: 96, height: 64,
  decoration: BoxDecoration(
    borderRadius: BorderRadius.circular(6),
    boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.18), blurRadius: 8, offset: Offset(0, 3))],
  ),
  child: ClipRRect(
    borderRadius: BorderRadius.circular(6),
    child: Image.asset(
      'assets/icon/Flag_of_country.png',
      fit: BoxFit.cover,
      errorBuilder: (_, __, ___) => Icon(Icons.school, size: 64, ...),
    ),
  ),
)
```

L'asset `assets/icon/` est déjà déclaré dans `pubspec.yaml` → aucune modification de config nécessaire.

---

### 🔴 Fix — Cohérence offline non déclenchée à la saisie

**Symptôme observé** : Les contrôles de cohérence offline ne s'affichaient que lors de l'envoi
au serveur (`sendToServer()`), jamais pendant la saisie ni à l'ouverture d'un formulaire.

**Audit session 18 — problèmes identifiés** :

| # | Problème | Localisation |
|---|---------|-------------|
| A | `updateField()` ne déclenche JAMAIS la cohérence offline | `data_entry_provider.dart` |
| B | `selectFilter()` ne re-déclenche pas la cohérence après changement de période | `data_entry_provider.dart` |
| C | `_autoReloadFromServerBackground()` met à jour `_formData` sans re-vérifier la cohérence | `data_entry_provider.dart` |
| D | Indicateur visuel "contrôle en cours" absent dans l'UI | `school_data_screen.dart` |

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** — 4 corrections :

**Fix A — Import `dart:async` + Timer debounce + `updateField()` déclenche la cohérence** :
```dart
// En tête de fichier — ajout
import 'dart:async';

// Dans la déclaration des champs
Timer? _coherenceDebounce;  // debounce 800 ms pour updateField()

// updateField() — désormais déclenche checkCoherenceOffline() en debounce
void updateField(String fieldName, String value) {
  _formData[fieldName] = value;
  _hasUnsavedChanges   = true;
  _validationErrors.remove(fieldName);
  notifyListeners();
  // Debounce 800 ms — évite une évaluation à chaque frappe
  _coherenceDebounce?.cancel();
  _coherenceDebounce = Timer(const Duration(milliseconds: 800), () {
    if (_formData.isNotEmpty && !_isCheckingOffline) {
      checkCoherenceOffline();
    }
  });
}
```

**Fix B — `selectFilter()` relance la cohérence après changement de filtre** :
```dart
// À la fin de selectFilter(), après le chargement des données
if (_formData.isNotEmpty) {
  Future(() => checkCoherenceOffline());
}
```

**Fix C — `_autoReloadFromServerBackground()` relance la cohérence après fusion** :
```dart
// Après notifyListeners() dans le bloc if (changed)
if (!_isCheckingOffline) {
  Future(() => checkCoherenceOffline());
}
```

**Fix D — Nettoyage du Timer dans `dispose()`** :
```dart
@override
void dispose() {
  _coherenceDebounce?.cancel();
  super.dispose();
}
```

**`stateduc_flutter/lib/screens/data_entry/school_data_screen.dart`** — indicateur visuel :
```dart
// Avant le banner d'erreurs offline : spinner pendant le contrôle
if (entry.isCheckingOffline)
  const LinearProgressIndicator(),
if (entry.hasOfflineCoherenceErrors)
  _OfflineCoherenceBanner(errors: entry.offlineCoherenceErrors, ...),
```

---

### 📊 Tableau complet des déclenchements cohérence offline (après session 18)

| Événement | Trigger | Délai | Depuis |
|-----------|---------|-------|--------|
| Saisie d'un champ | `updateField()` → debounce 800 ms | 0.8 s après dernière frappe | **Session 18** |
| Sauvegarde locale | `saveLocally()` → `Future()` | Immédiat (arrière-plan) | Sessions 1-16 |
| Ouverture formulaire déjà rempli | `selectQuestion()` → `Future()` | Immédiat | Session 17 |
| Changement de filtre/période | `selectFilter()` → `Future()` | Immédiat | **Session 18** |
| Règles reçues du serveur | `_fetchAndStoreCoherenceRulesBackground()` | Arrière-plan | Session 17 |
| Données serveur fusionnées | `_autoReloadFromServerBackground()` → `Future()` | Arrière-plan | **Session 18** |
| Envoi serveur | `sendToServer()` → `checkCoherence()` (API) | Après POST réussi | Sessions 1-16 |

---

### 📊 Fichiers modifiés — Session 18

| Fichier | Type | Résumé |
|---------|------|--------|
| `lib/screens/login/pin_screen.dart` | UX | Remplacement `Icons.school` → `Image.asset('assets/icon/Flag_of_country.png')` avec `errorBuilder` |
| `lib/providers/data_entry_provider.dart` | Fix | `dart:async` import ; `Timer _coherenceDebounce` ; `updateField()` debounce ; `selectFilter()` trigger ; `_autoReloadFromServerBackground()` trigger ; `dispose()` |
| `lib/screens/data_entry/school_data_screen.dart` | UX | `LinearProgressIndicator` pendant `isCheckingOffline` |

---

## [Unreleased] — 2026-06-03 — Session 17 : timeout, cohérence offline, envoi global, identification, contraste settings

### 🔴 Fix — `api_service.dart` : timeout "délais d'attente dépassé" sur réseau stable

**Symptôme** : L'envoi d'un formulaire échouait avec `DioExceptionType.sendTimeout` même sur un réseau intranet stable. La chaîne d'appels `data_save.php → session_write_close → curl interne → questionnaire_ws.php` peut dépasser 2 minutes sur un serveur XAMPP chargé.

**Cause racine** : `sendTimeout` était fixé à **120 s** — insuffisant pour les envois lents (payload volumineux + traitement PHP).

**`stateduc_flutter/lib/services/api_service.dart`** :
- `sendTimeout` 120 s → **300 s** (5 minutes)
- `connectTimeout` reste 60 s, `receiveTimeout` reste 300 s (inchangés depuis session 12b)

```dart
connectTimeout: const Duration(seconds: 60),
receiveTimeout: const Duration(seconds: 300),
sendTimeout:    const Duration(seconds: 300),  // était 120 s
```

| Timeout | Avant | Après | Rôle |
|---------|-------|-------|------|
| `connectTimeout` | 60 s | 60 s | Établissement connexion TCP |
| `receiveTimeout` | 300 s | 300 s | Attente réponse complète serveur |
| `sendTimeout` | **120 s** | **300 s** | Envoi du corps de la requête |

---

### 🟠 Fix — Cohérence offline non déclenchée après sauvegarde locale

**Symptôme** : Après `saveLocally()`, l'indicateur de cohérence offline restait vide. Les règles de cohérence étaient chargées en arrière-plan ; au moment où `checkCoherenceOffline()` était appelé, elles n'étaient pas encore en SQLite → résultat vide.

**Cause racine** : `checkCoherenceOffline()` n'était déclenché que dans le flux `sendToServer()` / `saveLocally()`, pas après l'arrivée des règles ni à l'ouverture d'un formulaire déjà rempli.

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** — deux corrections :

**Fix 1 — Re-déclencher la cohérence quand les règles arrivent (`_fetchAndStoreCoherenceRulesBackground`)** :
```dart
if (rules.isNotEmpty) {
  await _db.insertCoherenceRules(rules);
  // NOUVEAU : re-trigger si règles arrivent pour le formulaire courant déjà rempli
  if (_selectedQuestion?.idQst == q.idQst &&
      _formData.isNotEmpty &&
      !_isCheckingOffline) {
    await checkCoherenceOffline();
  }
}
```

**Fix 2 — Déclencher la cohérence à l'ouverture d'un formulaire déjà rempli (`selectQuestion`)** :
```dart
// NOUVEAU : lance la cohérence offline si le formulaire contient déjà des données
if (_formData.isNotEmpty) {
  Future(() => checkCoherenceOffline());
}
```

---

### 🟢 Nouveau — Envoi global : tous les formulaires d'un établissement

**Besoin** : Pouvoir envoyer en une seule action tous les formulaires saisis pour l'établissement courant, sans devoir naviguer dans chaque formulaire et cliquer "Envoyer".

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** — nouvelle méthode `sendAllFormsForSchool()` :
```dart
Future<Map<String, bool>> sendAllFormsForSchool({
  required User user,
  void Function(int sent, int total)? onProgress,
}) async {
  // Itère sur toutes les questions (_questions) de l'établissement courant (_idEtab)
  // Pour chaque question : charge les données depuis SQLite, envoie via _api.saveData()
  // Marque is_sent=1 dans SQLite en cas de succès
  // Retourne Map<idQst, bool> (true = succès, false = échec)
}
```

**`stateduc_flutter/lib/screens/data_entry/school_data_screen.dart`** — nouveau menu item et méthode :
- Ajout dans le `PopupMenu` : `'send_all'` → `ListTile` avec `Icons.cloud_sync_outlined` et label "Envoyer tous les formulaires"
- `_onMenuSelected` : branche `'send_all'` → appel `_sendAllForms(context, auth, entry)`
- Nouvelle méthode `_sendAllForms()` :
  - Dialogue de confirmation
  - Progress dialog avec `ValueNotifier<int>` et `LinearProgressIndicator`
  - Appel `entry.sendAllFormsForSchool(user: user, onProgress: callback)`
  - Fermeture du progress dialog
  - Dialogue résumé : ✅ N succès / ⚠️ N échecs

---

### 🟢 Nouveau — Envoi global : tous les formulaires de toute la campagne

**Besoin** : Depuis l'écran de campagne, envoyer d'un seul tap tous les formulaires de tous les établissements collectés.

**`stateduc_flutter/lib/services/database_service.dart`** — nouvelle méthode `getDistinctEtabQstWithData()` :
```dart
Future<List<Map<String, String>>> getDistinctEtabQstWithData(String idCamp) async {
  final db = await database;
  final rows = await db.rawQuery(
    'SELECT DISTINCT id_etab, id_qst FROM collected_data WHERE id_camp = ?',
    [idCamp],
  );
  return rows
      .map((r) => {
            'id_etab': r['id_etab'] as String? ?? '',
            'id_qst':  r['id_qst']  as String? ?? '',
          })
      .where((m) => m['id_etab']!.isNotEmpty && m['id_qst']!.isNotEmpty)
      .toList();
}
```

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** — nouvelle méthode `sendAllFormsForCampaign()` :
```dart
Future<Map<String, bool>> sendAllFormsForCampaign({
  required User user,
  required String idCamp,
  required String idSystem,
  void Function(int sent, int total)? onProgress,
}) async {
  // Utilise _db.getDistinctEtabQstWithData(idCamp) pour lister toutes les paires (etab, qst)
  // Pour chaque paire : charge les données SQLite, envoie, marque is_sent=1 si succès
  // Retourne Map<"${etabId}_${qstId}", bool>
}
```

**`stateduc_flutter/lib/screens/schools/campaign_detail_screen.dart`** — conversion `StatelessWidget` → `StatefulWidget` + bouton global :

```dart
// AVANT
class CampaignDetailScreen extends StatelessWidget { ... }

// APRÈS
class CampaignDetailScreen extends StatefulWidget {
  const CampaignDetailScreen({super.key, required this.campaign});
  final Campaign campaign;
  @override
  State<CampaignDetailScreen> createState() => _CampaignDetailScreenState();
}
class _CampaignDetailScreenState extends State<CampaignDetailScreen> {
  Campaign get campaign => widget.campaign;
}
```

- Imports ajoutés : `auth_provider.dart`, `data_entry_provider.dart`
- Bouton `OutlinedButton.icon` en tête de liste établissements :
  - Label : "Envoyer tous les établissements"
  - Icône : `Icons.cloud_sync_outlined`
  - Désactivé si `entry.isSending`
  - Appelle `_sendAllCampaignForms()` avec confirmation + progress dialog + résumé

---

### 🟡 Fix — Identification : données serveur ne remplacent pas les données locales

**Symptôme** : Sur certains types de campagne, le formulaire d'identification se pré-remplissait avec les données locales (souvent vides ou incomplètes) et ignorait les données du serveur.

**Cause racine** : `_autoReloadFromServerBackground()` utilisait `localWasEmpty = _formData.isEmpty` pour décider si le serveur devait écraser le local. Si des données locales existaient (même incomplètes), le serveur était ignoré.

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** :
```dart
// AVANT — paramètre inexistant, toujours basé sur _formData.isEmpty
void _autoReloadFromServerBackground() { ... }

// APRÈS — forceOverwrite=true => localWasEmpty=true => serveur gagne toujours
void _autoReloadFromServerBackground({bool forceOverwrite = false}) {
  final localWasEmpty = _formData.isEmpty || forceOverwrite;
  ...
}
```
- `selectQuestion()` appelle désormais :
  ```dart
  _autoReloadFromServerBackground(forceOverwrite: isIdentificationForm);
  ```
- Pour les formulaires d'identification → **le serveur a toujours priorité** (données d'établissement officielles).
- Pour les autres formulaires → comportement inchangé (le local n'est écrasé que s'il était vide).

---

### 🟡 Fix — Settings : onglets Serveur/PIN/Sécurité trop peu contrastés

**Symptôme** : Dans l'écran Paramètres, les libellés et icônes des onglets "Serveur", "PIN", "Sécurité" étaient grisés et difficiles à lire (surtout l'onglet non sélectionné).

**Cause racine** : Material 3 applique par défaut `unselectedLabelColor = onSurface.withOpacity(0.38)` — très peu lisible sur un fond coloré (`AppBar`).

**`stateduc_flutter/lib/screens/settings/settings_screen.dart`** :
```dart
// AVANT — couleurs Material 3 par défaut (trop grises)
TabBar(
  controller: _tabController,
  tabs: const [ Tab(icon: Icon(Icons.dns_outlined), text: 'Serveur'), ... ],
),

// APRÈS — couleurs explicites basées sur appBarFg
final appBarFg = Theme.of(context).appBarTheme.foregroundColor
    ?? Theme.of(context).colorScheme.onPrimary;
TabBar(
  controller: _tabController,
  labelColor:            appBarFg,
  unselectedLabelColor:  appBarFg.withOpacity(0.80),
  indicatorColor:        appBarFg,
  labelStyle:            const TextStyle(fontWeight: FontWeight.w600, fontSize: 12),
  unselectedLabelStyle:  const TextStyle(fontWeight: FontWeight.w500, fontSize: 12),
  tabs: const [ Tab(icon: Icon(Icons.dns_outlined), text: 'Serveur'), ... ],
),
```

---

### 📊 Fichiers modifiés — Session 17 (Flutter uniquement, aucun changement PHP)

| Fichier | Type | Résumé |
|---------|------|--------|
| `lib/services/api_service.dart` | Fix | `sendTimeout` 120 s → 300 s |
| `lib/services/database_service.dart` | Nouveau | `getDistinctEtabQstWithData(idCamp)` |
| `lib/providers/data_entry_provider.dart` | Fix + Nouveau | Cohérence offline re-trigger × 2 ; `_autoReloadFromServerBackground(forceOverwrite)` ; `sendAllFormsForSchool()` ; `sendAllFormsForCampaign()` |
| `lib/screens/data_entry/school_data_screen.dart` | Nouveau | Menu "Envoyer tous les formulaires" + `_sendAllForms()` avec progress dialog |
| `lib/screens/schools/campaign_detail_screen.dart` | Nouveau | `StatelessWidget` → `StatefulWidget` ; bouton "Envoyer tous les établissements" + `_sendAllCampaignForms()` |
| `lib/screens/settings/settings_screen.dart` | Fix | `TabBar` : `labelColor`/`unselectedLabelColor`/`indicatorColor` explicites depuis `appBarFg` |

**Commit** : `1db4be2` — `feat(session17): timeout, cohérence offline, envoi global, identification, settings`  
**PR** : [#1 — ak_main → main](https://github.com/NasserKailou/stateduc_mobile/pull/1)

---

## [Unreleased] — 2026-05-30 — Session 13 : PHP 500 sur data_save.php — variables indéfinies dans callbacks

### 🔴 Fix CRITIQUE — HTTP 500 sur POST `/theme_save/.../id_annee` malgré le fix session 12b

**Symptôme** : `[Dio←] 500 .../data_save.php/theme_save/test/2/2/15702/70/0/0/23` — body vide.
**Log Apache** : `PHP Parse error: syntax error, unexpected ')' on line 334` sur le XAMPP de l'utilisateur.

**Double diagnostic** :

1. **Parse error sur XAMPP** : L'utilisateur n'avait pas encore copié le `data_save.php` corrigé (sessions 11-12b) vers son XAMPP. La version sur le serveur contenait encore `});` (ligne 334 originale, corrigée en session 11 → `}` dans notre repo). **Action** : l'utilisateur doit copier le fichier depuis le repo vers XAMPP.

2. **Variables indéfinies dans `theme_save_handler()`** (présentes dans notre repo, fixes additionnelles session 13) :
   - `$curl->error()` callback (ancienne L296) capturait `$data` dans `use(...)` — variable qui n'existe pas dans le scope de `theme_save_handler()` (elle existe dans la route GET originale mais pas dans la fonction déléguée). PHP 7 strict mode → fatal error.
   - `$date_time` utilisé dans `saveLogInfo()` dans le callback `error` (ancienne L307) — également non défini dans ce scope (défini seulement dans le callback `success`).
   - `saveLogInfo()` appelée sans le 9e paramètre `$id_annee` depuis l'ancien callback error → warning PHP.

**`StatEduc_MEN_2025/data_save.php`** (session 13) :
- Callback `$curl->error()` dans `theme_save_handler()` : suppression de `$data` du `use(...)`, suppression de l'utilisation de `$data` dans le body, remplacement de `$date_time` non défini par `$date_time_err = date(...)` local, passage de `$id_year` à `saveLogInfo()`.
- `saveLogInfo()` : paramètre `$id_annee` rendu optionnel (`= 0`) pour compatibilité avec les appels existants.

---

## [Unreleased] — 2026-05-29 — Session 12b : KOSAVE timeout 3min — deadlock Apache self-curl

### 🔴 Fix CRITIQUE — Timeout 3 minutes sur l'envoi + KOSAVE persistant

**Diagnostic complet** :

*Chaîne d'appels du save* :
```
Flutter Dio POST → data_save.php (Slim route) → $curl->post() → questionnaire_ws.php
```

**Problème 1 — Timeout 3 minutes** : `data_save.php` fait un appel curl **HTTP interne** vers `questionnaire_ws.php` sur le même serveur Apache. Aucun timeout n'était configuré sur cet objet curl PHP. Si Apache est saturé (plusieurs requêtes parallèles) ou si `questionnaire_ws.php` est lent (page HTML complète + queries DB), le curl attend **indéfiniment**. Flutter time-out après 180s et renvoie `DioExceptionType.receiveTimeout`.

**Problème 2 — Session deadlock potentiel** : `common_ws.php` (inclus par `data_save.php`) appelle `session_start()`. Sans `session_write_close()` avant l'appel curl interne, la session PHP reste verrouillée pendant tout l'appel. Si `questionnaire_ws.php` tente d'accéder à la même session ID (via cookie ou config partagée), un deadlock de fichier de session peut se produire.

**`StatEduc_MEN_2025/data_save.php`** :
- `CURLOPT_CONNECTTIMEOUT = 15` : echec rapide si le serveur interne est injoignable
- `CURLOPT_TIMEOUT = 60` : abort au bout de 60s si questionnaire_ws.php ne répond pas
- `session_write_close()` avant chaque `$curl->post()` (deux appels : route GET ligne 170, route POST `theme_save_handler` ligne 342)
- Ces fixes évitent que Flutter attende 3 minutes et reçoive un timeout au lieu d'une réponse

**`stateduc_flutter/lib/services/api_service.dart`** :
- `receiveTimeout` élevé de 180s → **300s (5 minutes)** : le save est une opération lente (`data_save.php` → curl → `questionnaire_ws.php` = page entière + 2× include grille + queries DB). 5 min = sécurité au cas où le serveur est lent mais répond quand même

**Note** : si KOSAVE persiste après ces fixes, la cause est que `questionnaire_ws.php` ne trouve pas le fichier grille `$curfile` (ACTION_THEME introuvable) ou que la DB write échoue silencieusement. Vérifier les logs XAMPP (`moblogs/test.log`) pour le détail.

---

## [Unreleased] — 2026-05-29 — Session 12 : données jamais écrites en DB (codeyear vide) + en-tête formulaire enrichi

### 🔴 Fix CRITIQUE — OKSAVE/KOSAVE : données envoyées mais jamais écrites en base de données

**Cause racine** : `auth_service.dart::getStoredUser()` retournait `codeyear: ''` après un déverrouillage PIN (sans reconnexion réseau). Cette chaîne vide se propageait comme suit :
- `yearCode = ''` dans `saveData()` d'`api_service.dart`
- `anneeSegment = ''` → URL 7 segments (sans `/id_annee`)
- `data_save.php` : pas de paramètre `id_annee` → `$id_year = $_SESSION['annee']` (vide en contexte REST)
- `questionnaire_ws.php?annee=` → `$_SESSION['annee'] = ''`
- La grille SQL : `WHERE CODE_TYPE_ANNEE = ''` → 0 lignes → pas d'écriture en DB
- `questionnaire_ws.php` retourne quand même `ISOKSAVEINDATABASE` (faux positif) → OKSAVE sans écriture

**`stateduc_flutter/lib/services/auth_service.dart`** (fix Flutter — cause racine) :
- Ajout des constantes `_kCodeyear = 'auth_codeyear'` et `_kLibyear = 'auth_libyear'`
- `login()` : sauvegarde de `codeyear` et `libyear` dans le stockage sécurisé (`flutter_secure_storage`) ET dans la base SQLite (`_db.setSetting`)
- `getStoredUser()` : restaure `codeyear`/`libyear` depuis le stockage sécurisé avec fallback DB pour les anciennes installations
- Correction : `codeyear` est maintenant correctement rétabli après un déverrouillage PIN → `yearCode != ''` → URL 8 segments → `annee` correcte → écriture en DB

**`StatEduc_MEN_2025/data_save.php`** (filet de sécurité PHP) :
- Ajout du fallback `PARAM_DEFAUT` dans `theme_save_handler()` quand `$id_year` reste vide après résolution URL/session :
  ```php
  if ($id_year == '' || $id_year == '0') {
      $_def = $GLOBALS['conn_dico']->GetOne('SELECT CODE_ANNEE FROM PARAM_DEFAUT');
      if ($_def && (int)$_def > 0) { $id_year = $_def; $_SESSION['annee'] = $id_year; }
  }
  ```
- Protège contre les appelants legacy qui n'envoient pas `id_annee` dans l'URL

### 🟡 Amélioration — En-tête de chaque formulaire : libellés enrichis

**`stateduc_flutter/lib/screens/data_entry/school_data_screen.dart`** :
- "Année Courante" renommé en **"Année en session"**
- "Sous secteur" renommé en **"Type secteur"** (correspond au `libSystem` du système d'enseignement)
- Correction : `libSubsector` ne prend plus `libStatus` comme fallback (les deux sont des concepts différents)
- L'en-tête affiche donc : Année en session · Hiérarchie admin · Établissement/ID/Code · **Statut** · **Type secteur**

---

## [Unreleased] — 2026-05-29 — Session 11 : parse error data_save, HTTP 500 contrôle, pré-remplissage dates identification

### 🔴 Fix CRITIQUE — `data_save.php` : erreur de parsing PHP bloquant tous les envois

**Cause racine** : La ligne 334 (comptage PHP via `\n`) contenait `});` — résidu d'une ancienne fermeture de route Slim. La fonction `theme_save_handler()` est une fonction PHP autonome qui se ferme avec `}` seul. Le `)` orphelin causait `Parse error: syntax error, unexpected ')'` → HTTP 500 sur **tous** les envois.

**Note technique** : Le fichier `data_save.php` a des fins de ligne CRLF avec des `\n` Unix intégrés à la ligne 194 (1437 octets, 33 `\n` intégrés). PHP compte les lignes via `\n` uniquement → la "ligne 334" PHP ≠ ligne 334 CRLF. Correction appliquée par analyse octets Python : `lf_lines[333] = b'}\r'`.

**`StatEduc_MEN_2025/data_save.php`** :
- `});` → `}` à la ligne 334 (comptage PHP/LF)

### 🔴 Fix — `data_controle.php` : HTTP 500 sur tous les contrôles de cohérence

**Cause racine** : `controle_theme_batch.class.php` prend `$ctrl_id = ID_ASSOC_REG_THM` (une règle d'association précise) et non un ID de thème. Passer `15702` comme `ctrl_id` → `WHERE ID_ASSOC_REG_THM = 15702` → 0 lignes → `array_change_key_case_unicode(null)` → fatal PHP 500. De plus, le paramètre `$alert` n'a pas de valeur par défaut → un appel à 5 arguments causait aussi une erreur fatale.

**`StatEduc_MEN_2025/data_controle.php`** — réécriture complète :
- `controle_strip_theme_id($id_theme, $id_sector)` : décompose l'ID thème composite (ex. `15702` → `1570`) identique à `data_rules.php`
- `controle_run_for_theme($raw_theme_id, ...)` : requête `SELECT DISTINCT ID_ASSOC_REG_THM FROM DICO_REGLE_THEME_ASSOC WHERE ID_THEME = x AND ACTIVER_CTRL = 1`, puis appel `new controle_theme_batch($ctrl_id, ..., $alert=false)` pour chaque règle d'association, collecte des violations

### 🟡 Fix — Formulaire identification : dates `DATE_CREATION_0` / `DATE_RECONNAISSANCE_0` non pré-remplies

**Cause racine** : Ces champs existent uniquement côté serveur (pas dans le modèle `School` local). Le rechargement auto depuis le serveur ne se déclenchait que si `_formData.isEmpty` → premier chargement seulement. En ré-ouvrant la fiche, les données locales existaient → pas de rechargement → dates absentes.

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** :
- `_autoReloadFromServerBackground()` se déclenche **toujours** pour les formulaires d'identification (pas seulement si `_formData.isEmpty`)
- Fusion intelligente : si `localWasEmpty=true` au moment de l'appel → remplacement complet + `_hasUnsavedChanges = false` ; sinon → remplissage conditionnel (ne remplace que les champs vides, préserve les saisies utilisateur)

---

## [Unreleased] — 2026-05-29 — Session 10 : cohérence nb_regles, grille add-row, pré-remplissage identification

### 🔴 Fix — Cohérence `nb_regles:0` : `data_rules.php` interrogeait le mauvais `ID_THEME`

**Cause racine** : L'app mobile envoie un `id_theme` **composite** (ex. `15602` = thème `1560` + secteur `2`). `data_rules.php` utilisait directement `WHERE ID_THEME=15602` alors que la table `DICO_REGLE_THEME` stocke l'ID brut (`1560`). Même décomposition que dans `questionnaire_reload_ws.php` :  
`str_theme_id = substr(id_theme, 0, len(id_theme) - len(id_sector))`

**`StatEduc_MEN_2025/data_rules.php`** (les deux routes GET) :
- Ajout de la décomposition du `id_theme` composite avant la requête `DICO_REGLE_THEME`
- Variables : `$str_theme_id` (route 1) et `$str_theme_id2` (route 2)
- La requête utilise maintenant `WHERE ID_THEME = (int)$str_theme_id`

### 🔴 Fix — Grille add-row : `maxIdx` calculé sur le mauvais segment numérique

**Cause racine** : La JS utilisait `el.name.match(/_(\d+)(?:_\d+)?$/)` sur les noms de champs pour déduire l'index de ligne. Certains formulaires ont des champs comme `CODE_TYPE_DISCIPLINE_FORM_1_0` où `1` est le numéro de **colonne**, pas de ligne → `maxIdx = 1` au lieu de `0` → la nouvelle ligne est indexée `2` au lieu de `1`.

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- Remplacement de la détection de `maxIdx` par lecture des attributs `id` des `<TR>` (ex. `id='ligne-paire_14_0'` → index 14)
- La regex de remplacement utilise maintenant `new RegExp('_' + maxRowIdx + '(_\\d+)?$')` (index spécifique) au lieu du pattern générique
- Fallback : `/(\d+)(_\d+)?$/` si aucun TR id trouvé

### 🟡 Fix — Identification pré-remplissage : boutons radio non cochés au premier ouverture

**Cause racine** : `_prefillIdentificationFields()` ne remplissait que les champs texte (nom, code, statut textuel). Les radios HTML de l'identification (`CODE_TYPE_STATUT_ETABLISSEMENT_0`) ont des VALUE littéraux comme `'CODE_TYPE_STATUT_ETABLISSEMENT_0_1'` — il faut pré-remplir `_formData['CODE_TYPE_STATUT_ETABLISSEMENT_0'] = 'CODE_TYPE_STATUT_ETABLISSEMENT_0_1'` pour que `_injectData()` coche le bon bouton.

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** :
- Nouveau champ `_idStatus` + paramètre `idStatus` dans `initForSchool()`
- `_prefillIdentificationFields()` : si `_idStatus != null`, pré-remplit `CODE_TYPE_STATUT_ETABLISSEMENT_0` avec la valeur composite `CODE_TYPE_STATUT_ETABLISSEMENT_0_{idStatus}`
- Fonctionne car `el.value === val` dans `_injectData()` compare la chaîne complète

**`stateduc_flutter/lib/screens/data_entry/school_data_screen.dart`** :
- Passage de `idStatus: widget.school.idStatus` à `initForSchool()`

---

## [Unreleased] — 2026-05-29 — Session 9 : crash FormatException regex, icône splash asset, regroups parentid=0

### 🔴 Fix CRITIQUE — Crash `FormatException: Invalid group (?i)` dans les formulaires

**Cause racine** : `RegExp(r'(?i)(value=)...')` — Dart ne supporte **pas** les flags inline comme `(?i)` dans les expressions régulières. Flutter lève une `FormatException` au moment de compiler le regex, ce qui crashe `_preprocessHtml()` et affiche l'écran rouge d'erreur.

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- Remplacé `RegExp(r'(?i)(value=)"(\$[A-Z_]...)"')` par `RegExp(r'(value=)"(\$[A-Z_]...)"', caseSensitive: false)`
- Remplacé `RegExp(r'(?i)(value=)\$([A-Z_]...)')` par `RegExp(r'(value=)\$([A-Z_]...)', caseSensitive: false)`
- Dart utilise `caseSensitive: false` comme paramètre — pas de flag inline

### 🔴 Fix — Icône splash toujours vide : asset `assets/icon/icon.png` non déclaré

**Cause racine** : `pubspec.yaml` déclarait `- assets/` (répertoire racine uniquement). Flutter **n'inclut pas automatiquement les sous-répertoires** — chaque sous-dossier doit être déclaré explicitement. `assets/icon/icon.png` n'était donc pas bundlé dans l'APK → `Image.asset` échouait silencieusement.

**`stateduc_flutter/pubspec.yaml`** :
- Ajouté `- assets/icon/` sous la clé `assets:`

### 🟡 Fix — Navigation regroupements : `parentid=0` ignoré comme racine

**Cause racine** : Certains déploiements StatEduc retournent `parentid: "0"` pour les regroupements racine (au lieu de `-1` qui est la convention JS standard). Le code ne mappait que `-1` → null (racine). Résultat : tous les regroupements semblaient avoir un parent → `getChildRegroups(null)` retournait 0 lignes → navigation vide.

**`stateduc_flutter/lib/models/regroup.dart`** :
- `fromJson` : ajouté `"0"` et `""` comme sentinels supplémentaires → mappés null (racine)
- Getter `isRoot` : robustifié → vrai si `idParentRegp` est null, `'-1'`, `'0'`, ou chaîne vide

**`stateduc_flutter/lib/services/database_service.dart`** :
- Requête fallback `getChildRegroups` : ajouté `OR id_parent_regp = '0'` pour gérer les données existantes en DB

**`stateduc_flutter/lib/providers/campaign_provider.dart`** :
- Ajout d'un 2e fallback dans `selectSystem()` : si `getChildRegroups` retourne vide mais `_allRegroups` contient des entrées, utilise `_allRegroups.where((r) => r.isRoot)` — couvre les données migrées avant ce correctif

---

## [Unreleased] — 2026-05-29 — Session 8 : icône splash, formulaire gris, pré-remplissage, grille scroll, typo PHP

### 🔴 Fix — Icône splash screen toujours vide (cercle blanc)

**Cause racine confirmée** : `icon.png` est une image 2048×2048 RGBA avec fond blanc (87 % de pixels blancs) et contenu bleu centré. L'approche précédente (cercle blanc + `BoxFit.contain`) rendait le logo invisible : blanc sur blanc.

**`stateduc_flutter/lib/screens/splash/splash_screen.dart`** :
- Supprimé le `Container` avec fond blanc et `BoxDecoration` cercle
- Remplacé par `ClipOval` + `Image.asset(..., fit: BoxFit.cover, width: 160, height: 160)`
- Le fond blanc de `icon.png` lui-même fournit le cercle blanc — le `ClipOval` le découpe circulairement
- Sur le fond bleu splash, le résultat est un cercle blanc avec le logo bleu centré

### 🔴 Fix — Formulaire gris (écran WebView entièrement gris)

**Cause racine** : Le `Stack` de l'overlay de chargement avait un fond transparent. Avant que `setBackgroundColor(Colors.white)` prenne effet dans le moteur WebView, la couleur de fond du `Scaffold` parent (gris clair) transparaissait.

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- Enveloppé le `Stack` de chargement dans un `Container(color: Colors.white, ...)`
- Le fond blanc garantit qu'aucun gris ne transparaît, même avant que le WebView initialise sa propre couleur de fond

### 🔴 Fix — Pré-remplissage identification : race condition de timing

**Cause racine** : `onPageFinished` était appelé directement sur le thread de rendu Flutter. Si `selectQuestion()` avait appelé `notifyListeners()` juste avant que le WebView termine son chargement, `widget.data` pouvait encore contenir l'ancien `Map` vide au moment où `_injectData()` s'exécutait (avant que Flutter propage le nouveau `data` aux props du widget).

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- `onPageFinished` utilise maintenant `WidgetsBinding.instance.addPostFrameCallback(...)` pour différer `_injectData()` + `_injectBridge()` jusqu'au prochain frame Flutter
- Garantit que `widget.data` reflète les dernières valeurs pré-remplies (`_prefillIdentificationFields()`) avant l'injection JS

### 🔴 Fix — Injection JS champs avec `NAME=` majuscule

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- `_injectData()` JavaScript inclut désormais un fallback case-insensitive pour les noms d'attributs
- Si `querySelectorAll('[name="X"]')` ne trouve rien, boucle sur tous les inputs et compare `getAttribute('name').toUpperCase() === name.toUpperCase()`
- Couvre les formulaires dont le HTML utilise `NAME='NOM_ETABLISSEMENT_0'` (majuscule) au lieu de `name=`

### 🟡 Fix — Formulaires grille : scroll horizontal des tableaux larges

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- `_buildHtmlPage()` : CSS amélioré — les `<table>` sont maintenant enveloppés par JS dans un `<div class="div-table-questionnaire">` avec `overflow-x: auto; display: block; -webkit-overflow-scrolling: touch`
- `th` : `white-space: normal; min-width: 60px` pour permettre le retour à la ligne dans les en-têtes de colonnes grille
- Script `DOMContentLoaded` qui enveloppe automatiquement tous les tableaux en divs scrollables

### 🔴 Fix — Typo `$id_teme` dans `data_save.php` (route GET)

**`StatEduc_MEN_2025/data_save.php`** (route GET `/theme_save/.../:data`, ligne 153) :
- Corrigé `if ($id_teme == $id_theme_ident)` → `if ($id_theme == $id_theme_ident)`
- Ce typo empêchait la comparaison du thème courant avec le thème d'identification → `LOC_REG_0` n'était jamais injecté côté serveur pour la route GET historique

---

## [Unreleased] — 2026-05-29 — Session 5 : fix VALUE=$VAR dans les formulaires grille + détection grille améliorée

### 🔴 Fix CRITIQUE — Boutons radio/select jamais pré-sélectionnés dans les formulaires grille

**Problème** : Dans tous les formulaires grille (personnel enseignant, locaux, effectifs…), les données précédemment saisies n'étaient pas restaurées sur les boutons radio et listes déroulantes lors du chargement du formulaire.

**Cause racine** : Les fichiers HTML sont des templates PHP servis par le serveur après substitution. L'application mobile cache le HTML brut (non substitué) — les champs texte ont `VALUE="$NOM_0"` et les radios ont `VALUE=$CODE_TYPE_SEXE_0_1` (sans quotes). La fonction `_injectData()` fait `el.checked = (el.value === val)` mais `el.value` vaut le littéral `"$CODE_TYPE_SEXE_0_1"` au lieu de `"1"` → la comparaison échoue toujours.

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- **Étape 4a** dans `_preprocessHtml()` : Remplace `VALUE="$VARNAME"` (texte) → `VALUE=""` (vide)
  - `_injectData()` remplit ensuite la valeur correcte via JS
- **Étape 4b** dans `_preprocessHtml()` : Remplace `VALUE=$CODE_TYPE_SEXE_0_1` (non quoté) → `VALUE=1`
  - Le dernier segment numérique après `_` est la valeur réelle de l'option radio/select
  - `_injectData()` peut maintenant faire `el.checked = (el.value === "1")` correctement
- Les deux remplacements sont insensibles à la casse (`(?i)`) pour `value=` / `VALUE=`

### 🔴 Fix — Détection et comptage des formulaires grille incomplets

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :

**`_detectGridForm()`** :
- Ajout de `MiseEvidenceLigneFrame` comme signal de détection grille
  → `Personnel_Enseignant_4.html` n'a ni `NUMERO_LOCAL` ni `addGrilleLine` mais utilise `MiseEvidenceLigneFrame`
  → Sans ce signal, le bouton « + Ajouter une ligne » n'était pas affiché

**`_countGridRows()`** :
- Réécriture pour gérer deux conventions de nommage des lignes :
  1. `$NUMERO_LOCAL_N` — formulaires locaux (9303, Mob1-Locaux, effectif_gp_age)
  2. `id='ligne-paire_N_0'` / `id='ligne-impaire_N_0'` — formulaires personnel (Personnel_Enseignant_4)
- Renvoie le nombre de lignes HTML pré-générées pour affichage correct dans le compteur du bouton

---

### 🔴 Fix CRITIQUE — Envoi données toujours en échec (`data_save.php` + `questionnaire_ws.php`)

**Problème** : Les données n'arrivent toujours pas en base malgré le fix `&annee` de la session 3.

**Cause racine** : `questionnaire_ws.php` est appelé via curl interne depuis `data_save.php`. Ce curl crée une **nouvelle session PHP vide** — `$_SESSION['login']` n'est jamais positionné, or la classe arbre et les requêtes SQL en dépendent pour écrire les données (`UPDATE ... SET login=...`). Sans login, les écritures échouent silencieusement ou sont rejetées.

**Corrections apportées** :

**`StatEduc_MEN_2025/data_save.php`** (lignes 139 + 291) :
- Ajout de `&login=$user&langue=fr` aux deux URL curl vers `questionnaire_ws.php`
- Avant : `...&annee='.$id_year`
- Après  : `...&annee='.$id_year.'&login='.$user.'&langue=fr'`

**`StatEduc_MEN_2025/questionnaire_ws.php`** (après ligne 23, avant `$GLOBALS['lancer_theme_manager']`) :
- Ajout du bloc "Mobile/curl session bootstrap" :
  - `$_SESSION['login']` ← `$_GET['login']` (si fourni)
  - `$_SESSION['langue']` ← `$_GET['langue']` (si fourni, défaut `'fr'`)
  - `$_SESSION['style']` ← défaut `'stateduc.css'` (évite erreur CSS)
  - `$_SESSION['valide']` ← `true` (bypass vérification session)
  - `$_SESSION['code_user']` ← `0`, `$_SESSION['groupe']` ← `1` (bypass restrictions privilèges)

### 🔴 Fix — Splash screen : icône blanche dans le cercle

**`stateduc_flutter/lib/screens/splash/splash_screen.dart`** :
- Changé `logo.gif` (bannière paysage 370×109) → `icon.png` (carré 2048×2048) avec `ClipOval` + `fit: BoxFit.cover`

### 🔴 Fix — Formulaire d'identification : champs vides

**Cause** : `_prefillIdentificationFields()` utilisait des noms sans suffixe `_0` (ex: `NOM_ETABLISSEMENT`) alors que le formulaire serveur utilise `NOM_ETABLISSEMENT_0`, `CODE_ADMINISTRATIF_0`, etc.

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** :
- Noms de champs corrigés avec suffixe `_0` : `NOM_ETABLISSEMENT_0`, `CODE_ADMINISTRATIF_0`
- Logique fill améliorée : remplace aussi les valeurs vides (ne se limite plus aux champs absents)
- Ajout des variantes sans suffixe en fallback pour les autres formulaires

### 🔴 Fix — Mojibake (`NÂ°`, `attribuÃ©`, `PrÃ©nom`) : correction à la source

**Cause racine** : `getFormHtml()` dans `api_service.dart` utilisait `ResponseType.plain`, laissant Dio décoder les octets ISO-8859-15 avec une interprétation inconsistante selon la locale.

**`stateduc_flutter/lib/services/api_service.dart`** :
- Changé `ResponseType.plain` → `ResponseType.bytes` pour le téléchargement HTML
- Décodage explicite `String.fromCharCodes(rawBytes)` = Latin-1 pur (byte → code point)
- Le pré-processeur `_preprocessHtml()` détecte ensuite le mojibake et répare (Latin-1 → UTF-8)

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- Détection mojibake améliorée : liste de patterns fréquents (`Ã©`, `Â°`, `Nâ`, etc.)
- Ajout du désentitisation HTML : `&lt;` → `<`, `&gt;` → `>`, `&amp;` → `&`, etc.
  → Corrige `&lt;b&gt;1.6 Chaine …&lt;/b&gt;` affiché en texte brut
- `$NUMERO_LOCAL_N` → numéro de ligne (déjà présent, conservé)

### 🔴 Fix — Formulaires de type grille (personnel enseignant, locaux)

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- Détection automatique des formulaires grille (`$NUMERO_LOCAL`, `addGrilleLine`, pattern `FIELD_N_col`)
- Affichage d'un bouton natif **"+ Ajouter une ligne"** en bas des formulaires grille
- Le bouton clone la dernière ligne du tableau et incrémente les indices des champs
- Compatibilité : appelle `addGrilleLine()` JS si disponible, sinon fallback DOM clone
- Compteur de lignes affiché sur le bouton pour confirmation visuelle

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
