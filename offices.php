<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: offices
Description: Default module for defining offices
Version: 1.0.1
Requires at least: 2.3.*
*/

define('OFFICES_MODULE_NAME', 'offices');
define('OFFICE_ATTACHMENTS_FOLDER', 'uploads/offices/');

hooks()->add_filter('before_office_updated', '_format_data_office_feature');
hooks()->add_filter('before_office_added', '_format_data_office_feature');

//hooks()->add_action('after_cron_run', 'offices_notification');
hooks()->add_action('admin_init', 'offices_module_init_menu_items');
hooks()->add_action('admin_init', 'offices_permissions');
hooks()->add_action('clients_init', 'offices_clients_area_menu_items');

hooks()->add_action('staff_member_deleted', 'offices_staff_member_deleted');

hooks()->add_action('after_office_updated', 'office_create_assigned_qrcode');

hooks()->add_filter('migration_tables_to_replace_old_links', 'offices_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'offices_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'offices_global_search_result_output', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'offices_add_dashboard_widget');
hooks()->add_filter('module_offices_action_links', 'module_offices_action_links');

function offices_add_dashboard_widget($widgets)
{
    $widgets[] = [
        'path'      => 'offices/widgets/office_this_week',
        'container' => 'left-8',
    ];

    return $widgets;
}


function offices_staff_member_deleted($data)
{
    $CI = &get_instance();
    $CI->db->where('staff_id', $data['id']);
    $CI->db->update(db_prefix() . 'offices', [
            'staff_id' => $data['transfer_data_to'],
        ]);
}

function offices_global_search_result_output($output, $data)
{
    if ($data['type'] == 'offices') {
        $output = '<a href="' . admin_url('offices/office/' . $data['result']['id']) . '">' . format_office_number($data['result']['id']) . '</a>';
    }

    return $output;
}

function offices_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (has_permission('offices', '', 'view')) {

        // offices
        $CI->db->select()
           ->from(db_prefix() . 'offices')
           ->like(db_prefix() . 'offices.formatted_number', $q)->limit($limit);
        
        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'offices',
                'search_heading' => _l('offices'),
            ];
        
        if(isset($result[0]['result'][0]['id'])){
            return $result;
        }

        // offices
        $CI->db->select()->from(db_prefix() . 'offices')->like(db_prefix() . 'clients.company', $q)->or_like(db_prefix() . 'offices.formatted_number', $q)->limit($limit);
        $CI->db->join(db_prefix() . 'clients',db_prefix() . 'offices.clientid='.db_prefix() .'clients.userid', 'left');
        $CI->db->order_by(db_prefix() . 'clients.company', 'ASC');

        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'offices',
                'search_heading' => _l('offices'),
            ];
    }

    return $result;
}

function offices_migration_tables_to_replace_old_links($tables)
{
    $tables[] = [
                'table' => db_prefix() . 'offices',
                'field' => 'description',
            ];

    return $tables;
}

function offices_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('offices', $capabilities, _l('offices'));
}


/**
* Register activation module hook
*/
register_activation_hook(OFFICES_MODULE_NAME, 'offices_module_activation_hook');

function offices_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register deactivation module hook
*/
register_deactivation_hook(OFFICES_MODULE_NAME, 'offices_module_deactivation_hook');

function offices_module_deactivation_hook()
{

     log_activity( 'Hello, world! . offices_module_deactivation_hook ' );
}

//hooks()->add_action('deactivate_' . $module . '_module', $function);

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(OFFICES_MODULE_NAME, [OFFICES_MODULE_NAME]);

/**
 * Init offices module menu items in setup in admin_init hook
 * @return null
 */
function offices_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
            'name'       => _l('office'),
            'url'        => 'offices',
            'permission' => 'offices',
            'position'   => 57,
            ]);

    if (has_permission('offices', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('offices', [
                'slug'     => 'offices-tracking',
                'name'     => _l('offices'),
                'icon'     => 'fa fa-briefcase',
                'href'     => admin_url('offices'),
                'position' => 13,
        ]);
    }
}

function module_offices_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=offices') . '">' . _l('settings') . '</a>';

    return $actions;
}

function offices_clients_area_menu_items()
{   
    // Show menu item only if client is logged in
    if (is_client_logged_in()) {
        add_theme_menu_item('offices', [
                    'name'     => _l('offices'),
                    'href'     => site_url('offices/list'),
                    'position' => 15,
        ]);
    }
}

/**
 * [perfex_dark_theme_settings_tab net menu item in setup->settings]
 * @return void
 */
function offices_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('offices', [
        'name'     => _l('settings_group_offices'),
        //'view'     => module_views_path(OFFICES_MODULE_NAME, 'admin/settings/includes/offices'),
        'view'     => 'offices/offices_settings',
        'position' => 51,
    ]);
}

$CI = &get_instance();
$CI->load->helper(OFFICES_MODULE_NAME . '/offices');

if(($CI->uri->segment(0)=='admin' && $CI->uri->segment(1)=='offices') || $CI->uri->segment(1)=='offices'){
    $CI->app_css->add(OFFICES_MODULE_NAME.'-css', base_url('modules/'.OFFICES_MODULE_NAME.'/assets/css/'.OFFICES_MODULE_NAME.'.css'));
    $CI->app_scripts->add(OFFICES_MODULE_NAME.'-js', base_url('modules/'.OFFICES_MODULE_NAME.'/assets/js/'.OFFICES_MODULE_NAME.'.js'));
}

