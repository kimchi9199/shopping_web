
<?php
session_start();
define('DB_SERVER','localhost');
define('DB_USER','root');
define('DB_PASS' ,'');
define('DB_NAME', 'shopping');
$con = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
// Check connection
if (mysqli_connect_errno())
{
 echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

if(strlen($_SESSION['alogin'])==0)
	{	
header('location:index.php');
}
else{
    $data_points = array();
    try{
        if(isset($_POST['submityear']))
        {
            $year=$_POST['year'];


            $result = mysqli_query($con, "  SELECT B.Year, B.Month, SUM(B.Total_Revenue) as 'Total_Revenue'
                                        FROM (	SELECT A.Year, A.Month, A.Sum_Quantity * p.productPrice as 'Total_Revenue' 
                                                from products p JOIN (SELECT Year(orderDate) as 'Year', Month(orderDate) as 'Month', ProductID, SUM(Quantity) as 'Sum_Quantity' 
                                                                    FROM orders  
                                                                    GROUP BY Month(orderDate), orderDate, ProductID
                                                                    HAVING YEAR(orderDate) = $year) as A 
                                                on p.id = A.productID) as B
                                        GROUP BY (B.Month);");


            $_SESSION['msg']="Category Created !!";
            // $data_points = array();
            $count = 1;
            $last_month = 0;
            while($row = mysqli_fetch_array($result))
            {   for($i = 1; $i <= 12; $i++)
                {
                    if($i == $count)
                    {
                        if($row['Month'] != $i)
                        {
                            $point = array("label" => $i , "y" => 0);
                            $last_month = $i;
                            array_push($data_points, $point);  
                            $count++;
                        }
                        else
                        {
                            $point = array("label" => $row['Month'] , "y" => $row['Total_Revenue']);
                            $last_month = $row['Month'];
                            array_push($data_points, $point);  
                            $count++;
                            break;
                        }
                    }    
                }      
            }
            if($last_month < 12)
            {
                for ($i = $last_month + 1; $i <= 12; $i++)
                {
                    $point = array("label" => $i , "y" => 0);
                    array_push($data_points, $point);
                }
            }
            // echo json_encode($data_points, JSON_NUMERIC_CHECK);
        }
        // $data_points = array();
        // $result = mysqli_query($con, "SELECT B.Month, SUM(B.Total_Revenue) as 'Total_Revenue' FROM (SELECT A.Month, A.Sum_Quantity * p.productPrice as 'Total_Revenue' from products p JOIN (SELECT Month(orderDate) as 'Month', ProductID, SUM(Quantity) as 'Sum_Quantity' FROM orders  GROUP BY Month(orderDate), ProductID) as A on p.id = A.productID) as B GROUP BY (B.Month);");
        // $result = mysqli_query($con, "  SELECT B.Year, B.Month, SUM(B.Total_Revenue) as 'Total_Revenue'
        //                                 FROM (	SELECT A.Year, A.Month, A.Sum_Quantity * p.productPrice as 'Total_Revenue' 
        //                                         from products p JOIN (SELECT Year(orderDate) as 'Year', Month(orderDate) as 'Month', ProductID, SUM(Quantity) as 'Sum_Quantity' 
        //                                                             FROM orders  
        //                                                             GROUP BY Month(orderDate), orderDate, ProductID
        //                                                             HAVING YEAR(orderDate) = '2006') as A 
        //                                         on p.id = A.productID) as B
        //                                 GROUP BY (B.Month);");
        
        // while($row = mysqli_fetch_array($result))
        // {        
        //     $point = array("label" => $row['Month'] , "y" => $row['Total_Revenue']);
            
        //     array_push($data_points, $point);        
        // }
        
        // echo json_encode($data_points, JSON_NUMERIC_CHECK);

    }
    catch(\PDOException $ex){
        print($ex->getMessage());
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin| Manage Products</title>
	<link type="text/css" href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link type="text/css" href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
	<link type="text/css" href="css/theme.css" rel="stylesheet">
	<link type="text/css" href="images/icons/css/font-awesome.css" rel="stylesheet">
	<link type="text/css" href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600' rel='stylesheet'>

    <script src="jquery.js"></script>
    <script src="canvasjs.js"></script>
    <!-- Render Chart -->
    <script>
        window.onload = function () {
        
        var chart = new CanvasJS.Chart("chartContainer", {
            animationEnabled: true,
            exportEnabled: true,
            theme: "light1", // "light1", "light2", "dark1", "dark2"
            title:{
                text: "Monthly Revenue " + <?php echo $year ?>
            },
            axisX: {
                    title: "Month",
                    interval:1,
                    labelMaxWidth: 100,
                    labelAngle: 0,
                    labelFontFamily:"verdana0"
                },
            axisY: {
                title: "Revenue (VND)",
            },
            data: [{
                type: "column", //change type to bar, line, area, pie, etc  
                dataPoints: <?php echo json_encode($data_points, JSON_NUMERIC_CHECK); ?>
            }]
        });
        chart.render();
        
        }

        
    </script>

</head>
<body>
<?php include('include/header.php');?>


	<div class="wrapper">
		<div class="container">
			<div class="row">
<?php include('include/sidebar.php');?>				
			<div class="span9">
					<div class="content">

                    <form class="form-horizontal row-fluid" name="Category" method="post" >			
                        <div class="control-group">
                        <label class="control-label" for="basicinput">Year</label>
                        <div class="controls">
                        <input type="text" placeholder="Enter the year you want to see revenue"  name="year" class="span8 tip" required>
                        <button type="submit" name="submityear" class="btn">ok</button>
                        </div>
                        </div>
					</form>

	<div class="module">
							<div class="module-head">
								<h3>Monthly Statistic </h3>
							</div>
							<div class="module-body table">
	<?php if(isset($_GET['del']))
{?>
									<div class="alert alert-error">
										<button type="button" class="close" data-dismiss="alert">×</button>
									<strong>Oh snap!</strong> 	<?php echo htmlentities($_SESSION['delmsg']);?><?php echo htmlentities($_SESSION['delmsg']="");?>
									</div>
<?php } ?>

									<br />

							
								
                                    <div id="chartContainer" style="height: 370px; width: 100%;"></div>
                                    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>   
				

						
						
					</div><!--/.content-->
				</div><!--/.span9-->
			</div>
		</div><!--/.container-->
	</div><!--/.wrapper-->

<?php include('include/footer.php');?>

	<script src="scripts/jquery-1.9.1.min.js" type="text/javascript"></script>
	<script src="scripts/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script>
	<script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="scripts/flot/jquery.flot.js" type="text/javascript"></script>
	<script src="scripts/datatables/jquery.dataTables.js"></script>
	<script>
		$(document).ready(function() {
			$('.datatable-1').dataTable();
			$('.dataTables_paginate').addClass("btn-group datatable-pagination");
			$('.dataTables_paginate > a').wrapInner('<span />');
			$('.dataTables_paginate > a:first-child').append('<i class="icon-chevron-left shaded"></i>');
			$('.dataTables_paginate > a:last-child').append('<i class="icon-chevron-right shaded"></i>');
		} );
	</script>
</body>
<?php } ?>
