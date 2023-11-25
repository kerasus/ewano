FROM wordpress:latest

# Install WooCommerce plugin dependencies
RUN apt-get update && apt-get install -y unzip curl nano

# Download and install WooCommerce plugin
RUN curl -SL "https://downloads.wordpress.org/plugin/woocommerce.zip" -o /tmp/woocommerce.zip && \
    unzip /tmp/woocommerce.zip -d /usr/src/wordpress/wp-content/plugins/ && \
    rm /tmp/woocommerce.zip

## Copy your custom plugin into the container
#COPY ./ewano /var/www/html/wp-content/plugins/ewano

# Set up WordPress configuration file for development environment
RUN echo "define('WP_DEBUG', true);" >> /var/www/html/wp-config.php && \
    echo "define('WP_DEBUG_LOG', true);" >> /var/www/html/wp-config.php && \
    echo "define('SCRIPT_DEBUG', true);" >> /var/www/html/wp-config.php

# Set proper file permissions
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]