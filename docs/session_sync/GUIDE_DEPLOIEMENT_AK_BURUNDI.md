# Guide de déploiement — branche `ak_burundi` (synchronisation, traçabilité, suivi admin — U01–U27)

**Projet :** PAQABU / UNESCO — MEN Burundi  
**Branche :** `ak_burundi` (créée depuis `ak_secure` — `ak_secure` reste intacte et constitue le point de retour)  
**Périmètre :** serveur PHP `StatEduc_burundi` + application `stateduc_flutter`

---

## 1. Ordre de déploiement recommandé

```
1. Sauvegarde base (dico + données)          ← obligatoire
2. Migration SQL (additive, rejouable)        ← serveur
3. Copie des fichiers PHP / JS                ← serveur
4. Ajustement params.php (optionnel)          ← serveur
5. Vérification serveur (section 4)
6. flutter analyze + flutter test             ← poste de build
7. Build APK et déploiement progressif        ← mobile
```

> Le serveur `ak_burundi` est **rétro-compatible avec l'APK actuel** (`ak_secure`) et l'APK `ak_burundi` est **rétro-compatible avec l'ancien serveur** (tous les nouveaux appels sont *fail-open*). L'ordre ci-dessus est néanmoins celui qui active toutes les fonctionnalités dès le premier envoi.

---

## 2. Serveur — migration SQL (U09 / U18)

| SGBD | Script |
|---|---|
| SQL Server | `StatEduc_burundi/server-side/sql/migration_sync_tracabilite_sqlserver.sql` |
| Access | `StatEduc_burundi/server-side/sql/migration_sync_tracabilite_access.sql` (exécuter chaque instruction séparément dans l'éditeur de requêtes Access ; ignorer l'erreur si la colonne/table existe déjà) |

Contenu (100 % additif, aucune donnée existante modifiée) :

- `DATA_SAVING_LOGS` : colonnes `SYNC_ID`, `SYNC_HASH`, `NB_INSERT`, `NB_UPDATE`, `NB_DELETE`, `NB_UNCHANGED`, `MESSAGE_ERREUR`, `APP_VERSION`, `IP_CLIENT` (toutes `NULL`) + index `IX_DSL_SUIVI`, `IX_DSL_SYNC_ID`, `IX_DSL_SYNC_HASH`.
- Tables `SYNC_ETAB_STATUT` (statut courant par établissement/thème/période : `RECU`, `A_CORRIGER`, `VALIDE`, `VERROUILLE`) et `SYNC_ETAB_STATUT_HIST` (historique).
- `DICO_MENU` : entrées `30626` (suivi_sync_mobile) et `30627` (feedbacks) sous le menu Administration (`30605`). Adapter les identifiants si votre `DICO_MENU` utilise déjà ces ID.

Le script SQL Server est **idempotent** (`IF NOT EXISTS` sur chaque objet). Le code PHP fonctionne aussi **sans** la migration (colonnes absentes ⇒ journalisation historique, WS de statut renvoient des listes vides) — la migration reste nécessaire pour l'idempotence, l'ACK détaillé et l'écran admin.

---

## 3. Serveur — fichiers à copier

**Nouveaux**
- `StatEduc_burundi/sync_lib.php`
- `StatEduc_burundi/sync_status_ws.php`
- `StatEduc_burundi/server-side/include/administration/suivi_sync_mobile.php`
- `StatEduc_burundi/server-side/include/administration/suivi_sync_mobile_service.php`
- `StatEduc_burundi/server-side/sql/migration_sync_tracabilite_{sqlserver,access}.sql`

**Modifiés**
- `data_save.php`, `data_reload.php`, `questionnaire_ws.php`, `administration.php`, `params.php`, `sms_sync.php`
- `server-side/classes/metier/grille.class.php`
- `server-side/include/administration/remind_collectors.php`, `suivi_saisie_list_etabs.php`, `ctrl_collectors_feedbacks.php`
- `client-side/js/menus/1/{fr,eng,por}/_menu_princ.js`, `client-side/js/menus/0/por/_menu_princ.js`

### Paramètres (`params.php`) — défauts = comportement actuel

