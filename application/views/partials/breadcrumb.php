<section id="breadcurmb" class="w3-theme-l5 w3-border-bottom w3-border-light-gray">
    <div class="w3-content">
        <div class="w3-container">
            <div class="breadcurmb my-font w3-large">
                <div class="w3-left">
                    <?php $breadcrumbCounter = 1; ?>
                    <?php foreach ($breadcrumb as $link => $title) : ?>
                        <a href="<?php echo $link ?>"><?php echo $title ?></a>
                        <?php if ($breadcrumbCounter !== count($breadcrumb)) : ?>
                            <i class="fa fa-angle-right">&nbsp;</i>
                            <?php $breadcrumbCounter++; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>