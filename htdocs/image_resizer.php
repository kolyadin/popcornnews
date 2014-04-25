<?

require_once 'downloader.php';

/**
 * формат запроса к этому файлу /k/act/size/upload/news_images/6/5/6/id/filename
 * @example http://popcornnews/k/news/500x700/upload/news_images/4/7/2/95274/ade947a8af77558bad42b778c1875a5c.jpg
 * 
 * 
 * If folder last char is "a" -than it using:
 *		Resizing to Fill a Given Space
 *		@link http://www.imagemagick.org/Usage/resize/
 * 
 * @author Azat Khuzhin
 */
if (preg_match('@^/k(?:1|)/(?P<act>[^/]+)/(?P<size>[^/]+)/(?P<path>.+)$@Uis', $_SERVER['REQUEST_URI'], $matches)) {
	setlocale(LC_ALL, 'ru_RU.CP1251');
	define('imagickPath', '/usr/local/bin/convert');
	define('imagickCompositePath', '/usr/local/bin/composite');
	define('lockfPath', '/usr/bin/lockf');
	
	$extensions = array('jpeg', 'gif', 'png');
	$sizes = array(
        '500x700', '130', '130x150', '540x490',
        '300x150', '80x100a', '150x150', '400',
        '274x274', '70', '90x90', '70x70',
        '100x100', '300x300', '110x110', '140x140',
        '180x180'
    );
	$actions = array('news', 'community', 'yourstyle', 'photo_articles');
	
	$matches['path'] = preg_replace('@(?:\.\.)@is', '', $matches['path']);

	// check size format
	preg_match('@^(?:(?P<x>\d+)|)(?:x(?P<y>\d+)|)(?P<fill>a|)$@Uis', $matches['size'], $size);
	$x = (isset($size['x']) && $size['x'] ? $size['x'] : null);
	$y = (isset($size['y']) && $size['y'] ? $size['y'] : null);
	$q = 90;
	
	$ext = strtolower(pathinfo($matches['path'], PATHINFO_EXTENSION));
	if ($ext == 'jpg') $ext = 'jpeg';

	$path = sprintf('%s/%s', $_SERVER['DOCUMENT_ROOT'], $matches['path']);
	$dst	= sprintf('%s/k/%s/%s/%s', $_SERVER['DOCUMENT_ROOT'], $matches['act'], $matches['size'], $matches['path']);

	if ($x > 1500 || $y > 1500 || $q < 0 || $q > 100 || !in_array($ext, $extensions) || (!$x && !$y) || !in_array($matches['size'], $sizes) || !in_array($matches['act'], $actions)) {
		header('HTTP/1.1 400 Bad Request');
		die;
	}
	
	if(!file_exists($path)) {
	    $url = $matches['path'];
	    download($url);
	}
	
	$lock_file = $path.'.lock';
	while(file_exists($lock_file)) {;}

	// create paths
	//$mkpath = sprintf('%s/k/%s/%s', $_SERVER['DOCUMENT_ROOT'], $matches['act'], $matches['size']);
	if(!is_dir(dirname($dst))) {
	    mkdir(dirname($dst), 0777, true);
	}
	/*$paths = explode('/', $matches['path']);
	array_pop($paths);
	foreach ($paths as $p) {
		$mkpath .= '/' . $p;
		
		if (!is_dir($mkpath)) {
			mkdir($mkpath, 0777);
			chmod($mkpath, 0777);
			var_dump($mkpath);
		}
	}*/
	
	$log_file = '/data/sites/v1.popcorn-news.ru/htdocs/.convert.log';
	$pid = getmypid();
	
	// resize
	$output = $return_var = null;
	// Resizing to Fill a Given Space
	if (!empty($size['fill'])) {
		// convert vk1.png -resize 80x100\> -size 80x100 xc:white +swap -gravity center -composite 1.png
		$command = sprintf('%s %s -quality %u -resize %sx%s\> -size %sx%s xc:white +swap -gravity center -composite %s', imagickPath, escapeshellarg($path), $q, $x, $y, $x, $y, escapeshellarg($dst));
	} else {
		$command = sprintf('%s %s -quality %u -resize %sx%s\> %s', imagickPath, escapeshellarg($path), $q, $x, $y, escapeshellarg($dst));
	}
	//error_log('['.date('H:i:s').'] PID: '.$pid.' Convert command: '.$command."\n\n", 3, $log_file);
	$lock_command = sprintf('%s -t 300 /tmp/convert_%s.lock %s', lockfPath, md5($command), $command);
	//error_log('['.date('H:i:s').'] PID: '.$pid.' Lock command: '.$lock_command."\n\n", 3, $log_file);
	exec($lock_command, $output, $return_var);
	//error_log('['.date('H:i:s').'] PID: '.$pid.' Output: '.print_r($output, true)."\n\n", 3, $log_file);
	//error_log('['.date('H:i:s').'] PID: '.$pid.' Result: '.print_r($return_var, true)."\n\n\n", 3, $log_file);
	//exec($command, $output, $return_var);
	// resize - success
	if ($return_var === 0) {
	    if(in_array($matches['act'], array('news', 'photo_articles')) && ($x > 200 && $y > 200)) {
	        $tmp_file = tempnam('/tmp', '');
	        $command = sprintf('%s -gravity SouthEast /data/sites/v1.popcorn-news.ru/htdocs/img/watermark.png %s %s', imagickCompositePath, escapeshellarg($dst), escapeshellarg($tmp_file));
	        //error_log('['.date('H:i:s').'] PID: '.$pid.' Watermark command: '.$command."\n\n", 3, $log_file);	         
	        $lock_command = sprintf('%s -t 300 /tmp/convert_%s.lock %s', lockfPath, md5($command), $command);
	        //error_log('['.date('H:i:s').'] PID: '.$pid.' Watermark lock command: '.$lock_command."\n\n", 3, $log_file);	         
	        exec($lock_command, $output, $ret);
	        //error_log('['.date('H:i:s').'] PID: '.$pid.' Watermark output: '.print_r($output, true)."\n\n", 3, $log_file);
	        //error_log('['.date('H:i:s').'] PID: '.$pid.' Watermark result: '.print_r($return_var, true)."\n\n\n", 3, $log_file);
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
