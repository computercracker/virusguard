<?php
include '../backend/db_connection.php';

// Fetch vaccination data
$query = "SELECT * FROM vaccinations";
$result = $conn->query($query);
$vaccinations = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $vaccinations[] = $row;
    }
}

// Count totals for display
$totalVaccinations = count($vaccinations);
$totalPending = count(array_filter($vaccinations, fn($v) => $v['status'] === 'pending'));
$totalCompleted = count(array_filter($vaccinations, fn($v) => $v['status'] === 'completed'));
$totalCanceled = count(array_filter($vaccinations, fn($v) => $v['status'] === 'canceled'));

// Close the connection
$conn->close();

// Function to filter records based on the search term
function filterVaccinations($vaccinations, $searchTerm) {
    return array_filter($vaccinations, function($v) use ($searchTerm) {
        return stripos($v['name'], $searchTerm) !== false ||
               stripos($v['vaccine'], $searchTerm) !== false ||
               stripos($v['status'], $searchTerm) !== false;
    });
}

// Handle search functionality
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$filteredVaccinations = filterVaccinations($vaccinations, $searchTerm);

// Function to download CSV
function downloadCSV($vaccinations) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="vaccination_records.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', 'Vaccine', 'Dose Date', 'Next Dose Due', 'Status', 'Location', 'Administered By']); // Header row

    foreach ($vaccinations as $vaccination) {
        fputcsv($output, $vaccination); // Write each row to the CSV
    }

    fclose($output);
    exit;
}

// Check if download button is clicked
if (isset($_GET['download'])) {
    downloadCSV($filteredVaccinations);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Management Dashboard | VirusGuard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            margin-top: 50px;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            flex: 1;
        }
        h2 {
            color: #007bff;
        }
        footer {
            padding: 10px 0;
            background-color: #007bff;
            color: white;
            position: relative;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Vaccination Management Dashboard</h2>

        <div class="mb-3">
            <form class="form-inline" method="GET">
                <input type="text" name="search" class="form-control mr-2" placeholder="Search..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <button type="submit" name="download" class="btn btn-success ml-2">Download All Data</button>
            </form>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Total Vaccinations</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $totalVaccinations; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">Pending</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $totalPending; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Completed</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $totalCompleted; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-header">Canceled</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $totalCanceled; ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <h4>Vaccination Records</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Vaccine</th>
                    <th>Dose Date</th>
                    <th>Next Dose Due</th>
                    <th>Status</th>
                    <th>Location</th>
                    <th>Administered By</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($filteredVaccinations) > 0): ?>
                    <?php foreach ($filteredVaccinations as $vaccination): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vaccination['name']); ?></td>
                            <td><?php echo htmlspecialchars($vaccination['vaccine']); ?></td>
                            <td><?php echo htmlspecialchars($vaccination['dose_date']); ?></td>
                            <td><?php echo htmlspecialchars($vaccination['next_due']); ?></td>
                            <td><?php echo htmlspecialchars($vaccination['status']); ?></td>
                            <td><?php echo htmlspecialchars($vaccination['location']); ?></td>
                            <td><?php echo htmlspecialchars($vaccination['administered_by']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No vaccination records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <footer class="text-center">
        <p>&copy; 2024 VirusGuard. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
