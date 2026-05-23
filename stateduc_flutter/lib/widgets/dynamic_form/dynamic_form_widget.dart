import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import '../../models/question.dart';

/// DynamicFormWidget — renders the HTML form received from the server and
/// wires interactive fields (input, select, textarea, radio, checkbox) to
/// Flutter form widgets.
///
/// This is the most complex widget in the app. It replaces:
///   page_etab.js  → initHtml(), addGrilleLine(), createNextTr()
///   calc_total.js → total calculation (via callbacks)
///   script.js     → ctrl_saisie, ctrl_saisie_text, ctrl_saisie_dimension
///
/// Architecture:
///   1. The raw HTML is passed in (cached in SQLite from server).
///   2. flutter_html renders the static structure (tables, labels, etc.).
///   3. We post-process the HTML to identify form fields and replace them
///      with native Flutter widgets using CustomRender callbacks.
///   4. All field changes are reported up via onFieldChanged.
///   5. Dynamic grid rows (grilleContainer) use addGrilleLine.
///
/// Field naming convention (same as original JS):
///   Numeric input:  name="val_{row}_{col}" or name="{fieldName}"
///   Select:         name="{fieldName}"
///   Total display:  id="total_r{row}", id="total_c{col}", id="total_all"
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
  // Tracks text controllers to avoid re-creating on every rebuild
  final Map<String, TextEditingController> _controllers = {};

  TextEditingController _controllerFor(String fieldName) {
    if (!_controllers.containsKey(fieldName)) {
      _controllers[fieldName] = TextEditingController(
        text: widget.data[fieldName] ?? '',
      );
    }
    return _controllers[fieldName]!;
  }

  @override
  void didUpdateWidget(DynamicFormWidget old) {
    super.didUpdateWidget(old);
    // Sync controller text when data is reloaded externally
    for (final entry in widget.data.entries) {
      final ctrl = _controllers[entry.key];
      if (ctrl != null && ctrl.text != entry.value) {
        ctrl.text = entry.value;
        ctrl.selection =
            TextSelection.collapsed(offset: entry.value.length);
      }
    }
  }

  @override
  void dispose() {
    for (final c in _controllers.values) {
      c.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(8),
      child: Html(
        data: widget.html,
        style: {
          'body': Style(margin: Margins.zero, padding: HtmlPaddings.zero),
          'table': Style(
            border: Border.all(
                color: Theme.of(context).colorScheme.outlineVariant,
                width: 0.5),
          ),
          'th': Style(
            backgroundColor:
                Theme.of(context).colorScheme.primaryContainer,
            padding: HtmlPaddings.symmetric(horizontal: 6, vertical: 4),
            fontSize: FontSize(12),
            fontWeight: FontWeight.bold,
          ),
          'td': Style(
            padding: HtmlPaddings.symmetric(horizontal: 4, vertical: 2),
            fontSize: FontSize(13),
          ),
          'label': Style(
            fontSize: FontSize(13),
            fontWeight: FontWeight.w500,
          ),
          '.total': Style(
            fontWeight: FontWeight.bold,
            color: Theme.of(context).colorScheme.primary,
          ),
        },
        extensions: [
          // ── Text / number inputs ────────────────────────────────────────
          TagExtension(
            tagsToExtend: {'input'},
            builder: (context, attributes, child) {
              final type = attributes['type']?.toLowerCase() ?? 'text';
              final name = attributes['name'] ?? '';
              if (name.isEmpty) return const SizedBox.shrink();

              if (type == 'text' || type == 'number') {
                return _buildTextInput(name, type, attributes);
              }
              if (type == 'radio') {
                return _buildRadio(name, attributes);
              }
              if (type == 'checkbox') {
                return _buildCheckbox(name, attributes);
              }
              if (type == 'button' || type == 'submit') {
                final onclick = attributes['onclick'] ?? '';
                return _buildButton(
                    attributes['value'] ?? 'OK', onclick);
              }
              return const SizedBox.shrink();
            },
          ),
          // ── Textarea ────────────────────────────────────────────────────
          TagExtension(
            tagsToExtend: {'textarea'},
            builder: (context, attributes, child) {
              final name = attributes['name'] ?? '';
              if (name.isEmpty) return const SizedBox.shrink();
              return _buildTextArea(name);
            },
          ),
          // ── Select ──────────────────────────────────────────────────────
          TagExtension(
            tagsToExtend: {'select'},
            builder: (context, attributes, child) {
              final name = attributes['name'] ?? '';
              if (name.isEmpty) return const SizedBox.shrink();
              // Options must be parsed from child HTML
              return _buildSelectPlaceholder(name);
            },
          ),
        ],
      ),
    );
  }

  // ─── Text/number input ─────────────────────────────────────────────────────
  Widget _buildTextInput(
      String name, String type, Map<String, String> attrs) {
    final ctrl = _controllerFor(name);
    final hasError = widget.validationErrors.containsKey(name);
    final isReadOnly =
        attrs['readonly'] != null || attrs['disabled'] != null;
    final isTotal = name.startsWith('total_') ||
        (attrs['class'] ?? '').contains('total');

    if (isTotal) {
      // Display-only total field
      return Container(
        width: _inputWidth(attrs),
        alignment: Alignment.centerRight,
        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
        decoration: BoxDecoration(
          border: Border.all(color: Colors.grey.shade300),
          color: Colors.blue.shade50,
        ),
        child: ValueListenableBuilder<TextEditingValue>(
          valueListenable: ctrl,
          builder: (_, val, __) => Text(
            val.text.isEmpty ? '0' : val.text,
            style: const TextStyle(
                fontWeight: FontWeight.bold,
                color: Color(0xFF1565C0)),
          ),
        ),
      );
    }

    return SizedBox(
      width: _inputWidth(attrs),
      child: TextField(
        controller: ctrl,
        readOnly: isReadOnly,
        keyboardType: type == 'number'
            ? const TextInputType.numberWithOptions(decimal: true)
            : TextInputType.text,
        textAlign:
            type == 'number' ? TextAlign.right : TextAlign.left,
        style: const TextStyle(fontSize: 13),
        decoration: InputDecoration(
          isDense: true,
          contentPadding: const EdgeInsets.symmetric(
              horizontal: 6, vertical: 6),
          border: const OutlineInputBorder(
            borderRadius: BorderRadius.all(Radius.circular(4)),
          ),
          errorText: hasError
              ? widget.validationErrors[name]
              : null,
          errorStyle: const TextStyle(fontSize: 10),
          fillColor: isReadOnly ? Colors.grey.shade100 : null,
          filled: isReadOnly,
        ),
        onChanged: (v) => widget.onFieldChanged(name, v),
      ),
    );
  }

  // ─── Radio button ───────────────────────────────────────────────────────────
  Widget _buildRadio(String name, Map<String, String> attrs) {
    final value = attrs['value'] ?? '';
    final currentValue = widget.data[name] ?? '';
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Radio<String>(
          value: value,
          groupValue: currentValue,
          onChanged: (v) => widget.onFieldChanged(name, v ?? ''),
          visualDensity: VisualDensity.compact,
        ),
        if (attrs['label'] != null)
          Text(attrs['label']!, style: const TextStyle(fontSize: 13)),
      ],
    );
  }

  // ─── Checkbox ────────────────────────────────────────────────────────────────
  Widget _buildCheckbox(String name, Map<String, String> attrs) {
    final currentValue = widget.data[name] ?? '';
    return Checkbox(
      value: currentValue == '1' || currentValue == 'true',
      onChanged: (v) =>
          widget.onFieldChanged(name, (v == true) ? '1' : '0'),
      visualDensity: VisualDensity.compact,
    );
  }

  // ─── Textarea ────────────────────────────────────────────────────────────────
  Widget _buildTextArea(String name) {
    final ctrl = _controllerFor(name);
    return TextField(
      controller: ctrl,
      maxLines: 3,
      style: const TextStyle(fontSize: 13),
      decoration: InputDecoration(
        isDense: true,
        contentPadding: const EdgeInsets.all(8),
        border: const OutlineInputBorder(),
        errorText: widget.validationErrors[name],
      ),
      onChanged: (v) => widget.onFieldChanged(name, v),
    );
  }

  // ─── Select placeholder ───────────────────────────────────────────────────
  Widget _buildSelectPlaceholder(String name) {
    // flutter_html's TagExtension doesn't easily give us <option> children.
    // We render a simple dropdown with the current value.
    // In production, options would be pre-parsed from the HTML.
    final currentValue = widget.data[name] ?? '';
    return DropdownButton<String>(
      value: currentValue.isNotEmpty ? currentValue : null,
      hint: const Text('Sélectionner…',
          style: TextStyle(fontSize: 13)),
      isDense: true,
      items: currentValue.isNotEmpty
          ? [
              DropdownMenuItem(
                  value: currentValue, child: Text(currentValue))
            ]
          : [],
      onChanged: (v) => widget.onFieldChanged(name, v ?? ''),
    );
  }

  // ─── Button ───────────────────────────────────────────────────────────────
  Widget _buildButton(String label, String onclick) {
    // "Ajouter une ligne" button for dynamic grids
    if (onclick.contains('addGrilleLine') ||
        label.toLowerCase().contains('ajouter')) {
      // Extract table id from onclick if possible
      final match =
          RegExp(r"addGrilleLine\(['\"](\w+)['\"]").firstMatch(onclick);
      final tableId = match?.group(1) ?? 'grille';
      return ElevatedButton.icon(
        onPressed: () => widget.onAddGridRow?.call(tableId),
        icon: const Icon(Icons.add, size: 16),
        label: Text(label, style: const TextStyle(fontSize: 13)),
        style: ElevatedButton.styleFrom(
          visualDensity: VisualDensity.compact,
          padding: const EdgeInsets.symmetric(
              horizontal: 10, vertical: 6),
        ),
      );
    }
    return ElevatedButton(
      onPressed: () {},
      style: ElevatedButton.styleFrom(
        visualDensity: VisualDensity.compact,
      ),
      child: Text(label, style: const TextStyle(fontSize: 13)),
    );
  }

  // ─── Helpers ──────────────────────────────────────────────────────────────
  double? _inputWidth(Map<String, String> attrs) {
    final size = int.tryParse(attrs['size'] ?? '');
    if (size != null) {
      // Approximate: 1 size unit ≈ 8px
      return (size * 8.0).clamp(40.0, 200.0);
    }
    return null; // expand naturally
  }
}
