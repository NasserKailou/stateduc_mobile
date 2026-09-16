import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/data_entry_provider.dart';
import '../../models/campaign.dart';
import '../../models/question.dart';
import '../../models/school.dart';
import '../../models/sync_status.dart';
import '../../services/database_service.dart';
import '../../widgets/sync/sync_widgets.dart';

/// PendingFormsScreen — AK-SYNC U05/U06/U07
///
/// Liste nominative des formulaires de la campagne qui restent à transmettre
/// (statut `pending`, `failed`, `kosave`, ou repli `is_sent = 0`), groupés par
/// établissement, avec la dernière erreur connue et le nombre de tentatives.
///
/// Actions :
///   - « Tout envoyer » : envoie uniquement ces formulaires (moteur unique du
///     provider, verrou global U25), puis affiche le résultat nominatif avec
///     « Réessayer les échecs ».
///   - Tap sur un établissement : envoi ciblé de ses formulaires en attente.
class PendingFormsScreen extends StatefulWidget {
  const PendingFormsScreen({
    super.key,
    required this.campaign,
    required this.idSystem,
    this.libSystem,
  });
  final Campaign campaign;
  final String idSystem;
  final String? libSystem;

  @override
  State<PendingFormsScreen> createState() => _PendingFormsScreenState();
}

