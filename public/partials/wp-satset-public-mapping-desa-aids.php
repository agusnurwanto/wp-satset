<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Satset_Mapping_Desa_AIDS {
    public $wpdb;
    public $table_data_aids;
    public $table_data_batas_desa;
    public $table_mapping_aids;

    public function __construct() {
        global $wpdb;
        $this->wpdb                = $wpdb;
        $this->table_data_aids     = 'data_aids';
        $this->table_data_batas_desa = 'data_batas_desa';
        $this->table_mapping_aids  = 'mapping_aids';
    }

    /**
     * Ambil data AIDS dikelompokkan per desa domisili pasien,
     * beserta hasil mapping jika sudah ada di tabel mapping_aids.
     */
    public function get_aids_data() {
        $query = "SELECT
            a.provinsi_domisili,
            a.kabkot_domisili,
            a.kecamatan_domisili,
            a.desa_domisili,
            a.alamat_domisili,
            CONCAT_WS('-', a.provinsi_domisili, a.kabkot_domisili, a.kecamatan_domisili, a.desa_domisili) AS key_domisili,
            COUNT(a.id) AS total_data,
            m.kode_desa_aids   AS kode_desa_aids,
            m.kode_desa_satset AS kode_desa_satset
        FROM {$this->table_data_aids} a
        LEFT JOIN {$this->table_mapping_aids} m
            ON CONCAT_WS('-', a.provinsi_domisili, a.kabkot_domisili, a.kecamatan_domisili, a.desa_domisili) = m.kode_desa_aids
        WHERE a.active = 1
        GROUP BY a.provinsi_domisili, a.kabkot_domisili, a.kecamatan_domisili, a.desa_domisili
        ORDER BY a.provinsi_domisili, a.kabkot_domisili, a.kecamatan_domisili, a.desa_domisili";

        return $this->wpdb->get_results($query);
    }

    /**
     * Ambil seluruh desa dari data_batas_desa sebagai pilihan kode SIKS.
     */
    public function get_desa_satset_data() {
        $query = "SELECT
            provinsi,
            kab_kot,
            kecamatan,
            desa,
            id2012
        FROM {$this->table_data_batas_desa}
        WHERE active = 1
            AND id2012 IS NOT NULL
            AND id2012 != ''
        GROUP BY id2012
        ORDER BY provinsi, kab_kot, kecamatan, desa";

        return $this->wpdb->get_results($query);
    }
}

