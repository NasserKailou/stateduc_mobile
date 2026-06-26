import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../../models/question.dart';

/// DynamicFormWidget — affiche le formulaire HTML du serveur dans un WebView.
///
/// Architecture générale :
///   1. Le HTML brut est transmis en paramètre (mis en cache dans SQLite lors
///      du téléchargement de la campagne).
///   2. Le WebView charge le HTML via une data-URI Base64 (UTF-8) afin que
///      les caractères accentués s'affichent toujours correctement,
///      indépendamment de la locale de l'appareil.
///   3. Un pont JavaScript ('FieldChanged') renvoie les événements de changement
///      de champ vers Flutter dès qu'un input/select/textarea change.
///   4. À l'initialisation, les valeurs existantes ([data]) sont injectées via
///      JavaScript pour que le formulaire affiche les valeurs préalablement sauvegardées.
///   5. Les erreurs de validation sont affichées via JavaScript en ajoutant
///      une bordure rouge aux champs concernés.
///   6. Un bouton natif "Ajouter une ligne" (FAB) est superposé pour les formulaires
///      de type grille, permettant d'ajouter des lignes même si les boutons
///      JavaScript du serveur ne fonctionnent pas dans le WebView.
///
/// Traitement du HTML avant affichage [_preprocessHtml] :
///   1. Réparation du mojibake ISO-8859-15 → UTF-8 (seuil 5% U+FFFD)
///   2. Décodage des entités HTML (&lt; &gt; &amp; …) — deux passes
///   3. Substitution des numéros de lignes $NUMERO_LOCAL_N (1-based)
///   4. Substitution des placeholders PHP $VAR dans les attributs VALUE=
///
/// Pont JavaScript [_injectBridge] :
///   - Tous les inputs/selects/textareas envoient leurs valeurs via FieldChanged.postMessage
///   - Les boutons "Ajouter" (addGrilleLine) déclenchent __addGridRow__
///
/// Gestion des grilles (formulaires de type tableau) :
///   - Détection : [_detectGridForm] — $NUMERO_LOCAL, addGrilleLine, NAME double-indexé
///   - Comptage des lignes : [_countGridRows] — deux patterns (NUMERO_LOCAL et ligne-paire)
///   - Ajout de lignes : bouton natif + JavaScript fallback (clone de la dernière ligne)
class DynamicFormWidget extends StatefulWidget {
  const DynamicFormWidget({
    super.key,
    required this.html,
    required this.data,
    required this.validationErrors,
    required this.rules,
    required this.onFieldChanged,
    this.onAddGridRow,
  });

  final String html;
  final Map<String, String> data;
  final Map<String, String> validationErrors;
  final List<ValidationRule> rules;
  final void Function(String fieldName, String value) onFieldChanged;
  final void Function(String tableId)? onAddGridRow;

  @override
  State<DynamicFormWidget> createState() => _DynamicFormWidgetState();
}

class _DynamicFormWidgetState extends State<DynamicFormWidget> {
  late final WebViewController _controller;
  bool _pageLoaded = false;
  bool _isRendering =
      false; // vrai pendant le chargement initial du HTML dans le WebView

  // Vrai si le HTML correspond à un formulaire de type grille (tableau multi-lignes).
  bool get _isGridForm => _detectGridForm(widget.html);

  // Nombre de lignes de grille actuellement affichées (pour le label du bouton FAB).
  int _gridRowCount = 0;

  @override
  void initState() {
    super.initState();
    _gridRowCount = _countGridRows(widget.html);
    _isRendering = true;
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(
          Colors.white) // ← évite le flash gris avant le chargement
      ..addJavaScriptChannel(
        'FieldChanged',
        // Pont JavaScript → Flutter : reçoit les changements de champs depuis le WebView.
        // msg.message = JSON : {"name":"nomDuChamp","value":"valeurDuChamp"}
        // Cas spécial : name == '__addGridRow__' → demande d'ajout de ligne
        onMessageReceived: (JavaScriptMessage msg) {
          try {
            final m = json.decode(msg.message) as Map<String, dynamic>;
            final name = m['name']?.toString() ?? '';
            final value = m['value']?.toString() ?? '';
            if (name == '__addGridRow__') {
              // Demande d'ajout de ligne depuis le pont ou le bouton FAB
              widget.onAddGridRow?.call(value);
            } else if (name.isNotEmpty) {
              widget.onFieldChanged(name, value);
            }
          } catch (_) {}
        },
      )
      ..setNavigationDelegate(NavigationDelegate(
        onPageFinished: (_) {
          _pageLoaded = true;
          if (mounted) setState(() => _isRendering = false);
          // addPostFrameCallback : attend que Flutter ait propagé les dernières
          // widget.data (incluant les champs d'identification pré-remplis) avant
          // d'injecter les valeurs dans le WebView.
          // Sans cela, _injectData() pourrait s'exécuter avec des widget.data
          // obsolètes/vides si notifyListeners() du provider n'a pas encore
          // déclenché le rebuild de ce widget.
          WidgetsBinding.instance.addPostFrameCallback((_) {
            _injectData();
            _refreshMatrixTotals();
            _injectBridge();
          });
        },
        onWebResourceError: (err) {
          debugPrint('[DynamicForm] WebView error: ${err.description}');
          if (mounted) setState(() => _isRendering = false);
        },
      ))
      ..loadRequest(_buildHtmlUri(widget.html));
  }

