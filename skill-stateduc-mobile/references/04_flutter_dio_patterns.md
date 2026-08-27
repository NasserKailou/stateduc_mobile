# Patterns Flutter / Dio — Bonnes Pratiques

---

## 1. Politique FAIL-OPEN pour les guards de cohérence réseau

**Principe** : Un guard qui vérifie une condition côté serveur AVANT d'envoyer
des données doit être FAIL-OPEN sur les erreurs réseau.
Seule une réponse CONFIRMANT un problème doit bloquer.

```dart
/// _checkYearConsistency() — politique FAIL-OPEN
///
/// FAIL-CLOSED uniquement si le serveur répond ET confirme un vrai mismatch.
/// FAIL-OPEN sur toute erreur réseau (timeout, 404, JSON malformé...).
Future<bool> _checkYearConsistency(User user) async {
  final mobileCode = int.tryParse(_codeyear ?? '') ?? 0;
  if (mobileCode <= 0) {
    _error = 'Année de collecte non définie sur le mobile.';
    notifyListeners();
    return false;
  }

  try {
    final serverYear = await _api.fetchServerActiveYear(user.login);

    if (serverYear.code != mobileCode) {
      // ── SEUL CAS DE BLOCAGE : mismatch CONFIRMÉ ──────────────────────
      _error = 'Incohérence d\'année :\n'
               '• Mobile : $_libyear (code $mobileCode)\n'
               '• Serveur : ${serverYear.libelle} (code ${serverYear.code})';
      notifyListeners();
      return false;
    }

    debugPrint('[DataEntry] année OK: mobile=$mobileCode server=${serverYear.code}');
    return true;

  } on ApiException catch (e) {
    // Serveur UP mais réponse anormale → LAISSER PASSER
    debugPrint('[DataEntry] ApiException → fail-open: ${e.message}');
    return true;

  } catch (e) {
    // Timeout, réseau KO → LAISSER PASSER
    debugPrint('[DataEntry] réseau KO → fail-open: $e');
    return true;
  }
}
```

---

## 2. fetchServerActiveYear() avec timeout court dédié

**Principe** : Ne jamais hériter du timeout global Dio pour des endpoints légers.
Un check d'année ne doit pas bloquer 60s.

```dart
static const Duration _kYearCheckTimeout = Duration(seconds: 8);

Future<({int code, String libelle})> fetchServerActiveYear(String login) async {
  final encodedLogin = Uri.encodeComponent(login);
  late Response<dynamic> response;

  try {
    response = await _dio.get(
      'annees_ws.php/active/$encodedLogin',
      options: Options(
        responseType: ResponseType.plain,
        sendTimeout:    _kYearCheckTimeout,
        receiveTimeout: _kYearCheckTimeout,
      ),
    ).timeout(
      _kYearCheckTimeout,
      onTimeout: () => throw ApiException(
          'Timeout (${_kYearCheckTimeout.inSeconds}s) — serveur trop lent'),
    );
  } on DioException catch (e) {
    throw ApiException('Erreur réseau : ${e.message ?? e.type.name}');
  }

  final statusCode = response.statusCode ?? 0;
  if (statusCode == 401) throw ApiException('Accès refusé (401)');
  if (statusCode == 404) throw ApiException('Endpoint introuvable (404)');
  if (statusCode >= 300) throw ApiException('Erreur serveur ($statusCode)');

  final rawBody = response.data?.toString().trim() ?? '';
  if (rawBody.isEmpty) throw ApiException('Réponse vide');

  dynamic parsed;
  try {
    parsed = json.decode(rawBody);
  } catch (_) {
    throw ApiException('Réponse non-JSON : $rawBody');
  }

  final seData = parsed is Map ? (parsed['se_data'] ?? parsed) : null;
  if (seData is! Map<String, dynamic>) {
    throw ApiException('Réponse inattendue : $parsed');
  }

  final code    = (seData['code']    as num?)?.toInt() ?? 0;
  final libelle = (seData['libelle'] as String?)        ?? '';
  if (code <= 0) {
    throw ApiException('Année active serveur non définie (code=$code)');
  }

  debugPrint('[ApiService] fetchServerActiveYear: code=$code libelle=$libelle');
  return (code: code, libelle: libelle);
}
```

