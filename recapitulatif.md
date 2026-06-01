# Récapitulatif Technique — StatEduc Mobile MEN
**Projet :** Application mobile de collecte statistique pour le Ministère de l'Éducation Nationale  
**Dépôt :** https://github.com/NasserKailou/stateduc_mobile  
**Branche principale :** `ak_main`  
**Date :** Juin 2026

---

## 1. Vue d'ensemble du projet

StatEduc Mobile est une application Flutter qui permet aux agents de collecte du MEN de saisir des données statistiques scolaires (effectifs, infrastructures, personnels) sur tablette Android, même **hors ligne**, et de les synchroniser avec le serveur StatEduc existant.

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
| Fichier | Description |
|---------|-------------|
| `stateduc_flutter/lib/services/api_service.dart` | Service HTTP central (singleton Dio) |
| `stateduc_flutter/lib/services/coherence_evaluator.dart` | Moteur évaluation cohérence offline |
| `stateduc_flutter/lib/services/database_service.dart` | Service SQLite — 13 tables |
| `stateduc_flutter/lib/providers/data_entry_provider.dart` | Provider saisie données |
| `stateduc_flutter/lib/providers/campaign_provider.dart` | Provider gestion campagnes |
| `stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart` | Widget WebView formulaires HTML |
| `stateduc_flutter/lib/screens/schools/campaign_detail_screen.dart` | Écran navigation établissements |
| `stateduc_flutter/lib/screens/data_entry/school_data_screen.dart` | Écran saisie données école |

---

## 3. Chaîne de sauvegarde des données

### Flux complet Flutter → Base de données

```
[Agent Flutter]
    │
    ▼ Appuie sur "Envoyer"
DataEntryProvider.sendToServer()
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
  ID_ASSOC_REG_THM  → identifiant de l'association (ctrl_id)
  ID_REGLE_THEME    → règle R1 (ex: "Total élèves filles")
  ID_REGLE_THEME_ASSOC → règle R2 (ex: "Total élèves")
  CRITERE           → opérateur (<=, >=, =, <, >, <>)
  ACTIVER_CTRL      → 1 = contrôle actif

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

### 4.4 Contrôle serveur (controle_theme_batch.class.php)

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

### 4.5 ID thème composite

```
ID thème composite : 15702 = thème 1570 + secteur 2
→ PHP : controle_strip_theme_id('15702', '2') → '1570'
→ Dart : même logique dans DataEntryProvider
```

---

## 5. Correctifs importants par session

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

---

## 6. Gestion du mojibake (encodage)

### Problème
Le serveur retourne du HTML encodé en **ISO-8859-15** (encodage Windows/legacy).
Dart/Flutter utilise UTF-8 nativement → caractères accentués corrompus.

### Solution (api_service.dart + dynamic_form_widget.dart)

```dart
// ÉTAPE 1 : api_service.dart — getFormHtml()
// Récupérer les bytes bruts (pas de décodage automatique Dio)
final step2 = await _dio.get(htmlUrl, options: Options(responseType: ResponseType.bytes));
// Convertir byte-à-byte en Latin-1 (chaque byte → code Unicode équivalent)
final html = String.fromCharCodes(rawBytes); // préserve 0xE9 → 'é' (Latin-1)

// ÉTAPE 2 : dynamic_form_widget.dart — _preprocessHtml()
// Tenter décodage UTF-8 propre
final decoded = utf8.decode(latin1Bytes, allowMalformed: true);
// Compter les caractères de remplacement U+FFFD
final replacements = '\uFFFD'.allMatches(decoded).length;
final threshold = (latin1Bytes.length * 0.05).ceil(); // seuil 5%
if (replacements <= threshold) {
  html = decoded; // UTF-8 valide → utiliser
} else {
  html = String.fromCharCodes(latin1Bytes); // trop de corruption → Latin-1 brut
}
```

---

## 7. Structure de la base de données SQLite

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

---

## 8. Cloner le projet depuis GitHub

```bash
# 1. Cloner le dépôt
git clone https://github.com/NasserKailou/stateduc_mobile.git
cd stateduc_mobile

# 2. Se positionner sur la branche de développement
git checkout ak_main

# 3. Vérifier les fichiers
ls StatEduc_MEN_2025/    # fichiers PHP serveur
ls stateduc_flutter/     # projet Flutter
```

---

## 9. Préparer l'environnement de développement

### 9.1 Prérequis

| Outil | Version recommandée | Lien |
|-------|---------------------|------|
| Flutter SDK | 3.x (stable) | https://flutter.dev/docs/get-started/install |
| Dart SDK | inclus avec Flutter | — |
| Android Studio | 2023.x ou supérieur | https://developer.android.com/studio |
| Git | 2.x | https://git-scm.com |
| PHP | 7.4 ou 8.x | Pour le serveur |

### 9.2 Installation Flutter (Linux/macOS)

```bash
# Télécharger Flutter SDK
wget https://storage.googleapis.com/flutter_infra_release/releases/stable/linux/flutter_linux_3.x.x-stable.tar.xz
tar xf flutter_linux_3.x.x-stable.tar.xz

