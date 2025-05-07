<?php  if(session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>
<?php
require_once("../backend/config.php");

// Initialize error variable
$error = null;
$success = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'] ?? '';
    $id = $_POST['id'] ?? 0;

    // Get all data fields except 'table' and 'id'
    $data = $_POST;
    unset($data['table']);
    unset($data['id']);

    if ($table && $id) {
        try {
            $conn->autocommit(FALSE); 
            
            $setParts = [];
            $params = [];
            $types = '';
            
            foreach ($data as $field => $value) {
                if (!in_array($field, ['submit', 'csrf_token'])) {
                    $setParts[] = "`$field` = ?";
                    $params[] = $value === '' ? null : $value;
                    
                    if (is_int($value)) {
                        $types .= 'i';
                    } elseif (is_float($value)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                }
            }
            
            $params[] = $id;
            $types .= 'i'; 

            switch ($table) {
                case 'discipline_grave':
                    $sql = "UPDATE Discipline_Grave SET " . implode(', ', $setParts) . " WHERE level_id = ?";
                    break;
                case 'evaluation_criteria':
                    $sql = "UPDATE Evaluation_Criteria SET " . implode(', ', $setParts) . " WHERE criteria_id = ?";
                    break;
                case 'performance_rating_scale':
                    $sql = "UPDATE Performance_Rating_Scale SET " . implode(', ', $setParts) . " WHERE scale_id = ?";
                    break;
                case 'tardiness_rating':
                    $sql = "UPDATE Tardiness_Rating SET " . implode(', ', $setParts) . " WHERE rate_id = ?";
                    break;
                default:
                    throw new Exception("Invalid table specified");
            }

            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $conn->commit();
            $success = "Record updated successfully!";
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error: " . $e->getMessage();
        } finally {
            $conn->autocommit(TRUE); // Restore autocommit
            if ($stmt) $stmt->close();
        }
    } else {
        $error = "Table and ID are required";
    }
}

function fetchTableData($conn, $table) {
    $data = [];
    $query = "SELECT * FROM $table ORDER BY " . ($table === 'Performance_Rating_Scale' ? 'rating DESC' : ($table === 'Tardiness_Rating' ? 'rate DESC' : ($table === 'Evaluation_Criteria' ? 'criteria_id' : 'level_id')));
    
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Error fetching $table data: " . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

try {
    $disciplineData = fetchTableData($conn, 'Discipline_Grave');
    $evaluationData = fetchTableData($conn, 'Evaluation_Criteria');
    $ratingData = fetchTableData($conn, 'Performance_Rating_Scale');
    $tardinessData = fetchTableData($conn, 'Tardiness_Rating');
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criteria Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .tab-content {
            padding: 20px;
            border-left: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            border-radius: 0 0 5px 5px;
        }

        .nav-tabs .nav-link.active {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .criteria-table th {
            white-space: nowrap;
            vertical-align: middle;
        }

        .criteria-table td {
            vertical-align: middle;
        }

        .form-control-sm {
            min-width: 70px;
        }
        
        .btn-save {
            white-space: nowrap;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <?php include "sidebar.php"; ?>
        <h2 class="mb-4">Evaluation Criteria Management</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <ul class="nav nav-tabs" id="criteriaTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="discipline-tab" data-bs-toggle="tab" data-bs-target="#discipline" type="button" role="tab">Discipline Grave</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="evaluation-tab" data-bs-toggle="tab" data-bs-target="#evaluation" type="button" role="tab">Evaluation Criteria</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rating-tab" data-bs-toggle="tab" data-bs-target="#rating" type="button" role="tab">Performance Rating Scale</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tardiness-tab" data-bs-toggle="tab" data-bs-target="#tardiness" type="button" role="tab">Tardiness Rating</button>
            </li>
        </ul>

        <div class="tab-content" id="criteriaTabContent">
            <!-- Discipline Grave Tab -->
            <div class="tab-pane fade show active" id="discipline" role="tabpanel" aria-labelledby="discipline-tab">
                <h4 class="mb-3">Discipline Grave Criteria</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered criteria-table">
                        <thead class="table-dark">
                            <tr>
                                <th>Level ID</th>
                                <th>Min Minor</th>
                                <th>Max Minor</th>
                                <th>Min Grave</th>
                                <th>Max Grave</th>
                                <th>Min Suspension</th>
                                <th>Max Suspension</th>
                                <th>Rate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($disciplineData as $row): ?>
                            <form method="post">
                                <input type="hidden" name="table" value="discipline_grave">
                                <input type="hidden" name="id" value="<?php echo $row['level_id']; ?>">
                                <tr>
                                    <td><?php echo htmlspecialchars($row['level_id']); ?></td>
                                    <td><input type="number" class="form-control form-control-sm" name="min_minor" value="<?php echo htmlspecialchars($row['min_minor']); ?>" required></td>
                                    <td><input type="number" class="form-control form-control-sm" name="max_minor" value="<?php echo htmlspecialchars($row['max_minor'] ?? ''); ?>"></td>
                                    <td><input type="number" class="form-control form-control-sm" name="min_grave" value="<?php echo htmlspecialchars($row['min_grave']); ?>" required></td>
                                    <td><input type="number" class="form-control form-control-sm" name="max_grave" value="<?php echo htmlspecialchars($row['max_grave'] ?? ''); ?>"></td>
                                    <td><input type="number" class="form-control form-control-sm" name="min_suspension" value="<?php echo htmlspecialchars($row['min_suspension']); ?>" required></td>
                                    <td><input type="number" class="form-control form-control-sm" name="max_suspension" value="<?php echo htmlspecialchars($row['max_suspension'] ?? ''); ?>"></td>
                                    <td><input type="number" class="form-control form-control-sm" name="rate" value="<?php echo htmlspecialchars($row['rate']); ?>" required></td>
                                    <td><button type="submit" class="btn btn-sm btn-primary btn-save"><i class="fas fa-save"></i> Save</button></td>
                                </tr>
                            </form>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Evaluation Criteria Tab -->
            <div class="tab-pane fade" id="evaluation" role="tabpanel" aria-labelledby="evaluation-tab">
                <h4 class="mb-3">Evaluation Criteria</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered criteria-table">
                        <thead class="table-dark">
                            <tr>
                                <th>Criteria ID</th>
                                <th>Category</th>
                                <th>Weight</th>
                                <th>Scale</th>
                                <th>Score</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($evaluationData as $row): ?>
                            <form method="post">
                                <input type="hidden" name="table" value="evaluation_criteria">
                                <input type="hidden" name="id" value="<?php echo $row['criteria_id']; ?>">
                                <tr>
                                    <td><?php echo htmlspecialchars($row['criteria_id']); ?></td>
                                    <td><input type="text" class="form-control form-control-sm" name="category" value="<?php echo htmlspecialchars($row['category']); ?>" required></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm" name="weight" value="<?php echo htmlspecialchars($row['weight']); ?>" required></td>
                                    <td><input type="number" class="form-control form-control-sm" name="scale" value="<?php echo htmlspecialchars($row['scale']); ?>" required></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm" name="score" value="<?php echo htmlspecialchars($row['score'] ?? ''); ?>"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm" name="rating" value="<?php echo htmlspecialchars($row['rating'] ?? ''); ?>"></td>
                                    <td><button type="submit" class="btn btn-sm btn-primary btn-save"><i class="fas fa-save"></i> Save</button></td>
                                </tr>
                            </form>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Performance Rating Scale Tab -->
            <div class="tab-pane fade" id="rating" role="tabpanel" aria-labelledby="rating-tab">
                <h4 class="mb-3">Performance Rating Scale</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered criteria-table">
                        <thead class="table-dark">
                            <tr>
                                <th>Scale ID</th>
                                <th>Min Score</th>
                                <th>Max Score</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ratingData as $row): ?>
                            <form method="post">
                                <input type="hidden" name="table" value="performance_rating_scale">
                                <input type="hidden" name="id" value="<?php echo $row['scale_id']; ?>">
                                <tr>
                                    <td><?php echo htmlspecialchars($row['scale_id']); ?></td>
                                    <td><input type="number" class="form-control form-control-sm" name="min_score" value="<?php echo htmlspecialchars($row['min_score'] ?? ''); ?>"></td>
                                    <td><input type="number" class="form-control form-control-sm" name="max_score" value="<?php echo htmlspecialchars($row['max_score'] ?? ''); ?>"></td>
                                    <td><input type="number" class="form-control form-control-sm" name="rating" value="<?php echo htmlspecialchars($row['rating']); ?>" required></td>
                                    <td><button type="submit" class="btn btn-sm btn-primary btn-save"><i class="fas fa-save"></i> Save</button></td>
                                </tr>
                            </form>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tardiness Rating Tab -->
            <div class="tab-pane fade" id="tardiness" role="tabpanel" aria-labelledby="tardiness-tab">
                <h4 class="mb-3">Tardiness Rating</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered criteria-table">
                        <thead class="table-dark">
                            <tr>
                                <th>Rate ID</th>
                                <th>Rate</th>
                                <th>Min Instances</th>
                                <th>Max Instances</th>
                                <th>Min Minutes</th>
                                <th>Max Minutes</th>
                                <th>Min Absenteeism</th>
                                <th>Max Absenteeism</th>
                                <th>Min UAB/UHD</th>
                                <th>Max UAB/UHD</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tardinessData as $row): ?>
                            <form method="post">
                                <input type="hidden" name="table" value="tardiness_rating">
                                <input type="hidden" name="id" value="<?php echo $row['rate_id']; ?>">
                                <tr>
                                    <td><?php echo htmlspecialchars($row['rate_id']); ?></td>
                                    <td><input type="number" class="form-control form-control-sm" name="rate" value="<?php echo htmlspecialchars($row['rate']); ?>" required></td>
                                    <td><input type="number" class="form-control form-control-sm" name="min_instances" value="<?php echo htmlspecialchars($row['min_instances']); ?>" required></td>
                                    <td><input type="number" class="form-control form-control-sm" name="max_instances" value="<?php echo htmlspecialchars($row['max_instances'] ?? ''); ?>"></td>
                                    <td><input type="number" class="form-control form-control-sm" name="min_minutes" value="<?php echo htmlspecialchars($row['min_minutes']); ?>" required></td>
                                    <td><input type="number" class="form-control form-control-sm" name="max_minutes" value="<?php echo htmlspecialchars($row['max_minutes'] ?? ''); ?>"></td>
                                    <td><input type="number" class="form-control form-control-sm" name="min_absenteeism" value="<?php echo htmlspecialchars($row['min_absenteeism']); ?>" required></td>
                                    <td><input type="number" class="form-control form-control-sm" name="max_absenteeism" value="<?php echo htmlspecialchars($row['max_absenteeism'] ?? ''); ?>"></td>
                                    <td><input type="number" class="form-control form-control-sm" name="min_uab_uhd" value="<?php echo htmlspecialchars($row['min_uab_uhd']); ?>" required></td>
                                    <td><input type="number" class="form-control form-control-sm" name="max_uab_uhd" value="<?php echo htmlspecialchars($row['max_uab_uhd'] ?? ''); ?>"></td>
                                    <td><button type="submit" class="btn btn-sm btn-primary btn-save"><i class="fas fa-save"></i> Save</button></td>
                                </tr>
                            </form>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activate the current tab when page reloads
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash) {
                const tabTrigger = new bootstrap.Tab(document.querySelector(window.location.hash + '-tab'));
                tabTrigger.show();
            }
            
            // Update URL hash when tab changes
            document.querySelectorAll('#criteriaTabs button').forEach(tab => {
                tab.addEventListener('click', function() {
                    window.location.hash = this.getAttribute('data-bs-target').replace('#', '');
                });
            });
        });
    </script>
</body>
</html>