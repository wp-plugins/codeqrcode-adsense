<?php

class CodeqrcodeWidget
{

    public $codeqrcode_url;
    public $api_data;
    public $api_data_table;
    public $ad_data = array();

    public function __construct()
    {

        $this->codeqrcode_url = "http://www.codeqrcode.com/analytics/";


        if (is_admin()) {
            add_action("admin_menu", array(
                &$this,
                "adminMenu"
            ));

            add_action('admin_init', array(
                &$this,
                "setOptions"
            ));


            if (is_null($this->addNewWebsiteApi())) { // Fetch data via codeQRCode API
                $this->api_data = new stdClass();
                $this->api_data->data = array();
            } else {

                $this->api_data_table = $this->addNewWebsiteApi();
                $this->api_data = $this->addNewWebsiteApi();
            }
            /* Add new items to the end of array data*/
            $item_add = new stdClass();

            /*
             * Add items to array for qr selection
             */

            $item_add->img_idkey = 'static';
            $item_add->name = 'Static QR code';
            array_push($this->api_data->data, unserialize(serialize($item_add)));

            /*
             * Add items to array for ads selection
             */

            if (get_option('codeQRCodeAds') !== '') {
                $item_add->uniq_name = stripslashes(htmlspecialchars_decode(get_option('codeQRCodeAds')));
                if (get_option('codeQRCodeAds1Name') != "") {
                    $item_add->title = get_option('codeQRCodeAds1Name');
                } else {
                    $item_add->title = 'Ad 1 code';
                }

                array_push($this->ad_data, unserialize(serialize($item_add)));
            }

            if (get_option('codeQRCodeAds2') !== '') {
                $item_add->uniq_name = stripslashes(htmlspecialchars_decode(get_option('codeQRCodeAds2')));
                if (get_option('codeQRCodeAds2Name') != "") {
                    $item_add->title = get_option('codeQRCodeAds2Name');
                } else {
                    $item_add->title = 'Ad 2 code';
                }
                array_push($this->ad_data, unserialize(serialize($item_add)));
            }
            if (get_option('codeQRCodeAds3') !== '') {
                $item_add->uniq_name = stripslashes(htmlspecialchars_decode(get_option('codeQRCodeAds3')));
                if (get_option('codeQRCodeAds3Name') != "") {
                    $item_add->title = get_option('codeQRCodeAds3Name');
                } else {
                    $item_add->title = 'Ad 3 code';
                }
                array_push($this->ad_data, unserialize(serialize($item_add)));
            }

            $item_add->uniq_name = 'none';
            $item_add->title = 'Do not show';
            array_push($this->ad_data, unserialize(serialize($item_add)));

        }

        //var_dump($this->api_data);

        if (get_option('codeQRCodeSingleWidgetID') !== 'none') {

            if (get_option('codeQRCodeSingleWidgetID') == '') {
                if ($this->api_data->data[0] && $this->api_data->data[0]->uniq_name != 'none') {
                    update_option('codeQRCodeSingleWidgetID', $this->api_data->data[0]->uniq_name);
                }

                add_filter('the_content', 'codeQRCode_bottom_post');
            }
        }

        if (get_option('codeQRCodePageWidgetID') !== 'none') {

            if (get_option('codeQRCodePageWidgetID') == '') {
                if ($this->api_data->data[0] && $this->api_data->data[0]->uniq_name != 'none') {
                    update_option('codeQRCodePageWidgetID', $this->api_data->data[0]->uniq_name);
                }

            }
            add_filter('the_content', 'codeQRCode_bottom_post');
        }


    }

