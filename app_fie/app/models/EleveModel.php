<?php
/**
 * app_fie/app/models/EleveModel.php
 * Modèle CRUD pour la table eleves.
 */

class EleveModel
{
    /**
     * Crée un élève et génère son IUE.
     *
     * @param array $data   Données validées de l'élève
     * @param int   $codeSecteur
     * @param int   $codeAnnee
     * @return array  ['id' => int, 'iue' => string]
     */
    public static function create(array $data, int $codeSecteur, int $codeAnnee): array
    {
        $iue = IueGenerator::generate($codeSecteur, $codeAnnee);

        Database::query(
            "INSERT INTO eleves (
                iue, nom, prenoms, sexe, date_naissance, lieu_naissance, province_naissance,
                nationalite, numero_acte_naissance, date_acte_naissance, commune_acte,
                nom_pere, nom_mere, nom_tuteur, telephone_tuteur, adresse_tuteur,
                photo_path, statut, doublon_suspect, doublon_iue_ref,
                created_by, updated_by
            ) VALUES (
                :iue, :nom, :prenoms, :sexe, :ddn, :lieu, :prov_naiss,
                :nat, :acte_num, :acte_date, :acte_commune,
                :pere, :mere, :tuteur, :tel_tuteur, :adr_tuteur,
                :photo, 'actif',
                :doublon, :doublon_ref,
                :created_by, :created_by
            )",
            [
                ':iue'          => $iue,
                ':nom'          => strtoupper(trim($data['nom'])),
                ':prenoms'      => ucwords(strtolower(trim($data['prenoms']))),
                ':sexe'         => $data['sexe'],
                ':ddn'          => $data['date_naissance'],
                ':lieu'         => $data['lieu_naissance']       ?? null,
                ':prov_naiss'   => $data['province_naissance']   ?? null,
                ':nat'          => $data['nationalite']          ?? 'BDI',
                ':acte_num'     => $data['numero_acte_naissance']?? null,
                ':acte_date'    => $data['date_acte_naissance']  ?? null,
                ':acte_commune' => $data['commune_acte']         ?? null,
                ':pere'         => $data['nom_pere']             ?? null,
                ':mere'         => $data['nom_mere']             ?? null,
                ':tuteur'       => $data['nom_tuteur']           ?? null,
                ':tel_tuteur'   => $data['telephone_tuteur']     ?? null,
                ':adr_tuteur'   => $data['adresse_tuteur']       ?? null,
                ':photo'        => $data['photo_path']           ?? null,
                ':doublon'      => 0,
                ':doublon_ref'  => null,
                ':created_by'   => $data['created_by']           ?? null,
            ]
        );

        $id = (int)Database::lastInsertId();

        // Vérification doublon post-création
        $doublons = IueGenerator::detectDoublons(
            $data['nom'], $data['prenoms'], $data['date_naissance'],
            $data['lieu_naissance'] ?? null, $iue
        );
        if (!empty($doublons)) {
            Database::query(
                "UPDATE eleves SET doublon_suspect=1, doublon_iue_ref=? WHERE id=?",
                [$doublons[0], $id]
            );
        }

        return ['id' => $id, 'iue' => $iue];
    }

    /**
     * Trouve un élève par son IUE.
     */
    public static function findByIue(string $iue): ?array
    {
        return Database::fetchOne(
            "SELECT e.*, GROUP_CONCAT(i.id) AS inscription_ids
             FROM eleves e
             LEFT JOIN inscriptions i ON i.eleve_id = e.id
             WHERE e.iue = ?
             GROUP BY e.id",
            [$iue]
        );
    }

    /**
     * Trouve un élève par son ID.
     */
    public static function findById(int $id): ?array
    {
        return Database::fetchOne("SELECT * FROM eleves WHERE id = ?", [$id]);
    }

    /**
     * Recherche multi-critères (pour l'AJAX de déduplication).
     */
    public static function search(array $criteria, int $page = 1, int $perPage = 25): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($criteria['nom'])) {
            $where[] = "nom LIKE ?";
            $params[] = '%' . $criteria['nom'] . '%';
        }
        if (!empty($criteria['prenoms'])) {
            $where[] = "prenoms LIKE ?";
            $params[] = '%' . $criteria['prenoms'] . '%';
        }
        if (!empty($criteria['date_naissance'])) {
            $where[] = "date_naissance = ?";
            $params[] = $criteria['date_naissance'];
        }
        if (!empty($criteria['sexe'])) {
            $where[] = "sexe = ?";
            $params[] = $criteria['sexe'];
        }
        if (!empty($criteria['iue'])) {
            $where[] = "iue = ?";
            $params[] = $criteria['iue'];
        }

        $whereSql = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = (int)Database::fetchScalar(
            "SELECT COUNT(*) FROM eleves WHERE $whereSql", $params
        );

        $rows  = Database::fetchAll(
            "SELECT id, iue, nom, prenoms, sexe, date_naissance, lieu_naissance, statut
             FROM eleves WHERE $whereSql
             ORDER BY nom, prenoms LIMIT $perPage OFFSET $offset",
            $params
        );

        return ['total' => $total, 'rows' => $rows, 'page' => $page, 'per_page' => $perPage];
    }

    /**
     * Met à jour les informations d'un élève (données personnelles uniquement,
     * l'IUE est immuable).
     */
    public static function update(int $id, array $data): bool
    {
        $result = Database::query(
            "UPDATE eleves SET
                nom=?, prenoms=?, sexe=?, date_naissance=?,
                lieu_naissance=?, province_naissance=?,
                nom_pere=?, nom_mere=?, nom_tuteur=?,
                telephone_tuteur=?, adresse_tuteur=?,
                updated_by=?, updated_at=NOW()
             WHERE id=?",
            [
                strtoupper(trim($data['nom'])),
                ucwords(strtolower(trim($data['prenoms']))),
                $data['sexe'],
                $data['date_naissance'],
                $data['lieu_naissance']     ?? null,
                $data['province_naissance'] ?? null,
                $data['nom_pere']           ?? null,
                $data['nom_mere']           ?? null,
                $data['nom_tuteur']         ?? null,
                $data['telephone_tuteur']   ?? null,
                $data['adresse_tuteur']     ?? null,
                $data['updated_by']         ?? null,
                $id,
            ]
        );
        return $result->rowCount() > 0;
    }

    /**
     * Calcule le CODE_TYPE_AGE StatEduc à partir de la date de naissance
     * et de l'année scolaire (règle : âge au 1er janvier de l'année scolaire).
     *
     * @param string $dateNaissance  'YYYY-MM-DD'
     * @param int    $codeAnnee      Année de début (ex: 2025 pour 2025-2026)
     * @return int                   CODE_TYPE_AGE (1–20)
     */
    public static function computeCodeTypeAge(string $dateNaissance, int $codeAnnee): int
    {
        $anneeRef = (int)date('Y', strtotime("$codeAnnee-01-01"));
        $anneeNaiss = (int)substr($dateNaissance, 0, 4);
        $age = $anneeRef - $anneeNaiss;

        // Correction : si l'anniversaire n'est pas encore passé au 1er janvier
        // (tous les élèves ont leur âge calculé au 31 décembre de l'année de début)
        if ($age < 0)  $age = 0;
        if ($age <= 2)  return 1;  // moins de 3 ans
        if ($age >= 21) return 20; // 21 ans et plus
        return $age - 1;           // 3 ans → code 2, 4 ans → code 3, …, 20 ans → code 19
    }
}
