<?php
require __DIR__ . "/functions.php";
$user = get_user_by_id($_GET['id']);

// удаление сессии, если пользователь удаляет сам себя
if(user_identification($user['id'])) unset($_SESSION['user']);

// удаление из БД
if (!user_delete($_GET['id'])) {
	message_maker('delete_user', 'error');
	redirect_to('/users.php');
}

// удаление аватара если он есть
if (empty($user['avatar'])) {
	message_maker('delete_user', 'complete');
	redirect_to('/page_register.php');
}

if (file_delete(AVATARS, $user['avatar'])) {
	message_maker('delete_user', 'complete');
	redirect_to('/page_register.php');
}

message_maker('delete_user', 'error');
redirect_to('/users.php');
