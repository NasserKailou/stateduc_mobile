// =============================================================================
// api_service.dart — Service HTTP central pour l'application StatEduc Mobile
// =============================================================================
//
// Ce fichier est le SEUL point d'entrée vers le serveur REST StatEduc.
// Il est implémenté comme un SINGLETON : une seule instance est créée et partagée
// par tous les providers (AuthProvider, CampaignProvider, DataEntryProvider).
//
// CORRESPONDANCE avec le code JavaScript original (app web) :
//   getDataFromServer(servSuffix, params, callBack)    → _get(path)
//   postDataToServer(servSuffix, params, themeData)    → saveData()
//   getFormDataFromServer(servSuffix, params, ...)     → reloadData()
//   addQstHtml: GET url → then GET that url with auth  → getFormHtml()
//
// Sources JS d'origine : charge_camp.js, page_etab.js, page_new_camp.js, users.js
//
// ARCHITECTURE HTTP :
//   - Client HTTP : Dio (avec intercepteurs)
//   - Timeouts configurés (session 19) :
//       connectTimeout = 60s   (connexion initiale — temps réseau)
//       receiveTimeout = 600s  (10 min — chaîne data_save → questionnaire_ws très lente)
//       sendTimeout    = null  (désactivé — le body POST est petit, ne pas limiter)
//   - Retry automatique : 2 tentatives supplémentaires sur sendTimeout/receiveTimeout/unknown
//   - SSL : certificats auto-signés acceptés (intranet MEN)
//   - Auth : HTTP Basic (Authorization: Basic base64(login:password))
//   - Intercepteur _AuthInjectorInterceptor : ré-injecte l'en-tête auth sur chaque requête
//
// MÉTHODES PRINCIPALES :
//   authenticate()     → Authentification utilisateur (GET /user_ident.php/user/...)
//   saveData()         → Envoi du formulaire au serveur (POST /data_save.php/theme_save/...)
//   fetchRules()       → Récupération règles cohérence offline (GET /data_rules.php/...)
//   checkCoherence()   → Contrôle cohérence serveur post-envoi (GET /data_controle.php/...)
//   getFormHtml()      → Chargement HTML formulaire en 2 étapes (bytes Latin-1 → String)
//   reloadData()       → Rechargement données serveur (GET /data_reload.php/...)
//
// GESTION MOJIBAKE (correction session 14) :
//   Les formulaires HTML sont encodés ISO-8859-15 côté serveur.
//   getFormHtml() récupère les bytes bruts (ResponseType.bytes) et les convertit
//   avec String.fromCharCodes() pour préserver les codes Latin-1.
//   DynamicFormWidget._preprocessHtml() corrige ensuite l'encodage avec un seuil 5% U+FFFD.

import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import 'package:dio/io.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';
import '../models/campaign.dart';
import '../models/school.dart';
import '../models/regroup.dart';
import '../models/education_system.dart';
import '../models/question.dart';

class ApiService {
  late Dio _dio;
  String? _serverUrl;
  String? _login;
  String? _password;

  // ─── Cache DNS — résolution IP au moment de l'authentification ───────────────
  // IMPORTANT : Le fallback DNS→IP est DÉSACTIVÉ pour HTTPS.
  // Raison : sur les serveurs HTTPS publics (ex: stateduc.mineduc.gov.bi),
  // le DNS peut retourner 127.0.0.1 (IP locale du serveur). Si on remplace
  // le hostname par cette IP dans les URLs, BoringSSL rejette la connexion
  // avec : SSL error 51 — SAN mismatch (cert émis pour le hostname, pas l'IP).
  //
  // Le fallback DNS→IP reste actif UNIQUEMENT pour HTTP intranet (non-HTTPS)
  // avec une IP non-loopback (≠ 127.x.x.x et ≠ ::1).
  String? _cachedServerIp; // IP numérique du serveur (HTTP intranet seulement)
  int?    _cachedServerPort; // Port extrait de l'URL

  static const String _kDnsCacheKeyPrefix = 'dns_cache_';

  // ─── Singleton ──────────────────────────────────────────────────────────────
  // UNE SEULE instance partagée par AuthProvider, CampaignProvider et DataEntryProvider.
  // configure() est appelée lors de la connexion — les modifications sont immédiatement
  // visibles par tous les providers qui utilisent ApiService().
  // Pattern factory : ApiService() retourne toujours _instance.
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;

  ApiService._internal() {
    _dio = Dio(
      BaseOptions(
        // Timeouts — session 19 : sendTimeout désactivé (null), receiveTimeout 600s
        //
        // sendTimeout = null  : Le body POST de saveData est petit (<10 KB). Sur Android,
        //   Dio peut déclencher sendTimeout prématurément même sur réseau stable si le
        //   serveur tarde à accusser réception. Désactiver évite ce faux-positif.
        // receiveTimeout = 600s : La chaîne data_save.php → curl interne → questionnaire_ws.php
        //   peut dépasser 5 min sur un serveur XAMPP chargé (page HTML complète + 2× include
        //   + requêtes DB Oracle). 10 min = sécurité maximale pour les réseaux MEN.
        connectTimeout: const Duration(seconds: 60),   // 60s : connexion initiale
        receiveTimeout: const Duration(seconds: 600),  // 10 min : chaîne save → questionnaire_ws
        sendTimeout:    null,                           // désactivé : body petit, pas limiter
        followRedirects: true,
        maxRedirects: 5,
        // validateStatus : accepte tous les codes HTTP < 600 pour les gérer manuellement
        // (évite que Dio lance une exception pour les 400/401/404 — on les traite nous-mêmes)
        validateStatus: (status) => status != null && status < 600,
      ),
    );

    // ── SSL / Certificat auto-signé ──────────────────────────────────────────
    // PROBLÈME : Le serveur StatEduc est déployé sur intranet avec un certificat
    // auto-signé (ou HTTP simple sans TLS). Dart/Flutter utilise BoringSSL
    // indépendamment du système Android, ce qui provoque l'erreur
    // "Software caused connection abort" si le certificat est invalide.
    //
    // SOLUTION : On configure l'IOHttpClientAdapter pour ignorer les erreurs
    // de certificat via badCertificateCallback → return true.
    //
    // SÉCURITÉ : Ce contournement est acceptable car :
    //   1. L'application fonctionne sur intranet MEN (réseau fermé)
    //   2. L'authentification HTTP Basic ajoute une couche de contrôle d'accès
    //   3. Les données ne transitent pas sur Internet public
    (_dio.httpClientAdapter as IOHttpClientAdapter).createHttpClient = () {
      final client = HttpClient();
      client.badCertificateCallback =
          (X509Certificate cert, String host, int port) {
        debugPrint('[ApiService] Accepting certificate for $host:$port '
            'subject=${cert.subject}');
        return true; // Accept self-signed / untrusted certs
      };
      return client;
    };

    _dio.interceptors.add(_AuthInjectorInterceptor(this));
    _dio.interceptors.add(_DnsFallbackInterceptor(this));
    _dio.interceptors.add(_buildLogInterceptor());
  }

  // ─── Log Interceptor ────────────────────────────────────────────────────────
  // Intercepteur de journalisation pour le débogage des requêtes/réponses Dio.
  // Affiche dans la console (debugPrint) :
  //   [Dio→] : méthode HTTP, URI, début du header Authorization, début du body POST
  //   [Dio←] : code HTTP, URI, début du corps de réponse
  //   [Dio✗] : type d'erreur, URI, message, corps de réponse si disponible
  // Les corps sont tronqués à 200/300 caractères pour éviter de saturer les logs.

  InterceptorsWrapper _buildLogInterceptor() {
    return InterceptorsWrapper(
      onRequest: (options, handler) {
        debugPrint('[Dio→] ${options.method} ${options.uri}');
        debugPrint(
            '[Dio→] Auth: ${options.headers['Authorization']?.toString().substring(0, 20) ?? 'MISSING'}...');
        if (options.data != null) {
          final dataStr = options.data.toString();
          debugPrint(
              '[Dio→] Body: ${dataStr.length > 200 ? dataStr.substring(0, 200) + "…" : dataStr}');
        }
        handler.next(options);
      },
      onResponse: (response, handler) {
        debugPrint(
            '[Dio←] ${response.statusCode} ${response.requestOptions.uri}');
        final bodyStr = response.data?.toString() ?? '';
        debugPrint(
            '[Dio←] Body: ${bodyStr.length > 300 ? bodyStr.substring(0, 300) + "…" : bodyStr}');
        handler.next(response);
      },
      onError: (DioException e, handler) {
        debugPrint('[Dio✗] type=${e.type} uri=${e.requestOptions.uri}');
        debugPrint('[Dio✗] message=${e.message}');
        if (e.response != null) {
          debugPrint(
              '[Dio✗] status=${e.response?.statusCode} body=${e.response?.data}');
        }
        handler.next(e);
      },
    );
  }

