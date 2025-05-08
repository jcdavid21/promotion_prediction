<?php
    require_once("../config.php");
    $username = "admin2";
    $password = "admin2";

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql_query = "INSERT INTO tbl_account (username, password, position_id) VALUES (?, ?, 2)";
    $stmt = $conn->prepare($sql_query);
    $stmt->bind_param("ss", $username, $hashedPassword);
    $stmt->execute();

    $generated_id = $stmt->insert_id;

    $sql_details_insert = "insert into tbl_account_details (acc_id, first_name, middle_name, last_name, contact, address) VALUES (?, 'Jc', '', 'David', '09565535401', 'Cielito Homes')";
    $stmt_details = $conn->prepare($sql_details_insert);
    $stmt_details->bind_param("i", $generated_id);
    $stmt_details->execute();

    echo "Account created with ID: " . $generated_id;

?>