$mapping_aids = new WP_Satset_Mapping_Desa_AIDS();
?>
<style type="text/css">
    .wrap-table-mapping {
        overflow: auto;
        max-height: 80vh;
        width: 100%;
    }
    #tbl-mapping-aids thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #343a40;
        color: #fff;
        white-space: nowrap;
    }
    #tbl-mapping-aids td {
        white-space: nowrap;
        vertical-align: middle;
    }
    #tbl-mapping-aids select {
        min-width: 320px;
    }
    .badge-mapped   { background-color: #28a745; color: #fff; padding: 2px 8px; border-radius: 4px; }
    .badge-unmapped { background-color: #dc3545; color: #fff; padding: 2px 8px; border-radius: 4px; }
</style>

<div class="cetak">
    <div style="padding: 10px; margin: 0 0 3rem 0;">
        <h1 class="text-center" style="margin: 3rem;">Mapping Kode Desa — Data AIDS/HIV</h1>

        <!-- Info Box -->
        <div style="background-color:#f0f0f1; padding:15px; margin-bottom:20px; border-left:4px solid #0073aa; border-radius:3px;">
            <p><strong>Informasi:</strong> Halaman ini membaca data dari tabel <code>data_aids</code> yang dikelompokkan berdasarkan kolom <code>desa_domisili</code> (alamat domisili pasien), kemudian melakukan matching dengan kolom <code>id2012</code> di tabel <code>data_batas_desa</code>. Hasil mapping disimpan di tabel <code>mapping_aids</code>.</p>
            <p>Kolom <strong>Desa Domisili</strong> = nama desa domisili pasien dari data AIDS. Kolom <strong>Kode SIKS</strong> = <code>id2012</code> dari tabel batas desa.</p>
        </div>

        <div style="margin-bottom: 20px;" class="text-center">
            <button class="btn btn-success" onclick="autoMapAids();">
                <i class="dashicons dashicons-randomize"></i> Auto Mapping Desa
            </button>
            <span style="margin-left:20px;" id="mapping-progress"></span>
        </div>

        <div class="wrap-table-mapping">
            <table id="tbl-mapping-aids" class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Provinsi Domisili</th>
                        <th class="text-center">Kab/Kota Domisili</th>
                        <th class="text-center">Kecamatan Domisili</th>
                        <th class="text-center">Desa Domisili</th>
                        <th class="text-center">Total Data</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Kode Desa (id2012)</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $aids_data = $mapping_aids->get_aids_data();
                if (!empty($aids_data)):
                    $desa_satset = $mapping_aids->get_desa_satset_data();

                    // Bangun opsi <select> sekali
                    $opsi_base = '<option value="">-- Pilih Kode Desa --</option>';
                    foreach ($desa_satset as $d) {
                        $label = esc_attr($d->id2012) . ' (' .
                                 esc_html($d->provinsi) . ' - ' .
                                 esc_html($d->kab_kot)  . ' - ' .
                                 esc_html($d->kecamatan) . ' - ' .
                                 esc_html($d->desa) . ')';
                        $opsi_base .= '<option value="' . esc_attr($d->id2012) . '">' . $label . '</option>';
                    }

                    $no = 0;
                    foreach ($aids_data as $row):
                        $no++;
                        $desa_domisili = esc_attr($row->key_domisili);

                        // Pastikan baris ada di mapping_aids (gunakan key gabungan)
                        if (empty($row->kode_desa_aids)) {
                            $mapping_aids->wpdb->insert(
                                $mapping_aids->table_mapping_aids,
                                array('kode_desa_aids' => $row->key_domisili)
                            );
                        }

                        // Terapkan selected jika sudah di-mapping
                        $opsi = $opsi_base;
                        if (!empty($row->kode_desa_satset)) {
                            $opsi = str_replace(
                                'value="' . esc_attr($row->kode_desa_satset) . '"',
                                'value="' . esc_attr($row->kode_desa_satset) . '" selected',
                                $opsi_base
                            );
                        }
                        $sudah_mapping = !empty($row->kode_desa_satset);
                        $badge = $sudah_mapping
                            ? '<span class="badge-mapped">Mapped</span>'
                            : '<span class="badge-unmapped">Belum</span>';
                ?>
                    <tr>
                        <td class="text-center"><?php echo $no; ?></td>
                        <td><?php echo esc_html($row->provinsi_domisili); ?></td>
                        <td><?php echo esc_html($row->kabkot_domisili); ?></td>
                        <td><?php echo esc_html($row->kecamatan_domisili); ?></td>
                        <td><?php echo esc_html($row->desa_domisili); ?></td>
                        <td class="text-center"><?php echo intval($row->total_data); ?></td>
                        <td class="text-center"><?php echo $badge; ?></td>
                        <td>
                            <select class="form-data form-control form-control-sm"
                                    onchange="mappingKodeAids(this);"
                                    data-desa-domisili="<?php echo $desa_domisili; ?>">
                                <?php echo $opsi; ?>
                            </select>
                        </td>
                    </tr>
                <?php
                    endforeach;
                else:
                ?>
                    <tr>
                        <td colspan="8" class="text-center" style="color:#999;">
                            Tidak ada data AIDS yang ditemukan. Pastikan tabel <code>data_aids</code> berisi data dengan kolom <code>desa_domisili</code>.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
/**
 * Kirim satu mapping ke server.
 * @param {HTMLElement} el      – elemen <select> yang berubah
 * @param {boolean}     silent  – jika true, tidak tampilkan alert
 */
function mappingKodeAids(el, silent) {
    silent = silent || false;
    var kode_desa_satset      = jQuery(el).val();
    var desa_domisili  = jQuery(el).data('desa-domisili');

    if (!silent) jQuery('#wrap-loading').show();

    return new Promise(function(resolve) {
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                action:        'mapping_kode_desa_aids',
                api_key:       '<?php echo get_option(SATSET_APIKEY); ?>',
                kode_desa_aids:      desa_domisili,
                kode_desa_satset:     kode_desa_satset
            },
            success: function(res) {
                if (!silent) {
                    jQuery('#wrap-loading').hide();
                    alert(res.message);
                }
                // Perbarui badge status di baris yang sama
                var badge = kode_desa_satset
                    ? '<span class="badge-mapped">Mapped</span>'
                    : '<span class="badge-unmapped">Belum</span>';
                jQuery(el).closest('tr').find('td:nth-child(8)').html(badge);
                resolve(res);
            },
            error: function(xhr, status, err) {
                if (!silent) {
                    jQuery('#wrap-loading').hide();
                    alert('Terjadi kesalahan: ' + err);
                }
                resolve({ status: 'error', message: err });
            }
        });
    });
}

