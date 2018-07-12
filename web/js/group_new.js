var condition = {
    t: 3
};

function topicList() {
    $.getContent(apiBaseUrl + '/topic/topic-list', condition, 'topicList');
}

function rightTopicList() {
    $.getContent(apiBaseUrl + '/topic/right-topic-list', {}, 'rightTopicList');
}

function activeUser() {
    $.getContent(apiBaseUrl + '/topic/active-user', {}, 'activeUser');
}

function groupNew(groupId) {
    $.getContent(apiBaseUrl + '/topic/group-new', {groupId: groupId}, 'groupNew');
}

function newJoin(groupId) {
    $.getContent(apiBaseUrl + '/topic/new-join', {groupId: groupId}, 'newJoin');
}

function topicCondition(params, callback) {
    $(".hgq-content-tab").find("li").each(function (status) {
        $(this).on('click', function () {
            $(this).addClass("act").siblings().removeClass("act");
            $('.hgq-content-list').html('');
            if(status == 0) status = 3;
            params.t = status;
            callback();
        })
    });
}

function createTopicUrl($topicId){
    return 'http://group.' + baseHost + '/topic-' + $topicId + '.html';
}

function success_topicList(json) {
    var skinUrl = 'http://skin.' + baseHost;
    var strHtml = '';
    $.each(json, function(i, v) {
        var user_center_url = createUserCenterUrl(v.home);
        strHtml += '<li>';
        strHtml += '<div>';
        strHtml += '<a href="' + createTopicUrl(v.id) + '" target="_blank" class="newtopic"> ' + v.title + ' ';
        if(v.top == 1){
            strHtml += '<img src="' + skinUrl + '/img/o_pic32.png">';
        }
        if(v.digest == 1){
            strHtml += '<img src="' + skinUrl + '/img/o_pic30.png">';
        }
        if(v.city == 1){
            strHtml += '<img src="' + skinUrl + '/img/o_pic31.png">';
        }
        strHtml += '</a>';
        strHtml += '<p style="display: inline; position: relative; top:-3px;">' + v.time + '</p>';
        strHtml += '<p><a href="' + user_center_url + '" target="_blank" style="font-size: 14px;color:#999">' + v.username + '</a>  &nbsp;&nbsp;来自：' + v.group + '</p>';
        strHtml += '</div>';
        strHtml += '<aside>';
        strHtml += '<span class="reply"><a href="' + createTopicUrl(v.id) + '" target="_blank"> 回复('+ v.comment +')</a></span>';
        strHtml += '<em>/</em>';
        strHtml += '<a href="' + createTopicUrl(v.id) + '" target="_blank"><span class="like">人气('+ v.view +')</span></a>';
        strHtml += '</aside>';
        strHtml += '</li>';
    })
    $('.hgq-content-list').html(strHtml);
}

function success_rightTopicList(json) {
    var strHtml = '';
    $.each(json, function(i, v) {
        var user_center_url = createUserCenterUrl(v.home);

        if(v.intro == '') v.intro = '这家伙很懒，什么都没留下。';

        strHtml += '<li>';
        strHtml += '<picture><img src="' + v.avatar + '"><i></i></picture>';
        strHtml += '<a href="' + createTopicUrl(v.id) + '" title="'+v.title+'" target="_blank">' + v.title + '</a>';
        strHtml += '<p class="gP_hf">' + v.group + ' | ' + v.comment + '条回复</p>';
        strHtml += '<figure class="member-infocard" style="display: none">';
        strHtml += '<em><em></em></em><picture><img src="' + v.avatar + '"><i></i></picture>';
        strHtml += '<figcaption>';
        strHtml += '<h2><a href="' + user_center_url + '" target="_blank">' + v.username + '</a></h2>';
        strHtml += '<span class="member-infocard-level">' + v.grade_name + '</span>';
        if(v.city != ''){
            strHtml += ' <span class="member-infocard-location">' + v.city + '</span>';
        }
        strHtml += '<p>' + v.intro + '</p>';
        if(v.friend == '' || v.friend == 0){
            strHtml += '<a href="javascript:;" class="add-friend" data-id="' + v.home + '">+ 加好友</a>';
        }
        strHtml += '<a href="javascript:;" class="send-message letter" data-id="' + v.home + '">发私信</a>';
        strHtml += '</figcaption>';
        strHtml += '</figure>';
        strHtml += '</li>';
    })
    $('.rightTopic').html(strHtml);

    $('.letter').on('click',function(){
        var user = $(this).siblings('h2').html();
        var id = $(this).attr('data-id');

        $('.letter-fixed-con h2').html('对'+user+'说：');
        $('.commentid').html('<input type="hidden" name="id" value="'+ id +'">');
        $('#letter-fixed').stop().fadeIn('fast');
    })

    //添加好友
    $('.add-friend').click(function(){
        var id = $(this).attr('data-id');
        var urls = apiBaseUrl + '/group/add-friend';
        $.ajax({
            async: false,
            url: urls,
            type: "GET",
            dataType: 'jsonp',
            jsonp: 'callback',
            data: {id: id},
            success: function (data) {
                if(data.code == 100 || data.code == 102){
                    $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                }else{
                    $('.safety-b-box h3').html(data.msg);
                }
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },2000)
            }
        })
    })
}


