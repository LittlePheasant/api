<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: PUT, OPTIONS");
    //header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");


    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == "OPTIONS") {
        header("Content-Type: application/json");
        die();
    }


    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') :
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Request!.Only PUT method is allowed',
        ]);
    endif;

    require 'db_connect.php';
    $database = new Operations();
    $conn = $database->dbConnection();

    // Get the updated report data from the request body
    $data = file_get_contents('php://input');

    parse_str($data, $formData);

    // Extract the form fields
    preg_match_all('/name="([^"]*)"\s*[\r\n]+([^\-]+)/', $data, $matches, PREG_SET_ORDER);
    $formFields = [];
    foreach ($matches as $match) {
        $fieldName = $match[1];
        $fieldValue = trim($match[2]);
        $formFields[$fieldName] = $fieldValue;
    }

    // Extract the file information
    preg_match('/name="file"; filename="([^"]*)"\s*[\r\n]+Content-Type: ([^\r\n]+)/', $data, $fileMatch);
    $filename = '';
    $filetype = '';

    if (count($fileMatch) >= 3) {
        $filename = $fileMatch[1];
        $filetype = $fileMatch[2];
    }

    $fileInfo = [
        'filename' => $filename,
        'filetype' => $filetype
    ];

    $formFields['file'] = $fileInfo;

    $post_max_size=ini_get('post_max_size');
    $upload_max_filesize=ini_get('upload_max_filesize');

    

    try {

        $entryid = null;

        if (isset($_GET['id'])) {
            $entryid = filter_var($_GET['id'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => 'all_records',
                    'min_range' => 1
                ]
            ]);
            
        }

        // Display the extracted data
        echo json_encode($formFields);
        echo json_encode($fileInfo);

        // Validate file upload
        if (!isset($formFields['file']) || $formFields['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode([
                'success' => 0,
                'message' => 'Invalid file upload',
            ]);
            exit();
        } else {

            // Access the 'file' field
            $filesize = $_FILES['file']['size'];
            $filename = $_FILES['file']['name'];

            $maxFileSize = 2 * 1024 * 1024;

            if ($filesize > $maxFileSize) {
                echo 'File size exceeds the maximum allowed size.';
            } else {

                $updatedData = array(
                    'user_id' => $_POST['user_id'],
                    'program_id' => $_POST['program_id'],
                    'date_entry' => $_POST['date_entry'],
                    'title' => $_POST['title'],
                    'type_beneficiary' => $_POST['type_beneficiary'],
                    'count_male' => $_POST['count_male'],
                    'count_female' => $_POST['count_female'],
                    'poor_rate' => $_POST['poor_rate'],
                    'fair_rate' => $_POST['fair_rate'],
                    'satisfactory_rate' => $_POST['satisfactory_rate'],
                    'verysatisfactory_rate' => $_POST['verysatisfactory_rate'],
                    'excellent_rate' => $_POST['excellent_rate'],
                    'duration' => $_POST['duration'],
                    'unitOpt' => $_POST['unitOpt'],
                    'serviceOpt' => $_POST['serviceOpt'],
                    'partners' => $_POST['partners'],
                    'fac_staff' => $_POST['fac_staff'],
                    'role' => $_POST['role'],
                    'cost_fund' => $_POST['cost_fund'],
                    '_file' => $_FILES['file']['name']
                );


                // Move the file to the directory
                $uploadDir = "attachments/uploads/";
                $filePath = $uploadDir . $filename;

                if (file_exists($filePath)) {

                    // Retrieve the original row from the database
                    $statement = $pdo->prepare("SELECT * FROM `monthlyreport_tbl` WHERE entry_id = :entryid");
                    $statement->bindParam(':entryid', $entryid);
                    $statement->execute();
                    $originalRow = $statement->fetch(PDO::FETCH_ASSOC);

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
                        $updateSql = "UPDATE `monthlyreport_tbl` SET ";
                    
                        foreach ($updateColumns as $index => $column) {
                            $updateSql .= $column . " = ?";
                            if ($index < count($updateColumns) - 1) {
                                $updateSql .= ", ";
                            }
                        }
                    
                        $updateSql .= " WHERE entry_id = :entryid";
                    
                        $updateStatement = $pdo->prepare($updateSql);
                        $updateStatement->bindParam(':entryid', $entryid);
                    
                        foreach ($updateValues as $index => $value) {
                            $updateStatement->bindValue(($index + 1), $value);
                        }
                    
                        $updateStatement->execute();
                    
                        echo "The row has been updated.";
                    } else {
                        echo "No changes were made to the row.";
                    }

                    echo json_encode ([
                        'message' => 'File already exists.'
                    ]);

                } else {
                    echo '0';
                }
                
            }
        }


        

        // if (file_exists($filePath)) {
        //     echo 'File already exists => ' . $filePath;
        //     //exit();
        // } else {
        //     if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        //         echo 'File successfully updated => "' . $filePath;

        //         // Prepare the update query
        //         $query = "UPDATE `monthlyreport_tbl` 
        //         SET 
        //                 user_id = :user_id,
        //                 program_id = :program_id,
        //                 date_entry = :date_entry, 
        //                 title = :title,
        //                 type_beneficiary = :type_beneficiary,
        //                 count_male = :count_male,
        //                 count_female = :count_female,
        //                 poor_rate = :poor_rate,
        //                 fair_rate = :fair_rate,
        //                 satisfactory_rate = :satisfactory_rate,
        //                 verysatisfactory_rate = :verysatisfactory_rate,
        //                 excellent_rate = :excellent_rate,
        //                 duration = :duration,
        //                 unitOpt = :unitOpt,
        //                 serviceOpt = :serviceOpt,
        //                 partners = :partners,
        //                 fac_staff = :fac_staff,
        //                 role = :role,
        //                 cost_fund = :cost_fund,
        //                 _file = :filename
        //         WHERE entry_id = :entryid";

        //         $stmt = $conn->prepare($query);

        //         $stmt->bindValue(':entryid', $entryid, PDO::PARAM_INT);
        //         $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        //         $stmt->bindParam(':program_id', $program_id, PDO::PARAM_INT);
        //         $stmt->bindParam(':date_entry', $date_entry, PDO::PARAM_STR);
        //         $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        //         $stmt->bindParam(':type_beneficiary', $type_beneficiary, PDO::PARAM_STR);
        //         $stmt->bindParam(':count_male', $count_male, PDO::PARAM_INT);
        //         $stmt->bindParam(':count_female', $count_female, PDO::PARAM_INT);
        //         $stmt->bindParam(':poor_rate', $poor_rate, PDO::PARAM_INT);
        //         $stmt->bindParam(':fair_rate', $fair_rate, PDO::PARAM_INT);
        //         $stmt->bindParam(':satisfactory_rate', $satisfactory_rate, PDO::PARAM_INT);
        //         $stmt->bindParam(':verysatisfactory_rate', $verysatisfactory_rate, PDO::PARAM_INT);
        //         $stmt->bindParam(':excellent_rate', $excellent_rate, PDO::PARAM_INT);
        //         $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);
        //         $stmt->bindParam(':unitOpt', $unitOpt, PDO::PARAM_STR);
        //         $stmt->bindParam(':serviceOpt', $serviceOpt, PDO::PARAM_STR);
        //         $stmt->bindParam(':partners', $partners, PDO::PARAM_STR);
        //         $stmt->bindParam(':fac_staff', $fac_staff, PDO::PARAM_STR);
        //         $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        //         $stmt->bindParam(':cost_fund', $cost_fund, PDO::PARAM_INT);
        //         $stmt->bindParam(':filename', $filename, PDO::PARAM_STR);

        //         if ($stmt->execute()) {
        //             // Update successful
        //             http_response_code(200);
        //             echo json_encode(['success' => 1, 'message' => 'Report updated successfully']);
                    
        //             $query = "SELECT * FROM `actualreportbytotal_tbl` WHERE user_id = :user_id";
        //             $stmt = $conn->prepare($query);
        //             $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        //             $stmt->execute();
        //             $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //             if ($stmt->rowCount() > 0){

        //                 if (empty($_FILES['file'])) {
        //                     $particular_id = 1;


        //                 }

        //             } else {
        //                 echo json_encode([
        //                     'success' => 0,
        //                     'message' => 'No data!'
        //                 ]);
        //             }
        //         } else {
        //             // Update failed
        //             http_response_code(500);
        //             echo json_encode(['success' => 0, 'message' => 'Failed to update report']);
        //         }

        //     } else {
        //         echo json_encode([
        //             'success' => 0,
        //             'message' => 'No file uploaded'
        //         ]);
        //     }
        // }

    }  catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()
        ]);
        exit;
    }

?>
