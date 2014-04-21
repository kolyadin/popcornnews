<?php
define('root', realpath(dirname(__FILE__) . '/../') . '/');
require_once root . 'inc/connect.php';

if ($_GET['path'] == 'delete' && $_GET['id'] > 0)
{
	$result = mysql_query(sprintf('SELECT screen1 FROM popcornnews_guess_star WHERE id = %u'
		,$_GET['id']
	));
	list($f1,$f2) = mysql_fetch_row($result);
	
	unlink(root . 'tmp_photos/guess_star/'.$f1);
	unlink(root . 'tmp_photos/guess_star/'.$f2);
	
	$result = mysql_query(sprintf('DELETE FROM popcornnews_guess_star WHERE id = %u'
		,$_GET['id']
	));
	
	header('Location:/manager/guess_star.php');
	die;
}

if ($_GET['path'] == 'add')
{
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['save'] == 1)
	{
		$filename1 = md5(microtime().$_FILES['screen1']['tmp_name']).'.jpg';
		
		exec(sprintf('/usr/local/bin/convert -resize 560x421 -quality 90 %s %s'
			,$_FILES['screen1']['tmp_name']
			,root . 'tmp_photos/guess_star/'.$filename1
		));
		
		$sql = <<<EOL
		INSERT INTO
			popcornnews_guess_star
		SET
			 difficulty     = %u
			,star_version1 = "%s"
			,star_version2 = "%s"
			,star_version3 = "%s"
			,star_version4 = "%s"
			,right_version  = %u
			,screen1        = "%s"
EOL;
		mysql_query(sprintf($sql
			,$_POST['difficulty']
			,mysql_real_escape_string(stripslashes($_POST['version1']))
			,mysql_real_escape_string(stripslashes($_POST['version2']))
			,mysql_real_escape_string(stripslashes($_POST['version3']))
			,mysql_real_escape_string(stripslashes($_POST['version4']))
			,$_POST['right_version']
			,$filename1
		));
		
		header('Location:/manager/guess_star.php');
		die;
	}
?>

<a href="/manager/guess_star.php?path=add">Добавить вопрос</a> | <a href="/manager/guess_star.php">Список вопросов</a>
<form method="post" enctype="multipart/form-data">
	<input type="hidden" name="save" value="1">
	
	<table>
		<tr>
			<td colspan="2">Уровень сложности</td>
			<td>
				<select name="difficulty">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><input type="radio" name="right_version" value="1" checked="true"></td>
			<td>Вариант №1</td>
			<td><input name="version1" style="width:300px;"></td>
		</tr>
		<tr>
			<td><input type="radio" name="right_version" value="2"></td>
			<td>Вариант №2</td>
			<td><input name="version2" style="width:300px;"></td>
		</tr>
		<tr>
			<td><input type="radio" name="right_version" value="3"></td>
			<td>Вариант №3</td>
			<td><input name="version3" style="width:300px;"></td>
		</tr>
		<tr>
			<td><input type="radio" name="right_version" value="4"></td>
			<td>Вариант №4</td>
			<td><input name="version4" style="width:300px;"></td>
		</tr>
		<tr>
			<td colspan="2">Кадр №1</td>
			<td><input type="file" name="screen1"></td>
		</tr>
	</table>
	<div><input type="submit" value="Сохранить"></div>
</form>

<?php
} 
else if ($_GET['path'] == 'edit')
{
	$result = mysql_query(sprintf('SELECT * FROM popcornnews_guess_star WHERE id = %u'
		,$_GET['id']
	));
	$row = mysql_fetch_assoc($result);
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['save'] == 1)
	{
		if (!empty($_FILES['screen1']['tmp_name']))
		{
			unlink(root . 'tmp_photos/guess_star/'.$row['screen1']);
			$filename1 = md5(microtime().$_FILES['screen1']['tmp_name']).'.jpg';
			exec(sprintf('/usr/local/bin/convert -resize 560x421 -quality 90 %s %s'
				,$_FILES['screen1']['tmp_name']
				,root . 'tmp_photos/guess_star/'.$filename1
			));
		}
		
		$sql = <<<EOL
		UPDATE
			popcornnews_guess_star
		SET
			 difficulty     = %u
			,star_version1 = "%s"
			,star_version2 = "%s"
			,star_version3 = "%s"
			,star_version4 = "%s"
			,right_version  = %u
			%s
		WHERE
			id = %u
EOL;
		mysql_query(sprintf($sql
			,$_POST['difficulty']
			,mysql_real_escape_string(stripslashes($_POST['version1']))
			,mysql_real_escape_string(stripslashes($_POST['version2']))
			,mysql_real_escape_string(stripslashes($_POST['version3']))
			,mysql_real_escape_string(stripslashes($_POST['version4']))
			,$_POST['right_version']
			,(!empty($_FILES['screen1']['tmp_name']))
				? sprintf(',screen1 = "%s"'
					,$filename1
				)
				: ''
			,$_POST['id']
		));
		
		header('Location:/manager/guess_star.php?path=edit&id='.$_POST['id']);
		die;
	}
