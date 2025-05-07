<?php  if(session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XGBoost Analytics - Feature Importance</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/featureImportance.css">
    <link rel="stylesheet" href="../css/general.css">
    <style>
        .feature-highlight {
            background-color: rgba(255, 193, 7, 0.2);
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 0 4px 4px 0;
        }
        .top-feature {
            font-weight: bold;
            color: #dc3545;
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid body-con">
        <?php require "sidebar.php"; ?>
        
        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-project-diagram me-2"></i>XGBoost Analytics</h1>
                <p class="text-muted">Feature Importance and Model Insights</p>
            </div>

            <!-- Feature Importance Highlight Section -->
            <div class="feature-highlight">
                <h5><i class="fas fa-lightbulb me-2"></i>Key Insights</h5>
                <p>The XGBoost model identifies <span class="top-feature">performance_score</span>, <span class="top-feature">attendance_score</span>, 
                and <span class="top-feature">discipline_score</span> as the most influential factors in promotion predictions. 
                These features collectively account for <span id="topFeaturesPercentage">80%</span> of the model's decision-making process.</p>
            </div>

            <div class="row">
                <!-- Feature Importance Section -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar me-2"></i>Feature Importance
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="featureImportanceChart"></canvas>
                            </div>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                The chart above shows the relative importance of each feature in the XGBoost model's decision-making process. 
                                Higher values indicate greater influence on promotion predictions.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Model Metrics Section -->
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tachometer-alt me-2"></i>Model Performance
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="metric-card bg-light p-3 rounded text-center">
                                        <h6 class="metric-title">Accuracy</h6>
                                        <h3 class="metric-value" id="accuracyMetric">-</h3>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="metric-card bg-light p-3 rounded text-center">
                                        <h6 class="metric-title">Precision</h6>
                                        <h3 class="metric-value" id="precisionMetric">-</h3>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="metric-card bg-light p-3 rounded text-center">
                                        <h6 class="metric-title">Recall</h6>
                                        <h3 class="metric-value" id="recallMetric">-</h3>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="metric-card bg-light p-3 rounded text-center">
                                        <h6 class="metric-title">F1 Score</h6>
                                        <h3 class="metric-value" id="f1Metric">-</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-warning mt-2">
                                <small>
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Model trained on <span id="trainingDate">-</span> with <span id="sampleSize">-</span> employee records
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SHAP Explanation Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-brain me-2"></i>Individual Prediction Explanations
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="employeeSelect" class="form-label">Select Employee</label>
                                        <select class="form-select" id="employeeSelect">
                                            <option value="">Loading employees...</option>
                                        </select>
                                    </div>
                                    <div id="employeeDetails" class="mt-3 p-3 bg-light rounded">
                                        <h5 id="selectedEmployee">No employee selected</h5>
                                        <div class="row mt-3">
                                            <div class="col-6">
                                                <small class="text-muted">Department:</small>
                                                <p id="employeeDept">-</p>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Position:</small>
                                                <p id="employeePosition">-</p>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6">
                                                <small class="text-muted">Total Score:</small>
                                                <h4 id="employeeScore">-</h4>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Promotion Probability:</small>
                                                <h4 id="promotionProb">-</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="chart-container" style="height: 300px;">
                                        <canvas id="shapForcePlot"></canvas>
                                    </div>
                                    <div class="alert alert-secondary mt-3">
                                        <small>
                                            <i class="fas fa-question-circle me-1"></i>
                                            This visualization shows how each feature contributes to the final prediction for the selected employee. 
                                            Positive values (blue) increase promotion likelihood, while negative values (red) decrease it.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature Impact Summary -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-pie me-2"></i>Feature Impact Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Feature</th>
                                            <th>Description</th>
                                            <th>Average Impact</th>
                                            <th>Direction</th>
                                        </tr>
                                    </thead>
                                    <tbody id="featureImpactTable">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
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
    <script src="../js/featureImportance.js">
        
    </script>
</body>
</html>