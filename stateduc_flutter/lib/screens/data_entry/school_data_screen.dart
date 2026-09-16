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
import '../../models/sync_status.dart';  // AK-SYNC : SyncStatus, SendOutcome, SyncFeedback
import '../../widgets/sync/sync_widgets.dart';  // AK-SYNC : couleurs/icônes, dialogue résultat
import '../../services/coherence_evaluator.dart';  // OfflineCoherenceError
import '../../services/theme_rule_engine.dart';     // ThemeCoherenceError

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
///     - [_ThemeCoherenceBanner] : violations de cohérence offline (moteur DICO_REGLE_THEME)
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
  // AK-YEAR-MULTI-01 : mémorise l'année active lors du dernier initForSchool()
  // pour détecter un changement d'année en cours de session (ex. via Paramètres).
  String? _lastInitYear;

  @override
  void initState() {
    super.initState();
    // addPostFrameCallback : attend que le build initial soit terminé avant
    // d'appeler initForSchool, car read<Provider>() ne peut pas être appelé
    // pendant le build du widget parent (contrainte Flutter/Provider).
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _doInitForSchool();
    });
  }

  // AK-YEAR-MULTI-01 : initialise (ou ré-initialise) le provider pour l'école
  // courante avec l'année active au moment de l'appel.
  void _doInitForSchool() {
    if (!mounted) return;
    final auth  = context.read<AuthProvider>();
    final entry = context.read<DataEntryProvider>();
    final yearCode = auth.effectiveYearCode.isNotEmpty
        ? auth.effectiveYearCode
        : auth.user?.codeyear;

    // Stocke l'utilisateur courant pour que selectQuestion puisse
    // déclencher le rechargement automatique depuis le serveur
    entry.setCurrentUser(auth.user);
    entry.initForSchool(
      idCamp:         widget.campaign.idCamp,
      idEtab:         widget.school.idEtab,
      libEtab:        widget.school.libEtab,
      idSystem:       widget.idSystem,
      idRegroupEtab:  widget.school.idRegroup,
      idStatus:       widget.school.idStatus,
      codeEtab:       widget.school.codeEtab,
      libyear:        auth.activeYear?.libelle ?? auth.user?.libyear,
      // AK-YEAR-MULTI-01 : utilise l'année active choisie dans Paramètres,
      // avec fallback sur user.codeyear (comportement pré-AK-YEAR-01).
      codeyear:       yearCode,
      libStatus:      widget.school.libStatus,
      libSubsector:   widget.libSystem,
      adminHierarchy: widget.school.libHierarchy,
    );
    _lastInitYear = yearCode;
  }

  @override
  Widget build(BuildContext context) {
    // Consumer2 écoute AuthProvider (pour l'utilisateur courant)
    // et DataEntryProvider (pour l'état du formulaire et des données)
    return Consumer2<AuthProvider, DataEntryProvider>(
      builder: (context, auth, entry, _) {
        // AK-YEAR-MULTI-01 : détection du changement d'année en cours de session.
        // AuthProvider.setActiveYear() appelle notifyListeners() → ce builder
        // est rappelé. Si l'année a changé depuis le dernier initForSchool(),
        // on ré-initialise le provider pour recharger les données de la nouvelle année.
        // WidgetsBinding.addPostFrameCallback évite d'appeler setState/initForSchool
        // pendant le build (contrainte Flutter).
        final currentYear = auth.effectiveYearCode.isNotEmpty
            ? auth.effectiveYearCode
            : auth.user?.codeyear;
        if (_lastInitYear != null && currentYear != _lastInitYear) {
          WidgetsBinding.instance.addPostFrameCallback((_) {
            if (mounted) _doInitForSchool();
          });
        }

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
  ///
  /// FIX SESSION 66 — issue #5 : boutons redessinés pour être clairement visibles
  /// sur tous les gabarits d'écran (smartphone / tablette) et accessibles.
  ///   - Bouton VERT   : sauvegarde locale  (fond vert vif, icône + libellé)
  ///   - Bouton BLEU   : envoi au serveur   (fond bleu foncé, icône + libellé)
  ///   - État sauvegardé : fond vert clair + icône coche (feedback visuel)
  ///   - Contraste WCAG AA garanti : texte blanc sur fond coloré (ratio > 4.5:1)
  List<Widget> _buildActions(
      BuildContext context, AuthProvider auth, DataEntryProvider entry) {
    if (entry.selectedQuestion == null) return [];

    // Palette accessible : WCAG AA minimum ratio 4.5:1 texte blanc
    const Color saveUnsavedBg  = Color(0xFF2E7D32); // vert foncé — modifications en attente
    const Color saveSavedBg    = Color(0xFF388E3C); // vert moyen — tout sauvegardé
    const Color sendBg         = Color(0xFF1565C0); // bleu foncé — envoi serveur
    const Color disabledBg     = Color(0xFF9E9E9E); // gris       — action en cours

    final bool saving  = entry.isSaving;
    final bool sending = entry.isSending;
    final bool unsaved = entry.hasUnsavedChanges;
    // AK-SYNC U21 : formulaire validé/verrouillé par l'administration → envoi impossible
    final bool locked  = entry.isCurrentFormLocked;

    return [
      // ── Bouton Sauvegarder localement ─────────────────────────────────
      // Fond VERT pour différencier clairement du bouton "Envoyer"
      // Icône + libellé court pour éviter toute confusion avec le cloud
      Padding(
        padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 2),
        child: Tooltip(
          message: saving
              ? 'Sauvegarde en cours…'
              : (unsaved
                  ? 'Sauvegarder les modifications localement'
                  : 'Données sauvegardées localement'),
          child: ElevatedButton.icon(
            icon: Icon(
              unsaved ? Icons.save_rounded : Icons.check_circle_rounded,
              size: 18,
              color: Colors.white,
            ),
            label: Text(
              unsaved ? 'Sauver' : 'Sauvé',
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            ),
            style: ElevatedButton.styleFrom(
              backgroundColor: saving
                  ? disabledBg
                  : (unsaved ? saveUnsavedBg : saveSavedBg),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 0),
              minimumSize: const Size(72, 36),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
              elevation: unsaved ? 4 : 1,
            ),
            onPressed: saving ? null : () => _saveLocally(context, entry),
          ),
        ),
      ),
      // ── Bouton Envoyer au serveur ──────────────────────────────────────
      // Fond BLEU foncé — action réseau / serveur
      Padding(
        padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 2),
        child: Tooltip(
          message: sending
              ? 'Envoi en cours…'
              : (locked
                  ? 'Formulaire verrouillé par l\'administration — envoi impossible'
                  : 'Envoyer les données au serveur'),
          child: ElevatedButton.icon(
            icon: Icon(
              sending
                  ? Icons.hourglass_top_rounded
                  : (locked ? Icons.lock_outline : Icons.cloud_upload_rounded),
              size: 18,
              color: Colors.white,
            ),
            label: Text(
              sending ? '…' : 'Envoyer',
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            ),
            style: ElevatedButton.styleFrom(
              backgroundColor: (sending || locked) ? disabledBg : sendBg,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 0),
              minimumSize: const Size(80, 36),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
              elevation: 4,
            ),
            onPressed: (sending || locked)
                ? null
                : () => _sendToServer(context, auth, entry),
          ),
        ),
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
          // ── AK-SYNC U07 : envoi ciblé des formulaires en attente / en échec ──
          const PopupMenuItem(
              value: 'send_pending',
              child: ListTile(
                leading: Icon(Icons.outbox_outlined),
                title: Text('Envoyer les formulaires en attente'),
                subtitle: Text('Uniquement non envoyés ou en échec'),
              )),
          // ── AK-SYNC U17 : rafraîchir l'état de sync depuis le serveur ──
          const PopupMenuItem(
              value: 'sync_refresh',
              child: ListTile(
                leading: Icon(Icons.sync_outlined),
                title: Text('Actualiser l\'état de synchronisation'),
                subtitle: Text('Statuts serveur, verrous, corrections'),
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
        // ── AK-SYNC U05/U08 : état de synchronisation du formulaire courant ──
        if (entry.selectedQuestion != null)
          _SyncStatusLine(entry: entry),
        // ── AK-SYNC U19 : demande de correction de l'administration ─────
        if (entry.currentCorrectionRequest != null)
          _CorrectionBanner(feedback: entry.currentCorrectionRequest!),
        // ── AK-SYNC U21 : formulaire verrouillé / validé ─────────────────
        if (entry.selectedQuestion != null && entry.isCurrentFormLocked)
          _LockedBanner(
            status: entry.currentSyncStatus,
            onReload: () => _reloadFromServer(context, auth, entry),
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
        // SESSION 53 — Avertissement KOSAVE (orange) : données reçues serveur
        // mais non enregistrées en base (ISOKSAVEINDATABASE absent).
        if (entry.warningMessage != null)
          _MessageBanner(
            message: entry.warningMessage!,
            isError: false,
            isWarning: true,
            onDismiss: entry.clearWarning,
          ),
        // ── Violations de cohérence hors ligne ───────────────────────────
        // Source de vérité : moteur ThemeRuleEngine (DICO_REGLE_THEME).
        // Un seul panneau affiché — pas de doublon avec le moteur paire.
        // Spinner pendant l'évaluation manuelle (menu → Vérifier la cohérence).
        if (entry.isCheckingOffline)
          const LinearProgressIndicator(),
        if (entry.hasThemeCoherenceErrors)
          _ThemeCoherenceBanner(
            errors: entry.themeCoherenceErrors,
            onDismiss: entry.clearThemeCoherenceErrors,
          ),
        // ── Sélecteur de question (thème) ────────────────────────────────
        // Chips horizontales pour naviguer entre les thèmes du système éducatif
        if (entry.questions.isNotEmpty)
          _QuestionSelector(
            questions: entry.questions,
            selected: entry.selectedQuestion,
            onSelect: entry.selectQuestion,
            statusOf: entry.syncStatusForQuestion,  // AK-SYNC U05 : pastille d'état
          ),
        // ── FIX SESSION 66 issue #9 — Barre navigation Précédent / Suivant ─
        if (entry.questions.isNotEmpty && entry.selectedQuestion != null)
          _NavBar(
            questions:  entry.questions,
            selected:   entry.selectedQuestion!,
            hasUnsaved: entry.hasUnsavedChanges,
            isSaving:   entry.isSaving,
            onNavigate: (q) => _navigateTo(context, entry, q),
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
      disabledFields: entry.disabledFields,  // Fix #5 — questions conditionnelles
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
    // AK-SYNC U21 : formulaire verrouillé → on n'envoie pas, on propose le rechargement
    if (entry.isCurrentFormLocked) {
      final reload = await showDialog<bool>(
        context: context,
        builder: (ctx) => AlertDialog(
          title: const Row(children: [
            Icon(Icons.lock_outline, color: Colors.deepPurple),
            SizedBox(width: 8),
            Text('Formulaire verrouillé'),
          ]),
          content: const Text(
              'Ce formulaire a été validé/verrouillé par l\'administration. '
              'Il ne peut plus être modifié depuis l\'application.\n\n'
              'Voulez-vous recharger les données officielles du serveur ?'),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Fermer')),
            ElevatedButton.icon(
                onPressed: () => Navigator.pop(ctx, true),
                icon: const Icon(Icons.refresh),
                label: const Text('Recharger')),
          ],
        ),
      );
      if (reload == true && mounted) await entry.reloadFromServer(user: auth.user!);
      return;
    }
    // Validation — ne bloque pas l'envoi (comme dans l'application originale)
    entry.validateAll();

    // AK-SYNC U21 : détection de conflit (le serveur a une version plus récente
    // que notre dernier envoi, écrite depuis le web ou un autre appareil).
    // Fail-open : null si WS absent / hors ligne → envoi normal.
    final conflictAt = await entry.detectServerConflict(user: auth.user!);
    if (!mounted) return;
    if (conflictAt != null) {
      final choice = await showDialog<String>(
        context: context,
        builder: (ctx) => AlertDialog(
          title: const Row(children: [
            Icon(Icons.merge_type, color: Colors.orange),
            SizedBox(width: 8),
            Text('Conflit de version'),
          ]),
          content: Text(
              'Le serveur a reçu une version plus récente de ce formulaire '
              '(${_fmtDate(conflictAt)}), postérieure à votre dernier envoi.\n\n'
              '• Recharger : remplace vos données locales par celles du serveur.\n'
              '• Écraser : envoie vos données locales et remplace celles du serveur.'),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx, 'cancel'), child: const Text('Annuler')),
            OutlinedButton.icon(
                onPressed: () => Navigator.pop(ctx, 'reload'),
                icon: const Icon(Icons.download_outlined),
                label: const Text('Recharger')),
            ElevatedButton.icon(
                onPressed: () => Navigator.pop(ctx, 'overwrite'),
                icon: const Icon(Icons.cloud_upload_outlined),
                label: const Text('Écraser')),
          ],
        ),
      );
      if (!mounted || choice == null || choice == 'cancel') return;
      if (choice == 'reload') {
        await entry.reloadFromServer(user: auth.user!);
        return;
      }
      // 'overwrite' → on poursuit l'envoi normalement
    }

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
      // Seul point d'entrée du contrôle : le déclenchement automatique
      // (debounce) a été désactivé — l'agent lance le contrôle explicitement.
      entry.checkCoherenceOffline();
    } else if (value == 'send_all') {
      _sendAllForms(context, auth, entry);
    } else if (value == 'send_pending') {
      _sendPendingForms(context, auth, entry);
    } else if (value == 'sync_refresh') {
      _refreshSyncFromServer(context, auth, entry);
    }
  }

  /// AK-SYNC U17 : réconciliation manuelle avec le serveur (fail-open).
  Future<void> _refreshSyncFromServer(BuildContext context, AuthProvider auth,
      DataEntryProvider entry) async {
    if (auth.user == null) return;
    final changed = await entry.reconcileWithServer(
      user: auth.user!,
      idCamp: widget.campaign.idCamp,
      idSystem: widget.idSystem,
    );
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(changed > 0
          ? 'État de synchronisation actualisé ($changed formulaire(s) mis à jour)'
          : 'État de synchronisation actualisé'),
    ));
  }

  /// AK-SYNC U07 : envoie uniquement les formulaires en attente / en échec
  /// de cet établissement, puis affiche le résultat nominatif (U06).
  Future<void> _sendPendingForms(BuildContext context, AuthProvider auth,
      DataEntryProvider entry) async {
    if (auth.user == null) return;
    final pending = await entry.listPendingForms(widget.campaign.idCamp,
        idEtab: widget.school.idEtab);
    if (!mounted) return;
    if (pending.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Aucun formulaire en attente d\'envoi pour cet établissement')));
      return;
    }
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Row(children: [
          Icon(Icons.outbox_outlined),
          SizedBox(width: 8),
          Text('Formulaires en attente'),
        ]),
        content: SizedBox(
          width: double.maxFinite,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('${pending.length} formulaire(s) à envoyer :',
                  style: const TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              Flexible(
                child: ListView(
                  shrinkWrap: true,
                  children: pending.map((f) {
                    final q = entry.questions.where((x) => x.idQst == f.idQst).toList();
                    final lib = q.isNotEmpty ? q.first.libQst : 'Thème ${f.idQst}';
                    return ListTile(
                      dense: true,
                      leading: Icon(syncStatusIcon(f.status), color: syncStatusColor(f.status), size: 18),
                      title: Text(lib + (f.idFilter.isEmpty ? '' : ' [${f.idFilter}]'),
                          style: const TextStyle(fontSize: 13)),
                      subtitle: f.lastError == null ? null
                          : Text(f.lastError!, style: const TextStyle(fontSize: 11), maxLines: 2,
                              overflow: TextOverflow.ellipsis),
                    );
                  }).toList(),
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          ElevatedButton.icon(
              onPressed: () => Navigator.pop(ctx, true),
              icon: const Icon(Icons.cloud_upload_outlined),
              label: const Text('Envoyer')),
        ],
      ),
    );
    if (confirm != true || !mounted) return;

    final total = pending.length;
    final progressNotifier = ValueNotifier<int>(0);
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => ValueListenableBuilder<int>(
        valueListenable: progressNotifier,
        builder: (_, progress, __) => AlertDialog(
          title: const Text('Envoi en cours…'),
          content: Column(mainAxisSize: MainAxisSize.min, children: [
            LinearProgressIndicator(value: total > 0 ? progress / total : 0),
            const SizedBox(height: 12),
            Text('$progress / $total formulaire(s)'),
          ]),
        ),
      ),
    );
    final outcomes = await entry.sendPendingForms(
      user: auth.user!,
      idCamp: widget.campaign.idCamp,
      idSystem: widget.idSystem,
      idEtab: widget.school.idEtab,
      onProgress: (sent, _) => progressNotifier.value = sent,
    );
    if (mounted) Navigator.of(context, rootNavigator: true).pop();
    progressNotifier.dispose();
    if (!mounted) return;
    await _showBatchResult(context, auth, entry, outcomes);
  }

  /// AK-SYNC U06 : dialogue de résultat d'un envoi groupé, avec la liste
  /// nominative des formulaires en échec et un bouton « Réessayer les échecs »
  /// (widget partagé : widgets/sync/sync_widgets.dart).
  Future<void> _showBatchResult(BuildContext context, AuthProvider auth,
      DataEntryProvider entry, List<SendOutcome> outcomes) async {
    await showBatchResultDialog(
      context,
      outcomes: outcomes,
      providerError: entry.error,
      describe: (o) => _describeOutcome(entry, o),
      onRetry: auth.user == null
          ? null
          : () => entry.retryFailedOutcomes(
                user: auth.user!,
                idCamp: widget.campaign.idCamp,
                idSystem: widget.idSystem,
              ),
    );
  }

  /// Libellé nominatif d'un résultat (thème résolu via la liste des questions).
  String _describeOutcome(DataEntryProvider entry, SendOutcome o) {
    final q = entry.questions.where((x) => x.idQst == o.idQst).toList();
    final lib = (o.libQst ?? '').isNotEmpty
        ? o.libQst!
        : (q.isNotEmpty ? q.first.libQst : 'Thème ${o.idQst}');
    final f = (o.idFilter ?? '').isEmpty ? '' : ' [${o.idFilter}]';
    final m = (o.message ?? '').isEmpty ? '' : ' — ${o.message}';
    return '$lib$f : ${o.label}$m';
  }

  static String _fmtDate(DateTime d) {
    String two(int n) => n < 10 ? '0$n' : '$n';
    return '${two(d.day)}/${two(d.month)}/${d.year} ${two(d.hour)}:${two(d.minute)}';
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
    await entry.sendAllFormsForSchool(
      user: auth.user!,
      onProgress: (sent, tot) {
        progressNotifier.value = sent;
      },
    );

    // Ferme le dialogue de progression
    if (mounted) Navigator.of(context, rootNavigator: true).pop();
    progressNotifier.dispose();

    if (!mounted) return;

    // AK-SYNC U06 : résumé nominatif + « Réessayer les échecs »
    await _showBatchResult(context, auth, entry, entry.lastBatchOutcomes);
  }

  /// Appelé par [DynamicFormWidget] quand l'utilisateur ajoute une ligne
  /// dans un formulaire de type grille.
  /// Le widget gère l'ajout en interne via le pont JavaScript ;
  /// le provider peut aussi suivre les lignes supplémentaires si nécessaire.
  void _onAddGridRow(String tableId) {
    // DynamicFormWidget gère l'ajout de ligne en interne ;
    // le provider peut traquer les lignes supplémentaires si besoin.
  }

  // ── FIX SESSION 66 issue #9 — Navigation Précédent / Suivant ──────────────
  /// Navigue vers [target] en sauvegardant d'abord si des changements sont en attente.
  /// - Si `hasUnsavedChanges` : sauvegarde locale silencieuse avant changement
  /// - Puis sélectionne la question cible via le provider
  Future<void> _navigateTo(
      BuildContext context, DataEntryProvider entry, Question target) async {
    if (entry.isSaving || entry.isSending) return;
    // Sauvegarde automatique si modifications en attente
    if (entry.hasUnsavedChanges) {
      final ok = await entry.saveLocally();
      if (!ok && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(entry.error ?? 'Erreur lors de la sauvegarde'),
          backgroundColor: Theme.of(context).colorScheme.error,
        ));
        // On navigue quand même pour ne pas bloquer l'utilisateur
      }
    }
    if (!mounted) return;
    await entry.selectQuestion(target);
  }
}

