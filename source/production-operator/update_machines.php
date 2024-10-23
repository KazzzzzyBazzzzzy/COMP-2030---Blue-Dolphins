<?php
session_start();
require '../home/auth_check.php';
checkUserRole('productionoperator');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Samuel Ngiri" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/global.css">
    <link rel="stylesheet" type="text/css" href="../css/logout.css">
    <title>Update machines</title>
</head>
<h1>Update machines assigned to production operators</h1>
<body>
<div id="production-operator-select">
    <ul>
        <li><a href="#"><button>Monitor Factory Performance</button></a></li>
        <li><a href="../production-operator/update_machines.php"><button>Update Machines</button></a></li>
        <li><a href="#"><button>Update Jobs</button></a></li>
        <li><a href="../production-operator/task_notes.php"><button>Manage Task Notes</button></a></li>
        <button class="logout-button" onclick="window.location.href='../home/logout.php'">Logout</button>
    </ul>
</div>
</body>
</html>

