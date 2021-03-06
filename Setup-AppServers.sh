#!/bin/bash
  
   # To mount device
   mkfs -t ext4 /dev/sdh
   mkdir /mnt/disk1
   mount /dev/sdh /mnt/disk1
   echo "/dev/sdh /mnt/disk1 ext4 defaults,nofail 0 0" >> /etc/fstab
   
   #Install Zip
   sudo apt-get install zip
   sudo apt-get install unzip
   
   sudo apt-get -y update 
   sudo apt-get -y install --force-yes apache2 wget php5 php5-curl curl git php5-mysql  wget

    #Install Git	
   sudo apt-get install git
   
   sudo sed  -i     '/;mysqli.allow_local_infile = On/c\mysqli.allow_local_infile = On' /etc/php5/apache2/php.ini
   sudo /etc/init.d/apache2 restart
   
   #chnage permissions
   sudo chmod -R 777 /var/www/html 
  
   #Get Code files from Github account
   git clone https://github.com/Pawarsnehal23/IITC.git
   
   mv /IITC/composer.json /composer.json
   
   # Get composer
   curl -sS https://getcomposer.org/installer | sudo php
   sudo php composer.phar install
   
   #Make directory to store uploaded files
   mkdir /var/www/uploads
   sudo chmod -R 777 /var/www/uploads
  
   # Move file to www 
   mv  /IITC/Index.php /var/www/html
   mv  /IITC/Result.php /var/www/html
   mv  /IITC/HomePageAppServer.php /var/www/html
   mv /IITC/Gallary.php /var/www/html
   mv  /vendor /var/www/html
