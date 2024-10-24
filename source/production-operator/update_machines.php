<?php
session_start();
require '../home/auth_check.php';
checkUserRole('productionoperator');

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

    $machineOptions = '';
    $machineSql = "SELECT id, machine_name FROM machine";
    $machineResult = $conn->query($machineSql);
    
    if ($machineResult->num_rows > 0) {
        while ($row = $machineResult->fetch_assoc()) {
            $machineOptions .= '<option value="' . htmlspecialchars($row['machine_name']) . '">' . htmlspecialchars($row['machine_name']) . '</option>';
        }
    } else {
        $machineOptions = '<option value="">No machines available</option>';
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $job_id = $_POST['job_id'];
        $machine_name = $_POST['machine_name'];


        if (!empty($job_id) && !empty($machine_name)) {
            $checkJobSql = "SELECT * FROM jobs WHERE `job-id`='$job_id'";
            $checkJobResult = $conn->query($checkJobSql);

            if ($checkJobResult->num_rows > 0) {
                $sql = "UPDATE jobs SET Machine='$machine_name' WHERE `job-id`='$job_id'";
                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Machine updated successfully for Job ID: $job_id');</script>";
                } else {
                    logError("Update failed: " . $conn->error);
                    echo "<script>alert('Error occurred. Please check the logs.');</script>";
                }
            } else {
                // Job ID does not exist
                echo "<script>alert('Error: Job ID \"$job_id\" does not exist.');</script>";
            }
        } else {
            echo "<script>alert('Error: Job ID and Machine name are required.');</script>";
        }
    }

    $sql = "SELECT `job-id`, Employee, Machine FROM jobs";
    $result = $conn->query($sql);
    $jobRows = '';

    if (isset($result) && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $jobRows .= '<tr>
                <td>' . htmlspecialchars($row['job-id']) . '</td>
                <td>' . htmlspecialchars($row['Employee']) . '</td>
                <td>' . htmlspecialchars($row['Machine']) . '</td>
                <td>
                    <a href="#" onclick="editJob(\'' . htmlspecialchars($row['job-id']) . '\', \'' . htmlspecialchars($row['Machine']) . '\')">Edit</a>
                </td>
            </tr>';
        }
    } else {
        $jobRows = '<tr><td colspan="4">No jobs found.</td></tr>';
    }

    $layout = file_get_contents('../production-operator/temp/update_machines_layout.html');
    $layout = str_replace('{{jobRows}}', $jobRows, $layout);
    $layout = str_replace('{{machineOptions}}', $machineOptions, $layout);
    echo $layout;

} catch (mysqli_sql_exception $e) {
    logError("Database query failed: " . $e->getMessage());
    echo "<script>alert('Error occurred. Please check the logs.');</script>";
}

$conn->close();
?>