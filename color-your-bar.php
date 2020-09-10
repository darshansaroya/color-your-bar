<?php
/**
 * Plugin Name: Color Your Bar
 * Plugin URI: https://darshansaroya.com/product/color-your-bar-plugin
 * Description: A ultimate plugin to colorise the status bar for Mobile.
 * Author: Darshan Saroya
 * Author URI: http://darshansaroya.com
 * Version: 1.5
 * Text Domain: color-your-bar
 * Domain Path: languages
 *
 * Color Your Bar is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Color Your Bar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Color Your Bar. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Color Your Bar
 * @author Darshan Saroya
 * @version 1.5
 */

register_activation_hook( __FILE__, 'cyb_active_func' );

function cyb_active_func(){
	update_option( 'cyb-switch', 0 );
	update_option( 'cyb-ios-full-mode', 0 );
	update_option( 'cyb-post-type', array('post', 'page') );
}

add_action( 'admin_enqueue_scripts', 'cyb_assets' );
function cyb_assets() {
    wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'meta-box-color-js', plugin_dir_url( __FILE__ ) . 'color-picker.js', array( 'wp-color-picker' ) );
}

add_action( 'wp_head', 'cyb_add_head_tag');

function cyb_add_head_tag(){
	$enable_cyb= get_option('cyb-switch'); 
	$ios_full_mode= get_option('cyb-ios-full-mode'); 
	if($enable_cyb!='0'){
	global $post;
	$cyb_post_type = get_option( 'cyb-post-type');
	$cyb_color= get_option('cyb-color', esc_html('#ffffff'));
	if($post):
		$cyb_post = get_post_type($post->ID);
		if(null !== $cyb_post_type && is_array($cyb_post_type)){
			foreach ($cyb_post_type as $post_type ) {
				$color = get_post_meta( $post->ID, 'cyb-color', true );
				if($cyb_post == $post_type && $color !=''){
					$cyb_color = $color;
				}
			}
		}
	endif; ?>
	<meta name="theme-color" content="<?php echo esc_attr( $cyb_color ); ?>">
<?php }

if($ios_full_mode != '0'){
	$ios_style_mode= get_option('cyb-ios-bar-style', 'normal'); ?>
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="<?php echo esc_attr($ios_style_mode); ?>">
<?php }
}

//Adding setting page for plugin
function cyb_add_menu(){
    add_menu_page( 
        __( 'Color Your Bar', 'color-your-bar' ),
        __( 'Color Your Bar', 'color-your-bar' ),
        'manage_options',
        'color-your-bar',
        'cyb_menu_page',
        'dashicons-schedule',
        90
    ); 
}
add_action( 'admin_menu', 'cyb_add_menu' );
 
//Ading Setting Page Link in Plugin List page.
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'cyb_add_link');
function cyb_add_link( $links ) {
	$links[] = '<a href="' .
		admin_url( 'admin.php?page=color-your-bar' ) .
		'">' . __('Settings', 'color-your-bar') . '</a>';
	return $links;
}

//Page for Plugin
function cyb_menu_page(){ ?>
	<div class="wrap">
		<!-- run the settings_errors() function here. -->
            <?php settings_errors(); ?>
        <form method="post" action="options.php">
            <?php 
            //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
                settings_fields("cyb_setting_section");
                
                // all the add_settings_field callbacks is displayed here
                do_settings_sections('color-your-bar');
            
                // Add the submit button to serialize the options
                submit_button();
            ?>          
        </form>
    </div>
<?php }


