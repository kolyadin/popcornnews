<?

    //настрокий текущего проэкта

    // отключим вывод сообщений об ошибках
    //error_reporting(0);// отключим сообщения об ошибках
    error_reporting(E_ERROR | E_WARNING | E_PARSE); //вывод сообщений об ошибках включен
    setlocale(LC_ALL, "ru_RU.CP1251");

    $project="popconnews"; // название проекта (для сощдания баз - 1 слово)
    $admin_login="admin"; // логин админа по дефолту
    $admin_pass="pass"; // пароль админа по дефолту
    $project_fullname="Popcornnews"; // название проекта - для титла
    $firstpage="goods.php";//первая страница после проверки в манагере

    //физический путь, куда будет картинки загружаться
    $uploadpath = realpath($DOCUMENT_ROOT . '/upload/') . '/';
    //путь в адресной строке к папке с загружеными файлами
    $filepath="/upload/";

    $pictures="file"; // картинки загружаем на диск
    //$pictures="base"; // картинки загружаем в sql

    $ver="v6.00b"; // версия админки

    $maxpoles=45; // 0-50 - количество доп полей
    $maxdescrs=0; // 0-5 - количество "свойств" для папки
    $maxmores=0;  // 0-10 - количество дополнительных "свойств" (checkbox) для папки


    $text="";

/*=====================================================================*/
    //настройки подключения к базе данных mysql
	 $hostname =	':/tmp/mysql-kino.sock';   //адрес сервера
	 $login =		'sky';			   //логин
	 $pass =		'uGrs7u8rN';		   //пароль
	 $dbname =		'popcornnews';		   //имя базы данных
/*=====================================================================*/


    srand((double)microtime()*1000000);

    $good_id=intval($good_id);
    $goods_id=intval($goods_id);
    $page_id=intval($page_id);
    $pages_id=intval($pages_id);
    $fold_id=intval($fold_id);
    $pageid=intval($pageid);
    $subpageid=intval($subpageid);
    $id=intval($id);
    $ip=getenv("REMOTE_ADDR");
    srand((double)microtime()*1000000);
    $tek=intval($tek);

    if(strpos($_SERVER["SERVER_SOFTWARE"],"Win")){
        $server_os=1; // сервер вин
    } else $server_os=0;//сервер юникс

    $link = mysql_connect ($hostname, $login, $pass); // подключимся к базе

    if(!$link) { // подключиться не удалось
        //print "Не удалось подключиться к базе данных!<br>Возможно сервер перегружен или заданы неверные настройки подключения.";
        //print "...";
        echo 'Уважаемые			     посетители!<br>На сервере проводятся профилактические работы, пожалуйста, попробуйте зайти через пять минут.';
        exit(1);
    };

    mysql_select_db("$dbname",$link);

        // таблица с комментариями
        define ('TBL_COMMENTS', $project.'_comments');


///////////////////
// проверка наличия таблицц и создание если нету.


//        $tbl_files=$project."_files";// файлы - для папок и файлов
/*        $tbl=$tbl_files;
        $line = mysql_query("show tables",$link);$flag=true;while($string=mysql_fetch_array($line)){if($string[0]==$tbl) $flag=false;};
if($flag==true){$line = mysql_query("
CREATE TABLE $tbl_files (
  `id` int(11) NOT NULL auto_increment,
  `name` text NOT NULL,
  `goods_id` int(10) NOT NULL default '0',
  `goods_id_` int(10) NOT NULL default '0',
  `pix_id` text NOT NULL default '',
  `regtime` timestamp(14) NOT NULL,
  `dat` int(8) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)
",$link);};
*/


        $tbl_logs=$project."_log";// логи активности пользователя
        $tbl=$tbl_logs;
/*        $line = mysql_query("show tables",$link);$flag=true;while($string=mysql_fetch_array($line)){if($string[0]==$tbl) $flag=false;};
if($flag==true){$line = mysql_query("
CREATE TABLE $tbl_logs (
  `id` int(11) NOT NULL auto_increment,
  `user_name` text NOT NULL,
  `user_id` int(11) NOT NULL default '0',
  `ip` text NOT NULL,
  `referrer` text NOT NULL,
  `url` text NOT NULL,
  `commands` text NOT NULL,
  `comment` text NOT NULL,
  `orderby` text NOT NULL,
  `id_` int(11) NOT NULL default '0',
  `fold_id` int(11) NOT NULL default '0',
  `goods_id` int(11) NOT NULL default '0',
  `good_id` int(11) NOT NULL default '0',
  `page_id` int(11) NOT NULL default '0',
  `pageid` int(11) NOT NULL default '0',
  `tek` int(11) NOT NULL default '0',
  `admin` int(11) NOT NULL default '0',
  `regtime` timestamp(14) NOT NULL,
  `mysql_error` text not null default '',
  PRIMARY KEY  (`id`)
)
",$link); print mysql_error();};*/

        $tbl_pix=$project."_pix";// картинки для структуры
        $tbl=$tbl_pix;
