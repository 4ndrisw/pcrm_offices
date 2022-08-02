<?php defined('BASEPATH') or exit('No direct script access allowed');

class Myoffice extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('offices_model');
        $this->load->model('clients_model');
    }

    /* Get all offices in case user go on index page */
    public function list($id = '')
    {
        if (!is_client_logged_in() && !is_staff_logged_in()) {
            if (get_option('view_office_only_logged_in') == 1) {
                redirect_after_login_to_current_url();
                redirect(site_url('authentication/login'));
            }
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('offices', 'admin/tables/table'));
        }
        $contact_id = get_contact_user_id();
        $user_id = get_user_id_by_contact_id($contact_id);
        $client = $this->clients_model->get($user_id);
        $data['offices'] = $this->offices_model->get_client_offices($client);
        $data['officeid']            = $id;
        $data['title']                 = _l('offices_tracking');

        $data['bodyclass'] = 'offices';
        $this->data($data);
        $this->view('themes/'. active_clients_theme() .'/views/offices/offices');
        $this->layout();
    }

    public function show($id, $hash)
    {
        check_office_restrictions($id, $hash);
        $office = $this->offices_model->get($id);

        if (!is_client_logged_in()) {
            load_client_language($office->clientid);
        }

        $identity_confirmation_enabled = get_option('office_accept_identity_confirmation');

        // Handle Office PDF generator

        $office_number = format_office_number($office->id);
        if ($this->input->post('officepdf')) {
            try {
                $pdf = office_pdf($office);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }

            //$office_number = format_office_number($office->id);
            $companyname     = get_option('company_name');
            if ($companyname != '') {
                $office_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
            }

            $filename = hooks()->apply_filters('customers_area_download_office_filename', mb_strtoupper(slug_it($office_number), 'UTF-8') . '.pdf', $office);

            $pdf->Output($filename, 'D');
            die();
        }

        $data['title'] = $office_number;
        $this->disableNavigation();
        $this->disableSubMenu();

        $data['office_number']              = $office_number;
        $data['hash']                          = $hash;
        $data['can_be_accepted']               = false;
        $data['office']                     = hooks()->apply_filters('office_html_pdf_data', $office);
        $data['bodyclass']                     = 'viewoffice';
        $data['client_company']                = get_office_name_by_id($id);
        $setSize = get_option('office_qrcode_size');
        $data['identity_confirmation_enabled'] = $identity_confirmation_enabled;
        if ($identity_confirmation_enabled == '1') {
            $data['bodyclass'] .= ' identity-confirmation';
        }

        $qrcode_data  = '';
        $qrcode_data .= _l('office_number') . ' : ' . $office_number ."\r\n";
        $qrcode_data .= _l('office_date') . ' : ' . $office->date ."\r\n";
        $qrcode_data .= _l('office_datesend') . ' : ' . $office->datesend ."\r\n";
        $qrcode_data .= _l('office_assigned_string') . ' : ' . get_staff_full_name($office->assigned) ."\r\n";
        $qrcode_data .= _l('office_url') . ' : ' . site_url('offices/show/'. $office->id .'/'.$office->hash) ."\r\n";

        $office_path = get_upload_path_by_type('offices') . $office->id . '/';
        _maybe_create_upload_path('uploads/offices');
        _maybe_create_upload_path('uploads/offices/'.$office_path);

        $params['data'] = $qrcode_data;
        $params['writer'] = 'png';
        $params['setSize'] = isset($setSize) ? $setSize : 160;
        $params['encoding'] = 'UTF-8';
        $params['setMargin'] = 0;
        $params['setForegroundColor'] = ['r'=>0,'g'=>0,'b'=>0];
        $params['setBackgroundColor'] = ['r'=>255,'g'=>255,'b'=>255];

        $params['crateLogo'] = true;
        $params['logo'] = './uploads/company/favicon.png';
        $params['setResizeToWidth'] = 60;

        $params['crateLabel'] = false;
        $params['label'] = $office_number;
        $params['setTextColor'] = ['r'=>255,'g'=>0,'b'=>0];
        $params['ErrorCorrectionLevel'] = 'hight';

        $params['saveToFile'] = FCPATH.'uploads/offices/'.$office_path .'assigned-'.$office_number.'.'.$params['writer'];

        $this->load->library('endroid_qrcode');
        $this->endroid_qrcode->generate($params);

        $this->data($data);
        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');
        $this->view('themes/'. active_clients_theme() .'/views/offices/officehtml');
        add_views_tracking('office', $id);
        hooks()->do_action('office_html_viewed', $id);
        no_index_customers_area();
        $this->layout();
    }


    /* Generates office PDF and senting to email  */
    public function pdf($id)
    {
        $canView = user_can_view_office($id);
        if (!$canView) {
            access_denied('Offices');
        } else {
            if (!has_permission('offices', '', 'view') && !has_permission('offices', '', 'view_own') && $canView == false) {
                access_denied('Offices');
            }
        }
        if (!$id) {
            redirect(admin_url('offices'));
        }
        $office        = $this->offices_model->get($id);
        $office_number = format_office_number($office->id);

        $office->assigned_path = FCPATH . get_office_upload_path('office').$office->id.'/assigned-'.$office_number.'.png';
        $office->acceptance_path = FCPATH . get_office_upload_path('office').$office->id .'/'.$office->signature;
        $office->client_company = $this->clients_model->get($office->clientid)->company;
        $office->acceptance_date_string = _dt($office->acceptance_date);


        try {
            $pdf = office_pdf($office);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $fileNameHookData = hooks()->apply_filters('office_file_name_admin_area', [
                            'file_name' => mb_strtoupper(slug_it($office_number)) . '.pdf',
                            'office'  => $office,
                        ]);

        $pdf->Output($fileNameHookData['file_name'], $type);
    }


}
