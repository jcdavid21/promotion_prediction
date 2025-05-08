<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once "../backend/config.php";

// Initialize response
$response = array();

// FIXED: Check if user is logged in AND is an admin (position_id = 1)
if (!isset($_SESSION["acc_id"]) || !isset($_SESSION["position_id"]) || $_SESSION["position_id"] != 1) {
    // Redirect non-admins back with error
    $_SESSION['error_message'] = "You don't have permission to perform this action.";
    header("Location: teamManagement.php");
    exit;
}

// Process the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['pin']) && isset($_POST['emp_id'])) {
        $submitted_pin = trim($_POST['pin']); // Trim whitespace
        $emp_id = intval($_POST['emp_id']); // Ensure integer
        
        // Get the stored PIN for the current admin user
        $query = "SELECT pin_pass FROM tbl_pin WHERE acc_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $_SESSION["acc_id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stored_pin = trim($row["pin_pass"]); // Trim whitespace
            
            // Verify the PIN with strict comparison
            if ($submitted_pin === $stored_pin) {
                // PIN is correct, process the employee termination
                try {
                    // Start transaction
                    $conn->begin_transaction();
                    
                    // Delete the employee
                    $update_query = "UPDATE tbl_employee_details SET active_status = 2, end_date = NOW(), emp_status = 'INACTIVE' WHERE emp_id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("i", $emp_id);

                    
                    if ($update_stmt->execute()) {
                        // Commit transaction
                        $conn->commit();
                        // Successful termination
                        $_SESSION['success_message'] = "Employee terminated successfully.";
                    } else {
                        // Rollback on error
                        $conn->rollback();
                        // Failed to delete
                        $_SESSION['error_message'] = "Failed to terminate employee: " . $conn->error;
                    }
                } catch (Exception $e) {
                    $conn->rollback();
                    $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
                }
            } else {
                // Invalid PIN
                $_SESSION['error_message'] = "Invalid PIN. Please try again.";
            }
        } else {
            // No PIN found for this admin
            $_SESSION['error_message'] = "Could not verify admin credentials. Please contact system administrator.";
        }
    } else {
        // Missing required parameters
        $_SESSION['error_message'] = "Missing required information.";
    }
}

// Redirect back to team management page
header("Location: teamManagement.php");
exit;
?>