# Changelog

## [2.0.0] - 2026-07-27

### Added
- ZIP archive overview page with statistics
- Modern UI with CSS gradients, animations, and responsive design
- Breadcrumb navigation for ZIP contents
- File size and modification date display
- Cache-Control headers for better performance
- X-Content-Type-Options header for security
- Support for WebP, AVIF, WASM MIME types
- Empty state page when no ZIPs found
- 301 redirects for directory URLs without trailing slash

### Changed
- Complete rewrite for PHP 8.2+
- Replaced zip_* functions with ZipArchive class
- Moved to object-oriented architecture
- Improved error handling with exceptions
- Modern CSS with system-ui font stack
- Simplified MIME type detection with match expression
- Better URL generation using SCRIPT_NAME

### Fixed
- URL generation creating invalid https://archive.zip/ links
- Path traversal in internal ZIP paths
- Memory issues with large files

### Removed
- Legacy zip_open() / zip_read() API
- Deprecated do-while(FALSE) error handling pattern
- Old-style array access on function returns

## [1.0.0] - 2019-02-18

### Added
- Initial release
- Basic ZIP file serving via PATH_INFO
- Directory listing with HTML output
- MIME type mapping for common formats
- Welcome file support (index.html)
- Error pages for 403, 404, 500