function cyb_setting_display()
{
	add_settings_section("cyb_setting_section", esc_html__( 'Android Settings', 'color-your-bar' ), "cyb_content_callback", "color-your-bar");

    add_settings_field('cyb-switch', esc_html__( 'Enable Color Bar', 'color-your-bar' ), "cyb_color_switch_element", "color-your-bar", "cyb_setting_section");

    $cyb_switch_args = array(
        'type' => 'string', 
        'sanitize_callback' => 'cyb_sanitize_checkbox',
        'default' => 0,
    );
    register_setting("cyb_setting_section", 'cyb-switch', $cyb_switch_args);

    add_settings_field('cyb-post-type', esc_html__( 'Show Meta Box on Post Type', 'color-your-bar' ), "cyb_post_type_element", "color-your-bar", "cyb_setting_section");

    $cyb_post_type_args = array(
        'type' => 'array', 
        'sanitize_callback' => 'cyb_sanitize_array',
        'default' => 0,
    );
    register_setting("cyb_setting_section", 'cyb-post-type', $cyb_post_type_args);

    add_settings_field('cyb-color', esc_html__( 'Choose Color', 'color-your-bar' ), "cyb_color_field_element", "color-your-bar", "cyb_setting_section");

    $cyb_color_args = array(
        'type' => 'string', 
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '#ffffff',
    );
	register_setting("cyb_setting_section", 'cyb-color', $cyb_color_args);

	add_settings_field('cyb-ios-section', esc_html__( 'iOS Settings', 'color-your-bar' ), "cyb_ios_section_func", "color-your-bar", "cyb_setting_section");

	//ios full mode switch
	add_settings_field('cyb-ios-full-mode', esc_html__( 'Enable Full Screen Mode In Iphone', 'color-your-bar' ), "cyb_ios_switch_full_mode", "color-your-bar", "cyb_setting_section");

    $cyb_ios_full_mode_args = array(
        'type' => 'string', 
        'sanitize_callback' => 'cyb_sanitize_checkbox',
        'default' => 0,
    );
	register_setting("cyb_setting_section", 'cyb-ios-full-mode', $cyb_ios_full_mode_args);
	
	//ios style of status for web application
	add_settings_field('cyb-ios-bar-style', esc_html__( 'Select Style of status bar in IOS', 'color-your-bar' ), "cyb_ios_bar_style_func", "color-your-bar", "cyb_setting_section");

    $cyb_ios_status_style_args = array(
        'type' => 'string', 
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'normal',
    );
    register_setting("cyb_setting_section", 'cyb-ios-bar-style', $cyb_ios_status_style_args);

}

function cyb_content_callback(){
	esc_html_e( 'Settings to change Google Chrome(Mobile) address bar color', 'color-your-bar' );
}

function cyb_ios_section_func(){
	return '';
}

function cyb_ios_heading_callback(){
	esc_html_e( 'Status Bar Mode for iOS', 'color-your-bar' );
}
function cyb_color_field_element()
{
    //id and name of form element should be same as the setting name.
    ?>
        <input type="text" name='cyb-color' id='cyb-color' value="<?php echo get_option('cyb-color'); ?>" />
    <?php
}


function cyb_color_switch_element()
{
	$val= get_option('cyb-switch');
    //id and name of form element should be same as the setting name.
    ?>
        <input type="checkbox" name='cyb-switch' id='cyb-switch' value="1" <?php if($val!='0') echo esc_attr( 'checked' ); ?>>
    <?php
}

function cyb_ios_switch_full_mode()
{
	$ios_full_mode= get_option('cyb-ios-full-mode');
    //id and name of form element should be same as the setting name.
    ?>
        <input type="checkbox" name='cyb-ios-full-mode' id='cyb-ios-full-mode' value="1" <?php if($ios_full_mode!='0') echo esc_attr( 'checked' ); ?>>
		<p><?php esc_html_e( 'Sets whether a web application runs in full-screen mode.', 'color-your-bar' ); ?></p>
    <?php
}

function cyb_ios_bar_style_func(){
	$val= get_option('cyb-ios-bar-style');
    //id and name of form element should be same as the setting name.
    ?>
	<select name="cyb-ios-bar-style" id="cyb-ios-bar-style">
		<option value="normal" <?php if($val=='normal') echo esc_attr( 'selected' ); ?>><?php esc_html_e( 'Normal', 'color-your-bar' ); ?></option>
		<option value="black" <?php if($val=='black') echo esc_attr( 'selected' ); ?>><?php esc_html_e( 'Black', 'color-your-bar' ); ?></option>
		<option value="black-translucent" <?php if($val=='black-translucent') echo esc_attr( 'selected' ); ?>><?php esc_html_e( 'Translucent', 'color-your-bar' ); ?></option>
	</select>
	<p><?php esc_html_e( 'If content is set to Default, the status bar appears normal. If set to Black, the status bar has a black background. If set to Translucent, the status bar is black and translucent. If set to Default or Black, the web content is displayed below the status bar. If set to Translucent, the web content is displayed on the entire screen, partially obscured by the status bar. It\'s work only if full mode is set.', 'color-your-bar' ); ?></p>
    <?php
}

