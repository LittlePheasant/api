<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: access, Content-Type, AUTHORization, X-Requested-With");
    header("Access-Control-Allow-Methods: DELETE, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
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
    //echo $data = file_get_contents("php://input");

    $id =  $_GET['id'];
    echo json_encode($id);

    if (!isset($id)) {
        echo json_encode(['success' => 0, 'message' => 'Please provide the post ID.']);
        exit;
    }

    try {

        $fetch_post = "SELECT * FROM `monthlyreport_tbl` WHERE entry_id=:id";
        $fetch_stmt = $conn->prepare($fetch_post);
        $fetch_stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $fetch_stmt->execute();

        if ($fetch_stmt->rowCount() > 0) :

            $delete_post = "DELETE FROM `monthlyreport_tbl` WHERE entry_id=:id";
            $delete_post_stmt = $conn->prepare($delete_post);
            $delete_post_stmt->bindValue(':id', $id,PDO::PARAM_INT);

            if ($delete_post_stmt->execute()) {

                echo json_encode([
                    'success' => 1,
                    'message' => 'Record Deleted successfully'
                ]);
                exit;
            }

            echo json_encode([
                'success' => 0,
                'message' => 'Could not delete. Something went wrong.'
            ]);
            exit;

        else :
            echo json_encode(['success' => 0, 'message' => 'Invalid ID. No posts found by the ID.']);
            exit;
        endif;

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()
        ]);
        exit;
    }
?>