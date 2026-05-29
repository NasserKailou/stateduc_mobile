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

  // True when the HTML looks like a grille (grid) form.
  bool get _isGridForm => _detectGridForm(widget.html);

  // Number of grid rows currently rendered (for FAB label).
  int _gridRowCount = 0;

  @override
  void initState() {
    super.initState();
    _gridRowCount = _countGridRows(widget.html);
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
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
          _injectData();
          _injectBridge();
        },
      ))
      ..loadRequest(_buildHtmlUri(widget.html));
  }

  @override
  void didUpdateWidget(DynamicFormWidget old) {
    super.didUpdateWidget(old);
    // Reload HTML when the form changes (question switch)
    if (old.html != widget.html) {
      _pageLoaded = false;
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
  String _preprocessHtml(String html) {
    // ── 1. Mojibake repair ─────────────────────────────────────────────────
    if (_looksLikeMojibake(html)) {
      try {
        // Treat each code unit as a raw Latin-1 byte (code units ≤ 0xFF)
        final latin1Bytes = <int>[
          for (final c in html.codeUnits) if (c <= 0xFF) c,
        ];
        final decoded = utf8.decode(latin1Bytes, allowMalformed: true);
        // Only adopt the decoded version if it doesn't contain replacement chars
        if (!decoded.contains('\uFFFD')) {
          html = decoded;
          debugPrint('[DynamicForm] Mojibake repaired (${latin1Bytes.length} bytes)');
        }
      } catch (_) {
        // Keep original if repair fails
      }
    }

    // ── 2. HTML entity unescape ────────────────────────────────────────────
    // Run AFTER mojibake repair so entities in repaired text are also caught.
    html = html
        .replaceAll('&lt;',   '<')
        .replaceAll('&gt;',   '>')
        .replaceAll('&amp;',  '&')
        .replaceAll('&quot;', '"')
        .replaceAll('&#39;',  "'")
        .replaceAll('&nbsp;', '\u00A0');

    // ── 3. $NUMERO_LOCAL_N → row number (1-based) ─────────────────────────
    html = html.replaceAllMapped(
      RegExp(r'\$NUMERO_LOCAL_(\d+)'),
      (m) {
        final n = int.tryParse(m.group(1) ?? '0') ?? 0;
        return (n + 1).toString();
      },
    );

    return html;
  }

  // Returns true if the string likely contains ISO-8859-1 bytes mis-read as
  // individual Unicode code points (Mojibake).
  // Signature: U+00C3 followed by U+0080–U+00BF = UTF-8 two-byte sequence.
  bool _looksLikeMojibake(String s) {
    // Quick check: common French Mojibake patterns
    if (s.contains('Ã©') || s.contains('Ã¨') || s.contains('Ã ') ||
        s.contains('Ã´') || s.contains('Ã®') || s.contains('Ã¢') ||
        s.contains('Ã«') || s.contains('Ã¯') || s.contains('Ã»') ||
        s.contains('Ã¹') || s.contains('Ã§') || s.contains('Ã¼') ||
        s.contains('Â°')  || s.contains('Nâ')) {
      return true;
    }
    // Thorough check: scan for UTF-8 continuation byte pairs
    for (int i = 0; i < s.length - 1; i++) {
      if (s.codeUnitAt(i) == 0xC3) {
        final next = s.codeUnitAt(i + 1);
        if (next >= 0x80 && next <= 0xBF) return true;
      }
    }
    return false;
  }

  // ── Detect whether this is a grid (grille) type form ──────────────────────
  // Grid forms have multiple row fields with patterns like FIELD_N_col or
  // contain $NUMERO_LOCAL or have addGrilleLine JS calls.
  bool _detectGridForm(String html) {
    return html.contains(r'$NUMERO_LOCAL') ||
           html.contains('addGrilleLine') ||
           html.contains('NUMERO_LOCAL') ||
           RegExp(r'NAME=\'[A-Z_]+_\d+_\d+\'').hasMatch(html);
  }

  // Count grid rows already in the HTML (for grille forms).
  int _countGridRows(String html) {
    final matches = RegExp(r'\$NUMERO_LOCAL_(\d+)').allMatches(html);
    if (matches.isEmpty) return 0;
    int max = 0;
    for (final m in matches) {
      final n = int.tryParse(m.group(1) ?? '0') ?? 0;
      if (n > max) max = n;
    }
    return max + 1; // 0-indexed → count
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
  table { border-collapse: collapse; width: 100%; }
  td, th {
    border: 1px solid #ccc;
    padding: 4px 6px;
    vertical-align: middle;
  }
  th { background: #dce6f1; font-weight: bold; font-size: 12px; }
  input[type=text], input[type=number], textarea, select {
    width: 100%;
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
  /* Grid table overflow scrolling */
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
</head>
<body>
$formHtml
</body>
</html>''';
  }

  // ── Inject saved field values into the form ────────────────────────────────
  void _injectData() {
    if (widget.data.isEmpty) return;
    final jsonData = json.encode(widget.data);
    _controller.runJavaScript('''
(function() {
  var data = $jsonData;
  for (var name in data) {
    var val = data[name];
    var els = document.querySelectorAll('[name="' + name + '"]');
    els.forEach(function(el) {
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
          _controller.runJavaScript('''
(function() {
  if (typeof addGrilleLine === 'function') {
    addGrilleLine();
  } else {
    // Fallback: clone the last row in any grille table and clear its inputs
    var tables = document.querySelectorAll('table');
    tables.forEach(function(tbl) {
      var rows = tbl.querySelectorAll('tbody tr, tr');
      if (rows.length > 1) {
        var lastRow = rows[rows.length - 1];
        var newRow = lastRow.cloneNode(true);
        var newIdx = rows.length;
        // Update field names: replace last numeric segment
        newRow.querySelectorAll('input, select, textarea').forEach(function(el) {
          if (el.name) {
            el.name  = el.name.replace(/(\\d+)(?=[^\\d]*\$)/, newIdx.toString());
            el.id    = el.id   ? el.id.replace(/(\\d+)(?=[^\\d]*\$)/, newIdx.toString()) : '';
            el.value = '';
            if (el.type === 'radio' || el.type === 'checkbox') el.checked = false;
          }
        });
        // Update row-number display cells
        newRow.querySelectorAll('td:first-child').forEach(function(td) {
          if (td.textContent.trim().match(/^\\d+\$/)) td.textContent = (newIdx).toString();
        });
        lastRow.parentNode.insertBefore(newRow, lastRow.nextSibling);
      }
    });
  }
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
    if (_isGridForm) {
      return Column(
        children: [
          Expanded(child: WebViewWidget(controller: _controller)),
          _buildAddRowButton(),
        ],
      );
    }
    return WebViewWidget(controller: _controller);
  }
}
