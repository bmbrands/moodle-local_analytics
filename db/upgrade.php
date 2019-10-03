<?php
// This file is part of the Local Analytics plugin for Moodle
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Analytics
 *
 * This module provides extensive analytics on a platform of choice
 * Currently support Google Analytics and Piwik
 *
 * @package    local_analytics
 * @copyright  Bas Brands, Sonsbeekmedia 2017
 * @author     Bas Brands <bas@sonsbeekmedia.nl>, David Bezemer <info@davidbezemer.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade local_analytics.
 *
 * @param int $oldversion
 * @return bool always true
 */

function xmldb_local_analytics_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2017032702) {
        $setting = $DB->get_record('config_plugins', array('plugin' => 'local_analytics', 'name' => 'analytics'));

        $options = array('piwik', 'ganalytics', 'guniversal');
        foreach ($options as $option) {
            if ($DB->get_record('config_plugins', array('plugin' => 'local_analytics', 'name' => $option))) {
                continue;
            }
            if ($setting->value == $option) {
                $newsetting = new stdClass();
                $newsetting->plugin = 'local_analytics';
                $newsetting->name = $option;
                $newsetting->value = 1;
                $DB->insert_record('config_plugins', $newsetting);
            } else {
                $newsetting = new stdClass();
                $newsetting->plugin = 'local_analytics';
                $newsetting->name = $option;
                $newsetting->value = 0;
                $DB->insert_record('config_plugins', $newsetting);
            }
        }

        if ($setting->value == 'ganalytics' || $setting->value == 'guniversal' ) {
            if ($siteid = $DB->get_record('config_plugins', array('plugin' => 'local_analytics', 'name' => 'siteid'))) {
                $newsetting = new stdClass();
                $newsetting->plugin = 'local_analytics';
                $newsetting->name = 'analyticsid';
                $newsetting->value = $siteid->value;
                $DB->insert_record('config_plugins', $newsetting);
            }
        } else {
            $newsetting = new stdClass();
            $newsetting->plugin = 'local_analytics';
            $newsetting->name = 'analyticsid';
            $newsetting->value = 0;
            $DB->insert_record('config_plugins', $newsetting);
        }

        upgrade_plugin_savepoint(true, 2017032702, 'local', 'analytics');
    }

    if ($oldversion < 2019070801) {
        // Remove 'analytics' from the configuration table.
        if ($analytics = $DB->get_record('config_plugins', array('plugin' => 'local_analytics', 'name' => 'analytics')) ) {
            $DB->delete_records('config_plugins', array('id' => $analytics->id));
        }
        upgrade_plugin_savepoint(true, 2019070801, 'local', 'analytics');
    }

    return true;
}
