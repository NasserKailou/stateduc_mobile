<?php
/**
 * app_fie/services/AggregateService.php
 * ══════════════════════════════════════════════════════════════════════════════
 * Service de calcul et d'export des agrégats ELEVES_AGE_NIVEAU_SEXE
 * vers StatEduc (SQL Server).
 *
 * Principe :
 *   1. Calcul depuis `inscriptions` + `eleves` (GROUP BY niveau, âge, section)
 *   2. Stockage dans `agregats_eleves_age_niveau_sexe` (cache local MySQL)
 *   3. Exposition via l'endpoint REST /api/endpoints/aggregates_ws.php
 *      pour que StatEduc puisse récupérer et mettre à jour sa table SQL Server.
 * ══════════════════════════════════════════════════════════════════════════════
 */

class AggregateService
{
    /**
     * Recalcule les agrégats pour un établissement + année scolaire.
     * Appelé automatiquement après chaque inscription/mouvement.
     *
     * @param int $codeEtab   CODE_ETABLISSEMENT
     * @param int $codeAnnee  CODE_TYPE_ANNEE
     */
    public static function recalculate(int $codeEtab, int $codeAnnee): void
    {
        // Récupérer toutes les inscriptions actives pour cet étab/année
        $inscriptions = Database::fetchAll(
            "SELECT i.code_type_niveau, i.code_type_section,
                    e.sexe, e.date_naissance
             FROM inscriptions i
             JOIN eleves e ON e.id = i.eleve_id
             WHERE i.code_etablissement = ?
               AND i.code_type_annee    = ?
               AND i.statut NOT IN ('abandon','exclus','transféré_sortant')",
            [$codeEtab, $codeAnnee]
        );

        // Agréger par (niveau, section, âge)
        $buckets = []; // clé = "niveau|section|codeAge"
        foreach ($inscriptions as $ins) {
            $niveau  = (int)$ins['code_type_niveau'];
            $section = (int)$ins['code_type_section'];
            $codeAge = EleveModel::computeCodeTypeAge($ins['date_naissance'], $codeAnnee);
            $key     = "$niveau|$section|$codeAge";

            if (!isset($buckets[$key])) {
                $buckets[$key] = ['filles' => 0, 'total' => 0];
            }
            $buckets[$key]['total']++;
            if ($ins['sexe'] === 'F') $buckets[$key]['filles']++;
        }

        // code_type_etat_fonct supprimé de etablissements_miroir par migration 005
        // (DROP 11 colonnes hors ATLAS_COLLINE) — valeur NULL conservée dans l'agrégat
        $etatFonct = null;

        // UPSERT dans agregats_eleves_age_niveau_sexe
        // Supprimer d'abord les lignes obsolètes (élèves désinscrit ou transférés)
        Database::query(
            "DELETE FROM agregats_eleves_age_niveau_sexe
             WHERE code_etablissement=? AND code_type_annee=?",
            [$codeEtab, $codeAnnee]
        );

        if (empty($buckets)) return;

        $values = [];
        $params = [];
        foreach ($buckets as $key => $counts) {
            [$niveau, $section, $codeAge] = explode('|', $key);
            $values[] = "(?,?,?,?,?,?,?,?,0)";
            array_push($params,
                $codeEtab, $codeAnnee, (int)$niveau, (int)$codeAge, (int)$section,
                $counts['filles'], $counts['total'], $etatFonct
            );
        }

        Database::query(
            "INSERT INTO agregats_eleves_age_niveau_sexe
             (code_etablissement, code_type_annee, code_type_niveau, code_type_age,
              code_type_section, filles_age_niveau, total_age_niveau,
              code_type_etat_fonct, synced_to_stateduc)
             VALUES " . implode(',', $values),
            $params
        );
    }

    /**
     * Retourne les agrégats non encore synchronisés vers StatEduc.
     * Formatés pour correspondre exactement à ELEVES_AGE_NIVEAU_SEXE.
     */
    public static function getPendingSync(int $limit = 5000): array
    {
        return Database::fetchAll(
            "SELECT code_etablissement, code_type_annee, code_type_niveau,
                    code_type_age, code_type_section,
                    filles_age_niveau, total_age_niveau,
                    estimation, code_type_etat_fonct
             FROM agregats_eleves_age_niveau_sexe
             WHERE synced_to_stateduc = 0
             ORDER BY code_etablissement, code_type_annee
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Marque des agrégats comme synchronisés.
     */
    public static function markSynced(array $keys): void
    {
        if (empty($keys)) return;
        foreach ($keys as $k) {
            Database::query(
                "UPDATE agregats_eleves_age_niveau_sexe
                 SET synced_to_stateduc=1, synced_at=NOW()
                 WHERE code_etablissement=? AND code_type_annee=?
                   AND code_type_niveau=? AND code_type_age=? AND code_type_section=?",
                [$k['ce'], $k['ca'], $k['cn'], $k['cag'], $k['cs']]
            );
        }
    }

    /**
     * Retourne les agrégats pour un établissement/année (toutes lignes).
     */
    public static function forEtab(int $codeEtab, int $codeAnnee): array
    {
        return Database::fetchAll(
            "SELECT a.*,
                    rn.libelle AS libelle_niveau,
                    ra.libelle AS libelle_age,
                    rs.libelle AS libelle_section
             FROM agregats_eleves_age_niveau_sexe a
             LEFT JOIN ref_type_niveau  rn ON rn.code_type_niveau    = a.code_type_niveau
             LEFT JOIN ref_type_age     ra ON ra.code_type_age        = a.code_type_age
             LEFT JOIN ref_type_section rs ON rs.code_type_section    = a.code_type_section
             WHERE a.code_etablissement=? AND a.code_type_annee=?
             ORDER BY a.code_type_niveau, a.code_type_section, a.code_type_age",
            [$codeEtab, $codeAnnee]
        );
    }
}
