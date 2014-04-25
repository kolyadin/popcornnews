<?

    switch($news_id){

      default:

        print "<br><br>\n\nNEWS ID not found: $news_id\n\n<br><br>";

      break;

      /*
      case(37312): // ЦНТИ "ПРОГРЕСС" LIFESTYLE

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        $buf=download($url);

        print $url;

        print "-".strlen($buf)."-";

        $result=preg_match_all("/(?<=\"\>\<a href\=\")([\w\W]{1,100}?)(?=\"\>\w{5,15}\<\/a\>\<\/span\>)/",$buf,$found);

        //print $buf;
        //exit(1);


        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          print $found[0][$i]."<br>";
        };


        $url="http://www.cntiprogress.ru/news/news_spb/";
        
        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";

            //print "<br>".$news[$i];
            

            $buf=download($url.$news[$i]);

            //print $news[$i]."(".strlen($buf).")<br>";

            
            $result=preg_match_all("/(?<=\" class\=\"Event_Title\"\>)([\w\W]{1,100}?)(?=\<\/span\>\<br\>\<)/",$buf,$found);
            $name=$found[0][0];

            $result=preg_match_all("/(?<=\<img src\=\")([\w\W]{1,50}?)(?=\" align\=\"middle\" border\=\"1\"\>)/",$buf,$found);
            $pic=$found[0][0];
            if($pic!="")$pic=$url.$pic;

            $result=preg_match_all("/(?<= class\=\"Event_BriefDescription\"\>)([\w\W]{300,}?)(?=\<\/td\>\<\/tr\>\<tr\>\<td class\=\"Event_BodyCell\")/",$buf,$found);

            $txt=$found[0][0];

            print "<h2>$name</h2>$txt<hr>$pic<hr><br><br>";
            //
            //print $url."new_site/news/".$pic."<br>";
            
            //add_news($name,$txt,$pic,"",$news[$i]);
            //exit(1);
          };
        };

      break;
      */


      case(298933): // CarDesign.ru АВТО

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        $buf=download($url);

        //print $url;

        //print "-".strlen($buf)."-";

        $result=preg_match_all("/(?<=\s\shref\=\")([\w\W]{1,100}?)(?=\"\>)/",$buf,$found);

        //print $buf;
        //exit(1);


        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$url.$found[0][$i];
          //print $found[0][$i]."<br>";
        };

        //exit(1);

        $url="http://www.cardesign.ru/ru/news/";
        
        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";

            //print "<br>".$url.$news[$i];
            

            $buf=download($news[$i]);

            //print $news[$i]."(".strlen($buf).")<br>";

            
            //$result=preg_match_all("/(?<=\<TD\>\<font class\=text15\>\<b\>)([\w\W]{1,100}?)(?=\<\/b\>\<\/font\>\<br\>\<\/td\>\<\/tr\>)/i",$buf,$found);
            $result=preg_match_all("/(?<=\<TD align=left\>\<FONT class=q1\>)([\w\W]{1,200}?)(?=\<\/FONT\>\<\/TD\>\<\/TR\>)/i",$buf,$found);
            $name=strip_tags($found[0][0]);

            //$result=preg_match_all("/(?<=\'\>\<IMG src\=\")([\w\W]{1,100}?)(?=\" border\=0 align\=\"middle\"\>\<\/a\>\<BR\>)/i",$buf,$found);
            $result=preg_match_all("/(?<=javascript\:openimage\(\"http\:\/\/www\.cardesign\.ru\/image\/\?src=\/)([\w\W]{1,150}?)(?=\&w=\d\d\d\&h=\d\d\d\",\d\d\d,\d\d\d\)\'\>)/i",$buf,$found);
            $pic="";
            if($found[0][0]!=""){
              //$pic=$url.$news[$i];
              //$pic=substr($url.$pic,0,strrpos($pic,'/'))."/".$found[0][0];
              $pic="http://www.cardesign.ru/".$found[0][0];
            };

            //$result=preg_match_all("/(?<=\<TBODY\>\<TBODY\>\<TR\>\<TD vAlign\=top\>)([\w\W]{1,}?)(?=\<\/FONT\>\<br\>\<br\>)/i",$buf,$found);
            //$result=preg_match_all("/(?<=\<TR\>\<TD vAlign\=top\>)([\w\W]{1,}?)(?=Ждём ваших мнений на нашем)/i",$buf,$found);
            $result=preg_match_all("/(?<=\<TD vAlign=top\>\<FONT class=textn\>)([\w\W]{1,}?)(?=\<\/FONT\>\<\/TD\>)/i",$buf,$found);
            $txt=strip_tags($found[0][0],"<b><p><i><u><strong><br>");

            //print "<h2>$name</h2>$txt<hr>$pic<hr><br><br>";
            
            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);
            //exit(1);
          };
        };

      break;


      case(298934): // НТВ ПЕТЕРБУРГ (город)

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        $buf=download($url);

        //$result=preg_match_all("/(?<=\<a href\=\/)([\w\W]{1,60}?)(?=\sclass\=text13\>)/",$buf,$found);
        $result=preg_match_all("/(?<=\<div class=\"txt_blue\"\>\<a href=\"\/spb\/)([\w\W]{1,60}?)(?=\"\>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };


        $url="http://news.ntv.ru/spb/";
        
        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";

            //print "<br>".$url.$news[$i];
            //exit(1);
            

            $buf=download($url.$news[$i]);

            //print $news[$i]."(".strlen($buf).")<br>";

            //$result=preg_match_all("/(?<=\<div class\=redtext14 style=\'margin\-top\:3px; margin\-bottom:5px\'\>)([\w\W]{1,250}?)(?=\<\/div\>)/",$buf,$found);
            //<title>Россияне не хотят ехать во Францию</title>
            $result=preg_match_all("/(?<=\<title\>)([\w\W]{1,250}?)(?=\<\/title\>)/",$buf,$found);
            $name=$found[0][0];

            //$result=preg_match_all("/(?<=\<img src\=\")([\w\W]{1,100}?)(?=\" width\=\"218\" border\=\"1\" class\=photo)/",$buf,$found);
            $result=preg_match_all("/(?<=\<td\>\<img src=\"\/home\/news\/)([\w\W]{1,100}?)(?=\" alt=\"\" border=\"0\"\>\<\/td\>)/",$buf,$found);
            $pic=$found[0][0];
            if($pic!="")$pic="http://news.ntv.ru/home/news/".$pic;

            //$result=preg_match_all("/(?<=\<div class\=text13 style\=\'margin\-top\:3px\; margin\-bottom\:5px\'\>)([\w\W]{1,}?)(?=\<a href\=\"javascript\:void\(0\)\")/",$buf,$found);
            $result=preg_match_all("/(?<=\<tr\>\<td valign=\"top\" id=\"onenews\"\>)([\w\W]{1,}?)(?=\<strong\>Читайте также\:\<\/strong\>\<br\>\<br\>)/",$buf,$found);
            $txt=$found[0][0];

            $result=preg_match_all("/(?<=\<div class=\"txtb\"\>)([\w\W]{1,}?)(?=\<\/div\>\<\/td\>\<\/tr\>)/",$buf,$txt);
            $txt=$found[0][0];

            //print "<h2>$name</h2>$txt<hr>$pic<hr><br><br>";
            //exit(1);

            //
            //print $url."new_site/news/".$pic."<br>";
            
            $txt=strip_tags($txt,"<b><br><i><u><p>");

            add_news($name,$txt,$pic,"",$news[$i]);
            //exit(1);
          };
        };

      break;


      case(298944): // Вести "Санкт-Петербург" (транспорт)
      case(298943): // Вести "Санкт-Петербург" (образование)
      case(298942): // Вести "Санкт-Петербург" (экология)

      case(298941): // Вести "Санкт-Петербург" (политика)
      case(298936): // Вести "Санкт-Петербург" (город)
      case(298939): // Вести "Санкт-Петербург" (спорт)
      case(298938): // Вести "Санкт-Петербург" (lifestyle)
      case(298937): // Вести "Санкт-Петербург" (социалка-экономика)
      case(298940): // Вести "Санкт-Петербург" (город)
      case(298935): // Вести "Санкт-Петербург" (экономика)

      case(539329)://криминал

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        $buf=download($url);

        //$result=preg_match_all("/(?<=\<b\>\<a href=\")([\w\W]{1,50}?)(?=\" style\=\"text\-decoration\: none\" class=\"\"\>)/",$buf,$found);
        $result=preg_match_all("/(?<=\<a href=\')([\w\W]{1,50}?)(?=\'\>\<img src\=http\:\/\/www\.rtr\.spb\.ru)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];

          //print $found[0][$i]."<br>";

        };

        //exit(1);

        //$url="http://www.rtr.spb.ru/vesti/vesti_news/";
        $url="http://www.rtr.spb.ru/vesti/vesti_2005/";

        //http://www.rtr.spb.ru/vesti/vesti_2005/default_cat.asp?Categ=11
        //http://www.rtr.spb.ru/vesti/vesti_2005/news_detail.asp?id=3547
        
        for($i=0;$i<count($news);$i++){
//print "!";
          
          $cmd="SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."!!!'";
          $line = mysql_query($cmd,$link);
          if(!($s=mysql_fetch_array($line))){
          
            print ".  ";

            //print "<br>".$url.$news[$i];
            

            $buf=download($url.$news[$i]);

            //print $news[$i]."(".strlen($buf).")<br>";

            $result=preg_match_all("/(?<=\<font class\=\"base\"\>)([\w\W]{1,250}?)(?=\<\/font\>\<\/b\>)/",$buf,$found);
            $name=$found[0][0];
            if($name==""){
                $result=preg_match_all("/(?<=\<font class\=\"importance\"\>)([\w\W]{1,250}?)(?=\<\/font\>\<\/b\>)/",$buf,$found);
                $name=$found[0][0];
            };

            $result=preg_match_all("/(?<=\<img src=\")([\w\W]{1,80}?)(?=\" alt=\"\" style\=\'border\: 0px ridge #FFFFFF\'\>\<\/div\>)/",$buf,$found);
            $pic=$found[0][0];

            /*
            $result=preg_match_all("/(?<=\<blockquote\>\<p align\=\'justify\'>)([\w\W]{1,}?)(?=\<a href\=\"news_detail_v\.asp\?id\=)/",$buf,$found);
            $txt=$found[0][0];
            if($txt==""){
                $result=preg_match_all("/(?<=\<blockquote\>\<p align\=\'justify\'>)([\w\W]{1,}?)(?=\<p align\=\"justify\"\>)/",$buf,$found);
                $txt=$found[0][0];
            };
            */
            //$result=preg_match_all("/(?<=\<blockquote\>\<p align\=\'justify\'>)([\w\W]{1,}?)(?=\<)/",$buf,$found);
            $result=preg_match_all("/(?<=\<blockquote\>\<p align\=\'justify\'>)([\w\W]{1,}?)(?=\<\/blockquote\>)/",$buf,$found);
            $txt=$found[0][0];

            //$txt=strip_tags($txt,"");
            $txt=strip_tags($txt,"<b><p><i><u><strong><br>");
            $txt=eregi_replace("См. видео","",$txt);

            //print "<h2>$name</h2>$txt<hr>$pic<hr><br><br>";
            //
            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);
            //exit(1);
          };

