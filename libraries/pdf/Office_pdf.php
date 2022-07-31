<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(LIBSPATH . 'pdf/App_pdf.php');

class Office_pdf extends App_pdf
{
    protected $office;

    private $office_number;

    public function __construct($office, $tag = '')
    {
        $this->load_language($office->clientid);

        $office                = hooks()->apply_filters('office_html_pdf_data', $office);
        $GLOBALS['office_pdf'] = $office;

        parent::__construct();

        $this->tag             = $tag;
        $this->office        = $office;
        $this->office_number = format_office_number($this->office->id);

        $this->SetTitle($this->office_number);
    }

    public function prepare()
    {

        $this->set_view_vars([
            'status'          => $this->office->status,
            'office_number' => $this->office_number,
            'office'        => $this->office,
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'office';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_officepdf.php';
        $actualPath = module_views_path('offices','themes/' . active_clients_theme() . '/views/offices/officepdf.php');

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
