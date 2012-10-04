<?php
require_once('jpgraph/jpgraph.php');
require_once('jpgraph/jpgraph_canvas.php');
require_once('jpgraph/pdf417/jpgraph_pdf417.php');



$params = array(
    array('data','PDF417'),array('compaction',1),array('modwidth',1),
    array('info',false),array('columns',10),array('errlevel',0),
    array('showtext',false),array('height',3),
    array('showframe',false),array('truncated',false),
    array('vertical',false) ,
    array('backend','PS'), array('file',''),
    array('scale',1), array('pswidth','') );

$n=count($params);
for($i=0; $i < $n; ++$i ) {
    $v  = $params[$i][0];
    if( empty($_GET[$params[$i][0]]) ) {
        $$v = $params[$i][1];
    }
    else
        $$v = $_GET[$params[$i][0]];
}

if( $data=="" ) {
    echo "<h3>Please enter data to be encoded and press 'Ok'.</h3>";
}
elseif( $columns < 2 || $columns > 30 ) {
    echo "<h4> Columns must be in range [2, 30]</h4>";
}
elseif($scale < 0.2 || $scale > 15 )  {
    echo "<h4> Scale must be in range [0.2, 15]</h4>";
}
else {

    // Setup data
    switch( $compaction ) {
        case 2: // Alpha
            $data = array(array(USE_TC,$data));
            break;
        case 3:  // Numeric
            $data = array(array(USE_NC,$data));
            break;
        case 4: // Byte
            $data = array(array(strlen($data)%6==0 ? USE_BC_E6 : USE_BC_O6,$data));
            break;
        default: // Auto
            break;
    }

    $encoder = new PDF417Barcode($columns,$errlevel);
    $encoder->SetTruncated($truncated);
    $b =  $backend=='EPS' ? 'PS' : $backend;
    $b = substr($backend,0,5) == 'IMAGE' ? 'IMAGE' : $b;
    $e = PDF417BackendFactory::Create($b,$encoder);

    if( substr($backend,0,5) == 'IMAGE' ) {
        if( substr($backend,5,1) == 'J' )
            $e->SetImgFormat('JPEG');
    }

    if( $e ) {
        if( $backend == 'EPS' )
            $e->SetEPS();
        if( $pswidth!='' && $b == 'PS' )
            $modwidth = $pswidth;
        $e->SetModuleWidth($modwidth);
        $e->NoText(!$showtext);
        $e->SetScale($scale);
        $e->SetVertical($vertical);
        $e->ShowFrame($showframe);
        $e->SetHeight($height);
        $r = $e->Stroke($data,$file);

        if( $r )
            echo nl2br(htmlspecialchars($r));
        if( $file != '' )
            echo "<p>Wrote file $file.";
    }
    else
        echo "<h3>Can't create choosen backend: $backend.</h3>";
}

?>
