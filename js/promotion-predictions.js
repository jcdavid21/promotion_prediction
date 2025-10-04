let employeeDataTable = null;
let currentEmployeeData = [];
let currentThreshold = 0;
let allEmployeeData = [];
let currentEmployee = null; // Add this line

$(document).ready(function () {
    initializeDataTable();
    loadPromotionPredictions();

    $('#refresh-btn').click(function () {
        loadPromotionPredictions();
    });

    $('#promotion-filter').change(function () {
        applyPromotionFilter();
    });

    $('#promotionTable').on('click', '.view-details', function () {
        const empId = $(this).data('id');
        const employee = allEmployeeData.find(e => e.emp_id == empId);
        if (employee) {
            currentEmployee = employee; // Add this line
            showShapDetails(employee);
        }
    });
});


function applyPromotionFilter() {
    const filterValue = $('#promotion-filter').val();
    let filteredData = [...allEmployeeData];

    switch (filterValue) {
        case 'promoted':
            filteredData = allEmployeeData.filter(e =>
                e.promotion_history && e.promotion_history.length > 0);
            break;
        case 'not-promoted':
            filteredData = allEmployeeData.filter(e =>
                !e.promotion_history || e.promotion_history.length === 0);
            break;
        case 'recently-promoted':
            filteredData = allEmployeeData.filter(e => {
                if (!e.promotion_history || e.promotion_history.length === 0) return false;
                const latestPromo = new Date(e.promotion_history[0].promotion_date);
                const oneYearAgo = new Date();
                oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1);
                return latestPromo > oneYearAgo;
            });
            break;
        case 'frequent-promotions':
            filteredData = allEmployeeData.filter(e =>
                e.promotion_history && e.promotion_history.length >= 3);
            break;
        // 'all' case falls through to default
    }

    currentEmployeeData = filteredData;
    refreshDataTable();
}

function refreshDataTable() {
    if (employeeDataTable) {
        employeeDataTable.clear();
        if (currentEmployeeData.length > 0) {
            employeeDataTable.rows.add(currentEmployeeData).draw();

            // Reapply highlighting
            employeeDataTable.rows().every(function () {
                const data = this.data();
                if (parseFloat(data.total_score) >= currentThreshold) {
                    $(this.node()).addClass('table-success');
                } else {
                    $(this.node()).removeClass('table-success');
                }
            });
        }
    }
}

function initializeDataTable() {
    employeeDataTable = $('#promotionTable').DataTable({
        columns: [
            {
                title: "Employee",
                data: "emp_name",
                render: function (data, type, row) {
                    const promoCount = row.promotion_history ? row.promotion_history.length : 0;
                    if (type === 'display') {
                        return promoCount > 0 ?
                            `${data} <span class="badge bg-primary ms-2">${promoCount} promo${promoCount !== 1 ? 's' : ''}</span>` :
                            data;
                    }
                    return data;
                }
            },
            { title: "Position", data: "position" },
            { title: "Department", data: "department" },
            {
                title: "Score",
                data: "total_score",
                render: function (data) {
                    return data ? parseFloat(data).toFixed(2) : 'N/A';
                }
            },
            {
                title: "Promotion Probability",
                data: "promotion_probability",
                render: function (data) {
                    if (data === undefined || data === null) return 'N/A';
                    const percent = (parseFloat(data) * 100).toFixed(1);
                    const color = data >= 0.7 ? 'success' : data >= 0.4 ? 'warning' : 'danger';
                    return `
                        <div class="progress">
                            <div class="progress-bar bg-${color}" 
                                role="progressbar" 
                                style="width: ${percent}%" 
                                aria-valuenow="${percent}" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                ${percent}%
                            </div>
                        </div>`;
                }
            },
            {
                title: "Actions",
                data: null,
                render: function (data, type, row) {
                    const promoCount = row.promotion_history ? row.promotion_history.length : 0;
                    return `
                        <button class="btn btn-sm btn-info view-details" 
                                data-id="${row.emp_id}">
                            <i class="fas fa-chart-bar me-1"></i>
                            ${promoCount > 0 ? '<i class="fas fa-history me-1"></i>' : ''}
                            Details
                        </button>`;
                }
            }
        ],
        order: [[3, 'desc']], // Default sort by Score column
        responsive: true,
        dom: '<"top"f>rt<"bottom"lip><"clear">',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search employees...",
            lengthMenu: "Show _MENU_ employees",
            info: "Showing _START_ to _END_ of _TOTAL_ employees",
            infoEmpty: "No employees found",
            infoFiltered: "(filtered from _MAX_ total)"
        }
    });
}

