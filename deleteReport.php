<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, AUTHORization, X-Requested-With");
    header("Access-Control-Allow-Methods: DELETE");
    header("Content-Type: application/json");
    
    
    error_reporting(E_ERROR);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == "OPTIONS") {
        header("Content-Type: application/json");
        die();
    }       

    
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') :
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Request!.Only DELETE method is allowed',
        ]);
    endif;
    
    require 'db_connect.php';
    $database = new Operations();
    $conn = $database->dbConnection();


    $entryid = null;

    if (isset($_GET['id'])) {
        $entryid = filter_var($_GET['id'], FILTER_VALIDATE_INT, [
            'options' => [
                'default' => null,
                'min_range' => 1
            ]
        ]);
        
    }

    try {

        if (!empty($entryid)) {

            $fetchSql = "SELECT m.*, a.quarter_id
            FROM `monthlyreport_tbl` m
            INNER JOIN `actualreportbytotal_tbl` a ON m.user_id = a.user_id
            WHERE m.entry_id = :entryid";

            $fetchStmt = $conn -> prepare($fetchSql);
            $fetchStmt->bindValue(':entryid', $entryid, PDO::PARAM_INT);
            $fetchStmt->execute();
            $fetchData = $fetchStmt->fetch(PDO::FETCH_ASSOC);
            
            $fetchDataArray = array($fetchData);

            $userid = null;
            $quarterid = null;
            $totalrates = null;
            $totaltrainees = null;
            $filename = null;

            foreach ($fetchDataArray as $items) {

                $userid = intval($items['user_id']);
                $quarterid = intval($items['quarter_id']);
                $totalrates = doubleval($items['total_rate_by_length']);
                $totaltrainees = doubleval($items['total_trainees_by_length']);
                $filename = $items['_file'];
            }

            $conn->beginTransaction();

            try {

                $deleteSql = "DELETE FROM `monthlyreport_tbl` WHERE entry_id = :entryid";
                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->bindValue(':entryid', $entryid, PDO::PARAM_INT);
                
                if($deleteStmt->execute()) {

                    $fetchUpdateSql = "SELECT * FROM `actualreportbytotal_tbl` WHERE quarter_id = :quarterid AND user_id = :userid";
                    $fetchUpdateStmt = $conn->prepare($fetchUpdateSql);
                    $fetchUpdateStmt->bindValue(':quarterid', $quarterid, PDO::PARAM_INT);
                    $fetchUpdateStmt->bindValue(':userid', $userid, PDO::PARAM_INT);
                    $fetchUpdateStmt->execute();

                    $fetchUpdateData = $fetchUpdateStmt->fetchAll(PDO::FETCH_ASSOC);

                    $counts = array();
                    $particularIDs = array();

                    foreach ($fetchUpdateData as $row) {
                        
                        $particularid = intval($row['particular_id']);
                        $count = doubleval($row['count']);

                        if ($particularid === 1) {
                            // Subtract 1 from the count and make sure it doesn't go below 0
                            $count1 = max(0, $count - 1);
                            $pID1 = $particularid;
                        } elseif ($particularid === 2) {
                            // Substract count in actualreportbytotal_tbl to total_trainees in monthlyreport_tbl
                            $count2 = doubleval($count - $totaltrainees);
                            $pID2 = $particularid;
                        } elseif ($particularid === 3) {
                            // If the particular_id is 3, keep the original count
                            $count3 = max(0, $count);
                            $pID3 = $particularid;
                        } else {
                            // Substract count in actualreportbytotal_tbl to total_rate in monthlyreport_tbl 
                            $count4 = doubleval($count - $totalrates);
                            $pID4 = $particularid;
                        }

                        $counts = [$count1, $count2, $count3, $count4];
                        $particularIDs = [$pID1, $pID2, $pID3, $pID4];

                    }

                    // Directory
                    $uploadDir = "attachments/uploads/";
                    $filePath = $uploadDir . $filename;

                    // Check if the file exists before attempting to delete
                    if (file_exists($filePath)) {

                        // Attempt to delete the file
                        if (unlink($filePath)) {

                            // Check if the counts and particularIDs arrays have the same length
                            if (count($counts) === count($particularIDs)) {

                                // Loop through each particular_id and countValue and update the count in the database
                                for ($i = 0; $i < count($particularIDs); $i++) {
                                    $particularId = $particularIDs[$i];
                                    $countValue = $counts[$i];

                                    // Prepare the update query
                                    $updateQuery = "UPDATE `actualreportbytotal_tbl` SET count = :countValue WHERE particular_id = :particularId AND user_id = :userid";
                                    $updateStmt = $conn->prepare($updateQuery);

                                    // Bind the parameters
                                    $updateStmt->bindValue(':countValue', strval($countValue), PDO::PARAM_STR);
                                    $updateStmt->bindValue(':particularId', $particularId, PDO::PARAM_INT);
                                    $updateStmt->bindValue(':userid', $userid, PDO::PARAM_INT);
                                    
                                    // Execute the update query and check if it was successful
                                    $updateStmt->execute()
                                }

                            }
                        } 
                    }

                    // Commit the transaction
                    $conn->commit();

                    echo json_encode([
                        'success' => 1,
                        'message' => 'Data deleted successfully!',
                    ]);


                }

            } catch(Exception $e) {

                // Rollback the transaction on error
                $conn->rollback();
                
                http_response_code(500);
                echo json_encode([
                    'success' => 0,
                    'message' => $e->getMessage()
                ]);
            }

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