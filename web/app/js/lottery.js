$(function() {
    //奖品列表
    $.getJsonp(apiBaseUrl + '/active/lottery-config-new', {id:id}, actLotteryConfig);
    $.getJsonp(apiBaseUrl + '/active/reward-list', {id:id}, actRewardList);
})

function actRewardList(json)
{
    var html = '';
    $.each(json, function(i, v){
        html += '<li>恭喜用户<span>'+ v.username +'</span>获得"'+ v.reward +'"奖品</li>';
    })
    $('.noliststyle-2').append(html);

    // 中奖用户
    var slider_index=0;
    var slider_count= $('.message ul li').length;
    function message_slider(){
        slider_index++;
        if( slider_index > slider_count -1 ) slider_index=0;
        $('.message ul').animate({marginTop: -slider_index * 30 },150);
    }
    window.setInterval( function(){message_slider() ; } ,3000);

}

function actLotteryConfig(json)
{
    var html = '';
    var l = '';
    $.each(json.list,function(i,v){
        if(i == 3){
            l = 7;
        }else if(i == 4){
            l = 3;
        }else if(i == 5){
            l = 6;
        }else if(i == 6){
            l = 5;
        }else if(i == 7){
            l = 4
        }else{
            l = i;
        }

        if(i == 4){
            if(json.num > 0){
                html += '<li class="start" data-count="3"><div class="img"><img src="./images/lottery/img3.png" class="button"><div class="txt">（<span class="count">'+ json.num +'</span>次机会）</div></div></li>';
            }else{
                html += '<li class="start" data-count="3"><div class="img"><img src="./images/lottery/img4.png" class="button"><div class="txt">充100抽1次</div></div></li>';
            }
        }
        html += '<li data-txt="'+ v.name+'" class=" lottery-unit lottery-unit-'+ l +'">';
        html += '<div class="img bi"><p>'+ v.name+'</p><img src="'+ v.icon +'" alt=""><img src="./images/lottery/img5.png" class="act"></div>';
        html += '</li>';
    })
    $("#lottery").append(html);


    lottery.init('lottery');
    lottery.clickCount = json.num ; // 允许点击次数
    $(".start").click(function(){
        $(".start .button").attr('src','./images/lottery/img4.png');

        if( lottery.clickCount >0 ){
            if (click) {
                return false;
            }else{
                lottery.clickCount--;
                if(lottery.clickCount<=0) lottery.clickCount=0;
                $(".start .count").text(lottery.clickCount);

                lottery.speed=100;
                roll();
                click=true;
                lottery.complete= function(index){
                    // alert(index);
                    // 获取名称
                    // ...
                    if(lottery.clickCount>0){
                        $(".start .button").attr('src','./images/lottery/img3.png');
                    }
                    popShow('.choujiang_result');
                };
                return false;
            }}else{
            $(".start .button").attr('src','./images/lottery/img4.png');
        }
    });
}

// 点击抽奖
var lottery={
    index:-1,	//当前转动到哪个位置，起点位置
    count:0,	//总共有多少个位置
    timer:0,	//setTimeout的ID，用clearTimeout清除
    speed:20,	//初始转动速度
    times:0,	//转动次数
    cycle:50,	//转动基本次数：即至少需要转动多少次再进入抽奖环节
    prize:-1,	//中奖位置,
    clickCount:0, // 点击次数
    init:function(id){
        if ($("#"+id).find(".lottery-unit").length>0) {
            $lottery = $("#"+id);
            $units = $lottery.find(".lottery-unit");
            this.obj = $lottery;
            this.count = $units.length;
            $lottery.find(".lottery-unit-"+this.index).addClass("active");
        };
    },
    roll:function(){
        var index = this.index;
        var count = this.count;
        var lottery = this.obj;
        $(lottery).find(".lottery-unit-"+index).removeClass("active");
        index += 1;
        if (index>count-1) {
            index = 0;
        };
        $(lottery).find(".lottery-unit-"+index).addClass("active");
        this.index=index;
        return false;
    },
    stop:function(index){
        this.prize=index;
        return false;
    },
    complete:function(){}
};

function roll(){
    lottery.times += 1;
    lottery.roll();
    if (lottery.times > lottery.cycle+10 && lottery.prize==lottery.index) {
        clearTimeout(lottery.timer);
        lottery.complete(lottery.prize);
        lottery.prize=-1;
        lottery.times=0;
        click=false;
    }else{
        if (lottery.times<lottery.cycle) {
            lottery.speed -= 10;
        }else if(lottery.times==lottery.cycle) {
            //var index = Math.random()*(lottery.count)|0;
            var urls = apiBaseUrl + '/active/raffle?id='+id;
            $.getJsonp(urls,{},function(data) {
                if(data.code == 0){
                    lottery.prize = data.id;
                    $('.reward_img img').attr('src', data.pic);
                    $('.s_title').html('恭喜您，获得”'+ data.name +'”');
                    if(data.type == 4){
                        $('.descr').html('点击“我的伙购—账户明细”查收');
                    }else if(data.type == 5){
                        $('.descr').html('点击“我的伙购—我的福分”查收');
                    }else if(data.type == 1){
                        $('.descr').html('点击“我的伙购—我的红包”查收');
                    }
                }else{
                    $('.adel_ts').html(data.msg);
                    $('.buy_box').css('display', 'block')
                    $('.popcover').show();

                    $('.close').click(function(){
                        $('.buy_box').hide();
                        $('.popcover').hide();
                        location.reload();
                    })
                    return false;
                }
            });
        }else{
            if (lottery.times > lottery.cycle+10 && ((lottery.prize==0 && lottery.index==7) || lottery.prize==lottery.index+1)) {
                lottery.speed += 110;
            }else{
                lottery.speed += 20;
            }
        }
        if (lottery.speed<40) {
            lottery.speed=40;
        };
        //console.log(lottery.times+'^^^^^^'+lottery.speed+'^^^^^^^'+lottery.prize);
        lottery.timer = setTimeout(roll,lottery.speed);
    }
    return false;
}

var click=false;

