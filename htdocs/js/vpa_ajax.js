Function.prototype.bind = function(object) {
    var method = this
    return function() {
        return method.apply(object, arguments)
    }
}


function vpa_ajax()
{
    this.responseText='';
    this.loadStatus;
    this.getter=null;
    this.func=null; // func for backlink action (this function will be exec when XML data is loaded)
    
    this.makeRequest=function (url,func,getter) {
        this.getter = getter;
        this.http_request = false;
        this.func=func;
        if (window.XMLHttpRequest) { // Mozilla, Safari,...
            this.http_request = new XMLHttpRequest();
            if (this.http_request.overrideMimeType) {
                this.http_request.overrideMimeType('text/xml');
                // See note below about this line
            }
        } else if (window.ActiveXObject) { // IE
            try {
                this.http_request = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                try {
                    this.http_request = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {}
            }
        }
        if (!this.http_request) {
            throw new Error('Cannot create an XMLHTTP instance. You shutdown ActiveX ?\n Compatibility: IE 5.0, Mozilla 1.7, Firefox 1.0, Opera 8.0');
            return false;
        }
        this.http_request.onreadystatechange = this.alertContents;
        this.http_request.open('GET', url, true);
        this.http_request.send(null);
    }
    
    this.makeRequestPost=function (url,func,data) {
        this.http_request = false;
        this.func=func;
        if (window.XMLHttpRequest) { // Mozilla, Safari,...
            this.http_request = new XMLHttpRequest();
            if (this.http_request.overrideMimeType) {
                this.http_request.overrideMimeType('text/xml');
                // See note below about this line
            }
        } else if (window.ActiveXObject) { // IE
            try {
                this.http_request = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                try {
                    this.http_request = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {}
            }
        }

        if (!this.http_request) {
            alert('Cannot create an XMLHTTP instance. You shutdown ActiveX ?\n Compatibility: IE 5.0, Mozilla 1.7, Firefox 1.0, Opera 8.0');
            return false;
        }
        this.http_request.onreadystatechange = this.alertContents;
        this.http_request.open('POST', url, true);
        this.http_request.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
        this.http_request.send(data);
    }

    this.alertContents=function (e) {
        var ajax=this;//window['storage'].ajax;
        var http_request=this.http_request;
        this.send_status(http_request.readyState);
        if (http_request.readyState == 4) {
            if (http_request.status == 200) {
                this.responseText=http_request.responseText;
                if(http_request.responseXML && http_request.responseXML.documentElement == null)
                {
                    try
                    {
                        http_request.responseXML.loadXML(http_request.responseText)
                    } catch (e) {}
                }
				this.func(http_request.responseText,http_request.responseXML);
            } else {
                this.loadStatus=http_request.status;
                this.func('',null);
            }
        }
    }.bind(this)
    
    this.send_status=function(status)
    {
        window.status=status;
    }
}

vpa_ajax.prototype.toString=function () { return 'vpa_ajax'; }

