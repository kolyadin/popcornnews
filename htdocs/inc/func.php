<?


// чистит запрос от мусора
function clean_request($REQUEST)
{
 $REQUEST=eregi_replace("\?","",$REQUEST);
 $REQUEST=eregi_replace("'","",$REQUEST);
 $REQUEST=eregi_replace('"',"",$REQUEST);
 $REQUEST=eregi_replace(">","",$REQUEST);
 $REQUEST=eregi_replace("<","",$REQUEST);

 while ($REQUEST[0]=="/") $REQUEST=substr($REQUEST,1);
 while ($REQUEST[(strlen($REQUEST)-1)]=="/") $REQUEST=substr($REQUEST,0,(strlen($REQUEST)-1));
 while (strpos($REQUEST,"//")) $REQUEST=eregi_replace("//","/",$REQUEST); 

 $REQUEST=htmlspecialchars($REQUEST);
 return $REQUEST;
};

// считает сколько файлов в папке
function dir_count($dir)
{
  $d=dir($dir);
  $num=0;
  while (false !== ($file = $d->read())) if (!is_dir($dir.'/'.$file)) $num++;
  $d->close();
  return $num;
};


function get_file($id)
{
 global $link;
 global $tbl_goods_;
 
 $id=intval($id);

 $line = mysql_query("SELECT * FROM $tbl_goods_ WHERE id=$id",$link);
 $s=mysql_fetch_array($line);
 return $s;

};


?>