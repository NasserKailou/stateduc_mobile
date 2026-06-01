# Note de Présentation — StatEduc Mobile MEN
## Transfert de Compétences aux Bénéficiaires

---

## À l'attention de l'IA de présentation

Ce document est destiné à être utilisé pour préparer une présentation de transfert de compétences pour les bénéficiaires du projet StatEduc Mobile (agents techniques du MEN, développeurs, superviseurs de collecte). La présentation doit être accessible, visuelle et orientée pratique.

---

## 1. Contexte et enjeux du projet

### Situation initiale
Le Ministère de l'Éducation Nationale (MEN) dispose d'une application web **StatEduc** utilisée pour collecter des données statistiques scolaires (effectifs élèves, enseignants, infrastructures). Cette application :
- Fonctionne uniquement avec un **navigateur web** et une **connexion Internet**
- N'est pas utilisable sur le terrain, dans les zones à faible connectivité
- Nécessite que les agents de collecte aient accès à un ordinateur

### Problème à résoudre
Les agents de collecte travaillent dans des établissements scolaires souvent situés dans des zones **sans connexion Internet fiable**. Ils ne peuvent pas utiliser l'application web existante sur le terrain.

### Solution développée
Une **application mobile Android** (tablette) qui :
- Fonctionne **hors ligne** (sans Internet)
- Synchronise les données avec le serveur quand la connexion est disponible
- Garde la **même logique métier** que l'application web existante
- Ne nécessite **aucune modification** de la base de données serveur

---

## 2. Ce que fait l'application

### Fonctionnalités principales

**📥 Téléchargement de campagne**
- L'agent se connecte une fois avec WiFi
- Télécharge toute la campagne de collecte (formulaires, règles, établissements)
- Tout est stocké localement sur la tablette

**📝 Saisie des données**
- Navigation géographique : Région → Département → Commune → École
- Saisie des données dans des formulaires identiques à l'application web
- Sauvegarde locale automatique à chaque modification

**✅ Contrôles de cohérence**
- L'application vérifie automatiquement la cohérence des données saisies
- *Exemple : "Le nombre d'élèves filles ne peut pas être supérieur au nombre total d'élèves"*
- Deux niveaux de contrôle : immédiat (hors ligne) et après envoi (serveur)

**☁️ Envoi des données**
- Quand la connexion est disponible, l'agent envoie les données au serveur
- Confirmation visuelle d'envoi réussi
- Les données envoyées sont conservées localement pour référence

---

## 3. Architecture technique (version simplifiée)

### Schéma global

```
[Tablette Android]                    [Serveur MEN]
┌─────────────────┐                  ┌──────────────┐
│   Application   │ ← WiFi/Internet → │  Base de     │
│   Flutter       │                  │  données     │
│                 │                  │  Oracle/MySQL│
│  📱 Interface   │                  └──────────────┘
│  📦 SQLite DB   │
│  ⚙️ Moteur      │
│     cohérence  │
└─────────────────┘
```

### Technologies utilisées
| Composant | Technologie | Pourquoi |
|-----------|-------------|----------|
| Application mobile | **Flutter (Dart)** | Multi-plateforme, performant, communauté active |
| Base de données locale | **SQLite** | Légère, embarquée, fiable hors ligne |
| Communication serveur | **HTTP REST (Dio)** | Compatibilité avec l'API existante StatEduc |
| Serveur existant | **PHP Slim** | Pas de modification de l'infrastructure existante |

---

## 4. Composants clés à connaître

### 4.1 Côté mobile (Flutter)

**`api_service.dart`** — Le "téléphone" vers le serveur
> Gère toutes les communications HTTP. Singleton partagé par toute l'application.
> Configure les timeouts, l'authentification HTTP Basic, et la gestion SSL.

**`database_service.dart`** — La "mémoire" locale
> Stocke tout ce qui est téléchargé et saisi. 13 tables SQLite.
> Les données saisies restent disponibles même sans Internet.

**`coherence_evaluator.dart`** — Le "vérificateur" hors ligne
> Vérifie la cohérence des données AVANT l'envoi, sans Internet.
> Extrait les valeurs depuis les règles SQL téléchargées.

**`data_entry_provider.dart`** — Le "chef d'orchestre" de la saisie
> Coordonne : chargement formulaire → saisie → sauvegarde locale → envoi → contrôle.

**`dynamic_form_widget.dart`** — L'"afficheur" de formulaires
> Affiche les formulaires HTML du serveur dans un WebView.
> Corrige automatiquement les problèmes d'encodage (caractères accentués).

### 4.2 Côté serveur (PHP)

**`data_save.php`** — Réception et enregistrement
> Reçoit les données POST du mobile → les transmet à la base Oracle/MySQL.
> **Point critique** : libère le verrou de session avant d'appeler le service interne.

**`data_controle.php`** — Contrôle post-envoi
> Après sauvegarde, vérifie la cohérence des données en base réelle.
> Retourne les violations en JSON pour affichage sur mobile.

**`data_rules.php`** — Fourniture des règles offline
> Fournit les règles de cohérence au mobile pour évaluation hors ligne.
> Interpolation SQL : les requêtes contiennent des variables PHP dynamiques.

**`controle_theme_batch.class.php`** — Moteur de contrôle
> Classe PHP qui exécute les contrôles de cohérence sur la base de données.
> Peut fonctionner en mode "alerte HTML" (navigateur) ou "batch API" (mobile).

