<html>
<head>
  <style>
   body
    {
       margin:50px;
    }
     label
     {
             margin:10px;
			 padding:10px;
			 color:black;
     }
	 input
	 {
	     margin:10px;
		 width:600px;
	 }
    
	</style>
  </head>
  <body>
        <i style="color:green;font-size:1.2em;"> Tip - Launch your RDS via - LaunchRDSInstance.php (if not already done) .
		    Once instance becomes ready , find the below details from AWS management console and enter here. </br>
			For creating queue and topic use CreateQueue.php and CreateTopic.php respectively.
		   </br> </br>This form will take all the provided information and store it to S3 for all further usage .There is no need of manually updating 
		   any of the code file
		</i>
			
        <!-- The form-->
        <form  style="background-color:#31B0D4;margin-top:20px;border:solid;black;1px;padding:20px;" 
		      action="ConfigureDetails.php" method="POST">
		   <br/><label>RDS Database Identifier</label> <input name="dbName" type="text" readonly="readonly" value="itmo544a20264861ImgaeProcessingIIF"/>  
		   <br/><label>RDS Database Name</label> <input name="dbName" type="text" readonly="readonly" value="itmo544a20264861ImageProcessingDb"/>
		   <br/><label>RDS Read Replica DB Identifier</label> <input name="dbName" type="text" readonly="readonly" value="itmo544a20264861ImageProcessingReadIIF"/>
           <br/><label>RDS Master User Name</label> <input name="userName" type="text" readonly="readonly" value="Administrator" />
           <br/><label>RDS Master Password</label> <input name="password" type="text" readonly="readonly" value="Administrator"/>
		   <br/><label>Provide Table Name(this table will get created now)</label>
		   <input name="tableName" type="text" readonly="readonly" value="ImageProcessingTb"/>
		   <br/><label>Your Region Of SNS Topic</label> <input name="snsRegion" type="text"/>
		   <br/><label>Your Region Of Queue</label> <input name="queueRegion" type="text"/>
		   <br/><label>RDS region</label> <input name="rdsRegion" type="text"/>
		   <br/><label>SNS Topic ARN</label> <input name="snsTopicARN" type="text"/>
		   <br/><input type="submit" class="submit" style="width:100px;" value="Submit" />
        </form>
   
   </body>
 </html>
