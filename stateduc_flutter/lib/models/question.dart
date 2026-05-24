/// Question (Formulaire/Thème) model — unified field names matching:
///   - JS source:  StmQst(id, title, idcamp, idsys, filter)
///   - DB schema:  questions (id_camp, id_qst, lib_qst, id_system, has_filter)
///   - Server JSON from /data_camp.php/theme_camp/{campId}/{sysId}/eng:
///       { id, title, idcamp, idsys, filter }  — filter: 1 = filtered by period
///
/// From charge_camp.js addQsts():
///   window.localStorage.setItem('stm_ChargedQst_'+id_camp+'_'+id_sys, JSON.stringify(qsts));

class Question {
  final String idQst;     // server: id,     DB: id_qst
  final String libQst;    // server: title,  DB: lib_qst
  final String idSystem;  // server: idsys,  DB: id_system
  final bool hasFilter;   // server: filter, DB: has_filter (1=yes)

  Question({
    required this.idQst,
    required this.libQst,
    required this.idSystem,
    this.hasFilter = false,
  });

  /// Parses server JSON from /data_camp.php/theme_camp/{campId}/{sysId}/eng
  factory Question.fromJson(Map<String, dynamic> json) {
    return Question(
      idQst:     (json['id'] ?? json['id_qst'] ?? '').toString(),
      libQst:    (json['title'] ?? json['lib_qst'] ?? '').toString(),
      idSystem:  (json['idsys'] ?? json['id_system'] ?? '').toString(),
      hasFilter: (int.tryParse((json['filter'] ?? '0').toString()) ?? 0) == 1,
    );
  }

  Map<String, dynamic> toJson() => {
    'id_qst':    idQst,
    'lib_qst':   libQst,
    'id_system': idSystem,
    'has_filter': hasFilter ? 1 : 0,
  };

  bool get isFiltered => hasFilter;
}

/// ValidationRule for a form field.
///
/// From charge_camp.js getRules():
///   /data_camp.php/regle_theme_camp/{qstId}/{sysId}
///   Server JSON: { champ, type, taille, format, inter, min_val, max_val,
///                  pres, paru, obli, int_ref, edits, enums, uniq }
///
/// DB schema: validation_rules (id_camp, id_qst, id_champ, rule_type, rule_value)
class ValidationRule {
  final String idChamp;    // server: champ,   DB: id_champ
  final String ruleType;   // derived from type/obli/inter etc., DB: rule_type
  final String? ruleValue; // derived value, DB: rule_value

  // Original server fields (kept for full fidelity)
  final String type;    // 'int', 'decimal', 'date', 'text'
  final int taille;     // max length
  final int inter;      // interval check enabled
  final int minVal;     // min value
  final int maxVal;     // max value
  final int obli;       // mandatory (1 = required)
  final String enums;   // comma-separated allowed values

  late final List<String> enumsArray;

  ValidationRule({
    required this.idChamp,
    required this.ruleType,
    this.ruleValue,
    this.type = 'text',
    this.taille = 0,
    this.inter = 0,
    this.minVal = 0,
    this.maxVal = 0,
    this.obli = 0,
    this.enums = '',
  }) {
    enumsArray = enums.split(',').where((s) => s.isNotEmpty).toList();
  }

  /// Parses server JSON from /data_camp.php/regle_theme_camp/{qstId}/{sysId}
  factory ValidationRule.fromJson(Map<String, dynamic> json) {
    final champ   = (json['champ'] ?? json['id_champ'] ?? '').toString();
    final type    = (json['type'] ?? 'text').toString();
    final taille  = int.tryParse((json['taille'] ?? '0').toString()) ?? 0;
    final inter   = int.tryParse((json['inter'] ?? '0').toString()) ?? 0;
    final minVal  = int.tryParse((json['min_val'] ?? '0').toString()) ?? 0;
    final maxVal  = int.tryParse((json['max_val'] ?? '0').toString()) ?? 0;
    final obli    = int.tryParse((json['obli'] ?? '0').toString()) ?? 0;
    final enums   = (json['enums'] ?? '').toString();

    // Derive simplified ruleType for DB storage / validation logic
    String ruleType = 'type_$type';
    String? ruleValue;

    if (obli == 1) {
      ruleType = 'mandatory';
    } else if (inter != 0) {
      ruleType = 'range';
      ruleValue = '$minVal,$maxVal';
    } else if (taille > 0) {
      ruleType = 'max_length';
      ruleValue = taille.toString();
    } else if (enums.isNotEmpty) {
      ruleType = 'enum';
      ruleValue = enums;
    }

    return ValidationRule(
      idChamp:   champ,
      ruleType:  ruleType,
      ruleValue: ruleValue,
      type:      type,
      taille:    taille,
      inter:     inter,
      minVal:    minVal,
      maxVal:    maxVal,
      obli:      obli,
      enums:     enums,
    );
  }

  Map<String, dynamic> toJson() => {
    'id_champ':   idChamp,
    'rule_type':  ruleType,
    'rule_value': ruleValue,
  };

  /// Validates a value against this rule.
  /// Returns a French error message or null if valid.
  String? validate(String value) {
    switch (ruleType) {
      case 'mandatory':
        if (value.trim().isEmpty) return 'Ce champ est obligatoire';
        break;
      case 'type_int':
        if (value.isNotEmpty && int.tryParse(value) == null) {
          return 'Valeur entière requise';
        }
        break;
      case 'type_decimal':
        if (value.isNotEmpty) {
          final normalized = value.replaceAll(',', '.');
          if (double.tryParse(normalized) == null) {
            return 'Valeur numérique requise';
          }
        }
        break;
      case 'type_date':
        if (value.isNotEmpty) {
          final parts = value.split('/');
          if (parts.length != 3) return 'Format de date invalide (JJ/MM/AAAA)';
          final day   = int.tryParse(parts[0]);
          final month = int.tryParse(parts[1]);
          final year  = int.tryParse(parts[2]);
          if (day == null || month == null || year == null ||
              day < 1 || day > 31 || month < 1 || month > 12) {
            return 'Date invalide';
          }
        }
        break;
      case 'max_length':
        final maxLen = int.tryParse(ruleValue ?? '') ?? 0;
        if (maxLen > 0 && value.length > maxLen) {
          return 'Longueur maximale dépassée ($maxLen caractères)';
        }
        break;
      case 'range':
        final parts = (ruleValue ?? '0,0').split(',');
        final minV = double.tryParse(parts[0]) ?? 0;
        final maxV = double.tryParse(parts.length > 1 ? parts[1] : '0') ?? 0;
        final val  = double.tryParse(value.replaceAll(',', '.'));
        if (val != null) {
          if (val < minV) return 'Valeur minimale : $minV';
          if (val > maxV) return 'Valeur maximale : $maxV';
        }
        break;
      case 'enum':
        final allowed = (ruleValue ?? '').split(',').map((s) => s.trim()).toList();
        if (value.isNotEmpty && !allowed.contains(value)) {
          return 'Valeur non autorisée';
        }
        break;
    }
    return null;
  }
}