// ─── FIX SESSION 66 issue #9 — Barre de navigation Précédent / Suivant ────────
/// Barre compacte affichée sous le sélecteur de thème, permettant de naviguer
/// séquentiellement entre les formulaires dans l'ordre exact des chips.
///
/// Comportement :
///   - Bouton "Précédent" désactivé si le formulaire courant est le premier
///   - Bouton "Suivant"   désactivé si le formulaire courant est le dernier
///   - Le centre affiche "N / total" (numérotation 1-based)
///   - La navigation déclenche une sauvegarde locale automatique si
///     [hasUnsaved] est vrai (via [onNavigate])
class _NavBar extends StatelessWidget {
  const _NavBar({
    required this.questions,
    required this.selected,
    required this.hasUnsaved,
    required this.isSaving,
    required this.onNavigate,
  });
  final List<Question> questions;
  final Question selected;
  final bool hasUnsaved;
  final bool isSaving;
  final Future<void> Function(Question) onNavigate;

  @override
  Widget build(BuildContext context) {
    final idx   = questions.indexWhere((q) => q.idQst == selected.idQst);
    final total = questions.length;
    final hasPrev = idx > 0;
    final hasNext = idx < total - 1;

    const Color navBg   = Color(0xFFF5F5F5);
    const Color btnColor = Color(0xFF1565C0);

    return Container(
      color: navBg,
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      child: Row(
        children: [
          // ── Bouton Précédent ──────────────────────────────────────────
          _NavButton(
            icon: Icons.chevron_left_rounded,
            label: 'Préc.',
            enabled: hasPrev && !isSaving,
            color: btnColor,
            onTap: hasPrev && !isSaving
                ? () => onNavigate(questions[idx - 1])
                : null,
          ),
          // ── Indicateur de position ────────────────────────────────────
          Expanded(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  '${idx + 1} / $total',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 11,
                    color: Colors.black54,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                if (hasUnsaved)
                  const Text(
                    '● modif. en attente',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 9,
                      color: Color(0xFF2E7D32),
                      fontStyle: FontStyle.italic,
                    ),
                  ),
              ],
            ),
          ),
          // ── Bouton Suivant ────────────────────────────────────────────
          _NavButton(
            icon: Icons.chevron_right_rounded,
            label: 'Suiv.',
            enabled: hasNext && !isSaving,
            color: btnColor,
            onTap: hasNext && !isSaving
                ? () => onNavigate(questions[idx + 1])
                : null,
            iconOnRight: true,
          ),
        ],
      ),
    );
  }
}

