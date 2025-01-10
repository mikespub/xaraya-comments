<?php

/**
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Comments\UserApi;

use Xaraya\Modules\MethodClass;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi activate function
 */
class ActivateMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Activate the specified comment
     * @author Carl P. Corliss (aka rabbitt)
     * @access public
     * @param int $id id of the comment to lookup
     * @return bool returns true on success, throws an exception and returns false otherwise
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (empty($id)) {
            $msg = xarML('Missing or Invalid parameter \'id\'!!');
            throw new BadParameterException($msg);
        }

        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();

        // First grab the objectid and the modid so we can
        // then find the root node.
        $sql = "UPDATE $xartable[comments]
                SET status='" . _COM_STATUS_ON . "'
                WHERE id=?";
        $bindvars = [(int) $id];

        $result = & $dbconn->Execute($sql, $bindvars);

        if (!$result) {
            return;
        }
    }
}
