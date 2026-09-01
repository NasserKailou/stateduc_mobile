import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../login/pin_screen.dart';

/// OnboardingScreen — s'affiche UNIQUEMENT au premier lancement.
///
/// Structure : 5 pages défilantes horizontalement (PageView).
/// Personnalisation : modifier la liste [_pages] ci-dessous.
///
/// Après la dernière page → marque 'onboarding_done' dans SharedPreferences
/// et navigue vers PinScreen.
class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final PageController _pageCtrl = PageController();
  int _currentPage = 0;

  // ── Pages de l'onboarding ─────────────────────────────────────────────────
  // Personnalisez ici : titre, description, icône, couleur d'accentuation.
  static const List<_OnboardingPage> _pages = [
    _OnboardingPage(
      icon: Icons.school_rounded,
      title: 'Bienvenue sur StatEduc',
      description:
          'L\'application officielle de collecte de données éducatives.\n'
          'Suivez et saisissez les statistiques de vos établissements '
          'scolaires en toute simplicité.',
      accentColor: Color(0xFF1565C0),
    ),
    _OnboardingPage(
      icon: Icons.campaign_rounded,
      title: 'Campagnes de collecte',
      description:
          'Téléchargez les campagnes assignées à votre compte, '
          'accédez à la liste de vos établissements et commencez '
          'la saisie même sans connexion internet.',
      accentColor: Color(0xFF1976D2),
    ),
    _OnboardingPage(
      icon: Icons.edit_note_rounded,
      title: 'Saisie de données',
      description:
          'Remplissez les formulaires dynamiques pour chaque établissement. '
          'Vos données sont sauvegardées localement et synchronisées '
          'avec le serveur dès que vous êtes connecté.',
      accentColor: Color(0xFF0288D1),
    ),
    _OnboardingPage(
      icon: Icons.cloud_sync_rounded,
      title: 'Synchronisation',
      description:
          'Envoyez vos données saisies vers le serveur StatEduc en un clic. '
          'Rechargez les données existantes pour reprendre une saisie '
          'commencée sur un autre appareil.',
      accentColor: Color(0xFF0097A7),
    ),
    _OnboardingPage(
      icon: Icons.lock_rounded,
      title: 'Sécurité & PIN',
      description:
          'Protégez l\'accès à vos données avec un code PIN personnel. '
          'Vos identifiants sont stockés de façon sécurisée sur l\'appareil '
          'et jamais transmis en clair.',
      accentColor: Color(0xFF00796B),
    ),
  ];

  Future<void> _completeOnboarding() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('onboarding_done', true);
    if (!mounted) return;
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => const PinScreen()),
    );
  }

  void _nextPage() {
    if (_currentPage < _pages.length - 1) {
      _pageCtrl.nextPage(
        duration: const Duration(milliseconds: 350),
        curve: Curves.easeInOut,
      );
    } else {
      _completeOnboarding();
    }
  }

  void _skip() => _completeOnboarding();

  @override
  void dispose() {
    _pageCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isLast = _currentPage == _pages.length - 1;

    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            // ── Skip button ───────────────────────────────────────────────
            Align(
              alignment: Alignment.topRight,
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                child: isLast
                    ? const SizedBox(height: 40)
                    : TextButton(
                        onPressed: _skip,
                        child: const Text(
                          'Passer',
                          style: TextStyle(
                              color: Colors.grey, fontWeight: FontWeight.w500),
                        ),
                      ),
              ),
            ),

            // ── PageView ──────────────────────────────────────────────────
            Expanded(
              child: PageView.builder(
                controller: _pageCtrl,
                itemCount: _pages.length,
                onPageChanged: (i) => setState(() => _currentPage = i),
                itemBuilder: (_, i) => _OnboardingPageWidget(page: _pages[i]),
              ),
            ),

            // ── Dot indicators ────────────────────────────────────────────
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(
                  _pages.length,
                  (i) => AnimatedContainer(
                    duration: const Duration(milliseconds: 250),
                    margin: const EdgeInsets.symmetric(horizontal: 4),
                    width: _currentPage == i ? 24 : 8,
                    height: 8,
                    decoration: BoxDecoration(
                      color: _currentPage == i
                          ? _pages[i].accentColor
                          : Colors.grey.shade300,
                      borderRadius: BorderRadius.circular(4),
                    ),
                  ),
                ),
              ),
            ),

            // ── Navigation buttons ────────────────────────────────────────
            Padding(
              padding: const EdgeInsets.fromLTRB(24, 0, 24, 32),
              child: Row(
                children: [
                  // Back button (hidden on first page)
                  if (_currentPage > 0)
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () => _pageCtrl.previousPage(
                          duration: const Duration(milliseconds: 350),
                          curve: Curves.easeInOut,
                        ),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          side: BorderSide(
                              color: _pages[_currentPage].accentColor),
                          foregroundColor: _pages[_currentPage].accentColor,
                        ),
                        child: const Text('Précédent'),
                      ),
                    ),

                  if (_currentPage > 0) const SizedBox(width: 12),

                  // Next / Start button
                  Expanded(
                    flex: _currentPage > 0 ? 2 : 1,
                    child: ElevatedButton(
                      onPressed: _nextPage,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _pages[_currentPage].accentColor,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8)),
                      ),
                      child: Text(
                        isLast ? 'Commencer' : 'Suivant',
                        style: const TextStyle(
                            fontSize: 16, fontWeight: FontWeight.w600),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Data model ───────────────────────────────────────────────────────────────
class _OnboardingPage {
  final IconData icon;
  final String title;
  final String description;
  final Color accentColor;

  const _OnboardingPage({
    required this.icon,
    required this.title,
    required this.description,
    required this.accentColor,
  });
}

// ─── Single page widget ───────────────────────────────────────────────────────
class _OnboardingPageWidget extends StatelessWidget {
  const _OnboardingPageWidget({required this.page});
  final _OnboardingPage page;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 32),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          // Icon circle
          Container(
            width: 140,
            height: 140,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: page.accentColor.withOpacity(0.1),
            ),
            child: Center(
              child: Icon(page.icon, size: 72, color: page.accentColor),
            ),
          ),

          const SizedBox(height: 40),

          // Title
          Text(
            page.title,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: page.accentColor,
              height: 1.3,
            ),
          ),

          const SizedBox(height: 20),

          // Description
          Text(
            page.description,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 15,
              color: Colors.grey.shade700,
              height: 1.6,
            ),
          ),
        ],
      ),
    );
  }
}
