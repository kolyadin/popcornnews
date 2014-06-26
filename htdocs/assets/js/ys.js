// немного ООП, которого не умеет jq
var _oop_ = {
    extend: (function(){
    function F() {}
    return function (parent, child, extend) {
      F.prototype = parent.prototype;
      child.prototype = new F;
      child.prototype.constructor = child;
      child.superproto = parent.prototype;
      
      for (var n in extend) child.prototype[n] = extend[n];
      return child;
    };
  })()
};

// модальные окна (alert, confirm)
var __modal = (function() {
    var $overlay = $("<div class=\"b-modal__overlay\"/>"),
        $alert = $("<div class=\"b-modal\">" +
            "<div class=\"b-modal__deco\">" +
                "<p class=\"b-modal__title\"/>" +
                "<div class=b-modal__controls>" +
                    "<button>OK</button>" +
                "</div>" +
            "</div>" +
        "</div>"),
        $alertTitle = $alert.find("p"),
        $confirm = $("<div class=\"b-modal\">" +
            "<div class=\"b-modal__deco\">" +
                "<p class=\"b-modal__title\"/>" +
                "<div class=b-modal__controls>" +
                    "<button class=\"ok\">OK</button>" +
                    "<button class=\"cancel\">Отмена</button>" +
                "</div>" +
            "</div>" +
        "</div>"),
        $confirmTitle = $confirm.find("p"),
        
        currentConfirmCallback, currentConfirmCancelCallback, isModal;
    
    // private
    function open(modal) {
        var wHeight = $(window).height(),
            dHeight = $(document).height(),
            scrollTop = $(window).scrollTop();
        
        $overlay
            .appendTo($("body"))
            .css("height", dHeight);
        
        modal
            .appendTo($("body"))
            .css({
                top: scrollTop + wHeight/2 - modal.height()/2
            });
        
        isModal = modal;
    };
    function close() {
        $overlay.detach();
        $alert.detach();
        $confirm.detach();
        isModal = false;
    };
    
    // events
    $alert.find("button").bind("click", close);
    $confirm.find(".ok").bind("click", function() {
        close();
        currentConfirmCallback && currentConfirmCallback();
        currentConfirmCallback = currentConfirmCancelCallback = null;
    });
    $confirm.find(".cancel").bind("click", function() {
        close();
        currentConfirmCancelCallback && currentConfirmCancelCallback();
        currentConfirmCallback = currentConfirmCancelCallback = null;
    });
    
    $(window).bind("resize", function() {
        if (!isModal) return;
        open(isModal);
    });
    $(window).bind("scroll", function() {
        if (!isModal) return;
        open(isModal);
    });
    
    // public
    return {
        alert: function(title) {
            $alertTitle.html(title || "");
            open($alert);
        },
        confirm: function(title, callback, cancelCallback) {
            currentConfirmCallback = callback;
            currentConfirmCancelCallback = cancelCallback;
            $confirmTitle.html(title || "");
            open($confirm);
        }
    };
})();

var __ysPopup;

// обработка ошибок в запросах
var __ajaxError = (function() {
    var isDebugMode = true;
    var errorCodesTable = {};
    var postErrorMessage = function(error) {
        if (error in errorCodesTable) {
            __modal.alert(errorCodesTable[error]);
        }
        else {
            __modal.alert(error);
        }
    };
    
    return function(data) {
        try {
            if (data.error) {
                postErrorMessage(data.error);
                return true;
            }
            else return false;
        }
        catch(e) {
            return false;
        }
    };
})();

var __processColorSwitcher = function() {
    var _this = this,
        $this = $(this),
        _parent = this.parentNode,
        $parent = $(_parent);
    
    if ($parent.hasClass("__js_inited")) return;
    $parent.addClass("__js_inited");
    
    $(this).bind("click", function() {
        $parent.addClass("b-dropdown-dropped");
    });
    $(document).bind("click", function(e) {
        if (!$.contains(_parent, e.target)) {
            $parent.removeClass("b-dropdown-dropped");
        }
    });
};

// unselectable
$.fn.disableSelection = function() {
    $(this).css('-moz-user-select', 'none')
			.css('-khtml-user-select', 'none')
			.css('user-select', 'none')
           .each(function() { 
               this.onselectstart = function() { return false; };
            });
};
$.fn.enableSelection = function() {
    $(this).css('-moz-user-select', '')
			.css('-khtml-user-select', '')
			.css('user-select', '')	
           .each(function() { 
               this.onselectstart = "";
            });
};

/**
 * @returns {SetItem}
 */
function SetItem() {this.init.apply(this, arguments);};
SetItem.prototype = {
    element: "<div class='ys-canvas__item'/>",
    hflipClass: "is_hflip",
    vflipClass: "is_vflip",
    underlayClass: "is_underlay",
    editModeClass: "ys-canvas__item_active",
    
    hflipDecorator: "<div class='hflip'/>",
    vflipDecorator: "<div class='vflip'/>",
    overlay: "<i/>",
    ltResizer: "<b class='lt'/>",
    rtResizer: "<b class='rt'/>",
    rbResizer: "<b class='rb'/>",
    lbResizer: "<b class='lb'/>",
    ieItemWrapper: "<div class='_ie_item_wrapper'/>",
    
    maxSize: {
        width: 300,
        height: 300
    },
    minSize: {
        width: 30,
        height: 30
    },
    
    init: function(canvas, id, state, set, isNotFromSave) {
        this.canvas = canvas;
        this.id = id;
        this.set = set;
        this.isNotFromSave = isNotFromSave;
        
        this.$doc = $(document);
        this.$body = $("body");
        this.$win = $(window);
        
        this.initState(state);
        this.create();
        this.listen();
    },
    
    create: function() {
        this.element = $(this.element);
        this.hflipDecorator = $(this.hflipDecorator).appendTo(this.element);
        this.vflipDecorator = $(this.vflipDecorator).appendTo(this.hflipDecorator);
        this.overlay = $(this.overlay).appendTo(this.element);
        this.ltResizer = $(this.ltResizer).appendTo(this.element);
        this.rtResizer = $(this.rtResizer).appendTo(this.element);
        this.rbResizer = $(this.rbResizer).appendTo(this.element);
        this.lbResizer = $(this.lbResizer).appendTo(this.element);
        
        this.image = $("<img/>").appendTo(this.vflipDecorator);
        this.setOrder(this.id);
        
        if (this.state.width && this.state.height) {
            this.applyState(this.state);
            this.element.appendTo(this.canvas);
            this.updateSetState();
        }
        else {
            this.image.bind("load", $.proxy(function() {
                this.image.unbind("load");
                this.element.appendTo(this.canvas);
                this.state.width = this.image.width();
                this.state.height = this.image.height();
                this.applyState(this.state);
                this.updateSetState();
            }, this));
        }
        this.image.attr("src", this.state.image);
    },
    
    initState: function(state) {
        this.state = {
            hidden: state.hidden || false,
            vflip: state.vflip || false,
            hflip: state.hflip || false,
            underlay: state.underlay || false,
            left: (("left" in state) ? state.left : (("leftOffset" in state) ? state.leftOffset : this.set.canvasData.width/2)),
            top: (("top" in state) ? state.top : (("topOffset" in state) ? state.topOffset : this.set.canvasData.height/2)),
            tid: state.tid || state.id,
            image: state.image,
            width: state.width || null,
            height: state.height || null
        };
    },
    applyState: function(state) {
        if (this.isNotFromSave && !this.isApplied) {
            state = this.validateSize(state);
            state = this.validatePosition(state);
        }
        this.state = state;
        
        this.element.css({
            left: this.state.left,
            top: this.state.top
        });
        this.image.css({
            width: this.state.width,
            height: this.state.height
        });
        this.image.attr("src", this.state.image);
        
        this.element[this.state.hidden ? "hide" : "show"]();
        this.element[this.state.vflip ? "addClass" : "removeClass"](this.vflipClass);
        this.element[this.state.hflip ? "addClass" : "removeClass"](this.hflipClass);
        this.element[this.state.underlay ? "addClass" : "removeClass"](this.underlayClass);
        
        this.isApplied = true;
    },
    validateSize: function(state) {
        // save proportions
        var q, nq;
        if (state.width !== undefined && state.height !== undefined) {
            q = state.width / state.height
        }
        
        state.width = (state.width != undefined)
            ? Math.max(Math.min(state.width, this.maxSize.width), this.minSize.width)
            : this.state.width;
    
        state.height = (state.height != undefined)
            ? Math.max(Math.min(state.height, this.maxSize.height), this.minSize.height)
            : this.state.height;
        
        // restore proporti
        if (q !== undefined) {
            nq = state.width / state.height;
            if (nq < q) {
                state.height = state.width / q;
            }
            else if (nq > q) {
                state.width = q * state.height;
            }
        }
            
        return state;
    },
    validatePosition: function(state) {
        var viewport = {
            width: this.set.canvasData.width,
            height: this.set.canvasData.height
        };
        
        if (state.width == undefined) state.width = this.state.width;
        if (state.height == undefined) state.height = this.state.height;
        
        if (state.left != undefined) {
            state.left = Math.max(Math.min(state.left, viewport.width - state.width), 0);
        }
        
        if (state.top != undefined) {
            state.top = Math.max(Math.min(state.top, viewport.height - state.height), 0);
        }
        
        return state;
    },
    getState: function() {
        return this.state;
    },
    
    listen: function() {
        this.element.bind("mousedown", $.proxy(this.onMouseDown, this));
        this.$doc.bind("mouseup", $.proxy(this.onMouseUp, this));
        this.$doc.bind("keydown", $.proxy(this.onKeyDown, this));
        
        this.ltResizer.bind("mousedown", $.proxy(function(e) { this.onResizeStart(e, "lt"); return false; }, this));
        this.rtResizer.bind("mousedown", $.proxy(function(e) { this.onResizeStart(e, "rt"); return false; }, this));
        this.rbResizer.bind("mousedown", $.proxy(function(e) { this.onResizeStart(e, "rb"); return false; }, this));
        this.lbResizer.bind("mousedown", $.proxy(function(e) { this.onResizeStart(e, "lb"); return false; }, this));
        
        this.set.$itemToolbar
            .find(".remove").bind("click", $.proxy(function() {this.onToolbarAction("hide");}, this)).end()
            .find(".vflip").bind("click", $.proxy(function() {this.onToolbarAction("vflip");}, this)).end()
            .find(".hflip").bind("click", $.proxy(function() {this.onToolbarAction("hflip");}, this)).end()
            .find(".underlay").bind("click", $.proxy(function() {this.onToolbarAction("underlay");}, this)).end()
            .find(".clone").bind("click", $.proxy(function() {this.onToolbarAction("clone");}, this)).end()
            .find(".zmore").bind("click", $.proxy(function() {this.onToolbarAction("zmore");}, this)).end()
            .find(".zless").bind("click", $.proxy(function() {this.onToolbarAction("zless");}, this));
    },
    
    // mouse events
    onMouseDown: function(e) {
        if (!this.isEditMode) {
            this.enableEditMode();
        }
        else {
            this.onDragStarted(e);
        }
		this.$doc.bind("mousemove", $.proxy(this.onMouseMove, this));		
        return false;
    },
    onMouseMove: function(e) {
        if (this.isDragged) {
            this.onDrag(e);
        }
        if (this.isResized) {
            this.onResize(e);
        }
		return false;
    },
    onMouseUp: function(e) {
        if (this.isDragged) {
            this.onDragEnd(e);
        }
        if (this.isResized) {
            this.onResizeEnd(e);
        }
		this.$doc.unbind("mousemove");
    },
    
    // keyboard events
    keyMap: {
        "37": "moveLeft",
        "38": "moveTop",
        "39": "moveRight",
        "40": "moveBottom",
        "46": "hide"
    },
    onKeyDown: function(e) {
        if (!this.isEditMode) return;
        
        if (e.keyCode in this.keyMap) {
            this[this.keyMap[e.keyCode]](e);
            return false;
        }
    },
    
    moveStep: 1,
    moveStepForce: 10,
    moveLeft: function(e) { this.move(e, "left"); },
    moveTop: function(e) { this.move(e, "top"); },
    moveRight: function(e) { this.move(e, "left", true); },
    moveBottom: function(e) { this.move(e, "top", true); },
    
    move: function(e, offsetType, inverse) {
        var offset = this.state[offsetType],
            step = (e.shiftKey ? this.moveStepForce : this.moveStep) * (inverse ? 1 : -1);
        
        offset += step;
        
        var state = {};
        state[offsetType] = offset;
        state = this.validatePosition(state);
        
        this.state[offsetType] = state[offsetType];
        this.applyState(this.state);
    },
    
    // edit mode
    enableEditMode: function() {
        this.set.forceDisableEditMode();
        this.isEditMode = true;
        this.element.addClass(this.editModeClass);
        this.set.onItemEnableEditMode();
    },
    disableEditMode: function() {
        this.isEditMode = false;
        this.element.removeClass(this.editModeClass);
    },
    
    // drag
    onDragStarted: function(e) {
        this.isDragged = true;
        this.dragData = {
            startX: e.clientX,
            startY: e.clientY,
            startLeft: this.state.left,
            startTop: this.state.top,
            currentLeft: this.state.left,
            currentTop: this.state.top
        };
    },
    onDrag: function(e) {
        var diffX = e.clientX - this.dragData.startX;
        var diffY = e.clientY - this.dragData.startY;
        
        this.dragData.currentLeft = Math.max( Math.min( this.dragData.startLeft + diffX, this.set.canvasData.width - this.state.width ), 0 );
        this.dragData.currentTop = Math.max( Math.min( this.dragData.startTop + diffY, this.set.canvasData.height - this.state.height ), 0 );
        
        this.element.css({
            left: this.dragData.currentLeft,
            top: this.dragData.currentTop
        });
    },
    onDragEnd: function() {
        this.state.left = this.dragData.currentLeft;
        this.state.top = this.dragData.currentTop;
        
        this.isDragged = this.dragData = null;
        this.updateSetState();
    },
    moveBy: function(x, y) {
        var state = this.validatePosition({
            left: this.state.left + x,
            top: this.state.top + y
        });
        this.state.left = state.left;
        this.state.top = state.top;
        
        this.applyState(this.state);
    },
    setZoomIn: function(zoomQ) {
        this.setCommonZoom(zoomQ, "in");
    },
    setZoomOut: function(zoomQ) {
        this.setCommonZoom(zoomQ, "out");
    },
    setCommonZoom: function(zoomQ, zoomAction) {
        var viewport = this.set.canvasData;
		var oldWidth=this.state.width;
		var oldHeight=this.state.height;
		var canvasWidth=this.canvas.width();
		var canvasHeight=this.canvas.height();
        //var leftOffset = (this.state.left + this.state.width/2);
        //var topOffset = (this.state.top + this.state.height/2);
		
		
		if(zoomAction == "out"){
			var newWidth=oldWidth / zoomQ;
			var newHeight=oldHeight / zoomQ;
			var newLeft=this.state.left - (newWidth-oldWidth)/2;
			var newTop=this.state.top - (newHeight-oldHeight)/2;
		}
		else {
			var newWidth=oldWidth * zoomQ;
			if (newWidth>canvasWidth){
				newWidth=canvasWidth;
				zoomQ=newWidth/oldWidth;
				newHeight=oldHeight * zoomQ;
			}
			
			var newHeight=oldHeight * zoomQ;
			if(newHeight>canvasHeight){
				newHeight=canvasHeight;
				zoomQ=newHeight/oldHeight;
				newWidth=oldWidth * zoomQ;
			}			
			
			var newLeft=this.state.left - (newWidth-oldWidth)/2;
			if(newLeft+newWidth>canvasWidth){
				newLeft=canvasWidth-newWidth;
				
			}
			if(newLeft<0) newLeft=0;
			
			var newTop=this.state.top - (newHeight-oldHeight)/2;
			if(newTop+newHeight>canvasHeight){
				newTop=canvasHeight-newHeight;
			}
			if(newTop<0) newTop=0;


		}

		this.state.width = newWidth;
		this.state.height = newHeight;
		this.state.left = newLeft;
		this.state.top = newTop;
		
        
        this.applyState(this.state);
    },
    
    // resize
    onResizeStart: function(e, mode) {
        this.isResized = true;
        this.resizeData = {
            mode: mode,
            startX: e.clientX,
            startY: e.clientY,
            startLeft: this.state.left,
            startTop: this.state.top,
            currentLeft: this.state.left,
            currentTop: this.state.top,
            startWidth: this.state.width,
            startHeight: this.state.height,
            currentWidth: this.state.width,
            currentHeight: this.state.height
        };
		this.$doc.bind("mousemove", $.proxy(this.onMouseMove, this));
    },
    onResize: function(e) {
        var diffX = e.clientX - this.resizeData.startX,
            diffY = e.clientY - this.resizeData.startY,
            
            currentLeft, currentWidth, currentTop, currentHeight;
        
        switch (this.resizeData.mode) {
            case "lt" : 
                currentLeft = Math.max(0, Math.min(this.resizeData.startLeft + diffX, this.resizeData.startLeft + this.resizeData.startWidth));
                currentTop = Math.max(0, Math.min(this.resizeData.startTop + diffY, this.resizeData.startTop + this.resizeData.startHeight));
                break;
            case "rt" : 
                currentWidth = Math.max(0, Math.min(this.resizeData.startWidth + diffX, this.set.canvasData.width - this.resizeData.startLeft));
                currentTop = Math.max(0, Math.min(this.resizeData.startTop + diffY, this.resizeData.startTop + this.resizeData.startHeight));
                break;
            case "rb" : 
                currentWidth = Math.max(0, Math.min(this.resizeData.startWidth + diffX, this.set.canvasData.width - this.resizeData.startLeft));
                currentHeight = Math.max(0, Math.min(this.resizeData.startHeight + diffY, this.set.canvasData.height - this.resizeData.startTop));
                break;
            case "lb" : 
                currentLeft = Math.max(0, Math.min(this.resizeData.startLeft + diffX, this.resizeData.startLeft + this.resizeData.startWidth));
                currentHeight = Math.max(0, Math.min(this.resizeData.startHeight + diffY, this.set.canvasData.height - this.resizeData.startTop));
                break;
        
        }
        
        if (currentLeft) {
            currentWidth = this.resizeData.startWidth + (this.resizeData.startLeft - currentLeft);
        }
        else if (currentWidth) {
            currentLeft = this.resizeData.currentLeft;
        }
        
        if (currentTop) {
            currentHeight = this.resizeData.startHeight + (this.resizeData.startTop - currentTop);
        }
        else if (currentHeight) {
            currentTop = this.resizeData.currentTop;
        }
        
        this.resizeData.currentLeft = currentLeft;
        this.resizeData.currentTop = currentTop;
        this.resizeData.currentWidth = currentWidth;
        this.resizeData.currentHeight = currentHeight;
        
        this.element.css({
            left: this.resizeData.currentLeft,
            top: this.resizeData.currentTop
        });
        this.image.css({
            width: this.resizeData.currentWidth,
            height: this.resizeData.currentHeight
        });
    },
    onResizeEnd: function() {
        this.state.left = this.resizeData.currentLeft;
        this.state.top = this.resizeData.currentTop;
        this.state.width = this.resizeData.currentWidth;
        this.state.height = this.resizeData.currentHeight;
        
        this.isResized = this.resizeData = null;
        this.updateSetState();
    },
    
    // TOOLBAR
    noUpdateActions: {
        "zmore": true,
        "zless": true
    },
    onToolbarAction: function(action) {
        if ( !this.isEditMode ) return;
        this[action] && this[action]();
        
        if (action in this.noUpdateActions) return;
        this.updateSetState();
    },
    hide: function() {
        this.element.hide();
        this.state.hidden = true;
        this.set.forceDisableEditMode();
    },
    hflip: function() {
        this.element.toggleClass(this.hflipClass);
        this.state.hflip = !this.state.hflip;
    },
    vflip: function() {
        this.element.toggleClass(this.vflipClass);
        this.state.vflip = !this.state.vflip;
    },
    underlay: function() {
        this.element.toggleClass(this.underlayClass);
        this.state.underlay = !this.state.underlay;
    },
    clone: function() {
        this.set.addClone(this);
    },
    zmore: function() {
        this.set.zmore(this);
    },
    zless: function() {
        this.set.zless(this);
    },
    setOrder: function(order) {
        this.element.css("z-index", order);
        this.id = order;
    },
    
    updateSetState: function() {
        if ( isNaN( this.state.left ) ) this.state.left = parseInt ( this.element.css("left") );
        if ( isNaN( this.state.top ) ) this.state.top = parseInt( this.element.css("top") );
        if ( isNaN( this.state.width )) this.state.width = parseInt( this.element.css("width") );
        if ( isNaN( this.state.height )) this.state.height = parseInt( this.element.css("height") );
        
        var updatedState = {};
        updatedState[this.id] = this.getState();
        this.set.updateState(updatedState);
    },
    
    // remove
    remove: function() {
        this.element.remove();
    }
};

