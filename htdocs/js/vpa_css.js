/**
* Copyright Xpoint.ru
**/
// класс-обёртка, явным образом обычно не создаётся (см. ниже)
function CssClassesHandler(object) { this.object = object }

CssClassesHandler.prototype = {
    object      : null,
    
    // возвращает все классы элемента в виде массива строк
    all         : function() {
                    return this.object.className.split(/\s+/)
                },

    // назначен ли элементу данный класс?
    exists      : function(className) {
                    var classes = this.all()
                    for(var i = 0; i < classes.length; i++)
                        if(classes[i] == className) return true
                    return false
                },

    // назначает элементу класс
    add         : function(className) {
                    var classes = this.all()
                    for(var i = 0; i < classes.length; i++)
                        if(classes[i] == className) return
                    this.object.className = this.object.className + " " + className
                },

    // удаляет класс из назначенных элементу
    remove      : function(className) {
                    var classes = this.all()
                    var cn = ""
                    for(var i = 0; i < classes.length; i++)
                        if(classes[i] != className) cn = cn + " " + classes[i]
                    this.object.className = cn.substr(1)
                },

    // назначает/удаляет класс в зависимости от булевского параметра state
    set         : function(className, state) {
                    if(state)
                        this.add(className)
                    else
                        this.remove(className)
                },

    // назначает элементу класс, если он ещё не назначен, в противном случае удаляет
    flip        : function(className) {
                    if(this.exists(className))
                        this.remove(className)
                    else
                        this.add(className)
                }
}

// функция, создающая класс-обёртку для данного элемента
function CssClasses(object) {
    return new CssClassesHandler(object)
}