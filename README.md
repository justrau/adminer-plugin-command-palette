# Adminer Command Palette

A powerful command palette plugin for [Adminer](https://www.adminer.org) that provides quick navigation to databases and tables using keyboard shortcuts.

![Command Palette Demo](demo.gif)

## Features

- 🚀 **Quick Access**: Press `Cmd+K` (Mac) or `Ctrl+K` (Windows/Linux) to open
- 🔍 **Fuzzy Search**: Smart search with intelligent ranking
- ⌨️ **Keyboard Navigation**: Full keyboard support with arrow keys
- 🖱️ **Mouse Support**: Seamless mouse and keyboard interaction
- 📊 **Table Priority**: Tables are prioritized over databases in search results
- 🎨 **Modern UI**: Clean, responsive design with hover effects

## Installation

### Method 1: Direct Download
1. Download `command-palette.php` from this repository
2. Place it in your Adminer plugins directory
3. Include it in your Adminer setup

### Method 2: Git Clone
```bash
git clone https://github.com/yourusername/adminer-command-palette.git
cp adminer-command-palette/command-palette.php /path/to/adminer/plugins/
```

## Usage

### Setup
Include the plugin in your Adminer configuration:

```php
<?php
function adminer_object() {
    include_once "./plugins/command-palette.php";

    return new AdminerPlugin(array(
        new AdminerCommandPalette(),
        // ... other plugins
    ));
}
include "./adminer.php";
```

### Keyboard Shortcuts
- **`Cmd+K` / `Ctrl+K`**: Open/close command palette
- **`Arrow Up/Down`**: Navigate through results
- **`Enter`**: Select and navigate to item
- **`Escape`**: Close palette
- **`Cmd+J` / `Ctrl+J`**: Copy search results as JSON (debug mode)

### Search Tips
- Search is case-insensitive and supports partial matches
- Tables are shown before databases in results
- Exact matches are prioritized over fuzzy matches
- Type part of a table or database name to quickly find it

## Requirements

- Adminer 4.x+
- Modern browser with JavaScript enabled
- CSP-compliant (uses nonce for inline scripts)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is dual-licensed under:
- [Apache License 2.0](LICENSE-APACHE)
- [GNU General Public License v2.0](LICENSE-GPL)

You may choose either license.

## Acknowledgments

- Built for the [Adminer](https://www.adminer.org) community
- Inspired by modern command palette interfaces like VS Code and GitHub