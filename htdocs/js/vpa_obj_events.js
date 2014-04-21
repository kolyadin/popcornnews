/**
* Copyright Xpoint.ru
**/
// класс-обёртка, явным образом обычно не создаётся (см. ниже)
function ObjEventsHandler() { this.object = window.storage.obj_events; }

ObjEventsHandler.prototype = {
    object      : '',
    
    // возвращает все классы элемента в виде массива строк
    all         : function() {
                    return window.storage.obj_events.split(/\s+/)
                },

    // назначен ли элементу данный класс?
    exists      : function(obj_event) {
                    var events = this.all()
                    for(var i = 0; i < events.length; i++)
                        if(events[i] == obj_event) return true
                    return false
                },

    // назначает элементу класс
    add         : function(obj_event) {
                    var events = this.all()
                    for(var i = 0; i < events.length; i++)
                        if(events[i] == obj_event) return
                    window.storage.obj_events = window.storage.obj_events + " " + obj_event
                },

    // удаляет класс из назначенных элементу
    remove      : function(obj_event) {
                    var events = this.all()
                    var cn = ""
                    for(var i = 0; i < events.length; i++)
                        if(events[i] != obj_event) cn = cn + " " + events[i]
                    window.storage.obj_events = cn.substr(1)
                },

    // назначает/удаляет класс в зависимости от булевского параметра state
    set         : function(obj_event, state) {
                    if(state)
                        this.add(obj_event)
                    else
                        this.remove(obj_event)
                },

    // назначает элементу класс, если он ещё не назначен, в противном случае удаляет
    flip        : function(obj_event) {
                    if(this.exists(obj_event))
                        this.remove(obj_event)
                    else
                        this.add(obj_event)
                },
                
    toString    : function() {
                    return 'vpa_obj_events';
                }
}

// функция, создающая класс-обёртку для данного элемента
function ObjEvents() {
    return new ObjEventsHandler()
}