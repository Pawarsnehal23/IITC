<html>
<head>
  <style>
   body
    {
       margin-left:50px;
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
	  <?php
           
		   error_reporting(E_ALL);
		   ini_set('display_errors', 1);
   
              
			// Include the SDK using the Composer autoloader
			require 'vendor/autoload.php';
				 
			//Get details from post array
			$snsRegion=$_POST["snsRegion"];
			$queueRegion=$_POST["queueRegion"];
			$RDSRegion = $_POST["rdsRegion"];
			$snsTopicARN=$_POST["snsTopicARN"];
			
			
			//sames Values used while launching DB instance
			$userName = 'Administrator';
			$password = 'Administrator';
			$database= 'itmo544a20264861ImageProcessingDb';
			$tableName='ImageProcessingTb';
			$dbInstanceIdentifier='itmo544a20264861ImgaeProcessingIIF';
			$dbReadReplicaIdentifier='itmo544a20264861ImageProcessingReadIIF';
			$sqsQueueName='itmo544a20264861ImgaeProcessingQueue';
			$ReadReplicaServerName='';
			
			//Get database end point
			 $RDSClient = Aws\Rds\RdsClient::factory(array('region'  => $RDSRegion ,));
			 $rdsResult = $RDSClient->describeDBInstances(array('DBInstanceIdentifier' => $dbInstanceIdentifier,));
			
			//Find out end point of database
						foreach ($rdsResult as $key => $value) 
						{
						   echo '</br>';
						   if($key=="DBInstances")
						   { 
						     foreach ($value as $key2 => $value2) 
							   {
								  echo '</br>RDS URL is -' .$value2["Endpoint"]["Address"];
								  $servername =$value2["Endpoint"]["Address"];
							   }
						   }
						} 
			 $rdsResultForReadReplica = $RDSClient->describeDBInstances(array('DBInstanceIdentifier' => $dbReadReplicaIdentifier,));
			
			//Find out end point of database
						foreach ($rdsResultForReadReplica as $key => $value) 
						{
						   echo '</br>';
						   if($key=="DBInstances")
						   { 
						     foreach ($value as $key2 => $value2) 
							   {
								  echo '</br>RDS URL is -' .$value2["Endpoint"]["Address"];
								  $ReadReplicaServerName =$value2["Endpoint"]["Address"];
							   }
						   }
						} 
			
			//Get queue URL
			 $sqsClientFactory = Aws\Sqs\SqsClient::factory(array('region'  => $queueRegion));
			 $resultSqsQueueURL = $sqsClientFactory->getQueueUrl(array(
						// QueueName is required
						'QueueName' => $sqsQueueName, ));
			
			$sqsQueueURL = $resultSqsQueueURL["QueueUrl"];
			echo 'SQS Queue URL is -' .$resultSqsQueueURL["QueueUrl"];
			
			 //Store details to s3 as comma seperated string
	         echo '</br>Storing database details to s3';
			 $s3ClientObject = Aws\S3\S3Client::factory();
			 
			 //Store details in this bucket
			 $bucket = 'itmo544spawar1a20264861finaldbdetailsLatest';
	         $key ='MySqlDbDetails';
			 
			 if ($s3ClientObject->doesBucketExist($bucket))
			 {
			    // Delete the objects in the bucket before attempting to delete
				// the bucket
			    $s3ClientObject->clearBucket($bucket);
					
				// Delete the bucket
				$s3ClientObject->deleteBucket( array('Bucket' => $bucket));

				// Wait until the bucket is not accessible
				$s3ClientObject->waitUntil('BucketNotExists', array('Bucket' => $bucket));
			 
			 }
			 
			 //Create bucket
			 $result = $s3ClientObject->createBucket(array('Bucket' => $bucket));
			 
			  // Wait until the bucket is created
              $s3ClientObject->waitUntilBucketExists(array('Bucket' => $bucket));
			 
	         $currentDbDetails=$servername.','.$userName.','.$password.','. $database.','.$tableName.','.$sqsQueueURL.','.$snsTopicARN.','.$queueRegion.','.$snsRegion.','.$RDSRegion.','.$ReadReplicaServerName;
	         
	         $result = $s3ClientObject->putObject(array(
                        'Bucket' => $bucket,
                        'Key'    => $key,
                        'Body'   => $currentDbDetails,
						'ACL'    => 'public-read'
                ));
			
			echo '</br>Finished storing database details to s3';
			 
			// Check connection
			$WriteConnection = mysqli_connect($servername, $userName, $password);
					
			//check if error any
			if (mysqli_connect_errno())
				{
				  echo "Failed to connect to server ";
				}
				
			// Create Database with name entered by user
			$db_found = mysqli_select_db($WriteConnection,$database);
					
			//if database created
			if($db_found)
			{
					echo "</br><p style=\"color:green;\">Database {$database} on server {$servername} found </br></p>";
					
					
					//check if table already exists
					$dropStatement ="drop table {$tableName}";
					//drop table
					mysqli_query($WriteConnection,$dropStatement);
					
					 //Re-create table		
					 $createTblSql ="CREATE TABLE {$tableName}  (
							 id int auto_increment primary key,
							 S3BucketName varchar(500) NOT NULL,
							 ImageS3Key varchar(500) NOT NULL,
							 ImageThumbnailS3Key varchar(500) NOT NULL,
							 userEmail varchar(100) NOT NULL,
							 userName varchar(100) NOT NULL,
							 userPhone varchar(100) NOT NULL
					   )";
					 mysqli_query($WriteConnection,$createTblSql);  
					 echo "</br>{$tableName} table created successfully..!</br>";
			}
			else
			 {
					echo "</br><p style=\"color:red;\">Database {$database} on server {$servername} NOT found </br></p>";
					
			}
?>
