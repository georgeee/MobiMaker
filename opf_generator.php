<?php

include dirname(__FILE__).'/include.php';

$root = require 'directory_saved.php';
$nav_levels = array();
for($i=0; $i<10; $i++) $nav_levels[$i] = isset($settings['levels'][$i])&&$settings['levels'][$i];
$docs_used = array();

function dfs(node $node, $depth = 0) {
    global $manifest, $mtc, $order, $navs, $tul, $nav_levels, $docs_used;
    if(isset($docs_used[$node->hash])) return;
    $docs_used[$node->hash] = true;
    $manifest.='<item id="' . $node->hash . '" media-type="application/xhtml+xml" href="docs/' . $node->hash .'.html"></item>';
        
    
    if ($nav_levels[$depth]) {
        $mtc .= '        <itemref idref="' . $node->hash. '"/>';
        $navs.='
        <navPoint class="h' . $depth . '" id="' . $node->hash . '" playOrder="' . ($order++) . '">
            <navLabel>
                <text>' . $node->name . '</text>
            </navLabel>
            <content src="docs/' . $node->hash . '.html"/>';
        $tul.='<li class="h' . $depth . '"><a href="' . $node->hash . '.html">' . $node->name . '</a></li>';
        if (count($node->children) > 0)
            $tul.='<ul>';
    }
    foreach ($node->children as $child)
        dfs($child, $depth + 1);
    if ($nav_levels[$depth]) {
        $navs.="</navPoint>";
        if (count($node->children) > 0)
            $tul.='</ul>';
    }
}

$manifest = $mtc = $navs = $tul = '';
$order = 2;
dfs($root);
$objects = scandir("docs/img");
foreach ($objects as $object) {
    if ($object != "." && $object != "..") {
        $manifest .= '<item id="' . $object . '" media-type="image/jpeg" href="docs/img/' . $object . '"/>';
    }
}

$ncx = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE ncx PUBLIC "-//NISO//DTD ncx 2005-1//EN"
    "http://www.daisy.org/z3986/2005/ncx-2005-1.dtd">

<!--
    For a detailed description of NCX usage please refer to:
    http://www.idpf.org/2007/opf/OPF_2.0_final_spec.html#Section2.4.1
-->

<ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1" xml:lang="en-US">
    <head>
        <meta name="dtb:uid" content="BookId"/>
        <meta name="dtb:depth" content="2"/>
        <meta name="dtb:totalPageCount" content="0"/>
        <meta name="dtb:maxPageNumber" content="0"/>
    </head>
    <docTitle>
        <text>' . $root->name . '</text>
    </docTitle>
    <docAuthor>
        <text>Georgeee</text>
    </docAuthor>
    <navMap>
        <navPoint class="toc" id="toc" playOrder="1">
            <navLabel>
                <text>Содержание</text>
            </navLabel>
            <content src="docs/toc.html"/>
        </navPoint>
        ' . $navs . '
    </navMap>
</ncx>';


$opf = '<?xml version="1.0" encoding="utf-8"?>
<package xmlns="http://www.idpf.org/2007/opf" version="2.0" unique-identifier="BookId">
    <metadata xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:opf="http://www.idpf.org/2007/opf">
        <dc:title>' . $root->name . '</dc:title>
        <dc:language>'.$settings['book']['lang'].'</dc:language>
        '.(empty($settings['creator'])?'':'<dc:creator>'.implode('</dc:creator><dc:creator>', $settings['creator']).'</dc:creator>').'
        <dc:publisher>Georgeee</dc:publisher>
        <dc:creator>Georgeee</dc:creator>
        <dc:subject>Reference</dc:subject>
        <dc:date>' . date('Y-m-d') . '</dc:date>
        <dc:description>'.(empty($settings['book']['desc'])?'':$settings['book']['desc']).'</dc:description>
    </metadata>
    <manifest>
        <item id="toc" media-type="application/xhtml+xml" href="docs/toc.html"></item>
        ' . $manifest . '
        <item id="My_Table_of_Contents" media-type="application/x-dtbncx+xml" href="book.ncx"/>
    </manifest>
    <spine toc="My_Table_of_Contents">
        <itemref idref="toc"/>
        ' . $mtc . '
    </spine>
    <guide>
        <reference type="toc" title="Содержание" href="docs/toc.html"></reference>
    </guide>
</package>';
$toc = '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Содержание</title>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
        <link rel="stylesheet" href="styles.css" type="text/css" />
    </head>
    <body>
        <div>
            <h2>Содержание</h2>
            <ul>
                ' . $tul . '
            </ul>
        </div>
    </body>
</html>';

file_put_contents("book.opf", $opf);
file_put_contents("book.ncx", $ncx);
file_put_contents('docs/toc.html', $toc);

//exec('./kindlegen/kindlegen book.opf');