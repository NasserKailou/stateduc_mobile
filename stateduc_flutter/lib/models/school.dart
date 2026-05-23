// School (Etablissement) model

class School {
  final String id;
  final String code;
  final String nom;
  final String? status;
  final String idRegroup;

  School({
    required this.id,
    required this.code,
    required this.nom,
    this.status,
    required this.idRegroup,
  });

  factory School.fromJson(Map<String, dynamic> json) {
    return School(
      id: json['id']?.toString() ?? '',
      code: json['code']?.toString() ?? '',
      nom: json['nom'] ?? '',
      status: json['status']?.toString(),
      idRegroup: json['idregroup']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'code': code,
        'nom': nom,
        'status': status,
        'idregroup': idRegroup,
      };

  bool inRegroup(String regroupId) => idRegroup == regroupId;
}

// Localisation chain - links campaign, system, regroups and schools
class Localisation {
  final String idLoc;
  final String idCamp;
  final String idSys;
  final List<String> regroupIds;
  final List<String> etabIds;

  Localisation({
    required this.idLoc,
    required this.idCamp,
    required this.idSys,
    required this.regroupIds,
    required this.etabIds,
  });

  factory Localisation.fromJson(Map<String, dynamic> json) {
    return Localisation(
      idLoc: json['idloc']?.toString() ?? '',
      idCamp: json['idcamp']?.toString() ?? '',
      idSys: json['idsys']?.toString() ?? '',
      regroupIds: (json['regroups']?.toString() ?? '')
          .split(',')
          .where((s) => s.isNotEmpty)
          .toList(),
      etabIds: (json['etabs']?.toString() ?? '')
          .split(',')
          .where((s) => s.isNotEmpty)
          .toList(),
    );
  }

  Map<String, dynamic> toJson() => {
        'idloc': idLoc,
        'idcamp': idCamp,
        'idsys': idSys,
        'regroups': regroupIds.join(','),
        'etabs': etabIds.join(','),
      };
}

// School status
class SchoolStatus {
  final String id;
  final String name;

  SchoolStatus({required this.id, required this.name});

  factory SchoolStatus.fromJson(Map<String, dynamic> json) {
    return SchoolStatus(
      id: json['id']?.toString() ?? '',
      name: json['name'] ?? '',
    );
  }

  Map<String, dynamic> toJson() => {'id': id, 'name': name};
}
