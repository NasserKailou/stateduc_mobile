// year_confirm_dialog.dart — Dialog de confirmation de changement d'année
//
// USAGE: Copier/adapter dans settings_screen.dart
//
// TAGS: AK-YEAR-MULTI
//
// CONTEXTE:
//   Changer d'année en cours de saisie peut invalider les données non envoyées.
//   Ce dialog avertit l'utilisateur et demande confirmation avant tout changement.

import 'package:flutter/material.dart';

// ─────────────────────────────────────────────────────────────────────────────
// FONCTION PRINCIPALE — showYearConfirmDialog()
// ─────────────────────────────────────────────────────────────────────────────

/// Affiche un dialog de confirmation avant le changement d'année scolaire.
///
/// Retourne `true` si l'utilisateur confirme, `false` ou `null` s'il annule.
///
/// Exemple:
/// ```dart
/// final ok = await showYearConfirmDialog(context, selectedYear);
/// if (ok == true) { auth.changeActiveYear(selectedYear); }
/// ```
Future<bool?> showYearConfirmDialog(
  BuildContext context,
  SchoolYear targetYear, {
  SchoolYear? currentYear,
}) {
  return showDialog<bool>(
    context: context,
    barrierDismissible: false,  // forcer un choix explicite
    builder: (ctx) => _YearConfirmDialog(
      targetYear:  targetYear,
      currentYear: currentYear,
    ),
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// WIDGET DIALOG
// ─────────────────────────────────────────────────────────────────────────────

class _YearConfirmDialog extends StatelessWidget {
  final SchoolYear  targetYear;
  final SchoolYear? currentYear;

  const _YearConfirmDialog({
    Key? key,
    required this.targetYear,
    this.currentYear,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final theme   = Theme.of(context);
    final hasFrom = currentYear != null;

    return AlertDialog(
      // ── Icône ──────────────────────────────────────────────────────────
      icon: Icon(
        Icons.swap_horiz,
        color: theme.colorScheme.primary,
        size: 32,
      ),

      // ── Titre ──────────────────────────────────────────────────────────
      title: const Text('Changer d\'année scolaire'),

      // ── Contenu ────────────────────────────────────────────────────────
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Résumé du changement
          if (hasFrom) ...[
            _buildYearRow(
              context,
              label:  'Année actuelle',
              year:   currentYear!,
              active: true,
            ),
            const SizedBox(height: 8),
            const Row(
              children: [
                SizedBox(width: 8),
                Icon(Icons.arrow_downward, size: 16),
              ],
            ),
            const SizedBox(height: 8),
          ],
          _buildYearRow(
            context,
            label:  'Nouvelle année',
            year:   targetYear,
            active: false,
          ),

          const SizedBox(height: 16),
          const Divider(),
          const SizedBox(height: 8),

          // Avertissement
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.amber.shade50,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.amber.shade300),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(Icons.warning_amber, color: Colors.amber, size: 20),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Les données non encore envoyées au serveur '
                    'resteront associées à l\'année précédente.\n\n'
                    'Assurez-vous d\'envoyer vos saisies avant de changer d\'année.',
                    style: theme.textTheme.bodySmall,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),

      // ── Actions ────────────────────────────────────────────────────────
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(false),
          child: const Text('Annuler'),
        ),
        FilledButton(
          onPressed: () => Navigator.of(context).pop(true),
          child: const Text('Confirmer le changement'),
        ),
      ],

      actionsPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
    );
  }

  Widget _buildYearRow(
    BuildContext context, {
    required String     label,
    required SchoolYear year,
    required bool       active,
  }) {
    final theme = Theme.of(context);
    return Row(
      children: [
        Icon(
          active ? Icons.check_circle : Icons.radio_button_unchecked,
          color: active ? Colors.green : theme.colorScheme.primary,
          size: 18,
        ),
        const SizedBox(width: 8),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: theme.textTheme.bodySmall?.copyWith(
                  color: theme.colorScheme.outline,
                ),
              ),
              Text(
                year.libelle,
                style: theme.textTheme.bodyMedium?.copyWith(
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// USAGE COMPLET dans settings_screen.dart
// ─────────────────────────────────────────────────────────────────────────────

/*
// Import SchoolYear si défini dans un fichier séparé:
// import '../models/school_year.dart';

// Dans la classe _SettingsScreenState:

Future<void> _onYearSelected(AuthProvider auth, SchoolYear year) async {
  // Ne pas demander confirmation si c'est déjà l'année active
  if (year == auth.activeYear) return;

  final confirmed = await showYearConfirmDialog(
    context,
    year,
    currentYear: auth.activeYear,
  );

  if (confirmed == true && mounted) {
    await auth.changeActiveYear(year);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Année changée: ${year.libelle}'),
        backgroundColor: Colors.green,
        duration: const Duration(seconds: 2),
      ),
    );
  }
}
*/

// ─────────────────────────────────────────────────────────────────────────────
// STUB SchoolYear (à supprimer si SchoolYear est dans un fichier dédié)
// ─────────────────────────────────────────────────────────────────────────────

// Si SchoolYear n'est pas encore défini, utiliser cette version minimale:
// class SchoolYear {
//   final String code;
//   final String libelle;
//   const SchoolYear({required this.code, required this.libelle});
//   @override bool operator ==(Object o) => o is SchoolYear && o.code == code;
//   @override int get hashCode => code.hashCode;
// }
