<?php
session_start();

require '../home/auth_check.php';
checkUserRole('auditor');

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

    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '2024-04-01';
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '2024-04-02';

    $start_date = date('j/m/Y', strtotime($start_date));
    $end_date = date('j/m/Y', strtotime($end_date));

    $sql = "SELECT timestamp, machine_name, operational_status 
            FROM factory_logs 
            WHERE STR_TO_DATE(timestamp, '%d/%m/%Y') BETWEEN STR_TO_DATE(?, '%d/%m/%Y') 
            AND STR_TO_DATE(?, '%d/%m/%Y')
            ORDER BY STR_TO_DATE(timestamp, '%d/%m/%Y') 
            LIMIT ?, ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssii', $start_date, $end_date, $start_from, $records_per_page);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        logError("Query failed: " . $conn->error);
        die("Failed to retrieve data.");
    }
} catch (Exception $e) {
    logError($e->getMessage());
    die("An error occurred. Please try again later.");
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
    <script>
        function toggleForm() {
            const form = document.getElementById('summary-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <h1>Auditor Home</h1>
    <div id="auditor-select">
        <ul>
            <li><a href="#"><button>Dashboard</button></a></li>
            <button class="logout-button" onclick="window.location.href='../home/logout.php'">Logout</button>
        </ul>
    </div>

    <h2>Generate Summary Report</h2>
    <form method="POST" action="">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($start_date))); ?>" required>
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($end_date))); ?>" required>
        <button type="submit">Generate Report</button>
    </form>

    <h2>Factory Logs</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Timestamp</th>
            <th>Machine Name</th>
            <th>Operational Status</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                    <td><?php echo htmlspecialchars($row['machine_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['operational_status']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">No records found for the selected date range.</td>
            </tr>
        <?php endif; ?>
    </table>

    <?php
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>
