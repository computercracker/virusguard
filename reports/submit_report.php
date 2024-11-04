<?php
include '../backend/db_connection.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    // Collect symptoms from checkboxes
    $symptoms = isset($_POST['symptoms']) ? implode(", ", $_POST['symptoms']) : '';

    $sql = "INSERT INTO reports (name, location, email, phone, symptoms) VALUES ('$name', '$location', '$email', '$phone', '$symptoms')";

    if (mysqli_query($conn, $sql)) {
        echo "Report submitted successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report a Case | VirusGuard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Report a Case</h2>
        <form action="submit_report.php" method="POST">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" class="form-control" name="location" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" name="email">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" class="form-control" name="phone" required>
            </div>
            <div class="form-group">
                <label>Symptoms:</label><br>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="symptoms[]" value="Fever" id="symptom1">
                    <label class="form-check-label" for="symptom1">Fever</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="symptoms[]" value="Cough" id="symptom2">
                    <label class="form-check-label" for="symptom2">Cough</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="symptoms[]" value="Fatigue" id="symptom3">
                    <label class="form-check-label" for="symptom3">Fatigue</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="symptoms[]" value="Loss of Taste" id="symptom4">
                    <label class="form-check-label" for="symptom4">Loss of Taste</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="symptoms[]" value="Shortness of Breath" id="symptom5">
                    <label class="form-check-label" for="symptom5">Shortness of Breath</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="symptoms[]" value="Other" id="symptom6">
                    <label class="form-check-label" for="symptom6">Other</label>
                </div>
            </div>
            <button type="submit" class="btn btn-danger">Submit Report</button>
        </form>
    </div>
</body>
</html>
