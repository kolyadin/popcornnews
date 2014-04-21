<?php
function cat($mixed){
  echo '---' . date('Y-m-d H-i-s') . '  :';
  if (is_array($mixed)) print_r($mixed);
  else echo $mixed;
  echo '---' . "\n";
}

include 'inc/connect.php';
$act = $_SERVER['argv'][1];

switch ($act){
  case 'user_check':
	 define ('SUPPORT_MAIL', 'support@popcornnews.ru'); /*от кого письмо приходить*/
	 /*
	 send_email - поле в бд, если отослано письмо то оно меняется на 1
	 ldate - дата последнего входа
	 */

// 	 Удаление
	 $sql = 'SELECT id, name FROM popkorn_users WHERE send_email = 1 AND ldate<'.strtotime('-6 months +3 day');
	 if($result = mysql_query($sql,$link)){
		$usersDel = '';
		while ($res = mysql_fetch_assoc($result)) $usersDel .= $res['id'] . ' : ' . $res['name'] . ', ' . "\n";
		$usersDel = substr($usersDel, 0, -2);
		if ($usersDel){
		  $sql = 'DELETE FROM popkorn_users WHERE ldate<'.strtotime('-6 months +3 day');
		  if ($result = mysql_query($sql, $link)) cat('Удалены сл. пользователи: ' . "\n" . $usersDel);
		  else cat(mysql_error());
		}else cat('Некого удалять!');
	 }
	 

// 	 Рассылка уведомлений
	 $sql = 'SELECT id, name, email FROM popkorn_users WHERE (send_email = 0 OR send_email = "" OR send_email IS NULL) AND ldate>'.strtotime('-6 months').' AND ldate <'.strtotime('-6 months +3 day');
// 	 $sql = 'SELECT id, name, email FROM popkorn_users WHERE (send_email = 0 OR send_email = "" OR send_email IS NULL) AND ldate<'.strtotime('-6 months');
	 if($result = mysql_query($sql,$link))
		if ($res = mysql_fetch_assoc($result)){
		  $usersWarns = '';
		  $usersMails = '';
		  while ($res){
			 $usersWarns .= $res['name'] . '<' . $res['email'] . '>, ' . "\n";
			 $usersMails  .= $res['email'] . ', ';
			 $res = mysql_fetch_assoc($result);
		  }
		  $usersWarns = substr($usersWarns, 0, -2);
		  $usersMails = substr($usersMails, 0, -2);
		  // отправляем емайлы
		  $to      = $usersMails;
		  $subject = 'Popcornnews.ru';
		  $message = '<p><b>Уважаемый пользователь!</b></p>' . 
						'<p>Вы не были на сайте <a href="http://popcornnews.ru">popcornnews.ru</a> 6 месяцев.</p>' .
						'<p>Если вы не активизируете Ваш профайл в течении трех дней, то Ваш аккаунт будет удален автоматически.</p>';
		  $headers = 'From: ' . SUPPORT_MAIL . "\r\n" .
						'Reply-To: ' . SUPPORT_MAIL . "\r\n" .
						'Content-type: text/html; charset=windows-1251' . "\r\n" .
						'X-Mailer: PHP/' . phpversion();
		  if (mail($to, $subject, $message, $headers)){
			 cat('Разосланы уведомления сл. пользователям: ' . "\n" . $usersWarns);
			 $sql = 'UPDATE popkorn_users SET send_email = 1 WHERE ldate>'.strtotime('-6 months').' AND ldate <'.strtotime('-6 months +3 day');
  // 		  $sql = 'UPDATE popkorn_users SET send_email = 1 WHERE ldate<'.strtotime('-6 months');
			 mysql_query($sql, $link);
		  }
		}else cat('Некому высылать!');
	 else cat(mysql_error());

	 break;
  default:
	 exit;
}