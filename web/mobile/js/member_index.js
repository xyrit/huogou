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

    $('#addGroup').attr('ios-url','mqqapi://card/show_pslcard?src_type=internal&version=1&uin=364218153&key=74ad2311a16fb0b24383eb61d9c51cc3421b88e5e7674e0960a6c6954ac82eb2&card_type=group&source=external');
    $('#addGroup').attr('android-url','mqqopensdkapi://bizAgent/qm/qr?url=http%3A%2F%2Fqm.qq.com%2Fcgi-bin%2Fqm%2Fqr%3Ffrom%3Dapp%26p%3Dandroid%26k%3D-GeBEdoGts8tZYG2R9YuiN9Jku9i1rjy');
    openApp($('#addGroup'));
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