| Paramètre | Défaut | Rôle |
|---|---|---|
| `MOBILE_CHECK_SURVEY_STATUS` | `false` | U23 — refuser l'envoi si la campagne est fermée (`SURVEY_CLOSED`) |
| `MOBILE_NO_DELETE_ON_PARTIAL` | `false` | U24 — ne pas supprimer en base les lignes absentes d'un POST mobile partiel (D→R, compteur `g` dans l'ACK) |
| `MOBILE_DUPLICATE_WINDOW_MIN` | `30` | U11/U22 — fenêtre (minutes) de détection des doublons `X-Sync-Id` / `SYNC_HASH` |
| `MOBILE_ENFORCE_LOCK` | `true` | U20 — rejeter (`FORM_LOCKED`) un envoi sur un formulaire `VALIDE`/`VERROUILLE` |
| `MOBILE_CODE_GROUPE` | `4` | Groupe utilisateur des agents mobiles (listes de l'écran admin) |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASSWORD`, `MAIL_SECURE` | `""` | U26 — SMTP des relances (plus de secret en dur ; relance ignorée si `MAIL_HOST` vide) |

---

## 4. Vérification serveur (5 minutes)

1. `php -l` sur chaque fichier copié (PHP 7.3).
2. Connexion admin → menu **Administration › Suivi synchronisation mobile** : la matrice établissements × thèmes s'affiche, filtres par campagne / année / chaîne fonctionnent, export CSV OK.
3. `GET sync_status_ws.php/camp/<login>/<camp>/<sys>/<annee>` (avec l'authentification HTTP habituelle) ⇒ JSON `{ "ok": true, ... }`.
4. Envoi d'un formulaire depuis l'APK actuel ⇒ `DATA_SAVING_LOGS` : ligne `OKSAVE` avec `NB_*` renseignés ; renvoyer le même formulaire deux fois rapidement ⇒ seconde ligne `DUPLICATE` (si `X-Sync-Id` présent) ou `OKSAVE` avec `nb_unchanged` = total.
5. Depuis l'écran admin : « Demander correction » sur un formulaire ⇒ `SYNC_ETAB_STATUT.STATUT = 'A_CORRIGER'` ; l'agent renvoie ⇒ statut repasse `RECU` automatiquement.
6. « Valider » puis tenter un envoi mobile ⇒ HTTP 400 `se_code = FORM_LOCKED`, ligne `REJET_VERROU` dans les logs.

---

## 5. Mobile — avant build

```bash
cd stateduc_flutter
flutter pub get
flutter analyze
flutter test                     # inclut test/sync_status_test.dart et test/sql_translator_test.dart
flutter build apk --release
```

- SQLite passe en **version 9** (`form_sync_status`, `sync_feedback`) ; la migration initialise les statuts depuis `is_sent` existant — aucune ré-installation nécessaire, aucune donnée perdue.
- `is_sent` reste maintenu en parallèle de `form_sync_status` : les écrans et l'envoi historiques continuent de fonctionner sur la même source de vérité.
- ⚠️ **Retour arrière APK** : l'APK `ak_secure` (SQLite v8) ne définit pas `onDowngrade` ; il ne peut pas ouvrir une base déjà migrée en v9. Avant de réinstaller un APK plus ancien, envoyer toutes les données en attente puis vider les données de l'application (ou désinstaller) et re-télécharger la campagne.

---

## 6. Plan de test de non-régression sur appareil

| # | Scénario | Résultat attendu |
|---|---|---|
| 1 | Saisie + « Envoyer » d'un formulaire (serveur à jour) | Ligne d'état verte « Envoyé », résumé ACK (« N ajout(s), N mise(s) à jour… ») ; pastille verte sur le chip |
| 2 | Même envoi renvoyé sans modification | ACK « déjà reçu le … » (duplicate) ou `nb_unchanged` = total ; pas de doublon en base |
| 3 | Envoi hors ligne | Statut « Échec d'envoi » (rouge), bannière « N formulaire(s) en attente » sur la campagne ; le bouton « Envoyer uniquement les formulaires en attente » les renvoie dès le retour réseau |
| 4 | KOSAVE forcé (ex. contrainte SQL) | Statut « Reçu, non enregistré (KOSAVE) » orange ; **non** marqué envoyé ; réapparaît dans « Formulaires en attente » |
| 5 | Envoi global école / campagne avec un échec | Dialogue résultat nominatif « Étab › Thème : motif » + « Réessayer les échecs » ne renvoie que les échecs |
| 6 | Admin « Demander correction » puis ouverture du formulaire (⟳ Actualiser) | Bannière rouge avec le commentaire ; renvoi ⇒ bannière disparaît, statut admin `RECU` |
| 7 | Admin « Valider » puis tentative d'envoi | Bouton Envoyer grisé (cadenas), bannière violette, proposition « Recharger » ; aucun POST |
| 8 | Modification web du même formulaire après envoi mobile, puis modif locale + Envoyer | Dialogue « Conflit de version » : Recharger / Écraser / Annuler |
| 9 | Ancien serveur (sans migration ni WS) | Envoi unitaire et global identiques à `ak_secure` ; aucun message d'erreur lié au statut ; réconciliation silencieuse |
| 10 | Changement d'année dans Paramètres, envoi et rechargement | Données de l'année active uniquement (route `/:id_annee`), vérification année fail-open inchangée |
| 11 | Import Excel utilisateurs, pluriannuel `CODE_TYPE_ANNEE` | Inchangés (aucune modification de ces modules) |

---

## 7. Retour arrière

- **Serveur** : recopier les fichiers de `ak_secure`. La migration SQL peut rester en place (colonnes `NULL`, tables non lues par l'ancien code). Supprimer les deux entrées `DICO_MENU` si besoin.
- **Mobile** : envoyer d'abord tous les formulaires en attente (écran « Formulaires en attente »), puis désinstaller / vider les données de l'application avant de réinstaller l'APK `ak_secure` (voir avertissement section 5 : pas de rétrogradation SQLite v9 → v8 à chaud).
