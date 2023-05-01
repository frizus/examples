(function ($, window, document) {
    window.frizusPluginExtend({
        showForm: function (windowName, options) {
            var plugin = this
            var first = false

            if (this.currentWindow && (this.currentWindow in this.loadedWindows)) {
                this.loadedWindows[this.currentWindow].hide()
                this.pauseTimers(this.currentWindow)
            }

            this.currentWindow = windowName
            if (!(this.currentWindow in this.loadedWindows)) {
                first = true
                this.loadedWindows[this.currentWindow] = this.$elem.find('.' + windowName)
            }

            var func

            if (options && (typeof options['prepare'] === 'function')) {
                func = options['prepare']
            } else {
                var prepareName = windowName.replace(/(-.)/g, function (x) {
                    return x[1].toUpperCase()
                }) + 'Prepare'
                prepareName = (plugin.settings.preparePrefix ? (plugin.settings.preparePrefix + prepareName.replace(/^(.)/g, function (x) {
                    return x[0].toUpperCase()
                })) : prepareName)
                if (typeof this[prepareName] === 'function') {
                    func = this[prepareName]
                }
            }

            if (func) {
                func.apply(this, [windowName, options, first])
            }

            this.resumeTimers(windowName)

            this.currentWindow = windowName
            this.loadedWindows[this.currentWindow].show()
        }
    })
})(jQuery, window, document);