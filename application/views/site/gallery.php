<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo $page_title ?></h2>
                <h4><?php echo $page_subtitle ?></h4>
            </div>

            <div class="w3-container w3-section">
                <article class="w3-center"><?php echo $page['description'] ?></article>
                <div class="w3-display-container">
                    <?php foreach ($list as $index => $img) : ?>
                        <img src="<?php echo UPLOAD_PATH . 'page/gallery/' . $img->photo ?>" class="w3-image w3-block w3-animate-zoom slides w3-round" style="height:400px; width:100%">
                        <button class="w3-button w3-display-left w3-theme-d1 w3-hover-theme" onclick="plusDivs(-1)">&#10094;</button>
                        <button class="w3-button w3-display-right w3-theme-d1 w3-hover-theme" onclick="plusDivs(+1)">&#10095;</button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="w3-row-padding">
                <?php foreach ($list as $index => $img) : ?>
                    <div class="w3-third w3-margin-bottom bullet">
                        <figure class="w3-display-container" onclick="currentDiv('<?php echo $index + 1 ?>')">
                            <img style="height: 400px;" src="<?php echo UPLOAD_PATH . 'page/gallery/' . $img->photo ?>" alt="<?php echo $img->title ?>" class="w3-image w3-block w3-round" />
                            <figcaption class="w3-center w3-opacity-min w3-white w3-display-bottommiddle w3-block w3-text-upper"><?php echo $img->title ?></figcaption>
                        </figure>
                    </div>
                <?php endforeach ?>
                <div class="w3-col w3-block w3-center">
                    <?php if (!empty($pagination)) : ?>
                        <?php echo $pagination ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script type="text/javascript">
    //w3.slideshow(".slides", 5000);
    var slideIndex = 1;
    showDivs(slideIndex);

    function currentDiv(n) {
        showDivs(slideIndex = n);
    }

    function plusDivs(n) {
        showDivs(slideIndex += n);
    }

    function showDivs(n) {
        console.log(n);
        var i;
        var x = document.getElementsByClassName("slides");
        if (n > x.length) slideIndex = 1
        if (n < 1) slideIndex = x.length;
        for (i = 0; i < x.length; i++) x[i].style.display = "none";
        x[slideIndex - 1].style.display = "block";
    }
</script>