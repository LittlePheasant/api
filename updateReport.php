<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: access, Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: PUT, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");


    error_reporting(E_ERROR);
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

    // Get the report ID from the request
    $entry_id = $_GET['id'];

    // Get the updated report data from the request body
    $data = json_decode(file_get_contents('php://input'));

    // Extract the individual fields from the data
    $date_entry = $data->date_entry;
    $facilitator = $data->facilitator;
    $title = $data->title;
    $type_beneficiary = $data->type_beneficiary;
    $count_male = $data->count_male;
    $count_female = $data->count_female;
    $poor_rate = $data->poor_rate;
    $fair_rate = $data->fair_rate;
    $satisfactory_rate = $data->satisfactory_rate;
    $verysatisfactory_rate = $data->verysatisfactory_rate;
    $excellent_rate = $data->excellent_rate;
    $duration = $data->duration;
    $unitOpt = $data->unitOpt;
    $serviceOpt = $data->serviceOpt;
    $partners = $data->partners;
    $fac_staff = $data->fac_staff;
    $role = $data->role;
    $cost_fund = $data->cost_fund;
    $_file = $data->_file;

    // Prepare the update query
    $query = "UPDATE `monthlyreport_tbl` 
              SET 
                    date_entry = :date_entry, 
                    facilitator = :facilitator, 
                    title = :title,
                    type_beneficiary = :type_beneficiary,
                    count_male = :count_male,
                    count_female = :count_female,
                    poor_rate = :poor_rate,
                    fair_rate = :fair_rate,
                    satisfactory_rate = :satisfactory_rate,
                    verysatisfactory_rate = :verysatisfactory_rate,
                    excellent_rate = :excellent_rate,
                    duration = :duration,
                    unitOpt = :unitOpt,
                    serviceOpt = :serviceOpt,
                    partners = :partners,
                    fac_staff = :fac_staff,
                    role = :role,
                    cost_fund = :cost_fund,
                    _file = :_file
              WHERE entry_id = :entry_id";

    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Bind the parameters
    $stmt->bindParam(':date_entry', $date_entry, PDO::PARAM_STR);
    $stmt->bindParam(':facilitator', $facilitator, PDO::PARAM_STR);
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
    $stmt->bindParam(':_file', $_file, PDO::PARAM_STR);
    $stmt->bindParam(':entry_id', $entry_id, PDO::PARAM_INT);

    // Execute the update query
    if ($stmt->execute()) {
        // Update successful
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Report updated successfully']);
    } else {
        // Update failed
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update report']);
    }

?>
