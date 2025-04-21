<?php
require_once __DIR__ . "/../bootstrap.php";

class TestGateway 
{
    private PDO $conn;

    public function __construct(Database $database) 
    {
        $this->conn = $database->getConnect();
    }

    /**
    * Get all records
    * @return array
    */
    public function getAll(): array 
    {
        $sql = "SELECT * FROM `TABLE_NAME`";

        $stmt = $this->conn->query($sql);

        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return $data;
    }

    /**
    * Get a specific record by ID
    * @param string $id
    * @return array|false
    */
    public function get(string $id): array|false 
    {
        $sql = "SELECT * FROM `TABLE_NAME` WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
    * Create a new record
    * @param array $data
    * @return string
    */
    public function create(array $data): string 
    {
        $sql = "INSERT INTO `TABLE_NAME` 
            (/* entity fields */) 
            VALUES 
            (/* :bindings */)";

        $stmt = $this->conn->prepare($sql);

        // Bind values (example placeholders)
        // $stmt->bindValue(':field', $data['field'], PDO::PARAM_STR);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    /**
    * Update an existing record
    * @param string $id
    * @param array $data
    * @return void
    */
    public function update(string $id, array $data): void 
    {
        try {
            $sql = "UPDATE `TABLE_NAME` SET 
                /* field1 = :field1, field2 = :field2 */ 
                WHERE id = :id";
    
            $stmt = $this->conn->prepare($sql);

            // Bind values (example placeholders)
            // $stmt->bindValue(':field', $data['field'], PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            $stmt->execute();
    
            if ($stmt->rowCount() === 0) {
                throw new Exception("Record with ID $id not found or no changes made.");
            }

            http_response_code(200);
            echo json_encode(["success" => "Record updated!", "id" => $id]);
    
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    /**
    * Delete a specific record by ID
    * @param string $id
    * @return void
    */
    public function delete(string $id): void 
    {
        $sql = "DELETE FROM `TABLE_NAME` WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new Exception("Record with ID $id not found.");
        }
    }
}
