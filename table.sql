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