<div class="content-area top-mutual" id="contentArea">
    <?php $this->load->view('partials/banner') ?>
    <div class="w3-content w3-section w3-row-padding">
        <div class="w3-col l3 m3 w3-s12 w3-hide-small">&nbsp;</div>
        <div class="w3-col l6 m6 s12">
            <div class="w3-white w3-round">
                <div class="w3-large w3-border-bottom w3-border-theme w3-theme">
                    <div class="w3-large w3-padding-small"><?php echo lang('recovery') . ' ' . lang('code') ?></div>
                </div>
                <?php echo form_open(site_url('login/recoverycode'), array('method' => 'post', 'id' => 'customerverificationForm', 'class' => 'w3-container', 'autocomplete' => 'off')) ?>
                <div class="form-group">
                    <label class="w3-text-capitalize" for="code"><?php echo $this->lang->line('recovery_code') ?></label>
                    <input id="verification_code" class="w3-input w3-border w3-border-gray w3-round" autocomplete="off" name="verification_code" value='<?php echo set_value('verification_code') ?>' type="text" />
                    <div class="w3-row has-error"><?php echo form_error('verification_code') ?></div>
                </div>
                <div class="w3-row w3-section form-group">
                    <div class="w3-col s6 w3-left-align">
                        <button class="w3-button w3-border-0 w3-round w3-theme w3-hover-theme" type="submit"><?php echo $this->lang->line('submit') ?></button>
                    </div>
                    <div class="w3-col s6 w3-right-align">
                        <a class="w3-button w3-round w3-gray-light w3-hover-none" href="<?php echo site_url('login/recoverycode/' . $customer_id) ?>"><?php echo $this->lang->line('resend_code') ?></a>
                    </div>
                </div>
                <?php if ($this->session->flashdata('warning_msg')) : ?>
                    <div class="w3-row w3-section flash-msg"><span class="w3-text-red"><?php $this->session->flashdata('warning_msg') ? print '<i class="fa fa-bell">&nbsp;</i>' . $this->session->flashdata('warning_msg') : print '' ?></span></div>
                <?php endif; ?>
                <?php echo form_close() ?>
            </div>
        </div>
        <div class="w3-col l3 m3 w3-s12 w3-hide-small">&nbsp;</div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#customerverificationForm').validate({
            errorClass: "has-error",
            highlight: function(e) {
                $(e).closest('.form-group').addClass('has-error');
            },
            unhighlight: function(e) {
                $(e).closest('.form-group').removeClass('has-error');
            },
            rules: {
                verification_code: {
                    required: true
                }
            },
            messages: {
                verification_code: {
                    required: "<?php echo sprintf($this->lang->line('field_required_msg'), $this->lang->line('recovery_code')) ?>"
                }
            },
            errorPlacement: function(error, element) {
                errorPlacement(error, element);
            }
        });
    });
</script>