  @override
  void didUpdateWidget(DynamicFormWidget old) {
    super.didUpdateWidget(old);
    // Recharge le HTML quand le formulaire change (changement de question)
    if (old.html != widget.html) {
      _pageLoaded = false;
      _isRendering = true;
      setState(() {
        _gridRowCount = _countGridRows(widget.html);
      });
      _controller.loadRequest(_buildHtmlUri(widget.html));
      return;
    }
    // Réinjecte les données quand elles changent de l'extérieur
    // (ex. rechargement depuis le serveur, pré-remplissage identification)
    if (_pageLoaded &&
        (old.data != widget.data ||
            old.validationErrors != widget.validationErrors)) {
      _injectData();
      _refreshMatrixTotals();
      _injectValidationErrors();
    }
  }

  // ── Convertit le HTML en data-URI Base64 pour forcer l'encodage UTF-8 ──────
  //
  // loadHtmlString() ne respecte pas de manière fiable le <meta charset="UTF-8">
  // sur Android, ce qui provoque l'affichage des caractères accentués (é, è, à…)
  // sous forme de Mojibake (Ã©, etc.). En encodant les octets en Base64 et en
  // chargeant via une data: URI, on force le moteur à décoder en UTF-8.
  Uri _buildHtmlUri(String formHtml) {
    final processed = _preprocessHtml(formHtml);
    final bytes = utf8.encode(_buildHtmlPage(processed));
    final base64Html = base64Encode(bytes);
    return Uri.parse('data:text/html;charset=utf-8;base64,$base64Html');
  }

