<?php
/**
 * The Setup Wizard - configure the SEO settings in a few steps.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\CMB2;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Traits\Wizard;
use RankMath\Admin\Importers\Detector;

defined( 'ABSPATH' ) || exit;

/**
 * Setup_Wizard class.
 */
class Setup_Wizard {

	use Hooker, Wizard;

	/**
	 * Hold steps data.
	 *
	 * @var array
	 */
	protected $steps = [];

	/**
	 * Hold current step.
	 *
	 * @var string
	 */
	protected $step = '';

	/**
	 * Current step slug.
	 *
	 * @var string
	 */
	protected $step_slug = '';

	/**
	 * Top level admin page.
	 *
	 * @var string
	 */
	protected $slug = 'rank-math-wizard';

	/**
	 * CMB2 object
	 *
	 * @var \CMB2
	 */
	public $cmb = null;

	/**
	 * Wizard Step instance.
	 *
	 * @var Wizard_Step
	 */
	public $wizard_step = null;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'cmb2_admin_init', 'steps', 9 );
		$this->action( 'cmb2_admin_init', 'register_cmb2' );
		$this->action( 'admin_menu', 'add_admin_menu' );
		$this->action( 'admin_post_rank_math_save_wizard', 'save_wizard' );

		// If not the page is not this page stop here.
		if ( ! $this->is_current_page() ) {
			return;
		}

		$this->action( 'admin_init', 'admin_page', 30 );
		$this->filter( 'user_has_cap', 'filter_user_has_cap' );
		$this->filter( 'rank_math/wizard/step/label', 'change_label' );
		$this->filter( 'rank_math/wizard/step/label_url', 'change_label_url' );
	}

	/**
	 * Setup steps.
	 */
	public function steps() {
		$this->steps = [
			'compatibility' => [
				'slug'  => 'requirements',
				'name'  => esc_html__( 'Requirements', 'rank-math' ),
				'class' => '\\RankMath\\Wizard\\Compatibility',
			],
			'import'        => [
				'name'  => esc_html__( 'Import', 'rank-math' ),
				'class' => '\\RankMath\\Wizard\\Import',
			],
			'yoursite'      => [
				'name'  => esc_html__( 'Your Site', 'rank-math' ),
				'class' => '\\RankMath\\Wizard\\Your_Site',
			],

			'searchconsole' => [
				'name'  => esc_html__( 'Search Console', 'rank-math' ),
				'class' => '\\RankMath\\Wizard\\Search_Console',
			],

			'sitemaps'      => [
				'name'  => esc_html__( 'Sitemaps', 'rank-math' ),
				'class' => '\\RankMath\\Wizard\\Sitemap',
			],

			'optimization'  => [
				'name'  => esc_html__( 'Optimization', 'rank-math' ),
				'class' => '\\RankMath\\Wizard\\Optimization',
			],

			'ready'         => [
				'name'  => esc_html__( 'Ready', 'rank-math' ),
				'class' => '\\RankMath\\Wizard\\Ready',
			],

			'role'          => [
				'slug'  => 'rolemanager',
				'name'  => esc_html__( 'Role Manager', 'rank-math' ),
				'class' => '\\RankMath\\Wizard\\Role',
			],

			'redirection'   => [
				'slug'  => '404redirection',
				'name'  => esc_html__( '404 + Redirection', 'rank-math' ),
				'class' => '\\RankMath\\Wizard\\Monitor_Redirection',
			],

			'misc'          => [
				'name'  => esc_html__( 'Misc', 'rank-math' ),
				'class' => '\\RankMath\\Wizard\\Misc',
			],
		];

		$this->set_current_step();
	}

	/**
	 * Register CMB2 option page for setup wizard.
	 */
	public function register_cmb2() {
		$this->cmb = new_cmb2_box( array(
			'id'           => 'rank-math-wizard',
			'object_types' => array( 'options-page' ),
			'option_key'   => 'rank-math-wizard',
			'hookup'       => false,
			'save_fields'  => false,
			'classes'      => 'wp-core-ui rank-math-ui',
		) );

		$this->wizard_step->form( $this );
		CMB2::pre_init( $this->cmb );
	}

	/**
	 * Change label.
	 *
	 * @param string $label Label.
	 *
	 * @return string
	 */
	public function change_label( $label ) {
		if ( $this->is_advance() ) {
			return esc_html__( 'Advance Options', 'rank-math' );
		}

		return $label;
	}

	/**
	 * Change label url.
	 *
	 * @param string $url Label Url.
	 *
	 * @return string
	 */
	public function change_label_url( $url ) {

		if ( $this->is_advance() ) {
			return Helper::get_admin_url( 'wizard', 'step=ready' );
		}

		return $url;
		return rank_math()->admin_dir() . "wizard/views/{$view}.php";
	}

	/**
	 * Execute save handler for current step.
	 */
	public function save_wizard() {

		// If no form submission, bail.
		if ( empty( $_POST ) ) {
			return wp_safe_redirect( $_POST['_wp_http_referer'] );
		}

		check_admin_referer( 'rank-math-wizard', 'security' );

		$values       = $this->cmb->get_sanitized_values( $_POST );
		$show_content = $this->wizard_step->save( $values, $this );

		$redirect = $show_content ? $this->step_next_link() : $_POST['_wp_http_referer'];
		if ( is_string( $show_content ) ) {
			$redirect = $show_content;
		}
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Add the admin menu item, under Appearance.
	 */
	public function add_admin_menu() {
		if ( empty( $_GET['page'] ) || $this->slug !== $_GET['page'] ) {
			return;
		}

		$this->hook_suffix = add_submenu_page(
			null, esc_html__( 'Setup Wizard', 'rank-math' ), esc_html__( 'Setup Wizard', 'rank-math' ), 'manage_options', $this->slug, array( $this, 'admin_page' )
		);
	}

	/**
	 * Add the admin page.
	 */
	public function admin_page() {

		// Do not proceed, if we're not on the right page.
		if ( empty( $_GET['page'] ) || $this->slug !== $_GET['page'] ) {
			return;
		}

		if ( ob_get_length() ) {
			ob_end_clean();
		}

		// Enqueue styles.
		\CMB2_hookup::enqueue_cmb_css();
		\CMB2_hookup::enqueue_cmb_js();
		rank_math()->admin_assets->register();
		wp_enqueue_style( 'rank-math-wizard', rank_math()->plugin_url() . 'assets/admin/css/setup-wizard.css', array( 'wp-admin', 'buttons', 'cmb2-styles', 'select2-rm', 'rank-math-common', 'rank-math-cmb2' ), rank_math()->version );

		// Enqueue javascript.
		wp_enqueue_script( 'rank-math-wizard', rank_math()->plugin_url() . 'assets/admin/js/wizard.js', array( 'media-editor', 'select2-rm', 'rank-math-common' ), rank_math()->version, true );

		Helper::add_json( 'currentStep', $this->step );
		Helper::add_json( 'deactivated', esc_html__( 'Deactivated', 'rank-math' ) );
		Helper::add_json( 'confirm', esc_html__( 'Are you sure you want to import settings into Rank Math? Don\'t worry, your current configuration will be saved as a backup.', 'rank-math' ) );
		Helper::add_json( 'isConfigured', Helper::is_configured() );

		ob_start();

		/**
		 * Start the actual page content.
		 */
		include_once $this->get_view( 'header' );
		include_once $this->get_view( 'content' );
		include_once $this->get_view( 'footer' );
		exit;
	}

	/**
	 * Is navigation item hidden or not.
	 *
	 * @param string $slug Slug of nav item.
	 *
	 * @return bool
	 */
	public function is_nav_item_hidden( $slug ) {
		if ( 'compatibility' === $slug ) {
			return true;
		}

		$is_advanced   = $this->is_advance();
		$advance_steps = array( 'role', 'redirection', 'misc' );

		return in_array( $slug, $advance_steps ) ? ! $is_advanced : $is_advanced;
	}

	/**
	 * Get view file to display.
	 *
	 * @param string $view View to display.
	 * @return string
	 */
	public function get_view( $view ) {
		return rank_math()->admin_dir() . "wizard/views/{$view}.php";
	}

	/**
	 * Get the step URL.
	 *
	 * @param string $step Name of the step, appended to the URL.
	 */
	public function get_step_link( $step ) {
		return add_query_arg( 'step', $step );
	}

	/**
	 * Get Skip Link.
	 */
	public function get_skip_link() {
		?>
		<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="button button-secondary button-skip"><?php esc_html_e( 'Skip step', 'rank-math' ); ?></a>
		<?php
	}

	/**
	 * Set current step.
	 */
	private function set_current_step() {
		if ( $this->maybe_remove_import() ) {
			unset( $this->steps['import'] );
		}

		if ( ! Helper::is_module_active( 'role-manager' ) ) {
			unset( $this->steps['role'] );
		}

		$this->steps       = $this->do_filter( 'wizard/steps', $this->steps );
		$this->step        = isset( $_REQUEST['step'] ) && ! empty( $_REQUEST['step'] ) ? sanitize_key( $_REQUEST['step'] ) : current( array_keys( $this->steps ) );
		$this->step_slug   = isset( $this->steps[ $this->step ]['slug'] ) ? $this->steps[ $this->step ]['slug'] : $this->step;
		$this->wizard_step = new $this->steps[ $this->step ]['class'];
	}

	/**
	 * Checks if current step is advanced.
	 *
	 * @return bool
	 */
	private function is_advance() {
		return isset( $_REQUEST['step'] ) && in_array( $_REQUEST['step'], array( 'role', 'redirection', 'misc' ) );
	}

	/**
	 * Maybe remove import step.
	 *
	 * @return bool
	 */
	private function maybe_remove_import() {
		if ( false === get_option( 'rank_math_is_configured' ) ) {
			$detector = new Detector;
			$plugins  = $detector->detect();
			if ( ! empty( $plugins ) ) {
				return false;
			}
		}

		return true;
	}
}
