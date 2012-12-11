<?php
include dirname(__FILE__).'/include.php';

$directry_url = $settings['url'];
$directry_page = $http->get($directry_url);
$directry_doc = phpQuery::newDocumentHTML($directry_page);
$directry_div = $directry_doc->find('div.mw-content-ltr');
$directry_div = pq($directry_div);

$last_h2 = $last_h3 = null;


$root = new node(pq($directry_doc->find('h1'))->text());


foreach ($directry_div->children() as $child) {
    $child = pq($child);
    if ($child->is('h2')) {
        $root->children[]=new node($child->text());
        $last_h2 = $root->children[count($root->children)-1];
        $last_h3 = null;
    } else if($child->is('h3')){
        $last_h2->children[]=new node($child->text());
        $last_h3 = $last_h2->children[count($last_h2->children)-1];
    } else if($child->is("ul, ol")){
        foreach ($child->find('li > a') as $a) {
            $a = pq($a);
            $parent = ($last_h3==null?$last_h2:$last_h3);
            $parent->children[]= new node($a->text(), http::fixUrl($directry_url, $a->attr('href')));
        }
    }
}

if(isset($settings['book']['title'])) $root->name = $settings['book']['title'];

file_put_contents("directory_saved.php", "<?php return ".var_export($root, true).";");
print_r($root);