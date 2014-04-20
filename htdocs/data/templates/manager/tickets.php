<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"  "http://www.w3.org/TR/html4/strict.dtd">
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
		<script type="text/javascript">
			function sel_all()
			{
				var otb=document.getElementById('tb');
				for (var i=1;i<otb.rows.length;i++)
				{
					ls=otb.rows[i].cells.length;
					ol=otb.rows[i].cells[ls-1];
					inps=ol.getElementsByTagName('input');
					if (inps.length)
					{
						if (!inps[0].checked)
						{
							inps[0].checked=true;
						}
						else
						{
							inps[0].checked=false;
						}
					}
				}
			}

			function test_frm(frm)
			{
				if (!frm.elements['msg'].value)
				{
					alert ('Пустое сообщение !');
					return false;
				}
				var sndrs=0;
				for (var i=0;i<frm.elements.length;i++)
				{
					if (frm.elements[i].type=='checkbox' && frm.elements[i].checked)
					{
						sndrs+=1;
					}
				}
				if (sndrs==0)
				{
					alert ('Не выбрано ни одного получателя !');
				}
				return window.confirm('Вы точно хотите отправить сообщение '+sndrs+' получателям ?');
			}
		</script>
	</head>

	<body>
		<a name="topper"></a>
		<form method="POST" action="/manager/admin.php" onsubmit="return test_frm(this);" enctype="multipart/form-data">
			<input type="hidden" name="type" value="tickets">
			<input type="hidden" name="action" value="send">
			<table cellspacing="1" class="TableFiles" id="tb">
				<tr>
					<td class="TFHeader" colspan="3">Сообщение</td>
					<td class="TFHeader"><input type="submit" value="Отправить" style="font-size:12px;"></td>
				</tr>
				<tr>
					<td colspan="4">
						<textarea name="msg" style="width:100%;" rows="10"></textarea>
					</td>
				</tr>
				<tr>
					<td class="TFHeader">ID</td>
					<td class="TFHeader">Ник</td>
					<td class="TFHeader">Email</td>
					<td class="TFHeader">Разослать <input type="checkbox" value="1" onclick="sel_all()"> всем</td>
				</tr>
				<?
				foreach ($p['query']->get('users', null, array('nick'), null, null) as $i => $country) {?>
				<tr>
					<td><?=$country['id']?></td>
					<td width="70%"><?=htmlspecialchars($country['nick']);?></td>
					<td width="70%"><?=$country['email']?></td>
					<td><input type="checkbox" name="im[<?=$country['id']?>]" value="2"></td>
				</tr>
					<?
				}
				?>
				<tr><td class="TFHeader" colspan="7" align="right"><input type="submit" value="Отправить" style="font-size:12px;"></td></tr>
			</table>
		</form>
	</body>
</html>