FROM composer:latest
RUN mkdir /data
COPY . /app
WORKDIR /app
RUN composer install

