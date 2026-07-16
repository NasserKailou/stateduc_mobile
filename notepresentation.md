# Note de Présentation — StatEduc Mobile MEN
## Transfert de Compétences aux Bénéficiaires
**Dernière mise à jour : Session 18 — Juin 2026**

---

> **Usage** : Ce document est destiné à préparer une présentation de transfert de compétences pour les bénéficiaires du projet StatEduc Mobile (agents techniques du MEN, développeurs, superviseurs de collecte). La présentation doit être accessible, visuelle et orientée pratique.

---

## 1. Contexte et enjeux du projet

### Situation initiale
Le Ministère de l'Éducation Nationale du **Burundi** dispose d'une application web **StatEduc** pour collecter des données statistiques scolaires (effectifs élèves, enseignants, infrastructures). Cette application :
- Fonctionne uniquement avec un **navigateur web** et une **connexion Internet permanente**
- N'est pas utilisable sur le terrain, dans les zones à faible connectivité
- Nécessite que les agents de collecte aient accès à un ordinateur

### Problème à résoudre
Les agents travaillent dans des établissements souvent situés dans des zones **sans connexion Internet fiable**. Ils ne peuvent pas utiliser l'application web existante sur le terrain.

### Solution développée
Une **application mobile Android** (tablette) qui :
- Fonctionne **hors ligne** (sans Internet)
- Synchronise les données avec le serveur quand la connexion est disponible
- Reproduit **exactement** la logique métier de l'application web existante
- Ne nécessite **aucune modification** de la base de données ou de l'application web serveur

---

## 2. Ce que fait l'application

### Fonctionnalités principales

| Fonctionnalité | Description |
|---------------|-------------|
| 📱 **Écran d'accueil** | Drapeau du Burundi + authentification PIN sécurisée |
| 📥 **Téléchargement de campagne** | Récupération des formulaires, règles et établissements depuis le serveur |
| 📝 **Saisie hors ligne** | Formulaires identiques à l'application web, sauvegarde locale SQLite |
| ✅ **Cohérence automatique** | Vérification des données en temps réel, **sans Internet** |
| ☁️ **Envoi des données** | 3 niveaux : formulaire, établissement, campagne complète |
| 🔄 **Pré-remplissage** | Page d'identification automatiquement remplie depuis le serveur |
| ⚙️ **Paramètres** | URL serveur, PIN sécurisé, question de sécurité |

### Navigation dans l'application
```
Connexion PIN
    ↓
Liste des campagnes actives
    ↓
Sélection d'un établissement (navigation Région → Commune → École)
    ↓
Saisie des formulaires (thème par thème)
    ↓
Envoi au serveur (formulaire / école / campagne complète)
```

---

## 3. Architecture technique

### Schéma global
```
[Tablette Android]                         [Serveur MEN]
┌──────────────────────────────┐           ┌─────────────────────┐
│  Application Flutter/Dart    │           │  PHP Slim v2 API    │
│                              │  WiFi /   │                     │
│  📱 Interface utilisateur    │◄─────────►│  data_save.php      │
│  📦 SQLite (13 tables)       │  Internet │  data_controle.php  │
│  ⚙️  Moteur cohérence offline │           │  data_rules.php     │
│  🔒 PIN + chiffrement        │           │  data_reload.php    │
└──────────────────────────────┘           │                     │
                                           │  Base Oracle/MySQL  │
                                           └─────────────────────┘
```

### Technologies
| Composant | Technologie | Rôle |
|-----------|-------------|------|
| Application mobile | **Flutter (Dart)** | Interface + logique métier |
| Base de données locale | **SQLite (sqflite)** | Stockage hors ligne |
| HTTP client | **Dio** | Communication REST |
| Architecture état | **Provider/ChangeNotifier** | Gestion état réactif |
| Serveur | **PHP Slim v2** | API REST existante |
| Base serveur | **Oracle / MySQL** | Données officielles |

---

## 4. Composants clés

### 4.1 Côté mobile (Flutter/Dart)

**`api_service.dart`** — Communication serveur
> Singleton Dio. Timeouts : connectTimeout 60s, receiveTimeout 300s, sendTimeout 300s.
> Authentification HTTP Basic. Correction encodage ISO-8859-15 → UTF-8.

**`database_service.dart`** — Mémoire locale
> 13 tables SQLite. Gestion de toutes les données locales (campagnes, formulaires, données collectées, règles de cohérence).

**`coherence_evaluator.dart`** — Vérificateur hors ligne
> Équivalent mobile de `controle_theme_batch.class.php`. Évalue les règles de cohérence sur les données SQLite + mémoire.

