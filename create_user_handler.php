<?php
require __DIR__ . "/functions.php";

// проверяю уникальность емэйла
if (!is_unique_email($_POST['id'], $_POST['email'])) {
	message_maker('email', 'already use');
	redirect_to('/security.php?id=' . $_POST['id']);
}

// основная информация пользователя
$user_id = make_registration(['email' => $_POST['email'], 'password' => $_POST['password'], 'role' => 'user']);

if ($user_id == false) {
	message_maker('registration', 'error');
	redirect_to('/create_user.php');
}

// если ошибок нет сохраняю оставшиеся данные.
// аватар
$avatar_name = file_rename(AVATARS, $_FILES['avatar']['name'], $user_id);

if (!file_move($_FILES['avatar']['tmp_name'], AVATARS, $avatar_name)) {
	message_maker('avatar', 'cant save');
	redirect_to('/create_user.php');
}
if (!avatar_save_in_db($user_id, $avatar_name)) $message = 'error';

//общая информация
if (!edit_user_general_info($user_id, $_POST)) $message = 'error';

// статус пользователя
if (!set_status(['id' => $user_id, 'status' => $_POST['status']])) $message = 'error';

// социальные сети
if (!edit_user_social_info($user_id, $_POST)) $message = 'error';


if ($message = 'error') {
	message_maker('create_user', 'error');
	redirect_to('/create_user.php');
}

message_maker('create_user', 'complete');
redirect_to('/create_user.php');
