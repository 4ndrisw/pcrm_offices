<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('offices_model');

$statuses = $CI->offices_model->get_statuses();

$aColumns = [
    'formatted_number',
    'full_name',
    'status',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'offices';


$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'offices.clientid',
    //'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . db_prefix() . 'offices.project_id',
];



$where  = [];

$additionalColumns = hooks()->apply_filters('offices_table_additional_columns_sql', [
    'id',
    'acceptance_lastname',
]);

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns);

$output  = $result['output'];
$rResult = $result['rResult'];

json_encode($rResult);

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'formatted_number') {
            $_data = '<a href="' . admin_url('offices/office/' . $aRow['id']) . '">' . $_data . '</a>';
            $_data .= '<div class="row-options">';
            $_data .= '<a href="' . admin_url('offices/update/' . $aRow['id']) . '">' . _l('edit') . '</a>';

            if (has_permission('offices', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('offices/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= '</div>';
        }elseif ($aColumns[$i] == 'project_id') {
            $_data = get_project_name_by_id($_data);
        }/*
        elseif ($aColumns[$i] == 'name') {
            $_data = $_data;
        }*/
        elseif ($aColumns[$i] == 'status') {

            $span = '';
                //if (!$locked) {
                    $span .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
                    $span .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                    $span .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
                    $span .= '</a>';

                    $span .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';
                    foreach ($statuses as $officeChangeStatus) {
                        if ($aRow['status'] != $officeChangeStatus) {
                            $span .= '<li>
                          <a href="#" onclick="office_mark_as(' . $officeChangeStatus . ',' . $aRow['id'] . '); return false;">
                             ' . format_office_status($officeChangeStatus) . '
                          </a>
                       </li>';
                        }
                    }
                    $span .= '</ul>';
                    $span .= '</div>';
                //}
                $span .= '</span>';
            

            if ($aRow['status'] == 1) {
                $outputStatus = '<span class="label label-danger inline-block">' . _l('office_status_draft') . $span;
            } elseif ($aRow['status'] == 2) {
                $outputStatus = '<span class="label label-info inline-block">' . _l('office_status_sent') . $span;
            } elseif ($aRow['status'] == 3) {
                $outputStatus = '<span class="label label-default inline-block">' . _l('office_status_declined') . $span;
            } elseif ($aRow['status'] == 4) {
                $outputStatus = '<span class="label label-success inline-block">' . _l('office_status_accepted') . '</span>';
            } elseif ($aRow['status'] == 5) {
                $outputStatus = '<span class="label label-primary inline-block">' . _l('office_status_expired') . $span;
            }

            $_data = $outputStatus;


        } elseif ($aColumns[$i] == 'date') {
            $_data = _d($_data);
        } elseif ($aColumns[$i] == 'acceptance_firstname') {
            //$_data = $_data;
            $_data = $aRow['acceptance_firstname'] .' '. $aRow['acceptance_lastname'];
        }elseif ($aColumns[$i] == 'acceptance_date') {
            $_data = _dt($_data); 
        }


        $row[] = $_data;
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
