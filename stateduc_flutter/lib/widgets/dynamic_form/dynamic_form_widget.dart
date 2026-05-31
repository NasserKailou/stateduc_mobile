import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../../models/question.dart';

/// DynamicFormWidget — renders the server HTML form inside a WebView.
///
/// Architecture:
///   1. The raw HTML is passed in (cached in SQLite from server download).
///   2. A WebView loads it via a Base64 data-URI (UTF-8) so accented chars
///      always render correctly, regardless of device locale.
///   3. A JavaScript bridge ('FieldChanged') posts field-change events back
///      to Flutter whenever an input/select/textarea changes.
///   4. On init, existing [data] values are injected via JS so the form
///      shows previously saved values.
///   5. Validation errors are shown via JS by adding a red border to fields.
///   6. A native "+ Ajouter une ligne" FAB is overlaid for grid-type forms
///      so users can add rows even when the server JS buttons are unavailable.
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
  bool _isRendering = false;  // true while WebView is loading initial HTML

  // True when the HTML looks like a grille (grid) form.
  bool get _isGridForm => _detectGridForm(widget.html);

  // Number of grid rows currently rendered (for FAB label).
  int _gridRowCount = 0;

  @override
  void initState() {
    super.initState();
    _gridRowCount = _countGridRows(widget.html);
    _isRendering  = true;
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(Colors.white)   // ← prevents gray flash before page loads
      ..addJavaScriptChannel(
        'FieldChanged',
        onMessageReceived: (JavaScriptMessage msg) {
          // msg.message = JSON: {"name":"fieldName","value":"fieldValue"}
          try {
            final m = json.decode(msg.message) as Map<String, dynamic>;
            final name  = m['name']?.toString()  ?? '';
            final value = m['value']?.toString() ?? '';
            if (name == '__addGridRow__') {
              // Native add-row request from bridge or FAB
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
          // Use addPostFrameCallback so that Flutter finishes propagating
          // the latest widget.data (including pre-filled identification fields)
          // before we inject values into the WebView.
          // Without this, _injectData() might run with stale/empty widget.data
          // if the provider notifyListeners() hasn't rebuilt this widget yet.
          WidgetsBinding.instance.addPostFrameCallback((_) {
            _injectData();
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
    // Reload HTML when the form changes (question switch)
    if (old.html != widget.html) {
      _pageLoaded  = false;
      _isRendering = true;
      setState(() {
        _gridRowCount = _countGridRows(widget.html);
      });
      _controller.loadRequest(_buildHtmlUri(widget.html));
      return;
    }
    // Re-inject data when it changes externally (e.g. reload from server)
    if (_pageLoaded &&
        (old.data != widget.data ||
         old.validationErrors != widget.validationErrors)) {
      _injectData();
      _injectValidationErrors();
    }
  }

  // ── Convert HTML to a Base64 data-URI so the WebView always uses UTF-8 ──────
  // loadHtmlString() does not reliably honour the <meta charset="UTF-8"> tag on
  // Android, causing accented characters (é, è, à …) to display as Mojibake
  // (Ã©, etc.).  Encoding the bytes as Base64 and loading via a data: URI
  // forces the engine to decode the content as UTF-8.
  Uri _buildHtmlUri(String formHtml) {
    final processed  = _preprocessHtml(formHtml);
    final bytes      = utf8.encode(_buildHtmlPage(processed));
    final base64Html = base64Encode(bytes);
    return Uri.parse('data:text/html;charset=utf-8;base64,$base64Html');
  }

  // ── Pre-process raw HTML from the server before rendering ─────────────────
  //
  // 1. ISO-8859-15 → UTF-8 Mojibake repair:
  //    Server HTML files are encoded in ISO-8859-15.  When sqflite stores /
  //    reads them as Dart Strings, each byte > 0x7F becomes the corresponding
  //    Unicode code point (Latin-1 interpretation), so the UTF-8 multi-byte
  //    sequences for French chars appear as pairs like U+00C3 U+00A9 ("Ã©").
  //    Fix: re-encode code units ≤ 0xFF as raw bytes and decode as UTF-8.
  //
  // 2. HTML entity unescape:
  //    Some form files double-encode HTML entities.  Common symptom:
  //    "&lt;b&gt;1.6 …&lt;/b&gt;" rendered literally instead of <b>…</b>.
  //    We unescape the five standard XML/HTML entities before render.
  //
  // 3. $NUMERO_LOCAL_N substitution:
  //    Grille template rows contain "$NUMERO_LOCAL_0", "$NUMERO_LOCAL_1" etc.
  //    Replace with 1-based display numbers.
  //
  // 4. PHP $VAR placeholder substitution in VALUE= attributes:
  //    All HTML form files are PHP templates served by the server after
  //    variable substitution.  The mobile app receives the RAW template with
  //    unresolved $VAR placeholders because it caches the static HTML, not
  //    the rendered output.
  //
  //    Two patterns appear:
  //    a) Quoted text inputs:  VALUE="$NOM_ETABLISSEMENT_0"
  //       → Strip to VALUE="" so the input starts empty; _injectData() fills it.
  //    b) Unquoted radio/select options: VALUE=$CODE_TYPE_SEXE_0_1
  //       → The last numeric segment IS the actual option value (e.g. 1, 2, 12).
  //       → Replace with VALUE=1 so _injectData()'s el.checked=(el.value===val)
  //         comparison works correctly.
  String _preprocessHtml(String html) {
    // ── 1. Mojibake repair ─────────────────────────────────────────────────
    // Strategy: always attempt repair if mojibake patterns are detected.
    // Accept the decoded result even if a small number of \uFFFD replacement
    // chars appear (< 5% of total length) — these come from lone continuation
    // bytes like U+00C2 followed by a plain ASCII space (non-breaking space
    // sequences). Rejecting the whole repair because of a handful of \uFFFD
    // leaves ALL accented characters garbled, which is worse.
    if (_looksLikeMojibake(html)) {
      try {
        // Treat each code unit as a raw Latin-1 byte (code units ≤ 0xFF)
        final latin1Bytes = <int>[
          for (final c in html.codeUnits) if (c <= 0xFF) c,
        ];
        final decoded = utf8.decode(latin1Bytes, allowMalformed: true);
        // Count replacement chars — accept if fewer than 5% of input length
        final replacements = '\uFFFD'.allMatches(decoded).length;
        final threshold    = (latin1Bytes.length * 0.05).ceil();
        if (replacements <= threshold) {
          html = decoded;
          debugPrint('[DynamicForm] Mojibake repaired '
              '(${latin1Bytes.length} bytes, $replacements replacement chars)');
        } else {
          debugPrint('[DynamicForm] Mojibake repair rejected '
              '($replacements replacements > threshold $threshold)');
        }
      } catch (_) {
        // Keep original if repair fails
      }
    }

    // ── 2. HTML entity unescape ────────────────────────────────────────────
    // Run AFTER mojibake repair so entities in repaired text are also caught.
    // Run TWICE to handle double-encoded entities like &amp;lt;b&amp;gt;
    // (server sometimes double-encodes: &amp;lt; → first pass: &lt; → second: <)
    for (int pass = 0; pass < 2; pass++) {
      html = html
          .replaceAll('&lt;',   '<')
          .replaceAll('&gt;',   '>')
          .replaceAll('&amp;',  '&')
          .replaceAll('&quot;', '"')
          .replaceAll('&#39;',  "'")
          .replaceAll('&nbsp;', '\u00A0')
          .replaceAll('&apos;', "'");
    }

    // ── 3. $NUMERO_LOCAL_N → row number (1-based) ─────────────────────────
    html = html.replaceAllMapped(
      RegExp(r'\$NUMERO_LOCAL_(\d+)'),
      (m) {
        final n = int.tryParse(m.group(1) ?? '0') ?? 0;
        return (n + 1).toString();
      },
    );

    // ── 4a. Quoted VALUE="$VAR" → VALUE="" ────────────────────────────────
    // Matches: value="$ANY_CAPS_VAR_0" (case-insensitive attribute name)
    // NOTE: Dart's RegExp does NOT support inline flags like (?i).
    //       Use caseSensitive: false parameter instead.
    html = html.replaceAllMapped(
      RegExp(r'(value=)"(\$[A-Z_][A-Z_0-9]*)"', caseSensitive: false),
      (m) => '${m.group(1)!}""',
    );

    // ── 4b. Unquoted VALUE=$VAR → VALUE=<last-numeric-segment> ────────────
    // Matches: VALUE=$CODE_TYPE_SEXE_0_1  → VALUE=1
    //          VALUE=$CODE_DIPLOME_0_12   → VALUE=12
    // The last underscore-separated segment of the var name is the option value.
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

  // Returns true if the string likely contains ISO-8859-1 bytes mis-read as
  // individual Unicode code points (Mojibake).
  //
  // ISO-8859-15 → UTF-8 two-byte sequences start with 0xC2 or 0xC3.
  //   0xC3 (U+00C3) + 0x80–0xBF → Latin chars: é è à ô î â ë ï û ù ç ü Ô Î …
  //   0xC2 (U+00C2) + 0x80–0xBF → special chars: ° nbsp © ® « » …
  //     Common: 0xC2 0xA0 = non-breaking space (shows as 'Â ' in mojibake)
  //             0xC2 0xB0 = degree sign (shows as 'Â°')
  //
  // Also catches 0xE2 (U+00E2) start byte for 3-byte UTF-8 sequences:
  //   0xE2 + 0x80 + 0x99 = U+2019 right single quotation mark (apostrophe)
  //   shows as 'â€™' in mojibake (e.g. "l'établissement" → "lâ€™établissement")
  bool _looksLikeMojibake(String s) {
    // Quick check: common French mojibake two-char patterns
    if (s.contains('Ã©') || s.contains('Ã¨') || s.contains('Ã ') ||
        s.contains('Ã´') || s.contains('Ã®') || s.contains('Ã¢') ||
        s.contains('Ã«') || s.contains('Ã¯') || s.contains('Ã»') ||
        s.contains('Ã¹') || s.contains('Ã§') || s.contains('Ã¼') ||
        s.contains('Ãˆ') || s.contains('Ã‰') || s.contains('Ã€') ||
        s.contains('Â°')  || s.contains('Â ')  ||  // degree + nbsp
        s.contains('Â«')  || s.contains('Â»')  ||  // guillemets
        s.contains('â€™') || s.contains('â€œ') ||  // smart quotes
        s.contains('Nâ')  || s.contains('nÂ°')) {  // N° patterns
      return true;
    }
    // Thorough check: scan for UTF-8 lead byte followed by continuation byte
    for (int i = 0; i < s.length - 1; i++) {
      final c = s.codeUnitAt(i);
      if (c == 0xC2 || c == 0xC3) {
        final next = s.codeUnitAt(i + 1);
        if (next >= 0x80 && next <= 0xBF) return true;
      }
      // 3-byte UTF-8 sequence start (0xE2 = â in Latin-1)
      if (c == 0xE2 && i + 2 < s.length) {
        final n1 = s.codeUnitAt(i + 1);
        final n2 = s.codeUnitAt(i + 2);
        if (n1 >= 0x80 && n1 <= 0xBF && n2 >= 0x80 && n2 <= 0xBF) return true;
      }
    }
    return false;
  }

  // ── Detect whether this is a grid (grille) type form ──────────────────────
  // Grid forms have multiple row fields identified by any of these patterns:
  //   • $NUMERO_LOCAL_N  — explicit row-number placeholder (locaux forms)
  //   • addGrilleLine    — JS function that adds a new row
  //   • NUMERO_LOCAL     — partial match (same class of forms)
  //   • MiseEvidenceLigneFrame — JS used in all multi-row grille tables
  //   • NAME='FIELD_N_V'  — double-indexed field name (row index + option value)
  bool _detectGridForm(String html) {
    return html.contains(r'$NUMERO_LOCAL') ||
           html.contains('addGrilleLine') ||
           html.contains('NUMERO_LOCAL') ||
           html.contains('MiseEvidenceLigneFrame') ||
           RegExp(r"NAME='[A-Z_]+_\d+_\d+'").hasMatch(html);
  }

  // Count grid rows already in the HTML (for grille forms).
  // Handles two patterns:
  //   1. $NUMERO_LOCAL_N  — explicit row number placeholder (locaux, etc.)
  //   2. ligne-paire_N_0 / ligne-impaire_N_0 — CSS row-id pattern
  //      (personnel, identification_local, etc.)
  int _countGridRows(String html) {
    int max = -1;

    // Pattern 1: $NUMERO_LOCAL_N
    for (final m in RegExp(r'\$NUMERO_LOCAL_(\d+)').allMatches(html)) {
      final n = int.tryParse(m.group(1) ?? '') ?? 0;
      if (n > max) max = n;
    }
    if (max >= 0) return max + 1;

    // Pattern 2: id='ligne-paire_N_0' or id='ligne-impaire_N_0'
    for (final m in RegExp(r"ligne-(?:paire|impaire)_(\d+)_0").allMatches(html)) {
      final n = int.tryParse(m.group(1) ?? '') ?? 0;
      if (n > max) max = n;
    }
    return max >= 0 ? max + 1 : 0;
  }

  // ── Build the full HTML page loaded into the WebView ───────────────────────
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
  /* Horizontal scroll for wide grid tables */
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
  /* Allow text wrapping in header cells */
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
  /* Hide Cordova-specific elements that don't work in WebView */
  .ui-loader, [data-role=navbar], [data-role=footer],
  input[id^=btn_save], input[name^=btn_save],
  input[id^=btn_save_and], input[name^=btn_save_and] { display: none !important; }
  /* Total fields styling */
  input.total_, input[name^=total_] {
    background: #e8f0fe;
    font-weight: bold;
    color: #1a237e;
    text-align: right;
  }
  label { font-weight: 500; }
  /* Grid table overflow scrolling — wrap all tables in a scrollable container */
  .table-questionnaire { overflow-x: auto; display: block; }
  /* Add-row button in grid forms */
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
// Wrap all tables in a scrollable div after load (for wide grille tables)
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

  // ── Inject saved field values into the form ────────────────────────────────
  // NOTE: do NOT guard on widget.data.isEmpty — identification fields are
  // pre-filled in _formData BEFORE the page loads, and we must inject them
  // even when no previously-saved data exists.
  void _injectData() {
    if (!_pageLoaded) return;
    final jsonData = json.encode(widget.data);
    _controller.runJavaScript('''
(function() {
  var data = $jsonData;
  for (var name in data) {
    var val = data[name];
    // Try attribute selector (case-insensitive in HTML5 for standard attrs)
    var els = document.querySelectorAll('[name="' + name + '"]');
    // Fallback: iterate all inputs/selects if selector returned nothing
    // (handles edge cases with non-standard attribute casing)
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
        el.checked = (el.value === val);
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

  // ── Wire all form fields to post changes back via FieldChanged channel ─────
  void _injectBridge() {
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

  // Wire server-side "Ajouter" buttons (addGrilleLine) if present
  document.querySelectorAll('input[type=button], button').forEach(function(btn) {
    var oc = btn.getAttribute('onclick') || '';
    var txt = (btn.textContent || btn.value || '').toLowerCase();
    if (oc.indexOf('addGrilleLine') >= 0 || txt.indexOf('ajouter') >= 0) {
      btn.style.display = '';  // ensure visible
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        notify('__addGridRow__', oc);
      });
    }
  });
})();
''');
  }

  // ── Highlight validation errors ────────────────────────────────────────────
  void _injectValidationErrors() {
    final jsonErrors = json.encode(widget.validationErrors);
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

  // ── Native "+ Ajouter une ligne" button for grid forms ─────────────────────
  Widget _buildAddRowButton() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
      child: ElevatedButton.icon(
        onPressed: () {
          // Notify parent to add a new grid row
          widget.onAddGridRow?.call('native_add');
          setState(() => _gridRowCount++);
          // Also tell the WebView to call addGrilleLine if JS function exists
          _controller.runJavaScript(r'''
(function() {
  if (typeof addGrilleLine === 'function') {
    addGrilleLine();
    return;
  }
  // ── Fallback: find the last DATA row (a row that contains inputs) and clone it.
  // Grille tables have a complex header (multiple <tr> for column headers) and
  // then data rows. We must find the last <tr> that actually contains an
  // <input>, <select> or <textarea> — not a header or spacer row.
  var tables = document.querySelectorAll('table');
  tables.forEach(function(tbl) {
    var allRows = tbl.querySelectorAll('tr');
    // Find rows that have at least one input/select/textarea
    var dataRows = [];
    for (var i = 0; i < allRows.length; i++) {
      if (allRows[i].querySelectorAll('input, select, textarea').length > 0) {
        dataRows.push(allRows[i]);
      }
    }
    if (dataRows.length === 0) return;

    // The last data row is our template
    var templateRow = dataRows[dataRows.length - 1];
    var newRow = templateRow.cloneNode(true);

    // Determine the new row index.
    // Strategy: read the row index from TR id attributes across all data rows.
    //   e.g. id='ligne-paire_14_0' → row index 14; next row = 15.
    // We do NOT use field names because some forms embed column numbers in
    // field names (e.g. CODE_TYPE_DISCIPLINE_FORM_1_0 where '1' is the column,
    // not the row — this would give a wrong maxIdx).
    var newIdx = dataRows.length; // default fallback
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

    // Update ALL inputs in the new row:
    // Replace ONLY the row-index segment in NAME and ID, keep option suffix.
    //
    // We replace the SPECIFIC occurrence of _{maxRowIdx} (or _{maxRowIdx}_N)
    // rather than any /_(\d+)(_\d+)?$/ pattern, because some field names embed
    // column numbers before the row index:
    //   CODE_TYPE_DISCIPLINE_FORM_1_14  → replace _14 (row), keep nothing
    //   CODE_TYPE_SEXE_14_1             → replace _14 (row), keep _1 (option)
    //   MATRICULE_14                    → replace _14
    // Using a specific regex avoids matching the wrong numeric segment.
    // Build a regex that matches _{maxRowIdx} or _{maxRowIdx}_{optSuffix} at end of name.
    // Fallback: when maxRowIdx<0 (no TR ids found), match generic _{digits} pattern.
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
      // Clear value
      if (el.type === 'radio' || el.type === 'checkbox') {
        el.checked = false;
      } else {
        el.value = '';
      }
    });

    // Insert the new row right after the template row
    templateRow.parentNode.insertBefore(newRow, templateRow.nextSibling);

    // Wire the new fields to the FieldChanged bridge
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
    // Show a brief loading indicator while the WebView renders the first page.
    // This replaces the gray blank that appears before onPageFinished fires.
    // The white Container ensures no gray flash even before setBackgroundColor
    // takes effect in the WebView engine.
    final webView = WebViewWidget(
      controller: _controller,
    );

    final body = _isRendering
        ? Container(
            color: Colors.white,
            child: Stack(
              children: [
                webView,
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

    if (_isGridForm) {
      return Column(
        children: [
          Expanded(child: body),
          _buildAddRowButton(),
        ],
      );
    }
    return body;
  }
}
