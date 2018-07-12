<?php
/**
 * @category  huogou.com
 * @name  AppConfig
 * @version 1.0
 * @date 2015-12-29
 * @author  keli <liwanglai@gmail.com>
 * 
 */
namespace app\models;

class AppPush extends AppConfig
{
    static function G($id = 'push', $attr = array())
    {
        return parent::G($id, $attr);
    }
    
    public function rules()
    {
        return [
            [["msg_name",'os'], 'required'],
            [['id'], 'integer'],
        ];
    }
    
     public function attributeLabels()
    {
        return [
            "msg_name" => "通知标题",
            "msg_des" => "通知内容",
            "msg_do" => "后续动作",     //app: http: up:
            "msg_type" => "提醒方式",
            "msg_log" => "是否在推送记录页显示",
            'msg_transmission'=>"透传消息",
            'os'    => "目标平台",
            'status'=>"启用状态",
             'time'=>"操作时间",
        ];
    }
}