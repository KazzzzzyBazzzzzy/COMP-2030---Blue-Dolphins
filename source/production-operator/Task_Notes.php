<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Bailey Boyd, Daniel Rosich" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Production Operator Home</title>
</head>
<h1>Add Task Notes</h1>
<body>
<div id="production-operator-select">
    <ul>
        <li><a href="../home/home.html"><button>Monitor Factory Performance</button></a></li>
        <li><a href="../home/home.html"><button>Update Machines</button></a></li>
        <li><a href="../home/home.html"><button>Update Jobs</button></a></li>
        <li><a href="../home/home.html"><button>Create Task Notes</button></a></li>
    </ul>
</div>

<div id="factory-logs">
    <h2>Factory Logs</h2>
    
</body>
</html>

<?php
$host = 'localhost';
$dbname = 'smd_database';  
$user = 'root';            
$pass = '';                

$sql = "SELECT temperature, humidity FROM factory_logs LIMIT 10";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>