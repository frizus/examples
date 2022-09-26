(function ($, window, document) {
    var pluginName = 'bitrixProductAction',
        defaults = {
            namespace: pluginName,
            dataKey: pluginName + 'Data',
            timeout: 30 * 1000,
            method: 'post',
            checkState: false,
            buy: {
                method: 'post',
                url: '/catalog/buy.php',
                queryData: null,
                postData: function() {
                    return {
                        id: this.$elem.attr('data-product'),
                    }
                },
                selector: 'a.buy',
                basketUrl: '/basket/',
                addedClass: 'added',
                added: function($buttons) {
                    var settings = this.settings.buy
                    $buttons.each(function() {
                        var $this = $(this)
                        if (!$this.hasClass(settings.addedClass)) {
                            $this
                                .attr('href', settings.basketUrl)
                                .addClass(settings.addedClass)
                            if ($this.hasClass('buy-light')) {
                                $this.html(`
<svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18">
    <path d="M1005.97,4556.22l-1.01,4.02a0.031,0.031,0,0,0-.01.02,0.87,0.87,0,0,1-.14.29,0.423,0.423,0,0,1-.05.07,0.7,0.7,0,0,1-.2.18,0.359,0.359,0,0,1-.1.07,0.656,0.656,0,0,1-.21.08,1.127,1.127,0,0,1-.18.03,0.185,0.185,0,0,1-.07.02H993c-0.03,0-.056-0.02-0.086-0.02a1.137,1.137,0,0,1-.184-0.04,0.779,0.779,0,0,1-.207-0.08c-0.031-.02-0.059-0.04-0.088-0.06a0.879,0.879,0,0,1-.223-0.22s-0.007-.01-0.011-0.01a1,1,0,0,1-.172-0.43l-1.541-6.14H988a1,1,0,1,1,0-2h3.188a0.3,0.3,0,0,1,.092.02,0.964,0.964,0,0,1,.923.76l1.561,6.22h9.447l0.82-3.25a1,1,0,0,1,1.21-.73A0.982,0.982,0,0,1,1005.97,4556.22Zm-7.267.47c0,0.01,0,.01,0,0.01a1,1,0,0,1-1.414,0l-2.016-2.03a0.982,0.982,0,0,1,0-1.4,1,1,0,0,1,1.414,0l1.305,1.31,4.3-4.3a1,1,0,0,1,1.41,0,1.008,1.008,0,0,1,0,1.42ZM995,4562a3,3,0,1,1-3,3A3,3,0,0,1,995,4562Zm0,4a1,1,0,1,0-1-1A1,1,0,0,0,995,4566Zm7-4a3,3,0,1,1-3,3A3,3,0,0,1,1002,4562Zm0,4a1,1,0,1,0-1-1A1,1,0,0,0,1002,4566Z" transform="translate(-987 -4550)"></path>
</svg>
                                `).attr('title', 'В корзине')
                            } else if ($this.hasClass('buy-mobile')) {
                                $this.html(`
<svg xmlns="http://www.w3.org/2000/svg" width="11" height="8" viewBox="0 0 11 8">
    <path d="M1408.83,622.835l-6.98,7v0.017a0.51,0.51,0,0,1-.36.144,0.421,0.421,0,0,1-.06-0.011,0.511,0.511,0,0,1-.3-0.133l-3.01-3.029a0.5,0.5,0,0,1,0-.7,0.522,0.522,0,0,1,.72,0l2.65,2.67,6.64-6.664a0.5,0.5,0,0,1,.7,0A0.5,0.5,0,0,1,1408.83,622.835Z" transform="translate(-1398 -622)"/>
</svg>В корзине`)
                            } else if ($this.hasClass('button')) {
                                $this.html('В корзине')
                            } else {
                                $this.attr('title', 'В корзине')
                            }
                        }
                    })
                },
                removed: function($buttons) {
                    var settings = this.settings.buy
                    $buttons.each(function() {
                        var $this = $(this)
                        if ($this.hasClass(settings.addedClass)) {
                            $this
                                .attr('href', 'javascript:void(0)')
                                .removeClass(settings.addedClass)
                            if ($this.hasClass('buy-light')) {
                                $this.html(`
<svg width="19" height="16" viewBox="0 0 19 16">
    <path d="M956.047,952.005l-0.939,1.009-11.394-.008-0.952-1-0.953-6h-2.857a0.862,0.862,0,0,1-.952-1,1.025,1.025,0,0,1,1.164-1h2.327c0.3,0,.6.006,0.6,0.006a1.208,1.208,0,0,1,1.336.918L943.817,947h12.23L957,948v1Zm-11.916-3,0.349,2h10.007l0.593-2Zm1.863,5a3,3,0,1,1-3,3A3,3,0,0,1,945.994,954.005ZM946,958a1,1,0,1,0-1-1A1,1,0,0,0,946,958Zm7.011-4a3,3,0,1,1-3,3A3,3,0,0,1,953.011,954.005ZM953,958a1,1,0,1,0-1-1A1,1,0,0,0,953,958Z" transform="translate(-938 -944)"></path>
</svg>
                                `).attr('title', 'В корзину')
                            } else if ($this.hasClass('button')) {
                                $this.html('В корзину')
                            } else if ($this.hasClass('buy-mobile')) {
                                $this.html('В корзину')
                            } else {
                                $this.attr('title', 'В корзину')
                            }
                        }
                    })
                }
            },
            favorite: {
                method: 'post',
                url: '/catalog/favorite.php',
                queryData: null,
                postData: function($button) {
                    var data = {
                        id: this.$elem.attr('data-product'),
                    }
                    if ($button.hasClass(this.settings.favorite.addedClass)) {
                        data['remove'] = true
                    }
                    return data
                },
                selector: 'a.add-favorite',
                addedClass: 'added',
                added: function($buttons) {
                    var settings = this.settings.favorite
                    $buttons.each(function() {
                        var $this = $(this)
                        if (!$this.hasClass(settings.addedClass)) {
                            $this
                                .addClass(settings.addedClass)
                                .attr('title', 'В отложенных')
                        }
                    })
                },
                removed: function($buttons) {
                    var settings = this.settings.favorite
                    $buttons.each(function() {
                        var $this = $(this)
                        if ($this.hasClass(settings.addedClass)) {
                            $this
                                .removeClass(settings.addedClass)
                                .attr('title', 'Отложить')
                        }
                    })
                }
            },
            compare: {
                method: 'post',
                url: '/catalog/compare_action.php',
                queryData: null,
                postData: function($button) {
                    var data = {
                        id: this.$elem.attr('data-product'),
                    }
                    if ($button.hasClass(this.settings.compare.addedClass)) {
                        data['remove'] = true
                    }
                    return data
                },
                selector: 'a.add-compare',
                addedClass: 'added',
                added: function($buttons) {
                    var settings = this.settings.compare
                    $buttons.each(function() {
                        var $this = $(this)
                        if (!$this.hasClass(settings.addedClass)) {
                            $this
                                .addClass(settings.addedClass)
                                .attr('title', 'В сравнении')
                        }
                    })
                },
                removed: function($buttons) {
                    var settings = this.settings.compare
                    $buttons.each(function() {
                        var $this = $(this)
                        if ($this.hasClass(settings.addedClass)) {
                            $this
                                .removeClass(settings.addedClass)
                                .attr('title', 'Сравнить')
                        }
                    })
                }
            },
            quickLook: {
                selector: 'a.quick-look'
            }
        },
        actions = ['buy', 'favorite', 'compare']

    function Plugin(element, options) {
        this.element = $(element)
        this.$elem = this.element
        this._name = pluginName
        this.settings = $.extend({}, defaults, options)
        this._defaults = defaults
        this.actions = actions
        this.$window = $(window)

        return this.init()
    }

    Plugin.prototype = {
        options: function(option, val) {
            this.settings[option] = val
        },
        destroy: function() {
            if (this.xhr !== null) {
                this.xhr.abort()
            }
            for (var key in this.actions) {
                var action = this.actions[key]
                var settings = this.settings[action]
                var $button = this.$elem.find(settings.selector)
                $button.unbind('click.' + this.settings.namespace)
            }
            this.$window.bitrixProductManager('removeChangedHandler', this.handlerId)
            $.removeData(this.$elem, this.settings.dataKey)
            $.removeData(this.element, this._name)
        },
        init: function() {
            this.xhr = null
            this.actionHandlers = {}
            this.successCallbacks = {}
            this.alwaysCallbacks = {}

            var plugin = this
            for (var key in this.actions) {
                var action = this.actions[key]
                var settings = this.settings[action]
                var $button = $(settings.selector, this.$elem)
                this.actionHandlers[action] = {
                    added: $.proxy(settings.added, this, $button),
                    removed: $.proxy(settings.removed, this, $button)
                }
                this.successCallbacks[action] = {
                    added: $.proxy(this.sendChangedState, this, action, true),
                    removed: $.proxy(this.sendChangedState, this, action, false)
                }
                this.alwaysCallbacks[action] = $.proxy(this.freeBusy, this, action)
                $button.each(function() {
                    $(this).bind('click.' + plugin.settings.namespace, $.proxy(plugin.action, plugin, this, action))
                })
            }
            var product = this.$elem.attr('data-product')
            this.handlerId = this.$window.bitrixProductManager('addChangedHandler', product, $.proxy(this.setStateHandler, this))
            if (this.settings.checkState) {
                this.$window.bitrixProductManager('checkState', product, this.handlerId, true)
            }
            // TODO this is legacy
            BX.addCustomEvent('onCompleteAction', function(eventData, _this) {
                if (eventData.action === 'loadForm') {
                    if ($(_this).hasClass('one_click_buy_trigger')) {
                        $('.products a.quick-buy.clicked').removeClass('clicked')
                    }
                }
            })
            return this
        },
        setStateHandler: function(action, added) {
            if (added) {
                this.actionHandlers[action]['added']()
            } else {
                this.actionHandlers[action]['removed']()
            }
        },
        sendChangedState: function(action, added) {
            var product = this.$elem.attr('data-product')
            var called = false
            if (added) {
                if (action === 'buy') {
                    this.$window.bitrixProductManager('changed', product, {[action]: added, 'favorite': false})
                    called = true
                } else if (action === 'favorite') {
                    this.$window.bitrixProductManager('changed', product, {[action]: added, 'buy': false})
                    called = true
                }
            }
            if (!called) {
                this.$window.bitrixProductManager('changed', product, action, added)
            }
            this.$window.bitrixProductManager('updateBasket', action)
        },
        action: function(button, action, e) {
            var settings = this.settings[action]
            var remove = $(button).hasClass(settings.addedClass)
            if ((action === 'buy') && remove) {
                return
            }
            e.preventDefault()

            if (this.makeBusy(action)) {
                this.sendAjax(
                    settings.method,
                    settings.url,
                    typeof settings.queryData === 'function' ? $.proxy(settings.queryData, this, $(button))() : settings.queryData,
                    typeof settings.postData === 'function' ? $.proxy(settings.postData, this, $(button))() : settings.postData,
                    !remove ? this.successCallbacks[action]['added'] : this.successCallbacks[action]['removed'],
                    this.alwaysCallbacks[action]
                )
            }
        },
        makeBusy: function(action) {
            var busy = false
            var status = this.$elem.data(this.settings.dataKey)
            if (typeof status === typeof undefined) {
                status = {
                    [action]: true
                }
                this.$elem.data(this.settings.dataKey, status)
                return true
            } else {
                if ((action === 'buy') || action === 'favorite') {
                    var busy = ('buy' in status) || ('favorite' in status)
                    if (!busy) {
                        status[action] = true
                        this.$elem.data(this.settings.dataKey, status)
                    }
                    return !busy
                } else {
                    var busy = action in status
                    if (!busy) {
                        status[action] = true
                        this.$elem.data(this.settings.dataKey, status)
                    }
                    return !busy
                }
            }
        },
        freeBusy: function(action) {
            var status = this.$elem.data(this.settings.dataKey)
            if (typeof status !== typeof undefined) {
                if (action in status) {
                    delete status[action]
                }
                if (Object.keys(status).length === 0) {
                    $.removeData(this.$elem, this.settings.dataKey)
                } else {
                    this.$elem.data(this.settings.dataKey, status)
                }
            }
        },
        ajaxDone: function (successCallback, data, textStatus, jqXHR) {
            if (!('status' in jqXHR) || parseInt(jqXHR['status']) !== 200) {
                this.statusNot200(false, jqXHR, data, textStatus)
            } else {
                var responseJson = this.parseResponse(data)
                if ((responseJson !== null) && (responseJson['status'] === 'success')) {
                    successCallback()
                }
            }
        },
        sendAjax: function(method, url, queryData, postData, successCallback, alwaysCallback) {
            var ajaxSettings = {
                url: url,
                type: method,
                dataType: 'html',
                timeout: this.settings.timeout,
                beforeSend: $.proxy(this.ajaxBeforeSend, this)
            }
            if (queryData !== null) {
                var data = queryData
                if (data && typeof data !== 'string') {
                    data = $.param(data, false)
                }
                ajaxSettings.url = ajaxSettings.url.replace(/#.*$/, '')
                ajaxSettings.url += (/\?/.test(ajaxSettings.url) ? '&' : '?') + data
            }

            var hasContent = !/^(?:GET|HEAD)$/.test(ajaxSettings.type.toUpperCase());
            if (hasContent && (postData !== null)) {
                ajaxSettings['data'] = postData
            }
            this.xhr = $.ajax(ajaxSettings)
                .done($.proxy(this.ajaxDone, this, successCallback))
                .fail($.proxy(this.ajaxFail, this))
                .always($.proxy(this.ajaxAlways, this, alwaysCallback))
        },
        ajaxBeforeSend: function(jqXHR, settings) {

        },
        ajaxFail: function (jqXHR, textStatus, errorThrown) {
            this.statusNot200(true, jqXHR, null, textStatus, errorThrown)
        },
        statusNot200: function(isError, jqXHR, data, textStatus, errorThrown) {

        },
        parseResponse: function(data) {
            var responseJson = null
            try {
                responseJson = JSON.parse(data)
                if (!('status' in responseJson)) {
                    responseJson = null
                }
            } catch (e) {
                responseJson = null
            }
            return responseJson
        },
        ajaxAlways: function(alwaysCallback, dataOrJqXHR, textStatus, jqXHRorErrorThrown) {
            alwaysCallback()
        },
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
})(jQuery, window, document)
