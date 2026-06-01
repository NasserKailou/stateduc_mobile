import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/campaign_provider.dart';
import '../../models/campaign.dart';
import '../../models/regroup.dart';
import '../../models/school.dart';
import '../../models/education_system.dart';
import '../data_entry/school_data_screen.dart';

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
///   [_UnsentDataBanner] — bannière d'alerte données non envoyées (placeholder)
class CampaignDetailScreen extends StatelessWidget {
  const CampaignDetailScreen({super.key, required this.campaign});
  final Campaign campaign;

  @override
  Widget build(BuildContext context) {
    return Consumer<CampaignProvider>(builder: (context, camps, _) {
      return Scaffold(
        appBar: AppBar(
          title: Text(campaign.libCamp),
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
    return Column(
      children: [
        // Bannière de données non envoyées (placeholder — voir _UnsentDataBanner)
        _UnsentDataBanner(
          idCamp: campaign.idCamp,
          idSystem: camps.selectedSystem!.idSystem,
        ),
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.all(8),
            itemCount: camps.currentSchools.length,
            itemBuilder: (context, i) {
              final s = camps.currentSchools[i];
              return _SchoolTile(
                school: s,
                onTap: () => _openSchool(context, camps, s),
              );
            },
          ),
        ),
      ],
    );
  }

  /// Ouvre l'écran de saisie [SchoolDataScreen] pour un établissement.
  ///
  /// Construit la hiérarchie administrative à partir du fil d'Ariane de navigation.
  /// Ex. : breadcrumb = [AGADEZ, ADERBISANAT, ADEBISSANAT]
  ///   → adminHierarchy = "AGADEZ / ADERBISANAT / ADEBISSANAT"
  ///
  /// Si le fil d'Ariane est vide (navigation plate), utilise [school.libHierarchy]
  /// comme fallback (valeur stockée sur le serveur à l'inscription de l'établissement).
  void _openSchool(
      BuildContext context, CampaignProvider camps, School school) {
    final breadcrumb = camps.regroupBreadcrumb;
    // Construit la chaîne de hiérarchie administrative depuis le fil d'Ariane
    final adminHierarchy = breadcrumb.isNotEmpty
        ? breadcrumb.map((r) => r.libRegp).join(' / ')
        : school.libHierarchy;  // fallback : valeur stockée dans la BDD serveur

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
    );
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
class _SchoolTile extends StatelessWidget {
  const _SchoolTile({required this.school, required this.onTap});
  final School school;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 4),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor:
              Theme.of(context).colorScheme.secondaryContainer,
          child: Icon(Icons.business_outlined,
              color:
                  Theme.of(context).colorScheme.onSecondaryContainer),
        ),
        title: Text(school.libEtab),
        subtitle: school.idStatus != null
            ? Text('Statut : ${school.idStatus}')
            : null,
        trailing: const Icon(Icons.chevron_right),
        onTap: onTap,
      ),
    );
  }
}

// ─── Bannière données non envoyées ──────────────────────────────────────────
/// Bannière affichée au-dessus de la liste des établissements lorsque des
/// données collectées n'ont pas encore été envoyées au serveur.
///
/// PLACEHOLDER : dans l'implémentation complète, ce widget interrogerait
/// la table [collected_data] de SQLite pour compter les lignes avec
/// is_sent = 0 pour la campagne et le système donnés, puis afficherait
/// une bannière d'alerte orange avec le nombre d'établissements concernés.
class _UnsentDataBanner extends StatelessWidget {
  const _UnsentDataBanner(
      {required this.idCamp, required this.idSystem});
  final String idCamp;
  final String idSystem;

  @override
  Widget build(BuildContext context) {
    // Placeholder — dans une vraie implémentation, vérifier la DB pour les données non envoyées
    // et afficher une bannière si is_sent=0 pour (idCamp, idSystem)
    return const SizedBox.shrink();
  }
}
