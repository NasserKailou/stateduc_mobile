import 'package:flutter/foundation.dart';

/// Représente une règle conditionnelle extraite du HTML d'un formulaire.
///
/// Signification : si la valeur du champ [sourceField] n'est PAS [triggerValue],
/// alors les champs [targetFields] doivent être désactivés (grisés + non saisissables).
///
/// Exemples :
///   - "Si oui, quelle est sa superficie ?"
///       → ConditionalRule(sourceField: "DOMAINE_DELIMITE_0", triggerValue: "1",
///                         targetFields: ["SUPERFICIE_DOMAINE_0"])
///   - "Si non, collabore-t-elle avec un centre spécialisé ?"
///       → ConditionalRule(sourceField: "DETECTER_ENFANT_HANDI_0", triggerValue: "0",
///                         targetFields: ["CSPEC_DIAGNO_CASDOUTEUX_0"])
class ConditionalRule {
  final String sourceField;        // champ source Oui/Non (ex. "DOMAINE_DELIMITE_0")
  final String triggerValue;       // valeur qui ACTIVE les champs dépendants : "1" (si oui) ou "0" (si non)
  final List<String> targetFields; // champs à désactiver si la condition n'est PAS satisfaite

  const ConditionalRule({
    required this.sourceField,
    required this.triggerValue,
    required this.targetFields,
  });

  @override
  String toString() =>
      'ConditionalRule(source=$sourceField trigger=$triggerValue targets=$targetFields)';
}

/// Parseur de règles conditionnelles extraites du HTML brut d'un formulaire StatEduc.
///
/// ─── Stratégie de détection ────────────────────────────────────────────────
///
/// Les formulaires StatEduc-Burundi utilisent un pattern purement textuel pour
/// indiquer les dépendances conditionnelles entre champs :
///   • Labels commençant par "Si oui"  → condition triggerValue = "1"
///   • Labels commençant par "Si non" ou "Sinon" → condition triggerValue = "0"
///
/// Il n'y a PAS d'attributs data-* ni de marqueurs programmatiques dans le HTML.
///
/// ─── Deux topologies détectées dans infos_gen_1.html ──────────────────────
///
/// 1. **Même TR, deux TD côte à côte** (ELECTRICITE_0 / EAU_POTABLE_0) :
///    <TR>
///      <TD>...Oui<INPUT NAME='ELECTRICITE_0'...>Non<INPUT...></TD>
///      <TD>&nbsp;Si oui, le système...Oui<INPUT NAME='FONCT_ALIMENT_ELECTRICITE_0'...></TD>
///    </TR>
///    → Le TD "source" (sans "Si oui/non") précède le TD "target" dans la même TR.
///
/// 2. **TR suivant** (DOMAINE_DELIMITE_0, LATRINES_DISPOSE_0, etc.) :
///    <TR>...<TD>...<INPUT NAME='DOMAINE_DELIMITE_0'...></TD></TR>
///    <TR><TD>...Si oui, quelle est sa superficie...<INPUT NAME='SUPERFICIE_DOMAINE_0'...></TD></TR>
///    (séparés parfois par un TR td_space_blanc vide)
///
/// ─── Algorithme ────────────────────────────────────────────────────────────
///
/// On travaille sur des "blocs TR" extraits séquentiellement.
/// Pour chaque TR :
///   a) Chercher des TD contenant "Si oui/non/Sinon" → topologie 1 (même TR)
///   b) Vérifier si le texte de ce TR commence par "Si oui/non/Sinon"
///      → topologie 2 (ce TR est dépendant du TR précédent portant le champ source)
///
/// ─── Robustesse ────────────────────────────────────────────────────────────
/// • Insensible à la casse (Si Oui / SI OUI / si oui)
/// • Ignore les TR d'espacement (td_space_blanc, vides)
/// • Gère plusieurs champs cibles dans un même TR dépendant
/// • Ne lève jamais d'exception — retourne une liste vide en cas d'erreur
class ConditionalRulesParser {
  // Préfixes "Si oui" (trigger = "1")
  static final RegExp _siOuiRe =
      RegExp(r'^\s*(?:&nbsp;|\s)*\s*si\s+oui\b', caseSensitive: false);

