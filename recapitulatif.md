# Récapitulatif Technique — StatEduc Mobile MEN
**Projet :** Application mobile de collecte statistique pour le Ministère de l'Éducation Nationale du Burundi
**Dépôt :** https://github.com/NasserKailou/stateduc_mobile
**Branche principale :** `ak_main`
**Date :** Juin 2026
**Dernière session :** Session 20 — en-tête institutionnel + administration.md + docs mis à jour

---

## 1. Vue d'ensemble du projet

StatEduc Mobile est une application Flutter qui permet aux agents de collecte du MEN du Burundi de saisir des données statistiques scolaires (effectifs, infrastructures, personnels) sur tablette Android, même **hors ligne**, et de les synchroniser avec le serveur StatEduc existant.

### Architecture globale

```
[Tablette Android]                    [Serveur Apache / PHP]
┌──────────────────┐                  ┌──────────────────────┐
│  Flutter App     │                  │  StatEduc Web (PHP)  │
│  ─────────────   │  HTTP REST       │  ────────────────    │
│  ApiService      │ ←───────────────→│  data_save.php       │
│  (Dio client)    │                  │  data_controle.php   │
│  DatabaseService │                  │  data_rules.php      │
│  (SQLite local)  │                  │  data_reload.php     │
│  CoherenceEval.  │                  │  questionnaire_ws.php│
└──────────────────┘                  │  (Oracle/MySQL DB)   │
                                      └──────────────────────┘
```

### Technologies
| Couche | Technologie |
|--------|-------------|
| Mobile | Flutter/Dart, Provider (ChangeNotifier), Dio HTTP, sqflite |
| Serveur | PHP Slim v2, AdoDB (Oracle/MySQL), CURL interne |
| Base de données | Oracle / MySQL côté serveur, SQLite côté mobile |

---

## 2. Fichiers modifiés — Inventaire complet

### Fichiers PHP (serveur)
| Fichier | Description |
|---------|-------------|
| `StatEduc_MEN_2025/data_save.php` | Route POST envoi formulaire — anti-deadlock, CURLOPT_TIMEOUT 120s, yearCode |
| `StatEduc_MEN_2025/data_controle.php` | Route GET contrôle cohérence serveur post-envoi |
| `StatEduc_MEN_2025/data_rules.php` | Route GET règles cohérence pour évaluation offline |
| `StatEduc_MEN_2025/data_reload.php` | Route GET rechargement HTML pré-rempli |
| `StatEduc_MEN_2025/server-side/classes/metier/controle_theme_batch.class.php` | Moteur de contrôle de cohérence (commentaires français ajoutés) |

### Fichiers Flutter/Dart (mobile)
| Fichier | Dernière modif. | Description |
|---------|-----------------|-------------|
| `stateduc_flutter/lib/services/api_service.dart` | **Session 19** | Service HTTP central — `sendTimeout=null`, `receiveTimeout=600s`, `_withRetry<T>()` + `onRetry` callback |
| `stateduc_flutter/lib/services/coherence_evaluator.dart` | Session 15 | Moteur évaluation cohérence offline |
| `stateduc_flutter/lib/services/database_service.dart` | Session 17 | Service SQLite — 13 tables + `getDistinctEtabQstWithData()` |
| `stateduc_flutter/lib/providers/data_entry_provider.dart` | **Session 19** | Provider saisie — debounce cohérence offline, `kMaxSendAttempts=3`, `_sendAttempt`, `finally` reset |
| `stateduc_flutter/lib/providers/campaign_provider.dart` | Session 16 | Provider gestion campagnes |
| `stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart` | Session 16 | Widget WebView formulaires HTML |
| `stateduc_flutter/lib/screens/login/pin_screen.dart` | **Session 20** | En-tête institutionnel ("République du Burundi" + "Ministère de l'Éducation Nationale") en italique avant le drapeau |
| `stateduc_flutter/lib/screens/schools/campaign_detail_screen.dart` | Session 17 | Écran navigation établissements — bouton envoi global campagne |
| `stateduc_flutter/lib/screens/data_entry/school_data_screen.dart` | **Session 19** | `LinearProgressIndicator` pendant `isCheckingOffline` ; overlay "Envoi… (tentative N/3)" |
| `stateduc_flutter/lib/screens/settings/settings_screen.dart` | Session 17 | Paramètres — TabBar contrasté |

