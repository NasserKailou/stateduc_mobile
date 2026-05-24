import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../login/pin_screen.dart';

/// SettingsScreen — Server URL config, PIN change, security question.
///
/// Mirrors:
///   page_settings.js → PIN change (change_code), security question change
///   page_index.js    → server URL setting (init_options)
class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  final _serverUrlController = TextEditingController();

  // PIN change
  final _oldPinController = TextEditingController();
  final _newPinController = TextEditingController();
  final _confirmPinController = TextEditingController();

  // Security question
  final _secQController = TextEditingController();
  final _secAController = TextEditingController();

  bool _obscureOldPin = true;
  bool _obscureNewPin = true;
  int _currentTab = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final auth = context.read<AuthProvider>();
      if (auth.serverUrl != null) {
        _serverUrlController.text = auth.serverUrl!;
      }
      if (auth.securityQuestion != null) {
        _secQController.text = auth.securityQuestion!;
      }
    });
  }

  @override
  void dispose() {
    _serverUrlController.dispose();
    _oldPinController.dispose();
    _newPinController.dispose();
    _confirmPinController.dispose();
    _secQController.dispose();
    _secAController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(builder: (context, auth, _) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Paramètres'),
          bottom: TabBar(
            onTap: (i) => setState(() => _currentTab = i),
            tabs: const [
              Tab(icon: Icon(Icons.dns_outlined), text: 'Serveur'),
              Tab(icon: Icon(Icons.lock_outline), text: 'PIN'),
              Tab(icon: Icon(Icons.help_outline), text: 'Sécurité'),
            ],
          ),
        ),
        body: IndexedStack(
          index: _currentTab,
          children: [
            _buildServerTab(auth),
            _buildPinTab(auth),
            _buildSecurityTab(auth),
          ],
        ),
      );
    });
  }

  // ─── Server tab ─────────────────────────────────────────────────────────────
  Widget _buildServerTab(AuthProvider auth) {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text('Configuration du serveur',
              style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          const Text(
              'Adresse du serveur StatEduc (ex: http://192.168.1.1)'),
          const SizedBox(height: 16),
          TextField(
            controller: _serverUrlController,
            keyboardType: TextInputType.url,
            decoration: const InputDecoration(
              labelText: 'URL du serveur',
              prefixIcon: Icon(Icons.dns_outlined),
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 16),
          if (auth.storedLogin != null)
            ListTile(
              leading: const Icon(Icons.person_outline),
              title: const Text('Utilisateur connecté'),
              subtitle: Text(auth.storedLogin!),
              contentPadding: EdgeInsets.zero,
            ),
          const Spacer(),
          ElevatedButton.icon(
            onPressed: () => _saveServerUrl(auth),
            icon: const Icon(Icons.save_outlined),
            label: const Text('Enregistrer'),
          ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: () => _confirmLogout(auth),
            icon: const Icon(Icons.logout),
            label: const Text('Se déconnecter'),
            style: OutlinedButton.styleFrom(
              foregroundColor: Theme.of(context).colorScheme.error,
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _saveServerUrl(AuthProvider auth) async {
    final url = _serverUrlController.text.trim();
    if (url.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('L\'URL ne peut pas être vide')));
      return;
    }
    await auth.updateServerUrl(url);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('URL enregistrée')));
    }
  }

  void _confirmLogout(AuthProvider auth) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Déconnexion'),
        content: const Text(
            'Voulez-vous vous déconnecter ? '
            'Vos données locales seront conservées.'),
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
            child: const Text('Déconnecter'),
          ),
        ],
      ),
    );
  }

  // ─── PIN tab ─────────────────────────────────────────────────────────────────
  Widget _buildPinTab(AuthProvider auth) {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text('Changer le code PIN',
              style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 16),
          TextField(
            controller: _oldPinController,
            keyboardType: TextInputType.number,
            obscureText: _obscureOldPin,
            maxLength: 8,
            decoration: InputDecoration(
              labelText: 'PIN actuel',
              prefixIcon: const Icon(Icons.lock_outline),
              border: const OutlineInputBorder(),
              suffixIcon: IconButton(
                icon: Icon(_obscureOldPin
                    ? Icons.visibility_off_outlined
                    : Icons.visibility_outlined),
                onPressed: () =>
                    setState(() => _obscureOldPin = !_obscureOldPin),
              ),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _newPinController,
            keyboardType: TextInputType.number,
            obscureText: _obscureNewPin,
            maxLength: 8,
            decoration: InputDecoration(
              labelText: 'Nouveau PIN',
              prefixIcon: const Icon(Icons.lock),
              border: const OutlineInputBorder(),
              suffixIcon: IconButton(
                icon: Icon(_obscureNewPin
                    ? Icons.visibility_off_outlined
                    : Icons.visibility_outlined),
                onPressed: () =>
                    setState(() => _obscureNewPin = !_obscureNewPin),
              ),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _confirmPinController,
            keyboardType: TextInputType.number,
            obscureText: true,
            maxLength: 8,
            decoration: const InputDecoration(
              labelText: 'Confirmer le nouveau PIN',
              prefixIcon: Icon(Icons.lock),
              border: OutlineInputBorder(),
            ),
          ),
          if (auth.error != null) ...[
            const SizedBox(height: 8),
            Text(auth.error!,
                style: TextStyle(
                    color: Theme.of(context).colorScheme.error)),
          ],
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: auth.isLoading ? null : () => _doChangePin(auth),
            icon: auth.isLoading
                ? const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2))
                : const Icon(Icons.check),
            label: const Text('Changer le PIN'),
          ),
        ],
      ),
    );
  }

  Future<void> _doChangePin(AuthProvider auth) async {
    final newPin = _newPinController.text.trim();
    final confirm = _confirmPinController.text.trim();
    if (newPin != confirm) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Les PIN ne correspondent pas')));
      return;
    }
    final ok =
        await auth.changePin(_oldPinController.text.trim(), newPin);
    if (ok && mounted) {
      _oldPinController.clear();
      _newPinController.clear();
      _confirmPinController.clear();
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('PIN modifié avec succès')));
    }
  }

  // ─── Security question tab ──────────────────────────────────────────────────
  Widget _buildSecurityTab(AuthProvider auth) {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text('Question de sécurité',
              style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          const Text(
              'La question de sécurité permet de réinitialiser votre PIN si vous l\'oubliez.'),
          const SizedBox(height: 16),
          TextField(
            controller: _secQController,
            decoration: const InputDecoration(
              labelText: 'Question',
              hintText: 'Ex: Nom de votre école primaire ?',
              prefixIcon: Icon(Icons.help_outline),
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _secAController,
            decoration: const InputDecoration(
              labelText: 'Réponse',
              prefixIcon: Icon(Icons.question_answer_outlined),
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: () => _doSaveSecurityQ(auth),
            icon: const Icon(Icons.save_outlined),
            label: const Text('Enregistrer'),
          ),
        ],
      ),
    );
  }

  Future<void> _doSaveSecurityQ(AuthProvider auth) async {
    final q = _secQController.text.trim();
    final a = _secAController.text.trim();
    if (q.isEmpty || a.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text('Question et réponse obligatoires')));
      return;
    }
    await auth.updateSecurityQuestion(q, a);
    if (mounted) {
      _secAController.clear();
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text('Question de sécurité mise à jour')));
    }
  }
}
