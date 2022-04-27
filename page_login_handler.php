<?php
require __DIR__ . "/functions.php";

$user =  get_user_by_email($_POST['email']);
if (!is_array($user)) {
	message_maker('email', 'not found');
	redirect_to('/page_login.php');
}

if (password_verify($_POST['password'], $user['password'])) {
	$_SESSION['user'] = $user;
	unset($_SESSION['user']['password']); // надо ли так делать? или хранить хэш пароля в сессии можно?
	message_maker('login', 'complete');
	redirect_to('/users.php');
}

message_maker('password', 'bad password');
redirect_to('/page_login.php');
