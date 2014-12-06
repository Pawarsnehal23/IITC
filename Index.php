<html>
 <head>
  <style>
    
	   body
       {
          padding-left:100px;
          padding-right:100px;
          padding-top:100px;
          padding-bottom:300px;
      }
        input
       {
             margin-top:10px;
            width:500px;
       }

        label
        {
                font-weight:bold;
                color:white;
				margin:20px;
        }
		
	  .submit
	  {
		margin-left:300px;
		margin-top:30px;
		font-weight:bold;
	  }
	  
	  .inputFile
	  {
		font-weight:bold;
		margin-bottom:20px;
	  }
</style>
 </head>
 <body>
        <!-- The form-->
        <form  style="background-color:#31B0D4;margin-top:100px;border:solid;black;1px;padding:20px;" enctype="multipart/form-data" action="Result.php" method="POST">
                <!-- MAX_FILE_SIZE must precede the file input field -->
                <input type="hidden" name="MAX_FILE_SIZE" value="9000000" />
                  <br/>
                <!-- Name of input element determines name in $_FILES array -->
                <label>Send this file:</label> <input name="userFile" type="file" class="inputFile" />
				  <br/><label>Enter your Name:</label> <input name="userName" type="text"/>
                <br/><label>Enter your Phone No:</label> <input name="userPhone" type="text"/>
                <br/><label>Enter your Email Address:</label> <input name="userEmail" type="text"/>
         <br/>  <input type="submit" class="submit" style="width:100px;" value="Send File" />
        </form>
 </body>

</html>


