/**
 * Created by jun on 15/12/14.
 */
$(function () {
    $('.gzhuBtn').click(function () {
        $('#showcode').show();
    })
    $('.closebtn').click(function () {
        $('#showcode').hide();
    });


    $('#avatar').on('change', function () {
        $('#pageForm').submit();
        $.PageDialog.ok('正在提交...', function () {

        }, 10000);
        $(this).val('');
    });

    $('#nicknameBtn').click(function() {
        var nickname = $(this).attr('data-val');
        var t = '<input type="text" id="nicknameInput" class="tc_ipt" value="'+nickname+'">';
        $.PageDialog.custom(t, function () {
            var nickname = $('#nicknameInput').val();
            if (nickname.length<2 || nickname.length>20) {
                $.PageDialog.hidden();
                $.PageDialog.fail('昵称必须在2-20之间');
                return;
            }
            $.getJsonp(apiBaseUrl + '/info/change-nickname', {'nickname': nickname}, function (json) {
                if (json.code == 100) {
                    $.PageDialog.ok('昵称修改成功', function () {
                        window.location.reload();
                    });
                } else {
                    $.PageDialog.hidden();
                    $.PageDialog.fail(json.msg);
                }
            });
        },function() {
            var t = $('#nicknameInput').val();
            $('#nicknameInput').val("").focus().val(t);
        });
    });
});

function successUploadImage(data) {
    data = JSON.parse(data);
    if (data.error) {
        $.PageDialog.fail(data.message);
    } else {
        $.PageDialog.ok('头像更改成功', function () {
            window.location.reload();
        });

    }
}
