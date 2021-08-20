<div id="orderServicePopup" class="w3-modal w3-text-bold">
    <div class="w3-modal-content w3-white" id="servicePopup"></div>
</div>
<script id="servicePopupTemplate" type="text/x-handlebars-template">
    <header class="w3-container w3-theme-d1 w3-padding">
        <a href="javascript:void(0)" onclick="closePopup()" class="w3-display-topright w3-hover-none w3-padding-8"><i class="fa fa-times w3-xlarge w3-padding-small"></i></a>
        <h5 class="w3-text-capitalize w3-text-bold">
        {{#if services}}<?php echo sprintf(lang('select_option'), sprintf(lang('order_option'), lang('type'))) ?>{{else}} <?php echo lang('notice') ?>{{/if}}
        </h5>
    </header>
    <div class="w3-container">
       <div class="w3-row w3-center w3-padding-16">
            {{#if services}}
            {{#each services}}
                <div class="w3-text-upper w3-col {{#iflt ../service_counter 2}}w3-block w3-round{{else}}s6 {{#if_even @index}}w3-radio-last{{else}}w3-radio-first{{/if_even}}{{/iflt}} w3-border w3-border-light-gray w3-padding-small w3-cursor-pointer choose-order-type" data-type="{{value}}" {{#if_even @index}}style="border-left:0!important"{{/if_even}}>
                    <h5 class="w3-text-upper no-margin {{class}}">{{#if class}}<i class="fa fa-check-circle-o" aria-hidden="true">&nbsp;</i>{{else}}<i class="fa fa-circle-o" aria-hidden="true">&nbsp;</i>{{/if}}{{langof name this.lang}}</h5>
                    <div class="w3-tiny {{class}}"><sub><i class="fa fa-clock-o {{class}}" aria-hidden="true"></i> {{number delay this.lang}} <?php echo lang('date_minutes') ?></sub></div>
                </div>
            {{/each}}
            {{else}}
                <div class="w3-col w3-block w3-text-red w3-left-align"><?php echo sprintf(lang('option_service_temp_down'), lang('collection') . ' ' . lang('and') . ' ' . lang('delivery') . ' ' . lang('both')) ?></div>
            {{/if}}
        </div>
    </div>
</script>