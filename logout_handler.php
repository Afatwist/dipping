<?php
require __DIR__ . "/functions.php";
unset($_SESSION['user']);
redirect_to('/page_login.php');
