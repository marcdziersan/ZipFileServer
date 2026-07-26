# 📦 ZipFileServer (zfp.php)

A modern, single-file PHP application for browsing and serving files directly from ZIP archives without extraction.

![Version](https://img.shields.io/badge/version-2.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple)
![License](https://img.shields.io/badge/license-MIT-green)

## ✨ Features

- 📦 Browse multiple ZIP archives in a directory
- 📁 Navigate ZIP contents like a filesystem
- 📄 Serve individual files with correct MIME types
- 🎨 Modern, responsive UI with CSS Grid and Flexbox
- 🔒 Security: file size limits, path traversal protection
- 📊 Archive statistics: file count, total size, dates
- ⚡ Fast: No extraction needed, direct from ZIP
- 📱 Mobile-friendly responsive design

## 🚀 Quick Start

1. Download `zfp.php`
2. Place it in a directory with your ZIP files
3. Access via browser: `http://localhost/zfp.php`

That's it! No configuration needed.

## 📖 Usage

| URL | Description |
|-----|-------------|
| /zfp.php | List all available ZIP archives |
| /zfp.php/archive.zip/ | Browse contents of archive.zip |
| /zfp.php/archive.zip/file.pdf | Download/view file.pdf |
| /zfp.php/archive.zip/docs/ | Browse docs/ folder in ZIP |

## ⚙️ Configuration

Edit these variables at the top of `zfp.php`:

$welcomeFile = 'index.html';
$allowListing = true;
$maxFileSize = 100 * 1024 * 1024;
$cacheDuration = 3600;

## 📋 Requirements

- PHP 8.2 or higher
- PHP Extensions: zip (ZipArchive), fileinfo (MIME detection)

## 🛡️ Security

- Files are served directly from ZIP - no extraction to disk
- Path traversal is prevented by design
- File size limits prevent memory exhaustion
- MIME type validation prevents XSS
- No external file access beyond script directory

## 📁 Project Structure

your-zip-directory/
├── zfp.php
├── archive1.zip
├── archive2.zip
└── ...

## 🔧 Troubleshooting

ERR_SSL_PROTOCOL_ERROR on localhost:
Use http://localhost/zfp.php instead of https://

ZipArchive class not available:
Enable zip extension in php.ini: extension=zip

Large files timeout:
Increase $maxFileSize in configuration

## 📝 Changelog

v2.0.0 (2026-07-27) - Complete rewrite for PHP 8.2+
v1.0.0 (2019-02-18) - Initial release

## 👤 Author

Marcus Dziersan

## 📄 License

MIT License - See LICENSE file for details.