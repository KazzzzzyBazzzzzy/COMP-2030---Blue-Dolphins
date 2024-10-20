<?php
// Start the session to manage user sessions
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'factorymanager') {
    header("Location: ../home/login.php");
    exit();
}

// Include the configuration file which contains database connection details
require '../config/config.php';

// Function to log errors to a file
function logError($errorMessage) {
    $logFile = '../logs/errors.log'; // Path to the log file
    $currentDateTime = date('Y-m-d H:i:s'); // Get the current date and time
    file_put_contents($logFile, "[$currentDateTime] Error: $errorMessage" . PHP_EOL, FILE_APPEND); // Append the error message to the log file
}

// Enable error reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Check if there is a connection error
    if ($conn->connect_error) {
        logError("Connection failed: " . $conn->connect_error); // Log the connection error
        die("Connection failed. Please try again later."); // Terminate the script and display an error message
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $employee = $_POST['employee'];
        $job = $_POST['Machine'];

        $sql = "INSERT INTO jobs (Employee, Machine) VALUES ('$employee', '$job')";
        $conn->query($sql);
        echo "New job added successfully."; // Display a success message
    }

    // Fetch employees
    $sql = "SELECT * FROM `jobs` ORDER BY `jobs`.`Employee` ASC";
    $result = $conn->query($sql);

    $employees = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
    }

    // Fetch existing jobs and their associated machines
    $sql = "SELECT * FROM jobs";
    $result = $conn->query($sql);

} catch (mysqli_sql_exception $e) {
    logError("Database connection failed: " . $e->getMessage()); // Log the database connection error
    echo "Error occurred. Please check the logs."; // Display an error message
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Daniel Rosich">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/global.css">
    <link rel="stylesheet" type="text/css" href="../css/logout.css">
    <title>Manage Jobs Home</title>
</head>
<body>
<h1>Manage Jobs Home</h1>
<div id="factory-manager-select">
    <ul>
        <li><a href="../home/home.html"><button>Monitor Factory Performance</button></a></li>
        <li><a href="../factory-manager/manage_machines.php"><button>Manage Machines</button></a></li>
        <li><a href="../factory-manager/manage-jobs.php"><button>Manage Jobs</button></a></li>
        <li><a href="../home/home.html"><button>Assign Roles</button></a></li>
        <button class="logout-button" onclick="window.location.href='../home/logout.php'">Logout</button>
    </ul>
</div>

<h2>Add & Edit Jobs</h2>
<form method="post" class="job-form">
    <label for="employee"></label>
    <select id="employee" name="employee" onchange="fillEmployeeTextbox()">
        <option value="">Select an employee</option>
        <?php foreach ($employees as $employee): ?>
            <option value="<?php echo $employee['Employee']; ?>"><?php echo $employee['Employee']; ?></option>
        <?php endforeach; ?>
    </select>
    <label for="job_name">Job:</label>
    <input type="text" name="job_name" id="job_name" required>

    <div class="form-buttons">
        <button type="submit" name="add_job" class="add-button">Add Job</button>
    </div>
</form>

<h2>Existing Jobs</h2>
<table class="jobs-table">
    <tr>
        <th>Employee</th>
        <th>Job</th>
    </tr>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['Employee']}</td>
                    <td>{$row['Machine']}</td>
                    <td>
                        <a href='#' onclick=\"editJob('{$row['Employee']}', '{$row['Machine']}')\">Edit</a>
                        <a href='?delete_id={$row['Employee']}' onclick=\"return confirm('Are you sure you want to delete this job?');\">Delete</a>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No jobs found.</td></tr>";
    }
    ?>
</table>

<script>
    function fillEmployeeTextbox() {
        var employeeDropdown = document.getElementById('employee');
        var employeeTextbox = document.getElementById('employee_textbox');
        employeeTextbox.value = employeeDropdown.value;
    }

</script>

</body>
</html>

<?php $conn->close(); ?> <!-- Close the database connection -->