---

## 3. Dropdown années — ExpansionTile accordéon

**Comportement** :
- Fermé : seule l'année active est visible
- Ouvert : les autres années apparaissent avec un tap → dialog de confirmation

```dart
Widget _buildYearTab(AuthProvider auth) {
  final years   = auth.schoolYears;
  final active  = auth.activeYear;
  final loading = auth.yearsLoading;
  final otherYears = years.where((y) => y.code != active?.code).toList();

  return Card(
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(10),
      side: BorderSide(color: Theme.of(context).colorScheme.primary, width: 1.5),
    ),
    child: Theme(
      data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
      child: ExpansionTile(
        leading: Icon(Icons.calendar_today,
            color: Theme.of(context).colorScheme.primary),
        title: Text(
          active?.libelle ?? 'Aucune année en session',
          style: TextStyle(
            fontWeight: FontWeight.w700, fontSize: 15,
            color: active != null
                ? Theme.of(context).colorScheme.primary
                : Theme.of(context).colorScheme.outline,
          ),
        ),
        subtitle: const Text('Année en session — appuyer pour changer',
            style: TextStyle(fontSize: 11)),
        trailing: Icon(Icons.expand_more,
            color: Theme.of(context).colorScheme.primary),
        children: otherYears.isEmpty
          ? [Padding(
              padding: const EdgeInsets.all(16),
              child: Text('Aucune autre année disponible.',
                  style: Theme.of(context).textTheme.bodySmall))]
          : otherYears.map((y) => _buildYearItem(auth, y)).toList(),
      ),
    ),
  );
}

Widget _buildYearItem(AuthProvider auth, SchoolYear year) {
  return InkWell(
    onTap: () => _selectYear(auth, year),
    child: Container(
      decoration: BoxDecoration(
        border: Border(top: BorderSide(
          color: Theme.of(context).colorScheme.outlineVariant, width: 0.5))),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(children: [
        Icon(Icons.radio_button_unchecked, size: 20,
            color: Theme.of(context).colorScheme.onSurfaceVariant),
        const SizedBox(width: 12),
        Expanded(child: Text(year.libelle, style: const TextStyle(fontSize: 14))),
        Icon(Icons.chevron_right, size: 18,
            color: Theme.of(context).colorScheme.onSurfaceVariant),
      ]),
    ),
  );
}
```

---

## 4. Dialog de confirmation avant changement d'année

```dart
Future<void> _selectYear(AuthProvider auth, SchoolYear year) async {
  final currentYear = auth.activeYear;

  final confirmed = await showDialog<bool>(
    context: context,
    barrierDismissible: false, // choix explicite obligatoire
    builder: (ctx) => AlertDialog(
      icon: const Icon(Icons.calendar_today_outlined, size: 32),
      title: const Text('Changer l\'année active ?'),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (currentYear != null) ...[
            Text('Année actuelle :',
                style: TextStyle(color: Theme.of(ctx).colorScheme.outline,
                    fontSize: 12)),
            Text(currentYear.libelle,
                style: const TextStyle(fontWeight: FontWeight.w500)),
            const SizedBox(height: 12),
          ],
          Text('Nouvelle année :',
              style: TextStyle(color: Theme.of(ctx).colorScheme.outline,
                  fontSize: 12)),
          Text(year.libelle,
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15,
                  color: Theme.of(ctx).colorScheme.primary)),
          const SizedBox(height: 12),
          const Text('Les formulaires seront rechargés pour l\'année sélectionnée.',
              style: TextStyle(fontSize: 12)),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(ctx).pop(false),
          child: const Text('Annuler'),
        ),
        FilledButton(
          onPressed: () => Navigator.of(ctx).pop(true),
          child: const Text('Confirmer'),
        ),
      ],
    ),
  );

  if (confirmed != true || !mounted) return;

  await auth.setActiveYear(year);
  if (mounted) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text('Année active : ${year.libelle}'),
      backgroundColor: Theme.of(context).colorScheme.primary,
    ));
  }
}
```