**`data_entry_provider.dart`** — Chef d'orchestre de la saisie
> Coordonne tout le cycle de vie d'un formulaire. Déclenche la cohérence offline à 7 moments différents (session 18).

**`dynamic_form_widget.dart`** — Afficheur de formulaires
> Affiche les formulaires HTML du serveur dans un WebView, corrige le mojibake.

**`pin_screen.dart`** — Écran d'accueil / PIN
> Affiche le drapeau du Burundi. Gère 3 modes : création PIN, connexion serveur, déverrouillage PIN.

### 4.2 Côté serveur (PHP)

**`data_save.php`** — Enregistrement des données
> Reçoit le POST mobile → appel curl interne → questionnaire_ws.php → base Oracle/MySQL.
> Point critique : `session_write_close()` avant le curl pour éviter le deadlock Apache.

**`data_controle.php`** — Contrôle post-envoi
> Exécute les règles SQL sur les données fraîchement enregistrées. Retourne les violations.

**`data_rules.php`** — Règles de cohérence offline
> Fournit les règles interpolées au mobile pour évaluation hors ligne.

**`data_reload.php`** — Rechargement des données serveur
> Retourne les données déjà enregistrées pour pré-remplissage (identification).

---

## 5. Points techniques critiques

### 🔴 Anti-deadlock Apache (session 12b)
`session_write_close()` libère le verrou de session PHP avant l'appel curl interne vers questionnaire_ws.php. Sans cela, timeout de 3+ minutes sur tous les envois.

### 🕐 Timeouts Dio (configuration actuelle)
| Paramètre | Valeur | Corrigé en |
|-----------|--------|-----------|
| `connectTimeout` | 60 s | Sessions initiales |
| `receiveTimeout` | 300 s | Session 12b |
| `sendTimeout` | **300 s** | Session 17 *(était 120 s)* |

### 📅 yearCode — Contournement session PHP
Le code de l'année scolaire est passé **dans l'URL** car l'application mobile n'a pas de session PHP persistante :
```
/data_save.php/theme_save/login/camp/sys/qst/etab/filter/2024
```

### 🔡 Mojibake — Correction encodage
Le serveur utilise ISO-8859-15. L'application détecte et corrige automatiquement les caractères corrompus (seuil : 5% de caractères U+FFFD invalides).

### 🔍 ID thème composite
Le thème `15702` = thème `1570` + secteur `2`. PHP et Flutter décomposent cet identifiant identiquement pour les requêtes SQL.

---

## 6. Contrôles de cohérence — Fonctionnement détaillé

### Qu'est-ce qu'un contrôle de cohérence ?
Vérification automatique que les données saisies sont **logiquement cohérentes entre elles**.

### Exemples de règles
| Règle | Signification |
|-------|--------------|
| `Nb filles ≤ Nb total élèves` | Impossible d'avoir plus de filles que d'élèves total |
| `Nb enseignants > 0 si élèves > 0` | Toute école avec élèves doit avoir au moins 1 enseignant |
| `Total lignes = Total colonnes` | Cohérence des totaux dans les grilles de saisie |

### Deux niveaux de contrôle

**Niveau 1 — Hors ligne (offline)** : Sur la tablette, SANS Internet
- Déclenché automatiquement à **7 moments** distincts (session 18)
- Affiche une bannière d'avertissement + indicateur de progression

**Niveau 2 — Serveur** : Après envoi des données au serveur
- Plus précis (données réelles en base Oracle/MySQL)
- Dialogue avec le détail de chaque violation

### Les 7 déclenchements de la cohérence offline (session 18)

| Événement | Délai | Depuis |
|-----------|-------|--------|
| Frappe dans un champ | 800 ms (debounce) | **Session 18** |
| Bouton "Sauvegarder" | Immédiat | Sessions 1-16 |
| Ouverture formulaire déjà saisi | Immédiat | Session 17 |
| Changement de période/filtre | Immédiat | **Session 18** |
| Règles reçues du serveur | Arrière-plan | Session 17 |
| Données serveur fusionnées | Arrière-plan | **Session 18** |
| Envoi serveur réussi | Via API serveur | Sessions 1-16 |

---

## 7. Flux de travail de l'agent de collecte

