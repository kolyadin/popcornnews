<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title><?=$_SERVER['HTTP_HOST']?></title>
</head>

<body style="background: #fff; padding: 0; margin: 0;">
<div style="border-top: 5px solid #f70080; border-bottom: 5px solid #999; height: 0; overflow: hidden;">
</div>
<div style="padding: 20px">
	<h1 style="font: bold 26px Tahoma, Verdana, Arial; margin-top: 0; margin-bottom: 0;"><span style="color: #f70080;">ПОП</span><span style="color: #000">КОРН</span><span style="color: #999">NEWS</span></h1>
	<h2 style="font: 24px 'Trebuchet MS', Tahoma, Verdana, Arial; margin-top: 15px; margin-bottom: 10px;">Регистрация</h2>
	<div style="background: #e5e5e5; padding: 12px 15px;">
		<span style="font: bold 13px Tahoma, Verdana, Arial; display: block;">Вы были зарегистрированы на сайте www.popcornnews.ru.</span>
		<span style="font: 13px Tahoma, Verdana, Arial; display: block; margin-bottom: 12px;">Для продолжения регистрации вам необходимо активизировать свой аккаунт, для это перейдите по этой ссылке: </span>
		<a style="font: 13px Tahoma, Verdana, Arial; color: #f70080;" href="http://<?=$_SERVER['HTTP_HOST']?>/commit/<?=$d['user_id']?>/<?=$d['code']?>">http://<?=$_SERVER['HTTP_HOST']?>/commit/<?=$d['user_id']?>/<?=$d['code']?></a>
		<span style="font: 13px Tahoma, Verdana, Arial; display: block; margin-top: 15px;">С уважением, редакция Popcornnews <a style="color: #f70080" href="http://www.popcornnews.ru/">http://www.popcornnews.ru/</a></span>
	</div>
</div>

</body>
</html>
