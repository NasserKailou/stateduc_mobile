// Campaign model

class Campaign {
  final String id;
  final String nom;
  final String debut;
  final String fin;
  final int statut;
  final String typeregroups; // comma-separated type IDs
  List<String> typeRegroupsList;

  static const int statusActive = 1;
  static const int statusOpen = 2;
  static const int statusClosed = 3;

  static const Map<int, String> statusLabels = {
    1: 'à venir',
    2: 'en cours',
    3: 'fermées',
  };

  Campaign({
    required this.id,
    required this.nom,
    required this.debut,
    required this.fin,
    required this.statut,
    required this.typeregroups,
    List<String>? typeRegroupsList,
  }) : typeRegroupsList =
            typeRegroupsList ?? typeregroups.split(',').where((s) => s.isNotEmpty).toList();

  factory Campaign.fromJson(Map<String, dynamic> json) {
    return Campaign(
      id: json['id']?.toString() ?? '',
      nom: json['nom'] ?? '',
      debut: json['debut'] ?? '',
      fin: json['fin'] ?? '',
      statut: int.tryParse(json['statut']?.toString() ?? '0') ?? 0,
      typeregroups: json['typeregroups']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'nom': nom,
        'debut': debut,
        'fin': fin,
        'statut': statut,
        'typeregroups': typeregroups,
      };

  String get statusLabel => statusLabels[statut] ?? 'inconnu';

  bool get isLastRegroupType {
    return typeRegroupsList.isNotEmpty;
  }

  bool isLastTypeRegroup(String typeId) {
    if (typeRegroupsList.isEmpty) return false;
    return typeId == typeRegroupsList.last;
  }
}
