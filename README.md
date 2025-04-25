# Live Edit Plugin

A WordPress plugin that adds an editor that allows logged-in users to edit posts in the WordPress classic editor directly on the front-end of posts to the WordPress classic editor.


## Features

- Adds an "Edit Post" box at the bottom of posts on the front-end
- Only visible to logged-in users with appropriate permissions
- One-click access to the WordPress classic editor for the current post
- No front-end editing - uses the familiar WordPress admin editor
- Simple and lightweight implementation
- Responsive design that works on mobile devices

## Installation

1. Download the plugin files and upload them to your `/wp-content/plugins/live-edit-plugin` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit any single post page while logged in to see the edit button

## Usage

1. Navigate to any single post page while logged in as a user with post editing capabilities
2. Scroll to the bottom of the post content to see the "Edit Post" button
3. Click the button to be redirected to the WordPress classic editor for that post
4. Make your changes in the WordPress admin editor
5. Save the post to update the content

## Requirements

- WordPress 5.0 or higher
- User must have editing capabilities
- Works with any theme that uses the standard WordPress content hooks

## Customization

You can customize the appearance of the button by modifying the CSS in the `css/live-edit.css` file.

## License

GPL2
