<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
    <head>
        <!--[if gte mso 9]>
        <xml>
                <o:OfficeDocumentSettings>
                <o:AllowPNG/>
                <o:PixelsPerInch>96</o:PixelsPerInch>
                </o:OfficeDocumentSettings>
        </xml>
        <![endif]-->
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="format-detection" content="date=no" />
        <meta name="format-detection" content="address=no" />
        <meta name="format-detection" content="telephone=no" />
        <meta name="x-apple-disable-message-reformatting" />
        <!--[if !mso]><!-->
        <link href="https://fonts.googleapis.com/css?family=Hind+Vadodara|Stint+Ultra+Condensed" rel="stylesheet"/> 
        <!--<![endif]-->
        <title><?php echo $this->lang->line('email_verification_code') . ' | ' . $this->lang->line('site_title') ?></title>
        <!--[if gte mso 9]>
        <style type="text/css" media="all">
                sup { font-size: 100% !important; }
        </style>
        <![endif]-->

        <style type="text/css" media="screen">
            /* Linked Styles */
            body { padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#fbfbfb; -webkit-text-size-adjust:none;font-family:'Hind Vadodara','Verdana',"Helvetica Neue",Helvetica,sans-serif;font-size:14.5px;line-height:1.4 }
            a { color:#000001; text-decoration:none }
            p { padding:0 !important; margin:0 !important } 
            img { -ms-interpolation-mode: bicubic; /* Allow smoother rendering of resized image in Internet Explorer */ }
            .mcnPreviewText { display: none !important; }
            .text-footer2 a { color: #ffffff; } 

            /* Mobile styles */
            @media only screen and (max-device-width: 480px), only screen and (max-width: 480px) {
                .mobile-shell { width: 100% !important; min-width: 100% !important; }
                .m-center { text-align: center !important; }
                .m-left { text-align: left !important; margin-right: auto !important; }
                .center { margin: 0 auto !important; }
                .content2 { padding: 8px 15px 12px !important; }
                .t-left { float: left !important; margin-right: 30px !important; }
                .t-left-2  { float: left !important; }
                .td { width: 100% !important; min-width: 100% !important; }
                .content { padding: 30px 15px !important; }
                .section { padding: 30px 15px 0px !important; }
                .m-br-15 { height: 15px !important; }
                .mpb5 { padding-bottom: 5px !important; }
                .mpb15 { padding-bottom: 15px !important; }
                .mpb20 { padding-bottom: 20px !important; }
                .mpb30 { padding-bottom: 30px !important; }
                .m-padder { padding: 0px 15px !important; }
                .m-padder2 { padding-left: 15px !important; padding-right: 15px !important; }
                .p70 { padding: 30px 0px !important; }
                .pt70 { padding-top: 30px !important; }
                .p0-15 { padding: 0px 15px !important; }
                .p30-15 { padding: 30px 15px !important; }			
                .p30-15-0 { padding: 30px 15px 0px 15px !important; }			
                .p0-15-30 { padding: 0px 15px 30px 15px !important; }			
                .text-footer { text-align: center !important; }
                .m-td,.m-hide { display: none !important; width: 0 !important; height: 0 !important; font-size: 0 !important; line-height: 0 !important; min-height: 0 !important; }
                .m-block { display: block !important; }
                .fluid-img img { width: 100% !important; max-width: 100% !important; height: auto !important; }
                .column,
                .column-dir,
                .column-top,
                .column-empty,
                .column-top-30,
                .column-top-60,
                .column-empty2,
                .column-bottom { float: left !important; width: 100% !important; display: block !important; }
                .column-empty { padding-bottom: 15px !important; }
                .column-empty2 { padding-bottom: 30px !important; }
                .content-spacing { width: 15px !important; }
            }
        </style>
    </head>
    <body class="body"style="padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#fbfbfb; -webkit-text-size-adjust:none;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#fbfbfb">
            <tr>
                <td align="center" valign="top">
                    <table width="650" border="0" cellspacing="0" cellpadding="0" class="mobile-shell" style="margin-top: 40px">
                        <tr>
                            <td class="td" style="width:650px; min-width:650px; font-size:20pt; line-height:100pt; padding:0; margin:0; font-weight:normal;">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td bgcolor="#d6e35f" class="p30-15 img-center" style="padding: 20px; font-size:0pt; line-height:0pt; text-align:center;">
                                            <a href="<?php echo site_url() ?>" target="_blank">
                                                <img src="<?php echo ASSET_PATH ?>img/logo-icon.png" width="250" height="75" border="0" alt="" /><br/>
                                                <span style="color:#000111; font-size: 14px;  line-height:17px; text-align:center; padding:12px 0px;"><?php $this->config->item('address') ?><br/><?php $this->config->item('phone') ?></span>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-nav-white" bgcolor="#c1d325"style="color:#ffffff; font-size: 17px;  line-height:17px; text-align:center; text-transform:capitalize; padding:12px 0px;">
                                            <a href="<?php echo site_url() ?>" target="_blank" class="link-white"style="color:#ffffff; text-decoration:none;"><span class="link-white"style="color:#ffffff; text-decoration:none;"><?php echo $this->lang->line('nav_home') ?></span></a>
                                            &nbsp; &nbsp;<span class="m-hide"> &nbsp; &nbsp; </span>
                                            <a href="<?php echo site_url('about') ?>" target="_blank" class="link-white"style="color:#ffffff; text-decoration:none;"><span class="link-white"style="color:#ffffff; text-decoration:none;"><?php echo $this->lang->line('nav_about') ?></span></a>
                                            &nbsp; &nbsp;<span class="m-hide"> &nbsp; &nbsp; </span>
                                            <a href="<?php echo site_url('contact') ?>" target="_blank" class="link-white"style="color:#ffffff; text-decoration:none;"><span class="link-white"style="color:#ffffff; text-decoration:none;"><?php echo $this->lang->line('nav_contact') ?></span></a>
                                            &nbsp; &nbsp;<span class="m-hide"> &nbsp; &nbsp; </span>
                                            <a href="<?php echo site_url('help') ?>" target="_blank" class="link-white"style="color:#ffffff; text-decoration:none;"><span class="link-white"style="color:#ffffff; text-decoration:none;"><?php echo $this->lang->line('nav_help') ?></span></a>
                                        </td>
                                    </tr>
                                </table>
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ebebeb">
                                    <tr>
                                        <td class="p30-15-0" style="padding:30px" bgcolor="#ffffff">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr><td class="h2-center" style="color:#000000;  font-family: 'Stint Ultra Condensed', cursive; font-size:30px; line-height:30px; text-align:center; padding-bottom:30px;"><span style="border-bottom: 2px solid #000000"><?php echo $this->lang->line('registration_success') ?></span></td></tr>
                                                <tr><td class="h5-center" style="color:#000000; font-family:'Hind Vadodara','Verdana'; font-size:17px; line-height:25px; text-align:left;"><?php echo $this->lang->line('dear') . ' ' . $recipent ?>, </td></tr>
                                                <tr><td class="text-center" style="color:#273001; font-family:'Hind Vadodara','Verdana'; font-size:15px; line-height:20px; text-align:left; padding-bottom:30px;"><?php echo $text ?></td></tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" bgcolor="#ffffff">
                                            <table border="0" cellspacing="0" cellpadding="0">
                                                <tr><td class="h2-center"style="color:#000000;  font-family: 'Stint Ultra Condensed', cursive; font-size:25px; line-height:30px; text-align:center; padding-bottom:10px;"><span style="border-bottom: 2px solid #000000"><?php echo $this->lang->line('account_information') ?></span></td></tr>
                                                <tr>
                                                    <td class="text-center" style="color:#000000; font-family:'Hind Vadodara','Verdana'; font-size:17px; line-height:20px; text-align:center;">
                                                        <span><?php echo $this->lang->line('name') ?>: <?php echo $name ?></span><br/>
                                                        <span><?php echo $this->lang->line('mobile_number') ?>: <?php echo $phone ?></span><br/>
                                                        <span><?php echo $this->lang->line('email_address') ?>: <?php echo $email ?></span><br/>
                                                        <?php if ($recipent_type === "customer"): ?>
                                                            <span><?php echo $this->lang->line('password') ?> :*************</span><br/>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <?php if ($recipent_type === "customer"): ?>
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ebebeb">
                                        <tr>
                                            <td class="p30-15-0" bgcolor="#ffffff" style="padding:30px">
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td class="text-left"style="color:#074b83; font-family:'Hind Vadodara','Verdana'; font-size:13px; line-height:15px; text-align:left;">
                                                            <?php echo $this->lang->line('resgistration_retreat_msg') ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ebebeb">
                                        <tr>
                                            <td class="p30-15-0" bgcolor="#ffffff" style="padding:10px 30px;">
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td class="text-left"style="color:#000000; font-family:'Raleway', Arial,sans-serif; font-size:13px; line-height:15px; text-align:left;">
                                                            <?php echo $this->lang->line('sincerely_yours') ?>,<br/>
                                                            <?php echo $this->lang->line('site_title') ?>, <?php echo $this->lang->line('account_team') ?>.
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                <?php endif; ?>
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 30px;">
                                    <tr>
                                        <td class="p30-15-0" bgcolor="#818c19" style="padding: 10px;">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <td align="center" class="p30-15" style="font-size: 12px; color: #ffffff; line-height: 20pt">
                                                        &copy;&nbsp;<?php echo date('Y') ?>&nbsp;<a style="color: #ffffff" href="<?php echo site_url() ?>"><?php echo config_item('site_url') ?></a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
