(function ($, window, document) {
    var pluginName = 'bitrixCategories',
        defaults = $.extend({}, $.fn.remoteSelect.prototype.defaults, {
            optionTextPartClass: 'option-label-part',
            optionTextSeparatorClass: 'option-label-separator',
            optionTextSeparatorText: '&ndash;',
            wrongPathClass: 'wrong-path',
            wrongPathTitle: '<div class="font-weight-bold">Поменялся путь раздела</div>',
            wrongPathOptionHtml: `<div class="selected-option">
                <span class="selected-option-label"></span>
                <a href="javascript:void(0)" class="restore-path-selected-option text-success">Переместить в <span class="correct-path-selected-option"></span></a>
                <a href="javascript:void(0)" title="Удалить" class="delete-selected-option text-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </a>
                <a href="javascript:void(0)" class="restore-selected-option text-success">Восстановить</a>
            </div>`,
            wrongPathCorrectPathClass: 'correct-path-selected-option',
            optionTextRestorePathButton: '.restore-path-selected-option',
            url: '/widget/categories'
        })

    function Plugin(element, options) {
        this.element = $(element);
        this.$elem = $(this.element);
        this._name = pluginName;
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;

        return this.init();
    }

    Plugin.prototype = $.extend({}, $.fn.remoteSelect.prototype.methods, {
        init: function() {
            this.wrongPathValues = null
            $.proxy($.fn.remoteSelect.prototype.methods.init, this)()
        },
        setInputValue: function(setSelectValue) {
            var valueInput = []
            if (setSelectValue) var selectValues = []
            $(this.settings.optionText, $('.' + this.settings.existingClass, this.children.$optionTextsWrapper)).each(function() {
                var id = $(this).data('id')
                var val = {
                    'id': id
                }
                var name = $(this).data('name')
                if (typeof name !== 'undefined') {
                    val['name'] = name
                }
                var path = $(this).data('path')
                if (typeof path !== 'undefined') {
                    val['path'] = path
                }
                valueInput.push(val)
                if (setSelectValue) selectValues.push(id)
            })
            for (var key in this.nonExistingValues) {
                valueInput.push(this.nonExistingValues[key])
            }
            for (var key2 in this.wrongPathValues) {
                valueInput.push(this.wrongPathValues[key2])
            }
            this.children.$valueInput.val(JSON.stringify(valueInput))
            if (setSelectValue) this.children.$select.val(selectValues)
        },
        prepareLabel: function(value, names) {
            var label = $()
            var first = true
            for (var key in names.name) {
                if (!first) {
                    label = label.add($('<span class="' + this.settings.optionTextSeparatorClass + '">' + this.settings.optionTextSeparatorText + '</span>'))
                }
                else {
                    first = false
                }
                var text = $('<span class="' + this.settings.optionTextPartClass + '"></span>')
                text.text(names.name[key])
                label = label.add(text)
            }
            if (value !== this.settings.rootId) {
                label = label.add(('<span class="' + this.settings.optionTextIdClass + '">[' + value + ']</span>'))
            }
            if (names.disabled) {
                label = label.add($('<span class="' + this.settings.optionTextDisabledClass + '">' + this.settings.optionTextDisabledText + '</span>'))
            }
            return label
        },
        setData: function($text, value, names) {
            $text.data('id', value)
            if ('inputPath' in names) {
                $text.data('path', names.inputPath)
            } else {
                $text.removeData('path')
            }
            if ('inputName' in names) {
                $text.data('name', names.inputName)
            } else {
                $text.removeData('name')
            }
        },
        getName: function($option, value) {
            if (value === this.settings.rootId) {
                return {
                    name: [this.settings.rootName],
                    disabled: false
                }
            }

            var name = this.getOptionName($option)
            var names = {
                name: [name],
                inputName: name,
                disabled: $option.attr('data-disabled') === 'true'
            }
            var parent = $option.attr('data-parent')
            while (typeof parent !== typeof undefined && parent !== false) {
                var $parentOption = $('option[value="' + parent + '"]', this.children.$select)
                parent = $parentOption.attr('data-parent')
                name = this.getOptionName($parentOption)
                if (!('inputPath' in names)) {
                    names.inputPath = [name]
                } else {
                    names.inputPath.unshift(name)
                }
                names.name.unshift(name)
            }
            return names
        },
        getOptionName: function($option) {
            return $option.attr('data-name')
        },
        deleteWrongPathOptionText: function(button, e) {
            e.preventDefault()
            if (this.$elem.hasClass(this.settings.loadingClass)) {
                return
            }
            var $text = $(button).closest(this.settings.optionText)
            var id = $text.data('id')
            this.wrongPathValues[id]['delete'] = true
            $(button).hide()
            $(this.settings.optionTextRestoreButton, $text).show()
            this.setInputValue()
        },
        restoreWrongPathOptionText: function(button, e) {
            e.preventDefault()
            if (this.$elem.hasClass(this.settings.loadingClass)) {
                return
            }
            var $text = $(button).closest(this.settings.optionText)
            var id = $text.data('id')
            if ('delete' in this.wrongPathValues[id]) {
                delete this.wrongPathValues[id]['delete']
            }
            $(button).hide()
            $(this.settings.optionTextDeleteButton, $text).show()
            this.setInputValue()
        },
        restorePathWrongPathOptionText: function(button, e) {
            e.preventDefault()
            if (this.$elem.hasClass(this.settings.loadingClass)) {
                return
            }

            var $typeWrapper = $(button).closest('.' + this.settings.wrongPathClass)
            var $optionTexts = $(this.settings.optionText, $typeWrapper)
            var $text = $(button).closest(this.settings.optionText)
            var id = $text.data('id')
            if ($optionTexts.length === 1) {
                this.evenOptionTexts(0, this.settings.wrongPathClass)
            } else {
                $text.remove()
            }
            delete this.wrongPathValues[id]
            var selectValues = this.children.$select.val()
            var haveSelectValue = false
            if (selectValues.length > 0) {
                for (var key in selectValues) {
                    var selectValue = selectValues[key]
                    if (selectValue === id) {
                        haveSelectValue = true
                        break
                    }
                }
            }
            if (!haveSelectValue) {
                selectValues.push(id)
                this.children.$select.val(selectValues)
                this.selectOnChange(this.children.$select[0])
            }
        },
        nonExistingOptionTextFill: function($text, value) {
            $text.data('id', value['id'])
            var $option = $('option[value="' + value['id'] + '"]', this.children.$select)
            var label = this.prepareNonExistingLabel(value)
            $(this.settings.optionTextLabel, $text).html(label)
        },
        prepareNonExistingLabel: function(value) {
            var name = {
                name: [],
                disabled: false
            }
            if (value['id'] === this.settings.rootId) {
                name['name'].push(this.settings.rootName)
            } else {
                if ('path' in value) {
                    name['name'] = $.extend([], value['path'])
                }
                name['name'].push(value['name'])
            }
            return this.prepareLabel(value['id'], name)
        },
        fillWrongPathOptionTextsAndBindEvents: function(values, indices) {
            var i = 0
            var plugin = this
            $(this.settings.optionText, $('.' + this.settings.wrongPathClass)).each(function() {
                var value = values[indices[i]]
                plugin.wrongPathOptionTextFill($(this), value)
                var deleting = 'delete' in value
                $(plugin.settings.optionTextRestorePathButton, $(this)).each(function() {
                    $(this).bind('click.' + plugin.settings.namespace, $.proxy(plugin.restorePathWrongPathOptionText, plugin, this))
                })
                $(plugin.settings.optionTextDeleteButton, $(this)).toggle(!deleting).each(function() {
                    $(this).bind('click.' + plugin.settings.namespace, $.proxy(plugin.deleteWrongPathOptionText, plugin, this))
                })
                $(plugin.settings.optionTextRestoreButton, $(this)).toggle(deleting).each(function() {
                    $(this).bind('click.' + plugin.settings.namespace, $.proxy(plugin.restoreWrongPathOptionText, plugin, this))
                })
                i++
            })
        },
        wrongPathOptionTextFill: function($text, value) {
            $text.data('id', value['id'])
            $(this.settings.optionTextLabel, $text).text(value['name'])

            var $option = $('option[value="' + value['id'] + '"]', this.children.$select)
            var names = $.proxy(this.getName, this, $option, value['id'])()

            var label = this.prepareWrongPathLabel(value, names)
            var correctPathLabel = this.prepareLabel(value['id'], names)
            $(this.settings.optionTextLabel, $text).html(label)
            $('.' + this.settings.wrongPathCorrectPathClass, $text).html(correctPathLabel)
        },
        prepareWrongPathLabel: function(value, name) {
            var wrongName = {
                name: [],
                disabled: name.disabled
            }
            if (value['id'] === this.settings.rootId) {
                wrongName['name'].push(this.settings.rootName)
            } else {
                if ('path' in value) {
                    wrongName['name'] = $.extend([], value['path'])
                }
                wrongName['name'].push(value['name'])
            }
            return this.prepareLabel(value['id'], wrongName)
        },
        getSelectChild: function(name, parent) {
            if (parent === null) {
                return $('option[data-name="' + name + '"]:not([data-parent])', this.children.$select)
            } else {
                return $('option[data-parent="' + parent + '"][data-name="' + name + '"]', this.children.$select)
            }
        },
        processInputValue: function() {
            this.nonExistingValues = {}
            this.wrongPathValues = {}
            var nonExistingIndices = []
            var nonExistingLength = 0
            var wrongPathIndices = []
            var wrongPathLength = 0
            var selectValues = []
            if (this.children.$select.length > 0) {
                for (var key in this.inputValue) {
                    var value = this.inputValue[key]
                    if (value['id'] === this.settings.rootId) {
                        selectValues.push(value['id'])
                    } else {
                        var parent = null
                        var pathNotFound = false
                        var selectChild = null
                        var part = null
                        if ('path' in value) {
                            for (var key2 in value['path']) {
                                part = value['path'][key2]
                                selectChild = this.getSelectChild(part, parent)
                                if (selectChild.length === 0) {
                                    pathNotFound = true
                                    break
                                }
                                parent = selectChild.val()
                            }
                        }
                        if (!pathNotFound) {
                            part = value['name']
                            selectChild = this.getSelectChild(part, parent)
                            if (selectChild.length === 0) {
                                pathNotFound = true
                            }
                        }

                        if (!pathNotFound) {
                            selectValues.push(selectChild.val())
                        } else {
                            var $option = $('option[value="' + value['id'] + '"]', this.children.$select)
                            if ($option.length > 0) {
                                this.wrongPathValues[value['id']] = value
                                wrongPathIndices.push(value['id'])
                                wrongPathLength++
                            } else {
                                this.nonExistingValues[value['id']] = value
                                nonExistingIndices.push(value['id'])
                                nonExistingLength++
                            }
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
            if (nonExistingLength > 0) {
                this.createTypeWrapper(this.settings.nonExistingClass, this.settings.nonExistingTitle)
                this.evenOptionTexts(nonExistingLength, this.settings.nonExistingClass, this.settings.nonExistingOptionHtml)
                this.fillNonExistingOptionTextsAndBindEvents(this.nonExistingValues, nonExistingIndices)
            }
            if (wrongPathLength > 0) {
                this.createTypeWrapper(this.settings.wrongPathClass, this.settings.wrongPathTitle)
                this.evenOptionTexts(wrongPathLength, this.settings.wrongPathClass, this.settings.wrongPathOptionHtml)
                this.fillWrongPathOptionTextsAndBindEvents(this.wrongPathValues, wrongPathIndices)
            }
        }
    })

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
})(jQuery, window, document)
