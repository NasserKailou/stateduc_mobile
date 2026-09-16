# Guide de déploiement — branche `ak_burundi` (synchronisation & traçabilité U01–U27)

Ce guide décrit l'ordre de mise en production des évolutions de la branche `ak_burundi`
(serveur PHP `StatEduc_burundi` + application mobile `stateduc_flutter`), les points de
rétro-compatibilité et la procédure de retour arrière. `ak_secure` reste la branche de
référence stable : elle n'a **pas** été modifiée.

---

## 1. Ordre recommandé

| Étape | Quoi | Rétro-compatible ? |
|---|---|---|
| 1 | Sauvegarde base **dico** (SQL Server ou Access) | — |
| 2 | Migration SQL additive | ✅ n'altère aucune colonne existante |
| 3 | Fichiers PHP / JS serveur | ✅ l'APK **actuel** continue de fonctionner |
| 4 | Paramétrage `params.php` (valeurs par défaut = comportement historique) | ✅ |
| 5 | Vérifications serveur (§4) | — |
| 6 | Build & déploiement APK (`flutter analyze` + `flutter test` d'abord) | ✅ fonctionne aussi face à un **ancien** serveur (fail-open) |

> Le serveur peut être déployé **avant** l'APK, et l'APK peut être déployé **avant** le serveur :
> chaque côté détecte l'absence de l'autre (WS `sync_status_ws.php` absent ⇒ silence côté mobile ;
> en-têtes `X-Sync-Id` absents ⇒ traitement classique côté serveur).

---

## 2. Serveur — migration SQL (U09 / U18 / U14)

Fichiers : `StatEduc_burundi/server-side/sql/`
- `migration_sync_tracabilite_sqlserver.sql` (SQL Server, idempotent : `IF NOT EXISTS`)
- `migration_sync_tracabilite_access.sql` (Access : exécuter bloc par bloc, ignorer les erreurs
  « colonne existe déjà »)

Contenu (100 % **additif**) :
1. `DATA_SAVING_LOGS` : colonnes `SYNC_ID VARCHAR(36)`, `SYNC_HASH VARCHAR(40)`, `NB_INSERT`,
   `NB_UPDATE`, `NB_DELETE`, `NB_UNCHANGED INT`, `MESSAGE_ERREUR VARCHAR(500)`,
   `APP_VERSION VARCHAR(20)`, `IP_CLIENT VARCHAR(45)` + index (établissement/thème/date, sync_id).
2. Table `SYNC_ETAB_STATUT` (statut administratif courant par formulaire :
   `RECU / A_CORRIGER / VALIDE / VERROUILLE`) et `SYNC_ETAB_STATUT_HIST` (historique).
3. (SQL Server uniquement) entrées `DICO_MENU` / `DICO_TRADUCTION` / `ADMIN_DROITS` pour le
   nouvel écran **Administration › Suivi synchronisation mobile** (ids 30636/30637). Sur Access,
   créer ces lignes via l'interface d'administration des menus si besoin.

Toutes les fonctions PHP de `sync_lib.php` et `sync_status_ws.php` sont **fail-safe** : si la
migration n'est pas passée, elles retournent vide / `false` sans erreur SQL bloquante.

---

## 3. Serveur — fichiers à copier

```
StatEduc_burundi/
  params.php                                  (nouveaux PARAM — voir §4)
  sync_lib.php                                (nouveau, racine)
  sync_status_ws.php                          (nouveau — WS lecture /etab /camp /feedback)
  data_save.php                               (ACK, idempotence, rejets journalisés, verrou)
  data_reload.php                             (route /theme_data/.../:id_annee)
  questionnaire_ws.php                        (MAJ_STATS)
  administration.php                          (2 nouveaux cas)
  sms_sync.php                                (U27)
  server-side/classes/metier/grille.class.php (compteurs + garde anti-suppression)
  server-side/include/administration/
      suivi_sync_mobile.php                   (nouveau — écran)
      suivi_sync_mobile_service.php           (nouveau — AJAX)
      remind_collectors.php                   (SMTP externalisé, U27)
      suivi_saisie_list_etabs.php             (U27)
      ctrl_collectors_feedbacks.php           (U27)
  client-side/js/menus/1/{fr,eng,por}/_menu_princ.js, 0/por/_menu_princ.js
```

Syntaxe **PHP 7.3 native** conservée (pas de `match`, pas de types union, pas de `str_starts_with`
non gardé).

---

## 4. Paramètres (`params.php`) — défauts = comportement historique

| Paramètre | Défaut | Rôle | Quand le changer |
|---|---|---|---|
| `MOBILE_CHECK_SURVEY_STATUS` | `false` | Refuse (`SURVEY_CLOSED`) l'envoi sur une campagne fermée | Après validation métier |
| `MOBILE_NO_DELETE_ON_PARTIAL` | `false` | Un POST mobile partiel ne supprime plus les lignes absentes (D→R, compteur `g` dans l'ACK) | Si des pertes de lignes sont constatées |
| `MOBILE_DUPLICATE_WINDOW_MIN` | `30` | Fenêtre (min) de détection des doublons `X-Sync-Id`/`SYNC_HASH` ⇒ `OKSAVE duplicate=true` | Rarement |
| `MOBILE_ENFORCE_LOCK` | `true` | Rejette (`FORM_LOCKED`) l'envoi d'un formulaire `VALIDE`/`VERROUILLE` | `false` pour désactiver le verrou |
| `MAIL_HOST/PORT/USERNAME/PASSWORD/SECURE` | vides | SMTP des rappels (`remind_collectors.php`) ; vide ⇒ envoi désactivé | Renseigner en prod |

### Vérifications serveur après déploiement
1. `php -l` sur chaque fichier copié (PHP 7.3).
2. Ouvrir **Administration › Suivi synchronisation mobile** : la matrice établissement × thème
   s'affiche (vide si aucune donnée).
3. `GET sync_status_ws.php/camp/<login>/<camp>/<sys>/<annee>` (HttpAuth) ⇒ JSON `{"ok":true,...}`.
4. Envoyer un formulaire depuis l'APK actuel ⇒ `DATA_SAVING_LOGS` renseigne `NB_*`
   (colonnes `SYNC_ID` vides tant que le nouvel APK n'est pas déployé : normal).

---

## 5. Mobile — build

```bash
cd stateduc_flutter
flutter pub get
flutter analyze              # doit être vierge (aucun SDK Flutter dans le bac à sable de dev)
flutter test                 # sql_translator_test.dart + sync_status_test.dart
flutter build apk --release
```

- Base SQLite passe en **v9** : tables `form_sync_status` et `sync_feedback` créées au premier
  lancement ; les états initiaux sont dérivés de `is_sent` existant (aucune perte de données locales).
  `is_sent` continue d'être maintenu en parallèle (compatibilité des écrans non modifiés).
- Face à un **ancien serveur** : `se_ack` absent ⇒ pas d'accusé détaillé ; `sync_status_ws.php`
  absent ⇒ réconciliation/feedback ignorés silencieusement ; route `/:id_annee` absente ⇒
  `reloadData` retombe sur la route historique.

### Scénarios de recette (appareil)
| # | Scénario | Attendu |
|---|---|---|
| 1 | Envoi unitaire OK | Pastille verte sur le chip, ligne « Envoyé · dernier envoi OK … · Serveur : n ajout(s)… » |
| 2 | Coupure réseau pendant l'envoi | Statut « Échec d'envoi », formulaire listé dans « Formulaires en attente », bannière campagne « N formulaires en attente » |
| 3 | Double tap Envoyer / retry Dio | Un seul enregistrement serveur ; 2e requête ⇒ `duplicate=true` (« déjà reçu le … ») |
| 4 | Serveur renvoie KOSAVE | Statut orange « Reçu, non enregistré (KOSAVE) », **non** marqué envoyé, ré-essayable |
| 5 | Admin met `A_CORRIGER` | Après « Actualiser » : bannière rouge sur le formulaire + badge sur l'école ; renvoi ⇒ statut repasse `RECU`, bannière disparaît |
| 6 | Admin met `VALIDE`/`VERROUILLE` | Bouton Envoyer grisé (cadenas), bannière violette, « Recharger » propose les données officielles ; envoi forcé ⇒ `FORM_LOCKED` |
| 7 | Modif web après envoi mobile puis modif mobile | Dialogue « Conflit de version » : Recharger / Écraser / Annuler |
| 8 | Envoi global campagne avec 1 échec | Dialogue nominatif « École › Thème : motif » + « Réessayer les échecs » ne renvoie que l'échec |
| 9 | Changement d'année dans Paramètres | Statuts/pastilles filtrés par `code_type_annee` ; rechargement sur la bonne année |

---

## 6. Retour arrière

- **Serveur** : recopier les fichiers de `ak_secure` ; les colonnes/tables ajoutées peuvent rester
  (inutilisées, sans effet). Remettre `MOBILE_*` n'est pas nécessaire (ignorés par l'ancien code).
- **Mobile** : réinstaller l'APK `ak_secure`. La base SQLite v9 reste lisible (tables
  supplémentaires ignorées ; `is_sent` est à jour). Aucune migration descendante requise.

---

## 7. Références
- Diagnostic initial : `DIAGNOSTIC_SYNCHRONISATION_TRACABILITE.md` (U01–U27, C1–C10)
- État d'implémentation détaillé : `ETAT_IMPLEMENTATION_AK_BURUNDI.md`
- Journaux : `StatEduc_burundi/CHANGELOG.md`, `stateduc_flutter/CHANGELOG.md`