//print $cmd.mysql_error();

        };

      break;

      



      case(298948): // МК в Питере (рампа - лайфстайл)
      case(298947): // МК в Питере (мегаполис-город)
      case(298946): // МК в Питере (политика)
      case(298945): // МК в Питере (спорт)
      case(539382): // криминал

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        $buf=download($url);

        $result=preg_match_all("/(?<=\<a href=\")([\w\W]{1,100}?)(?=\"\>\&raquo\;\&raquo\;\&raquo\;\<\/a\>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];

          //print $found[0][$i]."<br>";

        };


        $url="http://www.mk-piter.ru/";
        
        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";

            //print "<br>".$url.$news[$i];
            

            $buf=download($url.$news[$i]);

            //print $news[$i]."(".strlen($buf).")<br>";

            $result=preg_match_all("/(?<=\<h1\>)([\w\W]{1,250}?)(?=\<\/h1\>)/",$buf,$found);
            $name=$found[0][0];

            $result=preg_match_all("/(?<=\<p\>\<img src\=\"gif\.php\?id=)([\d]{1,25}?)(?=\")/",$buf,$found);
            $pic=$found[0][0];
            if($pic!="")$pic=$url."gif.php?id=".$pic;
            //http://mk-piter.ru/gif.php?id=3171

            $result=preg_match_all("/(?<=\<\/h1\>\<p\>\<b\>)([\w\W]{1,}?)(?=\<img src\=\"i\/printer1\.gif\" width\=20 height\=20 border\=0\>)/",$buf,$found);
            //удалиитьвсе теги

            $txt=strip_tags($found[0][0],"<b><br><i><u><p><font>");

            //print "<h2>$name</h2>$txt<hr>$pic<hr><br><br>";
            //
            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);
            //exit(1);
          };
        };

      break;       


      case(298877): // фонтанка город
      case(298874): // фонтанка экономика
      case(298881): // фонтанка лайфстайл
      case(298882): // фонтанка спорт
      case(298878): // фонтанка политика
      case(539339): // криминал
        
        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */
        $buf=download($url);

        unset($news);

        $result=preg_match_all("/(?<=\<div class=ArticleAnonsTitle\>)[\W\w]{1,180}(?=\<\/A\>\<br\>)/",$buf,$found);

        for($j=0;$j<count($found[0]);$j++){
           $news[count($news)]=substr($found[0][$j],strpos($found[0][$j],'"')+1,strpos($found[0][$j],'"',strpos($found[0][$j],'"')+1)-strpos($found[0][$j],'"')-1);
        };

        
        
        $result=preg_match_all("/(?<=\<td width=100% class=BlockLastNewsText\>\<A HREF=\")[\W\w]{1,180}(?=\"\>)/",$buf,$found);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        }


        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            /*
            if($fp=fopen($news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp);
            };
            */
            
            $buf=download($news[$i]);

            $result=preg_match_all("/(?<=\<div class=ArticleTitle style=\"padding-left: 130px;\"\>)[\W\w]{1,200}(?=<\/div\>)/",$buf,$found);
            $name=$found[0][0];

            //текст
            $result=preg_match_all("/(?<=\<P\>)[\W\w]*(?=\<\/P\>)/",$buf,$found);
            $txt=$found[0][0];

            $m=explode('/',$news[$i]);

            $pix="http://www.fontanka.ru/pictures/".$m[count($m)-1]."/title.jpg";

            //print "<h1>$name</h1>$txt<hr>-$pix-<hr>";

            //print "-!$pix!-<br>";

            add_news($name,$txt,$pix,"",$news[$i]);
          };
        };

      break;

      case(298876): // Autonews.ru
        
        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);
        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */
        $buf=download($url);
        //топ новость
        //Opel начнет продажи новых Vectra и Signum с новыми двигателями и прочими новшествами... <a href="/news/html/newsline/index.shtml?/2005/06/15/66464" onClick='rBannerFash("/news/html/newsline/index.shtml?/2005/06/15/66464", "auto_fashion"); return false;' class="std">далее</a>
        //$result=preg_match_all("/(?<=\.\.\. \<a href=\"\/)[.\w\W\s]{1,150}(?=\" class=\"std\"\>далее)/",$buf,$found);
        //$result=preg_match_all("/(?<=\.\.\. \<a href=\"\/)[.\w\W\s]{1,150}(?=\" onClick=\')/",$buf,$found);

        $result=preg_match_all("/(?<=\<h4 class=\"name\"\>\<a href=\")[.\w\W\s]{1,150}(?=\" title=\")/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          //print "<br>".$found[0][$i];
        };

        //остальные новости
        //$result=preg_match_all("/(?<=\<li class=\"nsln\"\>\<a href=\"\/)[.\w\W\s]{1,150}(?=\" class=\"news\"\>\<font color=\"#000000\"\>\<b\>)/",$buf,$found);
        //$result=preg_match_all("/(?<=\<li class=\"nsln\"\>\<a href=\"\/)[.\w\W\s]{1,150}(?=\" onClick=\")/",$buf,$found);

        $result=preg_match_all("/(?<=\<p class=\"n_line\"\>&bull;&nbsp;\<a href=\")[.\w\W\s]{1,150}(?=\" onClick=\')/",$buf,$found);


        //<li class="nsln"><a href="/news/html/newsline/index.shtml?/2005/06/15/66544" onClick="rBannerFash('/news/html/newsline/index.shtml?/2005/06/15/66544', 'auto_fashion'); return false;" class="news"><font color="#000000"><b>17:56</b> Китайцы открыли первый исследовательский центр в Италии </font> </a>
        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          //print "<br>".$found[0][$i];
        };


        $result=preg_match_all("/(?<=\<i\>\<a href=\")[.\w\W\s]{1,150}(?=\" class=\"black\" title=\"\"\>\<b\>)/",$buf,$found);


        //<li class="nsln"><a href="/news/html/newsline/index.shtml?/2005/06/15/66544" onClick="rBannerFash('/news/html/newsline/index.shtml?/2005/06/15/66544', 'auto_fashion'); return false;" class="news"><font color="#000000"><b>17:56</b> Китайцы открыли первый исследовательский центр в Италии </font> </a>
        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          //print "<br>".$found[0][$i];
        };



        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            
            };
            */

            $buf=download($url.$news[$i]);

            //print "<br>=".strlen($buf)."=";

            //название
            //$result=preg_match_all("/(?<=\<p class=\"\head\"\>)[.\w\W\s]{1,250}(?=\<\/p\>)/",$buf,$found);
            $result=preg_match_all("/(?<=\<div class=car_info\>\<h3 class=modelname\>)[.\w\W\s]{1,250}(?=\<\/h3\>\<br\>\<\/div\>)/",$buf,$found);
            $name=$found[0][0];

            //текст
            //$result=preg_match_all("/(?<=\<\/p\>\<p align=\"justify\"\>)[\W\w]*(?=\<\/td\>\s+\<\/tr\>\s+\<\!\-\-\s+\<tr\>)/",$buf,$found);
            //$result=preg_match_all("/(?<=\<p align=\"justify\"\>)[\W\w]*(?=\<\/p\>\s+\<\/td\>\s+\<\/tr\>\s+)/",$buf,$found);

            $result=preg_match_all("/(?<=\<div class=full_news\>)[\W\w]*(?=\<div style=\"float\:left\"\>\<a href=\"\/automarket_news\")/",$buf,$found);
            $txt=$found[0][0];
            $txt=strip_tags($found[0][0],"<b><p><i><u><strong><br>");

            //картинка
            //onclick="MM_openBrWindow('/picture.shtml?/img/news/topics/2005/06/15/66464_top_photo.jpg','','width=
            //$result=preg_match_all("/(?<=\<img src='\/)[\W\w]{1,180}(?='\swidth='\d+'\sheight='\d+'\salign='left')/",$buf,$found);
            //$result=preg_match_all("/(?<=onclick=\"MM_openBrWindow\(\'\/picture\.shtml\?)[\W\w]{1,180}(?=\',\'\',\'width=)/",$buf,$found);

            $result=preg_match_all("/(?<=ShowPicture\(\')[\W\w]{1,180}(?=\',\'\',\'\d\d\d\',\'\d\d\d\'\)\"\>\<img src=\")/",$buf,$found);
            $pic=trim(strip_tags(eregi_replace("'",'"',$found[0][0])));  
            //if($pic!="")$pic="http://www.autonews.ru".$pic;

            //print "<h2>$name</h2>$txt<hr>$pic<hr><hr>";
            
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;
      
      case(298872): // Коммерсанть СПб
        
        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        //$r_id=8;//город
        //$url="http://www.kommersant.ru/region/spb/";
        //$news_id=17142;//id файла в каталоге связаного 
        /*
        if($fp=fopen($url,"rb")){
          //$buf=fread($fp,$max_file_size_url); 
          //buf.=fread($fp,$max_file_size_url); 
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
        
          fclose($fp); 
        };
        */

        $buf=download($url);
        
        //print "=".strlen($buf)."=<hr>";

        //$result=preg_match_all("/page.htm(.{1,150})icon_mor/",$buf,$found);
        $result=preg_match_all("/(?<=class=\"more\" href=\")[\W\w]{1,50}(?=\" target=\"_self\"\>подробнее)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          //print substr($found[0][$i],0,strpos($found[0][$i],'"'))."<br>";
          //$news[count($news)]=substr($found[0][$i],0,strpos($found[0][$i],'"'));
          $news[count($news)]="http://www.kommersant.ru".$found[0][$i];
          //print "<br>".$found[0][$i];;
          //print "<br>".substr($found[0][$i],0,strpos($found[0][$i],'"'));;
        };

        $result=preg_match_all("/(?<= href=\")[\W\w]{1,50}(?=\" class=\"reg_i_open\"\>подробнее)/",$buf,$found);

        for($i=0;$i<count($found[0]);$i++){
          //print substr($found[0][$i],0,strpos($found[0][$i],'"'))."<br>";
          //$news[count($news)]=substr($found[0][$i],0,strpos($found[0][$i],'"'));
          $news[count($news)]="http://www.kommersant.ru/region/spb/".$found[0][$i];
          //print "<br>".$found[0][$i];;
          //print "<br>".substr($found[0][$i],0,strpos($found[0][$i],'"'));;
        };

        
        //print_r($news);
        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($news[$i]);

            //print "=".strlen($buf)."=<hr>";
            //print $buf;
            //exit(1);

            //print "<hr>";
            
            //$result=preg_match_all("/(?<=title0\">)[.\w\W\s]{10,}(?=\<\/span\>\<br\>\<IMGTITLE)/i",$buf,$found);
            //print_r($found);
            //заголовок
            $result=preg_match_all("/(?<=\<td\>\<font color=\"#000000\" class=\"reg_m_title\"\>)[\W\w]{1,250}(?=\<\/font\>\<\/a\>\<\/td\>)/",$buf,$found);
            $name=$found[0][0];
            //print "<h2>$name</h2>";


            //$result=preg_match_all("/(?<=introd\">)[.\w\W\s]{10,}(?=\<\!-- Тело - конец)/i",$buf,$found);
            $result=preg_match_all("/(?<=\<span class=\"reg_m_vvodka\"\>)[\W\w]{1,}(?=\<\/span\>\<\/font\>)/",$buf,$found);
            //новость
            $txt=$found[0][0];
            //print_r($found);

            //<td><img src="pics/piter/2005/107/16_big.GIF" width="240" height="160" border="1"></td>
            //картинка новости
            //$result=preg_match_all("/(?<=\<img src=\")[.\w\W\s]{1,150}(?=imgkommersant)/i",$buf,$found);
            $result=preg_match_all("/(?<=\<img src=\")[.\w\W\s]{1,150}(?=\" width=\"\d\d\d\" height=\"\d\d\d\" border=\"1\"\>\<\/td\>)/i",$buf,$found);
            //print_r($found);
            $pic=$found[0][0];
            if($pic!="")$pic="http://www.kommersant.ru/region/spb/".$pic;
            //$pic=trim(strip_tags(eregi_replace("'",'"',substr($found[0][0],0,strpos($found[0][0],'"')))));  


            //print "<h2>$name<br><b>-$pic-</b></h2>$txt";
            add_news($name,$txt,$pic,$url,$news[$i]);
            
            /*
            $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and name LIKE '%$name%'",$link);
            if(!($s=mysql_fetch_array($line))){
              //нет такой новости еще походу

              
              if($pic!=""){
                $fp=fopen($url.$pic,"rb"); 
                $buf="";
                while (!feof($fp)) {
                  $buf .= fread($fp, $max_file_size_url);
                }
              
                fclose($fp); 
              
                if($buf!=""){

                  //print "=(".strlen($buf).")=";


                  //exit(1);
                  
                  //$pix=save_file("pic",strlen($buf),$buf,7,0,date("Ymd"),"-","-",0,0,0,0);
                  //print "($pix)";
                  
                  $n=tempnam("../upload/","");

                  $fp=fopen("$n",'w');
                  fwrite($fp,$buf);
                  fclose($fp);

                  //print "---!$n!---";
                  $img=getimagesize($n); 

                  switch($img[0]){

                    default:
                    case(2):
                      $ext=".jpg"; 
                    break;
                    case(1):
                      $ext=".gif"; 
                    break;
                    case(3):
                      $ext=".png"; 
                    break;
                    case(4):
                      $ext=".swf"; 
                    break;
                    case(5):
                      $ext=".psd"; 
                    break;
                    case(6):
                      $ext=".bmp"; 
                    break;

                  };
                  
                  rename($n,$n.$ext);
                  $n=$n.$ext;

                  $pix=save_file("pic".rand(),strlen($buf),$n,7,0,date("Ymd"),"-","-",0,0,0,0);

                  //unlink($n);

                  //print "-!$n!/!$pix!-";
                  //exit(1);

                  //$pix=save_file($n,file_size($n),$u,$type,$seq,$dat,$descr,$name,$g,$g_,$p,$p_);
                  //print "=$pix=";
                  //unlink($n);
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

                };

                //print "=$n=<br>";

              } else $pix="";

              $more_news="";//новости по теме
              //$pix="";//картинка раздела
              if(strlen($txt)>300)$anons=substr($txt,0,strpos($txt,".",120)).".";
                else $anons=$txt;

              $cmd="INSERT INTO $tbl_goods_ (goods_id,dat,pole22,name,pole1,pole2,pole4,pole5,pole8,pole11,pole23,pole24) 
                                       VALUES (396,".date("Ymd").",'".date("His")."','$name','$anons','$txt','Yes','$more_news','$pix','Yes','".$news[$i]."','$r_id')";
              $line = mysql_query($cmd,$link);

              if(mysql_error()==""){
                $cmd2="UPDATE $tbl_goods_ SET pole3=pole3+1 WHERE id=$news_id";
                $line = mysql_query($cmd2,$link);
              };

              //print "<br>$cmd<br>".mysql_error()."<br>";
            };

            //if($name!="" && $)


            //print "<hr>";
            */
          };
        };

//заголовок новости
//$result=preg_match_all("/(?<=title0\">)[.\w\W\s]{10,}(?=\<\/span\>\<br\>\<IMGTITLE)/i",$text,$found);


//<SPAN class="introd">
//<!-- Тело - конец -->
//$result=preg_match_all("/(?<!<td>)(?<=>)\d*\.\d*(?!<\/b>)(?=<\/a>)/",$text,$found);

//тело новости
//$result=preg_match_all("/(?<=introd\">)[.\w\W\s]{10,}(?=\<\!-- Тело - конец)/i",$text,$found);

//картинка новости
//<img src="pics/piter/2004/159/big.gif" width="240" height="161" alt="" hspace="10" vspace="5" border="1" style="imgkommersant">
//$result=preg_match_all("/(?<=\<img src=\")[.\w\W\s]{1,150}(?=imgkommersant)/i",$text,$found);

//$pic=substr($found[0][0],0,strpos($found[0][0],'"'));  

      break;

      case(298873): // газета недвижимости

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);
        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        //топ новость
        $result=preg_match_all("/(?<=\<a class=\"lmhl\" href=\"\/)[.\w\/]{1,50}(?=\")/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };




        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //название
            $result=preg_match_all("/(?<=\<center\>\<p align=\"center\" class=\"head\"\>)[.\w\W]{1,250}(?=\<\/p\>\<\/center\>)/",$buf,$found);
            $name=$found[0][0];

            //текст
            //$result=preg_match_all("/(?<=\<tr\>\<td\>\<p align=\"justify\"\>)[.\w\W]+(?=\<\/td\>\<\/tr\>\<\/table\>\<a name=\"obs\"\>\<\/a\>)/",$buf,$found);
            //$result=preg_match_all("/(?<=align=\"justify\"\>\<p align=\"justify\"\>\<b\>)[.\w\W]+(?=\<\!\-\-upload time)/",$buf,$found);
            //$result=preg_match_all("/(?<=\<p align=\"justify\"\>\<b\>)[.\w\W]+(?=\<\!\-\-upload time)/",$buf,$found);
            $result=preg_match_all("/(?<=\<p align=\"justify\"\>\<)[.\w\W]+(?=\<\!\-\-upload time)/",$buf,$found);
            $txt=$found[0][0];

            $pic="";
            
            //print "=<h1>$name</h1>=$txt<hr>";
            
            add_news($name,$txt,$pic,$url,$news[$i]);

          };
        };

      break;

      case(298880): // РБК

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        //$result=preg_match_all("/(?<=\<a href=\"\/spb\/)[.\w\/]{1,100}(?=\"\>\<li\>\<b\>)/",$buf,$found);
        $result=preg_match_all("/(?<=\<\/b\> \<a href=\"\/spb\/freenews\/)[.\w\/]{1,100}(?=\"\>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };
/*
print "<pre>";
print_r ($found);
print "</pre>";
exit(1);
*/
        $url="http://www.rbc.ru/spb/freenews/";

        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //print $url.$news[$i];
            //exit(1);

            //название
            $result=preg_match_all("/(?<=\<\!-- Начало новости --\>)[.\w\W]{1,250}(?=\<\/B>)/",$buf,$found);
            $name=strip_tags($found[0][0]);

            $result=preg_match_all("/(?<=\<p align=\"justify\"\>)[.\w\W]*(?=\<p align=justify\>)/i",$buf,$found);
            $txt=$found[0][0];

            $pic="";
            
//print "=<h1>$name</h1>=$txt<hr>";
            
            add_news($name,$txt,$pic,$url,$news[$i]);

          };
        };

      break;

      case(298895): // Официальный портал Администрации Санкт-Петербурга

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };

        */

        $buf=download($url);

        $result=preg_match_all("/(?<=\<p style=\"text-align: justify;\"\>\<b\>\d\d-\d\d-\d\d\d\d\<\/b\>\<br\>\<a href=\"\/)[.\w\W]{1,100}(?=\" style)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };

        //print "<pre>";
//print_r ($found);
//print "</pre>";
//exit(1);


        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //print $url.$news[$i];
            //exit(1);

            //название
            //$result=preg_match_all("/(?<=\<p\>\<strong\>\d\d-\d\d-\d\d\d\d\<\/strong\>)[\w\W]{1,250}(?=\<\/p\>)/",$buf,$found);
            $result=preg_match_all("/(?<=\<p align=justify\>\<strong\>\d\d-\d\d-\d\d\d\d\<\/strong\>)[\w\W]{1,250}(?=\<\/p\>)/",$buf,$found);
            $name=strip_tags($found[0][0]);

            $result=preg_match_all("/(?<=\<\/p\>\<span class=bnn\>)[\w\W]*(?=\<\/P\>)/",$buf,$found);
            $txt=$found[0][0];

            $pic="";
            
//print "=<h1>$name</h1>=$txt<hr>";
            
            add_news($name,$txt,$pic,$url,$news[$i]);

          };
        };

      break;


      case(298898): // невский спорт

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        $result=preg_match_all("/(?<=\<a href=\")[\w\W]{1,100}(?=\" class=\"smallink\"\>Подробнее..\<\/a\>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };
/*
print "<pre>";
print_r ($found);
print "</pre>";
exit(1);
*/

        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            
            
            /*
            if($fp=fopen($news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($news[$i]);

            //print $url.$news[$i];
            //exit(1);

            //название
            
            //$result=preg_match_all("/(?<=\<p align=justify\>\<b\>)[\w\W]{1,250}(?=\<\/b\>\<\/p\>)/",$buf,$found);
            $result=preg_match_all("/(?<=\<p align=\"justify\"\>\<b\>)[\w\W]{1,250}(?=\<\/b\>\<\/p\>)/",$buf,$found);
            $name=strip_tags($found[0][0]);

            $result=preg_match_all("/(?<=\<\/b\>\<\/p\>)[\w\W]*(?=\<p align=\"right\" class=\"ah\"\>)/",$buf,$found);
            $txt=$found[0][0];
            $txt=strip_tags($found[0][0],"<b><p><i><u><strong><br>");

            //$result=preg_match_all("/(?<=\s\<img src=\")[\w\W]{1,250}(?=\" alt=\"[\w\s.]*\" align=\"left\" hspace=5 vspace=2\>)/",$buf,$found);
            //<img src="http://www.nevasport.ru/photo/zenit_loko.jpg" alt="Фотография: &quot;Зенит&quot; ждет проверка &quot;Локомотивом&quot;" align="left" hspace="5" vspace="2">
            $result=preg_match_all("/(?<=\<img src=\"http\:\/\/www\.nevasport\.ru\/photo\/)[\w\W]{1,50}(?=\" alt=\"Фотография\:)/",$buf,$found);
            $pic=$found[0][0];
            if($pic!="")$pic="http://www.nevasport.ru/photo/".$pic;
            
            //print "<h2>$name</h2><b>-$pic-</b><br>$txt<hr>";
            
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;

      case(298894): // автогазета

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        $result=preg_match_all("/(?<=\\<p class=\"hnews\"\>\<a href=\")[\w\W]{1,50}(?=\"\>\<img src=\")/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };

        //print "<pre>";
//print_r ($found);
//print "</pre>";
//exit(1);

        $url="http://www.autogazeta.com/";

        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //print $url.$news[$i];
            //exit(1);

            //название
            
            
            $result=preg_match_all("/(?<= width=10 height=13\>\<\/a\>)[\w\W]{1,200}(?=\<\/td\>\<\/tr\>\<\/table\>)/",$buf,$found);
            $name=strip_tags($found[0][0]);

            $result=preg_match_all("/(?<=\<p class=a\>)[\w\W]*(?=\<\/td\>\<td width=2\>\<\/td\>\<td width=2)/",$buf,$found);
            $txt=$found[0][0];
            $txt=strip_tags($txt,"<b><br><i><u><p>");

            $result=preg_match_all("/(?<=src=')[\w\W]{1,100}(?=' align=left\>\<p class=a\>)/",$buf,$found);
            $pic=$found[0][0];
            
            add_news($name,$txt,$url.$pic,"",$news[$i]);

          };
        };

      break;

      case(298899): // асинфо

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        unset($news);

        //$result=preg_match_all("/(?<=\<\!--block begin --\>)[\w\W]{1,2000}(?=\<\!--bloсk end --\>)/",$buf,$found);
        $result=preg_match_all("/(?<=\<td class=\"contentheading\" width=\"100%\"\>)[\w\W]{1,500}(?=\<\/td\>)/",$buf,$found);

/*
print "<pre>";
print_r ($found);
print "</pre>";
*/

        for($i=0;$i<count($found[0]);$i++){

          if(strpos($found[0][$i],"Санкт-Петербург")){

            $news[count($news)]=substr($found[0][$i],strpos($found[0][$i],'<a href="')+9,strpos($found[0][$i],'" class="contentpagetitle">')-strpos($found[0][$i],'<a href="')-10);

            //print "<br><h1>=".$news[count($news)-1]."=</h1>";

          } 

        };

        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            //print "=".$url.$news[$i]."=<br>";
            
            /*
            if(@$fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($news[$i]);

            //print $url.$news[$i];
            //exit(1);

            //название
            
            
            //$result=preg_match_all("/(?<=\s\<b\>)[\w\W]{1,250}(?=\<\/b\>\<br\>\s)/",$buf,$found);
            $result=preg_match_all("/(?<=\<td class=\"contentheading\" width=\"100\%\"\>)[\w\W]{1,250}(?=\<td align=\"right\" width=\"100\%\" class=\"buttonheading\"\>)/",$buf,$found);
            $name=$found[0][0];

            $name=eregi_replace("Санкт-Петербург. ","",$name);

                                        //<div><img src="
            $result=preg_match_all("/(?<=\<div\>\<img src=\")[\w\W]{1,250}(?=\"width=\"120\" height=\"100\" align=\"left\" hspace=\"6\" alt=\"Image\" title=\"Image\" border=\"0\" \/\>)/i",$buf,$found);
            $pix=$found[0][0];                                               //"width="120" height="100" align="left" hspace="6" alt="Image" title="Image" border="0" />

            if($pix==""){
              $result=preg_match_all("/(?<=\<div\>\<img src=\")[\w\W]{1,250}(?=\"width=\"120\" height=\"100\" align=\"left\" hspace=\"6\" alt=\"Image\" title=\"Image\" border=\"0\" \/\>)/i",$buf,$found);
              $pix=$found[0][0];
            };

            $result=preg_match_all("/(?<=\<td valign=\"top\" colspan=\"2\"\>)[\w\W]{1,}(?=\<br \/\>\<\/strong\>\<\/div\>\<div\>\<br \/\>\<div\>\<br \/\>\<\/div\>\<\/div\>)/",$buf,$found);
            $txt=$found[0][0];                                                                   //<br /></strong></div><br /><div><br /></div>

            if($txt==""){
              $result=preg_match_all("/(?<=\<td valign=\"top\" colspan=\"2\"\>)[\w\W]{1,}(?=\<br \/\>\<\/strong\>\<\/div\>\<br \/\>\<div\>\<br \/\>\<\/div\>)/",$buf,$found);
              $txt=$found[0][0];                                                                   //<br /></strong></div><br /><div><br /></div>
            };


            if($txt==""){
              $result=preg_match_all("/(?<=\<td valign=\"top\" colspan=\"2\"\>)[\w\W]{1,}(?=\<\/div\>\<br \/\>\<br \/\>)/",$buf,$found);
              $txt=$found[0][0];                                                                   //<br /></strong></div><br /><div><br /></div>
            };

            /*
            $result=preg_match_all("/(?<=\s\<br\>)([\w\W]*?)(?=\<\/td\>\s)/",$buf,$found);
            $result=preg_match_all("/(?<=\<td width=\"20\"\>&nbsp;\<\/td\>)([\w\W]*?)(?=\<td width=\"20\"\>&nbsp;\<\/td\>)/",$buf,$found_);

            //$txt=$found[0][0]."<br>".$found_[0][0];
            $txt=strip_tags($found[0][0]."<br>".$found_[0][0],"<b><br><p>");
            */
            //$result=preg_match_all("/(?<=\s\<br\>)[\w\s\-.,\<\>]{1,700}(?=\<\/td\>\s)/",$buf,$found);
            /*
            $result=preg_match_all("/(?<=\s\<br\>)[\w\s\-.,\<\>]{1,700}(?=\<\/td\>\s)/",$buf,$found);
            $result=preg_match_all("/(?<=\s\<td colspan=\"2\"\>)[\w\W]*(?=\s\<td width=\"20\"\>&nbsp;\<\/td\>\s)/",$buf,$found_);


            if($found[0][0]!="")$txt=$found[0][0].". ".$found_[0][0];
              else $txt="";

            //$pic="";
            */
            //print "<h2>$name</h2><b>".$txt."</b><br>".$pix."<hr>";
            
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;


      case(298902): //  АБН news-zenit СПОРТ

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        $result=preg_match_all("/(?<=\<a class=lenta href=\"javascript:onClick=further\(')[\w.\?=_&]{1,50}(?=')/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };

        //print "<pre>";
//print_r ($found);
//print "</pre>";
//exit(1);

        $url="http://www.abnews.ru/";

        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //print $url.$news[$i];
            //exit(1);

            //название
            
            
            $result=preg_match_all("/(?<=\<h3\>)[\w\W]{1,200}(?=\<\/h3\>)/",$buf,$found);
            $name=$found[0][0];
            $result=preg_match_all("/(?<=\<\/h3\>)[\w\W]*(?=\<hr color=\"#285513\" width=\"100%\" size=1\>)/",$buf,$found);
            $txt=$found[0][0];

            $pic="";
            
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;

      case(298879): //  муз тв

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        $result=preg_match_all("/(?<=\<td width=\"100%\" valign=\"top\"\>\<a href=\")[\w\W]{1,40}(?=\"\s)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };

        //print "<pre>";
//print_r ($found);
//print "</pre>";
//exit(1);

        //$url="http://www.abnews.ru/unitsimg/";

        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //print $url.$news[$i];
            //exit(1);

            //название
            
            $result=preg_match_all("/(?<=\<div class=\"head3\"\>)[\w\W]{1,200}(?=\<\/div\>)/",$buf,$found);
            $name=$found[0][0];
            
            $result=preg_match_all("/(?<=\<\/div\>)[\w\W]*(?=\<a href=\"pro.php\" class=\"contentbold\"\>)/",$buf,$found);
            $txt=$found[0][0];
            
            $result=preg_match_all("/(?<=\<br\>\<img src=\"unitsimg\/)[\w\W]{1,20}(?=\"\s)/",$buf,$found);
            $pic="unitsimg/".$found[0][0];
            
            //print "<h2>$name</h2>$txt<hr>$pic<hr><hr>";
            
            add_news($name,$txt,$url.$pic,"",$news[$i]);

          };
        };

      break;
      
      case(298893): //  PeterOut

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        $result=preg_match_all("/(?<=\<a href=\'\/)[\w\W]{1,100}(?='\>\<span class=newstitle\>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };

        //print "<pre>";
//print_r ($found);
//print "</pre>";
//exit(1);

        //$url="http://www.abnews.ru/unitsimg/";

        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //print $url.$news[$i];
            //exit(1);

            //название
            
            $result=preg_match_all("/(?<=\<font class=zagolovok\>)[\w\W]{1,200}(?=\<\/font\>)/",$buf,$found);
            $name=$found[0][0];

            $result=preg_match_all("/(?<=\<\/font\>\<p align=\"justify\"\>)([\w\W]*?)(?=\<\/font\>\<\/td\>)/",$buf,$found);
            //$result=preg_match_all("/(?<=\<\/font\>\<p align=\"justify\"\>)[\w\W]*(?=\<br\>\<\/p\>\<br\>\<br\>\<\/font\>\<\/td\>)/",$buf,$found);
            $txt=$found[0][0];

            $result=preg_match_all("/(?<=\<br\>\<img src='\/)[\w\W]{1,30}(?='\s)/",$buf,$found);
            $pic=$found[0][0];
            
            //print "<h2>$name</h2>$txt<hr>$pic<hr><hr>";
            
            add_news($name,$txt,$url.$pic,"",$news[$i]);

          };
        };

      break;

      case(298883): //  Комсомольская правда политика
      case(298949): //  Комсомольская правда экономика
      case(298950): //  Комсомольская правда lifestyle
      case(298951): //  Комсомольская правда спорт
      case(539404): // криминал

        //print "=$pictures=";
        //exit(1);

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        $result=preg_match_all("/(?<=\<a href=\"\/)[\w\W]{1,200}(?=\"\>\<img src=\"\/kkp.files\/more1.gif)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          //print "<br>".$found[0][$i];
        };

        $url="http://www.spb.kp.ru/";

        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */


            //print "<br>".$url.$news[$i];

            $buf=download($url.$news[$i]);

            //название
            
            $result=preg_match_all("/(?<=\<div class=\"article-head\"\>)[\w\W]{1,250}(?=\<title\>)/",$buf,$found);
            $name=$found[0][0];

            //$result=preg_match_all("/(?<=\<\/td\>\<\/tr\>\<\/table\> \<\/td\>\<\/tr\> \<\/table\> \<\/td\>\<\/tr\>\<\/table\>)[\w\W]*(?=\<p\> \<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\>)/",$buf,$found);
            //$result=preg_match_all("/(?<=\<\/title\>)([\w\W]*?)(?=\<p\> \<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\>)/",$buf,$found);
            $result=preg_match_all("/(?<=\<\/title\>)([\w\W]*?)(?=\<p\> \<br clear=all\> \<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\>)/",$buf,$found);
            $txt=strip_tags($found[0][0],"<b><i><br><p>");

            //$result=preg_match_all("/(?<=\<table cellpadding=\"0\" cellspacing=\"2\" border=\"0\"\> \<tr\>\<td\>\<img src=\"\/)[\w\W]{1,100}(?=\"  border=\"0\" alt=)/",$buf,$found);
            // <tr><td><img src="/readyimages/113972.jpg"  border="0" alt=`Члены Уставного суда подумали 