/**
 * @returns {Set}
 */
function Set() {this.init.apply(this, arguments);};
Set.prototype = {
    ajaxSaveURL: "/yourstyle/editor/saveSet",
    ajaxPublishURL: "/yourstyle/editor/publishSet",
    emptyCanvasClass: "ys-canvas__empty",
    emptyCanvasText: "<h1>Перетаскивай предметы сюда</h1>",
    zoomQ: 1.2,
    init: function(canvas, id, state) {
        this.canvas = canvas;
        this.canvasContainer = this.canvas.parent();
        this.canvasData = {
            width: this.canvas.width(),
            height: this.canvas.height()
        };
        
        this.$itemToolbar = $(".ys-canvas__toolbar_item", this.canvasContainer);
        
        this.setId = id;
        this.undoHistory = [];
        this.redoHistory = [];
        
        this.listen();
        this.setEmptyCanvas();
        
        if (state) {
            this.setCanvasWithItems();
            this.initItems(state);
        }
        
        this.isSaved = !!this.setId;
    },
    
    // listen
    listen: function() {
        this.canvas.bind("mousedown", $.proxy(this.forceDisableEditMode, this));
    },
    
    // process empty canvas
    setEmptyCanvas: function() {
        this.isEmpty = true;
        this.canvasContainer.addClass(this.emptyCanvasClass);
        this.$emptyCanvasText = $(this.emptyCanvasText).appendTo(this.canvasContainer);
        this.currentState = {};
        
        this.$itemToolbar.hide();
    },
    setCanvasWithItems: function() {
        this.isEmpty = false;
        this.canvasContainer.removeClass(this.emptyCanvasClass);
        this.$emptyCanvasText.remove();
        this.items = [];
    },
    
    // adding items to set
    initItems: function(state) {
        for (var i=0, l=state.length; i<l; i++) {
            this.items[i] = new SetItem(this.canvas, i, state[i], this);
        }
        this.currentState = this.getCurrentState();
        YSEditorController.setHistoryActivity();
    },
    addItem: function(itemData) {
        if (this.isEmpty) {
            this.setCanvasWithItems();
        }
        
        this.items.push(new SetItem(
            this.canvas, this.items.length, itemData, this, true
        ));
        YSEditorController.setHistoryActivity();
    },
    addClone: function(item) {
        var state = item.getState(),
            cloneState = {};
        for (var i in state) cloneState[i] = state[i];
        
        cloneState.left = this.canvasData.width/2 - cloneState.width/2;
        cloneState.top = this.canvasData.height/2 - cloneState.height/2;
        
        this.addItem(cloneState);
    },
    zmore: function(item) {
        var id = item.id,
            changeItem = this.items[id + 1];
        if (changeItem) {
            this.items[id + 1] = item;
            this.items[id] = changeItem;
            
            item.setOrder(id + 1);
            changeItem.setOrder(id);

            this.redoHistory = [];
            this.undoHistory.unshift(this.copyState(this.currentState));
            this.currentState = this.getCurrentState();
        }
    },
    zless: function(item) {
        var id = item.id,
            changeItem = this.items[id - 1];
        if (changeItem) {
            this.items[id - 1] = item;
            this.items[id] = changeItem;
            
            item.setOrder(id - 1);
            changeItem.setOrder(id);

            this.redoHistory = [];
            this.undoHistory.unshift(this.copyState(this.currentState));
            this.currentState = this.getCurrentState();
        }
    },
    forceDisableEditMode: function() {
        if (!this.items) return;
        for (var i=0, l=this.items.length; i<l; i++) {
            this.items[i].disableEditMode();
        }
        this.$itemToolbar.hide();
    },
    onItemEnableEditMode: function() {
        this.$itemToolbar.show();
    },
    
    // undo/redo
    undo: function() {
        if ( !this.undoHistory.length ) return;
        
        this.redoHistory.unshift(this.copyState(this.currentState));
        this.applyState(this.copyState(this.undoHistory.shift()));
    },
    redo: function() {
        if ( !this.redoHistory.length ) return;
        
        this.undoHistory.unshift(this.copyState(this.currentState));
        this.applyState(this.copyState(this.redoHistory.shift()));
    },
    
    // center + zoom
    center: function() {
        var realCenterX = this.canvasData.width / 2,
            realCenterY = this.canvasData.height / 2,
            fakeCenterX, fakeCenterY;
        
        function getFake(items, offset, size) {
            var sum = 0;
            for (var i=0, l=items.length; i<l; i++) {
                sum += items[i].state[offset] + items[i].state[size]/2;
            }
            return Math.round(sum / items.length);
        }
        
        fakeCenterX = getFake(this.items, "left", "width");
        fakeCenterY = getFake(this.items, "top", "height");
        
        for (var i=0, l=this.items.length; i<l; i++) {
            this.items[i].moveBy(realCenterX - fakeCenterX, realCenterY - fakeCenterY);
        }
        
        this.redoHistory = [];
        this.undoHistory.unshift(this.copyState(this.currentState));
        this.currentState = this.getCurrentState();
    },
    zoomIn: function() {
        for (var i=0, l=this.items.length; i<l; i++) {
            this.items[i].setZoomIn(this.zoomQ);
        }
        
        this.redoHistory = [];
        this.undoHistory.unshift(this.copyState(this.currentState));
        this.currentState = this.getCurrentState();
    },
    zoomOut: function() {
        for (var i=0, l=this.items.length; i<l; i++) {
            this.items[i].setZoomOut(this.zoomQ);
        }
        
        this.redoHistory = [];
        this.undoHistory.unshift(this.copyState(this.currentState));
        this.currentState = this.getCurrentState();
    },
    
    // apply, update and collect state
    applyState: function(state) {
        for (var itemId=0, l=this.items.length; itemId<l; itemId++) {
            if (itemId in state) {
                this.items[itemId].applyState(state[itemId]);
            }
            else {
                this.items[itemId].hide();
            }
        }
        this.currentState = this.copyState(state);
        YSEditorController.setHistoryActivity();
    },
    updateState: function(state) {
        if (this.isUpdateStateLock) return;
        this.redoHistory = [];
        this.undoHistory.unshift(this.copyState(this.currentState));
        
        state = this.copyState(state);
        for (var itemId in state) {
            this.currentState[itemId] = state[itemId];
        }
        
        this.isSaved = false;
        YSEditorController.setHistoryActivity();
    },
    copyState: function(state) {
        var tmpState = {};
        for (var itemId in state) {
            tmpState[itemId] = {};
            for (var i in state[itemId]) {
                tmpState[itemId][i] = state[itemId][i];
            }
        }
        return tmpState;
    },
    getCurrentState: function() {
        var state = {};
        for (var itemId=0, l=this.items.length; itemId<l; itemId++) {
            state[itemId] = this.items[itemId].getState();
        }
        YSEditorController.setHistoryActivity();
        return state;
    },
    serializeStateToJSON: function(state) {
        state = state || this.currentState;
        var items = [];
        var parts;
        var n, v;
        for (var itemId in state) {
            if (state[itemId].hidden) continue;
            parts = [];
            for (var statePart in state[itemId]) {
                v = state[itemId][statePart];
                n = statePart;
                if (typeof v == "string") v = "\"" + v + "\"";
                if (n == "left") n = "leftOffset";
                if (n == "top") n = "topOffset";
                
                parts.push("\"" + n + "\":" + v);
            }
            items.push("{" + parts.join(",") + "}");
        }
        return "{\"tiles\": [" + items.join(",") + "]" + (this.setId ? ", \"id\": " + this.setId : "") + "}";
    },
    
    
    // TOOLBAR
    // save
    save: function(afterSaveCallback) {
        var saveData = {
            action: "editor",
            type: "yourstyle",
            json: this.serializeStateToJSON()
        };
        $.ajax({
            url: this.ajaxSaveURL,
            type: "post",
            data: saveData,
            dataType: "json",
            success: $.proxy(function(data) {
                if (__ajaxError.apply(window, arguments)) return;
                this.onSave(data);
                afterSaveCallback && afterSaveCallback();
            }, this)
        });
    },
    onSave: function(data) {
        this.setId = data;
        this.isSaved = true;
        YSEditorController.onSetSave(this.setId);
    },
    
    // publish
    publish: function() {
        if (!this.isSaved) {
            this.save($.proxy(this.publish, this));
            return;
        }
        this.showPublishPopup();
    },
    showPublishPopup: function() {
        if (__ysPopup) {
            __ysPopup.remove();
            __ysPopup = null;
        }
        
        var popup = __ysPopup = $('<div class="ys-canvas__publish"><div class="b-deco">' +
            '<h1>Публикация сета</h1>' +
            '<fieldset>' +
                '<label>Название сета <input type="text" name="title"/></label>' +
            '</fieldset>' +
            '<fieldset>' +
                '<label>Звезды (необязательно)</label>' +
                '<ul class="ys-canvas__publish_tags" />' +
                '<input type="text" name="suggest"/>' +
                '<div class="ys-canvas__publish_suggest">' +
                    '<div class="b-deco">' +
                        '<ul />' +
                    '</div>' +
                '</div>' +
            '</fieldset>' +
            '<div class="b-foot">' +
                '<button class="publish">Опубликовать</button>' +
                '<button class="cancel">Отменить</button>' +
            '</div>' +
        '</div></div>').appendTo($("body"));
        
        // vars
        var titleInput = popup.find("input[name='title']");
        var suggestInput = popup.find("input[name='suggest']");
        var suggestContainer = popup.find("div.ys-canvas__publish_suggest").hide();
        var suggestList = suggestContainer.find("ul");
        var tags = {};
        var tagsList = popup.find("ul.ys-canvas__publish_tags");
        var suggestDelay = 150;
        var lastKeyUpTime;
        
        
        // listen
        // suggest
        var suggestUrl = "/yourstyle/editor/getSetsTags/";
        suggestInput.bind("keyup", function() {
            lastKeyUpTime = new Date();
            setTimeout(function() {
                if (new Date() - lastKeyUpTime < suggestDelay / 1.5) return;
                var suggestValue = suggestInput.val();
                if (suggestValue == "") {
                    suggestContainer.hide();
                }
                else {
                    $.ajax({
                        url: suggestUrl + suggestValue,
                        dataType: "json",
                        success: function(data) {
                            if (__ajaxError.apply(window, arguments)) return;
                            if (data.length) {
                                suggestList.html("");
                                for (var i=0, l=data.length; i<l; i++) {
                                    var item = $("<li />");
                                    item.append(data[i].name);
                                    data[i].engName && item.append("<span class='original'> / " + data[i].engName + '</span>');
                                    item.appendTo(suggestList);
                                    item.html( item.html().replace( new RegExp("^(" + suggestValue + ")"), "<b>$1</b>") );
                                    
                                    (function(itemData) {
                                        item.bind("click", function() {
                                            var item = $(this);
                                            var id = itemData.id;
                                            if (id in tags) return;
                                            
                                            tags[id] = true;
                                            suggestInput.val("");
                                            suggestContainer.hide();
                                            
                                            var tag = $("<li><a>" + itemData.name + "<i>x</i></a></li>");
                                            tag
                                                .appendTo(tagsList)
                                                .find("i").bind("click", function() {
                                                    delete tags[id];
                                                    tag.remove();
                                                });
                                        });
                                    })(data[i]);
                                }
                                suggestContainer.show();
                            }
                            else {
                                suggestContainer.hide();
                            }
                        }
                    });
                }
            }, suggestDelay);
        });
        
        // submit
        popup.find("button.publish").bind("click", $.proxy(function() {
            if (/^\s*$/.test(titleInput.val())) {
				titleInput.focus();
                return;
            }
            
            var setTags = [];
            for (var tag in tags) setTags.push(tag);
            setTags = "[" + setTags.join(",") + "]";
            
            var publishData = {
                type: "yourstyle",
                action: "editor",
                json: "{\"id\":" + this.setId + ",\"title\":\"" + titleInput.val() + "\",\"tags\":" + setTags + "}"
            };
            $.ajax({
                url: this.ajaxPublishURL,
                type: "post",
                data: publishData,
                success: $.proxy(this.onPublish, this),
                dataType: "json"
            });
            popup.remove();
        }, this));
        
        popup.find("button.cancel").bind("click", function() {
            popup.remove();
        });
    },
    onPublish: function(data) {
        if (__ajaxError.apply(window, arguments)) return;
        YSEditorController.onSetPublish(this.setId);
    },
    
    // close
    close: function() {
        if (this.items) {
            for (var i=0, l=this.items.length; i<l; i++) {
                this.items[i].remove();
            }
        }
        this.setCanvasWithItems();
    }
};

