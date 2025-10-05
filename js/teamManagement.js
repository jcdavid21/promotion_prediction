let currentPage = 1;
const itemsPerPage = 10;
let totalEmployees = 0;
let currentEmployeeId = null;
const API_URL = 'http://localhost:8800';
let startDateChangeListener = null;
let userPositionId = null; // Store the user's position ID

// DOM Elements
const elements = {
    orgChartContainer: document.getElementById('orgChartContainer'),
    paginationContainer: document.getElementById('paginationContainer'),
    departmentFilter: document.getElementById('departmentFilter'),
    statusFilter: document.getElementById('statusFilter'),
    searchInput: document.getElementById('searchInput'),
    searchBtn: document.getElementById('searchBtn'),
    employeeForm: document.getElementById('employeeForm'),
    empId: document.getElementById('empId'),
    empName: document.getElementById('empName'),
    empAge: document.getElementById('empAge'),
    empGender: document.getElementById('empGender'),
    empStatus: document.getElementById('empStatus'),
    empTenure: document.getElementById('empTenure'),
    empDepartment: document.getElementById('empDepartment'),
    empPosition: document.getElementById('empPosition'),
    startDate: document.getElementById('startDate'),
    regularizationDate: document.getElementById('regularizationDate'),
    saveEmployeeBtn: document.getElementById('saveEmployeeBtn'),
    editEmployeeBtn: document.getElementById('editEmployeeBtn'),
    formModalTitle: document.getElementById('formModalTitle'),
    employeeFormModal: new bootstrap.Modal(document.getElementById('employeeFormModal')),
    employeeModal: new bootstrap.Modal(document.getElementById('employeeModal')),
    confirmModal: new bootstrap.Modal(document.getElementById('confirmModal')),
    confirmMessage: document.getElementById('confirmMessage'),
    confirmActionBtn: document.getElementById('confirmActionBtn'),
    pinVerificationModal: new bootstrap.Modal(document.getElementById('pinVerificationModal')),
    pinInput: document.getElementById('pinInput'),
    pinError: document.getElementById('pinError'),
    verifyPinBtn: document.getElementById('verifyPinBtn'),
    importDataModal: new bootstrap.Modal(document.getElementById('importDataModal')),
    csvFileInput: document.getElementById('csvFileInput'),
    uploadCsvBtn: document.getElementById('uploadCsvBtn'),
    recentlyUploadedSection: document.getElementById('recentlyUploadedSection'),
    recentlyUploadedCards: document.getElementById('recentlyUploadedCards'),
    recentUploadCount: document.getElementById('recentUploadCount'),
    clearRecentBtn: document.getElementById('clearRecentBtn'),
    csvPreviewSection: document.getElementById('csvPreviewSection'),
    csvPreviewTable: document.getElementById('csvPreviewTable'),
    totalRowsCount: document.getElementById('totalRowsCount'),
};


document.addEventListener('DOMContentLoaded', () => {
    const isAdminElement = document.getElementById('isAdmin');
    userPositionId = isAdminElement ? (isAdminElement.value === '1' ? 1 : 2) : 2;

    loadEmployees();
    loadDepartments();
    loadPositions();
    loadRecentlyUploaded(); // Add this line
    setupEventListeners();

    document.getElementById('addEmployeeBtn').addEventListener('click', prepareAddForm);
});

function setupEventListeners() {
    elements.departmentFilter.addEventListener('change', filterEmployees);
    elements.statusFilter.addEventListener('change', filterEmployees);
    elements.searchBtn.addEventListener('click', filterEmployees);
    elements.searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') filterEmployees();
    });
    elements.saveEmployeeBtn.addEventListener('click', saveEmployee);
    elements.editEmployeeBtn.addEventListener('click', editEmployee);
    elements.verifyPinBtn.addEventListener('click', verifyPinAndDelete);

    // Clear PIN input and error message when modal is closed
    document.getElementById('pinVerificationModal').addEventListener('hidden.bs.modal', () => {
        elements.pinInput.value = '';
        elements.pinError.style.display = 'none';
    });

    document.getElementById('importDataBtn').addEventListener('click', () => {
        elements.importDataModal.show();
    });
    elements.uploadCsvBtn.addEventListener('click', handleCsvUpload);
    elements.clearRecentBtn.addEventListener('click', clearRecentUploads);
    elements.csvFileInput.addEventListener('change', handleFileSelect);
}

