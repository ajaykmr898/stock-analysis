$(document).ready( function () {

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
    $.ajax({
        type: "POST",
        url: "/data",
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: "json",
        success: (msg) => {
            console.log('ok');
        },
        error: () => {
            console.log('error');
        }
    });
}