  // Préfixes "Si non" ou "Sinon" (trigger = "0")
  static final RegExp _siNonRe =
      RegExp(r'^\s*(?:&nbsp;|\s)*\s*(si\s+non|sinon)\b', caseSensitive: false);

  // Extrait la valeur de base d'un NAME= d'input radio Oui/Non.
  // Ex. : NAME='ELECTRICITE_0' → "ELECTRICITE_0"
  //       NAME='ELECTRICITE_0_1' → on veut le baseName sans le suffixe _N final
  static final RegExp _nameAttrRe =
      RegExp(r"""NAME=['"]?([A-Za-z_][A-Za-z0-9_]*)['"]?""", caseSensitive: false);

  // Texte visible d'un TD (retire les balises HTML, décodes les entités basiques)
  static String _tdText(String td) {
    // Retire toutes les balises HTML
    var text = td.replaceAll(RegExp(r'<[^>]*>'), ' ');
    // Décode entités basiques
    text = text
        .replaceAll('&nbsp;', ' ')
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>')
        .replaceAll('&amp;', '&')
        .replaceAll('&#8217;', "'");
    // Compresse les espaces
    return text.replaceAll(RegExp(r'\s+'), ' ').trim();
  }

  /// Extrait tous les NAME= d'INPUT présents dans un bloc HTML donné.
  /// Retourne les noms de base (sans suffixe _N final s'il s'agit d'options
  /// radio numérotées comme _0_1, _0_0).
  static List<String> _extractFieldNames(String html) {
    final names = <String>[];
    for (final m in _nameAttrRe.allMatches(html)) {
      final raw = m.group(1)!;
      // Normalise : retire le dernier segment numérique si le nom se termine
      // par _<chiffres> et que le segment précédent est aussi numérique
      // (pattern radio : FIELD_0_1, FIELD_0_0 → on garde FIELD_0)
      final normalized = _normalizeFieldName(raw);
      if (!names.contains(normalized)) {
        names.add(normalized);
      }
    }
    return names;
  }

  /// Normalise un nom de champ radio en retirant le suffixe d'option.
  /// ELECTRICITE_0_1 → ELECTRICITE_0
  /// ELECTRICITE_0_0 → ELECTRICITE_0
  /// NB_LATRINES_ELEVES_0 → NB_LATRINES_ELEVES_0  (pas de suffixe option)
  static String _normalizeFieldName(String name) {
    // Si le nom se termine par _<chiffres>, et que la partie avant se termine
    // aussi par _<chiffres>, on retire le dernier segment (suffixe option radio)
    final parts = name.split('_');
    if (parts.length >= 3) {
      final lastPart = parts.last;
      final secondToLast = parts[parts.length - 2];
      // Critère : les deux derniers segments sont numériques → retirer le dernier
      if (RegExp(r'^\d+$').hasMatch(lastPart) &&
          RegExp(r'^\d+$').hasMatch(secondToLast)) {
        return parts.sublist(0, parts.length - 1).join('_');
      }
    }
    return name;
  }

  /// Vérifie si un bloc TR est un TR d'espacement (vide ou td_space_blanc).
  static bool _isSpacerTr(String tr) {
    final text = _tdText(tr);
    return text.isEmpty ||
        text == ' ' ||
        tr.toLowerCase().contains('td_space_blanc');
  }

