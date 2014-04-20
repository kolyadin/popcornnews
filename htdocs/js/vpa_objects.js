function vpa_in_rectangle(mx,my,x,y,w,h)
{
    window.status=Array(mx,my,x,y,w,h);
    return (mx>=x && mx<=x+w && my>=y && my<=y+h) ? true : false;
}

var VPA_EVENT_CLICK=1;
var VPA_EVENT_MOUSEMOVE=2;
var VPA_EVENT_MOUSEUP=4;
var VPA_EVENT_MOUSEDOWN=8;
var VPA_EVENT_MOUSEOVER=16;
var VPA_EVENT_MOUSEOUT=32;
var VPA_EVENT_SELECTSTART=64;

/**
* инициализатор VPA JSOS
**/

function vpa_os_init(screen,x,y,w,h)
{
    if (!window['storage'])
    {
        new vpa_storage();
        grid_1=false;
        var tpls=window['storage'].templates;
        window['vpa_theme']='/themes/theme_2/';
        tpls['vpa_cRollbar']='<div class="vpa_rollbar"><div class="vpa_shape"><div class="vpa_roll_name"></div><div class="vpa_roller"></div><input type="text" name="roller" value="" readonly="readonly" class="vpa_roll_status" /></div></div>';
		if (typeof window.os_start!='undefined') window.os_start();
        //window['storage']._loader();
    }
    try {
        window['storage'].ajax=new vpa_ajax();
        vpa_oi(document.getElementById(screen),'vpa_object')._box.set_box(x,y,w,h);
    } catch(e) {
        alert (e.message);
    }
}


/**
* объект-хранилище различных объектов
**/
function vpa_storage()
{
	this.drag_getters=Array();
	this.drag_getters_count=0;
    this.components=new Object();
	this._components_count=0;
    this.ajax=null;
    this.current_eventer=null;
    this.current_eventer_interface=null;
    this.obj_events='';
	this.templates=new Object();
	this.templates_status=new Object();
    this.body_event=null;
	this.current_load_status=null;
	this.current_load_timer=null;
	
	this._progress_timer=null;
	
	window['storage']=this;
    
    this._click						=   function(e){}
	this._mousemove		=   function(e)
											{
												window['storage'].current_eventer && window['storage'].current_eventer_interface._mousemove(e,window['storage'].current_eventer);
											}
                                
	this._mousedown=function(e) {}
	this._mouseup=function(e) {}
	
	this._loader					=	function()
											{
												for (tpl in this.templates)
												{
													if (!this.templates_status[tpl])
													{
														var ajax=new vpa_ajax();
														ajax.makeRequest(vpa_theme+'templates/'+tpl+'.html',this.loader,tpl);
														return false;
													}
												}
												this._progress_start();
												return true;
											}
	
	this._progress_start	=	function()
											{
												window['storage']._progress_timer=window.setInterval(
												function ()
												{
													if (document.getElementById('storage_progress'))
													{
														var progr=document.getElementById('storage_progress');
														var count=0;
														for (tpl in this.templates)
														{
															count++;
														}
														var part=parseInt(160/count);
														var i=0;
														for (tpl in this.templates)
														{
															if (this.templates_status[tpl])
															{
																i++;
																var wd=part*i;
																document.getElementById('storage_progress_line').style.width=wd+'px';
																window.status=tpl;
																document.getElementById('storage_progress_text').innerHTML='Загрузка шаблонов: '+tpl;
															}
														}
														if (i==count)
														{
															window.status=this._progress_timer;
															if (window['storage']._progress_timer)
															{
																window.clearInterval(window['storage']._progress_timer);
																window['storage']._progress_timer=null;
															}
															document.getElementById('storage_progress_text').innerHTML='Загрузка окончена';
															var pr=document.getElementById('storage_progress');
															pr.parentNode.removeChild(pr);
															this._progress_end();
														}
													}
													else
													{
														var progr=document.createElement('div');
														document.body.appendChild(progr);
														var width = document.body.offsetWidth ? document.body.offsetWidth : window.innerWidth;
														progr.className='storage_progress';
														progr.style.left=width/2-100+'px';
														progr.style.top='150px';
														progr.id='storage_progress';
														var sp=document.createElement('span');
														sp.appendChild(document.createTextNode('Загрузка шаблонов:'));
														sp.id='storage_progress_text';
														progr.appendChild(sp);
														var bar=document.createElement('div');
														progr.appendChild(bar);
														var lin=document.createElement('div');
														bar.appendChild(lin);
														lin.appendChild(document.createTextNode(' '));
														lin.className='line';
														lin.id='storage_progress_line';
													}
												}.bind(this)
												,10);
											}
											
	this._progress_end		=	function()
											{
												if (typeof window.os_start!='undefined')
													window.os_start();
											}
	
	this.loader					=	function(DOMtxt,DOMtags)
											{
												//alert (this);
												window['storage'].templates[this.getter]=DOMtxt;
												window['storage'].templates_status[this.getter]=true;
												window['storage']._loader();
											}
	
    // заодно перехватываем глобальные события мыши для всего документа
    vpa_add_event(document.body,'click',this._click);
    vpa_add_event(document.body,'mousedown',this._mousedown);
    vpa_add_event(document.body,'mouseup',this._mouseup);
    vpa_add_event(document.body,'mousemove',this._mousemove);
	
	/**
	* добавляет в хранилище объект типа vpa_drag_getter_object
	**/
	this.add_drag_getter=function(DOMobj)
	{
		this.drag_getters[this.drag_getters_count]=DOMobj;
		this.drag_getters_count++;
	}
	
	this.get_drag_getters=function()
	{
		return this.drag_getters;
	}
    
    /**
	* добавляет в хранилище компонения
	**/
	this.add_component=function(component)
	{
		this.components[component.id]=component;
		this._components_count++;
        return this._components_count-1;
	}
	
	this.get_component=function(id)
	{
		return this.components[id];
	}
}
/*===============================================================*/