/*        $line = mysql_query("show tables",$link);$flag=true;while($string=mysql_fetch_array($line)){if($string[0]==$tbl) $flag=false;};
if($flag==true){$line = mysql_query("
CREATE TABLE $tbl_pix (
  `id` int(11) NOT NULL auto_increment,
  `pix` longblob NOT NULL DEFAULT '',
  `descr` text NOT NULL DEFAULT '',
  `fizname` text NOT NULL DEFAULT '',
  `name` text NOT NULL DEFAULT '',
  `seq` int(10) NOT NULL default '0',
  `type` int(2) NOT NULL default '0',
  `regtime` timestamp(14) NOT NULL,
  `dat` int(8) NOT NULL default '0',
  `diskname` text NOT NULL DEFAULT '',
  `goods_id` int(10) NOT NULL default '0',
  `goods_id_` int(10) NOT NULL default '0',
  `pages_id` int(10) NOT NULL default '0',
  `pages_id_` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)
",$link);};*/


$tbl_backup="backup_".$project."_pix";// картинки для бакапа
        $tbl=$tbl_buckup;
/*        $line = mysql_query("show tables",$link);$flag=true;while($string=mysql_fetch_array($line)){if($string[0]==$tbl) $flag=false;};
if($flag==true){$line = mysql_query("
CREATE TABLE $tbl_backup (
  `id` int(11) NOT NULL auto_increment,
  `pix` longblob NOT NULL DEFAULT '',
  `diskname` text NOT NULL DEFAULT '',

  `dat` int(8) NOT NULL default '0',
  `backupname` text NOT NULL DEFAULT '',
  `regtime` timestamp(14) NOT NULL,
  PRIMARY KEY  (`id`)
)
",$link);};*/


        $tbl_pages=$project."_pages";//  таблицца томов
/*        $line = mysql_query("show tables",$link);$flag=true;
        while($string=mysql_fetch_array($line)){if($string[0]==$tbl_pages) $flag=false;};
        if($flag==true){
$line = mysql_query("
CREATE TABLE $tbl_pages (

  `id` int(11) NOT NULL auto_increment,
  `name` text NOT NULL default '',
  `descr` text NOT NULL default '',
  `dat` int(8) NOT NULL default '0',
  `icon` text NOT NULL default '',
  `desctop` int(1) NOT NULL default '0',
  `regtime` timestamp(14) NOT NULL,
  PRIMARY KEY  (`id`)
)
",$link);
        };  */


        $tbl_goods=$project."_goods";//  таблицца групп товаров
