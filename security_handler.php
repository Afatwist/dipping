<?php
require __DIR__ . "/functions.php";

// проверка на смену емейла и его уникальность
if (!is_unique_email($_POST['id'], $_POST['email'])) {
	message_maker('email', 'already use');
	redirect_to('/security.php?id=' . $_POST['id']);
}

// смена емейла
if (edit_user_email($_POST['id'], $_POST['email'])) $message = 'complete';

// проверка нового пароля 
$result = verification_new_password($_POST['id'], $_POST['old_password'], $_POST['new_password'], $_POST['confirm_password']);

//  вывод ошибок при не правильном заполнении полей пароля
if (isset($result) and !is_bool($result)) {
	message_maker('password', $result);
	redirect_to('/security.php?id=' . $_POST['id']);
}

// смена пароля
if ($result == true) {
	if (edit_user_password($_POST['id'], $_POST['new_password'])) $message = 'complete';
}

// вывод сообщения при успехе
if ($message == 'complete') {
	message_maker('edit_user', 'complete');
	redirect_to('/security.php?id=' . $_POST['id']);
}
// вывод сообщения при непредвиденных ошибках
message_maker('edit_user', 'error');
redirect_to('/security.php?id=' . $_POST['id']);
