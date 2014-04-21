var METABAR_SITEBAR = new function () {
    this.settings = {"toolbar_id": 1145663, "host": "metabar.ru", "html": "<!-- Custom --> <div id=\"metabar_headcrab\" class=\"sitebar sitebar__custom\"> <div class=\"sitebar__bg\"></div> <div class=\"sitebar__shdw\"></div> <a href=\"#\" onclick=\"window.parent.METABAR_SITEBAR.removeSitebar(true); return false;\" class=\"sitebar__close\" title=\"\u0417\u0430\u043a\u0440\u044b\u0442\u044c\">\u0417\u0430\u043a\u0440\u044b\u0442\u044c</a> <a href=\"http://popcornnews.metabar.ru/?source=sitebar\" target=\"_top\" class=\"sitebar__link\" id=\"mbr-downlad-url\"> <span class=\"sitebar__cnt\"> <span class=\"sitebar-label\"><span class=\"sitebar-label__t\">\u0421 \u043d\u0430\u0448\u0438\u043c \u043f\u0440\u0438\u043b\u043e\u0436\u0435\u043d\u0438\u0435\u043c \u0432\u0441\u0435 \u0433\u043b\u0430\u0432\u043d\u044b\u0435 \u0438\u043d\u0441\u0442\u0440\u0443\u043c\u0435\u043d\u0442\u044b \u0438\u043d\u0442\u0435\u0440\u043d\u0435\u0442\u0430 \u0431\u0443\u0434\u0443\u0442 \u0443 \u0432\u0430\u0441 \u043f\u043e\u0434 \u0440\u0443\u043a\u043e\u0439</span></span> <span class=\"sitebar-button\"><span class=\"sitebar-button__cnt\"><i class=\"sitebar-button__l\"></i><i class=\"sitebar-button__r\"></i>\u0423\u0441\u0442\u0430\u043d\u043e\u0432\u0438\u0442\u044c</span></span> </span> </a> </div>", "opera_allowed": true, "clickable": true, "fixed_position": true, "chrome_allowed": true, "css": "* {margin:0; padding:0; border:0;} html, body {background:transparent;} /* Sitebar common */ .sitebar {overflow:hidden; position:relative; width:100%; min-width:800px; font:13px/40px 'Trebuchet MS',Arial,sans-serif;} .sitebar, .sitebar * {height:45px; overflow:hidden; cursor:pointer;} .sitebar__bg, .sitebar__shdw, .sitebar__close, .sitebar-button__cnt, .sitebar-button__l, .sitebar-button__r {background:url(http://design.metabar.ru/sitebar/sitebar7-custom.png) 0 0 no-repeat;} .sitebar__bg, .sitebar__shdw {width:100%; position:absolute; left:0; right:0;} .sitebar__bg {height:41px; top:0; z-index:1; background:#f70080;} .sitebar__shdw {height:4px; bottom:0; background-position:0 -41px; background-repeat:repeat-x;} .sitebar__close, .sitebar__logo, .sitebar-button__l, .sitebar-button__r {position:absolute; top:0;} .sitebar__close {right:9px; text-indent:-9999px; width:28px; z-index:99; background-position:-18px -45px; display:block;} .sitebar__logo {left:0; height:41px; z-index:98; background-position:0 -135px;} .sitebar__link {margin-right:46px; position:relative; z-index:100; *zoom:1; cursor:pointer; text-decoration:none; white-space:nowrap; overflow:visible; text-align:center; display:block;} .sitebar__cnt {left:23px; position:relative;} .sitebar__cnt, .sitebar-label, .sitebar-button, .sitebar-button__cnt {display:-moz-inline-stack; display:inline-block; *display:inline; *zoom:1; vertical-align:top;} .sitebar-label {margin-right:12px;} .sitebar-label__t, .sitebar-button {line-height:316%; font-size:0.99em;} .sitebar-label__t {color: #fff;} .sitebar-button {padding:0 9px; position:relative; font-family:Arial,sans-serif; line-height:300%;} .sitebar-button__cnt {background-repeat:repeat-x; background-position:0 -90px; color:#575757 !important; text-shadow:0 1px 0 #e4e4e4; padding:0 10px 0 12px; overflow:visible;} .sitebar-button__l, .sitebar-button__r {width:9px;} .sitebar-button__l {background-position:0 -45px; left:0;} .sitebar-button__r {background-position:-9px -45px; right:0;}", "height": 30};

    this.settings.clickable = this.settings.clickable === undefined ? true :
        this.settings.clickable;

    this.iframe = null;
    this.iframeDocument = null;

    this.setCookie = function (name, value, expires, path, domain, secure) {
        document.cookie = name + "=" + escape(value) +
            ((expires) ? "; expires=" + expires : "") +
            ((path) ? "; path=" + path : "") +
            ((domain) ? "; domain=" + domain : "") +
            ((secure) ? "; secure" : "");
    }

    this.getCookie = function (name) {
        var cookie = " " + document.cookie;
        var search = " " + name + "=";
        var setStr = null;
        var offset = 0;
        var end = 0;
        if (cookie.length > 0) {
            offset = cookie.indexOf(search);
            if (offset != -1) {
                offset += search.length;
                end = cookie.indexOf(";", offset);
                if (end == -1) {
                    end = cookie.length;
                }
                setStr = unescape(cookie.substring(offset, end));
            }
        }
        return (setStr);
    }


    this.removeSitebar = function (track, daysHide) {
        daysHide = daysHide || 90;
        if (!this.iframe) return;
        if (track && this.iframeDocument) {
            this.iframeDocument.write("<img style='display:block; position:absolute;' src='" + 'http://' + this.settings.host + "/stats/add_sitebar_close_stat/?toolbar_id=" + this.settings.toolbar_id + "' />");
        }
        var expires = new Date();
        expires.setDate(expires.getDate() + daysHide);
        this.setCookie('hide_metabar_sitebar', 'true', expires.toGMTString(), '/', null, null);
        this.iframe.parentNode.removeChild(this.iframe);

        /* sitebar in top - unfix top margin */
        if (this.settings.verticalPosition !== "bottom") {
            this.unfixTopMargin();
        }
    };

    this.setAnotherHtmlSitebar = function() {
        if (this.iframeDocument) {
            if (this.anotherToolbarHtml) {
                var el = this.iframeDocument.getElementById('metabar_headcrab');
                el.innerHTML = this.anotherToolbarHtml;
            }
        }
    };

    this.modyfySitebarLinks = function(toolbar_id) {
        if (this.iframeDocument) {
            var link = this.iframeDocument.getElementById("mbr-downlad-url");
            try {
                var campaign = "&utm_source=" +
                    window.location.host +
                    "&utm_medium=sitebar&utm_campaign=add_widgets";
            } catch(ex) {
                return;
            }

            if (this.IS_IE || this.IS_FF) {
                link.href = "http://" + this.settings.host + "/account/edit_toolbar/" +
                    toolbar_id +
                    "/?from_toolbar=1&combined_toolbar_id=" +
                    this.settings.toolbar_id + campaign;
            } else {
                link.href += campaign;
                href = link.href;
            }
            var self = this;
            link.onclick = function() {
                self.removeSitebar(false);
                window.location.replace(link.href);
                return false;
            };
        }
    };

    this.setAnotherToolbarSitebar = function(toolbar_id) {
        this.setAnotherHtmlSitebar();
        this.modyfySitebarLinks(toolbar_id);
    };

    this.getIframeDocument = function (iframeNode) {
        if (iframeNode.contentDocument) return iframeNode.contentDocument
        if (iframeNode.contentWindow) return iframeNode.contentWindow.document
        return iframeNode.document
    }

    this.getTopMargin = function (node) {
        if (window.getComputedStyle) {
            return Number(window.getComputedStyle(node, null).marginTop.replace("px", ""));
        }
        else if (node.currentStyle) {   //IE
            if (node.currentStyle["marginTop"]) {
                var margin = node.currentStyle["marginTop"];
                if (/^\d+(px)?$/.test(margin)) {
                    return Number(margin.replace("px", ""));
                }
                return 0;                
            }
        }
        return 0;
    }

    this.isPinnedSitesMode = function () {
        try {
            if (window.external.msIsSiteMode()) {
                return true;
            }
        } catch (ex) {
            //
        }
        return false;
    }

    this.isQuirksMode = function () {
        return "BackCompat" == document.compatMode;
    }

    this.fixTopMargin = function () {
        var topMargin = this.settings.height;

        if (this.isQuirksMode()) {
            topMargin += this.getTopMargin(document.body) - 4; // 4 is height of the shadow
            document.body.style.marginTop = "" + topMargin + "px";
        } else {
            var html = document.body.parentNode;
            topMargin += this.getTopMargin(html) - 4;
            html.style.marginTop = "" + topMargin + "px";
        }
    }

    this.unfixTopMargin = function () {
        var topMargin;

        if (this.isQuirksMode()) {
            topMargin = Math.max(0, this.getTopMargin(document.body) - this.settings.height) - 4;
            document.body.style.marginTop = "" + topMargin + "px";
        } else {
            var html = document.body.parentNode;
            topMargin = Math.max(0, this.getTopMargin(html) - this.settings.height) - 4;
            html.style.marginTop = "" + topMargin + "px";
        }
    }

    this.addGAParams = function() {
        var campaign = "&utm_source=" +
            window.location.host +
            "&utm_medium=sitebar&utm_campaign=install_app";
        try {
            var link = this.iframeDocument.getElementById("mbr-downlad-url");
            if (link && /^http:\/\/.+?\.metabar\.ru/.test(link.href)) {
                link.href += campaign;
            }
        } catch(ex) {}
    }

    this.inject = function () {
        try {
            if (null == document.body) {
                document.write("<b id='metabar-loader'>M</b>");
                var loader = document.getElementById("metabar-loader");
                loader.parentNode.removeChild(loader);
            }

            this.IS_IE = /msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent);
            this.IS_FF = /Firefox[\/\s](\d+\.\d+)/i.test(navigator.userAgent);
            this.IS_OPERA = /opera/i.test(navigator.userAgent);
            this.IS_CHROME = /Chrome/i.test(navigator.userAgent);
            this.IS_WIN = /win/i.test(navigator.platform);
            var popupRE = /^Metabar_/;

            if ((this.IS_WIN || this.IS_FF) && (this.IS_IE || this.IS_FF || (this.IS_OPERA && this.settings.opera_allowed) || (this.IS_CHROME && this.settings.chrome_allowed)) && !this.getCookie('hide_metabar_sitebar')
                && !popupRE.test(window.name) && (window.parent == window)) {
                this.iframe = document.createElement("iframe");

                var position = (this.settings.fixed_position && !(this.IS_IE && this.isQuirksMode())) ? "fixed" : "absolute";
                this.iframe.style.position = position;
                /*if sitebar in top - set this.iframe.style.top to 0px. Else - set this.iframe.style.bottom to 0px */
                var verticalVariable = this.settings.verticalPosition === "bottom" ? "bottom" : "top";
                this.iframe.style[verticalVariable] = "0px";
                this.iframe.style.left = "0px";
                this.iframe.style.overflow = "hidden";
                this.iframe.style.zIndex = "1000000";
                this.iframe.style.border = "0px";
                this.iframe.style.backgroundColor = "transparent";

                var iframeShadowStyle = "#METABAR_IFRAME {\
                    -moz-box-shadow: 0 0 4px rgba(0,0,0,0.50);\
                    -khtml-box-shadow: 0 0 4px rgba(0,0,0,0.50);\
                    -webkit-box-shadow:0 0 4px rgba(0,0,0,0.50);\
                    -o-box-shadow:0 0 4px rgba(0,0,0,0.50);\
                    box-shadow:0 0 4px rgba(0,0,0,0.50);\
                }";
                try {
                    var styleElement = document.createElement("style");
                    styleElement.type = "text/css";
                    if (styleElement.stylesheet) {
                        styleElement.stylesheet.cssText = iframeShadowStyle;
                    } else {
                        var node = document.createTextNode(iframeShadowStyle);
                        styleElement.appendChild(node);
                    }
                    document.getElementsByTagName("head")[0].appendChild(styleElement);
                } catch (e) {
                    var stylesheet = document.createStyleSheet();
                    stylesheet.cssText = iframeShadowStyle;
                }


                this.iframe.setAttribute("frameborder", "0");
                this.iframe.setAttribute("marginwidth", "0");
                this.iframe.setAttribute("marginheight", "0");
                this.iframe.setAttribute("hspace", "0");
                this.iframe.setAttribute("vspace", "0");
                this.iframe.setAttribute("width", "100%");
                this.iframe.setAttribute("height", this.settings.height + "px");
                this.iframe.setAttribute("scrolling", "no");
                this.iframe.setAttribute("allowtransparency", "true");

                this.iframe.id = "METABAR_IFRAME";

                var self = this;
                this.testMetabar = document.createElement("div");
                this.testMetabar.style.display = "none";
                this.testMetabar.id = "TEST_METABAR";
                this.testMetabar.onclick = function () {
                    self.set_metabar_installed.apply(self, self.testMetabar.getAttribute("name").split("//"));
                };

                document.body.appendChild(this.testMetabar);

                document.body.appendChild(this.iframe);

                this.iframeDocument = this.getIframeDocument(this.iframe);

                //var body_class = IS_IE ? "ie" : (IS_FF ? "ff" : "");
                if (this.IS_IE){
                    body_class = 'ie';
                    if (navigator.userProfile) {
                        body_class += " ie6";
                    } else if (document.all && !document.querySelector) {
                        body_class += " ie7";
                    } else if (document.all && document.querySelector && !document.addEventListener) {
                        body_class += " ie8";
                    } else if (window.atob) {
                        body_class += " ie10";
                    } else {
                        body_class += " ie9";
                    }
                }
                else if (this.IS_FF){
                    body_class = 'ff';
                }
                else if (this.IS_OPERA){
                    body_class = 'opera';
                }
                else if (this.IS_CHROME){
                    body_class = 'chrome';
                }
                else { 
                    body_class = '';
                }
                var stats_img = "<img style='position:absolute;top:-10000px;' alt='' src='http://stats." + this.settings.host + "/blank.gif?toolbar_id=" + this.settings.toolbar_id + "' />";

                if (/<!-- TOOLBAR -->(.*)<!-- END_TOOLBAR -->/.test(this.settings.html)) {
                    this.anotherToolbarHtml = /<!-- TOOLBAR -->(.*)<!-- END_TOOLBAR -->/.exec(this.settings.html)[1];
                }

                var headcrabHTML = "";

                var oldInstalledToolbarId = this.getCookie("metabar_installed_id");
                if (this.anotherToolbarHtml && oldInstalledToolbarId) {
                    headcrabHTML = this.anotherToolbarHtml;
                } else if (this.IS_IE && /<!-- IE -->(.*)<!-- END_IE -->/.test(this.settings.html)) {
                    headcrabHTML = /<!-- IE -->(.*)<!-- END_IE -->/.exec(this.settings.html)[1];
                }
                else if (this.IS_FF && /<!-- FF -->(.*)<!-- END_FF -->/.test(this.settings.html)) {
                    headcrabHTML = /<!-- FF -->(.*)<!-- END_FF -->/.exec(this.settings.html)[1];
                }
                else if (this.IS_OPERA && /<!-- OPERA -->(.*)<!-- END_OPERA -->/.test(this.settings.html)) {
                    headcrabHTML = /<!-- OPERA -->(.*)<!-- END_OPERA -->/.exec(this.settings.html)[1];
                }
                else if (this.IS_CHROME && /<!-- CHROME -->(.*)<!-- END_CHROME -->/.test(this.settings.html)) {
                    headcrabHTML = /<!-- CHROME -->(.*)<!-- END_CHROME -->/.exec(this.settings.html)[1];
                } else {
                    headcrabHTML = this.settings.html;
                }


                this.iframeDocument.write(headcrabHTML + stats_img + "<style>" + this.settings.css + "</style>");
                this.iframeDocument.body.className += " " + body_class;
                this.iframeDocument.close();

                /* sitebar in top - fix top margin */
                if (this.settings.verticalPosition !== "bottom") {
                    this.fixTopMargin();
                }

                if (this.settings.clickable) {
                    this.iframeDocument.body.className += " " + "sitebar__pointer";
                    this.iframeDocument.body.onclick = function() {
                        var link = self.iframeDocument.getElementById("mbr-downlad-url");
                        if (link) {
                            link.click();
                        }
                    }
                }
                this.addGAParams();

                if (oldInstalledToolbarId) {
                    this.modyfySitebarLinks(oldInstalledToolbarId);
                }
            }
        } catch (e) {
            // do nothing
        }
    }

    if (!this.isPinnedSitesMode()) {
        this.inject();
    }

    this.set_metabar_installed = function (toolbar_id, version, source_id) {
        if (toolbar_id == this.settings.toolbar_id || source_id == this.settings.toolbar_id) {
            this.removeSitebar(false);
        }
        else {
            if (this.getCookie('metabar_installed_id') != toolbar_id) {
                this.setAnotherToolbarSitebar(toolbar_id);
            }
            /* update toolbar id and expires date */
            var expires = new Date();
            expires.setDate(expires.getDate() + 90);
            this.setCookie('metabar_installed_id', toolbar_id, expires.toGMTString(), '/', null, null);
        }
    }

    var self = this;
    window.set_metabar_installed = function () {
        self.set_metabar_installed.apply(self, arguments);
    }
}
