import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/campaign_provider.dart';
import '../../models/campaign.dart';
import '../../models/school_year.dart';
import '../login/pin_screen.dart';

/// SettingsScreen — Server URL config, PIN change, 3-question security setup.
///
/// Session 50: Security tab now manages 3 fixed mandatory questions.
///
/// Mirrors:
///   page_settings.js → PIN change (change_code), security question change
///   page_index.js    → server URL setting (init_options)
class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabController;
  // Clé pour déclencher loadYears() une seule fois
  bool _yearsInitialized = false;

  final _serverUrlController = TextEditingController();

  // PIN change
  final _oldPinController = TextEditingController();
  final _newPinController = TextEditingController();
  final _confirmPinController = TextEditingController();

  // Security answers for 3-question system (Session 50)
  final _secA1Controller = TextEditingController();
  final _secA2Controller = TextEditingController();
  final _secA3Controller = TextEditingController();

  bool _obscureOldPin = true;
  bool _obscureNewPin = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _tabController.addListener(_onTabChanged);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final auth = context.read<AuthProvider>();
      if (auth.serverUrl != null) {
        _serverUrlController.text = auth.serverUrl!;
      }
      // Security answers are hashed — not pre-filled (not recoverable for display)
    });
  }

  void _onTabChanged() {
    // Onglet 1 = Année (index 1) — charger les années la première fois
    if (_tabController.index == 1 && !_yearsInitialized) {
      _yearsInitialized = true;
      context.read<AuthProvider>().loadYears();
    }
  }

  @override
  void dispose() {
    _tabController.removeListener(_onTabChanged);
    _tabController.dispose();
    _serverUrlController.dispose();
    _oldPinController.dispose();
    _newPinController.dispose();
    _confirmPinController.dispose();
    _secA1Controller.dispose();
    _secA2Controller.dispose();
    _secA3Controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(builder: (context, auth, _) {
      // Couleurs de la TabBar — forcer un contraste élevé quel que soit le thème.
      // Par défaut Material 3, les onglets non sélectionnés utilisent
      // onSurface.withOpacity(0.5) ce qui les rend trop gris/peu lisibles.
      final appBarFg = Theme.of(context).appBarTheme.foregroundColor
          ?? Theme.of(context).colorScheme.onPrimary;
      return Scaffold(
        appBar: AppBar(
          title: const Text('Paramètres'),
          bottom: TabBar(
            controller: _tabController,
            // Onglet sélectionné : blanc (ou couleur avant-plan de l'AppBar)
            labelColor: appBarFg,
            // Onglets non sélectionnés : même couleur à 80 % d'opacité (lisible)
            unselectedLabelColor: appBarFg.withOpacity(0.80),
            indicatorColor: appBarFg,
            labelStyle: const TextStyle(
              fontWeight: FontWeight.w600,
              fontSize: 12,
            ),
            unselectedLabelStyle: const TextStyle(
              fontWeight: FontWeight.w500,
              fontSize: 12,
            ),
            tabs: const [
              Tab(icon: Icon(Icons.dns_outlined),        text: 'Serveur'),
              Tab(icon: Icon(Icons.calendar_today),      text: 'Année'),
              Tab(icon: Icon(Icons.lock_outline),        text: 'PIN'),
              Tab(icon: Icon(Icons.help_outline),        text: 'Sécurité'),
            ],
          ),
        ),
        body: TabBarView(
          controller: _tabController,
          children: [
            _buildServerTab(auth),
            _buildYearTab(auth),
            _buildPinTab(auth),
            _buildSecurityTab(auth),
          ],
        ),
      );
    });
  }

  // ─── Server tab ─────────────────────────────────────────────────────────────
  Widget _buildServerTab(AuthProvider auth) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text('Configuration du serveur',
              style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 4),
          Text(
            'Adresse complète du serveur StatEduc',
            style: Theme.of(context).textTheme.bodySmall,
          ),
          const SizedBox(height: 16),

          // ── URL field — explicit high-contrast style ──
          TextField(
            controller: _serverUrlController,
            keyboardType: TextInputType.url,
            autocorrect: false,
            enableSuggestions: false,
            style: Theme.of(context).textTheme.bodyLarge?.copyWith(
              fontFamily: 'monospace',
              fontSize: 15,
              color: Theme.of(context).colorScheme.onSurface,
            ),
            decoration: InputDecoration(
              labelText: 'URL du serveur',
              labelStyle: TextStyle(
                color: Theme.of(context).colorScheme.primary,
                fontWeight: FontWeight.w600,
              ),
              hintText: 'https://stateduc.mineduc.gov.bi/',
              helperText: 'Ex : https://stateduc.mineduc.gov.bi/ ou http://192.168.1.100:8083/StatEduc',
              helperMaxLines: 2,
              prefixIcon: const Icon(Icons.dns_outlined),
              border: const OutlineInputBorder(),
              enabledBorder: OutlineInputBorder(
                borderSide: BorderSide(
                  color: Theme.of(context).colorScheme.outline,
                  width: 1.5,
                ),
              ),
              focusedBorder: OutlineInputBorder(
                borderSide: BorderSide(
                  color: Theme.of(context).colorScheme.primary,
                  width: 2,
                ),
              ),
              filled: true,
              fillColor: Theme.of(context).colorScheme.surfaceContainerLowest,
            ),
          ),
          const SizedBox(height: 16),

          if (auth.storedLogin != null)
            Card(
              child: ListTile(
                leading: const Icon(Icons.person_outline),
                title: const Text('Utilisateur connecté'),
                subtitle: Text(
                  auth.storedLogin!,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: Theme.of(context).colorScheme.primary,
                  ),
                ),
                contentPadding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              ),
            ),

          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: () => _saveServerUrl(auth),
            icon: const Icon(Icons.save_outlined),
            label: const Text('Enregistrer l\'URL'),
          ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: () => _confirmLogout(auth),
            icon: const Icon(Icons.logout),
            label: const Text('Se déconnecter'),
            style: OutlinedButton.styleFrom(
              foregroundColor: Theme.of(context).colorScheme.error,
            ),
          ),

          // ── Suppression de campagne (pilote) ────────────────────────────
          // Déplacé depuis l'écran de liste des campagnes pour éviter
          // une suppression accidentelle sur le terrain.
          const SizedBox(height: 24),
          const Divider(),
          const SizedBox(height: 8),
          Text(
            'Gestion des campagnes',
            style: Theme.of(context).textTheme.titleMedium,
          ),
          const SizedBox(height: 4),
          Text(
            'La suppression d\'une campagne efface toutes les données saisies.',
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
              color: Theme.of(context).colorScheme.error,
            ),
          ),
          const SizedBox(height: 12),
          Consumer<CampaignProvider>(
            builder: (context, campaigns, _) {
              if (campaigns.campaigns.isEmpty) {
                return const Text(
                  'Aucune campagne locale à supprimer.',
                  style: TextStyle(fontStyle: FontStyle.italic),
                );
              }
              return Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: campaigns.campaigns.map((c) {
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: OutlinedButton.icon(
                      onPressed: () => _confirmDeleteCampaign(c, campaigns),
                      icon: const Icon(Icons.delete_outline),
                      label: Text('Supprimer « ${c.libCamp} »'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: Theme.of(context).colorScheme.error,
                        side: BorderSide(
                          color: Theme.of(context).colorScheme.error,
                        ),
                      ),
                    ),
                  );
                }).toList(),
              );
            },
          ),
        ],
      ),
    );
  }

  /// Confirme et exécute la suppression d'une campagne locale.
  /// Logique identique à l'ancien _confirmDelete de campaign_list_screen.dart —
  /// seul le point d'entrée dans l'UI a changé (Paramètres, pas liste principale).
  void _confirmDeleteCampaign(Campaign c, CampaignProvider campaigns) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Supprimer la campagne'),
        content: Text(
            'Supprimer « ${c.libCamp} » ? Les données saisies seront perdues.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Annuler')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Theme.of(context).colorScheme.error,
            ),
            onPressed: () async {
              Navigator.pop(ctx);
              await campaigns.deleteCampaign(c.idCamp);
              await campaigns.loadLocalCampaigns();
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('« ${c.libCamp} » supprimée.')),
                );
              }
            },
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );
  }

  Future<void> _saveServerUrl(AuthProvider auth) async {
    final url = _serverUrlController.text.trim();
    if (url.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('L\'URL ne peut pas être vide')));
      return;
    }
    await auth.updateServerUrl(url);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('URL enregistrée')));
    }
  }

  void _confirmLogout(AuthProvider auth) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Déconnexion'),
        content: const Text(
            'Voulez-vous vous déconnecter ? '
            'Vos données locales seront conservées.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Annuler')),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              await auth.logout();
              if (mounted) {
                Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (_) => const PinScreen()),
                  (_) => false,
                );
              }
            },
            child: const Text('Déconnecter'),
          ),
        ],
      ),
    );
  }

  // ─── Year tab ─────────────────────────────────────────────────────────────────
  /// Onglet « Année » — sélection de l'année de recensement active.
  ///
  /// Comportement :
  ///  - Affiche uniquement l'année active en session (liste fermée)
  ///  - Cliquer sur la liste déroulante révèle les autres années disponibles
  ///  - L'année sélectionnée est persistée et utilisée dans tous les formulaires
  Widget _buildYearTab(AuthProvider auth) {
    final years   = auth.schoolYears;
    final active  = auth.activeYear;
    final loading = auth.yearsLoading;

    // Années autres que l'année active (celles accessibles en déroulant)
    final otherYears = years.where((y) => y.code != active?.code).toList();

    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // ── Titre + indicateur réseau ─────────────────────────────────
          Row(
            children: [
              Expanded(
                child: Text(
                  'Année de recensement',
                  style: Theme.of(context).textTheme.titleMedium,
                ),
              ),
              if (loading)
                const SizedBox(
                  width: 18,
                  height: 18,
                  child: CircularProgressIndicator(strokeWidth: 2),
                ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            'L\'année en session est affichée. Déroulez pour en changer.',
            style: Theme.of(context).textTheme.bodySmall,
          ),
          const SizedBox(height: 16),

          // ── Liste déroulante des années ───────────────────────────────
          if (years.isEmpty && !loading) ...[
            // Aucune année disponible
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    Icon(Icons.cloud_off_outlined,
                        size: 40,
                        color: Theme.of(context).colorScheme.outline),
                    const SizedBox(height: 8),
                    Text(
                      'Aucune année disponible.\n'
                      'Vérifiez votre connexion et rafraîchissez.',
                      textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                  ],
                ),
              ),
            ),
          ] else ...[
            // ExpansionTile : en-tête = année active, corps = autres années
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
                side: BorderSide(
                  color: Theme.of(context).colorScheme.primary,
                  width: 1.5,
                ),
              ),
              child: Theme(
                // Supprime le diviseur interne par défaut d'ExpansionTile
                data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
                child: ExpansionTile(
                  leading: Icon(
                    Icons.calendar_today,
                    color: Theme.of(context).colorScheme.primary,
                  ),
                  // En-tête : année active (ou placeholder si aucune)
                  title: Text(
                    active?.libelle ?? 'Aucune année en session',
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 15,
                      color: active != null
                          ? Theme.of(context).colorScheme.primary
                          : Theme.of(context).colorScheme.outline,
                    ),
                  ),
                  subtitle: Text(
                    'Année en session — appuyer pour changer',
                    style: TextStyle(
                      fontSize: 11,
                      color: Theme.of(context).colorScheme.onSurfaceVariant,
                    ),
                  ),
                  trailing: Icon(
                    Icons.expand_more,
                    color: Theme.of(context).colorScheme.primary,
                  ),
                  // Corps déroulé : liste des autres années
                  children: otherYears.isEmpty
                      ? [
                          Padding(
                            padding: const EdgeInsets.fromLTRB(16, 4, 16, 12),
                            child: Text(
                              'Aucune autre année disponible.',
                              style: Theme.of(context).textTheme.bodySmall,
                            ),
                          ),
                        ]
                      : otherYears
                          .map((y) => _buildYearDropdownItem(auth, y))
                          .toList(),
                ),
              ),
            ),
          ],

          // ── Bouton rafraîchir ─────────────────────────────────────────
          const SizedBox(height: 20),
          OutlinedButton.icon(
            // AK-YEAR-02 : force=true pour forcer le rechargement réseau
            // même si un chargement précédent est en cours (bouton toujours actif)
            onPressed: () => auth.loadYears(force: true),
            icon: loading
                ? const SizedBox(
                    width: 16, height: 16,
                    child: CircularProgressIndicator(strokeWidth: 2))
                : const Icon(Icons.refresh),
            label: const Text('Rafraîchir la liste'),
          ),
        ],
      ),
    );
  }

  /// Élément de la liste déroulante représentant une année non active.
  /// Un tap déclenche le dialog de confirmation puis active l'année.
  Widget _buildYearDropdownItem(AuthProvider auth, SchoolYear year) {
    return InkWell(
      onTap: () => _selectYear(auth, year),
      child: Container(
        decoration: BoxDecoration(
          border: Border(
            top: BorderSide(
              color: Theme.of(context).colorScheme.outlineVariant,
              width: 0.5,
            ),
          ),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Row(
          children: [
            Icon(
              Icons.radio_button_unchecked,
              size: 20,
              color: Theme.of(context).colorScheme.onSurfaceVariant,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                year.libelle,
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w400,
                  color: Theme.of(context).colorScheme.onSurface,
                ),
              ),
            ),
            Icon(
              Icons.chevron_right,
              size: 18,
              color: Theme.of(context).colorScheme.onSurfaceVariant,
            ),
          ],
        ),
      ),
    );
  }

  /// Affiche un dialogue de confirmation avant de changer l'année active.
  ///
  /// Si l'utilisateur confirme → [auth.setActiveYear] est appelé et un SnackBar
  /// de confirmation s'affiche.
  /// Si l'utilisateur annule → aucun changement, l'année courante est conservée.
  Future<void> _selectYear(AuthProvider auth, SchoolYear year) async {
    // ── Dialogue de confirmation ─────────────────────────────────────────────
    final currentYear = auth.activeYear;
    final confirmed = await showDialog<bool>(
      context: context,
      barrierDismissible: false,   // l'utilisateur doit choisir explicitement
      builder: (ctx) => AlertDialog(
        icon: const Icon(Icons.calendar_today_outlined, size: 32),
        title: const Text('Changer l\'année active ?'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (currentYear != null) ...[
              Text('Année actuelle :',
                  style: TextStyle(
                      fontSize: 12,
                      color: Theme.of(ctx).colorScheme.onSurfaceVariant)),
              Text(currentYear.libelle,
                  style: const TextStyle(
                      fontWeight: FontWeight.bold, fontSize: 15)),
              const SizedBox(height: 12),
            ],
            Text('Nouvelle année :',
                style: TextStyle(
                    fontSize: 12,
                    color: Theme.of(ctx).colorScheme.onSurfaceVariant)),
            Text(year.libelle,
                style: TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 15,
                    color: Theme.of(ctx).colorScheme.primary)),
            const SizedBox(height: 12),
            Text(
              'Les formulaires affichés seront rechargés pour l\'année sélectionnée.',
              style: TextStyle(
                  fontSize: 12,
                  color: Theme.of(ctx).colorScheme.onSurfaceVariant),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),  // Annuler
            child: const Text('Annuler'),
          ),
          FilledButton(
            onPressed: () => Navigator.of(ctx).pop(true),   // Confirmer
            child: const Text('Confirmer'),
          ),
        ],
      ),
    );

    // Si l'utilisateur a annulé (ou fermé le dialogue), ne rien changer
    if (confirmed != true || !mounted) return;

    // ── Appliquer le changement ──────────────────────────────────────────────
    await auth.setActiveYear(year);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Année active : ${year.libelle}'),
          backgroundColor: Theme.of(context).colorScheme.primary,
        ),
      );
    }
  }

  // ─── PIN tab ─────────────────────────────────────────────────────────────────
  Widget _buildPinTab(AuthProvider auth) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text('Changer le code PIN',
              style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 16),
          TextField(
            controller: _oldPinController,
            keyboardType: TextInputType.number,
            obscureText: _obscureOldPin,
            maxLength: 8,
            style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                  color: Theme.of(context).colorScheme.onSurface,
                  fontSize: 16,
                ),
            decoration: InputDecoration(
              labelText: 'PIN actuel',
              prefixIcon: const Icon(Icons.lock_outline),
              border: const OutlineInputBorder(),
              filled: true,
              fillColor:
                  Theme.of(context).colorScheme.surfaceContainerLowest,
              suffixIcon: IconButton(
                icon: Icon(_obscureOldPin
                    ? Icons.visibility_off_outlined
                    : Icons.visibility_outlined),
                onPressed: () =>
                    setState(() => _obscureOldPin = !_obscureOldPin),
              ),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _newPinController,
            keyboardType: TextInputType.number,
            obscureText: _obscureNewPin,
            maxLength: 8,
            style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                  color: Theme.of(context).colorScheme.onSurface,
                  fontSize: 16,
                ),
            decoration: InputDecoration(
              labelText: 'Nouveau PIN',
              prefixIcon: const Icon(Icons.lock),
              border: const OutlineInputBorder(),
              filled: true,
              fillColor:
                  Theme.of(context).colorScheme.surfaceContainerLowest,
              suffixIcon: IconButton(
                icon: Icon(_obscureNewPin
                    ? Icons.visibility_off_outlined
                    : Icons.visibility_outlined),
                onPressed: () =>
                    setState(() => _obscureNewPin = !_obscureNewPin),
              ),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _confirmPinController,
            keyboardType: TextInputType.number,
            obscureText: true,
            maxLength: 8,
            style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                  color: Theme.of(context).colorScheme.onSurface,
                  fontSize: 16,
                ),
            decoration: InputDecoration(
              labelText: 'Confirmer le nouveau PIN',
              prefixIcon: const Icon(Icons.lock),
              border: const OutlineInputBorder(),
              filled: true,
              fillColor:
                  Theme.of(context).colorScheme.surfaceContainerLowest,
            ),
          ),
          if (auth.error != null) ...[
            const SizedBox(height: 8),
            Text(auth.error!,
                style: TextStyle(
                    color: Theme.of(context).colorScheme.error)),
          ],
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: auth.isLoading ? null : () => _doChangePin(auth),
            icon: auth.isLoading
                ? const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2))
                : const Icon(Icons.check),
            label: const Text('Changer le PIN'),
          ),
        ],
      ),
    );
  }

  Future<void> _doChangePin(AuthProvider auth) async {
    final newPin = _newPinController.text.trim();
    final confirm = _confirmPinController.text.trim();
    if (newPin != confirm) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Les PIN ne correspondent pas')));
      return;
    }
    final ok =
        await auth.changePin(_oldPinController.text.trim(), newPin);
    if (ok && mounted) {
      _oldPinController.clear();
      _newPinController.clear();
      _confirmPinController.clear();
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('PIN modifié avec succès')));
    }
  }

  // ─── Security tab — 3 fixed questions (Session 50) ─────────────────────────
  Widget _buildSecurityTab(AuthProvider auth) {
    final questions = auth.securityQuestions;
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text('Questions de sécurité',
              style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 6),
          Text(
            'Ces réponses permettent de récupérer votre PIN en cas d\'oubli '
            '(au moins 2 correctes sur 3 requises). '
            'Stockées de façon sécurisée (hachées).',
            style: Theme.of(context).textTheme.bodySmall,
          ),
          const SizedBox(height: 6),
          // Status indicator
          Row(
            children: [
              Icon(
                auth.hasSecurityAnswers
                    ? Icons.check_circle_outline
                    : Icons.warning_amber_outlined,
                size: 16,
                color: auth.hasSecurityAnswers
                    ? Colors.green
                    : Theme.of(context).colorScheme.error,
              ),
              const SizedBox(width: 4),
              Text(
                auth.hasSecurityAnswers
                    ? 'Questions configurées'
                    : 'Questions non configurées',
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: auth.hasSecurityAnswers
                          ? Colors.green
                          : Theme.of(context).colorScheme.error,
                    ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          _buildSettingsAnswerField(
            question: questions[0],
            controller: _secA1Controller,
            number: 1,
          ),
          const SizedBox(height: 12),
          _buildSettingsAnswerField(
            question: questions[1],
            controller: _secA2Controller,
            number: 2,
          ),
          const SizedBox(height: 12),
          _buildSettingsAnswerField(
            question: questions[2],
            controller: _secA3Controller,
            number: 3,
          ),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed:
                auth.isLoading ? null : () => _doSaveThreeSecurityQ(auth),
            icon: auth.isLoading
                ? const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2))
                : const Icon(Icons.save_outlined),
            label: const Text('Enregistrer les réponses'),
          ),
        ],
      ),
    );
  }

  Widget _buildSettingsAnswerField({
    required String question,
    required TextEditingController controller,
    required int number,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Q$number : $question',
          style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                fontWeight: FontWeight.w500,
              ),
        ),
        const SizedBox(height: 6),
        TextField(
          controller: controller,
          textCapitalization: TextCapitalization.none,
          style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                color: Theme.of(context).colorScheme.onSurface,
              ),
          decoration: InputDecoration(
            labelText: 'Nouvelle réponse',
            hintText: 'Saisissez pour modifier',
            prefixIcon: const Icon(Icons.question_answer_outlined),
            border: const OutlineInputBorder(),
            filled: true,
            fillColor: Theme.of(context).colorScheme.surfaceContainerLowest,
          ),
        ),
      ],
    );
  }

  Future<void> _doSaveThreeSecurityQ(AuthProvider auth) async {
    final a1 = _secA1Controller.text.trim();
    final a2 = _secA2Controller.text.trim();
    final a3 = _secA3Controller.text.trim();
    if (a1.isEmpty || a2.isEmpty || a3.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Les 3 réponses sont obligatoires')));
      return;
    }
    final ok = await auth.updateThreeSecurityAnswers([a1, a2, a3]);
    if (ok && mounted) {
      _secA1Controller.clear();
      _secA2Controller.clear();
      _secA3Controller.clear();
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Questions de sécurité mises à jour')));
    }
  }
}
