<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>i-PROMOTE Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../css/sidebar.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php
    session_start();
    if (!isset($_SESSION["acc_id"])) {
        header("Location: ../index.php");
    }

    // Get current page filename
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="../assets/logo.jpeg" alt="i-PROMOTE Logo">
                <h3>i-PROMOTE</h3>
            </div>
            <button class="toggle-btn" id="toggle-sidebar">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <div class="sidebar-footer">
            <div class="user-info">
                <div>
                    <div class="user-name">
                        <?php
                        if (!empty($_SESSION["full_name"])) {
                            echo $_SESSION["full_name"];
                        } else {
                            echo "User";
                        }
                        ?>
                    </div>
                    <div class="user-role">
                        <?php
                        if (!empty($_SESSION["position"])) {
                            echo $_SESSION["position"];
                        } else {
                            echo "Role";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="sidebar-content">
            <div class="menu-category">Dashboard</div>
            <a href="./dashboard.php" class="menu-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Overview</span>
            </a>

            <div class="menu-category">Talent Analytics</div>
            <a href="./performanceMetrics.php" class="menu-item <?php echo ($current_page == 'performanceMetrics.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Performance Metrics</span>
            </a>
            <a href="./promotionPredictions.php" class="menu-item <?php echo ($current_page == 'promotionPredictions.php') ? 'active' : ''; ?>">
                <i class="fas fa-arrow-trend-up"></i>
                <span>Promotion Predictions</span>
            </a>

            <div class="menu-category">XGBoost Analytics</div>
            <a href="./featureImportance.php" class="menu-item <?php echo ($current_page == 'featureImportance.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Feature Importance</span>
            </a>
            <a href="./fairnessMetrics.php" class="menu-item <?php echo ($current_page == 'fairnessMetrics.php') ? 'active' : ''; ?>">
                <i class="fas fa-check-square"></i>
                <span>Fairness Metrics</span>
            </a>

            <div class="menu-category">Management</div>
            <a href="./teamManagement.php" class="menu-item <?php echo ($current_page == 'teamManagement.php') ? 'active' : ''; ?>">
                <i class="fas fa-users-cog"></i>
                <span>Team Management</span>
            </a>
            <a href="./evaluation.php" class="menu-item <?php echo ($current_page == 'evaluation.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-cog"></i>
                <span>Evaluation</span>
            </a>
            <a href="./criteria.php" class="menu-item <?php echo ($current_page == 'criteria.php') ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Criteria</span>
            </a>

            <div class="menu-category">Settings</div>
            <!-- <a href="#" class="menu-item">
                <i class="fas fa-question-circle"></i>
                <span>Help & Support</span>
            </a> -->

            <a href="./logout.php" class="menu-item <?php echo ($current_page == 'logout.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Log out</span>
            </a>
        </div>
    </div>


    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar functionality
        document.getElementById('toggle-sidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed');

            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-chevron-left')) {
                icon.classList.replace('fa-chevron-left', 'fa-chevron-right');
            } else {
                icon.classList.replace('fa-chevron-right', 'fa-chevron-left');
            }
        });

        // Mobile menu toggle if you have a mobile toggle button
        // Uncomment and modify if you have a mobile menu button
        document.getElementById('mobile-toggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('mobile-active');
        });
    </script>

</body>

</html>