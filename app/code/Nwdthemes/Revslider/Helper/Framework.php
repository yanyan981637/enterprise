<?php

namespace Nwdthemes\Revslider\Helper;

use \Magento\Framework\UrlInterface;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Nwdthemes\Revslider\Model\FrameworkAdapter;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderGlobals;

class Framework extends \Magento\Framework\App\Helper\AbstractHelper {

    const CACHE_PREFIX = 'nwdthemes-revslider-cache-';
    const CACHE_TAG = 'nwdrevslider_cache';
    const TRANSIENT_PREFIX = 'nwdthemes-revslider-transient-';
    const SLIDER_TRANSIENT_TAG = 'nwdrevslider_slider';

	const SVG_URL_PACEHOLDER = '{revslider_base_svg_url}';
    const SVG_PATH = 'public/assets/assets/svg/';

	const WPINC = true;
	const WP_CONTENT_DIR = 'revslider';
    const WP_MAX_MEMORY_LIMIT = '1024M';
	const RS_DEMO = false;
	const MODULE = 'Nwdthemes_Revslider';
	const RS_REVISION = '6.5.3.3';
	const RS_TP_TOOLS = '6.5.3';
	const RS_PLUGIN_SLUG_PATH = 'nwdthemes/revslider';

    const RS_T   = '	';
    const RS_T2  = '		';
    const RS_T3  = '			';
    const RS_T4  = '				';
    const RS_T5  = '					';
    const RS_T6  = '						';
    const RS_T7  = '							';
    const RS_T8  = '								';
    const RS_T9  = '									';
    const RS_T10 = '										';
    const RS_T11 = '											';

	protected $_authSession;
	protected $_backendUrl;
    protected $_filterProvider;
    protected $_customerGroupCollection;
    protected $_pageProvider;
    protected $_cacheProxy;
    protected $_scopeConfig;
    protected $_redirect;
    protected $_formKey;
    protected $_filesystem;
    protected $_date;
    protected $_assetRepo;
    protected $_minification;
    protected $_templateFileResolver;
    protected $_layoutFactory;
    protected $_storeManager;

    protected $_curlHelper;
    protected $_filesystemHelper;
    protected $_imagesHelper;
    protected $_optionsHelper;
    protected $_pluginHelper;
    protected $_productsHelper;
    protected $_queryHelper;
    private $_registerHelper;

    protected $_areaCode;
    protected $_request;
    protected $_directory;

	public static $RS_PLUGIN_PATH;
	public static $RS_PLUGIN_URL;
	public static $RS_PLUGIN_SLUG;
	public static $WP_PLUGIN_URL;

	// to get rid of globals
	public static $rs_material_icons_css = false;
	public static $rs_material_icons_css_parsed = false;
	public static $rs_slider_serial = 0;
	public static $rs_ids_collection = array();
    public static $rs_preview_mode = false;
    public static $rs_js_collection = array('revapi' => array(), 'js' => array(), 'minimal' => '');
    public static $rs_css_collection = array();
	public static $revslider_fonts = array('queue' => array(), 'loaded' => array());
	public static $revslider_addon_notice_merged = 0;
    public static $revslider_animations = array();
	public static $revslider_rev_start_size_loaded = false;
    public static $rs_do_init_action = true;

    public static $fa_icon_var;
    public static $fa_var;
    public static $pe_7s_var;

    /**
     * @var array
     */
    protected $_overviewData;

    /**
     * @var string
     */
    protected $_currentFilter;

    /**
     * List of scripts to require
     *
     * @var array
     */
    protected $_scriptsToRequire = [];

