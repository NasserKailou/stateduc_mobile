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
              // Logout
              IconButton(
                icon: const Icon(Icons.logout),
                tooltip: 'Déconnexion',
                onPressed: () => _confirmLogout(auth),
              ),
            ],
          ),
          body: _buildBody(auth, campaigns),
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

  Widget _buildBody(AuthProvider auth, CampaignProvider campaigns) {
    if (campaigns.isLoadingCampaigns) {
      return const Center(child: CircularProgressIndicator());
    }
    if (campaigns.error != null) {
      return _buildError(campaigns);
    }
    if (campaigns.campaigns.isEmpty) {
      return _buildEmpty();
    }
    return _buildList(campaigns);
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

  Widget _buildList(CampaignProvider campaigns) {
    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: campaigns.campaigns.length,
      itemBuilder: (context, i) {
        final c = campaigns.campaigns[i];
        return _CampaignCard(
          campaign: c,
          onTap: () => _openCampaign(c, campaigns),
          onDelete: () => _confirmDelete(c, campaigns),
        );
      },
    );
  }

  void _openCampaign(Campaign c, CampaignProvider campaigns) async {
    await campaigns.selectCampaign(c);
    if (mounted) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => CampaignDetailScreen(campaign: c),
        ),
      );
    }
  }

  void _confirmDelete(Campaign c, CampaignProvider campaigns) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Supprimer la campagne'),
        content: Text(
            'Supprimer "${c.libCamp}" ? Les données saisies seront perdues.'),
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
              // TODO: campaigns.deleteCampaign(c.idCamp)
              // Reload list
              await campaigns.loadLocalCampaigns();
            },
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );
  }

  void _confirmLogout(AuthProvider auth) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Déconnexion'),
        content:
            const Text('Vos données locales seront conservées.'),
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
            child: const Text('Se déconnecter'),
          ),
        ],
      ),
    );
  }
}

// ─── Campaign card ───────────────────────────────────────────────────────────
class _CampaignCard extends StatelessWidget {
  const _CampaignCard({
    required this.campaign,
    required this.onTap,
    required this.onDelete,
  });
  final Campaign campaign;
  final VoidCallback onTap;
  final VoidCallback onDelete;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: Theme.of(context).colorScheme.primaryContainer,
          child: Icon(Icons.campaign_outlined,
              color: Theme.of(context).colorScheme.primary),
        ),
        title: Text(campaign.libCamp,
            style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Text(
          [
            if (campaign.libYear != null) campaign.libYear!,
            if (campaign.dateDebut != null && campaign.dateFin != null)
              '${campaign.dateDebut} → ${campaign.dateFin}',
          ].join(' · '),
        ),
        trailing: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.chevron_right),
            IconButton(
              icon: const Icon(Icons.delete_outline),
              onPressed: onDelete,
              color: Theme.of(context).colorScheme.error,
              tooltip: 'Supprimer',
            ),
          ],
        ),
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
