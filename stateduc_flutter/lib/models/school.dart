/// School (Établissement) model — unified field names matching:
///   - JS source:  StmEtab(id, code, nom, status, idRgp); idregroup (lowercase) from server
///   - DB schema:  schools (id_camp, id_etab, lib_etab, code_etab, id_status, id_regroup)
///   - Server JSON: { id, code, nom, status, idregroup }
///
/// From etabs.js:
///   stmEtabs.addEtab(value) → new StmEtab(value.id, value.code, value.nom, value.status, value.idregroup)
///   NOTE: server uses lowercase "idregroup"!

class School {
  final String idEtab;     // server: id,        DB: id_etab
  final String? codeEtab;  // server: code,      DB: code_etab (admin code)
  final String libEtab;    // server: nom,        DB: lib_etab
  final String? idStatus;  // server: status,     DB: id_status
  final String? idRegroup; // server: idregroup,  DB: id_regroup (LOWERCASE from server!)
  final String? libStatus;    // resolved status label e.g. "Public"
  final String? libHierarchy; // geographic hierarchy e.g. "AGADEZ / ADERBISANAT"

  School({
    required this.idEtab,
    this.codeEtab,
    required this.libEtab,
    this.idStatus,
    this.idRegroup,
    this.libStatus,
    this.libHierarchy,
  });

  /// Parses server JSON from /user_camp.php/etabs_camp/{userId}/{campId}/1
  factory School.fromJson(Map<String, dynamic> json) {
    return School(
      idEtab:       (json['id'] ?? json['id_etab'] ?? '').toString(),
      codeEtab:     json['code']?.toString() ?? json['code_etab']?.toString(),
      libEtab:      (json['nom'] ?? json['lib_etab'] ?? '').toString(),
      idStatus:     json['status']?.toString() ?? json['id_status']?.toString(),
      // Server sends 'idregroup' (lowercase) — see etabs.js line: value.idregroup
      idRegroup:    json['idregroup']?.toString() ?? json['id_regroup']?.toString(),
      libStatus:    json['lib_status']?.toString() ?? json['statut']?.toString(),
      libHierarchy: json['lib_hierarchy']?.toString() ?? json['hierarchy']?.toString(),
    );
  }

  /// Returns a copy with libStatus and libHierarchy resolved.
  School copyWith({String? libStatus, String? libHierarchy}) {
    return School(
      idEtab:       idEtab,
      codeEtab:     codeEtab,
      libEtab:      libEtab,
      idStatus:     idStatus,
      idRegroup:    idRegroup,
      libStatus:    libStatus ?? this.libStatus,
      libHierarchy: libHierarchy ?? this.libHierarchy,
    );
  }

  Map<String, dynamic> toJson() => {
    'id_etab':     idEtab,
    'code_etab':   codeEtab,
    'lib_etab':    libEtab,
    'id_status':   idStatus,
    'id_regroup':  idRegroup,
    'lib_status':  libStatus,
    'lib_hierarchy': libHierarchy,
  };
}

/// Localisation — links a school to a system and its regroup chain.
///
/// From charge_camp.js addLocs():
///   locs_camp response: each item has idloc, idsys, etabs (CSV), regroups (CSV or array)
///   We expand multi-etab rows into individual Localisation objects per etab.
///
/// DB schema: localisations(id_camp, id_system, id_etab, regroups_json)
class Localisation {
  final String idEtab;       // DB: id_etab
  final String idSystem;     // DB: id_system
  final String regroupsJson; // DB: regroups_json — JSON array string '[id1, id2, ...]'

  Localisation({
    required this.idEtab,
    required this.idSystem,
    required this.regroupsJson,
  });
}

/// Expands a single raw locs_camp item (which may have multiple etabs)
/// into one [Localisation] per etab. Called when parsing /user_camp.php/locs_camp response.
///
/// Raw item structure (from the JS locs storage):
///   { idsys, etabs: "id1,id2,...", regroups: "id1,id2,..." OR [id1,id2,...] }
List<Localisation> localisationsFromRawJson(Map<String, dynamic> raw) {
  final idSys = (raw['idsys'] ?? raw['id_system'] ?? raw['sys'] ?? '').toString();

  // etabs can be a comma-separated string or a list
  final etabsRaw = raw['etabs'];
  final List<String> etabIds;
  if (etabsRaw is List) {
    etabIds = etabsRaw.map((e) => e.toString()).where((s) => s.isNotEmpty).toList();
  } else {
    etabIds = (etabsRaw?.toString() ?? '')
        .split(',')
        .map((s) => s.trim())
        .where((s) => s.isNotEmpty)
        .toList();
  }

  // regroups can be a comma-separated string or a list
  final regroupsRaw = raw['regroups'];
  final List<String> regroupIds;
  if (regroupsRaw is List) {
    regroupIds = regroupsRaw.map((e) => e.toString()).where((s) => s.isNotEmpty).toList();
  } else {
    regroupIds = (regroupsRaw?.toString() ?? '')
        .split(',')
        .map((s) => s.trim())
        .where((s) => s.isNotEmpty)
        .toList();
  }

  // Build JSON string for storage: '["id1","id2"]'
  final regroupsJson = '[${regroupIds.map((id) => '"$id"').join(',')}]';

  return etabIds
      .map((etabId) => Localisation(
            idEtab: etabId,
            idSystem: idSys,
            regroupsJson: regroupsJson,
          ))
      .toList();
}

/// School status model.
///
/// From charge_camp.js addStatus():
///   new StmStatus(value.id, value.name)
///   Server JSON: { id, name }
class SchoolStatus {
  final String idStatus;   // server: id,   DB: id_status
  final String libStatus;  // server: name, DB: lib_status

  SchoolStatus({required this.idStatus, required this.libStatus});

  factory SchoolStatus.fromJson(Map<String, dynamic> json) {
    return SchoolStatus(
      idStatus:  (json['id'] ?? json['id_status'] ?? '').toString(),
      libStatus: (json['name'] ?? json['lib_status'] ?? '').toString(),
    );
  }

  Map<String, dynamic> toJson() => {
    'id_status':  idStatus,
    'lib_status': libStatus,
  };
}
