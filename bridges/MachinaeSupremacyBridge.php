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
        
        $container = $html->find('div#masu', 0);
        
        // Fix links and images
        foreach($container->find('a') as $linkTag) {
            if (strpos($linkTag->href, '/') === 0) {
                $linkTag->href = self::BASE_URI . $linkTag->href;
            }
        }
        foreach($container->find('img') as $imgTag) {
            if (strpos($imgTag->src, '/') === 0) {
                $imgTag->src = self::BASE_URI . $imgTag->src;
            }
        }
        
        /* @var $container simple_html_dom_node */
        $item = new \Item();
        foreach($container->children() as $child) {
            switch($child->tag) {
            	case 'h2':
            	    $item->title = $child->plaintext;
            	    $item->uri = $child->find('a', 0)->href;
            	    $item->timestamp = time();
            	    $item->content = '';
            	    break;
            	    
        	    case 'p';
        	       $item->content .= (string) $child;
        	       break;
        	    
        	    case 'hr':
        	        $this->items[] = $item;
        	        $item = new \Item();
        	        break;
            }
        }
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
        return 86400; // 1 day
    }
}
