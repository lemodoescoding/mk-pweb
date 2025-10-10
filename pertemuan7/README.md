# A Brief to Pertemuan 7 - PWEB B
# How to run the app (using PHP and composer)

#### Prerequisite
1. Install PHP on your local system (or alternatively install XAMPP / HTTPd server)
2. Install Composer, here is a link to the documentation [composer](https://getcomposer.org/doc/00-intro.md)

#### Setup
1. Setup your google account for using the PHPMailer (you need to login to your google account), add an application and copy the password (no spaces), here is a link [add application on your gmail](https://myaccount.google.com/apppasswords)
2. Copy and paste your email and the password for your application onto the `.env` on `pertemuan7/mailer/.env` (make a duplicate from `.env.example`, and make sure the name is `.env`)
3. Fill the `SMTP_HOST` and `SMTP_PORT` smtp.gmail.com and 587 respectively
4. Paste or fill on the `.env` file only inside the double-quotes, except for the `SMTP_PORT`.

#### Run
1. Go to the pertemuan7/mailer
2. Run this command, ```composer install```
3. To start the microservice, run ```php -S 127.0.0.1:PORT```, specify your PORT, use larger than 3000
4. Modify the AJAX request on the index.html on `pertemuan7/index.html` on the AJAX script portion, update the port like `http://127.0.0.1:PORT/app/sendmail.php`
5. And it's done, to stop the service just Ctrl+C the process sendmail.php
