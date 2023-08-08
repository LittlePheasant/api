<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, AUTHORization, X-Requested-With");
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

    if (isset($_GET['id'])) {
        $userid = filter_var($_GET['id'], FILTER_VALIDATE_INT, [
            'options' => [
                'default' => null,
                'min_range' => 1
            ]
        ]);
    }

    try {

        if (!empty($userid)){
            $userRoleQuery = "SELECT user_role FROM `user_tbl` WHERE user_id = :userid";
            $userRoleStmt = $conn->prepare($userRoleQuery);
            $userRoleStmt->bindValue(':userid', $userid, PDO::PARAM_INT);
            $userRoleStmt->execute();

            $userRoleResult = $userRoleStmt->fetch(PDO::FETCH_ASSOC);
            $userRole = $userRoleResult['user_role'];

            $fetchData = null;
            $fetchName = null;


            if ($userRole === 'ADMIN') {
                $sqlName = "SELECT user_id, name FROM `user_tbl` WHERE user_role NOT LIKE 'ADMIN'";
                $stmtName = $conn->prepare($sqlName);
                $stmtName ->execute();
                $fetchName = $stmtName->fetchAll(PDO::FETCH_ASSOC);

                $sql = "SELECT * FROM `user_tbl` WHERE user_id = :userid";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':userid', $userid, PDO::PARAM_INT);
                $stmt ->execute();
                if ($stmt->rowCount() > 0) {
                    $fetchData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $uploadDir = "attachments/images/";
    
                    // Loop through the fetched data and update the imagename
                    foreach ($fetchData as &$row) {
                        if (!empty($row['imagename'])) {
                            $row['imagename'] = $uploadDir . $row['imagename'];
                        }
                    }
                } else {
                    $fetchData = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            } else {

                $sql = "SELECT * FROM `user_tbl` WHERE user_id = :userid";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':userid', $userid, PDO::PARAM_INT);
                $stmt ->execute();
                if ($stmt->rowCount() > 0) {
                    $fetchData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $uploadDir = "attachments/images/";
    
                    // Loop through the fetched data and update the imagename
                    foreach ($fetchData as &$row) {
                        if (!empty($row['imagename'])) {
                            $row['imagename'] = $uploadDir . $row['imagename'];
                        }
                    }
                } else {
                    $fetchData = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }

        } else {
            $sql = "SELECT * FROM `user_tbl` ORDER BY name ASC";
            $stmt = $conn->prepare($sql);
            $stmt ->execute();

            if ($stmt->rowCount() > 0) {
                $fetchData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $uploadDir = "attachments/images/";

                // Loop through the fetched data and update the imagename
                foreach ($fetchData as &$row) {
                    if (!empty($row['imagename'])) {
                        $row['imagename'] = $uploadDir . $row['imagename'];
                    }
                }
            } else {
                $fetchData = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }

        
        // Send the response as JSON
        echo json_encode([
            'data' => $fetchData,
            'name' => $fetchName,
            'userRole' => $userRole
        ], JSON_UNESCAPED_SLASHES);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()
        ]);
        exit;
    }
?>