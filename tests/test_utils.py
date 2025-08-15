# -*- coding: utf-8 -*-
"""Tests for the utility functions."""

import unittest
from unittest.mock import patch, MagicMock

from src.ai_image_renamer import utils


class TestUtils(unittest.TestCase):
    """Tests for the utility functions."""

    @patch('src.ai_image_renamer.utils.encode_image')
    @patch('src.ai_image_renamer.utils.Groq')
    def test_get_words_success(self, mock_groq, mock_encode_image):
        """Test get_words function with a successful API call."""
        # Arrange
        mock_encode_image.return_value = "encoded_image_string"
        mock_completion = MagicMock()
        mock_completion.choices[0].message.content = "A test description"
        mock_groq.return_value.chat.completions.create.return_value = mock_completion

        # Act
        result = utils.get_words("test_image.jpg", 8)

        # Assert
        self.assertEqual(result, "A test description")

    @patch('src.ai_image_renamer.utils.encode_image')
    @patch('src.ai_image_renamer.utils.Groq')
    def test_get_words_failure(self, mock_groq, mock_encode_image):
        """Test get_words function with a failed API call."""
        # Arrange
        mock_encode_image.return_value = "encoded_image_string"
        mock_groq.return_value.chat.completions.create.return_value = None

        # Act
        result = utils.get_words("test_image.jpg", 8)

        # Assert
        self.assertEqual(result, "")


if __name__ == '__main__':
    unittest.main()
