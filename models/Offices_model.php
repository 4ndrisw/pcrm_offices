<?php

use app\services\AbstractKanban;
use app\services\offices\OfficesPipeline;

defined('BASEPATH') or exit('No direct script access allowed');

class Offices_model extends App_Model
{
    private $statuses;

    private $shipping_fields = ['shipping_street', 'shipping_city', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country'];

    public function __construct()
    {
        parent::__construct();

        $this->statuses = hooks()->apply_filters('before_set_office_statuses', [
            1,
            2,
        ]);
    }
    /**
     * Get unique sale agent for offices / Used for filters
     * @return array
     */
    public function get_assigneds()
    {
        return $this->db->query("SELECT DISTINCT(assigned) as assigned, CONCAT(firstname, ' ', lastname) as full_name FROM " . db_prefix() . 'offices JOIN ' . db_prefix() . 'staff on ' . db_prefix() . 'staff.staffid=' . db_prefix() . 'offices.assigned WHERE assigned != 0')->result_array();
    }

    /**
     * Get office/s
     * @param mixed $id office id
     * @param array $where perform where
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
//        $this->db->select('*,' . db_prefix() . 'currencies.id as currencyid, ' . db_prefix() . 'offices.id as id, ' . db_prefix() . 'currencies.name as currency_name');
        $this->db->select('*,' . db_prefix() . 'offices.id as id');
        $this->db->from(db_prefix() . 'offices');
        //$this->db->join(db_prefix() . 'currencies', db_prefix() . 'currencies.id = ' . db_prefix() . 'offices.currency', 'left');
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'offices.id', $id);
            $office = $this->db->get()->row();
            if ($office) {
                $office->attachments                           = $this->get_attachments($id);
                $office->visible_attachments_to_customer_found = false;

                foreach ($office->attachments as $attachment) {
                    if ($attachment['visible_to_customer'] == 1) {
                        $office->visible_attachments_to_customer_found = true;

                        break;
                    }
                }

                $office->items = get_items_by_type('office', $id);
                if(isset($office->office_id)){

                    if ($office->office_id != 0) {
                        $this->load->model('offices_model');
                        $office->office_data = $this->offices_model->get($office->office_id);
                    }

                }
                $office->client = $this->clients_model->get($office->clientid);

                if (!$office->client) {
                    $office->client          = new stdClass();
                    $office->client->company = $office->deleted_customer_name;
                }

                $this->load->model('email_schedule_model');
                $office->scheduled_email = $this->email_schedule_model->get($id, 'office');
            }

            return $office;
        }
        $this->db->order_by('number,YEAR(date)', 'desc');

        return $this->db->get()->result_array();
    }

    /**
     * Get office statuses
     * @return array
     */
    public function get_statuses()
    {
        return $this->statuses;
    }




    /**
     * Get office/s
     * @param mixed $id office id
     * @param array $where perform where
     * @return mixed
     */
    public function get_staff_by_role($id = '', $where = [])
    {
        $this->db->select('staffid, firstname, lastname');
        $this->db->from(db_prefix() . 'staff');
        $this->db->join(db_prefix() . 'roles', db_prefix() . 'roles.roleid = '. db_prefix() . 'staff.role');
        $this->db->where($where);

        return $this->db->get()->result_array();
    }

    /**
     * Get office statuses
     * @return array
     */
    public function get_status($status,$id)
    {
        $this->db->where('status', $status);
        $this->db->where('id', $id);
        $office = $this->db->get(db_prefix() . 'offices')->row();

        return $this->status;
    }


    /**
     * Copy office
     * @param mixed $id office id to copy
     * @return mixed
     */
    public function copy($id)
    {
        $_office                       = $this->get($id);
        $new_office_data               = [];
        $new_office_data['short_name']   = $_office->short_name;
        $new_office_data['full_name']   = $_office->full_name;

        $new_office_data['kepala_dinas_id']   = $_office->kepala_dinas_id;
        $new_office_data['kepala_upt_id']   = $_office->kepala_upt_id;

        $new_office_data['number']     = get_option('next_office_number');
        $new_office_data['date']       = _d(date('Y-m-d'));



        $number = get_option('next_office_number');
        $format = get_option('office_number_format');
        $prefix = get_option('office_prefix');
        $date = date('Y-m-d');

        $new_office_data['formatted_number'] = office_number_format($number, $format, $prefix, $date);



        $new_office_data['assigned']       = $_office->assigned;
        // Since version 1.0.6
        $new_office_data['billing_street']   = clear_textarea_breaks($_office->billing_street);
        $new_office_data['billing_city']     = $_office->billing_city;
        $new_office_data['billing_state']    = $_office->billing_state;
        $new_office_data['billing_zip']      = $_office->billing_zip;
        $new_office_data['billing_country']  = $_office->billing_country;
        $new_office_data['shipping_street']  = clear_textarea_breaks($_office->shipping_street);
        $new_office_data['shipping_city']    = $_office->shipping_city;
        $new_office_data['shipping_state']   = $_office->shipping_state;
        $new_office_data['shipping_zip']     = $_office->shipping_zip;
        $new_office_data['shipping_country'] = $_office->shipping_country;
        if ($_office->include_shipping == 1) {
            $new_office_data['include_shipping'] = $_office->include_shipping;
        }
        $new_office_data['show_shipping_on_office'] = $_office->show_shipping_on_office;
        // Set to unpaid status automatically
        $new_office_data['status']     = 1;

        $id = $this->add($new_office_data);
        if ($id) {

            $tags = get_tags_in($_office->id, 'office');
            handle_tags_save($tags, $id, 'office');

            $this->log_office_activity('Copied Office ' . format_office_number($_office->id));

            return $id;
        }

        return false;
    }

    /**
     * Performs offices totals status
     * @param array $data
     * @return array
     */
    public function get_offices_total($data)
    {
        $statuses            = $this->get_statuses();
        $has_permission_view = has_permission('offices', '', 'view');
        $this->load->model('currencies_model');
        if (isset($data['currency'])) {
            $currencyid = $data['currency'];
        } elseif (isset($data['customer_id']) && $data['customer_id'] != '') {
            $currencyid = $this->clients_model->get_customer_default_currency($data['customer_id']);
            if ($currencyid == 0) {
                $currencyid = $this->currencies_model->get_base_currency()->id;
            }
        } elseif (isset($data['office_id']) && $data['office_id'] != '') {
            $this->load->model('offices_model');
            $currencyid = $this->offices_model->get_currency($data['office_id'])->id;
        } else {
            $currencyid = $this->currencies_model->get_base_currency()->id;
        }

        $currency = get_currency($currencyid);
        $where    = '';
        if (isset($data['customer_id']) && $data['customer_id'] != '') {
            $where = ' AND clientid=' . $data['customer_id'];
        }

        if (isset($data['office_id']) && $data['office_id'] != '') {
            $where .= ' AND office_id=' . $data['office_id'];
        }

        if (!$has_permission_view) {
            $where .= ' AND ' . get_offices_where_sql_for_staff(get_staff_user_id());
        }

        $sql = 'SELECT';
        foreach ($statuses as $office_status) {
            $sql .= '(SELECT SUM(total) FROM ' . db_prefix() . 'offices WHERE status=' . $office_status;
            $sql .= ' AND currency =' . $this->db->escape_str($currencyid);
            if (isset($data['years']) && count($data['years']) > 0) {
                $sql .= ' AND YEAR(date) IN (' . implode(', ', array_map(function ($year) {
                    return get_instance()->db->escape_str($year);
                }, $data['years'])) . ')';
            } else {
                $sql .= ' AND YEAR(date) = ' . date('Y');
            }
            $sql .= $where;
            $sql .= ') as "' . $office_status . '",';
        }

        $sql     = substr($sql, 0, -1);
        $result  = $this->db->query($sql)->result_array();
        $_result = [];
        $i       = 1;
        foreach ($result as $key => $val) {
            foreach ($val as $status => $total) {
                $_result[$i]['total']         = $total;
                $_result[$i]['symbol']        = $currency->symbol;
                $_result[$i]['currency_name'] = $currency->name;
                $_result[$i]['status']        = $status;
                $i++;
            }
        }
        $_result['currencyid'] = $currencyid;

        return $_result;
    }

    /**
     * Insert new office to database
     * @param array $data invoiec data
     * @return mixed - false if not insert, office ID if succes
     */
    public function add($data)
    {
        $affectedRows = 0;

        $data['datecreated'] = date('Y-m-d H:i:s');

        $data['addedfrom'] = get_staff_user_id();

        $data['prefix'] = get_option('office_prefix');

        $data['number_format'] = get_option('office_number_format');

        $save_and_send = isset($data['save_and_send']);


        $data['hash'] = app_generate_hash();
        $tags         = isset($data['tags']) ? $data['tags'] : '';

        $items = [];
        if (isset($data['newitems'])) {
            $items = $data['newitems'];
            unset($data['newitems']);
        }

        $data = $this->map_shipping_columns($data);

        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);

        if (isset($data['shipping_street'])) {
            $data['shipping_street'] = trim($data['shipping_street']);
            $data['shipping_street'] = nl2br($data['shipping_street']);
        }

        $hook = hooks()->apply_filters('before_office_added', [
            'data'  => $data,
            'items' => $items,
        ]);

        $data  = $hook['data'];
        $items = $hook['items'];

        unset($data['tags']);
        unset($data['allowed_payment_modes']);
        unset($data['save_as_draft']);
        unset($data['schedule_id']);
        unset($data['duedate']);

        try {
            $this->db->insert(db_prefix() . 'offices', $data);
        } catch (Exception $e) {
            $message = $e->getMessage();
            log_activity('Insert ERROR ' . $message);
        }

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // Update next office number in settings
            $this->db->where('name', 'next_office_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update(db_prefix() . 'options');

            handle_tags_save($tags, $insert_id, 'office');

            foreach ($items as $key => $item) {
                if ($new_item_added = add_new_office_item_post($item, $insert_id, 'office')) {
                    $affectedRows++;
                }
            }

            hooks()->do_action('after_office_added', $insert_id);

            if ($save_and_send === true) {
                $this->send_office_to_client($insert_id, '', true, '', true);
            }

            return $insert_id;
        }

        return false;
    }


