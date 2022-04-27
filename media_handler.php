<?php
require __DIR__ . "/functions.php";
$user = get_user_by_id($_POST['id']);

$avatar_name = file_rename(AVATARS, $_FILES['avatar']['name'], $_POST['id']);

if (!file_move($_FILES['avatar']['tmp_name'], AVATARS, $avatar_name)) {
	message_maker('avatar', 'cant save');
	redirect_to('/media.php?id=' . $_POST['id']);
}

if (isset($user['avatar']) and !empty($user['avatar'])) {
	file_delete(AVATARS, $user['avatar']);
}

if (avatar_save_in_db($_POST['id'], $avatar_name)) {
	message_maker('avatar', 'complete');
	redirect_to('/users.php');
}

message_maker('avatar', 'error');
redirect_to('/media.php?id=' . $_POST['id']);
