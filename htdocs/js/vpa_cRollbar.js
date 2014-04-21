function vpa_cRollbar(insertNode,name,x,y,w,h,dragable)
{
    this.insertNode=insertNode;
    this.dragable=dragable;
    this.name=name;
    this.type='window';
    this.rollerObj=null;
    this.statusObj=null;
    this.nameObj=null;
	this._start=0;
	this._end=100;
	this._step=1;
    this.current_value=0;
    this.average_value=0;
    this.show_rule=false;
    this.reload_votes=false;
    this.ajax=null;
    if(typeof x!='undefined')
    {
        this.x=x;
        this.y=y;
        this.w=w;
        this.h=h;
    }
    
    this.parse      =   function()
                        {
							this.DOMobj=document.createElement('div');
                            this.DOMobj.innerHTML=this.template;
                            this.DOMobj=this.DOMobj.firstChild;
							this.contentObj=this.DOMobj;
                            this._init(this.DOMobj);
                            if(this.rollerObj=this.get_by_class(this.DOMobj,'vpa_roller'))
                            {
                                vpa_oi(this.rollerObj,'vpa_drag_object')._box.set_box('110px','10px','13px','13px').vpa_drag_object.set_drag_options('horizontal',true,110,220);
                                vpa_oi(this.DOMobj,'vpa_event_proxy_object')._box.set_box(this.x+'px',this.y+'px',this.w+'px',this.h+'px');
                                this.DOMobj.vpa_event_proxy_object.set_getter(this.rollerObj,this.rollerObj.vpa_drag_object);

                                if(this.dragable==0)
                                {
                                    this.rollerObj.onclick=function() { alert ('Для голосования вам необходимо зарегистрироваться !'); };
                                }
                            }
                            
							if(this.statusObj=this.get_by_class(this.DOMobj,'vpa_roll_status'))
                            {
                                vpa_oi(this.statusObj,'vpa_object')._box.set_box('230px','5px','55px','15px');
                                this.statusObj.value=0;
                            }
                            
                            if(this.nameObj=this.get_by_class(this.DOMobj,'vpa_roll_name'))
                            {
                                vpa_oi(this.nameObj,'vpa_object')._box.set_box('0px','5px','105px','15px');
                                this.nameObj.innerHTML=this.name;
                            }
                            this.draw();
                            this.ajax=new vpa_ajax();
                            aid=document.location.href.replace('http://','').split('/')[2];
                            this.ajax.makeRequest('/ajax_get_vote.php?rubric='+this.name+'&aid='+aid,this.set_votes.bind(this),this);
                        }
                    
    this._eventer   =   function (e)
                        {
							if (typeof ObjEvents && ObjEvents().exists('drag_process'))
                            {
                                var k=(this.DOMobj.offsetWidth-178)/(this._end-this._start);
								var v=this._start+Math.ceil((this.rollerObj._box._x-112)/k);
                                this.statusObj.value=v/10;//this.rollerObj._box._x;
                            }
                            
                            if (typeof ObjEvents && ObjEvents().exists('drag_stop') && e.type=='mouseup')
                            {
                                this.reload_votes=true;
                                this.current_value=this.statusObj.value;
                                aid=document.location.href.replace('http://','').split('/')[2];
                                this.show_rule=false;
                                //this.ajax.makeRequest('/ajax_vote.php?vote='+this.statusObj.value+'&rubric='+this.name+'&aid='+aid,this.voter,tpl);
                                this.ajax.makeRequest('/ajax_vote.php?vote='+this.statusObj.value+'&rubric='+this.name+'&aid='+aid,this.voter,this);
                            }
                            
                            if (e.type=='mouseout')
                            {
                                this._overed(1);
                            }
                            else
                            {
                                this._overed(0);
                            }
                        }
    
    this.voter      =   function(DOMtxt,DOMtags)
    {
        //alert (DOMtxt);
    }
    
    this.set_votes_user  =   function (DOMtxt,DOMtags)
    {
        this.current_value=DOMtxt/10;
        //var v=this._start+Math.ceil((this.rollerObj._box._x-91)/k);
        this.set_vote_default();
        document.title=this.current_value;
    }
    
    this.get_votes_user  =   function (DOMtxt,DOMtags)
    {
        this.current_value=DOMtxt/10;
        var k=(this.DOMobj.offsetWidth-178)/(this._end-this._start);
        var x=this.current_value*10*k+112;
        this.rollerObj._box.set_position(x,this.rollerObj._box._y);
    }
    
    this.set_vote_default=  function ()
    {
        this.statusObj.value=this.current_value;
    }
    
    this.set_votes  =   function (DOMtxt,DOMtags)
    {
        this.statusObj.value=DOMtxt/10;
        this.average_value=this.statusObj.value;
        (new vpa_ajax()).makeRequest('/ajax_get_vote.php?rubric='+this.name+'&aid='+aid+'&user=1',this.get_votes_user.bind(this),this);
    }
    
    this._overed    =   function(val)
    {
        if (val==1)
        {
            this.rollerObj.style.display='none';
            CssClasses(this.DOMobj).add('roller_over');
            this.show_rule=false;
            if (this.reload_votes==true)
            {
                aid=document.location.href.replace('http://','').split('/')[2];
                this.ajax.makeRequest('/ajax_get_vote.php?rubric='+this.name+'&aid='+aid,this.set_votes.bind(this),this);
                this.reload_votes=false;
            }
            else
            {
                this.statusObj.value=this.average_value;
            }
        }
        else
        {
            this.rollerObj.style.display='';
            CssClasses(this.DOMobj).remove('roller_over');
            if (this.show_rule==false)
            {
                this.set_vote_default();
                this.show_rule=true;
            }
        }
    }
						
}

vpa_cRollbar.prototype=new vpa_component('rollbar');
vpa_cRollbar.prototype.toString=function() {return 'vpa_cRollbar';}
