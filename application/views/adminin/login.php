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
                <div class="w3-theme-light w3-round w3-border-theme w3-border">
                    <div class="w3-xlarge w3-border-bottom w3-border-theme w3-text-theme">
                        <div class="w3-text-bold my-font w3-container"><?php echo lang('admin') ?> <?php echo lang('login') ?></div>
                    </div>
                    <?php echo form_open(site_url('adminin'), array('method' => 'post', 'id' => 'customerLoginForm', 'class' => 'w3-container', 'autocomplete' => 'off')) ?>
                    <?php if ($this->session->flashdata('success_msg')) : ?>
                        <div class="highlight unhighlight">
                            <div class="w3-small w3-padding-vertical-8">&nbsp;<span class="w3-text-theme"><i class="fa fa-check">&nbsp;</i><?php echo $this->session->flashdata('success_msg') ?></span></div>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="w3-text-capitalize"><?php echo $this->lang->line('mobile_number') ?> / <?php echo $this->lang->line('email') ?></label>
                        <input id="username" class="w3-input w3-border w3-border-theme w3-hover-border-theme w3-round" autocomplete="off" name="username" value='<?php echo set_value('username') ?>' type="text" required="required" />
                        <div class="w3-row has-error"><?php echo form_error('username') ?></div>
                    </div>
                    <div class="form-group">
                        <label class="w3-text-capitalize"><?php echo $this->lang->line('password') ?></label>
                        <input id="password" class="w3-input w3-border w3-border-theme w3-hover-border-theme w3-round" autocomplete="off" name="password" value='<?php echo '' ?>' type="password" required="required">
                        <div class="w3-row has-error"><?php echo form_error('password') ?></div>
                    </div>
                    <div class="w3-row form-group w3-section">
                        <div class="w3-col m3 s4 w3-left-align">
                            <button class="w3-button w3-round w3-theme-dark w3-hover-theme" type="submit"><span class="spinning" style="display: none"><img src="<?php echo ASSET_PATH . "img/ajax-loader.gif" ?>" class="w3-image w3-circle"></span> <?php echo lang('login') ?></button>
                        </div>
                        <div class="w3-col m9 s8 w3-right-align w3-small">
                            <div class="w3-text-red w3-padding-vertical-16 highlight unhighlight"><?php $this->session->flashdata('warning_msg') ? print '<i class="fa fa-warning">&nbsp;</i>' . $this->session->flashdata('warning_msg') : print '' ?></div>
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
        $('#customerLoginForm').validate({
            invalidHandler: function(event, validator) {
                var errors = validator.numberOfInvalids();
                errors > 0 ? $('.spinning').show() : $('.spinning').hide();
            },
            errorClass: "has-error w3-small",
            highlight: function(e) {
                $(e).closest('.form-group').addClass('has-error w3-small');
                $('.spinning').show();
                $('.unhighlight').text('');
            },
            unhighlight: function(e) {
                $(e).closest('.form-group').removeClass('has-error w3-small');
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
                username: "<?php echo sprintf($this->lang->line('field_required_msg'), $this->lang->line('mobile_number') . '/' . $this->lang->line('email')) ?>",
                password: "<?php echo sprintf($this->lang->line('field_required_msg'), $this->lang->line('password')) ?>"
            },
            errorPlacement: function(error, element) {
                errorPlacement(error, element);
            }
        });
    });
</script>