<?php
// This file is part of Moodle - http://moodle.org/
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
 * Analytics tests for dimensions infrastructure.
 *
 * @package    local_analytics
 * @category   test
 * @copyright  2016 Catalyst IT
 * @author     Nigel Cunningham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_analytics;

use local\analytics\dimensions\dimension_interface;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../dimensions.php');

function should_use_mock_scandir($set = null) {
    static $use_mock_scandir = true;

    if (!is_null($set)) {
        $use_mock_scandir = $set;
    }

    return $use_mock_scandir;
}

/**
 * Mock scandir function.
 *
 * @param string $directory
 *   The directory to scan.
 *
 * @return array
 *   The list of files found.
 */
function scandir($directory) {
    if (should_use_mock_scandir()) {
        return array(
            '.',
            '..',
            'foo.text',
            'bar.php',
        );
    } else {
        return \scandir($directory);
    }
}

/**
 * Class local_analytics_dimensions_testcase
 */
class local_analytics_dimensions_testcase extends \advanced_testcase
{

    /**
     * Setup test data.
     */
    public function setUp() {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();

// 		set_config('location', 'header', 'local_analytics');
    }

    /**
     * Test that enumerate_plugins filters non php files out.
     *
     * GIVEN the dimensions class
     * WHEN its enumerate_plugins function is invoked
     * THEN the result should be a list of files in the dimensions directory
     * AND the '.' and '..' files should be excluded
     * AND files not ending in '.php' should be excluded
     *
     * @test
     */
    public function enumeratePluginsFiltersScanDirAsExpected() {
        $actual = dimensions::enumerate_plugins();

        // 3 because it's the fourth element (zero indexed) in the scandir result.
        $expected = array(3 => 'bar.php');

        $this->assertSame($expected, $actual);
    }

    /**
     * Test that instantiate_plugins can instantiate all plugins.
     *
     * GIVEN the dimensions class
     * WHEN its instantiate_plugins function is invoked
     * THEN the result should be an array of plugin instances
     *
     * @test
     */
    public function instantiatePluginsCanReturnAnArrayOfPlugins() {
        should_use_mock_scandir(false);

        $actual = dimensions::instantiate_plugins();

        foreach ($actual as $name => $plugin) {
            $this->assertInstanceOf($name, $plugin);
        }
    }

    /**
     * Test that instantiated plugins have expected attributes.
     *
     * GIVEN the array of plugin instances returned by instantiate_plugins
     * WHEN each is checked
     * THEN it should implement the dimension interface.
     *
     * @test
     */
    public function instantiatedPluginsImplementInterface() {
        should_use_mock_scandir(false);

        $actual = dimensions::instantiate_plugins();

        foreach ($actual as $name => $plugin) {
            $this->assertTrue($plugin instanceOf dimension_interface);
        }
    }

}