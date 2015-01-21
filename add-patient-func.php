<?php
include_once('gen-sched-func.php');
include_once('header.php');

function checkEmail($str)
{
	return preg_match("/^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $str);
}



function validatePatient($patient_var)
{
	$err = array();
	
	if(($patient_var['email']))
	{
		if(!checkEmail($patient_var['email']))
		{
			$err[]='Your email is not valid!';
		}
	}

	if(($patient_var['phone']))
	{
		if( !preg_match("/^[0-9]{1,}$/", $patient_var['phone']) )
		{
			$err[]='Your phone number 1 is not valid!';
		}
	}

	if(($patient_var['phone2']))
	{
		if( !preg_match("/^[0-9]{1,}$/", $patient_var['phone2']) )
		{
			$err[]='Your phone number 2 is not valid!';
		}
	}
	
	if(!$patient_var['name'] || !$patient_var['dob'])
	{
		$err[] = 'First name and date of birth must be filled!';
	}

	if(strtotime($patient_var['dob']) > strtotime('now'))
	{
		$err[] = 'Enter a valid date!';
	}

	return $err;
}



function prePatient(&$patient_var)
{
	$patient_var['first_name'] = ucwords($patient_var['first_name']);
	$patient_var['last_name'] = ucwords($patient_var['last_name']);
	$patient_var['address'] = ucwords($patient_var['address']);
	$patient_var['name'] = $patient_var['first_name']." ".$patient_var['last_name'];
	$patient_var['father_name'] = ucwords($patient_var['father_name']);
	$patient_var['mother_name'] = ucwords($patient_var['mother_name']);
	$patient_var['obstetrician'] = ucwords($patient_var['obstetrician']);
	$patient_var['place_of_birth'] = ucwords($patient_var['place_of_birth']);
	if(!$patient_var['active'])
	{
		$patient_var['active'] = '0';
	}
	if((!$patient_var['doregistration'])||($patient_var['doregistration']=='0000-00-00'))
	{
		$patient_var['doregistration'] = $patient_var['dob'];
	}
	if($patient_var['phone'][0]!='0')
			$patient_var['phone']='0'.$patient_var['phone'];
	if($patient_var['phone2'])
	{	
		if($patient_var['phone2'][0]!='0')
			$patient_var['phone2']='0'.$patient_var['phone2'];
	}
}



function addPatient($patient_var)
{
	global $link;
	prePatient($patient_var);
	$err = validatePatient($patient_var);
	if(!count($err))
	{
		$patient_var['email'] = mysqli_real_escape_string($link, $patient_var['email']);
		$patient_var['name'] = mysqli_real_escape_string($link, $patient_var['name']);
		$patient_var['phone'] = mysqli_real_escape_string($link, $patient_var['phone']);
		$patient_var['dob'] = mysqli_real_escape_string($link, $patient_var['dob']);
		
		// Escape the input data
		if(mysqli_query($link, "INSERT INTO patients(name,first_name,last_name,email,dob,phone,phone2,sex,father_name,father_occ,mother_name,mother_occ,address,
			birth_weight,born_at,head_circum,length,mode_of_delivery,gestation,sibling,active,date_of_registration,obstetrician,place_of_birth)
					VALUES(
					'".$patient_var['name']."', '".$patient_var['first_name']."', '".$patient_var['last_name']."',
					'".$patient_var['email']."',
					'".$patient_var['dob']."',
					'".$patient_var['phone']."',
					'".$patient_var['phone2']."',
					'".$patient_var['sex']."',
					'".$patient_var['father_name']."',
					'".$patient_var['father_occ']."',
					'".$patient_var['mother_name']."',
					'".$patient_var['mother_occ']."',
					'".$patient_var['address']."',
					'".$patient_var['birth_weight']."',
					'".$patient_var['born_at']."',
					'".$patient_var['head_circum']."',
					'".$patient_var['length']."',
					'".$patient_var['mode_of_delivery']."',
					'".$patient_var['gestation']."',
					'".$patient_var['sibling']."',
					".$patient_var['active'].",
					'".$patient_var['doregistration']."',
					'".$patient_var['obstetrician']."',
					'".$patient_var['place_of_birth']."')"))
		{	
			$new_patient_id = mysqli_insert_id($link);
			$_SESSION['msg']['reg-success']="Patient successfully added! Patient id is <strong>".$new_patient_id."</strong>";
			//This code for sibling is only valid if there is only 1 sibling at most
			//Need to implement a method with equivalence classes
			//Hashed code was probably an incorrect implementation
			if($patient_var['sibling']!=0)
			{
				$row_sibling = mysqli_fetch_assoc(mysqli_query($link, "SELECT sibling FROM patients WHERE id={$patient_var['sibling']}"));
				// if(!$row_sibling['sibling'])	//If sibling does not have any other sibling
				// {
					if(!mysqli_query($link, "UPDATE patients SET sibling='{$new_patient_id}' WHERE id={$patient_var['sibling']}"))
						$err[]="Some error in adding sibling";
				// }
				// else //If sibling has other sibling(s)
				// {
				// 	$new_sibling = $row_sibling['sibling'].",".$patient_var['sibling'];
				// 	echo "UPDATE patients SET sibling={$new_sibling} WHERE id={$patient_var['sibling']}";
				// 	if(!mysqli_query($link, "UPDATE patients SET sibling='{$new_sibling}' WHERE id={$patient_var['sibling']}"))
				// 		$err[]="Some error in adding sibling";
				// 	if(!mysqli_query($link, "UPDATE patients SET sibling='{$new_sibling}' WHERE id={$new_patient_id}"))
				// 		$err[]="Some error in adding sibling";
				// }
			}
			if($patient_var['gen_sched']=='1')
			{
				generate_patient_schedule($new_patient_id);
			}
		}
		else $err[]='An unknown error has occured.';
	}
	
	if(count($err))
	{
		$_SESSION['msg']['reg-err'] = implode('<br />',$err);
		return 0;
	}
	else
	{
		return $new_patient_id;
	}
}



function editPatient($patient_var)
{
	global $link;
	prePatient($patient_var);
	$err = validatePatient($patient_var);
	if(!count($err))
	{
		$patient_var['email'] = mysqli_real_escape_string($link, $patient_var['email']);
		$patient_var['name'] = mysqli_real_escape_string($link, $patient_var['name']);
		$patient_var['phone'] = mysqli_real_escape_string($link, $patient_var['phone']);
		$patient_var['dob'] = mysqli_real_escape_string($link, $patient_var['dob']);
		$patient_var['add_sibling'] = mysqli_real_escape_string($link, $patient_var['add_sibling']);
		// Escape the input data

		if(mysqli_query($link, "UPDATE patients SET 
			name = '{$patient_var['name']}',
			first_name = '".$patient_var['first_name']."',
			last_name =  '".$patient_var['last_name']."',
			email = '".$patient_var['email']."',
			dob = '".$patient_var['dob']."',
			phone = '".$patient_var['phone']."',
			phone2 = '".$patient_var['phone2']."',
			sex = '".$patient_var['sex']."',
			father_name = '".$patient_var['father_name']."',
			father_occ = '".$patient_var['father_occ']."',
			mother_name = '".$patient_var['mother_name']."',
			mother_occ = '".$patient_var['mother_occ']."',
			birth_weight = '".$patient_var['birth_weight']."',
			born_at = '".$patient_var['born_at']."',
			head_circum = '".$patient_var['head_circum']."',
			length = '".$patient_var['length']."',
			mode_of_delivery = '".$patient_var['mode_of_delivery']."',
			gestation = '".$patient_var['gestation']."',
			active = '".$patient_var['active']."',
			date_of_registration = '".$patient_var['date_of_registration']."',
			obstetrician = '".$patient_var['obstetrician']."',
			place_of_birth = '".$patient_var['place_of_birth']."',
			address = '".$patient_var['address']."' WHERE id = {$patient_var['id']}"))
		{
			$_SESSION['msg']['reg-success']="Patient successfully edited!";
		}
		else
			$err[]='An unknown error has occured.';
		if($patient_var['delete_siblings'])
		{
			$total_string = "(";
			foreach ($patient_var['delete_siblings'] as $key => $value) {
				$total_string = $total_string.$value.",";
			}
			$total_string = $total_string.$patient_var['id'].")";
			if(mysqli_query($link, "DELETE FROM siblings WHERE (s_id IN {$total_string}) AND (p_id IN {$total_string})"))
			{
				$_SESSION['msg']['reg-success'] = $_SESSION['msg']['reg-success']."<br>Sibling(s) deleted!";
			}
			else
				$err[] = 'Error deleting sibling(s)';
		}
		if($patient_var['add_sibling'] > 0)
		{
			$siblings_result = mysqli_query($link, "SELECT * FROM siblings WHERE p_id = {$patient_var['id']}");
			$total_string = " ";
			while($row = mysqli_fetch_assoc($siblings_result))
			{
				$total_string = $total_string."(".$row['s_id'].",".$patient_var['add_sibling']."),";
				$total_string = $total_string."(".$patient_var['add_sibling'].",".$row['s_id']."),";
			}
			$total_string = $total_string."(".$patient_var['id'].",".$patient_var['add_sibling']."),";
			$total_string = $total_string."(".$patient_var['add_sibling'].",".$patient_var['id'].")";
			if(mysqli_query($link, "INSERT INTO siblings(p_id, s_id) VALUES ".$total_string))
			{
				$_SESSION['msg']['reg-success'] = $_SESSION['msg']['reg-success']."<br>Sibling added!";
			}
			else
				$err[] = 'Error adding sibling';
		}
	}

	if(count($err))
	{
		$_SESSION['msg']['reg-err'] = implode('<br />',$err);
	}
}
?>