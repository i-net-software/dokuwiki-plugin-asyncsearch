<?php
/**
 * DokuWiki Plugin asyncsearch (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  i-net software / Gerry Weißbach <tools@inetsoftware.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_asyncsearch_pagelookup extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
       $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax_call_unknown');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_ajax_call_unknown( Doku_Event &$event, $param) {
        global $ACT, $INPUT;

        if ( $event->data === 'asyncsearch' && $INPUT->str('pluginID') == 'pagelookup' ) {
            $this->handle_ft_pageLookup( $INPUT->str('term') );
        } else
        if ( $event->data === 'asyncsearch' && $INPUT->str('pluginID') == 'pagesearch' ) {
            $this->handle_ft_pageSearch( $INPUT->str('term') );
        } else {
            return true;
        }

        $event->preventDefault();
        return false;
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    private function handle_ft_pageLookup( $QUERY ) {
        global $lang;

        //do quick pagesearch
        $data = ft_pageLookup($QUERY,true,useHeading('navigation'));
        if(count($data)){
            print '<div class="search_quickresult">';
            print '<h3>'.$lang['quickhits'].':</h3>';
            print '<ul class="search_quickhits">';
            foreach($data as $id => $title){
                print '<li> ';
                if (useHeading('navigation')) {
                    $name = $title;
                }else{
                    $ns = getNS($id);
                    if($ns){
                        $name = shorten(noNS($id), ' ('.$ns.')',30);
                    }else{
                        $name = $id;
                    }
                }
                print html_wikilink(':'.$id,$name);
                print '</li> ';
            }
            print '</ul> ';
            //clear float (see http://www.complexspiral.com/publications/containing-floats/)
            print '<div class="clearer"></div>';
            print '</div>';
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
    private function handle_ft_pageSearch( $QUERY ) {
        global $lang;

        //do fulltext search
        $data = ft_pageSearch($QUERY,$regex);
        if(count($data)){
            print '<dl class="search_results">';
            $num = 1;
            foreach($data as $id => $cnt){
                print '<dt>';
                print html_wikilink(':'.$id,useHeading('navigation')?null:$id,$regex);
                if($cnt !== 0){
                    print ': '.$cnt.' '.$lang['hits'].'';
                }
                print '</dt>';
                if($cnt !== 0){
                    if($num < FT_SNIPPET_NUMBER){ // create snippets for the first number of matches only
                        print '<dd>'.ft_snippet($id,$regex).'</dd>';
                    }
                    $num++;
                }
                flush();
            }
            print '</dl>';
        }else{
            print '<div class="nothing">'.$lang['nothingfound'].'</div>';
        }
    }    
    
}

// vim:ts=4:sw=4:et:
