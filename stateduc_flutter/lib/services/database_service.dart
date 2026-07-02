import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import '../models/campaign.dart';
import '../services/api_service.dart';
import '../models/regroup.dart';
import '../models/school.dart';
import '../models/education_system.dart';
import '../models/question.dart';
import '../models/user.dart';

/// DatabaseService — Couche d'accès aux données SQLite de l'application StatEduc Mobile.
///
/// Remplace les 25+ clés localStorage de l'application web originale par des tables SQLite.
/// Implémenté comme SINGLETON : une seule instance partagée par tous les providers.
///
/// CORRESPONDANCE localStorage → SQLite :
///   stm_User, stm_Year, stm_Filter       → settings (clé/valeur)
///   stm_Campagnes                         → campaigns
///   stm_Localisations                     → localisations
///   stm_Systems                           → education_systems
///   stm_TypeRegroups                      → regroup_types
///   stm_Regroups                          → regroups
///   stm_Etabs                             → schools
///   stm_Status                            → school_statuses
///   stm_Questions                         → questions
///   stm_Rules                             → validation_rules
///   stm_EtabCollectData_{etab}_{qst}[_{filter}] → collected_data
///
/// VERSION DE LA BASE :
///   v1 : schéma initial
///   v2 : ajout colonne sort_order dans questions
///   v3 : ajout table coherence_rules (session 14 — contrôles offline)
///
/// TABLE CRITIQUE — coherence_rules :
///   Stocke les règles téléchargées depuis data_rules.php pour l'évaluation offline.
///   Indexée par (id_camp, id_qst, id_etab) pour recherche rapide.
///
/// TABLE CRITIQUE — collected_data :
///   Stocke toutes les saisies de l'agent de collecte (field_name → field_value).
///   Clé unique : (id_camp, id_etab, id_qst, COALESCE(id_filter,''), field_name)
///   Champ is_sent=1 après envoi serveur réussi.
class DatabaseService {
  static final DatabaseService _instance = DatabaseService._internal();
  factory DatabaseService() => _instance;
  DatabaseService._internal();

  Database? _db;

  Future<Database> get database async {
    _db ??= await _initDatabase();
    return _db!;
  }

