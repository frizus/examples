(function ($, window, document) {
    window.frizusPluginExtend({
        registerCodeCooldown: function (name, action, cooldown, alreadyCooldown, timeLeft) {
            this.cooldowns[name] = {
                cooldown: parseInt(cooldown), action: action, started: false, finished: false, group: name
            }

            this._startCooldown(name, alreadyCooldown ? timeLeft : this.cooldowns[name].cooldown, alreadyCooldown)
        }, _startCooldown: function (name, cooldown, codeSendDenied) {
            var startedAt = new Date()
            var currentAt = new Date()
            var endingAt = new Date()
            cooldown = parseInt(cooldown)
            this.cooldowns[name].codeSendDenied = codeSendDenied

            if (cooldown > 0) {
                endingAt.setSeconds(endingAt.getSeconds() + cooldown)

                $.extend(true, this.cooldowns[name], {
                    started: true, startedAt: startedAt, currentAt: currentAt, endingAt: endingAt, finished: false,
                })
            } else {
                this.cooldowns[name].finished = true
            }
        }, initCodeCooldown: function (name, $form, first, reset) {
            if (!(name in this.cooldowns) || (!first && !reset)) {
                return
            }

            var plugin = this
            var cooldown = this.cooldowns[name]

            if (first) {
                $form.find('.resend-code-available .resend-code-link').bind('click.' + this.settings.namespace, function (e) {
                    e.preventDefault()
                    plugin.sendFormToAjax(cooldown.action, $form, {
                        name: name,
                        success: plugin.resendCodeSuccess,
                        fail: plugin.resendCodeFail,
                        removeCommonErrors: true,
                        noFormParams: true
                    })
                })
            }

            if (cooldown.finished) {
                this.codeCooldownFinish(name, $form)
            } else {
                this.codeCooldownStart(name, $form)
            }
        }, resendCodeSuccess: function (data, $form, options) {
            var name = options.name
            var cooldown = this.cooldowns[name]
            var $codeSendDenied = $form.find('.code-send-denied')
            var $codeSent = $form.find('.code-sent')

            this._startCooldown(name, data['is_cooldown'] ? data['time_left'] : cooldown.cooldown, data['is_cooldown'])
            if (cooldown.finished) {
                this.codeCooldownFinish(name, $form)
            } else {
                this.codeCooldownStart(name, $form)
            }
        }, resendCodeFail: function (failType, $form, data, options, jqXHR, textStatus, errorThrown) {
            this.resolveFormError(failType, $form, data, jqXHR['status'])
        }, codeCooldownStart: function (name, $form, noStartTimer) {
            var cooldown = this.cooldowns[name]
            var $resendCodeCooldown = $form.find('.resend-code-cooldown')
            var $timeLeft = $resendCodeCooldown.find('.time-left')
            var $resendCodeAvailable = $form.find('.resend-code-available')
            var $codeSent = $form.find('.code-sent')
            var $codeSendDenied = $form.find('.code-send-denied')

            $timeLeft.text('')
            $resendCodeAvailable.hide()
            $resendCodeCooldown.show()

            if (cooldown.codeSendDenied) {
                $codeSendDenied.show()
                $codeSent.hide()
            } else {
                $codeSendDenied.hide()
                $codeSent.show()
            }

            if (!noStartTimer) {
                this.startCodeCooldownTimer(name, $form)
            }
        }, codeCooldownFinish: function (name, $form) {
            if (!this.cooldowns[name].started) {
                this.codeCooldownStart(name, $form, true)
            }

            var $resendCodeCooldown = $form.find('.resend-code-cooldown')
            var $timeLeft = $resendCodeCooldown.find('.time-left')
            var $resendCodeAvailable = $form.find('.resend-code-available')

            $timeLeft.text('')
            $resendCodeCooldown.hide()
            $resendCodeAvailable.show()

            this.cooldowns[name].finished = true
        }, codeCooldownFormatTimeLeft: function (seconds) {
            seconds = parseInt(seconds, 10)
            var minutes = Math.floor(seconds / 60)
            seconds = seconds - (minutes * 60)

            if (minutes < 10) {
                minutes = '0' + minutes
            }

            if (seconds < 10) {
                seconds = '0' + seconds
            }

            return minutes + ':' + seconds
        }, codeCooldownTick: function (name, $form) {
            var cooldown = this.cooldowns[name]
            var $timeLeft = $form.find('.resend-code-cooldown .time-left')
            var diff = Math.floor((cooldown.endingAt - cooldown.currentAt) / 1000)
            $timeLeft.text(this.codeCooldownFormatTimeLeft(diff))
        }, startCodeCooldownTimer: function (name, $form) {
            var plugin = this

            this.startTimer(this.cooldowns[name].group, name, function () {
                plugin.cooldowns[name].currentAt.setSeconds(plugin.cooldowns[name].currentAt.getSeconds() + 1)

                if (plugin.cooldowns[name].currentAt >= plugin.cooldowns[name].endingAt) {
                    plugin.stopTimer(plugin.cooldowns[name].group, name)
                    plugin.codeCooldownFinish(name, $form)
                    plugin.cooldowns[name].started = false
                } else {
                    plugin.codeCooldownTick(name, $form)
                }
            }, true, 1000)

            this.codeCooldownTick(name, $form)
        },
    })
})(jQuery, window, document);