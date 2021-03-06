<?php
/**
 * @copyright See LICENCE.md
 * @package   Driskell_Daemon
 * @author    Jason Woods <devel@jasonwoods.me.uk>
 */

/**
 * Observer class
 */
class Driskell_CookieCutter_Model_Observer
{
    /**
     * Trigger removal of duplicated cookies on every page load
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function triggerCookieCutter($observer)
    {
        if (!Mage::getStoreConfig('driskell_cookiecutter/general/enabled') || !isset($_SERVER['HTTP_COOKIE']) || !isset($_SERVER['HTTP_HOST'])) {
            return;
        }

        $cookies = array();
        $duplicateCookies = array();
        $cookieSplit = preg_split('/;\\s*/', $_SERVER['HTTP_COOKIE']);
        foreach ($cookieSplit as $cookieString) {
            $keyValue = explode('=', $cookieString, 2);
            $cookieValue = isset($keyValue[1]) ? $keyValue[1] : '';
            // Handle receiving same cookie twice - this can happen normally because Magento will renew sessions
            // by sending a cookie domain, but will start a new logged in session without it so we end up with two cookies
            if (isset($cookies[$keyValue[0]]) && !in_array($keyValue[0], $duplicateCookies) && $cookieValue !== $cookies[$keyValue[0]]) {
                $duplicateCookies[] = $keyValue[0];
            }
            $cookies[$keyValue[0]] = $cookieValue;
        }

        if (!$duplicateCookies) {
            return;
        }

        $hostSplit = explode('.', $_SERVER['HTTP_HOST']);
        if (count($hostSplit) < 2) {
            return;
        }

        $hostList[] = $currentHost = implode('.', array_reverse(array(array_pop($hostSplit), array_pop($hostSplit))));
        foreach (array_reverse($hostSplit) as $key) {
            $hostList[] = $currentHost = $key . '.' . $currentHost;
        }

        foreach ($duplicateCookies as $key) {
            Mage::log(
                sprintf('Removing duplicated cookie %s from hosts %s', $key, implode(', ', $hostList)),
                null,
                'cookiecutter.log'
            );
            setcookie($key, '', time() - 3600, '/');
            foreach ($hostList as $host) {
                // NOTE: Should we load the path from configuration?
                setcookie($key, '', time() - 3600, '/', $host);
            }
        }
    }
}
