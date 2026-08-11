import 'package:flutter/foundation.dart';

/// Représente une règle conditionnelle extraite du HTML d'un formulaire.
///
/// Signification : si la valeur du champ [sourceField] n'est PAS [triggerValue],
/// alors les champs [targetFields] doivent être désactivés (grisés + non saisissables).
class ConditionalRule {
  final String sourceField;        // champ source Oui/Non
  final String triggerValue;       // "1" (si oui) ou "0" (si non)
  final List<String> targetFields; // champs à désactiver si condition non satisfaite

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
/// ─── Topologies détectées ──────────────────────────────────────────────────
///
/// T1 — Même TR, deux TD côte à côte :
///   <TR>
///     <TD>...Oui <INPUT NAME='ELECTRICITE_0'>...Non...</TD>
///     <TD>Si oui, ...fonctionne-t-il ? <INPUT NAME='FONCT_ALIMENT_ELECTRICITE_0'></TD>
///   </TR>
///
/// T2 — TR suivant introduit par "Si oui/non/Sinon" :
///   <TR>...<INPUT NAME='DOMAINE_DELIMITE_0'>...</TR>
///   <TR>Si oui, superficie... <INPUT NAME='SUPERFICIE_DOMAINE_0'></TR>
///   → Couvre aussi les préfixes : "a) Si oui", "b) Si oui", "   Si oui"
///
/// T2-src — TR conditionnel qui est lui-même un champ source Oui/Non :
///   <TR>...<INPUT NAME='EXISTE_LAVAGE_MAINS_0'>... Si oui, à proximité?
///          <INPUT NAME='LAVAGE_MAINS_PROX_LATRINES_0'></TR>
///   → Le TR source précédent devient la source, ce champ binaire devient cible
///   → Le contexte propagé ensuite (T3) pointe vers le TR source, pas vers ce TR
///
/// T3 — Propagation de contexte (sous-questions sans marqueur "Si") :
///   Après un TR conditionnel (T2/T2-src), les TR suivants qui :
///     • ne contiennent PAS de champ source Oui/Non propre
///     • ne débutent PAS par "Si oui/non/Sinon" (ne déclenchent pas T2)
///     • ne sont pas des TR d'espacement
///     • ne commencent PAS par un marqueur de nouvelle question <b>N.N
///   sont rattachés au même source conditionnel, jusqu'au prochain TR qui :
///     • contient un INPUT radio Oui/Non autonome (nouveau champ source)
///     • ou commence par un marqueur de nouvelle question principale
///
///   Exemples :
///   LATRINES_DISPOSE_0 → [NB_LATRINES_ELEVES_0, NB_LATRINES_FILLES_0,
///                          NB_LATRINES_ELEVES_NON_FONCT_0, NB_LATRINES_BON_ETAT_0,
///                          NB_LATRINES_BON_ETAT_F_0]
///   EXISTE_LAVAGE_MAINS_0 → [LAVAGE_MAINS_PROX_LATRINES_0,
///                             NB_INSTALL_LAVAGE_MAINS_T_0, NB_INSTALL_LAVAGE_MAINS_F_0,
///                             NB_LAVAGE_MAINS_FONCT_T_0, NB_LAVAGE_MAINS_FONCT_F_0]
class ConditionalRulesParser {
  // ── Regex "Si oui" ──────────────────────────────────────────────────────────
  // Accepte : "Si oui", "a) Si oui", "b) Si oui", "&nbsp; Si oui", espaces, etc.
  // Le caractère avant "si" peut être : espaces, &nbsp;, ponctuation a-z), chiffres)
  static final RegExp _siOuiRe = RegExp(
    r'(?:^|[\s\xa0])[a-z]?\)?\s*(?:&nbsp;|\s)*si\s+oui\b',
    caseSensitive: false,
  );

  // "Si non" ou "Sinon"
  static final RegExp _siNonRe = RegExp(
    r'(?:^|[\s\xa0])[a-z]?\)?\s*(?:&nbsp;|\s)*(?:si\s+non|sinon)\b',
    caseSensitive: false,
  );

  // Marqueur de nouvelle question principale : <b>N.N  (ex: <b>2.4, <b>3.1)
  // → stoppe la propagation T3 vers les sections suivantes
  static final RegExp _newQRe = RegExp(
    r'<b>\s*\d+\.\d+',
    caseSensitive: false,
  );

  // Détecte un champ radio Oui/Non source : INPUT type=radio avec _0 ou _1 en VALUE
  // Critère : le TD/TR contient au moins 2 INPUT TYPE=radio avec le même NAME de base
  static final RegExp _radioInputRe =
      RegExp(r"""<INPUT\b[^>]*TYPE=['"]?radio['"]?[^>]*>""", caseSensitive: false);

  static final RegExp _nameAttrRe =
      RegExp(r"""NAME=['"]?([A-Za-z_][A-Za-z0-9_]*)['"]?""", caseSensitive: false);

  // ── Utilitaires ────────────────────────────────────────────────────────────