function loadPromotionPredictions() {
    showLoading();

    $.ajax({
        url: 'http://localhost:8800/api/promotion_predictions',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            hideLoading();

            if (response && response.success) {
                allEmployeeData = response.data || [];
                currentEmployeeData = [...allEmployeeData]; // Start with all data
                currentThreshold = response.threshold ? parseFloat(response.threshold) : 0;

                if (isNaN(currentThreshold)) {
                    currentThreshold = 0;
                    console.warn("Invalid threshold value received, defaulting to 0");
                }

                // Initialize or refresh table
                if (!employeeDataTable) {
                    initializeDataTable();
                } else {
                    refreshDataTable();
                }

                // Apply any active filter
                applyPromotionFilter();
            } else {
                const errorMsg = response?.error || 'Unknown server error';
                showError('Failed to load predictions: ' + errorMsg);
            }
        },
        error: function (xhr, status, error) {
            hideLoading();
            let errorMsg = error;

            try {
                const errResponse = JSON.parse(xhr.responseText);
                errorMsg = errResponse.error || errorMsg;
            } catch (e) {
                console.error("Error parsing error response:", e);
            }

            showError('Failed to connect: ' + errorMsg);
        }
    });
}


function showShapDetails(employee) {
    if (!employee) return;

    // Update basic info
    $('#employee-name').text(employee.emp_name || 'N/A');
    $('#employee-position').text(employee.position || 'N/A');
    $('#employee-department').text(employee.department || 'N/A');

    // Update metrics
    updateAttendanceProgress(
        employee.details?.attendance?.score,
        employee.details?.attendance?.tardiness,
        employee.details?.attendance?.absences
    );
    updateDisciplineProgress(
        employee.details?.discipline?.score,
        employee.details?.discipline?.minor_offenses,
        employee.details?.discipline?.grave_offenses
    );
    updatePerformanceProgress(
        employee.details?.performance?.score,
        employee.details?.performance?.total
    );

    // Handle SHAP plot
    renderShapPlot(employee.shap_explanation);

    // Render promotion history timeline
    renderPromotionHistory(employee.promotion_history);

    // Show modal
    $('#shapModal').modal('show');
}

function renderPromotionHistory(promotions) {
    const timeline = $('#promotion-timeline');
    timeline.empty();

    if (!promotions || promotions.length === 0) {
        timeline.html(`
            <div class="alert alert-warning">
                No promotion history found for this employee
            </div>
        `);
        $('.historical-header .badge').removeClass('bg-primary').addClass('bg-secondary').text('Impact: None');
        $('.impact-fill').css('width', '0%');
        $('.impact-value').text('0%');
        return;
    }

    // Calculate impact score based on number and recency of promotions
    const now = new Date();
    const impactScore = Math.min(100,
        promotions.length * 15 +
        (30 - Math.min(30, (now - new Date(promotions[0].promotion_date)) / (1000 * 60 * 60 * 24 * 30))) * 2
    );

    // Update impact visualization
    $('.impact-fill').css('width', `${impactScore}%`);
    $('.impact-value').text(`${Math.round(impactScore)}%`);

    // Set impact level badge
    const impactBadge = $('.historical-header .badge');
    impactBadge.removeClass('bg-primary bg-warning bg-danger');
    if (impactScore > 70) {
        impactBadge.addClass('bg-danger').text('Impact: Very High');
    } else if (impactScore > 40) {
        impactBadge.addClass('bg-warning').text('Impact: High');
    } else {
        impactBadge.addClass('bg-primary').text('Impact: Moderate');
    }

    // Add timeline items
    promotions.forEach(promo => {
        const promoDate = new Date(promo.promotion_date).toLocaleDateString();
        const timeInRole = promo.days_since_promotion ?
            `${Math.floor(promo.days_since_promotion / 30)} months` : 'Current role';

        timeline.append(`
            <div class="timeline-item">
                <div class="timeline-date">${promoDate}</div>
                <div class="timeline-content">
                    <h6>${promo.new_position || 'Promotion'}</h6>
                    <p>From ${promo.previous_position} to ${promo.new_position}</p>
                    <p>Department: ${promo.new_department || promo.previous_department}</p>
                    <p>Performance Rating: ${promo.performance_rating || 'N/A'}</p>
                    <span class="timeline-impact">Time in role: ${timeInRole}</span>
                </div>
            </div>
        `);
    });

    // Add model impact explanation
    const impactText = impactScore > 70 ?
        "Strong historical pattern of promotions significantly increases prediction" :
        impactScore > 40 ?
            "Past promotions moderately influence current prediction" :
            "Limited promotion history has small impact on prediction";

    $('.historical-section .alert-info').html(`
        <i class="fas fa-info-circle me-2"></i>
        ${impactText}. Employees with similar history are 
        <strong>${(impactScore / 30).toFixed(1)}x</strong> more likely to be promoted.
    `);
}