### Documents
| Fichier | Dernière modif. | Description |
|---------|-----------------|-------------|
| `administration.md` | **Session 20** | Guide complet administrateur A→Z (nouveau) |
| `notepresentation.md` | Session 18 | Note transfert de compétences (bénéficiaires) |
| `recapitulatif.md` | **Session 20** | Architecture, correctifs, guide développeur (ce fichier) |
| `stateduc_flutter/CHANGELOG.md` | **Session 20** | Historique détaillé des modifications Flutter |

---

## 3. Chaîne de sauvegarde des données

### Flux complet Flutter → Base de données

```
[Agent Flutter]
    │
    ▼ Appuie sur "Envoyer" (formulaire courant) OU "Envoyer tous les formulaires"
DataEntryProvider.sendToServer()             — envoi d'un seul formulaire
DataEntryProvider.sendAllFormsForSchool()    — envoi global établissement courant
DataEntryProvider.sendAllFormsForCampaign()  — envoi global tous établissements
    │
    ▼ POST /data_save.php/theme_save/{login}/{campId}/{sysId}/{qstId}/{etabId}/{filter}/0/{yearCode}
    │  Content-Type: application/x-www-form-urlencoded
    │  Body: field1=valeur1&field2=valeur2&...&LOC_REG_0={regroupId}&switch_theme_id=&save_and_prev=&save_and_next=
    │
    ▼ data_save.php (PHP Slim v2)
    │  1. Vérification droits : DICO_FIXE_REGROUPEMENT → fallback ADMIN_USERS
    │  2. Récupération yearCode : URL (mobile) > $_SESSION['annee'] > PARAM_DEFAUT
    │  3. session_write_close()  ← ANTI-DEADLOCK (libère le verrou session Apache)
    │  4. CURL interne POST → questionnaire_ws.php
    │     CURLOPT_TIMEOUT = 120s (augmenté session 14, était 60s)
    │
    ▼ questionnaire_ws.php
    │  1. Chargement du thème PHP (include fichier thème)
    │  2. Classe arbre : écriture en base Oracle/MySQL
    │  3. Émet "ISOKSAVEINDATABASE" si succès
    │
    ▼ data_save.php (retour)
    │  → Retourne { se_status:200, se_data:"OKSAVE" } si succès
    │  → Retourne { se_status:400, se_data:"message" } si erreur
    │  → Log via saveLogInfo() dans table de journalisation
    │
    ▼ ApiService.saveData() Flutter
    │  → _withRetry() : 2 re-tentatives automatiques sur sendTimeout/receiveTimeout/unknown
    │  → Interprète OKSAVE, KOSAVE, se_status 400
    │
    ▼ DataEntryProvider
    │  → Marque données as is_sent=1 dans SQLite collected_data
    │  → Lance checkCoherence() → data_controle.php
```

### Format du corps POST
```
# Champs radio : "NOM_CHAMP#ID_OPTION" = "1" → transformé en "NOM_CHAMP=ID_OPTION"
# Autres champs : remplacement "/" → "_slh_" uniquement (pas d'encodeURIComponent)
# Obligatoire fin : switch_theme_id=&save_and_prev=&save_and_next=
# Premier thème : LOC_REG_0={idRegroupEtab}
```

---

## 4. Contrôles de cohérence — Implémentation détaillée

### 4.1 Architecture à deux niveaux

```
NIVEAU 1 — OFFLINE (avant envoi, pendant la saisie)
┌─────────────────────────────────────────────────────┐
│ data_rules.php → SQLite coherence_rules              │
│   → CoherenceEvaluator.evaluate()                   │
│   → Regex sur SQL → valeur dans collected_data      │
│   → Affiche bannière OfflineCoherenceBanner          │
│                                                     │
│ DÉCLENCHEMENTS (7 au total — état session 18) :     │
│   • updateField() debounce 800 ms                   │
│   • saveLocally() background                        │
│   • selectQuestion() formulaire rempli              │
│   • selectFilter() changement période               │
│   • _fetchAndStoreCoherenceRulesBackground()        │
│   • _autoReloadFromServerBackground()               │
│   • sendToServer() → API (côté serveur)             │
└─────────────────────────────────────────────────────┘

NIVEAU 2 — SERVEUR (après envoi réussi)
┌─────────────────────────────────────────────────────┐
│ data_controle.php → controle_theme_batch             │
│   → SQL réel sur Oracle/MySQL                       │
│   → Retourne violations JSON                        │
│   → Affiche dialog CoherenceErrorDialog              │
└─────────────────────────────────────────────────────┘
```