function success_activeUser(json) {
    var strHtml = '';
    $.each(json, function(i, v) {
        var user_center_url = createUserCenterUrl(v.home);

        if(v.intro == '' || v.intro == null) v.intro = '这家伙很懒，什么都没留下。';

        strHtml += '<li>';
        strHtml += '<a href="' + user_center_url + '" target="_blank">';
        strHtml += '<picture><img src="' + v.avatar + '"><i></i></picture>';
        strHtml += '<p title="'+v.username+'">' + v.username + '</p></a>';
        strHtml += '<figure class="member-infocard">';
        strHtml += '<em><em></em></em><picture><img src="' + v.avatar + '"><i></i></picture>';
        strHtml += '<figcaption>';
        strHtml += '<h2><a href="' + user_center_url + '" title="'+v.username+'" target="_blank">' + v.username + '</a></h2>';
        strHtml += '<span class="member-infocard-level">' + v.grade_name + '</span>'
        if(v.city  != '') strHtml += '<span class="member-infocard-location">' + v.city + '</span>';
        strHtml += '<p>' + v.intro + '</p>';
        if(v.friend == 'undefined' || v.u == null || v.friend == 0){
            strHtml += '<a href="javascript:;" class="add-friend" data-id="' + v.home + '">+ 加好友</a>';
        }
        strHtml += '<a href="javascript:;" class="send-message letter" data-id="' + v.home + '">发私信</a>';
        strHtml += '</figcaption>';
        strHtml += '</figure>';
        strHtml += '</li>';
    })
    $('.activeUser').html(strHtml);

    $('.hot-member').find('li').each(function(index){
        if((index+1)%3 == 0){
            $('.hot-member').find('li:eq('+index+')').css('marginRight','0');
        }
    });

    $('.letter').on('click',function(){
        var user = $(this).siblings('h2').html();
        var id = $(this).attr('data-id');

        $('.letter-fixed-con h2').html('对'+user+'说：');
        $('.commentid').html('<input type="hidden" name="id" value="'+ id +'">');
        $('#letter-fixed').stop().fadeIn('fast');
    })

    //添加好友
    $('.add-friend').click(function(){
        var id = $(this).attr('data-id');
        var urls = apiBaseUrl + '/group/add-friend';
        $.ajax({
            async: false,
            url: urls,
            type: "GET",
            dataType: 'jsonp',
            jsonp: 'callback',
            data: {id: id},
            success: function (data) {
                if(data.code == 100 || data.code == 102){
                    $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                }else{
                    $('.safety-b-box h3').html(data.msg);
                }
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },2000)
            }
        })
    })
}

