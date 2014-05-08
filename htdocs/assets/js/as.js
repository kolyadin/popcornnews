var as = (function() {
    var d = document;
    var w = window;
    var $ = function(id) {return d.getElementById(id)};
    var btn = function(tn,pn) {return pn ? pn.getElementsByTagName(tn) : d.getElementsByTagName(tn)};
    var sss = /^[A-Za-z]+$/;
    var attrsRE = /\[(([^\]]+=['"][^'"]+['"]\])|([^\]]+\]))/gi;
    function camelize(string) {
        var parts = string.split("-"),
            camelized = parts[0];
        for (var i=1;i<parts.length;i++) {
            camelized += parts[i].substring(0,1).toUpperCase() + parts[i].substring(1);
        }
        return camelized;
    }
    /*** EVENTS ***/
    Event = (function() {
        var guid = 0;
        function fixEvent(event) {
            event = event || window.event;
            if (event.isFixed) return event;
            event.isFixed = true;
            event.preventDefault = event.preventDefault || function(){this.returnValue = false}
            event.stopPropagation = event.stopPropagaton || function(){this.cancelBubble = true}
            if (!event.target) event.target = event.srcElement;
            if (!event.relatedTarget && event.fromElement) event.relatedTarget = event.fromElement == event.target ? event.toElement : event.fromElement;
            if (event.pageX == null && event.clientX != null) {
                var html = document.documentElement, body = document.body;
                event.pageX = event.clientX + (html && html.scrollLeft || body && body.scrollLeft || 0) - (html.clientLeft || 0);
                event.pageY = event.clientY + (html && html.scrollTop || body && body.scrollTop || 0) - (html.clientTop || 0);
            }
            if (!event.which && event.button ) event.which = (event.button & 1 ? 1 : ( event.button & 2 ? 3 : ( event.button & 4 ? 2 : 0 )));
            return event;
        }
        function commonHandle(event, index) {
            event = fixEvent(event);
            var handlers = this.events[event.type];
            for ( var g in handlers ) {
                var handler = handlers[g];
                var ret = handler.call(this, event, index);
                if (ret === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }
        }
        return {
            add: function(elem, type, handler, index) {
                if (elem.setInterval && (elem != window && !elem.frameElement)) elem = window;
                if (!handler.guid) handler.guid = ++guid;
                if (!elem.events) {
                    elem.events = {};
                    elem.handle = function(event) {
                        if (typeof Event !== "undefined") return commonHandle.call(elem, event, index);
                    }
                }
                if (!elem.events[type]) {
                    elem.events[type] = {};
                    if (elem.addEventListener) elem.addEventListener(type, elem.handle, false);
                    else if (elem.attachEvent) elem.attachEvent("on" + type, elem.handle);
                }
                elem.events[type][handler.guid] = handler;
            },
            remove: function(elem, type, handler) {
                var handlers = elem.events && elem.events[type];
                if (!handlers) return;
                if (!handler) {
                    delete elem.events[type];
                    return;
                }
                delete handlers[handler.guid];
                for (var any in handlers) return;
                if (elem.removeEventListener) elem.removeEventListener(type, elem.handle, false);
                else if (elem.detachEvent) elem.detachEvent("on" + type, elem.handle);
                delete elem.events[type];
                for (var any in elem.events) return;
                //delete elem.handle;
                //delete elem.events;
            },
            cancelEvent: function(e,fullCancel) {
                e.preventDefault ? e.preventDefault() : e.returnValue = false;
                fullCancel && Event.stopProp(e);
            },
            stopProp: function(e) {
                e.stopPropagation ? e.stopPropagation() : e.cancelBubble = true;
            }
        }
    }());
    var e = {};
    (function(){
        foreach(
            ["click","mouseover","mouseup","mouseout","mousedown","mousemove","keyup","keydown","keypress","resize","select","submit","load","abort","blur","focus","abort","unload","change","error","reset","resize"],
            function(ename){
                e[ename] = function(el,hn,context,i) {Event.add(el,ename,context?as.bind(hn,context):hn,i);return el;}
                e["de"+ename] = function(el,hn,context) {Event.remove(el,ename,context?as.bind(hn,context):hn);return el;}
            }
        );
        function getDirection(e) {
            return (e.detail && (e.detail > 0 ? "down" : "up")) || (e.wheelDelta && (e.wheelDelta < 0 ? "down" : "up")) || null;
        }
        e["mwheel"] = function(el,hn,context,opts) {
            var mwf = function(e) {
                var dr = getDirection(e);
                if (opts && opts.direction && opts.direction != dr) return;
                context ? hn.call(context,e,dr) : hn(e,dr);
                (opts && opts.noCancel) || Event.cancelEvent(e,true);
            }
            Event.add(el,"mousewheel",mwf);
            Event.add(el,"DOMMouseScroll",mwf);
        }
        e["demwheel"] = function(el) {
            Event.remove(el,"mousewheel");
            Event.remove(el,"DOMMouseScroll");
        }
        e["mwheelup"] = function(el,hn,context) {e.mwheel(el,hn,context,{direction:"up"})}
        e["mwheeldown"] = function(el,hn,context) {e.mwheel(el,hn,context,{direction:"down"})}
        e["demwheelup"] = e["demwheeldown"] = e["demwheel"];
    })();
    /***\\ EVENTS\\ ***/
    function getXHR() {
        var xhr = null;
        try {xhr = new ActiveXObject("Msxml2.XMLHTTP");}
        catch(e) {
            try {xhr = new ActiveXObject("Microsoft.XMLHTTP");}
            catch(e2) {xhr = false}
        }
        if (!xhr && typeof XMLHttpRequest!='undefined') xhr = new XMLHttpRequest();
        return xhr;
    }
    var cache = {};
    var ready = {
        fnList: [],
        add: function(fnList) {
            for (var i=0;i<fnList.length;i++) {
                var fn = fnList[i], inList = false;
                for (var j=0;j<this.fnList.length;j++) {
                    if (fn == this.fnList[j]) {inList=true;break;}
                }
                !inList && this.fnList.push(fn);
            }
        },
        init: function() {
            /* for Mozilla */
            if (document.addEventListener) {
                document.addEventListener("DOMContentLoaded", function(){as.onready();}, false);
                ready.dcl = true;
            }
            /* for Internet Explorer */
            /*@cc_on @*/
            /*@if (@_win32)
             (function () {
             var t = document.createElement('doc:rdy');
             try {
             t.doScroll('left');
             as.onready();
             } catch (e) {
             function() {setTimeout(arguments.callee,0)}
             }
             })();
             /*@end @*/
            /* for Safari */
            if (/WebKit/i.test(navigator.userAgent)) { // sniff
                if (ready.dcl) { return; }
                var _timer = setInterval(function() {
                    if (/loaded|complete/.test(document.readyState)) {
                        as.onready(); // call the onload handler
                        clearInterval(_timer);
                    }
                }, 10);
            }
            /* for other browsers */
            window.onload = function(){as.onready();};
        },
        initFn: function() {
            for (i=0;i<this.fnList.length;i++) {
                this.fnList[i]();
            }
            ready.dcl = "undefined";
        }
    }
    /*** SELECTORS ***/
    function simpleCreate(element) {
        return document.createElement(element);
    }
    function html(element,html) {
        if (html) {element.innerHTML = html; return element;}
        else {return element.innerHTML}
    }
    function simpleSelector(selector,pn) {
        if (selector.match(sss)) return btn(selector,pn);
        var data = issData(selector);
        return filter(
            data.tn ? btn(data.tn,pn) : btn("*",pn),
            function (element) {
                return iss(element,data);
            }
        );
    }
    function issData(selector) {
        var tn = selector.match(/(^| )[^.:\[\]#]+/); tn && (tn = tn[0]);
        var id = selector.match(/#[^\[\].#]+/); id && (id = id[0].replace("#",""));
        var cns = selector.match(/\.[^\[\].#]+/gi);
        if (cns) {for (var i=cns.length-1;i>=0;i--) {cns[i] = cns[i].replace(".","")}}
        var attrs = selector.match(attrsRE);
        if (attrs) {for (var i=attrs.length-1;i>=0;i--) {attrs[i] = attrTest(attrs[i])}}
        return {tn: tn, id: id, cns: cns, attrs: attrs}
    }
    function iss(element,data) {
        var tn = data.tn, id = data.id, cns = data.cns, attrs = data.attrs, eAttr;
        if (tn && element.tagName.toLowerCase() != tn) return false;
        if (id && element.id != id) return false;
        if (cns) {
            for (var i=cns.length-1;i>=0;i--) {
                if ((" "+element.className+" ").indexOf(" "+cns[i]+" ") == -1) return false;
            }
        }
        if (attrs) {
            for (var i=attrs.length-1;i>=0;i--) {
                eAttr = element.getAttribute(attrs[i].name);
                if (eAttr == null) return false;
                if (attrs[i].value != null) {
                    if (attrs[i].startsWith) {
                        if (eAttr.indexOf(attrs[i].value) == -1) return false;
                    }
                    else if (attrs[i].notEqual) {
                        if (eAttr == attrs[i].value) return false;
                    }
                    else {
                        if (eAttr != attrs[i].value) return false;
                    }
                }
            }
        }
        return true;
    }
    function is(element,selector) {
        var s = true;
        var tn = selector.match(/(^| )[^\[\].*#]+/);
        var id = selector.match(/#[^\[\].#]+/);
        var cns = selector.match(/\.[^\[\].#]+/gi);
        var attrs = selector.match(attrsRE);
        tn && (element.tagName.toLowerCase() != tn[0]) && (s = false);
        id && (element.id != id[0].replace("#","")) && (s = false);
        cns && map(cns,function(cn){return cn.replace(".","")});
        cns && foreach(
            cns,
            function(cn) {
                (" "+element.className+" ").indexOf(" "+cn+" ") == -1 && (s = false);
            }
        );
        attrs && map(attrs,function(attr) {return attrTest(attr)});
        attrs && foreach(
            attrs,
            function(attr) {
                var eAttr = element.getAttribute(attr.name);
                if (eAttr == null) { s = false; return; }
                if (attr.value != null) {
                    if (attr.startsWith) {
                        if (eAttr.indexOf(attr.value) == -1) { s = false; return; }
                    }
                    else if (attr.notEqual) {
                        if (eAttr == attr.value) { s = false; return; }
                    }
                    else {
                        if (eAttr != attr.value) { s = false; return; }
                    }
                }
            }
        );
        return s;
    }
    function attrTest(rawAttr) {
        var name = null, value = null, notEqual = rawAttr.indexOf("!") != -1, startsWith = rawAttr.indexOf("~") != -1, index = rawAttr.indexOf("=");
        if (index != -1) {
            name = rawAttr.match(/\w+/)[0];
            value = rawAttr.substring(index + 2, rawAttr.length - 2);
        }
        else {
            name = rawAttr.substring(1, rawAttr.length - 1);
        }
        return {name: name, value: value, startsWith: startsWith, notEqual: notEqual};
    }
    /***\\ SELECTORS \\ ***/
    /*** ARRAY FUNCTIONS ***/
    function filter(set,fn) {
        if (!set) return [];
        var nset = [], l=set.length, i=0;
        while(set && l>i) {
            if (fn(set[i],i)) { nset.push(set[i]) }
            i++;
        }
        return nset;
    }
    function map(set,fn) {
        if (!set) return [];
        var l=set.length, i=0;
        while(set && l>i) {
            set[i] = fn(set[i],i);
            i++;
        }
        return set;
    }
    function foreach(set,fn) {
        if (!set) return [];
        if (set.forEach) { set.forEach(fn); return; }
        var l=set.length, i=0;
        while(set && l>i) {
            fn(set[i],i);
            i++;
        }
        return set;
    }
    /*\\ ARRAY FUNCTIONS \\*/
    /* STYLES */
    function getStyle(el,prop) {
        if (el.currentStyle) {
            return el.currentStyle[camelize(prop)];
        }
        else if (window.getComputedStyle) {
            return document.defaultView.getComputedStyle(el,null).getPropertyValue(prop);
        }
        else return null;
    }
    /*\\ STYLES \\*/
    ready.init();
    /*** PLUGINS ***/
    var Plugin = function() {}
    Plugin.prototype = {};
    var pluginStore = {urls:{},plugins:{}};
    /*** WRAPPER ***/
    function Wrapper(set) {
        this.set = (set === undefined) ? [] : (((set.length || set.length===0) && !set.tagName) ? set : [set]);
    }
    Wrapper.prototype = {
        each: function(fn) {
            foreach(this.set,function(item,i){
                fn.call(item,i);
            });
            return this;
        },
        filter: function(fn) {
            this.set = filter(this.set,function(item,i){
                return fn.call(item,i);
            });
            return this;
        },
        html: function(html) {
            return html ? this.each(function(){this.innerHTML = html}) : this.set.length ? this.first().innerHTML : "";
        },
        length: function() {
            return this.set.length;
        },
        first: function() {
            return this.set[0];
        },
        last: function() {
            return this.set[this.set.length-1];
        },
        remove: function() {
            while (this.set.length) {
                as.remove(this.set[0]);
            }
        },
        append: function(element,toAll) {
            toAll ? this.each(function() {as.append(element,this)}) : as.append(element,this.first());
        },
        prepend: function(element,toAll) {
            toAll ? this.each(function() {as.prepend(element,this)}) : as.prepend(element,this.first());
        },
        next: function(ofAll) {
            return as.next(this.first());
        },
        prev: function(ofAll) {
            return as.prev(this.first());
        }
    };
    (function() {
        for (var ename in e) {
            (function(ename) {
                Wrapper.prototype[ename] = function(fn,context) {
                    this.each(function(i) {
                        as.e[ename](this,fn,context,i);
                    });
                    return this;
                }
            })(ename);
        }
    })();
    return {
        w: function(selector,pn,fc) {
            return typeof arguments[0] == "string" ? new Wrapper(as.$(selector,pn,fc)) : new Wrapper(arguments[0]);
        },
        $: function(selector,pn,fc) {
            if (fc && !pn && cache[selector]) { return cache[selector]; }
            if (selector.match(sss)) return cache[selector] = btn(selector,pn);
            //pn = (pn ? (pn.length ? pn : [pn]) : null);
            var parts = selector.split(" "), set = [], endElements = simpleSelector(parts[parts.length-1],pn), tempData = [];
            for (var i=parts.length-2;i>=0;i--) {
                tempData[i] = issData(parts[i]);
            }
            foreach(
                endElements,
                function(element) {
                    if (parts.length < 2) {
                        if (!pn) {set.push(element);return;}
                        var oe = element;
                        while (element.parentNode) {
                            element = element.parentNode;
                            //if(as.filter(pn,function(pn) {return pn === element;}).length) {set.push(oe);return;}
                            if (pn === element) {set.push(oe);return;}
                        }
                        return;
                    }
                    var c = parts.length - 2;
                    (function(element,c) {
                        var data = tempData[c];
                        var s = false, f, element = element.parentNode;
                        while (element.parentNode) {
                            if (iss(element,data)) {
                                s = true;
                                f = element;
                                break;
                            }
                            element = element.parentNode;
                        }
                        if (!s) {return false;}
                        if (c===0) {
                            if (!pn) {return true;}
                            while (element.parentNode) {
                                element = element.parentNode;
                                //if(as.filter(pn,function(pn) {return pn === element;}).length) {return true;}
                                if (pn === element) {return true;}
                            }
                            return false;
                        }
                        return arguments.callee(f,--c);
                    })(element,c) && set.push(element);
                }
            );
            return cache[selector] = set;
        },
        $$: function(selector,pn,fc) {
            return this.$(selector,pn,fc)[0];
        },
        onready: function() {
            if (arguments.callee.done) return;
            arguments.callee.done = true;
            ready.initFn();
        },
        ready: function() {
            ready.add(arguments);
        },
        filter: filter,
        map: map,
        foreach: foreach,
        e: e,
        cancelEvent: Event.cancelEvent,
        stopProp: Event.stopProp,
        is: is,
        parent: function(element,ss) {
            element = element.parentNode;
            while(element.parentNode) {
                if (typeof ss == "string") {
                    if (is(element,ss)) {return element;}
                }
                else {
                    if (element == ss) {return element;}
                }
                element = element.parentNode;
            }
            return false;
        },
        clearCache: function(selector) {
            if (selector) {cache[selector] = null;}
            else {
                for (selector in cache) {
                    cache[selector] = null;
                }
            }
        },
        /*** PLUGINS ***/
        observePlugins: function() {
            map(this.$(".as-plugin"),function(rq) {
                if (!rq.onclick) {return;}
                var url = rq.onclick().require || null;
                var pluginId = rq.onclick().pluginId;
                var params = rq.onclick().params || {};
                url && (pluginStore.urls[url] || (pluginStore.urls[url] = true));
                pluginStore.plugins[pluginId] ?
                    (pluginStore.plugins[pluginId].elementsList ?
                        pluginStore.plugins[pluginId].elementsList.push({element: rq, params: params}) :
                        pluginStore.plugins[pluginId].elementsList = [{element: rq, params: params}]) :
                    pluginStore.plugins[pluginId] = {elementsList: [{element: rq, params: params}]};
                as.onpluginload(pluginId);
            });
            for (var url in pluginStore.urls) {
                as.append("script",as.$$("head"),"",{type: 'text/javascript', src: url});
            }
        },
        onpluginload: function(pluginId) {
            if (!pluginStore.plugins[pluginId] || !pluginStore.plugins[pluginId].elementsList || !pluginStore.plugins[pluginId].pluginConstructor) {return;}
            as.map(pluginStore.plugins[pluginId].elementsList,function(item) {
                new pluginStore.plugins[pluginId].pluginConstructor().init(item.element,item.params);
            });
        },
        Plugin: function(pluginId,pluginConstructor) {
            var plugin = function() {};
            plugin.prototype = new Plugin(pluginId,pluginConstructor);
            for (var i in pluginConstructor.prototype) {
                plugin.prototype[i] = pluginConstructor.prototype[i];
            }
            pluginStore.plugins[pluginId] ?
                pluginStore.plugins[pluginId].pluginConstructor = pluginConstructor :
                pluginStore.plugins[pluginId] = {pluginConstructor: pluginConstructor};
            this.onpluginload(pluginId);
            return plugin;
        },
        /*** DOM FUNCTIONS ***/
        create: function(element,ih,opts) {
            try {
                element = (typeof element == "string") ? (element.indexOf("<") != -1 ? this.raw(element) : document.createElement(element)) : element;
                ih && (element.innerHTML = ih);
                for (var opt in opts) {
                    tpo = (opt == "class") ? "className" : opt;
                    element[tpo] = opts[opt];
                }
            }
            catch(e) {};
            return element;
        },
        raw: function(string) {
            return html(simpleCreate("div"),string).firstChild;
        },
        append: function(element,cnt,ih,opts) {
            element = this.create(element,ih,opts);
            try {
                cnt.appendChild(element);
            }
            catch(e) {};
            return element;
        },
        prepend: function(element,cnt,ih,opts) {
            element = this.create(element,ih,opts);
            try {
                cnt.firstChild ? cnt.insertBefore(element,cnt.firstChild) : cnt.appendChild(element);
            }
            catch(e) {};
            return element;
        },
        before: function(element,before,ih,opts) {
            element = this.create(element,ih,opts);
            try {
                before.parentNode.insertBefore(element,before);
            }
            catch(e) {};
            return element;
        },
        after: function(element,after,ih,opts) {
            element = this.create(element,ih,opts);
            try {
                after.nextSibling ? after.parentNode.insertBefore(element,after.nextSibling) : after.parentNode.appendChild(element);
            }
            catch(e) {};
            return element;
        },
        remove: function(element) {
            try {element.parentNode.removeChild(element);}
            catch(e) {}
            return element;
        },
        next: function(element) {
            var ns = element.nextSibling;
            while (ns) {
                if (ns.nodeType == 1) return ns;
                ns = ns.nextSibling;
            }
            return null;
        },
        prev: function(element) {
            var ps = element.previousSibling;
            while (ps) {
                if (ps.nodeType == 1) return ps;
                ps = ps.previousSibling;
            }
            return null;
        },
        style: function(element,styles) {
            var retStyle = {};
            if (styles.length) {
                if (typeof styles == "string") return getStyle(element,styles);
                else {
                    for (var i=0,l=styles.length;i<l;i++) {
                        retStyle[styles[i]] = getStyle(element,styles[i]);
                    }
                    return retStyle;
                }
            }
            else {
                for (var style in styles) {
                    try {
                        element.style[camelize(style)] = styles[style];
                    }
                    catch(e) {}
                }
                return element;
            }
        },
        bind: function bind(fn,context) {
            return function() {
                return fn.apply(context,arguments);
            }
        },
        ajax: function(url,callback,type,data,timeout,headers,sync) {
            type = type || "GET";
            data = data || null;
            timeout = timeout || 10000;
            var xhr = getXHR();
            // @edited
            if (!sync) {
                xhr.open(type,url,true);
                headers && (foreach(headers,function(header){
                    xhr.setRequestHeader(header.name, header.value);
                }));
                xhr.send(data);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState != 4) return;
                    if (xhr.status != 200) return;
                    if (!callback) return;
                    if (xhr.responseXML && xhr.responseXML.firstChild) callback(xhr.responseXML);
                    else callback(xhr.responseText);
                }
            } else {
                xhr.open(type,url,false);
                headers && (foreach(headers,function(header){
                    xhr.setRequestHeader(header.name, header.value);
                }));
                xhr.send(data);

                if (xhr.readyState != 4) return;
                if (xhr.status != 200) return;
                if (!callback) return;
                // callback
                if (xhr.responseXML && xhr.responseXML.firstChild) callback(xhr.responseXML);
                else callback(xhr.responseText);
            }
        },
        fade: function(element,start,end,speed,callback) {
            if (/MSIE/.test(navigator.userAgent)) {
                start = start*100;
                end = end*100;
            }
            var diff = end - start;
            as.setOpacity(element,start);
            var animData = {opacity: []}
            for (var i=1;i<=speed;i++) {
                for (var ad in animData) {
                    animData[ad][i] = start+(diff/speed)*i;
                }
            }
            for (var i=1;i<=speed;i++) {
                (function(i) {
                    setTimeout(
                        function() {
                            as.setOpacity(element,animData.opacity[i]);
                            (i == speed) && callback && callback();
                        },i*12
                    );
                })(i)
            }
        },
        fadeIn: function(params) {
            params = params || {};
            speed = params.speed || 40;
            as.fade(params.element,0,1,speed,params.callback);
        },
        fadeOut: function(params) {
            params = params || {};
            speed = params.speed || 40;
            as.fade(params.element,1,0,speed,params.callback);
        },
        setOpacity: function(element,opacity) {
            if (/MSIE/.test(navigator.userAgent)) {
                element.style.filter = "alpha(opacity="+(opacity)+")";
            }
            else {
                element.style.opacity = opacity;
            }
        },
        getElementPosition: function(elem) {
            function getOffsetRect(elem) {
                var box = elem.getBoundingClientRect();
                var body = document.body;
                var docElem = document.documentElement;
                var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
                var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
                var clientTop = docElem.clientTop || body.clientTop || 0;
                var clientLeft = docElem.clientLeft || body.clientLeft || 0;
                var top  = box.top +  scrollTop - clientTop;
                var left = box.left + scrollLeft - clientLeft;
                return { top: Math.round(top), left: Math.round(left), width: elem.offsetWidth, height: elem.offsetHeight }
            }
            function getOffsetSum(elem) {
                var l = 0;
                var t = 0;
                var w = elem.offsetWidth;
                var h = elem.offsetHeight;
                while (elem) {
                    l += elem.offsetLeft;
                    t += elem.offsetTop;
                    elem = elem.offsetParent;
                }
                return {"left":l, "top":t, "width": w, "height": h};
            }
            if (elem.getBoundingClientRect) {
                return getOffsetRect(elem)
            } else {
                return getOffsetSum(elem)
            }
        }
    }
})();
as.ready(function(){as.observePlugins();});