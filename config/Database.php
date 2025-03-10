<?php

namespace Config;

class Database
{
    /**
     * Thông tin kết nối database
     */
    private $host;
    private $user;
    private $pass;
    private $dbname;
    private $charset = 'utf8mb4';
    private static $instance = null;
    private $connection = null;

    /**
     * Constructor: Khởi tạo thông tin kết nối từ config
     */
    public function __construct()
    {
        $this->host = DB_HOST;
        $this->user = DB_USER;
        $this->pass = DB_PASS;
        $this->dbname = DB_NAME;
    }

    /**
     * Tạo kết nối PDO tới database
     * @throws \Exception nếu kết nối thất bại
     */
    public function connect()
    {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
                $options = [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
                ];

                $this->connection = new \PDO($dsn, $this->user, $this->pass, $options);
                return $this->connection;
            } catch (\PDOException $e) {
                throw new \Exception("Không thể kết nối đến database: " . $e->getMessage());
            }
        }
        return $this->connection;
    }

    /**
     * Đóng kết nối database
     */
    public function close()
    {
        $this->connection = null;
    }

    /**
     * Kiểm tra trạng thái kết nối
     */
    public function isConnected()
    {
        return $this->connection !== null;
    }

    /**
     * Destructor: Đảm bảo đóng kết nối khi object bị hủy
     */
    public function __destruct()
    {
        $this->close();
    }
}
