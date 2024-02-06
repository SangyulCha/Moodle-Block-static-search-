<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Block google_search is defined here.
 *
 * @package     block_google_staic_search
 * @copyright   2024 sangyul cha <eddie6798@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_google_static_search extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_google_static_search');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        // Google API Key and Custom Search Engine ID
        $api_key = 'AIzaSyDRzmXvfPQWSsilU-5eos8a68PFwKsGF9U';
        $search_engine_id = '46edb91afe35b466e';

        // Set the search term with spaces replaced by %20
        $search_term = str_replace(' ', '%20', 'Moodle Blocks');

        // Google API call
        $api_url = "https://www.googleapis.com/customsearch/v1?q=$search_term&key=$api_key&cx=$search_engine_id";
        $response = file_get_contents($api_url);

        // Convert JSON data to an associative array
        $json_data = json_decode($response, true);

        // Output JSON data in raw format
        //$this->content->text = '<pre>' . htmlentities($response) . '</pre>';

        // Display search results in HTML
        $html_result = '<div class="block-google-static-search">';
        $html_result .= '<link rel="stylesheet" type="text/css" href="' . $this->get_css_url() . '">';
        $html_result .= '<table>';
        foreach ($json_data['items'] as $item) {
            $title = $item['title'];
            $link = $item['link'];
            $html_result .= "<tr><td><a href='$link'>$title</a></td></tr>";
        }
        $html_result .= '</table>';
        $html_result .= '</div>';

        $this->content->text = $html_result;
        return $this->content;
    }
    private function get_css_url() {
        $block_path = rtrim(dirname(__FILE__), '/');
        $css_file = 'style.css';
        return "$block_path/$css_file";
    }
    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_google_static_search');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array(
            'all' => true,
        );
    }
    function _self_test() {
  	return true;
	}
}
