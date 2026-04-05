# AI Image Renamer

![PyPI - Version](https://img.shields.io/pypi/v/ai-image-renamer) ![PyPI - Downloads](https://img.shields.io/pypi/dm/ai-image-renamer) ![PyPI - License](https://img.shields.io/pypi/l/ai-image-renamer)

**AI Image Renamer** is a command-line tool that leverages generative artificial intelligence to intelligently rename your image files based on their content. This helps in organizing your photo collection by giving images more descriptive and searchable filenames. A [free Groq API key](https://console.groq.com/keys) is required for this project. **For a full documentation of this tool, please visit the [official documentation](https://docs.kolja-nolte.com/ai-image-renamer-cli)**.

## Features

- 🤖 **AI:** Leverage the latest AI technology to quickly rename your images
- ⚡️**Speed:** Groq's fast infrastructure processes your files in milliseconds
- 🔎 **SEO:** Generated file names are [SEO-friendly](https://developers.google.com/search/docs/fundamentals/seo-starter-guide)
- 📚 **Batch:** Use one or multiple image files within a single command
- 👨‍💻 **Easy:** Renaming files requires only a single command line

## Installation

*AI Image Renamer* is available through multiple installation methods:

### 1. Using `pipx` (recommended)

```bash
pipx install ai-image-renamer
```

### 2. Using `pip`

```bash
pip install ai-image-renamer
```

### 3. From the Git repository

```bash
git clone https://gitlab.com/thaikolja/ai-image-renamer.git
cd ai-image-renamer
pip install -e .
```

### 4. From a ZIP archive

1. Download the ZIP from the repository.
2. Extract it and run:

```bash
cd ai-image-renamer-main
pip install .
```

### 5. Run directly from source

```bash
python -m ai_image_renamer.cli path/to/image.jpg
```

After installation, obtain a free Groq API key and set it as an environment variable:

```bash
export GROQ_API_KEY="your-key-here"
```

## Usage

The `rename-images` command is your entry point to the tool. However, since it's using [Groq and Meta's Llama 4 Scout](https://console.groq.com/docs/vision) model, some limitations apply:

### Basic Usage

To rename a single image:

```bash
rename-images path/to/your/image.jpg
```

To rename multiple images:

```bash
rename-images image1.png image2.jpg path/to/another/image.webp
```

To rename an image with only 3 words:

```bash
rename-images -w 3 DSC_123.jpg
```

See `rename-images -h` for more options or read about them in the [documentation](https://docs.kolja-nolte.com/ai-image-renamer-cli/usage/options).

## Contributing

I welcome contributions to **AI Image Renamer**! Please see the [CONTRIBUTING.md](CONTRIBUTING.md) file for guidelines on how to contribute.

## Author

1. **Kolja Nolte** (kolja.nolte@gmail.com)

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