---

## 5. Normalisation des valeurs serveur (tableaux vs scalaires)

**Problème** : Le serveur peut retourner `{"val": [5, "Oui"]}` ou `{"val": "Oui"}` ou `{"val": 5}`.

```dart
/// Normalise une valeur reçue du serveur : extrait le code si c'est un tableau.
String normalizeServerValue(dynamic rawVal) {
  if (rawVal == null) return '';
  if (rawVal is List) {
    return rawVal.isNotEmpty ? rawVal[0].toString() : '';
  }
  return rawVal.toString();
}

// Usage dans _autoReloadFromServerBackground()
serverData.forEach((key, rawVal) {
  formData[key] = normalizeServerValue(rawVal);
});
```

---

## 6. Détection changement d'année et rechargement formulaire

```dart
// school_data_screen.dart — dans Consumer2.builder
String? _lastInitYear;

@override
Widget build(BuildContext context) {
  return Consumer2<AuthProvider, DataEntryProvider>(
    builder: (context, auth, dataEntry, _) {
      final currentYearCode = auth.effectiveYearCode;

      // Recharger si l'année a changé depuis le dernier init
      if (_lastInitYear != null && _lastInitYear != currentYearCode) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (mounted) _doInitForSchool();
        });
      }
      _lastInitYear = currentYearCode;

      return /* ... */;
    },
  );
}
```

---

## 7. Configuration Dio — timeouts recommandés

```dart
_dio = Dio(BaseOptions(
  baseUrl: serverUrl,
  connectTimeout: const Duration(seconds: 30),  // connexion TCP
  receiveTimeout: const Duration(seconds: 600), // réception données (gros formulaires)
  sendTimeout:    const Duration(seconds: 60),  // envoi données
));

// Timeout court DÉDIÉ pour les endpoints légers (year check, ping)
static const Duration _kYearCheckTimeout = Duration(seconds: 8);
static const Duration _kPingTimeout       = Duration(seconds: 5);
```

---

## 8. Structure modèle SchoolYear

```dart
class SchoolYear {
  final int    code;    // CODE_ANNEE en base
  final String libelle; // ex: "2026/2027"
  final int    ordre;   // tri

  const SchoolYear({
    required this.code,
    required this.libelle,
    required this.ordre,
  });

  factory SchoolYear.fromJson(Map<String, dynamic> json) {
    return SchoolYear(
      code:    (json['code']    as num?)?.toInt() ?? 0,
      libelle: (json['libelle'] as String?)        ?? '',
      ordre:   (json['ordre']   as num?)?.toInt() ?? 0,
    );
  }

  // Cache SQLite
  Map<String, dynamic> toMap() => {
    'code':    code,
    'libelle': libelle,
    'ordre':   ordre,
  };
}
```

---

## 9. Vérification syntaxique Dart (sans compilateur)

```python
# scripts/syntax_check.py
# Compte les accolades structurelles (hors ${...} dans les strings)
def check_dart_braces(path):
    content = open(path).read()
    net = content.count('{') - content.count('}')
    # Note: ${...} dans les strings comptent comme +1{ +1} → net inchangé
    # Les commentaires contenant { ou } fausseront le compteur
    # → Utiliser uniquement comme approximation, pas comme vérité absolue
    return net  # 0 = probablement OK
```

**Attention** : Le compteur Python peut donner des faux positifs si des accolades
apparaissent dans des commentaires `//` ou des strings. Un net=1 avec des
commentaires `//` contenant `{` est un faux positif. Toujours vérifier
dans le contexte du fichier.
