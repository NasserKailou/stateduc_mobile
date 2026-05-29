import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/data_entry_provider.dart';
import '../../models/campaign.dart';
import '../../models/school.dart';
import '../../models/question.dart';
import '../../widgets/dynamic_form/dynamic_form_widget.dart';
import '../../widgets/common/loading_overlay.dart';
import '../../models/user.dart';  // FilterPeriod
import '../../services/coherence_evaluator.dart';  // OfflineCoherenceError

/// SchoolDataScreen — data entry for a specific school.
///
/// Mirrors:
///   camp.html (p_etab page) + page_etab.js completely:
///   - initHtml()       → render form HTML
///   - initPageData()   → load saved values
///   - savePage()       → save locally
///   - saveQstOnServer() → send to server
///   - reload from server
///   - question selector
///   - filter (period) selector
///   - dynamic grid rows (addGrilleLine)
class SchoolDataScreen extends StatefulWidget {
  const SchoolDataScreen({
    super.key,
    required this.campaign,
    required this.school,
    required this.idSystem,
    this.libSystem,    // e.g. "Education de Base"
  });
  final Campaign campaign;
  final School school;
  final String idSystem;
  final String? libSystem;

  @override
  State<SchoolDataScreen> createState() => _SchoolDataScreenState();
}

class _SchoolDataScreenState extends State<SchoolDataScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final auth = context.read<AuthProvider>();
      final entry = context.read<DataEntryProvider>();
      // Store the current user so selectQuestion can auto-reload from server
      entry.setCurrentUser(auth.user);
      entry.initForSchool(
        idCamp:         widget.campaign.idCamp,
        idEtab:         widget.school.idEtab,
        libEtab:        widget.school.libEtab,
        idSystem:       widget.idSystem,
        idRegroupEtab:  widget.school.idRegroup,
        idStatus:       widget.school.idStatus,   // ← numeric status for radio pre-fill
        codeEtab:       widget.school.codeEtab,
        libyear:        auth.user?.libyear,
        codeyear:       auth.user?.codeyear,
        libStatus:      widget.school.libStatus,
        libSubsector:   widget.libSystem ?? widget.school.libStatus,
        adminHierarchy: widget.school.libHierarchy,
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    return Consumer2<AuthProvider, DataEntryProvider>(
      builder: (context, auth, entry, _) {
        return LoadingOverlay(
          isLoading: entry.isSending || entry.isReloading,
          message:
              entry.isSending ? 'Envoi en cours…' : 'Rechargement…',
          child: Scaffold(
            appBar: AppBar(
              title: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(widget.school.libEtab,
                      style: const TextStyle(fontSize: 16)),
                  if (entry.selectedQuestion != null)
                    Text(entry.selectedQuestion!.libQst,
                        style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.normal)),
                ],
              ),
              actions: _buildActions(context, auth, entry),
            ),
            body: entry.isLoading
                ? const Center(child: CircularProgressIndicator())
                : _buildBody(context, auth, entry),
          ),
        );
      },
    );
  }

  List<Widget> _buildActions(
      BuildContext context, AuthProvider auth, DataEntryProvider entry) {
    if (entry.selectedQuestion == null) return [];
    return [
      // Save locally
      IconButton(
        icon: Icon(
          entry.hasUnsavedChanges
              ? Icons.save_outlined
              : Icons.check_circle_outline,
          color: entry.hasUnsavedChanges
              ? Theme.of(context).colorScheme.primary
              : null,
        ),
        tooltip: 'Sauvegarder',
        onPressed:
            entry.isSaving ? null : () => _saveLocally(context, entry),
      ),
      // Send to server
      IconButton(
        icon: const Icon(Icons.cloud_upload_outlined),
        tooltip: 'Envoyer au serveur',
        onPressed: entry.isSending
            ? null
            : () => _sendToServer(context, auth, entry),
      ),
      // More options
      PopupMenuButton<String>(
        onSelected: (v) => _onMenuSelected(v, context, auth, entry),
        itemBuilder: (_) => [
          const PopupMenuItem(
              value: 'reload',
              child: ListTile(
                leading: Icon(Icons.refresh),
                title: Text('Recharger depuis serveur'),
              )),
        ],
      ),
    ];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  Widget _buildBody(
      BuildContext context, AuthProvider auth, DataEntryProvider entry) {
    return Column(
      children: [
        // ── School identification header ─────────────────────────────────
        _SchoolInfoHeader(
          entry: entry,
          campaign: widget.campaign,
          school: widget.school,
          libSystem: widget.libSystem,
        ),
        // ── Messages ────────────────────────────────────────────────────
        if (entry.error != null)
          _MessageBanner(
            message: entry.error!,
            isError: true,
            onDismiss: entry.clearError,
          ),
        if (entry.successMessage != null)
          _MessageBanner(
            message: entry.successMessage!,
            isError: false,
            onDismiss: entry.clearMessages,
          ),
        // ── Offline coherence violations ─────────────────────────────────
        if (entry.hasOfflineCoherenceErrors)
          _OfflineCoherenceBanner(
            errors: entry.offlineCoherenceErrors,
            onDismiss: entry.clearMessages,
          ),
        // ── Question selector ────────────────────────────────────────────
        if (entry.questions.isNotEmpty)
          _QuestionSelector(
            questions: entry.questions,
            selected: entry.selectedQuestion,
            onSelect: entry.selectQuestion,
          ),
        // ── Filter selector (if question has filter) ─────────────────────
        if (entry.selectedQuestion?.hasFilter == true &&
            entry.filterPeriods.isNotEmpty)
          _FilterSelector(
            filters: entry.filterPeriods,
            selected: entry.selectedFilter,
            onSelect: entry.selectFilter,
          ),
        // ── Form ──────────────────────────────────────────────────────────
        Expanded(
          child: entry.selectedQuestion == null
              ? _buildNoQuestion()
              : _buildForm(context, entry),
        ),
      ],
    );
  }

  Widget _buildNoQuestion() {
    return const Center(
      child: Padding(
        padding: EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.assignment_outlined, size: 56),
            SizedBox(height: 12),
            Text('Sélectionnez un formulaire pour commencer la saisie',
                textAlign: TextAlign.center),
          ],
        ),
      ),
    );
  }

  Widget _buildForm(BuildContext context, DataEntryProvider entry) {
    final html = entry.formHtml;
    // Show offline message when HTML is missing, empty, or is the error
    // placeholder stored during a failed campaign download
    final isUnavailable = html == null ||
        html.trim().isEmpty ||
        html.contains('Formulaire non disponible');
    if (isUnavailable) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.cloud_off_outlined, size: 48, color: Colors.grey),
              SizedBox(height: 12),
              Text(
                'Formulaire non disponible.\nRe-téléchargez la campagne pour récupérer les formulaires.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey),
              ),
            ],
          ),
        ),
      );
    }
    return DynamicFormWidget(
      html: html,
      data: entry.formData,
      validationErrors: entry.validationErrors,
      rules: entry.selectedQuestion != null
          ? [] // rules accessed via provider
          : [],
      onFieldChanged: entry.updateField,
      onAddGridRow: _onAddGridRow,
    );
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // ACTIONS
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> _saveLocally(
      BuildContext context, DataEntryProvider entry) async {
    final ok = await entry.saveLocally();
    if (!ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content: Text(entry.error ?? 'Erreur sauvegarde'),
              backgroundColor: Theme.of(context).colorScheme.error));
    }
  }

  Future<void> _sendToServer(BuildContext context, AuthProvider auth,
      DataEntryProvider entry) async {
    if (auth.user == null) return;
    // Validate — but only warn, do not block send (mirrors original app)
    entry.validateAll();

    final ok = await entry.sendToServer(user: auth.user!);
    if (!mounted) return;

    if (!ok && entry.error != null) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(entry.error!),
          backgroundColor: Theme.of(context).colorScheme.error));
      return;
    }

    // ── After successful send: show coherence errors if any ─────────────────
    if (ok && entry.hasCoherenceErrors) {
      final errors = entry.coherenceErrors;
      await showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          title: Row(
            children: const [
              Icon(Icons.warning_amber_rounded, color: Colors.orange),
              SizedBox(width: 8),
              Text('Contrôle de cohérence'),
            ],
          ),
          content: SizedBox(
            width: double.maxFinite,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '${errors.length} incohérence(s) détectée(s) :',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                ...errors.map((e) => Padding(
                  padding: const EdgeInsets.symmetric(vertical: 4),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(Icons.error_outline,
                          size: 16, color: Colors.red),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          e.message.isNotEmpty
                              ? e.message
                              : 'Règle ${e.idRegle} ${e.critere} règle ${e.idRegleAssoc}',
                          style: const TextStyle(fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                )),
              ],
            ),
          ),
          actions: [
            ElevatedButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Compris'),
            ),
          ],
        ),
      );
    }
  }

  Future<void> _reloadFromServer(BuildContext context, AuthProvider auth,
      DataEntryProvider entry) async {
    if (auth.user == null) return;
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Recharger depuis le serveur'),
        content: const Text(
            'Les données locales seront remplacées par les données du serveur. Continuer ?'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Annuler')),
          ElevatedButton(
              onPressed: () => Navigator.pop(ctx, true),
              child: const Text('Recharger')),
        ],
      ),
    );
    if (confirm == true && mounted) {
      await entry.reloadFromServer(user: auth.user!);
    }
  }

  void _onMenuSelected(String value, BuildContext context,
      AuthProvider auth, DataEntryProvider entry) {
    if (value == 'reload') {
      _reloadFromServer(context, auth, entry);
    }
  }

  void _onAddGridRow(String tableId) {
    // DynamicFormWidget handles this internally;
    // provider can also track extra rows if needed.
  }
}

