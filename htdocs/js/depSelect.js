var DepSelect;
if (DepSelect) throw new Error('DepSelect существует');
DepSelect=new Object();

DepSelect=function(data){
	this.form=document.getElementById(data.id);
	if (!this.form) throw new Error('Формы с таким id не найдено');
	this.init(data);
}
DepSelect.prototype={
	init:function(){
		this.findElements();		
	},
	showGroups:function(obj){
		for(var i=0,j=this.opt.length;i<j;i++){
			if(this.opt[i].selected){
				var chooseVal=this.opt[i].firstChild.nodeValue;
				var opt=obj.dep.sel.getElementsByTagName('OPTION');
				for(var m=0;m<opt.length;m++){
					obj.dep.storage.appendChild(opt[m]);
					m--;
				}
				var childs=obj.dep.storage.getElementsByTagName('OPTION');
				for(var m=0;m<childs.length;m++){
					if(childs[m].lbl==chooseVal){
						obj.dep.sel.appendChild(childs[m]);	
						--m;
					}
				}
				//после добавления элементов по новой определяем выбранный option
				for(var m=0;m<opt.length;m++){
					if(opt[m].sl)  opt[m].selected=true;
						else opt[m].selected=false;
				}

				
				if (i==0){//если выбрали первый элемент (все), значит делаем нективным зависимый
					obj.dep.className=obj.dep.className.replace(/ disabled/, '')+' disabled';
					obj.dep.sel.disabled=true;
				}
				else{
					obj.dep.className=obj.dep.className.replace(/ disabled/, '');
					obj.dep.sel.disabled=false;
				}
				break;
			}
		}
	},
	findElements:function(){
		var obj=this;
		this.labels=this.form.getElementsByTagName('LABEL');
		for(var i=0,j=this.labels.length;i<j;i++){
			if(this.labels[i].className=='independent') {
				this.indep=this.labels[i];
				var indep=this.indep;
				this.indep.sel=this.indep.getElementsByTagName('SELECT')[0];
				this.indep.opt=this.indep.getElementsByTagName('OPTION');
				this.findOptionsVal(this.indep);
				this.indep.sel.onchange=(function(indep){return function(){obj.showGroups.call(indep, obj);}})(indep)
			}
			else if (this.labels[i].className=='dependent') {
				this.dep=this.labels[i];
				this.dep.sel=this.dep.getElementsByTagName('SELECT')[0];
				this.dep.storage=document.createElement('SELECT');
				var groups=this.dep.sel.getElementsByTagName('OPTGROUP');
				for(var m=0,n=groups.length;m<n;m++){
					var label=groups[m].label;
					var opt=groups[m].getElementsByTagName('OPTION');
					
					//определяем активный option
					for(var k=0,l=opt.length;k<l;k++){
						opt[k].lbl=label;
						if(opt[k].selected==true) opt[k].sl=true;
						else opt[k].sl=false
					}
					
					for(var k=0;k<opt.length;k=k+0){
						this.dep.storage.appendChild(opt[k]);
					}
				}
				this.dep.sel.innerHTML='';
			}
		}
		//запускаем onchange
		for(var i=0,j=this.indep.opt.length;i<j;i++){
			if(this.indep.opt[i].selected){
				this.indep.sel.onchange();
				break;
			}
		}
	},
	findOptionsVal:function(indep){
		indep.values=[];
		var o=indep.getElementsByTagName('OPTION');
		for(var i=0,j=o.length;i<j;i++){
			indep.values.push[o[i].value];
		}
	}
}
