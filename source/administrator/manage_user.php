<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
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

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $role = $_POST['role'];

        if (isset($_POST['add_user'])) {
            $checkSql = "SELECT * FROM users WHERE id='$user_id'";
            $checkResult = $conn->query($checkSql);

            if ($checkResult->num_rows > 0) {
                echo "Error: User ID already exists. Please choose a different ID.";
            } else {
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $sql = "INSERT INTO users (id, username, password, role) VALUES ('$user_id', '$username', '$password', '$role')";
                $conn->query($sql);
                echo "New user added successfully.";
            }
        } elseif (isset($_POST['update_user'])) {
            $original_id = $_POST['original_id'];

            if (!empty($user_id) && !empty($original_id)) {
                if ($user_id !== $original_id) {
                    $checkSql = "SELECT * FROM users WHERE id='$user_id'";
                    $checkResult = $conn->query($checkSql);

                    if ($checkResult->num_rows > 0) {
                        echo "Error: User ID already exists. Please choose a different ID.";
                    } else {
                        $sql = "UPDATE users SET id='$user_id', username='$username', role='$role' WHERE id='$original_id'";
                        $conn->query($sql);
                        echo "User updated successfully.";
                    }
                } else {
                    $sql = "UPDATE users SET username='$username', role='$role' WHERE id='$user_id'";
                    $conn->query($sql);
                    echo "User updated successfully.";
                }
            } else {
                logError("Update failed: User ID is not specified.");
                echo "Error occurred. Please check the logs.";
            }
        }
    }

    if (isset($_GET['delete_id'])) {
        $user_id = $_GET['delete_id'];

        if (!empty($user_id)) {
            $sql = "DELETE FROM users WHERE id='$user_id'";
            $conn->query($sql);
            echo "User deleted successfully.";
        } else {
            logError("Delete failed: User ID is not specified.");
            echo "Error occurred. Please check the logs.";
        }
    }

    $sql = "SELECT * FROM users";
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
    <meta name="author" content="Your Name" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/global.css">
    <link rel="stylesheet" type="text/css" href="../css/manage_users.css">
    <link rel="stylesheet" type="text/css" href="../css/logout.css">
    <script src="../javascript/manage_users.js"></script>
    <title>Manage Users Home</title>
</head>
<body>
<h1>Manage Users</h1>
<div id="admin-select">
    <ul>
        <li><a href="../administrator/administrator_dashboard.php"><button>Admin Dashboard</button></a></li>
        <button class="logout-button" onclick="window.location.href='../home/logout.php'">Logout</button>
    </ul>
</div>

<h2>Add / Update User</h2>
<form method="post" class="user-form">
    <input type="hidden" name="original_id" id="original_id" value="">
    <label for="user_id">User ID:</label>
    <input type="text" name="user_id" id="user_id" value="" required>
    <label for="username">Username:</label>
    <input type="text" name="username" id="username" required>
    <label for="password">Password:</label>
    <input type="password" name="password" id="password">
    <label for="role">Role:</label>
    <select name="role" id="role" required>
        <option value="admin">Admin</option>
        <option value="factorymanager">Factory Manager</option>
        <option value="productionoperator">Production Operator</option>
        <option value="auditor">Auditor</option>
    </select>

    <div class="form-buttons">
        <button type="submit" name="add_user" class="add-button">Add User</button>
        <button type="submit" name="update_user" class="update-button">Update User</button>
    </div>
</form>

<h2>Existing Users</h2>
<table class="users-table">
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Role</th>
        <th>Actions</th>
    </tr>
    <?php if (isset($result) && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['role']; ?></td>
                <td>
                    <a href="#" onclick="editUser('<?php echo $row['id']; ?>', '<?php echo $row['username']; ?>', '<?php echo $row['role']; ?>')">Edit</a>
                    <a href="?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="4">No users found.</td>
        </tr>
    <?php endif; ?>
</table>
</body>
</html>
