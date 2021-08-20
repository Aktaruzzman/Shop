<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo lang('forgotten_password') ?></h2>
            </div>
            <div class="w3-row">
                <div class="w3-col w3-half w3-hide-small">
                    <img style="max-height:400px" src="<?php echo UPLOAD_PATH . 'page/' . $page['photo'] ?>" alt="<?php echo $page['subtitle'] ?>" class="w3-image w3-block w3-border-white w3-border w3-round" />
                </div>
                <div class="w3-col w3-half">
                    <?php echo form_open(site_url('forgot-password'), array('method' => 'post', 'id' => 'forgotPasswordForm', 'class' => 'w3-row-padding w3-padding-64', 'autocomplete' => 'off')) ?>
                    <article class="w3-col w3-block w3-padding-16"><?php echo lang('password_reset_code') ?></article>
                    <?php if ($this->session->flashdata('success_msg')) : ?>
                        <div class="w3-col w3-block highlight unhighlight flash-msg">
                            <div class="w3-small w3-padding-vertical-8">&nbsp;<span class="w3-text-theme"><i class="fa fa-check">&nbsp;</i><?php echo $this->session->flashdata('success_msg') ?></span></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($otp_code)) : ?>
                        <div class="w3-col w3-block">
                            <div class="form-group">
                                <label class="w3-text-capitalize"><?php echo sprintf(lang('supply_option'), lang('code')) ?> </label>
                                <input id="otp_code" class="w3-input w3-transparent w3-border w3-border-theme w3-round" autocomplete="off" name="otp_code" type="password" required="required" />
                                <div class="w3-row has-error flash-msg"><?php echo form_error('otp_code') ?></div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="w3-col w3-block">
                            <div class="form-group">
                                <label class="w3-text-capitalize"><?php echo lang('your') ?> <?php echo lang('phone') ?> / <?php echo lang('email') ?></label>
                                <input id="username" class="w3-input w3-transparent w3-border w3-border-theme w3-round" autocomplete="off" name="username" value='<?php echo $this->session->userdata('username') ?>' type="text" required="required" />
                                <div class="w3-row has-error flash-msg"><?php echo form_error('username') ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="w3-col w3-block">
                        <div class="form-group">
                            <button class="w3-button w3-theme-d1 w3-hover-theme w3-border w3-border-theme w3-round" type="submit">
                                <?php if (!empty($otp_code)) : ?>
                                    <?php echo sprintf(lang('send_option'), lang('code')) ?>
                                <?php else : ?>
                                    <?php echo lang('submit') ?>
                                <?php endif; ?>
                            </button>
                            <?php if (!empty($otp_code)) : ?>
                                <div class="w3-right w3-right-align">
                                    <a href="<?php echo site_url('site/send_otp') ?>" class="w3-right-align"><?php echo sprintf(lang('resend_option'), lang('code')) ?></a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($this->session->flashdata('warning_msg')) : ?>
                        <div class="w3-col w3-half">
                            <div class="form-group">
                                <div class="w3-text-red w3-padding-vertical-8 flash-msg"><i class="fa fa-warning">&nbsp;</i><?php echo $this->session->flashdata('warning_msg') ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script type="text/javascript">
    $('document').ready(function() {
        $('#forgotPasswordForm').validate({
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
                username: {
                    required: true
                },
            },
            messages: {
                username: "<?php echo lang('your') . ' ' . sprintf(lang('field_required_msg'), lang('phone') . ' / ' . lang('email')) ?>"
            },
            errorPlacement: function(error, element) {
                errorPlacement(error, element);
            }
        });
    });
</script>