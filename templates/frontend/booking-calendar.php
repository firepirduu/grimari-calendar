<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="plandok-frontend">
    <!-- Header -->
    <div class="plandok-header">
        <div class="plandok-header-content">
            <div>
                <h2><?php _e('Book Your Appointment', 'plandok-booking'); ?></h2>
                <p class="booking-subtitle"><?php _e('Select a date and service to view available time slots', 'plandok-booking'); ?></p>
            </div>
            <div>
                <a href="#" class="staff-login-btn"><?php _e('Staff Login', 'plandok-booking'); ?></a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="plandok-calendar-content">
        <div class="calendar-header">
            <div class="calendar-title">
                <h1><?php _e('Available Appointments', 'plandok-booking'); ?></h1>
                <p><?php _e('Choose your preferred date and time from the calendar below', 'plandok-booking'); ?></p>
            </div>
            <div class="calendar-controls">
                <div class="view-toggle">
                    <button class="view-btn active" data-view="month"><?php _e('Month', 'plandok-booking'); ?></button>
                    <button class="view-btn" data-view="week"><?php _e('Week', 'plandok-booking'); ?></button>
                </div>
                <div class="month-navigation">
                    <button class="nav-btn" id="prev-month">‹</button>
                    <span class="current-month" id="current-month"></span>
                    <button class="nav-btn" id="next-month">›</button>
                </div>
                <button class="today-btn" id="today-btn"><?php _e('Today', 'plandok-booking'); ?></button>
            </div>
        </div>

        <div class="calendar-layout">
            <!-- Calendar Grid -->
            <div class="calendar-grid-container">
                <div id="calendar-grid">
                    <!-- Calendar will be populated by JavaScript -->
                </div>
            </div>

            <!-- Sidebar -->
            <div class="calendar-sidebar">
                <!-- Default message when no service is selected -->
                <div id="default-message" class="default-message-card">
                    <div class="calendar-icon">📅</div>
                    <h3><?php _e('Select a Service', 'plandok-booking'); ?></h3>
                    <p><?php _e('Click on an available service slot to view details and book your appointment.', 'plandok-booking'); ?></p>
                </div>

                <!-- Service details when a service is selected -->
                <div id="service-details" class="service-details-card" style="display: none;">
                    <!-- Content will be populated by JavaScript -->
                </div>

                <!-- How to book information -->
                <div class="how-to-book-card">
                    <h3><?php _e('How to Book', 'plandok-booking'); ?></h3>
                    <div class="booking-steps">
                        <div class="step">
                            <span class="step-number">1</span>
                            <span><?php _e('Select your preferred date and service from the calendar', 'plandok-booking'); ?></span>
                        </div>
                        <div class="step">
                            <span class="step-number">2</span>
                            <span><?php _e('Click "Apply for This Appointment" to open the booking form', 'plandok-booking'); ?></span>
                        </div>
                        <div class="step">
                            <span class="step-number">3</span>
                            <span><?php _e('Fill in your contact details and submit your application', 'plandok-booking'); ?></span>
                        </div>
                        <div class="step">
                            <span class="step-number">4</span>
                            <span><?php _e('We will contact you to confirm your appointment', 'plandok-booking'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Available Services List -->
                <?php if (!empty($services)): ?>
                <div class="services-list-card">
                    <h3><?php _e('Available Services', 'plandok-booking'); ?></h3>
                    <div class="services-list">
                        <?php foreach ($services as $service): ?>
                        <div class="service-item">
                            <div class="service-name"><?php echo esc_html($service->name); ?></div>
                            <div class="service-details">
                                <span class="service-duration">⏱️ <?php echo esc_html($service->duration); ?></span>
                                <?php if ($atts['show_price'] === 'true'): ?>
                                <span class="service-price">💰 €<?php echo esc_html(number_format($service->price, 2)); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($service->description)): ?>
                            <div class="service-description"><?php echo esc_html($service->description); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
    <div id="booking-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php _e('Book Your Appointment', 'plandok-booking'); ?></h3>
                <button type="button" class="close-btn" id="close-modal">×</button>
            </div>

            <!-- Selected Service Info -->
            <div class="selected-service">
                <div id="selected-service-info">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>

            <!-- Booking Form -->
            <form id="booking-form" class="booking-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name"><?php _e('First Name', 'plandok-booking'); ?> *</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name"><?php _e('Last Name', 'plandok-booking'); ?> *</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone"><?php _e('Phone Number', 'plandok-booking'); ?> *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="email"><?php _e('Email Address', 'plandok-booking'); ?> *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes"><?php _e('Additional Notes', 'plandok-booking'); ?> <span class="optional">(<?php _e('optional', 'plandok-booking'); ?>)</span></label>
                    <textarea id="notes" name="notes" placeholder="<?php _e('Any special requests or information we should know...', 'plandok-booking'); ?>"></textarea>
                </div>

                <!-- Hidden fields -->
                <input type="hidden" id="service_id" name="service_id">
                <input type="hidden" id="preferred_date" name="preferred_date">
                <input type="hidden" id="preferred_time" name="preferred_time">

                <div class="form-actions">
                    <button type="button" class="cancel-btn" id="cancel-booking"><?php _e('Cancel', 'plandok-booking'); ?></button>
                    <button type="submit" class="submit-btn"><?php _e('Submit Application', 'plandok-booking'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.services-list-card {
    background: #fff;
    border-radius: 10px;
    padding: 35px;
    box-shadow: 0 3px 10px rgba(19, 49, 109, 0.1);
    margin-bottom: 24px;
}

.services-list-card h3 {
    font-size: 18px;
    font-weight: 700;
    color: rgba(0, 0, 0, 0.88);
    margin-bottom: 16px;
}

.service-item {
    padding: 16px;
    border: 1px solid #d9d9d9;
    border-radius: 5px;
    margin-bottom: 12px;
    transition: all 0.2s;
}

.service-item:hover {
    border-color: rgb(19, 49, 109);
    box-shadow: 0 2px 5px rgba(19, 49, 109, 0.1);
}

.service-name {
    font-weight: 600;
    color: rgba(0, 0, 0, 0.88);
    margin-bottom: 8px;
}

.service-details {
    display: flex;
    gap: 16px;
    font-size: 14px;
    color: rgba(0, 0, 0, 0.45);
    margin-bottom: 8px;
}

.service-description {
    font-size: 14px;
    color: rgba(0, 0, 0, 0.65);
    line-height: 1.4;
}

.modal-overlay {
    z-index: 10000;
}
</style>