/// Bouton compact pour la barre de navigation Précédent / Suivant.
class _NavButton extends StatelessWidget {
  const _NavButton({
    required this.icon,
    required this.label,
    required this.enabled,
    required this.color,
    this.onTap,
    this.iconOnRight = false,
  });
  final IconData icon;
  final String label;
  final bool enabled;
  final Color color;
  final VoidCallback? onTap;
  final bool iconOnRight;

  @override
  Widget build(BuildContext context) {
    final effectiveColor = enabled ? color : Colors.grey.shade400;
    final children = [
      Icon(icon, size: 20, color: effectiveColor),
      const SizedBox(width: 2),
      Text(
        label,
        style: TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.bold,
          color: effectiveColor,
        ),
      ),
    ];
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(6),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: iconOnRight ? children.reversed.toList() : children,
        ),
      ),
    );
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
///
/// Fix #4 (issue #12) — S67 :
///   Ancienne implémentation StatelessWidget : SingleChildScrollView sans
///   ScrollController → l'offset revenait à 0 à chaque rebuild déclenché par
///   notifyListeners() du Provider (sauvegarde, changement de données…).
///
///   Nouvelle implémentation StatefulWidget :
///   • ScrollController persistant _scrollCtrl — survit aux rebuilds.
///   • GlobalKey sur chaque chip — permet de localiser le chip actif
///     dans la liste et d'appeler Scrollable.ensureVisible().
///   • didUpdateWidget : si la question sélectionnée change, on post-frame
///     un ensureVisible vers le chip actif (animation 300 ms).
///   • Si seule la liste des questions change (rare) on recalcule les clés
///     et on rescrolle vers le chip actif.
class _QuestionSelector extends StatefulWidget {
  const _QuestionSelector({
    required this.questions,
    required this.selected,
    required this.onSelect,
    this.statusOf,
  });
  final List<Question> questions;
  final Question? selected;
  final Future<void> Function(Question) onSelect;
  /// AK-SYNC U05 : état de sync par thème (null = jamais saisi → pas de pastille).
  final SyncStatus? Function(String idQst)? statusOf;

