<head>
<LINK REL=STYLESHEET TYPE="text/css" HREF="demoapp.css">
</head>

<h3>Datamatrix Demo</h3>

<div class="menu">
<form name="datamatrixspec" action="datamatrix_image.php" target=imageframe method post>
<table cellspacing=4 cellpadding=0>

<tr>
<td colspan=2>
Data:<br>
<textarea cols=30 rows=5 name=data>
Datamatrix 2D-Barcode
</textarea>
</td>
</tr>

<tr>
<td colspan=2>
<input type=checkbox value=1 name=tilde> Process tilde
</td>
</tr>


<tr>
<td>
Encoding:<br>
<select name=encoding>
<option   value=6> Automatic </option>
<option   value=1> Text</option>
<option   value=0> C40 </option>
<option   value=4> ASCII </option>
<option   value=5> BASE256 </option>
<option   value=2> X12 </option>
</select>
</td>

<td>
Module size:<br>
<select name=modwidth>
<option value=1> One  </option>
<option value=2> Two </option>
<option value=3 selected> Three </option>
<option value=4> Four </option>
<option value=5> Five </option>
</select>
</td>
</tr>

<tr>
<td>
Symbol size:<br>
<select name=symsize>
<option value=-1 >AUTO </option>
<option value=0 >10x10</option>
<option value=1 >12x12</option>
<option value=2 >14x14</option>
<option value=3 >16x16</option>
<option value=4 >18x18</option>
<option value=5 >20x20</option>
<option value=6 >22x22</option>
<option value=7 >24x24</option>
<option value=8 >26x26</option>
<option value=9 >32x32</option>
<option value=10 >36x36</option>
<option value=11 >40x40</option>
<option value=12 >44x44</option>
<option value=13 >48x48</option>
<option value=14 >52x52</option>
<option value=15 >64x64</option>
<option value=16 >72x72</option>
<option value=17 >80x80</option>
<option value=18 >88x88</option>
<option value=19 >96x96</option>
<option value=20 >104x104</option>
<option value=21 >120x120</option>
<option value=22 >132x132</option>
<option value=23 >144x144</option>
<option value=24 >8x18</option>
<option value=25 >8x32</option>
<option value=26 >12x26</option>
<option value=27 >12x36</option>
<option value=28 >16x36</option>
<option value=29 >16x48</option>
</select>
</td>
<td>
Quiet zone:<br>
<select name=quietzone>
<option value=0>0 px</option>
<option value=1>1 px</option>
<option value=5>5 px</option>
<option value=10>10 px</option>
<option value=15 selected>15 px</option>
<option value=20>20 px</option>
<option value=25>25 px</option>
<option value=30>30 px</option>
<option value=40>40 px</option>
<option value=50>50 px</option>
<option value=60>60 px</option>
</select>
</td>
</tr>

<tr>
<td>
Format:<br>
<select name=format>
<option value="image">Image</option>
<option value="ps">PS</option>
<option value="eps">EPS</option>
<option value="ascii">ASCII</option>
</select>
</td>
<td>
&nbsp;
</td>
</tr>


<tr>
<td colspan=2 align=right valign=bottom>
<br>
<input type=submit name=submit value="&nbsp; Create &nbsp;" >
</td>
</tr>
</table>
</form>
</div>


