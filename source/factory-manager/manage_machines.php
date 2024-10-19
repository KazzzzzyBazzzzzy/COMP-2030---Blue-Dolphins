<?php
// Start the session to manage user sessions
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'factorymanager') {
    header("Location: ../home/login.php");
    exit();
}
// Include the configuration file which contains database connection details
require '../config/config.php';

// Function to log errors to a file
function logError($errorMessage) {
    $logFile = '../logs/errors.log'; // Path to the log file
    $currentDateTime = date('Y-m-d H:i:s'); // Get the current date and time
    file_put_contents($logFile, "[$currentDateTime] Error: $errorMessage" . PHP_EOL, FILE_APPEND); // Append the error message to the log file
}

// Enable error reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Check if there is a connection error
    if ($conn->connect_error) {
        logError("Connection failed: " . $conn->connect_error); // Log the connection error
        die("Connection failed. Please try again later."); // Terminate the script and display an error message
    }

    // Check if the request method is POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $machine_id = $_POST['machine_id']; // Get the machine ID from the POST data
        $machine_name = $_POST['machine_name']; // Get the machine name from the POST data

        // Check if the 'add_machine' button was clicked
        if (isset($_POST['add_machine'])) {
            $checkSql = "SELECT * FROM machine WHERE id='$machine_id'"; // SQL query to check if the machine ID already exists
            $checkResult = $conn->query($checkSql); // Execute the query

            // If the machine ID already exists, display an error message
            if ($checkResult->num_rows > 0) {
                echo "Error: Machine ID already exists. Please choose a different ID.";
            } else {
                $sql = "INSERT INTO machine (id, machine_name) VALUES ('$machine_id', '$machine_name')"; // SQL query to insert a new machine
                $conn->query($sql); // Execute the query
                echo "New machine added successfully."; // Display a success message
            }
        }
        // Check if the 'update_machine' button was clicked
        elseif (isset($_POST['update_machine'])) {
            $original_id = $_POST['original_id']; // Store the original ID before updating

            if (!empty($machine_id) && !empty($original_id)) {
                // Check if the ID has changed
                if ($machine_id !== $original_id) {
                    // Check if the new ID already exists
                    $checkSql = "SELECT * FROM machine WHERE id='$machine_id'";
                    $checkResult = $conn->query($checkSql);

                    if ($checkResult->num_rows > 0) {
                        echo "Error: Machine ID already exists. Please choose a different ID.";
                    } else {
                        // Update both ID and name
                        $sql = "UPDATE machine SET id='$machine_id', machine_name='$machine_name' WHERE id='$original_id'";
                        $conn->query($sql);
                        echo "Machine updated successfully.";
                    }
                } else {
                    // Update name only if ID hasn't changed
                    $sql = "UPDATE machine SET machine_name='$machine_name' WHERE id='$machine_id'";
                    $conn->query($sql);
                    echo "Machine updated successfully.";
                }
            } else {
                logError("Update failed: Machine ID is not specified."); // Log the error if the machine ID is not specified
                echo "Error occurred. Please check the logs."; // Display an error message
            }
        }
    }

    // Check if the 'delete_id' parameter is set in the GET request
    if (isset($_GET['delete_id'])) {
        $machine_id = $_GET['delete_id']; // Get the machine ID from the GET data

        if (!empty($machine_id)) {
            $sql = "DELETE FROM machine WHERE id='$machine_id'"; // SQL query to delete the machine
            $conn->query($sql); // Execute the query
            echo "Machine deleted successfully."; // Display a success message
        } else {
            logError("Delete failed: Machine ID is not specified."); // Log the error if the machine ID is not specified
            echo "Error occurred. Please check the logs."; // Display an error message
        }
    }

    // SQL query to select all machines
    $sql = "SELECT * FROM machine";
    $result = $conn->query($sql); // Execute the query
} catch (mysqli_sql_exception $e) {
    logError("Database query failed: " . $e->getMessage()); // Log the database query error
    echo "Error occurred. Please check the logs."; // Display an error message
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Set the character encoding for the HTML document -->
    <meta name="author" content="Daniel Rosich" /> <!-- Set the author of the document -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Set the viewport settings for responsive design -->
    <link rel="stylesheet" href="../style.css"> <!-- Link to the main stylesheet -->
    <link rel="stylesheet" href="../factory-manager/styles/manage_machines.css"> <!-- Link to the specific stylesheet for managing machines -->
    <link rel="stylesheet" type="text/css" href="../css/logout.css">
    <title>Manage Machines Home</title> <!-- Set the title of the webpage -->
</head>
<body>
<h1>Manage Machines Home</h1> <!-- Main heading of the page -->
<div id="factory-manager-select">
    <ul>
        <li><a href="../home/home.html"><button>Monitor Factory Performance</button></a></li> <!-- Link to monitor factory performance -->
        <li><a href="../home/home.html"><button>Manage Jobs</button></a></li> <!-- Link to manage jobs -->
        <li><a href="../factory-manager/manage_machines.php"><button>Manage Machines</button></a></li> <!-- Link to manage machines -->
        <li><a href="../home/home.html"><button>Assign Roles</button></a></li> <!-- Link to assign roles -->
        <button class="logout-button" onclick="window.location.href='../home/logout.php'">Logout</button>
    </ul>
</div>

<h2>Add / Update Machine</h2> <!-- Heading for the form to add or update a machine -->
<form method="post" class="machine-form"> <!-- Form to add or update a machine -->
    <input type="hidden" name="original_id" id="original_id" value=""> <!-- Hidden field for original ID -->
    <label for="machine_id">Machine ID:</label> <!-- Label for the machine ID input -->
    <input type="text" name="machine_id" id="machine_id" value="" required> <!-- Input field for the machine ID -->
    <label for="machine_name">Machine Name:</label> <!-- Label for the machine name input -->
    <input type="text" name="machine_name" id="machine_name" required> <!-- Input field for the machine name -->

    <div class="form-buttons">
        <button type="submit" name="add_machine" class="add-button">Add Machine</button> <!-- Button to add a machine -->
        <button type="submit" name="update_machine" class="update-button">Update Machine</button> <!-- Button to update a machine -->
    </div>
</form>

<h2>Existing Machines</h2> <!-- Heading for the table of existing machines -->
<table class="machines-table"> <!-- Table to display existing machines -->
    <tr>
        <th>ID</th> <!-- Table header for machine ID -->
        <th>Name</th> <!-- Table header for machine name -->
        <th>Actions</th> <!-- Table header for actions (edit/delete) -->
    </tr>
    <?php if (isset($result) && $result->num_rows > 0): ?> <!-- Check if there are any machines in the database -->
        <?php while($row = $result->fetch_assoc()): ?> <!-- Loop through each machine -->
            <tr>
                <td><?php echo $row['id']; ?></td> <!-- Display the machine ID -->
                <td><?php echo $row['machine_name']; ?></td> <!-- Display the machine name -->
                <td>
                    <a href="#" onclick="editMachine('<?php echo $row['id']; ?>', '<?php echo $row['machine_name']; ?>')">Edit</a> <!-- Link to edit the machine -->
                    <a href="?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this machine?');">Delete</a> <!-- Link to delete the machine -->
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="3">No machines found.</td> <!-- Display a message if no machines are found -->
        </tr>
    <?php endif; ?>
</table>

<script>
    function editMachine(id, name) {
        document.getElementById('machine_id').value = id;  // Set the machine ID in the form
        document.getElementById('machine_name').value = name; // Set the machine name in the form
        document.getElementById('original_id').value = id; // Set original ID for update
    }
</script>

</body>
</html>

<?php $conn->close(); ?> <!-- Close the database connection -->
