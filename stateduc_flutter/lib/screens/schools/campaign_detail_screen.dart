import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/campaign_provider.dart';
import '../../models/campaign.dart';
import '../../models/regroup.dart';
import '../../models/school.dart';
import '../../models/education_system.dart';
import '../data_entry/school_data_screen.dart';

/// CampaignDetailScreen — system selector + hierarchical regroup drill-down
/// + school list.
///
/// Navigation flow (mirrors page_camp.js):
///   displaySystems()        → EducationSystem selection chips
///   displayRegroups(null)   → Root regroupements list
///   displayRegroups(id)     → Child regroupements drill-down
///   displayFinalRegroupEtabs() → School list (leaf node)
///
/// Breadcrumb trail shown at top when drilling into regroups.
class CampaignDetailScreen extends StatelessWidget {
  const CampaignDetailScreen({super.key, required this.campaign});
  final Campaign campaign;

  @override
  Widget build(BuildContext context) {
    return Consumer<CampaignProvider>(builder: (context, camps, _) {
      return Scaffold(
        appBar: AppBar(
          title: Text(campaign.libCamp),
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

  Widget _buildBody(BuildContext context, CampaignProvider camps) {
    // No system selected → show system chips
    if (camps.selectedSystem == null) {
      return _buildSystemSelector(context, camps);
    }
    // Navigation in progress → real loading spinner (controlled by isNavigating)
    if (camps.isNavigating) {
      return const Center(child: CircularProgressIndicator());
    }
    // System selected, no regroups left, schools available → school list
    if (camps.currentRegroups.isEmpty && camps.currentSchools.isNotEmpty) {
      return _buildSchoolList(context, camps);
    }
    // Both lists empty after navigation finished → show empty/error state (no infinite spinner)
    if (camps.currentRegroups.isEmpty && camps.currentSchools.isEmpty) {
      return _buildEmptyState(context, camps);
    }
    // Regroup drill-down list
    return _buildRegroupList(context, camps);
  }

  // ─── System selector ─────────────────────────────────────────────────────
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

  // ─── Empty state (no regroups and no schools after navigation) ──────────
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

  // ─── Regroup drill-down ──────────────────────────────────────────────────
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

  // ─── School list ─────────────────────────────────────────────────────────
  Widget _buildSchoolList(
      BuildContext context, CampaignProvider camps) {
    return Column(
      children: [
        // Unsent data warning banner
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

  void _openSchool(
      BuildContext context, CampaignProvider camps, School school) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => SchoolDataScreen(
          campaign: campaign,
          school: school,
          idSystem: camps.selectedSystem!.idSystem,
          libSystem: camps.selectedSystem?.libSystem,
        ),
      ),
    );
  }

  void _backToSystems(BuildContext context, CampaignProvider camps) {
    camps.selectCampaign(campaign);
  }

  void _navigateToLevel(
      BuildContext context, CampaignProvider camps, int breadcrumbIndex) {
    final levelsUp =
        camps.regroupBreadcrumb.length - 1 - breadcrumbIndex;
    if (levelsUp > 0) {
      camps.navigateUpRegroup(levelsUp: levelsUp);
    }
  }
}

// ─── Breadcrumb bar ──────────────────────────────────────────────────────────
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
          InkWell(
            onTap: onSystemTap,
            child: Text(system.libSystem,
                style: TextStyle(
                    color: Theme.of(context).colorScheme.primary,
                    fontWeight: FontWeight.w600)),
          ),
          for (int i = 0; i < breadcrumb.length; i++) ...[
            const Icon(Icons.chevron_right, size: 16),
            InkWell(
              onTap: () => onBreadcrumbTap(i),
              child: Text(
                breadcrumb[i].libRegp,
                style: TextStyle(
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

// ─── System chip ────────────────────────────────────────────────────────────
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

// ─── Regroup tile ────────────────────────────────────────────────────────────
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

// ─── School tile ─────────────────────────────────────────────────────────────
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

// ─── Unsent data banner ──────────────────────────────────────────────────────
class _UnsentDataBanner extends StatelessWidget {
  const _UnsentDataBanner(
      {required this.idCamp, required this.idSystem});
  final String idCamp;
  final String idSystem;

  @override
  Widget build(BuildContext context) {
    // In a real app this would check the DB for unsent data
    // and show a banner if any; for now it's a placeholder.
    return const SizedBox.shrink();
  }
}
