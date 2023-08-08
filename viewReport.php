<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, AUTHORization, X-Requested-With");
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
    $programid = null;

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

    if (isset($_GET['programid'])) {
        $programid = filter_var($_GET['programid'], FILTER_VALIDATE_INT, [
            'options' => [
                'default' => 'all_records',
                'min_range' => 1
            ]
        ]);
        
    }
    
    try {

        $sql = "SELECT user_role FROM `user_tbl` WHERE user_id = :userid";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':userid', $userid, PDO::PARAM_INT);
        $stmt->execute();
        

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $userRole = $row['user_role'];
        
        if ($userRole === 'ADMIN') {

            if (!empty($entryid)) {
                //ADMIN view single data by entry id
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
                        'file' => $fileName
                    );
    
                } else {
                    // File not found
                    header('HTTP/1.0 404 Not Found');
                    echo json_encode(['error' => 'File not found']);
                }

            } elseif (!empty($programid)) {

                // User has 'ADMIN' role, select all data from monthlyreport_tbl
                $sql = "SELECT m.*,u.name, u.campus_name, c.description
                 FROM `monthlyreport_tbl` m
                 INNER JOIN `user_tbl` u ON m.user_id = u.user_id
                 INNER JOIN `college_programs_tbl` c ON c.program_id = m.program_id
                 WHERE m.program_id = :programid AND YEAR(date_entry) = YEAR(CURDATE())";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':programid', $programid, PDO::PARAM_INT);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $sql = "SELECT u.campus_name, c.description
                    FROM `user_tbl` u
                    INNER JOIN `college_programs_tbl` c ON c.user_id = u.user_id
                    WHERE c.program_id = :programid";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue(':programid', $programid, PDO::PARAM_INT);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                

            } else {

                // User has 'ADMIN' role, select all data from monthlyreport_tbl
                $sql = "SELECT m.*, u.name
                 FROM `monthlyreport_tbl` m
                 INNER JOIN `user_tbl` u ON u.user_id = m.user_id
                 WHERE YEAR(date_entry) = YEAR(CURDATE())";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            }
            
        } else if ($userRole === 'AUTHOR'){

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
                    echo json_encode(['message' => 'File not found']);
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

            } elseif (!empty($programid)) {

                $sql = "SELECT m.*, u.name, u.campus_name, c.description
                 FROM `monthlyreport_tbl` m
                 INNER JOIN `user_tbl` u ON m.user_id = u.user_id
                 INNER JOIN `college_programs_tbl` c ON c.program_id = m.program_id
                 WHERE m.program_id = :programid AND YEAR(date_entry) = YEAR(CURDATE())";
                $stmt = $conn->prepare($sql);
                //$stmt->bindValue(':userid', $userid, PDO::PARAM_INT);
                $stmt->bindValue(':programid', $programid, PDO::PARAM_INT);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $sql = "SELECT u.campus_name, c.description
                    FROM `user_tbl` u
                    INNER JOIN `college_programs_tbl` c ON c.user_id = u.user_id
                    WHERE c.program_id = :programid";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue(':programid', $programid, PDO::PARAM_INT);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                
            } else {

                // User doesn't have 'ADMIN' role, select specific data based on userid
                $sql = "SELECT m.*, u.name, u.user_role FROM `monthlyreport_tbl` m
                INNER JOIN `user_tbl` u ON m.user_id = u.user_id 
                WHERE m.user_id = :userid 
                AND YEAR(date_entry) = YEAR(CURDATE())";
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