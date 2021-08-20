<div id="orderDeliveryAreaPopup" class="w3-modal w3-text-bold">
    <div class="w3-modal-content w3-white" id="deliveryAreaPopup"></div>
</div>
<script id="deliveryAreaPopupTemplate" type="text/x-handlebars-template">
    <header class="w3-container w3-theme-d1 w3-padding">
        <a href="javascript:void(0)" onclick="closePopup()" class="w3-display-topright w3-hover-none w3-padding-8"><i class="fa fa-times w3-xlarge w3-padding-small"></i></a>
        <h4 class="w3-text-capitalize w3-text-bold"><?php echo sprintf(lang('your_option'), lang('area')) ?></h4>
    </header>
    <div class="w3-container">
       <div class="w3-row w3-text-capitalize w3-padding-16">
            <ul class="w3-col w3-dropdown-hover w3-white w3-mobile">
                {{#if area}}
                <li onclick="dropdown('deliveryAreaOptions')" class="w3-hover-white w3-row w3-border w3-border-round w3-border-light-gray w3-cursor-pointer">
                    <div class="w3-col s10 w3-padding">{{langof area lang}}</div>
                    <div class="w3-text-theme w3-col s2 w3-padding w3-right-align"> <i class="fa fa-angle-down"></i></div>
                </li>
                {{else}}
                <li onclick="dropdown('deliveryAreaOptions')" class="w3-hover-white w3-row w3-border w3-border-round w3-border-light-gray w3-cursor-pointer">
                    <div class="w3-col s10 w3-padding"><?php echo sprintf(lang('select_option'), lang('area')) ?></div>
                    <div class="w3-text-theme w3-col s2 w3-padding w3-right-align"> <i class="fa fa-angle-down"></i></div>
                </li>
                {{/if}}
                <li class="w3-dropdown-content w3-bar-block" id="deliveryAreaOptions" style="max-height: 250px; overflow-y:auto; overflow-x: hidden;">
                   <ul class="w3-ul">{{#each areas}}<li class="w3-hover-text-theme w3-cursor-pointer submit-delivery-area" data-area="{{area_id}}">{{langof area ../lang}}</li>{{/each}}</ul>
                </li>
            </ul>
        </div>
    </div>
</script>