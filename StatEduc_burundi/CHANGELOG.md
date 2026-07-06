# StatEduc MEN 2025 — Journal des travaux (Serveur PHP)

Branche de développement : `ak_main` / `ak_secure`  
Dépôt : `https://github.com/NasserKailou/stateduc_mobile`  
Pull Request ouverte : [PR #2](https://github.com/NasserKailou/stateduc_mobile/pull/2)

---

## Session 41 — Correctif DNS production : fallback IP cache sur résolution hostname MEN (branche `ak_secure`)

### Symptôme (constaté en production VM `http://stateduc.ins.ne:9191/StatEduc/`)

Après déploiement sur la VM de production MEN :
- Authentification ✅ (réseau MEN disponible au login)
- Téléchargement campagne ✅
- **Envoi des données ❌** : `"6 : Could not resolve host: stateduc.ins.ne"`

Screenshot de l'erreur : DioException type=connectionError → SocketException: Failed host lookup 'stateduc.ins.ne'.

### Cause racine

`stateduc.ins.ne` est un **nom DNS interne au réseau LAN du MEN**. Scénario réel :
1. L'agent se connecte depuis le bureau MEN (DNS interne disponible) → auth + campagne OK
2. L'agent se rend dans l'école, saisit les données **hors ligne** (mode offline)
3. Au retour, l'agent envoie depuis un réseau différent (4G, autre WiFi) **où `stateduc.ins.ne` n'est pas résolvable** → code 6

Sur le réseau local de test, le DNS fonctionne → pas d'erreur en test local.

### Solution : IP caching au moment de l'authentification

#### Stratégie générale

À l'authentification (qui réussit car le réseau MEN est disponible à ce moment-là) :
1. Résoudre `stateduc.ins.ne` → IP numérique via `InternetAddress.lookup()`
2. Mettre l'IP en cache mémoire **et** dans `SharedPreferences` (persistance multi-session)
3. Sur les requêtes suivantes, si la résolution DNS échoue → remplacer le hostname par l'IP cachée et rejouer

#### Modifications dans `api_service.dart`

##### 1. Nouveaux champs + constante (classe `ApiService`)
```dart
String? _cachedServerIp;   // IP numérique résolue (ex: '192.168.10.5')
int?    _cachedServerPort; // Port extrait de l'URL (ex: 9191)
static const String _kDnsCacheKeyPrefix = 'dns_cache_';
```

##### 2. `configure()` — appel à `_loadCachedIp()` au démarrage
`_loadCachedIp()` lit SharedPreferences pour restaurer le cache DNS d'une session précédente.

##### 3. `_loadCachedIp()` — lecture cache persisté
```dart
Future<void> _loadCachedIp() async { ... }
```
Charge la valeur `dns_cache_<hostname>` depuis SharedPreferences au démarrage de l'app.

##### 4. `_resolveAndCacheIp()` — résolution + mise en cache
```dart
Future<void> _resolveAndCacheIp() async { ... }
```
Appelée en **background** (unawaited) après une authentification réussie :
- Ignore les IPs numériques (déjà cachées)
- `InternetAddress.lookup(host).timeout(10s)` → préfère IPv4
- Stocke `<ip>:<port>` dans SharedPreferences (`dns_cache_<hostname>`)
- Non-bloquante si timeout ou exception

##### 5. Helpers statiques
- `_extractHostFromUrl()` — `Uri.parse(url).host`
- `_extractPortFromUrl()` — `Uri.parse(url).port`
- `_isNumericIp()` — regex IPv4 + détection IPv6 (`:`)
- `_buildFallbackUrl()` — `uri.replace(host: cachedIp, port: cachedPort)`

##### 6. Nouvelle classe `_DnsFallbackInterceptor`
Intercepteur `onError` ajouté AVANT le log intercepteur :
```dart
class _DnsFallbackInterceptor extends Interceptor {
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    // Condition 1 : connectionError uniquement
    // Condition 2 : message contient 'Failed host lookup' / 'Could not resolve host'
    // Condition 3 : _cachedServerIp != null
    // → Remplace hostname par IP, rejoue via _service._dio.fetch(fallbackOptions)
    // → handler.resolve(response) si succès
    // → handler.next(err) si IP inconnue ou fallback échoue aussi
  }
}
```

Chaine des intercepteurs (ordre d'ajout = ordre d'exécution `onRequest`, inversé pour `onError`) :
```
Ajout : _AuthInjectorInterceptor → _DnsFallbackInterceptor → LogInterceptor
onError LIFO : LogInterceptor (log) → _DnsFallbackInterceptor (fallback) → _AuthInjector
```

##### 7. `authenticate()` — déclenchement de la résolution
```dart
// Après parsing réussi :
_resolveAndCacheIp(); // non-awaited, non-bloquant
return User.fromJson(userMap);
```

#### Avantages du design
- **Zéro régression** : toute requête qui fonctionne normalement n'est pas affectée
- **Transparent** : le fallback est invisible pour l'utilisateur (pas d'erreur si IP cachée disponible)
- **Persistant** : l'IP survit aux redémarrages de l'app (SharedPreferences)
- **Multi-pattern** : capture les variantes du message SocketException sur Android/iOS/Linux

### Information : contrôle offline déjà opérationnel (Sessions 39–40)

La capture d'écran transmise montre **"1 incohérence(s) locale(s) — Contrôle offline – non envoyé au serveur"** — le moteur de cohérence offline est **déjà fonctionnel**.

Il a été implémenté en Sessions 39–40 (`CoherenceEvaluator`, `coherence_rules` SQLite, debounce 800ms). Pour bénéficier des correctifs de Session 40 (regex TABLE.FIELD, agrégats virtuels, données cross-formulaires), un `git pull` + rebuild APK + re-téléchargement de la campagne est nécessaire.

### Fichiers modifiés — Session 41

| Fichier | Modification |
|---------|-------------|
| `stateduc_flutter/lib/services/api_service.dart` | +236 lignes : DNS cache, `_resolveAndCacheIp()`, `_loadCachedIp()`, `_buildFallbackUrl()`, `_DnsFallbackInterceptor` |
| `stateduc_mobile/stateduc_flutter/lib/services/api_service.dart` | Miroir identique |
| `RESTITUTION_TECHNIQUE_STATEDUC_MOBILE.md` | Commit initial (document créé en Session 40, non committé) |

---
### Correctif 41c -- SOLUTION DEFINITIVE : SERVER_ADDR:SERVER_PORT pour le curl interne

**Session 41b** utilisait `127.0.0.1:9191` mais Apache n'ecoute pas sur le port 9191 (c'est le port du reverse proxy/Tomcat frontal). Resultat : curl code 7 "Connection refused".

**Solution finale** dans `config_app.php` :
```php
// $_SERVER['SERVER_ADDR'] = IP reelle Apache (127.0.0.1 ou LAN IP)
// $_SERVER['SERVER_PORT'] = port reel Apache (80, 8080...) -- pas le port proxy externe
$_sised_server_addr  = $_SERVER['SERVER_ADDR'];
$_sised_server_port  = (int)$_SERVER['SERVER_PORT'];
$_sised_scheme_int   = ($_sised_server_port === 443) ? 'https' : 'http';
$_sised_port_int     = (!in_array($_sised_server_port, [80, 443])) ? ':' . $_sised_server_port : '';
$SISED_AURL_INTERNAL = $_sised_scheme_int . '://' . $_sised_server_addr . $_sised_port_int . $SISED_URL;
```

