/// SchoolYear — représente une année de recensement scolaire (TYPE_ANNEE).
///
/// Correspondance serveur (annees_ws.php) :
///   CODE_TYPE_ANNEE    → code    (entier, identifiant technique)
///   LIBELLE_TYPE_ANNEE → libelle (chaîne d'affichage conviviale)
///   ORDRE_TYPE_ANNEE   → ordre   (tri croissant)
///
/// Correspondance SQLite (table school_years) :
///   code    → code    INTEGER
///   libelle → libelle TEXT
///   ordre   → ordre   INTEGER
///
/// Usage :
///   - Affiché dans l'onglet « Année » de Paramètres (libelle)
///   - Utilisé comme filtre dans les URLs de saisie/rechargement (code)
///   - Persisté en SQLite pour consultation hors ligne
class SchoolYear {
  final int    code;     // CODE_TYPE_ANNEE  — valeur technique envoyée au serveur
  final String libelle;  // LIBELLE_TYPE_ANNEE — label d'affichage (ex: "2024-2025")
  final int    ordre;    // ORDRE_TYPE_ANNEE  — position de tri

  const SchoolYear({
    required this.code,
    required this.libelle,
    required this.ordre,
  });

  /// Depuis la réponse JSON de annees_ws.php → GET /list/:login
  factory SchoolYear.fromJson(Map<String, dynamic> json) {
    return SchoolYear(
      code:    int.tryParse(json['code']?.toString() ?? '0')    ?? 0,
      libelle: (json['libelle'] ?? '').toString().trim(),
      ordre:   int.tryParse(json['ordre']?.toString() ?? '0')   ?? 0,
    );
  }

  /// Depuis une ligne SQLite (table school_years)
  factory SchoolYear.fromSqlite(Map<String, Object?> row) {
    return SchoolYear(
      code:    (row['code']    as int?)    ?? 0,
      libelle: (row['libelle'] as String?) ?? '',
      ordre:   (row['ordre']   as int?)    ?? 0,
    );
  }

  Map<String, dynamic> toJson() => {
    'code':    code,
    'libelle': libelle,
    'ordre':   ordre,
  };

  Map<String, Object?> toSqliteMap() => {
    'code':    code,
    'libelle': libelle,
    'ordre':   ordre,
  };

  @override
  String toString() => 'SchoolYear(code=$code, libelle=$libelle, ordre=$ordre)';

  @override
  bool operator ==(Object other) =>
      other is SchoolYear && other.code == code;

  @override
  int get hashCode => code.hashCode;
}