/*        $line = mysql_query("show tables",$link);$flag=true;
        while($string=mysql_fetch_array($line)){if($string[0]==$tbl_goods) $flag=false;};
        if($flag==true){
$line = mysql_query("

CREATE TABLE `$tbl_goods` (
 `id` int(11) NOT NULL auto_increment,
 `name` text NOT NULL,
 `goods_id` int(8) NOT NULL default '0',
 `goods_id_` int(8) NOT NULL default '0',
 `id_` int(8) NOT NULL default '0',
 `orderby` text NOT NULL,
 `regtime` timestamp(14) NOT NULL,
 `dat` int(8) NOT NULL default '0',
 `readonly` int(1) NOT NULL default '0',
 `descr1` text NOT NULL,
 `descr2` text NOT NULL,
 `descr3` text NOT NULL,
 `descr4` text NOT NULL,
 `descr5` text NOT NULL,
 `opt1` text NOT NULL,
 `opt2` text NOT NULL,
 `opt3` text NOT NULL,
 `opt4` text NOT NULL,
 `opt5` text NOT NULL,
 `page_id` int(8) NOT NULL default '0',
 `page_id_` int(8) NOT NULL default '0',
 `temp_id` int(8) NOT NULL default '0',
 `mpage` int(8) NOT NULL default '0',
 `adds` int(2) NOT NULL default '0',
 `addf` int(2) NOT NULL default '0',
 `cansr` int(2) NOT NULL default '0',
 `attdir` text NOT NULL,
 `attfil` text NOT NULL,
 `attxls` text NOT NULL,
 `icon` text NOT NULL,
 `desctop` int(1) NOT NULL default '0',
 `pole1` text NOT NULL,
 `pole1_` int(1) NOT NULL default '0',
 `pole2` text NOT NULL,
 `pole2_` int(1) NOT NULL default '0',
 `pole3` text NOT NULL,
 `pole3_` int(1) NOT NULL default '0',
 `pole4` text NOT NULL,
 `pole4_` int(1) NOT NULL default '0',
 `pole5` text NOT NULL,
 `pole5_` int(1) NOT NULL default '0',
 `pole6` text NOT NULL,
 `pole6_` int(1) NOT NULL default '0',
 `pole7` text NOT NULL,
 `pole7_` int(1) NOT NULL default '0',
 `pole8` text NOT NULL,
 `pole8_` int(1) NOT NULL default '0',
 `pole9` text NOT NULL,
 `pole9_` int(1) NOT NULL default '0',
 `pole10` text NOT NULL,
 `pole10_` int(1) NOT NULL default '0',
 `pole11` text NOT NULL,
 `pole11_` int(1) NOT NULL default '0',
 `pole12` text NOT NULL,
 `pole12_` int(1) NOT NULL default '0',
 `pole13` text NOT NULL,
 `pole13_` int(1) NOT NULL default '0',
 `pole14` text NOT NULL,
 `pole14_` int(1) NOT NULL default '0',
 `pole15` text NOT NULL,
 `pole15_` int(1) NOT NULL default '0',
 `pole16` text NOT NULL,
 `pole16_` int(1) NOT NULL default '0',
 `pole17` text NOT NULL,
 `pole17_` int(1) NOT NULL default '0',
 `pole18` text NOT NULL,
 `pole18_` int(1) NOT NULL default '0',
 `pole19` text NOT NULL,
 `pole19_` int(1) NOT NULL default '0',
 `pole20` text NOT NULL,
 `pole20_` int(1) NOT NULL default '0',
 `pole21` text NOT NULL,
 `pole21_` int(1) NOT NULL default '0',
 `pole22` text NOT NULL,
 `pole22_` int(1) NOT NULL default '0',
 `pole23` text NOT NULL,
 `pole23_` int(1) NOT NULL default '0',
 `pole24` text NOT NULL,
 `pole24_` int(1) NOT NULL default '0',
 `pole25` text NOT NULL,
 `pole25_` int(1) NOT NULL default '0',
 `pole26` text NOT NULL,
 `pole26_` int(1) NOT NULL default '0',
 `pole27` text NOT NULL,
 `pole27_` int(1) NOT NULL default '0',
 `pole28` text NOT NULL,
 `pole28_` int(1) NOT NULL default '0',
 `pole29` text NOT NULL,
 `pole29_` int(1) NOT NULL default '0',
 `pole30` text NOT NULL,
 `pole30_` int(1) NOT NULL default '0',
 `pole31` text NOT NULL,
 `pole31_` int(1) NOT NULL default '0',
 `pole32` text NOT NULL,
 `pole32_` int(1) NOT NULL default '0',
 `pole33` text NOT NULL,
 `pole33_` int(1) NOT NULL default '0',
 `pole34` text NOT NULL,
 `pole34_` int(1) NOT NULL default '0',
 `pole35` text NOT NULL,
 `pole35_` int(1) NOT NULL default '0',
 `pole36` text NOT NULL,
 `pole36_` int(1) NOT NULL default '0',
 `pole37` text NOT NULL,
 `pole37_` int(1) NOT NULL default '0',
 `pole38` text NOT NULL,
 `pole38_` int(1) NOT NULL default '0',
 `pole39` text NOT NULL,
 `pole39_` int(1) NOT NULL default '0',
 `pole40` text NOT NULL,
 `pole40_` int(1) NOT NULL default '0',
 `pole41` text NOT NULL,
 `pole41_` int(1) NOT NULL default '0',
 `pole42` text NOT NULL,
 `pole42_` int(1) NOT NULL default '0',
 `pole43` text NOT NULL,
 `pole43_` int(1) NOT NULL default '0',
 `pole44` text NOT NULL,
 `pole44_` int(1) NOT NULL default '0',
 `pole45` text NOT NULL,
 `pole45_` int(1) NOT NULL default '0',
 `pole46` text NOT NULL,
 `pole46_` int(1) NOT NULL default '0',
 `pole47` text NOT NULL,
 `pole47_` int(1) NOT NULL default '0',
 `pole48` text NOT NULL,
 `pole48_` int(1) NOT NULL default '0',
 `pole49` text NOT NULL,
 `pole49_` int(1) NOT NULL default '0',
 `pole50` text NOT NULL,
 `pole50_` int(1) NOT NULL default '0',
 `pole1__` int(2) NOT NULL default '0',
 `pole2__` int(2) NOT NULL default '0',
 `pole3__` int(2) NOT NULL default '0',
 `pole4__` int(2) NOT NULL default '0',
 `pole5__` int(2) NOT NULL default '0',
 `pole6__` int(2) NOT NULL default '0',
 `pole7__` int(2) NOT NULL default '0',
 `pole8__` int(2) NOT NULL default '0',
 `pole9__` int(2) NOT NULL default '0',
 `pole10__` int(2) NOT NULL default '0',
 `pole11__` int(2) NOT NULL default '0',
 `pole12__` int(2) NOT NULL default '0',
 `pole13__` int(2) NOT NULL default '0',
 `pole14__` int(2) NOT NULL default '0',
 `pole15__` int(2) NOT NULL default '0',
 `pole16__` int(2) NOT NULL default '0',
 `pole17__` int(2) NOT NULL default '0',
 `pole18__` int(2) NOT NULL default '0',
 `pole19__` int(2) NOT NULL default '0',
 `pole20__` int(2) NOT NULL default '0',
 `pole21__` int(2) NOT NULL default '0',
 `pole22__` int(2) NOT NULL default '0',
 `pole23__` int(2) NOT NULL default '0',
 `pole24__` int(2) NOT NULL default '0',
 `pole25__` int(2) NOT NULL default '0',
 `pole26__` int(2) NOT NULL default '0',
 `pole27__` int(2) NOT NULL default '0',
 `pole28__` int(2) NOT NULL default '0',
 `pole29__` int(2) NOT NULL default '0',
 `pole30__` int(2) NOT NULL default '0',
 `pole31__` int(2) NOT NULL default '0',
 `pole32__` int(2) NOT NULL default '0',
 `pole33__` int(2) NOT NULL default '0',
 `pole34__` int(2) NOT NULL default '0',
 `pole35__` int(2) NOT NULL default '0',
 `pole36__` int(2) NOT NULL default '0',
 `pole37__` int(2) NOT NULL default '0',
 `pole38__` int(2) NOT NULL default '0',
 `pole39__` int(2) NOT NULL default '0',
 `pole40__` int(2) NOT NULL default '0',
 `pole41__` int(2) NOT NULL default '0',
 `pole42__` int(2) NOT NULL default '0',
 `pole43__` int(2) NOT NULL default '0',
 `pole44__` int(2) NOT NULL default '0',
 `pole45__` int(2) NOT NULL default '0',
 `pole46__` int(2) NOT NULL default '0',
 `pole47__` int(2) NOT NULL default '0',
 `pole48__` int(2) NOT NULL default '0',
 `pole49__` int(2) NOT NULL default '0',
 `pole50__` int(2) NOT NULL default '0',
 `dat__` int(2) NOT NULL default '0',
 `seq__` int(2) NOT NULL default '0',
 `seq` int(10) NOT NULL default '0',
 `rem1` text NOT NULL,
 `rem2` text NOT NULL,
 `more1` text NOT NULL,
 `more1_` text NOT NULL,
 `more2` text NOT NULL,
 `more2_` text NOT NULL,
 `more3` text NOT NULL,
 `more3_` text NOT NULL,
 `more4` text NOT NULL,
 `more4_` text NOT NULL,
 `more5` text NOT NULL,
 `more5_` text NOT NULL,
 `more6` text NOT NULL,
 `more6_` text NOT NULL,
 `more7` text NOT NULL,
 `more7_` text NOT NULL,
 `more8` text NOT NULL,
 `more8_` text NOT NULL,
 `more9` text NOT NULL,
 `more9_` text NOT NULL,
 `more10` text NOT NULL,
 `more10_` text NOT NULL,
 PRIMARY KEY  (`id`),
 KEY `page_id` (`page_id`),
 KEY `goods_id` (`goods_id`)
)


",$link);
        };*/



        $tbl_goods_=$project."_goods_";//  таблицца товаров
