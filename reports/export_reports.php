<?php
include '../backend/db_connection.php';
include '../backend/auth.php';  
checkAuth();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reports.csv');
$output = fopen('php://output', 'w');

// Add column headings to CSV
fputcsv($output, array('Report ID', 'Name', 'Location', 'Email', 'Phone', 'Symptoms', 'Date Submitted'));

// Fetch reports from the database
$sql = "SELECT id, name, location, email, phone, symptoms, report_date FROM reports"; // Updated SQL to select specific columns
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    // Only include the required fields
    $csv_row = array(
        $row['id'],
        $row['name'],
        $row['location'],
        $row['email'],
        $row['phone'],
        $row['symptoms'],
        $row['report_date']
    );
    fputcsv($output, $csv_row);
}

fclose($output);

// Close the database connection
mysqli_close($conn);
exit();
?>
