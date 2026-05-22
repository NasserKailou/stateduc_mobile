# StatEduc Mobile

> Application mobile hybride Android de collecte de données pour le secteur éducatif en Afrique.  
> Version légère (light) de StatEduc, conçue pour la saisie terrain en mode offline avec synchronisation serveur.

---

## Table des matières

1. [Présentation](#présentation)
2. [Architecture technique](#architecture-technique)
3. [Prérequis](#prérequis)
4. [Installation de l'environnement](#installation-de-lenvironnement)
5. [Procédure de build](#procédure-de-build)
6. [Lancement et test](#lancement-et-test)
7. [Configuration de l'application](#configuration-de-lapplication)
8. [Guide d'utilisation](#guide-dutilisation)
9. [Structure du projet](#structure-du-projet)
10. [API Serveur](#api-serveur)
11. [Dépannage](#dépannage)

---

## Présentation

StatEduc Mobile permet à des agents de terrain de :

1. **Se connecter** au serveur StatEduc central pour télécharger des campagnes de collecte
2. **Saisir des données** éducatives (effectifs, résultats, équipements…) sur des formulaires dynamiques, sans connexion internet
3. **Synchroniser** les données collectées vers le serveur quand la connexion est disponible

L'application est conçue pour des **zones à faible connectivité** en Afrique subsaharienne. Elle supporte également un mode de transmission de secours par **SMS**.

---

## Architecture technique

| Composant | Technologie |
|---|---|
| Framework | Apache Cordova 13.x |
| Plateforme | Android uniquement |
| Langage | HTML5, JavaScript (ES5), CSS3 |
| UI Framework | jQuery Mobile 1.4.5 |
| Stockage local | localStorage / sessionStorage (WebView) |
| Communication | HTTP REST + HTTP Basic Authentication |
| Build | Gradle (via Cordova Android) |

---

## Prérequis

### Environnement de développement

| Outil | Version recommandée | Lien |
|---|---|---|
| **Node.js** | v14 ou v16 LTS | https://nodejs.org |
| **npm** | v6+ (inclus avec Node.js) | — |
| **Apache Cordova CLI** | v12+ | `npm install -g cordova` |
| **Java JDK** | JDK 11 ou 17 | https://adoptium.net |
| **Android Studio** | 2022+ (Flamingo ou plus récent) | https://developer.android.com/studio |
| **Android SDK** | API Level 34 (Android 14) | Via Android Studio SDK Manager |
| **Gradle** | 8.x (inclus dans Cordova Android 13) | — |

### Variables d'environnement à configurer

```bash
# Java
export JAVA_HOME=/path/to/jdk
export PATH=$PATH:$JAVA_HOME/bin

# Android SDK
export ANDROID_HOME=$HOME/Android/Sdk
export ANDROID_SDK_ROOT=$ANDROID_HOME
export PATH=$PATH:$ANDROID_HOME/platform-tools
export PATH=$PATH:$ANDROID_HOME/tools
```

> **Windows** : Ajouter ces variables dans les Variables Système du Panneau de Configuration.

### Vérification de l'environnement

```bash
node --version       # v14.x.x ou v16.x.x
npm --version        # 6.x.x ou supérieur
cordova --version    # 12.x.x
java --version       # 11.x.x ou 17.x.x
```

---

## Installation de l'environnement

### 1. Cloner le dépôt

```bash
git clone https://github.com/NasserKailou/stateduc_mobile.git
cd stateduc_mobile
```

### 2. Installer les dépendances npm

```bash
npm install
```

### 3. Ajouter la plateforme Android

> ⚠️ Si le dossier `platforms/android` existe déjà dans le dépôt, cette étape peut être ignorée.

```bash
cordova platform add android
```

Pour vérifier la plateforme installée :

```bash
cordova platform list
# Résultat attendu : android 13.x.x
```

### 4. Installer les plugins Cordova

```bash
cordova plugin add cordova-plugin-dialogs
cordova plugin add cordova-plugin-network-information
cordova plugin add cordova-plugin-vibration
cordova plugin add hazems-cordova-plugin-sms
```

Pour vérifier les plugins :

```bash
cordova plugin list
```

### 5. Vérifier la configuration de build

```bash
cordova requirements android
```

La commande doit retourner "✓" pour Java, Android SDK et Gradle. Corriger les erreurs signalées avant de continuer.

---

## Procédure de build

### Build Debug (développement)

```bash
# Build APK debug
cordova build android

# L'APK est généré dans :
# platforms/android/app/build/outputs/apk/debug/app-debug.apk
```

### Build Release (production)

#### 1. Générer un keystore (une seule fois)

```bash
keytool -genkey -v \
  -keystore stateduc.keystore \
  -alias stateduc \
  -keyalg RSA \
  -keysize 2048 \
  -validity 10000
```

> Conserver précieusement le fichier `.keystore` et les mots de passe. **Ne pas le committer dans git.**

#### 2. Configurer la signature

Créer le fichier `platforms/android/release-signing.properties` :

```properties
storeFile=../../../stateduc.keystore
storePassword=VOTRE_STORE_PASSWORD
keyAlias=stateduc
keyPassword=VOTRE_KEY_PASSWORD
```

#### 3. Construire l'APK release signé

```bash
cordova build android --release
```

L'APK signé sera dans :
```
platforms/android/app/build/outputs/apk/release/app-release.apk
```

### Build AAB (Google Play Store)

```bash
cordova build android --release -- --packageType=bundle
# Output : platforms/android/app/build/outputs/bundle/release/app-release.aab
```

---

## Lancement et test

### Sur un émulateur Android

```bash
# Lister les AVD disponibles
emulator -list-avds

# Démarrer un AVD
emulator -avd NOM_AVD &

# Déployer l'application sur l'émulateur
cordova run android --emulator
```

### Sur un appareil physique (USB)

1. Activer le **Mode Développeur** sur l'appareil Android :  
   `Paramètres → À propos du téléphone → Tapper 7 fois sur "Numéro de build"`

2. Activer le **Débogage USB** :  
   `Paramètres → Options pour les développeurs → Débogage USB`

3. Connecter l'appareil via USB et exécuter :

```bash
# Vérifier que l'appareil est détecté
adb devices

# Déployer l'application
cordova run android --device
```

### Débogage avec Chrome DevTools

1. Ouvrir Google Chrome et naviguer vers : `chrome://inspect/#devices`
2. Identifier l'application StatEduc Mobile dans la liste
3. Cliquer sur **Inspect** pour accéder à la console JavaScript, network, storage

```bash
# Pour activer les logs Cordova en temps réel
adb logcat -s CordovaLog:V
```

---

## Configuration de l'application

### Premier lancement

1. **Créer un PIN local** : à la première ouverture, l'utilisateur définit un code PIN de sécurité (4 chiffres minimum) ainsi qu'une question de sécurité et sa réponse.

2. **Configurer l'URL du serveur** : l'application redirige automatiquement vers la page de paramétrage si aucun serveur n'est configuré.
   - Saisir l'URL complète du serveur StatEduc, exemple : `http://192.168.1.100:8080`
   - Valider en cliquant sur **Valider**

> **Note** : L'URL doit commencer par `http://` (HTTP non chiffré activé dans le manifest Android via `usesCleartextTraffic="true"`). Pour la production, configurer HTTPS et modifier ce paramètre.

### Paramètres disponibles (`settings.html`)

| Paramètre | Description |
|---|---|
| URL du serveur | Adresse du serveur StatEduc |
| Changer le PIN | Modifier le code d'accès local |
| Question de sécurité | Modifier la question/réponse de récupération de PIN |

---

## Guide d'utilisation

### Flux complet d'utilisation

```
[Ouverture app]
      │
      ▼
[Saisie PIN local]
      │
      ├─► [Premier lancement] → [Création PIN + Question sécurité]
      │
      ▼
[Vérification URL serveur]
      │
      ├─► [URL manquante] → [Page Configuration URL]
      │
      ▼
[Liste des campagnes chargées]
      │
      ├─► [Aucune campagne] → [Charger une nouvelle campagne]
      │
      ▼
[Connexion au serveur] (icône utilisateur en haut à droite)
      │
      ▼
[Charger une campagne]
      │   • Sélectionner la campagne dans la liste déroulante
      │   • Cliquer sur "Charger"
      │   • Attendre la fin du téléchargement
      ▼
[Page Campagne]
      │   • Sélectionner le secteur d'enseignement
      │   • Naviguer dans les entités administratives (région → département → commune)
      │   • La liste des établissements s'affiche
      ▼
[Sélectionner un établissement]
      │
      ▼
[Page Établissement - Saisie]
      │   • Sélectionner le questionnaire (formulaire) à remplir
      │   • Saisir les données dans les champs
      │   • Utiliser ◄ ► pour naviguer entre les formulaires
      │   • Cliquer sur "Enregistrer" pour sauvegarder localement
      ▼
[Synchronisation vers le serveur]
          • Cliquer sur "Envoyer" (icône action)
          • Choisir : "Courant" (formulaire actif), "Tout" (tous les formulaires), "SMS"
```

### Actions disponibles par page

#### Page Accueil (`index.html`)
- Saisie du PIN local
- Récupération du PIN via question de sécurité
- Bouton "Options" (menu contextuel) : Paramètres, À propos, Quitter

#### Page Liste des Campagnes (`#p_lst_camps`)
- Affichage des campagnes chargées (en cours uniquement)
- Navigation vers une campagne
- Chargement d'une nouvelle campagne (via menu Options → "Charger campagne")

#### Page Chargement Campagne (`new_camp.html`)
- Connexion obligatoire au serveur
- Sélection de la campagne à charger
- Suivi du téléchargement en temps réel

#### Page Campagne (`camp.html → #p_camp`)
- Informations de la campagne
- Sélection du secteur d'enseignement
- Filtrage géographique hiérarchique (drill-down)
- Liste des établissements filtrés
- Actions : Supprimer, Recharger, Envoyer toutes les données

#### Page Établissement (`camp.html → #p_etab`)
- Informations de l'établissement
- Sélection du questionnaire actif
- Sélection du filtre (période, etc.) si applicable
- Formulaire de saisie dynamique
- Pagination entre formulaires (◄ ►)
- Sauvegarde locale ("Enregistrer")
- Envoi serveur ("Envoyer")
- Rechargement depuis le serveur ("Recharger")

#### Menu Options (disponible sur toutes les pages)
- Charger campagne
- Paramètres
- À propos
- Quitter

### Gestion du mode offline

- Toutes les données de configuration (établissements, formulaires, règles de validation) sont téléchargées lors du chargement de la campagne
- La saisie ne nécessite **aucune connexion internet**
- La connexion n'est nécessaire que pour :
  - L'authentification au serveur
  - Le chargement/rechargement d'une campagne
  - L'envoi des données collectées

### Envoi via SMS (mode de secours)

Si aucune connexion internet n'est disponible, les données peuvent être envoyées par SMS à une passerelle :
1. Configurer le numéro et la clé de la passerelle SMS dans les Paramètres
2. Dans la page Établissement → "Envoyer" → "SMS"
3. Les données sont fragmentées automatiquement en messages de 148 caractères

---

## Structure du projet

```
stateduc_mobile/
├── config.xml              # Configuration Cordova (ID app, plugins, permissions)
├── package.json            # Dépendances Node/Cordova
├── package-lock.json       # Lockfile npm
├── .gitignore
│
├── www/                    # ← CODE SOURCE PRINCIPAL
│   ├── index.html          # Page login + settings + liste campagnes
│   ├── camp.html           # Page campagne + établissement + saisie
│   ├── new_camp.html       # Page chargement nouvelle campagne
│   ├── settings.html       # Page paramètres avancés
│   │
│   ├── css/
│   │   ├── style.css       # Styles personnalisés StatEduc
│   │   ├── index.css       # Styles page accueil
│   │   └── themes/
│   │       ├── stateduc/   # Thème jQuery Mobile personnalisé
│   │       └── default/    # Thème jQuery Mobile structure
│   │
│   ├── img/                # Logos et icônes
│   │   ├── logo_stateduc.png
│   │   ├── user_login.png
│   │   ├── user_logout.png
│   │   └── btn_options.png
│   │
│   └── js/
│       ├── jquery/         # Bibliothèques jQuery
│       ├── default.js      # Modèles Year, Filter
│       ├── status.js       # Modèle Status
│       ├── systems.js      # Modèle System (secteurs)
│       ├── campagnes.js    # Modèles Campaign, Localisation
│       ├── regroups.js     # Modèle Regroup (entités admin)
│       ├── etabs.js        # Modèles Etab, CollectData
│       ├── questions.js    # Modèles Question, Rule
│       ├── users.js        # Gestion utilisateur
│       ├── charge_camp.js  # Téléchargement campagne (AJAX)
│       ├── calc_total.js   # Calculs totaux matrices
│       ├── error_msg.js    # Messages d'erreur
│       ├── script.js       # Utilitaires globaux
│       ├── start.js        # Initialisation
│       ├── page_index.js   # Logique page accueil
│       ├── page_new_camp.js# Logique page chargement campagne
│       ├── page_camp.js    # Logique page campagne
│       ├── page_etab.js    # Logique page établissement
│       └── page_settings.js# Logique paramètres
│
├── platforms/
│   └── android/            # Code Android généré par Cordova
│       ├── app/
│       │   └── src/main/
│       │       ├── AndroidManifest.xml
│       │       └── assets/www/  # Copie du dossier www
│       └── CordovaLib/     # Bibliothèque Cordova Android
│
└── plugins/                # Plugins Cordova installés
    ├── cordova-plugin-dialogs/
    ├── cordova-plugin-network-information/
    ├── cordova-plugin-vibration/
    └── hazems-cordova-plugin-sms/
```

---

## API Serveur

L'application communique avec un serveur StatEduc via les endpoints suivants.

**Format de réponse standard :**
```json
{
  "se_status": 200,
  "se_message": "ok",
  "se_data": [...]
}
```

**Authentification :** HTTP Basic Auth sur tous les endpoints (sauf login).

| Endpoint | Méthode | Description |
|---|---|---|
| `/user_ident.php/user/{login}/{mdp}` | GET | Authentification |
| `/user_ident.php/logout/xxxx/xxxx` | GET | Déconnexion |
| `/user_ident.php/user_test_login/` | GET | Vérification session |
| `/user_camp.php/new_camp/{id_user}/1` | GET | Campagnes disponibles |
| `/user_camp.php/reg_camp/{login}/{id_camp}/1` | GET | Entités administratives |
| `/user_camp.php/typ_reg_camp/{id_user}/{id_camp}/{types}` | GET | Types entités admin |
| `/user_camp.php/etabs_status/` | GET | Statuts établissements |
| `/user_camp.php/etabs_camp/{id_user}/{id_camp}/1` | GET | Établissements |
| `/user_camp.php/locs_camp/{id_user}/{id_camp}` | GET | Chaînes de localisation |
| `/user_camp.php/sys_camp/{id_user}/{id_camp}` | GET | Secteurs d'enseignement |
| `/data_camp.php/theme_camp/{id_camp}/{id_sys}/eng` | GET | Liste questionnaires |
| `/data_camp.php/html_theme_camp/{id_camp}/{id_qst}/eng` | GET | HTML formulaire |
| `/data_camp.php/regle_theme_camp/{id_qst}/{id_sys}` | GET | Règles validation |
| `/data_save.php/theme_save/{login}/{id_camp}/{id_sys}/{id_qst}/{id_etab}/{filter}` | POST | Envoi données |
| `/data_reload.php/theme_data/{login}/{id_sys}/{id_qst}/{id_camp}/{id_etab}/{filter}` | GET | Rechargement données |

---

## Dépannage

### Erreur : `ANDROID_HOME not set`

```bash
export ANDROID_HOME=$HOME/Android/Sdk
export PATH=$PATH:$ANDROID_HOME/platform-tools
```

### Erreur : `Gradle build failed`

```bash
cd platforms/android
./gradlew clean
cd ../..
cordova build android
```

### Erreur : `Could not find SDK version`

Ouvrir Android Studio → SDK Manager → Installer **Android 13 (API 33)** ou **Android 14 (API 34)**.

### Erreur de connexion au serveur dans l'app

1. Vérifier que l'URL est correcte (format : `http://ip:port` sans slash final)
2. Vérifier que le serveur StatEduc est accessible depuis l'appareil
3. Si sur émulateur : utiliser `http://10.0.2.2:PORT` pour accéder au localhost de la machine hôte
4. Si le message "401" apparaît : vérifier les identifiants utilisateur

### L'application plante au démarrage

```bash
# Consulter les logs Android
adb logcat -s CordovaLog:V CordovaActivity:V
```

### LocalStorage saturé

Si des messages d'erreur apparaissent lors de la sauvegarde :
1. Aller dans Paramètres Android → Applications → StatEduc → Vider le cache
2. Recharger la campagne

### Nettoyer et reconstruire complètement

```bash
cordova clean android
npm install
cordova platform rm android
cordova platform add android
cordova plugin add cordova-plugin-dialogs
cordova plugin add cordova-plugin-network-information
cordova plugin add cordova-plugin-vibration
cordova plugin add hazems-cordova-plugin-sms
cordova build android
```

---

## Notes importantes

> ⚠️ **Sécurité** : Cette version utilise HTTP (non chiffré) et stocke les mots de passe en sessionStorage. Pour un déploiement en production, il est fortement recommandé de configurer HTTPS sur le serveur et d'adapter `config.xml` en conséquence.

> ⚠️ **Maintenance** : Les technologies utilisées (Cordova, jQuery Mobile 1.4.5) ne sont plus activement maintenues. Une migration vers Flutter est recommandée pour assurer la pérennité de l'application (voir branche `ak_main`).

> ℹ️ **Stockage** : Le localStorage du WebView Android est limité à environ 5 Mo. Pour des campagnes avec de nombreux établissements et formulaires complexes, cette limite peut être atteinte.
