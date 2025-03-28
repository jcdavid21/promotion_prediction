let performanceGauge, attendanceGauge, disciplineGauge, managerGauge, psaGauge;
let attendanceChart, disciplineChart;
let dynamicRatings;

// Helper Functions
function getElement(id) {
    const el = document.getElementById(id);
    if (!el) {
        console.warn(`Element with ID ${id} not found`);
        return null;
    }
    return el;
}

function setTextContent(id, text) {
    const el = getElement(id);
    if (el) el.textContent = text;
}

function updateProgressBar(id, value, showValue = true) {
    const progressBar = getElement(id);
    if (progressBar) {
        progressBar.style.width = `${value}%`;
        progressBar.setAttribute('aria-valuenow', value);
        if (showValue) {
            progressBar.textContent = `${value}%`;
        }
    }
}

// Rating Calculation Functions
function getDynamicRatings(all_ratings) {
    if (!all_ratings) {
        console.error('No ratings data provided');
        return {
            evaluation_criteria: [],
            tardiness_rating: [],
            discipline_rating: [],
            performance_rating: []
        };
    }
    
    return {
        evaluation_criteria: all_ratings.evaluation_criteria || [],
        tardiness_rating: all_ratings.tardiness_rating || [],
        discipline_rating: all_ratings.discipline_rating || [],
        performance_rating: all_ratings.performance_rating || []
    };
}

function calculateSkillsRatings(employee) {
    if (!employee) return {
        admin: { score: 0, percentage: 0 },
        knowledge: { score: 0, percentage: 0 },
        quality: { score: 0, percentage: 0 },
        communication: { score: 0, percentage: 0 },
        team: { score: 0, percentage: 0 },
        decision: { score: 0, percentage: 0 },
        dependability: { score: 0, percentage: 0 },
        adaptability: { score: 0, percentage: 0 },
        leadership: { score: 0, percentage: 0 },
        customer: { score: 0, percentage: 0 },
        average: { score: 0, percentage: 0 }
    };
    
    const adminScore = employee.administration || 0;
    const knowledgeScore = employee.knowledge_of_work || 0;
    const qualityScore = employee.quality_of_work || 0;
    const communicationScore = employee.communication || 0;
    const teamScore = employee.team || 0;
    const decisionScore = employee.decision || 0;
    const dependabilityScore = employee.dependability || 0;
    const adaptabilityScore = employee.adaptability || 0;
    const leadershipScore = employee.leadership || 0;
    const customerScore = employee.customer || 0;
    
    const totalSkills = adminScore + knowledgeScore + qualityScore + communicationScore + 
                       teamScore + decisionScore + dependabilityScore + adaptabilityScore + 
                       leadershipScore + customerScore;
    const averageScore = totalSkills / 10;
    
    return {
        admin: { score: adminScore, percentage: (adminScore / 5) * 100 },
        knowledge: { score: knowledgeScore, percentage: (knowledgeScore / 5) * 100 },
        quality: { score: qualityScore, percentage: (qualityScore / 5) * 100 },
        communication: { score: communicationScore, percentage: (communicationScore / 5) * 100 },
        team: { score: teamScore, percentage: (teamScore / 5) * 100 },
        decision: { score: decisionScore, percentage: (decisionScore / 5) * 100 },
        dependability: { score: dependabilityScore, percentage: (dependabilityScore / 5) * 100 },
        adaptability: { score: adaptabilityScore, percentage: (adaptabilityScore / 5) * 100 },
        leadership: { score: leadershipScore, percentage: (leadershipScore / 5) * 100 },
        customer: { score: customerScore, percentage: (customerScore / 5) * 100 },
        average: { score: averageScore, percentage: (averageScore / 5) * 100 }
    };
}

function mapManagerScore(inputScore) {
    if (inputScore === null || inputScore === undefined) return 0;
    return Math.min(10, Math.max(0, Math.round(parseFloat(inputScore) * 2)));
}

