<?php
// Start the session to manage user sessions
session_start();

// Include the configuration file which contains database connection details
require '../config/config.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username']; // Get the username from the POST data
    $password = $_POST['password']; // Get the password from the POST data

    // SQL query to select the username, password, and role from the users table
    $sql = "SELECT username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql); // Prepare the SQL statement
    $stmt->bind_param("s", $username); // Bind the username parameter to the SQL statement
    $stmt->execute(); // Execute the SQL statement
    $result = $stmt->get_result(); // Get the result of the SQL statement

    // Check if the username exists in the database
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc(); // Fetch the user details
        $hashed_password = $user['password']; // Get the hashed password from the database

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $username; // Set the user ID in the session
            $_SESSION['role'] = $user['role']; // Set the user role in the session

            logError("Role fetched from database: " . $_SESSION['role']); // Log the role fetched from the database

            echo "<p>Login successful! Redirecting to your dashboard...</p>"; // Display a success message
            header("refresh:2;url=" . getDashboardUrl($_SESSION['role'])); // Redirect to the appropriate dashboard
            exit; // Terminate the script
        } else {
            $error = "Invalid username or password."; // Set the error message
            logError($error); // Log the error
        }
    } else {
        $error = "Invalid username or password."; // Set the error message
        logError($error); // Log the error
    }
}

// Function to get the dashboard URL based on the user role
function getDashboardUrl($role) {
    switch (strtolower($role)) {
        case 'admin':
            return '../administrator/administrator.php';
        case 'factorymanager':
            return '../factory-manager/factory-manager.php';
        case 'productionoperator':
            return '../production-operator/production-operator.php';
        case 'auditor':
            return '../auditor/auditor.php';
        default:
            logError("Unknown role: $role"); // Log the unknown role error
            return 'login.php?error=unknownrole'; // Return the error URL
    }
}

// Function to log errors to a file
function logError($errorMessage) {
    $logFile = '../logs/errors.log'; // Path to the log file
    $currentDateTime = date('Y-m-d H:i:s'); // Get the current date and time
    file_put_contents($logFile, "[$currentDateTime] Error: $errorMessage" . PHP_EOL, FILE_APPEND); // Append the error message to the log file
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Set the character encoding for the HTML document -->
    <title>Login</title> <!-- Set the title of the webpage -->
    <link rel="stylesheet" href="../home/styles/style.css"> <!-- Link to the stylesheet -->
</head>
<body>
    <div class="error-container">
        <?php
        // Check if there is an error in the GET request
        if (isset($_GET['error']) && $_GET['error'] == 'unknownrole') {
            echo "<p class='error'>Error: Unknown user role. Please contact the administrator.</p>"; // Display the error message
        }

        // Check if there is an error in the POST request
        if (isset($error)) {
            echo "<p class='error'>$error</p>"; // Display the error message
        }
        ?>
    </div>
    <form action="login.php" method="POST"> <!-- Form for user login -->
        <h2>Login</h2> <!-- Heading for the login form -->
        Username:
        <select name="username" required> <!-- Dropdown for selecting the username -->
            <option value="" disabled selected>Select a username</option>
            <option value="admin">Admin</option>
            <option value="factorymanager">Factory Manager</option>
            <option value="productionoperator">Production Operator</option>
            <option value="auditor">Auditor</option>
        </select><br>
        Password: <input type="password" name="password" required><br> <!-- Input field for the password -->
        <button type="submit">Login</button> <!-- Submit button for the login form -->
    </form>
</body>
</html>
