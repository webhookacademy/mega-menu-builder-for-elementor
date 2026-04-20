# Mega Menu Builder for Elementor

![WordPress Plugin Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress Compatibility](https://img.shields.io/badge/wordpress-6.0%2B-brightgreen.svg)
![PHP Version](https://img.shields.io/badge/php-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL%20v2%2B-red.svg)

Advanced mega menu builder widget for Elementor with horizontal/vertical layouts, dropdown animations, WooCommerce integration, and mobile-responsive design.

## 🚀 Features

### Multiple Layout Options
- **Horizontal Navigation Bar** - Classic top navigation
- **Vertical Sidebar Menu** - Perfect for category menus
- **Mobile Hamburger Menu** - Responsive mobile navigation
- **Custom Breakpoints** - Control when mobile menu appears

### Dropdown Types
- **Simple List** - Traditional dropdown menus
- **Mega Panels** - Multi-column layouts with images
- **WordPress Menus** - Integrate existing WP menus
- **Recent Posts** - Display latest posts with thumbnails
- **WooCommerce Products** - Showcase products with prices

### Advanced Customization
- Unlimited menu items with icons
- Badge support (NEW, HOT, SALE, etc.)
- Custom colors and typography
- Hover animations (Slide, Fade, Zoom)
- Complete style controls
- Border, shadow, and spacing options

### Mobile Optimized
- Touch-friendly accordion dropdowns
- Custom hamburger icons
- Smooth animations
- Responsive design

### WooCommerce Ready
- Display products in dropdowns
- Show prices and thumbnails
- Filter by featured products
- Sort by date, popularity, rating

### Security Features
- Input sanitization and validation
- Output escaping
- Nonce verification
- Capability checks
- XSS and CSRF protection
- Prepared SQL statements

## 📋 Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- Elementor 3.0.0 or higher

## 💾 Installation

### From WordPress Admin

1. Download the plugin ZIP file
2. Go to **Plugins > Add New** in WordPress admin
3. Click **Upload Plugin** and choose the ZIP file
4. Click **Install Now** and then **Activate**

### Manual Installation

1. Upload the `mega-menu-builder-for-elementor` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Make sure Elementor is installed and activated

### Using Composer

```bash
composer require yourusername/mega-menu-builder-for-elementor
```

## 🎯 Usage

### Method 1: Using Pre-Designed Templates (Recommended)

1. Go to **WordPress Admin > Mega Menu**
2. Browse the pre-designed templates
3. Click **Import Now** on your preferred template
4. Template will be saved to your site
5. Edit any page with Elementor
6. Add the **Mega Menu** widget to your page
7. In the **Template** section, select your imported template
8. Click **Apply Template** button
9. Menu items will load automatically
10. Customize as needed in **Menu Items** and **Style** sections
11. Publish your page!

### Method 2: Building from Scratch

1. Edit any page with Elementor
2. Search for "Mega Menu" in the widgets panel
3. Drag and drop the widget onto your page
4. Configure your menu items in the **Menu Items** section
5. Customize the appearance in the **Style** tab
6. Publish your page!

### Using Shortcodes

You can also use the shortcode to display templates anywhere in WordPress:

```
[mmb_menu id="simple-menu"]
[mmb_menu id="ecommerce-menu"]
[mmb_menu id="restaurant-menu"]
```

**Note:** Shortcodes use the original template files, not imported templates. For customizable menus, use the widget method above.

**Shortcode Parameters:**
- `id` (required) - The template ID from the template files

**Where to use:**
- WordPress Classic Editor
- Text widgets
- Page builders
- Theme template files (using `do_shortcode()`)

**Example in PHP:**
```php
<?php echo do_shortcode('[mmb_menu id="simple-menu"]'); ?>
```

### Creating a Mega Menu

1. Add the Mega Menu widget to your page
2. In **Menu Items**, click **Add Item**
3. Set the label and link
4. Choose **Dropdown Type** as "Mega Panel"
5. Add columns and links in **Mega Columns**
6. Optionally add an image and promo bar
7. Style your menu in the **Style** tab

### Adding WooCommerce Products

1. Add a menu item
2. Set **Dropdown Type** to "WooCommerce Products"
3. Configure number of products and columns
4. Choose sorting option (Latest, Popular, Top Rated, Random)
5. Toggle featured products filter if needed
6. Customize product display in Style tab

### Displaying Recent Posts

1. Add a menu item
2. Set **Dropdown Type** to "Recent Posts"
3. Select post type
4. Set number of posts and columns
5. Toggle thumbnail and date display
6. Style the posts panel

## 🎨 Customization

### Style Controls

- **Nav Bar** - Background, padding, border, shadow
- **Menu Items** - Typography, colors, spacing, hover effects
- **Dropdown Panel** - Background, border, shadow, padding
- **Sub-menu Links** - Typography, colors, hover states
- **Posts/Products** - Image size, title style, spacing
- **Mega Panel Links** - Icon size, image size, alignment
- **Dropdown Indicator** - Size, color, rotation
- **Hamburger Button** - Size, color, background

### Layout Options

- Horizontal or vertical orientation
- Menu alignment (left, center, right)
- Dropdown alignment
- Trigger type (hover or click)
- Animation style (slide, fade, zoom)
- Mobile breakpoint
- Sidebar width (vertical layout)
- Dropdown width

## 🔒 Security

This plugin follows WordPress security best practices:

- All inputs are sanitized using WordPress functions
- All outputs are escaped properly
- SQL queries use prepared statements
- Nonce verification for AJAX requests
- Capability checks for admin functions
- XSS and CSRF protection
- Regular security audits

## 🐛 Bug Reports

If you find a bug, please create an issue on [GitHub](https://github.com/yourusername/mega-menu-builder-for-elementor/issues) with:

- WordPress version
- PHP version
- Elementor version
- Steps to reproduce
- Expected behavior
- Actual behavior
- Screenshots (if applicable)

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 Changelog

### 1.0.0 - 2026-04-20

**Initial Release**

- Horizontal and vertical menu layouts
- Multiple dropdown types (Simple, Mega, WP Menu, Posts, Products)
- Mobile responsive with hamburger menu
- Complete style customization
- WooCommerce integration
- Posts integration
- Badge support
- Icon support
- Hover animations
- Accessibility features (ARIA labels, keyboard navigation)
- Security hardening

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2026 Your Name

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 👨‍💻 Author

**Your Name**
- Website: [https://yourwebsite.com](https://yourwebsite.com)
- GitHub: [@yourusername](https://github.com/yourusername)

## 🙏 Credits

- [Elementor](https://elementor.com/) - Page builder framework
- [Font Awesome](https://fontawesome.com/) - Icon library
- [WordPress](https://wordpress.org/) - CMS platform

## 📞 Support

For support, please:

1. Check the [FAQ section](https://wordpress.org/plugins/mega-menu-builder-for-elementor/#faq)
2. Search [existing issues](https://github.com/yourusername/mega-menu-builder-for-elementor/issues)
3. Create a new issue if needed
4. Contact through WordPress.org support forum

---

Made with ❤️ for the WordPress community
