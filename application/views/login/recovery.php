<div class="content-area top-mutual" id="contentArea">
    <?php $this->load->view('partials/banner'); ?>
    <div class="w3-content w3-section w3-row-padding">
        <div class="w3-col l3 m4 w3-s12 w3-hide-small">&nbsp;</div>
        <div class="w3-col l6 m6 s12">
            <div class="w3-white w3-round">
                <div class="w3-large w3-border-bottom w3-border-theme w3-theme">
                    <div class="w3-text-bold w3-padding-small"><?php echo lang('recovery') ?></div>
                </div>
                <?php echo form_open(site_url('login/recovery'), array('method' => 'post', 'id' => 'recoveryDeviceAddressForm', 'class' => 'w3-container', 'autocomplete' => 'off')) ?>
                <div class="form-group w3-section">
                    <label class="w3-text-capitalize w3-text-theme"><?php echo lang('phone') ?> / <?php echo lang('email') ?></label>
                    <input id="email" class="w3-input w3-border w3-border-gray w3-round" autocomplete="off" name="username" value='<?php echo set_value('username') ?>' type="text" required="required" />
                    <div class="w3-row has-error"><?php echo form_error('username') ?></div>
                </div>
                <div class="w3-row form-group w3-section">
                    <div class="w3-col l3 m3 s4 w3-left-align">
                        <button class="w3-button w3-round w3-theme w3-hover-theme" type="submit"><?php echo lang('submit') ?></button>
                    </div>
                    <div class="w3-col l9 m9 s8 w3-right-align">
                        <div class="w3-padding-vertical-8 w3-text-red unhighlight flash-msg"><?php $this->session->flashdata('warning_msg') ? print '<i class="fa fa-bell">&nbsp;</i>' . $this->session->flashdata('warning_msg') : print '' ?></div>
                    </div>
                </div>
                <?php echo form_close() ?>
            </div>
        </div>
        <div class="w3-col l3 m3 w3-s12 w3-hide-small">&nbsp;</div>
    </div>
</div>

<script type="text/javascript">
    $('document').ready(function() {
        $('#recoveryDeviceAddressForm').validate({
            errorClass: "has-error",
            highlight: function(e) {
                $(e).closest('.form-group').addClass('has-error');
            },
            unhighlight: function(e) {
                $(e).closest('.form-group').removeClass('has-error');
                $('.unhighlight').text('');
            },
            rules: {
                username: {
                    required: true
                }
            },
            messages: {
                username: {
                    required: "<?php echo sprintf(lang('field_required_msg'),  lang('recovery') . ' ' . lang('phone') . '/' . lang('email')) ?>",

                }
            },
            errorPlacement: function(error, element) {
                errorPlacement(error, element);
            }
        });
    });
</script>