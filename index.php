<?php
// index.php : Main page wrapper and singleton class setup
// @author John Day jdayworkplace@gmail.com

require_once 'SimpleStatus.php';

$status = new SimpleStatus();
$_GET['page'] ? $page = $_GET['page'] : $page = 'status';

session_start();
?>
<html>
<head>
    <title>JDayStudio Simple Status</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php echo $status->NavigationBar($page); ?>
    <?php echo $status->PageContents($page); ?>
</body>
</html>
