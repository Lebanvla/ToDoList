<?php

class Database
{
    static ?Self $instance = null;
    private PDO $connection;
    private function __construct()
    {
        $driver = "mysql";
        $dbname = "todo_list";
        $host = "localhost";
        $user = "root";
        $password = "qq";
        $this->connection = new PDO(
            "$driver:dbname=$dbname;host=$host",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    public function __wakeup()
    {
        throw new \Exception('Not implemented');
    }

    public function __clone()
    {
        throw new \Exception('Not implemented');
    }

    public static function getInstance(): Self
    {
        if (self::$instance === null) {
            self::$instance = new Self();
        }
        return self::$instance;
    }

    public static function getConnection(): PDO
    {
        return self::getInstance()->connection;
    }

    public static function query(string $sql): PDOStatement
    {
        $result = self::getConnection()->query($sql);
        return $result;
    }

    public static function prepare(string $sql, array $fields): PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        foreach ($fields as $field => ['type' => $type, 'value' => $value]) {
            $paramName = (strpos($field, ':') === 0) ? $field : ":$field";
            $stmt->bindValue($paramName, $value, $type);
        }
        $stmt->execute();
        return $stmt;
    }
}