    /**
     * Update office data
     * @param array $data office data
     * @param mixed $id officeid
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows = 0;

        $data['number'] = trim($data['number']);

        $original_office = $this->get($id);

        $original_status = $original_office->status;

        $original_number = $original_office->number;

        $original_number_formatted = format_office_number($id);

        $save_and_send = isset($data['save_and_send']);

        $items = [];
        if (isset($data['items'])) {
            $items = $data['items'];
            unset($data['items']);
        }

        $newitems = [];
        if (isset($data['newitems'])) {
            $newitems = $data['newitems'];
            unset($data['newitems']);
        }

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'office')) {
                $affectedRows++;
            }
        }

        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);

        $data['shipping_street'] = trim($data['shipping_street']);
        $data['shipping_street'] = nl2br($data['shipping_street']);

        $data = $this->map_shipping_columns($data);

        $hook = hooks()->apply_filters('before_office_updated', [
            'data'             => $data,
            'items'            => $items,
            'newitems'         => $newitems,
            'removed_items'    => isset($data['removed_items']) ? $data['removed_items'] : [],
        ], $id);

        $data                  = $hook['data'];
        $items                 = $hook['items'];
        $newitems              = $hook['newitems'];
        $data['removed_items'] = $hook['removed_items'];

        // Delete items checked to be removed from database
        foreach ($data['removed_items'] as $remove_item_id) {
            $original_item = $this->get_office_item($remove_item_id);
            if (handle_removed_office_item_post($remove_item_id, 'office')) {
                $affectedRows++;
                $this->log_office_activity($id, 'office_activity_removed_item', false, serialize([
                    $original_item->description,
                ]));
            }
        }

        unset($data['removed_items']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'offices', $data);

        if ($this->db->affected_rows() > 0) {
            // Check for status change
            if ($original_status != $data['status']) {
                $this->log_office_activity($original_office->id, 'not_office_status_updated', false, serialize([
                    '<original_status>' . $original_status . '</original_status>',
                    '<new_status>' . $data['status'] . '</new_status>',
                ]));
                if ($data['status'] == 2) {
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'offices', ['sent' => 1, 'datesend' => date('Y-m-d H:i:s')]);
                }
            }
            if ($original_number != $data['number']) {
                $this->log_office_activity($original_office->id, 'office_activity_number_changed', false, serialize([
                    $original_number_formatted,
                    format_office_number($original_office->id),
                ]));
            }
            $affectedRows++;
        }

        foreach ($items as $key => $item) {
            $original_item = $this->get_office_item($item['itemid']);

            if (update_office_item_post($item['itemid'], $item, 'item_order')) {
                $affectedRows++;
            }

            if (update_office_item_post($item['itemid'], $item, 'unit')) {
                $affectedRows++;
            }


            if (update_office_item_post($item['itemid'], $item, 'qty')) {
                $this->log_office_activity($id, 'office_activity_updated_qty_item', false, serialize([
                    $item['description'],
                    $original_item->qty,
                    $item['qty'],
                ]));
                $affectedRows++;
            }

            if (update_office_item_post($item['itemid'], $item, 'description')) {
                $this->log_office_activity($id, 'office_activity_updated_item_short_description', false, serialize([
                    $original_item->description,
                    $item['description'],
                ]));
                $affectedRows++;
            }

            if (update_office_item_post($item['itemid'], $item, 'long_description')) {
                $this->log_office_activity($id, 'office_activity_updated_item_long_description', false, serialize([
                    $original_item->long_description,
                    $item['long_description'],
                ]));
                $affectedRows++;
            }

        }

        foreach ($newitems as $key => $item) {
            if ($new_item_added = add_new_office_item_post($item, $id, 'office')) {
                $affectedRows++;
            }
        }

        if ($save_and_send === true) {
            $this->send_office_to_client($id, '', true, '', true);
        }

        if ($affectedRows > 0) {
            hooks()->do_action('after_office_updated', $id);
            return true;
        }

        return false;
    }

    public function mark_action_status($action, $id, $client = false)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'offices', [
            'status' => $action,
            'signed' => ($action == 4) ? 1 : 0,
        ]);

        $notifiedUsers = [];

        if ($this->db->affected_rows() > 0) {
            $office = $this->get($id);
            if ($client == true) {
                $this->db->where('staffid', $office->addedfrom);
                $this->db->or_where('staffid', $office->assigned);
                $staff_office = $this->db->get(db_prefix() . 'staff')->result_array();

                $invoiceid = false;
                $invoiced  = false;

                $contact_id = !is_client_logged_in()
                    ? get_primary_contact_user_id($office->clientid)
                    : get_contact_user_id();

                if ($action == 4) {
                    $this->log_office_activity($id, 'office_activity_client_accepted', true);

                    // Send thank you email to all contacts with permission offices
                    $contacts = $this->clients_model->get_contacts($office->clientid, ['active' => 1, 'project_emails' => 1]);

                    foreach ($contacts as $contact) {
                        // (To fix merge field) send_mail_template('office_accepted_to_customer','offices', $office, $contact);
                    }

                    foreach ($staff_office as $member) {
                        $notified = add_notification([
                            'fromcompany'     => true,
                            'touserid'        => $member['staffid'],
                            'description'     => 'office_customer_accepted',
                            'link'            => 'offices/office/' . $id,
                            'additional_data' => serialize([
                                format_office_number($office->id),
                            ]),
                        ]);

                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }

                        // (To fix merge field) send_mail_template('office_accepted_to_staff','offices', $office, $member['email'], $contact_id);
                    }

                    pusher_trigger_notification($notifiedUsers);
                    hooks()->do_action('office_accepted', $office);

                    return true;
                } elseif ($action == 3) {
                    foreach ($staff_office as $member) {
                        $notified = add_notification([
                            'fromcompany'     => true,
                            'touserid'        => $member['staffid'],
                            'description'     => 'office_customer_declined',
                            'link'            => 'offices/office/' . $id,
                            'additional_data' => serialize([
                                format_office_number($office->id),
                            ]),
                        ]);

                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }
                        // Send staff email notification that customer declined office
                        // (To fix merge field) send_mail_template('office_declined_to_staff', 'offices',$office, $member['email'], $contact_id);
                    }
                    pusher_trigger_notification($notifiedUsers);
                    $this->log_office_activity($id, 'office_activity_client_declined', true);
                    hooks()->do_action('office_declined', $office);

                    return true;
                }
            } else {
                if ($action == 2) {
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'offices', ['sent' => 1, 'datesend' => date('Y-m-d H:i:s')]);

                    $this->db->where('active', 1);
                    $staff_office = $this->db->get(db_prefix() . 'staff')->result_array();
                    $contacts = $this->clients_model->get_contacts($office->clientid, ['active' => 1, 'project_emails' => 1]);

                        foreach ($staff_office as $member) {
                            $notified = add_notification([
                                'fromcompany'     => true,
                                'touserid'        => $member['staffid'],
                                'description'     => 'office_send_to_customer_already_sent',
                                'link'            => 'offices/office/' . $id,
                                'additional_data' => serialize([
                                    format_office_number($office->id),
                                ]),
                            ]);

                            if ($notified) {
                                array_push($notifiedUsers, $member['staffid']);
                            }
                            // Send staff email notification that customer declined office
                            // (To fix merge field) send_mail_template('office_declined_to_staff', 'offices',$office, $member['email'], $contact_id);
                        }

                    // Admin marked office
                    $this->log_office_activity($id, 'office_activity_marked', false, serialize([
                        '<status>' . $action . '</status>',
                    ]));
                    pusher_trigger_notification($notifiedUsers);
                    hooks()->do_action('office_send_to_customer_already_sent', $office);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get office attachments
     * @param mixed $office_id
     * @param string $id attachment id
     * @return mixed
     */
    public function get_attachments($office_id, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $office_id);
        }
        $this->db->where('rel_type', 'office');
        $result = $this->db->get(db_prefix() . 'files');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    /**
     *  Delete office attachment
     * @param mixed $id attachmentid
     * @return  boolean
     */
    public function delete_attachment($id)
    {
        $attachment = $this->get_attachments('', $id);
        $deleted    = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('office') . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_office_activity('Office Attachment Deleted [OfficeID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_by_type('office') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('office') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('office') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * Delete office items and all connections
     * @param mixed $id officeid
     * @return boolean
     */
    public function delete($id, $simpleDelete = false)
    {
        if (get_option('delete_only_on_last_office') == 1 && $simpleDelete == false) {
            if (!is_last_office($id)) {
                return false;
            }
        }
        $office = $this->get($id);
        /*
        if (!is_null($office->invoiceid) && $simpleDelete == false) {
            return [
                'is_invoiced_office_delete_error' => true,
            ];
        }
        */
        hooks()->do_action('before_office_deleted', $id);

        $number = format_office_number($id);

        $this->clear_signature($id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'offices');

        if ($this->db->affected_rows() > 0) {
            if (!is_null($office->short_link)) {
                app_archive_short_link($office->short_link);
            }

            if (get_option('office_number_decrement_on_delete') == 1 && $simpleDelete == false) {
                $current_next_office_number = get_option('next_office_number');
                if ($current_next_office_number > 1) {
                    // Decrement next office number to
                    $this->db->where('name', 'next_office_number');
                    $this->db->set('value', 'value-1', false);
                    $this->db->update(db_prefix() . 'options');
                }
            }

            delete_tracked_emails($id, 'office');

            // Delete the items values

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'office');
            $this->db->delete(db_prefix() . 'notes');

            $this->db->where('rel_type', 'office');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'views_tracking');

            $this->db->where('rel_type', 'office');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'taggables');

            $this->db->where('rel_type', 'office');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'reminders');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'office');
            $this->db->delete(db_prefix() . 'office_items');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'office');
            $this->db->delete(db_prefix() . 'item_tax');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'office');
            $this->db->delete(db_prefix() . 'office_activity');

            // Delete the items values
            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'office');
            $this->db->delete(db_prefix() . 'itemable');


            $attachments = $this->get_attachments($id);
            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'office');
            $this->db->delete('scheduled_emails');

            // Get related tasks
            $this->db->where('rel_type', 'office');
            $this->db->where('rel_id', $id);
            $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id']);
            }
            if ($simpleDelete == false) {
                $this->log_office_activity('Offices Deleted [Number: ' . $number . ']');
            }

            return true;
        }

        return false;
    }

    /**
     * Set office to sent when email is successfuly sended to client
     * @param mixed $id officeid
     */
    public function set_office_sent($id, $emails_sent = [])
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'offices', [
            'sent'     => 1,
            'datesend' => date('Y-m-d H:i:s'),
        ]);

        $this->log_office_activity($id, 'office_activity_sent_to_client', false, serialize([
            '<custom_data>' . implode(', ', $emails_sent) . '</custom_data>',
        ]));

        // Update office status to sent
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'offices', [
            'status' => 2,
        ]);

        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'office');
        $this->db->delete('officed_emails');
    }

    /**
     * Send expiration reminder to customer
     * @param mixed $id office id
     * @return boolean
     */
    public function send_expiry_reminder($id)
    {
        $office        = $this->get($id);
        $office_number = format_office_number($office->id);
        set_mailing_constant();
        $pdf              = office_pdf($office);
        $attach           = $pdf->Output($office_number . '.pdf', 'S');
        $emails_sent      = [];
        $sms_sent         = false;
        $sms_reminder_log = [];

        // For all cases update this to prevent sending multiple reminders eq on fail
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'offices', [
            'is_expiry_notified' => 1,
        ]);

        $contacts = $this->clients_model->get_contacts($office->clientid, ['active' => 1, 'project_emails' => 1]);

        foreach ($contacts as $contact) {
            $template = mail_template('office_expiration_reminder', $office, $contact);

            $merge_fields = $template->get_merge_fields();

            $template->add_attachment([
                'attachment' => $attach,
                'filename'   => str_replace('/', '-', $office_number . '.pdf'),
                'type'       => 'application/pdf',
            ]);

            if ($template->send()) {
                array_push($emails_sent, $contact['email']);
            }

            if (can_send_sms_based_on_creation_date($office->datecreated)
                && $this->app_sms->trigger(SMS_TRIGGER_OFFICE_EXP_REMINDER, $contact['phonenumber'], $merge_fields)) {
                $sms_sent = true;
                array_push($sms_reminder_log, $contact['firstname'] . ' (' . $contact['phonenumber'] . ')');
            }
        }

        if (count($emails_sent) > 0 || $sms_sent) {
            if (count($emails_sent) > 0) {
                $this->log_office_activity($id, 'not_expiry_reminder_sent', false, serialize([
                    '<custom_data>' . implode(', ', $emails_sent) . '</custom_data>',
                ]));
            }

            if ($sms_sent) {
                $this->log_office_activity($id, 'sms_reminder_sent_to', false, serialize([
                    implode(', ', $sms_reminder_log),
                ]));
            }

            return true;
        }

        return false;
    }

    /**
     * Send office to client
     * @param mixed $id officeid
     * @param string $template email template to sent
     * @param boolean $attachpdf attach office pdf or not
     * @return boolean
     */
    public function send_office_to_client($id, $template_name = '', $attachpdf = true, $cc = '', $manually = false)
    {
        $office = $this->get($id);

        if ($template_name == '') {
            $template_name = $office->sent == 0 ?
                'office_send_to_customer' :
                'office_send_to_customer_already_sent';
        }

        $office_number = format_office_number($office->id);

        $emails_sent = [];
        $send_to     = [];

        // Manually is used when sending the office via add/edit area button Save & Send
        if (!DEFINED('CRON') && $manually === false) {
            $send_to = $this->input->post('sent_to');
        } elseif (isset($GLOBALS['officed_email_contacts'])) {
            $send_to = $GLOBALS['officed_email_contacts'];
        } else {
            $contacts = $this->clients_model->get_contacts(
                $office->clientid,
                ['active' => 1, 'project_emails' => 1]
            );

            foreach ($contacts as $contact) {
                array_push($send_to, $contact['id']);
            }
        }

        $status_auto_updated = false;
        $status_now          = $office->status;

        if (is_array($send_to) && count($send_to) > 0) {
            $i = 0;

            // Auto update status to sent in case when user sends the office is with status draft
            if ($status_now == 1) {
                $this->db->where('id', $office->id);
                $this->db->update(db_prefix() . 'offices', [
                    'status' => 2,
                ]);
                $status_auto_updated = true;
            }

            if ($attachpdf) {
                $_pdf_office = $this->get($office->id);
                set_mailing_constant();
                $pdf = office_pdf($_pdf_office);

                $attach = $pdf->Output($office_number . '.pdf', 'S');
            }

            foreach ($send_to as $contact_id) {
                if ($contact_id != '') {
                    // Send cc only for the first contact
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }

                    $contact = $this->clients_model->get_contact($contact_id);

                    if (!$contact) {
                        continue;
                    }

                    $template = mail_template($template_name, $office, $contact, $cc);

                    if ($attachpdf) {
                        $hook = hooks()->apply_filters('send_office_to_customer_file_name', [
                            'file_name' => str_replace('/', '-', $office_number . '.pdf'),
                            'office'  => $_pdf_office,
                        ]);

                        $template->add_attachment([
                            'attachment' => $attach,
                            'filename'   => $hook['file_name'],
                            'type'       => 'application/pdf',
                        ]);
                    }

                    if ($template->send()) {
                        array_push($emails_sent, $contact->email);
                    }
                }
                $i++;
            }
        } else {
            return false;
        }

        if (count($emails_sent) > 0) {
            $this->set_office_sent($id, $emails_sent);
            hooks()->do_action('office_sent', $id);

            return true;
        }

        if ($status_auto_updated) {
            // Office not send to customer but the status was previously updated to sent now we need to revert back to draft
            $this->db->where('id', $office->id);
            $this->db->update(db_prefix() . 'offices', [
                'status' => 1,
            ]);
        }

        return false;
    }

    /**
     * All office activity
     * @param mixed $id officeid
     * @return array
     */
    public function get_office_activity($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'office');
        $this->db->order_by('date', 'desc');

        return $this->db->get(db_prefix() . 'office_activity')->result_array();
    }

    /**
     * Log office activity to database
     * @param mixed $id officeid
     * @param string $description activity description
     */
    public function log_office_activity($id, $description = '', $client = false, $additional_data = '')
    {
        $staffid   = get_staff_user_id();
        $full_name = get_staff_full_name(get_staff_user_id());
        if (DEFINED('CRON')) {
            $staffid   = '[CRON]';
            $full_name = '[CRON]';
        } elseif ($client == true) {
            $staffid   = null;
            $full_name = '';
        }

        $this->db->insert(db_prefix() . 'office_activity', [
            'description'     => $description,
            'date'            => date('Y-m-d H:i:s'),
            'rel_id'          => $id,
            'rel_type'        => 'office',
            'staffid'         => $staffid,
            'full_name'       => $full_name,
            'additional_data' => $additional_data,
        ]);
    }

    /**
     * Updates pipeline order when drag and drop
     * @param mixe $data $_POST data
     * @return void
     */
    public function update_pipeline($data)
    {
        $this->mark_action_status($data['status'], $data['officeid']);
        AbstractKanban::updateOrder($data['order'], 'pipeline_order', 'offices', $data['status']);
    }

    /**
     * Get office unique year for filtering
     * @return array
     */
    public function get_offices_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'offices ORDER BY year DESC')->result_array();
    }

    private function map_shipping_columns($data)
    {
        if (!isset($data['include_shipping'])) {
            foreach ($this->shipping_fields as $_s_field) {
                if (isset($data[$_s_field])) {
                    $data[$_s_field] = null;
                }
            }
            $data['show_shipping_on_office'] = 1;
            $data['include_shipping']          = 0;
        } else {
            $data['include_shipping'] = 1;
            // set by default for the next time to be checked
            if (isset($data['show_shipping_on_office']) && ($data['show_shipping_on_office'] == 1 || $data['show_shipping_on_office'] == 'on')) {
                $data['show_shipping_on_office'] = 1;
            } else {
                $data['show_shipping_on_office'] = 0;
            }
        }

        return $data;
    }
