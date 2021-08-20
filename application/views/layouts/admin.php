<!DOCTYPE html>
<html lang="<?php echo lang_option() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (ENVIRONMENT === "development"): ?>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <?php endif;?>
    <?php echo $template['metadata'] ?>
    <title><?php echo $template['title'] ?></title>
    <link rel="shortcut icon" href="<?php echo base_url() ?>/favicon.ico" />
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">
    <link rel="icon" href="<?php echo base_url() ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo ASSET_PATH ?>css/w3/w3.css" />
    <link rel="stylesheet" href="<?php echo ASSET_PATH ?>css/style.css" />
    <!--<link rel="stylesheet" href="<?php echo ASSET_PATH ?>css/media.css" />
    <link rel="stylesheet" href="<?php echo ASSET_PATH ?>css/w3/w3-theme-<?php echo config_item('web_theme') ?>.css" />
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>js/jquery.min.js"></script>
    <script>
    if (typeof module === 'object') {
        window.module = module;
        module = undefined;
    }
    </script>
</head>

<body class="w3-light-gray <?php echo $body_class ?>">
    <?php echo $template['partials']['header'] ?>
    <?php echo $template['body'] ?>
    <?php echo $template['partials']['footer'] ?>
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>js/jquery.validate.min.js"></script>
    <!--<script type="text/javascript" src="<?php echo ASSET_PATH ?>js/handlebars.min-v4.7.3.js"></script>-->
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>js/w3.js"></script>
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>js/jquery.sticky-kit.min.js"></script>
    <script type="text/javascript" src="<?php echo ASSET_PATH ?>js/app.js"></script>
    <script type="text/javascript">
    if (window.module) module = window.module;
    </script>
</body>

</html>