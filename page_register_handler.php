<?php
require __DIR__ . "/functions.php";

$result =  get_user_by_email($_POST['email']);
if (is_array($result)) {
	message_maker('email', 'already use');
	redirect_to('/page_register.php');
}

if (make_registration($_POST)){
	message_maker('registration', 'complete');
	redirect_to('/page_login.php');
}

message_maker('registration', 'error');
redirect_to('/page_register.php');
