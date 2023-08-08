<?php 

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, AUTHORization, X-Requested-With");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Content-Type: application/json");

    error_reporting(E_ERROR);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == "OPTIONS") {
        header("Content-Type: application/json");
        die();
    }       

    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') :
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Request!.Only POST method is allowed',
        ]);
    endif;
    
    require 'db_connect.php';
    $database = new Operations();
    $conn = $database->dbConnection();

    try {

        // Access the 'file' field
        $filesize = $_FILES['file']['size'];
        $filename = $_FILES['file']['name'];

        $maxFileSize = 2 * 1024 * 1024;

        if ($filesize > $maxFileSize) {
            http_response_code(400);
            echo json_encode([
                'success' => 0,
                'message' => 'File size exceeds the maximum allowed size of 2MB'
            ]);
            exit();
        }

        // Validate file upload
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode([
                'success' => 0,
                'message' => 'Invalid file upload',
            ]);
            exit();
        }

        // Validate and sanitize other input fields
        $name = strtoupper($_POST['name']);
        $username = $_POST['username'];
        $user_email = $_POST['user_email'];
        $user_password = $_POST['password'];
        $user_role = strtoupper($_POST['user_role']);

        // Move the file to the directory
        $uploadDir = "attachments/images/";
        $filePath = $uploadDir . $filename;

        if (file_exists($filePath)) {
            echo json_encode([
                'success' => 0,
                'message' => 'File already exists => ' . $filePath;
            ]);
            exit();
        } else {
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            } else {
                if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                    $insertSql = "INSERT INTO `user_tbl` (
                        `name`,
                        `username`,
                        `user_email`,
                        `user_password`,
                        `user_role`,
                        `imagename`
                    ) VALUES (
                        :name,
                        :username,
                        :user_email,
                        :user_password,
                        :user_role,
                        :filename
                    )";
                    $insertStmt = $conn->prepare($insertSql);
                    $insertStmt->bindValue(':name', $name, PDO::PARAM_STR);
                    $insertStmt->bindValue(':username', $username, PDO::PARAM_STR);
                    $insertStmt->bindValue(':user_email', $user_email, PDO::PARAM_STR);
                    $insertStmt->bindValue(':user_password', $user_password, PDO::PARAM_STR);
                    $insertStmt->bindValue(':user_role', $user_role, PDO::PARAM_STR);
                    $insertStmt->bindValue(':filename', $filename, PDO::PARAM_STR);
                    
                    if ($insertStmt->execute()) {
                        echo json_encode([
                            'success' => 1,
                            'message' => 'Added Successfully!'
                        ]);
        
                    } else {
        
                        echo json_encode([
                            'success' => 0,
                            'message' => 'Unable to add!'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => 0,
                        'message' => 'No image uploaded';
                    ]);
                }
            }
        }

        if (!empty($_POST)) {

        
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