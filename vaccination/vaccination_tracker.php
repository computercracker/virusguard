<?php
include '../backend/db_connection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the form is submitted for adding a new vaccination
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_vaccination'])) {
    // Retrieve and sanitize the submitted form data
    $name = htmlspecialchars(trim($_POST['name']));
    $vaccine = htmlspecialchars(trim($_POST['vaccine']));
    $dose_date = $_POST['dose_date'];
    $next_due = $_POST['next_due'];
    $status = $_POST['status'] ?? 'pending';
    $location = htmlspecialchars(trim($_POST['location']));
    $administered_by = htmlspecialchars(trim($_POST['administered_by']));
    $reaction = htmlspecialchars(trim($_POST['reaction']));

    // Insert new vaccination record into the database
    $stmt = $conn->prepare("INSERT INTO vaccinations (name, vaccine, dose_date, next_due, status, location, administered_by, reaction) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $vaccine, $dose_date, $next_due, $status, $location, $administered_by, $reaction);
    $stmt->execute();
    $stmt->close();

    $message = "Vaccination data recorded successfully!";
    $messageType = "success";
}

// Check if the user wants to update a vaccination
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_vaccination'])) {
    // Retrieve and sanitize the submitted form data for updating
    $id = $_POST['vaccination_id'];
    $name = htmlspecialchars(trim($_POST['update_name']));
    $vaccine = htmlspecialchars(trim($_POST['update_vaccine']));
    $dose_date = $_POST['update_dose_date'];
    $next_due = $_POST['update_next_due'];
    $status = $_POST['update_status'];
    $location = htmlspecialchars(trim($_POST['update_location']));
    $administered_by = htmlspecialchars(trim($_POST['update_administered_by']));
    $reaction = htmlspecialchars(trim($_POST['update_reaction']));

    // Update the vaccination record in the database
    $stmt = $conn->prepare("UPDATE vaccinations SET name=?, vaccine=?, dose_date=?, next_due=?, status=?, location=?, administered_by=?, reaction=? WHERE id=?");
    $stmt->bind_param("ssssssssi", $name, $vaccine, $dose_date, $next_due, $status, $location, $administered_by, $reaction, $id);
    $stmt->execute();
    $stmt->close();

    $message = "Vaccination data updated successfully!";
    $messageType = "success";
}

// Retrieve all vaccination records for display
$result = $conn->query("SELECT * FROM vaccinations");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Management Dashboard | VirusGuard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
        }
        .container {
            margin-top: 40px;
            padding: 20px 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007bff;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
        }
        footer {
            margin-top: 40px;
            background-color: #007bff;
            color: white;
            padding: 15px 0;
        }
        .btn-success, .btn-warning {
            background-image: linear-gradient(to right, #28a745, #218838);
            border: none;
        }
        .btn-success:hover, .btn-warning:hover {
            background-color: #218838;
        }
        table {
            margin-top: 30px;
        }
        table thead {
            background-color: #007bff;
            color: white;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        .modal-body label {
            font-weight: 500;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Vaccination Management Dashboard</h2>

        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Form to Add a New Vaccination -->
        <h4>Add New Vaccination</h4>
        <form action="vaccination_tracker.php" method="POST">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="name">Name:</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="vaccine">Vaccine Name:</label>
                    <input type="text" class="form-control" name="vaccine" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="dose_date">Dose Date:</label>
                    <input type="date" class="form-control" name="dose_date" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="next_due">Next Dose Due Date:</label>
                    <input type="date" class="form-control" name="next_due" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="status">Status:</label>
                    <select class="form-control" name="status">
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="canceled">Canceled</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="location">Location:</label>
                    <input type="text" class="form-control" name="location" placeholder="e.g. Clinic, Hospital">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="administered_by">Administered By:</label>
                    <input type="text" class="form-control" name="administered_by" placeholder="Administrator name">
                </div>
                <div class="form-group col-md-6">
                    <label for="reaction">Adverse Reaction (if any):</label>
                    <textarea class="form-control" name="reaction" rows="3" placeholder="Describe any adverse reactions..."></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-success btn-block" name="add_vaccination">Submit Vaccination</button>
        </form>

        <!-- Vaccination Records Table -->
        <h4 class="mt-5">Existing Vaccinations</h4>
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Vaccine</th>
                    <th>Dose Date</th>
                    <th>Next Due</th>
                    <th>Status</th>
                    <th>Location</th>
                    <th>Administered By</th>
                    <th>Reaction</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['vaccine']; ?></td>
                    <td><?php echo $row['dose_date']; ?></td>
                    <td><?php echo $row['next_due']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['location']; ?></td>
                    <td><?php echo $row['administered_by']; ?></td>
                    <td><?php echo $row['reaction']; ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#updateModal<?php echo $row['id']; ?>"><i class="fas fa-edit"></i></button>

                        <!-- Update Modal -->
                        <div class="modal fade" id="updateModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateModalLabel">Update Vaccination Data</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="vaccination_tracker.php" method="POST">
                                            <input type="hidden" name="vaccination_id" value="<?php echo $row['id']; ?>">
                                            <div class="form-group">
                                                <label for="update_name">Name:</label>
                                                <input type="text" class="form-control" name="update_name" value="<?php echo $row['name']; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="update_vaccine">Vaccine Name:</label>
                                                <input type="text" class="form-control" name="update_vaccine" value="<?php echo $row['vaccine']; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="update_dose_date">Dose Date:</label>
                                                <input type="date" class="form-control" name="update_dose_date" value="<?php echo $row['dose_date']; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="update_next_due">Next Due Date:</label>
                                                <input type="date" class="form-control" name="update_next_due" value="<?php echo $row['next_due']; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="update_status">Status:</label>
                                                <select class="form-control" name="update_status">
                                                    <option value="pending" <?php echo ($row['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="completed" <?php echo ($row['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                                    <option value="canceled" <?php echo ($row['status'] == 'canceled') ? 'selected' : ''; ?>>Canceled</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="update_location">Location:</label>
                                                <input type="text" class="form-control" name="update_location" value="<?php echo $row['location']; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="update_administered_by">Administered By:</label>
                                                <input type="text" class="form-control" name="update_administered_by" value="<?php echo $row['administered_by']; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="update_reaction">Reaction:</label>
                                                <textarea class="form-control" name="update_reaction" rows="3"><?php echo $row['reaction']; ?></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-block" name="update_vaccination">Update</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <footer class="text-center">
        <p>&copy; 2024 VirusGuard - Vaccination Tracker</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>