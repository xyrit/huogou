/**
 * Created by han on 2015/10/12.
 */
function lazy_img(h){
    $(h+' img[src="images/loading.gif"]').each(function(){
        $(this).attr("src",$(this).attr("src2"));
    })
}
