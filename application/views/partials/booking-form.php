<?php $is_enabled = config_item('opening_hour')->booking ?>
<?php echo form_open(site_url('booking'), array('method' => 'post', 'id' => 'customerBookingForm', 'class' => 'w3-row-padding', 'autocomplete' => 'off')) ?>
<div class="w3-col w3-block">
    <?php if ($isLoggedin) : ?>
        <?php if ($is_enabled) : ?>
            <p class="">
                <span><?php echo $my['name'][$lang] ?> <?php echo lang('booking') ?> <?php echo lang('information') ?></span>
                <a class="w3-border-bottom w3-text-bold" href="javascript:void(0)" onclick="openPopup('daysOpeningTimePopup')"><?php echo sprintf(lang('option_opening_hours'), lang('store')) ?></a>
            </p>
        <?php else : ?>
            <p><?php echo sprintf(lang('option_service_temp_down'), lang('booking')) ?></p>
        <?php endif; ?>
    <?php else : ?>
        <?php if ($is_enabled) : ?>
            <p>
                <?php echo lang('login_before_booking'); ?> <a class="w3-anchor w3-text-blue w3-text-bold" href="<?php echo site_url('login') ?>"><?php echo lang('login') ?></a><span class="w3-anchor w3-text-blue w3-text-bold">&nbsp;|&nbsp;</span><a class="w3-anchor w3-text-blue w3-text-bold" href="<?php echo site_url('signup') ?>"><?php echo lang('signup') ?></a>
            </p>
        <?php else : ?>
            <p><?php echo sprintf(lang('option_service_temp_down'), lang('booking')) ?></p>
        <?php endif ?>
    <?php endif; ?>
</div>
<div class="w3-col w3-block">
    <div class="w3-col w3-dropdown-click w3-hover-text-theme w3-hover-none form-group w3-padding w3-border w3-border-theme w3-round">
        <div <?php if ($isLoggedin && $is_enabled) : ?> onclick="dropdown('bookingGuestOptions')" <?php endif ?> class="w3-bar-item guest-block w3-row <?php !$isLoggedin || !$is_enabled ? print 'w3-disabled' : '' ?>">
            <div class="w3-col s10"><i class="fa fa-group">&nbsp;</i><span id="guestText"><?php echo !empty((set_value('guest'))) ? to_bengali(set_value('guest'))  . ' ' . lang('people') : sprintf(lang('select_option'), sprintf(lang('option_number'), lang('guest'))) ?></span></div>
            <div class="w3-col s2 w3-right-align"><i class="fa fa-angle-down"></i></div>
            <input type="hidden" id="guest" name="guest" value="<?php echo set_value('guest') ?>" required="required">
            <div class="has-error guest-error flash-msg"><?php echo form_error('guest') ?></div>
        </div>
        <div class="w3-dropdown-content w3-bar-block w3-padding-small w3-padding-8 w3-round" id="bookingGuestOptions" style="max-height: 250px; overflow-y:auto;">
            <div class="w3-row w3-row-padding-4">
                <?php for ($i = 1; $i <= 21; $i++) : ?>
                    <div class="w3-col m3 s6 w3-section-tiny">
                        <div class="w3-block w3-button w3-small w3-hover-theme w3-border w3-border-light-gray w3-round select-booking-guest" data-value="<?php echo $i ?>" data-text="<?php echo to_bengali($i) ?>"><?php echo to_bengali($i) . ' ' . lang('people') ?></div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>
