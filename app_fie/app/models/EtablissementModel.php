<?php
/**
 * app_fie/app/models/EtablissementModel.php
 * ══════════════════════════════════════════════════════════════════════════════
 * Modèle pour la table miroir etablissements_miroir (ATLAS_COLLINE).
 *
 * Cascades :
 *   Province → Commune → Colline → Établissement
 *
 * Stratégie :
 *   1. Les cascades primaires utilisent les tables ref_province / ref_commune /
 *      ref_colline (codes entiers, alimentées par SyncService).
 *   2. Fallback sur etablissements_miroir (colonnes texte) si les ref tables
 *      sont vides (avant le 1er import/sync).
 * ══════════════════════════════════════════════════════════════════════════════
 */

class EtablissementModel
{
    // ══════════════════════════════════════════════════════════════════════════
    // CASCADE : Provinces
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Liste des provinces (depuis ref_province, fallback sur etablissements_miroir).
     * Retourne [['code_province' => ..., 'libelle' => ...], ...]
     */
    public static function getProvinces(): array
    {
        // Source primaire : ref_province (alimentée lors de la sync/import)
        $rows = Database::fetchAll(
            "SELECT code_province, libelle
             FROM ref_province
             ORDER BY libelle"
        );

        if (!empty($rows)) return $rows;

        // Fallback : texte depuis etablissements_miroir
        return Database::fetchAll(
            "SELECT DISTINCT code_province, province AS libelle
             FROM etablissements_miroir
             WHERE actif = 1 AND province IS NOT NULL AND code_province IS NOT NULL
             ORDER BY province"
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CASCADE : Communes d'une province (par code entier)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Communes d'une province donnée (code entier).
     * @param int $codeProvince  CODE_PROVINCE (ex: 117)
     * @return array  [['code_commune' => ..., 'libelle' => ...], ...]
     */
    public static function getCommunesByCode(int $codeProvince): array
    {
        $rows = Database::fetchAll(
            "SELECT code_commune, libelle
             FROM ref_commune
             WHERE code_province = ?
             ORDER BY libelle",
            [$codeProvince]
        );

        if (!empty($rows)) return $rows;

        // Fallback texte
        return Database::fetchAll(
            "SELECT DISTINCT code_commune, commune AS libelle
             FROM etablissements_miroir
             WHERE actif = 1 AND code_province = ? AND commune IS NOT NULL AND code_commune IS NOT NULL
             ORDER BY commune",
            [$codeProvince]
        );
    }

    /**
     * Communes d'une province (texte legacy — pour rétrocompat).
     */
    public static function getCommunes(string $province): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT code_commune, commune AS libelle
             FROM etablissements_miroir
             WHERE actif = 1 AND province = ? AND commune IS NOT NULL
             ORDER BY commune",
            [$province]
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CASCADE : Collines d'une commune (par code entier)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Collines d'une commune donnée (code entier).
     * @param int $codeCommune  CODE_COMMUNE (ex: 11716)
     * @return array  [['code_colline' => ..., 'libelle' => ...], ...]
     */
    public static function getCollinesByCode(int $codeCommune): array
    {
        $rows = Database::fetchAll(
            "SELECT code_colline, libelle
             FROM ref_colline
             WHERE code_commune = ?
             ORDER BY libelle",
            [$codeCommune]
        );

        if (!empty($rows)) return $rows;

        // Fallback texte
        return Database::fetchAll(
            "SELECT DISTINCT code_colline, colline AS libelle
             FROM etablissements_miroir
             WHERE actif = 1 AND code_commune = ? AND colline IS NOT NULL AND code_colline IS NOT NULL
             ORDER BY colline",
            [$codeCommune]
        );
    }

    /**
     * Collines (texte legacy).
     */
    public static function getCollines(string $province, string $commune, ?string $zone = null): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT code_colline, colline AS libelle
             FROM etablissements_miroir
             WHERE actif = 1 AND province = ? AND commune = ? AND colline IS NOT NULL
             ORDER BY colline",
            [$province, $commune]
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CASCADE : Établissements d'une colline (par code entier)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Établissements d'une colline/commune (codes entiers).
     * Retourne toutes les infos nécessaires pour auto-remplissage du formulaire.
     *
     * @param int      $codeColline   CODE_COLLINE (peut être 0 → pas de filtre colline)
     * @param int      $codeCommune   CODE_COMMUNE
     * @param int|null $secteur       CODE_TYPE_SECTEUR_ENS (optionnel)
     * @return array
     */
    public static function getEtablissementsByCode(
        int  $codeColline,
        int  $codeCommune,
        ?int $secteur = null
    ): array {
        $where  = "actif = 1 AND code_commune = ?";
        $params = [$codeCommune];

        if ($codeColline > 0) {
            $where .= " AND code_colline = ?";
            $params[] = $codeColline;
        }
        if ($secteur !== null) {
            $where .= " AND code_type_secteur_ens = ?";
            $params[] = $secteur;
        }

        return Database::fetchAll(
            "SELECT
                code_etablissement,
                nom_etablissement,
                code_province,    province,
                code_commune,     commune,
                code_colline,     colline,
                code_type_secteur_ens, secteur_ens,
                code_type_statut_org,  statut_org,
                code_type_milieu,      milieu,
                chaine_localisation
             FROM etablissements_miroir
             WHERE $where
             ORDER BY nom_etablissement",
            $params
        );
    }

    /**
     * Établissements (legacy texte — pour rétrocompat AJAX existant).
     */
    public static function getEtablissements(
        string $province,
        string $commune,
        ?string $zone = null,
        ?string $colline = null,
        ?int $secteur = null
    ): array {
        $params = [$province, $commune];
        $where  = "actif = 1 AND province = ? AND commune = ?";

        if ($colline) {
            $where .= " AND colline = ?";
            $params[] = $colline;
        }
        if ($secteur !== null) {
            $where .= " AND code_type_secteur_ens = ?";
            $params[] = $secteur;
        }

        return Database::fetchAll(
            "SELECT
                code_etablissement, nom_etablissement,
                code_province,    province,
                code_commune,     commune,
                code_colline,     colline,
                code_type_secteur_ens, secteur_ens,
                code_type_statut_org,  statut_org,
                code_type_milieu,      milieu,
                chaine_localisation
             FROM etablissements_miroir
             WHERE $where
             ORDER BY nom_etablissement",
            $params
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // LOOKUP INDIVIDUEL
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Trouve un établissement par son code (retourne toutes les infos ATLAS_COLLINE).
     */
    public static function findByCode(int $code): ?array
    {
        return Database::fetchOne(
            "SELECT
                code_etablissement, nom_etablissement,
                code_province,    province,
                code_commune,     commune,
                code_colline,     colline,
                code_type_secteur_ens, secteur_ens,
                code_type_statut_org,  statut_org,
                code_type_milieu,      milieu,
                chaine_localisation, actif
             FROM etablissements_miroir
             WHERE code_etablissement = ?",
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
            "SELECT
                code_etablissement, nom_etablissement,
                province, commune, colline, secteur_ens,
                chaine_localisation, code_type_secteur_ens
             FROM etablissements_miroir
             WHERE actif = 1 AND nom_etablissement LIKE ?
             ORDER BY nom_etablissement
             LIMIT ?",
            [$likeTerm, $limit]
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

    /**
     * Nombre d'établissements dans le miroir.
     */
    public static function count(): int
    {
        return (int)(Database::fetchScalar(
            "SELECT COUNT(*) FROM etablissements_miroir WHERE actif = 1"
        ) ?? 0);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // LEGACY (conservé pour rétrocompat — InscriptionController existant)
    // ══════════════════════════════════════════════════════════════════════════

    public static function getZones(string $province, string $commune): array
    {
        // ATLAS_COLLINE n'a pas de zone — on retourne tableau vide pour désactiver le step
        return [];
    }
}
