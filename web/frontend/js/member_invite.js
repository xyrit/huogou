function getInviteList(page, perpage) {
    $.getContent(apiBaseUrl + '/invite/invite-list', {page: page, perpage: perpage}, 'inviteList');
}

var condition = {
    page: 1,
    perpage: 10,
    status: -1,
    region: 0,
    start_time: "",
    end_time: "",
};
function getCommissionList() {
    $.getContent(apiBaseUrl + '/invite/commission-list', condition, 'commissionList');
}

var mention_condition = {
    page: 1,
    perpage: 10,
    status: -1,
    region: 0,
    start_time: "",
    end_time: "",
};
function getMentionList() {
    $.getContent(apiBaseUrl + '/invite/mention-list', mention_condition, 'mentionList');
}

function applyMention() {
    $('#applyMention').bind('click', function() {
        money = $(".application-for-con").find("input[name=money]").val();
        user = $(".application-for-con").find("input[name=user]").val();
        bank = $(".application-for-con").find("input[name=bank]").val();
        branch = $(".application-for-con").find("input[name=branch]").val();
        bank_number = $(".application-for-con").find("input[name=bank_number]").val();
        phone = $(".application-for-con").find("input[name=phone]").val();

        if (!(money && user && bank && branch && bank_number && phone)) {
            alert("请填写完整信息");
            return;
        }

        $.getContent(apiBaseUrl + '/invite/apply-mention', {money: money, user: user, bank: bank, branch: branch, bank_number: bank_number, phone: phone}, 'applyMention');
    });
}

function success_inviteList(json) {
    totalCount = json.totalCount;
    totalPage = json.totalPage;

    $(".invite-friends-table").find("tbody").html("");
    var strHtml = '';
    $.each(json.list, function (i, v) {
        var userFace = createUserFaceImgUrl(160, v.inviteduid);
        var userCenterUrl = createUserCenterUrl(v.home_id);
        strHtml += '<tr><td class="left padding name">';
        strHtml += '<picture><img src="' + userFace + '" alt=""></picture>';
        strHtml += '<a href="' + userCenterUrl + '">' + v.user + '</a>';
        strHtml += '</td><td>' + v.date + '</td>';
        strHtml += '<td>' + v.number + '</td>';
        if (v.status == 1) {
            strHtml += '<td>已参与伙购</td></tr>';
        } else {
            strHtml += '<td>未参与伙购</td></tr>';
        }
    });
    $(".invite-friends-table").find("tbody").append(strHtml);

    if (totalPage > 1) {
        $(".pagination").createPage({
            pageCount: totalPage,
            current: condition.page,
            downPage: 1,
            backFn: function (p) {
                //console.log(p);
            }
        });
    }

    if (totalCount == 0) {
        $("#record_con").find("tbody").append("暂无记录");
    }
}

function success_commissionList(json) {
    totalCount = json.totalCount;
    totalPage = json.totalPage;

    $(".invite-friends-table").find("tbody").html("");
    var strHtml = '';
    $.each(json.list, function (i, v) {
        var userFace = createUserFaceImgUrl(160, v.inviteduid);
        var userCenterUrl = createUserCenterUrl(v.home_id);
        strHtml += '<tr><td class="left padding name">';
        strHtml += '<picture><img src="' + userFace + '" alt=""></picture>';
        strHtml += '<a href="' + userCenterUrl + '">' + v.user + '</a>';
        strHtml += '</td><td class="left">' + v.date + '</td>';
        strHtml += '<td class="left">' + v.description + '</td>';
        strHtml += '<td>' + v.money + '</td>';
        strHtml += '<td class="orange">+' + v.commission + '</td>';
    });
    $(".invite-friends-table").find("tbody").append(strHtml);

    if (totalPage > 1) {
        $(".pagination").createPage({
            pageCount: totalPage,
            current: condition.page,
            downPage: 1,
            backFn: function (p) {
                //console.log(p);
            }
        });
    }

    if (totalCount == 0) {
        $("#record_con").find("tbody").append("暂无记录");
    }
}

function filt()
{
    $(".screening").find("a").each(function(C, B) {
        $(this).bind("click",
            function() {
                $(this).addClass("act").siblings().removeClass("act");
                condition.start_time = "";
                condition.end_time = "";
                condition.region = C;
                getCommissionList();
            })
    });

    $(".screening").find("input[type=submit]").bind("click", function() {
        if ($("#J-xl").val() && $("#J-xl-2").val()) {
            condition.start_time = $("#J-xl").val();
            condition.end_time = $("#J-xl-2").val();
            getCommissionList();
        }
    });
}

function success_applyMention(json) {
    alert(json.msg);
}

function success_mentionList(json) {
    totalCount = json.totalCount;
    totalPage = json.totalPage;

    $(".get").find("tbody").html("");
    var strHtml = '';
    $.each(json.list, function (i, v) {
        var userFace = createUserFaceImgUrl(160, v.inviteduid);
        var userCenterUrl = createUserCenterUrl(v.home_id);
        strHtml += '<tr><td class="left padding">' + v.date + '</td>';
        strHtml += '<td class="left">' + v.bank_number + '</td>';
        strHtml += '<td class="orange">' + v.money + '</td>';
        strHtml += '<td>' + v.renew + '</td>';
        if (v.status == 0) {
            strHtml += '<td><span class="green">未审核</span></td></tr>';
        } else if (v.status == 1) {
            strHtml += '<td><span class="green">审核通过</span></td></tr>';
        } else {
            strHtml += '<td><span class="green">审核未通过</span></td></tr>';
        }
    });
    $(".get").find("tbody").append(strHtml);

    if (totalPage > 1) {
        $(".pagination").createPage({
            pageCount: totalPage,
            current: mention_condition.page,
            downPage: 1,
            backFn: function (p) {
                //console.log(p);
            }
        });
    }

    if (totalCount == 0) {
        $("#record_con").find("tbody").append("暂无记录");
    }
}

function mention_filt()
{
    $(".screening").find("a").each(function(C, B) {
        $(this).bind("click",
            function() {
                $(this).addClass("act").siblings().removeClass("act");
                mention_condition.start_time = "";
                mention_condition.end_time = "";
                mention_condition.region = C;
                getMentionList();
            })
    });

    $(".screening").find("input[type=submit]").bind("click", function() {
        if ($("#J-xl").val() && $("#J-xl-2").val()) {
            mention_condition.start_time = $("#J-xl").val();
            mention_condition.end_time = $("#J-xl-2").val();
            getMentionList();
        }
    });
}