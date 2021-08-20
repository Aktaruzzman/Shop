<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo $page_title ?></h2>
                <h4><?php echo $page_subtitle ?></h4>
                <article class="w3-left-align"><?php echo $page['description'] ?></article>
            </div>
            <div class="w3-row-padding w3-mobile">
                <div class="w3-col w3-quarter w3-margin-bottom">
                    <?php $this->load->view('customer/sidebar') ?>
                </div>
                <div class="w3-col w3-threequarter w3-margin-bottom">
                    <?php echo form_open_multipart(site_url('customer/profile'), array('method' => 'post', 'id' => 'customerProfileUpdate', 'class' => 'w3-white w3-border w3-border-light-gray w3-round w3-mobile', 'autocomplete' => 'off')) ?>
                    <div class="w3-large w3-text-upper w3-text-bold w3-text-theme w3-border-bottom w3-padding w3-border-light-gray"><?php echo sprintf(lang('my_option'), lang('info')) ?></div>
                    <div class="w3-section w3-container">
                        <?php if ($lang === 'bn') : ?>
                            <div class="form-group">
                                <label for="name_en" class="w3-text-capitalize"><?php echo lang('name') ?> (<?php echo lang('english') ?>)</label>
                                <input class="w3-input w3-border w3-border-theme w3-round w3-text-capitalize" id="name_en" name="name_en" value="<?php echo $my['name']['en']; ?>" type="text" />
                                <div class="w3-row has-error"><?php echo form_error('name_en') ?></div>
                            </div>
                            <div class="form-group">
                                <label for="name" class="w3-text-capitalize"><?php echo lang('name') ?> (<?php echo lang('bangla') ?>)</label>
                                <input class="w3-input w3-border w3-border-theme w3-round w3-text-capitalize" id="name_bn" name="name_bn" value="<?php echo $my['name']['bn']; ?>" type="text" />
                                <div class="w3-row has-error"><?php echo form_error('name_bn') ?></div>
                            </div>
                        <?php else : ?>
                            <div class="form-group">
                                <label for="name_en" class="w3-text-capitalize"><?php echo lang('name') ?></label>
                                <input class="w3-input w3-border w3-border-theme w3-round w3-text-capitalize" id="name_en" name="name_en" value="<?php echo $my['name']['en']; ?>" type="text" />
                                <div class="w3-row has-error"><?php echo form_error('name_en') ?></div>
                                <input type="hidden" name="name_bn" value="<?php echo $my['name']['bn']; ?>">
                            </div>
                        <?php endif ?>
                        <div class="form-group">
                            <label for="phone" class="w3-text-capitalize"><?php echo lang('mobile_number') ?> <?php !trim($my['phone']) ? print "<span class='w3-red w3-tag'>" . lang('urgent') . "</span>" : '' ?></label>
                            <input class="w3-input w3-border w3-round w3-border-theme" id="phone" name="phone" value="<?php echo $my['phone']; ?>" type="text" <?php echo $my['phone'] ? "disabled" : ""; ?> />
                            <div class="w3-row has-error"><?php echo form_error('phone') ?></div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="w3-text-capitalize"><?php echo lang('email') ?> <?php !trim($my['email']) ? print "<span class='w3-red w3-tag'>" . lang('urgent') . "</span>" : '' ?></label>
                            <input class="w3-input w3-border w3-round w3-border-theme" name="email" value="<?php echo $my['email']; ?>" type="email" <?php echo $my['email'] ? "disabled" : ""; ?> />
                            <div class="w3-row has-error"><?php echo form_error('email') ?></div>
                        </div>
                        <div class="form-group">
                            <label for="photo"><?php echo lang('profile_photo') ?></label><br />
                            <div class="w3-round">
                                <img src="<?php $my['photo'] ? print UPLOAD_PATH . 'customer/' . $my['photo'] : print ASSET_PATH . 'img/user.png' ?>" class="w3-image" style="width: 100px; height: 100px; " />
                                <br /><b>(100 PX <i class="fa fa-close"></i> 100 PX)</b>
                            </div>
                            <input type="file" id="photo" name="photo" accept="image/png,image/jpeg image/gif,image/jpg">
                        </div>
                        <?php if ($this->session->flashdata('warning_msg')) : ?>
                            <div class="w3-text-red"><?php echo $this->session->flashdata('warning_msg') ?></div>
                        <?php endif; ?>
                        <div class="w3-margin-top form-group">
                            <button class="w3-button w3-theme-d1 w3-round w3-hover-theme" onclick="customerProfileUpdatePrecheck()" type="submit"><?php echo lang('update') ?></button>
                        </div>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </main>
</div>



<script type="text/javascript">
    function customerProfileUpdatePrecheck() {
        $('#customerProfileUpdate').validate({
            errorClass: "has-error",
            highlight: function(e) {
                $(e).closest('.form-group').addClass('has-error');
            },
            unhighlight: function(e) {
                $(e).closest('.form-group').removeClass('has-error');
            },
            rules: {
                name_en: {
                    required: true,
                    alphaonly: true,
                    minlength: 2,
                    maxlength: 50
                },
                name_bn: {
                    required: true,
                    minlength: 2,
                    maxlength: 50
                },
            },
            messages: {
                name_en: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('name')) ?>",
                    alphaonly: "<?php echo lang('alphaonly_error_msg') ?>",
                    minlength: "<?php echo sprintf(lang('min_error_msg'), 2) ?>",
                    maxlength: "<?php echo sprintf(lang('max_error_msg'), 50) ?>"
                },
                name_bn: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('name')) ?>",
                    minlength: "<?php echo sprintf(lang('min_error_msg'), 2) ?>",
                    maxlength: "<?php echo sprintf(lang('max_error_msg'), 50) ?>"
                },
            },
            errorPlacement: function(error, element) {
                errorPlacement(error, element);
            }
        });
    }
</script>