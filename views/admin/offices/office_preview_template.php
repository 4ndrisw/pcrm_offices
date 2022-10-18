<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="col-md-12 no-padding">
   <div class="panel_s">
      <div class="panel-body">
         <div class="horizontal-scrollable-tabs preview-tabs-top">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
               <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                  <li role="presentation" class="active">
                     <a href="#tab_office" aria-controls="tab_office" role="tab" data-toggle="tab">
                     <?php echo _l('office'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_activity" aria-controls="tab_activity" role="tab" data-toggle="tab">
                     <?php echo _l('office_view_activity_tooltip'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_reminders" onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $office->id ;?> + '/' + 'office', undefined, undefined, undefined,[1,'asc']); return false;" aria-controls="tab_reminders" role="tab" data-toggle="tab">
                     <?php echo _l('office_reminders'); ?>
                     <?php
                        $total_reminders = total_rows(db_prefix().'reminders',
                          array(
                           'isnotified'=>0,
                           'staff'=>get_staff_user_id(),
                           'rel_type'=>'office',
                           'rel_id'=>$office->id
                           )
                          );
                        if($total_reminders > 0){
                          echo '<span class="badge">'.$total_reminders.'</span>';
                        }
                        ?>
                     </a>
                  </li>
                  <li role="presentation" class="tab-separator">
                     <a href="#tab_notes" onclick="get_sales_notes(<?php echo $office->id; ?>,'offices'); return false" aria-controls="tab_notes" role="tab" data-toggle="tab">
                     <?php echo _l('office_notes'); ?>
                     <span class="notes-total">
                        <?php if($totalNotes > 0){ ?>
                           <span class="badge"><?php echo $totalNotes; ?></span>
                        <?php } ?>
                     </span>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" title="<?php echo _l('emails_tracking'); ?>" class="tab-separator">
                     <a href="#tab_emails_tracking" aria-controls="tab_emails_tracking" role="tab" data-toggle="tab">
                     <?php if(!is_mobile()){ ?>
                     <i class="fa fa-envelope-open-o" aria-hidden="true"></i>
                     <?php } else { ?>
                     <?php echo _l('emails_tracking'); ?>
                     <?php } ?>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('view_tracking'); ?>" class="tab-separator">
                     <a href="#tab_views" aria-controls="tab_views" role="tab" data-toggle="tab">
                     <?php if(!is_mobile()){ ?>
                     <i class="fa fa-eye"></i>
                     <?php } else { ?>
                     <?php echo _l('view_tracking'); ?>
                     <?php } ?>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="tab-separator toggle_view">
                     <a href="#" onclick="small_table_full_view(); return false;">
                     <i class="fa fa-expand"></i></a>
                  </li>
               </ul>
            </div>
         </div>
         <div class="row mtop10">
            <div class="col-md-3">
               <?php echo format_office_status($office->status,'mtop5');  ?>
            </div>
            <div class="col-md-9">
               <div class="visible-xs">
                  <div class="mtop10"></div>
               </div>
               <div class="pull-right _buttons">
                  <?php if((staff_can('edit', 'offices') && $office->status != 4) || is_admin()){ ?>
                     <a href="<?php echo admin_url('offices/update/'.$office->id); ?>" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('edit_office_tooltip'); ?>" data-placement="bottom"><i class="fa fa-pencil-square-o"></i></a>

                  <?php } ?>
                  <div class="btn-group">
                     <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf-o"></i><?php if(is_mobile()){echo ' PDF';} ?> <span class="caret"></span></a>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <li class="hidden-xs"><a href="<?php echo site_url('offices/pdf/'.$office->id.'?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                        <li class="hidden-xs"><a href="<?php echo site_url('offices/pdf/'.$office->id.'?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                        <li><a href="<?php echo site_url('offices/pdf/'.$office->id); ?>"><?php echo _l('download'); ?></a></li>
                        <li>
                           <a href="<?php echo site_url('offices/pdf/'.$office->id.'?print=true'); ?>" target="_blank">
                           <?php echo _l('print'); ?>
                           </a>
                        </li>
                     </ul>
                  </div>
                  <?php
                     $_tooltip = _l('office_sent_to_email_tooltip');
                     $_tooltip_already_send = '';
                     if($office->sent == 1){
                        $_tooltip_already_send = _l('office_already_send_to_client_tooltip', time_ago($office->datesend));
                     }
                     ?>
                  <?php if(!empty($office->clientid)){ ?>
                  <a href="#" class="office-send-to-client btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo $_tooltip; ?>" data-placement="bottom"><span data-toggle="tooltip" data-title="<?php echo $_tooltip_already_send; ?>"><i class="fa fa-envelope"></i></span></a>
                  <?php } ?>
                  <div class="btn-group">
                     <button type="button" class="btn btn-default pull-left dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo _l('more'); ?> <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <li>
                           <a href="<?php echo site_url('offices/show/' . $office->id . '/' .  $office->hash) ?>" target="_blank">
                           <?php echo _l('view_office_as_client'); ?>
                           </a>
                        </li>
                        <?php hooks()->do_action('after_office_view_as_client_link', $office); ?>
                        <?php if((!empty($office->expirydate) && date('Y-m-d') < $office->expirydate && ($office->status == 2 || $office->status == 5)) && is_offices_expiry_reminders_enabled()){ ?>
                        <li>
                           <a href="<?php echo admin_url('offices/send_expiry_reminder/'.$office->id); ?>">
                           <?php echo _l('send_expiry_reminder'); ?>
                           </a>
                        </li>
                        <?php } ?>
                        <li>
                           <a href="#" data-toggle="modal" data-target="#sales_attach_file"><?php echo _l('attach_file'); ?></a>
                        </li>
                        <?php if($office->invoiceid == NULL){
                           if(staff_can('edit', 'offices')){
                             foreach($office_statuses as $status){
                               if($office->status != $status){ ?>
                                 <li>
                                    <a href="<?php echo admin_url() . 'offices/mark_action_status/'.$status.'/'.$office->id; ?>">
                                    <?php echo _l('office_mark_as',format_office_status($status,'',false)); ?></a>
                                 </li>
                              <?php }
                             }
                             ?>
                           <?php } ?>
                        <?php } ?>
                        <?php if(staff_can('create', 'offices')){ ?>
                        <li>
                           <a href="<?php echo admin_url('offices/copy/'.$office->id); ?>">
                           <?php echo _l('copy_office'); ?>
                           </a>
                        </li>
                        <?php } ?>
                        <?php if(!empty($office->signature) && staff_can('delete', 'offices')){ ?>
                        <li>
                           <a href="<?php echo admin_url('offices/clear_signature/'.$office->id); ?>" class="_delete">
                           <?php echo _l('clear_signature'); ?>
                           </a>
                        </li>
                        <?php } ?>
                        <?php if(staff_can('delete', 'offices')){ ?>
                        <?php
                           if((get_option('delete_only_on_last_office') == 1 && is_last_office($office->id)) || (get_option('delete_only_on_last_office') == 0)){ ?>
                        <li>
                           <a href="<?php echo admin_url('offices/delete/'.$office->id); ?>" class="text-danger delete-text _delete"><?php echo _l('delete_office_tooltip'); ?></a>
                        </li>
                        <?php
                           }
                           }
                           ?>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
         <div class="clearfix"></div>
         <hr class="hr-panel-heading" />
         <div class="tab-content">
            <div role="tabpanel" class="tab-pane ptop10 active" id="tab_office">
               <?php if(isset($office->officed_email) && $office->officed_email) { ?>
                     <div class="alert alert-warning">
                        <?php echo _l('will_be_sent_at', _dt($office->officed_email->officed_at)); ?>
                        <?php if(staff_can('edit', 'offices') || $office->addedfrom == get_staff_user_id()) { ?>
                           <a href="#"
                           onclick="edit_office_officed_email(<?php echo $office->officed_email->id; ?>); return false;">
                           <?php echo _l('edit'); ?>
                        </a>
                     <?php } ?>
                  </div>
               <?php } ?>
               <div id="office-preview">
                  <div class="row">
                     <?php if($office->status == 4 && !empty($office->acceptance_firstname) && !empty($office->acceptance_lastname) && !empty($office->acceptance_email)){ ?>
                     <div class="col-md-12">
                        <div class="alert alert-info mbot15">
                           <?php echo _l('accepted_identity_info',array(
                              _l('office_lowercase'),
                              '<b>'.$office->acceptance_firstname . ' ' . $office->acceptance_lastname . '</b> (<a href="mailto:'.$office->acceptance_email.'">'.$office->acceptance_email.'</a>)',
                              '<b>'. _dt($office->acceptance_date).'</b>',
                              '<b>'.$office->acceptance_ip.'</b>'.(is_admin() ? '&nbsp;<a href="'.admin_url('offices/clear_signature/'.$office->id).'" class="_delete text-muted" data-toggle="tooltip" data-title="'._l('clear_this_information').'"><i class="fa fa-remove"></i></a>' : '')
                              )); ?>
                        </div>
                     </div>
                     <?php } ?>
                     <?php if($office->project_id != 0){ ?>
                     <div class="col-md-12">
                        <h4 class="font-medium mbot15"><?php echo _l('related_to_project',array(
                           _l('office_lowercase'),
                           _l('project_lowercase'),
                           '<a href="'.admin_url('projects/view/'.$office->project_id).'" target="_blank">' . $office->project_data->name . '</a>',
                           )); ?></h4>
                     </div>
                     <?php } ?>
                     <div class="col-md-6 col-sm-6">
                        <h4 class="bold">
                           <?php
                              $tags = get_tags_in($office->id,'office');
                              if(count($tags) > 0){
                                echo '<i class="fa fa-tag" aria-hidden="true" data-toggle="tooltip" data-title="'.html_escape(implode(', ',$tags)).'"></i>';
                              }
                              ?>
                           <a href="<?php echo site_url('offices/show/'.$office->id.'/'.$office->hash); ?>">
                           <span id="office-number">
                           <?php echo format_office_number($office->id); ?>
                           </span>
                           </a>
                        </h4>
                        <address>
                           <?php echo format_organization_info(); ?>
                        </address>
                     </div>
                     <div class="col-sm-6 text-right">
                        <span class="bold"><?php echo _l('office_to'); ?>:</span>
                        <address>
                           <?php echo format_customer_info($office, 'office', 'billing', true); ?>
                        </address>
                        <?php if($office->include_shipping == 1 && $office->show_shipping_on_office == 1){ ?>
                        <span class="bold"><?php echo _l('ship_to'); ?>:</span>
                        <address>
                           <?php echo format_customer_info($office, 'office', 'shipping'); ?>
                        </address>
                        <?php } ?>
                     </div>
                  </div>
                  <div class="row">
                        <div class="col-md-12">

                           <p class="no-mbot">
                              <span class="bold">
                              <?php echo _l('office_short_name'); ?>:
                              </span>
                              <?php echo $office->short_name; ?>
                           </p>

                           <p class="no-mbot">
                              <span class="bold">
                              <?php echo _l('office_full_name'); ?>:
                              </span>
                              <?php echo $office->full_name; ?>
                           </p>

                           <p class="no-mbot">
                              <span class="bold">
                              <?php echo _l('office_kepala_dinas'); ?>:
                              </span>
                              <?php echo $office->kepala_dinas_nama; ?>
                           </p>

                           <p class="no-mbot">
                              <span class="bold">
                              <?php echo _l('office_kepala_upt'); ?>:
                              </span>
                              <?php echo $office->kepala_upt_nama; ?>
                           </p>

                           <p class="no-mbot">
                              <span class="bold">
                              <?php echo _l('office_data_date'); ?>:
                              </span>
                              <?php echo $office->date; ?>
                           </p>

                        </div>
                  </div>

                  <div class="row">

                     <?php if(count($office->attachments) > 0){ ?>
                        <div class="clearfix"></div>
                        <hr />
                        <div class="col-md-12">
                           <p class="bold text-muted"><?php echo _l('office_files'); ?></p>
                        </div>
                        <?php foreach($office->attachments as $attachment){
                           $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                           if(!empty($attachment['external'])){
                             $attachment_url = $attachment['external_link'];
                           }
                           ?>
                           <div class="mbot15 row col-md-12" data-attachment-id="<?php echo $attachment['id']; ?>">
                              <div class="col-md-8">
                                 <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                                 <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a>
                                 <br />
                                 <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
                              </div>
                              <div class="col-md-4 text-right">
                                 <?php if($attachment['visible_to_customer'] == 0){
                                    $icon = 'fa fa-toggle-off';
                                    $tooltip = _l('show_to_customer');
                                    } else {
                                    $icon = 'fa fa-toggle-on';
                                    $tooltip = _l('hide_from_customer');
                                    }
                                    ?>
                                 <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $office->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="<?php echo $icon; ?>" aria-hidden="true"></i></a>
                                 <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                                 <a href="#" class="text-danger" onclick="delete_office_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                                 <?php } ?>
                              </div>
                           </div>
                        <?php } ?>
                     <?php } ?>


                  </div>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_reminders">
               <a href="#" data-toggle="modal" class="btn btn-info" data-target=".reminder-modal-office-<?php echo $office->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('office_set_reminder_title'); ?></a>
               <hr />
               <?php render_datatable(array( _l( 'reminder_description'), _l( 'reminder_date'), _l( 'reminder_staff'), _l( 'reminder_is_notified')), 'reminders'); ?>
               <?php $this->load->view('admin/includes/modals/reminder',array('id'=>$office->id,'name'=>'office','members'=>$members,'reminder_title'=>_l('office_set_reminder_title'))); ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
               <?php
                  $this->load->view('admin/includes/emails_tracking',array(
                     'tracked_emails'=>
                     get_tracked_emails($office->id, 'office'))
                  );
                  ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_notes">
               <?php echo form_open(admin_url('offices/add_note/'.$office->id),array('id'=>'sales-notes','class'=>'office-notes-form')); ?>
               <?php echo render_textarea('description'); ?>
               <div class="text-right">
                  <button type="submit" class="btn btn-info mtop15 mbot15"><?php echo _l('office_add_note'); ?></button>
               </div>
               <?php echo form_close(); ?>
               <hr />
               <div class="panel_s mtop20 no-shadow" id="sales_notes_area">
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_activity">
               <div class="row">
                  <div class="col-md-12">
                     <div class="activity-feed">
                        <?php foreach($activity as $activity){
                           $_custom_data = false;
                           ?>
                        <div class="feed-item" data-sale-activity-id="<?php echo $activity['id']; ?>">
                           <div class="date">
                              <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($activity['date']); ?>">
                              <?php echo time_ago($activity['date']); ?>
                              </span>
                           </div>
                           <div class="text">
                              <?php if(is_numeric($activity['staffid']) && $activity['staffid'] != 0){ ?>
                              <a href="<?php echo admin_url('profile/'.$activity["staffid"]); ?>">
                              <?php echo staff_profile_image($activity['staffid'],array('staff-profile-xs-image pull-left mright5'));
                                 ?>
                              </a>
                              <?php } ?>
                              <?php
                                 $additional_data = '';
                                 if(!empty($activity['additional_data'])){
                                  $additional_data = unserialize($activity['additional_data']);

                                  $i = 0;
                                  foreach($additional_data as $data){
                                    if(strpos($data,'<original_status>') !== false){
                                      $original_status = get_string_between($data, '<original_status>', '</original_status>');
                                      $additional_data[$i] = format_office_status($original_status,'',false);
                                    } else if(strpos($data,'<new_status>') !== false){
                                      $new_status = get_string_between($data, '<new_status>', '</new_status>');
                                      $additional_data[$i] = format_office_status($new_status,'',false);
                                    } else if(strpos($data,'<status>') !== false){
                                      $status = get_string_between($data, '<status>', '</status>');
                                      $additional_data[$i] = format_office_status($status,'',false);
                                    } else if(strpos($data,'<custom_data>') !== false){
                                      $_custom_data = get_string_between($data, '<custom_data>', '</custom_data>');
                                      unset($additional_data[$i]);
                                    }
                                    $i++;
                                  }
                                 }
                                 $_formatted_activity = _l($activity['description'],$additional_data);
                                 if($_custom_data !== false){
                                 $_formatted_activity .= ' - ' .$_custom_data;
                                 }
                                 if(!empty($activity['full_name'])){
                                 $_formatted_activity = $activity['full_name'] . ' - ' . $_formatted_activity;
                                 }
                                 echo $_formatted_activity;
                                 if(is_admin()){
                                 echo '<a href="#" class="pull-right text-danger" onclick="delete_sale_activity('.$activity['id'].'); return false;"><i class="fa fa-remove"></i></a>';
                                 }
                                 ?>
                           </div>
                        </div>
                        <?php } ?>
                     </div>
                  </div>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_views">
               <?php
                  $views_activity = get_views_tracking('office',$office->id);
                  if(count($views_activity) === 0) {
                     echo '<h4 class="no-mbot">'._l('not_viewed_yet',_l('office_lowercase')).'</h4>';
                  }
                  foreach($views_activity as $activity){ ?>
               <p class="text-success no-margin">
                  <?php echo _l('view_date') . ': ' . _dt($activity['date']); ?>
               </p>
               <p class="text-muted">
                  <?php echo _l('view_ip') . ': ' . $activity['view_ip']; ?>
               </p>
               <hr />
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</div>
