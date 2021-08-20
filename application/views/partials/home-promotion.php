<?php
$discountplans = discountplan();
$bogos = discountmulti();
$prodfreeplan = prodfreeplan();
$column = 0;
if (!empty($discountplans)) $column++;
if (!empty($bogos)) $column++;
if (!empty($prodfreeplan)) $column++;
$col = '';
if ($column === 3) $col = "w3-third";
else if ($column === 2) $col = "w3-half";
else  $col = "w3-block";
?>
<?php if (!empty($discountplans) || !empty($bogos) || !empty($prodfreeplan)) : ?>
    <h3 class="w3-center w3-text-upper"><?php echo lang('our_running_offer') ?></h3>
    <div class="w3-row-padding w3-center">
        <!--PLAN DISCOUNT-->
        <?php if (!empty($discountplans)) : ?>
            <div class="w3-col <?php echo $col ?>">
                <div class="w3-padding">
                    <img src="<?php echo ASSET_PATH ?>img/svg/adbox1.svg" class="w3-image" width="64" />
                    <h4><?php echo sprintf(lang('ad_box1_title'), number(cfgdiscount($discountplans[0]->discount_id)->value) . '%') ?></h4>
                    <p>
                        <?php foreach ($discountplans as $k => $discountplan) : ?>
                            <?php $cfg = cfgdiscount($discountplan->discount_id); ?>
                            <?php $dptxt = number($cfg->value) . '%'; ?>
                            <?php if ($cfg->func != "percent") : ?>
                                <?php $dptxt = currency($cfg->value) ?>
                            <?php endif; ?>
                            <span><?php echo sprintf(lang('order_discount'), currency($discountplan->min_order), $dptxt) ?></span>
                            <?php if ((count($discountplans) - 1) !== $k) : ?>&comma;<?php endif; ?>
                        <?php endforeach; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
        <!--PLAN DISCOUNT END-->
        <!--BOGO DISCOUNT-->
        <?php if (!empty($bogos)) : ?>
            <div class="w3-col <?php echo $col ?>">
                <div class="w3-padding">
                    <img src="<?php echo ASSET_PATH ?>img/svg/adbox2.svg" class="w3-image" width="64" />
                    <h4><?php echo lang('bogo') ?> <?php echo lang('item') ?></h4>
                    <p>
                        <?php foreach ($bogos as $k => $bogo) : ?>
                            <?php $item = get_name('proditems', ['id' => $bogo->item_id]) ?>
                            <?php if ($bogo->option_id) : ?>
                                <?php $option = get_name('prodoptions', ['id' => $bogo->option_id]) ?>
                                <?php $item = $option . ' ' . $item  ?>
                            <?php endif; ?>
                            <?php echo sprintf(lang('buy_get_string'), number($bogo->buy), $item, number($bogo->get)) ?>
                            <?php if ((count($bogos) - 1) !== $k) : ?>&comma;<?php endif; ?>
                        <?php endforeach; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
        <!--BOGO DISCOUNT END-->
        <!--FREE GIFT ITEM-->
        <?php if (!empty($prodfreeplan)) : ?>
            <div class="w3-col <?php echo $col ?>">
                <div class="w3-padding">
                    <img src="<?php echo ASSET_PATH ?>img/svg/adbox3.svg" class="w3-image" width="64" />
                    <h4><?php echo lang('gift') ?> <?php echo lang('item') ?></h4>
                    <p>
                        <?php foreach ($prodfreeplan as $k => $pp) : ?>
                            <span><?php echo sprintf(lang('order_free_item'), currency($pp->min_order), number($pp->quantity), json_decode($pp->name, true)[$lang]) ?></span>
                            <?php if ((count($prodfreeplan) - 1) !== $k) : ?>&comma;<?php endif; ?>
                        <?php endforeach; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
        <!--BOGO DISCOUNT END-->
    <?php endif; ?>