/**
 * Интерфейс конструктора таба
 * @returns {InterfaceYSEditorTab}
 */
function InterfaceYSEditorTab() { this.init.apply(this, arguments); };
InterfaceYSEditorTab.prototype = {
    // static
    tabLayout: "<li />",
    tabButtonLayout: "<a/>",
    tabCloseLayout: "<a class=\"close\"/>",
    defaultTabName: "",
    activeTabClass: "active",
    optsFields: "place contentPlace controlPlace",
    isDefault: false,
    // </static
    
    // init
    _initError: function(opts) {
        var e = [];
        $.each(this.optsFields.split(" "), function(i, option) {
            if ( !(option in opts) ) {
                e.push("Поле " + option + "отсутствует в инициализирующем объекте");
            }
        });
        if (e.length) {
            throw new Error(e.join("\n"));
        }
    },
    init: function(tabController, opts) {
        this._initError(opts);
        
        this.tabController = tabController;
        this.opts = opts;
        
        this.createTab();
        this.createTabControl();
        this.createTabContent();
        
        this.afterCreate();
        
        return this;
    },
    // </init
    
    // create tab
    createTab: function() {
		
        this.$tab = $(this.tabLayout).appendTo(this.opts.place);
        this.$tabButton = $(this.tabButtonLayout).appendTo(this.$tab);
        this.setTabName(this.opts.name || this.defaultTabName);
        
        this.$tabButton.bind("click", $.proxy(this.open, this));
        
        var isDefault = this.isDefault = ("isDefault" in this.opts) ? this.opts.isDefault : this.isDefault;
        if ( !isDefault ) {
            $(this.tabCloseLayout).appendTo(this.$tab).bind("click", $.proxy(this.remove, this));
        }
    },
    createTabControl: function() {
        this.tabControl = new this.TabControl(this, {
            place: this.opts.controlPlace
        });
    },
    createTabContent: function() {
        this.tabContent = new this.TabContent(this, {
            place: this.opts.contentPlace
        });
    },
    afterCreate: function() {
        if ( !("isActive" in this.opts && this.opts.isActive) ) {
            this.close();
        }
        else {
            this.open();
        }
    },
    // </create tab
    
    // open/close
    open: function() {
        this.$tab.addClass(this.activeTabClass);
        this.tabControl.show();
        this.tabContent.show();
        this.isOpen = true;
        
        this.tabController.onTabOpen(this);
    },
    close: function() {
        this.$tab.removeClass(this.activeTabClass);
        this.tabControl.hide();
        this.tabContent.hide();
        this.isOpen = false;
    },
    remove: function() {
        this.$tab.remove();
        this.tabControl.remove();
        this.tabContent.remove();
        
        this.tabController.onTabRemove(this);
    },
    
    // tab title
    setTabName: function(name) {
        this.$tabButton.html(name);
    },
    getTabName: function() {
        return this.$tabButton.html();
    },
    
    getTabData: function() {}
};

/**
 * Интерфейс конструктора контента таба
 * @returns {InterfaceYSEditorTabContent}
 */
function InterfaceYSEditorTabContent() { this.init.apply(this, arguments); };
InterfaceYSEditorTabContent.prototype = {
    // static
    tabContentLayout: "<div class=\"ys-canvas__section\"/>",
    loadContentURL: "",
    tabContentOverlayLayout: "<div class=\"ys-canvas__section__overlay\"/>",
    tabContentLoaderLayout: "<div class=\"ys-canvas__loader\"/>",
    tabContentEmptyClass: "ys-canvas__empty",
    dataType: "json",
    // </static
    
    // init
    init: function(tab, opts) {
        this.tab = tab;
        this.opts = opts;
        
        this.createContent();
        
        return this;
    },
    // </init
    
    // create
    createContent: function() {
		this.$content = $(this.tabContentLayout).appendTo(this.opts.place);		
        this.afterCreate();
    },
    afterCreate: function() {},
    // </create
    
    // show/hide
    show: function() {
        this.$content.show();

        if (this.isContentLoaded !== true && this.tab.opts.noDefaultLoad !== true) {            
			
			//если есть какой нить фильтр или перед нами простая группа - загружем списки
			if(this.tab.opts.userData.tabColor || this.tab.opts.userData.tabBrand || this.tab.opts.userData.gid){
				this.tab.tabControl.getFiltered();
			}
			//если супергруппа без фильтра - грузим супергруппу
			else if (this.tab.opts.userData.rgid){
				this.loadSupergroup(this.tab.opts.userData.rgid); 
			}
			//значит перед нами таб с главным содержимым
			else {
				this.loadContent();					
			}
        }		
    },
    hide: function() {
        this.$content.hide();
    },
    remove: function() {
        this.$content.remove();
    },
    
    // load content
    loadContent: function() {
		
		this.showLoader();
		
		//Если когда нить нужно будет сделать pages для таба с главным содержимимым - придется здесь все менять
		//если мы не подгружаем страницу - скидываем номер загружаемой страницы
		if(!this.tab.opts.isLoadingPage) this.tab.opts.numLoadPage=1;		
		
		//если уже загружали основной объект - нет смысла его по новой загружать (нужно для Таба ВЕЩИ)
		if (this.cache && this.cache.groups){
			this.tab.opts.pages=1; //значение нужно будет подставлять реальное - если когда нить появятся pages
			this.onContentLoaded(this.cache.data);
		}
		else{
			$.ajax(this.loadContentURL, { 
				dataType: this.dataType,
				cache: true,
				success: $.proxy(function(data) {
					if (__ajaxError.apply(window, arguments)) return;
					this.isContentLoaded = true;				
					
					this.tab.opts.pages=1; //значение нужно будет подставлять реальное - если когда нить появятся pages
					
					this.onContentLoaded(data);
				}, this),
				error: function(xhr, status, error) {
					__modal.alert(status);
					__modal.alert(error);
				}
			});
		}
    },
    onContentLoaded: function(data) {},
    showLoader: function() {
		this.tab.opts.isLoading=true; //говорим, что происходит загрузка чего либо
        this.$tabContentOverlay = $(this.tabContentOverlayLayout).appendTo(this.$content);
        this.$tabContentLoader = $(this.tabContentLoaderLayout).appendTo(this.$content);
    },
    hideLoader: function() {
		this.tab.opts.isLoading=false; //говорим, что загрузка чего либо завершена
        
		this.$tabContentOverlay.remove();
        this.$tabContentLoader.remove();
    }
    // </load content
};

