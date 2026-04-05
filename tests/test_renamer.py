# -*- coding: utf-8 -*-
"""
Test suite for the AI Image Renamer core renamer module.

This module contains unit tests for the ImageRenamer class in
ai_image_renamer.renamer, which orchestrates the image renaming pipeline.

All tests use unittest.mock to simulate dependencies without actual file
operations or API calls.
"""

# ==============================================================================
# Standard Library Imports
# ==============================================================================
import unittest
from unittest.mock import patch, MagicMock
import os
import sys

# ==============================================================================
# Package Imports
# ==============================================================================
# Use try/except to handle both installed and development environments
try:
    from ai_image_renamer import renamer
except ImportError:
    # Fallback for development/testing without installation
    sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', 'src'))
    from ai_image_renamer import renamer


# ==============================================================================
# Test Class for ImageRenamer
# ==============================================================================

class TestRenamer(unittest.TestCase):
    """
    Unit tests for the ImageRenamer class.

    These tests verify the renaming pipeline:
    - Validation of image files
    - AI content retrieval
    - Path sanitization
    - File rename operations

    All filesystem and API operations are mocked to ensure tests are
    fast, deterministic, and don't modify real files.
    """

    # ==========================================================================
    # Tests for Successful Rename Operations
    # ==========================================================================

    @patch('ai_image_renamer.renamer.utils')
    @patch('os.rename')
    def test_rename_success(self, mock_os_rename, mock_utils):
        """
        Test the rename function with a successful rename operation.

        This test verifies that:
        1. The image file is validated
        2. AI content is retrieved
        3. The path is sanitized
        4. os.rename is called with correct arguments
        """
        # Arrange: Set up mock arguments
        args = MagicMock()
        args.image_paths = ["test_image.jpg"]
        args.words = 8

        # Arrange: Configure mock utils behavior
        mock_utils.verify_image_file.return_value = True
        mock_utils.get_words.return_value = "A test description"
        mock_utils.sanitize_image_path.return_value = "a-test-description.jpg"

        # Act: Create ImageRenamer instance (triggers rename)
        renamer.ImageRenamer(args)

        # Assert: os.rename should be called once with expected arguments
        mock_os_rename.assert_called_once_with(
            "test_image.jpg",
            "a-test-description.jpg"
        )

    @patch('ai_image_renamer.renamer.utils')
    @patch('os.rename')
    def test_rename_multiple_images(self, mock_os_rename, mock_utils):
        """
        Test renaming multiple images in a single batch.

        Verifies that all images in the list are processed.
        """
        # Arrange: Set up mock arguments for multiple images
        args = MagicMock()
        args.image_paths = ["image1.jpg", "image2.png", "image3.webp"]
        args.words = 6

        # Arrange: Configure mock utils for success
        mock_utils.verify_image_file.return_value = True
        mock_utils.get_words.side_effect = ["description one", "description two", "description three"]
        mock_utils.sanitize_image_path.side_effect = [
            "description-one.jpg",
            "description-two.png",
            "description-three.webp"
        ]

        # Act: Create ImageRenamer instance
        renamer.ImageRenamer(args)

        # Assert: os.rename should be called three times
        self.assertEqual(mock_os_rename.call_count, 3)

    # ==========================================================================
    # Tests for Failed/Skipped Rename Operations
    # ==========================================================================

    @patch('ai_image_renamer.renamer.utils')
    @patch('os.rename')
    def test_rename_invalid_image(self, mock_os_rename, mock_utils):
        """
        Test that invalid images are skipped without renaming.

        Verifies that verify_image_file is called and invalid files
        do not trigger os.rename.
        """
        # Arrange: Set up mock arguments
        args = MagicMock()
        args.image_paths = ["test_image.jpg"]
        args.words = 8

        # Arrange: Configure mock utils to report invalid image
        mock_utils.verify_image_file.return_value = False

        # Act: Create ImageRenamer instance
        renamer.ImageRenamer(args)

        # Assert: os.rename should NOT be called for invalid images
        mock_os_rename.assert_not_called()

    @patch('ai_image_renamer.renamer.utils')
    @patch('os.rename')
    def test_rename_empty_content(self, mock_os_rename, mock_utils):
        """
        Test that files are skipped when AI returns empty content.

        Verifies the graceful handling of failed API responses.
        """
        # Arrange: Set up mock arguments
        args = MagicMock()
        args.image_paths = ["test_image.jpg"]
        args.words = 8

        # Arrange: Configure mock utils for valid image but empty content
        mock_utils.verify_image_file.return_value = True
        mock_utils.get_words.return_value = ""  # Empty content

        # Act: Create ImageRenamer instance
        renamer.ImageRenamer(args)

        # Assert: os.rename should NOT be called with empty content
        mock_os_rename.assert_not_called()

    @patch('ai_image_renamer.renamer.utils')
    @patch('os.rename')
    def test_rename_short_filename(self, mock_os_rename, mock_utils):
        """
        Test that very short filenames are skipped.

        Filenames with length <= 3 characters are considered unusable.
        """
        # Arrange: Set up mock arguments
        args = MagicMock()
        args.image_paths = ["test_image.jpg"]
        args.words = 8

        # Arrange: Configure mock utils for valid but very short result
        mock_utils.verify_image_file.return_value = True
        mock_utils.get_words.return_value = "ab"  # Very short content
        mock_utils.sanitize_image_path.return_value = "ab.jpg"

        # Act: Create ImageRenamer instance
        renamer.ImageRenamer(args)

        # Assert: Very short stems should be skipped before renaming
        mock_os_rename.assert_not_called()

    @patch('ai_image_renamer.renamer.utils')
    @patch('os.rename')
    def test_rename_skips_when_target_matches_source(self, mock_os_rename, mock_utils):
        """
        Test that the renamer skips a file when the sanitized path matches the source.
        """
        args = MagicMock()
        args.image_paths = ["/tmp/same-name.jpg"]
        args.words = 8

        mock_utils.verify_image_file.return_value = True
        mock_utils.get_words.return_value = "same name"
        mock_utils.sanitize_image_path.return_value = "/tmp/same-name.jpg"

        renamer.ImageRenamer(args)

        mock_os_rename.assert_not_called()

    @patch('ai_image_renamer.renamer.utils')
    @patch('ai_image_renamer.renamer.os.path.exists')
    @patch('os.rename')
    def test_rename_adds_suffix_when_target_exists(self, mock_os_rename, mock_exists, mock_utils):
        """
        Test that an existing destination gets a numeric suffix instead of being overwritten.
        """
        args = MagicMock()
        args.image_paths = ["/tmp/source.jpg"]
        args.words = 8

        mock_utils.verify_image_file.return_value = True
        mock_utils.get_words.return_value = "target name"
        mock_utils.sanitize_image_path.return_value = "/tmp/target-name.jpg"
        mock_exists.side_effect = [True, False]

        renamer.ImageRenamer(args)

        mock_os_rename.assert_called_once_with("/tmp/source.jpg", "/tmp/target-name-1.jpg")

    # ==========================================================================
    # Tests for Argument Handling
    # ==========================================================================

    @patch('ai_image_renamer.renamer.utils')
    @patch('os.rename')
    def test_words_parameter_passed_to_get_words(self, mock_os_rename, mock_utils):
        """
        Test that the words parameter is correctly passed to get_words.
        """
        # Arrange: Set up mock arguments with custom words count
        args = MagicMock()
        args.image_paths = ["test_image.jpg"]
        args.words = 3  # Custom word count

        # Arrange: Configure mock utils
        mock_utils.verify_image_file.return_value = True
        mock_utils.get_words.return_value = "short desc"
        mock_utils.sanitize_image_path.return_value = "short-desc.jpg"

        # Act: Create ImageRenamer instance
        renamer.ImageRenamer(args)

        # Assert: get_words should be called with words=3
        mock_utils.get_words.assert_called_once_with("test_image.jpg", 3)


# ==============================================================================
# Test Runner Entry Point
# ==============================================================================

if __name__ == '__main__':
    unittest.main()
