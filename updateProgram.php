<?php 
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, AUTHORization, X-Requested-With");
    header("Access-Control-Allow-Methods: PUT, OPTIONS");
    header("Content-Type: application/json");


    error_reporting(E_ERROR);
    ini_set('display_errors', 1);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == "OPTIONS") {
        header("Content-Type: application/json");
        die();
    }


    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') :
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Request!.Only PUT method is allowed',
        ]);
    endif;

    require 'db_connect.php';
    $database = new Operations();
    $conn = $database->dbConnection();

    $data = json_decode(file_get_contents('php://input'));

    $userid = null;

    if (isset($_GET['id'])) {
        $userid = filter_var($_GET['id'], FILTER_VALIDATE_INT, [
            'options' => [
                'default' => 'all_records',
                'min_range' => 1
            ]
        ]);
        
    }

    try {

        if (!empty($userid)) {

            $progname = $data->prog_name;
            $progdesc = $data->prog_desc;

            $sql = "UPDATE `college_programs_tbl` SET program = :progname, description = :progdesc WHERE user_id = :userid";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':progname', $progname, PDO::PARAM_STR);
            $stmt->bindValue(':progdesc', $progdesc, PDO::PARAM_STR);
            $stmt->bindValue(':userid', $userid, PDO::PARAM_INT);
            
            if ($stmt ->execute()) {

                http_response_code(200);
                echo json_encode([
                    'success' => 1,
                    'message' => 'Updated Successfully!'
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