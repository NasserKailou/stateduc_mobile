import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../providers/auth_provider.dart';
import '../login/pin_screen.dart';
import '../onboarding/onboarding_screen.dart';

/// SplashScreen — premier écran visible après que Flutter démarre.
///
/// Séquence :
///   1. Affiche le logo.gif animé + nom de l'application sur fond bleu (2.5s)
///   2. Vérifie si c'est le premier lancement (SharedPreferences 'onboarding_done')
///      → Premier lancement : navigue vers OnboardingScreen
///      → Relancement : navigue vers PinScreen (comportement normal)
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with SingleTickerProviderStateMixin {
  late final AnimationController _fadeCtrl;
  late final Animation<double> _fadeAnim;

  @override
  void initState() {
    super.initState();

    // Fade-in animation for the logo
    _fadeCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    )..forward();
    _fadeAnim = CurvedAnimation(parent: _fadeCtrl, curve: Curves.easeIn);

    // Navigate after splash duration
    _navigateAfterDelay();
  }

  @override
  void dispose() {
    _fadeCtrl.dispose();
    super.dispose();
  }

  Future<void> _navigateAfterDelay() async {
    // Wait for splash display time
    await Future.delayed(const Duration(milliseconds: 2500));
    if (!mounted) return;

    // Check if onboarding has been shown before
    final prefs = await SharedPreferences.getInstance();
    final onboardingDone = prefs.getBool('onboarding_done') ?? false;

    if (!mounted) return;

    if (!onboardingDone) {
      // First launch → show onboarding
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const OnboardingScreen()),
      );
    } else {
      // Returning user → show PIN screen
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const PinScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1565C0), // StatEduc brand blue
      body: FadeTransition(
        opacity: _fadeAnim,
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // ── Animated GIF logo ────────────────────────────────────────
              Container(
                width: 140,
                height: 140,
                decoration: BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.25),
                      blurRadius: 20,
                      offset: const Offset(0, 8),
                    ),
                  ],
                ),
                padding: const EdgeInsets.all(12),
                child: ClipOval(
                  child: Image.asset(
                    'assets/icon/icon.png',
                    fit: BoxFit.cover,
                  ),
                ),
              ),

              const SizedBox(height: 32),

              // ── App name ─────────────────────────────────────────────────
              const Text(
                'StatEduc',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 36,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 2.0,
                ),
              ),

              const SizedBox(height: 8),

              // ── Tagline ──────────────────────────────────────────────────
              Text(
                'Collecte de données éducatives',
                style: TextStyle(
                  color: Colors.white.withOpacity(0.85),
                  fontSize: 14,
                  letterSpacing: 0.5,
                ),
              ),

              const SizedBox(height: 60),

              // ── Loading indicator ────────────────────────────────────────
              SizedBox(
                width: 24,
                height: 24,
                child: CircularProgressIndicator(
                  color: Colors.white.withOpacity(0.7),
                  strokeWidth: 2.5,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