function calculatePerformanceRating(employee, dynamicRatings) {
    if (!employee || !dynamicRatings) return { score: 0, percentage: 0 };
    
    const performance = employee.performance || 0;
    const performanceRating = dynamicRatings.performance_rating || [];
    
    for (const rating of performanceRating) {
        const minScore = rating.min_score !== null ? parseFloat(rating.min_score) : -Infinity;
        const maxScore = rating.max_score !== null ? parseFloat(rating.max_score) : Infinity;
        
        if (performance >= minScore && performance <= maxScore) {
            return {
                score: parseFloat(rating.rating),
                percentage: (parseFloat(rating.rating) / 10) * 100
            };
        }
    }
    
    return { score: 0, percentage: 0 };
}

function calculateAttendanceRating(employee, dynamicRatings) {
    if (!employee || !dynamicRatings) return { score: 10, percentage: 100 };
    
    const tardiness = employee.tardiness || 0;
    const tardy = employee.tardy || 0;
    const combAbHd = (employee.AB || 0) + (employee.HD || 0);
    const combUabUhd = (employee.UAB || 0) + (employee.UHD || 0);
    
    const tardinessRating = dynamicRatings.tardiness_rating || [];
    let worstRate = 10;
    
    for (const rating of tardinessRating) {
        const rate = parseFloat(rating.rate);
        const minInstances = rating.min_instances !== null ? parseFloat(rating.min_instances) : -Infinity;
        const maxInstances = rating.max_instances !== null ? parseFloat(rating.max_instances) : Infinity;
        const minAbsenteeism = rating.min_absenteeism !== null ? parseFloat(rating.min_absenteeism) : -Infinity;
        const maxAbsenteeism = rating.max_absenteeism !== null ? parseFloat(rating.max_absenteeism) : Infinity;
        const minUabUhd = rating.min_uab_uhd !== null ? parseFloat(rating.min_uab_uhd) : -Infinity;
        const maxUabUhd = rating.max_uab_uhd !== null ? parseFloat(rating.max_uab_uhd) : Infinity;
        
        if ((tardiness >= minInstances && tardiness <= maxInstances) ||
            (combAbHd >= minAbsenteeism && combAbHd <= maxAbsenteeism) ||
            (combUabUhd >= minUabUhd && combUabUhd <= maxUabUhd)) {
            if (rate < worstRate) {
                worstRate = rate;
            }
        }
    }
    
    return {
        score: worstRate,
        percentage: (worstRate / 10) * 100
    };
}

function calculateDisciplineRating(employee, dynamicRatings) {
    if (!employee || !dynamicRatings) return { score: 10, percentage: 100 };
    
    const minor = employee.minor || 0;
    const grave = employee.grave || 0;
    const suspension = employee.suspension || 0;
    
    const disciplineRating = dynamicRatings.discipline_rating || [];
    let worstRate = 10;
    
    for (const rating of disciplineRating) {
        const rate = parseFloat(rating.rate);
        const minMinor = rating.min_minor !== null ? parseFloat(rating.min_minor) : -Infinity;
        const maxMinor = rating.max_minor !== null ? parseFloat(rating.max_minor) : Infinity;
        const minGrave = rating.min_grave !== null ? parseFloat(rating.min_grave) : -Infinity;
        const maxGrave = rating.max_grave !== null ? parseFloat(rating.max_grave) : Infinity;
        const minSuspension = rating.min_suspension !== null ? parseFloat(rating.min_suspension) : -Infinity;
        const maxSuspension = rating.max_suspension !== null ? parseFloat(rating.max_suspension) : Infinity;
        
        if ((minor >= minMinor && minor <= maxMinor) ||
            (grave >= minGrave && grave <= maxGrave) ||
            (suspension >= minSuspension && suspension <= maxSuspension)) {
            if (rate < worstRate) {
                worstRate = rate;
            }
        }
    }
    
    return {
        score: worstRate,
        percentage: (worstRate / 10) * 100
    };
}