Fonctionne quelle que soit la topologie : Apache direct, reverse proxy, XAMPP, IIS, Tomcat AJP.

---

### Correctif 41b — Cause racine réelle de l'erreur DNS : curl PHP interne (SERVEUR)

**Important** : le correctif Flutter (Session 41) était basé sur une mauvaise hypothèse.
Les logs de débogage révèlent la vraie cause :

```
[Dio←] 200 http://stateduc.ins.ne:9191/stateduc/data_save.php/...
Body: {"se_status":400,"se_data":"6 : Could not resolve host: stateduc.ins.ne"}
```

**Le client Flutter reçoit HTTP 200** — la requête Dio arrive au serveur sans problème.
**L'erreur DNS est dans la réponse JSON** — c'est le serveur PHP qui échoue à faire son curl interne.

#### Chaîne d'exécution côté serveur (data_save.php)

```
Mobile (Flutter)
  → POST data_save.php/theme_save/... (HTTP 200 — OK)
     → PHP vérifie droits, construit $urlBase = $GLOBALS['SISED_AURL'].'questionnaire_ws.php?...'
       $SISED_AURL = 'http://' . $_SERVER['HTTP_HOST'] . '/StatEduc/'
                   = 'http://stateduc.ins.ne:9191/StatEduc/'
     → $curl->post('http://stateduc.ins.ne:9191/StatEduc/questionnaire_ws.php?...', $data)
       ← ERREUR : curl code 6 — "Could not resolve host: stateduc.ins.ne"
          (le serveur lui-même ne résout pas son propre hostname DNS)
     ← curl->error() callback → echo {"se_status":400,"se_data":"6 : ..."}
  ← Flutter reçoit HTTP 200 + corps JSON d'erreur
```

#### Cause : `/etc/hosts` de la VM ne contient pas `stateduc.ins.ne`

`stateduc.ins.ne` est résolu par un serveur DNS interne au LAN MEN. La VM elle-même
n'a pas cette entrée dans ses résolveurs locaux → curl PHP depuis la VM échoue.

#### Solution : `SISED_AURL_INTERNAL` dans `config_app.php`

```php
// Nouvelle variable : utilise 127.0.0.1 au lieu du hostname DNS pour les curl internes
$_sised_host_parts   = explode(':', $_SERVER['HTTP_HOST']);
$_sised_port         = isset($_sised_host_parts[1]) ? ':' . $_sised_host_parts[1] : '';
$SISED_AURL_INTERNAL = 'http://127.0.0.1' . $_sised_port . $SISED_URL;
// Ex: 'http://stateduc.ins.ne:9191/StatEduc/' → 'http://127.0.0.1:9191/StatEduc/'
```

Le port est conservé depuis `HTTP_HOST` pour que Apache/IIS achemine vers le bon vhost.

#### Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `StatEduc_burundi/config_app.php` | +10 lignes : définition de `$SISED_AURL_INTERNAL` avec `127.0.0.1` |
| `StatEduc_burundi/data_save.php` | 2 lignes : `$GLOBALS['SISED_AURL']` → `$GLOBALS['SISED_AURL_INTERNAL']` (urlBase questionnaire_ws) |
| `StatEduc_burundi/data_reload.php` | 2 lignes : même correctif (questionnaire_ws + questionnaire_reload_ws) |

#### Portée complète (autres fichiers non mobiles — PROD uniquement)

Pour les pages d'administration web (`ctrl_validation_criteres.php`, `ctrl_validation_etab_details.php`,
`ctrl_validation_etab_service.php`), le même problème peut survenir mais ces fichiers utilisent `common.php`
(non `common_ws.php`) — ils ne sont pas touchés ici car hors périmètre mobile.

---


## Session 40 — Correctif moteur cohérence offline : regex TABLE.FIELD + données cross-formulaires + agrégats virtuels (branche `ak_secure`)

### Symptôme (après Session 39)
Après `git pull` + re-téléchargement de la campagne, le contrôle offline retourne toujours **0 violation** alors que le serveur en détecte 2.

Les logs Flutter montrent que les règles arrivent maintenant du serveur (`rules=2`) mais que **toutes les valeurs V1 et V2 sont `null`** → toutes les règles sont ignorées :

```
[CoherenceEval] rule=493 sql_regle="SELECT Sum(ELEVES_AGE_NIVEAU_SEXE.FILLES_AGE_NIVEAU) ..." → v1=null
[CoherenceEval] skip idRegle=493 — field not found in collected data
[CoherenceEval] rule=495 sql_regle="SELECT Sum(ELEVES_AGE_NIVEAU_SEXE.TOTAL_AGE_NIVEAU) ..." → v1=null
[CoherenceEval] skip idRegle=495 — field not found in collected data
```

### Causes racines (trois problèmes indépendants)

#### Problème 1 — Regex `\w+` ne traverse pas le point dans `TABLE.FIELD` (CRITIQUE)

L'ancienne regex `SUM\s*\(\s*(\w+)\s*\)` capturait le **qualificateur de table** au lieu du **nom de champ** :

```
SUM(ELEVES_AGE_NIVEAU_SEXE.FILLES_AGE_NIVEAU)
     ↑ \w+ s'arrête au point → capture "ELEVES_AGE_NIVEAU_SEXE" au lieu de "FILLES_AGE_NIVEAU"
```

Résultat : `_sumFieldAcrossAllFilters("ELEVES_AGE_NIVEAU_SEXE", values)` → null car aucun champ form n'a ce nom.

**Correctif** : nouveau pattern `SUM\s*\(\s*(?:\w+\.)?\s*(\w+)\s*\)` — le préfixe table est optionnel et non capturant.

#### Problème 2 — Colonnes de vues DB agrégées absentes de `collected_data` (VIEW COLUMNS)

Les colonnes `FILLES_AGE_NIVEAU` et `TOTAL_AGE_NIVEAU` sont des colonnes de la **vue DB `ELEVES_AGE_NIVEAU_SEXE`** — elles n'existent pas comme champs de formulaire dans `collected_data`. Même avec la regex corrigée, un lookup direct de `FILLES_AGE_NIVEAU` échouerait.

**Correctif** : calcul de **totaux virtuels** dans `_injectVirtualAggregates()` :
- `TOTAL_AGE_NIVEAU` ← somme de TOUS les champs numériques du formulaire courant
- `FILLES_AGE_NIVEAU` ← somme des champs dont le nom porte un marqueur "filles" (`NB_F_*`, `_FILLES_*`, etc.)

Ces valeurs approchent les agrégats calculés côté serveur par la vue SQL.

#### Problème 3 — Données cross-formulaires manquantes (`DONNEES_ETABLISSEMENT`)

