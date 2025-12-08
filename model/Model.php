<?php
include $_SERVER["DOCUMENT_ROOT"] . "/logic/Database.php";
abstract class Model
{
    protected static ?string $tableName = null;

    // ------------------------------------------------------------
    // Методы работы с сырыми запросами
    // ------------------------------------------------------------

    /**
     * Построение WHERE условия в правильном формате для Database::prepare
     * 
     * @param array|null $conditions Формат массива условий:
     * [
     *     'field1' => [
     *         'operation' => '=', // Оператор сравнения (=, !=, >, <, LIKE и т.д.)
     *         'value' => mixed    // Значение для сравнения
     *     ],
     *     'field2' => [
     *         [
     *             'operation' => '>',
     *             'value' => 10
     *         ],
     *         [
     *             'operation' => '<',
     *             'value' => 20
     *         ]
     *     ]
     * ]
     * @param int $startCounter Начальное значение для нумерации параметров
     * @return array Массив [whereSql, params]
     */
    private static function buildWhereClause(
        ?array $conditions,
        int $startCounter = 0
    ): array {
        if (empty($conditions)) {
            return ['', []];
        }

        $fields = static::getFields();
        $whereParts = [];
        $params = [];
        $counter = $startCounter;

        foreach ($conditions as $field => $fieldConditions) {
            if (!array_key_exists($field, $fields)) {
                throw new InvalidArgumentException(
                    "Поле '$field' не существует в таблице " . static::$tableName
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

                // Обработка NULL
                if ($value === null) {
                    if (strtoupper($operation) === '=') {
                        $whereParts[] = "`{$field}` IS NULL";
                        continue;
                    } elseif (strtoupper($operation) === '!=') {
                        $whereParts[] = "`{$field}` IS NOT NULL";
                        continue;
                    }
                }

                $paramName = 'where_' . $counter++;
                $whereParts[] = "`{$field}` {$operation} :" . $paramName;

                // Конвертация типа для PDO
                $type = self::convertPhpTypeToPdoType($value, $fields[$field]);
                $params[$paramName] = [
                    'type' => $type,
                    'value' => $value
                ];
            }
        }

        $whereSql = implode(' AND ', $whereParts);
        return [$whereSql, $params];
    }

    /**
     * Построение всех частей запроса SELECT
     * 
     * @param array|null $columns Формат: ['column1', 'column2', ...] или null для всех колонок
     * @param array|null $conditions Формат условий (см. buildWhereClause)
     * @param int|null $limit Ограничение количества записей
     * @param int|null $offset Смещение
     * @return array Массив [selectSql, whereSql, params]
     */
    private static function buildQueryParts(
        ?array $columns,
        ?array $conditions,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        // SELECT часть
        if ($columns === null) {
            $selectSql = "*";
        } else {
            $fields = static::getFields();
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
     * Конвертация PHP типа в PDO тип
     */
    private static function convertPhpTypeToPdoType(mixed $value, string $fieldType): int
    {
        if ($fieldType === 'i') {
            return PDO::PARAM_INT;
        }

        if ($fieldType === 's') {
            return PDO::PARAM_STR;
        }

        // Автоматическое определение по значению
        return match (true) {
            is_int($value) => PDO::PARAM_INT,
            is_bool($value) => PDO::PARAM_BOOL,
            is_null($value) => PDO::PARAM_NULL,
            default => PDO::PARAM_STR,
        };
    }

    // ------------------------------------------------------------
    // Основные методы запросов (после методов работы с сырыми запросами)
    // ------------------------------------------------------------

    /**
     * Получение всех записей
     * 
     * @param array|null $columns Формат: ['column1', 'column2', ...] или null для всех колонок
     * @return array Массив записей
     */
    public static function getAll(?array $columns = null): array
    {
        // SELECT часть
        if ($columns === null) {
            $selectSql = "*";
        } else {
            $fields = static::getFields();
            foreach ($columns as $column) {
                if (!array_key_exists($column, $fields)) {
                    throw new InvalidArgumentException(
                        "Колонка '$column' не существует в таблице"
                    );
                }
            }
            $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);
            $selectSql = implode(", ", $escapedColumns);
        }

        $sql = "SELECT {$selectSql} FROM `" . static::$tableName . "`";
        return Database::query($sql)->fetchAll();
    }

    /**
     * Получение записей по условиям
     * 
     * @param array|null $columns Формат: ['column1', 'column2', ...] или null для всех колонок
     * @param array|null $conditions Формат условий (см. buildWhereClause)
     * @param int|null $limit Ограничение количества записей
     * @param int|null $offset Смещение
     * @return array Массив записей
     */
    public static function getBy(
        ?array $columns = null,
        ?array $conditions = null,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        [$selectSql, $whereSql, $params] = self::buildQueryParts(
            $columns,
            $conditions,
            $limit,
            $offset
        );

        $sql = "SELECT {$selectSql} FROM `" . static::$tableName . "`";
        if (!empty($whereSql)) {
            $sql .= " WHERE {$whereSql}";
        }

        // Добавляем LIMIT и OFFSET
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            $params['limit'] = [
                'type' => PDO::PARAM_INT,
                'value' => $limit
            ];

            if ($offset !== null) {
                $sql .= " OFFSET :offset";
                $params['offset'] = [
                    'type' => PDO::PARAM_INT,
                    'value' => $offset
                ];
            }
        }

        return Database::prepare($sql, $params)->fetchAll();
    }

    /**
     * Подсчет количества записей по условиям
     * 
     * @param array|null $conditions Формат условий (см. buildWhereClause)
     * @return int Количество записей
     */
    public static function countBy(?array $conditions = null): int
    {
        [$whereSql, $params] = self::buildWhereClause($conditions);

        $sql = "SELECT COUNT(*) as total FROM `" . static::$tableName . "`";
        if (!empty($whereSql)) {
            $sql .= " WHERE {$whereSql}";
        }

        $result = Database::prepare($sql, $params)->fetch();
        return (int) $result['total'];
    }

    /**
     * Удаление записей по условиям
     * 
     * @param array|null $conditions Формат условий (см. buildWhereClause)
     * @return int Количество удаленных записей
     */
    public static function deleteBy(?array $conditions = null): int
    {
        if (empty($conditions)) {
            throw new LogicException("Удаление без условий запрещено");
        }

        [$whereSql, $params] = self::buildWhereClause($conditions);

        $sql = "DELETE FROM `" . static::$tableName . "` WHERE {$whereSql}";
        $stmt = Database::prepare($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Создание записи
     * 
     * @param array $data Формат: ['field1' => 'value1', 'field2' => 'value2', ...]
     * @return string|int ID созданной записи
     */
    public static function create(array $data): string|int
    {
        if (empty($data)) {
            throw new InvalidArgumentException("Нет данных для создания");
        }

        $fields = static::getFields();
        $columns = [];
        $placeholders = [];
        $params = [];
        $counter = 0;

        foreach ($data as $field => $value) {
            if (!array_key_exists($field, $fields)) {
                throw new InvalidArgumentException(
                    "Поле '$field' не существует в таблице " . static::$tableName
                );
            }

            $paramName = 'param_' . $counter++;
            $columns[] = "`{$field}`";
            $placeholders[] = ":" . $paramName;

            // Подготовка параметра в нужном формате
            $type = self::convertPhpTypeToPdoType($value, $fields[$field]);
            $params[$paramName] = [
                'type' => $type,
                'value' => $value
            ];
        }

        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            static::$tableName,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        Database::prepare($sql, $params);
        return Database::getConnection()->lastInsertId();
    }

    /**
     * Обновление записей по условиям
     * 
     * @param array $data Формат: ['field1' => 'newValue1', 'field2' => 'newValue2', ...]
     * @param array|null $conditions Формат условий (см. buildWhereClause)
     * @return int Количество обновленных записей
     */
    public static function update(array $data, ?array $conditions = null): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException("Нет данных для обновления");
        }

        if (empty($conditions)) {
            throw new LogicException("Обновление без условий запрещено");
        }

        $fields = static::getFields();
        $setParts = [];
        $params = [];
        $counter = 0;

        // SET часть
        foreach ($data as $field => $value) {
            if (!array_key_exists($field, $fields)) {
                throw new InvalidArgumentException(
                    "Поле '$field' не существует в таблице " . static::$tableName
                );
            }

            $paramName = 'set_' . $counter++;
            $setParts[] = "`{$field}` = :" . $paramName;

            $type = self::convertPhpTypeToPdoType($value, $fields[$field]);
            $params[$paramName] = [
                'type' => $type,
                'value' => $value
            ];
        }

        // WHERE часть
        [$whereSql, $whereParams] = self::buildWhereClause($conditions, $counter);
        $params = array_merge($params, $whereParams);

        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE %s",
            static::$tableName,
            implode(', ', $setParts),
            $whereSql
        );

        $stmt = Database::prepare($sql, $params);
        return $stmt->rowCount();
    }

    // ------------------------------------------------------------
    // Синтаксический сахар (в самом низу)
    // ------------------------------------------------------------

    /**
     * Обновление записи по ID
     * 
     * @param int|string $id ID записи
     * @param array $data Формат: ['field1' => 'newValue1', 'field2' => 'newValue2', ...]
     * @return bool true если запись была обновлена
     */
    public static function updateById(int|string $id, array $data): bool
    {
        $updated = self::update($data, [
            'id' => ['operation' => '=', 'value' => $id]
        ]);
        return $updated > 0;
    }

    /**
     * Удаление записи по ID
     * 
     * @param int|string $id ID записи
     * @return int Количество удаленных записей
     */
    public static function deleteById(int|string $id): int
    {
        return self::deleteBy([
            "id" => [
                "operation" => "=",
                "value" => $id
            ]
        ]);
    }

    abstract protected static function getFields(): array;
}
