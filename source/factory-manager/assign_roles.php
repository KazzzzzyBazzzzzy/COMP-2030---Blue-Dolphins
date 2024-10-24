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
        echo "<script>alert('Connection failed. Please try again later.');</script>";
        die();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['action'])) {
            $action = $_POST['action'];

            // Add new job
            if ($action === 'add') {
                $machine = $_POST['machine'];
                $employee = $_POST['employee'];
                $jobs_description = $_POST['jobs_description'];

                if (!empty($machine) && !empty($employee) && !empty($jobs_description)) {
                    $sql = "INSERT INTO jobs (`Machine`, `Employee`, `Jobs-Description`) VALUES ('$machine', '$employee', '$jobs_description')";
                    if ($conn->query($sql) === TRUE) {
                        echo "<script>alert('Job added successfully.');</script>";
                    } else {
                        logError("Insert failed: " . $conn->error);
                        echo "<script>alert('Error occurred while adding the job. Please check the logs.');</script>";
                    }
                } else {
                    echo "<script>alert('Error: All fields are required.');</script>";
                }

            // Update job
            } elseif ($action === 'update') {
                $job_id = $_POST['job_id'];
                $machine = $_POST['machine'];
                $employee = $_POST['employee'];
                $jobs_description = $_POST['jobs_description'];

                if (!empty($job_id) && !empty($machine) && !empty($employee) && !empty($jobs_description)) {
                    $checkJobSql = "SELECT * FROM jobs WHERE `job-id`='$job_id'";
                    $checkJobResult = $conn->query($checkJobSql);

                    if ($checkJobResult->num_rows > 0) {
                        $sql = "UPDATE jobs SET `Machine`='$machine', `Employee`='$employee', `Jobs-Description`='$jobs_description' WHERE `job-id`='$job_id'";
                        if ($conn->query($sql) === TRUE) {
                            echo "<script>alert('Job updated successfully for Job ID: $job_id');</script>";
                        } else {
                            logError("Update failed: " . $conn->error);
                            echo "<script>alert('Error occurred. Please check the logs.');</script>";
                        }
                    } else {
                        echo "<script>alert('Error: Job ID \"$job_id\" does not exist.');</script>";
                    }
                } else {
                    echo "<script>alert('Error: All fields are required.');</script>";
                }

            // Delete job
            } elseif ($action === 'delete') {
                $job_id = $_POST['job_id'];
            
                if (!empty($job_id)) {
                    $deleteSql = "DELETE FROM jobs WHERE `job-id`='$job_id'";
                    if ($conn->query($deleteSql) === TRUE) {
                        echo "<script>alert('Job deleted successfully for Job ID: $job_id');</script>";
                    } else {
                        logError("Delete failed: " . $conn->error);
                        echo "<script>alert('Error occurred while deleting. Please check the logs.');</script>";
                    }
                } else {
                    echo "<script>alert('Error: Job ID is required for deletion.');</script>";
                }
            }
        }
    }

    $machineSql = "SELECT * FROM machine";
    $machines = $conn->query($machineSql);

    $jobSql = "SELECT `job-id`, Employee, Machine, `Jobs-Description` FROM jobs";
    $jobResult = $conn->query($jobSql);
    $jobRows = '';

    if ($jobResult->num_rows > 0) {
        while ($row = $jobResult->fetch_assoc()) {
            $jobRows .= '<tr>
                <td>' . htmlspecialchars($row['job-id']) . '</td>
                <td>' . htmlspecialchars($row['Employee']) . '</td>
                <td>' . htmlspecialchars($row['Machine']) . '</td>
                <td>' . htmlspecialchars($row['Jobs-Description']) . '</td>
                <td>
                    <a href="#" onclick="editJob(\'' . htmlspecialchars($row['job-id']) . '\', \'' . htmlspecialchars($row['Machine']) . '\', \'' . htmlspecialchars($row['Employee']) . '\', \'' . htmlspecialchars($row['Jobs-Description']) . '\')">Edit</a>
                    <a href="#" onclick="deleteJob(\'' . htmlspecialchars($row['job-id']) . '\')">Delete</a>
                </td>
            </tr>';
        }
    } else {
        $jobRows = '<tr><td colspan="5">No jobs found.</td></tr>';
    }

    $layout = file_get_contents('../factory-manager/temp/assign_roles_layout.html');
    $layout = str_replace('{{jobRows}}', $jobRows, $layout);
    $layout = str_replace('{{machinesOptions}}', generateMachineOptions($machines), $layout);
    echo $layout;

} catch (mysqli_sql_exception $e) {
    logError("Database query failed: " . $e->getMessage());
    echo "<script>alert('Error occurred. Please check the logs.');</script>";
}

$conn->close();

function generateMachineOptions($machines) {
    $options = '';
    while ($row = $machines->fetch_assoc()) {
        $options .= '<option value="' . htmlspecialchars($row['machine_name']) . '">' . htmlspecialchars($row['machine_name']) . '</option>';
    }
    return $options;
}
?>