/*        $line = mysql_query("show tables",$link);$flag=true;
        while($string=mysql_fetch_array($line)){if($string[0]==$tbl_goods_) $flag=false;};
        if($flag==true){
$line = mysql_query("
CREATE TABLE `$tbl_goods_` (
 `id` int(11) NOT NULL auto_increment,
 `name` text NOT NULL,
 `goods_id` int(8) NOT NULL default '0',
 `goods_id_` int(8) NOT NULL default '0',
 `seq` int(8) NOT NULL default '0',
 `regtime` timestamp(14) NOT NULL,
 `dat` int(8) NOT NULL default '0',
 `readonly` int(10) NOT NULL default '0',
 `icon` text NOT NULL,
 `desctop` int(1) NOT NULL default '0',
 `page_id` int(8) NOT NULL default '0',
 `page_id_` int(8) NOT NULL default '0',
 `descr` text NOT NULL,
 `pole1` text NOT NULL,
 `pole2` text NOT NULL,
 `pole3` text NOT NULL,
 `pole4` text NOT NULL,
 `pole5` text NOT NULL,
 `pole6` text NOT NULL,
 `pole7` text NOT NULL,
 `pole8` text NOT NULL,
 `pole9` text NOT NULL,
 `pole10` text NOT NULL,
 `pole11` text NOT NULL,
 `pole12` text NOT NULL,
 `pole13` text NOT NULL,
 `pole14` text NOT NULL,
 `pole15` text NOT NULL,
 `pole16` text NOT NULL,
 `pole17` text NOT NULL,
 `pole18` text NOT NULL,
 `pole19` text NOT NULL,
 `pole20` text NOT NULL,
 `pole21` text NOT NULL,
 `pole22` text NOT NULL,
 `pole23` text NOT NULL,
 `pole24` text NOT NULL,
 `pole25` text NOT NULL,
 `pole26` text NOT NULL,
 `pole27` text NOT NULL,
 `pole28` text NOT NULL,
 `pole29` text NOT NULL,
 `pole30` text NOT NULL,
 `pole31` text NOT NULL,
 `pole32` text NOT NULL,
 `pole33` text NOT NULL,
 `pole34` text NOT NULL,
 `pole35` text NOT NULL,
 `pole36` text NOT NULL,
 `pole37` text NOT NULL,
 `pole38` text NOT NULL,
 `pole39` text NOT NULL,
 `pole40` text NOT NULL,
 `pole41` text NOT NULL,
 `pole42` text NOT NULL,
 `pole43` text NOT NULL,
 `pole44` text NOT NULL,
 `pole45` text NOT NULL,
 `pole46` text NOT NULL,
 `pole47` text NOT NULL,
 `pole48` text NOT NULL,
 `pole49` text NOT NULL,
 `pole50` text NOT NULL,
 PRIMARY KEY  (`id`),
 KEY `page_id` (`page_id`),
 KEY `goods_id` (`goods_id`),
 FULLTEXT KEY `pole7` (`pole7`)
)
",$link);
        };*/


        //пользователи

        $tbl_goods_users=$project."_goods_users_";// GOODS-ы юзеры