    function setOptions()
    {
        register_setting('codeQRCode-options', 'codeQRCodeApplicationID');
        register_setting('codeQRCode-options', 'codeQRCodePoweredBy');
        register_setting('codeQRCode-options', 'codeQRCodeSingleWidgetID');
        register_setting('codeQRCode-options', 'codeQRCodePageWidgetID');
        register_setting('codeQRCode-options', 'codeQRCodeSingleWidgetTitle');
        register_setting('codeQRCode-options', 'codeQRCodeShadow');
        // Ads codes
        register_setting('codeQRCode-options', 'codeQRCodeAds');
        register_setting('codeQRCode-options', 'codeQRCodeAds2');
        register_setting('codeQRCode-options', 'codeQRCodeAds3');
        // Custom ads name
        register_setting('codeQRCode-options', 'codeQRCodeAds1Name');
        register_setting('codeQRCode-options', 'codeQRCodeAds2Name');
        register_setting('codeQRCode-options', 'codeQRCodeAds3Name');

        //Static QR codes
        //for signle page
        register_setting('codeQRCode-options', 'codeQRCodeSingle');
        register_setting('codeQRCode-options', 'enableQROnSingle');
        register_setting('codeQRCode-options', 'qrCodeAlignSingle'); //Settings for align qr code on single post
        register_setting('codeQRCode-options', 'qrMarginSingle'); //Settings for margin on single post
        register_setting('codeQRCode-options', 'qrImgSizeSingle'); //Settings for Image size on single post
        //for single page
        register_setting('codeQRCode-options', 'codeQRCodePage'); // Used to store qr data
        register_setting('codeQRCode-options', 'enableQROnPage'); // Settings to enable/disable qr code
        register_setting('codeQRCode-options', 'qrCodeAlignPage'); //Settings for align qr code on single page
        register_setting('codeQRCode-options', 'qrMarginPage'); //Settings for margin on single page
        register_setting('codeQRCode-options', 'qrImgSizePage'); //Settings for Image size on single page


    }

    public function adminMenu()
    {
        add_menu_page('CodeQRCode Generator - Premium QR codes and Analytics', 'Code QRCode', 'manage_options', 'codeqrcode-adsense', array(
            $this,
            'createAdminPage'
        ), content_url() . '/plugins/codeqrcode-adsense/images/codeqrcode-icon.png');

    }

    public function getSignupUrl()
    {

        return $this->codeqrcode_url . 'signup?utm_source=wordpress_cqc&utm_medium=admin&e=' . urlencode(get_option('admin_email')) . '&pub=' .  preg_replace('/^www\./','',$_SERVER['SERVER_NAME']).
        '&un=' . urlencode(wp_get_current_user()->display_name);

    }

