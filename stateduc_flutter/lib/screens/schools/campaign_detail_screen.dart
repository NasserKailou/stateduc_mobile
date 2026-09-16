import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/campaign_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/data_entry_provider.dart';
import '../../models/campaign.dart';
import '../../models/regroup.dart';
import '../../models/school.dart';
import '../../models/education_system.dart';
import '../../models/sync_status.dart';
import '../../services/database_service.dart';
import '../../widgets/sync/sync_widgets.dart';
import '../data_entry/school_data_screen.dart';
import 'pending_forms_screen.dart';

/// CampaignDetailScreen — sélecteur de système éducatif + navigation hiérarchique
/// dans les regroupements + liste des établissements.
///
/// Flux de navigation (réplique page_camp.js) :
///   displaySystems()             → puces de sélection du système éducatif
///   displayRegroups(null)        → liste des regroupements racine
///   displayRegroups(id)          → sous-regroupements (drill-down)
///   displayFinalRegroupEtabs()   → liste des établissements (nœud feuille)
///
/// Un fil d'Ariane est affiché en haut de l'écran dès qu'un système est
/// sélectionné, permettant de remonter à n'importe quel niveau de la hiérarchie.
///
/// Widgets privés :
///   [_BreadcrumbBar]   — barre de navigation avec système + regroupements
///   [_SystemChip]      — puce de sélection d'un système éducatif
///   [_RegroupTile]     — tuile de navigation dans un regroupement
///   [_SchoolTile]      — tuile représentant un établissement
///   [_UnsentDataBanner] — bannière « N formulaires en attente » + envoi ciblé (AK-SYNC U05/U07)
class CampaignDetailScreen extends StatefulWidget {
  const CampaignDetailScreen({super.key, required this.campaign});
  final Campaign campaign;

  @override
  State<CampaignDetailScreen> createState() => _CampaignDetailScreenState();
}

class _CampaignDetailScreenState extends State<CampaignDetailScreen> {
  Campaign get campaign => widget.campaign;

