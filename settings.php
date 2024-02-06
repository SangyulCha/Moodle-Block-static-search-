<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'block_google_static_search/search_engine_id',
        get_string('search_engine_id', 'block_google_static_search'),
        get_string('search_engine_id_desc', 'block_google_static_search'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'block_google_static_search/apikey',
        get_string('apikey', 'block_google_static_search'),
        get_string('apikey_desc', 'block_google_static_search'),
        ''
    ));
}