```
PHASE 1 — Initialisation (avec connexion WiFi)
──────────────────────────────────────────────
1. Connecter la tablette au WiFi
2. Ouvrir StatEduc Mobile
3. Saisir URL serveur + identifiant + mot de passe
4. Créer un code PIN de sécurité (4-8 chiffres)
5. Sélectionner la campagne → télécharger (formulaires + établissements + règles)

PHASE 2 — Collecte sur le terrain (sans connexion)
───────────────────────────────────────────────────
1. Ouvrir l'application → saisir le PIN
2. Sélectionner la campagne → naviguer jusqu'à l'école
3. La page d'identification est automatiquement pré-remplie (nom, code, statut)
4. Saisir les données formulaire par formulaire
5. Les contrôles de cohérence s'affichent en temps réel pendant la saisie
6. Appuyer "Sauvegarder" après chaque formulaire

PHASE 3 — Synchronisation (avec connexion WiFi)
────────────────────────────────────────────────
Option A — Envoi par formulaire :
  Ouvrir un formulaire → "Envoyer" → consulter les contrôles serveur

Option B — Envoi par établissement (recommandé) :
  Ouvrir une école → menu ⋮ → "Envoyer tous les formulaires" → confirmer

Option C — Envoi global campagne (le plus efficace) :
  Depuis la liste des établissements → "Envoyer tous les établissements"
  → Barre de progression → Résumé : N succès / N ignorés
```

---

## 8. Installation et déploiement

### Prérequis tablette
- Android 5.0 minimum (recommandé : Android 8+)
- 50 MB d'espace disque libre
- Connexion WiFi pour le premier téléchargement des campagnes

### Installation application
1. Activer "Sources inconnues" (Paramètres → Sécurité)
2. Copier `stateduc-mobile.apk` sur la tablette
3. Ouvrir le fichier APK et confirmer l'installation
4. Lancer l'application → saisir URL serveur + identifiants

### Déploiement serveur
- Copier les fichiers `StatEduc_MEN_2025/*.php` sur le serveur Apache existant
- **Aucune modification** de la base de données Oracle/MySQL requise
- **Aucune modification** de l'application web existante requise

---

## 9. Maintenance et évolution

### Qui peut maintenir ?
- **Développeur Flutter/Dart** → modifications application mobile
- **Développeur PHP** → modifications côté serveur
- Les deux composants sont **indépendants**

### Obtenir le code source
```bash
git clone https://github.com/NasserKailou/stateduc_mobile.git
git checkout ak_main
```

### Documentation disponible
| Document | Contenu | Audience |
|---------|---------|----------|
| `administration.md` | Guide A→Z : installation, PIN, campagne, saisie, envoi, dépannage | Administrateurs, superviseurs |
| `recapitulatif.md` | Architecture, correctifs, guide développeur (sessions 1-20) | Développeurs, mainteneurs |
| `stateduc_flutter/CHANGELOG.md` | Historique détaillé Flutter (sessions 1-20) | Développeurs |
| `StatEduc_MEN_2025/CHANGELOG.md` | Historique des modifications PHP | Développeurs PHP |
| Code source | Tous les fichiers commentés en français | Développeurs |
| PR #1 | https://github.com/NasserKailou/stateduc_mobile/pull/1 | Équipe projet |

---

## 10. Résultats obtenus — Sessions 1-18

| Objectif | Résultat | Session |
|---------|---------|---------|
| Application fonctionne hors ligne | ✅ SQLite, formulaires mis en cache | 1-5 |
| Données envoyées au serveur existant | ✅ API PHP compatible sans modification | 3-14 |
| Anti-deadlock Apache | ✅ `session_write_close()` avant curl interne | 12b |
| Données toujours écrites en DB | ✅ `codeyear` persisté + URL 8 segments | 12 |
| Contrôles de cohérence serveur | ✅ Post-envoi via `data_controle.php` | 11 |
| Correction mojibake encodage | ✅ Détection et correction automatiques | 14 |
| Cohérence offline — règles téléchargées | ✅ `data_rules.php` + `yearCode` passé | 14 |
| Timeout robuste | ✅ `sendTimeout` 300s, `receiveTimeout` 300s | 17 |
| Page d'identification pré-remplie | ✅ `forceOverwrite` pour tous types de campagne | 17 |
| Envoi global établissement | ✅ Tous les formulaires d'une école en 1 clic | 17 |
| Envoi global campagne | ✅ Tous les établissements en 1 opération | 17 |
| Paramètres lisibles | ✅ TabBar Serveur/PIN/Sécurité contrasté | 17 |
| Identité visuelle Burundi | ✅ Drapeau du Burundi à l'écran d'accueil | **18** |
| Cohérence offline en temps réel | ✅ 7 déclencheurs dont debounce saisie | **18** |
| Code documenté en français | ✅ Tous les fichiers source commentés | 15-16 |

---

*Document de transfert de compétences — Projet StatEduc Mobile MEN — Sessions 1-18 — Juin 2026*
