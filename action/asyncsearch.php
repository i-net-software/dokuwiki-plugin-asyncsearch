<?php
/**
 * DokuWiki Plugin asyncsearch (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  i-net software / Gerry Weißbach <tools@inetsoftware.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_asyncsearch_asyncsearch extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
       $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_action_act_preprocess');
       $controller->register_hook('JS_SCRIPT_LIST', 'BEFORE', $this, 'handle_js_script_list');
       $controller->register_hook('TPL_ACT_RENDER', 'BEFORE', $this, 'handle_action_tpl_act_renderer');
       $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'handle_tpl_metaheader_output');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_action_act_preprocess(Doku_Event &$event, $param) {
        global $ACT;
        global $QUERY;

        $QUERY = cleanID( $QUERY );
        if ( $event->data === 'search' && !empty( $QUERY ) ) {
            $ACT = 'asyncsearch';
            $event->preventDefault();
            return false;
        }
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_action_tpl_act_renderer(Doku_Event &$event, $param) {
        global $ACT, $QUERY, $ID;
        global $lang;
        
        if ( $ACT !== 'asyncsearch' ) { return; }

        $intro = p_locale_xhtml('searchpage');
        // allow use of placeholder in search intro
        $pagecreateinfo = (auth_quickaclcheck($ID) >= AUTH_CREATE) ? $lang['searchcreatepage'] : '';
        $intro = str_replace(
            array('@QUERY@', '@SEARCH@', '@CREATEPAGEINFO@'),
            array(hsc(rawurlencode($QUERY)), hsc($QUERY), $pagecreateinfo),
            $intro
        );
        
        echo $intro;
        flush();
        echo '<div id="asyncsearch" data-term="'.hsc($QUERY).'"></div>';

        $event->preventDefault();
        return false;
    }

    /**
     * Insert an extra script tag for users that have AUTH_EDIT or better
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_tpl_metaheader_output(Doku_Event &$event, $param) {
        global $ACT;
        
        // add script if user has better auth than AUTH_EDIT
        if ( $ACT !== 'asyncsearch' ) {  return; }

        $event->data['script'][] = array(
            'type'=> 'text/javascript', 'charset'=> 'utf-8', '_data'=> '',
            'src' => DOKU_BASE.'lib/exe/js.php'.'?type=asyncsearch&tseed='.$tseed
        );
    }

    /**
     * Finally, handle the JS script list. The script would be fit to do even more stuff / types
     * but handles only admin and default currently.
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_js_script_list(Doku_Event &$event, $param) {
        global $INPUT;

        if ( $INPUT->str('type') === 'asyncsearch' ) {
            $event->data = $this->js_pluginscripts();
            sort($event->data);
        }
    }

    /**
     * Returns a list of possible Plugin Scripts (no existance check here)
     * @return array
     */
     private function js_pluginscripts(){
        $list = array();
        $plugins = plugin_list();
        foreach ($plugins as $p){
            $list[] = DOKU_PLUGIN."$p/asyncsearch.js";
        }
        return $list;
    }
}

// vim:ts=4:sw=4:et:
