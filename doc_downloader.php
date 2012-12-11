<?php

include dirname(__FILE__).'/include.php';

$root = require 'directory_saved.php';
rrmdir("docs");
mkdir("docs");
mkdir("docs/img");

function changeName($node, $name) {
    $newnode = pq($node->ownerDocument->createElement($name));
    $node = pq($node);
    foreach ($node->attr('*') as $attrName => $attrNode) {
        $newnode->attr($attrName, $attrNode);
    }
    $newnode->html($node->html());
    $newnode->insertBefore($node);
    $node->remove();
    return $newnode;
}

function getImgPath($src) {
    preg_match('~\.([^\.]+)$~', $src, $mtch);
    return md5($src) . '.' . (isset($mtch[1]) ? $mtch[1] : 'jpg');
}

function dfs(node $node, $depth = 1) {
    $node->hash = $depth . "_" . md5($node->name);
    $node->name = htmlspecialchars($node->name);
    if (!empty($node->meta)) {
        $doc = phpQuery::newDocumentFileHTML($node->meta);
        $head = pq($doc->find('head'));
        $head->children()->remove();
        $head->append("<meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />
<link rel=\"stylesheet\" href=\"styles.css\" type=\"text/css\">
            <title>" . $node->name . "</title>");
        $content = pq($doc->find('div.mw-content-ltr'))->html();
        $doc->find('body')->html("<h1 class=\"main\">" . $node->name . "</h1>" . $content);
        $doc->find('table#toc, script, span.editsection')->remove();
        foreach ($doc->find('a') as $a) {
            $a = pq($a);
            $a->after(htmlspecialchars(trim($a->text())));
            $a->remove();
        }
        for ($i = 12; $i >= 1; $i--) {
            foreach ($doc->find("h$i") as $h) {
                changeName($h, "h" . ($i - 1 + $depth));
            }
        }
        foreach ($doc->find('img') as $img) {
            $img = pq($img);
            $src = http::fixUrl($node->meta, $img->attr('src'));
            $path = getImgPath($src);
            if (!file_exists("docs/img/$path"))
                download_file($src, "docs/img/$path");
            $img->attr('src', "img/$path");
        }
        file_put_contents("docs/" . $node->hash . ".html", $doc->html());
    }else {
        file_put_contents("docs/" . $node->hash . ".html", "<!DOCTYPE html>
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<title>{$node->name}</title>
<meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />
<link rel=\"stylesheet\" href=\"styles.css\" type=\"text/css\">
</head>
<body>
<div>
<h$depth>{$node->name}</h$depth>
</div>
</body>
</html>");
    }
    foreach ($node->children as $child)
        dfs($child, $depth + 1);
    
}

dfs($root);
file_put_contents("directory_saved.php", "<?php return ".var_export($root, true).";");
copy(dirname(__FILE__)."/styles.css", "docs/styles.css");