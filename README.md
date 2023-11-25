# Ewano plugin for wordpress

## build docker image
```bash
docker-compose up --build -d
```
#active debugger
```bash
docker exec -it ewano_wordpress_1 /bin/bash

nano wp-config.php

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('SCRIPT_DEBUG', true);
```