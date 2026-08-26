<?php
/**
 * app_fie/services/IueGenerator.php
 * ══════════════════════════════════════════════════════════════════════════════
 * Générateur de l'Identifiant Unique de l'Élève (IUE)
 *
 * Format : BI-SSSS-AAAA-NNNNNN-CC
 *   BI      = Code pays ISO 3166-1 alpha-2
 *   SSSS    = Code sous-secteur d'enseignement sur 4 chiffres (ex: 0002 pour Fondamental)
 *   AAAA    = Année scolaire de première inscription (ex: 2025)
 *   NNNNNN  = Numéro de séquence, unique par (secteur, année), zero-padded sur 6
 *   CC      = 2 chiffres de contrôle (algorithme Luhn modifié)
 *
 * Exemple : BI-0002-2025-000042-07
 *
 * Propriétés :
 *   - Persistant : l'IUE ne change jamais, même lors d'un transfert d'établissement
 *     ou d'un passage dans un autre niveau/secteur.
 *   - Opaque : le numéro de séquence ne révèle pas d'informations personnelles.
 *   - Vérifiable : le code de contrôle permet de détecter les erreurs de saisie.
 *   - Sans collision : la table iue_sequences utilise un LOCK pour l'atomicité.
 * ══════════════════════════════════════════════════════════════════════════════
 */

class IueGenerator
{
    /**
     * Génère un nouvel IUE unique et atomique.
     *
     * @param int $codeSecteur   CODE_TYPE_SECTEUR_ENS
     * @param int $codeAnnee     CODE_TYPE_ANNEE (ex: 2025 pour 2025-2026)
     * @return string            IUE formaté : "BI-0002-2025-000042-07"
     * @throws RuntimeException  Si la séquence ne peut pas être incrémentée
     */
    public static function generate(int $codeSecteur, int $codeAnnee): string
    {
        $seq = self::nextSequence($codeSecteur, $codeAnnee);

        $country = IUE_COUNTRY_CODE;                     // BI
        $ssss    = str_pad($codeSecteur, 4, '0', STR_PAD_LEFT);
        $aaaa    = str_pad($codeAnnee,   4, '0', STR_PAD_LEFT);
        $nnnnnn  = str_pad($seq,          6, '0', STR_PAD_LEFT);

        // Corps sans les chiffres de contrôle
        $body = $country . $ssss . $aaaa . $nnnnnn;

        // Calcul du code de contrôle (2 chiffres)
        $cc = str_pad(self::computeCheckDigits($body), 2, '0', STR_PAD_LEFT);

        return "$country-$ssss-$aaaa-$nnnnnn-$cc";
    }

    /**
     * Valide un IUE existant (format + chiffres de contrôle).
     *
     * @param string $iue  L'IUE à valider (ex: "BI-0002-2025-000042-07")
     * @return bool
     */
    public static function validate(string $iue): bool
    {
        if (!preg_match(IUE_FORMAT_REGEX, $iue)) return false;

        // Extraire les parties
        $parts = explode('-', $iue);
        // Format: [0]=BI, [1]=SSSS, [2]=AAAA, [3]=NNNNNN, [4]=CC
        if (count($parts) !== 5) return false;

        [$country, $ssss, $aaaa, $nnnnnn, $cc] = $parts;
        $body     = $country . $ssss . $aaaa . $nnnnnn;
        $expected = str_pad(self::computeCheckDigits($body), 2, '0', STR_PAD_LEFT);

        return hash_equals($expected, $cc);
    }

    /**
     * Décode un IUE en ses composants.
     *
     * @return array ['country', 'secteur', 'annee', 'seq', 'check', 'valid']
     */
    public static function decode(string $iue): array
    {
        $valid = self::validate($iue);
        if (!preg_match('/^([A-Z]{2})-(\d{4})-(\d{4})-(\d{6})-(\d{2})$/', $iue, $m)) {
            return ['valid' => false];
        }
        return [
            'country' => $m[1],
            'secteur' => (int)$m[2],
            'annee'   => (int)$m[3],
            'seq'     => (int)$m[4],
            'check'   => (int)$m[5],
            'valid'   => $valid,
        ];
    }

