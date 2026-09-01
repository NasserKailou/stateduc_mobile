import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../campaigns/campaign_list_screen.dart';

/// PinScreen — entry point for all auth flows.
///
/// Handles three modes determined by AuthState:
///   1. firstTimeSetup  → PIN creation + 3 mandatory security questions
///   2. needsServerLogin → server URL + login/password form
///   3. pinRequired      → PIN unlock pad
///                          ↳ after ≥3 failed attempts + security configured:
///                            "PIN oublié ?" → 3-question recovery flow
///
/// Session 50: 3-question security system
///   - Setup: 3 mandatory answers (impossible to finalise without them)
///   - Unlock: "PIN oublié ?" appears ONLY after 3 consecutive failed attempts
///             AND if security answers are configured
///   - Recovery: ≥2/3 correct answers → new PIN (with confirmation)
///   - Migration: existing accounts without answers → prompt to configure
///
/// Mirrors:
///   page_index.js  → init_connexion(), stmPageIndex.displayIndex()
///   page_settings.js → PIN change, security question
///   users.js        → stmUser.connect()
class PinScreen extends StatefulWidget {
  const PinScreen({super.key});

  @override
  State<PinScreen> createState() => _PinScreenState();
}

class _PinScreenState extends State<PinScreen> {
  // ─── Controllers ─────────────────────────────────────────────────────────
  final _pinController = TextEditingController();
  final _confirmPinController = TextEditingController();

  // Security answers for setup (3 questions)
  final _secA1Controller = TextEditingController();
  final _secA2Controller = TextEditingController();
  final _secA3Controller = TextEditingController();

  final _serverUrlController = TextEditingController();
  final _loginController = TextEditingController();
  final _passwordController = TextEditingController();

  bool _obscurePassword = true;

  // Recovery flow state
  bool _showForgotPin = false;
  // New-PIN confirmation in recovery
  final _newPinController = TextEditingController();
  final _confirmNewPinController = TextEditingController();
  // Recovery answers (3 questions)
  final _recA1Controller = TextEditingController();
  final _recA2Controller = TextEditingController();
  final _recA3Controller = TextEditingController();

  // Migration prompt: shown after server login if security answers not set
  bool _showMigrationPrompt = false;
  // Migration setup answers
  final _migA1Controller = TextEditingController();
  final _migA2Controller = TextEditingController();
  final _migA3Controller = TextEditingController();

