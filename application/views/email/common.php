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
    <link href="https://fonts.googleapis.com/css?family=Hind+Vadodara|Stint+Ultra+Condensed" rel="stylesheet" />
    <!--<![endif]-->
    <title><?php echo lang('email') . ' | ' . get_domain() ?></title>
    <!--[if gte mso 9]>
        <style type="text/css" media="all">
                sup { font-size: 100% !important; }
        </style>
    <![endif]-->

    <style type="text/css" media="screen">
        body {
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
            min-width: 100% !important;
            width: 100% !important;
            background: #fbfbfb;
            -webkit-text-size-adjust: none;
            font-family: 'Hind Vadodara', 'Verdana', "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.4
        }

        a {
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body style="padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#fbfbfb; -webkit-text-size-adjust:none;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#fbfbfb">
        <tr>
            <td align="center" valign="top">
                <table width="650" border="0" cellspacing="0" cellpadding="0" style="margin-top: 40px">
                    <tr>
                        <td style="width:650px; min-width:650px; font-size:20pt;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <?php $store_info = $store_info ? $store_info : store_info() ?>
                                    <td bgcolor="#eeeeee" style="padding: 15px; font-size:0pt; line-height:0pt; text-align:center;">
                                        <div style="color:#000000; font-size:20pt;line-height: 1.5; text-align:center;"><?php echo $store_info['name'] ?></div>
                                        <div style="color:#000000; font-size:14pt;line-height: 1.3; text-align:center;"><?php echo $store_info['house'] ?><br /><?php echo $store_info['area'] ?></div>
                                        <div style="color:#000000; font-size:14pt;line-height: 1.3; text-align:center;"><?php echo lang('phone') ?> : <?php echo $store_info['phone'] ?></div>
                                        <div style="color:#000000; font-size:14pt;line-height: 1.3; text-align:center;"><?php echo lang('email') ?> : <?php echo $store_info['email'] ?></div>
                                    </td>
                                </tr>
                            </table>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
                                <tr>
                                    <td style="padding:20px" bgcolor="#ffffff">
                                        <table width="100%;" border="0" cellspacing="0" cellpadding="0" style="min-height: 150px;">
                                            <tr>
                                                <td style="font-size: 14pt;">
                                                    <?php echo $message ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td bgcolor="#dddddd" style="padding: 10px;">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td align="center" style="font-size: 12px; color: #000000;">
                                                    Copyright&nbsp;&copy;&nbsp;<?php echo date('Y') ?>&nbsp;<a style="color: #000000" href="<?php echo site_url() ?>"><?php echo get_domain() ?></a>
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