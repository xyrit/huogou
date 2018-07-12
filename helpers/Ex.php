<?php
/**
 * 生成excel文件操作
 *
 * @author wesley wu
 * @date 2013.12.9
 */
namespace app\helpers;

class Ex
{
    private $limit = 10000;

    public function download($data, $fileName)
    {
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=".$fileName);
        $str = ' <html xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns="http://www.w3.org/TR/REC-html40">
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 <html>
     <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
         <style id="Classeur1_16681_Styles"></style>
     </head>
     <body>
         <div id="Classeur1_16681" align=center x:publishsource="Excel">
             ';

        $guard = 0;
        $long = '<table x:str border=0 cellpadding=0 cellspacing=0 width=100% style="border-collapse: collapse">';
        foreach($data as $v)
        {
            $guard++;
            if($guard==$this->limit)
            {
                ob_flush();
                flush();
                $guard = 0;
            }
            $long .= $this->_addRow($v);
        }
        $long .= '</table>';

        $str .= $long.'
         </div>
     </body>
 </html> ';

        echo $str;die;
    }

    private function _addRow($row)
    {
        $cells = "";
        foreach ($row as $k => $v)
        {
            $cells .= '<td class=xl2216681 nowrap>'.$v.'</td>';
        }

        return  "<tr style='border:1px solid #D3D3D3'>" . $cells . "</tr>";
    }
}