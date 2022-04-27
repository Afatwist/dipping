<?php
require __DIR__ . "/functions.php";

if (edit_user_general_info($_POST['id'], $_POST)) {
	message_maker('edit_user', 'complete');
	redirect_to('/edit.php?id='.$_POST['id']);
}
message_maker('edit_user', 'error');
redirect_to('/edit.php?id='.$_POST['id']);