class _PendingFormsScreenState extends State<PendingFormsScreen> {
  List<FormSyncStatus> _pending = [];
  Map<String, String> _libEtab = {};
  Map<String, String> _libQst  = {};
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _load());
  }

  Future<void> _load() async {
    if (!mounted) return;
    setState(() => _loading = true);
    final entry = context.read<DataEntryProvider>();
    final db = DatabaseService();
    try {
      final pending = await entry.listPendingForms(widget.campaign.idCamp);
      List<School> schools = [];
      List<Question> questions = [];
      try { schools = await db.getSchools(widget.campaign.idCamp); } catch (_) {}
      try { questions = await db.getQuestions(widget.campaign.idCamp, widget.idSystem); } catch (_) {}
      if (!mounted) return;
      setState(() {
        _pending = pending;
        _libEtab = {for (final s in schools) s.idEtab: s.libEtab};
        _libQst  = {for (final q in questions) q.idQst: q.libQst};
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _loading = false);
      ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur de lecture : $e')));
    }
  }

  /// Regroupe les formulaires par établissement (ordre d'apparition conservé).
  Map<String, List<FormSyncStatus>> get _byEtab {
    final m = <String, List<FormSyncStatus>>{};
    for (final f in _pending) {
      m.putIfAbsent(f.idEtab, () => []).add(f);
    }
    return m;
  }

  @override
  Widget build(BuildContext context) {
    final grouped = _byEtab;
    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('Formulaires en attente'),
            Text(
              '${widget.campaign.libCamp}${widget.libSystem != null ? ' · ${widget.libSystem}' : ''}',
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.normal),
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Actualiser',
            icon: const Icon(Icons.refresh),
            onPressed: _loading ? null : _load,
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _pending.isEmpty
              ? _buildEmpty(context)
              : Column(
                  children: [
                    _buildSummaryBar(context),
                    Expanded(
                      child: ListView(
                        padding: const EdgeInsets.all(8),
                        children: grouped.entries
                            .map((e) => _buildEtabCard(context, e.key, e.value))
                            .toList(),
                      ),
                    ),
                  ],
                ),
      bottomNavigationBar: _pending.isEmpty
          ? null
          : SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(12, 6, 12, 10),
                child: Consumer<DataEntryProvider>(
                  builder: (ctx, entry, _) => ElevatedButton.icon(
                    onPressed: entry.isSending ? null : () => _sendPending(context),
                    icon: entry.isSending
                        ? const SizedBox(
                            width: 16, height: 16,
                            child: CircularProgressIndicator(strokeWidth: 2))
                        : const Icon(Icons.cloud_upload_outlined),
                    label: Text(entry.isSending
                        ? 'Envoi en cours…'
                        : 'Envoyer les ${_pending.length} formulaire(s) en attente'),
                    style: ElevatedButton.styleFrom(minimumSize: const Size.fromHeight(46)),
                  ),
                ),
              ),
            ),
    );
  }

  Widget _buildEmpty(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.cloud_done_outlined, size: 64, color: Color(0xFF2E7D32)),
            const SizedBox(height: 16),
            Text('Tous les formulaires saisis ont été transmis.',
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            const Text(
              'Aucun formulaire en attente, en échec ou KOSAVE pour cette campagne.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.black54),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryBar(BuildContext context) {
    final pend   = _pending.where((f) => f.status == SyncStatus.pending).length;
    final failed = _pending.where((f) => f.status == SyncStatus.failed).length;
    final kosave = _pending.where((f) => f.status == SyncStatus.kosave).length;
    Widget chip(SyncStatus s, int n) => n == 0
        ? const SizedBox.shrink()
        : Padding(
            padding: const EdgeInsets.only(right: 8),
            child: Chip(
              avatar: Icon(syncStatusIcon(s), size: 16, color: syncStatusColor(s)),
              label: Text('$n ${s.shortLabel}', style: const TextStyle(fontSize: 12)),
              backgroundColor: syncStatusColor(s).withOpacity(0.10),
              side: BorderSide(color: syncStatusColor(s).withOpacity(0.4)),
            ),
          );
    return Container(
      width: double.infinity,
      color: Colors.grey.shade100,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      child: Row(children: [
        Text('${_pending.length} formulaire(s) · ${_byEtab.length} établissement(s)',
            style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
        const Spacer(),
        chip(SyncStatus.pending, pend),
        chip(SyncStatus.failed, failed),
        chip(SyncStatus.kosave, kosave),
      ]),
    );
  }

  Widget _buildEtabCard(BuildContext context, String idEtab, List<FormSyncStatus> forms) {
    final lib = _libEtab[idEtab] ?? 'Établissement $idEtab';
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 4),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          ListTile(
            dense: true,
            leading: const Icon(Icons.business_outlined),
            title: Text(lib, style: const TextStyle(fontWeight: FontWeight.w600)),
            subtitle: Text('${forms.length} formulaire(s) à envoyer',
                style: const TextStyle(fontSize: 12)),
            trailing: Consumer<DataEntryProvider>(
              builder: (ctx, entry, _) => TextButton.icon(
                onPressed: entry.isSending ? null : () => _sendPending(context, idEtab: idEtab),
                icon: const Icon(Icons.send_outlined, size: 16),
                label: const Text('Envoyer', style: TextStyle(fontSize: 12)),
              ),
            ),
          ),
          const Divider(height: 1),
          ...forms.map((f) => ListTile(
                dense: true,
                leading: Icon(syncStatusIcon(f.status), color: syncStatusColor(f.status), size: 20),
                title: Text(
                  (_libQst[f.idQst] ?? 'Thème ${f.idQst}') +
                      (f.idFilter.isEmpty ? '' : '  [période ${f.idFilter}]'),
                  style: const TextStyle(fontSize: 13),
                ),
                subtitle: Text(
                  [
                    f.status.label,
                    if (f.attempts > 0) '${f.attempts} tentative(s)',
                    if (f.lastAttemptAt != null) 'dernier essai ${fmtSyncDate(f.lastAttemptAt)}',
                    if ((f.lastError ?? '').isNotEmpty) f.lastError!,
                  ].join(' · '),
                  style: const TextStyle(fontSize: 11),
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                ),
              )),
        ],
      ),
    );
  }

  Future<void> _sendPending(BuildContext context, {String? idEtab}) async {
    final auth  = context.read<AuthProvider>();
    final entry = context.read<DataEntryProvider>();
    if (auth.user == null) return;
    if (DataEntryProvider.isAnySendRunning) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Un envoi est déjà en cours. Patientez puis réessayez.')));
      return;
    }
    final targets = idEtab == null ? _pending : _pending.where((f) => f.idEtab == idEtab).toList();
    final total = targets.length;
    if (total == 0) return;

    final progress = ValueNotifier<int>(0);
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => ValueListenableBuilder<int>(
        valueListenable: progress,
        builder: (_, p, __) => AlertDialog(
          title: const Text('Envoi en cours…'),
          content: Column(mainAxisSize: MainAxisSize.min, children: [
            LinearProgressIndicator(value: total > 0 ? p / total : null),
            const SizedBox(height: 12),
            Text('$p / $total formulaire(s)'),
          ]),
        ),
      ),
    );
    final outcomes = await entry.sendPendingForms(
      user: auth.user!,
      idCamp: widget.campaign.idCamp,
      idSystem: widget.idSystem,
      idEtab: idEtab,
      onProgress: (sent, _) => progress.value = sent,
    );
    if (mounted) Navigator.of(context, rootNavigator: true).pop();
    progress.dispose();
    if (!mounted) return;

    await showBatchResultDialog(
      context,
      outcomes: outcomes,
      providerError: entry.error,
      onRetry: () => entry.retryFailedOutcomes(
        user: auth.user!,
        idCamp: widget.campaign.idCamp,
        idSystem: widget.idSystem,
      ),
    );
    if (mounted) await _load();
  }
}
