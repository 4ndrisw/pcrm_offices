<?php

use app\services\offices\OfficesPipeline;

defined('BASEPATH') or exit('No direct script access allowed');

class Offices extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('offices_model');
        $this->load->model('clients_model');
        $this->load->model('projects_model');
    }

    /* Get all offices in case user go on index page */
    public function index($id = '')
    {
        if (!has_permission('offices', '', 'view')) {
            access_denied('offices');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('offices', 'admin/tables/table'));
        }
        $data['officeid']            = $id;
        $data['title']                 = _l('offices_tracking');
        $this->load->view('admin/offices/manage', $data);
    }


    /* Add new office or update existing */
    public function office($id)
    {

        $office = $this->offices_model->get($id);

        if (!$office || !user_can_view_office($id)) {
            blank_page(_l('office_not_found'));
        }

        $data['office'] = $office;
        $data['edit']     = false;
        $title            = _l('preview_office');


        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['office_statuses'] = $this->offices_model->get_statuses();
        $data['title']             = $title;

        $office->date       = _d($office->date);

        if ($office->project_id !== null) {
            $this->load->model('projects_model');
            $office->project_data = $this->projects_model->get($office->project_id);
        }

        //$data = office_mail_preview_data($template_name, $office->clientid);

        //$data['office_items']    = $this->offices_model->get_office_item($id);

        $data['activity']          = $this->offices_model->get_office_activity($id);
        $data['office']          = $office;
        $data['members']           = $this->staff_model->get('', ['active' => 1]);
        $data['office_statuses'] = $this->offices_model->get_statuses();
        $data['totalNotes']        = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'office']);

        $data['send_later'] = false;
        if ($this->session->has_userdata('send_later')) {
            $data['send_later'] = true;
            $this->session->unset_userdata('send_later');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('offices', 'admin/tables/small_table'));
        }

        $this->load->view('admin/offices/office_preview', $data);
    }


    /* Add new office */
    public function create()
    {
        if ($this->input->post()) {

            $office_data = $this->input->post();

            $save_and_send_later = false;
            if (isset($office_data['save_and_send_later'])) {
                unset($office_data['save_and_send_later']);
                $save_and_send_later = true;
            }

            if (!has_permission('offices', '', 'create')) {
                access_denied('offices');
            }

            $next_office_number = get_option('next_office_number');
            $_format = get_option('office_number_format');
            $_prefix = get_option('office_prefix');

            $prefix  = isset($office->prefix) ? $office->prefix : $_prefix;
            $format  = isset($office->number_format) ? $office->number_format : $_format;
            $number  = isset($office->number) ? $office->number : $next_office_number;

            $date = date('Y-m-d');

            $office_data['formatted_number'] = office_number_format($number, $format, $prefix, $date);

            $id = $this->offices_model->add($office_data);

            if ($id) {
                set_alert('success', _l('added_successfully', _l('office')));

                $redUrl = admin_url('offices/office/' . $id);

                if ($save_and_send_later) {
                    $this->session->set_userdata('send_later', true);
                    // die(redirect($redUrl));
                }

                redirect(
                    !$this->set_office_pipeline_autoload($id) ? $redUrl : admin_url('offices/office/')
                );
            }
        }
        $title = _l('create_new_office');

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }
        /*
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        */
        $data['kepala_dinas']             = $this->offices_model->get_staff_by_role('', ['name' => 'Kepala Dinas']);
        $data['kepala_upt']             = $this->offices_model->get_staff_by_role('', ['name' => 'Kepala UPT']);
        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['office_statuses'] = $this->offices_model->get_statuses();
        $data['title']             = $title;

        $this->load->view('admin/offices/office_create', $data);
    }

    /* Add new office */
    public function import($client,$project)
    {

        $data['clientid'] = $this->uri->segment(4);
        $data['project_id'] = $this->uri->segment(5);

        $project = $this->projects_model->get($data['project_id']);

        $data['project_data'] = false;
        $data['task'] = false;

        if(isset($project->id)){
            $data['project_data'] = $project;
            $data['client_data'] = $project->client_data;
            $task = $this->projects_model->get_tasks($project->id);
            $data['task_data'] = $task;
        }

        if ($this->input->post()) {

            $office_data = $this->input->post();

            $save_and_send_later = false;
            if (isset($office_data['save_and_send_later'])) {
                unset($office_data['save_and_send_later']);
                $save_and_send_later = true;
            }

            if (!has_permission('offices', '', 'create')) {
                access_denied('offices');
            }

            $next_office_number = get_option('next_office_number');
            $_format = get_option('office_number_format');
            $_prefix = get_option('office_prefix');

            $prefix  = isset($office->prefix) ? $office->prefix : $_prefix;
            $format  = isset($office->number_format) ? $office->number_format : $_format;
            $number  = isset($office->number) ? $office->number : $next_office_number;

            $date = date('Y-m-d');

            $office_data['formatted_number'] = office_number_format($number, $format, $prefix, $date);

            $id = $this->offices_model->add($office_data);

            if ($id) {
                set_alert('success', _l('added_successfully', _l('office')));

                $redUrl = admin_url('offices/office/' . $id);

                if ($save_and_send_later) {
                    $this->session->set_userdata('send_later', true);
                    // die(redirect($redUrl));
                }

                redirect(
                    !$this->set_office_pipeline_autoload($id) ? $redUrl : admin_url('offices/office/')
                );
            }
        }


        $title = _l('create_new_office');

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }
        /*
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        */

        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['office_statuses'] = $this->offices_model->get_statuses();
        $data['title']             = $title;

        $this->load->view('admin/offices/office_import', $data);
    }

    /* update office */
    public function update($id)
    {
        if ($this->input->post()) {
            $office_data = $this->input->post();

            $save_and_send_later = false;
            if (isset($office_data['save_and_send_later'])) {
                unset($office_data['save_and_send_later']);
                $save_and_send_later = true;
            }

            if (!has_permission('offices', '', 'edit')) {
                access_denied('offices');
            }

            $next_schedule_number = get_option('next_office_number');
            $format = get_option('office_number_format');
            $_prefix = get_option('office_prefix');

            $number_settings = $this->get_number_settings($id);

            $prefix = isset($number_settings->prefix) ? $number_settings->prefix : $_prefix;

            $number  = isset($office_data['number']) ? $office_data['number'] : $next_office_number;

            $date = date('Y-m-d');

            $office_data['formatted_number'] = office_number_format($number, $format, $prefix, $date);

            $success = $this->offices_model->update($office_data, $id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('office')));
            }

            if ($this->set_office_pipeline_autoload($id)) {
                redirect(admin_url('offices/'));
            } else {
                redirect(admin_url('offices/office/' . $id));
            }
        }

            $office = $this->offices_model->get($id);

            if (!$office || !user_can_view_office($id)) {
                blank_page(_l('office_not_found'));
            }

            $data['office'] = $office;
            $data['edit']     = true;
            $title            = _l('edit', _l('office_lowercase'));


        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }


        $data['office_members']  = $this->offices_model->get_office_members($id);
        //$data['office_items']    = $this->offices_model->get_office_item($id);

        $data['kepala_dinas']             = $this->offices_model->get_staff_by_role('', ['name' => 'Kepala Dinas']);
        $data['kepala_upt']             = $this->offices_model->get_staff_by_role('', ['name' => 'Kepala UPT']);

        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['office_statuses'] = $this->offices_model->get_statuses();
        $data['title']             = $title;
        $this->load->view('admin/offices/office_update', $data);
    }

    public function get_number_settings($id){
        $this->db->select('prefix');
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'schedules')->row();

    }

    public function update_number_settings($id)
    {
        $response = [
            'success' => false,
            'message' => '',
        ];
        if (has_permission('offices', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'offices', [
                'prefix' => $this->input->post('prefix'),
            ]);
            if ($this->db->affected_rows() > 0) {
                $response['success'] = true;
                $response['message'] = _l('updated_successfully', _l('office'));
            }
        }

        echo json_encode($response);
        die;
    }

    public function validate_office_number()
    {
        $isedit          = $this->input->post('isedit');
        $number          = $this->input->post('number');
        $date            = $this->input->post('date');
        $original_number = $this->input->post('original_number');
        $number          = trim($number);
        $number          = ltrim($number, '0');

        if ($isedit == 'true') {
            if ($number == $original_number) {
                echo json_encode(true);
                die;
            }
        }

        if (total_rows(db_prefix() . 'offices', [
            'YEAR(date)' => date('Y', strtotime(to_sql_date($date))),
            'number' => $number,
        ]) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }


    public function add_note($rel_id)
    {
        if ($this->input->post() && user_can_view_office($rel_id)) {
            $this->misc_model->add_note($this->input->post(), 'office', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        if (user_can_view_office($id)) {
            $data['notes'] = $this->misc_model->get_notes($id, 'office');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }

    public function mark_action_status($status, $id)
    {
        if (!has_permission('offices', '', 'edit')) {
            access_denied('offices');
        }
        $success = $this->offices_model->mark_action_status($status, $id);
        if ($success) {
            set_alert('success', _l('office_status_changed_success'));
        } else {
            set_alert('danger', _l('office_status_changed_fail'));
        }
        if ($this->set_office_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('offices/office/' . $id));
        }
    }


    public function set_office_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }

        if ($this->session->has_userdata('office_pipeline')
                && $this->session->userdata('office_pipeline') == 'true') {
            $this->session->set_flashdata('officeid', $id);

            return true;
        }

        return false;
    }

    public function copy($id)
    {
        if (!has_permission('offices', '', 'create')) {
            access_denied('offices');
        }
        if (!$id) {
            die('No office found');
        }
        $new_id = $this->offices_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('office_copied_successfully'));
            if ($this->set_office_pipeline_autoload($new_id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('offices/office/' . $new_id));
            }
        }
        set_alert('danger', _l('office_copied_fail'));
        if ($this->set_office_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('offices/office/' . $id));
        }
    }

    /* Delete office */
    public function delete($id)
    {
        if (!has_permission('offices', '', 'delete')) {
            access_denied('offices');
        }
        if (!$id) {
            redirect(admin_url('offices'));
        }
        $success = $this->offices_model->delete($id);
        if (is_array($success)) {
            set_alert('warning', _l('is_invoiced_office_delete_error'));
        } elseif ($success == true) {
            set_alert('success', _l('deleted', _l('office')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('office_lowercase')));
        }
        redirect(admin_url('offices'));
    }

    /* Used in kanban when dragging and mark as */
    public function update_office_status()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            $this->offices_model->update_office_status($this->input->post());
        }
    }

    public function clear_signature($id)
    {
        if (has_permission('offices', '', 'delete')) {
            $this->offices_model->clear_signature($id);
        }

        redirect(admin_url('offices/office/' . $id));
    }

}
