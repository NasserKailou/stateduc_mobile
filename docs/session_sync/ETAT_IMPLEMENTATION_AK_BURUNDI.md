# État d'implémentation — branche `ak_burundi` (U01–U27)

`ak_secure` est **intacte** (dernier commit `37c63e2` = rapport de diagnostic). Tout le travail est sur `ak_burundi`.

## ✅ Serveur (`StatEduc_burundi`) — TERMINÉ (lint PHP OK)

| U | Fichier(s) | Contenu |
|---|---|---|
| U08 | `data_save.php`, `questionnaire_ws.php`, `grille.class.php` | ACK enrichi `se_ack` (sync_id, server_time, clé, nb_insert/update/delete/unchanged, duplicate, warning), compteurs `MAJ_STATS=` émis par `maj_bdd()`/`comparer()` |
| U09 | `server-side/sql/migration_sync_tracabilite_{sqlserver,access}.sql` | Colonnes `DATA_SAVING_LOGS` (SYNC_ID, SYNC_HASH, NB_*, MESSAGE_ERREUR, APP_VERSION, IP_CLIENT) + index |
| U10 | `data_save.php` | Rejets journalisés : `REJET_ANNEE`, `REJET_ACCES`, `REJET_CAMP_FERMEE`, `REJET_VERROU`, `DUPLICATE` |
| U11/U22 | `sync_lib.php`, `data_save.php` | Idempotence `X-Sync-Id` (ou `_sync_id`) + empreinte `SYNC_HASH` (fenêtre `PARAM['MOBILE_DUPLICATE_WINDOW_MIN']`) ⇒ `OKSAVE duplicate=true` sans re-sauvegarde |
| U13 | `data_reload.php` | Route additionnelle `/theme_data/.../:id_annee` (historique conservée) + polyfills gardés |
| U14/U15 | `suivi_sync_mobile.php`, `suivi_sync_mobile_service.php`, `administration.php`, menus JS ×4, SQL DICO_MENU | Écran matrice établissement × thème, filtres, drill-down chaîne, détail historique, export CSV, `get_etab_sync_summary` (COMPLET/PARTIEL/ERREUR/VIDE/AUCUN) |
| U16/U19 | `sync_status_ws.php` | WS lecture `/etab`, `/camp`, `/feedback` (fail-safe si migration absente) |
| U18/U20 | SQL `SYNC_ETAB_STATUT(+_HIST)`, `sync_lib.php`, service admin, `data_save.php` | Statuts RECU/A_CORRIGER/VALIDE/VERROUILLE, actions admin, rejet `FORM_LOCKED`, auto `A_CORRIGER→RECU` après OKSAVE |
| U23 | `params.php`, `data_save.php` | `PARAM['MOBILE_CHECK_SURVEY_STATUS']` (défaut `false` = comportement actuel) |
| U24 | `params.php`, `grille.class.php` | `PARAM['MOBILE_NO_DELETE_ON_PARTIAL']` (défaut `false`) : D→R sur POST mobile partiel, compteur `g` dans l'ACK |
| U26 | `remind_collectors.php`, `params.php`, menus | SMTP externalisé (plus de secret en dur), entrée menu `feedbacks` |
| U27 | `suivi_saisie_list_etabs.php`, `ctrl_collectors_feedbacks.php`, `remind_collectors.php`, `sms_sync.php` | `CODE_PERIODE = ? OR CODE_PERIODE IS NULL` |

**Déploiement serveur** : exécuter la migration SQL (sauvegarde dico avant), puis copier les fichiers PHP/JS. Rétro-compatible avec l'APK actuel.

## ✅ Mobile — couche données & réseau TERMINÉE (syntax_check OK)

| U | Fichier | Contenu |
|---|---|---|
| U04 | `database_service.dart` (v9), `models/sync_status.dart` | Tables `form_sync_status`, `sync_feedback` ; migration init depuis `is_sent` ; `getFormSyncStatus`, `markFormLocallyModified`, `recordSendAttempt` (maintient `is_sent`), `getPendingForms` (+ repli is_sent=0), `getDistinctFormsWithData` (avec filtre), `getSchoolSyncSummaries`, `countUnsentForms`, `reconcileSchoolWithServer`, `applyFeedbackToSyncStatus`, `replaceSyncFeedback`, `getSyncFeedback` |
| U01 | `api_service.dart` | Réponse vide / non-JSON / se_data inattendu ⇒ `ApiException` (plus jamais succès) |
| U08 | `api_service.dart` | `lastAck` (`ServerAck`) parsé depuis `se_ack` |
| U11 | `api_service.dart` | `newSyncId()` UUID v4, en-têtes `X-Sync-Id` + `X-App-Version`, même id sur les retries |
| U13 | `api_service.dart` | `reloadData(yearCode:)` → route `/:id_annee` |
| U16/U19 | `api_service.dart` | `fetchServerSyncStatus`, `fetchSyncFeedback` (fail-open) ; `FormLockedException`, `KosaveException.hint`, `ApiException.code` |

## ✅ Mobile — provider & UI TERMINÉS (syntax_check OK, tests unitaires ajoutés)