La règle 493 (`sql_assoc`) référence `DONNEES_ETABLISSEMENT.NB_ELEVES_F` — un champ qui peut être collecté via un **formulaire différent** (`id_qst` différent). L'ancienne implémentation ne chargeait que les données du formulaire courant.

**Correctif** : nouvelle méthode `getAllCollectedDataForCampEtab()` dans `database_service.dart` qui charge TOUTES les données collectées pour l'école + campagne (tous formulaires, tous filtres), puis injectées dans la map `values` avec priorité inférieure aux données du formulaire courant.

### Correctifs appliqués

#### `stateduc_flutter/lib/services/coherence_evaluator.dart` (SESSION 40) — RÉÉCRITURE COMPLÈTE

1. **Regex SUM corrigée** : `SUM\s*\(\s*(?:\w+\.)?\s*(\w+)\s*\)` — qualificateur table optionnel
2. **Fallback multi-colonnes** : si la 1re colonne SUM n'est pas trouvée, essaie les colonnes suivantes
3. **Chargement cross-formulaires** : appel à `getAllCollectedDataForCampEtab()` en étape 2 du `evaluate()`
4. **Totaux virtuels** : `_injectVirtualAggregates()` calcule `TOTAL_AGE_NIVEAU` et `FILLES_AGE_NIVEAU` depuis les données brutes du formulaire courant
5. **Logs améliorés** : raison précise du skip (`v1=X v2=Y`)

#### `stateduc_flutter/lib/services/database_service.dart` (SESSION 40)

Nouvelle méthode `getAllCollectedDataForCampEtab()` :
- Requête sans filtre `id_qst` : charge TOUS les formulaires de l'école pour la campagne
- Somme les valeurs numériques par nom de champ (même comportement que `SUM()` serveur)
- Retourne `Map<String, String>` avec clés en MAJUSCULES

### Résolution attendue pour règles 493 et 495

| SQL pattern | Champ extrait | Résolution |
|-------------|--------------|------------|
| `Sum(ELEVES_AGE_NIVEAU_SEXE.FILLES_AGE_NIVEAU)` | `FILLES_AGE_NIVEAU` | Virtual aggregate (somme champs _F_) |
| `Sum(ELEVES_AGE_NIVEAU_SEXE.TOTAL_AGE_NIVEAU)` | `TOTAL_AGE_NIVEAU` | Virtual aggregate (somme tous champs) |
| `Sum(DONNEES_ETABLISSEMENT.NB_ELEVES_F)` | `NB_ELEVES_F` | Cross-question lookup |
| `Sum(DONNEES_ETABLISSEMENT.NB_ELEVES)` | `NB_ELEVES` | Cross-question lookup |

### Fichiers modifiés — Session 40

| Fichier | Changement |
|---------|-----------|
| `stateduc_flutter/lib/services/coherence_evaluator.dart` | **SESSION 40** : Réécriture `_extractValue()`, ajout `_injectVirtualAggregates()`, chargement cross-formulaires, regex TABLE.FIELD corrigée |
| `stateduc_flutter/lib/services/database_service.dart` | **SESSION 40** : Nouvelle méthode `getAllCollectedDataForCampEtab()` |
| `stateduc_mobile/stateduc_flutter/lib/services/coherence_evaluator.dart` | Miroir synchronisé |
| `stateduc_mobile/stateduc_flutter/lib/services/database_service.dart` | Miroir synchronisé |

---

## Session 39 — Correctif contrôle de cohérence offline (branche `ak_secure`)