async function loadEmployees(page = 1) {
    try {
        // Corrected template literal syntax
        const response = await fetch(`${API_URL}/api/employees?page=${page}&per_page=${itemsPerPage}`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const { employees, total } = await response.json();

        totalEmployees = total;
        renderOrgChart(employees);
        renderPagination();
    } catch (error) {
        console.error('Error loading employees:', error);
        alert('Failed to load employees. Please try again.');
    }
}

function renderOrgChart(employees) {
    elements.orgChartContainer.innerHTML = '';

    employees.forEach(emp => {
        const statusClass = getStatusClass(emp.emp_status);
        const tenure = calculateTenure(emp.start_date);

        const card = document.createElement('div');
        card.className = 'employee-card p-3';
        card.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1">${emp.emp_name}</h6>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="department-badge rounded-pill">${emp.dept_name}</span>
                        <small class="text-muted">${emp.position_name}</small>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="employee-status ${statusClass} rounded-pill">${emp.emp_status}</span>
                        <small class="text-muted">${tenure}</small>
                    </div>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-outline-primary view-detail" data-id="${emp.emp_id}">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary edit-employee" data-id="${emp.emp_id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    ${userPositionId === 1 ? `
                    <button class="btn btn-sm btn-outline-danger delete-employee" data-id="${emp.emp_id}">
                        <i class="fas fa-trash"></i>
                    </button>` : ''}
                </div>
            </div>
        `;

        card.querySelector('.view-detail').addEventListener('click', () => showEmployeeDetail(emp.emp_id));
        card.querySelector('.edit-employee').addEventListener('click', () => prepareEditForm(emp.emp_id));

        // Only add delete event listener if the button exists (admin only)
        const deleteBtn = card.querySelector('.delete-employee');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => confirmDelete(emp.emp_id));
        }

        elements.orgChartContainer.appendChild(card);
    });
}

function getStatusClass(status) {
    switch (status) {
        case 'REGULAR': return 'status-regular';
        case 'PROBI': return 'status-probi';
        case 'SEASONAL': return 'status-seasonal';
        default: return 'bg-secondary';
    }
}

function renderPagination() {
    const totalPages = Math.ceil(totalEmployees / itemsPerPage);
    elements.paginationContainer.innerHTML = '';

    if (totalPages <= 1) return;

    const pagination = document.createElement('nav');
    pagination.innerHTML = `
                <ul class="pagination">
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <button class="page-link" onclick="changePage(${currentPage - 1})">Previous</button>
                    </li>
                    
                    ${Array.from({ length: totalPages }, (_, i) => `
                        <li class="page-item ${i + 1 === currentPage ? 'active' : ''}">
                            <button class="page-link" onclick="changePage(${i + 1})">${i + 1}</button>
                        </li>
                    `).join('')}

                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <button class="page-link" onclick="changePage(${currentPage + 1})">Next</button>
                    </li>
                </ul>
            `;

    elements.paginationContainer.appendChild(pagination);
}

function changePage(newPage) {
    if (newPage < 1 || newPage > Math.ceil(totalEmployees / itemsPerPage)) return;
    currentPage = newPage;
    filterEmployees();
}

