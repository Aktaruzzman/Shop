<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo $page_title ?></h2>
                <h4><?php echo $page_subtitle ?></h4>
            </div>
            <div class="w3-row-padding">
                <div class="w3-col w3-half w3-hide-small">
                    <img style="max-height: 450px;" src="<?php echo UPLOAD_PATH . 'page/' . $page['photo'] ?>" alt="<?php echo $page['subtitle'] ?>" class="w3-image w3-block w3-border-white w3-border w3-round" />
                </div>
                <div class="w3-col w3-half w3-padding-48">
                    <?php if (!empty($page['description'])) : ?>
                        <div class="w3-col w3-block w3-margin-bottom">
                            <p><?php echo $page['description'] ?></p>
                        </div>
                    <?php endif; ?>
                    <?php echo form_open(site_url('login'), array('method' => 'post', 'id' => 'customerLoginForm', 'class' => 'w3-row', 'autocomplete' => 'off')) ?>
                    <?php if ($this->session->flashdata('success_msg')) : ?>
                        <div class="w3-col w3-block highlight unhighlight flash-msg ">
                            <div class="w3-small w3-padding-vertical-8">&nbsp;<span class="w3-text-theme"><i class="fa fa-check">&nbsp;</i><?php echo $this->session->flashdata('success_msg') ?></span></div>
                        </div>
                    <?php endif; ?>
                    <div class="w3-col w3-block">
                        <div class="form-group">
                            <label class="w3-text-capitalize"><?php echo lang('phone') ?> / <?php echo lang('email') ?></label>
                            <input id="username" class="w3-input w3-transparent w3-border w3-border-theme w3-round" autocomplete="off" name="username" value='<?php echo $this->session->userdata('username') ?>' type="text" required="required" />
                            <div class="w3-row has-error flash-msg"><?php echo form_error('username') ?></div>
                        </div>
                    </div>
                    <div class="w3-col w3-block">
                        <div class="form-group">
                            <label class="w3-text-capitalize"><?php echo lang('password') ?></label>
                            <input id="password" class="w3-input w3-transparent w3-border w3-border-theme w3-round" autocomplete="off" name="password" value='<?php echo set_value('password') ?>' type="password" required="required" />
                            <div class="w3-row has-error flash-msg"><?php echo form_error('username') ?></div>
                        </div>
                    </div>
                    <div class="w3-col w3-block w3-section-small">
                        <div class="form-group">
                            <button class="w3-button w3-theme-d1 w3-hover-theme w3-border w3-border-theme w3-round w3-text-bold-500" type="submit"><?php echo lang('login') ?></button>
                        </div>
                    </div>
                    <?php if ($this->session->flashdata('warning_msg')) : ?>
                        <div class="w3-col w3-block">
                            <div class="form-group">
                                <div class="w3-text-red w3-padding-vertical-8 flash-msg"><i class="fa fa-warning">&nbsp;</i><?php echo $this->session->flashdata('warning_msg') ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="w3-col w3-half">
                        <div class="form-group w3-left-align">
                            <a class="w3-text-bold-500 w3-border-bottom" href="<?php echo site_url('forgot-password') ?>"><?php echo sprintf(lang('forgot_option'), lang('password')) ?></a>
                        </div>
                    </div>
                    <div class="w3-col w3-half">
                        <div class="form-group shift-align-to-left w3-right-align">
                            <?php echo sprintf(lang('have_no_option'), lang('account')) ?> <a class="w3-text-bold-500 w3-border-bottom" href="<?php echo site_url('signup') ?>"> <?php echo lang('signup') ?> </a>
                        </div>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script type="text/javascript">
    $('document').ready(function() {
        $('#customerLoginForm').validate({
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
                password: {
                    required: true
                }
            },
            messages: {
                username: "<?php echo sprintf(lang('field_required_msg'), lang('phone') . '/' . lang('email')) ?>",
                password: "<?php echo sprintf(lang('field_required_msg'), lang('password')) ?>"
            },
            errorPlacement: function(error, element) {
                errorPlacement(error, element);
            }
        });
    });
</script>