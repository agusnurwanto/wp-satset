function filePickedSatset(oEvent) {
    jQuery('#wrap-loading').show();
    // Get The File From The Input
    var oFile = oEvent.target.files[0];
    var sFilename = oFile.name;
    // Create A File Reader HTML5
    var reader = new FileReader();

    reader.onload = function(e) {
        var data = e.target.result;
        var workbook = XLSX.read(data, {
            type: 'binary'
        });

        var cek_sheet_name = false;
        workbook.SheetNames.forEach(function(sheetName) {
            // Here is your object
            console.log('sheetName', sheetName);
            if(sheetName == 'data'){
                cek_sheet_name = true;
                var XL_row_object = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[sheetName]);
                var data = [];
                XL_row_object.map(function(b, i){
                	for(ii in b){
                		b[ii] = b[ii].replace(/(\r\n|\n|\r)/g, " ").trim();
                	}
                    data.push(b);
                });
                var json_object = JSON.stringify(data);
                jQuery('#data-excel').val(json_object);
                jQuery('#wrap-loading').hide();
            }
        });
        setTimeout(function(){
            if(false == cek_sheet_name){
                jQuery('#data-excel').val('');
                alert('Sheet dengan nama "data" tidak ditemukan!');
                jQuery('#wrap-loading').hide();
            }
        }, 2000);
    };

    reader.onerror = function(ex) {
      console.log(ex);
    };

    reader.readAsBinaryString(oFile);
}

function relayAjax(options, retries=20, delay=5000, timeout=9000000){
    options.timeout = timeout;
    options.cache = false;
    jQuery.ajax(options)
    .fail(function(jqXHR, exception){
        // console.log('jqXHR, exception', jqXHR, exception);
        if(
            jqXHR.status != '0' 
            && jqXHR.status != '503'
            && jqXHR.status != '500'
        ){
            if(jqXHR.responseJSON){
                options.success(jqXHR.responseJSON);
            }else{
                options.success(jqXHR.responseText);
            }
        }else if (retries > 0) {
            console.log('Koneksi error. Coba lagi '+retries, options);
            var new_delay = Math.random() * (delay/1000);
            setTimeout(function(){ 
                relayAjax(options, --retries, delay, timeout);
            }, new_delay * 1000);
        } else {
            alert('Capek. Sudah dicoba berkali-kali error terus. Maaf, berhenti mencoba.');
        }
    });
}

function sql_migrate_satset(){
    jQuery('#wrap-loading').show();
    relayAjax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'sql_migrate_satset',
            migrate: prompt("Apakah anda mau menjalakan insert query dari folder migrate? Ketik 1 jika iya dan kosongkan jika hanya mau memperbaharui struktur database.")
        },
        success: function(res){
            jQuery('#wrap-loading').hide();
            alert('Success melakukan SQL migrate!');
        },
        error: function(e){
            console.log('Error sql_migrate_satset', e);
            alert('Error '+e);
            jQuery('#wrap-loading').hide();
        }
    });
}

function import_excel_p3ke(tipe_data=0){
    var data = jQuery('#data-excel').val();
    var tahun_anggaran = jQuery('#data-tahun-p3ke').val();
    if (!tahun_anggaran.match(/^\d{4}$/)) {
        return alert('Tahun anggaran tidak valid!');
    }
    if(!data){
        return alert('Excel Data can not empty!');
    }else{
        var update_active = prompt("Apakah anda mau menonaktifkan data sebelumnya? ketik 1 jika iya dan kosongkan saja jika tidak.");
        data = JSON.parse(data);
        jQuery('#wrap-loading').show();

        var data_all = [];
        var data_sementara = [];
        var max = 100;
        data.map(function(b, i){
            data_sementara.push(b);
            if(data_sementara.length%max == 0){
                data_all.push(data_sementara);
                data_sementara = [];
            }
        });
        if(data_sementara.length > 0){
            data_all.push(data_sementara);
        }
        var page = 0;
        var last = data_all.length - 1;
        data_all.reduce(function(sequence, nextData){
            return sequence.then(function(current_data){
                return new Promise(function(resolve_reduce, reject_reduce){
                    page++;
                    relayAjax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            action: 'import_excel_p3ke',
                            data: current_data,
                            page: page,
                            tipe_data: tipe_data,
                            tahun_anggaran: tahun_anggaran,
                            update_active: update_active
                        },
                        success: function(res){
                            resolve_reduce(nextData);
                        },
                        error: function(e){
                            console.log('Error import excel', e);
                        }
                    });
                })
                .catch(function(e){
                    console.log(e);
                    return Promise.resolve(nextData);
                });
            })
            .catch(function(e){
                console.log(e);
                return Promise.resolve(nextData);
            });
        }, Promise.resolve(data_all[last]))
        .then(function(data_last){
            jQuery('#wrap-loading').hide();
            alert('Success import data P3KE dari excel!');
        })
        .catch(function(e){
            console.log(e);
            jQuery('#wrap-loading').hide();
            alert('Error!');
        });
    }
}

