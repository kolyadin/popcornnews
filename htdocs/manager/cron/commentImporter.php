<?php
/**
 * User: anubis
 * Date: 29.03.13 11:26
 */

require_once dirname(__FILE__) . '/../inc/connect.php';
require_once dirname(__FILE__).'/../../data/libs/config.lib.php';
require_once dirname(__FILE__).'/../../data/libs/ui/user.lib.php';
require_once dirname(__FILE__).'/../../data/libs/ui/im/CommentsImporter.php';

$hr = mysql_query("SELECT * FROM news_convert_queue ORDER BY nid DESC LIMIT 10");

if($hr === false) {
    echo mysql_error()."\r\n";
    exit;
}

while(($r = mysql_fetch_assoc($hr)) != false) {

    mysql_query("UPDATE news_convert_queue SET stat = 1 WHERE nid = {$r['nid']}");

    echo "Converting {$r['nid']}...";
    CommentsImporter::import($r['nid'], $r['type']);

    mysql_query("DELETE FROM news_convert_queue WHERE nid = {$r['nid']}");
    echo "done\r\n";

}

mysql_free_result($hr);