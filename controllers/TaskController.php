<?php

class TaskController {
    public function processRequest($method, $id) {
        if ($id === null) {
            if ($method == "GET") {
                echo "index";
            } else if ($method == "POST") {
                echo "create";
            } else {
                $this->respondMethodNotAllowed("POST, GET");
            } 

        } else {
            switch ($method) {
                case "GET":
                    echo "show" . $id;
                    break;
                case "PATCH":
                    echo "update" . $id;
                    break;
                case "DELETE":
                    echo "delete" . $id;
                    break;
                default:
                    $this->respondMethodNotAllowed("GET, PATCH, DELETE");
            }
        }
    }


    private function respondMethodNotAllowed(string $allowedMethods): void {
        http_response_code(405);
        header("Allow: $allowedMethods");
    }
}