  // ─── AK-SYNC (U05/U07/U17/U19) — état de synchronisation agrégé ────────────
  /// Résumés par établissement (lecture SQLite form_sync_status).
  Map<String, SchoolSyncSummary> _summaries = {};
  /// Établissements avec une demande de correction admin active (U19).
  Set<String> _correctionEtabs = {};
  /// Nombre total de formulaires en attente / en échec pour la campagne.
  int _pendingCount = 0;
  /// Système pour lequel la réconciliation serveur a déjà été faite (1 fois/ouverture).
  String? _reconciledForSystem;
  bool _syncRefreshing = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _refreshSyncState());
  }

  /// Recharge les agrégats locaux (rapide, SQLite). Appelé à l'ouverture,
  /// au retour d'un écran de saisie et après chaque envoi groupé.
  Future<void> _refreshSyncState() async {
    if (!mounted) return;
    final entry = context.read<DataEntryProvider>();
    final auth  = context.read<AuthProvider>();
    final annee = auth.effectiveYearCode.isNotEmpty
        ? auth.effectiveYearCode
        : (auth.user?.codeyear ?? campaign.idYear ?? '');
    try {
      final db = DatabaseService();
      final sums = await db.getSchoolSyncSummaries(campaign.idCamp, codeTypeAnnee: annee);
      final fbs  = await db.getSyncFeedback(campaign.idCamp, codeTypeAnnee: annee);
      final pend = await entry.countPendingForms(campaign.idCamp);
      if (!mounted) return;
      setState(() {
        _summaries = sums;
        _correctionEtabs = fbs.where((f) => f.isCorrection).map((f) => f.idEtab).toSet();
        _pendingCount = pend;
      });
    } catch (e) {
      debugPrint('[CampaignDetail] _refreshSyncState: $e');
    }
  }

  /// U17 : réconciliation avec le serveur (fail-open, silencieuse) — une fois
  /// par système sélectionné, puis à la demande via le bouton « Actualiser ».
  Future<void> _reconcile(String idSystem, {bool force = false}) async {
    if (!mounted) return;
    if (!force && _reconciledForSystem == idSystem) return;
    _reconciledForSystem = idSystem;
    final auth  = context.read<AuthProvider>();
    final entry = context.read<DataEntryProvider>();
    if (auth.user == null) return;
    setState(() => _syncRefreshing = true);
    int changed = 0;
    try {
      changed = await entry.reconcileWithServer(
          user: auth.user!, idCamp: campaign.idCamp, idSystem: idSystem);
    } catch (_) {}
    if (!mounted) return;
    setState(() => _syncRefreshing = false);
    await _refreshSyncState();
    if (force && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(changed > 0
            ? 'État de synchronisation actualisé ($changed formulaire(s) mis à jour)'
            : 'État de synchronisation actualisé'),
        duration: const Duration(seconds: 2),
      ));
    }
  }

  @override
  Widget build(BuildContext context) {
    // fix AK-F-01 : détecter si cette campagne est l'année active serveur
    final auth = context.read<AuthProvider>();
    final serverCodeyear = auth.user?.codeyear ?? '';
    final isCurrentYear  = serverCodeyear.isNotEmpty &&
        campaign.idYear == serverCodeyear;

    return Consumer<CampaignProvider>(builder: (context, camps, _) {
      return Scaffold(
        appBar: AppBar(
          // fix AK-F-01 : titre avec indicateur d'année
          title: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(campaign.libCamp),
              if (campaign.libYear != null)
                Text(
                  campaign.libYear! + (isCurrentYear ? ' · ✓ Année active' : ''),
                  style: TextStyle(
                    fontSize: 12,
                    color: isCurrentYear
                        ? Theme.of(context).colorScheme.inversePrimary
                        : Theme.of(context).colorScheme.onSurfaceVariant,
                    fontWeight: isCurrentYear ? FontWeight.bold : FontWeight.normal,
                  ),
                ),
            ],
          ),
          // Affiche le fil d'Ariane sous le titre dès qu'un système est sélectionné
          bottom: camps.selectedSystem != null
              ? PreferredSize(
                  preferredSize: const Size.fromHeight(40),
                  child: _BreadcrumbBar(
                    system: camps.selectedSystem!,
                    breadcrumb: camps.regroupBreadcrumb,
                    onSystemTap: () => _backToSystems(context, camps),
                    onBreadcrumbTap: (index) =>
                        _navigateToLevel(context, camps, index),
                  ),
                )
              : null,
        ),
        body: _buildBody(context, camps),
      );
    });
  }

  /// Construit le corps selon l'état de navigation :
  ///   - Aucun système sélectionné → sélecteur de systèmes
  ///   - Navigation en cours → indicateur de chargement
  ///   - Niveau feuille (établissements) → liste d'établissements
  ///   - Listes vides après navigation → état vide / erreur
  ///   - Sinon → liste de regroupements (drill-down)
  Widget _buildBody(BuildContext context, CampaignProvider camps) {
    // Aucun système sélectionné → affiche les puces de sélection
    if (camps.selectedSystem == null) {
      return _buildSystemSelector(context, camps);
    }
    // Navigation en cours → indicateur de chargement (contrôlé par isNavigating)
    if (camps.isNavigating) {
      return const Center(child: CircularProgressIndicator());
    }
    // Système sélectionné, plus de regroupements, des établissements disponibles → liste
    if (camps.currentRegroups.isEmpty && camps.currentSchools.isNotEmpty) {
      // AK-SYNC U17 : réconciliation serveur (1 fois par système, hors build)
      final sysId = camps.selectedSystem!.idSystem;
      if (_reconciledForSystem != sysId) {
        WidgetsBinding.instance.addPostFrameCallback((_) => _reconcile(sysId));
      }
      return _buildSchoolList(context, camps);
    }
    // Les deux listes vides après navigation → état vide/erreur (pas de spinner infini)
    if (camps.currentRegroups.isEmpty && camps.currentSchools.isEmpty) {
      return _buildEmptyState(context, camps);
    }
    // Navigation dans la hiérarchie de regroupements
    return _buildRegroupList(context, camps);
  }

  // ─── Sélecteur de système éducatif ──────────────────────────────────────
  /// Affiche les systèmes éducatifs de la campagne sous forme de puces cliquables.
  /// Ex. : "Education de Base", "Enseignement Secondaire", "Alphabétisation"
  Widget _buildSystemSelector(
      BuildContext context, CampaignProvider camps) {
    if (camps.systems.isEmpty) {
      return const Center(
        child: Text('Aucun système éducatif disponible pour cette campagne'),
      );
    }
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Sélectionnez un système éducatif',
              style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 16),
          Wrap(
            spacing: 10,
            runSpacing: 10,
            children: camps.systems
                .map((s) => _SystemChip(
                      system: s,
                      onTap: () => camps.selectSystem(s),
                    ))
                .toList(),
          ),
        ],
      ),
    );
  }

  // ─── État vide (aucun regroupement ni établissement après navigation) ────
  /// Affiché quand une navigation aboutit à un résultat vide.
  /// Montre l'erreur éventuelle du provider ou un message générique.
  /// Propose un bouton "Revenir en arrière" si un fil d'Ariane existe.
  Widget _buildEmptyState(BuildContext context, CampaignProvider camps) {
    final errorMsg = camps.error;
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.folder_open_outlined,
                size: 56, color: Theme.of(context).colorScheme.outline),
            const SizedBox(height: 16),
            Text(
              errorMsg ?? 'Aucun établissement trouvé pour ce regroupement.',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: errorMsg != null
                        ? Theme.of(context).colorScheme.error
                        : Theme.of(context).colorScheme.onSurfaceVariant,
                  ),
            ),
            const SizedBox(height: 24),
            if (camps.regroupBreadcrumb.isNotEmpty)
              TextButton.icon(
                icon: const Icon(Icons.arrow_back),
                label: const Text('Revenir en arrière'),
                onPressed: () => camps.navigateUpRegroup(levelsUp: 1),
              ),
          ],
        ),
      ),
    );
  }

  // ─── Liste de regroupements (drill-down) ────────────────────────────────
  /// Affiche la liste des sous-regroupements du niveau courant.
  /// Chaque tuile appelle [CampaignProvider.navigateIntoRegroup] au tap.
  Widget _buildRegroupList(
      BuildContext context, CampaignProvider camps) {
    return ListView.builder(
      padding: const EdgeInsets.all(8),
      itemCount: camps.currentRegroups.length,
      itemBuilder: (context, i) {
        final r = camps.currentRegroups[i];
        return _RegroupTile(
          regroup: r,
          typeLabel: camps.regroupTypeLabel(r.idTypeRegp),
          onTap: () => camps.navigateIntoRegroup(r),
        );
      },
    );
  }

  // ─── Liste des établissements (nœud feuille) ─────────────────────────────
  /// Affiche la bannière de données non envoyées (placeholder) puis
  /// la liste des établissements du regroupement courant.
  Widget _buildSchoolList(
      BuildContext context, CampaignProvider camps) {
    final idSystem = camps.selectedSystem!.idSystem;
    return Column(
      children: [
        // ── Bouton d'envoi global pour tous les établissements ────────────
        // Permet d'envoyer en une seule opération tous les formulaires
        // de tous les établissements actuellement affichés.
        Padding(
          padding: const EdgeInsets.fromLTRB(12, 8, 12, 0),
          child: Consumer<DataEntryProvider>(
            builder: (ctx, entry, _) => Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: entry.isSending
                        ? null
                        : () => _sendAllCampaignForms(context, camps, idSystem),
                    icon: entry.isSending
                        ? const SizedBox(
                            width: 16,
                            height: 16,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Icon(Icons.cloud_sync_outlined),
                    label: Text(entry.isSending
                        ? 'Envoi en cours…'
                        : 'Envoyer tous les établissements'),
                    style: OutlinedButton.styleFrom(
                      minimumSize: const Size.fromHeight(44),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                // AK-SYNC U17 : actualiser l'état de sync depuis le serveur
                Tooltip(
                  message: 'Actualiser l\'état de synchronisation (serveur)',
                  child: SizedBox(
                    height: 44,
                    child: OutlinedButton(
                      onPressed: (entry.isSending || _syncRefreshing)
                          ? null
                          : () => _reconcile(idSystem, force: true),
                      style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(horizontal: 10)),
                      child: _syncRefreshing
                          ? const SizedBox(
                              width: 16, height: 16,
                              child: CircularProgressIndicator(strokeWidth: 2))
                          : const Icon(Icons.sync),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 4),
        // AK-SYNC U05/U07 : bannière « N formulaires en attente » + envoi ciblé
        _UnsentDataBanner(
          pendingCount: _pendingCount,
          isSending: context.watch<DataEntryProvider>().isSending,
          onSendPending: () => _sendPendingCampaignForms(context, idSystem),
          onShowList: () => _openPendingList(context, camps, idSystem),
        ),
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.all(8),
            itemCount: camps.currentSchools.length,
            itemBuilder: (context, i) {
              final s = camps.currentSchools[i];
              return _SchoolTile(
                school: s,
                summary: _summaries[s.idEtab],
                correctionRequested: _correctionEtabs.contains(s.idEtab),
                onTap: () => _openSchool(context, camps, s),
              );
            },
          ),
        ),
      ],
    );
  }

  // ═══════════════════════════════════════════════════════════════════════════
  /// Envoie tous les formulaires de tous les établissements de la campagne
  /// au serveur.
  ///
  /// Affiche une boîte de confirmation, puis un dialogue de progression,
  /// et enfin un résumé des résultats.
  // ═══════════════════════════════════════════════════════════════════════════
  Future<void> _sendAllCampaignForms(
      BuildContext context, CampaignProvider camps, String idSystem) async {
    final auth  = context.read<AuthProvider>();
    final entry = context.read<DataEntryProvider>();
    if (auth.user == null) return;

    // Confirmation
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.cloud_sync_outlined),
            SizedBox(width: 8),
            Text('Envoi global campagne'),
          ],
        ),
        content: const Text(
          'Envoyer TOUS les formulaires de TOUS les établissements '  
          'de cette campagne ?\n\n'
          'Cette opération peut prendre plusieurs minutes.\n'
          'Vérifiez que votre réseau est stable avant de continuer.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Annuler'),
          ),
          ElevatedButton.icon(
            onPressed: () => Navigator.pop(ctx, true),
            icon: const Icon(Icons.cloud_upload_outlined),
            label: const Text('Envoyer tout'),
          ),
        ],
      ),
    );
    if (confirm != true || !mounted) return;

    // Dialogue de progression
    final progressNotifier = ValueNotifier<(int, int)>((0, 0));
    if (!mounted) return;
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => ValueListenableBuilder<(int, int)>(
        valueListenable: progressNotifier,
        builder: (_, progress, __) {
          final sent  = progress.$1;
          final total = progress.$2;
          return AlertDialog(
            title: const Text('Envoi en cours…'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                LinearProgressIndicator(
                  value: total > 0 ? sent / total : null,
                ),
                const SizedBox(height: 12),
                Text(total > 0
                    ? '$sent / $total formulaire(s)'
                    : 'Préparation…'),
              ],
            ),
          );
        },
      ),
    );

    // Lancement de l'envoi global campagne
    await entry.sendAllFormsForCampaign(
      user:     auth.user!,
      idCamp:   campaign.idCamp,
      idSystem: idSystem,
      onProgress: (sent, total) {
        progressNotifier.value = (sent, total);
      },
    );

    // Ferme le dialogue de progression
    if (mounted) Navigator.of(context, rootNavigator: true).pop();
    progressNotifier.dispose();
    if (!mounted) return;

    // AK-SYNC U06 : résumé nominatif (établissement › thème : motif) + « Réessayer les échecs »
    await showBatchResultDialog(
      context,
      outcomes: entry.lastBatchOutcomes,
      providerError: entry.error,
      onRetry: () => entry.retryFailedOutcomes(
          user: auth.user!, idCamp: campaign.idCamp, idSystem: idSystem),
    );
    if (mounted) await _refreshSyncState();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  /// AK-SYNC U07 — Envoie UNIQUEMENT les formulaires en attente / en échec de
  /// la campagne (tous établissements), sans retoucher ceux déjà transmis.
  // ═══════════════════════════════════════════════════════════════════════════
  Future<void> _sendPendingCampaignForms(BuildContext context, String idSystem) async {
    final auth  = context.read<AuthProvider>();
    final entry = context.read<DataEntryProvider>();
    if (auth.user == null) return;
    if (DataEntryProvider.isAnySendRunning) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Un envoi est déjà en cours. Patientez puis réessayez.')));
      return;
    }
    final total = _pendingCount;
    if (total == 0) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Aucun formulaire en attente d\'envoi')));
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
        content: Text(
          'Envoyer uniquement les $total formulaire(s) en attente ou en échec '
          'de cette campagne ?\n\nLes formulaires déjà transmis ne seront pas renvoyés.',
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

    final progressNotifier = ValueNotifier<int>(0);
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => ValueListenableBuilder<int>(
        valueListenable: progressNotifier,
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
      idCamp: campaign.idCamp,
      idSystem: idSystem,
      onProgress: (sent, _) => progressNotifier.value = sent,
    );
    if (mounted) Navigator.of(context, rootNavigator: true).pop();
    progressNotifier.dispose();
    if (!mounted) return;
    await showBatchResultDialog(
      context,
      outcomes: outcomes,
      providerError: entry.error,
      onRetry: () => entry.retryFailedOutcomes(
          user: auth.user!, idCamp: campaign.idCamp, idSystem: idSystem),
    );
    if (mounted) await _refreshSyncState();
  }

  /// AK-SYNC U05 : ouvre l'écran listant nominativement les formulaires en attente.
  Future<void> _openPendingList(
      BuildContext context, CampaignProvider camps, String idSystem) async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => PendingFormsScreen(
          campaign: campaign,
          idSystem: idSystem,
          libSystem: camps.selectedSystem?.libSystem,
        ),
      ),
    );
    if (mounted) await _refreshSyncState();
  }

  /// Ouvre l'écran de saisie [SchoolDataScreen] pour un établissement.
  ///
  /// SESSION 53 FIX (v3) — Chaîne de localisation depuis la chaîne PRINCIPALE du serveur :
  ///
  ///   La hiérarchie est stockée dans schools.lib_localisation, remplie en deux temps :
  ///   1. Étape 4b (sync) : computeAndStoreLocalisations() — parcourt le graphe local
  ///      regroups (chaîne de l'agent). Peut retourner la mauvaise chaîne admin si le
  ///      pays a plusieurs chaînes (ex. Burundi : Chain 2 NEW = BUHUMUZA…).
  ///   2. Étape 6b (sync) : getEtabHierarchies() → endpoint etab_hier du serveur.
  ///      Utilise la chaîne PRINCIPALE (= $_SESSION['chaine'] de questionnaire.php).
  ///      Écrase la valeur de l'étape 4b avec la bonne valeur (ex. CANKUZO/CENDAJURU/…).
  ///
  ///   Résultat dans school.libLocalisation : chaîne officielle = identique au serveur.
  ///
  ///   Priorité d'affichage (school.bestLocalisation) :
  ///     1. school.libLocalisation — depuis étape 6b (chaîne primaire serveur) ou
  ///        étape 4b si serveur indisponible (offline)
  ///     2. school.libHierarchy   — reçu du serveur (optionnel, rarement présent)
  ///
  ///   Fallback si les deux sont absents :
  ///     3. breadcrumb de navigation (chemin parcouru par l'utilisateur)
  ///
  ///   NOTE internationale : l'endpoint etab_hier fonctionne pour tout pays —
  ///   il utilise la notion universelle de "chaîne primaire" (première par ORDRE
  ///   dans TYPE_CHAINE_REGROUPEMENT), identique à questionnaire.php.
  void _openSchool(BuildContext context, CampaignProvider camps, School school) {
    // Priorité 1 : lib_localisation pré-calculée à la synchronisation
    String? adminHierarchy = school.bestLocalisation;

    // Priorité 2 : breadcrumb de navigation (fallback si sync incomplète)
    if ((adminHierarchy == null || adminHierarchy.isEmpty) &&
        camps.regroupBreadcrumb.isNotEmpty) {
      adminHierarchy = camps.regroupBreadcrumb.map((r) => r.libRegp).join(' / ');
      debugPrint('[CampaignDetail] localisation: fallback breadcrumb → $adminHierarchy');
    }

    debugPrint('[CampaignDetail] localisation etab=${school.idEtab}: '
        'libLocalisation="${school.libLocalisation}", '
        'libHierarchy="${school.libHierarchy}", '
        'used="$adminHierarchy"');

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => SchoolDataScreen(
          campaign: campaign,
          school: school.copyWith(libHierarchy: adminHierarchy),
          idSystem: camps.selectedSystem!.idSystem,
          libSystem: camps.selectedSystem?.libSystem,
        ),
      ),
    ).then((_) {
      // AK-SYNC U05 : au retour de la saisie, rafraîchit les badges d'état
      if (mounted) _refreshSyncState();
    });
  }

  /// Revient à la sélection de système éducatif (réinitialise la navigation).
  void _backToSystems(BuildContext context, CampaignProvider camps) {
    camps.selectCampaign(campaign);
  }

  /// Navigue vers le niveau [breadcrumbIndex] du fil d'Ariane.
  /// Calcule le nombre de niveaux à remonter et appelle [navigateUpRegroup].
  void _navigateToLevel(
      BuildContext context, CampaignProvider camps, int breadcrumbIndex) {
    final levelsUp =
        camps.regroupBreadcrumb.length - 1 - breadcrumbIndex;
    if (levelsUp > 0) {
      camps.navigateUpRegroup(levelsUp: levelsUp);
    }
  }
}