  // ── Prétraitement du HTML brut du serveur avant affichage ─────────────────
  //
  // 1. Réparation du mojibake ISO-8859-15 → UTF-8 :
  //    Les fichiers HTML du serveur sont encodés en ISO-8859-15. Quand sqflite
  //    les stocke/lit comme des chaînes Dart, chaque octet > 0x7F devient le
  //    point de code Unicode correspondant (interprétation Latin-1), donc les
  //    séquences UTF-8 multi-octets pour les caractères français apparaissent
  //    comme des paires telles que U+00C3 U+00A9 ("Ã©").
  //    Correction : ré-encode les unités de code ≤ 0xFF comme octets bruts
  //    et décode en UTF-8.
  //
  //    Seuil de tolérance : accepte le résultat même si < 5% de U+FFFD
  //    apparaissent (séquences d'octets orphelins comme espace insécable).
  //    Rejeter l'ensemble de la réparation à cause d'une poignée de U+FFFD
  //    laisserait TOUS les caractères accentués illisibles, ce qui est pire.
  //
  // 2. Décodage des entités HTML :
  //    Certains fichiers de formulaire ont des entités HTML doublement encodées.
  //    Symptôme typique : "&lt;b&gt;1.6 …&lt;/b&gt;" affiché littéralement.
  //    On décode les cinq entités standard XML/HTML avant le rendu.
  //    Deux passes pour gérer le double-encodage (&amp;lt; → &lt; → <).
  //
  // 3. Substitution $NUMERO_LOCAL_N :
  //    Les lignes de template de grille contiennent "$NUMERO_LOCAL_0", "_1" etc.
  //    Remplace par des numéros d'affichage (base 1).
  //
  // 4. Substitution des placeholders PHP $VAR dans les attributs VALUE= :
  //    Les formulaires HTML sont des templates PHP servis après substitution
  //    de variables. L'application mobile reçoit le template brut avec les
  //    placeholders $VAR non résolus (cache HTML statique).
  //    Deux patterns :
  //    a) Entrées texte entre guillemets : VALUE="$NOM_ETABLISSEMENT_0"
  //       → Remplace par VALUE="" (vide), _injectData() remplira ensuite.
  //    b) Options radio/select non quotées : VALUE=$CODE_TYPE_SEXE_0_1
  //       → Le dernier segment numérique est la vraie valeur de l'option.
  //       → Remplace par VALUE=1 pour que el.checked=(el.value===val) fonctionne.
  String _preprocessHtml(String html) {
    // ── 1. Réparation du mojibake ──────────────────────────────────────────
    // Stratégie : tente toujours la réparation si des patterns mojibake sont détectés.
    // Accepte le résultat décodé même si un petit nombre de \uFFFD apparaît
    // (< 5% de la longueur totale) — provient d'octets de continuation orphelins
    // comme U+00C2 suivi d'un espace ASCII (séquence espace insécable).
    if (_looksLikeMojibake(html)) {
      try {
        // Traite chaque unité de code comme un octet Latin-1 brut (≤ 0xFF)
        final latin1Bytes = <int>[
          for (final c in html.codeUnits)
            if (c <= 0xFF) c,
        ];
        final decoded = utf8.decode(latin1Bytes, allowMalformed: true);
        // Compte les caractères de remplacement — accepte si < 5% de la longueur
        final replacements = '\uFFFD'.allMatches(decoded).length;
        final threshold = (latin1Bytes.length * 0.05).ceil();
        if (replacements <= threshold) {
          html = decoded;
          debugPrint('[DynamicForm] Mojibake repaired '
              '(${latin1Bytes.length} bytes, $replacements replacement chars)');
        } else {
          debugPrint('[DynamicForm] Mojibake repair rejected '
              '($replacements replacements > threshold $threshold)');
        }
      } catch (_) {
        // Conserve l'original si la réparation échoue
      }
    }

    // ── 2. Décodage des entités HTML ────────────────────────────────────────
    // Exécuté APRÈS la réparation mojibake pour capturer aussi les entités
    // dans le texte réparé.
    // Deux passes pour gérer le double-encodage (&amp;lt; → &lt; → <)
    for (int pass = 0; pass < 2; pass++) {
      html = html
          .replaceAll('&lt;', '<')
          .replaceAll('&gt;', '>')
          .replaceAll('&amp;', '&')
          .replaceAll('&quot;', '"')
          .replaceAll('&#39;', "'")
          .replaceAll('&nbsp;', '\u00A0')
          .replaceAll('&apos;', "'");
    }

    // ── 3. $NUMERO_LOCAL_N → numéro de ligne (base 1) ─────────────────────
    html = html.replaceAllMapped(
      RegExp(r'\$NUMERO_LOCAL_(\d+)'),
      (m) {
        final n = int.tryParse(m.group(1) ?? '0') ?? 0;
        return (n + 1).toString();
      },
    );

    // ── 4a. VALUE="$VAR" entre guillemets → VALUE="" ────────────────────────
    // Matche : value="$NOM_QCQ_VAR_0" (attribut insensible à la casse)
    // NOTE : le RegExp de Dart ne supporte PAS les flags inline (?i).
    //        Utiliser le paramètre caseSensitive: false à la place.
    html = html.replaceAllMapped(
      RegExp(r'(value=)"(\$[A-Z_][A-Z_0-9]*)"', caseSensitive: false),
      (m) => '${m.group(1)!}""',
    );

    // ── 4b. VALUE=$VAR non quoté → VALUE=<dernier-segment-numérique> ────────
    // Matche : VALUE=$CODE_TYPE_SEXE_0_1  → VALUE=1
    //          VALUE=$CODE_DIPLOME_0_12   → VALUE=12
    // Le dernier segment séparé par un underscore est la valeur de l'option.
    html = html.replaceAllMapped(
      RegExp(r'(value=)\$([A-Z_][A-Z_0-9]*)', caseSensitive: false),
      (m) {
        final varName = m.group(2)!;
        final lastSeg = varName.contains('_')
            ? varName.substring(varName.lastIndexOf('_') + 1)
            : varName;
        return '${m.group(1)!}$lastSeg';
      },
    );

    return html;
  }

