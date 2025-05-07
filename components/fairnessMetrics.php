<?php  if(session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XGBoost Analytics - Fairness Metrics</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/general.css">
    <style>
        .fairness-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        .fairness-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .demographic-chip {
            background-color: #e9ecef;
            border-radius: 16px;
            padding: 2px 10px;
            margin-right: 8px;
            font-size: 0.8rem;
        }
        .metric-badge {
            font-size: 0.9rem;
            padding: 5px 10px;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .disparity-table th {
            position: sticky;
            top: 0;
            background: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid body-con">
        <?php require "sidebar.php"; ?>
        
        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-scale-balanced me-2"></i>Fairness Metrics</h1>
                <p class="text-muted">Bias Analysis for Promotion Prediction Model</p>
            </div>

            <!-- Overview Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card fairness-card border-left-primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-primary">Overall Fairness Score</h6>
                                    <h2 id="overallFairnessScore">-</h2>
                                </div>
                                <div class="icon-circle bg-primary-light">
                                    <i class="fas fa-star text-primary"></i>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 8px;">
                                <div id="fairnessProgress" class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted">Higher is better (0-100 scale)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card fairness-card border-left-warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-warning">Highest Disparity</h6>
                                    <h2 id="highestDisparity">-</h2>
                                </div>
                                <div class="icon-circle bg-warning-light">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                </div>
                            </div>
                            <p class="mt-2 mb-1" id="disparityGroup">-</p>
                            <small class="text-muted">Group with largest prediction difference</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card fairness-card border-left-success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-success">Most Balanced</h6>
                                    <h2 id="mostBalanced">-</h2>
                                </div>
                                <div class="icon-circle bg-success-light">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                            </div>
                            <p class="mt-2 mb-1" id="balancedGroup">-</p>
                            <small class="text-muted">Most equitable demographic dimension</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demographic Parity Section -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-people-arrows me-2"></i>Demographic Parity Analysis
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs mb-3" id="demographicTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="gender-tab" data-bs-toggle="tab" data-bs-target="#gender" type="button" role="tab">Gender</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="age-tab" data-bs-toggle="tab" data-bs-target="#age" type="button" role="tab">Age Group</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="department-tab" data-bs-toggle="tab" data-bs-target="#department" type="button" role="tab">Department</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="demographicTabContent">
                                <div class="tab-pane fade show active" id="gender" role="tabpanel">
                                    <div class="chart-container">
                                        <canvas id="genderParityChart"></canvas>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6>Statistical Parity Difference</h6>
                                                    <h3 id="genderParityDiff">-</h3>
                                                    <small class="text-muted">Ideal: 0 (no difference)</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6>Disparate Impact Ratio</h6>
                                                    <h3 id="genderImpactRatio">-</h3>
                                                    <small class="text-muted">Ideal: 1 (no disparity)</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="age" role="tabpanel">
                                    <div class="chart-container">
                                        <canvas id="ageParityChart"></canvas>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="department" role="tabpanel">
                                    <div class="chart-container">
                                        <canvas id="deptParityChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-lightbulb me-2"></i>Fairness Indicators
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                These metrics help identify potential biases in the promotion prediction model.
                            </div>
                            <div class="mb-3">
                                <h6>Equal Opportunity</h6>
                                <span class="badge bg-success metric-badge" id="equalOpportunity">-</span>
                                <small class="text-muted d-block mt-1">True positive rates across groups</small>
                            </div>
                            <div class="mb-3">
                                <h6>Predictive Parity</h6>
                                <span class="badge bg-warning metric-badge" id="predictiveParity">-</span>
                                <small class="text-muted d-block mt-1">PPV across groups</small>
                            </div>
                            <div class="mb-3">
                                <h6>False Positive Balance</h6>
                                <span class="badge bg-danger metric-badge" id="falsePositiveBalance">-</span>
                                <small class="text-muted d-block mt-1">FPR across groups</small>
                            </div>
                            <hr>
                            <h6>Recommended Actions</h6>
                            <div id="recommendations">
                                <p class="text-muted">Analyzing data...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disparity Details Table -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-table me-2"></i>Detailed Disparity Metrics
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive disparity-table" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-hover">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Demographic</th>
                                            <th>Group</th>
                                            <th>Promotion Rate</th>
                                            <th>Size</th>
                                            <th>Parity Diff</th>
                                            <th>Impact Ratio</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="disparityTableBody">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mitigation Strategies -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-warning text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-hands-helping me-2"></i>Bias Mitigation Strategies
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5><i class="fas fa-adjust text-primary me-2"></i>Pre-processing</h5>
                                            <ul class="mt-3">
                                                <li>Reweight training samples</li>
                                                <li>Disparate impact remover</li>
                                                <li>Feature selection audit</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5><i class="fas fa-cogs text-info me-2"></i>In-processing</h5>
                                            <ul class="mt-3">
                                                <li>Add fairness constraints</li>
                                                <li>Adversarial debiasing</li>
                                                <li>Use fairness-aware algorithms</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5><i class="fas fa-filter text-success me-2"></i>Post-processing</h5>
                                            <ul class="mt-3">
                                                <li>Reject option classification</li>
                                                <li>Calibrated thresholds</li>
                                                <li>Equalized odds postprocessing</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="../js/fairnessMetrics.js">

    </script>
</body>
</html>