  @override
  State<_QuestionSelector> createState() => _QuestionSelectorState();
}

class _QuestionSelectorState extends State<_QuestionSelector> {
  /// Contrôleur de scroll persistant — survit aux rebuilds du parent.
  final ScrollController _scrollCtrl = ScrollController();

  /// Une GlobalKey par chip pour pouvoir appeler ensureVisible.
  /// Recréée uniquement si la liste des questions change (longueur ou ids).
  late List<GlobalKey> _chipKeys;

  // ── Initialisation ────────────────────────────────────────────────────────
  @override
  void initState() {
    super.initState();
    _chipKeys = _buildKeys(widget.questions);
    // Premier scroll vers le chip actif après le premier rendu.
    WidgetsBinding.instance.addPostFrameCallback((_) => _scrollToSelected());
  }

  // ── Mise à jour ───────────────────────────────────────────────────────────
  @override
  void didUpdateWidget(_QuestionSelector old) {
    super.didUpdateWidget(old);

    // Si la liste des questions a changé (longueur ou identifiants),
    // on recrée les clés pour éviter des conflits de GlobalKey.
    if (_listChanged(old.questions, widget.questions)) {
      _chipKeys = _buildKeys(widget.questions);
    }

    // Si la question sélectionnée a changé (navigation, tap chip…),
    // on déclenche un scroll vers le nouveau chip actif.
    if (old.selected?.idQst != widget.selected?.idQst) {
      WidgetsBinding.instance.addPostFrameCallback((_) => _scrollToSelected());
    }
  }

