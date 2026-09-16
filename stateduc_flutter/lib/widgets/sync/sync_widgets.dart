import 'package:flutter/material.dart';
import '../../models/sync_status.dart';

/// AK-SYNC — helpers visuels partagés pour l'état de synchronisation (U05/U06).
///
/// Utilisés par : SchoolDataScreen (chips + ligne d'état), CampaignDetailScreen
/// (tuiles d'établissement + bannière), PendingFormsScreen.

/// Couleur associée à un état de synchronisation.
Color syncStatusColor(SyncStatus s) {
  switch (s) {
    case SyncStatus.sent:     return const Color(0xFF2E7D32); // vert
    case SyncStatus.pending:  return const Color(0xFFF9A825); // jaune/orangé
    case SyncStatus.failed:   return const Color(0xFFC62828); // rouge
    case SyncStatus.kosave:   return const Color(0xFFEF6C00); // orange
    case SyncStatus.rejected: return const Color(0xFFC62828); // rouge
    case SyncStatus.locked:   return const Color(0xFF6A1B9A); // violet
  }
}

/// Icône associée à un état de synchronisation.
IconData syncStatusIcon(SyncStatus s) {
  switch (s) {
    case SyncStatus.sent:     return Icons.cloud_done_rounded;
    case SyncStatus.pending:  return Icons.schedule_rounded;
    case SyncStatus.failed:   return Icons.cloud_off_rounded;
    case SyncStatus.kosave:   return Icons.report_problem_rounded;
    case SyncStatus.rejected: return Icons.block_rounded;
    case SyncStatus.locked:   return Icons.lock_rounded;
  }
}

/// Format court « jj/mm/aaaa hh:mm » (— si null).
String fmtSyncDate(DateTime? d) {
  if (d == null) return '—';
  String two(int n) => n < 10 ? '0$n' : '$n';
  return '${two(d.day)}/${two(d.month)}/${d.year} ${two(d.hour)}:${two(d.minute)}';
}

/// Libellé nominatif d'un résultat d'envoi. [withEtab] préfixe le nom de
/// l'établissement (envoi campagne) ; sinon seulement le thème (envoi école).
String describeOutcome(SendOutcome o, {bool withEtab = true}) {
  final e = (o.libEtab ?? '').isNotEmpty ? o.libEtab! : 'Étab. ${o.idEtab}';
  final q = (o.libQst ?? '').isNotEmpty ? o.libQst! : 'Thème ${o.idQst}';
  final f = (o.idFilter ?? '').isEmpty ? '' : ' [${o.idFilter}]';
  final m = (o.message ?? '').isEmpty ? '' : ' — ${o.message}';
  return withEtab ? '$e › $q$f : ${o.label}$m' : '$q$f : ${o.label}$m';
}

