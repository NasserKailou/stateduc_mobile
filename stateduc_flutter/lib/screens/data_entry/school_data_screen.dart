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

/// SchoolDataScreen — écran de saisie des données pour un établissement scolaire.
///
/// Réplique complètement la page camp.html (p_etab) + page_etab.js :
///   - initHtml()          → affichage du formulaire HTML dans le WebView
///   - initPageData()      → chargement des valeurs sauvegardées
///   - savePage()          → sauvegarde locale dans SQLite
///   - saveQstOnServer()   → envoi au serveur (POST data_save.php)
///   - reloadQstFromServer() → rechargement des données depuis le serveur
///   - sélecteur de question (thème)
///   - sélecteur de filtre (période)
///   - ajout de lignes dans les grilles (addGrilleLine)
///
/// Structure de l'écran :
///   AppBar :
///     - Titre : nom de l'établissement + nom de la question sélectionnée
///     - Actions : [sauvegarder] [envoyer au serveur] [menu options]
///   Body :
///     - [_SchoolInfoHeader] : bandeau d'identification de l'établissement
///     - [_MessageBanner]    : messages d'erreur / succès (dismissible)
///     - [_OfflineCoherenceBanner] : violations de cohérence offline
///     - [_QuestionSelector] : chips de sélection de thème (horizontale)
///     - [_FilterSelector]   : menu déroulant de période si filtre actif
///     - [DynamicFormWidget] : formulaire HTML dans WebView
///
/// Après un envoi réussi avec violations de cohérence serveur :
///   → Affiche un [AlertDialog] listant chaque violation avec son message.
class SchoolDataScreen extends StatefulWidget {
  const SchoolDataScreen({
    super.key,
    required this.campaign,
    required this.school,
    required this.idSystem,
    this.libSystem,    // ex. "Education de Base"
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
    // addPostFrameCallback : attend que le build initial soit terminé avant
    // d'appeler initForSchool, car read<Provider>() ne peut pas être appelé
    // pendant le build du widget parent (contrainte Flutter/Provider).
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final auth = context.read<AuthProvider>();
      final entry = context.read<DataEntryProvider>();
      // Stocke l'utilisateur courant pour que selectQuestion puisse
      // déclencher le rechargement automatique depuis le serveur
      entry.setCurrentUser(auth.user);
      entry.initForSchool(
        idCamp:         widget.campaign.idCamp,
        idEtab:         widget.school.idEtab,
        libEtab:        widget.school.libEtab,
        idSystem:       widget.idSystem,
        idRegroupEtab:  widget.school.idRegroup,
        idStatus:       widget.school.idStatus,   // ← statut numérique pour le pré-remplissage radio
        codeEtab:       widget.school.codeEtab,
        libyear:        auth.user?.libyear,
        codeyear:       auth.user?.codeyear,
        libStatus:      widget.school.libStatus,
        libSubsector:   widget.libSystem,   // type secteur enseignement (ex. "Education de Base")
        adminHierarchy: widget.school.libHierarchy,
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    // Consumer2 écoute AuthProvider (pour l'utilisateur courant)
    // et DataEntryProvider (pour l'état du formulaire et des données)
    return Consumer2<AuthProvider, DataEntryProvider>(
      builder: (context, auth, entry, _) {
        return LoadingOverlay(
          // Overlay de chargement pendant l'envoi au serveur ou le rechargement.
          // En cas de retry (_sendAttempt > 1), affiche le numéro de tentative.
          isLoading: entry.isSending || entry.isReloading,
          message: entry.isSending
              ? (entry.sendAttempt > 1
                  ? 'Envoi… (tentative ${entry.sendAttempt}/${DataEntryProvider.kMaxSendAttempts})'
                  : 'Envoi en cours…')
              : 'Rechargement…',
          child: Scaffold(
            appBar: AppBar(
              title: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Ligne 1 : nom de l'établissement
                  Text(widget.school.libEtab,
                      style: const TextStyle(fontSize: 16)),
                  // Ligne 2 : libellé de la question/thème sélectionné(e)
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

  /// Construit les boutons d'action de la AppBar.
  /// N'affiche rien si aucune question n'est sélectionnée.
  ///   - Icône disquette / coche : sauvegarde locale (change selon hasUnsavedChanges)
  ///   - Icône cloud upload   : envoi au serveur
  ///   - Menu popup           : option "Recharger depuis serveur"
  List<Widget> _buildActions(
      BuildContext context, AuthProvider auth, DataEntryProvider entry) {
    if (entry.selectedQuestion == null) return [];
    return [
      // Sauvegarder localement
      IconButton(
        icon: Icon(
          entry.hasUnsavedChanges
              ? Icons.save_outlined      // disquette si modifications non sauvegardées
              : Icons.check_circle_outline,  // coche si tout est sauvegardé
          color: entry.hasUnsavedChanges
              ? Theme.of(context).colorScheme.primary
              : null,
        ),
        tooltip: 'Sauvegarder',
        onPressed:
            entry.isSaving ? null : () => _saveLocally(context, entry),
      ),
      // Envoyer au serveur
      IconButton(
        icon: const Icon(Icons.cloud_upload_outlined),
        tooltip: 'Envoyer au serveur',
        onPressed: entry.isSending
            ? null
            : () => _sendToServer(context, auth, entry),
      ),
      // Options supplémentaires
      PopupMenuButton<String>(
        onSelected: (v) => _onMenuSelected(v, context, auth, entry),
        itemBuilder: (_) => [
          const PopupMenuItem(
              value: 'reload',
              child: ListTile(
                leading: Icon(Icons.refresh),
                title: Text('Recharger depuis serveur'),
              )),
          // ── Vérification manuelle de la cohérence offline ───────────────
          const PopupMenuItem(
              value: 'check_coherence',
              child: ListTile(
                leading: Icon(Icons.rule_folder_outlined),
                title: Text('Vérifier la cohérence'),
                subtitle: Text('Contrôle offline immédiat'),
              )),
          // ── Envoi global de tous les formulaires de cet établissement ──
          const PopupMenuItem(
              value: 'send_all',
              child: ListTile(
                leading: Icon(Icons.cloud_sync_outlined),
                title: Text('Envoyer tous les formulaires'),
                subtitle: Text('Tous les formulaires de cet établissement'),
              )),
        ],
      ),
    ];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  /// Construit le corps de l'écran : bandeau d'info + messages + formulaire.
  Widget _buildBody(
      BuildContext context, AuthProvider auth, DataEntryProvider entry) {
    return Column(
      children: [
        // ── Bandeau d'identification de l'établissement ──────────────────
        // Affiche : année scolaire, hiérarchie admin, nom/code/ID, statut, secteur
        _SchoolInfoHeader(
          entry: entry,
          campaign: widget.campaign,
          school: widget.school,
          libSystem: widget.libSystem,
        ),
        // ── Messages d'erreur / succès ───────────────────────────────────
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
        // ── Violations de cohérence hors ligne ───────────────────────────
        // Affichées dès qu'une violation est détectée après sauvegarde locale,
        // modification de champ (debounce 800 ms) ou ouverture d'un formulaire
        // déjà saisi. Un spinner indique que le contrôle est en cours.
        if (entry.isCheckingOffline)
          const LinearProgressIndicator(),
        if (entry.hasOfflineCoherenceErrors)
          _OfflineCoherenceBanner(
            errors: entry.offlineCoherenceErrors,
            onDismiss: entry.clearMessages,
          ),
        // ── Sélecteur de question (thème) ────────────────────────────────
        // Chips horizontales pour naviguer entre les thèmes du système éducatif
        if (entry.questions.isNotEmpty)
          _QuestionSelector(
            questions: entry.questions,
            selected: entry.selectedQuestion,
            onSelect: entry.selectQuestion,
          ),
        // ── Sélecteur de filtre (période) ────────────────────────────────
        // Visible seulement si la question courante supporte les filtres
        if (entry.selectedQuestion?.hasFilter == true &&
            entry.filterPeriods.isNotEmpty)
          _FilterSelector(
            filters: entry.filterPeriods,
            selected: entry.selectedFilter,
            onSelect: entry.selectFilter,
          ),
        // ── Formulaire de saisie ─────────────────────────────────────────
        Expanded(
          child: entry.selectedQuestion == null
              ? _buildNoQuestion()   // état vide si aucune question sélectionnée
              : _buildForm(context, entry),
        ),
      ],
    );
  }

  /// Affiche un écran vide invitant à sélectionner un formulaire.
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

  /// Construit le formulaire de saisie.
  ///
  /// Si le HTML est manquant / vide / placeholder d'erreur → affiche
  /// un message "Formulaire non disponible" avec conseil de re-téléchargement.
  /// Sinon → affiche [DynamicFormWidget] avec le HTML du thème sélectionné.
  Widget _buildForm(BuildContext context, DataEntryProvider entry) {
    final html = entry.formHtml;
    // Formulaire indisponible si HTML null, vide, ou placeholder d'erreur
    // stocké lors d'un échec de téléchargement de campagne
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
    // Formulaire disponible → WebView avec HTML du thème
    return DynamicFormWidget(
      html: html,
      data: entry.formData,
      validationErrors: entry.validationErrors,
      rules: entry.selectedQuestion != null
          ? [] // règles accessibles via le provider
          : [],
      onFieldChanged: entry.updateField,
      onAddGridRow: _onAddGridRow,
    );
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // ACTIONS
  // ═══════════════════════════════════════════════════════════════════════════

  /// Sauvegarde les données du formulaire courant dans SQLite (hors ligne).
  /// Affiche un SnackBar en cas d'échec.
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

  /// Envoie les données au serveur via POST data_save.php/theme_save.
  ///
  /// Workflow :
  ///   1. Validation des champs (avertissement seulement, pas de blocage)
  ///   2. [DataEntryProvider.sendToServer] → POST + cohérence serveur
  ///   3. En cas d'erreur → SnackBar
  ///   4. En cas de succès avec violations de cohérence →
  ///      [AlertDialog] listant les incohérences détectées par le serveur
  ///
  /// Réplique : stmPageEtab.saveQstOnServer() + contrôle cohérence serveur
  Future<void> _sendToServer(BuildContext context, AuthProvider auth,
      DataEntryProvider entry) async {
    if (auth.user == null) return;
    // Validation — ne bloque pas l'envoi (comme dans l'application originale)
    entry.validateAll();

    final ok = await entry.sendToServer(user: auth.user!);
    if (!mounted) return;

    if (!ok && entry.error != null) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(entry.error!),
          backgroundColor: Theme.of(context).colorScheme.error));
      return;
    }

    // ── Après envoi réussi : afficher les erreurs de cohérence si présentes ──
    // Le serveur exécute controle_theme_batch.class.php après la sauvegarde
    // et retourne les violations via data_controle.php.
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
                // Liste des violations avec message ou description générique
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

  /// Recharge les données du thème courant depuis le serveur.
  /// Demande une confirmation avant d'écraser les données locales.
  ///
  /// Réplique : stmPageEtab.reloadQstFromServer()
  Future<void> _reloadFromServer(BuildContext context, AuthProvider auth,
      DataEntryProvider entry) async {
    if (auth.user == null) return;
    // Boîte de dialogue de confirmation avant écrasement des données locales
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

  /// Gère la sélection dans le menu popup de la AppBar.
  void _onMenuSelected(String value, BuildContext context,
      AuthProvider auth, DataEntryProvider entry) {
    if (value == 'reload') {
      _reloadFromServer(context, auth, entry);
    } else if (value == 'check_coherence') {
      // Déclenchement manuel du contrôle de cohérence offline.
      // Utile si le check automatique (debounce) n'a pas encore pu s'exécuter
      // (ex. règles pas encore téléchargées depuis le serveur).
      entry.checkCoherenceOffline();
    } else if (value == 'send_all') {
      _sendAllForms(context, auth, entry);
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  /// Envoie TOUS les formulaires de l'établissement courant vers le serveur.
  ///
  /// Affiche une boîte de dialogue de confirmation, puis une barre de
  /// progression pendant l'envoi séquentiel de chaque formulaire.
  /// Un résumé est affiché en fin d'opération (succès / erreurs).
  // ═══════════════════════════════════════════════════════════════════════════
  Future<void> _sendAllForms(BuildContext context, AuthProvider auth,
      DataEntryProvider entry) async {
    if (auth.user == null) return;

    final total = entry.questions.length;
    if (total == 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Aucun formulaire à envoyer')),
      );
      return;
    }

    // Confirmation avant envoi
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.cloud_sync_outlined),
            SizedBox(width: 8),
            Text('Envoi global'),
          ],
        ),
        content: Text(
          'Envoyer les $total formulaire(s) de cet établissement ?\n\n'
          'Les données seront sauvegardées localement avant envoi.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Annuler'),
          ),
          ElevatedButton.icon(
            onPressed: () => Navigator.pop(ctx, true),
            icon: const Icon(Icons.cloud_upload_outlined),
            label: const Text('Envoyer'),
          ),
        ],
      ),
    );
    if (confirm != true || !mounted) return;

    // Dialogue de progression
    int _sent = 0;
    final progressNotifier = ValueNotifier<int>(0);

    if (!mounted) return;
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => ValueListenableBuilder<int>(
        valueListenable: progressNotifier,
        builder: (_, progress, __) => AlertDialog(
          title: const Text('Envoi en cours…'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              LinearProgressIndicator(
                value: total > 0 ? progress / total : 0,
              ),
              const SizedBox(height: 12),
              Text('$progress / $total formulaire(s)'),
            ],
          ),
        ),
      ),
    );

    // Lancement de l'envoi global
    final results = await entry.sendAllFormsForSchool(
      user: auth.user!,
      onProgress: (sent, tot) {
        progressNotifier.value = sent;
      },
    );

    // Ferme le dialogue de progression
    if (mounted) Navigator.of(context, rootNavigator: true).pop();
    progressNotifier.dispose();

    if (!mounted) return;

    // Résumé
    final okCount   = results.values.where((v) => v).length;
    final failCount = results.values.where((v) => !v).length;
    final hasError  = entry.error != null;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Row(
          children: [
            Icon(
              hasError || failCount > 0
                  ? Icons.warning_amber_rounded
                  : Icons.check_circle_outline,
              color: hasError || failCount > 0
                  ? Colors.orange
                  : Colors.green,
            ),
            const SizedBox(width: 8),
            const Text('Résultat de l\'envoi'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('✅  $okCount formulaire(s) envoyé(s) avec succès'),
            if (failCount > 0)
              Text('⚠️  $failCount formulaire(s) non envoyé(s) '  
                   '(données manquantes ou erreur serveur)'),
            if (hasError) ...[  
              const SizedBox(height: 8),
              Text(
                entry.error ?? '',
                style: TextStyle(
                    color: Theme.of(context).colorScheme.error,
                    fontSize: 13),
              ),
            ],
          ],
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Fermer'),
          ),
        ],
      ),
    );
  }

  /// Appelé par [DynamicFormWidget] quand l'utilisateur ajoute une ligne
  /// dans un formulaire de type grille.
  /// Le widget gère l'ajout en interne via le pont JavaScript ;
  /// le provider peut aussi suivre les lignes supplémentaires si nécessaire.
  void _onAddGridRow(String tableId) {
    // DynamicFormWidget gère l'ajout de ligne en interne ;
    // le provider peut traquer les lignes supplémentaires si besoin.
  }
}