/*        $line = mysql_query("show tables",$link);$flag=true;
        while($string=mysql_fetch_array($line)){if($string[0]==$tbl_goods_users) $flag=false;};
if($flag) $line = mysql_query("
CREATE TABLE $tbl_goods_users (
  `id` int(11) NOT NULL auto_increment,
  `regtime` timestamp(14) NOT NULL,
  `bdat` int(8) NOT NULL default '0',
  `name` text NOT NULL default '',
  `login` text NOT NULL default '',
  `pass` text NOT NULL default '',
  `icq` text NOT NULL default '',
  `sex` text NOT NULL default '',
  `descr` text NOT NULL default '',
  `ip` text NOT NULL default '',
  `cook` text NOT NULL default '',
  `status` int(3) NOT NULL default '0',
  `city` text NOT NULL default '',
  `lstdat` text NOT NULL default '',
  `lsttime` text NOT NULL default '',
  `email` text NOT NULL default '',
  `pix` text NOT NULL default '',
  PRIMARY KEY  (`id`)
)
",$link);*/



       $tbl_gus=$project."_goods_users_svaz";// GOODS-ы юзеры связь с папками
/*        $line = mysql_query("show tables",$link);$flag=true;
        while($string=mysql_fetch_array($line)){if($string[0]==$tbl_gus) $flag=false;};
if($flag) $line = mysql_query("
CREATE TABLE $tbl_gus (
  `id` int(11) NOT NULL auto_increment,
  `regtime` timestamp(14) NOT NULL,
  `user_id` int(10) NOT NULL default '0',
  `fold_id` int(10) NOT NULL default '0',
  `page_id` int(10) NOT NULL default '0',
  `access` int(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)
",$link);*/




/////////////////// ф-ции даты и времени
function dat($dat,$s){
// возвращает дату в формате DD/MM/YYYY из dat(YYYYMMDD)  с разделителем s (/)
        //if($dat<10000000)$dat="0".intval($dat);
        $dat=intval($dat);
        while(strlen($dat)<8)$dat="0".$dat;
        return substr($dat,6,2).$s.substr($dat,4,2).$s.substr($dat,0,4);
};

function dat_($dat,$s){
// возвращает дату в формате DD month YYYY из dat(YYYYMMDD)  с разделителем s ( )
        //if($dat<10000000)$dat="0".intval($dat);
        $dat=intval($dat);
        while(strlen($dat)<8)$dat="0".$dat;
        return intval(substr($dat,6,2)).$s.get_month(substr($dat,4,2)).$s.substr($dat,0,4);
};


function dat2($dat){
// возвращает дату в формате DD month из dat(YYYYMMDD)  с разделителем ( )
        //if($dat<10000000)$dat="0".intval($dat);
        $dat=intval($dat);
        while(strlen($dat)<8)$dat="0".$dat;
        return substr($dat,6,2)." ".get_month(substr($dat,4,2));
};

function tim($dat,$s){
// возвращает дату в формате HH:MM:SS из dat(HHMMSS)  с разделителем s (:)
        //if($dat<100000)$dat="0".intval($dat);
        $dat=intval($dat);
        while(strlen($dat)<6)$dat="0".$dat;
        return substr($dat,0,2).$s.substr($dat,2,2).$s.substr($dat,4,2);
};


function tim2($dat){
// возвращает дату в формате HH:MM из dat(HHMM)  с разделителем (:)
        //if($dat<100000)$dat="0".intval($dat);
        $dat=intval($dat);
        if(strlen($dat)<4){
          while(strlen($dat)<4)$dat="0".$dat;
        } else {
          while(strlen($dat)<6)$dat="0".$dat;
        }
        return substr($dat,0,2).":".substr($dat,2,2);
};

function tim4($dat){
// возвращает дату в формате HH:MM из dat(HHMMSS)  с разделителем (:)
        //if($dat<100000)$dat="0".intval($dat);
        $dat=intval($dat);
        while(strlen($dat)<6)$dat="0".$dat;
        return substr($dat,0,2).":".substr($dat,2,2);
};


function fulldat($dat,$tim,$s,$ss,$sss){
// возвращает полную дату в формате DD/MM/YYYY - HH:MM:SS из dat(YYYYMMDD) и tim(HHMMSS)  с разделителями s (/)  ss ( - ) и sss(:)
        if($dat<10000000)$dat="0".intval($dat);
        if($tim<100000)$tim="0".intval($tim);
        return substr($dat,6,2).$s.substr($dat,4,2).$s.substr($dat,0,4).$ss.substr($tim,0,2).$sss.substr($tim,2,2).$sss.substr($tim,4,2);
};

