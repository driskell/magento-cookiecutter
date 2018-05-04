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
     * @param Varien_Observer $observer
     * @return void
     */
    public function triggerCookieCutter(Varien_Observer $observer)
    {
        $this->cleanupDuplicatedCooies();
    }

    /**
     * Remove duplicated cookies
     *
     * @return void
     */
    private function cleanupDuplicatedCooies()
    {
        if (!isset($_SERVER['HTTP_COOKIE']) || !isset($_SERVER['HTTP_HOST'])) {
            return;
        }

        $cookies = array();
        $duplicateCookies = array();
        $cookieSplit = preg_split('/;\\s*/', $_SERVER['HTTP_COOKIE']);
        foreach ($cookieSplit as $cookieString) {
            $keyValue = explode('=', $cookieString, 2);
            if (isset($cookies[$keyValue[0]])) {
                $duplicateCookies[] = $keyValue[0];
            } else {
                $cookies[$keyValue[0]] = isset($keyValue[1]) ? $keyValue[1] : '';
            }
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
