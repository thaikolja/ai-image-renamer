# -*- coding: utf-8 -*-
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

import argparse


def main():
	parser = argparse.ArgumentParser(
		description="AI Image Renamer CLI",
		epilog="For more information, visit https://docs.kolja-nolte.com/ai-image-renamer"
	)
	parser.add_argument(
		'--version', '-v',
		action="version",
		version="ai_image_renamer 1.0.0",
		help="Show the version of the ai_image_renamer package",
	)
	parser.add_argument(
		'image_path',
		help="Path to the image file to be renamed",
		action="store",
		type=str
	)

	args = parser.parse_args()

	return args.image_path


def __init__():
	"""Initialize the CLI module."""
	main()
