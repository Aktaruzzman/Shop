<div id="orderItemBoxPopup" class="w3-modal">
    <div class="w3-modal-content w3-white" id="itemBoxPopup"></div>
</div>
<script id="itemBoxPopupTemplate" type="text/x-handlebars-template">
    <form id="itemBoxPopupForm" method="post" class="w3-small">
        <header class="w3-container w3-theme-d1 w3-padding">
            <a href="javascript:void(0)" onclick="closePopup()" class="w3-display-topright w3-hover-none w3-padding-8"><i class="fa fa-times w3-xlarge w3-padding-small"></i></a>
            <h4 class="w3-text-capitalize w3-text-bold">{{langof name lang}}</h4>
        </header>
        <div id="sideDisplayContainer" class="w3-container"></div>
        <div class="w3-container w3-section-small top-levels">
            {{#if tops}}
            <div class="top-level">
                <p class="w3-large"><?php echo lang('select') . ' ' . lang('topping') ?></p>
                <ul class="w3-ul" style="line-height:1.25; max-height: 50vh; overflow-y: auto;">
                    {{#each tops}}
                    <li class="w3-row" style="padding-left: 0px; padding-right:0px">
                        <div class="w3-col s6">
                            <div class="w3-block">
                                <a class="side-minus" href="javascript:void(0)" data-source="topping" data-id="{{id}}" data-name="{{json this.name}}" data-unit="{{json this.unit.short}}" data-show_unit="{{this.show_unit}}" data-price="{{out_price}}" data-group_name="{{json group_name}}" data-group_id="{{group_id}}" data-lang="{{this.lang}}"><i class="w3-text-theme w3-text-bold">&minus;</i></a>
                                <span class="w3-text-capitalize">{{langof name this.lang}}</span> 
                            </div>
                        </div>
                        <div class="w3-col s6 w3-right-align">
                            <div class="w3-block">
                                <span class="w3-padding-small">
                                    <span>{{currency out_price this.lang}}</span>
                                    <sub>{{#if show_unit}}/{{langof unit.short this.lang}}{{/if}}</sub>
                                </span>
                                <span class="w3-right-align">
                                    <a class="side-plus w3-button w3-padding-small w3-theme-d1 w3-hover-theme w3-round w3-border w3-border-theme" href="javascript:void(0)" data-source="topping" data-id="{{id}}" data-name="{{json this.name}}" data-unit="{{json this.unit.short}}" data-show_unit="{{this.show_unit}}" data-price="{{out_price}}" data-group_name="{{json group_name}}" data-group_id="{{group_id}}" data-lang="{{this.lang}}"><?php echo lang('select') ?></a>
                                </span>
                            </div>
                        </div>
                    </li>
                    {{/each}}
                </ul>
                <div class="w3-section">
                    <a data-container="top-levels" data-level="top-level" class=" w3-button w3-theme-d1 w3-hover-theme w3-round w3-border w3-border-theme next-btn w3-text-bold"><?php echo lang('next') ?> <i class="fa fa-angle-double-right" aria-hidden="true"></i></a>
                </div>
            </div>
            {{/if}}
             <div class="w3-row top-level">
                <div class="w3-row w3-section">
                    <div class="w3-block">
                        <i class="fa fa-balance-scale w3-text-theme w3-border w3-border-light-gray w3-padding w3-medium w3-round"  onclick="dropdown('dropdownpop_{{#if item_id}}{{item_id}}{{else}}{{id}}{{/if}}_{{#if item_id}}{{id}}{{else}}0{{/if}}')"> : </i>
                        <span class="w3-border w3-border-light-gray w3-padding w3-round">
                            <i class="fa fa-minus-circle w3-text-theme w3-medium qty-impact w3-cursor-pointer" data-action="minus" data-input="inputpop_{{#if item_id}}{{item_id}}{{else}}{{id}}{{/if}}_{{#if item_id}}{{id}}{{else}}0{{/if}}" data-step="{{qty_step}}"></i>
                            <input class="w3-border-0 w3-center" type="text" name="qty" id="inputpop_{{#if item_id}}{{item_id}}{{else}}{{id}}{{/if}}_{{#if item_id}}{{id}}{{else}}0{{/if}}" value="{{qty}}" style="width: 60px;">
                            <i class="fa fa-plus-circle w3-text-theme w3-medium qty-impact w3-cursor-pointer" data-action="plus" data-input="inputpop_{{#if item_id}}{{item_id}}{{else}}{{id}}{{/if}}_{{#if item_id}}{{id}}{{else}}0{{/if}}" data-step="{{qty_step}}"></i>
                        </span>
                        {{#if show_unit}}<i>&nbsp;</i><span class="w3-border w3-border-light-gray w3-padding w3-round">{{langof unit.short lang}}</span>{{/if}}
                    </div>
                    <div id="dropdownpop_{{#if item_id}}{{item_id}}{{else}}{{id}}{{/if}}_{{#if item_id}}{{id}}{{else}}0{{/if}}" class="w3-dropdown-content w3-bar-block w3-border w3-border-light-gray w3-left-align w3-section-small" style="min-width: 100px;height:150px; overflow-y:auto;">
                        <ul class="w3-ul w3-block">
                            {{#for qty_lower qty_upper qty_step}}
                                <li class="w3-tiny add-to-qty-box w3-cursor-pointer item-box-qty-select" style="padding:4px!important" data-dropdown="dropdownpop_{{#if ../item_id}}{{../item_id}}{{else}}{{../id}}{{/if}}_{{#if ../item_id}}{{../id}}{{else}}0{{/if}}" data-input="inputpop_{{#if ../item_id}}{{../item_id}}{{else}}{{../id}}{{/if}}_{{#if ../item_id}}{{../id}}{{else}}0{{/if}}" data-qty="{{this}}"><i class="fa fa-balance-scale">&nbsp;</i>{{number this ../lang}}&nbsp;{{#if ../show_unit}}{{langof ../unit.short ../lang}}{{/if}}</li>
                            {{/for}}
                        </ul>
                    </div>
                </div>
                <div class="w3-row">
                    <div class="from-group">
                        <textarea class="w3-input w3-border w3-border-light-gray w3-round" name="note" rows="3" placeholder="<?php echo lang('special_item_note') ?>"></textarea>
                    </div>
                </div>
                <input type="hidden" name="item_id" value="{{#if item_id}}{{item_id}}{{else}}{{id}}{{/if}}">
                <input type="hidden" name="option_id" value="{{#if item_id}}{{id}}{{else}}0{{/if}}">
                <input type="hidden" name="role" value="{{role}}">
                <input type="hidden" name="has_top" value="{{has_top}}">
                <div class="w3-row w3-section">
                    <button class="w3-left w3-button w3-theme-d1 w3-hover-theme w3-border w3-border-theme w3-round w3-text-bold" type="submit"><i class="fa fa-cart-plus">&nbsp;</i><?php echo lang('add_to_cart') ?></button>
                     {{#if tops}}<a data-container="top-levels" data-level="top-level" class="w3-right w3-text-theme w3-cursor-pointer next-btn w3-text-bold"><i class="fa fa-angle-double-left" aria-hidden="true"></i> <?php echo lang('previous') ?></a>{{/if}}
                </div>
            </div>
        </div>
    </form>
</script>