# Ajouter au PATH
export PATH="$PATH:`pwd`/flutter/bin"

# Vérifier l'installation
flutter doctor

# Activer le support Android
flutter config --enable-android
```

### 9.3 Installer les dépendances Flutter

```bash
cd stateduc_mobile/stateduc_flutter

# Installer les packages pub.dev
flutter pub get

# Vérifier les dépendances clés
cat pubspec.yaml | grep -E "dio|sqflite|provider|webview"
```

### 9.4 Dépendances principales (pubspec.yaml)

```yaml
dependencies:
  flutter:
    sdk: flutter
  dio: ^5.x          # Client HTTP avec intercepteurs
  sqflite: ^2.x      # Base de données SQLite locale
  provider: ^6.x     # Gestion d'état (ChangeNotifier)
  webview_flutter: ^4.x  # Affichage formulaires HTML
  path: ^1.x         # Manipulation chemins fichiers
  path_provider: ^2.x    # Chemins système Android
```

### 9.5 Configuration du serveur PHP

```bash
# Copier les fichiers PHP sur le serveur
scp -r StatEduc_MEN_2025/ user@serveur:/var/www/html/

# Vérifier les permissions
chmod 755 /var/www/html/StatEduc_MEN_2025/*.php

# Configurer l'URL de base dans config.php (selon votre déploiement)
```

---

## 10. Compiler l'APK Android

### 10.1 Mode Debug (développement)

```bash
cd stateduc_mobile/stateduc_flutter

# Connecter une tablette Android (activer mode développeur + débogage USB)
adb devices

# Lancer en mode debug directement sur la tablette
flutter run

# Ou générer APK debug
flutter build apk --debug
# APK généré : build/app/outputs/flutter-apk/app-debug.apk
```

### 10.2 Mode Release (production)

```bash
# Générer APK de production (optimisé, obfusqué)
flutter build apk --release

# APK généré :
# build/app/outputs/flutter-apk/app-release.apk

# Pour un APK universel (toutes architectures) :
flutter build apk --release --split-per-abi
# Génère : app-armeabi-v7a-release.apk, app-arm64-v8a-release.apk, app-x86_64-release.apk
```

### 10.3 Installer sur tablette

```bash
# Via USB
adb install build/app/outputs/flutter-apk/app-release.apk

# Ou copier l'APK et installer manuellement depuis le gestionnaire de fichiers
```

### 10.4 Résolution des problèmes courants

| Problème | Solution |
|---------|---------|
| `flutter doctor` signale SDK Android manquant | Installer Android Studio + SDK API 21+ |
| `Gradle build failed` | `cd android && ./gradlew clean` |
| `Certificate error` au runtime | Normal sur intranet — SSL bypass intégré |
| `TimeoutException` | Vérifier CURLOPT_TIMEOUT=120 dans data_save.php |
| `nb_regles=0` | Vérifier yearCode dans URL data_rules.php |

---

## 11. Points d'attention pour la maintenance

1. **CRLF dans controle_theme_batch.class.php** : Ce fichier a des fins de ligne Windows (`\r\n`). Ne pas l'ouvrir avec un éditeur qui convertit automatiquement les fins de ligne sans reconfigurer. Utiliser Python pour le modifier (voir `/tmp/patch_controle.py`).

2. **CURLOPT_TIMEOUT** : Actuellement à 120s dans `data_save.php`. Si le serveur devient plus lent, augmenter cette valeur. Ne jamais la mettre à 0 (illimité) en production.

3. **yearCode** : Paramètre critique passé dans toutes les URLs REST `/.../{yearCode}`. Sans ce paramètre, les fonctions PHP utilisant `$_SESSION['annee']` retourneront des résultats vides (0 règles, échec de sauvegarde des logs).

4. **Encodage ISO-8859-15** : Le serveur ne sera pas migré vers UTF-8. La logique de correction mojibake dans `dynamic_form_widget.dart` (`_preprocessHtml()`) doit être maintenue.

5. **Base DICO** : Les règles de cohérence sont dans la base DICO (séparée de la base de collecte). La connexion `$GLOBALS['conn_dico']` doit toujours être configurée dans l'environnement PHP.

---

*Document généré dans le cadre du projet StatEduc Mobile MEN — Sessions 1-15 — 2026*
