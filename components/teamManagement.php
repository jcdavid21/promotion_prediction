<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/teamManagement.css">
    <link rel="stylesheet" href="../css/general.css">
</head>
<body>
    <div class="container-fluid">
    <?php require "sidebar.php"; ?>
        <div class="row">
            <div class="col-md-3 bg-light p-4">
                <h4 class="mb-4">Team Management</h4>
                <div class="mb-4">
                    <button class="btn btn-primary w-100" id="addEmployeeBtn" data-bs-toggle="modal" data-bs-target="#employeeFormModal">
                        <i class="fas fa-plus me-2"></i>Add New Member
                    </button>
                </div>
                
                <h5 class="mt-4">Filters</h5>
                <div class="mb-3">
                    <label class="form-label">Department</label>
                    <select class="form-select" id="departmentFilter">
                        <option value="">All Departments</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="REGULAR">Regular</option>
                        <option value="PROBI">Probationary</option>
                        <option value="SEASONAL">Seasonal</option>
                    </select>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Organization Chart</h3>
                    <div class="d-flex gap-2">
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" class="form-control" placeholder="Search employees..." id="searchInput" autocomplete="off">
                            <button class="btn btn-outline-secondary" id="searchBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="orgChartContainer" class="org-chart">
                    <!-- Employee cards will be dynamically inserted here -->
                </div>

                <div id="paginationContainer" class="pagination-container"></div>
            </div>
        </div>
    </div>

    <!-- Employee Form Modal (Add/Edit) -->
    <div class="modal fade" id="employeeFormModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalTitle">Add New Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="employeeForm">
                        <input type="hidden" id="empId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="empName" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="empName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="empAge" class="form-label">Age</label>
                                <input type="number" class="form-control" id="empAge">
                            </div>
                            <div class="col-md-6">
                                <label for="empGender" class="form-label">Gender</label>
                                <select class="form-select" id="empGender">
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="empStatus" class="form-label">Employment Status</label>
                                <select class="form-select" id="empStatus">
                                    <option value="REGULAR">Regular</option>
                                    <option value="PROBI">Probationary</option>
                                    <option value="SEASONAL">Seasonal</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="empDepartment" class="form-label">Department</label>
                                <select class="form-select" id="empDepartment" required>
                                    <option value="">Select Department</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="empPosition" class="form-label">Position</label>
                                <select class="form-select" id="empPosition" required>
                                    <option value="">Select Position</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="startDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="startDate" required>
                            </div>
                            <div class="col-md-6">
                                <label for="regularizationDate" class="form-label">Regularization Date</label>
                                <input type="date" class="form-control" id="regularizationDate">
                            </div>
                            <div class="col-md-12">
                                <label for="empTenure" class="form-label">Tenure</label>
                                <input type="text" class="form-control" id="empTenure" readonly>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveEmployeeBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Detail Modal -->
    <div class="modal fade" id="employeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Employee Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h4 id="detailEmpName"></h4>
                            <div class="mb-3">
                                <span class="badge rounded-pill" id="detailEmpStatus"
                                style="color: rgb(90, 90, 90)"></span>
                            </div>
                            <div class="mb-2"><strong>Department:</strong> <span id="detailDept"></span></div>
                            <div class="mb-2"><strong>Position:</strong> <span id="detailPosition"></span></div>
                            <div class="mb-2"><strong>Tenure:</strong> <span id="detailTenure"></span></div>
                        </div>
                        <div class="col-md-8">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#details">Details</a>
                                </li>
                                <!-- <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#performance">Performance</a>
                                </li> -->
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#history">History</a>
                                </li>
                            </ul>

                            <div class="tab-content mt-3">
                                <div class="tab-pane active" id="details">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Age:</strong> <span id="detailAge"></span></p>
                                            <p><strong>Gender:</strong> <span id="detailGender"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Start Date:</strong> <span id="detailStartDate"></span></p>
                                            <p><strong>Regularization Date:</strong><br> <span id="detailRegDate"></span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="performance">
                                    <canvas id="performanceChart"></canvas>
                                </div>
                                <div class="tab-pane" id="history">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Previous Position</th>
                                                <th>New Position</th>
                                                <th>Department</th>
                                            </tr>
                                        </thead>
                                        <tbody id="promotionHistory">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="editEmployeeBtn">Edit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage">Are you sure you want to deactivate this employee?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmActionBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/teamManagement.js"></script>
</body>
</html>