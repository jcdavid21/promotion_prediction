<?php 
    require_once('config.php');

    // Load JSON data
    $json_data = file_get_contents('employee_data.json');
    $employees = json_decode($json_data, true);

    // Check if JSON decoding was successful
    if (!$employees) {
        die("Error decoding JSON data");
    }

    foreach ($employees as $employee) {
        $first_name = str_replace(",", "", explode(" ", $employee['Employee Name'])[0]);
        $date_string = $employee['Start_Date'];

        // Convert date format
        $date = DateTime::createFromFormat("d-M-y", $date_string);
        if (!$date) {
            echo "Invalid date format: $date_string\n";
            continue; // Skip this entry if date conversion fails
        }

        $formatted_date = $date->format("Y-m-d");

        $query_select = "SELECT * FROM tbl_employee_details where emp_name LIKE '%".$first_name."%' AND start_date = '".$formatted_date."'";
        $result = $conn->query($query_select);
        
        if($result -> num_rows > 0){
            $data = $result->fetch_assoc();
            $emp_id = $data['emp_id'];
        
            // Insert into tbl_eval_attendance to generate eval_id
            $insert_attendance = "INSERT INTO tbl_eval_attendance (emp_id, tardiness, tardy, comb_ab_hd, comb_uab_uhd, AB, UAB, HD, UHD) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_attendance);
            $stmt->bind_param("iiiiiiiii", $emp_id, $employee['Tardiness'], $employee['Tardy Minutes'], $employee['Comb_AB_HD'], $employee['Comb_UAB_UHD'], $employee['AB'], $employee['UAB'], $employee['HD'], $employee['UHD']);
            $stmt->execute();
            
            // Get the generated eval_id
            $eval_id = $conn->insert_id;

            echo "Generated eval_id: ".$eval_id."\n";

            // Insert into tbl_eval_discipline
            $insert_discipline = "INSERT INTO tbl_eval_discipline (eval_id, minor, grave, suspension) 
                                VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_discipline);
            $stmt->bind_param("iiii", $eval_id, $employee['Minor_Discipline'], $employee['Grave_Discipline'], $employee['Suspension']);
            $stmt->execute();

            // Insert into tbl_eval_others
            $insert_others = "INSERT INTO tbl_eval_others (eval_id, performance, manager_input, psa_input) 
                            VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_others);
            $stmt->bind_param("iddd", $eval_id, $employee['Performance_Evaluation'], $employee['Manager_Input'], $employee['PSA_Input']);
            $stmt->execute();
        }
    }

    echo "Data inserted successfully";
?>
