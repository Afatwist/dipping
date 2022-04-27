<?php
require __DIR__ . "/functions.php";
if (set_status($_POST)) {
	message_maker('status', 'complete');
	redirect_to('/users.php');
}

message_maker('status', 'error');
redirect_to('/status.php?id='.$_POST['id']);