/**
 * @returns {InterfaceYSEditorTabControl}
 */
function InterfaceYSEditorTabControl() { this.init.apply(this, arguments); };
InterfaceYSEditorTabControl.prototype = {
    // static
    tabControlLayout: "<div class=\"ys-canvas__active_tab_control\"/>",
    // </static
    
    // init
    init: function(tab, opts) {
		this.tab = tab;
        this.opts = opts;
        
        this.createControl();
        
        return this;
    },
    // </init
    
    // create
    createControl: function() {
        this.$control = $(this.tabControlLayout).appendTo(this.opts.place);
		this.afterCreate();
    },
    afterCreate: function() {},
    // </create
    
    // show/hide/remove
    show: function() {
        this.$control.show();
    },
    hide: function() {
        this.$control.hide();
    },
    remove: function() {
        this.$control.remove();
    }
};


// Таб "Вещи"
function YSEditorItemsTab() { this.init.apply(this, arguments); };
_oop_.extend(InterfaceYSEditorTab, YSEditorItemsTab, {
    TabContent: YSEditorItemsTabContent,
    TabControl: YSEditorItemsTabControl,
    
    defaultTabName: "Вещи",    
    
    getTabData: function() {//для сохранения закладки
        var data = {}, tabData = [];
        //data.title = this.getTabName();
		data.title=this.opts.userData.title;
		
        if (this.opts.userData.rgid) data.rgid = this.opts.userData.rgid;
        if (this.opts.userData.gid) data.gid = this.opts.userData.gid;
        if (this.tabId) data.id = this.tabId;
        //if (this.searchText) data.searchText = this.searchText;
        if (this.opts.userData.tabColor) data.tabColor=this.opts.userData.tabColor;
		if (this.opts.userData.tabBrand) data.tabBrand=this.opts.userData.tabBrand;
        
        for (var i in data) {
			
			if(typeof data[i] == "string" || typeof data[i] == "number") var param='"'+data[i]+'"';			
			else if(typeof data[i] == "object"){			
				var param='{'
				for (var key in data[i]) param+=key+':"'+data[i][key]+'",'
				param=param.replace(/,$/, '');
				param+='}'			
			}				
            tabData.push('"' + i + '":' + param);
        }
        tabData = "{" + tabData.join(",") + "}";
        return tabData;
    },
    
    afterCreate: function() {
        
		InterfaceYSEditorTab.prototype.afterCreate.call(this);
		
        this.tabContent.$list = $(this.tabContent.listLayout).appendTo(this.tabContent.$content);
		this.tabContent.$list.bind("scroll", $.proxy(function() {
			this.addThingsScroll();
		
		}, this));
		
        if (this.isDefault) return;
		
		//если пользовательские данные существуют, значит , будем создавать списки
		if (this.opts.userData.length!=0) { 
			this.id = this.tabId = this.opts.userData.id;
            this.setTabName(this.opts.userData.title);           
        }
		//Пользовательских данных нет, только что создали. Если это не таб, в который не нужно ничего загружать (loadSupergroup), сохраняем его
		else {
            if (this.opts.noDefaultLoad !== true) {
                this.tabController.saveTab(this);
            }
        }
    },
	addThingsScroll:function(){
	
		// Если происходит загрузка чего либо или мы загрузили последнюю страницу или не прокрутили до конца - выходим
		if(this.opts.isLoading || this.opts.pages==this.opts.numLoadPage || (this.tabContent.$list.attr('scrollHeight')-this.tabContent.$list.scrollTop()-this.tabContent.$list.height()-200>=0) ) return;
		
		this.opts.isLoadingPage=true; //показываем, что мы подгружаем страницу, то есть чистить списки и обнолять numLoadPage не нужно
		++this.opts.numLoadPage;
		this.tabControl.getFiltered();		
	}
	
});
function YSEditorItemsTabContent() { this.init.apply(this, arguments); };
_oop_.extend(InterfaceYSEditorTabContent, YSEditorItemsTabContent, {
    //mode: "groups",
    loadContentURL: "/yourstyle/editor/getGroups",
    //loadGroupURL: "/yourstyle/editor/getFiltered?",
    loadItemURL: "/yourstyle/editor/getGroupTile/",
    cache: {},
    
    significantDiff: 10,
    
    listLayout: "<ul class=ys-canvas__stuff/>",
    
    blankImageSrc: "/assets/img/ys/0.gif",
    blankImageClass: "ys-canvas__tile_loading",
    
    itemCloneClass: "ys-canvas__item_dragged",
    itemPopupLayout: "<div class='ys-canvas__stuff_item'>" +
        "<div class='ys-canvas__stuff_item_content'>" +
            "<a class='item-url'><img /></a>" +
            "<div class='ys-canvas__stuff_item_wrapper'>" +
                "<h2></h2>" +
                "<div class='controls'>" +
                    "<ul class='ys-canvas__simple_tabs' />" +
                "</div>" +
				'<div class="sub_rating">'+
				'</div>'+
				 "<div class='price'></div>" +
				 "<div class='group_name'></div>" +
				 "<div class='brand'></div>" +
                "<div class='description' />" +
            "</div>" +
        "</div>" +
        "<div class='underlay' /><a class='close' href='#' />" +
    "</div>",
    
    afterCreate: function() {
        //this.loadContent();
    },
    onContentLoaded: function(data) {
		
		this.tab.tabContent.hideLoader();	
		
		//очищаем, только если не подгружаем страницу при скролле
		//при простом очищении $list срабатывает скролл и соотв ф-я, поэтому делаем так
		if(!this.tab.opts.isLoadingPage){
			this.$content.html("");
			this.$list = $(this.listLayout).appendTo(this.$content);
			this.$list.bind("scroll", $.proxy(function() {
				this.tab.addThingsScroll(); 					
			}, this));
		}
		else this.tab.opts.isLoadingPage=false; 
		
		
		//если есть какой нить фильтр или перед нами простая группа - загружем списки
		if(this.tab.opts.userData.tabColor || this.tab.opts.userData.tabBrand || this.tab.opts.userData.gid){
			this.createItems(data);
		}
		//если супергруппа без фильтра - грузим супергруппу, в кеш ничего не сохраняем
		else if (this.tab.opts.userData.rgid){
			this.createGroups(data);
		}
		//значит перед нами таб с главным содержимым
		else {
				this.saveCache(data);				
                this.createGroups(data);	
		}
		
		

		/*switch (this.mode) {
            case "groups" :
				this.saveCache(data);	
				
                this.createGroups(data);
                break; 
            
            case "supergroups" :
                this.createGroups(data);
                break;
            
            case "items" : 
                this.createItems(data);
                break;
        }*/
    },
    
    saveCache: function(data) {
        var groups = {};
       
        for (var i = 0, l = data.length; i < l; i++) {
            groups[ data[i].id ] = data[i];
        }
        this.cache.data=data; //Добавил, чтобы повторно не отправлять запрос
        this.cache.groups = groups;
		
    },
    
    createGroups: function(data) {
	for (var i=0, l=data.length; i<l; i++) {
            var $item = $("<li><a><i class='cut'><img /></i></a></li>"),
                $img = $item.find("img"),
                $link = $item.find("a");

            $item.attr("id", data[i].id);
            // if no group image
            try {
                $img.attr("src", data[i].tile.image);
            } catch (e) {
                $img.attr("src", "http://v0.popcorn-news.ru/img/no_photo.jpg");
            }
            
            // resize image on load
            $img
                .css("visibility", "hidden")
                .bind("load", function() {
                var width = this.offsetWidth,
                    height = this.offsetHeight,
                    $this = $(this);                
                var h = this.parentNode.offsetHeight / 2 - height / 2;
                /*if(height > this.parentNode.offsetHeight / 2)
                	h = 0;*/
                if (width > height) {
                    $this.css({
                        width: 68,
                        height: "auto",
                        "padding-top" : h
                    });
                }
                else {
                    $this.css({
                        width: "auto",
                        height: 68
                    });
                }
                
                $this.css("visibility", "visible");                
            });

            $link.append(data[i].title);
            
            $item.appendTo(this.$list);
            
            (function($link, item) {
                $link.bind("click", $.proxy(function() {
					
					
					if (!this.tab.opts.userData.rgid) {
						//this.tab.opts.userData.rgid=item.id; - добавим в loadSupergroup, чтобы не присваивать rgid главному табу (тогда содержимое будет открываться в нем же)
						this.loadSupergroup(item.id);
                    }
                    else {
					
						this.tab.opts.userData.rgid = null; 
						this.tab.opts.userData.gid=item.id;			
						this.tab.opts.userData.title=item.title;

						this.tab.tabControl.getFiltered(item);
						
						//this.loadGroup(item);
                    }
                }, this));
            }).call(this, $link, data[i]);
        }
    },
    loadSupergroup: function(id) {
		if (this.tab.isDefault) {
			var newTab = YSEditorController.addNewTab({
                noDefaultLoad: true
            });
			
			//newTab.tabContent.mode = "supergroups";			
			newTab.opts.userData.rgid=id;
			
            newTab.tabContent.cache = this.cache;
            newTab.tabContent.loadSupergroup.call(newTab.tabContent, id);
            return;
        }
		
		
		this.tab.tabContent.showLoader();
		//если мы не подгружаем страницу - скидываем номер загружаемой страницы
		if(!this.tab.opts.isLoadingPage) this.tab.opts.numLoadPage=1;		



        var fromCache = this.cache.groups[id],
            groups = fromCache.groups;   //groups.items - если когда нить появятся pages
			
        this.tab.opts.userData.title = fromCache.title; 			
        //this.mode = "supergroups";
		this.tab.opts.userData.rgid=id;
		
		this.tab.opts.pages=1; //groups.pages - если когда нить появятся pages
		
        this.onContentLoaded(groups);
        this.tab.setTabName(this.tab.opts.userData.title);
		this.tab.tabContent.isContentLoaded = true;
		
		
        //this.tab.setSupergroupsMode(id);
		
        this.tab.tabControl.enableFullSearch();
        //this.searchText = null;
        this.tab.tabController.saveTab(this.tab);
		
		
		
		
    },
    
    createItems: function(data) {
		//если данные отсутствуют, показываем пользователю соответствующее уведомление
		if(!data.length){
			var $item=$('<li class="no_data">Нет вещей</li>');
			$item.appendTo(this.$list);
		}	
		else{
			for (var i=0, l=data.length; i<l; i++) {
				this.createOneItem(data[i]);
			}
		}
    },
    createOneItem: function(itemData) {
	
		var $item = $("<li><a><i class='cut'><img /></i><i/></a><b/></li>"),
            $img = $item.find("img"),
            $link = $item.find("a");
			
			
		$item.attr("id", itemData.id);
        $img.attr("src", itemData.image);

        
		
        // resize image on load
		/*
        $img
            .css("visibility", "hidden")

            .bind("load", function() {
			
            var width = this.offsetWidth,
                height = this.offsetHeight,
                $this = $(this);

				
            var h = this.parentNode.offsetHeight / 2 - height / 2;
			
			                var h = this.parentNode.offsetHeight / 2 - height / 2;
                //if(height > this.parentNode.offsetHeight / 2)
                	//h = 0;

			
			
			
            if (width > height) {
                $this.css({
                    width: 68,
                    height: "auto",
                    "padding-top": h
                });                
            }
            else {
                $this.css({
                    width: "auto",
                    height: 68
                });
            }
            
            $this.css("visibility", "visible");
        });*/
        
        itemData.isMine && $item.addClass("is-mine");
        
        $item.appendTo(this.$list);
        
        this.processItem($item, itemData);
    },
    processItem: function($item, itemData) {
        var x, y, isDragged = false, clone, $window = $(window), $body = $("body"), offset;
        
        $item.bind("mousedown", $.proxy(function(e) {
            offset = $item.offset(),
            x = e.clientX + $window.scrollLeft(),
            y = e.clientY + $window.scrollTop(),
            isDragged = true;
            
            clone = $item.clone(true)
                         .css({
                             left: offset.left,
                             top: offset.top
                         })
                         .unbind("mousedown")
                         .addClass(this.itemCloneClass)
                         .appendTo($body);
            
            $body.disableSelection();
        $(document).bind("mousemove", function(e) {
            if ( !isDragged ) return false;
            clone.css({
                left: offset.left - x + $window.scrollLeft() + e.clientX,
                top: offset.top - y + $window.scrollTop() + e.clientY
            });
        });
        }, this));
        
        
        $(document).bind("mouseup", $.proxy(function(e) {
            if ( !isDragged ) return false;
            isDragged = false;
			$(document).unbind("mousemove");
            
            var diffX = e.clientX + $window.scrollLeft() - x;
            var diffY = e.clientY + $window.scrollTop() - y;
            
            clone.remove();
            $body.enableSelection();
            
            if (Math.abs(diffX) < this.significantDiff && Math.abs(diffY) < this.significantDiff) {
                this.openItemPopup($item, itemData);
            }
            else {
                this.addItemToSet($item, itemData, {
                    x: e.clientX + $window.scrollLeft(),
                    y: e.clientY + $window.scrollTop()
                });
            }
        }, this));
    },
    addItemToSet: function($item, itemData, where) {
        this.tab.tabController.tryAddItem($item, itemData, where);
    },
    
    // item popup
    openItemPopup: function($item, itemData) {
        if (__ysPopup) {
            __ysPopup.remove();
            __ysPopup = null;
        }
        var popup = __ysPopup = $(this.itemPopupLayout);
		
        var $img = popup.find("img");
		
        popup.find("h2").html(itemData.title);
		
		if(itemData.group) popup.find("div.group_name").html('Группа: '+itemData.group);
		else  popup.find("div.group_name").css('display', 'none');
		
		// если не пользовательская вещь (у нее gid=0) выводим всю инфо
        if(itemData.gid!=0){
		
			popup.find("div.brand").html('<span>Бренд:</span> '+itemData.brand);	
			
			popup.find("div.description").html('Описание: '+itemData.description);
			
			if(itemData.price) popup.find("div.price").html('Цена: '+itemData.price);
			else  popup.find("div.price").css('display', 'none');
			
			if(itemData.rating) {
				var num=(itemData.rating+'').replace(/\./, ',');
			}
			else{
				var num=0;
				itemData.rating=0;			
			}
			popup.find('div.sub_rating').html('<span class="num">Рейтинг: '+num+'</span>'+
					'<span class="vote">'+
						'<span class="stars" style="width:'+itemData.rating*20+'px;"></span>'+
						'<a class="star _1" href="#">1</a>'+
						'<a class="star _2" href="#">2</a>'+
						'<a class="star _3" href="#">3</a>'+
						'<a class="star _4" href="#">4</a>'+
						'<a class="star _5" href="#">5</a>'+
					'</span>'
			);
		}
		
		
		
        popup.find("a.item-url")
            .attr("href", "/yourstyle/tile/" + itemData.id)
            .bind("click", function() {
                if (YSEditorController.currentSet && !YSEditorController.currentSet.isSaved && !YSEditorController.currentSet.isEmpty) {
                    __modal.confirm("Вы уверены, что собираетесь покинуть эту страницу, не сохранив открытый сет?", function() {
                        location = "/yourstyle/tile/" + itemData.id;
                    });
                    return false;
                }
            });
        
        // resize image on load
        $img
			.attr("src", itemData.image)
            .css("visibility", "hidden")
            .bind("load", function() {
                var width = this.offsetWidth,
                    height = this.offsetHeight,
                    $this = $(this);
                
                if (width > height) {
                    $this.css({
                        width: 150,
                        height: "auto"
                    });
                }
                else {
                    $this.css({
                        width: "auto",
                        height: 150
                    });
                }
                
                $this.css("visibility", "visible");
            });
            
        
        this.addPopupControls($item, itemData, popup);
        
        popup.find("a.close").bind("mousedown", function(e) {
			e.stopPropagation();										 
            popup.remove();
            return false;
        });
        var offset = $item.offset();
        popup.css(offset);
		//если попап вылезает за пределы рабочей области документа, меняем позиционирование
		var indent=5;//отступ от правого края 
		var winWidth=$(window).width();
		var popupWidth=parseInt(popup.css('width'));
		if(winWidth<popupWidth+indent+parseInt(popup.css('left'))){
			popup.css('left', winWidth-popupWidth-indent);
		}
        popup.appendTo($("body"));
    },
    addPopupControls: function($item, itemData, popup) {
        function onMyItems(urlPostfix, callback) {
            $.ajax({
                url: "/yourstyle/tile/" + itemData.id + "/" + urlPostfix,
                dataType: "json",
                success: function(data) {
                    if (__ajaxError.apply(window, arguments)) return;
                    callback && callback();
                }
            });
        };
        
        var container = popup.find("ul.ys-canvas__simple_tabs");
        
        var $addToSet = $("<li><a>Добавить в сет</a></li>")
            .bind("click", function() {
                YSEditorController.tryAddItem($item, itemData);
            });
        
        var $toMyItems = $("<li>Добавить: <a>в мои вещи</a></li>")
            .bind("click", function() {
                onMyItems("toMy", function() {
                    $toMyItems.detach();
                    $fromMyItems.prependTo(container);
                    $item.addClass("is-mine");
                    itemData.isMine = true;
                });
            });
        
        var $fromMyItems = $("<li><a>Удалить из моих вещей</a></li>")
            .bind("click", function() {
                onMyItems("fromMy", function() {
                    $fromMyItems.detach();
                    $toMyItems.prependTo(container);
                    $item.removeClass("is-mine");
                    itemData.isMine = false;
                });
            });
        
        if (itemData.isMine) {
            $fromMyItems.appendTo(container);
        }
        else {
            $toMyItems.appendTo(container);
        }
        
        if (YSEditorController.currentSet) {
            $addToSet.appendTo(container);
        }
    }
});
function YSEditorItemsTabControl() { this.init.apply(this, arguments); };
_oop_.extend(InterfaceYSEditorTabControl, YSEditorItemsTabControl, {
    formLayout: "<div class=\"search\">" +
        "<a class=\"home\" title=\"Назад к группам\"></a>" +
        "<form class=\"ys-canvas-search\">" +
            '<span class="ys-label">Цвет</span>' +
			'<div class="b-color-chooser">' +
				'<span class="b-color-chooser__chosen b-color-chooser__chooser"><i>' +
                    '<a title="Выбрать цвет"/>' +
                '</i></span>' +
                '<div class="b-color-chooser__list">' +
                    '<div class="b-deco">' +
                        '<ul></ul>' +
                        '<a href="#" class="all">все цвета</a>' +
                    '</div>' +
                '</div>' +
            '</div>' +	
            '<div class="label">' +
				'<span class=\"ys-label\">Бренд</span><input type="text" name="brand" autocomplete="off"  />' +
			"<div class='ys_select _2'>" +
				"<div class='fon'></div>" +
				"<div class='container'>" +
					'<ul class="brand">' +
						'<li>Все бренды</li>' +
					"</ul>" +
				"</div>" +
			"</div>" +
        "</form>" +
        "<div class=\"switcher\">" +
            "<a class=\"prev\"></a>" +
            "<input disabled />" +
            "<a class=\"next\"></a>" +
        "</div>" +
    "</div>",
    fullSearchClass: "full-search",
	getBrandsURL: "/yourstyle/editor/suggestBrands",
    searchURL: "/yourstyle/editor/search?",
    
    afterCreate: function() {
        this.$form = $(this.formLayout).appendTo(this.$control);
        // убрали поиск this.$queryField = this.$form.find("input[type=text]"); 
        this.$home = this.$form.find("a.home");
        
        this.colorChooser = this.$form.find(".b-color-chooser");
        this.colorChooserButton = this.colorChooser.find(".b-color-chooser__chooser");
        this.colorList = this.colorChooser.find(".b-color-chooser__list ul");
        this.colorIndicator = this.colorChooser.find(".b-color-chooser__chosen i");
        this.colorReset = this.colorChooser.find(".b-color-chooser__list a.all");
        
        __processColorSwitcher.call(this.colorChooserButton[0]);
        
        this.loadColors();
        this.listen();
        this.searchBrand();
    },
    listen: function() {
        this.$home.bind("click", $.proxy(function() {
			
            //Обнуляем соответствующие значения
			this.onColorChange();
			this.onBrandChange();
			this.tab.tabControl.disableFullSearch();	
			this.tab.opts.numLoadPage=1;
			//this.tab.tabContent.mode = "groups";
			this.tab.opts.userData.rgid = this.tab.opts.userData.gid = null;
			this.tab.opts.userData.title=this.tab.defaultTabName;	
			
			this.tab.tabContent.loadContent();			
			this.tab.setTabName(this.tab.opts.userData.title);			
			this.tab.tabController.saveTab(this.tab);

        }, this));
        
        //this.$form.bind("submit", $.proxy(this.onSearch, this));
        
        this.colorReset.bind("click", $.proxy(function() {
		
			this.colorChooser.removeClass("b-dropdown-dropped");
			this.onColorChange();
			
			//если отсутствует фильтр по бренду и перед нами или супергруппа или таб с главным содержимым
			//то фильтр по всем цветам применять не нужно, а нужно  отбразить соответственно, либо супергруппу, либо таб с главным содержимым			
			if(!this.tab.opts.userData.tabBrand){
				if(!this.tab.opts.userData.rgid && !this.tab.opts.userData.gid){
					this.tab.tabControl.disableFullSearch();
					//this.tab.tabContent.mode = "groups";
					this.tab.tabContent.loadContent();			
					this.tab.tabController.saveTab(this.tab);
				}
				else if(this.tab.opts.userData.rgid) this.tab.tabContent.loadSupergroup(this.tab.opts.userData.rgid);
				else this.getFiltered();   
			}
			else this.getFiltered();           
			
            return false;
        }, this));
    },
	searchBrand:function(){
		var obj=this;
		this.$brand = this.$form.find("input[name='brand']");
		var $select=this.$form.find("div.ys_select");
		var $list=this.$form.find('ul.brand');
	
		this.$brand.bind("click", function(e){
			e.stopPropagation();					
		});
		$(document).bind("click", $.proxy(function(e) {
			if($select.hasClass('ys_selectabs')) {
				//если мы начали что-то вводить в поле и не выбрали из предложенного, а начали тыркать в другие места на странице
				//возвращаем старое значение, если оно есть, в противном случае возвращаем пусто
				if (this.tab.opts.userData.tabBrand){				
					if(this.tab.opts.userData.tabBrand.brand != this.$brand.attr('value')){
						this.$brand.attr('value', this.tab.opts.userData.tabBrand.brand);
					}			
				}	
				else this.$brand.attr('value', '')
				
				$select.removeClass('ys_selectabs');
			}
		
		
			
				
		}, this));
		
		
		
		this.$brand.bind("keyup mouseup", $.proxy(function() {
			
			//всегда выводим возможность выбрать все бренды
			$list.empty();
			$list
				.append($('<li>Все бренды</li>')
				.click(function(){
					
					obj.$brand.attr('value', '');
					$select.removeClass('ys_selectabs');
					
					//если поле до этого было пустое,а мы опять выбрали все бренды - ничего не делаем, незачем отправлять лишний запрос 
					if (!obj.tab.opts.userData.tabBrand)  return false;			
					
					obj.tab.opts.userData.tabBrand=undefined; 
					
					//если отсутствует фильтр по цвету и перед нами или супергруппа или таб с главным содержимым
					//то фильтр по всем брендам применять не нужно, а нужно  отбразить соответственно, либо супергруппу, либо таб с главным содержимым			
					if(!obj.tab.opts.userData.tabColor){
						if(!obj.tab.opts.userData.rgid && !obj.tab.opts.userData.gid){
							obj.tab.tabControl.disableFullSearch();
							//obj.tab.tabContent.mode = "groups";
							obj.tab.tabContent.loadContent();			
							obj.tab.tabController.saveTab(obj.tab);  
						}
						else if(obj.tab.opts.userData.rgid) obj.tab.tabContent.loadSupergroup(obj.tab.opts.userData.rgid);		
						else obj.tab.tabControl.getFiltered();
					}
					else obj.tab.tabControl.getFiltered();   
					
				}));
				
			//если поле пустое - запрос не делаем
			if (!this.$brand.attr('value')) return false;
			
			$.ajax({
				url: this.getBrandsURL+'?q='+obj.$brand.attr('value'),
				type:'GET',
				dataType: "json",
				success:$.proxy(function(data) {
					var obj=this;
					if(data.length){
						$(data).each(function(indx, el){
							$list
								.append($('<li />')
								.attr('id', el.id)
								.append(el.brand)
								.click(function(){ 
								
									var val=el.brand.replace(/<\/?strong[^>]*>/g,'')
									obj.$brand.attr('value', val);
									$select.removeClass('ys_selectabs');
									
									//если выбрали то же самое, не отправляем лишний запрос
									if(obj.tab.opts.userData.tabBrand && obj.tab.opts.userData.tabBrand.brand==val) return false;
									
									obj.tab.opts.userData.tabBrand={brand:el.brand, id:el.id}; 
									obj.tab.tabControl.getFiltered();

								}));
						});
						
					}
					if(!$select.hasClass('ys_selectabs')) $select.addClass('ys_selectabs');			
				}, this)
			})								  
		}, this));
		
	},
    loadColors: function() {
        $.ajax({
            url: "/yourstyle/editor/getColors",
            dataType: "json",
            success: $.proxy(function(data) {
				var obj=this;
				for (var i=0,j=data.length;i<j;i++) {                    
					(function(color){	
						$("<li><a href='#' /></li>")
							.appendTo(obj.colorList)
							.find("a")
								.css({
									background: color.val
								})
								.attr("id", color.en)	
								.bind("click", $.proxy(function(e) {
									this.tab.opts.userData.tabColor=color;// color - {"val":"#CC0000","en":"red","ru":"красный"}
									this.getFiltered(); 
									this.colorChooser.removeClass("b-dropdown-dropped");
									return false; 
								}, obj));							
					})(data[i]);	
                }
            }, this)
        });
    },
    getFiltered: function() { 
			
			this.tab.tabContent.showLoader();
			
			
			//если мы не подгружаем страницу - скидываем номер загружаемой страницы
			if(!this.tab.opts.isLoadingPage) this.tab.opts.numLoadPage=1;
			
			
			
			//определяем параметры для запроса (id, цвет итд)			
			if(this.tab.opts.userData.rgid) var uid='rgid='+this.tab.opts.userData.rgid +'&';
			else if (this.tab.opts.userData.gid) var uid='gid='+this.tab.opts.userData.gid +'&';
			else var uid='';
			
			if(this.tab.opts.userData.tabColor) var tabColor='tabColor=' + this.tab.opts.userData.tabColor.en +'&';
			else var tabColor='';
			
			if(this.tab.opts.userData.tabBrand) var bid='bid=' + this.tab.opts.userData.tabBrand.id +'&';
			else var bid='';
			
			
			var query = uid + tabColor + bid + 'page=' + this.tab.opts.numLoadPage;
			
			
            $.ajax({
                url: "/yourstyle/editor/getFiltered?" + query,
                dataType: "json",
                success: $.proxy(function(data) {
                    if (__ajaxError.apply(window, arguments)) return;
                    
                    this.tab.tabContent.isContentLoaded = true;
					//this.tab.tabContent.mode = "items";					

					this.onColorChange(this.tab.opts.userData.tabColor);
					this.onBrandChange(this.tab.opts.userData.tabBrand);
					
					this.tab.opts.pages=data.pages;
					
                    this.enableFullSearch();		
					this.tab.setTabName(this.tab.opts.userData.title);
					this.tab.tabController.saveTab(this.tab);
                    this.tab.tabContent.onContentLoaded(data.items);
					
                }, this)
            });
        //}
        
    },
    onBrandChange: function(brand) {
		if (!brand) {
            this.$brand.attr('value', '');
			this.tab.opts.userData.tabBrand = undefined;
        }
        else {
            this.$brand.attr('value',  brand.brand);
			//this.tab.opts.userData.tabColor = color;
        }
		
    },
    onColorChange: function(color) {
		if (!color) {
            this.colorIndicator.removeAttr("style");
			this.tab.opts.userData.tabColor = undefined;
        }
        else {
            this.colorIndicator.css("background", color.val);
			this.tab.opts.userData.tabColor = color;
        }
		
    },
    
    /*onSearch: function() {
        var query = this.$queryField.val();
        var url = this.searchURL + "q=" + query + (this.tab.tabGroupId ? ("&gid=" + this.tab.tabGroupId) : "");
        if (query.length > 2) {
            $.ajax({
                url: url,
                dataType: "json",
                success: $.proxy(function(data) {
                    if (__ajaxError.apply(window, arguments)) return;
                    this.tab.setTabName(query);
                    this.tab.setSearchText(query);
                    this.enableFullSearch();
                    
                    this.tab.tabContent.mode = "items";
                    this.tab.tabContent.onContentLoaded(data);
                }, this)
            });
        }
        return false;
    },*/
    
    enableFullSearch: function() {
        this.$form.addClass(this.fullSearchClass);
    },
    disableFullSearch: function() {
        this.$form.removeClass(this.fullSearchClass);
    }
});

