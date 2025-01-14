<?php

/**
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Comments\UserGui;


use Xaraya\Modules\Comments\UserGui;
use Xaraya\Modules\Comments\Defines;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarVar;
use xarModHooks;
use xarModVars;
use xarTpl;
use xarController;
use xarMod;
use xarUser;
use xarLocale;
use xarConfigVars;
use DataObjectFactory;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * comments user reply function
 * @extends MethodClass<UserGui>
 */
class ReplyMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * processes comment replies and then redirects back to the
     * appropriate module/object itemid (aka page)
     * @author Carl P. Corliss (aka rabbitt)
     * @access public
     * @return array|string|null returns whatever needs to be parsed by the BlockLayout engine
     */
    public function __invoke(array $args = [])
    {
        if (!$this->checkAccess('PostComments')) {
            return;
        }


        # --------------------------------------------------------
        # Get all the relevant info from the submitted comments form
        #


        # --------------------------------------------------------
        # Take appropriate action
        #
        if (!$this->fetch('comment_action', 'str', $data['comment_action'], 'reply', xarVar::NOT_REQUIRED)) {
            return;
        }
        switch (strtolower($data['comment_action'])) {
            case 'submit':
                # --------------------------------------------------------
                # Get the values from the form
                #
                $data['reply'] = DataObjectFactory::getObject(['name' => 'comments_comments']);
                $valid = $data['reply']->checkInput();

                // call transform input hooks
                // should we look at the title as well?
                $package['transform'] = ['text'];

                $package = xarModHooks::call(
                    'item',
                    'transform-input',
                    0,
                    $package,
                    'comments',
                    0
                );

                if ($this->getModVar('AuthorizeComments') || $this->checkAccess('AddComments')) {
                    $status = Defines::STATUS_ON;
                } else {
                    $status = Defines::STATUS_OFF;
                }

                # --------------------------------------------------------
                # If something is wrong, represent the form
                #
                if (!$valid) {
                    return xarTpl::module('comments', 'user', 'reply', $data);
                }

                # --------------------------------------------------------
                # Everything is go: if there is a comment, create and go to the next page
                #
                if (!empty($data['reply']->properties['text']->value)) {
                    $data['comment_id'] = $data['reply']->createItem();
                } else {
                    $data['comment_id'] = 0;
                }
                $this->redirect($data['reply']->properties['parent_url']->value . '#' . $data['comment_id']);
                return true;

            case 'reply':
                # --------------------------------------------------------
                # Bail if the proper args were not passed
                #
                if (!$this->fetch('comment_id', 'int:1:', $data['comment_id'], 0, xarVar::NOT_REQUIRED)) {
                    return;
                }
                if (empty($data['comment_id'])) {
                    return xarController::notFound(null, $this->getContext());
                }

                # --------------------------------------------------------
                # Create the comment object
                #
                sys::import('modules.dynamicdata.class.objects.factory');
                $data['object'] = DataObjectFactory::getObject(['name' => 'comments_comments']);
                $data['object']->getItem(['itemid' => $data['comment_id']]);

                // replace the deprecated eregi stuff below
                $title = & $data['object']->properties['title']->value;
                $text  = & $data['object']->properties['text']->value;
                $title = preg_replace('/^re:/i', '', $title);
                $new_title = 'Re: ' . $title;

                // get the title and link of the original object
                $modinfo = xarMod::getInfo($data['object']->properties['moduleid']->value);
                try {
                    $itemlinks = xarMod::apiFunc(
                        $modinfo['name'],
                        'user',
                        'getitemlinks',
                        ['itemtype' => $data['object']->properties['itemtype']->value,
                            'itemids' => [$data['object']->properties['itemid']->value], ]
                    );
                } catch (Exception $e) {
                }
                if (!empty($itemlinks) && !empty($itemlinks[$data['object']->properties['itemid']->value])) {
                    $url = $itemlinks[$header['itemid']]['url'];
                    $header['objectlink'] = $itemlinks[$data['object']->properties['itemid']->value]['url'];
                    $header['objecttitle'] = $itemlinks[$data['object']->properties['itemid']->value]['label'];
                } else {
                    $url = xarController::URL($modinfo['name'], 'user', 'main');
                }
                /*
                            list($text,
                                 $title) =
                                        xarModHooks::call('item',
                                                        'transform',
                                                         $data['object']->properties['parent_id']->value,
                                                         array($text,
                                                               $title));
                */
                $text         = xarVar::prepHTMLDisplay($text);
                $title        = xarVar::prepForDisplay($title);

                $package['new_title']            = xarVar::prepForDisplay($new_title);
                $data['package']               = $package;

                // Create an object item for the reply
                $data['reply'] = DataObjectFactory::getObject(['name' => 'comments_comments']);
                $data['reply']->properties['title']->value = $new_title;
                $data['reply']->properties['position']->reference_id = $data['comment_id'];
                $data['reply']->properties['position']->position = 3;
                $data['reply']->properties['moduleid']->value = $data['object']->properties['moduleid']->value;
                $data['reply']->properties['itemtype']->value = $data['object']->properties['itemtype']->value;
                $data['reply']->properties['itemid']->value = $data['object']->properties['itemid']->value;
                $data['reply']->properties['parent_url']->value = $data['object']->properties['parent_url']->value;
                break;
            case 'preview':
            default:
                [$package['transformed-text'],
                    $package['transformed-title']] = xarModHooks::call(
                        'item',
                        'transform',
                        $header['parent_id'],
                        [$package['text'],
                            $package['title'], ]
                    );

                $package['transformed-text']  = xarVar::prepHTMLDisplay($package['transformed-text']);
                $package['transformed-title'] = xarVar::prepForDisplay($package['transformed-title']);
                $package['text']              = xarVar::prepHTMLDisplay($package['text']);
                $package['title']             = xarVar::prepForDisplay($package['title']);

                $comments[0]['text']      = $package['text'];
                $comments[0]['title']     = $package['title'];
                $comments[0]['moduleid']  = $header['moduleid'];
                $comments[0]['itemtype']  = $header['itemtype'];
                $comments[0]['itemid']    = $header['itemid'];
                $comments[0]['parent_id'] = $header['parent_id'];
                $comments[0]['author']    = ((xarUser::isLoggedIn() && !$package['postanon']) ? xarUser::getVar('name') : 'Anonymous');
                $comments[0]['id']       = 0;
                $comments[0]['postanon']  = $package['postanon'];
                // FIXME delete after time output testing
                // $comments[0]['date']      = xarLocale::formatDate("%d %b %Y %H:%M:%S %Z",time());
                $comments[0]['date']      = time();
                $comments[0]['hostname']  = 'somewhere';

                $package['comments']          = $comments;
                $package['new_title']         = $package['title'];
                $receipt['action']            = 'reply';

                break;
        }

        $hooks = xarMod::apiFunc('comments', 'user', 'formhooks');
        /*
            // Call new hooks for categories, dynamicdata etc.
            $args['module'] = 'comments';
            $args['itemtype'] = 0;
            $args['itemid'] = 0;
            // pass along the current module & itemtype for pubsub (urgh)
        // FIXME: handle 2nd-level hook calls in a cleaner way - cfr. categories navigation, comments add etc.
            $args['id'] = 0; // dummy category
            $modinfo = xarMod::getInfo($header['moduleid']);
            $args['current_module'] = $modinfo['name'];
            $args['current_itemtype'] = $header['itemtype'];
            $args['current_itemid'] = $header['itemid'];
            $hooks['iteminput'] = xarModHooks::call('item', 'new', 0, $args);
        */

        # --------------------------------------------------------
        # Pass args to the form template
        #
        $anonuid = xarConfigVars::get(null, 'Site.User.AnonymousUID');
        $data['hooks']              = $hooks;
        $data['package']            = $package;
        $data['package']['date']    = time();
        $data['package']['role_id']     = ((xarUser::isLoggedIn() && !$data['object']->properties['anonpost']->value) ? xarUser::getVar('id') : $anonuid);
        $data['package']['uname']   = ((xarUser::isLoggedIn() && !$data['object']->properties['anonpost']->value) ? xarUser::getVar('uname') : 'anonymous');
        $data['package']['name']    = ((xarUser::isLoggedIn() && !$data['object']->properties['anonpost']->value) ? xarUser::getVar('name') : 'Anonymous');

        return $data;
    }
}
