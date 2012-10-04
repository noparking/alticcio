<?php
/*=======================================================================
// File:          QR_IMAGE.PHP
// Description:   Demo application for QR barcodes.
// Created:       2008-08-27
// Ver:           $Id$
//
// Copyright (c) 2008 Aditus Consulting. All rights reserved.
//========================================================================
*/
require_once '../qrencoder.inc.php';

// Setup acceptable paameters to this script
$params = array(
    array('encoding',6),array('data',''),array('modwidth',2),array('version',-1), array('errcorr',-1),
    array('quietzone',10), array('tilde',0),array('imgformat','png'),array('filename',''));


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

if( $modwidth < 1 || $modwidth > 10 ) {
    $txt= '<h4>Module width must be between 1 and 10 pixels</h4>';
}
elseif( $data==="" ) {
    $txt =  '<h3>Please enter data to be encoded.</h3>
    <i>Note: Data must be valid for the chosen encoding.</i>';
}
elseif( $encoding<-1 || $encoding > 2 ) {
    $txt = '<h4>Illegal encoding specified.</h4>';
}
elseif( $version<-1 || $version>40 ) {
    $txt = '<h4>Non valid symbolsize specified.</h4>';
}
elseif( $errcorr<-1 || $errcorr > 3) {
    $txt = '<h4>Non valid error correction level specified.</h4>';    
}
elseif( strlen($filename) > 80 ) {
    $txt = '<h4>Filename can only be 80 characters.</h4>';    
}
else {
    
    // Create a new instance of the encoder using the specified
    // QR version and error correction
    $e=new QREncoder($version,$errcorr);
    
    //$e->SetTilde($tilde==1);
          
    // Use the image backend 
    switch ($imgformat) {
        case 'jpeg':
        case 'png' :
        case 'gif':
        case 'wbmp':
            $backend = BACKEND_IMAGE;
            break;
         
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
            $imgformat = 'png';
            $backend = BACKEND_IMAGE;
            break; 
        
    }
    $b=QRCodeBackendFactory::Create($e, $backend);

    // $b->SetQuietZone($quietzone);
    if( $backend == BACKEND_IMAGE ) {
        $b->SetImgFormat($imgformat);    
        $b->SetColor('black','white');
    }
    
    // Set the module size
    $b->SetModuleWidth($modwidth);
    
    // Stroke the barcode 
    try {
        if( $encoding > -1 ) {
            if( $encoding == 0 )
                $encoding = QREncoder::MODE_ALPHANUM;
            elseif( $encoding == 1)
                $encoding = QREncoder::MODE_NUMERIC;
            elseif( $encoding == 2)
                $encoding = QREncoder::MODE_BYTE;                                
            $data = array( array($encoding,$data) );
        }                
        if( $filename != '') {
           list($version,$errc) =  $b->Stroke($data,$filename);
        }          
        $b->Stroke($data);          
    } catch( QRException $e ) {
        $errstr = $e->getMessage();
        echo '<div style="border:solid black 1px;padding:5px;">Data can not be encoded with chosen combination of version, encoding and image format.<br>Error = <span style="color:darkred;font-weight:bold;font-style:italic;">"'.$errstr.'"</span></div>';    
    }
 
}

if( $txt != '' ) {
    echo '<html><head><LINK REL=STYLESHEET TYPE="text/css" HREF="demoapp.css"></head>';
    echo '<body>';
    echo '<div class="infotext">';
    echo $txt;
    echo '</div>';
    echo '</body></html>';
}

?>