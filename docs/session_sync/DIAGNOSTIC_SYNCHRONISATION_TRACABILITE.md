# Diagnostic — Synchronisation, doublons, traçabilité et suivi administratif

**Projet :** StatEduc Mobile Burundi — `StatEduc_burundi` (PHP natif / Slim 2 / ADODB) + `stateduc_flutter` (Flutter / Dart / SQLite)
**Branche analysée :** `ak_secure` (commit `d5b3c4b`)
**Nature du document :** audit + propositions. **Aucune modification de code n'a été effectuée.**
**Date :** 2026-09-16

---

## 0. Synthèse exécutive

| Axe | Constat principal | Gravité |
|---|---|---|
| Visibilité mobile des statuts | Le flag `is_sent` existe en SQLite mais **n'est affiché nulle part** ; la bannière « données non envoyées » est un *placeholder vide* (`SizedBox.shrink()`) | Élevée |
| Échec partiel | Le résumé d'envoi global donne des **compteurs sans liste** des formulaires en échec ; aucune persistance de l'échec (pas de statut « échec », pas de message conservé) | Élevée |
| Renvoi sélectif | **Inexistant** : l'envoi global renvoie *tout* (déjà envoyé ou non) et **exclut les formulaires à période** (`id_filter IS NULL` uniquement) | Élevée |
| Soumissions multiples | Serveur idempotent au niveau des tables métier (diff `comparer()` → `R/U/I/D`) → **pas de doublon de données** ; mais **une ligne de journal par tentative**, et un renvoi de données locales partielles peut **supprimer** (`D`) des lignes serveur valides | Moyenne |
| KOSAVE | Marqué `is_sent=1` côté mobile → l'agent croit avoir transmis alors que **rien n'est en base** | Élevée |
| Réponses serveur vides / non-JSON | Traitées comme **succès** par le mobile (`return true`) | Élevée |
| Accusé de réception | Simple chaîne `OKSAVE` : pas d'identifiant, pas d'horodatage serveur, pas de compte de lignes | Moyenne |
| Écrans admin | `suivi_saisie` partiellement branché sur `DATA_SAVING_LOGS` (S51) ; écrans `feedbacks` / `controle_val` **orphelins** (pas dans le menu) ; aucun tableau établissement × thème | Élevée |
| Indicateurs complet/partiel/rien | Logique existante uniquement dans le mailer `remind_collectors.php` (non branché, SMTP en dur) ; filtre `CODE_PERIODE=N` **ne matche jamais** les envois mobiles sans période (`NULL`) | Élevée |
| Demande de correction / réouverture | Tables `THEME_VALIDATION` / `TYPE_VALIDATION` paramétrées mais **aucun chemin d'écriture** ; contrôle de fermeture de campagne **désactivé en dur** (`$survey_curr_status = 2;`) | Élevée |

---

## 1. Cartographie de la chaîne de synchronisation

### 1.1 Côté mobile — `stateduc_flutter/lib`

| Couche | Fichier | Éléments impliqués |
|---|---|---|
| Persistance | `services/database_service.dart` | Table `collected_data` (v8) ; méthodes `saveCollectedData`, `markCollectedDataSent`, `hasUnsentData` (**non utilisée**), `getDistinctEtabQstWithData`, `deleteCollectedData` |
| Réseau | `services/api_service.dart` | `saveData()` → `_withRetry()` → `_saveDataOnce()` (l. 1080-1303) ; `reloadData()` (l. 1317) ; `fetchServerActiveYear()` ; `checkCoherence()` ; classes `ApiException`, `KosaveException` |
| Orchestration | `providers/data_entry_provider.dart` | `saveLocally()` (l. 919), `_checkYearConsistency()` (l. 973), `sendToServer()` (l. 1042), `reloadFromServer()` (l. 1302), `sendAllFormsForSchool()` (l. 1420), `sendAllFormsForCampaign()` (l. 1549), `sendAllCampaignData()` (l. 1657, non appelée), `_autoReloadFromServerBackground()` (l. 553) |
| Écrans | `screens/data_entry/school_data_screen.dart` | Boutons *Sauver* / *Envoyer* (l. 171-294), menu *Recharger* / *Envoyer tous*, `_QuestionSelector` (chips sans statut, l. 1064), `_MessageBanner` |
| | `screens/schools/campaign_detail_screen.dart` | *Envoi global campagne* (l. 280-410), `_UnsentDataBanner` **placeholder** (l. 628-640), `_SchoolTile` sans indicateur |
| Moteurs offline | `services/theme_rule_engine.dart`, `services/coherence_evaluator.dart` | Injectent `formData` dans `collected_data` avec `is_sent=0` sous SAVEPOINT (rollback → sans effet persistant) |

**Schéma SQLite `collected_data` (v8)**

```
id INTEGER PK AUTOINCREMENT
code_type_annee TEXT NOT NULL DEFAULT ''   -- année (CODE_TYPE_ANNEE)
id_camp, id_etab, id_qst TEXT NOT NULL
id_filter TEXT NULL                        -- période
field_name TEXT NOT NULL, field_value TEXT
is_sent INTEGER NOT NULL DEFAULT 0         -- 0 = en attente, 1 = envoyé/rechargé
updated_at TEXT NOT NULL
UNIQUE (code_type_annee, id_camp, id_etab, id_qst, COALESCE(id_filter,''), field_name)
```

> Granularité = **champ**, pas formulaire. Il n'existe aucune entité « soumission » (ni date d'envoi, ni erreur, ni tentative).

### 1.2 Côté serveur — `StatEduc_burundi`

| Endpoint (Slim 2) | Fichier | Rôle dans la chaîne |
|---|---|---|
| `POST data_save.php/theme_save/:user/:camp/:sector/:theme/:etab/:filter/:start/:id_annee` | `data_save.php` l. 205-487 (`theme_save_handler`) | Contrôle année (AK-YEAR-CHECK, PARAM_DEFAUT), droits (DICO_FIXE_REGROUPEMENT + fallback ADMIN_USERS), cURL interne → `questionnaire_ws.php`, journalisation |
| `questionnaire_ws.php?sector&theme&code_etab&type_ent_stat&annee&login&langue&filtre` | `questionnaire_ws.php` + `server-side/instances/instance_grille.php` | Double-include : GET construit la grille (`$_SESSION['curobj_instance']`), POST → `get_post_template()` → `comparer()` → `maj_bdd()` dans une transaction ; émet `MAJ_OK=true/false` puis `ISOKSAVEINDATABASE` |
| `GET data_reload.php/theme_data/:user/:sector/:theme/:camp/:etab/:filter` | `data_reload.php` l. 105 → `questionnaire_reload_ws.php` | Relecture des données serveur (année = `$_SESSION['annee']`, **pas** de segment `id_annee`) |
| `GET annees_ws.php/active/:login`, `/list/:login` | `annees_ws.php` | Année active serveur pour `_checkYearConsistency` |
| `GET data_controle.php/...`, `data_rules.php/...` | | Cohérence serveur / règles offline |
| `POST data_save.php/theme_info_save` | `data_save.php` l. 490 | Géolocalisation (`THEME_INFO_SAVE`) — **non appelé par Flutter** |

**Logique d'upsert serveur** — `server-side/classes/metier/grille.class.php`

- `existe_element_grille_action()` (l. 2658) compare les **clés** de chaque ligne (PK de la table mère : `CODE_ETABLISSEMENT`, `CODE_TYPE_ANNEE`, `[CODE_TYPE_PERIODE]`, dimensions) : `R` (identique), `U` (modifiée), `I` (absente en BD).
- `comparer()` (l. 5708) : ligne en BD absente du POST → `D`.
- `maj_bdd()` (l. 5864) : DELETE → INSERT → UPDATE ; toute erreur SQL ⇒ `$GLOBALS['theme_data_MAJ_ok']=false` ⇒ KOSAVE. Trace par action dans `DICO_TRACE` si `PARAM['DATA_ENTRY_TRACE']` (= `true` au Burundi).

