<?php
    session_start();
    require_once("../config.php");

    if(isset($_POST["username"]) && isset($_POST["password"])){
        $username = $_POST["username"];
        $password = $_POST["password"];

        $sql_query = "SELECT ta.acc_id, ta.username, ta.password, ta.position_id, tp.position_name, 
                      CONCAT(td.first_name, ' ', td.middle_name, ' ', td.last_name) AS full_name, td.contact, td.address 
                      FROM tbl_account ta 
                      JOIN tbl_positions tp ON ta.position_id = tp.position_id 
                      JOIN tbl_account_details td ON ta.acc_id = td.acc_id 
                      WHERE ta.username = ?";
        
        $stmt = $conn->prepare($sql_query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            $user = $result->fetch_assoc();
            $hashed_password = $user["password"];

            if(password_verify($password, $hashed_password)){
                $_SESSION["full_name"] = $user["full_name"];
                $_SESSION["position"] = $user["position_name"];
                $_SESSION["acc_id"] = $user["acc_id"];
                $_SESSION["position_id"] = $user["position_id"];
                echo "success";
            } else {
                echo "failed"; // Wrong password
            }
        } else {
            echo "failed"; // No user found
        }

        $stmt->close();
    } else {
        echo "failed"; // No input provided
    }
?>