  /// Extrait les blocs <TR>...</TR> du HTML (insensible à la casse, imbrications simples).
  /// Note : les TR imbriqués ne sont pas attendus dans les formulaires StatEduc.
  static List<String> _extractTrBlocks(String html) {
    final blocks = <String>[];
    final openRe = RegExp(r'<TR\b[^>]*>', caseSensitive: false);
    final closeRe = RegExp(r'</TR\s*>', caseSensitive: false);

    int pos = 0;
    while (pos < html.length) {
      final openMatch = openRe.firstMatch(html.substring(pos));
      if (openMatch == null) break;
      final openStart = pos + openMatch.start;
      final openEnd = pos + openMatch.end;

      final closeMatch = closeRe.firstMatch(html.substring(openEnd));
      if (closeMatch == null) break;
      final closeEnd = openEnd + closeMatch.end;

      blocks.add(html.substring(openStart, closeEnd));
      pos = closeEnd;
    }
    return blocks;
  }

  /// Extrait les blocs <TD>...</TD> d'un TR (premier niveau, non imbriqués).
  static List<String> _extractTdBlocks(String tr) {
    final blocks = <String>[];
    final openRe = RegExp(r'<TD\b[^>]*>', caseSensitive: false);
    final closeRe = RegExp(r'</TD\s*>', caseSensitive: false);

    int pos = 0;
    while (pos < tr.length) {
      final openMatch = openRe.firstMatch(tr.substring(pos));
      if (openMatch == null) break;
      final openStart = pos + openMatch.start;
      final openEnd = pos + openMatch.end;

      final closeMatch = closeRe.firstMatch(tr.substring(openEnd));
      if (closeMatch == null) break;
      final closeEnd = openEnd + closeMatch.end;

      blocks.add(tr.substring(openStart, closeEnd));
      pos = closeEnd;
    }
    return blocks;
  }

  /// Point d'entrée principal.
  ///
  /// [html] : le HTML brut du formulaire (tel que stocké dans SQLite).
  /// Retourne une liste de [ConditionalRule], potentiellement vide.
  static List<ConditionalRule> parse(String html) {
    try {
      return _doParse(html);
    } catch (e, st) {
      debugPrint('[ConditionalRulesParser] Erreur de parsing : $e\n$st');
      return [];
    }
  }

