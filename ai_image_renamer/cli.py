# -*- coding: utf-8 -*-
"""Command-line interface for ai_image_renamer."""
import argparse


def main():
	parser = argparse.ArgumentParser(
		description="AI Image Renamer CLI",
		epilog="For more information, visit https://docs.kolja-nolte.com/ai-image-renamer",
		formatter_class=argparse.ArgumentDefaultsHelpFormatter,
	)
	parser.add_argument(
		'--version', '-v',
		action="version",
		version="ai_image_renamer 1.0.0",
		help="Show the version of the ai_image_renamer package",
	)
	parser.add_argument(
		'path',
		help="Path to the image file to be renamed"
	)

	args = parser.parse_args()

	print(args.path)


def __init__():
	"""Initialize the CLI module."""
	main()
