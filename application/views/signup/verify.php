<div class="content-area top-mutual" id="contentArea">
    <?php $this->load->view('partials/banner') ?>
    <div class="w3-content w3-section">
        <div class="w3-row w3-container">
            <div class="w3-col l3 m3 w3-s12 w3-hide-small">&nbsp;</div>
            <div class="w3-col l6 m6 s12">
                <div class="w3-theme-l5 w3-round ">
                    <div class="w3-theme w3-center w3-border-bottom">
                        <div class="w3-large w3-padding"><?php echo lang('verification_code') ?></div>
                    </div>
                    <?php echo form_open(site_url('signup/verify'), array('method' => 'post', 'id' => 'customerverificationForm', 'class' => 'w3-container', 'autocomplete' => 'off')) ?>
                    <?php if ($this->session->flashdata('success_msg')) : ?><div class="w3-small w3-text-theme"><i class="fa fa-bell">&nbsp;</i><?php echo $this->session->flashdata('success_msg') ?></div><?php endif; ?>
                    <div class="form-group w3-section">
                        <input id="verification_code" class="w3-input w3-border w3-border-theme w3-round" autocomplete="off" name="verification_code" value='<?php echo set_value('verification_code') ?>' type="text" />
                        <div class="w3-row has-error"><?php echo form_error('verification_code') ?></div>
                    </div>
                    <div class="w3-row w3-section form-group">
                        <div class="w3-col s6 w3-left-align">
                            <button class="w3-button w3-border-0 w3-round w3-theme-dark w3-hover-theme" type="submit"><?php echo $this->lang->line('submit') ?></button>
                        </div>
                        <div class="w3-col s6 w3-right-align">
                            <a class="w3-button w3-round w3-theme-l4 w3-hover-theme" href="<?php echo site_url('signup/verify/' . $id) ?>"><?php echo $this->lang->line('resend_code') ?></a>
                        </div>
                    </div>
                    <?php if ($warning_msg) : ?>
                        <div class="w3-row w3-section"><span class="w3-text-red"><?php echo $warning_msg ?></span></div>
                    <?php endif; ?>
                    <?php echo form_close() ?>
                </div>
            </div>
            <div class="w3-col l3 m3 w3-s12 w3-hide-small">&nbsp;</div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('document').ready(function() {
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
                    required: "<?php echo sprintf($this->lang->line('field_required_msg'), $this->lang->line('verification_code')) ?>"
                }
            },
            errorPlacement: function(error, element) {
                errorPlacement(error, element);
            }
        });
    });
</script>