  // Retourne vrai si la chaîne contient probablement des octets ISO-8859-1
  // mal interprétés comme des points de code Unicode individuels (Mojibake).
  //
  // Les séquences ISO-8859-15 → UTF-8 à deux octets commencent par 0xC2 ou 0xC3 :
  //   0xC3 (U+00C3) + 0x80–0xBF → caractères latins : é è à ô î â ë ï û ù ç ü Ô Î …
  //   0xC2 (U+00C2) + 0x80–0xBF → caractères spéciaux : ° nbsp © ® « » …
  //     Fréquents : 0xC2 0xA0 = espace insécable (affiché 'Â ' en mojibake)
  //                 0xC2 0xB0 = degré (affiché 'Â°')
  //
  // Capte aussi 0xE2 (U+00E2) début d'une séquence UTF-8 à 3 octets :
  //   0xE2 + 0x80 + 0x99 = U+2019 apostrophe courbe droite
  //   affiché 'â€™' en mojibake (ex. "l'établissement" → "lâ€™établissement")
  bool _looksLikeMojibake(String s) {
    // Vérification rapide : patterns mojibake français courants (deux caractères)
    if (s.contains('Ã©') ||
        s.contains('Ã¨') ||
        s.contains('Ã ') ||
        s.contains('Ã´') ||
        s.contains('Ã®') ||
        s.contains('Ã¢') ||
        s.contains('Ã«') ||
        s.contains('Ã¯') ||
        s.contains('Ã»') ||
        s.contains('Ã¹') ||
        s.contains('Ã§') ||
        s.contains('Ã¼') ||
        s.contains('Ãˆ') ||
        s.contains('Ã‰') ||
        s.contains('Ã€') ||
        s.contains('Â°') ||
        s.contains('Â ') || // degré + espace insécable
        s.contains('Â«') ||
        s.contains('Â»') || // guillemets français
        s.contains('â€™') ||
        s.contains('â€œ') || // apostrophes courbes
        s.contains('Nâ') ||
        s.contains('nÂ°')) {
      // patterns N°
      return true;
    }
    // Vérification approfondie : recherche un octet de tête UTF-8 suivi
    // d'un octet de continuation dans la chaîne
    for (int i = 0; i < s.length - 1; i++) {
      final c = s.codeUnitAt(i);
      if (c == 0xC2 || c == 0xC3) {
        final next = s.codeUnitAt(i + 1);
        if (next >= 0x80 && next <= 0xBF) return true;
      }
      // Début de séquence UTF-8 à 3 octets (0xE2 = 'â' en Latin-1)
      if (c == 0xE2 && i + 2 < s.length) {
        final n1 = s.codeUnitAt(i + 1);
        final n2 = s.codeUnitAt(i + 2);
        if (n1 >= 0x80 && n1 <= 0xBF && n2 >= 0x80 && n2 <= 0xBF) return true;
      }
    }
    return false;
  }

  // ── Détecte si ce formulaire est de type grille (tableau multi-lignes) ────
  //
  // Les formulaires grille ont plusieurs champs de ligne identifiés par :
  //   • $NUMERO_LOCAL_N  — placeholder de numéro de ligne (formulaires locaux)
  //   • addGrilleLine    — fonction JS qui ajoute une nouvelle ligne
  //   • NUMERO_LOCAL     — correspondance partielle (même famille de formulaires)
  //   • MiseEvidenceLigneFrame — JS utilisé dans tous les tableaux grille multi-lignes
  //   • NAME='FIELD_N_V'  — nom de champ doublement indexé (index ligne + valeur option)
  bool _detectGridForm(String html) {
    return html.contains(r'$NUMERO_LOCAL') ||
        html.contains('addGrilleLine') ||
        html.contains('NUMERO_LOCAL') ||
        html.contains('MiseEvidenceLigneFrame') ||
        RegExp(r"NAME='[A-Z_]+_\d+_\d+'").hasMatch(html);
  }

  // Compte les lignes de grille déjà présentes dans le HTML (pour les formulaires grille).
  // Gère deux patterns :
  //   1. $NUMERO_LOCAL_N  — placeholder de numéro de ligne (locaux, etc.)
  //   2. ligne-paire_N_0 / ligne-impaire_N_0 — pattern d'id CSS de ligne
  //      (personnel, identification_local, etc.)
  int _countGridRows(String html) {
    int max = -1;

    // Pattern 1 : $NUMERO_LOCAL_N
    for (final m in RegExp(r'\$NUMERO_LOCAL_(\d+)').allMatches(html)) {
      final n = int.tryParse(m.group(1) ?? '') ?? 0;
      if (n > max) max = n;
    }
    if (max >= 0) return max + 1;

    // Pattern 2 : id='ligne-paire_N_0' ou id='ligne-impaire_N_0'
    for (final m
        in RegExp(r"ligne-(?:paire|impaire)_(\d+)_0").allMatches(html)) {
      final n = int.tryParse(m.group(1) ?? '') ?? 0;
      if (n > max) max = n;
    }
    return max >= 0 ? max + 1 : 0;
  }

