import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../campaigns/campaign_list_screen.dart';

/// PinScreen — entry point for all auth flows.
///
/// Handles three modes determined by AuthState:
///   1. firstTimeSetup  → PIN creation + optional security question
///   2. needsServerLogin → server URL + login/password form
///   3. pinRequired      → PIN unlock pad + forgot-PIN via security question
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
  final _secQController = TextEditingController();
  final _secAController = TextEditingController();

  final _serverUrlController = TextEditingController();
  final _loginController = TextEditingController();
  final _passwordController = TextEditingController();

  bool _obscurePassword = true;
  bool _showSecurityQuestion = false;
  bool _showForgotPin = false;

  // For forgot-PIN flow
  final _forgotAnswerController = TextEditingController();
  final _newPinController = TextEditingController();

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
    _secQController.dispose();
    _secAController.dispose();
    _serverUrlController.dispose();
    _loginController.dispose();
    _passwordController.dispose();
    _forgotAnswerController.dispose();
    _newPinController.dispose();
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
          // Navigate to campaign list
          WidgetsBinding.instance.addPostFrameCallback((_) {
            Navigator.of(context).pushReplacement(
              MaterialPageRoute(
                  builder: (_) => const CampaignListScreen()),
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
              padding: const EdgeInsets.symmetric(
                  horizontal: 24, vertical: 32),
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
  // Affiche le drapeau du Burundi + nom de l'application + sous-titre.
  // Le drapeau est chargé depuis assets/icon/Flag_of_country.png.
  // En cas d'erreur de chargement (asset manquant), repli sur l'icône school.
  Widget _buildLogo() {
    return Column(
      children: [
        // Drapeau du Burundi — encadré avec ombre légère pour le détacher du fond
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
                    color:
                        Theme.of(context).colorScheme.onErrorContainer)),
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
  // SETUP PIN (first time)
  // ═══════════════════════════════════════════════════════════════════════════
  Widget _buildSetupPin(AuthProvider auth) {
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
            const SizedBox(height: 8),
            Row(
              children: [
                Checkbox(
                  value: _showSecurityQuestion,
                  onChanged: (v) =>
                      setState(() => _showSecurityQuestion = v!),
                ),
                const Expanded(
                    child: Text('Ajouter une question de sécurité')),
              ],
            ),
            if (_showSecurityQuestion) ...[
              const SizedBox(height: 8),
              TextField(
                controller: _secQController,
                decoration: const InputDecoration(
                  labelText: 'Question de sécurité',
                  prefixIcon: Icon(Icons.help_outline),
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 8),
              TextField(
                controller: _secAController,
                decoration: const InputDecoration(
                  labelText: 'Réponse',
                  prefixIcon: Icon(Icons.question_answer_outlined),
                  border: OutlineInputBorder(),
                ),
              ),
            ],
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

  Future<void> _doSetupPin(AuthProvider auth) async {
    final pin = _pinController.text.trim();
    final confirm = _confirmPinController.text.trim();
    if (pin.length < 4) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Le PIN doit avoir au moins 4 chiffres')));
      return;
    }
    if (pin != confirm) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Les PIN ne correspondent pas')));
      return;
    }
    await auth.setupPin(
      pin: pin,
      securityQuestion:
          _showSecurityQuestion ? _secQController.text.trim() : null,
      securityAnswer:
          _showSecurityQuestion ? _secAController.text.trim() : null,
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
                hintText: 'http://192.168.1.100:8083/StatEduc',
                helperText: 'Ex : http://192.168.1.100:8083/StatEduc_MEN_2025',
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
              onPressed:
                  auth.isLoading ? null : () => _doServerLogin(auth),
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
          const SnackBar(
              content: Text('Veuillez remplir tous les champs')));
      return;
    }
    // Display the normalized URL in the field so user can see what was used
    _serverUrlController.text = rawUrl;
    await auth.loginToServer(
      serverUrl: rawUrl,   // ApiService.normalizeServerUrl() handles http://
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
                  ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('PIN incorrect')));
                }
              },
            ),
            const SizedBox(height: 12),
            // Forgot PIN
            if (auth.securityQuestion != null)
              TextButton(
                onPressed: () =>
                    setState(() => _showForgotPin = true),
                child: const Text('PIN oublié ?'),
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
  // FORGOT PIN
  // ═══════════════════════════════════════════════════════════════════════════
  Widget _buildForgotPin(AuthProvider auth) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text('Réinitialiser le PIN',
                style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 12),
            if (auth.securityQuestion != null) ...[
              Text('Question : ${auth.securityQuestion}',
                  style: Theme.of(context).textTheme.bodyMedium),
              const SizedBox(height: 12),
              TextField(
                controller: _forgotAnswerController,
                decoration: const InputDecoration(
                  labelText: 'Votre réponse',
                  prefixIcon: Icon(Icons.question_answer_outlined),
                  border: OutlineInputBorder(),
                ),
              ),
            ],
            const SizedBox(height: 12),
            TextField(
              controller: _newPinController,
              keyboardType: TextInputType.number,
              obscureText: true,
              maxLength: 8,
              decoration: const InputDecoration(
                labelText: 'Nouveau PIN',
                prefixIcon: Icon(Icons.lock_outline),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () => _doResetPin(auth),
              child: const Text('Réinitialiser'),
            ),
            TextButton(
              onPressed: () =>
                  setState(() => _showForgotPin = false),
              child: const Text('Annuler'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _doResetPin(AuthProvider auth) async {
    final ok = await auth.resetPinWithSecurityAnswer(
      _forgotAnswerController.text,
      _newPinController.text.trim(),
    );
    if (ok && mounted) {
      setState(() => _showForgotPin = false);
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('PIN réinitialisé avec succès')));
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
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('PIN trop court')));
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
            _DigitButton(
                digit: '⌫',
                onTap: _backspace,
                isIcon: true),
            _DigitButton(digit: '0', onTap: () => _append('0')),
            _DigitButton(
                digit: '✓',
                onTap: _confirm,
                isConfirm: true),
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