// ─── Bandeau d'identification de l'établissement ─────────────────────────────
/// Affiche les informations d'identification au-dessus de chaque formulaire,
/// reproduisant l'en-tête de la page serveur :
///   Année en session : 2024-2025
///   AGADEZ / ADERBISANAT / ADEBISSANAT
///   Établissement : ABACHARA  ID : 70  Code Admin : 101012071
///   Statut : Public  Type secteur : Education de Base
///
/// Les valeurs proviennent du [DataEntryProvider] (priorité) puis du [School].
/// Le type de secteur vient de [libSystem] (jamais de libStatus, concept différent).
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
    // Hiérarchie administrative : provider d'abord, puis school.libHierarchy
    final adminHier   = entry.adminHierarchy ?? school.libHierarchy ?? '';
    final libEtab     = school.libEtab;
    final idEtab      = school.idEtab;
    final codeEtab    = school.codeEtab ?? '';
    final libStatus   = entry.libStatus ?? school.libStatus ?? '';
    // Type de secteur : provider d'abord, puis libSystem du widget
    // Ne jamais utiliser libStatus ici (concept différent)
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
          // Ligne : année scolaire en session
          if (libyear.isNotEmpty)
            _InfoRow(
              icon: Icons.calendar_today_outlined,
              text: 'Année en session : $libyear',
              bold: true,
            ),
          // Ligne : hiérarchie administrative (ex. AGADEZ / ADERBISANAT / …)
          if (adminHier.isNotEmpty)
            _InfoRow(
              icon: Icons.location_on_outlined,
              text: adminHier,
            ),
          // Chips : nom de l'établissement, ID interne, code administratif
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
          // Chips : statut (Public/Privé…) et type de secteur
          Wrap(
            spacing: 16,
            runSpacing: 2,
            children: [
              if (libStatus.isNotEmpty)
                _InfoChip(label: 'Statut', value: libStatus),
              if (libSubsect.isNotEmpty)
                _InfoChip(label: 'Type secteur', value: libSubsect),
            ],
          ),
        ],
      ),
    );
  }
}

