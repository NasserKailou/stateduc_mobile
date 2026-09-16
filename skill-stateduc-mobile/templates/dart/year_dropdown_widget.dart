// year_dropdown_widget.dart — ExpansionTile accordion pour sélection d'année
//
// USAGE: Copier/adapter dans stateduc_flutter/lib/screens/settings/settings_screen.dart
//
// TAGS: AK-YEAR-MULTI
//
// PROBLÈME RÉSOLU:
//   La liste d'années était une liste plate → toutes les années visibles en même
//   temps → confusion sur l'année active. Remplacé par un accordion ExpansionTile
//   avec l'année active en entête et les autres années dans le corps.
//
// DÉPENDANCES: flutter/material.dart, provider

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
// import '../providers/auth_provider.dart';  // adapter selon votre structure

// ─────────────────────────────────────────────────────────────────────────────
// MODÈLE SchoolYear
// ─────────────────────────────────────────────────────────────────────────────

class SchoolYear {
  final String code;
  final String libelle;
  final bool   active;

  const SchoolYear({
    required this.code,
    required this.libelle,
    this.active = false,
  });

  factory SchoolYear.fromJson(Map<String, dynamic> json) {
    // Normaliser la casse des clés (ADODB retourne en majuscules)
    final normalised = {
      for (final e in json.entries) e.key.toLowerCase(): e.value,
    };
    return SchoolYear(
      code:    (normalised['code']    ?? '').toString(),
      libelle: (normalised['libelle'] ?? normalised['code'] ?? '').toString(),
      active:  normalised['active'] == 1 || normalised['active'] == true,
    );
  }

  @override
  bool operator ==(Object other) =>
      other is SchoolYear && other.code == code;

  @override
  int get hashCode => code.hashCode;

  @override
  String toString() => 'SchoolYear($code, active=$active)';
}

// ─────────────────────────────────────────────────────────────────────────────
// WIDGET — YearAccordionSelector
// ─────────────────────────────────────────────────────────────────────────────

/// Sélecteur d'année sous forme d'accordéon ExpansionTile.
///
/// - Entête : année active (ou "Aucune" si non définie)
/// - Corps : liste des autres années, cliquables pour changer
///
/// [years]          Liste complète des années disponibles
/// [activeYear]     Année actuellement sélectionnée
/// [onYearSelected] Callback appelé avec la nouvelle année sélectionnée
///                  AVANT d'afficher le dialog de confirmation — le widget
///                  ne confirme pas lui-même, laisse l'appelant décider.
class YearAccordionSelector extends StatelessWidget {
  final List<SchoolYear> years;
  final SchoolYear?       activeYear;
  final void Function(SchoolYear year) onYearSelected;

  const YearAccordionSelector({
    super.key,
    required this.years,
    required this.activeYear,
    required this.onYearSelected,
  });

  @override
  Widget build(BuildContext context) {
    final theme      = Theme.of(context);
    final otherYears = years.where((y) => y.code != activeYear?.code).toList();

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ExpansionTile(
        // ── Entête : année active ──────────────────────────────────────────
        leading: const Icon(Icons.calendar_today),
        title: Text(
          activeYear?.libelle ?? 'Aucune année en session',
          style: theme.textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.bold,
          ),
        ),
        subtitle: Text(
          otherYears.isEmpty
              ? 'Aucune autre année disponible'
              : 'Appuyer pour changer d\'année (${otherYears.length} disponible${otherYears.length > 1 ? 's' : ''})',
          style: theme.textTheme.bodySmall,
        ),
        // Indicateur visuel que c'est l'année active
        trailing: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (activeYear != null)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: theme.colorScheme.primaryContainer,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  'Active',
                  style: TextStyle(
                    fontSize: 11,
                    color: theme.colorScheme.onPrimaryContainer,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            const SizedBox(width: 4),
            const Icon(Icons.expand_more),
          ],
        ),

        // ── Corps : autres années ──────────────────────────────────────────
        children: otherYears.isEmpty
            ? [
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Text(
                    'Aucune autre année scolaire configurée sur le serveur.',
                    style: theme.textTheme.bodyMedium?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                ),
              ]
            : otherYears.map((year) => _buildYearItem(context, year)).toList(),
      ),
    );
  }

  Widget _buildYearItem(BuildContext context, SchoolYear year) {
    final theme = Theme.of(context);
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 2),
      leading: Icon(
        Icons.calendar_month_outlined,
        color: theme.colorScheme.secondary,
      ),
      title: Text(year.libelle),
      subtitle: Text(
        'Code : ${year.code}',
        style: theme.textTheme.bodySmall,
      ),
      trailing: const Icon(Icons.chevron_right),
      onTap: () => onYearSelected(year),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// UTILISATION DANS settings_screen.dart
// ─────────────────────────────────────────────────────────────────────────────

/*
// Dans SettingsScreen — build():

YearAccordionSelector(
  years:      auth.years,
  activeYear: auth.activeYear,
  onYearSelected: (year) async {
    // Afficher dialog de confirmation avant de changer
    final confirmed = await showYearConfirmDialog(context, year);
    if (confirmed == true && context.mounted) {
      await context.read<AuthProvider>().changeActiveYear(year);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Année changée : ${year.libelle}')),
      );
    }
  },
),
*/

// ─────────────────────────────────────────────────────────────────────────────
// WIDGET COMPACT — pour la Card Settings (utilisé dans settings_screen.dart)
// ─────────────────────────────────────────────────────────────────────────────

/// Version compacte intégrée directement dans une Card Settings.
/// Affiche le même accordion mais dans un contexte de liste de paramètres.
Widget buildYearSettingsSection({
  required BuildContext          context,
  required List<SchoolYear>      years,
  required SchoolYear?           activeYear,
  required Future<void> Function(SchoolYear) onYearChange,
}) {
  return Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      // Titre de section
      Padding(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
        child: Text(
          'ANNÉE SCOLAIRE',
          style: Theme.of(context).textTheme.labelSmall?.copyWith(
            color: Theme.of(context).colorScheme.primary,
            letterSpacing: 1.2,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),

      // Accordion
      YearAccordionSelector(
        years:      years,
        activeYear: activeYear,
        onYearSelected: (year) async {
          final confirmed = await _showYearChangeConfirmation(context, year);
          if (confirmed == true) {
            await onYearChange(year);
          }
        },
      ),
    ],
  );
}

/// Dialogue de confirmation interne — voir year_confirm_dialog.dart pour la
/// version complète avec plus d'options.
Future<bool?> _showYearChangeConfirmation(
    BuildContext context, SchoolYear newYear) {
  return showDialog<bool>(
    context: context,
    builder: (ctx) => AlertDialog(
      title: const Text('Changer d\'année ?'),
      content: Text(
        'Passer à l\'année "${newYear.libelle}" ?\n\n'
        'Les données non envoyées de l\'année courante resteront '
        'accessibles dans l\'historique.',
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(ctx).pop(false),
          child: const Text('Annuler'),
        ),
        FilledButton(
          onPressed: () => Navigator.of(ctx).pop(true),
          child: const Text('Confirmer'),
        ),
      ],
    ),
  );
}
