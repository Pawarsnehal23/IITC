#!/bin/bash
  
   #Install Zip
   sudo apt-get install zip
   sudo apt-get install unzip
   
   sudo apt-get -y update 
   sudo apt-get -y install --force-yes apache2 wget php5 php5-curl curl git php5-mysql 
   
   #Install GD 
   sudo apt-get install php5-gd && sudo service apache2 restart
   
   #Install font required for creating thumbnail
   sudo apt-get install msttcorefonts
   
   #Enable Mysqli
   sudo sed  -i  '/;mysqli.allow_local_infile = On/c\mysqli.allow_local_infile = On' /etc/php5/apache2/php.ini
   sudo sed  -i  '/;extension=php_gd2.dll/c\extension=php_gd2.dll' /etc/php5/apache2/php.ini
   
   sudo /etc/init.d/apache2 restart
   
   #chnage permissions
   sudo chmod -R 777 /var/www/html 
   
   #chnage permissions
   sudo chmod -R 777 /var/www/html ;
   
   #Get Code files from Github account
   git clone https://github.com/Pawarsnehal23/IITC.git;
   
   mv /IITC/composer.json /composer.json;
  
   # Get composer
   curl -sS https://getcomposer.org/installer | sudo php
   sudo php composer.phar install
   
        
   # Move file to www 
   mv /vendor /var/www/html
   mv /IITC/Worker.php /var/www/html
   
   #Launch worker
   php /var/www/html/Worker.php &
   exit 0

