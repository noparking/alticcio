<?php
require_once ('jpgraph/pdf417/jpgraph_pdf417.php');

$data =
'WE THE PEOPLES OF THE UNITED NATIONS DETERMINED to save succeeding generations from the
scourge of war, which twice in our life-time has brought untold sorrow to mankind, and to reaffirm faith in
fundamental human rights, in the dignity and worth of the human person, in the equal rights of men and women
and of nations large and small, and to establish conditions under which justice and respect for the obligations
arising from treaties and other sources of international law can be maintained, and to promote social progress and
better standards of life in larger freedom,

AND FOR THESE ENDS to practice tolerance and live together in peace with one another as good neighbours
and to unite our strength to maintain international peace and security, and to ensure, by the acceptance of
principles and the institution of methods, that armed force shall not be used, save in the common interest, and to
employ international machinery for the promotion of the economic and social advancement of all peoples,

HAVE RESOLVED TO COMBINE OUR EFFORTS TO ACCOMPLISH THESE AIMS Accordingly, our respective
Governments, through representatives assembled in the city of San Francisco, who have exhibited their full
powers found to be in good and due form, have agreed to the present Charter of the United Nations and do
hereby establish an international organisation to be known as the United Nations';

// Setup some symbolic names for barcode specification

$columns = 15;   // Use 8 data (payload) columns
$errlevel = 4;  // Use error level 4
$modwidth = 1;  // Setup module width (in pixels)
$height = 3;    // Height factor (=2)
$showtext = false;  // Show human readable string

// Create a new encoder and backend to generate PNG images
try {
	$encoder = new PDF417Barcode($columns,$errlevel);
	$backend = PDF417BackendFactory::Create(BACKEND_IMAGE,$encoder);
    $backend->SetModuleWidth($modwidth);
    $backend->SetHeight($height);
    $backend->NoText(!$showtext);
    $backend->Stroke($data);
}
catch(JpGraphException $e) {
	echo 'PDF417 Error: '.$e->GetMessage();
}
?>
