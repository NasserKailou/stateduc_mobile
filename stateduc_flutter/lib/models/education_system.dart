// Education system (Secteur) model

class EducationSystem {
  final String id;
  final String nom;

  EducationSystem({
    required this.id,
    required this.nom,
  });

  factory EducationSystem.fromJson(Map<String, dynamic> json) {
    return EducationSystem(
      id: json['id']?.toString() ?? '',
      nom: json['nom'] ?? '',
    );
  }

  Map<String, dynamic> toJson() => {'id': id, 'nom': nom};
}
