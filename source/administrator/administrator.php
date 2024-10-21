<?php
session_start();
require '../home/auth_check.php';
checkUserRole('admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Daniel Rosich" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/global.css">
    <link rel="stylesheet" type="text/css" href="../css/logout.css">
    <title>Administrator Home</title>
</head>
<body>

    <h1>Administrator Home</h1>
    <div id="admin-select">
        <ul>
            <li><a href="../administrator/manage_user.php"><button>User Accounts & Roles</button></a></li>
            <li><a href="#"><button>Dashboard</button></a></li>
            <button class="logout-button" onclick="window.location.href='../home/logout.php'">Logout</button>
        </ul>
    </div>

</body>
</html>
