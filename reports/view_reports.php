<?php
include '../backend/db_connection.php';
include '../backend/auth.php';  
checkAuth();

// Pagination settings
$results_per_page = 10;  // Number of reports per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Search filter
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$location_filter = isset($_GET['location']) ? mysqli_real_escape_string($conn, $_GET['location']) : '';
$sort_by = isset($_GET['sort']) ? mysqli_real_escape_string($conn, $_GET['sort']) : 'report_date';

// Build SQL query with filters and sorting
$sql = "SELECT * FROM reports WHERE 
        (name LIKE '%$search_query%' OR symptoms LIKE '%$search_query%') 
        AND (location LIKE '%$location_filter%')
        ORDER BY $sort_by DESC
        LIMIT $start_from, $results_per_page";

$result = mysqli_query($conn, $sql);

// Count total number of reports for pagination
$total_reports_sql = "SELECT COUNT(id) AS total FROM reports WHERE 
        (name LIKE '%$search_query%' OR symptoms LIKE '%$search_query%')
        AND (location LIKE '%$location_filter%')";
$total_result = mysqli_query($conn, $total_reports_sql);
$total_reports = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_reports / $results_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports | VirusGuard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Submitted Reports</h2>

        <!-- Search and filter form -->
        <form method="GET" class="form-inline mb-4">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" class="form-control mr-2" placeholder="Search by name or symptoms">
            <input type="text" name="location" value="<?php echo htmlspecialchars($location_filter); ?>" class="form-control mr-2" placeholder="Filter by location">
            <select name="sort" class="form-control mr-2">
                <option value="report_date" <?php echo $sort_by == 'report_date' ? 'selected' : ''; ?>>Sort by Date</option>
                <option value="location" <?php echo $sort_by == 'location' ? 'selected' : ''; ?>>Sort by Location</option>
                <option value="name" <?php echo $sort_by == 'name' ? 'selected' : ''; ?>>Sort by Name</option>
            </select>
            <button type="submit" class="btn btn-primary">Search & Filter</button>
        </form>

        <!-- Reports table -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Report ID</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Email</th> <!-- Added Email Column -->
                    <th>Phone</th> <!-- Added Phone Column -->
                    <th>Symptoms</th>
                    <th>Date Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    $i=1;
                    while($row = mysqli_fetch_assoc($result)) {
                      
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($i) . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";  // Display Email
                        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";  // Display Phone
                        echo "<td>" . htmlspecialchars($row['symptoms']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['report_date']) . "</td>";
                        echo "<td><a href='view_report_details.php?id=" . $i . "' class='btn btn-info btn-sm'>View Details</a></td>";
                        echo "</tr>";
                        $i++;
                    }
                    
                } else {
                    echo "<tr><td colspan='8' class='text-center'>No reports found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php 
                for ($i = 1; $i <= $total_pages; $i++) {
                    echo "<li class='page-item ".($page == $i ? 'active' : '')."'><a class='page-link' href='view_reports.php?page=$i&search=" . urlencode($search_query) . "&location=" . urlencode($location_filter) . "&sort=" . urlencode($sort_by) . "'>$i</a></li>";
                }
                ?>
            </ul>
        </nav>
    </div>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
