<?php
session_start();
session_destroy();
header("Location: /melody-masters/index.php");
exit;
