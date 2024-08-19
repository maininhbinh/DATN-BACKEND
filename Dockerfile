# Sử dụng hình ảnh PHP chính thức
FROM php:8.1-fpm

# Cài đặt các extension PHP cần thiết
RUN docker-php-ext-install pdo pdo_mysql

# Cài đặt các công cụ hệ thống cần thiết
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    && docker-php-ext-install zip

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Sao chép mã nguồn vào container
COPY . /var/www/html

# Đặt thư mục làm việc
WORKDIR /var/www/html

# Xóa cache Composer trước khi cài đặt
RUN composer clear-cache

# Chạy lệnh composer install để cài đặt các gói phụ thuộc
RUN composer install --ignore-platform-reqs --no-scripts --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Thiết lập quyền sở hữu và quyền truy cập cho thư mục
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Mở cổng 80 để lắng nghe các kết nối HTTP
EXPOSE 80

# Khởi động PHP's built-in server trên cổng 80
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html"]