function updatePerformanceProgress(score, total) {
    updateProgressBar('#performance-progress', '#performance-score',
        parseFloat(score || 0), 10);
    updateDetail('#performance-total', total || 0);
}



function updateAttendanceProgress(score, tardiness, absences) {
    // Convert score to number and ensure it's within 0-10 range
    const attendanceScore = Math.min(Math.max(parseFloat(score || 0), 0), 10);
    const maxScore = 10;

    // Calculate percentage (minimum 5% width for any non-zero score)
    let percentage = (attendanceScore / maxScore) * 100;
    if (attendanceScore > 0 && percentage < 5) {
        percentage = 5;
    }

    // Set color based on score range
    let colorClass = 'bg-danger'; // Default to red
    if (attendanceScore >= 8) colorClass = 'bg-success';
    else if (attendanceScore >= 5) colorClass = 'bg-warning';

    // Update progress bar
    $('#attendance-progress')
        .css('width', percentage + '%')
        .removeClass('bg-success bg-warning bg-danger')
        .addClass(colorClass);

    // Update score display (show exact value, not just 0.0 or 10)
    $('#attendance-score').text(attendanceScore.toFixed(1) + '/' + maxScore);

    // Update detail values
    updateDetail('#tardiness', tardiness || 0);
    updateDetail('#absences', absences || 0);
}

function updateDisciplineProgress(score, minorOffenses, graveOffenses) {
    const disciplineScore = parseFloat(score || 0);
    const maxScore = 10;

    // Calculate percentage with minimum 5% width for any non-zero score
    let percentage = (disciplineScore / maxScore) * 100;
    if (disciplineScore > 0 && disciplineScore < 0.5) {
        percentage = 5; // Minimum visible width for low but non-zero scores
    }

    // Set color based on score range
    let colorClass = 'bg-danger'; // Default to red
    if (disciplineScore >= 8) {
        colorClass = 'bg-success';
    } else if (disciplineScore >= 5) {
        colorClass = 'bg-warning';
    }

    // Update progress bar
    $('#discipline-progress')
        .css('width', percentage + '%')
        .removeClass('bg-success bg-warning bg-danger')
        .addClass(colorClass);

    // Update score display
    $('#discipline-score').text(disciplineScore.toFixed(1) + '/' + maxScore);

    // Update detail values (always show, even if 0)
    updateDetail('#minor-offenses', minorOffenses || 0);
    updateDetail('#grave-offenses', graveOffenses || 0);
}


function updateProgressBar(progressBarId, scoreId, value, max) {
    const displayValue = Math.max(value, 0); // Don't allow negative values
    const percentage = Math.min((displayValue / max) * 100, 100);

    // Set minimum width of 5% for any non-zero value to ensure visibility
    const minVisibleWidth = value > 0 ? Math.max(percentage, 5) : percentage;

    $(progressBarId).css('width', minVisibleWidth + '%');
    $(scoreId).text(displayValue.toFixed(1) + '/' + max);

    // Update color based on value
    const colorClass = displayValue >= 8 ? 'bg-success' :
        displayValue >= 5 ? 'bg-warning' : 'bg-danger';
    $(progressBarId)
        .removeClass('bg-success bg-warning bg-danger')
        .addClass(colorClass);
}


