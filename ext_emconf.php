<?php
$EM_CONF[$_EXTKEY] = array(
    'title' => 'Anti-Robots',
    'description' => 'Prevent search engines from indexing specific news',
    'category' => '',
    'author' => 'Markus Kugler',
    'author_mail' => 'projects@jweiland.net',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => '',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.1.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '6.2.0-6.2.99',
            'news' => '3.0.0'
        ),
        'conflicts' => array(
            
        ),
        'suggests' => array(
            
        ),
    )
);