---

## 5. Points techniques critiques à retenir

### 🔴 Anti-deadlock Apache
**Problème** : Quand data_save.php faisait un appel curl interne vers questionnaire_ws.php, les deux processus se bloquaient mutuellement sur le verrou de session PHP.

**Solution** : `session_write_close()` libère le verrou avant l'appel curl.

```php
// ANTI-DEADLOCK : libération du verrou de session avant curl interne
session_write_close();
$curl->post($urlBase, $data_to_send);
```

### 🕐 Timeout 120 secondes
Le traitement de questionnaire_ws.php peut prendre **plus de 60 secondes** sur un serveur chargé. Le timeout CURL a été augmenté à 120s pour éviter les fausses erreurs de timeout.

### 📅 yearCode — Contournement session PHP
L'application mobile ne crée pas de session PHP (pas de navigateur). Le code de l'année scolaire est donc passé **directement dans l'URL** :
```
/data_save.php/theme_save/.../2024
```
Sans ce paramètre, les fonctions PHP utilisant `$_SESSION['annee']` échouent silencieusement.

### 🔡 Mojibake — Correction encodage
Le serveur utilise ISO-8859-15 (encodage hérité). L'application mobile corrige automatiquement les caractères corrompus avec un **seuil de tolérance de 5%** de caractères invalides.

---

## 6. Flux de travail de l'agent de collecte

```
JOUR 1 (avec connexion)           JOURS 2-N (sans connexion)
─────────────────────             ────────────────────────────
1. Se connecter au WiFi            1. Ouvrir l'application
2. Ouvrir l'application            2. Sélectionner la campagne
3. Se connecter au serveur         3. Naviguer : Région → École
4. Télécharger la campagne         4. Saisir les données
5. Attendre le téléchargement      5. Contrôle cohérence automatique
   (formulaires, règles, écoles)   6. Sauvegarder localement

QUAND CONNEXION DISPONIBLE
──────────────────────────
1. Ouvrir l'application
2. Sélectionner l'école
3. Appuyer "Envoyer"
4. Vérifier confirmation
5. Consulter les contrôles de cohérence
```

---

## 7. Contrôles de cohérence — Explications pour les bénéficiaires

### Qu'est-ce qu'un contrôle de cohérence ?
C'est une **vérification automatique** que les données saisies sont logiquement correctes entre elles.

### Exemples de contrôles
| Contrôle | Signification |
|---------|---------------|
| `Nb filles <= Nb total élèves` | Impossible d'avoir plus de filles que d'élèves au total |
| `Nb enseignants > 0 si élèves > 0` | Une école avec des élèves doit avoir au moins un enseignant |
| `Salles de classe >= 1` | Toute école doit avoir au minimum une salle |

### Deux niveaux de contrôle
1. **Contrôle immédiat (offline)** : Pendant la saisie, sur la tablette, SANS Internet
   - Rapide, basé sur les données déjà saisies
   - Affiche une bannière d'avertissement dans l'application

2. **Contrôle serveur (après envoi)** : Après envoi des données au serveur
   - Plus précis, basé sur toutes les données en base
   - Affiche une boîte de dialogue avec le détail des violations

---

## 8. Installation et déploiement

### Prérequis
- Tablette Android 5.0 ou supérieur (recommandé : Android 8+)
- 50 MB d'espace disque libre minimum
- Connexion WiFi pour le premier téléchargement des campagnes

### Installation
1. Activer l'installation depuis sources inconnues (Paramètres → Sécurité)
2. Copier le fichier `stateduc-mobile.apk` sur la tablette
3. Ouvrir le fichier APK et confirmer l'installation
4. Saisir l'URL du serveur StatEduc, login et mot de passe

### Configuration serveur
- Déployer les fichiers PHP `StatEduc_MEN_2025/` sur le serveur Apache existant
- Aucune modification de la base de données requise
- Aucune modification de l'application web existante requise

---

## 9. Maintenance et évolution

### Qui peut maintenir ce projet ?
- **Développeur Flutter/Dart** : Pour les modifications de l'application mobile
- **Développeur PHP** : Pour les modifications côté serveur
- Les deux composants sont **indépendants** et peuvent être maintenus séparément

### Comment obtenir le code source ?
```bash
git clone https://github.com/NasserKailou/stateduc_mobile.git
git checkout ak_main
```

### Documentation disponible
- **`recapitulatif.md`** : Documentation technique complète (architecture, correctifs, guide développeur)
- **Commentaires dans le code** : Tous les fichiers source sont commentés en français
- **Pull Request #1** : Historique détaillé de toutes les modifications

---

## 10. Résultats obtenus

| Objectif | Résultat |
|---------|---------|
| Application fonctionne hors ligne | ✅ SQLite local, formulaires mis en cache |
| Données envoyées au serveur existant | ✅ Compatible API PHP StatEduc sans modification |
| Contrôles de cohérence | ✅ Offline (immédiat) + Serveur (post-envoi) |
| Interface utilisateur | ✅ Navigation géographique hiérarchique |
| Gestion des erreurs d'encodage | ✅ Correction mojibake automatique |
| Timeout robuste | ✅ 120s pour questionnaire_ws.php lent |
| Code documenté en français | ✅ Tous les fichiers commentés |

---

*Document préparé pour transfert de compétences — Projet StatEduc Mobile MEN — 2026*
