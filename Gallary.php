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
			
			// Include the SDK using the Composer autoloader
		     require 'vendor/autoload.php';
				 
		   if($_SERVER['REQUEST_METHOD'] == 'POST' AND count($_POST) >0 )
		   {
		      $userEmailID= $_POST['userEmail'];
			  
			  echo '</br>Getting database details from s3';
			    $s3ClientObject = Aws\S3\S3Client::factory();
				
			  //write code to retrieve all rows from database where email id matches and get details from s3 display them on this page
			     $databaseBucketName='itmo544spawar1a20264861finaldbdetailsLatest';
				 $databaseKeyName='MySqlDbDetails';
				 $resultDataBaseDetails = $s3ClientObject->getObject
				 (  array(
					'Bucket' => $databaseBucketName,
					'Key'    => $databaseKeyName
				 ));
				 
				
				 $myDbDetailsArray = explode(',', $resultDataBaseDetails['Body']);
				 $servernameOfReadReplica=$myDbDetailsArray[10];
				 $username = $myDbDetailsArray[1];
				 $password = $myDbDetailsArray[2];
				 $database= $myDbDetailsArray[3];
				 $tableName=$myDbDetailsArray[4];
				 
				 // Check connection
				$connectionOfReadReplica = mysqli_connect($servernameOfReadReplica, $username, $password);
						
				//check if error any
				if (mysqli_connect_errno())
					{
					  echo "Failed to connect to server ";
					}
					
				$db_found = mysqli_select_db($connectionOfReadReplica,$database);
				echo "</br>DB found".$db_found ;
				
				$selectTblSql ="select S3BucketName,ImageS3Key,ImageThumbnailS3Key,userEmail,userName,userPhone from {$tableName}
				 where userEmail=\"{$userEmailID}\"";
				 
				$mysqlSelectResult = mysqli_query($connectionOfReadReplica,$selectTblSql);
				
				 if ($mysqlSelectResult) 
				 {
				      $row = $mysqlSelectResult->fetch_assoc();
					  if($row !=null)
					 {
					    echo '</br><b>User Email:</b>'.$row["userEmail"];
					    echo '</br><b>User Name:</b>'.$row["userName"];
					    echo '</br><b>User Phone:</b>'.$row["userPhone"].'</br></br>';
					 }  
					 $UrlToImage="";
					 $UrlToImageThumbnail="";
					 while($row!=null)
					  {
					     //Get object URL 
						 $bucketAddress=$row["S3BucketName"];
						 if ($s3ClientObject->doesBucketExist($bucketAddress))
			              {
						     $key=$row["ImageS3Key"];
						     if( $s3ClientObject->doesObjectExist($bucketAddress,$key))
				               {
							     $UrlToImage=$s3ClientObject->getObjectUrl($bucketAddress,$key );
							    }
							$key=$row["ImageThumbnailS3Key"]; 
							if( $s3ClientObject->doesObjectExist($bucketAddress,$key))
						      {
							     $UrlToImageThumbnail=$s3ClientObject->getObjectUrl($bucketAddress,$key );
							  }	 
						  }	
						  
						 echo '<table>'; 
						 echo '<tr>';
						 echo '<td><b>Image</b>';
						 if( $UrlToImage !=null) 
						  {
						    echo "<br/><img style=\"border:1px solid black;margin:20px;\" width=300px height=200px; src=\"{$UrlToImage}\"/>";
						    
						  }
						  else
						  {
						     echo "Image Not available..!";
						  }
						  echo '</td>';
						 echo '<td><b>Image Thumbnail</b>';
						 if( $UrlToImageThumbnail !=null) 
						  {
						    echo "<br/><img style=\"border:1px solid black;margin:20px;\" width=50px height=50px; src=\"{$UrlToImageThumbnail}\"/>";
						  }
						  else
						  {
						     echo "<br/>Image thumbail Not available..!";
						  }
						   echo '</td>';
						  echo '</tr>'; 
						echo '<table>'; 
					    $row = $mysqlSelectResult->fetch_assoc();
					  }
					 
				 }
				else 
				{
				  echo mysql_error();
				}

		   }
		  else
		  {
		    echo ' <form  style="background-color:#31B0D4;margin-top:100px;border:solid;black;1px;padding:20px;" enctype="multipart/form-data" action="Gallary.php" method="POST">
			             <label>Enter Your email Address<label> 
			             <input style="color:black;" type="text" name="userEmail" style="width:400px;"></text>

   			             </br><input style="color:black;"  type="submit"></input>
			    </form>';
		  
		  }
		?> 
	</body>
</html> 
  
