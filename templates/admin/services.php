<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap plandok-admin">
    <div class="plandok-header">
        <h1><?php _e('Service Management', 'plandok-booking'); ?></h1>
    </div>

    <div class="plandok-card">
        <h3><?php _e('Add New Service', 'plandok-booking'); ?></h3>
        <form id="service-form" class="plandok-form">
            <input type="hidden" id="service_id" name="id" value="">

            <div class="plandok-form-row">
                <div class="plandok-form-group">
                    <label for="name"><?php _e('Service Name', 'plandok-booking'); ?></label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="plandok-form-group">
                    <label for="category"><?php _e('Category', 'plandok-booking'); ?></label>
                    <input type="text" id="category" name="category" required>
                </div>
            </div>

            <div class="plandok-form-row">
                <div class="plandok-form-group">
                    <label for="duration"><?php _e('Duration', 'plandok-booking'); ?></label>
                    <select id="duration" name="duration" class="duration-input" required>
                        <option value="15min">15 minutes</option>
                        <option value="30min">30 minutes</option>
                        <option value="45min">45 minutes</option>
                        <option value="1h">1 hour</option>
                        <option value="1h 15min">1 hour 15 minutes</option>
                        <option value="1h 30min">1 hour 30 minutes</option>
                        <option value="2h">2 hours</option>
                        <option value="2h 30min">2 hours 30 minutes</option>
                        <option value="3h">3 hours</option>
                    </select>
                </div>
                <div class="plandok-form-group">
                    <label for="price"><?php _e('Price (€)', 'plandok-booking'); ?></label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required>
                </div>
            </div>

            <div class="plandok-form-row">
                <div class="plandok-form-group">
                    <label for="extra_time_before"><?php _e('Extra Time Before', 'plandok-booking'); ?></label>
                    <select id="extra_time_before" name="extra_time_before">
                        <option value="0min">No extra time</option>
                        <option value="5min">5 minutes</option>
                        <option value="10min">10 minutes</option>
                        <option value="15min">15 minutes</option>
                        <option value="30min">30 minutes</option>
                    </select>
                </div>
                <div class="plandok-form-group">
                    <label for="extra_time_after"><?php _e('Extra Time After', 'plandok-booking'); ?></label>
                    <select id="extra_time_after" name="extra_time_after">
                        <option value="0min">No extra time</option>
                        <option value="5min">5 minutes</option>
                        <option value="10min">10 minutes</option>
                        <option value="15min">15 minutes</option>
                        <option value="30min">30 minutes</option>
                    </select>
                </div>
            </div>

            <div class="plandok-form-group">
                <label for="description"><?php _e('Description', 'plandok-booking'); ?></label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>

            <div class="plandok-form-group">
                <label for="staff_ids"><?php _e('Assigned Staff', 'plandok-booking'); ?></label>
                <select id="staff_ids" name="staff_ids[]" multiple>
                    <?php foreach ($staff as $member): ?>
                    <option value="<?php echo esc_attr($member->id); ?>">
                        <?php echo esc_html($member->name); ?> - <?php echo esc_html($member->position); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <small><?php _e('Hold Ctrl/Cmd to select multiple staff members', 'plandok-booking'); ?></small>
            </div>

            <div class="plandok-form-group">
                <label>
                    <input type="checkbox" id="available_online" name="available_online" checked>
                    <?php _e('Available for Online Booking', 'plandok-booking'); ?>
                </label>
            </div>

            <div class="form-actions">
                <button type="button" onclick="resetServiceForm()" class="plandok-btn plandok-btn-secondary">
                    <?php _e('Reset', 'plandok-booking'); ?>
                </button>
                <button type="submit" class="plandok-btn">
                    <?php _e('Save Service', 'plandok-booking'); ?>
                </button>
            </div>
        </form>
    </div>

    <?php if (!empty($services)): ?>
    <div class="plandok-card">
        <h3><?php _e('Existing Services', 'plandok-booking'); ?></h3>
        <table class="plandok-table plandok-data-table">
            <thead>
                <tr>
                    <th><?php _e('Name', 'plandok-booking'); ?></th>
                    <th><?php _e('Category', 'plandok-booking'); ?></th>
                    <th><?php _e('Duration', 'plandok-booking'); ?></th>
                    <th><?php _e('Price', 'plandok-booking'); ?></th>
                    <th><?php _e('Staff', 'plandok-booking'); ?></th>
                    <th><?php _e('Status', 'plandok-booking'); ?></th>
                    <th><?php _e('Actions', 'plandok-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                <tr data-id="<?php echo esc_attr($service->id); ?>">
                    <td>
                        <strong><?php echo esc_html($service->name); ?></strong>
                        <?php if (!empty($service->description)): ?>
                        <br><small><?php echo esc_html(substr($service->description, 0, 50)); ?>...</small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($service->category); ?></td>
                    <td><?php echo esc_html($service->duration); ?></td>
                    <td>€<?php echo esc_html(number_format($service->price, 2)); ?></td>
                    <td>
                        <?php
                        if (!empty($service->staff_ids)) {
                            $staff_ids = explode(',', $service->staff_ids);
                            $staff_names = array();
                            foreach ($staff as $member) {
                                if (in_array($member->id, $staff_ids)) {
                                    $staff_names[] = $member->name;
                                }
                            }
                            echo esc_html(implode(', ', $staff_names));
                        } else {
                            echo __('No staff assigned', 'plandok-booking');
                        }
                        ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo esc_attr($service->status); ?>">
                            <?php echo esc_html(ucfirst($service->status)); ?>
                        </span>
                    </td>
                    <td>
                        <button type="button" class="plandok-btn plandok-btn-small" onclick="editService(<?php echo esc_attr($service->id); ?>)">
                            <?php _e('Edit', 'plandok-booking'); ?>
                        </button>
                        <button type="button" class="plandok-btn plandok-btn-small plandok-btn-danger delete-btn"
                                data-type="service" data-id="<?php echo esc_attr($service->id); ?>">
                            <?php _e('Delete', 'plandok-booking'); ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
function resetServiceForm() {
    document.getElementById('service-form').reset();
    document.getElementById('service_id').value = '';
}

function editService(serviceId) {
    // This would populate the form with service data for editing
    // Implementation would depend on how you want to handle editing
    console.log('Edit service:', serviceId);
}
</script>
