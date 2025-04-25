# CLAUDE.md - Live Edit WordPress Plugin

## Development Commands
- WordPress plugin development does not require build or compile steps
- Activate plugin through WordPress admin: Plugins > Installed Plugins
- Test plugin by viewing a single post page while logged in as editor/admin
- Validate PHP: `php -l live-edit-plugin.php`
- WordPress Coding Standards: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/

## Code Style Guidelines
- Follow WordPress PHP Coding Standards
- Use snake_case for function names and variables
- Prefix functions with `live_edit_` to avoid namespace conflicts
- Add docblocks above functions with descriptions
- Proper security practices: nonce verification, capability checks
- Sanitize inputs and escape outputs (wp_kses, esc_attr, etc.)
- Use direct database queries sparingly, prefer WP API functions
- CSS uses hyphenated class names prefixed with `live-edit-`
- Use tabs for indentation in PHP (WordPress standard)
- Error handling: verify permissions and validate data before operations

## Security Practices
- Check user capabilities with `current_user_can()`
- Implement nonce validation for AJAX requests
- Sanitize input data and escape output
- Use wpdb->prepare for database queries