  static String _tdText(String td) {
    var text = td.replaceAll(RegExp(r'<[^>]*>'), ' ');
    text = text
        .replaceAll('&nbsp;', ' ')
        .replaceAll('\xa0', ' ')
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>')
        .replaceAll('&amp;', '&')
        .replaceAll('&#8217;', "'")
        .replaceAll('&#8216;', "'");
    return text.replaceAll(RegExp(r'\s+'), ' ').trim();
  }

  static List<String> _extractFieldNames(String html) {
    final names = <String>[];
    for (final m in _nameAttrRe.allMatches(html)) {
      final raw = m.group(1)!;
      final normalized = _normalizeFieldName(raw);
      if (!names.contains(normalized)) names.add(normalized);
    }
    return names;
  }

  /// Normalise : retire le dernier segment numérique si les deux derniers le sont.
  /// ELECTRICITE_0_1 → ELECTRICITE_0
  /// NB_LATRINES_ELEVES_0 → NB_LATRINES_ELEVES_0 (inchangé)
  static String _normalizeFieldName(String name) {
    final parts = name.split('_');
    if (parts.length >= 3) {
      if (RegExp(r'^\d+$').hasMatch(parts.last) &&
          RegExp(r'^\d+$').hasMatch(parts[parts.length - 2])) {
        return parts.sublist(0, parts.length - 1).join('_');
      }
    }
    return name;
  }

  static bool _isSpacerTr(String tr) {
    final text = _tdText(tr);
    return text.isEmpty ||
        text == ' ' ||
        tr.toLowerCase().contains('td_space_blanc');
  }

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

  // ── Point d'entrée ─────────────────────────────────────────────────────────

  static List<ConditionalRule> parse(String html) {
    try {
      return _doParse(html);
    } catch (e, st) {
      debugPrint('[ConditionalRulesParser] Erreur : $e\n$st');
      return [];
    }
  }

  static List<ConditionalRule> _doParse(String html) {
    final rules = <ConditionalRule>[];
    final trBlocks = _extractTrBlocks(html);

    // Liste des TR non-spacers (index original + html)
    final nonSpacers = <({int idx, String html})>[];
    for (var i = 0; i < trBlocks.length; i++) {
      if (!_isSpacerTr(trBlocks[i])) {
        nonSpacers.add((idx: i, html: trBlocks[i]));
      }
    }

    // ── Passe 1 : T1 — même TR, deux TD côte à côte ──────────────────────────
    // TD source (Oui/Non) + TD suivant commençant par "Si oui/non"
    // On enregistre les indices k (dans nonSpacers) traités par T1
    // pour éviter qu'ils soient retraités comme T2-src dans la Passe 2.
    final t1TrIndices = <int>{};
    for (var ki = 0; ki < nonSpacers.length; ki++) {
      final entry = nonSpacers[ki];
      final tdBlocks = _extractTdBlocks(entry.html);
      for (var j = 1; j < tdBlocks.length; j++) {
        final tdText = _tdText(tdBlocks[j]);
        String? trigger;
        if (_siOuiRe.hasMatch(tdText)) {
          trigger = '1';
        } else if (_siNonRe.hasMatch(tdText)) {
          trigger = '0';
        }
        if (trigger == null) continue;

        final sourceTd = tdBlocks[j - 1];
        final sourceFields = _extractFieldNames(sourceTd);
        if (sourceFields.isEmpty) continue;
        final targetFields = _extractFieldNames(tdBlocks[j]);
        if (targetFields.isEmpty) continue;

        final sourceField = sourceFields.first;
        _mergeRule(rules, sourceField, trigger, targetFields, 'T1');
        t1TrIndices.add(ki);
      }
    }

    // ── Passe 2 : T2 — TR suivant introduit par "Si oui/non/Sinon" ──────────
    // + T2-src : TR conditionnel qui est lui-même un champ source Oui/Non
    // + T3 : propagation de contexte aux TR fils sans marqueur "Si"
    //
    // Contexte actif : (ctxSource, ctxTrigger) — le source conditionnel courant.
    // T2-src : quand on trouve un TR binaire qui est aussi conditionnel,
    //   • on crée une règle T2-src liant src_back → ce champ binaire
    //   • on met le contexte à (src_back, trigger) pour que T3 propage les TR fils
    //     vers src_back (et non vers ce champ binaire lui-même)
    String? ctxSource;
    String? ctxTrigger;

    for (var k = 0; k < nonSpacers.length; k++) {
      final currentTr = nonSpacers[k].html;
      final currentText = _tdText(currentTr);

      // Détecter si ce TR est un nouveau TR source Oui/Non (champ binaire)
      final radioNames = _getOuiNonFieldNames(currentTr);
      final isBinaryTr = radioNames.isNotEmpty;

      // Détecter "Si oui/non" dans ce TR
      String? trigger;
      if (_siOuiRe.hasMatch(currentText)) {
        trigger = '1';
      } else if (_siNonRe.hasMatch(currentText)) {
        trigger = '0';
      }

      // Détecter si ce TR marque une nouvelle question principale (stoppe T3)
      final isNewQ = _newQRe.hasMatch(currentTr);

      if (isBinaryTr) {
        // ── T2-src : ce TR est lui-même conditionnel (contient "Si oui/non")
        //            ET n'est pas déjà traité par T1 (t1TrIndices)
        //            ET il y a un TR précédent non-spacer
        if (trigger != null && !t1TrIndices.contains(k) && k > 0) {
          final srcBack = _findLastSourceField(nonSpacers, k);
          if (srcBack != null && srcBack != radioNames.first) {
            // Ce champ binaire est une cible du champ source précédent
            _mergeRule(rules, srcBack, trigger, [radioNames.first], 'T2-src');
            // La propagation T3 suivante se fait depuis src_back (pas depuis ce champ)
            ctxSource = srcBack;
            ctxTrigger = trigger;
          } else {
            // Pas de src_back distinct → ce TR devient simplement le nouveau source
            ctxSource = radioNames.first;
            ctxTrigger = null;
          }
        } else {
          // Nouveau champ source binaire sans condition → réinitialiser le contexte
          ctxSource = radioNames.first;
          ctxTrigger = null;
        }
        continue; // passer au TR suivant
      }

      // Ce TR n'est PAS un champ source Oui/Non

      // Stopper la propagation T3 si nouvelle question principale sans "Si"
      if (isNewQ && trigger == null) {
        ctxSource = null;
        ctxTrigger = null;
        continue;
      }

      if (trigger != null) {
        // T2 : TR conditionnel → lier au dernier TR source trouvé (look-back)
        final sourceField = _findLastSourceField(nonSpacers, k);
        if (sourceField != null) {
          final targetFields = _extractFieldNames(currentTr);
          if (targetFields.isNotEmpty) {
            _mergeRule(rules, sourceField, trigger, targetFields, 'T2');
            // Activer propagation T3 depuis ce source
            ctxSource = sourceField;
            ctxTrigger = trigger;
          }
        }
      } else if (ctxSource != null && ctxTrigger != null) {
        // T3 : propagation de contexte — ce TR est un "fils" du contexte actif
        final targetFields = _extractFieldNames(currentTr);
        if (targetFields.isNotEmpty) {
          _mergeRule(rules, ctxSource!, ctxTrigger!, targetFields, 'T3');
        }
        // Continuer la propagation (ne pas réinitialiser ctxSource)
      }
      // Ni trigger ni contexte actif : TR autonome, ignorer
    }

    debugPrint(
      '[ConditionalRulesParser] ${rules.length} règle(s) extraite(s).',
    );
    for (final r in rules) {
      debugPrint('  $r');
    }
    return rules;
  }