function vpa_box_object(DOMobj)
{
	this._DOMelement=DOMobj;
    this._x=DOMobj.offsetLeft;
	this._y=DOMobj.offsetTop;
	this._w=DOMobj.offsetWidth;
	this._h=DOMobj.offsetHeight;
    
    this._init              =   function(DOMobj) 
                                {
                                    return DOMobj;
                                }
                                
    this.init               =   function(DOMobj)
                                {
                                    return this._init(DOMobj);
                                }

	this.set_position		=	function(x,y)
								{
									this._x=x;
		                            this._y=y;
									this._DOMelement.style.left=x;
                            		this._DOMelement.style.top=y;
								}
    
	this.set_box            =   function(x,y,w,h)
                                {
                                    this._x=x;
		                            this._y=y;
		                            this._w=w;
		                            this._h=h;
                                    if (parseInt(x.replace(/px/,'is',''))>=0)
                                    {
                                        this._DOMelement.style.left=x;
                                    }
                                    else
                                    {
                                        this._DOMelement.style.right=-parseInt(x.replace(/px/,'is',''))+'px';
                                    }
                                    this._DOMelement.style.top=y;
                                    this._DOMelement.style.width=w;
                                    this._DOMelement.style.height=h;
                                    return this._DOMelement;
                                }
                                
    this.init(DOMobj);
}

/**
* Базовый класс объекта
**/
function vpa_object(EVENTS)
{
    this.events=EVENTS;
    this._DOMelement=null;
	this._eventType=null;
	this._eventTimer=null;
	this._click=function(e,DOM) 				{ }
	this._mousemove=function(e,DOM)	{ }
	this._mousedown=function(e,DOM)	{ }
	this._mouseup=function(e,DOM) 		{ }
	this._mouseover=function(e,DOM) 	{ }
	this._mouseout=function(e,DOM) 		{ }
	this._selectstart=function(e) {}
	
	/**
	* инициализация объекта
	**/
	this._init              =   function(DOMobj)
	                            {
		                            this._DOMelement=DOMobj;
                                    if (this.events & 1) vpa_add_event(DOMobj,'click',this._click);
                            		if (this.events & 8) vpa_add_event(DOMobj,'mousedown',this._mousedown);
		                            if (this.events & 4) vpa_add_event(DOMobj,'mouseup',this._mouseup);
		                            if (this.events & 2) vpa_add_event(DOMobj,'mousemove',this._mousemove);
		                            if (this.events & 16) vpa_add_event(DOMobj,'mouseover',this._mouseover);
		                            if (this.events & 32) vpa_add_event(DOMobj,'mouseout',this._mouseout);
		                            if (this.events & 64) vpa_add_event(DOMobj,'selectstart',this._selectstart);
                                    return DOMobj;
	                            }
	
	this.init               =   function (DOMobj)
	                            {
                                    if (typeof DOMobj._box=='undefined')
                                    {
                                        DOMobj._box=new vpa_box_object(DOMobj);
                                    }
                                    return this._init(DOMobj);
	                            }
}
/*===============================================================*/


