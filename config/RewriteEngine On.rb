RewriteEngine On

# Set environment variable for project folder
SetEnvIf Request_URI "^/agoda2/" PROJECT_FOLDER=agoda2

# Remove /htdocs from URL
RewriteCond %{THE_REQUEST} ^[A-Z]{3,}\s/+%{ENV:PROJECT_FOLDER}/htdocs/([^\s]*) [NC]
RewriteRule ^ /%{ENV:PROJECT_FOLDER}/%1 [R=301,L]

RewriteBase /%{ENV:PROJECT_FOLDER}/

# Skip existing files and directories  
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Route everything to index.php
RewriteRule ^(.*)$ index.php [QSA,L]

# Block direct access to app directory
RewriteRule ^app/ - [F,L]
