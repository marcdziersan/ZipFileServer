# ZipFileServer .htaccess (Optional)

# zfp.php als Standard-Datei
DirectoryIndex zfp.php

# Verhindere Auflistung von ZIP-Dateien durch Apache
Options -Indexes

# Erlaube Zugriff nur auf zfp.php
<FilesMatch "\.(zip)$">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "^zfp\.php$">
    Order allow,deny
    Allow from all
</FilesMatch>