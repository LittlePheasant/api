<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");

    error_reporting(E_ERROR);
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') :
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Reqeust Detected! Only get method is allowed',
        ]);
        exit;
    endif;

    require 'db_connect.php';
    $database = new Operations();
    $conn = $database->dbConnection();

    $userid = null;
    $entryid = null;

    if (isset($_GET['userid'])) {
        $userid = filter_var($_GET['userid'], FILTER_VALIDATE_INT, [
            'options' => [
                'default' => 'all_records',
                'min_range' => 1
            ]
        ]);
        
    }
    
    if (isset($_GET['entry_id'])) {
        $entryid = $_GET['entry_id'];
    }
    
    try {
        if (!empty($entryid)) {
            $sql = "SELECT * FROM `monthlyreport_tbl` WHERE entry_id = :entryid";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':entryid', $entryid, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sql = "SELECT user_role FROM `user_tbl` WHERE user_id = :userid";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':userid', $userid, PDO::PARAM_INT);
            $stmt->execute();
            //echo json_encode($userid);

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

                
                //echo json_encode($userRole);
            } else {

                // echo json_encode($result);
                // User not found
                echo json_encode([
                    'success' => 0,
                    'message' => 'User not found!',
                ]);
            }
        }

        echo json_encode($result);
        

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()
        ]);
        exit;
    }
?>