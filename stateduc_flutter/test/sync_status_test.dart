// =============================================================================
// sync_status_test.dart — AK-SYNC (branche ak_burundi)
// Tests unitaires des modèles de synchronisation (lib/models/sync_status.dart)
// =============================================================================
//
// Testé en isolation (aucune dépendance SQLite / réseau) :
//   1. SyncStatus       — codes, compatibilité 'draft', needsSend / isProblem
//   2. FormSyncStatus   — clé composite, verrou admin, détection de conflit (U21)
//   3. ServerAck        — parsing se_ack (U08), doublon (U11), garde suppression (U22)
//   4. SendOutcome      — isSuccess / isFailure / toStatus / clés legacy / libellés (U06)
//   5. ServerFormStatus — parse 'Y/m/d H:i:s' + toReconcileMap (U17)
//   6. SyncFeedback     — appliesTo (U19/U21) et parsing WS /feedback
//   7. SchoolSyncSummary— agrégats (U05)
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
    test('fromCode retrouve chaque état par son nom', () {
      for (final s in SyncStatus.values) {
        expect(SyncStatusX.fromCode(s.code), s);
      }
    });

    test("fromCode : 'draft' (ancien libellé) et inconnu → pending", () {
      expect(SyncStatusX.fromCode('draft'), SyncStatus.pending);
      expect(SyncStatusX.fromCode('???'), SyncStatus.pending);
      expect(SyncStatusX.fromCode(null), SyncStatus.pending);
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

    test('label et shortLabel non vides pour tous les états', () {
      for (final s in SyncStatus.values) {
        expect(s.label, isNotEmpty);
        expect(s.shortLabel, isNotEmpty);
      }
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 2. FormSyncStatus
  // ═══════════════════════════════════════════════════════════════════════════
  group('FormSyncStatus', () {
    FormSyncStatus mk({
      SyncStatus status = SyncStatus.pending,
      String idFilter = '',
      DateTime? lastSuccessAt,
      DateTime? localUpdatedAt,
      DateTime? serverLastOk,
      String? adminStatus,
    }) =>
        FormSyncStatus(
          codeTypeAnnee: '2024', idCamp: '12', idEtab: '4567', idQst: '3',
          idFilter: idFilter, status: status,
          lastSuccessAt: lastSuccessAt, localUpdatedAt: localUpdatedAt,
          serverLastOk: serverLastOk, adminStatus: adminStatus,
        );

    test('clé composite = annee|camp|etab|qst|filtre (filtre vide autorisé)', () {
      expect(mk().key, '2024|12|4567|3|');
      expect(mk(idFilter: '21').key, '2024|12|4567|3|21');
      expect(mk().idFilterOrNull, isNull);
      expect(mk(idFilter: '21').idFilterOrNull, '21');
    });

    test('isLocked via statut local OU statut admin VALIDE/VERROUILLE', () {
      expect(mk(status: SyncStatus.locked).isLocked, isTrue);
      expect(mk(adminStatus: 'VALIDE').isLocked, isTrue);
      expect(mk(adminStatus: 'VERROUILLE').isLocked, isTrue);
      expect(mk(adminStatus: 'RECU').isLocked, isFalse);
      expect(mk(adminStatus: 'A_CORRIGER').correctionRequested, isTrue);
    });

    test('hasServerConflict : serveur plus récent que le dernier envoi + modifs locales', () {
      final t0 = DateTime(2026, 9, 1, 10, 0);
      final t1 = DateTime(2026, 9, 1, 11, 0);
      // pas de date serveur → pas de conflit
      expect(mk(lastSuccessAt: t0).hasServerConflict, isFalse);
      // déjà envoyé (rien à envoyer) → pas de conflit même si serveur plus récent
      expect(mk(status: SyncStatus.sent, lastSuccessAt: t0, serverLastOk: t1).hasServerConflict, isFalse);
      // modifs locales + serveur postérieur à notre dernier envoi → conflit
      expect(mk(status: SyncStatus.pending, lastSuccessAt: t0, serverLastOk: t1).hasServerConflict, isTrue);
      // modifs locales + serveur antérieur → pas de conflit
      expect(mk(status: SyncStatus.pending, lastSuccessAt: t1, serverLastOk: t0).hasServerConflict, isFalse);
      // jamais envoyé ni sauvegardé localement mais serveur a des données → conflit potentiel
      expect(mk(status: SyncStatus.pending, serverLastOk: t0).hasServerConflict, isTrue);
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 3. ServerAck (se_ack)
  // ═══════════════════════════════════════════════════════════════════════════
  group('ServerAck', () {
    test('fromJson : compteurs numériques ou chaînes, doublon, garde suppression', () {
      final ack = ServerAck.fromJson({
        'sync_id': 'a1b2', 'server_time': '2026-09-16T10:00:00',
        'nb_insert': 3, 'nb_update': '7', 'nb_delete': 0, 'nb_unchanged': '12',
        'duplicate': false, 'warning': 'NO_DELETE_GUARD', 'nb_delete_prevented': 5,
        'correction_cleared': true,
      });
      expect(ack.syncId, 'a1b2');
      expect(ack.nbInsert, 3);
      expect(ack.nbUpdate, 7);
      expect(ack.nbUnchanged, 12);
      expect(ack.hasCounters, isTrue);
      expect(ack.nbRows, 22);
      expect(ack.duplicate, isFalse);
      expect(ack.correctionCleared, isTrue);
      final s = ack.summary();
      expect(s, contains('3 ajout(s)'));
      expect(s, contains('7 mise(s) à jour'));
      expect(s, contains('5 suppression(s) évitée(s)'));
      expect(s, contains('demande de correction levée'));
    });

    test('doublon (U11) : duplicate=true + original_time dans le résumé', () {
      final ack = ServerAck.fromJson({'duplicate': true, 'original_time': '2026/09/16 09:58:00'});
      expect(ack.duplicate, isTrue);
      expect(ack.hasCounters, isFalse);
      expect(ack.nbRows, isNull);
      expect(ack.summary(), contains('déjà reçu le 2026/09/16 09:58:00'));
    });

    test('ack vide (ancien serveur sans se_ack) : résumé vide, pas d\'exception', () {
      final ack = ServerAck.fromJson({});
      expect(ack.hasCounters, isFalse);
      expect(ack.summary(), isEmpty);
    });

    test('toJson conserve le JSON brut reçu', () {
      final j = {'sync_id': 'x', 'nb_insert': 1, 'extra': 'kept'};
      expect(ServerAck.fromJson(j).toJson(), j);
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 4. SendOutcome (U06)
  // ═══════════════════════════════════════════════════════════════════════════
  group('SendOutcome', () {
    test('isSuccess / isFailure / asLegacyBool par type', () {
      const ok = SendOutcome(kind: SendOutcomeKind.ok);
      const ko = SendOutcome(kind: SendOutcomeKind.kosave);
      const rej = SendOutcome(kind: SendOutcomeKind.rejected);
      const lock = SendOutcome(kind: SendOutcomeKind.locked);
      const net = SendOutcome(kind: SendOutcomeKind.networkError);
      const nod = SendOutcome(kind: SendOutcomeKind.noData);
      const skip = SendOutcome(kind: SendOutcomeKind.skipped);

      expect(ok.isSuccess, isTrue);
      expect(ok.isFailure, isFalse);
      for (final o in [ko, rej, lock, net]) {
        expect(o.isSuccess, isFalse, reason: '${o.kind}');
        expect(o.isFailure, isTrue, reason: '${o.kind}');
      }
      // noData / skipped : ni succès ni échec (ignorés)
      expect(nod.isSuccess, isFalse);
      expect(nod.isFailure, isFalse);
      expect(skip.isFailure, isFalse);
      // Contrat historique : KOSAVE comptait comme "reçu" (true)
      expect(ok.asLegacyBool, isTrue);
      expect(ko.asLegacyBool, isTrue);
      expect(net.asLegacyBool, isFalse);
    });

    test('toStatus : mapping vers SyncStatus (KOSAVE ≠ sent)', () {
      expect(const SendOutcome(kind: SendOutcomeKind.ok).toStatus, SyncStatus.sent);
      expect(const SendOutcome(kind: SendOutcomeKind.kosave).toStatus, SyncStatus.kosave);
      expect(const SendOutcome(kind: SendOutcomeKind.locked).toStatus, SyncStatus.locked);
      expect(const SendOutcome(kind: SendOutcomeKind.rejected).toStatus, SyncStatus.rejected);
      expect(const SendOutcome(kind: SendOutcomeKind.yearMismatch).toStatus, SyncStatus.rejected);
      expect(const SendOutcome(kind: SendOutcomeKind.networkError).toStatus, SyncStatus.failed);
      expect(const SendOutcome(kind: SendOutcomeKind.noData).toStatus, isNull);
      expect(const SendOutcome(kind: SendOutcomeKind.skipped).toStatus, isNull);
    });

    test('clés legacy « etab_qst[_filtre] » (compat Map<String,bool>)', () {
      const a = SendOutcome(kind: SendOutcomeKind.ok, idEtab: '4567', idQst: '3');
      const b = SendOutcome(kind: SendOutcomeKind.ok, idEtab: '4567', idQst: '3', idFilter: '21');
      const c = SendOutcome(kind: SendOutcomeKind.ok, idEtab: '4567', idQst: '3', idFilter: '');
      expect(a.key, '4567_3');
      expect(b.key, '4567_3_21');
      expect(c.key, '4567_3');
      expect(a.keyWithoutEtab, '3');
      expect(b.keyWithoutEtab, '3_21');
    });

    test('describe : nominatif, avec repli sur les identifiants', () {
      const anon = SendOutcome(kind: SendOutcomeKind.networkError, idEtab: '4567', idQst: '3',
          message: 'timeout');
      expect(anon.describe(), 'Étab. 4567 › Thème 3 : Erreur réseau — timeout');
      final named = anon.withLabels(libEtab: 'EP Gitega I', libQst: 'Effectifs');
      expect(named.describe(), 'EP Gitega I › Effectifs : Erreur réseau — timeout');
      expect(named.kind, SendOutcomeKind.networkError);
      expect(named.message, 'timeout');
      // withLabels ne remplace pas un libellé déjà présent par null
      expect(named.withLabels().libEtab, 'EP Gitega I');
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 5. ServerFormStatus (WS /camp, /etab — U17)
  // ═══════════════════════════════════════════════════════════════════════════
  group('ServerFormStatus', () {
    test("parseServerTime : 'Y/m/d H:i:s' et ISO, null si vide/invalide", () {
      expect(ServerFormStatus.parseServerTime('2026/09/16 14:05:09'), DateTime(2026, 9, 16, 14, 5, 9));
      expect(ServerFormStatus.parseServerTime('2026-09-16T14:05:09'), DateTime(2026, 9, 16, 14, 5, 9));
      expect(ServerFormStatus.parseServerTime(''), isNull);
      expect(ServerFormStatus.parseServerTime(null), isNull);
      expect(ServerFormStatus.parseServerTime('n/a'), isNull);
    });

    test('fromJson + toReconcileMap', () {
      final f = ServerFormStatus.fromJson({
        'id_theme': 3, 'libelle': 'Effectifs', 'statut': 'OK',
        'last_ok_time': '2026/09/16 14:05:09', 'nb_tentatives': '2', 'nb_rows': 40,
        'locked': true, 'admin': {'statut': 'VALIDE', 'commentaire': 'ok'},
      });
      expect(f.idTheme, '3');
      expect(f.nbTentatives, 2);
      expect(f.locked, isTrue);
      expect(f.adminStatus, 'VALIDE');
      final m = f.toReconcileMap();
      expect(m['statut'], 'OK');
      expect(m['statut_admin'], 'VALIDE');
      expect(m['commentaire'], 'ok');
      expect(DateTime.parse(m['date'] as String), DateTime(2026, 9, 16, 14, 5, 9));
    });

    test('statut absent → AUCUN ; date absente → null', () {
      final f = ServerFormStatus.fromJson({'id_theme': '9'});
      expect(f.statut, 'AUCUN');
      expect(f.toReconcileMap()['date'], isNull);
      expect(f.adminStatus, isNull);
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 6. SyncFeedback (WS /feedback — U19/U21)
  // ═══════════════════════════════════════════════════════════════════════════
  group('SyncFeedback', () {
    SyncFeedback fb({String? idQst, String? idFilter, String statut = 'A_CORRIGER'}) => SyncFeedback(
          idCamp: '12', codeTypeAnnee: '2024', idEtab: '4567',
          idQst: idQst, idFilter: idFilter, statut: statut, commentaire: 'c',
          fetchedAt: DateTime(2026, 9, 16),
        );

    test('isCorrection / isLock', () {
      expect(fb(statut: 'A_CORRIGER').isCorrection, isTrue);
      expect(fb(statut: 'A_CORRIGER').isLock, isFalse);
      expect(fb(statut: 'VALIDE').isLock, isTrue);
      expect(fb(statut: 'VERROUILLE').isLock, isTrue);
      expect(fb(statut: 'RECU').isLock, isFalse);
    });

    test('appliesTo : portée établissement, thème, thème+période', () {
      // Tout l'établissement
      expect(fb().appliesTo('3', null), isTrue);
      expect(fb().appliesTo('7', '21'), isTrue);
      // Thème précis, toutes périodes
      expect(fb(idQst: '3').appliesTo('3', null), isTrue);
      expect(fb(idQst: '3').appliesTo('3', '21'), isTrue);
      expect(fb(idQst: '3').appliesTo('4', null), isFalse);
      // Thème + période
      expect(fb(idQst: '3', idFilter: '21').appliesTo('3', '21'), isTrue);
      expect(fb(idQst: '3', idFilter: '21').appliesTo('3', '22'), isFalse);
      expect(fb(idQst: '3', idFilter: '21').appliesTo('3', null), isFalse);
      // Filtre vide côté feedback = toutes périodes
      expect(fb(idQst: '3', idFilter: '').appliesTo('3', '22'), isTrue);
    });

    test('fromJson : chaînes vides → null (portée globale)', () {
      final f = SyncFeedback.fromJson({
        'code_etab': 4567, 'nom': 'EP Gitega I', 'id_theme': '', 'code_filtre': null,
        'statut': 'A_CORRIGER', 'commentaire': 'Revoir effectifs', 'admin': 'dpe1',
        'date': '2026/09/16 09:00:00',
      }, idCamp: '12', codeTypeAnnee: '2024');
      expect(f.idEtab, '4567');
      expect(f.idQst, isNull);
      expect(f.idFilter, isNull);
      expect(f.isCorrection, isTrue);
      expect(f.appliesTo('3', '21'), isTrue);
      expect(f.libEtab, 'EP Gitega I');
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 7. SchoolSyncSummary (U05)
  // ═══════════════════════════════════════════════════════════════════════════
  group('SchoolSyncSummary', () {
    test('agrégats', () {
      const s = SchoolSyncSummary(idEtab: '1', total: 6, sent: 3, pending: 2, failed: 1);
      expect(s.allSent, isFalse);
      expect(s.hasPending, isTrue);
      expect(s.hasIssues, isTrue);
      expect(s.toSend, 3);
      const done = SchoolSyncSummary(idEtab: '1', total: 4, sent: 4);
      expect(done.allSent, isTrue);
      expect(done.toSend, 0);
      const none = SchoolSyncSummary(idEtab: '1');
      expect(none.allSent, isFalse);
    });
  });
}
