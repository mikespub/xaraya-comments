<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2007 The copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage comments
 * @link http://xaraya.com/index.php/release/14.html
 * @author Carl P. Corliss <rabbitt@xaraya.com>
 */
/**
 * handle static text property
 *
 * @package dynamicdata
 *
 */
sys::import('modules.dynamicdata.class.properties.base');

class CommentsNumberOfProperty extends DataProperty
{
    /**
     * Get the base information for this property.
     *
     * @return array base information for this property
     **/
    public function getBasePropertyInfo()
    {
        $args = [];
        $baseInfo = [
                            'id'         => 104,
                            'name'       => 'numcomments',
                            'label'      => '# of Comments',
                            'format'     => '104',
                            'validation' => 'comments_userapi_get_count',
                            'source'     => 'user function',
                            'dependancies' => '',
                            'requiresmodule' => 'comments',
                            'aliases' => '',
                            'args'           => serialize($args),
                            // ...
                           ];
        return $baseInfo;
    }
}
