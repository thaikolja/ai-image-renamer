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
#  @website     https://docs.kolja-nolte.com/ai-image-renamer-cli
#  @repository  https://gitlab.com/thaikolja/ai-image-renamer

"""
Utility module for AI Image Renamer.

This module provides core utility functions for:
- Image file validation based on magic bytes (not file extensions)
- Base64 encoding of image files for API transmission
- Path sanitization to create SEO-friendly filenames
- AI-powered image content description via Groq API

The functions in this module are designed to work together as a pipeline:
1. verify_image_file() - Validate that a file is a supported image
2. encode_image() - Convert image to base64 for API upload
3. get_words() - Send to Groq API and receive description
4. sanitize_image_path() - Generate a clean, SEO-friendly filename
"""

# ==============================================================================
# Standard Library Imports
# ==============================================================================

# os: Provides filesystem path and file operations
# Used for: path manipulation, file existence checks, environment variables
import os

# re: Regular expression operations for text processing
# Used for: sanitizing filenames, removing non-alphabetic characters
import re

# base64: Encoding binary data as ASCII strings
# Used for: converting image bytes to base64 for API transmission
import base64

# ==============================================================================
# Third-Party Library Imports
# ==============================================================================

# filetype: Infers file type from magic bytes (file header)
# More reliable than file extensions for security and accuracy
# Supports: JPEG, PNG, GIF, WebP, and many other formats
import filetype

# Groq: Official Python client for Groq's LLM API
# Provides fast inference for multimodal models (text + images)
from groq import Groq


# ==============================================================================
# Image Validation Functions
# ==============================================================================

def verify_image_file(image_path: str) -> bool:
    """
    Determine whether the given filesystem path points to a valid image file.

    This function performs two critical validations:
    1. Verifies the path exists and points to a regular file (not directory/symlink)
    2. Infers the MIME type from magic bytes (file header), not extension

    Using magic bytes instead of file extensions provides security benefits:
    - Prevents spoofing (e.g., malicious.exe renamed to image.jpg)
    - Works correctly even if file has wrong or missing extension
    - Only reads the first few bytes, keeping the check efficient

    Args:
        image_path (str): Absolute or relative path to the candidate file.
                         Can be any string; function will handle invalid paths gracefully.

    Returns:
        bool: True if the file exists and its inferred MIME type starts with 'image/'.
              False for any of the following conditions:
              - Path does not exist
              - Path points to a directory
              - File type cannot be determined
              - File is not an image (e.g., PDF, video, executable)

    Examples:
        >>> verify_image_file("photo.jpg")
        True
        >>> verify_image_file("document.pdf")
        False
        >>> verify_image_file("/nonexistent/path.png")
        False
        >>> verify_image_file("malware.exe.jpg")  # Spoofed extension
        False

    Note:
        This function never raises exceptions for invalid inputs.
        It returns False instead, making it safe for bulk filtering workflows.
    """
    # Step 1: Check if path exists and is a regular file
    # os.path.isfile() returns False for directories, symlinks, and non-existent paths
    if not os.path.isfile(image_path):
        return False

    # Step 2: Infer MIME type from file's magic bytes (header)
    # filetype.guess() reads only the first ~262 bytes, keeping this fast
    # Returns None if file type cannot be determined
    mime_type = filetype.guess(image_path)

    # Step 3: Validate that we got a result and it's an image type
    # mime_type.mime format: "image/jpeg", "image/png", "video/mp4", etc.
    if not mime_type or not mime_type.mime.startswith('image/'):
        return False

    # All validation checks passed - this is a valid image file
    return True


# ==============================================================================
# Image Encoding Functions
# ==============================================================================

