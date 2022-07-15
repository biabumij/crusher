<!DOCTYPE html>
<html>
	<head>
	<title>PERGERAKAN BAHAN JADI</title>
	
	<?php
	$search = array(
	'January',
	'February',
	'March',
	'April',
	'May',
	'June',
	'July',
	'August',
	'September',
	'October',
	'November',
	'December'
	);
	
	$replace = array(
	'Januari',
	'Februari',
	'Maret',
	'April',
	'Mei',
	'Juni',
	'Juli',
	'Agustus',
	'September',
	'Oktober',
	'November',
	'Desember'
	);
	
	$subject = "$filter_date";

	echo str_replace($search, $replace, $subject);

	?>
	
	<style type="text/css">
	table tr.table-judul{
		background-color: #e69500;
		font-weight: bold;
		font-size: 8px;
		color: black;
	}
		
	table tr.table-baris1{
		background-color: #F0F0F0;
		font-size: 8px;
	}

	table tr.table-baris1-bold{
		background-color: #F0F0F0;
		font-size: 8px;
		font-weight: bold;
	}
		
	table tr.table-baris2{
		font-size: 8px;
		background-color: #E8E8E8;
	}

	table tr.table-baris2-bold{
		font-size: 8px;
		background-color: #E8E8E8;
		font-weight: bold;
	}
		
	table tr.table-total{
		background-color: #cccccc;
		font-weight: bold;
		font-size: 8px;
		color: black;
	}
	</style>

	</head>
	<body>
		<br />
		<br />
		<table width="98%" cellpadding="3">
			<tr>
				<td align="center"  width="100%">
					<div style="display: block;font-weight: bold;font-size: 12px;">PERGERAKAN BAHAN JADI</div>
					<div style="display: block;font-weight: bold;font-size: 12px; text-transform: uppercase;">PERIODE : <?php echo str_replace($search, $replace, $subject);?></div>
				</td>
			</tr>
		</table>
		<br />
		<br />
		<br />
		<?php
		$data = array();
		
		$arr_date = $this->input->get('filter_date');
		$arr_filter_date = explode(' - ', $arr_date);
		$date1 = '';
		$date2 = '';

		if(count($arr_filter_date) == 2){
			$date1 	= date('Y-m-d',strtotime($arr_filter_date[0]));
			$date2 	= date('Y-m-d',strtotime($arr_filter_date[1]));
			$filter_date = date('d F Y',strtotime($arr_filter_date[0])).' - '.date('d F Y',strtotime($arr_filter_date[1]));
		}
		
		?>
		
		<table width="98%" border="0" cellpadding="3" border="0">
			
		<?php

		//LAPORAN BEBAN POKOK PRODUKSI

		//PERGERAKAN BAHAN BAKU
		
		//Opening Balance
		$date1_ago = date('2020-01-01');
		$date2_ago = date('Y-m-d', strtotime('-1 days', strtotime($date1)));
		$date3_ago = date('Y-m-d', strtotime('-1 months', strtotime($date1)));
		
		$pergerakan_bahan_baku_ago = $this->db->select('
		p.nama_produk, 
		prm.display_measure as satuan, 
		SUM(prm.display_volume) as volume, 
		SUM(prm.display_price) / SUM(prm.display_volume) as harga, 
		SUM(prm.display_price) as nilai')
		->from('pmm_receipt_material prm')
		->join('pmm_purchase_order po', 'prm.purchase_order_id = po.id','left')
		->join('produk p', 'prm.material_id = p.id','left')
		->where("prm.date_receipt between '$date1_ago' and '$date2_ago'")
		->where("prm.material_id = 15")
		->group_by('prm.material_id')
		->get()->row_array();
		
		$total_volume_pembelian_ago = $pergerakan_bahan_baku_ago['volume'];
		$total_volume_pembelian_akhir_ago  = $total_volume_pembelian_ago;
		
		$produksi_harian_ago = $this->db->select('sum(pphd.use) as used')
		->from('pmm_produksi_harian pph ')
		->join('pmm_produksi_harian_detail pphd','pphd.produksi_harian_id = pph.id','left')
		->where("(pph.date_prod between '$date1_ago' and '$date2_ago')")
		->where("pph.status = 'PUBLISH'")
		->get()->row_array();
		
		$total_volume_produksi_ago = $produksi_harian_ago['used'];
		$total_volume_produksi_akhir_ago = $total_volume_pembelian_akhir_ago - $total_volume_produksi_ago;

		$harga_satuan_ago = $this->db->select('
		p.nama_produk, 
		prm.display_measure as satuan, 
		SUM(prm.display_volume) as volume, 
		SUM(prm.display_price) / SUM(prm.display_volume) as harga, 
		SUM(prm.display_price) as nilai')
		->from('pmm_receipt_material prm')
		->join('pmm_purchase_order po', 'prm.purchase_order_id = po.id','left')
		->join('produk p', 'prm.material_id = p.id','left')
		->where("prm.date_receipt between '$date3_ago' and '$date2_ago'")
		->where("prm.material_id = 15")
		->group_by('prm.material_id')
		->get()->row_array();
	
		$nilai_harga_satuan_ago = ($harga_satuan_ago['volume']!=0)?($harga_satuan_ago['nilai'] / $harga_satuan_ago['volume'])  * 1:0;

		$harga_hpp_bahan_baku = $this->db->select('pp.date_hpp, pp.boulder, pp.bbm')
		->from('hpp_bahan_baku pp')
		->where("(pp.date_hpp between '$date3_ago' and '$date2_ago')")
		->get()->row_array();
		
		//file_put_contents("D:\\harga_hpp_bahan_baku.txt", $this->db->last_query());

		$total_volume_produksi_akhir_ago_fix = round($total_volume_produksi_akhir_ago,2);

		$volume_opening_balance = $total_volume_produksi_akhir_ago_fix;
		$harga_opening_balance = $harga_hpp_bahan_baku['boulder'];
		$nilai_opening_balance = $total_volume_produksi_akhir_ago_fix * $harga_opening_balance;

		$pergerakan_bahan_baku_ago_solar = $this->db->select('
		p.nama_produk, 
		prm.display_measure as satuan, 
		SUM(prm.display_volume) as volume, 
		SUM(prm.display_price) / SUM(prm.display_volume) as harga, 
		SUM(prm.display_price) as nilai')
		->from('pmm_receipt_material prm')
		->join('pmm_purchase_order po', 'prm.purchase_order_id = po.id','left')
		->join('produk p', 'prm.material_id = p.id','left')
		->where("prm.date_receipt between '$date1_ago' and '$date2_ago'")
		->where("prm.material_id = 13")
		->group_by('prm.material_id')
		->get()->row_array();

		$volume_pergerakan_bahan_baku_ago_solar = $pergerakan_bahan_baku_ago_solar['volume'];
		
		$stock_opname_solar_ago = $this->db->select('`prm`.`volume` as volume, `prm`.`total` as total')
		->from('pmm_remaining_materials_cat prm ')
		->where("prm.material_id = 13")
		->where("(prm.date < '$date1')")
		->where("status = 'PUBLISH'")
		->order_by('date','desc')->limit(1)
		->get()->row_array();

		$volume_stock_opname_solar_ago = $stock_opname_solar_ago['volume'];

		$volume_opening_balance_solar = $volume_stock_opname_solar_ago;
		$volume_opening_balance_solar_fix = round($volume_opening_balance_solar,2);

		$harga_opening_balance_solar = $harga_hpp_bahan_baku['bbm'];
		$nilai_opening_balance_solar = $volume_opening_balance_solar_fix * $harga_opening_balance_solar;

		//Now
		//Bahan Baku			
		$pergerakan_bahan_baku = $this->db->select('
		p.nama_produk, 
		prm.display_measure as satuan, 
		SUM(prm.display_volume) as volume, 
		(prm.display_price / prm.display_volume) as harga, 
		SUM(prm.display_price) as nilai')
		->from('pmm_receipt_material prm')
		->join('pmm_purchase_order po', 'prm.purchase_order_id = po.id','left')
		->join('produk p', 'prm.material_id = p.id','left')
		->where("prm.date_receipt between '$date1' and '$date2'")
		->where("prm.material_id = 15")
		->group_by('prm.material_id')
		->get()->row_array();
		
		$total_volume_pembelian = $pergerakan_bahan_baku['volume'];
		$total_nilai_pembelian =  $pergerakan_bahan_baku['nilai'];
		$total_harga_pembelian = ($total_volume_pembelian!=0)?$total_nilai_pembelian / $total_volume_pembelian * 1:0;

		$total_volume_pembelian_akhir  = $total_volume_produksi_akhir_ago + $total_volume_pembelian;
		$total_harga_pembelian_akhir = ($nilai_opening_balance + $total_nilai_pembelian) / $total_volume_pembelian_akhir;
		$total_nilai_pembelian_akhir =  $total_volume_pembelian_akhir * $total_harga_pembelian_akhir;			
		
		$produksi_harian = $this->db->select('sum(pphd.use) as used')
		->from('pmm_produksi_harian pph ')
		->join('pmm_produksi_harian_detail pphd','pphd.produksi_harian_id = pph.id','left')
		->where("(pph.date_prod between '$date1' and '$date2')")
		->where("pph.status = 'PUBLISH'")
		->get()->row_array();

		$akumulasi_bahan_baku = $this->db->select('pp.date_akumulasi, pp.total_nilai_keluar as total_nilai_keluar, pp.total_nilai_keluar_2 as total_nilai_keluar_2')
		->from('akumulasi_bahan_baku pp')
		->where("(pp.date_akumulasi between '$date1' and '$date2')")
		->get()->result_array();

		$total_akumulasi_bahan_baku = 0;
		$total_akumulasi_bahan_baku_2 = 0;

		foreach ($akumulasi_bahan_baku as $b){
			$total_akumulasi_bahan_baku += $b['total_nilai_keluar'];
			$total_akumulasi_bahan_baku_2 += $b['total_nilai_keluar_2'];
		}

		$akumulasi_nilai_bahan_baku = $total_akumulasi_bahan_baku;
		$akumulasi_nilai_bahan_baku_2 = $total_akumulasi_bahan_baku_2;
		
		$total_volume_produksi = $produksi_harian['used'];
		$total_nilai_produksi = $akumulasi_nilai_bahan_baku;
		$total_harga_produksi = ($total_volume_produksi!=0)?($total_nilai_produksi / $total_volume_produksi)  * 1:0;
		
		$total_volume_produksi_akhir = $total_volume_pembelian_akhir - $total_volume_produksi;
		$total_harga_produksi_akhir = $total_harga_produksi;
		$total_nilai_produksi_akhir = $total_volume_produksi_akhir * $total_harga_produksi_akhir;

		//BBM Solar
		$pergerakan_bahan_baku_solar = $this->db->select('
		p.nama_produk, 
		prm.display_measure as satuan, 
		SUM(prm.display_volume) as volume, 
		(prm.display_price / prm.display_volume) as harga, 
		SUM(prm.display_price) as nilai')
		->from('pmm_receipt_material prm')
		->join('pmm_purchase_order po', 'prm.purchase_order_id = po.id','left')
		->join('produk p', 'prm.material_id = p.id','left')
		->where("prm.date_receipt between '$date1' and '$date2'")
		->where("prm.material_id = 13")
		->group_by('prm.material_id')
		->get()->row_array();
		
		$total_volume_pembelian_solar = $pergerakan_bahan_baku_solar['volume'];
		$total_nilai_pembelian_solar =  $pergerakan_bahan_baku_solar['nilai'];
		$total_harga_pembelian_solar = ($total_volume_pembelian_solar!=0)?$total_nilai_pembelian_solar / $total_volume_pembelian_solar * 1:0;

		$total_volume_pembelian_akhir_solar  = $volume_opening_balance_solar + $total_volume_pembelian_solar;
		$total_harga_pembelian_akhir_solar = ($nilai_opening_balance_solar + $total_nilai_pembelian_solar) / $total_volume_pembelian_akhir_solar;
		$total_nilai_pembelian_akhir_solar =  $total_volume_pembelian_akhir_solar * $total_harga_pembelian_akhir_solar;

		$stock_opname_solar = $this->db->select('(prm.volume) as volume, (prm.total) as total')
		->from('pmm_remaining_materials_cat prm ')
		->where("prm.material_id = 13")
		->where("prm.date between '$date1' and '$date2'")
		->where("status = 'PUBLISH'")
		->order_by('date','desc')->limit(1)
		->get()->row_array();

		$volume_stock_opname_solar = $stock_opname_solar['volume'];
		
		$total_volume_produksi_akhir_solar = $volume_stock_opname_solar;
		$total_harga_produksi_akhir_solar = round($total_harga_pembelian_akhir_solar,0);
		$total_nilai_produksi_akhir_solar = $total_volume_produksi_akhir_solar * $total_harga_produksi_akhir_solar;

		$total_volume_produksi_solar = $total_volume_pembelian_akhir_solar - $total_volume_produksi_akhir_solar;
		$total_nilai_produksi_solar =  $akumulasi_nilai_bahan_baku_2;
		$total_harga_produksi_solar = ($total_volume_produksi_solar!=0)?($total_nilai_produksi_solar / $total_volume_produksi_solar)  * 1:0;
		//END PERGERAKAN BAHAN BAKU

		//PERALATAN & OPERASIONAL
		$abu_batu = $this->db->select('pph.no_prod, SUM(pphd.use) as jumlah_used, (SUM(pphd.use) * pk.presentase_a) / 100 AS jumlah_pemakaian_a,  (SUM(pphd.use) * pk.presentase_b) / 100 AS jumlah_pemakaian_b,  (SUM(pphd.use) * pk.presentase_c) / 100 AS jumlah_pemakaian_c,  (SUM(pphd.use) * pk.presentase_d) / 100 AS jumlah_pemakaian_d,  (SUM(pphd.use) * pk.presentase_e) / 100 AS jumlah_pemakaian_e, pk.produk_a, pk.produk_b, pk.produk_c, pk.produk_d, pk.produk_e, pk.measure_a, pk.measure_b, pk.measure_c, pk.measure_d, pk.measure_e, pk.presentase_a, pk.presentase_b, pk.presentase_c, pk.presentase_d, pk.presentase_e, (pk.presentase_a + pk.presentase_b + pk.presentase_c + pk.presentase_d + pk.presentase_e) as jumlah_presentase')
		->from('pmm_produksi_harian pph ')
		->join('pmm_produksi_harian_detail pphd','pphd.produksi_harian_id = pph.id','left')
		->join('pmm_kalibrasi pk', 'pphd.product_id = pk.id','left')	
		->where("(pph.date_prod between '$date1' and '$date2')")
		->where("pph.status = 'PUBLISH'")
		->get()->row_array();

		$total_abu_batu = 0;
		$nilai_abu_batu1 = 0;
		$nilai_abu_batu2 = 0;
		$nilai_abu_batu3 = 0;
		$nilai_abu_batu4 = 0;
		$nilai_abu_batu5 = 0;
		$nilai_abu_batu_all = 0;

		$total_abu_batu = $abu_batu['jumlah_pemakaian_a'] + $abu_batu['jumlah_pemakaian_b'] + $abu_batu['jumlah_pemakaian_c'] + $abu_batu['jumlah_pemakaian_d'] + $abu_batu['jumlah_pemakaian_e'];
		$nilai_abu_batu1 = $abu_batu['jumlah_pemakaian_a'] * $total_harga_produksi_akhir;
		$nilai_abu_batu2 = $abu_batu['jumlah_pemakaian_b'] * $total_harga_produksi_akhir;
		$nilai_abu_batu3 = $abu_batu['jumlah_pemakaian_c'] * $total_harga_produksi_akhir;
		$nilai_abu_batu4 = $abu_batu['jumlah_pemakaian_d'] * $total_harga_produksi_akhir;
		$nilai_abu_batu5 = $abu_batu['jumlah_pemakaian_e'] * $total_harga_produksi_akhir;
		$nilai_abu_batu_all = $nilai_abu_batu1 + $nilai_abu_batu2 + $nilai_abu_batu3 + $nilai_abu_batu4 + $nilai_abu_batu5;

		$nilai_abu_batu_total = $abu_batu['jumlah_used'] * $total_harga_pembelian;
		
		$stone_crusher_biaya = $this->db->select('sum(pdb.jumlah) as total')
		->from('pmm_biaya pb ')
		->join('pmm_detail_biaya pdb','pb.id = pdb.biaya_id','left')
		->where("pdb.akun = 101")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$stone_crusher_jurnal = $this->db->select('sum(pdb.debit) as total')
		->from('pmm_jurnal_umum pb ')
		->join('pmm_detail_jurnal pdb','pb.id = pdb.jurnal_id','left')
		->where("pdb.akun = 101")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$stone_crusher = $stone_crusher_biaya['total'] + $stone_crusher_jurnal['total'];
		
		$whell_loader_biaya = $this->db->select('sum(pdb.jumlah) as total')
		->from('pmm_biaya pb ')
		->join('pmm_detail_biaya pdb','pb.id = pdb.biaya_id','left')
		->where("pdb.akun = 104")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$whell_loader_jurnal = $this->db->select('sum(pdb.debit) as total')
		->from('pmm_jurnal_umum pb ')
		->join('pmm_detail_jurnal pdb','pb.id = pdb.jurnal_id','left')
		->where("pdb.akun = 104")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$whell_loader = $whell_loader_biaya['total'] + $whell_loader_jurnal['total'];
		
		$excavator = $this->db->select('sum(prm.display_price) as price')
		->from('pmm_receipt_material prm ')
		->where("prm.material_id = 18")
		->where("(prm.date_receipt between '$date1' and '$date2')")
		->get()->row_array();
		
		$genset_biaya = $this->db->select('sum(pdb.jumlah) as total')
		->from('pmm_biaya pb ')
		->join('pmm_detail_biaya pdb','pb.id = pdb.biaya_id','left')
		->where("pdb.akun = 197")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$genset_jurnal = $this->db->select('sum(pdb.debit) as total')
		->from('pmm_jurnal_umum pb ')
		->join('pmm_detail_jurnal pdb','pb.id = pdb.jurnal_id','left')
		->where("pdb.akun = 197")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$genset = $genset_biaya['total'] + $genset_jurnal['total'];
		
		$timbangan_biaya = $this->db->select('sum(pdb.jumlah) as total')
		->from('pmm_biaya pb ')
		->join('pmm_detail_biaya pdb','pb.id = pdb.biaya_id','left')
		->where("pdb.akun = 198")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$timbangan_biaya_jurnal = $this->db->select('sum(pdb.debit) as total')
		->from('pmm_jurnal_umum pb ')
		->join('pmm_detail_jurnal pdb','pb.id = pdb.jurnal_id','left')
		->where("pdb.akun = 198")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$timbangan = $timbangan_biaya['total'] + $timbangan_biaya_jurnal['total'];
		
		$tangki_solar_biaya = $this->db->select('sum(pdb.jumlah) as total')
		->from('pmm_biaya pb ')
		->join('pmm_detail_biaya pdb','pb.id = pdb.biaya_id','left')
		->where("pdb.akun = 207")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$tangki_solar_jurnal = $this->db->select('sum(pdb.debit) as total')
		->from('pmm_jurnal_umum pb ')
		->join('pmm_detail_jurnal pdb','pb.id = pdb.jurnal_id','left')
		->where("pdb.akun = 207")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$tangki_solar = $tangki_solar_biaya['total'] + $tangki_solar_jurnal['total'];		
		
		$total_biaya_peralatan = $stone_crusher + $whell_loader + $excavator['price'] + $genset + $timbangan + $tangki_solar;
		$hpp_peralatan = ($total_abu_batu!=0)?($total_biaya_peralatan / $total_abu_batu)  * 1:0;
		
		$gaji_upah_biaya = $this->db->select('sum(pdb.jumlah) as total')
		->from('pmm_biaya pb ')
		->join('pmm_detail_biaya pdb','pb.id = pdb.biaya_id','left')
		->where("pdb.akun in (199,200)")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$gaji_upah_jurnal = $this->db->select('sum(pdb.debit) as total')
		->from('pmm_jurnal_umum pb ')
		->join('pmm_detail_jurnal pdb','pb.id = pdb.jurnal_id','left')
		->where("pdb.akun in (199,200)")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$gaji_upah = $gaji_upah_biaya['total'] + $gaji_upah_jurnal['total'];
		
		$konsumsi_biaya = $this->db->select('sum(pdb.jumlah) as total')
		->from('pmm_biaya pb ')
		->join('pmm_detail_biaya pdb','pb.id = pdb.biaya_id','left')
		->where("pdb.akun = 201")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$konsumsi_jurnal = $this->db->select('sum(pdb.debit) as total')
		->from('pmm_jurnal_umum pb ')
		->join('pmm_detail_jurnal pdb','pb.id = pdb.jurnal_id','left')
		->where("pdb.akun = 201")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$konsumsi = $konsumsi_biaya['total'] + $konsumsi_jurnal['total'];
		
		$thr_bonus_biaya = $this->db->select('sum(pdb.jumlah) as total')
		->from('pmm_biaya pb ')
		->join('pmm_detail_biaya pdb','pb.id = pdb.biaya_id','left')
		->where("pdb.akun = 202")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$thr_bonus_jurnal = $this->db->select('sum(pdb.debit) as total')
		->from('pmm_jurnal_umum pb ')
		->join('pmm_detail_jurnal pdb','pb.id = pdb.jurnal_id','left')
		->where("pdb.akun = 202")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$thr_bonus = $thr_bonus_biaya['total'] + $thr_bonus_jurnal['total'];
		
		$perbaikan_biaya = $this->db->select('sum(pdb.jumlah) as total')
		->from('pmm_biaya pb ')
		->join('pmm_detail_biaya pdb','pb.id = pdb.biaya_id','left')
		->where("pdb.akun = 203")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$perbaikan_jurnal = $this->db->select('sum(pdb.debit) as total')
		->from('pmm_jurnal_umum pb ')
		->join('pmm_detail_jurnal pdb','pb.id = pdb.jurnal_id','left')
		->where("pdb.akun = 203")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();
		
		$perbaikan = $perbaikan_biaya['total'] + $perbaikan_jurnal['total'];

		$akomodasi_tamu_biaya = $this->db->select('sum(pdb.jumlah) as total')
		->from('pmm_biaya pb ')
		->join('pmm_detail_biaya pdb','pb.id = pdb.biaya_id','left')
		->where("pdb.akun = 204")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$akomodasi_tamu_jurnal = $this->db->select('sum(pdb.debit) as total')
		->from('pmm_jurnal_umum pb ')
		->join('pmm_detail_jurnal pdb','pb.id = pdb.jurnal_id','left')
		->where("pdb.akun = 204")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$akomodasi_tamu = $akomodasi_tamu_biaya['total'] + $akomodasi_tamu_jurnal['total'];
		
		$pengujian_biaya = $this->db->select('sum(pdb.jumlah) as total')
		->from('pmm_biaya pb ')
		->join('pmm_detail_biaya pdb','pb.id = pdb.biaya_id','left')
		->where("pdb.akun = 205")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$pengujian_jurnal = $this->db->select('sum(pdb.debit) as total')
		->from('pmm_jurnal_umum pb ')
		->join('pmm_detail_jurnal pdb','pb.id = pdb.jurnal_id','left')
		->where("pdb.akun = 205")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$pengujian = $pengujian_biaya['total'] + $pengujian_jurnal['total'];
		
		$listrik_internet_biaya = $this->db->select('sum(pdb.jumlah) as total')
		->from('pmm_biaya pb ')
		->join('pmm_detail_biaya pdb','pb.id = pdb.biaya_id','left')
		->where("pdb.akun = 206")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();

		$listrik_internet_jurnal = $this->db->select('sum(pdb.debit) as total')
		->from('pmm_jurnal_umum pb ')
		->join('pmm_detail_jurnal pdb','pb.id = pdb.jurnal_id','left')
		->where("pdb.akun = 206")
		->where("status = 'PAID'")
		->where("(tanggal_transaksi between '$date1' and '$date2')")
		->get()->row_array();
		
		$listrik_internet = $listrik_internet_biaya['total'] + $listrik_internet_jurnal['total'];

		$total_operasional = $gaji_upah + $konsumsi + $thr_bonus + $perbaikan + $akomodasi_tamu + $pengujian + $listrik_internet;
		$hpp_operasional = ($total_abu_batu!=0)?($total_operasional / $total_abu_batu)  * 1:0;
		$total_bpp = $total_nilai_produksi + $total_nilai_produksi_solar + $total_biaya_peralatan + $total_operasional;
		$harga_bpp = ($total_abu_batu!=0)?($total_bpp / $total_abu_batu)  * 1:0;
		
		$harga_pemakaian_a = 0;
		$harga_pemakaian_b = 0;
		$harga_pemakaian_c = 0;
		$harga_pemakaian_d = 0;
		$total_harga_pemakaian = 0;
		
		$harga_pemakaian_a = $harga_bpp * $abu_batu['jumlah_pemakaian_a'];
		$harga_pemakaian_b = $harga_bpp * $abu_batu['jumlah_pemakaian_b'];
		$harga_pemakaian_c = $harga_bpp * $abu_batu['jumlah_pemakaian_c'];
		$harga_pemakaian_d = $harga_bpp * $abu_batu['jumlah_pemakaian_d'];
		
		$total_harga_pemakaian = $harga_pemakaian_a + $harga_pemakaian_b + $harga_pemakaian_c + $harga_pemakaian_d;
		//END PERALATAN & OPERASIONAL
		//END LAPORAN BEBAN POKOK PRODUKSI
			
	        
		//Opening Balance Pergerakan Bahan Jadi
		$tanggal_awal = date('2020-01-01');
		$tanggal_opening_balance = date('Y-m-d', strtotime('-1 days', strtotime($date1)));

		$produksi_harian_bulan_lalu = $this->db->select('pph.date_prod, pph.no_prod, SUM(pphd.duration) as jumlah_duration, SUM(pphd.use) as jumlah_used, (SUM(pphd.use) * pk.presentase_a) / 100 AS jumlah_pemakaian_a,  (SUM(pphd.use) * pk.presentase_b) / 100 AS jumlah_pemakaian_b,  (SUM(pphd.use) * pk.presentase_c) / 100 AS jumlah_pemakaian_c,  (SUM(pphd.use) * pk.presentase_d) / 100 AS jumlah_pemakaian_d, pk.presentase_a, pk.presentase_b, pk.presentase_c, pk.presentase_d')
		->from('pmm_produksi_harian pph ')
		->join('pmm_produksi_harian_detail pphd','pphd.produksi_harian_id = pph.id','left')
		->join('pmm_kalibrasi pk', 'pphd.product_id = pk.id','left')
		->where("(pph.date_prod between '$tanggal_awal' and '$tanggal_opening_balance')")
		->where("pph.status = 'PUBLISH'")
		->get()->row_array();

		$volume_produksi_harian_abubatu_bulan_lalu = $produksi_harian_bulan_lalu['jumlah_pemakaian_a'];
		$volume_produksi_harian_batu0510_bulan_lalu = $produksi_harian_bulan_lalu['jumlah_pemakaian_b'];
		$volume_produksi_harian_batu1020_bulan_lalu = $produksi_harian_bulan_lalu['jumlah_pemakaian_c'];
		$volume_produksi_harian_batu2030_bulan_lalu = $produksi_harian_bulan_lalu['jumlah_pemakaian_d'];

		$penjualan_abubatu_bulan_lalu = $this->db->select('p.nama_produk, pp.convert_measure as satuan, SUM(pp.display_volume) as volume, (pp.display_price / pp.display_volume) as harga, SUM(pp.display_price) as nilai')
		->from('pmm_productions pp')
		->join('pmm_sales_po po', 'pp.salesPo_id = po.id','left')
		->join('produk p', 'pp.product_id = p.id','left')
		->where("pp.date_production between '$tanggal_awal' and '$tanggal_opening_balance'")
		->where("pp.product_id = 7")
		->where("po.status in ('OPEN','CLOSED')")
		->where("pp.status = 'PUBLISH'")
		->group_by('pp.product_id')
		->get()->row_array();

		$volume_penjualan_abubatu_bulan_lalu = $penjualan_abubatu_bulan_lalu['volume'];

		$penjualan_batu0510_bulan_lalu = $this->db->select('p.nama_produk, pp.convert_measure as satuan, SUM(pp.display_volume) as volume, (pp.display_price / pp.display_volume) as harga, SUM(pp.display_price) as nilai')
		->from('pmm_productions pp')
		->join('pmm_sales_po po', 'pp.salesPo_id = po.id','left')
		->join('produk p', 'pp.product_id = p.id','left')
		->where("pp.date_production between '$tanggal_awal' and '$tanggal_opening_balance'")
		->where("pp.product_id = 8")
		->where("po.status in ('OPEN','CLOSED')")
		->where("pp.status = 'PUBLISH'")
		->group_by('pp.product_id')
		->get()->row_array();

		$volume_penjualan_batu0510_bulan_lalu = $penjualan_batu0510_bulan_lalu['volume'];

		$penjualan_batu1020_bulan_lalu = $this->db->select('p.nama_produk, pp.convert_measure as satuan, SUM(pp.display_volume) as volume, (pp.display_price / pp.display_volume) as harga, SUM(pp.display_price) as nilai')
		->from('pmm_productions pp')
		->join('pmm_sales_po po', 'pp.salesPo_id = po.id','left')
		->join('produk p', 'pp.product_id = p.id','left')
		->where("pp.date_production between '$tanggal_awal' and '$tanggal_opening_balance'")
		->where("pp.product_id = 3")
		->where("po.status in ('OPEN','CLOSED')")
		->where("pp.status = 'PUBLISH'")
		->group_by('pp.product_id')
		->get()->row_array();

		$volume_penjualan_batu1020_bulan_lalu = $penjualan_batu1020_bulan_lalu['volume'];

		$penjualan_batu2030_bulan_lalu = $this->db->select('p.nama_produk, pp.convert_measure as satuan, SUM(pp.display_volume) as volume, (pp.display_price / pp.display_volume) as harga, SUM(pp.display_price) as nilai')
		->from('pmm_productions pp')
		->join('pmm_sales_po po', 'pp.salesPo_id = po.id','left')
		->join('produk p', 'pp.product_id = p.id','left')
		->where("pp.date_production between '$tanggal_awal' and '$tanggal_opening_balance'")
		->where("pp.product_id = 4")
		->where("po.status in ('OPEN','CLOSED')")
		->where("pp.status = 'PUBLISH'")
		->group_by('pp.product_id')
		->get()->row_array();

		$volume_penjualan_batu2030_bulan_lalu = $penjualan_batu2030_bulan_lalu['volume'];

		//Agregat Bulan Lalu
		$agregat_bulan_lalu = $this->db->select('p.nama_produk, pp.convert_measure as satuan, SUM(pp.display_volume) as volume, (pp.display_price / pp.display_volume) as harga, SUM(pp.display_price) as nilai, (SUM(pp.display_volume) * pa.presentase_a) / 100 as volume_agregat_a, (SUM(pp.display_volume) * pa.presentase_b) / 100 as volume_agregat_b, (SUM(pp.display_volume) * pa.presentase_c) / 100 as volume_agregat_c, (SUM(pp.display_volume) * pa.presentase_d) / 100 as volume_agregat_d')
		->from('pmm_productions pp')
		->join('pmm_sales_po po', 'pp.salesPo_id = po.id','left')
		->join('pmm_agregat pa', 'pp.komposisi_id = pa.id','left')
		->join('produk p', 'pp.product_id = p.id','left')
		->where("pp.date_production between '$tanggal_awal' and '$tanggal_opening_balance'")
		->where("pp.product_id = 24")
		->where("po.status in ('OPEN','CLOSED')")
		->where("pp.status = 'PUBLISH'")
		->group_by('pp.product_id')
		->get()->row_array();

		$volume_agregat_abubatu_bulan_lalu = $agregat_bulan_lalu['volume_agregat_a'];
		$volume_agregat_batu0510_bulan_lalu = $agregat_bulan_lalu['volume_agregat_b'];
		$volume_agregat_batu1020_bulan_lalu = $agregat_bulan_lalu['volume_agregat_c'];
		$volume_agregat_batu2030_bulan_lalu = $agregat_bulan_lalu['volume_agregat_d'];

		$agregat_bulan_lalu_2 = $this->db->select('p.nama_produk, pp.convert_measure as satuan, SUM(pp.display_volume) as volume, (pp.display_price / pp.display_volume) as harga, SUM(pp.display_price) as nilai, (SUM(pp.display_volume) * pa.presentase_a) / 100 as volume_agregat_a, (SUM(pp.display_volume) * pa.presentase_b) / 100 as volume_agregat_b, (SUM(pp.display_volume) * pa.presentase_c) / 100 as volume_agregat_c, (SUM(pp.display_volume) * pa.presentase_d) / 100 as volume_agregat_d')
		->from('pmm_productions pp')
		->join('pmm_sales_po po', 'pp.salesPo_id = po.id','left')
		->join('pmm_agregat pa', 'pp.komposisi_id = pa.id','left')
		->join('produk p', 'pp.product_id = p.id','left')
		->where("pp.date_production between '$tanggal_awal' and '$tanggal_opening_balance'")
		->where("pp.product_id = 14")
		->where("po.status in ('OPEN','CLOSED')")
		->where("pp.status = 'PUBLISH'")
		->group_by('pp.product_id')
		->get()->row_array();

		$volume_agregat_abubatu_bulan_lalu_2 = $agregat_bulan_lalu_2['volume_agregat_a'];
		$volume_agregat_batu0510_bulan_lalu_2 = $agregat_bulan_lalu_2['volume_agregat_b'];
		$volume_agregat_batu1020_bulan_lalu_2 = $agregat_bulan_lalu_2['volume_agregat_c'];
		$volume_agregat_batu2030_bulan_lalu_2 = $agregat_bulan_lalu_2['volume_agregat_d'];

		//Opening Balance
		$volume_opening_balance_abubatu_bulan_lalu = $volume_produksi_harian_abubatu_bulan_lalu - $volume_penjualan_abubatu_bulan_lalu - $volume_agregat_abubatu_bulan_lalu - $volume_agregat_abubatu_bulan_lalu_2;
		$volume_opening_balance_batu0510_bulan_lalu = $volume_produksi_harian_batu0510_bulan_lalu - $volume_penjualan_batu0510_bulan_lalu - $volume_agregat_batu0510_bulan_lalu - $volume_agregat_batu0510_bulan_lalu_2;
		$volume_opening_balance_batu1020_bulan_lalu = $volume_produksi_harian_batu1020_bulan_lalu - $volume_penjualan_batu1020_bulan_lalu - $volume_agregat_batu1020_bulan_lalu - $volume_agregat_batu1020_bulan_lalu_2;
		$volume_opening_balance_batu2030_bulan_lalu = $volume_produksi_harian_batu2030_bulan_lalu - $volume_penjualan_batu2030_bulan_lalu - $volume_agregat_batu2030_bulan_lalu - $volume_agregat_batu2030_bulan_lalu_2;

		//Rumus Harga Opening Balance

		//Dua Bulan Lalu
		$tanggal_opening_balance_2 = date('Y-m-d', strtotime('-1 months', strtotime($date1)));
		//Satu Bulan Lalu
		$tanggal_opening_balance_3 = date('Y-m-d', strtotime('-1 days', strtotime($date1)));
		
		$harga_hpp = $this->db->select('pp.date_hpp, pp.abubatu, pp.batu0510, pp.batu1020, pp.batu2030')
		->from('hpp pp')
		->where("(pp.date_hpp between '$tanggal_opening_balance_2' and '$tanggal_opening_balance_3')")
		->get()->row_array();
		
		//file_put_contents("D:\\harga_hpp.txt", $this->db->last_query());

		$harga_opening_balance_abubatu_bulan_lalu = $harga_hpp['abubatu'];
		$harga_opening_balance_batu0510_bulan_lalu =  $harga_hpp['batu0510'];
		$harga_opening_balance_batu1020_bulan_lalu =  $harga_hpp['batu1020'];
		$harga_opening_balance_batu2030_bulan_lalu =  $harga_hpp['batu2030'];

		$vol_1 = round($volume_opening_balance_abubatu_bulan_lalu,2);
		$vol_2 = round($volume_opening_balance_batu0510_bulan_lalu,2);
		$vol_3 = round($volume_opening_balance_batu1020_bulan_lalu,2);
		$vol_4 = round($volume_opening_balance_batu2030_bulan_lalu,2);

		$nilai_opening_balance_abubatu_bulan_lalu = $vol_1 * $harga_opening_balance_abubatu_bulan_lalu;
		$nilai_opening_balance_batu0510_bulan_lalu = $vol_2 * $harga_opening_balance_batu0510_bulan_lalu;
		$nilai_opening_balance_batu1020_bulan_lalu = $vol_3 * $harga_opening_balance_batu1020_bulan_lalu;
		$nilai_opening_balance_batu2030_bulan_lalu = $vol_4 * $harga_opening_balance_batu2030_bulan_lalu;

		//Now		
		$produksi_harian_bulan_ini = $this->db->select('pph.date_prod, pph.no_prod, SUM(pphd.duration) as jumlah_duration, SUM(pphd.use) as jumlah_used, (SUM(pphd.use) * pk.presentase_a) / 100 AS jumlah_pemakaian_a,  (SUM(pphd.use) * pk.presentase_b) / 100 AS jumlah_pemakaian_b,  (SUM(pphd.use) * pk.presentase_c) / 100 AS jumlah_pemakaian_c,  (SUM(pphd.use) * pk.presentase_d) / 100 AS jumlah_pemakaian_d, pk.presentase_a as presentase_a, pk.presentase_b as presentase_b, pk.presentase_c as presentase_c, pk.presentase_d as presentase_d')
		->from('pmm_produksi_harian pph ')
		->join('pmm_produksi_harian_detail pphd','pphd.produksi_harian_id = pph.id','left')
		->join('pmm_kalibrasi pk', 'pphd.product_id = pk.id','left')
		->where("(pph.date_prod between '$date1' and '$date2')")
		->where("pph.status = 'PUBLISH'")
		->get()->row_array();

		$volume_produksi_harian_abubatu_bulan_ini = $produksi_harian_bulan_ini['jumlah_pemakaian_a'];
		$volume_produksi_harian_batu0510_bulan_ini = $produksi_harian_bulan_ini['jumlah_pemakaian_b'];
		$volume_produksi_harian_batu1020_bulan_ini = $produksi_harian_bulan_ini['jumlah_pemakaian_c'];
		$volume_produksi_harian_batu2030_bulan_ini = $produksi_harian_bulan_ini['jumlah_pemakaian_d'];
		
		$tidak_ada_produksi = $this->db->select('pp.date_akumulasi, pp.tidak_ada_produksi as total')
		->from('akumulasi_biaya pp')
		->where("(pp.date_akumulasi between '$date1' and '$date2')")
		->get()->row_array();

		$harga_produksi_harian_abubatu_bulan_ini = $harga_bpp;
		$harga_produksi_harian_batu0510_bulan_ini = $harga_bpp;
		$harga_produksi_harian_batu1020_bulan_ini = $harga_bpp;
		$harga_produksi_harian_batu2030_bulan_ini = $harga_bpp;

		$nilai_produksi_harian_abubatu_bulan_ini = $volume_produksi_harian_abubatu_bulan_ini * $harga_produksi_harian_abubatu_bulan_ini;
		$nilai_produksi_harian_batu0510_bulan_ini = $volume_produksi_harian_batu0510_bulan_ini * $harga_produksi_harian_abubatu_bulan_ini;
		$nilai_produksi_harian_batu1020_bulan_ini = $volume_produksi_harian_batu1020_bulan_ini * $harga_produksi_harian_abubatu_bulan_ini;
		$nilai_produksi_harian_batu2030_bulan_ini = $volume_produksi_harian_batu2030_bulan_ini * $harga_produksi_harian_abubatu_bulan_ini;

		$volume_akhir_produksi_harian_abubatu_bulan_ini = $volume_opening_balance_abubatu_bulan_lalu + $volume_produksi_harian_abubatu_bulan_ini;
		$harga_akhir_produksi_harian_abubatu_bulan_ini = ($nilai_opening_balance_abubatu_bulan_lalu + $nilai_produksi_harian_abubatu_bulan_ini) / $volume_akhir_produksi_harian_abubatu_bulan_ini;
		$nilai_akhir_produksi_harian_abubatu_bulan_ini = $volume_akhir_produksi_harian_abubatu_bulan_ini * $harga_akhir_produksi_harian_abubatu_bulan_ini;

		$volume_akhir_produksi_harian_batu0510_bulan_ini = $volume_opening_balance_batu0510_bulan_lalu + $volume_produksi_harian_batu0510_bulan_ini;
		$harga_akhir_produksi_harian_batu0510_bulan_ini = ($nilai_opening_balance_batu0510_bulan_lalu + $nilai_produksi_harian_batu0510_bulan_ini) / $volume_akhir_produksi_harian_batu0510_bulan_ini;
		$nilai_akhir_produksi_harian_batu0510_bulan_ini = $volume_akhir_produksi_harian_batu0510_bulan_ini * $harga_akhir_produksi_harian_batu0510_bulan_ini;

		$volume_akhir_produksi_harian_batu1020_bulan_ini = $volume_opening_balance_batu1020_bulan_lalu + $volume_produksi_harian_batu1020_bulan_ini;
		$harga_akhir_produksi_harian_batu1020_bulan_ini = ($nilai_opening_balance_batu1020_bulan_lalu + $nilai_produksi_harian_batu1020_bulan_ini) / $volume_akhir_produksi_harian_batu1020_bulan_ini;
		$nilai_akhir_produksi_harian_batu1020_bulan_ini = $volume_akhir_produksi_harian_batu1020_bulan_ini * $harga_akhir_produksi_harian_batu1020_bulan_ini;

		$volume_akhir_produksi_harian_batu2030_bulan_ini = $volume_opening_balance_batu2030_bulan_lalu + $volume_produksi_harian_batu2030_bulan_ini;
		$harga_akhir_produksi_harian_batu2030_bulan_ini = ($nilai_opening_balance_batu2030_bulan_lalu + $nilai_produksi_harian_batu2030_bulan_ini) / $volume_akhir_produksi_harian_batu2030_bulan_ini;
		$nilai_akhir_produksi_harian_batu2030_bulan_ini = $volume_akhir_produksi_harian_batu2030_bulan_ini * $harga_akhir_produksi_harian_batu2030_bulan_ini;
	
		//Abu Batu
		$penjualan_abubatu_bulan_ini = $this->db->select('p.nama_produk, pp.convert_measure as satuan, SUM(pp.display_volume) as volume, (pp.display_price / pp.display_volume) as harga, SUM(pp.display_price) as nilai')
		->from('pmm_productions pp')
		->join('pmm_sales_po po', 'pp.salesPo_id = po.id','left')
		->join('produk p', 'pp.product_id = p.id','left')
		->where("pp.date_production between '$date1' and '$date2'")
		->where("pp.product_id = 7")
		->where("po.status in ('OPEN','CLOSED')")
		->where("pp.status = 'PUBLISH'")
		->group_by('pp.product_id')
		->get()->row_array();

		$volume_penjualan_abubatu_bulan_ini = $penjualan_abubatu_bulan_ini['volume'];
		$harga_penjualan_abubatu_bulan_ini = round($harga_akhir_produksi_harian_abubatu_bulan_ini,0);
		$nilai_penjualan_abubatu_bulan_ini = $volume_penjualan_abubatu_bulan_ini * $harga_penjualan_abubatu_bulan_ini;

		$volume_akhir_penjualan_abubatu_bulan_ini = round($volume_akhir_produksi_harian_abubatu_bulan_ini - $volume_penjualan_abubatu_bulan_ini,2);
		$harga_akhir_penjualan_abubatu_bulan_ini = $harga_penjualan_abubatu_bulan_ini;
		$nilai_akhir_penjualan_abubatu_bulan_ini = $volume_akhir_penjualan_abubatu_bulan_ini * $harga_akhir_penjualan_abubatu_bulan_ini;

		//Batu 0,5 - 10
		$penjualan_batu0510_bulan_ini = $this->db->select('p.nama_produk, pp.convert_measure as satuan, SUM(pp.display_volume) as volume, (pp.display_price / pp.display_volume) as harga, SUM(pp.display_price) as nilai')
		->from('pmm_productions pp')
		->join('pmm_sales_po po', 'pp.salesPo_id = po.id','left')
		->join('produk p', 'pp.product_id = p.id','left')
		->where("pp.date_production between '$date1' and '$date2'")
		->where("pp.product_id = 8")
		->where("po.status in ('OPEN','CLOSED')")
		->where("pp.status = 'PUBLISH'")
		->group_by('pp.product_id')
		->get()->row_array();

		$volume_penjualan_batu0510_bulan_ini = $penjualan_batu0510_bulan_ini['volume'];
		$harga_penjualan_batu0510_bulan_ini = round($harga_akhir_produksi_harian_batu0510_bulan_ini,0);
		$nilai_penjualan_batu0510_bulan_ini = $volume_penjualan_batu0510_bulan_ini * $harga_penjualan_batu0510_bulan_ini;

		$volume_akhir_penjualan_batu0510_bulan_ini = round($volume_akhir_produksi_harian_batu0510_bulan_ini - $volume_penjualan_batu0510_bulan_ini,2);
		$harga_akhir_penjualan_batu0510_bulan_ini =  $harga_penjualan_batu0510_bulan_ini;
		$nilai_akhir_penjualan_batu0510_bulan_ini = $volume_akhir_penjualan_batu0510_bulan_ini * $harga_akhir_penjualan_batu0510_bulan_ini;

		//Batu 10 - 20
		$penjualan_batu1020_bulan_ini = $this->db->select('p.nama_produk, pp.convert_measure as satuan, SUM(pp.display_volume) as volume, (pp.display_price / pp.display_volume) as harga, SUM(pp.display_price) as nilai')
		->from('pmm_productions pp')
		->join('pmm_sales_po po', 'pp.salesPo_id = po.id','left')
		->join('produk p', 'pp.product_id = p.id','left')
		->where("pp.date_production between '$date1' and '$date2'")
		->where("pp.product_id = 3")
		->where("po.status in ('OPEN','CLOSED')")
		->where("pp.status = 'PUBLISH'")
		->group_by('pp.product_id')
		->get()->row_array();
		
		//file_put_contents("D:\\penjualan_batu0510_bulan_ini.txt", $this->db->last_query());

		$volume_penjualan_batu1020_bulan_ini = $penjualan_batu1020_bulan_ini['volume'];
		$harga_penjualan_batu1020_bulan_ini = round($harga_akhir_produksi_harian_batu1020_bulan_ini,0);
		$nilai_penjualan_batu1020_bulan_ini = $volume_penjualan_batu1020_bulan_ini * $harga_penjualan_batu1020_bulan_ini;

		$volume_akhir_penjualan_batu1020_bulan_ini = round($volume_akhir_produksi_harian_batu1020_bulan_ini - $volume_penjualan_batu1020_bulan_ini,2);
		$harga_akhir_penjualan_batu1020_bulan_ini = $harga_penjualan_batu1020_bulan_ini;
		$nilai_akhir_penjualan_batu1020_bulan_ini = $volume_akhir_penjualan_batu1020_bulan_ini * $harga_akhir_penjualan_batu1020_bulan_ini;

		//Batu 20 - 30
		$penjualan_batu2030_bulan_ini = $this->db->select('p.nama_produk, pp.convert_measure as satuan, SUM(pp.display_volume) as volume, (pp.display_price / pp.display_volume) as harga, SUM(pp.display_price) as nilai')
		->from('pmm_productions pp')
		->join('pmm_sales_po po', 'pp.salesPo_id = po.id','left')
		->join('produk p', 'pp.product_id = p.id','left')
		->where("pp.date_production between '$date1' and '$date2'")
		->where("pp.product_id = 4")
		->where("po.status in ('OPEN','CLOSED')")
		->where("pp.status = 'PUBLISH'")
		->group_by('pp.product_id')
		->get()->row_array();

		$volume_penjualan_batu2030_bulan_ini = $penjualan_batu2030_bulan_ini['volume'];
		$harga_penjualan_batu2030_bulan_ini = round($harga_akhir_produksi_harian_batu2030_bulan_ini,0);
		$nilai_penjualan_batu2030_bulan_ini = $volume_penjualan_batu2030_bulan_ini * $harga_penjualan_batu2030_bulan_ini;

		$volume_akhir_penjualan_batu2030_bulan_ini = round($volume_akhir_produksi_harian_batu2030_bulan_ini - $volume_penjualan_batu2030_bulan_ini,2);
		$harga_akhir_penjualan_batu2030_bulan_ini = $harga_penjualan_batu2030_bulan_ini;
		$nilai_akhir_penjualan_batu2030_bulan_ini = $volume_akhir_penjualan_batu2030_bulan_ini * $harga_akhir_penjualan_batu2030_bulan_ini;
		
		//Agregat
		$agregat_bulan_ini = $this->db->select('p.nama_produk, pp.convert_measure as satuan, SUM(pp.display_volume) as volume, (pp.display_price / pp.display_volume) as harga, SUM(pp.display_price) as nilai, (SUM(pp.display_volume) * pa.presentase_a) / 100 as volume_agregat_a, (SUM(pp.display_volume) * pa.presentase_b) / 100 as volume_agregat_b, (SUM(pp.display_volume) * pa.presentase_c) / 100 as volume_agregat_c, (SUM(pp.display_volume) * pa.presentase_d) / 100 as volume_agregat_d')
		->from('pmm_productions pp')
		->join('pmm_sales_po po', 'pp.salesPo_id = po.id','left')
		->join('pmm_agregat pa', 'pp.komposisi_id = pa.id','left')
		->join('produk p', 'pp.product_id = p.id','left')
		->where("pp.date_production between '$date1' and '$date2'")
		->where("pp.product_id = 24")
		->where("po.status in ('OPEN','CLOSED')")
		->where("pp.status = 'PUBLISH'")
		->group_by('pp.product_id')
		->get()->row_array();

		$volume_agregat_abubatu_bulan_ini = round($agregat_bulan_ini['volume_agregat_a'],2);
		$volume_agregat_batu0510_bulan_ini = round($agregat_bulan_ini['volume_agregat_b'],2);
		$volume_agregat_batu1020_bulan_ini = round($agregat_bulan_ini['volume_agregat_c'],2);
		$volume_agregat_batu2030_bulan_ini = round($agregat_bulan_ini['volume_agregat_d'],2);

		$harga_agregat_abubatu_bulan_ini = $harga_akhir_penjualan_abubatu_bulan_ini;
		$harga_agregat_batu0510_bulan_ini = $harga_akhir_penjualan_batu0510_bulan_ini;
		$harga_agregat_batu1020_bulan_ini = $harga_akhir_penjualan_batu1020_bulan_ini;
		$harga_agregat_batu2030_bulan_ini = $harga_akhir_penjualan_batu2030_bulan_ini;

		$nilai_agregat_abubatu_bulan_ini = $volume_agregat_abubatu_bulan_ini * $harga_agregat_abubatu_bulan_ini;
		$nilai_agregat_batu0510_bulan_ini = $volume_agregat_batu0510_bulan_ini * $harga_agregat_batu0510_bulan_ini;
		$nilai_agregat_batu1020_bulan_ini = $volume_agregat_batu1020_bulan_ini * $harga_agregat_batu1020_bulan_ini;
		$nilai_agregat_batu2030_bulan_ini = $volume_agregat_batu2030_bulan_ini * $harga_agregat_batu2030_bulan_ini;

		$volume_akhir_agregat_abubatu_bulan_ini = round($volume_akhir_penjualan_abubatu_bulan_ini - $volume_agregat_abubatu_bulan_ini,2);
		$volume_akhir_agregat_batu0510_bulan_ini = round($volume_akhir_penjualan_batu0510_bulan_ini - $volume_agregat_batu0510_bulan_ini,2);
		$volume_akhir_agregat_batu1020_bulan_ini = round($volume_akhir_penjualan_batu1020_bulan_ini - $volume_agregat_batu1020_bulan_ini,2);
		$volume_akhir_agregat_batu2030_bulan_ini = round($volume_akhir_penjualan_batu2030_bulan_ini - $volume_agregat_batu2030_bulan_ini,2);

		$harga_akhir_agregat_abubatu_bulan_ini = $harga_agregat_abubatu_bulan_ini;
		$harga_akhir_agregat_batu0510_bulan_ini = $harga_agregat_batu0510_bulan_ini;
		$harga_akhir_agregat_batu1020_bulan_ini = $harga_agregat_batu1020_bulan_ini;
		$harga_akhir_agregat_batu2030_bulan_ini = $harga_agregat_batu2030_bulan_ini;

		$nilai_akhir_agregat_abubatu_bulan_ini = $volume_akhir_agregat_abubatu_bulan_ini * $harga_akhir_agregat_abubatu_bulan_ini;
		$nilai_akhir_agregat_batu0510_bulan_ini = $volume_akhir_agregat_batu0510_bulan_ini * $harga_akhir_agregat_batu0510_bulan_ini;
		$nilai_akhir_agregat_batu1020_bulan_ini = $volume_akhir_agregat_batu1020_bulan_ini * $harga_akhir_agregat_batu1020_bulan_ini;
		$nilai_akhir_agregat_batu2030_bulan_ini = $volume_akhir_agregat_batu2030_bulan_ini * $harga_akhir_agregat_batu2030_bulan_ini;

		$agregat_bulan_ini_2 = $this->db->select('p.nama_produk, pp.convert_measure as satuan, SUM(pp.display_volume) as volume, (pp.display_price / pp.display_volume) as harga, SUM(pp.display_price) as nilai, (SUM(pp.display_volume) * pa.presentase_a) / 100 as volume_agregat_a, (SUM(pp.display_volume) * pa.presentase_b) / 100 as volume_agregat_b, (SUM(pp.display_volume) * pa.presentase_c) / 100 as volume_agregat_c, (SUM(pp.display_volume) * pa.presentase_d) / 100 as volume_agregat_d')
		->from('pmm_productions pp')
		->join('pmm_sales_po po', 'pp.salesPo_id = po.id','left')
		->join('pmm_agregat pa', 'pp.komposisi_id = pa.id','left')
		->join('produk p', 'pp.product_id = p.id','left')
		->where("pp.date_production between '$date1' and '$date2'")
		->where("pp.product_id = 14")
		->where("po.status in ('OPEN','CLOSED')")
		->where("pp.status = 'PUBLISH'")
		->group_by('pp.product_id')
		->get()->row_array();

		$volume_agregat_abubatu_bulan_ini_2 = round($agregat_bulan_ini_2['volume_agregat_a'],2);
		$volume_agregat_batu0510_bulan_ini_2 = round($agregat_bulan_ini_2['volume_agregat_b'],2);
		$volume_agregat_batu1020_bulan_ini_2 = round($agregat_bulan_ini_2['volume_agregat_c'],2);
		$volume_agregat_batu2030_bulan_ini_2 = round($agregat_bulan_ini_2['volume_agregat_d'],2);

		$harga_agregat_abubatu_bulan_ini_2 = $harga_agregat_abubatu_bulan_ini;
		$harga_agregat_batu0510_bulan_ini_2 = $harga_agregat_batu0510_bulan_ini;
		$harga_agregat_batu1020_bulan_ini_2 = $harga_agregat_batu1020_bulan_ini;
		$harga_agregat_batu2030_bulan_ini_2 = $harga_agregat_batu2030_bulan_ini;

		$nilai_agregat_abubatu_bulan_ini_2 = $volume_agregat_abubatu_bulan_ini_2 * $harga_agregat_abubatu_bulan_ini_2;
		$nilai_agregat_batu0510_bulan_ini_2 = $volume_agregat_batu0510_bulan_ini_2 * $harga_agregat_batu0510_bulan_ini_2;
		$nilai_agregat_batu1020_bulan_ini_2 = $volume_agregat_batu1020_bulan_ini_2 * $harga_agregat_batu1020_bulan_ini_2;
		$nilai_agregat_batu2030_bulan_ini_2 = $volume_agregat_batu2030_bulan_ini_2 * $harga_agregat_batu2030_bulan_ini_2;

		$volume_akhir_agregat_abubatu_bulan_ini_2 = round($volume_akhir_agregat_abubatu_bulan_ini - $volume_agregat_abubatu_bulan_ini_2,2);
		$volume_akhir_agregat_batu0510_bulan_ini_2 = round($volume_akhir_agregat_batu0510_bulan_ini - $volume_agregat_batu0510_bulan_ini_2,2);
		$volume_akhir_agregat_batu1020_bulan_ini_2 = round($volume_akhir_agregat_batu1020_bulan_ini - $volume_agregat_batu1020_bulan_ini_2,2);
		$volume_akhir_agregat_batu2030_bulan_ini_2 = round($volume_akhir_agregat_batu2030_bulan_ini - $volume_agregat_batu2030_bulan_ini_2,2);

		$harga_akhir_agregat_abubatu_bulan_ini_2 = $harga_agregat_abubatu_bulan_ini_2;
		$harga_akhir_agregat_batu0510_bulan_ini_2 = $harga_agregat_batu0510_bulan_ini_2;
		$harga_akhir_agregat_batu1020_bulan_ini_2 = $harga_agregat_batu1020_bulan_ini_2;
		$harga_akhir_agregat_batu2030_bulan_ini_2 = $harga_agregat_batu2030_bulan_ini_2;

		$nilai_akhir_agregat_abubatu_bulan_ini_2 = $volume_akhir_agregat_abubatu_bulan_ini_2 * $harga_akhir_agregat_abubatu_bulan_ini_2;
		$nilai_akhir_agregat_batu0510_bulan_ini_2 = $volume_akhir_agregat_batu0510_bulan_ini_2 * $harga_akhir_agregat_batu0510_bulan_ini_2;
		$nilai_akhir_agregat_batu1020_bulan_ini_2 = $volume_akhir_agregat_batu1020_bulan_ini_2 * $harga_akhir_agregat_batu1020_bulan_ini_2;
		$nilai_akhir_agregat_batu2030_bulan_ini_2 = $volume_akhir_agregat_batu2030_bulan_ini_2 * $harga_akhir_agregat_batu2030_bulan_ini_2;

		//TOTAL BAHAN BAKU
		$nilai_opening_bahan_jadi = $nilai_opening_balance_abubatu_bulan_lalu + $nilai_opening_balance_batu0510_bulan_lalu + $nilai_opening_balance_batu1020_bulan_lalu + $nilai_opening_balance_batu2030_bulan_lalu;

		$volume_penjualan_abubatu = $volume_penjualan_abubatu_bulan_ini + $volume_agregat_abubatu_bulan_ini + $volume_agregat_abubatu_bulan_ini_2;
		$nilai_penjualan_abubatu = $nilai_penjualan_abubatu_bulan_ini + $nilai_agregat_abubatu_bulan_ini + $nilai_agregat_abubatu_bulan_ini_2;
		$harga_penjualan_abubatu = ($volume_penjualan_abubatu!=0)?($nilai_penjualan_abubatu / $volume_penjualan_abubatu)  * 1:0;

		$volume_penjualan_batu0510 = $volume_penjualan_batu0510_bulan_ini + $volume_agregat_batu0510_bulan_ini + $volume_agregat_batu0510_bulan_ini_2;
		$nilai_penjualan_batu0510 = $nilai_penjualan_batu0510_bulan_ini + $nilai_agregat_batu0510_bulan_ini + $nilai_agregat_batu0510_bulan_ini_2;
		$harga_penjualan_batu0510 = ($volume_penjualan_batu0510!=0)?($nilai_penjualan_batu0510 / $volume_penjualan_batu0510)  * 1:0;

		$volume_penjualan_batu1020 = $volume_penjualan_batu1020_bulan_ini + $volume_agregat_batu1020_bulan_ini + $volume_agregat_batu1020_bulan_ini_2;
		$nilai_penjualan_batu1020 = $nilai_penjualan_batu1020_bulan_ini + $nilai_agregat_batu1020_bulan_ini + $nilai_agregat_batu1020_bulan_ini_2;
		$harga_penjualan_batu1020 = ($volume_penjualan_batu1020!=0)?($nilai_penjualan_batu1020 / $volume_penjualan_batu1020)  * 1:0;

		$volume_penjualan_batu2030 = $volume_penjualan_batu2030_bulan_ini + $volume_agregat_batu2030_bulan_ini + $volume_agregat_batu2030_bulan_ini_2;
		$nilai_penjualan_batu2030 = $nilai_penjualan_batu2030_bulan_ini + $nilai_agregat_batu2030_bulan_ini + $nilai_agregat_batu2030_bulan_ini_2;
		$harga_penjualan_batu2030 = ($volume_penjualan_batu2030!=0)?($nilai_penjualan_batu2030 / $volume_penjualan_batu2030)  * 1:0;

		//TOTAL
		$total_volume_masuk = $volume_produksi_harian_abubatu_bulan_ini + $volume_produksi_harian_batu0510_bulan_ini + $volume_produksi_harian_batu1020_bulan_ini + $volume_produksi_harian_batu2030_bulan_ini;
		$total_nilai_masuk = $nilai_produksi_harian_abubatu_bulan_ini + $nilai_produksi_harian_batu0510_bulan_ini + $nilai_produksi_harian_batu1020_bulan_ini + $nilai_produksi_harian_batu2030_bulan_ini;

		$total_volume_keluar = $volume_penjualan_abubatu_bulan_ini + $volume_agregat_abubatu_bulan_ini + $volume_agregat_abubatu_bulan_ini_2 + $volume_penjualan_batu0510_bulan_ini + $volume_agregat_batu0510_bulan_ini + $volume_agregat_batu0510_bulan_ini_2 + $volume_penjualan_batu1020_bulan_ini + $volume_agregat_batu1020_bulan_ini + $volume_agregat_batu1020_bulan_ini_2 + $volume_penjualan_batu2030_bulan_ini + $volume_agregat_batu2030_bulan_ini + $volume_agregat_batu2030_bulan_ini_2;
		$total_nilai_keluar = $nilai_penjualan_abubatu_bulan_ini + $nilai_agregat_abubatu_bulan_ini + $nilai_agregat_abubatu_bulan_ini_2 +  $nilai_penjualan_batu0510_bulan_ini + $nilai_agregat_batu0510_bulan_ini + $nilai_agregat_batu0510_bulan_ini_2 + $nilai_penjualan_batu1020_bulan_ini + $nilai_agregat_batu1020_bulan_ini + $nilai_agregat_batu1020_bulan_ini_2 + $nilai_penjualan_batu2030_bulan_ini + $nilai_agregat_batu2030_bulan_ini + $nilai_agregat_batu2030_bulan_ini_2;
		
		$total_volume_akhir = $volume_akhir_agregat_abubatu_bulan_ini_2 + $volume_akhir_agregat_batu0510_bulan_ini_2 + $volume_akhir_agregat_batu1020_bulan_ini_2 + $volume_akhir_agregat_batu2030_bulan_ini_2;
		$total_nilai_akhir = $nilai_akhir_agregat_abubatu_bulan_ini_2 + $nilai_akhir_agregat_batu0510_bulan_ini_2 + $nilai_akhir_agregat_batu1020_bulan_ini_2 + $nilai_akhir_agregat_batu2030_bulan_ini_2;
		
		?>
			
		<tr class="table-judul">
			<th class="garis_kanan" width="11%" align="center" rowspan="2">&nbsp;<br>URAIAN</th>
			<th class="garis_kanan" width="8%" align="center" rowspan="2">&nbsp;<br>SATUAN</th>
			<th class="garis_kanan" width="27%" align="center" colspan="3">MASUK</th>
			<th class="garis_kanan" width="27%" align="center" colspan="3">KELUAR</th>
			<th width="27%" align="center" colspan="3">AKHIR</th>
		</tr>
		<tr class="table-judul">
			<th align="center" width="8%">VOLUME</th>
			<th align="center" width="8%">HARGA</th>
			<th class="garis_kanan" align="center" width="11%">NILAI</th>
			<th align="center" width="8%">VOLUME</th>
			<th align="center" width="8%">HARGA</th>
			<th class="garis_kanan" align="center" width="11%">NILAI</th>
			<th align="center" width="8%">VOLUME</th>
			<th align="center" width="8%">HARGA</th>
			<th align="center" width="11%">NILAI</th>
		</tr>
		<tr class="table-baris1">
			<th align = "center" colspan="12"><b>BAHAN JADI</b></th>
		</tr>
		<tr class="table-baris1">			
			<th align = "left" colspan="8"><i>Opening Balance</i></th>
			<th align = "center"></th>
			<th align = "right"></th>
			<th align = "right"><?php echo number_format($nilai_opening_bahan_jadi,0,',','.');?></th>
		</tr>
		<tr class="table-baris1">		
			<th align = "left"><i>Batu 0,0 - 0,5</i></th>
			<th align = "center">Ton</th>
			<th align = "center"><?php echo number_format($volume_produksi_harian_abubatu_bulan_ini,2,',','.');?></th>
			<th align = "right"><?php echo number_format($harga_produksi_harian_abubatu_bulan_ini,0,',','.');?></th>
			<th align = "right"><?php echo number_format($nilai_produksi_harian_abubatu_bulan_ini,0,',','.');?></th>
			<th align = "center"><?php echo number_format($volume_penjualan_abubatu,2,',','.');?></th>
			<th align = "right"><?php echo number_format($harga_penjualan_abubatu,0,',','.');?></th>
			<th align = "right"><?php echo number_format($nilai_penjualan_abubatu,0,',','.');?></th>
			<th align = "center"><?php echo number_format($volume_akhir_agregat_abubatu_bulan_ini_2,2,',','.');?></th>
			<th align = "right"><?php echo number_format($harga_akhir_agregat_abubatu_bulan_ini_2,0,',','.');?></th>
			<th align = "right"><?php echo number_format($nilai_akhir_agregat_abubatu_bulan_ini_2,0,',','.');?></th>
		</tr>
		<tr class="table-baris1">
			<th align = "left"><i>Batu 0,5 - 10</i></th>
			<th align = "center">Ton</th>
			<th align = "center"><?php echo number_format($volume_produksi_harian_batu0510_bulan_ini,2,',','.');?></th>
			<th align = "right"><?php echo number_format($harga_produksi_harian_batu0510_bulan_ini,0,',','.');?></th>
			<th align = "right"><?php echo number_format($nilai_produksi_harian_batu0510_bulan_ini,0,',','.');?></th>
			<th align = "center"><?php echo number_format($volume_penjualan_batu0510,2,',','.');?></th>
			<th align = "right"><?php echo number_format($harga_penjualan_batu0510,0,',','.');?></th>
			<th align = "right"><?php echo number_format($nilai_penjualan_batu0510,0,',','.');?></th>
			<th align = "center"><?php echo number_format($volume_akhir_agregat_batu0510_bulan_ini_2,2,',','.');?></th>
			<th align = "right"><?php echo number_format($harga_akhir_agregat_batu0510_bulan_ini_2,0,',','.');?></th>
			<th align = "right"><?php echo number_format($nilai_akhir_agregat_batu0510_bulan_ini_2,0,',','.');?></th>
		</tr>
		<tr class="table-baris1">
			<th align = "left"><i>Batu 10 - 20</i></th>
			<th align = "center">Ton</th>
			<th align = "center"><?php echo number_format($volume_produksi_harian_batu1020_bulan_ini,2,',','.');?></th>
			<th align = "right"><?php echo number_format($harga_produksi_harian_batu1020_bulan_ini,0,',','.');?></th>
			<th align = "right"><?php echo number_format($nilai_produksi_harian_batu1020_bulan_ini,0,',','.');?></th>
			<th align = "center"><?php echo number_format($volume_penjualan_batu1020,2,',','.');?></th>
			<th align = "right"><?php echo number_format($harga_penjualan_batu1020,0,',','.');?></th>
			<th align = "right"><?php echo number_format($nilai_penjualan_batu1020,0,',','.');?></th>
			<th align = "center"><?php echo number_format($volume_akhir_agregat_batu1020_bulan_ini_2,2,',','.');?></th>
			<th align = "right"><?php echo number_format($harga_akhir_agregat_batu1020_bulan_ini_2,0,',','.');?></th>
			<th align = "right"><?php echo number_format($nilai_akhir_agregat_batu1020_bulan_ini_2,0,',','.');?></th>
		</tr>
		<tr class="table-baris1">		
			<th align = "left"><i>Batu 20 - 30</i></th>
			<th align = "center">Ton</th>
			<th align = "center"><?php echo number_format($volume_produksi_harian_batu2030_bulan_ini,2,',','.');?></th>
			<th align = "right"><?php echo number_format($harga_produksi_harian_batu2030_bulan_ini,0,',','.');?></th>
			<th align = "right"><?php echo number_format($nilai_produksi_harian_batu2030_bulan_ini,0,',','.');?></th>
			<th align = "center"><?php echo number_format($volume_penjualan_batu2030,2,',','.');?></th>
			<th align = "right"><?php echo number_format($harga_penjualan_batu2030,0,',','.');?></th>
			<th align = "right"><?php echo number_format($nilai_penjualan_batu2030,0,',','.');?></th>
			<th align = "center"><?php echo number_format($volume_akhir_agregat_batu2030_bulan_ini_2,2,',','.');?></th>
			<th align = "right"><?php echo number_format($harga_akhir_agregat_batu2030_bulan_ini_2,0,',','.');?></th>
			<th align = "right"><?php echo number_format($nilai_akhir_agregat_batu2030_bulan_ini_2,0,',','.');?></th>
		</tr>
		<tr class="table-total">
			<th align = "center" colspan="2">TOTAL</th>
			<th align = "center"><?php echo number_format($total_volume_masuk,2,',','.');?></th>
			<th align = "right">-</th>
			<th align = "right"><?php echo number_format($total_nilai_masuk,0,',','.');?></th>
			<th align = "center"><?php echo number_format($total_volume_keluar,2,',','.');?></th>
			<th align = "right">-</th>
			<th align = "right"><?php echo number_format($total_nilai_keluar,0,',','.');?></th>
			<th align = "center"><?php echo number_format($total_volume_akhir,2,',','.');?></th>
			<th align = "right">-</th>
			<th align = "right"><?php echo number_format($total_nilai_akhir,0,',','.');?></th>
		</tr>
	</table>
	<br />
	<br />
	<table width="98%" border="0" cellpadding="30">
		<tr >
			<td width="5%"></td>
			<td width="90%">
				<table width="100%" border="0" cellpadding="2">
					<tr>
						<td align="center" >
							Disetujui Oleh
						</td>
						<td align="center" colspan="2">
							Diperiksa Oleh
						</td>
						<td align="center">
							Dibuat Oleh
						</td>
					</tr>
					<tr class="">
						<td align="center" height="40px">
						
						</td>
						<td align="center">
						
						</td>
						<td align="center">
						
						</td>
						<td align="center">
						
						</td>
					</tr>
					<tr>
						<td align="center">
							<b><u>Gervasius K. Hekin</u><br />
							Ka. Plant</b>
						</td>
						<td align="center">
							<b><br />
							Keuangan</b>
						</td>
						<td align="center">
							<b><br />
							Ka. Produksi</b>
						</td>
						<td align="center" >
							<b><u></u><br />
							Ka. Logistik</b>
						</td>
					</tr>
				</table>
			</td>
			<td width="5%"></td>
		</tr>
	</table>
	</body>
</html>