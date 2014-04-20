<html>
	<head>
		<title>Система управления сайтом "TRAFFIC"</title>
		<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="no-cache">
		<link rel="stylesheet" type="text/css" href="/manager/styles/global.css">
		<link rel="stylesheet" type="text/css" href="/manager/styles/additional.css">
	</head>

	<body>
		<form method="POST" action="/manager/user_act.php">
			<a name="topper"></a>
			<table cellspacing="1" class="TableFiles">
				<?$topic = array_shift($p['query']->get('fanfics_admin', array('id'=>$d['fid']), null, 0, 1));?>
				<tr><td colspan="6"><a href="admin.php?type=fanfics">К списку</a></td></tr>
				<tr>
					<td colspan="6">
						<p id="f1" style="cursor: pointer;" onclick="var el = document.getElementById('f2'); el.style.display = (el.style.display == 'block' ? 'none' : 'block'); el.focus();">Читать фанфик</p>
						<p id="f2" style="display: none;"><?=$topic['content'];?></p>
					</td>
				</tr>
				<tr>
					<td class="TFHeader">ID</td>
					<td class="TFHeader">Комментарий</td>
					<td class="TFHeader">Автор</td>
					<td class="TFHeader">Дата</td>
					<td class="TFHeader">Удалить</td></tr>
				<?
				foreach ($p['query']->get('fanfics_comments', array('fid'=>$d['fid']), array('cdate desc'), null, null) as $i => $fact) {
					$user = array_shift($p['query']->get('users', array('id'=>$fact['uid']), null, 0, 1));
					?>
				<tr>
					<td><?=$fact['id']?></td>
					<td><?=$fact['content']?></td>
					<td><?=htmlspecialchars($user['nick']);?></td>
					<td><?=date("d.m.Y H:i", $fact['cdate'])?></td>
					<td align="center"><a href="admin.php?type=fanfic_comments&action=del&id=<?=$fact['id']?>&fid=<?=$d['fid']?>" onclick="return window.confirm('Вы точно хотите удалить этот комментарий ?');">&raquo;</a></td>
				</tr>
					<?
				}
				?>
				<tr><td class="TFHeader" colspan="7" align="right"><input type="submit" value="Сохранить" style="font-size:12px;"></td></tr>
			</table>
		</form>
	</body>
</html>