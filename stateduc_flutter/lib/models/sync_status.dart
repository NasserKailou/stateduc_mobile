/// Modèles de synchronisation — branche ak_burundi (U04 / U06 / U08 / U16 / U19).
///
/// Clé de soumission (identique côté serveur — SUBMISSION_KEY) :
///   (codeTypeAnnee, idCamp, idEtab, idQst, idFilter|'')
///
/// Contrat partagé avec `DatabaseService` (table `form_sync_status`, v9),
/// `ApiService` (parsing `se_ack`) et `DataEntryProvider`.
library;

// ═══════════════════════════════════════════════════════════════════════════
// Statut local d'un formulaire
// ═══════════════════════════════════════════════════════════════════════════

enum SyncStatus {
  /// Sauvegardé localement, jamais envoyé ou modifié depuis le dernier envoi OK.
  pending,
  /// Dernier envoi accepté ET enregistré par le serveur (OKSAVE) — ou rechargé depuis le serveur.
  sent,
  /// Dernier envoi en échec réseau / timeout / réponse invalide.
  failed,
  /// Reçu par le serveur mais NON enregistré en base (KOSAVE).
  kosave,
  /// Rejeté par le serveur (400 : année, accès, campagne fermée…).
  rejected,
  /// Verrouillé / validé par l'administration (FORM_LOCKED) — lecture seule locale.
  locked,
}

extension SyncStatusX on SyncStatus {
  String get code => name;

  static SyncStatus fromCode(String? v) {
    for (final s in SyncStatus.values) {
      if (s.name == v) return s;
    }
    // Compatibilité : 'draft' (ancien libellé) → pending
    return SyncStatus.pending;
  }

  /// Vrai si le formulaire doit être (re)transmis.
  bool get needsSend =>
      this == SyncStatus.pending || this == SyncStatus.failed || this == SyncStatus.kosave;

  bool get isProblem =>
      this == SyncStatus.failed || this == SyncStatus.kosave || this == SyncStatus.rejected;

  String get label {
    switch (this) {
      case SyncStatus.pending:  return 'En attente d\'envoi';
      case SyncStatus.sent:     return 'Envoyé';
      case SyncStatus.failed:   return 'Échec d\'envoi';
      case SyncStatus.kosave:   return 'Reçu, non enregistré (KOSAVE)';
      case SyncStatus.rejected: return 'Rejeté par le serveur';
      case SyncStatus.locked:   return 'Verrouillé par l\'administration';
    }
  }

  String get shortLabel {
    switch (this) {
      case SyncStatus.pending:  return 'À envoyer';
      case SyncStatus.sent:     return 'Envoyé';
      case SyncStatus.failed:   return 'Échec';
      case SyncStatus.kosave:   return 'KOSAVE';
      case SyncStatus.rejected: return 'Rejeté';
      case SyncStatus.locked:   return 'Verrouillé';
    }
  }
}

/// Ligne de `form_sync_status`.
class FormSyncStatus {
  final String codeTypeAnnee;
  final String idCamp;
  final String idEtab;
  final String idQst;
  final String idFilter;              // '' si aucun filtre
  final SyncStatus status;
  final DateTime? lastAttemptAt;
  final DateTime? lastSuccessAt;
  final DateTime? localUpdatedAt;     // dernière sauvegarde locale
  final String? lastError;
  final int attempts;
  final String? syncId;               // UUID de la tentative logique en cours (U11)
  final ServerAck? serverAck;         // dernier accusé (U08)
  final String? serverStatus;         // OK | VIDE | ERREUR | AUCUN (réconciliation U17)
  final DateTime? serverLastOk;       // dernière réception OK côté serveur
  final String? adminStatus;          // RECU | A_CORRIGER | VALIDE | VERROUILLE (U18/U19)
  final String? adminComment;

  const FormSyncStatus({
    required this.codeTypeAnnee,
    required this.idCamp,
    required this.idEtab,
    required this.idQst,
    required this.idFilter,
    required this.status,
    this.lastAttemptAt,
    this.lastSuccessAt,
    this.localUpdatedAt,
    this.lastError,
    this.attempts = 0,
    this.syncId,
    this.serverAck,
    this.serverStatus,
    this.serverLastOk,
    this.adminStatus,
    this.adminComment,
  });

  String get key => '$codeTypeAnnee|$idCamp|$idEtab|$idQst|$idFilter';
  String? get idFilterOrNull => idFilter.isEmpty ? null : idFilter;

  bool get isLocked => status == SyncStatus.locked ||
      adminStatus == 'VALIDE' || adminStatus == 'VERROUILLE';
  bool get correctionRequested => adminStatus == 'A_CORRIGER';