function import_excel_stunting(){
    var tahun_anggaran = jQuery('#data-tahun-stunting').val();
    if (!tahun_anggaran.match(/^\d{4}$/)) {
        return alert('Tahun anggaran tidak valid!');
    }
    var data = jQuery('#data-excel').val();
    if(!data){
        return alert('Excel Data can not empty!');
    }else{
        var update_active = prompt("Apakah anda mau menonaktifkan data sebelumnya? ketik 1 jika iya dan kosongkan saja jika tidak.");
        data = JSON.parse(data);
        jQuery('#wrap-loading').show();

        var data_all = [];
        var data_sementara = [];
        var max = 100;
        data.map(function(b, i){
            data_sementara.push(b);
            if(data_sementara.length%max == 0){
                data_all.push(data_sementara);
                data_sementara = [];
            }
        });
        if(data_sementara.length > 0){
            data_all.push(data_sementara);
        }
        var page = 0;
        var last = data_all.length - 1;
        data_all.reduce(function(sequence, nextData){
            return sequence.then(function(current_data){
                return new Promise(function(resolve_reduce, reject_reduce){
                    page++;
                    relayAjax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            action: 'import_excel_stunting',
                            data: current_data,
                            tahun_anggaran: tahun_anggaran,
                            update_active: update_active
                        },
                        success: function(res){
                            resolve_reduce(nextData);
                        },
                        error: function(e){
                            console.log('Error import excel', e);
                        }
                    });
                })
                .catch(function(e){
                    console.log(e);
                    return Promise.resolve(nextData);
                });
            })
            .catch(function(e){
                console.log(e);
                return Promise.resolve(nextData);
            });
        }, Promise.resolve(data_all[last]))
        .then(function(data_last){
            jQuery('#wrap-loading').hide();
            alert('Success import data stunting dari excel!');
        })
        .catch(function(e){
            console.log(e);
            jQuery('#wrap-loading').hide();
            alert('Error!');
        });
    }
}

function import_excel_tbc(){
    var data = jQuery('#data-excel').val();
    if(!data){
        return alert('Excel Data can not empty!');
    }else{
        data = JSON.parse(data);
        jQuery('#wrap-loading').show();

        var data_all = [];
        var data_sementara = [];
        var max = 100;
        data.map(function(b, i){
            data_sementara.push(b);
            if(data_sementara.length%max == 0){
                data_all.push(data_sementara);
                data_sementara = [];
            }
        });
        if(data_sementara.length > 0){
            data_all.push(data_sementara);
        }
        var last = data_all.length - 1;
        data_all.reduce(function(sequence, nextData){
            return sequence.then(function(current_data){
                return new Promise(function(resolve_reduce, reject_reduce){
                    relayAjax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            action: 'import_excel_tbc',
                            data: current_data
                        },
                        success: function(res){
                            resolve_reduce(nextData);
                        },
                        error: function(e){
                            console.log('Error import excel', e);
                        }
                    });
                })
                .catch(function(e){
                    console.log(e);
                    return Promise.resolve(nextData);
                });
            })
            .catch(function(e){
                console.log(e);
                return Promise.resolve(nextData);
            });
        }, Promise.resolve(data_all[last]))
        .then(function(data_last){
            jQuery('#wrap-loading').hide();
            alert('Success import data TBC dari excel!');
        })
        .catch(function(e){
            console.log(e);
            jQuery('#wrap-loading').hide();
            alert('Error!');
        });
    }
}

