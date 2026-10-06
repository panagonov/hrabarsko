<?php
/**
 * Подготвени заявки с проверка на резултата.
 * В production PDO е в ERRMODE_SILENT, затова грешките се проверяват ръчно.
 */

namespace SLiMS\Bg;

use PDO;
use PDOStatement;
use RuntimeException;
use SLiMS\DB as SlimsDB;

class Db
{
    private static ?PDO $pdo = null;

    /**
     * Връзката се взема веднъж: всяко SLiMS\DB::getInstance() изпълнява „SET NAMES“,
     * а това нулира lastInsertId().
     */
    public static function pdo(): PDO
    {
        if (self::$pdo === null) self::$pdo = SlimsDB::getInstance();
        return self::$pdo;
    }

    public static function lastInsertId(): int
    {
        return (int)self::pdo()->lastInsertId();
    }

    public static function q(string $sql, array $params = []): PDOStatement
    {
        $st = self::pdo()->prepare($sql);
        if ($st === false || $st->execute($params) === false) {
            $info = $st ? $st->errorInfo() : self::pdo()->errorInfo();
            throw new RuntimeException('DB: ' . ($info[2] ?? 'unknown error'));
        }
        return $st;
    }

    public static function value(string $sql, array $params = [])
    {
        $v = self::q($sql, $params)->fetchColumn();
        return $v === false ? null : $v;
    }

    public static function row(string $sql, array $params = []): ?array
    {
        $r = self::q($sql, $params)->fetch(PDO::FETCH_ASSOC);
        return $r === false ? null : $r;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::q($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Таблицата съществува ли (преди SQL файлът да е приложен). */
    public static function tableExists(string $table): bool
    {
        try {
            return (bool)self::value('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$table]);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
