<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: access, Content-Type, AUTHORization, X-Requested-With");
    header("Access-Control-Allow-Methods: DELETE, OPTIONS");
    header("Content-Type: application/json");


    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == "OPTIONS") {
        header("Content-Type: application/json");
        die();
    }


    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') :
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Reqeust detected. HTTP method should be DELETE',
        ]);
        exit;
    endif;

    require 'db_connect.php';
    $database = new Operations();
    $conn = $database->dbConnection();

    $data = json_decode(file_get_contents("php://input"));

    $programid =  null;

    if (isset($_GET['id'])) {
        $programid = filter_var($_GET['id'], FILTER_VALIDATE_INT, [
            'options' => [
                'default' => null,
                'min_range' => 1
            ]
        ]);
    }

    try {

        $deleteSql = "DELETE FROM `college_programs_tbl` WHERE program_id =:programid";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bindValue(':programid', $programid, PDO::PARAM_INT);

        if ($deleteStmt->execute()) {

            echo json_encode([
                'success' => 1,
                'message' => 'Program selected is deleted successfully!'
            ]);

        } else {
            echo json_encode(['success' => 0, 'message' => 'Invalid ID. No program found.']);
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