function calculateAllRatings(employee, dynamicRatings) {
    if (!employee || !dynamicRatings) {
        return {
            attendance: { score: 0, percentage: 0 },
            discipline: { score: 0, percentage: 0 },
            performance: { score: 0, percentage: 0 },
            mingrInput: { score: 0, percentage: 0 },
            psaInput: { score: 0, percentage: 0 },
            skills: { score: 0, percentage: 0 },
            totalRating: 0
        };
    }
    
    // Get weights from evaluation criteria
    const getWeight = (category) => {
        const criteria = dynamicRatings.evaluation_criteria.find(c => c.category === category);
        return criteria ? parseFloat(criteria.weight) / 100 : 0;
    };
    
    const attendanceWeight = getWeight("ATTENDANCE");
    const disciplineWeight = getWeight("DISCIPLINE");
    const performanceWeight = getWeight("PERFORMANCE EVAL");
    const mingrInputWeight = getWeight("MNGR INPUT");
    const psaInputWeight = getWeight("PSA INPUT");
    
    // Calculate component scores
    const attendance = calculateAttendanceRating(employee, dynamicRatings);
    const discipline = calculateDisciplineRating(employee, dynamicRatings);
    const performance = calculatePerformanceRating(employee, dynamicRatings);
    
    const mingrInput = {
        score: mapManagerScore(employee.manager_input),
        percentage: (mapManagerScore(employee.manager_input) / 10) * 100
    };
    
    const psaInput = {
        score: employee.psa_input === 'NU' ? 10 : 0,
        percentage: employee.psa_input === 'NU' ? 100 : 0
    };
    
    const skills = calculateSkillsRatings(employee);
    
    // Calculate total weighted score (matches Python backend calculation)
    const totalRating = (
        (attendance.score * attendanceWeight) +
        (discipline.score * disciplineWeight) +
        (performance.score * performanceWeight) +
        (mingrInput.score * mingrInputWeight) +
        (psaInput.score * psaInputWeight)
    );
    
    return {
        attendance,
        discipline,
        performance,
        mingrInput,
        psaInput,
        skills,
        totalRating
    };
}

// Display Functions
function updateSkillsDisplay(skillsRatings) {
    updateProgressBar('adminProgress', skillsRatings.admin.percentage);
    setTextContent('adminScore', `${skillsRatings.admin.score} (${Math.round(skillsRatings.admin.percentage)}%)`);
    
    updateProgressBar('knowledgeProgress', skillsRatings.knowledge.percentage);
    setTextContent('knowledgeScore', `${skillsRatings.knowledge.score} (${Math.round(skillsRatings.knowledge.percentage)}%)`);
    
    updateProgressBar('qualityProgress', skillsRatings.quality.percentage);
    setTextContent('qualityScore', `${skillsRatings.quality.score} (${Math.round(skillsRatings.quality.percentage)}%)`);
    
    updateProgressBar('communicationProgress', skillsRatings.communication.percentage);
    setTextContent('communicationScore', `${skillsRatings.communication.score} (${Math.round(skillsRatings.communication.percentage)}%)`);
    
    updateProgressBar('teamProgress', skillsRatings.team.percentage);
    setTextContent('teamScore', `${skillsRatings.team.score} (${Math.round(skillsRatings.team.percentage)}%)`);
    
    updateProgressBar('decisionProgress', skillsRatings.decision.percentage);
    setTextContent('decisionScore', `${skillsRatings.decision.score} (${Math.round(skillsRatings.decision.percentage)}%)`);
    
    updateProgressBar('dependabilityProgress', skillsRatings.dependability.percentage);
    setTextContent('dependabilityScore', `${skillsRatings.dependability.score} (${Math.round(skillsRatings.dependability.percentage)}%)`);
    
    updateProgressBar('adaptabilityProgress', skillsRatings.adaptability.percentage);
    setTextContent('adaptabilityScore', `${skillsRatings.adaptability.score} (${Math.round(skillsRatings.adaptability.percentage)}%)`);
    
    updateProgressBar('leadershipProgress', skillsRatings.leadership.percentage);
    updateProgressBar('customerProgress', skillsRatings.customer.percentage);
    setTextContent('customerScore', `${skillsRatings.customer.score} (${Math.round(skillsRatings.customer.percentage)}%)`);
    
    setTextContent('skillsAverageScore', `${skillsRatings.average.score.toFixed(1)} (${Math.round(skillsRatings.average.percentage)}%)`);
}