function fulldat_($dat,$tim,$s,$ss,$sss){
// возвращает полную дату в формате DD month YYYY - HH:MM:SS из dat(YYYYMMDD) и tim(HHMMSS)  с разделителями s ( )  ss ( - ) и sss(:)
        if($dat<10000000)$dat="0".intval($dat);
        if($tim<100000)$tim="0".intval($tim);
        return substr($dat,6,2).$s.get_month(substr($dat,4,2)).$s.substr($dat,0,4).$ss.substr($tim,0,2).$sss.substr($tim,2,2).$sss.substr($tim,4,2);
};

function full_dat($dat,$s,$ss,$sss){
// возвращает полную дату в формате DD month YYYY - HH:MM:SS из dat(YYYYMMDDHHMMSS)  с разделителями s ( )  ss ( - ) и sss(:)
        return substr($dat,6,2).$s.get_month(substr($dat,4,2)).$s.substr($dat,0,4).$ss.substr($dat,8,2).$sss.substr($dat,10,2).$sss.substr($dat,12,2);

};


function get_month($m){
        switch($m){
                case(1): return "января";
                break;
                case(2): return "февраля";
                break;
                case(3): return "марта";
                break;
                case(4): return "апреля";
                break;
                case(5): return "мая";
                break;
                case(6): return "июня";
                break;
                case(7): return "июля";
                break;
                case(8): return "августа";
                break;
                case(9): return "сентября";
                break;
                case(10): return "октября";
                break;
                case(11): return "ноября";
                break;
                case(12): return "декабря";
                break;
        };
};

function get_month2($m){
        switch($m){
                case(1): return "январь";
                break;
                case(2): return "февраль";
                break;
                case(3): return "март";
                break;
                case(4): return "апрель";
                break;
                case(5): return "май";
                break;
                case(6): return "июнь";
                break;
                case(7): return "июль";
                break;
                case(8): return "август";
                break;
                case(9): return "сентябрь";
                break;
                case(10): return "октябрь";
                break;
                case(11): return "ноябрь";
                break;
                case(12): return "декабрь";
                break;
        };
};

function check_len($str){
// ф-я проверяет строку str на наличие слов, длиной больше 30 и делает пробелы если такое есть
     $g=explode("\n",$str);
     for($r=0;$r<count($g);$r++){
        $m=explode(" ",$g[$r]);
        for($i=0;$i<count($m);$i++){
                $m[$i]=" ".$m[$i]." ";
                if((strlen($m[$i])>30)&&(!strpos($m[$i],"<"))&&(!strpos($m[$i],"["))&&(!strpos($m[$i],"http://"))){
                        for($j=30;$j<strlen($m[$i]);$j+=30){
                                $m[$i][$j]=" ";
                        };
                };
                $m[$i]=trim($m[$i]);
        };
        $g[$r]=implode(" ",$m);
     };
     $str=implode("\n",$g);
     return $str;
};


function get_select_types($def){
// ф-я возвращает кусок <select>-а с типами файлов

  $text='<option value=25';
  if($def==25) $text.= " SELECTED";
  $text.= '>файл/данные</option>';

  $text.= '<option value=26';
  if($def==26) $text.= " SELECTED";
  $text.= '>файл word</option>';

  $text.= '<option value=16';
  if($def==16) $text.= " SELECTED";
  $text.= '>файл excel</option>';

  $text.= '<option value=17';
  if($def==17 || $def==-1) $text.= " SELECTED";
  $text.= '>gif/jpg картинка</option>';

  $text.= '<option value=18';
  if($def==18) $text.= " SELECTED";
  $text.= '>SWF файл</option>';

  $text.= '<option value=19';
  if($def==19) $text.= " SELECTED";
  $text.= '>PDF файл</option>';

  $text.= '<option value=20';
  if($def==20) $text.= " SELECTED";
  $text.= '>AVI видео</option>';

  $text.= '<option value=21';
  if($def==21) $text.= " SELECTED";
  $text.= '>MPEG видео</option>';

  $text.= '<option value=22';
  if($def==22) $text.= " SELECTED";
  $text.= '>WAV звук</option>';

  $text.= '<option value=23';
  if($def==23) $text.= " SELECTED";
  $text.= '>MP3 музыка</option>';

  $text.= '<option value=24';
  if($def==24) $text.= " SELECTED";
  $text.= '>ZIP архив</option>';

  return $text;

};

function get_pix_types($type){
// ф-я определяет контент-тип для $type

  switch(intval($type)){

    case(25): // file
      $fe="";
      $ct="text/plain";
      $fn="file";
    break;

    case(26): // word
      $fe="doc";
      $ct="application/msword";
      $fn="document";
    break;

    case(16): // excel
      $fe="xls";
      $ct="application/vnd.ms-excel";
      $fn="table";
    break;

    default:
    case(17): // jpg/gif
      $fe="jpg";
      $ct="image/jpeg";
      $fn="picture";
    break;

    case(18): // swf
      $fe="swf";
      $ct="application/x-shockwave-flash";
      $fn="flash";
    break;

    case(19): // pdf
      $fe="pdf";
      $ct="application/pdf";
      $fn="document";
    break;

    case(20): // avi
      $fe="avi";
      $ct="video/avi";
      $fn="video";
    break;

    case(21): // mpeg
      $fe="mpeg";
      $ct="video/avi";
      $fn="video";
    break;

    case(22): // вав
      $fe="wav";
      $ct="audio/x-wav";
      $fn="sound";
    break;

    case(23): // mp3
      $fe="mp3";
      $ct="audio/mpeg";
      $fn="sound";
    break;

    case(24): // zip
      $fe="zip";
      $ct="application/zip";
      $fn="archive";
    break;

  };

  //$s[0]=$fe; // расширение
  //$s[1]=$ct; // контент-туп
  //$s[2]=$fn; // название файла
  $s=$ct;

  return $s;

};

