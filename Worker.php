#!/bin/bash
  
   #Install Zip
   sudo apt-get install zip
   sudo apt-get install unzip
   
   sudo apt-get -y update 
   sudo apt-get -y install --force-yes apache2 wget php5 php5-curl curl git php5-mysql 
   
   #Install GD 
   sudo apt-get install php5-gd
   
   #Install font required for creating thumbnail
   sudo apt-get install msttcorefonts
   
   #Enable Mysqli
   sudo sed  -i  '/;mysqli.allow_local_infile = On/c\mysqli.allow_local_infile = On' /etc/php5/apache2/php.ini
   sudo /etc/init.d/apache2 restart
   
   #chnage permissions
   sudo chmod -R 777 /var/www/html 
  
   # Get files from web server to application server
   wget http://ec2-54-148-142-162.us-west-2.compute.amazonaws.com/composer.json
   
   # Get composer
   curl -sS https://getcomposer.org/installer | sudo php
   sudo php composer.phar install
   
   wget http://ec2-54-148-142-162.us-west-2.compute.amazonaws.com/WorkerPhp.zip

   unzip WorkerPhp.zip 
     
   # Move file to www 
   mv /vendor /var/www/html
   mv /Worker.php /var/www/html
   
   #Launch worker
   php /var/www/html/Worker.php &
   exit 0