/**
* Передает все события, подписанному объекту
**/
function vpa_event_proxy_object()
{
	this.DOMgetter=null;
	this.getterClass=null;
    
    //this._click         =function(e) { vpa_create_event(this.DOMgetter,'click'); }
	this._mousemove     =	function(e)
											{
												this.vpa_event_proxy_object.getterClass._mousemove(e,this.vpa_event_proxy_object.DOMgetter);
											}
	//this._mousedown     =function(e) { vpa_create_event(this.vpa_event_proxy_object.DOMgetter,'mousedown'); }
	//this._mouseup       =function(e) { vpa_create_event(this.vpa_event_proxy_object.DOMgetter,'mouseup'); }
	//this._mouseover     =function(e) { vpa_create_event(this.vpa_event_proxy_object.DOMgetter,'mouseover'); }
	//this._mouseout      =function(e) { vpa_create_event(this.vpa_event_proxy_object.DOMgetter,'mouseout'); }
	//this._selectstart   =function(e) { vpa_create_event(this.vpa_event_proxy_object.DOMgetter,'selectstart'); }
	
    this.set_getter         =   function(DOMobj,DOMclass)
                                {
                                    this.DOMgetter=DOMobj;
									this.getterClass=DOMclass;
                                    return this._DOMelement;
                                }
                                
}
vpa_event_proxy_object.prototype=new vpa_object(127);
/*===============================================================*/


function vpa_drag_getter_object()
{
	this._getter=true;
	
	this._click             =   function(e) {}
	this._mousemove         =   function(e)
                                {
		                            var event=e || window.event;
		                            //window.status=event.clientX;
	                            }
	this._mousedown         =   function(e) {}
	this._mouseup           =   function(e) {}
	
	this.drag_over          =   function()
	                            {
                                    CssClasses(this._DOMelement).add('get_drag');
	                            }
	
	this.drag_out           =   function()
	                            {
		                            CssClasses(this._DOMelement).remove('get_drag');
                                }
	
	this.init               =   function(DOMobj)
                    	        {
                                    if (!window['storage']) throw new Error("vpa_storage not initializated");
		                            window.storage.add_drag_getter(DOMobj);
		                            return this._init(DOMobj);
	                            }
}
vpa_drag_getter_object.prototype=new vpa_object();


