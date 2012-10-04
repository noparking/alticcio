<head>
<LINK REL=STYLESHEET TYPE="text/css" HREF="demoapp.css">
</head>

<h3>PDF417 Demo</h3>

<div class="menu">

<form name="barcodespec" action="demo_imgframes.php" target=imgframes method post>

<table cellspacing=4 cellpadding=0>
<tr>
<td colspan=2>
Data:<br>
<textarea cols=30 rows=4 name=data>PDF417</textarea>
</td>

<tr><td>
Encoding:<br>
<select name=compaction>
<option  selected  value=1> Auto </option>
<option   value=2> Alpha </option>
<option   value=3> Numeric </option>
<option   value=4> Byte </option>
</select>
</td>


<td>
Columns:<br>
<input type=text value=10 name=columns size=4 maxlength=2>
</td>


</tr>

<tr>
<td>
Error level:<br>
<select name=errlevel>
<option selected value=0> 0 </option>
<option value=1> 1 </option>
<option value=2> 2 </option>
<option value=3> 3 </option>
<option value=4> 4 </option>
<option value=5> 5 </option>
<option value=6> 6 </option>
<option value=7> 7 </option>
<option value=8> 8 </option>
</select>
</td>

<td>
Module width:<br>
<select name=modwidth>
<option value=1> One </option>
<option value=2> Two </option>
<option value=3> Three </option>
<option value=4> Four </option>
</select>
</td>

</tr>

<tr>
<td>
Height factor:<br>
<select name=height>
<option value=1> 1 </option>
<option value=2> 2 </option>
<option selected value=3> 3 </option>
<option value=4> 4 </option>
<option value=5> 5 </option>
</td>

<td>
Scale:<br>
<input type=text name=scale value="1.0" size=4 maxlength=4>
</td>
</tr>

<tr>

<td>
Text:<br>
<input type=checkbox value=1 name=showtext>
</td>

<td>
Vertical:<br>
<input type=checkbox value=1 name=vertical>
</td>
</tr>

<tr>
<td>
Frame:<br>
<input type=checkbox value=1 name=showframe>
</td>

<td>
Truncated:<br>
<input type=checkbox value=1 name=truncated>
</td>

</tr>

<tr>

</tr>

<tr>
<td colspan=2 style="border-top:black solid 2pt;">
<br>Write to file:<br>
<input type=text name=file size=25 maxlength=80>
</td>

<tr>
<td>
PS module:<br>
<input type=text name=pswidth size=4 maxlength=4><br>
</td>
<td>
Format:<br>
<select name=backend>
<option selected value="IMAGEPNG"> PNG </option>
<option value="IMAGEJPG"> JPEG</option>
<option value="PS">Postscript</option>
<option value="EPS">EPS</option>
</select>
</td>
</tr>

<tr>
<td colspan=2   style="border-bottom:black solid 2pt;">
&nbsp;<br>
</td>
</tr>

</tr>
<tr>
<td>
<input type=checkbox value=1 name=info>
Debug info
</td>

<td valign=bottom align=right>
<input type=submit name=submit value="&nbsp; Create &nbsp;" style="font-weight:bold;">
</td>
</tr>

</table>
</form>
</div>





