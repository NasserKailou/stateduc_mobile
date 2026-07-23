# StatEduc Mobile — Système de collecte de données éducatives

**Auteur :** Abdoul Nasser Kailou  
**Projet :** PAQABU / UNESCO — Ministère de l'Éducation Nationale du Burundi  
**Version :** 2.0 — Juillet 2026  
**Dépôt :** `https://github.com/NasserKailou/stateduc_mobile`

---

## Présentation

**StatEduc Mobile** est le système complet de collecte de données statistiques éducatives pour le **Ministère de l'Éducation Nationale (MEN) du Burundi**, développé dans le cadre du projet **PAQABU** (Programme d'Appui à la Qualité de l'Éducation de Base au Burundi) sous l'égide de l'**UNESCO/IIEP**.

Le système se compose de deux parties :

| Composant | Technologie | Rôle |
|-----------|-------------|------|
| `stateduc_flutter/` | Flutter 3.35 / Dart 3.9 | Application Android native (agents de collecte) |
| `StatEduc_burundi/` | PHP (Slim v2) / SQL Server 2012 | Serveur REST API + base SISED |

---

## Arborescence du dépôt

```
stateduc_mobile/
│
├── stateduc_flutter/           ← Application Flutter Android
│   ├── lib/
│   │   ├── main.dart
│   │   ├── models/
│   │   ├── viewmodels/
│   │   ├── views/
│   │   ├── services/
│   │   │   ├── api_service.dart          ← Client Dio + intercepteurs
│   │   │   ├── database_service.dart     ← SQLite (sqflite)
│   │   │   └── coherence_evaluator.dart  ← Moteur cohérence offline
│   │   └── widgets/
│   ├── android/
│   │   ├── app/
│   │   │   └── build.gradle              ← Signature release configurée
│   │   └── key.properties                ← (NON versionné — confidentiel)
│   └── pubspec.yaml
│
├── StatEduc_burundi/           ← Serveur PHP (SISED)
│   ├── config_app.php          ← Configuration + bypass NAT
│   ├── common_ws.php           ← Bootstrap web services
│   ├── data_save.php           ← Sauvegarde données collecte
│   ├── data_reload.php         ← Rechargement données
│   ├── data_rules.php          ← Règles cohérence
│   ├── data_controle.php       ← Contrôle cohérence
│   ├── questionnaire_ws.php    ← API principale (Slim v2)
│   └── server-side/
│       └── classes/metier/
│           ├── user.class.php
│           ├── campagne.class.php
│           └── questionnaire.class.php
│
├── docs/                       ← Documentation et livrables
│   └── generate_deliverables.py  ← Script Python → .docx
│
├── README.md                   ← Ce fichier
├── ANALYSIS.md                 ← Analyse technique application
├── RESTITUTION_TECHNIQUE_STATEDUC_MOBILE.md
├── administration.md           ← Guide d'administration
├── notepresentation.md         ← Note de présentation
└── recapitulatif.md            ← Récapitulatif technique
```

---

## Prérequis

### Application Flutter

- Flutter SDK ≥ 3.35 ([flutter.dev](https://flutter.dev))
- Dart ≥ 3.9
- Android SDK (API 21+)
- Un appareil Android ou émulateur

### Serveur PHP

- XAMPP (Windows) ou Apache + PHP 7.4+
- SQL Server 2012 ou supérieur
- Extension PHP : `php_sqlsrv`, `php_pdo_sqlsrv`
- Composer (gestion des dépendances PHP)

---

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/NasserKailou/stateduc_mobile.git
cd stateduc_mobile
git checkout ak_secure
```

### 2. Configurer le serveur PHP

```bash
# Copier les fichiers dans le répertoire web Apache
cp -r StatEduc_burundi/ /xampp/htdocs/stateduc/

# Configurer la connexion SQL Server dans config_app.php
# Éditer les variables $SISED_DB_* avec les paramètres de votre instance
```

Vérifier la configuration dans `config_app.php` :

```php
$SISED_DB_HOST     = 'localhost';          // Hôte SQL Server
$SISED_DB_NAME     = 'stateduc_men';       // Nom de la base
$SISED_DB_USER     = 'sa';                 // Utilisateur SQL Server
$SISED_DB_PASSWORD = '***';               // Mot de passe
$SISED_AURL        = 'http://stateduc.mnineduc.gov.bi/stateduc/'; // URL publique
```

### 3. Construire l'application Flutter

```bash
cd stateduc_flutter/

# Récupérer les dépendances
flutter pub get

# Build APK debug (test)
flutter build apk --debug

# Build APK release (livraison)
# Prérequis : android/app/stateduc_release.jks + android/key.properties
flutter build apk --release
```

L'APK release se trouve dans :
```
stateduc_flutter/build/app/outputs/flutter-apk/app-release.apk
```

---

## Génération des livrables Word

```bash
# Installer les dépendances Python
pip install python-docx

# Générer tous les documents Word
cd docs/
python generate_deliverables.py
```

Les fichiers `.docx` sont générés dans `docs/output/`.

---

## Documentation

| Document | Fichier | Description |
|----------|---------|-------------|
| Présentation technique | `stateduc_flutter/presentation.md` | Architecture complète du système |
| Architecture technique Flutter | `stateduc_flutter/architecture_technique.md` | Guide développeur Flutter |
| Guide d'administration | `administration.md` | Manuel utilisateur A→Z |
| Note de présentation | `notepresentation.md` | Synthèse pour bénéficiaires |
| Récapitulatif technique | `recapitulatif.md` | Guide mainteneur / développeur |
| Analyse application | `ANALYSIS.md` | Analyse technique détaillée |
| Restitution technique | `RESTITUTION_TECHNIQUE_STATEDUC_MOBILE.md` | Compte-rendu des travaux |
| Notes de version Flutter | `stateduc_flutter/CHANGELOG.md` | Journal des modifications Flutter |
| Notes de version serveur | `StatEduc_burundi/CHANGELOG.md` | Journal des modifications PHP |
| Signature release | `stateduc_flutter/RELEASE_SIGNING.md` | Procédure build APK release |

---

## Branches Git

| Branche | Rôle |
|---------|------|
| `ak_secure` | Branche principale de développement (ce dépôt parent) |
| `ak_main` | Branche du sous-module `stateduc_mobile` |
| `main` | Branche de production |

---

## Licence et contacts

- **Auteur** : Abdoul Nasser Kailou
- **Projet** : PAQABU — Programme d'Appui à la Qualité de l'Éducation de Base au Burundi
- **Commanditaire** : Ministère de l'Éducation Nationale du Burundi / UNESCO-IIEP
- **Dépôt** : [github.com/NasserKailou/stateduc_mobile](https://github.com/NasserKailou/stateduc_mobile)

---

*Juillet 2026*
