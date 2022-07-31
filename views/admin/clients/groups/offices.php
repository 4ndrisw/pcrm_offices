<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if(isset($client)){ ?>
	<h4 class="customer-profile-group-heading"><?php echo _l('offices'); ?></h4>
	<?php if(has_permission('offices','','create')){ ?>
		<a href="<?php echo admin_url('offices/office?customer_id='.$client->userid); ?>" class="btn btn-info mbot15<?php if($client->active == 0){echo ' disabled';} ?>"><?php echo _l('create_new_office'); ?></a>
	<?php } ?>
	<?php if(has_permission('offices','','view') || has_permission('offices','','view_own') || get_option('allow_staff_view_offices_assigned') == '1'){ ?>
		<a href="#" class="btn btn-info mbot15" data-toggle="modal" data-target="#client_zip_offices"><?php echo _l('zip_offices'); ?></a>
	<?php } ?>
	<div id="offices_total"></div>
	<?php
	$this->load->view('admin/offices/table_html', array('class'=>'offices-single-client'));
	//$this->load->view('admin/clients/modals/zip_offices');
	?>
<?php } ?>
