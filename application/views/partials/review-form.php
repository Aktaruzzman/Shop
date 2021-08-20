<?php echo form_open(site_url('review'), array('method' => 'post', 'id' => 'customerReviewForm', 'class' => 'w3-row-padding', 'autocomplete' => 'off')) ?>
<h3 class="w3-col w3-block"><?php echo sprintf(lang('your_option'), lang('rating')) ?></h3>
<?php if (!$isLoggedin) : ?>
    <div class="w3-col w3-block">
        <p>
            <?php echo lang('login_before_booking'); ?> <a class="w3-anchor w3-text-blue w3-text-bold" href="<?php echo site_url('login') ?>"><?php echo lang('login') ?></a><span class="w3-anchor w3-text-blue w3-text-bold">&nbsp;|&nbsp;</span><a class="w3-anchor w3-text-blue w3-text-bold" href="<?php echo site_url('signup') ?>"><?php echo lang('signup') ?></a>
        </p>
    </div>
<?php endif; ?>
<div class="w3-col w3-third">
    <div class="form-group <?php if (!$isLoggedin) : ?> w3-disabled <?php endif ?>">
        <label for="product_rating" class="w3-text-capitalize"><?php echo lang('product') ?> <?php echo lang('quality') ?></label>
        <div class="rateit bigstars w3-border w3-border-theme w3-round w3-block w3-transparent w3-padding" data-rateit-resetable="false" data-rateit-ispreset="true" data-rateit-backingfld="#product_rating" data-rateit-step="0.25" data-rateit-max="5" data-rateit-mode="font" style="font-size:20px;"></div>
        <input type="hidden" id="product_rating" name="product_rating" value="5">
        <div class="w3-row has-error"><?php echo form_error('product_rating') ?></div>
    </div>
</div>
<div class="w3-col w3-third">
    <div class="form-group <?php if (!$isLoggedin) : ?> w3-disabled <?php endif ?>">
        <label for="price_rating" class="w3-text-capitalize"><?php echo lang('price') ?> <?php echo lang('quality') ?></label>
        <div class="rateit bigstars w3-border w3-border-theme w3-round w3-block w3-transparent w3-padding" data-rateit-resetable="false" data-rateit-ispreset="true" data-rateit-backingfld="#price_rating" data-rateit-step="0.25" data-rateit-max="5" data-rateit-mode="font" style="font-size:20px;"></div>
        <input type="hidden" id="price_rating" name="price_rating" value="5">
        <div class="w3-row has-error"><?php echo form_error('price_rating') ?></div>
    </div>
</div>
<div class="w3-col w3-third">
    <div class="form-group  <?php if (!$isLoggedin) : ?> w3-disabled <?php endif ?>">
        <label for="price_rating" class="w3-text-capitalize"><?php echo lang('service') ?> <?php echo lang('quality') ?></label>
        <div class="rateit bigstars w3-border w3-border-theme w3-round w3-block w3-transparent w3-padding" data-rateit-resetable="false" data-rateit-ispreset="true" data-rateit-backingfld="#service_rating" data-rateit-step="0.25" data-rateit-max="5" data-rateit-mode="font" style="font-size:20px;"></div>
        <input type="hidden" id="service_rating" name="service_rating" value="5">
        <div class="w3-row has-error"><?php echo form_error('service_rating') ?></div>
    </div>
</div>
<div class="w3-col w3-block">
    <div class="form-group <?php if (!$isLoggedin) : ?> w3-disabled <?php endif ?>">
        <label for="note" class="w3-text-capitalize"><?php echo lang('message') ?></label>
        <textarea <?php if (!$isLoggedin) : ?> disabled="disabled" <?php endif ?> name="note" id="note" class="w3-input w3-border w3-border-theme w3-round w3-transparent w3-padding" rows="5"><?php echo set_value('note'); ?></textarea>
        <div class="w3-row has-error w3-tiny"><?php echo form_error('note') ?></div>
    </div>
</div>
<div class="w3-col m3 s6">
    <div class="form-group <?php if (!$isLoggedin) : ?> w3-disabled <?php endif ?>">
        <div class="w3-padding w3-border w3-border-theme w3-round w3-right-align"><?php echo $captcha_val_1 . ' + ' . $captcha_val_2 ?> ?</div>
    </div>
</div>
<div class="w3-col m3 s6">
    <input class="w3-input w3-border w3-border-theme w3-round w3-transparent <?php if (!$isLoggedin) : ?> w3-disabled <?php endif; ?>" id="captcha_total" name="captcha_total" placeholder="Total" required="required" <?php if (!$isLoggedin) : ?> disabled="disabled" <?php endif; ?> />
    <div class="w3-row has-error"><?php echo form_error('captcha_total') ?></div>
</div>
<div class="w3-col m6">
    <div class="form-group <?php if (!$isLoggedin) : ?> w3-disabled <?php endif ?>">
        <button <?php if (!$isLoggedin) : ?> disabled="disabled" <?php endif ?> class="w3-button w3-theme-d1 w3-hover-theme w3-border w3-border-theme w3-round w3-block" type="submit"><?php echo lang('submit') ?></button>
    </div>
</div>
<?php if ($this->session->flashdata('warning_msg')) : ?><div class="w3-block flash-msg has-error w3-text-bold-500"><i class="fa fa-bell">&nbsp;</i><?php echo $this->session->flashdata('warning_msg') ?></div><?php endif; ?>
<?php if ($this->session->flashdata('success_msg')) : ?><div class="w3-text-green w3-text-bold flash-msg"><i class="fa fa-bell">&nbsp;</i><?php echo $this->session->flashdata('success_msg') ?></div><?php endif; ?>
<?php echo form_close() ?>

<script type="text/javascript">
    $('document').ready(function() {
        $('#customerReviewForm').validate({
            errorClass: "has-error w3-small",
            highlight: function(e) {
                $(e).closest('.form-group').addClass('has-error w3-small');
                $('.unhighlight').html('');
            },
            unhighlight: function(e) {
                $(e).closest('.form-group').removeClass('has-error');
                $('.unhighlight').html('');
            },
            rules: {
                note: {
                    required: true,
                    maxlength: 150,
                    minlength: 2,
                },
                captcha_total: {
                    required: true
                }
            },
            messages: {
                note: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('message')) ?>",
                    minlength: "<?php echo sprintf(lang('min_error_msg'), 2) ?>",
                    maxlength: "<?php echo sprintf(lang('max_error_msg'), 150) ?>"
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