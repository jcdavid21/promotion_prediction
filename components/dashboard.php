<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>i-PROMOTE Employee Evaluation Overview</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/general.css">
    <style>
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filter-group {
            margin-bottom: 10px;
        }
        .badge-performance {
            font-size: 0.9em;
            padding: 5px 8px;
        }
        .clickable-row {
            cursor: pointer;
        }
        .clickable-row:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <?php require "../backend/config.php"; ?>
    <?php 

        // Base query
        $get_data_query = "SELECT 
                td.emp_id, 
                td.emp_name, 
                td.emp_status, 
                ta.eval_id,
                ta.tardiness,
                ta.tardy,
                ta.comb_ab_hd,
                ta.comb_uab_uhd,
                ta.AB,
                ta.UAB,
                ta.HD,
                ta.UHD,
                tbd.minor,
                tbd.grave,
                tbd.suspension,
                tbo.performance,
                tbo.manager_input,
                tbo.psa_input,
                tbe.highlight,
                tbe.lowlight,
                tp.position_name, dp.dept_name
            FROM 
                tbl_employee_details td 
            INNER JOIN 
                tbl_eval_attendance ta ON ta.emp_id = td.emp_id 
            INNER JOIN 
                tbl_eval_discipline tbd ON tbd.eval_id = ta.eval_id 
            INNER JOIN 
                tbl_eval_others tbo ON tbo.eval_id = ta.eval_id
            INNER JOIN
                tbl_evaluation tbe ON tbe.emp_id = td.emp_id
            INNER JOIN tbl_positions tp on tp.position_id = td.position
            inner join tbl_department dp on dp.dept_id = td.department";

        $stmt = $conn->prepare($get_data_query);
        $stmt->execute();
        $result = $stmt->get_result();

        $employee_data = array();
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $employee_data[$row['emp_id']] = $row;
            }
        }
    ?>
    <div class="container-fluid body-con">
        <?php include 'sidebar.php'; ?>
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-chart-line me-2 text-primary"></i> i-PROMOTE Employee Evaluation Overview</h2>
                    <p class="text-muted">Comprehensive analysis of employee performance metrics and attendance data</p>
                </div>
                <div class="col-md-4">
                    <select id="employeeSelector" class="form-select">
                        <option value="0">Select Employee</option>
                        <?php foreach($employee_data as $emp_id => $employee): ?>
                            <option value="<?php echo $emp_id; ?>">
                                <?php echo htmlspecialchars($employee['emp_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Filters Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card filter-section">
                    <div class="card-header bg-light">
                        <i class="fas fa-filter me-2"></i> Filters
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 filter-group">
                                <label for="performanceFilter" class="form-label">Performance Range</label>
                                <select id="performanceFilter" class="form-select">
                                    <option value="all">All Performance</option>
                                    <option value="90-100">Excellent (90-100)</option>
                                    <option value="70-89">Good (70-89)</option>
                                    <option value="50-69">Fair (50-69)</option>
                                    <option value="0-49">Poor (0-49)</option>
                                </select>
                            </div>
                            <div class="col-md-3 filter-group">
                                <label for="absenceFilter" class="form-label">Absences</label>
                                <select id="absenceFilter" class="form-select">
                                    <option value="all">All Absences</option>
                                    <option value="none">No Absences</option>
                                    <option value="1-3">1-3 Absences</option>
                                    <option value="4+">4+ Absences</option>
                                </select>
                            </div>
                            <div class="col-md-3 filter-group">
                                <label for="tardinessFilter" class="form-label">Tardiness</label>
                                <select id="tardinessFilter" class="form-select">
                                    <option value="all">All Tardiness</option>
                                    <option value="none">No Tardiness</option>
                                    <option value="1-5">1-5 Incidents</option>
                                    <option value="6+">6+ Incidents</option>
                                </select>
                            </div>
                            <div class="col-md-3 filter-group">
                                <label for="statusFilter" class="form-label">Employment Status</label>
                                <select id="statusFilter" class="form-select">
                                    <option value="all">All Statuses</option>
                                    <option value="REGULAR">Regular</option>
                                    <option value="PROBATIONARY">Probationary</option>
                                    <option value="CONTRACTUAL">Contractual</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12 text-end">
                                <button id="resetFilters" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-undo me-1"></i> Reset Filters
                                </button>
                                <button id="applyFilters" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i> Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar me-2"></i> Employee Performance Comparison
                    </div>
                    <div class="card-body">
                        <canvas id="performanceBarChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-table me-2"></i> Employee Evaluation Summary
                        </div>
                        <div>
                            <button id="exportCSV" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-file-csv me-1"></i> Export CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="employeesTable" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Performance</th>
                                        <th>Tardiness</th>
                                        <th>Absences</th>
                                        <th>Discipline</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($employee_data as $emp_id => $employee): ?>
                                    <tr data-emp-id="<?php echo $emp_id; ?>">
                                        <td><?php echo $emp_id; ?></td>
                                        <td><?php echo htmlspecialchars($employee['emp_name']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['emp_status']); ?></td>
                                        <td>
                                            <?php 
                                                
                                                $badgeClass = 'bg-secondary';
                                                if ($employee['performance'] >= 90) $badgeClass = 'bg-success';
                                                elseif ($employee['performance'] >= 70) $badgeClass = 'bg-primary';
                                                elseif ($employee['performance'] >= 50) $badgeClass = 'bg-warning';
                                                elseif ($employee['performance'] > 0) $badgeClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?> badge-performance">
                                                <?php echo $employee['performance'] ?? 'N/A'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $employee['tardiness'] ?? '0'; ?></td>
                                        <td>
                                            <?php echo ($employee['AB'] ?? '0') . ' / ' . ($employee['UAB'] ?? '0'); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">Minor: <?php echo $employee['minor'] ?? '0'; ?></span>
                                            <span class="badge bg-danger">Grave: <?php echo $employee['grave'] ?? '0'; ?></span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-employee" data-emp-id="<?php echo $emp_id; ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content empDiv">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">Employee Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-id-card me-2"></i> Basic Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Employee ID:</th>
                                    <td id="modal-emp-id">--</td>
                                </tr>
                                <tr>
                                    <th>Name:</th>
                                    <td id="modal-emp-name">--</td>
                                </tr>
                                <tr>
                                    <th>Position:</th>
                                    <td id="modal-emp-position">--</td>
                                </tr>
                                <tr>
                                    <th>Department</th>
                                    <td id="modal-emp-department">--</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td id="modal-emp-status">--</td>
                                </tr>
                            </table>
                            
                            <h6 class="mt-4"><i class="fas fa-star me-2"></i> Performance</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Score:</th>
                                    <td id="modal-performance">--</td>
                                </tr>
                                <tr>
                                    <th>Manager Input:</th>
                                    <td id="modal-manager-input">--</td>
                                </tr>
                                <tr>
                                    <th>PSA Input:</th>
                                    <td id="modal-psa-input">--</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-calendar-check me-2"></i> Attendance</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Tardiness:</th>
                                    <td id="modal-tardiness">--</td>
                                </tr>
                                <tr>
                                    <th>Tardy Count:</th>
                                    <td id="modal-tardy-count">--</td>
                                </tr>
                                <tr>
                                    <th>Absences (AB):</th>
                                    <td id="modal-absences">--</td>
                                </tr>
                                <tr>
                                    <th>Unexcused Absences (UAB):</th>
                                    <td id="modal-unexcused-absences">--</td>
                                </tr>
                                <tr>
                                    <th>Half Days (HD):</th>
                                    <td id="modal-half-days">--</td>
                                </tr>
                            </table>
                            
                            <h6 class="mt-4"><i class="fas fa-gavel me-2"></i> Discipline</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Minor Infractions:</th>
                                    <td id="modal-minor">--</td>
                                </tr>
                                <tr>
                                    <th>Grave Infractions:</th>
                                    <td id="modal-grave">--</td>
                                </tr>
                                <tr>
                                    <th>Suspensions:</th>
                                    <td id="modal-suspension">--</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <i class="fas fa-chart-bar me-2"></i> Highlights & Lowlights
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-success"><i class="fas fa-arrow-up me-2"></i> Highlights</h6>
                                            <ul id="modal-highlights">
                                                <li>--</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-danger"><i class="fas fa-arrow-down me-2"></i> Lowlights</h6>
                                            <ul id="modal-lowlights">
                                                <li>--</li>
                                            </ul>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    
    <script>
        const employeeData = <?php echo json_encode($employee_data); ?>;
        let performanceBarChart, radarChart;
        let employeesTable;

        function initializeDataTable() {
            employeesTable = $('#employeesTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                dom: '<"top"lf>rt<"bottom"ip>',
                columnDefs: [
                    { targets: [7], orderable: false, searchable: false }
                ],
                createdRow: function(row, data, dataIndex) {
                    // Store employee ID on the row for easy access
                    const empId = $(row).find('button').data('emp-id');
                    $(row).attr('data-emp-id', empId);
                }
            });

            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    const row = employeesTable.row(dataIndex).node();
                    const empId = $(row).data('emp-id');
                    const employee = employeeData[empId];
                    
                    if (!employee) return false;
                    
                    const performanceFilter = $('#performanceFilter').val();
                    const absenceFilter = $('#absenceFilter').val();
                    const tardinessFilter = $('#tardinessFilter').val();
                    const statusFilter = $('#statusFilter').val();
                    if (performanceFilter !== 'all') {
                        const performance = parseFloat(employee.performance) || 0;
                        const [min, max] = performanceFilter.split('-').map(Number);
                        
                        if (max === undefined) {
                            if (performance < min) return false;
                        } else {
                            if (performance < min || performance > max) return false;
                        }
                    }
                    
                    if (absenceFilter !== 'all') {
                        const absences = parseInt(employee.AB) || 0;
                        if (absenceFilter === 'none' && absences > 0) return false;
                        if (absenceFilter === '1-3' && (absences < 1 || absences > 3)) return false;
                        if (absenceFilter === '4+' && absences < 4) return false;
                    }
                    
                    if (tardinessFilter !== 'all') {
                        const tardiness = parseInt(employee.tardiness) || 0;
                        if (tardinessFilter === 'none' && tardiness > 0) return false;
                        if (tardinessFilter === '1-5' && (tardiness < 1 || tardiness > 5)) return false;
                        if (tardinessFilter === '6+' && tardiness < 6) return false;
                    }
                    
                    if (statusFilter !== 'all' && employee.emp_status !== statusFilter) {
                        return false;
                    }
                    
                    return true;
                }
            );
        }

        function getFilteredEmployees() {
                const performanceFilter = $('#performanceFilter').val();
                const absenceFilter = $('#absenceFilter').val();
                const tardinessFilter = $('#tardinessFilter').val();
                const statusFilter = $('#statusFilter').val();
                
                return Object.values(employeeData).filter(employee => {
                    if (performanceFilter !== 'all') {
                        const performance = parseFloat(employee.performance) || 0;
                        const [min, max] = performanceFilter.split('-').map(Number);
                        
                        if (max === undefined) {
                            if (performance < min) return false;
                        } else {
                            if (performance < min || performance > max) return false;
                        }
                    }
                    
                    if (absenceFilter !== 'all') {
                        const absences = parseInt(employee.AB) || 0;
                        if (absenceFilter === 'none' && absences > 0) return false;
                        if (absenceFilter === '1-3' && (absences < 1 || absences > 3)) return false;
                        if (absenceFilter === '4+' && absences < 4) return false;
                    }
                    
                    if (tardinessFilter !== 'all') {
                        const tardiness = parseInt(employee.tardiness) || 0;
                        if (tardinessFilter === 'none' && tardiness > 0) return false;
                        if (tardinessFilter === '1-5' && (tardiness < 1 || tardiness > 5)) return false;
                        if (tardinessFilter === '6+' && tardiness < 6) return false;
                    }
                    
                    if (statusFilter !== 'all' && employee.emp_status !== statusFilter) {
                        return false;
                    }
                    
                    return true;
                });
            }

            function initializePerformanceBarChart(filteredEmployees = null) {
                const ctx = document.getElementById('performanceBarChart').getContext('2d');
                
                if (performanceBarChart) {
                    performanceBarChart.destroy();
                }
                
                
                const employees = filteredEmployees || Object.values(employeeData);
                const sortedEmployees = employees.sort((a, b) => (b.performance || 0) - (a.performance || 0));
                const labels = sortedEmployees.map(emp => emp.emp_name || `Employee #${emp.emp_id}`);
                const data = sortedEmployees.map(emp => emp.performance || 0);
                
                // Color bars based on performance score
                const backgroundColors = data.map(score => {
                    if (score >= 90) return 'rgba(40, 167, 69, 0.7)'; // Green
                    if (score >= 70) return 'rgba(23, 162, 184, 0.7)'; // Blue
                    if (score >= 50) return 'rgba(255, 193, 7, 0.7)'; // Yellow
                    return 'rgba(220, 53, 69, 0.7)'; // Red
                });
                
                performanceBarChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Performance Score',
                            data: data,
                            backgroundColor: backgroundColors,
                            borderColor: backgroundColors.map(color => color.replace('0.7', '1')),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Performance Score'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Employees'
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    afterLabel: function(context) {
                                        const empId = Object.values(employeeData)
                                            .find(emp => emp.emp_name === context.label || `Employee #${emp.emp_id}` === context.label).emp_id;
                                        const employee = employeeData[empId];
                                        let tooltip = '';
                                        
                                        tooltip += `\nStatus: ${employee.emp_status || 'N/A'}`;
                                        tooltip += `\nTardiness: ${employee.tardiness || '0'}`;
                                        tooltip += `\nAbsences: ${employee.AB || '0'} (${employee.UAB || '0'} unexcused)`;
                                        
                                        return tooltip;
                                    }
                                }
                            },
                            legend: {
                                display: false
                            }
                        },
                        onClick: (event, elements) => {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const label = performanceBarChart.data.labels[index];
                                const empId = Object.values(employeeData)
                                    .find(emp => emp.emp_name === label || `Employee #${emp.emp_id}` === label).emp_id;
                                showEmployeeModal(empId);
                            }
                        }
                    }
                });
            }


        function showEmployeeModal(empId) {
            const employee = employeeData[empId];
            if (!employee) return;

            $('#modal-emp-id').text(employee.emp_id || 'N/A');
            $('#modal-emp-name').text(employee.emp_name || 'N/A');
            $('#modal-emp-status').text(employee.emp_status || 'N/A');
            $('#modal-emp-position').text(employee.position_name || 'N/A');
            $('#modal-emp-department').text(employee.dept_name || 'N/A');
            

            $('#modal-highlights').html(
                employee.highlight ? 
                employee.highlight.split('\n').map(highlight => `<li>${highlight}</li>`).join('') : 
                '<li>No highlights</li>'
            );
            $('#modal-lowlights').html(
                employee.lowlight ? 
                employee.lowlight.split('\n').map(lowlight => `<li>${lowlight}</li>`).join('') : 
                '<li>No lowlights</li>'
            );
            

            $('#modal-performance').html(
                employee.performance ? 
                `<span class="badge ${getPerformanceBadgeClass(employee.performance)}">
                    ${employee.performance}
                </span>` : 
                'N/A'
            );
            $('#modal-manager-input').text(employee.manager_input || 'No input');
            $('#modal-psa-input').text(employee.psa_input || 'No input');
            

            $('#modal-tardiness').text(employee.tardiness !== undefined ? employee.tardiness : 'N/A');
            $('#modal-tardy-count').text(employee.tardy !== undefined ? employee.tardy : 'N/A');
            $('#modal-absences').text(employee.AB !== undefined ? employee.AB : 'N/A');
            $('#modal-unexcused-absences').text(employee.UAB !== undefined ? employee.UAB : 'N/A');
            $('#modal-half-days').text(employee.HD !== undefined ? employee.HD : 'N/A');
            

            $('#modal-minor').text(employee.minor !== undefined ? employee.minor : 'N/A');
            $('#modal-grave').text(employee.grave !== undefined ? employee.grave : 'N/A');
            $('#modal-suspension').text(employee.suspension !== undefined ? employee.suspension : 'N/A');
            

            const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
            modal.show();
        }

        function getPerformanceBadgeClass(score) {
            if (score >= 90) return 'bg-success';
            if (score >= 70) return 'bg-primary';
            if (score >= 50) return 'bg-warning';
            if (score > 0) return 'bg-danger';
            return 'bg-secondary';
        }

        // Export to CSV
        function exportToCSV() {
            let csv = 'Employee ID,Name,Status,Performance,Tardiness,Absences,Unexcused Absences,Minor Infractions,Grave Infractions\n';
            
            Object.values(employeeData).forEach(employee => {
                csv += `"${employee.emp_id}","${employee.emp_name}","${employee.emp_status}",`;
                csv += `${employee.performance || ''},${employee.tardiness || ''},`;
                csv += `${employee.AB || ''},${employee.UAB || ''},`;
                csv += `${employee.minor || ''},${employee.grave || ''}\n`;
            });
            
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            saveAs(blob, 'employee_evaluation.csv');
        }

        $(document).ready(function() {
            initializeDataTable();
            initializePerformanceBarChart();
            
            $('#applyFilters').click(function() {
                employeesTable.draw();
                $('#performanceBarChart').fadeOut(200, function() {
                    initializePerformanceBarChart(filteredEmployees);
                    $(this).fadeIn(200);
                });
                const filteredEmployees = getFilteredEmployees();
                initializePerformanceBarChart(filteredEmployees);
            });
            
            $('#resetFilters').click(function() {
                $('#performanceFilter, #absenceFilter, #tardinessFilter, #statusFilter').val('all');
                employeesTable.draw();
                initializePerformanceBarChart(); 
            });

            $('#exportCSV').click(exportToCSV);
            
            $(document).on('click', '.view-employee', function() {
                const empId = $(this).data('emp-id');
                showEmployeeModal(empId);
            });
            
            $('#employeesTable tbody').on('click', 'tr', function(e) {
                // Don't trigger if clicking on a button or link
                if (!$(e.target).is('button, a, input, .no-click')) {
                    const empId = $(this).data('emp-id') || $(this).find('button').data('emp-id');
                    if (empId) showEmployeeModal(empId);
                }
            });
            
            const firstEmployeeId = Object.keys(employeeData)[0];
            if (firstEmployeeId) {
                $('#employeeSelector').val(firstEmployeeId);
            }
        });
    </script>
</body>
</html>