  // ── Nettoyage ─────────────────────────────────────────────────────────────
  @override
  void dispose() {
    _scrollCtrl.dispose();
    super.dispose();
  }

  // ── Helpers ───────────────────────────────────────────────────────────────

  /// Crée une nouvelle liste de GlobalKey de même longueur que [questions].
  List<GlobalKey> _buildKeys(List<Question> questions) =>
      List.generate(questions.length, (_) => GlobalKey());

  /// Retourne true si [a] et [b] diffèrent en longueur ou en idQst.
  bool _listChanged(List<Question> a, List<Question> b) {
    if (a.length != b.length) return true;
    for (var i = 0; i < a.length; i++) {
      if (a[i].idQst != b[i].idQst) return true;
    }
    return false;
  }

  /// Fait défiler la barre pour rendre le chip actif visible.
  /// Utilise Scrollable.ensureVisible pour une animation fluide
  /// et un alignement centré (alignment: 0.5).
  void _scrollToSelected() {
    if (!mounted) return;
    final idx = widget.questions
        .indexWhere((q) => q.idQst == widget.selected?.idQst);
    if (idx < 0 || idx >= _chipKeys.length) return;
    final ctx = _chipKeys[idx].currentContext;
    if (ctx != null) {
      Scrollable.ensureVisible(
        ctx,
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
        alignment: 0.5, // centre le chip dans la fenêtre visible
      );
    }
  }

