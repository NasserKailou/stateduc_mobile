// =============================================================================
// sync_status_test.dart — AK-SYNC (ak_burundi)
// Tests unitaires des modèles de synchronisation (models/sync_status.dart)
// =============================================================================
//
// Testé en isolation (aucune dépendance SQLite / réseau) :
//   1. SyncStatus       : codes, compat 'draft', needsSend / isProblem
//   2. FormSyncStatus   : clé composite, isLocked, correctionRequested, conflit
//   3. ServerAck        : parsing se_ack (compteurs, doublon, garde), summary()
//   4. SendOutcome      : isSuccess / isFailure / asLegacyBool / toStatus / key
//   5. SchoolSyncSummary: agrégats (allSent, hasIssues, toSend)
//   6. SyncFeedback     : appliesTo (portée thème / période / établissement)
//   7. ServerFormStatus : parseServerTime 'Y/m/d H:i:s'
//
// Pour lancer :
//   flutter test test/sync_status_test.dart

import 'package:flutter_test/flutter_test.dart';
import 'package:stateduc_mobile/models/sync_status.dart';

void main() {
  // ═══════════════════════════════════════════════════════════════════════════
  // 1. SyncStatus
  // ═══════════════════════════════════════════════════════════════════════════
  group('SyncStatus', () {
    test('fromCode round-trip pour tous les états', () {
      for (final s in SyncStatus.values) {
        expect(SyncStatusX.fromCode(s.code), s);
      }
    });

    test("compat : 'draft' et inconnu → pending", () {
      expect(SyncStatusX.fromCode('draft'), SyncStatus.pending);
      expect(SyncStatusX.fromCode(null), SyncStatus.pending);
      expect(SyncStatusX.fromCode('???'), SyncStatus.pending);
    });

    test('needsSend : pending / failed / kosave uniquement', () {
      expect(SyncStatus.pending.needsSend, isTrue);
      expect(SyncStatus.failed.needsSend, isTrue);
      expect(SyncStatus.kosave.needsSend, isTrue);
      expect(SyncStatus.sent.needsSend, isFalse);
      expect(SyncStatus.rejected.needsSend, isFalse);
      expect(SyncStatus.locked.needsSend, isFalse);
    });

    test('isProblem : failed / kosave / rejected', () {
      expect(SyncStatus.failed.isProblem, isTrue);
      expect(SyncStatus.kosave.isProblem, isTrue);
      expect(SyncStatus.rejected.isProblem, isTrue);
      expect(SyncStatus.pending.isProblem, isFalse);
      expect(SyncStatus.sent.isProblem, isFalse);
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 2. FormSyncStatus
  // ═══════════════════════════════════════════════════════════════════════════
  group('FormSyncStatus', () {
    FormSyncStatus make({
      SyncStatus status = SyncStatus.pending,
      String idFilter = '',
      String? adminStatus,
      DateTime? lastSuccessAt,
      DateTime? localUpdatedAt,
      DateTime? serverLastOk,
    }) =>
        FormSyncStatus(
          codeTypeAnnee: '2024', idCamp: '7', idEtab: '101', idQst: '3',
          idFilter: idFilter, status: status, adminStatus: adminStatus,
          lastSuccessAt: lastSuccessAt, localUpdatedAt: localUpdatedAt,
          serverLastOk: serverLastOk,
        );

    test('clé composite = annee|camp|etab|qst|filtre (filtre vide conservé)', () {
      expect(make().key, '2024|7|101|3|');
      expect(make(idFilter: '21').key, '2024|7|101|3|21');
      expect(make().idFilterOrNull, isNull);
      expect(make(idFilter: '21').idFilterOrNull, '21');
    });

    test('isLocked : statut locked OU admin VALIDE / VERROUILLE', () {
      expect(make(status: SyncStatus.locked).isLocked, isTrue);
      expect(make(adminStatus: 'VALIDE').isLocked, isTrue);
      expect(make(adminStatus: 'VERROUILLE').isLocked, isTrue);
      expect(make(adminStatus: 'RECU').isLocked, isFalse);
      expect(make().isLocked, isFalse);
    });

    test('correctionRequested : admin A_CORRIGER', () {
      expect(make(adminStatus: 'A_CORRIGER').correctionRequested, isTrue);
      expect(make(adminStatus: 'RECU').correctionRequested, isFalse);
    });

    test('hasServerConflict : serveur plus récent que le dernier envoi local + modifs locales', () {
      final t0 = DateTime(2025, 1, 1, 10);
      final t1 = DateTime(2025, 1, 1, 12);
      // Pas d'info serveur → pas de conflit
      expect(make(lastSuccessAt: t0).hasServerConflict, isFalse);
      // Rien à envoyer → pas de conflit même si serveur plus récent
      expect(make(status: SyncStatus.sent, lastSuccessAt: t0, serverLastOk: t1).hasServerConflict, isFalse);
      // Modifs locales + serveur postérieur → conflit
      expect(make(lastSuccessAt: t0, serverLastOk: t1).hasServerConflict, isTrue);
      // Modifs locales + serveur antérieur → pas de conflit
      expect(make(lastSuccessAt: t1, serverLastOk: t0).hasServerConflict, isFalse);
      // Jamais envoyé localement mais serveur a des données → conflit (autre source)
      expect(make(serverLastOk: t1).hasServerConflict, isTrue);
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 3. ServerAck (se_ack — U08)
  // ═══════════════════════════════════════════════════════════════════════════
  group('ServerAck', () {
    test('parse compteurs (int ou string) et résumé', () {
      final ack = ServerAck.fromJson({
        'sync_id': 'abc', 'server_time': '2025-01-01T10:00:00',
        'nb_insert': 3, 'nb_update': '2', 'nb_delete': 0, 'nb_unchanged': 10,
      });
      expect(ack.syncId, 'abc');
      expect(ack.nbInsert, 3);
      expect(ack.nbUpdate, 2);
      expect(ack.hasCounters, isTrue);
      expect(ack.nbRows, 15);
      expect(ack.duplicate, isFalse);
      expect(ack.summary(), contains('3 ajout(s)'));
      expect(ack.summary(), contains('2 mise(s) à jour'));
    });

    test('doublon (U11) : duplicate + original_time', () {
      final ack = ServerAck.fromJson({'duplicate': true, 'original_time': '2025/01/01 09:59:00'});
      expect(ack.duplicate, isTrue);
      expect(ack.summary(), contains('déjà reçu le 2025/01/01 09:59:00'));
      expect(ack.hasCounters, isFalse);
      expect(ack.nbRows, isNull);
    });

    test('garde anti-suppression (U22) : warning NO_DELETE_GUARD', () {
      final ack = ServerAck.fromJson({'warning': 'NO_DELETE_GUARD', 'nb_delete_prevented': 4});
      expect(ack.summary(), contains('4 suppression(s) évitée(s)'));
    });

    test('correction levée (U19) : correction_cleared', () {
      final ack = ServerAck.fromJson({'correction_cleared': true});
      expect(ack.correctionCleared, isTrue);
      expect(ack.summary(), contains('demande de correction levée'));
    });

    test('ack vide → summary vide, toJson conserve raw', () {
      final ack = ServerAck.fromJson({});
      expect(ack.summary(), '');
      final j = {'sync_id': 'x', 'nb_insert': 1};
      expect(ServerAck.fromJson(j).toJson(), j);
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 4. SendOutcome (U06)
  // ═══════════════════════════════════════════════════════════════════════════
  group('SendOutcome', () {
    SendOutcome o(SendOutcomeKind k, {String? filter}) =>
        SendOutcome(kind: k, idEtab: '101', idQst: '3', idFilter: filter);

    test('isSuccess uniquement pour ok', () {
      expect(o(SendOutcomeKind.ok).isSuccess, isTrue);
      for (final k in SendOutcomeKind.values.where((k) => k != SendOutcomeKind.ok)) {
        expect(o(k).isSuccess, isFalse, reason: '$k');
      }
    });

    test('isFailure : tout sauf ok / noData / skipped', () {
      expect(o(SendOutcomeKind.ok).isFailure, isFalse);
      expect(o(SendOutcomeKind.noData).isFailure, isFalse);
      expect(o(SendOutcomeKind.skipped).isFailure, isFalse);
      expect(o(SendOutcomeKind.kosave).isFailure, isTrue);
      expect(o(SendOutcomeKind.rejected).isFailure, isTrue);
      expect(o(SendOutcomeKind.locked).isFailure, isTrue);
      expect(o(SendOutcomeKind.networkError).isFailure, isTrue);
      expect(o(SendOutcomeKind.yearMismatch).isFailure, isTrue);
    });

    test('asLegacyBool : compat ancien contrat (ok et kosave = "reçu")', () {
      expect(o(SendOutcomeKind.ok).asLegacyBool, isTrue);
      expect(o(SendOutcomeKind.kosave).asLegacyBool, isTrue);
      expect(o(SendOutcomeKind.networkError).asLegacyBool, isFalse);
    });

    test('toStatus : mapping vers SyncStatus ; noData/skipped → null', () {
      expect(o(SendOutcomeKind.ok).toStatus, SyncStatus.sent);
      expect(o(SendOutcomeKind.kosave).toStatus, SyncStatus.kosave);
      expect(o(SendOutcomeKind.locked).toStatus, SyncStatus.locked);
      expect(o(SendOutcomeKind.rejected).toStatus, SyncStatus.rejected);
      expect(o(SendOutcomeKind.yearMismatch).toStatus, SyncStatus.rejected);
      expect(o(SendOutcomeKind.networkError).toStatus, SyncStatus.failed);
      expect(o(SendOutcomeKind.noData).toStatus, isNull);
      expect(o(SendOutcomeKind.skipped).toStatus, isNull);
    });

    test('clés historiques etab_qst[_filtre]', () {
      expect(o(SendOutcomeKind.ok).key, '101_3');
      expect(o(SendOutcomeKind.ok, filter: '21').key, '101_3_21');
      expect(o(SendOutcomeKind.ok, filter: '').key, '101_3');
      expect(o(SendOutcomeKind.ok, filter: '21').keyWithoutEtab, '3_21');
    });

    test('withLabels / describe nominatif', () {
      final base = SendOutcome(
          kind: SendOutcomeKind.networkError, idEtab: '101', idQst: '3', message: 'timeout');
      final lab = base.withLabels(libEtab: 'EP Gitega', libQst: 'Effectifs');
      expect(lab.libEtab, 'EP Gitega');
      expect(lab.libQst, 'Effectifs');
      expect(lab.kind, SendOutcomeKind.networkError);
      expect(lab.describe(), 'EP Gitega › Effectifs : Erreur réseau — timeout');
      expect(base.describe(), 'Étab. 101 › Thème 3 : Erreur réseau — timeout');
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 5. SchoolSyncSummary (U05)
  // ═══════════════════════════════════════════════════════════════════════════
  group('SchoolSyncSummary', () {
    test('agrégats', () {
      const s = SchoolSyncSummary(idEtab: '101', total: 5, sent: 2, pending: 2, failed: 1);
      expect(s.allSent, isFalse);
      expect(s.hasIssues, isTrue);
      expect(s.hasPending, isTrue);
      expect(s.toSend, 3);
      const ok = SchoolSyncSummary(idEtab: '101', total: 3, sent: 3);
      expect(ok.allSent, isTrue);
      expect(ok.toSend, 0);
      const empty = SchoolSyncSummary(idEtab: '101');
      expect(empty.allSent, isFalse);
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 6. SyncFeedback (U19)
  // ═══════════════════════════════════════════════════════════════════════════
  group('SyncFeedback.appliesTo', () {
    SyncFeedback fb({String? qst, String? filter, String statut = 'A_CORRIGER'}) => SyncFeedback(
        idCamp: '7', codeTypeAnnee: '2024', idEtab: '101', idQst: qst, idFilter: filter,
        statut: statut, commentaire: '', fetchedAt: DateTime(2025));

    test('portée établissement (idQst null) → tous les thèmes', () {
      expect(fb().appliesTo('3', null), isTrue);
      expect(fb().appliesTo('9', '21'), isTrue);
    });

    test('portée thème sans filtre → toutes les périodes du thème', () {
      expect(fb(qst: '3').appliesTo('3', null), isTrue);
      expect(fb(qst: '3').appliesTo('3', '21'), isTrue);
      expect(fb(qst: '3').appliesTo('4', null), isFalse);
    });

    test('portée thème + période → période exacte', () {
      expect(fb(qst: '3', filter: '21').appliesTo('3', '21'), isTrue);
      expect(fb(qst: '3', filter: '21').appliesTo('3', '22'), isFalse);
      expect(fb(qst: '3', filter: '21').appliesTo('3', null), isFalse);
    });

    test('isCorrection / isLock', () {
      expect(fb().isCorrection, isTrue);
      expect(fb(statut: 'VALIDE').isLock, isTrue);
      expect(fb(statut: 'VERROUILLE').isLock, isTrue);
      expect(fb(statut: 'RECU').isLock, isFalse);
    });

    test('fromJson (sync_status_ws.php /feedback)', () {
      final f = SyncFeedback.fromJson({
        'code_etab': 101, 'nom': 'EP Gitega', 'id_theme': '', 'code_filtre': null,
        'statut': 'A_CORRIGER', 'commentaire': 'Effectifs incohérents', 'admin': 'dpe1',
        'date': '2025/01/02 08:00:00',
      }, idCamp: '7', codeTypeAnnee: '2024');
      expect(f.idEtab, '101');
      expect(f.idQst, isNull);           // '' → null (portée établissement)
      expect(f.idFilter, isNull);
      expect(f.libEtab, 'EP Gitega');
      expect(f.isCorrection, isTrue);
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 7. ServerFormStatus.parseServerTime
  // ═══════════════════════════════════════════════════════════════════════════
  group('ServerFormStatus.parseServerTime', () {
    test("format serveur 'Y/m/d H:i:s'", () {
      expect(ServerFormStatus.parseServerTime('2025/03/04 15:06:07'),
          DateTime(2025, 3, 4, 15, 6, 7));
    });
    test('ISO 8601 accepté en repli ; vide / null → null', () {
      expect(ServerFormStatus.parseServerTime('2025-03-04T15:06:07'),
          DateTime(2025, 3, 4, 15, 6, 7));
      expect(ServerFormStatus.parseServerTime(''), isNull);
      expect(ServerFormStatus.parseServerTime(null), isNull);
    });
  });
}
