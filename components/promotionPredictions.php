<?php  if(session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion Predictions</title>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

    <!-- Plotly -->
    <script src="https://cdn.plot.ly/plotly-2.18.2.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/prediction.css">
    <link rel="stylesheet" href="../css/general.css">

    <style>

    </style>
</head>

<body>
    <div class="container-fluid body-con">
        <?php require "sidebar.php"; ?>

        <div class="main-content p-4 pb-0">

            <!-- Alert container -->
            <div id="alert-container" class="mt-3"></div>

            <!-- Loading indicator -->
            <div id="loadingIndicator" class="text-center my-5 py-5" style="display: none;">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mt-3 text-muted">Loading promotion analysis...</h5>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">Promotion Analysis Dashboard</h2>
                        <div class="d-flex">
                            <div class="me-3">
                                <select id="promotion-filter" class="form-select">
                                    <option value="all">All Employees</option>
                                    <option value="promoted">With Promotion History</option>
                                    <option value="not-promoted">Without Promotion History</option>
                                    <option value="recently-promoted">Recently Promoted (Last 12 months)</option>
                                    <option value="frequent-promotions">Frequent Promotions (3+)</option>
                                </select>
                            </div>
                            <button id="refresh-btn" class="btn btn-primary">
                                <i class="fas fa-sync-alt me-2"></i> Refresh Data
                            </button>
                        </div>
                    </div>

                    <div class="promotion-card card mb-4">
                        <div class="card-header card-header-custom bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Employee Promotion Predictions</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="promotionTable" class="table table-hover" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Position</th>
                                            <th>Department</th>
                                            <th>Score</th>
                                            <th>Promotion Probability</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SHAP Explanation Modal -->
            <div class="modal fade" id="shapModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fas fa-chart-pie me-2"></i>Promotion Factors Analysis</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="employee-badge d-inline-block">
                                        <i class="fas fa-user me-2"></i><span id="employee-name">N/A</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="employee-badge d-inline-block">
                                        <i class="fas fa-briefcase me-2"></i><span id="employee-position">N/A</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="employee-badge d-inline-block">
                                        <i class="fas fa-building me-2"></i><span id="employee-department">N/A</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="shap-plot-container mb-4">
                                        <h6 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Key Promotion Factors</h6>
                                        <div id="shap-plot" style="height: 400px;"></div>
                                    </div>

                                    <!-- Add Historical Promotion Section -->
                                    <div class="historical-section">
                                        <div class="historical-header">
                                            <h6><i class="fas fa-history me-2"></i>Promotion History</h6>
                                            <span class="badge bg-primary">Impact: High</span>
                                        </div>

                                        <div id="promotion-timeline" class="timeline">
                                            <!-- Timeline items will be added dynamically -->
                                        </div>

                                        <div class="model-impact-viz">
                                            <small>Historical Impact:</small>
                                            <div class="impact-bar">
                                                <div class="impact-fill" style="width: 75%"></div>
                                            </div>
                                            <span class="impact-value">75%</span>
                                        </div>

                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Employees with past promotions are <strong>3.2x</strong> more likely to be promoted again.
                                        </div>
                                    </div>
                                </div>

                                <div class="detail-card discipline card mb-3">
                                    <div class="card-header bg-transparent border-0">
                                        <h6 class="mb-0"><i class="fas fa-gavel me-2 text-warning"></i>Discipline</h6>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="mb-2">
                                            <small class="text-muted">Score</small>
                                            <div class="d-flex align-items-center">
                                                <div class="progress w-100 me-2" style="height: 10px;">
                                                    <div class="progress-bar bg-warning"
                                                        role="progressbar"
                                                        id="discipline-progress"
                                                        style="width: 0%">
                                                    </div>
                                                </div>
                                                <span id="discipline-score" class="fw-bold">0/10</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Minor Offenses</small>
                                                <p class="mb-0 fw-bold" id="minor-offenses">0</p>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Grave Offenses</small>
                                                <p class="mb-0 fw-bold" id="grave-offenses">0</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="detail-card performance card">
                                    <div class="card-header bg-transparent border-0">
                                        <h6 class="mb-0"><i class="fas fa-star me-2 text-info"></i>Performance</h6>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="mb-2">
                                            <small class="text-muted">Score</small>
                                            <div class="d-flex align-items-center">
                                                <div class="progress w-100 me-2" style="height: 10px;">
                                                    <div class="progress-bar bg-info"
                                                        role="progressbar"
                                                        id="performance-progress"
                                                        style="width: 0%">
                                                    </div>
                                                </div>
                                                <span id="performance-score" class="fw-bold">0/10</span>
                                            </div>
                                        </div>
                                        <div>
                                            <small class="text-muted">Total Evaluation</small>
                                            <p class="mb-0 fw-bold" id="performance-total">0/70</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>


    </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <!-- Custom JavaScript -->
    <script src="../js/promotion-predictions.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>

</html>