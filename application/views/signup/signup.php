<div class="content-area top-mutual" id="contentArea">
    <?php echo $this->load->view('partials/banner') ?>
    <div class="w3-content w3-container">
        <!--open it page description is required-->
        <!--
        <?php if (!empty($page['description'])) : ?>
            <article class="w3-row w3-section w3-center"><?php echo $page['description'] ?></article>
        <?php endif; ?>
        -->
        <div class="w3-row w3-section">
            <div class="w3-col l3 m3 w3-s12 w3-hide-small">&nbsp;</div>
            <div class="w3-col l6 m6 w3-s12">
                <div class="w3-white w3-round">
                    <div class="w3-large w3-border-bottom w3-border-theme w3-theme">
                        <div class="w3-text-bold w3-padding-small"><?php echo lang('signup') ?></div>
                    </div>
                    <?php if ($this->session->flashdata('warning_msg')) : ?>
                        <div class="w3-container highlight unhighlight">
                            <div class="w3-small w3-padding-vertical-8">&nbsp;<span class="w3-text-red"><i class="fa fa-warning">&nbsp;</i><?php echo $this->session->flashdata('warning_msg') ?></span></div>
                        </div>
                    <?php endif; ?>
                    <?php echo form_open(site_url('signup'), array('method' => 'post', 'id' => 'customerSignupForm', 'class' => 'w3-container', 'autocomplete' => 'off')) ?>
                    <div class="form-group">
                        <label for="name" class="w3-text-capitalize"><?php echo lang('name') ?></label>
                        <input class="w3-input w3-border w3-border-gray w3-round" id="name_en" name="name_en" value="<?php echo set_value('name_en'); ?>" type="text" required="required" minlength="2" maxlength="50" alphaonly="alphaonly" />
                        <div class="w3-row has-error w3-tiny"><?php echo form_error('name_en') ?></div>
                    </div>
                    <div class="form-group">
                        <label for="phone" class="w3-text-capitalize"><?php echo lang('mobile_number') ?></label>
                        <input class="w3-input w3-border w3-border-gray w3-round" id="phone" name="phone" value="<?php echo set_value('phone'); ?>" type="tel" required="required" phone="phone" nowhitespace="nowhitespace" />
                        <div class="w3-row has-error w3-tiny"><?php echo form_error('phone') ?></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="w3-text-capitalize"><?php echo lang('email') ?></label>
                        <input class="w3-input w3-border w3-border-gray w3-round" name="email" value="<?php echo set_value('email'); ?>" type="email" required="required" minlength="7" maxlength="100" email="email" nowhitespace="nowhitespace" />
                        <div class="w3-row has-error w3-tiny"><?php echo form_error('email') ?></div>
                    </div>
                    <div class="form-group">
                        <label for="password" class="w3-text-capitalize"><?php echo lang('password') ?></label>
                        <input class="w3-input w3-border w3-border-gray w3-round" id="password" name="password" value="<?php echo set_value('password'); ?>" type="password" required="required" minlength="8" />
                        <div class="w3-row has-error w3-tiny"><?php echo form_error('password') ?></div>
                    </div>
                    <div class="form-group">
                        <label for="retype_password" class="w3-text-capitalize"><?php echo lang('retype') . ' ' . lang('password') ?></label>
                        <input class="w3-input w3-border w3-border-gray w3-round" id="retype_password" name="retype_password" value="<?php echo set_value('retype_password'); ?>" type="password" required="required" minlength="8" />
                        <div class="w3-row has-error w3-tiny"><?php echo form_error('retype_password') ?></div>
                    </div>
                    <div class="w3-row w3-section form-group">
                        <div class="w3-col s4 w3-left-align">
                            <button class="w3-button w3-theme-d1 w3-hover-theme w3-round" type="submit"><span class="spinning" style="display: none"><img src="<?php echo ASSET_PATH . "img/ajax-loader.gif" ?>" class="w3-image w3-circle"></span> <?php echo lang('signup') ?></button>
                        </div>
                        <div class="w3-col s8 w3-right-align">
                            <div class="w3-padding-vertical-8 w3-small"><?php echo lang('already_have_an_account') ?>&nbsp;<a class="w3-text-bold w3-text-theme" href="<?php echo site_url('login') ?>"><?php echo lang('login') ?></a></div>
                        </div>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
            <div class="w3-col l3 m3 w3-s12 w3-hide-small">&nbsp;</div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('document').ready(function() {
        $('#customerSignupForm').validate({
            invalidHandler: function(event, validator) {
                var errors = validator.numberOfInvalids();
                errors ? $('.spinning').show() : $('.spinning').hide();
            },
            errorClass: "has-error w3-small",
            highlight: function(e) {
                $(e).closest('.form-group').addClass('has-error w3-small');
                $('.spinning').show()
                $('.unhighlight').html('');
            },
            unhighlight: function(e) {
                $(e).closest('.form-group').removeClass('has-error w3-small');
                $('.spinning').hide();
                $('.unhighlight').html('');
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
                    required: true,
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
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('retype') . ' ' . lang('password')) ?>",
                    minlength: "<?php echo sprintf(lang('min_error_msg'), 8) ?>",
                    equalTo: "<?php echo lang('equalto_error_msg') ?>"
                }
            },
            errorPlacement: function(error, element) {
                errorPlacement(error, element);
            }
        });
    })
</script>