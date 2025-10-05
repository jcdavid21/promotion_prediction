<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Evaluation</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .rating-badge {
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
        }

        .criteria-card {
            transition: all 0.3s;
        }

        .criteria-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #0d6efd;
        }

        .performance-indicator {
            height: 10px;
            border-radius: 5px;
        }

        #ratingChart {
            max-height: 300px;
        }
    </style>
</head>

<body>
    <div class="container-fluid body-con">
        <?php require "sidebar.php"; ?>

        <div class="main-content">
            <div id="loadingSpinner" class="d-none text-center my-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div class="container-fluid py-4">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Employee Evaluation</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">HR</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Evaluation</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">Select Employee</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 position-relative">
                                    <label for="employeeSearch" class="form-label">Employee Name</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="employeeSearch"
                                        placeholder="Type to search employees..."
                                        autocomplete="off">
                                    <div id="searchResults" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1000; max-height: 300px; overflow-y: auto; display: none;"></div>
                                </div>
                                <div id="employeeInfo" class="mt-3 d-none">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0">

                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 id="empName" class="mb-1"></h5>
                                            <p class="mb-1" id="empPosition"></p>
                                            <p class="mb-1" id="empDepartment"></p>
                                            <p class="mb-1" id="empStatus"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">Evaluation Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mb-2 text-muted">Attendance</h6>
                                                <span id="attendanceRating" class="badge bg-primary rating-badge">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mb-2 text-muted">Discipline</h6>
                                                <span id="disciplineRating" class="badge bg-primary rating-badge">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mb-2 text-muted">Performance</h6>
                                                <span id="performanceRating" class="badge bg-primary rating-badge">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mb-2 text-muted">Overall</h6>
                                                <span id="overallRating" class="badge bg-success rating-badge">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <canvas id="ratingChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">Evaluation Details</h5>
                            </div>
                            <div class="card-body">
                                <ul class="nav nav-tabs mb-4" id="evaluationTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">Attendance</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="discipline-tab" data-bs-toggle="tab" data-bs-target="#discipline" type="button" role="tab">Discipline</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="performance-tab" data-bs-toggle="tab" data-bs-target="#performance" type="button" role="tab">Performance</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="inputs-tab" data-bs-toggle="tab" data-bs-target="#inputs" type="button" role="tab">Manager/PSA Inputs</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="evaluationTabsContent">
                                    <!-- Attendance Tab -->
                                    <div class="tab-pane fade show active" id="attendance" role="tabpanel">
                                        <form id="attendanceForm">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="tardinessInstances" class="form-label">Tardiness Instances</label>
                                                    <input type="number" class="form-control" id="tardinessInstances" name="tardiness_instances" min="0">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="tardyMinutes" class="form-label">Total Tardy Minutes</label>
                                                    <input type="number" class="form-control" id="tardyMinutes" name="tardy_minutes" min="0">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="absenteeism" class="form-label">Absenteeism (AB + HD)</label>
                                                    <input type="number" class="form-control" id="absenteeism" name="absenteeism" min="0">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="uabUhd" class="form-label">Unexcused Absences (UAB + UHD)</label>
                                                    <input type="number" class="form-control" id="uabUhd" name="uab_uhd" min="0">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="AB" class="form-label">AB (Absences)</label>
                                                    <input type="number" class="form-control" id="AB" name="AB" min="0">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="UAB" class="form-label">UAB (Unexcused Absences)</label>
                                                    <input type="number" class="form-control" id="UAB" name="UAB" min="0">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="HD" class="form-label">HD (Half Day Absences)</label>
                                                    <input type="number" class="form-control" id="HD" name="HD" min="0">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="UHD" class="form-label">UHD (Unexcused Half Day)</label>
                                                    <input type="number" class="form-control" id="UHD" name="UHD" min="0">
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Discipline Tab -->
                                    <div class="tab-pane fade" id="discipline" role="tabpanel">
                                        <form id="disciplineForm">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label for="minor" class="form-label">Minor Infractions</label>
                                                    <input type="number" class="form-control" id="minor" name="minor" min="0">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="grave" class="form-label">Grave Infractions</label>
                                                    <input type="number" class="form-control" id="grave" name="grave" min="0">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="suspension" class="form-label">Suspensions</label>
                                                    <input type="number" class="form-control" id="suspension" name="suspension" min="0">
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Performance Tab -->
                                    <div class="tab-pane fade" id="performance" role="tabpanel">
                                        <form id="performanceForm">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label for="administration" class="form-label">Administration</label>
                                                    <select class="form-select" id="administration" name="administration">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="knowledge_of_work" class="form-label">Knowledge of Work</label>
                                                    <select class="form-select" id="knowledge_of_work" name="knowledge_of_work">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="quality_of_work" class="form-label">Quality of Work</label>
                                                    <select class="form-select" id="quality_of_work" name="quality_of_work">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="communication" class="form-label">Communication</label>
                                                    <select class="form-select" id="communication" name="communication">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="team" class="form-label">Teamwork</label>
                                                    <select class="form-select" id="team" name="team">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="decision" class="form-label">Decision Making</label>
                                                    <select class="form-select" id="decision" name="decision">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="dependability" class="form-label">Dependability</label>
                                                    <select class="form-select" id="dependability" name="dependability">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="adaptability" class="form-label">Adaptability</label>
                                                    <select class="form-select" id="adaptability" name="adaptability">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="leadership" class="form-label">Leadership</label>
                                                    <select class="form-select" id="leadership" name="leadership">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="customer" class="form-label">Customer Service</label>
                                                    <select class="form-select" id="customer" name="customer">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="human_relations" class="form-label">Human Relations</label>
                                                    <select class="form-select" id="human_relations" name="human_relations">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="personal_appearance" class="form-label">Personal Appearance</label>
                                                    <select class="form-select" id="personal_appearance" name="personal_appearance">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="safety" class="form-label">Safety</label>
                                                    <select class="form-select" id="safety" name="safety">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="discipline" class="form-label">Discipline</label>
                                                    <select class="form-select" id="discipline" name="discipline">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="potential_growth" class="form-label">Potential Growth</label>
                                                    <select class="form-select" id="potential_growth" name="potential_growth">
                                                        <option value="1">1 - Needs Improvement</option>
                                                        <option value="2">2 - Developing</option>
                                                        <option value="3">3 - Meets Expectations</option>
                                                        <option value="4">4 - Exceeds Expectations</option>
                                                        <option value="5">5 - Outstanding</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="highlight" class="form-label">Highlights</label>
                                                    <textarea class="form-control" id="highlight" name="highlight" rows="3"></textarea>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="lowlight" class="form-label">Areas for Improvement</label>
                                                    <textarea class="form-control" id="lowlight" name="lowlight" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Inputs Tab -->
                                    <div class="tab-pane fade" id="inputs" role="tabpanel">
                                        <form id="inputsForm">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="performance_eval" class="form-label">Performance Evaluation Score</label>
                                                    <input type="number" class="form-control" id="performance_eval" name="performance_eval" min="1" max="100">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="manager_input" class="form-label">Manager Input (1-5)</label>
                                                    <input type="number" class="form-control" id="manager_input" name="manager_input" min="1" max="5" step="0.1">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="psa_input" class="form-label">PSA Input (1-5)</label>
                                                    <input type="number" class="form-control" id="psa_input" name="psa_input" min="1" max="5" step="0.1">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-secondary" id="calculateBtn">
                                        <i class="fas fa-calculator me-2"></i>Calculate Rating
                                    </button>
                                    <button type="button" class="btn btn-primary" id="saveBtn">
                                        <i class="fas fa-save me-2"></i>Save Evaluation
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        const ApiUrl = "http://localhost:8800";
        let ratingChart;
        let currentEmpId = null;
        let eval_id = null;
        let allEmployees = [];

        $(document).ready(function() {
            initRatingChart();
            loadEmployees();
            // Employee search functionality
            $('#employeeSearch').on('input', function() {
                const searchTerm = $(this).val().toLowerCase().trim();

                if (searchTerm.length === 0) {
                    $('#searchResults').hide().empty();
                    return;
                }

                // Filter employees based on search term
                const filteredEmployees = allEmployees.filter(emp =>
                    emp.emp_name.toLowerCase().includes(searchTerm)
                );

                // Display search results
                if (filteredEmployees.length > 0) {
                    let html = '';
                    filteredEmployees.forEach(emp => {
                        html += `
                <a href="#" class="list-group-item list-group-item-action employee-item" data-emp-id="${emp.emp_id}">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${emp.emp_name}</h6>
                    </div>
                    <small class="text-muted">ID: ${emp.emp_id}</small>
                </a>
            `;
                    });
                    $('#searchResults').html(html).show();
                } else {
                    $('#searchResults').html(`
            <div class="list-group-item text-muted">
                No employees found
            </div>
        `).show();
                }
            });

            // Handle employee selection from search results
            $(document).on('click', '.employee-item', function(e) {
                e.preventDefault();
                const empId = $(this).data('emp-id');
                const empName = $(this).find('h6').text();

                $('#employeeSearch').val(empName);
                $('#searchResults').hide().empty();

                currentEmpId = empId;
                loadEmployeeData(empId);
            });

            // Hide search results when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#employeeSearch, #searchResults').length) {
                    $('#searchResults').hide();
                }
            });

            // Calculate button click handler
            $('#calculateBtn').click(function() {
                if (!currentEmpId) {
                    alert('Please select an employee first');
                    return;
                }
                calculateRating();
            });

            // Save button click handler
            $('#saveBtn').click(function() {
                if (!currentEmpId) {
                    alert('Please select an employee first');
                    return;
                }
                saveEvaluation();
            });
        });

        // Initialize the radar chart
        function initRatingChart() {
            const ctx = document.getElementById('ratingChart').getContext('2d');

            ratingChart = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: ['Attendance', 'Discipline', 'Performance', 'Manager Input', 'PSA Input'],
                    datasets: [{
                        label: 'Evaluation Scores',
                        data: [0, 0, 0, 0, 0],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(54, 162, 235, 1)'
                    }]
                },
                options: {
                    scales: {
                        r: {
                            angleLines: {
                                display: true
                            },
                            suggestedMin: 0,
                            suggestedMax: 10
                        }
                    }
                }
            });
        }

        function loadEmployeeData(empId) {
            showLoading(true);
            $.ajax({
                url: `${ApiUrl}/api/evaluation/employee/${empId}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        eval_id = data.eval_id;
                        $('#employeeInfo').removeClass('d-none');
                        $('#empName').text(response.data.employee.emp_name || 'N/A');
                        $('#empPosition').text(`Position: ${response.data.employee.position_name || 'N/A'}`);
                        $('#empDepartment').text(`Department: ${response.data.employee.dept_name || 'N/A'}`);
                        $('#empStatus').text(`Status: ${response.data.employee.emp_status || 'N/A'}`);

                        // Populate form data
                        populateFormData(response.data);
                    } else {
                        alert('Failed to load employee data: ' + response.error);
                    }
                    showLoading(false);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading employee data:', error);
                    alert('Failed to load employee data. Please try again.');
                    showLoading(false);
                }
            });
        }

        // Load all employees for dropdown
        function loadEmployees() {
            showLoading(true);

            $.ajax({
                url: `${ApiUrl}/api/evaluation/employees`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        // Store employees in global variable for search
                        allEmployees = response.employees;
                    } else {
                        alert('Failed to load employees: ' + response.error);
                    }
                    showLoading(false);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading employees:', error);
                    alert('Failed to load employees. Please try again.');
                    showLoading(false);
                }
            });
        }

        // Show/hide loading spinner
        function showLoading(show) {
            if (show) {
                $('#loadingSpinner').removeClass('d-none');
            } else {
                $('#loadingSpinner').addClass('d-none');
            }
        }

        // Populate form with employee data
        function populateFormData(data) {
            // Populate attendance form
            $('#tardinessInstances').val(data.attendance.tardiness || 0);
            $('#tardyMinutes').val(data.attendance.tardy || 0);
            $('#absenteeism').val(data.attendance.comb_ab_hd || 0);
            $('#uabUhd').val(data.attendance.comb_uab_uhd || 0);
            $('#AB').val(data.attendance.AB || 0);
            $('#UAB').val(data.attendance.UAB || 0);
            $('#HD').val(data.attendance.HD || 0);
            $('#UHD').val(data.attendance.UHD || 0);

            // Populate discipline form
            $('#minor').val(data.discipline.minor || 0);
            $('#grave').val(data.discipline.grave || 0);
            $('#suspension').val(data.discipline.suspension || 0);

            // Populate performance form
            const metrics = [
                'administration', 'knowledge_of_work', 'quality_of_work',
                'communication', 'team', 'decision', 'dependability',
                'adaptability', 'leadership', 'customer', 'human_relations',
                'personal_appearance', 'safety', 'discipline', 'potential_growth'
            ];

            metrics.forEach(metric => {
                $(`#${metric}`).val(data.evaluation[metric] || 1);
            });

            $('#highlight').val(data.evaluation.highlight || '');
            $('#lowlight').val(data.evaluation.lowlight || '');

            // Populate inputs form
            $('#performance_eval').val(data.other_metrics.performance || 0);
            $('#manager_input').val(data.other_metrics.manager_input || 0);
            $('#psa_input').val(data.other_metrics.psa_input || 0);
        }

        // Calculate the evaluation rating
        function calculateRating() {
            const formData = collectFormData();
            showLoading(true);
            $.ajax({
                url: `${ApiUrl}/api/evaluation/calculate`,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                    if (response.success) {
                        $('#attendanceRating').text(response.results.attendance_rating.toFixed(2));
                        $('#disciplineRating').text(response.results.discipline_rating.toFixed(2));
                        $('#performanceRating').text(response.results.weighted_score.toFixed(2));
                        $('#overallRating').text(response.results.overall_rating);
                        // Update radar chart
                        ratingChart.data.datasets[0].data = [
                            response.results.attendance_rating,
                            response.results.discipline_rating,
                            response.results.weighted_score / 10,
                            formData.manager_input,
                            formData.psa_input
                        ];
                        ratingChart.update();
                    } else {
                        alert('Error calculating rating: ' + response.error);
                    }
                    showLoading(false);
                },
                error: function(xhr, status, error) {
                    console.error('Error calculating rating:', error);
                    alert('Failed to calculate rating');
                    showLoading(false);
                }
            });
        }

        // Save evaluation data
        function saveEvaluation() {
            const formData = collectFormData();
            formData.emp_id = currentEmpId;
            formData.eval_id = eval_id;
            console.log(formData);
            showLoading(true);

            $.ajax({
                url: `${ApiUrl}/api/evaluation/save`,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                    if (response.success) {
                        // Show appropriate message based on action
                        const action = response.action === 'created' ? 'created' : 'updated';
                        alert(`Evaluation ${action} successfully`);

                        // Reload the data to reflect changes
                        loadEmployeeData(currentEmpId);
                    } else {
                        alert('Failed to save evaluation: ' + response.error);
                    }
                    showLoading(false);
                },
                error: function(xhr, status, error) {
                    console.error('Error saving evaluation:', error);
                    alert('Failed to save evaluation');
                    showLoading(false);
                }
            });
        }

        // Collect all form data
        function collectFormData() {
            return {
                // Attendance form data
                tardiness_instances: parseInt($('#tardinessInstances').val()) || 0,
                tardy_minutes: parseInt($('#tardyMinutes').val()) || 0,
                absenteeism: parseInt($('#absenteeism').val()) || 0,
                uab_uhd: parseInt($('#uabUhd').val()) || 0,
                AB: parseInt($('#AB').val()) || 0,
                UAB: parseInt($('#UAB').val()) || 0,
                HD: parseInt($('#HD').val()) || 0,
                UHD: parseInt($('#UHD').val()) || 0,

                // Discipline form data
                minor: parseInt($('#minor').val()) || 0,
                grave: parseInt($('#grave').val()) || 0,
                suspension: parseInt($('#suspension').val()) || 0,

                // Performance form data
                administration: parseInt($('#administration').val()) || 1,
                knowledge_of_work: parseInt($('#knowledge_of_work').val()) || 1,
                quality_of_work: parseInt($('#quality_of_work').val()) || 1,
                communication: parseInt($('#communication').val()) || 1,
                team: parseInt($('#team').val()) || 1,
                decision: parseInt($('#decision').val()) || 1,
                dependability: parseInt($('#dependability').val()) || 1,
                adaptability: parseInt($('#adaptability').val()) || 1,
                leadership: parseInt($('#leadership').val()) || 1,
                customer: parseInt($('#customer').val()) || 1,
                human_relations: parseInt($('#human_relations').val()) || 1,
                personal_appearance: parseInt($('#personal_appearance').val()) || 1,
                safety: parseInt($('#safety').val()) || 1,
                discipline: parseInt($('#discipline').val()) || 1,
                potential_growth: parseInt($('#potential_growth').val()) || 1,
                highlight: $('#highlight').val() || null,
                lowlight: $('#lowlight').val() || null,

                // Inputs form data
                performance_eval: parseFloat($('#performance_eval').val()) || 0,
                manager_input: parseFloat($('#manager_input').val()) || 0,
                psa_input: parseFloat($('#psa_input').val()) || 0
            };
        }

        // Reset all forms to default values
        function resetForms() {
            // Hide employee info
            $('#employeeInfo').addClass('d-none');

            // Reset attendance form
            $('#tardinessInstances, #tardyMinutes, #absenteeism, #uabUhd, #AB, #UAB, #HD, #UHD').val(0);

            // Reset discipline form
            $('#minor, #grave, #suspension').val(0);

            // Reset performance form selects to default
            $('#administration, #knowledge_of_work, #quality_of_work, #communication, #team, #decision, #dependability, #adaptability, #leadership, #customer, #human_relations, #personal_appearance, #safety, #discipline, #potential_growth').val(1);

            // Reset text areas
            $('#highlight, #lowlight').val('');

            // Reset inputs form
            $('#performance_eval, #manager_input, #psa_input').val(0);

            // Reset rating badges
            $('#attendanceRating, #disciplineRating, #performanceRating, #overallRating').text('-');

            // Reset chart
            ratingChart.data.datasets[0].data = [0, 0, 0, 0, 0];
            ratingChart.update();
        }
    </script>
</body>

</html>