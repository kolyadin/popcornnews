<html>
	<head>
		<title>Система управления сайтом "TRAFFIC"</title>
		<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
		<meta Name="author" Content="Shilov Konstantin, sky@traffic.spb.ru">
		<meta NAME="description" CONTENT="">
		<meta NAME="keywords" CONTENT=''>
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="no-cache">
		<meta http-equiv="Cache-Control" content="no-cache">

		<link rel="stylesheet" type="text/css" href="/manager/styles/global.css">
		<link rel="stylesheet" type="text/css" href="/manager/styles/additional.css">
	</head>

	<body>
		<a name="topper"></a>
		<table width="100%">
			<tr><td><?$this->_render('inc_stat_cities');?></td></tr>
			<tr><td valign="top"><?$this->_render('inc_stat_ages');?></td></tr>
			<tr><td valign="top"><?$this->_render('inc_stat_sex');?></td></tr>
		</table>
	</body>
</html>