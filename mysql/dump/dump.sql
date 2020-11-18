ALTER USER root IDENTIFIED WITH mysql_native_password BY 'root';
CREATE DATABASE urls;
CREATE TABLE urls.d_urls (tiny_url VARCHAR(64), full_url VARCHAR(1024), UNIQUE KEY tiny_url (tiny_url));
