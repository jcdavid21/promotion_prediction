<?php
session_start();
include_once "../config.php";

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION["acc_id"])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
    exit;
}

$acc_id = $_SESSION["acc_id"];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'update_profile':
            updateProfile($conn, $acc_id);
            break;
        
        case 'update_account':
            updateAccount($conn, $acc_id);
            break;
        
        case 'change_password':
            changePassword($conn, $acc_id);
            break;
        
        default:
            throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function updateProfile($conn, $acc_id) {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($contact) || empty($address)) {
        throw new Exception('Please fill in all required fields.');
    }
    
    // Validate contact number (basic Philippine format)
    if (!preg_match('/^09\d{9}$/', $contact)) {
        throw new Exception('Please enter a valid Philippine mobile number (09XXXXXXXXX).');
    }
    
    // Check if account details exist
    $check_query = "SELECT id FROM tbl_account_details WHERE acc_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $acc_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing record
        $update_query = "UPDATE tbl_account_details SET 
                        first_name = ?, middle_name = ?, last_name = ?, contact = ?, address = ? 
                        WHERE acc_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sssssi", $first_name, $middle_name, $last_name, $contact, $address, $acc_id);
        
        if ($update_stmt->execute()) {
            // Update session variables
            $_SESSION["full_name"] = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
        } else {
            throw new Exception('Failed to update profile.');
        }
    } else {
        // Insert new record
        $insert_query = "INSERT INTO tbl_account_details (acc_id, first_name, middle_name, last_name, contact, address) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("isssss", $acc_id, $first_name, $middle_name, $last_name, $contact, $address);
        
        if ($insert_stmt->execute()) {
            // Update session variables
            $_SESSION["full_name"] = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
            echo json_encode(['success' => true, 'message' => 'Profile created successfully!']);
        } else {
            throw new Exception('Failed to create profile.');
        }
    }
}

function updateAccount($conn, $acc_id) {
    $username = trim($_POST['username'] ?? '');
    $position_id = (int)($_POST['position_id'] ?? 0);
    
    // Validate required fields
    if (empty($username) || $position_id <= 0) {
        throw new Exception('Please fill in all required fields.');
    }
    
    // Validate username format
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        throw new Exception('Username must be 3-20 characters long and contain only letters, numbers, and underscores.');
    }
    
    // Check if username is already taken by another user
    $check_username = "SELECT acc_id FROM tbl_account WHERE username = ? AND acc_id != ?";
    $check_stmt = $conn->prepare($check_username);
    $check_stmt->bind_param("si", $username, $acc_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        throw new Exception('Username is already taken. Please choose a different username.');
    }
    
    // Check if position exists
    $check_position = "SELECT position_name FROM tbl_positions WHERE position_id = ?";
    $pos_stmt = $conn->prepare($check_position);
    $pos_stmt->bind_param("i", $position_id);
    $pos_stmt->execute();
    $pos_result = $pos_stmt->get_result();
    
    if ($pos_result->num_rows == 0) {
        throw new Exception('Invalid position selected.');
    }
    
    $position_data = $pos_result->fetch_assoc();
    
    // Update account
    $update_query = "UPDATE tbl_account SET username = ?, position_id = ? WHERE acc_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sii", $username, $position_id, $acc_id);
    
    if ($update_stmt->execute()) {
        // Update session variables
        $_SESSION["position_id"] = $position_id;
        $_SESSION["position"] = $position_data['position_name'];
        echo json_encode(['success' => true, 'message' => 'Account settings updated successfully!']);
    } else {
        throw new Exception('Failed to update account settings.');
    }
}

function changePassword($conn, $acc_id) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate required fields
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        throw new Exception('Please fill in all password fields.');
    }
    
    // Check if new passwords match
    if ($new_password !== $confirm_password) {
        throw new Exception('New passwords do not match.');
    }
    
    // Validate password strength
    if (strlen($new_password) < 6) {
        throw new Exception('New password must be at least 6 characters long.');
    }
    
    // Get current password hash
    $get_password = "SELECT password FROM tbl_account WHERE acc_id = ?";
    $get_stmt = $conn->prepare($get_password);
    $get_stmt->bind_param("i", $acc_id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    
    if ($result->num_rows == 0) {
        throw new Exception('Account not found.');
    }
    
    $user_data = $result->fetch_assoc();
    
    // Verify current password
    if (!password_verify($current_password, $user_data['password'])) {
        throw new Exception('Current password is incorrect.');
    }
    
    // Check if new password is different from current
    if (password_verify($new_password, $user_data['password'])) {
        throw new Exception('New password must be different from current password.');
    }
    
    // Hash new password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $update_password = "UPDATE tbl_account SET password = ? WHERE acc_id = ?";
    $update_stmt = $conn->prepare($update_password);
    $update_stmt->bind_param("si", $new_password_hash, $acc_id);
    
    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
    } else {
        throw new Exception('Failed to change password.');
    }
}
?>