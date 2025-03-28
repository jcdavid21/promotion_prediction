let employeeDataTable = null;
let currentEmployeeData = [];
let currentThreshold = 0;
let allEmployeeData = [];

$(document).ready(function() {
    initializeDataTable();
    loadPromotionPredictions();

    $('#refresh-btn').click(function() {
        loadPromotionPredictions();
    });

    $('#promotion-filter').change(function() {
        applyPromotionFilter();
    });

    $('#promotionTable').on('click', '.view-details', function() {
        const empId = $(this).data('id');
        const employee = allEmployeeData.find(e => e.emp_id == empId);
        if (employee) {
            showShapDetails(employee);
        }
    });
});

function applyPromotionFilter() {
    const filterValue = $('#promotion-filter').val();
    let filteredData = [...allEmployeeData];

    switch(filterValue) {
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
            employeeDataTable.rows().every(function() {
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
                render: function(data, type, row) {
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
                render: function(data) {
                    return data ? parseFloat(data).toFixed(2) : 'N/A';
                }
            },
            { 
                title: "Promotion Probability", 
                data: "promotion_probability",
                render: function(data) {
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
                render: function(data, type, row) {
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
        success: function(response) {
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
        error: function(xhr, status, error) {
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
            `${Math.floor(promo.days_since_promotion/30)} months` : 'Current role';
        
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
        <strong>${(impactScore/30).toFixed(1)}x</strong> more likely to be promoted.
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