### 4.2 Modèle des règles (base DICO)

```
DICO_REGLE_THEME_ASSOC
  ID_ASSOC_REG_THM     → identifiant de l'association (ctrl_id)
  ID_REGLE_THEME       → règle R1 (ex: "Total élèves filles")
  ID_REGLE_THEME_ASSOC → règle R2 (ex: "Total élèves")
  CRITERE              → opérateur (<=, >=, =, <, >, <>)
  ACTIVER_CTRL         → 1 = contrôle actif

DICO_REGLE_THEME
  ID_REGLE_THEME    → identifiant règle
  ID_THEME          → thème de collecte
  SQL_REGLE_THEME   → requête SQL avec variables PHP ($code_etablissement, etc.)
```

### 4.3 Évaluation offline (CoherenceEvaluator)

```dart
// 1. Construction de la map valeurs
Map<String,double> values = {
  "NB_FILLES": 42.0,   // depuis collected_data SQLite
  "NB_TOTAL": 85.0,    // depuis formData en mémoire (override)
};

// 2. Extraction valeur par regex
// sql = "SELECT SUM(NB_FILLES) FROM COLLECTE WHERE CODE_ETAB='X'"
// → extrait "NB_FILLES" → values["NB_FILLES"] = 42.0

// 3. Application opérateur
// critere = "<="  →  violated = !(42.0 <= 85.0) = false  → OK
// critere = "<="  →  violated = !(90.0 <= 85.0) = true   → VIOLATION
```

### 4.4 Fix sessions 17-18 — déclenchement cohérence offline

**Session 17** : deux déclenchements ajoutés (règles arrivées + ouverture formulaire).

**Session 18** : audit révèle que les contrôles restaient absents pendant la saisie active.
Quatre déclenchements supplémentaires ajoutés :

```dart
// Dans updateField() : debounce 800 ms après chaque frappe
_coherenceDebounce = Timer(const Duration(milliseconds: 800), () {
  if (_formData.isNotEmpty && !_isCheckingOffline) checkCoherenceOffline();
});

// Dans selectFilter() : après changement de filtre/période
if (_formData.isNotEmpty) { Future(() => checkCoherenceOffline()); }

// Dans _autoReloadFromServerBackground() : après fusion données serveur
if (!_isCheckingOffline) { Future(() => checkCoherenceOffline()); }
```

**Tableau récapitulatif des 7 déclenchements (état final session 18)** :

| Événement | Méthode | Délai | Session |
|-----------|---------|-------|---------|
| Frappe dans un champ | `updateField()` debounce | 800 ms | **18** |
| Sauvegarde locale | `saveLocally()` | Immédiat | 1-16 |
| Ouverture formulaire rempli | `selectQuestion()` | Immédiat | 17 |
| Changement filtre/période | `selectFilter()` | Immédiat | **18** |
| Règles arrivées du serveur | `_fetchAndStoreCoherenceRulesBackground()` | Arrière-plan | 17 |
| Données serveur fusionnées | `_autoReloadFromServerBackground()` | Arrière-plan | **18** |
| Envoi serveur | `sendToServer()` → API | Après POST OK | 1-16 |

### 4.5 Contrôle serveur (controle_theme_batch.class.php)