function updateMetrics(employeeId, employeeData) {
    if (!employeeId || !employeeData || !employeeData[employeeId] || !dynamicRatings) {
        console.error('Invalid employee data or ratings not loaded');
        return;
    }
    
    const employee = employeeData[employeeId];
    const ratings = calculateAllRatings(employee, dynamicRatings);
    
    setTextContent('performanceScore', `(${Math.round(ratings.performance.percentage)}%)`);
    setTextContent('attendanceScore', ` (${Math.round(ratings.attendance.percentage)}%)`);
    setTextContent('disciplineScore', ` (${Math.round(ratings.discipline.percentage)}%)`);
    setTextContent('managerScore', ` (${Math.round(ratings.mingrInput.percentage)}%)`);
    setTextContent('psaScore', `(${Math.round(ratings.psaInput.percentage)}%)`);
    setTextContent('totalScore', Math.round(ratings.totalRating));
    
    if (performanceGauge) updateGauge(performanceGauge, ratings.performance.percentage, 100, ['#ff0000', '#ffff00', '#00ff00']);
    if (attendanceGauge) updateGauge(attendanceGauge, ratings.attendance.percentage, 100, ['#ff0000', '#ffff00', '#00ff00']);
    if (disciplineGauge) updateGauge(disciplineGauge, ratings.discipline.percentage, 100, ['#ff0000', '#ffff00', '#00ff00']);
    if (managerGauge) updateGauge(managerGauge, ratings.mingrInput.percentage, 100, ['#ff0000', '#ffff00', '#00ff00']);
    if (psaGauge) updateGauge(psaGauge, ratings.psaInput.percentage, 100, ['#ff0000', '#ffff00', '#00ff00']);
    
    setTextContent('tardinessValue', employee.tardiness || 0);
    setTextContent('tardyValue', employee.tardy || 0);
    setTextContent('absenceValue', (employee.AB || 0) + (employee.HD || 0));
    setTextContent('halfdayValue', (employee.UAB || 0) + (employee.UHD || 0));
    setTextContent('minorValue', employee.minor || 0);
    setTextContent('graveValue', employee.grave || 0);
    setTextContent('highlightText', employee.highlight || 'No highlights');
    setTextContent('lowlightText', employee.lowlight || 'No lowlights');
    
    updateSkillsDisplay(ratings.skills);
    
    if (attendanceChart) updateAttendanceChart(employee);
    if (disciplineChart) updateDisciplineChart(employee);
}

// Chart Functions (remain the same as before)
function createGaugeChart(ctxId, maxValue, colors) {
    const canvas = getElement(ctxId);
    if (!canvas) return null;
    
    try {
        return new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [maxValue, 0],
                    backgroundColor: [colors[0], '#f5f5f5'],
                    borderWidth: 0
                }]
            },
            options: {
                circumference: 180,
                rotation: 270,
                cutout: '80%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    } catch (error) {
        console.error(`Failed to create gauge chart on ${ctxId}:`, error);
        return null;
    }
}

function updateGauge(chart, value, maxValue, colors) {
    if (!chart) return;
    
    let color;
    if (value < maxValue * 0.4) color = colors[0];
    else if (value < maxValue * 0.7) color = colors[1];
    else color = colors[2];
    
    chart.data.datasets[0].data = [value, maxValue - value];
    chart.data.datasets[0].backgroundColor = [color, '#f5f5f5'];
    chart.update();
}

