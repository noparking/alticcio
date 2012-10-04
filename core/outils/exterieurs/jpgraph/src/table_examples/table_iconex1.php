<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_canvas.php');
require_once ('jpgraph/jpgraph_table.php');
require_once ('jpgraph/jpgraph_iconplot.php');

// Create a canvas graph where the table can be added
$graph = new CanvasGraph(700,300);

// Setup the basic table
$data = array(
    array('',        'April', 'May','June','July','August'),
    array('',        'Triumph', 'Triump','Triumph','Triumph','Triumph'),
    array('2005',7,13,17,15,8),
    array('2006',7,34,35,26,20),
    array('2007',7,41,43,49,45),
    array('Sum:',21,88,95,90,73)
    );

// Setup the basic table and default font
$table = new GTextTable();
$table->Set($data);
$table->SetFont(FF_TIMES,FS_NORMAL,11);

// Setup default horizontal ad vertical alignment
$table->SetAlign('left','top');

// Header row
$table->SetRowFont(0,FF_ARIAL,FS_BOLD,12);
$table->SetRowColor(0,'white');
$table->SetRowAlign(0,'center');
$table->SetRowFillColor(0,'darkorange');

// Set text color
$table->SetRowFont(1,FF_ARIAL,FS_BOLD,12);
$table->SetRowColor(1,'white');

//Set summary row format
$table->SetRowFont(5,FF_ARIAL,FS_BOLD,12);

// Setup overall grid
$table->SetGrid(1);

///$table->SetRowFillColor(4,'lightgray@0.5');
$table->SetColFillColor(0,'lightgray@0.5');
$table->SetFillColor(0,0,4,0,'lightgray@0.5');

// Setup column = 2 minimum width to 100 pixels regardeless of content
$table->SetMinColWidth(2,100);

// Add images of motorcycles to the top row
$table->SetRowAlign(1,'center','top');
for( $i=1; $i <= 5; ++$i ) {
	$table->SetCellImage(1,$i,"tr$i.jpg");
	$table->SetCellImageConstrain(1,$i,TIMG_HEIGHT,80);
}

// Add table to the graph
$graph->Add($table);

// send it back to the client
$graph->Stroke();

?>

