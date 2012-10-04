<?php
require_once('jpgraph/pdf417/jpgraph_pdf417.php');
require_once 'debug_collector.inc';

echo "<h2>Debug window</h2>";
echo "<hr><b>Control parameters:</b><br>";print_r($_GET);

$params = array(
    array('data','PDF417'),array('compaction',1),array('modwidth',1),
    array('info',false),array('columns',10),array('errlevel',0),
    array('showtext',false),array('height',3),
    array('showframe',false),array('truncated',false),
    array('vertical',false) ,
    array('backend','IMAGE'), array('file',''),
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


if( $data==="" ) {
    echo "<h3>Please enter data to be encoded and press 'Ok'.</h3>";
}
elseif( $scale < 0.1 || $scale > 15 ) {
    echo "<h4> Scale must be in range [0.1, 15]</h4>";
}
else {
    $dc = new DebugCollector();
    $pdf417 = new PDF417Barcode($columns,$errlevel);

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
            $data = $pdf417->PrepData($data);
            break;
    }

    // Only encode the first part if there are several.
    echo "<hr><b>Raw partioned data:</b><br>";
    print_r($data);
    echo "<p>";
    $pdf417->SetTruncated($truncated);
    $spec = $pdf417->Enc($data);
    echo "<hr><b>Backend specification:</b><br>";
    echo $spec->toString();


    $tc = new TextCompressor($dc);
    $nc = new NumericCompressor($dc);
    $bc = new ByteCompressor($dc);

    $n = count($data);
    echo "<p><hr><b>Symbol array(s):</b><br>";
    for( $i=0; $i< $n; ++$i ) {
        $dc->Reset();
        $dc->iCompression = $data[$i][0];

        switch( $data[$i][0] ) {
            case USE_TC:
                $tc->Encode($data[$i][1]);
                $dc->Dump();
                $r = $tc->Decode($dc);
                echo "<b>Decoded as:</b> $r\n<p>";
            break;
            case USE_NC:
                $nc->Encode($data[$i][1]);
                $dc->Dump();
                $r = $nc->Decode($dc);
                echo "<b>Decoded as:</b> $r\n<p>";
                break;
            default:
                $bc->Encode($data[$i][1]);
                $dc->Dump();
                $r = $bc->Decode($dc);
                echo "<b>Decoded as:</b> ";
                $m = count($r);
                for( $k=0; $k < $m; ++$k ) {
                    echo $r[$k].", ";
                }
                echo "<p>";
            break;
        }
    }

/*
    echo "<p><b>Symbol array:</b><br>";
    $tc->Encode($data[0][1]);
    $dc->Dump();

    $r = $tc->Decode($dc);
    echo "<p><b>Decoded result:</b><br>";
    echo "r=$r\n";
*/
}

?>
