<?php
session_start();
require '../home/auth_check.php';
checkUserRole('factorymanager');

require '../config/config.php';

function logError($errorMessage) {
    $logFile = '../logs/errors.log';
    $currentDateTime = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$currentDateTime] Error: $errorMessage" . PHP_EOL, FILE_APPEND);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if ($conn->connect_error) {
        logError("Connection failed: " . $conn->connect_error);
        die("Connection failed. Please try again later.");
    }

    if (isset($_GET['delete_id'])) {
        $delete_id = $_GET['delete_id'];
        $sql = "DELETE FROM `jobs` WHERE `jobs`.`job-id` = '$delete_id'";
        $conn->query($sql);
        header("Location: manage-jobs.php");
        exit();
    }

    // Update job if update_job is set
    if (isset($_POST['update_job'])) {
        $jobId = $_POST['edit_job_id'];
        $jobDescription = $_POST['edit_job_name'];

        $sql = "UPDATE `jobs` SET `Jobs-Description` = '$jobDescription' WHERE `job-id` = '$jobId'";
        $conn->query($sql);
        echo "Job updated successfully.";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['update_job'])) {
        $employee = $_POST['employee'];
        $machine = $_POST['machine'];
        $jobsDescription = $_POST['job_name'];

        $sql = "INSERT INTO jobs (Employee, Machine, `Jobs-Description`) VALUES ('$employee', '$machine', '$jobsDescription')";
        $conn->query($sql);
        echo "New job added successfully.";
    }


    $sql = "SELECT * FROM `jobs` ORDER BY `jobs`.`Employee` ASC";
    $result = $conn->query($sql);

    $employees = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
    }

    $sql = "SELECT * FROM `jobs` ORDER BY `jobs`.`Machine` ASC";
    $result = $conn->query($sql);

    $machines = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $machines[] = $row;
        }
    }

    $sql = "SELECT * FROM jobs";
    $existingJobs = $conn->query($sql);

} catch (mysqli_sql_exception $e) {
    logError("Database connection failed: " . $e->getMessage()); 
    echo "Error occurred. Please check the logs."; 
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
            <li><a href="#"><button>Monitor Factory Performance</button></a></li>
            <li><a href="../factory-manager/manage-jobs.php"><button>Manage Jobs</button></a></li>
            <li><a href="../factory-manager/manage_machines.php"><button>Manage Machines</button></a></li>
            <li><a href="#"><button>Assign Roles</button></a></li>
            <button class="logout-button" onclick="window.location.href='../home/logout.php'">Logout</button>
        </ul>
    </div>


    <h2>Add a Job</h2>
    <form method="post" class="job-form">
        <label for="employee"></label>
        <select id="employee" name="employee">
            <option value="">Select an employee</option>
            <?php foreach ($employees as $employee): ?>
                <option value="<?php echo $employee['Employee']; ?>"><?php echo $employee['Employee']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="machine"></label>
        <select id="machine" name="machine">
            <option value="">Select a machine</option>
            <?php foreach ($machines as $machine): ?>
                <option value="<?php echo $machine['Machine']; ?>"><?php echo $machine['Machine']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="job_name">Job Description:</label>
        <textarea name="job_name" id="job_name" rows="4" cols="50" required></textarea>

        <div class="form-buttons">
            <button type="submit" name="add_job" class="add-button">Add Job</button>
        </div>
    </form>

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
                echo "<tr>
                        <td>{$row['Employee']}</td>
                        <td>{$row['Machine']}</td>
                        <td>{$row['Jobs-Description']}</td>
                        <td>
                            <!--'Edit' link with an onclick event to call the JavaScript editJob function -->
                            <a href='#' onclick=\"editJob('{$row['Employee']}', '{$row['Machine']}', '{$row['Jobs-Description']}', '{$row['job-id']}')\">Edit</a>
                            <!--'Delete' link that triggers a confirmation popup before deletion -->
                            <a href='?delete_id={$row['job-id']}' onclick=\"return confirm('Are you sure you want to delete this job?');\">Delete</a>
                        </td>
                      </tr>";
            }
        } else {

            echo "<tr><td colspan='4'>No jobs found.</td></tr>";
        }
        ?>
    </table>

    <script>

        function editJob(employee, machine, jobDescription, jobId) {
            document.getElementById('edit_employee').value = employee;
            document.getElementById('edit_machine').value = machine;
            document.getElementById('edit_job_name').value = jobDescription;
            document.getElementById('edit_job_id').value = jobId;
            document.getElementById('edit-job-form').style.display = 'block';
        }
    </script>


    <div id="edit-job-form" style="display:none;">
        <h2>Edit Job</h2>
        <form method="post" class="job-form">
            <input type="hidden" id="edit_job_id" name="edit_job_id">
            <label for="edit_employee">Employee:</label>
            <input type="text" id="edit_employee" name="edit_employee" readonly>

            <label for="edit_machine">Machine:</label>
            <input type="text" id="edit_machine" name="edit_machine" readonly>

            <label for="edit_job_name">Job Description:</label>
            <textarea name="edit_job_name" id="edit_job_name" rows="4" cols="50" required></textarea>

            <div class="form-buttons">
                <button type="submit" name="update_job" class="update-button">Update Job</button>
            </div>
        </form>
    </div>


</body>
</html>

<?php $conn->close(); ?>