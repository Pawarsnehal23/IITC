#!/bin/bash
    
   #Install Zip
   sudo apt-get install zip
   sudo apt-get install unzip
   
   sudo apt-get -y update 
   sudo apt-get -y install --force-yes apache2 wget php5 php5-curl curl git php5-mysql  wget
    
   #Install Git	
   sudo apt-get install git
   
   #enable mysqli
   sudo sed  -i  '/;mysqli.allow_local_infile = On/c\mysqli.allow_local_infile = On' /etc/php5/apache2/php.ini
   #restart apache
   sudo /etc/init.d/apache2 restart
   
   #chnage permissions
   sudo chmod -R 777 /var/www/html 
  
   #Get required files
   git clone https://github.com/Pawarsnehal23/IITC.git
   
   mv /IITC/composer.json /composer.json
   
   # Get composer
   curl -sS https://getcomposer.org/installer | sudo php
   sudo php composer.phar install
   
     
   # Move file to www 
   mv  /vendor /var/www/html
   mv /IITC/ConfigureDetails.php /var/www/html
   mv /IITC/ConfigureITM0544ImageProcessingSystem.php /var/www/html
   mv /IITC/HomePage.php /var/www/html
   mv /IITC/SubscribeToSNS.php /var/www/html
   mv /IITC/Gallary.php /var/www/html
