<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content office-update">
		<div class="row">
			<?php
			echo form_open($this->uri->uri_string(),array('id'=>'office-form','class'=>'_transaction_form'));
			if(isset($office)){
				echo form_hidden('isedit');
			}
			?>
			<div class="col-md-12">
				<?php $this->load->view('admin/offices/office_template'); ?>
			</div>
			<?php echo form_close(); ?>
			<?php $this->load->view('admin/invoice_items/item'); ?>
		</div>
	</div>
</div>
</div>
<?php init_tail(); ?>

<script type="text/javascript" src="/modules/offices/assets/js/offices.js?<?=strtotime('now')?>"></script>

<script>
	$(function(){
		validate_office_form();

	});
</script>
</body>
</html>
