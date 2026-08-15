<?php
/**
 * app_fie/app/models/EtablissementModel.php
 * Modèle pour la table miroir etablissements_miroir.
 * Alimente les selects dépendants (Province → Commune → Zone → Colline → Établissement).
 */

class EtablissementModel
{
    /**
     * Liste distincte des provinces (pour le 1er select).
     */
    public static function getProvinces(): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT province
             FROM etablissements_miroir
             WHERE actif = 1 AND province IS NOT NULL
             ORDER BY province"
        );
    }

    /**
     * Communes d'une province donnée.
     */
    public static function getCommunes(string $province): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT commune
             FROM etablissements_miroir
             WHERE actif = 1 AND province = ? AND commune IS NOT NULL
             ORDER BY commune",
            [$province]
        );
    }

    /**
     * Zones d'une commune donnée.
     */
    public static function getZones(string $province, string $commune): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT zone_admin
             FROM etablissements_miroir
             WHERE actif = 1 AND province = ? AND commune = ? AND zone_admin IS NOT NULL
             ORDER BY zone_admin",
            [$province, $commune]
        );
    }

    /**
     * Collines d'une zone donnée.
     */
    public static function getCollines(string $province, string $commune, ?string $zone): array
    {
        if ($zone) {
            return Database::fetchAll(
                "SELECT DISTINCT colline
                 FROM etablissements_miroir
                 WHERE actif = 1 AND province = ? AND commune = ? AND zone_admin = ? AND colline IS NOT NULL
                 ORDER BY colline",
                [$province, $commune, $zone]
            );
        }
        return Database::fetchAll(
            "SELECT DISTINCT colline
             FROM etablissements_miroir
             WHERE actif = 1 AND province = ? AND commune = ? AND colline IS NOT NULL
             ORDER BY colline",
            [$province, $commune]
        );
    }

    /**
     * Établissements d'une colline (filtré optionnellement par secteur).
     */
    public static function getEtablissements(
        string $province,
        string $commune,
        ?string $zone,
        ?string $colline,
        ?int $secteur = null
    ): array {
        $params = [$province, $commune];
        $where  = "actif = 1 AND province = ? AND commune = ?";

        if ($zone) {
            $where .= " AND zone_admin = ?";
            $params[] = $zone;
        }
        if ($colline) {
            $where .= " AND colline = ?";
            $params[] = $colline;
        }
        if ($secteur !== null) {
            $where .= " AND code_type_secteur_ens = ?";
            $params[] = $secteur;
        }

        return Database::fetchAll(
            "SELECT code_etablissement, nom_etablissement,
                    code_type_secteur_ens, code_type_milieu, chaine_localisation
             FROM etablissements_miroir
             WHERE $where
             ORDER BY nom_etablissement",
            $params
        );
    }

    /**
     * Trouve un établissement par son code.
     */
    public static function findByCode(int $code): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM etablissements_miroir WHERE code_etablissement = ?",
            [$code]
        );
    }

    /**
     * Recherche full-text d'établissements par nom.
     */
    public static function search(string $term, int $limit = 20): array
    {
        $likeTerm = '%' . str_replace('%', '\\%', $term) . '%';
        return Database::fetchAll(
            "SELECT code_etablissement, nom_etablissement, province, commune,
                    chaine_localisation, code_type_secteur_ens
             FROM etablissements_miroir
             WHERE actif = 1 AND (nom_etablissement LIKE ? OR code_ecole_pays LIKE ?)
             ORDER BY nom_etablissement
             LIMIT ?",
            [$likeTerm, $likeTerm, $limit]
        );
    }

    /**
     * Date de la dernière synchronisation depuis l'API StatEduc.
     */
    public static function getLastSyncDate(): ?string
    {
        return Database::fetchScalar(
            "SELECT MAX(ended_at) FROM sync_log WHERE status='success' AND source_type='api_stateduc'"
        );
    }
}
