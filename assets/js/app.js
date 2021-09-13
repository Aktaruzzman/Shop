/**PLUGINS */
(function($) {
    $.fn.getFormData = function() {
        var data = {};
        var dataArray = $(this).serializeArray();
        for (var i = 0; i < dataArray.length; i++) {
            data[dataArray[i].name] = dataArray[i].value;
        }
        return data;
    };
})(jQuery);
(function($) {
    $.fn.clearForm = function() {
        return this.each(function() {
            var type = this.type,
                tag = this.tagName.toLowerCase();
            if (tag === 'form') return $(':input', this).clearForm();
            if (type === 'text' || type === 'tel' || type === 'email' || type === 'number' || type === 'password' || tag === 'textarea') this.value = '';
            else if (type === 'hidden') this.value = '';
            else if (type === 'checkbox' || type === 'radio') this.checked = false;
            else if (tag === 'select') this.selectedIndex = 0;
        });
    };
})(jQuery);
(function($) {
    function injector(t, splitter, klass, after) {
        var text = t.text(),
            a = text.split(splitter),
            inject = '';
        if (a.length) {
            $(a).each(function(i, item) {
                inject += '<span class="' + klass + (i + 1) + '" aria-hidden="true">' + item + '</span>' + after;
            });
            t.attr('aria-label', text).empty().append(inject)
        }
    }
    var methods = {
        init: function() {
            return this.each(function() {
                injector($(this), '', 'char', '');
            });
        },
        words: function() {
            return this.each(function() {
                injector($(this), ' ', 'word', ' ');
            });
        },
        lines: function() {
            return this.each(function() {
                var r = "eefec303079ad17405c889e092e105b0";
                injector($(this).children("br").replaceWith(r).end(), r, 'line', '');
            });
        }
    };

    $.fn.lettering = function(method) {
        if (method && methods[method]) {
            return methods[method].apply(this, [].slice.call(arguments, 1));
        } else if (method === 'letters' || !method) {
            return methods.init.apply(this, [].slice.call(arguments, 0)); // always pass an array
        }
        $.error('Method ' + method + ' does not exist on jQuery.lettering');
        return this;
    };
})(jQuery);

/**UTILITY FUNCTIONS */
function openPopup(id) {
    closePopup();
    jQuery('#' + id).show();
    $(".spinning").hide();
}

function closePopup() {
    jQuery('.w3-modal').hide();
    Side.clear();
    $(".spinning").hide();
};

function divToggleShow(hashId) {
    if (jQuery(hashId).length > 0) {
        if (jQuery(hashId + '-angle').length > 0 && jQuery(hashId + '-angle').hasClass('fa-angle-down')) {
            jQuery(hashId + '-angle').removeClass('fa-angle-down');
            jQuery(hashId + '-angle').addClass('fa-angle-up');
        } else {
            if (jQuery(hashId + '-angle').length > 0 && jQuery(hashId + '-angle').hasClass('fa-angle-up')) {
                jQuery(hashId + '-angle').removeClass('fa-angle-uo');
                jQuery(hashId + '-angle').addClass('fa-angle-down');
            }
        }
        jQuery(hashId).slideToggle();
    }
};

function scrollToPosition(position) {
    if (jQuery(position).length > 0) {
        $('html, body').animate({
            scrollTop: parseInt($(position).offset().top - 140)
        }, 1000);
    }
    if (arguments[1]) {
        $('.category').removeClass('w3-text-theme');
        $(arguments[1]).addClass('w3-text-theme');
    }
};

function dropdown(menu) {
    var x = document.getElementById(menu);
    if (x.className.indexOf("w3-show") === -1) {
        $('.w3-dropdown-content').removeClass('w3-show');
        x.className += " w3-show";
    } else x.className = x.className.replace(" w3-show", "");
};

function message(status) {
    if (status) {
        $('.success-msg').show();
        $('.error-msg').hide();
        $('.success-msg').delay(3000).fadeOut('slow');
    } else {
        $('.success-msg').hide();
        $('.error-msg').show();
        $('.error-msg').delay(3000).fadeOut('slow');
    }
}

function empty(val) {
    if (val === undefined) return true;
    if (typeof(val) == 'function' || typeof(val) == 'number' || typeof(val) == 'boolean' || Object.prototype.toString.call(val) === '[object Date]') return false;
    if (val == null || val.length === 0) return true;
    if (typeof(val) == "object") {
        var r = true;
        for (var f in val) r = false;
        return r;
    }
    return false;
}

function sleep(time) {
    return new Promise((resolve) => setTimeout(resolve, time));
}

function errorPlacement(error, element) {
    var placement = $(element).data('error');
    if (placement) $(placement).append(error);
    else error.insertAfter(element);
}

function reset(formId) {
    jQuery('#' + formId).clearForm();
}

function convert(digits, lang = "en") {
    if (digits) return lang === "bn" ? digits.toString().getBengali() : digits.toString().getEnglish()
}

function currency(amount, lang = 'en') {
    let CURRENCY = '৳';
    let SIDE = 'right';
    let LANG = lang;
    if (SIDE === 'right') {
        if (LANG === "bn") return Number(amount ? amount : 0).toFixed(2).getBengali() + ' ' + CURRENCY;
        else return Number(amount ? amount : 0).toFixed(2) + ' ' + 'Tk';
    } else {
        if (LANG === "bn") return CURRENCY + '' + Number(amount ? amount : 0).toFixed(2).getBengali();
        else return 'Tk' + '' + Number(amount ? amount : 0).toFixed(2);
    }
}

