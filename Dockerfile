# ====================================================================
# DOCKERFILE - HỆ THỐNG QUẢN LÝ PHÒNG GYM & FITNESS (ĐỀ 23)
# Base Image: PHP 8.3 Apache Official
# Áp dụng Hardening: non-root permission, tắt thông tin phiên bản
# ====================================================================

FROM php:8.3-apache

# Cài đặt các extension kết nối cơ sở dữ liệu MySQL (PDO & MySQLi) theo hướng dẫn Lab
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Kích hoạt module mod_rewrite của Apache
RUN a2enmod rewrite

# Hardening Apache: Tắt hiển thị phiên bản web server và OS trong response header
RUN echo "ServerTokens Prod" >> /etc/apache2/apache2.conf && \
    echo "ServerSignature Off" >> /etc/apache2/apache2.conf

# Sao chép toàn bộ mã nguồn ứng dụng vào DocumentRoot
COPY src/ /var/www/html/

# Phân quyền cho user www-data (Non-root user của Apache) theo đúng chuẩn Lab
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
