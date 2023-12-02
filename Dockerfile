FROM wordpress:latest

# Install WooCommerce plugin dependencies
RUN apt-get update && apt-get install -y unzip curl nano

# Download and install WooCommerce plugin
RUN curl -SL "https://downloads.wordpress.org/plugin/woocommerce.zip" -o /tmp/woocommerce.zip && \
    unzip /tmp/woocommerce.zip -d /usr/src/wordpress/wp-content/plugins/ && \
    rm /tmp/woocommerce.zip

# Set proper file permissions
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]