// Таб "Сеты"
function YSEditorSetsTab() { this.init.apply(this, arguments); };
_oop_.extend(InterfaceYSEditorTab, YSEditorSetsTab, {
    TabContent: YSEditorSetsTabContent,
    TabControl: YSEditorSetsTabControl,
    
    defaultTabName: "Сеты",
    
    switchMode: function(mode) {
        this.tabContent.showSets(mode);
        this.tabControl.setActiveTab(mode);
    },
    
    addDraft: function(id) {
        if (!this.tabContent.itemsMap) {
            setTimeout($.proxy(function() {
                this.addDraft(id);
            }, this), 100);
            return;
        }
        
        this.tabContent.removeItem(id);
        this.switchMode("drafts");
        this.tabContent.loadItem(id);
    },
    addPublished: function(id) {
        if (!this.tabContent.itemsMap) {
            setTimeout($.proxy(function() {
                this.addPublished(id);
            }, this), 100);
            return;
        }
        
        this.tabContent.removeItem(id);
        this.switchMode("published");
        this.tabContent.loadItem(id);
    }
});
function YSEditorSetsTabContent() { this.init.apply(this, arguments); };
_oop_.extend(InterfaceYSEditorTabContent, YSEditorSetsTabContent, {
    loadContentURL: "/yourstyle/editor/getUsersSets",
    isDraftClass: "isDraft",
    
    onContentLoaded: function(data) {
		this.hideLoader();
        //if (data.length) {
            this.createList(data);
        //}
        //else {
        //    this.onEmptyList();
        //}
    },
    
    createList: function(data) {
        this.itemsMap = {};
        this.$list = $("<ul class=\"ys-canvas__sets\"/>").appendTo(this.$content);
        for (var i=0, l=data.length; i<l; i++) {
            this.createItem(data[i]);
        }
        
        this.findLists();
        this.tab.switchMode("published");
    },
    findLists: function() {
        this.$items = this.$list.find("li.ys-canvas__sets_set");
        this.$drafts = this.$items.filter("." + this.isDraftClass);
        this.$published = this.$items.filter(":not(." + this.isDraftClass + ")");
    },
    createItem: function(itemData, isNewItem) {
        var item = $("<li class=\"ys-canvas__sets_set\">" +
            "<a class=\"set-image open\"><img src=\"" + itemData.image + "\"/></a>" + 
            (itemData.isDraft ? "" : "<h2 class=\"ys-canvas_sets_set_name\"><a class=open>" + itemData.title + "</a></h2>") + 
            "<ul class=\"ys-canvas__simple_tabs\">" + 
                (itemData.isDraft ? "<li><a class=open>открыть</a></li>" : "") + 
                "<li><a class=remove>удалить</a></li>" + 
            "</ul>" + 
        "</li>");
        
        isNewItem && item.addClass("ys-canvas__sets_set_active");
        item[isNewItem ? "prependTo" : "appendTo"](this.$list);
        this.itemsMap[itemData.id] = item;
        
        this.processItem(item, itemData);
    },
    processItem: function($item, itemData) {
        if (itemData.isDraft) {
            $item.find("a.open").bind("click", $.proxy(function() {
                this.tab.tabController.loadSet(itemData.id);
            }, this));
            $item.addClass(this.isDraftClass);
        }
        else {
            $item.find("a.open")
                .attr("href", "/yourstyle/set/" + itemData.id)
                .bind("click", function() {
                    if (YSEditorController.currentSet && !YSEditorController.currentSet.isSaved && !YSEditorController.currentSet.isEmpty) {
                        __modal.confirm("Вы уверены, что собираетесь покинуть эту страницу, не сохранив открытый сет?", function() {
                            location = "/yourstyle/set/" + itemData.id;
                        });
                        return false;
                    }
                });
        }
        $item.find("a.remove").bind("click", $.proxy(function() {
            this.tab.tabController.deleteSet(itemData.id, function(status) {
                if (status == "true") {
                    $item.css({
                        "visibility": "hidden"
                    });
                    $item.animate({
                        "height": 0,
                        "min-height": 0
                    }, function() {
                        $item.remove();
                    });
                }
            });
        }, this));
    },
    loadItem: function(id) {
        $.ajax({
            url: "/yourstyle/editor/loadSet/" + id,
            dataType: "json",
            success: $.proxy(function(data) {
                this.createItem(data.info, true);
                this.findLists();
            }, this)
        });
    },
    removeItem: function(id) {
        if (id in this.itemsMap) {
            this.itemsMap[id].remove();
            delete this.itemsMap[id];
        }
    },
    
    onEmptyList: function() {
        
    },
    
    showSets: function(type) {
        this.$items.hide();
        if (type == "drafts") {
            this.$drafts.show();
        }
        else {
            this.$published.show();
        }
    }
});
function YSEditorSetsTabControl() { this.init.apply(this, arguments); };
_oop_.extend(InterfaceYSEditorTabControl, YSEditorSetsTabControl, {
    setsTypesTabsLayout: "<ul class='ys-canvas__simple_tabs'><li class='published'><a>Опубликованные</a></li><li class='drafts'><a>Черновики</a></li></ul>",
    
    afterCreate: function() {
        this.setsTypesTabsList = $(this.setsTypesTabsLayout).appendTo(this.$control);
        this.setsTypesTabs = this.setsTypesTabsList.find("li");
        
        this.setsTypesTabs.filter(".drafts").bind("click", $.proxy(function() {
            this.tab.switchMode("drafts");
        }, this));
        this.setsTypesTabs.filter(".published").bind("click", $.proxy(function() {
            this.tab.switchMode("published");
        }, this));
    },
    
    setActiveTab: function(tab) {
        this.setsTypesTabs.removeClass("active");
        this.setsTypesTabs.filter("." + tab).addClass("active");
    }
});


