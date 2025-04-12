<?php
set_time_limit(0);
ob_start();

$Successno			=0;
$ErrorInfo			=0;
$sroot 				= $_SERVER['DOCUMENT_ROOT'];


function PrintSPKOri($Nama_APP, $kode_produksi, $koneksi, $printby, $kode_product, $product_to, $id_delivery){
	
	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);
	
	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);
	
	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	// include $sroot. "/".$Nama_APP."/application/libraries/PHPMailer/PHPMailerAutoload.php";
	include $sroot."/".$Nama_APP."/application/libraries/MPDF57/mpdf.php";
	$mpdf=new mPDF('utf-8','A4');
	// $mpdf=new mPDF('utf-8','A4-L');
	
	set_time_limit(0);
	ini_set('memory_limit','1024M');

	//Beginning Buffer to save PHP variables and HTML tags
	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('l, d F Y [H:i:s]');
	
	
	// $qHeader2	= "SELECT * FROM production_header WHERE id_produksi='".$kode_produksi."' ";
	$qHeader2	= "	SELECT 
						a.*, 
						b.id_category,
						c.delivery_name
					FROM 
						production_header a 
						LEFT JOIN production_detail b ON a.id_produksi=b.id_produksi 
						LEFT JOIN delivery c ON b.id_delivery=c.id_delivery 						
					WHERE 
						a.id_produksi='".$kode_produksi."'
						AND b.id_delivery = '".$id_delivery."'
						LIMIT 1"; 
	// echo $qHeader2;
	$dResult2	= mysqli_query($conn, $qHeader2);
	$dHeader2	= mysqli_fetch_array($dResult2);
	
	$qHeader	= "SELECT a.*, b.nm_customer FROM product_header a INNER JOIN customer b ON b.id_customer=a.customer_real WHERE a.id_product='".$kode_product."' ";
	// echo $qHeader;
	$dResult	= mysqli_query($conn, $qHeader);
	$dHeader	= mysqli_fetch_array($dResult);
	
	?>
	
	<table class="gridtable" border='1' width='100%' cellpadding='2'>
		<tr>
			<td width='15%' rowspan='3'></td>
			<td align='center'><b>PT  ORI POLYTEC COMPOSITE</b></td>
			<td width='15%'>Nomor Dok.</td>
			<td width='15%'></td>
		</tr>
		<tr>
			<td align='center' rowspan='2'><b><h2>DAILY PRODUCTION REPORT</h2></b></td>
			<td>Rev.</td>
			<td></td>
		</tr>
		<tr>
			<td>Tgl Berlaku</td>
			<td></td>
		</tr>
	</table>
	<br>
	<table class="gridtable2" border='0' width='100%' >
		<tr>
			<td width='24%'>Tanggal</td>
			<td width='1%'>:</td>
			<td width='25%'><?= date('d F Y') ?></td>
			<td width='24%'>Cust/Project</td>
			<td width='1%'>:</td>
			<td width='25%'><?= $dHeader2['nm_customer']; ?></td>
		</tr>
		<tr>
			<td>No. SPK</td>
			<td>:</td>
			<td></td>
			<td>Spec Product</td>
			<td>:</td>
			<td><?= $dHeader['diameter']." x ".$dHeader['panjang']." x ".str_replace('.', ',', number_format($dHeader['design']));?></td> 
		</tr>
		<tr>
			<td>No. Mesin</td>
			<td>:</td>
			<td></td>
			<td><?= ucwords($dHeader2['id_category']);?> Ke</td>
			<td>:</td>
			<td><?= $product_to." (".ucfirst(strtolower($dHeader2['delivery_name'])).")";?></td>
		</tr>
	</table>
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th width='13%'>Material</th>
				<th width='7%'>Jumlah Layer</th>
				<th width='27%'>Tipe Material</th>
				<th width='10%'>Qty</th>
				<th width='15%'>Lot/Batch Num</th>
				<th width='10%'>Actual Type</th>
				<th width='8%'>Layer</th>
				<th width='8%'>Terpakai</th>
			</tr>
			<tr>
				<th align='left' colspan='8'>LINER THIKNESS / CB</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost  FROM product_detail WHERE id_product='".$kode_product."' AND detail_name='LINER THIKNESS / CB' AND id_material <> 'MTL-1903000' AND id_category <> 'TYP-0001' ";
		$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
		
		while($valx = mysqli_fetch_array($dDetailLiner)){
			$dataL	= ($valx['layer'] == 0.00)?'-':$valx['layer'];
			?>
			<tr>
				<td><?= $valx['nm_category'];?></td>
				<td align='center'><?= $dataL;?></td>
				<td><?= $valx['nm_material'];?></td>
				<td align='right'><?= $valx['last_cost'];?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM product_detail WHERE id_product='".$kode_product."' AND detail_name='LINER THIKNESS / CB' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
		$dDetailResin	= mysqli_query($conn, $detailResin);
		// echo $detailResin;
		while($valH = mysqli_fetch_array($dDetailResin)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= $valH['last_cost'];?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM product_detail_plus WHERE id_product='".$kode_product."' AND detail_name='LINER THIKNESS / CB' AND id_material <> 'MTL-1903000' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= $valH['last_cost'];?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailAdd	= "SELECT nm_category, nm_material, last_cost  FROM product_detail_add WHERE id_product='".$kode_product."' AND detail_name='LINER THIKNESS / CB' ";
		$dDetailAdd	= mysqli_query($conn, $detailAdd);
		$NUmRow		= mysqli_num_rows($dDetailAdd);
		// echo $NUmRow;
		if($NUmRow > 0){
			echo "<tr>";
				echo "<th align='left' colspan='8'>Add Materials</th>";
			echo "</tr>";
			while($valD = mysqli_fetch_array($dDetailAdd)){
			?>
			<tr>
				<td colspan='2'><?= $valD['nm_category'];?></td>
				<td><?= $valD['nm_material'];?></td>
				<td align='right'><?= $valD['last_cost'];?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
			}
		}
		?>
		
		</tbody>
	
		<thead align='center'>
			<tr>
				<th align='left' colspan='8'>STRUKTUR THICKNESS</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost  FROM product_detail WHERE id_product='".$kode_product."' AND detail_name='STRUKTUR THICKNESS' AND id_material <> 'MTL-1903000' AND id_category <>'TYP-0001' ";
		// echo $tDetailLiner;
		$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
		while($valx = mysqli_fetch_array($dDetailLiner)){
			$dataL	= ($valx['layer'] == 0.00)?'-':$valx['layer'];
			?>
			<tr>
				<td><?= $valx['nm_category'];?></td>
				<td align='center'><?= $dataL;?></td>
				<td><?= $valx['nm_material'];?></td>
				<td align='right'><?= $valx['last_cost'];?> Kg</td>
				<td></td>
				<td></td>
				<td></td> 
				<td></td>
			</tr>
			<?php
		}
		
		$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM product_detail WHERE id_product='".$kode_product."' AND detail_name='STRUKTUR THICKNESS' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
		$dDetailResin	= mysqli_query($conn, $detailResin);
		// echo $detailResin;
		while($valH = mysqli_fetch_array($dDetailResin)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= $valH['last_cost'];?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM product_detail_plus WHERE id_product='".$kode_product."' AND detail_name='STRUKTUR THICKNESS' AND id_material <> 'MTL-1903000' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= $valH['last_cost'];?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailAdd	= "SELECT nm_category, nm_material, last_cost  FROM product_detail_add WHERE id_product='".$kode_product."' AND detail_name='STRUKTUR THICKNESS' ";
		$dDetailAdd	= mysqli_query($conn, $detailAdd);
		$NUmRow		= mysqli_num_rows($dDetailAdd);
		// echo $NUmRow;
		if($NUmRow > 0){
			echo "<tr>";
				echo "<th align='left' colspan='8'>Add Materials</th>";
			echo "</tr>";
			
			while($valD = mysqli_fetch_array($dDetailAdd)){
			?>
			<tr>
				<td colspan='2'><?= $valD['nm_category'];?></td>
				<td><?= $valD['nm_material'];?></td>
				<td align='right'><?= $valD['last_cost'];?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
			}
		}
		?>
		</tbody>
	
		
		<?php
		$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost  FROM product_detail WHERE id_product='".$kode_product."' AND detail_name='EXTERNAL LAYER THICKNESS' AND id_material <> 'MTL-1903000' AND id_category <>'TYP-0001' ";
		// echo $tDetailLiner;
		$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
		$numRows		= mysqli_num_rows($dDetailLiner);
		
		if($numRows > 0){
			?>
				<thead align='center'>
					<tr>
						<th align='left' colspan='8'>EXTERNAL LAYER THICKNESS</th>
					</tr>
				</thead>
				<tbody>
			<?php
		}
		
		while($valx = mysqli_fetch_array($dDetailLiner)){
			$dataL	= ($valx['layer'] == 0.00)?'-':$valx['layer'];
			?>
			<tr>
				<td><?= $valx['nm_category'];?></td>
				<td align='center'><?= $dataL;?></td>
				<td><?= $valx['nm_material'];?></td>
				<td align='right'><?= $valx['last_cost'];?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM product_detail WHERE id_product='".$kode_product."' AND detail_name='EXTERNAL LAYER THICKNESS' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
		$dDetailResin	= mysqli_query($conn, $detailResin);
		// echo $detailResin;
		while($valH = mysqli_fetch_array($dDetailResin)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= $valH['last_cost'];?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM product_detail_plus WHERE id_product='".$kode_product."' AND detail_name='EXTERNAL LAYER THICKNESS' AND id_material <> 'MTL-1903000' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= $valH['last_cost'];?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailAdd	= "SELECT nm_category, nm_material, last_cost  FROM product_detail_add WHERE id_product='".$kode_product."' AND detail_name='EXTERNAL LAYER THICKNESS' ";
		$dDetailAdd	= mysqli_query($conn, $detailAdd);
		$NUmRow		= mysqli_num_rows($dDetailAdd);
		// echo $NUmRow;
		if($NUmRow > 0){
			echo "<tr>";
				echo "<th align='left' colspan='8'>Add Materials</th>";
			echo "</tr>";
			
			while($valD = mysqli_fetch_array($dDetailAdd)){
			?>
			<tr>
				<td colspan='2'><?= $valD['nm_category'];?></td>
				<td><?= $valD['nm_material'];?></td>
				<td align='right'><?= $valD['last_cost'];?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
			}
		}
		?>
		</tbody>
	</table>
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='6'>TOPCOAT</th>
			</tr>
			<tr>
				<th width='20%'>Material</th>
				<th width='27%'>Tipe Material</th>
				<th width='10%'>Qty</th>
				<th width='15%'>Lot/Batch Num</th>
				<th width='18%'>Actual Type</th>
				<th width='8%'>Terpakai</th>
			</tr>
		</thead>
		<tbody>
		<?php
		
		$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM product_detail_plus WHERE id_product='".$kode_product."' AND detail_name='TOPCOAT' AND id_material <> 'MTL-1903000' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= $valH['last_cost'];?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailAdd	= "SELECT nm_category, nm_material, last_cost  FROM product_detail_add WHERE id_product='".$kode_product."' AND detail_name='TOPCOAT' ";
		$dDetailAdd	= mysqli_query($conn, $detailAdd);
		$NUmRow		= mysqli_num_rows($dDetailAdd);
		// echo $NUmRow;
		if($NUmRow > 0){
			echo "<tr>";
				echo "<th align='left' colspan='6'>Add Materials</th>";
			echo "</tr>";
			
			while($valD = mysqli_fetch_array($dDetailAdd)){
			?>
			<tr>
				<td><?= $valD['nm_category'];?></td>
				<td><?= $valD['nm_material'];?></td>
				<td align='right'><?= $valD['last_cost'];?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
			}
		}
		?>
		</tbody>
	</table>
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
			<tr>
				<th align='left' colspan='7'>NOTE</th>
			</tr>
			<tr>
				<td height='75px' colspan='7'></td> 
			</tr>
	</table>
	<div id='space'></div>

	<p class='foot1'> <?php echo "<i>Printed by : ".ucwords(strtolower($printby)).", ".$today." / ".$kode_produksi." / ".$kode_product."</i>"; ?> </p>
	<br>
	<!--
	<table class="gridtable2" width='100%'  cellpadding='2'>
		<tr>
			<td height='30px' align='c'>Diperiksa</td>
			<td align='center'></td>
			<td align='center'></td>
		</tr>
		<tr>
			<td height='30px' align='left'></td>
			<td align='center'></td>
			<td align='center'></td>
		</tr>
		<tr>
			<td height='30px' align='left'></td>
			<td align='center'></td>
			<td align='center'></td>
		</tr>
		<tr>
			<td height='30px' align='left'>--Name---</td>
			<td align='center'></td>
			<td align='center'></td>
		</tr>
		<tr>
			<td height='30px' align='left'>Supervisor</td>
			<td align='center'></td>
			<td align='center'></td>
		</tr>
	</table>
	-->
	<style type="text/css">
	@page {
		margin-top: 1cm;
		margin-left: 1.5cm;
		margin-right: 1cm;
		margin-bottom: 1cm;
	}
	p.foot1 {
		font-family: verdana,arial,sans-serif;
		font-size:10px;
	}
	.font{
		font-family: verdana,arial,sans-serif;
		font-size:14px;
	}
	.fontheader{
		font-family: verdana,arial,sans-serif;
		font-size:13px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable th {
		border-width: 1px;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable th.head {
		border-width: 1px;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable td {
		border-width: 1px;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable td.cols {
		border-width: 1px;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	
	table.gridtable2 {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable2 th {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable2 th.head {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable2 td {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable2 td.cols {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	table.cooltabs {
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
	}
	table.cooltabs th.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
	}
	table.cooltabs td.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		padding: 5px;
	}
	#cooltabs {
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 800px;
		height: 20px;
	}
	#cooltabs2{
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 180px;
		height: 10px;
	}
	#space{
		padding: 3px;
		width: 180px;
		height: 1px;
	}
	#cooltabshead{
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 0 0;
		background: #dfdfdf;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	#cooltabschild{
		font-size:10px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 0 0 5px 5px;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	p {
		margin: 0 0 0 0;
	}
	p.pos_fixed {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 30px;
		left: 230px;
	}
	p.pos_fixed2 {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 589px;
		left: 230px;
	}
	.barcode {
		padding: 1.5mm;
		margin: 0;
		vertical-align: top;
		color: #000044;
	}
	.barcodecell {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: -10px;
		right: 10px;
	}
	.barcodecell2 {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: 548px;
		right: 10px;
	}
	p.barcs {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 38px;
		right: 115px;
	}
	p.barcs2 {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 591px;
		right: 115px;
	}
	p.pt {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 62px;
		left: 5px;
	}
	p.alamat {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 71px;
		left: 5px;
	}
	p.tlp {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 80px;
		left: 5px;
	}
	p.pt2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 596px;
		left: 5px;
	}
	p.alamat2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 605px;
		left: 5px;
	}
	p.tlp2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 614px;
		left: 5px;
	}
	#hrnew {
		border: 0;
		border-bottom: 1px dashed #ccc;
		background: #999;
	}
</style>

	
	<?php
	
	$html = ob_get_contents(); 
	// $footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px;'><i>Printed by : ".ucfirst(strtolower($printby)).", ".$today."</i></p>";
	// exit;
	ob_end_clean(); 
	// $mpdf->SetWatermarkText('ORI Group');
	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('SPK Of Production');
	// $mpdf->setHTMLFooter($footer);
	$mpdf->WriteHTML($html);		
	$mpdf->Output($kode_produksi.'_'.strtolower($dHeader['nm_product']).'_product_ke_'.$product_to.'.pdf' ,'I');

	//exit;
	//return $attachment;
}

function PrintSPKRealOri($Nama_APP, $kode_produksi, $koneksi, $printby, $kode_product, $product_to, $id_production_detail, $id_delivery, $id_milik){
	
	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);
	
	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);
	
	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	// include $sroot. "/".$Nama_APP."/application/libraries/PHPMailer/PHPMailerAutoload.php";
	include $sroot."/".$Nama_APP."/application/libraries/MPDF57/mpdf.php";
	$mpdf=new mPDF('utf-8','A4');
	// $mpdf=new mPDF('utf-8','A4-L');
	
	set_time_limit(0);
	ini_set('memory_limit','1024M');

	//Beginning Buffer to save PHP variables and HTML tags
	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('l, d F Y [H:i:s]');
	
	
	// $qHeader2	= "SELECT * FROM production_header WHERE id_produksi='".$kode_produksi."' ";
	$qHeader2	= "SELECT 
						a.*, 
						b.id_category
					FROM 
						production_header a 
						LEFT JOIN production_detail b ON a.id_produksi=b.id_produksi
					WHERE 
						a.id_produksi='".$kode_produksi."' 
						AND b.id_delivery = '".$id_delivery."'
						LIMIT 1"; 
	// echo $qHeader2;
	$dResult2	= mysqli_query($conn, $qHeader2);
	$dHeader2	= mysqli_fetch_array($dResult2);
	
	$qHeader	= "SELECT a.*, b.* FROM bq_component_header a INNER JOIN bq_detail_header b ON a.id_milik=b.id 
					WHERE a.id_product='".$kode_product."' AND a.id_milik='".$id_milik."' ";
	// echo $qHeader;
	$dResult	= mysqli_query($conn, $qHeader);
	$dHeader	= mysqli_fetch_array($dResult);
	
	$qIPP	= "SELECT a.* FROM production a WHERE a.no_ipp='".$dHeader2['no_ipp']."' ";
	// echo $qIPP;
	$dIPP	= mysqli_query($conn, $qIPP);
	$dRIPP	= mysqli_fetch_array($dIPP);
	
	if($dHeader['id_category'] == 'pipe'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['length'])." x ".floatval($dHeader['thickness']);
	}
	elseif($dHeader['id_category'] == 'elbow mitter' OR $dHeader['id_category'] == 'elbow mould'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['thickness']).", ".$dHeader['type']." ".$dHeader['sudut'];
	}
	elseif($dHeader['id_category'] == 'concentric reducer' OR $dHeader['id_category'] == 'reducer tee mould' OR $dHeader['id_category'] == 'eccentric reducer' OR $dHeader['id_category'] == 'reducer tee slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['diameter_2'])." x ".floatval($dHeader['thickness']);
	}
	elseif($dHeader['id_category'] == 'end cap' OR $dHeader['id_category'] == 'flange slongsong' OR $dHeader['id_category'] == 'flange mould' OR $dHeader['id_category'] == 'equal tee mould' OR $dHeader['id_category'] == 'blind flange' OR $dHeader['id_category'] == 'equal tee slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['thickness']);
	}
	else{$dim = "belum di set";} 
	
	?>
	
	<table class="gridtable" border='1' width='100%' cellpadding='2'>
		<tr>
			<td width='70px' rowspan='3' style='padding:0px;'><img src='<?=$sroot;?><?=$Nama_APP;?>/assets/images/ori_logo.jpg' alt="" height='80' width='70' ></td>
			<td align='center'><b>PT  ORI POLYTEC COMPOSITE</b></td>
			<td width='15%'>Doc Number</td>
			<td width='15%'><?= $dHeader2['no_ipp']; ?></td>
		</tr>
		<tr>
			<td align='center' rowspan='2'><b><h2>PRODUCTION REAL REPORT</h2></b></td>
			<td>Rev</td>
			<td></td>
		</tr>
		<tr>
			<td>Due Date</td>
			<td></td>
		</tr>
	</table>
	<table class="gridtable2" border='0' width='100%' >
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width='20%'>Production Date</td>
			<td width='1%'>:</td>
			<td width='29%'></td>
			<td width='20%'>SO Number</td>
			<td width='1%'>:</td>
			<td width='29%'><?= $dHeader2['so_number']; ?></td>
		</tr>
		<tr>
			<td>SPK Number</td>
			<td>:</td>
			<td><?= $dHeader['no_spk'];?></td>
			<td>Customer</td>
			<td>:</td>
			<td><?= $dRIPP['nm_customer']; ?></td>
		</tr>
		<tr>
			<td>Machine Number</td>
			<td>:</td>
			<td><?= strtoupper($dHeader2['nm_mesin']);?></td>
			<td>Spec Product</td>
			<td>:</td>
			<td><?= $dim;?></td>
		</tr>
		<tr>
			<td>Project</td>
			<td>:</td>
			<td><?= strtoupper($dRIPP['project']); ?></td>
			<td><?= ucwords($dHeader['parent_product']);?> To</td>
			<td>:</td>
			<td><?= $product_to." (".strtoupper(strtolower($dHeader['no_komponen'])).") of ".$dHeader['qty']." Component";?></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th width='13%'>Material</th>
				<th width='7%'>Layer</th>
				<th width='27%'>Tipe Material</th>
				<th width='10%'>Qty</th>
				<th width='15%'>Lot/Batch Num</th>
				<th width='10%'>Actual</th>
				<th width='8%'>Layer</th>
				<th width='8%'>Terpakai</th>
			</tr>
			<tr>
				<th align='left' colspan='8'>LINER THIKNESS / CB</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$tDetailLiner	= "	SELECT 
								a.nm_category, 
								a.layer, 
								a.nm_material, 
								a.last_cost,
								b.batch_number,
								b.actual_type,
								b.layer as layer_real,
								b.material_terpakai								
							FROM 
								bq_component_detail a 
								INNER JOIN production_real_detail b ON a.id_detail = b.id_detail 
							WHERE 
								a.id_product='".$kode_product."' 
								AND a.id_milik = '".$id_milik."'
								AND a.detail_name='LINER THIKNESS / CB' 
								AND a.id_material <> 'MTL-1903000' 
								AND b.id_production_detail = '".$id_production_detail."'
								AND a.id_category <> 'TYP-0001' ";
		$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
		// echo $tDetailLiner; exit;
		while($valx = mysqli_fetch_array($dDetailLiner)){
			$dataL	= ($valx['layer'] == 0.00)?'-':($valx['layer'] == 0)?'-':floatval($valx['layer']);
			?>
			<tr>
				<td><?= $valx['nm_category'];?></td>
				<td align='center'><?= $dataL;?></td>
				<td><?= $valx['nm_material'];?></td>
				<td align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
				<td><?= $valx['batch_number'];?></td>
				<td><?= $valx['actual_type'];?></td>
				<td align='center'><?= $valx['layer_real'];?></td>
				<td align='right'><?= number_format($valx['material_terpakai'], 3);?> Kg</td>
			</tr>
			<?php
		} 
		
		$detailResin	= "
							SELECT 
								a.nm_category, 
								a.nm_material, 
								a.last_cost,
								b.batch_number,
								b.actual_type,
								b.layer as layer_real,
								b.material_terpakai	
							FROM 
								bq_component_detail a 
								INNER JOIN production_real_detail b ON a.id_detail = b.id_detail
							WHERE 
								a.id_product='".$kode_product."' 
								AND a.detail_name='LINER THIKNESS / CB' 
								AND b.id_production_detail = '".$id_production_detail."'
								AND a.id_category ='TYP-0001' 
							ORDER BY 
								a.id_detail DESC LIMIT 1 ";
		$dDetailResin	= mysqli_query($conn, $detailResin);
		// echo $detailResin;
		while($valH = mysqli_fetch_array($dDetailResin)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td><?= $valH['batch_number'];?></td>
				<td colspan='2'><?= $valH['actual_type'];?></td>
				<td align='right'><?= number_format($valH['material_terpakai'], 3);?> Kg</td>
			</tr>
			<?php
		}
		
		$detailPlus	= "
						SELECT 
							a.nm_category, 
							a.nm_material, 
							a.last_cost,
							b.batch_number,
							b.actual_type,
							b.material_terpakai	
						FROM 
							bq_component_detail_plus a 
							INNER JOIN production_real_detail_plus b ON a.id_detail = b.id_detail
						WHERE 
							a.id_product='".$kode_product."' 
							AND a.id_milik = '".$id_milik."'
							AND a.detail_name='LINER THIKNESS / CB' 
							AND b.id_production_detail = '".$id_production_detail."'
							AND a.id_material <> 'MTL-1903000' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td><?= $valH['batch_number'];?></td>
				<td colspan='2'><?= $valH['actual_type'];?></td>
				<td align='right'><?= number_format($valH['material_terpakai'], 3);?> Kg</td>
			</tr>
			<?php
		}
		
		$detailAdd	= "	SELECT 
							a.nm_category, 
							a.nm_material, 
							a.last_cost,
							b.batch_number,
							b.actual_type,
							b.material_terpakai
						FROM 
							bq_component_detail_add a 
							INNER JOIN production_real_detail_add b ON a.id_detail = b.id_detail
						WHERE 
							a.id_product='".$kode_product."' 
							AND a.id_milik = '".$id_milik."'
							AND b.id_production_detail = '".$id_production_detail."'
							AND a.detail_name='LINER THIKNESS / CB' ";
		$dDetailAdd	= mysqli_query($conn, $detailAdd);
		$NUmRow		= mysqli_num_rows($dDetailAdd);
		// echo $NUmRow;
		if($NUmRow > 0){
			echo "<tr>";
				echo "<th align='left' colspan='8'>Add Materials</th>";
			echo "</tr>";
			while($valD = mysqli_fetch_array($dDetailAdd)){
			?>
			<tr>
				<td colspan='2'><?= $valD['nm_category'];?></td>
				<td><?= $valD['nm_material'];?></td>
				<td align='right'><?= number_format($valD['last_cost'], 3);?> Kg</td>
				<td><?= $valD['batch_number'];?></td>
				<td colspan='2'><?= $valD['actual_type'];?></td>
				<td align='right'><?= number_format($valD['material_terpakai'], 3);?> Kg</td>
			</tr>
			<?php
			}
		}
		?>
		
		</tbody>
		</table>
		<!-- STRUCTURE NECK ================================================================================ -->
		
		<?php
		$tDetailLinerN1	= "
								SELECT 
									a.id_category,
									a.nm_category, 
									a.layer, 
									a.nm_material, 
									a.last_cost,
									a.jumlah,
									b.batch_number,
									b.actual_type,
									b.layer as layer_real,
									b.material_terpakai,
									b.benang
								FROM 
									bq_component_detail a 
									INNER JOIN production_real_detail b ON a.id_detail = b.id_detail
								WHERE 
									a.id_product='".$kode_product."' 
									AND a.id_milik = '".$id_milik."'
									AND a.detail_name='STRUKTUR NECK 1' 
									AND b.id_production_detail = '".$id_production_detail."'
									AND a.id_material <> 'MTL-1903000' 
									AND a.id_category <>'TYP-0001' ";
		$dDetailLinerN1	= mysqli_query($conn, $tDetailLinerN1);							
		$dDetailLinerN1Num	= mysqli_num_rows($dDetailLinerN1);
		if($dDetailLinerN1Num > 0){
		?>
			<table class="gridtable" width='100%' border='1' cellpadding='2'>
			<thead align='center'>
				<tr>
					<th align='left' colspan='8'>STRUKTUR NECK 1</th>
				</tr>
			</thead>
			<tbody>
			<?php
			// echo $tDetailLiner; exit;
			
			while($valx = mysqli_fetch_array($dDetailLinerN1)){
				$dataL	= ($valx['layer'] == 0.00)?'-':floatval($valx['layer']);
				$benang = "";
				$benangR = "";
					if($valx['id_category'] == 'TYP-0005'){
						$benang = " | ".floatval($valx['jumlah']);
						$benangR = " | ".floatval($valx['benang']);
					}
				?>
				<tr>
					<td width='13%'><?= $valx['nm_category'];?></td>
					<td width='7%' align='center'><?= $dataL.$benang;?></td>
					<td width='27%'><?= $valx['nm_material'];?></td>
					<td width='10%' align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
					<td width='15%'><?= $valx['batch_number'];?></td>
					<td width='10%'><?= $valx['actual_type'];?></td>
					<td width='8%' align='center'><?= $valx['layer_real'].$benang;?></td>
					<td width='8%' align='right'><?= number_format($valx['material_terpakai'], 3);?> Kg</td>
				</tr>
				<?php
			}
			
			$detailResinN1	= "	SELECT 
									a.nm_category, 
									a.nm_material, 
									a.last_cost,
									b.batch_number,
									b.actual_type,
									b.layer as layer_real,
									b.material_terpakai	
								FROM 
									bq_component_detail a 
									INNER JOIN production_real_detail b ON a.id_detail = b.id_detail
								WHERE 
									a.id_product='".$kode_product."' 
									AND a.id_milik = '".$id_milik."'
									AND a.detail_name='STRUKTUR NECK 1' 
									AND b.id_production_detail = '".$id_production_detail."'
									AND a.id_category ='TYP-0001' 
								ORDER BY 
									a.id_detail DESC LIMIT 1 ";
			$dDetailResinN1	= mysqli_query($conn, $detailResinN1);
			// echo $detailResin;
			while($valH = mysqli_fetch_array($dDetailResinN1)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td><?= $valH['batch_number'];?></td>
					<td colspan='2'><?= $valH['actual_type'];?></td>
					<td align='right'><?= number_format($valH['material_terpakai'],3);?> Kg</td>
				</tr>
				<?php
			}
			
			$detailPlusN1	= "SELECT 
								a.nm_category, 
								a.nm_material, 
								a.last_cost,
								b.batch_number,
								b.actual_type,
								b.material_terpakai
							FROM 
								bq_component_detail_plus a 
								INNER JOIN production_real_detail_plus b ON a.id_detail = b.id_detail
							WHERE 
								a.id_product='".$kode_product."' 
								AND a.id_milik = '".$id_milik."'
								AND a.detail_name='STRUKTUR NECK 1' 
								AND b.id_production_detail = '".$id_production_detail."'
								AND a.id_material <> 'MTL-1903000' ";
			$dDetailPlusN1	= mysqli_query($conn, $detailPlusN1);
			// echo $detailPlus;
			while($valH = mysqli_fetch_array($dDetailPlusN1)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'],3);?> Kg</td>
					<td><?= $valH['batch_number'];?></td>
					<td colspan='2'><?= $valH['actual_type'];?></td>
					<td align='right'><?= number_format($valH['material_terpakai'],3);?> Kg</td>
				</tr>
				<?php
			}
			
			$detailAddN1	= "SELECT 
								a.nm_category, 
								a.nm_material, 
								a.last_cost,
								b.batch_number,
								b.actual_type,
								b.material_terpakai
							FROM 
								bq_component_detail_add a 
								INNER JOIN production_real_detail_add b ON a.id_detail = b.id_detail
							WHERE 
								a.id_product='".$kode_product."' 
								AND a.id_milik = '".$id_milik."'
								AND b.id_production_detail = '".$id_production_detail."'
								AND a.detail_name='STRUKTUR NECK 1' ";
			$dDetailAddN1	= mysqli_query($conn, $detailAddN1);
			$NUmRowN1		= mysqli_num_rows($dDetailAddN1);
			// echo $NUmRow;
			if($NUmRowN1 > 0){
				echo "<tr>";
					echo "<th align='left' colspan='8'>Add Materials</th>";
				echo "</tr>";
				
				while($valD = mysqli_fetch_array($dDetailAddN1)){
				?>
				<tr>
					<td colspan='2'><?= $valD['nm_category'];?></td>
					<td><?= $valD['nm_material'];?></td>
					<td align='right'><?= number_format($valD['last_cost'],3);?> Kg</td>
					<td><?= $valD['batch_number'];?></td>
					<td colspan='2'><?= $valD['actual_type'];?></td>
					<td align='right'><?= number_format($valD['material_terpakai'],3);?> Kg</td>
				</tr>
				<?php
				}
			}
			?>
			</tbody>
			</table>
			
			
			
			<table class="gridtable" width='100%' border='1' cellpadding='2'>
			<thead align='center'>
				<tr>
					<th align='left' colspan='8'>STRUKTUR NECK 2</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$tDetailLinerN2	= "
								SELECT 
									a.id_category,
									a.nm_category, 
									a.layer, 
									a.nm_material, 
									a.last_cost,
									a.jumlah,
									b.batch_number,
									b.actual_type,
									b.layer as layer_real,
									b.material_terpakai,
									b.benang
								FROM 
									bq_component_detail a 
									INNER JOIN production_real_detail b ON a.id_detail = b.id_detail
								WHERE 
									a.id_product='".$kode_product."' 
									AND a.id_milik = '".$id_milik."'
									AND a.detail_name='STRUKTUR NECK 2' 
									AND b.id_production_detail = '".$id_production_detail."'
									AND a.id_material <> 'MTL-1903000' 
									AND a.id_category <>'TYP-0001' ";							
			$dDetailLinerN2	= mysqli_query($conn, $tDetailLinerN2);
			
			while($valx = mysqli_fetch_array($dDetailLinerN2)){
				$dataL	= ($valx['layer'] == 0.00)?'-':floatval($valx['layer']);
				$benang = "";
				$benangR = "";
					if($valx['id_category'] == 'TYP-0005'){
						$benang = " | ".floatval($valx['jumlah']);
						$benangR = " | ".floatval($valx['benang']);
					}
				?>
				<tr>
					<td width='13%'><?= $valx['nm_category'];?></td>
					<td width='7%' align='center'><?= $dataL.$benang;?></td>
					<td width='27%'><?= $valx['nm_material'];?></td>
					<td width='10%' align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
					<td width='15%'><?= $valx['batch_number'];?></td>
					<td width='10%'><?= $valx['actual_type'];?></td>
					<td width='8%' align='center'><?= $valx['layer_real'].$benang;?></td>
					<td width='8%' align='right'><?= number_format($valx['material_terpakai'], 3);?> Kg</td>
				</tr>
				<?php
			}
			
			$detailResinN2	= "	SELECT 
									a.nm_category, 
									a.nm_material, 
									a.last_cost,
									b.batch_number,
									b.actual_type,
									b.layer as layer_real,
									b.material_terpakai	
								FROM 
									bq_component_detail a 
									INNER JOIN production_real_detail b ON a.id_detail = b.id_detail
								WHERE 
									a.id_product='".$kode_product."' 
									AND a.id_milik = '".$id_milik."'
									AND a.detail_name='STRUKTUR NECK 2' 
									AND b.id_production_detail = '".$id_production_detail."'
									AND a.id_category ='TYP-0001' 
								ORDER BY 
									a.id_detail DESC LIMIT 1 ";
			$dDetailResinN2	= mysqli_query($conn, $detailResinN2);
			// echo $detailResin;
			while($valH = mysqli_fetch_array($dDetailResinN2)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td><?= $valH['batch_number'];?></td>
					<td colspan='2'><?= $valH['actual_type'];?></td>
					<td align='right'><?= number_format($valH['material_terpakai'],3);?> Kg</td>
				</tr>
				<?php
			}
			
			$detailPlusN2	= "SELECT 
								a.nm_category, 
								a.nm_material, 
								a.last_cost,
								b.batch_number,
								b.actual_type,
								b.material_terpakai
							FROM 
								bq_component_detail_plus a 
								INNER JOIN production_real_detail_plus b ON a.id_detail = b.id_detail
							WHERE 
								a.id_product='".$kode_product."' 
								AND a.id_milik = '".$id_milik."'
								AND a.detail_name='STRUKTUR NECK 2' 
								AND b.id_production_detail = '".$id_production_detail."'
								AND a.id_material <> 'MTL-1903000' ";
			$dDetailPlusN2	= mysqli_query($conn, $detailPlusN2);
			// echo $detailPlus;
			while($valH = mysqli_fetch_array($dDetailPlusN2)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'],3);?> Kg</td>
					<td><?= $valH['batch_number'];?></td>
					<td colspan='2'><?= $valH['actual_type'];?></td>
					<td align='right'><?= number_format($valH['material_terpakai'],3);?> Kg</td>
				</tr>
				<?php
			}
			
			$detailAddN2	= "SELECT 
								a.nm_category, 
								a.nm_material, 
								a.last_cost,
								b.batch_number,
								b.actual_type,
								b.material_terpakai
							FROM 
								bq_component_detail_add a 
								INNER JOIN production_real_detail_add b ON a.id_detail = b.id_detail
							WHERE 
								a.id_product='".$kode_product."' 
								AND a.id_milik = '".$id_milik."'
								AND b.id_production_detail = '".$id_production_detail."'
								AND a.detail_name='STRUKTUR NECK 2' ";
			$dDetailAddN2	= mysqli_query($conn, $detailAddN2);
			$NUmRowN2		= mysqli_num_rows($dDetailAddN2);
			// echo $NUmRow;
			if($NUmRowN2 > 0){
				echo "<tr>";
					echo "<th align='left' colspan='8'>Add Materials</th>";
				echo "</tr>";
				
				while($valD = mysqli_fetch_array($dDetailAddN2)){
				?>
				<tr>
					<td colspan='2'><?= $valD['nm_category'];?></td>
					<td><?= $valD['nm_material'];?></td>
					<td align='right'><?= number_format($valD['last_cost'],3);?> Kg</td>
					<td><?= $valD['batch_number'];?></td>
					<td colspan='2'><?= $valD['actual_type'];?></td>
					<td align='right'><?= number_format($valD['material_terpakai'],3);?> Kg</td>
				</tr>
				<?php
				}
			}
			?>
			</tbody>
			</table>
		<?php
		}
		?>
		
		<!-- ===============================================================================================-->
		<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='8'>STRUKTUR THICKNESS</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$tDetailLiner	= "
							SELECT 
								a.id_category,
								a.nm_category, 
								a.layer, 
								a.nm_material, 
								a.last_cost,
								a.jumlah,
								b.batch_number,
								b.actual_type,
								b.layer as layer_real,
								b.material_terpakai,
								b.benang
							FROM 
								bq_component_detail a 
								INNER JOIN production_real_detail b ON a.id_detail = b.id_detail
							WHERE 
								a.id_product='".$kode_product."' 
								AND a.id_milik = '".$id_milik."'
								AND a.detail_name='STRUKTUR THICKNESS' 
								AND b.id_production_detail = '".$id_production_detail."'
								AND a.id_material <> 'MTL-1903000' 
								AND a.id_category <>'TYP-0001' ";
		// echo $tDetailLiner; exit;
		$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
		while($valx = mysqli_fetch_array($dDetailLiner)){
			$dataL	= ($valx['layer'] == 0.00)?'-':floatval($valx['layer']);
			$benang = "";
			$benangR = "";
				if($valx['id_category'] == 'TYP-0005'){
					$benang = " | ".floatval($valx['jumlah']);
					$benangR = " | ".floatval($valx['benang']);
				}
			?>
			<tr>
				<td width='13%'><?= $valx['nm_category'];?></td>
				<td width='7%' align='center'><?= $dataL.$benang;?></td>
				<td width='27%'><?= $valx['nm_material'];?></td>
				<td width='10%' align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
				<td width='15%'><?= $valx['batch_number'];?></td>
				<td width='10%'><?= $valx['actual_type'];?></td>
				<td width='8%' align='center'><?= $valx['layer_real'].$benang;?></td>
				<td width='8%' align='right'><?= number_format($valx['material_terpakai'], 3);?> Kg</td>
			</tr>
			<?php
		}
		
		$detailResin	= "	SELECT 
								a.nm_category, 
								a.nm_material, 
								a.last_cost,
								b.batch_number,
								b.actual_type,
								b.layer as layer_real,
								b.material_terpakai	
							FROM 
								bq_component_detail a 
								INNER JOIN production_real_detail b ON a.id_detail = b.id_detail
							WHERE 
								a.id_product='".$kode_product."' 
								AND a.id_milik = '".$id_milik."'
								AND a.detail_name='STRUKTUR THICKNESS' 
								AND b.id_production_detail = '".$id_production_detail."'
								AND a.id_category ='TYP-0001' 
							ORDER BY 
								a.id_detail DESC LIMIT 1 ";
		$dDetailResin	= mysqli_query($conn, $detailResin);
		// echo $detailResin;
		while($valH = mysqli_fetch_array($dDetailResin)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td><?= $valH['batch_number'];?></td>
				<td colspan='2'><?= $valH['actual_type'];?></td>
				<td align='right'><?= number_format($valH['material_terpakai'],3);?> Kg</td>
			</tr>
			<?php
		}
		
		$detailPlus	= "SELECT 
							a.nm_category, 
							a.nm_material, 
							a.last_cost,
							b.batch_number,
							b.actual_type,
							b.material_terpakai
						FROM 
							bq_component_detail_plus a 
							INNER JOIN production_real_detail_plus b ON a.id_detail = b.id_detail
						WHERE 
							a.id_product='".$kode_product."' 
							AND a.id_milik = '".$id_milik."'
							AND a.detail_name='STRUKTUR THICKNESS' 
							AND b.id_production_detail = '".$id_production_detail."'
							AND a.id_material <> 'MTL-1903000' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'],3);?> Kg</td>
				<td><?= $valH['batch_number'];?></td>
				<td colspan='2'><?= $valH['actual_type'];?></td>
				<td align='right'><?= number_format($valH['material_terpakai'],3);?> Kg</td>
			</tr>
			<?php
		}
		
		$detailAdd	= "SELECT 
							a.nm_category, 
							a.nm_material, 
							a.last_cost,
							b.batch_number,
							b.actual_type,
							b.material_terpakai
						FROM 
							bq_component_detail_add a 
							INNER JOIN production_real_detail_add b ON a.id_detail = b.id_detail
						WHERE 
							a.id_product='".$kode_product."' 
							AND a.id_milik = '".$id_milik."'
							AND b.id_production_detail = '".$id_production_detail."'
							AND a.detail_name='STRUKTUR THICKNESS' ";
		$dDetailAdd	= mysqli_query($conn, $detailAdd);
		$NUmRow		= mysqli_num_rows($dDetailAdd);
		// echo $NUmRow;
		if($NUmRow > 0){
			echo "<tr>";
				echo "<th align='left' colspan='8'>Add Materials</th>";
			echo "</tr>";
			
			while($valD = mysqli_fetch_array($dDetailAdd)){
			?>
			<tr>
				<td colspan='2'><?= $valD['nm_category'];?></td>
				<td><?= $valD['nm_material'];?></td>
				<td align='right'><?= number_format($valD['last_cost'],3);?> Kg</td>
				<td><?= $valD['batch_number'];?></td>
				<td colspan='2'><?= $valD['actual_type'];?></td>
				<td align='right'><?= number_format($valD['material_terpakai'],3);?> Kg</td>
			</tr>
			<?php
			}
		}
		?>
		</tbody>
		</table>
		<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<?php
		$tDetailLiner	= "SELECT 
								a.nm_category, 
								a.layer, 
								a.nm_material, 
								a.last_cost,
								b.batch_number,
								b.actual_type,
								b.layer as layer_real,
								b.material_terpakai	
							FROM 
								bq_component_detail a
								INNER JOIN production_real_detail b ON a.id_detail = b.id_detail
							WHERE 
								a.id_product='".$kode_product."' 
								AND a.id_milik = '".$id_milik."'
								AND a.detail_name='EXTERNAL LAYER THICKNESS' 
								AND b.id_production_detail = '".$id_production_detail."'
								AND a.id_material <> 'MTL-1903000' 
								AND a.id_category <>'TYP-0001' ";
		// echo $tDetailLiner;
		$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
		$numRows		= mysqli_num_rows($dDetailLiner);
		
		if($numRows > 0){
			?>
				
				<thead align='center'>
					<tr>
						<th align='left' colspan='8'>EXTERNAL LAYER THICKNESS</th>
					</tr>
				</thead>
				<tbody>
			<?php
		}
		
		while($valx = mysqli_fetch_array($dDetailLiner)){
			$dataL	= ($valx['layer'] == 0)?'-':floatval($valx['layer']);
			?>
			<tr>
				<td width='13%'><?= $valx['nm_category'];?></td>
				<td width='7%' align='center'><?= $dataL;?></td>
				<td width='27%'><?= $valx['nm_material'];?></td>
				<td width='10%' align='right'><?= number_format($valx['last_cost'],3);?> Kg</td>
				<td width='15%'><?= $valx['batch_number'];?></td>
				<td width='10%'><?= $valx['actual_type'];?></td>
				<td width='8%' align='center'><?= $valx['layer_real'];?></td>
				<td width='8%' align='right'><?= number_format($valx['material_terpakai'],3);?> Kg</td>
			</tr>
			<?php
		}
		
		$detailResin	= "SELECT 
								a.nm_category, 
								a.nm_material, 
								a.last_cost,
								b.batch_number,
								b.actual_type,
								b.layer as layer_real,
								b.material_terpakai	
							FROM 
								bq_component_detail a 
								INNER JOIN production_real_detail b ON a.id_detail = b.id_detail
							WHERE 
								a.id_product='".$kode_product."' 
								AND a.id_milik = '".$id_milik."'
								AND a.detail_name='EXTERNAL LAYER THICKNESS' 
								AND b.id_production_detail = '".$id_production_detail."'
								AND a.id_category ='TYP-0001' 
							ORDER BY a.id_detail DESC LIMIT 1 ";
		$dDetailResin	= mysqli_query($conn, $detailResin);
		// echo $detailResin;
		while($valH = mysqli_fetch_array($dDetailResin)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'],3);?> Kg</td>
				<td><?= $valH['batch_number'];?></td>
				<td colspan='2'><?= $valH['actual_type'];?></td>
				<td align='right'><?= number_format($valH['material_terpakai'],3);?> Kg</td>
			</tr>
			<?php
		}
		
		$detailPlus	= "SELECT 
							a.nm_category, 
							a.nm_material, 
							a.last_cost,
							b.batch_number,
							b.actual_type,
							b.material_terpakai
						FROM 
							bq_component_detail_plus a
							INNER JOIN production_real_detail_plus b ON a.id_detail = b.id_detail							
						WHERE 
							a.id_product='".$kode_product."' 
							AND a.id_milik = '".$id_milik."'
							AND a.detail_name='EXTERNAL LAYER THICKNESS' 
							AND b.id_production_detail = '".$id_production_detail."'
							AND a.id_material <> 'MTL-1903000' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'],3);?> Kg</td>
				<td><?= $valH['batch_number'];?></td>
				<td colspan='2'><?= $valH['actual_type'];?></td>
				<td align='right'><?= number_format($valH['material_terpakai'],3);?> Kg</td>
			</tr>
			<?php
		}
		
		$detailAdd	= "SELECT 
							a.nm_category, 
							a.nm_material, 
							a.last_cost,
							b.batch_number,
							b.actual_type,
							b.material_terpakai
						FROM 
							bq_component_detail_add a
							INNER JOIN production_real_detail_add b ON a.id_detail = b.id_detail
						WHERE 
							a.id_product='".$kode_product."'
							AND a.id_milik = '".$id_milik."'
							AND b.id_production_detail = '".$id_production_detail."'							
							AND a.detail_name='EXTERNAL LAYER THICKNESS' ";
		$dDetailAdd	= mysqli_query($conn, $detailAdd);
		$NUmRow		= mysqli_num_rows($dDetailAdd);
		// echo $NUmRow;
		if($NUmRow > 0){
			echo "<tr>";
				echo "<th align='left' colspan='8'>Add Materials</th>";
			echo "</tr>";
			
			while($valD = mysqli_fetch_array($dDetailAdd)){
			?>
			<tr>
				<td colspan='2'><?= $valD['nm_category'];?></td>
				<td><?= $valD['nm_material'];?></td>
				<td align='right'><?= number_format($valD['last_cost'],3);?> Kg</td>
				<td><?= $valD['batch_number'];?></td>
				<td colspan='2'><?= $valD['actual_type'];?></td>
				<td align='right'><?= number_format($valD['material_terpakai'],3);?> Kg</td>
			</tr>
			<?php
			}
		}
		?>
		</tbody>
	</table>
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='6'>TOPCOAT</th>
			</tr>
			<tr>
				<th width='20%'>Material</th>
				<th width='27%'>Tipe Material</th>
				<th width='10%'>Qty</th>
				<th width='15%'>Lot/Batch Num</th>
				<th width='18%'>Actual Type</th>
				<th width='8%'>Terpakai</th>
			</tr>
		</thead>
		<tbody>
		<?php
		
		$detailPlus	= "SELECT 
							a.nm_category, 
							a.nm_material, 
							a.last_cost,
							b.batch_number,
							b.actual_type,
							b.material_terpakai
						FROM 
							bq_component_detail_plus a 
							INNER JOIN production_real_detail_plus b ON a.id_detail = b.id_detail
						WHERE 
							a.id_product='".$kode_product."' 
							AND a.id_milik = '".$id_milik."'
							AND b.id_production_detail = '".$id_production_detail."'
							AND a.detail_name='TOPCOAT' 
							AND a.id_material <> 'MTL-1903000' GROUP BY a.id_detail";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'],3);?> Kg</td>
				<td><?= $valH['batch_number'];?></td>
				<td><?= $valH['actual_type'];?></td>
				<td align='right'><?= number_format($valH['material_terpakai'],3);?> Kg</td>
			</tr>
			<?php
		}
		
		$detailAdd	= "SELECT 
							a.nm_category, 
							a.nm_material, 
							a.last_cost,
							b.batch_number,
							b.actual_type,
							b.material_terpakai
						FROM 
							bq_component_detail_add a 
							INNER JOIN production_real_detail_add b ON a.id_detail = b.id_detail
						WHERE 
							a.id_product='".$kode_product."' 
							AND a.id_milik = '".$id_milik."'
							AND b.id_production_detail = '".$id_production_detail."'
							AND a.detail_name='TOPCOAT' ";
		$dDetailAdd	= mysqli_query($conn, $detailAdd);
		$NUmRow		= mysqli_num_rows($dDetailAdd);
		// echo $NUmRow;
		if($NUmRow > 0){
			echo "<tr>";
				echo "<th align='left' colspan='6'>Add Materials</th>";
			echo "</tr>";
			
			while($valD = mysqli_fetch_array($dDetailAdd)){
			?>
			<tr>
				<td><?= $valD['nm_category'];?></td>
				<td><?= $valD['nm_material'];?></td>
				<td align='right'><?= number_format($valD['last_cost'],3);?> Kg</td>
				<td><?= $valD['batch_number'];?></td>
				<td><?= $valD['actual_type'];?></td>
				<td align='right'><?= number_format($valD['material_terpakai'],3);?> Kg</td>
			</tr>
			<?php
			}
		}
		?>
		</tbody>
	</table>
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
			<tr>
				<th align='left' colspan='7'>NOTE</th>
			</tr>
			<tr>
				<td height='75px' colspan='7'></td> 
			</tr>
	</table>

	
	<style type="text/css">
	@page {
		margin-top: 1cm;
		margin-left: 1.5cm;
		margin-right: 1cm;
		margin-bottom: 1cm;
	}
	p.foot1 {
		font-family: verdana,arial,sans-serif;
		font-size:10px;
	}
	.font{
		font-family: verdana,arial,sans-serif;
		font-size:14px;
	}
	.fontheader{
		font-family: verdana,arial,sans-serif;
		font-size:13px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable th {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable th.head {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable td {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable td.cols {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	
	table.gridtable2 {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable2 th {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable2 th.head {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable2 td {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable2 td.cols {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	table.cooltabs {
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
	}
	table.cooltabs th.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
	}
	table.cooltabs td.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		padding: 5px;
	}
	#cooltabs {
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 800px;
		height: 20px;
	}
	#cooltabs2{
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 180px;
		height: 10px;
	}
	#space{
		padding: 3px;
		width: 180px;
		height: 1px;
	}
	#cooltabshead{
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 0 0;
		background: #dfdfdf;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	#cooltabschild{
		font-size:10px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 0 0 5px 5px;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	p {
		margin: 0 0 0 0;
	}
	p.pos_fixed {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 30px;
		left: 230px;
	}
	p.pos_fixed2 {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 589px;
		left: 230px;
	}
	.barcode {
		padding: 1.5mm;
		margin: 0;
		vertical-align: top;
		color: #000044;
	}
	.barcodecell {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: -10px;
		right: 10px;
	}
	.barcodecell2 {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: 548px;
		right: 10px;
	}
	p.barcs {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 38px;
		right: 115px;
	}
	p.barcs2 {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 591px;
		right: 115px;
	}
	p.pt {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 62px;
		left: 5px;
	}
	p.alamat {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 71px;
		left: 5px;
	}
	p.tlp {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 80px;
		left: 5px;
	}
	p.pt2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 596px;
		left: 5px;
	}
	p.alamat2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 605px;
		left: 5px;
	}
	p.tlp2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 614px;
		left: 5px;
	}
	#hrnew {
		border: 0;
		border-bottom: 1px dashed #ccc;
		background: #999;
	}
</style>

	
	<?php
	
	
	$html = ob_get_contents(); 
	// $footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px; color:black;'><i>Printed by : ".ucfirst(strtolower($printby)).", ".$today."</i></p>";
	// $footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px; color:black;'><i>Printed by : ".ucwords(strtolower($printby)).", ".$today." / ".$kode_produksi." / ".$kode_product." / ".$dRIPP['no_ipp']." / <b>First</b></i></p>";
	$footer = "<p class='foot1'><i>Printed by : ".ucwords(strtolower($printby)).", ".$today." / ".$kode_produksi." / ".$kode_product." / ".$id_production_detail."</i></p>";
	
	// exit;
	ob_end_clean(); 
	// $mpdf->SetWatermarkText('ORI Group');
	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('SPK Of Production');
	$mpdf->AddPage();
	$mpdf->SetFooter($footer);
	$mpdf->WriteHTML($html);		
	$mpdf->Output($kode_produksi.'_'.strtolower($dHeader['nm_product']).'_product_ke_'.$product_to.'.pdf' ,'I');
	
	//exit;
	//return $attachment;
}