```php
// Flux : __construct → get_regles() → controle_regles_theme()
//
// get_regles() :
//   - Charge R1 et R2 depuis DICO_REGLE_THEME via DICO_REGLE_THEME_ASSOC
//   - Interpole les variables PHP dans le SQL :
//     eval('$sql = "$sql_regle_theme";')
//     → $code_etablissement, $code_annee, $code_filtre remplacés
//
// controle_regles_theme() :
//   V1 = valeur_sql_regle(sql_R1)  → GetAll() sur Oracle/MySQL
//   V2 = valeur_sql_regle(sql_R2)
//   eval("if(V1 OP V2) \$OK=true; else \$OK=false;")
//   Si OK=false → $tab_regles_theme_assoc_not_ok[R1][R2] = infos violation
//
// data_controle.php lit $tab_regles_theme_assoc_not_ok et sérialise en JSON
```

### 4.6 ID thème composite

```
ID thème composite : 15702 = thème 1570 + secteur 2
→ PHP : controle_strip_theme_id('15702', '2') → '1570'
→ Dart : même logique dans DataEntryProvider
```

---

## 5. Envoi global des données — Nouvelles fonctionnalités (session 17)

### 5.1 Architecture d'envoi

```
NIVEAU 1 — Formulaire courant (existant)
  sendToServer() → envoie uniquement _selectedQuestion

NIVEAU 2 — Tous les formulaires d'un établissement (session 17)
  sendAllFormsForSchool() → itère sur _questions pour l'établissement courant
  UI : Menu ⋮ → "Envoyer tous les formulaires" dans school_data_screen.dart

NIVEAU 3 — Tous les formulaires de toute la campagne (session 17)
  sendAllFormsForCampaign() → getDistinctEtabQstWithData() → itère sur tous les couples
  UI : Bouton "Envoyer tous les établissements" dans campaign_detail_screen.dart
```

### 5.2 Méthode DB — `getDistinctEtabQstWithData(idCamp)`

```dart
// Nouvelle méthode dans database_service.dart (session 17)
// SELECT DISTINCT id_etab, id_qst FROM collected_data WHERE id_camp = ?
Future<List<Map<String, String>>> getDistinctEtabQstWithData(String idCamp)
```

---

## 6. Page d'identification — Rechargement serveur (session 17)

```dart
// _autoReloadFromServerBackground() — paramètre forceOverwrite (session 17)
void _autoReloadFromServerBackground({bool forceOverwrite = false}) {
  final localWasEmpty = _formData.isEmpty || forceOverwrite;
  // Si forceOverwrite = true : les données serveur remplacent toujours les données locales
}

// Appel depuis selectQuestion() :
_autoReloadFromServerBackground(forceOverwrite: isIdentificationForm);
```

---

## 7. Timeouts Dio — Configuration actuelle (session 19)

| Paramètre | Session 17 | Session 19 (actuel) | Raison du changement |
|-----------|-----------|---------------------|---------------------|
| `connectTimeout` | 60 s | 60 s | Inchangé |
| `receiveTimeout` | 300 s | **600 s** | Chaîne save→questionnaire_ws peut dépasser 5 min |
| `sendTimeout` | 300 s | **null** (désactivé) | Faux-positif Android fréquent sur réseau stable |

### Retry automatique (session 19)

```dart
static const int _kMaxRetries = 2;   // 2 re-tentatives → 3 essais au total
static const int _kRetryDelay = 5;   // délai progressif : 5s × numéro tentative

// Ne réessaie PAS sur : ApiException (401, 404, se_status 400), connectionTimeout
// Réessaie sur : sendTimeout, receiveTimeout, DioExceptionType.unknown
```

---

## 8. Correctifs importants par session

