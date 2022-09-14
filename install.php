<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'offices')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "offices` (
      `id` int(11) NOT NULL,
      `staff_id` int(11) NOT NULL DEFAULT 0,
      `sent` tinyint(1) NOT NULL DEFAULT 0,
      `datesend` datetime DEFAULT NULL,
      `short_name` varchar(30) NOT NULL,
      `full_name` varchar(100) NOT NULL,
      `kepala_dinas_id` int(11) NOT NULL DEFAULT 0,
      `kepala_upt_id` int(11) NOT NULL DEFAULT 0,
      `number` int(11) NOT NULL DEFAULT 0,
      `prefix` varchar(50) DEFAULT NULL,
      `number_format` int(11) NOT NULL DEFAULT 0,
      `formatted_number` varchar(20) DEFAULT NULL,
      `hash` varchar(32) DEFAULT NULL,
      `datecreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `date` date DEFAULT NULL,
      `addedfrom` int(11) NOT NULL DEFAULT 0,
      `status` int(11) NOT NULL DEFAULT 1,



      `clientid` int(11) NOT NULL DEFAULT 0,
      `deleted_customer_name` varchar(100) DEFAULT NULL,
      `project_id` int(11) NOT NULL DEFAULT 0,
      `clientnote` text DEFAULT NULL,
      `adminnote` text DEFAULT NULL,
      `invoiceid` int(11) DEFAULT NULL,
      `invoiced_date` datetime DEFAULT NULL,
      `terms` text DEFAULT NULL,
      `reference_no` varchar(100) DEFAULT NULL,
      `assigned` int(11) NOT NULL DEFAULT 0,
      `billing_street` varchar(200) DEFAULT NULL,
      `billing_city` varchar(100) DEFAULT NULL,
      `billing_state` varchar(100) DEFAULT NULL,
      `billing_zip` varchar(100) DEFAULT NULL,
      `billing_country` int(11) DEFAULT NULL,
      `shipping_street` varchar(200) DEFAULT NULL,
      `shipping_city` varchar(100) DEFAULT NULL,
      `shipping_state` varchar(100) DEFAULT NULL,
      `shipping_zip` varchar(100) DEFAULT NULL,
      `shipping_country` int(11) DEFAULT NULL,
      `include_shipping` tinyint(1) NOT NULL DEFAULT 0,
      `show_shipping_on_office` tinyint(1) NOT NULL DEFAULT 1,
      `show_quantity_as` int(11) NOT NULL DEFAULT 1,
      `pipeline_order` int(11) DEFAULT 1,
      `is_expiry_notified` int(11) NOT NULL DEFAULT 0,
      `signed` tinyint(1) NOT NULL DEFAULT 0,
      `acceptance_firstname` varchar(50) DEFAULT NULL,
      `acceptance_lastname` varchar(50) DEFAULT NULL,
      `acceptance_email` varchar(100) DEFAULT NULL,
      `acceptance_date` datetime DEFAULT NULL,
      `acceptance_ip` varchar(40) DEFAULT NULL,
      `signature` varchar(40) DEFAULT NULL,
      `short_link` varchar(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'offices`
      ADD PRIMARY KEY (`id`),
      ADD UNIQUE( `number`),
      ADD KEY `signed` (`signed`),
      ADD KEY `status` (`status`),
      ADD KEY `clientid` (`clientid`),
      ADD KEY `project_id` (`project_id`),
      ADD KEY `date` (`date`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'offices`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}


if (!$CI->db->table_exists(db_prefix() . 'office_members')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "office_members` (
      `id` int(11) NOT NULL,
      `office_id` int(11) NOT NULL DEFAULT 0,
      `staff_id` int(11) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'office_members`
      ADD PRIMARY KEY (`id`),
      ADD KEY `staff_id` (`staff_id`),
      ADD KEY `office_id` (`office_id`) USING BTREE;');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'office_members`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'office_activity')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "office_activity` (
  `id` int(11) NOT NULL,
  `rel_type` varchar(20) DEFAULT NULL,
  `rel_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `additional_data` text DEFAULT NULL,
  `staffid` varchar(11) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `date` datetime NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'office_activity`
        ADD PRIMARY KEY (`id`),
        ADD KEY `date` (`date`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'office_activity`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'office_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "office_items` (
      `id` int(11) NOT NULL,
      `rel_id` int(11) NOT NULL,
      `rel_type` varchar(15) NOT NULL,
      `description` mediumtext NOT NULL,
      `long_description` mediumtext DEFAULT NULL,
      `qty` decimal(15,2) NOT NULL,
      `unit` varchar(40) DEFAULT NULL,
      `item_order` int(11) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'office_items`
      ADD PRIMARY KEY (`id`),
      ADD KEY `rel_id` (`rel_id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'office_items`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

$CI->db->query("
INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('office', 'office-send-to-client', 'english', 'Send office to Customer', 'office # {office_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">Please find the attached office <strong># {office_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>office status:</strong> {office_status}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the office on the following link: <a href=\"{office_link}\">{office_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">We look forward to your communication.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}<br /></span>', '{companyname} | CRM', '', 0, 1, 0),
('office', 'office-already-send', 'english', 'office Already Sent to Customer', 'office # {office_number} ', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank you for your office request.</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the office on the following link: <a href=\"{office_link}\">{office_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('office', 'office-declined-to-staff', 'english', 'office Declined (Sent to Staff)', 'Customer Declined office', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) declined office with number <strong># {office_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the office on the following link: <a href=\"{office_link}\">{office_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('office', 'office-accepted-to-staff', 'english', 'office Accepted (Sent to Staff)', 'Customer Accepted office', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted office with number <strong># {office_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the office on the following link: <a href=\"{office_link}\">{office_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('office', 'office-thank-you-to-customer', 'english', 'Thank You Email (Sent to Customer After Accept)', 'Thank for you accepting office', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank for for accepting the office.</span><br /> <br /><span style=\"font-size: 12pt;\">We look forward to doing business with you.</span><br /> <br /><span style=\"font-size: 12pt;\">We will contact you as soon as possible.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('office', 'office-expiry-reminder', 'english', 'office Expiration Reminder', 'office Expiration Reminder', '<p><span style=\"font-size: 12pt;\">Hello {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">The office with <strong># {office_number}</strong> will expire on <strong>{office_expirydate}</strong></span><br /><br /><span style=\"font-size: 12pt;\">You can view the office on the following link: <a href=\"{office_link}\">{office_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span></p>', '{companyname} | CRM', '', 0, 1, 0),
('office', 'office-send-to-client', 'english', 'Send office to Customer', 'office # {office_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">Please find the attached office <strong># {office_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>office status:</strong> {office_status}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the office on the following link: <a href=\"{office_link}\">{office_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">We look forward to your communication.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}<br /></span>', '{companyname} | CRM', '', 0, 1, 0),
('office', 'office-already-send', 'english', 'office Already Sent to Customer', 'office # {office_number} ', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank you for your office request.</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the office on the following link: <a href=\"{office_link}\">{office_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('office', 'office-declined-to-staff', 'english', 'office Declined (Sent to Staff)', 'Customer Declined office', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) declined office with number <strong># {office_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the office on the following link: <a href=\"{office_link}\">{office_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('office', 'office-accepted-to-staff', 'english', 'office Accepted (Sent to Staff)', 'Customer Accepted office', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted office with number <strong># {office_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the office on the following link: <a href=\"{office_link}\">{office_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('office', 'staff-added-as-project-member', 'english', 'Staff Added as Project Member', 'New project assigned to you', '<p>Hi <br /><br />New office has been assigned to you.<br /><br />You can view the office on the following link <a href=\"{office_link}\">office__number</a><br /><br />{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0),
('office', 'office-accepted-to-staff', 'english', 'office Accepted (Sent to Staff)', 'Customer Accepted office', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted office with number <strong># {office_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the office on the following link: <a href=\"{office_link}\">{office_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0);
");
/*
 *
 */

// Add options for offices
add_option('delete_only_on_last_office', 1);
add_option('office_prefix', 'UPT-');
add_option('next_office_number', 1);
add_option('default_office_assigned', 9);
add_option('office_number_decrement_on_delete', 0);
add_option('show_offices_clients_area_menu_items', 0);
add_option('office_number_format', 4);
add_option('office_year', date('Y'));
add_option('exclude_office_from_client_area_with_draft_status', 1);
add_option('predefined_clientnote_office', '');
add_option('predefined_terms_office', '- Dokumen ini diterbitkan melalui Aplikasi CRM, tidak memerlukan tanda tangan basah dari PT. Cipta Mas Jaya.');
add_option('office_due_after', 1);
add_option('allow_staff_view_offices_assigned', 1);
add_option('show_assigned_on_offices', 1);
add_option('require_client_logged_in_to_view_office', 0);

add_option('show_project_on_office', 1);
add_option('offices_pipeline_limit', 1);
add_option('default_offices_pipeline_sort', 1);
add_option('office_accept_identity_confirmation', 1);
add_option('office_qrcode_size', '160');


/*

DROP TABLE `tbloffices`;
DROP TABLE `tbloffice_activity`, `tbloffice_items`, `tbloffice_members`;
delete FROM `tbloptions` WHERE `name` LIKE '%office%';
DELETE FROM `tblemailtemplates` WHERE `type` LIKE 'office';



*/
