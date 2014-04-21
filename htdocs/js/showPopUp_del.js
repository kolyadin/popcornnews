// JavaScript Document

//—крипт показывает всплывающее окно относительно верхнего угла елемента на котором произошел клик
//»значально попап находитс€ в контейнере, относительно которого он будет спозиционирован.
//чтобы подключить функцию, в массив elements нужно добавить id кнопки,
// при клике на которой вы хотите, чтобы открывалс€ попап.
//ѕопап при этом имеет id = id элемента_popup
//ѕопап располагаетс€ в <body> изначально и абсолютно спозиционирован
//Ёлемент, который закрывает попап  имеет id = id элемента_close
(function () {
var elements=['ass']



function hidePopUp(e) {
		
			var id=this.id.replace(/_close/, '_popup');
			document.getElementById(id).style.left='-9999px';
			document.getElementById(id).style.top='-9999px';
			return false;
}

function showPopUp(e) {
			var popUp=document.getElementById(this.id+'_popup');
			popUp.wrapper=popUp.parentNode;
			popUp.wrapper.left=getOffset(popUp.wrapper).left;
			popUp.style.left=mouseShowHandler(e).pageX-(mouseShowHandler(e).pageX-getOffset(this).left)-popUp.wrapper.left+'px';
			popUp.style.top=mouseShowHandler(e).pageY-(mouseShowHandler(e).pageY-getOffset(this).top)+'px';
			return false;
}

function mouseShowHandler(e){
	e = e || window.event

	if (e.pageX == null && e.clientX != null ) { 
		var html = document.documentElement
		var body = document.body
	
		e.pageX = e.clientX + (html && html.scrollLeft || body && body.scrollLeft || 0) - (html.clientLeft || 0)
		e.pageY = e.clientY + (html && html.scrollTop || body && body.scrollTop || 0) - (html.clientTop || 0)
	}
	return {pageX:e.pageX, pageY:e.pageY}
}
function getOffset(elem) {
    if (elem.getBoundingClientRect) {
        // "правильный" вариант
        return getOffsetRect(elem)
    } else {
        // пусть работает хоть как-то
        return getOffsetSum(elem)
    }
	
}
function getOffsetRect(elem) {
    // (1)
    var box = elem.getBoundingClientRect()

    // (2)
    var body = document.body
    var docElem = document.documentElement

    // (3)
    var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop
    var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft

    // (4)
    var clientTop = docElem.clientTop || body.clientTop || 0
    var clientLeft = docElem.clientLeft || body.clientLeft || 0

    // (5)
    var top  = box.top +  scrollTop - clientTop
    var left = box.left + scrollLeft - clientLeft
    return { top: Math.round(top), left: Math.round(left) }
}
function getOffsetSum(elem) {
    var top=0, left=0
    while(elem) {
        top = top + parseInt(elem.offsetTop)
        left = left + parseInt(elem.offsetLeft)
        elem = elem.offsetParent
    }
	return {top: top, left: left}
}


var d=document;
for (var i=0; i<elements.length; i++)
{
	try
	{
		var button=d.getElementById(elements[i]);
		button.onclick=showPopUp;
		d.getElementById(elements[i]+'_close').onclick=hidePopUp;
	}
	catch (e) {}
}

		  
		  
})();

		