// Таб "Мое"
function YSEditorMyTab() { this.init.apply(this, arguments); };
_oop_.extend(InterfaceYSEditorTab, YSEditorMyTab, {
    defaultTabName: "Загрузить",
    TabControl: YSEditorMyTabControl,
    TabContent: YSEditorMyTabContent,
    
    switchLayoutToItems: function() {
        this.tabControl.$addItem.show();
        this.tabControl.$switchToItems.hide();
        this.tabContent.showItems();
    },
    switchLayoutToUpload: function() {
        this.tabControl.$addItem.hide();
        this.tabControl.$switchToItems.show();
        this.tabContent.showUpload();
    }
});

function YSEditorMyTabControl() { this.init.apply(this, arguments); };
_oop_.extend(InterfaceYSEditorTabControl, YSEditorMyTabControl, {
    addItemLayout: "<a>Добавить вещь</a>",
    switchToItemsLayout: "<a>К моим вещам</a>",
    
    afterCreate: function() {
        this.$addItem = $(this.addItemLayout).appendTo(this.$control).bind("click", $.proxy(function() {
            this.tab.switchLayoutToUpload();
        }, this)).hide();
        
        this.$switchToItems = $(this.switchToItemsLayout).appendTo(this.$control).bind("click", $.proxy(function() {
            this.tab.switchLayoutToItems();
        }, this)).hide();
    }
});

