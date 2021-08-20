<div id="orderCustomsetPopup" class="w3-modal">
    <div class="w3-modal-content w3-white" id="customsetPoup"></div>
</div>
<script id="customsetPoupTemplate" type="text/x-handlebars-template">
    <form id="customsetPoupForm" class="w3-text-capitalize" method="post">
        <header class="w3-container w3-theme-d1 w3-padding">
            <a href="javascript:void(0)" onclick="closePopup()" class="w3-display-topright w3-hover-none w3-padding-8"><i class="fa fa-times w3-xlarge w3-padding-small"></i></a>
            <h4 class="w3-text-capitalize w3-text-bold">{{langof name lang}}</h4>
        </header>
        <div id="setOptionDisplayContainer" class="w3-container"></div>
        <div class="w3-container eposCartItemBundleData" id="eposCartItemBundleData">
            {{#each levels}}
                {{#isLevelWithMulSets this.length}}
                <div class="eposCartItemBundleDataLabel rel-level-{{@key}}" id="level-{{@key}}" style="display:{{displaySet @key}}">
                    <div class="eposCartItemBundleDataLevelOptionList option-parent">
                        <div class="w3-left-align w3-text-bold-500 "><?php echo sprintf(lang('select_option'), lang('option')) ?></div>
                        <ul class="w3-ul" style="line-height:1.25">
                            {{#each this}}
                            <li class="w3-row custom-li">
                                <div class="w3-col s11">{{langof name lang}}</div>
                                <div class="w3-col s1 w3-cursor-pointer option select-set-option w3-text-bold w3-text-theme" data-level="{{level}}" data-set="{{id}}"><?php echo lang('select') ?></div>
                            </li>
                            {{/each}}
                        </ul>
                    </div>
                    {{#each this}}
                    <div class="eposCartItemBundleDataLabel rel-level-option-{{level}}" id="relLevelOption-{{level}}-{{id}}" style="display:none">
                        <div class="w3-left-align w3-text-bold">
                            <span class="w3-border-bottom w3-large">
                                <?php echo lang('select') ?> {{langof name 'en'}}   
                                {{#ifneq min max}}
                                <!----->
                                {{#if min}}<span>&nbsp;({{langof 'min'}} : {{min}})&nbsp;</span>){{/if}}
                                <!----->
                                {{#if max}}<span>&nbsp;({{langof 'max'}} : {{max}})&nbsp;</span>{{/if}})
                                <!---->
                                {{else}}
                                <!---->
                                {{#ifgt max 1}}<span>&nbsp;({{langof 'max'}} : {{max}})&nbsp;</span>{{/ifgt}}
                                <!---->
                                {{/ifneq}}
                            </span>
                        </div>
                        <ul class="w3-ul" style="max-height: 30vh; overflow-y:auto;">
                            {{#each options}}
                            <li class="w3-row custom-li">
                                <div class="w3-col s6 w3-left-align">{{langof name lang}}</div>
                                <div class="w3-col s3 w3-right-align ">{{#if out_price}}&plus;&nbsp;{{currency out_price lang}}{{else}}&nbsp;{{/if}}</div>
                                <div class="w3-col s3 w3-right-align w3-cursor-pointer w3-text-bold w3-text-theme add-customset-side" data-source="{{../source}}" data-id="{{id}}" data-name="{{json this.name}}" data-unit="{{json this.unit.short}}" data-show_unit="{{this.show_unit}}" data-price="{{out_price}}" data-group_id="{{../id}}" data-group_name="{{json ../name}}" data-min="{{../min}}" data-max="{{../max}}" data-lang="{{../lang}}"><?php echo lang('select') ?></div>
                            </li>
                            {{/each}}
                        </ul>
                        {{#if_min min}}
                        <div class="w3-section w3-right-align">
                            <a class="w3-text-theme w3-text-bold w3-cursor-pointer navigate-next-set"><?php echo lang('next') ?> <i class="fa fa-angle-double-right" aria-hidden="true"></i></a>
                        </div>   
                        {{/if_min}}
                    </div>
                    {{/each}}
                </div>
                {{else}}
                    {{#each ../this}}
                        <div class="w3-row eposCartItemBundleDataLabel rel-level-option-{{level}}" id="relLevelOption-{{level}}-{{id}}" style="display:{{displaySet level}}">
                            <div class="w3-left-align w3-text-bold-500 w3-samll">
                                <span class="w3-border-bottom w3-large">
                                    <?php echo lang('select') ?> {{langof name lang}}  
                                    {{#ifneq min max}}
                                    <!----->
                                    {{#if min}}<span>&nbsp;(<?php echo lang('minimum') ?> : {{min}})&nbsp;</span>){{/if}}
                                    <!----->
                                    {{#if max}}<span>&nbsp;(<?php echo lang('maximum') ?> : {{max}})&nbsp;</span>{{/if}})
                                    <!---->
                                    {{else}}
                                    <!---->
                                    {{#ifgt max 1}}<span>&nbsp;(<?php echo lang('maximum') ?> : {{max}})&nbsp;</span>{{/ifgt}}
                                    <!---->
                                    {{/ifneq}}
                                </span>
                            </div>
                             <ul class="w3-ul" style="max-height: 30vh; overflow-y:auto;">
                                {{#each options}}
                                <li class="w3-row custom-li">
                                    <div class="w3-col s6 w3-left-align">{{langof name lang}}</div>
                                    <div class="w3-col s3 w3-right-align ">{{#if out_price}}&plus;&nbsp;{{currency out_price lang}}{{else}}&nbsp;{{/if}}</div>
                                    <div class="w3-col s3 w3-right-align w3-cursor-pointer w3-text-bold w3-text-theme add-customset-side" data-source="{{../source}}" data-id="{{id}}" data-name="{{json this.name}}" data-unit="{{json this.unit.short}}" data-show_unit="{{this.show_unit}}" data-price="{{out_price}}" data-group_id="{{../id}}" data-group_name="{{json ../name}}"  data-min="{{../min}}" data-max="{{../max}}" data-lang="{{../lang}}">
                                        <span class="w3-button w3-padding-small w3-theme-d1 w3-hover-theme w3-round w3-border w3-border-theme"><?php echo lang('select') ?></span>
                                    </div>
                                </li>
                                {{/each}}
                            </ul>
                            {{#if_min min}}
                                <div class="w3-section">
                                    <a class="w3-button w3-round w3-theme-d1 w3-hover-theme w3-text-bold w3-cursor-pointer navigate-next-set"><?php echo lang('next') ?> <i class="fa fa-angle-double-right" aria-hidden="true"></i> </a>
                                </div>   
                            {{/if_min}}
                        </div>
                    {{/each}}
                <!---->
                {{/isLevelWithMulSets}}
            <!---->
            {{/each}}
        </div>
        <footer class="w3-container w3-section" id="bundleDataSelectedCartButtonToProcess" style="display:none">
            <div class="w3-row w3-section">
                <div class="w3-col w3-block">
                    <i class="fa fa-balance-scale w3-text-theme w3-border w3-border-light-gray w3-padding w3-medium w3-round"  onclick="dropdown('dropdownpop_{{#if item_id}}{{item_id}}{{else}}{{id}}{{/if}}_{{#if item_id}}{{id}}{{else}}0{{/if}}')"> : </i>
                    <span class="w3-border w3-border-light-gray w3-padding w3-round">
                        <i class="fa fa-minus-circle w3-text-theme w3-medium qty-impact w3-cursor-pointer" data-action="minus" data-input="inputpop_{{#if item_id}}{{item_id}}{{else}}{{id}}{{/if}}_{{#if item_id}}{{id}}{{else}}0{{/if}}" data-step="{{qty_step}}"></i>
                        <input class="w3-border-0 w3-center" type="text" name="qty" id="inputpop_{{#if item_id}}{{item_id}}{{else}}{{id}}{{/if}}_{{#if item_id}}{{id}}{{else}}0{{/if}}" value="{{qty_lower}}" style="width: 60px;">
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
                <div class="w3-col w3-block from-group">
                    <textarea class="w3-input w3-border w3-border-light-gray w3-round" name="note" rows="3" placeholder="<?php echo lang('special_item_note') ?>"></textarea>
                </div>
            </div>
            <div class="w3-section">
                <input type="hidden" name="item_id" value="{{#if item_id}}{{item_id}}{{else}}{{id}}{{/if}}">
                <input type="hidden" name="option_id" value="{{#if item_id}}{{id}}{{else}}0{{/if}}">
                <input type="hidden" name="role" value="{{role}}">
                <button class="w3-button w3-theme-d1 w3-hover-theme w3-border w3-round w3-border-theme w3-text-bold" type="submit"><?php echo lang('add_to_cart') ?></button>
            </div>
        </footer>
    </form>
</script>