function amount(value, lang = 'en') {
    var number = Number(value ? value : 0).toFixed(2);
    return lang === "bn" ? number.getBengali() : number.getEnglish();
}

function number(value, lang = 'en') {
    return convert(value, lang);
}

function choose_set_menu(level, group) {
    jQuery('#level-' + level + ' .option-parent').hide();
    jQuery('#relLevelOption-' + level + '-' + group).show();
}

function next_set_menu() {
    if ($("#eposCartItemBundleData .eposCartItemBundleDataLabel:visible").next().length !== 0) {
        $("#bundleDataSelectedCartButtonToProcess").hide();
        $("#eposCartItemBundleData .eposCartItemBundleDataLabel:visible").next().show().prev().hide();
    } else {
        $("#eposCartItemBundleData .eposCartItemBundleDataLabel:visible").hide();
        $('.bundleDataNavigatorBlock').hide();
        $("#bundleDataSelectedCartButtonToProcess").show();
    }
};

function prev_set_menu() {
    if ($("#eposCartItemBundleData .eposCartItemBundleDataLabel:visible").prev().length !== 0) {
        $("#eposCartItemBundleData .eposCartItemBundleDataLabel:visible").prev().show().next().hide();
    } else {
        $("#eposCartItemBundleData .eposCartItemBundleDataLabel:visible").hide();
        $("#eposCartItemBundleData .eposCartItemBundleDataLabel:last").show();
    }
    return false;
};