  // ── Build ──────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Container(
      color: Theme.of(context).colorScheme.surfaceContainerHighest,
      child: SingleChildScrollView(
        controller: _scrollCtrl, // ← ScrollController persistant (Fix #4)
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
        child: Row(
          children: List.generate(widget.questions.length, (i) {
            final q = widget.questions[i];
            final isSelected = widget.selected?.idQst == q.idQst;
            final SyncStatus? st = widget.statusOf?.call(q.idQst);
            return Padding(
              // GlobalKey sur chaque chip pour ensureVisible (Fix #4)
              key: _chipKeys[i],
              padding: const EdgeInsets.only(right: 6),
              child: ChoiceChip(
                // AK-SYNC U05 : pastille colorée = état de synchronisation du thème
                avatar: st == null
                    ? null
                    : Tooltip(
                        message: st.label,
                        child: Icon(syncStatusIcon(st), size: 16, color: syncStatusColor(st)),
                      ),
                label: Text(
                  q.libQst,
                  style: TextStyle(
                    fontSize: 13,
                    color: isSelected ? Colors.white : Colors.black87,
                  ),
                ),
                selected: isSelected,
                selectedColor: Theme.of(context).colorScheme.primary,
                backgroundColor: Colors.grey.shade200,
                side: BorderSide(color: Colors.grey.shade400),
                onSelected: (_) => widget.onSelect(q),
              ),
            );
          }),
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

// ─── Bannière de violations de cohérence hors ligne ──────────────────────────
/// Bannière affichant les violations détectées par [ThemeRuleEngine]
/// (moteur unique piloté par les métadonnées DICO_REGLE_THEME — source de vérité).
///
/// Présentation :
///   - Style identique au dialog serveur "Contrôle de cohérence"
///   - Hauteur maximale contrainte + scroll interne → zéro overflow
///   - Un seul panneau orange, libellé standard "Contrôle local"
class _ThemeCoherenceBanner extends StatelessWidget {
  const _ThemeCoherenceBanner({
    required this.errors,
    required this.onDismiss,
  });
  final List<ThemeCoherenceError> errors;
  final VoidCallback onDismiss;

  @override
  Widget build(BuildContext context) {
    // Hauteur max = 40% de l'écran → le banner ne prend jamais plus de place
    // que nécessaire et ne déborde jamais (overflow corrigé).
    final maxH = MediaQuery.of(context).size.height * 0.40;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      constraints: BoxConstraints(maxHeight: maxH),
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
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── En-tête fixe (hors scroll) ────────────────────────────────────
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
                            fontSize: 14, fontWeight: FontWeight.bold),
                      ),
                      Text(
                        'Contrôle local — non encore envoyé au serveur',
                        style: TextStyle(
                            fontSize: 10,
                            color: Colors.grey,
                            fontStyle: FontStyle.italic),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close, size: 16),
                  onPressed: onDismiss,
                  padding: EdgeInsets.zero,
                  constraints:
                      const BoxConstraints(minWidth: 28, minHeight: 28),
                  tooltip: 'Fermer',
                ),
              ],
            ),
          ),
          // ── Corps scrollable ──────────────────────────────────────────────
          Flexible(
            child: SingleChildScrollView(
              padding: const EdgeInsets.only(bottom: 8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Compteur
                  Padding(
                    padding: const EdgeInsets.fromLTRB(12, 8, 12, 4),
                    child: Text(
                      '${errors.length} incohérence(s) détectée(s) :',
                      style: const TextStyle(
                          fontSize: 13, fontWeight: FontWeight.w600),
                    ),
                  ),
                  // Liste des violations
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
                                    : 'Règle ${e.idRegle} — incohérence'
                                        '${e.value1 != null && e.value2 != null ? ' (${e.value1!.toStringAsFixed(0)} ${e.critere ?? "≠"} ${e.value2!.toStringAsFixed(0)})' : ""}',
                                style: const TextStyle(fontSize: 12),
                              ),
                            ),
                          ],
                        ),
                      )),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// Bannière des violations CoherenceEvaluator (moteur paire).
