<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Content-Type: application/json");
    
    
    error_reporting(E_ALL);
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

        $userid = null;

        //Validate user_id
        if (!isset($_POST['user_id']) && empty($_POST['user_id'])) {
            echo json_encode([
                'success' => 0,
                'message' => 'No id passed!',
            ]);
        } else {
            $userid = filter_var($_POST['user_id'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => 'all_records',
                    'min_range' => 1
                ]
            ]);
        }

        // Validate file upload
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode([
                'success' => 0,
                'message' => 'Invalid file upload',
            ]);

        } else {

            // Access the 'file' field
            $filesize = $_FILES['file']['size'];
            $filename = $_FILES['file']['name'];
            //echo json_encode($filename);

            $maxFileSize = 2 * 1024 * 1024;

            if ($filesize > $maxFileSize) {
                echo 'File size exceeds the maximum allowed size.';
            } else {

                // Move the file to the directory
                $uploadDir = "attachments/downloads/";
                $filePath = $uploadDir . $filename;

                if (file_exists($filePath)) {
                    echo 'File already exists => ' . $filePath;

                } else {

                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    } else {

                        // Get the current date as a string
                        $currentDate = date('Y-m-d');
                        
                        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {

                            $sql = "INSERT INTO `downloadable_files_tbl` (
                                        `admin_id`,
                                        `_filename`,
                                        `uploaded_at`
                                    ) VALUES (
                                        :userid,
                                        :filename,
                                        :currentDate
                                    )";

                            // Bind the date parameter in your prepared statement
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
                            $stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
                            $stmt->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);

                            if ($stmt->execute()) {
                                echo json_encode([
                                    'success' => 1,
                                    'message' => 'Uploaded Successfully!'
                                ]);

                            } else {
                                echo json_encode([
                                    'success' => 0,
                                    'message' => 'Could not upload!'
                                ]);
                            }

                        } else {

                            echo json_encode([
                                'success' => 0,
                                'message' => 'No file uploaded'
                            ]);
                        }
                    }
                }
            }
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()

        ]);
    }

?>