<?php
/**
 * Plugin Name:     SJ Reading Time
 * Description:     Add estimated reading time to articles.
 * Author:          Virnamic
 * Author URI:      https://www.virnamic.com/
 * Plugin URI:      https://www.virnamic.com/products/sj-reading-time/
 * Text Domain:     sjrt-reading-time
 * Domain Path:     /languages
 * Version:         1.0.1
 * License:         GPLv2 or later
 * License URI:     https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * @package         Sjrt_Reading_Time
 */

 /*
    SJ Reading Time is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    any later version.

    SJ Reading Time is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with SJ Reading Time. If not, see {URI to Plugin License}.
*/

class SjrtReadingTimePlugin {
  function __construct() {
    // setup shortcode
    add_shortcode( 'sjrt_reading_time', array( $this, 'sjrt_reading_time_shortcode') );
    
    // initialize settings
    add_action( 'admin_init', array( $this, 'sjrt_settings_init'));

    // add submenu for settings
    add_action( 'admin_menu', array( $this, 'sjrt_options_page') );

    // on activation of plugin populate the default settings
    register_activation_hook( __FILE__, array($this, 'sjrt_options_init') ); 

    // on uninstallation of plugin clean up settings options
    register_uninstall_hook( __FILE__, array($this, 'sjrt_options_cleanup') );
  }

  /**
   * Count words in a give post and provide an estimate of read time
   * 
   * @return string|null
   */
  function sjrt_estimate_reading_time() {
    $wpm = get_option( 'sjrt_settings_wpm' );
    $include_images = get_option( 'sjrt_settings_include_images' );

    $reading_time = null;
    $post = get_post( get_the_ID() );
    $reading_time = str_word_count($post->{'post_content'}) / $wpm;

    // only include images in read time consideration when setting is checked.
    if($include_images == true) {
      $reading_time += ( substr_count( $post->{'post_content'},'<img' ) * 10 ) / 60;
    }
    return ceil( $reading_time );
   }

  /**
   * Shortcode to display reading time.
   * 
   * @return string
   */
  function sjrt_reading_time_shortcode( $atts ) {
    // normalize attribute keys, lowercase only!
    $atts = array_change_key_case( (array) $atts, CASE_LOWER );
    
    // Attributes
    $atts = shortcode_atts(
      array(
        'label' => esc_html__('Reading time:','sjrt-reading-time'),
        'postfix' => esc_html__('mins','sjrt-reading-time'),
        'postfix_singular' => esc_html__('min','sjrt-reading-time')
      ),
      $atts
    );
    $reading_time = $this->sjrt_estimate_reading_time();
    $reading_time .= $reading_time == 1 ? " " . $atts['postfix_singular'] : " " . $atts['postfix'];
    $reading_time = $atts['label'] ." ". $reading_time;

    return $reading_time;
  }

  /**
   * Administration page for managing options for plugin. Handles HTML presentation
   * 
   */
  function sjrt_options_page_html() {
    if( !current_user_can( 'manage_options' ) ){
      return;
    } 
    ?>
    <div class="wrap">
      <h1><?php echo esc_html__( 'Reading Time Configuration', 'sjrt-reading-time' );?></h1>
      <form action="options.php" method="post">
        <?php 
        settings_fields('sjrt');
        do_settings_sections( 'sjrt-reading-time' );
        submit_button( esc_html__('Save Settings', 'sjrt-reading-time') );
        ?>
      </form>
    </div>
    <?php
  }


  /**
   * Callback to display wpm field, method used with add_settings_field for id sjrt_settings_wpm.
   */
  function sjrt_setting_field_wpm_cb( $args ) {
    $setting_wpm = get_option('sjrt_settings_wpm'); ?>

    <input type="number" name="sjrt_settings_wpm" value="<?php echo isset($setting_wpm) ? esc_attr( $setting_wpm ) : '';  ?>">
    <p><?php echo esc_html__('By default the average wpm is set to 250.','sjrt-reading-time'); ?></p>
    <?php
  }

  /**
   * Callback to display wpm field, method used with add_settings_field for id sjrt_settings_include_images.
   */
  function sjrt_setting_field_include_images_cb( ) {
    $setting_include_image = get_option('sjrt_settings_include_images'); ?>

    <input type="checkbox" name="sjrt_settings_include_images" value="true" <?php echo $setting_include_image == true ? 'checked' : '' ?> >
    <p><?php echo esc_html__('By default an additional 10 seconds is added to your post read time for each image. Uncheck to remove additional time consideration.','sjrt-reading-time');?></p>
    <?php
  }