**Tables de traçabilité (base dico)**

| Table | Écrite par | Colonnes utilisées | Lue par |
|---|---|---|---|
| `DATA_SAVING_LOGS` | `data_save.php::saveLogInfo()` (l. 596) — succès **et** erreur cURL | `CODE_USER` (login), `LOG_DATE_TIME`, `ID_THEME_SYSTEME`, `CODE_ANNEE`, `CODE_PERIODE`, `CODE_CAMPAGNE`, `CODE_SECTEUR`, `CODE_ECOLE`, `CODE_FILTRE`, `STATUT_OPERATION` ∈ {`OKSAVE`,`KOSAVE`,`KO`} | `suivi_saisie_list_etabs.php` (S51, agents `CODE_GROUPE=4`), `ctrl_collectors_feedbacks.php`, `remind_collectors.php`, `sms_sync.php` |
| `DICO_TRACE` | `grille.class.php::maj_bdd()` (actions I/U/D) | `ID_TRACE, NOM_TABLE, ID_ACTION, CODE_USER, NOM_USER, ID_THEME, ACTION, SQL_ACTION, CODE_SECTEUR, CODE_ETABLISSEMENT, CODE_ANNEE, CODE_FILTRE, DATE_SAISIE` | `suivi_saisie_list_etabs.php`, `ctrl_coherence_criteres.php` |
| `moblogs/<login>.log` (fichier plat) | `data_save.php` | `date;camp;sector;theme;etab;filter;statut` (+ diag KOSAVE S62) | Lecture manuelle |
| `THEME_INFO_SAVE` | `theme_info_save` (non utilisé par Flutter) | | — |
| `THEME_VALIDATION` / `TYPE_VALIDATION` | **aucun chemin d'écriture trouvé** | `ID_THEME, CODE_TYPE_VALIDATION, COMMENTAIRES, CODE_ETABLISSEMENT, CODE_TYPE_ANNEE, CODE_TYPE_PERIODE` | `sms_sync.php`, `ctrl_collectors_feedbacks.php` (lecture seule) |

**Écrans d'administration liés** (`administration.php?val=…`)

| `val` | Fichier | Dans le menu ? |
|---|---|---|
| `suivi_saisie` / `suivi_list_etabs` | `suivi_saisie.php`, `suivi_saisie_list_etabs.php` | Oui (« Suivi Opérateurs Saisie ») |
| `controle` / `incohr_list_etabs` | `controle_coherence.php` | Oui |
| `feedbacks` | `gestion_collectors_feedback.php` → `ctrl_collectors_feedbacks.php` | **Non** (URL directe) |
| `feedbacks_auto*`, `send_feedbacks_auto` | `auto_collectors_feedbacks*.php`, `remind_collectors.php` | **Non** |
| `controle_val`, `validate_etab_detais` | `controle_validation.php`, `ctrl_validation_etab_details.php` | **Non** |
| `ViderTraces` | `vider_traces_saisie.php` | Oui |

### 1.3 Séquence d'un envoi (état actuel)

```
Flutter sendToServer()
  ├─ _checkYearConsistency()  (annees_ws.php/active — fail-open réseau)
  ├─ saveLocally()            → collected_data is_sent=0 (REPLACE)
  ├─ api.saveData() ── _withRetry (2 retries : sendTimeout/receiveTimeout/unknown)
  │     POST data_save.php/theme_save/.../{filter|'null'}/0/{annee}
  │       ├─ AK-YEAR-CHECK (PARAM_DEFAUT.CODE_ANNEE)      → 400 « Année incorrecte »
  │       ├─ droits (DICO_FIXE_REGROUPEMENT ∪ ADMIN_USERS)  → 400
  │       ├─ getSurveyStatus() … puis $survey_curr_status = 2;  ← contrôle NEUTRALISÉ
  │       ├─ session_write_close(); cURL → questionnaire_ws.php (&filtre= toujours présent)
  │       │      comparer() → maj_bdd() [StartTrans/CompleteTrans] → MAJ_OK=… ISOKSAVEINDATABASE
  │       ├─ success : OKSAVE|KOSAVE → moblogs + DATA_SAVING_LOGS → {se_status:200, se_data:'OKSAVE'}
  │       └─ error   : {se_status:400, se_data:'28 : timeout…'} → DATA_SAVING_LOGS 'KO'
  ├─ OK      → markCollectedDataSent (is_sent=1) → checkCoherence serveur
  ├─ KOSAVE  → markCollectedDataSent (is_sent=1) + bannière orange transitoire
  └─ ApiException / réseau → _error transitoire ; is_sent reste 0
```

---

## 2. Analyse détaillée — Partie A (mobile)

### Point 1 — Affichage des statuts de synchronisation (synchronisé / en attente / échec)

**(a) État actuel**
- Donnée disponible : `collected_data.is_sent` (0/1), positionné par `markCollectedDataSent()` après `OKSAVE`, après `KOSAVE`, et après `reloadFromServer()`.
- `DatabaseService.hasUnsentData()` existe (l. 1905) mais **n'est appelée par aucun écran ni provider**.
- `school_data_screen.dart` : le bouton vert *Sauver/Sauvé* reflète `hasUnsavedChanges` (mémoire → SQLite), **pas** l'état d'envoi. Le bouton *Envoyer* est toujours bleu, sans indication « déjà envoyé ». Les chips `_QuestionSelector` (l. 1156-1190) n'affichent que le libellé.
- `campaign_detail_screen.dart` : `_UnsentDataBanner` (l. 628) est un **placeholder** retournant `SizedBox.shrink()` ; `_SchoolTile` n'affiche que « Statut : {idStatus} » (statut administratif, pas de sync).
- Messages `successMessage` / `error` / `warningMessage` : bannières **transitoires** (effacées au changement de question/écran, `selectQuestion()` remet `_error` et `_successMessage` à null).
- Il n'existe **aucun état « échec »** persistant : un envoi raté laisse simplement `is_sent=0`, indistinguable d'un formulaire jamais envoyé.

**(b) Écarts / limites**
- L'utilisateur ne peut pas savoir, formulaire par formulaire ou école par école, ce qui est transmis.
- Le modèle à deux états (0/1) ne permet pas de distinguer *jamais envoyé*, *modifié depuis envoi*, *échec réseau*, *rejeté serveur (400)*, *KOSAVE*.
- Pas d'horodatage de dernière synchronisation.

**(c) Mise à niveau proposée**
1. **Table SQLite `form_sync_status`** (v9) — 1 ligne par formulaire : `(code_type_annee, id_camp, id_etab, id_qst, COALESCE(id_filter,''))` UNIQUE + `status` ∈ {`draft`,`pending`,`sent`,`failed`,`kosave`,`rejected`} + `last_attempt_at`, `last_success_at`, `last_error`, `attempts`, `server_ack` (JSON). Alimentée par les points existants (`saveLocally`, `sendToServer`, batch, `reloadFromServer`). `is_sent` conservé et maintenu tel quel (compatibilité).
2. **Indicateurs UI** : pastille colorée sur chaque chip de `_QuestionSelector` ; icône + date sur `_SchoolTile` (agrégat par école : n envoyés / n en attente / n échecs) ; remplacement du placeholder `_UnsentDataBanner` par un vrai compteur (requête `status IN ('pending','failed','kosave')`).
3. Écran/onglet « État de synchronisation » listant les formulaires non transmis (voir point 2).

Effort : **moyen** (DB + provider + 3 widgets). Risque de régression : **faible** (table additive, migration idempotente, aucun changement des requêtes existantes).

---

### Point 2 — Identification des formulaires non synchronisés après échec partiel / interruption réseau

