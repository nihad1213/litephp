<?php
require_once __DIR__ . "/../api/bootstrap.php";

class TaskController extends BaseController
{
    public function __construct(private TaskGateway $gateway) {}

    public function processRequest(string $method, ?string $id): void
    {
        if ($id === null) {
            if ($method == "GET") {
                echo json_encode($this->gateway->getAll());
            } else if ($method == "POST") {
                $data = $this->getJsonInput();
                $errors = $this->getValidationErrors($data);

                if (!empty($errors)) {
                    $this->respondUnprocessableEntity($errors);
                    return;
                }

                $id = $this->gateway->create($data);
                $this->respondCreated($id);
            } else {
                $this->respondMethodNotAllowed("POST, GET");
            }
        } else {
            $task = $this->gateway->get($id);

            if (!$task) {
                $this->respondNotFound($id);
                return;
            }

            switch ($method) {
                case "GET":
                    echo json_encode($task);
                    break;

                case "PATCH":
                    $data = $this->getJsonInput();
                    $errors = $this->getValidationErrors($data, false);

                    if (!empty($errors)) {
                        $this->respondUnprocessableEntity($errors);
                        return;
                    }

                    try {
                        $this->gateway->update($id, $data);
                        echo json_encode(["success" => "Task updated!", "id" => $id]);
                    } catch (Exception $e) {
                        $this->respondInternalError($e->getMessage());
                    }
                    break;

                case "DELETE":
                    try {
                        $this->gateway->delete($id);
                        echo json_encode(["success" => "Task deleted!", "id" => $id]);
                    } catch (Exception $e) {
                        $this->respondInternalError($e->getMessage());
                    }
                    break;

                default:
                    $this->respondMethodNotAllowed("GET, PATCH, DELETE");
            }
        }
    }

    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];

        if ($is_new && empty($data['name'])) {
            $errors[] = "Name is required.";
        }

        if ($is_new && !isset($data['priority'])) {
            $errors[] = "Priority is required.";
        }

        if ($is_new && !isset($data['is_completed'])) {
            $errors[] = "Completion status is required.";
        }

        return $errors;
    }
}
