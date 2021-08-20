<div id="outletChangingPopup" class="w3-modal">
    <div class="w3-modal-content w3-white" id="outletPopup"></div>
</div>
<script id="outletPopupTemplate" type="text/x-handlebars-template">
    <header class="w3-container w3-theme-d1 w3-padding">
        <a href="javascript:void(0)" onclick="closePopup()" class="w3-display-topright w3-hover-none w3-padding-8"><i class="fa fa-times w3-xlarge w3-padding-small"></i></a>
        <h4 class="w3-text-capitalize w3-text-bold"><?php echo sprintf(lang('select_option'), lang('outlet')) ?></h4>
    </header>
    <div class="w3-row-padding w3-padding-16">
        <div class="w3-col w3-dropdown-hover w3-mobile w3-hover-text-theme w3-hover-none">
            <div class="w3-bar-item w3-row w3-border w3-bodyborder-theme w3-round w3-padding w3-text-theme">
                <div class="w3-col s11" style="text-align: left!important;"><strong class="w3-text-bold">{{langof selected.name lang}}</strong> ({{langof selected.house lang}} {{langof selected.area lang}})</div>
                <div class="w3-col s1 w3-right-align"><div><i class="fa fa-angle-down"></i></div></div>
            </div>
            <div class="w3-dropdown-content w3-bar-block" style="max-height: 250px; overflow-y:auto;">
                <div class="w3-row-padding">
                    {{#each list}}
                        <div class="w3-col w3-block w3-border-bottom w3-bodyborder-theme w3-padding">
                            <div style="text-align: left!important;" class="w3-block w3-cursor-pointer w3-hover-text-theme choose-outlet" data-store="{{id}}"><strong class="w3-text-bold">{{langof name lang}}</strong> ({{langof house lang}}, {{langof area lang}})</div>
                        </div>
                    {{/each}}
                </div>
            </div>
        </div>
    </div>
</script>