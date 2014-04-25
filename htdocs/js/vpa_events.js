Function.prototype.bind = function(object) {
    var method = this
    return function() {
        return method.apply(object, arguments) 
    }
}


function vpa_create_event(DOMobj,event_name,event_type)
{
	if (typeof(DOMobj.fireEvent)=='function' || typeof(DOMobj.fireEvent)=='object')
    {
        var evt = document.createEventObject();
        DOMobj.fireEvent('on'+event_name, evt);
    }
    else
    {
        if (event_type=='undefined')
        {
            event_type='MouseEvents';
        }
        //var evt = document.createEvent("HTMLEvents");
        //var evt = document.createEvent("MouseEvents");
        var evt = document.createEvent(event_type);
        evt.initEvent(event_name, true, false);
        DOMobj.dispatchEvent(evt);
    }
}

function vpa_add_event(object, event, handler)
{
    if (typeof object.addEventListener != 'undefined')
    {
        object.addEventListener(event, handler.bind(object), false);
    }
    else if (typeof object.attachEvent != 'undefined')
    {
        object.attachEvent('on' + event, handler.bind(object));
    }
    else
    {
        var handlersProp = '_handlerStack_' + event;
        var eventProp = 'on' + event;
        if (typeof object[handlersProp] == 'undefined')
        {
            object[handlersProp] = [];
            if (typeof object[eventProp] != 'undefined')
            object[handlersProp].push(object[eventProp]);
            object[eventProp] = function(e)
            {
                var ret = true;
                for (var i = 0; ret != false && i < object[handlersProp].length; i++)
                ret = object[handlersProp][i](e);
                return ret;
            }
        }
        object[handlersProp].push(handler);
    }
}

function vpa_drop_event(object, event, handler)
{
  if (typeof object.removeEventListener != 'undefined')
    object.removeEventListener(event, handler, false);
  else if (typeof object.detachEvent != 'undefined')
    object.detachEvent('on' + event, handler);
  else
  {
    var handlersProp = '_handlerStack_' + event;
    if (typeof object[handlersProp] != 'undefined')
    {
      for (var i = 0; i < object[handlersProp].length; i++)
      {
        if (object[handlersProp][i] == handler)
        {
          object[handlersProp].splice(i, 1);
          return;
        }
      }
    }
  }
}
