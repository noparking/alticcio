<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!--
/*=======================================================================
// File:          QR_MENU.PHP
// Description:   Demo application for QR barcodes.
// Created:       2008-08-27
// Ver:           $Id$
//
// Copyright (c) 2008 Aditus Consulting. All rights reserved.
//========================================================================
*/
-->


<html>
<head>
  <link rel="STYLESHEET" type="text/css" href="demoapp.css">
  <title>QR Menu frame</title>
</head>

<body>
  <h3>QR Barcode</h3>

  <div class="menu">
    <form name="qrspec" action="qr_image.php" target="imageframe"
    method="" post="" id="qrspec">
      <table cellspacing="4" cellpadding="0">
        <tr>
          <td colspan="2">Data:<br>
          <textarea cols="30" rows="3" name="data">01234567</textarea></td>
        </tr>
<!--
<tr>
<td colspan=2>
<input type=checkbox value=1 name=tilde> Process tilde
</td>
</tr>
-->

        <tr>
          <td>Encoding:<br>
          <select name="encoding">
            <option value="-1">
              Automatic
            </option>

            <option value="0">
              ALPHANUMERIC
            </option>

            <option value="1">
              NUMERIC
            </option>

            <option value="2">
              BYTE
            </option>
          </select></td>

          <td>Module size:<br>
          <select name="modwidth">
            <option value="1">
              One
            </option>

            <option value="2">
              Two
            </option>

            <option value="3" selected>
              Three
            </option>

            <option value="4">
              Four
            </option>

            <option value="5">
              Five
            </option>

            <option value="6">
              Six
            </option>

            <option value="7">
              Seven
            </option>

            <option value="8">
              Eight
            </option>

            <option value="9">
              Nine
            </option>
          </select></td>
        </tr>

        <tr>
          <td>Version:<br>
          <select name="version">
            <option value="-1" selected>
              Auto
            </option>
<?php
    $n=40;
    for($i=1; $i <= $n; $i++) {
        echo "<option value=\"$i\"> $i </option>";
    }
?>
          </select></td>

          <td>Error correction:<br>
          <select name="errcorr">
            <option value="-1">
              Auto
            </option>

            <option value="0">
              L
            </option>

            <option value="1">
              M
            </option>

            <option value="2">
              Q
            </option>

            <option value="3">
              H
            </option>
          </select></td>
        </tr>

        <tr>
          <td>Image format:<br>
          <select name="imgformat">
            <option value="auto">
              Auto
            </option>

            <option value="png">
              PNG
            </option>

            <option value="jpeg">
              JPEG
            </option>

            <option value="gif">
              GIF
            </option>

            <option value="wbmp">
              WBMP
            </option>
            
            <option value="ps">
              Postscript
            </option>   

            <option value="eps">
              EPS
            </option>                        
                     
            <option value="ascii">
              ASCII
            </option>                        
                     
          </select></td>

          <td>Save to:<br>
          <input type="text" name="filename" value="" align="left"
          size="15" maxlength="80"></td>
        </tr>

        <tr>
          <td colspan="2" align="right" valign="bottom">
            <hr>
            <input type="submit" name="submit" value=
            "&nbsp; Create &nbsp;">
          </td>
        </tr>
      </table>
    </form>
  </div>
</body>
</html>
