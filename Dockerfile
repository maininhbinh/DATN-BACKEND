# Sử dụng hình ảnh PHP chính thức
FROM php:8.1-fpm

# Cài đặt các extension PHP cần thiết
RUN docker-php-ext-install pdo pdo_mysql

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Sao chép mã nguồn vào container
COPY . /var/www/html

# Đặt thư mục làm việc
WORKDIR /var/www/html

# Chạy lệnh composer install để cài đặt các gói phụ thuộc
RUN composer install

# Thiết lập quyền sở hữu và quyền truy cập cho thư mục
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Khởi động dịch vụ PHP-FPM
CMD ["php-fpm"]