  static List<ConditionalRule> _doParse(String html) {
    final rules = <ConditionalRule>[];
    final trBlocks = _extractTrBlocks(html);

    // Liste des TR non-spacers avec leurs index dans trBlocks (pour look-back)
    // Chaque entrée : (index original dans trBlocks, String trHtml)
    final nonSpacerTrs = <({int idx, String html})>[];

    for (var i = 0; i < trBlocks.length; i++) {
      final tr = trBlocks[i];
      if (!_isSpacerTr(tr)) {
        nonSpacerTrs.add((idx: i, html: tr));
      }
    }

    // ── Passe 1 : topologie « même TR, deux TD côte à côte » ─────────────────
    // Ex. : TD[0]=source Oui/Non, TD[1]="Si oui, ..."
    for (final entry in nonSpacerTrs) {
      final tdBlocks = _extractTdBlocks(entry.html);
      // Cherche parmi les TD un TD "Si oui/non" et son TD précédent
      for (var j = 1; j < tdBlocks.length; j++) {
        final tdText = _tdText(tdBlocks[j]);
        String? triggerValue;
        if (_siOuiRe.hasMatch(tdText)) {
          triggerValue = '1';
        } else if (_siNonRe.hasMatch(tdText)) {
          triggerValue = '0';
        }
        if (triggerValue == null) continue;

        // TD précédent = source probable
        final sourceTd = tdBlocks[j - 1];
        final sourceFields = _extractFieldNames(sourceTd);
        if (sourceFields.isEmpty) continue;

        final targetFields = _extractFieldNames(tdBlocks[j]);
        if (targetFields.isEmpty) continue;

        // Le champ source est celui qui a les inputs radio Oui/Non
        // (on prend le premier champ du TD source)
        final sourceField = sourceFields.first;

        // Évite les doublons
        final alreadyExists = rules.any(
          (r) => r.sourceField == sourceField && r.triggerValue == triggerValue,
        );
        if (!alreadyExists) {
          rules.add(ConditionalRule(
            sourceField: sourceField,
            triggerValue: triggerValue!,
            targetFields: List.unmodifiable(targetFields),
          ));
          debugPrint(
            '[ConditionalRulesParser] Règle (même TR): $sourceField → '
            'trigger=$triggerValue → targets=$targetFields',
          );
        }
      }
    }

    // ── Passe 2 : topologie « TR suivant » ───────────────────────────────────
    // Pour chaque TR non-spacer, vérifier si son texte visible commence par
    // "Si oui" / "Si non" / "Sinon". Si oui, son champ source est dans le
    // dernier TR non-spacer précédent (look-back).
    for (var k = 1; k < nonSpacerTrs.length; k++) {
      final currentTr = nonSpacerTrs[k].html;
      final currentText = _tdText(currentTr);

      String? triggerValue;
      if (_siOuiRe.hasMatch(currentText)) {
        triggerValue = '1';
      } else if (_siNonRe.hasMatch(currentText)) {
        triggerValue = '0';
      }
      if (triggerValue == null) continue;

      // Si ce TR a déjà été traité en topologie 1 (TD côte à côte), sauter
      // Pour éviter la double détection, on vérifie que le TR entier ne contient
      // PAS un TD "Si oui" précédé d'un autre TD source dans la même ligne.
      // (Les topologie 1 ont leur "Si oui" dans un TD secondaire du même TR,
      //  les topologie 2 ont tout leur contenu dans un seul TD principal.)
      final tdBlocks = _extractTdBlocks(currentTr);
      final conditionalTdCount = tdBlocks.where((td) {
        final t = _tdText(td);
        return _siOuiRe.hasMatch(t) || _siNonRe.hasMatch(t);
      }).length;

      // Si la même TR a déjà un TD source visible (topologie 1), ignorer
      if (conditionalTdCount < tdBlocks.length && conditionalTdCount > 0) {
        // topologie 1 possible : le TD conditionnel n'est PAS le seul TD
        // → déjà prise en charge en passe 1, on saute
        continue;
      }

      // Remonter pour trouver le TR source (dernier non-spacer avant ce TR)
      final prevTr = nonSpacerTrs[k - 1].html;
      final sourceFields = _extractFieldNames(prevTr);
      if (sourceFields.isEmpty) continue;

      final targetFields = _extractFieldNames(currentTr);
      if (targetFields.isEmpty) continue;

      // Le champ source est le premier champ radio du TR précédent
      final sourceField = sourceFields.first;

      // Évite les doublons avec passe 1
      final alreadyExists = rules.any(
        (r) => r.sourceField == sourceField && r.triggerValue == triggerValue,
      );
      if (!alreadyExists) {
        rules.add(ConditionalRule(
          sourceField: sourceField,
          triggerValue: triggerValue!,
          targetFields: List.unmodifiable(targetFields),
        ));
        debugPrint(
          '[ConditionalRulesParser] Règle (TR suivant): $sourceField → '
          'trigger=$triggerValue → targets=$targetFields',
        );
      } else {
        // Si la règle existe déjà pour ce source+trigger, ajouter les cibles manquantes
        final existingIdx = rules.indexWhere(
          (r) => r.sourceField == sourceField && r.triggerValue == triggerValue,
        );
        if (existingIdx >= 0) {
          final existing = rules[existingIdx];
          final mergedTargets = List<String>.from(existing.targetFields);
          for (final t in targetFields) {
            if (!mergedTargets.contains(t)) mergedTargets.add(t);
          }
          rules[existingIdx] = ConditionalRule(
            sourceField: existing.sourceField,
            triggerValue: existing.triggerValue,
            targetFields: List.unmodifiable(mergedTargets),
          );
        }
      }
    }

    debugPrint(
      '[ConditionalRulesParser] ${rules.length} règle(s) extraite(s) du HTML.',
    );
    return rules;
  }
}
