<div id="daysDiscountOfferPopup" class="w3-modal">
    <?php
    $discountplans = discountplan();
    $bogos = discountmulti();
    $prodfreeplan = prodfreeplan();
    $discountpromo = discountpromo(null, date('D'));
    $marquee_line1 = '';
    $marquee_line2 = '';
    ?>
    <div class="w3-modal-content w3-white">
        <header class="w3-container w3-theme-d1 w3-padding">
            <a href="javascript:void(0)" onclick="closePopup()" class="w3-display-topright w3-hover-none w3-padding-8"><i class="fa fa-times w3-xlarge w3-padding-small"></i></a>
            <h4 class="w3-text-capitalize w3-text-bold"><?php echo sprintf(lang('option_offer'), lang('discount')) ?></h4>
        </header>
        <div class="w3-container">
            <div class="w3-ul w3-padding-16">
                <?php if (empty($discountplans) && empty($bogos) && empty($prodfreeplan) && empty($discountpromo)) : ?>
                    <?php echo lang('sorry_no_discount') ?>
                <?php else : ?>
                    <?php if (!empty($bogos)) : ?>
                        <h6 class="w3-text-bold"><span class="w3-border-bottom"><?php echo lang('bogo') ?> <?php echo lang('item') ?></span></h6>
                        <ul class="w3-ul">
                            <?php foreach ($bogos as $k => $bogo) : ?>
                                <li class="w3-border-light-gray" style="padding:4px 0px">
                                    <?php $item = get_name('proditems', ['id' => $bogo->item_id]) ?>
                                    <?php if ($bogo->option_id) : ?>
                                        <?php $option = get_name('prodoptions', ['id' => $bogo->option_id]) ?>
                                        <?php $item = $option . ' ' . $item  ?>
                                    <?php endif; ?>
                                    <?php $marquee_line1 .= sprintf(lang('buy_get_string'), number($bogo->buy), $item, number($bogo->get)) ?>
                                    <?php echo sprintf(lang('buy_get_string'), number($bogo->buy), $item, number($bogo->get)) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($prodfreeplan)) : ?>
                        <h6 class="w3-text-bold"><span class="w3-border-bottom"><?php echo lang('gift') ?> <?php echo lang('item') ?></span></h6>
                        <ul class="w3-ul">
                            <?php foreach ($prodfreeplan as $k => $pp) : ?>
                                <li class="w3-border-light-gray" style="padding:4px 0px">
                                    <?php $marquee_line1 .=  sprintf(lang('order_free_item'), currency($pp->min_order), number($pp->quantity), json_decode($pp->name, true)[$lang]) ?>
                                    <?php echo sprintf(lang('order_free_item'), currency($pp->min_order), number($pp->quantity), json_decode($pp->name, true)[$lang]) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($discountplans)) : ?>
                        <h6 class="w3-text-bold"><span class="w3-border-bottom"><?php echo lang('discount') ?> <?php echo lang('plan') ?></span></h6>
                        <ul class="w3-ul">
                            <?php foreach ($discountplans as $k => $discountplan) : ?>
                                <li class="w3-border-light-gray" style="padding:4px 0px">
                                    <?php $cfg = cfgdiscount($discountplan->discount_id); ?>
                                    <?php $dptxt = number($cfg->value) . '%'; ?>
                                    <?php if ($cfg->func != "percent") : ?>
                                        <?php $dptxt = currency($cfg->value) ?>
                                    <?php endif; ?>
                                    <?php $marquee_line2 .= sprintf(lang('order_discount'), currency($discountplan->min_order), $dptxt) ?>
                                    <?php echo sprintf(lang('order_discount'), currency($discountplan->min_order), $dptxt) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($discountpromo)) : ?>
                        <h6 class="w3-text-bold"><span class="w3-border-bottom"><?php echo lang('coupon') ?> <?php echo lang('code') ?></span></h6>
                        <ul class="w3-ul">
                            <?php foreach ($discountpromo as $k => $pp) : ?>
                                <li class="w3-border-light-gray" style="padding:4px 0px">
                                    <?php $cfg = cfgdiscount($pp->discount_id); ?>
                                    <?php $dptxt = number($cfg->value) . '%'; ?>
                                    <?php if ($cfg->func != "percent") : ?>
                                        <?php $dptxt = currency($cfg->value) ?>
                                    <?php endif; ?>
                                    <?php $marquee_line2 .= "bn" ? sprintf(lang('order_discount_code'), currency($pp->min_order), $dptxt, $pp->code) : sprintf(lang('order_discount_code'), currency($pp->min_order), $dptxt, $pp->code) ?>
                                    <?php echo $lang = "bn" ? sprintf(lang('order_discount_code'), currency($pp->min_order), $dptxt, $pp->code) : sprintf(lang('order_discount_code'), currency($pp->min_order), $dptxt, $pp->code) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>