<?php
session_start();
require '../config/config.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $hashed_password = $user['password'];

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $username;
            $_SESSION['role'] = $user['role'];

            logError("Role fetched from database: " . $_SESSION['role']);

            echo "<p>Login successful! Redirecting to your dashboard...</p>";
            header("refresh:2;url=" . getDashboardUrl($_SESSION['role'])); 
            exit;
        } else {
            $error = "Invalid username or password.";
            logError($error);
        }
    } else {
        $error = "Invalid username or password.";
        logError($error);
    }
}

function getDashboardUrl($role) {
    switch (strtolower($role)) { 
        case 'admin':
            return '../administrator/administrator.html';
        case 'factorymanager':
            return '../factory-manager/factory-manager.html';
        case 'productionoperator':
            return '../production-operator/production-operator.html';
        case 'auditor':
            return '../auditor/auditor.html';
        default:
            logError("Unknown role: $role");
            return 'login.php?error=unknownrole';
    }
}

function logError($errorMessage) {
    $logFile = '../logs/errors.log';
    $currentDateTime = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$currentDateTime] Error: $errorMessage" . PHP_EOL, FILE_APPEND);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../home/styles/style.css">
</head>
<body>
    <div class="error-container">
        <?php 
        if (isset($_GET['error']) && $_GET['error'] == 'unknownrole') {
            echo "<p class='error'>Error: Unknown user role. Please contact the administrator.</p>";
        }

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


