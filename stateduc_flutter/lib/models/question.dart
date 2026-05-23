// Question (Form/Theme) and validation rule models

class Question {
  final String id;
  final String title;
  final String idcamp;
  final String idsys;
  final int filter; // 1 = filtered by period, 0 = not filtered
  String? html; // HTML content of the form

  Question({
    required this.id,
    required this.title,
    required this.idcamp,
    required this.idsys,
    this.filter = 0,
    this.html,
  });

  factory Question.fromJson(Map<String, dynamic> json) {
    return Question(
      id: json['id']?.toString() ?? '',
      title: json['title'] ?? '',
      idcamp: json['idcamp']?.toString() ?? '',
      idsys: json['idsys']?.toString() ?? '',
      filter: int.tryParse(json['filter']?.toString() ?? '0') ?? 0,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'idcamp': idcamp,
        'idsys': idsys,
        'filter': filter,
      };

  bool get isFiltered => filter == 1;
}

// Validation rule for a form field
class ValidationRule {
  final String champ; // field name
  final String type; // int, date, decimal, text
  final int taille; // max length
  final String format;
  final int inter; // interval check enabled
  final int minVal;
  final int maxVal;
  final String pres;
  final String paru;
  final int obli; // mandatory
  final String intRef;
  final String edits;
  final String enums; // comma-separated allowed values
  final String uniq;

  late final List<String> enumsArray;

  ValidationRule({
    required this.champ,
    required this.type,
    required this.taille,
    required this.format,
    required this.inter,
    required this.minVal,
    required this.maxVal,
    required this.pres,
    required this.paru,
    required this.obli,
    required this.intRef,
    required this.edits,
    required this.enums,
    required this.uniq,
  }) {
    enumsArray = enums.split(',').where((s) => s.isNotEmpty).toList();
  }

  factory ValidationRule.fromJson(Map<String, dynamic> json) {
    return ValidationRule(
      champ: json['champ'] ?? '',
      type: json['type'] ?? 'text',
      taille: int.tryParse(json['taille']?.toString() ?? '0') ?? 0,
      format: json['format'] ?? '',
      inter: int.tryParse(json['inter']?.toString() ?? '0') ?? 0,
      minVal: int.tryParse(json['min_val']?.toString() ?? '0') ?? 0,
      maxVal: int.tryParse(json['max_val']?.toString() ?? '0') ?? 0,
      pres: json['pres'] ?? '',
      paru: json['paru'] ?? '',
      obli: int.tryParse(json['obli']?.toString() ?? '0') ?? 0,
      intRef: json['int_ref'] ?? '',
      edits: json['edits'] ?? '',
      enums: json['enums'] ?? '',
      uniq: json['uniq'] ?? '',
    );
  }

  Map<String, dynamic> toJson() => {
        'champ': champ,
        'type': type,
        'taille': taille,
        'format': format,
        'inter': inter,
        'min_val': minVal,
        'max_val': maxVal,
        'pres': pres,
        'paru': paru,
        'obli': obli,
        'int_ref': intRef,
        'edits': edits,
        'enums': enums,
        'uniq': uniq,
      };

  /// Validates a value, returns null if valid, or error code string if invalid
  String? validate(String value) {
    if (value.isNotEmpty) {
      // Type check
      if (!_checkType(value)) {
        return 'type_$type';
      }
      // Length check
      if (taille > 0 && value.length > taille) {
        return 'taille_$taille';
      }
      // Interval check
      if (inter != 0) {
        final numVal = int.tryParse(value);
        if (numVal == null || numVal < minVal || numVal > maxVal) {
          return 'inter_[$minVal - $maxVal]';
        }
      }
    }
    // Mandatory check
    if (obli != 0 && value.isEmpty) {
      return 'obli';
    }
    return null; // valid
  }

  bool _checkType(String value) {
    switch (type) {
      case 'int':
        return int.tryParse(value) != null;
      case 'decimal':
        return double.tryParse(value) != null;
      case 'date':
        return true; // Date format validated by picker
      default:
        return true;
    }
  }
}

// Collected data entry
class CollectedData {
  final String qst; // question/form ID
  final String key; // field name (or name#id for radio)
  String value;
  final String type; // text, radio, checkbox, select, hidden

  CollectedData({
    required this.qst,
    required this.key,
    required this.value,
    required this.type,
  });

  factory CollectedData.fromJson(Map<String, dynamic> json) {
    return CollectedData(
      qst: json['qst']?.toString() ?? '',
      key: json['key'] ?? '',
      value: json['value']?.toString() ?? '',
      type: json['type'] ?? 'text',
    );
  }

  Map<String, dynamic> toJson() => {
        'qst': qst,
        'key': key,
        'value': value,
        'type': type,
      };

  bool inQuestion(String qstId) => qst == qstId;

  bool isEqual(CollectedData other) => qst == other.qst && key == other.key;
}
