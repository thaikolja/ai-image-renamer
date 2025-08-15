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

# Project utility module: provides image verification, word extraction, and path sanitization helpers
import ai_image_renamer.utils as utils


class ImageRenamer:
	"""
	Handles renaming of image files based on descriptive textual content extracted from them.

	The `ImageRenamer` class processes a list of image file paths, verifies their validity,
	extracts textual content, and renames the files to a more descriptive name. This is
	useful for organizing and making image archives more searchable or user-friendly.

	:ivar image_paths: A list of file paths to the images that need to be renamed.
	:type image_paths: list
	"""

	def __init__(self, args):
		"""
		Initializes an instance and renames files based on the provided image paths.

		The constructor accepts arguments required for setting up the instance and
		processes file renaming for the given image paths.

		:param args: Arguments containing image paths.
		:type args: Any
		"""
		self.args = args
		self.image_paths = args.image_paths
		self.rename()

	def rename(self):
		"""
		Rename each image file in `self.image_paths` using extracted descriptive content.

		Pipeline:
			1. Verification: Skip files that are not valid / supported images (`utils.verify_image_file`).
			2. Content extraction: Derive descriptive words (`utils.get_words`); skip if none returned.
			3. Sanitization: Build a new, safe target path (`utils.sanitize_image_path`); skip if path looks implausible (<=3 chars).
			4. Rename: Perform `os.rename` and report the change.

		Side effects:
			- Mutates the filesystem by renaming files.
			- Writes progress and skip reasons to stdout.

		Returns:
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
			content = utils.get_words(path, self.args.words)
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
