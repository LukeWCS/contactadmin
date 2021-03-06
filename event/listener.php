<?php
/**
*
* Contact admin extension for the phpBB Forum Software package.
*
* @copyright 2016 Rich McGirr (RMcGirr83)
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/
namespace rmcgirr83\contactadmin\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpbb root path */
	protected $phpbb_root_path;

	/** @var string phpbb root path */
	protected $php_ext;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$phpbb_root_path,
		$php_ext
	)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'	=> 'page_header_after',
			'core.user_setup'			=> 'user_setup',
			'core.login_box_failed'		=> 'login_box_failed',
		);
	}

	public function user_setup($event)
	{
		$url = $this->helper->get_current_url();
		if ($this->config['contactadmin_enable'] && empty($this->user->data['is_bot']) && $this->config['board_disable'] && substr($url, strrpos($url, '/') + 1) === 'contactadmin')
		{
			define('SKIP_CHECK_DISABLED', true);
		}
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'rmcgirr83/contactadmin',
			'lang_set' => 'contact',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function page_header_after($event)
	{
		if ($this->config['contactadmin_enable'] && !$this->user->data['is_bot'])
		{
			$version = phpbb_version_compare($this->config['version'], '3.2.0-b2', '>=');

			$this->template->assign_vars(array(
				'U_CONTACT_US'					=> false,
				'U_CONTACTADMIN'				=> $this->helper->route('rmcgirr83_contactadmin_displayform'),
				'S_FORUM_VERSION'				=> $version,
				'CONTACT_PRIVACYPOLICY_EXPLAIN'	=> sprintf($this->user->lang['CONTACT_PRIVACYPOLICY_EXPLAIN'], append_sid("{$this->phpbb_root_path}ucp.$this->php_ext", 'mode=privacy')),
			));
		}
	}

	public function login_box_failed($event)
	{
		$error = $event['err'];
		$result = $event['result'];
		if ($this->config['contactadmin_enable'] && ($result['error_msg'] == 'LOGIN_ERROR_USERNAME' || $result['error_msg'] == 'LOGIN_ERROR_PASSWORD'))
		{
			$error = sprintf($this->user->lang[$result['error_msg']], '<a href="' . $this->helper->route('rmcgirr83_contactadmin_displayform') . '">', '</a>');
		}
		$event['err'] = $error;
	}
}