<div class="w3-col w3-block">
    <div class="w3-col w3-dropdown-click w3-hover-text-theme w3-hover-none form-group w3-padding w3-border w3-border-theme w3-round">
        <div <?php if ($isLoggedin && $is_enabled) : ?> onclick="dropdown('bookingDateOptions')" <?php endif ?> class="w3-bar-item date-block w3-row <?php !$isLoggedin || !$is_enabled ? print 'w3-disabled' : '' ?>">
            <div class="w3-col s10"><i class="fa fa-calendar">&nbsp;</i><span id="dateText"><?php echo !empty((set_value('date'))) ? to_bengali(date('d/m/Y', strtotime(set_value('date')))) : sprintf(lang('select_option'), lang('date')) ?></span></span></div>
            <div class="w3-col s2 w3-right-align"><i class="fa fa-angle-down"></i></div>
            <input type="hidden" id="date" name="date" value="<?php echo set_value('date') ?>" required="required">
            <div class="has-error date-error flash-msg"><?php echo form_error('date') ?></div>
        </div>
        <div class="w3-dropdown-content w3-bar-block w3-padding-small w3-padding-8 w3-round" id="bookingDateOptions" style="max-height: 250px; overflow-y:auto;">
            <div class="w3-row w3-row-padding-4">
                <?php foreach ($dates as $date) : ?>
                    <div class="w3-col m4 s12 w3-section-tiny">
                        <div class="w3-block w3-button w3-small w3-hover-theme w3-border w3-border-light-gray w3-round select-booking-date" data-value="<?php echo $date['date_value'] ?>" data-text="<?php echo to_bengali($date['date_text']) ?>&nbsp;(<?php echo $date['date_day'] ?>)"><?php echo to_bengali($date['date_text']) ?>&nbsp;(<?php echo $date['date_day'] ?>)</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<div class="w3-col w3-block">
    <div class="w3-col w3-dropdown-click w3-hover-text-theme w3-hover-none form-group w3-padding w3-border w3-border-theme w3-round">
        <div <?php if ($isLoggedin && $is_enabled) : ?> onclick="dropdown('bookingTimeOptions')" <?php endif; ?> class="w3-bar-item time-block w3-row <?php !$isLoggedin || !$is_enabled ? print 'w3-disabled' : '' ?>">
            <div class="w3-col s10"><i class="fa fa-clock-o">&nbsp;</i><span id="timeText"><?php echo !empty(set_value('time')) ? to_bengali(set_value('time')) : sprintf(lang('select_option'), lang('time')) ?></span></div>
            <div class="w3-col s2 w3-right-align"><i class="fa fa-angle-down"></i></div>
            <input type="hidden" id="time" name="time" value="<?php echo set_value('time') ?>" required="required">
            <div class="has-error time-error flash-msg"><?php echo form_error('time') ?></div>
        </div>
        <div class="w3-dropdown-content w3-bar-block w3-padding-small w3-padding-8 w3-round" id="bookingTimeOptions" style="max-height: 250px; overflow-y:auto;">
            <div class="w3-row w3-row-padding-4">
                <?php foreach ($times as $time) : ?>
                    <div class="w3-col m3 s6 w3-section-tiny">
                        <div class="w3-block w3-button w3-small w3-hover-theme w3-border w3-border-light-gray w3-round select-booking-time" data-value="<?php echo $time ?>" data-text="<?php echo to_bengali($time) ?>"><?php echo to_bengali($time) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php if (config_item('store_type') === 'multiple') : ?>
    <div class="w3-col w3-block">
        <div class="w3-col w3-dropdown-click w3-hover-text-theme w3-hover-none form-group w3-padding w3-border w3-border-theme w3-round">
            <div <?php if ($isLoggedin && $is_enabled) : ?> onclick="dropdown('bookingBranchOptions')" <?php endif; ?> class="w3-bar-item time-block w3-row <?php !$isLoggedin || !$is_enabled ? print 'w3-disabled' : '' ?>">
                <div class="w3-col s10"><i class="fa fa-university">&nbsp;</i><span id="branchText"><?php echo !empty(set_value('hub_id')) ? $selected_store['name'][$lang] . ' (' . $selected_store['house'][$lang] . ', ' . $selected_store['area'][$lang] . ')' : sprintf(lang('select_option'), lang('branch')) ?></span></div>
                <div class="w3-col s2 w3-right-align"><i class="fa fa-angle-down"></i></div>
                <input type="hidden" id="hub_id" name="hub_id" value="<?php echo set_value('hub_id') ?>" required="required">
                <div class="has-error branch-error flash-msg"><?php echo form_error('hub_id') ?></div>
            </div>
            <div class="w3-dropdown-content w3-bar-block w3-padding-small w3-padding-8 w3-round" id="bookingBranchOptions" style="max-height: 250px; overflow-y:auto;">
                <div class="w3-row w3-row-padding-4">
                    <?php foreach ($stores as $store) : ?>
                        <div class="w3-col w3-block w3-section-tiny">
                            <div class="w3-block w3-cursor-pointer w3-text-left w3-padding w3-small w3-hover-theme w3-border w3-border-light-gray w3-round select-booking-store" data-value="<?php echo $store['id'] ?>" data-text="<?php echo  $store['name'][$lang] . ' (' . $store['house'][$lang] . ', ' . $store['area'][$lang] . ')' ?>"><?php echo '<strong class="w3-text-bold">' . $store['name'][$lang] . '</strong> (' . $store['house'][$lang] . ', ' . $store['area'][$lang] . ')' ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<div class="w3-col w3-block">
    <div class="form-group">
        <textarea <?php if (!$isLoggedin || !$is_enabled) : ?> disabled="disabled" <?php endif ?> name="note" id="note" placeholder="<?php echo lang('any_message_for_restaurant') ?>" class="w3-input w3-transparent w3-border w3-border-theme w3-round <?php if (!$isLoggedin || !$is_enabled) : ?> w3-disabled <?php endif; ?>" rows="2"><?php echo set_value('note'); ?></textarea>
        <div class="w3-row has-error flash-msg"><?php echo form_error('note') ?></div>
    </div>
