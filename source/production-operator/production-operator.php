<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'productionoperator') {
    header("Location: ../home/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Daniel Rosich" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/global.css">
    <link rel="stylesheet" type="text/css" href="../css/logout.css">
    <title>Production Operator Home</title>
</head>
<h1>Production Operator Home</h1>
<body>
<div id="production-operator-select">
    <ul>
        <li><a href="#"><button>Monitor Factory Performance</button></a></li>
        <li><a href="#"><button>Update Machines</button></a></li>
        <li><a href="#"><button>Update Jobs</button></a></li>
        <li><a href="../production-operator/task_notes.php"><button>Manage Task Notes</button></a></li>
        <button class="logout-button" onclick="window.location.href='../home/logout.php'">Logout</button>
    </ul>
</div>
</body>
</html>

