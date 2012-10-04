<?php


class DebugCollector {

    var $iCnt=0;
    var $iCurrentModeTxt='TEXT_SUBMODE_ALPHA';
    var $iCurrentMode =TEXT_SUBMODE_ALPHA;
    var $iSymbols=array();
    var $iOrgData='';
    var $iCompression=USE_TC;

    function DebugCollector() {
    }

    function AddSymbol($aVal,$aVal1='',$aVal2='') {
	$this->iSymbols[$this->iCnt++] = array($aVal,$aVal1,$aVal2);
    }

    function Reset() {
	$this->iSymbols = array();
	$this->iCnt = 0;
    }

    function Cmp($aC2) {

	$n1 = count($this->iSymbols);
	$n2 = count($aC2->iSymbols);

	if( $n1 != $n2 ) {
	    return 0;
	}

	$i=0;
	while($i < $n1 ) {
	    if( $this->iSymbols[$i][0] != $aC2->iSymbols[$i][0] ) {
		return -$i;
	    }
	    ++$i;
	}
	return 1;
    }

    function Dump() {
	if( $this->iCompression == USE_TC ) {
	    for(  $i=0; $i < $this->iCnt; ++$i ) {
		echo "Symbol ".sprintf("%02d",$i+1)." [".$this->iCurrentModeTxt."] : ".sprintf("%03d",$this->iSymbols[$i][0]).
		    " (".sprintf("%02d ",$this->iSymbols[$i][1]).
		    ", ".sprintf("%02d ",$this->iSymbols[$i][2]).")<br>\n";
		$aVal1 = $this->iSymbols[$i][1];
		$aVal2 = $this->iSymbols[$i][2];
		switch( $this->iCurrentMode ) {
		    case TEXT_SUBMODE_ALPHA:
			if( LATCH_TO_LOWER == $aVal1 || LATCH_TO_LOWER == $aVal2 ) {
			    $this->iCurrentModeTxt = 'TEXT_SUBMODE_LOWER';
			    $this->iCurrentMode = TEXT_SUBMODE_LOWER;
			}
			elseif( LATCH_TO_MIXED == $aVal1 || LATCH_TO_MIXED == $aVal2 ) {
			    $this->iCurrentModeTxt = 'TEXT_SUBMODE_MIXED';
			    $this->iCurrentMode = TEXT_SUBMODE_MIXED;
			}
			break;
		    case TEXT_SUBMODE_LOWER:
			if( LATCH_TO_MIXED == $aVal1 || LATCH_TO_MIXED == $aVal2 ) {
			    $this->iCurrentModeTxt = 'TEXT_SUBMODE_MIXED';
			    $this->iCurrentMode = TEXT_SUBMODE_MIXED;
			}
			elseif( SHIFT_TO_ALPHA == $aVal1 || SHIFT_TO_ALPHA == $aVal2 ) {
			    $this->iCurrentModeTxt = 'TEXT_SUBMODE_LOWER';
			    $this->iCurrentMode = TEXT_SUBMODE_LOWER;
			}
			elseif( SHIFT_TO_PUNCT == $aVal1 || SHIFT_TO_PUNCT == $aVal2 ) {
			    $this->iCurrentModeTxt = 'TEXT_SUBMODE_LOWER';
			    $this->iCurrentMode = TEXT_SUBMODE_LOWER;
			}
			break;
		    case TEXT_SUBMODE_MIXED:
			if(  LATCH_TO_ALPHA == $aVal1 || LATCH_TO_ALPHA == $aVal2 ) {
			    $this->iCurrentModeTxt = 'TEXT_SUBMODE_ALPHA';
			    $this->iCurrentMode = TEXT_SUBMODE_ALPHA;
			}
			elseif( LATCH_TO_LOWER == $aVal1 || LATCH_TO_LOWER == $aVal2 ) {
			    $this->iCurrentModeTxt = 'TEXT_SUBMODE_LOWER';
			    $this->iCurrentMode = TEXT_SUBMODE_LOWER;
			}
			elseif( LATCH_TO_PUNCT == $aVal1 || LATCH_TO_PUNCT == $aVal2 ) {
			    $this->iCurrentModeTxt = 'TEXT_SUBMODE_PUNCT';
			    $this->iCurrentMode = TEXT_SUBMODE_PUNCT;
			}
			break;
		    case TEXT_SUBMODE_PUNCT:
			if( LATCH_TO_ALPHA_FROM_PUNCT == $aVal1 || LATCH_TO_ALPHA_FROM_PUNCT == $aVal2 ) {
			    $this->iCurrentModeTxt = 'TEXT_SUBMODE_ALPHA';
			    $this->iCurrentMode = TEXT_SUBMODE_ALPHA;
			}
			break;
		}
	    }
	} // if
	elseif( $this->iCompression == USE_NC ) {
	    for(  $i=0; $i < $this->iCnt; ++$i ) {
		echo "Symbol ".sprintf("%02d",$i+1)." [NUMERIC] : ".sprintf("%03d",$this->iSymbols[$i][0])."<br>\n";
	    }
	}
	else {
	    for(  $i=0; $i < $this->iCnt; ++$i ) {
		echo "Symbol ".sprintf("%02d",$i+1)." [BYTE] : ".sprintf("%03d",$this->iSymbols[$i][0]).
		    " (".sprintf("%02d ",$this->iSymbols[$i][1]).
		    ", ".sprintf("%02d ",$this->iSymbols[$i][2]).")<br>\n";
	    }
	}
    }
}



?>
