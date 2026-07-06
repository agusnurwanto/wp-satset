<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$id_wilayah = !empty($_GET['id_wilayah']) ? intval($_GET['id_wilayah']) : 0;

if (empty($id_wilayah)) {
    echo '<div class="alert alert-danger">ID Wilayah tidak ditemukan.</div>';
    return;
}

echo '<h2>Detail DTSEN</h2>';
echo '<p>ID Wilayah : <b>'.$id_wilayah.'</b></p>';