<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo $page_title ?></h2>
                <h4><?php echo $page_subtitle ?></h4>
                <article class="w3-left-align"><?php echo $page['description'] ?></article>
            </div>
            <div class="w3-row">
                <div class="w3-col w3-half">
                    <?php echo form_open(site_url('signup'), array('method' => 'post', 'id' => 'customerSignupForm', 'class' => 'w3-row-padding', 'autocomplete' => 'off')) ?>
                    <?php if ($this->session->flashdata('success_msg')) : ?>
                        <div class="w3-col w3-block highlight unhighlight flash-msg w3-block">
                            <div class="w3-small w3-padding-vertical-8">&nbsp;<span class="w3-text-theme"><i class="fa fa-check">&nbsp;</i><?php echo $this->session->flashdata('success_msg') ?></span></div>
                        </div>
                    <?php endif; ?>
                    <div class="w3-col w3-block">
                        <div class="form-group">
                            <label for="name" class="w3-text-capitalize"><?php echo lang('name') ?> <sup class="w3-text-red w3-text-bold">*</sup></label>
                            <input class="w3-input w3-transparent w3-border w3-border-theme w3-round" id="name_en" name="name_en" value="<?php echo set_value('name_en'); ?>" type="text" />
                            <div class="w3-row has-error w3-tiny"><?php echo form_error('name_en') ?></div>
                        </div>
                    </div>
                    <div class="w3-col w3-half">
                        <div class="form-group">
                            <label for="phone" class="w3-text-capitalize"><?php echo lang('phone') ?> <sup class="w3-text-red w3-text-bold">*</sup></label>
                            <input class="w3-input w3-transparent w3-border w3-border-theme w3-round" id="phone" name="phone" value="<?php echo set_value('phone'); ?>" type="tel" />
                            <div class="w3-row has-error w3-tiny"><?php echo form_error('phone') ?></div>
                        </div>
                    </div>
                    <div class="w3-col w3-half">
                        <div class="form-group">
                            <label for="email" class="w3-text-capitalize"><?php echo lang('email') ?></label>
                            <input class="w3-input w3-transparent w3-border w3-border-theme w3-round" name="email" value="<?php echo set_value('email'); ?>" type="email" />
                            <div class="w3-row has-error w3-tiny"><?php echo form_error('email') ?></div>
                        </div>
                    </div>
                    <div class="w3-col w3-half">
                        <div class="form-group">
                            <label for="password" class="w3-text-capitalize"><?php echo lang('password') ?> <sup class="w3-text-red w3-text-bold">*</sup></label>
                            <input class="w3-input w3-transparent w3-border w3-border-theme w3-round" id="password" name="password" value="<?php echo set_value('password'); ?>" type="password" />
                            <div class="w3-row has-error w3-tiny"><?php echo form_error('password') ?></div>
                        </div>
                    </div>
                    <div class="w3-col w3-half">
                        <div class="form-group">
                            <label for="retype_password" class="w3-text-capitalize"><?php echo sprintf(lang('retype_option'), lang('password'))  ?> <sup class="w3-text-red w3-text-bold">*</sup></label>
                            <input class="w3-input w3-transparent w3-border w3-border-theme w3-round" id="retype_password" name="retype_password" value="<?php echo set_value('retype_password'); ?>" type="password" />
                            <div class="w3-row has-error w3-tiny"><?php echo form_error('retype_password') ?></div>
                        </div>
                    </div>
                    <?php if (is_active_page('termsofuse')) : ?>
                        <div class="w3-col w3-half">
                            <div class="form-group">
                                <label><input name="tos" id="tos" value="1" <?php set_value('tos') == "1" ? print "checked='checked'" : '' ?> data-error="#tos_error" type="checkbox" class="w3-checkbox">&nbsp;<?php echo sprintf(lang('accept_condition'), lang('terms_of_use')) ?><sup class="w3-text-red w3-text-bold">*</sup></label>
                                <a class="w3-text-blue w3-border-bottom w3-border-blue w3-tiny" href="<?php echo site_url('terms-of-use') ?>"><?php echo lang('link') ?></a>
                                <div id="tos_error"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (is_active_page('privacypolicy')) : ?>
                        <div class="w3-col w3-half">
                            <div class="form-group">
                                <label><input name="pvp" id="pvp" value="1" data-error="#pvp_error" type="checkbox" class="w3-checkbox" checked="checked">&nbsp;<?php echo sprintf(lang('accept_condition'), lang('privacy_policy')) ?></label>
                                <a class="w3-text-blue w3-border-bottom w3-border-blue w3-tiny" href="<?php echo site_url('privacy-policy') ?>"><?php echo lang('link') ?></a>
                                <div id="pvp_error"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (is_active_page('cookiepolicy')) : ?>
                        <div class=" w3-col w3-half">
                            <div class="form-group">
                                <label><input name="ckp" id="ckp" value="1" data-error="#ckp_error" type="checkbox" class="w3-checkbox" checked="checked">&nbsp;<?php echo sprintf(lang('accept_condition'), lang('cookie_policy')) ?></label>
                                <a class="w3-text-blue w3-border-bottom w3-border-blue w3-tiny" href="<?php echo site_url('cookie-policy') ?>"><?php echo lang('link') ?></a>
                                <div id="ckp_error"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="w3-col w3-half">
                        <div class="form-group">
                            <label><input name="sep" id="sep" value="1" data-error="#sep_error" type="checkbox" class="w3-checkbox" checked="checked">&nbsp;<?php echo sprintf(lang('accept_condition'), lang('sms_email_promotion')) ?></label>
                            <div id="sep_error"></div>
                        </div>
                    </div>
                    <div class="w3-col m3 s6 w3-section-small">
                        <div class="w3-padding w3-border w3-border-theme w3-round w3-right-align"><?php echo $captcha_val_1 . ' + ' . $captcha_val_2 ?> ?</div>
                    </div>
                    <div class="w3-col m3 s6 w3-section-small">
                        <input class="w3-input w3-border w3-border-theme w3-round w3-transparent" id="captcha_total" name="captcha_total" placeholder="Total" required="required" />
                        <div class="w3-row has-error"><?php echo form_error('captcha_total') ?></div>
                    </div>
                    <div class="w3-col w3-half w3-section-small">
                        <div class="form-group">
                            <button class="w3-button w3-theme-d1 w3-hover-theme w3-border w3-border-theme w3-round w3-block" type="submit"><?php echo lang('signup') ?></button>
                        </div>
                    </div>
                    <div class="w3-col w3-block"><?php echo sprintf(lang('have_option_question'), lang('account')) ?>&nbsp;<a class="w3-text-bold w3-border-bottom" href="<?php echo site_url('login') ?>"><?php echo lang('login') ?></a></div>
                    <?php if ($this->session->flashdata('warning_msg')) : ?>
                        <div class="w3-col w3-block">
                            <div class="form-group">
                                <div class="w3-text-red w3-text-bold w3-padding-vertical-8 w3-text-red flash-msg"><i class="fa fa-warning">&nbsp;</i><?php echo $this->session->flashdata('warning_msg') ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php echo form_close() ?>
                </div>
                <div class="w3-col w3-half">
                    <div class="w3-container"><img style="max-height: 390px;" src="<?php echo UPLOAD_PATH . 'page/' . $page['photo'] ?>" alt="<?php echo $page['subtitle'] ?>" class="w3-image w3-block w3-border-white w3-border w3-round" /></div>
                </div>
            </div>
        </div>
    </main>
