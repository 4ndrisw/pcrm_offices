<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Office_send_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $office;

    protected $contact;

    public $slug = 'office-send-to-client';

    public $rel_type = 'office';

    public function __construct($office, $contact, $cc = '')
    {
        parent::__construct();

        $this->office = $office;
        $this->contact = $contact;
        $this->cc      = $cc;
    }

    public function build()
    {
        if ($this->ci->input->post('email_attachments')) {
            $_other_attachments = $this->ci->input->post('email_attachments');
            foreach ($_other_attachments as $attachment) {
                $_attachment = $this->ci->offices_model->get_attachments($this->office->id, $attachment);
                $this->add_attachment([
                                'attachment' => get_upload_path_by_type('office') . $this->office->id . '/' . $_attachment->file_name,
                                'filename'   => $_attachment->file_name,
                                'type'       => $_attachment->filetype,
                                'read'       => true,
                            ]);
            }
        }

        $this->to($this->contact->email)
        ->set_rel_id($this->office->id)
        ->set_merge_fields('client_merge_fields', $this->office->clientid, $this->contact->id)
        ->set_merge_fields('office_merge_fields', $this->office->id);
    }
}
