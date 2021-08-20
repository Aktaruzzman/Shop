<?php if (config_item('store_type') === "multiple") : ?>
    <div class="w3-row-padding w3-section-small">
        <div class="w3-theme-light w3-border w3-border-light-gray w3-round w3-padding-8" id="orderStore" style="min-height:15vh">
            <div class="w3-center"><img src="<?php echo ASSET_PATH . "img/ajax-loader.gif" ?>" class="w3-image"></div>
        </div>
    </div>
    <script id="orderStoreTemplate" type="text/x-handlebars-template">
        <div class="w3-row-padding w3-text-theme">
            <div class="w3-col m5 w3-section-small" style="line-height: 1.3;">
                <ul class="w3-col w3-dropdown-click w3-block w3-theme-light w3-text-theme">
                    <li onclick="dropdown('storeDropdown')" class="w3-row w3-cursor-pointer w3-border w3-border-light-gray w3-cursor-pointer w3-block w3-padding w3-round" style="height: 93px;">
                        <div class="w3-col s2 w3-left-align">
                            <i class="fa fa-store"></i>
                            <span class="spinning" style="display: none"><img src="<?php echo ASSET_PATH . "img/ajax-loader.gif" ?>" class="w3-image"></span>
                         </div>
                        <div class="w3-col s9 w3-center">
                            <div>{{langof selected.name 'en'}} <i class="fa fa-angle-down"></i></div>
                            <sub>{{langof selected.house 'en' }}, {{langof selected.area 'en'}}</sub>
                        </div>
                    </li>
                    <li id="storeDropdown" class="w3-dropdown-content w3-theme-light w3-bar-block" style="max-height: 250px; overflow-y:auto; overflow-x: hidden;">
                        <ul class="w3-ul">
                        {{#each list}} {{#ifneq ../selected.id  id}}
                            <li class="w3-cursor-pointer w3-border-light-gray w3-cursor-pointer select-store-branch" data-store="{{id}}">
                                <div class="w3-xlarge">{{langof name 'en'}}</div>
                                <sub>{{langof house 'en' }} {{langof area 'en'}}</sub>
                            </li>
                            <!---->
                        {{/ifneq}}{{/each}}
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="w3-col m7 w3-section-small">
                <div class="w3 w3-border w3-border-light-gray w3-round w3-padding"><strong>{{branch_change_text}}</strong></div>
            </div>
        </div>
</script>
<?php endif; ?>