//и решили 
//не торопиться.` ></a
            $result=preg_match_all("/(?<=\<img src=\"\/readyimages\/)[\w\W]{1,100}(?=\"  border=\"0\" alt=\`)/",$buf,$found);
            $pic=$found[0][0];
            if($pic!="")$pic="http://www.spb.kp.ru/readyimages/".$pic;
            
            //print "<h2>$name</h2>$txt<hr>$pic<hr><hr>";
            
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;


      case(298905): // СПб Ведомости lifestyle
      case(298904): // СПб Ведомости спорт
      case(298892): //  СПб Ведомости город
      case(534959): // СПб Ведомости (модный дом - культура)
      case(534958): // СПб Ведомости (модный дом - культура)
      case(534960): // CПб Ведомости (наследие - город)

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        //<br><a class="Statya-zag-prew" href="?id=5356&folder=167"><У нас боеспособный коллектив>, &gt;&gt;</a><br
        //<br><a class="Statya-zag-prew" href="?id=5358&folder=167">Дебют Александра Поветкина &gt;&gt;</a><br>
        //$result=preg_match_all("/(?<=\<span class=middle\>\<b\>\<a href=\"\/)[\w\W]{1,100}(?=\"\>)/",$buf,$found);
        $result=preg_match_all("/(?<=\<a class=\"Statya\-zag\-prew\" href=\")[\w\W]{1,30}(?=\"\>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          //print "<br>".$found[0][$i];;
        };

        $url="http://www.spbvedomosti.ru/document/";
        
        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //название
            
            //$result=preg_match_all("/(?<=\<p class=big\>)[\w\W]{1,250}(?=\<\/p\>)/",$buf,$found);
            $result=preg_match_all("/(?<=class=\"Statya\-zag\"\>)[\w\W]{1,250}(?=\<\/span\>\<br\>\<span)/",$buf,$found);
            $name=$found[0][0];
            if(strpos(" $name ","Диалог:")>0)$name="";

            //$result=preg_match_all("/(?<=\<p\>)[\w\W]*(?=\<\!----------------- text\/end -------------------\>)/",$buf,$found);
            $result=preg_match_all("/(?<=\<\/span\>\<br\>\<span class=\"Statya\-text\-info\"\>)[\w\W]*(?=\<\/td\>\<\/tr\>\<tr\>\<td align=\"right\"\>)/",$buf,$found);
            $txt=$found[0][0];
            $txt=strip_tags($found[0][0],"<b><p><i><u><strong><br>");

            if($txt==""){
              $result=preg_match_all("/(?<=align=\"justify\" class=\"Statya\-text\-info2\"\>)[\w\W]*(?=\<\/td\>\<\/tr\>\<tr\>\<td align=\"right\"\>)/",$buf,$found);
              $txt=$found[0][0];
              $txt=strip_tags($found[0][0],"<b><p><i><u><strong><br>");
            };

            //$result=preg_match_all("/(?<=\<p\>\<img src=\")[\w\W]{1,70}(?=\" width=\d)/",$buf,$found);
            //<IMG height=335 hspace=7 src="images\5343-0" width=200 align=left vspace=7 border=1>
            $result=preg_match_all("/(?<= hspace=7 src\=\"images)[\w\W]{1,20}(?=\")/",$buf,$found);
            $pic="";
            if($found[0][0]!="")$pic="http://www.spbvedomosti.ru/document/images".$found[0][0];
            
            //print "<h2>$name</h2>$txt<hr>$pic<hr><hr>";
            //print "<br>--$pic--<br>";
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;

      
      case(298889): //авто
      case(298888): //бюджет
      case(298887): //образование
      case(298886): //топливо
      case(298885): // DP.ru Безопасность (город)
      case(298884): //DP.ru Конфликты в Петербурге   (город)
      case(298897): // медиа
      case(298896): // DP.ru реклама
      case(298891): // DP.ru Строительство (недвижимость)
      case(298900): // DP.ru Недвижимость 

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

