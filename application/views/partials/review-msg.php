<?php if (!empty($list)) : ?>
    <section class="w3-container w3-section w3-row">
        <div class="w3-col w3-quarter w3-hide-small">&nbsp;</div>
        <div class="w3-display-container w3-half">
            <?php foreach ($list as $key => $review) : ?>
                <div class="slides w3-animate-right w3-center w3-display-container">
                    <?php $avatar = get_avatar($review->cust_id) ?>
                    <div class="w3-col w3-block"><img src="<?php echo $avatar ?>" alt="Avatar" onerror="this.src='<?php echo ASSET_PATH ?>img/user.png'" class="w3-image" style="max-width:75px"></div>
                    <?php $avg = ($review->product_rating + $review->price_rating + $review->service_rating) / 3 ?>
                    <div class="w3-col w3-block"><?php echo get_name('customers', array('id' => $review->cust_id)) ?></div>
                    <div class="rateit w3-block" data-rateit-value="<?php echo $avg ?>" data-rateit-ispreset="true" data-rateit-readonly="true"></div>
                    <div class="w3-col w3-block"><?php echo date('d/m/Y', strtotime($review->created_at)) ?><br /></div>
                    <div class="w3-col w3-block"><?php echo $review->note ?></div>
                    <button class="w3-button w3-transparent w3-hover-none w3-display-left w3-text-theme w3-text-bold" onclick="plusDivs(-1)">&#10094;</button>
                    <button class="w3-button w3-transparent w3-hover-none w3-display-right w3-text-theme w3-text-bold" onclick="plusDivs(+1)">&#10095;</button>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="w3-col w3-quarter w3-hide-small">&nbsp;</div>
    </section>
<?php else : ?>
    <div class="w3-container w3-row">
        <div class="w3-padding-16"><?php echo lang('no_reviews_yet') ?></div>
    </div>
<?php endif; ?>

<script type="text/javascript">
    var slideIndex = 1;
    showDivs(slideIndex);

    function plusDivs(n) {
        showDivs(slideIndex += n);
        $('.rateit').rateit();
    }

    function showDivs(n) {
        var i;
        var x = document.getElementsByClassName("slides");
        if (n > x.length) slideIndex = 1
        if (n < 1) slideIndex = x.length;
        for (i = 0; i < x.length; i++) x[i].style.display = "none";
        x[slideIndex - 1].style.display = "block";
    }
</script>