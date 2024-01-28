<?php

require_once 'loadEnv.php';

class Database
{
    private $pdo;

    public function __construct()
    {
        loadEnv();
        $this->connect();
    }

    private function connect()
    {
        $host = $_ENV['DB_HOST'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];
        $dbname = $_ENV['DB_NAME'];
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Failed to connect to DATABASE: " . $e->getMessage());
        }
    }

    public function query(string $sql, array $replacements, ?bool $debug = false): array
    {
        if ($debug) {
            echo $sql;
        }

        $stmt = $this->pdo->prepare($sql);

        foreach ($replacements as $key => $data) {
            $value = $data['value'];
            $type = $data['type'];
            $stmt->bindValue(":$key", $value, $type);
        }

        if (!$stmt->execute()) {
            throw new Exception('PDO statement execution failed: ' . $stmt->errorInfo()[2]);
        }

        if ($stmt->columnCount() > 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return ['affected_rows' => $stmt->rowCount()];
        }
    }
}
