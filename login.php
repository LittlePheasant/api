<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");

    error_reporting(E_ERROR);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == "OPTIONS") {
        //header("Content-Type: application/json");
        die();
    }

    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') :
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Request!.Only POST method is allowed',
        ]);
        exit;
    endif;
    require 'db_connect.php';
    $database = new Operations();
    $conn = $database->dbConnection();

    $data = json_decode(file_get_contents("php://input"));

    //var_dump($data);
    
    $email = $data->email;
    $password = $data->password;

    try {
        $sql = "SELECT user_id, user_role FROM `user_tbl` WHERE username = '$email' AND user_password = '$password'";
        $stmt = $conn->prepare($sql);
        // $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        // $stmt->bindValue(':password', $password, PDO::PARAM_STR);
        $stmt->execute();


        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $fetchedData = $row;

            //http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => 1,
                // $fetchedData,
                'data' => $fetchedData,
                //'message' => 'Valid credentials',
            ]);
        } else {
            //header('Content-Type: application/json');
            echo json_encode([
                'success' => 0,
                'message' => 'Invalid credentials'
            ]);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
        ]);
        exit;
    }
?>
