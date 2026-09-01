# Patron Multi-Année — Isolation Complète (AK-YEAR-MULTI)

## Vue d'ensemble

Le patron **Multi-Année** permet à un seul déploiement de gérer plusieurs années
scolaires simultaneously. Les données de chaque année sont physiquement isolées
par une colonne `annee_code` dans chaque table métier et par une session
Flutter dédiée par année active.

### Principe fondamental
```
Une année active ≠ Une base de données séparée
Une année active = Un filtre WHERE systématique + une colonne de partition
```

---

## Phase 1 — Migration Base de Données (Access/ADODB)

### 1.1 Ajout colonne `annee_code`

Chaque table métier reçoit une colonne `annee_code` NOT NULL avec la valeur
par défaut de l'année courante.

```sql
-- Ordre à appliquer dans Access via ADODB ou DDL direct
-- Voir templates/sql/migration_multiannee.sql pour le script complet

ALTER TABLE etablissements ADD COLUMN annee_code VARCHAR(10) NOT NULL DEFAULT '2024';
ALTER TABLE questionnaires  ADD COLUMN annee_code VARCHAR(10) NOT NULL DEFAULT '2024';
ALTER TABLE reponses        ADD COLUMN annee_code VARCHAR(10) NOT NULL DEFAULT '2024';
ALTER TABLE enqueteurs      ADD COLUMN annee_code VARCHAR(10) NOT NULL DEFAULT '2024';

-- Index composite pour performance
CREATE INDEX idx_etab_annee ON etablissements (annee_code, id);
CREATE INDEX idx_quest_annee ON questionnaires (annee_code, etab_id);
```

### 1.2 Table `annees` (catalogue des années)

```sql
CREATE TABLE annees (
    code        VARCHAR(10)  PRIMARY KEY,   -- ex: "2024", "2025"
    libelle     VARCHAR(100) NOT NULL,       -- ex: "Année scolaire 2024-2025"
    active      TINYINT      NOT NULL DEFAULT 0,  -- 1 = année en session globale
    date_debut  DATE,
    date_fin    DATE
);

-- Contrainte : une seule année active à la fois (trigger Access)
-- Ou appliquer côté PHP : UPDATE annees SET active=0 WHERE active=1 BEFORE INSERT

INSERT INTO annees (code, libelle, active) VALUES ('2024', 'Année scolaire 2024-2025', 1);
```

### 1.3 Règle de cohérence
- `annees.active = 1` → année utilisée par défaut si Flutter ne précise pas
- L'app Flutter peut **surcharger** l'année active en passant `annee_code` dans les requêtes
- Le serveur **valide** que le `annee_code` envoyé existe dans `annees`

---

## Phase 2 — PHP Routing (Slim 2.x)

### 2.1 Extraction de `annee_code` dans chaque route

```php
// Pattern: chaque endpoint accepte /annee/{code} optionnel
// Exemple: GET /etablissements/annee/2024

$app->get('/etablissements(/annee/:annee_code)', function($annee_code = null) use ($app) {
    // 1. Résoudre l'année
    $annee = _resolveAnnee($annee_code); // voir pattern ci-dessous
    if (!$annee) {
        $app->response->setStatus(400);
        echo json_encode(['erreur' => 'Année invalide : ' . $annee_code]);
        return;
    }

    // 2. Requête filtrée par année
    $sql = "SELECT * FROM etablissements WHERE annee_code = ?";
    $rs  = $GLOBALS['conn']->Execute($sql, [$annee['code']]);
    // ...
});
```

### 2.2 Fonction `_resolveAnnee()`

```php
/**
 * Résout l'année à utiliser.
 * - Si $code fourni et valide → utilise ce code
 * - Si $code null ou invalide → utilise l'année active en base
 * - Si aucune année active → erreur 500
 *
 * @param  string|null $code
 * @return array|null  ['code'=>'2024','libelle'=>'...'] ou null si introuvable
 */
function _resolveAnnee($code = null) {
    $conn = $GLOBALS['conn'];

    if ($code !== null) {
        // Valider que l'année demandée existe
        $rs = $conn->Execute("SELECT code, libelle FROM annees WHERE code = ?", [$code]);
        if ($rs && !$rs->EOF) {
            return $rs->fields;
        }
        return null; // code invalide → l'appelant retourne 400
    }

    // Pas de code fourni → année active
    $rs = $conn->Execute("SELECT code, libelle FROM annees WHERE active = 1");
    if ($rs && !$rs->EOF) {
        return $rs->fields;
    }
    return null; // aucune année active → erreur 500
}
```