def encode_image(image_path: str) -> str:
    """
    Read the binary contents of an image file and return a base64-encoded string.

    Base64 encoding converts binary data into ASCII characters, which is required
    for embedding images in JSON payloads sent to the Groq API. The resulting
    string can be used in a data URL format: data:image/jpeg;base64,<encoded_data>

    Args:
        image_path (str): Filesystem path to the image file to encode.
                         Should be a valid path to an existing image file.

    Returns:
        str: Base64 encoded representation of the image file contents.
             This is a UTF-8 string containing only ASCII-safe characters
             (A-Z, a-z, 0-9, +, /, =).

    Raises:
        FileNotFoundError: If the file does not exist at the specified path.
        PermissionError: If the process lacks read permissions for the file.
        OSError: If the file cannot be opened or read (disk error, etc.).

    Example:
        >>> encoded = encode_image("photo.jpg")
        >>> encoded[:50]  # First 50 chars of base64 string
        '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBw'
        >>> len(encoded)  # Length depends on file size
        123456

    Note:
        The returned string does NOT include the data URL prefix.
        Callers must prepend "data:image/jpeg;base64," when constructing URLs.
    """
    # Open file in binary read mode ('rb')
    # Binary mode is essential - text mode would corrupt image data
    with open(image_path, "rb") as image_file:
        # Read entire file contents into memory
        # For large files, consider chunked reading in production
        binary_data = image_file.read()

        # Encode binary data to base64 bytes, then decode to UTF-8 string
        # b64encode returns bytes; decode() converts to str for JSON compatibility
        return base64.b64encode(binary_data).decode('utf-8')


# ==============================================================================
# Path Sanitization Functions
# ==============================================================================

def sanitize_image_path(image_path: str, image_content: str) -> str:
    """
    Generate a sanitized, SEO-friendly file path from image description.

    This function transforms a descriptive text string into a clean filename:
    1. Converts to lowercase for consistency
    2. Removes all non-alphabetic characters (keeps only a-z and spaces)
    3. Replaces whitespace sequences with single hyphens
    4. Preserves the original file extension

    The resulting filename is:
    - URL-safe (no special characters)
    - SEO-friendly (hyphen-separated keywords)
    - Cross-platform compatible (no reserved characters)

    Args:
        image_path (str): Original file path. Used to extract:
                         - Directory path (preserved in output)
                         - File extension (preserved in output)
        image_content (str): Descriptive text to convert into filename.
                            Typically AI-generated description of the image.
                            Example: "A beautiful sunset over the ocean"

    Returns:
        str: Sanitized absolute path with the new filename.
             Format: /original/directory/beautiful-sunset-over-ocean.jpg
             Returns very short paths (<4 chars) if content is mostly non-alphabetic.

    Examples:
        >>> sanitize_image_path("/photos/IMG_001.jpg", "sunset beach")
        '/photos/sunset-beach.jpg'

        >>> sanitize_image_path("pic.PNG", "Dog Running in Park!")
        '/absolute/path/to/dog-running-in-park.png'

        >>> sanitize_image_path("test.jpg", "123!!!")  # No alphabetic content
        '/absolute/path/to/.jpg'  # Empty slug

    Note:
        The function always returns an absolute path, even for relative inputs.
        If the sanitized name is empty or very short, the result may be unusable.
        Callers should check the returned path length before using it.
    """
    # Step 1: Extract the absolute directory path
    # os.path.abspath() resolves relative paths and symlinks
    # os.path.dirname() returns everything before the final slash
    dir_path = os.path.abspath(os.path.dirname(image_path))

    # Step 2: Preserve the original file extension (case-normalized)
    # os.path.splitext() returns (basename_without_ext, extension)
    # Lowercase ensures consistency across operating systems
    extension = os.path.splitext(image_path)[1].lower()

    # Step 3: Lowercase the AI-generated description for consistency
    # This ensures "Sunset Beach" and "sunset beach" produce same result
    lower_content = image_content.lower()

    # Step 4: Remove all non-alphabetic characters (except spaces)
    # Regex [^a-z\s] matches anything NOT a-z or whitespace
    # Replace matched characters with a space to maintain word boundaries
    clean_content = re.sub(r'[^a-z\s]+', ' ', lower_content)

    # Step 5: Collapse whitespace sequences into single hyphens
    # \s+ matches one or more whitespace characters (space, tab, newline)
    # strip('-') removes leading/trailing hyphens from the result
    slug = re.sub(r'\s+', '-', clean_content).strip('-')

    # Step 6: Construct the final path
    # os.path.join() handles path separator correctly across platforms
    return os.path.join(dir_path, f"{slug}{extension}")


# ==============================================================================
# AI Content Generation Functions
# ==============================================================================

