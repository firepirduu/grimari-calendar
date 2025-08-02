# Plandok Booking System - WordPress Plugin

A complete appointment booking system for WordPress with calendar management, services, staff, and customer applications.

## Features

### Admin Features

- **Dashboard** - View upcoming appointments and key metrics
- **Calendar Management** - Create, edit, and manage appointments
- **Service Management** - Add and configure services with pricing
- **Staff Management** - Manage team members and their schedules
- **Application Management** - Handle customer booking requests

### Public Features

- **Public Booking Calendar** - Customer-facing appointment booking
- **Service Selection** - Browse available services with pricing
- **Application Forms** - Customer booking request submission
- **Email Notifications** - Automatic confirmations and notifications

### Technical Features

- **Responsive Design** - Works on all devices
- **AJAX Interface** - Smooth user experience
- **Database Integration** - Proper WordPress database handling
- **Security** - WordPress security best practices
- **Shortcode Support** - Easy page integration

## Installation

### Method 1: Upload Plugin

1. Download the plugin files
2. Upload the `plandok-booking` folder to `/wp-content/plugins/`
3. Activate the plugin in WordPress admin
4. Access the admin panel at **WordPress Admin → Plandok**

### Method 2: WordPress Admin Upload

1. Go to **Plugins → Add New → Upload Plugin**
2. Choose the plugin ZIP file
3. Install and activate
4. Access **WordPress Admin → Plandok**

## Usage

### Admin Setup

1. **Configure Services**:

   - Go to **Plandok → Services**
   - Add your services with pricing and duration
   - Assign staff members to services

2. **Add Staff Members**:

   - Go to **Plandok → Staff**
   - Add team members with contact info
   - Set working hours and positions

3. **Manage Calendar**:
   - Go to **Plandok → Calendar**
   - Create appointment slots
   - Use bulk operations for recurring appointments

### Public Booking

1. **Automatic Page Creation**:

   - Plugin creates `/book-appointment` page automatically
   - Customers can book appointments directly

2. **Manual Integration**:

   - Use shortcode `[plandok_booking_calendar]` on any page
   - Customize appearance with CSS

3. **Application Management**:
   - Review customer applications in **Plandok → Applications**
   - Approve, reject, or edit applications
   - Send confirmations to customers

## Shortcodes

### Primary Shortcode

```
[plandok_booking_calendar]
```

### Shortcode Parameters

```
[plandok_booking_calendar view="calendar" show_staff="true" show_price="true"]
```

- `view` - Display mode (calendar/list)
- `show_staff` - Show staff information (true/false)
- `show_price` - Show pricing (true/false)

## Database Tables

The plugin creates 4 custom tables:

- `wp_plandok_services` - Service definitions
- `wp_plandok_staff` - Staff member information
- `wp_plandok_appointments` - Calendar appointments
- `wp_plandok_applications` - Customer booking requests

## Customization

### Styling

Override plugin styles by adding CSS to your theme:

```css
/* Custom booking calendar styles */
.plandok-frontend {
  /* Your custom styles */
}
```

### Hooks and Filters

The plugin provides hooks for customization:

```php
// Modify booking form fields
add_filter('plandok_booking_form_fields', 'custom_form_fields');

// Custom email notifications
add_action('plandok_application_submitted', 'custom_notification');
```

## Requirements

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+

## Support

For support and feature requests, please visit the plugin repository or contact the development team.

## License

GPL v2 or later

## Changelog

### Version 1.0.0

- Initial release
- Complete booking system
- Admin dashboard
- Public calendar interface
- Email notifications
- Responsive design
