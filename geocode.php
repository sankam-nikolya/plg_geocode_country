<?php
/**
 * @package     plg_geocode
 *
 * @copyright   Copyright (C) 2011 - 2016 SNAKAM, Inc. All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

class PlgSystemGeocode extends JPlugin
{
    private $languge;
    private $country;
    private $app;

   	/**
     * Constructor.
     *
     * @param   object  &$subject  The object to observe.
     * @param   array   $config    An optional associative array of configuration settings.
     *
     * @since   1.5
     */
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);

        $this->loadLanguage();

        $this->app = JFactory::getApplication();

        if ($this->app->isAdmin())
        {
            return;
        }

        $session = JFactory::getSession();
        
        if(!$session->get('country', false)) {
            $this->setDefaults();
        }
    }

    private function setDefaults() {
        $session = JFactory::getSession();

        $this->languge = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
        $session->set('default_language', $this->languge);
        
        $this->country = $this->getUserCountry();
        $session->set('country', $this->country);
    }


    public function getUserCountry($ip = null)
    {
        if(empty($ip)) {
            $ip = $this->getUserIP();
        }

        if($ip) {
            $ip = ip2long($ip);

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select(
                $db->quoteName(
                    'l.country_name',
                    'name'
                )
            );
            $query->select(
                $db->quoteName(
                    'l.country_iso_code',
                    'iso_code'
                )
            );

            $query->from(
                $db->quoteName('#__blocks_ipv4', 'b')
            );
            $query->join(
                'LEFT',
                $db->quoteName('#__location', 'l') . ' ON (' . $db->quoteName('b.geoname_id') . ' = ' . $db->quoteName('l.id') . ')'
            );
            $query->where(
                $db->quote($ip).' BETWEEN ' . $db->quoteName('network_start') . ' AND ' . $db->quoteName('network_last')
            );
            $query->setLimit(1);

            $db->setQuery($query);
            $result = $db->loadObject();

            if(!empty($result)) {
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
        
    }

    private function getUserIP()
    {
        $ip = false;
            
        if (isset($_SERVER)) {
            if ($_SERVER['HTTP_CLIENT_IP']) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if(isset($_SERVER['HTTP_X_FORWARDED'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED'];
            } else if(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_FORWARDED_FOR'];
            } else if(isset($_SERVER['HTTP_FORWARDED'])) {
                $ip = $_SERVER['HTTP_FORWARDED'];
            } else if(isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_CLIENT_IP')) {
                $ip = getenv('HTTP_CLIENT_IP');
            } else if(getenv('HTTP_X_FORWARDED_FOR')) {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
            } else if(getenv('HTTP_X_FORWARDED')) {
                $ip = getenv('HTTP_X_FORWARDED');
            } else if(getenv('HTTP_FORWARDED_FOR')) {
                $ip = getenv('HTTP_FORWARDED_FOR');
            } else if(getenv('HTTP_FORWARDED')) {
                $ip = getenv('HTTP_FORWARDED');
            } else if(getenv('REMOTE_ADDR')) {
                $ip = getenv('REMOTE_ADDR');
            }
        }
        
        return $ip;
    }

}