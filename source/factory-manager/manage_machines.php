<?php
session_start();
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
            if (!empty($machine_id)) {
                $sql = "UPDATE machine SET machine_name='$machine_name' WHERE id='$machine_id'";
                $conn->query($sql);
                echo "Machine updated successfully.";
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
} catch (mysqli_sql_exception $e) {
    logError("Database query failed: " . $e->getMessage());
    echo "Error occurred. Please check the logs.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Daniel Rosich" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../factory-manager/styles/manage_machines.css">
    <title>Manage Machines Home</title>
</head>
<body>
<h1>Manage Machines Home</h1>
<div id="factory-manager-select">
    <ul>
        <li><a href="../home/home.html"><button>Monitor Factory Performance</button></a></li>
        <li><a href="../home/home.html"><button>Manage Jobs</button></a></li>
        <li><a href="../factory-manager/manage_machines.php"><button>Manage Machines</button></a></li>
        <li><a href="../home/home.html"><button>Assign Roles</button></a></li>
    </ul>
</div>

<h2>Add / Update Machine</h2>
<form method="post" class="machine-form">
    <label for="machine_id">Machine ID:</label>
    <input type="text" name="machine_id" id="machine_id" value="" required>
    <label for="machine_name">Machine Name:</label>
    <input type="text" name="machine_name" id="machine_name" required>
    
    <div class="form-buttons">
        <button type="submit" name="add_machine" class="add-button">Add Machine</button>
        <button type="submit" name="update_machine" class="update-button">Update Machine</button>
    </div>
</form>

<h2>Existing Machines</h2>
<table class="machines-table">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Actions</th>
    </tr>
    <?php if (isset($result) && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['machine_name']; ?></td>
                <td>
                    <a href="#" onclick="editMachine('<?php echo $row['id']; ?>', '<?php echo $row['machine_name']; ?>')">Edit</a>
                    <a href="?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this machine?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="3">No machines found.</td>
        </tr>
    <?php endif; ?>
</table>

<script>
    function editMachine(id, name) {
        document.getElementById('machine_id').value = id; 
        document.getElementById('machine_name').value = name;
    }
</script>

</body>
</html>




<?php $conn->close(); ?>
