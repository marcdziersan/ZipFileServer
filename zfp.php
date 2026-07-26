<?php
declare(strict_types=1);

/**
 * =============================================================================
 * ZIP File Server (zfp.php)
 * =============================================================================
 * 
 * Serves files directly from ZIP archives with optional directory listing.
 * Modernized version for PHP 8.2+ (2026)
 * 
 * -----------------------------------------------------------------------------
 * METADATA
 * -----------------------------------------------------------------------------
 * 
 * @package     ZipFileServer
 * @version     2.1.0
 * @author      Marcus Dziersan
 * @since       2019 (initial version)
 * @copyright   2019-2026 Marcus Dziersan
 * @license     MIT License
 * 
 * =============================================================================
 */

// -----------------------------------------------------------------------------
// CONFIGURATION
// -----------------------------------------------------------------------------

$welcomeFile = 'index.html';
$allowListing = true;
$maxFileSize = 100 * 1024 * 1024; // 100 MB
$cacheDuration = 3600;

// Code-Viewer Konfiguration
$codeExtensions = ['txt', 'md', 'php', 'js', 'json', 'html', 'htm', 'css', 'xml', 'yaml', 'yml', 'ini', 'conf', 'log', 'sh', 'bat', 'py', 'rb', 'java', 'c', 'cpp', 'h', 'sql', 'csv', 'tsv'];
$codeMaxSize = 1024 * 1024; // 1 MB max fur Code-Ansicht

// -----------------------------------------------------------------------------
// ERROR HANDLING
// -----------------------------------------------------------------------------

