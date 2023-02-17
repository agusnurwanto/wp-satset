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
    PRIMARY KEY  (id)
);

CREATE TABLE `data_dtks` (
    `id` int(11) NOT NULL auto_increment,
    `NOKK` TEXT DEFAULT NULL,
    `NIK` TEXT DEFAULT NULL,
    `Nama` TEXT DEFAULT NULL,
    `Alamat` TEXT DEFAULT NULL,
    `FIRST_SK` TEXT DEFAULT NULL,
    `padankan_at` TEXT DEFAULT NULL,
    `BPNT` TEXT DEFAULT NULL,
    `BST` TEXT DEFAULT NULL,
    `PKH` TEXT DEFAULT NULL,
    `PBI` TEXT DEFAULT NULL,
    `BNPT_PPKM` TEXT DEFAULT NULL,
    `BLT` TEXT DEFAULT NULL,
    `BLT_BBM` TEXT DEFAULT NULL,
    `RUTILAHU` TEXT DEFAULT NULL,
    `keterangan_meninggal` TEXT DEFAULT NULL,
    `keterangan_disabilitas` TEXT DEFAULT NULL,
    PRIMARY KEY  (id)
);