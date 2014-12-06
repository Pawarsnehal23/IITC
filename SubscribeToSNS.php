<html>
	<head>
	  <style>
			body
			{
				margin:50px;
				padding:30px;
			}
			label,input
			{
				 margin:30px;
			     color:white;
			}

			#message
		  {
					 margin:10px;
			}
		</style>
	</head>
  <body>
		<?php
		  
		    error_reporting(E_ALL);
		    ini_set('display_errors', 1);
		   
		   if($_SERVER['REQUEST_METHOD'] == 'POST' AND count($_POST) >0 )
		   {
		        $emailIDToBeSubscribed=$_POST['userEmail'];
				
				  
				// Include the SDK using the Composer autoloader
				 require 'vendor/autoload.php';
				 
				echo '</br>Getting database details from s3';
			    $s3ClientObject = Aws\S3\S3Client::factory();
			 
				//Get details from s3
				 $databaseBucketName='itmo544spawar1a20264861finaldbdetailsLatest';
				 $databaseKeyName='MySqlDbDetails';
				 $resultDataBaseDetails = $s3ClientObject->getObject
				 (  array(
					'Bucket' => $databaseBucketName,
					'Key'    => $databaseKeyName
				 ));
				 
				 
				 $myDbDetailsArray = explode(',', $resultDataBaseDetails['Body']);
				 if (count($myDbDetailsArray) >6 )
					{
					    $sqsTopicARN=$myDbDetailsArray[6];
						$queueRegion=$myDbDetailsArray[7];
						 
						// Include the SDK using the Composer autoloader
						require 'vendor/autoload.php';
						
						$snsClientFactory = Aws\Sns\SnsClient::factory(array('region'  => $queueRegion,));

						//SNS operation - Add subscriber
						echo "<br/>subscribe to AWS notification<br/>";

						$result = $snsClientFactory->subscribe(array(
							// TopicArn is required
							'TopicArn' => $sqsTopicARN,
							// Protocol is required
							'Protocol' => 'Email',
							'Endpoint' => $emailIDToBeSubscribed,
						));
							
						echo "<br/>User subscribed to topic successfully<br/>";	
					}
				else	
					{
					   echo "<br/>Topic ARN not found..!<br/>";	
					}
			}
			 else
		    {
		       echo ' <form  style="background-color:#31B0D4;margin-top:100px;border:solid;black;1px;padding:20px;" enctype="multipart/form-data" action="SubscribeToSNS.php" method="POST">
			             <label>Enter Your email Address<label> 
			             <input style="color:black;width:600px;" type="text" name="userEmail" style="width:400px;"></text>

   			             </br><input style="color:black;"  type="submit"></input>
			    </form>';
		  
		   }
		 ?>
  </body>
</html>
                                                                         
																		 
