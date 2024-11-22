CREATE TABLE `data_batas_desa` (
    `id` int(11) NOT NULL auto_increment,
    `id_desa` int(11) DEFAULT NULL,
    `desa` TEXT DEFAULT NULL,
    `kecamatan` TEXT DEFAULT NULL,
    `kab_kot` TEXT DEFAULT NULL,
    `provinsi` TEXT DEFAULT NULL,
    `area` TEXT DEFAULT NULL,
    `perimeter` TEXT DEFAULT NULL,
    `hectares` TEXT DEFAULT NULL,
    `ukuran_kot` TEXT DEFAULT NULL,
    `pemusatan` TEXT DEFAULT NULL,
    `jumplah_pen` TEXT DEFAULT NULL,
    `provno` VARCHAR(10) DEFAULT NULL,
    `kabkotno` VARCHAR(10) DEFAULT NULL,
    `kecno` VARCHAR(10) DEFAULT NULL,
    `desano` VARCHAR(10) DEFAULT NULL,
    `id2012` TEXT DEFAULT NULL,
    `polygon` LONGTEXT DEFAULT NULL,
    `active` tinyint(4) DEFAULT 1,
    `update_at` datetime DEFAULT current_timestamp,
    PRIMARY KEY  (id)
);

CREATE TABLE `data_batas_kecamatan` (
    `id` int(11) NOT NULL auto_increment,
    `provno` VARCHAR(10) DEFAULT NULL,
    `kabkotno` VARCHAR(10) DEFAULT NULL,
    `kecno` VARCHAR(10) DEFAULT NULL,
    `provinsi` TEXT DEFAULT NULL,
    `kabkot` TEXT DEFAULT NULL,
    `kecamatan` TEXT DEFAULT NULL,
    `id2012` TEXT DEFAULT NULL,
    `polygon` LONGTEXT DEFAULT NULL,
    `active` tinyint(4) DEFAULT 1,
    `update_at` datetime DEFAULT current_timestamp,
    PRIMARY KEY  (id)
);

CREATE TABLE `data_p3ke` (
    `id` int(11) NOT NULL auto_increment,
    `id_p3ke` TEXT DEFAULT NULL,
    `provinsi` TEXT DEFAULT NULL,
    `kabkot` TEXT DEFAULT NULL,
    `kecamatan` TEXT DEFAULT NULL,
    `desa` TEXT DEFAULT NULL,
    `kode_kemendagri` TEXT DEFAULT NULL,
    `jenis_desil` TINYINT(1) DEFAULT NULL,
    `alamat` TEXT DEFAULT NULL,
    `kepala_keluarga` TEXT DEFAULT NULL,
    `nik` TEXT DEFAULT NULL,
    `padan_dukcapil` VARCHAR(30) DEFAULT NULL,
    `jenis_kelamin` VARCHAR(30) DEFAULT NULL,
    `tanggal_lahir` datetime DEFAULT NULL,
    `pekerjaan` TEXT DEFAULT NULL,
    `pendidikan` TEXT DEFAULT NULL,
    `rumah` TEXT DEFAULT NULL,
    `punya_tabungan` VARCHAR(30) DEFAULT NULL,
    `jenis_atap` TEXT DEFAULT NULL,
    `jenis_dinding` TEXT DEFAULT NULL,
    `jenis_lantai` TEXT DEFAULT NULL,
    `sumber_penerangan` TEXT DEFAULT NULL,
    `bahan_bakar_memasak` TEXT DEFAULT NULL,
    `sumber_air_minum` TEXT DEFAULT NULL,
    `fasilitas_bab` TEXT DEFAULT NULL,
    `penerima_bpnt` VARCHAR(30) DEFAULT NULL,
    `penerima_bpum` VARCHAR(30) DEFAULT NULL,
    `penerima_bst` VARCHAR(30) DEFAULT NULL,
    `penerima_pkh` VARCHAR(30) DEFAULT NULL,
    `penerima_sembako` VARCHAR(30) DEFAULT NULL,
    `resiko_stunting` VARCHAR(30) DEFAULT NULL,
    `active` tinyint(4) DEFAULT 1,
    `update_at` datetime DEFAULT current_timestamp,
    `tahun_anggaran` year(4) DEFAULT NULL,
    PRIMARY KEY  (id)
);

