var VPA_LOAD_START=1;
var VPA_LOAD_PROCESS=2;
var VPA_LOAD_END=4;

/**
* Реализует базовый класс для создания компонент
**/
function vpa_component(template) { this.template=template; }

vpa_component.prototype={
    x					:   0,
    y					:   0,
    w					:   0,
    h					:   0,
	_w				:	0,
	_h				:	0,
	_glyph			:	Array(),	// реализация паттерна Glyph
	_index			:	0,				// индекс для подсчется детей-glyph-ов
	_parent		:	null,			//	родитель данного компонента
    _gRoot			:	null,			//	элемент, к которому будут присоединяться дети
	insertNode	:   null,
    DOMobj		:   null,
    contentObj  :   null,
    id					:   null,
    type				:   'component',
    theme			:   '',
    error			:   null,
    template		:   '',
    name			:   'noname',
    _timer			:   null,
    
    stick			:	function(component)
						{
							this._glyph[this._index]=component;
							component._gRoot=this;
							this._index++;
							return component;
						},
						
	rsize		:	function()
						{
							this.x=this.DOMobj.offsetLeft;
							this.y=this.DOMobj.offsetTop;
							this.w=parseInt(this.DOMobj.style.width.replace(/px/i,''));
							this.h=parseInt(this.DOMobj.style.height.replace(/px/i,''));
						},
						
	/**
	* запоминает размеры компонента (используется для того, чтобы методы-трансформеры могли вернуть ему первоначальные размеры)
	**/
	save_wh	:	function()
						{
							this._w=parseInt(this.DOMobj.style.width.replace(/px/i,''));
							this._h=parseInt(this.DOMobj.style.height.replace(/px/i,''));
						},
	
	/**
    * определяет как именно работать с данным шаблоном
    * переопределяется в "детях"
    **/
    parse       :   function()
                    {
						this.DOMobj=document.createElement('div');
                        this.DOMobj.innerHTML=this.template;
                        this.DOMobj=this.DOMobj.firstChild;
						this.contentObj=this.DOMobj;
                        this._init(this.DOMobj);
                        this.draw();
                    },
                    
    draw	     :   function()
                    {
						//this.DOMobj.setAttribute('id','component_'+window['storage'].add_component(this));
						this.insertNode.appendChild(this.DOMobj);
                        this.x=this.DOMobj.offsetLeft;
                        this.y=this.DOMobj.offsetTop;
                        this.w=this.DOMobj.offsetWidth;
                        this.h=this.DOMobj.offsetHeight;
						this.save_wh();
                    },
					
	redraw		:	function()
					{
						this.DOMobj.style.left=this.x;
                        this.DOMobj.style.top=this.y;
                        this.DOMobj.style.width=this.w;
                        this.DOMobj.style.height=this.h;
					},
					
                    
    create      :   function ()
                    {
                        this.theme=window['vpa_theme'];
						this.template=window['storage'].templates[this.toString()];
						this.parse();
						return this;
                    },
	
    toString    :   function()
                    {
                        return 'vpa_component';
                    },
    
    get_pattern :   function (DOMobj,str)
                    {
                        //var list=DOMobj.childNodes;
                        var list=(typeof DOMobj.getElementsByTagName!='undefined') ? DOMobj.getElementsByTagName("*") : DOMobj.all;
                        for (var i=0;i<list.length;i++)
                        {
                            txts=list[i].childNodes;
                            for (var j=0;j<txts.length;j++)
                            {
                                if (txts[j].nodeType==3)
                                {
                                    if (txts[j].nodeValue==str) return txts[j];
                                }
                            }
                        }
                        return null;
                    },

    /**
	* Проверяет, имеется ли среди детей данного объекта элемент с заданным классом
	**/
	get_by_class:   function (DOMobj,str)
                    {
                        if (DOMobj.className==str) return DOMobj;
                        var list=(typeof DOMobj.getElementsByTagName!='undefined') ? DOMobj.getElementsByTagName("*") : DOMobj.all;
						for (var i=0;i<list.length;i++)
                        {
							if (list[i].className==str) return list[i];
                        }
                        return null;
                    },
    
    /**
	*	Инициализирует прием компонентом событий методом _eventer
	**/
	_init		:   function(DOMobj)
                    {
                        //if (!this.template) throw new Error("template "+this.toString()+" not loaded. Verify, what this template wrote in vpa_storage.");
                        vpa_add_event(DOMobj,'click',this._eventer.bind(this));
                        vpa_add_event(DOMobj,'dblclick',this._eventer.bind(this));
                        vpa_add_event(DOMobj,'mousedown',this._eventer.bind(this));
                        vpa_add_event(DOMobj,'mouseup',this._eventer.bind(this));
                        vpa_add_event(DOMobj,'mousemove',this._eventer.bind(this));
                        vpa_add_event(DOMobj,'mouseover',this._eventer.bind(this));
                        vpa_add_event(DOMobj,'mouseout',this._eventer.bind(this));
                        //this._timer=window.setInterval(this._eventer.bind(this),100);
						window['storage'].add_component(this);
                        return DOMobj;
                    },
					
	
	/**
	*	Показывает текущий компонент
	**/
	_show	:	function()
					{
						this.DOMobj.style.display='block';
					},
	/**
	*	Скрывает текущий компонент
	**/
	_hide	:	function()
					{
						this.DOMobj.style.display='none';
					},
   
	/**
	*	Обработчик событий, происходящих с компонентом
	**/
    _eventer    :   function(e) {}

}