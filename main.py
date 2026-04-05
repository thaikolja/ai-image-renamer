#!/usr/bin/env python3
# -*- coding: utf-8 -*-
#
#  AI Image Renamer
#
#  Copyright (C) 2025 Kolja Nolte
#  https://www.kolja-nolte.com
#  kolja.nolte@gmail.com
#
#  This work is licensed under the MIT License. You are free to use, modify, and distribute this work, provided that you include the copyright notice and this permission notice in all copies or substantial portions of the work. For more information, visit: https://opensource.org/licenses/MIT
#
#  @author      Kolja Nolte
#  @email       kolja.nolte@gmail.com
#  @license     MIT
#  @date        2025
#  @website     https://docs.kolja-nolte.com/ai-image-renamer-cli
#  @repository  https://gitlab.com/thaikolja/ai-image-renamer

"""
AI Image Renamer - Main Entry Point

This script serves as the primary entry point for the AI Image Renamer application
when running from the project root directory. It provides multiple ways to use
the application:

1. **As a CLI command** (after installation):
   $ rename-images image.jpg

2. **As a Python module** (from project root):
   $ python main.py image.jpg

3. **As an imported module** (in Python code):
   >>> from main import main
   >>> main()

This file handles:
- Setting up the Python path for development mode
- Importing and delegating to the main CLI function
- Providing a clean entry point for both installed and development environments

Usage Examples:
    Command Line::

        # Basic usage with single image
        python main.py photo.jpg

        # Multiple images
        python main.py photo1.jpg photo2.png photo3.webp

        # Custom word count
        python main.py -w 3 photo.jpg

        # Show help
        python main.py --help

        # Show version
        python main.py --version

    Python::

        >>> import sys
        >>> sys.argv = ['main.py', 'photo.jpg', '-w', '5']
        >>> from main import main
        >>> main()

Environment Requirements:
    GROQ_API_KEY: Your Groq API key (required for AI functionality)
        - Get a free key at: https://console.groq.com/keys
        - Set via: export GROQ_API_KEY="your-key-here"
        - Or create a .env file in the project root

For full documentation, visit: https://docs.kolja-nolte.com/ai-image-renamer-cli
"""

# ==============================================================================
# Path Setup for Development Mode
# ==============================================================================
# When running from the project root without installation, we need to add
# the 'src' directory to the Python path so imports work correctly.
# This block is safe to leave in place even after installation - it only
# affects the path if 'src' exists relative to this file.

import os
import sys

# Get the directory containing this main.py file
_current_dir = os.path.dirname(os.path.abspath(__file__))

# Add the 'src' directory to sys.path for development mode
# This allows: from ai_image_renamer import ... to work without installation
_src_path = os.path.join(_current_dir, 'src')
if _src_path not in sys.path:
    sys.path.insert(0, _src_path)

# ==============================================================================
# Main Application Entry Point
# ==============================================================================
# Import and call the main function from the CLI module
# This provides a clean separation between the entry point and the CLI logic

def main():
    """
    Main entry point for the AI Image Renamer application.

    This function:
    1. Ensures the Python path is correctly set up
    2. Imports the CLI main function
    3. Delegates execution to the CLI module

    All command-line argument parsing and image processing is handled
    by the CLI module.

    Returns:
        None: This function performs side effects only (file operations).

    Raises:
        RuntimeError: If GROQ_API_KEY is not set (propagated from utils)
        FileNotFoundError: If specified image file does not exist
        Exception: Any underlying exception from the processing pipeline

    Example:
        >>> import sys
        >>> sys.argv = ['main.py', 'vacation.jpg']
        >>> main()
        Processing vacation.jpg...
        Renamed vacation.jpg to /photos/sunny-beach-sunset.jpg
    """
    # Import the CLI module's main function
    # Import is done inside the function to ensure path setup is complete
    from ai_image_renamer.cli import main as cli_main

    # Delegate to the CLI main function
    cli_main()


# ==============================================================================
# Script Execution
# ==============================================================================
# This block runs when the script is executed directly (not imported)
# It allows the script to be run as: python main.py [arguments]

if __name__ == '__main__':
    # Call the main function with any command-line arguments
    # sys.argv is automatically parsed by argparse in cli.main()
    main()
