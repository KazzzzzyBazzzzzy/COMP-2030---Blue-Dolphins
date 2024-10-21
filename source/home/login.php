<?php
// Start the session
session_start();

// Include configuration file (database connection)
require '../config/config.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); // Sanitize the username input
    $password = trim($_POST['password']); // Sanitize the password input

    // SQL query to select username, password, and role
    $sql = "SELECT username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql); // Prepare the SQL statement
    $stmt->bind_param("s", $username); // Bind parameters
    $stmt->execute(); // Execute the query
    $result = $stmt->get_result(); // Get the result

    // Check if a user exists with the provided username
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc(); // Fetch the user details
        $hashed_password = $user['password']; // Get the hashed password

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Set session variables for the user
            $_SESSION['user_id'] = $username; // Store username in session
            $_SESSION['role'] = $user['role']; // Store user role in session

            // Log successful login
            logError("User logged in successfully: " . $username);

            // Redirect based on the role
            header("Location: " . getDashboardUrl($user['role']));
            exit; // Terminate script after redirect
        } else {
            // Invalid credentials
            $error = "Invalid username or password.";
            logError($error);
        }
    } else {
        // Username not found in the database
        $error = "Invalid username or password.";
        logError($error);
    }
}

// Function to get the correct dashboard URL based on role
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
            logError("Unknown role: $role");
            return 'login.php?error=unknownrole';
    }
}

// Function to log errors to a file
function logError($errorMessage) {
    $logFile = '../logs/errors.log'; // Path to the log file
    $currentDateTime = date('Y-m-d H:i:s'); // Get current date and time
    file_put_contents($logFile, "[$currentDateTime] Error: $errorMessage" . PHP_EOL, FILE_APPEND); // Append the log
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="../css/login.css">
</head>
<body>
    <div class="error-container">
        <?php
        // Display error for unknown role
        if (isset($_GET['error']) && $_GET['error'] == 'unknownrole') {
            echo "<p class='error'>Error: Unknown user role. Please contact the administrator.</p>";
        }

        // Display any login errors
        if (isset($error)) {
            echo "<p class='error'>$error</p>";
        }
        ?>
    </div>
    
    <form action="login.php" method="POST">
        <h2>Login</h2>
        Username:
        <select name="username" required>
            <option value="" disabled selected>Select a username</option>
            <option value="admin">Admin</option>
            <option value="factorymanager">Factory Manager</option>
            <option value="productionoperator">Production Operator</option>
            <option value="auditor">Auditor</option>
        </select><br>
        Password: <input type="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
