# ZipFileServer - Benutzerhandbuch

## Installation

### Schritt 1: PHP prufen
Stellen Sie sicher, dass PHP 8.2 oder hoher installiert ist:
php -v

### Schritt 2: Erweiterungen aktivieren
In php.ini sicherstellen:
extension=zip
extension=fileinfo

### Schritt 3: zfp.php platzieren
Kopieren Sie zfp.php in das Verzeichnis mit Ihren ZIP-Dateien:

/var/www/html/archiv/
├── zfp.php
├── dokumente.zip
├── bilder.zip
└── software.zip

### Schritt 4: Aufrufen
Offnen Sie im Browser:
http://localhost/archiv/zfp.php

## Verwendung

### Startseite
Die Startseite zeigt alle verfugbaren ZIP-Archive mit:
- Dateiname
- Grosse
- Anderungsdatum
- Gesamtstatistik

### ZIP-Inhalt durchsuchen
Klicken Sie auf ein ZIP-Archiv, um dessen Inhalt zu sehen:
http://localhost/archiv/zfp.php/dokumente.zip/

### Ordner navigieren
- Klicken Sie auf Ordner, um tiefer zu navigieren
- Klicken Sie auf "Parent Directory", um eine Ebene hoher zu gehen
- Uber die Breadcrumb-Leiste konnen Sie zur Startseite zuruck

### Dateien anzeigen/herunterladen
Klicken Sie auf eine Datei, um sie im Browser anzuzeigen:
http://localhost/archiv/zfp.php/dokumente.zip/berichte/2024.pdf

## Konfiguration

Offnen Sie zfp.php und passen Sie die Einstellungen an:

### Welcome-Datei
$welcomeFile = 'index.html';
Wenn ein Ordner eine Datei mit diesem Namen enthalt, wird sie automatisch angezeigt.

### Verzeichnisliste
$allowListing = true;   // Zeigt Ordnerinhalte an
$allowListing = false;  // Versteckt Ordnerinhalte (404)

### Maximale Dateigrosse
$maxFileSize = 100 * 1024 * 1024; // 100 MB
$maxFileSize = 500 * 1024 * 1024; // 500 MB

### Cache-Dauer
$cacheDuration = 3600;  // 1 Stunde
$cacheDuration = 86400; // 24 Stunden

## Tipps & Tricks

### Als Startseite einrichten
Erstellen Sie eine .htaccess Datei:
DirectoryIndex zfp.php

### Direktlinks erstellen
Sie konnen direkte Links zu Dateien in ZIPs teilen:
https://ihre-domain.de/archiv/zfp.php/software.zip/readme.txt

## Fehlerbehebung

| Problem | Losung |
|---------|--------|
| Weisse Seite | PHP Error Log prufen |
| ZipArchive not available | extension=zip in php.ini aktivieren |
| 404 bei vorhandener ZIP | Gross-/Kleinschreibung prufen |
| Datei zu gross | $maxFileSize erhohen |
| SSL-Fehler lokal | http:// statt https:// nutzen |

## Sicherheitshinweise

Wichtig:
- Dieses Tool ist fur vertrauenswurdige Umgebungen gedacht
- Keine sensiblen Daten in offentlich zugangliche ZIPs packen
- $allowListing = false setzen, wenn Verzeichnisinhalte privat bleiben sollen
- ZIP-Dateien konnen beliebig gross sein - $maxFileSize begrenzt die Extraktion