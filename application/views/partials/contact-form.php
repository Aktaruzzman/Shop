<?php echo form_open(site_url('contact'), array('method' => 'post', 'id' => 'customerContactForm', 'class' => 'w3-row-padding', 'autocomplete' => 'off')) ?>
<div class="w3-col w3-block">
    <div class="form-group">
        <label for="name" class="w3-text-capitalize"><?php echo lang('your') . ' ' . lang('name') ?></label>
        <?php if (!empty($my['name'][$lang])) : ?>
            <input class="w3-input w3-border w3-border-theme w3-round w3-transparent" id="from_name" name="from_name" value="<?php echo $my['name']['en']; ?>" type="text" readonly="readonly" />
        <?php else : ?>
            <input class="w3-input w3-border w3-border-theme w3-round w3-transparent" id="from_name" name="from_name" value="<?php echo set_value('name'); ?>" type="text" required="required" minlength="2" maxlength="50" alphaonly="alphaonly" />
        <?php endif; ?>
        <div class="w3-row has-error"><?php echo form_error('name') ?></div>
    </div>
</div>
<div class="w3-col w3-half">
    <div class="form-group">
        <label for="phone" class="w3-text-capitalize"><?php echo lang('phone') ?></label>
        <?php if (!empty($my['phone'])) : ?>
            <input class="w3-input w3-border w3-border-theme w3-round w3-transparent" id="from_phone" name="from_phone" value="<?php echo $my['phone']; ?>" type="tel" readonly="readonly" />
        <?php else : ?>
            <input class="w3-input w3-border w3-border-theme w3-round w3-transparent" id="from_phone" name="from_phone" value="<?php echo set_value('phone'); ?>" type="tel" required="required" phone="phone" nowhitespace="nowhitespace" />
        <?php endif; ?>
        <div class="w3-row has-error"><?php echo form_error('phone') ?></div>
    </div>
</div>
<div class="w3-col w3-half">
    <div class="form-group">
        <label for="email" class="w3-text-capitalize"><?php echo lang('email') ?></label>
        <?php if (!empty($my['email'])) : ?>
            <input class="w3-input w3-border w3-border-theme w3-round w3-transparent" id="from_email" name="from_email" value="<?php echo $my['email']; ?>" readonly="readonly" />
        <?php else : ?>
            <input class="w3-input w3-border w3-border-theme w3-round w3-transparent" id="from_email" name="from_email" value="<?php echo set_value('email'); ?>" type="email" required="required" minlength="7" maxlength="100" email="email" nowhitespace="nowhitespace" />
        <?php endif; ?>
        <div class="w3-row has-error"><?php echo form_error('email') ?></div>
    </div>
</div>
<div class="w3-col w3-block">
    <div class="form-group">
        <label for="message" class="w3-text-capitalize"><?php echo lang('message') ?> / <?php echo lang('query') ?></label>
        <textarea name="message" id="message" class="w3-input w3-border w3-border-theme w3-round w3-transparent" rows="9" required="required" minlength="20" maxlength="100"><?php echo set_value('message'); ?></textarea>
        <div class="w3-row has-error"><?php echo form_error('message') ?></div>
    </div>
</div>
<div class="w3-col m3 s6 w3-section-small">
    <div class="w3-padding w3-border w3-border-theme w3-round w3-right-align"><?php echo $captcha_val_1 . ' + ' . $captcha_val_2 ?> ?</div>
</div>
<div class="w3-col m3 s6 w3-section-small">
    <input class="w3-input w3-border w3-border-theme w3-round w3-transparent" id="captcha_total" name="captcha_total" placeholder="Total" required="required" />
    <div class="w3-row has-error"><?php echo form_error('captcha_total') ?></div>
</div>
<div class="w3-col w3-half w3-section-small">
    <button class="w3-button w3-theme-d1 w3-hover-theme w3-border w3-border-theme w3-round w3-block" type="submit"><?php echo sprintf(lang('send_option'), lang('message')) ?></button>
</div>
<?php if ($this->session->flashdata('warning_msg')) : ?><div class="w3-col w3-block flash-msg has-error"><i class="fa fa-bell">&nbsp;</i><?php echo $this->session->flashdata('warning_msg') ?></div><?php endif; ?>
<?php if ($this->session->flashdata('success_msg')) : ?><div class="w3-col w3-block w3-text-green flash-msg"><i class="fa fa-bell">&nbsp;</i><?php echo $this->session->flashdata('success_msg') ?></div><?php endif; ?>
<?php echo form_close() ?>
<script type="text/javascript">
    $('document').ready(function() {
        $('#customerContactForm').validate({
            errorClass: "has-error",
            highlight: function(e) {
                $(e).closest('.form-group').addClass('has-error');
                $('.unhighlight').html('');
            },
            unhighlight: function(e) {
                $(e).closest('.form-group').removeClass('has-error');
                $('.unhighlight').html('');
            },

            messages: {
                from_name: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('name')) ?>",
                    minlength: "<?php echo sprintf(lang('min_error_msg'), 2) ?>",
                    maxlength: "<?php echo sprintf(lang('max_error_msg'), 50) ?>"

                },
                from_phone: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('mobile_number')) ?>",
                    phone: "<?php echo lang('mobile_error_msg') ?>",
                    nowhitespace: "<?php echo lang('nowhitespace_error_msg') ?>"
                },
                from_email: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('email')) ?>",
                    email: "<?php echo lang('email_error_msg') ?>",
                    maxlength: "<?php echo sprintf(lang('max_error_msg'), 100) ?>",
                    nowhitespace: "<?php echo lang('nowhitespace_error_msg') ?>"
                },
                message: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('message')) ?>",
                    minlength: "<?php echo sprintf(lang('min_error_msg'), 20) ?>",
                    maxlength: "<?php echo sprintf(lang('max_error_msg'), 100) ?>",
                },
                captcha_total: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('total')) ?>",
                }

            },
            errorPlacement: function(error, element) {
                errorPlacement(error, element);
            }
        });
    })
</script>