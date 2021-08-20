$.fn.extend({
    suggest: function(options) {
        var suggestions = [];
        const input = this;
        var selectedIndex = -1;
        var inputValue = null;
        $("<div class=\"suggestions-container w3-col w3-block\" id=\"suggestions-container\"></div>").insertAfter(input);
        if ($(input).length >= 1) {
            $(input).on("input", function(e) {
                inputValue = this.value;
                if (inputValue.length >= 3) {
                    inputValue = inputValue.replace(/[&\/\\#,@+()$~%.'":*?<>{}]/g, '');
                    inputValue = inputValue.split(' ').join('-');
                    $.get(_BASE_URL_ + 'data/search/' + inputValue, function(data, status) {
                        suggestions = data;
                        if (suggestions.length > 0) {
                            $(".suggestions-container").html(format(suggestions));
                            $(".suggestions-container").show();
                            $("div.suggestions-container-row").hover(function() {
                                selectedIndex = $(this).attr("data-index");
                                $(this).focus();
                            });
                        } else $(".suggestions-container").hide();
                    });
                } else $(".suggestions-container").hide();
            });
        } else $(".suggestions-container").hide();

        function format(suggestions) {
            var htmlGlobal = "<div class='w3-section-tiny w3-small w3-text-bold-500'>";
            suggestions.forEach(function(item, index) {
                if (item.options && item.options.length > 0) {
                    item.options.forEach(function(option, i) {
                        console.log(option);
                        var htmlCurrent = "";
                        htmlCurrent += "<div class=\"w3-row w3-text-capitalize w3-padding-8 suggestions-container-row w3-border-bottom w3-border-light-gray\" tabindex=\"" + index + "_" + i + "\" id=\"item_" + index + "_" + i + "\" data-index=\"" + index + "_" + i + "\">";
                        htmlCurrent += "<div class='w3-col s6'>";
                        htmlCurrent += option.name[option.lang] + ' ' + option.item_name[option.lang];
                        htmlCurrent += "</div>";
                        htmlCurrent += "<div class='w3-col s6 w3-row'>";
                        htmlCurrent += "<div class='w3-col m10 s9 w3-right-align'>";
                        htmlCurrent += currency(option.out_price, option.lang);
                        if (option.show_unit) htmlCurrent += '<span class="w3-text-lower">&nbsp;' + option.unit[option.lang] + '&nbsp;</span>';
                        htmlCurrent += '<div class="w3-right-align w3-text-theme w3-text-lower in-cart-qty-label in-cart-' + option.item_id + '-' + option.id + '"></div>';
                        htmlCurrent += "</div>";
                        htmlCurrent += "<div class='w3-col m2 s3 w3-right-align'>";
                        htmlCurrent += '<a class="add-to-cart w3-theme-d1 w3-center w3-border w3-border-theme w3-round w3-padding-small w3-card" href="javascript:void(0)" data-item="' + option.item_id + '" data-option="' + option.id + '" data-role="' + option.role + '" data-top="' + option.has_top + '" data-qty="' + option.qty_lower + '" data-comment="' + option.allow_item_comment + '"><i class="fa fa-cart-plus"></i></a>';
                        htmlCurrent += "</div>";
                        htmlCurrent += "</div>"
                        htmlCurrent += "</div>";
                        htmlGlobal += htmlCurrent;
                    })
                } else {
                    var htmlCurrent = "";
                    htmlCurrent += "<div class=\"w3-row w3-text-capitalize w3-padding-8 suggestions-container-row w3-border-bottom w3-border-light-gray\" tabindex=\"" + index + "\" id=\"item_" + index + "\" data-index=\"" + index + "\">";
                    htmlCurrent += "<div class='w3-col s6'>";
                    if (item.item_id) htmlCurrent += item.name[item.lang] + ' ' + item.item_name[item.lang];
                    else htmlCurrent += item.name[item.lang];
                    htmlCurrent += "</div>";
                    htmlCurrent += "<div class='w3-col s6 w3-row'>";
                    htmlCurrent += "<div class='w3-col m10 s9 w3-right-align'>";
                    htmlCurrent += currency(item.out_price, item.lang);
                    if (item.show_unit) htmlCurrent += '<span class="w3-text-lower">&nbsp;' + item.unit[item.lang] + '&nbsp;</span>';
                    htmlCurrent += '<div class="w3-right-align w3-text-theme w3-text-lower in-cart-qty-label in-cart-' + item.id + '-0"></div>';
                    htmlCurrent += "</div>";
                    htmlCurrent += "<div class='w3-col m2 s3 w3-right-align'>";
                    if (item.item_id) htmlCurrent += '<a class="add-to-cart w3-theme-d1 w3-center w3-border w3-border-theme w3-round w3-padding-small w3-card" href="javascript:void(0)" data-item="' + item.item_id + '" data-option="' + item.id + '" data-role="' + item.role + '" data-top="' + item.has_top + '" data-qty="' + item.qty_lower + '" data-comment="' + item.allow_item_comment + '"><i class="fa fa-cart-plus"></i></a>';
                    else htmlCurrent += '&nbsp;<a class="add-to-cart w3-theme-d1 w3-center w3-border w3-border-theme w3-round w3-padding-small w3-card" href="javascript:void(0)" data-item="' + item.id + '" data-option="0" data-role="' + item.role + '" data-top="' + item.has_top + '" data-qty="' + item.qty_lower + '" data-comment="' + item.allow_item_comment + '"><i class="fa fa-cart-plus"></i></a>';
                    htmlCurrent += "</div>";
                    htmlCurrent += "</div>"
                    htmlCurrent += "</div>";
                    htmlGlobal += htmlCurrent;
                }
            });
            htmlGlobal += "</div>";
            return htmlGlobal;
        }
        $(document).keydown(function(e) {
            if (e.which === 38) {
                if (selectedIndex > 0) selectedIndex--;
                $("#item_" + selectedIndex).focus();
                $('.suggestions-container-row').removeClass('w3-theme-d4');
                $("#item_" + selectedIndex).addClass('w3-theme-d4');
            } else if (e.which === 40) {
                if (selectedIndex < suggestions.length - 1) selectedIndex++;
                $("#item_" + selectedIndex).focus();
                $('.suggestions-container-row').removeClass('w3-theme-d4');
                $("#item_" + selectedIndex).addClass('w3-theme-d4');
            } else return;
            e.preventDefault();
        });
    }
});