// Question (Thème / Formulaire) et règles de validation
// Uniformisé avec database_service.dart et data_entry_provider.dart

class Question {
  final String idQst;
  final String libQst;
  final String idSystem;
  final bool hasFilter;
  final int sortOrder;  // server-returned order (position in API response)

  Question({
    required this.idQst,
    required this.libQst,
    required this.idSystem,
    this.hasFilter = false,
    this.sortOrder = 0,
  });

  factory Question.fromJson(Map<String, dynamic> json, {int serverIndex = 0}) {
    return Question(
      idQst: json['id_qst']?.toString() ??
             json['id']?.toString() ?? '',
      libQst: json['lib_qst'] ?? json['title'] ?? '',
      idSystem: json['id_system']?.toString() ??
                json['idsys']?.toString() ?? '',
      hasFilter: json['has_filter'] == 1 ||
                 json['has_filter'] == true ||
                 json['filter'] == 1,
      // Preserve server-returned order (used for questionnaire display order)
      sortOrder: (json['sort_order'] as int?) ??
                 (json['ordre'] as int?) ??
                 serverIndex,
    );
  }

  Map<String, dynamic> toJson() => {
    'id_qst': idQst,
    'lib_qst': libQst,
    'id_system': idSystem,
    'has_filter': hasFilter ? 1 : 0,
    'sort_order': sortOrder,
  };
}

// ValidationRule — règle de validation d'un champ de formulaire
// Stockée dans la table validation_rules de SQLite
class ValidationRule {
  final String idChamp;   // nom du champ HTML
  final String ruleType;  // type de règle: mandatory, type_int, type_decimal,
                          //               type_date, max_length, min_val, max_val, enum
  final String? ruleValue; // valeur associée à la règle (ex: "10" pour max_length)

  ValidationRule({
    required this.idChamp,
    required this.ruleType,
    this.ruleValue,
  });

  factory ValidationRule.fromJson(Map<String, dynamic> json) {
    // Compatibilité avec l'ancien format (champ/type/taille/obli/inter etc.)
    final champ = json['id_champ'] ?? json['champ'] ?? '';
    String ruleType = json['rule_type'] ?? '';
    String? ruleValue = json['rule_value']?.toString();

    // Conversion de l'ancien format vers le nouveau
    if (ruleType.isEmpty) {
      final type = json['type'] ?? 'text';
      final obli = int.tryParse(json['obli']?.toString() ?? '0') ?? 0;
      final taille = int.tryParse(json['taille']?.toString() ?? '0') ?? 0;
      final inter = int.tryParse(json['inter']?.toString() ?? '0') ?? 0;
      final minVal = json['min_val']?.toString();
      final maxVal = json['max_val']?.toString();
      final enums = json['enums'] ?? '';

      // On retourne la règle de type principal
      if (obli == 1) {
        ruleType = 'mandatory';
      } else if (type == 'int') {
        ruleType = 'type_int';
      } else if (type == 'decimal') {
        ruleType = 'type_decimal';
      } else if (type == 'date') {
        ruleType = 'type_date';
      } else if (taille > 0) {
        ruleType = 'max_length';
        ruleValue = taille.toString();
      } else if (inter == 1 && minVal != null) {
        ruleType = 'min_val';
        ruleValue = minVal;
      } else if (enums.isNotEmpty) {
        ruleType = 'enum';
        ruleValue = enums;
      } else {
        ruleType = 'text';
      }
    }

    return ValidationRule(
      idChamp: champ,
      ruleType: ruleType,
      ruleValue: ruleValue,
    );
  }

  /// Validates [value] against this rule.
  /// Returns a French error message, or null if the value is valid.
  /// Called by DataEntryProvider.validateField().
  String? validate(String value) {
    final v = value.trim();
    switch (ruleType) {
      case 'mandatory':
        if (v.isEmpty) return 'Ce champ est obligatoire';
        return null;

      case 'type_int':
        if (v.isEmpty) return null; // mandatory handled separately
        if (int.tryParse(v) == null) return 'Veuillez saisir un nombre entier';
        return null;

      case 'type_decimal':
        if (v.isEmpty) return null;
        // Accept both comma and dot as decimal separator
        if (double.tryParse(v.replaceAll(',', '.')) == null) {
          return 'Veuillez saisir un nombre décimal';
        }
        return null;

      case 'type_date':
        if (v.isEmpty) return null;
        // Accepts DD/MM/YYYY or YYYY-MM-DD
        final ddmmyyyy = RegExp(r'^\d{2}/\d{2}/\d{4}$');
        final yyyymmdd = RegExp(r'^\d{4}-\d{2}-\d{2}$');
        if (!ddmmyyyy.hasMatch(v) && !yyyymmdd.hasMatch(v)) {
          return 'Format de date invalide (JJ/MM/AAAA)';
        }
        return null;

      case 'max_length':
        if (v.isEmpty) return null;
        final max = int.tryParse(ruleValue ?? '');
        if (max != null && v.length > max) {
          return 'Maximum $max caractères autorisés';
        }
        return null;

      case 'min_val':
        if (v.isEmpty) return null;
        final num = double.tryParse(v.replaceAll(',', '.'));
        final min = double.tryParse(ruleValue ?? '');
        if (num != null && min != null && num < min) {
          return 'La valeur minimale est $ruleValue';
        }
        return null;

      case 'max_val':
        if (v.isEmpty) return null;
        final num2 = double.tryParse(v.replaceAll(',', '.'));
        final max2 = double.tryParse(ruleValue ?? '');
        if (num2 != null && max2 != null && num2 > max2) {
          return 'La valeur maximale est $ruleValue';
        }
        return null;

      case 'enum':
        if (v.isEmpty) return null;
        final allowed = (ruleValue ?? '').split(',').map((e) => e.trim()).toList();
        if (allowed.isNotEmpty && !allowed.contains(v)) {
          return 'Valeur non autorisée';
        }
        return null;

      default: // 'text' and unknown types — no constraint
        return null;
    }
  }

  Map<String, dynamic> toJson() => {
    'id_champ': idChamp,
    'rule_type': ruleType,
    'rule_value': ruleValue,
  };
}

// CollectedData — donnée saisie pour un champ/école/question
class CollectedData {
  final String idEtab;
  final String idQst;
  final String? idFilter;
  final String fieldName;
  String fieldValue;

  CollectedData({
    required this.idEtab,
    required this.idQst,
    this.idFilter,
    required this.fieldName,
    required this.fieldValue,
  });
}