function updateDetail(selector, value) {
    // Always show the value, even if 0
    const displayValue = value || 0;
    if (typeof displayValue === 'number') {
        $(selector).text(Number.isInteger(displayValue) ?
            displayValue :
            displayValue.toFixed(1));
    } else {
        $(selector).text(displayValue);
    }
}

function renderShapPlot(shapExplanation) {
    const plotElement = document.getElementById('shap-plot');
    Plotly.purge(plotElement);

    if (!shapExplanation || !shapExplanation.features || !shapExplanation.values) {
        plotElement.innerHTML = '<div class="alert alert-warning">SHAP explanation not available</div>';
        return;
    }

    // Prepare SHAP data for visualization
    const features = shapExplanation.features || [];
    const values = shapExplanation.values || [];
    const dataValues = shapExplanation.data || [];

    // Combine and sort by absolute SHAP value
    const shapData = features.map((feature, i) => ({
        feature: feature,
        value: parseFloat(values[i] || 0),
        data: parseFloat(dataValues[i] || 0)
    })).filter(item => item.feature)
        .sort((a, b) => Math.abs(b.value) - Math.abs(a.value))
        .slice(0, 10); // Top 10 features

    if (shapData.length === 0) {
        plotElement.innerHTML = '<div class="alert alert-info">No SHAP data available</div>';
        return;
    }

    // Create Plotly visualization
    const trace = {
        x: shapData.map(d => d.value),
        y: shapData.map(d => d.feature.replace(/_/g, ' ')), // Make feature names more readable
        text: shapData.map(d => `Value: ${d.data.toFixed(2)}`),
        type: 'bar',
        orientation: 'h',
        marker: {
            color: shapData.map(d => d.value > 0 ? '#4CAF50' : '#F44336') // Green for positive, red for negative
        }
    };

    const layout = {
        title: 'Top Factors Influencing Promotion',
        xaxis: { title: 'SHAP Value (Impact on Prediction)' },
        margin: { l: 150, r: 20, t: 40, b: 40 },
        hovermode: 'closest'
    };

    Plotly.newPlot(plotElement, [trace], layout);
}

function showLoading() {
    $('#loadingIndicator').show();
    $('#promotionTable_wrapper').hide();
}

function hideLoading() {
    $('#loadingIndicator').hide();
    $('#promotionTable_wrapper').show();
}

function showError(message) {
    const alert = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
    $('#alert-container').html(alert);
}

$('#print-report-btn').click(function() {
    if (currentEmployee) {
        generatePrintReport(currentEmployee);
    } else {
        alert('No employee data available for printing.');
    }
});

