/// Campaign model — unified field names matching both:
///   - JS source: StmCampagne(id, nom, debut, fin, statut, typeRegroups)
///   - DB schema:  campaigns (id_camp, lib_camp, date_debut, date_fin, id_year, lib_year)
///   - Server JSON for new_camp: { id, nom, debut, fin, statut, typeRegroups }
///
/// From page_new_camp.js / campagnes.js — original JS model:
///   StmCampagne { id, nom, debut, fin, statut, typeregroups }
///   typeregroups is a CSV of type IDs e.g. "1,2,3"

class Campaign {
  final String idCamp;        // server: id,    DB: id_camp
  final String libCamp;       // server: nom,   DB: lib_camp
  final String? dateDebut;    // server: debut, DB: date_debut
  final String? dateFin;      // server: fin,   DB: date_fin
  final int statut;           // server: statut (1=à venir, 2=en cours, 3=fermée)
  final String typeRegroups;  // server: typeRegroups or typeregroups (CSV)
  final String? idYear;       // DB: id_year  (from codeyear when saving)
  final String? libYear;      // DB: lib_year (from libyear when saving)

  late final List<String> typeRegroupsList;

  static const int statusPending = 1;
  static const int statusOpen    = 2;
  static const int statusClosed  = 3;

  static const Map<int, String> statusLabels = {
    1: 'à venir',
    2: 'en cours',
    3: 'fermée',
  };

  Campaign({
    required this.idCamp,
    required this.libCamp,
    this.dateDebut,
    this.dateFin,
    this.statut = 0,
    this.typeRegroups = '',
    this.idYear,
    this.libYear,
    List<String>? typeRegroupsList,
  }) {
    this.typeRegroupsList = typeRegroupsList ??
        typeRegroups.split(',').where((s) => s.trim().isNotEmpty).toList();
  }

  /// Parses server JSON from /user_camp.php/new_camp/{userId}/1
  /// Response envelope: { se_status:200, se_data: [{id, nom, debut, fin, statut, typeRegroups}, ...] }
  factory Campaign.fromJson(Map<String, dynamic> json) {
    // Handle both server key variants (typeRegroups vs typeregroups)
    final typeR = (json['typeRegroups'] ?? json['typeregroups'] ?? '').toString();
    // Handle both server key (id) and DB key (id_camp)
    final id = (json['id'] ?? json['id_camp'] ?? '').toString();
    return Campaign(
      idCamp:      id,
      libCamp:     (json['nom'] ?? json['lib_camp'] ?? '').toString(),
      dateDebut:   json['debut']?.toString() ?? json['date_debut']?.toString(),
      dateFin:     json['fin']?.toString()   ?? json['date_fin']?.toString(),
      statut:      int.tryParse((json['statut'] ?? '0').toString()) ?? 0,
      typeRegroups: typeR,
      idYear:      json['id_year']?.toString(),
      libYear:     json['lib_year']?.toString(),
    );
  }

  Map<String, dynamic> toJson() => {
    'id_camp':    idCamp,
    'lib_camp':   libCamp,
    'date_debut': dateDebut,
    'date_fin':   dateFin,
    'statut':     statut,
    'typeRegroups': typeRegroups,
    'id_year':    idYear,
    'lib_year':   libYear,
  };

  String get statusLabel => statusLabels[statut] ?? 'inconnu';

  /// Returns true if [typeId] is the last regroup type for this campaign.
  /// Used to decide when to stop the drill-down navigation.
  bool isLastTypeRegroup(String typeId) {
    if (typeRegroupsList.isEmpty) return true;
    return typeId == typeRegroupsList.last;
  }
}
