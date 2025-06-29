<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2022 ThemePunch
 */

namespace Nwdthemes\Revslider\Model\Revslider\Admin;

use \Nwdthemes\Revslider\Helper\Data;
use \Nwdthemes\Revslider\Helper\Framework;
use \Nwdthemes\Revslider\Model\FrameworkAdapter as FA;
use \Nwdthemes\Revslider\Model\Revslider\Admin\Includes\RevSliderAddons;
use \Nwdthemes\Revslider\Model\Revslider\Admin\Includes\RevSliderFolder;
use \Nwdthemes\Revslider\Model\Revslider\Admin\Includes\RevSliderFunctionsAdmin;
use \Nwdthemes\Revslider\Model\Revslider\Admin\Includes\RevSliderHelp;
use \Nwdthemes\Revslider\Model\Revslider\Admin\Includes\RevSliderLicense;
use \Nwdthemes\Revslider\Model\Revslider\Admin\Includes\RevSliderLoadBalancer;
use \Nwdthemes\Revslider\Model\Revslider\Admin\Includes\RevSliderPluginUpdate;
use \Nwdthemes\Revslider\Model\Revslider\Admin\Includes\RevSliderSliderExport;
use \Nwdthemes\Revslider\Model\Revslider\Admin\Includes\RevSliderSliderExportHtml;
use \Nwdthemes\Revslider\Model\Revslider\Admin\Includes\RevSliderSliderImport;
use \Nwdthemes\Revslider\Model\Revslider\Admin\Includes\RevSliderTemplate;
use \Nwdthemes\Revslider\Model\Revslider\Admin\Includes\RevSliderTooltips;
use \Nwdthemes\Revslider\Model\Revslider\ExternalSources\RevSliderFacebook;
use \Nwdthemes\Revslider\Model\Revslider\ExternalSources\RevSliderFlickr;
use \Nwdthemes\Revslider\Model\Revslider\ExternalSources\RevSliderYoutube;
use \Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderFunctions;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderFavorite;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderGlobals;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderNavigation;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderObjectLibrary;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderOutput;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderSlide;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderSlider;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderUpdate;
use \Nwdthemes\Revslider\Model\Revslider\RSColorpicker;

class RevSliderAdmin extends RevSliderFunctionsAdmin {
	private $view			 = 'slider';
    private $user_role       = 'administrator';
    private $global_settings = array();
	private $screens		 = array(); //holds all RevSlider Relevant screens in it
	private $allowed_views	 = array('sliders', 'slider', 'slide', 'update'); //holds pages, that are allowed to be included
	private $pages			 = array('revslider'); //, 'revslider_navigation', 'rev_addon', 'revslider_global_settings'
	private $dev_mode		 = false;
	private $path_views;

	/**
	 * construct admin part
	 **/
	public function __construct() {

		if(!file_exists(Framework::$RS_PLUGIN_PATH.'admin/assets/js/plugins/utils.min.js') && !file_exists(Framework::$RS_PLUGIN_PATH.'admin/assets/js/modules/editor.min.js')){
			$this->dev_mode = true;
		}

		$this->path_views = Framework::$RS_PLUGIN_PATH . 'admin/views/';
		$this->global_settings = $this->get_global_settings();

		$this->set_current_page();
		$this->set_user_role();
		$this->do_update_checks();
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * enqueue all admin styles
	 **/
	public function enqueue_admin_styles(){

        $f     = new RevSliderFunctions();
        $gs     = $f->get_global_settings();
        $fdl = $f->get_val($gs, 'fontdownload', 'off');
        if($fdl === 'off'){
            $url_css = $f->modify_fonts_url('https://fonts.googleapis.com/css?family=');
            $url_material = str_replace('css?', 'icon?', $url_css);
            FA::wp_enqueue_style('rs-open-sans', $url_css.'Open+Sans:400,300,700,600,800');
            FA::wp_enqueue_style('rs-roboto', $url_css.'Roboto');
            FA::wp_enqueue_style('tp-material-icons', $url_material.'Material+Icons');
        }elseif($fdl === 'preload'){
            $fonts = array('Open Sans' => 'Open+Sans:400%2C300%2C700%2C600%2C800', 'Roboto' => 'Roboto:400%2C300%2C700%2C500');//, 'Material Icons' => 'Material+Icons'
            $html = $f->preload_fonts($fonts);
            if(!empty($html)) echo $html;
            echo "\n<style>@font-face {
    font-family: 'Material Icons';
    font-style: normal;
    font-weight: 400;
    src: local('Material Icons'),
    local('MaterialIcons-Regular'),
    url(".Framework::$RS_PLUGIN_URL."public/assets/fonts/material/MaterialIcons-Regular.woff2) format('woff2'),
    url(".Framework::$RS_PLUGIN_URL."public/assets/fonts/material/MaterialIcons-Regular.woff) format('woff'),
    url(".Framework::$RS_PLUGIN_URL."public/assets/fonts/material/MaterialIcons-Regular.ttf) format('truetype');
}
.material-icons {
    font-family: 'Material Icons';
    font-weight: normal;
    font-style: normal;
        font-size: inherit;
    display: inline-block;
    text-transform: none;
    letter-spacing: normal;
    word-wrap: normal;
    white-space: nowrap;
    direction: ltr;
    vertical-align: top;
    line-height: inherit;
    /* Support for IE. */
    font-feature-settings: 'liga';

    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
    -moz-osx-font-smoothing: grayscale;
}
</style>\n";
        }//disable => load on your own

		FA::wp_enqueue_style(array('wp-jquery-ui', 'wp-jquery-ui-core', 'wp-jquery-ui-dialog', 'wp-color-picker'));

        if(FA::is_rtl()){
            FA::wp_enqueue_style('rs-new-plugin-settings-rtl', Framework::$RS_PLUGIN_URL . 'admin/assets/css/builder-rtl.css', array('rs-new-plugin-settings'), Framework::RS_REVISION);
        }
	}

	/**
	 * enqueue all admin scripts
	 **/
	public function enqueue_admin_scripts(){

		FA::wp_enqueue_script(array('jquery', 'jquery-ui-core', 'jquery-ui-mouse', 'jquery-ui-accordion', 'jquery-ui-datepicker', 'jquery-ui-dialog', 'jquery-ui-slider', 'jquery-ui-autocomplete', 'jquery-ui-sortable', 'jquery-ui-droppable', 'jquery-ui-tabs', 'jquery-ui-widget', 'wp-color-picker', 'wpdialogs', 'updates'));
		FA::wp_enqueue_script(array('wp-color-picker'));

        FA::wp_localize_script('revbuilder-admin', 'RVS_LANG', $this->get_javascript_multilanguage()); //Load multilanguage for JavaScript
        $view = $this->get_val(Data::$_GET, 'view');
        if ($view == 'slide') {
            FA::add_action('admin_print_scripts', array($this, 'add_editor_mode'), 1);
        }
    }

    /**
     * adds needed JavaScript to the header
     * to tell the scripts that we are in the editor
     * @since: 6.4.0
     **/
    public function add_editor_mode(){
		echo '<script>'."\n";
        echo "var _R_is_Editor = 'true';\n";
        echo '</script>'."\n";
	}