?>
<a href="/manager/guess_star.php?path=add">Добавить вопрос</a> | <a href="/manager/guess_star.php">Список вопросов</a>
<form method="post" enctype="multipart/form-data">
	<input type="hidden" name="save" value="1">
	<input type="hidden" name="id" value="<?php print $_GET['id']; ?>">
	<table>
		<tr>
			<td colspan="2">Уровень сложности</td>
			<td>
				<select name="difficulty">
					<option value="1"<?php if ($row['difficulty']==1) print ' selected="true"'; ?>>1</option>
					<option value="2"<?php if ($row['difficulty']==2) print ' selected="true"'; ?>>2</option>
					<option value="3"<?php if ($row['difficulty']==3) print ' selected="true"'; ?>>3</option>
					<option value="4"<?php if ($row['difficulty']==4) print ' selected="true"'; ?>>4</option>
					<option value="5"<?php if ($row['difficulty']==5) print ' selected="true"'; ?>>5</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><input type="radio" name="right_version" value="1"<?php if ($row['right_version'] == 1) print ' checked="true"'; ?>></td>
			<td>Вариант №1</td>
			<td><input name="version1" value="<?php print htmlspecialchars($row['star_version1']); ?>" style="width:300px;"></td>
		</tr>
		<tr>
			<td><input type="radio" name="right_version" value="2"<?php if ($row['right_version'] == 2) print ' checked="true"'; ?>></td>
			<td>Вариант №2</td>
			<td><input name="version2" value="<?php print htmlspecialchars($row['star_version2']); ?>" style="width:300px;"></td>
		</tr>
		<tr>
			<td><input type="radio" name="right_version" value="3"<?php if ($row['right_version'] == 3) print ' checked="true"'; ?>></td>
			<td>Вариант №3</td>
			<td><input name="version3" value="<?php print htmlspecialchars($row['star_version3']); ?>" style="width:300px;"></td>
		</tr>
		<tr>
			<td><input type="radio" name="right_version" value="4"<?php if ($row['right_version'] == 4) print ' checked="true"'; ?>></td>
			<td>Вариант №4</td>
			<td><input name="version4" value="<?php print htmlspecialchars($row['star_version4']); ?>" style="width:300px;"></td>
		</tr>
		<tr>
			<td colspan="2">Кадр №1</td>
			<td><input type="file" name="screen1"><?php printf('<br><img src="/tmp_photos/guess_star/%s" width="150">',$row['screen1']); ?></td>
		</tr>
	</table>
	<div><input type="submit" value="Сохранить"></div>
</form>

<?php
}
else
{
	$result = mysql_query('SELECT * FROM popcornnews_guess_star ORDER BY id DESC');
	
	if ($result) $k = mysql_num_rows($result);
	else $k = null;

	if ($k > 0)
	{
?>

<a href="/manager/guess_star.php?path=add">Добавить вопрос</a> | <a href="/manager/guess_star.php">Список вопросов</a>

<table width="100%" border="1">
	<tr>
		<th></th>
		<th>Уровень сложности</th>
		<th width="30%">Вариант фильмов</th>
		<th width="30%">Правильный вариант</th>
		<th></th>
	</tr>
	<?php
	$tmpl = <<<EOL
	<tr>
		<td><a href="/manager/guess_star.php?path=edit&id=%u" style="font-weight:bold;">%u</a>/%u</td>
		<td>%u</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href="/manager/guess_star.php?path=edit&id=%u" style="font-weight:bold;">редактировать</a><br><a href="/manager/guess_star.php?path=delete&id=%u" onclick="if (confirm('Точно удалить?')){return true;}else{return false;}" style="font-weight:bold;">удалить</a></td>
	</tr>
EOL;
	
	while ($row = mysql_fetch_assoc($result))
	{
		printf($tmpl
			,$row['id']
			,$row['id']
			,$k--
			,$row['difficulty']
			,$row['star_version1']."<br>".$row['star_version2']."<br>".$row['star_version3']."<br>".$row['star_version4']."<br>"
			,$row['star_version'.$row['right_version'].'']
			,$row['id']
			,$row['id']
		);
	}
	?>
</table>

<?php
	} else {
		print '<a href="/manager/guess_star.php?path=add">Добавить вопрос</a> | <a href="/manager/guess_star.php">Список вопросов</a>';
		print '<div><strong>Пусто</strong></div>';
	}
?>

<?php } ?>
