<?php 
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, AUTHORization, X-Requested-With");
    header("Access-Control-Allow-Methods: POST");
    header("Content-Type: application/json");


    error_reporting(E_ERROR);
    ini_set('display_errors', 1);
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

        // Validate file upload
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {


            $filename = $_POST['file'];
            
            // Move the file to the directory
            $uploadDir = "attachments/images/";
            $filePath = $uploadDir . $filename;

            if (file_exists($filePath)) {
                $updatedData = array(
                    'user_id' => $userid,
                    'campus_name' => $_POST['campus_name'],
                    'name' => $_POST['name'],
                    'username' => $_POST['username'],
                    'user_email' => $_POST['user_email'],
                    'user_password' => $_POST['password'],
                    'imagename' => $filename
                );
            } else {
                echo json_encode([
                    'success' => 0,
                    'message' => 'File ' . $filename .  ' not found!'
                ]);
            }

        } else {
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

            // Move the file to the directory
            $uploadDir = "attachments/images/";
            $filePath = $uploadDir . $filename;

            if (!file_exists($filePath)) {
                if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                    $updatedData = array(
                        'campus_name' => $_POST['campus_name'],
                        'name' => $_POST['name'],
                        'username' => $_POST['username'],
                        'user_email' => $_POST['user_email'],
                        'user_password' => $_POST['password'],
                        'imagename' => $filename
    
                    );
                } else {
                    echo json_encode([
                        'success' => 0,
                        'message' => 'Unable ' . $filename .  ' to upload!'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => 0,
                    'message' => 'File ' . $filename .  ' exist!'
                ]);
            }

        }

        // Retrieve the original row from the database
        $fetchSql = "SELECT * FROM `user_tbl` WHERE user_id = :userid";
        $fetchStmt = $conn->prepare($fetchSql);
        $fetchStmt->bindValue(':userid', $userid);
        $fetchStmt->execute();
        $originalRow = $fetchStmt->fetch(PDO::FETCH_ASSOC);

        $originalRow['user_id'] = (int) $originalRow['user_id'];

        $updateColumns = array();
        $updateValues = array();

        foreach ($updatedData as $column => $updatedValue) {
            $originalValue = $originalRow[$column];
        
            // Compare original and updated values
            if ($originalValue !== $updatedValue) {
                $updateColumns[] = $column;
                $updateValues[] = $updatedValue;
            }
        }

        if (!empty($updateColumns)) {
            $updateSql = "UPDATE `user_tbl` SET ";
        
            foreach ($updateColumns as $index => $column) {
                $updateSql .= $column . " = :" . $column;
                if ($index < count($updateColumns) - 1) {
                    $updateSql .= ", ";
                }
            }
        
            $updateSql .= " WHERE user_id = :userid";

            $updateStmt = $conn->prepare($updateSql);
            
            foreach ($updateValues as $index => $value) {
                $updateStmt->bindValue(':' . $updateColumns[$index], $value);
            }
            
            $updateStmt->bindValue(':userid', $userid);

            if ($updateStmt->execute()) {
                echo json_encode([
                    'success' => 1,
                    'message' => 'Updated Successfully!'
                ]);
            } else {
                echo json_encode([
                    'success' => 0,
                    'message' => 'Unable to update!'
                ]);
            }
        } else {
            echo json_encode([
                'success' => 0,
                'message' => 'No changes were made!'
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