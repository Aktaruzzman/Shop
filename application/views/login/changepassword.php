<div class="content-area top-mutual" id="contentArea">
    <?php $this->load->view('partials/banner') ?>
    <section class="w3-section w3-content">
        <div class="w3-row-padding">
            <div class="w3-col m3 w3-s12 w3-hide-small">&nbsp;</div>
            <div class="w3-col m6 w3-s12">
                <div class="w3-white w3-round">
                    <div class="w3-large w3-border-bottom w3-border-theme w3-theme">
                        <div class="w3-large w3-padding-small"><?php echo lang('change_password') ?></div>
                    </div>
                    <?php echo form_open(site_url('login/changepassword'), array('method' => 'post', 'id' => 'customerPasswordChangeForm', 'class' => 'w3-container', 'autocomplete' => 'off')) ?>
                    <div class="form-group">
                        <label for="password" class="w3-text-capitalize w3-text-theme"><?php echo $this->lang->line('password') ?></label>
                        <input class="w3-input w3-border w3-border-theme w3-round" id="password" name="password" value="<?php echo set_value('password'); ?>" type="password" />
                        <div class="w3-row has-error"><?php echo form_error('password') ?></div>
                    </div>
                    <div class="form-group">
                        <label for="retype_password" class="w3-text-capitalize w3-text-theme"><?php echo $this->lang->line('confirmed') . ' ' . $this->lang->line('password') ?></label>
                        <input class="w3-input w3-border w3-border-theme w3-round" id="retype_password" name="retype_password" value="<?php echo set_value('retype_password'); ?>" type="password" />
                        <div class="w3-row has-error"><?php echo form_error('retype_password') ?></div>
                    </div>
                    <?php if ($this->session->flashdata('warning_msg')) : ?>
                        <div class="w3-section flash-msg"><?php echo $this->session->flashdata('warning_msg') ?></div>
                    <?php endif; ?>
                    <div class="w3-section form-group">
                        <button class="w3-button w3-border-0 w3-round w3-font-size-17 w3-theme-d1 w3-hover-theme" type="submit"><?php echo $this->lang->line('change') ?></button>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
            <div class="w3-col m3 w3-s12 w3-hide-small">&nbsp;</div>
        </div>
    </section>
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