    private function addNewWebsiteApi()
    {

        if (!is_callable('curl_init')) {
            return;
        }

        $service     = $this->codeqrcode_url . "wp-authenticate/user";
        $p['ip']     = $_SERVER['REMOTE_ADDR'];
        $p['domain'] = site_url();
        $p['source'] = "wordpress";
        $p['QRCodeApplicationID'] = get_option('codeQRCodeApplicationID');


        $client = curl_init();

        curl_setopt($client, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($client, CURLOPT_HEADER, 0);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($client, CURLOPT_URL, $service);

        if (!empty($p)) {
            curl_setopt($client, CURLOPT_POST, count($p));
            curl_setopt($client, CURLOPT_POSTFIELDS, http_build_query($p));
        }

        $data = curl_exec($client);

        if (curl_error($client) != "") {
            $this->curlfailovao = 1;
        } else {
            $this->curlfailovao = 0;
        }
        curl_close($client);

        $data = json_decode($data);
        //var_dump($data);die;
        return $data;

    }

    public function createAdminPage()
    {
        $code = get_option('codeQRCodeApplicationID');
        $qr_home_url = 'http://www.codeqrcode.com';
        $qr_dashboard_url = 'http://www.codeqrcode.com/analytics/qranalytics';

        ?>
        <style>
            .qrcode-signup-button {
                float: left;
                vertical-align: top;
                width: auto;
                height: 30px;
                line-height: 30px;
                padding: 10px;
                font-size: 22px;
                color: white;
                text-align: center;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
                background: #c0392b;
                border-radius: 5px;
                border-bottom: 2px solid #b53224;
                cursor: pointer;
                -webkit-box-shadow: inset 0 -2px #b53224;
                box-shadow: inset 0 -2px #b53224;
                text-decoration: none;
                margin-top: 10px;
                margin-bottom: 10px;
                clear: both;
            }

            a.qrcode-signup-button:hover {
                cursor: pointer;
                color: #f8f8f8;
            }
            textarea {
                overflow: auto;
                padding: 4px 6px;
                line-height: 1.4;
            }

            .btn { border: 1px solid #fff; font-size: 13px; border-radius: 3px; background: transparent; text-transform: uppercase; font-weight: 700; padding: 4px 10px; min-width: 162px; max-width: 100%; text-decoration: none;}
            .btn:Hover, .btn.hovered { border: 1px solid #fff; }
            .btn:Active, .btn.pressed { opacity: 1; border: 1px solid #fff; border-top: 3px solid #17ade0; -webkit-box-shadow: 0 0 0 transparent; box-shadow: 0 0 0 transparent; }

            .btn-primary { background: #1ac6ff; border:1px solid #1ac6ff; color: #fff; text-decoration: none;}
            .btn-primary:hover, .btn-primary.hovered { background: #1ac6ff;  border:1px solid #1ac6ff; opacity:0.9; }
            .btn-primary:Active, .btn-primary.pressed { background: #1ac6ff; border:1px solid #1ac6ff; }

            .box{float: left; margin-left: 10px; width: 600px; background-color:#f8f8f8; padding: 10px; border-radius: 5px;}
            .box img{width: 910px};
        </style>
        <!-- Load css libraries -->

        <link href="//cdn.datatables.net/1.10.5/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

        <div id="codeQRCode-options" style="width:980px;margin-top:10px;">

            <div style="float: left; width: 300px;">

                <a target="_blank" href="<?php echo $qr_home_url; ?>?utm_source=wordpress_cqc">
                    <img style="border-radius:5px;border:0px;" src=" <?php echo plugins_url('images/logo.jpg', __FILE__);?>" /></a>
                <?php
                if ($code != '') : ?>
                    <a target="_blank" href="<?php echo $qr_dashboard_url; ?>/?utm_source=wordpress_cqc">
                        <img style="border:0px;margin-top:5px;border-radius:5px;" src="<?php echo plugins_url('images/dashboard.jpg', __FILE__); ?>" /></a>

                <?php endif; ?>

                <a target="_blank" href="<?php echo $qr_home_url;?>/contact/?utm_source=wp-plugin-contact-cqc">
                    <img style="border:0px;margin-top:5px; margin-bottom:5px;border-radius:5px;" src="<?php echo plugins_url('images/support.jpg', __FILE__); ?>" /></a>

                <a target="_blank" href="http://qr.rs/q/56a5d">
                    <img style="border:0px;margin-top:5px; margin-bottom:5px;border-radius:5px;" src="<?php echo plugins_url('images/cqc-promo-300x200.png', __FILE__); ?>" /></a>

            </div>
            <div class="box">

                <h1>Code QR codes generator</h1>

                <?php

                if ($code == '') : ?>
                    <h3 style="float: left">Step 1:</h3>
                    <a class='qrcode-signup-button' target='_blank' href="<?php echo $this->getSignupUrl(); ?>">Click here to create your FREE account!</a>

                <?php endif; ?>

                <div style="clear: both"></div>
                <?php if ($code == '') { ?>
                    <h3>Step 2: &nbsp;&nbsp;&nbsp;&nbsp; Paste your CodeQRCode Application ID</h3>
                <?php }else{ ?>
                    <h3>Your CodeQRcode Application ID</h3>
                <?php } ?>

                <form method="post" action="options.php">
                    <?php
                    settings_fields('codeQRCode-options');
                    ?>

                    <p>
                        <input type="text" style="width: 400px" name="codeQRCodeApplicationID" id="codeQRCodeApplicationID" value="<?php
                        echo (get_option("codeQRCodeApplicationID"));
                        ?>" maxlength="999" onchange="appIDChange(this.value)"/>
                    </p>
                    <p>
                        <input type="checkbox" id="codeQRCodePoweredBy" name="codeQRCodePoweredBy" <?php echo (get_option("codeQRCodePoweredBy") == true ? 'checked="checked"' : ''); ?> Required="Required">
                        <strong>Required</strong> I acknowledge there is a <a style="text-decoration: none" href="http://codeqrcode.com" target="_blank">'powered by QR CODE with love'</a> link on the widget. <br />
                    </p>

                    <?php if($this->api_data_table->flag === false): ?>
                    <p><span style="color:red"><?php echo $this->api_data_table->error; ?></span></p>
                    <?php endif; ?>

                    <h1>Options</h1>


                    <h3><?php _e('QR codes Settings :'); ?></h3>

                    <p>
                        <input type="checkbox" id="codeQRCodeShadow" name="codeQRCodeShadow" <?php echo (get_option("codeQRCodeShadow") == true ? 'checked="checked"' : ''); ?>>
                        <label for="codeQRCodeSingleWidgetTitle"><strong>Enable Shadow</strong> on QR codes</label><br/>
                    </p>

                    <label for="codeQRCodeSingleWidgetTitle">Title Above Adsence code (Optional): </label><br/>
                    <input type="text" style="width: 300px; margin:10px 0px" name="codeQRCodeSingleWidgetTitle" id="codeQRCodeSingleWidgetTitle" value="<?php echo (get_option("codeQRCodeSingleWidgetTitle")); ?>" maxlength="999" />


                    <table border="0" cellspacing="5" cellpadding="0">

                        <tr valign="top">
                            <td align="left" style="padding:0px 10px"><input type="checkbox" id="enableQROnSingle" name="enableQROnSingle" <?php echo (get_option("enableQROnSingle") == true ? 'checked="checked"' : ''); ?>></td>
                            <td align="left"><strong>to End of each Post</strong> </td>
                            <td align="left" style="padding:0px 10px"> <select id="codeQRCodeSingle" name="codeQRCodeSingle">
                                    <?php
                                    foreach ($this->api_data->data as $item): ?>
                                        <option <?php echo (stripslashes(htmlspecialchars_decode(get_option('codeQRCodeSingle'))) == $item->img_idkey)? 'selected="selected"' : '' ;?> value="<?php echo addslashes(htmlspecialchars($item->img_idkey)); ?>"><?php echo $item->name; ?></option>
                                    <?php endforeach; ?>

                                </select></td>
                            <td align="left" style="padding-left: 10px">
                                <?php $single_align = get_option('qrCodeAlignSingle'); ?>
                                <select name="qrCodeAlignSingle">
                                    <option value="Left" <?php echo $single_align == 'Left'? 'selected="selected"': ''; ?>><?php _e('Left') ; ?></option>
                                    <option value="Center" <?php echo $single_align == 'Center'? 'selected="selected"': ''; ?>><?php _e('Center') ; ?></option>
                                    <option value="Right" <?php echo $single_align == 'Right'? 'selected="selected"': ''; ?>><?php _e('Right') ; ?></option>
                                    <option value="None" <?php echo $single_align == 'None'? 'selected="selected"': ''; ?>><?php _e('None') ; ?></option></select> <?php _e('alignment'); ?><br/>
                                <input style="width:73px;text-align:center;" id="qrMarginSingle" name="qrMarginSingle" value="<?php echo get_option('qrMarginSingle') != '' ? get_option('qrMarginSingle') : '10'; ?>" /> px &nbsp;- <?php _e('margin'); ?><br/>
                                <input style="width:73px;text-align:center;" id="qrImgSizeSingle" name="qrImgSizeSingle" value="<?php echo get_option('qrImgSizeSingle') != '' ? get_option('qrImgSizeSingle') : '120'; ?>" /> px &nbsp;- <?php _e('Image size'); ?><br/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <td align="left" style="padding:0px 10px"><input type="checkbox" id="enableQROnPage" name="enableQROnPage" <?php echo (get_option("enableQROnPage") == true ? 'checked="checked"' : ''); ?>></td>
                            <td align="left"><strong>to End of each Page</strong> </td>
                            <td align="left" style="padding:0px 10px"> <select id="codeQRCodePage" name="codeQRCodePage">
                                    <?php
                                    foreach ( $this->api_data->data as $item ): ?>
                                        <option <?php echo (stripslashes(htmlspecialchars_decode(get_option('codeQRCodePage'))) == $item->img_idkey)? 'selected="selected"' : '' ;?> value="<?php echo addslashes(htmlspecialchars($item->img_idkey)); ?>"><?php echo $item->name; ?></option>
                                    <?php endforeach; ?>

                                </select></td>
                            <td align="left" style="padding-left: 10px">
                                <?php $singlePage_align = get_option('qrCodeAlignPage'); ?>
                                <select name="qrCodeAlignPage">
                                    <option value="Left" <?php echo $singlePage_align == 'Left'? 'selected="selected"': ''; ?> ><?php _e('Left') ; ?></option>
                                    <option value="Center" <?php echo $singlePage_align == 'Center'? 'selected="selected"': ''; ?>><?php _e('Center') ; ?></option>
                                    <option value="Right" <?php echo $singlePage_align == 'Right'? 'selected="selected"': ''; ?>><?php _e('Right') ; ?></option>
                                    <option value="None" <?php echo $singlePage_align == 'None'? 'selected="selected"': ''; ?>><?php _e('None') ; ?></option></select> <?php _e('alignment'); ?><br/>
                                <input style="width:73px;text-align:center;" id="qrMarginPage" name="qrMarginPage" value="<?php echo get_option('qrMarginPage') != '' ? get_option('qrMarginPage') : '10'; ?>" /> px &nbsp;- <?php _e('margin'); ?><br/>
                                <input style="width:73px;text-align:center;" id="qrImgSizePage" name="qrImgSizePage" value="<?php echo get_option('qrImgSizePage') != '' ? get_option('qrImgSizePage') : '120'; ?>" /> px &nbsp;- <?php _e('Image size'); ?><br/>
                            </td>
                        </tr>

                    </table>

                    <p>
                        <input type="checkbox" id="ad_setting_box">
                        <strong>Bonus</strong>: Use this plugin to serve <strong>Ad Codes</strong> using separate widget in Appearance->widgets. <br />
                    </p>


                    <div id="adsetings">
                        <h3 style="font-size:120%;margin-bottom:5px"><?php _e('Add your Adsense Code or any other script codes'); ?></h3>
                        <p style="margin-top:0px"><span class="description"><?php _e('Paste your <strong>Ad</strong> codes here and you will be able to show that <strong>Ad</strong>  by drag and drop CodeQRCode-AdSense widget in Appearance ->Widgets to desired position in your sidebar.') ?></span></p>

                        <h4><?php _e('Paste your Ad codes :'); ?></h4>
                        <table border="0" cellspacing="0" cellpadding="5">

                            <tr valign="top">
                                <td align="left" style="width:140px; padding-right: 5px"><strong>Ad1:</strong> <br/>Custom Ad name
                                    <input id="codeQRCodeAds1Name" name="codeQRCodeAds1Name" value="<?php echo stripslashes(htmlspecialchars(get_option('codeQRCodeAds1Name'))); ?>" placeholder="Optional Ad1 name"/>
                                </td>
                                <td align="left"><textarea style="margin:0 5px 3px 0; resize: none; overflow-y: scroll;text-align: left; height: 75px" id="codeQRCodeAds" name="codeQRCodeAds" rows="3" cols="45"><?php echo stripslashes(htmlspecialchars(get_option('codeQRCodeAds'))); ?></textarea></td>

                            </tr>
                            <tr valign="top">
                                <td align="left" style="width:140px; padding-right: 5px"><strong>Ad2:</strong> <br/>Custom Ad name
                                    <input id="codeQRCodeAds2Name" name="codeQRCodeAds2Name" value="<?php echo stripslashes(htmlspecialchars(get_option('codeQRCodeAds2Name'))); ?>" placeholder="Optional Ad2 name"/>
                                </td>
                                <td align="left"><textarea style="margin:0 5px 3px 0; resize: none; overflow-y: scroll;text-align: left; height: 75px" id="codeQRCodeAds2" name="codeQRCodeAds2" rows="3" cols="45"><?php echo stripslashes(htmlspecialchars(get_option('codeQRCodeAds2'))); ?></textarea></td>

                            </tr>
                            <tr valign="top">
                                <td align="left" style="width:140px; padding-right: 5px"><strong>Ad3:</strong> <br/>Custom Ad name
                                    <input id="codeQRCodeAds3Name" name="codeQRCodeAds3Name" value="<?php echo stripslashes(htmlspecialchars(get_option('codeQRCodeAds3Name'))); ?>" placeholder="Optional Ad3 name"/>
                                </td>
                                <td align="left"><textarea style="margin:0 5px 3px 0; resize: none; overflow-y: scroll;text-align: left; height: 75px" id="codeQRCodeAds3" name="codeQRCodeAds3" rows="4" cols="45"><?php echo stripslashes(htmlspecialchars(get_option('codeQRCodeAds3'))); ?></textarea></td>

                            </tr>

                        </table>
                    </div>

                    <input style ="margin: 15px 0px;" type="submit" value="<?php echo (_e("Save Changes")); ?>" />


                </form>
            </div>

        </div>


        <div style="clear:both"></div>
        <div style="margin-top: 20px; margin-left: 0px; width: 910px;" class="box">

        <?php if ($this->curlfailovao && get_option('codeQRCodeApplicationID') != ''): ?>
                <h2 style="color:red">Error communicating with CodeQRCode server, please refresh plugin page or try again later. </h2>
            <?php endif;?>
        <?php if(!$this->api_data_table->flag): ?>
            <a href="<?php echo $this->getSignupUrl(); ?>" target="_blank"><img style="border-radius:5px;border:0px;" src=" <?php echo plugins_url('images/teaser-810x262.png', __FILE__);?>" /></a>
        <?php else : ?>
            <!-- Start of dataTables -->
            <div id="codeQRCode-options">
                <h1>Your Dynamic QR Codes</h1>
                <div>In order to add new QR codes please <a href="http://www.codeqrcode.com/analytics" target="_blank">login to CodeQRCode</a></div>
            </div>
            <br>
            <table cellpadding="0" cellspacing="0" border="0"
                   class="responsive dynamicTable display table table-bordered" width="100%">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Data(URL/Contact name...)</th>
                    <th>QR type</th>
                    <th>Date Created</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->api_data_table->data as $item): ?>

                    <tr class="odd">
                        <td style="vertical-align: middle;" ><?php echo str_replace(' - Dynamic','',$item->name); ?></td>
                        <td style="vertical-align: middle;" ><a href="<?php echo $item->url; ?>" target="_blank"><?php echo $item->url; ?></a></td>
                        <td style="vertical-align: middle;" ><?php echo $item->qr_type; ?></td>
                        <td style="vertical-align: middle;" ><?php echo $item->qr_date; ?></td>

                    </tr>
                <?php endforeach; ?>

                </tbody>
                <tfoot>
                <tr>
                    <th>Name</th>
                    <th>Domain</th>
                    <th>QR type</th>
                    <th>Date Created</th>

                </tr>
                </tfoot>
            </table>
            </div>

        <?php endif; ?>

        <!-- load js scripts -->

        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="<?php echo content_url(); ?>/plugins/codeqrcode-adsense/assets/dataTables/jquery.dataTables.min.js"></script>


        <script type="text/javascript">
            function appIDChange(val) {

                $('#codeQRCodeSingleWidgetID option:first-child').val('');
                $('#codeQRCodePageWidgetID option:first-child').val('');

            }

            $(document).ready(function(){

                $('#adsetings').hide();
                $("#ad_setting_box").click(function () {
                    if($(this).is(":checked")){
                        $('#adsetings').show();
                    }else{
                        $('#adsetings').hide();
                    }

                });

                if ($('table').hasClass('dynamicTable')) {
                    $('.dynamicTable').dataTable({
                        "iDisplayLength": 10,
                        "sPaginationType": "full_numbers",
                        "bJQueryUI": false,
                        "bAutoWidth": false

                    });
                }
            });

        </script>

    <?php
    }


}


new CodeqrcodeWidget();
