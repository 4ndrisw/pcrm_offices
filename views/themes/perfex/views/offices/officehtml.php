<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="mtop15 preview-top-wrapper">
   <div class="row">
      <div class="col-md-3">
         <div class="mbot30">
            <div class="office-html-logo">
               <?php echo get_dark_company_logo(); ?>
            </div>
         </div>
      </div>
      <div class="clearfix"></div>
   </div>
   <div class="top" data-sticky data-sticky-class="preview-sticky-header">
      <div class="container preview-sticky-container">
         <div class="row">
            <div class="col-md-12">
               <div class="pull-left">
                  <h3 class="bold no-mtop office-html-number no-mbot">
                     <span class="sticky-visible hide">
                     <?php echo format_office_number($office->id); ?>
                     </span>
                  </h3>
                  <h4 class="office-html-status mtop7">
                     <?php echo format_office_status($office->status,'',true); ?>
                  </h4>
               </div>
               <div class="visible-xs">
                  <div class="clearfix"></div>
               </div>
               <?php
                  // Is not accepted, declined and expired
                  if ($office->status != 4 && $office->status != 3 && $office->status != 5) {
                    $can_be_accepted = true;
                    if($identity_confirmation_enabled == '0'){
                      echo form_open($this->uri->uri_string(), array('class'=>'pull-right mtop7 action-button'));
                      echo form_hidden('office_action', 4);
                      echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_office').'</button>';
                      echo form_close();
                    } else {
                      echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_office').'</button>';
                    }
                  } else if($office->status == 3){
                      $can_be_accepted = true;
                      if($identity_confirmation_enabled == '0'){
                        echo form_open($this->uri->uri_string(),array('class'=>'pull-right mtop7 action-button'));
                        echo form_hidden('office_action', 4);
                        echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_office').'</button>';
                        echo form_close();
                      } else {
                        echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_office').'</button>';
                      }
                  }
                  // Is not accepted, declined and expired
                  if ($office->status != 4 && $office->status != 3 && $office->status != 5) {
                    echo form_open($this->uri->uri_string(), array('class'=>'pull-right action-button mright5 mtop7'));
                    echo form_hidden('office_action', 3);
                    echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-default action-button accept"><i class="fa fa-remove"></i> '._l('clients_decline_office').'</button>';
                    echo form_close();
                  }
                  ?>
               <?php echo form_open(site_url('offices/pdf/'.$office->id), array('class'=>'pull-right action-button')); ?>
               <button type="submit" name="officepdf" class="btn btn-default action-button download mright5 mtop7" value="officepdf">
               <i class="fa fa-file-pdf-o"></i>
               <?php echo _l('clients_html_btn_download'); ?>
               </button>
               <?php echo form_close(); ?>
               <?php if(is_client_logged_in() && has_contact_permission('offices')){ ?>
               <a href="<?php echo site_url('offices/list'); ?>" class="btn btn-default pull-right mright5 mtop7 action-button go-to-portal">
               <?php echo _l('client_go_to_dashboard'); ?>
               </a>
               <?php } ?>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="clearfix"></div>
