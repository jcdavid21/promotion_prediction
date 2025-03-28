document.addEventListener('DOMContentLoaded', function() {
    // Global variables
    let allEmployeesData = [];
    let modelMetrics = {};
    let featureImportances = {};
    const ApiURL = 'http://localhost:8800';

    function formatFeatureName(feature) {
        return feature.replace(/_/g, ' ')
                     .replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
    }
    
    // Load data from API
    fetch(`${ApiURL}/api/promotion_predictions`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log("Received data:", data);
            
            if (data.success) {
                allEmployeesData = data.data || [];
                modelMetrics = data.model_metrics || {};
                
                // Extract feature importances from the first employee's SHAP values if not directly available
                if (allEmployeesData.length > 0 && allEmployeesData[0].shap_explanation) {
                    featureImportances = calculateFeatureImportances(allEmployeesData);
                }
                
                // Initialize all components with available data
                if (allEmployeesData.length > 0) {
                    initModelMetrics();
                    initEmployeeSelection();
                    
                    if (Object.keys(featureImportances).length > 0) {
                        initFeatureImportanceChart();
                        initFeatureImpactTable();
                        calculateTopFeaturesPercentage();
                    } else {
                        showWarning('Feature importance data not available - using SHAP values for visualization');
                    }
                } else {
                    showError('No employee data available');
                }
            } else {
                showError(data.error || 'Invalid data format received from server');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to load data. Please try again later.');
        });


    // Calculate feature importances from SHAP values if not provided by server
    function calculateFeatureImportances(employees) {
        const featureImpacts = {};
        const featureCounts = {};
        
        employees.forEach(emp => {
            if (emp.shap_explanation) {
                emp.shap_explanation.features.forEach((feature, index) => {
                    const absImpact = Math.abs(emp.shap_explanation.values[index]);
                    featureImpacts[feature] = (featureImpacts[feature] || 0) + absImpact;
                    featureCounts[feature] = (featureCounts[feature] || 0) + 1;
                });
            }
        });
        
        // Calculate average absolute impact for each feature
        const result = {};
        Object.keys(featureImpacts).forEach(feature => {
            result[feature] = featureImpacts[feature] / featureCounts[feature];
        });
        
        return result;
    }

    // Initialize Feature Importance Chart
    function initFeatureImportanceChart() {
        const ctx = document.getElementById('featureImportanceChart');
        if (!ctx) {
            console.error('Feature importance chart element not found');
            return;
        }

        // Sort features by importance
        const features = Object.keys(featureImportances);
        const importanceValues = features.map(f => featureImportances[f]);
        
        const sortedIndices = [...Array(features.length).keys()]
            .sort((a, b) => importanceValues[b] - importanceValues[a]);
        
        const sortedFeatures = sortedIndices.map(i => formatFeatureName(features[i]));
        const sortedValues = sortedIndices.map(i => importanceValues[i]);
        
        // Create gradient
        const ctx2d = ctx.getContext('2d');
        const gradient = ctx2d.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(58, 123, 213, 0.8)');
        gradient.addColorStop(1, 'rgba(0, 210, 255, 0.6)');
        
        new Chart(ctx2d, {
            type: 'bar',
            data: {
                labels: sortedFeatures,
                datasets: [{
                    label: 'Feature Importance',
                    data: sortedValues,
                    backgroundColor: gradient,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                // Get original feature name (without formatting)
                                const originalFeature = features[sortedIndices[context.dataIndex]];
                                const importance = context.raw.toFixed(4);
                                const percentage = (context.raw * 100 / sortedValues.reduce((a, b) => a + b, 0)).toFixed(2);
                                return `${originalFeature}: ${importance} (${percentage}% of total)`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Importance Score',
                            font: {
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        ticks: {
                            autoSkip: false,
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                animation: {
                    duration: 2000
                }
            }
        });
    }

    // Initialize Model Metrics
    function initModelMetrics() {
        if (modelMetrics.accuracy !== undefined) {
            document.getElementById('accuracyMetric').textContent = (modelMetrics.accuracy * 100).toFixed(1) + '%';
        }
        if (modelMetrics.precision !== undefined) {
            document.getElementById('precisionMetric').textContent = (modelMetrics.precision * 100).toFixed(1) + '%';
        }
        if (modelMetrics.recall !== undefined) {
            document.getElementById('recallMetric').textContent = (modelMetrics.recall * 100).toFixed(1) + '%';
        }
        if (modelMetrics.f1 !== undefined) {
            document.getElementById('f1Metric').textContent = (modelMetrics.f1 * 100).toFixed(1) + '%';
        }
        
        // Additional metadata if available
        if (modelMetrics.training_date) {
            document.getElementById('trainingDate').textContent = modelMetrics.training_date;
        }
        if (modelMetrics.sample_size) {
            document.getElementById('sampleSize').textContent = modelMetrics.sample_size;
        } else if (allEmployeesData.length > 0) {
            document.getElementById('sampleSize').textContent = allEmployeesData.length;
        }
    }

    // Initialize Employee Selection
    function initEmployeeSelection() {
        const select = document.getElementById('employeeSelect');
        select.innerHTML = '';
        
        if (allEmployeesData.length === 0) {
            select.innerHTML = '<option value="">No employees available</option>';
            return;
        }
        
        allEmployeesData.forEach(emp => {
            const option = document.createElement('option');
            option.value = emp.emp_id;
            option.textContent = `${emp.emp_name} (${emp.position || emp.department || 'Unknown'}) - Score: ${emp.total_score?.toFixed(1) || 'N/A'}`;
            select.appendChild(option);
        });
        
        select.addEventListener('change', function() {
            const empId = this.value;
            const employee = allEmployeesData.find(e => e.emp_id == empId);
            
            if (employee) {
                updateEmployeeDetails(employee);
                updateShapPlot(employee);
            }
        });
        
        // Select first employee by default if available
        if (allEmployeesData.length > 0) {
            select.value = allEmployeesData[0].emp_id;
            select.dispatchEvent(new Event('change'));
        }
    }

    // Update Employee Details
    function updateEmployeeDetails(employee) {
        document.getElementById('selectedEmployee').textContent = 
            `${employee.emp_name}`;
        document.getElementById('employeeDept').textContent = 
            employee.dept_name || employee.department || '-';
        document.getElementById('employeePosition').textContent = 
            employee.position_name || employee.position || '-';
        document.getElementById('employeeScore').textContent = 
            employee.total_score?.toFixed(1) || 'N/A';
        
        const prob = employee.promotion_probability;
        const probElement = document.getElementById('promotionProb');
        if (prob !== undefined) {
            probElement.textContent = `${(prob * 100).toFixed(1)}%`;
            
            // Highlight promotion status
            if (prob >= 0.7) {
                probElement.className = 'text-success';
            } else if (prob >= 0.4) {
                probElement.className = 'text-warning';
            } else {
                probElement.className = 'text-danger';
            }
        } else {
            probElement.textContent = 'N/A';
            probElement.className = '';
        }
    }

    // Update SHAP Plot
    function updateShapPlot(employee) {
        const ctx = document.getElementById('shapForcePlot');
        
        // Destroy previous chart if it exists
        if (window.shapChart) {
            window.shapChart.destroy();
        }
        
        if (!employee.shap_explanation) {
            console.error('No SHAP explanation available for this employee');
            return;
        }
        
        // Prepare SHAP data
        const features = employee.shap_explanation.features || [];
        const values = employee.shap_explanation.values || [];
        
        // Sort by absolute value for better visualization
        const sortedIndices = [...Array(features.length).keys()]
            .sort((a, b) => Math.abs(values[b]) - Math.abs(values[a]));
        
        const sortedFeatures = sortedIndices.map(i => formatFeatureName(features[i]));
        const sortedValues = sortedIndices.map(i => values[i]);
        
        // Create new chart
        window.shapChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: sortedFeatures,
                datasets: [{
                    label: 'SHAP Value Impact',
                    data: sortedValues,
                    backgroundColor: sortedValues.map(v => 
                        v >= 0 ? 'rgba(0, 123, 255, 0.7)' : 'rgba(220, 53, 69, 0.7)'),
                    borderColor: sortedValues.map(v => 
                        v >= 0 ? 'rgba(0, 123, 255, 1)' : 'rgba(220, 53, 69, 1)'),
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                // Get original feature name (without formatting)
                                const originalFeature = features[sortedIndices[context.dataIndex]];
                                const impact = context.raw >= 0 ? 'increases' : 'decreases';
                                return `${originalFeature}: ${context.raw.toFixed(4)} (${impact} promotion likelihood)`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'SHAP Value Impact',
                            font: {
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        ticks: {
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Initialize Feature Impact Table
    function initFeatureImpactTable() {
        const tableBody = document.getElementById('featureImpactTable');
        tableBody.innerHTML = '';
        
        if (Object.keys(featureImportances).length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No feature importance data available</td></tr>';
            return;
        }
        
        // Sort features by importance
        const features = Object.keys(featureImportances);
        const sortedFeatures = features.sort((a, b) => featureImportances[b] - featureImportances[a]);
        
        // Calculate average SHAP values across all employees
        const avgShapValues = {};
        sortedFeatures.forEach(feat => {
            const sum = allEmployeesData.reduce((acc, emp) => {
                if (!emp.shap_explanation) return acc;
                const idx = emp.shap_explanation.features.indexOf(feat);
                return acc + (idx >= 0 ? emp.shap_explanation.values[idx] : 0);
            }, 0);
            avgShapValues[feat] = sum / allEmployeesData.length;
        });
        
        // Add rows to table
        sortedFeatures.forEach(feat => {
            const row = document.createElement('tr');
            
            // Highlight top 3 features
            if (sortedFeatures.indexOf(feat) < 3) {
                row.classList.add('table-primary');
            }
            
            const importance = featureImportances[feat];
            const avgImpact = avgShapValues[feat] || 0;
            const direction = avgImpact >= 0 ? 'Positive' : 'Negative';
            
            row.innerHTML = `
                <td><strong>${formatFeatureName(feat)}</strong></td>
                <td>${getFeatureDescription(feat)}</td>
                <td>${avgImpact.toFixed(4)}</td>
                <td>
                    <span class="badge ${direction === 'Positive' ? 'bg-success' : 'bg-danger'}">
                        ${direction}
                    </span>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }
    
    // Get feature description
    function getFeatureDescription(feature) {
        const descriptions = {
            'performance_score': 'Calculated performance rating based on evaluation metrics',
            'attendance_score': 'Composite score based on tardiness and absenteeism',
            'discipline_score': 'Composite score based on disciplinary records',
            'manager_input': 'Manager evaluation score (1-5 scale converted to 0-10)',
            'psa_input': 'PSA evaluation status (NU=10, others=0)',
            'knowledge_of_work': 'Skill rating for job knowledge',
            'quality_of_work': 'Skill rating for work quality',
            'communication': 'Skill rating for communication abilities',
            'team': 'Skill rating for teamwork',
            'decision': 'Skill rating for decision-making',
            'dependability': 'Skill rating for dependability',
            'administration': 'Administrative skills rating',
            'adaptability': 'Adaptability skills rating',
            'leadership': 'Leadership skills rating',
            'customer': 'Customer service skills rating',
            'human_relations': 'Human relations skills rating',
            'tardiness': 'Number of tardiness incidents',
            'tardy': 'Number of tardy incidents',
            'comb_ab_hd': 'Combined absenteeism and half-day incidents',
            'comb_uab_uhd': 'Combined unauthorized absenteeism and half-day incidents',
            'minor': 'Number of minor disciplinary offenses',
            'grave': 'Number of grave disciplinary offenses',
            'suspension': 'Number of suspension incidents'
        };
        return descriptions[feature] || 'No description available';
    }
    
    // Calculate percentage of top features
    function calculateTopFeaturesPercentage() {
        const features = Object.keys(featureImportances);
        if (features.length === 0) return;
        
        const totalImportance = features.reduce((sum, feat) => sum + featureImportances[feat], 0);
        
        // Get top 3 features
        const sortedFeatures = features.sort((a, b) => featureImportances[b] - featureImportances[a]);
        const topFeaturesImportance = sortedFeatures.slice(0, 3).reduce((sum, feat) => sum + featureImportances[feat], 0);
        
        const percentage = (topFeaturesImportance / totalImportance * 100).toFixed(0);
        document.getElementById('topFeaturesPercentage').textContent = `${percentage}%`;
    }
    
    // Show error message
    function showError(message) {
        // Remove any existing error messages first
        const existingAlerts = document.querySelectorAll('.alert.alert-danger');
        existingAlerts.forEach(alert => alert.remove());
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger mt-3';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${message}
        `;
        
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            mainContent.prepend(alertDiv);
        } else {
            document.body.prepend(alertDiv);
        }
    }
    
    // Show warning message
    function showWarning(message) {
        const existingAlerts = document.querySelectorAll('.alert.alert-warning');
        existingAlerts.forEach(alert => alert.remove());
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning mt-3';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>
            ${message}
        `;
        
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            mainContent.prepend(alertDiv);
        }
    }
});