# A Brief to Pertemuan 14 - PWEB B - Multi-user Level Login

# How to run the app (using PHP, MySQL) or you can use XAMPP just to run the MySQL

#### Prerequisite

1. PHP / XAMPP / MAMP / LAMP / Laragon
2. Apache/HTTPd
3. MySQL DBMS server (Linux Distro) or just install XAMPP (Windows) or MAMP (MacOS) or Laragon
4. phpMyAdmin - [phpMyAdmin](https://www.phpmyadmin.net/) -> documentation

#### Setup

1. Make sure you have already installed the MySQL server using your distro package manager or on Windows just use XAMPP and start the MySQL service from there
2. Download the phpMyAdmin and copy the folder containing the phpMyAdmin codes (post-extraction) to the root of the APACHE/NGINX/HTTPd&nbsp

   2.a. If you are using HTTPD, it is on /var/www/html (if you are advanced user, you can create another VirtualHost block just for this app, or you can setup a Dockerfile to run this app from docker)

   2.b. If you are using Apache/XAMPP, find the where the configuration path are and find a folder named `htdocs`

   2.c. Paste the folder on them and try to access the phpMyAdmin on the browser (localhost/phpMyAdmin)&nbsp

3. Log in to the phpMyAdmin using your own credential (user and password)
4. Copy the code `ddl.sql` to the phpMyAdmin on the SQL tab (Run SQL Query) and run. It should create a database named `user_level` with `user` table inside.

#### Run

1. Clone this repo first, then copy the entire content of this directory (pertemuan9) to the root of your HTTPd/Apache/XAMPP
   1.a. Make sure to replace the credential needed on the file `koneksi.php`. (User, Password).
   1.a. or you can just start a local server (just PHP) using `php -S localhost:8181` (make sure the port is not conflicting or not used by other process or program)
   1.b. If you place it on the root of you Apache/HTTPd/XAMPP just refresh the page on localhost and direct the url to the /pertemuan11/ or if you placed the index.php right on the htdocs, just refresh the page

2. The page should load by the time you access the app main entry file `index.php`, and that should be it. You can try to do the Login action using the populated entry of the user table.