function cyb_post_type_element()
{
	$val= get_post_types ( array('public'   => true,));
    //id and name of form element should be same as the setting name.
    $post_selected = get_option('cyb-post-type');
    foreach ($val as $post_type) {
    ?>
        <input type="checkbox" name='cyb-post-type[]' value="<?php echo esc_attr($post_type); ?>" <?php  
        if(is_array($post_selected)){
        	foreach($post_selected as $type){
        		if($type == $post_type){ 
        			echo esc_attr( 'checked' ); 
        		}
        	} 
        } ?>> <?php echo esc_html($post_type); ?><br>
    <?php }
}

function cyb_sanitize_checkbox($input){
		if ( $input != 1 ) {
			return 0;
		} else {
			return 1;
		}
}

function cyb_sanitize_array($input){
		if ( !is_array( $input ) ) {
			return '';
		} else {
			return $input;
		}
}

add_action("admin_init", "cyb_setting_display");

function cyb_add_color_metaboxes() {
	$cyb_post_type = get_option( 'cyb-post-type' );
	foreach ($cyb_post_type as $post_type ) {
		add_meta_box(
			'cyb_color_meta',
			__('Chrome Bar Color', 'color-your-bar'),
			'cyb_color_meta',
			$post_type,
			'side',
			'default'
		);
	}
}

if( get_option( 'cyb-switch') != 0){
	add_action( 'add_meta_boxes', 'cyb_add_color_metaboxes' );
}

function cyb_color_meta() {
	global $post;
	// Nonce field to validate form request came from current site
	wp_nonce_field( basename( __FILE__ ), 'cyb-nonce' );
	// Get the cyb_color data if it's already been entered
	$cyb_color = get_option('cyb-color');
	if( null !== get_post_meta( $post->ID, 'cyb-color', true )  && get_post_meta( $post->ID, 'cyb-color', true )!=''){
		$cyb_color = get_post_meta( $post->ID, 'cyb-color', true );
	}
	// Output the field ?>
	<input type="text" name='cyb-color' id='cyb-color' value="<?php echo esc_html($cyb_color); ?>" />
<?php }

/**
 * Save the metabox data
 */
function cyb_save_meta( $post_id, $post ) {
	// Return if the user doesn't have edit permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	// Verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times.
	if ( ! isset( $_POST['cyb-color'] ) || ! wp_verify_nonce( $_POST['cyb-nonce'], basename(__FILE__) ) ) {
		return $post_id;
	}
	// Now that we're authenticated, time to save the data.
	// This sanitizes the data from the field and saves it into an array $cyb_meta.
	$cyb_meta['cyb-color'] = esc_textarea( $_POST['cyb-color'] );
	// Cycle through the $cyb_meta array.
	// Note, in this example we just have one item, but this is helpful if you have multiple.
	foreach ( $cyb_meta as $key => $value ) :
		// Don't store custom data twice
		if ( 'revision' === $post->post_type ) {
			return;
		}
		if ( get_post_meta( $post_id, $key, false ) ) {
			// If the custom field already has a value, update it.
			update_post_meta( $post_id, $key, $value );
		} else {
			// If the custom field doesn't have a value, add it.
			add_post_meta( $post_id, $key, $value);
		}
		if ( ! $value ) {
			// Delete the meta key if there's no value
			delete_post_meta( $post_id, $key );
		}
	endforeach;
}
add_action( 'save_post', 'cyb_save_meta', 1, 2 );

?>