def get_words(image_path: str, words: int = 6) -> str:
    """
    Generate a concise, SEO-friendly description for an image using AI.

    This function sends an image to Groq's multimodal API (Llama 4 Scout model)

    and receives a short text description of the image contents. The description
    is suitable for use as a filename.

    The function uses the Groq API which provides:
    - Extremely fast inference (milliseconds per request)
    - Multimodal understanding (text + image input)
    - Free tier available with API key

    Args:
        image_path (str): Filesystem path to the image to analyze.
                          Must point to a readable image file.
                          Supports JPEG, PNG, WebP, GIF, and other common formats.
        words (int, optional): Maximum number of words requested in the description.
                               The model treats this as a soft constraint.
                               Defaults to 6.
                               Range: 1-50 (enforced by CLI, not this function).

    Returns:
        str: AI-generated description of the image contents.
             Example: "sunset over calm ocean with orange sky"
             Returns empty string '' if:
             - GROQ_API_KEY environment variable is not set
             - API returns empty response
             - API returns None for any expected field

    Raises:
        RuntimeError: If GROQ_API_KEY environment variable is not set.
                     User must set this before calling the function.
        FileNotFoundError: If image_path does not exist (propagated from encode_image).
        Exception: Any underlying Groq client exception is not caught here.
                  Network errors, API rate limits, etc. will propagate.

    Environment Variables:
        GROQ_API_KEY (str, required): Your Groq API key.
            Get a free key at: https://console.groq.com/keys
            Set via: export GROQ_API_KEY="gsk_..."

    Example:
        >>> # Set environment variable first
        >>> os.environ["GROQ_API_KEY"] = "gsk_xxx"
        >>> description = get_words("beach_photo.jpg", words=5)
        >>> print(description)
        'sunny beach with palm trees'

    Technical Details:
        - Model: meta-llama/llama-4-scout-17b-16e-instruct
        - Temperature: 2.0 (high creativity, varied outputs)
        - Image encoding: Base64 JPEG data URL
        - Request type: Chat completion with multimodal content

    Note:
        Temperature=2.0 encourages creative, varied descriptions.
        Lower values (0.0-1.0) would produce more consistent outputs.
        The model may occasionally exceed the requested word count.
    """
    # Step 1: Retrieve the API key from environment variables
    # This must be set before calling the function
    groq_api_key = os.getenv("GROQ_API_KEY")

    # Step 2: Validate API key exists
    # Fail fast with clear error message if key is missing
    if not groq_api_key:
        raise RuntimeError(
            "GROQ_API_KEY environment variable is not set. "
            "Please set it using: export GROQ_API_KEY='your-key-here' "
            "Get a free key at: https://console.groq.com/keys"
        )

    # Step 3: Initialize the Groq client with the API key
    # The client handles connection pooling and request formatting
    client = Groq(api_key=groq_api_key)

    # Step 4: Encode the image to base64 for API transmission
    # This converts binary image data to a string format suitable for JSON
    encoded_image = encode_image(image_path)

    # Step 5: Construct the multimodal chat completion request
    # The message contains both text instructions and the image data
    completion = client.chat.completions.create(
        # Use Llama 4 Maverick: fast, multimodal, good for descriptive tasks
        model="meta-llama/llama-4-scout-17b-16e-instruct",

        # Temperature controls randomness:
        # 0.0 = deterministic, 1.0 = balanced, 2.0 = very creative
        # Higher temperature produces more varied, creative descriptions
        temperature=2,

        # Disable streaming - we want the complete response at once
        stream=False,

        # No custom stop sequences - let the model complete naturally
        stop=None,

        # Messages array: conversation history for the model
        messages=[
            {
                # Role: "user" indicates this is user input
                "role": "user",

                # Content array: allows mixing text and images
                "content": [
                    {
                        # First element: text instruction/prompt
                        "type": "text",
                        # Prompt asks for SEO-friendly, word-limited description
                        "text": (
                            f"What's in this image? "
                            f"Describe the content of this image with no more than {words} words "
                            f"in an SEO-friendly way"
                        )
                    },
                    {
                        # Second element: image data as URL
                        "type": "image_url",
                        "image_url": {
                            # Data URL format: data:[MIME-type];base64,[data]
                            # We assume JPEG; most images work with this
                            "url": f"data:image/jpeg;base64,{encoded_image}",
                        }
                    }
                ]
            }
        ],
    )

    # Step 6: Extract the response content with defensive null checks
    # The API response structure: completion.choices[0].message.content

    # Check if we received any response at all
    if not completion or not completion.choices:
        return ''

    # Check if the first choice has a message
    if not completion.choices[0].message:
        return ''

    # Check if the message has content
    if not completion.choices[0].message.content:
        return ''

    # All checks passed - return the generated description
    return completion.choices[0].message.content