/// DÉSACTIVÉE — remplacée par [_ThemeCoherenceBanner] (moteur DICO_REGLE_THEME).
/// Conservée pour compatibilité ascendante si le moteur paire est réactivé.
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

// ─── AK-SYNC U05/U08 — Ligne d'état de synchronisation du formulaire courant ──
/// Affiche sous le bandeau : état (pastille + libellé), dernier envoi réussi,
/// dernière erreur, et le contenu du dernier accusé de réception serveur
/// (compteurs I/U/D/=, doublon ignoré, garde anti-suppression…).
class _SyncStatusLine extends StatelessWidget {
  const _SyncStatusLine({required this.entry});
  final DataEntryProvider entry;

  @override
  Widget build(BuildContext context) {
    final st  = entry.currentSyncStatus;
    final ack = entry.lastAck;
    if (st == null && ack == null) {
      // Jamais sauvegardé / envoyé : ligne discrète
      return Container(
        width: double.infinity,
        color: Colors.grey.shade100,
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
        child: Row(children: const [
          Icon(Icons.radio_button_unchecked, size: 14, color: Colors.grey),
          SizedBox(width: 6),
          Text('Aucune donnée synchronisée pour ce formulaire',
              style: TextStyle(fontSize: 11.5, color: Colors.grey)),
        ]),
      );
    }
    final status = st?.status ?? SyncStatus.sent;
    final color  = syncStatusColor(status);
    final parts = <String>[];
    if (st?.lastSuccessAt != null) parts.add('dernier envoi OK : ${fmtSyncDate(st!.lastSuccessAt)}');
    if (st != null && st.attempts > 0 && status.needsSend) parts.add('${st.attempts} tentative(s)');
    if (st?.adminStatus != null && st!.adminStatus!.isNotEmpty && st.adminStatus != 'RECU') {
      parts.add('admin : ${st.adminStatus}');
    }
    final ackText = ack != null ? ack.summary() : null;
    final err = st?.lastError;

    return Container(
      width: double.infinity,
      color: color.withOpacity(0.08),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            Icon(syncStatusIcon(status), size: 15, color: color),
            const SizedBox(width: 6),
            Text(status.label,
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: color)),
            if (parts.isNotEmpty) ...[
              const SizedBox(width: 8),
              Expanded(
                child: Text(parts.join(' · '),
                    style: const TextStyle(fontSize: 11, color: Colors.black54),
                    overflow: TextOverflow.ellipsis),
              ),
            ],
          ]),
          if (ackText != null && ackText.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 2, left: 21),
              child: Text('Serveur : $ackText',
                  style: const TextStyle(fontSize: 11, color: Colors.black87)),
            ),
          if (err != null && err.isNotEmpty && status.isProblem)
            Padding(
              padding: const EdgeInsets.only(top: 2, left: 21),
              child: Text(err,
                  style: TextStyle(fontSize: 11, color: color),
                  maxLines: 2, overflow: TextOverflow.ellipsis),
            ),
        ],
      ),
    );
  }
}

