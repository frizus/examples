(function($) {
    function add_popup_html(container) {
        if ($('> .overlay-popup', container).length === 0) {
            container.append('<div class="overlay-popup"></div><div class="popup"></div>');
            container.find('> .overlay-popup').click(closeIframe);
        }
    }

    function changeTop(top, popupHeight, windowHeight) {
        var changed = false;
        var adminBar = $('#wpadminbar');
        if (adminBar.length > 0) {
            top += adminBar.outerHeight();
            changed = true;
        }

        var footerBar = $('.storefront-handheld-footer-bar');
        if (footerBar.is(':visible')) {
            top -= footerBar.outerHeight();
            changed = true;
        }

        if (changed) {
            if (top < 0) {
                top = 0;
            }
            if ((top + popupHeight) > windowHeight) {
                top = windowHeight - popupHeight;
            }
        }

        return top;
    }

    function windowResize() {
        var body = $('body');

        var doc = document.documentElement;
        var windowLeft = (window.pageXOffset || doc.scrollLeft) - (doc.clientLeft || 0);
        var windowTop = (window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0);
        var windowViewWidth = doc.clientWidth || body.get(0).clientWidth;
        var windowViewHeight = doc.clientHeight || body.get(0).clientHeight;
        //var windowWidth = doc.scrollWidth || body.get(0).scrollWidth;
        var windowHeight = doc.scrollHeight || body.get(0).scrollHeight;

        var popup = $('> .popup', body);
        var popupWidth = popup.outerWidth();
        var popupHeight = popup.outerHeight();
        var popupPosition = popup.position();

        windowTop = changeTop(windowTop, popupHeight, windowHeight);

        //console.log(windowViewWidth,windowViewHeight,windowWidth,windowHeight);

        if (windowViewWidth > popupWidth) {
            var newLeft = windowLeft + Math.floor((windowViewWidth - popupWidth) / 2);
            if (popupPosition.left !== newLeft) {
                popup.css('left', newLeft + 'px');
            }
        } else {
            var newLeft = windowLeft;
            if (popupPosition.left !== newLeft) {
                popup.css('left', newLeft + 'px');
            }
        }

        if (windowViewHeight > popupHeight) {
            var newTop = windowTop + Math.floor((windowViewHeight - popupHeight) / 2);
            if (popupPosition.top !== newTop) {
                popup.css('top', newTop + 'px');
            }
        } else {
            var newTop = windowTop;
            if (popupPosition.top !== newTop) {
                popup.css('top', newTop + 'px');
            }
        }
    }

    function closeIframe(e) {
        e.preventDefault();
        e.stopPropagation();
        $('body > .overlay-popup').fadeOut(300, function() {
            $('body > .popup > iframe').remove();
        });
        $('body > .popup').hide();
        $(window).unbind('resize.popup');
    }

    function addParameter(url, name, value) {
        var hashPos = url.indexOf('#');
        if (hashPos !== -1) {
            var hash = url.substring(hashPos);
            url = url.substring(0, hashPos);
        }
        var queryMark = url.indexOf('?');
        if (queryMark !== -1) {
            url = url + '&';
        } else {
            url = url + '?';
        }
        url = url + name + '=' + value;
        if (hashPos !== -1) {
            url = url + hash;
        }
        return url;
    }

    $(document).ready(function($){
        $('.oformit a, a.oformit').click(function(e) {
            e.preventDefault();
            var body = $('body');
            add_popup_html(body);
            $(window).bind('resize.popup', windowResize).trigger('resize.popup');
            var popup = body.find('> .popup');
            var iframeSrc = $(this).attr('href');
            iframeSrc = addParameter(iframeSrc, 'iframe', '');

            popup.find('> iframe').remove();
            popup.append('<iframe></iframe>');
            var iframe = $('> iframe', popup);
            iframe
                .attr('src', iframeSrc)
                .load(function() {
                    var contents = $(this).contents();
                    var iframeBody = contents.find('body');
                    iframeBody.append('<a href="javascript:void(0)" class="close" title="Закрыть"></a>');
                    iframeBody.find('.close, .close-iframe').click(closeIframe);
                });
            $('> .overlay-popup, > .popup', body).show();
        })
    });
})(jQuery);