(function ($, window, document) {
    $.fn.imaskRussianPhone = function () {
        var $this = this
        $this.each(function () {
            var imask = IMask(this, {
                mask: '+{7} (000) 000-00-00', lazy: false
            })
            $this.bind('reset', function () {
                imask._value = ''
                imask._unmaskedValue = ''
                imask.updateValue()
                imask._onChange()
            })
        })
    }
})(jQuery, window, document);