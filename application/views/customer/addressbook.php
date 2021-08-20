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
                <div class="w3-col w3-threequarter w3-margin-bottom w3-row-padding">
                    <div class="w3-row-padding-4 w3-white w3-round w3-border w3-border-light-gray">
                        <div class="w3-col w3-block w3-margin-top">
                            <a class="w3-text-theme w3-border-bottom w3-border-theme w3-padding-small" href="<?php echo site_url('customer/addressbook/') ?>"><?php echo sprintf(lang('add_new_option'), lang('address')) ?></a>
                        </div>
                        <form id="AddressEntryForm" class="w3-col w3-block w3-row-padding w3-section w3-small" method="POST" action="<?php echo site_url('customer/addressbook/' . $id) ?>">
                            <div class="w3-col w3-third w3-section-tiny">
                                <label><?php echo lang('area') ?> / <?php echo lang('zone') ?></label>
                                <select name="area_id" class="w3-input w3-border w3-border-theme w3-round">
                                    <option value=""><?php echo lang('select') ?></option>
                                    <?php foreach ($areas as $obj) : ?> <option value="<?php echo $obj['area_id'] ?>" <?php echo !empty($entity) && $entity['area_id'] == $obj['area_id']  ? 'selected="selected"' : '' ?>><?php echo $obj['formatted'][$lang] ?></option><?php endforeach ?>
                                </select>
                                <?php echo form_error('area_id', '<div class="w3-text-red">', '</div>') ?>
                            </div>
                            <div class="w3-col w3-third w3-section-tiny">
                                <label><?php echo lang('address') ?> <?php echo lang('label') ?> (<?php echo lang('english') ?>)</label>
                                <input name="label_en" value="<?php echo !empty($entity) ? $entity['label_en'] : set_value('label_en') ?>" type="text" class="w3-input w3-border w3-border-theme w3-round">
                                <?php echo form_error('label_en', '<div class="w3-text-red">', '</div>') ?>
                            </div>
                            <div class="w3-col w3-third w3-section-tiny">
                                <label><?php echo lang('address') ?> <?php echo lang('label') ?> (<?php echo lang('bangla') ?>)</label>
                                <input name="label_bn" value="<?php echo !empty($entity) ? $entity['label_bn'] : set_value('label_bn') ?>" type="text" class="w3-input w3-border w3-border-theme w3-round">
                                <?php echo form_error('label_bn', '<div class="w3-text-red">', '</div>') ?>
                            </div>
                            <div class="w3-col w3-third w3-section-tiny">
                                <label><?php echo lang('house') ?> (<?php echo lang('english') ?>)</label>
                                <input name="house_en" value="<?php echo !empty($entity) ? $entity['house_en'] : set_value('house_en') ?>" type="text" class="w3-input w3-border w3-border-theme w3-round">
                                <?php echo form_error('house_en', '<div class="w3-text-red">', '</div>') ?>
                            </div>
                            <div class="w3-col w3-third w3-section-tiny">
                                <label><?php echo lang('house') ?> (<?php echo lang('bangla') ?>)</label>
                                <input name="house_bn" value="<?php echo !empty($entity) ? $entity['house_bn'] :  set_value('house_bn') ?>" type="text" class="w3-input w3-border w3-border-theme w3-round">
                                <?php echo form_error('house_bn', '<div class="w3-text-red">', '</div>') ?>
                            </div>
                            <div class="w3-col w3-third w3-section-tiny">
                                <?php if (!empty($entity)) : ?><input type="hidden" name="id" value="<?php echo $entity['id'] ?>"><?php endif ?>
                                <input type="hidden" name="cust_id" value="<?php echo $my['id'] ?>">
                                <label class="w3-hide-small">&nbsp;</label>
                                <button type="submit" class="w3-button w3-theme-d1 w3-border w3-border-theme w3-round w3-block w3-text-upper w3-text-bold w3-hover-theme"><?php echo !empty($entity) ? lang('update') . ' ' . lang('address') :  sprintf(lang('add_new_option'), lang('address'))  ?></button>
                            </div>
                        </form>
                        <?php foreach ($addresses as $address) : ?>
                            <div class="w3-col w3-third">
                                <div class="w3-padding-small w3-section-tiny">
                                    <div class="w3-border-bottom w3-border-theme w3-text-bold-500"><?php echo $address['label'][$lang] ?></div>
                                    <p class="w3-small"><?php echo $address['house'][$lang] ?><br /> <?php echo $address['area']['formatted'][$lang] ?></p>
                                    <a class="w3-small" href="<?php echo site_url('customer/addressbook/' . $address['id']) ?>"><i class="fa fa-edit w3-text-theme">&nbsp;</i><?php echo $this->lang->line('update') ?></a>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<script type="text/javascript">
    $('document').ready(function() {
        $('#AddressEntryForm').validate({
            invalidHandler: function(event, validator) {
                console.log(validator.numberOfInvalids());
            },
            errorClass: "has-error",
            highlight: function(e) {
                $(e).closest('.form-group').addClass('has-error');
                $('.spinning').show();
                $('.unhighlight').text('');
            },
            unhighlight: function(e) {
                $(e).closest('.form-group').removeClass('has-error');
                $('.spinning').hide();
                $('.unhighlight').text('');
            },
            rules: {
                area_id: {
                    required: true,
                },
                label_en: {
                    required: true,
                },
                label_bn: {
                    required: true,
                },
                house_en: {
                    required: true,
                },
                house_bn: {
                    required: true,
                },

            },
            messages: {
                area_id: "<?php echo sprintf(lang('field_required_msg'), lang('area')) ?>",
                label_en: "<?php echo sprintf(lang('field_required_msg'), lang('label') . ' (' . lang('english') . ')') ?>",
                label_bn: "<?php echo sprintf(lang('field_required_msg'), lang('label')) . ' (' . lang('bangla') . ')' ?>",
                house_en: "<?php echo sprintf(lang('field_required_msg'), lang('house')) . ' (' . lang('english') . ')' ?>",
                house_bn: "<?php echo sprintf(lang('field_required_msg'), lang('house')) . ' (' . lang('bangla') . ')' ?>"
            },
            errorPlacement: function(error, element) {
                errorPlacement(error, element);
            }
        });
    });
</script>