<?php
if (session_status() != 2) session_start();
define('AVATARS', __DIR__ . '\storage\avatars\\');
define('AVATARS_HTML', '/storage/avatars/');

// DATA BASE
$pdo = new PDO("mysql:host=localhost; dbname=dipping", "root", null);

function getAll($table)
{
	global $pdo;
	$sql = "SELECT * FROM $table";
	$statement = $pdo->prepare($sql);
	$statement->execute();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	return $result;
}

function getOne($table, $field, $value)
{
	global $pdo;
	$sql = "SELECT * FROM $table WHERE $field=:$field LIMIT 1";
	$statement = $pdo->prepare($sql);
	$statement->bindParam($field, $value);
	$statement->execute();
	$result = $statement->fetch(PDO::FETCH_ASSOC);
	return $result;
}

function store($table, $data)
{
	global $pdo;
	$keys = array_keys($data);
	$fields = implode(', ', $keys);
	$placeholders = ':' . implode(', :', $keys);

	$sql = "INSERT INTO $table ($fields) VALUE ($placeholders)";
	$statement = $pdo->prepare($sql);
	$statement->execute($data);
	$error = $statement->errorInfo();
	if ($error[0] == '00000') return $pdo->lastInsertId();
	return false;
}

function update($table, $data)
{
	global $pdo;
	$keyString = '';
	foreach ($data as $key => $value) {
		$keyString .= $key . '=:' . $key . ', ';
	}
	$keyString = rtrim($keyString, ', ');

	$sql = "UPDATE $table SET $keyString WHERE id=:id";
	$statement = $pdo->prepare($sql);
	$statement->execute($data);
	$result = $statement->errorInfo();
	if ($result[0] == '00000') return true;
	return false;
}

function delete($table, $id)
{
	global $pdo;
	$sql = "DELETE FROM $table WHERE id=:id";
	$statement = $pdo->prepare($sql);
	$statement->bindParam('id', $id);
	$statement->execute();
	return $statement->errorInfo();
}

// USERS
// create user
function make_registration($data)
{
	$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
	$result = store('users', $data);
	if ($result == false) return false;
	return $result;
}
// get some user
function get_all_users()
{
	$result = getAll('users');
	return $result;
}

function get_user_by_id($id)
{
	$user = getOne('users', 'id', $id);
	return $user;
}

function get_user_by_email($email)
{
	$result = getOne('users', 'email', $email);
	if (is_array($result)) return $result;
	return false;
}

// edit user info
function edit_user_info($data)
{
	if (update('users', $data)) return true;
	return false;
}

function edit_user_security_info($id, $email, $password)
{
	$data['id'] = $id;
	if (isset($email) and !empty($email)) {
		$data['email'] = $email;
	}
	if (isset($password) and !empty($password)) {
		$data['password'] = password_hash($password, PASSWORD_DEFAULT);
	}

	$result = update('users', $data);
	return $result;
}

function edit_user_general_info($user_id, $data)
{
	$info = [
		'id' => $user_id,
		'username' => $data['username'],
		'work_place' => $data['work_place'],
		'phone' => $data['phone'],
		'address' => $data['address']
	];
	if (edit_user_info($info)) return true;
	return false;
}

function edit_user_social_info($user_id, $data)
{
	$info = [
		'id' => $user_id,
		'vk' => $data['vk'],
		'telegram' => $data['telegram'],
		'instagram' => $data['instagram']
	];
	if (edit_user_info($info)) return true;
	return false;
}

function set_status($data)
{
	if (update('users', $data)) return true;
	return false;
}

function avatar_save_in_db($user_id, $avatar_name)
{

	if (update('users', ['id' => $user_id, 'avatar' => $avatar_name])) return true;
	return false;
}

function edit_user_email($user_id, $new_email)
{
	if (isset($new_email) and !empty($new_email)) {
		if (edit_user_info(['id' => $user_id, 'email' => $new_email])) return true;
	}
	return false;
}

function edit_user_password($user_id, $new_password)
{
	if (isset($new_password) and !empty($new_password)) {
		if (edit_user_info(['id' => $user_id, 'password' => $new_password])) return true;
	}
	return false;
}

// delete user
function user_delete($id)
{
	$result =  delete('users', $id);
	if ($result[0] == '00000') return true;
	return false;
}

//testing
function check_password($id, $password)
{
	if (isset($password) and !empty($password)) {
		$user = get_user_by_id($id);
		if (password_verify($password, $user['password'])) {
			return true;
		}
	}
	return false;
}

function is_unique_email($id, $email)
{
	if (isset($email) and !empty($email)) {
		$user = get_user_by_email($email);
		if (is_array($user)) {
			if ($user['id'] != $id) return false;
		}
	}
	return true;
}

function verification_new_password($user_id, $old_password, $new_password, $confirm_password)
{
	if (isset($new_password) and !empty($new_password)) {
		if (isset($confirm_password) and !empty($confirm_password)) {
			if ($new_password != $confirm_password) {
				return 'password not confirm';
			}
			if (isset($old_password) and !empty($old_password)) {
				if (!check_password($user_id, $old_password)) return 'bad password';
			} else return 'empty old password';
		} else return 'empty confirm password';
	} else return false;
	return true;
}
function is_not_login()
{
	if (isset($_SESSION['user']) and !empty($_SESSION['user'])) {
		return false;
	}
	return true;
}

function check()
{
	if (isset($_SESSION['user']) and !empty($_SESSION['user'])) {
		return true;
	}
	return false;
}