function import_excel_rtlh(){
    var data = jQuery('#data-excel').val();
    if(!data){
        return alert('Excel Data can not empty!');
    }else{
        data = JSON.parse(data);
        jQuery('#wrap-loading').show();

        var data_all = [];
        var data_sementara = [];
        var max = 100;
        data.map(function(b, i){
            data_sementara.push(b);
            if(data_sementara.length%max == 0){
                data_all.push(data_sementara);
                data_sementara = [];
            }
        });
        if(data_sementara.length > 0){
            data_all.push(data_sementara);
        }
        var last = data_all.length - 1;
        data_all.reduce(function(sequence, nextData){
            return sequence.then(function(current_data){
                return new Promise(function(resolve_reduce, reject_reduce){
                    relayAjax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            action: 'import_excel_rtlh',
                            data: current_data
                        },
                        success: function(res){
                            resolve_reduce(nextData);
                        },
                        error: function(e){
                            console.log('Error import excel', e);
                        }
                    });
                })
                .catch(function(e){
                    console.log(e);
                    return Promise.resolve(nextData);
                });
            })
            .catch(function(e){
                console.log(e);
                return Promise.resolve(nextData);
            });
        }, Promise.resolve(data_all[last]))
        .then(function(data_last){
            jQuery('#wrap-loading').hide();
            alert('Success import data RTLH dari excel!');
        })
        .catch(function(e){
            console.log(e);
            jQuery('#wrap-loading').hide();
            alert('Error!');
        });
    }
}

function get_data_desa(argument) {
	show_loading();
	relayAjax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'get_data_desa'
        },
        dataType: 'json',
        success: function(res){
            console.log('res', res);
            if(res.status == 'error'){
            	alert(res.message);
            }else{
				var body = '';
				for(var b in res.data){
					var id_kec = res.data[b].kec.provno+res.data[b].kec.kabkotno+res.data[b].kec.kecno;
					body += ''
						+'<tr style="background: #ebbcbc;">'
							+'<td style="text-align: center;"><input class="data-kecamatan" type="checkbox" value="'+id_kec+'"></td>'
							+'<td colspan="2">'+res.data[b].kec.kecamatan+'</td>'
						+'</tr>';
					res.data[b].desa.map(function(bb, ii){
						body += ''
							+'<tr>'
								+'<td style="text-align: center;"><input provinsi="'+bb.provinsi+'" kabkot="'+bb.kab_kot+'" type="checkbox" id_kec="'+id_kec+'" value="'+id_kec+bb.desano+'"></td>'
								+'<td>'+bb.kecamatan+'</td>'
								+'<td>'+bb.desa+'</td>'
							+'</tr>';
					});
				};

	            var modal_desa = ''
				  	+'<table id="konfirmasi-desa" style="width: 100%;">'
				      	+'<thead>'
				        	+'<tr style="background: #8997bd;">'
				          		+'<th class="text-white"><input type="checkbox" id="modal_cek_all"></th>'
				          		+'<th class="text-white" width="300">Kecamatan</th>'
				          		+'<th class="text-white">Desa</th>'
				        	+'</tr>'
				      	+'</thead>'
				      	+'<tbody>'+body+'</tbody>'
				  	+'</table>';
	            jQuery('#pilih-desa').html(modal_desa);
	        }
            hide_loading()
        },
        error: function(e){
            console.log('Error import excel', e);
        }
    });
}

function get_data_dtks(argument) {
	var selected = [];
	jQuery('#konfirmasi-desa tbody tr input[type="checkbox"]').map(function(i, b){
		var checkbox = jQuery(b);
		if(checkbox.is(':checked')){
			var id_kec = checkbox.attr('id_kec');
			if(
				id_kec != '' 
				&& typeof id_kec != 'undefined'
			){
				var tr = checkbox.closest('tr');
				selected.push({
					provinsi: checkbox.attr('provinsi'),
					kabkot: checkbox.attr('kabkot'),
					kecamatan: tr.find('td').eq(1).text(),
					desa_kelurahan: tr.find('td').eq(2).text(),
					id_kec: id_kec,
					id_desa: checkbox.val()
				});
			};
		}
	});
	if(selected.length == 0){
		alert("Pilih desa dulu!");
	}else{
		show_loading();
		var last = selected.length-1;
		selected.reduce(function(sequence, nextData){
            return sequence.then(function(current_data){
                return new Promise(function(resolve_reduce, reject_reduce){
                	pesan_loading('Singkronisasi data '+JSON.stringify(current_data));
					relayAjax({
				        url: ajaxurl,
				        type: 'post',
				        data: {
				            action: 'get_data_dtks',
				            desa: current_data
				        },
				        success: function(res){
				        	console.log(res);
				            resolve_reduce(nextData);
				        },
				        error: function(e){
				            console.log('Error import excel', e);
				        }
				    });
                })
                .catch(function(e){
                    console.log(e);
                    return Promise.resolve(nextData);
                });
            })
            .catch(function(e){
                console.log(e);
                return Promise.resolve(nextData);
            });
        }, Promise.resolve(selected[last]))
        .then(function(){
            hide_loading();
            alert('Success singkronisasi data DTKS dari WP-SIKS!');
        })
        .catch(function(e){
            console.log(e);
            hide_loading();
            alert('Error!');
        });
	}
}

