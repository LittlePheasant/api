<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
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

    $post_max_size=ini_get('post_max_size');
    $upload_max_filesize=ini_get('upload_max_filesize');

    try {

        $userid = null;
        $programid = null;
        $countmale = null;
        $countfemale = null;
        $satisfactoryrate = null;
        $verysatisfactoryrate = null;
        $excellentrate = null;
        $duration = null;

        // Validate user_id (integer value expected)
        if (isset($_POST['user_id']) && isset($_POST['program_id']) && isset($_POST['count_male']) && 
            isset($_POST['count_female']) && isset($_POST['satisfactory_rate']) && isset($_POST['verysatisfactory_rate']) &&
            isset($_POST['excellent_rate']) && isset($_POST['duration'])) {
            
            $userid = filter_var($_POST['user_id'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => 'all_records',
                    'min_range' => 1
                ]
            ]);

            $programid = filter_var($_POST['program_id'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => 'all_records',
                    'min_range' => 1
                ]
            ]);

            $countmale = filter_var($_POST['count_male'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => 'all_records',
                    'min_range' => 1
                ]
            ]);

            $countfemale = filter_var($_POST['count_female'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => 'all_records',
                    'min_range' => 1
                ]
            ]);

            $satisfactoryrate = filter_var($_POST['satisfactory_rate'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => 'all_records',
                    'min_range' => 1
                ]
            ]);

            $verysatisfactoryrate = filter_var($_POST['verysatisfactory_rate'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => 'all_records',
                    'min_range' => 1
                ]
            ]);

            $excellentrate = filter_var($_POST['excellent_rate'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => 'all_records',
                    'min_range' => 1
                ]
            ]);

            $duration = filter_var($_POST['duration'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => 'all_records',
                    'min_range' => 1
                ]
            ]);
            
            if (is_int($userid) && is_int($programid) && is_int($countmale) && is_int($countfemale) &&
                is_int($satisfactoryrate) && is_int($verysatisfactoryrate) &&
                is_int($excellentrate) && is_int($duration)) {
                http_response_code(200);
            } else {
                http_response_code(400);
            }

        } else {
            http_response_code(400);
        }

        // Access the 'file' field
        $filesize = $_FILES['file']['size'];
        $filename = $_FILES['file']['name'];
        //echo json_encode($file);

        $maxFileSize = 2 * 1024 * 1024;

        if ($filesize > $maxFileSize) {
            http_response_code(400);
            echo json_encode([
                'success' => 0,
                'message' => 'File size exceeds the maximum allowed size.'
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
        $user_id = $userid;
        $program_id = $programid;
        $date_entry = $_POST['date_entry'];
        $title = $_POST['title'];
        $type_beneficiary = $_POST['type_beneficiary'];
        $count_male = $countmale;
        $count_female = $countfemale;
        $poor_rate = $_POST['poor_rate'];
        $fair_rate = $_POST['fair_rate'];
        $satisfactory_rate = $satisfactoryrate;
        $verysatisfactory_rate = $verysatisfactoryrate;
        $excellent_rate = $excellentrate;
        $duration = $duration;
        $unitOpt = $_POST['unitOpt'];
        $serviceOpt = $_POST['serviceOpt'];
        $partners = $_POST['partners'];
        $fac_staff = $_POST['fac_staff'];
        $role = $_POST['role'];
        $cost_fund = $_POST['cost_fund'];
        
        
        // Move the file to the directory
        $uploadDir = "attachments/uploads/";
        $filePath = $uploadDir . $filename;

        if (file_exists($filePath)) {
            echo 'File already exists => ' . $filePath;
            exit();
        } else {
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            } else {
                // Everything for owner, read and execute for others
                chmod($uploadDir, 0755);
                if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                    //echo 'File successfully uploaded => "' . $filePath;

                    $query = "INSERT INTO `monthlyreport_tbl` (
                        user_id,
                        program_id,
                        date_entry,
                        title,
                        type_beneficiary,
                        count_male,
                        count_female,
                        poor_rate,
                        fair_rate,
                        satisfactory_rate,
                        verysatisfactory_rate,
                        excellent_rate,
                        duration,
                        unitOpt,
                        serviceOpt,
                        partners,
                        fac_staff,
                        role,
                        cost_fund,
                        _file
                    ) VALUES (
                        :user_id,
                        :program_id,
                        :date_entry,
                        :title,
                        :type_beneficiary,
                        :count_male,
                        :count_female,
                        :poor_rate,
                        :fair_rate,
                        :satisfactory_rate,
                        :verysatisfactory_rate,
                        :excellent_rate,
                        :duration,
                        :unitOpt,
                        :serviceOpt,
                        :partners,
                        :fac_staff,
                        :role,
                        :cost_fund,
                        :filename
                    )";
            
                    $stmt = $conn->prepare($query);

                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(':program_id', $program_id, PDO::PARAM_INT);
                    $stmt->bindParam(':date_entry', $date_entry, PDO::PARAM_STR);
                    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
                    $stmt->bindParam(':type_beneficiary', $type_beneficiary, PDO::PARAM_STR);
                    $stmt->bindParam(':count_male', $count_male, PDO::PARAM_INT);
                    $stmt->bindParam(':count_female', $count_female, PDO::PARAM_INT);
                    $stmt->bindParam(':poor_rate', $poor_rate, PDO::PARAM_INT);
                    $stmt->bindParam(':fair_rate', $fair_rate, PDO::PARAM_INT);
                    $stmt->bindParam(':satisfactory_rate', $satisfactory_rate, PDO::PARAM_INT);
                    $stmt->bindParam(':verysatisfactory_rate', $verysatisfactory_rate, PDO::PARAM_INT);
                    $stmt->bindParam(':excellent_rate', $excellent_rate, PDO::PARAM_INT);
                    $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);
                    $stmt->bindParam(':unitOpt', $unitOpt, PDO::PARAM_STR);
                    $stmt->bindParam(':serviceOpt', $serviceOpt, PDO::PARAM_STR);
                    $stmt->bindParam(':partners', $partners, PDO::PARAM_STR);
                    $stmt->bindParam(':fac_staff', $fac_staff, PDO::PARAM_STR);
                    $stmt->bindParam(':role', $role, PDO::PARAM_STR);
                    $stmt->bindParam(':cost_fund', $cost_fund, PDO::PARAM_INT);
                    $stmt->bindParam(':filename', $filename, PDO::PARAM_STR);

                    if ($stmt->execute()) {

                        // Begin transaction
                        $conn->beginTransaction();

                        try {

                            // Extract the quarter from the date (1, 2, 3, or 4)
                            $quarter = ceil(date('n', strtotime($date_entry)) / 3);

                            if ($quarter >= 1 && $quarter <= 4) {
                                $quarter_id = $quarter;
                                $particular_id = 1;

                                $query = "SELECT 1 FROM `actualreportbytotal_tbl` WHERE user_id = :user_id AND quarter_id = :quarter_id AND particular_id = :particular_id";

                                $stmt = $conn->prepare($query);
                                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                                $stmt->bindValue(':quarter_id', $quarter_id, PDO::PARAM_INT);
                                $stmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                                $stmt->execute();

                                if ($stmt->fetchColumn()) { //check if row is empty by user_id

                                    //Check if file is empty
                                    if (!empty($_FILES['file'])){
                                        // Update `count` field in `actualreportbytotal_tbl` table
                                        $query = "UPDATE `actualreportbytotal_tbl` SET count = count + 1 WHERE user_id = :user_id AND quarter_id = :quarter_id AND particular_id = :particular_id";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                                        $stmt->bindValue(':quarter_id', $quarter_id, PDO::PARAM_INT);
                                        $stmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                                        $stmt->execute();

                                        if (!empty($unitOpt)) { //checks if uniopt is not empty

                                            $particular_id = 2;
                                            $weight = null;
                                            $total_count_male_female = (int)$count_male + (int)$count_female;
            
                                            if ($unitOpt == 'Hours') {
                                                if ($duration == 8) {
                                                    $weight = 1;
                                                } else {
                                                    $weight = 0.5;
                                                }
                                            } else {
                                                if ($duration == 1) {
                                                    $weight = 1;
                                                } elseif ($duration == 2) {
                                                    $weight = 1.25;
                                                } elseif ($duration > 2 && $duration < 5) {
                                                    $weight = 1.5;
                                                } else {
                                                    $weight = 2;
                                                }
                                            }
            
                                            $count_total = $total_count_male_female * $weight;
                                            
                                            // Update `count` field in `actualreportbytotal_tbl` table
            
                                            $query = "UPDATE `actualreportbytotal_tbl` SET count = count + $count_total 
                                                    WHERE user_id = :user_id AND quarter_id = :quarter_id AND particular_id = :particular_id";
                                            $stmt = $conn->prepare($query);
                                            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                                            $stmt->bindValue(':quarter_id', $quarter_id, PDO::PARAM_INT);
                                            $stmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                                            $stmt->execute();
                                        }

                                        if (!empty($program_id)) {
                                                
                                            $countQuery = "SELECT count(*) AS count_total FROM `college_programs_tbl` WHERE user_id = :user_id";
        
                                            $countStmt = $conn ->prepare($countQuery);
                                            $countStmt ->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                                            $countStmt ->execute();
                                            $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
        
                                            $particular_id = 3;
                                            $count = $countResult['count_total'];
        
                                            $updateQuery = "UPDATE `actualreportbytotal_tbl` SET count = :count WHERE user_id = :user_id AND quarter_id = :quarter_id AND particular_id = :particular_id";
        
                                            $updateStmt = $conn->prepare($updateQuery);
                                            $updateStmt->bindValue(':count', $count, PDO::PARAM_INT);
                                            $updateStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                                            $updateStmt->bindValue(':quarter_id', $quarter_id, PDO::PARAM_INT);
                                            $updateStmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                                            $updateStmt->execute();
                                        }

                                        if (!empty($satisfactory_rate) && !empty($verysatisfactory_rate) 
                                                                            && !empty($excellent_rate)) {

                                            $particular_id = 4;

                                            $total_count_male_female = (int)$count_male + (int)$count_female;
                                            $sum = (int)$satisfactory_rate + (int)$verysatisfactory_rate + (int)$excellent_rate;
                                            $result = null;

                                            if ($sum === $total_count_male_female) {
                                                $result = 100;
                                            } else {
                                                if ($total_count_male_female > 0) {
                                                    $percentage = ($sum / $total_count_male_female) * 100;
                                                    $result = round($percentage, 0);
                                                } else {
                                                    // Handle the case when $total_count_male_female is zero or empty
                                                    $result = 0;
                                                }
                                            }

                                            $query = "UPDATE `actualreportbytotal_tbl` SET count = $result WHERE user_id = :user_id AND quarter_id = :quarter_id AND particular_id = :particular_id";
                                            $stmt = $conn->prepare($query);
                                            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                                            $stmt->bindValue(':quarter_id', $quarter_id, PDO::PARAM_INT);
                                            $stmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                                            $stmt->execute();
                                        }
                                    } else {
                                        http_response_code(500);
                                        echo json_encode([
                                            'success' => 0,
                                            'message' => 'No file uploaded.'
                                        ]);
                                    }
                                } else {
                                    //if no data by ids

                                    if (!empty($_FILES['file'])) { //checks if file is not empty
                                
                                        $particular_id = 1;
                                        $count = 1;
                                        // Insert `count` field in `actualreportbytotal_tbl` table
                                        $insertQuery = "INSERT INTO `actualreportbytotal_tbl` (
                                                    quarter_id,
                                                    particular_id,
                                                    user_id,
                                                    count
                                                ) VALUES (
                                                    :quarter_id,
                                                    :particular_id,
                                                    :user_id,
                                                    :count
                                                )";

                                        $insertStmt = $conn->prepare($insertQuery);
                                        $insertStmt->bindValue(':quarter_id', $quarter_id, PDO::PARAM_INT);
                                        $insertStmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                                        $insertStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                                        $insertStmt->bindValue(':count', $count, PDO::PARAM_INT);
                                        $insertStmt->execute();

                                        if (!empty($unitOpt)) { //checks if uniopt is not empty

                                            $particular_id = 2;
                                            $weight = null;
                                            $total_count_male_female = $count_male + $count_female;
            
                                            if ($unitOpt == 'Hours') {
                                                if ($duration == 8) {
                                                    $weight = 1;
                                                } else {
                                                    $weight = 0.5;
                                                }
                                            } else {
                                                if ($duration == 1) {
                                                    $weight = 1;
                                                } elseif ($duration == 2) {
                                                    $weight = 1.25;
                                                } elseif ($duration > 2 && $duration < 5) {
                                                    $weight = 1.5;
                                                } else {
                                                    $weight = 2;
                                                }
                                            }
            
                                            $count = $total_count_male_female * $weight;
                                            
                                            // Insert `count` field in `actualreportbytotal_tbl` table
            
                                            $insertQuery = "INSERT INTO `actualreportbytotal_tbl` (
                                                        quarter_id,
                                                        particular_id,
                                                        user_id,
                                                        count
                                                    ) VALUES (
                                                        :quarter_id,
                                                        :particular_id,
                                                        :user_id,
                                                        :count
                                                    )";
    
                                            $insertStmt = $conn->prepare($insertQuery);
                                            $insertStmt->bindValue(':quarter_id', $quarter_id, PDO::PARAM_INT);
                                            $insertStmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                                            $insertStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                                            $insertStmt->bindValue(':count', $count, PDO::PARAM_INT);
                                            $insertStmt->execute();
    
                                        }
    
                                        if (!empty($program_id)) {
                                            
                                            $countQuery = "SELECT count(*) AS count_total FROM `college_programs_tbl` WHERE user_id = :user_id";
    
                                            $countStmt = $conn ->prepare($countQuery);
                                            $countStmt ->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                                            $countStmt ->execute();
                                            $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    
                                            $particular_id = 3;
                                            $count = $countResult['count_total'];
    
                                            $insertQuery = "INSERT INTO `actualreportbytotal_tbl` (
                                                        quarter_id,
                                                        particular_id,
                                                        user_id,
                                                        count
                                                    ) VALUES (
                                                        :quarter_id,
                                                        :particular_id,
                                                        :user_id,
                                                        :count
                                                    )";
    
                                            $insertStmt = $conn->prepare($insertQuery);
                                            $insertStmt->bindValue(':quarter_id', $quarter_id, PDO::PARAM_INT);
                                            $insertStmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                                            $insertStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                                            $insertStmt->bindValue(':count', $count, PDO::PARAM_INT);
                                            $insertStmt->execute();
                                        }
    
                                        if (!empty($satisfactory_rate) && !empty($verysatisfactory_rate) 
                                                                            && !empty($excellent_rate)) {
    
                                            $particular_id = 4;
    
                                            $total_count_male_female = ($count_male + $count_female);
                                            $sum = (int)$satisfactory_rate + (int)$verysatisfactory_rate + (int)$excellent_rate;
                                            $result = null;
    
                                            if ($sum === $total_count_male_female) {
                                                $result = 100;
                                            } else {
                                                if ($total_count_male_female > 0) {
                                                    $percentage = ($sum / $total_count_male_female) * 100;
                                                    $result = round($percentage, 0);
                                                } else {
                                                    // Handle the case when $total_count_male_female is zero or empty
                                                    $result = 0;
                                                }
                                            }
    
                                            $insertQuery = "INSERT INTO `actualreportbytotal_tbl` (
                                                        quarter_id,
                                                        particular_id,
                                                        user_id,
                                                        count
                                                    ) VALUES (
                                                        :quarter_id,
                                                        :particular_id,
                                                        :user_id,
                                                        :result
                                                    )";
    
                                            $insertStmt = $conn->prepare($insertQuery);
                                            $insertStmt->bindValue(':quarter_id', $quarter_id, PDO::PARAM_INT);
                                            $insertStmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                                            $insertStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                                            $insertStmt->bindValue(':result', $result, PDO::PARAM_INT);
                                            $insertStmt->execute();
                                        }
                                    } else {
                                        http_response_code(500);
                                        echo json_encode([
                                            'success' => 0,
                                            'message' => 'No file uploaded.'
                                        ]);
                                    }
                                }
                            } else {
                                //INVALID DATE
                                throw new Exception("Invalid date.");
                            }

                            // Commit the transaction
                            $conn->commit();
                            
                            http_response_code(200);
                            echo json_encode([
                                'success' => 1,
                                'message' => 'Data inserted successfully.'
                            ]);
                        } catch(Exception $e) {

                            // Rollback the transaction on error
                            $conn->rollback();
                            
                            http_response_code(500);
                            echo json_encode([
                                'success' => 0,
                                'message' => $e->getMessage()
                            ]);
                        }

                    } else {
                        http_response_code(500);
                        echo json_encode([
                            'success' => 0,
                            'message' => 'There is some problem in data inserting'
                        ]);
                    }
                } else {
                    // Failed to upload the file
                    http_response_code(500);
                    echo json_encode([
                        'success' => 0,
                        'message' => 'Failed to upload file to local'
                    ]);
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
