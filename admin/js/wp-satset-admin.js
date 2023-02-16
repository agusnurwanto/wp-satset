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

function relayAjax(options, retries=20, delay=5000, timeout=90000){
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

function import_excel_p3ke(){
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
                            action: 'import_excel_p3ke',
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
                            action: 'import_excel_stunting',
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