function get_pix_types_name($type){
// ф-я возвращает название контент-тупа
    switch($type){

      case(25):
        return "файл/данные";
      break;
      case(26):
        return "файл word";
      break;
      case(16):
        return "файл excel";
      break;
      case(17):
        return "gif/jpg картинка";
      break;
      case(18):
        return "SWF файл";
      break;
      case(19):
        return "PDF файл";
      break;

      case(20):
        return "AVI видео";
      break;
      case(21):
        return "MPEG видео";
      break;
      case(22):
        return "WAV звук";
      break;
      case(23):
        return "MP3 музыка";
      break;
      case(24):
        return "ZIP архив";
      break;

    };
};





/* далее ф-и антимата */

$tbl_ffuck="gorod_forum_fuckword";// слова нехорошие

function nofuckl($l){
// ф-я перебирает буквы, ищет аналоги в англ. языке.
        if($l=="a")$l="а";
        if($l=="6")$l="б";
        if($l=="b")$l="в";
        if($l=="8")$l="в";
        if($l=="d")$l="д";
        if($l=="e")$l="е";
        if($l=="ё")$l="е";
        if($l=="Ё")$l="е";
        if($l=="*")$l="ж";
        if($l=="3")$l="з";
        if($l=="u")$l="и";
        if($l=="й")$l="и";
        if($l=="m")$l="м";
        if($l=="h")$l="н";
        if($l=="o")$l="о";
        if($l=="0")$l="о";
        if($l=="n")$l="п";
        if($l=="p")$l="р";
        if($l=="c")$l="с";
        if($l=="t")$l="т";
        if($l=="y")$l="у";
        if($l=="x")$l="х";
        if($l=="4")$l="ч";
        if($l=="r")$l="я";
        return($l);
};

function preparenofuck($name){
// ф-я "подготавливает" строку для использования ф-и нофак
//global $ip;

        for($i=0;$i<strlen($name);$i++){
//if($ip=="213.170.77.253")print "<br>$name[$i]:".ord($name[$i])."\n";
                if((ord($name[$i])>=192)&&(ord($name[$i])<=223))$name[$i]=chr(ord($name[$i])+32);
//if($ip=="213.170.77.253")print "<br>$name[$i]:".ord($name[$i])."<br>\n";
        };
//if($ip=="213.170.77.253")print "<hr>";
        //$name=strtolower($name);
        for($i=0;$i<strlen($name);$i++){
//if($ip=="213.170.77.253")print "<br>$name[$i]:".ord($name[$i])."\n";
                $name[$i]=nofuckl($name[$i]);
//if($ip=="213.170.77.253")print "<br>$name[$i]:".ord($name[$i])."<br>\n";
        };

//if($ip=="213.170.77.253")print $name;
        //exit(1);

        return $name;
};

function check_fword($msg,$word,$pos){
// ф-я проверяет не исключение ли это?
// в строке msg слова word на позиции pos
        global $tbl_ffuck;
        global $link;

        //print $word."<br>";

        $cmd="SELECT name FROM $tbl_ffuck where name LIKE '%$word%' and w_id<>0";
        $line = mysql_query($cmd,$link);
        while($s=mysql_fetch_array($line)){

          @$t=strpos($msg,$s[0],($pos+strlen($word)-strlen($s[0])-1));

          //print "($msg) $word($pos)$s[0](".$t."/".($pos+strlen($word)-strlen($s[0])-1).")<br>";

          if($t!=""){// && strpos($msg,$s[0],($pos+strlen($word)-strlen($s[0])))<=$pos){

            //print "<b>!</b><br>";
            //print "($msg) $word($pos)$s[0](".$t.")<br>";

            if($t<=$pos){
                return false;
            };
          };

        };

        return true;

};