  /// Vrai si le serveur a été modifié après le dernier envoi réussi local
  /// et que des modifications locales existent (conflit potentiel — U21).
  bool get hasServerConflict {
    if (serverLastOk == null) return false;
    if (!status.needsSend) return false;
    final ref = lastSuccessAt ?? localUpdatedAt;
    if (ref == null) return true;
    return serverLastOk!.isAfter(ref);
  }
}

/// Agrégat par établissement (pour `_SchoolTile` et la bannière campagne — U05).
class SchoolSyncSummary {
  final String idEtab;
  final int total;      // formulaires ayant une ligne de statut
  final int sent;
  final int pending;
  final int failed;     // failed + kosave + rejected
  final int locked;
  final bool correctionRequested;
  final DateTime? lastSuccessAt;

  const SchoolSyncSummary({
    required this.idEtab,
    this.total = 0,
    this.sent = 0,
    this.pending = 0,
    this.failed = 0,
    this.locked = 0,
    this.correctionRequested = false,
    this.lastSuccessAt,
  });

  bool get allSent => total > 0 && sent == total;
  bool get hasIssues => failed > 0;
  bool get hasPending => pending > 0;
  int get toSend => pending + failed;
}

// ═══════════════════════════════════════════════════════════════════════════
// Accusé de réception serveur (se_ack — U08)
// ═══════════════════════════════════════════════════════════════════════════

class ServerAck {
  final String? syncId;
  final String? serverTime;     // ISO 8601 (heure serveur)
  final int? nbInsert, nbUpdate, nbDelete, nbUnchanged;
  final bool duplicate;
  final String? originalTime;
  final String? warning;        // ex. NO_DELETE_GUARD
  final int? nbDeletePrevented;
  final bool correctionCleared;
  final String? errorHint;
  final Map<String, dynamic> raw;

  const ServerAck({
    this.syncId, this.serverTime,
    this.nbInsert, this.nbUpdate, this.nbDelete, this.nbUnchanged,
    this.duplicate = false, this.originalTime, this.warning, this.nbDeletePrevented,
    this.correctionCleared = false, this.errorHint,
    this.raw = const {},
  });

  static int? _i(dynamic v) => v == null ? null : (v is int ? v : int.tryParse(v.toString()));

  factory ServerAck.fromJson(Map<String, dynamic> j) => ServerAck(
        syncId:            j['sync_id']?.toString(),
        serverTime:        j['server_time']?.toString(),
        nbInsert:          _i(j['nb_insert']),
        nbUpdate:          _i(j['nb_update']),
        nbDelete:          _i(j['nb_delete']),
        nbUnchanged:       _i(j['nb_unchanged']),
        duplicate:         j['duplicate'] == true,
        originalTime:      j['original_time']?.toString(),
        warning:           j['warning']?.toString(),
        nbDeletePrevented: _i(j['nb_delete_prevented']),
        correctionCleared: j['correction_cleared'] == true,
        errorHint:         j['error_hint']?.toString(),
        raw:               Map<String, dynamic>.from(j),
      );

  Map<String, dynamic> toJson() => raw.isNotEmpty
      ? raw
      : {
          'sync_id': syncId, 'server_time': serverTime,
          'nb_insert': nbInsert, 'nb_update': nbUpdate, 'nb_delete': nbDelete, 'nb_unchanged': nbUnchanged,
          'duplicate': duplicate, 'original_time': originalTime, 'warning': warning,
          'nb_delete_prevented': nbDeletePrevented, 'correction_cleared': correctionCleared, 'error_hint': errorHint,
        };

  bool get hasCounters => nbInsert != null || nbUpdate != null || nbUnchanged != null;
  int? get nbRows => hasCounters ? (nbInsert ?? 0) + (nbUpdate ?? 0) + (nbUnchanged ?? 0) : null;

