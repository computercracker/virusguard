<?php 
include 'backend/auth.php'; 
checkAuth(); 

include 'backend/db_connection.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get total reports
$reportsCountResult = $conn->query("SELECT COUNT(*) AS total FROM reports");
$reportsCount = $reportsCountResult->fetch_assoc()['total'];

// Get total vaccinations
$vaccineCountResult = $conn->query("SELECT COUNT(*) AS total FROM vaccinations");
$vaccineCount = $vaccineCountResult->fetch_assoc()['total'];

// Get total users (optional)
$usersCountResult = $conn->query("SELECT COUNT(*) AS total FROM users");
$usersCount = $usersCountResult->fetch_assoc()['total'];

// Get pending alerts
$pendingAlertsResult = $conn->query("SELECT COUNT(*) AS total FROM alerts WHERE status = 'pending'");
$pendingAlerts = $pendingAlertsResult->fetch_assoc()['total'];

// Get vaccination counts grouped by month
$vaccineMonthlyCountResult = $conn->query("
    SELECT MONTH(dose_date) AS month, COUNT(*) AS total
    FROM vaccinations
    WHERE YEAR(dose_date) = YEAR(CURRENT_DATE)
    GROUP BY MONTH(dose_date)
");
$vaccineMonthlyCounts = [];
while ($row = $vaccineMonthlyCountResult->fetch_assoc()) {
    $vaccineMonthlyCounts[$row['month']] = $row['total'];
}

// Prepare labels and data arrays for chart
$labels = [];
$data = [];
for ($month = 1; $month <= 12; $month++) {
    $labels[] = date('F', mktime(0, 0, 0, $month, 1)); // Month names
    $data[] = isset($vaccineMonthlyCounts[$month]) ? $vaccineMonthlyCounts[$month] : 0; // Vaccination count
}

// Calculate percentage of vaccinations for each month
$totalVaccinationsThisYear = array_sum($data);
$percentages = [];
for ($month = 1; $month <= 12; $month++) {
    $percentages[] = $totalVaccinationsThisYear > 0 ? ($data[$month - 1] / $totalVaccinationsThisYear) * 100 : 0;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | VirusGuard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .header {
            background-color: #007bff;
            color: white;
        }
        .dashboard-title {
            text-align: center;
            margin: 30px 0;
            font-weight: bold;
        }
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: scale(1.05);
        }
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            background-color: #007bff;
            color: white;
        }
        .btn {
            font-size: 1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
                <a class="navbar-brand" href="#">VirusGuard</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                User Profile
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#">Settings</a>
                                <a class="dropdown-item" href="backend/logout.php">Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>
    <div class="container mt-4">
        <h2 class="dashboard-title">Welcome to VirusGuard</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Reports</h5>
                        <h2 class="card-text"><?php echo $reportsCount; ?></h2>
                        <a href="reports/view_reports.php" class="btn btn-primary">View Reports <i class="fas fa-file-alt"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Vaccinations</h5>
                        <h2 class="card-text"><?php echo round(($vaccineCount / ($reportsCount ? $reportsCount : 1)) * 100) . '%'; ?></h2>
                        <a href="vaccination/vaccine_dashboard.php" class="btn btn-primary">Track Vaccinations <i class="fas fa-syringe"></i></a>
                    </div>
                </div>
            </div>
            <!-- User Management Card -->
            <div class="col-md-4 mb-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">User Management</h5>
                        <h2 class="card-text"><?php echo $usersCount; ?></h2>
                        <a href="user/user_management.php" class="btn btn-primary">Manage Users <i class="fas fa-users"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <canvas id="myChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2024 VirusGuard, All Rights Reserved</p>
    </footer>
    
    <!-- Required JS Files -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Chart.js Script -->
    <script>
        var ctx = document.getElementById('myChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar', // You can change this to 'line' if you prefer
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Vaccinations',
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'Percentage of Vaccinations',
                    data: <?php echo json_encode($percentages); ?>,
                    type: 'line',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Vaccination Progress Over the Year'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