function is_admin()
{
	if (isset($_SESSION['user']['role']) and $_SESSION['user']['role'] == 'admin') {
		return true;
	}
	return false;
}

function user_identification($user_id)
{
	if (check() and $_SESSION['user']['id'] == $user_id) {
		return true;
	}
	return false;
}

// show user information
function show_status($status)
{
	if (isset($status)) {
		if ($status == 'online') return 'success';
		if ($status == 'away') return 'warning';
		if ($status == 'not worry') return 'danger';
	}
	return 'secondary';
}

function telephone($telephone)
{
	if (isset($telephone) and !empty($telephone)) {
		$telephone = str_replace(['-', ' '], '', $telephone);
	}
	return $telephone;
}

function username_to_tag($username)
{
	if (isset($username) and !empty($username)) {
		$username = strtolower($username);
	}
	return $username;
}

// MESSAGES

function message_maker($type, $description)
{
	$_SESSION['message'][$type] = $description;
}

function message_show()
{
	if (!isset($_SESSION['message'])) return;

	// email
	if (isset($_SESSION['message']['email'])) {
		if ($_SESSION['message']['email'] == 'already use') $message = 'Этот email уже используется другим пользователем!';
		if ($_SESSION['message']['email'] == 'not found') $message = 'Такой e-mail не найден!';
	}

	// password
	if (isset($_SESSION['message']['password'])) {
		if ($_SESSION['message']['password'] == 'bad password') $message = 'Пароль не правильный!';
		if ($_SESSION['message']['password'] == 'password not confirm') $message = 'Новый пароль и подтверждение не совпадают!';
		if ($_SESSION['message']['password'] == 'enter password') $message = 'Введите пароли';
		if ($_SESSION['message']['password'] == 'empty old password') $message = 'Вы не ввели старый пароль!';
		if ($_SESSION['message']['password'] == 'empty confirm password') $message = 'Вы не ввели пароль подтверждение!';
	}

	// registration
	if (isset($_SESSION['message']['registration'])) {
		if ($_SESSION['message']['registration'] == 'complete') $message = 'Регистрация прошла успешно!';
		if ($_SESSION['message']['registration'] == 'error') $message = 'Возникли ошибки при регистрации! попробуйте позже!';
	}

	// edit user profile
	if (isset($_SESSION['message']['edit_user'])) {
		if ($_SESSION['message']['edit_user'] == 'complete') $message = 'Ваши данные обновлены!';
		if ($_SESSION['message']['edit_user'] == 'error') $message = 'Возникли ошибки при обновлении данных! Попробуйте позже!';
	}

	// delete user
	if (isset($_SESSION['message']['delete_user'])) {
		if ($_SESSION['message']['delete_user'] == 'complete') $message = 'Пользователь удален';
		if ($_SESSION['message']['delete_user'] == 'error') $message = 'Возникли ошибки при удалении пользователя! Попробуйте позже!';
	}

	// create user
	if (isset($_SESSION['message']['create_user'])) {
		if ($_SESSION['message']['create_user'] == 'complete') $message = 'Новый пользователь создан!';
		if ($_SESSION['message']['create_user'] == 'error') $message = 'Возникли ошибки при создании нового пользователя! Попробуйте позже!';
	}

	// login
	if (isset($_SESSION['message']['login'])) {
		if ($_SESSION['message']['login'] == 'complete') $message = 'Вы вошли в систему!';
		if ($_SESSION['message']['login'] == 'for logged only') $message = 'Войдите чтобы просматривать эту страницу!';
	}

	// security
	if (isset($_SESSION['message']['security'])) {
		if ($_SESSION['message']['security'] == 'not access') $message = 'Доступ запрещен!';
	}

	// status
	if (isset($_SESSION['message']['status'])) {
		if ($_SESSION['message']['status'] == 'complete') $message = 'Статус обновлен!';
		if ($_SESSION['message']['status'] == 'error') $message = 'Невозможно обновить статус!';
	}

	// avatars
	if (isset($_SESSION['message']['avatar'])) {
		if ($_SESSION['message']['avatar'] == 'cant save') $message = 'Невозможно сохранить выбранный аватар!';
		if ($_SESSION['message']['avatar'] == 'error') $message = 'Возникли ошибки! попробуйте позже!';
		if ($_SESSION['message']['avatar'] == 'complete') $message = 'Аватар обновлен!';
	}
	// make message
	$class = 'danger';
	if (in_array('complete', $_SESSION['message'])) $class = 'warning';


	$box = '<div class="alert alert-' . $class . ' text-dark" role="alert">
   	<strong>Уведомление!</strong><br /> ' . $message . '</div>';

	unset($_SESSION['message']);
	return $box;
}

// FILE MANAGER
function file_rename($path, $fileName, $user_id)
{
	$ext = pathinfo($fileName);
	$ext = $ext['extension'];
	do {
		$name = uniqid('id_' . $user_id . '_');
		$name = $name . '.' . $ext;
	} while (file_exists($path . $name));
	return $name;
}

function file_move($tmp_path, $path, $fileName)
{
	if (move_uploaded_file($tmp_path, $path . $fileName)) return true;
	return false;
}

function file_delete($path, $fileName)
{
	if (file_exists($path . $fileName)) {
		if (unlink($path . $fileName)) return true;
	}
	return false;
}

// SPECIAL

function redirect_to($url)
{
	header('location: ' . $url);
	exit;
}
