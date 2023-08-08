<?php 

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, AUTHORization, X-Requested-With");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Content-Type: application/json");

    error_reporting(E_ERROR);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == "OPTIONS") {
        header("Content-Type: application/json");
        die();
    }       

    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') :
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Request!.Only POST method is allowed',
        ]);
    endif;
    
    require 'db_connect.php';
    $database = new Operations();
    $conn = $database->dbConnection();

    $userid = null;

    if (isset($_POST['user_id'])) {
        $userid = filter_var($_POST['user_id'], FILTER_VALIDATE_INT, [
            'options' => [
                'default' => null,
                'min_range' => 1
            ]
        ]);
    }

    try {

        $program = $_POST['prog_name'];
        $description = $_POST['prog_desc'];

        if (!empty($userid)) {

            $insertSql = "INSERT INTO `college_programs_tbl` (
                `user_id`,
                `program`,
                `description`
            ) VALUES (
                :userid,
                :program,
                :description
            )";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bindValue(':userid', $userid, PDO::PARAM_INT);
            $insertStmt->bindValue(':program', $program, PDO::PARAM_STR);
            $insertStmt->bindValue(':description', $description, PDO::PARAM_STR);
            
            if ($insertStmt->execute()) {
                echo json_encode([
                    'success' => 1,
                    'message' => 'Added Successfully!'
                ]);

            } else {

                echo json_encode([
                    'success' => 0,
                    'message' => 'Unsuccessful!'
                ]);
            }

        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()
        ]);
        exit;
    }


?>