<html>
<head>
 
</head>
<body>
<?php
  
  if($_SERVER['REQUEST_METHOD'] == 'POST' AND count ($_POST) >1 )
  {
            error_reporting(E_ALL);
		    ini_set('display_errors', 1);
		   
			$uploaddir = '/var/www/uploads/';
			$fileName= basename($_FILES['userFile']['name']);
			
			$uploadfile = $uploaddir . $fileName;
			
			echo '<br/>';

			echo  '<label id="message">File data present</label>';

			echo '<br/>';
         

           if (move_uploaded_file($_FILES['userFile']['tmp_name'], $uploadfile))
                {
                     echo '<label id="message" style="color:green;font-size:1em;">File is valid, and was successfully uploaded. </label><br/>';
                }
                else
                {
                       echo '<label id="message"  style="color:red;font-size:1em;">Possible file upload attack!\n </label><br/>';
                }

				// Include the SDK using the Composer autoloader
				require 'vendor/autoload.php';

				//create s3 client object
                $s3ClientObject = Aws\S3\S3Client::factory();
		   
		        //Give some unique bucket name
				$bucket = "snehalitmo544".time();
                
                echo '<br/><label id="message">Creating bucket named '.$bucket.'</label>';
                $result = $s3ClientObject->createBucket(array(
                              'Bucket' => $bucket
                ));

                // Wait until the bucket is created
                $s3ClientObject->waitUntilBucketExists(array('Bucket' => $bucket));

                echo '<br/><label id="message">Bucket created..!</label>';

                echo '<br/><label id="message">Setting ACL to bucket</label>';

                $result = $s3ClientObject->putBucketAcl(array(
                    'ACL' => 'public-read','Bucket' => $bucket));

				//Keep keyname same as file name	
                $key = $fileName;
                echo '<br/><label id="message">Creating a new object with key-'.$key.'</label>';
                $result = $s3ClientObject->putObject(array(
                        'Bucket' => $bucket,
                        'Key'    => $key,
                        'SourceFile'  => $uploadfile,
						'ACL'        => 'public-read'
                ));

                echo '<br/><label id="message">Done with object creation.</label>';
               
				//Get object URL 
				$UrlToImage=$s3ClientObject->getObjectUrl($bucket, $key);
				
                session_start();
                //echo $_POST["userEmail"];
                $_SESSSION['userName']=$_POST["userName"];
                $_SESSSION['userPhone']=$_POST["userPhone"];
                $_SESSSION['userEmail']=$_POST["userEmail"];

                echo "<br/><br/><label>Your Image (pulled from AWS S3 - {$UrlToImage} )</label>";
                echo "<br/><img style=\"border:1px solid black;margin:20px;\" width=400px height=300px; src=\"{$UrlToImage}\"/>" ;
                echo '<br/><label><b> User Name :</b></label>'.$_SESSSION['userName'] ;
                echo '<br/><label><b> User Phone :</b></label>'.$_SESSSION['userPhone'] ;
                echo '<br/><label><b> User Email :</b></label>'.$_SESSSION['userEmail'] ;

             	//Insert Data into database
				//Get DB connection details from s3
				 $databaseBucketName='itmo544spawar1a20264861finaldbdetailsLatest';
				 $databaseKeyName='MySqlDbDetails';
				 $resultDataBaseDetails = $s3ClientObject->getObject
				 (  array(
					'Bucket' => $databaseBucketName,
					'Key'    => $databaseKeyName
				 ));
				 
				 $myDbDetailsArray = explode(',', $resultDataBaseDetails['Body']);
				 $servernameOfWriteInstance=$myDbDetailsArray[0];
				 $username = $myDbDetailsArray[1];
				 $password = $myDbDetailsArray[2];
				 $database= $myDbDetailsArray[3];
				 $tableName=$myDbDetailsArray[4];
				 $sqsQueueURL=$myDbDetailsArray[5];
			     $snsTopicARN=$myDbDetailsArray[6];
				 $queueRegion=$myDbDetailsArray[7];
				 					 
				// Check connection
				$connection = mysqli_connect($servernameOfWriteInstance, $username, $password);
						
				//check if error any
				if (mysqli_connect_errno())
					{
					  echo "Failed to connect to server ";
					}
					
				$db_found = mysqli_select_db($connection,$database);
				echo "</br>DB found - ".$db_found ;
				
				$insertTblSql ="insert into {$tableName} (S3BucketName,ImageS3Key,ImageThumbnailS3Key,userEmail,userName,userPhone) values (\"{$bucket}\",\"{$key}\",\"NotAvailable\",\"{$_SESSSION['userEmail']}\",\"{$_SESSSION['userName']}\",\"{$_SESSSION['userPhone']}\");";
				
				//echo $insertTblSql;	
				
				$mysqliObject = new mysqli($servernameOfWriteInstance, $username, $password, $database);
				$mysqliObject->query($insertTblSql);

				echo " </br> Data inserted into table {$tableName} </br>";
				
				$lastInserID = $mysqliObject->insert_id;
				echo '</br> last database insert ID is -'.$lastInserID;
								
				//SQS operations start
			    $sqsClientFactory = Aws\Sqs\SqsClient::factory(array('region'  => $queueRegion));
				
				//Push this instance to queue				
				$sqsSendMsgResult = $sqsClientFactory->sendMessage(array(
					// QueueUrl is required
					'QueueUrl' => $sqsQueueURL,
					// MessageBody is required
					'MessageBody' => $lastInserID,
			    ));

                echo '</br>Data uploaded to AWS queue.';				
			    //Drop MySQL connection
			    mysqli_close($connection);
		}
	
 ?>
</body>

</html>
                                                                         
																		 
