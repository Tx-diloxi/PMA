<?php
require_once __DIR__ . '/../src/functions.php';
startSession();
session_unset();
session_destroy();
redirect('login.php');