async function filterEmployees() {
    const department = elements.departmentFilter.value;
    const status = elements.statusFilter.value;
    const search = elements.searchInput.value.trim();

    try {
        const params = new URLSearchParams({
            page: currentPage,
            per_page: itemsPerPage
        });

        if (department) params.append('department', department);
        if (status) params.append('status', status);
        if (search) params.append('search', search);

        const response = await fetch(`${API_URL}/api/employees?${params.toString()}`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const { employees, total } = await response.json();

        totalEmployees = total;
        renderOrgChart(employees);
        renderPagination();
    } catch (error) {
        console.error('Error filtering employees:', error);
        alert(`Failed to filter employees: ${error.message}`);
    }
}

async function showEmployeeDetail(empId) {
    try {
        const response = await fetch(`${API_URL}/api/employees/${empId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const employee = await response.json();

        currentEmployeeId = empId;
        const tenure = calculateTenure(employee.start_date);

        // Update modal content
        document.getElementById('detailEmpName').textContent = employee.emp_name;
        document.getElementById('detailEmpStatus').textContent = employee.emp_status;
        document.getElementById('detailEmpStatus').className = `badge rounded-pill ${getStatusClass(employee.emp_status)}`;
        document.getElementById('detailDept').textContent = employee.dept_name;
        document.getElementById('detailPosition').textContent = employee.position_name;
        document.getElementById('detailTenure').textContent = tenure || 'N/A';
        document.getElementById('detailAge').textContent = employee.age || 'N/A';
        document.getElementById('detailGender').textContent = employee.gender === 'M' ? 'Male' : 'Female';
        document.getElementById('detailStartDate').textContent = formatDateForDisplay(employee.start_date);
        document.getElementById('detailRegDate').textContent = formatDateForDisplay(employee.regularization);

        // Load promotion history
        const historyTable = document.getElementById('promotionHistory');
        historyTable.innerHTML = '';

        if (employee.promotion_history && employee.promotion_history.length > 0) {
            employee.promotion_history.forEach(promo => {
                const row = document.createElement('tr');
                row.innerHTML = `
                            <td>${formatDateForInput(promo.promotion_date) || 'N/A'}</td>
                            <td>${promo.previous_position || 'N/A'}</td>
                            <td>${promo.new_position || 'N/A'}</td>
                            <td>${promo.new_department || 'N/A'}</td>
                        `;
                historyTable.appendChild(row);
            });
        } else {
            historyTable.innerHTML = '<tr><td colspan="4" class="text-center">No promotion history</td></tr>';
        }

        // Show or hide the edit button based on user role
        const editBtn = document.getElementById('editEmployeeBtn');
        editBtn.style.display = 'block'; // Everyone can edit

        // Show the modal
        elements.employeeModal.show();

    } catch (error) {
        console.error('Error loading employee details:', error);
        alert(`Failed to load employee details: ${error.message}`);
    }
}

async function loadDepartments() {
    try {
        const response = await fetch(`${API_URL}/api/departments`);
        const departments = await response.json();

        // Populate department filter
        elements.departmentFilter.innerHTML = '<option value="">All Departments</option>';
        departments.forEach(dept => {
            const option = document.createElement('option');
            option.value = dept.dept_name;
            option.textContent = dept.dept_name;
            elements.departmentFilter.appendChild(option);
        });

        // Populate department dropdown in form
        elements.empDepartment.innerHTML = '<option value="">Select Department</option>';
        departments.forEach(dept => {
            const option = document.createElement('option');
            option.value = dept.dept_id;
            option.textContent = dept.dept_name;
            elements.empDepartment.appendChild(option);
        });

    } catch (error) {
        console.error('Error loading departments:', error);
    }
}

async function loadPositions() {
    try {
        const response = await fetch(`${API_URL}/api/positions`);
        const positions = await response.json();

        // Populate position dropdown in form
        elements.empPosition.innerHTML = '<option value="">Select Position</option>';
        positions.forEach(pos => {
            const option = document.createElement('option');
            option.value = pos.position_id;
            option.textContent = pos.position_name;
            elements.empPosition.appendChild(option);
        });

    } catch (error) {
        console.error('Error loading positions:', error);
    }
}

function prepareAddForm() {
    elements.formModalTitle.textContent = 'Add New Employee';

    // First, remove any existing event listener
    if (startDateChangeListener) {
        elements.startDate.removeEventListener('change', startDateChangeListener);
        startDateChangeListener = null;
    }

    // Reset the form
    elements.employeeForm.reset();
    elements.empId.value = '';

    // Set default values
    elements.empStatus.value = 'PROBI'; // New employees typically start as probationary
    elements.empGender.value = 'M';

    // Set today's date as default start date
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    elements.startDate.value = `${year}-${month}-${day}`;

    // Clear tenure field
    elements.empTenure.value = '';

    // Add new event listener for start date changes
    startDateChangeListener = () => {
        elements.empTenure.value = calculateTenure(elements.startDate.value);
    };
    elements.startDate.addEventListener('change', startDateChangeListener);

    // Initialize tenure based on today's date
    elements.empTenure.value = calculateTenure(elements.startDate.value);

    elements.employeeFormModal.show();
}

// Modified prepareEditForm function
async function prepareEditForm(empId) {
    try {
        const response = await fetch(`${API_URL}/api/employees/${empId}`);
        const employee = await response.json();

        elements.formModalTitle.textContent = 'Edit Employee';

        // First, remove any existing event listener
        if (startDateChangeListener) {
            elements.startDate.removeEventListener('change', startDateChangeListener);
            startDateChangeListener = null;
        }

        // Set form values
        elements.empId.value = employee.emp_id;
        elements.empName.value = employee.emp_name;
        elements.empAge.value = employee.age || '';
        elements.empGender.value = employee.gender || 'M';
        elements.empStatus.value = employee.emp_status || 'REGULAR';
        elements.empDepartment.value = employee.department || '';
        elements.empPosition.value = employee.position || '';

        // Format dates for the input fields
        elements.startDate.value = formatDateForInput(employee.start_date);
        elements.regularizationDate.value = formatDateForInput(employee.regularization);

        // Calculate and set tenure automatically
        elements.empTenure.value = calculateTenure(employee.start_date);

        // Add event listener to update tenure when start date changes
        startDateChangeListener = () => {
            elements.empTenure.value = calculateTenure(elements.startDate.value);
        };
        elements.startDate.addEventListener('change', startDateChangeListener);

        elements.employeeFormModal.show();

    } catch (error) {
        console.error('Error preparing edit form:', error);
        alert('Failed to load employee data for editing. Please try again.');
    }
}


function formatDateForInput(dateString) {
    if (!dateString) return '';

    try {
        // Parse the date string (assuming format like "YYYY-MM-DD" or similar)
        const date = new Date(dateString);

        // Check if date is valid
        if (isNaN(date.getTime())) {
            console.warn('Invalid date:', dateString);
            return '';
        }

        // Format as YYYY-MM-DD (required by input[type="date"])
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    } catch (e) {
        console.error('Error formatting date:', e);
        return '';
    }
}


function formatDateForDisplay(dateString) {
    if (!dateString) return 'N/A';

    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'N/A';

        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (e) {
        console.error('Error formatting display date:', e);
        return 'N/A';
    }
}

function editEmployee() {
    if (currentEmployeeId) {
        prepareEditForm(currentEmployeeId);
        elements.employeeModal.hide();
    }
}

// Fix for the saveEmployee function
async function saveEmployee() {
    // Validate required fields
    if (!elements.empName.value) {
        alert('Employee name is required!');
        return;
    }
    if (!elements.empDepartment.value) {
        alert('Department is required!');
        return;
    }
    if (!elements.empPosition.value) {
        alert('Position is required!');
        return;
    }
    if (!elements.startDate.value) {
        alert('Start date is required!');
        return;
    }

    const employeeData = {
        emp_name: elements.empName.value,
        age: elements.empAge.value || null,
        gender: elements.empGender.value,
        emp_status: elements.empStatus.value,
        department: elements.empDepartment.value,
        position: elements.empPosition.value,
        start_date: elements.startDate.value,
        regularization: elements.regularizationDate.value || null
    };

    // are you sure you want to save changes?
    if (!confirm('Are you sure you want to save these changes?')) {
        return;
    }

    try {
        let url, method;

        if (elements.empId.value) {
            // Update existing employee
            url = `${API_URL}/api/employees/${elements.empId.value}`;
            method = 'PUT';
            employeeData.emp_id = elements.empId.value;
        } else {
            // Add new employee
            url = `${API_URL}/api/insert_employees`;
            method = 'POST';
        }

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(employeeData)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Failed to save employee');
        }

        elements.employeeFormModal.hide();
        await loadEmployees(currentPage);
        alert('Employee saved successfully!');
    } catch (error) {
        console.error('Error saving employee:', error);
        alert(`Failed to save employee: ${error.message}`);
    }
}

function calculateTenure(startDate) {
    if (!startDate) return '';

    try {
        const start = new Date(startDate);
        if (isNaN(start.getTime())) return '';

        const now = new Date();

        // Calculate total difference in months
        let months = (now.getFullYear() - start.getFullYear()) * 12;
        months -= start.getMonth();
        months += now.getMonth();

        // Calculate years and remaining months
        const years = Math.floor(months / 12);
        const remainingMonths = months % 12;

        // Calculate days
        const startDay = start.getDate();
        const nowDay = now.getDate();
        let days = nowDay - startDay;

        // Adjust for negative days
        if (days < 0) {
            const lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, startDay);
            days = Math.floor((now - lastMonth) / (1000 * 60 * 60 * 24));
            months--;
        }

        // Build the tenure string
        const parts = [];
        if (years > 0) parts.push(`${years} Year${years !== 1 ? 's' : ''}`);
        if (remainingMonths > 0) parts.push(`${remainingMonths} Month${remainingMonths !== 1 ? 's' : ''}`);
        if (days > 0 || parts.length === 0) parts.push(`${days} Day${days !== 1 ? 's' : ''}`);

        return parts.join(', ');
    } catch (e) {
        console.error('Error calculating tenure:', e);
        return '';
    }
}

function confirmDelete(empId) {
    // FIXED: Check if user is admin before proceeding
    if (userPositionId !== 1) {
        alert('You do not have permission to delete employees.');
        return;
    }

    currentEmployeeId = empId;

    // Show PIN verification modal for admins
    elements.pinVerificationModal.show();
}

function verifyPinAndDelete() {
    // FIXED: Double-check that the user is an admin
    if (userPositionId !== 1) {
        elements.pinVerificationModal.hide();
        alert('You do not have permission to delete employees.');
        return;
    }

    const pin = elements.pinInput.value.trim();

    if (!pin) {
        elements.pinError.textContent = 'PIN is required!';
        elements.pinError.style.display = 'block';
        return;
    }

    // Create a form to submit via POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'verify_pin.php';
    form.style.display = 'none';

    // Add PIN and employee ID inputs
    const pinInput = document.createElement('input');
    pinInput.type = 'hidden';
    pinInput.name = 'pin';
    pinInput.value = pin;

    const empIdInput = document.createElement('input');
    empIdInput.type = 'hidden';
    empIdInput.name = 'emp_id';
    empIdInput.value = currentEmployeeId;

    // Add a return URL input
    const returnUrlInput = document.createElement('input');
    returnUrlInput.type = 'hidden';
    returnUrlInput.name = 'return_url';
    returnUrlInput.value = window.location.href;

    // Append inputs to form
    form.appendChild(pinInput);
    form.appendChild(empIdInput);
    form.appendChild(returnUrlInput);

    // Append form to document and submit
    document.body.appendChild(form);
    form.submit();
}

async function deleteEmployee() {
    // This function is now only called from the confirmation modal
    // and should never be directly accessible to non-admins
    try {
        // FIXED: Add an additional server-side check before performing deletion
        if (userPositionId !== 1) {
            alert('You do not have permission to delete employees.');
            return;
        }

        const response = await fetch(`${API_URL}/api/employees/${currentEmployeeId}`, {
            method: 'DELETE',
            headers: {
                'X-Admin-Position': userPositionId.toString() // Send position ID to API for verification
            }
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Failed to delete employee');
        }

        elements.confirmModal.hide();
        await loadEmployees(currentPage);
        alert('Employee terminated successfully!');
    } catch (error) {
        console.error('Error deleting employee:', error);
        alert(`Failed to delete employee: ${error.message}`);
    }
}

// Expose functions to global scope for pagination buttons
window.changePage = changePage;


async function handleCsvUpload() {
    const fileInput = elements.csvFileInput;

    if (!fileInput.files.length) {
        alert('Please select a CSV file');
        return;
    }

    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('csv_file', file);

    try {
        document.getElementById('importProgress').style.display = 'block';
        document.getElementById('importStatus').textContent = 'Uploading and processing...';
        elements.uploadCsvBtn.disabled = true;

        const response = await fetch(`${API_URL}/api/import_employees`, {
            method: 'POST',
            body: formData
        });

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response. Check server logs for details.');
        }

        const result = await response.json();

        document.getElementById('importProgress').style.display = 'none';
        const resultsDiv = document.getElementById('importResults');
        resultsDiv.style.display = 'block';

        // Handle both success and partial success
        if (result.success || (result.successful && result.successful > 0)) {
            resultsDiv.className = result.failed > 0 ? 'alert alert-warning' : 'alert alert-success';

            let summaryHtml = `
                <strong>${result.failed > 0 ? 'Import Completed with Errors' : 'Import Successful!'}</strong><br>
                Total processed: ${result.total}<br>
                Successful: ${result.successful}<br>
                Failed: ${result.failed}<br>
            `;

            if (result.errors && result.errors.length > 0) {
                summaryHtml += '<br><strong>Errors:</strong><br>';
                summaryHtml += '<div style="max-height: 200px; overflow-y: auto; font-size: 0.9em;">';
                summaryHtml += result.errors.map(err => `• ${err}`).join('<br>');
                summaryHtml += '</div>';

                if (result.failed > result.errors.length) {
                    summaryHtml += `<br><small class="text-muted">... and ${result.failed - result.errors.length} more errors</small>`;
                }
            }

            resultsDiv.innerHTML = summaryHtml;

            // Save uploaded IDs to localStorage if any were successful
            if (result.uploaded_ids && result.uploaded_ids.length > 0) {
                localStorage.setItem('recentlyUploadedIds', JSON.stringify(result.uploaded_ids));
            }

            // Only auto-close if there were some successes
            if (result.successful > 0) {
                setTimeout(() => {
                    elements.importDataModal.hide();
                    loadEmployees(currentPage);
                    loadRecentlyUploaded();
                    resetImportModal();

                    // Show reminder to refresh promotion predictions
                    showPromotionRefreshReminder(result.successful);
                }, 3000);
            }
        } else {
            // Complete failure
            resultsDiv.className = 'alert alert-danger';

            let errorMessage = '<strong>Import Failed!</strong><br>';

            if (result.error) {
                errorMessage += `Error: ${result.error}<br>`;
            }

            if (result.details) {
                errorMessage += '<br><details><summary>Technical Details (click to expand)</summary>';
                errorMessage += `<pre style="font-size: 0.8em; max-height: 300px; overflow: auto;">${result.details}</pre>`;
                errorMessage += '</details>';
            }

            if (result.errors && result.errors.length > 0) {
                errorMessage += '<br><strong>Errors:</strong><br>';
                errorMessage += '<div style="max-height: 200px; overflow-y: auto; font-size: 0.9em;">';
                errorMessage += result.errors.map(err => `• ${err}`).join('<br>');
                errorMessage += '</div>';
            }

            resultsDiv.innerHTML = errorMessage;
        }
    } catch (error) {
        console.error('Upload error:', error);
        document.getElementById('importProgress').style.display = 'none';
        const resultsDiv = document.getElementById('importResults');
        resultsDiv.style.display = 'block';
        resultsDiv.className = 'alert alert-danger';
        resultsDiv.innerHTML = `
            <strong>Error!</strong><br>
            ${error.message}<br>
            <br>
            <small class="text-muted">
                Possible causes:<br>
                • Server is not running<br>
                • Network connection issue<br>
                • Server error occurred (check console/logs)<br>
                • Invalid CSV format
            </small>
        `;
    } finally {
        elements.uploadCsvBtn.disabled = false;
    }
}

// Add this function to teamManagement.js
function showPromotionRefreshReminder(uploadedCount) {
    const reminderDiv = document.createElement('div');
    reminderDiv.className = 'alert alert-info alert-dismissible fade show mt-3';
    reminderDiv.innerHTML = `
        <i class="fas fa-info-circle me-2"></i>
        <strong>Promotion Predictions Update Needed!</strong><br>
        ${uploadedCount} new employee(s) imported. To see their promotion predictions:
        <ol class="mb-2 mt-2">
            <li>Navigate to the <strong>Promotion Predictions</strong> page</li>
            <li>Click the <strong>Refresh Data</strong> button to reload predictions</li>
        </ol>
        <small class="text-muted">Note: The ML model will automatically calculate predictions for new employees.</small>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    const mainContent = document.querySelector('.col-md-9');
    if (mainContent) {
        mainContent.insertBefore(reminderDiv, mainContent.firstChild);
    }
}

function resetImportModal() {
    elements.csvFileInput.value = '';
    elements.csvPreviewSection.style.display = 'none';
    elements.uploadCsvBtn.style.display = 'none';
    document.getElementById('importResults').style.display = 'none';
}


async function loadRecentlyUploaded() {
    const recentIds = localStorage.getItem('recentlyUploadedIds');

    if (!recentIds) {
        elements.recentlyUploadedSection.style.display = 'none';
        return;
    }

    const ids = JSON.parse(recentIds);

    if (ids.length === 0) {
        elements.recentlyUploadedSection.style.display = 'none';
        return;
    }

    try {
        const response = await fetch(`${API_URL}/api/employees/recent?ids=${ids.join(',')}`);
        const employees = await response.json();

        if (employees.length === 0) {
            elements.recentlyUploadedSection.style.display = 'none';
            return;
        }

        elements.recentlyUploadedSection.style.display = 'block';
        elements.recentUploadCount.textContent = employees.length;
        elements.recentlyUploadedCards.innerHTML = '';

        employees.forEach(emp => {
            const card = createRecentEmployeeCard(emp);
            elements.recentlyUploadedCards.appendChild(card);
        });

    } catch (error) {
        console.error('Error loading recently uploaded employees:', error);
    }
}

function createRecentEmployeeCard(emp) {
    const col = document.createElement('div');
    col.className = 'col';

    const statusClass = getStatusClass(emp.emp_status);
    const tenure = calculateTenure(emp.start_date);

    col.innerHTML = `
        <div class="card border-success shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0">${emp.emp_name}</h6>
                    <span class="badge bg-success">NEW</span>
                </div>
                <div class="mb-2">
                    <span class="department-badge rounded-pill">${emp.dept_name}</span>
                    <small class="text-muted ms-2">${emp.position_name}</small>
                </div>
                <div class="d-flex gap-2 mb-3">
                    <span class="employee-status ${statusClass} rounded-pill">${emp.emp_status}</span>
                    <small class="text-muted">${tenure}</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary flex-fill view-detail" data-id="${emp.emp_id}">
                        <i class="fas fa-eye me-1"></i>View
                    </button>
                    <button class="btn btn-sm btn-outline-secondary flex-fill edit-employee" data-id="${emp.emp_id}">
                        <i class="fas fa-edit me-1"></i>Edit
                    </button>
                </div>
            </div>
        </div>
    `;

    col.querySelector('.view-detail').addEventListener('click', () => showEmployeeDetail(emp.emp_id));
    col.querySelector('.edit-employee').addEventListener('click', () => prepareEditForm(emp.emp_id));

    return col;
}

function clearRecentUploads() {
    if (confirm('Clear recently uploaded employees from this view?')) {
        localStorage.removeItem('recentlyUploadedIds');
        elements.recentlyUploadedSection.style.display = 'none';
    }
}

async function handleFileSelect(event) {
    const file = event.target.files[0];

    if (!file) {
        elements.csvPreviewSection.style.display = 'none';
        elements.uploadCsvBtn.style.display = 'none';
        return;
    }

    if (!file.name.endsWith('.csv')) {
        alert('Please select a CSV file');
        event.target.value = '';
        return;
    }

    try {
        const text = await file.text();
        const lines = text.split('\n').filter(line => line.trim());

        if (lines.length < 2) {
            alert('CSV file appears to be empty or invalid');
            return;
        }

        // Parse CSV
        const headers = parseCSVLine(lines[0]);
        const rows = [];

        for (let i = 1; i < Math.min(lines.length, 11); i++) { // Preview first 10 rows
            const row = parseCSVLine(lines[i]);
            if (row.length === headers.length) {
                rows.push(row);
            }
        }

        displayCSVPreview(headers, rows, lines.length - 1);
        elements.csvPreviewSection.style.display = 'block';
        elements.uploadCsvBtn.style.display = 'inline-block';

    } catch (error) {
        alert('Error reading CSV file: ' + error.message);
        event.target.value = '';
    }
}

function parseCSVLine(line) {
    const result = [];
    let current = '';
    let inQuotes = false;

    for (let i = 0; i < line.length; i++) {
        const char = line[i];

        if (char === '"') {
            inQuotes = !inQuotes;
        } else if (char === ',' && !inQuotes) {
            result.push(current.trim());
            current = '';
        } else {
            current += char;
        }
    }

    result.push(current.trim());
    return result;
}

function displayCSVPreview(headers, rows, totalRows) {
    elements.totalRowsCount.textContent = `${totalRows} rows`;

    // Create table headers
    const thead = elements.csvPreviewTable.querySelector('thead');
    thead.innerHTML = `
        <tr>
            <th>#</th>
            ${headers.map(header => `<th>${header}</th>`).join('')}
        </tr>
    `;

    // Create table rows
    const tbody = elements.csvPreviewTable.querySelector('tbody');
    tbody.innerHTML = rows.map((row, index) => `
        <tr>
            <td class="text-muted">${index + 1}</td>
            ${row.map(cell => `<td>${cell || '<span class="text-muted">-</span>'}</td>`).join('')}
        </tr>
    `).join('');
}