/*
    public function do_kanban_query($status, $search = '', $page = 1, $sort = [], $count = false)
    {
        _deprecated_function('Offices_model::do_kanban_query', '2.9.2', 'OfficesPipeline class');

        $kanBan = (new OfficesPipeline($status))
            ->search($search)
            ->page($page)
            ->sortBy($sort['sort'] ?? null, $sort['sort_by'] ?? null);

        if ($count) {
            return $kanBan->countAll();
        }

        return $kanBan->get();
    }

*/
/*   
    public function get_office_members($id, $with_name = false)
    {
        if ($with_name) {
            $this->db->select('firstname,lastname,email,office_id,staff_id');
        } else {
            $this->db->select('email,office_id,staff_id');
        }
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid=' . db_prefix() . 'office_members.staff_id');
        $this->db->where('office_id', $id);

        return $this->db->get(db_prefix() . 'office_members')->result_array();
    }

*/
    /**
     * Update canban office status when drag and drop
     * @param  array $data office data
     * @return boolean
     */
    public function update_office_status($data)
    {
        $this->db->select('status');
        $this->db->where('id', $data['officeid']);
        $_old = $this->db->get(db_prefix() . 'offices')->row();

        $old_status = '';

        if ($_old) {
            $old_status = format_office_status($_old->status);
        }

        $affectedRows   = 0;
        $current_status = format_office_status($data['status']);


        $this->db->where('id', $data['officeid']);
        $this->db->update(db_prefix() . 'offices', [
            'status' => $data['status'],
        ]);

        $_log_message = '';

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            if ($current_status != $old_status && $old_status != '') {
                $_log_message    = 'not_office_activity_status_updated';
                $additional_data = serialize([
                    get_staff_full_name(),
                    $old_status,
                    $current_status,
                ]);

                hooks()->do_action('office_status_changed', [
                    'office_id'    => $data['officeid'],
                    'old_status' => $old_status,
                    'new_status' => $current_status,
                ]);
            }
            $this->db->where('id', $data['officeid']);
            $this->db->update(db_prefix() . 'offices', [
                'last_status_change' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($affectedRows > 0) {
            if ($_log_message == '') {
                return true;
            }
            $this->log_office_activity($data['officeid'], $_log_message, false, $additional_data);

            return true;
        }

        return false;
    }


    /**
     * Get the offices about to expired in the given days
     *
     * @param  integer|null $staffId
     * @param  integer $days
     *
     * @return array
     */
    public function get_offices_this_week($staffId = null, $days = 7)
    {
        $diff1 = date('Y-m-d', strtotime('-' . $days . ' days'));
        $diff2 = date('Y-m-d', strtotime('+' . $days . ' days'));

        if ($staffId && ! staff_can('view', 'offices', $staffId)) {
            $this->db->where(db_prefix() . 'offices.addedfrom', $staffId);
        }

        $this->db->select(db_prefix() . 'offices.id,' . db_prefix() . 'offices.number,' . db_prefix() . 'clients.userid,' . db_prefix() . 'clients.company,' . db_prefix() . 'projects.id AS project_id,' . db_prefix() . 'projects.name,' . db_prefix() . 'offices.date');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'offices.clientid', 'left');
        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.id = ' . db_prefix() . 'offices.project_id', 'left');
        $this->db->where('date IS NOT NULL');
        $this->db->where('date >=', $diff1);
        $this->db->where('date <=', $diff2);

        return $this->db->get(db_prefix() . 'offices')->result_array();
    }

    /**
     * Get the offices for the client given
     *
     * @param  integer|null $staffId
     * @param  integer $days
     *
     * @return array
     */
    public function get_client_offices($client = null)
    {
        /*
        if ($staffId && ! staff_can('view', 'offices', $staffId)) {
            $this->db->where('addedfrom', $staffId);
        }
        */

        $this->db->select(db_prefix() . 'offices.id,' . db_prefix() . 'offices.number,' . db_prefix() . 'offices.status,' . db_prefix() . 'clients.userid,' . db_prefix() . 'offices.hash,' . db_prefix() . 'projects.name,' . db_prefix() . 'offices.date');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'offices.clientid', 'left');
        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.id = ' . db_prefix() . 'offices.project_id', 'left');
        $this->db->where('date IS NOT NULL');
        $this->db->where(db_prefix() . 'offices.status > ',1);
        $this->db->where(db_prefix() . 'offices.clientid =', $client->userid);

        return $this->db->get(db_prefix() . 'offices')->result_array();
    }


    /**
     * Get the offices about to expired in the given days
     *
     * @param  integer|null $staffId
     * @param  integer $days
     *
     * @return array
     */
    public function get_offices_between($staffId = null, $days = 7)
    {
        $diff1 = date('Y-m-d', strtotime('-' . $days . ' days'));
        $diff2 = date('Y-m-d', strtotime('+' . $days . ' days'));

        if ($staffId && ! staff_can('view', 'offices', $staffId)) {
            $this->db->where('addedfrom', $staffId);
        }

        $this->db->select(db_prefix() . 'offices.id,' . db_prefix() . 'offices.number,' . db_prefix() . 'clients.userid,' . db_prefix() . 'clients.company,' . db_prefix() . 'projects.name,' . db_prefix() . 'offices.date');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'offices.clientid', 'left');
        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.id = ' . db_prefix() . 'offices.project_id', 'left');
        $this->db->where('expirydate IS NOT NULL');
        $this->db->where('expirydate >=', $diff1);
        $this->db->where('expirydate <=', $diff2);

        return $this->db->get_compiled_select(db_prefix() . 'offices');
//        return $this->db->get(db_prefix() . 'offices')->get_compiled_select();
//        return $this->db->get(db_prefix() . 'offices')->result_array();
    }


}
