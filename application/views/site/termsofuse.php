<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo $page_title ?></h2>
                <h4><?php echo $page_subtitle ?></h4>
                <article class="w3-left-align">
                    <?php $store_info = store_info() ?>
                    <div class="w3-section w3-center w3-text-bold w3-large"> LAST UPDATED : <?php echo date('d/m/Y h:i A', filemtime(APPPATH . 'views/site/termsofuse.php')) ?></div>
                    <?php $domain = '<b>' . get_domain() . '</b>' ?>
                    <?php $address = '<b>' . $store_info['house'] . ' ' . $store_info['area'] . '</b>' ?>
                    <h4><strong>Terms Of Website Use</strong></h4>
                    <p>The terms of use tells you the conditions on which you can make use of our website as guest or as a registered user.</p>
                    <p>"We" "Us" "Our" and "<?php echo $domain ?>" refers to <?php echo $domain ?> and <?php echo config_item('shop_name_en') ?>.</p>
                    <p>"Service" refers to the service We provide for the communication of item orders ("Orders") or for seating reservations ("Reservations")</p>
                    <p>Please read these Terms of Use carefully before you start to use our Site, as these will apply to your use of our Site. We recommend that you print a copy of these Terms of Use for future reference.</p>
                    <p>Your continued use of our Site shall be deemed to show your express consent to be bound by these Terms of Use and that you agree to comply with the.</p>
                    <p>If you do not agree to these Terms of Use then you must not use our Site.</p>
                    <h4><strong>Introduction</strong></h4>
                    <p><?php echo $domain ?> (BD) is the online version of "<?php echo config_item('shop_name_en') ?>" with trade licence number "<?php echo config_item('shop_licence') ?>" at "<?php echo  $address ?>". The site is owned and operated by <?php echo config_item('shop_name_en') ?>. Your access to this website, continuing use of, placing of any order are conditional upon your acceptance of these Terms.</p>
                    <h4><strong>Other applicable terms</strong></h4>
                    <p>These Terms of Use refer to the following additional terms which also apply to your use of our Site:-
                    <div class="w3-padding">
                        Our Privacy Policy which sets out the terms on which we process any personal data we collect from you or that you provide to us. By using our Site you consent to such processing and you warrant that all data provided by you is accurate at the time it is given.<br />
                        Our Cookie Policy, which sets out information about the cookies on this website.<br />
                        If you purchase Services from our Site, these Terms of Use will apply.</br />

                    </div>
                    </p>
                    <h4><strong>Changes to these terms</strong></h4>
                    <p>We may revise these Terms of Use at any time by amending this page.</p>
                    <p>Please check the page from time to time and take notice of any changes we have made as they are binding upon you; the date set out at the top of these terms shows the last date of revision.</p>

                    <h4><strong>Changes to our website</strong></h4>
                    <p></p>

                    <h4><strong></strong></h4>
                    <p>We may update our Site from time to time, and may change the content at any time. However, please note that whilst we update the Site regularly, any of the content may be out of date at any given time and we are under no obligation to update it.</p>
                    <p>We do not guarantee that our Site, or any content on it, will be free from errors or omissions.</p>

                    <h4><strong>Accessing the site</strong></h4>
                    <p>Access to our Site is made available free of charge.</p>
                    <p>We cannot, however, guarantee that our Site, or any content on it, will always be available or uninterrupted. Access to our Site is permitted on a temporary basis only and access once does not guarantee continued or repeated access. We may suspend, withdraw, discontinue or change all or any part of our Site without notice to you, including making part or all of the site accessible only to registered users. We will not be liable to you for any reason should our Site become unavailable for any period of time, regardless of duration or cause.</p>
                    <p>You are responsible for making all arrangements necessary for you to have access to our Site including ensuring that you are operating a sufficiently up to date web browser that it is capable running the Site. You are also responsible for ensuring that all persons who access our Site through your own internet connection are aware of these Terms of Use and other applicable Terms of Use and that they comply with them. You can also check the FAQ.</p>
                    <p>Certain areas of the Site may only be available to registered users and not guests of the Site. Where this is the case, this area of the Site may sit behind a password gateway. The process for registration will be clearly explained on the Site itself.</p>

                    <h4><strong>Your account and password</strong></h4>
                    <p>If you choose to register with our website, you will be asked to select a password and may be asked to confirm other pieces of information relevant to our security procedure. You must treat any such information as strictly confidential and not disclose this to any third party.</p>
                    <p>We have the right to disable any user identification code or password, whether chosen by you or allocated by us at any time, if in our reasonable opinion you fail to comply with any of the provisions of the Terms of Use. If you know or suspect that anyone other than you knows your password then you must promptly notify us at support@<?php echo $domain ?> or take steps to amend the password yourself.</p>
                    <p>We cannot be held liable for any losses suffered by You, howsoever arising and whether foreseeable or not, as a result of unauthorised access to your members area where you have allowed a third party access to your password.</p>

                    <h4><strong>Our purpose</strong></h4>
                    <p><?php echo $domain ?> aims to provide a simple and convenient means of online order to our shop. We offer this either through a web page or through a mobile application. The service allows you either to order, pay for and arrange delivery of your order entirely through the service or to simply order, collect and pay at the shop outlet.</p>

                    <h4><strong>Placing an order</strong></h4>
                    <p>Once You have selected your Orders, You will be asked to confirm your Order is correct and to confirm how you wish to make payment for that Order. You have the option to select an online electronic payment, payment upon delivery or payment upon collection.</p>
                    <p>It is Your responsibility to ensure that your Order is accurate and You should thoroughly check the items which you have selected before confirming your selection.</p>
                    <p>If You have any questions or concerns regarding Your Order, including ingredients used, cookery methods, price or availability or if You suspect that your Order has been incorrectly calculated, You must contact the take-away or shop directly.</p>
                    <p>Once Your Order is processed, You will receive an email at the email address specified during your registration, confirming that Your Order has been received by Us ("Confirmation Email"). Receipt of this email does not mean that Your Order has been accepted by the shop. We ask that each of our shop partners acts promptly to notify You in the event that they cannot meet your Order or where You order requires modification to proceed. The shop will contact You directly in such an instance.</p>
                    <p>In the event that any payment made via Our Site is declined, You will be advised of the position and Your Order will not be further processed.</p>
                    <p>If the shop rejects your Order, You will be notified via email or telephone call. There are circumstances where a shop may not be able to process and deliver Your Order in a timely manner and may be required to reject it.</p>
                    <p>You will receive an estimated delivery time on your Order. This will be an estimation based on how busy the shop is, the geographical location of the shop to the place of delivery and any special event or incident which may otherwise affect the processing times. The shop can be held liable for any unforeseen delay in delivery to You.</p>
                    <h4><strong>Cancelling an order</strong></h4>
                    <p>After Your payment has been processed, You may not amend or cancel Your Order, save at the absolute discretion of the shop.</p>
                    <p>If You wish to attempt to amend or cancel Your Order after payment, You should contact the shop directly to discuss this. The shop will not however be under any obligation to cancel Your Order after You have made a payment.</p>
                    <h4><strong>Prices And Payment</strong></h4>
                    <p>All prices displayed include VAT but not delivery costs (which will be displayed separately where applicable) or any additional ordering fee as we may specify from time to time. Any such additional charges will be clearly displayed on the total amount shown prior to Your Order confirmation.</p>
                    <p>We shall use Our best endeavours to check all displayed prices however, given the number of items displayed upon the Website from time to time, there is the possibility of an error or a scenario where prices have been altered but not updated. Where it becomes apparent that any item for which You have placed an Order is incorrectly priced, We shall endeavour to advise You of this. For the avoidance of doubt however, the shop is under no obligation to complete any Order for an incorrect price. In such circumstances, You will not be able to claim any compensation from Us for the difference in the displayed price and the actual price of any item.</p>
                    <p>In the event that You make a payment online using a credit or debit card and intend to collect Your Order in person, You must ensure that You take the same payment card to the Shop as proof of identity. The Shop may refuse to serve You if it has concerns about Your identity.</p>
                    <p>Even where Your payment has been authorised by Your card provider, there may still be a delay in the money being removed from your account. You should not assume that because Your account appears to still show Your balance prior to the transaction, that payment will not be taken at a later date. Payment may take up to 60 days to be processed.</p>
                    <p>It is accepted banking practice that upon You authorising Your card provider to make a payment to the relevant shop, that they will "reserve" part of Your available balance upon that card to the value of Your Order whilst they process the order. Even in the event that this order is not completed for whatever reason (including cancellation or rejection by the shop), there may be a delay of up to 5 working days before Your card shows the return of the "reserved" balance to You. Neither the shop or Us have control over this and You should contact Your card provider directly in event that You have any queries regarding this process.</p>
                    <p>In the event of any dispute over eny electronic payment, howsoever arising, You should raise this with the shop and/or Your card provider or, in the case of any payment made via sslcommerce, with sslcommerce directly using the relevant "dispute" protocol. You will have no right to recourse against Us directly for any failure in the Service.</p>

                    <h4><strong>Age restriction</strong></h4>
                    <p>You are at least 18 years of age or older and are capable of entering into a binding contract.</p>
                    <?php if (config_item('shop_type') === "restaurant" || config_item('shop_type') === "takeaway") : ?>
                        <h4><strong>Making a seating reservation</strong></h4>
                        <p>We provide an online reservations service.The availability of the reservation shall be ascertained within a reasonable time and when the shop is open. Attempts to reserve seating during hours where the shop is closed will result in the reservation remaining pending until such time as the shop can be contacted. Once Your reservation request is received and processed, You will be notified by email whether or not Your request can be met.</p>
                        <p>In the event that You fail to attend 3 of reservations in any rolling 12 month period, We may, at Our absolute discretion, suspend Your ongoing access to Our reservations facility.</p>
                    <?php endif; ?>
                    <h4><strong>Customer assistance</strong></h4>
                    <p>Customer satisfaction is of vital importance to Us. We will try to assist You where possible in relation to any complaint or dispute relating to Order. You can contact Us on <?php echo config_item('shop_phone') ?> to discuss any issues with a Customer Services Representative.</p>
                    <h4><strong>Royalty point scheme</strong></h4>
                    <p>We operate royalty point scheme. Each time You place an Order, you will earn royalty point.</p>
                    <p>For every <?php echo currency(1) ?> is spent, You will receive <?php echo config_item('royalty_scheme_earning_rate') ?> point.The points are redeemable against future purchase.</p>
                    <p>Whereas the value of one point is <?php echo currency(config_item('royalty_scheme_redeem_rate')) ?> when redeemed against any order placed. There is no equivalent cash value exchange. The offer is non-exchangeable or transferrable and may only be used as described in these Terms of Use.</p>
                    <h4><strong>Intellectual property</strong></h4>
                    <p>We are the owner or the licensee of all intellectual property rights in our Site, and in the material published on it. These works are protected by copyright laws and treaties around the world. All such rights are reserved.</p>
                    <h4><strong>No reliance on information</strong></h4>
                    <p>The content of the Site is provided for general information only. It is not intended to amount to advice upon which you should rely. We make reasonable efforts to regularly update the information on the Site however, we make no representations, warranties, or guarantees, whether express or implied, the content of our site is accurate, complete or up to date at the time which you view it or at the time which you act on it's content.</p>

                    <h4><strong>Limitation of our liability</strong></h4>
                    <p>We will not be liable for any user for any loss or damage, whether in contract, tort, negligence, breach of statutory duty or otherwise even is foreseeable arising under or in connection with:-
                    <div class="w3-padding">
                        use of or inability to use this website.</br />
                        use of reliance on any content displayed on this website.</br>
                        any contract or relationship entered into as a result of using this site.</br />
                        arising from the purchase or attempt to purchase any Service from Our Site.<br />
                    </div>
                    </p>
                    <p>We will not be liable for any loss or damage caused by any virus, distributed denial of service attack or other technologically harmful material that may infect your computer equipment, computer programmes, data or other proprietary material due to your use of our site or to your downloading of any content on it or any website linked to it or for any loss of data that may result.</p>


                    <h4><strong>Events outside our control</strong></h4>
                    <p>We will not be liable or responsible for any failure to perform, or delay in performance of, any of Our obligations under this Agreement that is caused by an Event Outside Our Control.</p>
                    <p>An Event Outside Our Control means any act or event beyond Our reasonable control, including, without limitation, strikes, lock out or other industrial action by third parties, civil commotion, riot, invasion, terrorist attack or threat of terrorist attack, war (whether declared or not) or threat or preparation for war, fire, explosion, storm, flood, earthquake, subsidence, epidemic or other natural disaster, or failure of public or private telecommunications networks.</p>
                    <p>If an Event Outside Our Control takes place that affects the performance of the Service under this Terms of Use Our obligations under this Agreement will be suspended. We shall resume the Service as soon as reasonably possible after the Event Beyond Our Control is over. We will use Our reasonable endeavours to bring any Event Beyond Our Control to a close or find a suitable solution by which Our obligations under these Terms may be performed despite the Event Beyond Our Control.</p>

                    <h4><strong>Links to and from other websites</strong></h4>
                    <p>Links to third party websites on this Site are provided solely for your convenience or interest. Should you access these links and leave our Site then We will have no further control over any of the other websites you may visit or their content. We do not endorse or make any representation about any third party websites, the material on them or the results from using them. If you decide to access any third party website linked from our Site you do so entirely at your own risk.</p>


                    <h4><strong>Uploading content to our site</strong></h4>
                    <p>Whenever you make use of any feature that allows you to upload content to our Site, You warrant that any contribution does comply with the standards and you will be liable to us and indemnify us for any breach of this warranty.</p>
                    <p>Any content uploads to our Site will be considered non-confidential and non-proprietary. Whilst you retain all your ownership rights in your contents, you are required to grant us a limited licence to use, store and copy that content and to distribute it making it available to third parties. The rights you licence to us are described in the next paragraph (Right to Licence).</p>
                    <p>We also have the right to disclose your identity to any third party who is claiming that any content posted or uploaded by you to our Site constitutes a violation of their intellectual property rights or to their rights of privacy.</p>
                    <p>We will not be responsible for liable to any third party, for the content or accuracy of any content posted by you or any other user of our Site.</p>
                    <p>We have the right to remove any posting or information that you made to our Site if, in our opinion, any such information is offensive, obscene or unlawful in any way including where We have reason to believe that it infringes any intellectual property rights of another person or company.</p>

                    <h4><strong>Termination</strong></h4>
                    <p>We reserve the right to decline a new registration to Our Site or to terminate, suspend or remove you as a user of this Site at our absolute discretion and without explanation. We may take any action deemed necessary to prevent such a liability or loss from occurring, if We find you breach any of the material terms of this website.</p>
                    <p>Where You have an account terminated or during a period of account suspension, you are not allowed to order from this Site, nor should you attempt to re-register as a new user.</p>

                    <h4><strong>Limitation for customer</strong></h4>
                    <p>You are prohibited to register multiple memberships for use by the same person. Violation of this clause may result in the termination of any or all of Your memberships. Being a customer you agree not to impersonate any other person or entity or to use a false name or a name you are not authorised to use.</p>

                    <h4><strong>Governing law and jurisdiction</strong></h4>
                    <p>This Terms of Use Policy should be governed by and construed in accordance with country law. Disputes arising in connection with this legal notice shall be subject to the exclusive jurisdiction of the country courts.</p>

                    <h4><strong>Liability</strong></h4>
                    <p>We cannot accept any responsibility for any damage, loss, injury or disappointment suffered by any entrant entering the Competition or as a result of accepting any prize. We are not responsible for any problems or technical malfunction to any telephone network, telephone lines, computers, online systems, servers, internet providers, computer equipment or software, or the failure of any email or entry to be received on account of technical problems, traffic congestion on the internet, telephone lines or any website or any combination thereof, including any injury or damage to the entrant or other persons computer, mobile tablet or mobile telephone related to or resulting from participation in a Competition. Nothing in this clause shall exclude Our liability for death or personal injury as a result of this negligence.</p>

                    <h4><strong>Data Protection and Publicity</strong></h4>
                    <p>Winners of Our Competition may be requested to take part in promotional activity and We reserve the right to use the names and addresses of winners in any publicity, both online and in paper form.</p>
                    <p>Any personal data relating to entrants will not be disclosed to any third party without the individual's express consent. Please see Our Privacy Policy and Cookies Policy [kukd.com/privacy and kukd.com/cookies] for further details.</p>
                    <h4><strong>Jurisdiction</strong></h4>
                    <p>The Competition and Rules shall be governed by the laws of country and any disputes shall be subject to the exclusive jurisdiction of the Courts of country.</p>
                </article>
            </div>
        </div>
    </main>
</div>