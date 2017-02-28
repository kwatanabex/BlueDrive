<?php
/**
 * -----------------------------------------------------------------------------
 *
 * SyL - PHP Application Library
 *
 * PHP version 5 (>= 5.2.10)
 *
 * Copyright (C) 2006-2011 k.watanabe
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -----------------------------------------------------------------------------
 * @package    SyL.Lib
 * @subpackage SyL.Lib.Validation
 * @author     Koki Watanabe <k.watanabe@syl.jp>
 * @copyright 2006-2012 k.watanabe
 * @license    http://www.opensource.org/licenses/lgpl-license.php
 * @version    CVS: $Id$
 * @link       http://syl.jp/
 * -----------------------------------------------------------------------------
 */

/**
 * 画像フォーム要素クラス
 *
 * @package    SyL.Lib
 * @subpackage SyL.Lib.Form
 * @author     Koki Watanabe <k.watanabe@syl.jp>
 * @copyright 2006-2012 k.watanabe
 * @license    http://www.opensource.org/licenses/lgpl-license.php
 * @version    CVS: $Id$
 * @link       http://syl.jp/
 */
class SyL_FormElementImage extends SyL_FormElementAbstract
{
    /**
     * 画像表示フラグ
     *
     * @var bool
     */
    private $image_display = false;
    /**
     * ファイル領域名
     *
     * @var string
     */
    private $file_area = null;

    /**
     * 画像表示フラグをセットする
     *
     * @param bool 画像表示フラグ
     */
    public function setImageDisplay($image_display)
    {
        $this->image_display = $image_display;
    }

    /**
     * ファイル領域名をセットする
     *
     * @param string ファイル領域名
     */
    public function setFileArea($file_area)
    {
        $this->file_area = $file_area;
    }

    /**
     * フォーム要素HTML出力（入力項目）
     *
     * @param string フォーム名
     * @return string フォーム要素のHTML
     */
    protected function getHtmlTag()
    {
        $this->setAttribute($this->prefix_id . $this->name, 'id');
        $this->setAttribute($this->name, 'name');
        $this->setAttribute('text', 'type');
        $this->setAttribute($this->value, 'value');

        $html = '';
        if ($this->image_display) {
            $html = '<img src="' . $this->value . '" alt="" /><br />';
        }

        $fileid = null;
        if ($this->file_area) {
            $fileid = $this->prefix_id . $this->name . '_' . $this->file_area;
            $html .= '
<script type="text/javascript">
$(function(){
$("#' . $fileid . '").colorbox({width:"850px", height:"400px", iframe:true});
});
</script>
';
        }

        $html .= '<input ' . $this->getAttributeString() . ' />';
        if ($fileid) {
            $html .= ' <a href="/admin/file/manager/dialog.html?name=' . $this->file_area . '" id="' . $fileid . '">選択</a>';
        }

        return $html;
    }

    /**
     * フォーム要素HTML出力（表示）
     *
     * @return string フォーム要素のHTML
     */
    protected function getHtmlView()
    {
        $html = '';
        if ($this->image_display) {
            $html = '<img src="' . $this->value . '" alt="" />';
        } else {
            $html = $this->getHtmlSpan($this->name, self::encode($this->value));
        }
        $html .= $this->getHtmlHidden();

        return $html;
    }
}