  @override
  void initState() {
    super.initState();
    // Trigger initialization
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AuthProvider>().initialize().then((_) {
        final auth = context.read<AuthProvider>();
        if (auth.serverUrl != null) {
          _serverUrlController.text = auth.serverUrl!;
        }
        if (auth.storedLogin != null) {
          _loginController.text = auth.storedLogin!;
        }
      });
    });
  }

  @override
  void dispose() {
    _pinController.dispose();
    _confirmPinController.dispose();
    _secA1Controller.dispose();
    _secA2Controller.dispose();
    _secA3Controller.dispose();
    _serverUrlController.dispose();
    _loginController.dispose();
    _passwordController.dispose();
    _newPinController.dispose();
    _confirmNewPinController.dispose();
    _recA1Controller.dispose();
    _recA2Controller.dispose();
    _recA3Controller.dispose();
    _migA1Controller.dispose();
    _migA2Controller.dispose();
    _migA3Controller.dispose();
    super.dispose();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, auth, _) {
        if (auth.state == AuthState.unknown) {
          return const Scaffold(
            body: Center(child: CircularProgressIndicator()),
          );
        }
        if (auth.isLoggedIn) {
          // Check migration: logged in but no security answers configured
          if (!auth.hasSecurityAnswers && !_showMigrationPrompt) {
            // Schedule migration prompt after frame
            WidgetsBinding.instance.addPostFrameCallback((_) {
              if (mounted) setState(() => _showMigrationPrompt = true);
            });
          }
          if (_showMigrationPrompt) {
            return Scaffold(
              backgroundColor: Theme.of(context).colorScheme.surface,
              body: SafeArea(
                child: SingleChildScrollView(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
                  child: Column(
                    children: [
                      _buildLogo(),
                      const SizedBox(height: 32),
                      _buildMigrationSetup(auth),
                    ],
                  ),
                ),
              ),
            );
          }
          // Navigate to campaign list
          WidgetsBinding.instance.addPostFrameCallback((_) {
            Navigator.of(context).pushReplacement(
              MaterialPageRoute(builder: (_) => const CampaignListScreen()),
            );
          });
          return const Scaffold(
            body: Center(child: CircularProgressIndicator()),
          );
        }

        return Scaffold(
          backgroundColor: Theme.of(context).colorScheme.surface,
          body: SafeArea(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
              child: Column(
                children: [
                  _buildLogo(),
                  const SizedBox(height: 32),
                  // Error banner
                  if (auth.error != null) _buildErrorBanner(auth),
                  const SizedBox(height: 8),
                  // Body depends on state
                  if (auth.state == AuthState.firstTimeSetup)
                    _buildSetupPin(auth)
                  else if (auth.state == AuthState.needsServerLogin)
                    _buildServerLogin(auth)
                  else if (_showForgotPin)
                    _buildForgotPin(auth)
                  else
                    _buildPinEntry(auth),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  // ─── Logo ───────────────────────────────────────────────────────────────────
  // Affiche l'en-tête institutionnel complet :
  //   1. "République du Burundi"          — italic, même police que StatEduc
  //   2. "Ministère de l'Éducation Nationale" — italic, même police que StatEduc
  //   3. Drapeau du Burundi (Flag_of_country.png) — image avec ombre
  //   4. "StatEduc"                        — titre principal bold
  //   5. "Collecte de données éducatives"  — sous-titre
  Widget _buildLogo() {
    final primaryColor = Theme.of(context).colorScheme.primary;
    final labelStyle = Theme.of(context).textTheme.headlineMedium?.copyWith(
          fontStyle: FontStyle.italic,
          fontWeight: FontWeight.w600,
          color: primaryColor,
        );
    return Column(
      children: [
        // ── Ligne 1 : République du Burundi ─────────────────────────────────
        Text('République du Burundi', style: labelStyle),
        const SizedBox(height: 2),
        // ── Ligne 2 : Ministère de l'Éducation Nationale ────────────────────
        //Text("Ministère de l'Éducation Nationale", style: labelStyle),
        //const SizedBox(height: 1),

        // ── Drapeau du Burundi — encadré avec ombre légère ──────────────────
        Container(
          width: 96,
          height: 64,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(6),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.18),
                blurRadius: 8,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: Image.asset(
              'assets/icon/Flag_of_country.png',
              fit: BoxFit.cover,
              // Repli sur l'icône school si l'asset est introuvable
              errorBuilder: (_, __, ___) => Icon(
                Icons.school,
                size: 64,
                color: Theme.of(context).colorScheme.primary,
              ),
            ),
          ),
        ),
        const SizedBox(height: 12),
        Text('StatEduc',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                fontWeight: FontWeight.bold,
                color: Theme.of(context).colorScheme.primary)),
        const SizedBox(height: 4),
        Text('Collecte de données éducatives',
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Theme.of(context).colorScheme.onSurfaceVariant)),
      ],
    );
  }

  // ─── Error banner ───────────────────────────────────────────────────────────
  Widget _buildErrorBanner(AuthProvider auth) {
    return Container(
      width: double.infinity,
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
            child: Text(auth.error!,
                style: TextStyle(
                    color: Theme.of(context).colorScheme.onErrorContainer)),
          ),
          IconButton(
            icon: const Icon(Icons.close, size: 18),
            onPressed: auth.clearError,
            color: Theme.of(context).colorScheme.onErrorContainer,
          ),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SETUP PIN (first time) — 3 mandatory security questions
  // ═══════════════════════════════════════════════════════════════════════════
  Widget _buildSetupPin(AuthProvider auth) {
    final questions = auth.securityQuestions;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text('Première configuration',
                style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 8),
            const Text(
                'Créez un code PIN pour sécuriser l\'accès à l\'application.'),
            const SizedBox(height: 20),
            TextField(
              controller: _pinController,
              keyboardType: TextInputType.number,
              obscureText: true,
              maxLength: 8,
              decoration: const InputDecoration(
                labelText: 'Code PIN (4 à 8 chiffres)',
                prefixIcon: Icon(Icons.lock_outline),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _confirmPinController,
              keyboardType: TextInputType.number,
              obscureText: true,
              maxLength: 8,
              decoration: const InputDecoration(
                labelText: 'Confirmer le PIN',
                prefixIcon: Icon(Icons.lock),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 20),
            // ── 3 mandatory security questions ───────────────────────────
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Theme.of(context)
                    .colorScheme
                    .primaryContainer
                    .withOpacity(0.3),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: Theme.of(context).colorScheme.primary.withOpacity(0.3),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(Icons.security,
                          size: 18,
                          color: Theme.of(context).colorScheme.primary),
                      const SizedBox(width: 6),
                      Text(
                        'Questions de sécurité (obligatoires)',
                        style: Theme.of(context)
                            .textTheme
                            .titleSmall
                            ?.copyWith(
                                color: Theme.of(context).colorScheme.primary),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Ces réponses vous permettront de récupérer votre PIN si vous l\'oubliez.',
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Theme.of(context)
                            .colorScheme
                            .onSurfaceVariant),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            _buildSecurityAnswerField(
              question: questions[0],
              controller: _secA1Controller,
              number: 1,
            ),
            const SizedBox(height: 12),
            _buildSecurityAnswerField(
              question: questions[1],
              controller: _secA2Controller,
              number: 2,
            ),
            const SizedBox(height: 12),
            _buildSecurityAnswerField(
              question: questions[2],
              controller: _secA3Controller,
              number: 3,
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: auth.isLoading ? null : () => _doSetupPin(auth),
              icon: auth.isLoading
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2))
                  : const Icon(Icons.check),
              label: const Text('Créer le PIN'),
            ),
          ],
        ),
      ),
    );
  }

  /// Helper: a labeled text field for a security question answer.
  Widget _buildSecurityAnswerField({
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
          decoration: InputDecoration(
            labelText: 'Votre réponse',
            hintText: 'Réponse (insensible à la casse)',
            prefixIcon: const Icon(Icons.question_answer_outlined),
            border: const OutlineInputBorder(),
            helperText: 'Espaces de début/fin ignorés automatiquement',
          ),
        ),
      ],
    );
  }

  Future<void> _doSetupPin(AuthProvider auth) async {
    final pin = _pinController.text.trim();
    final confirm = _confirmPinController.text.trim();
    if (pin.length < 4) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Le PIN doit avoir au moins 4 chiffres')));
      return;
    }
    if (pin != confirm) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Les PIN ne correspondent pas')));
      return;
    }
    // Validate all 3 security answers are filled
    final a1 = _secA1Controller.text.trim();
    final a2 = _secA2Controller.text.trim();
    final a3 = _secA3Controller.text.trim();
    if (a1.isEmpty || a2.isEmpty || a3.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text(
              'Les 3 réponses aux questions de sécurité sont obligatoires')));
      return;
    }
    await auth.setupPin(
      pin: pin,
      securityAnswers: [a1, a2, a3],
    );
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SERVER LOGIN
  // ═══════════════════════════════════════════════════════════════════════════
  Widget _buildServerLogin(AuthProvider auth) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text('Connexion au serveur',
                style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 16),
            TextField(
              controller: _serverUrlController,
              keyboardType: TextInputType.url,
              autocorrect: false,
              decoration: const InputDecoration(
                labelText: 'URL du serveur',
                hintText: 'https://stateduc.mineduc.gov.bi/',
                helperText: 'Ex : https://stateduc.mineduc.gov.bi/ ou http://192.168.1.100:8083/StatEduc',
                helperMaxLines: 2,
                prefixIcon: Icon(Icons.dns_outlined),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _loginController,
              decoration: const InputDecoration(
                labelText: 'Identifiant',
                prefixIcon: Icon(Icons.person_outline),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _passwordController,
              obscureText: _obscurePassword,
              decoration: InputDecoration(
                labelText: 'Mot de passe',
                prefixIcon: const Icon(Icons.lock_outline),
                border: const OutlineInputBorder(),
                suffixIcon: IconButton(
                  icon: Icon(_obscurePassword
                      ? Icons.visibility_off_outlined
                      : Icons.visibility_outlined),
                  onPressed: () =>
                      setState(() => _obscurePassword = !_obscurePassword),
                ),
              ),
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: auth.isLoading ? null : () => _doServerLogin(auth),
              icon: auth.isLoading
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2))
                  : const Icon(Icons.login),
              label: const Text('Se connecter'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _doServerLogin(AuthProvider auth) async {
    // Trim whitespace and update field with cleaned value
    final rawUrl = _serverUrlController.text.trim();
    final login = _loginController.text.trim();
    final password = _passwordController.text;
    if (rawUrl.isEmpty || login.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Veuillez remplir tous les champs')));
      return;
    }
    // Display the normalized URL in the field so user can see what was used
    _serverUrlController.text = rawUrl;
    await auth.loginToServer(
      serverUrl: rawUrl, // ApiService.normalizeServerUrl() handles http://
      login: login,
      password: password,
    );
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // PIN ENTRY (unlock)
  // ═══════════════════════════════════════════════════════════════════════════
  Widget _buildPinEntry(AuthProvider auth) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text('Déverrouillage',
                style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 8),
            if (auth.storedLogin != null)
              Text('Connecté en tant que : ${auth.storedLogin}',
                  style: Theme.of(context).textTheme.bodySmall),
            const SizedBox(height: 16),
            // PIN pad display
            _PinPad(
              onPinComplete: (pin) async {
                final ok = await auth.unlockWithPin(pin);
                if (!ok && mounted) {
                  final attempts = auth.failedAttempts;
                  final remaining = 3 - attempts;
                  String msg = 'PIN incorrect';
                  if (attempts < 3) {
                    msg = 'PIN incorrect ($remaining tentative${remaining > 1 ? 's' : ''} avant récupération)';
                  } else {
                    msg = 'PIN incorrect — Utilisez "PIN oublié ?" pour récupérer l\'accès';
                  }
                  ScaffoldMessenger.of(context)
                      .showSnackBar(SnackBar(content: Text(msg)));
                }
              },
            ),
            const SizedBox(height: 12),
            // "PIN oublié ?" — visible ONLY after ≥3 failed attempts
            // AND security answers are configured
            if (auth.canShowForgotPin)
              TextButton.icon(
                onPressed: () => setState(() => _showForgotPin = true),
                icon: const Icon(Icons.help_outline, size: 18),
                label: const Text('PIN oublié ?'),
                style: TextButton.styleFrom(
                  foregroundColor: Theme.of(context).colorScheme.error,
                ),
              ),
            // Server re-login link
            TextButton.icon(
              onPressed: () => auth.logout(),
              icon: const Icon(Icons.swap_horiz, size: 18),
              label: const Text('Changer de compte'),
            ),
          ],
        ),
      ),
    );
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // FORGOT PIN — 3-question recovery screen
  // ═══════════════════════════════════════════════════════════════════════════
  Widget _buildForgotPin(AuthProvider auth) {
    final questions = auth.securityQuestions;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text('Récupération du PIN',
                style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 8),
            Text(
              'Répondez à au moins 2 questions sur 3 pour réinitialiser votre PIN.',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: Theme.of(context).colorScheme.onSurfaceVariant,
                  ),
            ),
            const SizedBox(height: 16),
            // Question 1
            _buildSecurityAnswerField(
              question: questions[0],
              controller: _recA1Controller,
              number: 1,
            ),
            const SizedBox(height: 12),
            // Question 2
            _buildSecurityAnswerField(
              question: questions[1],
              controller: _recA2Controller,
              number: 2,
            ),
            const SizedBox(height: 12),
            // Question 3
            _buildSecurityAnswerField(
              question: questions[2],
              controller: _recA3Controller,
              number: 3,
            ),
            const SizedBox(height: 20),
            // New PIN fields
            const Divider(),
            const SizedBox(height: 12),
            Text('Nouveau code PIN',
                style: Theme.of(context).textTheme.titleSmall),
            const SizedBox(height: 8),
            TextField(
              controller: _newPinController,
              keyboardType: TextInputType.number,
              obscureText: true,
              maxLength: 8,
              decoration: const InputDecoration(
                labelText: 'Nouveau PIN (4 à 8 chiffres)',
                prefixIcon: Icon(Icons.lock_outline),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _confirmNewPinController,
              keyboardType: TextInputType.number,
              obscureText: true,
              maxLength: 8,
              decoration: const InputDecoration(
                labelText: 'Confirmer le nouveau PIN',
                prefixIcon: Icon(Icons.lock),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: auth.isLoading ? null : () => _doResetPinThreeQ(auth),
              icon: auth.isLoading
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2))
                  : const Icon(Icons.lock_reset),
              label: const Text('Réinitialiser le PIN'),
            ),
            const SizedBox(height: 8),
            TextButton(
              onPressed: () {
                setState(() => _showForgotPin = false);
                auth.clearError();
                _recA1Controller.clear();
                _recA2Controller.clear();
                _recA3Controller.clear();
                _newPinController.clear();
                _confirmNewPinController.clear();
              },
              child: const Text('Annuler'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _doResetPinThreeQ(AuthProvider auth) async {
    final newPin = _newPinController.text.trim();
    final confirmPin = _confirmNewPinController.text.trim();

    if (newPin.length < 4) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Le nouveau PIN doit avoir au moins 4 chiffres')));
      return;
    }
    if (newPin != confirmPin) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Les PIN ne correspondent pas')));
      return;
    }

    final answers = [
      _recA1Controller.text,
      _recA2Controller.text,
      _recA3Controller.text,
    ];

    final ok = await auth.resetPinWithThreeAnswers(answers, newPin);
    if (ok && mounted) {
      setState(() => _showForgotPin = false);
      _recA1Controller.clear();
      _recA2Controller.clear();
      _recA3Controller.clear();
      _newPinController.clear();
      _confirmNewPinController.clear();
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('PIN réinitialisé avec succès'),
          backgroundColor: Colors.green));
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // MIGRATION — existing accounts without security answers
  // Shown once after first successful login when no answers are configured.
  // ═══════════════════════════════════════════════════════════════════════════
  Widget _buildMigrationSetup(AuthProvider auth) {
    final questions = auth.securityQuestions;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              children: [
                Icon(Icons.security_update_good,
                    color: Theme.of(context).colorScheme.primary),
                const SizedBox(width: 8),
                Expanded(
                  child: Text('Configuration sécurité',
                      style: Theme.of(context).textTheme.titleLarge),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: Theme.of(context)
                    .colorScheme
                    .tertiaryContainer
                    .withOpacity(0.4),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                'Pour renforcer la sécurité de votre compte, veuillez configurer '
                'les 3 questions de sécurité. Elles vous permettront de récupérer '
                'votre PIN en cas d\'oubli.',
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            ),
            const SizedBox(height: 20),
            _buildSecurityAnswerField(
              question: questions[0],
              controller: _migA1Controller,
              number: 1,
            ),
            const SizedBox(height: 12),
            _buildSecurityAnswerField(
              question: questions[1],
              controller: _migA2Controller,
              number: 2,
            ),
            const SizedBox(height: 12),
            _buildSecurityAnswerField(
              question: questions[2],
              controller: _migA3Controller,
              number: 3,
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: auth.isLoading ? null : () => _doMigrationSetup(auth),
              icon: auth.isLoading
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2))
                  : const Icon(Icons.save_outlined),
              label: const Text('Enregistrer et continuer'),
            ),
            const SizedBox(height: 8),
            TextButton(
              onPressed: () {
                // User skips — dismiss and go to campaigns
                setState(() => _showMigrationPrompt = false);
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute(builder: (_) => const CampaignListScreen()),
                );
              },
              child: const Text('Ignorer pour l\'instant'),
              style: TextButton.styleFrom(
                foregroundColor: Theme.of(context).colorScheme.onSurfaceVariant,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _doMigrationSetup(AuthProvider auth) async {
    final a1 = _migA1Controller.text.trim();
    final a2 = _migA2Controller.text.trim();
    final a3 = _migA3Controller.text.trim();
    if (a1.isEmpty || a2.isEmpty || a3.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Les 3 réponses sont obligatoires')));
      return;
    }
    final ok = await auth.updateThreeSecurityAnswers([a1, a2, a3]);
    if (ok && mounted) {
      _migA1Controller.clear();
      _migA2Controller.clear();
      _migA3Controller.clear();
      setState(() => _showMigrationPrompt = false);
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Questions de sécurité enregistrées'),
          backgroundColor: Colors.green));
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const CampaignListScreen()),
      );
    }
  }
}

