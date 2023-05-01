(function ($, window, document) {
    window.ParsleyConfig = $.extend(true, window.ParsleyConfig, {
        submits: 'input[type=submit], button[type=submit]',
        enableSubmitWhenValid: false,
        showErrorsImmediatelyIfNotRequired: true,
        excluded: 'input[type=button], input[type=submit], input[type=reset], input[type=hidden]:not([data-parsley-hidden-input])',
        formTopError: true,
        hideFormBackendErrorsOnInput: false,
        hideFieldBackendErrorsOnInput: true,
        backendErrorName: 'backend',
        haveBackendErrors: false
    });

    var oldTypeValidator = $.extend(true, {}, window.Parsley._validatorRegistry.validators.type)
    var emailTypeTester = /^[a-z0-9!#$%&'*+\/=?^_`{|}~.-]+@[a-z0-9-]+(\.[a-z0-9-]+)+$/i

    window.Parsley.addValidator('type', {
        validateString: function validateString(value, type) {
            if (type !== 'email') {
                return oldTypeValidator.validateString.apply(this, arguments)
            }

            if (!value) return true;

            if (!emailTypeTester.test(value)) return false;

            return true;
        },
        messages: oldTypeValidator.messages,
        requirementType: oldTypeValidator.requirementType,
        priority: oldTypeValidator.priority
    });

    window.ParsleyExtend = window.ParsleyExtend || {};

    window.ParsleyExtend = Object.assign(window.ParsleyExtend, {
        _actualizeTriggers: function _actualizeTriggers() {
            if (((this.__class__ === 'Field') || (this.__class__ === 'FieldMultiple')) && (this.parent.options.enableSubmitWhenValid)) {
                var _this2 = this
                var $toBind = this._findRelated()

                var trigger

                $toBind.off('.Parsley')
                if (trigger = window.Parsley.Utils.namespaceEvents(this.options.trigger, 'Parsley') || (trigger = window.Parsley.Utils.namespaceEvents(this.options.triggerAfterFailure, 'Parsley'))) {
                    if (!this._failedOnce) {
                        var showErrorsImmediately = false
                        if (this.options.showErrorsImmediately === true) {
                            showErrorsImmediately = true
                        }
                        if (!showErrorsImmediately && this.parent.options.showErrorsImmediatelyIfNotRequired) {
                            if (('constraintsByName' in this) && !('required' in this.constraintsByName)) {
                                showErrorsImmediately = true
                            }
                        }
                        if (showErrorsImmediately) {
                            this._failedOnce = true
                        }
                    }

                    $toBind.on(trigger, function (event) {
                        var field = _this2
                        var form = _this2.parent
                        var isValidForm = false
                        var disableSubmits = false

                        if (field._failedOnce) {
                            field._validateIfNeeded()
                            if (form.isValid()) {
                                isValidForm = true
                            } else {
                                disableSubmits = true
                            }
                        } else if (form.isValid()) {
                            isValidForm = true
                        }

                        if (isValidForm) {
                            var oldFocus = form.options.focus
                            if (oldFocus !== 'none') {
                                form.options.focus = 'none'
                            }
                            form.validate()
                            if (oldFocus !== 'none') {
                                form.options.focus = oldFocus
                                delete oldFocus
                            }

                            form.activateFieldTriggers()
                        } else if (disableSubmits && !form.submitsWereDisabled) {
                            form.trigger('form:error')
                        }
                    })
                }
            } else {
                if (this.__class__ in window.Parsley.UI) {
                    if (typeof window.Parsley.UI[this.__class__]._actualizeTriggers === 'function') {
                        return $.proxy(window.Parsley.UI[this.__class__]._actualizeTriggers, this)()
                    }
                }
            }
        }, activateFieldTriggers: function activateFieldTriggers() {
            if (this.__class__ === 'Form') {
                $.each(this.fields, function (_, field) {
                    if (!field._failedOnce) {
                        field._failedOnce = true
                        field._actualizeTriggers()
                    }
                })
            }
        }, disableSubmit: function disableSubmit() {
            if (this.__class__ === 'Form') {
                this.disableSubmits = true
                this.$element.find(this.options.submits).filter(':not([disabled])').prop('disabled', true)
            }
        }, enableSubmit: function enableSubmit() {
            if (this.__class__ === 'Form') {
                if ('disableSubmits' in this) {
                    delete this.disableSubmits
                }
                if (this.isValid()) {
                    this.$element.find(this.options.submits).filter('[disabled]').prop('disabled', false)
                }
            }
        }, removeBackendErrors: function removeBackendErrors() {
            if (this.__class__ === 'Form') {
                if (this.options.haveBackendErrors) {
                    $.each(this.fields, function (cursor, field) {
                        if (field.haveBackendErrors) {
                            field.removeError(field.options.backendErrorName, {updateClass: true})
                            field.haveBackendErrors = false
                        }
                    })
                }
            }
        }, removeCommonBackendErrors: function removeCommonBackendErrors() {
            if (this.__class__ === 'Form') {
                if (this.options.haveBackendErrors && this.options.formTopError) {
                    if (this.formTopError.haveBackendErrors) {
                        this.formTopError.removeError(this.options.backendErrorName, {updateClass: true})
                        this.formTopError.haveBackendErrors = false
                    }
                }
            }
        }
    });

    function formReset() {
        if (this.isValid()) {
            $.proxy(formSuccess, this)()
            $.proxy(this.activateFieldTriggers, this)()
        } else {
            $.proxy(formError, this)()
        }
    }

    function formError() {
        if (!this.disableSubmits) {
            this.$element.find(this.options.submits).filter(':not([disabled])').prop('disabled', true)
            this.submitsWereDisabled = true
        }
    }

    function formSuccess() {
        if (!this.disableSubmits) {
            this.$element.find(this.options.submits).filter('[disabled]').prop('disabled', false)
            this.submitsWereDisabled = false
        }
    }

    $.listen('parsley:form:init', function (ParsleyForm) {
        var _this = ParsleyForm

        if (_this.options.enableSubmitWhenValid && (_this.fields.length > 0)) {
            _this
                .on('form:success', $.proxy(formSuccess, _this))
                .on('form:error', $.proxy(formError, _this))
                .on('form:reset', $.proxy(formReset, _this))

            $.proxy(formReset, _this)()
        }

        if (_this.options.formTopError) {
            if (_this.$element.find('> [data-parsley-top-error]').length === 0) {
                $topError = $('<input type="hidden" disabled data-parsley-hidden-input data-parsley-top-error data-parsley-error-class="" data-parsley-success-class="">')
                _this.$element.prepend($topError)
                _this.$element.parsley().refresh()
            }
            if (!('formTopError' in _this)) {
                $.each(_this.fields, function (cursor, field) {
                    if (field.$element.is('[data-parsley-top-error]')) {
                        _this.formTopError = _this.fields[cursor]
                        field.formTopError = null
                        return false
                    }
                })
            }
        }

        if (_this.options.haveBackendErrors) {
            if (_this.options.formTopError && _this.options.hideFormBackendErrorsOnInput) {
                if (!_this.options.enableSubmitWhenValid) {
                    $.each(_this.fields, function (_, field) {
                        if ('formTopError' in field) {
                            return true
                        }

                        field.on('field:validate', function () {
                            if (this.parent.formTopError.haveBackendErrors) {
                                this.parent.formTopError.removeError(this.parent.options.backendErrorName, {updateClass: true})
                                this.parent.formTopError.haveBackendErrors = false
                            }
                        })
                    })
                } else {
                    _this.on('form:validate', function () {
                        if (this.formTopError.haveBackendErrors) {
                            this.formTopError.removeError(this.options.backendErrorName, {updateClass: true})
                            this.formTopError.haveBackendErrors = false
                        }
                    })
                }
            }

            if (_this.options.hideFieldBackendErrorsOnInput) {
                $.each(_this.fields, function (_, field) {
                    if ('formTopError' in field) {
                        return true
                    }

                    field.on('field:validate', function () {
                        this.removeError(this.options.backendErrorName, {updateClass: true})
                    })
                })
            }

            _this.on('form:validated', function () {
                $.each(this.fields, function (cursor, field) {
                    if (('formTopError' in field) && _this.options.hideFormBackendErrorsOnInput) {
                        return true
                    }

                    if (field.haveBackendErrors) {
                        field.removeError(field.options.backendErrorName, {updateClass: true})
                        field.haveBackendErrors = false
                    }
                })
            })
        }
    })
})(jQuery, window, document);