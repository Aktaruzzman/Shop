<div class="tp-banner-container Oswald w3-card" id="heroBanner">
    <div class="w3-display-container">
        <?php $sliders = get_rows('pagesliders', ['status' => 'active'], "*", "id", "DESC") ?>
        <?php foreach ($sliders as $index => $slider) : ?>
            <div class="w3-display-container mySlides">
                <img style="height: 450px;" src="<?php echo UPLOAD_PATH . 'slider/' . $slider->photo ?>" class="w3-image w3-block w3-card-4 slider-img">
            </div>
        <?php endforeach; ?>
        <button class="w3-button w3-display-left w3-theme-d1 w3-hover-theme" onclick="plusDivs(-1)">&#10094;</button>
        <button class="w3-button w3-display-right w3-theme-d1 w3-hover-theme" onclick="plusDivs(1)">&#10095;</button>
    </div>
</div>
<script>
    var slideIndex = 1;
    showDivs(slideIndex);

    function plusDivs(n) {
        showDivs(slideIndex += n);
    }

    function showDivs(n) {
        var i;
        var x = document.getElementsByClassName("mySlides");
        if (n > x.length) {
            slideIndex = 1
        }
        if (n < 1) {
            slideIndex = x.length
        }
        for (i = 0; i < x.length; i++) {
            x[i].style.display = "none";
        }
        x[slideIndex - 1].style.display = "block";
    }
</script>