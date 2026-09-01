// check_year_consistency.dart — Vérification d'année FAIL-OPEN
//
// USAGE: Copier/adapter dans stateduc_flutter/lib/providers/data_entry_provider.dart
//
// TAGS: AK-YEAR-MULTI
//
// PROBLÈME RÉSOLU:
//   _checkYearConsistency() était FAIL-CLOSED → bloquait l'envoi de données
//   en cas d'erreur réseau (ApiException, timeout, etc.).
//   Solution: tout catch retourne true (laisser passer) sauf mismatch confirmé.
//
// RÈGLE FONDAMENTALE:
//   On ne bloque QUE si le serveur répond ET l'année diffère.
//   Si réseau KO → on laisse passer (fail-open).

import 'package:flutter/foundation.dart';
import '../services/api_service.dart';

// ─────────────────────────────────────────────────────────────────────────────
// MÉTHODE — _checkYearConsistency()
// ─────────────────────────────────────────────────────────────────────────────

/// Vérifie que l'année locale (sélectionnée dans l'UI) correspond à
/// l'année active configurée sur le serveur.
///
/// Politique **FAIL-OPEN**:
/// - `true`  → données autorisées à passer (cohérent OU réseau KO)
/// - `false` → données BLOQUÉES (serveur répond + année différente)
///
/// Ne jamais bloquer sur une erreur réseau: c'est au serveur de valider
/// en dernier ressort (double validation). L'app mobile ne doit pas empêcher
/// l'agent de terrain de travailler hors-ligne ou en réseau instable.
///
/// [login]      Login de l'utilisateur connecté
/// [localYear]  Année sélectionnée dans l'UI (depuis AuthProvider)
/// [apiService] Instance du service API (injectée par le provider)
Future<bool> _checkYearConsistency({
  required String login,
  required String localYear,
  required ApiService apiService,
}) async {
  // Pas d'année locale sélectionnée → laisser passer (serveur décidera)
  if (localYear.isEmpty) {
    debugPrint('[DataEntry] Pas d\'année locale → fail-open');
    return true;
  }

  try {
    // Interroger le serveur avec un timeout court (8s — défini dans ApiService)
    final serverYear = await apiService.fetchServerActiveYear(login);

    // Normaliser pour comparaison insensible à la casse et aux espaces
    final serverCode = serverYear.code.toString().trim().toLowerCase();
    final localCode  = localYear.trim().toLowerCase();

    if (serverCode == localCode) {
      debugPrint('[DataEntry] Années cohérentes: $localYear == $serverCode ✓');
      return true;  // ✓ cohérent — laisser passer
    }

    // ⚠ Mismatch confirmé — bloquer et informer l'utilisateur
    debugPrint(
      '[DataEntry] MISMATCH année: local=$localYear ≠ serveur=$serverCode → BLOQUÉ',
    );
    return false;

  } on ApiException catch (e) {
    // Erreur API connue (ex: 404 aucune année active, timeout)
    // → FAIL-OPEN: on ne peut pas confirmer un mismatch
    debugPrint('[DataEntry] ApiException → fail-open: ${e.message}');
    return true;

  } catch (e) {
    // Toute autre erreur réseau (SocketException, TimeoutException, etc.)
    // → FAIL-OPEN: réseau indisponible ≠ incohérence confirmée
    debugPrint('[DataEntry] Réseau KO → fail-open: $e');
    return true;
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// UTILISATION DANS sendData()
// ─────────────────────────────────────────────────────────────────────────────

/*
// Dans DataEntryProvider.sendData():

Future<void> sendData({
  required String login,
  required Map<String, dynamic> formData,
  required String anneeCode,
}) async {
  // 1. Vérifier cohérence d'année avant envoi
  final yearOk = await _checkYearConsistency(
    login:      login,
    localYear:  anneeCode,
    apiService: _apiService,
  );

  if (!yearOk) {
    // Afficher un message à l'utilisateur
    _errorMessage = 'Année en session ($anneeCode) ne correspond pas '
                    'à l\'année active sur le serveur. '
                    'Mettez à jour votre année dans les Paramètres.';
    notifyListeners();
    return;
  }

  // 2. Procéder à l'envoi
  final result = await _apiService.sendData(
    login:      login,
    formData:   formData,
    anneeCode:  anneeCode,
  );
  // ...
}
*/

// ─────────────────────────────────────────────────────────────────────────────
// DIAGRAMME DE DÉCISION
// ─────────────────────────────────────────────────────────────────────────────

/*
_checkYearConsistency(login, localYear):
  │
  ├── localYear vide?
  │     └── return true (fail-open)
  │
  ├── fetchServerActiveYear() ──┐
  │     │                       │
  │     ├── Succès:             │
  │     │   serverCode == localCode? ──┐
  │     │     ├── Oui → return true   │
  │     │     └── Non → return false  │ ← seul cas de blocage
  │     │                             │
  │     ├── ApiException:             │
  │     │   return true (fail-open)   │
  │     │                             │
  │     └── catch(e):                 │
  │         return true (fail-open)   │
  └─────────────────────────────┘
*/
