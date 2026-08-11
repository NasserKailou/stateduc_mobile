// StatEduc Mobile — Flutter rewrite
// Entry point: Splash → Onboarding (1st launch) → PinScreen

import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'providers/auth_provider.dart';
import 'providers/campaign_provider.dart';
import 'providers/data_entry_provider.dart';
import 'services/auth_service.dart';
import 'services/api_service.dart';
import 'services/database_service.dart';
import 'services/theme_rule_engine.dart';
import 'screens/splash/splash_screen.dart';

// ─── Global SSL override ─────────────────────────────────────────────────────
// Le serveur StatEduc est déployé sur un Serveur du MEN avec des certificats
//accessible a travers le liens https://stateduc.mineduc.gov.bi/
// auto-signés ou sans HTTPS. Cet override permet à tous les HttpClient créés
// dans l'application d'accepter ces certificats.
// NOTE : n'affecte que les connexions via dart:io HttpClient.
//        Dio est configuré séparément dans ApiService._internal().
class _TrustAllCertificates extends HttpOverrides { 
  @override
  HttpClient createHttpClient(SecurityContext? context) {
    return super.createHttpClient(context)
      ..badCertificateCallback =
          (X509Certificate cert, String host, int port) => true;
  }
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  // Accept all SSL certificates (self-signed servers on private networks)
  HttpOverrides.global = _TrustAllCertificates();
  // Touch the database singleton to run _onCreate if first launch
  await DatabaseService().database;
  // CORRECTION SSL-51 : purger le cache DNS SharedPreferences au démarrage.
  // Un cache 'dns_cache_*' contenant 127.0.0.1 (IP locale du serveur retournée
  // par le DNS) provoque l'erreur SSL 51 en remplaçant le hostname par l'IP
  // dans les URL HTTPS. Ce nettoyage est fait une fois à chaque démarrage.
  await ApiService.clearAllDnsCache();
  // Démarre l'application immédiatement — le seeding JSON est différé
  // après le premier frame pour éviter un ANR (freeze avant runApp).
  // seedIfEmpty() est idempotent : no-op si la table est déjà peuplée.
  runApp(const StatEducApp());
  // Seeding différé : s'exécute après le premier frame Flutter,
  // sur le main isolate mais hors de la phase de démarrage critique.
  WidgetsBinding.instance.addPostFrameCallback((_) {
    ThemeRuleSeederHelper.seedIfEmpty(db: DatabaseService());
  });
}

class StatEducApp extends StatelessWidget {
  const StatEducApp({super.key});

  @override
  Widget build(BuildContext context) {
    // Instantiate singletons once at root
    final db = DatabaseService();
    final api = ApiService();
    final auth = AuthService();

    return MultiProvider(
      providers: [
        // ── Singletons (not ChangeNotifiers) ─────────────────────────────
        Provider<DatabaseService>.value(value: db),
        Provider<ApiService>.value(value: api),
        Provider<AuthService>.value(value: auth),

        // ── AuthProvider ─────────────────────────────────────────────────
        ChangeNotifierProvider<AuthProvider>(
          create: (_) => AuthProvider(authService: auth),
        ),

        // ── CampaignProvider — depends on db + api ────────────────────────
        ChangeNotifierProvider<CampaignProvider>(
          create: (_) => CampaignProvider(db: db, api: api),
        ),

        // ── DataEntryProvider — depends on db + api ───────────────────────
        ChangeNotifierProvider<DataEntryProvider>(
          create: (_) => DataEntryProvider(db: db, api: api),
        ),
      ],
      child: MaterialApp(
        title: 'StatEduc',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          colorScheme: ColorScheme.fromSeed(
            seedColor: const Color(0xFF1565C0),
            brightness: Brightness.light,
          ),
          useMaterial3: true,
          appBarTheme: const AppBarTheme(
            backgroundColor: Color(0xFF1565C0),
            foregroundColor: Colors.white,
            elevation: 2,
          ),
          elevatedButtonTheme: ElevatedButtonThemeData(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF1565C0),
              foregroundColor: Colors.white,
            ),
          ),
          chipTheme: ChipThemeData(
            selectedColor: const Color(0xFF1565C0),
            secondarySelectedColor: const Color(0xFF1565C0),
            labelStyle: const TextStyle(fontSize: 13),
            disabledColor: Colors.grey.shade200,
            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 0),
            shape: const StadiumBorder(),
          ),
          cardTheme: const CardThemeData(
            elevation: 2,
            margin: EdgeInsets.symmetric(horizontal: 0, vertical: 4),
          ),
        ),
        // ── Routing ──────────────────────────────────────────────────────
        // SplashScreen handles:
        //   - Logo display (logo.gif) during app startup
        //   - First launch detection → OnboardingScreen
        //   - Returning user → PinScreen
        home: const SplashScreen(),
      ),
    );
  }
}
