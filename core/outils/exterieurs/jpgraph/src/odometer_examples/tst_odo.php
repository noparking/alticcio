<?php
//=============================================================================
// File:	ODOEX01.PHP
// Description: Example 1 for odometer graphs
// Created:	2002-02-22
// Version:	$Id$
// 
// Comment:
// Example file for odometer graph. This examples extends odoex00
// by adding titles, captions and indicator band to the fascia.
//
// Copyright (C) 2002 Johan Persson. All rights reserved.
//=============================================================================
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_odo.php');

//---------------------------------------------------------------------
// Create a new odometer graph 
//---------------------------------------------------------------------
$graph = new OdoGraph(230,115);

//---------------------------------------------------------------------
// Set white plot and margin area
//---------------------------------------------------------------------
$graph->SetColor('white');
$graph->SetMarginColor('white');

//---------------------------------------------------------------------
// Just enough margin to show the frame
//---------------------------------------------------------------------
$graph->SetMargin(1,1,1,1);

//---------------------------------------------------------------------
// Now we need to create an odometer to add to the graph.
// By default the scale will be 0 to 100
//---------------------------------------------------------------------
$odo = new Odometer();

//---------------------------------------------------------------------
// Make the odometer cover the entire image
// (If the size is > 1.0 then it will be interpretated as absolute size)
//---------------------------------------------------------------------
$odo->SetSize(1);

//---------------------------------------------------------------------
// Specify no margin around the odometer
//---------------------------------------------------------------------
$odo->SetMargin(0);

//---------------------------------------------------------------------
// Set color indication between values 80 and 100 as red
//---------------------------------------------------------------------
$odo->AddIndication(80,100,"red");

//---------------------------------------------------------------------
// Set display value for the odometer
//---------------------------------------------------------------------
$odo->needle->Set(30);

//---------------------------------------------------------------------
// Add the odometer to the graph
//---------------------------------------------------------------------
$graph->Add($odo);

//---------------------------------------------------------------------
// ... and finally stroke and stream the image back to the browser
//---------------------------------------------------------------------
$graph->Stroke();

// EOF
?>