**(a) État actuel**
- `sendAllFormsForSchool()` et `sendAllFormsForCampaign()` envoient **séquentiellement**, un formulaire par requête HTTP ; un échec n'interrompt pas la boucle (try/catch par item). Le résultat est une `Map<clé,bool>` → dialogue final « ✅ n envoyés / ⚠️ m non envoyés » **sans liste nominative** et sans distinction entre « pas de données locales » (`false` aussi) et « erreur réseau ».
- Interruption réseau en cours de POST : `_withRetry` réessaie 2 fois pour `sendTimeout/receiveTimeout/unknown` (délai 5 s × n) ; `connectionTimeout`/`connectionError` ne sont pas réessayés → `ApiException` → `is_sent` reste 0. Aucun échec n'est mémorisé.
- Cas critique : requête **reçue et exécutée par le serveur** mais réponse perdue (coupure après `maj_bdd()`). Mobile ⇒ échec, `is_sent=0` ; serveur ⇒ données en base + `OKSAVE` dans `DATA_SAVING_LOGS`. Le renvoi ultérieur est idempotent (voir point 5), donc sans doublon, mais l'agent ne voit pas que c'est déjà passé.
- `_saveDataOnce()` l. 1229 et 1238 : **réponse vide ou non-JSON ⇒ `return true`** (succès). Une page d'erreur Apache/PHP (HTML, HTTP 200 avec warning) est donc comptée comme envoyée.
- Fermeture de l'app pendant l'envoi : aucun état intermédiaire persisté (`_isSending` en mémoire seulement).

**(b) Écarts / limites**
- Aucune liste des échecs, aucune raison conservée, aucune façon de retrouver *lesquels* réessayer.
- Faux positifs (réponse vide/HTML ⇒ succès) → données marquées envoyées à tort.
- Faux négatifs (réponse perdue) → renvoi inutile, mais sans danger grâce à l'idempotence serveur.