  Future<Database> _initDatabase() async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, 'stateduc.db');
    return await openDatabase(
      path,
      version: 3,
      onCreate: _onCreate,
      onUpgrade: _onUpgrade,
      onOpen: (db) async {
        await db.execute('PRAGMA foreign_keys = ON');
      },
    );
  }

  Future<void> _onUpgrade(Database db, int oldVersion, int newVersion) async {
    if (oldVersion < 2) {
      // v2: add sort_order column to questions table
      try {
        await db.execute(
          'ALTER TABLE questions ADD COLUMN sort_order INTEGER NOT NULL DEFAULT 0',
        );
      } catch (_) {
        // Column may already exist if DB was recreated
      }
    }
    if (oldVersion < 3) {
      // v3: coherence_rules table for offline coherence evaluation
      await _createCoherenceRulesTable(db);
    }
  }

  Future<void> _onCreate(Database db, int version) async {
    await db.execute('PRAGMA foreign_keys = ON');

    // ─── Settings (replaces loose localStorage items) ─────────────────────
    await db.execute('''
      CREATE TABLE settings (
        key   TEXT PRIMARY KEY,
        value TEXT
      )
    ''');

    // ─── Campaigns (stm_Campagnes) ─────────────────────────────────────────
    await db.execute('''
      CREATE TABLE campaigns (
        id_camp         TEXT PRIMARY KEY,
        lib_camp        TEXT NOT NULL,
        date_debut      TEXT,
        date_fin        TEXT,
        statut          INTEGER NOT NULL DEFAULT 0,
        type_regroups   TEXT NOT NULL DEFAULT '',
        id_year         TEXT,
        lib_year        TEXT,
        synced          INTEGER NOT NULL DEFAULT 0
      )
    ''');

    // ─── Education systems (stm_Systems) ──────────────────────────────────
    await db.execute('''
      CREATE TABLE education_systems (
        id_camp     TEXT NOT NULL,
        id_system   TEXT NOT NULL,
        lib_system  TEXT NOT NULL,
        PRIMARY KEY (id_camp, id_system)
      )
    ''');

    // ─── Regroup types (stm_TypeRegroups) ─────────────────────────────────
    await db.execute('''
      CREATE TABLE regroup_types (
        id_camp         TEXT NOT NULL,
        id_type_regp    TEXT NOT NULL,
        lib_type_regp   TEXT NOT NULL,
        PRIMARY KEY (id_camp, id_type_regp)
      )
    ''');

    // ─── Regroups (stm_Regroups) ──────────────────────────────────────────
    await db.execute('''
      CREATE TABLE regroups (
        id_camp         TEXT NOT NULL,
        id_regp         TEXT NOT NULL,
        lib_regp        TEXT NOT NULL,
        id_type_regp    TEXT NOT NULL,
        id_parent_regp  TEXT,
        PRIMARY KEY (id_camp, id_regp)
      )
    ''');
    await db.execute('''
      CREATE INDEX idx_regroups_parent
        ON regroups (id_camp, id_parent_regp)
    ''');

    // ─── School statuses (stm_Status) ─────────────────────────────────────
    await db.execute('''
      CREATE TABLE school_statuses (
        id_camp     TEXT NOT NULL,
        id_status   TEXT NOT NULL,
        lib_status  TEXT NOT NULL,
        PRIMARY KEY (id_camp, id_status)
      )
    ''');

    // ─── Schools / Établissements (stm_Etabs) ─────────────────────────────
    await db.execute('''
      CREATE TABLE schools (
        id_camp     TEXT NOT NULL,
        id_etab     TEXT NOT NULL,
        lib_etab    TEXT NOT NULL,
        code_etab   TEXT,
        id_status   TEXT,
        id_regroup  TEXT,
        PRIMARY KEY (id_camp, id_etab)
      )
    ''');
    await db.execute('''
      CREATE INDEX idx_schools_camp ON schools (id_camp)
    ''');

    // ─── Localisations (stm_Localisations) ────────────────────────────────
    // Links camp + system + regroups + school
    await db.execute('''
      CREATE TABLE localisations (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        id_camp     TEXT NOT NULL,
        id_system   TEXT NOT NULL,
        id_etab     TEXT NOT NULL,
        regroups_json TEXT NOT NULL
      )
    ''');
    await db.execute('''
      CREATE INDEX idx_locs_camp_system
        ON localisations (id_camp, id_system)
    ''');
    await db.execute('''
      CREATE INDEX idx_locs_camp_etab
        ON localisations (id_camp, id_etab)
    ''');

    // ─── Questions / Themes (stm_Questions) ───────────────────────────────
    await db.execute('''
      CREATE TABLE questions (
        id_camp     TEXT NOT NULL,
        id_qst      TEXT NOT NULL,
        lib_qst     TEXT NOT NULL,
        id_system   TEXT NOT NULL,
        has_filter  INTEGER NOT NULL DEFAULT 0,
        sort_order  INTEGER NOT NULL DEFAULT 0,
        PRIMARY KEY (id_camp, id_qst)
      )
    ''');

    // ─── Form HTML cache ───────────────────────────────────────────────────
    await db.execute('''
      CREATE TABLE form_html (
        id_camp   TEXT NOT NULL,
        id_qst    TEXT NOT NULL,
        html      TEXT NOT NULL,
        PRIMARY KEY (id_camp, id_qst)
      )
    ''');

    // ─── Validation rules (stm_Rules) ─────────────────────────────────────
    await db.execute('''
      CREATE TABLE validation_rules (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        id_camp         TEXT NOT NULL,
        id_qst          TEXT NOT NULL,
        id_champ        TEXT NOT NULL,
        rule_type       TEXT NOT NULL,
        rule_value      TEXT
      )
    ''');
    await db.execute('''
      CREATE INDEX idx_rules_qst
        ON validation_rules (id_camp, id_qst)
    ''');

    // ─── Collected data (stm_EtabCollectData_{etab}_{qst}[_{filter}]) ─────
    // Key pattern: id_camp + id_etab + id_qst + id_filter (nullable) + field_name → value
    await db.execute('''
      CREATE TABLE collected_data (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        id_camp     TEXT NOT NULL,
        id_etab     TEXT NOT NULL,
        id_qst      TEXT NOT NULL,
        id_filter   TEXT,
        field_name  TEXT NOT NULL,
        field_value TEXT,
        is_sent     INTEGER NOT NULL DEFAULT 0,
        updated_at  TEXT NOT NULL
      )
    ''');
    await db.execute('''
      CREATE UNIQUE INDEX idx_collected_data_key
        ON collected_data (id_camp, id_etab, id_qst, COALESCE(id_filter,''), field_name)
    ''');
    await db.execute('''
      CREATE INDEX idx_collected_data_etab_qst
        ON collected_data (id_camp, id_etab, id_qst)
    ''');

    // ─── Filter periods (StmFilter from default.js) ───────────────────────
    await db.execute('''
      CREATE TABLE filter_periods (
        id_camp     TEXT NOT NULL,
        id_filter   TEXT NOT NULL,
        lib_filter  TEXT NOT NULL,
        PRIMARY KEY (id_camp, id_filter)
      )
    ''');

    // ─── Coherence rules (offline evaluation) ─────────────────────────────
    await _createCoherenceRulesTable(db);
  }

  Future<void> _createCoherenceRulesTable(Database db) async {
    await db.execute('''
      CREATE TABLE IF NOT EXISTS coherence_rules (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        id_camp         TEXT NOT NULL,
        id_qst          TEXT NOT NULL,
        id_etab         TEXT NOT NULL,
        id_filter       TEXT,
        id_regle        INTEGER NOT NULL,
        lib_regle       TEXT NOT NULL DEFAULT '',
        sql_regle       TEXT NOT NULL,
        id_assoc        INTEGER NOT NULL,
        id_regle_assoc  INTEGER NOT NULL,
        lib_regle_assoc TEXT NOT NULL DEFAULT '',
        sql_assoc       TEXT NOT NULL,
        critere         TEXT NOT NULL,
        message         TEXT NOT NULL DEFAULT '',
        fetched_at      TEXT NOT NULL
      )
    ''');
    await db.execute('''
      CREATE INDEX IF NOT EXISTS idx_coherence_rules_ctx
        ON coherence_rules (id_camp, id_qst, id_etab)
    ''');
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SETTINGS
  // ═══════════════════════════════════════════════════════════════════════════

  Future<String?> getSetting(String key) async {
    final db = await database;
    final rows = await db.query('settings', where: 'key = ?', whereArgs: [key]);
    if (rows.isEmpty) return null;
    return rows.first['value'] as String?;
  }

  Future<void> setSetting(String key, String value) async {
    final db = await database;
    await db.insert(
      'settings',
      {'key': key, 'value': value},
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<void> deleteSetting(String key) async {
    final db = await database;
    await db.delete('settings', where: 'key = ?', whereArgs: [key]);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // CAMPAIGNS
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Campaign>> getCampaigns() async {
    final db = await database;
    final rows = await db.query('campaigns', orderBy: 'lib_camp ASC');
    return rows.map((r) => Campaign(
      idCamp:       r['id_camp']       as String,
      libCamp:      r['lib_camp']      as String,
      dateDebut:    r['date_debut']    as String?,
      dateFin:      r['date_fin']      as String?,
      statut:       (r['statut'] as int?) ?? 0,
      typeRegroups: (r['type_regroups'] as String?) ?? '',
      idYear:       r['id_year']       as String?,
      libYear:      r['lib_year']      as String?,
    )).toList();
  }

  Future<Campaign?> getCampaign(String idCamp) async {
    final db = await database;
    final rows = await db.query(
      'campaigns',
      where: 'id_camp = ?',
      whereArgs: [idCamp],
    );
    if (rows.isEmpty) return null;
    final r = rows.first;
    return Campaign(
      idCamp:       r['id_camp']       as String,
      libCamp:      r['lib_camp']      as String,
      dateDebut:    r['date_debut']    as String?,
      dateFin:      r['date_fin']      as String?,
      statut:       (r['statut'] as int?) ?? 0,
      typeRegroups: (r['type_regroups'] as String?) ?? '',
      idYear:       r['id_year']       as String?,
      libYear:      r['lib_year']      as String?,
    );
  }

  Future<void> insertCampaign(Campaign c) async {
    final db = await database;
    await db.insert(
      'campaigns',
      {
        'id_camp':       c.idCamp,
        'lib_camp':      c.libCamp,
        'date_debut':    c.dateDebut,
        'date_fin':      c.dateFin,
        'statut':        c.statut,
        'type_regroups': c.typeRegroups,
        'id_year':       c.idYear,
        'lib_year':      c.libYear,
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<void> deleteCampaign(String idCamp) async {
    final db = await database;
    await db.transaction((txn) async {
      await txn.delete('campaigns', where: 'id_camp = ?', whereArgs: [idCamp]);
      await txn.delete('education_systems', where: 'id_camp = ?', whereArgs: [idCamp]);
      await txn.delete('regroup_types', where: 'id_camp = ?', whereArgs: [idCamp]);
      await txn.delete('regroups', where: 'id_camp = ?', whereArgs: [idCamp]);
      await txn.delete('school_statuses', where: 'id_camp = ?', whereArgs: [idCamp]);
      await txn.delete('schools', where: 'id_camp = ?', whereArgs: [idCamp]);
      await txn.delete('localisations', where: 'id_camp = ?', whereArgs: [idCamp]);
      await txn.delete('questions', where: 'id_camp = ?', whereArgs: [idCamp]);
      await txn.delete('form_html', where: 'id_camp = ?', whereArgs: [idCamp]);
      await txn.delete('validation_rules', where: 'id_camp = ?', whereArgs: [idCamp]);
      await txn.delete('filter_periods', where: 'id_camp = ?', whereArgs: [idCamp]);
      await txn.delete('coherence_rules', where: 'id_camp = ?', whereArgs: [idCamp]);
      // Note: collected_data is kept for safety — call deleteCollectedData separately if needed
    });
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // EDUCATION SYSTEMS
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<EducationSystem>> getEducationSystems(String idCamp) async {
    final db = await database;
    final rows = await db.query(
      'education_systems',
      where: 'id_camp = ?',
      whereArgs: [idCamp],
      orderBy: 'lib_system ASC',
    );
    return rows.map((r) => EducationSystem(
      idSystem: r['id_system'] as String,
      libSystem: r['lib_system'] as String,
    )).toList();
  }

  Future<void> insertEducationSystems(
      String idCamp, List<EducationSystem> systems) async {
    final db = await database;
    final batch = db.batch();
    for (final s in systems) {
      batch.insert(
        'education_systems',
        {'id_camp': idCamp, 'id_system': s.idSystem, 'lib_system': s.libSystem},
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    await batch.commit(noResult: true);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // REGROUP TYPES
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<RegroupType>> getRegroupTypes(String idCamp) async {
    final db = await database;
    final rows = await db.query(
      'regroup_types',
      where: 'id_camp = ?',
      whereArgs: [idCamp],
    );
    return rows.map((r) => RegroupType(
      idTypeRegp: r['id_type_regp'] as String,
      libTypeRegp: r['lib_type_regp'] as String,
    )).toList();
  }

  Future<void> insertRegroupTypes(
      String idCamp, List<RegroupType> types) async {
    final db = await database;
    final batch = db.batch();
    for (final t in types) {
      batch.insert(
        'regroup_types',
        {
          'id_camp': idCamp,
          'id_type_regp': t.idTypeRegp,
          'lib_type_regp': t.libTypeRegp,
        },
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    await batch.commit(noResult: true);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // REGROUPS (administrative entities)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Regroup>> getRegroups(String idCamp) async {
    final db = await database;
    final rows = await db.query(
      'regroups',
      where: 'id_camp = ?',
      whereArgs: [idCamp],
      orderBy: 'lib_regp ASC',
    );
    return rows.map((r) => Regroup(
      idRegp: r['id_regp'] as String,
      libRegp: r['lib_regp'] as String,
      idTypeRegp: r['id_type_regp'] as String,
      idParentRegp: r['id_parent_regp'] as String?,
    )).toList();
  }

  /// Returns direct children of [parentId] for the given camp.
  ///
  /// When [parentId] is null → returns ROOT regroups (id_parent_regp IS NULL).
  ///
  /// Robustness: if null query returns nothing, also tries matching the string
  /// '-1' (some servers may store the sentinel as a string instead of NULL)
  /// and the empty string ''.
  Future<List<Regroup>> getChildRegroups(
      String idCamp, String? parentId) async {
    final db = await database;
    List<Map<String, Object?>> rows;
    if (parentId == null) {
      rows = await db.query(
        'regroups',
        where: 'id_camp = ? AND id_parent_regp IS NULL',
        whereArgs: [idCamp],
        orderBy: 'lib_regp ASC',
      );
      debugPrint('[DB] getChildRegroups camp=$idCamp parentId=NULL '
          '→ ${rows.length} rows (IS NULL query)');

      // Fallback: server may have stored '-1', '0', or '' instead of NULL
      if (rows.isEmpty) {
        rows = await db.rawQuery(
          "SELECT * FROM regroups WHERE id_camp = ? AND "
          "(id_parent_regp = '-1' OR id_parent_regp = '0' OR id_parent_regp = '' OR id_parent_regp IS NULL) "
          "ORDER BY lib_regp ASC",
          [idCamp],
        );
        debugPrint('[DB] getChildRegroups camp=$idCamp parentId=NULL '
            '→ ${rows.length} rows (fallback -1/0/empty query)');
      }

      // Last resort: if still empty, just return ALL regroups for this camp
      // so the user can at least navigate
      if (rows.isEmpty) {
        rows = await db.query(
          'regroups',
          where: 'id_camp = ?',
          whereArgs: [idCamp],
          orderBy: 'lib_regp ASC',
          limit: 50,
        );
        debugPrint('[DB] ⚠ getChildRegroups camp=$idCamp parentId=NULL '
            '→ ${rows.length} rows (ALL regroups last resort)');
      }
    } else {
      rows = await db.query(
        'regroups',
        where: 'id_camp = ? AND id_parent_regp = ?',
        whereArgs: [idCamp, parentId],
        orderBy: 'lib_regp ASC',
      );
      debugPrint('[DB] getChildRegroups camp=$idCamp parentId=$parentId '
          '→ ${rows.length} rows');
    }
    return rows.map((r) => Regroup(
      idRegp: r['id_regp'] as String,
      libRegp: r['lib_regp'] as String,
      idTypeRegp: r['id_type_regp'] as String,
      idParentRegp: r['id_parent_regp'] as String?,
    )).toList();
  }

  Future<void> insertRegroups(String idCamp, List<Regroup> regroups) async {
    final db = await database;
    final batch = db.batch();
    for (final r in regroups) {
      batch.insert(
        'regroups',
        {
          'id_camp': idCamp,
          'id_regp': r.idRegp,
          'lib_regp': r.libRegp,
          'id_type_regp': r.idTypeRegp,
          'id_parent_regp': r.idParentRegp,
        },
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    await batch.commit(noResult: true);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SCHOOL STATUSES
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<SchoolStatus>> getSchoolStatuses(String idCamp) async {
    final db = await database;
    final rows = await db.query(
      'school_statuses',
      where: 'id_camp = ?',
      whereArgs: [idCamp],
    );
    return rows.map((r) => SchoolStatus(
      idStatus: r['id_status'] as String,
      libStatus: r['lib_status'] as String,
    )).toList();
  }

  Future<void> insertSchoolStatuses(
      String idCamp, List<SchoolStatus> statuses) async {
    final db = await database;
    final batch = db.batch();
    for (final s in statuses) {
      batch.insert(
        'school_statuses',
        {
          'id_camp': idCamp,
          'id_status': s.idStatus,
          'lib_status': s.libStatus,
        },
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    await batch.commit(noResult: true);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SCHOOLS / ÉTABLISSEMENTS
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<School>> getSchools(String idCamp) async {
    final db = await database;
    final rows = await db.query(
      'schools',
      where: 'id_camp = ?',
      whereArgs: [idCamp],
      orderBy: 'lib_etab ASC',
    );
    return rows.map((r) => School(
      idEtab:    r['id_etab']    as String,
      libEtab:   r['lib_etab']   as String,
      codeEtab:  r['code_etab']  as String?,
      idStatus:  r['id_status']  as String?,
      idRegroup: r['id_regroup'] as String?,
    )).toList();
  }

  /// Returns schools linked to [idRegp] for the given campaign/system.
  ///
  /// Uses a two-strategy approach to maximise resilience:
  ///
  /// **Strategy 1 — via `localisations.regroups_json`**
  ///   Each localisation row has a JSON array of regroup IDs that cover the
  ///   user's full chain (parents + leaves). We look for any row whose array
  ///   contains [idRegp] and collect the corresponding etab IDs.
  ///   The server may store elements as integers OR quoted strings — we
  ///   normalise to string for comparison.
  ///
  /// **Strategy 2 — direct `schools.id_regroup` match (fallback)**
  ///   If Strategy 1 returns nothing (the clicked regroup is not in any
  ///   `regroups_json`, e.g. because the server omitted an intermediate node),
  ///   we fall back to a direct SQL query on `schools.id_regroup = idRegp`.
  ///   This covers the common case where the school row itself carries its
  ///   direct regroup ID from `etabs_camp`.
  ///
  /// **Strategy 3 — all schools for the campaign (last resort)**
  ///   If both strategies return nothing (possible data issue), we return ALL
  ///   schools for the campaign so the user is never stuck on an empty screen.
  Future<List<School>> getSchoolsByRegroup(
      String idCamp, String idSystem, String idRegp) async {
    final db = await database;

    // Special sentinel: '__all__' → skip strategies 1 & 2, return all schools
    if (idRegp == '__all__') {
      debugPrint('[DB] getSchoolsByRegroup __all__ → returning all schools '
          'for camp=$idCamp');
      final allRows = await db.query(
        'schools',
        where: 'id_camp = ?',
        whereArgs: [idCamp],
        orderBy: 'lib_etab ASC',
      );
      return allRows.map((r) => School(
        idEtab:    r['id_etab']    as String,
        libEtab:   r['lib_etab']   as String,
        codeEtab:  r['code_etab']  as String?,
        idStatus:  r['id_status']  as String?,
        idRegroup: r['id_regroup'] as String?,
      )).toList();
    }

    // ── Strategy 1: via localisations.regroups_json ────────────────────────
    final locRows = await db.query(
      'localisations',
      where: 'id_camp = ? AND id_system = ?',
      whereArgs: [idCamp, idSystem],
    );

    debugPrint('[DB] getSchoolsByRegroup('
        'camp=$idCamp, sys=$idSystem, regp=$idRegp) '
        '→ ${locRows.length} localisation rows found');

    // Log a sample to diagnose id_system mismatches
    if (locRows.isNotEmpty) {
      final sample = locRows.first;
      debugPrint('[DB] Sample loc row: id_etab=${sample['id_etab']}, '
          'id_system=${sample['id_system']}, '
          'regroups_json=${(sample['regroups_json'] as String? ?? '').length > 120 ? (sample['regroups_json'] as String).substring(0, 120) + "…" : sample['regroups_json']}');
    } else {
      // No rows → check if localisations exist at all for this camp
      final anyLoc = await db.query(
        'localisations',
        where: 'id_camp = ?',
        whereArgs: [idCamp],
        limit: 3,
      );
      debugPrint('[DB] ⚠ No localisation rows for sys=$idSystem. '
          'Total rows for camp=$idCamp: ${anyLoc.length}. '
          'Sample systems: ${anyLoc.map((r) => r['id_system']).toSet()}');
    }

    final etabIds = <String>{};
    for (final loc in locRows) {
      final jsonStr = loc['regroups_json'] as String? ?? '[]';
      try {
        final List<dynamic> regroupIds = jsonDecode(jsonStr) as List<dynamic>;
        if (regroupIds.any((id) => id.toString() == idRegp)) {
          etabIds.add(loc['id_etab'] as String);
        }
      } catch (_) {
        // JSON parse failed — use raw string search for both quoted/unquoted
        if (jsonStr.contains('"$idRegp"') ||
            jsonStr.contains("'$idRegp'") ||
            jsonStr.contains(',$idRegp,') ||
            jsonStr.contains('[$idRegp,') ||
            jsonStr.contains(',$idRegp]') ||
            jsonStr == '[$idRegp]') {
          etabIds.add(loc['id_etab'] as String);
        }
      }
    }

    debugPrint('[DB] Strategy 1 → ${etabIds.length} etab IDs matched '
        'via regroups_json for regp=$idRegp');

    if (etabIds.isNotEmpty) {
      final placeholders = etabIds.map((_) => '?').join(',');
      final rows = await db.query(
        'schools',
        where: 'id_camp = ? AND id_etab IN ($placeholders)',
        whereArgs: [idCamp, ...etabIds],
        orderBy: 'lib_etab ASC',
      );
      debugPrint('[DB] Strategy 1 → ${rows.length} schools returned');
      return rows.map((r) => School(
        idEtab:    r['id_etab']    as String,
        libEtab:   r['lib_etab']   as String,
        codeEtab:  r['code_etab']  as String?,
        idStatus:  r['id_status']  as String?,
        idRegroup: r['id_regroup'] as String?,
      )).toList();
    }

    // ── Strategy 2: direct schools.id_regroup match ────────────────────────
    debugPrint('[DB] Strategy 1 found nothing — trying Strategy 2: '
        'schools.id_regroup = $idRegp');

    final directRows = await db.query(
      'schools',
      where: 'id_camp = ? AND id_regroup = ?',
      whereArgs: [idCamp, idRegp],
      orderBy: 'lib_etab ASC',
    );

    debugPrint('[DB] Strategy 2 → ${directRows.length} schools '
        'with id_regroup=$idRegp');

    if (directRows.isNotEmpty) {
      return directRows.map((r) => School(
        idEtab:    r['id_etab']    as String,
        libEtab:   r['lib_etab']   as String,
        codeEtab:  r['code_etab']  as String?,
        idStatus:  r['id_status']  as String?,
        idRegroup: r['id_regroup'] as String?,
      )).toList();
    }

    // ── Strategy 3: all schools for this campaign (last resort) ────────────
    debugPrint('[DB] ⚠ Strategy 2 also empty — falling back to ALL schools '
        'for camp=$idCamp (last resort)');

    final allRows = await db.query(
      'schools',
      where: 'id_camp = ?',
      whereArgs: [idCamp],
      orderBy: 'lib_etab ASC',
    );

    debugPrint('[DB] Strategy 3 → ${allRows.length} schools total');
    return allRows.map((r) => School(
      idEtab:    r['id_etab']    as String,
      libEtab:   r['lib_etab']   as String,
      codeEtab:  r['code_etab']  as String?,
      idStatus:  r['id_status']  as String?,
      idRegroup: r['id_regroup'] as String?,
    )).toList();
  }

  Future<void> insertSchools(String idCamp, List<School> schools) async {
    final db = await database;
    final batch = db.batch();
    for (final s in schools) {
      batch.insert(
        'schools',
        {
          'id_camp':    idCamp,
          'id_etab':    s.idEtab,
          'lib_etab':   s.libEtab,
          'code_etab':  s.codeEtab,
          'id_status':  s.idStatus,
          'id_regroup': s.idRegroup,
        },
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    await batch.commit(noResult: true);
  }

  /// Enriches a list of schools with their [libStatus] resolved from the
  /// school_statuses table for the given campaign.
  ///
  /// Called after [getSchoolsByRegroup] to add human-readable status labels
  /// (e.g. "Public", "Privé") so they appear in the school header.
  Future<List<School>> resolveSchoolStatuses(
      String idCamp, List<School> schools) async {
    if (schools.isEmpty) return schools;
    // Build status map for O(1) lookup
    final statuses = await getSchoolStatuses(idCamp);
    final statusMap = <String, String>{
      for (final s in statuses) s.idStatus: s.libStatus,
    };
    return schools.map((school) {
      if (school.idStatus == null || statusMap.isEmpty) return school;
      final label = statusMap[school.idStatus!];
      if (label == null) return school;
      return school.copyWith(libStatus: label);
    }).toList();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // LOCALISATIONS
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Localisation>> getLocalisations(
      String idCamp, String idSystem) async {
    final db = await database;
    final rows = await db.query(
      'localisations',
      where: 'id_camp = ? AND id_system = ?',
      whereArgs: [idCamp, idSystem],
    );
    return rows.map((r) => Localisation(
      idEtab: r['id_etab'] as String,
      idSystem: r['id_system'] as String,
      regroupsJson: r['regroups_json'] as String,
    )).toList();
  }

  Future<void> insertLocalisations(
      String idCamp, List<Localisation> locs) async {
    final db = await database;
    final batch = db.batch();
    for (final l in locs) {
      batch.insert(
        'localisations',
        {
          'id_camp': idCamp,
          'id_system': l.idSystem,
          'id_etab': l.idEtab,
          'regroups_json': l.regroupsJson,
        },
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    await batch.commit(noResult: true);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // QUESTIONS / THEMES
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Question>> getQuestions(
      String idCamp, String idSystem) async {
    final db = await database;
    final rows = await db.query(
      'questions',
      where: 'id_camp = ? AND id_system = ?',
      whereArgs: [idCamp, idSystem],
      orderBy: 'sort_order ASC, lib_qst ASC',
    );
    return rows.map((r) => Question(
      idQst: r['id_qst'] as String,
      libQst: r['lib_qst'] as String,
      idSystem: r['id_system'] as String,
      hasFilter: (r['has_filter'] as int) == 1,
      sortOrder: (r['sort_order'] as int?) ?? 0,
    )).toList();
  }

  Future<void> insertQuestions(String idCamp, List<Question> questions) async {
    final db = await database;
    final batch = db.batch();
    for (int i = 0; i < questions.length; i++) {
      final q = questions[i];
      batch.insert(
        'questions',
        {
          'id_camp':    idCamp,
          'id_qst':     q.idQst,
          'lib_qst':    q.libQst,
          'id_system':  q.idSystem,
          'has_filter': q.hasFilter ? 1 : 0,
          'sort_order': q.sortOrder > 0 ? q.sortOrder : i,
        },
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    await batch.commit(noResult: true);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // FORM HTML CACHE
  // ═══════════════════════════════════════════════════════════════════════════

  Future<String?> getFormHtml(String idCamp, String idQst) async {
    final db = await database;
    final rows = await db.query(
      'form_html',
      where: 'id_camp = ? AND id_qst = ?',
      whereArgs: [idCamp, idQst],
    );
    if (rows.isEmpty) return null;
    return rows.first['html'] as String?;
  }

  Future<void> insertFormHtml(
      String idCamp, String idQst, String html) async {
    final db = await database;
    await db.insert(
      'form_html',
      {'id_camp': idCamp, 'id_qst': idQst, 'html': html},
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // VALIDATION RULES
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<ValidationRule>> getValidationRules(
      String idCamp, String idQst) async {
    final db = await database;
    final rows = await db.query(
      'validation_rules',
      where: 'id_camp = ? AND id_qst = ?',
      whereArgs: [idCamp, idQst],
    );
    return rows.map((r) => ValidationRule(
      idChamp: r['id_champ'] as String,
      ruleType: r['rule_type'] as String,
      ruleValue: r['rule_value'] as String?,
    )).toList();
  }

  Future<void> insertValidationRules(
      String idCamp, String idQst, List<ValidationRule> rules) async {
    final db = await database;
    // Delete old rules for this question first
    await db.delete(
      'validation_rules',
      where: 'id_camp = ? AND id_qst = ?',
      whereArgs: [idCamp, idQst],
    );
    final batch = db.batch();
    for (final r in rules) {
      batch.insert('validation_rules', {
        'id_camp': idCamp,
        'id_qst': idQst,
        'id_champ': r.idChamp,
        'rule_type': r.ruleType,
        'rule_value': r.ruleValue,
      });
    }
    await batch.commit(noResult: true);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // FILTER PERIODS (StmFilter from default.js)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<FilterPeriod>> getFilterPeriods(String idCamp) async {
    final db = await database;
    final rows = await db.query(
      'filter_periods',
      where: 'id_camp = ?',
      whereArgs: [idCamp],
    );
    return rows.map((r) => FilterPeriod(
      idFilter:  r['id_filter']  as String,
      libFilter: r['lib_filter'] as String,
    )).toList();
  }

  Future<void> insertFilterPeriods(
      String idCamp, List<FilterPeriod> filters) async {
    final db = await database;
    final batch = db.batch();
    for (final f in filters) {
      batch.insert(
        'filter_periods',
        {
          'id_camp': idCamp,
          'id_filter': f.idFilter,
          'lib_filter': f.libFilter,
        },
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    await batch.commit(noResult: true);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // COLLECTED DATA
  // Replaces: stm_EtabCollectData_{id_etab}_{id_qst}[_{id_filter}]
  // ═══════════════════════════════════════════════════════════════════════════

  /// Loads all field/value pairs for one school + question [+ optional filter].
  Future<Map<String, String>> getCollectedData({
    required String idCamp,
    required String idEtab,
    required String idQst,
    String? idFilter,
  }) async {
    final db = await database;
    List<Map<String, Object?>> rows;
    if (idFilter == null) {
      rows = await db.query(
        'collected_data',
        where:
            'id_camp = ? AND id_etab = ? AND id_qst = ? AND id_filter IS NULL',
        whereArgs: [idCamp, idEtab, idQst],
      );
    } else {
      rows = await db.query(
        'collected_data',
        where:
            'id_camp = ? AND id_etab = ? AND id_qst = ? AND id_filter = ?',
        whereArgs: [idCamp, idEtab, idQst, idFilter],
      );
    }
    final result = <String, String>{};
    for (final r in rows) {
      result[r['field_name'] as String] = r['field_value'] as String? ?? '';
    }
    return result;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SESSION 39 : getAllCollectedDataForCoherence
  //
  // Charge TOUTES les données collectées pour un contexte (camp+etab+qst)
  // SANS restriction de filtre (période). Utilisé par CoherenceEvaluator pour
  // reproduire le comportement du serveur : les SQL de cohérence font
  // SUM(CHAMP) WHERE CODE_ETAB=X AND CODE_ANNEE=Y, sans filtrer par période.
  // Retourne un Map { "FIELD_NAME#FILTER_ID" → value } pour les données filtrées
  // et { "FIELD_NAME" → value } pour les données sans filtre.
  // CoherenceEvaluator._sumFieldAcrossAllFilters() comprend les deux formats.
  // ═══════════════════════════════════════════════════════════════════════════
  Future<Map<String, String>> getAllCollectedDataForCoherence({
    required String idCamp,
    required String idEtab,
    required String idQst,
  }) async {
    final db = await database;
    final rows = await db.query(
      'collected_data',
      where: 'id_camp = ? AND id_etab = ? AND id_qst = ?',
      whereArgs: [idCamp, idEtab, idQst],
    );
    final result = <String, String>{};
    for (final r in rows) {
      final fieldName  = r['field_name']  as String;
      final fieldValue = r['field_value'] as String? ?? '';
      final filterId   = r['id_filter']   as String?;
      // Clé avec suffixe filtre si la donnée est périodique, sinon clé simple
      final key = (filterId != null && filterId.isNotEmpty)
          ? '$fieldName#$filterId'
          : fieldName;
      result[key] = fieldValue;
    }
    return result;
  }

  /// Saves (upsert) a single field value.
  Future<void> saveCollectedField({
    required String idCamp,
    required String idEtab,
    required String idQst,
    String? idFilter,
    required String fieldName,
    required String fieldValue,
  }) async {
    final db = await database;
    await db.insert(
      'collected_data',
      {
        'id_camp': idCamp,
        'id_etab': idEtab,
        'id_qst': idQst,
        'id_filter': idFilter,
        'field_name': fieldName,
        'field_value': fieldValue,
        'is_sent': 0,
        'updated_at': DateTime.now().toIso8601String(),
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  /// Saves a full map of field→value pairs in one transaction.
  Future<void> saveCollectedData({
    required String idCamp,
    required String idEtab,
    required String idQst,
    String? idFilter,
    required Map<String, String> data,
  }) async {
    final db = await database;
    final now = DateTime.now().toIso8601String();
    await db.transaction((txn) async {
      for (final entry in data.entries) {
        await txn.insert(
          'collected_data',
          {
            'id_camp': idCamp,
            'id_etab': idEtab,
            'id_qst': idQst,
            'id_filter': idFilter,
            'field_name': entry.key,
            'field_value': entry.value,
            'is_sent': 0,
            'updated_at': now,
          },
          conflictAlgorithm: ConflictAlgorithm.replace,
        );
      }
    });
  }

  /// Marks all data for a school+question as sent.
  Future<void> markCollectedDataSent({
    required String idCamp,
    required String idEtab,
    required String idQst,
    String? idFilter,
  }) async {
    final db = await database;
    if (idFilter == null) {
      await db.update(
        'collected_data',
        {'is_sent': 1},
        where:
            'id_camp = ? AND id_etab = ? AND id_qst = ? AND id_filter IS NULL',
        whereArgs: [idCamp, idEtab, idQst],
      );
    } else {
      await db.update(
        'collected_data',
        {'is_sent': 1},
        where: 'id_camp = ? AND id_etab = ? AND id_qst = ? AND id_filter = ?',
        whereArgs: [idCamp, idEtab, idQst, idFilter],
      );
    }
  }

  /// Returns true if there is any unsent data for the given school+question.
  Future<bool> hasUnsentData({
    required String idCamp,
    required String idEtab,
    required String idQst,
  }) async {
    final db = await database;
    final rows = await db.query(
      'collected_data',
      where: 'id_camp = ? AND id_etab = ? AND id_qst = ? AND is_sent = 0',
      whereArgs: [idCamp, idEtab, idQst],
      limit: 1,
    );
    return rows.isNotEmpty;
  }

  /// Retourne tous les couples distincts (id_etab, id_qst) qui ont des données
  /// collectées pour une campagne donnée (envoyées ou non).
  ///
  /// Utilisé par [sendAllFormsForCampaign] pour itérer sur tous les formulaires
  /// saisis, même si l'établissement courant n'est pas ouvert dans l'UI.
  Future<List<Map<String, String>>> getDistinctEtabQstWithData(
      String idCamp) async {
    final db = await database;
    // SELECT DISTINCT pour ne pas envoyer le même formulaire plusieurs fois
    // (plusieurs lignes dans collected_data par formulaire)
    final rows = await db.rawQuery(
      'SELECT DISTINCT id_etab, id_qst FROM collected_data WHERE id_camp = ?',
      [idCamp],
    );
    return rows
        .map((r) => {
              'id_etab': r['id_etab'] as String? ?? '',
              'id_qst': r['id_qst'] as String? ?? '',
            })
        .where((m) => m['id_etab']!.isNotEmpty && m['id_qst']!.isNotEmpty)
        .toList();
  }

  /// Deletes all collected data for a school+question (for reload from server).
  Future<void> deleteCollectedData({
    required String idCamp,
    required String idEtab,
    required String idQst,
    String? idFilter,
  }) async {
    final db = await database;
    if (idFilter == null) {
      await db.delete(
        'collected_data',
        where: 'id_camp = ? AND id_etab = ? AND id_qst = ?',
        whereArgs: [idCamp, idEtab, idQst],
      );
    } else {
      await db.delete(
        'collected_data',
        where:
            'id_camp = ? AND id_etab = ? AND id_qst = ? AND id_filter = ?',
        whereArgs: [idCamp, idEtab, idQst, idFilter],
      );
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // COHERENCE RULES — CRUD for offline evaluation
  // ═══════════════════════════════════════════════════════════════════════════

  /// Stores a batch of CoherenceRule objects for a given campaign+question+school.
  /// Replaces any previously stored rules for the same (id_camp, id_qst, id_etab)
  /// context so that a re-download always reflects the latest server rules.
  Future<void> insertCoherenceRules(List<CoherenceRule> rules) async {
    if (rules.isEmpty) return;
    final db = await database;

    // Determine contexts to replace (unique id_camp + id_qst + id_etab combos)
    final contexts = <String>{};
    for (final r in rules) {
      contexts.add('${r.idCamp}|${r.idQst}|${r.idEtab}');
    }

    await db.transaction((txn) async {
      // Delete old rules for each context
      for (final ctx in contexts) {
        final parts = ctx.split('|');
        await txn.delete(
          'coherence_rules',
          where: 'id_camp = ? AND id_qst = ? AND id_etab = ?',
          whereArgs: [parts[0], parts[1], parts[2]],
        );
      }
      // Insert new rules
      for (final r in rules) {
        await txn.insert(
          'coherence_rules',
          {
            'id_camp':          r.idCamp,
            'id_qst':           r.idQst,
            'id_etab':          r.idEtab,
            'id_filter':        r.idFilter,
            'id_regle':         r.idRegle,
            'lib_regle':        r.libRegle,
            'sql_regle':        r.sqlRegle,
            'id_assoc':         r.idAssoc,
            'id_regle_assoc':   r.idRegleAssoc,
            'lib_regle_assoc':  r.libRegleAssoc,
            'sql_assoc':        r.sqlAssoc,
            'critere':          r.critere,
            'message':          r.message,
            'fetched_at':       r.fetchedAt,
          },
          conflictAlgorithm: ConflictAlgorithm.replace,
        );
      }
    });
  }

  /// Returns all coherence rules for a given school+question context.
  /// Used by CoherenceEvaluator to run offline checks.
  Future<List<CoherenceRule>> getCoherenceRules({
    required String idCamp,
    required String idQst,
    required String idEtab,
  }) async {
    final db = await database;
    final rows = await db.query(
      'coherence_rules',
      where: 'id_camp = ? AND id_qst = ? AND id_etab = ?',
      whereArgs: [idCamp, idQst, idEtab],
    );
    return rows.map((r) => CoherenceRule(
      idCamp:        r['id_camp']          as String,
      idQst:         r['id_qst']           as String,
      idEtab:        r['id_etab']          as String,
      idFilter:      r['id_filter']        as String?,
      idRegle:       (r['id_regle'] as int?) ?? 0,
      libRegle:      (r['lib_regle'] as String?) ?? '',
      sqlRegle:      r['sql_regle']        as String,
      idAssoc:       (r['id_assoc'] as int?) ?? 0,
      idRegleAssoc:  (r['id_regle_assoc'] as int?) ?? 0,
      libRegleAssoc: (r['lib_regle_assoc'] as String?) ?? '',
      sqlAssoc:      r['sql_assoc']        as String,
      critere:       r['critere']          as String,
      message:       (r['message'] as String?) ?? '',
      fetchedAt:     r['fetched_at']       as String,
    )).toList();
  }

  /// Deletes all coherence rules for a given campaign+question+school context.
  Future<void> deleteCoherenceRules({
    required String idCamp,
    required String idQst,
    required String idEtab,
  }) async {
    final db = await database;
    await db.delete(
      'coherence_rules',
      where: 'id_camp = ? AND id_qst = ? AND id_etab = ?',
      whereArgs: [idCamp, idQst, idEtab],
    );
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // UTILITY
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> close() async {
    final db = _db;
    if (db != null) {
      await db.close();
      _db = null;
    }
  }

  /// Wipes the entire database (used during development / full reset).
  Future<void> clearAll() async {
    final db = await database;
    await db.transaction((txn) async {
      for (final table in [
        'coherence_rules',
        'collected_data',
        'filter_periods',
        'validation_rules',
        'form_html',
        'questions',
        'localisations',
        'schools',
        'school_statuses',
        'regroups',
        'regroup_types',
        'education_systems',
        'campaigns',
        'settings',
      ]) {
        await txn.delete(table);
      }
    });
  }
}
