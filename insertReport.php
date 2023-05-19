<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: access, Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json; charset=UTF-8");
    
    
    error_reporting(E_ERROR);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == "OPTIONS") {
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
    
    $data = json_decode(file_get_contents("php://input"));


    //echo json_encode($data);

    $user_id = null; //Set user_id
    

    //Get the file name and temporay location
    $fileName = $data->_file;
    $tmpName = $fileName;
    //echo json_encode($fileName);

    //Set the directory to store the file in 
    $uploadDir = 'uploads/';
    $filePath = $uploadDir . $fileName;

    //Move the file to the directory
    if(move_uploaded_file($tmpName, $filePath)){

        //File uploaded Successfully
        //Now save the file path to the database using pdo
        echo('1');
        
    }
    
    try {

        $user_id = $user_id;
        $date_entry = htmlspecialchars(trim($data->date_entry));
        $facilitator = htmlspecialchars(trim($data->facilitator));
        $title = htmlspecialchars(trim($data->title));
        $type_beneficiary = htmlspecialchars(trim($data->type_beneficiary));
        $count_male = $data->count_male;
        $count_female = $data->count_female;
        $poor_rate = $data->poor_rate;
        $fair_rate = $data->fair_rate;
        $satisfactory_rate = $data->satisfactory_rate;
        $verysatisfactory_rate = $data->verysatisfactory_rate;
        $excellent_rate = $data->excellent_rate;
        $duration = $data->duration;
        $unitOpt = htmlspecialchars(trim($data->unitOpt));
        $serviceOpt = htmlspecialchars(trim($data->serviceOpt));
        $partners = htmlspecialchars(trim($data->partners));
        $fac_staff = htmlspecialchars(trim($data->fac_staff));
        $role = htmlspecialchars(trim($data->role));
        $cost_fund = $data->cost_fund;
        $filePath = basename($filePath);

    
        $query = "INSERT INTO `monthlyreport_tbl`(
        user_id,
        date_entry,
        facilitator,
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
        ) 
        VALUES(
        :user_id,
        :date_entry,
        :facilitator,
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
        :_file
        )";
    
        $stmt = $conn->prepare($query);

        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':date_entry', $date_entry, PDO::PARAM_STR);
        $stmt->bindValue(':facilitator', $facilitator, PDO::PARAM_STR);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':type_beneficiary', $type_beneficiary, PDO::PARAM_STR);
        $stmt->bindValue(':count_male', $count_male, PDO::PARAM_INT);
        $stmt->bindValue(':count_female', $count_female, PDO::PARAM_INT);
        $stmt->bindValue(':poor_rate', $poor_rate, PDO::PARAM_INT);
        $stmt->bindValue(':fair_rate', $fair_rate, PDO::PARAM_INT);
        $stmt->bindValue(':satisfactory_rate', $satisfactory_rate, PDO::PARAM_INT);
        $stmt->bindValue(':verysatisfactory_rate', $verysatisfactory_rate, PDO::PARAM_INT);
        $stmt->bindValue(':excellent_rate', $excellent_rate, PDO::PARAM_INT);
        $stmt->bindValue(':duration', $duration, PDO::PARAM_INT);
        $stmt->bindValue(':unitOpt', $unitOpt, PDO::PARAM_STR);
        $stmt->bindValue(':serviceOpt', $serviceOpt, PDO::PARAM_STR);
        $stmt->bindValue(':partners', $partners, PDO::PARAM_STR);
        $stmt->bindValue(':fac_staff', $fac_staff, PDO::PARAM_STR);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);
        $stmt->bindValue(':cost_fund', $cost_fund, PDO::PARAM_INT);
        $stmt->bindValue(':_file', $filePath, PDO::PARAM_STR);


        if ($stmt->execute()) {
            $particular_id = 1;
            //http_response_code(201);
            echo json_encode([
                'success' => 1,
                'message' => 'Data Inserted Successfully.'
            ]);
        } else {
            echo json_encode([
                'success' => 0,
                'message' => 'There is some problem in data inserting'
            ]);
        }

        $query = "SELECT 1 FROM `actualreportbytotal_tbl` WHERE user_id = :user_id AND particular_id = :particular_id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn()) { //check if row is empty by user_id nad particular_id
            
            if (!empty($data->_file)) { //checks if file is not empty

                // Update `count` field in `actualreportbytotal_tbl` table
                $query = "UPDATE `actualreportbytotal_tbl` SET count = count + 1 WHERE user_id = :user_id AND particular_id = :particular_id";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    // Count field updated successfully
                    //http_response_code(201);
                    echo json_encode([
                        'success' => 1,
                        'message' => 'Count field updated.'
                    ]);
                } else {
                    // Failed to update count field
                    //http_response_code(500);
                    echo json_encode([
                        'success' => 0,
                        'message' => 'Failed to update count field.'
                    ]);
                }
            } else {
                // File path is empty, no need to update count field
                //http_response_code(201);
                echo json_encode([
                    'success' => 1,
                    'message' => 'Success but no file uploaded.'
                ]);
            };

            if (!empty($data->unitOpt)) { //checks if uniopt is not empty

                $particular_id = 2;
                $weight = null;
                $total_count_male_female = $count_male + $count_female;

                if ($data->unitOpt == 'Hours') {
                    if ($data->duration == 8) {
                        $weight = 1;
                    } else {
                        $weight = 0.5;
                    }
                } else {
                    if ($data->duration == 1) {
                        $weight = 1;
                    } elseif ($data->duration == 2) {
                        $weight = 1.25;
                    } elseif ($data->duration > 2 && $data->duration < 5) {
                        $weight = 1.5;
                    } else {
                        $weight = 2;
                    }
                }

                $count_total = $total_count_male_female * $weight;
                
                // Update `count` field in `actualreportbytotal_tbl` table

                $query = "UPDATE `actualreportbytotal_tbl` SET count = count + $count_total WHERE user_id = :user_id AND particular_id = :particular_id";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindValue(':particular_id', $particular_id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    // Count field updated successfully
                    //http_response_code(201);
                    echo json_encode([
                        'success' => 2,
                        'message' => 'Data Inserted Successfully. Count field updated.',
                        'data' => $count_total,
                    ]);
                } else {
                    // Failed to update count field
                    //http_response_code(500);
                    echo json_encode([
                        'success' => 0,
                        'message' => 'Failed to update count field in actualaccreport table.'
                    ]);
                }
            } else {
                // UnitOpt is empty, no need to update count field
                //http_response_code(201);
                echo json_encode([
                    'success' => 1,
                    'message' => 'Data Inserted Successfully.'
                ]);
            };

        } else {
            echo json_encode([
                'success' => 0,
                'message' => 'Cant find IDs.'
            ]);
        }
    
    } catch (PDOException $e) {
        //http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()

        ]);
    }

?>
