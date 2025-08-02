<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap plandok-admin">
    <div class="plandok-header">
        <h1><?php _e('Calendar Management', 'plandok-booking'); ?></h1>
    </div>

    <div class="plandok-calendar">
        <div class="plandok-calendar-header">
            <div class="plandok-calendar-nav">
                <button type="button" class="plandok-btn calendar-nav-prev">‹</button>
                <span id="calendar-month"><?php echo date('F Y'); ?></span>
                <button type="button" class="plandok-btn calendar-nav-next">›</button>
                <button type="button" class="plandok-btn plandok-btn-secondary calendar-today">
                    <?php _e('Today', 'plandok-booking'); ?>
                </button>
            </div>
            <div>
                <button type="button" class="plandok-btn" onclick="openNewEventModal()">
                    <?php _e('Add New Event', 'plandok-booking'); ?>
                </button>
            </div>
        </div>

        <div class="plandok-calendar-grid" id="calendar-grid">
            <!-- Calendar days headers -->
            <div class="plandok-calendar-day-header"><?php _e('Sun', 'plandok-booking'); ?></div>
            <div class="plandok-calendar-day-header"><?php _e('Mon', 'plandok-booking'); ?></div>
            <div class="plandok-calendar-day-header"><?php _e('Tue', 'plandok-booking'); ?></div>
            <div class="plandok-calendar-day-header"><?php _e('Wed', 'plandok-booking'); ?></div>
            <div class="plandok-calendar-day-header"><?php _e('Thu', 'plandok-booking'); ?></div>
            <div class="plandok-calendar-day-header"><?php _e('Fri', 'plandok-booking'); ?></div>
            <div class="plandok-calendar-day-header"><?php _e('Sat', 'plandok-booking'); ?></div>

            <!-- Calendar days will be populated by JavaScript -->
            <?php for ($i = 1; $i <= 42; $i++): ?>
            <div class="plandok-calendar-day" data-day="<?php echo $i; ?>">
                <div class="day-number"></div>
                <div class="day-events"></div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- New Event Modal -->
    <div id="new-event-modal" style="display: none;">
        <div class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><?php _e('Add New Event', 'plandok-booking'); ?></h3>
                    <button type="button" class="close-btn" onclick="closeNewEventModal()">×</button>
                </div>
                <form id="appointment-form" class="plandok-form">
                    <div class="plandok-form-row">
                        <div class="plandok-form-group">
                            <label for="service_id"><?php _e('Service', 'plandok-booking'); ?></label>
                            <select id="service_id" name="service_id" required>
                                <option value=""><?php _e('Select Service', 'plandok-booking'); ?></option>
                                <?php foreach ($services as $service): ?>
                                <option value="<?php echo esc_attr($service->id); ?>">
                                    <?php echo esc_html($service->name); ?> - <?php echo esc_html($service->duration); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="plandok-form-group">
                            <label for="staff_id"><?php _e('Staff Member', 'plandok-booking'); ?></label>
                            <select id="staff_id" name="staff_id" required>
                                <option value=""><?php _e('Select Staff', 'plandok-booking'); ?></option>
                                <?php foreach ($staff as $member): ?>
                                <option value="<?php echo esc_attr($member->id); ?>">
                                    <?php echo esc_html($member->name); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="plandok-form-row">
                        <div class="plandok-form-group">
                            <label for="appointment_date"><?php _e('Date', 'plandok-booking'); ?></label>
                            <input type="date" id="appointment_date" name="appointment_date" required>
                        </div>
                        <div class="plandok-form-group">
                            <label for="appointment_time"><?php _e('Time', 'plandok-booking'); ?></label>
                            <input type="time" id="appointment_time" name="appointment_time" class="time-picker" required>
                        </div>
                    </div>

                    <div class="plandok-form-group">
                        <label for="client_name"><?php _e('Client Name', 'plandok-booking'); ?></label>
                        <input type="text" id="client_name" name="client_name" placeholder="<?php _e('Leave empty for blocked time', 'plandok-booking'); ?>">
                    </div>

                    <div class="plandok-form-row">
                        <div class="plandok-form-group">
                            <label for="client_email"><?php _e('Client Email', 'plandok-booking'); ?></label>
                            <input type="email" id="client_email" name="client_email">
                        </div>
                        <div class="plandok-form-group">
                            <label for="client_phone"><?php _e('Client Phone', 'plandok-booking'); ?></label>
                            <input type="tel" id="client_phone" name="client_phone">
                        </div>
                    </div>

                    <div class="plandok-form-row">
                        <div class="plandok-form-group">
                            <label for="status"><?php _e('Status', 'plandok-booking'); ?></label>
                            <select id="status" name="status">
                                <option value="confirmed"><?php _e('Confirmed', 'plandok-booking'); ?></option>
                                <option value="pending"><?php _e('Pending', 'plandok-booking'); ?></option>
                                <option value="cancelled"><?php _e('Cancelled', 'plandok-booking'); ?></option>
                                <option value="blocked"><?php _e('Blocked Time', 'plandok-booking'); ?></option>
                            </select>
                        </div>
                        <div class="plandok-form-group">
                            <label for="location"><?php _e('Location', 'plandok-booking'); ?></label>
                            <input type="text" id="location" name="location" placeholder="<?php _e('Optional', 'plandok-booking'); ?>">
                        </div>
                    </div>

                    <div class="plandok-form-group">
                        <label for="notes"><?php _e('Notes', 'plandok-booking'); ?></label>
                        <textarea id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="plandok-btn plandok-btn-secondary" onclick="closeNewEventModal()">
                            <?php _e('Cancel', 'plandok-booking'); ?>
                        </button>
                        <button type="submit" class="plandok-btn">
                            <?php _e('Save Event', 'plandok-booking'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openNewEventModal() {
    document.getElementById('new-event-modal').style.display = 'block';
}

function closeNewEventModal() {
    document.getElementById('new-event-modal').style.display = 'none';
    document.getElementById('appointment-form').reset();
}
</script>
