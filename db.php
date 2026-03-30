<?php
require_once __DIR__ . '/config.php';

class DB {
    private static ?PDO $pdo = null;

    public static function get(): PDO {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }

    public static function q(string $sql, array $params = []): \PDOStatement {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function row(string $sql, array $p = []): ?array {
        return self::q($sql, $p)->fetch() ?: null;
    }

    public static function rows(string $sql, array $p = []): array {
        return self::q($sql, $p)->fetchAll();
    }

    public static function val(string $sql, array $p = []) {
        return self::q($sql, $p)->fetchColumn();
    }

    public static function lastId(): string {
        return self::get()->lastInsertId();
    }
}
?>