//print $buf;
//exit(1);

        //$result=preg_match_all("/(?<=\<a href=\"\/)[\w\W]{1,100}(?=\" class=gray-10\>)/",$buf,$found);
        $result=preg_match_all("/(?<=\<td\>\<a href=\"http\:\/\/www\.dp\.ru\/materials\/)[\w\W]{1,50}(?=\" class=\"title_news\"\>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };

        //$url="http://www.dp.ru/";
        $url="";
        /*
        print "<pre>";

        print_r($found) ;

        print "</pre>";
        */
        //exit(1);
        
        $url="http://www.dp.ru/materials/";

        for($i=0;$i<count($news);$i++){

//print "=$news[$i]=";

          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //название
            //$result=preg_match_all("/(?<=\<span class=\"article\"\>\<FONT SIZE=\"2\"\>\<B\>)[\w\W]{1,250}(?=\<\/B\>\<\/span\>\<P\>)/",$buf,$found);
            //$result=preg_match_all("/(?<=\<H1\>)[\w\W]{1,250}(?=\<\/H1\>)/",$buf,$found);
            $result=preg_match_all("/(?<=\<h1 class=\"tH1\" style=\"line\-height\:21px\; margin\-top\:3px\"\>)[\w\W]{1,250}(?=\<\/H1\>)/i",$buf,$found);
            $name=$found[0][0];
            //"Деловой Петербург":
            //$name=eregi_replace('"Деловой Петербург":','',$name);

            //$result=preg_match_all("/(?<=<\/H1>)[\w\W]*(?=\<div style=\"font-size: 10px;\")/",$buf,$found);
            $result=preg_match_all("/(?<=\<p class=\"atext\"\>)[\w\W]*(?=\<br\>\<img src=\"http\:\/\/www\.dp\.ru\/dpru\/siteimg\/break\.gif\" width=\"1\" height=\"8\"\>\<br\>)/",$buf,$found);
            $txt=$found[0][0];

            $txt=strip_tags($txt,"<b><p><i><u><strong><br>");

//print $txt;
//exit(1);
            
            //$txt=substr($txt,strpos($txt,"</table>"),strlen($txt));
            //$txt=preg_replace("/(?<=\<div\>)[\w\W]{1,2500}(?=\<\/div\>)/","",$txt);

            /*
            $t=$found[0][0];
            while(strpos($t,'<table')){
              $t=substr($t,0,strpos($t,'<table')).substr($t,strpos($t,'</table>')+8,strlen($t)-strpos($t,'</table>')-8);
            };
            $txt=$t;
            //$txt=eregi_replace('<p align="left">','',$txt);
            $txt=eregi_replace('<p align="left">','. ',$txt);
            $txt=eregi_replace('\.\. ','. ',$txt);
            $txt=eregi_replace('. . ','. ',$txt);
            $txt=eregi_replace('<b>','<h5>',$txt);
            $txt=eregi_replace('</b>','</h5>',$txt);
            $txt=eregi_replace('\. \.','. ',$txt);
            $txt=eregi_replace('\.  \.','. ',$txt);
            */
            
            //$result=preg_match_all("/(?<=\<IMG SRC=\"\/)[\w\W]{1,50}(?=\" border=\"0\" align=\"LEFT\")/",$buf,$found);
            //$result=preg_match_all("/(?<=\<td\>\<img src=\")[\w\W]{1,70}(?=\" border=0 width=\")/",$buf,$found);
            $result=preg_match_all("/(?<=\s\s\<td colspan=\"2\" valign=\"top\"\>\<img src=\")[\w\W]{1,70}(?=\"\>\<\/td\>)/",$buf,$found);
            $pic=$found[0][0];
            
            //print "<h2>$name</h2>$txt<hr>$pic<hr><hr>";
//exit(1);
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;


      case(298890): // интерфакс

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        $result=preg_match_all("/(?<=\<li\>\<a href =\"\/)[\w\W]{1,200}(?=\"\>\w)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };

        $url="http://www.interfax.ru/";


        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //название
            $result=preg_match_all("/(?<=\<h3 class=\"title_of_newsarticle\"\>)[\w\W]{1,250}(?=\<\/h3\>)/",$buf,$found);
            $name=$found[0][0];
            
            $result=preg_match_all("/(?<=\<\/h3\>)[\w\W]*(?=\<\/td\>\<td width=\"40%\" valign=\"top\" class=\"right_column\"\>)/",$buf,$found);
            $txt=$found[0][0];


            $pic="";
            
            
            //print "<h2>$name</h2>$txt<hr>$pic<hr><hr>";
            
            add_news($name,$txt,$url.$pic,"",$news[$i]);

          };
        };

      break;

      
      case(298901): // закс ру

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        $result=preg_match_all("/(?<=\<div class=\"text1\"\>)[\w\W]{1,100}(?=\"\>)/",$buf,$found);

        for($i=0;$i<count($found[0]);$i++)$found[0][$i]=substr($found[0][$i],strpos($found[0][$i],'"')+2,strlen($found[0][$i])-strpos($found[0][$i],'"'));


        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          //print "<br>".$found[0][$i];

        };

        $url="http://www.zaks.ru/";


        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //название
            $result=preg_match_all("/(?<=\<div class=\"text1\"\>)[\w\W]{1,350}(?=\<\/div\>)/",$buf,$found);
            $name=$found[0][0];
            /*
            if($name==""){
              $result=preg_match_all("/(?<=\<div class=\"text1\"\>)[\w\W]{1,250}(?=\<\/div\>)/",$buf,$found);
              $name=$found[0][0];
            };
            */
            //$result=preg_match_all("/(?<=\<div class=\"text1\" align=\"justify\"\>)([\w\W]*?)(?=\<\/div\>\s+\<table width=\"100%\" height=\"23\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\"\>\s+<tr bgcolor=\"#CA8C14\">\s+)/",$buf,$found);
            //$result=preg_match_all("/(?<=\<div class=\"text1\" align=\"justify\"\>)([\w\W]*?)(?=\<\/div\>\s+\<table width=\"100%\" height=\"23\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\"\>\s+<tr bgcolor=\"#CA8C14\">\s+\<td align=\"right\" class=\"text2\"\>\s+Упоминаемые персоны)/",$buf,$found);
            $result=preg_match_all("/(?<=\<div class=\"text1\" align=\"justify\"\>)([\w\W]*?)(?=\s+Упоминаемые персоны\s+)/",$buf,$found);
            $txt=$found[0][0];


            $pic="";
            
            
            //print "<h2>$name</h2>$txt<hr>$pic<hr><hr>";
            
            add_news($name,$txt,$url.$pic,"",$news[$i]);

          };
        };

      break;


      case(298903): // авторевю

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        $result=preg_match_all("/(?<=\"\/)[\w\W]{1,70}(?=\" class=\"l1b\"\>)/",$buf,$found);

        for($i=0;$i<count($found[0]);$i++)$found[0][$i]=substr($found[0][$i],strpos($found[0][$i],'"')+2,strlen($found[0][$i])-strpos($found[0][$i],'"'));


        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };


        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            //print "$url.$news[$i]<br>";
            
            /*
            if($fp=fopen($url."cg".$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url."cg".$news[$i]);

            //название
            $result=preg_match_all("/(?<=\s\<td class=\"title\"\>)[\w\W]{1,250}(?=\<\/td\>\s)/",$buf,$found);
            $name=$found[0][0];

            $result=preg_match_all("/(?<=\<td class=\"quota\"\>)[\w\W]*(?=\<\!--tr\>\s)/",$buf,$found);
            $found[0][0]=strip_tags($found[0][0],"<br>");
            $txt=$found[0][0];
            
            $result=preg_match_all("/(?<=\<a href=\"\/new_site\/news\/)[\w\W]{1,50}(?=\"\>\<img src=\"\/new_site\/news\/)/",$buf,$found);
            $pic=$found[0][0];
            if($pic!="")$pic=$url."new_site/news/".$pic;
            
            //print "<h2>$name</h2>$txt<hr>$pic<hr><hr>";

            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

        case(298875): // автомаркет

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        $result=preg_match_all("/(?<=\<A href=\"\/)[\w\W]{1,100}(?=\" style\=\"text)/",$buf,$found);

        for($i=0;$i<count($found[0]);$i++)$found[0][$i]=substr($found[0][$i],strpos($found[0][$i],'"')+2,strlen($found[0][$i])-strpos($found[0][$i],'"'));


        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          //print "<br>".$found[0][$i];
        };

        $url="http://www.avtomarket.ru/";

        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            //print "$url.$news[$i]<br>";
            /*
            if($fp=fopen($url."cg".$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url."sc".$news[$i]);

            //название
            
            //$result=preg_match_all("/(?<=\<IMG src=\"..\/..\/..\/imagesk\/curs1.gif\" width=\"13\" height=\"13\"\>&nbsp;\<b\>)[\w\W]{1,250}(?=\<\/b\>\<\/TD\>)/",$buf,$found);

            $result=preg_match_all("/(?<=\<IMG src=\"\/imagesk\/curs1\.gif\" width=\"13\" height=\"13\"\>&nbsp;\<b\>)[\w\W]{1,250}(?=\<\/b\>\<\/TD\>)/",$buf,$found);
            $name=$found[0][0];
            
            //$result=preg_match_all("/(?<=\<P style=\"text-align: justify\"\>)[\w\W]*(?=\<\/P\>)/",$buf,$found);
            $result=preg_match_all("/(?<=\<P style=\"text\-align\: justify\"\>)[\w\W]*(?=\<\/P\>)/i",$buf,$found);
            $txt=$found[0][0];
            
            $pic="";
            
            //print "<h2>$name</h2>$txt<hr>$pic<hr><hr>";

            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;
      case(298906): // Restate

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        $result=preg_match_all("/(?<=\<a href=)[\w\W]{1,100}(?= class=linksmall\>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };

        $url="http://www.restate.ru/";

        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            //print "$url.$news[$i]<br>";
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //название
            
            $result=preg_match_all("/(?<=\<h3\>)[\w\W]{1,250}(?=\<\/h3\>)/",$buf,$found);
            $name=strip_tags($found[0][0]);

            $result=preg_match_all("/(?<=\<\/h3\>)([\w\W]+?)(?=\<div )/",$buf,$found);
            $txt=$found[0][0];
            
            $pic="";
            
            //print "<h2>$name</h2>$txt<hr>$pic<hr><hr>";

            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;


      case(298908): // SportSPB

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);


        //$result=preg_match_all("/(?<=\<a href=)[\w\W]{1,100}(?= class=linksmall\>)/",$buf,$found);
        //$result=preg_match_all("/(?<=index.php\?news=)(\d{1,6}?)(?=\")/",$buf,$found);
        $result=preg_match_all("/(?<=index.php\?news=)[\d]{1,6}/",$buf,$found);

        /*
        print "<pre>";
        print_r ($found);
        print "</pre>";
        */

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };

        $url="http://www.sportspb.ru/index.php?news=";

        //print "=".count($news)."=";
        //print "=".strlen($buf)."=";

        //print $buf;
        //exit(1);
        /*
        print "<pre>";
        print_r ($news);
        print "</pre>";
        exit(1);
        */

        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            //print "$url$news[$i]<br>";
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //название
            
            
            $result=preg_match_all("/(?<=\<td\>\<font color=\"#000080\"\>\<b\>)([\w\W]{1,250}?)(?=\<\/b\>\<\/font\>\<\/td\>)/",$buf,$found);
            $name=strip_tags($found[0][0]);

            //картинка
            $result=preg_match_all("/(?<=http:\/\/www.sportspb.ru\/pic\/news\/)([\w\W]{0,50}?)(?=\")/",$buf,$found);
            $pic=$found[0][0];
            if($pic!="")$pic="http://www.sportspb.ru/pic/news/".$pic;

            $result=preg_match_all("/(?<=\<div align=\"justify\"\>)([\w\W]*?)(?=\<\/div\>)/",$buf,$found);
            $txt=strip_tags($found[0][0],"<i><b><u><br>");

            //$result=preg_match_all("/(?<=\<h3\>)[\w\W]{1,250}(?=\<\/h3\>)/",$buf,$found);
            //$name=strip_tags($found[0][0]);

            //$result=preg_match_all("/(?<=\<\/h3\>)([\w\W]+?)(?=\<div )/",$buf,$found);
            //$txt=$found[0][0];
            
            //$pic="";
            
            //print "<h2>$name</h2>$txt<hr>$pic<hr><hr>";

            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;



      case(298909): // СпортПресс

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);


        $result=preg_match_all("/(?<=\<a class=oglavl href=\"\/)([\w\W]{1,100}?)(?=\"\s)/",$buf,$found);


        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };


        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //название
            

            $result=preg_match_all("/(?<=\<h1\>)([\w\W]{1,250}?)(?=\<\/h1\>)/",$buf,$found);
            $name=strip_tags($found[0][0]);

            $result=preg_match_all("/(?<=\<TD class\=text\_stat\>)([\w\W]*?)(?=\<\/TD\>)/",$buf,$found);
            $txt=strip_tags($found[0][0],"<p><i><b><u><br>");
            
            $result=preg_match_all("/(?<=src=\"\/)([\w\W]{1,250}?)(?=\" border=1 style=\"border-color:#888888\"\>)/",$buf,$found);
            $pic=$url.$found[0][0];

            //print "<h2>$name</h2>$txt<hr>$pic<hr><br><br>";

            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;


      case(298910): // Новости Недвижимости

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        //$result=preg_match_all("/(?<=\<a class=oglavl href=\"\/)([\w\W]{1,100}?)(?=\"\s)/",$buf,$found);
        $result=preg_match_all("/(?<=\<a href=\")[\w\W]{1,120}(?=\"\>Подробнее\&\#133;\<\/a\>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };


        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";

            //print "<br>".$news[$i];
            
            /*
            if($fp=fopen($news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($news[$i]);

            //print strlen($buf);
            
            //название
            

            //$result=preg_match_all("/(?<=\<\/nobr\>\<\/a\>\<\/p\>)([\w\W]{1,250}?)(?=\<\/h1\>)/",$buf,$found);
            $result=preg_match_all("/(?<=\<h1\>)([\w\W]{1,250}?)(?=\<\/h1\>)/",$buf,$found);
            $name=$found[0][0];

            //$result=preg_match_all("/(?<=\<\/nobr\>\<\/a\>\<\/p\>)([\w\W]*?)(?=\<\/div\>)/",$buf,$found);
            //$result=preg_match_all("/\<\/h1\>[\w\W]*/",$found[0][0],$found);

            $result=preg_match_all("/(?<=\<P\>\<\/P\>)([\w\W]*?)(?=\<p\>Регион\: \<b\>Санкт\-Петербург\<\/b\>\.)/",$buf,$found);

            $txt=strip_tags($found[0][0],"<p><i><b><u><br>");
            
            //$result=preg_match_all("/(?<=src=\"\/)([\w\W]{1,250}?)(?=\" border=1 style=\"border-color:#888888\"\>)/",$buf,$found);
            //$pic=$url.$found[0][0];

            //print "<h2>$name</h2>$txt<hr>$pic<hr><br><br>";

            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,"","",$news[$i]);

          };
        };

      break;


      case(298911): // 5-й канал

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        
        //$result=preg_match_all("/(?<=\<a href=\")([\w\W]{1,50}?)(?=\"\>подробнее\<\/a\>\<\/td\>)/",$buf,$found);
        $result=preg_match_all("/(?<=\<b\>\|&nbsp;\<a href=\")([\w\W]{1,50}?)(?=\"\>читать\<\/a\>\<\/b\>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };

        //$url="http://www.spbtv.ru/";
        $url="http://www.5-tv.ru/";

        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";

            //print "<br>".$news[$i];
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //print strlen($buf);
            
            //название
            

            $result=preg_match_all("/(?<=\<span class=\"news_title\"\>)([\w\W]{1,250}?)(?=\<\/span\>)/",$buf,$found);
            $name=$found[0][0];

            //$result=preg_match_all("/(?<=\<td colspan=\"2\"\>)([\w\W]*?)(?=\<\/td\>)/",$buf,$found);
            
            $result=preg_match_all("/(?<=\<\/span\>\s\s\s\s\s\s\<br\>\<br\>\s)([\w\W]*?)(?=\<td align=\"right\"\>\<a href=\"\?cat=news\"\>все новости\<\/a\>\<\/td\>)/",$buf,$found);
            $txt=$found[0][0];
            $txt=str_replace("</td>","",$txt);
            $txt=str_replace("</tr>","",$txt);
            $txt=str_replace('<tr valign="top">',"",$txt);
/*
    </td>
</tr>
<tr valign="top">
*/
            
//<td><a href="?cat=news&key=5885"><img src="file/11695/img11695.gif" class="inText" align="left" width="112" height="83" alt="Семьсот лучших танцевальных дуэтов будут соревноваться в Петербурге" border="0"></a>            
            $result=preg_match_all("/(?<=\"\>\<img src=\")([\w\W]{1,70}?)(?=\" class=\"inText\" align=\"left\" width=\")/",$buf,$found);
            $pic="";
            //if($found[0][0]!="" && $found[0][0][0]!="0")$pic=$url."img/".$found[0][0];
            if($found[0][0]!="" && $found[0][0][0]!="0")$pic=$url.$found[0][0];


            //print "<h2>$name</h2>$txt<hr>$pic<hr><br><br>";
//exit(1);
            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;


      case(298912): // Газета ру

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        /*
        if($fp=fopen($url,"rb")){
          $buf="";
          while (!feof($fp)) {
            $buf .= fread($fp, $max_file_size_url);
          }
          fclose($fp); 
        };
        */

        $buf=download($url);

        //print "-".strlen($buf)."-";
        
        //$result=preg_match_all("/(?<=\<a href=\")([\w\W]{1,50}?)(?=\"\>подробнее\<\/a\>\<\/td\>)/",$buf,$found);
        /*
        $result=preg_match_all("/(?<=\<a href=\")([\w\W]{1,100}?)(?=\"\>\<img src=\/i\/24db.gif width=18 height=12 border=0 alt=\"подробнее\"\>\<\/a\>\<br\>)/",$buf,$found);
        $result=preg_match_all("/(?<=\<a href=\")([\w\W]{1,100}?)(?=\"\>\<img src=\/i\/24lb.gif width=18 height=12 border=0 alt=\"подробнее\"\>\<\/a\>\<br\>)/",$buf,$found1);

        $found[0]=$found[0]+$found1[0];
        */

        $result=preg_match_all("/(?<=\<a href=\")([\w\W]{1,100}?)(?=\"\>\<img src=\"\/i2\/119\.gif\" class=gzt_119 alt=\"подробнее\.\.\.\"\>\<\/a\>\<br\>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
        };

        /*
        print "<pre>";
        print_r($news);
        print "</pre>";
        */
        
        $url="http://www.gazeta.ru";
        
        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";

            //print "<br>".$news[$i];
            
            /*
            if($fp=fopen($url.$news[$i],"rb")){
              $buf="";
              while (!feof($fp)) {
                $buf .= fread($fp, $max_file_size_url);
              }
            
              fclose($fp); 
            };
            */

            $buf=download($url.$news[$i]);

            //print strlen($buf);
            
            //название
            
            /*
            $result=preg_match_all("/(?<=\<span  class=\"title\"\>)([\w\W]{1,250}?)(?=\<\/span\>)/",$buf,$found);
            $name=$found[0][0];
            $result=preg_match_all("/(?<=\<td colspan=\"2\"\>)([\w\W]*?)(?=\<\/td\>)/",$buf,$found);
            $txt=$found[0][0];
            $result=preg_match_all("/(?<=\"\>\<img src=\"img\/)([\w\W]{1,150}?)(?=\" align=\"left\" alt=\")/",$buf,$found);
            $pic="";
            if($found[0][0]!="" && $found[0][0][0]!="0")$pic=$url."img/".$found[0][0];
            */

            //$result=preg_match_all("/(?<=\<h1\>)([\w\W]{1,250}?)(?=\<\/h1\>)/",$buf,$found);
            $result=preg_match_all("/(?<=\<h1 style=\"width\: 80\%\" id=mb06\>)([\w\W]{1,250}?)(?=\<\/h1\>)/",$buf,$found);
            $name=$found[0][0];

            //$result=preg_match_all("/(?<=\<img src=\")([^\":]{1,250}?)(?=\")/",$buf,$found);
            $result=preg_match_all("/(?<=\<img src=\")([^\":]{1,250}?)(?=\" alt=\"\" name=\"gallery_image\"\>\<br\>)/",$buf,$found);
            $pic=$url.$found[0][0];

            //<p class=lead><p class=h11>
            //$result=preg_match_all("/(?<=\<p class=lead\>)([\w\W]*?)(?=\<p class=h11\>)/",$buf,$found);
            $result=preg_match_all("/(?<=\<p class=gzt_intro id=mb09\>)([\w\W]*?)(?=\<p class=gzt_date\>)/",$buf,$found);
            $found[0][0]=strip_tags($found[0][0],"<br>");
            $txt=$found[0][0];

            //print "<h2>$name(".$url.$news[$i].")</h2>$txt<hr>$pic<hr><br><br>";

            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;


      case(298913): // Бюллетень Недвижимости bn.bsn.ru

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        $buf=download($url);


        //$result=preg_match_all("/(?<=\<a href\=\'newsshow1\.shtml\?)([\w\W]{1,100}?)(?=\' class\=\'small\'\>)/",$buf,$found);
        $result=preg_match_all("/(?<=\<a href\=\'newsshow1\.shtml\?)([\w\W]{1,100}?)(?=\' class\=\'blue\'\>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          //print "<br>".$found[0][$i];
        };

        /*
        print "<pre>";
        print_r($news);
        print "</pre>";
        //http://bn.bsn.ru/newsshow1.shtml?date=25.10.2004&id_sn=1566
        */
        
        $url="http://bn.bsn.ru/newsshow1.shtml?";
        
        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";

            //print "<br>".$news[$i];
            

            $buf=download($url.$news[$i]);

            //print strlen($buf);
            
            //название
            
            $result=preg_match_all("/(?<=\<br\>\<center\>\<font size\=\'4\'\>\<b\>)([\w\W]{1,200}?)(?=\<\/font\>\<\/b\>\<\/center\>)/",$buf,$found);
            $name=strip_tags($found[0][0]);

            $result=preg_match_all("/(?<=\s\<\/font\>\<\/b\>\<\/center\>)([\w\W]{1,}?)(?=\<\/td\>\<\/tr\>\<\/table\>)/",$buf,$found);
            //$found[0][0]=strip_tags($found[0][0],"<br>");
            $txt=$found[0][0];

            //print "<h2>$name</h2>$txt<hr>$pic<hr><br><br>";
            //
            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);

          };
        };

      break;

      
      case(298922): // Афиша.ру дети
      case(298921): // Афиша.ру шопинг
      case(298920): // Афиша.ру фитнесс
      case(298919): // Афиша.ру рестораны
      case(298918): // Афиша.ру выставки
      case(298917): // Афиша.ру театры
      case(298916): // Афиша.ру клубы
      case(298915): // Афиша.ру концерты
      case(298914): // Афиша.ру кино

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        $buf=download($url);