  /**
   * Callback to display section notes
   */
  function sjrt_section_settings_cb( ){
    ?>
    <?php
  }

  /**
   * Callback to display section notes
   * @todo: how to make these fields multi language ready
   */
  function sjrt_section_settings_shortcode_cb( ){
    ?>
    <p><?php echo esc_html__('The shortcode supports 3 attributes: ','sjrt-reading-time'); ?><strong>label</strong>, <strong>postfix</strong>, <strong>postfix_singular</strong>.</p>
    <p>
      <strong><?php echo esc_html__('label','sjrt-reading-time'); ?></strong> - <?php echo esc_html__('text which should be shown before estimated read time.','sjrt-reading-time'); ?> (<strong><?php echo esc_html__('default','sjrt-reading-time');?></strong> - <?php echo esc_html__('"Reading time:"','sjrt-reading-time');?>) <br/>
      <strong><?php echo esc_html__('postfix','sjrt-reading-time'); ?></strong> - <?php echo esc_html__('text which should be shown after estimated read time. This value is used when read time is greater than 1 minute.','sjrt-reading-time'); ?> (<strong><?php echo esc_html__('default','sjrt-reading-time');?></strong> - <?php echo esc_html__('"mins"','sjrt-reading-time');?>)<br/>
      <strong><?php echo esc_html__('postfix_singular','sjrt-reading-time'); ?></strong> - <?php echo esc_html__('text which should be shown after estimated read time. This value is used when read time is 1 minute.','sjrt-reading-time'); ?> (<strong><?php echo esc_html__('default','sjrt-reading-time');?></strong> - <?php echo esc_html__('"min"','sjrt-reading-time');?>)
    </p>
    <code>
      [sjrt_reading_time label="<?php echo esc_html__('Reading time:','sjrt-reading-time');?>" postfix="<?php echo esc_html__('mins','sjrt-reading-time');?>" postfix_singular="<?php echo esc_html__('min','sjrt-reading-time');?>"]
    </code>

    <p>
      <strong><?php echo esc_html__('Note:','sjrt-reading-time'); ?></strong> <?php echo esc_html__('each attribute is optional, for cases where no value is provided for an attribute the default will be returned.','sjrt-reading-time');?> <br/>
    </p>
    <?php
  }

  /**
  * Register settings fields
  */
  function sjrt_settings_init() {
    register_setting ('sjrt','sjrt_settings_wpm');
    register_setting ('sjrt','sjrt_settings_include_images');

    add_settings_section('sjrt_section_settings',esc_html('Reading Time Configuration','sjrt-reading-time'),array( $this,'sjrt_section_settings_cb' ),'sjrt-reading-time');
    add_settings_section('sjrt_section_settings_shortcode',esc_html__('Shortcode','sjrt-reading-time'),array( $this,'sjrt_section_settings_shortcode_cb' ),'sjrt-reading-time');

    add_settings_field('sjrt_settings_wpm', esc_html__('Words Per Minute','sjrt-reading-time'), array( $this, 'sjrt_setting_field_wpm_cb' ),'sjrt-reading-time','sjrt_section_settings', array('label_for' => 'sjrt_settings_wpm'));
    add_settings_field('sjrt_settings_include_images', esc_html__('Include images?','sjrt-reading-time'), array( $this, 'sjrt_setting_field_include_images_cb'), 'sjrt-reading-time', 'sjrt_section_settings', array('label_for' => 'sjrt_settings_include_images'));
  }

  /**
  * Setup options page
  */
  function sjrt_options_page() {
    add_options_page(
      esc_html__('SJ Reading Time Settings','sjrt-reading-time'),
      esc_html__('SJ Reading Time','sjrt-reading-time'),
      'manage_options',
      'sjrt-reading-time',
      array( $this, 'sjrt_options_page_html' )
    );
  }

  function sjrt_options_init() {
    // Set default value for properties:
    add_option('sjrt_settings_wpm','250');
    add_option('sjrt_settings_include_images','true');
  }

  /**
   * Triggered on plugin uninstallation to clean up saved options
   */
  function sjrt_options_cleanup() {
    delete_option('sjrt_settings_wpm');
    delete_option('sjrt_settings_include_images');
  }

}

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$sjrt_reading_time = new SjrtReadingTimePlugin();