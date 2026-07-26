# ZipFileServer - Technische Dokumentation

## Architektur

### Klassenstruktur

ZipFileServer
├── __construct(welcomeFile, allowListing, maxFileSize, cacheDuration)
├── handleRequest()           // Haupt-Router
├── listAvailableZips()       // ZIP-Ubersicht (kein PATH_INFO)
├── serveFile(path)           // Einzeldatei ausliefern
├── serveDirectoryListing()   // Verzeichnisliste generieren
└── redirect(url)             // 301 Weiterleitung

Helper Functions:
├── getMimeType(filePath)     // MIME-Type Erkennung
├── sendErrorPage(code, uri)  // Fehlerseiten
└── generateDirectoryListing()// HTML Verzeichnisliste

### Ablaufdiagramm

Request
  │
  ├─ PATH_INFO leer?
  │   └─ Ja → listAvailableZips() → HTML Ubersicht
  │
  └─ PATH_INFO vorhanden?
      │
      ├─ ZIP-Datei im Pfad suchen
      │   └─ Nicht gefunden → 404
      │
      ├─ Gefunden → ZipArchive::open()
      │   └─ Fehler → 500
      │
      ├─ Root-Verzeichnis angefragt?
      │   └─ Ja → serveDirectoryListing('')
      │
      ├─ Einzeldatei?
      │   ├─ Gefunden → serveFile() → 200 + Content
      │   └─ Nicht gefunden → Weiter prufen
      │
      ├─ Ordner mit Welcome-File?
      │   ├─ Gefunden → serveFile() oder 301 Redirect
      │   └─ Nicht gefunden → Directory Listing (wenn erlaubt)
      │
      └─ Nichts gefunden → 404

### URL-Parsing

URL: /zfp.php/archive.zip/documents/report.pdf

1. PATH_INFO = "/archive.zip/documents/report.pdf"
2. Zerlegen: ["archive.zip", "documents", "report.pdf"]
3. Dateisystem-Suche von links:
   - __DIR__/archive.zip → Datei gefunden ✓
4. Interner Pfad: "documents/report.pdf"
5. In ZIP suchen: ZipArchive::statName("documents/report.pdf")

### Sicherheitskonzept

| Bedrohung | Schutzmassnahme |
|-----------|-----------------|
| Path Traversal | Pfad wird nur innerhalb ZIP interpretiert |
| Memory Exhaustion | $maxFileSize begrenzt Extraktion |
| MIME Sniffing | X-Content-Type-Options: nosniff |
| Directory Listing | $allowListing konfigurierbar |
| ZIP Bombs | Dateigrossen-Check vor Extraktion |

### MIME-Type Erkennung

1. Extension-basiert (schnell): Match auf Dateiendung
2. Content-basiert (Fallback): finfo analysiert Dateiinhalt
3. Default: application/octet-stream

Unterstutzt: HTML, CSS, JS, JSON, PDF, ZIP, JPEG, PNG, GIF, WebP, SVG, MP3, MP4, WebM, WOFF2, AVIF, WASM

### HTTP Status Codes

| Code | Bedeutung |
|------|-----------|
| 200 | Datei erfolgreich ausgeliefert |
| 301 | Weiterleitung auf Ordner (mit /) |
| 403 | Verzeichniszugriff nicht erlaubt |
| 404 | Datei/Verzeichnis nicht gefunden |
| 500 | Server-Fehler (ZIP lesen, etc.) |

### Performance

- Cache-Control: Browser-Caching fur statische Dateien
- Keine Extraktion: Dateien werden direkt aus ZIP gelesen
- MIME-Cache: Extension-basierte Erkennung vermeidet teure finfo-Aufrufe

### PHP-Abhangigkeiten

| Extension | Verwendung |
|-----------|------------|
| zip | ZipArchive Klasse |
| fileinfo | MIME-Type Erkennung (finfo) |

### Kompatibilitat

- PHP 8.2+
- Nutzt: match expressions, typed properties, str_starts_with, str_ends_with