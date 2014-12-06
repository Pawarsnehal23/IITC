#!/bin/bash
  
   sudo apt-get -y update ;
   sudo apt-get -y install --force-yes apache2 wget php5 php5-curl curl git php5-mysql  wget;
   
   #Install Git	
   sudo apt-get -y install --force-yes git ;
   
   #Install GD 
   sudo apt-get -y install --force-yes php5-gd ;
   
   sleep 10;
   
   #Enable Mysqli
   sudo sed  -i  '/;mysqli.allow_local_infile = On/c\mysqli.allow_local_infile = On' /etc/php5/apache2/php.ini;
   
   sudo /etc/init.d/apache2 restart;
   
   sleep 10;
   
   #Install font required for creating thumbnail
   sudo apt-get -y install --force-yes msttcorefonts;
   
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

