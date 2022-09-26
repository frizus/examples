(function ($, window, document) {
    var pluginName = 'remoteSelect',
        defaults = {
            namespace: 'remoteSelect',
            loadingClass: 'loading',
            timeout: 30 * 1000,
            method: 'post',
            ajaxGetData: function(updateButton) {
                return {
                    update: updateButton ? true : false,
                    server_id: this.children.$serverId.val()
                }
            },
            ajaxPostData: function(updateButton) {
                return {
                    'input_value': this.children.$valueInput.val()
                }
            },
            errorAnimateShowClass: 'blink',
            serverSelect: '.receiving-domains-select',
            widget: '.remote-select-widget',
            select: '.remote-select',
            rootId: 'null',
            rootName: null,
            error: '.remote-select-error',
            updateButton: '.remote-select-refresh',
            serverId: '.receiving_domain_id',
            valueInput: '.remote-select-value-input',
            optionTextsWrapper: '.remote-select-options-texts',
            optionTextsTypeWrapperGeneralClass: 'remote-select-options-text-type',
            optionTextFill: null,
            existingClass: 'existing',
            nonExistingClass: 'non-existing',
            existingTitle: '<div class="font-weight-bold">Выбрано</div>',
            nonExistingTitle: '<div class="font-weight-bold">Не привязано</div>',
            existingOptionHtml: `<div class="selected-option">
                <span class="selected-option-label"></span>
                <a href="javascript:void(0)" title="Удалить" class="delete-selected-option text-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </a>
            </div>`,
            nonExistingOptionHtml: `<div class="selected-option">
                <span class="selected-option-label"></span>
                <a href="javascript:void(0)" title="Удалить" class="delete-selected-option text-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </a>
                <a href="javascript:void(0)" class="restore-selected-option text-success">Восстановить</a>
            </div>`,
            optionText: '.selected-option',
            optionTextLabel: '.selected-option-label',
            optionTextNameClass: 'option-label-name',
            optionTextIdClass: 'option-label-id',
            optionTextDisabledClass: 'option-label-disabled',
            optionTextDisabledText: '(не активно)',
            optionTextDeleteButton: '.delete-selected-option',
            optionTextRestoreButton: '.restore-selected-option',
        };

    function Plugin(element, options) {
        this.element = $(element);
        this.$elem = $(this.element);
        this._name = pluginName;
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;

        return this.init();
    }

    Plugin.prototype = {
        options: function(option, val) {
            this.settings[option] = val;
        },
        destroy: function() {
            this.children.$serverSelect.unbind('change.' + this.settings.namespace)
            this.children.$updateButton.unbind('click.' + this.settings.namespace)
            this.children.$select.unbind('change.' + this.settings.namespace)
            $(this.children.$optionTextsWrapper).empty()
            this.xhr.abort()
            $.removeData(this.element[0], pluginName)
        },
        init: function() {
            if (!('url' in this.settings) || !('method' in this.settings)) {
                return null;
            }
            this.ajax = {
                method: null,
                url: null
            }
            this.inputValue = null
            this.nonExistingValues = null
            this.xhr = null
            this.children = {
                $serverSelect: $(this.settings.serverSelect, this.$elem),
                $serverId: $(this.settings.serverId, this.$elem),
                $widget: null,
                $updateButton: null,
                $select: null,
                $error: null,
                $valueInput: null,
                $optionTextsWrapper: null,
            }

            var plugin = this
            this.children.$serverSelect.each(function() {
                $(this).bind('change.' + plugin.settings.namespace, $.proxy(plugin.serverSelect, plugin, this))
            })
            this.bindWidget()
        },
        bindWidget: function() {
            this.children.$widget = $(this.settings.widget, this.$elem)
            this.children.$updateButton = $(this.settings.updateButton, this.$elem)
            this.children.$select = $(this.settings.select, this.$elem)
            this.children.$error = $(this.settings.error, this.$elem)
            this.children.$valueInput = $(this.settings.valueInput, this.$elem)
            this.children.$optionTextsWrapper = $(this.settings.optionTextsWrapper, this.$elem)

            this.readInputValue()
            this.processInputValue()

            var plugin = this
            this.children.$updateButton.each(function() {
                $(this).bind('click.' + plugin.settings.namespace, $.proxy(plugin.updateButton, plugin, this))
            })
            this.children.$select.each(function() {
                $(this).bind('change.' + plugin.settings.namespace, $.proxy(plugin.selectOnChange, plugin, this))
            })
        },
        updateButton: function(elem, e) {
            e.preventDefault()
            if (!this.$elem.hasClass(this.settings.loadingClass)) {
                this.sendAjax(true)
            }
        },
        readInputValue: function() {
            try {
                var val = this.children.$valueInput.val()
                if (typeof val !== undefined && val !== false && val !== '') {
                    this.inputValue = JSON.parse(this.children.$valueInput.val())
                } else {
                    this.inputValue = []
                }
            } catch (e) {
                this.inputValue = []
            }
        },
        setInputValue: function(setSelectValue) {
            var valueInput = []
            if (setSelectValue) var selectValues = []
            $(this.settings.optionText, $('.' + this.settings.existingClass)).each(function() {
                var id = $(this).data('id')
                var val = {
                    'id': id
                }
                var name = $(this).data('name')
                if (typeof name !== 'undefined') {
                    val['name'] = name
                }
                valueInput.push(val)
                if (setSelectValue) selectValues.push(id)
            })
            for (var key in this.nonExistingValues) {
                valueInput.push(this.nonExistingValues[key])
            }
            this.children.$valueInput.val(JSON.stringify(valueInput))
            if (setSelectValue) this.children.$select.val(selectValues)
        },
        selectOnChange: function(elem, e) {
            var values = $(elem).val()
            this.evenOptionTexts(values.length, this.settings.existingClass, this.settings.existingOptionHtml)
            if (values.length > 0) {
                this.fillExistingOptionTextsAndBindEvents(values)
            }
            this.setInputValue(true)
        },
        fillExistingOptionTextsAndBindEvents: function(values) {
            var i = 0
            var plugin = this
            $(this.settings.optionText, $('.' + this.settings.existingClass, this.children.$optionTextsWrapper)).each(function() {
                if (plugin.existingOptionTextFill($(this), values[i])) {
                    $(plugin.settings.optionTextDeleteButton, $(this)).each(function() {
                        $(this).unbind('click.' + plugin.settings.namespace)
                            .bind('click.' + plugin.settings.namespace, $.proxy(plugin.deleteExistingOptionText, plugin, this))
                    })
                }
                i++
            })
        },
        existingOptionTextFill: function($text, value) {
            var isNew = $text.data('id') !== value
            if (isNew) {
                var $option = $('option[value="' + value + '"]', this.children.$select)
                var name = $.proxy(this.getName, this, $option, value)()
                $.proxy(this.setData, this, $text, value, name)()
                var label = this.prepareLabel(value, name)
                $(this.settings.optionTextLabel, $text).html(label)
            }

            return isNew
        },
        prepareLabel: function(value, name) {
            var label = $()
            label = label.add($('<span class="' + this.settings.optionTextNameClass + '"></span>').text(name.name))
            if (value !== this.settings.rootId) {
                label = label.add(('<span class="' + this.settings.optionTextIdClass + '">[' + value + ']</span>'))
            }
            if (name.disabled) {
                label = label.add($('<span class="' + this.settings.optionTextDisabledClass + '">' + this.settings.optionTextDisabledText + '</span>'))
            }
            return label
        },
        setData: function($text, value, name) {
            $text.data('id', value)
            if ('inputName' in name) {
                $text.data('name', name)
            }
        },
        getName: function($option, value) {
            if (value === this.settings.rootId) {
                return {
                    name: this.settings.rootName,
                    disabled: false
                }
            }

            var name = this.getOptionName($option)

            return {
                name: name,
                inputName: name,
                disabled: $option.attr('data-disabled') === 'true'
            }
        },
        getOptionName: function($option) {
            var name = $option.attr('data-name')
            if (typeof name === typeof undefined || name === false) {
                name = $option.text()
            }
            return name
        },
        deleteExistingOptionText: function(button, e) {
            e.preventDefault()
            if (this.$elem.hasClass(this.settings.loadingClass)) {
                return
            }
            var $typeWrapper = $(button).closest('.' + this.settings.existingClass)
            var $optionTexts = $(this.settings.optionText, $typeWrapper)
            if ($optionTexts.length === 1) {
                this.evenOptionTexts(0, this.settings.existingClass)
            } else {
                var $text = $(button).closest(this.settings.optionText)
                $text.remove()
            }
            this.setInputValue(true)
        },
        serverSelect: function() {
            if (!this.$elem.hasClass(this.settings.loadingClass)) {
                this.sendAjax(false);
            }
        },
        sendAjax: function(updateButton) {
            var plugin = this
            var ajaxSettings = {
                url: this.settings.url,
                type: this.settings.method,
                dataType: 'html',
                timeout: this.settings.timeout,
                beforeSend: function(jqXHR, settings) {
                    plugin.ajaxBeforeSend(jqXHR, settings)
                }
            }
            if ('ajaxGetData' in this.settings) {
                var data = typeof this.settings.ajaxGetData === 'function' ? $.proxy(this.settings.ajaxGetData, this, updateButton)() : this.settings.ajaxGetData
                if (data && typeof data !== 'string') {
                    data = $.param(data, false)
                }
                ajaxSettings.url = ajaxSettings.url.replace(/#.*$/, '')
                ajaxSettings.url += (/\?/.test(ajaxSettings.url) ? '&' : '?') + data
            }
            var hasContent = !/^(?:GET|HEAD)$/.test(ajaxSettings.type.toUpperCase());
            if (hasContent && ('ajaxPostData' in this.settings)) {
                ajaxSettings['data'] = typeof this.settings.ajaxPostData === 'function' ? $.proxy(this.settings.ajaxPostData, this, updateButton)() : this.settings.ajaxPostData
                //console.log(ajaxSettings['data'])
            }
            this.xhr = $.ajax(ajaxSettings)
                .done(function(data, textStatus, jqXHR) {
                    plugin.ajaxDone(data, textStatus, jqXHR)
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    plugin.ajaxFail(jqXHR, textStatus, errorThrown)
                })
                .always(function(dataOrJqXHR, textStatus, jqXHRorErrorThrown) {
                    plugin.ajaxAlways(dataOrJqXHR, textStatus, jqXHRorErrorThrown)
                })
        },
        ajaxBeforeSend: function(jqXHR, settings) {
            if ('type' in settings) this.ajax.method = settings['type']
            if ('url' in settings) this.ajax.url = settings['url']
            this.children.$select.attr('disabled', true)
            this.children.$serverSelect.attr('disabled', true)
            this.$elem.addClass(this.settings.loadingClass)
        },
        ajaxDone: function (data, textStatus, jqXHR) {
            if (!('status' in jqXHR) || parseInt(jqXHR['status']) !== 200) {
                this.statusNot200(false, jqXHR, data, textStatus)
            } else {
                this.noAjaxError()
                this.children.$widget.replaceWith(data);
                this.bindWidget()
                if (this.children.$error.is(':visible')) {
                    this.addErrorTitle()
                    this.blinkError()
                }
            }
            //console.log('done', arguments)
        },
        ajaxFail: function (jqXHR, textStatus, errorThrown) {
            this.statusNot200(true, jqXHR, null, textStatus, errorThrown)
            //console.log('fail', arguments)
        },
        statusNot200: function(isError, jqXHR, data, textStatus, errorThrown) {
            this.children.$select.attr('disabled', false)
            this.children.$serverSelect.attr('disabled', false)
            this.ajaxError(jqXHR);
        },
        ajaxAlways: function(dataOrJqXHR, textStatus, jqXHRorErrorThrown) {
            this.$elem.removeClass(this.settings.loadingClass)
            this.children.$serverSelect.attr('disabled', false)
        },
        noAjaxError: function() {
            if (this.children.$error.is(':visible')) {
                this.children.$error.hide()
            }
        },
        ajaxError: function(jqXHR) {
            var message = '';
            if ('status' in jqXHR) {
                message += 'HTTP-стаус ' + jqXHR['status']
            }
            if ('statusText' in jqXHR) {
                if ('status' in jqXHR) message += ': '
                message += jqXHR['statusText'];
            }
            if ('responseText' in jqXHR) {
                var responseJson = null
                try {
                    responseJson = JSON.parse(jqXHR['responseText'])
                } catch (e) {
                    responseJson = null
                }
                if ((responseJson !== null) && ('error' in responseJson)) {
                    if (message !== '') message += ', ' + "\n"
                    if ($.isArray(responseJson['error'])) {
                        var first = true
                        for (var key in responseJson['error']) {
                            if (!first) message += ', '
                            else first = false
                            message += responseJson['error'][key]
                        }
                    } else {
                        message += responseJson['error']
                    }
                }
            }
            if (message === '') {
                message = 'Неизвестная ошибка'
            }
            this.addErrorTitle()
            this.children.$error.text(message).show()
            this.blinkError()
        },
        addErrorTitle: function() {
            if (this.ajax.method !== null || this.ajax.url !== null) {
                var title = 'Запрос '
                if (this.ajax.method !== null) {
                    title += this.ajax.method
                    if (this.ajax.url !== null) title += ' '
                }
                if (this.ajax.url !== null) {
                    title += this.ajax.url
                }
                this.children.$error.attr('title', title)
            } else {
                this.children.$error.removeAttr('title')
            }
        },
        blinkError: function() {
            this.children.$error.fadeOut(210).fadeIn(390)
        },
        deleteNonExistingOptionText: function(button, e) {
            e.preventDefault()
            if (this.$elem.hasClass(this.settings.loadingClass)) {
                return
            }
            var $text = $(button).closest(this.settings.optionText)
            var id = $text.data('id')
            this.nonExistingValues[id]['delete'] = true
            $(button).hide()
            $(this.settings.optionTextRestoreButton, $text).show()
            this.setInputValue()
        },
        restoreNonExistingOptionText: function(button, e) {
            e.preventDefault()
            if (this.$elem.hasClass(this.settings.loadingClass)) {
                return
            }
            var $text = $(button).closest(this.settings.optionText)
            var id = $text.data('id')
            if ('delete' in this.nonExistingValues[id]) {
                delete this.nonExistingValues[id]['delete']
            }
            $(button).hide()
            $(this.settings.optionTextDeleteButton, $text).show()
            this.setInputValue()
        },
        fillNonExistingOptionTextsAndBindEvents: function(values, indices) {
            var i = 0
            var plugin = this
            $(this.settings.optionText, $('.' + this.settings.nonExistingClass, this.children.$optionTextsWrapper)).each(function() {
                var value = values[indices[i]]
                plugin.nonExistingOptionTextFill($(this), value)
                var deleting = 'delete' in value
                $(plugin.settings.optionTextDeleteButton, $(this)).toggle(!deleting).each(function() {
                    $(this).bind('click.' + plugin.settings.namespace, $.proxy(plugin.deleteNonExistingOptionText, plugin, this))
                })
                $(plugin.settings.optionTextRestoreButton, $(this)).toggle(deleting).each(function() {
                    $(this).bind('click.' + plugin.settings.namespace, $.proxy(plugin.restoreNonExistingOptionText, plugin, this))
                })
                i++
            })
        },
        nonExistingOptionTextFill: function($text, value) {
            $text.data('id', value['id'])
            var $option = $('option[value="' + value['id'] + '"]', this.children.$select)
            var name = $.proxy(this.getName, this, $option, value['id'])()
            var label = this.prepareLabel(value, name)
            $(this.settings.optionTextLabel, $text).html(label)
        },
        processInputValue: function() {
            this.nonExistingValues = {}
            var nonExistingIndices = []

            var nonExistingLength = 0
            var selectValues = []
            if (this.children.$select.length > 0) {
                for (var key in this.inputValue) {
                    var value = this.inputValue[key]
                    if (value['id'] === this.settings.rootId) {
                        selectValues.push(value['id'])
                    } else {
                        var $option = $('option[data-name="' + value['name'] + '"]', this.children.$select)
                        if (value['name'] === this.getOptionName($option)) {
                            selectValues.push($option.val())
                        } else {
                            this.nonExistingValues[value['id']] = value
                            nonExistingIndices.push(value['id'])
                            nonExistingLength++
                        }
                    }
                }
            } else {
                for (var key in this.inputValue) {
                    var value = this.inputValue[key]
                    this.nonExistingValues[value['id']] = value
                    nonExistingIndices.push(value['id'])
                    nonExistingLength++
                }
            }

            this.createTypeWrapper(this.settings.existingClass, this.settings.existingTitle)
            this.evenOptionTexts(0, this.settings.existingClass)
            if (selectValues.length > 0) {
                this.evenOptionTexts(selectValues.length, this.settings.existingClass, this.settings.existingOptionHtml)
                this.fillExistingOptionTextsAndBindEvents(selectValues)
            }
            this.setInputValue(this.children.$select.length > 0)
            if (haveNonExisting > 0) {
                this.createTypeWrapper(this.settings.nonExistingClass, this.settings.nonExistingTitle)
                this.evenOptionTexts(nonExistingLength, this.settings.nonExistingClass, this.settings.nonExistingOptionHtml)
                this.fillNonExistingOptionTextsAndBindEvents(this.nonExistingValues, nonExistingIndices)
            }
        },
        createTypeWrapper: function(optionTextsTypeWrapperClass, title) {
            $typeWrapper = $('<div class="' + this.settings.optionTextsTypeWrapperGeneralClass + ' ' + optionTextsTypeWrapperClass + '">' + title + '</div>')
            this.children.$optionTextsWrapper.append($typeWrapper)
        },
        evenOptionTexts: function(valuesLength, optionTextsTypeWrapperClass, optionTextHtml) {
            var $wrapper = $(this.settings.optionTextsWrapper, this.$elem)
            var $typeWrapper = $('.' + optionTextsTypeWrapperClass, this.children.$optionTextsWrapper)

            if (valuesLength === 0) {
                if ($typeWrapper.is(':visible')) {
                    $typeWrapper.hide()
                    var $optionTexts = $(this.settings.optionText, $typeWrapper)
                    $optionTexts.remove()
                }
            } else {
                if ($typeWrapper.is(':hidden')) {
                    $typeWrapper.show()
                }
                var $optionTexts = $(this.settings.optionText, $typeWrapper)
                if (valuesLength > $optionTexts.length) {
                    var $newOptionTexts = $();
                    for (var i = valuesLength; i > $optionTexts.length; i--) {
                        var $newOptionText = $(optionTextHtml)
                        $newOptionTexts = $newOptionTexts.add($newOptionText)
                    }
                    $typeWrapper.append($newOptionTexts)
                } else if (valuesLength < $optionTexts.length) {
                    $optionTexts = $optionTexts.filter(':gt(' + (valuesLength - 1) + ')')
                    $optionTexts.remove()
                }
            }
        }
    }

    // https://stackoverflow.com/a/28396546
    $.fn[pluginName] = function(options) {
        var args = $.makeArray(arguments),
            after = args.slice(1);

        return this.each(function () {
            var instance = $.data(this, pluginName);

            if (instance) {
                if (instance[options]) {
                    instance[options].apply(instance, after);
                } else {
                    //$.error('Method ' + options + ' does not exist on Plugin');
                }
            } else {
                var plugin = new Plugin(this, options);

                $.data(this, pluginName, plugin);
                return plugin;
            }
        });
    }
    $.fn[pluginName].prototype.defaults = defaults
    $.fn[pluginName].prototype.methods = Plugin.prototype
})(jQuery, window, document);