// ─── School identification info header ───────────────────────────────────────
/// Displays the school identification breadcrumb above each form,
/// mirroring the server's header:
///   Année Courante: 2024-2025
///   AGADEZ / ADERBISANAT / ADEBISSANAT
///   Nom établissement: ABACHARA  Identifiant: 70  Code Administratif: 101012071
///   Statut: Public  Sous secteur: Education de Base
class _SchoolInfoHeader extends StatelessWidget {
  const _SchoolInfoHeader({
    required this.entry,
    required this.campaign,
    required this.school,
    this.libSystem,
  });
  final DataEntryProvider entry;
  final Campaign campaign;
  final School school;
  final String? libSystem;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final libyear     = entry.libyear ?? '';
    final adminHier   = entry.adminHierarchy ?? school.libHierarchy ?? '';
    final libEtab     = school.libEtab;
    final idEtab      = school.idEtab;
    final codeEtab    = school.codeEtab ?? '';
    final libStatus   = entry.libStatus ?? school.libStatus ?? '';
    final libSubsect  = entry.libSubsector ?? libSystem ?? '';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: theme.colorScheme.primaryContainer.withOpacity(0.18),
        border: Border(
          bottom: BorderSide(color: theme.colorScheme.primary.withOpacity(0.3)),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (libyear.isNotEmpty)
            _InfoRow(
              icon: Icons.calendar_today_outlined,
              text: 'Année Courante : $libyear',
              bold: true,
            ),
          if (adminHier.isNotEmpty)
            _InfoRow(
              icon: Icons.location_on_outlined,
              text: adminHier,
            ),
          Wrap(
            spacing: 16,
            runSpacing: 2,
            children: [
              _InfoChip(label: 'Établissement', value: libEtab),
              if (idEtab.isNotEmpty)
                _InfoChip(label: 'ID', value: idEtab),
              if (codeEtab.isNotEmpty)
                _InfoChip(label: 'Code Admin', value: codeEtab),
            ],
          ),
          Wrap(
            spacing: 16,
            runSpacing: 2,
            children: [
              if (libStatus.isNotEmpty)
                _InfoChip(label: 'Statut', value: libStatus),
              if (libSubsect.isNotEmpty)
                _InfoChip(label: 'Sous secteur', value: libSubsect),
            ],
          ),
        ],
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.icon, required this.text, this.bold = false});
  final IconData icon;
  final String text;
  final bool bold;
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 2),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 13,
              color: Theme.of(context).colorScheme.primary.withOpacity(0.7)),
          const SizedBox(width: 4),
          Expanded(
            child: Text(
              text,
              style: TextStyle(
                fontSize: 11,
                fontWeight: bold ? FontWeight.bold : FontWeight.normal,
                color: Theme.of(context).colorScheme.onSurface,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({required this.label, required this.value});
  final String label;
  final String value;
  @override
  Widget build(BuildContext context) {
    return RichText(
      text: TextSpan(
        style: const TextStyle(fontSize: 11, color: Colors.black87),
        children: [
          TextSpan(
            text: '$label: ',
            style: const TextStyle(fontWeight: FontWeight.w600),
          ),
          TextSpan(text: value),
        ],
      ),
    );
  }
}

// ─── Question selector ───────────────────────────────────────────────────────
class _QuestionSelector extends StatelessWidget {
  const _QuestionSelector({
    required this.questions,
    required this.selected,
    required this.onSelect,
  });
  final List<Question> questions;
  final Question? selected;
  final Future<void> Function(Question) onSelect;

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Theme.of(context).colorScheme.surfaceContainerHighest,
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
        child: Row(
          children: questions
              .map((q) => Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: ChoiceChip(
                      label: Text(
                        q.libQst,
                        style: TextStyle(
                          fontSize: 13,
                          color: selected?.idQst == q.idQst
                              ? Colors.white
                              : Colors.black87,
                        ),
                      ),
                      selected: selected?.idQst == q.idQst,
                      selectedColor: Theme.of(context).colorScheme.primary,
                      backgroundColor: Colors.grey.shade200,
                      side: BorderSide(color: Colors.grey.shade400),
                      onSelected: (_) => onSelect(q),
                    ),
                  ))
              .toList(),
        ),
      ),
    );
  }
}

