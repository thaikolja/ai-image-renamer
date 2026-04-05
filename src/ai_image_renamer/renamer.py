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
Image Renamer module - Core orchestration for AI-powered image renaming.

This module provides the ImageRenamer class which orchestrates the complete
image renaming pipeline:

    Input Image → Validation → AI Analysis → Filename Generation → File Rename

The module is designed to be used as the main processing engine, receiving
parsed CLI arguments and handling the batch processing of multiple images.

Usage:
    The ImageRenamer class is typically instantiated with parsed argparse
    arguments from the CLI module:

    >>> import argparse
    >>> args = argparse.Namespace(image_paths=['photo.jpg'], words=6)
    >>> renamer = ImageRenamer(args)
    # Images are renamed automatically during initialization

Dependencies:
    - utils module: Provides validation, AI calls, and path sanitization
    - os module: Filesystem operations for renaming files
"""

# ==============================================================================
# Standard Library Imports
# ==============================================================================

# os: Operating system interface for file and directory operations
# Used for: os.rename() to rename files, os.path for path manipulation
import os

# ==============================================================================
# Package Imports
# ==============================================================================

# Import utilities from the same package using relative import
# This provides:
# - verify_image_file(): Validate image files by magic bytes
# - get_words(): Get AI-generated description from Groq API
# - sanitize_image_path(): Generate clean, SEO-friendly filenames
from . import utils


# ==============================================================================
# Main Image Renamer Class
# ==============================================================================

class ImageRenamer:
    """
    Orchestrates the AI-powered image renaming process.

    This class is the main processing engine for the application. It receives
    a list of image paths and processes each one through a pipeline:

    1. **Validation**: Check if the file is a valid, supported image
    2. **AI Analysis**: Send image to Groq API for content description
    3. **Sanitization**: Generate a clean, SEO-friendly filename
    4. **Rename**: Perform the actual file system rename operation

    The class is designed for batch processing and handles errors gracefully:
    - Invalid files are skipped with a warning message
    - Failed API calls skip the file without crashing
    - Progress messages inform the user of each step

    Attributes:
        args (argparse.Namespace): Parsed command-line arguments containing:
            - image_paths (list): List of file paths to process
            - words (int): Maximum words in generated filename
        image_paths (list): Convenience reference to args.image_paths

    Example:
        >>> import argparse
        >>> args = argparse.Namespace(
        ...     image_paths=['vacation.jpg', 'family.png'],
        ...     words=5
        ... )
        >>> renamer = ImageRenamer(args)
        Processing vacation.jpg...
        Renamed vacation.jpg to /photos/sunny-beach-sunset.jpg
        Processing family.png...
        Renamed family.png to /photos/family-portrait-smile.png

    Note:
        The rename operation happens automatically in __init__.
        There is no need to call rename() manually unless processing
        images incrementally after initialization.
    """

    def __init__(self, args):
        """
        Initialize the ImageRenamer and process all specified images.

        This constructor stores the arguments and immediately begins processing
        all image paths through the rename pipeline. Each image is processed
        independently, so failures don't affect other files.

        Args:
            args (argparse.Namespace): Parsed CLI arguments containing:
                - image_paths (list[str]): One or more paths to image files
                - words (int): Maximum number of words for the new filename
                              (passed to AI prompt, actual count may vary)

        Returns:
            None: The constructor performs side effects (file renames) but
                  returns nothing.

        Side Effects:
            - Renames files on the filesystem
            - Prints progress messages to stdout
            - May raise exceptions from underlying operations

        Example:
            >>> args = argparse.Namespace(image_paths=['test.jpg'], words=6)
            >>> renamer = ImageRenamer(args)
            Processing test.jpg...
            Renamed test.jpg to /path/to/descriptive-name.jpg
        """
        # Store the complete arguments object for access to all CLI options
        self.args = args

        # Convenience reference to the image paths list
        # This is the primary data we iterate over in rename()
        self.image_paths = args.image_paths

        # Begin processing immediately
        # This design allows simple instantiation: ImageRenamer(args)
        self.rename()

    def rename(self):
        """
        Process and rename all images in the image_paths list.

        This method implements the core renaming pipeline:

        Pipeline Steps:
            1. **Verification**: Skip files that are not valid images
               - Uses magic byte detection (not file extensions)
               - Handles missing files, directories, and non-image files

            2. **Content Extraction**: Get AI-generated description
               - Sends image to Groq API via utils.get_words()
               - Skips file if API returns empty/None response

            3. **Path Sanitization**: Generate the new file path
               - Converts description to SEO-friendly slug
               - Preserves original file extension
               - Skips if resulting name is too short (≤3 chars)

            4. **File Rename**: Perform the actual rename operation
               - Uses os.rename() for atomic file system operation
               - Reports success with old and new paths

        Returns:
            None: This method performs side effects only (file operations).

        Side Effects:
            - Mutates the filesystem by renaming files
            - Prints progress and status messages to stdout

        Error Handling:
            - Invalid images: Skipped with warning message
            - Failed API calls: Skipped with warning message
            - Short filenames: Silently skipped
            - Rename failures: Exception propagates (file in use, permissions)

        Example Output:
            Processing photo1.jpg...
            Renamed photo1.jpg to /photos/beautiful-sunset-ocean.jpg
            Skipping invalid image file: document.pdf
            Processing photo2.png...
            Failed to retrieve content from image: photo2.png
        """
        # Iterate over each image path provided via CLI arguments
        # Each file is processed independently for fault isolation
        for path in self.image_paths:

            # ==================================================================
            # STEP 1: Verification
            # ==================================================================
            # Verify the file is a supported, accessible image before processing
            # This check uses magic bytes, not file extensions, for security
            if not utils.verify_image_file(path):
                # Skip invalid files but continue processing other images
                print(f"Skipping invalid image file: {path}")
                continue

            # ==================================================================
            # STEP 2: Content Extraction
            # ==================================================================
            # Notify user that processing has started for this file
            # This provides feedback for potentially slow API calls
            print(f"Processing {path}...")

            # Extract descriptive textual content from the image using AI
            # The words parameter comes from CLI --words argument
            content = utils.get_words(path, self.args.words)

            # If extraction fails or returns nothing meaningful, skip this file
            # Empty string indicates API failure or empty response
            if not content:
                print(f"Failed to retrieve content from image: {path}")
                continue

            # ==================================================================
            # STEP 3: Path Sanitization
            # ==================================================================
            # Build a sanitized new file path incorporating the AI description
            # This generates an SEO-friendly filename like "sunset-beach.jpg"
            new_path = utils.sanitize_image_path(path, content)

            # Skip if the resulting name is implausibly short
            # Length ≤3 means sanitization stripped most content (e.g., "a.jpg")
            # This prevents creating meaningless filenames
            if len(new_path) <= 3:
                print(f"Generated filename too short, skipping: {path}")
                continue

            # ==================================================================
            # STEP 4: File Rename
            # ==================================================================
            # Perform the actual file system rename operation
            # os.rename() is atomic on most filesystems
            # Note: This will fail if destination file already exists
            os.rename(path, new_path)

            # Report success to the user
            print(f"Renamed {path} to {new_path}")