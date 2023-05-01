(function ($, window, document) {
    window.frizusPlugin = function (settings) {
        if (!('pluginName' in settings) || !('prototype' in settings)) {
            return false
        }

        settings.defaults = settings.defaults || {}
        settings.defaults.namespace = settings.defaults.namespace || settings.pluginName
        settings.plugin = settings.plugin || function (element, options) {
            this.element = element
            this.$elem = $(this.element)
            this._name = pluginName
            this._defaults = $.fn[pluginName].prototype.defaults
            this.settings = $.extend({}, this._defaults, options)

            return this.init()
        }

        var pluginName = settings.pluginName
        var plugin = settings.plugin
        var defaults = settings.defaults
        var prototype = settings.prototype

        for (var cursor in prototype) {
            plugin.prototype[cursor] = prototype[cursor]
        }
        addMethods(commonMethods, plugin.prototype)
        if (pluginName in pluginMethods) {
            addMethods(pluginMethods[pluginName], plugin.prototype)
        }
        addInstalledPlugin(pluginName, plugin)

        $.fn[pluginName] = function (options) {
            var args = $.makeArray(arguments), after = args.slice(1), methodCall = typeof options === 'string',
                methodResult = undefined, first = true

            var eachResult = this.each(function () {
                var instance = $.data(this, pluginName)

                if (instance) {
                    if (instance[options]) {
                        if (first) {
                            methodResult = instance[options].apply(instance, after)
                        } else {
                            instance[options].apply(instance, after)
                        }
                    } else {
                        //$.error('Method ' + options + ' does not exist on Plugin')
                    }
                } else {
                    var pluginObject = new plugin(this, options)

                    $.data(this, pluginName, pluginObject)
                }

                if (first) {
                    first = false
                }
            })

            if (methodCall) {
                return methodResult
            }

            return eachResult
        }

        $.fn[pluginName].prototype.defaults = defaults
        $.fn[pluginName].prototype.methods = plugin.prototype
    }

    var installedPlugins = {}
    var commonMethods = {}
    var pluginMethods = {}

    function addInstalledPlugin(pluginName, plugin) {
        if (!(pluginName in installedPlugins)) {
            installedPlugins[pluginName] = null
        }
    }

    function addMethods(methods, prototype) {
        for (var cursor in methods) {
            if (!(cursor in prototype)) {
                prototype[cursor] = methods[cursor]
            }
        }
    }

    window.frizusPluginExtend = function (pluginName, methods) {
        var common = typeof pluginName !== 'string'
        if (common) {
            methods = pluginName
        }
        if (common) {
            addMethods(methods, commonMethods)

            for (var pluginName in installedPlugins) {
                addMethods(methods, $.fn[pluginName].prototype.methods)
            }
        } else {
            if (!(pluginName in pluginMethods)) {
                pluginMethods[pluginName] = {}
            }
            addMethods(methods, pluginMethods[pluginName])
            if (pluginName in $.fn) {
                addMethods(methods, $.fn[pluginName].prototype.methods)
            }
        }
    }
})(jQuery, window, document);