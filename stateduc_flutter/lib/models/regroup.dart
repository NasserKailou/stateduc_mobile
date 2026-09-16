/// Regroup (Regroupement administratif) model — unified field names matching:
///   - JS source:  StmRegroup(id, nom, type, parentid); parentid == -1 means root
///   - DB schema:  regroups (id_camp, id_regp, lib_regp, id_type_regp, id_parent_regp)
///   - Server JSON: { id, nom, type, parentid }
///
/// From regroups.js:
///   new StmRegroup(value.id, value.nom, value.type, value.parentid)
///   parentid = -1 means this is a root (top-level) regroup

class Regroup {
  final String idRegp;         // server: id,       DB: id_regp
  final String libRegp;        // server: nom,      DB: lib_regp
  final String idTypeRegp;     // server: type,     DB: id_type_regp
  final String? idParentRegp;  // server: parentid, DB: id_parent_regp
                               // NULL in DB when parentid was '-1' (root)

  Regroup({
    required this.idRegp,
    required this.libRegp,
    required this.idTypeRegp,
    this.idParentRegp,
  });

  /// Parses server JSON from /user_camp.php/reg_camp/{login}/{campId}/1
  factory Regroup.fromJson(Map<String, dynamic> json) {
    final parentidRaw = json['parentid']?.toString() ?? json['id_parent_regp']?.toString();
    // Map root sentinels → null (DB representation of root regroup):
    //   '-1' = JS StmRegroup convention (most StatEduc deployments)
    //   '0'  = some servers send 0 for top-level regroups
    //   ''   = empty string from some PHP versions
    //   null = already null
    final isRoot = parentidRaw == null ||
        parentidRaw == '-1' ||
        parentidRaw == '0' ||
        parentidRaw.trim().isEmpty;
    final parentId = isRoot ? null : parentidRaw;
    return Regroup(
      idRegp:      (json['id'] ?? json['id_regp'] ?? '').toString(),
      libRegp:     (json['nom'] ?? json['lib_regp'] ?? '').toString(),
      idTypeRegp:  (json['type'] ?? json['id_type_regp'] ?? '').toString(),
      idParentRegp: parentId,
    );
  }

  Map<String, dynamic> toJson() => {
    'id_regp':       idRegp,
    'lib_regp':      libRegp,
    'id_type_regp':  idTypeRegp,
    'id_parent_regp': idParentRegp,
  };

  /// True if this is a root-level regroup (no parent).
  /// Handles multiple root sentinels:
  ///   null = stored as NULL in DB (normal case after fromJson fix)
  ///   '-1' = legacy stored value before fromJson fix  
  ///   '0'  = some servers send 0 for top-level regroups
  ///   ''   = empty string from some PHP versions
  bool get isRoot =>
      idParentRegp == null ||
      idParentRegp == '-1' ||
      idParentRegp == '0' ||
      idParentRegp!.trim().isEmpty;

  bool isChildOf(String parentId) => idParentRegp == parentId;
}

/// RegroupType — type of administrative entity.
///
/// From charge_camp.js addTypeRegroup():
///   /user_camp.php/typ_reg_camp/{userId}/{campId}/{typeRegroups}
///   Server JSON: { id, nom }
///
/// DB schema: regroup_types (id_camp, id_type_regp, lib_type_regp)
class RegroupType {
  final String idTypeRegp;   // server: id,  DB: id_type_regp
  final String libTypeRegp;  // server: nom, DB: lib_type_regp

  RegroupType({required this.idTypeRegp, required this.libTypeRegp});

  factory RegroupType.fromJson(Map<String, dynamic> json) {
    return RegroupType(
      idTypeRegp:  (json['id'] ?? json['id_type_regp'] ?? '').toString(),
      libTypeRegp: (json['nom'] ?? json['lib_type_regp'] ?? '').toString(),
    );
  }

  Map<String, dynamic> toJson() => {
    'id_type_regp':  idTypeRegp,
    'lib_type_regp': libTypeRegp,
  };
}
