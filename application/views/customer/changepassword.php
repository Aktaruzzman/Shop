<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo $page_title ?></h2>
                <h4><?php echo $page_subtitle ?></h4>
            </div>
            <div class="w3-row-padding w3-mobile">
                <div class="w3-col w3-quarter w3-margin-bottom">
                    <?php $this->load->view('customer/sidebar') ?>
                </div>
                <div class="w3-col w3-threequarter w3-margin-bottom">
                    <?php echo form_open(site_url('customer/changepassword'), array('method' => 'post', 'id' => 'customerPasswordChangeForm', 'class' => 'w3-white w3-border w3-border-light-gray w3-round w3-mobile', 'autocomplete' => 'off')) ?>
                    <div class="w3-large w3-text-upper w3-text-bold w3-text-theme w3-border-bottom w3-padding w3-border-light-gray"><?php echo lang('password') ?> <?php echo lang('change') ?></div>
                    <div class="w3-section w3-container">
                        <div class="form-group">
                            <label for="password" class="w3-text-capitalize"><?php echo $this->lang->line('new') ?> <?php echo $this->lang->line('password') ?></label>
                            <input class="w3-input w3-border w3-round w3-border-theme" id="password" name="password" value="<?php echo set_value('password'); ?>" type="password" />
                            <div class="w3-row has-error"><?php echo form_error('password') ?></div>
                        </div>
                        <div class="form-group">
                            <label for="retype_password" class="w3-text-capitalize"><?php echo $this->lang->line('retype') . ' ' . $this->lang->line('password') ?></label>
                            <input class="w3-input w3-border w3-round w3-border-theme" id="retype_password" name="retype_password" value="<?php echo set_value('retype_password'); ?>" type="password" />
                            <div class="w3-row has-error"><?php echo form_error('retype_password') ?></div>
                        </div>
                        <?php if ($this->session->flashdata('warning_msg')) : ?>
                            <div class="w3-section w3-text-theme"><?php echo $this->session->flashdata('warning_msg') ?></div>
                        <?php endif; ?>
                        <div class="w3-margin-top form-group ">
                            <button class="w3-button w3-theme-d1 w3-round w3-hover-theme" type="submit"><?php echo $this->lang->line('update') ?></button>
                        </div>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </main>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        $('#customerPasswordChangeForm').validate({
            errorClass: "has-error",
            highlight: function(e) {
                $(e).closest('.form-group').addClass('has-error');
            },
            unhighlight: function(e) {
                $(e).closest('.form-group').removeClass('has-error');
            },
            rules: {
                password: {
                    required: true,
                    minlength: 8
                },
                retype_password: {
                    required: true,
                    minlength: 8,
                    equalTo: "#password"
                }
            },
            messages: {
                password: {
                    required: "<?php echo sprintf($this->lang->line('field_required_msg'), $this->lang->line('password')) ?>",
                    minlength: "<?php echo sprintf($this->lang->line('min_error_msg'), 8) ?>"
                },
                retype_password: {
                    required: "<?php echo sprintf($this->lang->line('field_required_msg'), $this->lang->line('retype') . ' ' . $this->lang->line('password')) ?>",
                    minlength: "<?php echo sprintf($this->lang->line('min_error_msg'), 8) ?>",
                    equalTo: "<?php echo $this->lang->line('equalto_error_msg') ?>"
                }
            },
            errorPlacement: function(error, element) {
                errorPlacement(error, element);
            }
        });
    });
</script>