| Session | Problème | Solution |
|---------|---------|---------|
| 12 | Deadlock Apache : session PHP non libérée avant curl interne | `session_write_close()` avant `$curl->post()` |
| 12 | `codeyear` vide après PIN → données jamais écrites | Correction lecture `codeyear` dans DataEntryProvider |
| 13 | HTTP 500 : `$data`/`$date_time` indéfinis dans `$curl->error()` | Suppression `$data` du use(), création `$date_time_err` locale |
| 13 | `saveLogInfo()` crash si appelé sans `$id_annee` | Valeur par défaut `$id_annee=0` ajoutée |
| 14 | Timeout 60s : questionnaire_ws.php peut prendre >60s | `CURLOPT_TIMEOUT = 120` |
| 14 | Mojibake partiel (`pharmacie fonctionnelleÂ ?`) | Seuil 5% U+FFFD + patterns `Â `, guillemets français |
| 14 | `nb_regles=0` cohérence offline | `yearCode` passé dans `fetchRules()` |
| 14 | Hiérarchie géo absente dans en-tête | `adminHierarchy` construit depuis breadcrumb |
| 14 | `libStatus` null dans en-tête | `resolveSchoolStatuses()` appelé après chargement |
| 15-16 | Code source non documenté | Commentaires français sur tous les fichiers Dart/PHP |
| 17 | Message "délais d'attente dépassé" sur réseau stable | `sendTimeout` 120s → 300s dans Dio |
| 17 | Cohérence offline muette après sauvegarde locale | Re-déclenchement `checkCoherenceOffline()` dès réception des règles |
| 17 | Envoi formulaire-par-formulaire uniquement | `sendAllFormsForSchool()` + `sendAllFormsForCampaign()` |
| 17 | Page identification non pré-remplie sur certaines campagnes | `forceOverwrite: true` pour formulaires d'identification |
| 17 | Onglets Settings (Serveur/PIN/Sécurité) trop gris | `labelColor`/`unselectedLabelColor` forcés sur `appBarFg` |
| **18** | Icône générique `school` à l'écran d'accueil | Drapeau Burundi `Flag_of_country.png` dans `pin_screen.dart` |
| **18** | Cohérence offline muette pendant la saisie active | Debounce 800ms dans `updateField()` + 3 nouveaux triggers |
| **19** | "Délai d'attente" sur réseau stable (faux-positif Android) | `sendTimeout = null` + `receiveTimeout = 600s` + `_withRetry()` 3 tentatives |
| **19** | Aucun retry sur erreur transitoire | `_withRetry<T>()` helper avec délai progressif 5s×N |
| **19** | Pas de retour visuel pendant retry | `_sendAttempt` field + overlay "Tentative 2/3…" |
| **20** | Identité institutionnelle absente de l'écran d'accueil | "République du Burundi" + "Ministère de l'Éducation Nationale" en italique avant le drapeau |
| **20** | Absence de guide administrateur | Création de `administration.md` (guide A→Z) |

---

## 9. Gestion du mojibake (encodage)

### Problème
Le serveur retourne du HTML encodé en **ISO-8859-15** (encodage Windows/legacy).
Dart/Flutter utilise UTF-8 nativement → caractères accentués corrompus.

### Solution (api_service.dart + dynamic_form_widget.dart)

```dart
// ÉTAPE 1 : api_service.dart — getFormHtml()
final step2 = await _dio.get(htmlUrl, options: Options(responseType: ResponseType.bytes));
final html = String.fromCharCodes(rawBytes); // préserve 0xE9 → 'é' (Latin-1)

// ÉTAPE 2 : dynamic_form_widget.dart — _preprocessHtml()
final decoded = utf8.decode(latin1Bytes, allowMalformed: true);
final replacements = '\uFFFD'.allMatches(decoded).length;
final threshold = (latin1Bytes.length * 0.05).ceil(); // seuil 5%
if (replacements <= threshold) {
  html = decoded; // UTF-8 valide → utiliser
} else {
  html = String.fromCharCodes(latin1Bytes); // trop de corruption → Latin-1 brut
}
```

---

## 10. Structure de la base de données SQLite

```sql
settings           -- clés/valeurs globales (user, url serveur, etc.)
campaigns          -- campagnes téléchargées
education_systems  -- systèmes éducatifs par campagne
regroup_types      -- types de regroupements administratifs
regroups           -- regroupements (région, département, commune...)
school_statuses    -- statuts établissements (public, privé...)
schools            -- établissements scolaires
localisations      -- liens établissement ↔ système ↔ regroupements (JSON)
questions          -- formulaires/thèmes par campagne et système
form_html          -- cache HTML des formulaires
validation_rules   -- règles de validation des champs
filter_periods     -- périodes filtre (trimestres, etc.)
collected_data     -- DONNÉES SAISIES par l'agent (field_name → field_value)
coherence_rules    -- règles cohérence offline (depuis data_rules.php)
```

### Requêtes clés pour l'envoi global

