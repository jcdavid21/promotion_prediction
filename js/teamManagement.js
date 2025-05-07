let currentPage = 1;
const itemsPerPage = 10;
let totalEmployees = 0;
let currentEmployeeId = null;
const API_URL = 'http://localhost:8800';
let startDateChangeListener = null;

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
    confirmActionBtn: document.getElementById('confirmActionBtn')
};


// Make sure to connect the Add New Member button to the prepareAddForm function
document.addEventListener('DOMContentLoaded', () => {
    loadEmployees();
    loadDepartments();
    loadPositions();
    setupEventListeners();
    
    // Add this line to connect the Add Employee button to the prepareAddForm function
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
                    <button class="btn btn-sm btn-outline-danger delete-employee" data-id="${emp.emp_id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;

        card.querySelector('.view-detail').addEventListener('click', () => showEmployeeDetail(emp.emp_id));
        card.querySelector('.edit-employee').addEventListener('click', () => prepareEditForm(emp.emp_id));
        card.querySelector('.delete-employee').addEventListener('click', () => confirmDelete(emp.emp_id));

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
    currentEmployeeId = empId;
    elements.confirmMessage.textContent = 'Are you sure you want to terminate this employee?';
    elements.confirmActionBtn.className = 'btn btn-danger';
    elements.confirmActionBtn.textContent = 'Delete';
    elements.confirmActionBtn.onclick = deleteEmployee;
    elements.confirmModal.show();
}

async function deleteEmployee() {
    try {
        const response = await fetch(`${API_URL}/api/employees/${currentEmployeeId}`, {
            method: 'DELETE'
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