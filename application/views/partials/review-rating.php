<?php
$product_rating = get_avg('customerreviews', 'product_rating', array('stage' => 2))->product_rating;
$price_rating = get_avg('customerreviews', 'price_rating', array('stage' => 2))->price_rating;
$service_rating = get_avg('customerreviews', 'service_rating', array('stage' => 2))->service_rating;
$avg = number_format(($product_rating + $price_rating + $service_rating) / 3, 2);
?>
<div class="w3-row-padding w3-text-capitalize w3-text-bold w3-section-small">
    <div class="w3-col w3-quarter w3-section-small">
        <div class="w3-padding w3-round w3-center w3-border w3-bodyborder-theme">
            <div class="w3-clear"><?php echo lang('our') ?> <?php echo lang('rating') ?> <?php echo $avg ?>/5 (<?php echo count_rows('customerreviews', array('stage' => 2)) ?>)</span></div>
            <div class="w3-clear">
                <div class="rateit" data-rateit-value="<?php echo $avg ?>" data-rateit-ispreset="true" data-rateit-readonly="true" data-rateit-mode="font" style="font-size:30px;"></div>
            </div>
        </div>
    </div>
    <div class="w3-col w3-quarter w3-section-small">
        <div class="w3-padding w3-round w3-center w3-border w3-bodyborder-theme">
            <div class="w3-clear"><?php echo lang('product') ?> <?php echo lang('quality') ?> <?php echo ceil($product_rating) ?>/5</span></div>
            <div class="w3-clear">
                <div class="rateit" data-rateit-value="<?php echo $product_rating ?>" data-rateit-ispreset="true" data-rateit-readonly="true" data-rateit-mode="font" style="font-size:30px;"></div>
            </div>
        </div>
    </div>
    <div class="w3-col w3-quarter w3-section-small">
        <div class="w3-padding w3-round w3-center w3-border w3-bodyborder-theme">
            <div class="w3-clear"><?php echo lang('price') ?> <?php echo lang('quality') ?> <?php echo ceil($price_rating) ?>/5</span></div>
            <div class="w3-clear">
                <div class="rateit" data-rateit-value="<?php echo $price_rating ?>" data-rateit-ispreset="true" data-rateit-readonly="true" data-rateit-mode="font" style="font-size:30px;"></div>
            </div>
        </div>
    </div>
    <div class="w3-col w3-quarter w3-section-small">
        <div class="w3-padding w3-round w3-center w3-border w3-bodyborder-theme">
            <div class="w3-clear"><?php echo lang('service') ?> <?php echo lang('quality') ?> <?php echo ceil($service_rating) ?>/5</span></div>
            <div class="w3-clear">
                <div class="rateit" data-rateit-value="<?php echo $service_rating ?>" data-rateit-ispreset="true" data-rateit-readonly="true" data-rateit-mode="font" style="font-size:30px;"></div>
            </div>
        </div>
    </div>
</div>