### Symptôme
Le contrôle de cohérence **serveur (online)** fonctionne correctement (visible dans la capture d'écran — "Contrôle de cohérence : 2 incohérence(s) détectée(s)"). Mais le contrôle **offline** retourne toujours 0 règles : `"no offline coherence rules returned"` dans les logs Flutter, même pour des thèmes qui ont des règles configurées en base de données.

### Cause racine — Mauvaise décomposition de l'ID composite dans `data_rules.php`

Les IDs de thèmes sont composites : l'app mobile concatène l'ID brut du thème avec le numéro de secteur zero-paddé.

**Exemples réels observés :**
```
ID_THEME_SYSTEME = 10102   →   raw_theme = 101 + suffixe "02" (2 digits)
ID_THEME_SYSTEME = 10202   →   raw_theme = 102 + suffixe "02"
ID_THEME_SYSTEME = 10302   →   raw_theme = 103 + suffixe "02"
```

L'ancienne logique de décomposition utilisait `strlen(id_sector)` :
```php
$len_sector = strlen("2");  // = 1 (ERREUR : le suffixe réel est "02" = 2 chars)
$candidate  = substr("10102", 0, 5 - 1);  // = "1010" (FAUX — devrait être "101")
```
Résultat : `SELECT ... WHERE ID_THEME = 1010` → 0 règles → `nb_regles: 0` → aucune règle stockée offline → contrôle offline muet.

**Pourquoi `data_controle.php` fonctionnait quand même ?**
`data_controle.php` utilise la même logique `strlen(id_sector)` mais traite des IDs différents (ex: `15702` → strip 1 → `1570` ✅). La base de données contient des IDs composites formés parfois avec 1 digit, parfois avec 2 digits selon le système et la campagne.

### Correctifs appliqués

#### `StatEduc_burundi/data_rules.php` (SESSION 39)

Nouvelle fonction helper `rules_resolve_theme_id($id_theme, $id_sector)` qui :
1. Teste plusieurs longueurs de suffixe (1 à 4 digits), en commençant par `strlen(id_sector)` pour la compatibilité rétrograde
2. **Valide chaque candidat contre `DICO_REGLE_THEME`** : si au moins une règle existe pour ce candidat → c'est le bon raw ID
3. Retourne le premier candidat validé, ou l'ID composite brut si aucun ne correspond
4. `error_log()` diagnostiques à chaque étape pour faciliter le débogage

```php
// Avant (FAUX pour composite "10102" / sector "2"):
$str_theme_id = substr("10102", 0, 5-1) = "1010"

// Après (CORRECT):
$str_theme_id = rules_resolve_theme_id("10102", "2")
// → teste strip=1 → "1010" → 0 règles en DB → skip
// → teste strip=2 → "101"  → N règles en DB → ✅ retourne "101"
```

#### `stateduc_flutter/lib/services/database_service.dart` (SESSION 39)

Nouvelle méthode `getAllCollectedDataForCoherence()` qui charge **toutes les données** pour un contexte (camp+etab+qst) **sans restriction de filtre (période)**. Les clés sont formées comme `"FIELD_NAME"` (sans filtre) ou `"FIELD_NAME#FILTER_ID"` (avec filtre).

Nécessaire car les SQL de cohérence serveur font `SUM(CHAMP) WHERE CODE_ETAB=X AND CODE_ANNEE=Y` (sans restriction de période), donc le moteur offline doit sommer les données de toutes les périodes.

#### `stateduc_flutter/lib/services/coherence_evaluator.dart` (SESSION 39)

Utilise `getAllCollectedDataForCoherence()` à la place de `getCollectedData()` pour le chargement des données persistées. Ajout de logs `debugPrint()` détaillés à chaque étape (champs chargés, valeurs V1/V2 extraites, résultat opérateur, violations).

### Fichiers modifiés — Session 39

| Fichier | Changement |
|---------|-----------|
| `StatEduc_burundi/data_rules.php` | **SESSION 39** : Nouvelle fonction `rules_resolve_theme_id()` avec validation DB multi-longueur, logs diagnostiques |
| `stateduc_flutter/lib/services/database_service.dart` | **SESSION 39** : Nouvelle méthode `getAllCollectedDataForCoherence()` |
| `stateduc_flutter/lib/services/coherence_evaluator.dart` | **SESSION 39** : Utilise `getAllCollectedDataForCoherence()`, ajout logs debugPrint détaillés |

---

## Session 38 — Correctif formulaires mobiles (étapes 7–9) + indicateur chargement "Actualiser" (branche `ak_secure`)

### Symptômes persistants après session 37b
- ✅ Connexion mobile : OK
- ✅ Étapes 1–6 (localisations, regroupements, établissements, systèmes) : OK
- ❌ **Étapes 7–9 (formulaires de saisie) : toujours échouées** — "Sélectionnez un formulaire pour commencer la saisie" s'affiche vide
- ❌ **Bouton "Actualiser"** (écran "Charger une campagne") : aucun retour visuel pendant le chargement

### Cause racine A — Filtre `FRAME <> ''` dans `data_camp.php/theme_camp/` (**CRITIQUE**)

Le SQL de la route `theme_camp` contenait :
```sql
AND (DICO_THEME_SYSTEME.FRAME <> '')
```
Ce filtre **masquait tous les thèmes dont le champ FRAME est vide ou NULL**, c'est-à-dire les formulaires dont le fichier `.frame` n'a pas encore été pré-généré côté serveur. Résultat : `GetAll()` retournait un tableau vide → Flutter `getQuestions()` retournait `[]` → la boucle `for (final q in questions)` ne s'exécutait jamais → **0 formulaire téléchargé**.

**Correctif** : suppression du filtre `FRAME <> ''`. Le champ `FRAME` est maintenant inclus dans le SELECT retourné, permettant au client de gérer le cas vide.

### Cause racine B — Boucle de tri `while ($nb > $nbo)` : risque de boucle infinie

L'algorithme de tri de la chaîne `PRECEDENT` utilisait `while ($nb > $nbo)` sans protection. Si un élément `pre` pointait vers un `id` absent du résultat (chaîne brisée), `$nbo` n'atteignait jamais `$nb` → boucle infinie → PHP timeout → réponse vide côté Flutter.

**Correctif** : réécriture complète avec compteur de sécurité `$max_iter = $nb * $nb + 1` et ajout des éléments restants en fin de liste en cas de chaîne brisée.

### Cause racine C — `utf8_encode()` déprécié PHP 8.2

Les lignes 74 et 89 utilisaient `utf8_encode()`, déprécié depuis PHP 8.2 et supprimé en PHP 9. Sur certaines configurations, cela génère des avertissements qui corrompent la réponse JSON.

**Correctif** : remplacement par `mb_convert_encoding('UTF-8', 'ISO-8859-1')` avec fallback `iconv()` et dernier recours `utf8_encode()`.

### Cause racine D — `fetchServerCampaigns()` ne mettait pas `_loadingCampaigns` à `true`

La méthode `fetchServerCampaigns()` dans `campaign_provider.dart` vidait `_serverCampaigns` et appelait `notifyListeners()` mais **n'activait jamais `_loadingCampaigns = true`**. Le bouton "Actualiser" ne détectait donc jamais l'état de chargement et restait statique.

**Correctif** : ajout de `_loadingCampaigns = true` en début de méthode et `_loadingCampaigns = false` dans les trois branches (succès + 2 erreurs).

### Améliorations supplémentaires — `data_camp.php`

- Ajout de `error_log()` diagnostiques dans `theme_camp` : SQL construit, nb lignes retournées, premier thème trouvé, erreur DB éventuelle
- Vérification de `conn_dico` avant toute requête (retour `status_ko` propre si DB non disponible)
- Route `html_theme_camp` : retour `status_ko` explicite si `FRAME` est vide/NULL (au lieu d'une URL malformée)

### Fichiers modifiés — Session 38

| Fichier | Changement |
|---------|-----------|
| `data_camp.php` | **REÉCRIT** : suppression filtre `FRAME <> ''`, inclusion FRAME dans SELECT, réécriture boucle de tri anti-boucle-infinie, remplacement `utf8_encode()`, logs diagnostiques, robustesse `html_theme_camp` FRAME vide |
| `stateduc_flutter/lib/providers/campaign_provider.dart` | `fetchServerCampaigns()` : activation/désactivation de `_loadingCampaigns` |
| `stateduc_flutter/lib/screens/campaigns/load_campaign_screen.dart` | `_buildEmpty()` : bouton "Actualiser" avec spinner + label "Chargement…" + désactivation pendant le fetch |

---

## Session 37b — Correctif collation SQL Server CI × bcrypt (branche `ak_secure`)

### Contexte
La base de données est sur **SQL Server** (pas Access). Le champ `PASSWORD` est déjà `VARCHAR(100)` — pas de troncature. La vraie cause racine du 401 sur `data_camp.php` est la **collation SQL Server Case Insensitive**.

### Cause racine — Collation SQL Server CI altère le hash bcrypt

SQL Server avec collation `French_CI_AS` ou `SQL_Latin1_General_CP1_CI_AS` (collation **CI** = Case Insensitive) peut normaliser la casse des valeurs `VARCHAR` lors de la lecture via le driver ADOdb `mssqlnative`. Le hash bcrypt (`$2y$12$BKWYlzy...`) contient des lettres majuscules/minuscules **significatives** (base64 modifié, case-sensitive). Si le driver retourne le hash avec la casse altérée, `password_verify()` retourne `false` → HTTP 401.

### Double correctif appliqué

#### Correctif 1 — PHP (déjà déployé) : `CONVERT ... COLLATE Latin1_General_CS_AS` dans le SELECT
Les trois fonctions `valide_user()`, `valide_user_ws()`, `infos_user_ws()` utilisent maintenant :
```sql
SELECT CONVERT(VARCHAR(100), PASSWORD) COLLATE Latin1_General_CS_AS AS PASSWORD
FROM ADMIN_USERS WHERE NOM_USER = '...'
```
Ce `COLLATE CS_AS` (Case Sensitive) au niveau de la requête force la restitution byte-pour-byte du hash, quel que soit la collation du serveur/base/champ.

#### Correctif 2 — SQL Server (script fourni) : ALTER TABLE champ PASSWORD en `COLLATE Latin1_General_CS_AS`
```sql
ALTER TABLE ADMIN_USERS
    ALTER COLUMN PASSWORD VARCHAR(100) COLLATE Latin1_General_CS_AS NOT NULL;
```
Script complet : `server-side/sql/alter_password_field_sqlserver.sql` (diagnostic, ALTER, vérification, test PHP).

### Fichiers modifiés — Session 37b

| Fichier | Changement |
|---------|-----------|
| `server-side/lib/fonctions.inc.php` | `valide_user()` + `valide_user_ws()` + `infos_user_ws()` : `SELECT PASSWORD` → `SELECT CONVERT(VARCHAR(100), PASSWORD) COLLATE Latin1_General_CS_AS AS PASSWORD` |
| `server-side/sql/alter_password_field_sqlserver.sql` | **NOUVEAU** — Script SQL Server complet : diagnostic collation, `ALTER TABLE` champ `PASSWORD` en `Latin1_General_CS_AS`, vérification, test PHP |

---

## Session 37 — Correctif complet : formulaires mobiles non téléchargés après migration bcrypt (branche `ak_secure`)

### Symptôme persistant après session 36
La connexion mobile fonctionnait, la liste des campagnes s'affichait, mais lors du téléchargement d'une campagne :
- ✅ Étapes 1–6 (localisations, établissements, systèmes) : OK via `user_camp.php` (HttpAuth désactivé)
- ❌ Étapes 7–9 (questions/thèmes, HTML formulaires, règles) : ÉCHEC via `data_camp.php` (HttpAuth **actif**)

### Causes racines identifiées — Session 37

#### Cause A — Champ `PASSWORD` trop petit dans la base Access (CRITIQUE)
Un hash bcrypt PHP mesure **60 caractères** (`$2y$12$...`). Si le champ `PASSWORD` de la table `ADMIN_USERS` dans `dico_DB.mdb/.accdb` est défini en `TEXT(32)` ou `TEXT(50)`, Microsoft Access **tronque silencieusement** le hash lors de l'enregistrement. Résultat : `password_verify()` retourne toujours `false` → HTTP 401 permanent.

**Correctif** : agrandir le champ à `TEXT(255)` via Access GUI ou DDL. Script fourni : `server-side/sql/alter_password_field_access.sql`.

#### Cause B — Hashes MD5 legacy non migrés
Si certains utilisateurs ont encore un hash MD5 (32 chars hexadécimaux) jamais mis à jour, `password_verify()` échoue systématiquement (bcrypt vs MD5 incompatibles).

**Correctif** : `valide_user_ws()` et `infos_user_ws()` supportent désormais 4 cas :
1. **Hash bcrypt complet** (60 chars, `$2y$`/`$2a$`) → `password_verify()`
2. **Hash MD5 legacy** (32 chars hex) → `md5()` compare → **auto-migration vers bcrypt** en base
3. **Hash bcrypt tronqué** (< 60 chars, commence par `$2y$`) → `error_log()` + refus (champ Access trop petit)
4. **Format inconnu** → `error_log()` + refus sécurisé

#### Cause C — `common_ws.php` `read_and_close` détruisait toutes les `$_SESSION` (session 34 regression)
`@session_start(['read_and_close' => true])` ouvre la session en **lecture seule**. Toutes les écritures `$_SESSION['langue']`, `$_SESSION['annee']`, etc. effectuées juste après étaient **silencieusement ignorées**. Les variables n'étaient jamais persistées.

**Correctif** : remplacement par `if (session_status() === PHP_SESSION_NONE) { session_start(); }` + `session_write_close()` explicite **après** toutes les initialisations. Protection anti-deadlock XAMPP maintenue par le close explicite.

#### Cause D — Double `session_start()` dans `user_camp.php` masquait le bug (session 34 side-effect)
La ligne 1 `<?php session_start()` dans `user_camp.php` démarrait une session normale. Le second appel `@session_start(['read_and_close' => true])` dans `common_ws.php` était ignoré (déjà ouvert). Résultat : `user_camp.php` obtenait une session normale par accident, `data_camp.php` (sans son propre `session_start`) héritait du `read_and_close` → explication exacte du comportement asymétrique étapes 1–6 OK / étapes 7+ KO.

**Correctif** : suppression du `session_start()` dupliqué de `user_camp.php` ligne 1.

### Fichiers modifiés — Session 37

| Fichier | Changement |
|---------|-----------|
| `server-side/lib/fonctions.inc.php` | `valide_user_ws()` + `infos_user_ws()` : réécriture robuste 4 cas (bcrypt/MD5/tronqué/inconnu) avec auto-migration MD5→bcrypt |
| `common_ws.php` | `@session_start(['read_and_close' => true])` → `if (session_status() === PHP_SESSION_NONE) { session_start(); }` + `session_write_close()` après init |
| `user_camp.php` | Suppression du `<?php session_start();` dupliqué ligne 1 |
| `server-side/sql/alter_password_field_access.sql` | Nouveau fichier : instructions pour agrandir le champ `PASSWORD` à `TEXT(255)` dans Access (3 méthodes : GUI, DDL, VBA) |

### Résumé technique de la chaîne de chargement mobile (9 étapes)
- Étapes 1–6 : `user_camp.php` (HttpAuth désactivé) — fonctionnaient avant, fonctionnent toujours ✅
- Étape 7 `theme_camp/` : `data_camp.php` (HttpAuth **actif**) — `valide_user_ws()` retournait `false` → 401 → **maintenant corrigé** ✅
- Étape 8 `html_theme_camp/` : `data_camp.php` (HttpAuth actif) — idem ✅
- Étape 9 `regle_theme_camp/` : `data_camp.php` (HttpAuth actif) — idem ✅

---

## Session 36 — Correctif : Régression chargement formulaires après migration bcrypt (branche `ak_secure`)

### Symptôme
Après la migration md5→bcrypt (session 35), la connexion mobile fonctionnait, mais le chargement des formulaires de campagne s'arrêtait après l'étape 5/9 (localisations). L'écran affichait « Sélectionnez un formulaire pour commencer la saisie » sans formulaire chargé.

### Cause racine identifiée — Double bug dans `valide_user_ws()`

#### Bug 1 (principal) — Conflit `read_and_close` × écriture session
`common_ws.php` utilise `@session_start(['read_and_close' => true])` depuis la session 34 (correction deadlock XAMPP Windows). Dans ce mode, la session est ouverte en **lecture seule** — toute écriture `$_SESSION[...]` est silencieusement ignorée.

L'ancien `valide_user_ws()` écrivait `$_SESSION['groupe']` puis vérifiait `isset($_SESSION['groupe'])` pour retourner `true`. Avec `read_and_close`, cette écriture n'avait aucun effet → `isset()` retournait `false` → la fonction retournait `false` même après un `password_verify()` réussi → **HTTP 401 sur tous les endpoints `data_camp.php`**.

Cela explique pourquoi `user_camp.php` (localisations — étape 5) fonctionnait : `$app->add(new \HttpAuth())` est **commenté** dans `user_camp.php` — aucune authentification requise. En revanche, `data_camp.php` (formulaires — étapes 7+) a `$app->add(new \HttpAuth())` **actif** → toutes les requêtes passaient par `valide_user_ws()` → 401.

#### Bug 2 (secondaire) — `ctype_alnum` trop restrictif dans `HttpAuth::authenticate()`
Le garde `if(!ctype_alnum($username))` bloquait tout login contenant un tiret, un underscore, un point ou une arobase. Remplacé par `empty($username) || empty($password)`.

### Fichiers corrigés

| Fichier | Changement |
|---------|-----------|
| `server-side/lib/fonctions.inc.php` | `valide_user_ws()` : suppression des écritures `$_SESSION` + vérification `isset()` ; remplacé par `return true` direct après `password_verify()` |
| `server-side/include/web_services/HttpAuth.php` | `authenticate()` : `ctype_alnum($username)` → `empty($username) \|\| empty($password)` |

### Résumé de la chaîne de chargement mobile (9 étapes)
- Étapes 1–4 : regroups, types de groupes, statuts écoles, écoles → `user_camp.php` (HttpAuth désactivé) ✅
- Étape 5 : localisations `locs_camp/` → `user_camp.php` (HttpAuth désactivé) ✅
- Étape 6 : systèmes éducatifs `sys_camp/` → `user_camp.php` (HttpAuth désactivé) ✅
- Étape 7+ : questions `theme_camp/` + HTML formulaires `html_theme_camp/` → `data_camp.php` (HttpAuth **actif**) ← **bloqué avant ce correctif**
- Étape 8 : règles `regle_theme_camp/` → `data_camp.php` (HttpAuth actif) ← **bloqué avant ce correctif**

---

## Session 35 — Sécurité : Migration md5 → bcrypt (branche `ak_secure`)

### Contexte
MD5 est un algorithme de hachage **cryptographiquement cassé** (collisions, rainbow tables, GPU cracking). Tous les mots de passe utilisateurs stockés en MD5 dans `ADMIN_USERS.PASSWORD` sont migrés vers **bcrypt** (`PASSWORD_BCRYPT`, cost=12), via les fonctions natives PHP `password_hash()` / `password_verify()`.

### Stratégie technique
- **Stockage** : `password_hash($mdp, PASSWORD_BCRYPT)` → hash `$2y$12$...` (60 chars)
- **Vérification** : au lieu de `WHERE PASSWORD = md5(input)`, on charge le hash par login seul, puis `password_verify(input, hash)`
- **Aucun changement côté Flutter** : l'app mobile envoyait déjà le mot de passe en clair dans l'URL REST et le header HTTP Basic. C'est le serveur qui faisait le md5. La suppression du md5 serveur est transparente pour le client mobile.

### Fichiers modifiés

| Fichier | Changement |
|---------|-----------|
| `server-side/lib/fonctions.inc.php` | `valide_user()`, `valide_user_ws()`, `infos_user_ws()` : requête SQL par login seul + `password_verify()` |
| `common.php` | Suppression de `md5($_POST['password'])` dans l'appel `valide_user()` |
| `user_ident.php` | Route `/user/` : suppression `md5($password)` ; route `/user_new_pwd/` : `password_hash($newpwd, PASSWORD_BCRYPT)` |
| `server-side/include/web_services/HttpAuth.php` | Suppression `$password_md5 = md5($password)` ; vérification déléguée à `valide_user_ws()` |
| `server-side/classes/metier/user.class.php` | Import Excel + formulaire création/modification : `md5()` → `password_hash($mdp, PASSWORD_BCRYPT)` |
| `server-side/sql/create_admin_nasser_bcrypt.sql` | **NOUVEAU** — Script SQL création admin `nasser` / `nasser@2026` en bcrypt |

### Script admin
```sql
-- Mot de passe : nasser@2026
-- Hash bcrypt $2y$12$ (PHP PASSWORD_BCRYPT, cost=12)
UPDATE ADMIN_USERS SET PASSWORD='$2y$12$BKWYlzyZuR5GrapX6c2ApuoxHONZ6GEGANd3ZA3DmaDe76LGYVGV2', CODE_GROUPE=1 WHERE NOM_USER='nasser';
```

### ⚠️ Important — Migration des utilisateurs existants
Les mots de passe existants stockés en MD5 **ne fonctionneront plus** après déploiement.  
Chaque utilisateur doit réinitialiser son mot de passe via l'interface de gestion (`administration.php?val=gestionuser`).  
Voir le script complet : `server-side/sql/create_admin_nasser_bcrypt.sql`

---

## Table des matières

1. [Architecture REST — Slim v2](#1-architecture-rest--slim-v2)
2. [Correction — page administration.php?val=param](#2-correction--page-administrationphpvalparam)
3. [Correction — AJAX gestion_base_service.php](#3-correction--ajax-gestion_base_servicephp)
4. [Correction — switch de connexion (toggle_sources)](#4-correction--switch-de-connexion-toggle_sources)
5. [Nouveau endpoint — data_save.php (route mobile)](#5-nouveau-endpoint--data_savephp-route-mobile)
6. [Nouveau endpoint — data_controle.php](#6-nouveau-endpoint--data_controlephp)
7. [Nouveau endpoint — data_rules.php](#7-nouveau-endpoint--data_rulesphp)
8. [Classes métier — contrôle de cohérence](#8-classes-métier--contrôle-de-cohérence)
9. [common.php — configuration des connexions](#9-commonphp--configuration-des-connexions)
10. [Stratégie de session pour l'app mobile](#10-stratégie-de-session-pour-lapp-mobile)

---

## 1. Architecture REST — Slim v2

### Framework
Tous les endpoints REST utilisent le micro-framework **Slim v2**. Chaque fichier PHP instancie `new \Slim\Slim()` et déclare ses routes.

### Règle Slim importante
> **Les routes plus spécifiques doivent être déclarées AVANT les routes génériques.**

Exemple dans `data_save.php` :
```php
// Route étendue (mobile, avec id_annee) — déclarée EN PREMIER
$app->post('/theme_save/:user/.../:id_annee', function(...) { ... });

// Route standard (web, sans id_annee) — déclarée EN SECOND
$app->post('/theme_save/:user/...', function(...) { ... });
```
Si la route générique est déclarée en premier, Slim la matche avant la route spécifique, et le paramètre `id_annee` n'est jamais capturé.

### Pattern de réponse standard
Tous les endpoints retournent le même enveloppe JSON :
```json
{
  "se_status":  200,
  "se_message": "OK",
  "se_data":    { ... }
}
```
- `se_status 200` = succès
- `se_status 400` = erreur métier (données manquantes, établissement non autorisé, etc.)
- `se_status 101` = erreur (utilisé dans gestion_base_service.php)

---

## 2. Correction — page `administration.php?val=param`

### Symptôme
Quand on naviguait vers `administration.php?val=param`, la page affichait du texte brut comme :
```
SELECT LIBELLE, CODE_LIBELLE FROM DICO_LIBELLE_PAGE WHERE CODE_LANGUE='fr';
{"se_statut":200, ...}
```
Et la page ne se rendu pas correctement.

### Cause racine — Bug 1 : ADOdb debug mode
**Fichier** : `StatEduc_MEN_2025/common.php`, ligne 626 (branche `postgres9`)

```php
// AVANT — actif, causait l'affichage SQL dans des <pre> :
$conn_dico = ADONewConnection('postgres9');
$conn_dico->debug = true;

// APRÈS — commenté :
$conn_dico = ADONewConnection('postgres9');
//$conn_dico->debug = true;
```

**Mécanisme** : Quand `$conn->debug = true` est actif, ADOdb (via `_adodb_debug_execute()` dans `adodb-lib.inc.php`) enveloppe **chaque requête SQL** dans des balises `<pre align=left>…</pre>` et les envoie directement sur la sortie HTTP — avant même que le HTML de la page soit émis. Résultat : toutes les requêtes SQL exécutées par `lit_libelles_page()` s'affichent en clair en tête de page.

### Détails techniques
- `lit_libelles_page()` dans `server-side/lib/fonctions.inc.php` (lignes 118–133) utilise `$GLOBALS['conn_dico']->GetAll()` pour charger les libellés
- Cette connexion `conn_dico` est créée dans `common.php` en cas de connexion via la branche `postgres9`
- Le `debug = true` était actif dans cette branche (et **seulement** cette branche), contrairement aux commentaires dans `connexion.class.php` où il était déjà commenté

---

## 3. Correction — AJAX `gestion_base_service.php`

### Symptôme
Quand la page `administration.php?val=param` se chargeait, une alerte JavaScript s'affichait avec :
```
SELECT LIBELLE... {"se_statut":200,...}
```
C'est-à-dire du SQL concatené avec le JSON de réponse.

### Cause racine — Bug 2 : pollution de sortie JSON
**Fichier** : `StatEduc_MEN_2025/server-side/include/administration/gestion_base_service.php`

L'endpoint AJAX jQuery (`dataType:'json'`) recevait une réponse corrompue : le JSON était précédé du texte SQL généré par ADOdb debug. jQuery ne pouvait pas parser le JSON → le callback `error:` s'activait → `alert(XMLHttpRequest.responseText)` affichait la réponse brute.

### Correction
Ajout du pattern **output buffering** :

```php
// En tête du fichier :
<?php
ob_start();  // Capture toute sortie parasite avant le JSON

header('Content-type: application/json');
require_once '../../../common.php';

// Dans chaque helper d'envoi :
function sendList($liste) {
    ob_clean();  // Vide le buffer (supprime SQL debug ou warnings PHP)
    $posts = array('se_statut'=>200,'se_message'=>'ok','se_datas'=>$liste);
    echo json_encode($posts);
}

function sendError($message) {
    ob_clean();
    $posts = array('se_statut'=>101,'se_message'=>$message,'se_datas'=>NULL);
    echo json_encode($posts);
}

function sendOk() {
    ob_clean();
    $posts = array('se_statut'=>200,'se_message'=>'ok','se_datas'=>'ok');
    echo json_encode($posts);
}
```

**Principe** : `ob_start()` intercepte toute sortie (SQL debug, notices PHP) avant qu'elle parte vers le client. `ob_clean()` vide ce buffer juste avant d'émettre le JSON → garantit une réponse JSON pure.

---

## 4. Correction — switch de connexion (`toggle_sources`)

### Symptôme
Basculer entre les connexions disponibles (bouton dans `administration.php?val=param`) ne fonctionnait pas — la page ne se rechargait pas correctement.

### Cause racine — Bug 3 : conséquence directe des bugs 1 et 2
La logique JavaScript de `toggle_sources()` dans `info_param.php` (ligne 202) est :
```javascript
function toggle_sources(serveur) {
    location.href = '?val=param&serveur=' + serveur;
}
```
Et côté PHP (lignes 61–63 de `info_param.php`) :
```php
if (isset($_GET['serveur'])) {
    $active['type'] = $_GET['serveur'];
}
```
Cette logique est **correcte**. Le problème était que la sortie SQL debug en tête de page cassait le rendu HTML, rendant les boutons dysfonctionnels.

**Correction** : En corrigeant les bugs 1 et 2, le switch de connexion fonctionne automatiquement.

---

## 5. Nouveau endpoint — `data_save.php` (route mobile)

### Problème initial
L'application mobile envoyait des données de formulaire mais elles n'étaient pas sauvegardées. Cause : la route Slim standard `POST /theme_save/...` utilise `$_SESSION['annee']` pour récupérer le code d'année scolaire, mais l'app mobile ne maintient pas de session PHP (pas de cookie de session).

### Solution : route étendue avec `id_annee`

**Fichier** : `StatEduc_MEN_2025/data_save.php`

#### Nouvelle route (ligne 172)
```php
// Route étendue (app mobile) — inclut id_annee pour fonctionner sans session navigateur
$app->post('/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start/:id_annee',
  function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start, $id_annee)
  use (...) {
    // Injecte id_annee dans la session si elle est vide
    if (!isset($_SESSION['annee']) || $_SESSION['annee'] == '') {
        $_SESSION['annee'] = $id_annee;
    }
    theme_save_handler(..., $id_annee, ...);
  }
);
```

#### Refactoring : extraction de `theme_save_handler()`
Pour éviter la duplication de code entre les deux routes, la logique de sauvegarde a été extraite dans une fonction `theme_save_handler()`. Les deux routes appellent cette même fonction.

#### Quatre fixes dans `theme_save_handler()`
1. **`getSurveyStatus`** : passe maintenant `$id_year` au lieu de `$_SESSION['annee']`
2. **`success` closure** : capture `$id_year` via `use (..., $id_year, ...)`
3. **`saveLogInfo`** dans le succès : passe `$id_year`
4. **`error` closure** : capture `$id_year` pour le log d'erreur

#### Route standard (ligne 179)
La route originale est conservée pour la compatibilité avec le navigateur web :
```php
$app->post('/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start',
  function (...) use (...) {
    $id_annee = isset($_SESSION['annee']) ? $_SESSION['annee'] : '';
    theme_save_handler(..., $id_annee, ...);
  }
);
```

---

## 6. Nouveau endpoint — `data_controle.php`

### Objectif
Permettre à l'application mobile d'effectuer un **contrôle de cohérence des données** après chaque sauvegarde, en utilisant les règles définies dans `DICO_REGLE_THEME` et `DICO_REGLE_THEME_ASSOC`.

### Fichier créé : `StatEduc_MEN_2025/data_controle.php`

### Route
```
GET /data_controle.php/theme_controle/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}
```

### Paramètres
| Paramètre | Description |
|---|---|
| `user` | Login utilisateur (vérification d'accès à la campagne) |
| `id_camp` | Identifiant de la campagne |
| `id_sector` | Identifiant du secteur (système éducatif) |
| `id_theme` | Identifiant du thème / formulaire |
| `id_etab` | Code établissement |
| `id_filter` | Période de filtre (ou `"null"`) |
| `id_annee` | Code d'année scolaire (injecté dans `$_SESSION['annee']`) |

### Réponse JSON
```json
{
  "se_status": 200,
  "se_message": "OK",
  "se_data": {
    "nb_erreurs": 2,
    "erreurs": [
      {
        "id_regle": 12,
        "id_regle_assoc": 7,
        "message": "Effectif total ≠ somme tranches d'âge : 120 > 98"
      }
    ]
  }
}
```

### Choix de la classe `controle_theme_batch`
La classe `controle_theme.class.php` (méthode `controle_regles_theme()`) génère du HTML et du JavaScript directement dans la réponse — incompatible avec une API REST JSON.

La classe `controle_theme_batch.class.php` stocke les violations dans un tableau PHP (`$tab_regles_theme_assoc_not_ok`) **sans émettre de sortie**. C'est cette classe qui est utilisée.

### Contrôle d'accès
Avant d'exécuter le contrôle, l'endpoint vérifie que l'établissement appartient bien à un regroupement autorisé pour cet utilisateur et cette campagne (requête sur `DICO_FIXE_REGROUPEMENT`).

### Compatibilité
- PHP 7.3.4 garanti (pas de syntaxe PHP 8+)
- Slim v2 (same as all other endpoints)

---

## 7. Nouveau endpoint — `data_rules.php`

### Objectif
Exposer les règles de cohérence d'un thème dans leur forme interpolée (variables PHP substituées) pour qu'elles puissent être stockées localement.

> **Note** : Cet endpoint a été créé dans le cadre d'une réflexion sur un contrôle offline. Il est conservé dans le code serveur pour usage futur, mais l'application mobile n'utilise pas cet endpoint actuellement — elle utilise exclusivement `data_controle.php` pour le contrôle de cohérence.

### Fichier créé : `StatEduc_MEN_2025/data_rules.php`

### Route
```
GET /data_rules.php/theme_rules/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}
```

### Réponse JSON
```json
{
  "se_status": 200,
  "se_message": "OK",
  "se_data": {
    "id_theme": 3,
    "nb_regles": 2,
    "regles": [
      {
        "id_regle": 5,
        "lib_regle": "Effectif garçons",
        "sql_regle": "SELECT SUM(NB_G) FROM COLLECT_PRIM WHERE CODE_ETAB='ECO001' AND CODE_ANNEE=2024",
        "associations": [
          {
            "id_assoc": 12,
            "id_regle_assoc": 7,
            "lib_regle_assoc": "Effectif total",
            "sql_assoc": "SELECT SUM(TOTAL) FROM ...",
            "critere": "<=",
            "message": "Effectif garçons doit être <= effectif total"
          }
        ]
      }
    ]
  }
}
```

### Interpolation SQL via `eval()`
Comme dans `controle_theme.class.php::get_regles()`, les variables dans le SQL brut (`SQL_REGLE_THEME`) sont substituées via `eval()` :
```php
// Variables définies avant eval() :
$code_etablissement = $id_etab;
$code_annee         = $id_annee;
$code_filtre        = $id_filter;

// Substitution (remplace ${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} etc.) :
eval("\$sql=\"$sql_regle_raw\";");
```

---

## 8. Classes métier — contrôle de cohérence

### Fichiers concernés (lecture seule — non modifiés)

#### `server-side/classes/metier/controle_theme.class.php`
- `get_regles()` (lignes 239–320) : lecture de `DICO_REGLE_THEME` + `DICO_REGLE_THEME_ASSOC`, interpolation SQL via `eval()`
- `controle_regles_theme()` (lignes 331–700) : exécute les règles et émet du HTML/JS d'alerte — **utilisé uniquement dans l'interface web**

#### `server-side/classes/metier/controle_theme_batch.class.php`
- Version batch sans sortie HTML/JS
- Stocke les violations dans `$tab_regles_theme_assoc_not_ok`
- **Utilisé par `data_controle.php`** pour l'API REST mobile

### Tables de données concernées

| Table | Rôle |
|---|---|
| `DICO_REGLE_THEME` | Définit chaque règle (SQL, libellé) |
| `DICO_REGLE_THEME_ASSOC` | Associe deux règles avec un opérateur (`CRITERE`) |
| `DICO_TRADUCTION` | Libellés traduits pour les messages de violation |

---

## 9. `common.php` — configuration des connexions

### Fichier : `StatEduc_MEN_2025/common.php`

#### Modification ligne 626 (branche `postgres9`)
```php
// AVANT :
$conn_dico = ADONewConnection('postgres9');
$conn_dico->debug = true;

// APRÈS :
$conn_dico = ADONewConnection('postgres9');
//$conn_dico->debug = true;
```

#### Contexte
`common.php` est le point d'entrée de toutes les pages de l'application. Il configure les connexions ADOdb (MySQL et PostgreSQL) selon le fichier `config.php`. La branche `postgres9` est suivie quand `$curcnx['type'] == 'postgres9'`.

#### ADOdb debug mode — comportement
Quand `$conn->debug = true` est actif :
- Chaque appel à `GetAll()`, `GetRow()`, `Execute()`, etc. passe par `_adodb_debug_execute()` dans `adodb-lib.inc.php`
- La fonction `outp()` (ligne 782 de `adodb.inc.php`) appelle `echo $msg . "<br>\n"` directement
- Ces lignes HTML/SQL sont émises **avant** tout header ou output buffering

#### Valeur par défaut ADOdb
`$conn->debug = false` est la valeur par défaut déclarée à la ligne 472 de `adodb.inc.php`. La modification dans `common.php` ramène simplement la connexion `conn_dico` à ce comportement normal.

---

## 10. Stratégie de session pour l'app mobile

### Problème
Le serveur PHP utilise `$_SESSION['annee']` pour connaître l'année scolaire courante. Ce mécanisme repose sur un cookie de session PHP (`PHPSESSID`) maintenu par le navigateur entre les requêtes.

L'application mobile utilise Dio (HTTP client) sans cookie de session persistant → `$_SESSION['annee']` est vide à chaque requête.

### Solution implémentée
Chaque endpoint REST critique accepte `id_annee` comme paramètre URL optionnel :

| Endpoint | Paramètre ajouté | Ligne |
|---|---|---|
| `data_save.php` | `/:id_annee` (route étendue) | 172 |
| `data_controle.php` | `/:id_annee` | Route principale |
| `data_rules.php` | `/:id_annee` | Route principale |

Le pattern PHP côté serveur :
```php
// En début de route :
if (!isset($_SESSION['annee']) || $_SESSION['annee'] == '') {
    $_SESSION['annee'] = $id_annee;
}
// Puis utilisation :
$id_year = ($id_annee != '' && $id_annee != '0')
    ? $id_annee
    : (isset($_SESSION['annee']) ? $_SESSION['annee'] : '');
```

### Côté mobile (Flutter)
`User.codeyear` est renseigné lors de l'authentification (réponse de `user_ident.php`). Il est transmis comme dernier segment URL dans chaque requête save/contrôle.

---

## Commits associés

| SHA | Message |
|---|---|
| `381de3e` | fix(admin): suppress ADOdb debug echo + guard AJAX JSON against output pollution |
| `b544819` | fix(save/coherence): complete data-not-saved fix + coherence control for mobile |
| `7bb5ac3` | serveur add code |

## Fichiers modifiés / créés

| Fichier | Statut | Description |
|---|---|---|
| `common.php` | **modifié** | Ligne 626 : `$conn_dico->debug = true` → commenté |
| `server-side/include/administration/gestion_base_service.php` | **modifié** | `ob_start()` + `ob_clean()` dans les helpers JSON |
| `data_save.php` | **modifié** | Route étendue `/:id_annee` + refactoring `theme_save_handler()` + 4 fixes `$id_year` |
| `data_controle.php` | **créé** | Endpoint REST contrôle de cohérence pour app mobile |
| `data_rules.php` | **créé** | Endpoint REST exposition des règles interpolées (usage futur) |