function PrintSPK1x($Nama_APP, $kode_produksi, $koneksi, $printby, $kode_product, $product_to, $id_delivery, $id_milik){ 
	
	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);
	
	// print_r($KONN); exit;
	
	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);
	
	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	// include $sroot. "/".$Nama_APP."/application/libraries/PHPMailer/PHPMailerAutoload.php";
	include $sroot."/".$Nama_APP."/application/libraries/MPDF57/mpdf.php";
	$mpdf=new mPDF('utf-8','A4');
	// $mpdf=new mPDF('utf-8','A4-L');
	
	set_time_limit(0);
	ini_set('memory_limit','1024M');

	//Beginning Buffer to save PHP variables and HTML tags
	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('D, d-M-Y H:i:s');
	
	$qHeader2	= "	SELECT 
						a.*
					FROM 
						production_header a 
						LEFT JOIN production_detail b ON a.id_produksi=b.id_produksi						
					WHERE 
						a.id_produksi='".$kode_produksi."'
						AND b.id_delivery = '".$id_delivery."'
						LIMIT 1"; 

	$dResult2	= mysqli_query($conn, $qHeader2);
	$dHeader2	= mysqli_fetch_array($dResult2);
	
	$qHeader	= "SELECT a.*, b.* FROM bq_component_header a INNER JOIN bq_detail_header b ON a.id_milik = b.id 
						WHERE a.id_product='".$kode_product."' AND a.id_milik ='".$id_milik."' ";
	// echo $qHeader;
	$dResult	= mysqli_query($conn, $qHeader);
	$dHeader	= mysqli_fetch_array($dResult);
	
	$qIPP	= "SELECT a.* FROM production a WHERE a.no_ipp='".$dHeader2['no_ipp']."' ";
	// echo $qIPP;
	$dIPP	= mysqli_query($conn, $qIPP); 
	$dRIPP	= mysqli_fetch_array($dIPP);
	
	if($dHeader['id_category'] == 'pipe' OR $dHeader['id_category'] == 'pipe slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['length'])." x ".floatval($dHeader['thickness']);
	}
	elseif($dHeader['id_category'] == 'elbow mitter' OR $dHeader['id_category'] == 'elbow mould'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['thickness']).", ".$dHeader['type']." ".$dHeader['sudut'];
	}
	elseif($dHeader['id_category'] == 'concentric reducer' OR $dHeader['id_category'] == 'reducer tee mould' OR $dHeader['id_category'] == 'eccentric reducer' OR $dHeader['id_category'] == 'reducer tee slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['diameter_2'])." x ".floatval($dHeader['thickness']);
	}
	elseif($dHeader['id_category'] == 'end cap' OR $dHeader['id_category'] == 'flange slongsong' OR $dHeader['id_category'] == 'flange mould' OR $dHeader['id_category'] == 'equal tee mould' OR $dHeader['id_category'] == 'blind flange' OR $dHeader['id_category'] == 'equal tee slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['thickness']);
		$dim2 = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['panjang_neck_1'])." x ".floatval($dHeader['design_neck_1']);
	}
	else{$dim = "belum di set";} 
	
	?>
	
	<table class="gridtable" border='1' width='100%' cellpadding='2'>
		<tr>
			<td width='70px' rowspan='3' style='padding:0px;'><img src='<?=$sroot;?><?=$Nama_APP;?>/assets/images/ori_logo.jpg' alt="" height='80' width='70' ></td>
			<td align='center'><b>PT  ORI POLYTEC COMPOSITE</b></td>
			<td width='15%'>Doc Number</td>
			<td width='15%'><?= $dHeader2['no_ipp']; ?></td>
		</tr>
		<tr>
			<td align='center' rowspan='2'><b><h2>DAILY PRODUCTION REPORT</h2></b></td>
			<td>Rev</td>
			<td></td>
		</tr>
		<tr>
			<td>Due Date</td>
			<td></td>
		</tr>
	</table>
	<table class="gridtable2" border='0' width='100%' >
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width='20%'>Production Date</td>
			<td width='1%'>:</td>
			<td width='29%'></td>
			<td width='20%'>SO Number</td>
			<td width='1%'>:</td>
			<td width='29%'><?= $dHeader2['so_number']; ?></td>
		</tr>
		<tr>
			<td>SPK Number</td>
			<td>:</td>
			<td><?= $dHeader['no_spk'];?></td>
			<td>Customer</td>
			<td>:</td>
			<td><?= $dRIPP['nm_customer']; ?></td>
		</tr>
		<tr>
			<td>Machine Number</td>
			<td>:</td>
			<td><?= strtoupper($dHeader2['nm_mesin']);?></td>
			<td>Spec Product</td>
			<td>:</td>
			<td><?= $dim;?></td>
		</tr>
		<tr>
			<td>Project</td>
			<td>:</td>
			<td><?= strtoupper($dRIPP['project']); ?></td>
			<td><?= ucwords($dHeader['parent_product']);?> To</td>
			<td>:</td>
			<td><?= $product_to." (".strtoupper(strtolower($dHeader['no_komponen'])).") of ".$dHeader['qty']." Component";?></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th width='13%'>Material</th>
				<th width='7%'>Number Layer</th>
				<th width='27%'>Material Type</th>
				<th width='10%'>Qty</th>
				<th width='15%'>Lot/Batch Num</th>
				<th width='10%'>Actual Type</th>
				<th width='8%'>Layer</th>
				<th width='8%'>Used</th>
			</tr>
			<?php
			if($dHeader['id_category'] != 'flange slongsong'){
			?>
			<tr>
				<th align='left' colspan='8'>LINER THIKNESS / CB</th>
			</tr>
			<?php } ?>
		</thead>
		
		<tbody>
		<?php
		if($dHeader['id_category'] != 'flange slongsong'){
			
		$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='LINER THIKNESS / CB' AND id_material <> 'MTL-1903000' AND id_category <> 'TYP-0001' ";
		$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
		// echo $tDetailLiner; exit;
		while($valx = mysqli_fetch_array($dDetailLiner)){
			$dataL	= ($valx['layer'] == 0.00)?'-':(floatval($valx['layer']) == 0)?'-':floatval($valx['layer']);
			?>
			<tr>
				<td><?= $valx['nm_category'];?></td>
				<td align='center'><?= $dataL;?></td>
				<td><?= $valx['nm_material'];?></td>
				<td align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='LINER THIKNESS / CB' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
		$dDetailResin	= mysqli_query($conn, $detailResin);
		// echo $detailResin;
		while($valH = mysqli_fetch_array($dDetailResin)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='LINER THIKNESS / CB' AND id_material <> 'MTL-1903000' AND id_category = 'TYP-0002' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		?>
		
		</tbody>
		<?php } ?>
		<!-- FLANGE MOULD -->
		
		<?php
		if($dHeader['parent_product'] == 'flange mould' OR $dHeader['parent_product'] == 'flange slongsong'){
			if($dHeader['id_category'] != 'flange slongsong'){
		?>
			
			<thead align='center'>
				<tr>
					<th align='left' colspan='8'>STRUKTUR NECK 1</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost, jumlah, id_category, bw  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 1' AND id_material <> 'MTL-1903000' AND id_category <>'TYP-0001' ";
			// echo $tDetailLiner;
			$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
			while($valx = mysqli_fetch_array($dDetailLiner)){
				$dataL	= ($valx['layer'] == 0.00)?'-':$valx['layer'];
				$SUn	= "";
				if($valx['id_category'] == 'TYP-0005'){
					$SUn	= " | ".floatval($valx['jumlah']);
				}
				?>
				<tr>
					<td><?= $valx['nm_category'];?></td>
					<td align='center'><?= floatval($dataL);?></td>
					<td><?= $valx['nm_material'];?></td>
					<td align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
					<td></td>
					<td></td>
					<td></td> 
					<td></td>
				</tr>
				<?php
				if($valx['id_category'] == 'TYP-0005'){
				?>
				<tr>
					<td colspan='2'></td>
					<td><b>Jumlah Benang</b></td>
					<td align='right'><?= floatval($valx['jumlah'])?></td>
					<td colspan='3'><b>Actual Jumlah Benang</b></td>
					<td></td>
				</tr>
				<tr>
					<td colspan='2'></td>
					<td><b>Bandwidch</b></td>
					<td align='right'><?= floatval($valx['bw'])?></td>
					<td colspan='3'><b>Actual Bandwidch</b></td>
					<td></td>
				</tr>
				<?php
				}
			}
			
			$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 1' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
			$dDetailResin	= mysqli_query($conn, $detailResin);
			// echo $detailResin;
			while($valH = mysqli_fetch_array($dDetailResin)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td></td>
					<td colspan='2'></td>
					<td></td>
				</tr>
				<?php
			}
			
			$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 1' AND id_material <> 'MTL-1903000' AND id_category = 'TYP-0002' ";
			$dDetailPlus	= mysqli_query($conn, $detailPlus);
			// echo $detailPlus;
			while($valH = mysqli_fetch_array($dDetailPlus)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td></td>
					<td colspan='2'></td>
					<td></td>
				</tr>
				<?php
			}
			?>
			</tbody>
			<?php } ?>
			<!-- NECK 2-->
			<thead align='center'>
				<tr>
					<th align='left' colspan='8'>STRUKTUR NECK 2</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$tDetailLinerN2	= "SELECT nm_category, layer, nm_material, last_cost, jumlah, id_category, bw  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 2' AND id_material <> 'MTL-1903000' AND id_category <>'TYP-0001' ";
			// echo $tDetailLinerN2;
			$dDetailLinerN2	= mysqli_query($conn, $tDetailLinerN2);
			while($valx = mysqli_fetch_array($dDetailLinerN2)){
				$dataL	= ($valx['layer'] == 0.00)?'-':$valx['layer'];
				$SUn	= "";
				if($valx['id_category'] == 'TYP-0005'){
					$SUn	= " | ".floatval($valx['jumlah']);
				}
				?>
				<tr>
					<td><?= $valx['nm_category'];?></td>
					<td align='center'><?= floatval($dataL);?></td>
					<td><?= $valx['nm_material'];?></td>
					<td align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
					<td></td>
					<td></td>
					<td></td> 
					<td></td>
				</tr>
				<?php
				if($valx['id_category'] == 'TYP-0005'){
				?>
				<tr>
					<td colspan='2'></td>
					<td><b>Jumlah Benang</b></td>
					<td align='right'><?= floatval($valx['jumlah'])?></td>
					<td colspan='3'><b>Actual Jumlah Benang</b></td>
					<td></td>
				</tr>
				<tr>
					<td colspan='2'></td>
					<td><b>Bandwidch</b></td>
					<td align='right'><?= floatval($valx['bw'])?></td>
					<td colspan='3'><b>Actual Bandwidch</b></td>
					<td></td>
				</tr>
				<?php
				}
			}
			$detailResinN2	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 2' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
			$dDetailResinN2	= mysqli_query($conn, $detailResinN2);
			// echo $detailResin;
			while($valH = mysqli_fetch_array($dDetailResinN2)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td></td>
					<td colspan='2'></td>
					<td></td>
				</tr>
				<?php
			}
			$detailPlusN2	= "SELECT nm_category, nm_material, last_cost, last_full  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 2' AND id_material <> 'MTL-1903000' AND id_category = 'TYP-0002' ";
			$dDetailPlusN2	= mysqli_query($conn, $detailPlusN2);
			// echo $detailPlus;
			while($valH = mysqli_fetch_array($dDetailPlusN2)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td></td>
					<td colspan='2'></td>
					<td></td>
				</tr>
				<?php
			}
			
			
			?>
			</tbody>
			
		<?php
		}
		?>
		
		<!-- END FLANGE MOULD -->
		</table>
		<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='8'>STRUKTUR THICKNESS</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost, jumlah, id_category, bw  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR THICKNESS' AND id_material <> 'MTL-1903000' AND id_category <>'TYP-0001' ";
		// echo $tDetailLiner;
		$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
		while($valx = mysqli_fetch_array($dDetailLiner)){
			$dataL	= ($valx['layer'] == 0.00)?'-':$valx['layer'];
			$SUn	= "";
			if($valx['id_category'] == 'TYP-0005'){
				$SUn	= " | ".floatval($valx['jumlah']);
			}
			?>
			<tr>
				<td width='13%'><?= $valx['nm_category'];?></td>
				<td width='7%' align='center'><?= floatval($dataL);?></td>
				<td width='27%'><?= $valx['nm_material'];?></td>
				<td width='10%' align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
				<td width='15%'></td>
				<td width='10%'></td>
				<td width='8%'></td> 
				<td width='8%'></td>
			</tr>
			<?php
			if($valx['id_category'] == 'TYP-0005'){
			?>
			<tr>
				<td colspan='2'></td>
				<td><b>Jumlah Benang</b></td>
				<td align='right'><?= floatval($valx['jumlah'])?></td>
				<td colspan='3'><b>Actual Jumlah Benang</b></td>
				<td></td>
			</tr>
			<tr>
				<td colspan='2'></td>
				<td><b>Bandwidch</b></td>
				<td align='right'><?= floatval($valx['bw'])?></td>
				<td colspan='3'><b>Actual Bandwidch</b></td>
				<td></td>
			</tr>
			<?php
			}
		}
		
		$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR THICKNESS' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
		$dDetailResin	= mysqli_query($conn, $detailResin);
		// echo $detailResin;
		while($valH = mysqli_fetch_array($dDetailResin)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR THICKNESS' AND id_material <> 'MTL-1903000' AND id_category = 'TYP-0002' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		?>
		</tbody>
		<?php
		$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='EXTERNAL LAYER THICKNESS' AND id_material <> 'MTL-1903000' AND id_category <>'TYP-0001' ";
		// echo $tDetailLiner;
		$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
		$numRows		= mysqli_num_rows($dDetailLiner);
		
		if($numRows > 0){
				?>
					<thead align='center'>
						<tr>
							<th align='left' colspan='8'>EXTERNAL LAYER THICKNESS</th>
						</tr>
					</thead>
					<tbody>
				<?php
			
			
			while($valx = mysqli_fetch_array($dDetailLiner)){
				$dataL	= ($valx['layer'] == 0.00)?'-':number_format($valx['layer']);
				?>
				<tr>
					<td><?= $valx['nm_category'];?></td>
					<td align='center'><?= $dataL;?></td>
					<td><?= $valx['nm_material'];?></td>
					<td align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<?php
			}
			
			$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='EXTERNAL LAYER THICKNESS' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
			$dDetailResin	= mysqli_query($conn, $detailResin);
			// echo $detailResin;
			while($valH = mysqli_fetch_array($dDetailResin)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td></td>
					<td colspan='2'></td>
					<td></td>
				</tr>
				<?php
			}
			
			$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='EXTERNAL LAYER THICKNESS' AND id_material <> 'MTL-1903000' AND id_category = 'TYP-0002' ";
			$dDetailPlus	= mysqli_query($conn, $detailPlus);
			// echo $detailPlus;
			while($valH = mysqli_fetch_array($dDetailPlus)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td></td>
					<td colspan='2'></td>
					<td></td>
				</tr>
				<?php
			}
		}
		?>
		</tbody>
	</table>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='6'>TOPCOAT</th>
			</tr>
			<tr>
				<th width='20%'>Material</th>
				<th width='29%'>Material Type</th>
				<th width='10%'>Qty</th>
				<th width='15%'>Lot/Batch Num</th>
				<th width='18%'>Actual Type</th>
				<th width='8%'>Used</th>
			</tr>
		</thead>
		<tbody>
		<?php
		
		$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='TOPCOAT' AND id_material <> 'MTL-1903000' AND (id_category = 'TYP-0002' OR id_category = 'TYP-0001') ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='6'>THICKNESS</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><b>Thickness Est</b></td>
				<td align='center'><b><?= floatval($dHeader['est']);?></b></td>
				<td><b>Thickness Act (Web)</b></td>
				<td></td>
				<td><b>Thickness Act (Dry)</b></td>
				<td width='80px'></td>
			</tr>
			<tr>
				<td><b>Status : Reject / Pass</b></td>
				<td colspan='2'><b>Inspector :</b></td>
				<td width='100px'><b>Signed : </b></td>
				<td colspan='2'><b>Inspection Date : </b></td>
			</tr>
			<tr>
				<td height='60px' colspan='6' style='vertical-align: top;'><b>Note :</b></td> 
			</tr>
		</tbody>
	</table>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='9'>MACHINE SETUP</th>
			</tr>
			<tr>
				<th><b>#</b></th>
				<th><b>Standard</b></th>
				<th><b>Actual</b></th>
				<th><b>#</b></th>
				<th><b>Standard</b></th>
				<th><b>Actual</b></th>
				<th><b>#</b></th>
				<th><b>Standard</b></th>
				<th><b>Actual</b></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td align='center'>RPM</td>
				<td></td>
				<td></td>
				<td align='center'>TENTION</td>
				<td></td>
				<td></td>
				<td align='center'>SUDUT ROOVING</td>
				<td></td>
				<td></td>
			</tr>
		</tbody>
	</table>
	<div id='space'></div>
	<table class="gridtable3" width='100%' border='0' cellpadding='2'>
		<tr>
			<td>Dibuat,</td>
			<td></td>
			<td>Diperiksa,</td>
			<td></td>
			<td>Diketahui,</td>
			<td></td>
		</tr>
		<tr>
			<td height='25px'></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td>Ka. Regu</td>
			<td></td>
			<td>SPV Produksi</td>
			<td></td>
			<td>Dept Head</td>
			<td></td>
		</tr>
	</table>
	<?php
	// $mpdf->AddPage();
	
	
	if($dHeader['id_category'] == 'flange slongsong'){
	echo "<pagebreak />";
	?>

	<table class="gridtable" border='1' width='100%' cellpadding='2'>
		<thead>
		<tr>
			<td width='70px' rowspan='3' style='padding:0px;'><img src='<?=$sroot;?><?=$Nama_APP;?>/assets/images/ori_logo.jpg' alt="" height='80' width='70' ></td>
			<td align='center'><b>PT  ORI POLYTEC COMPOSITE</b></td>
			<td width='15%'>Doc Number</td>
			<td width='15%'><?= $dHeader2['no_ipp']; ?></td>
		</tr>
		<tr>
			<td align='center' rowspan='2'><b><h2>DAILY PRODUCTION REPORT</h2></b></td>
			<td>Rev</td>
			<td></td>
		</tr>
		<tr>
			<td>Due Date</td>
			<td></td>
		</tr>
		<thead>
	</table>
	<table class="gridtable2" border='0' width='100%' >
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width='20%'>Production Date</td>
			<td width='1%'>:</td>
			<td width='29%'></td>
			<td width='20%'>SO Number</td>
			<td width='1%'>:</td>
			<td width='29%'><?= $dHeader2['so_number']; ?></td>
		</tr>
		<tr>
			<td>SPK Number</td>
			<td>:</td>
			<td><?= $dHeader['no_spk'];?></td>
			<td>Customer</td>
			<td>:</td>
			<td><?= $dRIPP['nm_customer']; ?></td>
		</tr>
		<tr>
			<td>Machine Number</td>
			<td>:</td>
			<td><?= strtoupper($dHeader2['nm_mesin']);?></td>
			<td>Spec Product</td>
			<td>:</td>
			<td><?= $dim2;?></td>
		</tr>
		<tr>
			<td>Project</td>
			<td>:</td>
			<td><?= strtoupper($dRIPP['project']); ?></td>
			<td><?= ucwords($dHeader['parent_product']);?> To</td>
			<td>:</td>
			<td><?= $product_to." (".strtoupper(strtolower($dHeader['no_komponen'])).") of ".$dHeader['qty']." Component";?></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th width='13%'>Material</th>
				<th width='7%'>Number Layer</th>
				<th width='27%'>Material Type</th>
				<th width='10%'>Qty</th>
				<th width='15%'>Lot/Batch Num</th>
				<th width='10%'>Actual Type</th>
				<th width='8%'>Layer</th>
				<th width='8%'>Used</th>
			</tr>
			<tr>
				<th align='left' colspan='8'>LINER THIKNESS / CB</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='LINER THIKNESS / CB' AND id_material <> 'MTL-1903000' AND id_category <> 'TYP-0001' ";
		$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
		// echo $tDetailLiner; exit;
		while($valx = mysqli_fetch_array($dDetailLiner)){
			$dataL	= ($valx['layer'] == 0.00)?'-':(floatval($valx['layer']) == 0)?'-':floatval($valx['layer']);
			?>
			<tr>
				<td><?= $valx['nm_category'];?></td>
				<td align='center'><?= $dataL;?></td>
				<td><?= $valx['nm_material'];?></td>
				<td align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='LINER THIKNESS / CB' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
		$dDetailResin	= mysqli_query($conn, $detailResin);
		// echo $detailResin;
		while($valH = mysqli_fetch_array($dDetailResin)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='LINER THIKNESS / CB' AND id_material <> 'MTL-1903000' AND id_category = 'TYP-0002' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		?>
		
		</tbody>
		<?php
		if($dHeader['parent_product'] == 'flange mould' OR $dHeader['parent_product'] == 'flange slongsong'){
		?>
			<thead align='center'>
				<tr>
					<th align='left' colspan='8'>STRUKTUR NECK 1</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost, jumlah, id_category, bw  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 1' AND id_material <> 'MTL-1903000' AND id_category <>'TYP-0001' ";
			// echo $tDetailLiner;
			$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
			while($valx = mysqli_fetch_array($dDetailLiner)){
				$dataL	= ($valx['layer'] == 0.00)?'-':$valx['layer'];
				$SUn	= "";
				if($valx['id_category'] == 'TYP-0005'){
					$SUn	= " | ".floatval($valx['jumlah']);
				}
				?>
				<tr>
					<td><?= $valx['nm_category'];?></td>
					<td align='center'><?= floatval($dataL);?></td>
					<td><?= $valx['nm_material'];?></td>
					<td align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
					<td></td>
					<td></td>
					<td></td> 
					<td></td>
				</tr>
				<?php
				if($valx['id_category'] == 'TYP-0005'){
				?>
				<tr>
					<td colspan='2'></td>
					<td><b>Jumlah Benang</b></td>
					<td align='right'><?= floatval($valx['jumlah'])?></td>
					<td colspan='3'><b>Actual Jumlah Benang</b></td>
					<td></td>
				</tr>
				<tr>
					<td colspan='2'></td>
					<td><b>Bandwidch</b></td>
					<td align='right'><?= floatval($valx['bw'])?></td>
					<td colspan='3'><b>Actual Bandwidch</b></td>
					<td></td>
				</tr>
				<?php
				}
			}
			
			$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 1' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
			$dDetailResin	= mysqli_query($conn, $detailResin);
			// echo $detailResin;
			while($valH = mysqli_fetch_array($dDetailResin)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td></td>
					<td colspan='2'></td>
					<td></td>
				</tr>
				<?php
			}
			
			$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 1' AND id_material <> 'MTL-1903000' AND id_category = 'TYP-0002' ";
			$dDetailPlus	= mysqli_query($conn, $detailPlus);
			// echo $detailPlus;
			while($valH = mysqli_fetch_array($dDetailPlus)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td></td>
					<td colspan='2'></td>
					<td></td>
				</tr>
				<?php
			}
			?>
			</tbody>	
		<?php
		}
		?>
		<!-- END FLANGE MOULD -->
	</table>
	
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='6'>THICKNESS</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><b>Thickness Est</b></td>
				<td align='center'><b><?= floatval($dHeader['est_neck_1']);?></b></td>
				<td><b>Thickness Act (Web)</b></td>
				<td></td>
				<td><b>Thickness Act (Dry)</b></td>
				<td width='80px'></td>
			</tr>
			<tr>
				<td><b>Status : Reject / Pass</b></td>
				<td colspan='2'><b>Inspector :</b></td>
				<td width='100px'><b>Signed : </b></td>
				<td colspan='2'><b>Inspection Date : </b></td>
			</tr>
			<tr>
				<td height='60px' colspan='6' style='vertical-align: top;'><b>Note :</b></td> 
			</tr>
		</tbody>
	</table>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='9'>MACHINE SETUP</th>
			</tr>
			<tr>
				<th><b>#</b></th>
				<th><b>Standard</b></th>
				<th><b>Actual</b></th>
				<th><b>#</b></th>
				<th><b>Standard</b></th>
				<th><b>Actual</b></th>
				<th><b>#</b></th>
				<th><b>Standard</b></th>
				<th><b>Actual</b></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td align='center'>RPM</td>
				<td></td>
				<td></td>
				<td align='center'>TENTION</td>
				<td></td>
				<td></td>
				<td align='center'>SUDUT ROOVING</td>
				<td></td>
				<td></td>
			</tr>
		</tbody>
	</table>
	<div id='space'></div>
	<table class="gridtable3" width='100%' border='0' cellpadding='2'>
		<tr>
			<td>Dibuat,</td>
			<td></td>
			<td>Diperiksa,</td>
			<td></td>
			<td>Diketahui,</td>
			<td></td>
		</tr>
		<tr>
			<td height='25px'></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td>Ka. Regu</td>
			<td></td>
			<td>SPV Produksi</td>
			<td></td>
			<td>Dept Head</td>
			<td></td>
		</tr>
	</table>
	<?php
	}
	?>
	<div id='space'></div>
	<style type="text/css">
	@page {
		margin-top: 1 cm;
		margin-left: 1 cm;
		margin-right: 1 cm;
		margin-bottom: 1 cm;
		margin-footer: 0 cm
	}
	p.foot1 {
		font-family: verdana,arial,sans-serif;
		font-size:10px;
	}
	.font{
		font-family: verdana,arial,sans-serif;
		font-size:14px;
	}
	.fontheader{
		font-family: verdana,arial,sans-serif;
		font-size:13px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable th {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable th.head {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable td {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable td.cols {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	
	table.gridtable2 {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable2 th {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable2 th.head {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable2 td {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable2 td.cols {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	table.gridtable3 {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
	}
	table.gridtable3 th {
		border-width: 1px;
		padding: 8px;
	}
	table.gridtable3 th.head {
		border-width: 1px;
		padding: 8px;
		color: #ffffff;
	}
	table.gridtable3 td {
		border-width: 1px;
		padding: 8px;
		background-color: #ffffff;
	}
	table.gridtable3 td.cols {
		border-width: 1px;
		padding: 8px;
		background-color: #ffffff;
	}
	
	table.cooltabs {
		font-size:12px;
		font-family: verdana,arial,sans-serif;
	}
	
	table.cooltabs th.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
	}
	table.cooltabs td.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		padding: 5px;
	}
	#cooltabs {
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 800px;
		height: 20px;
	}
	#cooltabs2{
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 180px;
		height: 10px;
	}
	#space{
		padding: 3px;
		width: 180px;
		height: 1px;
	}
	#cooltabshead{
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 0 0;
		background: #dfdfdf;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	#cooltabschild{
		font-size:10px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 0 0 5px 5px;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	p {
		margin: 0 0 0 0;
	}
	p.pos_fixed {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 30px;
		left: 230px;
	}
	p.pos_fixed2 {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 589px;
		left: 230px;
	}
	.barcode {
		padding: 1.5mm;
		margin: 0;
		vertical-align: top;
		color: #000044;
	}
	.barcodecell {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: -10px;
		right: 10px;
	}
	.barcodecell2 {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: 548px;
		right: 10px;
	}
	p.barcs {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 38px;
		right: 115px;
	}
	p.barcs2 {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 591px;
		right: 115px;
	}
	p.pt {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 62px;
		left: 5px;
	}
	p.alamat {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 71px;
		left: 5px;
	}
	p.tlp {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 80px;
		left: 5px;
	}
	p.pt2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 596px;
		left: 5px;
	}
	p.alamat2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 605px;
		left: 5px;
	}
	p.tlp2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 614px;
		left: 5px;
	}
	#hrnew {
		border: 0;
		border-bottom: 1px dashed #ccc;
		background: #999;
	}
</style>

	
	<?php
	
	$html = ob_get_contents(); 
	// $footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px; color:black;'><i>Printed by : ".ucfirst(strtolower($printby)).", ".$today."</i></p>";
	$footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px; color:black;'><i>Printed by : ".ucwords(strtolower($printby)).", ".$today." / ".$kode_product." / ".$dRIPP['no_ipp']." / First</i></p>";
	// exit;
	ob_end_clean(); 
	// $mpdf->SetWatermarkText('ORI Group');
	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('SPK Of Production');
	$mpdf->AddPage();
	$mpdf->SetFooter($footer);
	$mpdf->WriteHTML($html);		
	$mpdf->Output($kode_produksi.'_'.strtolower($dHeader['nm_product']).'_product_ke_'.$product_to.'.pdf' ,'I');

	//exit;
	//return $attachment;
}

function PrintSPK1($Nama_APP, $kode_produksi, $koneksi, $printby, $kode_product, $product_to, $id_delivery, $id_milik){ 
	
	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);
	
	// print_r($KONN); exit;
	
	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);
	
	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	// include $sroot. "/".$Nama_APP."/application/libraries/PHPMailer/PHPMailerAutoload.php";
	include $sroot."/".$Nama_APP."/application/libraries/MPDF57/mpdf.php";
	$mpdf=new mPDF('utf-8','A4');
	// $mpdf=new mPDF('utf-8','A4-L');
	
	set_time_limit(0);
	ini_set('memory_limit','1024M');

	//Beginning Buffer to save PHP variables and HTML tags
	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('D, d-M-Y H:i:s');
	
	$qHeader2	= "	SELECT 
						a.*
					FROM 
						production_header a 
						LEFT JOIN production_detail b ON a.id_produksi=b.id_produksi						
					WHERE 
						a.id_produksi='".$kode_produksi."'
						AND b.id_delivery = '".$id_delivery."'
						LIMIT 1"; 

	$dResult2	= mysqli_query($conn, $qHeader2);
	$dHeader2	= mysqli_fetch_array($dResult2);
	
	$qHeader	= "SELECT a.*, b.* FROM bq_component_header a INNER JOIN bq_detail_header b ON a.id_milik = b.id 
						WHERE a.id_product='".$kode_product."' AND a.id_milik ='".$id_milik."' ";
	// echo $qHeader;
	$dResult	= mysqli_query($conn, $qHeader);
	$dHeader	= mysqli_fetch_array($dResult);
	
	$qIPP	= "SELECT a.* FROM production a WHERE a.no_ipp='".$dHeader2['no_ipp']."' ";
	// echo $qIPP;
	$dIPP	= mysqli_query($conn, $qIPP); 
	$dRIPP	= mysqli_fetch_array($dIPP);
	
	if($dHeader['id_category'] == 'pipe' OR $dHeader['id_category'] == 'pipe slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['length'])." x ".floatval($dHeader['thickness']);
	}
	elseif($dHeader['id_category'] == 'elbow mitter' OR $dHeader['id_category'] == 'elbow mould'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['thickness']).", ".$dHeader['type']." ".$dHeader['sudut'];
	}
	elseif($dHeader['id_category'] == 'concentric reducer' OR $dHeader['id_category'] == 'reducer tee mould' OR $dHeader['id_category'] == 'eccentric reducer' OR $dHeader['id_category'] == 'reducer tee slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['diameter_2'])." x ".floatval($dHeader['thickness']);
	}
	elseif($dHeader['id_category'] == 'end cap' OR $dHeader['id_category'] == 'flange slongsong' OR $dHeader['id_category'] == 'flange mould' OR $dHeader['id_category'] == 'equal tee mould' OR $dHeader['id_category'] == 'blind flange' OR $dHeader['id_category'] == 'equal tee slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['thickness']);
		$dim2 = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['panjang_neck_1'])." x ".floatval($dHeader['design_neck_1']);
	}
	else{$dim = "belum di set";} 
	
	?>
	
	<table class="gridtable" border='1' width='100%' cellpadding='2'>
		<tr>
			<td width='70px' rowspan='3' style='padding:0px;'><img src='<?=$sroot;?><?=$Nama_APP;?>/assets/images/ori_logo.jpg' alt="" height='80' width='70' ></td>
			<td align='center'><b>SENTRAL CONSTRUCTION</b></td>
			<td width='15%'>Doc Number</td>
			<td width='15%'>IPP19506L</td>
		</tr>
		<tr>
			<td align='center' rowspan='2'><b><h2>WORK ORDER BUILDING</h2></b></td>
			<td>Rev</td>
			<td></td>
		</tr>
		<tr>
			<td>Due Date</td>
			<td></td>
		</tr>
	</table>
	<br>
	<table class="gridtable2" border='0' width='100%' >
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width='20%'>Planning Start</td>
			<td width='1%'>:</td>
			<td width='29%'>23 December 2019</td>
			<td width='20%'>Estimation End</td>
			<td width='1%'>:</td>
			<td width='29%'>29 January 2020</td>
		</tr>
		<tr>
			<td>SPK Number</td>
			<td>:</td>
			<td><?= $dHeader['no_spk'];?></td>
			<td width='20%'>SO Number</td>
			<td width='1%'>:</td>
			<td width='29%'>SO-IPP19506L</td>
		</tr>
		<tr>
			<td>Project</td>
			<td>:</td>
			<td>MTH OFFICE WALL</td>
			<td>Customer</td>
			<td>:</td>
			<td>AIRTAMA INDONESIA, PT</td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th width='20%'>Group</th>
				<th width='20%'>Component</th>
				<th width='15%'>Spesification</th>
				<th width='10%'>Est Qty</th>
				<th width='10%'>Real Qty</th>
				<th width='20%'>Information</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td align='center' rowspan='3'><b>LANTAI</b></td>
				<td>MARMER</td>
				<td>30 x 30 cm</td>
				<td>45 Lusin</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>SEMEN</td>
				<td>Semen Gresik</td>
				<td>78.9 Kg</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>PASIR</td>
				<td>-</td>
				<td>2,5 Bak</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td align='center' rowspan='3'><b>DINDING</b></td>
				<td>BATU BATA MERAH</td>
				<td>30 x 20 x 15 cm</td>
				<td>4755 Bata</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>SEMEN</td>
				<td>Semen Gresik</td>
				<td>878.9 Kg</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>PASIR</td>
				<td>-</td>
				<td>9 Bak</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td align='center' rowspan='4'><b>ATAP</b></td>
				<td>BESI HOLO</td>
				<td>30 m x 4.7</td>
				<td>786 Buah</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>GENTING</td>
				<td>100 x 100 cm</td>
				<td>78 Buah</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>SCROOP</td>
				<td>-</td>
				<td>767 Buah</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>GIPSUM</td>
				<td>-</td>
				<td>23 Buah</td>
				<td></td>
				<td></td>
			</tr>
		</tbody>
	</table>	
	<div id='space'></div>
	<table class="gridtable3" width='100%' border='0' cellpadding='2'>
		<tr>
			<td>Dibuat,</td>
			<td></td>
			<td>Diperiksa,</td>
			<td></td>
			<td>Diketahui,</td>
			<td></td>
		</tr>
		<tr>
			<td height='25px'></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td>Ka. Regu</td>
			<td></td>
			<td>SPV Produksi</td>
			<td></td>
			<td>Dept Head</td>
			<td></td>
		</tr>
	</table>
	<?php
	// $mpdf->AddPage();
	
	
	if($dHeader['id_category'] == 'flange slongsong'){
	echo "<pagebreak />";
	?>

	<table class="gridtable" border='1' width='100%' cellpadding='2'>
		<thead>
		<tr>
			<td width='70px' rowspan='3' style='padding:0px;'><img src='<?=$sroot;?><?=$Nama_APP;?>/assets/images/ori_logo.jpg' alt="" height='80' width='70' ></td>
			<td align='center'><b>PT  ORI POLYTEC COMPOSITE</b></td>
			<td width='15%'>Doc Number</td>
			<td width='15%'><?= $dHeader2['no_ipp']; ?></td>
		</tr>
		<tr>
			<td align='center' rowspan='2'><b><h2>DAILY PRODUCTION REPORT</h2></b></td>
			<td>Rev</td>
			<td></td>
		</tr>
		<tr>
			<td>Due Date</td>
			<td></td>
		</tr>
		<thead>
	</table>
	<table class="gridtable2" border='0' width='100%' >
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width='20%'>Production Date</td>
			<td width='1%'>:</td>
			<td width='29%'></td>
			<td width='20%'>SO Number</td>
			<td width='1%'>:</td>
			<td width='29%'><?= $dHeader2['so_number']; ?></td>
		</tr>
		<tr>
			<td>SPK Number</td>
			<td>:</td>
			<td><?= $dHeader['no_spk'];?></td>
			<td>Customer</td>
			<td>:</td>
			<td><?= $dRIPP['nm_customer']; ?></td>
		</tr>
		<tr>
			<td>Machine Number</td>
			<td>:</td>
			<td><?= strtoupper($dHeader2['nm_mesin']);?></td>
			<td>Spec Product</td>
			<td>:</td>
			<td><?= $dim2;?></td>
		</tr>
		<tr>
			<td>Project</td>
			<td>:</td>
			<td><?= strtoupper($dRIPP['project']); ?></td>
			<td><?= ucwords($dHeader['parent_product']);?> To</td>
			<td>:</td>
			<td><?= $product_to." (".strtoupper(strtolower($dHeader['no_komponen'])).") of ".$dHeader['qty']." Component";?></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th width='13%'>Material</th>
				<th width='7%'>Number Layer</th>
				<th width='27%'>Material Type</th>
				<th width='10%'>Qty</th>
				<th width='15%'>Lot/Batch Num</th>
				<th width='10%'>Actual Type</th>
				<th width='8%'>Layer</th>
				<th width='8%'>Used</th>
			</tr>
			<tr>
				<th align='left' colspan='8'>LINER THIKNESS / CB</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='LINER THIKNESS / CB' AND id_material <> 'MTL-1903000' AND id_category <> 'TYP-0001' ";
		$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
		// echo $tDetailLiner; exit;
		while($valx = mysqli_fetch_array($dDetailLiner)){
			$dataL	= ($valx['layer'] == 0.00)?'-':(floatval($valx['layer']) == 0)?'-':floatval($valx['layer']);
			?>
			<tr>
				<td><?= $valx['nm_category'];?></td>
				<td align='center'><?= $dataL;?></td>
				<td><?= $valx['nm_material'];?></td>
				<td align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='LINER THIKNESS / CB' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
		$dDetailResin	= mysqli_query($conn, $detailResin);
		// echo $detailResin;
		while($valH = mysqli_fetch_array($dDetailResin)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='LINER THIKNESS / CB' AND id_material <> 'MTL-1903000' AND id_category = 'TYP-0002' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		?>
		
		</tbody>
		<?php
		if($dHeader['parent_product'] == 'flange mould' OR $dHeader['parent_product'] == 'flange slongsong'){
		?>
			<thead align='center'>
				<tr>
					<th align='left' colspan='8'>STRUKTUR NECK 1</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost, jumlah, id_category, bw  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 1' AND id_material <> 'MTL-1903000' AND id_category <>'TYP-0001' ";
			// echo $tDetailLiner;
			$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
			while($valx = mysqli_fetch_array($dDetailLiner)){
				$dataL	= ($valx['layer'] == 0.00)?'-':$valx['layer'];
				$SUn	= "";
				if($valx['id_category'] == 'TYP-0005'){
					$SUn	= " | ".floatval($valx['jumlah']);
				}
				?>
				<tr>
					<td><?= $valx['nm_category'];?></td>
					<td align='center'><?= floatval($dataL);?></td>
					<td><?= $valx['nm_material'];?></td>
					<td align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
					<td></td>
					<td></td>
					<td></td> 
					<td></td>
				</tr>
				<?php
				if($valx['id_category'] == 'TYP-0005'){
				?>
				<tr>
					<td colspan='2'></td>
					<td><b>Jumlah Benang</b></td>
					<td align='right'><?= floatval($valx['jumlah'])?></td>
					<td colspan='3'><b>Actual Jumlah Benang</b></td>
					<td></td>
				</tr>
				<tr>
					<td colspan='2'></td>
					<td><b>Bandwidch</b></td>
					<td align='right'><?= floatval($valx['bw'])?></td>
					<td colspan='3'><b>Actual Bandwidch</b></td>
					<td></td>
				</tr>
				<?php
				}
			}
			
			$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 1' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
			$dDetailResin	= mysqli_query($conn, $detailResin);
			// echo $detailResin;
			while($valH = mysqli_fetch_array($dDetailResin)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td></td>
					<td colspan='2'></td>
					<td></td>
				</tr>
				<?php
			}
			
			$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 1' AND id_material <> 'MTL-1903000' AND id_category = 'TYP-0002' ";
			$dDetailPlus	= mysqli_query($conn, $detailPlus);
			// echo $detailPlus;
			while($valH = mysqli_fetch_array($dDetailPlus)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td></td>
					<td colspan='2'></td>
					<td></td>
				</tr>
				<?php
			}
			?>
			</tbody>	
		<?php
		}
		?>
		<!-- END FLANGE MOULD -->
	</table>
	
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='6'>THICKNESS</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><b>Thickness Est</b></td>
				<td align='center'><b><?= floatval($dHeader['est_neck_1']);?></b></td>
				<td><b>Thickness Act (Web)</b></td>
				<td></td>
				<td><b>Thickness Act (Dry)</b></td>
				<td width='80px'></td>
			</tr>
			<tr>
				<td><b>Status : Reject / Pass</b></td>
				<td colspan='2'><b>Inspector :</b></td>
				<td width='100px'><b>Signed : </b></td>
				<td colspan='2'><b>Inspection Date : </b></td>
			</tr>
			<tr>
				<td height='60px' colspan='6' style='vertical-align: top;'><b>Note :</b></td> 
			</tr>
		</tbody>
	</table>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='9'>MACHINE SETUP</th>
			</tr>
			<tr>
				<th><b>#</b></th>
				<th><b>Standard</b></th>
				<th><b>Actual</b></th>
				<th><b>#</b></th>
				<th><b>Standard</b></th>
				<th><b>Actual</b></th>
				<th><b>#</b></th>
				<th><b>Standard</b></th>
				<th><b>Actual</b></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td align='center'>RPM</td>
				<td></td>
				<td></td>
				<td align='center'>TENTION</td>
				<td></td>
				<td></td>
				<td align='center'>SUDUT ROOVING</td>
				<td></td>
				<td></td>
			</tr>
		</tbody>
	</table>
	<div id='space'></div>
	<table class="gridtable3" width='100%' border='0' cellpadding='2'>
		<tr>
			<td>Dibuat,</td>
			<td></td>
			<td>Diperiksa,</td>
			<td></td>
			<td>Diketahui,</td>
			<td></td>
		</tr>
		<tr>
			<td height='25px'></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td>Ka. Regu</td>
			<td></td>
			<td>SPV Produksi</td>
			<td></td>
			<td>Dept Head</td>
			<td></td>
		</tr>
	</table>
	<?php
	}
	?>
	<div id='space'></div>
	<style type="text/css">
	@page {
		margin-top: 1 cm;
		margin-left: 1 cm;
		margin-right: 1 cm;
		margin-bottom: 1 cm;
		margin-footer: 0 cm
	}
	p.foot1 {
		font-family: verdana,arial,sans-serif;
		font-size:10px;
	}
	.font{
		font-family: verdana,arial,sans-serif;
		font-size:14px;
	}
	.fontheader{
		font-family: verdana,arial,sans-serif;
		font-size:13px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable th {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable th.head {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable td {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable td.cols {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	
	table.gridtable2 {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable2 th {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable2 th.head {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable2 td {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable2 td.cols {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	table.gridtable3 {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
	}
	table.gridtable3 th {
		border-width: 1px;
		padding: 8px;
	}
	table.gridtable3 th.head {
		border-width: 1px;
		padding: 8px;
		color: #ffffff;
	}
	table.gridtable3 td {
		border-width: 1px;
		padding: 8px;
		background-color: #ffffff;
	}
	table.gridtable3 td.cols {
		border-width: 1px;
		padding: 8px;
		background-color: #ffffff;
	}
	
	table.cooltabs {
		font-size:12px;
		font-family: verdana,arial,sans-serif;
	}
	
	table.cooltabs th.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
	}
	table.cooltabs td.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		padding: 5px;
	}
	#cooltabs {
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 800px;
		height: 20px;
	}
	#cooltabs2{
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 180px;
		height: 10px;
	}
	#space{
		padding: 3px;
		width: 180px;
		height: 1px;
	}
	#cooltabshead{
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 0 0;
		background: #dfdfdf;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	#cooltabschild{
		font-size:10px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 0 0 5px 5px;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	p {
		margin: 0 0 0 0;
	}
	p.pos_fixed {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 30px;
		left: 230px;
	}
	p.pos_fixed2 {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 589px;
		left: 230px;
	}
	.barcode {
		padding: 1.5mm;
		margin: 0;
		vertical-align: top;
		color: #000044;
	}
	.barcodecell {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: -10px;
		right: 10px;
	}
	.barcodecell2 {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: 548px;
		right: 10px;
	}
	p.barcs {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 38px;
		right: 115px;
	}
	p.barcs2 {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 591px;
		right: 115px;
	}
	p.pt {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 62px;
		left: 5px;
	}
	p.alamat {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 71px;
		left: 5px;
	}
	p.tlp {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 80px;
		left: 5px;
	}
	p.pt2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 596px;
		left: 5px;
	}
	p.alamat2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 605px;
		left: 5px;
	}
	p.tlp2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 614px;
		left: 5px;
	}
	#hrnew {
		border: 0;
		border-bottom: 1px dashed #ccc;
		background: #999;
	}
</style>

	
	<?php
	
	$html = ob_get_contents(); 
	// $footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px; color:black;'><i>Printed by : ".ucfirst(strtolower($printby)).", ".$today."</i></p>";
	$footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px; color:black;'><i>Printed by : ".ucwords(strtolower($printby)).", ".$today." / ".$kode_product." / ".$dRIPP['no_ipp']." / First</i></p>";
	// exit;
	ob_end_clean(); 
	// $mpdf->SetWatermarkText('ORI Group');
	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('SPK Of Production');
	$mpdf->AddPage();
	$mpdf->SetFooter($footer);
	$mpdf->WriteHTML($html);		
	$mpdf->Output($kode_produksi.'_'.strtolower($dHeader['nm_product']).'_product_ke_'.$product_to.'.pdf' ,'I');

	//exit;
	//return $attachment;
}

function PrintSPK2($Nama_APP, $kode_produksi, $koneksi, $printby, $kode_product, $product_to, $id_delivery, $id_milik){
	
	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);
	
	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);
	
	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	// include $sroot. "/".$Nama_APP."/application/libraries/PHPMailer/PHPMailerAutoload.php";
	include $sroot."/".$Nama_APP."/application/libraries/MPDF57/mpdf.php";
	$mpdf=new mPDF('utf-8','A4');
	// $mpdf=new mPDF('utf-8','A4-L');
	
	set_time_limit(0);
	ini_set('memory_limit','1024M');

	//Beginning Buffer to save PHP variables and HTML tags
	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('D, d-M-Y H:i:s');
	
	
	// $qHeader2	= "SELECT * FROM production_header WHERE id_produksi='".$kode_produksi."' ";
	$qHeader2	= "	SELECT 
						a.*
					FROM 
						production_header a 
						LEFT JOIN production_detail b ON a.id_produksi=b.id_produksi						
					WHERE 
						a.id_produksi='".$kode_produksi."'
						AND b.id_delivery = '".$id_delivery."'
						LIMIT 1"; 
	// echo $qHeader2;
	$dResult2	= mysqli_query($conn, $qHeader2);
	$dHeader2	= mysqli_fetch_array($dResult2);
					
	$qHeader	= "SELECT a.*, b.* FROM bq_component_header a INNER JOIN bq_detail_header b ON a.id_milik = b.id 
						WHERE a.id_product='".$kode_product."' AND a.id_milik ='".$id_milik."' ";
	// echo $qHeader;
	$dResult	= mysqli_query($conn, $qHeader);
	$dHeader	= mysqli_fetch_array($dResult);
	
	$qIPP	= "SELECT a.* FROM production a WHERE a.no_ipp='".$dHeader2['no_ipp']."' ";
	// echo $qIPP;
	$dIPP	= mysqli_query($conn, $qIPP);
	$dRIPP	= mysqli_fetch_array($dIPP);
	
	if($dHeader['id_category'] == 'pipe' OR $dHeader['id_category'] == 'pipe slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['length'])." x ".floatval($dHeader['thickness']);
	}
	elseif($dHeader['id_category'] == 'elbow mitter' OR $dHeader['id_category'] == 'elbow mould'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['thickness']).", ".$dHeader['type']." ".$dHeader['sudut'];
	}
	elseif($dHeader['id_category'] == 'concentric reducer' OR $dHeader['id_category'] == 'reducer tee mould' OR $dHeader['id_category'] == 'eccentric reducer' OR $dHeader['id_category'] == 'reducer tee slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['diameter_2'])." x ".floatval($dHeader['thickness']);
	}
	elseif($dHeader['id_category'] == 'end cap' OR $dHeader['id_category'] == 'flange slongsong' OR $dHeader['id_category'] == 'flange mould' OR $dHeader['id_category'] == 'equal tee mould' OR $dHeader['id_category'] == 'blind flange' OR $dHeader['id_category'] == 'equal tee slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['thickness']);
	}
	else{$dim = "belum di set";} 
	
	?>
	
	<table class="gridtable" border='1' width='100%' cellpadding='2'>
		<tr>
			<td width='70px' rowspan='3' style='padding:0px;'><img src='<?=$sroot;?><?=$Nama_APP;?>/assets/images/ori_logo.jpg' alt="" height='80' width='70' ></td>
			<td align='center'><b>PT  ORI POLYTEC COMPOSITE</b></td>
			<td width='15%'>Doc Number</td>
			<td width='15%'><?= $dRIPP['no_ipp'];?></td>
		</tr>
		<tr>
			<td align='center' rowspan='2'><b><h2>DAILY PRODUCTION REPORT</h2></b></td>
			<td>Rev.</td>
			<td></td>
		</tr>
		<tr>
			<td>Due Date</td>
			<td></td>
		</tr>
	</table>
	<table class="gridtable2" border='0' width='100%' >
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width='20%'>Production Date</td>
			<td width='1%'>:</td>
			<td width='29%'></td>
			<td width='20%'>SO Number</td>
			<td width='1%'>:</td>
			<td width='29%'><?= $dHeader2['so_number']; ?></td>
		</tr>
		<tr>
			<td>SPK Number</td>
			<td>:</td>
			<td><?= $dHeader['no_spk'];?></td>
			<td>Customer</td>
			<td>:</td>
			<td><?= $dRIPP['nm_customer']; ?></td>
		</tr>
		<tr>
			<td>Machine Number</td>
			<td>:</td>
			<td><?= strtoupper($dHeader2['nm_mesin']);?></td>
			<td>Spec Product</td>
			<td>:</td>
			<td><?= $dim;?></td>
		</tr>
		<tr>
			<td>Project</td>
			<td>:</td>
			<td><?= strtoupper($dRIPP['project']); ?></td>
			<td><?= ucwords($dHeader['parent_product']);?> To</td>
			<td>:</td>
			<td><?= $product_to." (".strtoupper(strtolower($dHeader['no_komponen'])).") of ".$dHeader['qty']." Component";?></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th width='20%'>Material</th>
				<th width='29%'>Material Type</th>
				<th width='10%'>Qty</th>
				<th width='13%'>Lot/Batch Num</th>
				<th width='18%'>Actual Type</th>
				<th width='8%'>Used</th>
			</tr>
			<tr>
				<th align='left' colspan='6'>LINER THIKNESS / CB</th>
			</tr>
			
		</thead>
		<tbody>
		<?php
		$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='LINER THIKNESS / CB' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
		$dDetailResin	= mysqli_query($conn, $detailResin);
		// echo $detailResin;
		while($valH = mysqli_fetch_array($dDetailResin)){
			?>
			<tr>
				<td><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='LINER THIKNESS / CB' AND id_material <> 'MTL-1903000' AND id_category <> 'TYP-0002' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailAdd	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_add WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='LINER THIKNESS / CB' ";
		$dDetailAdd	= mysqli_query($conn, $detailAdd);
		$NUmRow		= mysqli_num_rows($dDetailAdd);
		// echo $NUmRow;
		if($NUmRow > 0){
			echo "<tr>";
				echo "<th align='left' colspan='6'>Add Materials</th>";
			echo "</tr>";
			while($valD = mysqli_fetch_array($dDetailAdd)){
			?>
			<tr>
				<td><?= $valD['nm_category'];?></td>
				<td><?= $valD['nm_material'];?></td>
				<td align='right'><?= number_format($valD['last_cost'], 3);?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
			}
		}
		?>
		</tbody>
		</table>
		
		<?php
		if($dHeader['parent_product'] == 'flange mould' OR $dHeader['parent_product'] == 'flange slongsong'){
		?>
			
			<table class="gridtable" width='100%' border='1' cellpadding='2'>!-- FLANGE MOULD -->
				<thead align='center'>
					<tr>
						<th align='left' colspan='6'>STRUKTUR NECK 1</th>
					</tr>
					
				</thead>
				<tbody>
					<?php
					
					$detailResinN1	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='STRUKTUR NECK 1' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
					$dDetailResinN1	= mysqli_query($conn, $detailResinN1);
					// echo $detailResinN1;
					while($valH = mysqli_fetch_array($dDetailResinN1)){
						?>
						<tr>
							<td width='20%'><?= $valH['nm_category'];?></td>
							<td width='29%'><?= $valH['nm_material'];?></td>
							<td width='10%' align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
							<td width='13%'></td>
							<td width='18%'></td>
							<td width='8%'></td>
						</tr>
						<?php
					}
					
					$detailPlusN1	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='STRUKTUR NECK 1' AND id_material <> 'MTL-1903000' AND id_category <> 'TYP-0002' ";
					$dDetailPlusN1	= mysqli_query($conn, $detailPlusN1);
					// echo $detailPlus;
					while($valH = mysqli_fetch_array($dDetailPlusN1)){
						?>
						<tr>
							<td><?= $valH['nm_category'];?></td>
							<td><?= $valH['nm_material'];?></td>
							<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<?php
					}
					
					$detailAddN1	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_add WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='STRUKTUR NECK 1' ";
					$dDetailAddN1	= mysqli_query($conn, $detailAddN1);
					$NUmRow		= mysqli_num_rows($dDetailAddN1);
					// echo $NUmRow;
					if($NUmRow > 0){
						echo "<tr>";
							echo "<th align='left' colspan='6'>Add Materials</th>";
						echo "</tr>";
						
						while($valD = mysqli_fetch_array($dDetailAddN1)){
						?>
						<tr>
							<td><?= $valD['nm_category'];?></td>
							<td><?= $valD['nm_material'];?></td>
							<td align='right'><?= number_format($valD['last_cost'], 3);?> Kg</td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<?php
						}
					}
				?>
				</tbody>
			</table>
			
			<table class="gridtable" width='100%' border='1' cellpadding='2'>
				<thead align='center'>
				<tr>
					<th align='left' colspan='6'>STRUKTUR NECK 2</th>
				</tr>
				
				</thead>
				<tbody>
				<?php
				
				$detailResinN2	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='STRUKTUR NECK 2' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
				$dDetailResinN2	= mysqli_query($conn, $detailResinN2);
				// echo $detailResinN1;
				while($valH = mysqli_fetch_array($dDetailResinN2)){
					?>
					<tr>
						<td width='20%'><?= $valH['nm_category'];?></td>
						<td width='29%'><?= $valH['nm_material'];?></td>
						<td width='10%' align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
						<td width='13%'></td>
						<td width='18%'></td>
						<td width='8%'></td>
					</tr>
					<?php
				}
				
				$detailPlusN2	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='STRUKTUR NECK 2' AND id_material <> 'MTL-1903000' AND id_category <> 'TYP-0002' ";
				$dDetailPlusN2	= mysqli_query($conn, $detailPlusN2);
				// echo $detailPlus;
				while($valH = mysqli_fetch_array($dDetailPlusN2)){
					?>
					<tr>
						<td><?= $valH['nm_category'];?></td>
						<td><?= $valH['nm_material'];?></td>
						<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<?php
				}
				
				$detailAddN2	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_add WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='STRUKTUR NECK 2' ";
				$dDetailAddN2	= mysqli_query($conn, $detailAddN2);
				$NUmRow		= mysqli_num_rows($dDetailAddN2);
				// echo $NUmRow;
				if($NUmRow > 0){
					echo "<tr>";
						echo "<th align='left' colspan='6'>Add Materials</th>";
					echo "</tr>";
					
					while($valD = mysqli_fetch_array($dDetailAddN2)){
					?>
					<tr>
						<td><?= $valD['nm_category'];?></td>
						<td><?= $valD['nm_material'];?></td>
						<td align='right'><?= number_format($valD['last_cost'], 3);?> Kg</td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<?php
					}
				}
				?>  
				</tbody>
			</table>
		<?php
		}
		?>
		
		
		<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<!-- END FLANGE MOULD -->
		<thead align='center'>
			<tr>
				<th align='left' colspan='6'>STRUKTUR THICKNESS</th>
			</tr>
			
		</thead>
		<tbody>
		<?php
		
		$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='STRUKTUR THICKNESS' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
		$dDetailResin	= mysqli_query($conn, $detailResin);
		// echo $detailResin;
		while($valH = mysqli_fetch_array($dDetailResin)){
			?>
			<tr>
				<td width='20%'><?= $valH['nm_category'];?></td>
				<td width='29%'><?= $valH['nm_material'];?></td>
				<td width='10%' align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td width='13%'></td>
				<td width='18%'></td>
				<td width='8%'></td>
			</tr>
			<?php
		}
		
		$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='STRUKTUR THICKNESS' AND id_material <> 'MTL-1903000' AND id_category <> 'TYP-0002' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailAdd	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_add WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='STRUKTUR THICKNESS' ";
		$dDetailAdd	= mysqli_query($conn, $detailAdd);
		$NUmRow		= mysqli_num_rows($dDetailAdd);
		// echo $NUmRow;
		if($NUmRow > 0){
			echo "<tr>";
				echo "<th align='left' colspan='6'>Add Materials</th>";
			echo "</tr>";
			
			while($valD = mysqli_fetch_array($dDetailAdd)){
			?>
			<tr>
				<td><?= $valD['nm_category'];?></td>
				<td><?= $valD['nm_material'];?></td>
				<td align='right'><?= number_format($valD['last_cost'], 3);?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
			}
		}
		?>
		</tbody>
		</table>
		
		<?php
		$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='EXTERNAL LAYER THICKNESS' AND id_material <> 'MTL-1903000' AND id_category <>'TYP-0001' ";
		// echo $tDetailLiner;
		$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
		$numRows		= mysqli_num_rows($dDetailLiner);
		
		if($numRows > 0){
			?>
				<table class="gridtable" width='100%' border='1' cellpadding='2'>
				<thead align='center'>
					<tr>
						<th align='left' colspan='6'>EXTERNAL LAYER THICKNESS</th>
					</tr>
					
				</thead>
				<tbody>
			<?php
			
			$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='EXTERNAL LAYER THICKNESS' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
			$dDetailResin	= mysqli_query($conn, $detailResin);
			// echo $detailResin;
			while($valH = mysqli_fetch_array($dDetailResin)){
				?>
				<tr>
					<td width='20%'><?= $valH['nm_category'];?></td>
					<td width='29%'><?= $valH['nm_material'];?></td>
					<td width='10%' align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td width='13%'></td>
					<td width='18%'></td>
					<td width='8%'></td>
				</tr>
				<?php
			}
			
			$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='EXTERNAL LAYER THICKNESS' AND id_material <> 'MTL-1903000' AND id_category <> 'TYP-0002' ";
			$dDetailPlus	= mysqli_query($conn, $detailPlus);
			// echo $detailPlus;
			while($valH = mysqli_fetch_array($dDetailPlus)){
				?>
				<tr>
					<td><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<?php
			}
			
			$detailAdd	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_add WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='EXTERNAL LAYER THICKNESS' ";
			$dDetailAdd	= mysqli_query($conn, $detailAdd);
			$NUmRow		= mysqli_num_rows($dDetailAdd);
			// echo $NUmRow;
			if($NUmRow > 0){
				echo "<tr>";
					echo "<th align='left' colspan='6'>Add Materials</th>";
				echo "</tr>";
				
				while($valD = mysqli_fetch_array($dDetailAdd)){
				?>
				<tr>
					<td><?= $valD['nm_category'];?></td>
					<td><?= $valD['nm_material'];?></td>
					<td align='right'><?= number_format($valD['last_cost'], 3);?> Kg</td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<?php
				}
			}
			echo "</tbody>";
			echo "</table>";
		}
		?>

	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='6'>TOPCOAT</th>
			</tr>
			
		</thead>
		<tbody>
		<?php
		
		$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='TOPCOAT' AND id_material <> 'MTL-1903000' AND id_category <> 'TYP-0002' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td width='20%'><?= $valH['nm_category'];?></td>
				<td width='29%'><?= $valH['nm_material'];?></td>
				<td width='10%' align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td width='13%'></td>
				<td width='18%'></td>
				<td width='8%'></td>
			</tr>
			<?php
		}
		
		$detailAdd	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_add WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='TOPCOAT' ";
		$dDetailAdd	= mysqli_query($conn, $detailAdd);
		$NUmRow		= mysqli_num_rows($dDetailAdd);
		// echo $NUmRow;
		if($NUmRow > 0){
			echo "<tr>";
				echo "<th align='left' colspan='6'>Add Materials</th>";
			echo "</tr>";
			
			while($valD = mysqli_fetch_array($dDetailAdd)){
			?>
			<tr>
				<td><?= $valD['nm_category'];?></td>
				<td><?= $valD['nm_material'];?></td>
				<td align='right'><?= number_format($valD['last_cost'], 3);?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
			}
		}
		?>
		</tbody>
	</table>
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
			<tr>
				<th align='left' colspan='7'>NOTE</th>
			</tr>
			<tr>
				<td height='50px' colspan='7'></td> 
			</tr>
	</table>
	
	
	<style type="text/css">
	@page {
		margin-top: 1cm;
		margin-left: 1cm;
		margin-right: 1cm;
		margin-bottom: 1cm;
	}
	p.foot1 {
		font-family: verdana,arial,sans-serif;
		font-size:10px;
	}
	.font{
		font-family: verdana,arial,sans-serif;
		font-size:14px;
	}
	.fontheader{
		font-family: verdana,arial,sans-serif;
		font-size:13px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable th {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable th.head {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable td {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable td.cols {
		border-width: 1px;
		padding: 6px; 
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	
	table.gridtable2 {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable2 th {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable2 th.head {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable2 td {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable2 td.cols {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	table.cooltabs {
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
	}
	table.cooltabs th.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
	}
	table.cooltabs td.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		padding: 5px;
	}
	#cooltabs {
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 800px;
		height: 20px;
	}
	#cooltabs2{
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 180px;
		height: 10px;
	}
	#space{
		padding: 3px;
		width: 180px;
		height: 1px;
	}
	#cooltabshead{
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 0 0;
		background: #dfdfdf;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	#cooltabschild{
		font-size:10px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 0 0 5px 5px;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	p {
		margin: 0 0 0 0;
	}
	p.pos_fixed {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 30px;
		left: 230px;
	}
	p.pos_fixed2 {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 589px;
		left: 230px;
	}
	.barcode {
		padding: 1.5mm;
		margin: 0;
		vertical-align: top;
		color: #000044;
	}
	.barcodecell {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: -10px;
		right: 10px;
	}
	.barcodecell2 {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: 548px;
		right: 10px;
	}
	p.barcs {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 38px;
		right: 115px;
	}
	p.barcs2 {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 591px;
		right: 115px;
	}
	p.pt {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 62px;
		left: 5px;
	}
	p.alamat {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 71px;
		left: 5px;
	}
	p.tlp {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 80px;
		left: 5px;
	}
	p.pt2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 596px;
		left: 5px;
	}
	p.alamat2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 605px;
		left: 5px;
	}
	p.tlp2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 614px;
		left: 5px;
	}
	#hrnew {
		border: 0;
		border-bottom: 1px dashed #ccc;
		background: #999;
	}
</style>

	
	<?php
	
	$html = ob_get_contents(); 
	$footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px; color:black;'><i>Printed by : ".ucwords(strtolower($printby)).", ".$today." / ".$kode_product." / ".$dRIPP['no_ipp']." / <b>Second</b></i></p>";
	// exit;
	ob_end_clean(); 
	// $mpdf->SetWatermarkText('ORI Group');
	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('SPK Of Production');
	$mpdf->AddPage();
	$mpdf->SetFooter($footer);
	$mpdf->WriteHTML($html);		
	$mpdf->Output($kode_produksi.'_'.strtolower($dHeader['nm_product']).'_product_ke_'.$product_to.'.pdf' ,'I');
}

function PrintIPPx($Nama_APP, $no_ipp, $koneksi, $printby){
	
	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);
	
	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);
	
	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	// include $sroot. "/".$Nama_APP."/application/libraries/PHPMailer/PHPMailerAutoload.php";
	include $sroot."/".$Nama_APP."/application/libraries/MPDF57/mpdf.php";
	$mpdf=new mPDF('utf-8','A4');
	// $mpdf=new mPDF('utf-8','A4-L');
	
	set_time_limit(0);
	ini_set('memory_limit','1024M');

	//Beginning Buffer to save PHP variables and HTML tags
	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('l, d F Y [H:i:s]');
	
	$qHeader	= "SELECT a.* FROM production a WHERE a.no_ipp='".$no_ipp."' ";
	$dResult	= mysqli_query($conn, $qHeader);
	$dHeader	= mysqli_fetch_array($dResult);
	
	$qHeaderD	= "SELECT a.* FROM production_req_customer a WHERE a.no_ipp='".$no_ipp."' ";
	$dResultD	= mysqli_query($conn, $qHeaderD);
	$dHeaderD	= mysqli_fetch_array($dResultD);
	
	$qFluida	= "SELECT a.* FROM list_fluida a WHERE a.id_fluida='".$dHeaderD['id_fluida']."' ";
	$dRFluida	= mysqli_query($conn, $qFluida);
	$dFluidaD	= mysqli_fetch_array($dRFluida);
	
	$qStand		= "SELECT a.* FROM list_standard a WHERE a.id_standard='".$dHeaderD['standard_spec']."' ";
	$dRStand	= mysqli_query($conn, $qStand);
	$dStand		= mysqli_fetch_array($dRStand);
	
	$qHeaderShi	= "SELECT a.* FROM production_delivery a WHERE a.no_ipp='".$no_ipp."' ";
	$dResultShip	= mysqli_query($conn, $qHeaderShi);
	$dHeaderShip	= mysqli_fetch_array($dResultShip);
	?>
	
	<table class="gridtable" border='1' width='100%' cellpadding='2'>
		<tr>
			<td width='15%' rowspan='4'></td>
			<td align='center'><b>PT  ORI POLYTEC COMPOSITE</b></td>
		</tr>
		<tr>
			<td align='center'><b><h2>IDENTIFIKASI PERMINTAAN PELANGGAN</h2></b></td>
		</tr>
	</table>
	<br>
	<table class="gridtable2" border='0' width='100%' >
		<tr>
			<td width='24%'>IPP Number</td>
			<td width='1%'>:</td>
			<td width='25%'><?= $no_ipp; ?></td>
			<td width='24%'>IPP Date</td>
			<td width='1%'>:</td>
			<td width='25%'><?= date('d F Y', strtotime($dHeader['created_date'])); ?></td>
		</tr>
		<tr>
			<td>Customer Name</td>
			<td>:</td>
			<td><?= $dHeader['nm_customer']; ?></td>
			<td>Revision To</td>
			<td>:</td>
			<td><?= $dHeader['ref_ke']; ?></td>
		</tr>
		<tr>
			<td>Project</td>
			<td>:</td>
			<td><?= $dHeader['project']; ?></td>
			<td>Product</td>
			<td>:</td>
			<td><?= $dHeader['product']; ?></td>
		</tr>
	</table>
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='center' colspan='5'>CUSTOM CUSTOMER</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th width='40%' colspan='2'>Application</th>
				<th width='40%' colspan='2'>Fluida</th>
				<th width='20%'>Liner Thickness</th>
			</tr>
			<tr>
				<td width='40%' align='center' colspan='2'><?= strtoupper($dHeaderD['aplikasi']); ?></td>
				<td width='40%' align='center' colspan='2'><?= $dFluidaD['fluida_name']; ?></td>
				<td width='20%' align='center'><?= $dHeaderD['liner_thick']; ?></td>
			</tr>
			<tr>
				<th width='20%'>Status IPP</th>
				<th width='20%'>Resin Type</th>
				<th width='20%'>Life Time</th>
				<th width='20%'>Stifness</th>
				<th width='20%'>Pressure</th>
			</tr>
			<tr>
				<td align='center'><?= $dHeader['sts_request']; ?></td>
				<td align='center'><?= $dHeader['type']; ?></td>
				<td align='center'><?= $dHeader['time_life']; ?> Year</td>
				<td align='center'><?= $dHeader['stifness']; ?> Pa</td>
				<td align='center'><?= $dHeader['pressure']; ?> Bar</td>
			</tr>
			<tr>
				<th align='center' colspan='2'>Vacum Rate</th>
				<th align='center' colspan='3'>Note</th>
			</tr>
			<tr>
				<td align='center' colspan='2'><?= strtoupper($dHeader['vacum_rate']); ?></td>
				<td align='center' colspan='3'><?= strtoupper(strtolower($dHeader['note'])); ?></td>
			</tr>
			<tr>
				<th width='20%'>Standard</th>
				<th width='20%'>Standard 1</th>
				<th width='20%'>Standard 2</th>
				<th width='20%'>Standard 3</th>
				<th width='20%'>Standard 4</th>
			</tr>
			<tr>
				<td align='center'><?= $dStand['nm_standard']; ?></td>
				<td align='center'><?= ($dHeaderD['standard_spec'] == 'S-ETC-08')?$dHeaderD['standard_1']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['standard_spec'] == 'S-ETC-08')?$dHeaderD['standard_2']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['standard_spec'] == 'S-ETC-08')?$dHeaderD['standard_3']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['standard_spec'] == 'S-ETC-08')?$dHeaderD['standard_4']:'-' ; ?></td>
			</tr>
			<tr>
				<th width='20%'>Document</th>
				<th width='20%'>Document 1</th>
				<th width='20%'>Document 2</th>
				<th width='20%'>Document 3</th>
				<th width='20%'>Document 4</th>
			</tr>
			<tr>
				<td align='center'><?= ($dHeaderD['document'] == 'Y')?'YES':'NO'; ?></td>
				<td align='center'><?= ($dHeaderD['document'] == 'Y')?$dHeaderD['document_1']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['document'] == 'Y')?$dHeaderD['document_2']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['document'] == 'Y')?$dHeaderD['document_3']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['document'] == 'Y')?$dHeaderD['document_4']:'-' ; ?></td>
			</tr>
			<tr>
				<th width='20%'>Certificate</th>
				<th width='20%'>Certificate 1</th>
				<th width='20%'>Certificate 2</th>
				<th width='20%'>Certificate 3</th>
				<th width='20%'>Certificate 4</th>
			</tr>
			<tr>
				<td align='center'><?= ($dHeaderD['sertifikat'] == 'Y')?'YES':'NO'; ?></td>
				<td align='center'><?= ($dHeaderD['sertifikat'] == 'Y')?$dHeaderD['sertifikat_1']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['sertifikat'] == 'Y')?$dHeaderD['sertifikat_2']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['sertifikat'] == 'Y')?$dHeaderD['sertifikat_3']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['sertifikat'] == 'Y')?$dHeaderD['sertifikat_4']:'-' ; ?></td>
			</tr>
			<tr>
				<th width='20%'>Testing</th>
				<th width='20%'>Testing 1</th>
				<th width='20%'>Testing 2</th>
				<th width='20%'>Testing 3</th>
				<th width='20%'>Testing 4</th>
			</tr>
			<tr>
				<td align='center'><?= ($dHeaderD['test'] == 'Y')?'YES':'NO'; ?></td>
				<td align='center'><?= ($dHeaderD['test'] == 'Y')?$dHeaderD['test_1']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['test'] == 'Y')?$dHeaderD['test_2']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['test'] == 'Y')?$dHeaderD['test_3']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['test'] == 'Y')?$dHeaderD['test_4']:'-' ; ?></td>
			</tr>
			<tr>
				<th width='20%'>Color</th>
				<th width='20%'>Color Liner</th>
				<th width='20%'>Color Structure</th>
				<th width='20%'>Color External</th>
				<th width='20%'>Color Topcoat</th>
			</tr>
			<tr>
				<td align='center'><?= ($dHeaderD['color'] == 'Y')?'YES':'NO'; ?></td>
				<td align='center'><?= ($dHeaderD['color'] == 'Y')?$dHeaderD['color_liner']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['color'] == 'Y')?$dHeaderD['color_structure']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['color'] == 'Y')?$dHeaderD['color_external']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['color'] == 'Y')?$dHeaderD['color_topcoat']:'-' ; ?></td>
			</tr>
			<tr>
				<th width='20%'>Abrasive</th>
				<th width='20%'>Abrasive Liner</th>
				<th width='20%'>Abrasive Structure</th>
				<th width='20%'>Abrasive External</th>
				<th width='20%'>Abrasive Topcoat</th>
			</tr>
			<tr>
				<td align='center'><?= ($dHeaderD['abrasi'] == 'Y')?'YES':'NO'; ?></td>
				<td align='center'><?= ($dHeaderD['abrasi'] == 'Y')?$dHeaderD['abrasi_liner']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['abrasi'] == 'Y')?$dHeaderD['abrasi_structure']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['abrasi'] == 'Y')?$dHeaderD['abrasi_ekternal']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['abrasi'] == 'Y')?$dHeaderD['abrasi_topcoat']:'-' ; ?></td>
			</tr>
			<tr>
				<th width='20%'>Conductive</th>
				<th width='20%'>Conductive Liner</th>
				<th width='20%'>Conductive Structure</th>
				<th width='20%'>Conductive External</th>
				<th width='20%'>Conductive Topcoat</th>
			</tr>
			<tr>
				<td align='center'><?= ($dHeaderD['konduksi'] == 'Y')?'YES':'NO'; ?></td>
				<td align='center'><?= ($dHeaderD['konduksi'] == 'Y')?$dHeaderD['konduksi_liner']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['konduksi'] == 'Y')?$dHeaderD['konduksi_structure']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['konduksi'] == 'Y')?$dHeaderD['konduksi_eksternal']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['konduksi'] == 'Y')?$dHeaderD['konduksi_topcoat']:'-' ; ?></td>
			</tr>
			<tr>
				<th width='20%'>Fire Retardant</th>
				<th width='20%'>Fire Retardant Liner</th>
				<th width='20%'>Fire Retardant Structure</th>
				<th width='20%'>Fire Retardant External</th>
				<th width='20%'>Fire Retardant Topcoat</th>
			</tr>
			<tr>
				<td align='center'><?= ($dHeaderD['tahan_api'] == 'Y')?'YES':'NO'; ?></td>
				<td align='center'><?= ($dHeaderD['tahan_api'] == 'Y')?$dHeaderD['tahan_api_liner']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['tahan_api'] == 'Y')?$dHeaderD['tahan_api_structure']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['tahan_api'] == 'Y')?$dHeaderD['tahan_api_eksternal']:'-' ; ?></td>
				<td align='center'><?= ($dHeaderD['tahan_api'] == 'Y')?$dHeaderD['tahan_api_topcoat']:'-' ; ?></td>
			</tr> 
		</tbody>
	</table>
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='center' colspan='5'>SHIPPING</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th width='20%'>Country</th>
				<th width='20%'>Date Delivery</th>
				<th width='20%'>Shipping Method</th>
				<th width='60%' colspan='2'>Address</th>
			</tr>
			<tr>
				<td width='20%' align='center'><?= strtoupper($dHeaderShip['country_code']); ?></td>
				<td width='20%' align='center'><?= strtoupper(date('d F Y', strtotime($dHeaderShip['date_delivery']))); ?></td>
				<td width='20%' align='center'><?= strtoupper($dHeaderShip['metode_delivery']); ?></td>
				<td width='60%' align='center' colspan='2'><?= strtoupper($dHeaderShip['address_delivery']); ?></td>
			</tr>
			<tr>
				<th width='20%'>Instalation</th>
				<th width='20%'>Handling Equipment</th>
				<th width='20%'>Truck</th>
				<th width='20%'>Vendor</th>
				<th width='20%'>Qty</th>
			</tr>
			<tr>
				<td align='center'><?= $dHeaderShip['isntalasi_by']; ?></td>
				<td align='center'><?= $dHeaderShip['alat_berat']; ?></td>
				<td align='center'><?= ($dHeaderShip['truck'] == null || $dHeaderShip['truck'] == '')?'-':$dHeaderShip['truck']; ?></td>
				<td align='center'><?= ($dHeaderShip['vendor'] == null || $dHeaderShip['vendor'] == '')?'-':$dHeaderShip['vendor']; ?></td>
				<td align='center'><?= ($dHeaderShip['qty'] == null || $dHeaderShip['qty'] == '')?'-':$dHeaderShip['qty']; ?></td>
			</tr>
			<tr>
				<th width='20%'>Packing</th>
				<th width='20%'>Pipe Packing</th>
				<th width='20%'>Fitting Packing</th>
				<th width='20%'>DG Packing</th>
				<th width='20%'>Validity & Guarantee</th>
			</tr>
			<tr>
				<td align='center'><?= $dHeaderShip['packing']; ?></td>
				<td align='center'><?= $dHeaderShip['packing_pipa_qty']; ?></td>
				<td align='center'><?= $dHeaderShip['packing_fitting_qty']; ?></td>
				<td align='center'><?= $dHeaderShip['packing_dg_qty']; ?></td>
				<td align='center'><?= $dHeaderShip['garansi']; ?> Year</td>
			</tr>
		</tbody>
	</table>
	<br>
	<p class='foot1'> <?php echo "<i>Printed by : ".ucwords(strtolower($printby)).", ".$today."</i>"; ?> </p>
	
	
	<style type="text/css">
	@page {
		margin-top: 1cm;
		margin-left: 1.5cm;
		margin-right: 1cm;
		margin-bottom: 1cm;
	}
	p.foot1 {
		font-family: verdana,arial,sans-serif;
		font-size:10px;
	}
	.font{
		font-family: verdana,arial,sans-serif;
		font-size:14px;
	}
	.fontheader{
		font-family: verdana,arial,sans-serif;
		font-size:13px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable th {
		border-width: 1px;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable th.head {
		border-width: 1px;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable td {
		border-width: 1px;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable td.cols {
		border-width: 1px;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	
	table.gridtable2 {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable2 th {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable2 th.head {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable2 td {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable2 td.cols {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	table.cooltabs {
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
	}
	table.cooltabs th.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
	}
	table.cooltabs td.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		padding: 5px;
	}
	#cooltabs {
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 800px;
		height: 20px;
	}
	#cooltabs2{
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 180px;
		height: 10px;
	}
	#space{
		padding: 3px;
		width: 180px;
		height: 1px;
	}
	#cooltabshead{
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 0 0;
		background: #dfdfdf;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	#cooltabschild{
		font-size:10px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 0 0 5px 5px;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	p {
		margin: 0 0 0 0;
	}
	p.pos_fixed {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 30px;
		left: 230px;
	}
	p.pos_fixed2 {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 589px;
		left: 230px;
	}
	.barcode {
		padding: 1.5mm;
		margin: 0;
		vertical-align: top;
		color: #000044;
	}
	.barcodecell {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: -10px;
		right: 10px;
	}
	.barcodecell2 {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: 548px;
		right: 10px;
	}
	p.barcs {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 38px;
		right: 115px;
	}
	p.barcs2 {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 591px;
		right: 115px;
	}
	p.pt {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 62px;
		left: 5px;
	}
	p.alamat {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 71px;
		left: 5px;
	}
	p.tlp {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 80px;
		left: 5px;
	}
	p.pt2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 596px;
		left: 5px;
	}
	p.alamat2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 605px;
		left: 5px;
	}
	p.tlp2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 614px;
		left: 5px;
	}
	#hrnew {
		border: 0;
		border-bottom: 1px dashed #ccc;
		background: #999;
	}
</style>

	
	<?php
	
	$html = ob_get_contents(); 
	// $footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px;'><i>Printed by : ".ucfirst(strtolower($printby)).", ".$today."</i></p>";
	// exit;
	ob_end_clean(); 
	// $mpdf->SetWatermarkText('ORI Group');
	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle($no_ipp);
	// $mpdf->setHTMLFooter($footer);
	$mpdf->WriteHTML($html);		
	$mpdf->Output($no_ipp.'revisi_ke.pdf' ,'I');

	//exit;
	//return $attachment;
}

