<?

require_once dirname(__FILE__) . '/../../inc/connect.php';

ini_set('display_errors', 'On');
error_reporting(E_ERROR);

if (isset($_SERVER['IS_AZAT_SERVER']) && $_SERVER['IS_AZAT_SERVER']) {
    define('JPEGOPTIM_BIN', '/usr/bin/jpegoptim');
} else {
    define('JPEGOPTIM_BIN', '/usr/local/bin/jpegoptim');
}

// проверка есть ли хоть один пользователь-админ в базе, и создание если нет
// дефот логин/пароль admin/admin

if ($project_fullname != "") {
    $line = mysql_query("SELECT * FROM $tbl_goods_users WHERE status=3", $link);
    if (!($s = mysql_fetch_array($line))) {
        $line = mysql_query("INSERT INTO $tbl_goods_users (login,pass,status,name) VALUES ('$admin_login','$admin_pass',3,'Администратор')", $link);
        generate_users();
    } elseif (isset($action) && $action == "gen") {
        generate_users();
    }
    unset($s);
}

//проверка и создании запици "корзина"
$cmd = "SELECT * FROM $tbl_pages WHERE id=1";
$line = mysql_query($cmd, $link);
//print $cmd.mysql_error();
if (!($s = mysql_fetch_array($line))) {

    $line = mysql_query("INSERT INTO $tbl_pages (id,name,descr,dat) VALUES (1,'Корзина','Корзина, папка по умолчанию'," . date("Ymd") . ")", $link);
}

//проверка и создании запици "каталог"
$line = mysql_query("SELECT * FROM $tbl_pages WHERE id=2", $link);
if (!($s = mysql_fetch_array($line))) {
    $line = mysql_query("INSERT INTO $tbl_pages (id,name,descr,dat) VALUES (2,'Каталог','Каталог, папка по умолчанию'," . date("Ymd") . ")", $link);
}

function addlog($text, $comment, $mysql_error) {
    // ф-я пишет лог-действие пользователя
    global $link;
    global $user_id;
    global $user_name;
    global $pageid;
    global $tbl_logs;
    global $ip;
    global $_SERVER;
    global $id;
    global $tek;
    global $fold_id;
    global $good_id;
    global $page_id;
    global $goods_id;
    global $order;

    $ref = getenv("HTTP_REFERER");
    $url = $_SERVER["CHARSET_HTTP_METHOD"] . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];


    $cmd = "INSERT INTO $tbl_logs (page_id,user_name,user_id,ip,referrer,url,pageid,id_,tek,fold_id,comment,orderby,commands,good_id,goods_id,mysql_error)
				 VALUES ($page_id,'" . get_cur_admin_name() . "'," . intval($user_id) . ",'$ip','$ref','$url'," . intval($pageid) . "," . intval($id) . "," . intval($tek) . "," . intval($fold_id) . ",'" . addslashes($comment) . "','$order','" . addslashes($text) . "'," . intval($good_id) . "," . intval($goods_id) . ",'$mysql_error')";
    $line = mysql_query($cmd, $link);

    //if(mysql_error()!="") print $cmd.mysql_error();
}

function getmicrotime() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

function uc2html($str) {
    $ret = '';
    for ($i = 0; $i < strlen($str) / 2; $i++) {
        $charcode = ord($str[$i * 2]) + 256 * ord($str[$i * 2 + 1]);
        $ret .= '&#' . $charcode;
    }
    return $ret;
}

function fatal($msg = '') {
    echo '[Fatal error]';
    if (strlen($msg) > 0)
        echo ": $msg";
    echo "<br>\nScript terminated<br>\n";
    if ($f_opened)
        @fclose($fh);
    addlog("", "импорт экселя хтмл - ошибка: $msg");
    exit(1);
}

function insarr() {

    global $link;
    global $arr;
    global $namest;
    global $fromst;
    global $tbl_goods_;
    global $fold_id;
    global $page_id;

    for ($i = 0; $i < count($namest); $i++) {

        $cmd1.=",";

        $cmd1.=recode_string("html..cp1251", $namest[$i]);

        $cmd2.=",";
        $cmd2.="'" . recode_string("html..cp1251", $arr[$fromst[$i] - 1]) . "'";
    }

    $cmd = "INSERT INTO $tbl_goods_ (page_id,goods_id $cmd1) VALUES ($page_id,$fold_id $cmd2)";
    $line = mysql_query($cmd, $link);
}