  // ── Construit la page HTML complète chargée dans le WebView ───────────────
  //
  // Encapsule le fragment HTML du formulaire dans une page complète avec :
  //   - <meta charset="UTF-8"> et viewport pour le mobile
  //   - CSS : mise en forme des champs, tableaux horizontalement défilants,
  //     champs de total (fond bleu), masquage des éléments Cordova
  //   - JavaScript : wrapping automatique des tableaux dans des divs défilants
  String _buildHtmlPage(String formHtml) {
    return '''<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<style>
  * { box-sizing: border-box; }
  body {
    font-family: Arial, sans-serif;
    font-size: 13px;
    margin: 4px;
    padding: 0;
    background: #fff;
    color: #000;
  }
  /* Défilement horizontal pour les tableaux de grille larges */
  body > form, body > table, .div-table-questionnaire {
    overflow-x: auto;
    display: block;
    -webkit-overflow-scrolling: touch;
  }
  table { border-collapse: collapse; min-width: 100%; }
  td, th {
    border: 1px solid #ccc;
    padding: 4px 6px;
    vertical-align: middle;
    white-space: nowrap;
  }
  th { background: #dce6f1; font-weight: bold; font-size: 12px; }
  /* Autoriser le retour à la ligne dans les cellules d'en-tête */
  th { white-space: normal; min-width: 60px; }
  input[type=text], input[type=number], textarea, select {
    width: 100%;
    min-width: 60px;
    padding: 4px;
    border: 1px solid #aaa;
    border-radius: 3px;
    font-size: 13px;
    background: #fff;
    color: #000;
  }
  input[type=text].error, input[type=number].error,
  textarea.error, select.error {
    border-color: red;
  }
  input[type=radio], input[type=checkbox] {
    transform: scale(1.3);
    margin: 4px;
  }
  input[readonly], input[disabled] {
    background: #f5f5f5;
    color: #555;
  }
  /* Masque les éléments Cordova qui ne fonctionnent pas dans le WebView */
  .ui-loader, [data-role=navbar], [data-role=footer],
  input[id^=btn_save], input[name^=btn_save],
  input[id^=btn_save_and], input[name^=btn_save_and] { display: none !important; }
  /* Style des champs de total */
  input.total_, input[name^=total_] {
    background: #e8f0fe;
    font-weight: bold;
    color: #1a237e;
    text-align: right;
  }
  label { font-weight: 500; }
  /* Défilement horizontal des tableaux grille — wrap dans un conteneur défilant */
  .table-questionnaire { overflow-x: auto; display: block; }
  /* Bouton d'ajout de ligne dans les formulaires grille */
  .grille-add-row {
    display: block;
    margin: 8px 0;
    padding: 10px 16px;
    background: #1565C0;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    width: 100%;
    text-align: center;
    font-weight: bold;
  }
  .grille-add-row:active { background: #0d47a1; }
</style>
<script>
// Wrap tous les tableaux dans un div défilant après le chargement (pour les tableaux grille larges)
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('table').forEach(function(tbl) {
    if (!tbl.parentElement.classList.contains('div-table-questionnaire')) {
      var wrapper = document.createElement('div');
      wrapper.className = 'div-table-questionnaire';
      tbl.parentNode.insertBefore(wrapper, tbl);
      wrapper.appendChild(tbl);
    }
  });
});
</script>
</head>
<body>
$formHtml
</body>
</html>''';
  }

