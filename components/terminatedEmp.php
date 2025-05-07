<?php
if (!isset($_SESSION["acc_id"])) {
    header("Location: ../index.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminated Employees - i-PROMOTE Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../css/sidebar.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .content-wrapper {
            padding: 20px;
            transition: margin-left 0.3s;
        }

        body.sidebar-collapsed .content-wrapper {
            margin-left: 70px;
        }

        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .pagination {
            justify-content: center;
            margin-top: 20px;
        }

        .search-filters {
            margin-bottom: 20px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-badge.terminated {
            background-color: #ffcccc;
            color: #d9534f;
        }

        .status-badge.pending-deletion {
            background-color: #fff3cd;
            color: #856404;
        }

        .action-buttons button {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        .auto-delete-notice {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #dc3545;
            margin-bottom: 20px;
        }

        .expired {
            background-color: #fff3cd;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding-top: 60px;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <?php  if(session_status() == PHP_SESSION_NONE) {
        session_start();
    } ?>

    <div class="content-wrapper">
        <div class="container-fluid">
            <h2 class="mb-4">Terminated Employees</h2>

            <div class="auto-delete-notice">
                <h5><i class="fas fa-exclamation-triangle text-danger me-2"></i>Auto-Deletion Policy</h5>
                <p class="mb-0">Employee records are automatically deleted 5 months after termination date. Records approaching deletion are highlighted in yellow.</p>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row search-filters mb-4">
                        <div class="col-md-3 mb-2">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by name or position">
                        </div>
                        <div class="col-md-3 mb-2">
                            <select id="departmentFilter" class="form-select">
                                <option value="">All Departments</option>
                                <!-- Will be populated from API -->
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <select id="deletionStatusFilter" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending">Pending Deletion</option>
                                <option value="recent">Recent Termination</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button id="resetFilters" class="btn btn-secondary me-2">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button id="refreshData" class="btn btn-primary">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Termination Date</th>
                                    <th>Reason</th>
                                    <th>Days Until Deletion</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="terminatedEmployeesTable">
                                <!-- Will be populated via JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <div id="pagination" class="pagination">
                        <!-- Will be populated via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Employee Modal -->
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewEmployeeModalLabel">Employee Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Personal Information</h6>
                            <p><strong>Name:</strong> <span id="modalEmpName"></span></p>
                            <p><strong>ID:</strong> <span id="modalEmpId"></span></p>
                            <p><strong>Age:</strong> <span id="modalAge"></span></p>
                            <p><strong>Gender:</strong> <span id="modalGender"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Employment Information</h6>
                            <p><strong>Department:</strong> <span id="modalDepartment"></span></p>
                            <p><strong>Position:</strong> <span id="modalPosition"></span></p>
                            <p><strong>Tenure:</strong> <span id="modalTenure"></span></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Dates</h6>
                            <p><strong>Start Date:</strong> <span id="modalStartDate"></span></p>
                            <p><strong>Regularization:</strong> <span id="modalRegularization"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Termination Details</h6>
                            <p><strong>End Date:</strong> <span id="modalEndDate"></span></p>
                            <p><strong>Exit Reason:</strong> <span id="modalExitReason"></span></p>
                            <p><strong>Deletion Date:</strong> <span id="modalDeletionDate"></span></p>
                        </div>
                    </div>
                    <div class="alert alert-warning" id="deletionWarning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This record is scheduled for automatic deletion on <span id="modalScheduledDeletion"></span>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="deleteEmployee">Delete Now</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to permanently delete the employee record for <strong id="deleteEmpName"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete Permanently</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentPage = 1;
        let totalPages = 1;
        let perPage = 10;
        let currentEmployeeId = null;
        
        // Format date for display
        function formatDate(dateString) {
            if (!dateString || dateString === '*NULL*' || dateString === 'NULL' || dateString === '') {
                return 'N/A';
            }
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }
        
        // Calculate days until deletion
        function calculateDaysUntilDeletion(endDate) {
            if (!endDate || endDate === '*NULL*' || endDate === 'NULL' || endDate === '') {
                return 'N/A';
            }
            
            const terminationDate = new Date(endDate);
            const deletionDate = new Date(terminationDate);
            deletionDate.setMonth(deletionDate.getMonth() + 5);
            
            const today = new Date();
            const diffTime = deletionDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            return diffDays;
        }
        
        // Load terminated employees data
        function loadTerminatedEmployees() {
            const search = $('#searchInput').val();
            const department = $('#departmentFilter').val();
            const deletionStatus = $('#deletionStatusFilter').val();
            
            $.ajax({
                url: 'http://localhost:8800/api/terminated',
                method: 'GET',
                data: {
                    page: currentPage,
                    per_page: perPage,
                    search: search,
                    department: department,
                    deletion_status: deletionStatus
                },
                success: function(response) {
                    const employees = response.employees;
                    totalPages = Math.ceil(response.total / perPage);
                    
                    let tableContent = '';
                    
                    if (employees.length === 0) {
                        tableContent = '<tr><td colspan="8" class="text-center">No terminated employees found</td></tr>';
                    } else {
                        employees.forEach(emp => {
                            const daysUntilDeletion = calculateDaysUntilDeletion(emp.end_date);
                            const rowClass = daysUntilDeletion <= 30 && daysUntilDeletion > 0 ? 'expired' : '';
                            
                            tableContent += `
                                <tr class="${rowClass}">
                                    <td>${emp.emp_id}</td>
                                    <td>${emp.emp_name}</td>
                                    <td>${emp.dept_name || 'N/A'}</td>
                                    <td>${emp.position_name || 'N/A'}</td>
                                    <td>${formatDate(emp.end_date)}</td>
                                    <td>${emp.exit_reason || 'N/A'}</td>
                                    <td>
                                        ${daysUntilDeletion > 0 ? 
                                          `<span class="badge ${daysUntilDeletion <= 30 ? 'bg-warning' : 'bg-info'}">${daysUntilDeletion} days</span>` : 
                                          `<span class="badge bg-danger">Overdue</span>`}
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-primary view-employee" data-id="${emp.emp_id}">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    
                    $('#terminatedEmployeesTable').html(tableContent);
                    updatePagination();
                    
                    // Add event listeners for view buttons
                    $('.view-employee').on('click', function() {
                        const empId = $(this).data('id');
                        viewEmployeeDetails(empId);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error loading terminated employees:', error);
                    $('#terminatedEmployeesTable').html(
                        '<tr><td colspan="8" class="text-center text-danger">Failed to load terminated employees</td></tr>'
                    );
                }
            });
        }
        
        // Load departments for filter
        function loadDepartments() {
            $.ajax({
                url: 'http://localhost:8800/api/departments',
                method: 'GET',
                success: function(response) {
                    let options = '<option value="">All Departments</option>';
                    response.departments.forEach(dept => {
                        options += `<option value="${dept.dept_id}">${dept.dept_name}</option>`;
                    });
                    $('#departmentFilter').html(options);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading departments:', error);
                }
            });
        }
        
        // Update pagination controls
        function updatePagination() {
            let paginationHtml = '';
            
            if (totalPages > 1) {
                paginationHtml += `
                    <ul class="pagination">
                        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                        </li>
                `;
                
                // Show up to 5 page numbers
                const startPage = Math.max(1, currentPage - 2);
                const endPage = Math.min(totalPages, startPage + 4);
                
                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                }
                
                paginationHtml += `
                        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                        </li>
                    </ul>
                `;
            }
            
            $('#pagination').html(paginationHtml);
            
            // Add event listeners for pagination
            $('.page-link').on('click', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && page !== currentPage && page > 0 && page <= totalPages) {
                    currentPage = page;
                    loadTerminatedEmployees();
                }
            });
        }
        
        // View employee details
        function viewEmployeeDetails(empId) {
            $.ajax({
                url: `http://localhost:8800/api/terminated/${empId}`,
                method: 'GET',
                success: function(response) {
                    const emp = response;
                    currentEmployeeId = emp.emp_id;
                    
                    // Fill modal with employee data
                    $('#modalEmpName').text(emp.emp_name);
                    $('#modalEmpId').text(emp.emp_id);
                    $('#modalAge').text(emp.age);
                    $('#modalGender').text(emp.gender);
                    $('#modalDepartment').text(emp.dept_name || 'N/A');
                    $('#modalPosition').text(emp.position_name || 'N/A');
                    $('#modalTenure').text(emp.tenure || 'N/A');
                    $('#modalStartDate').text(formatDate(emp.start_date));
                    $('#modalRegularization').text(formatDate(emp.regularization));
                    $('#modalEndDate').text(formatDate(emp.end_date));
                    $('#modalExitReason').text(emp.exit_reason || 'N/A');
                    
                    // Calculate deletion date and days remaining
                    if (emp.end_date) {
                        const endDate = new Date(emp.end_date);
                        const deletionDate = new Date(endDate);
                        deletionDate.setMonth(deletionDate.getMonth() + 5);
                        
                        $('#modalDeletionDate').text(formatDate(deletionDate));
                        $('#modalScheduledDeletion').text(formatDate(deletionDate));
                        
                        const daysUntilDeletion = calculateDaysUntilDeletion(emp.end_date);
                        if (daysUntilDeletion <= 30 && daysUntilDeletion > 0) {
                            $('#deletionWarning').removeClass('alert-warning').addClass('alert-danger').show();
                        } else if (daysUntilDeletion > 30) {
                            $('#deletionWarning').removeClass('alert-danger').addClass('alert-warning').show();
                        } else {
                            $('#deletionWarning').hide();
                        }
                    } else {
                        $('#modalDeletionDate').text('N/A');
                        $('#deletionWarning').hide();
                    }
                    
                    // Show the delete employee name in confirm modal
                    $('#deleteEmpName').text(emp.emp_name);
                    
                    // Open the modal
                    $('#viewEmployeeModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error loading employee details:', error);
                    alert('Failed to load employee details. Please try again.');
                }
            });
        }
        
        // Delete employee record
        function deleteEmployee(empId) {
            $.ajax({
                url: `http://localhost:8800/api/terminated/${empId}/delete`,
                method: 'DELETE',
                success: function(response) {
                    $('#confirmDeleteModal').modal('hide');
                    $('#viewEmployeeModal').modal('hide');
                    
                    alert('Employee record has been permanently deleted.');
                    loadTerminatedEmployees();
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting employee:', error);
                    alert('Failed to delete employee record. Please try again.');
                }
            });
        }
        
        // Retain employee record
        function retainEmployee(empId) {
            $.ajax({
                url: `http://localhost:8800/api/terminated/${empId}/retain`,
                method: 'PUT',
                success: function(response) {
                    $('#viewEmployeeModal').modal('hide');
                    
                    alert('Employee record has been marked for retention and will not be automatically deleted.');
                    loadTerminatedEmployees();
                },
                error: function(xhr, status, error) {
                    console.error('Error retaining employee:', error);
                    alert('Failed to retain employee record. Please try again.');
                }
            });
        }
        
        // Initialize
        $(document).ready(function() {
            loadTerminatedEmployees();
            loadDepartments();
            
            // Filter events
            $('#searchInput').on('keyup', function(e) {
                if (e.key === 'Enter') {
                    currentPage = 1;
                    loadTerminatedEmployees();
                }
            });
            
            $('#departmentFilter, #deletionStatusFilter').on('change', function() {
                currentPage = 1;
                loadTerminatedEmployees();
            });
            
            // Reset filters
            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#departmentFilter').val('');
                $('#deletionStatusFilter').val('');
                currentPage = 1;
                loadTerminatedEmployees();
            });
            
            // Refresh data
            $('#refreshData').on('click', function() {
                loadTerminatedEmployees();
            });
            
            // Delete button in modal
            $('#deleteEmployee').on('click', function() {
                $('#viewEmployeeModal').modal('hide');
                $('#confirmDeleteModal').modal('show');
            });
            
            // Confirm delete button
            $('#confirmDelete').on('click', function() {
                deleteEmployee(currentEmployeeId);
            });
            
            // Retain button
            $('#retainEmployee').on('click', function() {
                retainEmployee(currentEmployeeId);
            });
        });
    </script>
</body>

</html>