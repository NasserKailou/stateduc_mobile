# Guide d'administration — StatEduc Mobile
## Application mobile de collecte de données éducatives — MEN Burundi
**Version : Session 20 — Juin 2026**
**Public cible : Administrateurs, superviseurs de collecte, agents techniques MEN**

---

> **Usage de ce document**
> Support de présentation pour la formation des administrateurs de StatEduc Mobile.
> Couvre l'ensemble du cycle de vie applicatif : de l'installation initiale jusqu'au
> rechargement des données depuis le serveur, en passant par la gestion des PIN,
> la configuration réseau, la collecte hors ligne et les contrôles de cohérence.

---

## Table des matières

1. [Installation de l'application](#1-installation-de-lapplication)
2. [Premier démarrage — Connexion au serveur](#2-premier-démarrage--connexion-au-serveur)
3. [Création et gestion du code PIN](#3-création-et-gestion-du-code-pin)
4. [Configuration de l'URL serveur](#4-configuration-de-lurl-serveur)
5. [Téléchargement d'une campagne](#5-téléchargement-dune-campagne)
6. [Navigation vers un établissement](#6-navigation-vers-un-établissement)
7. [Remplissage d'un questionnaire](#7-remplissage-dun-questionnaire)
8. [Sauvegarde locale des données](#8-sauvegarde-locale-des-données)
9. [Contrôle de cohérence hors ligne](#9-contrôle-de-cohérence-hors-ligne)
10. [Envoi des données au serveur](#10-envoi-des-données-au-serveur)
11. [Contrôle de cohérence serveur](#11-contrôle-de-cohérence-serveur)
12. [Rechargement des données depuis le serveur](#12-rechargement-des-données-depuis-le-serveur)
13. [Envoi global — Établissement ou Campagne entière](#13-envoi-global--établissement-ou-campagne-entière)
14. [Gestion des erreurs réseau](#14-gestion-des-erreurs-réseau)
15. [Modification du code PIN](#15-modification-du-code-pin)
16. [Réinitialisation du PIN via question de sécurité](#16-réinitialisation-du-pin-via-question-de-sécurité)
17. [Déconnexion et changement d'utilisateur](#17-déconnexion-et-changement-dutilisateur)
18. [Paramètres avancés](#18-paramètres-avancés)
19. [Résolution des problèmes courants](#19-résolution-des-problèmes-courants)
20. [Tableau de bord administrateur — Vérifications essentielles](#20-tableau-de-bord-administrateur--vérifications-essentielles)

---

## 1. Installation de l'application

### Prérequis de la tablette
| Critère | Minimum | Recommandé |
|---------|---------|-----------|
| Système Android | Android 5.0 (Lollipop) | Android 8.0+ |
| Espace disque libre | 50 Mo | 200 Mo |
| RAM | 1 Go | 2 Go |
| Connexion réseau | WiFi ou 4G | WiFi (pour le téléchargement initial) |

### Procédure d'installation
1. **Autoriser les sources inconnues** sur la tablette :
   - Paramètres → Sécurité → Autoriser les sources inconnues → Activer
   - *(Nécessaire uniquement pour l'installation hors Google Play Store)*

2. **Transférer le fichier APK** sur la tablette :
   - Via câble USB : copier `stateduc-mobile.apk` dans le dossier Téléchargements
   - Via partage WiFi / clé USB OTG

3. **Installer l'application** :
   - Ouvrir le gestionnaire de fichiers
   - Naviguer vers `stateduc-mobile.apk`
   - Appuyer → Installer → Confirmer

4. **Vérifier l'installation** :
   - L'icône StatEduc Mobile doit apparaître dans le lanceur d'applications
   - L'icône affiche le drapeau du Burundi

### Mise à jour de l'application
- Installer le nouvel APK par-dessus l'installation existante
- **Les données locales (campagnes téléchargées, formulaires saisis) sont préservées**
- Le PIN et les paramètres serveur sont également conservés

---

## 2. Premier démarrage — Connexion au serveur

Au **tout premier lancement**, l'application détecte l'absence d'utilisateur configuré et affiche le formulaire de connexion serveur.

### Écran de connexion initiale

```
┌─────────────────────────────────────┐
│  République du Burundi              │
│  Ministère de l'Éducation Nationale │
│                                     │
│         [Drapeau du Burundi]        │
│                                     │
│             StatEduc                │
│   Collecte de données éducatives    │
│                                     │
│  URL du serveur :  [____________]   │
│  Identifiant :     [____________]   │
│  Mot de passe :    [____________]   │
│                                     │
│            [ Se connecter ]         │
└─────────────────────────────────────┘
```

### Saisir les informations de connexion
| Champ | Exemple | Notes |
|-------|---------|-------|
| URL du serveur | `192.168.1.10/stateduc` ou `https://men.bi/stateduc` | Intranet ou Internet |
| Identifiant | `agent_collecte_01` | Login StatEduc web existant |
| Mot de passe | `••••••••` | Même mot de passe que l'application web |

> **Important :** L'application ajoute automatiquement `http://` si aucun protocole n'est précisé, et s'assure que l'URL se termine par `/`.

### Ce qui se passe après connexion
1. L'application vérifie les credentials auprès du serveur (`/user_ident.php/user/...`)
2. Si succès : les informations utilisateur sont mémorisées localement
3. L'écran de création du **code PIN** s'affiche immédiatement
4. **La connexion au serveur n'est requise qu'une seule fois** (ou lors d'un changement de serveur/utilisateur)

---

## 3. Création et gestion du code PIN

Le code PIN remplace le mot de passe pour les déverrouillages quotidiens. Il est stocké localement sur la tablette.

### Règles du code PIN
- **Longueur :** 4 à 8 chiffres
- **Chiffres uniquement** (0-9)
- **Confirmation obligatoire** : le PIN est saisi deux fois pour éviter les erreurs

### Procédure de création
```
Écran "Créer votre PIN" :
  1. Saisir le PIN choisi (4-8 chiffres)
  2. Re-saisir le PIN pour confirmation
  3. (Optionnel) Définir une question de sécurité + réponse
     → Permet la réinitialisation en cas d'oubli
  4. Appuyer sur "Créer le PIN"
```

### Question de sécurité (recommandée)
La question de sécurité est le **seul moyen de réinitialiser le PIN sans connexion au serveur**.

Exemples de bonnes questions :
- "Nom de votre école primaire ?"
- "Nom de jeune fille de votre mère ?"
- "Ville de naissance ?"

> ⚠️ Sans question de sécurité, un oubli de PIN nécessite une **réinstallation complète** de l'application (perte des données non envoyées).

---

## 4. Configuration de l'URL serveur

### Accès aux paramètres serveur
```
Menu principal → Paramètres (⚙️) → Onglet "Serveur"
```

### Format de l'URL
```
Exemples valides :
  192.168.1.10/stateduc_app     ← réseau local (HTTP automatiquement ajouté)
  http://10.0.0.5/men           ← HTTP explicite
  https://stateduc.men.bi       ← HTTPS avec certificat
  https://192.168.1.10/stateduc ← HTTPS auto-signé (accepté automatiquement)

Exemples invalides :
  192.168.1.10                  ← pas de chemin d'application
  stateduc.men.bi/api/          ← chemin trop précis (l'application ajoute ses propres routes)
```

> **Note technique :** L'application accepte les certificats SSL auto-signés (courant sur les intranets MEN). Aucune configuration supplémentaire n'est nécessaire.

### Modifier l'URL serveur
1. Paramètres → Onglet "Serveur"
2. Effacer l'URL actuelle
3. Saisir la nouvelle URL
4. Appuyer "Enregistrer"
5. Tester la connexion avec le bouton "Tester"

> ⚠️ Modifier l'URL serveur **ne supprime pas** les données locales déjà saisies. Les données peuvent être envoyées au nouveau serveur si les identifiants sont compatibles.

---

## 5. Téléchargement d'une campagne

Le téléchargement d'une campagne est l'opération initiale qui récupère depuis le serveur :
- La liste des établissements scolaires
- Les formulaires (thèmes) à remplir
- Les règles de cohérence pour les contrôles hors ligne
- Les listes de référence (hiérarchie administrative, statuts, etc.)

### Prérequis
- ✅ Connexion WiFi active (recommandée) ou 4G
- ✅ URL serveur correctement configurée
- ✅ PIN saisi pour déverrouiller l'application

### Procédure de téléchargement
```
1. Écran principal → Liste des campagnes disponibles (chargement auto)
2. Appuyer sur la campagne souhaitée
3. Écran détail campagne → bouton "Télécharger"
   ┌──────────────────────────────────────────┐
   │  Téléchargement en cours…                │
   │  ████████░░░░░░░ 52%                     │
   │  Établissements : 143 / 276              │
   │                                          │
   │  Ne pas fermer l'application             │
   └──────────────────────────────────────────┘
4. Attendre la fin du téléchargement (2-15 min selon le volume)
5. Confirmation : "Campagne téléchargée : N établissements, N formulaires"
```

### Volume de données typique
| Contenu | Taille approximative |
|---------|---------------------|
| 300 établissements | ~2 Mo |
| 5 formulaires par établissement | ~5 Mo |
| Règles de cohérence | ~500 Ko |
| **Total** | **~8 Mo** |

### Après le téléchargement
- La campagne est disponible **hors ligne**
- Aucune nouvelle connexion réseau n'est nécessaire pour saisir les données
- Les règles de cohérence sont téléchargées en arrière-plan au fur et à mesure de la navigation

---

## 6. Navigation vers un établissement

### Structure de navigation
```
Campagnes
  └─ [Nom de la campagne] (ex: Recensement scolaire 2024-2025)
       └─ Liste des établissements
            ├─ Filtre par Région / Province
            ├─ Filtre par Commune / Secteur
            └─ [Nom de l'établissement] (ex: École Primaire Mutanga)
                 └─ Formulaires de saisie
```

### Rechercher un établissement
1. Depuis l'écran campagne, utiliser la **barre de recherche** (nom ou code)
2. Ou utiliser les filtres hiérarchiques : Région → Commune → École
3. Appuyer sur l'établissement → ouverture de l'écran de saisie

### En-tête d'identification de l'établissement
À l'ouverture d'un établissement, un bandeau affiche les informations de contexte :
```
┌─────────────────────────────────────────────┐
│ 2024-2025 | BUJUMBURA MAIRIE / NTAHANGWA    │
│ Code : BI-0042 · ID : 1576 · Public         │
│ Education de Base                           │
└─────────────────────────────────────────────┘
```

---

## 7. Remplissage d'un questionnaire

### Structure de l'écran de saisie
```
AppBar :
  [Nom établissement]
  [Thème sélectionné]
  Actions : [💾 Sauvegarder] [☁️ Envoyer] [⋮ Options]

Corps :
  Bandeau d'identification établissement
  Bannière erreurs/succès (si présente)
  Indicateur cohérence offline (si vérification en cours)
  Bannière violations cohérence (si problèmes détectés)
  Sélecteur de thème   ← chips horizontales
  Sélecteur de période ← si formulaire filtré par trimestre/mois
  Formulaire HTML      ← rempli avec WebView
```

### Sélectionner un formulaire (thème)
- Les thèmes disponibles sont affichés comme **chips horizontales** en haut du formulaire
- Appuyer sur un chip pour charger le formulaire correspondant
- Exemple de thèmes : `Identification` · `Effectifs élèves` · `Personnel` · `Infrastructures`

### Saisir les données
1. Le formulaire se charge automatiquement (HTML depuis le cache local)
2. Si des données ont déjà été saisies pour cet établissement, elles sont **pré-remplies**
3. Saisir les valeurs dans les champs correspondants
4. Les contrôles de cohérence s'exécutent **automatiquement** 800 ms après chaque saisie
5. Une **bannière orange** s'affiche si une règle est violée (ex: "Nb filles > Nb total élèves")

### Filtres par période
Certains formulaires (ex: absences, résultats par trimestre) proposent un **sélecteur de période** :
- Trimestre 1, Trimestre 2, Trimestre 3
- Ou : Janvier, Février, … Décembre
- Chaque période a ses propres données sauvegardées indépendamment

---

## 8. Sauvegarde locale des données

La sauvegarde locale persiste les données dans la base SQLite de la tablette. Elle est **indépendante du réseau**.

### Quand sauvegarder ?
- ✅ Après avoir rempli un formulaire (ou une partie)
- ✅ Avant de passer à un autre thème
- ✅ Avant de quitter l'application
- ℹ️ L'icône disquette (💾) indique qu'il y a des modifications non sauvegardées

### Procédure
```
Appuyer l'icône 💾 (disquette) dans la barre d'actions

→ Icône change en ✅ : données sauvegardées
→ Message "Données sauvegardées localement" affiché brièvement
→ Contrôle de cohérence hors ligne lancé automatiquement
```

### Ce qui est sauvegardé
- Toutes les valeurs saisies dans le formulaire courant
- La période de filtre active (si applicable)
- Le statut "non encore envoyé" (will be marked `is_sent=0`)

> **Bonne pratique :** Sauvegarder après chaque thème complété, même si l'envoi au serveur est prévu plus tard. En cas de crash ou de décharge de batterie, les données sauvegardées sont récupérées.

---

## 9. Contrôle de cohérence hors ligne

Le contrôle de cohérence vérifie que les données saisies sont **logiquement cohérentes entre elles**, sans connexion Internet.

### Fonctionnement
- Les règles de cohérence sont téléchargées depuis le serveur lors du téléchargement de la campagne
- Elles sont stockées localement dans SQLite (`coherence_rules`)
- Le moteur d'évaluation (`CoherenceEvaluator`) applique ces règles sur les données en mémoire

### Exemples de règles contrôlées
| Règle | Exemple de violation |
|-------|---------------------|
| Nb filles ≤ Nb total élèves | 150 filles pour 120 élèves total |
| Nb enseignants qualifiés ≤ Nb enseignants total | 12 qualifiés pour 10 total |
| Total ligne = somme des colonnes | Ligne "Total" ≠ somme des valeurs |

### Les 7 déclenchements automatiques
| Moment | Délai | Description |
|--------|-------|-------------|
| Frappe dans un champ | 800 ms | Après 800 ms d'inactivité clavier |
| Bouton Sauvegarder | Immédiat | Après chaque sauvegarde locale |
| Ouverture d'un formulaire rempli | Immédiat | Dès qu'on revient sur un thème déjà saisi |
| Changement de période/filtre | Immédiat | Quand on change de trimestre ou de mois |
| Règles reçues du serveur | Arrière-plan | Dès que les règles finissent de se télécharger |
| Données serveur fusionnées | Arrière-plan | Après rechargement depuis le serveur |
| Envoi serveur réussi | Via API | Contrôle serveur (voir section 11) |

### Lecture des résultats
```
┌──────────────────────────────────────────────────────┐
│ ⚠️  2 violation(s) de cohérence détectée(s)          │
│                                                      │
│ • Effectifs filles (210) > Effectifs total (185)    │
│   → Vérifier le champ "Total élèves" (thème 02)     │
│                                                      │
│ • Enseignants qualifiés (8) > Enseignants total (5) │
│   → Vérifier le champ "Total enseignants" (thème 04)│
│                                          [✕ Fermer] │
└──────────────────────────────────────────────────────┘
```

> **Important :** Les violations hors ligne sont des **avertissements** — elles n'empêchent pas l'envoi. L'agent peut choisir de corriger ou d'envoyer malgré la violation.

---

## 10. Envoi des données au serveur

L'envoi transmet les données du formulaire courant au serveur StatEduc via HTTP POST.

### Prérequis pour l'envoi
- ✅ Connexion réseau active (WiFi ou données mobiles)
- ✅ Serveur StatEduc accessible (XAMPP démarré, IP accessible)
- ✅ Données sauvegardées localement au préalable (l'envoi sauvegarde aussi automatiquement)

### Procédure d'envoi (formulaire courant)
```
1. Se positionner sur le formulaire à envoyer
2. Appuyer sur l'icône ☁️ (cloud upload) dans la barre d'actions
3. Overlay de chargement : "Envoi en cours…"
   → En cas de réseau lent : "Envoi… (tentative 2/3)" puis "Envoi… (tentative 3/3)"
4. Résultat :
   ✅ Succès : "Données envoyées avec succès"
      → Contrôle de cohérence serveur lancé automatiquement
   ❌ Échec : message d'erreur détaillé (voir section 14)
```

### Comportement en cas de réseau lent
L'application effectue jusqu'à **3 tentatives automatiques** :
- Tentative 1 : immédiate
- Tentative 2 : après 5 secondes (affiche "Envoi… (tentative 2/3)")
- Tentative 3 : après 10 secondes (affiche "Envoi… (tentative 3/3)")
- Si les 3 tentatives échouent : message d'erreur clair avec conseil

### Que se passe-t-il côté serveur ?
```
Application mobile
    → POST /data_save.php/theme_save/{login}/{campId}/{sysId}/{qstId}/{etabId}/{filter}/0/{yearCode}
    → Serveur PHP :
        1. Vérification droits d'accès
        2. Appel interne vers questionnaire_ws.php (écriture base Oracle/MySQL)
        3. Réponse : { se_status: 200, se_data: "OKSAVE" }
    → Application :
        - Marque les données comme "envoyées" dans SQLite
        - Lance le contrôle de cohérence serveur
```

---

## 11. Contrôle de cohérence serveur

Après chaque envoi réussi, le contrôle de cohérence **serveur** est lancé automatiquement. Il est plus précis que le contrôle hors ligne car il s'appuie sur les données réelles de la base Oracle/MySQL.

### Différence avec le contrôle hors ligne
| Critère | Hors ligne | Serveur |
|---------|-----------|---------|
| Connexion requise | Non | Oui (après envoi) |
| Données évaluées | SQLite local | Oracle/MySQL |
| Déclenchement | Automatique (7 moments) | Automatique après envoi |
| Précision | Approximative (regex SQL) | Exacte (SQL complet) |
| Résultat | Bannière inline | Dialogue modal |

### Lecture du dialogue de résultat
```
┌──────────────────────────────────────────────────────┐
│  Résultats du contrôle de cohérence                  │
│  ──────────────────────────────────────────────────  │
│  2 violation(s) détectée(s) par le serveur :         │
│                                                      │
│  Règle : "Nb filles ≤ Nb total"                     │
│  → Valeur R1 = 210, Valeur R2 = 185                 │
│    Condition attendue : R1 ≤ R2 → NON respectée     │
│                                                      │
│  Règle : "Total > 0 si école ouverte"               │
│  → Valeur R1 = 0, Valeur R2 = 1 (école ouverte)    │
│    Condition attendue : R1 > 0 → NON respectée      │
│                                          [  OK  ]   │
└──────────────────────────────────────────────────────┘
```

### Conduite à tenir
- **Violations mineures** : noter et corriger lors de la prochaine visite
- **Violations bloquantes** (décision administrative) : corriger sur place, renvoyer
- **Zéro violation** : aucun dialogue ne s'affiche — envoi validé silencieusement

---

## 12. Rechargement des données depuis le serveur

Cette fonctionnalité permet de **récupérer les données déjà enregistrées sur le serveur** pour les afficher dans le formulaire, en remplacement des données locales.

### Quand l'utiliser ?
- Lors d'une **reprise de collecte** : un autre agent a saisi des données sur l'application web
- Après un **envoi réussi** sur une autre tablette
- Pour **vérifier** ce qui a été enregistré en base de données
- En cas de **réinitialisation** de la tablette (perte des données locales)

### Procédure
```
1. Ouvrir le formulaire souhaité (thème sélectionné)
2. Menu ⋮ (trois points) → "Recharger depuis serveur"
3. Overlay : "Rechargement…"
4. Résultat :
   ✅ Les données du serveur remplacent les données locales dans le formulaire
   ✅ La version serveur est sauvegardée dans SQLite (cache local mis à jour)
   ❌ Erreur : "Aucune donnée retournée par le serveur" (aucune donnée enregistrée)
```

### Comportement du rechargement automatique
À chaque ouverture d'un formulaire, l'application tente **en arrière-plan** de récupérer les données serveur :
- **Formulaire vide** : les données serveur remplissent automatiquement le formulaire
- **Formulaire d'identification** : les données serveur sont toujours prioritaires (pré-remplissage forcé)
- **Formulaire avec saisies locales** : seuls les champs vides sont complétés (les saisies locales sont préservées)

> ⚠️ Le rechargement manuel (depuis le menu) **écrase toujours** les données locales. À utiliser avec précaution si des données ont été saisies et non encore envoyées.

---

## 13. Envoi global — Établissement ou Campagne entière

### Envoi de tous les formulaires d'un établissement
Envoie tous les thèmes saisis pour l'établissement courant en une seule opération.

```
Depuis l'écran de saisie d'un établissement :
  Menu ⋮ → "Envoyer tous les formulaires"
  → Confirmation : "Cette opération peut prendre plusieurs minutes. Continuer ?"
  → Barre de progression : "3 / 7 formulaires envoyés…"
  → Résumé final : "✅ 6 envoyés, ⚠️ 1 ignoré (données manquantes)"
```

**Formulaires ignorés** = thèmes sans aucune donnée locale (jamais remplis ou vides).

### Envoi de toute la campagne
Envoie tous les formulaires de **tous les établissements** saisis dans la campagne.

```
Depuis l'écran détail campagne / liste établissements :
  Bouton "Envoyer tous les établissements"
  → Confirmation + avertissement durée
  → Barre de progression : "47 / 278 formulaires envoyés…"
  → Résumé : "✅ 265 envoyés, ⚠️ 13 ignorés"
```

### Recommandations d'usage
| Situation | Méthode recommandée |
|-----------|---------------------|
| Envoi après saisie dans un seul établissement | Envoi par établissement |
| Retour en ville après terrain multi-établissements | Envoi global campagne |
| Vérification d'un formulaire spécifique | Envoi formulaire par formulaire |

> **Conseil réseau :** L'envoi global peut prendre 5-30 minutes selon le nombre de formulaires et la qualité du réseau. Brancher la tablette sur secteur et utiliser le WiFi.

---

## 14. Gestion des erreurs réseau

### Messages d'erreur et leur signification

| Message affiché | Cause probable | Action |
|-----------------|---------------|--------|
| "Impossible de joindre le serveur. Vérifiez l'URL et votre connexion réseau." | Serveur éteint, IP incorrecte, réseau coupé | Vérifier WiFi + URL + XAMPP démarré |
| "Délai d'attente dépassé après 3 tentatives. Le serveur ne répond pas." | Serveur très lent (XAMPP chargé) ou réseau instable | Réessayer dans quelques minutes |
| "Erreur réseau lors de l'envoi. Réessayez quand le réseau est stable." | Coupure réseau momentanée pendant l'envoi | Rapprocher la tablette du point d'accès WiFi |
| "Accès refusé (401)" | Mot de passe changé côté serveur | Reconnecter via Paramètres → Serveur |
| "Endpoint introuvable (404)" | URL serveur incorrecte ou serveur mal configuré | Vérifier l'URL dans les paramètres |
| "Pas de connexion. Les données seront envoyées ultérieurement." | Mode hors ligne explicite | Normal — données sauvegardées localement |

### Diagnostic réseau rapide
```
Avant d'envoyer, vérifier :
  ✅ Icône WiFi visible dans la barre d'état Android
  ✅ Ping du serveur : ouvrir navigateur → saisir l'URL serveur
  ✅ XAMPP démarré : Apache + MySQL verts dans le panneau XAMPP
  ✅ URL dans paramètres = URL qui fonctionne dans le navigateur
```

### Données perdues en cas d'échec ?
**Non.** L'application sauvegarde toujours localement **avant** d'envoyer. En cas d'échec réseau :
- Les données restent dans SQLite
- L'envoi peut être retentant à tout moment
- L'icône ☁️ reste disponible (non grisée)

---

## 15. Modification du code PIN

### Accès
```
Menu principal → Paramètres (⚙️) → Onglet "PIN"
```

### Procédure
```
1. Saisir l'ancien PIN (validation de l'identité)
2. Saisir le nouveau PIN (4-8 chiffres)
3. Confirmer le nouveau PIN
4. Appuyer "Changer le PIN"
5. Confirmation : "PIN mis à jour avec succès"
```

### Points importants
- L'ancien PIN est toujours requis (sécurité anti-modification non autorisée)
- Le nouveau PIN entre en vigueur immédiatement au prochain déverrouillage
- La question de sécurité n'est **pas** affectée par le changement de PIN

---

## 16. Réinitialisation du PIN via question de sécurité

En cas d'**oubli du PIN**, la question de sécurité permet d'en créer un nouveau sans connexion au serveur.

### Condition préalable
La question de sécurité et sa réponse doivent avoir été configurées lors de la création du PIN (voir section 3).

### Procédure
```
Écran de saisie PIN → Lien "PIN oublié ?"
  1. La question de sécurité s'affiche
  2. Saisir la réponse exacte (sensible à la casse)
  3. Saisir le nouveau PIN
  4. Confirmer le nouveau PIN
  5. "PIN réinitialisé avec succès"
```

### Si la question de sécurité n'a pas été configurée
Il n'existe aucun moyen de récupérer l'accès sans :
1. **Désinstaller** l'application (perte de toutes les données non envoyées)
2. **Réinstaller** et reconfigurer
3. → Les données envoyées au serveur avant la réinstallation sont **conservées** sur le serveur

> **Recommandation administrative :** Exiger la configuration de la question de sécurité pour tous les agents lors de l'installation.

---

## 17. Déconnexion et changement d'utilisateur

### Déconnexion simple (verrouillage PIN)
L'application se verrouille automatiquement à chaque fermeture. Au prochain lancement, le PIN est demandé. **Les données et paramètres sont conservés.**

### Changer d'utilisateur (compte différent)
```
Paramètres → Onglet "Serveur" → Modifier identifiant + mot de passe
→ Valider → Nouvelle connexion au serveur
```

> ⚠️ Si le nouvel utilisateur a des droits différents (autres campagnes), les campagnes déjà téléchargées restent disponibles localement mais ne sont peut-être plus accessibles sur le serveur.

### Réinitialisation complète
Pour remettre l'application à zéro (nouvelle tablette assignée à un autre agent) :
1. Désinstaller l'application
2. Réinstaller depuis l'APK
3. Reconfigurer : URL serveur → identifiants → PIN

---

## 18. Paramètres avancés

### Accès aux paramètres
```
Menu principal → Paramètres (⚙️)
  ├── Onglet "Serveur"  : URL + identifiants + test de connexion
  ├── Onglet "PIN"      : modification du PIN
  └── Onglet "Sécurité" : question de sécurité (créer / modifier / supprimer)
```

### Tester la connexion serveur
```
Paramètres → Serveur → bouton "Tester la connexion"
  ✅ "Connexion établie" : serveur joignable et identifiants valides
  ❌ "Erreur connexion" : vérifier URL, réseau, et XAMPP
```

### Gérer la question de sécurité
```
Paramètres → Sécurité
  → Créer : définir question + réponse (première fois)
  → Modifier : l'ancienne réponse est requise
  → Supprimer : désactive la récupération PIN par question
```

---

## 19. Résolution des problèmes courants

### Problème 1 : L'application ne se lance pas / plante au démarrage
**Causes possibles :**
- Version Android trop ancienne (< 5.0)
- APK corrompu lors du transfert

**Solution :**
1. Vérifier Android : Paramètres → À propos → Version Android
2. Retransférer l'APK depuis une source fiable
3. Désinstaller puis réinstaller

---

### Problème 2 : "Délai d'attente dépassé" lors de l'envoi sur réseau stable
**Cause :** Le serveur XAMPP met plus de 10 minutes à répondre (base de données lente, serveur chargé).

**Solutions :**
1. Attendre que le serveur soit moins chargé et réessayer
2. Vérifier que XAMPP est bien démarré (Apache + MySQL)
3. Vérifier qu'aucune autre opération lourde n'est en cours sur le serveur
4. Contacter l'administrateur serveur

> L'application réessaie automatiquement 3 fois avant d'afficher l'erreur.

---

### Problème 3 : Le formulaire s'affiche avec des caractères étranges (???, Ã©)
**Cause :** Problème d'encodage (ISO-8859-15 vs UTF-8). L'application corrige automatiquement dans 99% des cas.

**Solutions :**
1. Forcer un rechargement : quitter l'écran et revenir
2. Supprimer le cache HTML : désinstaller et réinstaller la campagne
3. Contacter le support technique

---

### Problème 4 : Les contrôles de cohérence ne s'affichent jamais
**Causes possibles :**
- Les règles de cohérence n'ont pas encore été téléchargées
- Le réseau a été coupé pendant le téléchargement des règles

**Solutions :**
1. S'assurer que la campagne a bien été téléchargée **en entier** avec connexion réseau
2. Naviguer vers un établissement → formulaire → attendre 10-30s (les règles arrivent en arrière-plan)
3. Si toujours absent : retélécharger la campagne (les données saisies sont conservées)

---

### Problème 5 : "Accès refusé (401)" lors de l'envoi
**Cause :** Les identifiants stockés sur la tablette ne correspondent plus au mot de passe actuel sur le serveur.

**Solution :**
1. Paramètres → Serveur → Saisir le nouveau mot de passe → Enregistrer
2. Réessayer l'envoi

---

### Problème 6 : Les données semblent perdues après réinstallation
**Explication :** La réinstallation efface la base SQLite locale. Les données **envoyées au serveur avant** la réinstallation sont disponibles via "Recharger depuis serveur".

**Récupération :**
1. Reconfigurer l'application (URL serveur, identifiants, PIN)
2. Télécharger la campagne
3. Naviguer vers chaque établissement → chaque formulaire → menu ⋮ → "Recharger depuis serveur"
4. Les données envoyées sont récupérées automatiquement

---

## 20. Tableau de bord administrateur — Vérifications essentielles

Ce tableau est destiné aux **superviseurs de collecte** pour s'assurer du bon fonctionnement du déploiement.

### Vérifications avant envoi sur le terrain

| Vérification | Commande / Action | Résultat attendu |
|--------------|------------------|-----------------|
| Application installée | Lancer StatEduc Mobile | Écran PIN ou connexion s'affiche |
| PIN configuré | Saisir le PIN | Accès à la liste des campagnes |
| Connexion serveur | Paramètres → Tester connexion | "Connexion établie" |
| Campagne téléchargée | Liste campagnes → statut | "Téléchargée le JJ/MM/AAAA" |
| Formulaires disponibles | Naviguer vers un établissement | Chips des thèmes visibles |
| Règles de cohérence actives | Saisir une valeur incohérente | Bannière orange apparaît |

### Vérifications après collecte terrain

| Vérification | Action | Résultat attendu |
|--------------|--------|-----------------|
| Données saisies localement | Ouvrir un formulaire saisi | Données pré-remplies |
| Envoi réussi | Icône statut formulaire | Coche verte (is_sent=1) |
| Cohérence serveur | Message après envoi | "Données envoyées avec succès" sans dialogue violation |
| Données sur serveur | Application web StatEduc | Données visibles dans interface web |

### Suivi du statut d'envoi par établissement
Depuis l'écran campagne (liste des établissements) :
- 📋 **Gris** : formulaire non rempli
- 💾 **Orange** : données locales présentes, non envoyées
- ✅ **Vert** : données envoyées au serveur avec succès
- ⚠️ **Rouge** : envoi échoué (erreur réseau ou serveur)

---

## Annexe — Contacts et ressources

| Ressource | Lien / Contact |
|-----------|---------------|
| Code source | https://github.com/NasserKailou/stateduc_mobile (branche `ak_main`) |
| PR de développement | https://github.com/NasserKailou/stateduc_mobile/pull/1 |
| Documentation technique | `recapitulatif.md` (architecture, correctifs, guide développeur) |
| Historique Flutter | `stateduc_flutter/CHANGELOG.md` |
| Note de présentation | `notepresentation.md` (transfert de compétences) |

---

*Guide d'administration StatEduc Mobile — MEN Burundi — Sessions 1-20 — Juin 2026*
*Document à utiliser comme support de formation pour les administrateurs et superviseurs de collecte.*