function generatePrintReport(employee) {
    const currentDate = new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Calculate metrics
    const attendanceScore = employee.details?.attendance?.score || 0;
    const disciplineScore = employee.details?.discipline?.score || 0;
    const performanceScore = employee.details?.performance?.score || 0;
    const promotionProbability = (employee.promotion_probability * 100).toFixed(1);

    // Get promotion history
    const promotionHistory = employee.promotion_history || [];
    let promotionHistoryHTML = '';

    if (promotionHistory.length > 0) {
        promotionHistoryHTML = promotionHistory.map(promo => {
            const promoDate = new Date(promo.promotion_date).toLocaleDateString();
            return `
                <div class="promotion-item">
                    <div class="promotion-date">${promoDate}</div>
                    <div class="promotion-details">
                        <strong>${promo.new_position || 'Promotion'}</strong><br>
                        <span>From: ${promo.previous_position}</span><br>
                        <span>To: ${promo.new_position}</span><br>
                        <span>Department: ${promo.new_department || promo.previous_department}</span><br>
                        <span>Performance Rating: ${promo.performance_rating || 'N/A'}</span>
                    </div>
                </div>
            `;
        }).join('');
    } else {
        promotionHistoryHTML = '<div class="no-promotions">No promotion history available</div>';
    }

    // Get top SHAP features
    const shapData = employee.shap_explanation?.features?.map((feature, i) => ({
        feature: feature.replace(/_/g, ' ').toUpperCase(),
        value: parseFloat(employee.shap_explanation.values[i] || 0),
        data: parseFloat(employee.shap_explanation.data[i] || 0)
    })).sort((a, b) => Math.abs(b.value) - Math.abs(a.value)).slice(0, 8) || [];

    const topFactorsHTML = shapData.map(item => `
        <tr>
            <td>${item.feature}</td>
            <td class="text-center">${item.data.toFixed(2)}</td>
            <td class="text-center ${item.value >= 0 ? 'positive-impact' : 'negative-impact'}">
                ${item.value >= 0 ? '+' : ''}${item.value.toFixed(4)}
            </td>
            <td class="text-center">
                <div class="impact-bar">
                    <div class="impact-fill ${item.value >= 0 ? 'positive' : 'negative'}" 
                         style="width: ${Math.min(Math.abs(item.value * 1000), 100)}%"></div>
                </div>
            </td>
        </tr>
    `).join('');

    const printHTML = `
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Employee Promotion Analysis Report - ${employee.emp_name}</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                background: #fff;
                padding: 20px;
            }
            
            .report-header {
                text-align: center;
                border-bottom: 3px solid #007bff;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            
            .company-logo {
                font-size: 24px;
                font-weight: bold;
                color: #007bff;
                margin-bottom: 10px;
            }
            
            .report-title {
                font-size: 28px;
                font-weight: bold;
                color: #2c3e50;
                margin-bottom: 10px;
            }
            
            .report-subtitle {
                font-size: 16px;
                color: #666;
                margin-bottom: 15px;
            }
            
            .report-date {
                font-size: 14px;
                color: #888;
            }
            
            .employee-info {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 25px;
                border-radius: 10px;
                margin-bottom: 30px;
            }
            
            .employee-info h2 {
                font-size: 24px;
                margin-bottom: 15px;
                border-bottom: 2px solid rgba(255,255,255,0.3);
                padding-bottom: 10px;
            }
            
            .info-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .info-item {
                display: flex;
                align-items: center;
            }
            
            .info-label {
                font-weight: bold;
                margin-right: 10px;
                min-width: 120px;
            }
            
            .promotion-summary {
                background: #f8f9fa;
                border: 2px solid #e9ecef;
                border-radius: 10px;
                padding: 25px;
                margin-bottom: 30px;
                text-align: center;
            }
            
            .probability-circle {
                width: 120px;
                height: 120px;
                border-radius: 50%;
                background: conic-gradient(#28a745 0deg ${promotionProbability * 3.6}deg, #e9ecef ${promotionProbability * 3.6}deg 360deg);
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                position: relative;
            }
            
            .probability-inner {
                width: 90px;
                height: 90px;
                background: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                font-weight: bold;
                color: #28a745;
            }
            
            .metrics-section {
                margin-bottom: 30px;
            }
            
            .metrics-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
            
            .metric-card {
                background: white;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                padding: 20px;
                text-align: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .metric-title {
                font-size: 14px;
                color: #666;
                margin-bottom: 10px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .metric-score {
                font-size: 32px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            
            .metric-bar {
                height: 8px;
                background: #e9ecef;
                border-radius: 4px;
                overflow: hidden;
                margin-bottom: 10px;
            }
            
            .metric-fill {
                height: 100%;
                transition: width 0.3s ease;
            }
            
            .metric-details {
                font-size: 12px;
                color: #666;
            }
            
            .attendance .metric-fill { background: #17a2b8; }
            .discipline .metric-fill { background: #ffc107; }
            .performance .metric-fill { background: #28a745; }
            
            .factors-section {
                margin-bottom: 30px;
            }
            
            .section-title {
                font-size: 20px;
                font-weight: bold;
                color: #2c3e50;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #007bff;
            }
            
            .factors-table {
                width: 100%;
                border-collapse: collapse;
                background: white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                border-radius: 8px;
                overflow: hidden;
            }
            
            .factors-table th {
                background: #007bff;
                color: white;
                padding: 15px 10px;
                text-align: left;
                font-weight: 600;
            }
            
            .factors-table td {
                padding: 12px 10px;
                border-bottom: 1px solid #dee2e6;
            }
            
            .factors-table tr:hover {
                background: #f8f9fa;
            }
            
            .positive-impact {
                color: #28a745;
                font-weight: bold;
            }
            
            .negative-impact {
                color: #dc3545;
                font-weight: bold;
            }
            
            .impact-bar {
                width: 60px;
                height: 20px;
                background: #e9ecef;
                border-radius: 10px;
                overflow: hidden;
                position: relative;
            }
            
            .impact-fill {
                height: 100%;
                border-radius: 10px;
            }
            
            .impact-fill.positive {
                background: #28a745;
            }
            
            .impact-fill.negative {
                background: #dc3545;
            }
            
            .promotion-history {
                margin-bottom: 30px;
            }
            
            .promotion-item {
                background: white;
                border-left: 4px solid #007bff;
                margin-bottom: 15px;
                padding: 15px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                border-radius: 0 8px 8px 0;
            }
            
            .promotion-date {
                color: #007bff;
                font-weight: bold;
                font-size: 14px;
                margin-bottom: 8px;
            }
            
            .promotion-details {
                color: #666;
                font-size: 14px;
                line-height: 1.4;
            }
            
            .promotion-details strong {
                color: #2c3e50;
                font-size: 16px;
            }
            
            .no-promotions {
                text-align: center;
                color: #666;
                font-style: italic;
                padding: 40px;
                background: #f8f9fa;
                border-radius: 8px;
            }
            
            .report-footer {
                margin-top: 50px;
                padding-top: 20px;
                border-top: 1px solid #dee2e6;
                text-align: center;
                color: #666;
                font-size: 12px;
            }
            
            @media print {
                body {
                    padding: 0;
                }
                
                .metrics-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
                
                .promotion-item {
                    break-inside: avoid;
                }
                
                .metric-card {
                    break-inside: avoid;
                }
            }
        </style>
    </head>
    <body>
        <div class="report-header">
            <div class="company-logo">HR Analytics System</div>
            <h1 class="report-title">Employee Promotion Analysis Report</h1>
            <p class="report-subtitle">Comprehensive Performance & Promotion Readiness Assessment</p>
            <p class="report-date">Generated on ${currentDate}</p>
        </div>

        <div class="employee-info">
            <h2>Employee Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Name:</span>
                    <span>${employee.emp_name}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Position:</span>
                    <span>${employee.position}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Department:</span>
                    <span>${employee.department}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Overall Score:</span>
                    <span>${parseFloat(employee.total_score).toFixed(2)}/10</span>
                </div>
            </div>
        </div>

        <div class="promotion-summary">
            <h2 class="section-title">Promotion Probability</h2>
            <div class="probability-circle">
                <div class="probability-inner">${promotionProbability}%</div>
            </div>
            <p><strong>Likelihood of Promotion:</strong> ${promotionProbability >= 70 ? 'High' : promotionProbability >= 40 ? 'Moderate' : 'Low'}</p>
        </div>

        <div class="metrics-section">
            <h2 class="section-title">Performance Metrics</h2>
            <div class="metrics-grid">
                <div class="metric-card attendance">
                    <div class="metric-title">Attendance</div>
                    <div class="metric-score">${attendanceScore.toFixed(1)}</div>
                    <div class="metric-bar">
                        <div class="metric-fill" style="width: ${(attendanceScore / 10) * 100}%"></div>
                    </div>
                    <div class="metric-details">
                        Tardiness: ${employee.details?.attendance?.tardiness || 0}<br>
                        Absences: ${employee.details?.attendance?.absences || 0}
                    </div>
                </div>

                <div class="metric-card discipline">
                    <div class="metric-title">Discipline</div>
                    <div class="metric-score">${disciplineScore.toFixed(1)}</div>
                    <div class="metric-bar">
                        <div class="metric-fill" style="width: ${(disciplineScore / 10) * 100}%"></div>
                    </div>
                    <div class="metric-details">
                        Minor: ${employee.details?.discipline?.minor_offenses || 0}<br>
                        Grave: ${employee.details?.discipline?.grave_offenses || 0}
                    </div>
                </div>

                <div class="metric-card performance">
                    <div class="metric-title">Performance</div>
                    <div class="metric-score">${performanceScore.toFixed(1)}</div>
                    <div class="metric-bar">
                        <div class="metric-fill" style="width: ${(performanceScore / 10) * 100}%"></div>
                    </div>
                    <div class="metric-details">
                        Total Evaluation: ${employee.details?.performance?.total || 0}/70
                    </div>
                </div>
            </div>
        </div>

        <div class="factors-section">
            <h2 class="section-title">Key Factors Influencing Promotion Decision</h2>
            <table class="factors-table">
                <thead>
                    <tr>
                        <th>Factor</th>
                        <th>Current Value</th>
                        <th>Impact Score</th>
                        <th>Influence</th>
                    </tr>
                </thead>
                <tbody>
                    ${topFactorsHTML}
                </tbody>
            </table>
        </div>

        <div class="promotion-history">
            <h2 class="section-title">Promotion History</h2>
            ${promotionHistoryHTML}
        </div>

        <div class="report-footer">
            <p>This report was generated by the HR Analytics System using machine learning algorithms.</p>
            <p>Report ID: RPT-${employee.emp_id}-${Date.now()} | Confidential Document</p>
        </div>
    </body>
    </html>
    `;

    // Open new window and print
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printHTML);
    printWindow.document.close();

    printWindow.onload = function () {
        printWindow.print();
        // Optionally close the window after printing
        printWindow.onafterprint = function () {
            printWindow.close();
        };
    };
}

