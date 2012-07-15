<?php include('header.php'); 
include ('gen-sched-func.php');
//This file is used both to add new vaccine as well as edit existing one
if(isset($_POST['submit']))
{  //If the Register form has been submitted
	$err = array();

	if(!isset($_POST['name']) || !isset($_POST['no_of_days']) || !isset($_POST['lower_limit']) || !isset($_POST['upper_limit']) )
	{
		$err[] = 'Please fill all fields!';
	}

	if((!is_numeric($_POST['no_of_days']))||(!is_numeric($_POST['lower_limit']))||(!is_numeric($_POST['upper_limit'])))
	{
		$err[] = "Please enter valid no. of days!";
	}

	if(!count($err))
	{
		$_POST['name'] = mysql_real_escape_string($_POST['name']);
		
		if(isset($_POST['id']))	//If editing vac
		{
			echo 'here';
			if($_POST['update']=='1')	//If need to update existing schedule
			{
				$vac_temp = mysql_fetch_assoc(mysql_query("SELECT no_of_days FROM vaccines WHERE id={$_POST['id']}"));
				$daystoadd = intval($_POST['no_of_days']) - intval($vac_temp['no_of_days']);
				if(!mysql_query("UPDATE vac_schedule SET date=date + {$daystoadd} WHERE v_id = {$_POST['id']} AND given='N'"))
					echo "Error updating in current records";
			}
			mysql_query("UPDATE vaccines SET name='{$_POST['name']}', 
				no_of_days={$_POST['no_of_days']}, 
				lower_limit={$_POST['lower_limit']}, 
				upper_limit={$_POST['upper_limit']} WHERE id={$_POST['id']}");
		}
		else 	//If adding new vac
		{
			mysql_query("INSERT INTO vaccines(name, no_of_days, dependent, sex, lower_limit, upper_limit)
					VALUES(
					'".$_POST['name']."',
					".$_POST['no_of_days'].",
					".$_POST['dependent'].",
					'".$_POST['sex']."',
					".$_POST['lower_limit'].",
					".$_POST['upper_limit'].")");
			if($_POST['update']=='1')
			{
				$_POST['id'] = mysql_insert_id();
				generate_vaccine_schedule($_POST);
			}
		}

		if(mysql_affected_rows($link)==1)
		{	
			$_SESSION['msg']['reg-success']='Vaccine successfully added!';
		}
		else $err[]='An unknown error has occured.';
	}
	
	if(count($err))
	{
		$_SESSION['msg']['reg-err'] = implode('<br />',$err);
	}	
}
						
if($_SESSION['msg']['reg-err'])
{
	echo '<div class="err">'.$_SESSION['msg']['reg-err'].'</div>';
	unset($_SESSION['msg']['reg-err']);
}

if($_SESSION['msg']['reg-success'])
{
	echo '<div class="success">'.$_SESSION['msg']['reg-success'].'</div>';
	unset($_SESSION['msg']['reg-success']);
}

?>

<?php include('footer.php'); ?>