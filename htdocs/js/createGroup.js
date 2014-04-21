// JavaScript Document
// Скрипт показывает попап (всплывающую подсказку) , получая данные Аяксом
// так же показывает выбранный результат в разметке.
// чтобы подключить функцию, в массив fields нужно добавить id  input-поля,
// при этом, id имеют только те поля к которым нужно подключить попап (это нужно при проверке заполнения полей)
// в массив requests добавить абсолютный путь аякс запроса
// в массив forms добавить id формы (для подключения функции проверки формы checkForm)
// Попап при этом имеет id = id элемента_popup
// Чтобы попап появился к его классу добавляется класс popuprel,
// Соответственно, чтобы убрался, класс убирается.
// UL в который будут вставляться выбранные данные имеет id = id элемента_chosen
// Выбранные данные записываются в скрытые поля
// Имена скрытых полей равны id  input-поля
// Подсказка(попап) исчезает при выборе какого-нибудь варианта или когда поле пустое или когда происходит щелчок на странице
// php должен выдавать json, ключ должен быть 'name'


(function () {
	var fields=['getTags', 'getMembers', 'getAssistants'];
	var requests=['/community/getTags/', '/community/getMembers/:gid/', '/community/getAssistants/:gid/'];
	var forms=['group_new'];
	var tagsCount = 0;

	var d=document;
	var hiddenInputs=new Array(); //Массив, куда записываем (откуда удаляем) выбранные значения (со всех выбранных полей).
	// При отправке формы из него сформируем скрытые поля.

	function createRequest()
	{
		request = null;
		try {
			request = new XMLHttpRequest();
		}
		catch (trymicrosoft)
		{
			try {
				request = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch (othermicrosoft)
			{
				try {
					request = new ActiveXObject ("Microsoft.XMLHTTP");
				}
				catch (failed) {
					request = null;
				}
			}
		}
		if (request == null)
		{
			alert ('Внимание! Объект запроса не создан. Обратитесь к разработчику');
		}
		else {
			return request;
		}
	}

	// Отправляем запрос на получение подсказки
	function reqHelp()
	{
		if (this.id == 'getTags' && tagsCount > 2) {
			return;
		}
		
		val=delBlanks(this.value);
		if (val!='')
		{
			this.request =  createRequest();
			var url = this.req + encodeURI(val);
			this.request.open ("GET", url, true);
			this.getHelp=getHelp;
			var el=this;
			this.request.onreadystatechange = function () {
				el.getHelp();
			}
			this.request.send(null);
		}
		else
		{
			var popup=d.getElementById(this.id+'_popup');
			popup.className=popup.className.replace(' popuprel', '');
		}
	}
	// Получаем данные подсказки
	function getHelp()
	{
		if (this.request.readyState == 4)
		{
			if (this.request.status == 200)
			{
				var help = eval ('(' + this.request.responseText + ')'); //передаем полученные данные переменной
				var popup=d.getElementById(this.id+'_popup');
				var chosenBlock=d.getElementById(this.id+'_chosen');
				var helpBlock=d.getElementById(this.id+'_help');
				helpBlock.innerHTML='';
				//если сущ хотя бы один результат
				if (help[0])
				{
					var liBlock=false; //вставляемый результат
					for (var i=0; i<help.length; i++)
					{
						var isChoice=false; //был или не был сделан такой же выбор
						for (var j=0; j<chosenBlock.childNodes.length; j++)
						{
							if(help[i]['id']==chosenBlock.childNodes[j].id) {
								isChoice=true;
								break;
							}
						}
						//если не был выбран, выбираем, изменяем переменную показа
						if (!isChoice)
						{
							liBlock=d.createElement('LI');
							liBlock.id=help[i]['id']
							liBlock.onclick=getChosen;
							if ('engName' in help[i] && help[i]['engName']!='')
							{
								var spanBlock=d.createElement('SPAN');
								spanBlock.appendChild(d.createTextNode('/'+help[i]['engName']));
								liBlock.appendChild(d.createTextNode(help[i]['name']));
								liBlock.appendChild(spanBlock);
							} else {
								liBlock.appendChild(d.createTextNode(help[i]['name']));
							}
							helpBlock.appendChild(liBlock);
							isChoice=false;
						}
					}
					//Если нет елементов для вывода
					if (!liBlock)
					{
						var liBlock=d.createElement('LI');
						liBlock.className='no_hover';
						var textBlock='Вы уже выбрали все варианты по этому запросу';
						liBlock.appendChild(d.createTextNode(textBlock));
						helpBlock.appendChild(liBlock);
					}
				}
				//иначе ничего не найдено
				else
				{
					var liBlock=d.createElement('LI');
					liBlock.className='no_hover';
					var textBlock='По вашему запросу ничего не найдено, измените критерий поиска';
					liBlock.appendChild(d.createTextNode(textBlock));
					helpBlock.appendChild(liBlock);
				}
				popup.className=(popup.className).replace(' popuprel', '') + ' popuprel';
			}
		//else {alert("Ошибка. Статус запроса" + this.request.status);}
		}
	}
	//Возврашаем строку без начальных и конечных пробелов
	function delBlanks(val)
	{
		val=val.replace(/^\s+/, '');
		val=val.replace(/\s+$/, '');
		return val;
	}



	//Показываем выбранную подсказку, выбранное значение записываем в массив
	function getChosen()
	{
		var id=this.parentNode.id.replace('_help', '');
		if (id == 'getTags') {
			tagsCount++;
		}
		
		var popup=d.getElementById(id+'_popup');
		var input=d.getElementById(id);
		input.value='';
		input.focus();
		popup.className=(popup.className).replace(' popuprel', '')
		var ul=d.getElementById(id+'_chosen');
		this.onclick=delChosen;
		this.inputName=id;
		hiddenInputs[hiddenInputs.length]=this;
		ul.appendChild(this);
	}
	//Удаляем выбранную подсказку
	function delChosen(e)
	{
		stopEvent(e);
		var id=this.parentNode.id.replace('_chosen', '');
		this.parentNode.removeChild(this);
		for (var i=0; i<hiddenInputs.length; i++)
		{
			if (hiddenInputs[i]==this) {
				hiddenInputs.splice(i, 1);
				break;
			}
		}
		var input=d.getElementById(id);
		input.onkeyup();
	}
	//Проверяем форму, если все заполнено, добавляем скрытые поля и отпраляем
	function checkForm()
	{
		var isFill=false;
		for (var i=0; i<this.childNodes.length; i++)
		{
			if (/allowEmpty/i.test(this.childNodes[i].className)) {
				continue;
			}
			// as.$$('input[name="deletePhoto"]')
			if (((this.childNodes[i].type=='file' && !as.$$('.oldImage')) || this.childNodes[i].type=='text' || this.childNodes[i].nodeName=='TEXTAREA') && delBlanks(this.childNodes[i].value) == '' && !this.childNodes[i].id)
			{
				alert('Пожалуйста, заполните все поля');
				return false;
			}
			if (this.childNodes[i].type=='text' && this.childNodes[i].id)
			{
				isFill=false;
				for(var j=0; j<hiddenInputs.length; j++)
				{
					if (this.childNodes[i].id==hiddenInputs[j].inputName) {
						isFill=true;
						break;
					}
				}
				if (!isFill) {
					alert('Пожалуйста, заполните все поля');
					return false;
				}
			}
		}
		//Если идем дальше, значит все заполнено. Формируем скрытые поля и отправляем форму.
		for (var i=0; i<hiddenInputs.length; i++)
		{
			var hiddenInput=d.createElement('INPUT');
			hiddenInput.type='hidden';
			hiddenInput.name=hiddenInputs[i].inputName + '[]';
			hiddenInput.value=hiddenInputs[i].id;
			this.appendChild(hiddenInput);
		}
	}
	//Останавливаем вспытие для элементов формы
	function stopEvent(e)
	{
		e = e || window.event
		e.stopPropagation ? e.stopPropagation() : (e.cancelBubble=true)
	}
	//Скрываем все попапы при клике на страницу
	function hidePoups()
	{
		for (var i=0; i<fields.length; i++)
		{
			try
			{
				var field=d.getElementById(fields[i]);
				if (el!=field)
				{
					field.value='';
					var popup=d.getElementById(fields[i]+'_popup');
					popup.className=popup.className.replace(' popuprel', '');
				}
			}
			catch (e) {}
		}
	}
	//Скрываем другие попапы при клике на поле
	function hideOtherPoups (event)
	{
		stopEvent(event);
		for (var i=0; i<fields.length; i++)
		{
			var field=d.getElementById(fields[i]);
			if (this!=field && field)
			{
				field.value='';
				var popup=d.getElementById(fields[i]+'_popup');
				popup.className=popup.className.replace(' popuprel', '');
			}
		}
	}

	//Добавляем событие к элементу
	function addEvent(elem, type, handler){
		if (elem.addEventListener){
			elem.addEventListener(type, handler, false)
		} else {
			elem.attachEvent("on"+type, handler)
		}
	}



	for (var i=0; i<fields.length; i++)
	{
		try
		{
			var el=d.getElementById(fields[i]);
			el.req=requests[i];
			var gid = document.location.pathname.match(/\/group\/(\d+)\//i);
			if (gid) {
				el.req=el.req.replace(':gid', gid[1]);
			}
			el.onkeyup=reqHelp;
			el.onkeyup();
			el.onclick=hideOtherPoups;
			//проверяем наличие выбранных подсказок, если они есть записываем в массив
			var ul=d.getElementById(fields[i]+'_chosen');
			for (var j=0; j<ul.childNodes.length; j++)
			{
				if (ul.childNodes[j].nodeName=='LI')
				{
					if (fields[i] == 'getTags') {
						tagsCount++;
					}
					
					ul.childNodes[j].onclick=delChosen;
					ul.childNodes[j].inputName=fields[i];
					hiddenInputs[hiddenInputs.length]=ul.childNodes[j];
				}
			}
		}
		catch (e) {}
	}
	for (var i=0; i<forms.length; i++)
	{
		try
		{
			var el=d.getElementById(forms[i]);
			el.onsubmit=checkForm;
		}
		catch (e) {}
	}
	addEvent(d.body, 'click', hidePoups);
})();
