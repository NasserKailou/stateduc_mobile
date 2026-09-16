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

## ⏳ Mobile — RESTE À FAIRE (provider + UI) — non commencé, aucun risque de régression en l'état

Le code actuel de `data_entry_provider.dart` et des écrans est **identique à `ak_secure`** ; l'APK se comporte donc exactement comme avant, avec en plus le durcissement U01, l'idempotence U11 et la migration v9 (transparents).

| U | À implémenter | Points d'accroche déjà prêts |
|---|---|---|
| U02 | `sendToServer` : `on KosaveException` → `recordSendAttempt(status: SyncStatus.kosave)` au lieu de `markCollectedDataSent` | `DatabaseService.recordSendAttempt` |
| U03/U07 | Réécrire `sendAllFormsForSchool/Campaign` sur `getDistinctFormsWithData` (avec `id_filter`, `etabRegroupId`, `isFirstQuestion`) ; nouvelle `sendPendingForms()` sur `getPendingForms` | méthodes DB prêtes |
| U06 | Retourner `List<BatchItemResult>` (typé `SendOutcome`) ; dialogue nominatif + « Réessayer les échecs » | `models/sync_status.dart` |
| U12 | `_autoReloadFromServerBackground` : ne pas écraser les champs `is_sent=0` modifiés ; marquer les lignes serveur `is_sent=1` | — |
| U17 | À l'ouverture campagne : `fetchServerSyncStatus` → `reconcileSchoolWithServer` ; `fetchSyncFeedback` → `replaceSyncFeedback` + `applyFeedbackToSyncStatus` | API + DB prêtes |
| U21 | Avant envoi : si `FormSyncStatus.hasServerConflict` → dialogue Recharger / Écraser / Annuler ; `on FormLockedException` → statut `locked` + proposition de rechargement | modèle prêt |
| U25 | `static bool _sendLock` global dans le provider (un seul envoi à la fois) | — |
| U05 | `school_data_screen.dart` : pastille statut sur chips (`getFormSyncStatusesForSchool`), bannière ACK ; `campaign_detail_screen.dart` : remplacer `_UnsentDataBanner` placeholder par `countUnsentForms`, agrégat `getSchoolSyncSummaries` sur `_SchoolTile`, bouton « Envoyer les formulaires en attente », badge « Correction demandée » | DB prêtes |

Estimation : ~1 journée de développement Flutter + tests sur appareil.
