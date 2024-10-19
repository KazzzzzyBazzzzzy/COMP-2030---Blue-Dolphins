<?php
// Include the configuration file which contains database connection details
require '../config/config.php';

// Define the user details
$id = 4;
$username = 'auditor';
$password = 'Audit1000@';
$role = 'auditor';

// SQL query to check if the user already exists
$sql_check = "SELECT * FROM users WHERE username = ?";
$stmt_check = $conn->prepare($sql_check); // Prepare the SQL statement
$stmt_check->bind_param("s", $username); // Bind the username parameter to the SQL statement
$stmt_check->execute(); // Execute the SQL statement
$result_check = $stmt_check->get_result(); // Get the result of the SQL statement

// Check if the user already exists in the database
if ($result_check->num_rows > 0) {
    echo "Error: User already exists!"; // Display an error message if the user already exists
} else {
    // Hash the password using the default password hashing algorithm
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // SQL query to insert the new user into the users table
    $sql_insert = "INSERT INTO users (id, username, password, role) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert); // Prepare the SQL statement
    $stmt_insert->bind_param("isss", $id, $username, $hashed_password, $role); // Bind the parameters to the SQL statement

    // Execute the SQL statement and check if the insertion was successful
    if ($stmt_insert->execute()) {
        echo "New user added successfully!"; // Display a success message if the user was added successfully
    } else {
        echo "Error: " . $stmt_insert->error; // Display an error message if the insertion failed
    }
}

// Close the prepared statements and the database connection
$stmt_check->close();
$stmt_insert->close();
$conn->close();
?>
