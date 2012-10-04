<?php
require_once '../datamatrix.inc.php';

$params = array(
    array('encoding',6),array('data',''),array('modwidth',2),array('symsize',-1),array('quietzone',10),
    array('tilde',0),array('format','image'));


$n=count($params);
for($i=0; $i < $n; ++$i ) {
    $v  = $params[$i][0];
    if( !isset($_GET[$params[$i][0]]) ) {
	$$v = $params[$i][1];
    }
    else
	$$v = $_GET[$params[$i][0]];
}

$txt='';

if( $modwidth < 1 || $modwidth > 5 ) {
    $txt= '<h4>Module width must be between 1 and 5 pixels</h4>';
}
elseif( $data==="" ) {
    $txt =  '<h3>Please enter data to be encoded.</h3>
    <i>Note: Data must be valid for the chosen encoding.</i>';
}
elseif( $encoding==-1 ) {
    $txt = '<h4>No code symbology selected.</h4>';
}
elseif( $symsize<-1 || $symsize>29 ) {
    $txt = '<h4>Non valid symbolsize specified.</h4>';
}
else {
    
    $coder = new Datamatrix(); 
    $coder->SetSize($symsize);
    $coder->SetEncoding($encoding);
    $coder->SetTilde($tilde==1);

    switch( $format ) {
	case 'ps':
	    $backend = BACKEND_PS;
	    break;
	case 'eps':
	    $backend = BACKEND_EPS;
	    break;
	case 'ascii':
	    $backend = BACKEND_ASCII;
	    break;
	default:
	    $backend = BACKEND_IMAGE;
	    break;
    }

    $e = DatamatrixBackendFactory::Create($coder,$backend);
    $e->SetModuleWidth($modwidth);
    $e->SetQuietZone($quietzone);

    //$e->SetImgFormat($imgformat);
    
    $e->SetColor('black@0.1','white@0.6','lightyellow@0.7');
    
    try {
	$r = $e->Stroke($data);
    } catch (DMExceptionL $e) {
	$errstr = $e->getmessage();
	echo '<div style="border:solid black 1px;padding:5px;">Data can not be encoded with chosen combination of symbol size, encoding and image format.<br>Error = <span style="color:darkred;font-weight:bold;font-style:italic;">"'.$errstr.'"</span></div>';
    }
}

if( $txt != '' ) {
    echo '<HEAD><LINK REL=STYLESHEET TYPE="text/css" HREF="demoapp.css"></HEAD>';
    echo '<BODY>';
    echo '<div class="infotext">';
    echo $txt;
    echo '</div>';
    echo '</BODY>';
}
?>