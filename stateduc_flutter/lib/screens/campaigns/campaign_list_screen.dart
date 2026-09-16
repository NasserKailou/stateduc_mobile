import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/campaign_provider.dart';
import '../../models/campaign.dart';
import '../settings/settings_screen.dart';
import '../login/pin_screen.dart';
import 'load_campaign_screen.dart';
import '../schools/campaign_detail_screen.dart';

/// CampaignListScreen — list of locally downloaded campaigns.
///
/// Mirrors:
///   page_index.js → stmPageLstCamps: displayCamps(), displayCamp()
///   index.html    → p_lst_camps page, p_new_camp navigation
///
/// fix AK-F-01 : multi-années — mise en avant de la campagne correspondant
/// à l'année active du serveur (user.codeyear / user.libyear).
class CampaignListScreen extends StatefulWidget {
  const CampaignListScreen({super.key});

  @override
  State<CampaignListScreen> createState() => _CampaignListScreenState();
}

class _CampaignListScreenState extends State<CampaignListScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<CampaignProvider>().loadLocalCampaigns();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Consumer2<AuthProvider, CampaignProvider>(
      builder: (context, auth, campaigns, _) {
        // fix AK-F-01 : tri des campagnes par année active serveur (user.codeyear)
        // AK-YEAR-02 : la bannière 'Année active serveur' est supprimée —
        // l'onglet Paramètres → Année gère désormais la sélection d'année.
        final serverCodeyear = auth.user?.codeyear ?? '';

        return Scaffold(
          appBar: AppBar(
            title: const Text('StatEduc'),
            actions: [
              // Connectivity indicator
              _ConnectivityIcon(),
              // Settings
              IconButton(
                icon: const Icon(Icons.settings_outlined),
                tooltip: 'Paramètres',
                onPressed: () => Navigator.push(
                  context,
                  MaterialPageRoute(
                      builder: (_) => const SettingsScreen()),
                ),
              ),
              // SESSION 52 — Bouton Accueil
              IconButton(
                icon: const Icon(Icons.home_outlined),
                tooltip: 'Accueil (PIN)',
                onPressed: () => Navigator.pushAndRemoveUntil(
                  context,
                  MaterialPageRoute(builder: (_) => const PinScreen()),
                  (route) => false,
                ),
              ),
            ],
          ),
          body: _buildBody(campaigns, serverCodeyear),
          floatingActionButton: FloatingActionButton.extended(
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(
                  builder: (_) => const LoadCampaignScreen()),
            ).then((_) =>
                context.read<CampaignProvider>().loadLocalCampaigns()),
            icon: const Icon(Icons.download_outlined),
            label: const Text('Charger campagne'),
          ),
        );
      },
    );
  }

  Widget _buildBody(CampaignProvider campaigns, String serverCodeyear) {
    if (campaigns.isLoadingCampaigns) {
      return const Center(child: CircularProgressIndicator());
    }
    if (campaigns.error != null) {
      return _buildError(campaigns);
    }
    if (campaigns.campaigns.isEmpty) {
      return _buildEmpty();
    }
    return _buildList(campaigns, serverCodeyear);
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.inbox_outlined,
              size: 72,
              color: Theme.of(context).colorScheme.onSurfaceVariant),
          const SizedBox(height: 16),
          Text('Aucune campagne chargée',
              style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          const Text(
              'Appuyez sur "Charger campagne" pour télécharger\nune campagne depuis le serveur.',
              textAlign: TextAlign.center),
        ],
      ),
    );
  }

  Widget _buildError(CampaignProvider campaigns) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.error_outline,
              size: 48, color: Theme.of(context).colorScheme.error),
          const SizedBox(height: 12),
          Text(campaigns.error!),
          const SizedBox(height: 12),
          ElevatedButton(
            onPressed: () {
              campaigns.clearError();
              campaigns.loadLocalCampaigns();
            },
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  // fix AK-F-01 : tri des campagnes — l'année active serveur en tête
  List<Campaign> _sortedCampaigns(List<Campaign> raw, String serverCodeyear) {
    final list = List<Campaign>.from(raw);
    list.sort((a, b) {
      final aMatch = serverCodeyear.isNotEmpty && a.idYear == serverCodeyear ? 0 : 1;
      final bMatch = serverCodeyear.isNotEmpty && b.idYear == serverCodeyear ? 0 : 1;
      if (aMatch != bMatch) return aMatch.compareTo(bMatch);
      // Secondaire : par idCamp décroissant (plus récent en tête)
      return b.idCamp.compareTo(a.idCamp);
    });
    return list;
  }

  Widget _buildList(CampaignProvider campaigns, String serverCodeyear) {
    final sorted = _sortedCampaigns(campaigns.campaigns, serverCodeyear);
    return Column(
      children: [
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            itemCount: sorted.length,
            itemBuilder: (context, i) {
              final c = sorted[i];
              // Mettre en valeur la campagne de l'année active serveur
              final isCurrentYear = serverCodeyear.isNotEmpty && c.idYear == serverCodeyear;
              return _CampaignCard(
                campaign: c,
                isCurrentYear: isCurrentYear,
                onTap: () => _openCampaign(c, campaigns),
              );
            },
          ),
        ),
      ],
    );
  }

  void _openCampaign(Campaign c, CampaignProvider campaigns) async {
    // Affiche le dialog de chargement pendant que selectCampaign() charge
    // les systèmes + regroupements depuis SQLite (même feedback que l'envoi).
    _showLoadingDialog(c.libCamp);
    await campaigns.selectCampaign(c);
    if (mounted) {
      Navigator.pop(context); // ferme le dialog de chargement
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => CampaignDetailScreen(campaign: c),
        ),
      );
    }
  }

  /// Affiche un dialog modal avec spinner pendant le chargement de la campagne.
  /// Ressemble au dialog d'attente lors de l'envoi de données.
  void _showLoadingDialog(String campName) {
    showDialog(
      context: context,
      barrierDismissible: false,   // l'utilisateur ne peut pas fermer manuellement
      builder: (ctx) => PopScope(
        canPop: false,             // désactive le bouton Retour système
        child: Dialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const SizedBox(
                  width: 36,
                  height: 36,
                  child: CircularProgressIndicator(strokeWidth: 3),
                ),
                const SizedBox(width: 20),
                Expanded(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Connexion en cours…',
                        style: Theme.of(context).textTheme.titleSmall,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        campName,
                        style: Theme.of(context).textTheme.bodySmall,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // _confirmLogout retiré avec l'icône déconnexion (pilote — mod3).
  // La déconnexion reste disponible dans SettingsScreen → _confirmLogout().
}

// _ServerYearBanner supprimée (AK-YEAR-02) — remplacée par l'onglet Paramètres → Année.

// ─── Campaign card ───────────────────────────────────────────────────────────
class _CampaignCard extends StatelessWidget {
  const _CampaignCard({
    required this.campaign,
    required this.onTap,
    this.isCurrentYear = false,
    // onDelete retiré — la suppression est déplacée dans Paramètres (pilote)
  });
  final Campaign campaign;
  final VoidCallback onTap;
  /// fix AK-F-01 : true si cette campagne correspond à l'année active serveur
  final bool isCurrentYear;

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      // Bordure colorée pour la campagne de l'année active serveur
      shape: isCurrentYear
          ? RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
              side: BorderSide(color: cs.primary, width: 2),
            )
          : null,
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: isCurrentYear
              ? cs.primary
              : cs.primaryContainer,
          child: Icon(
            Icons.campaign_outlined,
            color: isCurrentYear ? cs.onPrimary : cs.primary,
          ),
        ),
        title: Row(
          children: [
            Expanded(
              child: Text(campaign.libCamp,
                  style: const TextStyle(fontWeight: FontWeight.w600)),
            ),
            // Badge "Année en cours" pour la campagne active
            if (isCurrentYear)
              Container(
                margin: const EdgeInsets.only(left: 6),
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: cs.primary,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  'En cours',
                  style: TextStyle(
                    color: cs.onPrimary,
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
          ],
        ),
        subtitle: Text(
          [
            if (campaign.libYear != null) campaign.libYear!,
            if (campaign.dateDebut != null && campaign.dateFin != null)
              '${campaign.dateDebut} → ${campaign.dateFin}',
          ].join(' · '),
        ),
        trailing: const Icon(Icons.chevron_right),
        onTap: onTap,
      ),
    );
  }
}

// ─── Connectivity icon ───────────────────────────────────────────────────────
class _ConnectivityIcon extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    // connectivity_plus integration will live in the provider;
    // for now we show a static online icon.
    return const Padding(
      padding: EdgeInsets.symmetric(horizontal: 4),
      child: Icon(Icons.wifi, size: 20),
    );
  }
}
