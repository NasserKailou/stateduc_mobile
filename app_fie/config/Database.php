<?php
/**
 * app_fie/config/Database.php
 * Singleton PDO — connexion MySQL pour l'application FIE.
 * Utilise PDO avec requêtes préparées (protection injection SQL).
 */

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Retourne l'instance PDO unique (pattern Singleton).
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // En production : ne pas exposer le message d'erreur
                $msg = FIE_DEBUG ? $e->getMessage() : 'Erreur de connexion à la base de données.';
                throw new RuntimeException($msg, 500);
            }
        }
        return self::$instance;
    }

    /**
     * Helper : exécute une requête préparée et retourne le PDOStatement.
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $pdo  = self::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Helper : retourne toutes les lignes d'une requête SELECT.
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Helper : retourne une seule ligne.
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    /**
     * Helper : retourne la valeur d'une seule colonne.
     */
    public static function fetchScalar(string $sql, array $params = [])
    {
        return self::query($sql, $params)->fetchColumn();
    }

    /**
     * Démarre une transaction.
     */
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    /**
     * Valide une transaction.
     */
    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    /**
     * Annule une transaction.
     */
    public static function rollback(): void
    {
        self::getInstance()->rollBack();
    }

    /**
     * Retourne l'ID du dernier INSERT.
     */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }
}
