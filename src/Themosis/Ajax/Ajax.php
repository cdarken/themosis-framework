<?php
namespace Themosis\Ajax;

use Themosis\Configuration\Application;
use Themosis\Action\Action;

defined('DS') or die('No direct script access.');

class Ajax
{
	/**
	 * JS namespace
	*/
	private static $namespace;

	/**
	 * Ajax url js property
	*/
	private static $url;

    /**
     * The Ajax constructor.
     *
     */
	public function __construct()
	{
		static::$namespace = (Application::get('namespace')) ? Application::get('namespace') : 'themosis';

		if (Application::get('rewrite')) {

			static::$url = (Application::get('ajaxurl')) ? home_url().'/ajax/'.Application::get('ajaxurl').'php' : '';

		} else {

			static::$url = (Application::get('ajaxurl')) ? admin_url().Application::get('ajaxurl').'php' : '';

		}

		Action::listen('wp_head', $this, 'install')->dispatch();
	}

    /**
     * Handle the Ajax response. Run the appropriate
     * action hooks used by WordPress in order to perform
     * POST ajax request securely.
     * Developers have the option to run ajax for the
     * Front-end, Back-end either users are logged in or not
     * or both.
     *
     * @param string $action Your ajax 'action' name
     * @param string $logged Accepted values are 'no', 'yes', 'both'
     * @param callable $closure The function to run when ajax action is called
     * @throws AjaxException
     */
	public static function run($action, $logged, callable $closure)
	{
		if (is_string($action) && is_callable($closure)) {

			// Front-end ajax for non-logged users
			// Set $logged to FALSE
			if ($logged === 'no') {
				add_action('wp_ajax_nopriv_'.$action, $closure);
			}

			// Front-end and back-end for logged users
			if ($logged === 'yes') {
				add_action('wp_ajax_'.$action, $closure);
			}

			// Front-end and back-end for both logged in or out users
			if ($logged === 'both') {
				add_action('wp_ajax_nopriv_'.$action, $closure);
				add_action('wp_ajax_'.$action, $closure);
			}

		} else {
			throw new AjaxException("Invalid parameters for the Ajax::run method.");
		}
	}

    /**
     * Set the global ajax variable
     *
     * @return \Themosis\Ajax\Ajax
     * @ignore
     */
	public static function set()
	{
		return new static();
	}

	/**
	 * Install the Ajax global variable in the <head> tag.
     *
     * @return void
     * @ignore
	 */
	public static function install()
	{	
		$datas = apply_filters('themosisGlobalObject', array());

		?>
		<script type='text/javascript'>
  
  			//<![CDATA[
			var <?php echo(static::$namespace); ?> = {
				ajaxurl: '<?php echo(static::$url); ?>',
				<?php
					if (!empty($datas)) {
						foreach ($datas as $key => $value) {
							echo $key.": ".json_encode($value).",";
						}
					}
				?>
			};
			//]]>

		</script>
		<?php
	}
}

?>