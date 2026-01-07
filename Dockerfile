FROM php:8.4-cli

RUN apt-get update \
  && apt-get install -y --no-install-recommends git unzip zip \
  && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --prefer-dist

COPY . .

EXPOSE 8088

CMD ["php", "-S", "0.0.0.0:8088", "-t", "/app"]
