<?php 

//$d=$_GET['data']; $modwidth=$_GET['modwidth'];  
$params = array(
    array('data','PDF417'),array('compaction',1),array('modwidth',1),
    array('info',false),array('columns',10),array('errlevel',0),
    array('showtext',false),array('height',3),
    array('showframe',false),array('truncated',false),
    array('vertical',false) , 
    array('backend','IMAGE'), array('file',''),
    array('scale',1), array('pswidth','') );

$n=count($params);
$s='';
for($i=0; $i < $n; ++$i ) {
    $v  = $params[$i][0];
    if( empty($_GET[$params[$i][0]]) ) {
	$$v = $params[$i][1];
    }
    else
	$$v = $_GET[$params[$i][0]];
    $s .= $v.'='.urlencode($$v).'&';
}

if( $data=="" ) {
    die( "<h3> Error. Please enter data to be encoded and press 'Ok'.</h3>");
}
elseif( strlen($data)>1000 ) {
    die( "<h3> Error. To many input characters must be < 1000" );
}
elseif( $columns < 2 || $columns > 30 ) {
    die( "<h4> Error. Columns must be in range [2, 30]</h4>" );
}
elseif($scale < 0.2 || $scale > 15 )  {
    die( "<h4> Error. Scale must be in range [0.2, 15]</h4>" );
}

//echo "s=$s<p>";

?>

<!doctype html public "-//W3C//DTD HTML 4.0 Frameset//EN">
<html>
<?php
if( $info ) {
    if( $vertical ) {
	echo "<frameset cols=\"150,*\" name=frameset2>\n";
    }
    else {
	echo "<frameset rows=\"200,*\" name=frameset2>\n";
    }
    echo "<frame src=\"pdf417_demo_image.php?$s\" name=barcode>"; 
    echo "<frame src=\"pdf417_demo_debug.php?$s\" name=debug>"; 
    //echo "<frame src=\"http://www.idautomation.com/PDF417JavaBean/PDF417AppletTest.html\">"; 
    echo "</frameset>";
}
else {
    echo "<frameset cols=\"*\" name=frameset2>\n";
    echo "<frame src=\"pdf417_demo_image.php?$s\" name=barcode>"; 
    echo "</frameset>";
}

?>

</html>
