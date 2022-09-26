(function ($, window, document) {
    var pluginName = 'bitrixProductsBlock',
        defaults = {
            namespace: pluginName
        }

    function Plugin(element, options) {
        this.element = $(element)
        this.$elem = this.element
        this._name = pluginName
        this.settings = $.extend({}, defaults, options)
        this._defaults = defaults
        this.globalEventsInstance = globalEventsInstance

        return this.init()
    }

    Plugin.prototype = {
        options: function (option, val) {
            this.settings[option] = val
        },
        destroy: function () {
            if (this.itemsMaxIndex !== null) {
                this.globalEvents.removeResizeHandler(this.resizeHandlerId)
            }
            $.removeData(this.element, this._name)
        },
        init: function () {
            this.children = {
                $items: $('> .products-block .item', this.$elem),
            }

            if (this.children.$items.length > 1) {
                this.children.$itemNames = $('> .body > .name-wrapper', this.children.$items)
                this.children.$imageLinks = $('> .image-wrapper > .image-link', this.children.$items)
                this.globalEvents = this.globalEventsInstance()
                this.resizeHandlerId = this.globalEvents.addResizeHandler($.proxy(this.adjustHeight, this))
                this.adjustItemNames(this.getPerRow())

                var plugin = this
                // lazyload expand from thumb fix
                $('img', this.children.$imageLinks).each(function(i) {
                    var $this = $(this)
                    if ($this.is('.lazy[data-src]:not(.lazyloaded)')) {
                        $this.one('load.' + plugin.settings.namespace, function() {
                            $this.one('load.' + plugin.settings.namespace, $.proxy(plugin.adjustImageLinksColumn, plugin, i))
                        })
                    } else {
                        $this.one('load.' + plugin.settings.namespace, $.proxy(plugin.adjustImageLinksColumn, plugin, i))
                    }
                })
                /*$('img', this.children.$imageLinks).each(function(i) {
                    $(this).bind('load.' + plugin.settings.namespace, $.proxy(plugin.adjustImageLinksColumn, plugin, i))
                })*/
            } else {
                delete this.children
            }
            return this
        },

        adjustHeight: function() {
            var perRow = this.getPerRow()
            this.adjustItemNames(perRow)
            this.adjustImageLinks(perRow)
        },
        adjustItemNames: function(perRow) {
            var $items = this.children.$itemNames
            $items.css('height', '')
            if (perRow === 1) {
                return
            }
            var maxHeight = 0
            var maxI = $items.length - 1
            var $row = $()
            $items.each(function(i) {
                var $this = $(this)
                if ((i % perRow === 0) && (i > 0)) {
                    if (maxHeight > 0) {
                        $row.css('height', maxHeight + 'px')
                    }
                    $row = $()
                    maxHeight = 0
                }

                $row = $row.add($this)
                var height = $this.height()
                if (maxHeight < height) {
                    maxHeight = height
                }

                if (i === maxI) {
                    if (maxHeight > 0) {
                        $row.css('height', maxHeight + 'px')
                    }
                }
            })
        },
        adjustImageLinksColumn(i) {
            var perRow = this.getPerRow()
            var pos = i
            var mod = pos % perRow
            var rowNum = (mod > 0 ? (pos - mod) : pos) / perRow
            var start = rowNum * perRow
            var end = (rowNum + 1) * perRow
            var $items = $()
            for (var i = start; i < end; i++) {
                $items = $items.add(this.children.$imageLinks.eq(i))
            }
            $items.css({height: '', lineHeight: ''})
            var maxHeight = 0
            $items.each(function() {
                var height = $(this).height()
                if (maxHeight < height) {
                    maxHeight = height
                }
            })
            if (maxHeight > 0) {
                $items.css({height: maxHeight + 'px', lineHeight: maxHeight + 'px'})
            }
        },
        adjustImageLinks: function(perRow) {
            var $items = this.children.$imageLinks
            $items.css({height: '', lineHeight: ''})
            if (perRow === 1) {
                return
            }
            var maxHeight = 0
            var maxI = $items.length - 1
            var $row = $()
            $items.each(function(i) {
                var $this = $(this)
                if ((i % perRow === 0) && (i > 0)) {
                    if (maxHeight > 0) {
                        $row.css({height: maxHeight + 'px', lineHeight: maxHeight + 'px'})
                    }
                    $row = $()
                    maxHeight = 0
                }

                $row = $row.add($this)
                var height = $this.height()
                if (maxHeight < height) {
                    maxHeight = height
                }

                if (i === maxI) {
                    if (maxHeight > 0) {
                        $row.css({height: maxHeight + 'px', lineHeight: maxHeight + 'px'})
                    }
                }
            })
        },
        getPerRow: function() {
            var lastTop = null
            var perRow = 0
            this.children.$items.each(function(i) {
                var $this = $(this)
                var top = Math.round($this.position().top)
                if (lastTop === null) {
                    perRow++
                    lastTop = top
                } else if (lastTop !== top) {
                    return false
                } else {
                    perRow++
                }
            })
            return perRow
        }
    }

    $.fn[pluginName] = function(options) {
        var args = $.makeArray(arguments),
            after = args.slice(1)

        return this.each(function () {
            var instance = $.data(this, pluginName)

            if (instance) {
                if (instance[options]) {
                    instance[options].apply(instance, after)
                } else {
                    //$.error('Method ' + options + ' does not exist on Plugin');
                }
            } else {
                var plugin = new Plugin(this, options)

                $.data(this, pluginName, plugin)
                return plugin
            }
        })
    }
    $.fn[pluginName].prototype.defaults = defaults
    $.fn[pluginName].prototype.methods = Plugin.prototype

    var globalEventsDefaults = {
        namespace: pluginName,
        resizeTick: 500
    }

    var _globalEventsInstance = null
    function globalEventsInstance() {
        if (_globalEventsInstance === null) {
            _globalEventsInstance = new globalEvents()
        }
        return _globalEventsInstance
    }
    var globalEvents = function() {
        this.settings = globalEventsDefaults
        this.$window = $(window)
        this.window = window

        return this.init()
    }
    globalEvents.prototype = {
        init: function() {
            this.lastWidth = null
            this.lastTickWidth = null
            this.resizeTimer = null
            this.resizeHandlers = {}
            this.lastResizeHandlerId = null
            return this
        },
        addResizeHandler: function(callback) {
            if (this.lastResizeHandlerId === null) {
                this.lastResizeHandlerId = 0
                var registerEvent = true
            } else {
                this.lastResizeHandlerId++
                var registerEvent = false
            }
            this.resizeHandlers[this.lastResizeHandlerId] = callback
            if (registerEvent) {
                this.$window.bind('resize.' + this.settings.namespace, $.proxy(this.windowResize, this))
            }
            return this.lastResizeHandlerId
        },
        removeResizeHandler: function(handlerId) {
            if (handlerId in this.resizeHandlers) {
                delete this.resizeHandlers[handlerId]
            }
            if (Object.keys(this.resizeHandlers).length === 0) {
                this.$window.unbind('resize.' + this.settings.namespace)
                this.lastWidth = null
                this.lastTickWidth = null
                this.lastResizeHandlerId = null
                if (this.resizeTimer !== null) {
                    this.window.clearTimeout(this.resizeTimer)
                    this.resizeTimer = null
                }
            }
        },
        windowResize: function() {
            var width = this.$window.width()
            if (this.lastWidth === width) {
                return
            }
            this.lastWidth = width
            if (this.resizeTimer === null) {
                this.lastTickWidth = this.lastWidth
                this.resizeTimer = this.window.setTimeout($.proxy(this.resizeTick, this), this.settings.resizeTick)
            }
        },
        resizeTick: function() {
            if (this.lastTickWidth !== this.lastWidth) {
                this.lastTickWidth = this.lastWidth
                this.resizeTimer = this.window.setTimeout($.proxy(this.resizeTick, this), this.settings.resizeTick)
            } else {
                this.triggerResizeHandlers()
                this.resizeTimer = null
            }
        },
        triggerResizeHandlers: function() {
            for (var key in this.resizeHandlers) {
                this.resizeHandlers[key]()
            }
        }
    }
})(jQuery, window, document)
