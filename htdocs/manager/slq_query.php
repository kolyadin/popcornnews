<?php
// файл для обработки всяких запросов с которыми не могут справится супер классы ))))
include "../inc/connect.php";
$tbl_cities="popcornnews_cities";
$tbl_countries="popcornnews_countries";
$tbl_facts="popcornnews_facts";
$tbl_facts_votes="popcornnews_fact_votes";
$tbl_friends="popkorn_friends";
$tbl_comments="popconnews_comments";
$tbl_users="popkorn_users";
switch($action){
    case "city_skip":
	    foreach($_POST["skip_ids"] as $key=>$value){
	        $cmd="update $tbl_cities set skip='1' where id=".intval($value);
	        mysql_query ($cmd,$link);
	    }
	    header("location: ./admin.php?type=cities&country_id=".$_POST["country_id"]);
	    exit;
	break;

	case "fact_archive":
		if(count($_POST["ids_del"])){			foreach($_POST["ids_del"] as $k => $v){
				$cmd="delete from $tbl_facts where id='".intval($v)."'";
				mysql_query ($cmd,$link);
			}
		}

		if(count($_POST["ids_arch"])){
			foreach($_POST['ids'] as $key=>$value){
				if(in_array($value,$_POST["ids_arch"])){
					$cmd="select * from $tbl_facts where id=".intval($value)." and enabled=1";
					$line=mysql_query ($cmd,$link);
					if($s=mysql_fetch_assoc($line)){

						$cmd1="select count(*) trust_votes, sum(vote) trust from $tbl_facts_votes where fid=".$s["id"]." and rubric=1";
						$line1=mysql_query ($cmd1,$link);
						$ss=mysql_fetch_assoc($line1);
						$trust=$ss['trust'];
						$trust_votes=$ss['trust_votes'];

						$cmd2="select count(*) liked_votes, sum(vote) liked from $tbl_facts_votes where fid=".$s["id"]." and rubric=2";
						$line2=mysql_query ($cmd2,$link);
						$ss=mysql_fetch_assoc($line2);
						$liked=$ss['liked'];
						$liked_votes=$ss['liked_votes'];

						$cmd3="update $tbl_facts set enabled='0', trust_votes='$trust_votes', trust='$trust', liked_votes='$liked_votes', liked='$liked' where id=".$s['id'];
						mysql_query ($cmd3,$link);
					}
				}
				else{
					$cmd="update $tbl_facts set enabled='1' where id=".intval($value)." and enabled=0";
					mysql_query ($cmd,$link);
				}
			}
		}

		header("location: ./admin.php?type=facts&order=".$_POST["order"].'&enabled='.$_POST['enabled']);
		exit;
	break;

	case "fact_update":
					/* // не совсем верно было вот и апдейт пришлось делать
					$cmd="select * from $tbl_facts where enabled=0 order by id ";
					$line=mysql_query ($cmd,$link);
					while($s=mysql_fetch_assoc($line)){						$cmd1="select count(*) trust_votes, sum(vote) trust from $tbl_facts_votes where fid=".$s["id"]." and rubric=1";
						$line1=mysql_query ($cmd1,$link);
						$ss=mysql_fetch_assoc($line1);
						$trust=$ss['trust'];
						$trust_votes=$ss['trust_votes'];

						$cmd2="select count(*) liked_votes, sum(vote) liked from $tbl_facts_votes where fid=".$s["id"]." and rubric=2";
						$line2=mysql_query ($cmd2,$link);
						$ss=mysql_fetch_assoc($line2);
						$liked=$ss['liked'];
						$liked_votes=$ss['liked_votes'];
						if($s[trust]!=$trust || $liked!=$s[liked])
						echo "<br>$s[id] => trust: $s[trust] - liked: $s[liked] == trust: $trust - liked: $liked <br> ";

						$cmd3="update $tbl_facts set trust_votes='$trust_votes', trust='$trust', liked_votes='$liked_votes', liked='$liked' where id=".$s['id'];
						mysql_query ($cmd3,$link);

					}
					echo "<br>ok";
                    */

	break;


	case "friends": // чистка в друзьях дубликатов связей
	/*
        $cmd="select * from $tbl_friends where confirmed=1 order by uid";
        $line=mysql_query ($cmd,$link);
        $i=1;
        while($s=mysql_fetch_assoc($line)){
            $cmd="select * from $tbl_friends where fid='".$s["uid"]."' and uid='".$s["fid"]."' order by fid";
            $line1=mysql_query ($cmd,$link);
            if($ss=mysql_fetch_assoc($line1)){
                $cmd="select * from $tbl_friends where uid='".$ss["fid"]."' and fid='".$ss["uid"]."' and confirmed=1";
                $line2=mysql_query ($cmd,$link);
                if($sss=mysql_fetch_assoc($line2)){
	                print $i++.". ".$s["id"]." ".$s["uid"].'='.$s["fid"]." == ".$ss["id"]." ".$ss["fid"].'='.$ss["uid"]."<br>";
	                $cmd1="delete from $tbl_friends where fid='".$s["uid"]."' and uid='".$s["fid"]."'";
	                print "$cmd1<br>";
					mysql_query ($cmd1,$link);
                }
            }
        }
        */
	break;

	case "spam_clear": // очистка спама со списанием рейтинга
	/*
        $cmd="select pole5,pole3,pole8,name,count(*) a  from $tbl_comments where goods_id=4 and pole8<>''  and pole8<>0 group by pole5,pole8,pole3 having a>2 order by a desc";
        $line=mysql_query ($cmd,$link);
        while($s=mysql_fetch_assoc($line)){
			print  "<hr>".$s["pole5"].' = '.$s["pole8"].' = '.$s["a"].' = '.$s["pole3"];
			$i=1;
            $cmd1="select * from $tbl_comments where goods_id=4 and pole5=".$s["pole5"]." and pole8=".$s["pole8"]." and pole3 like'".$s["pole3"]."' order by id limit 1,".$s["a"];
            $line1=mysql_query ($cmd1,$link);
            while($s1=mysql_fetch_assoc($line1)){
                $cmd2="delete from $tbl_comments where id=".$s1["id"]." limit 1";
                print "<br>".$i++."$cmd2";
                mysql_query ($cmd2,$link);
            }

            if($i>1){
	            $cmd3="select * from $tbl_users where id=".$s["pole8"];
	            $line3=mysql_query ($cmd3,$link);
	            while($s3=mysql_fetch_assoc($line3)){

	                $cmd4="update $tbl_users set rating=rating-".(($s3["rating"]-$s["a"]+1)>0?($s["a"]-1):0)." where id=".$s["pole8"];
	                print "<br>$cmd4";
	                mysql_query ($cmd4,$link);
	            }
	        }
        }
    */
	break;

	case "bd_mail":
        /*
		$title='Поздравляем с днем рождением';
		$from="From: www.popcornnews.ru<info@popcornnews.ru>\nContent-Type: text/html; charset=windows-1251\n";
        $maket=file_get_contents("../data/templates/mail/message.inc");
	    $maket = str_replace('<#title>', $title, $maket);
        $cmd="select * from $tbl_users where substring(birthday,5,4)=".date("md")." limit 1";
        $line=mysql_query ($cmd,$link);
        while($s=mysql_fetch_assoc($line)){
	        $message=($s['sex']!=''?($s['sex']==1?'Уважаемый ':'Уважаемая '):'').$s['nick'].'! Администрация сайта от всей души поздравляет Вас с днем рожденья! Мы желаем Вам счастья, здоровья и успехов во всех начинаниях.';
		    $content=str_replace('<#message>',$message,$maket);
			mail ($s['nick'].' <'.$s['email'].'>', $title, $content, $from);
        }
        */
	break;

	case "clon_fans": // читска от дубликатов фанатов

        $cmd="select gid, uid, count(*) cnt  from popkorn_fans group by uid, gid having cnt>1 order by cnt desc";
        $line=mysql_query ($cmd,$link);
        while($s=mysql_fetch_assoc($line)){
            $cmd1="delete from popkorn_fans where gid=".$s['gid']." and uid=".$s['uid'].' order by id desc limit '.($s['cnt']-1);
            mysql_query ($cmd1,$link);
            print "$cmd1 ".mysql_error()."<br>";

        }

	break;
}

?>