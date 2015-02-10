<?php

/**
* RssBridgeMachinaeSupremacy
* Returns Machinae Supremacy's newest posts
*
* @name MachinaeSupremacy
* @homepage http://www.machinaesupremacy.com/
* @description Returns Machinae Supremacy's newest posts
* @maintainer nanawel
* @updated 2014-06-14
*/
class MachinaeSupremacyBridge extends BridgeAbstract
{

    const BASE_URI = 'http://www.machinaesupremacy.com';

    public function collectData(array $param)
    {
        
        $html = file_get_html(self::BASE_URI) or $this->returnError('Could not request MachinaeSupremacy.', 404);
        
        $container = $html->find('div#posts', 0);
        
        // Fix links and images
        foreach ($container->find('a') as $linkTag) {
            if (strpos($linkTag->href, '/') === 0) {
                $linkTag->href = self::BASE_URI . $linkTag->href;
            }
        }
        foreach ($container->find('img') as $imgTag) {
            if (strpos($imgTag->src, '/') === 0) {
                $imgTag->src = self::BASE_URI . $imgTag->src;
            }
        }
        
        /* @var $container simple_html_dom_node */
        $item = new \Item();
        foreach ($container->find('article') as $child) {
            $postMain = $child->find('section.postMain');
            if (!empty($postMain)) {
                $postMain = $postMain[0];
                
                // Title
                $title = $postMain->find('h3 a');
                if (!empty($title)) {
                    $title = $title[0];
                    $icon = $title->find('i');
                    if (!empty($icon)) {
                        $this->_removeNode($icon[0]);
                    }
                    $item->title = $this->_cleanup($title->innertext());
                }
                
                // URI
                $permalink = $child->find('li.permalink a')[0];
                $item->uri = $permalink->href;
                
                // TIMESTAMP
                $icon = $permalink->find('i')[0];
                $this->_removeNode($icon);
                $item->timestamp = strtotime($permalink->innertext());
                
                // CONTENT
                // Text
                $content = $postMain->find('div.description');
                if (!empty($content)) {
                    $item->content = (string) $content[0];
                }
                else {
                    // Image caption
                    $content = $postMain->find('div.caption');
                    if (!empty($content)) {
                        $item->content = (string) $content[0];
                    }
                    else {
                        // Tracklist
                        $content = $postMain->find('ol.tracklist');
                        if (!empty($content)) {
                            $item->content = (string) $content[0];
                        }
                    }
                }
                
                // TITLE (FALLBACK)
                if (!isset($item->title)) {
                    $item->title = mb_substr($this->_cleanup($content[0]->text()), 0, 60) . '...';
                }
                
                $this->items[] = $item;
                $item = new \Item();
            }
            
        }
    }
    
    protected function _removeNode($node)
    {
        $node->outertext = '';
    }
    
    protected function _cleanup($str)
    {
        return trim(html_entity_decode($str));
    }

    public function getName()
    {
        return 'MachinaeSupremacy';
    }

    public function getURI()
    {
        return self::BASE_URI . '/';
    }

    public function getCacheDuration()
    {
        return 43200; // 12h
    }
}
