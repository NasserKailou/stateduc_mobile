<?php
/**
 * app_fie/app/models/InscriptionModel.php
 * Modèle pour les inscriptions.
 */

class InscriptionModel
{
    /**
     * Crée une inscription en liant l'élève à un établissement/niveau/année.
     * Déclenche automatiquement le recalcul des agrégats.
     *
     * @param int   $eleveId
     * @param array $data     Données d'inscription validées
     * @return int  ID de l'inscription
     */
    public static function create(int $eleveId, array $data): int
    {
        Database::query(
            "INSERT INTO inscriptions (
                eleve_id, code_etablissement, code_type_secteur_ens,
                code_type_annee, code_type_niveau, code_type_section,
                numero_classe, date_inscription, date_debut_annee,
                statut, code_etab_precedent, annee_precedente,
                motif_arrivee, frais_inscription, bourse, matricule_etab,
                created_by, updated_by
            ) VALUES (
                :eleve, :etab, :secteur, :annee, :niveau, :section,
                :classe, :date_insc, :date_debut,
                'inscrit', :etab_prec, :annee_prec,
                :motif, :frais, :bourse, :matricule,
                :created_by, :updated_by
            )",
            [
                ':eleve'      => $eleveId,
                ':etab'       => $data['code_etablissement'],
                ':secteur'    => $data['code_type_secteur_ens'],
                ':annee'      => $data['code_type_annee'],
                ':niveau'     => $data['code_type_niveau'],
                ':section'    => $data['code_type_section']  ?? 1,
                ':classe'     => $data['numero_classe']       ?? null,
                ':date_insc'  => $data['date_inscription'],
                ':date_debut' => $data['date_debut_annee']   ?? null,
                ':etab_prec'  => $data['code_etab_precedent'] ?? null,
                ':annee_prec' => $data['annee_precedente']   ?? null,
                ':motif'      => $data['motif_arrivee']       ?? null,
                ':frais'      => $data['frais_inscription']   ?? null,
                ':bourse'     => $data['bourse']              ?? 0,
                ':matricule'  => $data['matricule_etab']      ?? null,
                ':created_by' => $data['created_by']          ?? null,
                ':updated_by' => $data['created_by']          ?? null,
            ]
        );
        $id = (int)Database::lastInsertId();

        // Recalcul asynchrone des agrégats pour cet étab/année
        AggregateService::recalculate(
            (int)$data['code_etablissement'],
            (int)$data['code_type_annee']
        );

        return $id;
    }

    /**
     * Retourne les inscriptions d'un élève (toutes années).
     */
    public static function forEleve(int $eleveId): array
    {
        return Database::fetchAll(
            "SELECT i.*, e.nom_etablissement, e.chaine_localisation,
                    rn.libelle AS libelle_niveau, rs.libelle AS libelle_secteur
             FROM inscriptions i
             JOIN etablissements_miroir e ON e.code_etablissement = i.code_etablissement
             LEFT JOIN ref_type_niveau  rn ON rn.code_type_niveau   = i.code_type_niveau
             LEFT JOIN ref_secteur_ens  rs ON rs.code_type_secteur_ens = i.code_type_secteur_ens
             WHERE i.eleve_id = ?
             ORDER BY i.code_type_annee DESC",
            [$eleveId]
        );
    }

    /**
     * Liste des inscriptions d'un établissement pour une année.
     */
    public static function forEtab(int $codeEtab, int $codeAnnee, int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;
        $total  = (int)Database::fetchScalar(
            "SELECT COUNT(*) FROM inscriptions WHERE code_etablissement=? AND code_type_annee=?",
            [$codeEtab, $codeAnnee]
        );
        $rows = Database::fetchAll(
            "SELECT i.id, i.eleve_id, e.iue, e.nom, e.prenoms, e.sexe, e.date_naissance,
                    i.code_type_niveau, i.code_type_section, i.numero_classe,
                    i.statut, i.date_inscription
             FROM inscriptions i
             JOIN eleves e ON e.id = i.eleve_id
             WHERE i.code_etablissement=? AND i.code_type_annee=?
             ORDER BY e.nom, e.prenoms
             LIMIT $perPage OFFSET $offset",
            [$codeEtab, $codeAnnee]
        );
        return ['total' => $total, 'rows' => $rows, 'page' => $page, 'per_page' => $perPage];
    }
}
