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
#  @website     https://docs.kolja-nolte.com/ai-image-renamer
#  @repository  https://gitlab.com/thaikolja/ai-image-renamer

# Standard library imports: filesystem operations and logging utilities
import os
import logging

# Project utility module: provides image verification, word extraction, and path sanitization helpers
import ai_image_renamer.utils as utils


class ImageRenamer:
	"""Renames image files based on extracted textual content.

	Workflow:
	1. Validate each image path with utils.verify_image_file.
	2. Extract words/descriptions via utils.get_words.
	3. Build a sanitized new path with utils.sanitize_image_path.
	4. Rename the original file using os.rename.

	Parameters
	----------
	args : argparse.Namespace
	    Must provide image_paths (Iterable[str]) to process.
	"""

	def __init__(self, args):
		"""Initialize and trigger the rename process.

		Parameters
		----------
		args : argparse.Namespace
		    Holds image_paths (Iterable[str]) that will be processed.
		"""
		self.image_paths = args.image_paths

		self.rename()
		pass

	def rename(self):
		"""Process and rename each image path in self.image_paths.

		For every path:
		- Skip and log an error if the file is not a valid image.
		- Extract descriptive content; skip if empty or failed.
		- Generate a sanitized new path; skip if too short.
		- Perform the filesystem rename.

		Returns
		-------
		None
		"""
		# Iterate over each image path provided via args.
		for path in self.image_paths:
			# Step 1: Verify the file is a supported, accessible image before any processing.
			if not utils.verify_image_file(path):
				print(f"Skipping invalid image file: {path}")
				continue

			# Step 2: Extract descriptive textual content (e.g., labels, tags, OCR results).
			# If extraction fails or returns nothing meaningful, skip this file.
			print(f"Processing {path}...")
			content = utils.get_words(path)
			if not content:
				print(f"Failed to retrieve content from image: {path}")
				continue

			# Step 3: Build a sanitized new file path (typically incorporating extracted words).
			# If the resulting name is implausibly short (<=3 chars), treat it as unusable.
			new_path = utils.sanitize_image_path(path, content)
			if len(new_path) <= 3:
				continue

			# Step 4: Rename the original file to the new, descriptive path.
			os.rename(path, new_path)
			print(f"Renamed {path} to {new_path}")