function YSEditorMyTabContent() { this.init.apply(this, arguments); };
_oop_.extend(InterfaceYSEditorTabContent, YSEditorMyTabContent, {
    loadContentURL: "/yourstyle/editor/getUsersTiles",
    
    listLayout: YSEditorItemsTabContent.prototype.listLayout,
    blankImageSrc: YSEditorItemsTabContent.prototype.blankImageSrc,
    blankImageClass: YSEditorItemsTabContent.prototype.blankImageClass,
    itemCloneClass: YSEditorItemsTabContent.prototype.itemCloneClass,
    itemPopupLayout: YSEditorItemsTabContent.prototype.itemPopupLayout,
    significantDiff: YSEditorItemsTabContent.prototype.significantDiff,
    //mode: "items",
    
    uploadFormLayout: "<form autocomplete='off' class='ys-canvas__upload_form' method='POST' action='/yourstyle/editor/upload'>" +
        "<fieldset><label>Картинка<input name='file' type='file'/></label></fieldset>" +
		//запретили пользователю самому добавлять бренд
        /*"<fieldset><label>Бренд <input name='brand' type='text'/></label>" + 
			"<div class='ys_select _1'>" +
				"<div class='fon'></div>" +
				"<div class='container'>" +
					"<ul>" +
					"</ul>" +
				"</div>" +
			"</div>" +
		"</fieldset>" +*/
        //"<fieldset><label>Описание <textarea name='description'></textarea></label></fieldset>" +
        //"<fieldset class='publish'><label><input type='checkbox' name='hidden'> Не публиковать</label></fieldset>" +
        //"<fieldset class='group'><label>Группы вещей <select name='gid' /></label></fieldset>" +
        "<fieldset class='submit'><input type=submit value='Загрузить' /></fieldset>" +
        "<input type='hidden' name='type' value='yourstyle'/><input type='hidden' name='action' value='editor'/>" +
    "</form>",
    loadGroupsURL: "/yourstyle/editor/getGroups",
	getBrandsURL: "/yourstyle/editor/suggestBrands",
    afterCreate: function() {
        this.$uploadForm = $(this.uploadFormLayout).appendTo(this.$content);
        
        // field for validation
        /*var $brand = this.$uploadForm.find("input[name='brand']");
        var $file = this.$uploadForm.find("input[name='file']");
        //var $unpublish = this.$uploadForm.find(".publish input");
        //var $groupFS = this.$uploadForm.find("fieldset.group");
		//var isSelectedFon;//если выбрали фон, то поле brand необязательно для заполнения
		//var $brandFieldset=$brand.parent().parent();
		
		// select brand
		var $select=this.$uploadForm.find("div.ys_select");
		var $list=this.$uploadForm.find('ul');
		$brand.bind("click", function(e){
			e.stopPropagation();					
		});
		
		$brand.bind("keyup focus", $.proxy(function() {
			$.ajax({
				url: this.getBrandsURL+'?q='+$brand.attr('value'),
				type:'GET',
				dataType: "json",
				success:$.proxy(function(data) {
					$list.empty();
					if(!data.length){
						if($select.hasClass('ys_selectrel')) $select.removeClass('ys_selectrel');		
					}
					else{
						$(data).each(function(indx, el){
							$list.append($('<li />').attr('id', el.id).append(el.brand).click(function(){
								var val=el.brand.replace(/<\/?strong[^>]*>/g,'')
								$brand.attr('value', val);
								$select.removeClass('ys_selectrel');
							}));
						});
						if(!$select.hasClass('ys_selectrel')) $select.addClass('ys_selectrel');	
					}
				}, this)
			})								  
		}, this));
		$(document).bind("click", function(e) {
			if($select.hasClass('ys_selectrel')) $select.removeClass('ys_selectrel');			
    	});
		*/
		
        // on unpublish change
        //$unpublish.bind("click", function() {
        //    $groupFS[ this.checked ? "hide" : "show" ]();
       // });
		
		
		
        
        // load groups for select
       //$.ajax({
            //url: this.loadGroupsURL,
            //dataType: "json",
            //success: $.proxy(function(data) {
                //var $select = this.$uploadForm.find("select"),
                    //$optgroup,
                    //category,
                    //groups,
                    //group;
				//$select.bind('change', $.proxy(function() {
				//	var selectedOptgroup=$select.find('option:selected')[0].parentNode;
				//	if (selectedOptgroup.label=='фон'){
				//		isSelectedFon=true;
						//$brandFieldset.css("display", "none");
						//$brand.attr('value', '');
				//	}
				//	 else {
				//		 isSelectedFon=false;
						 //$brandFieldset.css("display", "block");
				//	 }
				//}, this));	
				
				//for (var i = 0, categoriesMax = data.length; i < categoriesMax; i++) {
                    //category = data[i];
                    //$optgroup = $("<optgroup />")
                        //.attr("label", category.title)
                        //.appendTo($select);
                    //groups = category.groups;
                    
                    //for (var j = 0, groupsMax = groups.length; j < groupsMax; j++) {
                        //group = groups[j];
                        //$("<option/>")
                            //.val(group.id)
                            //.html(group.title)
                            //.appendTo($optgroup)
                    //}
                //}
            //}, this)
        //});
        
        // submit listener
        this.$uploadForm.bind("submit", $.proxy(function() {
            //if (!$brand.val() && !isSelectedFon) {
            //    return false;
            //}
           // if (!$file.val()) {
            //    return false;
           // }
            
            this.$uploadForm.ajaxSubmit({
                dataType: "json",
                success: $.proxy(this.onUpload, this)
            });
            
            return false;
        }, this));
    },
    onUpload: function(data) {
        if (__ajaxError.apply(window, arguments)) return;
        
        this.$uploadForm.clearForm();
        this.$uploadForm.find("input[type=file]").val("");
        this.tab.switchLayoutToItems();
        
        // add new item
        $.ajax({
            url: "/yourstyle/editor/getGroupTile/" + data,
            dataType: "json",
            success: $.proxy(this.addNewItem, this)
        });
    },
    addNewItem: function(data) {
        if (__ajaxError.apply(window, arguments)) return;
        this.createOneItem(data);
    },
    
    onContentLoaded: function(data) {
        this.$uploadForm.detach();
		
        //Нам не нужно смотреть какой пред нами mode? мы и так знаем, что нам нужен список
		//от mode вообще хочу избавиться - лишний параметр
		//YSEditorItemsTabContent.prototype.onContentLoaded.apply(this, arguments);		
		this.$content.html("");
        this.$list = $(this.listLayout).appendTo(this.$content);
		YSEditorItemsTabContent.prototype.createItems.apply(this, arguments);
        
        if (data.length == 0) {
            this.tab.switchLayoutToUpload(); 
        }
        else {
            this.tab.switchLayoutToItems();
        }
    },
    /*createItems: function() {
        YSEditorItemsTabContent.prototype.createItems.apply(this, arguments);
    },*/
    createOneItem: function() {
        YSEditorItemsTabContent.prototype.createOneItem.apply(this, arguments); 
		
    },
    processItem: function() {
        YSEditorItemsTabContent.prototype.processItem.apply(this, arguments);
    },
    addItemToSet: function() {
        YSEditorItemsTabContent.prototype.addItemToSet.apply(this, arguments);
    },
    loadItemPopup: function() {
        YSEditorItemsTabContent.prototype.loadItemPopup.apply(this, arguments);
    },
    openItemPopup: function() {
        YSEditorItemsTabContent.prototype.openItemPopup.apply(this, arguments);
    },
    addPopupControls: function($item, itemData, popup) {
        var container = popup.find("ul.ys-canvas__simple_tabs");
        var $fromMyItems = $("<li><a>Удалить из моих вещей</a></li>")
            .appendTo(container)
            .bind("click", function() {
                $.ajax({
                    url: "/yourstyle/tile/" + itemData.id + "/fromMy",
                    dataType: "json",
                    success: function(data) {
                        if (__ajaxError.apply(window, arguments)) return;
                        popup.remove();
                        $item.remove();
                    }
                });
            });
        var $addToSet = $("<li><a>Добавить в сет</a></li>")
            .appendTo(container)
            .bind("click", function() {
                YSEditorController.tryAddItem($item, itemData);
            });
    },
    
    showUpload: function() {
        this.$list.detach();
        this.$uploadForm.appendTo(this.$content);
    },
    showItems: function() {
        this.$list.appendTo(this.$content);
        this.$uploadForm.detach();
    }
});