  // ── Helpers ────────────────────────────────────────────────────────────────

  /// Fusionne les targetFields dans une règle existante ou crée une nouvelle règle.
  static void _mergeRule(
    List<ConditionalRule> rules,
    String sourceField,
    String triggerValue,
    List<String> newTargets,
    String topology,
  ) {
    final idx = rules.indexWhere(
      (r) => r.sourceField == sourceField && r.triggerValue == triggerValue,
    );
    if (idx < 0) {
      rules.add(ConditionalRule(
        sourceField: sourceField,
        triggerValue: triggerValue,
        targetFields: List.unmodifiable(newTargets),
      ));
      debugPrint(
        '[ConditionalRulesParser] [$topology] $sourceField → '
        'trigger=$triggerValue → $newTargets',
      );
    } else {
      final existing = rules[idx];
      final merged = List<String>.from(existing.targetFields);
      var added = false;
      for (final t in newTargets) {
        if (!merged.contains(t)) {
          merged.add(t);
          added = true;
        }
      }
      if (added) {
        rules[idx] = ConditionalRule(
          sourceField: existing.sourceField,
          triggerValue: existing.triggerValue,
          targetFields: List.unmodifiable(merged),
        );
        debugPrint(
          '[ConditionalRulesParser] [$topology] Merge $sourceField += $newTargets',
        );
      }
    }
  }

  /// Extrait les noms de base des champs radio Oui/Non (exactement 2 options)
  /// présents dans un TR.
  static List<String> _getOuiNonFieldNames(String trHtml) {
    final radioMatches = _radioInputRe.allMatches(trHtml).toList();
    final nameCounts = <String, int>{};
    for (final m in radioMatches) {
      final nameM = _nameAttrRe.firstMatch(m.group(0)!);
      if (nameM == null) continue;
      final base = _normalizeFieldName(nameM.group(1)!);
      nameCounts[base] = (nameCounts[base] ?? 0) + 1;
    }
    return nameCounts.entries
        .where((e) => e.value >= 2)
        .map((e) => e.key)
        .toList();
  }

  /// Remonte dans la liste des TR non-spacers pour trouver le dernier champ
  /// source Oui/Non (avant la position k).
  static String? _findLastSourceField(
    List<({int idx, String html})> nonSpacers,
    int k,
  ) {
    for (var j = k - 1; j >= 0; j--) {
      final names = _getOuiNonFieldNames(nonSpacers[j].html);
      if (names.isNotEmpty) return names.first;
    }
    return null;
  }
}
