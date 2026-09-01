import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/campaign_provider.dart';
import '../../models/campaign.dart';

/// LoadCampaignScreen — downloads a new campaign from the server.
///
/// Mirrors:
///   new_camp.html  → p_new_camp page
///   page_new_camp.js → stmPageNewCamp.getCampsFromServer()
///   charge_camp.js  → stmChargeCamp: the 9-step download sequence
class LoadCampaignScreen extends StatefulWidget {
  const LoadCampaignScreen({super.key});

  @override
  State<LoadCampaignScreen> createState() => _LoadCampaignScreenState();
}

class _LoadCampaignScreenState extends State<LoadCampaignScreen> {
  Campaign? _selectedCampaign;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final auth = context.read<AuthProvider>();
      if (auth.user != null) {
        // AK-CAMP-03 : passe l'année active mobile pour que le serveur
        // filtre les campagnes par année (et non plus par $_SESSION['annee']).
        context
            .read<CampaignProvider>()
            .fetchServerCampaigns(
              auth.user!.idUser,
              yearCode: auth.effectiveYearCode,
            );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Consumer2<AuthProvider, CampaignProvider>(
      builder: (context, auth, camps, _) {
        return Scaffold(
          appBar: AppBar(title: const Text('Charger une campagne')),
          body: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // ── Error ────────────────────────────────────────────────
                if (camps.error != null)
                  _ErrorCard(
                    message: camps.error!,
                    onDismiss: camps.clearError,
                  ),
                // ── Available campaigns from server ──────────────────────
                Text('Campagnes disponibles sur le serveur',
                    style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 8),
                if (camps.serverCampaigns.isEmpty && !camps.isLoadingCampaigns)
                  _buildEmpty(auth, camps)
                else
                  Expanded(
                    child: camps.isLoadingCampaigns
                        ? const Center(child: CircularProgressIndicator())
                        : _buildServerCampaignList(camps),
                  ),
                // ── Load button ──────────────────────────────────────────
                if (_selectedCampaign != null && !camps.isLoadingCampaign) ...[
                  const SizedBox(height: 12),
                  ElevatedButton.icon(
                    onPressed: () => _loadCampaign(auth, camps),
                    icon: const Icon(Icons.download_outlined),
                    label: Text(
                        'Charger "${_selectedCampaign!.libCamp}"'),
                  ),
                ],
                // ── Progress ─────────────────────────────────────────────
                if (camps.isLoadingCampaign) _buildProgress(camps),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildEmpty(AuthProvider auth, CampaignProvider camps) {
    // SESSION 38 : indicateur de chargement sur le bouton "Actualiser".
    // Quand fetchServerCampaigns() est en cours, camps.isLoadingCampaigns==true :
    //   • l'icône devient un CircularProgressIndicator animé
    //   • le texte passe à "Chargement…"
    //   • onPressed est null (bouton désactivé pour éviter les doubles-appels)
    final bool fetching = camps.isLoadingCampaigns;
    return Column(
      children: [
        const Icon(Icons.cloud_off_outlined, size: 48),
        const SizedBox(height: 8),
        const Text('Aucune campagne disponible'),
        const SizedBox(height: 12),
        ElevatedButton.icon(
          // AK-CAMP-03 : passe l'année active mobile lors de l'actualisation.
          onPressed: (auth.user == null || fetching)
              ? null
              : () => camps.fetchServerCampaigns(
                    auth.user!.idUser,
                    yearCode: auth.effectiveYearCode,
                  ),
          icon: fetching
              ? const SizedBox(
                  width: 18,
                  height: 18,
                  child: CircularProgressIndicator(
                    strokeWidth: 2.5,
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  ),
                )
              : const Icon(Icons.refresh),
          label: Text(fetching ? 'Chargement…' : 'Actualiser'),
        ),
      ],
    );
  }

  Widget _buildServerCampaignList(CampaignProvider camps) {
    return ListView.builder(
      itemCount: camps.serverCampaigns.length,
      itemBuilder: (context, i) {
        final c = camps.serverCampaigns[i];
        final isSelected = _selectedCampaign?.idCamp == c.idCamp;
        return ListTile(
          leading: CircleAvatar(
            backgroundColor: isSelected
                ? Theme.of(context).colorScheme.primary
                : Theme.of(context).colorScheme.surfaceContainerHighest,
            child: Icon(
              Icons.campaign_outlined,
              color: isSelected
                  ? Theme.of(context).colorScheme.onPrimary
                  : null,
            ),
          ),
          title: Text(c.libCamp,
              style: TextStyle(
                  fontWeight: isSelected
                      ? FontWeight.bold
                      : FontWeight.normal)),
          subtitle: Text(c.libYear ?? ''),
          selected: isSelected,
          onTap: () => setState(() => _selectedCampaign = c),
          trailing: isSelected
              ? Icon(Icons.check_circle,
                  color: Theme.of(context).colorScheme.primary)
              : null,
        );
      },
    );
  }

  Widget _buildProgress(CampaignProvider camps) {
    return Card(
      margin: const EdgeInsets.only(top: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              camps.loadStepLabel,
              style: Theme.of(context).textTheme.bodyMedium,
            ),
            const SizedBox(height: 8),
            LinearProgressIndicator(
              value: camps.loadTotalSteps > 0
                  ? camps.loadStep / camps.loadTotalSteps
                  : null,
            ),
            const SizedBox(height: 4),
            Text(
              'Étape ${camps.loadStep} / ${camps.loadTotalSteps}',
              style: Theme.of(context).textTheme.bodySmall,
              textAlign: TextAlign.end,
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _loadCampaign(
      AuthProvider auth, CampaignProvider camps) async {
    if (_selectedCampaign == null || auth.user == null) return;
    final ok = await camps.loadCampaignFromServer(
      campaign: _selectedCampaign!,
      login:    auth.user!.login,    // for reg_camp endpoint (uses login!)
      userId:   auth.user!.idUser,   // for all other endpoints (uses id)
    );
    if (ok && mounted) {
      // AK-YEAR-02 : synchronise AuthProvider avec les années mises en cache
      // par l'étape 0 de loadCampaignFromServer — fire & forget (non-bloquant).
      auth.loadYears();

      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(
              'Campagne "${_selectedCampaign!.libCamp}" chargée avec succès')));
      Navigator.pop(context);
    }
  }
}

// ─── Error card ──────────────────────────────────────────────────────────────
class _ErrorCard extends StatelessWidget {
  const _ErrorCard({required this.message, required this.onDismiss});
  final String message;
  final VoidCallback onDismiss;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.errorContainer,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Icon(Icons.error_outline,
              color: Theme.of(context).colorScheme.onErrorContainer),
          const SizedBox(width: 8),
          Expanded(
            child: Text(message,
                style: TextStyle(
                    color:
                        Theme.of(context).colorScheme.onErrorContainer)),
          ),
          IconButton(
            icon: const Icon(Icons.close, size: 16),
            onPressed: onDismiss,
          ),
        ],
      ),
    );
  }
}
