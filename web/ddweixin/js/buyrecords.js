/**
 * Created by jun on 15/11/21.
 */
var page = 1;
var perpage = 20;

$(function() {

    var periodId = getPeriodIdByUrl(window.location.href);
    var url = apiBaseUrl+'/period/buylist';
    var data = {id:periodId,page:page,perpage:perpage};
    $.getJsonp(url,data,function(json) {
        if (json.list.length==0) {
            $('#divRecordList').html('<div class="noRecords colorbbb clearfix"><s></s>暂无记录</div>');
        } else {
            createBuyrecordsHtml(json)
        }
    });


    var stopLoadPage = false;
    var isLoading = false;
    //下一页
    $.onSrollBottom( function() {
        if (stopLoadPage || isLoading) {
            return;
        }
        isLoading = true;
        $('#divBuyLoading').show();
        var page = $('#hidPage').val();
        page = parseInt(page)+1;

        var periodId = getPeriodIdByUrl(window.location.href);
        var url = apiBaseUrl+'/period/buylist';
        var data = {id:periodId,page:page,perpage:perpage};
        $.getJsonp(url,data,function(json) {
            var t = function() {
                createBuyrecordsHtml(json, true);
                $('#divBuyLoading').hide();
                if (json.list.length==0) {
                    stopLoadPage = true;
                } else {
                    $('#hidPage').val(page);
                    isLoading = false;
                }
            }
            setTimeout(t,1000);
        });
    });



    Base.getScript(skinBaseUrl + "/weixin/js/jquery.mousewheel.js", function () {
        Base.getScript(skinBaseUrl + "/weixin/js/jquery.jscrollpane.js", function () {

            //点击查看夺宝码
            $('#divRecordList').on('click', 'li', function () {
                var period_id = $(this).attr("period_id");
                var buyId = $(this).attr("buyId");
                var userCenterUrl = $(this).attr('userCenterUrl');
                var userName = $(this).attr('userName');
                $.getJsonp(apiBaseUrl + '/period/getuserbuycodesbybuyid', {
                    "periodid": period_id,
                    "buyid": buyId
                }, function (json) {
                    if (json && json.codes) {
                        var codes = json.codes.split(',');
                        var codesDiv = '';
                        $.each(codes,function(i,v) {
                            codesDiv += '<span>'+v+'</span>';
                        });
                        var w;
                        var z = function () {
                            var J = $("#dd_container", w).jScrollPane({verticalDragMinHeight: 15});
                            J.unbind("mousewheel").bind("mousewheel", function (O, P) {
                                var M = P > 0 ? "Up" : "Down";
                                var N = J.scrollTop();
                                var L = J[0].scrollHeight;
                                var K = J.height();
                                if (N + K >= L && P < 0) {
                                    preventDefault(O)
                                } else {
                                    if (N == 0 && P > 0) {
                                        preventDefault(O)
                                    }
                                }
                            })
                        };

                        var y = $(window).width();
                        var B = function (K,C,E) {
                            $("body").attr("style", "overflow:hidden;");
                            IsMasked = true;
                            var L = function () {
                                var O = '<div class="codes-box clearfix">';
                                O += '<a id="a_close" href="javascript:;" class="z-set box-close"></a>';
                                O += '<div class="buy_codes">';
                                O += "<dl>";
                                O += '<dt class="gray9"><span class="fl"><a href="javascript:;" class="blue">' + C.substring(0, 7) + '</a></span>本次参与<em class="orange">' + E + "</em>人次</dt>";
                                O += '<dd class="gray9" id="dd_container">';
                                O += '<div id="div_list">' + K + "</div>";
                                O += "</dd>";
                                O += "</dl>";
                                O += "</div>";
                                O += "</div>";
                                return O
                            };
                            var N = function () {
                                w = $("#pageDialog");
                                $("#a_close", w).click(function () {
                                    M.cancel();
                                    $("body").attr("style", "");
                                    IsMasked = false
                                });
                                w.bind("click", function (O) {
                                    stopBubble(O)
                                });
                                $("body").bind("click", function () {
                                    M.cancel();
                                    $("body").attr("style", "");
                                    IsMasked = false
                                });
                                $("#pageDialogBG").bind("click", function () {
                                    M.cancel();
                                    $("body").attr("style", "");
                                    IsMasked = false
                                });
                                z()
                            };
                            var J = 835;
                            if (y >= 1000) {
                                y = J
                            } else {
                                if (y >= 900) {
                                    y = J - 80 * 1
                                } else {
                                    if (y >= 800) {
                                        y = J - 80 * 2
                                    } else {
                                        if (y >= 700) {
                                            y = J - 80 * 3
                                        } else {
                                            if (y >= 600) {
                                                y = J - 80 * 4
                                            } else {
                                                if (y >= 500) {
                                                    y = J - 80 * 5
                                                } else {
                                                    if (y >= 400) {
                                                        y = J - 80 * 6
                                                    } else {
                                                        if (y >= 300) {
                                                            y = J - 80 * 7
                                                        } else {
                                                            y = J - 80 * 8
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            var M = new $.PageDialog(L(), {W: y, H: 300, close: true, autoClose: false, ready: N})
                        };
                        B(codesDiv,userName,codes.length);
                    }
                });
            });

        })
    })
    
});

function createBuyrecordsHtml(json, append) {
    var html = '';
     var periodId = getPeriodIdByUrl(window.location.href);
    $.each(json.list, function(i,v) {
        var buyId = v.buy_id;
        var buyAddr = v.buy_ip_addr;
        var buyNum = v.buy_num;
        var buyTime = v.buy_time;
        var userAvatar = createUserFaceImgUrl(v.user_avatar, avatarSize[1], avatarSize[1]);
        var userHomeId = v.user_home_id;
        var userName = v.user_name;
        var userCenterUrl = createUserCenterUrl(userHomeId);

        html += '<li period_id='+periodId+' buyid='+buyId+' userCenterUrl="'+userCenterUrl+'" userName="'+userName+'">';
        html += '<span>';
        html += '<a href="'+userCenterUrl+'">';
        html += '<img src="'+userAvatar+'" />';
        html += '</a>';
        html += '</span>';
        html += '<dl>';
        html += '<dt>';
        html += '<a href="'+userCenterUrl+'" class="blue">'+userName+'</a>';
        html += '<em>('+buyAddr+')</em>';
        html += ' </dt>';
        html += ' <dd class="gray6"  style="cursor:pointer">';
        html += '夺宝了';
        html += '<b class="orange">'+buyNum+'</b>人次 ';
        html += '</dd>';
        html += '<dd class="gray9">'+buyTime+'</dd>';
        html += ' </dd>';
        html += '</dl> <b class="fr z-arrow"></b>';
        html += '</li>';
    });
    if (append) {
        $('#divRecordList ').append(html);
    } else {
        $('#divRecordList ').html(html);
    }
}


function getPeriodIdByUrl(url) {
    var periodId = '';
    var s = '/buyrecords-([0-9]+)\.html';
    var reg = new RegExp(s);
    var r = url.match(reg);
    if (r != null) {
        periodId = r[1];
    }
    return periodId;
}
