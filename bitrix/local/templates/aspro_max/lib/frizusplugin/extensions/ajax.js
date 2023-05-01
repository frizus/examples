(function ($, window, document) {
    window.frizusPluginExtend({
        freeXhr: function () {
            if (('xhr' in this) && this.xhr !== null) {
                this.xhr.abort()
                this.xhr = null
            }
        }, loadAjax: function (method, url, query, post, expectJson) {
            var plugin = this
            var ajaxSettings = {
                url: url, type: method || 'GET', dataType: 'html', timeout: this.settings.timeout || 30 * 1000
            }

            expectJson = typeof expectJson === typeof undefined ? true : !!expectJson

            if (query) {
                if (query && (typeof query !== 'string')) {
                    query = $.param(query, false)
                }
                ajaxSettings.url = ajaxSettings.url.replace(/#.*$/, '')
                ajaxSettings.url += (/\?/.test(ajaxSettings.url) ? '&' : '?') + query
            }

            var hasContent = !/^(?:GET|HEAD)$/.test(ajaxSettings.type.toUpperCase())

            if (hasContent) {
                if (post && (typeof post === 'object') && ('constructor' in post) && ('name' in post.constructor) && (post.constructor.name === 'FormData')) {
                    $.extend(ajaxSettings, {
                        processData: false, contentType: false
                    })
                }
            }

            if (hasContent && post) {
                ajaxSettings['data'] = post
            }

            var deferred = $.Deferred()

            this.xhr = $.ajax(ajaxSettings)
                .done($.proxy(plugin.ajaxDone, plugin, deferred, expectJson))
                .fail($.proxy(plugin.ajaxFail, plugin, deferred))
                .always($.proxy(plugin.ajaxAlways, plugin))

            return deferred
        }, ajaxDone: function (deferred, expectJson, data, textStatus, jqXHR) {
            if (!('status' in jqXHR) || ((parseInt(jqXHR['status']) !== 200) && (parseInt(jqXHR['status']) !== 304))) {
                deferred.reject('load', null, jqXHR, textStatus)
            } else if (data === '') {
                deferred.reject('empty', null, jqXHR, textStatus)
            } else {
                if (!expectJson) {
                    deferred.resolve(data, textStatus, jqXHR)
                } else {
                    var dataJson = null
                    try {
                        dataJson = JSON.parse(data)
                    } catch (e) {
                        dataJson = null
                    }

                    if ((dataJson === null) || (typeof dataJson !== 'object')) {
                        deferred.reject('not_json', null, jqXHR, textStatus)
                    } else if (('status' in dataJson) && (dataJson['status'] !== 'success')) {
                        deferred.reject('json', dataJson, jqXHR, textStatus)
                    } else {
                        deferred.resolve(dataJson, textStatus, jqXHR)
                    }
                }
            }
        }, ajaxFail: function (deferred, jqXHR, textStatus, errorThrown) {
            if (textStatus === 'abort') {
                deferred.reject('cancel', null, jqXHR, textStatus, errorThrown)
            } else {
                deferred.reject('load', null, jqXHR, textStatus, errorThrown)
            }
        }, ajaxAlways: function (dataOrJqXHR, textStatus, jqXHRorErrorThrown) {
            this.xhr = null
        },
    })
})(jQuery, window, document);