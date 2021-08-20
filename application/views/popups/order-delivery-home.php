<div id="orderDeliveryHomePopup" class="w3-modal w3-text-bold">
    <div class="w3-modal-content w3-white" id="deliveryHomePopup"></div>
</div>
<script id="deliveryHomePopupTemplate" type="text/x-handlebars-template">
    <header class="w3-container w3-theme-d1 w3-padding">
        <a href="javascript:void(0)" onclick="closePopup()" class="w3-display-topright w3-hover-none w3-padding-8"><i class="fa fa-times w3-xlarge w3-padding-small"></i></a>
        <h4 class="w3-text-capitalize w3-text-bold">{{langof area.formatted lang}}</h4>
    </header>
    <div class="w3-container">
       <div class="w3-row w3-text-capitalize w3-padding-16">
            <div class="w3-col w3-block w3-section-small">
                <label class="w3-left-align"><input type="radio" class="w3-check" name="home_type" value="select_one" data-error="#homeTypeError"{{#if homes}}checked="checked"{{else}}disabled="disabled"{{/if}}>&nbsp;<?php echo sprintf(lang('select_option'), lang('house')) ?></label>
                <label class="w3-left-align"><input type="radio" class="w3-check" name="home_type" value="add_new" data-error="#homeTypeError"{{#if homes}}{{else}}checked="checked"{{/if}}>&nbsp;<?php echo sprintf(lang('add_new_option'), lang('house')) ?></label> 
            </div>

            <ul class="w3-col w3-dropdown-click w3-white" id="homeTypeOptions">
                {{#if home}}
                <li onclick="dropdown('deliveryHomeOptions')" class="w3-bar-item w3-hover-white w3-row w3-border w3-round w3-border-theme">
                    <div class="w3-col s10 w3-padding">{{langof home.house lang}}</div>
                    <div class="w3-text-theme w3-col s2 w3-padding w3-right-align"> <i class="fa fa-angle-down"></i></div>
                </li>
                {{else}}
                    {{#if homes}}
                    <li onclick="dropdown('deliveryHomeOptions')" class="w3-bar-item w3-hover-white w3-row w3-border w3-border-round w3-border-light-gray">
                        <div class="w3-col s11 w3-padding"><?php echo sprintf(lang('select_option'), lang('house')) ?></div>
                        <div class="w3-text-theme w3-col s1 w3-padding-16 w3-right-align"> <i class="fa fa-angle-down"></i></div>
                    </li>
                    {{/if}}
                {{/if}}
                {{#if homes}}
                <li class="w3-dropdown-content w3-bar-block" id="deliveryHomeOptions" style="max-height: 250px; overflow-y:auto; overflow-x: hidden;">
                   <ul class="w3-ul">{{#each homes}}<li class="w3-cursor-pointer w3-border-light-gray w3-cursor-pointer submit-delivery-home" data-house="{{id}}">{{langof house ../lang}}</li>{{/each}}</ul>
                </li>
                {{/if}}
            </ul>
            <form class="w3-col w3-block" id="homeTextInputOption" method="post" style="display:{{#if homes}}none{{else}}block{{/if}};">
                <div class="form-group">
                    <input type="text" name="house_en" class="w3-input w3-border w3-border-theme w3-round">
                    <div class="w3-text-red" id="houseError"></div>
                </div>
                <button type="submit" class="w3-button w3-theme-d1 w3-hover-theme w3-border w3-border-theme w3-round"><?php echo lang('add_new') ?></button>
            </form>
        </div>
    </div>
</script>