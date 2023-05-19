<?php



    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: access, Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    //header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json; charset=UTF-8");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') :
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Request!.Only POST method is allowed',
        ]);
    endif;

    error_reporting(E_ERROR);

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header('HTTP/1.1 200 OK');
        //exit();
    }
    
    require 'db_connect.php';
    $database = new Operations();
    $conn = $database->dbConnection();
    
    $data = json_decode(file_get_contents("php://input"));
    
    $userid = null;

    if (isset($_GET['id'])) {
        $userid = filter_var($_GET['id'], FILTER_VALIDATE_INT, [
            'options' => [
                'default' => 'all_records',
                'min_range' => 1
            ]
        ]);
        // echo json_encode($_GET['id']);
    }
    
    try{
        
        $sql = "SELECT user_role FROM user_tbl WHERE user_id = :userid";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':userid', $userid, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $userRole = $row['user_role'];
        
            if ($userRole === 'Admin') {
                // User has 'admin' role, select all data from monthlyreport_tbl
                $sql = "SELECT * FROM `monthlyreport_tbl`";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // User doesn't have 'admin' role, select specific data based on userid
                $sql = "SELECT * FROM `monthlyreport_tbl` WHERE user_id = :userid";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':userid', $userid, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        
            echo json_encode($result);
        } else {
            // User not found
            echo json_encode([
                'success' => 0,
                'message' => 'User not found!',
            ]);
        }
    } catch (PDOException $e) {
        //http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()

        ]);
    }
    
?>