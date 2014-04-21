<?

/**
 * формат запроса к этому файлу /upload/_ширина_высота_качество_имя
 */
if (preg_match('@^/upload(?:1|)/_(?P<x>\d+)_(?P<y>\d+)_(?P<q>\d+)_(?P<path>.+)$@Uis', $_SERVER['REQUEST_URI'], $matches)) {
	setlocale(LC_ALL, 'ru_RU.CP1251');
	define('imagickPath', '/usr/local/bin/convert');
	define('imagickCompositePath', '/usr/local/bin/composite');
	define('lockfPath', '/usr/bin/lockf');

	$extensions = array('jpeg', 'gif', 'png');

	$matches['path'] = preg_replace('@(?:\./|/)@is', '', $matches['path']);

	$x = (int)$matches['x'];
	$y = (int)$matches['y'];
	$q = (int)$matches['q'];
	
	$ext = strtolower(pathinfo($matches['path'], PATHINFO_EXTENSION));
	if ($ext == 'jpg') $ext = 'jpeg';

	$path = sprintf('%s/upload/%s', $_SERVER['DOCUMENT_ROOT'], $matches['path']);
	$dst	= sprintf('%s/upload/_%u_%u_%u_%s', $_SERVER['DOCUMENT_ROOT'], $x, $y, $q, $matches['path']);

	// error
	if ($x > 1500 || $y > 1500 || $q < 0 || $q > 100 || !in_array($ext, $extensions) || !file_exists($path)) {
		header('HTTP/1.1 400 Bad Request');
		die;
	}

	// resize
	$output = $return_var = null;
	$command = sprintf('%s -quality %u -colorspace rgb -resize %ux\> %s %s', imagickPath, $q, $x, escapeshellarg($path), escapeshellarg($dst));
	exec(sprintf('%s -t 0 /tmp/convert_%s.lock %s', lockfPath, md5($command), $command), $output, $return_var);
	//exec($command, $output, $return_var);	
	// resize - success
	if ($return_var === 0) {
	    if($x > 200 && $y > 200) {
	        $tmp_file = tempnam('/tmp', '');
	        $command = sprintf('%s -gravity SouthEast /data/sites/popcornnews.ru/htdocs/img/watermark.png %s %s', imagickCompositePath, escapeshellarg($dst), escapeshellarg($tmp_file));
	        exec($command, $output, $ret);	 
	        if($ret === 0) {
	            unlink($dst);
	            copy($tmp_file, $dst);
	            unlink($tmp_file);
	        }
	    }
		// write image to STDOUT
		header('Content-type: image/' . $ext);
		echo file_get_contents($dst);
		die;
	}
}
// error
header('HTTP/1.1 503 Service Unavailable');
header('HTTP/1.1 Retry-After 5');
