<div class="w3-border-light-gray w3-border w3-round w3-padding-16" style="margin-bottom: 4px;<?php echo config_item('web_theme_content_bg') ?>">
    <div class="w3-container">
        <input type="search" id="productSearchFromOrderPAge" class="w3-input w3-round w3-border w3-border-light-gray w3-round w3-transparent" placeholder="<?php echo lang('search') ?>">
    </div>
</div>
<ul class="w3-ul item-page w3-border w3-border-light-gray w3-round w3-text-bold-500" id="orderPage" style="min-height:60vh; <?php echo config_item('web_theme_content_bg') ?>">
    <div class="w3-padding-48 w3-center"><span class="spinning"><img src="<?php echo ASSET_PATH . "img/ajax-loader.gif" ?>" class="w3-image w3-circle w3-green"></span></div>
</ul>
<script id="orderPageTemplate" type="text/x-handlebars-template">
    {{#each categories}}
        {{#if items}}
            <li class="menu w3-clear w3-border-gray-light search-item" style="padding:0!important">
                <div class="w3-padding-tiny">
                    <a href="javascript:void(0)" onclick="divToggleShow('#cat-{{id}}')">
                        <div class="category-meta w3-section-tiny w3-row">
                            <div class="w3-col s11 w3-left-align category-title">
                                <div class="w3-text-capitalize w3-row-padding w3-text-bold-600">{{langof name lang}}</div>
                            </div>
                            <div class="w3-col s1 w3-right-align"><i class="fa fa-angle-down w3-text-bold" id="cat-{{id}}-angle"></i></div>
                        </div>
                    </a>
                </div>
                <div id="cat-{{id}}" class="category-menus">
                    <div class="w3-clear menu-desc w3-container w3-small">{{langof description lang}}</div>
                    <div class="w3-row-padding-4">
                        {{#each items}}
                            {{#if options}}
                                {{#each options}}
                                    <div class="w3-col w3-half w3-center w3-section-small">
                                        <div class="grid-box w3-border-0 w3-border-light-gray w3-round-medium" style="height: 244px;">
                                            <div class="w3-display-container">
                                                <div class="w3-centered"><img src="{{photos.thumb}}" alt="{{name.en}}" onerror="this.src='https://via.placeholder.com/120x120'" class="w3-image" /></div>
                                            </div>
                                            <div class="w3-block w3-section">
                                                <div class="w3-text-capitalize w3-small"><a href="javascript:void()">{{langof name lang}} {{langof ../name lang}} <i class="fa fa-info-circle w3-text-theme" aria-hidden="true"></i></a></div>
                                                <div class="w3-tiny">
                                                    {{#if_discount discount discount_by bogo}}<span class="w3-tiny">{{discount_tag discount discount_by bogo this.lang}}</span>{{/if_discount}}
                                                    {{#if_discount discount discount_by bogo}}<span style="text-decoration: line-through;">{{currency out_price lang}}</span>{{else}}<span class="no-discount">{{currency out_price lang}}</span>{{/if_discount}}
                                                    {{discount_price discount out_price discount_by this.lang}}{{#if this.show_unit}}<span class="w3-tiny">&nbsp;/&nbsp;{{langof unit lang}}</span>{{/if}}
                                                    <span class="w3-center w3-text-theme w3-tiny in-cart-qty-label in-cart-{{../id}}-{{id}}"></span>
                                                </div>
                                            </div>
                                            <div class="w3-row w3-small w3-padding-small">
                                                <div class="w3-col s6">
                                                    <div onclick="dropdown('dropdown_{{../id}}_{{id}}')">
                                                        <input type="hidden" id="input_{{../id}}_{{id}}" value="{{qty_lower}}">
                                                        <a href="javascript:void(0)" class="w3-button w3-block w3-hover-none w3-radio-first w3-border w3-border-light-gray w3-tiny" style="border-right:0!important">
                                                            <i class="fa fa-balance-scale w3-text-theme" aria-hidden="true"></i>
                                                            <span class="w3-qty w3-tiny" id="qty_{{../id}}_{{id}}">{{number qty_lower lang}}</span>
                                                            <span class="">{{#if show_unit}}{{langof unit lang}}{{/if}}</span>
                                                            <span class="fa fa-angle-down"></span>
                                                        </a>
                                                    </div>
                                                    <div id="dropdown_{{../id}}_{{id}}" class="w3-dropdown-content w3-bar-block w3-border w3-border-light-gray w3-left-align">
                                                        <ul class="w3-ul w3-block">
                                                            {{#for qty_lower qty_upper qty_step}}
                                                                <li class="w3-tiny add-to-cart w3-cursor-pointer" style="padding:4px!important" data-dropdown="dropdown_{{../../id}}_{{../id}}" data-qty="{{this}}" data-input="input_{{../../id}}_{{../id}}" data-item="{{../../id}}" data-option="{{../id}}" data-role="{{../role}}" data-top="{{../has_top}}" data-comment="{{../allow_item_comment}}"><i class="fa fa-cart-plus">&nbsp;</i>{{number this ../lang}}&nbsp;{{#if ../show_unit}}{{langof ../unit ../lang}}{{/if}}</li>
                                                            {{/for}}
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="w3-col s6">
                                                    <a href="javascript:void(0)" class="add-to-cart w3-block w3-button w3-hover-none w3-radio-last w3-border w3-border-light-gray w3-tiny" data-item="{{../id}}" data-option="{{id}}" data-role="{{role}}" data-top="{{has_top}}" data-comment="{{../allow_item_comment}}" data-qty="{{qty_lower}}"><i class="fa fa-cart-plus w3-text-theme">&nbsp;</i><?php echo lang('add_to_cart') ?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {{/each}}
                            {{else}}
                                <div class="w3-col w3-half w3-center w3-section-small">
                                    <div class="grid-box w3-border-0 w3-border-light-gray w3-round-medium" style="height: 244px;">
                                        <div class="w3-display-container">
                                            <div class="w3-centered"><img src="{{photos.thumb}}" alt="{{name.en}}" onerror="this.src='https://via.placeholder.com/120x120'" class="w3-image" /></div>
                                        </div>
                                        <div class="w3-block w3-section">
                                            <div class="w3-text-capitalize w3-small"><a href="javascript:void()">{{langof name lang}} <i class="fa fa-info-circle w3-text-theme" aria-hidden="true"></i></a></div>
                                            <div class="w3-tiny">
                                                {{#if_discount discount discount_by bogo}}<span class="w3-tiny">{{discount_tag discount discount_by bogo this.lang}}</span>{{/if_discount}}
                                                {{#if_discount discount discount_by bogo}}<span style="text-decoration: line-through;">{{currency out_price lang}}</span>{{else}}<span class="no-discount">{{currency out_price lang}}</span>{{/if_discount}}
                                                {{discount_price discount out_price discount_by this.lang}}{{#if this.show_unit}}<span class="w3-tiny">{{langof unit lang}}</span>{{/if}}
                                                <span class="w3-center w3-text-theme w3-tiny in-cart-qty-label in-cart-{{id}}-0"></span>
                                            </div>
                                        </div>
                                        <div class="w3-row w3-small w3-padding-small w3-text-bold">
                                            <div class="w3-col s6">
                                                <div onclick="dropdown('dropdown_{{id}}_0')">
                                                    <input type="hidden" id="input_{{id}}_0" value="{{qty_lower}}">
                                                    <a href="javascript:void(0)" class="w3-button w3-block w3-hover-none w3-radio-first w3-border w3-border-light-gray w3-tiny" style="border-right:0!important;padding:8px 2px;">
                                                        <i class="fa fa-balance-scale w3-text-theme" aria-hidden="true"></i>
                                                        <span class="w3-qty w3-tiny" id="qty_{{id}}_0">{{number qty_lower lang}}</span>
                                                        <span class="">{{#if show_unit}}{{langof unit lang}}{{/if}}</span>
                                                        <span class="fa fa-angle-down"></span>
                                                    </a>
                                                </div>
                                                <div id="dropdown_{{id}}_0" class="w3-dropdown-content w3-bar-block w3-border w3-border-light-gray w3-left-align" style="width: 125px!important;">
                                                    <ul class="w3-ul w3-block">
                                                        {{#for qty_lower qty_upper qty_step}}
                                                            <li class="w3-tiny add-to-cart w3-cursor-pointer" style="padding:4px 2px!important" data-dropdown="dropdown_{{../id}}_0" data-qty="{{this}}" data-input="input_{{../id}}_0" data-item="{{../id}}" data-option="0" data-role="{{../role}}" data-top="{{../has_top}}" data-comment="{{../allow_item_comment}}"><i class="fa fa-cart-plus">&nbsp;</i>{{number this ../lang}}&nbsp;{{#if ../show_unit}}{{langof ../unit ../lang}}{{/if}}</li>
                                                        {{/for}}
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="w3-col s6">
                                                <a href="javascript:void(0)" class="add-to-cart w3-block w3-button w3-hover-none w3-radio-last w3-border w3-border-light-gray w3-tiny" style="padding:8px 2px" data-item="{{id}}" data-option="0" data-role="{{role}}" data-top="{{has_top}}" data-comment="{{allow_item_comment}}" data-qty="{{qty_lower}}"><i class="fa fa-cart-plus w3-text-theme">&nbsp;</i><?php echo lang('add_to_cart') ?></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {{/if}}
                        {{/each}}
                    </div>
                </div>
            </li>
        {{/if}}
    {{/each}}
</script>