  // ─── Configuration ──────────────────────────────────────────────────────────
  // configure() est appelée UNE FOIS lors de la connexion de l'utilisateur.
  // Elle initialise l'URL de base Dio, les headers Authorization et User-Agent.
  // updateCredentials() permet de mettre à jour les credentials sans rechanger l'URL.

  /// Normalise l'URL serveur saisie par l'utilisateur :
  ///   - Ajoute http:// si aucun schéma présent (ex: "192.168.1.10/app")
  ///   - Garantit un slash final OBLIGATOIRE (comportement Dio avec baseUrl)
  ///
  /// IMPORTANT — comportement Dio avec baseUrl :
  ///   'http://host/app'  + get('/endpoint') → 'http://host/endpoint'  ← PERD /app !
  ///   'http://host/app/' + get('endpoint')  → 'http://host/app/endpoint' ← CORRECT ✓
  static String normalizeServerUrl(String raw) {
    String url = raw.trim();
    if (url.isEmpty) return url;
    // Ajouter http:// si aucun schéma n'est présent
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
      url = 'http://$url';
    }
    // Garantir un slash final pour que Dio conserve le chemin de base
    if (!url.endsWith('/')) {
      url = '$url/';
    }
    return url;
  }

  void configure(String serverUrl, String login, String password) {
    _serverUrl = normalizeServerUrl(serverUrl);
    _login = login;
    _password = password;
    _dio.options.baseUrl = _serverUrl!;
    _dio.options.headers['Authorization'] =
        'Basic ${base64Encode(utf8.encode('$login:$password'))}';
    // Match the User-Agent that a browser/jQuery AJAX would send
    _dio.options.headers['User-Agent'] =
        'Mozilla/5.0 (Linux; Android 10) StatEduc/1.0';
    _dio.options.headers['Accept'] = 'application/json, text/plain, */*';
    debugPrint('[ApiService] configure → baseUrl=$_serverUrl login=$login');
    debugPrint(
        '[ApiService] Authorization=Basic ${base64Encode(utf8.encode('$login:$password'))}');
    // Réinitialiser le cache IP en mémoire — obligatoire lors de chaque configure()
    // (l'URL serveur peut avoir changé HTTP→HTTPS ou vers un autre hôte)
    _cachedServerIp = null;
    _cachedServerPort = null;
    // Pour HTTPS : purger aussi le cache persisté dans SharedPreferences
    // pour éviter qu'une IP loopback (127.0.0.1) héritée d'une session
    // précédente ne contamine les requêtes de cette session.
    _purgeLoopbackDnsCache();
    // Charger le cache DNS persisté (HTTP intranet seulement)
    // _loadCachedIp() vérifie le schéma et ignore HTTPS + loopback
    _loadCachedIp();
  }

  // ─── DNS : purge du cache loopback ────────────────────────────────────────
  /// Supprime de SharedPreferences toute entrée 'dns_cache_*' dont la valeur
  /// est une adresse loopback (127.x.x.x ou ::1) ou vide.
  /// Appelée à chaque configure() pour nettoyer les résidus de sessions
  /// précédentes où le DNS avait retourné l'IP locale du serveur.
  Future<void> _purgeLoopbackDnsCache() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final keys = prefs.getKeys()
          .where((k) => k.startsWith(_kDnsCacheKeyPrefix))
          .toList();
      for (final key in keys) {
        final val = prefs.getString(key) ?? '';
        final ip = val.split(':').first;
        if (ip.isEmpty || ip.startsWith('127.') || ip == '::1') {
          await prefs.remove(key);
          debugPrint('[ApiService] _purgeLoopbackDnsCache: removed key=$key (ip=$ip)');
        }
      }
    } catch (e) {
      debugPrint('[ApiService] _purgeLoopbackDnsCache error (non-fatal): $e');
    }
  }

  // ─── DNS : chargement du cache persisté ───────────────────────────────────
  /// Charge l'IP DNS mise en cache depuis SharedPreferences lors du démarrage.
  /// Permet de réutiliser l'IP entre redémarrages de l'app.
  ///
  /// NE CHARGE PAS le cache si le schéma est HTTPS (la substitution hostname→IP
  /// causerait une erreur SSL 51 en HTTPS).
  Future<void> _loadCachedIp() async {
    try {
      // Ne pas charger de cache IP pour HTTPS (inutile + dangereux → SSL 51)
      final scheme = Uri.parse(_serverUrl ?? '').scheme.toLowerCase();
      if (scheme == 'https') {
        debugPrint('[ApiService] _loadCachedIp: HTTPS — not loading IP cache');
        _cachedServerIp = null;
        return;
      }

      final host = _extractHostFromUrl(_serverUrl ?? '');
      if (host.isEmpty) return;
      final prefs = await SharedPreferences.getInstance();
      final cached = prefs.getString('$_kDnsCacheKeyPrefix$host');
      if (cached != null && cached.isNotEmpty) {
        final parts = cached.split(':');
        final ip = parts[0];
        // Ne pas charger une IP loopback (non joignable depuis le mobile)
        if (ip.startsWith('127.') || ip == '::1') {
          debugPrint('[ApiService] _loadCachedIp: ignoring loopback IP $ip from cache');
          return;
        }
        _cachedServerIp = ip;
        _cachedServerPort = parts.length > 1 ? int.tryParse(parts[1]) : null;
        debugPrint('[ApiService] DNS cache loaded: $host → $_cachedServerIp:$_cachedServerPort');
      }
    } catch (e) {
      debugPrint('[ApiService] _loadCachedIp error: $e');
    }
  }

  // ─── DNS : résolution et mise en cache à l'authentification ──────────────
  /// Résout le hostname de _serverUrl en IP numérique via InternetAddress.lookup()
  /// et met l'IP en cache (mémoire + SharedPreferences).
  ///
  /// Appelée après une authentification réussie — si le réseau est disponible
  /// à ce moment-là (ce qui est garanti puisque auth a réussi), la résolution
  /// doit aboutir.
  ///
  /// LIMITATION : La résolution et le cache IP sont désactivés pour HTTPS.
  /// En HTTPS, la substitution hostname→IP provoquerait une erreur SSL 51
  /// (SAN mismatch). De plus, les serveurs HTTPS publics ont un DNS fiable.
  /// Le mécanisme de fallback IP est réservé au HTTP intranet.
  ///
  /// En cas d'erreur (timeout, exception), la méthode retourne silencieusement
  /// sans bloquer le flux d'authentification.
  Future<void> _resolveAndCacheIp() async {
    try {
      // Ne pas résoudre en HTTPS : la substitution hostname→IP causerait
      // une erreur SSL 51 (SAN mismatch) — certificat émis pour le hostname,
      // pas pour l'IP résolue.
      final scheme = Uri.parse(_serverUrl ?? '').scheme.toLowerCase();
      if (scheme == 'https') {
        debugPrint('[ApiService] _resolveAndCacheIp: HTTPS detected — '
            'skipping IP resolution (not needed, would cause SSL SAN mismatch)');
        return;
      }

      final host = _extractHostFromUrl(_serverUrl ?? '');
      if (host.isEmpty) return;

      // Si le host EST déjà une IP numérique, pas besoin de résolution
      if (_isNumericIp(host)) {
        _cachedServerIp = host;
        _cachedServerPort = _extractPortFromUrl(_serverUrl ?? '');
        debugPrint('[ApiService] _resolveAndCacheIp: host is already IP → $_cachedServerIp');
        return;
      }

      debugPrint('[ApiService] _resolveAndCacheIp: resolving $host...');
      final addresses = await InternetAddress.lookup(host)
          .timeout(const Duration(seconds: 10));

      if (addresses.isEmpty) {
        debugPrint('[ApiService] _resolveAndCacheIp: no addresses returned for $host');
        return;
      }

      // Préférer une adresse IPv4 (plus fiable sur intranet)
      final ipv4 = addresses.firstWhere(
        (a) => a.type == InternetAddressType.IPv4,
        orElse: () => addresses.first,
      );

      // Ne pas cacher une adresse loopback (127.x.x.x) — le serveur peut
      // répondre avec son IP locale qui n'est pas joignable depuis le mobile.
      if (ipv4.address.startsWith('127.') || ipv4.address == '::1') {
        debugPrint('[ApiService] _resolveAndCacheIp: resolved to loopback '
            '(${ipv4.address}) — not caching (not reachable from mobile)');
        return;
      }

      _cachedServerIp   = ipv4.address;
      _cachedServerPort = _extractPortFromUrl(_serverUrl ?? '');

      debugPrint('[ApiService] _resolveAndCacheIp: $host → $_cachedServerIp:$_cachedServerPort');

      // Persister dans SharedPreferences pour les sessions ultérieures
      final prefs = await SharedPreferences.getInstance();
      final cacheValue = _cachedServerPort != null
          ? '$_cachedServerIp:$_cachedServerPort'
          : _cachedServerIp!;
      await prefs.setString('$_kDnsCacheKeyPrefix$host', cacheValue);
      debugPrint('[ApiService] _resolveAndCacheIp: cached to SharedPreferences key=${_kDnsCacheKeyPrefix}$host');
    } catch (e) {
      // Non-fatal — si la résolution échoue, on continue sans cache
      debugPrint('[ApiService] _resolveAndCacheIp error (non-fatal): $e');
    }
  }

  // ─── DNS : helpers extraction URL ─────────────────────────────────────────
  /// Purge TOUTES les entrées 'dns_cache_*' de SharedPreferences.
  /// Méthode statique publique appelée depuis main() au démarrage pour
  /// éliminer définitivement tout cache loopback hérité des sessions précédentes.
  static Future<void> clearAllDnsCache() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final keys = prefs.getKeys()
          .where((k) => k.startsWith(_kDnsCacheKeyPrefix))
          .toList();
      for (final key in keys) {
        await prefs.remove(key);
        debugPrint('[ApiService] clearAllDnsCache: removed key=$key');
      }
      debugPrint('[ApiService] clearAllDnsCache: ${keys.length} entrie(s) cleared');
    } catch (e) {
      debugPrint('[ApiService] clearAllDnsCache error (non-fatal): $e');
    }
  }

  /// Extrait le hostname d'une URL (ex: 'http://stateduc.ins.ne:9191/app/' → 'stateduc.ins.ne')
  static String _extractHostFromUrl(String url) {
    try {
      return Uri.parse(url).host;
    } catch (_) {
      return '';
    }
  }

  /// Extrait le port d'une URL (ex: 'http://stateduc.ins.ne:9191/app/' → 9191)
  /// Retourne null si aucun port explicite (utilise le port par défaut du schéma).
  static int? _extractPortFromUrl(String url) {
    try {
      final port = Uri.parse(url).port;
      // Uri.parse retourne 0 si pas de port explicite (non -1 comme documenté)
      return (port > 0) ? port : null;
    } catch (_) {
      return null;
    }
  }

  /// Vérifie si une chaîne est une adresse IP numérique (IPv4 ou IPv6).
  static bool _isNumericIp(String host) {
    // IPv4 : 4 groupes de chiffres séparés par des points
    final ipv4Regex = RegExp(r'^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$');
    // IPv6 : contient au moins un ':'
    return ipv4Regex.hasMatch(host) || host.contains(':');
  }

  /// Construit l'URL de fallback en remplaçant le hostname par l'IP cachée.
  /// Préserve le schéma (http/https), le port, le chemin et les query params.
  ///
  /// Ex: 'http://stateduc.ins.ne:9191/StatEduc/data_save.php/...'
  ///   → 'http://192.168.1.10:9191/StatEduc/data_save.php/...'
  String? _buildFallbackUrl(String originalUrl) {
    if (_cachedServerIp == null) return null;
    try {
      final uri = Uri.parse(originalUrl);
      final fallbackUri = uri.replace(
        host: _cachedServerIp!,
        port: _cachedServerPort ?? uri.port,
      );
      return fallbackUri.toString();
    } catch (_) {
      return null;
    }
  }

  void updateCredentials(String login, String password) {
    _login = login;
    _password = password;
    _dio.options.headers['Authorization'] =
        'Basic ${base64Encode(utf8.encode('$login:$password'))}';
  }

  String? get serverUrl => _serverUrl;
  String? get login => _login;

  // ═══════════════════════════════════════════════════════════════════════════
  // AUTHENTICATION
  // Source JS: users.js
  //   Login:  GET /user_ident.php/user/{login}/{mdp}
  //           Header: Authorization: Basic btoa(login+':'+mdp)
  //           Response: { se_status:200, se_data: {id, login, firstname, lastname,
  //                       codeyear, libyear, filter, filters:[{CODE_TYPE_PERIOD,
  //                       NAME_TYPE_PERIOD, ORDER_TYPE_PERIOD}]} }
  //           se_message == 'log_ko' → invalid credentials
  //   Logout: GET /user_ident.php/logout/xxxx/xxxx
  // ═══════════════════════════════════════════════════════════════════════════

  Future<User?> authenticate(
      String serverUrl, String login, String password) async {
    configure(serverUrl, login, password);
    try {
      final encodedPassword = Uri.encodeComponent(password);
      final url = 'user_ident.php/user/$login/$encodedPassword';
      debugPrint('[ApiService] authenticate → GET ${_serverUrl}$url');

      final response = await _dio.get(
        url,
        options: Options(
          // Force plain-text response so we control JSON parsing
          // This avoids Dio failing when Content-Type is text/html or unusual
          responseType: ResponseType.plain,
        ),
      );
      // Note : _resolveAndCacheIp() est appelée plus bas, après le parsing
      // réussi — on ne résout le DNS que si l'auth a vraiment abouti.

      final statusCode = response.statusCode ?? 0;
      debugPrint('[ApiService] authenticate ← HTTP $statusCode');

      // 401 = either Apache/Nginx HTTP Basic Auth layer OR PHP app rejected
      if (statusCode == 401) {
        debugPrint(
            '[ApiService] authenticate: HTTP 401 — credentials rejected');
        throw ApiException('Accès refusé (401).\n'
            'Vérifiez vos identifiants ou contactez l\'administrateur.');
      }

      if (statusCode == 404) {
        throw ApiException(
            'Serveur introuvable (404). Vérifiez l\'URL : $_serverUrl');
      }

      // Parse the response body manually
      final rawBody = response.data?.toString().trim() ?? '';
      debugPrint('[ApiService] authenticate ← rawBody=$rawBody');

      if (rawBody.isEmpty) {
        debugPrint('[ApiService] authenticate: empty response body');
        return null;
      }

      Map<String, dynamic>? data;
      try {
        final parsed = json.decode(rawBody);
        data = parsed is Map ? Map<String, dynamic>.from(parsed) : null;
      } catch (e) {
        debugPrint('[ApiService] authenticate: JSON parse error → $e');
        debugPrint('[ApiService] authenticate: raw body was → $rawBody');
        return null;
      }

      if (data == null) return null;

      // se_message == 'log_ko' means invalid credentials at PHP app level
      if (data['se_message'] == 'log_ko') {
        debugPrint(
            '[ApiService] authenticate: log_ko → identifiants invalides (PHP level)');
        return null;
      }

      if (data['se_status'] == 200) {
        final userData = data['se_data'];
        if (userData != null) {
          final userMap = userData is String
              ? json.decode(userData) as Map<String, dynamic>
              : userData as Map<String, dynamic>;
          debugPrint('[ApiService] authenticate: success → $userMap');
          // ── DNS cache : résoudre et mettre en cache l'IP du serveur ──────
          // On lance la résolution en arrière-plan (unawaited) pour ne pas
          // bloquer le retour de l'User. Si ça échoue, ce n'est pas bloquant.
          _resolveAndCacheIp();
          return User.fromJson(userMap);
        }
      }

      debugPrint('[ApiService] authenticate: unexpected response → $data');
      return null;
    } on DioException catch (e) {
      debugPrint(
          '[ApiService] authenticate DioException: type=${e.type} status=${e.response?.statusCode} msg=${e.message}');
      debugPrint(
          '[ApiService] authenticate DioException body=${e.response?.data}');
      if (e.type == DioExceptionType.connectionTimeout ||
          e.type == DioExceptionType.receiveTimeout ||
          e.type == DioExceptionType.sendTimeout) {
        throw ApiException(
            'Serveur injoignable (timeout). Vérifiez l\'URL et le réseau.');
      }
      if (e.type == DioExceptionType.connectionError) {
        throw ApiException(
            'Impossible de joindre le serveur. Vérifiez l\'URL : $_serverUrl');
      }
      if (e.response?.statusCode == 401) {
        throw ApiException('Accès refusé (401).\n'
            'Le serveur exige une authentification HTTP supplémentaire.\n'
            'Contactez l\'administrateur du serveur.');
      }
      if (e.response?.statusCode == 404)
        throw ApiException('Serveur introuvable (404). Vérifiez l\'URL.');
      throw ApiException('Erreur réseau : ${e.message ?? e.type.name}');
    }
  }

  Future<void> logout() async {
    try {
      await _dio.get('user_ident.php/logout/xxxx/xxxx');
    } catch (_) {
      // Best effort — ignore errors on logout
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // AVAILABLE CAMPAIGNS
  // Source JS: page_new_camp.js
  //   getDataFromServer('/user_camp.php/new_camp/', currentUser.id + '/1', ...)
  //   → GET /user_camp.php/new_camp/{userId}/1
  //   Response se_data: [ { id, nom, debut, fin, statut, typeRegroups }, ... ]
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Campaign>> getAvailableCampaigns(String userId) async {
    final data = await _get('user_camp.php/new_camp/$userId/1');
    if (data is List) {
      return data.map((c) => Campaign.fromJson(c)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // REGROUPS (Entités administratives)
  // Source JS: charge_camp.js
  //   getDataFromServer('/user_camp.php/reg_camp/',
  //     currentUser.login +'/'+ id_camp +'/1', ...)
  //   → GET /user_camp.php/reg_camp/{login}/{id_camp}/1
  //   Response se_data: [ { id, nom, type, parentid }, ... ]
  // NOTE: uses currentUser.login (NOT .id)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Regroup>> getRegroups(String login, String campId) async {
    // SESSION 53 FIX: encoder le login pour éviter HTTP 400 sur les logins avec espaces
    // ex. "BUTERA III" → "BUTERA%20III" sans quoi Slim PHP reçoit une URL malformée.
    final encodedLogin = Uri.encodeComponent(login);
    final data = await _get('user_camp.php/reg_camp/$encodedLogin/$campId/1');
    if (data is List) {
      return data.map((r) => Regroup.fromJson(r)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // REGROUP TYPES (Types d'entités administratives)
  // Source JS: charge_camp.js
  //   getDataFromServer('/user_camp.php/typ_reg_camp/',
  //     currentUser.id +'/'+ id_camp +'/'+ this.currCamp.typeregroups, ...)
  //   → GET /user_camp.php/typ_reg_camp/{userId}/{id_camp}/{typeRegroups_csv}
  //   Response se_data: [ { id, nom }, ... ]
  // NOTE: uses currentUser.id (NOT .login); typeRegroups is CSV from campaign
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<RegroupType>> getRegroupTypes(
      String userId, String campId, String typeRegroups) async {
    final data =
        await _get('user_camp.php/typ_reg_camp/$userId/$campId/$typeRegroups');
    if (data is List) {
      return data.map((t) => RegroupType.fromJson(t)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SCHOOL STATUSES
  // Source JS: charge_camp.js
  //   getDataFromServer('/user_camp.php/etabs_status/', '', ...)
  //   → GET /user_camp.php/etabs_status/
  //   Response se_data: [ { id, name }, ... ]
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<SchoolStatus>> getSchoolStatuses() async {
    final data = await _get('user_camp.php/etabs_status/');
    if (data is List) {
      return data.map((s) => SchoolStatus.fromJson(s)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SCHOOLS (Établissements)
  // Source JS: charge_camp.js
  //   getDataFromServer('/user_camp.php/etabs_camp/',
  //     currentUser.id +'/'+ id_camp +'/1', ...)
  //   → GET /user_camp.php/etabs_camp/{userId}/{id_camp}/1
  //   Response se_data: [ { id, code, nom, status, idregroup }, ... ]
  // NOTE: uses currentUser.id (NOT .login)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<School>> getSchools(String userId, String campId) async {
    final data = await _get('user_camp.php/etabs_camp/$userId/$campId/1');
    if (data is List) {
      return data.map((s) => School.fromJson(s)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // LOCALISATIONS (Chaînes de localisation)
  // Source JS: charge_camp.js
  //   getDataFromServer('/user_camp.php/locs_camp/',
  //     currentUser.id +'/'+ id_camp, ...)
  //   → GET /user_camp.php/locs_camp/{userId}/{id_camp}
  //   Response se_data: [ { idloc, idcamp, idsys, regroups (CSV), etabs (CSV) },...]
  //   NOTE: each row has multiple etabs — we expand them via localisationsFromRawJson()
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Localisation>> getLocalisations(
      String userId, String campId) async {
    final data = await _get('user_camp.php/locs_camp/$userId/$campId');
    if (data is List) {
      // Each row may contain multiple etab IDs → expand
      final result = <Localisation>[];
      for (final item in data) {
        result.addAll(localisationsFromRawJson(item));
      }
      return result;
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // EDUCATION SYSTEMS (Secteurs)
  // Source JS: charge_camp.js
  //   getDataFromServer('/user_camp.php/sys_camp/',
  //     currentUser.id +'/'+ id_camp, ...)
  //   → GET /user_camp.php/sys_camp/{userId}/{id_camp}
  //   Response se_data: [ { id, nom }, ... ]
  //   NOTE: also triggers get_qsts for each system
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<EducationSystem>> getEducationSystems(
      String userId, String campId) async {
    final data = await _get('user_camp.php/sys_camp/$userId/$campId');
    if (data is List) {
      return data.map((s) => EducationSystem.fromJson(s)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // QUESTIONS / THEMES (Formulaires de collecte)
  // Source JS: charge_camp.js
  //   getDataFromServer('/data_camp.php/theme_camp/',
  //     id_camp+'/'+id_sys + '/eng', ...)
  //   → GET /data_camp.php/theme_camp/{id_camp}/{id_sys}/eng
  //   Response se_data: [ { id, title/lib_qst, idsys, filter }, ... ]
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Question>> getQuestions(String campId, String sysId) async {
    final data = await _get('data_camp.php/theme_camp/$campId/$sysId/eng');
    if (data is List) {
      // Pass serverIndex to preserve the server-returned order (sort_order)
      return List.generate(
        data.length,
        (i) => Question.fromJson(data[i] as Map<String, dynamic>, serverIndex: i),
      );
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // FORM HTML — TWO-STEP FETCH
  // Source JS: charge_camp.js addQstHtml():
  //   Step 1: GET /data_camp.php/html_theme_camp/{id_camp}/{id_qst}/eng
  //           → response is a URL string (HTML file URL on server)
  //   Step 2: GET {url} with Authorization header
  //           → response is HTML content
  // ═══════════════════════════════════════════════════════════════════════════

  Future<String> getFormHtml(String campId, String qstId) async {
    try {
      // Step 1: get the HTML URL
      final step1 = await _dio.get(
        'data_camp.php/html_theme_camp/$campId/$qstId/eng',
        options: Options(responseType: ResponseType.plain),
      );
      // The response is expected to be a URL string (or wrapped in se_data)
      String htmlUrl = step1.data.toString().trim();

      // Handle JSON envelope if present
      if (htmlUrl.startsWith('{')) {
        final parsed = json.decode(htmlUrl);
        if (parsed is Map) {
          // Session 49 : vérifier se_status AVANT d'utiliser se_data
          // Quand FRAME est vide en DB, serveur renvoie se_status=400, se_data=''
          // Sans ce check, htmlUrl='', throw ApiException silencieux → formHtml null
          final seStatus = parsed['se_status'];
          if (seStatus != null && seStatus != 200) {
            final seMessage = (parsed['se_message'] ?? 'Formulaire indisponible').toString();
            debugPrint('[ApiService] getFormHtml step1 — se_status=$seStatus qst=$qstId : $seMessage');
            throw ApiException('Formulaire indisponible (se_status=$seStatus) : $seMessage');
          }
          htmlUrl = (parsed['se_data'] ?? '').toString().trim();
        }
      }

      if (htmlUrl.isEmpty) {
        throw ApiException('URL formulaire vide (se_data absent) pour qst=$qstId');
      }

      // If the returned URL is relative, make it absolute
      if (!htmlUrl.startsWith('http')) {
        htmlUrl = '${_serverUrl ?? ''}$htmlUrl';
      }

      debugPrint('[ApiService] getFormHtml step2 → GET $htmlUrl');

      // Step 2: fetch HTML content using the SAME _dio instance so that
      // Basic Auth header is carried automatically.
      // When htmlUrl is absolute (starts with http), Dio 5.x ignores baseUrl
      // and uses the absolute URL directly — so no baseUrl conflict.
      //
      // ROOT CAUSE OF MOJIBAKE: The server sends ISO-8859-15 encoded HTML.
      // Using ResponseType.plain makes Dio decode bytes using its default
      // charset (often UTF-8 or Latin-1 inconsistently), producing garbled
      // text for accented characters.
      // FIX: Fetch as raw bytes (ResponseType.bytes) and decode explicitly
      // using latin1 (ISO-8859-1 ≈ ISO-8859-15 for French chars).
      // The pre-processor in DynamicFormWidget then re-encodes to UTF-8
      // for correct display.
      final step2 = await _dio.get(
        htmlUrl,
        options: Options(responseType: ResponseType.bytes),
      );
      // Decode as Latin-1 (byte-for-byte: each byte becomes its Unicode code point)
      // This faithfully preserves the ISO-8859-15 bytes as Dart code units ≤ 0xFF,
      // which DynamicFormWidget's _preprocessHtml() can then repair to proper UTF-8.
      final rawBytes = step2.data;
      final String html;
      if (rawBytes is List<int>) {
        html = String.fromCharCodes(rawBytes);
      } else {
        html = rawBytes?.toString() ?? '';
      }
      debugPrint('[ApiService] getFormHtml step2 ← ${step2.statusCode} '
          'bodyLen=${html.length} snippet=${html.length > 120 ? html.substring(0, 120) : html}');

      if (html.isEmpty) {
        throw ApiException('Formulaire HTML vide (réponse serveur)');
      }
      return html;
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      if (e.response?.statusCode == 404) {
        throw ApiException('Formulaire introuvable');
      }
      throw ApiException('Erreur chargement formulaire : ${e.message}');
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // VALIDATION RULES
  // Source JS: charge_camp.js getRules():
  //   getDataFromServer('/data_camp.php/regle_theme_camp/',
  //     id_qst +'/'+ id_sys, ...)
  //   → GET /data_camp.php/regle_theme_camp/{id_qst}/{id_sys}
  //   Response se_data: [ { champ, type, obli, taille, ... }, ... ]
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<ValidationRule>> getValidationRules(
      String qstId, String sysId) async {
    final data = await _get('data_camp.php/regle_theme_camp/$qstId/$sysId');
    if (data is List) {
      return data.map((r) => ValidationRule.fromJson(r)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SAUVEGARDE DES DONNÉES (POST)
  // =============================================================================
  // Source JS: page_etab.js saveOneQstOnServer() → postDataToServer():
  //
  //   POST /data_save.php/theme_save/{login}/{id_camp}/{id_sys}/{id_qst}/
  //        {id_etab}/{filter}/0[/{yearCode}]
  //   Body: application/x-www-form-urlencoded
  //   Réponse: JSON { se_status, se_data }
  //     se_data == 'OKSAVE' → succès
  //     se_status == 400    → erreur serveur (message dans se_data)
  //
  // CHAÎNE D'ENVOI CÔTÉ SERVEUR (data_save.php) :
  //   1. Vérifie les droits d'accès (DICO_FIXE_REGROUPEMENT + fallback ADMIN_USERS)
  //   2. session_write_close() → libère le verrou de session ANTI-DEADLOCK
  //   3. curl POST → questionnaire_ws.php (écriture en base Oracle/MySQL)
  //   4. Retourne OKSAVE si questionnaire_ws.php a émis ISOKSAVEINDATABASE
  //
  // CONSTRUCTION DU CORPS POST (mirroir exact de page_etab.js getPageDataToSend()) :
  //   • Champs radio : clé "fieldName#optionId" = "1" → transformé en "fieldName=optionId"
  //     (seules les options cochées sont envoyées, les décochées sont omises)
  //   • Autres champs : remplacement "/" → "_slh_" uniquement (PAS d'encodeURIComponent)
  //   • Toujours ajouté à la fin : switch_theme_id= save_and_prev= save_and_next=
  //   • Premier thème : LOC_REG_0={etabRegroupId} ajouté (regroupement de l'établissement)
  //
  // PARAMÈTRE yearCode (correction session 14) :
  //   Ajouté comme dernier segment de l'URL : .../0/{yearCode}
  //   Permet au PHP de récupérer l'année scolaire sans session navigateur.
  //   Sans ce paramètre, $_SESSION['annee'] est vide → saveLogInfo() échoue.
  // =============================================================================

  // ─── Helper : retry automatique pour les envois ──────────────────────────────
  // Réessaie l'opération [fn] jusqu'à [maxAttempts] fois en cas d'erreur
  // transitoire (sendTimeout, receiveTimeout, unknown/socket).
  //
  // Entre deux tentatives : délai progressif (délai × numéro de tentative)
  // pour laisser le serveur récupérer.
  //
  // Ne réessaie PAS sur :
  //   - Erreurs métier (ApiException) : 401, 404, réponse JSON se_status 400
  //   - connectionTimeout : le serveur est injoignable → inutile de réessayer
  static const int _kMaxRetries  = 2;       // 2 re-tentatives (3 essais au total)
  static const int _kRetryDelay  = 5;       // 5 secondes entre tentatives

  /// [onRetry] : callback optionnel appelé AVANT chaque re-tentative.
  ///   Paramètre : numéro de tentative en cours (1 = 1ère re-tentative, 2 = 2ème…).
  ///   Utilisé par DataEntryProvider pour mettre à jour _sendAttempt et
  ///   afficher "Tentative 2/3…" dans l'overlay UI.
  Future<T> _withRetry<T>(
    Future<T> Function() fn, {
    void Function(int attempt)? onRetry,
  }) async {
    int attempt = 0;
    while (true) {
      try {
        return await fn();
      } on ApiException {
        // Erreurs métier — ne pas réessayer
        rethrow;
      } on DioException catch (e) {
        final isRetryable =
            e.type == DioExceptionType.sendTimeout    ||
            e.type == DioExceptionType.receiveTimeout ||
            e.type == DioExceptionType.unknown;
        attempt++;
        if (!isRetryable || attempt > _kMaxRetries) rethrow;
        onRetry?.call(attempt);  // notifie le provider avant le délai
        final delay = Duration(seconds: _kRetryDelay * attempt);
        debugPrint('[ApiService] retry $attempt/$_kMaxRetries after ${delay.inSeconds}s '
            '(type=${e.type.name} msg=${e.message})');
        await Future.delayed(delay);
      }
    }
  }

  Future<bool> saveData({
    required String login,     // login de l'utilisateur (currentUser.login)
    required String campId,    // ID de la campagne (stmCamp.id)
    required String sysId,     // ID du secteur/système éducatif
    required String qstId,     // ID du thème/formulaire à sauvegarder
    required String etabId,    // ID de l'établissement scolaire
    required String? filter,   // ID de la période filtre (ou null si aucun filtre)
    required Map<String, dynamic> formData, // données du formulaire (champs + valeurs)
    String? etabRegroupId,     // ID du regroupement de l'établissement (pour LOC_REG_0)
    bool isFirstQuestion = false, // true si c'est le premier thème → inclut LOC_REG_0
    bool isLastPage = true,
    String yearCode = '',      // code année scolaire (user.codeyear) — contournement session PHP
    void Function(int attempt)? onRetry, // callback appelé avant chaque re-tentative
  }) async {
    // Délègue à _withRetry pour réessayer automatiquement en cas d'erreur transitoire.
    // onRetry est transmis pour que DataEntryProvider puisse afficher "Tentative 2/3…".
    return _withRetry(
      () => _saveDataOnce(
        login:           login,
        campId:          campId,
        sysId:           sysId,
        qstId:           qstId,
        etabId:          etabId,
        filter:          filter,
        formData:        formData,
        etabRegroupId:   etabRegroupId,
        isFirstQuestion: isFirstQuestion,
        isLastPage:      isLastPage,
        yearCode:        yearCode,
      ),
      onRetry: onRetry,
    );
  }

  /// Effectue une tentative d'envoi unique — appelé par saveData() via _withRetry().
  Future<bool> _saveDataOnce({
    required String login,
    required String campId,
    required String sysId,
    required String qstId,
    required String etabId,
    required String? filter,
    required Map<String, dynamic> formData,
    String? etabRegroupId,
    bool isFirstQuestion = false,
    bool isLastPage = true,
    String yearCode = '',
  }) async {
    final filterParam = (filter == null || filter.isEmpty) ? '0' : filter;
    // Build form body exactly like page_etab.js getPageDataToSend():
    //   • Radio keys are stored as "fieldName#optionId" = "1"|"0".
    //     Only checked radios (value == "1") are sent, transformed to
    //     "fieldName=optionId" (the option id, not "1").
    //   • All other fields: replace "/" → "_slh_", NO Uri.encodeComponent
    //     (JS only does replace(/\//g,'_slh_') — no encodeURIComponent).
    final bodyParts = <String>[];
    formData.forEach((key, value) {
      final strVal = value?.toString() ?? '';
      if (key.contains('#')) {
        // Radio field: key = "fieldName#optionId"
        // Only include if this option is checked (value == "1")
        if (strVal == '1' || strVal == 'true') {
          final hashIdx  = key.indexOf('#');
          final fieldName = key.substring(0, hashIdx);
          final optionId  = key.substring(hashIdx + 1).replaceAll('/', '_slh_');
          bodyParts.add('$fieldName=$optionId');
        }
        // Unchecked radio → skip (do not send anything)
      } else {
        // Text / number / select / checkbox — only _slh_ substitution
        final encodedVal = strVal.replaceAll('/', '_slh_');
        bodyParts.add('$key=$encodedVal');
      }
    });
    // Add LOC_REG_0 if this is the first question (mirrors page_etab.js LOC_REG_0 logic)
    if ((isFirstQuestion || !formData.containsKey('LOC_REG_0')) &&
        etabRegroupId != null) {
      if (!formData.containsKey('LOC_REG_0')) {
        bodyParts.add('LOC_REG_0=$etabRegroupId');
      }
    }
    // Append trailing params (mirrors page_etab.js)
    if (isLastPage) {
      bodyParts.add('switch_theme_id=');
      bodyParts.add('save_and_prev=');
      bodyParts.add('save_and_next=');
    } else {
      bodyParts.add('switch_theme_id=');
      bodyParts.add('save_and_prev=0');
      bodyParts.add('save_and_next=0');
    }
    final body = bodyParts.join('&');

    try {
      // Append yearCode as the last segment so PHP server can inject it into
      // $_SESSION['annee'] and bypass the missing browser session for REST calls.
      // The server has two routes: one with /:id_annee (mobile) and one without (web).
      final anneeSegment = (yearCode.isNotEmpty && yearCode != '0') ? '/$yearCode' : '';
      // SESSION 53 FIX: encoder le login pour éviter HTTP 400 sur les logins avec espaces
      final encodedLogin = Uri.encodeComponent(login);
      final response = await _dio.post(
        'data_save.php/theme_save/$encodedLogin/$campId/$sysId/$qstId/$etabId/$filterParam/0$anneeSegment',
        data: body,
        options: Options(
          contentType: 'application/x-www-form-urlencoded',
          responseType: ResponseType.plain,
        ),
      );
      final statusCode = response.statusCode ?? 0;
      if (statusCode == 401) throw ApiException('Accès refusé (401)');
      if (statusCode == 404) throw ApiException('Endpoint introuvable (404)');

      final responseStr = response.data?.toString().trim() ?? '';
      debugPrint('[ApiService] saveData ← HTTP $statusCode body=$responseStr');

      // Server may return empty body on success — treat as OKSAVE
      if (responseStr.isEmpty) return true;

      // Try to parse JSON — if it fails, treat non-empty response as success
      dynamic result;
      try {
        result = json.decode(responseStr);
      } catch (_) {
        // Non-JSON response — server accepted the data
        debugPrint('[ApiService] saveData: non-JSON response, assuming success');
        return true;
      }

      if (result is Map) {
        if (result['se_status'] == 400) {
          final errMsg = result['se_data']?.toString() ?? 'Erreur serveur (400)';
          debugPrint('[ApiService] saveData: server rejected → $errMsg');
          throw ApiException(errMsg);
        }
        if (result['se_data'] == 'OKSAVE') return true;
        if (result['se_data'] == 'KOSAVE') {
          // SESSION 53 FIX: KOSAVE n'est plus silencieux — on lève KosaveException
          // pour que l'UI affiche un avertissement visible à l'utilisateur.
          // La sauvegarde locale est déjà faite avant l'appel → données sûres.
          debugPrint('[ApiService] saveData: KOSAVE — données reçues mais non enregistrées en DB serveur');
          throw KosaveException(themeId: qstId);
        }
        if (result['se_status'] != null && result['se_status'] != 400) return true;
        debugPrint('[ApiService] saveData: unexpected result → $result');
        return false;
      }
      // Any non-Map non-error response = success
      return statusCode < 300;
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      if (e.response?.statusCode == 404) {
        throw ApiException('Endpoint introuvable (vérifiez l\'URL serveur)');
      }
      // ── Timeout (sendTimeout désactivé depuis session 19 → normalement plus reçu) ──
      // connectionTimeout : serveur injoignable (réseau coupé, IP incorrecte)
      // receiveTimeout    : questionnaire_ws.php a pris >600s (serveur très surchargé)
      // sendTimeout       : ne devrait plus se produire (= null depuis session 19)
      if (e.type == DioExceptionType.connectionTimeout) {
        throw ApiException(
            'Impossible de joindre le serveur.\n'
            'Vérifiez l\'URL et votre connexion réseau.');
      }
      if (e.type == DioExceptionType.sendTimeout ||
          e.type == DioExceptionType.receiveTimeout) {
        // Ces types seront interceptés par _withRetry — si on arrive ici
        // c'est que les _kMaxRetries tentatives ont toutes échoué.
        throw ApiException(
            'Délai d\'attente dépassé après ${_kMaxRetries + 1} tentatives.\n'
            'Le serveur ne répond pas (type: ${e.type.name}).\n'
            'Vérifiez que le serveur XAMPP est démarré et réessayez plus tard.');
      }
      if (e.type == DioExceptionType.connectionError) {
        throw ApiException(
            'Impossible de joindre le serveur. Vérifiez votre réseau.');
      }
      // DioExceptionType.unknown — peut être causé par : socket reset, serveur
      // qui coupe la connexion TCP avant de répondre, erreur SSL, etc.
      // Sera intercepté par _withRetry si c'est la 1ère/2ème tentative.
      final rawMsg = e.message ?? '';
      if (e.type == DioExceptionType.unknown ||
          rawMsg.toLowerCase().contains('socket') ||
          rawMsg.toLowerCase().contains('connection') ||
          rawMsg.toLowerCase().contains('network')) {
        throw ApiException(
            'Erreur réseau lors de l\'envoi (${e.type.name}).\n'
            'Cause probable : serveur interrompu ou réseau instable.\n'
            'Réessayez quand le réseau est stable.');
      }
      throw ApiException('Erreur envoi données : ${rawMsg.isNotEmpty ? rawMsg : e.type.name}');
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // RECHARGEMENT DES DONNÉES DEPUIS LE SERVEUR (GET)
  // Source JS: page_etab.js reloadOneQstFromServer() → getFormDataFromServer():
  //   GET /data_reload.php/theme_data/{login}/{id_sys}/{id_qst}/{id_camp}/
  //       {id_etab}/{filter}
  //   Réponse : JSON brut → map { fieldName: [valeur, type], ... }
  //   ou { se_status:400, se_data: message_erreur } en cas d'erreur
  //
  // Utilisé pour pré-remplir le formulaire avec les valeurs déjà enregistrées
  // en base de données (après un rechargement manuel par l'utilisateur).
  // ═══════════════════════════════════════════════════════════════════════════

  Future<Map<String, dynamic>?> reloadData({
    required String login, // login de l'utilisateur
    required String sysId, // ID du secteur
    required String qstId, // ID du thème/formulaire
    required String campId, // ID de la campagne
    required String etabId, // ID de l'établissement
    required String? filter, // période filtre (ou null)
  }) async {
    final filterParam = (filter == null || filter.isEmpty) ? 'null' : filter;
    // SESSION 53 FIX: encoder le login pour éviter HTTP 400 sur les logins avec espaces
    final encodedLogin = Uri.encodeComponent(login);
    try {
      final response = await _dio.get(
        'data_reload.php/theme_data/$encodedLogin/$sysId/$qstId/$campId/$etabId/$filterParam',
        options: Options(responseType: ResponseType.plain),
      );
      final responseStr = response.data.toString().trim();
      if (responseStr.isEmpty) return null;

      // Response is raw JSON string (not wrapped in se_data)
      final parsed = json.decode(responseStr);
      if (parsed is Map) {
        // Error envelope
        if (parsed['se_status'] == 400) return null;
        // If result is wrapped
        if (parsed.containsKey('se_data')) {
          final inner = parsed['se_data'];
          if (inner is Map) return Map<String, dynamic>.from(inner);
          if (inner is String) return json.decode(inner);
          return null;
        }
        return Map<String, dynamic>.from(parsed);
      }
      return null;
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      return null;
    } catch (_) {
      return null;
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // RÈGLES DE COHÉRENCE OFFLINE — TÉLÉCHARGEMENT DEPUIS data_rules.php
  // =============================================================================
  //   GET /data_rules.php/theme_rules/{login}/{campId}/{sysId}/{qstId}/
  //       {etabId}/{filter}/{yearCode}
  //
  //   Réponse se_data : { id_theme, nb_regles, regles: [
  //     { id_regle, lib_regle, sql_regle, associations: [
  //       { id_assoc, id_regle_assoc, lib_regle_assoc, sql_assoc, critere, message }
  //     ]}
  //   ]}
  //
  // Chaque paire (règle, association) devient UNE ligne CoherenceRule en SQLite.
  // Appelé une fois par thème lors du téléchargement de la campagne (non-bloquant).
  //
  // CORRECTION SESSION 14 :
  //   yearCode ajouté dans l'URL — sans lui, data_rules.php retourne nb_regles=0
  //   car $_SESSION['annee'] est vide en contexte mobile (pas de session PHP).
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<CoherenceRule>> fetchRules({
    required String login,
    required String campId,
    required String sysId,
    required String qstId,
    required String etabId,
    required String? filter,
    String yearCode = '',
  }) async {
    final filterParam  = (filter == null || filter.isEmpty) ? 'null' : filter;
    final anneeSegment = (yearCode.isNotEmpty && yearCode != '0') ? yearCode : '0';
    // SESSION 53 FIX: encoder le login pour éviter HTTP 400 sur les logins avec espaces
    final encodedLogin = Uri.encodeComponent(login);
    final path =
        'data_rules.php/theme_rules/$encodedLogin/$campId/$sysId/$qstId/$etabId/$filterParam/$anneeSegment';
    try {
      final response = await _dio.get(
        path,
        options: Options(responseType: ResponseType.plain),
      );
      final rawBody = response.data?.toString().trim() ?? '';
      if (rawBody.isEmpty) return [];

      dynamic parsed;
      try {
        parsed = json.decode(rawBody);
      } catch (_) {
        return [];
      }

      if (parsed is! Map) return [];
      if (parsed['se_status'] != 200) return [];

      final seData = parsed['se_data'];
      if (seData is! Map) return [];

      final regles = seData['regles'];
      if (regles is! List) return [];

      final now    = DateTime.now().toIso8601String();
      final result = <CoherenceRule>[];

      for (final regle in regles.whereType<Map>()) {
        final idRegle   = int.tryParse(regle['id_regle']?.toString() ?? '0') ?? 0;
        final libRegle  = (regle['lib_regle'] ?? '').toString();
        final sqlRegle  = (regle['sql_regle'] ?? '').toString();
        final assocs    = regle['associations'];
        if (assocs is! List || assocs.isEmpty) continue;

        for (final assoc in assocs.whereType<Map>()) {
          result.add(CoherenceRule(
            idCamp:        campId,
            idQst:         qstId,
            idEtab:        etabId,
            idFilter:      (filter == null || filter.isEmpty) ? null : filter,
            idRegle:       idRegle,
            libRegle:      libRegle,
            sqlRegle:      sqlRegle,
            idAssoc:       int.tryParse(assoc['id_assoc']?.toString()       ?? '0') ?? 0,
            idRegleAssoc:  int.tryParse(assoc['id_regle_assoc']?.toString() ?? '0') ?? 0,
            libRegleAssoc: (assoc['lib_regle_assoc'] ?? '').toString(),
            sqlAssoc:      (assoc['sql_assoc'] ?? '').toString(),
            critere:       (assoc['critere']  ?? '').toString(),
            message:       (assoc['message']  ?? '').toString(),
            fetchedAt:     now,
          ));
        }
      }
      return result;
    } on DioException catch (_) {
      return []; // Non-fatal — offline rules unavailable
    } catch (_) {
      return [];
    }
  }

  // CONTRÔLE DE COHÉRENCE SERVEUR — APRÈS ENVOI DES DONNÉES
  // =============================================================================
  // Source serveur: data_controle.php → controle_theme_batch.class.php
  //
  //   GET /data_controle.php/theme_controle/{login}/{campId}/{sysId}/{qstId}/
  //        {etabId}/{filter}/{yearCode}
  //   Réponse : { se_status:200, se_data: { nb_erreurs: N, erreurs: [...] } }
  //   erreurs[] : { id_regle, id_regle_assoc, message, regle_1, regle_2, critere }
  //
  // FONCTIONNEMENT :
  //   Exécuté APRÈS saveData() réussi pour vérifier la cohérence des données
  //   fraîchement enregistrées en base Oracle/MySQL.
  //   Contrairement au contrôle offline (CoherenceEvaluator), ce contrôle
  //   exécute les requêtes SQL directement sur la base de données serveur,
  //   donc il est exhaustif et précis.
  //
  // RÉSULTAT : Liste de CoherenceError (vide = pas de violations).
  //   En cas d'erreur réseau → retourne [] (non-bloquant, DataEntryProvider
  //   ne bloque pas l'envoi si le contrôle échoue).
  //
  // ID THÈME COMPOSITE :
  //   L'ID thème peut être composite (ex: 15702 = thème 1570 + secteur 2).
  //   data_controle.php appelle controle_strip_theme_id() pour extraire 1570.
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<CoherenceError>> checkCoherence({
    required String login,
    required String campId,
    required String sysId,
    required String qstId,
    required String etabId,
    required String? filter,
    String yearCode = '',
  }) async {
    final filterParam = (filter == null || filter.isEmpty) ? 'null' : filter;
    final anneeSegment = (yearCode.isNotEmpty && yearCode != '0') ? '/$yearCode' : '/0';
    // SESSION 53 FIX: encoder le login pour éviter HTTP 400 sur les logins avec espaces
    final encodedLogin = Uri.encodeComponent(login);
    final path =
        'data_controle.php/theme_controle/$encodedLogin/$campId/$sysId/$qstId/$etabId/$filterParam$anneeSegment';
    try {
      final response = await _dio.get(
        path,
        options: Options(responseType: ResponseType.plain),
      );
      final rawBody = response.data?.toString().trim() ?? '';
      if (rawBody.isEmpty) return [];

      dynamic parsed;
      try {
        parsed = json.decode(rawBody);
      } catch (_) {
        return [];
      }

      if (parsed is! Map) return [];
      if (parsed['se_status'] == 400) return [];

      final seData = parsed['se_data'];
      if (seData is! Map) return [];

      final erreurs = seData['erreurs'];
      if (erreurs is! List) return [];

      return erreurs
          .whereType<Map>()
          .map((e) => CoherenceError.fromJson(Map<String, dynamic>.from(e)))
          .toList();
    } on DioException catch (_) {
      // Non-critical — return empty (no blocking)
      return [];
    } catch (_) {
      return [];
    }
  }

  // Miroir de : getDataFromServer(servSuffix, params, callBack) dans charge_camp.js
  //   → callback reçoit response.se_data (déballage de l'enveloppe JSON)
  //
  // HELPER PRIVÉ — GET générique avec déballage de l'enveloppe se_data
  // Utilisé par toutes les méthodes de lecture (getSchools, getCampaigns, etc.)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<dynamic> _get(String path) async {
    try {
      final response = await _dio.get(
        path,
        options: Options(responseType: ResponseType.plain),
      );
      final statusCode = response.statusCode ?? 0;

      if (statusCode == 401) throw ApiException('Accès refusé (401)');
      if (statusCode == 404)
        throw ApiException('Endpoint introuvable (404) : $path');
      if (statusCode >= 300)
        throw ApiException('Erreur serveur ($statusCode) : $path');

      final rawBody = response.data?.toString().trim() ?? '';
      if (rawBody.isEmpty) return [];

      // Parse JSON manually
      dynamic parsed;
      try {
        parsed = json.decode(rawBody);
      } catch (_) {
        debugPrint(
            '[ApiService] _get: JSON parse error for $path body=$rawBody');
        return [];
      }

      if (parsed is Map) {
        // Unwrap se_data envelope (always present in server responses)
        return parsed['se_data'] ?? parsed;
      }
      return parsed ?? [];
    } on DioException catch (e) {
      final status = e.response?.statusCode;
      if (status == 401) throw ApiException('Accès refusé (401)');
      if (status == 404)
        throw ApiException('Endpoint introuvable (404) : $path');
      // e.message can be null for redirect/unknown errors — provide a useful message
      final msg = e.message ?? e.type.name;
      throw ApiException('Erreur réseau : $msg');
    }
  }
}


// ─── Intercepteur de fallback DNS ──────────────────────────────────────────
// PROBLÈME PRODUCTION : sur le réseau MEN, le hostname 'stateduc.ins.ne' est
// un DNS interne. Si l'agent de collecte est hors du LAN MEN au moment de
// l'envoi, la résolution DNS échoue avec :
//   SocketException: Failed host lookup 'stateduc.ins.ne'
//   → DioException type=connectionError, code "6" dans l'UI
//
// SOLUTION : Cet intercepteur capture les erreurs connectionError dont le
// message contient 'Failed host lookup'. Il remplace alors le hostname de
// l'URL par l'IP numérique cachée (résolue lors de l'authentification) et
// rejoue la requête une fois. Si l'IP est inconnue ou si le fallback échoue
// aussi, l'erreur originale est propagée normalement.
//
// LIMITATION CRITIQUE — HTTPS + substitution hostname→IP :
//   Quand l'URL est HTTPS et que le serveur répond avec un certificat émis
//   pour 'stateduc.mineduc.gov.bi' mais que la connexion est établie vers
//   '127.0.0.1' (ou toute autre IP), BoringSSL lève l'erreur :
//     SSL error 51: no alternative certificate subject name matches host name
//   C'est le comportement de l'erreur vue en production.
//
//   La substitution hostname→IP est donc DÉSACTIVÉE pour HTTPS :
//   - Le certificat TLS est validé par rapport au hostname SNI, pas l'IP
//   - Pour HTTPS public (Internet), le DNS est fiable — pas de fallback nécessaire
//   - Le fallback IP ne s'applique qu'au HTTP (intranet sans TLS)
//
// ORDRE dans la chaîne des intercepteurs Dio (ordre d'ajout) :
//   _AuthInjectorInterceptor → _DnsFallbackInterceptor → LogInterceptor
// Les onError sont appelés LIFO, donc LogInterceptor voit l'erreur en premier
// pour la loguer, puis _DnsFallbackInterceptor tente le fallback.
class _DnsFallbackInterceptor extends Interceptor {
  final ApiService _service;
  _DnsFallbackInterceptor(this._service);

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    // Condition 1 : doit être une connectionError (code 6 dans l'UI)
    if (err.type != DioExceptionType.connectionError) {
      return handler.next(err);
    }

    // Condition 2 : le message doit indiquer un échec de résolution DNS
    final msg = err.message?.toLowerCase() ?? '';
    final isHostLookupFailure =
        msg.contains('failed host lookup') ||
        msg.contains('could not resolve host') ||
        msg.contains('nodename nor servname') || // iOS
        msg.contains('name or service not known'); // Linux

    if (!isHostLookupFailure) {
      return handler.next(err);
    }

    // Condition 3 : on doit avoir une IP cachée
    if (_service._cachedServerIp == null) {
      debugPrint('[DnsFallback] No cached IP available, cannot fallback');
      return handler.next(err);
    }

    // Condition 4 — HTTPS : ne PAS substituer hostname→IP en HTTPS.
    // La substitution provoquerait une erreur SSL 51 (SAN mismatch) car le
    // certificat TLS est émis pour le hostname (ex: 'stateduc.mineduc.gov.bi')
    // et non pour l'IP (ex: '127.0.0.1'). Le fallback IP est réservé au HTTP
    // (intranet sans TLS) où il n'y a pas de vérification de certificat.
    final requestScheme = err.requestOptions.uri.scheme.toLowerCase();
    if (requestScheme == 'https') {
      debugPrint('[DnsFallback] HTTPS request — skipping IP fallback to avoid '
          'SSL SAN mismatch (cert issued for hostname, not IP). '
          'Cached IP=${_service._cachedServerIp}');
      return handler.next(err);
    }

    // Condition 5 — loopback : ne jamais substituer vers 127.x.x.x
    // (le serveur peut répondre 127.0.0.1 pour son hostname, ce qui n'est
    // pas joignable depuis le mobile)
    final cachedIp = _service._cachedServerIp ?? '';
    if (cachedIp.startsWith('127.') || cachedIp == '::1') {
      debugPrint('[DnsFallback] Cached IP is loopback ($cachedIp) — '
          'skipping fallback (loopback not reachable from mobile)');
      return handler.next(err);
    }

    // Construire l'URL de fallback avec l'IP numérique
    final originalUrl = err.requestOptions.uri.toString();
    final fallbackUrl = _service._buildFallbackUrl(originalUrl);
    if (fallbackUrl == null) {
      debugPrint('[DnsFallback] Could not build fallback URL from $originalUrl');
      return handler.next(err);
    }

    debugPrint('[DnsFallback] DNS failure detected: ${err.message}');
    debugPrint('[DnsFallback] Retrying with IP: $originalUrl → $fallbackUrl');

    // Cloner les options de la requête originale avec la nouvelle URL
    final fallbackOptions = err.requestOptions.copyWith(
      path: fallbackUrl,
      baseUrl: '',  // URL absolue — Dio ignorera baseUrl
    );

    try {
      // Rejouer la requête via le client Dio sous-jacent
      final response = await _service._dio.fetch(fallbackOptions);
      debugPrint('[DnsFallback] Fallback succeeded: HTTP ${response.statusCode}');
      handler.resolve(response);
    } on DioException catch (fallbackErr) {
      debugPrint('[DnsFallback] Fallback also failed: ${fallbackErr.message}');
      // Propager l'erreur originale (plus informative pour l'utilisateur)
      handler.next(err);
    } catch (e) {
      debugPrint('[DnsFallback] Fallback unexpected error: $e');
      handler.next(err);
    }
  }
}

// ─── Intercepteur d'injection du header Authorization ───────────────────────
// Garantit que le header Authorization est présent sur CHAQUE requête Dio,
// y compris celles générées automatiquement lors du suivi des redirections 3xx.
//
// PROBLÈME : Lors d'un redirect, Dio peut perdre ou ne pas propager les headers
// personnalisés selon la version et la configuration de l'adaptateur HTTP.
//
// SOLUTION : Cet intercepteur vérifie à chaque requête si Authorization est présent.
// Si absent, il le ré-injecte à partir des credentials stockés dans ApiService.
//
// Ce mécanisme est indispensable car le serveur StatEduc utilise parfois des
// redirections internes (ex: Apache mod_rewrite) qui peuvent provoquer une
// perte des headers.
class _AuthInjectorInterceptor extends Interceptor {
  final ApiService _service;
  _AuthInjectorInterceptor(this._service);

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    // If Authorization header is missing (can happen on redirect re-issue),
    // re-inject it from the service's current credentials.
    if (!options.headers.containsKey('Authorization') ||
        options.headers['Authorization'] == null) {
      final login = _service._login;
      final password = _service._password;
      if (login != null && password != null) {
        options.headers['Authorization'] =
            'Basic ${base64Encode(utf8.encode('$login:$password'))}';
        debugPrint('[AuthInjector] Re-injected Authorization header');
      }
    }
    handler.next(options);
  }
}

/// CoherenceRule — Règle de cohérence téléchargée depuis data_rules.php
/// pour l'évaluation hors ligne par CoherenceEvaluator.
///
/// Une règle représente une paire (R1, R2) avec un opérateur de comparaison :
///   "R1.sql_regle {critere} R2.sql_assoc" doit être vrai pour que les données soient cohérentes.
///
/// Stockage : table SQLite `coherence_rules` indexée par (id_camp, id_qst, id_etab).
/// Champs JSON serveur : id_regle, lib_regle, sql_regle, id_assoc, id_regle_assoc,
///                       lib_regle_assoc, sql_assoc, critere, message.
class CoherenceRule {
  final String idCamp;
  final String idQst;
  final String idEtab;
  final String? idFilter;
  final int    idRegle;
  final String libRegle;
  final String sqlRegle;
  final int    idAssoc;
  final int    idRegleAssoc;
  final String libRegleAssoc;
  final String sqlAssoc;
  final String critere;
  final String message;
  final String fetchedAt;

  const CoherenceRule({
    required this.idCamp,
    required this.idQst,
    required this.idEtab,
    this.idFilter,
    required this.idRegle,
    required this.libRegle,
    required this.sqlRegle,
    required this.idAssoc,
    required this.idRegleAssoc,
    required this.libRegleAssoc,
    required this.sqlAssoc,
    required this.critere,
    required this.message,
    required this.fetchedAt,
  });
}

class ApiException implements Exception {
  final String message;
  ApiException(this.message);

  @override
  String toString() => message;
}

/// SESSION 53 — Exception levée quand le serveur retourne KOSAVE.
///
/// KOSAVE = le serveur a reçu les données mais le fichier ACTION_THEME
/// (questionnaire_ws.php) n'a pas émis "ISOKSAVEINDATABASE".
/// Causes connues :
///   1. Le fichier include du thème est introuvable (ACTION_THEME absent)
///   2. theme_data_MAJ_ok=false (échec DB côté serveur)
///   3. Le thème d'identification a une logique spéciale nécessitant
///      un contexte de session PHP non disponible en REST mobile.
///
/// La sauvegarde LOCALE a déjà été effectuée avant l'envoi → les données
/// ne sont PAS perdues. L'utilisateur doit réessayer ou contacter l'admin.
class KosaveException implements Exception {
  final String themeId;
  final String message;
  KosaveException({required this.themeId, this.message = 'KOSAVE'});

  @override
  String toString() => 'KOSAVE (thème $themeId) : données reçues mais non enregistrées en base';
}

/// CoherenceError — Violation de cohérence retournée par data_controle.php.
///
/// Générée par controle_theme_batch.class.php côté serveur :
///   le tableau $tab_regles_theme_assoc_not_ok est sérialisé en JSON par data_controle.php.
///
/// Champs JSON :
///   id_regle       → ID de la règle R1 (DICO_REGLE_THEME)
///   id_regle_assoc → ID de la règle R2 associée
///   message        → message d'erreur traduit (depuis DICO_REGLE_THEME_ASSOC)
///   regle_1        → libellé de la valeur calculée pour R1 (pour affichage)
///   regle_2        → libellé de la valeur calculée pour R2 (pour affichage)
///   critere        → opérateur de comparaison (<=, >=, =, etc.)
class CoherenceError {
  final int idRegle;
  final int idRegleAssoc;
  final String message;
  final String regle1;
  final String regle2;
  final String critere;

  const CoherenceError({
    required this.idRegle,
    required this.idRegleAssoc,
    required this.message,
    this.regle1 = '',
    this.regle2 = '',
    this.critere = '',
  });

  factory CoherenceError.fromJson(Map<String, dynamic> json) {
    return CoherenceError(
      idRegle:       int.tryParse(json['id_regle']?.toString()       ?? '0') ?? 0,
      idRegleAssoc:  int.tryParse(json['id_regle_assoc']?.toString() ?? '0') ?? 0,
      message:       (json['message']  ?? '').toString(),
      regle1:        (json['regle_1']  ?? '').toString(),
      regle2:        (json['regle_2']  ?? '').toString(),
      critere:       (json['critere']  ?? '').toString(),
    );
  }
}