/**
* Добавляет поддержку Dran`n`Drop
**/
function vpa_drag_object()
{
	this._DNDparent=null;
	this._draggable=true;
	this._resizable=false;
	this._dragged=false;
	this._only_vertical=false; // перемещение только по вертикали
	this._only_horizontal=false; // перемещение только по горизонтали
	this._limited_motion=false;  // перемещение только в ограниченном диапазоне (todo)
	this._start_position=0; // абсолютная позиция начала диапазона в котором можно перемещаться
	this._end_position=100; // абсолютная позиция конца диапазона в котором можно перемещаться
	this._dx=null;
	this._dy=null;
    
	this.set_drag_options   =   function (directions,limited_motion,start_position,end_position)
	                            {
		                            if (directions=='horizontal')
		                            {
			                            this._only_horizontal=true;
		                            }
		                            if (directions=='vertical')
		                            {
			                            this._only_vertical=true;
		                            }
		                            this._limited_motion=limited_motion;
		                            this._start_position=start_position;
		                            this._end_position=end_position;
                                    return this._DOMelement;
	                            }
	
	this._click             =   function(e,DOM)
	                            {
		                            var event=e || window.event;
	                            }
    
	this._mousemove         =   function(e,DOM)
	                            {
                                    var event=e || window.event;
                                    var DOMobj=DOM ? DOM : this;
                                    var obj=DOMobj['vpa_drag_object'];
                                    var box=DOMobj['_box'];
                                    if (typeof obj!='undefined' && obj._draggable && obj._dragged)
		                            {
			                            if (!obj._only_vertical && (!obj._limited_motion || (obj._limited_motion && event.clientX-obj._dx>obj._start_position) && event.clientX-obj._dx<obj._end_position))
			                            {
				                            DOMobj.style.left=(event.clientX-obj._dx)+'px';
				                            box._x=(event.clientX-obj._dx);
			                            }
			                            if (!obj._only_horizontal && (!obj._limited_motion || (obj._limited_motion && event.clientY-obj._dy>obj._start_position) && event.clientY-obj._dy<obj._end_position))
			                            {
                            				DOMobj.style.top=(event.clientY-obj._dy)+'px';
				                            box._y=(event.clientY-obj._dy);
			                            }
                                        //window.status=DOMobj.offsetLeft+':'+box._x+','+box._y;
                                        ObjEvents().remove('drag_start');
                                        ObjEvents().add('drag_process');
			                            obj.test_getters();
                                    }
	                            }
	
	this._mousedown         =   function(e,DOM)
	                            {
		                            var event=e || window.event;
                                    var DOMobj=DOM ? DOM : this;
		                            var obj=DOMobj['vpa_drag_object'];
                                    var box=DOMobj['_box'];
		                            if (obj._draggable && !obj._dragged)
		                            {
			                            window.storage.current_eventer=DOMobj;
										window.storage.current_eventer_interface=obj;
										obj._dx=event.clientX-DOMobj.offsetLeft;
			                            obj._dy=event.clientY-DOMobj.offsetTop;
                                        box._x=(event.clientX-obj._dx);
			                            box._y=(event.clientY-obj._dy);
			                            DOMobj.style.zIndex=10;
			                            obj._dragged=true;
                                        ObjEvents().add('drag_start');
		                            }
                            	}
	
	this._mouseup           =   function(e,DOM)
	                            {
		                            var event=e || window.event;
                                    var DOMobj=DOM ? DOM : this;
                                    var obj=DOMobj['vpa_drag_object'];
                                    window.storage.current_eventer=null;
                                    window.storage.current_eventer_interface=null;
		                            if (obj._dragged)
                                    {
                                        obj._dragged=false;
                                        DOMobj.style.zIndex=1;
                                        ObjEvents().remove('drag_process');
                                        ObjEvents().add('drag_stop');
                                        window.setTimeout(function() {ObjEvents().remove('drag_stop')},100);
                                    }
	                            }
    
    this.test_getters       =   function()
	                            {
		                            var getters=window.storage.get_drag_getters();
		                            var len=getters.length;
                                    var flag=null;
		                            for (var i=0;i<len;i++)
		                            {
			                            var t=getters[i];
                            			t.style.zIndex=0;
			                            if (t.offsetLeft<this._DOMelement.offsetLeft && t.offsetLeft+t.offsetWidth>this._DOMelement.offsetLeft && t.offsetTop<this._DOMelement.offsetTop && t.offsetTop+t.offsetHeight>this._DOMelement.offsetTop)
			                            {
				                            this._DNDparent=t['vpa_drag_getter_object']._DOMelement;
				                            t['vpa_drag_getter_object'].drag_over();
                                            var flag=true;
                                            ObjEvents().add('drag_capture');
			                            }
			                            else
                            			{
				                            this._DNDparent=null;
				                            t['vpa_drag_getter_object'].drag_out();
                                            var flag=false;
                                            ObjEvents().remove('drag_capture');
			                            }
		                            }
	                            }
}
vpa_drag_object.prototype=new vpa_object(14);
vpa_drag_object.prototype.toString=function() { return 'vpa_drag_object'; }
/*================================*/

