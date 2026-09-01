// api_service_year.dart — fetchServerActiveYear() avec timeout 8s dédié
//
// USAGE: Copier/adapter dans stateduc_flutter/lib/services/api_service.dart
//
// TAGS: AK-YEAR-MULTI
//
// PROBLÈME RÉSOLU:
//   fetchServerActiveYear() héritait du timeout global Dio (60s) → bloquait
//   l'UI pendant 60s en cas de serveur inaccessible.
//   Solution: timeout 8s dédié via Options(sendTimeout/receiveTimeout) + .timeout()
//
// DÉPENDANCES: dio, dart:convert

import 'dart:convert';
import 'package:dio/dio.dart';

// ─────────────────────────────────────────────────────────────────────────────
// CONSTANTE — timeout court dédié à la vérification d'année
// ─────────────────────────────────────────────────────────────────────────────

/// Timeout dédié à fetchServerActiveYear().
/// Court (8s) pour ne pas bloquer l'UI — la vérification d'année est
/// non-bloquante (fail-open dans _checkYearConsistency).
static const Duration _kYearCheckTimeout = Duration(seconds: 8);

// ─────────────────────────────────────────────────────────────────────────────
// MODÈLE DE RETOUR
// ─────────────────────────────────────────────────────────────────────────────

/// Résultat de fetchServerActiveYear
typedef YearResult = ({int code, String libelle});

// ─────────────────────────────────────────────────────────────────────────────
// MÉTHODE — fetchServerActiveYear()
// ─────────────────────────────────────────────────────────────────────────────

/// Retourne l'année scolaire active configurée sur le serveur.
///
/// - Timeout court: 8s via [_kYearCheckTimeout]
/// - Lance [ApiException] si réseau KO ou réponse invalide
/// - L'appelant (_checkYearConsistency) doit traiter l'exception en fail-open
///
/// Exemple de réponse serveur attendue:
/// ```json
/// {"code": "2024", "libelle": "Année scolaire 2024-2025"}
/// ```
Future<YearResult> fetchServerActiveYear(String login) async {
  final encodedLogin = Uri.encodeComponent(login);

  late Response<dynamic> response;
  try {
    response = await _dio.get(
      'annees_ws.php/active/$encodedLogin',
      options: Options(
        // responseType plain → lecture brute pour parsing robuste
        responseType:   ResponseType.plain,
        // Timeout dédié court — évite d'hériter du timeout global (60s)
        sendTimeout:    _kYearCheckTimeout,
        receiveTimeout: _kYearCheckTimeout,
      ),
    ).timeout(
      _kYearCheckTimeout,
      onTimeout: () => throw ApiException(
        'Timeout (${_kYearCheckTimeout.inSeconds}s) — vérification année serveur',
      ),
    );
  } on DioException catch (e) {
    throw ApiException('Réseau KO: ${e.message}');
  }

  // ── Parser la réponse ──────────────────────────────────────────────────────
  final raw = response.data?.toString() ?? '';
  if (raw.isEmpty) {
    throw ApiException('Réponse vide du serveur pour /active/$login');
  }

  Map<String, dynamic> json;
  try {
    json = jsonDecode(raw) as Map<String, dynamic>;
  } catch (_) {
    throw ApiException('Réponse non-JSON: $raw');
  }

  // Normaliser la casse des clés (server peut retourner CODE ou code)
  final normalised = {
    for (final e in json.entries) e.key.toLowerCase(): e.value,
  };

  final codeRaw    = normalised['code']?.toString()    ?? '';
  final libelleRaw = normalised['libelle']?.toString() ?? codeRaw;

  if (codeRaw.isEmpty) {
    throw ApiException('Champ "code" manquant dans la réponse année: $raw');
  }

  // Convertir code en int si l'ancienne API retournait un int
  // Si code est une chaîne ("2024") → retourner 2024 comme int pour compatibilité
  final codeInt = int.tryParse(codeRaw) ?? 0;

  return (code: codeInt, libelle: libelleRaw);
}

// ─────────────────────────────────────────────────────────────────────────────
// USAGE DANS api_service.dart
// ─────────────────────────────────────────────────────────────────────────────

/*
class ApiService {
  static const Duration _kYearCheckTimeout = Duration(seconds: 8);

  // ... autres constantes et méthodes ...

  Future<({int code, String libelle})> fetchServerActiveYear(String login) async {
    // [coller le code de la méthode ci-dessus]
  }
}
*/

// ─────────────────────────────────────────────────────────────────────────────
// NOTES D'INTÉGRATION
// ─────────────────────────────────────────────────────────────────────────────

/*
1. Placer _kYearCheckTimeout comme static const dans la classe ApiService
   (PAS dans une méthode — const au niveau classe).

2. L'endpoint serveur est: GET /annees_ws.php/active/{login}
   Voir templates/php/annees_ws_template.php pour l'implémentation PHP.

3. En cas d'erreur réseau, l'appelant (data_entry_provider.dart) doit
   appliquer la politique FAIL-OPEN:
   catch (e) { return true; }  // laisser passer les données

4. Si le code retourné est une chaîne (ex: "2024") et non un int,
   adapter le modèle SchoolYear.fromJson() en conséquence.
   Voir templates/dart/year_dropdown_widget.dart pour le modèle complet.
*/
