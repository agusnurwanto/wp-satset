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