<div class="panel_s mtop20">
   <div class="panel-body">
      <div class="col-md-10 col-md-offset-1">
         <div class="row mtop20">
            <div class="col-md-6 col-sm-6 transaction-html-info-col-left">
               <h4 class="bold office-html-number"><?php echo format_office_number($office->id); ?></h4>
               <address class="office-html-company-info">
                  <?php echo format_organization_info(); ?>
               </address>
            </div>
            <div class="col-sm-6 text-right transaction-html-info-col-right">
               <span class="bold office_to"><?php echo _l('office_to'); ?>:</span>
               <address class="office-html-customer-billing-info">
                  <?php echo format_customer_info($office, 'office', 'billing'); ?>
               </address>
               <!-- shipping details -->
               <?php if($office->include_shipping == 1 && $office->show_shipping_on_office == 1){ ?>
               <span class="bold office_ship_to"><?php echo _l('ship_to'); ?>:</span>
               <address class="office-html-customer-shipping-info">
                  <?php echo format_customer_info($office, 'office', 'shipping'); ?>
               </address>
               <?php } ?>
            </div>
         </div>
         <div class="row">
            <div class="col-md-6">
               <div class="container-fluid">

               </div>
            </div>
            <div class="col-md-6 text-right">
               <p class="no-mbot office-html-date">
                  <span class="bold">
                  <?php echo _l('office_data_date'); ?>:
                  </span>
                  <?php echo _d($office->date); ?>
               </p>
               <?php if(!empty($office->reference_no)){ ?>
               <p class="no-mbot office-html-reference-no">
                  <span class="bold"><?php echo _l('reference_no'); ?>:</span>
                  <?php echo $office->reference_no; ?>
               </p>
               <?php } ?>
               <?php if($office->project_id != 0 && get_option('show_project_on_office') == 1){ ?>
               <p class="no-mbot office-html-project">
                  <span class="bold"><?php echo _l('project'); ?>:</span>
                  <?php echo get_project_name_by_id($office->project_id); ?>
               </p>
               <?php } ?>
               <?php $pdf_custom_fields = get_custom_fields('office',array('show_on_pdf'=>1,'show_on_client_portal'=>1));
                  foreach($pdf_custom_fields as $field){
                    $value = get_custom_field_value($office->id,$field['id'],'office');
                    if($value == ''){continue;} ?>
               <p class="no-mbot">
                  <span class="bold"><?php echo $field['name']; ?>: </span>
                  <?php echo $value; ?>
               </p>
               <?php } ?>
            </div>
         </div>
         <div class="row">
         
            <?php 
               $status = format_office_status($office->status,'',true);
               echo _l('task_mark_as',$status, '. and .'); 
            ?>
         </div>
         

         <div class="row">
            <div class="col-md-12">
               <div class="table-responsive">
                  <?php
                     $items = get_office_items_table_data($office, 'office');
                     echo $items->table();
                  ?>
               </div>
            </div>


            <div class="row mtop25">
               <div class="col-md-12">
                  <div class="col-md-6 text-center">
                     <div class="bold"><?php echo get_option('invoice_company_name'); ?></div>
                     <div class="qrcode text-center">
                        <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path(get_office_upload_path('office').$office->id.'/assigned-'.$office_number.'.png')); ?>" class="img-responsive center-block office-assigned" alt="office-<?= $office->id ?>">
                     </div>
                     <div class="assigned">
                     <?php if($office->assigned != 0 && get_option('show_assigned_on_offices') == 1){ ?>
                        <?php echo get_staff_full_name($office->assigned); ?>
                     <?php } ?>

                     </div>
                  </div>
                     <div class="col-md-6 text-center">
                       <div class="bold"><?php echo $client_company; ?></div>
                       <?php if(!empty($office->signature)) { ?>
                           <div class="bold">
                              <p class="no-mbot"><?php echo _l('office_signed_by') . ": {$office->acceptance_firstname} {$office->acceptance_lastname}"?></p>
                              <p class="no-mbot"><?php echo _l('office_signed_date') . ': ' . _dt($office->acceptance_date) ?></p>
                              <p class="no-mbot"><?php echo _l('office_signed_ip') . ": {$office->acceptance_ip}"?></p>
                           </div>
                           <p class="bold"><?php echo _l('document_customer_signature_text'); ?>
                           <?php if($office->signed == 1 && has_permission('offices','','delete')){ ?>
                              <a href="<?php echo admin_url('offices/clear_signature/'.$office->id); ?>" data-toggle="tooltip" title="<?php echo _l('clear_signature'); ?>" class="_delete text-danger">
                                 <i class="fa fa-remove"></i>
                              </a>
                           <?php } ?>
                           </p>
                           <div class="customer_signature text-center">
                              <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path(get_office_upload_path('office').$office->id.'/'.$office->signature)); ?>" class="img-responsive center-block office-signature" alt="office-<?= $office->id ?>">
                           </div>
                       <?php } ?>
                     </div>
               </div>
            </div>


            <?php if(!empty($office->clientnote)){ ?>
            <div class="col-md-12 office-html-note">
            <hr />
               <b><?php echo _l('office_order'); ?></b><br /><?php echo $office->clientnote; ?>
            </div>
            <?php } ?>
            <?php if(!empty($office->terms)){ ?>
            <div class="col-md-12 office-html-terms-and-conditions">
               <b><?php echo _l('terms_and_conditions'); ?>:</b><br /><?php echo $office->terms; ?>
            </div>
            <?php } ?>

         </div>
      </div>
   </div>
</div>
<?php
   if($identity_confirmation_enabled == '1' && $can_be_accepted){
    get_template_part('identity_confirmation_form',array('formData'=>form_hidden('office_action',4)));
   }
   ?>
<script>
   $(function(){
     new Sticky('[data-sticky]');
   })
</script>
