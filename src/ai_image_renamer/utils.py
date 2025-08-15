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

# Standard library imports:
# - os: filesystem path and file operations
# - re: regular expressions for sanitizing text
# - base64: encode image bytes for API transmission
import os
import re
import base64

# Third-party libraries:
# - filetype: infer MIME type from magic bytes
# - Groq: client for Groq multimodal chat completions
import filetype
from groq import Groq


def verify_image_file(image_path) -> bool:
	"""
	Determine whether the given filesystem path points to a valid image file.

	The function performs two validations:
	1. Verifies the path exists and is a regular file.
	2. Infers the MIME type via `filetype.guess` (magic bytes) and checks it starts with `image/`.

	This avoids reliance on filename extensions, which can be spoofed.

	Parameters:
		image_path (str): Absolute or relative path to the candidate file.

	Returns:
		bool: True if the file exists and its inferred MIME type is an image; otherwise False.

	Notes:
		- Returns False instead of raising to simplify bulk filtering workflows.
		- `filetype.guess` reads only the header, keeping the check efficient.

	Example:
		verify_image_file("photo.jpg") = True

		verify_image_file("document.pdf") = False
	"""
	# Fail fast if path does not point to an existing regular file.
	if not os.path.isfile(image_path):
		return False

	# Infer MIME type from magic bytes (ignores file extension).
	mime_type = filetype.guess(image_path)

	# Reject if MIME type cannot be determined or is not an image.
	if not mime_type or not mime_type.mime.startswith('image/'):
		return False

	# All checks passed; treat as valid image file.
	return True


def encode_image(image_path: str) -> str:
	"""
	Read the binary contents of an image file and return a base64-encoded UTF-8 string.

	Parameters:
		image_path (str): Filesystem path to the image file to encode.

	Returns:
		str: Base64 encoded representation of the image file.

	Raises:
		FileNotFoundError: If the file does not exist.
		OSError: If the file cannot be opened or read.
	"""
	with open(image_path, "rb") as image_file:
		return base64.b64encode(image_file.read()).decode('utf-8')


def sanitize_image_path(image_path: str, image_content: str) -> str:
	"""
	Sanitizes the image path based on the image content.

	Generates a new file name by removing non-alphabetic characters from the
	image content and converting spaces to hyphens.

	:param image_path: The original path of the image file.
	:param image_content: The content to use for the new file name.
	:return: The sanitized file path.
	"""
	# Absolute directory path containing the original image file
	dir_path = os.path.abspath(os.path.dirname(image_path))

	# Normalized (lowercase) file extension of the original image (kept unchanged)
	extension = os.path.splitext(image_path)[1].lower()

	# Lowercase the AI description and remove any character not a-z or whitespace
	clean_content = re.sub(r'[^a-z\s]+', ' ', image_content.lower())

	# Collapse consecutive whitespace into single hyphens and trim leading/trailing hyphens
	slug = re.sub(r'\s+', '-', clean_content).strip('-')

	# Construct the sanitized file path with the generated slug and original extension
	return os.path.join(dir_path, f"{slug}{extension}")


# noinspection PyTypeChecker
def get_words(image_path: str, words: int = 6) -> str:
	"""
	Generate a short, SEO-friendly description for an image using a Groq multimodal model.

	Parameters:
		image_path (str): Filesystem path to the image to analyze. Must point to a readable image file.
		words (int): Maximum number of words requested for the description (soft constraint for the model).

	Returns:
		str: A concise description (ideally within the requested word limit) or an empty string if
		the API returns no usable content.

	Raises:
		RuntimeError: If the environment variable GROQ_API_KEY is not set.
		FileNotFoundError: Propagated if the image file does not exist (from encode_image).
		OSError: Propagated if the image cannot be read.
		Exception: Any underlying Groq client exception is not caught here.

	Notes:
		- The temperature is set to 2 to encourage varied, creative phrasing; lower it for consistency.
		- The image is embedded as a data URL (assumed JPEG mime) for inline transmission.
		- Defensive checks ensure safe access to completion choices.
		- The model may occasionally exceed the requested word count; you can post-trim if strict limits are required.
	"""
	groq_api_key = os.getenv("GROQ_API_KEY")

	if not groq_api_key:
		raise RuntimeError("Set GROQ_API_KEY in your environment")

	# Initialize Groq client.
	client = Groq(api_key=groq_api_key)

	# Convert image binary to base64 for inline data URL usage.
	encoded_image = encode_image(image_path)

	# Create a multimodal chat completion requesting a concise description.
	completion = client.chat.completions.create(
		model="meta-llama/llama-4-maverick-17b-128e-instruct",
		temperature=2,
		stream=False,
		stop=None,
		messages=[
			{
				"role":    "user",
				"content": [
					{
						"type": "text",
						"text": f"What's in this image? Describe the content of this image with no more than {words} words in an SEO-friendly way"
					},
					{
						"type":      "image_url",
						"image_url": {
							"url": f"data:image/jpeg;base64,{encoded_image}",
						}
					}
				]
			}
		],
	)

	# Defensive checks for missing/empty response.
	if not completion or not completion.choices:
		return ''

	if not completion.choices[0].message or not completion.choices[0].message.content:
		return ''

	# Return the generated short description.
	return completion.choices[0].message.content
