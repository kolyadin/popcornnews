<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<?
		$wall = array_shift($p['query']->get('person_wallpapers', array('id' => $d['wall_id'], 'site' => $d['site']), null, null, null));
		?>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<title>Обои <?= $wall['tags'] ?>, скачать обои на рабочий стол <?= $wall['tags'] ?> - попкорнnews</title>
		<style>
			body {margin:0px;padding:0px;}
			img {border:0px;}
		</style>
	</head>
	<body bgcolor="#FFFFFF">
		<img src="<?=$this->getStaticPath(sprintf('/%s/%s', $d['site'] == 'pop' ? 'upload' : 'kinoupload', ($d['size'] == 1024 ? $wall['img1024'] : ($d['size'] == 1280 ? $wall['img1280'] : $wall['img1600']))))?>" alt="<?=$wall['tags']?>"
		/>
	</body>
</html>