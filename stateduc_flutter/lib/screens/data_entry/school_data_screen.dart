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
              : 'Envoyer les données au serveur',
          child: ElevatedButton.icon(
            icon: Icon(
              sending ? Icons.hourglass_top_rounded : Icons.cloud_upload_rounded,
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
              backgroundColor: sending ? disabledBg : sendBg,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 0),
              minimumSize: const Size(80, 36),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
              elevation: 4,
            ),
            onPressed: sending
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
        // Moteur paire (CoherenceEvaluator) — règles de cohérence classiques
        if (entry.hasOfflineCoherenceErrors)
          _OfflineCoherenceBanner(
            errors: entry.offlineCoherenceErrors,
            onDismiss: entry.clearMessages,
          ),
        // Moteur générique (ThemeRuleEngine) — règles DICO_REGLE_THEME
        if (entry.hasThemeCoherenceErrors)
          _ThemeCoherenceBanner(
            errors: entry.themeCoherenceErrors,
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
  });
  final List<Question> questions;
  final Question? selected;
  final Future<void> Function(Question) onSelect;

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
            return Padding(
              // GlobalKey sur chaque chip pour ensureVisible (Fix #4)
              key: _chipKeys[i],
              padding: const EdgeInsets.only(right: 6),
              child: ChoiceChip(
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

// ─── Bannière de violations de cohérence hors ligne ─────────────────────────
// ─── Bannière DICO_REGLE_THEME (moteur générique ThemeRuleEngine) ────────────
/// Bannière affichant les violations détectées par [ThemeRuleEngine]
/// (moteur générique piloté par les métadonnées DICO_REGLE_THEME).
///
/// Chaque violation affiche le message associé à la règle.
/// Distincte visuellement de [_OfflineCoherenceBanner] (bordure rouge foncé
/// vs orange) pour indiquer qu'il s'agit du moteur de règles métier.
class _ThemeCoherenceBanner extends StatelessWidget {
  const _ThemeCoherenceBanner({
    required this.errors,
    required this.onDismiss,
  });
  final List<ThemeCoherenceError> errors;
  final VoidCallback onDismiss;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: Colors.red.shade400, width: 1.5),
        borderRadius: BorderRadius.circular(8),
        boxShadow: [
          BoxShadow(
            color: Colors.red.shade50,
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          // ── En-tête ──────────────────────────────────────────────────────
          Container(
            padding: const EdgeInsets.fromLTRB(12, 10, 8, 10),
            decoration: BoxDecoration(
              color: Colors.red.shade50,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(7)),
            ),
            child: Row(
              children: [
                const Icon(Icons.rule_folder_outlined,
                    color: Colors.red, size: 20),
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
                        'Règles métier — contrôle local hors ligne',
                        style: TextStyle(
                          fontSize: 10,
                          color: Colors.grey,
                          fontStyle: FontStyle.italic,
                        ),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close, size: 16),
                  onPressed: onDismiss,
                  padding: EdgeInsets.zero,
                  constraints: const BoxConstraints(minWidth: 28, minHeight: 28),
                  tooltip: 'Fermer',
                ),
              ],
            ),
          ),
          // ── Compteur ─────────────────────────────────────────────────────
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
          // ── Liste des violations ──────────────────────────────────────────
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
                            : 'Règle ${e.idRegle} — incohérence détectée'
                                '${e.value1 != null && e.value2 != null
                                    ? ' (${e.value1!.toStringAsFixed(0)} ${e.critere ?? "≠"} ${e.value2!.toStringAsFixed(0)})'
                                    : ""}',
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