// ─── AK-SYNC U19 — Bannière « demande de correction » de l'administration ─────
class _CorrectionBanner extends StatelessWidget {
  const _CorrectionBanner({required this.feedback});
  final SyncFeedback feedback;

  @override
  Widget build(BuildContext context) {
    const Color bg = Color(0xFFFFEBEE);
    const Color fg = Color(0xFFB71C1C);
    return Container(
      width: double.infinity,
      color: bg,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.assignment_late_outlined, color: fg, size: 20),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Correction demandée par l\'administration',
                    style: TextStyle(fontWeight: FontWeight.bold, color: fg, fontSize: 13)),
                if (feedback.commentaire.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 2),
                    child: Text(feedback.commentaire,
                        style: const TextStyle(color: fg, fontSize: 12.5)),
                  ),
                Padding(
                  padding: const EdgeInsets.only(top: 2),
                  child: Text(
                    '${feedback.admin.isNotEmpty ? feedback.admin : 'admin'} · ${feedback.date}'
                    '${feedback.idQst == null ? ' · tout l\'établissement' : ''}',
                    style: const TextStyle(color: Colors.black54, fontSize: 11),
                  ),
                ),
                const Padding(
                  padding: EdgeInsets.only(top: 2),
                  child: Text('Corrigez la saisie puis renvoyez le formulaire : la demande sera levée automatiquement.',
                      style: TextStyle(color: Colors.black87, fontSize: 11.5)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─── AK-SYNC U21 — Bannière « formulaire verrouillé / validé » ────────────────
class _LockedBanner extends StatelessWidget {
  const _LockedBanner({required this.status, required this.onReload});
  final FormSyncStatus? status;
  final VoidCallback onReload;

  @override
  Widget build(BuildContext context) {
    const Color bg = Color(0xFFEDE7F6);
    const Color fg = Color(0xFF4A148C);
    final admin = status?.adminStatus;
    final comment = status?.adminComment;
    return Container(
      width: double.infinity,
      color: bg,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          const Icon(Icons.lock_outline, color: fg, size: 20),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  admin == 'VALIDE'
                      ? 'Formulaire validé par l\'administration'
                      : 'Formulaire verrouillé par l\'administration',
                  style: const TextStyle(fontWeight: FontWeight.bold, color: fg, fontSize: 13),
                ),
                Text(
                  (comment != null && comment.isNotEmpty)
                      ? comment
                      : 'Les modifications locales ne peuvent plus être envoyées. '
                        'Rechargez les données officielles du serveur.',
                  style: const TextStyle(color: Colors.black87, fontSize: 11.5),
                ),
              ],
            ),
          ),
          TextButton.icon(
            onPressed: onReload,
            icon: const Icon(Icons.refresh, size: 16, color: fg),
            label: const Text('Recharger', style: TextStyle(color: fg, fontSize: 12)),
          ),
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
    this.isWarning = false,  // SESSION 53 : orange pour KOSAVE
  });
  final String message;
  final bool isError;
  final bool isWarning;  // SESSION 53
  final VoidCallback onDismiss;

  @override
  Widget build(BuildContext context) {
    // SESSION 53 : couleur orange pour avertissement KOSAVE
    final color = isError
        ? Theme.of(context).colorScheme.errorContainer
        : isWarning
            ? Colors.orange.shade100
            : Theme.of(context).colorScheme.secondaryContainer;
    final textColor = isError
        ? Theme.of(context).colorScheme.onErrorContainer
        : isWarning
            ? Colors.orange.shade900
            : Theme.of(context).colorScheme.onSecondaryContainer;
    final icon = isError
        ? Icons.error_outline
        : isWarning
            ? Icons.warning_amber_rounded
            : Icons.check_circle_outline;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      color: color,
      child: Row(
        children: [
          Icon(icon, color: textColor, size: 18),
          const SizedBox(width: 8),
          Expanded(
              child: Text(message, style: TextStyle(color: textColor))),
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