// ─── Filter selector ─────────────────────────────────────────────────────────
class _FilterSelector extends StatelessWidget {
  const _FilterSelector({
    required this.filters,
    required this.selected,
    required this.onSelect,
  });
  final List<FilterPeriod> filters;
  final FilterPeriod? selected;
  final Future<void> Function(FilterPeriod?) onSelect;

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Theme.of(context).colorScheme.surface,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      child: Row(
        children: [
          const Text('Période :',
              style: TextStyle(fontWeight: FontWeight.w600)),
          const SizedBox(width: 8),
          DropdownButton<FilterPeriod?>(
            value: selected,
            hint: const Text('Toutes'),
            items: [
              const DropdownMenuItem<FilterPeriod?>(
                  value: null, child: Text('Toutes')),
              ...filters.map((f) => DropdownMenuItem<FilterPeriod?>(
                    value: f,
                    child: Text(f.libFilter),
                  )),
            ],
            onChanged: onSelect,
          ),
        ],
      ),
    );
  }
}

// ─── Offline coherence banner ────────────────────────────────────────────────
class _OfflineCoherenceBanner extends StatelessWidget {
  const _OfflineCoherenceBanner({
    required this.errors,
    required this.onDismiss,
  });
  final List<OfflineCoherenceError> errors;
  final VoidCallback onDismiss;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.orange.shade50,
        border: Border.all(color: Colors.orange.shade300),
        borderRadius: BorderRadius.circular(8),
      ),
      child: ExpansionTile(
        leading: const Icon(Icons.warning_amber_rounded,
            color: Colors.orange, size: 22),
        title: Text(
          '${errors.length} incohérence(s) locale(s)',
          style: const TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.deepOrange),
        ),
        subtitle: const Text(
          'Contrôle offline — non envoyé au serveur',
          style: TextStyle(fontSize: 11, color: Colors.grey),
        ),
        trailing: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            IconButton(
              icon: const Icon(Icons.close, size: 16),
              onPressed: onDismiss,
              padding: EdgeInsets.zero,
              constraints: const BoxConstraints(),
              tooltip: 'Fermer',
            ),
          ],
        ),
        children: errors
            .map((e) => Padding(
                  padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(Icons.error_outline,
                          size: 14, color: Colors.deepOrange),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          e.message.isNotEmpty
                              ? e.message
                              : '${e.libRegle} doit être ${e.critere} ${e.libRegleAssoc} '
                                  '(valeurs : ${e.value1.toStringAsFixed(0)} / ${e.value2.toStringAsFixed(0)})',
                          style: const TextStyle(fontSize: 12),
                        ),
                      ),
                    ],
                  ),
                ))
            .toList(),
      ),
    );
  }
}

// ─── Message banner ──────────────────────────────────────────────────────────
class _MessageBanner extends StatelessWidget {
  const _MessageBanner({
    required this.message,
    required this.isError,
    required this.onDismiss,
  });
  final String message;
  final bool isError;
  final VoidCallback onDismiss;

  @override
  Widget build(BuildContext context) {
    final color = isError
        ? Theme.of(context).colorScheme.errorContainer
        : Theme.of(context).colorScheme.secondaryContainer;
    final textColor = isError
        ? Theme.of(context).colorScheme.onErrorContainer
        : Theme.of(context).colorScheme.onSecondaryContainer;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      color: color,
      child: Row(
        children: [
          Icon(isError ? Icons.error_outline : Icons.check_circle_outline,
              color: textColor, size: 18),
          const SizedBox(width: 8),
          Expanded(
              child:
                  Text(message, style: TextStyle(color: textColor))),
          IconButton(
            icon: Icon(Icons.close, size: 16, color: textColor),
            onPressed: onDismiss,
            padding: EdgeInsets.zero,
            constraints: const BoxConstraints(),
          ),
        ],
      ),
    );
  }
}
