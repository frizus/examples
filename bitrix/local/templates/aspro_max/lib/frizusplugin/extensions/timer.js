(function ($, window, document) {
    window.frizusPluginExtend({
        startTimer: function (group, name, handle, isInterval, timeout) {
            var plugin = this

            if (!(group in this.timers)) {
                this.timers[group] = {}
            } else if ((group in this.activeTimers) && (name in this.activeTimers[group])) {
                this.stopTimer(group, name)
            }

            this.timers[group][name] = {
                handle: handle, isInterval: isInterval ? true : false
            }
            this.createTimer(group, name, timeout || 1000)
        }, createTimer: function (group, name, timeout, firstTimeout) {
            var plugin = this

            if (!(group in this.activeTimers)) {
                this.activeTimers[group] = {}
            } else if (name in this.activeTimers[group]) {
                this.pauseTimer(group, name)
            }

            this.activeTimers[group][name] = {
                id: null
            }
            var activeTimer = this.activeTimers[group][name]
            var timer = this.timers[group][name]

            if (timer.isInterval) {
                timer.timeout = timeout
                if (arguments.length > 3) {
                    timer.firstTimeout = firstTimeout
                    timer.isFirstTimeout = true
                    activeTimer.id = window.setTimeout(function () {
                        timer.handle.apply(plugin)
                        timer.isFirstTimeout = false
                        timer.startedAt = new Date()
                        activeTimer.id = window.setInterval(function () {
                            timer.handle.apply(plugin)
                        }, timeout)
                    }, firstTimeout)
                } else {
                    activeTimer.id = window.setInterval(function () {
                        timer.handle.apply(plugin)
                    }, timeout)
                }
            } else {
                timer.timeout = firstTimeout || timeout
                activeTimer.id = window.setTimeout(function () {
                    timer.handle.apply(plugin)
                    plugin.stopTimer(group, name)
                }, firstTimeout || timeout)
            }
        }, stopTimer: function (group, name) {
            if (!(group in this.timers) || !(name in this.timers[group])) {
                return
            }

            this.pauseTimer(group, name)

            delete this.timers[group][name]
            if (Object.keys(this.timers[group]).length === 0) {
                delete this.timers[group]
            }
        }, resumeTimers: function (group) {
            if (!(group in this.activeTimers)) {
                return
            }

            var plugin = this
            $.each(this.activeTimers[group], function (name, timer) {
                plugin.resumeTimer(group, name)
            })
        }, resumeTimer: function (group, name) {
            if ((group in this.activeTimers) && (name in this.activeTimers[group])) {
                return
            }

            if (!(group in this.timers) || !(name in this.timers[group])) {
                return
            }

            var timer = this.timers[group][name]
            var now = (new Date()).getTime()
            var firstTimeout

            if (timer.isInterval) {
                if (timer.isFirstTimeout) {
                    firstTimeout = timer.firstTimeout - (now % timer.firstTimeout)
                } else {
                    firstTimeout = timer.timeout - (now % timer.timeout)
                }
            } else {
                firstTimeout = timer.timeout - (now % timer.timeout)
            }
            this.createTimer(group, name, timer, firstTimeout)
        }, pauseTimers: function (group) {
            if (!(group in this.activeTimers)) {
                return
            }

            var plugin = this
            $.each(this.activeTimers[group], function (name, timer) {
                plugin.pauseTimer(group, name)
            })
        }, pauseTimer: function (group, name) {
            if (!(group in this.activeTimers) || !(name in this.activeTimers[group])) {
                return
            }

            if (!(group in this.timers) || !(name in this.timers[group])) {
                return
            }

            var activeTimer = this.activeTimers[group][name]
            var timer = this.timers[group][name]

            if (timer.isInterval) {
                if (timer.isFirstTimeout) {
                    window.clearTimeout(activeTimer.id)
                } else {
                    window.clearInterval(activeTimer.id)
                }
            } else {
                window.clearTimeout(activeTimer.id)
            }

            delete this.activeTimers[group][name]
            if (Object.keys(this.activeTimers[group]).length === 0) {
                delete this.activeTimers[group]
            }
        }
    })
})(jQuery, window, document);