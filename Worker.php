<?php
   // Include the SDK using the Composer autoloader
	require 'vendor/autoload.php';
	
	while(true)
	{
	            error_reporting(E_ALL);
                ini_set('display_errors', 1);
	 
	           //Get DB and queue connection details from s3
			     echo '</br>Getting database details from s3';
			    $s3ClientObject = Aws\S3\S3Client::factory();
			 
			 	 $databaseBucketName='itmo544spawar1a20264861finaldbdetailsLatest';
				 $databaseKeyName='MySqlDbDetails';
				 $resultDataBaseDetails = $s3ClientObject->getObject
				 (  array(
					'Bucket' => $databaseBucketName,
					'Key'    => $databaseKeyName
				 ));
				 
				 echo $resultDataBaseDetails['Body'];
				 $myDbDetailsArray = explode(',', $resultDataBaseDetails['Body']);
				 $servernameForWrite=$myDbDetailsArray[0];
				 $username = $myDbDetailsArray[1];
				 $password = $myDbDetailsArray[2];
				 $database= $myDbDetailsArray[3];
				 $tableName=$myDbDetailsArray[4];
				 $sqsQueueURL=$myDbDetailsArray[5];
			     $snsTopicARN=$myDbDetailsArray[6];
				 $queueRegion=$myDbDetailsArray[7];
				 $servernameReadReplica=$myDbDetailsArray[10];
	
	        // Check connection to read replica
			$connectionReadReplica = mysqli_connect($servernameReadReplica, $username, $password);
			 $connectionForWrite=null;
			 
			//check if error any
			if (mysqli_connect_errno())
				{
				  echo "Failed to connect to server ";
				}
				
			$db_found = mysqli_select_db($connectionReadReplica,$database);
			
	        //Add delay of sometime
			sleep(15);
			
			//SQS operations start
			$sqsClientFactory = Aws\Sqs\SqsClient::factory(array('region'  => $queueRegion));
			
			$sqsResult = $sqsClientFactory->receiveMessage(array(
				  // QueueUrl is required
					'QueueUrl' => $sqsQueueURL,
					'MaxNumberOfMessages' => 1,
					'VisibilityTimeout' => 30,
			));
			
			$messageBody = "";
			$receiptHandle = "";
			
			//consume message
			if( $sqsResult !=null && $sqsResult->getPath('Messages/*/Body') !=null)
			{
			    $currentJobId="";
				$bucket="";
				$key="";
  			  
			    
			   foreach ($sqsResult->getPath('Messages/*/Body') as $messageBody) 
			    {
					// Do something with the message
					echo "<br/>".$messageBody ."<br/>";
					$currentJobId=$messageBody;
				}
				
				//Process current Job
				//find out bucketname and objectkey from database for this id
				echo " <br/>Data in table {$tableName} is  <br/>";
				$selectSql = "SELECT S3BucketName, ImageS3Key FROM {$tableName} where id={$currentJobId}";
				$mySqlResult = mysqli_query($connectionReadReplica,$selectSql);

					if ($mySqlResult->num_rows > 0)
					{
						// output data of each row
						while($row = $mySqlResult->fetch_assoc()) 
						{
							    $bucket=$row["S3BucketName"];
								$key=$row["ImageS3Key"];
						}
					}
					else 
					{
						echo "0 results";
					}
				
				//If there was any valid job to process			
				if($currentJobId !="")
				{
						//Create thumbnail for this bucket and this key
						$thumbnailKey=createThumbnail($bucket,$key);
						
						 // Check connection to read replica
						 $connectionForWrite = mysqli_connect($servernameForWrite, $username, $password);
									
						 //check if error any
						 if (mysqli_connect_errno())
							{
							  echo "Failed to connect to server ";
							}
							
						 $db_found = mysqli_select_db($connectionForWrite,$database);
						
						 //Add delay of sometime
						 sleep(15);
						
						//Update thumbnail details back to database
						$updateSql = "Update {$tableName} set ImageThumbnailS3Key=\"{$thumbnailKey}\" where id = {$currentJobId};";
						$mysqlResult = mysqli_query($connectionForWrite,$updateSql);
						
						echo $updateSql;
						echo "Thumbnail details updated correctly to database..";
						
						//Send messages to all subscribers
						
						// Include the SDK using the Composer autoloader
						require 'vendor/autoload.php';
						
						$snsClientFactory = Aws\Sns\SnsClient::factory(array('region'  => $queueRegion));

						//SNS operation - publish message
						$message='job with id -' .$currentJobId .'completed successfully';
						echo "<br/>Publish new message<br/>";
							$snsResult = $snsClientFactory->
								publish(array(
								'TopicArn' => $snsTopicARN,
								// Message is required
								'Message' => $message,
								'Subject' => 'ITMO544A20264861-ImageProcessingSystem',
								'MessageStructure' => 'string'		    			
							));

						echo "<br/>topic published successfully..! <br/>";	
						
						
						//Get recipt handle ( Required to delete this message)
						foreach ($sqsResult->getPath('Messages/*/ReceiptHandle') as $receiptHandle) {
							// Do something with the message
						
						}
					 
						 //Delete consumed message
						 if ($receiptHandle !=null)
						{
						   $sqsDeleteResult = $sqsClientFactory->deleteMessage(array(
								// QueueUrl is required
								'QueueUrl' => $sqsQueueURL,
								// ReceiptHandle is required
								'ReceiptHandle' => $receiptHandle,
							));
					
							echo "<br/>Consumed Message has been deleted!<br/>";
					   }
				}//end of if validation
			}
			
			
		  //Drop MySQL all sql connections
		  mysqli_close($connectionReadReplica);
		  mysqli_close($connectionForWrite);
		}
		
	     //Function to create thumbnail
	     function createThumbnail($bucket,$key) 
	     {
			    // Include the SDK using the Composer autoloader
				require 'vendor/autoload.php';
				$AwsS3Client = Aws\S3\S3Client::factory();
				$UrlToImage =  $AwsS3Client->getObjectUrl($bucket, $key);
				 
				 #Secify image URL
				$remoteImage=$UrlToImage;
				
				
				$imginfo = getimagesize($remoteImage);
				//Get extension and mime type
				$mimeType = image_type_to_mime_type($imginfo[2]);
				$extension = image_type_to_extension($imginfo[2]);
				
				//Thumbnail image's size
				$final_width_of_image=50;
				
				//Call appropriate GD function based on extension
				if ($extension ==".png")
					$im = imagecreatefrompng($remoteImage);
				else if ($extension ==".jpeg") 
					$im = imagecreatefromjpeg($remoteImage);
				else if ($extension==".gif")
					$im = imagecreatefromgif($remoteImage);
					
				$ox = imagesx($im);
				$oy = imagesy($im);
				 
				$nx = $final_width_of_image;
				$ny = floor($oy * ($final_width_of_image / $ox));
				 
				$nm = imagecreatetruecolor($nx, $ny);
				 
				imagecopyresized($nm, $im, 0,0,0,0,$nx,$ny,$ox,$oy);
				
				$path_to_thumbs_directory='Thumbnails/';
				
				if(!file_exists($path_to_thumbs_directory)) 
				{
				    if(!mkdir($path_to_thumbs_directory)) 
					{
					   die("There was a problem. Please try again!");
				     } 
				}
				   
				$black = imagecolorallocate($nm, 255, 255, 255);
				// Path to our ttf font file
				$font_file = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
				//Write  out logo to image
				imagefttext($nm, 7, 0, 0, 30, $black,$font_file,  'ITMO544');
			   
			    //Call appropriate function based on extension
				if ($extension ==".png")
					{
					  $filename="ImageThumbnail.png";
					  imagepng($nm, $path_to_thumbs_directory . $filename);
					}
				else if($extension ==".jpeg")	
				   {
					  $filename="ImageThumbnail.jpg";
					  imagejpeg($nm, $path_to_thumbs_directory . $filename);
				   }	
				 else if ($extension ==".gif")
				   {
					  $filename="ImageThumbnail.gif";
					  imagegif($nm, $path_to_thumbs_directory . $filename);
				   }
				//Put thumbnail back to S3
				$newKey= 'Thumbnail'.$key;
				
				$result = $AwsS3Client->putObject(array(
									'Bucket' => $bucket,
									'Key'    => $newKey,
									'SourceFile'  => $path_to_thumbs_directory . $filename,
									'ACL'        => 'public-read'
							));
				
				$tn = '<img src="' . $path_to_thumbs_directory . $filename . '" alt="image" />';
				$tn .= '<br />Congratulations. Your file has been successfully uploaded, and a   thumbnail has been created.';
				echo $tn;
				
				return $newKey;
			}//end of function
			
			
   	
?>