| U | Fichier | Contenu |
|---|---|---|
| U02 | `data_entry_provider.dart` | `_sendOneForm()` primitive unique ; `KosaveException` ⇒ `recordSendAttempt(status: kosave)` (plus jamais marqué envoyé) ; `FormLockedException` ⇒ `locked` ; `ApiException` ⇒ `rejected` (YEAR_MISMATCH/ACCESS_DENIED/SURVEY_CLOSED) ; réseau ⇒ `failed` |
| U03/U07 | `data_entry_provider.dart` | Moteur batch `_sendForms()` sur `getDistinctFormsWithData` (période, regroupement `LOC_REG_0`, 1re question), `sendPendingForms()` sur `getPendingForms`, `retryFailedOutcomes()`, `countPendingForms()`, `listPendingForms()` ; anciennes signatures `Map<String,bool>` conservées (compat) |
| U06 | `models/sync_status.dart`, `widgets/sync/sync_widgets.dart` | `SendOutcome` nominatif (`withLabels`, `describe`) ; `showBatchResultDialog()` partagé : succès / ignorés / échecs nominatifs « Étab › Thème [période] : motif » + « Réessayer les échecs » |
| U12 | `data_entry_provider.dart` | `_autoReloadFromServerBackground` : si `form_sync_status` en attente (saisies locales non envoyées) ⇒ pas d'écrasement, avertissement ; sinon rechargement + `sent` |
| U13 | `data_entry_provider.dart` | `reloadFromServer`/auto-reload passent `yearCode` explicite ⇒ route `/:id_annee` |
| U17 | `data_entry_provider.dart`, `campaign_detail_screen.dart` | `reconcileWithServer()` (fail-open) appelé 1× par système à l'ouverture de la liste des écoles + bouton ⟳ « Actualiser » ; menu « Actualiser l'état de synchronisation » sur l'écran de saisie |
| U19 | `school_data_screen.dart`, `campaign_detail_screen.dart` | Bannière rouge « Correction demandée par l'administration » (commentaire, admin, date) sur le formulaire concerné ; badge « correction demandée » sur la tuile école |
| U21 | `school_data_screen.dart`, `data_entry_provider.dart` | `detectServerConflict()` avant envoi ⇒ dialogue **Recharger / Écraser / Annuler** ; formulaire verrouillé ⇒ bouton Envoyer désactivé (cadenas), bannière violette + « Recharger » |
| U25 | `data_entry_provider.dart` | `static _globalSendLock` : un seul envoi à la fois (unitaire ou groupé, tous écrans) ; `isAnySendRunning` |
| U05 | `school_data_screen.dart` | Pastille colorée sur chaque chip de thème (`syncStatusForQuestion`) ; ligne d'état sous le bandeau (statut, dernier envoi OK, tentatives, statut admin, **accusé serveur** : ajouts/MAJ/suppr/inchangées, doublon, garde anti-suppression) |
| U05 | `campaign_detail_screen.dart` | `_UnsentDataBanner` réel (« N formulaires en attente d'envoi ou en échec » + **Voir** / **Envoyer**) ; `_SchoolTile` avec `SchoolSyncBadge` (x/y envoyés, à envoyer, échecs, verrouillé, correction) et avatar coloré ; rafraîchi au retour de saisie |
| U05/U07 | `screens/schools/pending_forms_screen.dart` (nouveau) | Écran « Formulaires en attente » : liste nominative groupée par établissement (statut, tentatives, dernière erreur), envoi global ou par établissement |
| Tests | `test/sync_status_test.dart` | 30+ tests unitaires des modèles (SyncStatus, FormSyncStatus.hasServerConflict, ServerAck, SendOutcome, ServerFormStatus.parseServerTime, SyncFeedback.appliesTo, SchoolSyncSummary) |

### Vérifications effectuées
- `php -l` OK sur tous les fichiers PHP modifiés/créés (PHP 7.3 natif ; `sms_sync.php` a une erreur PHP 8 **préexistante** non liée, non touchée).
- `syntax_check.py` ✅ sur tous les fichiers Dart modifiés/créés ; vérification croisée automatique que chaque méthode appelée par l'UI existe dans le provider / DatabaseService / ApiService / modèles.
- Pas de SDK Flutter dans le bac à sable : `flutter analyze` et `flutter test` **à exécuter sur le poste de build** avant de générer l'APK (voir `GUIDE_DEPLOIEMENT_AK_BURUNDI.md`).

### Rétro-compatibilité (revue statique)
- Envoi unitaire : même endpoint, mêmes paramètres, même filtre effectif (SESSION 58) ; seuls s'ajoutent les en-têtes `X-Sync-Id` / `X-App-Version` (ignorés par un ancien serveur).
- Ancien serveur (sans `sync_status_ws.php`, sans `se_ack`) : réconciliation/feedback ignorés silencieusement (fail-open) ; ligne d'état sans détail serveur.
- `is_sent` maintenu en parallèle de `form_sync_status` (migration v9 initialisée depuis `is_sent`) ⇒ aucun formulaire « perdu ».
- Hors ligne : aucun appel bloquant ; badges/bannières calculés depuis SQLite uniquement.
- Vérification année (fail-open) et CODE_TYPE_ANNEE pluriannuel inchangés.