/// Ligne d'information avec icône et texte.
/// Utilisé dans [_SchoolInfoHeader] pour les lignes pleine largeur.
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

/// Paire label : valeur en ligne.
/// Utilisé dans [_SchoolInfoHeader] pour les informations en [Wrap].
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

// ─── Sélecteur de question (thème) ──────────────────────────────────────────
/// Barre de chips horizontale pour naviguer entre les thèmes / formulaires
/// du système éducatif sélectionné.
/// La chip sélectionnée est mise en évidence avec la couleur primaire.
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

// ─── Sélecteur de filtre (période) ──────────────────────────────────────────
/// Menu déroulant de sélection de la période de collecte.
/// Visible uniquement si la question courante a des filtres (hasFilter == true)
/// et que des périodes sont disponibles pour la campagne.
/// Ex. : "Trimestre 1", "Trimestre 2", "Toutes"
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

// ─── Bannière de violations de cohérence hors ligne ─────────────────────────
/// Bannière expansible affichant les violations de cohérence détectées
/// par [CoherenceEvaluator] (contrôle offline) après une sauvegarde locale.
///
/// Chaque violation affiche :
///   - Le message configuré sur le serveur, OU
///   - Une description générée : "libRegle doit être critere libRegleAssoc
///     (valeurs : val1 / val2)"
///
/// La bannière est indépendante du contrôle serveur (data_controle.php) ;
/// elle permet de corriger les données avant l'envoi au serveur.
///
/// Style calqué sur le dialog server-side "Contrôle de cohérence" (screenshot)
/// avec mention explicite "contrôle local" pour distinguer les deux contrôles.
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
        color: Colors.white,
        border: Border.all(color: Colors.orange.shade400, width: 1.5),
        borderRadius: BorderRadius.circular(8),
        boxShadow: [
          BoxShadow(
            color: Colors.orange.shade100,
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          // ── En-tête — style identique au dialog serveur ─────────────────
          Container(
            padding: const EdgeInsets.fromLTRB(12, 10, 8, 10),
            decoration: BoxDecoration(
              color: Colors.orange.shade50,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(7)),
            ),
            child: Row(
              children: [
                const Icon(Icons.warning_amber_rounded,
                    color: Colors.orange, size: 20),
                const SizedBox(width: 8),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Contrôle de cohérence',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Text(
                        'Contrôle local — non encore envoyé au serveur',
                        style: TextStyle(
                          fontSize: 10,
                          color: Colors.grey,
                          fontStyle: FontStyle.italic,
                        ),
                      ),
                    ],
                  ),
                ),
                // Bouton fermer
                IconButton(
                  icon: const Icon(Icons.close, size: 16),
                  onPressed: onDismiss,
                  padding: EdgeInsets.zero,
                  constraints: const BoxConstraints(
                    minWidth: 28, minHeight: 28,
                  ),
                  tooltip: 'Fermer',
                ),
              ],
            ),
          ),
          // ── Corps — compteur + liste des violations ──────────────────────
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 8, 12, 4),
            child: Text(
              '${errors.length} incohérence(s) détectée(s) :',
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          // Liste des violations avec icône rouge (identique au dialog serveur)
          ...errors.map((e) => Padding(
                padding: const EdgeInsets.fromLTRB(12, 3, 12, 3),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.error_outline,
                        size: 15, color: Colors.red),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        e.message.isNotEmpty
                            ? e.message
                            // Description générique si pas de message configuré
                            : '${e.libRegle} doit être ${e.critere} ${e.libRegleAssoc} '
                                '(valeurs : ${e.value1.toStringAsFixed(0)} / ${e.value2.toStringAsFixed(0)})',
                        style: const TextStyle(fontSize: 12),
                      ),
                    ),
                  ],
                ),
              )),
          const SizedBox(height: 8),
        ],
      ),
    );
  }
}

// ─── Bannière de message (erreur ou succès) ──────────────────────────────────
/// Bannière dismissible affichant un message d'erreur (fond rouge) ou
/// de succès (fond vert) après une opération de sauvegarde ou d'envoi.
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
