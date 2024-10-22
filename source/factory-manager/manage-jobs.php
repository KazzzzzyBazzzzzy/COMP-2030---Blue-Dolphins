<!--This script enables basic job management functionality for a factory manager, including listing jobs,
deleting them, and updating their descriptions.-->

<?php
// DATABASE SET UP
//----------------------------------------------------------------------------------------------------------------------

session_start(); // Starts a new session or resumes an existing one

// Checks if the user_id session variable is set and whether the logged-in user has the correct role.
// If not, the user is redirected to the login page and the script execution is stopped.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'factorymanager') {
    header("Location: ../home/login.php");
    exit();
}
session_start();
require '../home/auth_check.php';
checkUserRole('factorymanager');

// Load the config file which contains the database connection settings
require '../config/config.php';

// Function for logging errors to a file, each error is logged with the current date and time
function logError($errorMessage) {
    $logFile = '../logs/errors.log'; // Path to the log file
    $currentDateTime = date('Y-m-d H:i:s'); // Get the current date and time
    file_put_contents($logFile, "[$currentDateTime] Error: $errorMessage" . PHP_EOL, FILE_APPEND); // Append the error message to the log file
}

// Enable error reporting for MySQLi so that it throws exceptions in case of database issue
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
//----------------------------------------------------------------------------------------------------------------------


// DATABASE OPERATIONS
//----------------------------------------------------------------------------------------------------------------------
try {
    // Check if there is a connection error
    if ($conn->connect_error) {
        logError("Connection failed: " . $conn->connect_error); // Log the connection error
        die("Connection failed. Please try again later."); // Terminate the script and display an error message
    }

    // Delete job if delete_id is set
    if (isset($_GET['delete_id'])) {
        $delete_id = $_GET['delete_id'];
        $sql = "DELETE FROM `jobs` WHERE `jobs`.`job-id` = '$delete_id'";
        $conn->query($sql);
        header("Location: manage-jobs.php"); // Reload page
        exit();
    }

    // Update job if update_job is set
    if (isset($_POST['update_job'])) {
        $jobId = $_POST['edit_job_id']; // Get job id from hidden form field
        $jobDescription = $_POST['edit_job_name']; // Retrieve updated job description

        // SQL query to edit job description
        $sql = "UPDATE `jobs` SET `Jobs-Description` = '$jobDescription' WHERE `job-id` = '$jobId'";
        $conn->query($sql);
        echo "Job updated successfully."; // Display a success message
    }

    // Insert new job if the request method is POST and update_job is not set
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['update_job'])) {
        $employee = $_POST['employee'];
        $machine = $_POST['machine'];
        $jobsDescription = $_POST['job_name'];

        // SQL query to insert new job
        $sql = "INSERT INTO jobs (Employee, Machine, `Jobs-Description`) VALUES ('$employee', '$machine', '$jobsDescription')";
        $conn->query($sql);
        echo "New job added successfully."; // Display a success message
    }

    // Fetch existing jobs
    $sql = "SELECT * FROM jobs";
    $existingJobs = $conn->query($sql);

} catch (mysqli_sql_exception $e) {
    logError("Database connection failed: " . $e->getMessage()); // Log the database connection error
    echo "Error occurred. Please check the logs."; // Display an error message
}
//----------------------------------------------------------------------------------------------------------------------

?>

<!--Set up HTML-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management</title>
</head>
<body>

    <div>
        <h1>Manage Jobs</h1>
    </div>

    <!--Form to add a new job-->

    <h2>Add a Job</h2>
    <form method="post" class="job-form">
        <label for="employee">Employee:</label>
        <input type="text" name="employee" required>
        <label for="machine">Machine:</label>
        <input type="text" name="machine" required>
        <label for="job_name">Job Description:</label>
        <input type="text" name="job_name" required>
        <button type="submit">Add Job</button>
    </form>

    <!--DISPLAY CURRENT JOBS-->
    <!--------------------------------------------------------------------------------------------------------------------->
    <h2>Current Jobs</h2>
    <table class="jobs-table">
        <tr>
            <th>Employee</th>
            <th>Machine</th>
            <th>Job Description</th>
            <th>Actions</th>
        </tr>
        <?php
        if ($existingJobs && $existingJobs->num_rows > 0) {
            while ($row = $existingJobs->fetch_assoc()) {
                // Output a table with job details
                echo "<tr>
                        <td>{$row['Employee']}</td>
                        <td>{$row['Machine']}</td>
                        <td>{$row['Jobs-Description']}</td>
                        <td>
                            <button onclick=\"editJob('{$row['Employee']}', '{$row['Machine']}', '{$row['Jobs-Description']}', '{$row['job-id']}')\">Edit</button>
                            <a href='manage-jobs.php?delete_id={$row['job-id']}'>Delete</a>
                        </td>
                      </tr>";
            }
        } else {
            // If no jobs are found, display message
            echo "<tr><td colspan='4'>No jobs found.</td></tr>";
        }
        ?>
    </table>
    <!----------------------------------------------------------------------------------------------------------------->

    <!--Editing Jobs-->
    <!----------------------------------------------------------------------------------------------------------------->
    <script>
        // Fill the form field with the updated values
        function editJob(employee, machine, jobDescription, jobId) {
            document.getElementById('edit_employee').value = employee;
            document.getElementById('edit_machine').value = machine;
            document.getElementById('edit_job_name').value = jobDescription;
            document.getElementById('edit_job_id').value = jobId;
            document.getElementById('edit-job-form').style.display = 'block'; // Show the edit form
        }
    </script>

    <!--Edit form for when 'edit' is clicked-->
    <div id="edit-job-form" style="display:none;">
        <h2>Edit Job</h2>
        <form method="post" class="job-form">
            <input type="hidden" id="edit_job_id" name="edit_job_id">
            <label for="edit_employee">Employee:</label>
            <input type="text" id="edit_employee" name="employee" required>
            <label for="edit_machine">Machine:</label>
            <input type="text" id="edit_machine" name="machine" required>
            <label for="edit_job_name">Job Description:</label>
            <input type="text" id="edit_job_name" name="edit_job_name" required>
            <button type="submit" name="update_job">Update Job</button>
        </form>
    </div>
    <!----------------------------------------------------------------------------------------------------------------->

</body>
</html>

<?php $conn->close(); ?> <!-- Close the database connection -->