CREATE TABLE `data_p3ke_anggota_keluarga` (
    `id` int(11) NOT NULL auto_increment,
    `id_p3ke` TEXT DEFAULT NULL,
    `provinsi` TEXT DEFAULT NULL,
    `kabkot` TEXT DEFAULT NULL,
    `kecamatan` TEXT DEFAULT NULL,
    `desa` TEXT DEFAULT NULL,
    `kode_kemendagri` TEXT DEFAULT NULL,
    `jenis_desil` TINYINT(1) DEFAULT NULL,
    `alamat` TEXT DEFAULT NULL,
    `id_individu` TEXT DEFAULT NULL,
    `nama` TEXT DEFAULT NULL,
    `nik` TEXT DEFAULT NULL,
    `padan_dukcapil` VARCHAR(30) DEFAULT NULL,
    `jenis_kelamin` VARCHAR(30) DEFAULT NULL,
    `hubungan_keluarga` VARCHAR(30) DEFAULT NULL,
    `tanggal_lahir` datetime DEFAULT NULL,
    `status_kawin` VARCHAR(30) DEFAULT NULL,
    `pekerjaan` TEXT DEFAULT NULL,
    `pendidikan` TEXT DEFAULT NULL,
    `usia_dibawah_7` VARCHAR(30) DEFAULT NULL,
    `usia_7_12` VARCHAR(30) DEFAULT NULL,
    `usia_13_15` VARCHAR(30) DEFAULT NULL,
    `usia_16_18` VARCHAR(30) DEFAULT NULL,
    `usia_19_21` VARCHAR(30) DEFAULT NULL,
    `usia_22_59` VARCHAR(30) DEFAULT NULL,
    `usia_60_keatas` VARCHAR(30) DEFAULT NULL,
    `penerima_bpnt` VARCHAR(30) DEFAULT NULL,
    `penerima_bpum` VARCHAR(30) DEFAULT NULL,
    `penerima_bst` VARCHAR(30) DEFAULT NULL,
    `penerima_pkh` VARCHAR(30) DEFAULT NULL,
    `penerima_sembako` VARCHAR(30) DEFAULT NULL,
    `resiko_stunting` VARCHAR(30) DEFAULT NULL,
    `active` tinyint(4) DEFAULT 1,
    `update_at` datetime DEFAULT current_timestamp,
    `tahun_anggaran` year(4) DEFAULT NULL,
    PRIMARY KEY  (id)
);

CREATE TABLE `data_stunting` (
    `id` int(11) NOT NULL auto_increment,
    `nik` TEXT DEFAULT NULL,
    `nama` TEXT DEFAULT NULL,
    `jenis_kelamin` VARCHAR(2) DEFAULT NULL,
    `tanggal_lahir` TEXT DEFAULT NULL,
    `bb_lahir` int(11) DEFAULT NULL,
    `tb_lahir` int(11) DEFAULT NULL,
    `nama_ortu` TEXT DEFAULT NULL,
    `provinsi` TEXT DEFAULT NULL,
    `kabkot` TEXT DEFAULT NULL,
    `kecamatan` TEXT DEFAULT NULL,
    `puskesmas` TEXT DEFAULT NULL,
    `desa` TEXT DEFAULT NULL,
    `posyandu` TEXT DEFAULT NULL,
    `rt` int(11) DEFAULT NULL,
    `rw` int(11) DEFAULT NULL,
    `alamat` TEXT DEFAULT NULL,
    `usia_saat_ukur` TEXT DEFAULT NULL,
    `tanggal_pengukuran` TEXT DEFAULT NULL,
    `berat` int(11) DEFAULT NULL,
    `tinggi` int(11) DEFAULT NULL,
    `lingkar_lengan_atas` TEXT DEFAULT NULL,
    `bb_per_u` TEXT DEFAULT NULL,
    `zs_bb_per_u` int(11) DEFAULT NULL,
    `tb_per_u` TEXT DEFAULT NULL,
    `zs_tb_per_u` int(11) DEFAULT NULL,
    `bb_per_tb` TEXT DEFAULT NULL,
    `zs_bb_per_tb` int(11) DEFAULT NULL,
    `naik_berat_badan` VARCHAR(4) DEFAULT NULL,
    `pmt_diterima_per_kg` VARCHAR(100) DEFAULT NULL,
    `jml_vit_a` VARCHAR(100) DEFAULT NULL,
    `kpsp` VARCHAR(100) DEFAULT NULL,
    `kia` VARCHAR(100) DEFAULT NULL,
    `active` tinyint(4) DEFAULT 1,
    `update_at` datetime DEFAULT current_timestamp,
    `tahun_anggaran` year(4) DEFAULT NULL,
    PRIMARY KEY  (id)
);

