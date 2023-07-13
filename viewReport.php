<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

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

    if (isset($_GET['id'])) {
        $userid = filter_var($_GET['id'], FILTER_VALIDATE_INT, [
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

        $sql = "SELECT user_role FROM `user_tbl` WHERE user_id = :userid";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':userid', $userid, PDO::PARAM_INT);
        $stmt->execute();
        

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $userRole = $row['user_role'];
        
        if ($userRole === 'Admin') {

            if (!empty($entryid)) {
                //Admin view single data by entry id
                $sql = "SELECT * FROM `monthlyreport_tbl` WHERE entry_id = :entryid";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':entryid', $entryid, PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                //Local directory
                $uploadDir = "attachments/uploads/";
                $filePath = $uploadDir . $row['_file'];

                if (file_exists($filePath)) {
                    $fileName = basename($filePath);
                    $fileSize = filesize($filePath);
                    $fileType = mime_content_type($filePath);
                    
                    // Set the file details in the header
                    header('Content-Type: ' . $fileType); 
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . $fileName . '"');
                    header('Content-Length: ' . $fileSize);

                    //readfile($filePath);
                    $responseData = ['name' => $fileName, 'size' => $fileSize, 'type' => $fileType];
                } else {
                    // File not found
                    header('HTTP/1.0 404 Not Found');
                    echo json_encode(['error' => 'File not found']);
                }

                $result = array(
                    'entry_id' => $row['entry_id'],
                    'user_id' => $row['user_id'],
                    'program_id' => $row['program_id'],
                    'date_entry' => $row['date_entry'],
                    'title' => $row['title'],
                    'type_beneficiary' => $row['type_beneficiary'],
                    'count_male' => $row['count_male'],
                    'count_female' => $row['count_female'],
                    'poor_rate' => $row['poor_rate'],
                    'fair_rate' => $row['fair_rate'],
                    'satisfactory_rate' => $row['satisfactory_rate'],
                    'verysatisfactory_rate' => $row['verysatisfactory_rate'],
                    'excellent_rate' => $row['excellent_rate'],
                    'duration' => $row['duration'],
                    'unitOpt' => $row['unitOpt'],
                    'serviceOpt' => $row['serviceOpt'],
                    'partners' => $row['partners'],
                    'fac_staff' => $row['fac_staff'],
                    'role' => $row['role'],
                    'cost_fund' => $row['cost_fund'],
                    'file' => $responseData
                );

            } else {
                // User has 'admin' role, select all data from monthlyreport_tbl
                $sql = "SELECT * FROM `monthlyreport_tbl`";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
        } else if ($userRole === 'Author'){

            if (!empty($entryid)) {

                $sql = "SELECT * FROM `monthlyreport_tbl` WHERE entry_id = :entryid";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':entryid', $entryid, PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                //$result = array();

                //Local directory
                $uploadDir = "attachments/uploads/";
                $filePath = $uploadDir . $row['_file'];

                if (file_exists($filePath)) {
                    $fileName = basename($filePath);
                    $fileSize = filesize($filePath);
                    $fileType = mime_content_type($filePath);
                    
                    // Set the file details in the header
                    header('Content-Type: ' . $fileType); 
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . $fileName . '"');
                    header('Content-Length: ' . $fileSize);

                    //readfile($filePath);
                    $responseData = ['name' => $fileName, 'size' => $fileSize, 'type' => $fileType];
                } else {
                    // File not found
                    header('HTTP/1.0 404 Not Found');
                    echo json_encode(['error' => 'File not found']);
                }

                $result = array(
                    'entry_id' => $row['entry_id'],
                    'user_id' => $row['user_id'],
                    'program_id' => $row['program_id'],
                    'date_entry' => $row['date_entry'],
                    'title' => $row['title'],
                    'type_beneficiary' => $row['type_beneficiary'],
                    'count_male' => $row['count_male'],
                    'count_female' => $row['count_female'],
                    'poor_rate' => $row['poor_rate'],
                    'fair_rate' => $row['fair_rate'],
                    'satisfactory_rate' => $row['satisfactory_rate'],
                    'verysatisfactory_rate' => $row['verysatisfactory_rate'],
                    'excellent_rate' => $row['excellent_rate'],
                    'duration' => $row['duration'],
                    'unitOpt' => $row['unitOpt'],
                    'serviceOpt' => $row['serviceOpt'],
                    'partners' => $row['partners'],
                    'fac_staff' => $row['fac_staff'],
                    'role' => $row['role'],
                    'cost_fund' => $row['cost_fund'],
                    'file' => $responseData
                );

            } else {

                // User doesn't have 'admin' role, select specific data based on userid
                $sql = "SELECT m.*, u.user_role FROM `monthlyreport_tbl` m
                INNER JOIN `user_tbl` u ON m.user_id = u.user_id 
                WHERE m.user_id = :userid";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':userid', $userid, PDO::PARAM_INT);
                $stmt->execute();

                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                //$userRole = $row['user_role'];
            }

        } else {
            $result = 'User not found';
        }

        
        echo json_encode([
            'userRole'=>$userRole,
            'data' => $result
        ]);


        

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()
        ]);
        exit;
    }
?>