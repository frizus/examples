(function ($, window, document) {
    window.frizusPluginExtend({
        addFormErrors: function ($form, errors, commonErrors) {
            var parsley = $form.parsley()
            var allErrors = this.normalizeErrors(parsley, errors, commonErrors)
            errors = allErrors[0]
            commonErrors = allErrors[1]

            parsley.removeBackendErrors()
            var haveTopErrors = this.addFormTopError(parsley, commonErrors)
            var haveErrors = this.addErrors(parsley, errors, allErrors[2])

            if (haveTopErrors || haveErrors) {
                parsley.activateFieldTriggers()
            }
        }, addFormTopError: function (parsley, commonErrors) {
            if (('formTopError' in parsley) && (commonErrors['common'].length > 0)) {
                var lastIndex = commonErrors['common'].length - 1
                var i = 0
                $.each(commonErrors['common'], function (_, error) {
                    parsley.formTopError.addError(parsley.options.backendErrorName, {
                        message: error ? error : 'Сервер не написал ошибку', assert: '', updateClass: i === lastIndex
                    })
                    parsley.formTopError.haveBackendErrors = true
                    i++
                })
                return true
            }
            return false
        }, addErrors: function (parsley, errors, fields) {
            var plugin = this

            if (Object.keys(errors).length > 0) {
                $.each(errors, function (fieldName, error) {
                    var lastIndex = error.length - 1
                    var i = 0
                    $.each(error, function (cursor, singleError) {
                        var field = fields[fieldName]
                        field.addError(field.parent.options.backendErrorName, {
                            message: singleError ? singleError : 'Сервер не написал ошибку',
                            assert: '',
                            updateClass: i === lastIndex
                        })
                        field.haveBackendErrors = true
                        i++
                    })
                })
                return true
            }
            return false
        }, resetValues: function ($forms) {
            var $inputs = $forms.find(':input:not([type=hidden]):not([type=submit]):not([type=reset])')
            $inputs.each(function () {
                var $input = $(this)
                if ($input.is('[type=checkbox], [type=radio]')) {
                    $input.prop('checked', false).trigger('reset')
                } else if ($input.is('[type=file]')) {
                    $input.val(null).trigger('reset')
                } else {
                    $input.val('').trigger('reset')
                }
            })
        }, addCsrf: function ($forms, csrf) {
            $forms.each(function () {
                var $form = $(this)
                var $sessid = $form.find('input[name="sessid"]')
                if ($sessid.length > 0) {
                    $sessid.val(csrf)
                } else {
                    var $sessid = $('<input type="hidden" name="sessid">')
                    $sessid.val(csrf)
                    $form.prepend($sessid)
                }
            })
        }, activateInput: function ($container) {
            var $form
            var $firstContainer = $container.first()
            if ($firstContainer.is('form')) {
                $form = $firstContainer
            } else {
                $form = $container.find('form').first()
                if ($form.length === 0) {
                    $form = $container
                }
            }
            var $input = $form.find(':input:not([type=hidden]):not([type=submit]):not([type=reset]):not([disabled]):not([readonly])').first()
            if ($input.length > 0) {
                window.setTimeout(function () {
                    $input.focus()
                }, 0)
            }
        }, disableFormSubmit: function ($container) {
            if (!$container) {
                if (this.currentWindow && (this.currentWindow in this.loadedWindows)) {
                    $container = this.loadedWindows[this.currentWindow]
                }
            }

            if ($container) {
                var $form = $container.is('form') ? $container : $container.find('form')
                $form.each(function () {
                    var $form = $(this)
                    if ($form.data('Parsley')) {
                        $form.parsley().disableSubmit()
                    }
                })
            }
        }, enableFormSubmit: function ($container) {
            if (!$container) {
                if (this.currentWindow && (this.currentWindow in this.loadedWindows)) {
                    $container = this.loadedWindows[this.currentWindow]
                }
            }
            if ($container) {
                var $form = $container.is('form') ? $container : $container.find('form')
                $form.each(function () {
                    var $form = $(this)
                    if ($form.data('Parsley')) {
                        $form.parsley().enableSubmit()
                    }
                })
            }
        },


        normalizeErrors: function (parsley, errors, commonErrors) {
            if (typeof commonErrors === 'string') {
                commonErrors = {common: [commonErrors]}
            } else if ((commonErrors !== null) && (typeof commonErrors === 'object')) {
                if ($.isArray(commonErrors)) {
                    commonErrors = {common: commonErrors}
                } else {
                    if (!('common' in commonErrors)) {
                        commonErrors['common'] = []
                    } else if (typeof commonErrors['common'] === 'string') {
                        commonErrors['common'] = [commonErrors['common']]
                    }
                }
            } else {
                commonErrors = {common: []}
            }

            if (typeof errors === 'string') {
                errors = [errors]
            }

            var formErrors = {}
            if ((errors !== null) && (typeof errors === 'object')) {
                if ($.isArray(errors)) {
                    $.each(errors, function (_, error) {
                        commonErrors['common'].push(error)
                    })
                } else if (Object.keys(errors).length > 0) {
                    var fields = {}
                    if (parsley) {
                        $.each(parsley.fields, function (_, field) {
                            if (field.__class__ === 'Field') {
                                if (field.$element.is('[name]')) {
                                    fields[field.$element.attr('name')] = field
                                }
                            } else if (field.__class__ === 'FieldMultiple') {
                                $.each(field.$elements, function (_, $element) {
                                    if ($element.is('[name]')) {
                                        fields[$element.attr('name')] = field
                                    }
                                })
                            }
                        })
                    }

                    var extraCommonErrors
                    if (!('common' in errors) && !('form' in errors)) {
                        formErrors = errors
                    }
                    if ('common' in errors) {
                        if (typeof errors['common'] === 'string') {
                            extraCommonErrors = [errors['common']]
                        } else {
                            extraCommonErrors = errors['common']
                        }

                        if ((extraCommonErrors !== null) && (typeof extraCommonErrors === 'object')) {
                            $.each(extraCommonErrors, function (_, error) {
                                commonErrors['common'].push(error)
                            })
                        }
                        delete errors['common']
                    }
                    if ('form' in errors) {
                        formErrors = errors['form']
                    }

                    if (formErrors !== null && (typeof formErrors === 'object')) {
                        $.each(formErrors, function (fieldName, error) {
                            if (typeof error === 'object') {
                                if (error === null) {
                                    delete formErrors[fieldName]
                                    return true
                                } else if ($.isArray(error)) {
                                    if (error.length === 0) {
                                        delete formErrors[fieldName]
                                        return true
                                    }
                                } else {
                                    if (Object.keys(error).length === 0) {
                                        delete formErrors[fieldName]
                                        return true
                                    }
                                }
                            }

                            if (typeof error === 'string') {
                                formErrors[fieldName] = [formErrors[fieldName]]
                            }

                            if (!(fieldName in fields)) {
                                $.each(formErrors[fieldName], function (_, singleError) {
                                    commonErrors['common'].push(singleError)
                                })
                                delete formErrors[fieldName]
                            }
                        })
                    } else {
                        formErrors = {}
                    }
                }
            }

            return [formErrors, commonErrors, fields]
        }, resolveFormError: function (failType, $form, data, status) {
            if (failType === 'load') {
                this.addLoadFormError($form, status)
            } else if (failType === 'empty') {
                this.addEmptyFormError($form)
            } else if (failType === 'not_json') {
                this.addNotJsonFormError($form)
            } else if (failType === 'json') {
                this.addJsonError($form, data)
                if ('csrf' in data) {
                    this.addCsrf($form, data['csrf'])
                }
            }
        }, addLoadFormError: function ($form, status) {
            var error = 'Попробуйте отправить форму снова'
            status = parseInt(status)
            if (!isNaN(status)) {
                error += ' (статус ответа ' + status + ')'
            }
            this.addFormErrors($form, null, {
                common: error,
            })
        }, addEmptyFormError: function ($form, status) {
            this.addFormErrors($form, null, {
                common: 'Некорректный ответ сервера',
            })
        }, addNotJsonFormError: function ($form, status) {
            this.addFormErrors($form, null, {
                common: 'Некорректный ответ сервера',
            })
        }, addJsonError($form, data) {
            if ('error' in data) {
                this.addFormErrors($form, data['error'])
            } else {
                this.addUnknownError($form)
            }
        }, addUnknownError: function ($form) {
            this.addFormErrors($form, null, {
                common: 'Сервер не написал ошибку. Попробуйте отправить форму снова',
            })
        },
    })
})(jQuery, window, document);