var YSEditorController = {
     tabsDefaults: [{


        constructor: YSEditorItemsTab
    },{
        constructor: YSEditorMyTab
    }, {
        constructor: YSEditorSetsTab
    }],
    newTabConstructor: YSEditorItemsTab,
    newTabLayout: "<li class=new><a>+</a></li>",
    userTabsURL: "/yourstyle/editor/getBookmarks",
    saveTabURL: "/yourstyle/editor/saveBookmark",
    removeTabURL: "/yourstyle/editor/deleteBookmark/",
    
    layouts: {
        "sections": "<div class=\"ys-canvas__sections\"/>",
        "tabsContainer": "<div class=\"ys-canvas-tabs\"/>",
        "tabsWrapper": "<div class=\"ys-canvas-tabs__wrapper\"/>",
        "tabsLeftScroller": "<a class=\"ys-canvas-tabs__scroll_left\"/>",
        "tabsRightScroller": "<a class=\"ys-canvas-tabs__scroll_right\"/>",
        "tabs": "<ul class=\"ys-canvas-tabs\"/>",
        "canvasContainer": "<div class=\"ys-canvas__canvas\"/>",
        "canvas": "<div class=\"ys-canvas\" />",
        "toolbarsContainer": "<div class=\"ys-canvas__toolbars\"/>",
        "toolbar": "<dl class=\"ys-canvas__toolbar\">",
        "itemToolbar": {
            title: "Вещь",
            cn: "ys-canvas__toolbar_item",
            tools: [{
                "cn": "remove",
                "title": "Удалить"
            },{
                "cn": "hflip",
                "title": "Отобразить по горизонтали"
            },{
                "cn": "vflip",
                "title": "Отобразить по вертикали"
            },{
                "cn": "underlay",
                "title": "Показать/скрыть подложку"
            },{
                "cn": "clone",
                "title": "Копировать"
            },{
                "cn": "zmore",
                "title": "Приблизить"
            },{
                "cn": "zless",
                "title": "Отдалить"
            }]
        },
        "setToolbar": {
            title: "Сет",
            tools: [{
                "cn": "new",
                "title": "Новый сет"
            },{
                "cn": "undo",
                "title": "Отменить действие"
            },{
                "cn": "redo",
                "title": "Повторить действие"
            },{
                "cn": "zoomin",
                "title": "Увеличить"
            },{
                "cn": "zoomout",
                "title": "Уменьшить"
            },{
                "cn": "center",
                "title": "Центрировать сет"
            },{
                "cn": "save",
                "title": "Сохранить сет"
            },{
                "cn": "publish",
                "title": "Опубликовать сет"
            }]
        }
    },
    
    jsInitClass: "ys-canvas_js_init",
    
    initVars: function(container) {
        this.$container = $(container);
        this.$container.addClass(this.jsInitClass);
        
        this.$sections = $(this.layouts.sections).appendTo(this.$container);
        this.$tabsContainer = $(this.layouts.tabsContainer).appendTo(this.$sections);
        this.$tabsWrapper = $(this.layouts.tabsWrapper).appendTo(this.$tabsContainer);
        this.$tabs = $(this.layouts.tabs).appendTo(this.$tabsWrapper);
        this.$tabsLeftScroller = $(this.layouts.tabsLeftScroller).appendTo(this.$tabsWrapper);
        this.$tabsRightScroller = $(this.layouts.tabsRightScroller).appendTo(this.$tabsWrapper);
        
        this.$canvasContainer = $(this.layouts.canvasContainer).appendTo(this.$container);
        this.$canvas = $(this.layouts.canvas).appendTo(this.$canvasContainer);
        
        this.$toolbarsContainer = $(this.layouts.toolbarsContainer).appendTo(this.$canvasContainer);;
    },
    initUnload: function() {
        $(window).bind("beforeunload", function() {
            if (YSEditorController.currentSet && !YSEditorController.currentSet.isSaved && !YSEditorController.currentSet.isEmpty) {
                return "Вы потеряете все изменения в текущем сете.";
            }
        });
    },
    init: function(container) {
        if (!container) { throw new Error("YSEditorController: Отсутствует контейнер для инициализации"); }
        
        this.initVars(container);
        this.initTabs();
        this.initToolbars();
        this.initUnload();
        this.openNewSet();
    },
    
    // toolbars
    initToolbars: function() {
        this.$setToolbar = $(this.layouts.toolbar).appendTo(this.$toolbarsContainer);
        this.$itemToolbar = $(this.layouts.toolbar).appendTo(this.$toolbarsContainer);
        
        this.$itemToolbar.addClass(this.layouts.itemToolbar.cn);
        this.$itemToolbar.append("<dt>" + this.layouts.itemToolbar.title + "</dt>");
        for (var i=0, l=this.layouts.itemToolbar.tools.length; i<l; i++) {
            var tool = this.layouts.itemToolbar.tools[i];
            this.$itemToolbar.append("<dd><a class=\"" + tool.cn + "\" title=\"" + tool.title + "\"/></dd>");
        }
        
        this.$setToolbar.append("<dt>" + this.layouts.setToolbar.title + "</dt>");
        for (var i=0, l=this.layouts.setToolbar.tools.length; i<l; i++) {
            var tool = this.layouts.setToolbar.tools[i];
            this.$setToolbar.append("<dd><a class=\"" + tool.cn + "\" title=\"" + tool.title + "\"/></dd>");
        }
        this.listenSetToolbar();
    },
    listenSetToolbar: function() {
        this.$setToolbar
            .find(".new").bind("click", $.proxy(function() {this.openNewSet();}, this)).end()
            .find(".undo").bind("click", $.proxy(this.setUndo, this)).end()
            .find(".redo").bind("click", $.proxy(this.setRedo, this)).end()
            .find(".save").bind("click", $.proxy(this.setSave, this)).end()
            .find(".center").bind("click", $.proxy(this.setCenter, this)).end()
            .find(".zoomin").bind("click", $.proxy(this.setZoomIn, this)).end()
            .find(".zoomout").bind("click", $.proxy(this.setZoomOut, this)).end()
            .find(".publish").bind("click", $.proxy(this.setPublish, this)).end();
        
        $(document).bind("keyup", $.proxy(function(e) {
            if (e.ctrlKey) {
                if (e.keyCode == 90) {
                    this.setUndo();
                }
                else if (e.keyCode == 89) {
                    this.setRedo();
                }
                return false;
            }
        }, this));
    },
    setHistoryActivity: function() {
        this.$setToolbar.find(".undo")[this.currentSet.undoHistory.length ? "removeClass" : "addClass"]("inactive");
        this.$setToolbar.find(".redo")[this.currentSet.redoHistory.length ? "removeClass" : "addClass"]("inactive");
    },
    
    // tabs
    initTabs: function() {
		this.tabs = [];
        $.each(this.tabsDefaults, $.proxy(function(i, tab) {
            this.tabs[i] = new tab.constructor(this, {
                place: this.$tabs,
                contentPlace: this.$sections,
                controlPlace: this.$tabsContainer,
                isDefault: true,
                isActive: !i,
				userData:{}, //Добавил. По умолчанию пользовательских данных нет
				pages:1, //Добавил. По умолчанию количество страниц 1
				numLoadPage:1, //Добавил. По умолчанию тзагружаем первую страницу
				isLoading:false, //Добавил. Пока загружаем данные - этот параметр в true (нужен, например, чтобы отменить повторное выполнение ф-ии при скролле)
				isLoadingPage:false, //Добавил. Если подгружаем страницу - очищать не нужно

            });
        }, this));
        
        this.newTabButton = $(this.newTabLayout).appendTo(this.$tabs);
        this.newTabButton.bind("click", $.proxy(this.addNewTab, this));
        
        this.getUserTabs();
        this.pinDefaultTabs();
        this.onWithTile();
        
        this.listenTabsScroll();
    },
    getUserTabs: function() {
        $.ajax(this.userTabsURL, {
            success: $.proxy(function(data) {
				if (__ajaxError.apply(window, arguments)) return;
				for (var i=0, l=data.length; i<l; i++) {
                    var tab = new YSEditorItemsTab(this, {
                        place: this.$tabs,
                        contentPlace: this.$sections,
                        controlPlace: this.$tabsContainer,
                        isDefault: false,
                        userData: data[i],
						numLoadPage:1, //по умолчанию всегда загружаем первую страницу
						isLoading:false, //Добавил. Пока загружаем данные - этот параметр в true (нужен, например, чтобы отменить повторное выполнение ф-ии при скролле)
						isLoadingPage:false, //Добавил. Если подгружаем страницу - очищать не нужно


                    });
                    this.tabs.push(tab);
                    
                    this.newTabButton.appendTo(this.$tabs);
                }
                
                this.relayoutTabs();
            }, this),
            dataType: "json"
        });
    },
    addNewTab: function(extra) {
		var options = {
            place: this.$tabs,
            contentPlace: this.$sections,
            controlPlace: this.$tabsContainer,
            isDefault: false,
            isActive: true,
			userData:{}, //Добавил. По умолчанию пользовательских данных нет
			pages:1, //Добавил. По умолчанию количество страниц 1
			numLoadPage:1, //Добавил. По умолчанию текущая страница первая
			isLoading:false, //Добавил. Пока загружаем данные - этот параметр в true (нужен, например, чтобы отменить повторное выполнение ф-ии при скролле)
			isLoadingPage:false, //Добавил. Если подгружаем страницу - очищать не нужно
        };
        
        if (extra !== undefined) {
            for (var eOption in extra) options[eOption] = extra[eOption];
        }
        
        this.tabs.push(
            new this.newTabConstructor(this, options)
        );
        this.newTabButton.appendTo(this.$tabs);
        this.relayoutTabs();
        
        return this.tabs[this.tabs.length-1];
    },
    onTabRemove: function(tab) {
        var index;
        for (var i=0, l=this.tabs.length; i<l; i++) {
            if (this.tabs[i] == tab) {
                index = i;
            }
        }
        this.tabs.splice(index, 1);
        
        if (tab.isOpen) {
            index = Math.max(--index, 0);
            this.tabs[index].open();
        }
        
        $.ajax({
            url: this.removeTabURL + tab.tabId
        });
        
        this.relayoutTabs();
    },
    onTabOpen: function(tab) {
        for (var i=0, l=this.tabs.length; i<l; i++) {
            if (this.tabs[i] != tab) {
                this.tabs[i].close();
            }
            else {
                this.openTabId = i;
            }
        }
        this.openTab = tab.$tab;
        this.scrollToTab(this.openTab);
    },
    saveTab: function(tab) {
        if (tab.isDefault) return;
        $.ajax({
            url: this.saveTabURL,
            type: "post",
            data: {
                type: "yourstyle",
                action: "editor",
                json: tab.getTabData()
            },
            dataType: "json",
            success: function(id) {
                if (__ajaxError.apply(window, arguments)) return;
                tab.tabId = id;
            }
        });
    },
    
    relayoutTabs: function() {
        var viewport = this.$tabsWrapper.width(),
            tabsWidth = this.newTabButton[0].offsetWidth,
            tabsLeftScroll,
            i,
            l,
            $tab;
        
        for (i=0, l=this.tabs.length; i<l; i++) {
            $tab = this.tabs[i].$tab;
            tabsWidth += $tab[0].offsetWidth;
        }
        
        if (tabsWidth > viewport) {
            this.currentTabsWidth = tabsWidth;
            this.activateTabsScroll();
        } else {
            this.deactivateTabsScroll();
        }
        
        tabsLeftScroll = this.getTabsLeftScroll();
        if (tabsWidth > viewport && tabsLeftScroll + tabsWidth < viewport) {
            this.$tabs.css("left", viewport - tabsWidth);
        }
    },
    activateTabsScroll: function() {
        this.$tabsWrapper.addClass("ys-canvas-tabs__with-scroll");
        this.scrollToTab(this.openTab);
    },
    deactivateTabsScroll: function() {
        this.$tabsWrapper.removeClass("ys-canvas-tabs__with-scroll");
		this.$tabs.css('left', 0);
        this.scrollToTab(this.openTab);
    },
    
    listenTabsScroll: function() {
        var speed = 1000, // px per sec
            timeoutfn;
        
        this.$tabsLeftScroller.bind("mousedown", $.proxy(function() {
            var left = this.getTabsLeftScroll(),
                now = new Date();
            
            timeoutfn = setInterval($.proxy(function() {
                if (left >= 0) return;
                
                var timeGone = new Date() - now;
                var shift = timeGone / 1000 * speed;
                
                left = Math.min(0, left + shift);
                now = new Date();
                
                this.$tabs.css("left", left);
            }, this), 0);
        }, this));
        
        this.$tabsRightScroller.bind("mousedown", $.proxy(function() {
            var left = this.getTabsLeftScroll(),
                viewport = parseInt( this.$tabsWrapper.width() ),
                tabsWidth = this.currentTabsWidth,
                now = new Date();
            
            timeoutfn = setInterval($.proxy(function() {
                if (left <= viewport - tabsWidth) return;
                
                var timeGone = new Date() - now;
                var shift = timeGone / 1000 * speed;
                
                left = Math.max(viewport - tabsWidth, left - shift);
                now = new Date();
                
                this.$tabs.css("left", left);
            }, this), 0);
        }, this));
        
        $(document).bind("mouseup", function() {
            clearInterval( timeoutfn );
        });
        
        // mousescroll
        var onTabsScrollUp = function() {
            var index = (this.openTabId || 0) - 1;
            if (this.tabs[index] !== undefined) {
                this.tabs[index].open();
            }
        },
            onTabsScrollDown = function() {
            var index = (this.openTabId || 0) + 1;
            if (this.tabs[index] !== undefined) {
                this.tabs[index].open();
            }
        },
            _onTabsScroll = function(e) {
            e.preventDefault();
            var direction = (e.detail && (e.detail > 0 ? "down" : "up")) || (e.wheelDelta && (e.wheelDelta < 0 ? "down" : "up")) || null;
            
            if (direction == "up") {
                onTabsScrollUp.call(this);
            }
            else if (direction == "down") {
                onTabsScrollDown.call(this);
            }
        };
        
        this.$tabs.bind("mousewheel", $.proxy(_onTabsScroll, this));
        this.$tabs.bind("DOMMouseScroll", $.proxy(_onTabsScroll, this));
    },
    
    getTabsLeftScroll: function() {
        var leftScroll = parseInt(this.$tabs.css("left"));
        
        return isNaN(leftScroll) ? 0 : leftScroll;
    },
    scrollToTab: function($tab) {
        var tab,
            tabOffsetLeft,
            tabWidth,
            viewport,
            tabsScrollLeft;
        
        tab = $tab[0];
        tabOffsetLeft = $tab.offset().left - this.$tabs.offset().left;
        tabWidth = tab.offsetWidth;
        viewport = this.$tabsWrapper.width();
        tabsScrollLeft = this.getTabsLeftScroll();
        
        if (tabOffsetLeft < -tabsScrollLeft) {
            this.$tabs.css("left", -tabOffsetLeft);
        }
        
        else if (tabOffsetLeft + tabWidth > -tabsScrollLeft + viewport) {
            this.$tabs.css("left", viewport - tabOffsetLeft - tabWidth);
        }
    },
    
    // default tab actions
    pinDefaultTabs: function() {
        this.setsTab = this.tabs[2];
    },
    
    // sets tab actions
    onSetSave: function(id) {
        this.setsTab.open();
        this.setsTab.addDraft(id);
    },
    onSetPublish: function(id) {
        this.setsTab.open();
        this.setsTab.addPublished(id);
        
        this.openNewSet();
    },
    
    
    // sets
    loadSetURL: "/yourstyle/editor/loadSet/",
    deleteSetURL: "/yourstyle/editor/deleteSet/",
    loadSet: function(id) {
        $.ajax({
            url: this.loadSetURL + id,
            dataType: "json",
            success: function(data) {
                if (__ajaxError.apply(window, arguments)) return;
                this.openNewSet(id, data.tiles);
            },
            context: this
        });
    },
    deleteSet: function(id, callback) {
        $.ajax({
            url: this.deleteSetURL + id,
            success: function(data) {
                if (__ajaxError.apply(window, arguments)) return;
                callback && callback(data);
            }
        });
    },
    openNewSet: function(id, state) {
        if (this.currentSet) {
            if (!this.currentSet.isSaved && !this.currentSet.isEmpty) {
                __modal.confirm("Вы уверены, что не хотите сохранить текущий сет?", $.proxy(function() {
                    this.currentSet.close();
                    this.currentSet = new Set(this.$canvas, id, state);
                }, this));
            }
            else {
                this.currentSet.close();
                this.currentSet = new Set(this.$canvas, id, state);
            }
        }
        else {
            this.currentSet = new Set(this.$canvas, id, state);
        }
    },
    onWithTile: function() {
        if (/withTile\/\d+$/.test(location.pathname)) {
            $.ajax({
                url: "/yourstyle/editor/getGroupTile/" + location.pathname.match(/withTile\/\d+$/)[0].replace("withTile/", ""),
                dataType: "json",
                success: $.proxy(function(data) {
                    if (__ajaxError(data)) return;
                    
                    this.tryAddItem(null, data);
                }, this)
            });
        }
    },
    tryAddItem: function($item, itemData, offset) {
        var canvasOffset = this.$canvas.offset(),
            canvasPosition = {
                l: canvasOffset.left,
                t: canvasOffset.top,
                r: canvasOffset.left + this.$canvas.width(),
                b: canvasOffset.top + this.$canvas.height()
            };
        
        if (offset) {
            if (offset.x > canvasPosition.l && offset.x < canvasPosition.r && offset.y > canvasPosition.t && offset.y < canvasPosition.b) {
                this.currentSet.addItem({
                    left: offset.x - canvasPosition.l,
                    top: offset.y - canvasPosition.t,
                    id: itemData.id,
                    image: itemData.image
                });
            }
        }
        else {
            this.currentSet.addItem({
                leftOffset: 0,
                topOffset: 0,
                id: itemData.id,
                image: itemData.image
            });
        }
    },
    setUndo: function() {
        this.currentSet.undo();
    },
    setRedo: function() {
        this.currentSet.redo();
    },
    setCenter: function() {
        this.currentSet.center();
    },
    setZoomIn: function() {
        this.currentSet.zoomIn();
    },
    setZoomOut: function() {
        this.currentSet.zoomOut();
    },
    setSave: function() {
        this.currentSet.save();
    },
    setPublish: function() {
        this.currentSet.publish();
    }
};

function standAloneItemPopup() {
    var $item = $(this),
        id = $item.attr("id").replace("ys__set_item_", "");
    
    if (!id) return;
    
    function loadItemPopupData() {
        $.ajax({
            url: "/yourstyle/editor/getGroupTile/" + id,
            dataType: "json",
            success: function(data) {
                if (__ajaxError(data)) return;

                YSEditorItemsTabContent.prototype.openItemPopup.call(
                    YSEditorItemsTabContent.prototype,
                    $item,
                    data);
            }
        });
    }
    
    $item.find("a")
        .bind("mouseover", function() {
            loadItemPopupData();
            return false;
        });
}

$(function() {
    $("div.ys-canvas-wrapper").each(function() {
        YSEditorController.init(this);
    });
    $("div#content ul.ys-canvas__stuff li").each(standAloneItemPopup);
    
    
    function closeYSPopup() {
        if (!__ysPopup) return;
        __ysPopup.remove();
        __ysPopup = null;
    };
    $(document).bind("mousedown", function(e) {
        __ysPopup && !$.contains(__ysPopup[0], e.target) && closeYSPopup();
    });
    $(document).bind("keypress", function(e) {
        __ysPopup && e.keyCode == 27 && closeYSPopup();
    });
    
    $(".b-color-chooser .b-color-chooser__chooser").each(__processColorSwitcher);
});