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
#  @website     https://docs.kolja-nolte.com/ai-image-renamer
#  @repository  https://gitlab.com/thaikolja/ai-image-renamer


# Standard library import: argparse provides facilities for parsing CLI arguments.
import argparse
# Internal package import: renamer module (aliased) containing ImageRenamer logic.
import ai_image_renamer.renamer as renamer
# Standard library import: metadata retrieves the installed package version (PEP 566).
from importlib import metadata
# Third-party import: load_dotenv loads environment variables from a .env file.
from dotenv import load_dotenv


def main():
	"""
	Entry point for the AI Image Renamer CLI.

	Parses command line arguments and instantiates renamer.ImageRenamer
	with the resulting namespace.

	Supported arguments:
	- -v / --version: Displays the package version and exits.
	- image_paths: One or more paths to image files that should be renamed.

	Returns:
		None
	"""
	load_dotenv()

	# Create an ArgumentParser configured with a short description and an epilog
	# pointing users to the project documentation for extended usage details.
	parser = argparse.ArgumentParser(
		description="AI Image Renamer CLI",
		epilog="For more information, visit https://docs.kolja-nolte.com/ai-image-renamer"
	)
	# Add an optional flag (--version / -v) that prints the package version and exits.
	# The 'version' action handles displaying the string and terminating the program.
	parser.add_argument(
		'--version', '-v',
		action="version",
		version=f"%(prog)s {metadata.version('ai-image-renamer')}",
		help="Show the version of the ai_image_renamer package",
	)
	parser.add_argument(
		'--words', '-w',
		default=6,
		help="Number of words used to rename the image file (default: 6)",
		type=int,
		metavar='N',
		choices=range(1, 50),
	)
	# Add a positional argument accepting one or more image file paths.
	# nargs='+' enforces at least one path; each is collected as a string.
	parser.add_argument(
		'image_paths',
		help="Path(s) to the image file(s) to be renamed",
		action="store",
		type=str,
		nargs='+'
	)

	# Parse all command-line arguments into a Namespace object.
	args = parser.parse_args()

	# Instantiate the ImageRenamer with the parsed arguments to perform renaming logic.
	renamer.ImageRenamer(args)
