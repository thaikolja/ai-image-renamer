# -*- coding: utf-8 -*-
"""Tests for the renamer functions."""

import unittest
from unittest.mock import patch, MagicMock

from src.ai_image_renamer import renamer


class TestRenamer(unittest.TestCase):
    """Tests for the renamer functions."""

    @patch('src.ai_image_renamer.renamer.utils')
    @patch('os.rename')
    def test_rename_success(self, mock_os_rename, mock_utils):
        """Test rename function with a successful rename."""
        # Arrange
        args = MagicMock()
        args.image_paths = ["test_image.jpg"]
        args.words = 8
        mock_utils.verify_image_file.return_value = True
        mock_utils.get_words.return_value = "A test description"
        mock_utils.sanitize_image_path.return_value = "a-test-description.jpg"

        # Act
        renamer.ImageRenamer(args)

        # Assert
        mock_os_rename.assert_called_once_with("test_image.jpg", "a-test-description.jpg")

    @patch('src.ai_image_renamer.renamer.utils')
    @patch('os.rename')
    def test_rename_invalid_image(self, mock_os_rename, mock_utils):
        """Test rename function with an invalid image."""
        # Arrange
        args = MagicMock()
        args.image_paths = ["test_image.jpg"]
        args.words = 8
        mock_utils.verify_image_file.return_value = False

        # Act
        renamer.ImageRenamer(args)

        # Assert
        mock_os_rename.assert_not_called()


if __name__ == '__main__':
    unittest.main()