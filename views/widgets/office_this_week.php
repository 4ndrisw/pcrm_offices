<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
    $CI = &get_instance();
    $CI->load->model('offices/offices_model');
    $offices = $CI->offices_model->get_offices_this_week(get_staff_user_id());
?>

<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('office_this_week'); ?>">
    <?php if(staff_can('view', 'offices') || staff_can('view_own', 'offices')) { ?>
    <div class="panel_s offices-expiring">
        <div class="panel-body padding-10">
            <p class="padding-5"><?php echo _l('office_this_week'); ?></p>
            <hr class="hr-panel-heading-dashboard">
            <?php if (!empty($offices)) { ?>
                <div class="table-vertical-scroll">
                    <a href="<?php echo admin_url('offices'); ?>" class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <table id="widget-<?php echo create_widget_id(); ?>" class="table dt-table" data-order-col="3" data-order-type="desc">
                        <thead>
                            <tr>
                                <th><?php echo _l('office_number'); ?> #</th>
                                <th class="<?php echo (isset($client) ? 'not_visible' : ''); ?>"><?php echo _l('office_list_client'); ?></th>
                                <th><?php echo _l('office_list_project'); ?></th>
                                <th><?php echo _l('office_list_date'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($offices as $office) { ?>
                                <tr>
                                    <td>
                                        <?php echo '<a href="' . admin_url("offices/office/" . $office["id"]) . '">' . format_office_number($office["id"]) . '</a>'; ?>
                                    </td>
                                    <td>
                                        <?php echo '<a href="' . admin_url("clients/client/" . $office["userid"]) . '">' . $office["company"] . '</a>'; ?>
                                    </td>
                                    <td>
                                        <?php echo '<a href="' . admin_url("projects/view/" . $office["project_id"]) . '">' . $office["company"] . '</a>'; ?>
                                        <?php //echo $office['name']; ?>
                                    </td>
                                    <td>
                                        <?php echo _d($office['date']); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="text-center padding-5">
                    <i class="fa fa-check fa-5x" aria-hidden="true"></i>
                    <h4><?php echo _l('no_office_this_week',["7"]) ; ?> </h4>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>
