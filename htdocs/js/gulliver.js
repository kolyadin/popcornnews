


function getCookie(name) {
	var cookie = " " + document.cookie;
	var search = " " + name + "=";
	var setStr = null;
	var offset = 0;
	var end = 0;
	if (cookie.length > 0) {
		offset = cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			end = cookie.indexOf(";", offset)
			if (end == -1) {
				end = cookie.length;
			}
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	return(setStr);
}

		function setCookie (name, value, expires, path, domain, secure) {
      document.cookie = name + "=" + escape(value) +
        ((expires) ? "; expires=" + expires : "") +
        ((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : "");
		
}

	


//Проверяем куки
function isCookies()
{
	var isCookie = getCookie('banner');
	
	if (isCookie) 
	{
		return true;
	}
	else 
	{
		return false;
	} 
}

function isBunner()
{
	 //если куки есть или отсутствует флеш, просто вставляем фон и канат
	if (isCookies() || flashinstalled!=2) 
	{
		gulliverF(); cableMain.style.visibility='visible';
		//Вставили пиксель (банер и бренд)
		createPixel ('http://b.traf.spb.ru/b_show.php?bid=191350730&img=1', 'baner');
		createPixel ('http://b.traf.spb.ru/b_show.php?bid=191350729&img=1', 'brend');
	}
	//Куки отсутствуют вставляем флеш, записываем куки
	else 
	{	
	//Записывыем куки		
		var oDate = new Date();
		oDate.setHours(oDate.getHours() + 1);
		oDate.toUTCString();		
		setCookie('banner', 'yes', oDate);
	
	//Вставляем пиксель фулскрин
	createPixel('http://b.traf.spb.ru/b_show.php?bid=191350731&img=1', 'full_screen')
	
	//Показываем флешку
	document.body.className='show_full_banner';
		var fullScr = new SWFObject("/swf/fulscreen7.swf", "full_screen_flash", "100%", "100%", "7");
			fullScr.addParam("quality", "high");
			fullScr.addParam("wmode", "transparent");
			fullScr.addParam("allowScriptAccess", "always");
			fullScr.write("full_screen");		
	}	
}
//Вызывается из флеша, убираем прелоад меняем позицию у фона
function showFonFirst()
{
	document.body.removeChild(document.getElementById('preload_gulliver'));
	gulliverF();
}
//Вызывается из флеша, по окончании разрывания, вставляем видео
function playGulliverFirst()
{
	document.body.removeChild(document.getElementById('full_screen'));

	setTimeout ("document.getElementById('videoplayer_cont').style.marginTop= -255+getBodyScrollTop()+'px';", 1000);
}
function flashPlayer() {
				var bannerL = new SWFObject("/swf/uppod.swf", "videoplayer811r", "641", "388", "9");
				bannerL.addParam("quality", "high");
				bannerL.addParam("wmode", "opaque");
				bannerL.addParam("useExpressInstall", true);
				bannerL.addParam("autoplay", 1);
				bannerL.addParam("autoload", 1);
				bannerL.addParam("allowScriptAccess", "always");
				bannerL.addParam("allowScriptAccess", "always");
				bannerL.addVariable("comment", "Путешествия Гулливера");
				bannerL.addVariable("uid", "videoplayer811");
				bannerL.addVariable("m", "video");
				bannerL.addVariable("file", "http://v0.popcorn-news.ru/video/banner_guliver.flv");
				bannerL.write("videoplayer811");
}

//Вызывается из флеш-банера, показываем фулскрин
function playGulliver()
{
	gulliverFon.style.top='0px';
	cableMain.style.visibility='hidden';
	document.getElementById('wrapper').style.display='none';
	flashPlayer();
	var playerCont=document.getElementById('videoplayer_cont');
	document.body.className='show_full_banner';
	playerCont.style.marginTop= -255+getBodyScrollTop()+'px';
	//Вставляем пиксель фулскрин
	createPixel('http://b.traf.spb.ru/b_show.php?bid=191350731&img=1', 'full_screen')
}
function getBodyScrollTop()
{
  return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);
}
			
function hidePlayer()
{
	document.getElementById('videoplayer811').innerHTML='';
	document.body.className='';
	document.getElementById('videoplayer_cont').style.marginTop='-10000px';
	document.getElementById('wrapper').style.display='block';
	cableMain.style.visibility='visible';
	//Вставили пиксель (банер и бренд)
	createPixel ('http://b.traf.spb.ru/b_show.php?bid=191350730&img=1', 'baner');
	createPixel ('http://b.traf.spb.ru/b_show.php?bid=191350729&img=1', 'brend');
}


function goHref()
{
	window.open('http://b.traf.spb.ru/b_click2.php?bid=191350731');
	hidePlayer();
}


			function uppodOnEnd(playerID)
				{
					alert('123');
				}
				function uppodStartsReport(playerID)
				{
					alert('123');
				}
				

function detectIE6()
{
var browser = navigator.appName;
if (browser == "Microsoft Internet Explorer")
{
var b_version = navigator.appVersion;
var re = /\MSIE\s+(\d\.\d\b)/;
var res = b_version.match(re);

if (res[1]<= 6)
return true;
}

return false;
}

function fixedIE6()
{
	var top=getBodyScrollTop();
	if (top<(document.body.offsetHeight-document.documentElement.clientHeight))
	{
		gulliverFon.style.top=getBodyScrollTop()+'px';
		cableMain.style.top=getBodyScrollTop()+'px';
	}
}
				
function createPixel (ref, cl)
{
var pixel=document.createElement('IMG');
pixel.className='pixel '+ cl;
pixel.src=ref+'&dummy=' + new Date().getTime();
document.body.appendChild(pixel);
}
				