```sql
-- Tous les couples (etab, qst) ayant des données pour une campagne
SELECT DISTINCT id_etab, id_qst FROM collected_data WHERE id_camp = ?;

-- Données d'un formulaire
SELECT field_name, field_value FROM collected_data
WHERE id_camp = ? AND id_etab = ? AND id_qst = ? AND id_filter IS NULL;

-- Marquer comme envoyé
UPDATE collected_data SET is_sent = 1 WHERE id_camp = ? AND id_etab = ? AND id_qst = ?;
```

---

## 11. Cloner le projet et préparer l'environnement

```bash
# 1. Cloner
git clone https://github.com/NasserKailou/stateduc_mobile.git
cd stateduc_mobile && git checkout ak_main

# 2. Installer dépendances Flutter
cd stateduc_flutter && flutter pub get

# 3. Compiler APK debug
flutter run                              # sur tablette connectée en USB
flutter build apk --debug               # APK debug
flutter build apk --release             # APK production
flutter build apk --release --split-per-abi  # APK par architecture
```

### Dépendances principales (pubspec.yaml)

```yaml
dependencies:
  dio: ^5.x           # Client HTTP avec intercepteurs
  sqflite: ^2.x       # Base de données SQLite locale
  provider: ^6.x      # Gestion d'état (ChangeNotifier)
  webview_flutter: ^4.x   # Affichage formulaires HTML
  path_provider: ^2.x     # Chemins système Android
```

---

## 12. Points d'attention pour la maintenance

1. **`sendTimeout = null`** (session 19) : Ne jamais réactiver sans tests sur Android réel — risque de faux-positif à chaque envoi.

2. **`receiveTimeout = 600s`** : Si le XAMPP est remplacé par un serveur plus rapide, cette valeur peut être réduite à 120-300s.

3. **`_kMaxRetries = 2` / `_kRetryDelay = 5`** : Si le serveur est très instable, augmenter les retries. `DataEntryProvider.kMaxSendAttempts` doit rester synchronisé avec `_kMaxRetries + 1`.

4. **CRLF dans controle_theme_batch.class.php** : Ce fichier a des fins de ligne Windows (`\r\n`). Ne pas l'ouvrir avec un éditeur qui convertit automatiquement.

5. **CURLOPT_TIMEOUT = 120s** dans `data_save.php` : Si le serveur devient plus lent, augmenter. Ne jamais mettre à 0 (illimité) en production.

6. **yearCode** : Paramètre critique passé dans toutes les URLs REST `/{yearCode}`. Sans ce paramètre, les fonctions PHP utilisant `$_SESSION['annee']` retournent des résultats vides.

7. **Encodage ISO-8859-15** : Le serveur ne sera pas migré vers UTF-8. La logique mojibake dans `_preprocessHtml()` doit être maintenue.

8. **Base DICO** : Les règles de cohérence sont dans la base DICO (séparée de la base de collecte). La connexion `$GLOBALS['conn_dico']` doit toujours être configurée dans l'environnement PHP.

9. **`sendAllFormsForCampaign()`** utilise `SELECT DISTINCT` sur `collected_data`. Pour de grandes campagnes, envisager un index sur `(id_camp, id_etab, id_qst)`.

---

## 13. Documentation disponible

| Document | Usage | Audience |
|---------|-------|---------|
| `administration.md` | Guide complet A→Z : installation, PIN, campagne, saisie, envoi, dépannage | Administrateurs, superviseurs collecte |
| `notepresentation.md` | Support de transfert de compétences, présentation bénéficiaires | Formateurs, bénéficiaires MEN |
| `recapitulatif.md` | Architecture technique, correctifs par session, guide développeur | Développeurs, mainteneurs |
| `stateduc_flutter/CHANGELOG.md` | Historique complet des modifications Flutter session par session | Développeurs |
| `StatEduc_MEN_2025/CHANGELOG.md` | Historique des modifications PHP serveur | Développeurs PHP |
| PR #1 GitHub | Suivi des changements par session avec commentaires | Équipe projet |

---

*Document mis à jour — Projet StatEduc Mobile MEN Burundi — Sessions 1-20 — Juin 2026*