CREATE TABLE `data_tbc` (
    `id` int(11) NOT NULL auto_increment,
    `provinsi` TEXT DEFAULT NULL,
    `kabkot` TEXT DEFAULT NULL,
    `kecamatan` TEXT DEFAULT NULL,
    `desa` TEXT DEFAULT NULL,
    `tanggal_register` TEXT DEFAULT NULL,
    `no_reg_fasyankes` TEXT DEFAULT NULL,
    `no_reg_kabkot` TEXT DEFAULT NULL,
    `nik` TEXT DEFAULT NULL,
    `nama` TEXT DEFAULT NULL,
    `umur` INT(11) DEFAULT NULL,
    `jenis_kelamin` VARCHAR(4) DEFAULT NULL,
    `alamat` TEXT DEFAULT NULL,
    `pindahan_dari_fasyankes` TEXT DEFAULT NULL,
    `tindak_lanjut` TEXT DEFAULT NULL,
    `tanggal_mulai_pengobatan` TEXT DEFAULT NULL,
    `hasil_akhir_pengobatan` TEXT DEFAULT NULL,
    `status_pengobatan` TEXT DEFAULT NULL,
    `keterangan` TEXT DEFAULT NULL,
    `active` tinyint(4) DEFAULT 1,
    `update_at` datetime DEFAULT current_timestamp,
    `tahun_anggaran` year(4) DEFAULT NULL,
    PRIMARY KEY  (id)
);

CREATE TABLE `data_rtlh` (
    `id` int(11) NOT NULL auto_increment,
    `nik` TEXT DEFAULT NULL,
    `nama` TEXT DEFAULT NULL,
    `alamat` TEXT DEFAULT NULL,
    `provinsi` TEXT DEFAULT NULL,
    `kabkot` TEXT DEFAULT NULL,
    `kecamatan` TEXT DEFAULT NULL,
    `desa` TEXT DEFAULT NULL,
    `rw` VARCHAR(5) DEFAULT NULL,
    `rt` VARCHAR(5) DEFAULT NULL,
    `nilai_bantuan` double DEFAULT NULL,
    `lpj` TEXT DEFAULT NULL,
    `tgl_lpj` TEXT DEFAULT NULL,
    `sumber_dana` TEXT DEFAULT NULL,
    `active` tinyint(4) DEFAULT 1,
    `update_at` datetime DEFAULT current_timestamp,
    `tahun_anggaran` year(4) DEFAULT NULL,
    PRIMARY KEY  (id)
);