// Export to XLS functionality
$('#export-xlsx-btn').click(function() {
    showExportModal();
});

function showExportModal() {
    // Populate department dropdown
    const departments = [...new Set(allEmployeeData.map(e => e.department))].sort();
    const deptOptions = '<option value="all">All Departments</option>' + 
        departments.map(dept => `<option value="${dept}">${dept}</option>`).join('');
    $('#export-department').html(deptOptions);

    // Update preview count
    updateExportPreview();

    // Show modal
    $('#exportModal').modal('show');
}

$('#export-filter, #export-department').change(function() {
    updateExportPreview();
});

function updateExportPreview() {
    const filteredData = getFilteredExportData();
    const recentCount = getRecentlyAddedCount(filteredData);
    
    $('#export-count').text(filteredData.length);
    $('#export-recent-count').text(recentCount);
}

function getRecentlyAddedCount(data) {
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
    
    return data.filter(emp => {
        if (!emp.created_at && !emp.date_added) return false;
        const dateAdded = new Date(emp.created_at || emp.date_added);
        return dateAdded >= thirtyDaysAgo;
    }).length;
}

function getFilteredExportData() {
    const filterValue = $('#export-filter').val();
    const departmentValue = $('#export-department').val();
    let filteredData = [...allEmployeeData];

    // Apply promotion filter
    switch (filterValue) {
        case 'promoted':
            filteredData = filteredData.filter(e => e.promotion_history && e.promotion_history.length > 0);
            break;
        case 'not-promoted':
            filteredData = filteredData.filter(e => !e.promotion_history || e.promotion_history.length === 0);
            break;
        case 'recently-promoted':
            filteredData = filteredData.filter(e => {
                if (!e.promotion_history || e.promotion_history.length === 0) return false;
                const latestPromo = new Date(e.promotion_history[0].promotion_date);
                const oneYearAgo = new Date();
                oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1);
                return latestPromo > oneYearAgo;
            });
            break;
        case 'frequent-promotions':
            filteredData = filteredData.filter(e => e.promotion_history && e.promotion_history.length >= 3);
            break;
        case 'high-probability':
            filteredData = filteredData.filter(e => e.promotion_probability >= 0.7);
            break;
        case 'medium-probability':
            filteredData = filteredData.filter(e => e.promotion_probability >= 0.4 && e.promotion_probability < 0.7);
            break;
        case 'low-probability':
            filteredData = filteredData.filter(e => e.promotion_probability < 0.4);
            break;
        case 'recently-added':
            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
            filteredData = filteredData.filter(e => {
                if (!e.created_at && !e.date_added) return false;
                const dateAdded = new Date(e.created_at || e.date_added);
                return dateAdded >= thirtyDaysAgo;
            });
            break;
    }

    // Apply department filter
    if (departmentValue !== 'all') {
        filteredData = filteredData.filter(e => e.department === departmentValue);
    }

    // Sort by employee_id in ascending order
    filteredData.sort((a, b) => {
        const idA = String(a.emp_id || '').toUpperCase();
        const idB = String(b.emp_id || '').toUpperCase();
        return idA.localeCompare(idB);
    });

    return filteredData;
}

