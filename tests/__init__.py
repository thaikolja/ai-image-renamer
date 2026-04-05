# -*- coding: utf-8 -*-
"""
Test package for AI Image Renamer.

This package contains unit tests for all modules in the ai_image_renamer package:
- test_cli.py: Tests for the command-line interface
- test_renamer.py: Tests for the ImageRenamer class
- test_utils.py: Tests for utility functions

Running Tests:
    # Run all tests with pytest
    pytest tests/

    # Run all tests with unittest
    python -m unittest discover tests

    # Run a specific test file
    python -m unittest tests.test_utils

    # Run a specific test class
    python -m unittest tests.test_utils.TestUtils

    # Run a specific test method
    python -m unittest tests.test_utils.TestUtils.test_verify_image_file_with_valid_image

Test Dependencies:
    - pytest (optional, for pytest runner)
    - unittest (standard library)
    - unittest.mock (standard library)

Note:
    Tests use mocking to avoid actual API calls and file system modifications.
    The assets/ directory contains test images for integration-style tests.
"""
