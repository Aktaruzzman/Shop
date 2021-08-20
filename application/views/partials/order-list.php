<div class="w3-white item-page w3-border w3-border-theme w3-round <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="min-height:60vh;">
    <div class="w3-container" style="padding-top:16px">
        <input type="search" id="productSearchFromOrderPAge" class="w3-input w3-padding-8 w3-text-bold w3-round w3-border w3-bodyborder-theme w3-round w3-transparent" placeholder="<?php echo lang('add_item_by_search') ?>">
    </div>
    <ul class="w3-ul" id="orderPage">
        <div class="w3-padding-48 w3-center"><span class="spinning"><img src="<?php echo ASSET_PATH . "img/ajax-loader.gif" ?>" class="w3-image w3-circle w3-green"></span></div>
    </ul>
</div>
<script id="orderPageTemplate" type="text/x-handlebars-template">
    {{#each categories}}
        {{#if items}}
            <li class="menu w3-clear search-item w3-bodyborder-theme">
                <div onclick="divToggleShow('#cat-{{id}}')" class="w3-cursor-pointer" style="font-size:<?php echo $lang === 'bn' ? (config_item('web_theme_body_font_size') + 2) . 'px' : (config_item('web_theme_body_font_size') + 3) . 'px' ?>;">
                    <div class="category-meta" style="font-weight:<?php echo config_item('web_theme_body_font_weight') + 100 ?>">
                        <div class="w3-col s11 w3-left-align category-title w3-text-capitalize">
                            <div>{{langof name lang}}</div>
                        </div>
                        <div class="w3-col s1 w3-right-align"><i class="fa fa-angle-down" id="cat-{{id}}-angle"></i></div>
                    </div>
                </div>
                <div id="cat-{{id}}" class="w3-clear category-menus">
                    <div class="w3-clear menu-desc w3-text-bold-300">{{langof description lang}}</div>
                    <ul class="w3-ul">
                        {{#each items}}
                            <li class="menu w3-clear w3-row w3-bodyborder-theme" style="padding:8px 0px;">
                                {{#if options}}
                                    <h6 class="menu w3-text-capitalize">{{langof name lang}}</h6>
                                    <div class="w3-text-bold-300">{{langof description this.lang}}</div>
                                    <ul class="w3-ul">
                                        {{#each options}}
                                            <li class="menu w3-clear custom-li w3-row w3-bodyborder-theme" style="padding-left:0px!important;padding-right:0px!important">
                                                <div class="w3-col s6 w3-left-align">
                                                    <div class="menu menu-title w3-text-capitalize">
                                                        <i class="fa fa-angle-right w3-text-bold">&nbsp;</i>{{langof name lang}} <span class="w3-text-pink w3-tiny">{{discount_tag discount discount_by bogo this.lang}}</span>
                                                    </div>
                                                    <div class="w3-text-bold-300">{{langof description this.lang}}</div>
                                                </div>
                                                <div class="w3-col s6">
                                                    <div class="w3-row">
                                                        <div class="w3-col m10 s9 w3-right-align">
                                                            <div class="w3-padding-small">
                                                                {{#if_discount discount discount_by bogo}}<span style="text-decoration:{{#or bogo.collection bogo.delivery}} none; {{else}}line-through;{{/or}}">{{currency out_price lang}}</span>{{else}}<span class="no-discount">{{currency out_price lang}}</span>{{/if_discount}}
                                                                {{discount_price discount out_price discount_by this.lang}}
                                                                {{#if this.show_unit}}{{langof this.unit this.lang}}{{/if}}
                                                                <div class="w3-right-align w3-tiny w3-text-theme in-cart-qty-label w3-text-bold in-cart-{{../id}}-{{id}}"></div>
                                                            </div>
                                                        </div>
                                                        <div class="w3-col m2 s3 w3-right-align">
                                                            <a class="add-to-cart w3-theme-d1 w3-center w3-border w3-border-theme w3-round w3-padding-small w3-card" style="padding:4px" href="javascript:void(0)" data-item="{{item_id}}" data-option="{{id}}" data-role="{{role}}" data-top="{{has_top}}" data-comment="{{allow_item_comment}}" data-qty="{{qty_lower}}"><i class="fa fa-cart-plus"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        {{/each}}
                                    </ul>
                                {{else}}
                                    <div class="w3-col s6 w3-left-align">
                                        <div class="menu w3-text-capitalize" style="padding:4px 0px">
                                            {{langof name lang}} &nbsp;<span class="w3-text-pink w3-tiny">{{discount_tag discount discount_by bogo this.lang}}</span>
                                        </div>
                                        <div class="w3-text-bold-300">{{langof description this.lang}}</div>
                                    </div>
                                    <div class="w3-col s6">
                                        <div class="w3-row">
                                            <div class="w3-col m10 s9 w3-right-align">
                                                <div class="w3-padding-small">
                                                    {{#if_discount discount discount_by bogo}}<span style="text-decoration:{{#or bogo.collection bogo.delivery}} none; {{else}}line-through;{{/or}}">{{currency out_price lang}}</span>{{else}}<span class="no-discount">{{currency out_price lang}}</span>{{/if_discount}}
                                                    {{discount_price discount out_price discount_by this.lang}}
                                                    {{#if this.show_unit}}{{langof this.unit this.lang}}{{/if}}
                                                    <div class="w3-right-align w3-tiny w3-text-theme in-cart-qty-label w3-text-bold in-cart-{{id}}-0"></div>
                                                </div>
                                            </div>
                                            <div class="w3-col m2 s3 w3-right-align">
                                                <a class="add-to-cart w3-theme-d1 w3-center w3-border w3-border-theme w3-round w3-padding-small w3-card" style="padding:4px" href="javascript:void(0)" data-item="{{id}}" data-option="0" data-role="{{role}}" data-top="{{has_top}}" data-comment="{{allow_item_comment}}" data-qty="{{qty_lower}}"><i class="fa fa-cart-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                {{/if}}
                            </li>
                        {{/each}}
                    </ul>
                </div>
            </li>
        {{/if}}
    {{/each}}
</script>