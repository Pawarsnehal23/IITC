<html>
<head>
  <style>
   body
    {
       margin:50px;
    }
     label
     {
             margin:30px;
     }
    label #message
      {
                 margin:10px;
        }
	</style>
  </head>
  <body>
        <i style="color:green;font-size:1.2em;">
            </br>1.Launch WebServer instance , that script will also launch RDS instance , queue and topic . 
			</br>2.Then launch Application server instance and worker instance.
			</br>3.Once application server and DB instances become ready , find the required details from
			AWS management console OR shell script output which are required for "Configure all system details page" which will take all the provided information and store it to S3 for all further usage .There is no need of manually updating any of the code file.
			</br>4.Now, users can subscribe themselves.
			</br>5.After subscribing users can try to upload pictures and then come here again to check their finished job.
		</i>
		
	   <h3>Admin Section</h3> 
	   <div style="border:1px solid black;width:600px;margin:30px;padding:30px;">
	      <!--</br><a href="LaunchRDSInstance.php">Launch RDS instance</a>
	      </br><a href="CreateQueue.php">Create Queue </a>
		  </br><a href="CreateTopic.php">Create topic</a>-->
		  </br><a href="ConfigureITM0544ImageProcessingSystem.php">Feed RDS , queue and topic information to system</a>
		  </br><a href="SubscribeToSNS.php">Subscribe Users To notification</a> 
	  </div>
	   
  </body>

</html>  
