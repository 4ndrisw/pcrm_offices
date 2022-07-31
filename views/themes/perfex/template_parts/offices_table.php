<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<table class="table dt-table table-offices" data-order-col="1" data-order-type="desc">
    <thead>
        <tr>
            <th><?php echo _l('office_number'); ?> #</th>
            <th><?php echo _l('office_list_project'); ?></th>
            <th><?php echo _l('office_list_date'); ?></th>
            <th><?php echo _l('office_list_status'); ?></th>

        </tr>
    </thead>
    <tbody>
        <?php foreach($offices as $office){ ?>
            <tr>
                <td><?php echo '<a href="' . site_url("offices/show/" . $office["id"] . '/' . $office["hash"]) . '">' . format_office_number($office["id"]) . '</a>'; ?></td>
                <td><?php echo $office['name']; ?></td>
                <td><?php echo _d($office['date']); ?></td>
                <td><?php echo format_office_status($office['status']); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