//print "!";
        //print strlen($buf);
        //print $buf;

        //$result=preg_match_all("/(?<=\<a href=\")([\w\W]{1,100}?)(?=\"\>[читать\&nbsp\;дальше]{10,50}\<\/a\>)/",$buf,$found);
        $result=preg_match_all("/(?<=href=\")([\w\W]{1,100}?)(?=\"\>\&\#133\;\<\/a\>\<)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          //print $found[0][$i]."<br>";
        };

        unset($found1);
        unset($news1);
        //src="http://pics.afisha.ru/./plan-images/2005-06-10/1118427170f.gif" alt="
        $result=preg_match_all("/(?<=src=\"http\:\/\/pics\.afisha\.ru\/\.\/)([\w\W]{1,100}?)(?=\")/",$buf,$found1);
        for($i=0;$i<count($found1[0]);$i++){
          $news1[count($news1)]="http://pics.afisha.ru/./".$found1[0][$i];
          //print $found1[0][$i]."<br>";
        };

        //exit(1);

        if(!strpos($news[0],"ttp://"))$url="http://spb.afisha.ru/";
          else $url="";

        
        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";

            //print "<br>".$news[$i];
            

            $buf=download($url.$news[$i]);

            //print "<br>".$url.$news[$i]."(".strlen($buf).")";

            //print $news[$i]."(".strlen($buf).")<br>";

            
            //$result=preg_match_all("/(?<= class\=\"new\-title\"\>)([\w\W]{1,200}?)(?=\<\/)/",$buf,$found);
            $result=preg_match_all("/(?<=\<h1\>)([\w\W]{1,200}?)(?=\<\/h1\>)/",$buf,$found);
            $name=strip_tags($found[0][0]);
            //$result=preg_match_all("/(?<=\" hspace\=\"0\" border\=\"0\" align\=\"left\" src\=\")([\w\W]{1,100}?)(?=\"\>\<\/td\>\<td\>\<img height\=\"1\" width\=\"10\" src\=\")/",$buf,$found);
            //$result=preg_match_all("/(?<=\" hspace\=\"0\" border\=\"0\" align\=\"left\" src\=\")([\w\W]{1,100}?)(?=\"\>\<\/td\>\<td\>\<img height\=\"1\" width\=\"10\" src\=\")/",$buf,$found);
            //$pic=strip_tags($found[0][0]);
            $pic=$news1[$i];

            //</div><p style="margin: 0px; padding: 0px; padding-bottom: 15px;" class="large-text">
            //$result=preg_match_all("/(?<=\<td class\=\"review\"\>)([\w\W]{1,}?)(?=\<tr\>)/",$buf,$found);
            
            $result=preg_match_all("/(?<=\<\/div\>\<p style=\"margin\: 0px\; padding\: 0px\; padding\-bottom\: 15px\;\" class=\"large\-text\"\>)([\w\W]{1,}?)(?=\<\/div\>\<div style=\"width\: 240px\; height\: )/",$buf,$found);
            $txt=$found[0][0];

            if($txt==""){
              $result=preg_match_all("/(?<=\<div style=\"margin\-top\: 15px;\"\>\<p style=\"margin\: 0px; padding\: 0px; padding\-bottom\: 15px;\" class=\"large\-text\"\>)([\w\W]{1,}?)(?=\<\/p\>\<p style=\"margin\: 0px; padding\: 0px;\" class=\"tiny\-text\"\>)/",$buf,$found);
              $txt=$found[0][0];
            };

