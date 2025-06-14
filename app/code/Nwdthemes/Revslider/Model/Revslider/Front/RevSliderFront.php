<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2022 ThemePunch
 */

namespace Nwdthemes\Revslider\Model\Revslider\Front;

use \Nwdthemes\Revslider\Helper\Framework;
use \Nwdthemes\Revslider\Helper\Query;
use \Nwdthemes\Revslider\Model\FrameworkAdapter as FA;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderFunctions;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderGlobals;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderOutput;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderSlider;

class RevSliderFront extends RevSliderFunctions {

	const TABLE_BACKUP			 = 'nwdthemes_revslider_backup';
	const TABLE_SLIDER			 = 'nwdthemes_revslider_sliders';
	const TABLE_SLIDES			 = 'nwdthemes_revslider_slides';
	const TABLE_STATIC_SLIDES	 = 'nwdthemes_revslider_static_slides';
	const TABLE_CSS				 = 'nwdthemes_revslider_css';
	const TABLE_LAYER_ANIMATIONS = 'nwdthemes_revslider_animations';
	const TABLE_NAVIGATIONS		 = 'nwdthemes_revslider_navigations';
	const TABLE_SETTINGS		 = 'nwdthemes_revslider_settings'; //existed prior 5.0 and still needed for updating from 4.x to any version after 5.x
    const CURRENT_TABLE_VERSION	 = '1.0.12';

	const YOUTUBE_ARGUMENTS		 = 'hd=1&amp;wmode=opaque&amp;showinfo=0&amp;rel=0';
	const VIMEO_ARGUMENTS		 = 'title=0&amp;byline=0&amp;portrait=0&amp;api=1';

