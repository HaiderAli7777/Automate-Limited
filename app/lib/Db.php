<?php
/* A thin PDO wrapper. Every query goes through prepared statements. */
declare(strict_types=1);

final class Db
{
    private PDO $pdo;

    public function __construct(array $c)
    {
        $host = (string) ($c['host'] ?? 'localhost');
        $port = (int) ($c['port'] ?? 3306);
        $name = (string) ($c['name'] ?? '');
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $this->pdo = new PDO($dsn, (string) ($c['user'] ?? ''), (string) ($c['pass'] ?? ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);
        $this->pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($params));
        return $stmt;
    }

    public function all(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    public function one(string $sql, array $params = []): ?array
    {
        $row = $this->run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public function value(string $sql, array $params = [])
    {
        $v = $this->run($sql, $params)->fetchColumn();
        return $v === false ? null : $v;
    }

    public function column(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Rows keyed by their first column. */
    public function keyed(string $sql, array $params = []): array
    {
        $out = [];
        foreach ($this->all($sql, $params) as $row) {
            $out[reset($row)] = $row;
        }
        return $out;
    }

    /** Two-column result as key => value. */
    public function pairs(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql = 'INSERT INTO `' . $table . '` (`' . implode('`, `', $cols) . '`) VALUES ('
            . implode(', ', array_fill(0, count($cols), '?')) . ')';
        $this->run($sql, array_values($data));
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $params = []): int
    {
        $sets = [];
        foreach (array_keys($data) as $col) {
            $sets[] = '`' . $col . '` = ?';
        }
        $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $sets) . ' WHERE ' . $where;
        return $this->run($sql, array_merge(array_values($data), array_values($params)))->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        return $this->run('DELETE FROM `' . $table . '` WHERE ' . $where, $params)->rowCount();
    }

    public function tx(callable $fn)
    {
        $this->pdo->beginTransaction();
        try {
            $result = $fn($this);
            $this->pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}

/** "?, ?, ?" for an IN () list. Never call with an empty list. */
function in_list(array $values): string
{
    return implode(', ', array_fill(0, max(1, count($values)), '?'));
}