/// AK-SYNC U06 — Dialogue de résultat d'un envoi groupé.
///
/// Affiche : nombre de succès, formulaires ignorés (sans données), la liste
/// NOMINATIVE des échecs (établissement › thème : raison) et, s'il reste des
/// échecs ré-essayables (hors verrous admin), un bouton « Réessayer les
/// échecs » qui appelle [onRetry] puis ré-affiche le dialogue avec le nouveau
/// résultat (récursif jusqu'à fermeture).
Future<void> showBatchResultDialog(
  BuildContext context, {
  required List<SendOutcome> outcomes,
  String? providerError,
  bool withEtab = true,
  /// Libellé personnalisé par résultat (sinon [describeOutcome]).
  String Function(SendOutcome o)? describe,
  Future<List<SendOutcome>> Function()? onRetry,
}) async {
  String label(SendOutcome o) =>
      describe != null ? describe(o) : describeOutcome(o, withEtab: withEtab);
  final okCount   = outcomes.where((o) => o.isSuccess).length;
  final failures  = outcomes.where((o) => o.isFailure).toList();
  final skipped   = outcomes.where((o) => !o.isSuccess && !o.isFailure).length;
  final retryable = onRetry != null &&
      failures.any((o) => o.kind != SendOutcomeKind.locked);
  final warn      = (providerError != null && providerError.isNotEmpty) || failures.isNotEmpty;

  final action = await showDialog<String>(
    context: context,
    builder: (ctx) => AlertDialog(
      title: Row(children: [
        Icon(warn ? Icons.warning_amber_rounded : Icons.check_circle_outline,
            color: warn ? Colors.orange : Colors.green),
        const SizedBox(width: 8),
        const Text('Résultat de l\'envoi'),
      ]),
      content: SizedBox(
        width: double.maxFinite,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (outcomes.isEmpty)
              const Text('Aucune donnée locale à envoyer.'),
            if (outcomes.isNotEmpty)
              Text('✅  $okCount formulaire(s) envoyé(s) avec succès'),
            if (skipped > 0)
              Text('ℹ️  $skipped formulaire(s) ignoré(s) (aucune donnée locale)'),
            if (failures.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text('⚠️  ${failures.length} formulaire(s) non envoyé(s) :',
                  style: const TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 6),
              Flexible(
                child: ListView(
                  shrinkWrap: true,
                  children: failures.map((o) => Padding(
                    padding: const EdgeInsets.symmetric(vertical: 3),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(
                          o.kind == SendOutcomeKind.locked ? Icons.lock_outline : Icons.error_outline,
                          size: 16,
                          color: o.kind == SendOutcomeKind.locked ? Colors.deepPurple : Colors.red,
                        ),
                        const SizedBox(width: 6),
                        Expanded(
                          child: Text(label(o),
                              style: const TextStyle(fontSize: 12.5)),
                        ),
                      ],
                    ),
                  )).toList(),
                ),
              ),
            ],
            if (providerError != null && providerError.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(providerError,
                  style: TextStyle(color: Theme.of(ctx).colorScheme.error, fontSize: 13)),
            ],
          ],
        ),
      ),
      actions: [
        if (retryable)
          OutlinedButton.icon(
            onPressed: () => Navigator.pop(ctx, 'retry'),
            icon: const Icon(Icons.replay),
            label: const Text('Réessayer les échecs'),
          ),
        ElevatedButton(
          onPressed: () => Navigator.pop(ctx, 'close'),
          child: const Text('Fermer'),
        ),
      ],
    ),
  );

  if (action == 'retry' && onRetry != null && context.mounted) {
    final again = await onRetry();
    if (context.mounted) {
      await showBatchResultDialog(context,
          outcomes: again, withEtab: withEtab, describe: describe, onRetry: onRetry);
    }
  }
}

/// AK-SYNC U05 — Pastille compacte d'état agrégé d'un établissement
/// (utilisée dans la liste des établissements de la campagne).
class SchoolSyncBadge extends StatelessWidget {
  const SchoolSyncBadge({super.key, required this.summary, this.hasFeedback = false});
  final SchoolSyncSummary? summary;
  /// Vrai si une demande de correction admin est en attente (U19).
  final bool hasFeedback;

  @override
  Widget build(BuildContext context) {
    final s = summary;
    if (s == null || s.total == 0) {
      return hasFeedback ? _feedbackDot() : const SizedBox.shrink();
    }
    // Priorité : verrou > échec > attente > tout envoyé
    late final SyncStatus st;
    late final String txt;
    if (s.failed > 0)        { st = SyncStatus.failed;  txt = '${s.failed} échec'; }
    else if (s.pending > 0)  { st = SyncStatus.pending; txt = '${s.pending} à envoyer'; }
    else if (s.locked > 0 && s.locked == s.total) { st = SyncStatus.locked; txt = 'verrouillé'; }
    else                     { st = SyncStatus.sent;    txt = '${s.sent}/${s.total} envoyés'; }
    final c = syncStatusColor(st);
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        if (hasFeedback || s.correctionRequested) ...[_feedbackDot(), const SizedBox(width: 4)],
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
          decoration: BoxDecoration(
            color: c.withOpacity(0.10),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: c.withOpacity(0.45)),
          ),
          child: Row(mainAxisSize: MainAxisSize.min, children: [
            Icon(syncStatusIcon(st), size: 13, color: c),
            const SizedBox(width: 4),
            Text(txt, style: TextStyle(fontSize: 11, color: c, fontWeight: FontWeight.w600)),
          ]),
        ),
      ],
    );
  }

  Widget _feedbackDot() => const Tooltip(
        message: 'Correction demandée par l\'administration',
        child: Icon(Icons.assignment_late_outlined, size: 16, color: Color(0xFFB71C1C)),
      );
}