CREATE TABLE `data_dtks_satset` (
    `id` int(11) NOT NULL auto_increment,
    `provinsi` TEXT DEFAULT NULL,
    `kabkot` TEXT DEFAULT NULL,
    `kecamatan` TEXT DEFAULT NULL,
    `desa` TEXT DEFAULT NULL,
    `desa_kelurahan` TEXT DEFAULT NULL,
    `id_kec` VARCHAR(30) DEFAULT NULL,
    `id_desa` VARCHAR(30) DEFAULT NULL,
    `Alamat` TEXT DEFAULT NULL,
    `BLT` TEXT DEFAULT NULL,
    `BLT_BBM` TEXT DEFAULT NULL,
    `BNPT_PPKM` TEXT DEFAULT NULL,
    `BPNT` TEXT DEFAULT NULL,
    `BST` TEXT DEFAULT NULL,
    `FIRST_SK` TEXT DEFAULT NULL,
    `NIK` TEXT DEFAULT NULL,
    `NOKK` TEXT DEFAULT NULL,
    `Nama` TEXT DEFAULT NULL,
    `PBI` TEXT DEFAULT NULL,
    `PKH` TEXT DEFAULT NULL,
    `RUTILAHU` TEXT DEFAULT NULL,
    `SEMBAKO_ADAPTIF` TEXT DEFAULT NULL,
    `checkBtnHamil` TEXT DEFAULT NULL,
    `checkBtnVerifMeninggal` TEXT DEFAULT NULL,
    `counter` TEXT DEFAULT NULL,
    `deleted_label` TEXT DEFAULT NULL,
    `idsemesta` TEXT DEFAULT NULL,
    `isAktifHamil` TEXT DEFAULT NULL,
    `is_btn_dapodik` TEXT DEFAULT NULL,
    `is_btn_hidupkan` TEXT DEFAULT NULL,
    `is_btn_padankan` TEXT DEFAULT NULL,
    `is_nonaktif` TEXT DEFAULT NULL,
    `keterangan_disabilitas` TEXT DEFAULT NULL,
    `keterangan_meninggal` TEXT DEFAULT NULL,
    `masih_hidup_label` TEXT DEFAULT NULL,
    `padankan_at` TEXT DEFAULT NULL,
    `periode_blt` TEXT DEFAULT NULL,
    `periode_blt_bbm` TEXT DEFAULT NULL,
    `periode_bpnt` TEXT DEFAULT NULL,
    `periode_bpnt_ppkm` TEXT DEFAULT NULL,
    `periode_bst` TEXT DEFAULT NULL,
    `periode_pbi` TEXT DEFAULT NULL,
    `periode_pkh` TEXT DEFAULT NULL,
    `periode_rutilahu` TEXT DEFAULT NULL,
    `periode_sembako_adaptif` TEXT DEFAULT NULL,
    `verifyid` TEXT DEFAULT NULL,
    `active` tinyint(4) DEFAULT 1,
    `update_at` datetime DEFAULT current_timestamp,
    `tahun_anggaran` year(4) DEFAULT NULL,
    PRIMARY KEY  (id),
    INDEX(`id_desa`)
);

CREATE TABLE `satset_data_unit` (
  `id` int(11) NOT NULL auto_increment,
  `id_setup_unit` int(11) DEFAULT NULL,
  `id_unit` int(11) DEFAULT NULL,
  `is_skpd` tinyint(4) DEFAULT NULL,
  `kode_skpd` varchar(50) DEFAULT NULL,
  `kunci_skpd` int(11) DEFAULT NULL,
  `nama_skpd` text DEFAULT NULL,
  `posisi` varchar(30) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `id_skpd` int(11) DEFAULT NULL,
  `bidur_1` smallint(6) DEFAULT NULL,
  `bidur_2` smallint(6) DEFAULT NULL,
  `bidur_3` smallint(6) DEFAULT NULL,
  `idinduk` int(11) DEFAULT NULL,
  `ispendapatan` tinyint(4) DEFAULT NULL,
  `isskpd` tinyint(4) DEFAULT NULL,
  `kode_skpd_1` varchar(10) DEFAULT NULL,
  `kode_skpd_2` varchar(10) DEFAULT NULL,
  `kodeunit` varchar(30) DEFAULT NULL,
  `komisi` int(11) DEFAULT NULL,
  `namabendahara` text,
  `namakepala` text DEFAULT NULL,
  `namaunit` text DEFAULT NULL,
  `nipbendahara` varchar(30) DEFAULT NULL,
  `nipkepala` varchar(30) DEFAULT NULL,
  `pangkatkepala` varchar(50) DEFAULT NULL,
  `setupunit` int(11) DEFAULT NULL,
  `statuskepala` varchar(20) DEFAULT NULL,
  `mapping` varchar(10) DEFAULT NULL,
  `id_kecamatan` int(11) DEFAULT NULL,
  `id_strategi` int(11) DEFAULT NULL,
  `is_dpa_khusus` tinyint(4) DEFAULT NULL,
  `is_ppkd` tinyint(4) DEFAULT NULL,
  `set_input` tinyint(4) DEFAULT NULL,
  `update_at` datetime DEFAULT NULL,
  `tahun_anggaran` year(4) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY  (id)
);