# Rapport d'Analyse — Intégration des Agents de Collecte Mobile dans le Module `suivi_saisie`

**Date :** 2026-08-03  
**Branche :** `ak_secure`  
**Session :** 51  
**Auteur :** Développeur Senior StatEduc (IA)

---

## 1. Résumé Exécutif

Le module `suivi_saisie` (accessible via `administration.php?val=suivi_saisie`) exclut explicitement les agents de collecte mobile (`CODE_GROUPE=4`) de sa liste d'opérateurs. Cette exclusion est causée par **une seule ligne SQL** dans `suivi_saisie_criteres.php` (ligne 471). La correction est **additive et non destructive** : elle étend la liste des agents visibles sans modifier le comportement existant pour les agents classiques.

Le suivi individuel des agents mobiles nécessite en complément une adaptation de `suivi_saisie_list_etabs.php` pour brancher les agents `CODE_GROUPE=4` sur la table `DATA_SAVING_LOGS` (leur table de trace native) au lieu de `DICO_TRACE` (utilisée exclusivement par la saisie classique).

---

## 2. Fonctionnement Actuel du Module `suivi_saisie`

### 2.1 Architecture de la page

La page `suivi_saisie` fonctionne en deux temps :

**Temps 1 — Formulaire de critères** (`suivi_saisie.php` → inclut `suivi_saisie_criteres.php`) :
- Affiche 4 colonnes de sélection : regroupements géographiques, opérateurs, mode d'affichage, règles de contrôle
- À la soumission du formulaire POST, stocke les critères sélectionnés dans `$_SESSION['suivi_saisie']` :
  - `tab_etabs_run` : liste des codes établissements sélectionnés
  - `tab_users_run` : liste des opérateurs sélectionnés (CODE_USER → {CODE_USER, NOM_USER})
  - `tab_ctrls_run` : règles de contrôle sélectionnées
  - `val_choix_affich` : `par_nbre` ou `par_liste`
- Déclenche un appel AJAX vers `administration.php?val=suivi_list_etabs`

**Temps 2 — Affichage des résultats** (`suivi_saisie_list_etabs.php`) :
- Reçu via AJAX, exécute la logique de suivi
- Interroge `DICO_TRACE` pour construire le mapping `utilisateur → établissements actifs`
- Pour chaque utilisateur sélectionné, instancie la classe `suivi_saisie` (`suivi_saisie_batch.class.php`) qui évalue les règles SQL de `DICO_REGLE_SUIVI` contre les données de la base de saisie classique

### 2.2 Tables et jointures concernées

| Table | Rôle | Utilisée par |
|-------|------|--------------|
| `ADMIN_USERS` | Référentiel des utilisateurs (CODE_USER, NOM_USER, CODE_GROUPE) | `suivi_saisie_criteres.php` ligne 471 |
| `DICO_TRACE` | Traces de saisie classique (CODE_USER numérique, CODE_ETABLISSEMENT, ACTION, CODE_SECTEUR, CODE_ANNEE, CODE_FILTRE) | `suivi_saisie_list_etabs.php` lignes 44-56 |
| `DATA_SAVING_LOGS` | Traces de synchronisation mobile (CODE_USER = login string, CODE_ECOLE, ID_THEME_SYSTEME, CODE_CAMPAGNE, CODE_SECTEUR, CODE_ANNEE, CODE_PERIODE/CODE_FILTRE, STATUT_OPERATION) | `data_save.php` → `ctrl_collectors_feedbacks.php` |
| `DICO_FIXE_REGROUPEMENT` | Affectation des agents mobiles aux campagnes/écoles | `data_save.php`, `ctrl_collectors_feedbacks.php` |
| `DICO_REGLE_SUIVI` / `DICO_REGLE_SUIVI_SYSTEME` | Règles de contrôle évaluées | `suivi_saisie_batch.class.php` |
| Tables de données saisies (ex. `T_SCOLARISATION`) | Evaluées par les SQL des règles de suivi | `suivi_saisie_batch.class.php` |

