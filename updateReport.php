<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: POST, PUT");
    header("Content-Type: application/json");


    error_reporting(E_ERROR);
    ini_set('display_errors', 1);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == "OPTIONS") {
        header("Content-Type: application/json");
        die();
    }


    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Request!',
        ]);
    }

    require 'db_connect.php';
    $database = new Operations();
    $conn = $database->dbConnection();


    try {

        $entryid = null;
        $userid = null;
        $programid = null;
        $countmale = null;
        $countfemale = null;
        $satisfactoryrate = null;
        $verysatisfactoryrate = null;
        $excellentrate = null;
        $duration = null;
        $unitOpt = null;
        $result = null;
        $count_total = null;
        $status = null;

        $updatedData = [];

        if (isset($_GET['id'])) {
            $entryid = filter_var($_GET['id'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => null,
                    'min_range' => 1
                ]
            ]);
            
        }

        // Validate ids and rates (integer value expected)
        if (isset($_POST['user_id']) && isset($_POST['program_id']) && isset($_POST['count_male']) && 
            isset($_POST['count_female']) && isset($_POST['satisfactory_rate']) && isset($_POST['verysatisfactory_rate']) &&
            isset($_POST['excellent_rate']) && isset($_POST['duration']) && isset($_POST['unitOpt'])) {
            
            $userid = filter_var($_POST['user_id'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => null,
                    'min_range' => 0
                ]
            ]);

            $programid = filter_var($_POST['program_id'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => null,
                    'min_range' => 0
                ]
            ]);

            $countmale = filter_var($_POST['count_male'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => null,
                    'min_range' => 0
                ]
            ]);

            $countfemale = filter_var($_POST['count_female'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => null,
                    'min_range' => 0
                ]
            ]);

            $satisfactoryrate = filter_var($_POST['satisfactory_rate'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => null,
                    'min_range' => 0
                ]
            ]);

            $verysatisfactoryrate = filter_var($_POST['verysatisfactory_rate'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => null,
                    'min_range' => 0
                ]
            ]);

            $excellentrate = filter_var($_POST['excellent_rate'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => null,
                    'min_range' => 0
                ]
            ]);

            $duration = filter_var($_POST['duration'], FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => null,
                    'min_range' => 0
                ]
            ]);

            $unitOpt = $_POST['unitOpt'];
            
            if (is_int($userid) && is_int($programid) && is_int($countmale) && is_int($countfemale) &&
                is_int($satisfactoryrate) && is_int($verysatisfactoryrate) &&
                is_int($excellentrate) && is_int($duration)) {

                $total_count_male_female = $countmale + $countfemale;
                $sumRates = $satisfactoryrate + $verysatisfactoryrate + $excellentrate;
                $weight = null;
                
                //computation for particular_id 4
                if ($sumRates === $total_count_male_female) {
                    $result = 100;
                } else {
                    if ($total_count_male_female > 0) {
                        $percentage = ($sumRates / $total_count_male_female) * 100;
                        $result = round($percentage, 2);
                    } else {
                        // Handle the case when $total_count_male_female is zero or empty
                        $result = 0;
                    }
                }

                //computation for particular_id 3
                if ($unitOpt == 'Hours') {
                    if ($duration === 8) {
                        $weight = 1;
                    } else {
                        $weight = 0.5;
                    }
                } else {
                    if ($duration === 1) {
                        $weight = 1;
                    } elseif ($duration === 2) {
                        $weight = 1.25;
                    } elseif ($duration > 2 && $duration < 5) {
                        $weight = 1.5;
                    } else {
                        $weight = 2;
                    }
                }

                $count_total = $total_count_male_female * $weight;

            }

        } else {
            // Get the updated report data from the request body
            $data = file_get_contents('php://input');

            parse_str($data, $formData);

            // Extract the form fields
            preg_match_all('/name="([^"]*)"\s*[\r\n]+(.+)/', $data, $matches, PREG_SET_ORDER);
            $formFields = [];
            foreach ($matches as $match) {
                $fieldName = $match[1];
                $fieldValue = trim($match[2]);
                $formFields[$fieldName] = $fieldValue;
            }

            $status = $formFields['status'];
        }

        $date_entry = $_POST['date_entry'];
        $title = $_POST['title'];
        $type_beneficiary = $_POST['type_beneficiary'];
        $poor_rate = $_POST['poor_rate'];
        $fair_rate = $_POST['fair_rate'];
        $serviceOpt = $_POST['serviceOpt'];
        $partners = $_POST['partners'];
        $fac_staff = $_POST['fac_staff'];
        $role = $_POST['role'];
        $cost_fund = $_POST['cost_fund'];
        $file = $_POST['file'];

        if (!empty($status)) {

            $updateSql = "UPDATE `monthlyreport_tbl` SET status = :status WHERE entry_id = :entryid";
            $updateStmt = $conn -> prepare($updateSql);
            //$updateStmt->bindValue(':userid', $userid, PDO::PARAM_INT);
            $updateStmt->bindValue(':entryid', $entryid, PDO::PARAM_INT);
            $updateStmt->bindValue(':status', $status, PDO::PARAM_STR);

            if ($updateStmt->execute()) {
                echo json_encode([
                    'success' => 1,
                    'message' => 'Status updated!'
                ]);
            } else {
                echo json_encode([
                    'success' => 0,
                    'message' => 'Could not update status!'
                ]);
            }

        } else {
            // Validate file upload
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {

                // Move the file to the directory
                $uploadDir = "attachments/uploads/";
                $filePath = $uploadDir . $file;

                if (file_exists($filePath)) {

                    $updatedData = array(
                        'user_id' => $userid,
                        'program_id' => $programid,
                        'date_entry' => $date_entry,
                        'title' => $title,
                        'type_beneficiary' => $type_beneficiary,
                        'count_male' => $countmale,
                        'count_female' => $countfemale,
                        'poor_rate' => $poor_rate,
                        'fair_rate' => $fair_rate,
                        'satisfactory_rate' => $satisfactoryrate,
                        'verysatisfactory_rate' => $verysatisfactoryrate,
                        'excellent_rate' => $excellentrate,
                        'total_rate_by_length' => $result,
                        'total_trainees_by_length' => $count_total,
                        'duration' => $duration,
                        'unitOpt' => $unitOpt,
                        'serviceOpt' => $serviceOpt,
                        'partners' => $partners,
                        'fac_staff' => $fac_staff,
                        'role' => $role,
                        'cost_fund' => $cost_fund,
                        '_file' => $file
                    );
                    
                    
                } else {
                    echo json_encode([
                        'success' => 0,
                        'message' => 'File ' . $file .  ' not found!'
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
                $uploadDir = "attachments/uploads/";
                $filePath = $uploadDir . $filename;

                if (!file_exists($filePath)) {

                    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {

                        if (unlink($filePath)) {
                            $updatedData = array(
                                'user_id' => $userid,
                                'program_id' => $programid,
                                'date_entry' => $date_entry,
                                'title' => $title,
                                'type_beneficiary' => $type_beneficiary,
                                'count_male' => $countmale,
                                'count_female' => $countfemale,
                                'poor_rate' => $poor_rate,
                                'fair_rate' => $fair_rate,
                                'satisfactory_rate' => $satisfactoryrate,
                                'verysatisfactory_rate' => $verysatisfactoryrate,
                                'excellent_rate' => $excellentrate,
                                'total_rate_by_length' => $result,
                                'total_trainees_by_length' => $count_total,
                                'duration' => $duration,
                                'unitOpt' => $unitOpt,
                                'serviceOpt' => $serviceOpt,
                                'partners' => $partners,
                                'fac_staff' => $fac_staff,
                                'role' => $role,
                                'cost_fund' => $cost_fund,
                                '_file' => $filename
                            );
                        } else {
                            echo json_encode([
                                'success' => 0,
                                'message' => 'File ' . $filename .  ' not deleted!'
                            ]);
                        }
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

            //echo json_encode(['data' => $updatedData]);

            // Retrieve the original row from the database
            $fetchSql = "SELECT * FROM `monthlyreport_tbl` WHERE entry_id = :entryid";
            $fetchStmt = $conn->prepare($fetchSql);
            $fetchStmt->bindValue(':entryid', $entryid);
            $fetchStmt->execute();
            $originalRow = $fetchStmt->fetch(PDO::FETCH_ASSOC);

            // Convert specific columns to their intended data types
            $originalRow['entry_id'] = (int) $originalRow['entry_id'];
            $originalRow['user_id'] = (int) $originalRow['user_id'];
            $originalRow['program_id'] = (int) $originalRow['program_id'];
            $originalRow['count_male'] = (int) $originalRow['count_male'];
            $originalRow['count_female'] = (int) $originalRow['count_female'];
            $originalRow['satisfactory_rate'] = (int) $originalRow['satisfactory_rate'];
            $originalRow['verysatisfactory_rate'] = (int) $originalRow['verysatisfactory_rate'];
            $originalRow['excellent_rate'] = (int) $originalRow['excellent_rate'];
            $originalRow['duration'] = (int) $originalRow['duration'];

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
                    $updateSql .= $column . " = :" . $column;
                    if ($index < count($updateColumns) - 1) {
                        $updateSql .= ", ";
                    }
                }
            
                $updateSql .= " WHERE entry_id = :entryid";

                $updateStmt = $conn->prepare($updateSql);
                
                foreach ($updateValues as $index => $value) {
                    $updateStmt->bindValue(':' . $updateColumns[$index], $value);
                }
                
                $updateStmt->bindValue(':entryid', $entryid);

                if ($updateStmt->execute()) {

                    // Extract the quarter from the date (1, 2, 3, or 4)
                    $quarter = ceil(date('n', strtotime($date_entry)) / 3);

                    // Calculate the starting and ending months of the selected quarter
                    $startMonth = ($quarter - 1) * 3 + 1;
                    $endMonth = $quarter * 3;
                    
                    if ($quarter >= 1 && $quarter <= 4) {
                        $quarter_id = $quarter;
                        $particular_id = 1;

                        $countQuery = "SELECT COUNT(_file) AS count_total 
                                    FROM `monthlyreport_tbl`
                                    WHERE user_id =:userid AND MONTH(date_entry) BETWEEN $startMonth AND $endMonth";

                        $countStmt = $conn->prepare($countQuery);
                        $countStmt->bindValue(':userid', $userid, PDO::PARAM_INT);
                        
                        if ($countStmt ->execute()) {

                            $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
                            $count = (int) $countResult['count_total'];
                            $particular_id = 1;

                            // Update `count` field in `actualreportbytotal_tbl` table
                            $updateQuery = "UPDATE `actualreportbytotal_tbl` SET count = :count WHERE user_id = :userid AND quarter_id = :quarter_id AND particular_id = :particular_id";
                            $updateStmt = $conn->prepare($updateQuery);
                            $updateStmt->bindValue(':count', $count, PDO::PARAM_INT);
                            $updateStmt->bindValue(':userid', $userid, PDO::PARAM_INT);
                            $updateStmt->bindValue(':quarter_id', $quarter_id, PDO::PARAM_INT);
                            $updateStmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                            $updateStmt->execute();

                            if (!empty($unitOpt)) { //checks if uniopt is not empty

                                $countQuery = "SELECT SUM(total_trainees_by_length) as totalSum 
                                                FROM `monthlyreport_tbl` 
                                                WHERE user_id = :userid AND MONTH(date_entry) BETWEEN $startMonth AND $endMonth";

                                $countStmt = $conn ->prepare($countQuery);
                                $countStmt ->bindValue(':userid', $userid, PDO::PARAM_INT);
                                $countStmt ->execute();
                                $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);

                                $total_count = null;
                                $count = doubleval($countResult['totalSum']);
                                $total_count = round($count, 2);
                                $particular_id = 2;
                                
                                // Update `count` field in `actualreportbytotal_tbl` table

                                $updateQuery = "UPDATE `actualreportbytotal_tbl` SET count = :total_count
                                        WHERE user_id = :userid AND quarter_id = :quarter_id AND particular_id = :particular_id";
                                $updateStmt = $conn->prepare($updateQuery);
                                $updateStmt->bindValue(':total_count', strval($total_count), PDO::PARAM_STR);
                                $updateStmt->bindValue(':userid', $userid, PDO::PARAM_INT);
                                $updateStmt->bindValue(':quarter_id', $quarter_id, PDO::PARAM_INT);
                                $updateStmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                                $updateStmt->execute();
                            }

                            if (!empty($programid)) {
                                    
                                $countQuery = "SELECT COUNT(*) AS count_total FROM `college_programs_tbl` WHERE user_id = :userid";

                                $countStmt = $conn ->prepare($countQuery);
                                $countStmt ->bindValue(':userid', $userid, PDO::PARAM_INT);
                                $countStmt ->execute();
                                $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);

                                $particular_id = 3;
                                $count = $countResult['count_total'];

                                $updateQuery = "UPDATE `actualreportbytotal_tbl` SET count = :count WHERE user_id = :userid AND quarter_id = :quarter_id AND particular_id = :particular_id";

                                $updateStmt = $conn->prepare($updateQuery);
                                $updateStmt->bindValue(':count', $count, PDO::PARAM_INT);
                                $updateStmt->bindValue(':userid', $userid, PDO::PARAM_INT);
                                $updateStmt->bindValue(':quarter_id', $quarter_id, PDO::PARAM_INT);
                                $updateStmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                                $updateStmt->execute();
                            }

                            if (!empty($satisfactoryrate) && !empty($verysatisfactoryrate) 
                                                                && !empty($excellentrate)) {

                                $countQuery = "SELECT COUNT(*) AS rowCount, SUM(total_rate_by_length) AS totalSum
                                                FROM `monthlyreport_tbl` 
                                                WHERE user_id = :userid AND MONTH(date_entry) BETWEEN $startMonth AND $endMonth";

                                $countStmt = $conn ->prepare($countQuery);
                                $countStmt ->bindValue(':userid', $userid, PDO::PARAM_INT);
                                $countStmt ->execute();
                                $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);

                                $total_count = null;
                                $initialCount = null;

                                $rowCount = intval($countResult['rowCount']);
                                $totalSum = doubleval($countResult['totalSum']);

                                $initialCount = $totalSum / $rowCount;
                                $total_count = round($initialCount, 2);
                                $particular_id = 4;

                                $updateQuery = "UPDATE `actualreportbytotal_tbl` SET count = :total_count WHERE user_id = :userid AND quarter_id = :quarter_id AND particular_id = :particular_id";
                                $updateStmt = $conn->prepare($updateQuery);
                                $updateStmt->bindValue(':total_count', strval($total_count), PDO::PARAM_STR);
                                $updateStmt->bindValue(':userid', $userid, PDO::PARAM_INT);
                                $updateStmt->bindValue(':quarter_id', $quarter_id, PDO::PARAM_INT);
                                $updateStmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                                $updateStmt->execute();

                            }

                            echo json_encode([
                                'success' => 1,
                                'message' => 'Successfully updated!'
                            ]);

                        } else {
                            echo json_encode([
                                'success' => 0,
                                'message' => 'No file uploaded.'
                            ]);
                        }
                        
                    } else {
                        //INVALID DATE
                        echo json_encode([
                            'success' => 0,
                            'message' => 'Invalid date.'
                        ]);
                    }
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

        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()
        ]);
        exit();
    } 

?>