// ─── Espace commentaire pour la conversion StatelessWidget → StatefulWidget ──
// La classe CampaignDetailScreen a été convertie en StatefulWidget en
// session 17 pour permettre l'envoi global de campagne (méthode
// _sendAllCampaignForms) qui nécessite le lifecycle 'mounted'.

// ─── Barre de fil d'Ariane ───────────────────────────────────────────────────
/// Barre de navigation horizontale affichée sous le titre de la AppBar.
/// Montre : [Système] > [Regroupement 1] > [Regroupement 2] > …
/// Les éléments cliquables permettent de remonter à n'importe quel niveau.
class _BreadcrumbBar extends StatelessWidget {
  const _BreadcrumbBar({
    required this.system,
    required this.breadcrumb,
    required this.onSystemTap,
    required this.onBreadcrumbTap,
  });
  final EducationSystem system;
  final List<Regroup> breadcrumb;
  final VoidCallback onSystemTap;
  final void Function(int index) onBreadcrumbTap;

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      child: Row(
        children: [
          // Élément racine : nom du système éducatif (cliquable → retour systèmes)
          InkWell(
            onTap: onSystemTap,
            child: Text(system.libSystem,
                style: TextStyle(
                    color: Theme.of(context).colorScheme.primary,
                    fontWeight: FontWeight.w600)),
          ),
          // Éléments du fil d'Ariane : un chevron + nom de regroupement par niveau
          for (int i = 0; i < breadcrumb.length; i++) ...[
            const Icon(Icons.chevron_right, size: 16),
            InkWell(
              onTap: () => onBreadcrumbTap(i),
              child: Text(
                breadcrumb[i].libRegp,
                style: TextStyle(
                  // Dernier élément en noir (courant) ; les autres en couleur primaire (cliquables)
                  color: i < breadcrumb.length - 1
                      ? Theme.of(context).colorScheme.primary
                      : Theme.of(context).colorScheme.onSurface,
                  fontWeight: i == breadcrumb.length - 1
                      ? FontWeight.bold
                      : FontWeight.normal,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// ─── Puce de sélection d'un système éducatif ────────────────────────────────
/// Puce cliquable représentant un système éducatif.
/// Ex. : "Education de Base", "Enseignement Secondaire"
class _SystemChip extends StatelessWidget {
  const _SystemChip({required this.system, required this.onTap});
  final EducationSystem system;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return ActionChip(
      avatar: const Icon(Icons.school_outlined, size: 18),
      label: Text(system.libSystem),
      onPressed: onTap,
      backgroundColor: Theme.of(context).colorScheme.primaryContainer,
      labelStyle: TextStyle(
          color: Theme.of(context).colorScheme.onPrimaryContainer),
    );
  }
}

// ─── Tuile de regroupement ───────────────────────────────────────────────────
/// Tuile représentant un regroupement géographique ou administratif
/// (commune, département, région, district…).
/// Le [typeLabel] est résolu via [CampaignProvider.regroupTypeLabel].
class _RegroupTile extends StatelessWidget {
  const _RegroupTile({
    required this.regroup,
    required this.typeLabel,
    required this.onTap,
  });
  final Regroup regroup;
  final String typeLabel;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 4),
      child: ListTile(
        leading: const Icon(Icons.location_on_outlined),
        title: Text(regroup.libRegp),
        subtitle: Text(typeLabel,
            style: Theme.of(context).textTheme.bodySmall),
        trailing: const Icon(Icons.chevron_right),
        onTap: onTap,
      ),
    );
  }
}

// ─── Tuile d'établissement ───────────────────────────────────────────────────
/// Tuile représentant un établissement scolaire dans la liste.
/// Affiche le nom et le statut (Public/Privé/Communautaire) si disponible.
/// Le tap ouvre l'écran de saisie [SchoolDataScreen] via [_openSchool].
///
/// AK-SYNC U05/U07/U19 : affiche sous le nom un badge agrégé d'état de
/// synchronisation (« 5/5 envoyés », « 2 à envoyer », « 1 échec »,
/// « verrouillé », « correction demandée ») et colore l'avatar en conséquence.
class _SchoolTile extends StatelessWidget {
  const _SchoolTile({
    required this.school,
    required this.onTap,
    this.summary,
    this.correctionRequested = false,
  });
  final School school;
  final VoidCallback onTap;
  final SchoolSyncSummary? summary;
  final bool correctionRequested;

  Color? _avatarColor() {
    final s = summary;
    if (correctionRequested) return const Color(0xFFB71C1C);
    if (s == null || s.total == 0) return null;
    if (s.failed > 0) return syncStatusColor(SyncStatus.failed);
    if (s.pending > 0) return syncStatusColor(SyncStatus.pending);
    if (s.locked == s.total) return syncStatusColor(SyncStatus.locked);
    if (s.allSent || (s.sent + s.locked) == s.total) return syncStatusColor(SyncStatus.sent);
    return null;
  }

  @override
  Widget build(BuildContext context) {
    final c = _avatarColor();
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 4),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: c != null
              ? c.withOpacity(0.15)
              : Theme.of(context).colorScheme.secondaryContainer,
          child: Icon(Icons.business_outlined,
              color: c ?? Theme.of(context).colorScheme.onSecondaryContainer),
        ),
        title: Text(school.libEtab),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (school.idStatus != null) Text('Statut : ${school.idStatus}'),
            SchoolSyncBadge(summary: summary, hasFeedback: correctionRequested),
          ],
        ),
        isThreeLine: school.idStatus != null &&
            ((summary?.total ?? 0) > 0 || correctionRequested),
        trailing: const Icon(Icons.chevron_right),
        onTap: onTap,
      ),
    );
  }
}

