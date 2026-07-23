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
    this.disabledFields = const {},
  });

  final String html;
  final Map<String, String> data;
  final Map<String, String> validationErrors;
  final List<ValidationRule> rules;
  final void Function(String fieldName, String value) onFieldChanged;
  final void Function(String tableId)? onAddGridRow;

  /// Ensemble des noms de champs à désactiver (Fix #5 — questions conditionnelles).
  /// Transmis par DataEntryProvider.disabledFields.
  final Set<String> disabledFields;

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
            _enablePinchZoom(); // Active le pinch-to-zoom (Android WebView ignore user-scalable=yes)
            // Applique l'état désactivé initial (Fix #5 — questions conditionnelles)
            if (widget.disabledFields.isNotEmpty) {
              _injectDisabledFields(widget.disabledFields);
            }
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
    // Réinjecte l'état disable/enable quand disabledFields change (Fix #5)
    if (_pageLoaded && old.disabledFields != widget.disabledFields) {
      _injectDisabledFields(widget.disabledFields);
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
  //     champs de total (fond bleu), masquage des éléments Cordova,
  //     freeze sticky 1ère ligne + 1ère colonne pour tableaux 2D
  //   - JavaScript : détection automatique des tableaux 2D (≥ 4 colonnes +
  //     ligne-titre + inputs) → freeze header/col ; wrapping normal pour les autres
  String _buildHtmlPage(String formHtml) {
    return '''<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.25, maximum-scale=5.0, user-scalable=yes">
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

  /* ── FIX SESSION 66 issue #7 — Anti-chevauchement champs de saisie ──────
   * ROOT CAUSE : les cellules td avec white-space:nowrap + input width:100%
   * se chevauchaient quand plusieurs colonnes tenaient dans un espace étroit.
   * SOLUTION :
   *   1. td : position:relative + overflow:visible → pas de clip
   *   2. input dans td : position:static, largeur fixe explicite (min-width)
   *   3. conteneur .div-table-questionnaire : scroll horizontal natif
   *   4. td/input : largeur MINIMUM garantie (60px ou 80px) pour éviter
   *      les chevauchements même en cas de `width:100%` sur petit écran
   * ─────────────────────────────────────────────────────────────────────── */

  /* Défilement horizontal pour les tableaux de grille larges */
  .div-table-questionnaire {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    display: block;
    width: 100%;
    /* Marges pour éviter que le scroll masque les bords */
    padding-bottom: 4px;
  }
  body > form, body > table {
    overflow-x: auto;
    display: block;
    -webkit-overflow-scrolling: touch;
  }

  table {
    border-collapse: collapse;
    /* NE PAS mettre min-width:100% ici — le wrapper défilant gère la largeur */
    table-layout: auto;   /* colonne adaptée au contenu, pas de chevauchement */
  }
  td, th {
    border: 1px solid #ccc;
    padding: 4px 6px;
    vertical-align: middle;
    /* FIX : nowrap sur td provoque chevauchement → normal pour les données */
    white-space: normal;
    /* Largeur minimum pour garantir la lisibilité même en scroll horizontal */
    min-width: 60px;
    word-break: break-word;    /* évite le débordement des longs textes */
  }
  th {
    background: #dce6f1;
    font-weight: bold;
    font-size: 12px;
    white-space: normal;   /* retour à la ligne dans les en-têtes */
    min-width: 60px;
  }
  /* Cellules de saisie numérique : largeur fixe réduite */
  td.num, th.num { min-width: 52px; max-width: 90px; }

  input[type=text], input[type=number], textarea, select {
    /* FIX : width:100% dans une td peut déborder → on utilise une largeur adaptative */
    width: 100%;
    min-width: 52px;
    max-width: 100%;
    padding: 4px;
    border: 1px solid #aaa;
    border-radius: 3px;
    font-size: 13px;
    background: #fff;
    color: #000;
    /* Empêche l'input de grossir sa cellule au-delà de son contenu */
    box-sizing: border-box;
  }
  /* Inputs courts pour saisie numérique (colonnes étroites) */
  input[type=number] {
    min-width: 48px;
    text-align: right;
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

  /* ── Responsivité : le corps s'adapte à la largeur de l'écran ── */
  html, body {
    max-width: 100%;
    overflow-x: hidden;   /* le scroll horizontal se fait au niveau des tableaux, pas du body */
    touch-action: pan-x pan-y pinch-zoom;
  }

  /* ── Smartphone (largeur < 480 px) ── */
  @media (max-width: 480px) {
    body { font-size: 11px; margin: 2px; }
    td, th { padding: 2px 3px; font-size: 11px; min-width: 44px; }
    th { min-width: 44px; }
    input[type=text], input[type=number], textarea, select {
      max-width: 100%;
      font-size: 11px;
      padding: 2px 3px;
      min-width: 44px;
    }
    input[type=number] { min-width: 40px; }
    input[type=radio], input[type=checkbox] {
      transform: scale(1.1);
      margin: 2px;
    }
  }

  /* ── Mobile standard (480 – 600 px) ── */
  @media (min-width: 481px) and (max-width: 600px) {
    body { font-size: 12px; }
    td, th { padding: 3px 4px; min-width: 52px; }
    input[type=text], input[type=number], textarea, select {
      max-width: 100%;
    }
  }

  /* ── Tablette (> 600 px) ── */
  @media (min-width: 601px) {
    body { font-size: 14px; margin: 8px; }
    td, th { padding: 6px 8px; font-size: 13px; min-width: 72px; }
    input[type=text], input[type=number], textarea, select {
      font-size: 14px;
      padding: 5px 6px;
    }
    input[type=radio], input[type=checkbox] {
      transform: scale(1.4);
      margin: 6px;
    }
    th { min-width: 80px; }
  }

  /* Empêche les images/éléments larges de déborder */
  img { max-width: 100%; height: auto; }

  /* ── FREEZE 1ère ligne + 1ère colonne — conteneur de scroll ─────────────
   * IMPORTANT : position:sticky est cassé dans Android WebView quand un
   * ancêtre a overflow:auto/hidden (ce qui est le cas ici : body>form,
   * html/body overflow-x:hidden, .div-table-questionnaire overflow-x:auto).
   * Solution : clone JS — on crée un élément flottant (position:absolute)
   * qui reproduit la ligne d'en-tête et la 1ère colonne, puis on synchronise
   * sa position au scroll via addEventListener('scroll').
   * ────────────────────────────────────────────────────────────────────── */

  /* Conteneur scroll bi-directionnel pour tableaux freeze */
  .frz-wrap {
    position: relative;          /* ancre pour les clones position:absolute */
    overflow: auto;
    -webkit-overflow-scrolling: touch;
    display: block;
    width: 100%;
    max-height: 70vh;
    border: 1px solid #b7c4d8;
    border-radius: 8px;
    background: #fff;
    margin: 8px 0 12px;
  }

  /* Tableau à l'intérieur du wrapper freeze */
  .frz-tbl {
    border-collapse: collapse;
    table-layout: auto;
    min-width: 100%;
  }
  /* Marge supérieure pour laisser la place au clone d'en-tête flottant.
     La hauteur réelle sera injectée par JS après mesure du tr.ligne-titre. */
  .frz-tbl.frz-has-header { margin-top: 0; }   /* JS ajuste via paddingTop */

  /* Clone flottant de la ligne d'en-tête (reproduit le tr.ligne-titre) */
  .frz-header-clone {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 10;
    overflow: hidden;
    background: #dce6f1;
    border-bottom: 2px solid #b7c4d8;
    pointer-events: none;          /* les inputs restent cliquables sous le clone */
    box-sizing: border-box;
    /* hauteur/largeur injectées par JS */
  }
  .frz-header-clone table {
    border-collapse: collapse;
    table-layout: fixed;           /* mêmes largeurs que le vrai tableau */
    background: #dce6f1;
    margin: 0; padding: 0;
  }
  .frz-header-clone td, .frz-header-clone th {
    background: #dce6f1 !important;
    font-weight: bold;
    font-size: 12px;
    padding: 4px 6px;
    border: 1px solid #b7c4d8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    box-sizing: border-box;
  }

  /* Clone flottant de la 1ère colonne */
  .frz-col-clone {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 9;
    overflow: hidden;
    background: #f5f7fb;
    border-right: 2px solid #b7c4d8;
    pointer-events: none;
    box-sizing: border-box;
  }
  .frz-col-clone table {
    border-collapse: collapse;
    table-layout: fixed;
    background: #f5f7fb;
    margin: 0; padding: 0;
  }
  .frz-col-clone td, .frz-col-clone th {
    background: #f5f7fb !important;
    font-size: 12px;
    padding: 4px 6px;
    border: 1px solid #cdd5e0;
    border-left: none;
    white-space: normal;
    word-break: break-word;
    overflow: hidden;
    box-sizing: border-box;
  }
  .frz-col-clone tr.frz-header-tr td,
  .frz-col-clone tr.frz-header-tr th {
    background: #dce6f1 !important;
    font-weight: bold;
    border-bottom: 2px solid #b7c4d8;
  }

  /* Indicateur de scroll */
  .frz-hint {
    display: block;
    padding: 4px 8px 6px;
    color: #5d6b82;
    font-size: 11px;
    text-align: center;
    background: linear-gradient(180deg, rgba(255,255,255,0), rgba(245,247,251,1));
    border-top: 1px solid #e8ecf4;
  }

</style>
<script>
// Wrap tables + correction positionnement + freeze header/col JS-clone (Android WebView safe)
// Exécuté après DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {

  // ── Détecte si un tableau est un tableau 2D éligible au freeze ───────────
  // Critères : ≥ 4 colonnes, ligne-titre ou thead, inputs dans les données
  function isFreezable2DTable(tbl) {
    if (tbl.classList.contains('mobile-card-table')) return false;
    var rows = tbl.querySelectorAll('tr');
    var maxCols = 0;
    for (var i = 0; i < Math.min(rows.length, 5); i++) {
      var cells = rows[i].querySelectorAll('td, th');
      var colCount = 0;
      cells.forEach(function(c) {
        colCount += (parseInt(c.getAttribute('colspan') || '1', 10));
      });
      if (colCount > maxCols) maxCols = colCount;
    }
    if (maxCols < 4) return false;
    if (!tbl.querySelector('tr.ligne-titre, thead')) return false;
    return tbl.querySelector(
      'tr:not(.ligne-titre) input, tr:not(.ligne-titre) select, tr:not(.ligne-titre) textarea'
    ) !== null;
  }

  // ── Applique le freeze JS-clone sur un tableau 2D ────────────────────────
  //
  // Technique :
  //   • Wrap le tableau dans .frz-wrap (scroll bi-directionnel, position:relative)
  //   • Crée .frz-header-clone : copie de la tr.ligne-titre, position:absolute top:0
  //     → suit scrollLeft pour rester aligné horizontalement
  //   • Crée .frz-col-clone : copie de la 1ère cellule de chaque ligne,
  //     position:absolute left:0 → suit scrollTop pour rester aligné verticalement
  //   • Un paddingTop sur le tableau = hauteur du clone d'en-tête
  //     pour que la 1ère ligne de données ne soit pas masquée
  //   • Un paddingLeft sur le tableau = largeur du clone de 1ère colonne
  //   • Synchronisation via 'scroll' et ResizeObserver (recalcul si taille change)
  function applyFreeze(tbl) {
    // 1. Wrapper
    var parent = tbl.parentElement;
    var existingWrapper = null;
    if (parent && (parent.classList.contains('table-mobile-scroll') ||
                   parent.classList.contains('div-table-questionnaire') ||
                   parent.classList.contains('frz-wrap'))) {
      if (parent.classList.contains('frz-wrap')) return; // déjà wrappé
      existingWrapper = parent;
    }
    var wrap = document.createElement('div');
    wrap.className = 'frz-wrap';
    if (existingWrapper) {
      existingWrapper.parentNode.insertBefore(wrap, existingWrapper);
      wrap.appendChild(tbl);
      if (existingWrapper.parentNode) existingWrapper.parentNode.removeChild(existingWrapper);
    } else {
      tbl.parentNode.insertBefore(wrap, tbl);
      wrap.appendChild(tbl);
    }
    tbl.classList.add('frz-tbl');

    // 2. Identifie la ligne d'en-tête et les lignes de données
    var allRows  = Array.prototype.slice.call(tbl.querySelectorAll('tr'));
    var hdrRows  = allRows.filter(function(r) {
      return r.classList.contains('ligne-titre') ||
             (r.parentElement && r.parentElement.tagName === 'THEAD');
    });
    var dataRows = allRows.filter(function(r) { return hdrRows.indexOf(r) === -1; });
    if (hdrRows.length === 0) return;

    // 3. Clone de la ligne d'en-tête ─────────────────────────────────────
    var hClone = document.createElement('div');
    hClone.className = 'frz-header-clone';
    var hTable = document.createElement('table');
    var hTbody = document.createElement('tbody');
    hdrRows.forEach(function(hdr) {
      var tr = document.createElement('tr');
      var cells = hdr.querySelectorAll('td, th');
      cells.forEach(function(cell) {
        var td = document.createElement('td');
        td.innerHTML = cell.innerHTML;
        if (cell.getAttribute('colspan')) td.setAttribute('colspan', cell.getAttribute('colspan'));
        tr.appendChild(td);
      });
      hTbody.appendChild(tr);
    });
    hTable.appendChild(hTbody);
    hClone.appendChild(hTable);
    wrap.appendChild(hClone);

    // 4. Clone de la 1ère colonne ─────────────────────────────────────────
    var cClone = document.createElement('div');
    cClone.className = 'frz-col-clone';
    var cTable = document.createElement('table');
    var cTbody = document.createElement('tbody');
    // En-têtes (1ère cellule de chaque ligne d'en-tête)
    hdrRows.forEach(function(hdr) {
      var tr = document.createElement('tr');
      tr.className = 'frz-header-tr';
      var cell = hdr.querySelector('td, th');
      if (cell) {
        var td = document.createElement('td');
        td.innerHTML = cell.innerHTML;
        tr.appendChild(td);
      }
      cTbody.appendChild(tr);
    });
    // Données (1ère cellule de chaque ligne de données)
    dataRows.forEach(function(row) {
      var tr = document.createElement('tr');
      var cell = row.querySelector('td, th');
      if (cell) {
        var td = document.createElement('td');
        td.innerHTML = cell.innerHTML;
        tr.appendChild(td);
      }
      cTbody.appendChild(tr);
    });
    cTable.appendChild(cTbody);
    cClone.appendChild(cTable);
    wrap.appendChild(cClone);

    // 5. Indicateur de scroll
    var hint = document.createElement('div');
    hint.className = 'frz-hint';
    hint.textContent = '\u21c4 d\u00e9filer horizontalement  \u2195 verticalement';
    wrap.appendChild(hint);

    // 6. Mesure + synchronisation ─────────────────────────────────────────
    // Appelé à l'init et à chaque scroll/resize
    function syncFreeze() {
      // Dimensions réelles du tableau
      var tblW = tbl.offsetWidth;

      // Hauteur de chaque ligne d'en-tête
      var hdrH = 0;
      hdrRows.forEach(function(r) { hdrH += r.offsetHeight; });

      // Largeur de la 1ère colonne (mesurée sur la 1ère ligne de données)
      var col0W = 0;
      if (dataRows.length > 0) {
        var firstCell = dataRows[0].querySelector('td, th');
        if (firstCell) col0W = firstCell.offsetWidth;
      }
      if (col0W === 0 && hdrRows.length > 0) {
        var firstHdrCell = hdrRows[0].querySelector('td, th');
        if (firstHdrCell) col0W = firstHdrCell.offsetWidth;
      }

      var scrollLeft = wrap.scrollLeft;
      var scrollTop  = wrap.scrollTop;

      // ── Clone en-tête : largeur totale du tableau, suit scrollLeft ──
      hClone.style.width  = tblW + 'px';
      hClone.style.height = hdrH + 'px';
      hClone.style.transform = 'translateX(' + (-scrollLeft) + 'px)';
      hTable.style.width  = tblW + 'px';

      // Aligne chaque cellule du clone avec la cellule réelle du tableau
      // (copie les largeurs calculées pour un alignement pixel-perfect)
      if (hdrRows.length > 0) {
        var realCells  = hdrRows[0].querySelectorAll('td, th');
        var cloneCells = hTbody.querySelectorAll('tr:first-child td');
        for (var i = 0; i < Math.min(realCells.length, cloneCells.length); i++) {
          cloneCells[i].style.width = realCells[i].offsetWidth + 'px';
          cloneCells[i].style.minWidth = realCells[i].offsetWidth + 'px';
        }
      }

      // ── Padding supérieur du tableau = hauteur de l'en-tête clone ──
      tbl.style.paddingTop = hdrH + 'px';
      // Masque les lignes d'en-tête originales (remplacées par le clone)
      hdrRows.forEach(function(r) { r.style.visibility = 'hidden'; r.style.height = '0px'; });
      // Restauration : on garde la tr mais on l'écrase visuellement
      // (visibility:hidden conserve l'espace, permettant au clone de s'aligner)

      // ── Clone 1ère colonne : hauteur totale du tableau, suit scrollTop ──
      var totalH = tbl.offsetHeight;
      cClone.style.height    = totalH + 'px';
      cClone.style.width     = col0W + 'px';
      cClone.style.transform = 'translateY(' + (-scrollTop) + 'px)';
      cTable.style.width     = col0W + 'px';

      // Aligne chaque cellule de la colonne clone avec la ligne réelle
      var cCloneTrs = cTbody.querySelectorAll('tr');
      // Ligne d'en-tête clones
      hdrRows.forEach(function(r, idx) {
        if (cCloneTrs[idx]) {
          cCloneTrs[idx].style.height = r.offsetHeight + 'px';
          var tc = cCloneTrs[idx].querySelector('td');
          if (tc) tc.style.height = r.offsetHeight + 'px';
        }
      });
      // Lignes de données clones
      var offset = hdrRows.length;
      dataRows.forEach(function(r, idx) {
        var ci = offset + idx;
        if (cCloneTrs[ci]) {
          cCloneTrs[ci].style.height = r.offsetHeight + 'px';
          var tc = cCloneTrs[ci].querySelector('td');
          if (tc) tc.style.height = r.offsetHeight + 'px';
        }
      });

      // ── Padding gauche du tableau = largeur de la 1ère colonne ──
      tbl.style.paddingLeft = col0W + 'px';
      // Masque la 1ère cellule de chaque ligne (remplacée par le clone)
      allRows.forEach(function(r) {
        var fc = r.querySelector('td, th');
        if (fc) { fc.style.visibility = 'hidden'; }
      });

      // ── Coin supérieur gauche : le clone de colonne recouvre l'en-tête ──
      // On réajuste le z-index pour que le coin soit correctement rendu
      cClone.style.zIndex = '9';
      hClone.style.zIndex = '10';
    }

    // Synchronisation au scroll
    wrap.addEventListener('scroll', syncFreeze);

    // Synchronisation initiale après layout
    // requestAnimationFrame garantit que le layout est calculé
    requestAnimationFrame(function() {
      requestAnimationFrame(function() {
        syncFreeze();
      });
    });

    // Re-sync si le tableau est redimensionné (ex. rotation écran)
    if (typeof ResizeObserver !== 'undefined') {
      var ro = new ResizeObserver(function() { syncFreeze(); });
      ro.observe(wrap);
    } else {
      // Fallback pour navigateurs sans ResizeObserver
      window.addEventListener('resize', syncFreeze);
    }
  }

  // ── 1. Traitement de tous les tableaux ───────────────────────────────────
  var allTables = Array.prototype.slice.call(document.querySelectorAll('table'));
  var freezeTables = [], normalTables = [];
  allTables.forEach(function(tbl) {
    if (isFreezable2DTable(tbl)) freezeTables.push(tbl);
    else normalTables.push(tbl);
  });
  freezeTables.forEach(function(tbl) { applyFreeze(tbl); });

  // Wrap normal (scroll horizontal) pour les autres tableaux non wrappés
  normalTables.forEach(function(tbl) {
    if (!tbl.parentElement) return;
    var par = tbl.parentElement;
    if (!par.classList.contains('div-table-questionnaire') &&
        !par.classList.contains('frz-wrap') &&
        !par.classList.contains('table-mobile-scroll')) {
      var wrapper = document.createElement('div');
      wrapper.className = 'div-table-questionnaire';
      tbl.parentNode.insertBefore(wrapper, tbl);
      wrapper.appendChild(tbl);
    }
  });

  // ── 2. FIX CHEVAUCHEMENT inputs ─────────────────────────────────────────
  document.querySelectorAll('td input[type=text], td input[type=number], td select, td textarea').forEach(function(el) {
    el.style.width = '100%';
    el.style.minWidth = '44px';
    el.style.boxSizing = 'border-box';
    if (el.hasAttribute('size')) el.removeAttribute('size');
  });

  // ── 3. table-layout:auto sur tous les tableaux ──────────────────────────
  document.querySelectorAll('table').forEach(function(tbl) {
    tbl.style.tableLayout = 'auto';
    tbl.style.borderCollapse = 'collapse';
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

  // ── Désactive/réactive les champs conditionnels (Fix #5) ──────────────────
  //
  // Injecte du JavaScript pour désactiver visuellement et fonctionnellement
  // les champs dont les noms sont dans [disabled].
  //
  // Comportement :
  //   • Les champs désactivés sont grisés (background #f0f0f0, couleur #aaa)
  //     et portent l'attribut HTML disabled (exclus du POST côté serveur).
  //   • La ligne TR parente est mise à 50% d'opacité pour signaler visuellement
  //     que la question entière est inactive.
  //   • Les champs non présents dans [disabled] sont ré-activés (disabled=false,
  //     styles restaurés) pour gérer le cas "l'utilisateur change d'avis".
  //
  // Correspondance des noms :
  //   Un champ "ELECTRICITE_0" dans [disabled] correspond aux inputs dont
  //   le NAME commence par "ELECTRICITE_0" (gère les variantes _1, _0 etc.).
  void _injectDisabledFields(Set<String> disabled) {
    if (!_pageLoaded) return;
    final jsonList = json.encode(disabled.toList());
    // ⚠️  JAVASCRIPT ci-dessous (jusqu'à ''') — ne pas utiliser syntaxe Dart ici.
    _controller.runJavaScript('''
(function() {
  var disabledFields = $jsonList;

  document.querySelectorAll('input, textarea, select').forEach(function(el) {
    if (!el.name) return;
    var elName = el.name;

    // Vérifie si ce champ doit être désactivé
    var shouldDisable = false;
    for (var i = 0; i < disabledFields.length; i++) {
      var baseField = disabledFields[i];
      // Correspond si le nom commence par le champ de base
      if (elName === baseField || elName.indexOf(baseField) === 0) {
        shouldDisable = true;
        break;
      }
    }

    if (shouldDisable) {
      el.disabled = true;
      el.style.background = '#f0f0f0';
      el.style.color = '#aaa';
      el.style.cursor = 'not-allowed';
      el.style.opacity = '0.6';
      // Réduit l'opacité de la ligne TR parente
      var tr = el.closest('tr') || el.closest('TR');
      if (tr) tr.style.opacity = '0.5';
    } else {
      el.disabled = false;
      el.style.background = '';
      el.style.color = '';
      el.style.cursor = '';
      el.style.opacity = '';
      // Restaure l'opacité du TR si aucun champ désactivé dans cette ligne
      var tr = el.closest('tr') || el.closest('TR');
      if (tr) {
        var hasDisabledInRow = false;
        var rowInputs = tr.querySelectorAll('input, textarea, select');
        for (var j = 0; j < rowInputs.length; j++) {
          if (rowInputs[j].disabled) { hasDisabledInRow = true; break; }
        }
        if (!hasDisabledInRow) tr.style.opacity = '';
      }
    }
  });
})();
''');
  }

  // ── Active le pinch-to-zoom dans le WebView (Android) ─────────────────────
  //
  // Android WebView ignore user-scalable=yes dans le viewport meta par défaut.
  // Cette méthode :
  //   1. Remplace le meta viewport par une version sans restriction de zoom.
  //   2. Active explicitement touch-action: pinch-zoom sur html/body.
  //
  // Appelée après chaque chargement de page (onPageFinished) pour s'assurer
  // que le meta est bien en place même après un rechargement du HTML.
  void _enablePinchZoom() {
    if (!_pageLoaded) return;
    // ⚠️  JAVASCRIPT ci-dessous — ne pas utiliser syntaxe Dart ici.
    _controller.runJavaScript(r'''
(function() {
  // 1. Remplace/crée le meta viewport pour autoriser le zoom utilisateur
  var meta = document.querySelector('meta[name="viewport"]');
  if (!meta) {
    meta = document.createElement('meta');
    meta.setAttribute('name', 'viewport');
    document.head.appendChild(meta);
  }
  meta.setAttribute('content',
    'width=device-width, initial-scale=1.0, minimum-scale=0.25, maximum-scale=10.0, user-scalable=yes');

  // 2. Supprime toute règle CSS qui bloquerait le zoom (touch-action: manipulation
  //    bloque le pinch-zoom sur Android ; touch-action: pan-x pan-y pinch-zoom l'autorise)
  document.documentElement.style.touchAction = 'pan-x pan-y pinch-zoom';
  document.body.style.touchAction = 'pan-x pan-y pinch-zoom';
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
    // Note : gestureRecognizers NON utilisé ici.
    // EagerGestureRecognizer bloque le rendu WebView (freeze "isn't responding").
    // ScaleGestureRecognizer seul crée des conflits de scroll.
    // Le pinch-to-zoom est activé via JavaScript dans _enablePinchZoom()
    // (meta viewport + touch-action CSS), ce qui est suffisant sur Android.
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