	/**
	 *	Constructor
	 */
	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\App\State $appState,
		\Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Cms\Model\PageFactory $pageFactory,
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
		\Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupCollection,
		\Magento\Framework\App\Cache\Proxy $cacheProxy,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Response\Http $redirect,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\Asset\Minification $minification,
		\Magento\Framework\View\Element\Template\File\Resolver $templateFileResolver,
		\Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Nwdthemes\Revslider\Helper\Curl $curlHelper,
        \Nwdthemes\Revslider\Helper\Filesystem $filesystemHelper,
        \Nwdthemes\Revslider\Helper\Images $imagesHelper,
        \Nwdthemes\Revslider\Helper\Options $optionsHelper,
        \Nwdthemes\Revslider\Helper\Plugin $pluginHelper,
        \Nwdthemes\Revslider\Helper\Products $productsHelper,
        \Nwdthemes\Revslider\Helper\Query $queryHelper,
        \Nwdthemes\Revslider\Helper\Register $registerHelper
    ) {
        $this->_appState = $appState;
        $this->_authSession = $authSession;
        $this->_backendUrl = $backendUrl;
        $this->_filterProvider = $filterProvider;
        $this->_customerGroupCollection = $customerGroupCollection;
        $this->_pageFactory = $pageFactory;
        $this->_cacheProxy = $cacheProxy;
        $this->_scopeConfig = $scopeConfig;
        $this->_redirect = $redirect;
        $this->_formKey = $formKey;
        $this->_filesystem = $filesystem;
        $this->_date = $date;
        $this->_assetRepo = $assetRepo;
        $this->_minification = $minification;
        $this->_templateFileResolver = $templateFileResolver;
        $this->_layoutFactory = $layoutFactory;
        $this->_storeManager = $storeManager;

        $this->_curlHelper = $curlHelper;
        $this->_filesystemHelper = $filesystemHelper;
        $this->_imagesHelper = $imagesHelper;
        $this->_optionsHelper = $optionsHelper;
        $this->_pluginHelper = $pluginHelper;
        $this->_productsHelper = $productsHelper;
        $this->_queryHelper = $queryHelper;
        $this->_registerHelper = $registerHelper;

        $this->_areaCode = $appState->getAreaCode();

        $this->_request = $context->getRequest();
        $this->_directory = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        parent::__construct($context);

        self::$RS_PLUGIN_PATH = $this->getAssetPath() . DIRECTORY_SEPARATOR;
		self::$RS_PLUGIN_URL = $this->getAssetUrl() . '/';
        self::$RS_PLUGIN_SLUG = $this->apply_filters('set_revslider_slug', Data::REVSLIDER_PRODUCT);
		self::$WP_PLUGIN_URL = $this->_storeManager
			->getStore()
			->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . Plugin::WP_PLUGIN_DIR;

        // add filters to make image url relative
        $this->add_filter('revslider_slider_saveData', array($this, 'sliderSaveDataFilter'), 10, 1);
        $this->add_filter('revslider_slide_saveParams', array($this, 'slideSaveParamsFilter'), 10, 3);
		$this->add_filter('revslider_slide_saveLayers', array($this, 'slideSaveLayersFilter'), 10, 3);

		// add filters to make image url absolute
        $this->add_filter('revslider_slider_init_by_data', array($this, 'sliderInitByDataFilter'), 10, 1);
        $this->add_filter('revslider_slide_init_by_data', array($this, 'slideInitByDataFilter'), 10, 1);

        // Inject into adapter
        FrameworkAdapter::injectFramework($this);
	}

	/**
	 *	Check if unserialized
	 *
	 *	@param	var	Original
	 *	@return	var
	 */

	public function maybe_unserialize($original)
	{
		if ( $this->is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
			return @unserialize( $original );
		return $original;
	}

	/**
	 * Check value to find if it was serialized.
	 *
	 * @param string $data   Value to check to see if was serialized.
	 * @param bool   $strict Optional. Whether to be strict about the end of the string. Default true.
	 * @return bool False if not serialized and true if it was.
	 */

	public function is_serialized( $data, $strict = true ) {
		// if it isn't a string, it isn't serialized.
		if ( ! is_string( $data ) ) {
			return false;
		}
		$data = trim( $data );
		if ( 'N;' == $data ) {
			return true;
		}
		if ( strlen( $data ) < 4 ) {
			return false;
		}
		if ( ':' !== $data[1] ) {
			return false;
		}
		if ( $strict ) {
			$lastc = substr( $data, -1 );
			if ( ';' !== $lastc && '}' !== $lastc ) {
				return false;
			}
		} else {
			$semicolon = strpos( $data, ';' );
			$brace     = strpos( $data, '}' );
			// Either ; or } must exist.
			if ( false === $semicolon && false === $brace )
				return false;
			// But neither must be in the first X characters.
			if ( false !== $semicolon && $semicolon < 3 )
				return false;
			if ( false !== $brace && $brace < 4 )
				return false;
		}
		$token = $data[0];
		switch ( $token ) {
			case 's' :
				if ( $strict ) {
					if ( '"' !== substr( $data, -2, 1 ) ) {
						return false;
					}
				} elseif ( false === strpos( $data, '"' ) ) {
					return false;
				}
				// or else fall through
			case 'a' :
			case 'O' :
				return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
			case 'b' :
			case 'i' :
			case 'd' :
				$end = $strict ? '$' : '';
				return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
		}
		return false;
	}

	/**
	 *	Output checkbox checked
     *
     *	@param	string	Value
     *	@param	string	State (on/off)
     *	@param	boolean $output
     */
	function checked($value = '', $state = 'on', $output = true) {
		$result = $value == $state ? 'checked="checked"' : '';
		if ($output) {
			echo $result;
		}
		return $result;
	}

	/**
	 *	Output select option selected
	 *
	 *	@param	string	Value
	 *	@param	string	State
     *	@param	boolean	Output
	 */

	public function selected($value = '', $state = '', $output = true) {
		if ( $value == $state ) {
            if ($output) {
    			echo 'selected="selected"';
            } else {
                return 'selected="selected"';
            }
		}
	}

	/**
	 *	Does nothing
	 *
	 *	@param	string	Nounce
	 *	@param	string	Actions
	 *	@return	boolean	True
	 */

	public function wp_verify_nonce($nonce = '', $actions = '')
	{
		return TRUE;
	}

	/**
	 *	Does nothing
	 *
	 *	@param	string	Content
	 *	@param	Nwdthemes\Revslider\Model\Revslider\RevSliderSlide	Slide
	 *	@return	string	Content
	 */

	public function do_shortcode($content = '', $slide = false) {
        $filter = $this->_filterProvider
            ->getBlockFilter()
            ->setStoreId($this->getStoreId());
        if ($slide && $slide->isFromPost()) {
            $content = $slide->set_post_data($content, $slide->getPostData(), $slide->getID());
        }

        // add missing slashes to block class in CMS shortcode
        preg_match_all('/{{.*class="([a-zA-Z]+)".*}}/U', $content, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $match) {
            preg_match_all('/([A-Z]([a-z]+))/', $match[1], $_matches, PREG_SET_ORDER, 0);
            $className = '';
            foreach ($_matches as $_key => $_match) {
                if ($_key) {
                    $className .= '\\';
                }
                $className .= $_match[0];
            }
            $shortCode = str_replace($match[1], $className, $match[0]);
            $content = str_replace($match[0], $shortCode, $content);
        }

		return $this->forceSSL(__($filter->filter($content)));
	}

	/**
	 *	Check if has shortcode
	 *
	 *	@param	string $content
	 *	@param	string $keyword
	 *	@return	boolean
	 */
	public function has_shortcode($content, $keyword) {
		return strpos($content, '[' . $keyword) !== false;
	}

	/**
	 *	Does nothing
	 *
	 *	@return	string
	 */

	public function wp_create_nonce() {
        return $this->_formKey->getFormKey();
	}

	/**
	 *	Does nothing
	 *
	 *	@return	boolean	False
	 */

	public function is_multisite()
	{
		return FALSE;
	}

	/**
	 *	Returns content url
	 *
	 *	@return	string
	 */

	public function content_url() {
		return $this->_storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . self::WP_CONTENT_DIR;
	}

	/**
	 *	Gets Get Param
	 *
	 *	@param	string	Handle
	 *	@param	string	Default
	 *	@return	string	Value
	 */

	public function get_param($handle = '', $default = '')
	{
		$ci = &get_instance();
		$value = $ci->input->get($handle);
		return $value === FALSE ? $default : $value;
	}

	/**
	 *	Replicates WP style pagination
	 *
	 *	@param	array	Arguments
	 *	@return	string	Pagination html
	 */

	public function paginate_links( $args = array() ) {

		$total        = 1;
		$current      = $this->_request->getParam('paged', 1);

		$defaults = array(
			'total' => $total,
			'current' => $current,
			'show_all' => false,
			'prev_next' => true,
			'prev_text' => __('&laquo; Previous'),
			'next_text' => __('Next &raquo;'),
			'end_size' => 1,
			'mid_size' => 2,
			'type' => 'plain',
			'add_args' => false, // array of query args to add
			'add_fragment' => '',
			'before_page_number' => '',
			'after_page_number' => ''
		);

		$args = array_merge( $defaults, $args );

		// Who knows what else people pass in $args
		$total = (int) $args['total'];
		if ( $total < 2 ) {
			return;
		}
		$current  = (int) $args['current'];
		$end_size = (int) $args['end_size']; // Out of bounds?  Make it the default.
		if ( $end_size < 1 ) {
			$end_size = 1;
		}
		$mid_size = (int) $args['mid_size'];
		if ( $mid_size < 0 ) {
			$mid_size = 2;
		}
		$add_args = is_array( $args['add_args'] ) ? $args['add_args'] : false;
		$r = '';
		$page_links = array();
		$dots = false;

		if ( $args['prev_next'] && $current && 1 < $current ) :
			$link = str_replace( '%_%', 2 == $current ? '' : $args['format'], $args['base'] );
			$link = str_replace( '%#%', $current - 1, $link );
			if ( $add_args )
				$link = $this->add_query_arg( $add_args, $link );
			$link .= $args['add_fragment'];

			/**
			 * Filter the paginated links for the given archive pages.
			 *
			 * @since 3.0.0
			 *
			 * @param string $link The paginated link URL.
			 */
			$page_links[] = '<a class="prev page-numbers" href="' . $this->getBackendUrl($link) . '">' . $args['prev_text'] . '</a>';
		endif;
		for ( $n = 1; $n <= $total; $n++ ) :
			if ( $n == $current ) :
				$page_links[] = "<span class='page-numbers current'>" . $args['before_page_number'] . $n . $args['after_page_number'] . "</span>";
				$dots = true;
			else :
				if ( $args['show_all'] || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) :
					$link = str_replace( '%_%', 1 == $n ? '' : $args['format'], $args['base'] );
					$link = str_replace( '%#%', $n, $link );
					if ( $add_args )
						$link = $this->add_query_arg( $add_args, $link );
					$link .= $args['add_fragment'];

					/** This filter is documented in wp-includes/general-template.php */
					$page_links[] = "<a class='page-numbers' href='" . $this->getBackendUrl($link) . "'>" . $args['before_page_number'] . $n . $args['after_page_number'] . "</a>";
					$dots = true;
				elseif ( $dots && ! $args['show_all'] ) :
					$page_links[] = '<span class="page-numbers dots">' . __( '&hellip;' ) . '</span>';
					$dots = false;
				endif;
			endif;
		endfor;
		if ( $args['prev_next'] && $current && ( $current < $total || -1 == $total ) ) :
			$link = str_replace( '%_%', $args['format'], $args['base'] );
			$link = str_replace( '%#%', $current + 1, $link );
			if ( $add_args )
				$link = $this->add_query_arg( $add_args, $link );
			$link .= $args['add_fragment'];

			/** This filter is documented in wp-includes/general-template.php */
			$page_links[] = '<a class="next page-numbers" href="' . $this->getBackendUrl($link) . '">' . $args['next_text'] . '</a>';
		endif;
		switch ( $args['type'] ) {
			case 'array' :
				return $page_links;

			case 'list' :
				$r .= "<ul class='page-numbers'>\n\t<li>";
				$r .= join("</li>\n\t<li>", $page_links);
				$r .= "</li>\n</ul>\n";
				break;

			default :
				$r = join("\n", $page_links);
				break;
		}
		return $r;
	}

	/**
	 *	Add arguments to url
	 *
	 *	@param	array	Arguments
	 *	@param	string	Url
	 *	@return	string	Link
	 */
	public function add_query_arg($args = array(), $link = '') {
		if ( is_array($args) ) {
			foreach ($args as $_key => $_val) {
				$link .= $_key . '/' . $_val . '/';
			}
		}
		return $link;
	}

	/**
	 *	Check if SSL in use
	 *
	 *	@return	boolean
	 */

	public function is_ssl() {
		return $this->_storeManager->getStore()->isCurrentlySecure();
	}

    /**
     *	Force ssl on urls
     *
     *	@param	string
     *	@return	string
     */

	public function forceSSL($url) {
        if ($this->is_ssl()) {
            $url = str_replace('http://', 'https://', $url);
        }
		return $url;
	}

    /**
     * Set url scheme for url
     *
     * @param string $url
     * @return string
     */
    public function set_url_scheme($url) {
        if ($this->is_ssl()) {
            $url = str_replace('http://', 'https://', $url);
        } else {
            $url = str_replace('https://', 'http://', $url);
        }
        return $url;
    }

	/**
	 *	Get upload dir
	 *
	 *	@return	array
	 */

	public function wp_upload_dir() {
        $store = $this->_storeManager->getStore();
		$upload_dir = array(
			'path'		=> $this->_directory->getAbsolutePath(),
			'url'		=> $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),
			'subdir'	=> '/',
			'basedir'	=> $this->_directory->getAbsolutePath() . self::WP_CONTENT_DIR,
			'baseurl'	=> $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . self::WP_CONTENT_DIR,
			'error'		=> FALSE
		);
		return $upload_dir;
	}

	/**
	 *	Get time
	 *
	 *	@return	int
	 */

	public function current_time() {
		return $this->_date->gmtTimestamp() + $this->_date->getGmtOffset();
	}

	/**
	 *	Snitize title
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function sanitize_title($title) {
		return $this->sanitize_text_field($title);
	}

	/**
	 *	Snitize title
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function sanitize_title_with_dashes($title) {
        $string = $this->sanitize_title($title);
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
	}

	/**
	 *	Snitize text field
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function sanitize_text_field($string) {
		if (is_string($string)) {
			$filtered = strip_tags($string);
			$found = false;
			while ( preg_match('/%[a-f0-9]{2}/i', $filtered, $match) ) {
				$filtered = str_replace($match[0], '', $filtered);
				$found = true;
			}
			if ( $found ) {
				$filtered = trim( preg_replace('/ +/', ' ', $filtered) );
			}
			return $filtered;
		} else {
			if (is_array($string)) {
                foreach($string as $_key => $_string) {
    				$string[$_key] = $this->sanitize_text_field($_string);
    			}
            }
			return $string;
		}
	}

	/**
	 *	Strip shortcodes
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function strip_shortcodes($string) {
		return $string;
	}

	/**
	 *	Escape attribute
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function esc_attr($text) {
		return htmlspecialchars($text);
	}

    public function esc_url($text) {
        return $text;
    }

    public function esc_html($text) {
        return strip_tags($text);
	}

	/**
	 * Escape JS
	 *
	 *	@param	string
	 *	@return	string
	 */
    public function esc_js($text) {
		$text = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", stripslashes($text));
		$text = str_replace( "\r", '', $text);
		$text = str_replace( "\n", '\\n', addslashes($text));
        return $text;
    }

    /**
	 *	Format sizes
	 *
	 *	@param	int	Bytes
	 *	@param	int	Decimals
	 *	@return	string
	 */

	public function size_format( $bytes, $decimals = 0 ) {
		$quant = array(
			// ========================= Origin ====
			'TB' => 1099511627776,  // pow( 1024, 4)
			'GB' => 1073741824,     // pow( 1024, 3)
			'MB' => 1048576,        // pow( 1024, 2)
			'kB' => 1024,           // pow( 1024, 1)
			'B ' => 1,              // pow( 1024, 0)
		);
		foreach ( $quant as $unit => $mag )
			if ( doubleval($bytes) >= $mag )
				return number_format( $bytes / $mag, $decimals ) . ' ' . $unit;

		return false;
	}



    /**
     *
     *  Filter functions
     *
     */




    /**
     *	Add filter
     *
     *	@param	string	$handle
     *	@param	array	$filter
     *	@param	int 	$priority
     *	@param	int 	$accepted_args
     */

	public function add_filter($handle, $filter, $priority = 10, $accepted_args = 1) {
        $data = array(
            'function'      => $filter,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );
        $this->_registerHelper->addToRegister('filters', $handle, $data);
	}

    /**
     *	Add filter
     *
     *	@param	string	$handle
     *	@param	mixed	$value
     *	@return	var
     */

	public function apply_filters($handle, $value) {

        $this->_currentFilter = $handle;

        $args = func_get_args();
        if (func_num_args()) {
            unset($args[0]);
        }

        $filtersToApply = $this->_registerHelper->getFromRegister('filters', $handle);
        usort($filtersToApply, function ($a, $b) {
            if ($a['priority'] == $b['priority']) {
                return 0;
            } else {
                return $a['priority'] < $b['priority'] ? -1 : 1;
            }
        });
        foreach ($filtersToApply as $filter) {

            $args = array_slice(func_get_args(), 1, $filter['accepted_args']);
            $args[ 0] = $value;

			if (is_array($filter['function']) && count($filter['function']) && is_string($filter['function'][0])) {
				$reflectionMethod = new \ReflectionMethod($filter['function'][0], $filter['function'][1]);
				if (!$reflectionMethod->isStatic()) {
					$reflectionClass = new \ReflectionClass($filter['function'][0]);
					$filter['function'][0] = $reflectionClass->newInstanceWithoutConstructor();
				}
			}

			$value = call_user_func_array($filter['function'], $args);
        }

        if ($this->_registerHelper->getFromRegister('actions', $handle)) {
            $value = call_user_func_array([$this, 'do_action'], func_get_args());
        }

        $this->_currentFilter = NULL;

		return $value;
	}

    /**
     *	Get current filter
     *
     * @return string
     */
	public function current_filter() {
        return $this->_currentFilter;
	}

    /**
     *	Register script
     *
     *	@param	string	$handle
     *	@param	string	$script
     */

	public function wp_register_script($handle, $script = false) {
        $this->_registerHelper->addToRegister('register_scripts', $handle, $script);
	}

    /**
     *	Add script
     *
     *	@param	string	$handle
     *	@param	string	$script
     */

	public function wp_enqueue_script($handle, $script = false) {
        $handles = is_array($handle) ? $handle : array($handle);
        foreach ($handles as $_handle) {
            foreach ($this->_registerHelper->getFromRegister('register_scripts', $_handle) as $regScript) {
                $this->_registerHelper->setToRegister('scripts', $_handle, $regScript);
            }
        }
		if ($script && is_string($handle)) {
			$this->_registerHelper->setToRegister('scripts', $handle, $script);
		}
	}

    /**
     *	Register style
     *
     *	@param	string	$handle
     *	@param	string	$style
     */

	public function wp_register_style($handle, $style = false) {
        $this->_registerHelper->addToRegister('register_styles', $handle, $style);
	}

	/**
	 *	Add style
	 *
	 *	@param	string	$handle
	 *	@param	string	$style
	 */

	public function wp_enqueue_style($handle, $style = false) {
        $handles = is_array($handle) ? $handle : array($handle);
        foreach ($handles as $_handle) {
            foreach ($this->_registerHelper->getFromRegister('register_styles', $_handle) as $regStyle) {
                $this->_registerHelper->setToRegister('styles', $_handle, $regStyle);
            }
        }
		if ($style && is_string($handle)) {
			$this->_registerHelper->setToRegister('styles', $handle, $style);
		}
	}

	/**
	 *	Add inline style
	 *
	 *	@param	string	$handle
	 *	@param	string	$style
	 */

	public function wp_add_inline_style($handle, $style = false) {
        $this->_registerHelper->addToRegister('inline_styles', $handle, $style);
	}

    /**
     *	Get localize styles
     *
     *	@param	string	$handle
     *	@param	string	$var
     *	@param	array	$lang
     */

	public function wp_localize_script($handle, $var, $lang = array()) {
        $data = array(
            'var'   => $var,
            'lang'  => $lang
        );
        $this->_registerHelper->setToRegister('localize_scripts', $handle, $data);
	}

	/**
	 *	Get post meta (for compatibility)
	 *
	 *	@param	int		$id
	 *	@param	string	$hanlde
	 *	@param	bool	$flag
	 *	@return	var
	 */

	public function get_post_meta($id, $handle, $flag = true) {
        $product = $this->_productsHelper->getProduct($id);
		return isset($product[$handle]) ? $product[$handle] : false;
	}

	/**
	 *	Get post types
	 *
	 *	@param	array	$args
	 *	@return	array
	 */

	public function get_post_types($args = array()) {
		return array();
	}

	/**
	 *	Get post type object
	 *
	 *	@param	string	$postType
	 *	@return	object
	 */

	public function get_post_type_object($postType) {
		switch ($postType) {
			case 'post' :
				$postArray = array(
					'labels' => array('singular_name' => 'Product')
				);
			break;
			default :
				$postArray = array();
			break;
		}
		return $this->_array_to_object($postArray);
	}

	/**
	 *	Get object taxonomies
	 *
	 *	@param	array	$args
	 *	@param	string	$type
	 *	@return	object
	 */

	public function get_object_taxonomies($args, $type = 'objects') {
		$taxonomies = array();
		switch ($args['post_type']) {
			case 'post' :
				$taxonomies = array(
					'category' => array(
						'name'		=> 'category',
						'labels'	=> array('name' => 'Categories')
					)
				);
			break;
		}
		return $this->_array_to_object($taxonomies);
	}

	/**
	 *	Get categories
	 *
	 *	@param	array	$args
	 *	@param	string	$type
	 *	@return	object
	 */

	public function get_categories($args, $type = 'objects') {
		$categories = $this->_productsHelper->getCategories();
		foreach ($categories as $key => $category) {
			$categories[$key] = $this->_array_to_object($category);
		}
		return $categories;
	}

	/**
	 *	Get category link
	 *
	 *	@param	int		$id
	 *	@return	string
	 */

	public function get_category_link($id) {
		$category = $this->_productsHelper->getCategory($id);
		return $category['url'];
	}


	/**
	 *	Get tag list for compatibility
	 *	@param	int		$id
	 *	@return	array
	 */

	public function get_the_tag_list($id) {
		return array();
	}

	/**
	 *	Get post image id (product image)
	 *	@param	int		$id
	 *	@return	int
	 */

	public function get_post_thumbnail_id($id) {
		return $id;
	}

	/**
	 *	Get post
	 *
	 *	@param	int		$id
	 *	@return	object
	 */

	public function get_post($id) {
		return $this->_array_to_object( $this->_productsHelper->getProduct($id) );
	}


	/**
	 *	Get post title
	 *
	 *	@param	int		$id
	 *	@return	string
	 */

	public function get_the_title($id) {
		$product = $this->_productsHelper->getProduct($id);
		return $product && isset($product['name']) ? $product['name'] : false;
	}

	/**
	 *	Get post link
	 *
	 *	@param	int		$id
	 *	@return	string
	 */

	public function get_permalink($id) {
		$product = $this->_productsHelper->getProduct($id);
		return $product && isset($product['url']) ? $product['url'] : false;
	}

	/**
	 *	Get post terms
	 *
	 *	@param	int		$id
	 *	@param	array	$args
	 *	@param	string	$type
	 *	@return	array
	 */

	public function wp_get_post_terms($id, $args) {
        return array();
	}

	/**
	 *	Check if in admin access now
	 *
	 *	@return	bool
	 */

	public function is_admin() {
		return $this->_areaCode == 'adminhtml';
	}

	public function is_super_admin() {
		return $this->is_admin();
	}

	public function is_admin_bar_showing() {
		return $this->is_admin();
	}

	public function is_user_logged_in() {
		return $this->is_admin();
	}

    /**
     * Check if the ajax request
     *
     * @return boolean
     */
    public function wp_doing_ajax() {
        return $this->_request->isXmlHttpRequest();
    }

    /**
     * Get current user
     *
     * @return object
     */
    public function wp_get_current_user() {
        $user = (object) array(
            'display_name' => $this->_authSession->getUser()->getUsername()
        );
        return $user;
    }

	/**
	 *	Check if current user have access
	 *
	 *	@return	bool
	 */

	public function current_user_can() {
		return $this->is_admin();
	}

	/**
	 *	Check for error in data
	 *
	 *	@param	var		$data
	 *	@return	bool
	 */

	public function is_wp_error($data) {
		return false;
	}


    /**
     *
     *  Cache functions
     *
     */



	/**
	 *	Get transient
	 *
	 *	@param	string	Handle
	 *	@return var
	 */

	public function get_transient($handle) {
		$data = $this->_cacheProxy->load(self::TRANSIENT_PREFIX . $handle);
		if ($data !== false) {
			$data = unserialize($data);
		}
		return $data;
	}

	/**
	 *	Set transient
	 *
	 *	@param	string	Handle
	 *	@param	var		Value
	 *	@param	int		Expiration (seconds)
	 */
	public function set_transient($handle, $value, $expiration = 0) {
        $tags = [];
        preg_match_all('/_slider_(\d+)_/m', $handle, $matches, PREG_SET_ORDER, 0);
        if ($matches) {
            $tags[] = self::SLIDER_TRANSIENT_TAG;
            $tags[] = self::SLIDER_TRANSIENT_TAG . $matches[0][1];
        }
		$this->_cacheProxy->save(serialize($value), self::TRANSIENT_PREFIX . $handle, $tags, $expiration);
	}

	/**
	 *	Delete transient
	 *
	 *	@param	string	Handle
	 */
	public function delete_transient($handle) {
		$this->_cacheProxy->remove(self::TRANSIENT_PREFIX . $handle);
	}

	/**
	 *	Delete all slider transients
	 */
	public function deleteAllSliderTransients() {
		$this->_cacheProxy->clean([self::SLIDER_TRANSIENT_TAG]);
	}

	/**
	 *	Delete slider transients
     *
     * @paramm int $sliderId
	 */
	public function deleteSliderTransients($sliderId) {
		$this->_cacheProxy->clean([self::SLIDER_TRANSIENT_TAG . $sliderId]);
	}

	public function delete_site_transient($handle) {
		$this->delete_transient($handle);
	}

	/**
	 *	Set cache
	 *
	 *	@param string $cacheKey
	 *	@param var $data
	 *	@param string $cacheGroup
	 */
	public function wp_cache_set($cacheKey, $data, $cacheGroup = '') {
        if (false && ! $this->is_admin()) {
            $tags = [self::CACHE_TAG];
            if ($cacheGroup) {
                $tags[] = $cacheGroup;
            }
            $this->_cacheProxy->save(serialize($data), self::CACHE_PREFIX . $cacheKey, $tags);
        }
	}

	/**
	 *	Get cache
	 *
	 * @param string $cacheKey
     * @param string $cacheGroup
     * @param bool $force
     * @param bool $found
	 * @return var
	 */
	public function wp_cache_get($cacheKey, $cacheGroup = '', $force = false, &$found = null) {
        if (false && $this->is_admin()) {
            $data = false;
            $found = false;
        } else {
            $data = $this->_cacheProxy->load(self::CACHE_PREFIX . $cacheKey);
            if ($data !== false) {
                $data = unserialize($data);
            }
            $found = $data !== false;
        }
        return $data;
	}

	/**
	 *	Delete cache
	 *
	 * @param string $cacheKey
     * @param string $cacheGroup
	 */
	public function wp_cache_delete($cacheKey, $cacheGroup = '') {
		$this->_cacheProxy->remove($cacheKey);
	}

	/**
	 * Flush cache
	 */
	public function wp_cache_flush() {
        $this->_cacheProxy->clean([self::CACHE_TAG]);
	}


    /**
     * Check if in RTL mode
     *
     * @return boolean
     */
    public function is_rtl() {
        // TODO: See if it is possible to set admin in RTL mode at all
        return false;
	}

    /**
     * Die!
     */
    public function wp_die() {
        die();
    }

	/**
	 *	Date localization
	 *
	 *	@param	string	$format
	 *	@param	int		$date
	 */

	public function date_i18n($format, $date) {
		$format = trim($format);
		$format = $format ? $format : 'd M, Y - H:i';
		return date($format, is_string($date) ? strtotime($date) : $date);
	}

	/**
	 *	Check if file is writable
	 *
	 *	@param	string	$path
	 *	@return	bool
	 */

	public function wp_is_writable($path) {
		return is_writable($path);
	}

	/**
	 * Converts byte value to integer byte value
	 *
	 * @param	string	$size
	 * @return	int
	 */

	public function wp_convert_hr_to_bytes($size) {
		$size  = strtolower( $size );
		$bytes = (int) $size;
		if ( strpos( $size, 'k' ) !== false ) $bytes = intval( $size ) * 1024;
		elseif ( strpos( $size, 'm' ) !== false ) $bytes = intval($size) * 1024 * 1024;
		elseif ( strpos( $size, 'g' ) !== false ) $bytes = intval( $size ) * 1024 * 1024 * 1024;
		return $bytes;
	}

	/**
	 *	Get current page info
	 *
	 *	@return	obj
	 */

	public function get_current_screen() {
		$screen = array('id' => 'revslider');
		return (object) $screen;
	}

    /**
     *	Get revslider admin url
     *
     *	@param	string	url
     *	@param	string	args
     *	@return	string
     */

    public function admin_url($url, $args = '') {
        return $this->getBackendUrl($url, $args);
    }

    /**
     *	Get home url
     *
     *	@return	string
     */

    public function home_url() {
        $store = $this->_storeManager->getStore();
        return $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }

    /**
	 *	Get revslider backend url
	 *
	 *	@param	string	url
	 *	@param	mixed	args
	 *	@return	string
	 */

	public function getBackendUrl($url, $args = '') {

        $backendBase = 'nwdthemes_revslider/revslider/';
        $backendUrl = str_replace('admin-ajax.php', $backendBase . 'addonajax', $url);
        if (strpos($backendUrl, 'nwdthemes_revslider') === false && strpos($backendUrl, 'adminhtml/') === false) {
            $backendUrl = $backendBase . $backendUrl;
        }

		if (is_array($args)) {
            $params = $args;
        } else {
            $params = array();
            $argsArray = explode('&', ltrim($args, '?'));
            foreach ($argsArray as $arg) {
                if (strpos($arg, '=') !== false) {
                    list($key, $value) = explode('=', $arg);
                    $params[$key] = $value;
                } elseif ($arg) {
                    $params[$arg] = '';
                }
            }
        }

		return $this->_backendUrl->getUrl($backendUrl, $params);
	}

    /**
	 *	Get Store Id
	 *
	 *	@return	int
	 */

	public function getStoreId() {
		return $this->_storeManager->getStore()->getId();
	}

    /**
	 *	Get Asset Url
	 *
	 *	@param  string  Handle
	 *	@param  array   Params
	 *	@param  boolean $noModule
	 *	@return	string
	 */
	public function getAssetUrl($handle = '', $params = array(), $noModule = false) {
        $_params = ['_secure' => $this->_request->isSecure()];
		$_params = array_merge($_params, $params);
		$_handle = strpos($handle, '::') !== false || $noModule ? $handle : self::MODULE . '::' . $handle;
        return $this->_assetRepo->getUrlWithParams($_handle, $_params);
	}

    /**
	 *	Get Asset Path
	 *
	 *	@param  string  Handle
	 *	@param  array   Params
	 *	@return	string
	 */

	public function getAssetPath($handle = '', $params = array()) {
        $_params = ['_secure' => $this->_request->isSecure()];
        $_params = array_merge($_params, $params);
        $_handle = self::MODULE . '::' . $handle;
        $asset = $this->_assetRepo->createAsset($_handle, $_params);

        $directory = $this->_filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        $path = $directory->getAbsolutePath($asset->getPath());

        return $path;
	}

    /**
	 *	Convert assets url for frontend
	 *
	 *	@param  string  Handle
	 *	@param  array   Params
	 *	@return	string
	 */

	public function convertAssetUrlForOutput($url) {
        if (strpos($url, self::MODULE) !== false) {
            list($urlBase, $urlFile) = explode(self::MODULE, $url);
            $assetUrl = $this->getAssetUrl(ltrim($urlFile, '/'));
        } else {
            $assetUrl = $this->forceSSL($url);
        }
        return $assetUrl;
	}

    /**
     * Check if assets minification enabled
     *
     * @param string $type
     * @return boolean
     */
    public function isMinified($type) {
        return $this->_minification->isEnabled($type);
    }

	/**
	 * Dump output
	 *
	 * @param var $str
	 */

	public function dmp($str) {
		echo "<div align='left'>";
        if (is_string($str) || (is_object($str) && get_class($str) == 'Magento\Framework\Phrase')) {
            echo $str;
        } else {
    		echo "<pre>";
    		print_r($str);
    		echo "</pre>";
        }
		echo "</div>";
	}

	/**
	 *	Convert array to object
	 *
	 *	params	array	$array
	 *	return object
	 */

	private function _array_to_object($array) {
		return $this->json_decode(json_encode($array), FALSE);
	}

	/**
	 *	Check if mobile browser
	 *
	 *	@return	boolean
	 */

	public function wp_is_mobile() {
		$useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$is_mobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
		return $is_mobile;
	}

    /**
     * Trigger error
     *
     * @param   string
     * @param   int
     */

	public function trigger_error($errorMessage, $errorLevel = 0) {
		if ($errorLevel) {
			throw new \Exception($errorMessage, $errorLevel);
		}else{
			throw new \Exception($errorMessage);
		}
    }



    /**
     *
     *  Curl wrapper functions
     *
     */



    /**
     *	Get content from remote url
     *
     *	@param	string	$url
     *	@param	array	$args
     *	@return	array
     */

    public function wp_remote_post($url, $args = array()) {
        $r = $this->_curlHelper->request($url, $args);
        return $r;
    }

    /**
     *	Get content from remote url
     *
     *	@param	string	$url
     *	@param	array	$args
     *	@return	array
     */

    public function wp_remote_get($url, $args = array()) {
        return $this->_curlHelper->requestGet($url, $args);
    }

    /**
     *	Open content from remote url
     *
     *	@param	string	$url
     *	@return	string
     */

    public function wp_remote_fopen($url) {
        return $this->_curlHelper->remoteFileOpen($url);
    }

    /**
     *  Get response code from response
     *
     *	@param	array	$response
     *	@return	string
     */

    public function wp_remote_retrieve_response_code($response) {
        return $this->_curlHelper->getCode($response);
    }

    /**
     *  Get body from response
     *
     *	@param	array	$response
     *	@return	string
     */

    public function wp_remote_retrieve_body($response) {
        return $this->_curlHelper->getBody($response);
    }

    /**
     * Redirect
     *
     * @param string $url
     */
    public function wp_redirect($url) {
        $this->_redirect
            ->setRedirect($url)
            ->sendResponse();
        die();
    }


    /**
     *
     *  Actions wrapper functions
     *
     */



    /**
     *	Add action
     *
     *	@param	string	$handle
     *	@param	array	$action
     */

    public function add_action($handle, $action) {
        $this->_registerHelper->addAction($handle, $action);
    }

    /**
     *	Remove action
     *
     *	@param	string	$handle
     *	@param	array	$action
     */

    function remove_action($handle, $action) {
        $this->_registerHelper->removeAction($handle, $action);
    }

    /**
     *	Do action
     *
     *	@param	string	$handle
     *	@param	mixed	$args
     *	@return string
     */

    public function do_action($handle) {
        $args = func_get_args();
        if (func_num_args()) {
            unset($args[0]);
        }
        return $this->_registerHelper->doAction($handle, $args);
    }

    /**
     * Check if action added
     *
     * @param	string	$handle
     * @param	array	$action
     * @return  boolean
     */
    public function has_action($handle, $action) {
        return $this->_registerHelper->hasAction($handle, $action);
    }



    /**
     *
     *  Options wrapper functions
     *
     */



    /**
     *	Get option
     *
     *	@param	string $handle Handle
     *	@param	string|boolean $default Default value
     *	@return	mixed
     */

    public function get_option($handle, $default = false) {
        return $this->_optionsHelper->getOption($handle, $default);
    }

    /**
     * Update option
     *
     * @param string $handle
     * @param string $value
     */

    public function update_option($handle, $value = '') {
		$this->_optionsHelper->updateOption($handle, $value);
		return true;
    }

    /**
     * Add option
     *
     * @param string $handle
     * @param string $option
     * @param string $deprecated
     * @param string $autoload
     * @return boolean
     */

    public function add_option($handle, $option = '', $deprecated = '', $autoload = 'yes') {
        $this->_optionsHelper->updateOption($handle, $option);
        return true;
    }

    /**
     * Delete option
     *
     * @param string $handle
     */

    public function delete_option($handle) {
        $this->_optionsHelper->deleteOption($handle);
    }



    /**
     *
     *  Plugin wrapper functions
     *
     */



    /**
     *	Checks if addon activated
     *
     *  @param  string  $plugin
     *	@return	boolean
     */

    public function is_plugin_active($plugin) {
        return $this->_pluginHelper->isPluginActive($plugin);
    }

    /**
     *	Checks if addon not activated
     *
     *  @param  string  $plugin
     *	@return	boolean
     */

    public function is_plugin_inactive($plugin) {
        return ! $this->_pluginHelper->isPluginActive($plugin);
    }

    /**
     *	Activate plugin
     *
     *  @param  string  $plugin
     *	@return	boolean
     */

    public function activate_plugin($plugin) {
        return $this->_pluginHelper->activatePlugin($plugin);
    }

    /**
     *	Deactivate plugin
     *
     *  @param  string  $plugin
     *	@return	boolean
     */

    public function deactivate_plugins($plugin) {
        return $this->_pluginHelper->deactivatePlugin($plugin);
    }

    /**
     *	Get plugins
     *
     *	return	boolean
     */

    public function get_plugins() {
        return $this->_pluginHelper->getPlugins();
    }

    /**
     *	Get plugins url
     *
     *  @param  string  $file
     *  @param  string  $plugin
     *	@return	string
     */

    public function plugins_url($file, $plugin) {
        return $this->_pluginHelper->getPluginUrl($file, $plugin);
    }

    /**
     *	Get plugin dir path
     *
     *  @param  string  $plugin
     *	@return	string
     */

    public function plugin_dir_path($plugin) {
        return Plugin::getPluginDir() . $this->_pluginHelper->getPluginName($plugin) . '/';
    }

	/**
	 *	Get plugin dir url
 	 *
 	 *  @param  string  $plugin
 	 *	@return	string
  	 */
	public function plugin_dir_url($plugin) {
		return $this->plugins_url('', $plugin);
	}


    /**
     *
     *  Filesystem wrapper functions
     *
     */


	/**
	 *	Make writable directory
	 *
	 *	@param	string	$path
	 *	@return	bool
	 */
	public function wp_mkdir_p($dir) {
		return $this->_filesystemHelper->wp_mkdir_p($dir);
	}



    /**
     *
     *  Dummy wrapper functions
     *
     */



    public function _get_list_table() {}
    public function register_activation_hook() {}
    public function register_deactivation_hook() {}
    public function load_plugin_textdomain() {}

    public function get_intermediate_image_sizes() {
        return array();
    }

    public function wp_generate_attachment_metadata() {
        return false;
    }

    public function wp_update_attachment_metadata() {
        return false;
    }

    public function wp_get_attachment_metadata($image) {
        return $this->_imagesHelper->wp_get_attachment_metadata($image);
	}

	/**
	 *	Get image url by id and size
	 *
	 *	@param	int		Image id
	 *	@param	string	Size type
	 *	@return string
	 */
	public function wp_get_attachment_image_src($attachment_id, $size='thumbnail') {
		return $this->_imagesHelper->wp_get_attachment_image_src($attachment_id, $size);
	}

    public function wp_get_attachment_url() {
        return false;
	}

    public function wp_insert_attachment() {
        return false;
	}

	public function wp_image_editor_supports() {
		return true;
	}

	public function language_attributes() {}
    public function bloginfo() {}
    public function wp_head() {}
	public function body_class() {}

    public function wp_footer() {
		$this->do_action('wp_footer');
        $this->do_action('wp_print_footer_scripts');
	}

    /**
     *
     *  Own framwork functions
     *
     */



    /**
     * Get registered styles
     *
     * @return array
     */
    public function getRegisteredStyles() {
        $styles = $this->_registerHelper->getFromRegister('styles');
        return $styles ? $styles : [];
    }

    /**
     * Get registered localized scripts
     *
     * @return array
     */
    public function getLocalizeScripts() {
        $scripts = $this->_registerHelper->getFromRegister('localize_scripts');
        return $scripts ? $scripts : [];
    }

    /**
     * Get registered localized scripts code for html
     *
     * @return string
     */
    public function getLocalizeScriptsHtml() {
        $html = '';
        $localizeScripts = $this->getLocalizeScripts();
        if ($localizeScripts) {
            $html .= '<!-- REVOLUTION LOCALIZE SCRIPTS -->' . "\n";
            $html .= '<script type="text/javascript">' . "\n";
            foreach ($localizeScripts as $localizeScript) {
                $html .= 'define(\'' . $localizeScript['var'] . '\', [], function() {' . "\n";
                $html .=      $localizeScript['var'] . ' = ' . json_encode($localizeScript['lang']) . ';' . "\n";
                $html .= '    return ' . $localizeScript['var'] . ';' . "\n";
                $html .= '});' . "\n";
            }
            $html .= '</script>' . "\n";
        }
        return $html;
	}

    /**
     * Get registered localized scripts
     *
     * @return array
     */
    public function getInlineStylesHTML() {
        $html = '';
        if ($styles = $this->_registerHelper->getFromRegister('inline_styles')) {
            $html .= '<!-- REVOLUTION CUSTOM CSS -->' . "\n";
            $html .= '<style type="text/css">' . "\n";
            foreach ($styles as $style) {
                $html .= implode("\n", $style) . "\n";
            }
            $html .= '</style>' . "\n";
        }
        return $html;
    }

    /**
     * Add required scripts
     *
     * @param string $key
     * @param string $url
     */
    public function addRequiredScript($key, $url)
    {
        $this->_scriptsToRequire[$key] = $url;
    }

    /**
     * Get required scripts
     *
     * @return array
     */
    public function getRequiredScripts()
    {
        return array_values($this->_scriptsToRequire);
    }

    /**
     *	Require script
     *
     *	@param	string	$handle
     *	@param	string	$script
     */
	public function requireScript($handle, $script) {
		$path = pathinfo($script);
		if (pathinfo($path['filename'], PATHINFO_EXTENSION) != 'min') {
			$dir = str_replace(rtrim($this->_pluginHelper->getPluginUrl('', ''), '/'), rtrim($this->_pluginHelper->getPluginDir()), pathinfo($script, PATHINFO_DIRNAME));
			$scriptFile = $dir . DIRECTORY_SEPARATOR . $path['filename'] . '.js';
			$minScriptFile = $dir . DIRECTORY_SEPARATOR . $path['filename'] . '.min.js';
			if ( ! file_exists($minScriptFile) && file_exists($scriptFile)) {
				try {
					copy($scriptFile, $minScriptFile);
				} catch (Exception $e) {}
			}
		}
        $this->addRequiredScript($handle, $script);
	}

	/**
	 * Get include code for required scripts
	 *
	 * @return string
	 */
	public function includeRequiredScripts() {
        $html = '';
		if ($scripts = $this->getRequiredScripts()) {
			$_scripts = [''];
			foreach ($scripts as $script) {
				$_scripts[] = '\'' . $script . '\'';
			}
			$html = implode(', ', $_scripts);
		}
		return $html;
	}

    /**
     * Include source file
     *
     * @param string $file File name
     */
    public function includeFile($file) {
        if (file_exists($file)) {
            include_once $file;
        }
    }

    /**
     * Create CMS page
     *
     * @param string $id
     * @param string $title
     * @param string $content
     * @return int
     */
    public function createPage($id, $title, $content) {
        $page = $this->_pageFactory->create();
        $page->setTitle($title)
            ->setIdentifier($id)
            ->setIsActive(true)
            ->setPageLayout('1column')
            ->setStores(array(0))
            ->setContent($content)
			->save();
		return $page->getId();
	}

	/**
	 * Get CMS page url
	 *
	 * @param int $id
	 * @return string
	 */
	public function getPageUrl($id) {
		$page = $this->_pageFactory->create()->load($id);
		$identifier = $page->getIdentifier();
		return $this->_storeManager->getStore()->getBaseUrl() . $identifier;
	}

	/**
	 * Get CMS edit page url
	 *
	 * @param int $id
	 * @return string
	 */
	public function getEditPageUrl($id) {
		return $this->getBackendUrl('cms/page/edit/page_id/' . $id);
	}

    /**
     * Get current product Id
     * get most recent product viewed if there is no current one
     *
     * @return int
     */

    public function getCurrentProductId() {
        return $this->_productsHelper->getCurrentProductId();
    }

    /**
     * Get template file path
     *
     * @param string $template
     * @return string
     */

    public function getTemplatePath($template) {
        $path = $this->_templateFileResolver->getTemplateFileName($template);
        return $path;
	}

	/**
	 * Get cUrl helper
	 *
	 * @return \Nwdthemes\Revslider\Helper\Curl
	 */
	public function getCurlHelper() {
		return $this->_curlHelper;
	}

	/**
	 * Get file system helper
	 *
	 * @return \Nwdthemes\Revslider\Helper\Filesystem
	 */
	public function getFilesystemHelper() {
		return $this->_filesystemHelper;
	}

	/**
	 * Get images helper
	 *
	 * @return \Nwdthemes\Revslider\Helper\Images
	 */
	public function getImagesHelper() {
		return $this->_imagesHelper;
	}

	/**
	 * Get product helper
	 *
	 * @return \Nwdthemes\Revslider\Helper\Product
	 */
	public function getProductsHelper() {
		return $this->_productsHelper;
	}

	/**
	 * Get db query helper
	 *
	 * @return \Nwdthemes\Revslider\Helper\Query
	 */
	public function getQueryHelper() {
		return $this->_queryHelper;
	}

	/**
	 * Generate preview HTML
	 *
	 * @param array $data
	 * @return string
	 */
	public function generatePreview($data) {

        $data['head'] = $this->_layoutFactory->create()
            ->createBlock('Nwdthemes\Revslider\Block\Head')
            ->setTemplate('Nwdthemes_Revslider::head.phtml')
            ->toHtml();

        $data['require'] = $this->_layoutFactory->create()
            ->createBlock('Magento\RequireJs\Block\Html\Head\Config')
            ->toHtml();

        $data['footer'] = $this->_layoutFactory->create()
            ->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('Nwdthemes_Revslider::init.phtml')
            ->toHtml();

		$content = $this->_layoutFactory->create()
			->createBlock('Nwdthemes\Revslider\Block\Adminhtml\Block\Template')
			->setTemplate('Nwdthemes_Revslider::public/revslider-page-template.phtml')
			->assign($data)
			->toHtml();

		$result = base64_encode($content);

		return $result;
	}

    /**
     * Include enqueued assets
     *
     * @return string
     */
    public function includeEnqueuedAssets() {
        $output = '';
        foreach ($this->_registerHelper->getFromRegister('styles') as $_handle => $_style) {
            $output .= '<link  rel="stylesheet" type="text/css"  media="all" href="' . $_style . '" />' . "\n";
        }
        foreach ($this->_registerHelper->getFromRegister('scripts') as $_handle => $_script) {
            $output .= '<script type="text/javascript" src="' . $_script . '"></script>' . "\n";
        }
        return $output;
	}

	/**
	 * Get customer groups
	 *
	 * @return array
	 */
	public function getCustomerGroups() {
        $customerGroups = array();
        foreach($this->_customerGroupCollection->toOptionArray() as $group) {
            $customerGroups[] = $group;
		}
		return $customerGroups;
	}

	/**
	 * Save slider tables data to file system
	 */
	public function backupDB() {
		$tables = [
			'nwdthemes_revslider_animations',
			'nwdthemes_revslider_backup',
			'nwdthemes_revslider_css',
			'nwdthemes_revslider_navigations',
			'nwdthemes_revslider_slides',
			'nwdthemes_revslider_sliders',
			'nwdthemes_revslider_static_slides'
		];
		$data = [];
		foreach ($tables as $table) {
			$data[$table] = $this->_queryHelper->get_results("SELECT * FROM $table");
		}
		$directory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
		$backupPath = $directory->getAbsolutePath() . 'revslider_backup/';
		if ( ! is_dir($backupPath)) {
			mkdir($backupPath);
		}
		file_put_contents($backupPath . date('Y-m-d_h-i-s') . '.json', json_encode($data));
	}

	/**
	 * Re-generate rs plugin path as it was incorrect if helper initialized in controller
	 */
	public function reGeneratePluginPath() {
        self::$RS_PLUGIN_PATH = $this->getAssetPath() . DIRECTORY_SEPARATOR;
		self::$RS_PLUGIN_URL = $this->getAssetUrl() . '/';
	}

	/**
	 * Save slider data filter
	 *
	 * @param array $data
	 * @return array
	 */
	public function sliderSaveDataFilter($data) {
		$data['params'] = json_encode($this->_makeImagesRelative($this->json_decode($data['params'], true)));
		return $data;
	}

	/**
	 * Save slide params filter
	 *
	 * @param array $params
	 * @param array $staticSlide
	 * @param \Nwdthemes\Revslider\Model\Revslider\RevSliderSlide $slide
	 * @return array
	 */
	public function slideSaveParamsFilter($params, $staticSlide, $slide) {
		$params = $this->_makeImagesRelative($params);
		return $params;
	}

	/**
	 * Save slide layers filter
	 *
	 * @param array $layers
	 * @param array $staticSlide
	 * @param \Nwdthemes\Revslider\Model\Revslider\RevSliderSlide $slide
	 * @return array
	 */
	public function slideSaveLayersFilter($layers, $staticSlide, $slide) {
		$layers = $this->_makeImagesRelative($layers);
		return $layers;
	}

	/**
	 * Init slider filter
	 *
	 * @param array $data
	 * @return array
	 */
	public function sliderInitByDataFilter($data) {
		if ( ! empty($data['params'])) {
			$data['params'] = json_encode($this->_makeImagesAbsolute($this->json_decode($data['params'], true)));
		}
		return $data;
	}

	/**
	 * Init slide filter
	 *
	 * @param array $data
	 * @return array
	 */
	public function slideInitByDataFilter($data) {
		if ( ! empty($data['params'])) {
			$data['params'] = json_encode($this->_makeImagesAbsolute($this->json_decode($data['params'], true)));
		}
		if ( ! empty($data['layers'])) {
			$data['layers'] = json_encode($this->_makeImagesAbsolute($this->json_decode($data['layers'], true)));
		}
		return $data;
	}

	/**
	 * Make image urls relative
	 *
	 * @param array $data
	 * @return array
	 */
	protected function _makeImagesRelative($data) {
		if (is_array($data)) {
			foreach ($data as $key => $item) {
				$data[$key] = $this->_makeImagesRelative($item);
			}
		} elseif (is_string($data)) {
            if ($this->getImagesHelper()->isMedia($data) && $data !== $this->getImagesHelper()->imageFile($data)) {
                $data = Images::MEDIA_URL_PACEHOLDER . $this->getImagesHelper()->imageFile($data);
            } elseif (strpos($data, self::SVG_PATH) !== false) {
                $data = self::SVG_URL_PACEHOLDER . strstr($data, self::SVG_PATH);
            }
        }
		return $data;
	}

	/**
	 * Make image urls absolute
	 *
	 * @param array $data
	 * @return array
	 */
	protected function _makeImagesAbsolute($data) {
		if (is_array($data)) {
			foreach ($data as $key => $item) {
				$data[$key] = $this->_makeImagesAbsolute($item);
			}
		} elseif (is_string($data)) {
            if (strpos($data, Images::MEDIA_URL_PACEHOLDER) === 0) {
                if ($this->getImagesHelper()->isMedia($data)) {
                    $data = $this->getImagesHelper()->imageUrl(str_replace(Images::MEDIA_URL_PACEHOLDER, '', $data));
                } else {
                    $data = str_replace(Images::MEDIA_URL_PACEHOLDER, $this->_storeManager->getStore()->getBaseUrl(), $data);
                }
            } elseif (strpos($data, self::SVG_PATH) !== false) {
                if (strpos($data, self::SVG_URL_PACEHOLDER) === 0) {
                    $data = $this->getAssetUrl(str_replace(self::SVG_URL_PACEHOLDER, '', $data));
                } else {
                    $data = $this->getAssetUrl(strstr($data, self::SVG_PATH));
                }
            }
        }
		return $data;
	}

	/**
	 * Get config value
	 *
	 * @param string $path
	 * @return string
	 */
	public function getConfigValue($path) {
		return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

    /**
     * Get Overview Data
     *
     * @return array
     */
    public function getOverviewData() {
        if ($this->_overviewData == null) {
            $this->_overviewData = RevSliderGlobals::instance()->get('RevSliderFunctionsAdmin')->get_slider_overview();
        }
        return $this->_overviewData;
    }

    /**
     * JSON Decode wrapper
     *
     * @param mixed $json,
     * @param ?bool $associative = null,
     * @param int $depth = 512,
     * @param int $flags = 0
     * @return mixed
     */
    public function json_decode(
        $json,
        ?bool $associative = null,
        int $depth = 512,
        int $flags = 0
    ) {
        if (is_null($json)) {
            $json = false;
        }
        return json_decode($json, $associative, $depth, $flags);
    }

}
