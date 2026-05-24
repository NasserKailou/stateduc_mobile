/// EducationSystem (Secteur) model — unified field names matching:
///   - JS source:  new StmSystem(value.id, value.nom)
///   - DB schema:  education_systems (id_camp, id_system, lib_system)
///   - Server JSON: { id, nom }
///
/// From charge_camp.js addSystems():
///   /user_camp.php/sys_camp/{userId}/{campId}
///   Server JSON: { id, nom }

class EducationSystem {
  final String idSystem;   // server: id,  DB: id_system
  final String libSystem;  // server: nom, DB: lib_system

  EducationSystem({required this.idSystem, required this.libSystem});

  factory EducationSystem.fromJson(Map<String, dynamic> json) {
    return EducationSystem(
      idSystem:  (json['id'] ?? json['id_system'] ?? '').toString(),
      libSystem: (json['nom'] ?? json['lib_system'] ?? '').toString(),
    );
  }

  Map<String, dynamic> toJson() => {
    'id_system':  idSystem,
    'lib_system': libSystem,
  };
}