	/**
	 * add all js and css needed for media upload
	 */
	protected static function add_media_upload_includes(){
		if(function_exists('wp_enqueue_media')){
			wp_enqueue_media();
		}

		FA::wp_enqueue_script('thickbox');
		FA::wp_enqueue_script('media-upload');
		FA::wp_enqueue_style('thickbox');
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain(){
		FA::load_plugin_textdomain('revslider', false, dirname(Framework::RS_PLUGIN_SLUG_PATH) . '/languages/');
		FA::load_plugin_textdomain('revsliderhelp', false, dirname(Framework::RS_PLUGIN_SLUG_PATH) . '/languages/');
	}

	/**
	 * set the user role, to restrict plugin usage to certain groups
	 * @since: 6.0
	 **/
	public function set_user_role(){
		$this->user_role = $this->get_val($this->global_settings, 'permission', 'administrator');
		if($this->user_role === 'admin') $this->user_role = 'administrator';
		if(!in_array($this->user_role, array('author', 'editor', 'administrator'))) $this->user_role = 'administrator';

		switch($this->user_role){
			case 'author':
				$this->user_role = 'edit_published_posts';
			break;
			case 'editor':
				$this->user_role = 'edit_pages';
			break;
			default:
			case 'admin':
			case 'administrator':
				$this->user_role = 'manage_options';
			break;
		}
	}

	/**
	 *  custom output function
	 */
	public static function custom_post_fields_output(){
		$slider = new RevSliderSlider();
		$output = array();
		$output['default'] = 'default';

		$meta = FA::get_post_meta(get_the_ID(), 'slide_template', true);
		$meta = ($meta == '') ? 'default' : $meta;

		$page_bg = FA::get_post_meta(get_the_ID(), 'rs_page_bg_color', true);
		$page_bg = ($page_bg == '') ? '' : $page_bg;

		$blank = get_page_template_slug(get_the_ID()) == "../public/views/revslider-page-template.php";
		$blankcheck = $blank ? 'checked' : '';
		$hide_page_bg =  $blank ? '' : 'style="display:none;"';


		$slides = $slider->get_sliders_with_slides_short('template');
		$output = $output + $slides; //union arrays

		$latest_version	= FA::get_option('revslider-latest-version', Framework::RS_REVISION);

		?>
		<ul class="revslider_settings _TPRB_">
			<li id="slide_template_row">
				<label class="rs_wp_ppset" for="revslider_blank_template"><?php echo __('Blank Template'); ?></label><input id="rs_blank_template" name="rs_blank_template" <?php echo $blankcheck;?> class="" type="checkbox" >
			</li>
			<li id="slide_template_row">
				<div id="rs_page_bg_color_column" class="" <?php echo $hide_page_bg;?>>
					<label class="rs_wp_ppset"><?php echo __('Page Color');?></label><input type="text" data-editing="<?php echo __('Background Color');?>" name="rs_page_bg_color" id="rs_page_bg_color" class="my-color-field" value="<?php echo $page_bg; ?>">
				</div>
				<div class="clear"></div>
			</li>
			<li id="slide_template_row">
                <label class="rs_wp_ppset" id="slide_template_text"><?php echo __('Slide Template');?></label><select style="max-width:82px" name="slide_template" id="slide_template">
                <?php
                foreach($output as $handle => $name){
                    echo '<option ' . FA::selected($handle, $meta) . ' value="' . $handle . '">' . $name . '</option>';
                }
                ?></select>
			</li>
			<li id="slide_template_row" style="margin-top:40px">
				<solidiconbox><i class="material-icons">flag</i></solidiconbox><div class="pli_twoline_wp"><div class="pli_subtitle"><?php echo __('Installed Version');?></div><div class="dynamicval pli_subtitle"><?php echo Framework::RS_REVISION; ?></div></div>
				<div class="div5"></div>
				<solidiconbox id="available_version_icon"><i class="material-icons">cloud_download</i></solidiconbox><div id="available_version_content" class="pli_twoline_wp"><div class="pli_subtitle"><?php echo __('Available Version');?></div><div class="available_latest_version dynamicval pli_subtitle"><?php echo $latest_version; ?></div></div>
			</li>
			<li>
				<div class="rs_wp_plg_act_wrapper"><span><?php echo __('Unlock All Features');?></span></div>
			</li>
		</ul>

		<?php
	}

	/**
	 *
	 * on save post meta. Update metaboxes data from post, add it to the post meta
	 * @before: RevSliderBaseAdmin::onSavePost();
	 */
	public static function on_save_post(){
        $f = RevSliderGlobals::instance()->get('RevSliderFunctions');

		$post_id = $f->get_post_var('ID');

		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id; //protection against autosave
		if(empty($post_id)) return false;

		// Slide Template
		$slide_template = $f->get_post_var('slide_template');
        if(in_array($slide_template, array('', 'default'))){
            FA::delete_post_meta($post_id, 'slide_template');
        }else{
            FA::update_post_meta($post_id, 'slide_template', $slide_template);
        }

		// Blank Page Template Background Color
		$rs_page_bg_color = $f->get_post_var('rs_page_bg_color');
        if(strtolower($rs_page_bg_color) === '#ffffff'){
            FA::delete_post_meta($post_id, 'rs_page_bg_color');
        }else{
            FA::update_post_meta($post_id, 'rs_page_bg_color', $rs_page_bg_color);
        }

		// Set/Unset Blank Template depending on Blank Template Switch
		$rs_blank_template = $f->get_post_var('rs_blank_template');
        if(empty($rs_blank_template) && !empty($rs_page_bg_color) && FA::get_post_meta($post_id, '_wp_page_template', true) == '../public/views/revslider-page-template.php'){
			FA::update_post_meta($post_id, '_wp_page_template','');
		}
        if(!empty($rs_blank_template) &&  $rs_blank_template == 'on'){
			FA::update_post_meta($post_id, '_wp_page_template','../public/views/revslider-page-template.php');
		}
	}


	/**
	 * we dont want to show notices in our plugin
	 **/
	public function hide_notices(){
		if(in_array($this->get_val(Data::$_GET, 'page'), $this->pages)){
			remove_all_actions('admin_notices');
		}
	}

	/**
	 * check if we need to search for updates, if yes. Do them
	 **/
	private function do_update_checks(){
		$upgrade	= new RevSliderUpdate(Framework::RS_REVISION);
		$library	= new RevSliderObjectLibrary();
		$template	= new RevSliderTemplate();
		$validated	= FA::get_option('revslider-valid', 'false');
		$stablev	= FA::get_option('revslider-stable-version', '0');

        $uol = isset(Data::$_REQUEST['update_object_library']);
		$library->_get_list($uol);

        $us = isset(Data::$_REQUEST['update_shop']);
		$template->_get_template_list($us);

        $upgrade->force = in_array($this->get_val(Data::$_REQUEST, 'checkforupdates', 'false'), array('true', true), true);
		$upgrade->_retrieve_version_info();

        $upgrade->add_update_checks();
	}

	/**
	 * Add Classes to the WordPress body
	 * @since    6.0
     * @param string $classes
     * @return string
     */
	function modify_admin_body_class($classes){
		$classes .= ($this->get_val(Data::$_GET, 'page') == 'revslider' && $this->get_val(Data::$_GET, 'view') == 'slide') ? ' rs-builder-mode' : '';
		$classes .= ($this->_truefalse($this->get_val($this->global_settings, 'highContrast', false)) === true && $this->get_val(Data::$_GET, 'page') === 'revslider') ? ' rs-high-contrast' : '';

		return $classes;
	}


	/**
	 * Add all actions that the backend needs here
	 **/
	public function add_actions(){
		$pagenow = false;

        $cache = RevSliderGlobals::instance()->get('RevSliderCache');

		FA::add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
		FA::add_action('admin_head', array($this, 'hide_notices'), 1);
		FA::add_action('save_post', array($this, 'on_save_post'));
		FA::add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
		FA::add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
		FA::add_action('wp_ajax_revslider_ajax_action', array($this, 'do_ajax_action')); //ajax response to save slider options.
		FA::add_action('wp_ajax_revslider_ajax_call_front', array($this, 'do_front_ajax_action'));
		FA::add_action('wp_ajax_nopriv_revslider_ajax_call_front', array($this, 'do_front_ajax_action')); //for not logged in users

		FA::add_action('save_post', array($cache, 'check_for_post_transient_deletion'));
		FA::add_action('future_to_publish', array($cache, 'check_for_post_transient_deletion'));
		FA::add_action('publish_post', array($cache, 'check_for_post_transient_deletion'));
		FA::add_action('publish_future_post', array($cache, 'check_for_post_transient_deletion'));

		FA::add_action('admin_init', array($this, 'merge_addon_notices'), 99);
		FA::add_action('admin_init', array($this, 'add_suggested_privacy_content'), 15);

		$instagram = RevSliderGlobals::instance()->get('RevSliderInstagram');
		$instagram->add_actions();

		$facebook = RevSliderGlobals::instance()->get('RevSliderFacebook');
		$facebook->add_actions();
	}

	/**
	 * Add all filters that the backend needs here
	 **/
	public function add_filters(){
		FA::add_filter('admin_body_class', array($this, 'modify_admin_body_class'));
		FA::add_filter('plugin_locale', array($this, 'change_lang'), 10, 2);
	}

	/**
	 * Change the language of the Slider Backend even if WordPress is set to be a different language
	 * @since: 6.1.6
	 **/
	public function change_lang($locale, $domain = ''){
		return (in_array($domain, array('revslider', 'revsliderhelp'), true)) ? $this->get_val($this->global_settings, 'lang', 'default') : $locale;
	}

	/**
	 * merge the revslider addon notices into one bigger notice
	 * @since: 2.2.0
	 **/
	public function merge_addon_notices(){
		$wp_filter = array();

		if(!isset($wp_filter['admin_notices'])) return;
		if(!isset($wp_filter['admin_notices']->callbacks)) return;

		$slugs = array(
			'Revslider_404_Addon_Verify', 'RsAddOnBackupNotice', 'RsAddOnBeforeAfterNotice', 'RsAddOnBubblemorphNotice', 'Revslider_Domain_Switch_Addon_Verify',
			'RsAddOnDuotoneNotice', 'RsAddOnExplodinglayersNotice', 'Revslider_Featured_Addon_Verify', 'RsAddOnFilmstripNotice', 'Revslider_Gallery_Addon_Verify',
			'RsAddOnLiquideffectNotice', 'Revslider_Login_Addon_Verify', 'Revslider_Maintenance_Addon_Verify', 'RsAddOnMousetrapNotice', 'RsAddOnPaintbrushNotice',
			'RsAddOnPanoramaNotice', 'RsAddOnParticlesNotice', 'RsAddOnPolyfoldNotice', 'Revslider_Prev_Next_Addon_Verify', 'RsAddOnRefreshNotice',
			'Revslider_Related_Posts_Addon_Verify', 'RsAddOnRevealerNotice', 'RsAddOnShapebuilderNotice', 'Revslider_Sharing_Addon_Verify', 'RsAddOnSliceyNotice',
			'RsAddOnSnowNotice', 'RsAddOnSunbeamNotice', 'RsAddOnTypewriterNotice', 'Revslider_Weather_Addon_Verify', 'Revslider_Whiteboard_Addon_Verify',
			'Revslider_Whiteboard_Addon_Verify'
		);

		foreach($wp_filter['admin_notices']->callbacks as $k => $o){
			if(!empty($o)){
				foreach($o as $ok => $f){
					if(!isset($f['function'])) continue;
					if(!is_array($f['function'])) continue;
					if(!isset($f['function'][0])) continue;
					if(!is_object($f['function'][0])) continue;


					$class = get_class($f['function'][0]);
					if(in_array($class, $slugs, true)){
						unset($wp_filter['admin_notices']->callbacks[$k][$ok]);
						Framework::$revslider_addon_notice_merged++;
					}
				}
			}
		}
		if(Framework::$revslider_addon_notice_merged > 0){
			FA::add_action('admin_notices', array($this, 'add_addon_plugins_page_notices'));
		}
	}

	/**
	 * add addon merged notices
	 * @since: 6.2.0
	 **/
	public function add_addon_plugins_page_notices(){
		?>
		<div class="error below-h2 soc-notice-wrap revaddon-notice" style="display: none;">
            <p><?php echo __('Action required for Slider Revolution AddOns: Please <a href="https://www.sliderrevolution.com/manual-section/manual/getting-started/quick-setup/" target="_blank" rel="noopener">install</a>/<a href="https://www.sliderrevolution.com/manual-section/manual/getting-started/quick-setup/register-plugin/" target="_blank">activate</a>/<a href="https://www.sliderrevolution.com/manual-section/manual/getting-started/quick-setup/update-plugin/" target="_blank">update</a> Slider Revolution</a>'); ?><span data-addon="rs-addon-notice" data-noticeid="rs-addon-merged-notices" style="float: right; cursor: pointer" class="revaddon-dismiss-notice dashicons dashicons-dismiss"></span></p>
		</div>
		<?php
	}

	/**
	 * Show message for activation benefits
	 **/
	public static function show_purchase_notice($plugin_file, $plugin_data, $plugin_status){
		?>
		<p>
            <?php echo __('Activate Slider Revolution for <a href="https://www.sliderrevolution.com/premium-slider-revolution/" target="_blank">premium benefits (e.g. live updates)</a>.');?>
		</p>
        <?php
	}

	/**
	 * Add the suggested privacy policy text to the policy postbox.
	 */
	public function add_suggested_privacy_content() {
		if(function_exists('wp_add_privacy_policy_content')){
			$content = $this->get_default_privacy_content();
			wp_add_privacy_policy_content(__('Slider Revolution'), $content);
		}
	}

	/**
	 * Return the default suggested privacy policy content.
	 *
	 * @return string The default policy content.
	 */
	public function get_default_privacy_content(){
		return __('<h2>In case you’re using Google Web Fonts (default) or playing videos or sounds via YouTube or Vimeo in Slider Revolution we recommend to add the corresponding text phrase to your privacy police:</h2>
		<h3>YouTube</h3> <p>Our website uses plugins from YouTube, which is operated by Google. The operator of the pages is YouTube LLC, 901 Cherry Ave., San Bruno, CA 94066, USA.</p> <p>If you visit one of our pages featuring a YouTube plugin, a connection to the YouTube servers is established. Here the YouTube server is informed about which of our pages you have visited.</p> <p>If you\'re logged in to your YouTube account, YouTube allows you to associate your browsing behavior directly with your personal profile. You can prevent this by logging out of your YouTube account.</p> <p>YouTube is used to help make our website appealing. This constitutes a justified interest pursuant to Art. 6 (1) (f) DSGVO.</p> <p>Further information about handling user data, can be found in the data protection declaration of YouTube under <a href="https://www.google.de/intl/de/policies/privacy" target="_blank" rel="noopener">https://www.google.de/intl/de/policies/privacy</a>.</p>
		<h3>Vimeo</h3> <p>Our website uses features provided by the Vimeo video portal. This service is provided by Vimeo Inc., 555 West 18th Street, New York, New York 10011, USA.</p> <p>If you visit one of our pages featuring a Vimeo plugin, a connection to the Vimeo servers is established. Here the Vimeo server is informed about which of our pages you have visited. In addition, Vimeo will receive your IP address. This also applies if you are not logged in to Vimeo when you visit our plugin or do not have a Vimeo account. The information is transmitted to a Vimeo server in the US, where it is stored.</p> <p>If you are logged in to your Vimeo account, Vimeo allows you to associate your browsing behavior directly with your personal profile. You can prevent this by logging out of your Vimeo account.</p> <p>For more information on how to handle user data, please refer to the Vimeo Privacy Policy at <a href="https://vimeo.com/privacy" target="_blank" rel="noopener">https://vimeo.com/privacy</a>.</p>
		<h3>Google Web Fonts</h3> <p>For uniform representation of fonts, this page uses web fonts provided by Google. When you open a page, your browser loads the required web fonts into your browser cache to display texts and fonts correctly.</p> <p>For this purpose your browser has to establish a direct connection to Google servers. Google thus becomes aware that our web page was accessed via your IP address. The use of Google Web fonts is done in the interest of a uniform and attractive presentation of our plugin. This constitutes a justified interest pursuant to Art. 6 (1) (f) DSGVO.</p> <p>If your browser does not support web fonts, a standard font is used by your computer.</p> <p>Further information about handling user data, can be found at <a href="https://developers.google.com/fonts/faq" target="_blank" rel="noopener">https://developers.google.com/fonts/faq</a> and in Google\'s privacy policy at <a href="https://www.google.com/policies/privacy/" target="_blank" rel="noopener">https://www.google.com/policies/privacy/</a>.</p>
		<h3>SoundCloud</h3><p>On our pages, plugins of the SoundCloud social network (SoundCloud Limited, Berners House, 47-48 Berners Street, London W1T 3NF, UK) may be integrated. The SoundCloud plugins can be recognized by the SoundCloud logo on our site.</p>
			<p>When you visit our site, a direct connection between your browser and the SoundCloud server is established via the plugin. This enables SoundCloud to receive information that you have visited our site from your IP address. If you click on the “Like” or “Share” buttons while you are logged into your SoundCloud account, you can link the content of our pages to your SoundCloud profile. This means that SoundCloud can associate visits to our pages with your user account. We would like to point out that, as the provider of these pages, we have no knowledge of the content of the data transmitted or how it will be used by SoundCloud. For more information on SoundCloud’s privacy policy, please go to https://soundcloud.com/pages/privacy.</p><p>If you do not want SoundCloud to associate your visit to our site with your SoundCloud account, please log out of your SoundCloud account.</p>');
	}

	/**
	 * The Ajax Action part for backend actions only
	 **/
	public function do_ajax_action(){
		$slider	= new RevSliderSlider();
		$slide	= new RevSliderSlide();

		$action	= $this->get_request_var('client_action');
		$data	= $this->get_request_var('data', '', false);
		$data	= ($data == '') ? array() : $data;
		$nonce	= $this->get_request_var('nonce');
		$nonce	= (empty($nonce)) ? $this->get_request_var('rs-nonce') : $nonce;

        $no_cache = array('get_template_information_short', 'export_slider', 'export_slider_html', 'getSliderImage', 'getSliderSizeLayout', 'get_list_of', 'load_wordpress_object', 'get_global_settings', 'get_slides_by_slider_id', 'get_full_slider_object', 'load_builder', 'subscribe_to_newsletter', 'check_system', 'get_layers_by_slide', 'export_layer_group', 'load_wordpress_image', 'load_library_image', 'get_help_directory', 'get_tooltips', 'get_addons_sizes', 'get_v5_slider_list');

		try{
			if(Framework::RS_DEMO){
				switch ($action){
					case 'get_template_information_short':
					case 'import_template_slider':
					case 'install_template_slider':
					case 'install_template_slide':
					case 'get_list_of':
					case 'get_global_settings':
					case 'get_full_slider_object':
					case 'subscribe_to_newsletter':
					case 'check_system':
					case 'load_module':
					case 'get_addon_list':
					case 'get_layers_by_slide':
					case 'silent_slider_update':
					case 'get_help_directory':
					case 'set_tooltip_preference':
					case 'load_builder':
					case 'load_library_object':
					case 'get_tooltips':
					//case 'preview_slider':
						//these are all okay in demo mode
					break;
					default:
						$this->ajax_response_error(__('Function Not Available in Demo Mode'));
						exit;
					break;
				}
			}

			if(!FA::current_user_can($this->user_role) && FA::apply_filters('revslider_restrict_role', true)){
				switch($action){
					case 'activate_plugin':
					case 'deactivate_plugin':
					case 'import_template_slider':
					case 'install_template_slider':
					case 'install_template_slide':
					case 'import_slider':
					case 'delete_slider':
					case 'create_navigation_preset':
					case 'delete_navigation_preset':
					case 'save_navigation':
					case 'delete_animation':
					case 'save_animation':
					case 'check_system':
					case 'fix_database_issues':
					case 'trigger_font_deletion':
					case 'get_v5_slider_list':
					case 'reimport_v5_slider':
						$this->ajax_response_error(__('Function only available for administrators'));
						exit;
					break;
					default:
						$return = FA::apply_filters('revslider_admin_onAjaxAction_user_restriction', true, $action, $data, $slider, $slide, $operations);
						if($return !== true){
							$this->ajax_response_error(__('Function only available for administrators'));
							exit;
						}
					break;
				}
			}

			if(FA::wp_verify_nonce($nonce, 'revslider_actions') == false){
				//check if it is wp nonce and if the action is refresh nonce
				$this->ajax_response_error(__('Bad Request'));
				exit;
			}

			if(!in_array($action, $no_cache)) $this->flush_wp_cache();

			switch($action){
				case 'activate_plugin':
					$result	 = false;
					$code	 = trim($this->get_val($data, 'code'));
					$selling = $this->get_addition('selling');
					$rs_license = new RevSliderLicense();

					if(!empty($code)){
						$result = $rs_license->activate_plugin($code);
					}else{
						$error = ($selling === true) ? __('The License Key needs to be set!') : __('The Purchase Code needs to be set!');
						$this->ajax_response_error($error);
						exit;
					}

					if($result === true){
						$this->ajax_response_success(__('Plugin successfully activated'));
					}elseif($result === false){
						$error = ($selling === true) ? __('License Key is invalid') : __('Purchase Code is invalid');
						$this->ajax_response_error($error);
					}else{
						if($result == 'exist'){
							$error = ($selling === true) ? __('License Key already registered!') : __('Purchase Code already registered!');
							$this->ajax_response_error($error);
						}elseif($result == 'banned'){
							$error = ($selling === true) ? __('License Key was locked, please contact the ThemePunch support!') : __('Purchase Code was locked, please contact the ThemePunch support!');
							$this->ajax_response_error($error);
						}
						$error = ($selling === true) ? __('License Key could not be validated') : __('Purchase Code could not be validated');
						$this->ajax_response_error($error);
					}
				break;
				case 'deactivate_plugin':
					$rs_license = new RevSliderLicense();
					$result = $rs_license->deactivate_plugin();

					if($result){
						$this->ajax_response_success(__('Plugin deregistered'));
					}else{
						$this->ajax_response_error(__('Deregistration failed!'));
					}
				break;
				case 'close_deregister_popup':
					FA::update_option('revslider-deregister-popup', 'false');
					$this->ajax_response_success(__('Saved'));
				break;
				case 'dismiss_dynamic_notice':
					$ids = $this->get_val($data, 'id', array());
					$notices_discarded = FA::get_option('revslider-notices-dc', array());
					if(!empty($ids)){
						foreach($ids as $_id){
							$notices_discarded[] = FA::esc_attr(trim($_id));
						}

						FA::update_option('revslider-notices-dc', $notices_discarded);
					}

					$this->ajax_response_success(__('Saved'));
				break;
				case 'check_for_updates':
					$update = new RevSliderUpdate(Framework::RS_REVISION);
					$update->force = true;

					$update->_retrieve_version_info();
					$version = FA::get_option('revslider-latest-version', Framework::RS_REVISION);

					if($version !== false){
						$this->ajax_response_data(array('version' => $version));
					}else{
						$this->ajax_response_error(__('Connection to Update Server Failed'));
					}
				break;
				case 'get_template_information_short':
					$templates = new RevSliderTemplate();
					$sliders = $templates->get_tp_template_sliders();

					$this->ajax_response_data(array('templates' => $sliders));
				break;
				case 'import_template_slider': //before: import_slider_template_slidersview
					$uid		= $this->get_val($data, 'uid');
					$install	= $this->get_val($data, 'install', true);
					$templates	= new RevSliderTemplate();
					$filepath	= $templates->_download_template($uid);

					if($filepath !== false){
						$templates->remove_old_template($uid);
						$slider = new RevSliderSliderImport();
						$return = $slider->import_slider(false, $filepath, $uid, false, true, $install);

						if($this->get_val($return, 'success') == true){
							$new_id = $this->get_val($return, 'sliderID');
							if(intval($new_id) > 0){
								$map = $this->get_val($return, 'map',  array());
								$folder_id = $this->get_val($data, 'folderid', -1);
								if(intval($folder_id) > 0){
									$folder = new RevSliderFolder();
									$folder->add_slider_to_folder($new_id, $folder_id, false);
								}

								$new_slider = new RevSliderSlider();
								$new_slider->init_by_id($new_id);
								$data = $new_slider->get_overview_data();

								$hiddensliderid = $templates->get_slider_id_by_uid($uid);

								$templates->_delete_template($uid); //delete template file

								$this->ajax_response_data(array('slider' => $data, 'hiddensliderid' => $hiddensliderid, 'map' => $map, 'uid' => $uid));
							}
						}

						$templates->_delete_template($uid); //delete template file

						$error = ($this->get_val($return, 'error') !== '') ? $this->get_val($return, 'error') : __('Slider Import Failed');
						$this->ajax_response_error($error);
					}
					$this->ajax_response_error(__('Template Slider Import Failed'));
				break;
				case 'install_template_slider':
					$id = $this->get_val($data, 'sliderid');
					$new_id = $slider->duplicate_slider_by_id($id, true);
					if(intval($new_id) > 0){
						$new_slider = new RevSliderSlider();
						$new_slider->init_by_id($new_id);
						$data = $new_slider->get_overview_data();
						$slide_maps = $slider->get_map();
						$map = array(
							'slider' => array('template_to_duplication' => array($id => $new_id)),
							'slides' => $slide_maps
						);
						$this->ajax_response_data(array('slider' => $data, 'hiddensliderid' => $id, 'map' => $map));
					}
					$this->ajax_response_error(__('Template Slider Installation Failed'));
				break;
				case 'install_template_slide':
					$template = new RevSliderTemplate();
					$slider_id = intval($this->get_val($data, 'slider_id'));
					$slide_id = intval($this->get_val($data, 'slide_id'));

					if($slider_id == 0 || $slide_id == 0){
					}else{
						$new_slide_id = $slide->duplicate_slide_by_id($slide_id, $slider_id);

						if($new_slide_id !== false){
							$slide->init_by_id($new_slide_id);
							$_slides[] = array(
								'order' => $slide->get_order(),
								'params' => $slide->get_params(),
								'layers' => $slide->get_layers(),
								'id' => $slide->get_id(),
							);

							$this->ajax_response_data(array('slides' => $_slides));
						}
					}

					$this->ajax_response_error(__('Slide duplication failed'));
				break;
				case 'import_slider':
					$import = new RevSliderSliderImport();
					$return = $import->import_slider();

					if($this->get_val($return, 'success') == true){
						$new_id = $this->get_val($return, 'sliderID');

						if(intval($new_id) > 0){
							$folder = new RevSliderFolder();
							$folder_id = $this->get_val($data, 'folderid', -1);
							if(intval($folder_id) > 0){
								$folder->add_slider_to_folder($new_id, $folder_id, false);
							}

							$new_slider = new RevSliderSlider();
							$new_slider->init_by_id($new_id);
							$data = $new_slider->get_overview_data();

							$this->ajax_response_data(array('slider' => $data, 'hiddensliderid' => $new_id));
						}
					}

					$error = ($this->get_val($return, 'error') !== '') ? $this->get_val($return, 'error') : __('Slider Import Failed');

					$this->ajax_response_error($error);
				break;
				case 'add_to_media_library':
					$return = $this->import_upload_media();

					if($this->get_val($return, 'error', false) !== false){
						$this->ajax_response_error($this->get_val($return, 'error', false));
					}else{
						$this->ajax_response_data($return);
					}
				break;
				case 'adjust_modal_ids':
					$map = $this->get_val($data, 'map', array());

					if(!empty($map)){
						$slider_ids = $this->get_val($map, 'slider_map', array());
						$slides_ids = $this->get_val($map, 'slides_map', array());

						$ztt = $this->get_val($slider_ids, 'zip_to_template', array());
						$ztd = $this->get_val($slider_ids, 'zip_to_duplication', array());
						$ttd = $this->get_val($slider_ids, 'template_to_duplication', array());
						$s_a = array();
						if(!empty($slides_ids)){
							foreach($slides_ids as $k => $v){
								if(is_array($v)){
									foreach($v as $vk => $vv){
										$s_a[$vk] = $vv;
									}
									unset($slides_ids[$k]);
								}
							}
						}

						if(!empty($ztt)){
							foreach($ztt as $old => $new){
								$slider = new RevSliderSliderImport();
								$slider->init_by_id($new);

								$slider->update_modal_ids($ztt, $slides_ids);
							}
						}

						if(!empty($ztd)){
							foreach($ztd as $old => $new){
								$slider = new RevSliderSliderImport();
								$slider->init_by_id($new);
								$slider->update_modal_ids($ztd, $s_a);
							}
						}

						if(!empty($ttd)){
							foreach($ttd as $old => $new){
								$slider = new RevSliderSliderImport();
								$slider->init_by_id($new);
								$slider->update_modal_ids($ttd, $slides_ids);
							}
						}

						$this->ajax_response_data(array());
					}else{
						$this->ajax_response_error(__('Slider Map Empty'));
					}
				break;
				case 'adjust_js_css_ids':
					$map = $this->get_val($data, 'map', array());

					if(!empty($map)){
						$slider_map = array();
						foreach($map as $m){
							$slider_ids = $this->get_val($m, 'slider_map', array());
							if(!empty($slider_ids)){
								foreach($slider_ids as $old => $new){
									$slider = new RevSliderSliderImport();
									$slider->init_by_id($new);

									$slider_map[] = $slider;
								}
							}
						}

						if(!empty($slider_map)){
							foreach($slider_map as $slider){
								foreach($map as $m){
									$slider_ids = $this->get_val($m, 'slider_map', array());
									$slide_ids = $this->get_val($m, 'slide_map', array());
									if(!empty($slider_ids)){
										foreach($slider_ids as $old => $new){
											$slider->update_css_and_javascript_ids($old, $new, $slide_ids);
										}
									}
								}
							}
						}
					}
				break;
				case 'export_slider':
					$export = new RevSliderSliderExport();
					$id = intval($this->get_request_var('id'));

					$export->export_slider($id);

					//will never be called if all is good
					$this->ajax_response_error(__('Slider Export Error!!!'));
				break;
				case 'export_slider_html':
					$export = new RevSliderSliderExportHtml();
					$id = intval($this->get_request_var('id'));
					$export->export_slider_html($id);

					//will never be called if all is good
					$this->ajax_response_error(__('Slider HTML Export Error!!!'));
				break;
				case 'delete_slider':
					$id = $this->get_val($data, 'id');
					$slider->init_by_id($id);
					$slider->delete_slider();

					$this->ajax_response_success(__('Slider Deleted'));
				break;
				case 'duplicate_slider':
					$id = $this->get_val($data, 'id');
					$new_id = $slider->duplicate_slider_by_id($id);
					if(intval($new_id) > 0){
						$new_slider = new RevSliderSlider();
						$new_slider->init_by_id($new_id);
						$data = $new_slider->get_overview_data();
						$this->ajax_response_data(array('slider' => $data));
					}

					$this->ajax_response_error(__('Duplication Failed'));
				break;
				case 'save_slide':
					$slide_id = $this->get_val($data, 'slide_id');
					$slider_id = $this->get_val($data, 'slider_id');
					$return = $slide->save_slide($slide_id, $data, $slider_id);

					$cache = RevSliderGlobals::instance()->get('RevSliderCache');
					$cache->clear_transients_by_slider($slider_id);

					if($return){
						$this->ajax_response_success(__('Slide Saved'));
					}else{
						$this->ajax_response_error(__('Slide not found'));
					}
				break;
				case 'save_slide_advanced':
					$slide_id = $this->get_val($data, 'slide_id');
					$slider_id = $this->get_val($data, 'slider_id');
					$return = $slide->save_slide_advanced($slide_id, $data, $slider_id);

					$cache = RevSliderGlobals::instance()->get('RevSliderCache');
					$cache->clear_transients_by_slider($slider_id);

					if($return){
						$this->ajax_response_success(__('Slide Saved'));
					}else{
						$this->ajax_response_error(__('Slide not found'));
					}
				break;
				case 'save_slider':
					$slider_id = $this->get_val($data, 'slider_id');
					$slide_ids = $this->get_val($data, 'slide_ids', array());
					$return = $slider->save_slider($slider_id, $data);
					$missing_slides = array();
					$delete_slides = array();

					$cache = RevSliderGlobals::instance()->get('RevSliderCache');
					$cache->clear_transients_by_slider($slider_id);

					if($return !== false){
						if(!empty($slide_ids)){
							$slides = $slider->get_slides(false, true);

							//get the missing Slides (if any at all)
							foreach($slide_ids as $slide_id){
								$found = false;
								foreach($slides as $_slide){
									if($_slide->get_id() !== $slide_id){
										continue;
									}

									$found = true;
								}
								if(!$found){
									$missing_slides[] = $slide_id;
								}

							}

							//get the Slides that are no longer needed and delete them
							$existing_slide_ids = array();
							foreach($slides as $key => $_slide){
								$id = $_slide->get_id();
								if(!in_array($id, $slide_ids)){
									$delete_slides[] = $id;
								}else{
									$existing_slide_ids[] = $id;
								}
							}

							foreach($slides as $key => $_slide){
								//check if the parentID exists in the $slides, if not remove this child slide
								$parentID = $_slide->get_param(array('child', 'parentId'), false);
								if($parentID !== false){
									if(!in_array($parentID, $existing_slide_ids)){
										$slid = $_slide->get_id();
										if(!in_array($slid, $delete_slides)){
											$delete_slides[] = $slid;
										}
									}
								}
							}

							if(!empty($delete_slides)){
								//check for parentID's and if they exist, if the parentID will be deleted
								foreach($slides as $key => $_slide){
									//params -> child -> parentID
									$parentID = $_slide->get_param(array('child', 'parentId'), false);
									$child = $_slide->get_param(array('child'), false);
									//var_dump($parentID);
									if($parentID !== false){
										if(in_array($parentID, $delete_slides)){
											$delete_slides[] = $_slide->get_id();
										}
									}
								}

								foreach($slides as $key => $_slide){
									$id = $_slide->get_id();
									if(in_array($id, $delete_slides)){
										unset($slides[$key]); //remove none existing slides for further ordering process
									}
								}

								foreach($delete_slides as $delete_slide){
									$slide->delete_slide_by_id($delete_slide);
								}
							}

							//change the order of slides
							$slide = new RevSliderSlide();
							foreach($slide_ids as $order => $id){
								$new_order = $order + 1;
								$slide->change_slide_order($id, $new_order);
							}
						}

						$this->ajax_response_data(array('missing' => $missing_slides, 'delete' => $delete_slides));
					}else{
						$this->ajax_response_error(__('Slider not found'));
					}
				break;
				case 'delete_slide':
					$slide_id = intval($this->get_val($data, 'slide_id', ''));
					if($slide_id > 0){
						$slide->init_by_id($slide_id);
						$slider_id = $slide->get_slider_id();
						$cache = RevSliderGlobals::instance()->get('RevSliderCache');
						$cache->clear_transients_by_slider($slider_id);
					}
					$return = ($slide_id > 0) ? $slide->delete_slide_by_id($slide_id) : false;

					if($return !== false){
						$this->ajax_response_success(__('Slide deleted'));
					}else{
						$this->ajax_response_error(__('Slide could not be deleted'));
					}
				break;
				case 'duplicate_slide':
					$slide_id	= intval($this->get_val($data, 'slide_id', ''));
					$slider_id	= intval($this->get_val($data, 'slider_id', ''));

					$new_slide_id = $slide->duplicate_slide_by_id($slide_id, $slider_id);
					if($new_slide_id !== false){
						$slide->init_by_id($new_slide_id);
						$_slide = $slide->get_overview_data();

						$this->ajax_response_data(array('slide' => $_slide));
					}else{
						$this->ajax_response_error(__('Slide could not duplicated'));
					}
				break;
				case 'update_slide_order':
					$slide_ids	= $this->get_val($data, 'slide_ids', array());

					//change the order of slides
					if(!empty($slide_ids)){
						$init = false;
						foreach($slide_ids as $order => $id){
							if($init === false){
								$slide->init_by_id($id);
								$init = true;
							}
							$new_order = $order + 1;
							$slide->change_slide_order($id, $new_order);
						}

						$slider_id = $slide->get_slider_id();
						$cache = RevSliderGlobals::instance()->get('RevSliderCache');
						$cache->clear_transients_by_slider($slider_id);

						$this->ajax_response_success(__('Slide order changed'));
					}else{
						$this->ajax_response_error(__('Slide order could not be changed'));
					}
				break;
				case 'getSliderImage':
					// Available Sliders
					$slider = new RevSliderSlider();
					$arrSliders = $slider->get_sliders();

                    // Given Alias
					$alias = $this->get_val($data, 'alias');
					$return = array_search($alias,$arrSliders);

					foreach($arrSliders as $sliderony){
						if($sliderony->get_alias() != $alias) continue;

						$slider_found	= $sliderony->get_overview_data();
						$return			= $this->get_val($slider_found, array('bg', 'src'));
						$title			= $this->get_val($slider_found, 'title');
						$premium_state	= $this->get_val($slider_found, 'premium');

						break;
					}

					if(!$return) $return = '';

					if(!empty($title)){
						$this->ajax_response_data(array('image' => $return, 'title' => $title, 'premium' => $premium_state));
					}else{
						$this->ajax_response_error( __('The Slider with the alias "' . $alias . '" is not available!') );
					}

				break;
				case 'getSliderSizeLayout':
					// Available Sliders
					$slider = new RevSliderSlider();
					$arrSliders = $slider->get_sliders();
                    // Given Alias
                    $alias    = $this->get_val($data, 'alias');
                    $return = array_search($alias, $arrSliders);
                    $title    = '';
					foreach($arrSliders as $sliderony){
                        if($sliderony->get_alias() == $alias){
                            $slider_found = $sliderony->get_overview_data();
                            $return    = $slider_found['size'];
                            $title    = $slider_found['title'];
                        }
					}

					$this->ajax_response_data(array('layout' => $return, 'title' => $title));
				break;
				case 'get_list_of':
					$type = $this->get_val($data, 'type');
					switch($type){
						case 'sliders':
							$slider = new RevSliderSlider();
							$arrSliders = $slider->get_sliders();
							$return = array();
							foreach($arrSliders as $sliderony){
								$return[$sliderony->get_id()] = array('slug' => $sliderony->get_alias(), 'title' => $sliderony->get_title(), 'type' => $sliderony->get_type(), 'subtype' => $sliderony->get_param(array('source', 'post', 'subType'), false));
							}
							$this->ajax_response_data(array('sliders' => $return));
						break;
						case 'pages':
							$pages = get_pages(array());
							$return = array();
							foreach($pages as $page){
								if(!$page->post_password){
									$return[$page->ID] = array('slug' => $page->post_name, 'title' => $page->post_title);
								}

							}
							$this->ajax_response_data(array('pages' => $return));
						break;
						case 'posttypes':
							$args = array(
								'public' => true,
								'_builtin' => false,
							);
							$output = 'objects';
							$operator = 'and';
							$post_types = FA::get_post_types($args, $output, $operator);
							$return['post'] = array('slug' => 'post', 'title' => __('Posts'));

							foreach($post_types as $post_type){
								$return[$post_type->rewrite['slug']] = array('slug' => $post_type->rewrite['slug'], 'title' => $post_type->labels->name);
								if(!in_array($post_type->name, array('post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'custom_changeset', 'user_request'))){
									$taxonomy_objects = FA::get_object_taxonomies($post_type->name, 'objects');
									if(!empty($taxonomy_objects)){
										$return[$post_type->rewrite['slug']]['tax'] = array();
										foreach($taxonomy_objects as $name => $tax){
											$return[$post_type->rewrite['slug']]['tax'][$name] = $tax->label;
										}
									}
								}
							}

							$this->ajax_response_data(array('posttypes' => $return));
						break;
					}
				break;
				case 'load_wordpress_object':
					$id = $this->get_val($data, 'id', 0);
					$type = $this->get_val($data, 'type', 'full');

					$file = FA::wp_get_attachment_image_src($id, $type);
					if($file !== false){
						$this->ajax_response_data(array('url' => $this->get_val($file, 0)));
					}else{
						$this->ajax_response_error(__('File could not be loaded'));
					}
				break;
				case 'get_global_settings':
					$this->ajax_response_data(array('global_settings' => $this->global_settings));
				break;
				case 'update_global_settings':
					$global = $this->get_val($data, 'global_settings', array());
					if(!empty($global)){
						$update = $this->get_val($data, 'update', false);
						$return = $this->set_global_settings($global, $update);
						if($return === true){
							$this->ajax_response_success(__('Global Settings saved/updated'));
						}else{
							$this->ajax_response_error(__('Global Settings not saved/updated'));
						}
					}else{
						$this->ajax_response_error(__('Global Settings not saved/updated'));
					}
				break;
				case 'create_navigation_preset':
					$nav = new RevSliderNavigation();
					$return = $nav->add_preset($data);

					if($return === true){
						$this->ajax_response_success(__('Navigation preset saved/updated'), array('navs' => $nav->get_all_navigations_builder()));
					}else{
						if($return === false){
							$return = __('Preset could not be saved/values are the same');
						}

						$this->ajax_response_error($return);
					}
				break;
				case 'delete_navigation_preset':
					$nav = new RevSliderNavigation();
					$return = $nav->delete_preset($data);

					if($return === true){
						$this->ajax_response_success(__('Navigation preset deleted'), array('navs' => $nav->get_all_navigations_builder()));
					}else{
						if($return === false){
							$return = __('Preset not found');
						}

						$this->ajax_response_error($return);
					}
				break;
				case 'save_navigation': //also deletes if requested
					$_nav = new RevSliderNavigation();
					$navs = (array) $this->get_val($data, 'navs', array());
					$delete_navs = (array) $this->get_val($data, 'delete', array());

					if(!empty($delete_navs)){
						foreach($delete_navs as $dnav){
							$_nav->delete_navigation($dnav);
						}
					}

					if(!empty($navs)){
						$_nav->create_update_full_navigation($navs);
					}

					$navigations = $_nav->get_all_navigations_builder();

					$this->ajax_response_data(array('navs' => $navigations));
				break;
				case 'delete_animation':
					$animation_id = $this->get_val($data, 'id');
					$admin = new RevSliderFunctionsAdmin();
					$return = $admin->delete_animation($animation_id);
					if($return){
						$this->ajax_response_success(__('Animation deleted'));
					}else{
						$this->ajax_response_error(__('Deletion failed'));
					}
				break;
				case 'save_animation':
					$admin	= new RevSliderFunctionsAdmin();
					$id		= $this->get_val($data, 'id', false);
					$type	= $this->get_val($data, 'type', 'in');
					$animation = $this->get_val($data, 'obj');

					if($id !== false){
						$return = $admin->update_animation($id, $animation, $type);
					}else{
						$return = $admin->insert_animation($animation, $type);
					}

					if(intval($return) > 0){
						$this->ajax_response_data(array('id' => $return));
					} elseif($return === true){
						$this->ajax_response_success(__('Animation saved'));
					}else{
						if($return == false){
							$this->ajax_response_error(__('Animation could not be saved'));
						}
						$this->ajax_response_error($return);
					}
				break;
				case 'get_slides_by_slider_id':
					$sid	 = intval($this->get_val($data, 'id'));
					$slides	 = array();
					$_slides = $slide->get_slides_by_slider_id($sid);

					if(!empty($_slides)){
						foreach($_slides as $slide){
							$slides[] = $slide->get_overview_data();
						}
					}

					$this->ajax_response_data(array('slides' => $slides));
				break;
				case 'get_full_slider_object':
					$slide_id = $this->get_val($data, 'id');
					$slide_id = RevSliderFunctions::esc_attr_deep($slide_id);
					$slider_alias = $this->get_val($data, 'alias', '');
					$slider_alias = RevSliderFunctions::esc_attr_deep($slider_alias);

					if($slider_alias !== ''){
						$slider->init_by_alias($slider_alias);
						$slider_id = $slider->get_id();
					}else{
						if(strpos($slide_id, 'slider-') !== false){
							$slider_id = str_replace('slider-', '', $slide_id);
						}else{
							$slide->init_by_id($slide_id);

							$slider_id = $slide->get_slider_id();
							if(intval($slider_id) == 0){
								$this->ajax_response_error(__('Slider could not be loaded'));
							}
						}

						$slider->init_by_id($slider_id);
					}
					if($slider->inited === false){
						$this->ajax_response_error(__('Slider could not be loaded'));
					}

					//check if an update is needed
					if(version_compare($slider->get_param(array('settings', 'version')), FA::get_option('revslider_update_version', '6.0.0'), '<')){
						$upd = new RevSliderPluginUpdate();
						$upd->upgrade_slider_to_latest($slider);
						$slider->init_by_id($slider_id);
					}

                    //check if an update is needed
                    if(version_compare($slider->get_param(array('settings', 'version')), FA::get_option('revslider_update_version', '6.0.0'), '<')){
                        $upd = new RevSliderPluginUpdate();
                        $upd->upgrade_slider_to_latest($slider);
                        $slider->init_by_id($slider_id);
                    }

                    //create static Slide if the Slider not yet has one
					$static_slide_id = $slide->get_static_slide_id($slider_id);
					$static_slide_id = (intval($static_slide_id) === 0) ? $slide->create_slide($slider_id, '', true) : $static_slide_id;

					$static_slide = false;
					if(intval($static_slide_id) > 0){
						$static_slide = new RevSliderSlide();
						$static_slide->init_by_static_id($static_slide_id);
					}

					$slides = $slider->get_slides(false, true);
					$_slides = array();
					$_static_slide = array();

					if(!empty($slides)){
						foreach($slides as $s){
							$_slides[] = array(
								'order' => $s->get_order(),
								'params' => $s->get_params(),
								'layers' => $s->get_layers(),
								'id' => $s->get_id(),
							);
						}
					}

					if(!empty($static_slide)){
						$_static_slide = array(
							'params' => $static_slide->get_params(),
							'layers' => $static_slide->get_layers(),
							'id' => $static_slide->get_id(),
						);
					}

					$obj = array(
						'id' => $slider_id,
						'alias' => $slider->get_alias(),
						'title' => $slider->get_title(),
						'slider_params' => $slider->get_params(),
						'slider_settings' => $slider->get_settings(),
						'slides' => $_slides,
						'static_slide' => $_static_slide,
					);

					$this->ajax_response_data($obj);
				break;
				case 'load_builder':
					ob_start();
					require_once Framework::$RS_PLUGIN_PATH . 'admin/views/builder.php';
					$builder = ob_get_contents();
					ob_clean();
					ob_end_clean();

					$this->ajax_response_data($builder);
				break;
				case 'create_slider_folder':
					$folder = new RevSliderFolder();
					$title = $this->get_val($data, 'title', __('New Folder'));
					$parent = $this->get_val($data, 'parentFolder', 0);
					$new = $folder->create_folder($title, $parent);

					if($new !== false){
						$overview_data = $new->get_overview_data();
						$this->ajax_response_data(array('folder' => $overview_data));
					}else{
						$this->ajax_response_error(__('Folder Creation Failed'));
					}
				break;
				case 'delete_slider_folder':
					$id = $this->get_val($data, 'id');
					$folder = new RevSliderFolder();
					$is = $folder->init_folder_by_id($id);
					if($is === true){
						$folder->delete_slider();
						$this->ajax_response_success(__('Folder Deleted'));
					}else{
						$this->ajax_response_error(__('Folder Deletion Failed'));
					}
				break;
				case 'update_slider_tags':
					$id = $this->get_val($data, 'id');
					$tags = $this->get_val($data, 'tags');

					$return = $slider->update_slider_tags($id, $tags);
					if($return == true){
						$this->ajax_response_success(__('Tags Updated'));
					}else{
						$this->ajax_response_error(__('Failed to Update Tags'));
					}
				break;
				case 'save_slider_folder':
					$folder = new RevSliderFolder();
					$children = $this->get_val($data, 'children');
					$folder_id = $this->get_val($data, 'id');

					$return = $folder->add_slider_to_folder($children, $folder_id);

					if($return == true){
						$this->ajax_response_success(__('Slider Moved to Folder'));
					}else{
						$this->ajax_response_error(__('Failed to Move Slider Into Folder'));
					}
				break;
				case 'update_slider_name':
				case 'update_folder_name':
					$slider_id = $this->get_val($data, 'id');
					$new_title = $this->get_val($data, 'title');

					$slider->init_by_id($slider_id, $new_title);
					$return = $slider->update_title($new_title);
					if($return != false){
						$this->ajax_response_success(__('Title updated'), array('title' => $return));
					}else{
						$this->ajax_response_error(__('Failed to update Title'));
					}
				break;
				case 'preview_slider':
					$slider_id = $this->get_val($data, 'id');
					$slider_data = $this->get_val($data, 'data');
					$title = __('Slider Revolution Preview');

					if(intval($slider_id) > 0 && empty($slider_data)){
						$slider->init_by_id($slider_id);

						//check if an update is needed
						if(version_compare($slider->get_param(array('settings', 'version')), FA::get_option('revslider_update_version', '6.0.0'), '<')){
							$upd = new RevSliderPluginUpdate();
							$upd->upgrade_slider_to_latest($slider);
							$slider->init_by_id($slider_id);
						}

                        Framework::$rs_preview_mode = true;

						$output = new RevSliderOutput();
						$output->set_ajax_loaded();
						ob_start();
						$output->add_slider_to_stage($slider_id);
						$content = ob_get_contents();
						ob_clean();
						ob_end_clean();

					}elseif(!empty($slider_data)){
						$_slides = array();
						$_static = array();
						$slides = array();
						$static_slide = array();

						$_slider = array(
							'id'		=> $slider_id,
							'title'		=> 'Preview',
							'alias'		=> 'preview',
							'settings'	=> json_encode(array('version' => Framework::RS_REVISION)),
							'params'	=> $this->get_val($slider_data, 'slider') // removed stripping from here as it damaged data
						);

						$slide_order = FA::json_decode($this->get_val($slider_data, array('slide_order')), true); // removed stripping from here as it damaged data

						foreach($slider_data as $sk => $sd){
							if(in_array($sk, array('slider', 'slide_order'), true)) continue;

							if(strpos($sk, 'static_') !== false){
								$_static = array(
									'params' => $this->get_val($sd, 'params'), // removed stripping from here as it damaged data
									'layers' => $this->get_val($sd, 'layers'), // removed stripping from here as it damaged data
								);
							}else{
								$_slides[$sk] = array(
									'id'		=> $sk,
									'slider_id'	=> $slider_id,
									'slide_order' => array_search($sk, $slide_order),
									'params'	=> $this->get_val($sd, 'params'), // removed stripping from here as it damaged data
									'layers'	=> $this->get_val($sd, 'layers'), // removed stripping from here as it damaged data
									'settings'	=> array('version' => Framework::RS_REVISION)
								);
							}
						}

						$slider->set_slides($_slides);
                        $output = new RevSliderOutput();
                        $output->set_preview_mode(true);
						$slider->init_by_data($_slider);
						if($slider->is_stream() || $slider->is_posts()){
							$slides = $slider->get_slides_for_output();
						}else{
							if(!empty($_slides)){
								//reorder slides

								usort($_slides, array($this, 'sort_by_slide_order'));
								foreach($_slides as $_slide){
									$slide = new RevSliderSlide();
									$slide->init_by_data($_slide);
									if($slide->get_param(array('publish', 'state'), 'published') === 'unpublished') continue;
									$slides[] = $slide;
								}
							}
						}
						if(!empty($_static)){
							$slide = new RevSliderSlide();
							$slide->init_by_data($_static);
							$static_slide = $slide;
						}

						$output->set_slider($slider);
						$output->set_current_slides($slides);
						$output->set_static_slide($static_slide);

						ob_start();
						$slider = $output->add_slider_to_stage($slider_id);
						$content = ob_get_contents();
						ob_clean();
						ob_end_clean();
					}

					//get dimensions of slider
					$size = array(
						'width'	 => $slider->get_param(array('size', 'width'), array()),
						'height' => $slider->get_param(array('size', 'height'), array()),
						'custom' => $slider->get_param(array('size', 'custom'), array())
					);

					if(empty($size['width'])){
						$size['width'] = array(
							'd' => $this->get_val($this->global_settings, array('size', 'desktop'), '1240'),
							'n' => $this->get_val($this->global_settings, array('size', 'notebook'), '1024'),
							't' => $this->get_val($this->global_settings, array('size', 'tablet'), '778'),
							'm' => $this->get_val($this->global_settings, array('size', 'mobile'), '480')
						);
					}
					if(empty($size['height'])){
						$size['height'] = array('d' => '868', 'n' => '768', 't' => '960', 'm' => '720');
					}

                    if(extension_loaded('newrelic')){ //Ensure PHP agent is available
                        if(function_exists('newrelic_disable_autorum')){
                            newrelic_disable_autorum();
                        }
                    }

					$rev_slider_front = new RevSliderFront();

					$html = FA::generatePreview(get_defined_vars());

                    $return = array(
                        'html' => $html,
                        'size' => $size,
                        'layouttype' => $slider->get_param('layouttype', 'fullwidth'),
                        'requireConfigLegacyUrl' => str_replace('adminhtml/Magento', '_requirejs/adminhtml/Magento', FA::getAssetUrl('requirejs-config' . (FA::isMinified('js') ? '.min' : '') . '.js', [], true))
                    );
                    $return = FA::apply_filters('revslider_preview_slider_addition', $return, $slider);

                    $this->ajax_response_data($return);

					exit;
				break;
				case 'subscribe_to_newsletter':
					$email = $this->get_val($data, 'email');
					if(!empty($email)){
						$return = ThemePunch_Newsletter::subscribe($email);

						if($return !== false){
							if(!isset($return['status']) || $return['status'] === 'error'){
								$error = $this->get_val($return, 'message', __('Invalid Email'));
								$this->ajax_response_error($error);
							}else{
								$this->ajax_response_success(__('Success! Please check your E-Mails to finish the subscription'), $return);
							}
						}
						$this->ajax_response_error(__('Invalid Email/Could not connect to the Newsletter server'));
					}

					$this->ajax_response_error(__('No Email given'));
				break;
				case 'check_system':
					//recheck the connection to themepunch server
					$update = new RevSliderUpdate(Framework::RS_REVISION);
					$update->force = true;
					$update->_retrieve_version_info();

					$fun = new RevSliderFunctionsAdmin();
					$system = $fun->get_system_requirements();

					$this->ajax_response_data(array('system' => $system));
				break;
				case 'load_module':
					$module = $this->get_val($data, 'module', array('all'));
					$module_uid = $this->get_val($data, 'module_uid', false);
					$module_slider_id = $this->get_val($data, 'module_id', false);
					$refresh_from_server = $this->get_val($data, 'refresh_from_server', false);
					$get_static_slide = $this->_truefalse($this->get_val($data, 'static', false));

					if($module_uid === false){
						$module_uid = $module_slider_id;
					}

					$admin = new RevSliderFunctionsAdmin();
					$modules = $admin->get_full_library($module, $module_uid, $refresh_from_server, $get_static_slide);

					$this->ajax_response_data(array('modules' => $modules));
				break;
				case 'set_favorite':
					$do = $this->get_val($data, 'do', 'add');
					$type = $this->get_val($data, 'type', 'slider');
					$id = FA::esc_attr($this->get_val($data, 'id'));

                    $favorite = RevSliderGlobals::instance()->get('RevSliderFavorite');
					$favorite->set_favorite($do, $type, $id);

					$this->ajax_response_success(__('Favorite Changed'));
				break;
				case 'load_library_object':
					$library = new RevSliderObjectLibrary();

					$cover = false;
					$id = $this->get_val($data, 'id');
					$type = $this->get_val($data, 'type');
					if($type == 'thumb'){
						$thumb = $library->_get_object_thumb($id, 'thumb');
					}elseif($type == 'video'){
						$thumb = $library->_get_object_thumb($id, 'video_full', true);
						$cover = $library->_get_object_thumb($id, 'cover', true);
					}elseif($type == 'layers'){
						$thumb = $library->_get_object_layers($id);
					}else{
						$thumb = $library->_get_object_thumb($id, 'orig', true);
						if(isset($thumb['error']) && $thumb['error'] === false){
							$url = $library->get_correct_size_url($id, $type);
							if($url !== ''){
								$thumb['url'] = $url;
							}
						}
					}

					if(isset($thumb['error']) && $thumb['error'] !== false){
						$this->ajax_response_error(__('Object could not be loaded'));
					}else{
						if($type == 'layers'){
							$return = array('layers' => $this->get_val($thumb, 'data'));
						}else{
							$return = array('url' => $this->get_val($thumb, 'url'));
						}

						if($cover !== false){
							if(isset($cover['error']) && $cover['error'] !== false){
								$this->ajax_response_error(__('Video cover could not be loaded'));
							}

							$return['cover'] = $this->get_val($cover, 'url');
						}

						$this->ajax_response_data($return);
					}
				break;
				case 'create_slide':
					$slider_id = $this->get_val($data, 'slider_id', false);
					$amount = $this->get_val($data, 'amount', 1);
					$amount = intval($amount);
					$slide_ids = array();

					if(intval($slider_id) > 0 && ($amount > 0 && $amount < 50)){
						for ($i = 0; $i < $amount; $i++){
							$slide_ids[] = $slide->create_slide($slider_id);
						}
					}

					if(!empty($slide_ids)){
						$this->ajax_response_data(array('slide_id' => $slide_ids));
					}else{
						$this->ajax_response_error(__('Could not create Slide'));
					}
				break;
				case 'create_slider':
					/**
					 * 1. create a blank Slider
					 * 2. create a blank Slide
					 * 3. create a blank Static Slide
					 **/

					$slide_id = false;
					$slider_id = $slider->create_blank_slider();
					if($slider_id !== false){
						$slide_id = $slide->create_slide($slider_id); //normal slide
						$slide->create_slide($slider_id, '', true); //static slide
					}

					if($slide_id !== false){
						$this->ajax_response_data(array('slide_id' => $slide_id, 'slider_id' => $slider_id));
					}else{
						$this->ajax_response_error(__('Could not create Slider'));
					}
				break;
				case 'get_addon_list':
					$addon = new RevSliderAddons();
					$addons = $addon->get_addon_list();

					FA::update_option('rs-addons-counter', 0); //set the counter back to 0

					$this->ajax_response_data(array('addons' => $addons));
				break;
				case 'get_layers_by_slide':
					$slide_id = $this->get_val($data, 'slide_id');

					$slide->init_by_id($slide_id);
					$layers = $slide->get_layers();

					$this->ajax_response_data(array('layers' => $layers));
				break;
				case 'activate_addon':
					$handle = $this->get_val($data, 'addon');
					$update = $this->get_val($data, 'update', false);
					$addon = new RevSliderAddons();

					$return = $addon->install_addon($handle, $update);

					if($return === true){
						$version = $addon->get_addon_version($handle);
						//return needed files of the plugin somehow
						$data = array();
						$data = FA::apply_filters('revslider_activate_addon', $data, $handle);

						$this->ajax_response_data(array($handle => $data, 'version' => $version));
					}else{
						$error = ($return === false) ? __('AddOn could not be activated') : $return;

						$this->ajax_response_error($error);
					}
				break;
				case 'deactivate_addon':
					$handle = $this->get_val($data, 'addon');
					$addon = new RevSliderAddons();
					$return = $addon->deactivate_addon($handle);

					if($return){
						//return needed files of the plugin somehow
						$this->ajax_response_success(__('AddOn deactivated'));
					}else{
						$this->ajax_response_error(__('AddOn could not be deactivated'));
					}
				break;
				case 'create_draft_page':
					$admin		= new RevSliderFunctionsAdmin();
					$response	= array('open' => false, 'edit' => false);
					$slider_ids = $this->get_val($data, 'slider_ids');
					$modals		= $this->get_val($data, 'modals', array());
					$additions	= $this->get_val($data, 'additions', array());
					$page_id	= $admin->create_slider_page($slider_ids, $modals, $additions);

					if ($page_id) {
						$response['open'] = FA::getPageUrl($page_id);
						$response['edit'] = FA::getEditPageUrl($page_id);
					}
					$this->ajax_response_data($response);
				break;
				case 'export_layer_group': //developer function only :)
					$title = $this->get_val($data, 'title', $this->get_request_var('title'));
					$videoid = intval($this->get_val($data, 'videoid', $this->get_request_var('videoid')));
					$thumbid = intval($this->get_val($data, 'thumbid', $this->get_request_var('thumbid')));
					$layers = $this->get_val($data, 'layers', $this->get_request_var('layers', '', false));

					$export = new RevSliderSliderExport($title);
					$url = $export->export_layer_group($videoid, $thumbid, $layers);

					$this->ajax_response_data(array('url' => $url));
				break;
				case 'silent_slider_update':
					$upd = new RevSliderPluginUpdate();
					$return = $upd->upgrade_next_slider();

					$this->ajax_response_data($return);
				break;
				case 'load_wordpress_image':
					$id = $this->get_val($data, 'id', 0);
					$type = $this->get_val($data, 'type', 'orig');

					$img = FA::wp_get_attachment_image_url($id, $type);
					if(empty($img)){
						$this->ajax_response_error(__('Image could not be loaded'));
					}

					$this->ajax_response_data(array('url' => $img));
				break;
				case 'load_library_image':
					$images	= (!is_array($data)) ? (array)$data : $data;
					$images	= RevSliderFunctions::esc_attr_deep($images);
					$images	= RevSliderAdmin::esc_js_deep($images);
					$img_data = array();

					if(!empty($images)){
						$templates = new RevSliderTemplate();
						$obj = new RevSliderObjectLibrary();

						foreach($images as $image){
							$type = $this->get_val($image, 'librarytype');
							$img = $this->get_val($image, 'id');
							$ind = $this->get_val($image, 'ind');
							$mt = $this->get_val($image, 'mediatype');
							switch($type){
								case 'moduletemplates':
								case 'moduletemplateslides':
									$img = $templates->_check_file_path($img, true);
									$img_data[] = array(
										'ind' => $ind,
										'url' => $img,
										'mediatype' => $mt
									);
								break;
								case 'image':
								case 'images':
								case 'layers':
								case 'objects':
									$get = ($mt === 'video') ? 'video_thumb' : 'thumb';
									$img = $obj->_get_object_thumb($img, $get, true);
									if($this->get_val($img, 'error', false) === false){
										$img_data[] = array(
											'ind' => $ind,
											'url' => $this->get_val($img, 'url'),
											'mediatype' => $mt
										);
									}
								break;
								case 'videos':
									$get = ($mt === 'img') ? 'video' : 'video_thumb';
									$img = $obj->_get_object_thumb($img, $get, true);
									if($this->get_val($img, 'error', false) === false){
										$img_data[] = array(
											'ind' => $ind,
											'url' => $this->get_val($img, 'url'),
											'mediatype' => $mt
										);
									}
								break;
							}
						}
					}

					$this->ajax_response_data(array('data' => $img_data));
				break;
                case 'create_customlibrary_tags':
                    $obj = new RevSliderObjectLibrary();

                    $name = $this->get_val($data, 'name', '');
                    $type = $this->get_val($data, 'type', '');

                    $return = $obj->create_custom_tag($name, $type);
                    if(!is_array($return)){
                        $this->ajax_response_error($return);
                    }else{
                        $this->ajax_response_data($return);
                    }
                break;
                case 'edit_customlibrary_tags':
                    $obj = new RevSliderObjectLibrary();

                    $id = $this->get_val($data, 'id', '');
                    $name = $this->get_val($data, 'name', '');
                    $type = $this->get_val($data, 'type', '');

                    $return = $obj->edit_custom_tag($id, $name, $type);
                    if($return !== true){
                        $this->ajax_response_error($return);
                    }else{
                        $this->ajax_response_success(__('Tag successfully saved'));
                    }
                break;
                case 'delete_customlibrary_tags':
                    $obj = new RevSliderObjectLibrary();

                    $id = $this->get_val($data, 'id', '');
                    $type = $this->get_val($data, 'type', '');

                    $return = $obj->delete_custom_tag($id, $type);
                    if($return !== true){
                        $this->ajax_response_error($return);
                    }else{
                        $this->ajax_response_success(__('Tag successfully deleted'));
                    }
                break;
                case 'upload_customlibrary_item':
                    $obj = new RevSliderObjectLibrary();

                    $return = $obj->upload_custom_item($data);

                    if(!is_array($return)){
                        $this->ajax_response_error($return);
                    }else{
                        $return['tags'] = $this->get_val($obj->get_custom_tags(), 'svgcustom', array());
                        $this->ajax_response_data($return);
                    }
                break;
                case 'edit_customlibrary_item':
                    $obj = new RevSliderObjectLibrary();

                    $id = $this->get_val($data, 'id', '');
                    $type = $this->get_val($data, 'type', '');
                    $name = $this->get_val($data, 'name', '');
                    $tags = $this->get_val($data, 'tags', '');
                    $return = $obj->edit_custom_item($id, $type, $name, $tags);
                    if($return !== true){
                        $this->ajax_response_error(__('Item could not be changed'));
                    }else{
                        $this->ajax_response_success(__('Item successfully changed'));
                    }
                break;
                case 'delete_customlibrary_item':
                    $obj = new RevSliderObjectLibrary();

                    $id = $this->get_val($data, 'id', '');
                    $type = $this->get_val($data, 'type', '');
                    $return = $obj->delete_custom_item($id, $type);
                    if($return !== true){
                        $this->ajax_response_error(__('Item could not be deleted'));
                    }else{
                        $this->ajax_response_success(__('Item successfully deleted'));
                    }
                break;
				case 'get_help_directory':
					$help_data = RevSliderHelp::getIndex();
					$this->ajax_response_data(array('data' => $help_data));
				break;
				case 'get_tooltips':
					$tooltips = RevSliderTooltips::getTooltips();
					$this->ajax_response_data(array('data' => $tooltips));
				break;
				case 'set_tooltip_preference':
					FA::update_option('revslider_hide_tooltips', true);
                    $this->ajax_response_success(__('Preference Updated'));
				break;
				case 'save_color_preset':
					$presets = $this->get_val($data, 'presets', array());
					$color_presets = RSColorpicker::save_color_presets($presets);
					$this->ajax_response_data(array('presets' => $color_presets));
				break;
				case 'get_facebook_photosets':
                    if(empty($data['app_id'])){
                        $this->ajax_response_error(__('Facebook API error: Empty Access Token'));
                    }
                    if(empty($data['page_id'])){
                        $this->ajax_response_error(__('Facebook API error: Empty Page ID'));
                    }

                    $facebook = RevSliderGlobals::instance()->get('RevSliderFacebook');
                    $return = $facebook->get_photo_set_photos_options($data['app_id'], $data['page_id']);

                    if(empty($return)){
                        $error = __('Could not fetch Facebook albums');
                        $this->ajax_response_error($error);
                    }
                    if(!empty($return['error'])){
                        $this->ajax_response_error(__('Facebook API error: ') . $return['message']);
                    }

                    $this->ajax_response_success(__('Successfully fetched Facebook albums'), array('html' => implode(' ', $return)));
				break;
				case 'get_flickr_photosets':
					$error = __('Could not fetch flickr album');
					if(!empty($data['url']) && !empty($data['key'])){
						$flickr = new RevSliderFlickr($data['key']);
						$user_id = $flickr->get_user_from_url($data['url']);
						$return = $flickr->get_photo_sets($user_id, $data['count'], $data['set']);
						if(!empty($return)){
							$this->ajax_response_success(__('Successfully fetched flickr albums'), array('data' => array('html' => implode(' ', $return))));
						}else{
							$error = __('Could not fetch flickr albums');
						}
					}else{
						if(empty($data['url']) && empty($data['key'])){
							$this->ajax_response_success(__('Cleared Albums'), array('html' => implode(' ', $return)));
						}elseif(empty($data['url'])){
							$error = __('No User URL - Could not fetch flickr albums');
						}else{
							$error = __('No API KEY - Could not fetch flickr albums');
						}
					}

					$this->ajax_response_error($error);
				break;
				case 'get_youtube_playlists':
					if(!empty($data['id'])){
						$youtube = new RevSliderYoutube(trim($data['api']), trim($data['id']));
						$return = $youtube->get_playlist_options($data['playlist']);
                        if ($return) {
                            $this->ajax_response_success(__('Successfully fetched YouTube playlists'), array('data' => array('html' => implode(' ', $return))));
                        } else {
                            $this->ajax_response_error(__('Could not fetch YouTube playlists'));
                        }
					}else{
						$this->ajax_response_error(__('Could not fetch YouTube playlists'));
					}
				break;
				case 'fix_database_issues':
					FA::update_option('revslider_table_version', '1.0.0');
					RevSliderFront::create_tables(true);
					$this->ajax_response_success(__('Slider Revolution database structure was updated'));
				break;
				case 'clear_internal_cache':
					$cache = RevSliderGlobals::instance()->get('RevSliderCache');
					$cache->clear_all_transients();

					$this->ajax_response_success(__('Slider Revolution internal cache was fully cleared'));
				break;
				case 'trigger_font_deletion':
					$this->delete_google_fonts();
					$this->ajax_response_success(__('Downloaded Google Fonts will be updated'));
				break;
				case 'get_same_aspect_ratio':
					$images = $this->get_val($data, 'images', array());
					$return = $this->get_same_aspect_ratio_images($images);

					$this->ajax_response_data(array('images' => $return));
				break;
				case 'get_addons_sizes':
					$addons = $this->get_val($data, 'addons', array());
					$sizes = $this->get_addon_sizes($addons);

					$this->ajax_response_data(array('addons' => $sizes));
				break;
                case 'save_custom_templates_slidetransitions':
                    $return = $this->save_custom_slidetransitions($data);
                    if ($return === false || intval($return) === 0) {
                        $this->ajax_response_success(__('Slide transition template could not be saved'));
                    } else {
                        $this->ajax_response_success(__('Slide transition template saved'), array('data' => array('id' => $return)));
                    }
                break;
                case 'delete_custom_templates_slidetransitions':
                    if ($this->delete_custom_slidetransitions($data)) {
                        $this->ajax_response_success(__('Slide transition template deleted'));
                    } else {
                        $this->ajax_response_error(__('Slide transition template could not be deleted'));
                    }
                break;
                case 'create_image_from_raw':
                    $mpeg = $this->get_val($data, 'mpeg', '');
                    $slideid = $this->get_val($data, 'slideid', 0);
                    $bitmap = $this->get_val($data, 'bitmap', '');
                    $mpeg = basename($mpeg);
                    if(empty($mpeg)) {
                        $this->ajax_response_error(__('mpeg not set'));
                    }

                    $return = $this->import_media_raw($mpeg, $slideid, $bitmap);
                    if(!is_array($return) && ($return === false || intval($return) === 0)){
                        if ($return === false) {
                            $this->ajax_response_error(__('Image could not be created'));
                        } else {
                            $this->ajax_response_error($return);
                        }
                    }
                    if(isset($return['id'])){
                        $return['path'] = FA::wp_get_attachment_url($return['id']);
                    }

                    $this->ajax_response_data($return);
                break;
				case 'get_v5_slider_list':
					$admin = new RevSliderFunctionsAdmin();
					$sliders = $admin->get_v5_slider_data();

					$this->ajax_response_data(array('slider' => $sliders));
				break;
				case 'reimport_v5_slider':
					$status = false;
					if(!empty($data['id'])){
						$admin = new RevSliderFunctionsAdmin();
						$status = $admin->reimport_v5_slider($data['id']);

                        $cache = RevSliderGlobals::instance()->get('RevSliderCache');
                        $cache->clear_all_transients();
					}
					if($status === false){
						$this->ajax_response_error(__('Slider could not be transfered to v6'));
					}else{
						$this->ajax_response_success(__('Slider transfered to v6'));
					}
				break;
				default:
					$return = ''; //''is not allowed to be added directly in FA::apply_filters(), so its needed like this
					$return = FA::apply_filters('revslider_do_ajax', $return, $action, $data);
					if($return){
						if(is_array($return)){
							if(isset($return['error'])){
								$this->ajax_response_error($return['error']);
							}
							if(isset($return['message'])){
								$this->ajax_response_data(array('message' => $return['message'], 'data' => $return['data']));
							}

							$this->ajax_response_data(array('data' => $return['data']));
						}else{
							$this->ajax_response_success($return);
						}
					}
				break;
			}
		}catch(\Exception $e){
			$message = $e->getMessage();
			if(in_array($action, array('preview_slide', 'preview_slider'))){
				echo $message;
				FA::wp_die();
			}
			$this->ajax_response_error($message);
		}

		//it's an ajax action, so exit
		$this->ajax_response_error(__('No response on action'));
		FA::wp_die();
	}

	/**
	 * Ajax handling for frontend, no privileges here
	 */
	public function do_front_ajax_action(){
		$token = $this->get_post_var('token', false);

		$error = false;
        switch($this->get_post_var('client_action', false)){
            case 'get_transitions':
                $transitions = $this->get_base_transitions();
                $this->ajax_response_data(array('transitions' => $transitions));
            break;
            case 'get_slider_html':
                $alias = $this->get_post_var('alias', '');
                $usage = $this->get_post_var('usage', '');
                $modal = $this->get_post_var('modal', '');
                $layout = $this->get_post_var('layout', '');
                $offset = $this->get_post_var('offset', '');
                $id = intval($this->get_post_var('id', 0));

                //check if $alias exists in database, transform it to id
                if($alias !== ''){
                    $sr = new RevSliderSlider();
                    $id = intval($sr->alias_exists($alias, true));
                }

                if($id > 0){
                    ob_start();
                    $slider = new RevSliderOutput();
                    $slider->set_ajax_loaded();

                    $slider_class = $slider->add_slider_to_stage($id, $usage, $layout, $offset, $modal);
                    $html = ob_get_contents();
                    ob_clean();
                    ob_end_clean();

                    $result = !empty($slider_class) && $html !== '';

                    if(!$result){
                        $error = __('Slider not found');
                    }else{
                        if($html !== false){
							$htmlid = $slider->get_html_id();
							$return = array('data' => $html, 'waiting' => array(), 'toload' => array(), 'htmlid' => $htmlid);
                            $return = FA::apply_filters('revslider_get_slider_html_addition', $return, $slider);
                            $this->ajax_response_data($return);
						}else{
                            $error = __('Slider not found');
						}
					}
                }else{
                    $error = __('No Data Received');
                }
            break;
        }

		if($error !== false){
			$show_error = ($error !== true) ? __('Loading Error') : __('Loading Error: ') . $error;

			$this->ajax_response_error($show_error, false);
		}
		exit;
	}

	/**
	 * echo json ajax response as error
	 * @before: RevSliderBaseAdmin::ajaxResponseError();
	 */
    public function ajax_response_error($message, $data = null){
        $this->ajax_response(false, $message, $data);
	}

	/**
	 * echo ajax success response with redirect instructions
	 * @before: RevSliderBaseAdmin::ajaxResponseSuccessRedirect();
	 */
    public function ajax_response_redirect($message, $url){
		$data = array('is_redirect' => true, 'redirect_url' => $url);

        $this->ajax_response(true, $message, $data);
    }

	/**
	 * echo json ajax response, without message, only data
	 * @before: RevSliderBaseAdmin::ajaxResponseData()
	 */
    public function ajax_response_data($data){
        $data = (gettype($data) == 'string') ? array('data' => $data) : $data;

		$this->ajax_response(true, '', $data);
	}

	/**
	 * echo ajax success response
	 * @before: RevSliderBaseAdmin::ajaxResponseSuccess();
	 */
    public function ajax_response_success($message, $data = null){

		$this->ajax_response(true, $message, $data, true);
	}

	/**
	 * echo json ajax response
	 * before: RevSliderBaseAdmin::ajaxResponse
     * @param bool $success
     * @param string $message
     * @param mixed $data
     */
	private function ajax_response($success, $message, $data = null){

		$response = array(
			'success' => $success,
			'message' => $message,
		);

		if(!empty($data)){
			if(gettype($data) == 'string'){
				$data = array('data' => $data);
			}

			$response = array_merge($response, $data);
		}

		echo json_encode($response);

		FA::wp_die();
	}

	/**
	 * set the page that should be shown
	 **/
	private function set_current_page(){
        $this->view = $this->get_get_var('view', 'sliders');
	}

	/**
	 * include/display the previously set page
	 * only allow certain pages to be showed
	 **/
	public function display_admin_page(){
		try{
			if(!in_array($this->view, $this->allowed_views)){
				$this->throw_error(__('Bad Request'));
			}

            switch($this->view){
                //switch URLs to corresponding php files
                case 'slide':
                    $view = 'builder';
				break;
                case 'sliders':
                default:
                    $view = 'overview';
				break;
			}

			$this->validate_filepath($this->path_views . $view . '.php', 'View');

			require $this->path_views . 'header.php';
			require $this->path_views . $view . '.php';
			require $this->path_views . 'footer.php';

		}catch (\Exception $e){
			$this->show_error($this->view, $e->getMessage());
		}
	}


	/**
	 * show an nice designed error
	 **/
	public function show_error($view, $message){
		echo '<div class="rs-error">';
		echo __('Slider Revolution encountered the following error: ');
		echo FA::esc_attr($view);
		echo ' - Error: <span>';
		echo FA::esc_attr($message);
		echo '</span>';
		echo '</div>';
		exit;
	}


	/**
	 * validate that some file exists, if not - throw error
	 * @before: RevSliderFunctions::validateFilepath
	 */
	public function validate_filepath($filepath, $prefix = null){
		if(file_exists($filepath) == true) return true;

		$prefix	 = ($prefix == null) ? 'File' : $prefix;
		$message = $prefix.' '.FA::esc_attr($filepath).' not exists!';

		$this->throw_error($message);
	}

	/**
	 * esc attr recursive
	 * @since: 6.0
	 */
	public static function esc_js_deep($value){
		$value = is_array($value) ? array_map(array('\Nwdthemes\Revslider\Model\Revslider\Admin\RevSliderAdmin', 'esc_js_deep'), $value) : FA::esc_js($value);

		return $value;
	}

}