  // ── Injecte les valeurs de champs sauvegardées dans le formulaire ──────────
  //
  // IMPORTANT : ne pas conditionner à widget.data.isEmpty — les champs
  // d'identification sont pré-remplis dans _formData AVANT le chargement de la
  // page, et ils doivent être injectés même quand aucune donnée sauvegardée
  // n'existe pour ce thème.
  //
  // Gère trois types de champs HTML :
  //   - radio   : el.checked = (el.value === val)
  //   - checkbox : el.checked = (val === '1' || val === 'true')
  //   - autres   : el.value = val
  //
  // Fallback d'attribut : si le sélecteur CSS ne trouve rien, itère tous les
  // inputs/selects en comparant l'attribut NAME en majuscules (gère les
  // cas limites de casse non standard dans les anciens formulaires).
  void _injectData() {
    if (!_pageLoaded) return;
    final jsonData = json.encode(widget.data);
    // ⚠️  TOUT LE CODE CI-DESSOUS (jusqu'à ''') EST DU JAVASCRIPT, PAS DU DART.
    //     Ne jamais remplacer la syntaxe JS par de la syntaxe Dart ici.
    //     Exemples JS valides ici :  /regex/.test(x)   ===   var x   typeof
    _controller.runJavaScript('''
(function() {
  var data = $jsonData;
  for (var name in data) {
    var val = data[name];
    // Sélecteur CSS par attribut name (insensible à la casse en HTML5 pour les attributs standard)
    var els = document.querySelectorAll('[name="' + name + '"]');
    // Fallback : itère tous les inputs/selects si le sélecteur ne retourne rien
    // (gère les cas limites avec une casse non standard dans l'attribut)
    if (!els || els.length === 0) {
      var allInputs = document.querySelectorAll('input, select, textarea');
      var arr = [];
      for (var j = 0; j < allInputs.length; j++) {
        if ((allInputs[j].getAttribute('name') || '').toUpperCase() === name.toUpperCase()) {
          arr.push(allInputs[j]);
        }
      }
      els = arr;
    }
    var elArr = els instanceof Array ? els : Array.prototype.slice.call(els);
    elArr.forEach(function(el) {
      if (el.type === 'radio') {
        // Normalise les anciens identifiants serveur style "CODE_TYPE_ACCES_0_6" → "6"
        var normalizedVal = val;
        var lastUnder = val.lastIndexOf('_');
        if (lastUnder >= 0) {
          var lastSeg = val.substring(lastUnder + 1);
         if (/^\\d+\$/.test(lastSeg)) { normalizedVal = lastSeg; }  // JS regex — NE PAS modifier: ce code est du JavaScript (string WebView, pas du Dart)
        }
        el.checked = (el.value === normalizedVal);
      } else if (el.type === 'checkbox') {
        el.checked = (val === '1' || val === 'true');
      } else {
        el.value = val;
      }
    });
  }
})();
''');
  }

  // ── Recalcule les totaux matrix après injection des données ─────────────────
  //
  // session 32 : _injectData() remplit les champs NB_ depuis SQLite, mais
  // computeMatrixTotals() (défini dans _get_mobile_css_js()) a déjà tourné au
  // DOMContentLoaded avant que les valeurs soient injectées → les totaux
  // affichaient 0. Ce call déclenche un nouveau calcul APRÈS l'injection.
  //
  // ⚠️  JAVASCRIPT ci-dessous (jusqu'à ''') — ne pas utiliser syntaxe Dart ici.
  void _refreshMatrixTotals() {
    _controller.runJavaScript('''
(function() {
  if (typeof computeMatrixTotals !== 'function') return;
  document.querySelectorAll('table.table-questionnaire').forEach(function(table) {
    var firstNb = table.querySelector('input[type=text]:not([readonly])');
    if (firstNb && /^NB_/i.test(firstNb.name || '')) {
      computeMatrixTotals(firstNb);
    }
  });
})();
''');
  }

  // ── Câble tous les champs du formulaire pour renvoyer les changements via FieldChanged ──
  //
  // Après l'injection initiale des données, connecte tous les inputs/selects/textareas
  // au pont JavaScript FieldChanged pour transmettre chaque modification à Flutter.
  //
  // Cas spécial : les boutons "Ajouter" (qui contiennent addGrilleLine dans
  // leur onclick) sont reliés pour envoyer '__addGridRow__' via le pont,
  // déclenchant l'ajout d'une nouvelle ligne dans le formulaire grille.
  void _injectBridge() {
    // ⚠️  JAVASCRIPT ci-dessous (jusqu'à ''') — ne pas utiliser syntaxe Dart ici.
    _controller.runJavaScript('''
(function() {
  function notify(name, value) {
    FieldChanged.postMessage(JSON.stringify({name: name, value: value}));
  }
  document.querySelectorAll('input, textarea, select').forEach(function(el) {
    var name = el.name;
    if (!name) return;
    if (el.type === 'radio') {
      el.addEventListener('change', function() {
        if (el.checked) notify(name, el.value);
      });
    } else if (el.type === 'checkbox') {
      el.addEventListener('change', function() {
        notify(name, el.checked ? '1' : '0');
      });
    } else {
      el.addEventListener('input', function() { notify(name, el.value); });
      el.addEventListener('change', function() { notify(name, el.value); });
    }
  });

  // Câble les boutons "Ajouter" du serveur (addGrilleLine) si présents
  document.querySelectorAll('input[type=button], button').forEach(function(btn) {
    var oc = btn.getAttribute('onclick') || '';
    var txt = (btn.textContent || btn.value || '').toLowerCase();
    if (oc.indexOf('addGrilleLine') >= 0 || txt.indexOf('ajouter') >= 0) {
      btn.style.display = '';  // assure la visibilité
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        notify('__addGridRow__', oc);
      });
    }
  });
})();
''');
  }

