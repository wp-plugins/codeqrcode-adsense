<?php
/*
Plugin Name: QRCode Generator - Adsense
Plugin URI: http://www.codeqrcode.com/
Description: CodeQRCode plugin enables you to automatically generate QR codes on each post and page on your wordpress site. It also enables you to place dynamic QR codes using widget. Dynamic QR code enables you to track number of scans, device type, and change URL destination.
Version: 1.1
Author: Aklamator
Author URI: http://www.codeqrcode.com/
License: GPL2

Copyright 2015 CodeQRCode.com (email : office@codeqrcode.com)

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/


/*
 * Add setting link on plugin page
 */

if( !function_exists("codeQRCode_plugin_settings_link")){

    function codeQRCode_plugin_settings_link($links) {
        $settings_link = '<a href="admin.php?page=codeqrcode-adsense">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}
add_filter("plugin_action_links_".plugin_basename(__FILE__), 'codeQRCode_plugin_settings_link',10 ,2);


/*
 * Activation Hook
 */

register_activation_hook( __FILE__, 'codeQRCode_set_up_options' );

function codeQRCode_set_up_options(){
    add_option('codeQRCodeApplicationID', '');
    add_option('codeQRCodePoweredBy', '');
    add_option('codeQRCodeSingleWidgetID', '');
    add_option('codeQRCodePageWidgetID', '');
    add_option('codeQRCodeSingleWidgetTitle', '');
    add_option('codeQRCodeShadow', 'on');

    // Ads codes
    add_option('codeQRCodeAds', '');
    add_option('codeQRCodeAds2', '');
    add_option('codeQRCodeAds3', '');

    // Custom Ads names
    add_option('codeQRCodeAds1Name', '');
    add_option('codeQRCodeAds2Name', '');
    add_option('codeQRCodeAds3Name', '');

    //Static QR codes
    //Single
    add_option('codeQRCodeSingle', '');
    add_option('enableQROnSingle', '');
    add_option('qrCodeAlignSingle', ''); //Settings for align qr code on single post
    add_option('qrMarginSingle', ''); //Settings for margin on single post
    add_option('qrImgSizeSingle', ''); //Settings for Image on single post
    //Page
    add_option('codeQRCodePage', '');
    add_option('enableQROnPage', '');
    add_option('qrCodeAlignPage', ''); //Settings for align qr code on single page
    add_option('qrMarginPage', ''); //Settings for margin on single page
    add_option('qrImgSizePage', ''); //Settings for image on single page

}

/*
 * Uninstall Hook
 */

register_uninstall_hook(__FILE__, 'codeQRCode_uninstall');

function codeQRCode_uninstall()
{
    delete_option('codeQRCodeApplicationID');
    delete_option('codeQRCodePoweredBy');
    delete_option('codeQRCodeSingleWidgetID');
    delete_option('codeQRCodePageWidgetID');
    delete_option('codeQRCodeSingleWidgetTitle');
    delete_option('codeQRCodeShadow');
    // Ads codes
    delete_option('codeQRCodeAds');
    delete_option('codeQRCodeAds2');
    delete_option('codeQRCodeAds3');
    // Custom Ad names
    delete_option('codeQRCodeAds1Name');
    delete_option('codeQRCodeAds2Name');
    delete_option('codeQRCodeAds3Name');
    // QR codes
    //Single
    delete_option('codeQRCodeSingle');
    delete_option('enableQROnSingle'); // Check box settings
    delete_option('qrCodeAlignSingle');
    delete_option('qrMarginSingle');
    delete_option('qrImgSizeSingle');
    //Page
    delete_option('codeQRCodePage');
    delete_option('enableQROnPage');
    delete_option('qrCodeAlignSingle');
    delete_option('qrMarginPage');
    delete_option('qrImgSizePage');

}


if (!function_exists("codeQRCode_bottom_post")) {
    function codeQRCode_bottom_post($content)
    {

        /*  we want to change `the_content` of posts, not pages
            and the text file must exist for this to work */

        if (is_single()) {


            if(get_option('enableQROnSingle') != ''){
                // Show static qr code
                if(get_option('codeQRCodeSingle') == 'static'){ // User is selected static qr code to be shown

                    return $content . show_qr_code_img('static', (int)get_option('qrImgSizeSingle'), get_option('qrCodeAlignSingle'), (int)get_option('qrMarginSingle'));

                }else{ // User is selected some dynamic qr code

                    return $content . show_qr_code_img(get_option('codeQRCodeSingle'), (int)get_option('qrImgSizeSingle'), get_option('qrCodeAlignSingle'), (int)get_option('qrMarginSingle'));
                }
            }
        } elseif (is_page()) {

            if(get_option('enableQROnPage') != ''){

                // Show static qr code
                if(get_option('codeQRCodePage') == 'static'){ // User is selected static qr code to be shown

                    return $content . show_qr_code_img('static', (int)get_option('qrImgSizePage'), get_option('qrCodeAlignPage'), (int)get_option('qrMarginPage'));

                }else{ // User is selected some dynamic qr code

                    return $content . show_qr_code_img(get_option('codeQRCodePage'), (int)get_option('qrImgSizePage'), get_option('qrCodeAlignPage'), (int)get_option('qrMarginPage'));
                }

            }

        } else { // For all other pages we need to return actual content

            return $content;
        }

        // If user disable qr codes for single/Page post we still need return content
        return $content;

    }

}

function show_qr_code_img($type, $img_size, $align, $margin, $called_from = ""){

    if($type == 'static'){

        $current_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '';

        $content = urlencode($current_uri);

        $image = 'https://chart.googleapis.com/chart?chs=' . $img_size . 'x' . $img_size . '&cht=qr&chld=H|1&chl=' . $content;

    }else{

        $image = 'http://www.codeqrcode.com/img_qr_urls/'.$type.'.png';
    }

    if (empty($align) && $align !==0) {
        $align = "center";
    } else {
        $align = strtolower($align);
    }

    if(empty($img_size)){
        $img_size = '120';
    }else{
        $img_size = strtolower($img_size);
        if($img_size <= 50)
            $img_size = 120;
    }

    ?>

<?php

    if(get_option('codeQRCodeShadow')){
        $style='box-shadow: 10px 10px 5px #888888;';
    }else{
        $style='';
    }


    $output = '<div style="float:'.$align.'; '.$style.'" class="qrCodeTable"><table>';

        // We need to check if user is entered title to be displayed above qr code
        if(get_option('codeQRCodeSingleWidgetTitle') !="" && $called_from =="") {
            $output.='<tr><td>' . get_option('codeQRCodeSingleWidgetTitle') . '</td></tr>';
        }

        //Img section
        $output.='<tr><td>' .
            '<img id="qr_code_generator_wprhe" src="' . $image . '" alt="Scan the QR Code" width="' . $img_size . '" height="' . $img_size . '" ' . $align . ' />' .
            '</td></tr>';

         // We need to check if PowerBy is checked in plugin settings
        if(get_option('codeQRCodePoweredBy') !=""){
            $output.= ' <tr> <td style="padding-top: 0px">' .
                '<a href="http://www.codeqrcode.com" target="blank" ><img src="'.plugins_url('images/made_with_love.png', __FILE__ ).'" border="0" alt="QR Code Generator"></a>' .
                ' </td></tr>';
        }

        $output.='</table></div>';
        $output.='<div style="clear:both"></div>';


    return $output;

}

// Include Admin section
require_once('codeqrcode-adsense-admin.php');

// Widget section

add_action( 'after_setup_theme', 'vw_setup_vw_widgets_init_codeQRCode' );

function vw_setup_vw_widgets_init_codeQRCode() {
    add_action( 'widgets_init', 'vw_widgets_init_codeQRCode' );
}

function vw_widgets_init_codeQRCode() {
    register_widget( 'Wp_widget_codeQRCode' );
    register_widget( 'Wp_widget_codeQRCode_adsense' );

    wp_register_style( 'cqc-css-plugin', plugins_url( 'assets/css/plugin.css',__FILE__ ) );
    wp_enqueue_style( 'cqc-css-plugin' );

}

class Wp_widget_codeQRCode extends WP_Widget {

    private $default = array(
        'supertitle' => '',
        'title' => '',
        'content' => '',
        'img_size' =>''
    );

    public function __construct() {
        // widget actual processes
        parent::__construct(
            'wp_widget_codeQRCode', // Base ID
            'codeQRCode widget', // Name
            array( 'description' => __( 'Display QR Codes in Sidebar')) // Args
        );

    }

    function widget( $args, $instance ) {
        extract($args);
        //var_dump($instance); die();

        $supertitle_html = '';
        if ( ! empty( $instance['supertitle'] ) ) {
            $supertitle_html = sprintf( __( '<span class="super-title">%s</span>', 'envirra' ), $instance['supertitle'] );
        }

        $title_html = '';
        if ( ! empty( $instance['title'] ) ) {
            $title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base);
            $title_html = $supertitle_html.$title;
        }


        if ( $instance['title'] ) echo $before_title . $title_html . $after_title;
        ?>
        <?php echo $this->show_widget(do_shortcode( $instance['widget_id'] ), $instance['img_size']); ?>
        <?php
        echo $before_title;

    }

    private function show_widget($widget_id, $img_size)
    {
        return show_qr_code_img($widget_id, $img_size, 'center', 0, 'widget');

    }

    function form( $instance ) {

        $widget_data = new codeQRCodeWidget();

        $instance = wp_parse_args( (array) $instance, $this->default );

        $supertitle = strip_tags( $instance['supertitle'] );
        $title = strip_tags( $instance['title'] );
        $content = $instance['content'];
        $widget_id = $instance['widget_id'];
        $img_size = $instance['img_size'];

        if($widget_data->api_data->data[0]): ?>

            <!-- title -->
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title (text shown above widget):','envirra-backend'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </p>
            <p>
                <!-- Select - dropdown -->
                <label for="<?php echo $this->get_field_id('widget_id'); ?>"><?php _e('Select QR code:','envirra-backend'); ?></label>
                <select id="<?php echo $this->get_field_id('widget_id'); ?>" name="<?php echo $this->get_field_name('widget_id'); ?>">
                    <?php foreach ( $widget_data->api_data->data as $item ): ?>
                        <option <?php echo ($widget_id == stripslashes(htmlspecialchars_decode($item->img_idkey)))? 'selected="selected"' : '' ;?> value="<?php echo addslashes(htmlspecialchars($item->img_idkey)); ?>"><?php echo $item->name; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('img_size'); ?>"><?php _e('QR image size','envirra-backend'); ?></label>
                <input style="width:73px;text-align:center;" id="<?php echo $this->get_field_id('img_size'); ?>" name="<?php echo $this->get_field_name('img_size'); ?>" value="<?php echo $img_size != '' ? $img_size : '120'; ?>" /> px &nbsp;
            </p>

            <br>
        <?php else :?>
            <br>
            <span style="color:red">Please make sure that you configured codeQRCode plugin correctly.</span>
            <a href="<?php echo admin_url(); ?>admin.php?page=codeqrcode-adsense"><br/>Click here to configure codeQRCode plugin</a>
            <br>
            <br>
        <?php endif;

    }
}


class Wp_widget_codeQRCode_adsense extends WP_Widget {

    private $default = array(

        'title_ad' => '',
        'content_ad' => '',

    );

    public function __construct() {
        // widget actual processes
        parent::__construct(
            'Wp_widget_codeQRCode_adsense', // Base ID
            'codeQRCode widget - Adsense', // Name
            array( 'description' => __( 'Display Adsence in Sidebar')) // Args
        );

    }

    function widget( $args, $instance ) {
        extract($args);
        //var_dump($instance); die();


        $title_html = '';
        if ( ! empty( $instance['title_ad'] ) ) {
            $title = apply_filters( 'widget_title', $instance['title_ad'], $instance, $this->id_base);
            $title_html = $title;
        }


        if ( $instance['title_ad'] ) echo $before_title . $title_html . $after_title;
        ?>
        <?php echo $this->show_widget(do_shortcode( $instance['ad_code'] )); ?>
        <?php
        echo $before_title;

    }

    private function show_widget($widget_id)
    {
        return $widget_id;

    }

    function form( $instance ) {

        $widget_data = new codeQRCodeWidget();

        $instance = wp_parse_args( (array) $instance, $this->default );

        $title_ad = strip_tags( $instance['title_ad'] );
        $ad_code = $instance['ad_code'];

        if($widget_data->ad_data[0]): ?>

            <!-- title -->
            <p>
                <label for="<?php echo $this->get_field_id('title_ad'); ?>"><?php _e('Title (text shown above Ad code):','envirra-backend'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title_ad'); ?>" name="<?php echo $this->get_field_name('title_ad'); ?>" type="text" value="<?php echo esc_attr($title_ad); ?>" />
            </p>
            <p>
                <!-- Select - dropdown -->
                <label for="<?php echo $this->get_field_id('ad_code'); ?>"><?php _e('Select QR code:','envirra-backend'); ?></label>
                <select id="<?php echo $this->get_field_id('ad_code'); ?>" name="<?php echo $this->get_field_name('ad_code'); ?>">
                    <?php foreach ( $widget_data->ad_data as $item ): ?>
                        <option <?php echo ($ad_code == stripslashes(htmlspecialchars_decode($item->uniq_name)))? 'selected="selected"' : '' ;?> value="<?php echo addslashes(htmlspecialchars($item->uniq_name)); ?>"><?php echo $item->title; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

            <br>
        <?php else :?>
            <br>
            <span style="color:red">Please make sure that you configured codeQRCode plugin correctly.</span>
            <a href="<?php echo admin_url(); ?>admin.php?page=codeqrcode-adsense"><br/>Click here to configure codeQRCode plugin</a>
            <br>
            <br>
        <?php endif;

    }
}