function createAttendanceChart() {
    const canvas = getElement('attendanceChart');
    if (!canvas) return null;
    
    try {
        return new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Tardiness', 'Tardy', 'Absences', 'Half-days'],
                datasets: [{
                    label: 'Attendance Issues',
                    backgroundColor: ['#dc3545', '#ffc107', '#0dcaf0', '#198754'],
                    borderColor: ['#dc3545', '#ffc107', '#0dcaf0', '#198754'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    } catch (error) {
        console.error('Failed to create attendance chart:', error);
        return null;
    }
}

function updateAttendanceChart(employee) {
    if (!attendanceChart || !employee) return;
    
    attendanceChart.data.datasets[0].data = [
        employee.tardiness || 0,
        employee.tardy || 0,
        (employee.AB || 0) + (employee.HD || 0),
        (employee.UAB || 0) + (employee.UHD || 0)
    ];
    attendanceChart.update();
}

function createDisciplineChart() {
    const canvas = getElement('disciplineChart');
    if (!canvas) return null;
    
    try {
        return new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Minor', 'Grave', 'Suspensions'],
                datasets: [{
                    label: 'Disciplinary Issues',
                    backgroundColor: ['#ffc107', '#dc3545', '#6610f2'],
                    borderColor: ['#ffc107', '#dc3545', '#6610f2'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    } catch (error) {
        console.error('Failed to create discipline chart:', error);
        return null;
    }
}

function updateDisciplineChart(employee) {
    if (!disciplineChart || !employee) return;
    
    disciplineChart.data.datasets[0].data = [
        employee.minor || 0,
        employee.grave || 0,
        employee.suspension || 0
    ];
    disciplineChart.update();
}

// Employee Management Functions
function calculateOverallScore(employee) {
    if (!employee) return 0;
    const ratings = calculateAllRatings(employee, dynamicRatings);
    return Math.round(ratings.totalRating);
}

function displayTopEmployees(employeeData) {
    if (!employeeData) {
        console.error('No employee data provided for top employees');
        return;
    }
    
    const tableBody = getElement('topEmployeesBody');
    if (!tableBody) return;
    
    const employeesArray = Object.entries(employeeData)
        .map(([id, emp]) => ({
            id,
            ...emp,
            overallScore: calculateOverallScore(emp)
        }))
        .sort((a, b) => b.overallScore - a.overallScore);
    
    const topEmployees = employeesArray.slice(0, 5);
    tableBody.innerHTML = '';
    
    topEmployees.forEach((emp, index) => {
        const row = document.createElement('tr');
        if (getElement('employeeSelect')?.value === emp.id) {
            row.classList.add('table-primary');
        }
        
        const ratings = calculateAllRatings(emp, dynamicRatings);
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${emp.emp_name || 'Unknown'}</td>
            <td>${ratings.performance.score}</td>
            <td>${ratings.attendance.score}</td>
            <td>${ratings.discipline.score}</td>
            <td>${emp.manager_input || 'N/A'}</td>
            <td><strong>${emp.overallScore}</strong></td>
        `;
        
        row.addEventListener('click', () => {
            const select = getElement('employeeSelect');
            if (select) {
                select.value = emp.id;
                select.dispatchEvent(new Event('change'));
            }
        });
        
        tableBody.appendChild(row);
    });
}

function setupSearch(employeeData) {
    const searchInput = getElement('employeeSearch');
    const searchButton = getElement('searchButton');
    const clearSearch = getElement('clearSearch');
    const employeeSelect = getElement('employeeSelect');
    
    if (!searchInput || !employeeSelect || !employeeData) return;
    
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        if (!searchTerm) {
            populateEmployeeDropdown(employeeData);
            return;
        }
        
        const filteredEmployees = Object.entries(employeeData)
            .filter(([id, emp]) => 
                emp.emp_name && emp.emp_name.toLowerCase().includes(searchTerm))
            .reduce((acc, [id, emp]) => {
                acc[id] = emp;
                return acc;
            }, {});
        
        populateEmployeeDropdown(filteredEmployees);
    }
    
    function populateEmployeeDropdown(data) {
        if (!employeeSelect) return;
        
        employeeSelect.innerHTML = '';
        
        const sortedEmployees = Object.entries(data)
            .map(([id, emp]) => ({ id, ...emp }))
            .sort((a, b) => (a.emp_name || '').localeCompare(b.emp_name || ''));
        
        if (sortedEmployees.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No employees found';
            employeeSelect.appendChild(option);
            employeeSelect.disabled = true;
            return;
        }
        
        employeeSelect.disabled = false;
        
        sortedEmployees.forEach(employee => {
            const option = document.createElement('option');
            option.value = employee.id;
            option.textContent = employee.emp_name || 'Unknown Employee';
            employeeSelect.appendChild(option);
        });
        
        if (sortedEmployees.length > 0) {
            employeeSelect.value = sortedEmployees[0].id;
            updateMetrics(sortedEmployees[0].id, employeeData);
        }
    }
    
    if (searchButton) searchButton.addEventListener('click', performSearch);
    if (clearSearch) clearSearch.addEventListener('click', () => {
        searchInput.value = '';
        populateEmployeeDropdown(employeeData);
    });
    
    searchInput.addEventListener('keyup', (e) => {
        if (e.key === 'Enter') performSearch();
    });
}

// Initialization
function initializeDashboard(employeeData, all_ratings) {
    if (!employeeData) {
        console.error('No employee data provided for dashboard initialization');
        return;
    }
    
    try {
        dynamicRatings = getDynamicRatings(all_ratings);
        console.log('Dynamic ratings initialized:', dynamicRatings);
        
        if (!dynamicRatings.evaluation_criteria || !dynamicRatings.tardiness_rating || 
            !dynamicRatings.discipline_rating || !dynamicRatings.performance_rating) {
            console.warn('Incomplete ratings data - some features may not work properly');
        }
        
        performanceGauge = createGaugeChart('performanceGauge', 100, ['#ff0000', '#ffff00', '#00ff00']);
        attendanceGauge = createGaugeChart('attendanceGauge', 100, ['#ff0000', '#ffff00', '#00ff00']);
        disciplineGauge = createGaugeChart('disciplineGauge', 100, ['#ff0000', '#ffff00', '#00ff00']);
        managerGauge = createGaugeChart('managerGauge', 100, ['#ff0000', '#ffff00', '#00ff00']);
        psaGauge = createGaugeChart('psaGauge', 100, ['#ff0000', '#ffff00', '#00ff00']);
        
        attendanceChart = createAttendanceChart();
        disciplineChart = createDisciplineChart();
        
        setupSearch(employeeData);
        
        const employeeSelect = getElement('employeeSelect');
        if (employeeSelect) {
            const sortedEmployees = Object.entries(employeeData)
                .map(([id, emp]) => ({ id, ...emp }))
                .sort((a, b) => (a.emp_name || '').localeCompare(b.emp_name || ''));
            
            sortedEmployees.forEach(employee => {
                const option = document.createElement('option');
                option.value = employee.id;
                option.textContent = employee.emp_name || 'Unknown Employee';
                employeeSelect.appendChild(option);
            });
            
            employeeSelect.addEventListener('change', function() {
                updateMetrics(this.value, employeeData);
                displayTopEmployees(employeeData);
            });
            
            const employeesArray = Object.entries(employeeData)
                .map(([id, emp]) => ({
                    id,
                    ...emp,
                    overallScore: calculateOverallScore(emp)
                }))
                .sort((a, b) => b.overallScore - a.overallScore);
            
            const topEmployeeId = employeesArray[0]?.id || sortedEmployees[0]?.id;
            if (topEmployeeId) {
                employeeSelect.value = topEmployeeId;
                updateMetrics(topEmployeeId, employeeData);
                displayTopEmployees(employeeData);
            }
        }
    } catch (error) {
        console.error('Dashboard initialization failed:', error);
    }
}