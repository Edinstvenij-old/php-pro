<?php
namespace Core\Traits;
use App\Enums\DB\SQL;
use PDO;
use splitbrain\phpcli\Exception;
trait Queryable
{
    static protected string|null $tableName = null;
    static protected string $query = '';
    protected array $commands = [];
    public static function __callStatic(string $name, array $arguments)
    {
        if (in_array($name, ['where'])) {
            return call_user_func_array([new static, $name], $arguments);
        }
        throw new Exception("Static method not allowed", 422);
    }


    protected function openCondition(): void
    {
        if (in_array('beginCondition', $this->commands)) {
            static::$query .= ' (';

            unset(
                $this->commands[
                array_search('beginCondition', $this->commands)
                ]
            );
        }
    }

    public function beginCondition(): static
    {
        $this->commands[] = 'beginCondition';
        return $this;
    }

    public function endCondition(): static
    {
        static::$query .= ') ';
        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        if (in_array($name, ['where'])) {
            return call_user_func_array([$this, $name], $arguments);
        }
        throw new Exception("Static method not allowed", 422);
    }
    static public function select(array $column = ['*']): static
    {
        static::resetQuery();
        static::$query = 'SELECT ' . implode(', ', $column) . ' FROM ' . static::$tableName;
        $obj = new static;
        $obj->commands[] = 'select';
        return $obj;
    }
    static public function all(array $column = ['*']): array
    {
        return static::select($column)->get();
    }
    static public function find(int $id): static|false
    {
        $query = db()->prepare('SELECT * FROM ' . static::$tableName . ' WHERE id = :id');
        $query->bindParam('id', $id);
        $query->execute();
        return $query->fetchObject(static::class);
    }
    static public function findBy(string $column, mixed $value): static|false
    {
        $query = db()->prepare("SELECT * FROM " . static::$tableName . " WHERE $column = :$column");
        $query->bindParam($column, $value);
        $query->execute();
        return $query->fetchObject(static::class);
    }
    static public function create(array $fields): null|static
    {
        $params = static::prepareQueryParams($fields);
        $query = db()->prepare("INSERT INTO " . static::$tableName . " ($params[keys]) VALUES ($params[placeholders])");
        if (!$query->execute($fields)) {
            return null;
        }
        return static::find(db()->lastInsertId());
    }
    static protected function prepareQueryParams(array $fields): array
    {
        $keys = array_keys($fields);
        $placeholders = preg_filter('/^/', ':', $keys); // name = :name
        return [
            'keys' => implode(', ', $keys),
            'placeholders' => implode(', ', $placeholders)
        ];
    }
    static public function destroy(int $id): bool
    {
        $query = db()->prepare("DELETE FROM " . static::$tableName . " WHERE id = :id");
        $query->bindParam('id', $id);
        return $query->execute();
    }
    static protected function resetQuery(): void
    {
        static::$query = '';
    }
    public function get(): array
    {
        return db()->query(static::$query)->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    public function first(): static|null
    {
        return $this->get()[0] ?? null;
    }


    protected function where(string $column, SQL $operator = SQL::EQUAL, mixed $value = null): static
    {
        $this->prevent(['order', 'limit', 'having', 'group'], 'WHERE can not be used after');
        $obj = in_array('select', $this->commands) ? $this : static::select();
        if (
            !is_null($value) &&
            !is_bool($value) &&
            !is_numeric($value) &&
            !is_array($value)
        ) {
            $value = "'$value'";
        }
        if (is_null($value)) {
            $value = SQL::NULL->value;
        }
        if (is_array($value)) {
            $value = array_map(fn($item) => is_string($item) && $item !== SQL::NULL->value ? "'$item'" : $item, $value);
            $value = '(' . implode(', ', $value) . ')';
        }
        if (!in_array('where', $obj->commands)) {
            static::$query .= " WHERE";
            $obj->commands[] = 'where';
        }
        static::$query .= " $column $operator->value $value";
        return $obj;
    }
    public function and(string $column, SQL $operator = SQL::EQUAL, mixed $value = null): static
    {
        $this->require(['where'], 'AND can not be used without');
        static::$query .= " AND";
        $this->commands[] = 'and';

        $this->openCondition();

        return $this->where($column, $operator, $value);
    }

    public function or(string $column, SQL $operator = SQL::EQUAL, mixed $value = null): static
    {
        $this->require(['where'], 'OR can not be used without');
        static::$query .= " OR";
        $this->commands[] = 'or';

        $this->openCondition();

        return $this->where($column, $operator, $value);
    }

    /**
     * @param string $table
     * @param array $conditions = [
     *      [
     *          'left' => '',
     *          'operator' => '',
     *          'right' => '',
     *      ]
     * ]
     * @param string $type
     * @return $this
     */
    public function join(string $table, array $conditions, string $type = 'LEFT'): static
    {
        $this->require(['select'], 'JOIN can not be used without');

        $this->commands[] = 'join';

        static::$query .= " $type JOIN $table ON ";

        $lastKey = array_key_last($conditions);
        foreach ($conditions as $key => $condition) {
            static::$query .= "$condition[left] $condition[operator] $condition[right]" . ($key !== $lastKey ? ' AND ' : '');
        }
        return $this;
    }

    public function orderBy(array $columns): static
    {
        $this->require(['select'], 'ORDER BY can not be used without');
        $this->commands[] = 'order';
        $lastKey = array_key_last($columns);
        static::$query .= " ORDER BY ";
        foreach ($columns as $column => $order) {
            static::$query .= "$column $order" . ($column !== $lastKey ? ', ' : '');
        }
        return $this;
    }
    public function exists(): bool
    {
        $this->require(['select'], 'Method exists() can not be called without');
        return !empty($this->get());
    }

    public function sql(): string
    {
        return static::$query;
    }

    public function update(array $fields): static
    {
        $query = "UPDATE " . static::$tableName . " SET " . $this->updatePlaceholders(array_keys($fields)) . " WHERE id = :id";
        $query = db()->prepare($query);
        $fields['id'] = $this->id;
        $query->execute($fields);
        return static::find($this->id);
    }
    protected function updatePlaceholders(array $keys): string
    {
        $string = '';
        $lastKey = array_key_last($keys);
        foreach ($keys as $index => $key) {
            $string .= "$key = :$key" . ($index !== $lastKey ? ', ' : '');
        }
        return $string;
    }
    protected function prevent(array $preventMethods, string $text = ''): void
    {
        foreach ($preventMethods as $method) {
            if (in_array($method, $this->commands)) {
                $message = sprintf(
                    "%s: %s [%s]",
                    static::class,
                    $text,
                    $method
                );
                throw new Exception($message, 422);
            }
        }
    }
    protected function require(array $requireMethods, string $text = ''): void
    {
        foreach ($requireMethods as $method) {
            if (!in_array($method, $this->commands)) {
                $message = sprintf(
                    "%s: %s [%s]",
                    static::class,
                    $text,
                    $method
                );
                throw new Exception($message, 422);
            }
        }
    }
}