//print "<h2>-$name-</h2>$txt<hr>$pic<hr><br><br>";

            //
            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);
//exit(1);
          };
        };

      break;


      
      case(298923): // TimeOut кино LIFESTYLE
      case(298924): // TimeOut театр LIFESTYLE
      case(298925): // TimeOut клубы LIFESTYLE
      case(298926): // TimeOut музыка LIFESTYLE
      case(298927): // TimeOut музыка классика LIFESTYLE
      case(298928): // TimeOut выставки LIFESTYLE
      case(298929): // TimeOut тратить LIFESTYLE
      case(298930): // TimeOut тратить рестораны LIFESTYLE
      case(298931): // TimeOut дети LIFESTYLE
      case(298932): // TimeOut спорт LIFESTYLE
      case(298907): // TimeOut геи LIFESTYLE

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        $buf=download($url);


        $result=preg_match_all("/(?<=\>\&nbsp\;\&nbsp\;\<a href\=\'\/)([\w\W]{1,100}?)(?=\' class\=c13\>узнай больше\<\/a\>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          //print $found[0][$i]."<br>";
        };


        $url="http://www.timeout.ru/";
        
        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";

            //print "<br>".$news[$i];
            

            $buf=download($url.$news[$i]);

            //print $news[$i]."(".strlen($buf).")<br>";

            
            $result=preg_match_all("/(?<=\<h1\>)([\w\W]{1,100}?)(?=\<\/a\>\<\/h1\>)/",$buf,$found);
            $name=$found[0][0];

            $result=preg_match_all("/(?<=\<table cellpadding\=0 cellspacing\=0 border\=0 align\=left\>\<tr\>\<td\>\<img src\=\'\/)([\w\W]{1,100}?)(?=\' alt\=\'\' border\=1\>\<\/td\>\<td\>\&nbsp\;\<\/td\>\<\/tr\>\<\/table\>\<h1\>)/",$buf,$found);
            $pic=$found[0][0];
            if($pic!="")$pic=$url.$pic;

            $result=preg_match_all("/(?<=\<\/a>\<\/h1\>)([\w\W]{1,}?)(?=\<br clear\=all\>\<b\>\<br\>\<a name\=talk\>)/i",$buf,$found);

            $txt=$found[0][0];

            //print "<h2>$name</h2>$txt<hr>$pic<hr><br><br>";
            //
            //print $url."new_site/news/".$pic."<br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);
            //exit(1);
          };
        };

      break;


      case(539876): // невское время

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        $buf=download($url);


        //$result=preg_match_all("/(?<=\>\&nbsp\;\&nbsp\;\<a href\=\'\/)([\w\W]{1,100}?)(?=\' class\=c13\>узнай больше\<\/a\>)/",$buf,$found);
        $result=preg_match_all("/(?<=\<a class=black href=\/)[\W\w]{1,40}(?=>)/",$buf,$found);

        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          //print $found[0][$i]."<br>";
        };


        //exit(1);

        $url="http://www.nv.vspb.ru/";
        
        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";

            //print "<br>".$news[$i];
            

            $buf=download($url.$news[$i]);

            //print $news[$i]."(".strlen($buf).")<br>";

            
            $result=preg_match_all("/(?<=\<div class=h1 align=right\>)[\W\w]{1,250}(?=\<\/div\>\s)/",$buf,$found);
            $name=$found[0][0];

            $result=preg_match_all("/(?<=\<div align=justify\>\<b\>)[\W\w]{1,}(?=\<br\>\<div align=right\> \<b\>)/",$buf,$found);

            $txt=$found[0][0];

            //print "<h2>$name</h2>$txt<hr>$pic<hr><br><br>";

            //exit(1);
            
            add_news($name,$txt,$pic,"",$news[$i]);
            //exit(1);
          };
        };

      break;



      case(539884): // смена

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        $buf=download($url);


        $result=preg_match_all("/(?<=\<h4\>\<a href=\")[\W\w]{1,50}(?=\" title=\")/",$buf,$found);
        
        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          //print $found[0][$i]."<br>";
        };


        //$url="http://www.nv.vspb.ru/";
        $url="";
        
        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";

            //print "<br>".$news[$i];
            

            $buf=download($url.$news[$i]);

            $result=preg_match_all("/(?<=\<h3\>)[\W\w]{1,250}(?=\<\/h3\>)/",$buf,$found);
            $name=$found[0][0];

            $result=preg_match_all("/(?<=\<p align=\'justify\'\>)[\W\w]{1,}(?=\<div align=\"right\"\>\<a href=\"http\:\/\/smena\.ru\/forum\/\"\>)/",$buf,$found);
            $txt=strip_tags($found[0][0],"<b><p><i><u><strong><br>");


            //print "<h2>$name</h2>$txt<hr>$pic<hr><br><br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);
            //exit(1);
          };
        };

      break;




      case(539890): // регнум

        list($url,$r_id,$pole13,$mainpage)=get_param($news_id);

        $buf=download($url);


        $result=preg_match_all("/(?<=\<\/span\>\<\/td\>\<td width=99\% align=left valign=top\>\<a class=newsline href=\")[\W\w]{1,50}(?=\" target=\"news)/",$buf,$found);
        
        unset($news);

        for($i=0;$i<count($found[0]);$i++){
          $news[count($news)]=$found[0][$i];
          //print $found[0][$i]."<br>";
        };


        $url="http://www.newspb.ru";
        
        for($i=0;$i<count($news);$i++){
          $line = mysql_query("SELECT id FROM $tbl_goods_ WHERE goods_id=1110 and page_id=5 and pole23='".$news[$i]."'",$link);
          if(!($s=mysql_fetch_array($line))){

            print ".  ";

            //print "<br>".$news[$i];
            

            $buf=download($url.$news[$i]);

            $result=preg_match_all("/(?<=\<p align=\"left\"\>\<div style=\"color\:\#0C50AD\; font\-weight\: bold\; font\-size\:12pt\; \"\>)[\W\w]{1,250}(?=\<\/div\>\<\/p\>)/",$buf,$found);
            $name=$found[0][0];
            
            //$result=preg_match_all("/(?<=\<div align=\"justify\"\>\<p\>)[\W\w]{1,}(?=\<\/p\>\<\/div\>\<p align=\"left\"\>)/",$buf,$found);
            $result=preg_match_all("/(?<=\<div align=\"justify\"\>\<p\>)[\W\w]{1,}(?=\<\/p\>\<\/div\>)/",$buf,$found);
            $txt=$found[0][0];


            //print "<h2>$name</h2>$txt<hr>$pic<hr><br><br>";
            
            add_news($name,$txt,$pic,"",$news[$i]);
            //exit(1);
          };
        };

      break;


    };

    


?>