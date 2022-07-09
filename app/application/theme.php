<?php
    $c = [
        'text.color' => '#9AB2BC',
        'background' => '#1a1818',
        'seafoam' => '#5eaa89',
        'secondary' => '#303332',
        'menu.active.item' => 'rgba(247, 247, 247, 0.12)'
    ];
?>

<style>
    body {
        background-color: <?= $c['background'] ?>;
        color: <?= $c['text.color'] ?>;
    }
    div#site_main_container {
        margin-top: 40px;
    }

    div.ui.fixed.menu {
        background-color: <?= $c['secondary'] ?>;
    }
    div.ui.fixed.menu .container .item {
        color: white;
    }
    div.ui.fixed.menu .container .item.active {
        background-color: <?= $c['menu.active.item'] ?>;
    }

    .ui.segment {
        background-color: <?= $c['secondary'] ?>;
        color: <?= $c['text.color'] ?>;
    }
    .ui.segment.login {
        background-color: <?= $c['seafoam'] ?>;
    }
</style>