### 2.3 Endpoint actif — `annees_ws.php`

```php
// GET /active/{login} — retourne l'année active pour le login
$app->get('/active/:login', function($login) use ($app) {
    // Connexion spécifique (conn, pas conn_dico — voir BUG-ADODB-001)
    $conn = $GLOBALS['conn'];

    $rs = $conn->Execute("SELECT code, libelle FROM annees WHERE active = 1");
    if (!$rs || $rs->EOF) {
        $app->response->setStatus(404);
        echo json_encode(['erreur' => 'Aucune année active']);
        return;
    }

    // Réponse JSON (envelope standard — voir Pattern PHP-007)
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code'    => $rs->fields['CODE'],    // ADODB_ASSOC_CASE_UPPER
        'libelle' => $rs->fields['LIBELLE'],
    ]);
});
```

### 2.4 Passage d'`annee_code` dans `data_save.php`

L'app Flutter envoie le `annee_code` dans le corps JSON. `data_save.php` le
valide puis le passe à `questionnaire_ws.php` via cURL.

```php
// data_save.php — extraction et validation
$annee_code = $data['annee_code'] ?? null;
if (!$annee_code) {
    // Fallback : utiliser l'année active
    $rs = $conn->Execute("SELECT code FROM annees WHERE active = 1");
    $annee_code = $rs ? $rs->fields['CODE'] : null;
}
if (!$annee_code) {
    http_response_code(422);
    echo json_encode(['se_data' => 'Année introuvable']);
    exit;
}

// Passer à questionnaire_ws.php via GET param
$url_interne = SISED_AURL_INTERNAL . "/questionnaire_ws.php/save?annee=" . urlencode($annee_code);
```

---

## Phase 3 — Flutter — Gestion de l'Année Active

### 3.1 Modèle `SchoolYear`

```dart
// lib/models/school_year.dart
class SchoolYear {
  final String code;    // "2024"
  final String libelle; // "Année scolaire 2024-2025"
  final bool   active;  // vrai si c'est l'année serveur active

  const SchoolYear({
    required this.code,
    required this.libelle,
    this.active = false,
  });

  factory SchoolYear.fromJson(Map<String, dynamic> json) => SchoolYear(
    code:    (json['code']    ?? json['CODE']    ?? '').toString(),
    libelle: (json['libelle'] ?? json['LIBELLE'] ?? json['code'] ?? '').toString(),
    active:  json['active'] == 1 || json['active'] == true,
  );

  @override
  String toString() => 'SchoolYear($code, active=$active)';
}
```

### 3.2 `AuthProvider` — stockage de l'année en session

```dart
// lib/providers/auth_provider.dart — ajouts multi-année

class AuthProvider extends ChangeNotifier {
  SchoolYear? _activeYear;   // Année sélectionnée dans l'UI
  List<SchoolYear> _years = [];

  SchoolYear? get activeYear => _activeYear;
  List<SchoolYear> get years  => List.unmodifiable(_years);

  /// Appelé au login — charge les années disponibles et l'année active serveur
  Future<void> loadYears(ApiService api, String login) async {
    try {
      final serverYear = await api.fetchServerActiveYear(login);
      _activeYear = SchoolYear(
        code:    serverYear.code,
        libelle: serverYear.libelle,
        active:  true,
      );
      // TODO: charger la liste complète via un endpoint /annees
      _years = [_activeYear!];
      notifyListeners();
    } catch (e) {
      // Fail-open: garder l'année précédente en cache ou null
      debugPrint('[Auth] loadYears fail-open: $e');
    }
  }

  /// Changement volontaire d'année par l'utilisateur
  Future<void> changeActiveYear(SchoolYear year) async {
    _activeYear = year;
    notifyListeners();
    // Persister localement (SharedPreferences ou SQLite)
    await _persistActiveYear(year.code);
  }
}
```

### 3.3 Envoi d'`annee_code` dans chaque requête POST

