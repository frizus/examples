(function ($, window, document) {
    window.frizusPluginExtend({
        loadPopup: function (windowName, options, prepareFunction, method, ajaxUrl) {
            var plugin = this
            var needWindow = !(windowName in this.loadedWindows)
            var responseData = null

            if (!needWindow && !this.loadedWindowResources[windowName]) {
                return
            }

            var openPopup = function () {
                var $lastWindow
                if (plugin.currentWindow && (plugin.currentWindow in plugin.loadedWindows)) {
                    plugin.pauseTimers(plugin.currentWindow)
                    $lastWindow = plugin.loadedWindows[plugin.currentWindow]
                }

                var func

                if (options && (typeof options['prepare'] === 'function')) {
                    func = options['prepare']
                } else {
                    var prepareName = (plugin.settings.preparePrefix ? (plugin.settings.preparePrefix + windowName.replace(/^(.)/g, function (x) {
                        return x[0].toUpperCase()
                    })) : windowName) + 'Prepare'
                    if (typeof plugin[prepareName] === 'function') {
                        func = plugin[prepareName]
                    }
                }

                if (func) {
                    func.apply(plugin, [windowName, responseData, needWindow, options])
                }

                plugin.openPopup(windowName, options && options.denyClose)
                plugin.loadedWindowResources[windowName] = true
                plugin.resumeTimers(windowName)
                if ($lastWindow) {
                    plugin.enableFormSubmit($lastWindow)
                }
            }

            if ('loadAgain' in this) {
                if (this.loadAgain[windowName] === false) {
                    openPopup()
                    return
                }
            }

            this.freeXhr()
            this.disableFormSubmit()
            this.loadAjax(method || this.settings.method, ajaxUrl || this.settings.ajaxUrl, null, {
                action: windowName,
                signedParamsString: this.settings.signedParamsString,
                siteId: this.settings.siteId,
                siteTemplateId: this.settings.siteTemplateId,
                template: this.settings.template,
                needPopup: needWindow ? '1' : '0'
            }).done(function (data, textStatus, jqXHR) {
                if (needWindow) {
                    var result = plugin.moveCssJsToHead(data['html'])
                    $('body').append(result['popup'])
                    plugin.loadedWindowResources[windowName] = false
                    plugin.loadedWindows[windowName] = result['popup']
                    if ('loadAgain' in plugin) {
                        plugin.loadAgain[windowName] = !(('needAgain' in data) && (data['needAgain'] === false))
                    }
                    delete data['popup']
                    responseData = data
                    result['deferred'].always(openPopup)
                } else {
                    plugin.loadedWindowResources[windowName] = true
                    responseData = data
                    openPopup()
                }
            }).fail(function (failType, data, jqXHR, textStatus, errorThrown) {
                plugin.enableFormSubmit()

                if (failType === 'load') {
                    alert('Ошибка загрузки')
                } else if (failType === 'empty') {
                    alert('Некорректный ответ сервера')
                } else if (failType === 'not_json') {
                    alert('Некорректный ответ сервера')
                } else if (failType === 'json') {
                    if ('error' in data) {
                        if (plugin.currentWindow && (plugin.currentWindow in plugin.loadedWindows)) {
                            plugin.resolveFormError(failType, plugin.loadedWindows[plugin.currentWindow].find('form:visible').first(), data, jqXHR['status'])
                            return
                        }

                        var errorText = '';
                        var allErrors = plugin.normalizeErrors(null, data['error'])
                        var commonErrors = allErrors[1]
                        if (commonErrors['common'].length > 0) {
                            var first = true
                            $.each(commonErrors['common'], function (_, error) {
                                if (error) {
                                    errorText += error
                                    if (!first) {
                                        errorText += "\n"
                                    } else {
                                        first = false
                                    }
                                }
                            })
                        }
                    }

                    if (errorText) {
                        alert(errorText)
                    } else {
                        alert('Сервер не написал ошибку')
                    }
                }
            })
        }, openPopup: function (windowName, denyClose) {
            var plugin = this
            var instance = $.fancybox.getInstance()
            if (instance) {
                instance.opts.manualClosing = true
                $.fancybox.getInstance('close', null, 0)
            }
            var options = {
                hideScrollbar: false, closeExisting: true, touch: false, autoFocus: false, lang: 'ru', i18n: {
                    'ru': {
                        CLOSE: 'Закрыть',
                        NEXT: 'Следующий',
                        PREV: 'Предыдущий',
                        ERROR: 'The requested content cannot be loaded. <br/> Please try again later.',
                        PLAY_START: 'Начать слайдшоу',
                        PLAY_STOP: 'Приостановить слайдшоу',
                        FULL_SCREEN: 'В полный экран',
                        THUMBS: 'Thumbnails'
                    }
                }, beforeClose: function (instance) {
                    if (!instance.opts.manualClosing) {
                        if ('currentWindow' in this) {
                            plugin.enableFormSubmit()
                            plugin.pauseTimers(this.currentWindow)
                            delete this.currentWindow
                        }

                        plugin.freeXhr()

                        if (plugin.reloadOnClose) {
                            window.location.href = window.location.href
                        }
                    }
                }
            }
            if (denyClose) {
                options = $.extend(true, options, {
                    clickOutside: false, clickSlide: false, btnTpl: {
                        close: '', smallBtn: '',
                    }, keyboard: false
                })
            }
            if (instance) {
                options.animationEffect = false
            }
            $.fancybox.open(this.loadedWindows[windowName], options)
            this.currentWindow = windowName
        }, closePopup: function (reloadOnClose) {
            var instance = $.fancybox.getInstance()
            if (instance) {
                if (reloadOnClose) {
                    this.reloadOnClose = true
                }
                $.fancybox.getInstance('close')
                return true
            }

            return false
        }, reloadPage: function () {
            if (!this.closePopup(true)) {
                window.location.href = window.location.href
            }
        }, moveCssJsToHead: function (html) {
            var $head = $('head')
            var $html = $('<div>' + html + '</div>')
            var jsSelector = 'script[src]'
            var cssSelector = 'link[type="text/css"]'
            var $oldCss = $('html').find(cssSelector)
            var $oldJs = $('html').find(jsSelector)
            var $resources = $()
            var addedJs = {}
            var $newJs = $()
            var deferred = $.Deferred()

            $html.find('style, script, ' + cssSelector).each(function () {
                var $resource = $(this)
                var type
                var tagName = $resource.prop('tagName')

                if (tagName === 'STYLE') {
                    type = 'raw_css'
                } else if (tagName === 'SCRIPT') {
                    type = $resource.is('[src]') ? 'js' : 'raw_js'
                } else if (tagName === 'LINK') {
                    type = 'css'
                }

                if ((type === 'raw_css') || (type === 'raw_js')) {
                    $resources = $resources.add($resource)
                } else if (type === 'css') {
                    var cssHref = $resource.attr('href')
                    var alreadyHave = false
                    $oldCss.each(function () {
                        if (cssHref === $(this).attr('href')) {
                            alreadyHave = true
                            return false
                        }
                    })
                    if (!alreadyHave) {
                        $resources = $resources.add($resource)
                    }
                } else if (type === 'js') {
                    var newJsSrc = $resource.attr('src')
                    var alreadyHave = false
                    $oldJs.each(function () {
                        if (newJsSrc === $(this).attr('src')) {
                            alreadyHave = true
                            return false
                        }
                    })
                    if (!alreadyHave) {
                        addedJs[newJsSrc] = null
                        $resources = $resources.add($resource)
                    }
                }
            }).remove()

            var haveAddedJs = Object.keys(addedJs).length > 0
            var head = document.getElementsByTagName('head')[0]

            if (($resources.length > 0)) {
                $resources.each(function () {
                    var tagName = $(this).prop('tagName')
                    if (tagName === 'SCRIPT' && $(this).is('[src]')) {
                        var script = document.createElement('script')
                        script.async = false
                        head.appendChild(script)
                        script.addEventListener('load', function () {
                            delete addedJs[this.getAttribute('src')]
                            if (Object.keys(addedJs).length === 0) {
                                deferred.resolve()
                            }
                        })
                        script.addEventListener('error', function () {
                            delete addedJs[this.getAttribute('src')]
                            if (Object.keys(addedJs).length === 0) {
                                deferred.resolve()
                            }
                        })
                        script.src = $(this).attr('src')
                    } else {
                        $(head).append($(this))
                    }
                })
            }
            if (!haveAddedJs) {
                deferred.resolve()
            }

            return {deferred: deferred, popup: $html.find('> *')}
        }
    })
})(jQuery, window, document);