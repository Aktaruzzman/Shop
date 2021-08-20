<!DOCTYPE html>
<html lang="<?php echo lang_option() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (ENVIRONMENT === "development") : ?>
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
    <?php endif; ?>
    <meta http-equiv="refresh" content="9000" url="<?php echo site_url() ?>">
    <?php echo $template['metadata'] ?>
    <?php if (ENVIRONMENT === "production") : ?>
        <meta property="og:title" content="<?php echo $og_title ?>" />
        <meta property="og:description" content="<?php echo $og_desc ?>" />
        <meta property="og:image" content="<?php echo $og_img ?>" />
        <meta name="og:site_name" content="<?php echo $og_site_name ?>" />
        <meta name="og:email" content="<?php echo $og_email ?>" />
        <meta name="og:phone_number" content="<?php echo $og_phone_number ?>" />
        <meta name="og:latitude" content="24.8949" />
        <meta name="og:longitude" content="91.8687" />
        <meta name="og:street-address" content="<?php echo config_item('addstreet_' . lang_option()) ?>" />
        <meta name="og:locality" content="<?php echo config_item('addarea_' . lang_option()) ?>" />
        <meta name="og:region" content="<?php echo config_item('addcity_' . lang_option()) ?>" />
        <meta name="og:postal-code" content="<?php echo config_item('addzip_' . lang_option()) ?>" />
        <meta name="og:country-name" content="Bangladesh" />
    <?php endif; ?>
    <title><?php echo $template['title'] ?></title>
    <link rel="icon shortcut" href="<?php echo base_url() ?>/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo ASSET_PATH ?>plugins/rs-plugin/css/settings.css" />
    <link rel="stylesheet" href="<?php echo ASSET_PATH ?>plugins/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="<?php echo ASSET_PATH ?>plugins/rateit/rateit.css" />
    <link rel="stylesheet" href="<?php echo ASSET_PATH ?>css/w3.css" />
    <link rel="stylesheet" href="<?php echo ASSET_PATH ?>css/themes/w3-theme-<?php echo config_item('web_theme') ?>.css" />
    <link rel="stylesheet" href="<?php echo ASSET_PATH ?>css/style.css" />
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>js/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>js/w3.js"></script>
    <script>
        const _BASE_URL_ = '<?php echo site_url() ?>';
        const _LANG_ = '<?php echo lang_option() ?>'
        if (typeof module === 'object') {
            window.module = module;
            module = undefined;
        }
    </script>
    <?php if (config_item('web_theme_bg_type') === 'image') : ?>
        <?php $bodybg = !empty(config_item('web_theme_bg_image') && checkfile(APPPATH . "../uploads/", config_item('web_theme_bg_image'))) ? UPLOAD_PATH . config_item('web_theme_bg_image') : 'images/body-bg.jpg'  ?>
        <style>
            body {
                background: url('<?php echo $bodybg ?>')repeat center top fixed !important;
                background-size: cover !important;
                background-repeat: no-repeat !important;
                font-size: <?php echo $lang == 'bn' ? (config_item('web_theme_body_font_size') - 1) . 'px' :  config_item('web_theme_body_font_size') . 'px' ?>;
                font-weight: <?php echo config_item('web_theme_body_font_weight') ?>;
            }
        </style>
    <?php else : ?>
        <style>
            body {
                <?php echo config_item('web_theme_bg_color') ?>;
                font-size: <?php echo $lang == 'bn' ? (config_item('web_theme_body_font_size') - 1) . 'px' :  config_item('web_theme_body_font_size') . 'px' ?>;
                font-weight: <?php echo config_item('web_theme_body_font_weight') ?>;
            }
        </style>
    <?php endif; ?>
    <?php if (config_item('web_theme_layout') === 'wide') : ?>
        <style>
            .w3-content {
                min-width: 100% !important;
                max-width: 100% !important;
            }

            @media (min-width:992px) {
                .w3-wide-container {
                    padding-left: 100px !important;
                    padding-right: 100px !important;
                }
            }
        </style>
    <?php endif; ?>

</head>


<body class="w3-theme-light <?php echo $body_class ?>">
    <?php echo $template['partials']['header'] ?>
    <?php echo $template['body'] ?>
    <?php echo $template['partials']['footer'] ?>
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>plugins/rs-plugin/js/jquery.themepunch.tools.min.js"></script>
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>plugins/rs-plugin/js/jquery.themepunch.revolution.min.js"></script>
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>plugins/rs-plugin/js/revolution-slider.js"></script>
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>js/jquery.validate.min.js"></script>
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>js/handlebars.min-v4.7.6.js"></script>
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>js/jquery.sticky-kit.min.js"></script>
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>plugins/rateit/jquery.rateit.min.js"></script>
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>js/suggest.js"></script>
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>js/app.js"></script>
    <script type="text/javascript">
        if (window.module) module = window.module;
    </script>
</body>

</html>