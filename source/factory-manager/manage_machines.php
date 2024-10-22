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

    // Check if the request method is POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $machine_id = $_POST['machine_id'];
        $machine_name = $_POST['machine_name'];

        if (isset($_POST['add_machine'])) {
            $checkSql = "SELECT * FROM machine WHERE id='$machine_id'";
            $checkResult = $conn->query($checkSql);

            if ($checkResult->num_rows > 0) {
                echo "Error: Machine ID already exists. Please choose a different ID.";
            } else {
                $sql = "INSERT INTO machine (id, machine_name) VALUES ('$machine_id', '$machine_name')";
                $conn->query($sql);
                echo "New machine added successfully.";
            }
        } elseif (isset($_POST['update_machine'])) {
            $original_id = $_POST['original_id'];
            if (!empty($machine_id) && !empty($original_id)) {
                if ($machine_id !== $original_id) {
                    $checkSql = "SELECT * FROM machine WHERE id='$machine_id'";
                    $checkResult = $conn->query($checkSql);

                    if ($checkResult->num_rows > 0) {
                        echo "Error: Machine ID already exists. Please choose a different ID.";
                    } else {
                        $sql = "UPDATE machine SET id='$machine_id', machine_name='$machine_name' WHERE id='$original_id'";
                        $conn->query($sql);
                        echo "Machine updated successfully.";
                    }
                } else {
                    $sql = "UPDATE machine SET machine_name='$machine_name' WHERE id='$machine_id'";
                    $conn->query($sql);
                    echo "Machine updated successfully.";
                }
            } else {
                logError("Update failed: Machine ID is not specified.");
                echo "Error occurred. Please check the logs.";
            }
        }
    }

    if (isset($_GET['delete_id'])) {
        $machine_id = $_GET['delete_id'];
        if (!empty($machine_id)) {
            $sql = "DELETE FROM machine WHERE id='$machine_id'";
            $conn->query($sql);
            echo "Machine deleted successfully.";
        } else {
            logError("Delete failed: Machine ID is not specified.");
            echo "Error occurred. Please check the logs.";
        }
    }
    $sql = "SELECT * FROM machine";
    $result = $conn->query($sql);
    $machineRows = '';

    if (isset($result) && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $machineRows .= '<tr>
                <td>' . $row['id'] . '</td>
                <td>' . $row['machine_name'] . '</td>
                <td>
                    <a href="#" onclick="editMachine(\'' . $row['id'] . '\', \'' . $row['machine_name'] . '\')">Edit</a>
                    <a href="?delete_id=' . $row['id'] . '" onclick="return confirm(\'Are you sure you want to delete this machine?\');">Delete</a>
                </td>
            </tr>';
        }
    } else {
        $machineRows = '<tr><td colspan="3">No machines found.</td></tr>';
    }

    $layout = file_get_contents('../factory-manager/temp/manage_machines_layout.html');
    $layout = str_replace('{{machineRows}}', $machineRows, $layout);
    echo $layout;

} catch (mysqli_sql_exception $e) {
    logError("Database query failed: " . $e->getMessage());
    echo "Error occurred. Please check the logs.";
}

$conn->close();
