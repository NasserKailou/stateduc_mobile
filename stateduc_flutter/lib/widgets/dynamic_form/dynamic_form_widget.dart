import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../../models/question.dart';

/// DynamicFormWidget — renders the server HTML form inside a WebView.
///
/// Architecture:
///   1. The raw HTML is passed in (cached in SQLite from server download).
///   2. A WebView loads it via loadHtmlString() so all CSS/tables render
///      exactly as in the original Cordova app.
///   3. A JavaScript bridge ('FieldChanged') posts field-change events back
///      to Flutter whenever an input/select/textarea changes.
///   4. On init, existing [data] values are injected via JS so the form
///      shows previously saved values.
///   5. Validation errors are shown via JS by adding a red border to fields.
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

  @override
  void initState() {
    super.initState();
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
            if (name.isNotEmpty) widget.onFieldChanged(name, value);
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
  // 1. ISO-8859-15 → UTF-8 repair:
  //    Server HTML files are encoded in ISO-8859-15 (French characters like é, è, à).
  //    When SQLite stores these as TEXT and Dart reads them back as a String,
  //    the bytes may have been mis-interpreted as Latin-1, producing Mojibake
  //    (e.g. "UtilisÃ©e" instead of "Utilisée").
  //    Detection: if the string contains the UTF-8 replacement sequences that
  //    look like double-encoded Latin-1 (Ã©, Ã , Ã¨ …), re-encode as ISO-8859-1
  //    bytes then decode as UTF-8.
  // 2. $NUMERO_LOCAL_N substitution:
  //    The server's grille HTML files contain template variables "$NUMERO_LOCAL_0",
  //    "$NUMERO_LOCAL_1" etc. These should display as row numbers (1, 2, 3 …).
  //    Replace them here before rendering.
  String _preprocessHtml(String html) {
    // 1. Detect and fix ISO-8859-15 double-encoding mojibake.
    //    Signature: "Ã©" = U+00C3 U+00A9 = UTF-8 bytes of U+00E9 (é) mis-read as Latin-1.
    if (_looksLikeMojibake(html)) {
      try {
        // Re-encode as ISO-8859-1 bytes then decode as UTF-8
        final latin1Bytes = <int>[
          for (final c in html.codeUnits) if (c <= 0xFF) c,
        ];
        final decoded = utf8.decode(latin1Bytes, allowMalformed: true);
        if (!decoded.contains('\uFFFD')) {
          html = decoded;
        }
      } catch (_) {
        // Keep original if repair fails
      }
    }

    // 2. Replace $NUMERO_LOCAL_N with display row number (N+1, 1-based).
    //    Server HTML grille templates contain $NUMERO_LOCAL_0, $NUMERO_LOCAL_1 etc.
    html = html.replaceAllMapped(
      RegExp(r'\$NUMERO_LOCAL_(\d+)'),
      (m) {
        final n = int.tryParse(m.group(1) ?? '0') ?? 0;
        return (n + 1).toString();
      },
    );

    return html;
  }

  // Returns true if the string likely contains ISO-8859-1 bytes mis-read as UTF-8.
  bool _looksLikeMojibake(String s) {
    for (int i = 0; i < s.length - 1; i++) {
      if (s.codeUnitAt(i) == 0xC3) {
        final next = s.codeUnitAt(i + 1);
        if (next >= 0x80 && next <= 0xBF) return true;
      }
    }
    return false;
  }

  // ── Build the full HTML page loaded into the WebView ───────────────────────
  String _buildHtmlPage(String formHtml) {
    return '''<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
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
  /* Hide elements that are purely decorative/JS-driven in Cordova */
  .ui-loader, .ui-page-active > [data-role=header]:first-child,
  [data-role=navbar], [data-role=footer] { display: none !important; }
  /* Total fields */
  input.total_, input[name^=total_] {
    background: #e8f0fe;
    font-weight: bold;
    color: #1a237e;
    text-align: right;
  }
  label { font-weight: 500; }
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

  // addGrilleLine buttons
  document.querySelectorAll('input[type=button], button').forEach(function(btn) {
    var onclick = btn.getAttribute('onclick') || btn.textContent || '';
    if (onclick.indexOf('addGrilleLine') >= 0 ||
        btn.textContent.toLowerCase().indexOf('ajouter') >= 0) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        FieldChanged.postMessage(JSON.stringify({name:'__addGridRow__',value:btn.getAttribute('onclick') || ''}));
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
  // Clear previous errors
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

  @override
  Widget build(BuildContext context) {
    return WebViewWidget(controller: _controller);
  }
}