### 2.3 Variables de session utilisées

| Variable | Source | Rôle |
|----------|--------|------|
| `$_SESSION['secteur']` | GET `id_systeme` | CODE_SECTEUR (système d'enseignement, ex: 2) |
| `$_SESSION['annee']` | Global | CODE_ANNEE |
| `$_SESSION['filtre']` | GET `filtre` | CODE_FILTRE = CODE_PERIODE dans DATA_SAVING_LOGS |
| `$_SESSION['suivi_saisie']['tab_users_run']` | POST traité | Opérateurs sélectionnés |
| `$_SESSION['suivi_saisie']['tab_etabs_run']` | POST traité | Établissements du périmètre |
| `$_SESSION['suivi_saisie']['liste_etabs_user']` | Calculé dynamiquement | Établissements actifs par utilisateur courant |

---

## 3. Rôle Confirmé de `CODE_GROUPE=4`

### 3.1 Confirmation

`CODE_GROUPE=4` identifie les **agents de collecte mobile** dans la table `ADMIN_USERS`. Cette identification est confirmée par :

1. **`questionnaire_ws.php` (ligne 52-56)** : le web service mobile récupère `CODE_GROUPE` de `ADMIN_USERS` pour la session utilisateur
2. **`questionnaire_reload_ws.php` (ligne 73-77)** : idem pour le rechargement mobile
3. **`user.class.php` (ligne 1067-1068)** : la requête `ADMIN_GROUPES` utilise `CODE_GROUPE = -1` pour l'app mobile (`$_GET['app'] == 'mob'`) en lieu et place du groupe réel, confirmant une gestion séparée du profil mobile
4. **`suivi_saisie_criteres.php` (ligne 471)** : `WHERE CODE_GROUPE<>4` — exclusion **délibérée et explicite** des agents mobiles

### 3.2 Autres valeurs de CODE_GROUPE

D'après l'analyse du code :
- `CODE_GROUPE = 1` : administrateurs (accès complet, menu.class.php ligne 355)
- `CODE_GROUPE = 2/3` : opérateurs classiques de saisie (hiérarchie ORDRE_GROUPE)
- `CODE_GROUPE = 4` : agents de collecte mobile (exclus de suivi_saisie, utilisent data_save.php)

---

## 4. Rôle Confirmé du Paramètre de Traces dans `params.php`

### 4.1 Paramètre `DATA_ENTRY_TRACE`

```php
// params.php ligne 114
$GLOBALS['PARAM']['DATA_ENTRY_TRACE'] = true;
```

**Rôle réel** : ce paramètre **gouverne l'écriture** des entrées dans `DICO_TRACE` lors de la saisie classique via `grille.class.php`. Il est déjà à `true` dans Burundi — **aucune modification nécessaire**.

**Important** : ce paramètre n'a **aucun effet** sur les données mobiles. Les agents mobiles écrivent dans `DATA_SAVING_LOGS` via `data_save.php::saveLogInfo()`, indépendamment de ce paramètre.

### 4.2 Paramètre `FILTRE`

```php
// params.php ligne 116
$GLOBALS['PARAM']['FILTRE'] = true;
```

**Rôle réel** : active le filtrage par période. Quand `true`, la requête `DICO_TRACE` dans `suivi_saisie_list_etabs.php` inclut `CODE_FILTRE=$_SESSION['filtre']`. Pour la cohérence mobile, `$_SESSION['filtre']` correspond à `CODE_PERIODE` dans `DATA_SAVING_LOGS`.

### 4.3 Conclusion

Aucune modification de `params.php` n'est nécessaire. Les deux mécanismes de trace fonctionnent de façon indépendante :
- **Classique** : `grille.class.php` → `DICO_TRACE` (conditionné par `DATA_ENTRY_TRACE`)
- **Mobile** : `data_save.php::saveLogInfo()` → `DATA_SAVING_LOGS` (inconditionnelle)

---

## 5. Cause Exacte de l'Exclusion des Agents Mobiles

### 5.1 Exclusion de la liste (Couche 1)

**Fichier** : `suivi_saisie_criteres.php`  
**Ligne** : 471  
**Code incriminé** :

```php
$requete='SELECT CODE_USER, NOM_USER AS LIB_USER FROM ADMIN_USERS WHERE CODE_GROUPE<>4;';
```

La clause `WHERE CODE_GROUPE<>4` exclut **tous les utilisateurs de CODE_GROUPE=4** de la liste des opérateurs proposée dans le formulaire de critères. Sans apparaître dans cette liste, les agents mobiles ne peuvent jamais être sélectionnés pour un suivi.

### 5.2 Absence de données de trace (Couche 2)

**Fichier** : `suivi_saisie_list_etabs.php`  
**Lignes** : 44-56  
**Code incriminé** :

```php
$req_etabs_users_actions = "SELECT DICO_TRACE.CODE_ETABLISSEMENT, DICO_TRACE.CODE_USER, ...
    FROM DICO_TRACE
    WHERE DICO_TRACE.CODE_SECTEUR=...
    GROUP BY DICO_TRACE.CODE_ETABLISSEMENT, DICO_TRACE.CODE_USER, ...";
```

Même si un agent mobile était sélectionné (après correction de la couche 1), sa `CODE_USER` numérique n'apparaîtrait jamais dans `DICO_TRACE` (qui ne reçoit des données que de la saisie classique). Le résultat serait un agent affiché avec zéro établissement suivi.

### 5.3 Différence structurelle entre les deux tables de trace

| Caractéristique | DICO_TRACE (classique) | DATA_SAVING_LOGS (mobile) |
|-----------------|------------------------|---------------------------|
| Clé utilisateur | `CODE_USER` (integer) | `CODE_USER` (varchar = NOM_USER/login) |
| Clé école | `CODE_ETABLISSEMENT` | `CODE_ECOLE` |
| Période | `CODE_FILTRE` | `CODE_PERIODE` ET `CODE_FILTRE` |
| Granularité | ACTION par ACTION | 1 ligne par synchronisation de thème |
| Indicateur de succès | Presence = saisie | `STATUT_OPERATION='OKSAVE'` |
| Lien thème | Indirect via règles DICO_REGLE_SUIVI | Direct via `ID_THEME_SYSTEME` |

Cette différence structurelle impose un traitement **conditionnel** selon le type d'agent.

---

## 6. Solution Implémentée

### 6.1 Fichier 1 : `suivi_saisie_criteres.php` — Inclusion des agents mobiles

**Modification** : La requête à la ligne 471 est remplacée par une requête `UNION ALL` qui retourne :
- Tous les agents classiques (`CODE_GROUPE<>4`) avec le label `[Classique]` 
- Tous les agents mobiles (`CODE_GROUPE=4`) avec le label `[Mobile]`
- Un champ `CODE_GROUPE` supplémentaire stocké dans `tab_users_run` pour le routage conditionnel en Layer 2
- Tri : classiques en premier (alphabétique), puis mobiles (alphabétique)

La valeur de la checkbox reste `CODE_USER` (numérique) — **aucune régression** sur la logique de traitement POST existante (lignes 59-126).

**Adaptation complémentaire** : Dans la boucle `tab_users_checked` (lignes 120-126), la requête de résolution du CODE_USER vers {CODE_USER, NOM_USER} est étendue pour inclure `CODE_GROUPE`, afin de permettre le routage conditionnel dans `suivi_saisie_list_etabs.php`.

### 6.2 Fichier 2 : `suivi_saisie_list_etabs.php` — Routage vers DATA_SAVING_LOGS

**Modification** : Après la construction de `$tab_etabs_users` depuis `DICO_TRACE` (lignes 44-73), un bloc additionnel est ajouté pour traiter les agents mobiles (`CODE_GROUPE=4`) :

- Pour chaque utilisateur de `$tab_users_run` avec `CODE_GROUPE=4`, une requête sur `DATA_SAVING_LOGS` retrouve les écoles où l'agent a envoyé des données (`STATUT_OPERATION='OKSAVE'`) pour le secteur, l'année et la période en cours
- Ces écoles sont filtrées pour ne garder que celles appartenant au périmètre sélectionné (`tab_etabs_run`)
- Les résultats sont injectés dans `$tab_etabs_users[CODE_USER]` — **même structure** que pour les agents classiques
- L'affichage et les règles de suivi s'appliquent ensuite **de façon identique** pour les deux types d'agents

**Routage DATA_SAVING_LOGS** :
- `CODE_USER` dans DATA_SAVING_LOGS = `NOM_USER` (login string) → jointure via `ADMIN_USERS.NOM_USER`
- Filtre STATUT_OPERATION='OKSAVE' → ne compter que les soumissions réussies
- Filtre `CODE_SECTEUR` = `$_SESSION['secteur']` (cohérence systèmes d'enseignement)
- Filtre `CODE_ANNEE` = `$_SESSION['annee']`
- Si FILTRE=true : `CODE_PERIODE` = `$_SESSION['filtre']`

---

## 7. Garanties de Non-Régression

1. **Agents classiques** : la requête DICO_TRACE est **intacte** — aucune modification de ses lignes existantes
2. **Variable `$tab_etabs_users`** : le bloc mobile est **additif** — il ajoute des entrées sans modifier celles des agents classiques
3. **Classe `suivi_saisie`** : appelée avec les mêmes paramètres pour tous les agents — aucune modification
4. **Mode `par_liste`** : le lien vers `questionnaire.php` et la hiérarchie géographique fonctionnent identiquement
5. **Filtres URL** : `id_systeme`, `id_chaine`, `type_reg` non modifiés
6. **Session `tab_users_run`** : structure enrichie avec `CODE_GROUPE`, mais accès existant via `$user['NOM_USER']` et `$user['CODE_USER']` toujours valides
7. **`tableau_check()`** : appelé avec les mêmes paramètres (`CODE_USER`, `LIB_USER`) — aucune modification

---

## 8. Résultats Attendus Après Correction

| Scénario | Avant | Après |
|----------|-------|-------|
| Agent classique visible dans la liste | ✅ | ✅ |
| Agent mobile visible dans la liste | ❌ | ✅ (label `[Mobile]`) |
| Suivi agent classique | ✅ | ✅ (inchangé) |
| Suivi agent mobile | ❌ (0 école) | ✅ (via DATA_SAVING_LOGS) |
| Règles DICO_REGLE_SUIVI pour agent classique | ✅ | ✅ (inchangé) |
| Règles DICO_REGLE_SUIVI pour agent mobile | ❌ | ✅ (si les écoles ont des données) |
| Distinction visuelle classique/mobile | ❌ | ✅ (label dans le nom) |
| Filtre par période | ✅ pour classique | ✅ pour classique ET mobile |

---

## 9. Fichiers Modifiés

| Fichier | Nature | Lignes modifiées |
|---------|--------|-----------------|
| `StatEduc_burundi/server-side/include/administration/suivi_saisie_criteres.php` | Correction additive | ~471-472 (requête SQL) + ~120-126 (enrichissement session) |
| `StatEduc_burundi/server-side/include/administration/suivi_saisie_list_etabs.php` | Correction additive | Après ligne 73 (bloc mobile additionnel) |

**Fichiers non modifiés** :
- `params.php` — aucun changement nécessaire (`DATA_ENTRY_TRACE=true` déjà actif)
- `suivi_saisie_batch.class.php` — classe réutilisée sans modification
- `ctrl_collectors_feedbacks.php` — patterns de requêtes réutilisés, fichier non modifié
- `data_save.php` — non modifié
- Tous les autres modules — zéro impact
