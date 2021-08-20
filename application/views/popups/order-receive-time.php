<div id="orderReceiveTimePopup" class="w3-modal">
    <div class="w3-modal-content w3-white" id="receiveTimePopup"></div>
</div>
<script id="receiveTimePopupTemplate" type="text/x-handlebars-template">
    <header class="w3-container w3-theme-d1 w3-padding">
        <a href="javascript:void(0)" onclick="closePopup()" class="w3-display-topright w3-hover-none w3-padding-8"><i class="fa fa-times w3-xlarge w3-padding-small"></i></a>
        <h4 class="w3-text-capitalize w3-text-bold">{{label_select_option}}</h4>
    </header>
    <div class="w3-section w3-row-padding" id="receiveTimePopupForm">
     <div class="w3-text-red w3-col w3-block" id="orderReceiveTimePopupError"></div>
        {{#if limit}}
        <div class="w3-col w3-block w3-margin-bottom">
            <ul class="w3-col w3-dropdown-hover w3-mobile w3-white">
                <li class="w3-bar-item w3-hover-white w3-row w3-padding w3-border w3-round w3-border-light-gray">
                    <div class="w3-col s10"><?php echo lang('date') ?> : {{number selected_date.date_text lang }}<sub>&nbsp;({{selected_date.date_day}})</sub></div>
                    <div class="w3-col s2 w3-right-align"> <i class="fa fa-angle-down"></i></div>
                </li>
                <li class="w3-dropdown-content w3-bar-block" style="max-height: 250px; overflow-y:auto; overflow-x: hidden;">
                   <div class="w3-row-padding-4 w3-padding-8">
                       {{#each date_slots}}
                        <div class="w3-col m4 s6 form-group">
                           <button class="w3-button w3-border w3-border-light-gray w3-round w3-block w3-hover-theme select-receive-date" data-date="{{date_value}}" >
                               {{number date_text ../lang}}<sub>&nbsp;({{date_day}})</sub>
                           </button>
                        </div>
                     {{/each}}
                    </div>
                </li>
            </ul>
        </div>
        {{/if}}
         <div class="w3-col w3-block w3-margin-bottom">
            <ul class="w3-col w3-dropdown-hover w3-mobile w3-white">
                <li class="w3-bar-item w3-hover-white w3-row w3-padding w3-border w3-round w3-border-light-gray">
                    <div class="w3-col s10">{{#if selected_time}}<?php echo lang('time') ?> : {{number selected_time lang}}{{else}}{{label_select_time}}{{/if}}</div>
                    <div class="w3-col s2 w3-right-align"> <i class="fa fa-angle-down"></i></div>
                </li>
                <li class="w3-dropdown-content w3-bar-block" style="max-height: 250px; overflow-y:auto; overflow-x: hidden; min-width:300px;">
                   <div class="w3-row-padding-4 w3-padding-8">
                      {{#each time_slots}}
                        <div class="w3-col m4 s4 form-group">
                            <button class="w3-button w3-block w3-border w3-border-light-gray w3-round w3-block w3-hover-theme select-receive-time" data-date="{{../selected_date.date_value}}" data-time="{{this}}">{{number this ../lang}}</button>
                        </div>
                      {{/each}}
                    </div>
                </li>
            </ul>
        </div>
        {{#if limit}}
            <div class="w3-col w3-block w3-margin-bottom">
                {{#if selected_time}}
                   <a href="javascript:void(0)" class="w3-button w3-theme-d1 w3-hover-theme w3-border w3-border-theme w3-round close-popup">{{label_save_and_set}}</a>&nbsp;
                {{/if}}
            </div>
        {{/if}}
</div>
</script>