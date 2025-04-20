<?php

class TaskController {
    public function processRequest($method, $id) {
        if ($id === null) {
            if ($method == "GET") {
                echo "index";
            } else if ($method == "POST") {
                echo "create";
            } 

        } else {
            switch ($method) {
                case "GET":
                    echo "show" . $id;
                    break;
                case "PATHC":
                    echo "update" . $id;
                    break;
                }
        }
    }
}