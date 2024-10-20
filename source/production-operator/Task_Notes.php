<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'productionoperator') {
    header("Location: ../home/login.php");
    exit();
}

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

    // Fetch factory logs
    $sql = "SELECT timestamp, machine_name, temperature, humidity FROM factory_logs ORDER BY RAND() LIMIT 10";
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
} catch (Exception $e) {
    logError("Exception caught: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Daniel Rosich, Samuel Ngiri" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/global.css">
    <link rel="stylesheet" type="text/css" href="../css/task_notes.css">
    <link rel="stylesheet" type="text/css" href="../css/logout.css">
    <title>Production Operator Home</title>
    <style>
        .fade-in {
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
<h1>Production Operator Home</h1>
<div id="production-operator-select">
    <ul>
        <li><a href="../home/home.html"><button>Monitor Factory Performance</button></a></li>
        <li><a href="../home/home.html"><button>Update Machines</button></a></li>
        <li><a href="../home/home.html"><button>Update Jobs</button></a></li>
        <li><a href="../production-operator/task_notes.php"><button>Manage Task Notes</button></a></li>
        <button class="logout-button" onclick="window.location.href='../home/logout.php'">Logout</button>
    </ul>
</div>

<h2>Factory Logs</h2>
<table id="factory-logs" border="1">
    <thead>
        <tr>
            <th>Timestamp</th>
            <th>Machine Name</th>
            <th>Temperature (°C)</th>
            <th>Humidity (%)</th>
            <th>Notes</th>
            <th>Add Notes</th>
        </tr>
    </thead>
    <tbody>
    <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['timestamp']) . "</td>
                <td>" . htmlspecialchars($row['machine_name']) . "</td>
                <td>" . htmlspecialchars($row['temperature']) . " °C</td>
                <td>" . htmlspecialchars($row['humidity']) . " %</td>
                
                <!-- Column for adding notes -->
                <td>
                    <form method='POST' class='note-form'>
                        <input type='hidden' name='timestamp' value='" . htmlspecialchars($row['timestamp']) . "' />
                        <input type='hidden' name='machine_name' value='" . htmlspecialchars($row['machine_name']) . "' />
                        <input type='hidden' name='temperature' value='" . htmlspecialchars($row['temperature']) . "' />
                        <input type='hidden' name='humidity' value='" . htmlspecialchars($row['humidity']) . "' />
                        
                        <div class='textarea-button-container'>
                            <textarea name='note' placeholder='Add your notes here...' required></textarea>
                        
                        </div>
                    </form>
                </td>

                <td>
                    <button type='submit' name='submit_note'>Add Note</button>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No logs available</td></tr>";
}
?>
    </tbody>
</table>


<h2>Notes</h2>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Timestamp</th>
            <th>Machine Name</th>
            <th>Temperature (°C)</th>
            <th>Humidity (%)</th>
            <th>Note</th>
            <th>Actions</th>
            <th>Edit Note</th>
        </tr>
    </thead>
    <tbody id="notes-table">
        <?php
        $sql_notes = "SELECT id, timestamp, machine_name, temperature, humidity, notes FROM notes";
        
        try {
            $notes_result = $conn->query($sql_notes);
            
            if ($notes_result->num_rows > 0) {
                while ($note_row = $notes_result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($note_row['id']) . "</td>
                            <td>" . htmlspecialchars($note_row['timestamp']) . "</td>
                            <td>" . htmlspecialchars($note_row['machine_name']) . "</td>
                            <td>" . htmlspecialchars($note_row['temperature']) . " °C</td>
                            <td>" . htmlspecialchars($note_row['humidity']) . " %</td>
                            <td>" . htmlspecialchars($note_row['notes']) . "</td>
                            <form method='POST' class='edit-form'>
                            <td>

                                    <input type='hidden' name='note_id' value='" . htmlspecialchars($note_row['id']) . "' />
                                    <div class='textarea-button-container'>
                                        <textarea name='new_note' required>" . htmlspecialchars($note_row['notes']) . "</textarea>

                                    </div>
                               
                            </td>
                            <td>
                                <button type='submit' name='edit_note'>Edit Note</button>
                            </td>
                             </form>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No notes available</td></tr>";
            }
        } catch (Exception $e) {
            logError("Error fetching notes: " . $e->getMessage());
            echo "<tr><td colspan='7'>Error fetching notes. Please check the logs.</td></tr>";
        }
        ?>

    </tbody>
</table>

</body>
</html>
