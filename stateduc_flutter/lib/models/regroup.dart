// Administrative entity (Regroupement) model

class Regroup {
  final String id;
  final String nom;
  final String type;
  final String parentid;

  Regroup({
    required this.id,
    required this.nom,
    required this.type,
    required this.parentid,
  });

  factory Regroup.fromJson(Map<String, dynamic> json) {
    return Regroup(
      id: json['id']?.toString() ?? '',
      nom: json['nom'] ?? '',
      type: json['type']?.toString() ?? '',
      parentid: json['parentid']?.toString() ?? '-1',
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'nom': nom,
        'type': type,
        'parentid': parentid,
      };

  bool isChildOf(String parentId) => parentid == parentId;

  bool get isRoot => parentid == '-1';
}

// Type of administrative entity
class RegroupType {
  final String id;
  final String nom;

  RegroupType({
    required this.id,
    required this.nom,
  });

  factory RegroupType.fromJson(Map<String, dynamic> json) {
    return RegroupType(
      id: json['id']?.toString() ?? '',
      nom: json['nom'] ?? '',
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'nom': nom,
      };
}
