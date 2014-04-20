function addtg(txt1,txt2,txt3){
	if((txt2)||(txt2=="")){
		if(navigator.appName == "Microsoft Internet Explorer"){
			this.form.content.focus();
			var sel = document.selection;
			var rng = sel.createRange();
			rng.colapse;
			if(txt2!="") rng.text=txt1+txt2+txt3;
			else rng.text=txt1+rng.text+txt3;
		} else get_active_textarea().value=get_active_textarea().value+txt1+txt2+txt3;
	} else {
		get_active_textarea().value = get_active_textarea().value+txt1+txt2+txt3;
	}
}

function addtgs(txt1,txt2,txt3){
	if((txt2)||(txt2=="")){
		if(navigator.appName == "Microsoft Internet Explorer"){
			get_active_textarea().focus();
			var sel = document.selection;
			var rng = sel.createRange();
			rng.colapse;
			if(txt2!="") {
				while(txt2.indexOf("\n")>0){
					txt2=txt2.substring(0,txt2.indexOf("\n"))+"[*]"+txt2.substring(txt2.indexOf("\n")+1,txt2.length);
				}
				rng.text=txt1+"[*]"+txt2+txt3;
			}else rng.text=txt1+"[*]\n"+"[*]\n"+"[*]\n"+rng.text+txt3;
		} else get_active_textarea().value=get_active_textarea().value+txt1+"\n[*]\n[*]\n[*]"+txt3;
	}
}

function addtg_(){
	if(navigator.appName == "Microsoft Internet Explorer"){
		var sel = document.selection;
		var rng = sel.createRange();
		rng.colapse;
		get_active_textarea().value+="[quote]"+rng.text+"[/quote]";
	} else get_active_textarea().value = get_active_textarea().value+"[quote]"+sl+"[/quote]";
}

function addsm_2(txt1,txt2,txt3,txt4){
	if(txt1){
		if(navigator.appName == "Microsoft Internet Explorer"){
			get_active_textarea().focus();
			var sel = document.selection;
			var rng = sel.createRange();
			rng.colapse;
			rng.text='['+txt3+txt4+txt2+']'+txt1+'[/'+txt3+']';
		} else get_active_textarea().value=get_active_textarea().value+'['+txt3+txt4+txt2+']'+txt1+'[/'+txt3+']';;
	}
}

function get_sel(){
	var sel = '';
	if (document.getSelection){
		sel = document.getSelection();
	} else {
		sel = document.selection.createRange().text;
	}
	return sel;
}

function gs(){
	if (document.getSelection){
		sl=document.getSelection();
	} else {
		sl=document.selection.createRange().text;
	}
}

function get_active_textarea() {
	var forms = document.fmr;
	
	if (document.fmr instanceof HTMLCollection) {
		for (i = 0; i < forms.length; i++) {
			if (forms[i].style.display != 'none') {
				return forms[i].content;
			}
		}
	} else {
		return document.fmr.content;
	}
	throw e;
}