**(c) Mise à niveau proposée**
1. Résultat d'envoi typé (`SendOutcome {ok, kosave, rejected(msg), networkError(msg), noData}`) au lieu de `bool`, et écriture systématique dans `form_sync_status` (`failed` + `last_error`).
2. Dialogue de résultat listant **nommément** les formulaires en échec (école / thème / période / cause) avec bouton « Réessayer les échecs ».
3. **Durcir `_saveDataOnce`** : réponse vide ou non-JSON ⇒ `ApiException('Réponse serveur invalide')` (jamais succès). *Préalable :* vérifier en production que `data_save.php` renvoie toujours un JSON (c'est le cas dans le code : `echo json_encode($rps)` dans `success`, `error` et tous les retours anticipés).
4. Écran « Formulaires en attente » (liste `pending/failed/kosave`, tri par école) accessible depuis `campaign_detail_screen`.

Effort : **moyen**. Risque : **faible** (le durcissement 3 peut faire remonter des échecs jusque-là masqués — c'est le but ; à valider sur un jeu de test).

---

### Point 3 — Relance sélective des formulaires non transmis, sans doublons

**(a) État actuel**
- Il n'existe **aucun envoi sélectif** : `getDistinctEtabQstWithData()` retourne tous les couples ayant des données (envoyées ou non) ; `sendAllFormsForSchool()` itère sur **toutes** les questions.
- Les deux batchs appellent `getCollectedData(idFilter: null)` et `saveData(filter: null)` : les formulaires **à période** (`hasFilter=true`, données stockées avec `id_filter` non nul) sont **systématiquement ignorés** par l'envoi global. Seul `sendToServer()` (formulaire courant) gère le filtre via `_effectiveServerFilter`.
- `sendAllFormsForCampaign()` n'envoie pas `etabRegroupId` / `isFirstQuestion` → pas de `LOC_REG_0` ; le serveur le reconstitue depuis `ETABLISSEMENT_REGROUPEMENT` (l. 345-351) — OK tant que l'école existe déjà.
- Absence de doublon : garantie **uniquement** par la logique `comparer()` serveur (clés PK) — pas par un mécanisme d'idempotence explicite (pas d'identifiant de soumission).

**(b) Écarts / limites**
- Renvoi intégral coûteux (chaque POST déclenche la construction complète de la grille HTML côté serveur : plusieurs secondes par thème).
- Formulaires périodiques jamais renvoyés en batch → **perte silencieuse** si l'agent compte sur « Envoyer tout ».
- Un renvoi de formulaire déjà à jour est inoffensif (`R` → aucune écriture) mais crée une ligne `DATA_SAVING_LOGS` supplémentaire.

**(c) Mise à niveau proposée**
1. `getPendingForms(idCamp, codeTypeAnnee)` basé sur `form_sync_status.status IN ('pending','failed','kosave')` **et** repli `is_sent=0` (compatibilité données pré-v9), retournant aussi `id_filter`.
2. Nouvelle action « Envoyer uniquement les formulaires en attente » (école et campagne) ; l'action « Envoyer tout » existante est **conservée**.
3. Correction des batchs pour itérer sur `(id_etab, id_qst, id_filter)` et transmettre `filter`, `etabRegroupId`, `isFirstQuestion` — alignement sur `sendToServer()`.
4. Clé d'idempotence explicite : voir point 5 (`X-Sync-Id`).

Effort : **moyen**. Risque : **faible à moyen** (la prise en compte des filtres dans le batch change le périmètre envoyé — c'est une correction, à tester sur un thème périodique).

---

### Point 4 — Comportement lors de soumissions multiples des mêmes données

**(a) État actuel — comportement précis**
1. Mobile : `saveLocally()` → `REPLACE` sur l'index unique champ ⇒ idempotent localement.
2. Serveur `theme_save_handler` : aucun contrôle de re-soumission ; chaque POST déclenche `questionnaire_ws.php`.
3. `instance_grille.php` (POST) : `get_post_template()` reconstruit la matrice à partir du POST ; `comparer(matrice_BD, matrice_POST)` :
   - ligne identique ⇒ `R` → **aucune requête** ;
   - ligne modifiée ⇒ `U` → `UPDATE … WHERE clés` ;
   - ligne absente en BD ⇒ `I` → `INSERT` ;
   - ligne en BD absente du POST ⇒ `D` → `DELETE` (sauf `ETABLISSEMENT`, `DONNEES_ETABLISSEMENT` → mise à NULL, enseignants liés ailleurs).
4. Transaction ADODB `StartTrans/CompleteTrans` autour de `maj_bdd()` ; erreur SQL ⇒ `MAJ_OK=false` ⇒ `KOSAVE`. Note : `CompleteTrans()` ADODB effectue un rollback si une erreur a été détectée dans la transaction (comportement standard « smart transaction »), sinon commit.
5. Journal : **une ligne `DATA_SAVING_LOGS` et une ligne `moblogs` par tentative**, y compris pour les `R`. `DICO_TRACE` n'est alimentée que pour `I/U/D`.
6. Deux envois **simultanés** du même formulaire (double-tap, ou deux appareils) : `$_SESSION['curobj_instance']` est propre à la session cURL (nouveau cookie par appel) ⇒ pas de collision de session ; les deux `comparer()` peuvent lire le même état initial et produire deux `I` ⇒ le second `INSERT` viole la PK ⇒ **KOSAVE** pour le second (comportement observé S58/S59 pour d'autres causes). Aucun doublon de données, mais un faux « non enregistré ».

**(b) Écarts / limites**
- Pas de doublon *métier* : ✅. En revanche : journal gonflé, absence de verrou/idempotence sur double-clic, et **écrasement/suppression possible** si le POST est partiel (voir point 5).
- Le bouton *Envoyer* n'est désactivé que pendant `isSending` du formulaire courant ; l'envoi global campagne peut être lancé en parallèle d'un envoi unitaire (deux providers différents ne se voient pas).

**(c) Mise à niveau proposée**
1. Serveur : **empreinte de soumission** — `data_save.php` calcule `sha1(login|camp|sector|theme|etab|filter|annee|body_normalisé)` ; si une ligne `DATA_SAVING_LOGS` récente (< N min, configurable) porte la même empreinte avec `OKSAVE`, répondre immédiatement `OKSAVE` avec `se_ack.duplicate=true` **sans** rappeler `questionnaire_ws.php`. Nécessite 1 colonne `SYNC_HASH` (migration ALTER, nullable). Réduit la charge et neutralise le double-tap.
2. Mobile : garde globale `SyncLock` (un seul envoi à la fois, tous écrans) + désactivation du bouton pendant l'envoi.
3. Journal : conserver une ligne par tentative (utile en audit) mais ajouter `NB_INSERT/NB_UPDATE/NB_DELETE` pour distinguer « re-soumission sans changement » (voir point 6).

Effort : **faible à moyen**. Risque : **faible** (court-circuit uniquement sur empreinte strictement identique + statut OKSAVE ; désactivable par paramètre).

---

### Point 5 — Rejet / remplacement / mise à jour / doublons : clé d'unicité et idempotence

**(a) État actuel**
- **Clé d'unicité serveur** : PK des tables mères = `CODE_ETABLISSEMENT + CODE_TYPE_ANNEE [+ CODE_TYPE_PERIODE] + dimensions du thème`. Elle est *implicite* dans `grille.class.php` (`tab_keys_zones`), non déclarée comme « clé de soumission ». Le thème (`ID_THEME`) n'en fait pas partie : deux thèmes écrivant dans la même table partagent les lignes (c'est le design SISED).
- **Mode** : *remplacement différentiel* (`U/I/D`) — ce n'est **pas** un upsert pur : les lignes absentes du POST sont **supprimées**.
- Mobile : la clé locale `(année, camp, etab, qst, filtre, champ)` correspond bien à `(établissement + année + formulaire [+ période])`.
- Risque d'écrasement involontaire identifié :
  - `reloadFromServer()` : `deleteCollectedData` + `saveCollectedData(serveur)` ⇒ écrase des saisies locales non envoyées **après confirmation utilisateur** (OK).
  - `_autoReloadFromServerBackground(forceOverwrite: true)` pour le **formulaire d'identification** : `saveCollectedData(serverStr)` **remet `is_sent=0`** sur des données *venant du serveur* et écrase silencieusement en SQLite des modifications locales non envoyées de ce formulaire (la fusion en mémoire ne protège que `_formData`, pas SQLite).
  - Renvoi batch d'un formulaire dont les données locales sont **partielles** (ex. matrice chargée partiellement, ou `li_ch` filtrés) ⇒ côté serveur les lignes manquantes deviennent `D` ⇒ **suppression de données valides en base**. C'est le comportement natif du web SISED, mais amplifié par le batch mobile qui envoie sans que l'agent revoie le formulaire.
  - `reloadData()` ne transmet **pas** `id_annee` : le serveur utilise `$_SESSION['annee']` (init depuis `PARAM_DEFAUT`) — en mode pluriannuel, la relecture peut cibler une autre année que celle du mobile si la session WS persiste (`common_ws.php` l. 117-123).
- Pas d'en-tête/ID d'idempotence ; la réponse ne contient aucun identifiant de soumission.

**(b) Écarts / limites**
- L'idempotence est **de fait** (diff par clés) mais **non explicite** et **non protectrice** contre le POST partiel.
- Incohérence d'année possible sur `reloadData` (pas de segment année alors que `saveData` l'a).

**(c) Mise à niveau proposée**
1. **Formaliser la clé de soumission** : `SUBMISSION_KEY = (CODE_ANNEE, ID_CAMPAGNE, ID_THEME_SYSTEME, CODE_ETABLISSEMENT, CODE_FILTRE)` — documentée et utilisée par `DATA_SAVING_LOGS`, `form_sync_status` et l'empreinte du point 4.
2. **Identifiant d'idempotence** : le mobile génère `sync_id` (UUID v4) par tentative logique (même UUID pour les retries), transmis en en-tête `X-Sync-Id` et/ou paramètre POST `_sync_id`; stocké dans `DATA_SAVING_LOGS.SYNC_ID`; si déjà présent avec `OKSAVE` ⇒ réponse `OKSAVE` immédiate. Élimine les faux échecs « réponse perdue puis renvoi ».
3. **Garde anti-suppression** (paramètre `PARAM['MOBILE_NO_DELETE_ON_PARTIAL']`, défaut `false` = comportement inchangé) : pour les appels mobiles (`$GLOBALS['ne_pas_verifier_session']`), si le POST contient strictement moins de lignes que la BD pour une table mère, convertir les `D` en `R` (ne rien supprimer) et renvoyer un avertissement dans l'ACK. À valider métier : cela empêche la *suppression volontaire* de lignes depuis le mobile.
4. `reloadData()` : ajouter le segment `/:id_annee` (nouvelle route Slim **en plus** de l'existante, comme fait pour `theme_save`) et le propager à `questionnaire_reload_ws.php` (`&annee=`).
5. `_autoReloadFromServerBackground` : ne pas écraser en SQLite les lignes `is_sent=0` modifiées localement (`updated_at` > dernier envoi) ; marquer les lignes serveur `is_sent=1`.

Effort : 1-2 **faible**, 3 **moyen**, 4-5 **faible**. Risque : 1-2-4-5 **faible** (additifs) ; 3 **moyen** (changement de sémantique ⇒ derrière un paramètre, désactivé par défaut).

---

## 3. Analyse détaillée — Partie B (serveur / administrateur)

### Point 6 — Notification / traçabilité de bonne réception au niveau central

**(a) État actuel**
- **Accusé de réception** : JSON `{se_status:200, se_message:'ok', se_data:'OKSAVE'|'KOSAVE'}`. Aucun identifiant, aucun horodatage serveur, aucun décompte de lignes écrites, aucun rappel de la clé (etab/thème/année).
- **Horodatage** : `DATA_SAVING_LOGS.LOG_DATE_TIME` (`date('Y/m/d H:i:s')` PHP, stocké en chaîne) ; `moblogs/<login>.log` (rotation à 10 Mo). `DICO_TRACE.DATE_SAISIE` au format `d/m/Y H:i:s` (chaîne, non triable en SQL).
- **Journal des transmissions** : `DATA_SAVING_LOGS` couvre `OKSAVE`, `KOSAVE`, `KO` (erreur cURL). **Non journalisés** : rejets anticipés de `theme_save_handler` (année incorrecte, accès refusé, campagne fermée) — `return` avant `saveLogInfo()`. Pas de colonne pour le message d'erreur, ni pour l'IP/appareil/version APK, ni pour le nombre d'actions I/U/D.
- `CODE_PERIODE`/`CODE_FILTRE` reçoivent la **chaîne `null`** quand pas de filtre : `saveLogInfo()` concatène `$id_filter` sans quote ⇒ SQL `…, null, …` ⇒ valeur `NULL` en base. Toutes les requêtes de suivi (`CODE_PERIODE=".$_SESSION['filtre']`, `CODE_PERIODE=".$id_period`) filtrent sur un entier ⇒ **les envois mobiles sans période ne remontent jamais** dans les écrans qui filtrent par période.
- **Notification** : aucune (ni e-mail, ni SMS, ni push) déclenchée à la réception. `remind_collectors.php` sait envoyer un mail mais : non branché au menu, SMTP/identifiants **codés en dur** (l. 182-186), texte anglais fixe, dépend de PEAR `Mail.php`.
- Côté mobile, il n'existe aucun moyen de **consulter** l'accusé (rien n'est stocké).

**(b) Écarts / limites**
- Impossible de prouver *quand* et *quoi* a été reçu pour un formulaire donné (pas d'ID de soumission ; l'ACK n'est pas persisté côté mobile).
- Rejets métier invisibles dans le journal central.
- Journal inexploitable par période pour les envois mobiles sans filtre (NULL).

**(c) Mise à niveau proposée**
1. **ACK enrichi rétro-compatible** : conserver `se_data='OKSAVE'|'KOSAVE'` et **ajouter** `se_ack: { sync_id, server_time (ISO 8601), code_annee, id_camp, id_theme, code_etab, code_filtre, nb_insert, nb_update, nb_delete, nb_unchanged, duplicate }`. Les compteurs proviennent de `maj_bdd()` (ajout de 4 compteurs `$GLOBALS['maj_stats']`, sans changer la logique) émis par `questionnaire_ws.php` sous forme d'un marqueur parsable `MAJ_STATS={"i":..,"u":..,"d":..,"r":..}` à côté de `MAJ_OK`. Le mobile stocke `se_ack` dans `form_sync_status.server_ack` et l'affiche (« Reçu le … à … — 12 lignes mises à jour »).
2. **`DATA_SAVING_LOGS`** : migration additive `ALTER TABLE ADD` : `SYNC_ID VARCHAR(36) NULL`, `SYNC_HASH VARCHAR(40) NULL`, `NB_INSERT/NB_UPDATE/NB_DELETE INT NULL`, `MESSAGE_ERREUR VARCHAR(500) NULL`, `APP_VERSION VARCHAR(20) NULL`, `IP_CLIENT VARCHAR(45) NULL`. Index sur `(CODE_CAMPAGNE, CODE_ANNEE, CODE_ECOLE, ID_THEME_SYSTEME)` et sur `SYNC_ID`.
3. Journaliser aussi les **rejets anticipés** (`REJET_ANNEE`, `REJET_ACCES`, `REJET_CAMP_FERMEE`) via `saveLogInfo()` avant chaque `return`.
4. Normaliser `CODE_PERIODE`/`CODE_FILTRE` : écrire `NULL` (déjà le cas) **et** faire évoluer les requêtes de suivi pour utiliser `(CODE_PERIODE = ? OR CODE_PERIODE IS NULL)` quand la campagne n'a pas de période (voir point 8).
5. Optionnel : e-mail/SMS de confirmation au directeur — réutiliser `remind_collectors.php` après externalisation des identifiants SMTP dans `params.php` (`PARAM['MAIL_*']` existent déjà dans `ctrl_collectors_feedbacks.php`).

Effort : 1-2-3 **moyen** ; 4 **faible** ; 5 **moyen**. Risque : **faible** (tout est additif : nouvelles colonnes nullable, nouvelles clés JSON ignorées par les anciens clients).

---

### Point 7 — Outils / écrans de suivi des synchronisations par établissement

**(a) État actuel**
- **`administration.php?val=suivi_saisie`** (menu « Suivi Opérateurs Saisie ») → `suivi_saisie_criteres.php` + `suivi_saisie_list_etabs.php`. Orienté **opérateur** (utilisateur × critères de suivi `DICO_SUIVI_SAISIE_REGLE`), pas établissement. Depuis S51, les agents `CODE_GROUPE=4` sont branchés sur `DATA_SAVING_LOGS` (statut `OKSAVE` uniquement) ; le filtre `CODE_PERIODE=".$_SESSION['filtre']` exclut les envois sans période (cf. point 6).
- **`val=feedbacks`** → `ctrl_collectors_feedbacks.php` : le plus proche d'un tableau de bord (campagne × période × traitement : rappel / confirmation réception / validation / rejet). **Absent du menu**, encodage ISO cassé (`libell�`), dépend de `DICO_FIXE_REGROUPEMENT.ID_PERIODE`, envoie des mails.
- **`val=controle_val`** → `controle_validation.php` : compare deux campagnes (standard vs validation, thèmes 401/901…) — logique **codée en dur** pour un autre pays (Niger), non applicable telle quelle.
- **`moblogs/`** : fichiers plats consultables uniquement par accès disque.
- **Aucun écran** ne liste : établissement × thème × (dernière réception, statut, agent), ni les `KOSAVE`/`KO` en erreur, ni le contenu de `DATA_SAVING_LOGS` brut.

**(b) Écarts / limites**
- L'administrateur n'a pas de vue « établissement » ; les écrans existants sont soit orientés opérateur, soit orphelins/inadaptés.
- Les échecs (KOSAVE/KO) ne sont visibles nulle part dans l'interface.

**(c) Mise à niveau proposée**
1. **Nouvel écran « Suivi synchronisation mobile »** (`administration.php?val=suivi_sync_mobile` → `server-side/include/administration/suivi_sync_mobile.php`, PHP natif + AJAX/JS existant, style `suivi_saisie`) :
   - filtres : année (défaut `$_SESSION['annee']`), campagne, secteur, chaîne/regroupement (arbre existant), période (optionnelle), agent ;
   - matrice **établissement × thème** avec cellule = statut agrégé (✅ dernier OKSAVE, ⚠️ dernier KOSAVE/KO, ⬜ jamais reçu) + date + agent ; tri/export CSV ;
   - détail établissement : historique `DATA_SAVING_LOGS` (toutes tentatives) + lien vers `questionnaire.php?theme=&code_etab=&type_ent_stat=&filtre=` pour ouvrir la saisie web.
   - Source unique : `DATA_SAVING_LOGS` (+ colonnes du point 6). Aucune écriture.
2. Entrée de menu dans `client-side/js/menus/*/_menu_princ.js` sous « Suivi » ; `case 'suivi_sync_mobile'` dans `administration.php`.
3. Endpoint REST **lecture** `GET sync_status_ws.php/etab/:login/:id_camp/:id_etab/:id_annee` (retourne pour chaque thème le dernier statut serveur) — utilisé par le mobile pour **réconcilier** son `form_sync_status` avec la vérité serveur (résout le cas « réponse perdue »).

Effort : 1-2 **élevé** (écran complet) ; 3 **faible**. Risque : **faible** (lecture seule, fichiers nouveaux, une ligne de menu).

---

### Point 8 — Indicateurs complet / partiel / rien transmis

**(a) État actuel**
- Logique existante uniquement dans `remind_collectors.php` (l. 218-241) et `ctrl_collectors_feedbacks.php` : *complet* = `COUNT(DISTINCT ID_THEME_SYSTEME OKSAVE) == nb thèmes de la campagne` ; *partiel* = strictement inférieur ; *rien* = absent de `DATA_SAVING_LOGS`. Non affichée à l'écran (uniquement dans le corps d'un mail), filtrée par `CODE_PERIODE=N`.
- `get_camp_themes()` compte tous les thèmes `FRAME <> ''` de la campagne, y compris ceux qui ne s'appliquent pas à l'établissement (ex. thèmes conditionnels) ⇒ biais « partiel » possible.
- Aucun indicateur de **complétude du contenu** (formulaire reçu mais vide) : `DATA_SAVING_LOGS` ne dit pas combien de lignes ont été écrites.
- Mobile : aucun indicateur par école.

**(b) Écarts / limites**
- Indicateurs non exposés, non fiables sur les campagnes sans période, insensibles au contenu.

**(c) Mise à niveau proposée**
1. Vue/requête serveur centralisée (fonction PHP `get_etab_sync_summary($camp,$annee,$secteur,$periode|null)`) retournant par établissement : `nb_themes_attendus`, `nb_themes_ok`, `nb_themes_ko`, `derniere_reception`, `statut` ∈ {`COMPLET`,`PARTIEL`,`ERREUR`,`AUCUN`}. Gestion `CODE_PERIODE IS NULL`. Base des cellules de l'écran du point 7.
2. Complétude de contenu : exploiter `NB_INSERT+NB_UPDATE+NB_UNCHANGED` (point 6) ⇒ un `OKSAVE` avec 0 ligne ⇒ statut `VIDE`.
3. Liste des thèmes attendus par établissement : réutiliser `theme_manager->charger_theme($camp,$sector)` (source de vérité déjà utilisée par `data_save.php`) plutôt que la requête ad hoc de `remind_collectors.php`.
4. Mobile : agrégat identique calculé depuis `form_sync_status` (voir point 1) sur `_SchoolTile`.

Effort : **moyen**. Risque : **faible** (lecture seule).

---

### Point 9 — Procédure actuelle sur données incomplètes ; rôle de l'administrateur

**(a) État actuel**
- **Aucune procédure outillée.** L'administrateur peut : (i) ouvrir `questionnaire.php` en web et saisir/corriger lui-même (les données seront écrasées au prochain envoi mobile de l'agent si celui-ci renvoie ses données locales — cf. point 5 `D`) ; (ii) lancer `controle_coherence` ; (iii) contacter l'agent hors système (téléphone).
- `ctrl_collectors_feedbacks.php` prévoit 4 « traitements » (rappel, confirmation réception, confirmation validation, rejet) mais seul le traitement 1 (rappel) a du code exécutable, et il n'est pas branché.
- Le contrôle de campagne fermée est **neutralisé** (`data_save.php` l. 330 : `$survey_curr_status = 2;`) ⇒ un agent peut envoyer à tout moment, y compris après validation centrale.
- Aucun verrou de formulaire/établissement côté mobile ; `$_SESSION['list_themes_desact']` (verrou web) n'est pas propagé aux WS.

**(b) Écarts / limites**
- Pas de statut de traitement central (« reçu », « à corriger », « validé »), pas de journal des décisions admin, pas de protection des données validées contre un renvoi mobile.

**(c) Mise à niveau proposée**
1. **Table `SYNC_ETAB_STATUT`** (dico) : `(CODE_ANNEE, ID_CAMPAGNE, CODE_ETABLISSEMENT, ID_THEME_SYSTEME NULL, CODE_FILTRE NULL)` + `STATUT` ∈ {`RECU`,`A_CORRIGER`,`VALIDE`,`VERROUILLE`} + `COMMENTAIRE`, `CODE_USER_ADMIN`, `DATE_STATUT`. Réutiliser `THEME_VALIDATION`/`TYPE_VALIDATION` **si** leur structure est confirmée (voir §6 informations à confirmer) — sinon nouvelle table.
2. Actions admin dans l'écran du point 7 : *Marquer à corriger* (+ commentaire obligatoire), *Valider*, *Verrouiller*, *Rouvrir*.
3. `data_save.php` : si `STATUT IN ('VALIDE','VERROUILLE')` pour la clé de soumission ⇒ réponse `se_status=400, se_data='FORM_LOCKED'` + journal `REJET_VERROU`. Mobile ⇒ statut `rejected` + message explicite.
4. Rétablir le contrôle de campagne derrière un paramètre `PARAM['MOBILE_CHECK_SURVEY_STATUS']` (défaut `false` = comportement actuel conservé) plutôt que le `= 2` en dur.

Effort : **élevé** (table + écran + WS). Risque : **moyen** (point 3 introduit un nouveau motif de rejet — uniquement si un admin a explicitement verrouillé ; sinon comportement identique).

---

### Point 10 — Modalités de demande de correction / complément auprès de l'utilisateur

**(a) État actuel**
- Aucun canal in-app. Le mobile ne consulte jamais le serveur pour des messages/consignes. `THEME_VALIDATION.COMMENTAIRES` est lu par `sms_sync.php` (canal SMS historique, non utilisé par Flutter) et par le mailer.
- `ADMIN_USERS.EMAIL_USER`, `TEL_USER` existent → un canal e-mail/SMS est théoriquement possible mais non opérationnel (SMTP en dur, PEAR Mail).

**(b) Écarts / limites**
- La demande de correction est hors système, non tracée, non reliée au formulaire concerné.

**(c) Mise à niveau proposée**
1. Endpoint **lecture** `GET sync_status_ws.php/feedback/:login/:id_camp/:id_annee` ⇒ liste des `SYNC_ETAB_STATUT` avec `STATUT='A_CORRIGER'` pour les établissements de l'agent (périmètre via `DICO_FIXE_REGROUPEMENT` + fallback `ADMIN_USERS`, même logique que `data_save.php`).
2. Mobile : à l'ouverture d'une campagne (et via « Actualiser »), appel de cet endpoint ⇒ badge « Correction demandée » sur `_SchoolTile` et bannière rouge dans `school_data_screen` avec le commentaire admin ; stockage local `sync_feedback` (SQLite) pour lecture hors ligne.
3. Après renvoi `OKSAVE` d'un formulaire `A_CORRIGER` ⇒ `data_save.php` passe automatiquement le statut à `RECU` (et journalise) — l'admin revoit dans l'écran du point 7.
4. Optionnel : notification e-mail/SMS à la création d'un `A_CORRIGER` (réutilisation `remind_collectors.php` paramétrée).

Effort : **moyen**. Risque : **faible** (lecture + affichage ; aucun impact sur la sauvegarde).

---

### Point 11 — Renvoi / réouverture d'un formulaire pour correction

**(a) État actuel**
- **Serveur** : rien n'empêche techniquement un renvoi (campagne toujours « ouverte », pas de verrou). Il n'y a donc rien à « rouvrir » — mais rien à protéger non plus.
- **Mobile** : « Recharger depuis serveur » (`reloadFromServer`) remplace les données locales par celles du serveur puis `is_sent=1` ; l'agent modifie et renvoie ⇒ `U/I/D` côté serveur. C'est le seul flux de correction, à l'initiative de l'agent.
- L'admin peut corriger en web ; le mobile ne le saura pas (pas de détection de modification serveur ; `_autoReloadFromServerBackground` ne recharge que si données locales vides ou formulaire d'identification).

**(b) Écarts / limites**
- Pas de cycle *verrouiller → rouvrir → resynchroniser* ; risque d'écrasement croisé admin ↔ agent sans avertissement.

**(c) Mise à niveau proposée**
1. Cycle de vie complet via `SYNC_ETAB_STATUT` (point 9) : `VALIDE/VERROUILLE` bloque, `Rouvrir` remet `A_CORRIGER` ou `RECU`.
2. Détection de conflit côté mobile : le `sync_status_ws` renvoie `derniere_maj_serveur` (max `DICO_TRACE.DATE_SAISIE` ou `DATA_SAVING_LOGS.LOG_DATE_TIME` pour la clé) ; si > `form_sync_status.last_success_at` et que des modifications locales existent ⇒ dialogue « Le serveur a été modifié depuis votre dernier envoi : Recharger / Écraser / Annuler » avant l'envoi. Défaut sûr = *Recharger*.
3. Message serveur `FORM_LOCKED` ⇒ le mobile propose directement « Recharger depuis le serveur » (données validées) et passe le formulaire en lecture seule locale (drapeau `locked` dans `form_sync_status`, réversible à la prochaine réconciliation).

Effort : **moyen à élevé**. Risque : **moyen** (introduit des dialogues bloquants ; à limiter au cas conflit détecté ; comportement inchangé si le serveur ne renvoie pas `derniere_maj_serveur`).

---

## 4. Liste consolidée et priorisée des mises à niveau (à valider point par point)

Priorité : **P1** = intégrité / perte de données ou visibilité critique ; **P2** = suivi et pilotage ; **P3** = confort / optimisation.
Effort : F = faible (< 1 j), M = moyen (1-3 j), E = élevé (> 3 j). Risque de régression : F / M.

| N° | Prio | Module | Description | Bénéfice attendu | Effort | Risque | Dépend de |
|---|---|---|---|---|---|---|---|
| **U01** | P1 | Mobile | `_saveDataOnce` : réponse vide / non-JSON ⇒ `ApiException` au lieu de succès | Supprime les faux « envoyé » (page d'erreur HTML comptée comme succès) | F | F | — |
| **U02** | P1 | Mobile | KOSAVE ⇒ **ne plus** appeler `markCollectedDataSent` ; nouveau statut `kosave` persistant | L'agent voit que le serveur n'a rien enregistré et peut renvoyer | F | F | U04 |
| **U03** | P1 | Mobile | Batchs (`sendAllFormsForSchool/Campaign`) : itérer sur `(etab, qst, id_filter)`, transmettre `filter`, `etabRegroupId`, `isFirstQuestion` (alignement sur `sendToServer`) | Les formulaires à période sont enfin inclus dans « Envoyer tout » ; `LOC_REG_0` cohérent | M | F-M | — |
| **U04** | P1 | Mobile | Table SQLite `form_sync_status` (v9, additive) + alimentation depuis les 5 points d'envoi/rechargement ; `is_sent` conservé | Base de tout l'affichage d'état et du renvoi sélectif | M | F | — |
| **U05** | P1 | Mobile | Indicateurs UI : pastille statut sur chips thème, agrégat par école sur `_SchoolTile`, remplacement du placeholder `_UnsentDataBanner` | Réponse directe aux questions 1 et 8 côté agent | M | F | U04 |
| **U06** | P1 | Mobile | Résultat d'envoi typé (`SendOutcome`) + dialogue listant nommément les échecs + bouton « Réessayer les échecs » | Identification et relance ciblée après échec partiel | M | F | U04 |
| **U07** | P1 | Mobile | Action « Envoyer uniquement les formulaires en attente » (école + campagne) via `getPendingForms()` ; « Envoyer tout » conservé | Renvoi sélectif, moins de charge serveur, pas de journal gonflé | F | F | U03, U04 |
| **U08** | P1 | Serveur | ACK enrichi rétro-compatible `se_ack{sync_id, server_time, clé, nb_insert/update/delete/unchanged, duplicate}` + marqueur `MAJ_STATS=` dans `questionnaire_ws.php` (compteurs dans `maj_bdd()`, logique inchangée) | Accusé de réception exploitable, complétude de contenu | M | F | — |
| **U09** | P1 | Serveur | Migration additive `DATA_SAVING_LOGS` : `SYNC_ID`, `SYNC_HASH`, `NB_*`, `MESSAGE_ERREUR`, `APP_VERSION`, `IP_CLIENT` + index | Journal central complet et requêtable | F | F | — |
| **U10** | P1 | Serveur | Journaliser les rejets anticipés (`REJET_ANNEE`, `REJET_ACCES`, `REJET_CAMP_FERMEE`) via `saveLogInfo()` | Aucun envoi refusé n'est invisible | F | F | U09 |
| **U11** | P1 | Serveur+Mobile | Idempotence explicite : `X-Sync-Id` (UUID par tentative logique, conservé sur retry) ⇒ si déjà `OKSAVE` ⇒ réponse immédiate `duplicate=true` sans cURL | Résout « réponse perdue → renvoi » ; neutralise le double-tap ; économise le serveur | M | F | U08, U09 |
| **U12** | P1 | Mobile | `_autoReloadFromServerBackground` : ne pas remettre `is_sent=0` ni écraser en SQLite des champs modifiés localement non envoyés | Évite l'écrasement silencieux de saisies (formulaire d'identification) | F | F | U04 |
| **U13** | P1 | Serveur+Mobile | `reloadData` : route additionnelle `/:id_annee` propagée à `questionnaire_reload_ws.php` | Cohérence année mobile/serveur sur la relecture (pluriannuel) | F | F | — |
| **U14** | P2 | Serveur | Écran admin « Suivi synchronisation mobile » (matrice établissement × thème, filtres, détail historique, export CSV, lien saisie web) + entrée menu + `case` dans `administration.php` | Réponse aux questions 7, 8, 9 pour l'administrateur | E | F | U09 |
| **U15** | P2 | Serveur | Fonction `get_etab_sync_summary()` : statut `COMPLET/PARTIEL/ERREUR/VIDE/AUCUN` par établissement, gestion `CODE_PERIODE IS NULL`, thèmes attendus via `theme_manager` | Indicateurs fiables, réutilisables (écran, WS, mails) | M | F | U08, U09 |
| **U16** | P2 | Serveur | WS lecture `sync_status_ws.php/etab/...` (dernier statut serveur par thème) | Réconciliation mobile ↔ serveur | F | F | U15 |
| **U17** | P2 | Mobile | Réconciliation à l'ouverture de campagne : `form_sync_status` ← vérité serveur (`sent` si OKSAVE serveur plus récent) | Corrige les faux « en attente » après réponse perdue | F | F | U04, U16 |
| **U18** | P2 | Serveur | Table `SYNC_ETAB_STATUT` (`RECU/A_CORRIGER/VALIDE/VERROUILLE` + commentaire, admin, date) ou réutilisation `THEME_VALIDATION` si structure confirmée ; actions admin dans U14 | Procédure outillée de traitement des données incomplètes | M | F | U14 |
| **U19** | P2 | Serveur+Mobile | WS `sync_status_ws.php/feedback/...` + badge/bannière « Correction demandée » + cache local | Demande de correction in-app, tracée | M | F | U18 |
| **U20** | P2 | Serveur | `data_save.php` : rejet `FORM_LOCKED` si `VALIDE/VERROUILLE` ; retour auto `A_CORRIGER → RECU` après OKSAVE | Protection des données validées ; boucle de correction fermée | F | M | U18 |
| **U21** | P2 | Mobile | Détection de conflit avant envoi (`derniere_maj_serveur` > dernier envoi local + modifs locales) ⇒ dialogue Recharger / Écraser / Annuler ; gestion `FORM_LOCKED` (lecture seule locale) | Évite l'écrasement croisé admin ↔ agent | M | M | U16, U20 |
| **U22** | P2 | Serveur | Empreinte `SYNC_HASH` : court-circuit `OKSAVE duplicate` si POST strictement identique récent | Complément de U11 pour les anciens clients / renvois « Envoyer tout » | F | F | U09 |
| **U23** | P3 | Serveur | Paramètre `PARAM['MOBILE_CHECK_SURVEY_STATUS']` (défaut `false`) remplaçant le `$survey_curr_status = 2;` en dur | Rétablir le contrôle de campagne fermée sans changer le comportement actuel | F | F | — |
| **U24** | P3 | Serveur | Garde anti-suppression `PARAM['MOBILE_NO_DELETE_ON_PARTIAL']` (défaut `false`) : `D → R` sur POST mobile partiel + avertissement dans l'ACK | Protège les données serveur d'un renvoi local partiel | M | M | U08 |
| **U25** | P3 | Mobile | Verrou global `SyncLock` (un seul envoi simultané, tous écrans) | Évite KOSAVE par collision de deux envois parallèles | F | F | — |
| **U26** | P3 | Serveur | Externaliser SMTP dans `params.php`, brancher `feedbacks`/`remind_collectors` au menu ou les déprécier explicitement | Canal e-mail utilisable ou nettoyage | M | F | U15 |
| **U27** | P3 | Serveur | Requêtes de suivi existantes (`suivi_saisie_list_etabs`, `ctrl_collectors_feedbacks`) : `CODE_PERIODE = ? OR CODE_PERIODE IS NULL` | Les envois mobiles sans période remontent dans les écrans actuels | F | F | — |

**Lot recommandé pour une première itération (validation attendue)** : U01, U02, U03, U04, U05, U06, U07, U08, U09, U10, U11, U12, U13 (P1) — puis U14 → U17 (P2 suivi), puis U18 → U21 (P2 correction).

---

## 5. Stratégie de non-régression

### 5.1 Principes
1. **Additif avant tout** : nouvelles tables/colonnes nullable, nouvelles routes Slim *en plus* des existantes, nouvelles clés JSON *en plus* de `se_status/se_data`. Aucune suppression, aucun renommage.
2. **Comportement par défaut inchangé** pour tout ce qui modifie une sémantique (U20, U23, U24) : derrière un paramètre `PARAM[...]` à `false`, ou déclenché uniquement par une action admin explicite.
3. **Aucune écriture nouvelle dans les tables métier SISED** : toute la traçabilité vit dans la base *dico* (`DATA_SAVING_LOGS`, `SYNC_ETAB_STATUT`) et en SQLite.
4. **Migrations, jamais de DROP/RECREATE** (règle 6 du skill) : SQLite v8 → v9 via `onUpgrade` idempotent (`CREATE TABLE IF NOT EXISTS`, `try/catch` sur `ALTER`) ; SQL Server/Access via scripts `ALTER TABLE … ADD` livrés dans `server-side/sql/` et référencés dans `MANUEL_MIGRATION`.
5. **Compatibilité croisée des versions** : un ancien APK doit fonctionner avec le nouveau serveur (il ignore `se_ack`, n'envoie pas `X-Sync-Id` ⇒ chemin actuel) ; un nouvel APK doit fonctionner avec l'ancien serveur (absence de `se_ack` ⇒ statut `sent` sans détails ; 404 sur `sync_status_ws.php` ⇒ réconciliation ignorée, fail-open).

### 5.2 Garanties par fonctionnalité existante

| Fonctionnalité | Ce qui est préservé | Comment on le vérifie |
|---|---|---|
| Gestion pluriannuelle `CODE_TYPE_ANNEE` | `collected_data.code_type_annee` et l'index unique v8 intacts ; `form_sync_status` porte la même clé ; U13 ajoute une route, n'en modifie aucune | Test : saisie sur année N et N-1 sur le même établissement ⇒ deux statuts distincts, envoi bloqué si mismatch |
| Contrôle année mobile/serveur (`_checkYearConsistency` fail-open + AK-YEAR-CHECK serveur) | Non touché ; U10 ajoute seulement une ligne de journal au rejet | Test : mismatch confirmé ⇒ 400 + `REJET_ANNEE` journalisé ; réseau KO ⇒ envoi tenté |
| Mode hors ligne | `saveLocally`, `getCollectedData`, moteurs de cohérence offline (SAVEPOINT) inchangés ; `form_sync_status` mis à jour localement sans réseau ; WS de réconciliation/feedback en fail-open | Test : mode avion → saisie/sauvegarde/cohérence → retour réseau → « Envoyer en attente » |
| Import Excel des utilisateurs (`gestion_user.php`, PhpSpreadsheet) | Hors périmètre, aucun fichier commun modifié | Smoke test import d'un fichier de 3 utilisateurs |
| Chaîne cURL interne `data_save → questionnaire_ws` (ports, Host, session guards, `&filtre=`) | Non modifiée ; `MAJ_STATS=` est émis à côté de `MAJ_OK=` (déjà parsé par la recherche de tokens) | Test : OKSAVE sur thème sans filtre (10502) et avec filtre |
| Logique `comparer()/maj_bdd()` | Ajout de compteurs uniquement ; U24 (D→R) inactif par défaut | Test : renvoi identique ⇒ `nb_unchanged = n`, 0 requête SQL |
| Saisie web classique (`questionnaire.php`) | `ne_pas_verifier_session` reste le seul discriminant ; compteurs sans effet sur le HTML | Test : saisie/modif/suppression d'une ligne en web |
| Écrans admin existants (`suivi_saisie`, `controle`) | Nouveaux fichiers + une entrée menu ; U27 élargit un `WHERE` sans retirer de résultats | Comparaison avant/après sur un jeu de logs |
| Retry Dio, encodage login, `li_ch`, `_slh_`, radios | `_saveDataOnce` : seule la branche « réponse vide/non-JSON » change (U01) | Tests unitaires existants `test/` + cas réponse vide |

### 5.3 Plan de vérification
- **Tests unitaires Dart** (répertoire `test/` existant) : `form_sync_status` transitions ; `getPendingForms` avec/sans filtre ; parsing `se_ack` présent/absent ; `_saveDataOnce` sur réponse vide, HTML, JSON KOSAVE, JSON 400.
- **Scénarios manuels** (à dérouler sur le serveur de test avant tout merge) : (1) envoi nominal ; (2) coupure réseau après réception serveur puis renvoi ⇒ `duplicate=true`, statut `sent`, pas de doublon ; (3) KOSAVE provoqué ⇒ statut `kosave`, renvoi possible ; (4) « Envoyer tout » sur école avec thème périodique ; (5) admin marque `A_CORRIGER` ⇒ badge mobile ⇒ renvoi ⇒ `RECU` ; (6) admin `VERROUILLE` ⇒ `FORM_LOCKED` ⇒ recharge lecture seule.
- **Déploiement** : serveur d'abord (rétro-compatible), puis APK ; scripts SQL exécutés avant le déploiement PHP ; sauvegarde de la base dico.

---

## 6. Éléments à confirmer avant finalisation (pas d'hypothèse posée)

| # | Élément | Pourquoi | Où je n'ai pas trouvé l'information |
|---|---|---|---|
| C1 | **Structure exacte de `DATA_SAVING_LOGS`** (types, PK/identity, longueur `CODE_USER`, type de `LOG_DATE_TIME` : `VARCHAR` ou `DATETIME` ?) | Migration U09, tri par date, index | Aucun DDL dans `server-side/sql/` ni dans les docs |
| C2 | **Structure de `THEME_VALIDATION` et `TYPE_VALIDATION`** + valeurs de `CODE_TYPE_VALIDATION` (1,2,3 = ?) | Décider U18 : réutiliser ou créer `SYNC_ETAB_STATUT` | Seules des lectures dans `sms_sync.php` / `ctrl_collectors_feedbacks.php` ; aucune écriture, aucun DDL |
| C3 | **SGBD de production effectif** : SQL Server 2012 (README) ou Access (skill) ? Base *dico* et base *données* sur le même moteur ? | Syntaxe des `ALTER TABLE`, index, `ISNULL/IIF`, transactions | README dit SQL Server ; `skill-stateduc-mobile` et `connexion.class.php` évoquent Access ; commit S22 mentionne « conn SQL Server » |
| C4 | Version PHP réelle en production (**7.3** selon consigne ; le README indique 7.4+) | Syntaxe autorisée (pas de typed properties, pas de `??=`…) | À confirmer |
| C5 | Le champ `ID_PERIODE` de `DICO_FIXE_REGROUPEMENT` est-il renseigné pour les agents mobiles Burundi ? Les campagnes Burundi utilisent-elles des périodes (`TYPE_FILTRE`) ? | Détermine si U27 et la gestion `NULL` sont bloquantes ou secondaires | `data_save.php` a un fallback ADMIN_USERS, ce qui suggère que non |
| C6 | Existe-t-il des thèmes **conditionnels** (non attendus pour certains établissements) dans les campagnes mobiles Burundi ? | Fiabilité de l'indicateur COMPLET/PARTIEL (U15) | À confirmer métier |
| C7 | Politique métier souhaitée sur la **suppression** de lignes depuis le mobile (grilles enseignants, classes) : doit-elle rester possible ? | Décision sur U24 (garde `D → R`) | Choix métier |
| C8 | Le canal **e-mail/SMS** est-il souhaité (serveur SMTP MEN disponible ?) ou l'in-app (U19) suffit-il ? | Périmètre U26 | Identifiants OVH en dur dans `remind_collectors.php` = non réutilisables |
| C9 | Le sous-module `stateduc_mobile/` et `app_fie/` (non suivis dans `ak_secure`) sont-ils hors périmètre de cette mission ? | Éviter toute modification hors cible | `git status` les montre *untracked* |
| C10 | Les agents mobiles sont-ils tous `CODE_GROUPE = 4` ? | Filtres des écrans admin (S51 s'appuie sur cette convention) | Convention observée dans `suivi_saisie_list_etabs.php` uniquement |

---

*Fin du rapport. Aucune ligne de code applicatif n'a été modifiée ; ce document est le seul fichier ajouté (`docs/session_sync/`).*