/**
 * Auto-mapping: cocokkan kecamatan domisili + desa domisili dengan opsi yang tersedia.
 * Hanya memproses baris yang belum di-mapping (select kosong).
 */
async function autoMapAids() {
    if (!confirm('Apakah anda yakin? Hanya baris yang belum di-mapping yang akan diproses.')) return;

    var rows  = document.querySelectorAll('#tbl-mapping-aids tbody tr');
    var tasks = [];

    rows.forEach(function(row) {
        var select = row.querySelector('select.form-data');
        if (!select || select.value) return; // sudah mapped, skip

        // kolom: No(0) | Provinsi(1) | Kab/Kota(2) | Kecamatan(3) | Desa(4) | Total(5) | Status(6) | Select(7)
        var kecText  = row.cells[3].innerText.trim().toLowerCase().replace(/^kec\.\s*/i, '');
        var desaText = row.cells[4].innerText.trim().toLowerCase();
        var bestMatch = null;

        for (var i = 0; i < select.options.length; i++) {
            var opt = select.options[i];
            if (!opt.value) continue;

            var optText  = opt.text.toLowerCase();
            var parensMatch = optText.match(/\((.+)\)$/);
            if (!parensMatch) continue;

            var parts   = parensMatch[1].split('-').map(function(s){ return s.trim(); });
            var optKec  = parts[2] || '';
            var optDesa = parts[3] || '';

            if (optKec === kecText && optDesa === desaText) {
                bestMatch = opt.value;
                break;
            }
            if (optDesa === desaText && !bestMatch) {
                bestMatch = opt.value;
            }
            if (optKec === kecText && !bestMatch) {
                bestMatch = opt.value;
            }
        }

        if (bestMatch) {
            tasks.push({ select: select, value: bestMatch });
        }
    });

    if (tasks.length === 0) {
        alert('Tidak ada data yang cocok untuk di-auto-mapping.');
        return;
    }

    jQuery('#wrap-loading').show();
    jQuery('#mapping-progress').text('Memproses 0 / ' + tasks.length + ' ...');

    var done = 0;
    // Proses 4 permintaan sekaligus
    for (var i = 0; i < tasks.length; i += 4) {
        var batch = tasks.slice(i, i + 4);
        var promises = batch.map(function(t) {
            t.select.value = t.value;
            return mappingKodeAids(t.select, true);
        });
        await Promise.all(promises);
        done += batch.length;
        jQuery('#mapping-progress').text('Memproses ' + done + ' / ' + tasks.length + ' ...');
    }

    jQuery('#wrap-loading').hide();
    jQuery('#mapping-progress').text('');
    alert('Auto mapping selesai. ' + done + ' data berhasil diproses.');
}
</script>
