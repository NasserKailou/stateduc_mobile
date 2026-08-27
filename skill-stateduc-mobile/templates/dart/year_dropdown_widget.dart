// year_dropdown_widget.dart — Sélecteur d'année ExpansionTile (accordion)
//
// USAGE: Copier/adapter dans settings_screen.dart (section année scolaire)
//
// TAGS: AK-YEAR-MULTI
//
// PROBLÈME RÉSOLU:
//   Flat list montrait toutes les années en même temps.
//   Solution: ExpansionTile accordion — l'année active en header,
//   les autres années cachées dans le body (expand on tap).
//
// DÉPENDANCES: flutter/material.dart, provider

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

// ─────────────────────────────────────────────────────────────────────────────
// MODÈLE SchoolYear
// ─────────────────────────────────────────────────────────────────────────────

/// Représente une année scolaire.
class SchoolYear {
  final String code;     // "2024" ou "2024-2025"
  final String libelle;  // "Année scolaire 2024-2025"
  final bool   active;   // true si c'est l'année active serveur

  const SchoolYear({
    required this.code,
    required this.libelle,
    this.active = false,
  });

  factory SchoolYear.fromJson(Map<String, dynamic> json) {
    // Normaliser les clés (serveur peut retourner en majuscules ADODB)
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
// WIDGET — YearDropdownSection
// ─────────────────────────────────────────────────────────────────────────────

/// Section "Année scolaire" de l'écran Paramètres.
///
/// Affiche l'année active en header ExpansionTile.
/// Les autres années sont listées dans le body (dépliable).
///
/// Exemple d'usage dans settings_screen.dart:
/// ```dart
/// YearDropdownSection(
///   years:    _years,
///   active:   _activeYear,
///   onSelect: (year) => _onYearSelected(auth, year),
/// )
/// ```
class YearDropdownSection extends StatelessWidget {
  final List<SchoolYear>         years;
  final SchoolYear?              active;
  final void Function(SchoolYear) onSelect;

  const YearDropdownSection({
    Key? key,
    required this.years,
    required this.active,
    required this.onSelect,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final theme      = Theme.of(context);
    final otherYears = years.where((y) => y.code != active?.code).toList();

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ExpansionTile(
        // ── Header — année active ──────────────────────────────────────────
        leading: const Icon(Icons.calendar_today),
        title: Text(
          active?.libelle ?? 'Aucune année en session',
          style: theme.textTheme.bodyLarge?.copyWith(
            fontWeight: FontWeight.w600,
          ),
        ),
        subtitle: Text(
          otherYears.isEmpty
              ? 'Seule année disponible'
              : 'Année en session — appuyer pour changer',
          style: theme.textTheme.bodySmall?.copyWith(
            color: theme.colorScheme.primary,
          ),
        ),

        // ── Body — autres années (dépliable) ──────────────────────────────
        children: otherYears.isEmpty
            ? [
                const Padding(
                  padding: EdgeInsets.all(16),
                  child: Text(
                    'Aucune autre année disponible',
                    style: TextStyle(fontStyle: FontStyle.italic),
                  ),
                ),
              ]
            : otherYears
                .map((year) => _YearListItem(
                      year:     year,
                      onSelect: onSelect,
                    ))
                .toList(),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// WIDGET INTERNE — _YearListItem
// ─────────────────────────────────────────────────────────────────────────────

class _YearListItem extends StatelessWidget {
  final SchoolYear               year;
  final void Function(SchoolYear) onSelect;

  const _YearListItem({
    Key? key,
    required this.year,
    required this.onSelect,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 24),
      leading: const Icon(Icons.calendar_month_outlined),
      title: Text(year.libelle),
      subtitle: Text('Code: ${year.code}'),
      trailing: const Icon(Icons.chevron_right),
      onTap: () => onSelect(year),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// INTÉGRATION dans settings_screen.dart
// ─────────────────────────────────────────────────────────────────────────────

/*
// Dans _SettingsScreenState.build():

// 1. Récupérer les données depuis le provider
final auth   = context.watch<AuthProvider>();
final years  = auth.years;
final active = auth.activeYear;

// 2. Afficher le widget
YearDropdownSection(
  years:    years,
  active:   active,
  onSelect: (selectedYear) => _onYearSelected(auth, selectedYear),
)

// 3. Handler de sélection (avec dialog de confirmation)
Future<void> _onYearSelected(AuthProvider auth, SchoolYear year) async {
  // Voir year_confirm_dialog.dart pour le dialog de confirmation
  final confirmed = await showYearConfirmDialog(context, year);
  if (confirmed == true) {
    await auth.changeActiveYear(year);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Année changée: ${year.libelle}')),
      );
    }
  }
}
*/
