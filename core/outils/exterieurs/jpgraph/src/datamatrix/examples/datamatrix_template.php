<?php
require_once('jpgraph/datamatrix/datamatrix.inc.php');

$data = '0123456789';

$shape       = DMAT_AUTO;
$encoding    = ENCODING_AUTO;
$modulewidth = 4;
$quietzone   = 10;
$color1      = 'black';
$color0      = 'white';
$colorq      = 'white';
$outputfile  = '';

// Create and set parameters for the encoder
$encoder = DatamatrixFactory::Create($shape);
$encoder->SetEncoding($encoding);

// Create the image backend (default)
$backend = DatamatrixBackendFactory::Create($encoder);

// By default the module width is 2 pixel so we increase it a bit
$backend->SetModuleWidth($modulewidth);

// Set Quiet zone
$backend->SetQuietZone($quietzone);

// Set other than default colors (one, zero, quiet zone/background)
$backend->SetColor($color1, $color0, $colorq);

// Create the barcode from the given data string and write to output file
try {
    $backend->Stroke($data,$outputfile);
} catch (Exception $e) {
    $errstr = $e->GetMessage();
    echo "Datamatrix error message: $errstr\n";
}
?>
