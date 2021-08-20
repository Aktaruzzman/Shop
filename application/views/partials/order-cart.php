<div class="w3-white w3-border w3-border-theme w3-round <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="min-height:50vh;">
    <div class="w3-text-upper w3-row">
        <div class="w3-col s11 w3-left-align w3-container w3-medium w3-text-bold">
            <span class="fa fa-shopping-cart fa-2x w3-text-theme"></span>
            <?php echo sprintf(lang('your_option'), sprintf(lang('order_option'), lang(''))) ?>
            <span class="cart-hooker-text w3-text-bold w3-hide-small"></span>
        </div>
        <div class="w3-col s1 w3-right">
            <span class="spinning w3-padding-8" style="display: none">
                <img src="<?php echo ASSET_PATH . "img/ajax-loader.gif" ?>" class="w3-image">
            </span>
        </div>
    </div>
    <div class="w3-container" id="orderCart">
        <div class="w3-center"><span class="spinning"><img src="<?php echo ASSET_PATH . "img/ajax-loader.gif" ?>" class="w3-image w3-circle w3-green"></span></div>
    </div>
    <script id="orderCartTemplate" type="text/x-handlebars-template">
        <div class="w3-row w3-padding-8 w3-center">
            {{#each services}}
            <div class="w3-col {{#iflt ../service_counter 2}}w3-block w3-round{{else}}s6 {{#if_even @index}}w3-radio-last{{else}}w3-radio-first{{/if_even}}{{/iflt}} w3-border w3-bodyborder-theme w3-padding-8 w3-cursor-pointer {{#if class}}choose-receive-time{{else}}choose-order-type{{/if}}" data-type="{{value}}" {{#if_even @index}}style="border-left:0!important" {{/if_even}}>
                <div class="w3-text-upper {{class}}">{{#if class}}<i class="fa fa-check-circle-o" aria-hidden="true">&nbsp;</i>{{else}}<i class="fa fa-circle-o" aria-hidden="true">&nbsp;</i>{{/if}}{{langof name lang}}</div>
                <div class="w3-tiny {{class}}">
                    <sub>
                        <i class="fa fa-clock-o {{class}}" aria-hidden="true"></i> 
                        {{#if delivery_time}}
                        {{number delivery_date lang}} {{number delivery_time lang}}
                        <!---->
                        {{else}}
                        <!---->
                        {{number delay lang}} <?php echo lang('date_minutes') ?>
                        {{/if}}
                    </sub>
                </div>
            </div>
            {{/each}}
            <!----->
             {{#ifeq order_type 'delivery'}} {{#if delivery_area}}
                <div class="w3-col w3-block w3-row w3-section-tiny w3-padding-small w3-round w3-theme-light w3-border w3-bodyborder-theme order-delivery-area w3-cursor-pointer">
                    <div class="w3-col s11 w3-left-align w3-tiny">
                        <i class="fa fa-map-marker" aria-hidden="true">&nbsp;</i>
                        {{#if delivery_home}}{{langof delivery_home.house lang}}, {{/if}}
                        {{langof delivery_area.formatted lang}}
                    </div>
                    <div class="w3-col s1 w3-right-align"><i class="fa fa-angle-down" aria-hidden="true"></i></div>
                </div>
            {{/if}}{{/ifeq}}
        </div>
        
        <!---->
        {{#if items}}
        <ul class="w3-ul w3-clear cart-items" id="cartItemScrollbar">
            {{#each items as | pitem |}}
            <li class="w3-row w3-bodyborder-theme cart-item {{active}}">
                <div class="w3-col s1 w3-cursor-pointer w3-left-align">
                    <a class="cart-plus w3-text-theme w3-medium " data-line="{{@key}}" href="javascript:void(0)">&plus;</a>
                </div>
                <div class="w3-col s7 w3-left-align">
                    <span class="qty">{{number qty ../lang}}<span class="w3-tiny-no">{{#if ../show_unit}} {{langof unit_name ../lang}} {{/if}}</span></span>
                    <span class="item w3-text-capitalize">{{langof name ../lang}}</span>
                    {{#if sets}}
                        <div class="w3-row w3-tiny">
                            {{#each sets}}
                                <div style="margin-bottom:8px;">
                                    <div class="w3-text-capitalize" >
                                        {{#ifeq group_name.en 'Fixed Set'}}
                                        <i class="fa fa-angle-left">&nbsp;</i><?php echo lang('item') ?>&nbsp;<i class="fa fa-angle-right "></i>
                                        {{else}}
                                        <i class="fa fa-angle-left">&nbsp;</i>{{langof group_name ../../lang }}&nbsp;<i class="fa fa-angle-right "></i>
                                        {{/ifeq}}
                                    </div>
                                    {{#each sides}}
                                        <div class="w3-tiny">
                                            {{#if dec}}<a href="javascript:void(0)" class="cart-side-minus w3-text-theme" data-line="{{pitem.cart_id}}" data-g="{{this.group_id}}" data-i="{{this.item_id}}" data-o="{{this.option_id}}" data-t="{{this.topping_id}}" data-m="{{this.modify}}">&minus;</a>{{/if}}
                                            <span>{{#if qty}} {{#unless modify_id}} {{number qty ../../../lang}} {{#if ../../../show_unit}}{{langof unit_name ../../../lang}}{{/if}}{{/unless}}{{/if}} <span class="w3-text-capitalize">{{langof name ../../../lang}}</span>&nbsp;{{#if total}}{{#ifeq ../../../lang 'en'}}[{{amount total ../../../lang}}]{{else}}[{{currency total ../../../lang}}]{{/ifeq}} {{/if}}</span>
                                            {{#if inc}}<a href="javascript:void(0)" class="cart-side-plus w3-text-theme" data-line="{{pitem.cart_id}}" data-g="{{this.group_id}}" data-i="{{this.item_id}}" data-o="{{this.option_id}}" data-t="{{this.topping_id}}">&plus;</a>{{/if}}
                                        </div>
                                    {{/each}}
                                </div>
                            {{/each}}
                        </div>
                    {{/if}}
                </div>
                <div class="w3-col s3 w3-right-align">
                    {{#ifeq ../lang 'en'}}{{amount item_total ../lang}}{{else}}{{currency item_total ../lang}}{{/ifeq}}
                </div>
                <div class="w3-col s1 w3-right-align">
                    {{#iflteq qty 1}}
                        <a class="cart-remove w3-text-theme w3-medium " data-line="{{@key}}" href="javascript:void(0)">&times;</a>
                    {{else}}
                        <a class="cart-minus w3-text-theme w3-medium " data-line="{{@key}}" href="javascript:void(0)">&minus;</a>
                    {{/iflteq}}
                </div>
            </li>
            {{/each}}
        </ul>
        <ul class="w3-ul">
            <li class="w3-row cart-item w3-right-align w3-border w3-round w3-theme-light w3-bodyborder-theme">
                <div class="w3-col s6"><?php echo lang('subtotal') ?> : </div>
                <div class="w3-col s5">{{currency subtotal lang}}</div>
                <div class="w3-col s1">&nbsp;</div>
            </li>
            {{#if discount}}
                <li class="w3-row cart-item w3-padding-4 w3-right-align w3-border-0 w3-small">
                    <div class="w3-col s6"> (-) <?php echo lang('discount') ?> : </div>
                    <div class="w3-col s5">{{currency discount lang}}</div>
                    <div class="w3-col s1" onclick="$('#discountDetails').toggle('normal')"><i class="fa fa-angle-down"></i></div>
                    <div id="discountDetails" class="w3-row w3-tiny" style="display:none">
                        {{#if discount_details.plan_wise}}
                        <div class="w3-row">
                             <div class="w3-col s6"><?php echo lang('regular') . ' ' . lang('discount') ?> : </div>
                             <div class="w3-col s5">{{currency discount_details.plan_wise lang}}</div>
                             <div class="w3-col s1">&nbsp;</div>
                        </div>
                        {{/if}} {{#if discount_details.item_wise}}
                        <div class="w3-row">
                             <div class="w3-col s6"><?php echo lang('item') . ' ' . lang('discount') ?> : </div>
                             <div class="w3-col s5">{{currency discount_details.item_wise lang}}</div>
                             <div class="w3-col s1">&nbsp;</div>
                        </div>
                         {{/if}} {{#if discount_details.bogo_wise}}
                        <div class="w3-row">
                             <div class="w3-col s6"><?php echo lang('bogo') . ' ' . lang('discount') ?> : </div>
                             <div class="w3-col s5">{{currency discount_details.bogo_wise lang}}</div>
                             <div class="w3-col s1">&nbsp;</div>
                        </div>
                         {{/if}} 
                         
                        {{#if discount_details.promo_wise}}
                        <div class="w3-row">
                             <div class="w3-col s6"><?php echo lang('promo') ?> / <?php echo lang('coupon') ?> <?php echo lang('discount') ?> : </div>
                             <div class="w3-col s5">{{currency discount_details.promo_wise lang}}</div>
                              <div class="w3-col s1">&nbsp;</div>
                        </div>
                         {{/if}}
                    </div>
                </li>
            {{/if}}
            <!---->
            {{#if tax}}
                <li class="w3-row cart-item w3-padding-4 w3-right-align w3-border-0 w3-small">
                    <div class="w3-col s6 "> (-) <?php echo lang('vat') ?> : </div>
                    <div class="w3-col s5 ">{{currency tax lang}}</div>
                    <div class="w3-col s1 ">&nbsp;</div>
                </li>
            {{/if}}
            <!---->
            {{#if delivery_charge}}
                <li class="w3-row cart-item w3-padding-4 w3-right-align w3-border-0 w3-small">
                    <div class="w3-col s6"> (-) <?php echo sprintf(lang('option_charge'), lang('delivery')) ?> : </div>
                    <div class="w3-col s5">{{currency delivery_charge lang}}</div>
                    <div class="w3-col s1">&nbsp;</div>
                </li>
            {{/if}}
            <!---->
            {{#if service_charge}}
                <li class="w3-row cart-item w3-padding-4 w3-right-align w3-border-0 w3-small">
                    <div class="w3-col s6"> (-) <?php echo sprintf(lang('option_charge'), lang('service')) ?> : </div>
                    <div class="w3-col s5">{{currency service_charge lang}}</div>
                    <div class="w3-col s1">&nbsp;</div>
                </li>
            {{/if}}
            <!---->
            {{#if admin_fee}}
                <li class="w3-row cart-item w3-padding-4 w3-right-align w3-border-0 w3-small">
                    <div class="w3-col s6"> (-) <?php echo sprintf(lang('option_fee'), lang('admin')) ?> : </div>
                    <div class="w3-col s5">{{currency admin_fee lang}}</div>
                    <div class="w3-col s1">&nbsp;</div>
                </li>
            {{/if}}
            <!---->
            {{#if rounding}}
                <li class="w3-row cart-item w3-padding-4 w3-right-align w3-border-0 w3-small">
                    <div class="w3-col s6"> (+/-) <?php echo lang('rounding') ?> : </div>
                    <div class="w3-col s5">{{currency rounding lang}}</div>
                    <div class="w3-col s1">&nbsp;</div>
                </li>
            {{/if}}
            <li class="w3-row cart-item w3-right-align w3-border w3-theme-light w3-round w3-bodyborder-theme w3-section-tiny">
                <div class="w3-col s6 w3-text-bold"><?php echo lang('total') ?> : </div>
                <div class="w3-col s5 w3-text-bold">{{currency total lang}}</div>
                <div class="w3-col s1 w3-text-bold">&nbsp;</div>
            </li>
           
        </ul>
        <!---->
        {{#if minimum_promo_order}}
            <div class="w3-section">
                <div class="cart-item w3-border-0 w3-text-red w3-small w3-center">{{minimum_promo_order.message}}</div>
            </div>
        {{/if}}
        <!----->

        <?php if ($slug === "order") : ?>
        <div class="w3-section">
            {{#if minimum_delivery}}<div class="cart-item w3-border-0 w3-text-red w3-small w3-center">{{minimum_delivery.message}}</div>{{/if}}
            {{#if free_delivery_warning}}<div class="cart-item w3-border-0 w3-text-theme w3-small w3-center">{{free_delivery_warning.message}}</div>{{/if}}
            {{#if allow_checkout}}
                <a href="<?php echo site_url('order/checkout') ?>" class="w3-button w3-block w3-padding w3-theme-light w3-text-upper w3-xlarge w3-border w3-border-theme w3-round w3-hover-none"><?php echo lang('checkout') ?></a>
            {{else}}
                <a href="javascript:void(0)" onclick="return false" class="w3-button w3-block w3-padding w3-theme-light w3-text-upper w3-xlarge w3-border w3-border-theme w3-round w3-hover-none w3-disabled"><?php echo lang('checkout') ?></a>
            {{/if}}
        </div>
        <?php else : ?>
            <div class="w3-section">
               {{#if free_delivery_warning}}<div class="cart-item w3-border-0 w3-text-theme w3-small w3-center">{{free_delivery_warning.message}}</div>{{/if}}
                <a href="<?php echo site_url('order') ?>" class="w3-button w3-block w3-padding w3-theme-light w3-text-upper w3-xlarge w3-border w3-border-theme w3-round w3-hover-none"><?php echo lang('buy_more') ?></a>
            </div>
        <?php endif; ?>
        {{else}}
        <div class="w3-center w3-padding-48">
            <div class="w3-padding-48">
                <div><i class="fa fa-shopping-basket fa-5x w3-text-theme" aria-hidden="true"></i></div>
                <div class="w3-large w3-text-theme"> <?php echo lang('empty') . ' ' . lang('basket') ?></div>
            </div>
        </div>
        {{/if}}
    </script>
</div>