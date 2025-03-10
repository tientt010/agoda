<?php

namespace Core;

use Config\Database;  // Thêm dòng này

/**
 * BaseModel - Class cơ sở cho tất cả models:
 * - Kết nối và quản lý database 
 * - CRUD operations cơ bản
 * - Query builder methods
 * - Validation dữ liệu
 * - Xử lý lỗi
 * - Quản lý transactions
 */
class BaseModel
{
    // Kết nối database
    protected $db;

    // Tên bảng trong database
    protected $table;

    // Khóa chính của bảng
    protected $primaryKey = 'id';

    // Các trường được phép gán giá trị hàng loạt
    protected $fillable = [];

    // Lưu trữ các lỗi
    protected $errors = [];

    // Query builder properties
    protected $select = '*';
    protected $whereConditions = [];
    protected $orderBy = [];
    protected $limit = null;
    protected $offset = null;

    public function __construct()
    {
        try {
            // Sử dụng đường dẫn đầy đủ cho Database
            $database = new \Config\Database();
            $this->db = $database->connect();

            // Tăng timeout cho transactions
            $this->db->setAttribute(\PDO::ATTR_TIMEOUT, 60);
            $this->db->exec("SET innodb_lock_wait_timeout=50");
            $this->db->exec("SET SESSION wait_timeout=60");
        } catch (\Exception $e) {

            // Log error và throw exception
            error_log($e->getMessage());
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Tìm bản ghi theo ID
     * @param int $id ID của bản ghi cần tìm
     * @return array|false Trả về mảng dữ liệu hoặc false nếu không tìm thấy
     */
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tất cả bản ghi
     * @return array Mảng các bản ghi
     */
    public function all()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Thêm bản ghi mới
     * @param array $data Dữ liệu cần thêm
     * @return int|false ID của bản ghi mới hoặc false nếu thất bại
     */
    public function create($data)
    {
        // Lọc dữ liệu theo fillable
        $data = $this->filterFillable($data);

        $fields = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($values)";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute(array_values($data))) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Cập nhật bản ghi
     * @param int $id ID của bản ghi cần cập nhật
     * @param array $data Dữ liệu mới
     * @return bool Kết quả cập nhật
     */
    public function update($id, $data)
    {
        // Lọc dữ liệu theo fillable
        $data = $this->filterFillable($data);

        $fields = array_map(function ($field) {
            return "$field = ?";
        }, array_keys($data));

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) .
            " WHERE {$this->primaryKey} = ?";

        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Xóa bản ghi
     * @param int $id ID của bản ghi cần xóa
     * @return bool Kết quả xóa
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Lọc dữ liệu theo fillable
     * @param array $data Dữ liệu cần lọc
     * @return array Dữ liệu đã lọc
     */
    protected function filterFillable($data)
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Select specific columns
     * @param string|array $columns
     * @return $this
     */
    public function select($columns)
    {
        $this->select = is_array($columns) ? implode(', ', $columns) : $columns;
        return $this;
    }

    /**
     * Where clause with prepared statements
     * @param string $field
     * @param mixed $value
     * @param string $operator
     * @return $this
     */
    public function where($field, $value, $operator = '=')
    {
        $this->whereConditions[] = [
            'field' => $field,
            'value' => $value,
            'operator' => $operator
        ];
        return $this;
    }

    /**
     * Order by clause
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function orderBy($field, $direction = 'ASC')
    {
        $this->orderBy[] = "$field $direction";
        return $this;
    }

    /**
     * Set limit and offset
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit($limit, $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    /**
     * Execute the built query
     * @return array
     */
    public function get()
    {
        try {
            $query = "SELECT {$this->select} FROM {$this->table}";
            $values = [];

            // Add where conditions
            if (!empty($this->whereConditions)) {
                $whereClauses = [];
                foreach ($this->whereConditions as $condition) {
                    $whereClauses[] = "{$condition['field']} {$condition['operator']} ?";
                    $values[] = $condition['value'];
                }
                $query .= " WHERE " . implode(' AND ', $whereClauses);
            }

            // Add order by
            if (!empty($this->orderBy)) {
                $query .= " ORDER BY " . implode(', ', $this->orderBy);
            }

            // Add limit and offset
            if ($this->limit !== null) {
                $query .= " LIMIT {$this->limit}";
                if ($this->offset !== null) {
                    $query .= " OFFSET {$this->offset}";
                }
            }

            $stmt = $this->db->prepare($query);
            $stmt->execute($values);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->errors[] = "Database error: " . $e->getMessage();
            return false;
        } finally {
            // Reset query builder properties
            $this->resetQuery();
        }
    }

    /**
     * Reset query builder properties
     */
    protected function resetQuery()
    {
        $this->select = '*';
        $this->whereConditions = [];
        $this->orderBy = [];
        $this->limit = null;
        $this->offset = null;
    }

    /**
     * Kiểm tra dữ liệu
     * @param array $data Dữ liệu cần kiểm tra
     * @param array $rules
     * @return bool Kết quả kiểm tra
     */
    protected function validate($data, $rules = [])
    {
        $this->errors = [];

        foreach ($rules as $field => $rule) {
            // Required validation
            if (strpos($rule, 'required') !== false && empty($data[$field])) {
                $this->errors[$field][] = ucfirst($field) . ' is required';
            }

            // Email validation
            if (strpos($rule, 'email') !== false && !empty($data[$field])) {
                if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = 'Invalid email format';
                }
            }

            // Min length validation
            if (preg_match('/min:(\d+)/', $rule, $matches) && !empty($data[$field])) {
                $min = $matches[1];
                if (strlen($data[$field]) < $min) {
                    $this->errors[$field][] = ucfirst($field) . " must be at least $min characters";
                }
            }

            // Max length validation
            if (preg_match('/max:(\d+)/', $rule, $matches) && !empty($data[$field])) {
                $max = $matches[1];
                if (strlen($data[$field]) > $max) {
                    $this->errors[$field][] = ucfirst($field) . " must not exceed $max characters";
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Lấy lỗi
     * @return array Mảng các lỗi
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
