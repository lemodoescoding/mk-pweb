# A Brief to Pertemuan 9 - PWEB B

# How to run the app (using PHP, MySQL) or you can use XAMPP just to run the MySQL

#### Prerequisite

1. Install PHP on your local system (or alternatively install XAMPP / HTTPd server)
2. Install phpMyAdmin to the server [phpMyAdmin](https://www.phpmyadmin.net/) -> documentation

#### Setup

1. Make sure you have already installed the MySQL server using your distro package manager or on Windows just use XAMPP and start the MySQL service from there
2. Download the phpMyAdmin and copy the folder containing the phpMyAdmin codes (post-extraction) to the root of the APACHE/NGINX/HTTPd
   2.a. If you are using HTTPD, it is on /var/www/html
   2.b. If you are using Apache/XAMPP find the where the configuration path are and find a folder named `htdocs`
   2.c. Paste the folder on them and try to access the phpMyAdmin on the browser (localhost/phpMyAdmin)

3. Log in to the phpMyAdmin using your own credential (user and password)
4. Follow this tutorial just for the setup of the Database and the table [Original Tutorial](https://www.petanikode.com/tutorial-php-mysql/)

#### Run

1. Clone this repo first, then copy the entire content of this directory (pertemuan9) to the root of your HTTPd/Apache/XAMPP
   1.a. or you can just start a local server (just PHP) using `php -S localhost:8181` (make sure the port is not conflicting or not used by other process or program)
   1.b. If you place it on the root of you Apache/HTTPd/XAMPP just refresh the page on localhost and direct the url to the /pertemuan9/ or if you placed the index.php right on the htdocs, just refresh the page

2. The page should load by the time you access the index.php, and that's it
