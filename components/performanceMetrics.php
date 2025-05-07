<?php  if(session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Metrics</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/metrics.css">
    <link rel="stylesheet" href="../css/general.css">
</head>
<body>
    <div class="container-fluid body-con">
        <?php require "sidebar.php"; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
                <h1 class="mb-4">Performance Metrics Dashboard</h1>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="employeeSearch" class="form-control" placeholder="Search employees...">
                            <button class="btn btn-primary" id="searchButton">Search</button>
                            <button class="btn btn-outline-secondary" id="clearSearch">Clear</button>
                        </div>
                    </div>
                </div>
                
                
                <!-- Employee Selection -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <label for="employeeSelect" class="form-label">Select Employee</label>
                                <select class="form-select" id="employeeSelect">

                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end align-items-center h-100">
                                    <span class="badge rounded-pill 
                                        ">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                

                <!-- Top 5 Employees -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">Employees With Good Metrics</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="topEmployeesTable">
                                        <thead>
                                            <tr>
                                                <th>Rank</th>
                                                <th>Employee</th>
                                                <th>Performance</th>
                                                <th>Attendance</th>
                                                <th>Discipline</th>
                                                <th>Manager Rating</th>
                                                <th>Overall</th>
                                            </tr>
                                        </thead>
                                        <tbody id="topEmployeesBody">
                                            <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Performance Overview -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-3">Overall Performance</h6>
                                <div class="position-relative" style="height: 120px;">
                                    <canvas id="performanceGauge"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <h2 class="mb-0" id="performanceScore">0</h2>
                                        <small class="text-muted">out of 100</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-3">Attendance Score</h6>
                                <div class="position-relative" style="height: 120px;">
                                    <canvas id="attendanceGauge"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <h2 class="mb-0" id="attendanceScore">0</h2>
                                        <small class="text-muted">out of 100</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-3">Discipline Score</h6>
                                <div class="position-relative" style="height: 120px;">
                                    <canvas id="disciplineGauge"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <h2 class="mb-0" id="disciplineScore">0</h2>
                                        <small class="text-muted">out of 100</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-3">Manager Rating</h6>
                                <div class="position-relative" style="height: 120px;">
                                    <canvas id="managerGauge"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <h2 class="mb-0" id="managerScore">0</h2>
                                        <small class="text-muted">out of 5</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Metrics -->
                <div class="row">
                    <!-- Attendance Metrics -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">Attendance Metrics</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="attendanceChart"></canvas>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-danger rounded-circle me-2" style="width: 15px; height: 15px;"></div>
                                            <span>Tardiness: <span id="tardinessValue">0</span> mins</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-warning rounded-circle me-2" style="width: 15px; height: 15px;"></div>
                                            <span>Tardy: <span id="tardyValue">0</span> times</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-info rounded-circle me-2" style="width: 15px; height: 15px;"></div>
                                            <span>Absences: <span id="absenceValue">0</span> days</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-success rounded-circle me-2" style="width: 15px; height: 15px;"></div>
                                            <span>Half-days: <span id="halfdayValue">0</span> days</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Discipline Metrics -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-danger text-white">
                                <h5 class="card-title mb-0">Discipline Metrics</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="disciplineChart"></canvas>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-warning rounded-circle me-2" style="width: 15px; height: 15px;"></div>
                                            <span>Minor Offenses: <span id="minorValue">0</span></span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-danger rounded-circle me-2" style="width: 15px; height: 15px;"></div>
                                            <span>Grave Offenses: <span id="graveValue">0</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Skills Assessment -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">Skills Assessment</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Administration</label>
                                    <div class="progress">
                                        <div class="progress-bar" id="adminProgress" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Knowledge of Work</label>
                                    <div class="progress">
                                        <div class="progress-bar" id="knowledgeProgress" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Quality of Work</label>
                                    <div class="progress">
                                        <div class="progress-bar" id="qualityProgress" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Communication</label>
                                    <div class="progress">
                                        <div class="progress-bar" id="communicationProgress" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Teamwork</label>
                                    <div class="progress">
                                        <div class="progress-bar" id="teamProgress" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Decision Making</label>
                                    <div class="progress">
                                        <div class="progress-bar" id="decisionProgress" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Dependability</label>
                                    <div class="progress">
                                        <div class="progress-bar" id="dependabilityProgress" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Adaptability</label>
                                    <div class="progress">
                                        <div class="progress-bar" id="adaptabilityProgress" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Leadership</label>
                                    <div class="progress">
                                        <div class="progress-bar" id="leadershipProgress" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Customer Service</label>
                                    <div class="progress">
                                        <div class="progress-bar" id="customerProgress" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Highlights & Lowlights -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">Highlights</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text" id="highlightText">No highlights recorded</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="card-title mb-0">Lowlights</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text" id="lowlightText">No lowlights recorded</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../js/metrics.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const ApiURL = 'http://localhost:8800';
                const response = await fetch(`${ApiURL}/api/employee_data`, {
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const ratings = await fetch(`${ApiURL}/api/get_categories`, {
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    method: 'GET'
                })
                const all_ratings = await ratings.json();
                console.log(all_ratings);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const employeeData = await response.json();
                console.log('Employee data loaded:', employeeData);
                initializeDashboard(employeeData, all_ratings);
            } catch (error) {
                console.error('Error fetching employee data:', error);
                alert(`Error loading employee data: ${error.message}. Please ensure the backend server is running.`);
            }
        });
    </script>
</body>
</html>