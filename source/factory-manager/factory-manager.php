<?php
session_start();

// Database connection details
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "smd_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the factory_logs table
$sql = "SELECT * FROM factory_logs";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Start table to display the data
    echo "<table border='1'>
        <tr>
            <th>Timestamp</th>
            <th>Machine Name</th>
            <th>Temperature</th>
            <th>Pressure</th>
            <th>Vibration</th>
            <th>Humidity</th>
            <th>Power Consumption</th>
            <th>Operational Status</th>
            <th>Error Code</th>
            <th>Production Count</th>
            <th>Maintenance Log</th>
            <th>Speed</th>
        </tr>";
    
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>" . htmlspecialchars($row['timestamp']) . "</td>
            <td>" . htmlspecialchars($row['machine_name']) . "</td>
            <td>" . htmlspecialchars($row['temperature']) . "</td>
            <td>" . htmlspecialchars($row['pressure']) . "</td>
            <td>" . htmlspecialchars($row['vibration']) . "</td>
            <td>" . htmlspecialchars($row['humidity']) . "</td>
            <td>" . htmlspecialchars($row['power_consumption']) . "</td>
            <td>" . htmlspecialchars($row['operational_status']) . "</td>
            <td>" . htmlspecialchars($row['error_code']) . "</td>
            <td>" . htmlspecialchars($row['production_count']) . "</td>
            <td>" . htmlspecialchars($row['maintenance_log']) . "</td>
            <td>" . htmlspecialchars($row['speed']) . "</td>
        </tr>";
    }
    
    echo "</table>";
} else {
    echo "No data found in the factory_logs table.";
}

// Close connection
$conn->close();
?>