/**FIRING EVENTS */
$(document).ready(function() {
    $("#productSearchFromOrderPAge").suggest();
    $('.flash-msg').delay(10000).fadeOut('slow');
    jQuery.validator.addMethod("alphaonly", function(value, element) {
        return this.optional(element) || /^[A-Za-z][A-Za-z\'\-]+([\ A-Za-z][A-Za-z\'\-]+)*/i.test(value);
    }, "Only alphabetical characters,please");
    $.validator.addMethod("alphanumeric", function(value, element) {
        return this.optional(element) || /^[\w\-\s]+$/i.test(value);
    }, "Letters, numbers, and underscores only please");
    $.validator.addMethod("nowhitespace", function(value, element) {
        return this.optional(element) || /^\S+$/i.test(value);
    }, "No white space please");
    jQuery.validator.addMethod('phone', function(value, element) {
        return this.optional(element) || value.length > 9 &&
            value.match(/^(1[ \-\+]{0,3}|\+1[ -\+]{0,3}|\+1|\+)?((\(\+?1-[2-9][0-9]{1,2}\))|(\(\+?[2-8][0-9][0-9]\))|(\(\+?[1-9][0-9]\))|(\(\+?[17]\))|(\([2-9][2-9]\))|([ \-\.]{0,3}[0-9]{2,4}))?([ \-\.][0-9])?([ \-\.]{0,3}[0-9]{2,4}){2,3}$/);
    }, 'Please specify a valid phone number');
    $.validator.addMethod("postcodeUK", function(value, element) {
        return this.optional(element) || /^((([A-PR-UWYZ][0-9])|([A-PR-UWYZ][0-9][0-9])|([A-PR-UWYZ][A-HK-Y][0-9])|([A-PR-UWYZ][A-HK-Y][0-9][0-9])|([A-PR-UWYZ][0-9][A-HJKSTUW])|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY]))\s?([0-9][ABD-HJLNP-UW-Z]{2})|(GIR)\s?(0AA))$/i.test(value);
    }, "Please specify a valid UK postcode");
    $.validator.addMethod("username", function(value, element) {
        return this.optional(element) || /^[A-Za-z][a-z0-9\-\s]+$/i.test(value);
    }, "Username must contain only letters, numbers, or dashes.");
    $.validator.addMethod("textareaText", function(value, element) {
        return this.optional(element) || /^[A-Za-z][a-z0-9\/\-\.\,\'\s]+$/i.test(value);
    }, "Text contain only letters, numbers, or dashes.");
    $.validator.addMethod("email", function(value, element) {
        return this.optional(element) || /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(value);
    }, "Please specify valid email addresss");
    $.validator.addMethod("website", function(value, element) {
        return this.optional(element) || /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/.test(value);
    }, "Valid website address only");
    $.validator.addMethod("valueNotEquals", function(value, element, arg) {
        return arg !== value;
    }, "please select");
    jQuery.validator.addMethod("eposValidTime", function(value, element) {
        return this.optional(element) || /((1[0-2]|0?[1-9]):([0-5][0-9]) ?([AaPp][Mm]))/igm.test(value);
    }, "Valid time please");
    jQuery.validator.addMethod("customeValidIpAddress", function(value, element) {
        return this.optional(element) || isValidIpAddress(value);
    }, "Invalid ip address");
    var x = window.matchMedia("(min-width: 600px)");
    if (x.matches) {
        if (jQuery("#menuPageLeftSidebar").length > 0 && jQuery("#menuPageRightSidebar").length > 0) {
            $("#menuPageLeftSidebar, #menuPageRightSidebar").stick_in_parent();
        } else if (jQuery("#menuPageLeftSidebar").length > 0) {
            $("#menuPageLeftSidebar").stick_in_parent();
        } else if (jQuery("#menuPageRightSidebar").length > 0) {
            $("#menuPageRightSidebar").stick_in_parent();
        }
    }
    var englishBangal = {
        '0': '০',
        '1': '১',
        '2': '২',
        '3': '৩',
        '4': '৪',
        '5': '৫',
        '6': '৬',
        '7': '৭',
        '8': '৮',
        '9': '৯'
    };
    String.prototype.getBengali = function() {
        var returnStr = this;
        for (var x in englishBangal) {
            returnStr = returnStr.replace(new RegExp(x, 'g'), englishBangal[x]);
        }
        return returnStr;
    };
    var banglaEnglish = {
        '০': '0',
        '১': '1',
        '২': '2',
        '৩': '3',
        '৪': '4',
        '৫': '5',
        '৬': '6',
        '৭': '7',
        '৮': '8',
        '৯': '9'
    };
    String.prototype.getEnglish = function() {
        var returnStr = this;
        for (var x in banglaEnglish) {
            returnStr = returnStr.replace(new RegExp(x, 'g'), banglaEnglish[x]);
        }
        return returnStr;
    };
    Handlebars.registerHelper('ifeq', function(arg1, arg2, options) { return (arg1 == arg2) ? options.fn(this) : options.inverse(this); });
    Handlebars.registerHelper('ifneq', function(arg1, arg2, options) { return (arg1 != arg2) ? options.fn(this) : options.inverse(this); });
    Handlebars.registerHelper('ifgt', function(arg1, arg2, options) { return (arg1 > arg2) ? options.fn(this) : options.inverse(this); });
    Handlebars.registerHelper('ifgteq', function(arg1, arg2, options) { return (arg1 >= arg2) ? options.fn(this) : options.inverse(this); });
    Handlebars.registerHelper('iflt', function(arg1, arg2, options) { return (arg1 < arg2) ? options.fn(this) : options.inverse(this); });
    Handlebars.registerHelper('iflteq', function(arg1, arg2, options) { return (arg1 <= arg2) ? options.fn(this) : options.inverse(this); });
    Handlebars.registerHelper('and', function(arg1, arg2, options) { return (!empty(arg1) && !empty(arg2)) ? options.fn(this) : options.inverse(this); });
    Handlebars.registerHelper('or', function(arg1, arg2, options) { return (!empty(arg1) || !empty(arg2)) ? options.fn(this) : options.inverse(this); });
    Handlebars.registerHelper('inArray', function(arr, val, options) { return _.includes(arr, val) ? options.fn(this) : options.inverse(this); });
    Handlebars.registerHelper('xor', function(arg1, arg2, options) { return ((!arg1 && arg2) || (arg1 && !arg2)) ? options.fn(this) : options.inverse(this); });

    Handlebars.registerHelper('currency', function(amount, lang) { return currency(amount, lang); });
    Handlebars.registerHelper('amount', function(value, lang) { return amount(value, lang); });
    Handlebars.registerHelper('number', function(value, lang) { return number(value, lang) });
    Handlebars.registerHelper('convert', function(number, lang) { return convert(number, lang) });
    Handlebars.registerHelper('json', function(context) { return JSON.stringify(context); });
    Handlebars.registerHelper('langof', function(val, lang = 'en') {
        if (lang) return val[lang];
        else return val['en'];
    });
    Handlebars.registerHelper('percent', function(number, func, lang = "en") {
        var result = convert(number.toString(), 'bn');
        return func == 'percent' ? result + ' %' : result;
    });
    Handlebars.registerHelper('for', function(from, to, incr, block) {
        var options = '';
        for (var i = from; i <= to; i += incr) options += block.fn(i);
        return options;
    });
    Handlebars.registerHelper('isLevelWithMulSets', function(length, options) { return length > 1 ? options.fn(this) : options.inverse(); });
    Handlebars.registerHelper('if_min', function(min, options) { return Number(min) <= 0 ? options.fn(this) : options.inverse(); });
    Handlebars.registerHelper('displaySet', function(level, options) { return Number(level) <= 1 ? 'block' : 'none'; });
    Handlebars.registerHelper('if_even', function(conditional, options) {
        if (((Number(conditional) + 1) % 2) == 0) return options.fn(this);
        else return options.inverse(this);
    });
    Handlebars.registerHelper('if_discount', function(discount, by = "this", bogo = null, options) {
        if ((by == 'this' || by == "bogo") && (discount.value > 0 || bogo != null)) return options.fn(this);
        else return options.inverse(this);
    });
    Handlebars.registerHelper('discount_tag', function(discount, by = "this", bogo = null, lang = 'en', options) {

        if (by === 'this') {
            if (discount.value > 0) {
                var off_string = { en: "OFF", bn: "মূল্যে ছাড়" }
                var result = discount.func === 'percent' ? number(discount.value, lang) + '% ' + off_string[lang] : currency(discount.value, lang) + ' ' + off_string[lang];
                return new Handlebars.SafeString('<span>' + result + '</span>');
            }
        } else if (by == 'bogo' && bogo != null && (bogo.collection || bogo.delivery) && bogo.web) {
            var off_string = {
                en: "Buy " + number(bogo.buy, lang) + ", get " + number(bogo.get, lang) + " free",
                bn: number(bogo.buy, lang) + " টা কিনলে, " + number(bogo.get, lang) + " টা ফ্রি"
            };
            if (Date.now() < Number(bogo.date_end)) return new Handlebars.SafeString('<span>' + off_string[lang] + '</span>');
        }
    });
    Handlebars.registerHelper('discount_price', function(discount, price, by = "this", lang = 'en', options) {
        if (by === 'this') {
            if (discount.value > 0 && price > 0) {
                var discount_price = 0;
                if (discount.func == 'percent') discount_price = price - (price * (discount.value / 100))
                else discount_price = price - discount.value;
                return new Handlebars.SafeString(currency(discount_price, lang));
            }
        }
    });

    $(document).on("click", ".submit-preorder-permission", function() {
        $(".spinning").show();
        $.post(_BASE_URL_ + 'data/setpreorder', {}, function(data, status) {
            closePopup();
            Render.init(data.branch);
        });
    });
    $(document).on("click", ".change-outlet", function() {
        $(".spinning").show();
        $.get(_BASE_URL_ + 'data/outlet', function(data, status) {
            Render.outletPopup(data);
            $(".spinning").hide();
        });
    });
    $(document).on("click", ".choose-outlet", function() {
        $(".spinning").show();
        var outlet = this.dataset.store
        $.post(_BASE_URL_ + 'data/outlet', { outlet: outlet }, function(data, status) {
            window.location.reload();
        });
    });
    $(document).on("click", ".choose-order-type", function() {
        $(".spinning").show();
        var type = this.dataset.type;
        $.post(_BASE_URL_ + 'data/ordertype', { order_type: type }, function(data, status) {
            if (data.delivery_area_popup) Render.orderDeliveryAreaPopup();
            else if (data.delivery_home_popup) Render.orderDeliveryHomePopup();
            else if (data.receive_time_popup) Render.orderReceiveTimePopup();
            else {
                closePopup();
                data.page_slug && data.page_slug === 'checkout' ? Render.checkout() : Cart.load();
            }
        });
    });
    $(document).on("click", ".order-delivery-area", function() {
        $(".spinning").show();
        Render.orderDeliveryAreaPopup();
    });
    $(document).on("click", ".order-delivery-home", function() {
        $(".spinning").show();
        Render.orderDeliveryHomePopup();
    });
    $(document).on("click", ".submit-delivery-area", function() {
        $(".spinning").show();
        var area = this.dataset.area;
        $.post(_BASE_URL_ + 'data/deliveryarea', { area: area }, function(data, status) {

            if (data.delivery_home_popup) Render.orderDeliveryHomePopup();
            else if (data.receive_time_popup) Render.orderReceiveTimePopup();
            else {
                closePopup();
                data.page_slug && data.page_slug === 'checkout' ? Render.checkout() : Cart.load();
            }
        });
    });
    $(document).on("click", ".submit-delivery-home", function() {
        $(".spinning").show();
        var house_id = this.dataset.house;
        $.post(_BASE_URL_ + 'data/deliveryhome', { house_id: house_id }, function(data, status) {
            if (data.receive_time_popup)
                Render.orderReceiveTimePopup();
            else {
                closePopup();
                data.page_slug && data.page_slug === 'checkout' ? Render.checkout() : Cart.load();
            }
        });
    });
    $(document).on("click", ".choose-receive-time", function() {
        $(".spinning").show();
        Render.orderReceiveTimePopup();
    });
    $(document).on("click", ".select-receive-date", function() {
        var input = { date: this.dataset.date };
        $.post(_BASE_URL_ + 'data/receivedate', input, function(data, status) {
            $(".spinning").show();
            if (data.status) {
                Render.orderReceiveTimePopup();
                data.page_slug && data.page_slug === 'checkout' ? Render.checkout() : Cart.load();
            } else $("#orderReceiveTimePopupError").text(data.message)
            $(".spinning").hide();
        });
    });
    $(document).on("click", ".select-receive-time", function() {
        var input = { date: this.dataset.date, time: this.dataset.time };
        $.post(_BASE_URL_ + 'data/receivetime', input, function(data, status) {
            $(".spinning").show();
            if (data.status) {
                closePopup();
                data.page_slug && data.page_slug === 'checkout' ? Render.checkout() : Cart.load();
            } else $("#orderReceiveTimePopupError").text(data.message)
            $(".spinning").hide();
        });
    });
    $(document).on("click", ".close-popup", function() {
        closePopup();
        $(".spinning").hide();
    });
    $(document).on("click", ".add-to-cart", function() {
        $(".spinning").show();
        var dataset = this.dataset;
        var data = { item_id: dataset.item, option_id: dataset.option, qty: Number(dataset.qty) ? Number(dataset.qty) : 1, role: dataset.role, top: Number(dataset.top) == 1 ? true : false, plan: 0 }
        if (dataset.dropdown) {
            data.input = dataset.input;
            data.dropdown = dataset.dropdown;
            $('.w3-dropdown-content').removeClass('w3-show');
            //dropdown(dataset.dropdown);
        }
        if (data.role === "normal" || data.role === "fixed_set") {
            if (data.top || dataset.comment === 'yes') Render.itemBox(data);
            else Cart.add(data);
        } else if (data.role === "custom_set") Render.customset(data);
    });
    $(document).on("click", ".next-btn", function() {
        var container = this.dataset.container;
        var level = this.dataset.level;
        var element = '.' + container + ' .' + level;
        if ($(element + ":visible").next().length != 0)
            $(element + ":visible").next().show().prev().hide();
        else {
            $(element + ":visible").hide();
            $(element + ":first").show();
        }
        return false;
    });
    $(document).on("click", ".prev-btn", function() {
        var container = this.dataset.container;
        var level = this.dataset.level;
        var element = '.' + container + ' .' + level;
        if ($(element + ":visible").prev().length != 0)
            $(element + ":visible").prev().show().next().hide();
        else {
            $(element + ":visible").hide();
            $(element + ":last").show();
        }
        return false;
    });
    $(document).on("click", ".item-box-qty-select", function() {
        var qty = this.dataset.qty;
        var input = this.dataset.input;
        var dropdownId = this.dataset.dropdown;
        $('#' + input).val(qty);
        dropdown(dropdownId);
    })
    $(document).on("click", ".qty-impact", function() {
        var step = this.dataset.step;
        var input = this.dataset.input;
        var action = this.dataset.action;
        var value = Number($('#' + input).val());
        if (action == 'plus') value += Number(step);
        else if (value - Number(step) > 0) value -= Number(step);
        $('#' + input).val(value);
    })
    $(document).on("click", ".cart-plus", function() {
        $(".spinning").show();
        var dataset = this.dataset;
        Cart.plus({ line: dataset.line });
    });
    $(document).on("click", ".cart-minus", function() {
        $(".spinning").show();
        var dataset = this.dataset;
        Cart.minus({ line: dataset.line });
    });
    $(document).on("click", ".cart-remove", function() {
        $(".spinning").show();
        var dataset = this.dataset;
        Cart.remove({ line: dataset.line });
    });
    $(document).on("click", ".side-plus", function() {
        var dataset = this.dataset;
        Side.add({ id: Number(dataset.id), source: dataset.source, name: JSON.parse(dataset.name), unit: JSON.parse(dataset.unit), show_unit: dataset.show_unit === 'true' ? true : false, qty: 1, price: Number(dataset.price), group_id: Number(dataset.group_id), group_name: JSON.parse(dataset.group_name), lang: dataset.lang, action: 'plus' });
        Render.sides('sideDisplayContainer');
    });
    $(document).on("click", ".side-minus", function() {
        var dataset = this.dataset;
        Side.add({ id: Number(dataset.id), source: dataset.source, name: JSON.parse(dataset.name), unit: JSON.parse(dataset.unit), show_unit: dataset.show_unit === 'true' ? true : false, qty: 1, price: Number(dataset.price), group_id: Number(dataset.group_id), group_name: JSON.parse(dataset.group_name), lang: dataset.lang, action: 'minus' });
        Render.sides('sideDisplayContainer');
    });
    $(document).on("submit", "#itemBoxPopupForm", function(ev) {
        ev.preventDefault();
        var formData = $(this).getFormData();
        formData.sides = Side.list();
        formData.plan = 0;
        Cart.add(formData);
        closePopup();
    });
    $(document).on("submit", "#homeTextInputOption", function(ev) {
        ev.preventDefault();
        var formData = $(this).getFormData();
        if ($.trim(formData.house_en) && formData.house_en.match(/^[A-Za-z0-9][A-Za-z0-9\/\-\.\,\'\s]+$/i)) {
            $.post(_BASE_URL_ + 'data/deliveryhome', { house_en: formData.house_en }, function(data, status) {
                closePopup();
                Render.checkout();
            });
        } else {
            $("#homeTextInputOption").find("input").first().focus();
            $("#houseError").text("Please use only english alphabets");
        }
    });

    $(document).on("click", ".cart-side-minus", function() {
        var d = this.dataset
        var input = { line: d.line, group_id: d.g, item_id: d.i, option_id: d.o, topping_id: d.t, modify_id: d.m }
        $.post(_BASE_URL_ + 'data/cartsideminus', input, function(data, status) {
            Render.cart(data.cart, data.lang);
        });
    });
    $(document).on("click", ".cart-side-plus", function() {
        var d = this.dataset
        var input = { line: d.line, group_id: d.g, item_id: d.i, option_id: d.o, topping_id: d.t, modify_id: d.m }
        $.post(_BASE_URL_ + 'data/cartsideplus', input, function(data, status) {
            Render.cart(data.cart, data.lang);
        });
    });
    $(document).on("click", ".select-set-option", function() {
        let dataset = $(this)[0].dataset;
        choose_set_menu(Number(dataset.level), Number(dataset.set));
    });
    $(document).on("click", ".navigate-next-set", function() { next_set_menu(); });
    $(document).on("click", ".add-customset-side", function() {
        var dataset = this.dataset;
        Side.add({ id: Number(dataset.id), source: dataset.source, name: JSON.parse(dataset.name), unit: JSON.parse(dataset.unit), show_unit: dataset.show_unit === 'true' ? true : false, qty: 1, price: Number(dataset.price), group_id: Number(dataset.group_id), group_name: JSON.parse(dataset.group_name), lang: dataset.lang, action: 'plus' });
        Render.sides('setOptionDisplayContainer');
        if (Number(dataset.max) > 0 && Side.group_qty(dataset.group_id) >= Number(dataset.max)) next_set_menu();
    });
    $(document).on("click", ".cancel-customset-side", function() {
        var dataset = this.dataset;
        Side.add({ id: Number(dataset.id), source: dataset.source, name: JSON.parse(dataset.name), unit: JSON.parse(dataset.unit), show_unit: dataset.show_unit === 'true' ? true : false, qty: 1, price: Number(dataset.price), group_id: Number(dataset.group_id), group_name: JSON.parse(dataset.group_name), lang: dataset.lang, action: 'minus' });
        Render.sides('setOptionDisplayContainer');
        if (Number(dataset.max) > 0 && Side.group_qty(dataset.group_id) >= Number(dataset.max)) next_set_menu();
    });
    $(document).on("submit", "#customsetPoupForm", function(ev) {
        ev.preventDefault();
        var formData = $(this).getFormData();
        formData.sides = Side.list();
        formData.plan = 0;
        Cart.add(formData);
        closePopup();
    });
    $(document).on("click", ".add-free-item", function(ev) {
        ev.preventDefault();
        var dataset = this.dataset;
        var data = { item_id: dataset.item_id, option_id: dataset.option_id, role: 'normal', top: false, plan: dataset.plan }
        closePopup();
        Cart.add(data);
    });
    $(document).on("click", ".apply-coupon", function(ev) {
        ev.preventDefault();
        var coupon = $('#coupon').val();
        if (empty(coupon)) {
            var warn_text = { en: 'Please, provide coupon/promo code.', bn: 'দয়া করে কুপন/প্রমো কোড সরবরাহ করুন।' };
            $('#couponError').text(warn_text[_LANG_]);
        } else {
            $('#couponError').text('');
            $.post(_BASE_URL_ + 'data/applycoupon', { code: coupon }, function(data, status) {
                if (status === 'success' && data.status) Render.checkout();
                else $('#couponError').text(data.msg);
            });
        }
    });
    $(document).on("click", ".remove-coupon", function(ev) {
        ev.preventDefault();
        $.post(_BASE_URL_ + 'data/removecoupon', {}, function(data, status) {
            Render.checkout();
        });
    });

    $(document).on("change", "input[type=radio][name=who_receive]", function() {
        if ($(this).val() === 'someone') $('.someone-else').show();
        else $('.someone-else').hide();
    });
    $(document).on("change", "input[type=radio][name=home_type]", function() {
        if ($(this).val() == 'select_one') {
            $('#homeTypeOptions').show();
            $('#homeTextInputOption').hide()
        } else {
            $('#homeTextInputOption').show()
            $('#homeTypeOptions').hide();
        }
    });

});

/**MODULES */
var Render = {
    init: function(store = 0) {
        $.get(_BASE_URL_ + 'data/product/' + store, function(data, status) {
            Render.category(data)
            Render.item(data);
            Render.cart(data.cart, data.lang);
        });
    },
    category: function(data) {
        var theTemplateScript = $('#menuCategoryListTemplate').html();
        var theTemplate = Handlebars.compile(theTemplateScript);
        var compiledTemplateHtml = theTemplate(data);
        $('#menuCategoryList').html(compiledTemplateHtml);
    },
    itemBox: function(inputs) {
        $.get(_BASE_URL_ + 'data/itembox/' + inputs.item_id + '/' + inputs.option_id, function(data, status) {
            var theTemplateScript = $('#itemBoxPopupTemplate').html();
            var theTemplate = Handlebars.compile(theTemplateScript);
            data.qty = inputs.qty ? inputs.qty : data.qty_lower;
            var compiledTemplateHtml = theTemplate(data);
            $('#itemBoxPopup').html(compiledTemplateHtml);
            openPopup('orderItemBoxPopup')
            var element = '.top-levels .top-level';
            $(element).each(function(e) {
                if (e != 0) $(this).hide();
            });
            $(".spinning").hide();
        });
    },
    customset: function(inputs) {
        $.get(_BASE_URL_ + 'data/customset/' + inputs.item_id + '/' + inputs.option_id, function(data, status) {
            var theTemplateScript = $('#customsetPoupTemplate').html();
            var theTemplate = Handlebars.compile(theTemplateScript);
            data.qty = inputs.qty;
            var compiledTemplateHtml = theTemplate(data);
            $('#customsetPoup').html(compiledTemplateHtml);
            openPopup('orderCustomsetPopup')
            $(".spinning").hide();
        });
    },
    item: function(data) {
        var theTemplateScript = $('#orderPageTemplate').html();
        var theTemplate = Handlebars.compile(theTemplateScript);
        var compiledTemplateHtml = theTemplate(data);
        $('#orderPage').html(compiledTemplateHtml);
    },
    cart: function(data, lang) {
        data = data ? data : [];
        data.lang = lang;
        for (var i in data.items) {
            if (data.items[i].sides.length) data.items[i].sets = Cart.sets(data.items[i].sides);
        }
        var theTemplateScript = $('#orderCartTemplate').html();
        var theTemplate = Handlebars.compile(theTemplateScript);
        var compiledTemplateHtml = theTemplate(data);
        $('#orderCart').html(compiledTemplateHtml);
        var x = window.matchMedia("(min-width: 1024px)");
        if (x.matches) {
            let element = document.querySelector('#cartItemScrollbar .active');
            if (element) element.scrollIntoView({ behavior: "auto", block: "center", inline: "end" });
        }
        $('.cart-hooker-text').text(currency(data.total, data.lang));
        if (data.set_closed_popup) this.showStoreClosed(data);
        else if (data.set_temp_closed_popup) this.showStoreTempClosed(data);
        else if (data.set_preorder_popup) this.setPreorder(data);
        else if (data.service_popup) this.servicePopup(data);
        else if (data.delivery_popup) this.orderDeliveryAreaPopup();
        else if (data.delivery_home_popup) this.orderDeliveryHomePopup();
        else if (data.receive_time_popup) this.orderReceiveTimePopup();
        else if (data.free_item_qyery && data.free_item_qyery.is_satisfied === false) this.freeItemPopup(data.free_item_qyery);
        else if (!data.allow_checkout && data.page_slug === 'checkout') window.location.href = _BASE_URL_ + '/order';
        $(".spinning").hide()
        this.inCartItem(data.items, lang, data.show_unit)
    },
    inCartItem: function(items, lang, show_unit = true) {
        $('.in-cart-qty-label').html('');
        for (var i in items) {
            if (items[i].free_rule <= 0) {
                var id = '.in-cart-' + items[i].item_id + '-' + items[i].option_id;
                var text = number(items[i].qty, lang).toString();
                if (show_unit) text += ' ' + items[i].unit_name[lang];
                $(id).html('<span data-line="' + i + '" class="cart-remove w3-cursor-pointer w3-text-lower"><i class="fa fa-shopping-basket w3-text-bold">&nbsp;' + text + '&nbsp;&times;</i></span>')
            }
        }
    },
    showStoreClosed: function(data) {
        var theTemplateScript = $('#storeClosedTemplate').html();
        var theTemplate = Handlebars.compile(theTemplateScript);
        var compiledTemplateHtml = theTemplate(data);
        $('#storeClosed').html(compiledTemplateHtml);
        openPopup('storeClosedPopup')
    },
    showStoreTempClosed: function(data) {
        var theTemplateScript = $('#storeTempClosedTemplate').html();
        var theTemplate = Handlebars.compile(theTemplateScript);
        var compiledTemplateHtml = theTemplate(data);
        $('#storeTempClosed').html(compiledTemplateHtml);
        openPopup('storeTempClosedPopup')
    },
    setPreorder: function(data) {
        var theTemplateScript = $('#preorderPermissionTemplate').html();
        var theTemplate = Handlebars.compile(theTemplateScript);
        var compiledTemplateHtml = theTemplate(data);
        $('#preorderPermission').html(compiledTemplateHtml);
        openPopup('preorderPermissionPopup')
    },
    servicePopup: function(data) {
        var theTemplateScript = $('#servicePopupTemplate').html();
        var theTemplate = Handlebars.compile(theTemplateScript);
        var compiledTemplateHtml = theTemplate(data);
        $('#servicePopup').html(compiledTemplateHtml);
        openPopup('orderServicePopup')
    },
    outletPopup: function(data) {
        var theTemplateScript = $('#outletPopupTemplate').html();
        var theTemplate = Handlebars.compile(theTemplateScript);
        var compiledTemplateHtml = theTemplate(data);
        $('#outletPopup').html(compiledTemplateHtml);
        openPopup('outletChangingPopup')
    },
    orderDeliveryAreaPopup: function() {
        $.get(_BASE_URL_ + 'data/deliveryarea', function(data, status) {
            console.log(data);
            var theTemplateScript = $('#deliveryAreaPopupTemplate').html();
            var theTemplate = Handlebars.compile(theTemplateScript);
            var compiledTemplateHtml = theTemplate(data);
            $('#deliveryAreaPopup').html(compiledTemplateHtml);
            openPopup('orderDeliveryAreaPopup');
        });
    },
    orderDeliveryHomePopup: function() {
        $.get(_BASE_URL_ + 'data/deliveryhome', function(data, status) {
            var theTemplateScript = $('#deliveryHomePopupTemplate').html();
            var theTemplate = Handlebars.compile(theTemplateScript);
            var compiledTemplateHtml = theTemplate(data);
            $('#deliveryHomePopup').html(compiledTemplateHtml);
            openPopup('orderDeliveryHomePopup');
        });
    },
    orderReceiveTimePopup: function() {
        $.get(_BASE_URL_ + 'data/datetimeslot/order', function(data, status) {
            console.log(data);
            var theTemplateScript = $('#receiveTimePopupTemplate').html();
            var theTemplate = Handlebars.compile(theTemplateScript);
            var compiledTemplateHtml = theTemplate(data);
            $('#receiveTimePopup').html(compiledTemplateHtml);
            openPopup('orderReceiveTimePopup')
            $(".spinning").hide();
        });
    },
    sides: function(container) {
        var sides = Side.list();
        var sets = Cart.sets(sides);
        if (sets && sides.length > 0) {
            var html = '<table class="w3-table-all w3-border-0 no-padding w3-left-align">';
            for (var s in sets) {
                html += '<tr class="w3-white w3-border-0">';
                html += '<td class="no-padding no-margin w3-text-bold w3-left-align" style="padding-left:0px!important">';
                html += sets[s].group_name[sets[s].sides[0].lang] + ' : ';
                var side_list = sets[s].sides;
                var str = '';
                for (var i in side_list) {
                    var qty = side_list[i].qty > 0 ? side_list[i].qty + ' ' : '';
                    var unit = side_list[i].show_unit ? side_list[i].unit[side_list[i].lang] : '';
                    if (side_list[i].source !== 'modify') str += number(qty, side_list[i].lang) + unit + ' ' + side_list[i].name[side_list[i].lang];
                    else str += side_list[i].name[side_list[i].lang];
                    if (i != (side_list.length - 1)) str += ', ';
                }
                html += str;
                html += '</td>';
                html += '</tr>';
            }
            html += '</table>'
            $("#" + container).html(html);
        } else {
            $("#" + container).html('');
        }
    },
    freeItemPopup: function(data) {
        var theTemplateScript = $('#freeItemPopupTemplate').html();
        var theTemplate = Handlebars.compile(theTemplateScript);
        var compiledTemplateHtml = theTemplate(data);
        $('#freeItemPopup').html(compiledTemplateHtml);
        openPopup('orderFreeItemPopup')
    },
    checkout: function() {
        $.get(_BASE_URL_ + 'data/checkout', function(data, status) {
            Render.cart(data.cart, data.lang);
            Render.checkout_form(data);
        });
    },
    checkout_form: function(data) {
        var theTemplateScript = $('#orderCheckoutTemplate').html();
        var theTemplate = Handlebars.compile(theTemplateScript);
        var compiledTemplateHtml = theTemplate(data);
        $('#orderCheckout').html(compiledTemplateHtml);
    }
};
var Side = (function() {
    var sides = [];
    load();

    function init(side) {
        this.id = side.id;
        this.source = side.source ? side.source : false;
        this.name = side.name;
        this.unit = side.unit ? side.unit : { en: "", bn: "" };
        this.show_unit = side.show_unit ? side.show_unit : false;
        this.qty = side.qty;
        this.price = side.price;
        this.group_id = side.group_id;
        this.group_name = side.group_name;
        this.total = side.total;
        this.lang = side.lang;
    }

    function save() {
        sessionStorage.setItem("sides", JSON.stringify(sides));
    }

    function load() {
        sides = JSON.parse(sessionStorage.getItem("sides"));
        if (sides === null) {
            sides = [];
        }
    }

    function save_and_load() {
        save();
        load();
        return;
    }
    var obj = {};
    obj.add = function(side) {
        var found = false;
        for (var i in sides) {
            if (sides[i].id == side['id'] && sides[i].group_id == side.group_id) {
                found = true;
                if (side.action == 'minus' && sides[i].qty > 0) sides[i].qty -= side['qty'];
                else sides[i].qty += side['qty'];
                if (Number(sides[i].qty) <= 0 && sides[i].group_id != 666666) sides.splice(i, 1);
                else sides[i].total = sides[i].qty * sides[i].price
                break;
            }
        }
        if (!found) {
            if (side.group_id == 666666) {
                side.qty = 0;
                side.total = side.price;
                sides.push(new init(side));
            } else {
                if (side.qty > 0) {
                    side.total = side.qty * side.price;
                    sides.push(new init(side));
                }
            }
        }
        save_and_load();
    };
    obj.group_qty = function(group) {
        var group_sides = sides.filter(function(v) {
            return Number(v.group_id) == Number(group)
        })
        var qty = 0;
        for (var i in group_sides) {
            qty += group_sides[i].qty
        }
        return qty
    };
    obj.list = function() {
        var listCopy = [];
        for (var i in sides) {
            var item = sides[i];
            var itemCopy = {};
            for (var p in item) {
                itemCopy[p] = item[p];
            }
            listCopy.push(itemCopy);
        }
        return listCopy;
    };
    obj.get = function() {
        return JSON.parse(sessionStorage.getItem("sides"));
    };
    obj.clear = function() {
        sides = [];
        save();
        load();
    };
    return obj;
})();
var Cart = {
    add: function(input) {
        $.post(_BASE_URL_ + 'data/cartadd', input, function(data, status) {
            Render.cart(data.cart, data.lang);
        });
    },
    plus: function(input) {
        $.post(_BASE_URL_ + 'data/cartplus', input, function(data, status) {
            Render.cart(data.cart, data.lang);
        });
    },
    minus: function(input) {
        $.post(_BASE_URL_ + 'data/cartminus', input, function(data, status) {
            Render.cart(data.cart, data.lang);
        });
    },
    remove: function(input) {
        $.post(_BASE_URL_ + 'data/cartremove', input, function(data, status) {
            Render.cart(data.cart, data.lang);
        });
    },
    load() {
        $.get(_BASE_URL_ + 'data/cart', function(data, status) {
            Render.cart(data.cart, data.lang);
        });
    },
    sets: function(sides) {
        var sets = [];
        for (var i in sides) {
            var obj = { group_id: sides[i].group_id, group_name: sides[i].group_name };
            var found = false;
            for (var j in sets) {
                if (sets[j].group_id === sides[i].group_id && JSON.stringify(obj.group_name) === JSON.stringify(sides[i].group_name)) {
                    found = true;
                    break;
                }
            }
            if (!found) sets.push(obj);
        }
        for (var index in sets) {
            sets[index].sides = [];
            for (var s in sides) {
                if (sides[s].group_id === sets[index].group_id) sets[index].sides.push(sides[s]);
            }
        }
        return sets;
    },
};