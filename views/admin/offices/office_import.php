<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content office-add">
      <div class="row">
         <?php
         echo form_open($this->uri->uri_string(),array('id'=>'office-form','class'=>'_transaction_form'));
         if(isset($office)){
            echo form_hidden('isedit');
         }
         ?>
         <div class="col-md-12">
            <?php $this->load->view('admin/offices/office_import_template'); ?>
         </div>
         <?php echo form_close(); ?>
         <?php $this->load->view('admin/invoice_items/item'); ?>
      </div>
   </div>
</div>
</div>
<?php init_tail(); ?>
<script type="text/javascript" src="/modules/offices/assets/js/offices.js"></script>
<script type="text/javascript">
   $(function(){
      validate_office_form();
      // Project ajax search
      init_ajax_project_search_by_customer_id();
      // Maybe items ajax search
       init_ajax_search('items','#item_select.ajax-search',undefined,admin_url+'items/search');
   });
</script>
</body>
</html>
