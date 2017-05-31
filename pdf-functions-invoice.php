<?php

class PDF extends FPDF
{

	function Header()
	{
		// Arial bold 15


	}

	function InvoiceDetails($info, $doctor) {

		if($doctor == 'Dr. Mahima') {
			$doctor_name = 'Dr. Mahima Anurag';
			$doctor_degree = "MBBS, MD(Pediatrics)";
			$doctor_work = "Consultant Child Specialist and Neonatologist";
			$doctor_regn = "DMC-3334";
		} else {
			$doctor_name = 'Dr. Anurag Saxena';
			$doctor_degree = "MBBS, MD(Medicine)";
			$doctor_work = "Consultant Physician";
			$doctor_regn = "DMC-3283";
		}
		$this->SetFont('Arial','B',16);
		$this->Cell(100,15, $doctor_name);
		$this->SetFont('Arial','B',12);
		$this->Cell(70,7,"Specialists' Clinic",'',1,'R');

		$this->SetFont('Arial','',12);
		$this->Cell(100,15, $doctor_degree);
		$this->Cell(70,5,'C-14 Community Centre','',1,'R');

		$this->Cell(100,15, $doctor_work.' ');
		$this->Cell(70,5,'Naraina Vihar','',1,'R');

		$this->Cell(100,15,'Regn. No. '.$doctor_regn);
		$this->Cell(70,5,'New Delhi - 110028','',1,'R');

		$this->Cell(100,15,'');
		$this->Cell(70,5,'Tel. 9717585207','',1,'R');


		$this->Ln(7);
		$headers = array("Invoice No.:", "Invoice date:", "Patient ID:", "Patient name:");
		for($i = 0; $i < 2; ++$i)
    {
				$this->Cell(30,5,$headers[$i*2]);
				$this->Cell(40,5,$info[$i*2]);
				$this->Cell(30,5,$headers[$i*2+1]);
				$this->Cell(40,5,$info[$i*2+1]);
        $this->Ln();
    }
	}

	function AmountDetails($amountInfo, $mode) {
		$this->Ln(5);
		$descriptions = explode("*",$amountInfo[0]);
		$amounts = explode("*",$amountInfo[1]);
		$this->SetFont('Arial','B',12);
		$this->SetFillColor(200,200,200);
		$this->Cell(15,7,'S.No.','1','','C');
		$this->Cell(120,7,'Description','1','','C');
		$this->Cell(30,7,'Amount','1','','C');
		$this->Ln();
		$this->SetFont('Arial','',10);
		$total = 0;
		$fill = 0;
		for($i = 0; $i < sizeof($descriptions); $i++) {
			$this->Cell(15,7,$i+1,'LR','','C',$fill);
			if($descriptions[$i]!="CONSULTATION") {
				$descriptions[$i].=" Vaccination";
			}
			$this->Cell(120,7,"  ".$descriptions[$i],'LR','','L',$fill);
			$this->Cell(30,7,$amounts[$i]."  ",'LR','','R',$fill);
			$this->Ln();
			$total += intval($amounts[$i]);
			$fill = !$fill;
		}
		$this->Cell(15,7,'','LRB','','L', $fill);
		$this->Cell(120,7,"Grand Total:",'LRB','','R', $fill);
		$this->Cell(30,7,$total."  ",'LRB','','R', $fill);

		$this->Ln(12);
		$stringTotal = (string)$total;
		$this->SetFont('Arial','B',12);
		$this->Cell(70,5,"To Pay: Rs. ".$stringTotal."  only",'','','L');
		$this->Ln();
		$this->SetFont('Arial','',12);
		$this->Cell(70,5,"Mode of payment: ".$mode,'','','L');
		$this->Ln();
	}



	// Page footer
	function Footer()
	{
		$this->Ln(10);
		$this->SetFont('Arial','I',12);
		$this->Cell(165,5,"Authorised signatory",'','','R');
	}
}

	$pdf = new PDF();
	$pdf->SetMargins(20,20,10);

	$pdf->AddPage();
	$invoiceInfo = mysqli_fetch_assoc(mysqli_query($link, "SELECT i.invoice_id as id, i.p_id as p_id, i.date as date, i.mode as mode, i.descriptions as descriptions, i.amounts as amounts, p.name as name, i.doctor as doctor FROM patients p, invoice i WHERE i.id = {$_GET['id']} AND p.id = i.p_id"));
	$info = array($invoiceInfo["id"], date('d M Y', strtotime($invoiceInfo["date"])), $invoiceInfo["p_id"], $invoiceInfo["name"]);
	$doctor = $invoiceInfo['doctor'];
	$amountInfo = array($invoiceInfo["descriptions"], $invoiceInfo["amounts"]);
	$mode = $invoiceInfo["mode"];
	$pdf->InvoiceDetails($info, $doctor);
	$pdf->AmountDetails($amountInfo, $mode);




	$pdf->Output();
	// return $pdf;


?>
