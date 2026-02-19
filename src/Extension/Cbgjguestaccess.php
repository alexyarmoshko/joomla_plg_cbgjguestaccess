<?php
/**
 * CB GroupJive guest access gate — restricts guest access to GroupJive pages.
 *
 * @package     YakShaver.Plugin.System.Cbgjguestaccess
 * @subpackage  Extension
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Plugin\System\Cbgjguestaccess\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;

/**
 * Restricts guest access to CB GroupJive pages.
 *
 * Layer 1 of a two-plugin design: this plugin gates routes via HTTP redirect.
 * Layer 2 (plg_system_cbgjvisibility, deployed separately) scrubs group-identifying
 * HTML from the rendered event page. Both must be enabled.
 *
 * Ordering must be higher than plg_system_communitybuilder so that CB's own
 * auth-layer redirects (MFA, consent, com_users rerouting) run first.
 */
final class Cbgjguestaccess extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the plugin language file automatically so Text::_() keys resolve
     * at runtime, not just in admin/install contexts (.sys.ini).
     */
    protected $autoloadLanguage = true;

    /**
     * Allowlist of CB GroupJive plugin elements that guests may access.
     *
     * All other cbgroupjive* plugin elements are redirected to the CB login page.
     * New sub-plugins are blocked by default — add here only after deliberately
     * deciding guests should have access.
     */
    private const GUEST_ALLOWED_PLUGINS = ['cbgroupjiveevents'];

    public static function getSubscribedEvents(): array
    {
        return ['onAfterRoute' => 'onAfterRoute'];
    }

    /**
     * Redirect guests away from GroupJive pages that are not in the allowlist.
     *
     * Runs after Joomla has resolved URL parameters but before CB renders output.
     * Only intercepts com_comprofiler pluginclass requests for cbgroupjive* plugins.
     * Non-GroupJive CB plugins (cbactivity, cbprivacy, etc.) pass through untouched.
     */
    public function onAfterRoute(): void
    {
        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            return;
        }

        if (!$app->getIdentity()->guest) {
            return;
        }

        $input = $app->getInput();

        if ($input->get('option') !== 'com_comprofiler') {
            return;
        }

        // Joomla uses task and view interchangeably (communitybuilder.php:84).
        // Both can carry dotted values like 'pluginclass.someAction', so we
        // split on '.' and take the first segment.  Check both parameters
        // independently — if task is present but not 'pluginclass' while
        // view IS 'pluginclass', the guard must still engage.
        $taskSegment = explode('.', $input->get('task', ''), 2)[0];
        $viewSegment = explode('.', $input->get('view', ''), 2)[0];

        if ($taskSegment !== 'pluginclass' && $viewSegment !== 'pluginclass') {
            return;
        }

        $plugin = $input->get('plugin', '', 'cmd');

        // Only gate GroupJive plugin elements — let other CB pluginclass
        // requests (cbactivity, cbprivacy, etc.) pass through.
        if (!str_starts_with($plugin, 'cbgroupjive')) {
            return;
        }

        if (in_array($plugin, self::GUEST_ALLOWED_PLUGINS, true)) {
            return;
        }

        // CB login module reads GET 'return' as plain base64 (mod_cblogin/helper.php:77).
        // The B: prefix is added by the login template into the hidden POST field
        // (default.php:24), then stripped by comprofiler.php:1225 after form submission.
        // We must NOT add B: in the URL — it would double-prefix and break the return.
        // urlencode() protects base64 '+' and '=' characters in the query string.
        // Route::_() second arg false disables XHTML &amp; escaping; routing still applies.
        $return = base64_encode(Uri::getInstance()->toString());
        $loginUrl = Route::_(
            'index.php?option=com_comprofiler&view=login&return=' . urlencode($return),
            false
        );

        $app->enqueueMessage(Text::_('PLG_SYSTEM_CBGJGUESTACCESS_LOGIN_REQUIRED'), 'notice');
        $app->redirect($loginUrl, 302);
        $app->close();
    }
}
