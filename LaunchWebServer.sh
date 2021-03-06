 #! /bin/sh
 clear

 if [ $# != 7 ]
    then 
    echo "This script needs 7 arguments/variables to run;  client-token , image-id , keypair ,security group , set up file path ,number of instances , IAM role "
 else
 
    #launch asked database
    echo -e '\nCreating Database..'	
	CREATE_DB_INSTANCE=$(aws rds create-db-instance  --db-name itmo544a20264861ImageProcessingDb --db-instance-identifier itmo544a20264861ImgaeProcessingIIF --allocated-storage 5 --db-instance-class db.t2.micro --engine MySQL --master-username Administrator --master-user-password Administrator);
   
    echo -e "\nFinished creating Db instance.Sleeping 1000 seconds.."
	for i in {0..1000}; do echo -ne '.'; sleep 1;done
	
	#Create Read Replica
	echo -e '\nCreating Read replica..'	
	READ_REPLICA=$(aws rds create-db-instance-read-replica  --db-instance-identifier itmo544a20264861ImageProcessingReadIIF --source-db-instance-identifier itmo544a20264861ImgaeProcessingIIF);
	
	echo -e "\nFinished creating Read replica for Db instance.Sleeping 60 seconds.."
	for i in {0..60}; do echo -ne '.'; sleep 1;done
	
	#Create Queue 
	QUEUE_URL=$(aws sqs create-queue --queue-name itmo544a20264861ImgaeProcessingQueue --output=text);
	echo -e '\nFinished creating Queue.Sleeping 60 seconds..Queue Name is -' $QUEUE_URL;
		
	#Create Topic
	TOPIC_ARN=$(aws sns create-topic --name itmo544a20264861ImgaeProcessingTopic --output=text);
	echo -e '\nFinished creating topic.Topic ARN is -' $TOPIC_ARN;
	
		
	#Step 1: Create a VPC with a /28 cidr block (see the aws example) - assign the vpc-id to a variable  you can awk column $6 on the --output=text to get the value
	echo -e 'Creating VPC..'
	VPCID=(`aws ec2 create-vpc  --cidr-block 10.0.0.0/28 --output=text | awk {'print $6'}`);
	 
	#Step 2: Create a subnet for the VPC - use the same /28 cidr block that you used in step 1.  Save the subnet-id to a variable (retrieve it by awk column 6)
	echo -e 'Creating Subnet..'
	SUBNETID=$(aws ec2 create-subnet --vpc-id $VPCID --cidr-block 10.0.0.0/28 --output=text | awk {'print $6'});
	echo 'Subnet Id is ' $SUBNETID

	#Step 3: Create a custom security group per this VPC - store the group ID in a variable (awk $1)
    echo -e 'Creating security group..'
	GRPID=$(aws ec2 create-security-group --group-name $4 --description "Snehal Pawar Security group" --vpc-id $VPCID --output=text | awk {'print $1'});

	#step 3b:  Open the ports For SSH and WEB access to your security group ( this one I give you)
	echo -e 'Opening ports..'
	aws ec2 authorize-security-group-ingress --group-id $GRPID --protocol tcp --port 80 --cidr 0.0.0.0/0 
	aws ec2 authorize-security-group-ingress --group-id $GRPID --protocol tcp --port 22 --cidr 0.0.0.0/0
    aws ec2 authorize-security-group-ingress --group-id $GRPID --protocol tcp --port 3306 --cidr 0.0.0.0/0	

	#Step 4: We need to create an internet gateway so that our VPC has internet access z- save the gaetway ID to a vaiable (awk column 2) 
	echo -e 'Creating internet gateway..'
	GATEWAYID=$(aws ec2 create-internet-gateway | awk {'print $2'});

	#step 4b:  We need to modify the VPC attributes to enable dns support and enable dns hostnames - see the examples note that you cannot combine these options you have to make two modify entries
	echo -e 'Modifying VPC attributes..'
	aws ec2 modify-vpc-attribute --vpc-id $VPCID --enable-dns-support "{\"Value\":true}";
	aws ec2 modify-vpc-attribute --vpc-id $VPCID --enable-dns-hostnames "{\"Value\":true}";

	#Step 5 Modify-subnet-attribute - tell the subnet id to --map-public-ip-on-launch 
	echo -e 'Modifying subnet attributes..'
	aws ec2 modify-subnet-attribute  --subnet-id $SUBNETID --map-public-ip-on-launch ;

	#Step 6:  We need to attach the internet gateway we created to our VPC
	echo -e 'Attaching internet gateway to VPC ' $GATEWAYID;
	RESULTP=(`aws ec2 attach-internet-gateway --internet-gateway-id $GATEWAYID --vpc-id $VPCID`);
	
	#Step 6b: Now lets create a ROUTETABLE variable and use the command create-route-table command to get the routetable id us  | grep rtb | awk {'print $2'}
	echo -e 'Creating route table'
	ROUTETABLEID=(`aws ec2 create-route-table --vpc-id $VPCID | grep rtb | awk {'print $2'}`);

	#Step 6c: Now we create a route to be attached to the route table (I know its kind of verbose but this is what the GUI is doing automatically)  --destination-cidr-block is 0.0.0.0/0 
	echo -e 'Creating route'
	aws ec2 create-route --route-table-id $ROUTETABLEID --gateway-id $GATEWAYID --destination-cidr-block 0.0.0.0/0
	# Now associate that route with a route-table-id and a subnet-id
	echo 'Attaching route to route table'
	aws ec2 associate-route-table --route-table-id $ROUTETABLEID --subnet-id $SUBNETID

	echo -e "Launching instances in AWS ";
	QUERY_RESULTS=$(aws ec2 run-instances --block-device-mappings "[{\"DeviceName\": \"/dev/sdh\",\"Ebs\":{\"VolumeSize\":10}}]" --iam-instance-profile Name=$7 --image-id $2 --count $6 --instance-type t1.micro --security-group-ids $GRPID --key-name $3 --user-data file:$5 --client-token $1  --output text --subnet-id $SUBNETID);
	echo $QUERY_RESULTS ;
	
	echo -e "\nFinished launching EC2 Instances and sleeping 60 seconds"
	for i in {0..60}; do echo -ne '.'; sleep 1;done
	
fi  #End of if statement

