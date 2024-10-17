<?php
require '../config/config.php';

$id = 4;  
$username = 'auditor';
$password = 'Audit1000@'; 
$role = 'auditor';

$sql_check = "SELECT * FROM users WHERE username = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $username);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo "Error: User already exists!";
} else {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql_insert = "INSERT INTO users (id, username, password, role) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("isss", $id, $username, $hashed_password, $role);

    if ($stmt_insert->execute()) {
        echo "New user added successfully!";
    } else {
        echo "Error: " . $stmt_insert->error;
    }
}

$stmt_check->close();
$stmt_insert->close();
$conn->close();
?>
