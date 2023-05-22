<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");

    error_reporting(E_ERROR);
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Request Detected! Only GET method is allowed',
        ]);
        exit;
    }

    require 'db_connect.php';
    $database = new Operations();
    $conn = $database->dbConnection();

    $particular_id = null;
    $userid = null;

    if (isset($_GET['particular_id'])) {
        $particular_id = filter_var($_GET['particular_id'], FILTER_VALIDATE_INT, [
            'options' => [
                'default' => 'all_records',
                'min_range' => 1
            ]
        ]);
    }

    if (isset($_GET['id'])) {
        $userid = $_GET['id'];
    }
    //echo json_encode($userid);
    //echo json_encode($particular_id);

    try {
        $userRoleQuery = "SELECT user_role FROM `user_tbl` WHERE user_id = :userid";
        $userRoleStmt = $conn->prepare($userRoleQuery);
        $userRoleStmt->bindValue(':userid', $userid, PDO::PARAM_INT);
        $userRoleStmt->execute();
        $userRoleResult = $userRoleStmt->fetch(PDO::FETCH_ASSOC);
        $userRole = $userRoleResult['user_role'];

        if ($userRole === 'Admin') {
            $sql = "SELECT u.name, a.count
                    FROM `actualreportbytotal_tbl` a
                    INNER JOIN `user_tbl` u ON u.user_id = a.user_id
                    WHERE a.particular_id = :particular_id";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($data);
        } else {
            $sql = "SELECT u.name, a.count
                    FROM `actualreportbytotal_tbl` a
                    INNER JOIN `user_tbl` u ON u.user_id = a.user_id
                    WHERE a.particular_id = :particular_id AND u.user_id = :userid";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
            $stmt->bindValue(':userid', $userid, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($data);
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