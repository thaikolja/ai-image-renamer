#  -*- coding: utf-8 -*-
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
AI Image Renamer - Intelligent image file renaming using AI.

This package provides tools for renaming image files based on their content
using artificial intelligence. It leverages the Groq API with Meta's Llama 4
Maverick model to analyze images and generate descriptive, SEO-friendly filenames.

Package Structure:
    ai_image_renamer/
    ├── __init__.py     # Package initialization and exports (this file)
    ├── cli.py          # Command-line interface entry point
    ├── renamer.py      # Core ImageRenamer class for processing
    └── utils.py        # Utility functions (validation, encoding, API calls)

Main Components:
    - :class:`ImageRenamer`: Main class for orchestrating the rename pipeline
    - :func:`main`: CLI entry point function
    - :mod:`utils`: Utility functions for image processing

Quick Start:
    Command Line Usage::

        # Install the package
        pip install ai-image-renamer

        # Set your API key
        export GROQ_API_KEY="your-key-here"

        # Rename images
        rename-images photo.jpg

    Programmatic Usage::

        from ai_image_renamer import ImageRenamer
        import argparse

        args = argparse.Namespace(image_paths=['photo.jpg'], words=6)
        renamer = ImageRenamer(args)

For full documentation, visit: https://docs.kolja-nolte.com/ai-image-renamer-cli
"""

# ==============================================================================
# Version Information
# ==============================================================================
# Import version from metadata for consistent versioning across the package
# This reads from pyproject.toml's [project.version]
try:
    from importlib import metadata
    __version__ = metadata.version('ai-image-renamer')
except Exception:
    # Fallback version if metadata is not available (e.g., during development)
    __version__ = '1.0.0'

# ==============================================================================
# Public API Exports
# ==============================================================================
# Import main components to make them available at package level
# This allows: from ai_image_renamer import ImageRenamer, main

# Import the main CLI entry point
from .cli import main

# Import the core ImageRenamer class
from .renamer import ImageRenamer

# Import utility functions for direct access
from .utils import (
    verify_image_file,
    encode_image,
    sanitize_image_path,
    get_words,
)

# ==============================================================================
# Define Public Interface
# ==============================================================================
# __all__ controls what gets imported with "from ai_image_renamer import *"
# This is the official public API of the package
__all__ = [
    # Version
    '__version__',

    # Main classes
    'ImageRenamer',

    # CLI entry point
    'main',

    # Utility functions
    'verify_image_file',
    'encode_image',
    'sanitize_image_path',
    'get_words',
]