  /// Résumé lisible pour l'utilisateur.
  String summary() {
    final parts = <String>[];
    if (duplicate) parts.add('déjà reçu le ${originalTime ?? '?'}');
    if (hasCounters) {
      parts.add('${nbInsert ?? 0} ajout(s), ${nbUpdate ?? 0} mise(s) à jour, '
          '${nbDelete ?? 0} suppression(s), ${nbUnchanged ?? 0} inchangée(s)');
    }
    if (warning == 'NO_DELETE_GUARD') {
      parts.add('${nbDeletePrevented ?? 0} suppression(s) évitée(s) (envoi partiel)');
    }
    if (correctionCleared) parts.add('demande de correction levée');
    return parts.join(' · ');
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// Résultat typé d'un envoi (U06)
// ═══════════════════════════════════════════════════════════════════════════

enum SendOutcomeKind { ok, kosave, rejected, locked, networkError, noData, yearMismatch, skipped }

class SendOutcome {
  final SendOutcomeKind kind;
  final String idEtab;
  final String idQst;
  final String? idFilter;               // filtre local ('' ou null = aucun)
  final String? libEtab;
  final String? libQst;
  final String? message;
  final ServerAck? ack;
  final String? serverCode;             // se_code (YEAR_MISMATCH, ACCESS_DENIED, FORM_LOCKED, …)
  final Map<String, dynamic>? lockInfo; // se_lock

  const SendOutcome({
    required this.kind,
    this.idEtab = '',
    this.idQst = '',
    this.idFilter,
    this.libEtab,
    this.libQst,
    this.message,
    this.ack,
    this.serverCode,
    this.lockInfo,
  });

  /// Copie avec libellés nominatifs (établissement / thème) pour l'affichage (U06).
  SendOutcome withLabels({String? libEtab, String? libQst}) => SendOutcome(
        kind: kind, idEtab: idEtab, idQst: idQst, idFilter: idFilter,
        libEtab: libEtab ?? this.libEtab, libQst: libQst ?? this.libQst,
        message: message, ack: ack, serverCode: serverCode, lockInfo: lockInfo,
      );

  /// Clé historique « etab_qst[_filtre] » (compat Map<String,bool>).
  String get key => '${idEtab}_$idQst${(idFilter ?? '').isEmpty ? '' : '_$idFilter'}';
  String get keyWithoutEtab => '$idQst${(idFilter ?? '').isEmpty ? '' : '_$idFilter'}';

  bool get isSuccess => kind == SendOutcomeKind.ok;

  /// Vrai si le formulaire n'a PAS été enregistré côté serveur et mérite un
  /// nouvel essai ou une action (noData/skipped ne sont pas des échecs).
  bool get isFailure =>
      kind != SendOutcomeKind.ok &&
      kind != SendOutcomeKind.noData &&
      kind != SendOutcomeKind.skipped;

  /// Compatibilité avec l'ancien contrat `bool` (true = reçu par le serveur).
  bool get asLegacyBool => kind == SendOutcomeKind.ok || kind == SendOutcomeKind.kosave;

  SyncStatus? get toStatus {
    switch (kind) {
      case SendOutcomeKind.ok:           return SyncStatus.sent;
      case SendOutcomeKind.kosave:       return SyncStatus.kosave;
      case SendOutcomeKind.locked:       return SyncStatus.locked;
      case SendOutcomeKind.rejected:
      case SendOutcomeKind.yearMismatch: return SyncStatus.rejected;
      case SendOutcomeKind.networkError: return SyncStatus.failed;
      case SendOutcomeKind.noData:
      case SendOutcomeKind.skipped:      return null; // aucun changement d'état
    }
  }

  String get label {
    switch (kind) {
      case SendOutcomeKind.ok:           return 'Envoyé';
      case SendOutcomeKind.kosave:       return 'Reçu mais non enregistré (KOSAVE)';
      case SendOutcomeKind.rejected:     return 'Rejeté par le serveur';
      case SendOutcomeKind.locked:       return 'Verrouillé/validé par l\'administration';
      case SendOutcomeKind.networkError: return 'Erreur réseau';
      case SendOutcomeKind.noData:       return 'Aucune donnée locale';
      case SendOutcomeKind.yearMismatch: return 'Année incohérente';
      case SendOutcomeKind.skipped:      return 'Ignoré';
    }
  }

  /// Libellé nominatif pour le dialogue de résultat groupé (U06).
  String describe() {
    final e = (libEtab ?? '').isNotEmpty ? libEtab! : 'Étab. $idEtab';
    final q = (libQst ?? '').isNotEmpty ? libQst! : 'Thème $idQst';
    final f = (idFilter ?? '').isEmpty ? '' : ' [$idFilter]';
    final m = (message ?? '').isEmpty ? '' : ' — $message';
    return '$e › $q$f : $label$m';
  }
}

/// Résultat d'un formulaire dans un envoi groupé (U06).
class BatchItemResult {
  final String idEtab;
  final String? libEtab;
  final String idQst;
  final String? libQst;
  final String? idFilter;
  final SendOutcome outcome;
  const BatchItemResult({
    required this.idEtab, this.libEtab, required this.idQst, this.libQst, this.idFilter, required this.outcome,
  });
  String get key => '${idEtab}_$idQst${(idFilter ?? '').isEmpty ? '' : '_$idFilter'}';
  bool get isSuccess => outcome.isSuccess;
}

// ═══════════════════════════════════════════════════════════════════════════
// Statut serveur d'un formulaire (sync_status_ws.php /etab — U16 / U17 / U21)
// ═══════════════════════════════════════════════════════════════════════════

class ServerFormStatus {
  final String idTheme;
  final String libelle;
  final String statut;         // OK | VIDE | ERREUR | AUCUN
  final String? lastOkTime;    // 'Y/m/d H:i:s'
  final String? lastTime;
  final String? lastStatut;
  final int nbTentatives;
  final int? nbRows;
  final String? user;
  final bool locked;
  final Map<String, dynamic>? admin;

  const ServerFormStatus({
    required this.idTheme, required this.libelle, required this.statut,
    this.lastOkTime, this.lastTime, this.lastStatut, this.nbTentatives = 0, this.nbRows, this.user,
    this.locked = false, this.admin,
  });

  factory ServerFormStatus.fromJson(Map<String, dynamic> j) => ServerFormStatus(
        idTheme:      j['id_theme'].toString(),
        libelle:      (j['libelle'] ?? '').toString(),
        statut:       (j['statut'] ?? 'AUCUN').toString(),
        lastOkTime:   j['last_ok_time']?.toString(),
        lastTime:     j['last_time']?.toString(),
        lastStatut:   j['last_statut']?.toString(),
        nbTentatives: ServerAck._i(j['nb_tentatives']) ?? 0,
        nbRows:       ServerAck._i(j['nb_rows']),
        user:         j['user']?.toString(),
        locked:       j['locked'] == true,
        admin:        j['admin'] is Map ? Map<String, dynamic>.from(j['admin']) : null,
      );

  String? get adminStatus => admin?['statut']?.toString();
  String? get adminComment => admin?['commentaire']?.toString();

  /// Convertit 'Y/m/d H:i:s' (heure serveur) en DateTime local.
  static DateTime? parseServerTime(String? s) {
    if (s == null || s.isEmpty) return null;
    final m = RegExp(r'^(\d{4})/(\d{2})/(\d{2}) (\d{2}):(\d{2}):(\d{2})$').firstMatch(s.trim());
    if (m == null) return DateTime.tryParse(s);
    return DateTime(int.parse(m[1]!), int.parse(m[2]!), int.parse(m[3]!),
        int.parse(m[4]!), int.parse(m[5]!), int.parse(m[6]!));
  }

  /// Vue attendue par `DatabaseService.reconcileSchoolWithServer`.
  Map<String, dynamic> toReconcileMap() => {
        'statut': statut,
        'date': (lastOkTime ?? lastTime) == null ? null : parseServerTime(lastOkTime ?? lastTime)?.toIso8601String(),
        'statut_admin': adminStatus,
        'commentaire': adminComment,
      };
}

// ═══════════════════════════════════════════════════════════════════════════
// Demande de correction / verrou administratif (sync_status_ws.php /feedback — U19)
// ═══════════════════════════════════════════════════════════════════════════

class SyncFeedback {
  final String idCamp;
  final String codeTypeAnnee;
  final String idEtab;
  final String? idQst;         // null = tout l'établissement
  final String? idFilter;
  final String statut;         // A_CORRIGER | VALIDE | VERROUILLE
  final String commentaire;
  final String admin;
  final String date;
  final DateTime fetchedAt;
  final String? libEtab;

  const SyncFeedback({
    required this.idCamp, required this.codeTypeAnnee, required this.idEtab,
    this.idQst, this.idFilter, required this.statut, required this.commentaire,
    this.admin = '', this.date = '', required this.fetchedAt, this.libEtab,
  });

  bool get isCorrection => statut == 'A_CORRIGER';
  bool get isLock => statut == 'VALIDE' || statut == 'VERROUILLE';

  /// Le feedback s'applique-t-il au formulaire (thème [idQst], période [idFilter]) ?
  ///   - idQst null côté feedback   → tout l'établissement (tous thèmes)
  ///   - idFilter null côté feedback → toutes les périodes du thème
  ///   - sinon égalité stricte (filtre '' et null équivalents).
  bool appliesTo(String idQst, String? idFilter) {
    if (this.idQst != null && this.idQst != idQst) return false;
    if (this.idFilter == null || this.idFilter!.isEmpty) return true;
    return this.idFilter == (idFilter ?? '');
  }

  factory SyncFeedback.fromJson(Map<String, dynamic> j,
      {required String idCamp, required String codeTypeAnnee, DateTime? fetchedAt}) {
    String? nz(dynamic v) {
      if (v == null) return null;
      final s = v.toString();
      return s.isEmpty ? null : s;
    }
    return SyncFeedback(
      idCamp: idCamp, codeTypeAnnee: codeTypeAnnee,
      idEtab:      j['code_etab'].toString(),
      libEtab:     nz(j['nom']),
      idQst:       nz(j['id_theme']),
      idFilter:    nz(j['code_filtre']),
      statut:      (j['statut'] ?? '').toString(),
      commentaire: (j['commentaire'] ?? '').toString(),
      admin:       (j['admin'] ?? '').toString(),
      date:        (j['date'] ?? '').toString(),
      fetchedAt:   fetchedAt ?? DateTime.now(),
    );
  }
}