    /**
     * Vérifie si un IUE existe déjà en base (détection de doublon).
     */
    public static function exists(string $iue): bool
    {
        $count = Database::fetchScalar(
            "SELECT COUNT(*) FROM eleves WHERE iue = ?",
            [$iue]
        );
        return (int)$count > 0;
    }

    /**
     * Détecte les doublons potentiels pour un élève (même nom, prénom, date de naissance).
     * Retourne les IUE des enregistrements similaires.
     *
     * @return array  Tableau d'IUE suspects
     */
    public static function detectDoublons(
        string $nom,
        string $prenoms,
        string $dateNaissance,
        ?string $lieuNaissance = null,
        ?string $excludeIue    = null
    ): array {
        $sql = "
            SELECT iue, nom, prenoms, date_naissance, lieu_naissance
            FROM eleves
            WHERE SOUNDEX(nom) = SOUNDEX(?)
              AND SOUNDEX(prenoms) = SOUNDEX(?)
              AND date_naissance = ?
        ";
        $params = [$nom, $prenoms, $dateNaissance];

        if ($excludeIue) {
            $sql .= " AND iue != ?";
            $params[] = $excludeIue;
        }

        $rows = Database::fetchAll($sql, $params);

        // Affinage : si lieu de naissance disponible, scorer
        if ($lieuNaissance && !empty($rows)) {
            $scored = [];
            foreach ($rows as $r) {
                $score = 100; // déjà confirmé par SOUNDEX + date
                if ($r['lieu_naissance'] &&
                    similar_text(strtolower($lieuNaissance), strtolower($r['lieu_naissance'])) > 5) {
                    $score += 20;
                }
                $r['_score'] = $score;
                $scored[] = $r;
            }
            usort($scored, fn($a, $b) => $b['_score'] <=> $a['_score']);
            return array_column($scored, 'iue');
        }

        return array_column($rows, 'iue');
    }

    // ── Interne : séquence atomique ───────────────────────────────────────────

    /**
     * Incrémente atomiquement la séquence et retourne le prochain numéro.
     * Utilise INSERT...ON DUPLICATE KEY UPDATE + SELECT FOR UPDATE pour garantir
     * l'atomicité dans un environnement multi-utilisateurs.
     */
    private static function nextSequence(int $secteur, int $annee): int
    {
        $pdo = Database::getInstance();

        // LOCK : éviter la race condition sur la séquence
        $pdo->exec("LOCK TABLES iue_sequences WRITE");
        try {
            $pdo->exec("
                INSERT INTO iue_sequences (code_type_secteur_ens, code_type_annee, last_seq)
                VALUES ($secteur, $annee, 1)
                ON DUPLICATE KEY UPDATE last_seq = last_seq + 1
            ");
            $seq = (int)$pdo->query("
                SELECT last_seq FROM iue_sequences
                WHERE code_type_secteur_ens = $secteur AND code_type_annee = $annee
            ")->fetchColumn();

            if ($seq > 999999) {
                throw new OverflowException(
                    "Séquence IUE saturée pour secteur=$secteur, annee=$annee (max 999 999)"
                );
            }
            return $seq;
        } finally {
            $pdo->exec("UNLOCK TABLES");
        }
    }

    /**
     * Calcul du code de contrôle (2 chiffres).
     * Algorithme : somme pondérée des valeurs numériques des caractères
     * (lettres converties en position A=10, B=11, …), modulo 97.
     * Compatible avec la vérification IBAN (ISO 7064 MOD 97-10).
     */
    private static function computeCheckDigits(string $body): int
    {
        // Déplacer les 2 premières lettres (BI) en fin, remplacer par valeurs numériques
        $rearranged = substr($body, 2) . substr($body, 0, 2);

        // Convertir lettres en chiffres (A=10, B=11, ..., Z=35)
        $numeric = '';
        foreach (str_split($rearranged) as $ch) {
            if (ctype_alpha($ch)) {
                $numeric .= (string)(ord(strtoupper($ch)) - 55);
            } else {
                $numeric .= $ch;
            }
        }

        // Calcul modulo 97 en blocs (évite les limites des entiers PHP)
        $remainder = 0;
        foreach (str_split($numeric, 9) as $block) {
            $remainder = ($remainder . $block) % 97;
        }

        // Résultat : 98 - remainder (entre 2 et 98, tronqué à 2 chiffres)
        return (98 - $remainder) % 100;
    }
}
