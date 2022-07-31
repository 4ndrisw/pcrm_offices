<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="panel_s">
    <div class="panel-body">
     <?php if(has_permission('offices','','create')){ ?>
     <div class="_buttons">
        <a href="<?php echo admin_url('offices/create'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_office'); ?></a>
     </div>
     <?php } ?>
     <?php if(has_permission('offices','','create')){ ?>
     <div class="_buttons">
        <a href="<?php echo admin_url('offices'); ?>" class="btn btn-primary pull-right display-block"><?php echo _l('offices'); ?></a>
     </div>
     <?php } ?>
     <div class="clearfix"></div>
     <hr class="hr-panel-heading" />
     <div class="table-responsive">
        <?php render_datatable(array(
            _l('office_number'),
            _l('office_company'),
            _l('office_start_date'),
            ),'offices'); ?>
     </div>
    </div>
</div>
