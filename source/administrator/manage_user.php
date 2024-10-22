<?php
session_start();

require '../home/auth_check.php';
checkUserRole('admin');

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


include '../administrator/temp/manage_user_layout.html';
?>