set_error_handler(function(int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// -----------------------------------------------------------------------------
// MAIN EXECUTION
// -----------------------------------------------------------------------------

try {
    $server = new ZipFileServer($welcomeFile, $allowListing, $maxFileSize, $cacheDuration, $codeExtensions, $codeMaxSize);
    $server->handleRequest();
} catch (Throwable $e) {
    http_response_code(500);
    sendErrorPage(500, $_SERVER['REQUEST_URI'] ?? '/');
    error_log("ZipFileServer Error: " . $e->getMessage());
}

// -----------------------------------------------------------------------------
// HELPER FUNCTIONS
// -----------------------------------------------------------------------------

function getMimeType(string $filePath): string 
{
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    $mimeType = match($extension) {
        'html', 'htm' => 'text/html',
        'css' => 'text/css',
        'js', 'mjs' => 'text/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'pdf' => 'application/pdf',
        'zip' => 'application/zip',
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'audio/ogg',
        'woff2' => 'font/woff2',
        'woff' => 'font/woff',
        'ttf' => 'font/ttf',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'md' => 'text/markdown',
        'yaml', 'yml' => 'text/yaml',
        'wasm' => 'application/wasm',
        'avif' => 'image/avif',
        default => null
    };
    
    if ($mimeType !== null) {
        return $mimeType;
    }
    
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        return $detectedType ?: 'application/octet-stream';
    }
    
    return 'application/octet-stream';
}

function getLanguageClass(string $extension): string 
{
    return match($extension) {
        'php' => 'language-php',
        'js', 'mjs' => 'language-javascript',
        'json' => 'language-json',
        'html', 'htm' => 'language-html',
        'css' => 'language-css',
        'xml' => 'language-xml',
        'yaml', 'yml' => 'language-yaml',
        'md' => 'language-markdown',
        'sql' => 'language-sql',
        'py' => 'language-python',
        'rb' => 'language-ruby',
        'java' => 'language-java',
        'c', 'h' => 'language-c',
        'cpp', 'hpp' => 'language-cpp',
        'sh', 'bat' => 'language-bash',
        default => 'language-plain'
    };
}

function sendErrorPage(int $statusCode, string $requestUri, ?string $redirectUrl = null): void 
{
    http_response_code($statusCode);
    
    $titles = [
        301 => 'Moved Permanently',
        403 => 'Forbidden', 
        404 => 'Not Found',
        500 => 'Internal Server Error'
    ];
    
    $messages = [
        301 => 'The document has been permanently moved.',
        403 => 'You don\'t have permission to access this resource.',
        404 => 'The requested URL was not found on this server.',
        500 => 'The server encountered an internal error.'
    ];
    
    $title = $titles[$statusCode] ?? 'Error';
    $message = $messages[$statusCode] ?? 'An error occurred.';
    $sanitizedUri = htmlspecialchars($requestUri, ENT_QUOTES, 'UTF-8');
    
    if ($statusCode === 301 && $redirectUrl) {
        header("Location: " . $redirectUrl);
    }
    
    header('Content-Type: text/html; charset=utf-8');
    
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{$statusCode} {$title} - ZipFileServer</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: system-ui, -apple-system, sans-serif;
                background: #f5f5f5; color: #333;
                display: flex; justify-content: center; align-items: center;
                min-height: 100vh; padding: 20px;
            }
            .error-container {
                background: white; border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                padding: 40px; max-width: 600px; width: 100%;
            }
            h1 { font-size: 2em; margin-bottom: 20px; color: #cc0000; }
            p { margin-bottom: 15px; line-height: 1.6; }
            .uri { background: #f8f8f8; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
            .back-link { display: inline-block; margin-top: 20px; color: #0066cc; text-decoration: none; }
            .back-link:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>{$title}</h1>
            <p>{$message}</p>
            <p>URL: <span class="uri">{$sanitizedUri}</span></p>
            <a href="./" class="back-link">← Zurück zur Übersicht</a>
        </div>
    </body>
    </html>
    HTML;
}

function generateDirectoryListing(string $baseUri, array $entries, bool $isRoot, string $zipName = ''): void 
{
    $sanitizedUri = htmlspecialchars($baseUri, ENT_QUOTES, 'UTF-8');
    
    header('Content-Type: text/html; charset=utf-8');
    
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Index of <?= $sanitizedUri ?> - ZipFileServer</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: system-ui, -apple-system, sans-serif;
                background: #fafafa; color: #333; padding: 20px;
            }
            .container {
                max-width: 800px; margin: 0 auto; background: white;
                border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                padding: 30px;
            }
            .breadcrumb {
                margin-bottom: 20px; padding: 10px;
                background: #f8f9fa; border-radius: 4px; font-size: 0.9em;
            }
            .breadcrumb a { color: #0066cc; text-decoration: none; }
            .breadcrumb a:hover { text-decoration: underline; }
            h1 {
                font-size: 1.5em; margin-bottom: 20px;
                padding-bottom: 10px; border-bottom: 2px solid #eee;
            }
            ul { list-style: none; }
            li { margin-bottom: 5px; }
            a {
                display: flex; align-items: center; padding: 8px 12px;
                color: #0066cc; text-decoration: none; border-radius: 4px;
                transition: background-color 0.2s;
            }
            a:hover { background-color: #f0f7ff; }
            .icon { margin-right: 10px; font-size: 1.2em; width: 24px; text-align: center; }
            .parent-dir { font-weight: bold; margin-bottom: 10px; }
            .zip-info { color: #666; font-size: 0.9em; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="breadcrumb">
                <?php $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/zfp.php'; ?>
                <a href="<?= htmlspecialchars($scriptName) ?>" class="home-btn">🏠 Home</a>
                <a href="./">📦 Start</a>
                <?php if ($zipName): ?>
                    / <strong><?= htmlspecialchars($zipName) ?></strong>
                <?php endif; ?>
            </div>
            <h1>📁 Index of <?= $sanitizedUri ?></h1>
            <?php if ($zipName): ?>
                <div class="zip-info">🗜️ Archiv: <?= htmlspecialchars($zipName) ?></div>
            <?php endif; ?>
            <ul>
                <?php if (!$isRoot): ?>
                <li class="parent-dir">
                    <a href="../">
                        <span class="icon">📂</span> Parent Directory
                    </a>
                </li>
                <?php endif; ?>
                
                <?php foreach ($entries as $entry): ?>
                <li>
                    <a href="<?= htmlspecialchars(rawurlencode(rtrim($entry, '/'))) ?>">
                        <span class="icon"><?= str_ends_with($entry, '/') ? '📁' : '📄' ?></span>
                        <?= htmlspecialchars($entry) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </body>
    </html>
    <?php
}

function highlightCode(string $code, string $extension): string 
{
    if (in_array($extension, ['php', 'html', 'htm'])) {
        $code = preg_replace(
            '/(&lt;\/?)([\w-]+)(.*?)(\/?&gt;)/',
            '$1<span class="tag">$2</span>$3$4',
            $code
        );
        $code = preg_replace(
            '/(\s)([\w-]+)=(&quot;.*?&quot;)/',
            '$1<span class="attr">$2</span>=$3',
            $code
        );
    }
    
    if (in_array($extension, ['php', 'js', 'json', 'css'])) {
        $code = preg_replace(
            '/(&quot;.*?&quot;|&#039;.*?&#039;)/',
            '<span class="str">$1</span>',
            $code
        );
    }
    
    if (in_array($extension, ['php', 'js', 'css', 'java', 'c', 'cpp'])) {
        $code = preg_replace(
            '/(\/\/.*?$|#.*?$)/m',
            '<span class="cm">$1</span>',
            $code
        );
        $code = preg_replace(
            '/(\/\*.*?\*\/)/s',
            '<span class="cm">$1</span>',
            $code
        );
    }
    
    $code = preg_replace(
        '/\b(\d+\.?\d*)\b/',
        '<span class="num">$1</span>',
        $code
    );
    
    if ($extension === 'php') {
        $keywords = ['function', 'class', 'public', 'private', 'protected', 'static', 'return', 'if', 'else', 'foreach', 'for', 'while', 'new', 'try', 'catch', 'throw', 'echo', 'match', 'null', 'true', 'false', 'use', 'namespace', 'extends', 'implements', 'interface', 'trait', 'final', 'abstract', 'readonly'];
        foreach ($keywords as $kw) {
            $code = preg_replace(
                '/\b(' . $kw . ')\b/',
                '<span class="kw">$1</span>',
                $code
            );
        }
    }
    
    return $code;
}

function generateCodeViewer(string $content, string $filename, string $languageClass, int $lineCount, string $backUrl, string $rawUrl): void 
{
    $safeFilename = htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');
    $safeBackUrl = htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8');
    $safeRawUrl = htmlspecialchars($rawUrl, ENT_QUOTES, 'UTF-8');
    
    header('Content-Type: text/html; charset=utf-8');
    
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $safeFilename ?> - ZipFileServer Code Viewer</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: system-ui, -apple-system, sans-serif;
                background: #1e1e1e; color: #d4d4d4;
                min-height: 100vh;
            }
            .toolbar {
                background: #2d2d2d;
                padding: 12px 20px;
                display: flex;
                align-items: center;
                gap: 15px;
                border-bottom: 1px solid #404040;
                position: sticky;
                top: 0;
                z-index: 100;
            }
            .toolbar a {
                color: #cccccc;
                text-decoration: none;
                padding: 6px 12px;
                border-radius: 4px;
                font-size: 0.9em;
                transition: background 0.2s;
            }
            .toolbar a:hover { background: #404040; }
            .toolbar .back-btn { 
                background: #0e639c; 
                color: white;
            }
            .toolbar .back-btn:hover { background: #1177bb; }
            .toolbar .raw-btn { 
                background: #404040; 
            }
            .toolbar .raw-btn:hover { background: #505050; }
            .toolbar .home-btn {
                background: #404040;
            }
            .toolbar .home-btn:hover { background: #505050; }
            .file-info {
                margin-left: auto;
                color: #999;
                font-size: 0.85em;
            }
            .code-container {
                display: flex;
                min-height: calc(100vh - 53px);
            }
            .line-numbers {
                background: #252526;
                color: #858585;
                padding: 15px 10px;
                text-align: right;
                user-select: none;
                min-width: 60px;
                border-right: 1px solid #404040;
            }
            .line-numbers span {
                display: block;
                line-height: 1.5;
                font-family: 'Fira Code', 'Consolas', 'Monaco', monospace;
                font-size: 14px;
                padding: 0 8px;
            }
            .code-content {
                flex: 1;
                padding: 15px;
                overflow-x: auto;
            }
            .code-content pre {
                margin: 0;
                line-height: 1.5;
                font-family: 'Fira Code', 'Consolas', 'Monaco', monospace;
                font-size: 14px;
                white-space: pre-wrap;
                word-wrap: break-word;
            }
            .kw { color: #569cd6; font-weight: bold; }
            .str { color: #ce9178; }
            .num { color: #b5cea8; }
            .cm { color: #6a9955; font-style: italic; }
            .tag { color: #569cd6; }
            .attr { color: #9cdcfe; }
            @media (max-width: 768px) {
                .toolbar { flex-wrap: wrap; gap: 10px; }
                .file-info { margin-left: 0; width: 100%; }
            }
        </style>
    </head>
    <body>
        <div class="toolbar">
            <a href="<?= $safeBackUrl ?>" class="back-btn">← Zurück zum Ordner</a>
            <a href="./" class="home-btn">🏠 Start</a>
            <a href="<?= $safeRawUrl ?>" class="raw-btn">📄 Rohdaten</a>
            <span class="file-info"><?= $safeFilename ?> • <?= $lineCount ?> Zeilen</span>
        </div>
        <div class="code-container">
            <div class="line-numbers">
                <?php for ($i = 1; $i <= $lineCount; $i++): ?>
                    <span><?= $i ?></span>
                <?php endfor; ?>
            </div>
            <div class="code-content">
                <pre><code class="<?= $languageClass ?>"><?= $content ?></code></pre>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// -----------------------------------------------------------------------------
// MAIN CLASS
// -----------------------------------------------------------------------------

class ZipFileServer 
{
    private string $welcomeFile;
    private bool $allowListing;
    private int $maxFileSize;
    private int $cacheDuration;
    private array $codeExtensions;
    private int $codeMaxSize;
    private ?ZipArchive $zipArchive = null;
    
    public function __construct(string $welcomeFile, bool $allowListing, int $maxFileSize, int $cacheDuration, array $codeExtensions, int $codeMaxSize) 
    {
        $this->welcomeFile = $welcomeFile;
        $this->allowListing = $allowListing;
        $this->maxFileSize = $maxFileSize;
        $this->cacheDuration = $cacheDuration;
        $this->codeExtensions = $codeExtensions;
        $this->codeMaxSize = $codeMaxSize;
    }
    
    public function handleRequest(): void 
    {
        if (empty($_SERVER['PATH_INFO']) || $_SERVER['PATH_INFO'] === '/') {
            $this->listAvailableZips();
            return;
        }
        
        $pathInfo = ltrim($_SERVER['PATH_INFO'], '/');
        
        if (empty($pathInfo)) {
            $this->listAvailableZips();
            return;
        }
        
        $pathParts = explode('/', $pathInfo);
        $zipPath = __DIR__;
        $internalPath = '';
        $zipFound = false;
        
        foreach ($pathParts as $part) {
            if (!$zipFound) {
                $zipPath .= DIRECTORY_SEPARATOR . $part;
                
                if (is_dir($zipPath)) {
                    continue;
                } elseif (is_file($zipPath)) {
                    $zipFound = true;
                } else {
                    break;
                }
            } else {
                $internalPath = ($internalPath === '') ? $part : $internalPath . '/' . $part;
            }
        }
        
        if (!$zipFound) {
            sendErrorPage(404, $_SERVER['REQUEST_URI'] ?? '/');
            return;
        }
        
        $isRootRequest = ($internalPath === '');
        $isFolderRequest = str_ends_with($internalPath, '/');
        
        if ($isFolderRequest) {
            $internalPath = rtrim($internalPath, '/');
        }
        
        if (($isRootRequest || $isFolderRequest) && !$this->allowListing) {
            sendErrorPage(403, $_SERVER['REQUEST_URI'] ?? '/');
            return;
        }
        
        if (!class_exists('ZipArchive')) {
            sendErrorPage(500, $_SERVER['REQUEST_URI'] ?? '/');
            error_log("ZipFileServer: ZipArchive class not available");
            return;
        }
        
        $this->zipArchive = new ZipArchive();
        $openResult = $this->zipArchive->open($zipPath);
        
        if ($openResult !== true) {
            sendErrorPage(500, $_SERVER['REQUEST_URI'] ?? '/');
            error_log("ZipFileServer: Failed to open ZIP: $zipPath (Error: $openResult)");
            return;
        }
        
        try {
            if ($isRootRequest && $this->allowListing) {
                $this->serveDirectoryListing('', basename($zipPath));
                return;
            }
            
            if ($this->serveFile($internalPath)) {
                return;
            }
            
            if ($isFolderRequest) {
                $welcomePath = ltrim($internalPath . '/' . $this->welcomeFile, '/');
                
                if ($this->serveFile($welcomePath)) {
                    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
                    if (!str_ends_with($requestUri, '/')) {
                        $this->redirect($requestUri . '/');
                    }
                    return;
                }
                
                if ($this->allowListing) {
                    $this->serveDirectoryListing($internalPath . '/', basename($zipPath));
                    return;
                }
            }
            
            $welcomePath = ltrim($internalPath . '/' . $this->welcomeFile, '/');
            
            if ($this->serveFile($welcomePath)) {
                $requestUri = $_SERVER['REQUEST_URI'] ?? '';
                if (!str_ends_with($requestUri, '/')) {
                    $this->redirect($requestUri . '/');
                }
                return;
            }
            
            sendErrorPage(404, $_SERVER['REQUEST_URI'] ?? '/');
            
        } finally {
            $this->zipArchive->close();
        }
    }
    
    private function listAvailableZips(): void 
    {
        $zipFiles = glob(__DIR__ . '/*.zip');
        
        if (empty($zipFiles)) {
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html>
            <html lang="de">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Keine ZIP-Dateien - ZipFileServer</title>
                <style>
                    body { 
                        font-family: system-ui, -apple-system, sans-serif;
                        display: flex; justify-content: center; align-items: center;
                        min-height: 100vh; margin: 0; background: #f5f5f5;
                    }
                    .message {
                        background: white; padding: 40px; border-radius: 12px;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-align: center;
                        max-width: 500px;
                    }
                    h1 { color: #333; margin-bottom: 15px; }
                    p { color: #666; line-height: 1.6; }
                    .icon { font-size: 4em; margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <div class="message">
                    <div class="icon">📦</div>
                    <h1>Keine ZIP-Archive gefunden</h1>
                    <p>Legen Sie ZIP-Dateien in dieses Verzeichnis.</p>
                </div>
            </body>
            </html>';
            return;
        }
        
        sort($zipFiles);
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/zfp.php';
        
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html lang="de">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>ZIP-Archive - ZipFileServer</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: system-ui, -apple-system, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh; padding: 20px;
                }
                .container {
                    max-width: 900px; margin: 40px auto; background: white;
                    border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    padding: 40px;
                }
                .header { text-align: center; margin-bottom: 40px; }
                h1 { font-size: 2.5em; color: #333; margin-bottom: 10px; }
                .subtitle { color: #666; font-size: 1.1em; }
                .zip-grid { display: grid; gap: 15px; }
                .zip-item {
                    display: flex; align-items: center; padding: 20px;
                    background: #f8f9fa; border-radius: 12px; text-decoration: none;
                    color: #333; transition: all 0.3s ease; border: 2px solid transparent;
                }
                .zip-item:hover {
                    background: white; transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-color: #667eea;
                }
                .zip-icon {
                    font-size: 2.5em; margin-right: 20px; width: 60px; height: 60px;
                    display: flex; align-items: center; justify-content: center;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-radius: 12px;
                }
                .zip-info { flex: 1; }
                .zip-name { font-weight: 600; font-size: 1.2em; color: #333; margin-bottom: 5px; }
                .zip-meta { display: flex; gap: 20px; font-size: 0.9em; color: #666; }
                .zip-size { color: #764ba2; font-weight: 500; }
                .zip-date { color: #999; }
                .zip-arrow {
                    font-size: 1.5em; color: #667eea; margin-left: 15px;
                    transition: transform 0.3s ease;
                }
                .zip-item:hover .zip-arrow { transform: translateX(5px); }
                .stats {
                    display: flex; justify-content: center; gap: 30px;
                    margin-top: 30px; padding: 20px; background: #f8f9fa;
                    border-radius: 8px; text-align: center;
                }
                .stat-value { font-size: 1.5em; font-weight: bold; color: #333; }
                .stat-label { font-size: 0.9em; margin-top: 5px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>📦 ZIP-Archive</h1>
                    <p class="subtitle">Wählen Sie ein Archiv zum Durchsuchen:</p>
                </div>
                
                <div class="zip-grid">
                    <?php 
                    $totalSize = 0;
                    foreach ($zipFiles as $zipPath): 
                        $zipName = basename($zipPath);
                        $zipSize = filesize($zipPath);
                        $totalSize += $zipSize;
                        $zipDate = filemtime($zipPath);
                        $zipUrl = $scriptName . '/' . rawurlencode($zipName) . '/';
                        
                        if ($zipSize > 1073741824) {
                            $sizeText = round($zipSize / 1073741824, 2) . ' GB';
                        } elseif ($zipSize > 1048576) {
                            $sizeText = round($zipSize / 1048576, 1) . ' MB';
                        } elseif ($zipSize > 1024) {
                            $sizeText = round($zipSize / 1024, 1) . ' KB';
                        } else {
                            $sizeText = $zipSize . ' Bytes';
                        }
                        
                        $dateText = date('d.m.Y H:i', $zipDate);
                    ?>
                    <a href="<?= htmlspecialchars($zipUrl) ?>" class="zip-item">
                        <div class="zip-icon">🗜️</div>
                        <div class="zip-info">
                            <div class="zip-name"><?= htmlspecialchars($zipName) ?></div>
                            <div class="zip-meta">
                                <span class="zip-size"><?= $sizeText ?></span>
                                <span class="zip-date"><?= $dateText ?></span>
                            </div>
                        </div>
                        <span class="zip-arrow">→</span>
                    </a>
                    <?php endforeach; ?>
                </div>
                
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value"><?= count($zipFiles) ?></div>
                        <div class="stat-label">Archive</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">
                            <?php 
                            if ($totalSize > 1073741824) {
                                echo round($totalSize / 1073741824, 2) . ' GB';
                            } elseif ($totalSize > 1048576) {
                                echo round($totalSize / 1048576, 1) . ' MB';
                            } else {
                                echo round($totalSize / 1024, 1) . ' KB';
                            }
                            ?>
                        </div>
                        <div class="stat-label">Gesamtgröße</div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
    
    private function serveFile(string $path): bool 
    {
        $path = trim($path, '/');
        
        $statResult = $this->zipArchive->statName($path);
        if ($statResult === false) {
            return false;
        }
        
        if ($statResult['size'] > $this->maxFileSize) {
            sendErrorPage(500, $_SERVER['REQUEST_URI'] ?? '/');
            error_log("ZipFileServer: File too large: $path ({$statResult['size']} bytes)");
            return true;
        }
        
        $content = $this->zipArchive->getFromName($path);
        if ($content === false) {
            return false;
        }
        
        $isRaw = isset($_GET['raw']) && $_GET['raw'] === '1';
        
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $isCodeFile = in_array($extension, $this->codeExtensions);
        
        if ($isCodeFile && !$isRaw && $statResult['size'] <= $this->codeMaxSize) {
            $this->serveCodeFile($content, $path, $extension);
            return true;
        }
        
        $mimeType = getMimeType($path);
        
        header("Content-Type: $mimeType", true, 200);
        header("Content-Length: " . $statResult['size']);
        header("Cache-Control: public, max-age=" . $this->cacheDuration);
        header("X-Content-Type-Options: nosniff");
        
        echo $content;
        
        return true;
    }
    
    private function serveCodeFile(string $content, string $path, string $extension): void 
    {
        $filename = basename($path);
        $languageClass = getLanguageClass($extension);
        $lines = explode("\n", $content);
        $lineCount = count($lines);
        
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $requestUri = explode('?', $requestUri)[0];
        
        $backUrl = dirname($requestUri);
        if ($backUrl === '.' || $backUrl === '\\') {
            $backUrl = './';
        } else {
            $backUrl .= '/';
        }
        
        $rawUrl = $requestUri . '?raw=1';
        
        $highlightedContent = highlightCode(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'), $extension);
        
        generateCodeViewer($highlightedContent, $filename, $languageClass, $lineCount, $backUrl, $rawUrl);
    }
    
    private function serveDirectoryListing(string $directory, string $zipName = ''): void 
    {
        $requestUri = explode('?', $_SERVER['REQUEST_URI'] ?? '/')[0];
        if (!str_ends_with($requestUri, '/')) {
            $requestUri .= '/';
        }
        
        $entries = [];
        
        for ($i = 0; $i < $this->zipArchive->numFiles; $i++) {
            $entryName = $this->zipArchive->getNameIndex($i);
            
            if ($entryName === false) continue;
            
            if (empty($directory)) {
                $slashPos = strpos($entryName, '/');
                if ($slashPos === false) {
                    $entries[$entryName] = true;
                } else {
                    $dirName = substr($entryName, 0, $slashPos + 1);
                    $entries[$dirName] = true;
                }
            } elseif (str_starts_with($entryName, $directory) && $entryName !== $directory) {
                $relativePath = substr($entryName, strlen($directory));
                $slashPos = strpos($relativePath, '/');
                
                if ($slashPos === false) {
                    $entries[$relativePath] = true;
                } else {
                    $dirName = substr($relativePath, 0, $slashPos + 1);
                    $entries[$dirName] = true;
                }
            }
        }
        
        ksort($entries);
        $entryList = array_keys($entries);
        
        generateDirectoryListing($requestUri, $entryList, empty($directory), $zipName);
    }
    
    private function redirect(string $url): void 
    {
        sendErrorPage(301, $url, $url);
        exit;
    }
}

restore_error_handler();