function success_newJoin(json) {
    var strHtml = '';
    $.each(json, function(i, v) {
        var user_center_url = createUserCenterUrl(v.home);

        if(v.intro == '' || v.intro == null) v.intro = '这家伙很懒，什么都没留下。';

        strHtml += '<li class="show-card">';
        strHtml += '<a href="' + user_center_url + '" target="_blank">';
        strHtml += '<picture><img src="' + v.avatar + '"><i></i></picture>';
        strHtml += '<p title="' + v.username + '">' + v.username + '</p></a>';
        strHtml += '<figure class="member-infocard">';
        strHtml += '<em><em></em></em><picture><img src="' + v.avatar + '"><i></i></picture>';
        strHtml += '<figcaption>';
        strHtml += '<h2><a href="' + user_center_url + '" target="_blank">' + v.username + '</a></h2>';
        strHtml += '<span class="member-infocard-level">' + v.grade_name + '</span>'
        if(v.city != '') strHtml += ' <span class="member-infocard-location">' + v.city + '</span>';
        strHtml += '<p>' + v.intro + '</p>';
        if(v.friend == 'undefined' || v.u == null || v.friend == 0){
            strHtml += '<a href="javascript:;" class="add-friend" data-id="' + v.home + '">+ 加好友</a>';
        }
        strHtml += '<a href="javascript:;" class="send-message letter" data-id="' + v.home + '">发私信</a>';
        strHtml += '</figcaption>';
        strHtml += '</figure>';
        strHtml += '</li>';
    })
    $('.newJoin').html(strHtml);

    $('.hot-member').find('li').each(function(index){
        if((index+1)%3 == 0){
            $('.hot-member').find('li:eq('+index+')').css('marginRight','0');
        }
    });

    $('.show-card').hover(function(){
        $('.show-card').children('.member-infocard').hide();
        $(this).children('.member-infocard').show();
    },function(){
        $(this).children('.member-infocard').hide();
    })

    $('.letter').on('click',function(){
        var user = $(this).siblings('h2').html();
        var id = $(this).attr('data-id');

        $('.letter-fixed-con h2').html('对'+user+'说：');
        $('.commentid').html('<input type="hidden" name="id" value="'+ id +'">');
        $('#letter-fixed').stop().fadeIn('fast');
    })

    //添加好友
    $('.add-friend').click(function(){
        var id = $(this).attr('data-id');
        var urls = apiBaseUrl + '/group/add-friend';
        $.ajax({
            async: false,
            url: urls,
            type: "GET",
            dataType: 'jsonp',
            jsonp: 'callback',
            data: {id: id},
            success: function (data) {
                if(data.code == 100 || data.code == 102){
                    $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                }else{
                    $('.safety-b-box h3').html(data.msg);
                }
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },2000)
            }
        })
    })

}

function success_groupNew(json)
{
    var strHtml = '';
    $.each(json, function(i, v) {
        var user_center_url = createUserCenterUrl(v.home);

        if(v.intro == '' || v.intro == null) v.intro = '这家伙很懒，什么都没留下。';

        strHtml += '<li class="show-card">';
        strHtml += '<picture><img src="' + v.avatar + '"><i></i></picture>';
        strHtml += '<p class="scP_hf"><a href="'+ user_center_url +'" target="_blank">' + v.username + '</a>'+ v.time;
        if(v.topic == 1){
            strHtml += '发表';
        }else{
            strHtml += '回复';
        }
        strHtml += '话题<a href="'+ createTopicUrl(v.id) +'" class="gray" target="_blank">'+ v.title +'</a></p>';
        strHtml += '<figure class="member-infocard">';
        strHtml += '<em><em></em></em><picture><img src="' + v.avatar + '"><i></i></picture>';
        strHtml += '<figcaption>';
        strHtml += '<h2><a href="' + user_center_url + '" target="_blank">' + v.username + '</a></h2>';
        strHtml += '<span class="member-infocard-level">' + v.grade_name + '</span>'
        if(v.city != '') strHtml += '<span class="member-infocard-location">' + v.city + '</span>';
        strHtml += '<p>' + v.intro + '</p>';
        if(v.friend == 'undefined' || v.u == null || v.friend == 0){
            strHtml += '<a href="javascript:;" class="add-friend" data-id="' + v.home + '">+ 加好友</a>';
        }
        strHtml += '<a href="javascript:;" class="send-message letter" data-id="' + v.home + '">发私信</a>';
        strHtml += '</figcaption>';
        strHtml += '</figure>';
        strHtml += '</li>';
    })
    $('.newTopic').html(strHtml);

    $('.show-card').hover(function(){
        $('.show-card').children('.member-infocard').hide();
        $(this).children('.member-infocard').show();
    },function(){
        $(this).children('.member-infocard').hide();
    })

    $('.letter').on('click',function(){
        var user = $(this).siblings('h2').html();
        var id = $(this).attr('data-id');

        $('.letter-fixed-con h2').html('对'+user+'说：');
        $('.commentid').html('<input type="hidden" name="id" value="'+ id +'">');
        $('#letter-fixed').stop().fadeIn('fast');
    })

    //添加好友
    $('.add-friend').click(function(){
        var id = $(this).attr('data-id');
        var urls = apiBaseUrl + '/group/add-friend';
        $.ajax({
            async: false,
            url: urls,
            type: "GET",
            dataType: 'jsonp',
            jsonp: 'callback',
            data: {id: id},
            success: function (data) {
                if(data.code == 100 || data.code == 102){
                    $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                }else{
                    $('.safety-b-box h3').html(data.msg);
                }
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },2000)
            }
        })
    })
}
