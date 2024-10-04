<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

require_once('fpdf.php');





class PDF_MC_Table extends FPDF
{
var $widths;
var $aligns;

function SetWidths( $w)
{
    //Set the array of column widths
    $this->widths=$w;
}

function SetAligns( $a)
{
    //Set the array of column alignments
    $this->aligns=$a;
}

function Row( $data,$border=TRUE,$bold=FALSE)
{
    //Calculate the height of the row
    $nb=0;
    for ( $i=0; $i<count( $data); $i++)
	{
		
        $nb=max( $nb,$this->NbLines( $this->widths[$i],$data[$i] ) );
	}
	$h=8*$nb;
    //Issue a page break first if needed
    $this->CheckPageBreak( $h);
    //Draw the cells of the row
    for ( $i=0; $i<count( $data); $i++)
    {
        $w=$this->widths[$i];
        $a=isset( $this->aligns[$i] ) ? $this->aligns[$i] : 'L';
        //Save the current position
        $x=$this->GetX();
        $y=$this->GetY();
        //Draw the border
        if( $border)$this->Rect( $x,$y,$w,$h);
        //Print the text
        //bold first column
        if( $i==0)  {$this->SetFont('','B');}else{$this->SetFont('');}
        if( $bold)  {$this->SetFont('','B');}
        $this->MultiCell( $w,8,$data[$i],0,$a);
        //Put the position to the right of the cell
        $this->SetXY( $x+$w,$y);
    }
    //Go to the next line
    $this->Ln( $h);
}

function CheckPageBreak( $h)
{
    //If the height h would cause an overflow, add a new page immediately
    if( $this->GetY()+$h>$this->PageBreakTrigger)
        $this->AddPage( $this->CurOrientation);
}

function NbLines( $width,$txt)
{
    
    $string=str_replace("\r",'',$txt);
    $stringDimension=$this->getStringWidth( $string);
    
    $numberOfLines=ceil( $stringDimension/( $width-5) );
    
    return $numberOfLines;
}
}
