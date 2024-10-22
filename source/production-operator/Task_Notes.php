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
        die("Connection failed. Please try again later.");
    }

    $records_per_page = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $start_from = ($page - 1) * $records_per_page;

    // Get factory logs
    $sql = "SELECT timestamp, machine_name, temperature, humidity FROM factory_logs ORDER BY timestamp LIMIT $start_from, $records_per_page";
    $result = $conn->query($sql);

    if (!$result) {
        logError("Query failed: " . $conn->error);
        die("Failed to retrieve data.");
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['submit_note'])) {
            $timestamp = $_POST['timestamp'];
            $machine_name = $_POST['machine_name'];
            $temperature = $_POST['temperature'];
            $humidity = $_POST['humidity'];
            $notes = $_POST['note'];

            $insert_sql = "INSERT INTO notes (timestamp, machine_name, temperature, humidity, notes) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("ssdds", $timestamp, $machine_name, $temperature, $humidity, $notes);

            if (!$stmt->execute()) {
                logError("Insert failed: " . $stmt->error);
                die("Failed to save note.");
            }
        } elseif (isset($_POST['edit_note'])) {
            $note_id = $_POST['note_id'];
            $new_note = $_POST['new_note'];

            $update_sql = "UPDATE notes SET notes = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $new_note, $note_id);

            if (!$stmt->execute()) {
                logError("Update failed: " . $stmt->error);
                die("Failed to update note.");
            } else {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        }
    }

    // Get total number of pages
    $total_sql = "SELECT COUNT(*) FROM factory_logs";
    $total_result = $conn->query($total_sql);
    $total_rows = $total_result->fetch_array()[0];
    $total_pages = ceil($total_rows / $records_per_page);

    // Fetch notes
    $sql_notes = "SELECT id, timestamp, machine_name, temperature, humidity, notes FROM notes";
    $notes_result = $conn->query($sql_notes);

} catch (Exception $e) {
    logError("Exception caught: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}

include '../production-operator/temp/task_notes_layout.html';
?>
