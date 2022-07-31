<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                     <?php if(has_permission('offices','','create')){ ?>

                     <div class="_buttons">
                        <a href="<?php echo admin_url('offices/create'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_office'); ?></a>
                    </div>
                    <div class="clearfix"></div>
                    <hr class="hr-panel-heading" />
                    <?php } ?>
                    <?php render_datatable(array(
                        _l('office_number'),
                        _l('office_full_name'),
                        _l('office_status'),
                        ),'offices'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript" id="office-js" src="<?= base_url() ?>modules/offices/assets/js/offices.js?"></script>
<script>
    $(function(){
        initDataTable('.table-offices', window.location.href, 'undefined', 'undefined','fnServerParams', [0, 'desc']);
    });
</script>
</body>
</html>
