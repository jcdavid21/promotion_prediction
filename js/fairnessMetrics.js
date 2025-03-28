document.addEventListener('DOMContentLoaded', function() {
    const ApiURL = 'http://localhost:8800';
    
    fetch(`${ApiURL}/api/fairness_metrics`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                initFairnessDashboard(data.metrics);
            } else {
                showError(data.error || 'Failed to load fairness metrics');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to load fairness data. Please try again later.');
        });

        function initFairnessDashboard(metrics) {
            // 1. Update overview cards
            document.getElementById('overallFairnessScore').textContent = 
                metrics.overall_score?.toFixed(1) || 'N/A';
            document.getElementById('fairnessProgress').style.width = 
                `${metrics.overall_score || 0}%`;
            
            document.getElementById('highestDisparity').textContent = 
                metrics.max_disparity ? (metrics.max_disparity * 100).toFixed(1) + '%' : 'N/A';
            document.getElementById('disparityGroup').innerHTML = 
                metrics.max_disparity_group ? 
                `${metrics.max_disparity_group.dimension}: <span class="demographic-chip">${metrics.max_disparity_group.group}</span>` : 
                'N/A';
            
            document.getElementById('mostBalanced').textContent = 
                metrics.min_disparity ? (metrics.min_disparity * 100).toFixed(1) + '%' : 'N/A';
            document.getElementById('balancedGroup').innerHTML = 
                metrics.min_disparity_group ? 
                `${metrics.min_disparity_group.dimension}: <span class="demographic-chip">${metrics.min_disparity_group.group}</span>` : 
                'N/A';

            // 2. Update fairness indicators
            document.getElementById('equalOpportunity').textContent = 
                metrics.equal_opportunity ? 
                (metrics.equal_opportunity <= 0.1 ? 'Balanced' : 'Review Needed') : 'N/A';
            document.getElementById('equalOpportunity').className = 
                `badge ${metrics.equal_opportunity <= 0.1 ? 'bg-success' : 'bg-warning'} metric-badge`;
            
            document.getElementById('predictiveParity').textContent = 
                metrics.predictive_parity ? 
                (metrics.predictive_parity <= 0.15 ? 'Balanced' : 'Review Needed') : 'N/A';
            document.getElementById('predictiveParity').className = 
                `badge ${metrics.predictive_parity <= 0.15 ? 'bg-success' : 'bg-warning'} metric-badge`;
            
            document.getElementById('falsePositiveBalance').textContent = 
                metrics.false_positive_balance ? 
                (metrics.false_positive_balance <= 0.1 ? 'Balanced' : 'Review Needed') : 'N/A';
            document.getElementById('falsePositiveBalance').className = 
                `badge ${metrics.false_positive_balance <= 0.1 ? 'bg-success' : 'bg-danger'} metric-badge`;

            // 3. Initialize demographic parity charts
            if (metrics.gender && !metrics.gender.error) {
                initParityChart('genderParityChart', metrics.gender);
            } else {
                document.getElementById('gender-tab').style.display = 'none';
            }
            
            if (metrics.age && !metrics.age.error) {
                initParityChart('ageParityChart', metrics.age);
            } else {
                document.getElementById('age-tab').style.display = 'none';
            }
            
            if (metrics.department && !metrics.department.error) {
                initParityChart('deptParityChart', metrics.department);
            } else {
                document.getElementById('department-tab').style.display = 'none';
            }

            // 4. Populate disparity table
            const tableBody = document.getElementById('disparityTableBody');
            tableBody.innerHTML = '';
            
            // Combine all demographic groups
            const allGroups = [
                ...(metrics.gender?.groups?.map(g => ({...g, dimension: 'Gender'})) || []),
                ...(metrics.age?.groups?.map(g => ({...g, dimension: 'Age'})) || []),
                ...(metrics.department?.groups?.map(g => ({...g, dimension: 'Department'})) || [])
            ];
            
            if (allGroups.length > 0) {
                allGroups.forEach(group => {
                    const row = document.createElement('tr');
                    const statusClass = group.parity_diff <= 0.1 ? 'text-success' : 
                                    group.parity_diff <= 0.2 ? 'text-warning' : 'text-danger';
                    
                    row.innerHTML = `
                        <td>${group.dimension}</td>
                        <td><span class="demographic-chip">${group.name}</span></td>
                        <td>${(group.promotion_rate * 100).toFixed(1)}%</td>
                        <td>${group.size}</td>
                        <td>${group.parity_diff.toFixed(3)}</td>
                        <td>${group.impact_ratio.toFixed(2)}</td>
                        <td class="${statusClass}">
                            <i class="fas ${group.parity_diff <= 0.1 ? 'fa-check-circle' : 
                                        group.parity_diff <= 0.2 ? 'fa-exclamation-circle' : 'fa-times-circle'}"></i>
                            ${group.parity_diff <= 0.1 ? 'Fair' : 
                            group.parity_diff <= 0.2 ? 'Warning' : 'Unfair'}
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No disparity data available</td></tr>';
            }

            // 5. Generate recommendations
            const recommendations = document.getElementById('recommendations');
            recommendations.innerHTML = generateRecommendations(metrics);
        }

        function initParityChart(canvasId, data) {
            const ctx = document.getElementById(canvasId);
            if (!ctx) return;
            
            // Add null checks for data and data.groups
            if (!data || !data.groups || !Array.isArray(data.groups)) {
                console.error(`Invalid data structure for ${canvasId}`, data);
                return;
            }

            const groups = data.groups.map(g => g.name);
            const promotionRates = data.groups.map(g => (g.promotion_rate || 0) * 100);
            const colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'];
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: groups,
                    datasets: [{
                        label: 'Promotion Rate (%)',
                        data: promotionRates,
                        backgroundColor: colors,
                        borderColor: colors.map(c => `${c}cc`),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.parsed.y.toFixed(1)}% promotion rate`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Promotion Rate (%)'
                            }
                        }
                    }
                }
            });
            
            // Update metric displays for gender tab only if data exists
            if (canvasId === 'genderParityChart' && data.parity_diff !== undefined && data.impact_ratio !== undefined) {
                document.getElementById('genderParityDiff').textContent = 
                    data.parity_diff.toFixed(3);
                document.getElementById('genderImpactRatio').textContent = 
                    data.impact_ratio.toFixed(2);
            }
        }

    function generateRecommendations(metrics) {
        let html = '';
        
        // 1. Highest priority issues
        if (metrics.max_disparity > 0.25) {
            html += `
                <div class="alert alert-danger mb-3">
                    <strong>Critical Issue:</strong> Large disparity (${(metrics.max_disparity * 100).toFixed(1)}%) 
                    detected in ${metrics.max_disparity_group.dimension} for group "${metrics.max_disparity_group.group}"
                </div>
            `;
        }
        
        // 2. Equal opportunity issues
        if (metrics.equal_opportunity > 0.1) {
            html += `
                <div class="alert alert-warning mb-3">
                    <strong>Opportunity Gap:</strong> True positive rates vary significantly across groups 
                    (max difference: ${(metrics.equal_opportunity * 100).toFixed(1)}%)
                </div>
            `;
        }
        
        // 3. General recommendations
        html += `
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Consider reweighting training samples
                    <span class="badge bg-primary rounded-pill">Pre-process</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Add fairness constraints to model
                    <span class="badge bg-info rounded-pill">In-process</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Review threshold adjustments
                    <span class="badge bg-success rounded-pill">Post-process</span>
                </li>
            </ul>
        `;
        
        return html;
    }

    function showError(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger mt-3';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${message}
        `;
        document.querySelector('.main-content').prepend(alertDiv);
    }
});