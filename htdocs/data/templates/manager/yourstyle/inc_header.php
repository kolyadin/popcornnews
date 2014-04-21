<html>
	<head>
		<title>Система управления сайтом "TRAFFIC"</title>
		<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
		<link rel="stylesheet" type="text/css" href="/manager/styles/global.css">
		<link rel="stylesheet" type="text/css" href="/manager/styles/additional.css">
		<link rel="stylesheet" href="/manager/redactor/css/redactor.css" type="text/css" />
		
		<script type="text/javascript" src="/js/jquery/jquery.js"></script> 
		<script type="text/javascript" src="/js/ys.manager.js"></script> 
		
		<style type="text/css">
			div.ys_select {width:342px; margin: 2px 0 13px;}
			div.ys_select div.container {padding:7px 15px 15px; font-size:13px; color:#f70080;}
			div.ys_select ul {margin:0;padding:0;max-height:90px; overflow-y:auto; }
			div.ys_select li {margin:0px; padding:2px 0; border-bottom:1px solid #e5e5e5; cursor:pointer;list-style:none;}
			div.ys_select li:hover {background:#e5e5e5;}
			div.ys_select li.no_hover {cursor:default;}
			div.ys_select li.no_hover:hover {background:none;}
			div.ys_select li span {color:#7f7f7f;}
			div.ys_select {position:absolute; left:-9999px; top:-9999px; z-index:9999; overflow:hidden; margin:-1px 0px 0px -1px; padding:0px 5px 5px 0px;}
			div.ys_select .container {position:relative; padding:7px 23px 25px 15px;  border:1px solid #e5e5e5; background:#fff;}
			div.ys_select div.fon {position:absolute; left:6px; top:6px; width:100%; height:100%; background:#cecece;}
			/* Положение при появлении для относительных попапов */
			div.ys_selectrel {position:relative; left:0px; top:0px;}
		</style>
	</head>
	<body>
		<h1><?=(!empty($d['error']) ? $d['error'] : null)?></h1>
		<div id="q" style="float: left;">
			<form>
				<input type="hidden" name="type" value="yourstyle" />
				<input type="hidden" name="action" value="search" />
				
				<label><input type="radio" name="searchType" value="Groups"<?=((!empty($d['searchType']) && $d['searchType'] == 'Groups') || empty($d['searchType']) ? ' checked="checked"' : null)?> />Группы</label>
				<label><input type="radio" name="searchType" value="Users"<?=(!empty($d['searchType']) && $d['searchType'] == 'Users' ? ' checked="checked"' : null)?> />Пользователи</label>
				<label><input type="radio" name="searchType" value="GroupsTiles"<?=(!empty($d['searchType']) && $d['searchType'] == 'GroupsTiles' ? ' checked="checked"' : null)?> />Вещи</label>
				
				<input type="text" name="q" value="<?=(!empty($d['q']) ? $d['q'] : null)?>" />
				<input type="submit" value="Поиск" />
			</form>
		</div>
		<div class="controls" style="float: right;">
			<a href="?type=yourstyle">Группы</a>
			<a href="?type=yourstyle&action=tilesBrands">Бренды</a>
			
			<a href="?type=yourstyle&action=addRootGroup">Добавить группу</a>
			<a href="?type=yourstyle&action=addGroup">Добавить подгруппу</a>
			<a href="?type=yourstyle&action=addTileBrand">Добавить бренд</a>
			<a href="?type=yourstyle&action=uploadTile">Загрузить вещь</a>
		</div>
		<div class="clear" style="clear: both;"><h4><?=(!empty($d['title']) ? $d['title'] : '&nbsp;')?></h4></div>
