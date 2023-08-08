<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: access");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Allow-Headers: Content-Type, AUTHORization, X-Requested-With");
    header("Content-Type: application/json; charset=UTF-8");

    error_reporting(E_ERROR);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == "OPTIONS") {
        die();
    }

    
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') :
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

    //$data = json_decode(file_get_contents("php://input"), true);

    $entryid = 16;

    $fetchSql = "SELECT * FROM `monthlyreport_tbl` WHERE entry_id = :entryid";
    $fetchStmt = $conn->prepare($fetchSql);
    $fetchStmt->bindValue(':entryid', $entryid);
    $fetchStmt->execute();
    $originalRow = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    // $data = array();
    // foreach ($originalRow as $column => $dataValues) {
    //     $data[$column] = $originalRow[$column];
    // }
    echo json_encode($originalRow['_file']);

    //var_dump($data);

    // var_dump($email);
    // var_dump($password);

    // if (empty($data['email']) || empty($data['password'])) {
    //     http_response_code(400);
    //     echo json_encode([
    //         'success' => false,
    //         'message' => 'Please provide email and password',
    //     ]);
    //     exit;
    // }
    
    // try {
    //     $sql = "SELECT * FROM `user_tbl` WHERE username = '$email' AND user_password = '$password'";
    //     $stmt = $conn->prepare($sql);
    //     $stmt->execute();


    //     if ($stmt->rowCount() > 0) {
    //         $data = null;

    //         $data = $stmt->fetch(PDO::FETCH_ASSOC);
    //         http_response_code(201);
    //         echo json_encode([
    //             'success' => 1,
    //             'data' => $data,
    //             'message' => 'Valid credentials',
    //         ]);
    //         exit;
    //     }
        
    //     echo json_encode([
    //         'success' => 0,
    //         'message' => 'Invalid credentials'
    //     ]);




    //     // $sql = "SELECT * FROM `user_tbl`";
    //     // $stmt = $conn->prepare($sql);
    //     // $stmt->execute();

    //     // $credentials = '';

    //     // if ($stmt->rowCount() > 0) {

    //     //     $row = $stmt->fetch(PDO::FETCH_ASSOC);
    //     //     var_dump($row);
    //     //     $email = isset($data->email) ? $data->email :$row['username'];
    //     //     $password = isset($data->password) ? $data->password :$row['user_password'];

    //     //     $fetch_query = "SELECT * FROM `user_tbl` WHERE username=:email AND user_password=:password";
            
    //     //     $fetch_stmt = $conn->prepare($fetch_query);
    //     //     // $fetch_stmt->bindValue(':user_id', htmlspecialchars(strip_tags($user_id)), PDO::PARAM_INT);
    //     //     $fetch_stmt->bindValue(':email', htmlspecialchars(strip_tags($email)), PDO::PARAM_STR);
    //     //     $fetch_stmt->bindValue(':password', htmlspecialchars(strip_tags($password)), PDO::PARAM_STR);

    //     //     $credentials = $row;
    //     //     if($fetch_stmt->execute()){
    //     //         echo json_encode([
    //     //             'success' => 1,
    //     //             'data' => $credentials
    //     //         ]);
    //     //     }
    //     // } else {
    //     //     http_response_code(401);
    //     //     echo json_encode([
    //     //         'success' => 0,
    //     //         'message' => 'Invalid email or password',
    //     //     ]);
    //     // }
    // } catch (PDOException $e) {
    //     http_response_code(500);
    //     echo json_encode([
    //         'success' => false,
    //         'message' => $e->getMessage(),
    //     ]);
    //     exit;
    // }
?>
