(function ($, window, document) {
    window.frizusPluginExtend({
        signFormPrepare: function (windowName, options, first) {
            var reset = first || !(options && options.noReset)
            var $form = this.loadedWindows[windowName]

            if (first) {
                this.signFormFirstPrepare(windowName, $form)
                if (reset) {
                    this.resetValues($form)
                }
            } else if (reset) {
                this.resetValues($form)
                this.resetValidation($form)
            }

            if (options && options.activateInput) {
                this.activateInput($form)
            }
        }, signFormFirstPrepare: function (windowName, $form) {
            this.addValidation($form)
            this.submitAction('signRequestCode', $form)
        }, signRequestCodeSuccess: function (data, $form, options) {
            this.info.phoneFormatted = data['phoneFormatted']
            this.info.phone = data['phone']

            var newWindowName, action
            if (data['is_pre_sign_up']) {
                newWindowName = 'signup-form'
                action = 'signUpRequestResendCode'
                this.info.isSignUp = true
            } else {
                newWindowName = 'signin-form'
                action = 'signInRequestResendCode'
                this.info.isSignUp = false
            }

            this.registerCodeCooldown(newWindowName, action, data['cooldown'], data['is_cooldown'], data['time_left'])

            if (data['is_pre_sign_up']) {
                this.showForm(newWindowName, {activateInput: true})
            } else {
                this.showForm(newWindowName, {activateInput: true})
            }
        }, signRequestCodeFail: function (failType, $form, data, options, jqXHR, textStatus, errorThrown) {
            this.resolveFormError(failType, $form, data, jqXHR['status'])
        }
    })
})(jQuery, window, document);