function generate_users() {
// ф-я на сервере генерит .htpasswd c пользователями из базы

    global $link;
    global $tbl_goods_users;
    global $_SERVER;

    $ppp = eregi_replace("/goods.php", "", (isset($_SERVER["PATH_TRANSLATED"]) ? $_SERVER["PATH_TRANSLATED"] : ''));

    $cmd = "SELECT login,pass FROM $tbl_goods_users";
    $line = mysql_query($cmd, $link);
    $txt = '';
    while ($s = mysql_fetch_array($line)) {
        $txt.=$s[0] . ":" . crypt($s[1]) . "\n";
    }

    //print $txt;
    //exit(1);
    if ($fp = fopen(".htpasswd", 'w')) {
        fputs($fp, $txt);
        fclose($fp);
    } else {
        print "Нет доступа к файлу \".htpasswd\" на запись!";
        exit(1);
    }

    //разберемся с штакцессом
    if (file_exists(".htaccess")) { // .htaccess уже есть
        $fp = fopen(".htaccess", "r");
        $txt = fread($fp, filesize(".htaccess"));
        fclose($fp);
        if (strpos($txt, "AuthAuthoritative") < 1
        )$txt.="\nAuthAuthoritative on";
        if (strpos($txt, "AuthType") < 1
        )$txt.="\nAuthType Basic";
        if (strpos($txt, "AuthName") < 1
        )$txt.="\nAuthName ManagerStuff";
        if (strpos($txt, "AuthUserFile") < 1
        )$txt.="\nAuthUserFile $ppp/.htpasswd";
        if (strpos($txt, "Require") < 1
        )$txt.="\nRequire valid-user";
    } else { //.htaccess-а еще нет
        $txt = "
AuthAuthoritative on
AuthType Basic
AuthName ManagerStuff
AuthUserFile $ppp/.htpasswd
Require valid-user
";
        if ($fp = fopen(".htaccess", 'w')) {
            fputs($fp, $txt);
            fclose($fp);
        } else {

            print "Нет доступа к файлу \".htaccess\" на запись!";
            exit(1);
        }
    }
}

/**
// ф-я сохраняет файл и возвращает его id
// $u_n - физ имя файла (userfile_name)
// $u_s - размер файла (userfile_size)
// $u - сам файл (userfile)
// $type - тип (картинка/ворд/эксель/....)
// $seq - последовательность
// $dat - дата
// $descr - описание
// $name - имя картинки в системе управления 
// $g,$g_,$p,$p_ - goods_id,goods_id,pages_id,pages_id_
 */
function save_file($u_n, $u_s, $u, $type, $seq, $dat, $descr, $name, $g, $g_, $p, $p_) {
    global $pictures;
    global $link;
    global $tbl_pix;
    global $tbl_goods_;
    global $server_os;
    global $uploadpath;

    if ($u_s > 0) {
        $fp = fopen($u, 'r');
        $buf = fread($fp, $u_s);
        fclose($fp);
        $buf = addslashes($buf);

        if (file_exists($u)) unlink($u);

        switch ($pictures) {
            case("base"): // храним файлы в базе
                $cmdq = "INSERT INTO $tbl_pix (pix,name,type,seq,dat,descr,fizname,goods_id,goods_id_,pages_id,pages_id_) VALUES ('$buf','$name'," . intval($type) . "," . intval($seq) . "," . intval($dat) . ",'$descr','$u_n'," . intval($g) . "," . intval($g_) . "," . intval($p) . "," . intval($p_) . ")";
                $line = mysql_query($cmdq, $link);
                $line = mysql_query("SELECT max(id) FROM $tbl_pix WHERE pix='$buf'", $link);
                $st = mysql_fetch_array($line);
                $file = $st[0];
                break;
            case("file"): // храним файлы на диске
                if ($server_os == 0) { // юникс
                    $tmp = tempnam($uploadpath, '');
                    $u_n_info = pathinfo($u_n);
                    $ext = strtolower($u_n_info['extension']);

                    if (in_array($ext, array('asp', 'php', 'html', 'phtml', 'shtml', 'php3', 'php4', 'pl', 'php5', 'cgi', 'py'))) {
                        $ext = 'xxx';
                    }

                    $fp = fopen($tmp . "." . $ext, 'w');
                    fputs($fp, stripslashes($buf));
                    fclose($fp);
                    // not all files with extension gif are realy has gif image format
                    if (in_array($ext, array('jpg', 'jpeg', 'png', 'gif'))) {
                        exec(sprintf('%s --strip-all "%s.%s"', JPEGOPTIM_BIN, str_replace('"', '\"', $tmp), $ext), $output, $returnVar);
                    }
                    if (file_exists($tmp)) unlink($tmp);

                    $f = sprintf('%s.%s', $tmp, $ext);
                    $fil = eregi_replace($uploadpath, '', $f);
                } else { // вин
                    $m = explode(".", $u_n);
                    $un = eregi_replace(" ", "", (stripslashes($m[0]))) . "." . $ext;
                    while (file_exists("$uploadpath/$un")) {//придумываем случайные имена пока не будет уникальное
                        $un = rand(0, 10000000) . "." . $ext;
                    }
                    $fp = fopen("$uploadpath/$un", 'w');
                    fputs($fp, stripslashes($buf));
                    fclose($fp);
                    $fil = $un;
                }
                $file = $fil;

                if ($g != 0 || $g_ != 0) { // это аттач - нада записать в базу
                    $cmdq = "INSERT INTO $tbl_pix (diskname,name,type,seq,dat,descr,fizname,goods_id,goods_id_,pages_id,pages_id_) VALUES ('$file','$name'," . intval($type) . "," . intval($seq) . "," . intval($dat) . ",'$descr','$u_n'," . intval($g) . "," . intval($g_) . "," . intval($p) . "," . intval($p_) . ")";
                    $line = mysql_query($cmdq, $link);
                }
                break;
        }
        return $file;
    }
    return false;
}

?>