```dart
// lib/services/api_service.dart — ajout dans sendData()

Future<String> sendData({
  required String login,
  required Map<String, dynamic> formData,
  required String anneeCode,    // ← OBLIGATOIRE depuis AuthProvider
}) async {
  final payload = {
    ...formData,
    'annee_code': anneeCode,    // ← injecté systématiquement
    'login':      login,
  };

  final response = await _dio.post(
    'data_save.php',
    data: jsonEncode(payload),
    options: Options(
      contentType: 'application/json',
      responseType: ResponseType.plain,
    ),
  );
  return response.data.toString();
}
```

### 3.4 Vérification de cohérence avant envoi (`_checkYearConsistency`)

Voir `references/04_flutter_dio_patterns.md` → Pattern FLUTTER-002 pour le
code complet **FAIL-OPEN**.

Règle : si `fetchServerActiveYear()` échoue (réseau KO), on **laisse passer**
les données. On ne bloque QUE si la réponse serveur est reçue ET l'année
diffère.

---

## Phase 4 — SQLite Cache Local (Flutter)

### 4.1 Table `cache_annees`

```sql
-- Dans la migration SQLite locale (database_helper.dart)
CREATE TABLE IF NOT EXISTS cache_annees (
    code        TEXT PRIMARY KEY,
    libelle     TEXT NOT NULL,
    active      INTEGER NOT NULL DEFAULT 0,
    synced_at   TEXT NOT NULL    -- ISO-8601
);
```

### 4.2 Hydratation et lecture du cache

```dart
// lib/services/database_helper.dart

Future<void> cacheYear(SchoolYear year) async {
  final db = await database;
  await db.insert(
    'cache_annees',
    {
      'code':      year.code,
      'libelle':   year.libelle,
      'active':    year.active ? 1 : 0,
      'synced_at': DateTime.now().toIso8601String(),
    },
    conflictAlgorithm: ConflictAlgorithm.replace,
  );
}

Future<SchoolYear?> getCachedActiveYear() async {
  final db = await database;
  final rows = await db.query(
    'cache_annees',
    where: 'active = 1',
    limit: 1,
  );
  if (rows.isEmpty) return null;
  return SchoolYear.fromJson(rows.first);
}

Future<List<SchoolYear>> getAllCachedYears() async {
  final db = await database;
  final rows = await db.query('cache_annees', orderBy: 'code DESC');
  return rows.map(SchoolYear.fromJson).toList();
}
```

### 4.3 Stratégie offline-first

```
Au démarrage de l'app:
  1. Tenter fetchServerActiveYear()
  2. Si succès → stocker dans cache + utiliser
  3. Si échec (réseau) → lire cache local
  4. Si cache vide → afficher avertissement "Mode hors-ligne, données non synchronisées"

À l'envoi de données:
  1. Toujours inclure annee_code dans payload
  2. _checkYearConsistency() avec fail-open
  3. En cas d'erreur réseau → écrire en file d'attente locale (table pending_syncs)
```

---

## Checklist d'implémentation

| Étape | Fichier | Tag |
|-------|---------|-----|
| ✅ Ajouter colonne `annee_code` toutes tables | `migration_multiannee.sql` | AK-YEAR-MULTI-DB |
| ✅ Créer table `annees` | `migration_multiannee.sql` | AK-YEAR-MULTI-DB |
| ✅ Endpoint `GET /active/:login` | `annees_ws.php` | AK-YEAR-MULTI-API |
| ✅ Fonction `_resolveAnnee()` | `common_ws.php` ou inline | AK-YEAR-MULTI-API |
| ✅ Passer `annee_code` dans `data_save.php` | `data_save.php` | AK-YEAR-MULTI-API |
| ✅ Modèle `SchoolYear` Flutter | `models/school_year.dart` | AK-YEAR-MULTI-FL |
| ✅ `fetchServerActiveYear()` 8s timeout | `api_service.dart` | AK-YEAR-MULTI-FL |
| ✅ `_checkYearConsistency()` fail-open | `data_entry_provider.dart` | AK-YEAR-MULTI-FL |
| ✅ ExpansionTile dropdown Settings | `settings_screen.dart` | AK-YEAR-MULTI-FL |
| ✅ SQLite cache `cache_annees` | `database_helper.dart` | AK-YEAR-MULTI-FL |
| ✅ Inclure `annee_code` dans tout POST | `api_service.dart` | AK-YEAR-MULTI-FL |