// ─── PIN Pad widget ────────────────────────────────────────────────────────
class _PinPad extends StatefulWidget {
  const _PinPad({required this.onPinComplete});
  final Future<void> Function(String pin) onPinComplete;

  @override
  State<_PinPad> createState() => _PinPadState();
}

class _PinPadState extends State<_PinPad> {
  String _entered = '';
  static const int _maxLen = 8;

  void _append(String digit) {
    if (_entered.length >= _maxLen) return;
    setState(() => _entered += digit);
  }

  void _backspace() {
    if (_entered.isEmpty) return;
    setState(() => _entered = _entered.substring(0, _entered.length - 1));
  }

  void _confirm() async {
    if (_entered.length < 4) {
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('PIN trop court')));
      return;
    }
    final pin = _entered;
    setState(() => _entered = '');
    await widget.onPinComplete(pin);
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Dot indicators
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(
            _maxLen,
            (i) => Container(
              margin: const EdgeInsets.all(4),
              width: 14,
              height: 14,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: i < _entered.length
                    ? Theme.of(context).colorScheme.primary
                    : Theme.of(context).colorScheme.outlineVariant,
              ),
            ),
          ),
        ),
        const SizedBox(height: 16),
        // Number grid
        GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 3,
          childAspectRatio: 1.6,
          children: [
            for (final d in ['1', '2', '3', '4', '5', '6', '7', '8', '9'])
              _DigitButton(digit: d, onTap: () => _append(d)),
            _DigitButton(digit: '⌫', onTap: _backspace, isIcon: true),
            _DigitButton(digit: '0', onTap: () => _append('0')),
            _DigitButton(digit: '✓', onTap: _confirm, isConfirm: true),
          ],
        ),
      ],
    );
  }
}

class _DigitButton extends StatelessWidget {
  const _DigitButton({
    required this.digit,
    required this.onTap,
    this.isIcon = false,
    this.isConfirm = false,
  });
  final String digit;
  final VoidCallback onTap;
  final bool isIcon;
  final bool isConfirm;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(4),
      child: Material(
        color: isConfirm
            ? Theme.of(context).colorScheme.primary
            : Theme.of(context).colorScheme.surfaceContainerHighest,
        borderRadius: BorderRadius.circular(8),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(8),
          child: Center(
            child: Text(
              digit,
              style: TextStyle(
                fontSize: isIcon ? 20 : 24,
                fontWeight: FontWeight.w600,
                color: isConfirm
                    ? Theme.of(context).colorScheme.onPrimary
                    : Theme.of(context).colorScheme.onSurfaceVariant,
              ),
            ),
          ),
        ),
      ),
    );
  }
}
