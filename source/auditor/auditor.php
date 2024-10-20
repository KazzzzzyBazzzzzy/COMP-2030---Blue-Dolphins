<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'auditor') {
    header("Location: ../home/login.php");
    exit();
}

require_once('../config/config.php');

$report = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Fetch summary report from the database
    $sql = "SELECT machine_name, COUNT(*) AS log_count, 
                   SUM(CASE WHEN machine_name = 'error' THEN 1 ELSE 0 END) AS error_count
            FROM machinery_logs 
            WHERE log_date BETWEEN ? AND ?
            GROUP BY machine_name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $report = "<table border='1'>
                    <tr>
                        <th>Machine name</th>
                        <th>Total Logs</th>
                        <th>Error Logs</th>
                    </tr>";
        while ($row = $result->fetch_assoc()) {
            $report .= "<tr>
                        <td>{$row['machine_name']}</td>
                        <td>{$row['log_count']}</td>
                        <td>{$row['error_count']}</td>
                    </tr>";
        }
        $report .= "</table>";
    } else {
        $report = "<p>No logs found for the selected date range.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Daniel Rosich" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/global.css">
    <link rel="stylesheet" type="text/css" href="../css/logout.css">
    <title>Auditor Home</title>
</head>
<body>
    <h1>Auditor Home</h1>
    <div id="auditor-select">
        <ul>
            <li><button onclick="toggleForm()">Summary Report</button></li></li>
            <li><a href="#"><button>Dashboard</button></a></li>
            <button class="logout-button" onclick="window.location.href='../home/logout.php'">Logout</button>
        </ul>
    </div>

    <div id="summary-form" style="display:none;">
        <form method="POST" action="">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>
            <button type="submit">Generate Report</button>
        </form>
    </div>

    <div id="report-section">
        <?= $report ?>
    </div>

    <script>
        function toggleForm() {
            var form = document.getElementById('summary-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