function show_loading(){
	jQuery('#wrap-loading').show();
	jQuery('#persen-loading').html('');
	jQuery('#persen-loading').attr('persen', '');
	jQuery('#persen-loading').attr('total', '');
}

function hide_loading(){
	jQuery('#wrap-loading').hide();
	jQuery('#persen-loading').html('');
	jQuery('#persen-loading').attr('persen', '');
	jQuery('#persen-loading').attr('total', '');
}

function pesan_loading(pesan, loading=false){
	if(loading){
		pesan = 'LOADING...<br>'+pesan;
	}
	jQuery('#persen-loading').html(pesan);
	console.log(pesan);
}

jQuery(document).ready(function(){
	var loading = ''
		+'<div id="wrap-loading">'
	        +'<div class="lds-hourglass"></div>'
	        +'<div id="persen-loading"></div>'
	    +'</div>';
	if(jQuery('#wrap-loading').length == 0){
		jQuery('body').prepend(loading);
	}

	if(jQuery('#pilih-desa').length >= 1){
		get_data_desa();
	}
	jQuery('body').on('click', '#modal_cek_all', function(){
		var cek = jQuery(this).is(':checked');
		jQuery('#konfirmasi-desa tbody tr input[type="checkbox"]').prop('checked', cek);
	});
	jQuery('body').on('click', '#konfirmasi-desa .data-kecamatan', function(){
		var cek = jQuery(this).is(':checked');
		var id_kec = jQuery(this).val();
		jQuery('#konfirmasi-desa tbody tr input[type="checkbox"][id_kec="'+id_kec+'"]').prop('checked', cek);
	});


    if (jQuery("#satset_load_ajax_carbon").length >= 1) {
        jQuery("#wrap-loading").show();
        jQuery.ajax({
            url: ajaxurl,
            type: "post",
            data: {
                action: "satset_load_ajax_carbon",
                api_key: satset.api_key,
                type: jQuery("#satset_load_ajax_carbon").attr("data-type"),
            },
            dataType: "json",
            success: function (data) {
                jQuery("#wrap-loading").hide();
                if (data.status == "success") {
                    jQuery("#satset_load_ajax_carbon").html(data.message);
                } else {
                    return alert(data.message);
                }
            },
            error: function (e) {
                console.log(e);
                return alert(data.message);
            },
        });

        jQuery("body").on("click", ".satset-header-tahun", function () {
        var tahun = jQuery(this).attr("tahun");
        if (jQuery(this).hasClass("active")) {
            jQuery(this).removeClass("active");
            jQuery('.satset-body-tahun[tahun="' + tahun + '"]').removeClass(
                "active"
            );
        } else {
            jQuery(this).addClass("active");
            jQuery('.satset-body-tahun[tahun="' + tahun + '"]').addClass(
                "active"
            );
        }
    });
    }

    jQuery("#generate_user_satset").on("click", function () {
        if (confirm("Apakah anda yakin akan menggenerate user SIPD!")) {
            jQuery("#wrap-loading").show();
            jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "generate_user_satset",
                    api_key: satset.api_key,
                    pass: prompt(
                        "Masukan password default untuk User yang akan dibuat"
                    ),
                    update_pass: confirm(
                        "Apakah anda mau mereset password user existing juga?"
                    ),
                },
                dataType: "json",
                success: function (data) {
                    jQuery("#wrap-loading").hide();
                    return alert(data.message);
                },
                error: function (e) {
                    console.log(e);
                    return alert(data.message);
                },
            });
        }
    });
});

function get_data_unit_wpsipd_satset() {
    jQuery("#wrap-loading").show();
    jQuery.ajax({
        url: ajaxurl,
        type: "post",
        dataType: "json",
        data: {
            action: "get_data_unit_wpsipd_satset",
            server: jQuery(
                'input[name="carbon_fields_compact_input[_crb_url_server_satset]"]'
            ).val(),
            api_key: jQuery(
                'input[name="carbon_fields_compact_input[_crb_apikey_wpsipd]"]'
            ).val(),
            tahun_anggaran: jQuery(
                'input[name="carbon_fields_compact_input[_crb_tahun_wpsipd]"]'
            ).val(),
        },
        success: function (data) {
            jQuery("#wrap-loading").hide();
            console.log(data.message);
            if (data.status == "success") {
                alert("Data berhasil disinkron");
            } else {
                alert(data.message);
            }
        },
        error: function (e) {
            console.log(e);
            return alert(e);
        },
    });
}