function PrintIPP($Nama_APP, $no_ipp, $koneksi, $printby){
	
	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);
	
	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);
	
	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	include $sroot."/".$Nama_APP."/application/libraries/MPDF57/mpdf.php";
	// $mpdf=new mPDF('utf-8','A4');
	$mpdf=new mPDF('utf-8','A4-L');
	
	set_time_limit(0);
	ini_set('memory_limit','1024M');

	//Beginning Buffer to save PHP variables and HTML tags
	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('l, d F Y [H:i:s]');
	
	$qHeader	= "SELECT a.* FROM production a WHERE a.no_ipp='".$no_ipp."' ";
	$dResult	= mysqli_query($conn, $qHeader);
	$dHeader	= mysqli_fetch_array($dResult);
	
	// $qHeaderD	= "SELECT a.* FROM production_req_customer a WHERE a.no_ipp='".$no_ipp."' ";
	// $dResultD	= mysqli_query($conn, $qHeaderD);
	// $dHeaderD	= mysqli_fetch_array($dResultD);
	
	$qFluida	= "SELECT a.* FROM list_fluida a WHERE a.id_fluida='".$dHeaderD['id_fluida']."' ";
	$dRFluida	= mysqli_query($conn, $qFluida);
	$dFluidaD	= mysqli_fetch_array($dRFluida);
	
	$qStand		= "SELECT a.* FROM list_standard a WHERE a.id_standard='".$dHeaderD['standard_spec']."' ";
	$dRStand	= mysqli_query($conn, $qStand);
	$dStand		= mysqli_fetch_array($dRStand);
	
	$qHeaderShi	= "SELECT a.*, b.country_name FROM production_delivery a INNER JOIN country b ON a.country_code=b.country_code WHERE a.no_ipp='".$no_ipp."' ";
	$dResultShip	= mysqli_query($conn, $qHeaderShi);
	$dHeaderShip	= mysqli_fetch_array($dResultShip);
	
	$qHeaderDet	= "SELECT a.* FROM production_req_sp a WHERE a.no_ipp='".$no_ipp."' ";
	$dResultDet	= mysqli_query($conn, $qHeaderDet);
	$dResultDet2	= mysqli_query($conn, $qHeaderDet);
	?>
	
	<table class="gridtable2" border='1' width='100%' cellpadding='2'>
		<tr>
			<td align='center'><b>PT  ORI POLYTEC COMPOSITE</b></td>
		</tr>
		<tr>
			<td align='center'><b><h2>IDENTIFIKASI PERMINTAAN PELANGGAN</h2></b></td>
		</tr>
	</table>
	<br>
	<table class="gridtable2" border='0' width='100%' >
		<tr>
			<td width='24%'>IPP Number</td>
			<td width='1%'>:</td>
			<td width='25%'><?= $no_ipp; ?></td>
			<td width='24%'>IPP Date</td>
			<td width='1%'>:</td>
			<td width='25%'><?= date('d F Y', strtotime($dHeader['created_date'])); ?></td>
		</tr>
		<tr>
			<td>Customer Name</td>
			<td>:</td>
			<td><?= $dHeader['nm_customer']; ?></td>
			<td>Revision To</td>
			<td>:</td>
			<td><?= $dHeader['ref_ke'];?></td>
		</tr>
		<tr>
			<td>Project</td>
			<td>:</td>
			<td><?= $dHeader['project']; ?></td>
			<td>Revision By</td>
			<td>:</td>
			<td><?= ($dHeader['ref_ke'] == '0')?ucfirst(strtolower($dHeader['created_by'])):ucfirst(strtolower($dHeader['modified_by']));?></td>
		</tr>
		<tr>
			<td>Max Tolerance</td>
			<td>:</td>
			<td><?= floatval($dHeader['max_tol']); ?></td>
			<td>Min Tolerance</td>
			<td>:</td>
			<td><?= floatval($dHeader['min_tol']); ?></td>
		</tr>
	</table>
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='14'>SPECIFICATION LIST</th>
			</tr>
			<tr>
				<th width='5%'>No</th>
				<th width='7%'>Product</th>
				<th width='7%'>Resin Type</th>
				<th width='7%'>Liner</th>
				<th width='7%'>Preaseure</th>
				<th width='9%'>Stifness</th>
				<th width='10%'>Aplication</th>
				<th width='8%'>Vacum_Rate</th>
				<th width='8%'>Life Time</th>
				<th width='14%'>Reference Standard</th>
				<th width='7%'>Conductive</th>
				<th width='7%'>Fire Retardant</th>
				<th width='7%'>Color</th>
				<th width='7%'>Abrasive</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$no=0;
				while($data = mysqli_fetch_array($dResultDet)){
					$no++;
					$std_asme	= ($data['std_asme']=='Y')?'ASME , ':'';
					$std_ansi	= ($data['std_ansi']=='Y')?'ANSI , ':'';
					$std_astm	= ($data['std_astm']=='Y')?'ASTM , ':'';
					$std_awwa	= ($data['std_awwa']=='Y')?'AWWA , ':'';
					$std_bsi	= ($data['std_bsi']=='Y')?'BSI , ':'';
					$std_jis	= ($data['std_jis']=='Y')?'JIS , ':'';
					$std_sni	= ($data['std_sni']=='Y')?'SNI , ':'';
					$etc_1		= ($data['std_etc']=='Y' AND $data['etc_1'] != '')?$data['etc_1']."/":'';
					$etc_2		= ($data['std_etc']=='Y' AND $data['etc_2'] != '')?$data['etc_2']."/":'';
					$etc_3		= ($data['std_etc']=='Y' AND $data['etc_3'] != '')?$data['etc_3']."/":'';
					$etc_4		= ($data['std_etc']=='Y' AND $data['etc_4'] != '')?$data['etc_4']."/":'';
					
					?>
					<tr>
						<td align='center'><?= $no;?></td>
						<td align='center'><?= $data['product'];?></td>
						<td align='center'><?= $data['type_resin'];?></td>
						<td align='center'><?= $data['liner_thick'];?></td>
						<td align='center'><?= $data['pressure'];?> Bar</td>
						<td align='center'><?= $data['stifness'];?> Pa</td>
						<td align='center'><?= $data['aplikasi'];?></td>
						<td align='center'><?= $data['vacum_rate'];?></td>
						<td align='center'><?= $data['time_life'];?> Year</td>
						<td align='center'><?= $std_asme.$std_ansi.$std_astm.$std_awwa.$std_bsi.$std_jis.$std_sni.$etc_1.$etc_2.$etc_3.$etc_4; ?></td>
						
						<td align='center'>
							<table class="gridtable3" width='100%' border='0' cellpadding='0' cellspacing='0'>
								<tr>
									<td align='left' width='30%'>Liner</td>
									<td align='left' width='10%'>:</td>
									<td align='left' width='60%'><?= ($data['konduksi_liner'] == 'Y')?'<b>YES</b>':'NO';?></td>
								</tr>
								<tr>
									<td align='left'>Str</td>
									<td align='left'>:</td>
									<td align='left'><?= ($data['konduksi_structure'] == 'Y')?'<b>YES</b>':'NO';?></td>
								</tr>
								<tr>
									<td align='left'>Eks</td>
									<td align='left'>:</td>
									<td align='left'><?= ($data['konduksi_eksternal'] == 'Y')?'<b>YES</b>':'NO';?></td>
								</tr>
								<tr>
									<td align='left'>Tc</td>
									<td align='left'>:</td>
									<td align='left'><?= ($data['konduksi_topcoat'] == 'Y')?'<b>YES</b>':'NO';?></td>
								</tr>
							</table>
						</td>
						<td align='center'>
							<table class="gridtable3" width='100%' border='0' cellpadding='0' cellspacing='0'>
								<tr>
									<td align='left' width='30%'>Liner</td>
									<td align='left' width='10%'>:</td>
									<td align='left' width='60%'><?= ($data['tahan_api_liner'] == 'Y')?'<b>YES</b>':'NO';?></td>
								</tr>
								<tr>
									<td align='left'>Str</td>
									<td align='left'>:</td>
									<td align='left'><?= ($data['tahan_api_structure'] == 'Y')?'<b>YES</b>':'NO';?></td>
								</tr>
								<tr>
									<td align='left'>Eks</td>
									<td align='left'>:</td>
									<td align='left'><?= ($data['tahan_api_eksternal'] == 'Y')?'<b>YES</b>':'NO';?></td>
								</tr>
								<tr>
									<td align='left'>Tc</td>
									<td align='left'>:</td>
									<td align='left'><?= ($data['tahan_api_topcoat'] == 'Y')?'<b>YES</b>':'NO';?></td>
								</tr>
							</table>
						</td>
						<td align='center'>
							<table class="gridtable3" width='100%' border='0' cellpadding='0' cellspacing='0'>
								<tr>
									<td align='left' width='30%'>Liner</td>
									<td align='left' width='10%'>:</td>
									<td align='left' width='60%'><?= ($data['color'] == 'N')?'-':($data['color_liner'] == '')?'-':strtoupper($data['color_liner']);?></td>
								</tr>
								<tr>
									<td align='left'>Str</td>
									<td align='left'>:</td>
									<td align='left'><?= ($data['color'] == 'N')?'-':($data['color_structure'] == '')?'-':strtoupper($data['color_structure']);?></td>
								</tr>
								<tr>
									<td align='left'>Eks</td>
									<td align='left'>:</td>
									<td align='left'><?= ($data['color'] == 'N')?'-':($data['color_external'] == '')?'-':strtoupper($data['color_external']);?></td>
								</tr>
								<tr>
									<td align='left'>Tc</td>
									<td align='left'>:</td>
									<td align='left'><?= ($data['color'] == 'N')?'-':($data['color_topcoat'] == '')?'-':strtoupper($data['color_topcoat']);?></td>
								</tr>
							</table>
						</td>
						<td align='center'><?= ($data['abrasi'] == 'Y')?'<b>YES</b>':'NO';?></td>
					</tr>
					<?php
				}
			?>
		</tbody>
	</table>
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='6'>SPECIFICATION LIST</th>
			</tr>
			
			<tr>
				<th width='5%'>No</th>
				<th width='7%'>Product</th>
				<th width='22%'>Document</th>
				<th width='22%'>Certificate</th>
				<th width='22%'>Testing</th>
				<th width='22%'>Note</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$no2=0;
				while($data2 = mysqli_fetch_array($dResultDet2)){
					$no2++;
				?>
					<tr>
						<td align='center'><?= $no2;?></td>
						<td align='center'><?= $data2['product'];?></td>
						<td align='center'>
							<table class="gridtable3" width='100%' border='0' cellpadding='0' cellspacing='0'>
								<tr>
									<td align='left' width='26%'>Document 1</td>
									<td align='left' width='5%'>:</td>
									<td align='left' width='69%'><?= ($data2['document'] == 'N')?'-':($data2['document_1'] == '')?'-':strtoupper($data2['document_1']);?></td>
								</tr>
								<tr>
									<td align='left'>Document 2</td>
									<td align='left'>:</td>
									<td align='left' width='69%'><?= ($data2['document'] == 'N')?'-':($data2['document_2'] == '')?'-':strtoupper($data2['document_2']);?></td>
								</tr>
								<tr>
									<td align='left'>Document 3</td>
									<td align='left'>:</td>
									<td align='left' width='69%'><?= ($data2['document'] == 'N')?'-':($data2['document_3'] == '')?'-':strtoupper($data2['document_3']);?></td>
								</tr>
								<tr>
									<td align='left'>Document 4</td>
									<td align='left'>:</td>
									<td align='left' width='69%'><?= ($data2['document'] == 'N')?'-':($data2['document_4'] == '')?'-':strtoupper($data2['document_4']);?></td>
								</tr>
							</table>
						</td>
						<td align='center'>
							<table class="gridtable3" width='100%' border='0' cellpadding='0' cellspacing='0'>
								<tr>
									<td align='left' width='26%'>Certificate 1</td>
									<td align='left' width='5%'>:</td>
									<td align='left' width='69%'><?= ($data2['sertifikat'] == 'N')?'-':($data2['sertifikat_1'] == '')?'-':strtoupper($data2['sertifikat_1']);?></td>
								</tr>
								<tr>
									<td align='left'>Certificate 2</td>
									<td align='left'>:</td>
									<td align='left' width='69%'><?= ($data2['sertifikat'] == 'N')?'-':($data2['sertifikat_2'] == '')?'-':strtoupper($data2['sertifikat_2']);?></td>
								</tr>
								<tr>
									<td align='left'>Certificate 3</td>
									<td align='left'>:</td>
									<td align='left' width='69%'><?= ($data2['sertifikat'] == 'N')?'-':($data2['sertifikat_3'] == '')?'-':strtoupper($data2['sertifikat_3']);?></td>
								</tr>
								<tr>
									<td align='left'>Certificate 4</td>
									<td align='left'>:</td>
									<td align='left' width='69%'><?= ($data2['sertifikat'] == 'N')?'-':($data2['sertifikat_4'] == '')?'-':strtoupper($data2['sertifikat_4']);?></td>
								</tr>
							</table>
						</td>
						<td align='center'>
							<table class="gridtable3" width='100%' border='0' cellpadding='0' cellspacing='0'>
								<tr>
									<td align='left' width='21%'>Testing 1</td>
									<td align='left' width='5%'>:</td>
									<td align='left' width='69%'><?= ($data2['test'] == 'N')?'-':($data2['test_1'] == '')?'-':strtoupper($data2['test_1']);?></td>
								</tr>
								<tr>
									<td align='left'>Testing 2</td>
									<td align='left'>:</td>
									<td align='left' width='69%'><?= ($data2['test'] == 'N')?'-':($data2['test_2'] == '')?'-':strtoupper($data2['test_2']);?></td>
								</tr>
								<tr>
									<td align='left'>Testing 3</td>
									<td align='left'>:</td>
									<td align='left' width='69%'><?= ($data2['test'] == 'N')?'-':($data2['test_3'] == '')?'-':strtoupper($data2['test_3']);?></td>
								</tr>
								<tr>
									<td align='left'>Testing 4</td>
									<td align='left'>:</td>
									<td align='left' width='69%'><?= ($data2['test'] == 'N')?'-':($data2['test_4'] == '')?'-':strtoupper($data2['test_4']);?></td>
								</tr>
							</table>
						</td>
						<td><?= strtoupper($data2['note']);?></td>
					</tr>
				<?php
				}
			?>
		</tbody>
	</table>
	<br>
	<table class="gridtable2" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<td align='left' colspan='6'><b>SHIPPING DETAIL</b></td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td  width='24%'>Country</td>
				<td  width='1%'>:</td>
				<td  width='25%'><?= strtoupper($dHeaderShip['country_name']); ?></td>
				<td  width='24%'>Delivery Date</td>
				<td  width='1%'>:</td>
				<td  width='25%'><?= strtoupper(date('d F Y', strtotime($dHeaderShip['date_delivery']))); ?></td>
			</tr>
			<tr>
				<td>Shipping Method</td>
				<td>:</td>
				<td><?= strtoupper($dHeaderShip['metode_delivery']); ?></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td  width='24%'>Address</td>
				<td  width='1%'>:</td>
				<td  width='25%' colspan='4'><?= strtoupper($dHeaderShip['address_delivery']); ?></td>
			</tr>
			<tr>
				<td>Instalation</td>
				<td>:</td>
				<td><?= strtoupper($dHeaderShip['isntalasi_by']); ?></td>
				<td>Handling Equipment</td>
				<td>:</td>
				<td><?= strtoupper($dHeaderShip['alat_berat']); ?></td>
			</tr>
			
			<tr>
				<td>Truck</td>
				<td>:</td>
				<td><?= ($dHeaderShip['truck'] == null || $dHeaderShip['truck'] == '')?'-':$dHeaderShip['truck']; ?></td>
				<td>Vendor</td>
				<td>:</td>
				<td><?= ($dHeaderShip['vendor'] == null || $dHeaderShip['vendor'] == '')?'-':$dHeaderShip['vendor']; ?></td>
			</tr>
			
			<tr>
				<td>Qty</td>
				<td>:</td>
				<td><?= ($dHeaderShip['qty'] == null || $dHeaderShip['qty'] == '')?'-':$dHeaderShip['qty']; ?></td>
				<td>Packing</td>
				<td>:</td>
				<td><?= $dHeaderShip['packing']; ?></td>
			</tr>
			<tr>
				<td>Pipe Packing</td>
				<td>:</td>
				<td><?= $dHeaderShip['packing_pipa_qty']; ?></td>
				<td>Fitting Packing</td>
				<td>:</td>
				<td><?= $dHeaderShip['packing_dg_qty']; ?></td>
			</tr>
			<tr>
				<td>DG Packing</td>
				<td>:</td>
				<td><?= $dHeaderShip['packing_dg_qty']; ?></td>
				<td>Validity & Guarantee</td>
				<td>:</td>
				<td><?= $dHeaderShip['garansi']; ?> Year</td>
			</tr>
		</tbody>
	</table>
	<style type="text/css">
	@page {
		margin-top: 1cm;
		margin-left: 1.5cm;
		margin-right: 1cm;
		margin-bottom: 1cm;
	}
	p.foot1 {
		font-family: verdana,arial,sans-serif;
		font-size:10px;
	}
	.font{
		font-family: verdana,arial,sans-serif;
		font-size:14px;
	}
	.fontheader{
		font-family: verdana,arial,sans-serif;
		font-size:13px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size:9px; 
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable th {
		border-width: 1px;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable th.head {
		border-width: 1px;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable td {
		border-width: 1px;
		padding: 3px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable td.cols {
		border-width: 1px;
		padding: 3px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	table.gridtable3 {
		font-family: verdana,arial,sans-serif;
		font-size:9px; 
		color:#333333;
		border-width: 0px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable3 th {
		border-width: 1px;
		padding: 8px;
		border-style: none;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable3 th.head {
		border-width: 1px;
		padding: 8px;
		border-style: none;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable3 td {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable3 td.cols {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	
	table.gridtable2 {
		font-family: verdana,arial,sans-serif;
		font-size:12px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable2 th {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable2 th.head {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable2 td {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable2 td.cols {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	table.cooltabs {
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
	}
	table.cooltabs th.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
	}
	table.cooltabs td.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		padding: 5px;
	}
	#cooltabs {
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 800px;
		height: 20px;
	}
	#cooltabs2{
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 180px;
		height: 10px;
	}
	#space{
		padding: 3px;
		width: 180px;
		height: 1px;
	}
	#cooltabshead{
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 0 0;
		background: #dfdfdf;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	#cooltabschild{
		font-size:10px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 0 0 5px 5px;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	p {
		margin: 0 0 0 0;
	}
	p.pos_fixed {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 30px;
		left: 230px;
	}
	p.pos_fixed2 {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 589px;
		left: 230px;
	}
	.barcode {
		padding: 1.5mm;
		margin: 0;
		vertical-align: top;
		color: #000044;
	}
	.barcodecell {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: -10px;
		right: 10px;
	}
	.barcodecell2 {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: 548px;
		right: 10px;
	}
	p.barcs {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 38px;
		right: 115px;
	}
	p.barcs2 {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 591px;
		right: 115px;
	}
	p.pt {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 62px;
		left: 5px;
	}
	p.alamat {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 71px;
		left: 5px;
	}
	p.tlp {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 80px;
		left: 5px;
	}
	p.pt2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 596px;
		left: 5px;
	}
	p.alamat2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 605px;
		left: 5px;
	}
	p.tlp2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 614px;
		left: 5px;
	}
	#hrnew {
		border: 0;
		border-bottom: 1px dashed #ccc;
		background: #999;
	}
</style>

	
	<?php
	$footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px;'><i>Printed by : ".ucfirst(strtolower($printby)).", ".$today."</i></p>";
	$refX	=  $dHeader['ref_ke'];
	$html = ob_get_contents(); 
	// exit;
	ob_end_clean(); 
	// $mpdf->SetWatermarkText('ORI Group');
	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle($no_ipp);
	$mpdf->AddPage();
	$mpdf->SetFooter($footer);
	$mpdf->WriteHTML($html);		
	$mpdf->Output($no_ipp.'_revisi_ke_'.$refX.'.pdf' ,'I');
}

function PrintSPKSlong($Nama_APP, $kode_produksi, $koneksi, $printby, $kode_product, $product_to, $id_delivery, $id_milik){ 
	
	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);
	
	// print_r($KONN); exit;
	
	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);
	
	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	// include $sroot. "/".$Nama_APP."/application/libraries/PHPMailer/PHPMailerAutoload.php";
	include $sroot."/".$Nama_APP."/application/libraries/MPDF57/mpdf.php";
	$mpdf=new mPDF('utf-8','A4');
	// $mpdf=new mPDF('utf-8','A4-L');
	
	set_time_limit(0);
	ini_set('memory_limit','1024M');

	//Beginning Buffer to save PHP variables and HTML tags
	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('D, d-M-Y H:i:s');
	
	$qHeader2	= "	SELECT 
						a.*
					FROM 
						production_header a 
						LEFT JOIN production_detail b ON a.id_produksi=b.id_produksi						
					WHERE 
						a.id_produksi='".$kode_produksi."'
						AND b.id_delivery = '".$id_delivery."'
						LIMIT 1"; 

	$dResult2	= mysqli_query($conn, $qHeader2);
	$dHeader2	= mysqli_fetch_array($dResult2);
	
	$qHeader	= "SELECT a.*, b.* FROM bq_component_header a INNER JOIN bq_detail_header b ON a.id_milik = b.id 
						WHERE a.id_product='".$kode_product."' AND a.id_milik ='".$id_milik."' ";
	// echo $qHeader;
	$dResult	= mysqli_query($conn, $qHeader);
	$dHeader	= mysqli_fetch_array($dResult);
	
	$qIPP	= "SELECT a.* FROM production a WHERE a.no_ipp='".$dHeader2['no_ipp']."' ";
	// echo $qIPP;
	$dIPP	= mysqli_query($conn, $qIPP); 
	$dRIPP	= mysqli_fetch_array($dIPP);
	
	if($dHeader['id_category'] == 'pipe' OR $dHeader['id_category'] == 'pipe slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['length'])." x ".floatval($dHeader['thickness']);
	}
	elseif($dHeader['id_category'] == 'elbow mitter' OR $dHeader['id_category'] == 'elbow mould'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['thickness']).", ".$dHeader['type']." ".$dHeader['sudut'];
	}
	elseif($dHeader['id_category'] == 'reducer tee slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['diameter_2'])." x ".floatval($dHeader['thickness']);
	}
	elseif($dHeader['id_category'] == 'flange slongsong' OR $dHeader['id_category'] == 'equal tee slongsong'){
		$dim = floatval($dHeader['diameter_1'])." x ".floatval($dHeader['panjang_neck_1'])." x ".floatval($dHeader['design_neck_1']);
	}
	else{$dim = "belum di set";} 
	
	?>
	
	<table class="gridtable" border='1' width='100%' cellpadding='2'>
		<tr>
			<td width='70px' rowspan='3' style='padding:0px;'><img src='<?=$sroot;?><?=$Nama_APP;?>/assets/images/ori_logo.jpg' alt="" height='80' width='70' ></td>
			<td align='center'><b>PT  ORI POLYTEC COMPOSITE</b></td>
			<td width='15%'>Doc Number</td>
			<td width='15%'><?= $dHeader2['no_ipp']; ?></td>
		</tr>
		<tr>
			<td align='center' rowspan='2'><b><h2>DAILY PRODUCTION REPORT</h2></b></td>
			<td>Rev</td>
			<td></td>
		</tr>
		<tr>
			<td>Due Date</td>
			<td></td>
		</tr>
	</table>
	<table class="gridtable2" border='0' width='100%' >
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width='20%'>Production Date</td>
			<td width='1%'>:</td>
			<td width='29%'></td>
			<td width='20%'>SO Number</td>
			<td width='1%'>:</td>
			<td width='29%'><?= $dHeader2['so_number']; ?></td>
		</tr>
		<tr>
			<td>SPK Number</td>
			<td>:</td>
			<td><?= $dHeader['no_spk'];?></td>
			<td>Customer</td>
			<td>:</td>
			<td><?= $dRIPP['nm_customer']; ?></td>
		</tr>
		<tr>
			<td>Machine Number</td>
			<td>:</td>
			<td><?= strtoupper($dHeader2['nm_mesin']);?></td>
			<td>Spec Product</td>
			<td>:</td>
			<td><?= $dim;?></td>
		</tr>
		<tr>
			<td>Project</td>
			<td>:</td>
			<td><?= strtoupper($dRIPP['project']); ?></td>
			<td><?= ucwords($dHeader['parent_product']);?> To</td>
			<td>:</td>
			<td><?= $product_to." (".strtoupper(strtolower($dHeader['no_komponen'])).") of ".$dHeader['qty']." Component";?></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th width='13%'>Material</th>
				<th width='7%'>Number Layer</th>
				<th width='27%'>Material Type</th>
				<th width='10%'>Qty</th>
				<th width='15%'>Lot/Batch Num</th>
				<th width='10%'>Actual Type</th>
				<th width='8%'>Layer</th>
				<th width='8%'>Used</th>
			</tr>
			<tr>
				<th align='left' colspan='8'>LINER THIKNESS / CB</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='LINER THIKNESS / CB' AND id_material <> 'MTL-1903000' AND id_category <> 'TYP-0001' ";
		$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
		// echo $tDetailLiner; exit;
		while($valx = mysqli_fetch_array($dDetailLiner)){
			$dataL	= ($valx['layer'] == 0.00)?'-':(floatval($valx['layer']) == 0)?'-':floatval($valx['layer']);
			?>
			<tr>
				<td><?= $valx['nm_category'];?></td>
				<td align='center'><?= $dataL;?></td>
				<td><?= $valx['nm_material'];?></td>
				<td align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."' AND detail_name='LINER THIKNESS / CB' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
		$dDetailResin	= mysqli_query($conn, $detailResin);
		// echo $detailResin;
		while($valH = mysqli_fetch_array($dDetailResin)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		
		$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='LINER THIKNESS / CB' AND id_material <> 'MTL-1903000' AND id_category = 'TYP-0002' ";
		$dDetailPlus	= mysqli_query($conn, $detailPlus);
		// echo $detailPlus;
		while($valH = mysqli_fetch_array($dDetailPlus)){
			?>
			<tr>
				<td colspan='2'><?= $valH['nm_category'];?></td>
				<td><?= $valH['nm_material'];?></td>
				<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
				<td></td>
				<td colspan='2'></td>
				<td></td>
			</tr>
			<?php
		}
		?>
		
		</tbody>
		<?php
		if($dHeader['parent_product'] == 'flange mould' OR $dHeader['parent_product'] == 'flange slongsong'){
		?>
			<thead align='center'>
				<tr>
					<th align='left' colspan='8'>STRUKTUR NECK 1</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$tDetailLiner	= "SELECT nm_category, layer, nm_material, last_cost, jumlah, id_category, bw  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 1' AND id_material <> 'MTL-1903000' AND id_category <>'TYP-0001' ";
			// echo $tDetailLiner;
			$dDetailLiner	= mysqli_query($conn, $tDetailLiner);
			while($valx = mysqli_fetch_array($dDetailLiner)){
				$dataL	= ($valx['layer'] == 0.00)?'-':$valx['layer'];
				$SUn	= "";
				if($valx['id_category'] == 'TYP-0005'){
					$SUn	= " | ".floatval($valx['jumlah']);
				}
				?>
				<tr>
					<td><?= $valx['nm_category'];?></td>
					<td align='center'><?= floatval($dataL);?></td>
					<td><?= $valx['nm_material'];?></td>
					<td align='right'><?= number_format($valx['last_cost'], 3);?> Kg</td>
					<td></td>
					<td></td>
					<td></td> 
					<td></td>
				</tr>
				<?php
				if($valx['id_category'] == 'TYP-0005'){
				?>
				<tr>
					<td colspan='2'></td>
					<td><b>Jumlah Benang</b></td>
					<td align='right'><?= floatval($valx['jumlah'])?></td>
					<td colspan='3'><b>Actual Jumlah Benang</b></td>
					<td></td>
				</tr>
				<tr>
					<td colspan='2'></td>
					<td><b>Bandwidch</b></td>
					<td align='right'><?= floatval($valx['bw'])?></td>
					<td colspan='3'><b>Actual Bandwidch</b></td>
					<td></td>
				</tr>
				<?php
				}
			}
			
			$detailResin	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 1' AND id_category ='TYP-0001' ORDER BY id_detail DESC LIMIT 1 ";
			$dDetailResin	= mysqli_query($conn, $detailResin);
			// echo $detailResin;
			while($valH = mysqli_fetch_array($dDetailResin)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td></td>
					<td colspan='2'></td>
					<td></td>
				</tr>
				<?php
			}
			
			$detailPlus	= "SELECT nm_category, nm_material, last_cost  FROM bq_component_detail_plus WHERE id_product='".$kode_product."' AND id_milik='".$id_milik."'  AND detail_name='STRUKTUR NECK 1' AND id_material <> 'MTL-1903000' AND id_category = 'TYP-0002' ";
			$dDetailPlus	= mysqli_query($conn, $detailPlus);
			// echo $detailPlus;
			while($valH = mysqli_fetch_array($dDetailPlus)){
				?>
				<tr>
					<td colspan='2'><?= $valH['nm_category'];?></td>
					<td><?= $valH['nm_material'];?></td>
					<td align='right'><?= number_format($valH['last_cost'], 3);?> Kg</td>
					<td></td>
					<td colspan='2'></td>
					<td></td>
				</tr>
				<?php
			}
			?>
			</tbody>	
		<?php
		}
		?>
		<!-- END FLANGE MOULD -->
	</table>
	
	<br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='6'>THICKNESS</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><b>Thickness Est</b></td>
				<td align='center'><b><?= floatval($dHeader['est_neck_1']);?></b></td>
				<td><b>Thickness Act (Web)</b></td>
				<td></td>
				<td><b>Thickness Act (Dry)</b></td>
				<td width='80px'></td>
			</tr>
			<tr>
				<td><b>Status : Reject / Pass</b></td>
				<td colspan='2'><b>Inspector :</b></td>
				<td width='100px'><b>Signed : </b></td>
				<td colspan='2'><b>Inspection Date : </b></td>
			</tr>
			<tr>
				<td height='60px' colspan='6' style='vertical-align: top;'><b>Note :</b></td> 
			</tr>
		</tbody>
	</table>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th align='left' colspan='9'>MACHINE SETUP</th>
			</tr>
			<tr>
				<th><b>#</b></th>
				<th><b>Standard</b></th>
				<th><b>Actual</b></th>
				<th><b>#</b></th>
				<th><b>Standard</b></th>
				<th><b>Actual</b></th>
				<th><b>#</b></th>
				<th><b>Standard</b></th>
				<th><b>Actual</b></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td align='center'>RPM</td>
				<td></td>
				<td></td>
				<td align='center'>TENTION</td>
				<td></td>
				<td></td>
				<td align='center'>SUDUT ROOVING</td>
				<td></td>
				<td></td>
			</tr>
		</tbody>
	</table>
	<div id='space'></div>
	<table class="gridtable3" width='100%' border='0' cellpadding='2'>
		<tr>
			<td>Dibuat,</td>
			<td></td>
			<td>Diperiksa,</td>
			<td></td>
			<td>Diketahui,</td>
			<td></td>
		</tr>
		<tr>
			<td height='25px'></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td>Ka. Regu</td>
			<td></td>
			<td>SPV Produksi</td>
			<td></td>
			<td>Dept Head</td>
			<td></td>
		</tr>
	</table>
	<div id='space'></div>
	<style type="text/css">
	@page {
		margin-top: 1 cm;
		margin-left: 1 cm;
		margin-right: 1 cm;
		margin-bottom: 1 cm;
		margin-footer: 0 cm
	}
	p.foot1 {
		font-family: verdana,arial,sans-serif;
		font-size:10px;
	}
	.font{
		font-family: verdana,arial,sans-serif;
		font-size:14px;
	}
	.fontheader{
		font-family: verdana,arial,sans-serif;
		font-size:13px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable th {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable th.head {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable td {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable td.cols {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	
	table.gridtable2 {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable2 th {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable2 th.head {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable2 td {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable2 td.cols {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	
	table.gridtable3 {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
	}
	table.gridtable3 th {
		border-width: 1px;
		padding: 8px;
	}
	table.gridtable3 th.head {
		border-width: 1px;
		padding: 8px;
		color: #ffffff;
	}
	table.gridtable3 td {
		border-width: 1px;
		padding: 8px;
		background-color: #ffffff;
	}
	table.gridtable3 td.cols {
		border-width: 1px;
		padding: 8px;
		background-color: #ffffff;
	}
	
	table.cooltabs {
		font-size:12px;
		font-family: verdana,arial,sans-serif;
	}
	
	table.cooltabs th.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
	}
	table.cooltabs td.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		padding: 5px;
	}
	#cooltabs {
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 800px;
		height: 20px;
	}
	#cooltabs2{
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 180px;
		height: 10px;
	}
	#space{
		padding: 3px;
		width: 180px;
		height: 1px;
	}
	#cooltabshead{
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 0 0;
		background: #dfdfdf;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	#cooltabschild{
		font-size:10px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 0 0 5px 5px;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	p {
		margin: 0 0 0 0;
	}
	p.pos_fixed {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 30px;
		left: 230px;
	}
	p.pos_fixed2 {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 589px;
		left: 230px;
	}
	.barcode {
		padding: 1.5mm;
		margin: 0;
		vertical-align: top;
		color: #000044;
	}
	.barcodecell {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: -10px;
		right: 10px;
	}
	.barcodecell2 {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: 548px;
		right: 10px;
	}
	p.barcs {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 38px;
		right: 115px;
	}
	p.barcs2 {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 591px;
		right: 115px;
	}
	p.pt {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 62px;
		left: 5px;
	}
	p.alamat {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 71px;
		left: 5px;
	}
	p.tlp {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 80px;
		left: 5px;
	}
	p.pt2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 596px;
		left: 5px;
	}
	p.alamat2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 605px;
		left: 5px;
	}
	p.tlp2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 614px;
		left: 5px;
	}
	#hrnew {
		border: 0;
		border-bottom: 1px dashed #ccc;
		background: #999;
	}
</style>

	
	<?php
	
	$html = ob_get_contents(); 
	// $footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px; color:black;'><i>Printed by : ".ucfirst(strtolower($printby)).", ".$today."</i></p>";
	$footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px; color:black;'><i>Printed by : ".ucwords(strtolower($printby)).", ".$today." / ".$kode_product." / ".$dRIPP['no_ipp']." / Slongsong</i></p>";
	// exit;
	ob_end_clean(); 
	// $mpdf->SetWatermarkText('ORI Group');
	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('SPK Of Production');
	$mpdf->AddPage();
	$mpdf->SetFooter($footer);
	$mpdf->WriteHTML($html);		
	$mpdf->Output($kode_produksi.'_'.strtolower($dHeader['nm_product']).'_product_ke_'.$product_to.'.pdf' ,'I');

	//exit;
	//return $attachment;
}

?>