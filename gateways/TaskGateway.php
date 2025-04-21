<?php

class TaskGateway 
{
    private PDO $conn;
    
    public function __construct(Database $database) 
    {
        $this->conn = $database->getConnect();
    }

    /**
     * Get all tasks from the database
     * @return array
     */
    public function getAll(): array 
    {
        $sql = "SELECT * FROM `task`";

        $stmt = $this->conn->query($sql);
        
        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Get specific task by its ID
     * @param string $id
     * @return array|false
     */
    public function get(string $id): array|false 
    {
        $sql = "SELECT * FROM `task` WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Add a new task to the database
     * @param array $data
     * @return string
     */
    public function create(array $data): string 
    {
        $sql = "INSERT INTO `task` 
                (`name`, `priority`, `is_completed`) 
                VALUES 
                (:name, :priority, :is_completed)";
    
        $stmt = $this->conn->prepare($sql);
    
        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':priority', $data['priority'], PDO::PARAM_INT);
        $stmt->bindValue(':is_completed', $data['is_completed'], PDO::PARAM_BOOL);
    
        $stmt->execute();
    
        return $this->conn->lastInsertId();
    }

    /**
     * Update a specific task in the database
     * @param string $id
     * @param array $data
     * @return void
     */
    public function update(string $id, array $data): void 
    {
        try {
            $sql = "UPDATE `task` SET 
                    `name` = :name, 
                    `priority` = :priority, 
                    `is_completed` = :is_completed 
                    WHERE id = :id";
    
            $stmt = $this->conn->prepare($sql);
    
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':priority', $data['priority'], PDO::PARAM_INT);
            $stmt->bindValue(':is_completed', $data['is_completed'], PDO::PARAM_BOOL);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    
            $stmt->execute();
    
            if ($stmt->rowCount() === 0) {
                throw new Exception("Task with ID $id not found or no changes made.");
            }
    
            http_response_code(200);
            echo json_encode(["success" => "Task updated!", "id" => $id]);
    
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    /**
     * Delete a specific task from the database
     * @param string $id
     * @return void
     */
    public function delete(string $id): void 
    {
        $sql = "DELETE FROM `task` WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Task with ID $id not found.");
        }
    }
}
