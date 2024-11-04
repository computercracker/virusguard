<?php
include '../backend/db_connection.php';
include '../backend/auth.php';  
checkAuth();

$report_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

$sql = "SELECT * FROM reports WHERE id = '$report_id'";
$result = mysqli_query($conn, $sql);
$report = mysqli_fetch_assoc($result);

if (!$report) {
    echo "Report not found!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Details | VirusGuard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Report Details</h2>

        <p><strong>Name:</strong> <?php echo $report['name']; ?></p>
        <p><strong>Location:</strong> <?php echo $report['location']; ?></p>
        <p><strong>Email:</strong> <?php echo $report['email'] ? $report['email'] : 'N/A'; ?></p> <!-- Added Email -->
        <p><strong>Phone:</strong> <?php echo $report['phone'] ? $report['phone'] : 'N/A'; ?></p> <!-- Added Phone -->
        <p><strong>Symptoms:</strong> <?php echo $report['symptoms']; ?></p>
        <p><strong>Date Submitted:</strong> <?php echo $report['report_date']; ?></p>

        <a href="view_reports.php" class="btn btn-primary">Back to Reports</a>
    </div>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