</div>

<script type="text/javascript">
    $('document').ready(function() {
        $('#customerSignupForm').validate({
            invalidHandler: function(event, validator) {
                console.log(validator.numberOfInvalids());
            },
            errorClass: "has-error",
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
                name_en: {
                    required: true,
                    alphaonly: true,
                    minlength: 2,
                    maxlength: 50
                },
                phone: {
                    required: true,
                    phone: true,
                    nowhitespace: true
                },
                email: {
                    email: true,
                    nowhitespace: true,
                    maxlength: 100
                },
                password: {
                    required: true,
                    minlength: 8,
                },
                retype_password: {
                    required: true,
                    equalTo: "#password"
                },
                tos: {
                    required: true,
                },
                captcha_total: {
                    required: true,
                }
            },
            messages: {
                name_en: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('name')) ?>",
                    alphaonly: "<?php echo lang('alphaonly_error_msg') ?>",
                    minlength: "<?php echo sprintf(lang('min_error_msg'), 2) ?>",
                    maxlength: "<?php echo sprintf(lang('max_error_msg'), 50) ?>"

                },
                phone: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('mobile_number')) ?>",
                    phone: "<?php echo lang('mobile_error_msg') ?>",
                    nowhitespace: "<?php echo lang('nowhitespace_error_msg') ?>"
                },
                email: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('email')) ?>",
                    email: "<?php echo lang('email_error_msg') ?>",
                    maxlength: "<?php echo sprintf(lang('max_error_msg'), 100) ?>",
                    nowhitespace: "<?php echo lang('nowhitespace_error_msg') ?>"
                },
                password: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('password')) ?>",
                    minlength: "<?php echo sprintf(lang('min_error_msg'), 8) ?>"
                },
                retype_password: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('password')) ?>",
                    minlength: "<?php echo sprintf(lang('min_error_msg'), 8) ?>",
                    equalTo: "<?php echo sprintf(lang('equalto_error_msg'), lang('password')) ?>"
                },
                tos: '<?php echo lang('accept_terms_of_service') ?>',
                captcha_total: "<?php echo sprintf(lang('field_required_msg'), lang('total')) ?>"

            },
            errorPlacement: function(error, element) {
                errorPlacement(error, element);
            }
        });
    });
</script>