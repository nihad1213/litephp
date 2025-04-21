<?php
require_once __DIR__ . "/../bootstrap.php";
    
class TestController extends BaseController
{
    /*
    * Constructor
    * Injects the related gateway class for DB operations.
    */
    public function __construct(private /*GatewayClass*/ $gateway) {}
    
    /*
    * Main handler for incoming HTTP requests.
    * Determines action based on HTTP method and optional ID.
    */
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
            $resource = $this->gateway->get($id);
    
            if (!$resource) {
                $this->respondNotFound($id);
                return;
            }
    
            switch ($method) {
                case "GET":
                    echo json_encode($resource);
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
                        echo json_encode(["success" => "TestController updated!", "id" => $id]);
                    } catch (Exception $e) {
                        $this->respondInternalError($e->getMessage());
                    }
                    break;
                
                case "DELETE":
                    try {
                        $this->gateway->delete($id);
                        echo json_encode(["success" => "TestController deleted!", "id" => $id]);
                    } catch (Exception $e) {
                        $this->respondInternalError($e->getMessage());
                    }
                    break;
                
                default:
                    $this->respondMethodNotAllowed("GET, PATCH, DELETE");
            }
        }
    }
    
    /*
    * Validates input data for creation or update.
    * Simple checks for required fields.
    */
    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];
                
        if ($is_new && empty($data['entity'])) {
            $errors[] = "Entity is required.";
        }
                
        return $errors;
    }
}