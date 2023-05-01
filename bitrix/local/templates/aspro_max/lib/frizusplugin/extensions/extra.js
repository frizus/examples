(function ($, window, document) {
    window.frizusPluginExtend({
        addPhoneMask: function ($phone) {
            $phone.imaskRussianPhone()
        }, toAnotherForm: function (windowName, $link, options, init) {
            var plugin = this
            options = options || {}
            options.noReset = true
            options.activateInput = true
            $link.bind('click.' + this.settings.namespace, function (e) {
                e.preventDefault()
                if (typeof init === 'function') {
                    init.apply(plugin)
                }
                plugin.showForm(windowName, options)
            })
        }, submitAction: function (action, $form, windowName, options) {
            var plugin = this
            options = options || {}
            options.$form = $form
            options.windowName = windowName
            $form.bind('submit.' + this.settings.namespace, function (e) {
                e.preventDefault()
                plugin.sendFormToAjax(action, $(this), options)
            })
        }, resetValidation: function ($forms) {
            $forms.each(function () {
                var $form = $(this)
                $form.parsley().reset()
            })
        }, addValidation: function ($forms, options) {
            var defaultOptions = {
                errorsWrapper: '<div class="form-errors"></div>',
                errorTemplate: '<div class="form-error"></div>',
                errorClass: 'error',
                successClass: '',
                enableSubmitWhenValid: true,
                haveBackendErrors: true
            }

            if (options) {
                $.extend(true, options, defaultOptions)
            } else {
                options = defaultOptions
            }

            $forms.parsley(options)
        }
    })
})(jQuery, window, document);