function nofuck($msg){

        global $tbl_ffuck;
        global $link;

        $newmsg=preparenofuck(" $msg ");

        //print "<!--$newmsg-->";
        //exit(1);

        $cmd="SELECT name,code FROM $tbl_ffuck WHERE w_id=0";
        $line = mysql_query($cmd,$link);
        while($s=mysql_fetch_array($line)){
                $pos=0;
                while(strpos($newmsg,$s[0],$pos)){

                  //print "1---)".$msg." ($newmsg/$s[0]/$pos)<br>";

                  $pos=strpos($newmsg,$s[0],$pos);
                  //$newmsg=substr($newmsg,0,$pos).$s[1].substr($newmsg,$pos+strlen($s[0]),strlen($newmsg)-($pos+strlen($s[0])));
                  if(check_fword($newmsg,$s[0],$pos))$msg=substr($msg,0,$pos-1).$s[1].substr($msg,$pos-1+strlen($s[0]),strlen($msg)-($pos-1+strlen($s[0])));

                  $newmsg=substr($newmsg,0,$pos).$s[1].substr($newmsg,$pos+strlen($s[0]),strlen($newmsg)-($pos+strlen($s[0])));

                  //print "2---)".$msg." ($newmsg/$s[0]/$pos)<br>";

                  $t=strpos($newmsg,$s[0],$pos);
                  //print "=$t=<br>";

                };

        };


        return $msg;

};


/* конец ф-ци антимата */



//голосования

function check_golos($fold_g_id,$fold_ip_id){ // голосование

      global $tbl_goods;
      global $ip;
      global $golosid;
      global $link;
      global $tbl_goods_;
      global $golos;
      global $addgolos;
      global $page_id;

      $golosid=intval($golosid);

      if($addgolos=='add'){
        $cmd="SELECT * FROM $tbl_goods_ WHERE goods_id=$fold_ip_id and dat=".date("Ymd")." and pole1=$golosid and name='$ip'";
//print $cmd."<br>";
        $line = mysql_query($cmd,$link);
        if(!($st=mysql_fetch_array($line))){//такого IP за сегодня еще небыло

          $cmd="UPDATE $tbl_goods_ SET pole3=pole3+1, pole".$golos."=pole".$golos."+1 WHERE id=$golosid and goods_id=$fold_g_id";
//print $cmd."<br>";
          $line = mysql_query($cmd,$link);
//print mysql_error();

          $cmd="INSERT INTO $tbl_goods_ (page_id,goods_id,dat,name,pole1) VALUES ($page_id,$fold_ip_id,".date("Ymd").",'$ip',$golosid)";
//print $cmd."<br>";
          $line = mysql_query($cmd,$link);
//print mysql_error();

        };
      };

};

function get_golos($fold_g_id){

      global $tbl_goods_;
      global $ip;
      global $golosid;
      global $link;
      global $tbl_goods;
      global $golos;

      $golosid=intval($golosid);

      $lineZ = mysql_query("SELECT id,pole1,pole2,pole3,pole4,pole5,pole6,pole7,pole8,pole9,pole10,pole11,pole12,pole13,pole14,pole15,pole16,pole17,pole18,pole19,pole20,pole21,pole22,pole23,pole24,pole25 FROM $tbl_goods_ WHERE goods_id=$fold_g_id and id=$golosid",$link);
      if($ss=mysql_fetch_array($lineZ)){

            $text='<div id="Head"><h1>'.$ss["pole1"].'</h1></div><div id="Result">';

            for($i=6;$i<26;$i+=2){
                if($ss["pole".$i]!=""){
                  $text.='<div><p>'.$ss["pole".$i].' ('.(round(100*(($ss["pole".($i+1)]/$ss["pole3"])*100))/100).'%)</p><p><img src="/i/p.gif" alt="" width="'.(1.8*($ss["pole".($i+1)]/$ss["pole3"])*100).'"></p></div>';
                };
            };

            $text.='</div>';

            /*
            $text='<table cellpadding="4" cellspacing="0" border="0"><tr><td height="70"><h2>'.$ss["pole1"].'</h2></td></tr></table><table cellpadding="2" cellspacing="0" border="0">';
            for($i=6;$i<26;$i+=2){
                if($ss["pole".$i]!="")$text.='<tr><td><img src="/i/c23.gif" alt="" border="0" width="10" height="9"></td>
                    <td class="c10" width=100%>'.$ss["pole".$i].' ('.(round(100*(($ss["pole".($i+1)]/$ss["pole3"])*100))/100).'%)</td></tr>
                    <tr><td>&nbsp;</td><td><img src="/i/golos_line.gif" alt="" border="0" height="8" width="'.(1.8*($ss["pole".($i+1)]/$ss["pole3"])*100).'"></td></tr>';
            };
            $text.="</table>";
            */
      };

      return $text;

};


function get_file_name($id){
// ф-я возвращает название файла
        global $tbl_goods_;
        global $link;
        $line = mysql_query("SELECT name FROM $tbl_goods_ WHERE id=".intval($id),$link);
        $s=mysql_fetch_array($line);
        return $s[0];
};

function get_fold_name($id){
// ф-я возвращает название папки
        global $tbl_goods;
        global $link;
        $line = mysql_query("SELECT name FROM $tbl_goods WHERE id=".intval($id),$link);
        $s=mysql_fetch_array($line);
        return $s[0];
};


function get_day($m){
        switch($m){
                case(1): return "понедельник";
                break;
                case(2): return "вторник";
                break;
                case(3): return "среда";
                break;
                case(4): return "четверг";
                break;
                case(5): return "пятница";
                break;
                case(6): return "суббота";
                break;
                case(0): return "воскресенье";
                break;
        };
};

function download_url($url) {
  $user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}
//==============================================================================

?>
