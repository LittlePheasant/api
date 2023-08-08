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
    $programid = null;

    if (isset($_GET['id']) || isset($_GET['programid'])) {
        $userid = filter_var($_GET['id'], FILTER_VALIDATE_INT, [
            'options' => [
                'default' => null,
                'min_range' => 1
            ]
        ]);

        $programid = filter_var($_GET['programid'], FILTER_VALIDATE_INT, [
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

            if ($userRole === 'ADMIN') {

                $sql = "SELECT c.user_id, c.program_id, u.name, c.program, c.description 
                        FROM `college_programs_tbl` c
                        LEFT JOIN `user_tbl` u ON u.user_id = c.user_id
                        ORDER BY u.name";
                    $stmt = $conn->prepare($sql);
                    $stmt ->execute();

                if ($stmt->rowCount() > 0) {
                    $fetchData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $fetchData = $stmt->fetch(PDO::FETCH_ASSOC);
                }

                if (!empty($programid)){
                    $sql = "SELECT c.user_id, c.program_id, u.name, c.program, c.description 
                        FROM `college_programs_tbl` c
                        LEFT JOIN `user_tbl` u ON u.user_id = c.user_id
                        WHERE c.program_id = :programid";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue(':programid', $programid, PDO::PARAM_INT);
                    $stmt ->execute();

                    $fetchData = $stmt->fetch(PDO::FETCH_ASSOC);

                }
                
            } else {
                // Query the database to fetch the programs based on the user ID
                $sql = "SELECT c.user_id, c.program_id, u.name, c.program, c.description 
                        FROM `college_programs_tbl` c
                        INNER JOIN `user_tbl` u ON u.user_id = c.user_id
                        WHERE u.user_id = :userid";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':userid', $userid, PDO::PARAM_INT);
                $stmt->execute();

                // Fetch the program options
                $fetchData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            }

            // Send the response as JSON
            echo json_encode([
                'data' => $fetchData,
                'userRole' => $userRole
            ]);

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