</div>
<div class="w3-col m3 s6 w3-section-small">
    <div class="w3-padding w3-border w3-border-theme w3-round w3-right-align"><?php echo $captcha_val_1 . ' + ' . $captcha_val_2 ?> ?</div>
</div>
<div class="w3-col m3 s6 w3-section-small">
    <input class="w3-input w3-border w3-border-theme w3-round w3-transparent <?php if (!$isLoggedin || !$is_enabled) : ?> w3-disabled <?php endif; ?>" id="captcha_total" name="captcha_total" placeholder="Total" required="required" <?php if (!$isLoggedin || !$is_enabled) : ?> disabled="disabled" <?php endif; ?> />
    <div class="w3-row has-error"><?php echo form_error('captcha_total') ?></div>
</div>
<div class="w3-col m6 w3-section-small">
    <div class="form-group">
        <button <?php if (!$isLoggedin || !$is_enabled) : ?> disabled="disabled" <?php endif ?> class="w3-button w3-theme-d1 w3-hover-theme w3-border w3-border-theme w3-round w3-block" type="submit"><?php echo sprintf(lang('reserve_option'), sprintf(lang('my_option'), lang('table'))) ?></button>
    </div>
</div>
<?php if ($this->session->flashdata('warning_msg')) : ?><div class="w3-section w3-block flash-msg has-error"><i class="fa fa-bell">&nbsp;</i><?php echo $this->session->flashdata('warning_msg') ?></div><?php endif; ?>
<?php if ($this->session->flashdata('success_msg')) : ?><div class="w3-section w3-block w3-text-green flash-msg"><i class="fa fa-bell">&nbsp;</i><?php echo $this->session->flashdata('success_msg') ?></div><?php endif; ?>

<?php echo form_close() ?>
<script type="text/javascript">
    $('document').ready(function() {
        $(document).on("click", ".select-booking-date", function() {
            var dataset = this.dataset;
            $('#date').val(dataset.value);
            $('#dateText').text(dataset.text);
            $('.date-error').text('');
            dropdown('bookingDateOptions');
        });
        $(document).on("click", ".select-booking-time", function() {
            var dataset = this.dataset;
            $("#time").val(dataset.value);
            $("#timeText").text(dataset.text);
            $('.time-error').text('');
            dropdown('bookingTimeOptions');
        });
        $(document).on("click", ".select-booking-guest", function() {
            var dataset = this.dataset;
            $("#guest").val(dataset.value);
            $("#guestText").text(dataset.text + ' ' + '<?php echo lang('people') ?>');
            $('.guest-error').text('');
            dropdown('bookingGuestOptions');
        });
        $(document).on("click", ".select-booking-store", function() {
            var dataset = this.dataset;
            $("#hub_id").val(dataset.value);
            $("#branchText").text(dataset.text);
            $('.branch-error').text('');
            dropdown('bookingBranchOptions');
        });

        $('#customerBookingForm').validate({
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
                guest: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('guest')) ?>",
                },
                date: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('date')) ?>",
                },
                time: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('time')) ?>",
                },
                hub_id: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('store')) ?>",
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