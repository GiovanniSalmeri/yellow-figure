<?php
// Figure extension, https://github.com/GiovanniSalmeri/yellow-figure

class YellowFigure {
    const VERSION = "0.8.20";
    public $yellow;         // access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }

    // Handle page content of shortcut
    public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if ($name=="figure" && ($type=="inline" || $type=="block")) {
            if ($this->yellow->extension->isExisting("image")) {
                $imageElement = $this->yellow->extension->get("image")->onParseContentShortcut($page, "image", $text, "inline");
                if (preg_match('/ alt="([^"]+)"/', $imageElement, $matches)) {
                    $figureTag = [ "block"=>"figure", "inline"=>"span role=\"figure\"" ];
                    $captionTag = [ "block"=>"figcaption", "inline"=>"span" ];
                    $caption = $matches[1];
                    $captionId = "caption-".uniqid();
                    $captionElement = "<".$captionTag[$type]." id=\"".$captionId."\" class=\"figure-caption\">".$caption."</".strtok($captionTag[$type], " ").">\n";
                    preg_match('/ class="([^"]*)"/', $imageElement, $matches);
                    $class = " class=\"figure".($matches ? " ".$matches[1] : "")."\"";
                    preg_match('/ width="([^"]+)"/', $imageElement, $matches);
                    $width = $matches ? $matches[1] : "";
                    if (is_numeric($width)) $width .= "px";
                    $imageElement = preg_replace('/ class="[^"]*"/', "", $imageElement);
                    $imageElement = preg_replace('/ alt="[^"]*"/', " alt=\"\" aria-hidden=\"true\"", $imageElement);
                    $imageElement = preg_replace('/ title="[^"]*"/', "", $imageElement);
                    $output .= "<".$figureTag[$type]." style=\"max-width:".$width."\" aria-labelledby=\"".$captionId."\"".$class.">\n";
                    $output .= $imageElement."\n";
                    $output .= $captionElement;
                    $output .= "</".strtok($figureTag[$type], " ").">";
                } else {
                    $this->toolbox->log("error", "Invalid format of image shortcut!");
                    $output = $imageElement;
                }
            } else {
                $page->error(500, "Figure requires 'image' extension!");
            }
        }
        return $output;
    }
    
    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name=="header") {
            $extensionLocation = $this->yellow->system->get("coreServerBase").$this->yellow->system->get("coreExtensionLocation");
            $output = "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"{$extensionLocation}figure.css\" />\n";
        }
        return $output;
    }
}
