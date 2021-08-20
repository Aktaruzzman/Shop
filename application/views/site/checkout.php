<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo $page_title ?></h2>
                <h4><?php echo $page_subtitle ?></h4>
            </div>
            <div class="w3-row-padding w3-mobile">
                <div class="w3-col w3-third">
                    <div class="<?php echo config_item('web_theme_layout') === 'box' ? 'w3-mobile-container' : 'w3-mobile' ?>"><?php $this->load->view('partials/order-cart') ?></div>
                </div>
                <div class="w3-col w3-twothird w3-row-padding">
                    <div class="w3-border w3-border-theme w3-round w3-mobile" style="<?php echo config_item('web_theme_content_bg') ?>">
                        <article class="w3-left-align w3-padding"><?php echo $page['description'] ?></article>
                        <div id="orderCheckout" style="min-height: 50vh;"></div>
                        <script id="orderCheckoutTemplate" type="text/x-handlebars-template">
                            <form id="checkoutForm" class="w3-margin-bottom" method="post" action="<?php echo site_url('order/checkout') ?>">
                                <div class="w3-row-padding">
                                    <div class="w3-col w3-third form-group">
                                        <div class="w3-padding w3-round w3-theme"><i class="fa fa-user-o">&nbsp;</i>{{langof customer.name lang}}</div>
                                    </div>
                                    <div class="w3-col w3-third form-group">
                                        <div class="w3-padding w3-round w3-theme"><i class="fa fa-phone">&nbsp;</i>{{customer.phone}}</div>
                                    </div>
                                    <div class="w3-col w3-third form-group">
                                        <div class="w3-padding w3-round w3-theme"><i class="fa fa-envelope-o">&nbsp;</i>{{customer.email}}</div>
                                    </div>
                                </div>
                                <!--- delivery specific--->
                                {{#ifeq order_type 'delivery' }}
                                <div class="w3-row-padding">
                                    <div class="w3-col w3-block form-group">
                                    <label>{{order_type_text}} <?php echo lang('area') ?></label>
                                        <div class="w3-row">
                                            <div class="w3-col s10 w3-padding w3-border w3-border-theme w3-radio-first">{{langof cart.delivery_area.formatted lang}}</div>
                                            <div class="w3-col s2 w3-padding w3-border w3-border-theme w3-radio-last w3-center  w3-cursor-pointer order-delivery-area"  style="border-left:0!important">
                                                <span class="fa fa-edit">&nbsp;</span>
                                                <span class="w3-hide-small"><?php echo lang('Change') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w3-col w3-block form-group">
                                    <label><?php echo lang('house_address') ?></label>
                                        <div class="w3-row">
                                            {{#if home}}
                                                <div class="w3-col s10 w3-padding w3-border w3-border-theme w3-radio-first">{{langof home.house lang}}</div>
                                                <div style="border-left:0!important" class="w3-col s2 w3-padding-8 w3-border w3-border-theme w3-radio-last w3-center w3-cursor-pointer  order-delivery-home">
                                                    <span class="fa fa-edit">&nbsp;</span>
                                                    <span class="w3-hide-small"><?php echo lang('change') ?></span>
                                                    <input name="delivery_home_id" value="{{home.id}}" type="hidden">
                                                </div>
                                            {{else}}
                                                <div class="w3-col w3-block"><input type="text" id="delivery_home_name" name="delivery_home_name" class="w3-input w3-transparent w3-border w3-border-theme w3-round"></div>
                                            {{/if}}
                                        </div>
                                    </div>
                                </div>
                                {{/ifeq}}
                                <!--Delivery specific End--->

                                <!-- collection specific--->
                                {{#ifeq order_type 'collection' }}
                                <div class="w3-row-padding">
                                    <div class="w3-col w3-block">
                                        <div class="form-group">
                                        <label>{{order_type_text}} <?php echo lang('point') ?></label>
                                            <div class="w3-padding w3-border w3-border-theme w3-round">
                                            {{langof shop lang}}, {{langof store.house lang}}, {{langof store.area lang}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{/ifeq}}
                                <!----->
                                <div class="w3-row-padding">
                                    <div class="w3-col w3-block form-group">
                                    <label>{{order_type_text}} <?php echo lang('date') ?></label>
                                        <div class="w3-row">
                                            {{#if edit_date}}
                                            <div class="w3-col s10 w3-padding w3-border w3-border-theme w3-radio-first">{{number delivery_date lang }}</div>
                                            <div style="border-left:0!important" class="w3-col s2 w3-padding-8 w3-border w3-border-theme w3-radio-last w3-center  choose-receive-time w3-cursor-pointer">
                                                <span class="fa fa-edit">&nbsp;</span>
                                                <span class="w3-hide-small"><?php echo lang('change') ?></span>
                                            </div>
                                            {{else}}
                                                <div class="w3-col w3-block w3-padding w3-border w3-border-theme w3-round">{{number delivery_date lang }}</div>
                                            {{/if}}
                                        </div>
                                    </div>
                                    <div class="w3-col w3-block form-group">
                                    <label>{{order_type_text}} <?php echo lang('time') ?></label>
                                        <div class="w3-row">
                                            <div class="w3-col s10 w3-padding w3-border w3-border-theme w3-radio-first">{{number delivery_time lang}}</div>
                                            <div style="border-left:0!important" class="w3-col s2 w3-padding-8 w3-border w3-border-theme w3-radio-last w3-center  choose-receive-time w3-cursor-pointer">
                                                <span class="fa fa-edit">&nbsp;</span>
                                                <span class="w3-hide-small"><?php echo lang('change') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="w3-row-padding">
                                    <div class="w3-col w3-block form-group">
                                        <label>{{who_receive_on_behalf}}</label><br/>
                                        <label class="w3-margin-right"><input type="radio" class="w3-radio" name="who_receive" value="me" checked="checked">&nbsp;<?php echo lang('it_is_me') ?></label>
                                        <label><input type="radio" class="w3-radio" name="who_receive" value="someone">&nbsp;<?php echo lang('it_is_someone_else') ?></label>
                                    </div>
                                </div>
                                <div class="w3-row-padding someone-else w3-mobile" style="display: none;">
                                    <div class="w3-col w3-half form-group">
                                        <input id="name_en" name="name_en" type="text" class="w3-input w3-border w3-border-theme w3-round" placeholder="<?php echo lang('name') ?>">
                                    </div>
                                    <div class="w3-col w3-half form-group">
                                    <input id="phone" name="phone"  type="text" class="w3-input w3-transparent w3-border w3-border-theme w3-round" placeholder="<?php echo lang('phone') ?>">
                                    </div>
                                </div>
                                <div class="w3-row-padding">
                                    <div class="w3-col w3-block form-group">
                                        <label><?php echo sprintf(lang('have_option_question'), lang('coupon') . ' / ' . lang('promo') . ' ' . lang('code'))  ?></label>
                                        <div class="w3-row">
                                            <div class="w3-col m10 s8"> <input type="text" id="coupon" value="{{coupon}}" {{#if coupon}} readonly="readonly" {{/if}} class="w3-input w3-transparent w3-border w3-border-theme w3-radio-first"placeholder="<?php echo lang('code') ?>"></div>
                                            <div class="w3-col m2  s4 w3-cetner"> 
                                                <span class="w3-button w3-padding-8 w3-border w3-border-theme w3-radio-last w3-hover-none w3-block {{#if coupon}}remove-coupon{{else}}apply-coupon{{/if}}" style="padding:8px 0px!important; border-left:0!important">{{coupon_button}}</span>
                                            </div>
                                            <div class="has-error" id="couponError">{{coupon_error}}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="w3-row-padding">
                                    <div class="w3-col w3-block">
                                        <div class="form-group">
                                            <label><?php echo lang('order') . ' ' . lang('note') ?></label>
                                            <textarea name="order_note" id="order_note" class="w3-input w3-transparent w3-border w3-border-theme w3-round"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="w3-row-padding">
                                    <div class="w3-col w3-block"><?php echo sprintf(lang('select_option'), sprintf(lang('payment_option'), lang('type'))) ?></div>
                                    <div class="w3-col w3-half form-group">
                                        <label class="w3-input w3-border w3-border-theme w3-round w3-left-align"><input type="radio" class="w3-check" name="payment_type" value="COD" data-error="#paymentTypeError">&nbsp;<?php echo lang('cod') ?></label>
                                    </div>
                                    <div class="w3-col w3-half form-group">
                                        <label class="w3-input w3-border w3-border-theme w3-round w3-left-align"><input type="radio" class="w3-check" name="payment_type" value="EFT" data-error="#paymentTypeError">&nbsp;<?php echo lang('eft') ?></label>
                                    </div>
                                    <div class="w3-col w3-block w3-ceter" id="paymentTypeError"></div>
                                </div>
                                <div class="w3-row-padding w3-section">
                                    <div class="w3-col m6 s9">
                                        <button type="submit" class="w3-button w3-xlarge w3-block w3-theme-d1 w3-hover-theme w3-round w3-border w3-border-theme w3-text-upper" onclick="checkout()"><?php echo lang('confirm') ?></button>
                                    </div>
                                    <div class="w3-col m3 s3 w3-right">
                                        <a class="w3-block w3-padding-16 w3-text-red w3-right-align" href="<?php echo site_url(order_slug()) ?>"><span class="w3-border-bottom w3-border-red"><?php echo lang('cancel') ?></span></a>
                                    </div>
                                </div>
                            </form>
                        </script>
                    </div>
                </div>
            </div>
        </div>
        <?php echo $this->load->view('popups/store-closed') ?>
        <?php echo $this->load->view('popups/store-temp-closed') ?>
        <?php echo $this->load->view('popups/preorder-permission') ?>
        <?php echo $this->load->view('popups/order-service') ?>
        <?php echo $this->load->view('popups/order-delivery-area') ?>
        <?php echo $this->load->view('popups/order-delivery-home') ?>
        <?php echo $this->load->view('popups/order-receive-time') ?>
        <?php echo $this->load->view('popups/order-freeitem') ?>
        <script>
            $(function() {
                Render.checkout();
            });

            function checkout() {
                $('#checkoutForm').validate({
                    invalidHandler: function(event, validator) {
                        var errors = validator.numberOfInvalids();
                        console.log(errors);
                    },
                    errorClass: "has-error w3-small",
                    highlight: function(e) {
                        $(e).closest('.form-group').addClass('has-error');
                        $('.spinning').show();
                        $('.unhighlight').text('');
                    },
                    unhighlight: function(e) {
                        $(e).closest('.form-group').removeClass('has-error');
                        $('.spinning').hide();
                        $('.unhighlight').text('');
                    },
                    rules: {
                        delivery_home_name: {
                            required: true
                        },
                        delivery_home_id: {
                            required: true
                        },
                        name_en: {
                            required: true
                        },
                        name_bn: {
                            required: true
                        },
                        phone: {
                            required: true
                        },
                        payment_type: {
                            required: true
                        }
                    },
                    messages: {
                        delivery_home_name: "<?php echo sprintf(lang('field_required_msg'), lang('house')) ?>",
                        delivery_home_id: "<?php echo sprintf(lang('field_required_msg'), lang('house')) ?>",
                        name_en: "<?php echo sprintf(lang('field_required_msg'), lang('name')) ?>",
                        name_bn: "<?php echo sprintf(lang('field_required_msg'), lang('name')) ?>",
                        phone: "<?php echo sprintf(lang('field_required_msg'), lang('phone')) ?>",
                        payment_type: "<?php echo sprintf(lang('select_option'), sprintf(lang('payment_option'), lang('type'))) ?>"
                    },
                    errorPlacement: function(error, element) {
                        errorPlacement(error, element);
                    }
                });
            }
        </script>
    </main>
</div>