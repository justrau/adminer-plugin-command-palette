# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is an Adminer plugin that adds a command palette (Cmd+K / Ctrl+K) for quick navigation to databases and tables. It's a single-file PHP plugin for Adminer 5.x.

## Architecture

The entire plugin is contained in `command-palette.php`:
- **PHP class** (`AdminerCommandPalette`): Extends `Adminer\Plugin` and implements the `head()` method to inject JavaScript
- **JavaScript (inline)**: Creates the command palette UI dynamically, handles fuzzy search, and manages keyboard/mouse navigation
- **Data flow**: PHP generates a JavaScript array of tables (from current database) and databases (excluding system DBs: information_schema, mysql, performance_schema, sys)

Key components in the inline JavaScript:
- `fuzzyMatch()`: Scoring algorithm that prioritizes exact matches > starts-with > contains > fuzzy matches, with shorter strings ranked higher
- `filterItems()`: Separates and scores tables vs databases, showing tables first in results
- Dual input modes: Keyboard navigation (arrow keys) and mouse hover, with smooth transitions between them

## Development

No build process. To test changes:
1. Copy `command-palette.php` to an Adminer plugins directory
2. Include it in `adminer-plugins.php` with `new AdminerCommandPalette()`
3. Reload Adminer in browser