	public function __construct() {
		FA::add_action('wp_enqueue_scripts', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'add_actions'));
        FA::add_filter('wp_img_tag_add_loading_attr', array('RevSliderFront', 'check_lazy_loading'), 99, 3);
	}


	/**
	 * START: DEPRECATED FUNCTIONS THAT ARE IN HERE FOR OLD ADDONS TO WORK PROPERLY
	 **/

	/**
	 * old version of add_admin_bar();
	 **/
	public static function putAdminBarMenus(){
		return RevSliderFront::add_admin_bar();
	}

	/**
	 * END: DEPRECATED FUNCTIONS THAT ARE IN HERE FOR OLD ADDONS TO WORK PROPERLY
	 **/

	/**
	 * Add all actions that the frontend needs here
	 **/
	public static function add_actions(){

        $func    = RevSliderGlobals::instance()->get('RevSliderFunctions');
		$global	 = $func->get_global_settings();

		FA::add_action('wp_head', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'add_meta_generator'));
		FA::add_action('wp_head', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'js_set_start_size'), 99);
		FA::add_action('admin_head', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'js_set_start_size'), 99);
        FA::add_action('wp_footer', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'add_inline_css'), 10);
        FA::add_action('wp_footer', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'load_icon_fonts'), 11);
		FA::add_action('wp_footer', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'load_google_fonts'));
        FA::add_action('wp_footer', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'add_waiting_script'), 1);
        FA::add_action('wp_print_footer_scripts', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'add_inline_js'), 100);

        //defer JS Loading
        if($func->_truefalse($func->get_val($global, array('script', 'defer'), true)) === true){
            FA::add_filter('script_loader_tag', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'add_defer_forscript'), 11, 2);
        }

		//Async JS Loading
        if($func->_truefalse($func->get_val($global, array('script', 'async'), true)) === true){
            FA::add_filter('script_loader_tag', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'add_async_forscript'), 11, 2);
        }
	}

    /**
     * add css to the footer
     **/
    public static function add_inline_css(){
        $css     = RevSliderGlobals::instance()->get('RevSliderCssParser');
        /**
         * Fix for WordPress versions below 3.7
         **/
        $custom_css = $css->get_static_css();
        $custom_css = $css->compress_css($custom_css);

        if(!empty(Framework::$rs_css_collection)){
            $custom_css .= Framework::RS_T2;
            $custom_css .= implode("\n".Framework::RS_T2, Framework::$rs_css_collection);
        }

        $custom_css = (trim($custom_css) == '') ? '#rs-demo-id {}' : $custom_css;

        if(strpos($custom_css, 'revicon') !== false) Framework::$rs_revicons = true;

        FA::wp_add_inline_style('rs-plugin-settings', $custom_css);
    }

    /**
     * add all the JavaScript from the Sliders to the footer
     **/
    public static function add_inline_js(){

        if(empty(Framework::$rs_js_collection)) return true;
        if(empty(Framework::$rs_js_collection['revapi'])) return true;

        echo '<script id="rs-initialisation-scripts">'."\n";
        echo Framework::RS_T2.'if(window.RS_MODULES === undefined) window.RS_MODULES = {};'."\n";
        echo Framework::RS_T2.'if(RS_MODULES.modules === undefined) RS_MODULES.modules = {};'."\n";
        echo Framework::RS_T2.'var    '.implode(',', Framework::$rs_js_collection['revapi']) . ';'."\n";
        if(!empty(Framework::$rs_js_collection['js'])){
            echo "\n" . implode("\n", Framework::$rs_js_collection['js']);
        }
        if(!empty(Framework::$rs_js_collection['minimal'])){
            echo "\n" . Framework::$rs_js_collection['minimal'];
        }

        echo Framework::RS_T.'</script>'."\n";

    }

	public static function welcome_screen_activate(){
		FA::set_transient('_revslider_welcome_screen_activation_redirect', true, 60);
	}

	/**
	 * Add Meta Generator Tag in FrontEnd
	 * @since: 5.0
	 */
	public static function add_meta_generator(){
		echo FA::apply_filters('revslider_meta_generator', '<meta name="generator" content="Powered by Slider Revolution ' . Framework::RS_REVISION . ' - responsive, Mobile-Friendly Slider Plugin for WordPress with comfortable drag and drop interface." />' . "\n");
	}

	/**
	 * Load Used Icon Fonts
	 * @since: 5.0
	 */
	public static function load_icon_fonts(){
        $func    = RevSliderGlobals::instance()->get('RevSliderFunctions');
		$global	= $func->get_global_settings();
		$ignore_fa = $func->_truefalse($func->get_val($global, 'fontawesomedisable', false));

        echo Framework::$rs_revicons ? Framework::RS_T3.'<link rel="preload" as="font" id="rs-icon-set-revicon-woff" href="' . Framework::$RS_PLUGIN_URL . 'public/assets/fonts/revicons/revicons.woff?5510888" type="font/woff" crossorigin="anonymous" media="all" />'."\n" : '';
        echo ($ignore_fa === false && (Framework::$fa_icon_var == true || Framework::$fa_var == true)) ? Framework::RS_T3.'<link rel="preload" as="font" id="rs-icon-set-fa-icon-woff" type="font/woff2" crossorigin="anonymous" href="' . Framework::$RS_PLUGIN_URL . 'public/assets/fonts/font-awesome/fonts/fontawesome-webfont.woff2?v=4.7.0" media="all" />'."\n" : '';
        echo ($ignore_fa === false && (Framework::$fa_icon_var == true || Framework::$fa_var == true)) ? Framework::RS_T3.'<link rel="stylesheet" property="stylesheet" id="rs-icon-set-fa-icon-css" href="' . Framework::$RS_PLUGIN_URL . 'public/assets/fonts/font-awesome/css/font-awesome.min.css" type="text/css" media="all" />'."\n" : '';

        echo (Framework::$pe_7s_var) ? Framework::RS_T3.'<link rel="stylesheet" property="stylesheet" id="rs-icon-set-pe-7s-css" href="' . Framework::$RS_PLUGIN_URL . 'public/assets/fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.min.css" type="text/css" media="all" />'."\n" : '';
    }


	/**
	 * Load Used Google Fonts
	 * add google fonts of all sliders found on the page
	 * @since: 6.0
	 */
	public static function load_google_fonts(){
		$func	= RevSliderGlobals::instance()->get('RevSliderFunctions');
		$fonts	= $func->print_clean_font_import();
		if(!empty($fonts)){
			echo $fonts."\n";
		}
	}

    /**
     * add the scripts that needs to be waited on
     * @since: 6.4.12
     **/
    public static function add_waiting_script(){
        $func    = RevSliderGlobals::instance()->get('RevSliderFunctions');
        $global    = $func->get_global_settings();
        $wait    = array();
        $wait    = FA::apply_filters('revslider_modify_waiting_scripts', $wait);
        ?>

        <script>
            window.RS_MODULES = window.RS_MODULES || {};
            window.RS_MODULES.modules = window.RS_MODULES.modules || {};
            window.RS_MODULES.waiting = window.RS_MODULES.waiting || [];
            window.RS_MODULES.defered = <?php echo ($func->_truefalse($func->get_val($global, array('script', 'defer'), true)) === true) ? 'true' : 'false'; ?>;
            <?php if (!empty($wait)) {?>
            window.RS_MODULES.waiting = window.RS_MODULES.waiting.concat([ <?php echo '"'. implode('","', $wait) . '"'; ?>]);
            <?php }; ?>window.RS_MODULES.moduleWaiting = window.RS_MODULES.moduleWaiting || {};
            window.RS_MODULES.type = 'compiled';
        </script>
        <?php
    }

	/**
	 * add admin menu points in ToolBar Top
	 * @since: 5.0.5
	 * @before: putAdminBarMenus()
	 */
	public static function add_admin_bar(){
		if(!FA::is_super_admin() || !FA::is_admin_bar_showing()){
			return;
		}

		?>
		<script>
			function rs_adminBarToolBarTopFunction() {
				if(jQuery('#wp-admin-bar-revslider-default').length > 0 && jQuery('rs-module-wrap').length > 0){
					var aliases = new Array();
					jQuery('rs-module-wrap').each(function(){
						aliases.push(jQuery(this).data('alias'));
					});

					if(aliases.length > 0){
						jQuery('#wp-admin-bar-revslider-default li').each(function(){
							var li = jQuery(this),
								t = li.find('.ab-item .rs-label').data('alias'); //text()
							t = t!==undefined && t!==null ? t.trim() : t;
							if(jQuery.inArray(t,aliases)!=-1){
							}else{
								li.remove();
							}
						});
					}
				}else{
					jQuery('#wp-admin-bar-revslider').remove();
				}
			}
			var adminBarLoaded_once = false
			if (document.readyState === "loading")
				document.addEventListener('readystatechange',function(){
					if ((document.readyState === "interactive" || document.readyState === "complete") && !adminBarLoaded_once) {
						adminBarLoaded_once = true;
						rs_adminBarToolBarTopFunction()
					}
				});
			else {
				adminBarLoaded_once = true;
				rs_adminBarToolBarTopFunction();
			}
		</script>
		<?php
	}

	/**
	 * check that loading="lazy" is not written in slider HTML
	 **/
	public static function check_lazy_loading($value, $image, $context){
		return (strpos($image, 'tp-rs-img') !== false) ? false : $value;
	}

	/**
	 * adds async loading
	 * @since: 5.0
     * @updated: 6.4.12
     */
    public static function add_defer_forscript($tag, $handle){
		if(strpos($tag, 'rs6') === false && strpos($tag, 'rbtools.min.js') === false && strpos($tag, 'revolution.addon.') === false && strpos($tag, 'public/assets/js/libs/') === false && (strpos($tag, 'liquideffect') === false && strpos($tag, 'pixi.min.js') === false) && strpos($tag, 'rslottie-js') === false){
            return $tag;
        }elseif(FA::is_admin()){
            return $tag;
        }else{
            return str_replace(' id=', ' defer id=', $tag);
        }
    }

    /**
     * adds async loading
     * @since: 5.0
     * @updated: 6.4.12
	 */
    public static function add_async_forscript($tag, $handle){
		if(strpos($tag, 'rs6') === false && strpos($tag, 'rbtools.min.js') === false && strpos($tag, 'revolution.addon.') === false && strpos($tag, 'public/assets/js/libs/') === false && (strpos($tag, 'liquideffect') === false && strpos($tag, 'pixi.min.js') === false) && strpos($tag, 'rslottie-js') === false){
            return $tag;
        }elseif(FA::is_admin()){
            return $tag;
        }else{
            return str_replace(' id=', ' async id=', $tag);
        }
	}

	/**
	 * Add Meta Generator Tag in FrontEnd
	 * @since: 5.4.3
	 * @before: add_setREVStartSize()
	 */
	public static function js_set_start_size(){
		if(Framework::$revslider_rev_start_size_loaded === true) return false;

		$script = '<script>';
		$script .= 'function setREVStartSize(t){window.RSIW=void 0===window.RSIW?window.innerWidth:window.RSIW,window.RSIH=void 0===window.RSIH?window.innerHeight:window.RSIH;try{var h=0===(h=document.getElementById(t.c).parentNode.offsetWidth)||isNaN(h)||"fullwidth"==t.l||"fullwidth"==t.layout?window.RSIW:h;if(t.tabw=void 0===t.tabw?0:parseInt(t.tabw),t.thumbw=void 0===t.thumbw?0:parseInt(t.thumbw),t.tabh=void 0===t.tabh?0:parseInt(t.tabh),t.thumbh=void 0===t.thumbh?0:parseInt(t.thumbh),t.tabhide=void 0===t.tabhide?0:parseInt(t.tabhide),t.thumbhide=void 0===t.thumbhide?0:parseInt(t.thumbhide),t.mh=void 0===t.mh||""==t.mh||"auto"===t.mh?0:parseInt(t.mh,0),"fullscreen"===t.layout||"fullscreen"===t.l)d=Math.max(t.mh,window.RSIH);else{for(var e in t.gw=Array.isArray(t.gw)?t.gw:[t.gw],t.rl)void 0!==t.gw[e]&&0!==t.gw[e]||(t.gw[e]=t.gw[e-1]);for(var e in t.gh=void 0===t.el||""===t.el||Array.isArray(t.el)&&0==t.el.length?t.gh:t.el,t.gh=Array.isArray(t.gh)?t.gh:[t.gh],t.rl)void 0!==t.gh[e]&&0!==t.gh[e]||(t.gh[e]=t.gh[e-1]);var i,a=new Array(t.rl.length),r=0;for(e in t.tabw=t.tabhide>=h?0:t.tabw,t.thumbw=t.thumbhide>=h?0:t.thumbw,t.tabh=t.tabhide>=h?0:t.tabh,t.thumbh=t.thumbhide>=h?0:t.thumbh,t.rl)a[e]=t.rl[e]<window.RSIW?0:t.rl[e];for(e in i=a[0],a)i>a[e]&&0<a[e]&&(i=a[e],r=e);var w=h>t.gw[r]+t.tabw+t.thumbw?1:(h-(t.tabw+t.thumbw))/t.gw[r],d=t.gh[r]*w+(t.tabh+t.thumbh)}w=document.getElementById(t.c);null!==w&&w&&(w.style.height=d+"px"),null!==(w=document.getElementById(t.c+"_wrapper"))&&w&&(w.style.height=d+"px",w.style.display="block")}catch(t){console.log("Failure at Presize of Slider:"+t)}};';
		$script .= '</script>' . "\n";
		echo FA::apply_filters('revslider_add_setREVStartSize', $script);

		Framework::$revslider_rev_start_size_loaded = true;
	}

	/**
	 * sets the post saving value to true, so that the output echo will not be done
	 **/
	public static function set_post_saving(){
	}

	/**
	 * check the current post for the existence of a short code
	 * @before: hasShortcode()
	 */
	public static function has_shortcode($shortcode = ''){
		$found = false;

		if(empty($shortcode)) return false;
		if(!FA::is_singular()) return false;

		$post = FA::get_post(FA::get_the_ID());
		if(stripos($post->post_content, '[' . $shortcode) !== false) $found = true;

		return $found;
	}

	/**
	 * Create Tables
	 * @only_base needs to be false
	 *  it can only be true by fixing database issues
	 *  this protects that the _bkp tables are not filled after
	 *  we are already on version 6.0
	 **/
	public static function create_tables($only_base = false){
		$table_version = FA::get_option('revslider_table_version', '1.0.0');

		if(version_compare($table_version, self::CURRENT_TABLE_VERSION, '<')){
			$wpdb = FA::getQueryHelper();

			//create CSS entries
			$result = $wpdb->get_row("SELECT COUNT( DISTINCT id ) AS NumberOfEntrys FROM " . $wpdb->prefix . self::TABLE_CSS);
			if(!empty($result) && $result->NumberOfEntrys == 0){
				$css_class = RevSliderGlobals::instance()->get('RevSliderCssParser');
				$css_class->import_css_captions();
			}

			FA::update_option('revslider_table_version', self::CURRENT_TABLE_VERSION);
			//$table_version = self::CURRENT_TABLE_VERSION;
		}


		/**
		 * check if table version is below 1.0.8.
		 * if yes, duplicate the tables into _bkp
		 * this way, we can revert back to v5 if any slider
		 * has issues in the v6 migration process
		 **/
		if(version_compare($table_version, '1.0.8', '<') && ($only_base === false || $only_base === '')){
			FA::backupDB();
		}
	}


	/**
	 * get the images from posts/pages for yoast seo
	 **/
	public static function get_images_for_seo($url, $type, $user){
		if(in_array($type, array('user', 'term'), true)) return $url;
		if(!is_object($user) || !isset($user->ID)) return $url;

		$post = FA::get_post($user->ID);
		if(is_a($post, 'WP_Post') && FA::has_shortcode($post->post_content, 'rev_slider')){
			preg_match_all('/\[rev_slider.*alias=.(.*)"\]/', $post->post_content, $shortcodes);

			if(isset($shortcodes[1]) && $shortcodes[1] !== ''){
				foreach($shortcodes[1] as $s){
                    if(strpos($s, '"') !== false){
                        $s = explode('"', $s);
                        $s = (isset($s[0])) ? $s[0] : '';
                    }
					if(!RevSliderSlider::alias_exists($s)) continue;

					$sldr = new RevSliderSlider();
					$sldr->init_by_alias($s);
					$sldr->get_slides();
					$imgs = $sldr->get_images();
					if(!empty($imgs)){
						if(!isset($url['images'])) $url['images'] = array();
						foreach($imgs as $v){
							$url['images'][] = $v;
						}
					}
				}
			}
		}

		return $url;
	}

}
