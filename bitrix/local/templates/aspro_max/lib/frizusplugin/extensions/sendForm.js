(function ($, window, document) {
    window.frizusPluginExtend({
        sendForm: function (action, $form, options, method, ajaxUrl, inputGroup, excludeFromGroup, extra) {
            var plugin = this

            var post = this.getFormData($form, inputGroup, extra, excludeFromGroup, options && options.noFormParams)

            if (options && options.removeCommonErrors) {
                $form.data('Parsley').removeCommonBackendErrors()
            }
            this.freeXhr()
            this.disableFormSubmit()
            this.loadAjax(method || this.settings.method, ajaxUrl || this.settings.ajaxUrl, null, post).done(function (data, textStatus, jqXHR) {
                var $lastWindow
                if (plugin.currentWindow && (plugin.currentWindow in plugin.loadedWindows)) {
                    $lastWindow = plugin.loadedWindows[plugin.currentWindow]
                }

                var func

                if (options && (typeof options['success'] === 'function')) {
                    func = options['success']
                } else {
                    var successName = (plugin.settings.requestPrefix ? (plugin.settings.requestPrefix + action.replace(/^(.)/g, function (x) {
                        return x[0].toUpperCase()
                    })) : action) + 'Success'
                    if (typeof plugin[successName] === 'function') {
                        func = plugin[successName]
                    }
                }

                if (func) {
                    func.apply(plugin, [data, $form, options, textStatus, jqXHR])
                }
                if ($lastWindow && !(options && options.dontEnableFormSubmit)) {
                    plugin.enableFormSubmit($lastWindow)
                }
            }).fail(function (failType, data, jqXHR, textStatus, errorThrown) {
                plugin.enableFormSubmit()

                var func

                if (options && (typeof options['fail'] === 'function')) {
                    func = options['fail']
                } else {
                    var failName = (plugin.settings.requestPrefix ? (plugin.settings.requestPrefix + action.replace(/^(.)/g, function (x) {
                        return x[0].toUpperCase()
                    })) : action) + 'Fail'
                    if (typeof plugin[failName] === 'function') {
                        func = plugin[failName]
                    }
                }

                if (func) {
                    func.apply(plugin, [failType, $form, data, options, jqXHR, textStatus, errorThrown])
                }
            })
        }, getFormData: function ($form, inputGroup, extra, excludeFromGroup, noFormParams) {
            var inputGroup = inputGroup || this.settings.inputGroup || 'form'
            var excludeFromGrouping = excludeFromGrouping || this.settings.excludeFromGrouping || ['sessid']
            $form = $form.first()

            var formData = new FormData()

            if (!noFormParams) {
                $form.find(':input[name]:not([disabled])').each(function () {
                    var name = $(this).attr('name')
                    var newName

                    if ((excludeFromGrouping === true) || ($.inArray(name, excludeFromGrouping) !== -1)) {
                        newName = name
                    } else {
                        newName = inputGroup + '['

                        var pos = name.indexOf('[')
                        if (pos !== -1) {
                            newName += name.substr(0, pos) + ']' + name.substr(pos)
                        } else {
                            newName += name + ']'
                        }
                    }

                    if ($(this).is('[type="file"]')) {
                        $.each(this.files, function (_, file) {
                            formData.append(newName, file)
                        })
                    } else {
                        formData.append(newName, $(this).val())
                    }
                })
            }

            if (extra) {
                for (var cursor in extra) {
                    formData.append(cursor, extra[cursor].toString())
                }
            }

            return formData
        }, sendFormToAjaxDefault: function (action, $form, options, method, ajaxUrl, inputGroup, excludeFromGroup) {
            this.sendForm(action, $form, options, method, ajaxUrl, inputGroup, excludeFromGroup, {
                action: action,
                signedParamsString: this.settings.signedParamsString,
                siteId: this.settings.siteId,
                siteTemplateId: this.settings.siteTemplateId,
                template: this.settings.template,
            })
        }
    })
})(jQuery, window, document);