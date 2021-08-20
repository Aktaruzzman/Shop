<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo $page_title ?></h2>
                <h4><?php echo $page_subtitle ?></h4>
                <article class="w3-left-align">
                    <?php $store_info = store_info() ?>
                    <div class="w3-section w3-center w3-text-bold w3-large"> LAST UPDATED : <?php echo date('d/m/Y h:i A', filemtime(APPPATH . 'views/site/privacypolicy.php')) ?></div>
                    <?php $domain = '<b>' . get_domain() . '</b>' ?>
                    <?php $address = '<b>' . $store_info['house'] . ' ' . $store_info['area'] . '</b>' ?>

                    <h2><strong>What data we store, and how we use it.</strong></h2>
                    <p><?php echo $domain ?> (BD) Ltd ("We") are committed to protecting and respecting your privacy and protecting your data.</p>
                    <p>This policy (together with our <a class="w3-border-bottom" href="<?php echo site_url('terms-of-use') ?>">terms of use</a> and any other documents referred to on it) sets out the basis on which any personal data we collect from you, or that you provide to us, will be processed by us. Please read the following carefully to understand our views and practices regarding your personal data and how we will treat it. By visiting kukd.com you are accepting and consenting to the practices described in this policy.</p>
                    <p>By visiting this website, you consent to the collection and use of your Personal Data as described herein. If you do not agree with the terms set out herein, please do not visit this website.</p>

                    <h3><strong>Information we may collect from you</strong></h3>
                    <p>We may collect and process the following data about you:</p>
                    <p>Information you give us. You may give us information about you by filling in forms on <?php echo $domain ?> (our site) or by corresponding with us by phone, e-mail or otherwise. This includes information you provide when you register to use our site, subscribe to our service, search for a product, place an order on our site, make a reservation through our site or when you report a problem with our site. The information you give us may include your name, address, date of birth, e-mail address and phone number, and chosen passwords.</p>
                    <p>Information we collect about you.</p>
                    <p>Technical information, including the Internet protocol (IP) address used to connect your computer to the Internet, your login information, browser type and version, time zone setting, browser plug-in types and versions, operating system and platform;</p>
                    <p>Information about your visit, including the full Uniform Resource Locators (URL) clickstream to, through and from our site (including date and time); products you viewed or searched for; order information; previous orders; reservations history; page response times, download errors, length of visits to certain pages, page interaction information (such as scrolling, clicks, and mouse-overs), and methods used to browse away from the page and any phone number used to call our customer service number.</p>
                    <p>Information we receive from other sources. We may receive information about you if you use any of the other websites we operate or the other services we provide. We are also working closely with third parties (including, for example, business partners, sub-contractors in technical, payment and delivery services, advertising networks, analytics providers, search information providers, credit reference agencies) and may receive information about you from them.</p>

                    <h3><strong>Purpose of Processing Personal Data</strong></h3>
                    <p>All information collected from you is used for providing our business service to you and ensuring access to, and the use of, our website. The information may be used for such other purposes as communication with you, customised content for you, and to improve our website and service to you by analysing how users navigate and use the website.</p>

                    <h3><strong>Cookies</strong></h3>
                    <p>Our website uses cookies to distinguish you from other users of our website. This helps us to provide you with a good experience when you browse our website and also allows us to improve our site. For detailed information on the cookies we use and the purposes for which we use them see our Cookie policy</p>

                    <h3><strong>Use of your Information</strong></h3>
                    <p>We use information held about you in the following ways:</p>
                    <p>Information you give to us. We will use this information:</p>
                    <div class="w3-margin-left">
                        <p>To carry out our obligations arising from any contracts entered into between you and us and to provide you with the information, products and services that you request from us;</p>
                        <p>To provide you with information about other goods and services we offer that are similar to those that you have already purchased or enquired about;</p>
                        <p>To notify you about changes to our service;</p>
                        <p>To ensure that content from our site is presented in the most effective manner for you and for your computer.</p>
                    </div>
                    <p>Information we collect about you. We will use this information:</p>
                    <div class="w3-margin-left">
                        <p>To administer our site and for internal operations, including troubleshooting, data analysis, testing, research, statistical and survey purposes;</p>
                        <p>To improve our site to ensure that content is presented in the most effective manner for you and for your computer;</p>
                        <p>To allow you to participate in interactive features of our service, when you choose to do so;</p>
                        <p>As part of our efforts to keep our site safe and secure;</p>
                        <p>To measure or understand the effectiveness of advertising we serve to you and others, and to deliver relevant advertising to you;</p>
                        <p>To make suggestions and recommendations to you and other users of our site about goods or services that may interest you or them.</p>
                    </div>
                    <p>Information we receive from other sources. We may combine this information with information you give to us and information we collect about you. We may us this information and the combined information for the purposes set out above (depending on the types of information we receive).</p>

                    <h3><strong>Disclosure of your Information</strong></h3>
                    <p>We may share your personal information with any member of the shop and our subsidiaries.</p>
                    <p>We may share your information with selected third parties including:</p>
                    <div class="w3-margin-left">
                        <p>Business partners, suppliers and sub-contractors for the performance of any contract we enter into with them or you.</p>
                        <p>Advertisers and advertising networks that require the data to select and serve relevant adverts to you and others.</p>
                        <p>Analytics and search engine providers that assist us in the improvement and optimisation of our site.</p>
                        <p>Credit reference agencies for the purpose of assessing your credit score where this is a condition of us entering into a contract with you.</p>
                    </div>
                    <p>We may disclose your personal information to third parties:</p>
                    <div class="w3-margin-left">
                        <div class="w3-margin-left">
                            <p>In the event that we sell or buy any business or assets, in which case we may disclose your personal data to the prospective seller or buyer of such business or assets.</p>
                            <p>If <?php echo $domain ?> (BD) or substantially all of its assets are acquired by a third party, in which case personal data held by it about its customers will be one of the transferred assets.</p>
                            <p>If we are under a duty to disclose or share your personal data in order to comply with any legal obligation, or in order to enforce or apply our terms of use [<?php echo site_url('terms-of-use') ?>] and other agreements; or to protect the rights, property, or safety of <?php echo $domain ?> (BD), our customers, or others. This includes exchanging information with other companies and organisations for the purposes of fraud protection and credit risk reduction.</p>
                        </div>
                        <p>We may disclose a user’s personal information without their prior permission only in certain circumstances. We are permitted to disclose personal information when we have good reason to believe that this is legally required by the Data Protection Act 1998.</p>
                    </div>

                    <h3><strong>Where we store your personal data</strong></h3>
                    <p>The data that we collect from you may be transferred to, and stored at, a destination outside the country you live in. It may also be processed by staff operating outside the country who work for us. Such staff maybe engaged in, among other things, the fulfilment of your order, the processing of your payment details and the provision of support services. By submitting your personal data, you agree to this transfer, storing or processing. We will take all steps reasonably necessary to ensure that your data is treated securely and in accordance with this privacy policy.</p>
                    <p>Where we have given you (or where you have chosen) a password which enables you to access certain parts of our site, you are responsible for keeping this password confidential. We ask you not to share a password with anyone.</p>
                    <p>Unfortunately, the transmission of information via the internet is not completely secure. Although we will do our best to protect your personal data, we cannot guarantee the security of your data transmitted to our site; any transmission is at your own risk. Once we have received your information, we will use strict procedures and security features to try to prevent unauthorised access.</p>

                    <h3><strong>Retention and Deletion</strong></h3>
                    <p><?php echo $domain ?> (BD) will not retain your personal data for longer than is necessary to fulfil the purposes for which it was collected or as required by any and all applicable laws and/or regulations. When a user’s account is closed, terminated or expired, all Personal Data collected through our website will be deleted, as required by law and standard accounting practices.</p>
                    <p></p>

                    <h3><strong>Your Rights</strong></h3>
                    <p>Together with your general rights under the Data Protection Act 1998 and the General Data Protection Regulations, you specifically have the right to ask us not to process your personal data for marketing purposes. We will usually inform you (before collecting your data) if we intend to use your data for such purposes or if we intend to disclose your information to any third party for such purposes. You can exercise your right to prevent such processing by checking certain boxes on the forms we use to collect your data. You can also exercise the right at any time by contacting us at support@kukd.com. All information can be found in our <a href="<?php echo site_url('terms-of-use') ?>">terms and conditions page</a>.</p>
                    <p>You also have the right to withdraw your consent for your data to be stored, processed or retained at any time. This may result in you not being able to access your account. Should you wish to do so, you may contact us with your request by email to support@<?php echo $domain ?>,</p>
                    <p>Our site may, from time to time, contain links to and from the websites of our partner networks, advertisers and affiliates. If you follow a link to any of these websites, please note that these websites have their own privacy policies and that we do not accept any responsibility or liability for these policies. Please check these policies before you submit any personal data to these websites.</p>

                    <h3><strong>Access to Information</strong></h3>
                    <p>The Act gives you the right to access information held about you. Your right of access can be exercised in accordance with the Act. Any access request may be subject to a fee to meet our costs in providing you with details of the information we hold about you.</p>

                    <h3><strong>Changes To Our Privacy Policy</strong></h3>
                    <p>Any changes we may make to our privacy policy in the future will be posted on this page so please check this page regularly. Please check back frequently to see any updates or changes to our privacy policy.</p>

                    <h3><strong>Further Information</strong></h3>
                    <p>If you have any questions, comments or requests regarding this privacy policy, please feel free to contact us by email at support@<?php echo $domain ?>, or in writing to <?php echo config_item('shop_name_en') ?>,<?php echo $address ?>.</p>
                </article>
            </div>
        </div>
    </main>
</div>