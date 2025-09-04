<!DOCTYPE html>
<?php 
    session_start();
    include_once "../backend/config.php";

    if(!isset($_SESSION["acc_id"])){
        header("Location: ./logout.php");
    }
    
    $acc_id = $_SESSION["acc_id"];
    $position_id = $_SESSION["position_id"];

    // Fetch current user data
    $user_query = "SELECT a.*, ad.*, p.position_name 
                   FROM tbl_account a 
                   LEFT JOIN tbl_account_details ad ON a.acc_id = ad.acc_id 
                   LEFT JOIN tbl_positions p ON a.position_id = p.position_id 
                   WHERE a.acc_id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $acc_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_data = $user_result->fetch_assoc();

    // Fetch all positions for dropdown
    $positions_query = "SELECT * FROM tbl_positions ORDER BY position_name";
    $positions_result = $conn->query($positions_query);
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - i-PROMOTE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/sidebar.css">
    <style>
        :root {
            --dark-grey: #343a40;
            --medium-grey: #495057;
            --light-grey: #6c757d;
            --lighter-grey: #adb5bd;
            --lightest-grey: #dee2e6;
            --bg-grey: #f8f9fa;
            --white: #ffffff;
            --success-grey: #8f9da0;
            --danger-grey: #7d7d7d;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-grey);
            margin-left: 270px;
            transition: margin-left 0.3s;
        }

        .content-wrapper {
            padding: 30px;
            min-height: 100vh;
        }

        .page-header {
            background-color: var(--dark-grey);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 600;
        }

        .page-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        .settings-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            overflow: hidden;
            border: none;
        }

        .card-header-custom {
            background-color: var(--medium-grey);
            color: white;
            padding: 20px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
        }

        .card-body-custom {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-grey);
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid var(--lightest-grey);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--light-grey);
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
        }

        .btn-primary-custom, .btn-success-custom, .btn-danger-custom {
            background-color: var(--dark-grey) !important;
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            color: white !important;
            transition: all 0.3s;
        }

        .btn-primary-custom:hover, .btn-success-custom:hover, .btn-danger-custom:hover {
            background-color: var(--medium-grey);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
        }

        .alert-custom {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .row {
            margin: 0 -15px;
        }

        .col-md-6 {
            padding: 0 15px;
        }

        .input-group {
            position: relative;
        }

        .input-group .form-control {
            border-right: none;
        }

        .input-group-text {
            background: var(--bg-grey);
            border-left: none;
            border: 2px solid var(--lightest-grey);
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .password-toggle {
            cursor: pointer;
            color: var(--lighter-grey);
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: var(--light-grey);
        }

        @media (max-width: 768px) {
            body {
                margin-left: 0;
            }
            
            .content-wrapper {
                padding: 15px;
            }
            
            .page-header {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .card-body-custom {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include_once "./sidebar.php"; ?>
    
    <div class="content-wrapper">
        <div class="page-header">
            <h1>Profile Settings</h1>
            <p>Manage your personal information, security, and account preferences</p>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Profile Information Card -->
        <div class="settings-card">
            <div class="card-header-custom">
                <i class="fas fa-user me-2"></i>Personal Information
            </div>
            <div class="card-body-custom">
                <form id="profileForm">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="acc_id" value="<?php echo $acc_id; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name" 
                                       value="<?php echo htmlspecialchars($user_data['middle_name'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contact" name="contact" 
                                       value="<?php echo htmlspecialchars($user_data['contact'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-success-custom">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Settings Card -->
        <div class="settings-card">
            <div class="card-header-custom">
                <i class="fas fa-cog me-2"></i>Account Settings
            </div>
            <div class="card-body-custom">
                <form id="accountForm">
                    <input type="hidden" name="action" value="update_account">
                    <input type="hidden" name="acc_id" value="<?php echo $acc_id; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="position_id" class="form-label">Position</label>
                                <select class="form-control" id="position_id" name="position_id" required>
                                    <?php while($position = $positions_result->fetch_assoc()): ?>
                                        <option value="<?php echo $position['position_id']; ?>" 
                                                <?php echo ($position['position_id'] == $user_data['position_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($position['position_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-sync me-2"></i>Update Account
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password Change Card -->
        <div class="settings-card">
            <div class="card-header-custom">
                <i class="fas fa-lock me-2"></i>Change Password
            </div>
            <div class="card-body-custom">
                <form id="passwordForm">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="acc_id" value="<?php echo $acc_id; ?>">
                    
                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <span class="input-group-text">
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('current_password')"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <span class="input-group-text">
                                        <i class="fas fa-eye password-toggle" onclick="togglePassword('new_password')"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <span class="input-group-text">
                                        <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password')"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-danger-custom">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-custom alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('#alertContainer').html(alertHtml);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                $('.alert').fadeOut();
            }, 5000);
        }

        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Profile form submission
        $('#profileForm').submit(function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '../backend/accounts/update_profile.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        setTimeout(()=>{
                            location.reload();
                        }, 2000);
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('An error occurred while updating profile.', 'danger');
                }
            });
        });

        // Account form submission
        $('#accountForm').submit(function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '../backend/accounts/update_profile.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        window.scrollTo(0, 0);
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('An error occurred while updating account.', 'danger');
                }
            });
        });

        // Password form submission
        $('#passwordForm').submit(function(e) {
            e.preventDefault();
            
            const newPassword = $('#new_password').val();
            const confirmPassword = $('#confirm_password').val();
            
            if (newPassword !== confirmPassword) {
                showAlert('New passwords do not match!', 'danger');
                return;
            }
            
            if (newPassword.length < 6) {
                showAlert('Password must be at least 6 characters long!', 'danger');
                return;
            }
            
            $.ajax({
                url: '../backend/accounts/update_profile.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        $('#passwordForm')[0].reset();
                        window.scrollTo(0, 0);
                        setTimeout(()=>{
                            location.reload();
                        }, 2000);
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('An error occurred while changing password.', 'danger');
                }
            });
        });
    </script>
</body>
</html>