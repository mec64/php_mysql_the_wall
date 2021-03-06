<?php  

	session_start();
	include('connection.php');
	$link = mysqli_connect("localhost", "root", "root", "mydb");

	$messages_query = "SELECT users.id AS user_id, users.first_name AS first_name, users.last_name AS last_name,
					   messages.message AS message, messages.created_at AS created_at 
					   FROM users
					   JOIN messages 
					   ON users.id = messages.user_id
					   ORDER BY created_at DESC";
	$_SESSION['all_messages'][] = fetch($messages_query);

	if(!empty($_POST['action']) && $_POST['action'] == 'register')
	{	
		if(empty($_POST['f_name']) || is_numeric($_POST['f_name']))
		{
			$_SESSION['errors'][] = 'Please use a proper first name, without numbers';
		}
		if(empty($_POST['l_name']) || is_numeric($_POST['l_name']))
		{
			$_SESSION['errors'][] = 'Please use a proper last name, without numbers';
		}
		if(empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
		{
			$_SESSION['errors'][] = 'Please use a valid email address';
		}
		if(empty($_POST['password']) || strlen($_POST['password']) < 6)
		{
			$_SESSION['errors'][] = 'Please use a password longer than 6 characters';
		}
		if($_POST['confirm_pass'] !== $_POST['password'])
		{
			$_SESSION['errors'][] = 'Passwords do not match';
		}
		else
		{
			$esc_f_name = mysqli_real_escape_string($link, $_POST['f_name']);
			$esc_l_name = mysqli_real_escape_string($link, $_POST['l_name']);
			$esc_email = mysqli_real_escape_string($link, $_POST['email']);
			$esc_password = mysqli_real_escape_string($link, $_POST['password']);
			$query = "INSERT INTO users (first_name, last_name, email, password, created_at, updated_at)
					  VALUES('{$esc_f_name}', '{$esc_l_name}', '{$esc_email}', '{$esc_password}',
					  NOW(), NOW())";
			if(!run_mysql_query($query))
			{
				$_SESSION['errors'][] = 'Something went wrong, please try again soon!';
			}
			else
			{
				$_SESSION['success_message'] = 'Successfully registered! You may now login below!';
			}
		}
		header('location: index.php');
		die();
	}
	elseif(!empty($_POST['action']) && $_POST['action'] == 'login')
	{
		$query = "SELECT * FROM users WHERE users.email = '{$_POST['email']}'
				  AND users.password = '{$_POST['password']}'";
		$user_query = fetch($query);

		if(count($user_query) > 0)
		{
			$_SESSION['user_id'] = $user_query['id'];
			$_SESSION['user_name'] = $user_query['first_name'];
			$_SESSION['logged_in'] = TRUE;
			// var_dump($user_query);
			header('location: success.php');
			die();
		}
		else
		{
			$_SESSION['errors'][] = 'User not found. Please try again!';
			header('location: index.php');
			die();
		}
	}
	elseif(!empty($_POST['action']) && $_POST['action'] == 'post_message')
	{
		if(empty($_POST['message']))
		{
			$_SESSION['errors'][] = 'You must type something within the message field to post a message!';
		}
		else
		{
			$esc_message = mysqli_real_escape_string($link, $_POST['message']);
			$query_mess = "INSERT INTO messages (user_id, message, created_at, updated_at)
					  	   VALUES('{$_SESSION['user_id']}', '{$esc_message}', NOW(), NOW())";

			if(!run_mysql_query($query_mess))
			{
				$_SESSION['errors'][] = 'We have encountered an error! Please submit your message again!';
			}
			else
			{
				$_SESSION['message_success'] = 'Congratulations! Your message has been posted!';
			}		  
		}
		header('location: success.php');
		die();
	}
	elseif(!empty($_POST['action']) && $_POST['action'] == 'post_comment')
	{
		if(empty($_POST['comment']))
		{
			$_SESSION['errors'][] = 'You must type something within the comment field to post a comment!';
		}
		else
		{
			$esc_comment = mysqli_real_escape_string($link, $_POST['comment']);
			$esc_id = mysqli_real_escape_string($link, $_POST['message_id']);
			$query_comment = "INSERT INTO comments (coment, created_at, updated_at, user_id, message_id)
					  	      VALUES('{$esc_comment}', NOW(), NOW(), '{$_SESSION['user_id']}',
					  	      '{$esc_id}' )";
			if(!run_mysql_query($query_comment))
			{
				$_SESSION['errors'][] = 'We have encountered an error! Please submit your comment again!';
			}
			else
			{
				$_SESSION['message_success'] = 'Congratulations! Your comment has been posted!';
			}		  
		}
		header('location: success.php');
		die();
	}
	elseif(!empty($_POST['action']) && $_POST['action'] == 'delete')
	{
		$esc_row = mysqli_real_escape_string($link, $_POST['row']);
		$query_delete = "DELETE FROM messages
						 WHERE id = '{$esc_row}'";
						 // var_dump($query_delete);
						 // die();
		if(!run_mysql_query($query_delete))
		{
			$_SESSION['errors'][] = 'Sorry your message was not deleted. Please try again.';
		}
		else
		{
			$_SESSION['message_success'] = 'Your message was successfully deleted!';
		}
		header('location: success.php');
	}
	else //malicious navigation, or someone is logging off
	{
		session_destroy();
		header('location: index.php');
		die();
	}

?>