$('#confirm-export-btn').click(function() {
    exportToXLS();
});

function exportToXLS() {
    const exportData = getFilteredExportData();

    if (exportData.length === 0) {
        alert('No data to export with selected filters');
        return;
    }

    const recentCount = getRecentlyAddedCount(exportData);

    // Prepare data for export
    const xlsData = exportData.map(emp => {
        const isRecent = emp.created_at || emp.date_added ? 
            (new Date(emp.created_at || emp.date_added) >= new Date(Date.now() - 30*24*60*60*1000)) : false;

        return {
            'Employee ID': emp.emp_id || 'N/A',
            'Employee Name': emp.emp_name || 'N/A',
            'Position': emp.position || 'N/A',
            'Department': emp.department || 'N/A',
            'Overall Score': emp.total_score ? parseFloat(emp.total_score).toFixed(2) : '0.00',
            'Promotion Probability': emp.promotion_probability ? (parseFloat(emp.promotion_probability) * 100).toFixed(1) + '%' : '0.0%',
            'Attendance Score': emp.details?.attendance?.score || 0,
            'Tardiness': emp.details?.attendance?.tardiness || 0,
            'Absences': emp.details?.attendance?.absences || 0,
            'Discipline Score': emp.details?.discipline?.score || 0,
            'Minor Offenses': emp.details?.discipline?.minor_offenses || 0,
            'Grave Offenses': emp.details?.discipline?.grave_offenses || 0,
            'Performance Score': emp.details?.performance?.score || 0,
            'Performance Total': emp.details?.performance?.total || 0,
            'Promotion Count': emp.promotion_history ? emp.promotion_history.length : 0,
            'Last Promotion Date': emp.promotion_history && emp.promotion_history.length > 0 
                ? new Date(emp.promotion_history[0].promotion_date).toLocaleDateString() 
                : 'Never',
            'Date Added': emp.created_at || emp.date_added ? 
                new Date(emp.created_at || emp.date_added).toLocaleDateString() : 'N/A',
            'Recently Added': isRecent ? 'YES' : 'NO'
        };
    });

    // Create workbook and worksheet
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.json_to_sheet(xlsData);

    // Set column widths
    ws['!cols'] = [
        { wch: 15 }, { wch: 25 }, { wch: 25 }, { wch: 20 },
        { wch: 15 }, { wch: 20 }, { wch: 18 }, { wch: 12 },
        { wch: 12 }, { wch: 16 }, { wch: 15 }, { wch: 15 },
        { wch: 18 }, { wch: 18 }, { wch: 16 }, { wch: 20 },
        { wch: 15 }, { wch: 15 }
    ];

    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, 'Promotion Analysis');

    // Generate filename
    const timestamp = new Date().toISOString().slice(0, 10);
    const filterType = $('#export-filter').val();
    const filename = `promotion_analysis_${filterType}_${timestamp}.xls`;

    // Download file as XLS (using BIFF8 format)
    XLSX.writeFile(wb, filename, { bookType: 'xls' });

    // Close modal and show success
    $('#exportModal').modal('hide');
    showExportSuccess(exportData.length, recentCount, filename);
}

function showExportSuccess(totalCount, recentCount, filename) {
    const alert = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Export Successful!</strong><br>
            <strong>${totalCount}</strong> total records exported<br>
            <strong>${recentCount}</strong> recently added employees (last 30 days)<br>
            File: <strong>${filename}</strong> (sorted by Employee ID)
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    $('#alert-container').html(alert);
    
    setTimeout(() => {
        $('#alert-container').fadeOut(() => {
            $('#alert-container').html('').show();
        });
    }, 8000);
}