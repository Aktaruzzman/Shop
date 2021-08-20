<div id="orderFreeItemPopup" class="w3-modal">
    <div class="w3-modal-content w3-white" id="freeItemPopup"></div>
</div>
<script id="freeItemPopupTemplate" type="text/x-handlebars-template">
    <header class="w3-container w3-theme-d1 w3-padding">
        <a href="javascript:void(0)" onclick="closePopup()" class="w3-display-topright w3-hover-none w3-padding-8"><i class="fa fa-times w3-xlarge w3-padding-small"></i></a>
        <h4 class="w3-text-capitalize w3-text-bold"><?php echo lang('select') ?> {{number quantity lang}} {{langof name lang}}</h4>
    </header>
    <div class="w3-container">
        <ul class="w3-ul w3-small" style="line-height:1.25">
            {{#each options}}
            <li class="w3-row" style="padding-left: 0px; padding-right:0px">
                <div class="w3-col s8 w3-text-capitalize">{{langof name ../lang}}</div>
                <div class="w3-col s4 w3-right-align"><a href="javascript:void(0)" class="add-free-item w3-text-bold w3-text-theme" data-plan="{{../id}}" data-item_id="{{item_id}}" data-option_id="{{#if option_id}}{{option_id}}{{else}}0{{/if}}"><?php echo lang('select') ?></a></div>
            </li>
            {{/each}}
        </ul>
    </div>
</script>