  // ── Met en évidence les erreurs de validation ──────────────────────────────
  //
  // Ajoute/retire la classe CSS 'error' (bordure rouge) sur les champs
  // ayant des erreurs de validation. Appelé via didUpdateWidget quand
  // validationErrors change.
  void _injectValidationErrors() {
    final jsonErrors = json.encode(widget.validationErrors);
    // ⚠️  JAVASCRIPT ci-dessous (jusqu'à ''') — ne pas utiliser syntaxe Dart ici.
    _controller.runJavaScript('''
(function() {
  document.querySelectorAll('.error').forEach(function(el) {
    el.classList.remove('error');
  });
  var errors = $jsonErrors;
  for (var name in errors) {
    var els = document.querySelectorAll('[name="' + name + '"]');
    els.forEach(function(el) { el.classList.add('error'); });
  }
})();
''');
  }

  // ── Bouton natif "Ajouter une ligne" pour les formulaires grille ───────────
  //
  // Bouton Flutter natif affiché sous le WebView pour les formulaires de type grille.
  // Avantage : fonctionne même si les boutons JavaScript du serveur sont
  // masqués ou inopérants dans le WebView (styles Cordova masqués).
  //
  // Au clic :
  //   1. Notifie le parent (onAddGridRow)
  //   2. Incrémente le compteur de lignes (pour mettre à jour le label)
  //   3. Envoie au WebView le JS d'ajout de ligne :
  //      a) Tente d'abord la fonction addGrilleLine() du serveur si elle existe
  //      b) Sinon, fallback : clone la dernière ligne de données du tableau,
  //         remplace l'index de ligne dans les attributs NAME/ID,
  //         vide les valeurs et câble les nouveaux champs au pont FieldChanged
  //
  // Le fallback de clonage identifie l'index de ligne depuis les attributs id
  // des TR (ex. id='ligne-paire_14_0' → index 14) plutôt que depuis les noms
  // de champs (certains formulaires ont des numéros de colonnes dans les noms
  // de champs, ex. CODE_TYPE_DISCIPLINE_FORM_1_0 où '1' est la colonne, pas la ligne).
  Widget _buildAddRowButton() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
      child: ElevatedButton.icon(
        onPressed: () {
          // Notifie le parent pour qu'il puisse suivre les lignes supplémentaires
          widget.onAddGridRow?.call('native_add');
          setState(() => _gridRowCount++);
          // ⚠️  JAVASCRIPT ci-dessous (jusqu'à r''') — ne pas utiliser syntaxe Dart ici.
          // Demande au WebView d'appeler addGrilleLine si la fonction JS existe
          _controller.runJavaScript(r'''
(function() {
  if (typeof addGrilleLine === 'function') {
    addGrilleLine();
    return;
  }
  // ── Fallback : trouve la dernière ligne DATA (contenant des inputs) et la clone.
  // Les tableaux grille ont un en-tête complexe (plusieurs <tr> pour les entêtes de colonnes)
  // puis des lignes de données. On doit trouver le dernier <tr> contenant réellement
  // un <input>, <select> ou <textarea> — pas une ligne d'en-tête ou d'espacement.
  var tables = document.querySelectorAll('table');
  tables.forEach(function(tbl) {
    var allRows = tbl.querySelectorAll('tr');
    // Trouve les lignes qui contiennent au moins un input/select/textarea
    var dataRows = [];
    for (var i = 0; i < allRows.length; i++) {
      if (allRows[i].querySelectorAll('input, select, textarea').length > 0) {
        dataRows.push(allRows[i]);
      }
    }
    if (dataRows.length === 0) return;

    // La dernière ligne de données est notre template
    var templateRow = dataRows[dataRows.length - 1];
    var newRow = templateRow.cloneNode(true);

    // Détermine le nouvel index de ligne.
    // Stratégie : lit l'index de ligne depuis les attributs id des TR sur toutes les lignes de données.
    //   ex. id='ligne-paire_14_0' → index de ligne 14 ; prochaine ligne = 15.
    // On n'utilise PAS les noms de champs car certains formulaires ont des numéros
    // de colonnes dans les noms (ex. CODE_TYPE_DISCIPLINE_FORM_1_0 où '1' est la colonne,
    // pas la ligne — cela donnerait un maxIdx incorrect).
    var newIdx = dataRows.length; // fallback par défaut
    var maxRowIdx = -1;
    for (var ri = 0; ri < dataRows.length; ri++) {
      var rowId = dataRows[ri].getAttribute('id') || '';
      var mRow = rowId.match(/[_-](\d+)[_-]\d+$/) || rowId.match(/[_-](\d+)$/);
      if (mRow) {
        var n = parseInt(mRow[1], 10);
        if (n > maxRowIdx) maxRowIdx = n;
      }
    }
    if (maxRowIdx >= 0) { newIdx = maxRowIdx + 1; }

    // Met à jour TOUS les inputs de la nouvelle ligne :
    // Remplace UNIQUEMENT le segment d'index de ligne dans NAME et ID, garde le suffixe d'option.
    //
    // On remplace l'occurrence SPÉCIFIQUE de _{maxRowIdx} (ou _{maxRowIdx}_N)
    // plutôt qu'un pattern générique /_{digits}(_\d+)?$/, car certains noms de champs
    // intègrent des numéros de colonnes avant l'index de ligne :
    //   CODE_TYPE_DISCIPLINE_FORM_1_14  → remplace _14 (ligne), rien d'autre
    //   CODE_TYPE_SEXE_14_1             → remplace _14 (ligne), garde _1 (option)
    //   MATRICULE_14                    → remplace _14
    // Un regex spécifique évite de matcher le mauvais segment numérique.
    var oldRowPat = (maxRowIdx >= 0)
        ? new RegExp('_' + maxRowIdx + '(_\\d+)?$')
        : /_(\d+)(_\d+)?$/;
    newRow.querySelectorAll('input, select, textarea').forEach(function(el) {
      if (el.name) {
        el.name = el.name.replace(oldRowPat, function(m, optIdx) {
          return '_' + newIdx + (optIdx || '');
        });
      }
      if (el.id) {
        el.id = el.id.replace(oldRowPat, function(m, optIdx) {
          return '_' + newIdx + (optIdx || '');
        });
      }
      // Vide la valeur
      if (el.type === 'radio' || el.type === 'checkbox') {
        el.checked = false;
      } else {
        el.value = '';
      }
    });

    // Insère la nouvelle ligne juste après la ligne template
    templateRow.parentNode.insertBefore(newRow, templateRow.nextSibling);

    // Câble les nouveaux champs au pont FieldChanged
    newRow.querySelectorAll('input, select, textarea').forEach(function(el) {
      var name = el.name;
      if (!name) return;
      function notify(n, v) {
        FieldChanged.postMessage(JSON.stringify({name: n, value: v}));
      }
      if (el.type === 'radio') {
        el.addEventListener('change', function() { if (el.checked) notify(name, el.value); });
      } else if (el.type === 'checkbox') {
        el.addEventListener('change', function() { notify(name, el.checked ? '1' : '0'); });
      } else {
        el.addEventListener('input',  function() { notify(name, el.value); });
        el.addEventListener('change', function() { notify(name, el.value); });
      }
    });
  });
})();
''');
        },
        icon: const Icon(Icons.add, size: 18),
        label: Text('Ajouter une ligne ($_gridRowCount actuellement)'),
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFF1565C0),
          foregroundColor: Colors.white,
          minimumSize: const Size(double.infinity, 44),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    // Affiche un indicateur de chargement bref pendant que le WebView rend la première page.
    // Remplace le blanc/gris qui apparaît avant onPageFinished.
    // Le Container blanc assure l'absence de flash gris même avant que
    // setBackgroundColor prenne effet dans le moteur WebView.
    final webView = WebViewWidget(
      controller: _controller,
    );

    final body = _isRendering
        ? Container(
            color: Colors.white,
            child: Stack(
              children: [
                webView,
                // Indicateur de chargement centré pendant le rendu
                const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      CircularProgressIndicator(strokeWidth: 2),
                      SizedBox(height: 8),
                      Text('Chargement du formulaire…',
                          style: TextStyle(fontSize: 12, color: Colors.grey)),
                    ],
                  ),
                ),
              ],
            ),
          )
        : webView;

    // Pour les formulaires grille : WebView expansible + bouton "Ajouter une ligne"
    if (_isGridForm) {
      return Column(
        children: [
          Expanded(child: body),
          _buildAddRowButton(),
        ],
      );
    }
    // Pour les formulaires simples : WebView seul
    return body;
  }
}