// ─── Bannière données non envoyées (AK-SYNC U05/U07) ─────────────────────────
/// Bannière orange affichée au-dessus de la liste des établissements lorsque
/// des formulaires collectés n'ont pas encore été transmis (statut pending /
/// failed / kosave dans `form_sync_status`, ou repli `is_sent = 0`).
///
/// Actions : « Voir » (liste nominative) et « Envoyer » (envoi ciblé U07).
/// Masquée s'il n'y a rien à envoyer.
class _UnsentDataBanner extends StatelessWidget {
  const _UnsentDataBanner({
    required this.pendingCount,
    required this.isSending,
    required this.onSendPending,
    required this.onShowList,
  });
  final int pendingCount;
  final bool isSending;
  final VoidCallback onSendPending;
  final VoidCallback onShowList;

  @override
  Widget build(BuildContext context) {
    if (pendingCount <= 0) return const SizedBox.shrink();
    const Color bg = Color(0xFFFFF3E0);
    const Color fg = Color(0xFFE65100);
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.fromLTRB(12, 4, 12, 0),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: fg.withOpacity(0.4)),
      ),
      child: Row(
        children: [
          const Icon(Icons.cloud_upload_outlined, color: fg),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              '$pendingCount formulaire${pendingCount > 1 ? 's' : ''} '
              'en attente d\'envoi ou en échec',
              style: const TextStyle(color: fg, fontWeight: FontWeight.w600, fontSize: 13),
            ),
          ),
          TextButton(
            onPressed: onShowList,
            child: const Text('Voir', style: TextStyle(color: fg)),
          ),
          FilledButton.tonalIcon(
            onPressed: isSending ? null : onSendPending,
            icon: const Icon(Icons.send_outlined, size: 16),
            label: const Text('Envoyer', style: TextStyle(fontSize: 12.5)),
          ),
        ],
      ),
    );
  }
}
