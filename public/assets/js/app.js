$(document).ready( function () {

    $(document).on("change", ".companies", function () {
        let val = $(this).val();
        if (val > 0) {
            let actions = {
                success: (data, swal) => {
                    swal.close();
                    console.log(data);
                },
                error: () => {

                }
            };
            callAjax("POST", "/export", {id: val}, actions);
        }
    });

    $(document).on("click", ".single-old, .single-new", function () {
        let id = $(this).attr("data-id");
        let code = $(this).attr("data-code");
        let type = $(this)[0].classList[2];
        let timestamp = $(this).attr("data-timestamp");
        let data = {id, code, type, timestamp};
        importData(data);
    });

    $(document).on("click", ".all-new", function () {
        let data = {type: "all-new"};
        importData(data);
    });
});

function importData (data) {
    let actions = {
        "success": (data) => Swal.fire({
            title: 'Imported',
            text: 'Data imported successfully',
            icon: 'success',
            allowOutsideClick: false,
            preConfirm: () => {
                location.reload();
            }
        }),
        "error": () => Swal.fire({
            title: 'Error',
            text: 'Error during importing',
            icon: 'error',
            allowOutsideClick: false,
            preConfirm: () => {
            }
        })
    };
    callAjax("POST", "/import", data, actions);
}

function callAjax(type, url, data, actions) {
    let swal = Swal.fire({title: "Loading", text: "Please wait ...", icon:"info", showConfirmButton: false, allowOutsideClick: false});
    $.ajax({
        type: type,
        url: url,
        data: data,
        headers:  {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: "json",
        success: (data) => {
            actions.success(data, swal);
        },
        error: () => {
            actions.error();
        }
    });
}