/**
* Добавляет поддержку Resize
**/
function vpa_resize_object()
{
	this._resizable=true;
	this._resized=false;
	this._only_vertical=false; // перемещение только по вертикали
	this._only_horizontal=false; // перемещение только по горизонтали
	this._limited_motion=false;  // перемещение только в ограниченном диапазоне (todo)
	this._start_position=0; // абсолютная позиция начала диапазона в котором можно перемещаться
	this._end_position=100; // абсолютная позиция конца диапазона в котором можно перемещаться
	this._dx=null;
	this._dy=null;
	
	this.set_resize_options =   function (directions,limited_motion,start_position,end_position)
	                            {
		                            if (directions=='horizontal')
		                            {
			                            this._only_horizontal=true;
                                    }
		                            if (directions=='vertical')
		                            {
			                            this._only_vertical=true;
		                            }
		                            this._limited_motion=limited_motion;
		                            this._start_position=start_position;
		                            this._end_position=end_position;
                                    return this._DOMelement;
	                            }
	
	this._click             =   function(e,DOM)
	                            {
		                            var event=e || window.event;
	                            }
	
	this._mousemove         =   function(e,DOM)
	                            {
		                            var event=e || window.event;
                                    var DOMobj=DOM ? DOM : this;
                                    var obj=DOMobj['vpa_resize_object'];
                                    var box=DOMobj['_box'];
		                            if (typeof obj!='undefined' && obj._resizable && obj._resized)
		                            {
                                        if (!obj._only_vertical && (!obj._limited_motion || (obj._limited_motion && (event.clientX-box._x)+obj._dx>obj._start_position) && (event.clientX-box._x)+obj._dx<obj._end_position)) 
			                            {
				                            DOMobj.style.width=(event.clientX-box._x)+obj._dx+'px';
				                            box._w=(event.clientX-box._x)+obj._dx;
			                            }
                            			if (!obj._only_horizontal && (!obj._limited_motion || (obj._limited_motion && (event.clientY-box._y)+obj._dy>obj._start_position) && (event.clientY-box._y)+obj._dy<obj._end_position))
			                            {
				                            DOMobj.style.height=(event.clientY-box._y)+obj._dy+'px';
				                            box._h=(event.clientY-box._h)+obj._dy;
                            			}
                                        ObjEvents().remove('resize_start');
                                        ObjEvents().add('resize_process');
                                    }
	                            }
	
	this._mousedown         =   function(e,DOM)
	                            {
		                            var event=e || window.event;
                                    var DOMobj=DOM ? DOM : this;
                            		var obj=DOMobj['vpa_resize_object'];
                                    var box=DOMobj['_box'];
		                            if (obj._resizable && !obj._resized)
		                            {
                                        window.storage.current_eventer=DOMobj;
										window.storage.current_eventer_interface=obj;
			                            box._x=DOMobj.offsetLeft;
			                            box._y=DOMobj.offsetTop;
                                        obj._dx=DOMobj.offsetLeft+DOMobj.offsetWidth-event.clientX;
			                            obj._dy=DOMobj.offsetTop+DOMobj.offsetHeight-event.clientY;
			                            box._w=event.clientX+obj._dx;
			                            box._h=event.clientY+obj._dy;
			                            DOMobj.style.zIndex=10;
			                            obj._resized=true;
                                        ObjEvents().add('resize_start');
		                            }
	                            }
	
	this._mouseup           =   function(e,DOM)
	                            {
		                            var event=e || window.event;
                                    var DOMobj=DOM ? DOM : this;
		                            var obj=DOMobj['vpa_resize_object'];
                                    window.storage.current_eventer=null;
                                    window.storage.current_eventer_interface=null;
		                            if (obj._resizable && obj._resized)
                                    {
                                        obj._resized=false;
                                        DOMobj.style.zIndex=1;
                                        ObjEvents().add('resize_stop');
                                        window.setTimeout(function() {ObjEvents().remove('resize_stop')},100);
                                    }
                                    ObjEvents().remove('resize_process');
	                            }
					
}
vpa_resize_object.prototype=new vpa_object(14);
/*================================*/

/**
* Объект-выключатель позволяет в зависимости от событий включать или выключать определенные свойства других объектов
**/
function vpa_switch_object()
{
    this._targetDOMobj;
    this._target_class;
    this._target_property;
    this._target_value;
    
    this.link_switch        =   function(tDOMobj,tclass,tprop)
                                {
                                    this._targetDOMobj=tDOMobj;
                                    this._target_class=tclass;
                                    this._target_property=tprop;
                                }

    this._click             =   function(e)
                                {
                                    var event=e || window.event;
		                            var DOMobj=this;
                                    var obj=DOMobj['vpa_switch_object'];
                                    CssClasses(DOMobj).add('focus');
                                    obj._targetDOMobj[obj._target_class][obj._target_property]=(obj._targetDOMobj[obj._target_class][obj._target_property]) ? false : true;
                                }
                                
    this._selectstart       =   function(e)
                                {
                                    return false;
                                }


    this._mousedown         =   function(e)
                                {
                                    var event=e || window.event;
		                            var DOMobj=this;
                                    var obj=DOMobj['vpa_switch_object'];
                                    CssClasses(DOMobj).add('focus');
                                    obj._targetDOMobj[obj._target_class][obj._target_property]=true;
                                }
	
    this._mouseup         =   function(e)
                                {
                                    var event=e || window.event;
		                            var DOMobj=this;
                                    var obj=DOMobj['vpa_switch_object'];
                                    CssClasses(DOMobj).remove('focus');
                                    obj._targetDOMobj[obj._target_class][obj._target_property]=false;
                                }
}
vpa_switch_object.prototype=new vpa_object(76);
/*================================*/

function vpa_oi(DOMobj,class_name)
{
	if (typeof(window[class_name])!='function' && typeof(window[class_name])!='object') throw new Error ("Class "+class_name+" not exist");
	var obj=new window[class_name]();
	DOMobj[class_name]=obj;
	try {
	DOMobj[class_name].init(DOMobj);
	} catch(e) { alert (obj.init);  alert (e.message); }
	return DOMobj;
}


function show_info(obj)
{
str="";
wn=window.open("about:blank");
for (val in obj)
	{
	str+=val+": "+obj[val]+"<hr>";
	}
wn.document.write(str);
wn.document.close();
}
