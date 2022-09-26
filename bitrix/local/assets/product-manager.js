(function ($, window, document) {
    var pluginName = 'bitrixProductManager',
        defaults = {

        }

    function Plugin(element, options) {
        this.element = $(element)
        this.$elem = this.element
        this._name = pluginName
        this.settings = $.extend({}, defaults, options)
        this._defaults = defaults

        return this.init()
    }

    Plugin.prototype = {
        options: function (option, val) {
            this.settings[option] = val
        },
        destroy: function () {
            $.removeData(this.element, this._name)
        },
        init: function() {
            this.lastChangedHandlerId = {}
            this.changedHandlers = {}
            this.state = {}
            this.stateInited = false
            return this
        },
        initState: function(data) {
            for (var key in data) {
                var actions = data[key]
                var product = parseInt(key)
                this.state[product] = {}
                for (var key2 in actions) {
                    var action = actions[key2]
                    this.state[product][action] = true
                    this.triggerChanged(product, action, true, true)
                }
            }
            this.stateInited = true
        },
        checkState: function(product, handlerId, initing) {
            if (!this.stateInited) {
                return
            }
            product = parseInt(product)
            if ((product in this.state) &&
                (product in this.changedHandlers) &&
                (handlerId in this.changedHandlers[product])
            ) {
                for (var action in this.state[product]) {
                    var added = this.state[product][action]
                    if (!initing || (initing && added)) {
                        this.changedHandlers[product][handlerId](action, added)
                    }
                }
            }
        },
        changed: function(product, action, added, skipLegacy) {
            if (typeof action === 'object') {
                var actions = action
                skipLegacy = added
            } else {
                var actions = {[action]: added}
            }
            product = parseInt(product)
            for (action in actions) {
                added = actions[action]
                var wasAdded = ((product in this.state) && (action in this.state[product])) ? this.state[product][action] : null
                if (wasAdded === null) {
                    if (added) {
                        if (!(product in this.state)) {
                            this.state[product] = {}
                        }
                        if (!(action in this.state[product])) {
                            this.state[product][action] = true
                        }
                    }
                } else {
                    if (added) {
                        this.state[product][action] = true
                    } else {
                        if (action in this.state[product]) {
                            delete this.state[product][action]
                        }
                        if (Object.keys(this.state[product]).length === 0) {
                            delete this.state[product]
                        }
                    }
                }

                if (wasAdded !== added) {
                    this.triggerChanged(product, action, added, skipLegacy)
                }
            }
        },
        // TODO this is legacy
        updateBasket: function(action) {
            var iblockID = 33
            if (action === 'buy') {
                getActualBasket(iblockID);

                arStatusBasketAspro = {};

                if($("#ajax_basket").length)
                    reloadTopBasket('add', $('#ajax_basket'), 200, 5000, 'Y');

                if($("#basket_line .basket_fly").length){
                    basketFly('open');
                }

                if($(".top_basket").length){
                    basketTop('open');
                }
            } else if (action === 'favorite') {
                getActualBasket(iblockID);
                arStatusBasketAspro = {};

                if($("#ajax_basket").length)
                    reloadTopBasket('wish', $('#ajax_basket'), 200, 5000, 'N');

                if($("#basket_line .basket_fly").length){
                    basketFly('wish');
                }
            } else if (action === 'compare') {
                getActualBasket(iblockID, 'Compare');
                arStatusBasketAspro = {};
                if($('#compare_fly').length){
                    jsAjaxUtil.InsertDataToNode('/ajax/show_compare_preview_fly.php', 'compare_fly', false);
                }
            }
        },
        clearBasket: function(skipLegacy) {
            for (var product in this.state) {
                this.changed(product, 'buy', false, skipLegacy)
                this.changed(product, 'favorite', false, skipLegacy)
            }
        },
        addChangedHandler: function(product, callback) {
            product = parseInt(product)
            if (!(product in this.lastChangedHandlerId)) {
                this.lastChangedHandlerId[product] = 0
            } else {
                this.lastChangedHandlerId[product]++
            }

            if (!(product in this.changedHandlers)) {
                this.changedHandlers[product] = {}
            }
            this.changedHandlers[product][this.lastChangedHandlerId[product]] = callback
            return this.lastChangedHandlerId[product]
        },
        removeChangedHandler: function(product, handlerId) {
            product = parseInt(product)
            if (product in this.changedHandlers) {
                if (handlerId in this.changedHandlers[product]) {
                    delete this.changedHandlers[product][handlerId]
                }
                if (Object.keys(this.changedHandlers[product]).length === 0) {
                    delete this.changedHandlers[product]
                }
            }
        },
        triggerChanged: function(product, action, added, skipLegacy) {
            if (product in this.changedHandlers) {
                for (var key in this.changedHandlers[product]) {
                    this.changedHandlers[product][key](action, added)
                }
            }

            //console.log(product,action,added,skipLegacy)
            if (skipLegacy) {
                return
            }
            // TODO this is legacy
            var item = product
            if (action === 'buy') {
                if (added) {
                    // th.hide();
                    $('.to-cart[data-item='+item+']').hide();
                    $('.to-cart[data-item='+item+']').closest('.counter_wrapp').find('.counter_block_inner').hide();
                    $('.to-cart[data-item='+item+']').closest('.counter_wrapp').find('.counter_block').hide();
                    $('.to-cart[data-item='+item+']').parents('tr').find('.counter_block_wr .counter_block').hide();
                    $('.to-cart[data-item='+item+']').closest('.button_block').addClass('wide');
                    // th.parent().find('.in-cart').show();
                    $('.in-cart[data-item='+item+']').show();

                    addBasketCounter(item);
                    //$('.wish_item[data-item='+item+']').removeClass("added");
                    $('.wish_item[data-item='+item+']').find(".value").show();
                    $('.wish_item[data-item='+item+']').find(".value.added").hide();
                    $('.wish_item.to[data-item='+item+']').show();
                    $('.wish_item.in[data-item='+item+']').hide();
                } else {
                    $('.in-cart[data-item='+item+']').hide();
                    $('.to-cart[data-item='+item+']').show();
                    $('.to-cart[data-item='+item+']').closest('.button_block').removeClass('wide');
                    $('.to-cart[data-item='+item+']').closest('.counter_wrapp').find('.counter_block').show();
                    $('.counter_block[data-item='+item+']').closest('.counter_block_inner').show();
                    $('.counter_block[data-item='+item+']').show();
                    $('.in-subscribe[data-item='+item+']').hide();
                    $('.to-subscribe[data-item='+item+']').show();
                    //$('.wish_item[data-item='+item+']').removeClass("added");
                    //$('.wish_item[data-item='+item+'] .value:not(.added)').show();
                    //$('.wish_item[data-item='+item+'] .value.added').hide();
                    //$('.wish_item.to[data-item='+item+']').show();
                    //$('.wish_item.in[data-item='+item+']').hide();
                    $('.banner_buttons.with_actions .wraps_buttons[data-item='+item+'] .basket_item_add').removeClass('added');
                    //$('.banner_buttons.with_actions .wraps_buttons[data-item='+item+'] .wish_item_add').removeClass('added');
                }
            } else if (action === 'favorite') {
                if (added) {
                    $('.like_icons').each(function(){
                        if($(this).find('.wish_item.text[data-item="'+item+'"]').length){
                            $(this).find('.wish_item.text[data-item="'+item+'"]').addClass('added');
                            $(this).find('.wish_item.text[data-item="'+item+'"]').find('.value').hide();
                            $(this).find('.wish_item.text[data-item="'+item+'"]').find('.value.added').css({"display":"block"})
                        }
                        if($(this).find('.wish_item_button').length){
                            /*$(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"]').addClass('added');
                            $(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"]').find('.value').hide();
                            $(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"]').find('.value.added').show();*/
                            $(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"].to').hide();
                            $(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"].in').show();
                        }
                    })

                    $('.in-cart[data-item=' + item + ']').hide();
                    $('.to-cart[data-item=' + item + ']').removeClass('clicked');
                    $('.to-cart[data-item=' + item + ']').parent().removeClass('wide');
                    if(!$('.counter_block[data-item=' + item + ']').closest('.counter_wrapp').find('.to-order').length)
                    {
                        $('.to-cart[data-item=' + item + ']').show();
                        $('.counter_block[data-item=' + item + ']').closest('.counter_block_inner').show();
                        $('.counter_block[data-item=' + item + ']').show();
                    }
                } else {
                    $('.like_icons').each(function(){
                        if($(this).find('.wish_item.text[data-item="'+item+'"]').length){
                            $(this).find('.wish_item.text[data-item="'+item+'"]').removeClass('added');
                            $(this).find('.wish_item.text[data-item="'+item+'"]').find('.value').show();
                            $(this).find('.wish_item.text[data-item="'+item+'"]').find('.value.added').hide();
                        }
                        if($(this).find('.wish_item_button').length){
                            /*$(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"]').removeClass('added');
                            $(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"]').find('.value').show();
                            $(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"]').find('.value.added').hide();*/
                            $(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"].to').show();
                            $(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"].in').hide();
                        }
                    })
                }
            } else if (action === 'compare') {
                if (added) {
                    $('.like_icons').each(function(){
                        if($(this).find('.compare_item.text[data-item="'+item+'"]').length){
                            $(this).find('.compare_item.text[data-item="'+item+'"]').addClass('added');;
                            $(this).find('.compare_item.text[data-item="'+item+'"]').find('.value').hide();
                            $(this).find('.compare_item.text[data-item="'+item+'"]').find('.value.added').css({"display":"block"});
                        }
                        if($(this).find('.compare_item_button').length){
                            /*$(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"]').addClass('added');
                            $(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"]').find('.value.added').show();
                            $(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"]').find('.value').hide();*/
                            $(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"].to').hide();
                            $(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"].in').show();
                        }
                    })
                } else {
                    $('.like_icons').each(function(){
                        if($(this).find('.compare_item.text[data-item="'+item+'"]').length){
                            $(this).find('.compare_item.text[data-item="'+item+'"]').removeClass('added');
                            $(this).find('.compare_item.text[data-item="'+item+'"]').find('.value').show();
                            $(this).find('.compare_item.text[data-item="'+item+'"]').find('.value.added').hide();
                        }
                        if($(this).find('.compare_item_button').length){
                            /*$(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"]').removeClass('added');
                            $(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"]').find('.value').show();
                            $(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"]').find('.value.added').hide();*/
                            $(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"].in').hide();
                            $(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"].to').show();
                        }
                    })
                }
            }
        }
    }

    $.fn[pluginName] = function(options) {
        var args = $.makeArray(arguments),
            after = args.slice(1)

        var methodCall = false
        var result = null

        var eachResult = this.each(function () {
            var instance = $.data(this, pluginName)

            if (instance) {
                if (instance[options]) {
                    if (!methodCall) {
                        result = instance[options].apply(instance, after)
                        methodCall = true
                    } else {
                        instance[options].apply(instance, after)
                    }
                } else {
                    //$.error('Method ' + options + ' does not exist on Plugin');
                }
            } else {
                var plugin = new Plugin(this, options)

                $.data(this, pluginName, plugin)
                return plugin
            }
        })

        if (!methodCall) {
            return eachResult
        } else {
            return result
        }
    }
    $.fn[pluginName].prototype.defaults = defaults
    $.fn[pluginName].prototype.methods = Plugin.prototype

    $(window)[pluginName]()
})(jQuery, window, document)
