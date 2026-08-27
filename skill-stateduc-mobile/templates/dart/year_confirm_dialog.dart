// year_confirm_dialog.dart — Dialog confirmation changement d'année
//
// USAGE: Copier/adapter dans stateduc_flutter/lib/widgets/year_confirm_dialog.dart
//
// TAGS: AK-YEAR-MULTI
//
// CONTEXTE:
//   Changer l'année active est une action avec des conséquences importantes :
//   - Les données non envoyées sont liées à l'ancienne année
//   - Le serveur utilisera la nouvelle année pour tous les prochains envois
//   - La liste des campagnes disponibles peut changer
//
//   Ce dialog informe l'utilisateur et lui demande une confirmation explicite.

import 'package:flutter/material.dart';

// ─────────────────────────────────────────────────────────────────────────────
// FONCTION showYearConfirmDialog()
// ─────────────────────────────────────────────────────────────────────────────

/// Affiche un dialog de confirmation avant de changer d'année active.
///
/// Retourne :
/// - `true`  → l'utilisateur a confirmé le changement
/// - `false` → l'utilisateur a annulé
/// - `null`  → dialog fermé autrement (tap hors du dialog)
///
/// [context]      BuildContext courant (doit être valide)
/// [currentYear]  Année actuellement active (peut être null)
/// [newYear]      Nouvelle année à activer
/// [pendingCount] Nombre de formulaires non envoyés (avertissement si > 0)
Future<bool?> showYearConfirmDialog({
  required BuildContext context,
  required String?      currentYearLabel,
  required String       newYearCode,
  required String       newYearLabel,
  int pendingCount = 0,
}) {
  return showDialog<bool>(
    context: context,
    barrierDismissible: true,
    builder: (ctx) => _YearConfirmDialog(
      currentYearLabel: currentYearLabel,
      newYearCode:      newYearCode,
      newYearLabel:     newYearLabel,
      pendingCount:     pendingCount,
    ),
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// WIDGET DIALOG
// ─────────────────────────────────────────────────────────────────────────────

class _YearConfirmDialog extends StatelessWidget {
  final String? currentYearLabel;
  final String  newYearCode;
  final String  newYearLabel;
  final int     pendingCount;

  const _YearConfirmDialog({
    required this.currentYearLabel,
    required this.newYearCode,
    required this.newYearLabel,
    required this.pendingCount,
  });

  @override
  Widget build(BuildContext context) {
    final theme  = Theme.of(context);
    final hasPending = pendingCount > 0;

    return AlertDialog(
      icon: Icon(
        hasPending ? Icons.warning_amber_rounded : Icons.swap_horiz,
        color: hasPending
            ? theme.colorScheme.error
            : theme.colorScheme.primary,
        size: 40,
      ),
      title: const Text('Changer d\'année scolaire'),

      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Résumé du changement
          _buildChangeRow(
            context,
            label:    'Année actuelle',
            value:    currentYearLabel ?? 'Aucune',
            isSource: true,
          ),
          const SizedBox(height: 8),
          _buildChangeRow(
            context,
            label: 'Nouvelle année',
            value: newYearLabel,
            isSource: false,
          ),
          const SizedBox(height: 16),

          // Avertissement données en attente
          if (hasPending) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: theme.colorScheme.errorContainer,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  Icon(
                    Icons.info_outline,
                    color: theme.colorScheme.onErrorContainer,
                    size: 20,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      '$pendingCount formulaire${pendingCount > 1 ? 's' : ''} '
                      'non envoyé${pendingCount > 1 ? 's' : ''} pour l\'année actuelle.\n'
                      'Envoyez-les avant de changer d\'année.',
                      style: theme.textTheme.bodySmall?.copyWith(
                        color: theme.colorScheme.onErrorContainer,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
          ],

          // Message d'information général
          Text(
            hasPending
                ? 'Vous pouvez quand même continuer, mais les données '
                  'non envoyées seront associées à l\'ancienne année.'
                : 'Cette action changera l\'année de collecte active. '
                  'Les prochains formulaires envoyés seront associés '
                  'à l\'année « $newYearLabel ».',
            style: theme.textTheme.bodySmall?.copyWith(
              color: theme.colorScheme.onSurfaceVariant,
            ),
          ),
        ],
      ),

      actions: [
        // Bouton Annuler
        TextButton(
          onPressed: () => Navigator.of(context).pop(false),
          child: const Text('Annuler'),
        ),

        // Bouton Confirmer
        FilledButton(
          style: hasPending
              ? FilledButton.styleFrom(
                  backgroundColor: theme.colorScheme.error,
                  foregroundColor: theme.colorScheme.onError,
                )
              : null,
          onPressed: () => Navigator.of(context).pop(true),
          child: const Text('Confirmer le changement'),
        ),
      ],
    );
  }

  Widget _buildChangeRow(
    BuildContext context, {
    required String label,
    required String value,
    required bool   isSource,
  }) {
    final theme = Theme.of(context);
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(
          isSource ? Icons.arrow_circle_right_outlined : Icons.check_circle,
          size: 18,
          color: isSource
              ? theme.colorScheme.onSurfaceVariant
              : theme.colorScheme.primary,
        ),
        const SizedBox(width: 8),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: theme.textTheme.labelSmall?.copyWith(
                  color: theme.colorScheme.onSurfaceVariant,
                ),
              ),
              Text(
                value,
                style: theme.textTheme.bodyMedium?.copyWith(
                  fontWeight: isSource ? null : FontWeight.bold,
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
// UTILISATION
// ─────────────────────────────────────────────────────────────────────────────

/*
// Dans settings_screen.dart ou year_dropdown_widget.dart:

final confirmed = await showYearConfirmDialog(
  context:          context,
  currentYearLabel: auth.activeYear?.libelle,
  newYearCode:      newYear.code,
  newYearLabel:     newYear.libelle,
  pendingCount:     dataEntry.pendingCount,  // 0 si pas de données en attente
);

if (confirmed == true && context.mounted) {
  await context.read<AuthProvider>().changeActiveYear(newYear);

  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(
      content: Text('Année changée : ${newYear.libelle}'),
      backgroundColor: Theme.of(context).colorScheme.primary,
    ),
  );
}
*/
