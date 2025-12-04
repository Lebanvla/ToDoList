<?php

include("../logic/Database.php");

abstract class Model
{
    protected static string $tableName; // Изменено на protected

    /**
     * Получение всех записей
     */
    public static function getAll(?array $columns = null): array
    {
        // SELECT часть
        if ($columns === null) {
            $selectSql = "*";
        } else {
            $fields = self::getFields();
            foreach ($columns as $column) {
                if (!array_key_exists($column, $fields)) {
                    throw new InvalidArgumentException(
                        "Колонка '$column' не существует в таблице"
                    );
                }
            }
            // Fix: пробел после запятой и экранирование
            $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);
            $selectSql = implode(", ", $escapedColumns);
        }

        // Fix: экранирование имени таблицы
        $sql = "SELECT {$selectSql} FROM `" . self::$tableName . "`";
        return Database::query($sql)->fetchAll();
    }

    /**
     * Получение записей по условиям
     */
    public static function getBy(?array $columns = null, ?array $conditions = null): array
    {
        [$selectSql, $whereSql, $params] = self::buildQueryParts($columns, $conditions);

        $sql = "SELECT {$selectSql} FROM `" . self::$tableName . "`";
        if (!empty($whereSql)) {
            $sql .= " WHERE {$whereSql}";
        }

        return Database::prepare($sql, $params)->fetchAll();
    }

    /**
     * Удаление записей по условиям
     */
    public static function deleteBy(?array $conditions = null): int
    {
        if (empty($conditions)) {
            // Защита от удаления всей таблицы
            throw new LogicException("Удаление без условий запрещено");
        }

        [$whereSql, $params] = self::buildWhereClause($conditions);

        $sql = "DELETE FROM `" . self::$tableName . "` WHERE {$whereSql}";
        $stmt = Database::prepare($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Создание записи
     */
    public static function create(array $data): string|int
    {
        if (empty($data)) {
            throw new InvalidArgumentException("Нет данных для создания");
        }

        $fields = self::getFields();
        $columns = [];
        $placeholders = [];
        $params = [];
        $counter = 0;

        foreach ($data as $field => $value) {
            if (!array_key_exists($field, $fields)) {
                throw new InvalidArgumentException(
                    "Поле '$field' не существует в таблице " . self::$tableName
                );
            }

            $paramName = ':ins_' . $counter++;
            $columns[] = "`{$field}`";
            $placeholders[] = $paramName;
            $params[$paramName] = $value;
        }

        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            self::$tableName,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        Database::prepare($sql, $params);
        return Database::getConnection()->lastInsertId();
    }

    /**
     * Обновление записей
     */
    public static function update(array $data, ?array $conditions = null): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException("Нет данных для обновления");
        }

        if (empty($conditions)) {
            throw new LogicException("Обновление без условий запрещено");
        }

        $fields = self::getFields();
        $setParts = [];
        $params = [];
        $counter = 0;

        // SET часть
        foreach ($data as $field => $value) {
            if (!array_key_exists($field, $fields)) {
                throw new InvalidArgumentException(
                    "Поле '$field' не существует в таблице " . self::$tableName
                );
            }

            $paramName = ':set_' . $counter++;
            $setParts[] = "`{$field}` = {$paramName}";
            $params[$paramName] = $value;
        }

        // WHERE часть
        [$whereSql, $whereParams] = self::buildWhereClause($conditions, $counter);
        $params = array_merge($params, $whereParams);

        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE %s",
            self::$tableName,
            implode(', ', $setParts),
            $whereSql
        );

        $stmt = Database::prepare($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Обновление по ID
     */
    public static function updateById(int|string $id, array $data): bool
    {
        $updated = self::update($data, [
            'id' => ['operation' => '=', 'value' => $id]
        ]);
        return $updated > 0;
    }

    /**
     * Построение частей запроса (рефакторинг для устранения дублирования)
     */
    private static function buildQueryParts(
        ?array $columns,
        ?array $conditions
    ): array {
        // SELECT часть
        if ($columns === null) {
            $selectSql = "*";
        } else {
            $fields = self::getFields();
            foreach ($columns as $column) {
                if (!array_key_exists($column, $fields)) {
                    throw new InvalidArgumentException(
                        "Колонка '$column' не существует"
                    );
                }
            }
            $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);
            $selectSql = implode(', ', $escapedColumns);
        }

        // WHERE часть
        [$whereSql, $params] = self::buildWhereClause($conditions);

        return [$selectSql, $whereSql, $params];
    }

    /**
     * Построение WHERE условия
     */
    private static function buildWhereClause(
        ?array $conditions,
        int $startCounter = 0
    ): array {
        if (empty($conditions)) {
            return ['', []];
        }

        $fields = self::getFields();
        $whereParts = [];
        $params = [];
        $counter = $startCounter;

        foreach ($conditions as $field => $fieldConditions) {
            if (!array_key_exists($field, $fields)) {
                throw new InvalidArgumentException(
                    "Поле '$field' не существует в таблице " . self::$tableName
                );
            }

            $conditionsList = isset($fieldConditions['operation'])
                ? [$fieldConditions]
                : $fieldConditions;

            foreach ($conditionsList as $condition) {
                if (!isset($condition['operation'], $condition['value'])) {
                    throw new InvalidArgumentException(
                        "Неверный формат условия для поля '$field'"
                    );
                }

                $operation = $condition['operation'];
                $value = $condition['value'];

                // Fix: поддержка NULL значений
                if ($value === null) {
                    if (strtoupper($operation) === '=') {
                        $whereParts[] = "`{$field}` IS NULL";
                        continue;
                    } elseif (strtoupper($operation) === '!=') {
                        $whereParts[] = "`{$field}` IS NOT NULL";
                        continue;
                    }
                }

                $paramName = ':param_' . $counter++;
                $whereParts[] = "`{$field}` {$operation} {$paramName}";
                $params[$paramName] = $value;
            }
        }

        $whereSql = implode(' AND ', $whereParts);
        return [$whereSql, $params];
    }

    abstract protected static function getFields(): array;
}
