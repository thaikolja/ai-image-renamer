# AI Image Renamer

![PyPI - Version](https://img.shields.io/pypi/v/ai-image-renamer) ![PyPI - Downloads](https://img.shields.io/pypi/dm/ai-image-renamer) ![PyPI - License](https://img.shields.io/pypi/l/ai-image-renamer)

**AI Image Renamer** is a command-line tool that leverages generative artificial intelligence to intelligently rename your image files based on their content. This helps in organizing your photo collection by giving images more descriptive and searchable filenames. A [free Groq API key](https://console.groq.com/keys) is required for this project. For a full documentation of this tool, please visit https://docs.kolja-nolte.com/ai-image-renamer.

**Table of Contents**

[TOC]

## Features

- **AI-Powered Renaming**: Utilizes the advanced AI model `llama-4-maverick-17b-128e-instruct`[^1] to understand image content and suggest relevant names.
- **Batch Processing**:[^2] Rename multiple images at once with a single command.
- **Easy to Use**: Simple command-line interface for quick and efficient renaming.

## Installation

1. Install **AI Image Renamer** via the `pip` command:

   ```bash
   pip install ai-image-renamer
   ```

2. Get your [free API key on console.groq.com](https://console.groq.com/keys) and set it as an environment variable in  your user's `.bashrc`, `.bash_profile`, `.zshrc`, `.zprofile`, or whichever you use:

   ```bash
   export GROQ_API_KEY="..."
   ```

## Usage

The `rename-images` command is your entry point to the tool. However, since it's using [Groq and Meta's Llama 4 Maverick](https://console.groq.com/docs/vision) model[^1], some limitations apply:

### Limits

* **No more than 5 image** files per command
* The total size of all images **must not exceed 4 MB**
* Each image must have **fewer than 33,177,600 pixels** (e.g., 7680x4320)

### Basic Usage

To rename a single image:

```bash
rename-images path/to/your/image.jpg
```

To rename multiple images:

```bash
rename-images image1.png image2.jpg path/to/another/image.webp
```

### File Types

The following image file types are supported:

* `.jpg` / `.jpeg`
* `.png`
* `.webp`
* `.bmp`

### Help and Options

You can always get help and see all available options by running:

```bash
image-renamer -h # or --help
```

This will display information about the command, its arguments, and options, similar to this:

```bash
usage: rename-images [-h] [--version] [--words N] image_paths [image_paths ...]

AI Image Renamer CLI

positional arguments:
  image_paths    Path(s) to the image file(s) to be renamed

options:
  -h, --help     show this help message and exit
  --version, -v  Show the version of the ai_image_renamer package
  --words, -w N  Number of words used to rename the image file (default: 6)

For more information, visit https://docs.kolja-nolte.com/ai-image-renamer
```

## Contributing

I welcome contributions to **AI Image Renamer**! Please see the [CONTRIBUTING.md](CONTRIBUTING.md) file for guidelines on how to contribute.

## Author

1. **Kolja Nolte** (kolja.nolte@gmail.com)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

[^1]: Alternatively, you can also use the smaller LLM `meta-llama/llama-4-scout-17b-16e-instruct` model.
[^2]: Maximal 5 image files per command.
